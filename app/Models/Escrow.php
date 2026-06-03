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
        'reference',
        'status',
        'amount',
        'currency',
        'provider',
        'provider_reference',
        'metadata',
        'released_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'released_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

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
            'HELD' => ['RELEASED', 'DISPUTED', 'REFUNDED'],
            'RELEASED' => ['DISPUTED', 'FINALIZED'],
            'DISPUTED' => ['RELEASED', 'REFUNDED', 'ARBITRATED'],
            default => [],
        };

        return in_array($newStatus, $valid);
    }
}
