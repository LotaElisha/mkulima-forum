<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Seller\SellerStatus;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    // Was a second, hand-maintained copy of Roles::SELLERS. Two lists of the
    // same thing drift, and the one that drifts is the one nobody is looking
    // at when a role is added.

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($denied = $this->denyIfNotSeller($user)) {
            return $denied;
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
        // These two had no authorization check at all - only dashboard() did.
        // Scoping by user_id meant no data leaked, but a farmer calling them
        // got an empty success where the dashboard gave a 403, and the app
        // could not tell "you are not a seller" from "you have no products".
        if ($denied = $this->denyIfNotSeller($request->user())) {
            return $denied;
        }

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
        if ($denied = $this->denyIfNotSeller($request->user())) {
            return $denied;
        }

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

    /**
     * Refuse a non-seller, and say what to do about it.
     *
     * The old response was `Access denied. Only sellers can access this
     * endpoint.` with no machine-readable state, so the app could neither
     * explain it nor route the farmer to the application form. The body now
     * carries the same seller payload as /api/seller/status.
     */
    private function denyIfNotSeller($user): ?JsonResponse
    {
        if (in_array($user->role, Roles::SELLERS, true)) {
            return null;
        }

        return response()->json([
            'message' => __('seller.not_a_seller'),
            'seller' => app(SellerStatus::class)->payload($user),
        ], 403);
    }
}
