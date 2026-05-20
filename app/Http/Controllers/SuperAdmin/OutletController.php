<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::with('createdBy')
                         ->withCount('users')
                         ->latest()
                         ->paginate(15);

        return view('superadmin.outlets.index', compact('outlets'));
    }

    public function create()
    {
        $admins = User::where('role', 'admin')->whereNull('outlet_id')->orWhere('role', 'admin')->get();
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
        $outlet->load('users', 'createdBy');
        $admin = $outlet->users()->where('role', 'admin')->first();
        return view('superadmin.outlets.show', compact('outlet', 'admin'));
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
}