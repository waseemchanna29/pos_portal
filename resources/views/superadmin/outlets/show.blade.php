@extends('layouts.app')
@section('title', 'Outlet Details')
@section('page-title', 'Outlet Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $outlet->name }}</div>
        <div class="page-header-sub">Outlet Details</div>
    </div>
    <div style="display:flex; gap:0.6rem;">
        <a href="{{ route('superadmin.outlets.edit', $outlet) }}" class="btn btn-accent">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('superadmin.outlets.index') }}" class="btn-outline btn">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-info-circle"></i> Outlet Information</div>
        <span class="badge {{ $outlet->is_active ? 'badge-active' : 'badge-inactive' }}">
            {{ $outlet->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="info-item-label">Outlet Name</div><div class="info-item-value">{{ $outlet->name }}</div></div>
            <div><div class="info-item-label">City</div><div class="info-item-value">{{ $outlet->city }}</div></div>
            <div><div class="info-item-label">Province</div><div class="info-item-value">{{ $outlet->province }}</div></div>
            <div><div class="info-item-label">Phone</div><div class="info-item-value">{{ $outlet->phone ?? '—' }}</div></div>
            <div><div class="info-item-label">NTN</div><div class="info-item-value">{{ $outlet->ntn ?? '—' }}</div></div>
            <div><div class="info-item-label">STRN</div><div class="info-item-value">{{ $outlet->strn ?? '—' }}</div></div>
            <div><div class="info-item-label">Created By</div><div class="info-item-value">{{ $outlet->createdBy->name ?? '—' }}</div></div>
            <div><div class="info-item-label">Created At</div><div class="info-item-value">{{ $outlet->created_at->format('d M Y') }}</div></div>
        </div>
        <div class="form-group">
            <div class="info-item-label">Full Address</div>
            <div class="info-item-value">{{ $outlet->address }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-users"></i> Assigned Users ({{ $outlet->users->count() }})</div>
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($outlet->users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-{{ $user->role }}">{{ $user->role_label }}</span></td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No users assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="danger-zone">
    <h5><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
    <p>Deleting this outlet will permanently remove it. This action cannot be undone.</p>
    <form action="{{ route('superadmin.outlets.destroy', $outlet) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this outlet?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Delete Outlet
        </button>
    </form>
</div>
@endsection