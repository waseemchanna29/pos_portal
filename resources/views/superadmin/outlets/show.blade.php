@extends('layouts.app')
@section('title', 'Outlet Details')
@section('page-title', 'Outlet Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $outlet->name }}</div>
        <div class="page-header-sub">{{ $outlet->city }}, {{ $outlet->province }}</div>
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

{{-- Outlet Info --}}
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
        </div>
        <div><div class="info-item-label">Address</div><div class="info-item-value">{{ $outlet->address }}</div></div>
    </div>
</div>

{{-- Assign Admin Panel --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-user-shield"></i> Assigned Admin</div>
    </div>
    <div class="card-body">
        @if($admin)
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div class="topbar-avatar" style="width:48px; height:48px; font-size:1.1rem;">
                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600;">{{ $admin->name }}</div>
                    <div style="font-size:0.85rem; color:var(--text-muted);">{{ $admin->email }}</div>
                    @if($admin->phone)
                    <div style="font-size:0.85rem; color:var(--text-muted);">{{ $admin->phone }}</div>
                    @endif
                </div>
            </div>
            <button onclick="document.getElementById('assignAdminForm').classList.toggle('hidden-form')"
                    class="btn-outline btn btn-sm">
                <i class="fas fa-exchange-alt"></i> Change Admin
            </button>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            No admin assigned to this outlet yet.
        </div>
        @endif

        {{-- Assign Form --}}
        <div id="assignAdminForm" class="{{ $admin ? 'hidden-form' : '' }}" style="margin-top:1.2rem;">
            <form action="{{ route('superadmin.outlets.assign-admin', $outlet) }}" method="POST">
                @csrf
                <div style="display:flex; gap:0.8rem; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px; margin-bottom:0;">
                        <label class="form-label">Select Admin to Assign</label>
                        <select name="admin_id" class="form-select" id="adminSelect">
                            <option value="">— Loading admins... —</option>
                        </select>
                        <span class="form-text">Only unassigned admins are shown.</span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-bottom:0;">
                        <i class="fas fa-user-check"></i> Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Salesmen --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-users"></i> Salesmen ({{ $salesmen->count() }})
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($salesmen as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->phone ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $s->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $s->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                    No salesmen assigned yet.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="danger-zone">
    <h5><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
    <p>Deleting this outlet is permanent and cannot be undone.</p>
    <form action="{{ route('superadmin.outlets.destroy', $outlet) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this outlet?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Delete Outlet
        </button>
    </form>
</div>

<script>
// Load unassigned admins via AJAX when page loads
fetch('{{ route('superadmin.available-admins') }}')
    .then(r => r.json())
    .then(data => {
        const sel = document.getElementById('adminSelect');
        sel.innerHTML = '<option value="">— Select Admin —</option>';
        if (data.length === 0) {
            sel.innerHTML = '<option value="">No unassigned admins available</option>';
            return;
        }
        data.forEach(a => {
            sel.innerHTML += `<option value="${a.id}">${a.name} (${a.email})</option>`;
        });
    });
</script>
@endsection