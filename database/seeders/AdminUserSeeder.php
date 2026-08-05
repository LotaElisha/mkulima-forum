<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Default admin advertised on the dashboard login screen.
     * CHANGE THE PASSWORD (or delete this user) before production.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@mkulima.forum'],
            [
                'tenant_id' => 1,
                'name' => 'Platform Admin',
                'phone' => '255700000000',
                'password' => Hash::make('admin123'),
                'role' => Roles::ADMIN,
                'status' => 'active',
                'kyc_status' => 'verified',
                'phone_verified_at' => now(),
                'preferred_language' => 'sw',
            ],
        );

        $admin->assignRole(Roles::ADMIN);
    }
}
