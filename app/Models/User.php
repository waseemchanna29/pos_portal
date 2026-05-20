<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function getRoleBadgeClass(): string
    {
        return match ($this->role) {
            'superadmin' => 'badge-superadmin',
            'admin'      => 'badge-admin',
            default      => 'badge-salesman',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'admin'      => 'Admin',
            default      => 'Salesman',
        };
    }
}
