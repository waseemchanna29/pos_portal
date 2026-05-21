<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    public function index()
    {
        $outlets  = Outlet::orderBy('name')->get();
        $outletId = request('outlet_id');

        $products = $outletId
            ? $this->service->list((int) $outletId, request()->only('search', 'category_id'))
            : Product::with(['category', 'outlet'])->latest()->paginate(15);

        $categories = $outletId
            ? Category::where('outlet_id', $outletId)->orderBy('name')->get()
            : collect();

        return view('superadmin.products.index', compact('products', 'outlets', 'outletId', 'categories'));
    }

    public function create()
    {
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.products.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->create($validated, (int) $validated['outlet_id'], auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.products.index')
                         ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'outlet', 'createdBy');
        return view('superadmin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $outlets    = Outlet::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('outlet_id', $product->outlet_id)
                               ->where('is_active', true)
                               ->orderBy('name')
                               ->get();

        return view('superadmin.products.edit', compact('product', 'outlets', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->update($product, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        try {
            $this->service->delete($product);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('superadmin.products.index')
                         ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product = $this->service->toggleStatus($product);
        $label   = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$label} successfully.");
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'outlet_id'      => ['required', 'exists:outlets,id'],
            'name'           => ['required', 'string', 'max:200'],
            'category_id'    => ['required', 'exists:categories,id'],
            'sku'            => ['required', 'string', 'max:100', 'unique:products,sku,' . ($ignoreId ?? 'NULL')],
            'description'    => ['nullable', 'string', 'max:1000'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit'           => ['required', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ]);
    }
}