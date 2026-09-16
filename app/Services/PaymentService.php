<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a payment against an order.
     * Automatically recalculates paid_amount, balance_amount,
     * and updates payment_status on the order.
     *
     * @throws \Exception
     */
    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {

            // 1. Guard: cancelled orders cannot receive payments
            if ($order->isCancelled()) {
                throw new \Exception('Cannot record payment for a cancelled order.');
            }

            // 2. Guard: already fully paid
            if ($order->isFullyPaid()) {
                throw new \Exception('This order is already fully paid.');
            }

            // 3. Guard: payment cannot exceed outstanding balance
            $balance = (float) $order->balance_amount;
            $amount  = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than zero.');
            }

            if ($amount > $balance) {
                throw new \Exception(
                    "Payment amount PKR " . number_format($amount, 2)
                  . " exceeds outstanding balance PKR " . number_format($balance, 2) . "."
                );
            }

            // 4. Create payment record
            $payment = Payment::create([
                'order_id'         => $order->id,
                'collected_by'     => $data['collected_by']     ?? auth()->id(),
                'amount'           => $amount,
                'payment_method'   => $data['payment_method']   ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date'     => $data['payment_date']     ?? now()->toDateString(),
                'notes'            => $data['notes']            ?? null,
            ]);

            // 5. Recalculate order payment totals
            $this->recalculatePaymentStatus($order);

            return $payment;
        });
    }

    /**
     * Recalculate paid_amount, balance_amount, and payment_status
     * based on all payments recorded against the order.
     */
    public function recalculatePaymentStatus(Order $order): void
    {
        $order->refresh();

        $totalPaid = (float) $order->payments()->sum('amount');
        $netAmount = (float) $order->net_amount;
        $balance   = round($netAmount - $totalPaid, 2);

        $paymentStatus = match(true) {
            $totalPaid <= 0              => 'unpaid',
            $balance   <= 0             => 'paid',
            default                     => 'partial',
        };

        $order->update([
            'paid_amount'    => round($totalPaid, 2),
            'balance_amount' => max(0, $balance),
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * Get full payment summary for an order.
     */
    public function getSummary(Order $order): array
    {
        $order->loadMissing('payments.collectedBy');

        return [
            'net_amount'     => (float) $order->net_amount,
            'total_paid'     => (float) $order->paid_amount,
            'balance'        => (float) $order->balance_amount,
            'payment_status' => $order->payment_status,
            'is_fully_paid'  => $order->isFullyPaid(),
            'payments'       => $order->payments,
        ];
    }

    /**
     * Validate payment data before recording.
     *
     * @throws \Exception
     */
    public function validate(array $data, Order $order): void
    {
        if (empty($data['amount']) || (float) $data['amount'] <= 0) {
            throw new \Exception('Payment amount is required and must be greater than zero.');
        }

        if ((float) $data['amount'] > (float) $order->balance_amount) {
            throw new \Exception(
                'Payment exceeds balance. Maximum payable: PKR '
              . number_format($order->balance_amount, 2)
            );
        }

        $validMethods = ['cash', 'bank_transfer', 'cheque', 'easypaisa', 'jazzcash'];
        if (!empty($data['payment_method']) && !in_array($data['payment_method'], $validMethods)) {
            throw new \Exception('Invalid payment method selected.');
        }

        // Reference number required for non-cash methods
        $requiresRef = ['bank_transfer', 'cheque', 'easypaisa', 'jazzcash'];
        if (
            !empty($data['payment_method'])
            && in_array($data['payment_method'], $requiresRef)
            && empty($data['reference_number'])
        ) {
            throw new \Exception(
                ucfirst(str_replace('_', ' ', $data['payment_method']))
              . ' requires a reference number / transaction ID.'
            );
        }
    }

    /**
     * Authorize: ensure actor belongs to the order's outlet.
     */
    public function authorize(\App\Models\User $actor, Order $order): void
    {
        if ($order->outlet_id !== $actor->outlet_id) {
            throw new \Exception('You do not have access to this order.');
        }
    }
}