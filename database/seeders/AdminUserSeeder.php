<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', '');
        $password = (string) env('ADMIN_PASSWORD', '');
        if ($email === '' || strlen($password) < 12) {
            throw new \RuntimeException('ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters are required.');
        }

        $tenant = Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS'],
        );
        Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Platform Admin',
                'phone' => '255700000000',
                'password' => Hash::make($password),
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
