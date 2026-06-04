<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    /**
     * Search products for POS
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = Product::where('status', 'active')
            ->with('category', 'seller');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->limit(50)->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Create a POS order (field sale)
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,m-pesa,tigo_pesa,escrow'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
        ]);

        $vatRate = $validated['vat_rate'] ?? 18;
        $discount = $validated['discount'] ?? 0;

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $vatAmount = round($subtotal * ($vatRate / 100), 2);
        $total = $subtotal + $vatAmount - $discount;

        // Create or find customer
        $customer = User::firstOrCreate(
            ['phone' => $validated['customer_phone']],
            [
                'name' => $validated['customer_name'],
                'role' => 'buyer',
                'status' => 'active',
                'tenant_id' => $request->user()->tenant_id,
                'uuid' => Str::uuid(),
            ]
        );

        $order = DB::transaction(function () use ($validated, $customer, $subtotal, $vatAmount, $total, $discount, $vatRate) {
            $order = Order::create([
                'uuid' => Str::uuid(),
                'tenant_id' => $customer->tenant_id,
                'buyer_id' => $customer->id,
                'seller_id' => $validated['items'][0]['product_id'], // Will be updated
                'status' => $validated['payment_method'] === 'cash' ? 'completed' : 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cash' ? 'paid' : 'pending',
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'vat_rate' => $vatRate,
                'discount' => $discount,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'source' => 'pos',
                'location' => $validated['location'] ?? null,
                'processed_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'seller_id' => $product->seller_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                // Update stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Update seller_id to primary seller
            $primarySellerId = OrderItem::where('order_id', $order->id)->first()->seller_id;
            $order->update(['seller_id' => $primarySellerId]);

            return $order;
        });

        return response()->json([
            'message' => 'POS order created successfully.',
            'order' => $order->load('items.product', 'buyer'),
            'receipt' => [
                'order_number' => $order->uuid,
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $validated['customer_name'],
                'items' => $validated['items'],
                'subtotal' => $subtotal,
                'vat' => $vatAmount,
                'vat_rate' => $vatRate . '%',
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'processed_by' => auth()->user()->name,
            ],
        ], 201);
    }

    /**
     * Get POS receipt
     */
    public function receipt(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)
            ->where('source', 'pos')
            ->with(['items.product', 'buyer', 'processor'])
            ->firstOrFail();

        return response()->json([
            'receipt' => [
                'order_number' => $order->uuid,
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->buyer->name,
                'customer_phone' => $order->buyer->phone,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                    ];
                }),
                'subtotal' => $order->subtotal,
                'vat' => $order->vat_amount,
                'vat_rate' => $order->vat_rate . '%',
                'discount' => $order->discount,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'processed_by' => $order->processor?->name,
                'location' => $order->location,
                'notes' => $order->notes,
            ],
        ]);
    }

    /**
     * Get POS sales history
     */
    public function history(Request $request): JsonResponse
    {
        $query = Order::where('source', 'pos')
            ->with(['items.product', 'buyer'])
            ->latest();

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->has('processed_by')) {
            $query->where('processed_by', $request->input('processed_by'));
        }

        $orders = $query->paginate(20);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Get POS daily summary
     */
    public function dailySummary(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $summary = [
            'date' => $date,
            'total_sales' => Order::where('source', 'pos')->whereDate('created_at', $date)->count(),
            'total_revenue' => Order::where('source', 'pos')->whereDate('created_at', $date)->sum('total'),
            'cash_sales' => Order::where('source', 'pos')->whereDate('created_at', $date)->where('payment_method', 'cash')->sum('total'),
            'mobile_money_sales' => Order::where('source', 'pos')->whereDate('created_at', $date)->whereIn('payment_method', ['m-pesa', 'tigo_pesa'])->sum('total'),
            'items_sold' => OrderItem::whereHas('order', function ($q) use ($date) {
                $q->where('source', 'pos')->whereDate('created_at', $date);
            })->sum('quantity'),
        ];

        return response()->json($summary);
    }
}
