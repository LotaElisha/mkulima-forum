<?php

namespace App\Http\Controllers\Api\Admin\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use App\Services\Community\CommunityAnalyticsService;
use App\Services\Community\CommunityChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCommunityController extends Controller
{
    protected CommunityChannelService $channelService;

    protected CommunityAnalyticsService $analyticsService;

    public function __construct(CommunityChannelService $channelService, CommunityAnalyticsService $analyticsService)
    {
        $this->channelService = $channelService;
        $this->analyticsService = $analyticsService;
    }

    public function index(): JsonResponse
    {
        $channels = CommunityChannel::with(['geoUnit', 'crop', 'topic'])
            ->orderBy('sort_order')
            ->get();

        $stats = $this->analyticsService->getSummaryStats();

        return response()->json([
            'status' => 'success',
            'data' => $channels,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|in:'.implode(',', CommunityChannel::PLATFORMS),
            'channel_type' => 'required|string|in:'.implode(',', CommunityChannel::CHANNEL_TYPES),
            'url' => 'nullable|url',
            'phone_number' => 'nullable|string|max:32',
            'description_sw' => 'nullable|string',
            'description_en' => 'nullable|string',
            'default_greeting_sw' => 'nullable|string',
            'default_greeting_en' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_official' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_alert_channel' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'geo_unit_id' => 'nullable|integer|exists:geo_units,id',
            'crop_id' => 'nullable|integer|exists:crops,id',
            'topic_id' => 'nullable|integer|exists:agricultural_topics,id',
        ]);

        $payload = [
            'name' => $validated['name'],
            'platform' => $validated['platform'],
            'channel_type' => $validated['channel_type'],
            'url' => $validated['url'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'description' => [
                'sw' => $validated['description_sw'] ?? '',
                'en' => $validated['description_en'] ?? '',
            ],
            'default_greeting' => [
                'sw' => $validated['default_greeting_sw'] ?? 'Habari Mkulima Forum, nahitaji msaada...',
                'en' => $validated['default_greeting_en'] ?? 'Hello Mkulima Forum, I need assistance...',
            ],
            'icon' => $validated['icon'] ?? 'chat',
            'is_official' => $validated['is_official'] ?? false,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_alert_channel' => $validated['is_alert_channel'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
            'geo_unit_id' => $validated['geo_unit_id'] ?? null,
            'crop_id' => $validated['crop_id'] ?? null,
            'topic_id' => $validated['topic_id'] ?? null,
        ];

        $channel = $this->channelService->createChannel($payload, auth()->id());

        return response()->json(['status' => 'success', 'data' => $channel], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $channel = CommunityChannel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'platform' => 'sometimes|string',
            'channel_type' => 'sometimes|string',
            'url' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'description_sw' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_official' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_alert_channel' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['description_sw']) || isset($validated['description_en'])) {
            $validated['description'] = [
                'sw' => $validated['description_sw'] ?? ($channel->description['sw'] ?? ''),
                'en' => $validated['description_en'] ?? ($channel->description['en'] ?? ''),
            ];
            unset($validated['description_sw'], $validated['description_en']);
        }

        $updated = $this->channelService->updateChannel($channel, $validated, auth()->id());

        return response()->json(['status' => 'success', 'data' => $updated]);
    }

    public function destroy(string $id): JsonResponse
    {
        $channel = CommunityChannel::findOrFail($id);
        $channel->delete();

        return response()->json(['status' => 'success', 'message' => 'Channel deleted']);
    }

    public function generateQr(string $id): JsonResponse
    {
        $channel = CommunityChannel::findOrFail($id);
        $qrData = $this->channelService->generateQrCode($channel);

        return response()->json(['status' => 'success', 'data' => $qrData]);
    }
}
