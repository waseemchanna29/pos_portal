<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(protected UserService $userService) {}

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
        ]);

        $validated['created_by'] = auth()->id();

        Outlet::create($validated);

        return redirect()->route('superadmin.outlets.index')
                         ->with('success', 'Outlet created successfully.');
    }

    public function show(Outlet $outlet)
    {
        $outlet->load('createdBy');
        $admin    = $outlet->users()->where('role', 'admin')->first();
        $salesmen = $outlet->users()->where('role', 'salesman')->get();

        return view('superadmin.outlets.show', compact('outlet', 'admin', 'salesmen'));
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

    /**
     * Assign an admin user to this outlet.
     * Unassigns the previous admin first if one exists.
     */
    public function assignAdmin(Request $request, Outlet $outlet)
    {
        $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
        ]);

        $newAdmin = User::findOrFail($request->admin_id);

        if ($newAdmin->role !== 'admin') {
            return back()->with('error', 'Selected user is not an admin.');
        }

        // Unassign current admin from this outlet
        User::where('outlet_id', $outlet->id)
            ->where('role', 'admin')
            ->update(['outlet_id' => null]);

        // Assign new admin
        $newAdmin->update(['outlet_id' => $outlet->id]);

        return back()->with('success', "Admin {$newAdmin->name} assigned to {$outlet->name} successfully.");
    }

    /**
     * AJAX: return admins not yet assigned to any outlet.
     */
    public function availableAdmins()
    {
        $admins = User::where('role', 'admin')
                      ->whereNull('outlet_id')
                      ->orderBy('name')
                      ->get(['id', 'name', 'email']);

        return response()->json($admins);
    }
}