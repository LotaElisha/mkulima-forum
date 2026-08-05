<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tenants')->insert([
            [
                'country_code' => 'tz',
                'name' => 'Tanzania',
                'currency' => 'TZS',
                'timezone' => 'Africa/Dar_es_Salaam',
                'payment_providers' => json_encode(['mpesa', 'tigo_pesa', 'airtel_money', 'halopesa']),
                'settings' => json_encode([
                    'language' => 'sw',
                    'fallback_language' => 'en',
                    'regulator' => 'TFRA',
                    'extension_target_ratio' => '1:400',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'ke',
                'name' => 'Kenya',
                'currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'payment_providers' => json_encode(['mpesa', 'airtel_money']),
                'settings' => json_encode([
                    'language' => 'sw',
                    'fallback_language' => 'en',
                    'regulator' => 'KEPHIS',
                    'extension_target_ratio' => '1:600',
                ]),
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'ug',
                'name' => 'Uganda',
                'currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'payment_providers' => json_encode(['mtn_momo', 'airtel_money']),
                'settings' => json_encode([
                    'language' => 'lg',
                    'fallback_language' => 'en',
                    'regulator' => 'UNADA',
                ]),
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'rw',
                'name' => 'Rwanda',
                'currency' => 'RWF',
                'timezone' => 'Africa/Kigali',
                'payment_providers' => json_encode(['mtn_momo', 'airtel_money']),
                'settings' => json_encode([
                    'language' => 'rw',
                    'fallback_language' => 'fr',
                    'regulator' => 'RAB',
                ]),
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
