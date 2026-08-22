<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'phone',
        'email',
        'pending_email',
        'pending_email_requested_at',
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
        'email_verified_at',
        'last_active_at',
        'preferred_language',
        'is_active',
        'is_verified_expert',
        'expert_title',
        'store_name',
        'store_location',
        'business_license',
        'store_description',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'pending_email_requested_at' => 'datetime',
        'last_active_at' => 'datetime',
        'kyc_documents' => 'array',
        'is_active' => 'boolean',
        'is_verified_expert' => 'boolean',
    ];

    /**
     * Verification and reset mails are localised to the farmer's chosen
     * language; Laravel's stock English notifications are never used.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * A staged email change proves ownership of users.pending_email, not of
     * the address currently on the account.
     */
    public function sendPendingEmailVerificationNotification(): void
    {
        if (! $this->pending_email) {
            return;
        }

        $this->notify(new VerifyEmailNotification($this->pending_email));
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
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
        return $this->role === 'admin' || $this->role === 'superadmin' || $this->hasRole('admin') || $this->hasRole('superadmin');
    }

    /**
     * Admin & superadmin bypass ALL permission checks — they have full access.
     */
    public function can($abilities, $arguments = []): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return parent::can($abilities, $arguments);
    }

    public function isExpert(): bool
    {
        return $this->is_verified_expert
            || in_array($this->role, ['agronomist', 'veterinary']);
    }
}
