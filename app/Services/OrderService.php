<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    // ── Stock Validation ───────────────────────────────────────────────────────

    /**
     * Validate all items have enough available stock.
     * Throws detailed exception listing every failing product.
     *
     * @param  array $items  [['product_id', 'quantity'], ...]
     * @param  int   $outletId
     * @throws \Exception
     */
    public function validateStock(array $items, int $outletId): void
    {
        $errors = [];

        foreach ($items as $item) {
            $product = Product::where('id', $item['product_id'])
                              ->where('outlet_id', $outletId)
                              ->where('is_active', true)
                              ->first();

            if (!$product) {
                $errors[] = "Product ID {$item['product_id']} not found or inactive.";
                continue;
            }

            if (!$product->hasAvailableStock((int) $item['quantity'])) {
                $errors[] = "'{$product->name}': requested {$item['quantity']} "
                          . "but only {$product->available_stock} available "
                          . "(stock: {$product->stock_quantity}, reserved: {$product->reserved_stock}).";
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                "Stock validation failed:\n" . implode("\n", $errors)
            );
        }
    }

    // ── POS Order ─────────────────────────────────────────────────────────────

    /**
     * Create a walk-in POS order.
     * Stock is deducted immediately on creation.
     * Order is marked completed.
     * Payment is recorded immediately (full or partial).
     */
    public function createPosOrder(array $data, int $outletId, int $salesmanId): Order
    {
        return DB::transaction(function () use ($data, $outletId, $salesmanId) {

            // 1. Validate stock
            $this->validateStock($data['items'], $outletId);

            // 2. Build financials
            $financials = $this->calculateFinancials(
                $data['items'],
                $data['discount_amount'] ?? 0,
                $data['tax_amount'] ?? 0
            );

            // 3. Create order
            $order = Order::create([
                'outlet_id'        => $outletId,
                'salesman_id'      => $salesmanId,
                'order_number'     => Order::generateOrderNumber($outletId, 'pos'),
                'order_type'       => 'pos',
                'status'           => 'completed',
                'payment_status'   => 'unpaid',
                'customer_name'    => $data['customer_name']    ?? 'Walk-in Customer',
                'customer_phone'   => $data['customer_phone']   ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_city'    => $data['customer_city']    ?? null,
                'subtotal'         => $financials['subtotal'],
                'discount_amount'  => $financials['discount'],
                'tax_amount'       => $financials['tax'],
                'net_amount'       => $financials['net'],
                'paid_amount'      => 0,
                'balance_amount'   => $financials['net'],
                'order_date'       => now()->toDateString(),
                'notes'            => $data['notes'] ?? null,
            ]);

            // 4. Create items + deduct stock immediately
            $this->createOrderItems($order, $data['items']);
            $this->deductStock($data['items']);

            // 5. Record payment if provided
            if (!empty($data['paid_amount']) && $data['paid_amount'] > 0) {
                app(PaymentService::class)->recordPayment($order, [
                    'amount'           => $data['paid_amount'],
                    'payment_method'   => $data['payment_method']   ?? 'cash',
                    'reference_number' => $data['reference_number'] ?? null,
                    'payment_date'     => now()->toDateString(),
                    'notes'            => 'POS payment',
                    'collected_by'     => $salesmanId,
                ]);
            }

            return $order->fresh(['items', 'payments']);
        });
    }

    // ── Booking Order ──────────────────────────────────────────────────────────

    /**
     * Create a booking order.
     * Stock is reserved but NOT deducted yet.
     * Stock is deducted only when dispatched.
     */
    public function createBookingOrder(array $data, int $outletId, int $salesmanId): Order
    {
        return DB::transaction(function () use ($data, $outletId, $salesmanId) {

            // 1. Validate stock availability
            $this->validateStock($data['items'], $outletId);

            // 2. Build financials
            $financials = $this->calculateFinancials(
                $data['items'],
                $data['discount_amount'] ?? 0,
                $data['tax_amount'] ?? 0
            );

            // 3. Create order
            $order = Order::create([
                'outlet_id'              => $outletId,
                'salesman_id'            => $salesmanId,
                'order_number'           => Order::generateOrderNumber($outletId, 'booking'),
                'order_type'             => 'booking',
                'status'                 => 'pending',
                'payment_status'         => 'unpaid',
                'customer_name'          => $data['customer_name'],
                'customer_phone'         => $data['customer_phone']   ?? null,
                'customer_address'       => $data['customer_address'] ?? null,
                'customer_city'          => $data['customer_city']    ?? null,
                'subtotal'               => $financials['subtotal'],
                'discount_amount'        => $financials['discount'],
                'tax_amount'             => $financials['tax'],
                'net_amount'             => $financials['net'],
                'paid_amount'            => 0,
                'balance_amount'         => $financials['net'],
                'order_date'             => now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ]);

            // 4. Create items + reserve stock (no deduction yet)
            $this->createOrderItems($order, $data['items']);
            $this->reserveStock($data['items']);

            // 5. Record advance payment if provided
            if (!empty($data['paid_amount']) && $data['paid_amount'] > 0) {
                app(PaymentService::class)->recordPayment($order, [
                    'amount'           => $data['paid_amount'],
                    'payment_method'   => $data['payment_method']   ?? 'cash',
                    'reference_number' => $data['reference_number'] ?? null,
                    'payment_date'     => now()->toDateString(),
                    'notes'            => 'Advance payment',
                    'collected_by'     => $salesmanId,
                ]);
            }

            return $order->fresh(['items', 'payments']);
        });
    }

    // ── Status Transitions ─────────────────────────────────────────────────────

    /**
     * Confirm a pending booking order.
     */
    public function confirmOrder(Order $order): Order
    {
        if (!$order->isPending()) {
            throw new \Exception('Only pending orders can be confirmed.');
        }

        if (!$order->isBooking()) {
            throw new \Exception('Only booking orders need confirmation.');
        }

        $order->update(['status' => 'confirmed']);

        return $order->fresh();
    }

    /**
     * Dispatch a confirmed booking order.
     * Stock is physically deducted here and reservation is released.
     */
    public function dispatchOrder(Order $order): Order
    {
        if (!$order->canBeDispatched()) {
            throw new \Exception('Only confirmed orders can be dispatched.');
        }

        return DB::transaction(function () use ($order) {

            // Release reservation and deduct actual stock
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)
                       ->decrement('stock_quantity', $item->quantity);

                Product::where('id', $item->product_id)
                       ->decrement('reserved_stock', $item->quantity);
            }

            $order->update(['status' => 'dispatched']);

            return $order->fresh();
        });
    }

    /**
     * Mark a dispatched order as delivered.
     */
    public function deliverOrder(Order $order, ?string $deliveredDate = null): Order
    {
        if (!$order->canBeDelivered()) {
            throw new \Exception('Only dispatched orders can be marked as delivered.');
        }

        $order->update([
            'status'         => 'delivered',
            'delivered_date' => $deliveredDate ?? now()->toDateString(),
        ]);

        return $order->fresh();
    }

    /**
     * Cancel an order.
     * Restores stock depending on current status.
     */
    public function cancelOrder(Order $order, string $reason, int $cancelledBy): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception(
                'Only pending or confirmed orders can be cancelled. '
              . 'This order is currently ' . $order->status . '.'
            );
        }

        return DB::transaction(function () use ($order, $reason, $cancelledBy) {

            foreach ($order->items as $item) {
                if ($order->isBooking()) {
                    // Release reserved stock
                    Product::where('id', $item->product_id)
                           ->decrement('reserved_stock', $item->quantity);
                }

                if ($order->isPos()) {
                    // Restore deducted stock
                    Product::where('id', $item->product_id)
                           ->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update([
                'status'           => 'cancelled',
                'cancelled_reason' => $reason,
                'cancelled_by'     => $cancelledBy,
            ]);

            return $order->fresh();
        });
    }

    // ── Authorization ──────────────────────────────────────────────────────────

    /**
     * Ensure the actor belongs to the same outlet as the order.
     */
    public function authorize(User $actor, Order $order): void
    {
        if ($order->outlet_id !== $actor->outlet_id) {
            throw new \Exception('You do not have access to this order.');
        }
    }

    // ── Private Helpers ────────────────────────────────────────────────────────

    /**
     * Calculate subtotal, discount, tax, and net amount.
     */
    private function calculateFinancials(
        array $items,
        float $discountAmount = 0,
        float $taxAmount = 0
    ): array {
        $subtotal = 0;

        foreach ($items as $item) {
            $product   = Product::findOrFail($item['product_id']);
            $unitPrice = $item['unit_price'] ?? $product->sale_price;
            $discount  = $item['item_discount'] ?? 0;
            $lineTotal = ($unitPrice - $discount) * $item['quantity'];
            $subtotal += $lineTotal;
        }

        $net = $subtotal - $discountAmount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discountAmount, 2),
            'tax'      => round($taxAmount, 2),
            'net'      => round(max(0, $net), 2),
        ];
    }

    /**
     * Persist order items — snapshot product name/SKU at time of order.
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product   = Product::findOrFail($item['product_id']);
            $unitPrice = $item['unit_price'] ?? (float) $product->sale_price;
            $discount  = $item['item_discount'] ?? 0;
            $total     = ($unitPrice - $discount) * $item['quantity'];

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'product_sku'  => $product->sku,
                'quantity'     => $item['quantity'],
                'unit_price'   => $unitPrice,
                'discount'     => $discount,
                'total_price'  => round($total, 2),
            ]);
        }
    }

    /**
     * Deduct stock immediately (used for POS orders).
     */
    private function deductStock(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])
                   ->decrement('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Reserve stock without deducting (used for booking orders).
     * reserved_stock increases; stock_quantity stays the same.
     * available_stock = stock_quantity - reserved_stock decreases.
     */
    private function reserveStock(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])
                   ->increment('reserved_stock', $item['quantity']);
        }
    }
}