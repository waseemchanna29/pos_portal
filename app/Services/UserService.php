<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * List users filtered by role and optionally by outlet.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('outlet')->latest();

        if (!empty($filters['roles'])) {
            $query->whereIn('role', $filters['roles']);
        }

        if (!empty($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'outlet_id' => $data['outlet_id'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'] ?? $user->role,
            'outlet_id' => $data['outlet_id'] ?? $user->outlet_id,
            'phone'     => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user->fresh();
    }

    /**
     * Delete a user with safety checks.
     */
    public function delete(User $user): void
    {
        if ($user->isSuperAdmin()) {
            throw new \Exception('Super Admin account cannot be deleted.');
        }

        $user->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh();
    }

    /**
     * Ensure caller has permission over the target user.
     * Admins can only manage salesmen in their own outlet.
     * SuperAdmins can manage anyone except other superadmins.
     */
    public function authorize(User $actor, User $target): void
    {
        if ($actor->isSuperAdmin()) {
            if ($target->isSuperAdmin() && $actor->id !== $target->id) {
                throw new \Exception('You cannot manage another Super Admin account.');
            }
            return;
        }

        if ($actor->isAdmin()) {
            if ($target->role !== 'salesman') {
                throw new \Exception('Admins can only manage salesman accounts.');
            }
            if ($target->outlet_id !== $actor->outlet_id) {
                throw new \Exception('You can only manage salesmen within your outlet.');
            }
            return;
        }

        throw new \Exception('You do not have permission to manage users.');
    }
}