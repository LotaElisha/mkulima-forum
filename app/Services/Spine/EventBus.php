<?php

namespace App\Services\Spine;

use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\Log;

class EventBus
{
    /**
     * Record an event to the append-only analytics_events log.
     */
    public function fire(string $event, array $payload = []): AnalyticsEvent
    {
        $provenance = $payload['provenance'] ?? 'PLATFORM';
        if (! in_array($provenance, AnalyticsEvent::PROVENANCES)) {
            $provenance = 'PLATFORM';
        }

        $analyticsEvent = new AnalyticsEvent([
            'event' => $event,
            'actor_id' => $payload['actor_id'] ?? auth()->id(),
            'anon_id' => $payload['anon_id'] ?? null,
            'subject_type' => $payload['subject_type'] ?? null,
            'subject_id' => $payload['subject_id'] ?? null,
            'geo_unit_id' => $payload['geo_unit_id'] ?? null,
            'crop_id' => $payload['crop_id'] ?? null,
            'channel' => $payload['channel'] ?? null,
            'provenance' => $provenance,
            'metadata' => $payload['metadata'] ?? [],
            'occurred_at' => $payload['occurred_at'] ?? now(),
        ]);

        $analyticsEvent->save();

        return $analyticsEvent;
    }
}
