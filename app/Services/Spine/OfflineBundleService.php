<?php

namespace App\Services\Spine;

use App\Models\Advisory;
use App\Models\Agrodealer;
use App\Models\CommunityChannel;
use App\Models\Manufacturer;
use App\Models\RecallNotice;
use App\Models\RegulatedProduct;
use Illuminate\Support\Facades\Cache;

class OfflineBundleService
{
    /**
     * Generate or fetch the signed offline sync envelope bundle (1.7).
     */
    public function getSignedBundle(): array
    {
        return Cache::remember('offline_sync_bundle', 1800, function () {
            return $this->generateBundle();
        });
    }

    public function generateBundle(): array
    {
        $version = '1.0.' . time();
        $generatedAt = now()->toIso8601String();

        $payload = [
            'registered_products' => RegulatedProduct::where('registration_status', 'REGISTERED')
                ->select(['id', 'uuid', 'registration_number', 'trade_name', 'active_ingredient', 'provenance', 'confidence'])
                ->limit(500)->get()->toArray(),

            'prohibited_products' => RegulatedProduct::whereIn('registration_status', ['BANNED', 'WITHDRAWN', 'SUSPENDED'])
                ->select(['id', 'uuid', 'registration_number', 'trade_name', 'registration_status'])
                ->get()->toArray(),

            'recalls' => RecallNotice::where('status', 'ACTIVE')
                ->select(['id', 'product_id', 'recall_type', 'reason', 'affected_batches', 'provenance'])
                ->get()->toArray(),

            'seed_varieties' => RegulatedProduct::whereHas('category', function ($q) {
                $q->where('code', 'SEED');
            })->select(['id', 'uuid', 'registration_number', 'trade_name', 'permitted_crops'])->get()->toArray(),

            'manufacturers' => Manufacturer::where('is_verified', true)
                ->select(['id', 'uuid', 'name', 'country', 'provenance'])->get()->toArray(),

            'trusted_dealers' => Agrodealer::whereIn('status', ['MKULIMA_VERIFIED', 'REGULATOR_RECORD_MATCHED'])
                ->select(['id', 'uuid', 'business_name', 'regulator_licence_number', 'geo_unit_id', 'status'])->get()->toArray(),

            'community_channels' => CommunityChannel::where('is_active', true)
                ->select(['id', 'uuid', 'platform', 'channel_type', 'name', 'slug', 'url', 'phone_number', 'is_official', 'is_featured', 'provenance'])->get()->toArray(),

            'advisories' => Advisory::where('status', 'SENT')
                ->select(['id', 'uuid', 'type', 'title', 'body', 'channel_targets', 'sent_at'])->limit(20)->get()->toArray(),
        ];

        $signingKey = config('app.key') ?: 'mkulima_offline_secret_key_2026';
        $signature = hash_hmac('sha256', json_encode($payload), $signingKey);

        return [
            'bundle_version' => $version,
            'generated_at' => $generatedAt,
            'signature' => $signature,
            'payload' => $payload,
        ];
    }
}
