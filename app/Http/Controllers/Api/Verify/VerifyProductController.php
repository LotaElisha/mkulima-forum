<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Models\RegulatedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyProductController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $product = RegulatedProduct::with(['authority', 'category', 'manufacturer', 'recalls'])
            ->where('uuid', $id)
            ->orWhere('id', $id)
            ->orWhere('registration_number', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid' => $product->uuid,
                'trade_name' => $product->trade_name,
                'registration_number' => $product->registration_number,
                'active_ingredient' => $product->active_ingredient,
                'formulation' => $product->formulation,
                'permitted_crops' => $product->permitted_crops,
                'status' => $product->registration_status,
                'provenance' => $product->provenance,
                'confidence' => $product->confidence,
                'as_of' => $product->as_of ? $product->as_of->toIso8601String() : null,
                'authority' => $product->authority ? [
                    'name' => $product->authority->name,
                    'acronym' => $product->authority->acronym,
                    'display_note' => $product->authority->display_note,
                ] : null,
                'category' => $product->category?->name,
                'manufacturer' => $product->manufacturer?->name,
            ],
        ]);
    }

    public function seedVarieties(Request $request): JsonResponse
    {
        $query = RegulatedProduct::whereHas('category', function ($q) {
            $q->where('code', 'SEED');
        })->where('registration_status', 'REGISTERED');

        if ($request->filled('q')) {
            $query->where('trade_name', 'like', "%{$request->q}%");
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(20),
        ]);
    }

    public function pesticides(Request $request): JsonResponse
    {
        $query = RegulatedProduct::whereHas('category', function ($q) {
            $q->where('code', 'PESTICIDE');
        });

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('trade_name', 'like', "%{$request->q}%")
                  ->orWhere('active_ingredient', 'like', "%{$request->q}%");
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(20),
        ]);
    }
}
