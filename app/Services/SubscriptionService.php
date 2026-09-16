<?php
// Project path: app/Services/SubscriptionService.php

namespace App\Services;

use App\Models\Outlet;
use App\Models\ShopModule;
use App\Models\SubscriptionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SubscriptionService
{
    /**
     * Create a shop owner: an Outlet plus its Admin user, in one transaction,
     * with subscription fields set and every module defaulted to enabled.
     *
     * @param array $outletData    name, address, city, province, phone, ntn, strn
     * @param array $adminData     name, email, password, phone
     * @param array $planData      plan_type (trial|paid), trial_ends_at, monthly_amount,
     *                              max_salesmen, max_bookers
     */
    public function createShopOwner(array $outletData, array $adminData, array $planData, int $performedBy): Outlet
    {
        return DB::transaction(function () use ($outletData, $adminData, $planData, $performedBy) {
            $outlet = Outlet::create([
                ...$outletData,
                'is_active'           => true,
                'created_by'          => $performedBy,
                'plan_type'           => $planData['plan_type'],
                'trial_ends_at'       => $planData['plan_type'] === 'trial' ? ($planData['trial_ends_at'] ?? now()->addDays(14)) : null,
                'subscription_status' => 'active',
                'monthly_amount'      => $planData['monthly_amount'] ?? null,
                'max_salesmen'        => $planData['max_salesmen'] ?? null,
                'max_bookers'         => $planData['max_bookers'] ?? null,
            ]);

            User::create([
                'name'      => $adminData['name'],
                'email'     => $adminData['email'],
                'password'  => Hash::make($adminData['password']),
                'phone'     => $adminData['phone'] ?? null,
                'role'      => 'admin',
                'outlet_id' => $outlet->id,
                'is_active' => true,
            ]);

            $this->initializeModules($outlet);

            $this->log($outlet, 'created', $performedBy, 'Shop owner account created.', [
                'plan_type' => $planData['plan_type'],
            ]);

            if ($planData['plan_type'] === 'trial') {
                $this->log($outlet, 'trial_started', $performedBy, null, [
                    'trial_ends_at' => (string) $outlet->trial_ends_at,
                ]);
            }

            return $outlet;
        });
    }

    /**
     * Every module defaults to enabled for a new outlet.
     */
    public function initializeModules(Outlet $outlet): void
    {
        foreach (ShopModule::MODULES as $key) {
            ShopModule::firstOrCreate([
                'outlet_id'  => $outlet->id,
                'module_key' => $key,
            ], [
                'is_enabled' => true,
            ]);
        }
    }

    public function markPaid(Outlet $outlet, int $performedBy, ?string $note = null): Outlet
    {
        $outlet->update([
            'last_marked_paid_at' => now()->toDateString(),
            'marked_paid_by'      => $performedBy,
            'subscription_status' => 'active',
        ]);

        $this->log($outlet, 'marked_paid', $performedBy, $note);

        return $outlet->fresh();
    }

    public function changePlan(Outlet $outlet, array $planData, int $performedBy): Outlet
    {
        $before = $outlet->only(['plan_type', 'trial_ends_at', 'monthly_amount', 'max_salesmen', 'max_bookers']);

        $outlet->update([
            'plan_type'      => $planData['plan_type'],
            'trial_ends_at'  => $planData['plan_type'] === 'trial' ? ($planData['trial_ends_at'] ?? now()->addDays(14)) : null,
            'monthly_amount' => $planData['monthly_amount'] ?? $outlet->monthly_amount,
            'max_salesmen'   => $planData['max_salesmen'] ?? $outlet->max_salesmen,
            'max_bookers'    => $planData['max_bookers'] ?? $outlet->max_bookers,
        ]);

        $this->log($outlet, 'plan_changed', $performedBy, $planData['note'] ?? null, [
            'before' => $before,
            'after'  => $outlet->only(['plan_type', 'trial_ends_at', 'monthly_amount', 'max_salesmen', 'max_bookers']),
        ]);

        return $outlet->fresh();
    }

    /**
     * Terminate: blocks access via subscription_status only. Never deletes
     * the outlet or any related data — soft delete is a separate, explicit
     * action, not something termination does automatically.
     */
    public function terminate(Outlet $outlet, string $note, int $performedBy): Outlet
    {
        $outlet->update([
            'subscription_status' => 'terminated',
            'terminated_at'       => now(),
            'terminated_by'       => $performedBy,
            'termination_note'    => $note,
        ]);

        $this->log($outlet, 'terminated', $performedBy, $note);

        return $outlet->fresh();
    }

    public function reactivate(Outlet $outlet, int $performedBy, ?string $note = null): Outlet
    {
        $outlet->update([
            'subscription_status' => 'active',
            'terminated_at'       => null,
            'terminated_by'       => null,
            'termination_note'    => null,
        ]);

        $this->log($outlet, 'reactivated', $performedBy, $note);

        return $outlet->fresh();
    }

    /**
     * Called by a scheduled command to flip trial outlets whose trial date
     * has passed into 'expired' — hard lockout then applies automatically
     * via Outlet::hasActiveSubscription().
     */
    public function expireOverdueTrials(): int
    {
        return Outlet::where('plan_type', 'trial')
            ->where('subscription_status', 'active')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now()->toDateString())
            ->update(['subscription_status' => 'expired']);
    }

    private function log(Outlet $outlet, string $action, int $performedBy, ?string $note = null, ?array $meta = null): void
    {
        SubscriptionLog::create([
            'outlet_id'    => $outlet->id,
            'action'       => $action,
            'performed_by' => $performedBy,
            'note'         => $note,
            'meta'         => $meta,
        ]);
    }
}