<?php

namespace App\Services\AI\Secrets;

use App\Models\AiProviderCredential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptedDatabaseSecretManager implements SecretManagerServiceInterface
{
    public function storeSecret(int $providerId, string $apiKey): bool
    {
        try {
            $encryptedKey = Crypt::encryptString(trim($apiKey));
            $keyHash = hash('sha256', trim($apiKey));

            AiProviderCredential::updateOrCreate(
                ['ai_provider_id' => $providerId],
                [
                    'encrypted_api_key' => $encryptedKey,
                    'key_hash' => $keyHash,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to store secret for provider ID {$providerId}: ".$e->getMessage());

            return false;
        }
    }

    public function getSecret(int $providerId): ?string
    {
        try {
            $credential = AiProviderCredential::where('ai_provider_id', $providerId)->first();
            if (! $credential || empty($credential->encrypted_api_key)) {
                return null;
            }

            return Crypt::decryptString($credential->encrypted_api_key);
        } catch (\Throwable $e) {
            Log::error("Failed to decrypt secret for provider ID {$providerId}: ".$e->getMessage());

            return null;
        }
    }

    public function deleteSecret(int $providerId): bool
    {
        try {
            AiProviderCredential::where('ai_provider_id', $providerId)->delete();

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to delete secret for provider ID {$providerId}: ".$e->getMessage());

            return false;
        }
    }
}
