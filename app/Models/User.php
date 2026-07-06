<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'phone',
        'email',
        'name',
        'password',
        'avatar',
        'role',
        'status',
        'kyc_status',
        'kyc_documents',
        'device_fingerprint',
        'passkey_id',
        'phone_verified_at',
        'last_active_at',
        'preferred_language',
        'is_active',
        'is_verified_expert',
        'expert_title',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'kyc_documents' => 'array',
        'is_active' => 'boolean',
        'is_verified_expert' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function isAgrodealer(): bool
    {
        return $this->role === 'agrodealer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'superadmin' || $this->hasRole('admin');
    }

    public function isExpert(): bool
    {
        return $this->is_verified_expert
            || in_array($this->role, ['agronomist', 'veterinary']);
    }
}
