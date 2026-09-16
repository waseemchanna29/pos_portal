@extends('layouts.app')
@section('title', 'Bookings')
@section('page-title', 'Booking Orders')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Booking Orders</div>
        <div class="page-header-sub">Manage customer booking orders</div>
    </div>
    <a href="{{ route('salesman.bookings.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Booking
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->customer_phone ?? '—' }}</td>
                    <td>{{ $order->customer_city ?? '—' }}</td>
                    <td>{{ $order->formatted_net_amount }}</td>
                    <td>
                        @if($order->balance_amount > 0)
                            <span style="color:var(--danger); font-weight:600;">
                                {{ $order->formatted_balance }}
                            </span>
                        @else
                            <span style="color:var(--success);">Paid</span>
                        @endif
                    </td>
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
                    <td>{{ $order->order_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('salesman.bookings.show', $order) }}"
                           class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h4>No bookings yet</h4>
                            <p>Create your first booking order.</p>
                            <a href="{{ route('salesman.bookings.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Booking
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="card-footer">{{ $orders->links() }}</div>
    @endif
</div>
@endsection