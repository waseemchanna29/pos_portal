<?php
// Project path: app/Http/Controllers/SuperAdmin/OutletController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\ShopModule;
use App\Models\User;
use App\Services\ModuleAccessService;
use App\Services\SubscriptionService;
use App\Services\UserService;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(
        protected UserService         $userService,
        protected SubscriptionService $subscriptionService,
        protected ModuleAccessService $moduleService,
    ) {}

    public function index()
    {
        $outlets = Outlet::withCount('users')
                         ->with('admin')
                         ->latest()
                         ->paginate(15);

        return view('superadmin.outlets.index', compact('outlets'));
    }

    public function create()
    {
        return view('superadmin.outlets.create');
    }

    /**
     * Creates the shop owner: the Outlet AND its Admin user AND its
     * subscription, in one step. Replaces the old "create outlet, assign
     * admin later" two-step flow — a shop owner without a subscription plan
     * and an admin login isn't a usable account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150', 'unique:outlets,name'],
            'address'  => ['required', 'string', 'max:255'],
            'city'     => ['required', 'string', 'max:100'],
            'province' => ['required', 'string'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'ntn'      => ['nullable', 'string', 'max:30'],
            'strn'     => ['nullable', 'string', 'max:30'],

            'admin_name'     => ['required', 'string', 'max:100'],
            'admin_email'    => ['required', 'email', 'unique:users,email'],
            'admin_phone'    => ['nullable', 'string', 'max:20'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],

            'plan_type'      => ['required', 'in:trial,paid'],
            'trial_ends_at'  => ['nullable', 'date', 'after:today', 'required_if:plan_type,trial'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'max_salesmen'   => ['nullable', 'integer', 'min:0'],
            'max_bookers'    => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $outlet = $this->subscriptionService->createShopOwner(
                outletData: collect($validated)->only(['name', 'address', 'city', 'province', 'phone', 'ntn', 'strn'])->all(),
                adminData: [
                    'name'     => $validated['admin_name'],
                    'email'    => $validated['admin_email'],
                    'phone'    => $validated['admin_phone'] ?? null,
                    'password' => $validated['admin_password'],
                ],
                planData: collect($validated)->only(['plan_type', 'trial_ends_at', 'monthly_amount', 'max_salesmen', 'max_bookers'])->all(),
                performedBy: auth()->id(),
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.outlets.show', $outlet)
                         ->with('success', 'Shop owner account created successfully.');
    }

    public function show(Outlet $outlet)
    {
        $outlet->load('createdBy', 'modules', 'subscriptionLogs.performedBy');
        $admin    = $outlet->users()->where('role', 'admin')->first();
        $salesmen = $outlet->users()->where('role', 'salesman')->get();
        $bookers  = $outlet->users()->where('role', 'booker')->get();

        return view('superadmin.outlets.show', compact('outlet', 'admin', 'salesmen', 'bookers'));
    }

    public function edit(Outlet $outlet)
    {
        return view('superadmin.outlets.edit', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150', 'unique:outlets,name,' . $outlet->id],
            'address'  => ['required', 'string', 'max:255'],
            'city'     => ['required', 'string', 'max:100'],
            'province' => ['required', 'string'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'ntn'      => ['nullable', 'string', 'max:30'],
            'strn'     => ['nullable', 'string', 'max:30'],
        ]);

        $outlet->update($validated);

        return redirect()->route('superadmin.outlets.index')
                         ->with('success', 'Outlet updated successfully.');
    }

    /**
     * Soft delete only — reachable, but not something termination does
     * automatically. Data is preserved either way.
     */
    public function destroy(Outlet $outlet)
    {
        if ($outlet->users()->count() > 0) {
            return back()->with('error', 'Cannot delete outlet with assigned users. Remove users first.');
        }

        $outlet->delete();

        return redirect()->route('superadmin.outlets.index')
                         ->with('success', 'Outlet deleted successfully.');
    }

    public function toggleStatus(Outlet $outlet)
    {
        $outlet->update(['is_active' => !$outlet->is_active]);
        $label = $outlet->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Outlet {$label} successfully.");
    }

    // ── Subscription actions ─────────────────────────────────────────────────

    public function markPaid(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $this->subscriptionService->markPaid($outlet, auth()->id(), $validated['note'] ?? null);

        return back()->with('success', 'Shop owner marked as paid.');
    }

    public function changePlan(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'plan_type'      => ['required', 'in:trial,paid'],
            'trial_ends_at'  => ['nullable', 'date', 'after:today', 'required_if:plan_type,trial'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'max_salesmen'   => ['nullable', 'integer', 'min:0'],
            'max_bookers'    => ['nullable', 'integer', 'min:0'],
            'note'           => ['nullable', 'string', 'max:300'],
        ]);

        $this->subscriptionService->changePlan($outlet, $validated, auth()->id());

        return back()->with('success', 'Subscription plan updated.');
    }

    /**
     * Terminate requires a note (enforced by validation) and never deletes
     * data — only blocks login via EnsureSubscriptionActive.
     */
    public function terminate(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:300'],
        ]);

        $this->subscriptionService->terminate($outlet, $validated['note'], auth()->id());

        return back()->with('success', 'Subscription terminated. Shop owner access is blocked; all data is retained.');
    }

    public function reactivate(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $this->subscriptionService->reactivate($outlet, auth()->id(), $validated['note'] ?? null);

        return back()->with('success', 'Subscription reactivated.');
    }

    // ── Module toggles ────────────────────────────────────────────────────────

    public function toggleModule(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'module_key' => ['required', 'in:' . implode(',', ShopModule::MODULES)],
            'is_enabled' => ['required', 'boolean'],
            'note'       => ['nullable', 'string', 'max:300', 'required_if:is_enabled,false'],
        ]);

        $this->moduleService->toggle(
            $outlet,
            $validated['module_key'],
            $validated['is_enabled'],
            auth()->id(),
            $validated['note'] ?? null,
        );

        return back()->with('success', 'Module setting updated.');
    }

    // ── Assign / unassign admin (kept from previous version) ───────────────────

    public function assignAdmin(Request $request, Outlet $outlet)
    {
        $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
        ]);

        $newAdmin = User::findOrFail($request->admin_id);

        if ($newAdmin->role !== 'admin') {
            return back()->with('error', 'Selected user is not an admin.');
        }

        User::where('outlet_id', $outlet->id)
            ->where('role', 'admin')
            ->update(['outlet_id' => null]);

        $newAdmin->update(['outlet_id' => $outlet->id]);

        return back()->with('success', "Admin {$newAdmin->name} assigned to {$outlet->name} successfully.");
    }

    public function availableAdmins()
    {
        $admins = User::where('role', 'admin')
                      ->whereNull('outlet_id')
                      ->orderBy('name')
                      ->get(['id', 'name', 'email']);

        return response()->json($admins);
    }
}