<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * List categories scoped to an outlet.
     */
    public function list(int $outletId, array $filters = []): LengthAwarePaginator
    {
        $query = Category::where('outlet_id', $outletId)
                         ->withCount('products')
                         ->latest();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a category under a specific outlet.
     */
    public function create(array $data, int $outletId, int $createdBy): Category
    {
        $slug = $this->generateUniqueSlug($data['name'], $outletId);

        return Category::create([
            'outlet_id'   => $outletId,
            'created_by'  => $createdBy,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): Category
    {
        $slug = $this->generateUniqueSlug($data['name'], $category->outlet_id, $category->id);

        $category->update([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        return $category->fresh();
    }

    /**
     * Delete a category with safety check.
     */
    public function delete(Category $category): void
    {
        if ($category->products()->count() > 0) {
            throw new \Exception('Cannot delete category with products. Remove or reassign products first.');
        }

        $category->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Category $category): Category
    {
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh();
    }

    /**
     * Ensure the actor has access to this category's outlet.
     */
    public function authorize(User $actor, Category $category): void
    {
        $outletId = $this->resolveOutletId($actor);

        if ($category->outlet_id !== $outletId) {
            throw new \Exception('You do not have access to this category.');
        }
    }

    /**
     * Resolve which outlet the actor manages.
     * SuperAdmin passes outlet_id explicitly; Admin uses their assigned outlet.
     */
    public function resolveOutletId(User $actor, ?int $explicitOutletId = null): int
    {
        if ($actor->isSuperAdmin()) {
            if (!$explicitOutletId) {
                throw new \Exception('Outlet must be specified.');
            }
            return $explicitOutletId;
        }

        if (!$actor->outlet_id) {
            throw new \Exception('Your account is not assigned to any outlet.');
        }

        return $actor->outlet_id;
    }

    /**
     * Generate a slug unique within the outlet.
     */
    private function generateUniqueSlug(string $name, int $outletId, ?int $ignoreId = null): string
    {
        $slug  = Str::slug($name);
        $query = Category::where('outlet_id', $outletId)->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $count = $query->count();

        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }
}