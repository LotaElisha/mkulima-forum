<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \App\Services\AI\Secrets\SecretManagerServiceInterface::class,
            \App\Services\AI\Secrets\EncryptedDatabaseSecretManager::class
        );

        $this->app->singleton(\App\Services\AI\AIService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
