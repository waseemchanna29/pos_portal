<?php
// Project path: app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'outlet_id', 'phone', 'is_active'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isSalesman(): bool
    {
        return $this->role === 'salesman';
    }
    public function isBooker(): bool
    {
        return $this->role === 'booker';
    }

    /**
     * True for any role that operates inside a subscribed outlet
     * (i.e. everyone except super admin) — used by EnsureSubscriptionActive.
     */
    public function belongsToOutletSubscription(): bool
    {
        return ! $this->isSuperAdmin();
    }

    public function getRoleBadgeClass(): string
    {
        return match ($this->role) {
            'superadmin' => 'badge-superadmin',
            'admin'      => 'badge-admin',
            'booker'     => 'badge-booker',
            default      => 'badge-salesman',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'admin'      => 'Admin',
            'booker'     => 'Booker',
            default      => 'Salesman',
        };
    }
}