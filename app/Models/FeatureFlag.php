<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'enabled',
        'is_public',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Check if a feature is enabled
     */
    public static function isEnabled(string $key): bool
    {
        $feature = self::where('key', $key)->first();
        return $feature ? $feature->enabled : false;
    }

    /**
     * Enable a feature
     */
    public static function enable(string $key): void
    {
        self::where('key', $key)->update(['enabled' => true]);
    }

    /**
     * Disable a feature
     */
    public static function disable(string $key): void
    {
        self::where('key', $key)->update(['enabled' => false]);
    }
}
