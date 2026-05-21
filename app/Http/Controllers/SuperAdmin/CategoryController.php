<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Outlet;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $service) {}

    public function index()
    {
        $outlets    = Outlet::orderBy('name')->get();
        $outletId   = request('outlet_id');

        $categories = $outletId
            ? $this->service->list((int) $outletId, request()->only('search'))
            : Category::with('outlet')->withCount('products')->latest()->paginate(15);

        return view('superadmin.categories.index', compact('categories', 'outlets', 'outletId'));
    }

    public function create()
    {
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.categories.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id'   => ['required', 'exists:outlets,id'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->create($validated, (int) $validated['outlet_id'], auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.categories.index')
                         ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.categories.edit', compact('category', 'outlets'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->update($category, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        try {
            $this->service->delete($category);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('superadmin.categories.index')
                         ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category = $this->service->toggleStatus($category);
        $label    = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$label} successfully.");
    }
}