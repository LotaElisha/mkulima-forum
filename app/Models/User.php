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

    /**
     * Mass-assignable attributes.
     *
     * Everything that decides what an account is ALLOWED to do is deliberately
     * absent: role, status, is_active, is_verified_expert, kyc_status, the two
     * verification timestamps, and password. Those are set through
     * User::provision() or forceFill() at a small number of audited call sites.
     *
     * Before this, all of them were fillable. Nothing exploited it — every
     * controller validated a whitelist first — but a single future
     * `$user->update($request->all())` would have been privilege escalation,
     * account unbanning and verification bypass in one line. Keeping them out
     * of $fillable means that line cannot do damage even if someone writes it.
     */
    protected $fillable = [
        'tenant_id',
        'uuid',
        'phone',
        'email',
        'pending_email',
        'pending_email_requested_at',
        'name',
        'avatar',
        'kyc_documents',
        'device_fingerprint',
        'passkey_id',
        'last_active_at',
        'preferred_language',
        'expert_title',
        'store_name',
        'store_location',
        'business_license',
        'store_description',
    ];

    /**
     * Attributes that grant capability, and are never mass-assignable.
     *
     * Listed explicitly so the boundary is documented in one place rather than
     * inferred from what is missing above.
     *
     * @var array<int, string>
     */
    public const PRIVILEGED_ATTRIBUTES = [
        'role',
        'status',
        'is_active',
        'is_verified_expert',
        'kyc_status',
        'email_verified_at',
        'phone_verified_at',
        'password',
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

    /**
     * Create a user including attributes that are not mass-assignable.
     *
     * The only sanctioned way to set a role, a status or a verification
     * timestamp at creation time. Use it where the caller has already
     * established authority — registration deciding a self-selected role, an
     * admin creating staff, a verified social identity — and never with
     * unvalidated request input.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function provision(array $attributes): self
    {
        $user = new self;
        $user->forceFill($attributes);
        $user->save();

        return $user;
    }

    /**
     * Update attributes that are not mass-assignable, and persist.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function setPrivileged(array $attributes): bool
    {
        return $this->forceFill($attributes)->save();
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
