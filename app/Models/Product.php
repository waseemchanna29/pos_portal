<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'created_by',
        'name',
        'sku',
        'description',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'reserved_stock',
        'unit',
        'is_active'
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedSalePriceAttribute(): string
    {
        return 'PKR ' . number_format($this->sale_price, 2);
    }

    public function getFormattedPurchasePriceAttribute(): string
    {
        return 'PKR ' . number_format($this->purchase_price, 2);
    }

    public function getStockStatusAttribute(): string
    {
        $available = $this->available_stock;
        if ($available <= 0)  return 'out_of_stock';
        if ($available <= 10) return 'low_stock';
        return 'in_stock';
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Stock physically on shelf minus what is reserved for bookings.
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_stock);
    }

    /**
     * Is there enough available stock for a given quantity?
     */
    public function hasAvailableStock(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }
}
