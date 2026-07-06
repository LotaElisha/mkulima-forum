<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

Route::prefix('seller')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        
        // Only sellers/agrodealers can access
        if (!in_array($user->role, ['seller', 'agrodealer', 'admin', 'superadmin'])) {
            return response()->json([
                'message' => 'Access denied. Only sellers can access this endpoint.'
            ], 403);
        }
        
        $sellerId = $user->id;
        
        // Product stats
        $totalProducts = Product::where('user_id', $sellerId)->count();
        $activeProducts = Product::where('user_id', $sellerId)->where('status', 'active')->count();
        $outOfStock = Product::where('user_id', $sellerId)->where('stock_quantity', '<=', 0)->count();
        
        // Order stats
        $totalOrders = Order::where('seller_id', $sellerId)->count();
        $pendingOrders = Order::where('seller_id', $sellerId)->where('status', 'pending')->count();
        $completedOrders = Order::where('seller_id', $sellerId)->where('status', 'delivered')->count();
        
        // Revenue
        $totalRevenue = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->sum('total');
            
        $monthlyRevenue = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        
        // Recent orders
        $recentOrders = Order::where('seller_id', $sellerId)
            ->with(['buyer', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'uuid' => $order->uuid,
                    'status' => $order->status,
                    'total' => $order->total,
                    'buyer_name' => $order->buyer->name ?? 'Unknown',
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->toIso8601String(),
                ];
            });
        
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
    });
    
    Route::get('/products', function (Request $request) {
        $user = $request->user();
        $products = Product::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['products' => $products]);
    });
    
    Route::get('/orders', function (Request $request) {
        $user = $request->user();
        $orders = Order::where('seller_id', $user->id)
            ->with(['buyer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['orders' => $orders]);
    });
});
