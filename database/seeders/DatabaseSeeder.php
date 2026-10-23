<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // must run before any user creation
            TenantSeeder::class,
            AdminUserSeeder::class,
            CategoryProductSeeder::class,
            ForumCategorySeeder::class,
            FeatureFlagSeeder::class,
            LandingSettingSeeder::class,
            LogisticsServiceSeeder::class,
            SpineSeeder::class,
            RegulatoryAuthoritySeeder::class,
            CommunityChannelSeeder::class,
        ]);

        // Sample prices and English forum threads are local fixtures.
        // Production gets the Swahili categories above and nothing invented.
        if (! app()->environment('production')) {
            $this->call([
                ForumSeeder::class,
                MarketPriceSeeder::class,
            ]);
        }
    }
}
