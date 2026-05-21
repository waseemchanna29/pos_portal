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

    public function index()
    {
        $users = $this->service->list([
            'roles'  => ['admin', 'salesman'],
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
        $validated = $this->validateUser($request);

        try {
            $this->service->create($validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

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
        $validated = $this->validateUser($request, $user->id);

        try {
            $this->service->authorize(auth()->user(), $user);
            $this->service->update($user, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User updated successfully.');
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
                         ->with('success', 'User deleted successfully.');
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

        return back()->with('success', "User {$label} successfully.");
    }

    private function validateUser(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email,' . ($ignoreId ?? 'NULL')],
            'role'      => ['required', 'in:admin,salesman'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => [$ignoreId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}