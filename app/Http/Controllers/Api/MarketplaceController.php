<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Escrow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    /**
     * List categories
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon', 'description']);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * List products with filters
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['seller:id,uuid,name', 'category:id,name,slug'])
            ->active()
            ->where('status', 'active');

        // Category filter
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Verified only
        if ($request->boolean('verified')) {
            $query->where('is_verified', true);
        }

        // In stock
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get single product
     */
    public function product(string $uuid): JsonResponse
    {
        $product = Product::with(['seller:id,uuid,name,phone', 'category:id,name,slug'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Create product (agrodealer only)
     */
    public function createProduct(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAgrodealer()) {
            return response()->json([
                'message' => 'Only verified agrodealers can list products.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
            'attributes' => ['nullable', 'array'],
        ]);

        $validated['user_id'] = $user->id;
        $validated['tenant_id'] = $user->tenant_id;
        $validated['currency'] = $user->tenant->currency ?? 'TZS';

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('products', 'public');
            }
            $validated['images'] = $images;
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $product = Product::where('uuid', $uuid)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:draft,active,out_of_stock,suspended'],
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function deleteProduct(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $product = Product::where('uuid', $uuid)->where('user_id', $user->id)->firstOrFail();

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Create order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_uuid' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_address' => ['required', 'array'],
            'delivery_address.region' => ['required', 'string'],
            'delivery_address.district' => ['required', 'string'],
            'delivery_address.ward' => ['required', 'string'],
            'delivery_address.street' => ['nullable', 'string'],
            'delivery_phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = DB::transaction(function () use ($validated, $user) {
            $subtotal = 0;
            $sellerId = null;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('uuid', $item['product_uuid'])->active()-\u003einStock()-\u003efirstOrFail();

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                if ($sellerId && $sellerId !== $product->user_id) {
                    throw new \Exception('All items must be from the same seller.');
                }

                $sellerId = $product->user_id;
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'image' => $product->images[0] ?? null,
                        'unit' => $product->unit,
                    ],
                ];

                // Decrement stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            $deliveryFee = 5000; // Fixed for now, should be dynamic
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'tenant_id' => $user->tenant_id,
                'buyer_id' => $user->id,
                'seller_id' => $sellerId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'currency' => $user->tenant->currency ?? 'TZS',
                'delivery_address' => $validated['delivery_address'],
                'delivery_phone' => $validated['delivery_phone'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            // Create escrow
            Escrow::create([
                'tenant_id' => $user->tenant_id,
                'order_id' => $order->id,
                'reference' => 'ESC-' . strtoupper(Str::random(10)),
                'status' => 'HELD',
                'amount' => $total,
                'currency' => $order->currency,
                'expires_at' => now()->addDays(7),
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load(['items', 'escrow']),
        ], 201);
    }

    /**
     * Get user orders
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->input('type', 'buyer'); // buyer or seller

        $query = Order::with(['items.product', 'escrow'])
            ->when($type === 'buyer', function ($q) use ($user) {
                $q->where('buyer_id', $user->id);
            })
            ->when($type === 'seller', function ($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single order
     */
    public function order(string $uuid): JsonResponse
    {
        $user = request()->user();
        $order = Order::with(['items.product', 'buyer:id,uuid,name,phone', 'seller:id,uuid,name,phone', 'escrow'])
            ->where('uuid', $uuid)
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            })
            ->firstOrFail();

        return response()->json([
            'order' => $order,
        ]);
    }
}
