<?php

namespace App\Services\Payments;

use App\Models\Escrow;
use App\Models\EscrowLedger;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    protected MpesaService $mpesa;
    protected TigoPesaService $tigoPesa;

    public function __construct()
    {
        $this->mpesa = new MpesaService();
        $this->tigoPesa = new TigoPesaService();
    }

    /**
     * Create escrow for an order
     */
    public function createEscrow(Order $order, string $paymentMethod): Escrow
    {
        return DB::transaction(function () use ($order, $paymentMethod) {
            $escrow = Escrow::create([
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'amount' => $order->total,
                'currency' => $order->currency ?? 'TZS',
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'hold_until' => now()->addDays(7),
            ]);

            // Create ledger entry
            EscrowLedger::create([
                'escrow_id' => $escrow->id,
                'entry_type' => 'hold',
                'amount' => $order->total,
                'balance_after' => $order->total,
                'description' => 'Funds held in escrow for order #' . $order->id,
            ]);

            return $escrow;
        });
    }

    /**
     * Initiate payment via selected method
     */
    public function initiatePayment(Escrow $escrow, string $phone): array
    {
        $reference = 'MKF' . $escrow->id . strtoupper(substr(uniqid(), -6));
        $description = 'Payment for order #' . $escrow->order_id;

        return match ($escrow->payment_method) {
            'mpesa' => $this->mpesa->stkPush(
                $phone,
                $escrow->amount,
                $reference,
                $description
            ),
            'tigopesa' => $this->tigoPesa->pushPayment(
                $phone,
                $escrow->amount,
                $reference,
                $description
            ),
            default => [
                'success' => false,
                'message' => 'Unsupported payment method',
            ],
        };
    }

    /**
     * Release funds to seller after delivery confirmation
     */
    public function releaseFunds(Escrow $escrow): array
    {
        if ($escrow->status !== 'held') {
            return [
                'success' => false,
                'message' => 'Escrow is not in held status',
            ];
        }

        return DB::transaction(function () use ($escrow) {
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            EscrowLedger::create([
                'escrow_id' => $escrow->id,
                'entry_type' => 'release',
                'amount' => -$escrow->amount,
                'balance_after' => 0,
                'description' => 'Funds released to seller',
            ]);

            // Update order status
            $escrow->order->update(['status' => 'completed']);

            return [
                'success' => true,
                'message' => 'Funds released successfully',
            ];
        });
    }

    /**
     * Refund buyer (dispute resolution)
     */
    public function refundBuyer(Escrow $escrow, string $reason): array
    {
        if (!in_array($escrow->status, ['held', 'pending'])) {
            return [
                'success' => false,
                'message' => 'Escrow cannot be refunded',
            ];
        }

        return DB::transaction(function () use ($escrow, $reason) {
            $escrow->update([
                'status' => 'refunded',
                'released_at' => now(),
            ]);

            EscrowLedger::create([
                'escrow_id' => $escrow->id,
                'entry_type' => 'refund',
                'amount' => -$escrow->amount,
                'balance_after' => 0,
                'description' => 'Refund to buyer: ' . $reason,
            ]);

            $escrow->order->update(['status' => 'refunded']);

            return [
                'success' => true,
                'message' => 'Refund processed successfully',
            ];
        });
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleMpesaCallback(array $data): void
    {
        $callback = $data['Body']['stkCallback'] ?? null;

        if (!$callback) {
            Log::warning('Invalid M-Pesa callback', $data);
            return;
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? null;
        $resultDesc = $callback['ResultDesc'] ?? '';

        // Find escrow by checkout request ID
        $escrow = Escrow::where('transaction_reference', $checkoutRequestId)->first();

        if (!$escrow) {
            Log::warning('Escrow not found for checkout request', ['checkout_id' => $checkoutRequestId]);
            return;
        }

        if ($resultCode === 0) {
            // Payment successful
            $escrow->update([
                'status' => 'held',
                'paid_at' => now(),
            ]);

            EscrowLedger::create([
                'escrow_id' => $escrow->id,
                'entry_type' => 'deposit',
                'amount' => $escrow->amount,
                'balance_after' => $escrow->amount,
                'description' => 'M-Pesa payment received: ' . $resultDesc,
            ]);

            $escrow->order->update(['status' => 'paid']);
        } else {
            // Payment failed
            $escrow->update([
                'status' => 'failed',
                'failure_reason' => $resultDesc,
            ]);

            Log::error('M-Pesa payment failed', [
                'escrow_id' => $escrow->id,
                'result' => $resultDesc,
            ]);
        }
    }

    /**
     * Get escrow statistics
     */
    public function getStats(?int $sellerId = null): array
    {
        $query = Escrow::query();

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        return [
            'total_escrows' => $query->count(),
            'total_held' => (clone $query)->where('status', 'held')->sum('amount'),
            'total_released' => (clone $query)->where('status', 'released')->sum('amount'),
            'total_refunded' => (clone $query)->where('status', 'refunded')->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'held_count' => (clone $query)->where('status', 'held')->count(),
        ];
    }
}
