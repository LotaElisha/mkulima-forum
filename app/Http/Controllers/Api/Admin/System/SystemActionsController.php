<?php

namespace App\Http\Controllers\Api\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use App\Services\Spine\AuditTrail;
use App\Services\System\ProductionReadiness;
use App\Settings\SettingsManager;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Operational actions that sit alongside the configuration screen: proving mail
 * works, proving SMS works, and the readiness view.
 *
 * A test send exists because a green configuration screen is not proof of
 * delivery. Empty SMTP credentials do not error — mail simply never arrives,
 * and the first person to notice is a farmer who cannot recover their account.
 */
class SystemActionsController extends Controller
{
    public function __construct(
        private readonly SettingsManager $settings,
        private readonly AuditTrail $audit,
    ) {}

    /** Full production readiness, shared with `php artisan mkulima:preflight`. */
    public function readiness(ProductionReadiness $readiness): JsonResponse
    {
        return response()->json($readiness->run());
    }

    /**
     * Send a real email through the configured transport.
     *
     * Deliberately not queued: the operator is standing in front of the screen
     * waiting for an answer, and a queued send would report success the moment
     * the job was accepted rather than when SMTP actually agreed.
     */
    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'email', 'max:255'],
        ]);

        $user = $request->user();
        $to = $validated['to'];
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return response()->json([
                'success' => false,
                'message' => "The mail driver is '{$mailer}', so nothing is actually delivered. "
                    .'Set it to smtp and save before testing.',
            ], 422);
        }

        try {
            Mail::raw($this->testBody(), function (Message $message) use ($to) {
                $message->to($to)->subject('MkulimaForum — configuration test');
            });
        } catch (Throwable $e) {
            $this->settings->recordState('mail.last_failure_at', now()->toIso8601String());
            $this->settings->recordState('mail.last_failure_reason', $this->readableMailError($e));

            return response()->json([
                'success' => false,
                'message' => $this->readableMailError($e),
                // The raw exception often names the host and port, which is
                // useful to an operator and harmless — it never contains the
                // password.
                'detail' => mb_strimwidth($e->getMessage(), 0, 400, '…'),
            ], 422);
        }

        $this->settings->recordState('mail.last_test_at', now()->toIso8601String());

        return response()->json([
            'success' => true,
            'message' => "Test email sent to {$to} via {$mailer}. If it does not arrive, check the spam folder "
                .'and the sending domain\'s SPF record.',
        ]);
    }

    /**
     * Send a real SMS through the configured gateway.
     *
     * Costs money per message, so it is tightly throttled at the route and
     * restricted to superadmins.
     */
    public function testSms(Request $request, SmsService $sms): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
        ]);

        if (! $sms->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => "Gateway '{$sms->gateway()}' has no credentials configured.",
            ], 422);
        }

        $result = $sms->send(
            $validated['phone'],
            'MkulimaForum: this is a configuration test. No action needed.',
            'test',
            $request->user()?->id,
        );

        $succeeded = (bool) ($result['success'] ?? false);
        $this->settings->recordState(
            $succeeded ? 'sms.last_test_at' : 'sms.last_failure_at',
            now()->toIso8601String(),
        );

        return response()->json([
            'success' => $succeeded,
            'gateway' => $result['gateway'] ?? $sms->gateway(),
            'message' => $succeeded
                ? "Test message accepted by {$sms->gateway()} for {$validated['phone']}."
                : ($result['error'] ?? 'The gateway rejected the message.'),
        ], $succeeded ? 200 : 422);
    }

    /**
     * Generate a fresh webhook secret.
     *
     * Returned exactly once, because the operator has to paste it into the
     * provider's dashboard. After this response it is only ever stored
     * encrypted and is never readable again.
     */
    public function rotateWebhookSecret(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:sms,ivr'],
        ]);

        $user = $request->user();

        if ($user->role !== Roles::SUPERADMIN) {
            return response()->json(['message' => 'Rotating a webhook secret requires a superadmin.'], 403);
        }

        $secret = bin2hex(random_bytes(24));
        $key = "{$validated['channel']}.webhook_secret";

        $this->settings->set($key, $secret, $user);

        return response()->json([
            'message' => strtoupper($validated['channel']).' webhook secret rotated. '
                .'Copy it now — it cannot be shown again. Set the same value at the provider, '
                .'sent as the X-Webhook-Signature header.',
            'secret' => $secret,
            'shown_once' => true,
        ]);
    }

    private function testBody(): string
    {
        return implode("\n", [
            'This is a configuration test from the MkulimaForum admin dashboard.',
            '',
            'If you are reading this, outbound email is working — which means',
            'password reset and email verification will reach farmers.',
            '',
            'Application: '.config('app.url'),
            'Environment: '.config('app.env'),
            'Sent at: '.now()->toDayDateTimeString(),
        ]);
    }

    /** Turn an SMTP exception into something an operator can act on. */
    private function readableMailError(Throwable $e): string
    {
        $raw = strtolower($e->getMessage());

        return match (true) {
            str_contains($raw, 'authentication') || str_contains($raw, '535') => 'The mail server rejected the username or password. Gmail requires an app password, not the account password.',
            str_contains($raw, 'could not connect') || str_contains($raw, 'connection refused') => 'Could not reach the mail server. Check the host and port, and that the server allows outbound SMTP.',
            str_contains($raw, 'timed out') => 'The mail server did not respond in time. Port 587 is usually open where 465 is blocked.',
            str_contains($raw, 'certificate') || str_contains($raw, 'ssl') => 'TLS negotiation failed. Try switching encryption between tls and ssl.',
            str_contains($raw, 'sender') || str_contains($raw, 'from') => 'The server rejected the from-address. It usually has to match the authenticated account.',
            default => 'The mail server rejected the message. The detail below is the server\'s own reply.',
        };
    }
}
