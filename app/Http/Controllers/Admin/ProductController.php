<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    public function index()
    {
        $outletId = auth()->user()->outlet_id;
        $products = $this->service->list($outletId, request()->only('search', 'category_id', 'is_active'));

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = $this->getOutletCategories();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        try {
            $outletId = $this->service->resolveOutletId(auth()->user());
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->create($validated, $outletId, auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $this->authorize($product);
        $product->load('category', 'createdBy');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorize($product);
        $categories = $this->getOutletCategories();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize($product);
        $validated = $this->validateProduct($request, $product->id);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->update($product, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize($product);

        try {
            $this->service->delete($product);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $this->authorize($product);
        $product = $this->service->toggleStatus($product);
        $label   = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$label} successfully.");
    }

    private function authorize(Product $product): void
    {
        try {
            $this->service->authorize(auth()->user(), $product);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    private function getOutletCategories()
    {
        return Category::where('outlet_id', auth()->user()->outlet_id)
                       ->where('is_active', true)
                       ->orderBy('name')
                       ->get();
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
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