<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|integer',
            'product_id' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'delivery_days' => 'required|integer|min:1|max:30',
        ]);

        $user = Auth::user();
        $escrowId = 'ESC-' . time();

        $escrow = [
            'id' => $escrowId,
            'buyer_id' => $user->id,
            'buyer_name' => $user->name,
            'seller_id' => $validated['seller_id'],
            'product_id' => $validated['product_id'],
            'amount' => $validated['amount'],
            'delivery_days' => $validated['delivery_days'],
            'status' => 'pending_payment',
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addDays($validated['delivery_days'])->toIso8601String(),
        ];

        \Cache::put('escrow:' . $escrowId, $escrow, 259200);

        return response()->json([
            'message' => 'Mkataba wa escrow umeundwa',
            'escrow' => $escrow,
            'payment_instructions' => [
                'method' => 'Mkulima Pay',
                'amount' => $validated['amount'],
                'reference' => $escrowId,
            ],
        ]);
    }

    public function myEscrows()
    {
        $user = Auth::user();

        $escrows = [
            [
                'id' => 'ESC-123456',
                'type' => 'buying',
                'product_name' => 'Mbegu za Mahindi',
                'other_party' => 'Juma Mfugaji',
                'amount' => 75000,
                'status' => 'in_escrow',
                'created_at' => '2025-06-05T10:00:00Z',
                'expires_at' => '2025-06-12T10:00:00Z',
            ],
            [
                'id' => 'ESC-123455',
                'type' => 'selling',
                'product_name' => 'Mbolea ya CAN',
                'other_party' => 'Asha Mkuu',
                'amount' => 120000,
                'status' => 'completed',
                'created_at' => '2025-06-01T10:00:00Z',
                'completed_at' => '2025-06-04T10:00:00Z',
            ],
        ];

        return response()->json(['escrows' => $escrows]);
    }

    public function release($escrowId)
    {
        $escrow = \Cache::get('escrow:' . $escrowId);

        if (!$escrow) {
            return response()->json(['message' => 'Escrow haipatikani'], 404);
        }

        $escrow['status'] = 'completed';
        $escrow['completed_at'] = now()->toIso8601String();
        \Cache::put('escrow:' . $escrowId, $escrow, 259200);

        return response()->json([
            'message' => 'Malipo yametolewa kwa mwenza',
            'escrow' => $escrow,
        ]);
    }

    public function dispute(Request $request, $escrowId)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'required|string',
        ]);

        $escrow = \Cache::get('escrow:' . $escrowId);

        if (!$escrow) {
            return response()->json(['message' => 'Escrow haipatikani'], 404);
        }

        $escrow['status'] = 'disputed';
        $escrow['dispute'] = [
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'created_at' => now()->toIso8601String(),
        ];
        \Cache::put('escrow:' . $escrowId, $escrow, 259200);

        return response()->json([
            'message' => 'Malalamiko yamewasilishwa',
            'escrow' => $escrow,
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total_escrows' => 156,
            'completed' => 142,
            'in_progress' => 12,
            'disputed' => 2,
            'total_value' => 4500000,
            'success_rate' => 98.7,
        ]);
    }
}
