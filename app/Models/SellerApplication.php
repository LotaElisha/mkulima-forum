<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SellerApplication extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    /**
     * Status, reviewer and review timestamp are deliberately absent.
     *
     * They are the whole value of the record: an applicant who can set
     * `status` in the same request that creates the application has approved
     * themselves. Writes to those columns go through the review methods below.
     */
    protected $fillable = [
        'business_name',
        'business_type',
        'region',
        'district',
        'contact_phone',
        'description',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            $application->uuid ??= (string) Str::uuid();
            $application->status ??= self::PENDING;
            $application->submitted_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    /**
     * Approve, and grant the selling role in the same transaction.
     *
     * The two must not come apart: an approved application whose user is still
     * a farmer produces exactly the 403 this whole feature exists to remove.
     */
    public function approve(User $reviewer, string $grantRole): void
    {
        $this->forceFill([
            'status' => self::APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ])->save();

        $this->user->setPrivileged(['role' => $grantRole]);
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->forceFill([
            'status' => self::REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ])->save();
    }
}
