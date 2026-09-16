<?php

namespace App\Http\Controllers\Salesman;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    /**
     * POS screen — loads all active products for the outlet.
     */
    public function index()
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

        return view('salesman.pos.index', compact('categories', 'products'));
    }

    /**
     * Submit POS order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'          => ['nullable', 'string', 'max:150'],
            'customer_phone'         => ['nullable', 'string', 'max:20'],
            'customer_city'          => ['nullable', 'string', 'max:100'],
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
            $order = $this->orderService->createPosOrder(
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
            ->route('salesman.pos.receipt', $order)
            ->with('success', 'Order completed successfully.');
    }

    /**
     * Show receipt after a completed POS order.
     */
    public function receipt(Order $order)
    {
        // Scope to own outlet
        if ($order->outlet_id !== auth()->user()->outlet_id) {
            abort(403);
        }

        $order->load('items', 'payments.collectedBy', 'salesman');

        return view('salesman.pos.receipt', compact('order'));
    }
}