@extends('layouts.app')
@section('title', 'Receipt')
@section('page-title', 'Sale Receipt')

@section('content')
<div style="max-width:680px; margin:0 auto;">

    <div class="page-header">
        <div>
            <div class="page-header-title">{{ $order->order_number }}</div>
            <div class="page-header-sub">{{ $order->order_date->format('d M Y') }}</div>
        </div>
        <div style="display:flex; gap:0.6rem;">
            <button onclick="window.print()" class="btn-outline btn">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('salesman.pos.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Sale
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:2rem;">

            {{-- Receipt Header --}}
            <div style="text-align:center; margin-bottom:1.5rem; border-bottom:2px dashed var(--border); padding-bottom:1.2rem;">
                <div style="font-size:1.3rem; font-weight:700; color:var(--primary);">
                    {{ auth()->user()->outlet->name }}
                </div>
                <div style="font-size:0.85rem; color:var(--text-muted);">
                    {{ auth()->user()->outlet->address }}, {{ auth()->user()->outlet->city }}
                </div>
                @if(auth()->user()->outlet->phone)
                <div style="font-size:0.85rem; color:var(--text-muted);">
                    Tel: {{ auth()->user()->outlet->phone }}
                </div>
                @endif
                <div style="margin-top:0.8rem; font-size:1rem; font-weight:600;">SALE RECEIPT</div>
            </div>

            {{-- Order Info --}}
            <div style="display:flex; justify-content:space-between; margin-bottom:1.2rem; font-size:0.88rem;">
                <div>
                    <div><strong>Receipt #:</strong> {{ $order->order_number }}</div>
                    <div><strong>Date:</strong> {{ $order->order_date->format('d M Y') }}</div>
                    <div><strong>Salesman:</strong> {{ $order->salesman->name ?? '—' }}</div>
                </div>
                <div style="text-align:right;">
                    <div><strong>Customer:</strong> {{ $order->customer_name }}</div>
                    @if($order->customer_phone)
                    <div><strong>Phone:</strong> {{ $order->customer_phone }}</div>
                    @endif
                    @if($order->customer_city)
                    <div><strong>City:</strong> {{ $order->customer_city }}</div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <table style="width:100%; border-collapse:collapse; font-size:0.88rem; margin-bottom:1.2rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);">
                        <th style="padding:0.5rem 0; text-align:left;">#</th>
                        <th style="padding:0.5rem 0; text-align:left;">Item</th>
                        <th style="padding:0.5rem 0; text-align:center;">Qty</th>
                        <th style="padding:0.5rem 0; text-align:right;">Price</th>
                        <th style="padding:0.5rem 0; text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:0.5rem 0;">{{ $loop->iteration }}</td>
                        <td style="padding:0.5rem 0;">
                            <div>{{ $item->product_name }}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted);">{{ $item->product_sku }}</div>
                        </td>
                        <td style="padding:0.5rem 0; text-align:center;">{{ $item->quantity }}</td>
                        <td style="padding:0.5rem 0; text-align:right;">
                            PKR {{ number_format($item->unit_price - $item->discount, 2) }}
                        </td>
                        <td style="padding:0.5rem 0; text-align:right; font-weight:600;">
                            PKR {{ number_format($item->total_price, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div style="margin-left:auto; max-width:260px; font-size:0.9rem;">
                <div style="display:flex; justify-content:space-between; padding:0.3rem 0;">
                    <span>Subtotal:</span>
                    <span>PKR {{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--danger);">
                    <span>Discount:</span>
                    <span>- PKR {{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->tax_amount > 0)
                <div style="display:flex; justify-content:space-between; padding:0.3rem 0;">
                    <span>Tax:</span>
                    <span>PKR {{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between; padding:0.5rem 0;
                            border-top:2px solid var(--border); font-weight:700; font-size:1rem;">
                    <span>Total:</span>
                    <span>PKR {{ number_format($order->net_amount, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--success);">
                    <span>Paid:</span>
                    <span>PKR {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                @if($order->balance_amount > 0)
                <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--danger); font-weight:600;">
                    <span>Balance:</span>
                    <span>PKR {{ number_format($order->balance_amount, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Payment Method --}}
            @if($order->payments->count())
            <div style="margin-top:1.2rem; border-top:1px dashed var(--border); padding-top:0.8rem; font-size:0.85rem;">
                <strong>Payments:</strong>
                @foreach($order->payments as $payment)
                <div style="color:var(--text-muted);">
                    {{ $payment->method_label }} — PKR {{ number_format($payment->amount, 2) }}
                    @if($payment->reference_number) (Ref: {{ $payment->reference_number }}) @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Footer --}}
            <div style="text-align:center; margin-top:1.5rem; font-size:0.82rem;
                        color:var(--text-muted); border-top:2px dashed var(--border); padding-top:1rem;">
                Thank you for your purchase!<br>
                @if(auth()->user()->outlet->ntn)
                NTN: {{ auth()->user()->outlet->ntn }}
                @endif
            </div>

        </div>
    </div>
</div>
@endsection