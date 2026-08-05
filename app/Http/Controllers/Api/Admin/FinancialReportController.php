<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * Get comprehensive financial reports
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->input('period', '30');
        $startDate = now()->subDays((int) $period);

        // Revenue by channel (online vs POS)
        $revenueByChannel = [
            'online' => Order::where('source', '!=', 'pos')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('total'),
            'pos' => Order::where('source', 'pos')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('total'),
        ];

        // Revenue trends
        $revenueTrends = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(CASE WHEN source = \'pos\' THEN total ELSE 0 END) as pos_revenue'),
                DB::raw('SUM(CASE WHEN source != \'pos\' THEN total ELSE 0 END) as online_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Registration timeline
        $registrationTimeline = User::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as registrations'),
                DB::raw('SUM(CASE WHEN role = \'farmer\' THEN 1 ELSE 0 END) as farmers'),
                DB::raw('SUM(CASE WHEN role = \'agrodealer\' THEN 1 ELSE 0 END) as agrodealers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment method breakdown
        $paymentMethods = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        // Product performance
        $topProducts = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->with('items.product')
            ->get()
            ->pluck('items')
            ->flatten()
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'product_id' => $first->product_id,
                    'product_name' => $first->product?->name ?? 'Unknown',
                    'quantity_sold' => $items->sum('quantity'),
                    'revenue' => $items->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // Summary stats
        $summary = [
            'total_revenue' => Order::where('created_at', '>=', $startDate)->where('status', 'completed')->sum('total'),
            'total_orders' => Order::where('created_at', '>=', $startDate)->where('status', 'completed')->count(),
            'avg_order_value' => Order::where('created_at', '>=', $startDate)->where('status', 'completed')->avg('total') ?? 0,
            'total_vat' => Order::where('created_at', '>=', $startDate)->where('status', 'completed')->sum('vat_amount'),
            'total_discounts' => Order::where('created_at', '>=', $startDate)->where('status', 'completed')->sum('discount'),
            'new_registrations' => User::where('created_at', '>=', $startDate)->count(),
            'new_products' => Product::where('created_at', '>=', $startDate)->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'revenue_by_channel' => $revenueByChannel,
            'revenue_trends' => $revenueTrends,
            'registration_timeline' => $registrationTimeline,
            'payment_methods' => $paymentMethods,
            'top_products' => $topProducts,
            'period' => $period,
        ]);
    }

    /**
     * Get daily sales report
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $report = [
            'date' => $date,
            'total_sales' => Order::whereDate('created_at', $date)->where('status', 'completed')->count(),
            'total_revenue' => Order::whereDate('created_at', $date)->where('status', 'completed')->sum('total'),
            'online_sales' => Order::whereDate('created_at', $date)->where('status', 'completed')->where('source', '!=', 'pos')->count(),
            'online_revenue' => Order::whereDate('created_at', $date)->where('status', 'completed')->where('source', '!=', 'pos')->sum('total'),
            'pos_sales' => Order::whereDate('created_at', $date)->where('status', 'completed')->where('source', 'pos')->count(),
            'pos_revenue' => Order::whereDate('created_at', $date)->where('status', 'completed')->where('source', 'pos')->sum('total'),
            'vat_collected' => Order::whereDate('created_at', $date)->where('status', 'completed')->sum('vat_amount'),
            'discounts_given' => Order::whereDate('created_at', $date)->where('status', 'completed')->sum('discount'),
        ];

        return response()->json($report);
    }
}
