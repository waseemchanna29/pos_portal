<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'collected_by', 'amount',
        'payment_method', 'reference_number',
        'payment_date', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->amount, 2);
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'cheque'        => 'Cheque',
            'easypaisa'     => 'Easypaisa',
            'jazzcash'      => 'JazzCash',
            default         => 'Cash',
        };
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'badge-admin',
            'cheque'        => 'badge-po-ordered',
            'easypaisa'     => 'badge-status-confirmed',
            'jazzcash'      => 'badge-status-dispatched',
            default         => 'badge-active',
        };
    }
}