<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function getBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'balance' => 0,
                'currency' => 'TZS',
                'status' => 'active',
            ]
        );

        return response()->json([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'status' => $wallet->status,
        ]);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json(['transactions' => []]);
        }

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'phone' => ['required', 'string'],
            'provider' => ['required', 'string', 'in:mpesa,tigopesa,airtelmoney,bank'],
        ]);

        $user = $request->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'balance' => 0,
                'currency' => 'TZS',
                'status' => 'active',
            ]
        );

        // In production, integrate with M-Pesa/Tigo Pesa API
        // For now, simulate successful deposit
        $transaction = $wallet->deposit(
            $request->input('amount'),
            'Deposit via ' . strtoupper($request->input('provider')),
            [
                'phone' => $request->input('phone'),
                'provider' => $request->input('provider'),
            ]
        );

        return response()->json([
            'message' => 'Deposit successful',
            'transaction' => $transaction,
            'new_balance' => $wallet->balance,
        ]);
    }

    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_phone' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $senderWallet = Wallet::where('user_id', $user->id)->first();

        if (!$senderWallet || $senderWallet->balance < $request->input('amount')) {
            return response()->json([
                'message' => 'Insufficient balance',
            ], 422);
        }

        $recipient = User::where('phone', $request->input('recipient_phone'))->first();
        if (!$recipient) {
            return response()->json([
                'message' => 'Recipient not found',
            ], 404);
        }

        $recipientWallet = Wallet::firstOrCreate(
            ['user_id' => $recipient->id],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'balance' => 0,
                'currency' => 'TZS',
                'status' => 'active',
            ]
        );

        $transaction = $senderWallet->transferTo(
            $recipientWallet,
            $request->input('amount'),
            $request->input('description')
        );

        return response()->json([
            'message' => 'Transfer successful',
            'transaction' => $transaction,
            'new_balance' => $senderWallet->balance,
        ]);
    }
}
