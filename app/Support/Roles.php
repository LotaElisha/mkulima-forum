<?php

namespace App\Support;

/**
 * Canonical role list — single source of truth for:
 *  - users.role column values
 *  - Spatie roles (RolesAndPermissionsSeeder)
 *  - request validation rules (use Roles::rule())
 */
final class Roles
{
    public const FARMER = 'farmer';
    public const BUYER = 'buyer';
    public const AGRODEALER = 'agrodealer';
    public const SELLER = 'seller';
    public const AGRONOMIST = 'agronomist';
    public const VETERINARY = 'veterinary';
    public const DRIVER = 'driver';
    public const LOGISTICS = 'logistics';
    public const WAREHOUSE = 'warehouse';
    public const MODERATOR = 'moderator';
    public const ADMIN = 'admin';
    public const SUPERADMIN = 'superadmin';

    public const ALL = [
        self::FARMER, self::BUYER, self::AGRODEALER, self::SELLER,
        self::AGRONOMIST, self::VETERINARY, self::DRIVER, self::LOGISTICS,
        self::WAREHOUSE, self::MODERATOR, self::ADMIN, self::SUPERADMIN,
    ];

    /** Roles a user may self-select at registration. Staff roles excluded. */
    public const SELF_REGISTERABLE = [
        self::FARMER, self::BUYER, self::AGRODEALER, self::SELLER,
        self::AGRONOMIST, self::VETERINARY, self::LOGISTICS,
    ];

    /** Roles that can list products / access the seller dashboard. */
    public const SELLERS = [self::SELLER, self::AGRODEALER, self::ADMIN, self::SUPERADMIN];

    public static function rule(array $roles = self::ALL): string
    {
        return 'in:' . implode(',', $roles);
    }
}
