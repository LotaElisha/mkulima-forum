<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // Phase 1 Features
            [
                'key' => 'weather',
                'name' => 'Weather Alerts',
                'description' => 'Real-time weather updates and farming tips',
                'category' => 'phase1',
                'enabled' => true,
            ],
            [
                'key' => 'sms_ussd',
                'name' => 'SMS/USSD',
                'description' => 'SMS commands for market prices and weather',
                'category' => 'phase1',
                'enabled' => true,
            ],
            [
                'key' => 'wallet',
                'name' => 'Mkulima Pay',
                'description' => 'Digital wallet for payments',
                'category' => 'phase1',
                'enabled' => true,
            ],
            [
                'key' => 'ivr',
                'name' => 'IVR Voice',
                'description' => 'Voice call services',
                'category' => 'phase1',
                'enabled' => true,
            ],
            // Phase 2 Features
            [
                'key' => 'ai_agronomist',
                'name' => 'AI Agronomist',
                'description' => 'AI-powered farming advice',
                'category' => 'phase2',
                'enabled' => true,
            ],
            [
                'key' => 'crop_scanner',
                'name' => 'Crop Scanner',
                'description' => 'AI crop health diagnosis',
                'category' => 'phase2',
                'enabled' => true,
            ],
            [
                'key' => 'price_prediction',
                'name' => 'Price Prediction',
                'description' => 'AI market price forecasting',
                'category' => 'phase2',
                'enabled' => true,
            ],
            [
                'key' => 'notifications',
                'name' => 'Smart Notifications',
                'description' => 'Push notifications for weather, prices, orders',
                'category' => 'phase2',
                'enabled' => true,
            ],
            // Phase 3 Features
            [
                'key' => 'offline_mode',
                'name' => 'Offline Mode',
                'description' => 'Browse without internet connection',
                'category' => 'phase3',
                'enabled' => true,
            ],
            [
                'key' => 'community_groups',
                'name' => 'Community Groups',
                'description' => 'Farmer groups and cooperatives',
                'category' => 'phase3',
                'enabled' => true,
            ],
            [
                'key' => 'live_streaming',
                'name' => 'Live Streaming',
                'description' => 'Live broadcasts for farmers',
                'category' => 'phase3',
                'enabled' => true,
            ],
            [
                'key' => 'blockchain_certificates',
                'name' => 'Blockchain Certificates',
                'description' => 'Tamper-proof organic certificates',
                'category' => 'phase3',
                'enabled' => true,
            ],
            // Phase 4 Features
            [
                'key' => 'drone_services',
                'name' => 'Drone Services',
                'description' => 'Drone spraying and mapping',
                'category' => 'phase4',
                'enabled' => true,
            ],
            [
                'key' => 'iot_sensors',
                'name' => 'IoT Sensors',
                'description' => 'Soil moisture and weather sensors',
                'category' => 'phase4',
                'enabled' => true,
            ],
            [
                'key' => 'yield_estimation',
                'name' => 'Yield Estimation',
                'description' => 'AI harvest prediction',
                'category' => 'phase4',
                'enabled' => true,
            ],
            [
                'key' => 'escrow',
                'name' => 'Mkulima Escrow',
                'description' => 'Secure payment escrow',
                'category' => 'phase4',
                'enabled' => true,
            ],
        ];

        foreach ($features as $feature) {
            FeatureFlag::firstOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }
    }
}
