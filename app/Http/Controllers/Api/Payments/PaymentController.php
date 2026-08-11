<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Order;
use App\Services\Payments\EscrowService;
use App\Services\Payments\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected EscrowService $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Initiate payment for an order
     */
    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', 'string', 'in:mpesa,tigopesa'],
            'phone' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::findOrFail($request->input('order_id'));

        // Verify order belongs to current user
        if ($order->buyer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'This order is not awaiting payment.'], 409);
        }

        $lockedResult = Cache::lock("payment-initiation:order:{$order->id}", 30)->get(function () use ($order, $request) {
            $existing = Escrow::where('order_id', $order->id)
                ->whereIn('status', ['pending', 'held', 'released', 'disputed'])
                ->latest('id')
                ->first();

            return [
                'escrow' => $existing ?: $this->escrowService->createEscrow($order, $request->input('payment_method')),
                'created' => ! $existing,
            ];
        });

        if (! $lockedResult) {
            return response()->json(['message' => 'Payment initiation is already in progress.'], 409);
        }

        $escrow = $lockedResult['escrow'];
        if (! $lockedResult['created']) {
            return response()->json([
                'message' => 'A payment attempt already exists for this order.',
                'escrow' => $escrow,
            ], 409);
        }

        // Initiate payment
        $result = $this->escrowService->initiatePayment($escrow, $request->input('phone'));

        if ($result['success']) {
            $escrow->update([
                'transaction_reference' => $result['checkout_request_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Payment initiated',
                'escrow' => $escrow,
                'payment' => $result,
            ]);
        }

        $escrow->update([
            'status' => 'failed',
            'failure_reason' => $result['message'] ?? 'Provider initiation failed',
        ]);

        return response()->json([
            'message' => 'Payment initiation failed',
            'error' => $result['message'] ?? 'Unknown error',
        ], 400);
    }

    /**
     * Confirm delivery and release funds
     */
    public function confirmDelivery(Request $request, string $uuid): JsonResponse
    {
        $escrow = Escrow::where('uuid', $uuid)->firstOrFail();

        // Verify buyer
        if ($escrow->buyer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $this->escrowService->releaseFunds($escrow);

        return response()->json($result);
    }

    /**
     * Request refund (buyer only)
     */
    public function requestRefund(Request $request, string $uuid): JsonResponse
    {
        $escrow = Escrow::where('uuid', $uuid)->firstOrFail();

        if ($escrow->buyer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $this->escrowService->refundBuyer($escrow, $request->input('reason', 'Buyer requested refund'));

        return response()->json($result);
    }

    /**
     * Get escrow status
     */
    public function status(Request $request, string $uuid): JsonResponse
    {
        $escrow = Escrow::with(['ledgerEntries', 'order'])->where('uuid', $uuid)->firstOrFail();

        // Verify access
        if ($escrow->buyer_id !== $request->user()->id && $escrow->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'escrow' => $escrow,
            'ledger' => $escrow->ledgerEntries,
        ]);
    }

    /**
     * Get user's escrows
     */
    public function myEscrows(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $escrows = Escrow::where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
        })
            ->with('order')
            ->latest()
            ->paginate(20);

        $escrows->getCollection()->transform(function ($escrow) use ($userId) {
            $escrow->setAttribute('direction', $escrow->buyer_id === $userId ? 'buying' : 'selling');

            return $escrow;
        });

        return response()->json($escrows);
    }

    /**
     * M-Pesa callback webhook
     */
    public function mpesaCallback(Request $request, MpesaService $mpesa): JsonResponse
    {
        $providedSecret = $request->header('X-Mkulima-Webhook-Secret') ?? $request->query('token');
        if (! $mpesa->verifyCallbackSecret($providedSecret)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unauthorized'], 401);
        }

        $this->escrowService->handleMpesaCallback($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Get payment statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->escrowService->getStats(
            $request->user()->role === 'seller' ? $request->user()->id : null
        );

        return response()->json($stats);
    }
}
