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
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total'),
            'active_escrows' => Escrow::where('status', 'held')->count(),
            'pending_kyc' => User::where('kyc_status', 'pending')->count(),
            'active_orders' => Order::whereIn('status', ['pending', 'paid', 'processing'])->count(),
            'escrow_holdings' => Escrow::where('status', 'held')->sum('amount'),
            'new_today' => User::whereDate('created_at', today())->count(),
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

    public function showUser(string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)
            ->with('tenant')
            ->firstOrFail();

        return response()->json([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ]);
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

        $users = $query->paginate(50);

        return response()->json([
            'users' => $users
        ]);
    }

    public function updateUser(Request $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|regex:/^255[0-9]{9}$/|unique:users,phone,' . $user->id,
            'role' => 'sometimes|string|' . \App\Support\Roles::rule(),
            'kyc_status' => 'sometimes|string|in:pending,verified,rejected,not_submitted',
            'status' => 'sometimes|string|in:active,suspended,terminated',
            'preferred_language' => 'sometimes|string|in:sw,en,lg,rw,fr',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|regex:/^255[0-9]{9}$/|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|' . \App\Support\Roles::rule(\App\Support\Roles::SELF_REGISTERABLE),
            'tenant_id' => 'required|exists:tenants,id',
            'kyc_status' => 'string|in:pending,verified,rejected,not_submitted',
            'status' => 'string|in:active,suspended',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['uuid'] = \Illuminate\Support\Str::uuid();
        $validated['kyc_status'] = $validated['kyc_status'] ?? 'not_submitted';
        $validated['status'] = $validated['status'] ?? 'active';

        $user = User::create($validated);
        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function deleteUser(string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $query = Order::with(['buyer', 'seller'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(50);

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function showOrder(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)
            ->with(['buyer', 'seller', 'items.product'])
            ->firstOrFail();

        return response()->json([
            'order' => $order
        ]);
    }

    public function updateOrder(Request $request, string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,paid,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|string|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'delivery_phone' => 'nullable|string',
        ]);

        $order->update($validated);

        // Update timestamps based on status
        if ($request->input('status') === 'paid' && !$order->paid_at) {
            $order->update(['paid_at' => now()]);
        }
        if ($request->input('status') === 'shipped' && !$order->shipped_at) {
            $order->update(['shipped_at' => now()]);
        }
        if ($request->input('status') === 'delivered' && !$order->delivered_at) {
            $order->update(['delivered_at' => now()]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->fresh()->load('buyer', 'seller', 'items.product'),
        ]);
    }

    public function deleteOrder(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $order->items()->delete();
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

    public function escrows(Request $request): JsonResponse
    {
        $query = Escrow::with(['buyer', 'seller', 'order'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $escrows = $query->paginate(50);

        return response()->json([
            'escrows' => $escrows
        ]);
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

        return response()->json([
            'kyc' => $users
        ]);
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
