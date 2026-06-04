<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    /**
     * List all products with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'seller'])
            ->latest();

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

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->input('seller_id'));
        }

        if ($request->has('stock_low')) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
        }

        $products = $query->paginate(50);

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'seller_id' => ['required', 'exists:users,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'in:kg,gram,litre,ml,piece,packet,bag,box'],
            'images' => ['nullable', 'array'],
            'images.*' => ['url'],
            'specifications' => ['nullable', 'array'],
            'is_featured' => ['boolean'],
            'status' => ['string', 'in:active,inactive,out_of_stock'],
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['slug'] = Str::slug($validated['name']);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product->load('category', 'seller'),
        ], 201);
    }

    /**
     * Show product details
     */
    public function show(string $uuid): JsonResponse
    {
        $product = Product::where('uuid', $uuid)
            ->with(['category', 'seller', 'orderItems'])
            ->firstOrFail();

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['sometimes', 'string', 'in:kg,gram,litre,ml,piece,packet,bag,box'],
            'images' => ['nullable', 'array'],
            'images.*' => ['url'],
            'specifications' => ['nullable', 'array'],
            'is_featured' => ['boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive,out_of_stock'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh()->load('category', 'seller'),
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(string $uuid): JsonResponse
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['string', 'exists:products,uuid'],
        ]);

        $count = Product::whereIn('uuid', $validated['uuids'])->delete();

        return response()->json([
            'message' => "{$count} products deleted successfully.",
            'deleted_count' => $count,
        ]);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['string', 'exists:products,uuid'],
            'status' => ['required', 'string', 'in:active,inactive,out_of_stock'],
        ]);

        $count = Product::whereIn('uuid', $validated['uuids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'message' => "{$count} products updated to {$validated['status']}.",
            'updated_count' => $count,
        ]);
    }

    /**
     * Export products
     */
    public function export(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'seller']);

        if ($request->has('uuids')) {
            $query->whereIn('uuid', $request->input('uuids'));
        }

        $products = $query->get()->map(function ($product) {
            return [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'category' => $product->category->name,
                'seller' => $product->seller->name,
                'price' => $product->price,
                'stock' => $product->stock_quantity,
                'unit' => $product->unit,
                'status' => $product->status,
                'created_at' => $product->created_at,
            ];
        });

        return response()->json([
            'products' => $products,
            'export_format' => 'json',
            'total' => $products->count(),
        ]);
    }

    /**
     * Get low stock products
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->orWhere(function ($q) {
                $q->where('stock_quantity', '<=', 10)
                  ->whereNull('min_stock_level');
            })
            ->with(['category', 'seller'])
            ->get();

        return response()->json([
            'products' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Get categories for catalog
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
