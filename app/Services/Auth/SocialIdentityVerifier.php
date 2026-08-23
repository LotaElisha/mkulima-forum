<?php

namespace App\Services\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SocialIdentityVerifier
{
    public function verify(string $provider, string $identityToken): array
    {
        return match ($provider) {
            'google' => $this->verifyGoogle($identityToken),
            'apple' => $this->verifyApple($identityToken),
            default => throw ValidationException::withMessages(['provider' => 'Unsupported social provider.']),
        };
    }

    private function verifyGoogle(string $token): array
    {
        $audiences = array_filter(config('services.social.google_client_ids', []));
        if ($audiences === []) {
            throw ValidationException::withMessages(['provider' => 'Google sign-in is not configured.']);
        }

        $payload = false;
        foreach ($audiences as $audience) {
            $payload = (new GoogleClient(['client_id' => $audience]))->verifyIdToken($token);
            if ($payload !== false) {
                break;
            }
        }

        if ($payload === false || empty($payload['sub']) || empty($payload['email']) || ! ($payload['email_verified'] ?? false)) {
            throw ValidationException::withMessages(['identity_token' => 'Google identity could not be verified.']);
        }

        return [
            'id' => (string) $payload['sub'],
            'email' => strtolower((string) $payload['email']),
            'name' => $payload['name'] ?? null,
            'avatar' => $payload['picture'] ?? null,
        ];
    }

    private function verifyApple(string $token): array
    {
        $audiences = array_filter(config('services.social.apple_client_ids', []));
        if ($audiences === []) {
            throw ValidationException::withMessages(['provider' => 'Apple sign-in is not configured.']);
        }

        try {
            $jwks = Cache::remember('auth.apple.jwks', now()->addHours(6), fn () =>
                Http::timeout(10)->get('https://appleid.apple.com/auth/keys')->throw()->json()
            );
            $payload = (array) JWT::decode($token, JWK::parseKeySet($jwks));
        } catch (\Throwable) {
            throw ValidationException::withMessages(['identity_token' => 'Apple identity could not be verified.']);
        }

        if (($payload['iss'] ?? null) !== 'https://appleid.apple.com'
            || ! in_array($payload['aud'] ?? null, $audiences, true)
            || empty($payload['sub'])) {
            throw ValidationException::withMessages(['identity_token' => 'Apple identity could not be verified.']);
        }

        return [
            'id' => (string) $payload['sub'],
            'email' => isset($payload['email']) ? strtolower((string) $payload['email']) : null,
            'name' => null,
            'avatar' => null,
        ];
    }
}
