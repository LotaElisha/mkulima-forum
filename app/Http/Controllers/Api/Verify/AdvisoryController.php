<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Models\Advisory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Advisory::where('status', 'SENT');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $advisories = $query->orderByDesc('sent_at')->paginate(15);
        $locale = str_starts_with($request->header('Accept-Language', 'sw'), 'en') ? 'en' : 'sw';

        $transformed = $advisories->through(function ($ad) use ($locale) {
            return [
                'uuid' => $ad->uuid,
                'type' => $ad->type,
                'title' => $ad->title[$locale] ?? $ad->title['sw'] ?? $ad->title['en'] ?? '',
                'body' => $ad->body[$locale] ?? $ad->body['sw'] ?? $ad->body['en'] ?? '',
                'sent_at' => $ad->sent_at ? $ad->sent_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $transformed,
        ]);
    }
}
