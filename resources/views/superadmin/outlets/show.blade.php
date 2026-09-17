{{-- Project path: resources/views/superadmin/outlets/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Shop Owner Details')
@section('page-title', 'Shop Owner Details')

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
        <div style="display:flex; gap:0.5rem;">
            <span class="badge {{ $outlet->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $outlet->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="badge {{ $outlet->subscription_badge_class }}">
                {{ ucfirst($outlet->subscription_status) }} — {{ ucfirst($outlet->plan_type) }}
            </span>
        </div>
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

{{-- Subscription --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-crown"></i> Subscription</div>
    </div>
    <div class="card-body">
        <div class="info-grid" style="margin-bottom:1.2rem;">
            <div><div class="info-item-label">Plan</div><div class="info-item-value">{{ ucfirst($outlet->plan_type) }}</div></div>
            <div><div class="info-item-label">Status</div><div class="info-item-value">{{ ucfirst($outlet->subscription_status) }}</div></div>
            @if($outlet->plan_type === 'trial')
            <div><div class="info-item-label">Trial Ends</div><div class="info-item-value">{{ optional($outlet->trial_ends_at)->format('d M, Y') ?? '—' }}</div></div>
            @endif
            <div><div class="info-item-label">Monthly Amount</div><div class="info-item-value">{{ $outlet->monthly_amount ? 'PKR ' . number_format($outlet->monthly_amount, 2) : '—' }}</div></div>
            <div><div class="info-item-label">Last Marked Paid</div><div class="info-item-value">{{ optional($outlet->last_marked_paid_at)->format('d M, Y') ?? '—' }}</div></div>
            <div><div class="info-item-label">Salesman Limit</div><div class="info-item-value">{{ $outlet->salesmen_count }} / {{ $outlet->max_salesmen ?? '∞' }}</div></div>
            <div><div class="info-item-label">Booker Limit</div><div class="info-item-value">{{ $outlet->bookers_count }} / {{ $outlet->max_bookers ?? '∞' }}</div></div>
        </div>

        @if($outlet->isTerminated())
        <div class="alert alert-danger">
            <i class="fas fa-ban"></i>
            <div>
                <strong>Subscription terminated</strong>
                on {{ optional($outlet->terminated_at)->format('d M, Y') }}
                @if($outlet->terminatedBy) by {{ $outlet->terminatedBy->name }} @endif.
                <div style="margin-top:0.3rem;">{{ $outlet->termination_note }}</div>
            </div>
        </div>
        @endif

        <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
            <button onclick="document.getElementById('markPaidForm').classList.toggle('hidden-form')" class="btn btn-success btn-sm">
                <i class="fas fa-money-bill-wave"></i> Mark Paid
            </button>
            <button onclick="document.getElementById('changePlanForm').classList.toggle('hidden-form')" class="btn btn-accent btn-sm">
                <i class="fas fa-exchange-alt"></i> Change Plan
            </button>
            @if($outlet->isTerminated())
            <button onclick="document.getElementById('reactivateForm').classList.toggle('hidden-form')" class="btn btn-success btn-sm">
                <i class="fas fa-play-circle"></i> Reactivate
            </button>
            @else
            <button onclick="document.getElementById('terminateForm').classList.toggle('hidden-form')" class="btn btn-danger btn-sm">
                <i class="fas fa-ban"></i> Terminate
            </button>
            @endif
        </div>

        {{-- Mark Paid --}}
        <form id="markPaidForm" class="hidden-form" style="margin-top:1.2rem;"
              action="{{ route('superadmin.outlets.mark-paid', $outlet) }}" method="POST">
            @csrf
            <div style="display:flex; gap:0.8rem; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:220px; margin-bottom:0;">
                    <label class="form-label">Note (optional)</label>
                    <input type="text" name="note" class="form-control" placeholder="e.g. Paid via bank transfer, Sept cycle">
                </div>
                <button type="submit" class="btn btn-success" style="margin-bottom:0;">Confirm Paid</button>
            </div>
        </form>

        {{-- Change Plan --}}
        <form id="changePlanForm" class="hidden-form" style="margin-top:1.2rem; border-top:1px solid var(--border-color); padding-top:1.2rem;"
              action="{{ route('superadmin.outlets.change-plan', $outlet) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Plan Type</label>
                        <select name="plan_type" id="editPlanType" class="form-select">
                            <option value="trial" {{ $outlet->plan_type == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="paid" {{ $outlet->plan_type == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" id="editTrialEndsGroup">
                        <label class="form-label">Trial Ends On</label>
                        <input type="date" name="trial_ends_at" class="form-control"
                               value="{{ optional($outlet->trial_ends_at)->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Monthly Amount (PKR)</label>
                        <input type="number" step="0.01" min="0" name="monthly_amount" class="form-control"
                               value="{{ $outlet->monthly_amount }}">
                    </div>
                </div>
                <div class="col-6"></div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Max Salesman Accounts</label>
                        <input type="number" min="0" name="max_salesmen" class="form-control"
                               value="{{ $outlet->max_salesmen }}" placeholder="Unlimited">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Max Booker Accounts</label>
                        <input type="number" min="0" name="max_bookers" class="form-control"
                               value="{{ $outlet->max_bookers }}" placeholder="Unlimited">
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Note (optional)</label>
                <input type="text" name="note" class="form-control" placeholder="Reason for the change">
            </div>
            <button type="submit" class="btn btn-accent" style="margin-top:0.8rem;">Save Plan Changes</button>
        </form>

        {{-- Terminate --}}
        <form id="terminateForm" class="hidden-form" style="margin-top:1.2rem; border-top:1px solid var(--border-color); padding-top:1.2rem;"
              action="{{ route('superadmin.outlets.terminate', $outlet) }}" method="POST"
              onsubmit="return confirm('This blocks login for the shop owner and all staff immediately. Data is kept. Continue?')">
            @csrf
            <div class="form-group">
                <label class="form-label">Termination Note <span>*</span></label>
                <input type="text" name="note" class="form-control" placeholder="Reason for termination (required)" required>
            </div>
            <button type="submit" class="btn btn-danger">Confirm Termination</button>
        </form>

        {{-- Reactivate --}}
        <form id="reactivateForm" class="hidden-form" style="margin-top:1.2rem; border-top:1px solid var(--border-color); padding-top:1.2rem;"
              action="{{ route('superadmin.outlets.reactivate', $outlet) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Note (optional)</label>
                <input type="text" name="note" class="form-control" placeholder="e.g. Renewed for another month">
            </div>
            <button type="submit" class="btn btn-success" style="margin-top:0.4rem;">Confirm Reactivation</button>
        </form>
    </div>
</div>

{{-- Module Access --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-th-large"></i> Module Access</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Module</th><th>Status</th><th>Disabled Note</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach(\App\Models\ShopModule::MODULES as $key)
                    @php $module = $outlet->modules->firstWhere('module_key', $key); @endphp
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                        <td>
                            <span class="badge {{ !$module || $module->is_enabled ? 'badge-active' : 'badge-inactive' }}">
                                {{ !$module || $module->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td>{{ $module->disabled_note ?? '—' }}</td>
                        <td>
                            @if(!$module || $module->is_enabled)
                            <button onclick="document.getElementById('disableModal-{{ $key }}').classList.toggle('hidden-form')"
                                    class="btn btn-danger btn-sm">Disable</button>
                            <div id="disableModal-{{ $key }}" class="hidden-form" style="margin-top:0.6rem;">
                                <form action="{{ route('superadmin.outlets.toggle-module', $outlet) }}" method="POST"
                                      style="display:flex; gap:0.5rem; align-items:center;">
                                    @csrf
                                    <input type="hidden" name="module_key" value="{{ $key }}">
                                    <input type="hidden" name="is_enabled" value="0">
                                    <input type="text" name="note" class="form-control" placeholder="Reason (required)" required style="max-width:220px;">
                                    <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
                                </form>
                            </div>
                            @else
                            <form action="{{ route('superadmin.outlets.toggle-module', $outlet) }}" method="POST" style="display:inline">
                                @csrf
                                <input type="hidden" name="module_key" value="{{ $key }}">
                                <input type="hidden" name="is_enabled" value="1">
                                <button type="submit" class="btn btn-success btn-sm">Enable</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Assign Admin Panel --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-user-shield"></i> Shop Owner Login</div>
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
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-user-tie"></i> Salesmen ({{ $salesmen->count() }})
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

{{-- Bookers --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fas fa-clipboard-list"></i> Bookers ({{ $bookers->count() }})
        </div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($bookers as $b)
                <tr>
                    <td>{{ $b->name }}</td>
                    <td>{{ $b->email }}</td>
                    <td>{{ $b->phone ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $b->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $b->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                    No bookers assigned yet.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Subscription Log --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-history"></i> Subscription History</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Action</th><th>By</th><th>Note</th></tr>
            </thead>
            <tbody>
                @forelse($outlet->subscriptionLogs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M, Y H:i') }}</td>
                    <td>{{ $log->action_label }}</td>
                    <td>{{ $log->performedBy->name ?? '—' }}</td>
                    <td>{{ $log->note ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
                    No history yet.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
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

// Change-plan form: only show trial-ends field when plan type is trial
(function () {
    var planSelect = document.getElementById('editPlanType');
    var trialGroup = document.getElementById('editTrialEndsGroup');
    if (!planSelect) return;

    function toggleTrialField() {
        trialGroup.style.display = planSelect.value === 'trial' ? '' : 'none';
    }

    planSelect.addEventListener('change', toggleTrialField);
    toggleTrialField();
})();
</script>
@endsection