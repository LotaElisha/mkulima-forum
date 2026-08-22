<?php

namespace Database\Seeders;

use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Marketplace
            'products.create', 'products.manage-own', 'products.manage-any',
            'orders.view-own', 'orders.manage-any',
            // Forum
            'forum.post', 'forum.moderate',
            // Services
            'services.provide', 'services.book',
            // Finance
            'wallet.use', 'escrow.arbitrate', 'reports.view',
            // Admin / User management
            'users.manage', 'users.create', 'users.update', 'users.delete',
            // Vendors & KYC
            'vendors.manage', 'kyc.review',
            // Platform features
            'features.toggle', 'landing.manage',
            // Content
            'categories.manage', 'threads.moderate', 'replies.moderate',
            // Analytics
            'analytics.view', 'analytics.export',
            // HR / Staff
            'staff.manage',
            // Settings
            'settings.manage',
            // Production configuration (Admin -> System -> Configuration)
            'system.settings.view',
            'system.settings.manage',
            'system.queue.manage',
            'system.maintenance.manage',
        ];

        /*
         * Permissions a plain admin must NOT hold.
         *
         * Kept out of $permissions deliberately: the matrix below grants
         * Roles::ADMIN every entry of $permissions, so anything listed there is
         * automatically an admin capability. Rotating an SMTP password or a
         * webhook secret is a superadmin action, so it lives here instead.
         */
        $superadminOnly = [
            'system.secrets.manage',
        ];

        foreach (array_merge($permissions, $superadminOnly) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $matrix = [
            Roles::FARMER => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::BUYER => ['orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::AGRODEALER => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::SELLER => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'wallet.use'],
            Roles::AGRONOMIST => ['forum.post', 'services.provide', 'wallet.use'],
            Roles::VETERINARY => ['forum.post', 'services.provide', 'wallet.use'],
            Roles::DRIVER => ['forum.post', 'wallet.use'],
            Roles::LOGISTICS => ['forum.post', 'wallet.use'],
            Roles::WAREHOUSE => ['forum.post', 'wallet.use'],
            Roles::MODERATOR => ['forum.post', 'forum.moderate', 'threads.moderate', 'replies.moderate'],
            // Admin gets every ordinary permission; superadmin additionally
            // holds the production-secret permissions.
            Roles::ADMIN => $permissions,
            Roles::SUPERADMIN => array_merge($permissions, $superadminOnly),
        ];

        foreach ($matrix as $role => $perms) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
                ->syncPermissions($perms);
        }
    }
}
