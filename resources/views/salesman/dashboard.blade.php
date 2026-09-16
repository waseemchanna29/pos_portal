@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-cash-register"></i></div>
        <div>
            <div class="stat-value">{{ $stats['pos_today'] }}</div>
            <div class="stat-label">POS Sales Today</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-money-bill-wave"></i></div>
        <div>
            <div class="stat-value">PKR {{ number_format($stats['sales_today'], 0) }}</div>
            <div class="stat-label">Revenue Today</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon accent"><i class="fas fa-clipboard-list"></i></div>
        <div>
            <div class="stat-value">{{ $stats['bookings_active'] }}</div>
            <div class="stat-label">Active Bookings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></div>
        <div>
            <div class="stat-value">PKR {{ number_format($stats['outstanding'], 0) }}</div>
            <div class="stat-label">Outstanding Balance</div>
        </div>
    </div>
</div>

<div class="page-header" style="margin-bottom:1rem;">
    <div class="page-header-title">Quick Actions</div>
</div>

<div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="{{ route('salesman.pos.index') }}" class="btn btn-primary btn-lg">
        <i class="fas fa-cash-register"></i> Open POS
    </a>
    <a href="{{ route('salesman.bookings.create') }}" class="btn btn-accent btn-lg">
        <i class="fas fa-plus"></i> New Booking
    </a>
    <a href="{{ route('salesman.bookings.index') }}" class="btn-outline btn btn-lg">
        <i class="fas fa-clipboard-list"></i> View Bookings
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-history"></i> Recent Orders</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Type</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>
                        <span class="badge {{ $order->isPos() ? 'badge-status-completed' : 'badge-status-confirmed' }}">
                            {{ $order->isPos() ? 'POS' : 'Booking' }}
                        </span>
                    </td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->formatted_net_amount }}</td>
                    <td>
                        <span class="badge {{ $order->payment_badge_class }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">
                        No orders yet today.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection