<?php

namespace App\Services\Verify;

use App\Models\Advisory;
use App\Models\AdvisoryDelivery;
use App\Models\CommunityChannel;
use App\Models\OutboundMessage;
use App\Services\Spine\AuditTrail;
use App\Services\Spine\ChannelBus;
use App\Services\Spine\TaxonomyService;

class AdvisoryService
{
    protected ChannelBus $channelBus;

    protected TaxonomyService $taxonomyService;

    protected AuditTrail $auditTrail;

    public function __construct(ChannelBus $channelBus, TaxonomyService $taxonomyService, AuditTrail $auditTrail)
    {
        $this->channelBus = $channelBus;
        $this->taxonomyService = $taxonomyService;
        $this->auditTrail = $auditTrail;
    }

    /**
     * Compose and dispatch an Advisory across targeted channels (A17 & Part C).
     */
    public function dispatchAdvisory(Advisory $advisory): Advisory
    {
        $advisory->status = 'SENT';
        $advisory->sent_at = now();
        $advisory->save();

        $channels = $advisory->channel_targets ?? ['push', 'sms', 'in_app'];

        // 1. Dispatch via Outbound Channel Bus
        $msg = new OutboundMessage([
            'channel_driver' => $channels[0] ?? 'push',
            'payload' => [
                'title' => $advisory->title['sw'] ?? $advisory->title['en'] ?? 'Tahadhari ya Kilimo',
                'body' => $advisory->body['sw'] ?? $advisory->body['en'] ?? '',
                'type' => $advisory->type,
            ],
        ]);

        $this->channelBus->send($msg, [
            'geo_unit_ids' => $advisory->geo_unit_ids,
            'crop_ids' => $advisory->crop_ids,
        ], $channels);

        // Part C Join Point: Target WhatsApp Alert Channels & Matching Local Groups
        $alertChannels = CommunityChannel::alertChannels();
        if ($advisory->geo_unit_ids && count($advisory->geo_unit_ids) > 0) {
            $alertChannels->whereIn('geo_unit_id', $advisory->geo_unit_ids);
        }

        $matchingChannels = $alertChannels->get();

        foreach ($channels as $driver) {
            AdvisoryDelivery::create([
                'advisory_id' => $advisory->id,
                'channel_driver' => $driver,
                'recipient_count' => $matchingChannels->count() + 1,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        $this->auditTrail->record($advisory, 'sent', null, ['sent_at' => now()->toIso8601String()]);

        return $advisory;
    }
}
