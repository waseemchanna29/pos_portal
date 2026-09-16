<?php
// Project path: app/Models/ShopModule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopModule extends Model
{
    /**
     * The full set of toggleable module keys. Single source of truth —
     * ModuleAccessService and seeders both read from this.
     */
    public const MODULES = [
        'customers',
        'suppliers',
        'purchase_orders',
        'invoices',
        'bookings',
        'products',
        'inventory',
        'profit_loss',
        'pos',
    ];

    protected $fillable = [
        'outlet_id',
        'module_key',
        'is_enabled',
        'disabled_by',
        'disabled_note',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    public function getModuleLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->module_key));
    }
}