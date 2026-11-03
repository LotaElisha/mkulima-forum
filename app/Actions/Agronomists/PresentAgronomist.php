<?php

namespace App\Actions\Agronomists;

use App\Models\ServiceProvider;
use App\Support\Nearby;

/**
 * Public shape of an agronomist. No phone, email or licence number: a farmer
 * reaches an agronomist through a booking, which the agronomist accepts.
 */
class PresentAgronomist
{
    public static function card(ServiceProvider $provider): array
    {
        return [
            'uuid' => $provider->uuid,
            'name' => $provider->business_name ?: ($provider->user?->name ?? 'Mtaalamu'),
            'bio' => $provider->bio,
            'specializations' => array_values($provider->specializations ?? []),
            'region' => $provider->region,
            'district' => $provider->district,
            'consultation_fee' => $provider->consultation_fee !== null ? (float) $provider->consultation_fee : null,
            'visit_fee' => $provider->visit_fee !== null ? (float) $provider->visit_fee : null,
            'rating' => $provider->rating_count > 0 ? (float) $provider->rating : null,
            'rating_count' => (int) $provider->rating_count,
            'verified' => $provider->verification_status === 'verified',
            'distance_km' => Nearby::km($provider->getAttribute('distance_km')),
        ];
    }

    public static function detail(ServiceProvider $provider): array
    {
        return self::card($provider) + [
            'availability' => $provider->availability ?? (object) [],
        ];
    }
}
