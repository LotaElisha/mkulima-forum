<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Order;
use App\Services\Payments\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'payment_method' => ['required', 'string', 'in:mpesa,tigopesa,cash'],
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

        // Create escrow
        $escrow = $this->escrowService->createEscrow($order, $request->input('payment_method'));

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
        $escrow = Escrow::with(['ledger', 'order'])->where('uuid', $uuid)->firstOrFail();

        // Verify access
        if ($escrow->buyer_id !== $request->user()->id && $escrow->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'escrow' => $escrow,
            'ledger' => $escrow->ledger,
        ]);
    }

    /**
     * Get user's escrows
     */
    public function myEscrows(Request $request): JsonResponse
    {
        $escrows = Escrow::where('buyer_id', $request->user()->id)
            ->orWhere('seller_id', $request->user()->id)
            ->with('order')
            ->latest()
            ->paginate(20);

        return response()->json($escrows);
    }

    /**
     * M-Pesa callback webhook
     */
    public function mpesaCallback(Request $request): JsonResponse
    {
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
