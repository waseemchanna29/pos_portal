<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('outlet')
                     ->whereIn('role', ['admin', 'salesman'])
                     ->latest()
                     ->paginate(15);

        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.users.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'in:admin,salesman'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'phone'     => ['nullable', 'string', 'max:20'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.users.edit', compact('user', 'outlets'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'      => ['required', 'in:admin,salesman'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot delete Super Admin account.');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $label = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$label} successfully.");
    }
}