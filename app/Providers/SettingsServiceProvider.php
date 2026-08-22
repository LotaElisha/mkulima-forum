<?php

namespace App\Providers;

use App\Settings\SettingsManager;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Overlays database-managed settings onto Laravel's runtime configuration.
 *
 * Why this exists rather than calling env() where the values are needed:
 * `php artisan config:cache` bakes env() into a cached array and env() then
 * returns null everywhere else. Any design that reads env() at request time is
 * broken in production by definition. Instead, the cached config provides the
 * bootstrap defaults and this provider layers the database over the top on each
 * request, so a setting changed in the dashboard applies immediately with no
 * cache clear and no deploy.
 *
 * Failure here must never take the site down: if the database is unreachable
 * the application keeps running on its .env values, which is exactly the
 * behaviour it had before any of this existed.
 */
class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsManager::class);
    }

    public function boot(): void
    {
        // Nothing to overlay while the schema is being built, and reading the
        // table mid-migration would fail.
        if ($this->app->runningInConsole() && $this->isSchemaCommand()) {
            return;
        }

        try {
            $this->app->make(SettingsManager::class)->applyToRuntimeConfig();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function isSchemaCommand(): bool
    {
        $command = $_SERVER['argv'][1] ?? '';

        foreach (['migrate', 'db:', 'schema:', 'config:cache', 'config:clear', 'package:discover'] as $prefix) {
            if (str_starts_with($command, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
