@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Purchase Orders</div>
        <div class="page-header-sub">Manage inventory purchases for your outlet</div>
    </div>
    <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Purchase Order
    </a>
</div>

<div class="filter-bar">
    <form action="" method="GET" style="display:flex; gap:0.8rem; flex-wrap:wrap;">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">— All Statuses —</option>
            @foreach(['ordered','received','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" class="form-control"
               placeholder="Search PO number or supplier..."
               value="{{ request('search') }}">
        <button type="submit" class="btn-outline btn"><i class="fas fa-search"></i></button>
    </form>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->po_number }}</strong></td>
                    <td>{{ $order->supplier_name }}</td>
                    <td>{{ $order->order_date->format('d M Y') }}</td>
                    <td>{{ $order->expected_date ? $order->expected_date->format('d M Y') : '—' }}</td>
                    <td><strong>{{ $order->formatted_total }}</strong></td>
                    <td>
                        <span class="badge {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.purchase-orders.show', $order) }}" class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($order->isOrdered())
                        <form action="{{ route('admin.purchase-orders.receive', $order) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Mark as received? This will update product stock.')">
                            @csrf
                            <button class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Receive
                            </button>
                        </form>
                        @endif
                        @if(!$order->isReceived())
                        <form action="{{ route('admin.purchase-orders.destroy', $order) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Cancel this purchase order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <h4>No purchase orders yet</h4>
                            <p>Create your first purchase order to restock inventory.</p>
                            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Purchase Order
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