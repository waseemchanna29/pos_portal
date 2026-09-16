<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'outlet_id', 'created_by', 'supplier_name',
        'supplier_phone', 'supplier_address', 'total_amount',
        'status', 'order_date', 'expected_date', 'received_date', 'notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'ordered'   => 'badge-po-ordered',
            'received'  => 'badge-po-received',
            'cancelled' => 'badge-po-cancelled',
            default     => 'badge-po-draft',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'PKR ' . number_format($this->total_amount, 2);
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isOrdered(): bool  { return $this->status === 'ordered'; }
    public function isReceived(): bool { return $this->status === 'received'; }

    /**
     * Generate next PO number for an outlet.
     */
    public static function generatePoNumber(int $outletId): string
    {
        $count  = static::where('outlet_id', $outletId)->count() + 1;
        return 'PO-' . str_pad($outletId, 3, '0', STR_PAD_LEFT)
                     . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}