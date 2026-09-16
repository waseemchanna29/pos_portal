<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id',
        'product_name', 'product_sku',
        'quantity', 'unit_price', 'discount', 'total_price',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getFormattedTotalAttribute(): string
    {
        return 'PKR ' . number_format($this->total_price, 2);
    }

    public function getEffectiveUnitPriceAttribute(): float
    {
        return (float) $this->unit_price - (float) $this->discount;
    }
}