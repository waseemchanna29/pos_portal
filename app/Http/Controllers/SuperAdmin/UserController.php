<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $service) {}

    /**
     * SuperAdmin manages only admin-role users.
     * Salesmen are managed by their outlet's admin.
     */
    public function index()
    {
        $users = $this->service->list([
            'roles'  => ['admin'],
            'search' => request('search'),
        ]);

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
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->service->create([
                ...$validated,
                'role' => 'admin',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'Admin account created successfully.');
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
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->service->authorize(auth()->user(), $user);
            $this->service->update($user, array_merge($validated, ['role' => 'admin']));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        try {
            $this->service->authorize(auth()->user(), $user);
            $this->service->delete($user);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'Admin deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        try {
            $this->service->authorize(auth()->user(), $user);
            $user  = $this->service->toggleStatus($user);
            $label = $user->is_active ? 'activated' : 'deactivated';
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Admin {$label} successfully.");
    }
}