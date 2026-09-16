<?php
// Project path: app/Services/ModuleAccessService.php

namespace App\Services;

use App\Models\Outlet;
use App\Models\ShopModule;
use App\Models\SubscriptionLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleAccessService
{
    private const CACHE_TTL = 300; // seconds — module toggles change rarely

    /**
     * True if the given module is enabled for this outlet. Every controller/
     * service that gates a module (Customers, Suppliers, Purchase Orders,
     * Invoices, Bookings, Products, Inventory, Profit & Loss, POS) should
     * call this instead of checking ShopModule directly, so caching and the
     * "module not found = enabled by default" rule stay in one place.
     */
    public function isEnabled(Outlet $outlet, string $moduleKey): bool
    {
        return $this->modulesFor($outlet)->get($moduleKey, true);
    }

    public function modulesFor(Outlet $outlet): Collection
    {
        return Cache::remember(
            "outlet:{$outlet->id}:modules",
            self::CACHE_TTL,
            fn () => $outlet->modules()->pluck('is_enabled', 'module_key')
        );
    }

    public function toggle(Outlet $outlet, string $moduleKey, bool $enabled, int $performedBy, ?string $note = null): ShopModule
    {
        $module = ShopModule::updateOrCreate(
            ['outlet_id' => $outlet->id, 'module_key' => $moduleKey],
            [
                'is_enabled'    => $enabled,
                'disabled_by'   => $enabled ? null : $performedBy,
                'disabled_note' => $enabled ? null : $note,
            ]
        );

        Cache::forget("outlet:{$outlet->id}:modules");

        SubscriptionLog::create([
            'outlet_id'    => $outlet->id,
            'action'       => 'module_toggled',
            'performed_by' => $performedBy,
            'note'         => $note,
            'meta'         => ['module_key' => $moduleKey, 'enabled' => $enabled],
        ]);

        return $module;
    }
}