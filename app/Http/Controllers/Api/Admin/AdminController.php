<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Escrow;
use App\Models\DiseaseScan;
use App\Models\ForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'farmers' => User::where('role', 'farmer')->count(),
                'agrodealers' => User::where('role', 'agrodealer')->count(),
                'experts' => User::whereIn('role', ['agronomist', 'veterinary'])->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'paid' => Order::where('status', 'paid')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total'),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'out_of_stock' => Product::where('status', 'out_of_stock')->count(),
            ],
            'escrow' => [
                'total_held' => Escrow::where('status', 'held')->sum('amount'),
                'total_released' => Escrow::where('status', 'released')->sum('amount'),
                'pending_count' => Escrow::where('status', 'pending')->count(),
            ],
            'engagement' => [
                'scans_today' => DiseaseScan::whereDate('created_at', today())->count(),
                'threads_today' => ForumThread::whereDate('created_at', today())->count(),
                'active_users' => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            ],
        ];

        return response()->json($stats);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::with('tenant')->latest();

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('kyc_status')) {
            $query->where('kyc_status', $request->input('kyc_status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        return response()->json($query->paginate(50));
    }

    public function updateUser(Request $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|in:farmer,agrodealer,agronomist,veterinary,admin',
            'kyc_status' => 'sometimes|string|in:pending,verified,rejected',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated',
            'user' => $user,
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $query = Order::with(['buyer', 'seller'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate(50));
    }

    public function escrows(Request $request): JsonResponse
    {
        $query = Escrow::with(['buyer', 'seller', 'order'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate(50));
    }

    public function releaseEscrow(Request $request, string $uuid): JsonResponse
    {
        $escrow = Escrow::where('uuid', $uuid)->firstOrFail();

        $service = new \App\Services\Payments\EscrowService();
        $result = $service->releaseFunds($escrow);

        return response()->json($result);
    }

    public function refundEscrow(Request $request, string $uuid): JsonResponse
    {
        $escrow = Escrow::where('uuid', $uuid)->firstOrFail();

        $service = new \App\Services\Payments\EscrowService();
        $result = $service->refundBuyer($escrow, $request->input('reason', 'Admin initiated refund'));

        return response()->json($result);
    }

    public function kycPending(): JsonResponse
    {
        $users = User::where('kyc_status', 'pending')
            ->with('tenant')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function verifyKyc(Request $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->update(['kyc_status' => 'verified']);

        return response()->json([
            'message' => 'KYC verified',
            'user' => $user,
        ]);
    }

    public function rejectKyc(Request $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->update([
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'message' => 'KYC rejected',
            'user' => $user,
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        $dailyUsers = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyOrders = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json([
            'daily_users' => $dailyUsers,
            'daily_orders' => $dailyOrders,
            'top_products' => $topProducts,
        ]);
    }
}
