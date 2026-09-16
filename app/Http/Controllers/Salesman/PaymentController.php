<?php

namespace App\Http\Controllers\Salesman;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function store(Request $request, Order $order)
    {
        // Scope to own outlet
        if ($order->outlet_id !== auth()->user()->outlet_id) {
            abort(403);
        }

        $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_method'   => ['required', 'in:cash,bank_transfer,cheque,easypaisa,jazzcash'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'payment_date'     => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $this->paymentService->validate($request->all(), $order);

            $this->paymentService->recordPayment($order, [
                ...$request->all(),
                'collected_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded successfully.');
    }
}