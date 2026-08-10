<?php

namespace Database\Seeders;

use App\Models\RegulatoryAuthority;
use App\Models\RegulatoryDataSource;
use Illuminate\Database\Seeder;

class RegulatoryAuthoritySeeder extends Seeder
{
    public function run(): void
    {
        $authorities = [
            [
                'name' => 'Tanzania Plant Health and Pesticides Authority',
                'acronym' => 'TPHPA',
                'country' => 'TZ',
                'product_categories' => ['PESTICIDE'],
                'display_note' => 'TPHPA Official Pesticide Register Record',
            ],
            [
                'name' => 'Tanzania Official Seed Certification Institute',
                'acronym' => 'TOSCI',
                'country' => 'TZ',
                'product_categories' => ['SEED'],
                'display_note' => 'TOSCI National Seed Register Record',
            ],
            [
                'name' => 'Tanzania Fertilizer Regulatory Authority',
                'acronym' => 'TFRA',
                'country' => 'TZ',
                'product_categories' => ['FERTILIZER'],
                'display_note' => 'TFRA Registered Fertilizer Record',
            ],
            [
                'name' => 'Tanzania Bureau of Standards',
                'acronym' => 'TBS',
                'country' => 'TZ',
                'product_categories' => ['SEED', 'FERTILIZER', 'PESTICIDE', 'EQUIP'],
                'display_note' => 'TBS Certified Product Mark Record',
            ],
            [
                'name' => 'Ministry of Agriculture Tanzania',
                'acronym' => 'MAFC',
                'country' => 'TZ',
                'product_categories' => ['SEED', 'FERTILIZER', 'PESTICIDE', 'VET', 'EQUIP', 'OTHER'],
                'display_note' => 'Ministry of Agriculture Registry Dataset',
            ],
        ];

        foreach ($authorities as $authData) {
            $auth = RegulatoryAuthority::firstOrCreate(['acronym' => $authData['acronym']], $authData);

            RegulatoryDataSource::firstOrCreate(['authority_id' => $auth->id, 'name' => "{$auth->acronym} Internal Dataset"], [
                'authority_id' => $auth->id,
                'name' => "{$auth->acronym} Internal Registry List 2026",
                'source_url' => null,
                'api_endpoint' => null,
                'auth_type' => 'none',
                'backing_mode' => 'manual_import',
                'sync_interval_minutes' => 1440,
                'is_active' => true,
                'confidence_level' => 95,
            ]);
        }
    }
}
