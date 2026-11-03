<?php

namespace Database\Seeders;

use App\Models\EquipmentListing;
use App\Models\ServiceProvider;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo agronomists and equipment for local development and screenshots.
 *
 * Deliberately NOT called from DatabaseSeeder: these are invented people and
 * machines, and production must only list real, verified providers.
 *
 *   php artisan db:seed --class=ServicesDemoSeeder
 */
class ServicesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['country_code' => 'tz'], ['name' => 'Tanzania', 'currency' => 'TZS']);

        $agronomists = [
            ['Shamba Bora Advisory', 'Morogoro', 'Morogoro Mjini', -6.8210, 37.6610, ['mahindi', 'mpunga', 'mbogamboga'], 15000, 40000],
            ['Dkt. Neema Kileo', 'Morogoro', 'Mvomero', -6.3000, 37.4500, ['miwa', 'mahindi'], 10000, 35000],
            ['Kilimo Hai Dodoma', 'Dodoma', 'Dodoma Mjini', -6.1630, 35.7516, ['alizeti', 'mtama', 'zabibu'], 12000, 30000],
            ['Arusha Horticulture Experts', 'Arusha', 'Arusha Mjini', -3.3869, 36.6830, ['nyanya', 'vitunguu', 'maua'], 20000, 50000],
            ['Juma Mwakalinga', 'Mbeya', 'Mbeya Mjini', -8.9094, 33.4608, ['kahawa', 'parachichi', 'viazi'], 15000, 45000],
            ['Agro Pamoja (no GPS)', 'Morogoro', null, null, null, ['mahindi', 'maharage'], 8000, null],
        ];

        foreach ($agronomists as $i => [$name, $region, $district, $lat, $lng, $crops, $fee, $visit]) {
            $user = User::where('phone', '25570000'.(1000 + $i))->first()
                ?? User::provision([
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'phone' => '25570000'.(1000 + $i),
                    'role' => 'agronomist',
                ]);

            ServiceProvider::updateOrCreate(
                ['user_id' => $user->id, 'service_type' => 'agronomist'],
                [
                    'tenant_id' => $tenant->id,
                    'business_name' => $name,
                    'bio' => 'Ushauri wa mazao, udongo na wadudu. Natembelea mashamba ndani ya mkoa.',
                    'specializations' => $crops,
                    'region' => $region,
                    'district' => $district,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'verification_status' => 'verified',
                    'consultation_fee' => $fee,
                    'visit_fee' => $visit,
                    'rating' => [4.6, 4.8, 4.3, 4.9, 4.5, 0][$i],
                    'rating_count' => [12, 30, 7, 41, 9, 0][$i],
                    'is_active' => true,
                ],
            );
        }

        $owners = [
            ['Mussa Trekta', '255700002001'],
            ['Halima Zana', '255700002002'],
        ];
        $ownerIds = [];
        foreach ($owners as [$name, $phone]) {
            $ownerIds[] = (User::where('phone', $phone)->first()
                ?? User::provision(['tenant_id' => $tenant->id, 'name' => $name, 'phone' => $phone, 'role' => 'farmer']))->id;
        }

        $equipment = [
            ['tractor', 'Trekta Massey Ferguson 375 na dereva', 120000, true, 'Morogoro', -6.8300, 37.6700, 0],
            ['power_tiller', 'Power tiller Kubota', 45000, true, 'Morogoro', -6.7900, 37.6400, 1],
            ['water_pump', 'Pampu ya maji ya petroli 3"', 20000, false, 'Morogoro', -6.8500, 37.6900, 1],
            ['sprayer', 'Bomba la kunyunyizia lita 16', 5000, false, 'Morogoro', -6.8100, 37.6500, 1],
            ['thresher', 'Mashine ya kupura mpunga', 60000, true, 'Morogoro', -6.4500, 37.5000, 0],
            ['tractor', 'Trekta John Deere 5055E', 140000, true, 'Dodoma', -6.1700, 35.7400, 0],
            ['harvester', 'Mashine ndogo ya kuvuna mahindi', 90000, true, 'Arusha', -3.3700, 36.6900, 0],
        ];

        foreach ($equipment as [$category, $title, $rate, $operator, $region, $lat, $lng, $owner]) {
            EquipmentListing::updateOrCreate(
                ['owner_id' => $ownerIds[$owner], 'title' => $title],
                [
                    'tenant_id' => $tenant->id,
                    'category' => $category,
                    'description' => $operator ? 'Bei inajumuisha dereva/mwendeshaji. Mafuta juu ya mkodishaji.' : 'Unachukua mwenyewe. Rudisha ikiwa safi.',
                    'daily_rate' => $rate,
                    'with_operator' => $operator,
                    'region' => $region,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status' => 'active',
                ],
            );
        }

        $this->command?->info('Seeded '.count($agronomists).' demo agronomists and '.count($equipment).' equipment listings.');
    }
}
