<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'category_id', 'created_by', 'name', 'sku',
        'description', 'purchase_price', 'sale_price',
        'stock_quantity', 'unit', 'is_active'
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
        if ($this->stock_quantity <= 0)  return 'out_of_stock';
        if ($this->stock_quantity <= 10) return 'low_stock';
        return 'in_stock';
    }
}