<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Escrow extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'escrows';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'buyer_id',
        'seller_id',
        'uuid',
        'reference',
        'status',
        'amount',
        'currency',
        'payment_method',
        'provider_reference',
        'transaction_reference',
        'failure_reason',
        'metadata',
        'paid_at',
        'released_at',
        'hold_until',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
        'hold_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($escrow) {
            if (empty($escrow->uuid)) {
                $escrow->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(EscrowLedger::class);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $valid = match ($this->status) {
            'pending' => ['held', 'failed'],
            'held' => ['released', 'disputed', 'refunded'],
            'released' => ['disputed', 'finalized'],
            'disputed' => ['released', 'refunded', 'arbitrated'],
            default => [],
        };

        return in_array($newStatus, $valid);
    }
}
