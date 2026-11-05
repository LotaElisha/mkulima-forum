<?php

namespace App\Services\Trace;

/**
 * Units a lot can be counted in, and their weight in kilograms. Every
 * quantity is also stored in kg so hand-offs, splits and merges reconcile
 * whatever unit each party typed.
 */
final class Units
{
    public const KG = [
        'kg' => 1.0,
        'tonne' => 1000.0,
        'bag_50kg' => 50.0,
        'bag_90kg' => 90.0,
        'bag_100kg' => 100.0,
    ];

    public static function keys(): array
    {
        return array_keys(self::KG);
    }

    public static function toKg(float $quantity, string $unit): float
    {
        return round($quantity * (self::KG[$unit] ?? 1.0), 3);
    }

    public static function fromKg(float $kg, string $unit): float
    {
        return round($kg / (self::KG[$unit] ?? 1.0), 3);
    }

    /**
     * Allowed difference before two weights count as different: half a
     * percent or half a kilogram, whichever is larger. Scales disagree.
     */
    public static function tolerance(float $kg): float
    {
        return max(0.5, abs($kg) * 0.005);
    }

    public static function differs(float $a, float $b): bool
    {
        return abs($a - $b) > self::tolerance(max($a, $b));
    }
}
