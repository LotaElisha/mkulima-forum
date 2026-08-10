<?php

namespace App\Http\Controllers\Api\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use App\Services\Community\CommunityChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityClickController extends Controller
{
    protected CommunityChannelService $channelService;

    public function __construct(CommunityChannelService $channelService)
    {
        $this->channelService = $channelService;
    }

    public function recordClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'nullable|integer|exists:community_channels,id',
            'channel_uuid' => 'nullable|string|exists:community_channels,uuid',
            'event' => 'required|string|in:channel_view,join_link_clicked,whatsapp_contact_clicked,social_platform_clicked',
            'anon_id' => 'nullable|string|max:64',
            'referrer' => 'nullable|string|max:255',
        ]);

        $channel = null;
        if (! empty($validated['channel_uuid'])) {
            $channel = CommunityChannel::where('uuid', $validated['channel_uuid'])->first();
        } elseif (! empty($validated['channel_id'])) {
            $channel = CommunityChannel::find($validated['channel_id']);
        }

        if (! $channel) {
            return response()->json(['status' => 'error', 'message' => 'Channel not found'], 404);
        }

        $click = $this->channelService->recordClick(
            $channel,
            $validated['event'],
            $validated['anon_id'] ?? null,
            $validated['referrer'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'event' => $click->event,
                'occurred_at' => $click->occurred_at->toIso8601String(),
            ],
        ]);
    }
}
