<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected PurchaseOrderService $service) {}

    public function index()
    {
        $orders = $this->service->list(
            auth()->user()->outlet_id,
            request()->only('status', 'search')
        );

        return view('admin.purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('outlet_id', auth()->user()->outlet_id)
                           ->where('is_active', true)
                           ->with('category')
                           ->orderBy('name')
                           ->get();

        return view('admin.purchase-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'         => ['required', 'string', 'max:150'],
            'supplier_phone'        => ['nullable', 'string', 'max:20'],
            'supplier_address'      => ['nullable', 'string', 'max:300'],
            'order_date'            => ['required', 'date'],
            'expected_date'         => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes'                 => ['nullable', 'string', 'max:500'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'     => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->service->create(
                $request->all(),
                auth()->user()->outlet_id,
                auth()->id()
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.purchase-orders.index')
                         ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize($purchaseOrder);
        $purchaseOrder->load('items.product', 'createdBy');

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize($purchaseOrder);

        try {
            $this->service->cancel($purchaseOrder);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.purchase-orders.index')
                         ->with('success', 'Purchase order cancelled.');
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        $this->authorize($purchaseOrder);

        try {
            $this->service->receive($purchaseOrder);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order marked as received. Stock updated.');
    }

    private function authorize(PurchaseOrder $po): void
    {
        try {
            $this->service->authorize(auth()->user(), $po);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }
}