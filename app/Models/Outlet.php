<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'phone',
        'ntn',
        'strn',
        'is_active',
        'created_by'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'admin');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProvinceLabelAttribute(): string
    {
        return $this->province ?? '—';
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
