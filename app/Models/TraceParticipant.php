<?php

namespace App\Models;

use App\Services\Trace\TraceCode;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Someone who takes part in Mkulima Trace, and in which capacity.
 *
 * Farmers are active as soon as they sign up; every other type waits for an
 * admin. `verified_at` is the only thing the app shows as "verified".
 */
class TraceParticipant extends Model
{
    use BelongsToTenant;

    public const TYPES = ['farmer', 'collector', 'warehouse', 'processor', 'transporter', 'buyer'];

    public const STATUSES = ['active', 'pending', 'rejected', 'suspended'];

    protected $fillable = [
        'tenant_id', 'user_id', 'type', 'organisation_name', 'region', 'share_contact_with_chain',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'share_contact_with_chain' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $p) {
            $p->uuid ??= (string) Str::uuid();
            $p->code ??= TraceCode::unique(self::class);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plots(): HasMany
    {
        return $this->hasMany(TracePlot::class, 'participant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function displayName(): string
    {
        return $this->organisation_name ?: (string) $this->user?->name;
    }
}
