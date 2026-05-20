@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">All Users</div>
        <div class="page-header-sub">Manage admins and salesmen</div>
    </div>
    <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add User
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Outlet</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $users->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-{{ $user->role }}">{{ $user->role_label }}</span></td>
                    <td>{{ $user->outlet->name ?? '—' }}</td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.users.toggle', $user) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h4>No users yet</h4>
                            <p>Create your first admin or salesman account.</p>
                            <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add User
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection