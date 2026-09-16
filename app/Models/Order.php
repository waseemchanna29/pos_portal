<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'salesman_id', 'order_number', 'order_type',
        'status', 'payment_status',
        'customer_name', 'customer_phone', 'customer_address', 'customer_city',
        'subtotal', 'discount_amount', 'tax_amount', 'net_amount',
        'paid_amount', 'balance_amount',
        'order_date', 'expected_delivery_date', 'delivered_date',
        'notes', 'cancelled_reason', 'cancelled_by',
    ];

    protected $casts = [
        'order_date'             => 'date',
        'expected_delivery_date' => 'date',
        'delivered_date'         => 'date',
        'subtotal'               => 'decimal:2',
        'discount_amount'        => 'decimal:2',
        'tax_amount'             => 'decimal:2',
        'net_amount'             => 'decimal:2',
        'paid_amount'            => 'decimal:2',
        'balance_amount'         => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // ── Status Helpers ─────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isConfirmed(): bool  { return $this->status === 'confirmed'; }
    public function isDispatched(): bool { return $this->status === 'dispatched'; }
    public function isDelivered(): bool  { return $this->status === 'delivered'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isPos(): bool        { return $this->order_type === 'pos'; }
    public function isBooking(): bool    { return $this->order_type === 'booking'; }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeDispatched(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canBeDelivered(): bool
    {
        return $this->status === 'dispatched';
    }

    // ── Payment Helpers ────────────────────────────────────────────────────────

    public function isFullyPaid(): bool  { return $this->payment_status === 'paid'; }
    public function isPartialPaid(): bool { return $this->payment_status === 'partial'; }
    public function isUnpaid(): bool     { return $this->payment_status === 'unpaid'; }

    // ── Badge Classes ──────────────────────────────────────────────────────────

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'badge-status-pending',
            'confirmed'  => 'badge-status-confirmed',
            'dispatched' => 'badge-status-dispatched',
            'delivered'  => 'badge-status-delivered',
            'completed'  => 'badge-status-completed',
            'cancelled'  => 'badge-status-cancelled',
            default      => 'badge-po-draft',
        };
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid'    => 'badge-active',
            'partial' => 'badge-po-ordered',
            default   => 'badge-po-cancelled',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return ucfirst($this->payment_status);
    }

    // ── Formatted Amounts ──────────────────────────────────────────────────────

    public function getFormattedNetAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->net_amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'PKR ' . number_format($this->balance_amount, 2);
    }

    // ── Order Number Generator ─────────────────────────────────────────────────

    public static function generateOrderNumber(int $outletId, string $type): string
    {
        $prefix = $type === 'pos' ? 'POS' : 'BK';
        $count  = static::where('outlet_id', $outletId)
                        ->where('order_type', $type)
                        ->count() + 1;

        return $prefix . '-'
             . str_pad($outletId, 3, '0', STR_PAD_LEFT)
             . '-'
             . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}