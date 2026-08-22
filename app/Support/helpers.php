<?php

use App\Settings\SettingsManager;

if (! function_exists('setting')) {
    /**
     * Read an administrator-managed setting.
     *
     *     setting('mail.smtp_host', env('MAIL_HOST'))
     *
     * Resolution is database → env (per the schema) → the default given here.
     * Prefer reading through Laravel's own config() for anything with a
     * configPath, since SettingsServiceProvider has already overlaid the
     * database onto it; use this helper for settings that have no config path
     * of their own, or when you explicitly want to bypass the overlay.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsManager::class)->get($key, $default);
    }
}
