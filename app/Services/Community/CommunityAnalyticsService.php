<?php

namespace App\Services\Community;

use App\Models\AnalyticsEvent;
use App\Models\CommunityChannel;
use App\Models\CommunityChannelClick;
use Illuminate\Support\Facades\DB;

class CommunityAnalyticsService
{
    /**
     * Get aggregated community engagement metrics from the Event Bus.
     */
    public function getSummaryStats(): array
    {
        $totalChannels = CommunityChannel::count();
        $activeChannels = CommunityChannel::active()->count();
        $officialChannels = CommunityChannel::official()->count();

        $totalViews = CommunityChannelClick::where('event', 'channel_view')->count();
        $totalJoinClicks = CommunityChannelClick::where('event', 'join_link_clicked')->count();
        $totalWhatsappClicks = CommunityChannelClick::where('event', 'whatsapp_contact_clicked')->count();

        $topChannel = CommunityChannelClick::select('channel_id', DB::raw('count(*) as click_count'))
            ->groupBy('channel_id')
            ->orderByDesc('click_count')
            ->first();

        $topChannelName = $topChannel ? CommunityChannel::find($topChannel->channel_id)?->name : 'N/A';

        return [
            'total_channels' => $totalChannels,
            'active_channels' => $activeChannels,
            'official_channels' => $officialChannels,
            'total_views' => $totalViews,
            'total_join_clicks' => $totalJoinClicks,
            'total_whatsapp_clicks' => $totalWhatsappClicks,
            'top_channel_name' => $topChannelName,
        ];
    }
}
