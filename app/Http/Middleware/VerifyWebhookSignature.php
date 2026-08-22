<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared-secret authentication for gateway webhooks.
 *
 * The SMS and IVR callback endpoints carry no user credential, so before this
 * middleware anyone who knew the URL could POST to them. `/api/sms/receive` in
 * particular ran a market-price query and an outbound OpenWeather call on every
 * anonymous request — a free way to burn a metered API key and hammer the
 * database, and a way to forge delivery receipts.
 *
 * The secret is compared with hash_equals so a timing side channel cannot be
 * used to recover it a byte at a time.
 *
 * Configure per channel:
 *   services.sms.webhook_secret  ← Africa's Talking / Twilio callback secret
 *   services.ivr.webhook_secret  ← IVR provider callback secret
 *
 * If no secret is configured the request is refused in production and allowed
 * everywhere else, so local development and the test suite keep working while
 * a live deployment cannot silently run with the door open.
 */
class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $channel = 'sms'): Response
    {
        $expected = (string) config("services.{$channel}.webhook_secret", '');

        if ($expected === '') {
            if (app()->environment('production')) {
                Log::warning('Webhook rejected: no secret configured', [
                    'channel' => $channel,
                    'ip' => $request->ip(),
                ]);

                return response()->json(['message' => 'Webhook is not configured.'], 503);
            }

            return $next($request);
        }

        $presented = (string) (
            $request->header('X-Webhook-Signature')
            ?? $request->header('X-Mkulima-Signature')
            ?? $request->input('webhook_secret', '')
        );

        if ($presented === '' || ! hash_equals($expected, $presented)) {
            Log::warning('Webhook rejected: bad signature', [
                'channel' => $channel,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        return $next($request);
    }
}
