@extends('layouts.app')
@section('title', 'Booking Detail')
@section('page-title', 'Booking Detail')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $order->order_number }}</div>
        <div class="page-header-sub">{{ $order->customer_name }}</div>
    </div>
    <a href="{{ route('salesman.bookings.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

{{-- Status Hero --}}
<div class="po-summary" style="margin-bottom:1.5rem;">
    <div>
        <div class="po-summary-number">{{ $order->order_number }}</div>
        <div class="po-summary-meta">
            {{ $order->customer_name }}
            @if($order->customer_phone) &nbsp;|&nbsp; {{ $order->customer_phone }} @endif
            @if($order->customer_city) &nbsp;|&nbsp; {{ $order->customer_city }} @endif
        </div>
    </div>
    <div style="display:flex; gap:2rem; align-items:center; flex-wrap:wrap;">
        <div style="text-align:right;">
            <div class="po-summary-amount">{{ $order->formatted_net_amount }}</div>
            <div class="po-summary-label">Net Total</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:1.2rem; font-weight:700; color:#fbbf24;">
                {{ $order->formatted_balance }}
            </div>
            <div class="po-summary-label">Balance Due</div>
        </div>
        <div>
            <span class="badge {{ $order->status_badge_class }}" style="font-size:0.9rem; padding:0.4rem 1rem;">
                {{ $order->status_label }}
            </span>
            <br>
            <span class="badge {{ $order->payment_badge_class }}" style="margin-top:0.4rem;">
                {{ $order->payment_status_label }}
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
                    <th>#</th><th>Product</th><th>SKU</th>
                    <th>Qty</th><th>Unit Price</th><th>Discount</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td><code style="font-size:0.8rem;">{{ $item->product_sku }}</code></td>
                    <td>{{ $item->quantity }}</td>
                    <td>PKR {{ number_format($item->unit_price, 2) }}</td>
                    <td>
                        @if($item->discount > 0)
                            <span style="color:var(--danger);">
                                - PKR {{ number_format($item->discount, 2) }}
                            </span>
                        @else —
                        @endif
                    </td>
                    <td><strong>{{ $item->formatted_total }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align:right; padding:0.8rem; font-weight:700;">
                        Net Total:
                    </td>
                    <td style="padding:0.8rem; font-weight:700; color:var(--primary); font-size:1.05rem;">
                        {{ $order->formatted_net_amount }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Payment History + Add Payment --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-money-bill-wave"></i> Payments</div>
    </div>
    <div class="card-body">

        @if($order->payments->count())
        <div class="table-wrapper" style="margin-bottom:1.5rem;">
            <table class="data-table">
                <thead>
                    <tr><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th><th>Collected By</th></tr>
                </thead>
                <tbody>
                    @foreach($order->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge {{ $payment->method_badge_class }}">
                                {{ $payment->method_label }}
                            </span>
                        </td>
                        <td>{{ $payment->reference_number ?? '—' }}</td>
                        <td><strong>{{ $payment->formatted_amount }}</strong></td>
                        <td>{{ $payment->collectedBy->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Add Payment Form --}}
        @if(!$order->isFullyPaid() && !$order->isCancelled())
        <div style="border-top:1px solid var(--border); padding-top:1.2rem;">
            <div style="font-weight:600; margin-bottom:0.8rem;">
                Record Payment &nbsp;
                <span style="color:var(--danger); font-size:0.9rem;">
                    (Balance: {{ $order->formatted_balance }})
                </span>
            </div>
            <form action="{{ route('salesman.payments.store', $order) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Amount (PKR) <span>*</span></label>
                            <input type="number" step="0.01" name="amount"
                                   class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                   max="{{ $order->balance_amount }}"
                                   placeholder="Max: PKR {{ number_format($order->balance_amount, 2) }}">
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Payment Date <span>*</span></label>
                            <input type="date" name="payment_date" class="form-control"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Method <span>*</span></label>
                            <select name="payment_method" class="form-select" id="showPayMethod"
                                    onchange="toggleShowRef()">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="easypaisa">Easypaisa</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6" id="showRefGroup" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Reference / TXN No.</label>
                            <input type="text" name="reference_number" class="form-control"
                                   placeholder="Transaction or cheque number">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional note">
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Record Payment
                </button>
            </form>
        </div>
        @else
        <div class="alert alert-success" style="margin:0;">
            <i class="fas fa-check-circle"></i> This order is fully paid.
        </div>
        @endif
    </div>
</div>

{{-- Cancel Order --}}
@if($order->canBeCancelled())
<div class="danger-zone">
    <h5><i class="fas fa-times-circle"></i> Cancel Booking</h5>
    <p>Cancelling will release all reserved stock. This cannot be undone.</p>
    <form action="{{ route('salesman.bookings.cancel', $order) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to cancel this booking?')">
        @csrf
        <div class="form-group" style="max-width:400px; margin-bottom:0.8rem;">
            <input type="text" name="cancelled_reason" class="form-control"
                   placeholder="Reason for cancellation" required>
            @error('cancelled_reason') <div class="invalid-feedback" style="display:block;">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-times"></i> Cancel Booking
        </button>
    </form>
</div>
@endif

<script>
function toggleShowRef() {
    const method = document.getElementById('showPayMethod').value;
    document.getElementById('showRefGroup').style.display = method !== 'cash' ? 'flex' : 'none';
}
</script>
@endsection