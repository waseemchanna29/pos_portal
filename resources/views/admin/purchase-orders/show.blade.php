@extends('layouts.app')
@section('title', 'Purchase Order')
@section('page-title', 'Purchase Order')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $purchaseOrder->po_number }}</div>
        <div class="page-header-sub">{{ $purchaseOrder->supplier_name }}</div>
    </div>
    <div style="display:flex; gap:0.6rem;">
        @if($purchaseOrder->isOrdered())
        <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" method="POST"
              onsubmit="return confirm('Mark as received? Stock will be updated immediately.')">
            @csrf
            <button class="btn btn-success">
                <i class="fas fa-check-double"></i> Mark Received
            </button>
        </form>
        @endif
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn-outline btn">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

{{-- Summary Hero --}}
<div class="po-summary">
    <div>
        <div class="po-summary-number">{{ $purchaseOrder->po_number }}</div>
        <div class="po-summary-meta">
            Supplier: {{ $purchaseOrder->supplier_name }}
            &nbsp;|&nbsp;
            Ordered: {{ $purchaseOrder->order_date->format('d M Y') }}
            @if($purchaseOrder->expected_date)
                &nbsp;|&nbsp; Expected: {{ $purchaseOrder->expected_date->format('d M Y') }}
            @endif
            @if($purchaseOrder->received_date)
                &nbsp;|&nbsp; Received: {{ $purchaseOrder->received_date->format('d M Y') }}
            @endif
        </div>
    </div>
    <div style="text-align:right;">
        <div class="po-summary-amount">{{ $purchaseOrder->formatted_total }}</div>
        <div class="po-summary-label">
            <span class="badge {{ $purchaseOrder->status_badge_class }}">
                {{ $purchaseOrder->status_label }}
            </span>
        </div>
    </div>
</div>

{{-- Items --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-boxes"></i> Order Items</div>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Qty Ordered</th>
                    <th>Qty Received</th>
                    <th>Unit Cost</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $item->product->name ?? '—' }}</strong></td>
                    <td><code style="font-size:0.8rem;">{{ $item->product->sku ?? '—' }}</code></td>
                    <td>{{ $item->quantity_ordered }}</td>
                    <td>
                        @if($purchaseOrder->isReceived())
                            <span class="badge badge-active">{{ $item->quantity_received }}</span>
                        @else
                            {{ $item->quantity_received }}
                        @endif
                    </td>
                    <td>PKR {{ number_format($item->unit_cost, 2) }}</td>
                    <td><strong>{{ $item->line_total }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align:right; padding:0.8rem 1rem; font-weight:700;">Grand Total:</td>
                    <td style="padding:0.8rem 1rem; font-weight:700; color:var(--primary); font-size:1.05rem;">
                        {{ $purchaseOrder->formatted_total }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Supplier & Notes --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-truck"></i> Supplier Details</div>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="info-item-label">Supplier Name</div><div class="info-item-value">{{ $purchaseOrder->supplier_name }}</div></div>
            <div><div class="info-item-label">Phone</div><div class="info-item-value">{{ $purchaseOrder->supplier_phone ?? '—' }}</div></div>
            <div><div class="info-item-label">Created By</div><div class="info-item-value">{{ $purchaseOrder->createdBy->name ?? '—' }}</div></div>
            <div><div class="info-item-label">Created At</div><div class="info-item-value">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</div></div>
        </div>
        @if($purchaseOrder->supplier_address)
        <div><div class="info-item-label">Address</div><div class="info-item-value">{{ $purchaseOrder->supplier_address }}</div></div>
        @endif
        @if($purchaseOrder->notes)
        <div style="margin-top:0.8rem;"><div class="info-item-label">Notes</div><div class="info-item-value">{{ $purchaseOrder->notes }}</div></div>
        @endif
    </div>
</div>
@endsection