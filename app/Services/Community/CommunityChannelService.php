<?php

namespace App\Services\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityChannelClick;
use App\Services\Spine\AuditTrail;
use App\Services\Spine\EventBus;
use App\Services\Spine\QrService;

class CommunityChannelService
{
    protected EventBus $eventBus;

    protected QrService $qrService;

    protected AuditTrail $auditTrail;

    public function __construct(EventBus $eventBus, QrService $qrService, AuditTrail $auditTrail)
    {
        $this->eventBus = $eventBus;
        $this->qrService = $qrService;
        $this->auditTrail = $auditTrail;
    }

    public function createChannel(array $data, ?int $userId = null): CommunityChannel
    {
        $channel = new CommunityChannel($data);
        $channel->created_by = $userId;
        $channel->save();

        $this->auditTrail->record($channel, 'created', null, ['name' => $channel->name, 'platform' => $channel->platform], null);

        return $channel;
    }

    public function updateChannel(CommunityChannel $channel, array $data, ?int $userId = null): CommunityChannel
    {
        $before = $channel->toArray();
        $channel->update($data);
        $channel->updated_by = $userId;
        $channel->save();

        $this->auditTrail->record($channel, 'updated', $before, $data, null);

        return $channel;
    }

    /**
     * Track a click or view event on a community channel (B9 & Rule 7).
     */
    public function recordClick(CommunityChannel $channel, string $event = 'join_link_clicked', ?string $anonId = null, ?string $referrer = null): CommunityChannelClick
    {
        $allowedEvents = ['channel_view', 'join_link_clicked', 'whatsapp_contact_clicked', 'social_platform_clicked'];
        if (! in_array($event, $allowedEvents)) {
            $event = 'join_link_clicked';
        }

        $click = CommunityChannelClick::create([
            'channel_id' => $channel->id,
            'actor_id' => auth()->id(),
            'anon_id' => $anonId,
            'event' => $event,
            'referrer' => $referrer,
            'occurred_at' => now(),
        ]);

        $this->eventBus->fire("channel.{$event}", [
            'subject_type' => CommunityChannel::class,
            'subject_id' => $channel->id,
            'actor_id' => auth()->id(),
            'anon_id' => $anonId,
            'provenance' => 'PLATFORM',
            'metadata' => ['platform' => $channel->platform, 'channel_name' => $channel->name],
        ]);

        return $click;
    }

    public function generateQrCode(CommunityChannel $channel): array
    {
        $targetUrl = $channel->click_to_chat_url ?? $channel->url;

        return $this->qrService->generate($targetUrl, 'community', $channel);
    }
}
