<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    protected const SELLER_ROLES = ['seller', 'agrodealer', 'admin', 'superadmin'];

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, self::SELLER_ROLES, true)) {
            return response()->json([
                'message' => 'Access denied. Only sellers can access this endpoint.',
            ], 403);
        }

        $sellerId = $user->id;

        $totalProducts = Product::where('user_id', $sellerId)->count();
        $activeProducts = Product::where('user_id', $sellerId)->where('status', 'active')->count();
        $outOfStock = Product::where('user_id', $sellerId)->where('stock_quantity', '<=', 0)->count();

        $totalOrders = Order::where('seller_id', $sellerId)->count();
        $pendingOrders = Order::where('seller_id', $sellerId)->where('status', 'pending')->count();
        $completedOrders = Order::where('seller_id', $sellerId)->where('status', 'delivered')->count();

        $totalRevenue = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->sum('total');

        $monthlyRevenue = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $recentOrders = Order::where('seller_id', $sellerId)
            ->with(['buyer', 'items'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'uuid' => $order->uuid,
                'status' => $order->status,
                'total' => $order->total,
                'buyer_name' => $order->buyer->name ?? 'Unknown',
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->toIso8601String(),
            ]);

        return response()->json([
            'stats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'out_of_stock' => $outOfStock,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ],
            'recent_orders' => $recentOrders,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'products' => $products->items(),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = Order::where('seller_id', $request->user()->id)
            ->with(['buyer', 'items'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'orders' => $orders->items(),
            'total' => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }
}
