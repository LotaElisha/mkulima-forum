<?php

namespace App\Providers;

use App\Services\AI\AIService;
use App\Services\AI\Secrets\EncryptedDatabaseSecretManager;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
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
        //
    }
}
