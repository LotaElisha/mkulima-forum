<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\Warehouse;
use App\Models\Transporter;
use App\Models\User;
use Illuminate\Support\Str;

class LogisticsServiceSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $serviceProvider = User::firstOrCreate(
            ['email' => 'services@demo.mkulima'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Mkulima Services Ltd',
                'phone' => '255713000002',
                'role' => 'agronomist',
                'kyc_status' => 'verified',
                'status' => 'active',
                'password' => bcrypt('demo123'),
            ]
        );

        $warehouseOperator = User::firstOrCreate(
            ['email' => 'warehouse@demo.mkulima'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Mkulima Warehousing Co',
                'phone' => '255713000003',
                'role' => 'warehouse',
                'kyc_status' => 'verified',
                'status' => 'active',
                'password' => bcrypt('demo123'),
            ]
        );

        $transporter = User::firstOrCreate(
            ['email' => 'transport@demo.mkulima'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Mkulima Transport',
                'phone' => '255713000004',
                'role' => 'driver',
                'kyc_status' => 'verified',
                'status' => 'active',
                'password' => bcrypt('demo123'),
            ]
        );

        $providers = [
            [
                'service_type' => 'veterinary',
                'business_name' => 'Dr. Mwanga Veterinary Services',
                'bio' => 'Mobile veterinary services for cattle, poultry, and goats.',
                'specializations' => ['cattle', 'poultry', 'goats'],
                'region' => 'Arusha',
                'district' => 'Arusha City',
                'consultation_fee' => 25000,
                'visit_fee' => 50000,
            ],
            [
                'service_type' => 'agronomist',
                'business_name' => 'Mtaalamu wa Kilimo - Dodoma',
                'bio' => 'Soil testing, crop advice, and pest management.',
                'specializations' => ['maize', 'beans', 'soil_testing'],
                'region' => 'Dodoma',
                'district' => 'Dodoma City',
                'consultation_fee' => 15000,
                'visit_fee' => 35000,
            ],
            [
                'service_type' => 'soil_testing',
                'business_name' => 'Rafiki Tractor Repairs',
                'bio' => 'Tractor and farm machinery repair and maintenance.',
                'specializations' => ['tractors', 'sprayers', 'generators'],
                'region' => 'Mbeya',
                'district' => 'Mbeya City',
                'consultation_fee' => 20000,
                'visit_fee' => 45000,
            ],
            [
                'service_type' => 'soil_testing',
                'business_name' => 'Maji Mazuri Irrigation Solutions',
                'bio' => 'Drip irrigation, sprinkler systems, and water pumps.',
                'specializations' => ['drip_irrigation', 'sprinklers', 'pumps'],
                'region' => 'Morogoro',
                'district' => 'Morogoro Municipal',
                'consultation_fee' => 18000,
                'visit_fee' => 40000,
            ],
            [
                'service_type' => 'veterinary',
                'business_name' => 'Arusha Livestock Clinic',
                'bio' => 'Vaccination and treatment services for livestock.',
                'specializations' => ['cattle', 'goats', 'poultry'],
                'region' => 'Arusha',
                'district' => 'Arusha Rural',
                'consultation_fee' => 22000,
                'visit_fee' => 48000,
            ],
        ];

        foreach ($providers as $i => $p) {
            $user = User::firstOrCreate(
                ['email' => 'services' . ($i + 1) . '@demo.mkulima'],
                [
                    'tenant_id' => $tenantId,
                    'name' => $p['business_name'] . ' Account',
                    'phone' => '2557130002' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'role' => 'agronomist',
                    'kyc_status' => 'verified',
                    'status' => 'active',
                    'password' => bcrypt('demo123'),
                ]
            );

            ServiceProvider::firstOrCreate(
                ['business_name' => $p['business_name'], 'tenant_id' => $tenantId],
                array_merge($p, [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'license_number' => 'LIC-' . strtoupper(Str::random(8)),
                    'verification_status' => 'verified',
                    'availability' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'rating' => 4.5,
                    'rating_count' => 12,
                    'is_active' => true,
                ])
            );
        }

        $warehouses = [
            [
                'name' => 'Arusha Grain Warehouse',
                'storage_type' => 'grain_silo',
                'region' => 'Arusha',
                'location' => 'Arusha City, near TFA Centre',
                'capacity_tons' => 500,
                'available_tons' => 200,
                'price_per_ton_month' => 15000,
                'features' => ['weatherproof', 'fumigation', '24h_security', 'weighbridge'],
            ],
            [
                'name' => 'Dodoma Maize Store',
                'storage_type' => 'dry',
                'region' => 'Dodoma',
                'location' => 'Dodoma City, Nyerere Road',
                'capacity_tons' => 350,
                'available_tons' => 150,
                'price_per_ton_month' => 12000,
                'features' => ['weatherproof', 'ventilation', 'security'],
            ],
            [
                'name' => 'Mbeya Cold Storage',
                'storage_type' => 'cold',
                'region' => 'Mbeya',
                'location' => 'Mbeya City, Mbarali Road',
                'capacity_tons' => 100,
                'available_tons' => 40,
                'price_per_ton_month' => 45000,
                'features' => ['refrigeration', 'weatherproof', '24h_security'],
            ],
            [
                'name' => 'Morogoro Produce Warehouse',
                'storage_type' => 'general',
                'region' => 'Morogoro',
                'location' => 'Morogoro Town, Kilosa Road',
                'capacity_tons' => 250,
                'available_tons' => 100,
                'price_per_ton_month' => 10000,
                'features' => ['weatherproof', 'parking', 'security'],
            ],
        ];

        foreach ($warehouses as $i => $w) {
            $user = User::firstOrCreate(
                ['email' => 'warehouse' . ($i + 1) . '@demo.mkulima'],
                [
                    'tenant_id' => $tenantId,
                    'name' => $w['name'] . ' Operator',
                    'phone' => '2557130003' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'role' => 'warehouse',
                    'kyc_status' => 'verified',
                    'status' => 'active',
                    'password' => bcrypt('demo123'),
                ]
            );

            Warehouse::firstOrCreate(
                ['name' => $w['name'], 'tenant_id' => $tenantId],
                array_merge($w, [
                    'uuid' => (string) Str::uuid(),
                    'operator_id' => $user->id,
                    'verification_status' => 'verified',
                    'is_active' => true,
                ])
            );
        }

        $transporters = [
            [
                'vehicle_type' => 'lorry',
                'plate_number' => 'T 123 ABC',
                'capacity_kg' => 7000,
                'base_region' => 'Dar es Salaam',
                'rating' => 4.3,
                'rating_count' => 8,
            ],
            [
                'vehicle_type' => 'canter',
                'plate_number' => 'T 456 DEF',
                'capacity_kg' => 15000,
                'base_region' => 'Arusha',
                'rating' => 4.6,
                'rating_count' => 15,
            ],
            [
                'vehicle_type' => 'pickup',
                'plate_number' => 'T 789 GHI',
                'capacity_kg' => 2000,
                'base_region' => 'Dodoma',
                'rating' => 4.1,
                'rating_count' => 5,
            ],
            [
                'vehicle_type' => 'canter',
                'plate_number' => 'T 321 JKL',
                'capacity_kg' => 12000,
                'base_region' => 'Mbeya',
                'rating' => 4.4,
                'rating_count' => 10,
            ],
            [
                'vehicle_type' => 'lorry',
                'plate_number' => 'T 654 MNO',
                'capacity_kg' => 8000,
                'base_region' => 'Mwanza',
                'rating' => 4.2,
                'rating_count' => 7,
            ],
        ];

        foreach ($transporters as $i => $t) {
            $user = User::firstOrCreate(
                ['email' => 'transport' . ($i + 1) . '@demo.mkulima'],
                [
                    'tenant_id' => $tenantId,
                    'name' => $t['plate_number'] . ' Driver',
                    'phone' => '2557130004' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'role' => 'driver',
                    'kyc_status' => 'verified',
                    'status' => 'active',
                    'password' => bcrypt('demo123'),
                ]
            );

            Transporter::firstOrCreate(
                ['plate_number' => $t['plate_number'], 'tenant_id' => $tenantId],
                array_merge($t, [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'verification_status' => 'verified',
                    'is_available' => true,
                ])
            );
        }

        echo "Seeded " . ServiceProvider::count() . " service providers, " . Warehouse::count() . " warehouses, " . Transporter::count() . " transporters\n";
    }
}
