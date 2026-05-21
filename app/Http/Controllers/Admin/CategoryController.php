<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $service) {}

    public function index()
    {
        $outletId   = auth()->user()->outlet_id;
        $categories = $this->service->list($outletId, request()->only('search', 'is_active'));

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        try {
            $outletId = $this->service->resolveOutletId(auth()->user());
            $validated['is_active'] = $request->boolean('is_active', true);
            $this->service->create($validated, $outletId, auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $this->authorize($category);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize($category);

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

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->authorize($category);

        try {
            $this->service->delete($category);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $this->authorize($category);
        $category = $this->service->toggleStatus($category);
        $label    = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$label} successfully.");
    }

    private function authorize(Category $category): void
    {
        try {
            $this->service->authorize(auth()->user(), $category);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }
}