<?php

namespace App\Services\Notifications;

use App\Models\PushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send push notification via Firebase Cloud Messaging
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $tokens = PushToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active push tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send to multiple tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return ['success' => false, 'message' => 'FCM not configured'];
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1,
            ],
            'data' => $data,
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'mkulima_forum',
                    'priority' => 'high',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            $result = $response->json();

            // Handle invalid tokens
            if (isset($result['results'])) {
                foreach ($result['results'] as $index => $resultItem) {
                    if (isset($resultItem['error']) && $resultItem['error'] === 'NotRegistered') {
                        PushToken::where('token', $tokens[$index])->delete();
                    }
                }
            }

            return [
                'success' => $response->successful(),
                'success_count' => $result['success'] ?? 0,
                'failure_count' => $result['failure'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('FCM send failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send broadcast to topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            return ['success' => false, 'message' => 'FCM not configured'];
        }

        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            return [
                'success' => $response->successful(),
                'result' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('FCM topic send failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Register push token
     */
    public function registerToken(int $userId, string $token, string $platform = 'android'): PushToken
    {
        // Deactivate old tokens for this device
        PushToken::where('user_id', $userId)
            ->where('platform', $platform)
            ->update(['is_active' => false]);

        return PushToken::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $userId,
                'platform' => $platform,
                'is_active' => true,
            ]
        );
    }
}
