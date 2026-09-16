@extends('layouts.app')
@section('title', 'Super Admin Dashboard')
@section('page-title', 'Super Admin Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-store"></i></div>
        <div>
            <div class="stat-value">{{ $stats['total_outlets'] }}</div>
            <div class="stat-label">Total Outlets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $stats['active_outlets'] }}</div>
            <div class="stat-label">Active Outlets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon accent"><i class="fas fa-user-tie"></i></div>
        <div>
            <div class="stat-value">{{ $stats['total_admins'] }}</div>
            <div class="stat-label">Total Admins</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-user-clock"></i></div>
        <div>
            <div class="stat-value">{{ $stats['unassigned_admins'] }}</div>
            <div class="stat-label">Unassigned Admins</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-store"></i> Recent Outlets</div>
        <a href="{{ route('superadmin.outlets.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Outlet
        </a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Outlet</th>
                    <th>City</th>
                    <th>Assigned Admin</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOutlets as $outlet)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $outlet->name }}</strong></td>
                    <td>{{ $outlet->city }}</td>
                    <td>
                        @if($outlet->admin)
                            <span class="badge badge-admin">{{ $outlet->admin->name }}</span>
                        @else
                            <span style="color:var(--text-muted); font-size:0.85rem;">— Not Assigned —</span>
                        @endif
                    </td>
                    <td>{{ $outlet->users_count }}</td>
                    <td>
                        <span class="badge {{ $outlet->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $outlet->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.outlets.show', $outlet) }}" class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">No outlets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection