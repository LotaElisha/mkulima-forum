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
            CategoryProductSeeder::class,
            ForumSeeder::class,
            FeatureFlagSeeder::class,
        ]);
    }
}
