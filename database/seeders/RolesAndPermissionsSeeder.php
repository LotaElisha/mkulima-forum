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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $matrix = [
            Roles::FARMER     => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::BUYER      => ['orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::AGRODEALER => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'services.book', 'wallet.use'],
            Roles::SELLER     => ['products.create', 'products.manage-own', 'orders.view-own', 'forum.post', 'wallet.use'],
            Roles::AGRONOMIST => ['forum.post', 'services.provide', 'wallet.use'],
            Roles::VETERINARY => ['forum.post', 'services.provide', 'wallet.use'],
            Roles::DRIVER     => ['forum.post', 'wallet.use'],
            Roles::LOGISTICS  => ['forum.post', 'wallet.use'],
            Roles::WAREHOUSE  => ['forum.post', 'wallet.use'],
            Roles::MODERATOR  => ['forum.post', 'forum.moderate', 'threads.moderate', 'replies.moderate'],
            // Admin & Superadmin get EVERY permission (full access)
            Roles::ADMIN      => $permissions,
            Roles::SUPERADMIN => $permissions,
        ];

        foreach ($matrix as $role => $perms) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
                ->syncPermissions($perms);
        }
    }
}
