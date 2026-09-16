<?php

namespace App\Http\Controllers\Salesman;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected OrderService   $orderService,
        protected PaymentService $paymentService
    ) {}

    public function index()
    {
        $orders = Order::where('outlet_id', auth()->user()->outlet_id)
                       ->where('order_type', 'booking')
                       ->with('salesman')
                       ->latest()
                       ->paginate(15);

        return view('salesman.bookings.index', compact('orders'));
    }

    public function create()
    {
        $outletId = auth()->user()->outlet_id;

        $categories = Category::where('outlet_id', $outletId)
                               ->where('is_active', true)
                               ->orderBy('name')
                               ->get();

        $products = Product::where('outlet_id', $outletId)
                           ->where('is_active', true)
                           ->with('category')
                           ->orderBy('name')
                           ->get()
                           ->map(function ($p) {
                               return [
                                   'id'              => $p->id,
                                   'name'            => $p->name,
                                   'sku'             => $p->sku,
                                   'sale_price'      => (float) $p->sale_price,
                                   'available_stock' => $p->available_stock,
                                   'unit'            => $p->unit,
                                   'stock_status'    => $p->stock_status,
                                   'category_id'     => $p->category_id,
                                   'category_name'   => $p->category->name ?? '',
                               ];
                           });

        return view('salesman.bookings.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'          => ['required', 'string', 'max:150'],
            'customer_phone'         => ['required', 'string', 'max:20'],
            'customer_address'       => ['nullable', 'string', 'max:300'],
            'customer_city'          => ['required', 'string', 'max:100'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'tax_amount'             => ['nullable', 'numeric', 'min:0'],
            'paid_amount'            => ['nullable', 'numeric', 'min:0'],
            'payment_method'         => ['nullable', 'in:cash,bank_transfer,cheque,easypaisa,jazzcash'],
            'reference_number'       => ['nullable', 'string', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.item_discount'  => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->orderService->createBookingOrder(
                $request->all(),
                auth()->user()->outlet_id,
                auth()->id()
            );
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        return redirect()
            ->route('salesman.bookings.show', $order)
            ->with('success', 'Booking order created successfully.');
    }

    public function show(Order $order)
    {
        if ($order->outlet_id !== auth()->user()->outlet_id) {
            abort(403);
        }

        $order->load('items', 'payments.collectedBy', 'salesman');
        $paymentSummary = $this->paymentService->getSummary($order);

        return view('salesman.bookings.show', compact('order', 'paymentSummary'));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->outlet_id !== auth()->user()->outlet_id) {
            abort(403);
        }

        $request->validate([
            'cancelled_reason' => ['required', 'string', 'max:300'],
        ]);

        try {
            $this->orderService->cancelOrder(
                $order,
                $request->cancelled_reason,
                auth()->id()
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('salesman.bookings.index')
            ->with('success', 'Booking cancelled and stock released.');
    }
}