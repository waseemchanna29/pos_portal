<?php
// Project path: app/Models/Outlet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'phone',
        'ntn',
        'strn',
        'is_active',
        'created_by',

        // Subscription / plan
        'plan_type',
        'trial_ends_at',
        'subscription_status',
        'monthly_amount',
        'max_salesmen',
        'max_bookers',

        // Billing trail
        'last_marked_paid_at',
        'marked_paid_by',

        // Termination trail
        'terminated_at',
        'terminated_by',
        'termination_note',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'trial_ends_at'        => 'date',
        'monthly_amount'       => 'decimal:2',
        'last_marked_paid_at'  => 'date',
        'terminated_at'        => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'admin');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function salesmen()
    {
        return $this->hasMany(User::class)->where('role', 'salesman');
    }

    public function bookers()
    {
        return $this->hasMany(User::class)->where('role', 'booker');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markedPaidBy()
    {
        return $this->belongsTo(User::class, 'marked_paid_by');
    }

    public function terminatedBy()
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function modules()
    {
        return $this->hasMany(ShopModule::class);
    }

    public function subscriptionLogs()
    {
        return $this->hasMany(SubscriptionLog::class)->latest();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getProvinceLabelAttribute(): string
    {
        return $this->province ?? '—';
    }

    /**
     * Access is blocked for anything except super admin when this is false —
     * used by EnsureSubscriptionActive. Data is never touched by this check,
     * only login/route access.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_status !== 'active') {
            return false;
        }

        if ($this->plan_type === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isOnTrial(): bool
    {
        return $this->plan_type === 'trial';
    }

    public function isTerminated(): bool
    {
        return $this->subscription_status === 'terminated';
    }

    public function getSubscriptionBadgeClassAttribute(): string
    {
        return match ($this->subscription_status) {
            'active'     => 'badge-active',
            'expired'    => 'badge-po-ordered',
            'terminated' => 'badge-status-cancelled',
            default      => 'badge-po-draft',
        };
    }

    public function getSalesmenCountAttribute(): int
    {
        return $this->users()->where('role', 'salesman')->count();
    }

    public function getBookersCountAttribute(): int
    {
        return $this->users()->where('role', 'booker')->count();
    }

    public function canAddSalesman(): bool
    {
        return is_null($this->max_salesmen) || $this->salesmen_count < $this->max_salesmen;
    }

    public function canAddBooker(): bool
    {
        return is_null($this->max_bookers) || $this->bookers_count < $this->max_bookers;
    }
}