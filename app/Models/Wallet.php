<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'balance',
        'locked_balance',
        'currency',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'locked_balance' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposit(float $amount, string $description = null, array $metadata = []): WalletTransaction
    {
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->user_id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $this->balance - $amount,
            'balance_after' => $this->balance,
            'description' => $description ?? 'Deposit',
            'metadata' => $metadata,
        ]);
    }

    public function withdraw(float $amount, string $description = null, array $metadata = []): ?WalletTransaction
    {
        if ($this->balance < $amount) {
            return null;
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->user_id,
            'type' => 'withdraw',
            'amount' => -$amount,
            'balance_before' => $this->balance + $amount,
            'balance_after' => $this->balance,
            'description' => $description ?? 'Withdrawal',
            'metadata' => $metadata,
        ]);
    }

    public function transferTo(Wallet $recipient, float $amount, string $description = null): ?WalletTransaction
    {
        if ($this->balance < $amount) {
            return null;
        }

        $this->balance -= $amount;
        $this->save();

        $recipient->balance += $amount;
        $recipient->save();

        // Outgoing transaction
        $this->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->user_id,
            'type' => 'transfer_out',
            'amount' => -$amount,
            'balance_before' => $this->balance + $amount,
            'balance_after' => $this->balance,
            'description' => $description ?? 'Transfer to ' . $recipient->user->name,
            'metadata' => ['recipient_wallet_id' => $recipient->id],
        ]);

        // Incoming transaction
        return $recipient->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $recipient->user_id,
            'type' => 'transfer_in',
            'amount' => $amount,
            'balance_before' => $recipient->balance - $amount,
            'balance_after' => $recipient->balance,
            'description' => $description ?? 'Transfer from ' . $this->user->name,
            'metadata' => ['sender_wallet_id' => $this->id],
        ]);
    }
}
