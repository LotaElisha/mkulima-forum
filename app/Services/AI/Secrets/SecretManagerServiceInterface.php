<?php

namespace App\Services\AI\Secrets;

interface SecretManagerServiceInterface
{
    /**
     * Encrypt and store an API key secret for a given provider.
     */
    public function storeSecret(int $providerId, string $apiKey): bool;

    /**
     * Retrieve and decrypt the API key secret for a given provider.
     */
    public function getSecret(int $providerId): ?string;

    /**
     * Delete the API key secret for a given provider.
     */
    public function deleteSecret(int $providerId): bool;
}
