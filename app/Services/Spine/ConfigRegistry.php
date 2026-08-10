<?php

namespace App\Services\Spine;

use App\Models\ConfigSetting;
use Illuminate\Support\Facades\Cache;

class ConfigRegistry
{
    /**
     * Get a setting by key. Decrypts secrets server-side.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("config_setting:{$key}", 3600, function () use ($key) {
            return ConfigSetting::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value. Increment version and record audit log.
     */
    public function set(string $key, mixed $value, ?int $userId = null, string $group = 'general', string $type = 'string', bool $isEncrypted = false): ConfigSetting
    {
        $setting = ConfigSetting::firstOrNew(['key' => $key]);

        $before = $setting->exists ? ['value' => $setting->value, 'version' => $setting->version] : null;

        $setting->group = $group;
        $setting->type = $type;
        $setting->is_encrypted = $isEncrypted || ($type === 'secret');
        $setting->value = $value;
        $setting->version = ($setting->version ?? 0) + 1;
        $setting->updated_by = $userId;
        $setting->save();

        Cache::forget("config_setting:{$key}");

        // Audit Trail (1.5)
        app(AuditTrail::class)->record(
            $setting,
            $setting->wasRecentlyCreated ? 'created' : 'updated',
            $before,
            ['value' => '***', 'version' => $setting->version],
            $userId ? \App\Models\User::find($userId) : null
        );

        return $setting;
    }

    /**
     * Get public settings safely (filtering out secrets and encrypted values).
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('config_settings:public', 600, function () {
            return ConfigSetting::where('is_encrypted', false)
                ->where('type', '!=', 'secret')
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
