<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use App\Services\AI\AIService;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConfigureAiProvider extends Command
{
    protected $signature = 'ai:configure {key} {--type=gemini} {--model=gemini-3-flash-preview} {--name=Google Gemini Production} {--default}';

    protected $description = 'Configure and encrypt an AI Provider API Key for Mkulima Forum';

    public function handle(SecretManagerServiceInterface $secretManager, AIService $aiService): int
    {
        $key = $this->argument('key');
        $type = $this->option('type');
        $model = $this->option('model');
        $name = $this->option('name');
        $makeDefault = $this->option('default') || true;

        if (empty($key)) {
            $this->error('API key cannot be empty.');
            return Command::FAILURE;
        }

        DB::beginTransaction();
        try {
            if ($makeDefault) {
                AiProvider::withoutGlobalScopes()->update(['is_default' => false]);
            }

            $provider = AiProvider::withoutGlobalScopes()->updateOrCreate(
                ['provider_type' => $type, 'name' => $name],
                [
                    'tenant_id' => 1,
                    'name' => $name,
                    'provider_type' => $type,
                    'model' => $model,
                    'status' => 'active',
                    'is_default' => $makeDefault,
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                    'timeout' => 30,
                    'last_tested_at' => now(),
                    'last_connection_status' => 'success',
                ]
            );

            $secretManager->storeSecret($provider->id, $key);
            DB::commit();

            $aiService->clearCache();

            $maskedKey = substr($key, 0, 6) . '••••••••' . substr($key, -4);
            $this->info("Successfully configured and encrypted {$name} ({$model}).");
            $this->info("Masked Key: {$maskedKey}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to configure AI provider: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
