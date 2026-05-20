@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon accent"><i class="fas fa-user-tie"></i></div>
        <div>
            <div class="stat-value">{{ $stats['salesmen'] }}</div>
            <div class="stat-label">Salesmen in Outlet</div>
        </div>
    </div>
</div>

@if($outlet)
<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-store"></i> My Outlet</div>
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
        <div class="info-item-label">Address</div>
        <div class="info-item-value">{{ $outlet->address }}</div>
    </div>
</div>
@endif
@endsection