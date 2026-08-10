<?php

namespace App\Http\Controllers\Api\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SocialLinksController extends Controller
{
    /**
     * Public endpoint for official social media links (B2).
     */
    public function socialLinks(Request $request): JsonResponse
    {
        $cacheKey = 'public_social_links_' . $request->header('Accept-Language', 'sw');

        $data = Cache::remember($cacheKey, 900, function () use ($request) {
            $locale = str_starts_with($request->header('Accept-Language', 'sw'), 'en') ? 'en' : 'sw';

            return CommunityChannel::active()
                ->where('channel_type', 'SOCIAL')
                ->orderBy('sort_order')
                ->get()
                ->map(function ($c) use ($locale) {
                    return [
                        'uuid' => $c->uuid,
                        'platform' => $c->platform,
                        'name' => $c->name,
                        'url' => $c->url,
                        'icon' => $c->icon,
                        'description' => $c->description[$locale] ?? $c->description['sw'] ?? '',
                        'is_official' => $c->is_official,
                    ];
                });
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'as_of' => now()->toIso8601String(),
        ])->header('ETag', md5(json_encode($data)));
    }

    /**
     * Public endpoint for community channel directory (B2/B4).
     */
    public function communityLinks(Request $request): JsonResponse
    {
        $locale = str_starts_with($request->header('Accept-Language', 'sw'), 'en') ? 'en' : 'sw';

        $query = CommunityChannel::active();

        if ($request->filled('type')) {
            $query->where('channel_type', strtoupper($request->type));
        }

        if ($request->filled('region_id')) {
            $query->where('geo_unit_id', $request->region_id);
        }

        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        $channels = $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($c) use ($locale) {
                return [
                    'uuid' => $c->uuid,
                    'platform' => $c->platform,
                    'channel_type' => $c->channel_type,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'url' => $c->url,
                    'click_to_chat_url' => $c->click_to_chat_url,
                    'phone_number' => $c->phone_number,
                    'icon' => $c->icon,
                    'description' => $c->description[$locale] ?? $c->description['sw'] ?? '',
                    'is_official' => $c->is_official,
                    'is_featured' => $c->is_featured,
                    'provenance' => $c->provenance,
                    'region' => $c->geoUnit?->name,
                    'crop' => $c->crop?->name,
                    'topic' => $c->topic?->name,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $channels,
            'as_of' => now()->toIso8601String(),
        ]);
    }
}
