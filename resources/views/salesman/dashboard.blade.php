@extends('layouts.app')
@section('title', 'Salesman Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="card">
    <div class="card-body" style="text-align:center; padding:3rem;">
        <i class="fas fa-cash-register" style="font-size:3rem; color:var(--primary); opacity:0.4; display:block; margin-bottom:1rem;"></i>
        <h3 style="color:var(--primary); margin-bottom:0.5rem;">Welcome, {{ auth()->user()->name }}</h3>
        <p style="color:var(--text-muted);">
            Outlet: <strong>{{ auth()->user()->outlet->name ?? '—' }}</strong>
        </p>
    </div>
</div>
@endsection