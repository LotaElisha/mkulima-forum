<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Create an administrator, or reset an existing account's password.
 *
 * Resetting a password never changes the account's role unless --role is
 * passed explicitly. It used to force role=admin on every reset, which
 * silently demoted a superadmin and locked them out of the Super-Admin-only
 * secrets in Admin → System → Configuration (ConfigSettingPolicy).
 */
class CreateAdminUser extends Command
{
    /** Roles this command is allowed to assign. */
    private const ASSIGNABLE_ROLES = [Roles::ADMIN, Roles::SUPERADMIN];

    protected $signature = 'admin:create {email}
        {--name=Platform Admin : Display name, used only when creating a new account}
        {--role= : admin or superadmin. New accounts default to admin; an existing account keeps its role unless this is given}';

    protected $description = 'Create or reset an administrator account for Mkulima Forum Admin Panel';

    public function handle(): int
    {
        $email = $this->argument('email');

        $role = $this->option('role');
        if ($role !== null && ! in_array($role, self::ASSIGNABLE_ROLES, true)) {
            $this->error('--role must be one of: '.implode(', ', self::ASSIGNABLE_ROLES).'.');

            return Command::FAILURE;
        }

        $password = $this->secret('Enter a strong administrator password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Password must contain at least 12 characters.');

            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $changes = [
                'password' => Hash::make($password),
                'status' => 'active',
            ];
            if ($role !== null) {
                $changes['role'] = $role;
            }
            $user->setPrivileged($changes);

            $this->info("Password for '{$email}' was reset successfully (role: {$user->role}).");
            if (! in_array($user->role, self::ASSIGNABLE_ROLES, true)) {
                $this->warn("'{$email}' is not an administrator. Pass --role=admin to promote it.");
            }
        } else {
            $role ??= Roles::ADMIN;
            User::provision([
                'tenant_id' => 1,
                'name' => $this->option('name'),
                'email' => $email,
                'phone' => '255'.rand(700000000, 799999999),
                'password' => Hash::make($password),
                'role' => $role,
                'status' => 'active',
                'kyc_status' => 'verified',
                'phone_verified_at' => now(),
                'preferred_language' => 'sw',
            ]);
            $this->info("Admin user '{$email}' created successfully (role: {$role}).");
        }

        return Command::SUCCESS;
    }
}
