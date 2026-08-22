<?php

namespace App\Services\System;

use App\Models\User;
use App\Services\SmsService;
use App\Settings\SettingsManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * The single definition of "is this environment ready to serve farmers".
 *
 * Used by both `php artisan mkulima:preflight` and the admin readiness screen,
 * so the terminal and the dashboard can never disagree about what is wrong.
 *
 * Every check returns a status, a human explanation of the consequence, and
 * whether it blocks a launch. Nothing here returns a credential.
 */
class ProductionReadiness
{
    public const OK = 'ok';

    public const WARN = 'warn';

    public const FAIL = 'fail';

    /**
     * @return array{checks: array<int, array<string, mixed>>, summary: array<string, int>, ready: bool}
     */
    public function run(): array
    {
        $checks = array_merge(
            $this->applicationChecks(),
            $this->databaseChecks(),
            $this->mailChecks(),
            $this->authChecks(),
            $this->integrationChecks(),
            $this->storageChecks(),
            $this->queueChecks(),
        );

        $summary = [
            'ok' => count(array_filter($checks, fn ($c) => $c['status'] === self::OK)),
            'warn' => count(array_filter($checks, fn ($c) => $c['status'] === self::WARN)),
            'fail' => count(array_filter($checks, fn ($c) => $c['status'] === self::FAIL)),
        ];

        return [
            'checks' => $checks,
            'summary' => $summary,
            'ready' => $summary['fail'] === 0,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    // ── Groups ────────────────────────────────────────────────────────

    private function applicationChecks(): array
    {
        $url = (string) config('app.url');

        return [
            $this->check('Application', 'APP_KEY is set', (string) config('app.key') !== '', self::FAIL,
                'Run php artisan key:generate. Without it every encrypted value and signed URL breaks.'),

            $this->check('Application', 'Debug mode is off', ! config('app.debug'), self::FAIL,
                'APP_DEBUG=true leaks stack traces, env values and query contents to visitors.'),

            $this->check('Application', 'Application URL is a real https host',
                str_starts_with($url, 'https://') && ! str_contains($url, 'localhost'), self::FAIL,
                "Currently '{$url}'. Every verification and password-reset link is built from it.",
                $url),

            $this->check('Application', 'Environment is production',
                config('app.env') === 'production', self::WARN,
                "Currently '".config('app.env')."'.", (string) config('app.env')),
        ];
    }

    private function databaseChecks(): array
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            return [$this->check('Database', 'Connection succeeds', false, self::FAIL, $e->getMessage())];
        }

        return [
            $this->check('Database', 'Connection succeeds', true, self::FAIL, '',
                (string) DB::connection()->getDatabaseName()),

            $this->check('Database', 'Password reset table exists',
                Schema::hasTable('password_reset_tokens'), self::FAIL,
                'Run php artisan migrate --force. Every password-reset request throws without it.'),

            $this->check('Database', 'Email change column exists',
                Schema::hasColumn('users', 'pending_email'), self::FAIL,
                'Run php artisan migrate --force.'),

            $this->check('Database', 'Tenants are seeded', DB::table('tenants')->exists(), self::FAIL,
                'Run php artisan db:seed --class=TenantSeeder. Registration resolves a tenant by country code.'),

            $this->check('Database', 'An administrator exists',
                User::query()->whereIn('role', ['admin', 'superadmin'])->exists(), self::WARN,
                'Seed one with php artisan db:seed --class=AdminUserSeeder.'),
        ];
    }

    private function mailChecks(): array
    {
        $mailer = (string) config('mail.default');
        $checks = [
            $this->check('Email', 'A real mail transport is configured',
                ! in_array($mailer, ['log', 'array', ''], true), self::FAIL,
                "Currently '{$mailer}'. Verification and reset mail would be written to a log file, not delivered.",
                $mailer),
        ];

        if ($mailer === 'smtp') {
            $checks[] = $this->check('Email', 'SMTP host is set',
                (string) config('mail.mailers.smtp.host') !== '', self::FAIL, 'No SMTP host configured.');

            // The most expensive silent failure on this platform.
            $checks[] = $this->check('Email', 'SMTP password is set',
                (string) config('mail.mailers.smtp.password') !== '', self::FAIL,
                'Registration will create accounts that can never recover a lost password, and nothing will error.');
        }

        $checks[] = $this->check('Email', 'A from-address is set',
            (string) config('mail.from.address') !== '', self::FAIL,
            'Most providers reject mail with no from-address.');

        $lastTest = app(SettingsManager::class)->state('mail.last_test_at');
        $checks[] = $this->check('Email', 'A test email has succeeded',
            $lastTest !== null, self::WARN,
            'Send one from Admin → System → Configuration → Email. A green config is not proof of delivery.',
            $lastTest ? (string) $lastTest : null);

        return $checks;
    }

    private function authChecks(): array
    {
        return [
            $this->check('Authentication', 'Token expiry is finite',
                config('sanctum.expiration') !== null, self::WARN,
                'API tokens never expire while this is unset.'),

            $this->check('Authentication', 'Session cookies are secure',
                (bool) config('session.secure', false), self::WARN,
                'Session cookies may travel over plain HTTP.'),
        ];
    }

    private function integrationChecks(): array
    {
        $checks = [];

        foreach (['sms' => 'SMS', 'ivr' => 'IVR'] as $channel => $label) {
            $checks[] = $this->check('Integrations', "{$label} webhook secret is set",
                (string) config("services.{$channel}.webhook_secret", '') !== '', self::FAIL,
                "Without it /api/{$channel}/* refuses all traffic in production. Set the same value at the provider.");
        }

        $hosts = (array) config('services.short_links.allowed_hosts', []);
        $ownHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        $checks[] = $this->check('Integrations', 'Short-link allowlist includes this host',
            in_array($ownHost, array_map('strtolower', $hosts), true), self::FAIL,
            "'{$ownHost}' is not in the allowlist, so links to your own domain would show the interstitial.");

        try {
            $sms = app(SmsService::class);
            $checks[] = $this->check('Integrations', 'SMS gateway is configured',
                $sms->isConfigured(), self::WARN,
                'Phone OTP cannot send. Email authentication is unaffected.',
                $sms->gateway());
        } catch (Throwable $e) {
            $checks[] = $this->check('Integrations', 'SMS gateway is configured', false, self::WARN, $e->getMessage());
        }

        // Stated plainly rather than silently absent: this platform has none.
        $checks[] = $this->check('Integrations', 'Error tracking is configured', false, self::WARN,
            'No error tracker is installed, so production failures surface only in the log file. '
            .'Requires a composer package — see README.');

        return $checks;
    }

    private function storageChecks(): array
    {
        $checks = [
            $this->check('Storage', 'storage/ is writable', is_writable(storage_path()), self::FAIL,
                'The application cannot write logs, cache or uploads.'),

            $this->check('Storage', 'public/storage symlink exists',
                is_link(public_path('storage')) || is_dir(public_path('storage')), self::WARN,
                'Run php artisan storage:link, or uploaded images 404.'),
        ];

        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());
        if ($free && $total) {
            $pctFree = round(($free / $total) * 100);
            $checks[] = $this->check('Storage', 'Disk has headroom', $pctFree > 10, self::WARN,
                "Only {$pctFree}% of the disk is free.", "{$pctFree}% free");
        }

        return $checks;
    }

    private function queueChecks(): array
    {
        $connection = (string) config('queue.default');

        $checks = [
            $this->check('Queue', 'Queue is not synchronous', $connection !== 'sync', self::WARN,
                'QUEUE_CONNECTION=sync sends mail inline, so a slow SMTP host stalls the sign-up request.',
                $connection),
        ];

        if ($connection === 'database' && Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->count();
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

            $checks[] = $this->check('Queue', 'No backlog of pending jobs', $pending < 100, self::WARN,
                "{$pending} jobs waiting. Is a worker running? Verification and reset mail is queued.",
                (string) $pending);

            $checks[] = $this->check('Queue', 'No failed jobs', $failed === 0, self::WARN,
                "{$failed} failed jobs. Inspect with php artisan queue:failed.", (string) $failed);
        }

        return $checks;
    }

    // ── Helper ────────────────────────────────────────────────────────

    private function check(
        string $group,
        string $name,
        bool $passed,
        string $failLevel,
        string $consequence = '',
        ?string $detail = null,
    ): array {
        return [
            'group' => $group,
            'name' => $name,
            'status' => $passed ? self::OK : $failLevel,
            'blocking' => ! $passed && $failLevel === self::FAIL,
            'consequence' => $passed ? null : $consequence,
            'detail' => $detail,
        ];
    }
}
