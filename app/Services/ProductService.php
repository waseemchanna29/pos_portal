<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * List products scoped to an outlet with optional filters.
     */
    public function list(int $outletId, array $filters = []): LengthAwarePaginator
    {
        $query = Product::with('category')
                        ->where('outlet_id', $outletId)
                        ->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a product under a specific outlet.
     */
    public function create(array $data, int $outletId, int $createdBy): Product
    {
        return Product::create([
            'outlet_id'      => $outletId,
            'category_id'    => $data['category_id'],
            'created_by'     => $createdBy,
            'name'           => $data['name'],
            'sku'            => $data['sku'],
            'description'    => $data['description'] ?? null,
            'purchase_price' => $data['purchase_price'],
            'sale_price'     => $data['sale_price'],
            'stock_quantity' => $data['stock_quantity'],
            'unit'           => $data['unit'],
            'is_active'      => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        $product->update([
            'category_id'    => $data['category_id'],
            'name'           => $data['name'],
            'sku'            => $data['sku'],
            'description'    => $data['description'] ?? null,
            'purchase_price' => $data['purchase_price'],
            'sale_price'     => $data['sale_price'],
            'stock_quantity' => $data['stock_quantity'],
            'unit'           => $data['unit'],
            'is_active'      => $data['is_active'] ?? true,
        ]);

        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): void
    {
        // Future: check if product is in any open orders before deleting
        $product->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Product $product): Product
    {
        $product->update(['is_active' => !$product->is_active]);
        return $product->fresh();
    }

    /**
     * Ensure the actor has access to this product's outlet.
     */
    public function authorize(User $actor, Product $product): void
    {
        $outletId = $this->resolveOutletId($actor);

        if ($product->outlet_id !== $outletId) {
            throw new \Exception('You do not have access to this product.');
        }
    }

    /**
     * Resolve which outlet the actor manages.
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
}