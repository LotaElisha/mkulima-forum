<?php

namespace App\Providers;

use App\Services\AI\AIService;
use App\Services\AI\Secrets\EncryptedDatabaseSecretManager;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            SecretManagerServiceInterface::class,
            EncryptedDatabaseSecretManager::class
        );

        $this->app->singleton(AIService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fail loudly outside production when a write silently drops an
        // attribute that is not mass-assignable.
        //
        // This exists because of a real near-miss: moving role, status and
        // password out of User::$fillable made several User::create() calls
        // quietly discard them, producing users with no password and no role.
        // Nothing errored — the tests only failed later, at the assertion.
        // With this on, the discard itself throws, at the line that caused it.
        //
        // Off by default, because switching it on today throws in around forty
        // existing tests whose fixtures pass columns that were never fillable
        // (Tenant::create([...'id', 'slug', 'domain']) and similar). That is
        // pre-existing debt, harmless in itself, and cleaning it up is a
        // separate job from this security change.
        //
        // Set STRICT_MODELS=true locally to work through those fixtures; once
        // they are clean, flip the default to `! $this->app->isProduction()`.
        // Never enable it in production: a running platform should not 500 a
        // farmer's request over a developer-facing strictness check.
        Model::preventSilentlyDiscardingAttributes(
            ! $this->app->isProduction() && env('STRICT_MODELS', false)
        );
    }
}
