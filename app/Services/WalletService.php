<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletService
{
    public function getOrCreateWallet(int $userId): Wallet
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            [
                'uuid' => (string) Str::uuid(),
                'balance' => 0,
                'locked_balance' => 0,
                'currency' => 'TZS',
                'status' => 'active',
            ]
        );

        return $wallet;
    }

    public function getBalance(int $userId): array
    {
        $wallet = $this->getOrCreateWallet($userId);
        return [
            'wallet_id' => $wallet->uuid,
            'balance' => (float) $wallet->balance,
            'locked_balance' => (float) $wallet->locked_balance,
            'available_balance' => $wallet->availableBalance(),
            'currency' => $wallet->currency,
            'status' => $wallet->status,
        ];
    }

    public function deposit(int $userId, float $amount, string $reference, ?string $description = null, ?array $metadata = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }

        return DB::transaction(function () use ($userId, $amount, $reference, $description, $metadata) {
            $wallet = Wallet::lockForUpdate()->where('user_id', $userId)->firstOrFail();
            $balanceBefore = (float) $wallet->balance;
            $wallet->balance = $balanceBefore + $amount;
            $wallet->save();

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'reference' => $reference,
                'description' => $description ?? 'Wallet deposit',
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'transaction_id' => $tx->uuid,
                'balance' => (float) $wallet->balance,
                'amount' => $amount,
            ];
        });
    }

    public function withdraw(int $userId, float $amount, string $reference, ?string $description = null, ?array $metadata = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }

        return DB::transaction(function () use ($userId, $amount, $reference, $description, $metadata) {
            $wallet = Wallet::lockForUpdate()->where('user_id', $userId)->firstOrFail();
            $available = $wallet->availableBalance();

            if ($available < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            $balanceBefore = (float) $wallet->balance;
            $wallet->balance = $balanceBefore - $amount;
            $wallet->save();

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'reference' => $reference,
                'description' => $description ?? 'Wallet withdrawal',
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'transaction_id' => $tx->uuid,
                'balance' => (float) $wallet->balance,
                'amount' => $amount,
            ];
        });
    }

    public function transfer(int $fromUserId, int $toUserId, float $amount, ?string $description = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero'];
        }

        if ($fromUserId === $toUserId) {
            return ['success' => false, 'message' => 'Cannot transfer to yourself'];
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $description) {
            $fromWallet = Wallet::lockForUpdate()->where('user_id', $fromUserId)->firstOrFail();
            $toWallet = Wallet::lockForUpdate()->where('user_id', $toUserId)->firstOrFail();

            if ($fromWallet->availableBalance() < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            $reference = 'TRF-' . strtoupper(Str::random(8));

            // Debit sender
            $fromBalanceBefore = (float) $fromWallet->balance;
            $fromWallet->balance = $fromBalanceBefore - $amount;
            $fromWallet->save();

            WalletTransaction::create([
                'wallet_id' => $fromWallet->id,
                'uuid' => (string) Str::uuid(),
                'user_id' => $fromUserId,
                'type' => 'transfer_out',
                'amount' => -$amount,
                'balance_before' => $fromBalanceBefore,
                'balance_after' => $fromWallet->balance,
                'status' => 'completed',
                'reference' => $reference,
                'description' => $description ?? 'Transfer to user ' . $toUserId,
                'metadata' => ['to_user_id' => $toUserId],
            ]);

            // Credit recipient
            $toBalanceBefore = (float) $toWallet->balance;
            $toWallet->balance = $toBalanceBefore + $amount;
            $toWallet->save();

            WalletTransaction::create([
                'wallet_id' => $toWallet->id,
                'uuid' => (string) Str::uuid(),
                'user_id' => $toUserId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'balance_before' => $toBalanceBefore,
                'balance_after' => $toWallet->balance,
                'status' => 'completed',
                'reference' => $reference,
                'description' => $description ?? 'Transfer from user ' . $fromUserId,
                'metadata' => ['from_user_id' => $fromUserId],
            ]);

            return [
                'success' => true,
                'reference' => $reference,
                'amount' => $amount,
                'from_balance' => (float) $fromWallet->balance,
            ];
        });
    }

    public function transactionHistory(int $userId, int $perPage = 20): array
    {
        $wallet = $this->getOrCreateWallet($userId);
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'wallet_id' => $wallet->uuid,
            'balance' => (float) $wallet->balance,
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ];
    }
}
