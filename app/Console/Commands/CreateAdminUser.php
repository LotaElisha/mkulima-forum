<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email} {--name=Platform Admin}';

    protected $description = 'Create or reset an administrator account for Mkulima Forum Admin Panel';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->secret('Enter a strong administrator password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Password must contain at least 12 characters.');

            return Command::FAILURE;
        }
        $name = $this->option('name');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->setPrivileged([
                'password' => Hash::make($password),
                'role' => Roles::ADMIN,
                'status' => 'active',
            ]);
            $this->info("Admin user '{$email}' password was reset successfully.");
        } else {
            $user = User::provision([
                'tenant_id' => 1,
                'name' => $name,
                'email' => $email,
                'phone' => '255'.rand(700000000, 799999999),
                'password' => Hash::make($password),
                'role' => Roles::ADMIN,
                'status' => 'active',
                'kyc_status' => 'verified',
                'phone_verified_at' => now(),
                'preferred_language' => 'sw',
            ]);
            $this->info("Admin user '{$email}' created successfully.");
        }

        return Command::SUCCESS;
    }
}
