<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * One command that answers "is this deployable?".
 *
 * Written because the go-live checklist had eight items that were each a
 * separate thing to remember, and the expensive ones fail silently: mail with
 * no password does not error, it just never sends, and the first person to
 * notice is a farmer who cannot recover their account.
 *
 * Run on the production host after deploying:
 *   php artisan mkulima:preflight
 *
 * Exits non-zero if anything blocking is wrong, so it can gate a deploy script.
 */
class PreflightCheck extends Command
{
    protected $signature = 'mkulima:preflight {--strict : Treat warnings as failures}';

    protected $description = 'Verify this environment is ready to serve production traffic';

    /** @var array<int, array{level: string, name: string, detail: string}> */
    private array $results = [];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=green;options=bold>Mkulima Forum — pre-flight</>');
        $this->line('  <fg=gray>'.config('app.url').' · '.config('app.env').'</>');
        $this->newLine();

        $this->checkApplication();
        $this->checkDatabase();
        $this->checkMail();
        $this->checkAuth();
        $this->checkWebhooks();
        $this->checkStorage();
        $this->checkQueue();

        return $this->report();
    }

    // ── Application ───────────────────────────────────────────────────

    private function checkApplication(): void
    {
        $this->section('Application');

        $this->assert(
            'APP_KEY is set',
            (string) config('app.key') !== '',
            'Run php artisan key:generate. Without it, every encrypted value and signed URL breaks.'
        );

        $this->assert(
            'APP_DEBUG is off',
            ! config('app.debug'),
            'APP_DEBUG=true leaks stack traces, env values and query contents to visitors.'
        );

        $url = (string) config('app.url');
        $this->assert(
            'APP_URL is a real https host',
            str_starts_with($url, 'https://') && ! str_contains($url, 'localhost'),
            "APP_URL is '{$url}'. Every verification and password-reset link is built from it, so a wrong value sends users to the wrong server."
        );

        $this->expect(
            'APP_ENV is production',
            config('app.env') === 'production',
            "APP_ENV is '".config('app.env')."'."
        );
    }

    // ── Database ──────────────────────────────────────────────────────

    private function checkDatabase(): void
    {
        $this->section('Database');

        try {
            DB::connection()->getPdo();
            $this->ok('Connection succeeds', DB::connection()->getDatabaseName());
        } catch (Throwable $e) {
            $this->blocking('Connection succeeds', $e->getMessage());

            return;
        }

        // The migration that makes password reset and email change possible.
        $this->assert(
            'password_reset_tokens table exists',
            Schema::hasTable('password_reset_tokens'),
            'Run php artisan migrate --force. Without this table every password-reset request throws.'
        );

        $this->assert(
            'users.pending_email column exists',
            Schema::hasColumn('users', 'pending_email'),
            'Run php artisan migrate --force. Email changes cannot be staged without it.'
        );

        $this->assert(
            'Tenants are seeded',
            DB::table('tenants')->exists(),
            'Run php artisan db:seed --class=TenantSeeder. Registration resolves a tenant by country code and fails without one.'
        );

        $this->expect(
            'At least one administrator exists',
            User::query()->whereIn('role', ['admin', 'superadmin'])->exists(),
            'No admin account found. Set ADMIN_EMAIL and ADMIN_PASSWORD, then run php artisan db:seed --class=AdminUserSeeder.'
        );
    }

    // ── Mail ──────────────────────────────────────────────────────────

    private function checkMail(): void
    {
        $this->section('Mail');

        $mailer = (string) config('mail.default');
        $this->assert(
            'A real mail transport is configured',
            ! in_array($mailer, ['log', 'array', ''], true),
            "MAIL_MAILER is '{$mailer}'. Verification and reset mail would be written to a log file, not delivered."
        );

        if ($mailer === 'smtp') {
            $this->assert(
                'SMTP host is set',
                (string) config('mail.mailers.smtp.host') !== '',
                'MAIL_HOST is empty.'
            );

            // The single most expensive silent failure on this platform.
            $this->assert(
                'SMTP password is set',
                (string) config('mail.mailers.smtp.password') !== '',
                'MAIL_PASSWORD is EMPTY. Password reset and email verification are on the critical path: '
                .'registration will create accounts that can never recover a lost password, and nothing will error.'
            );
        }

        $this->assert(
            'A from-address is set',
            (string) config('mail.from.address') !== '',
            'MAIL_FROM_ADDRESS is empty; most providers reject mail without one.'
        );
    }

    // ── Auth ──────────────────────────────────────────────────────────

    private function checkAuth(): void
    {
        $this->section('Authentication');

        $expiry = config('sanctum.expiration');
        $this->expect(
            'Token expiry is finite',
            $expiry !== null,
            'SANCTUM_TOKEN_EXPIRATION is unset, so API tokens never expire.'
        );

        $this->expect(
            'Session cookies are secure',
            (bool) config('session.secure', false),
            'SESSION_SECURE_COOKIE is off, so session cookies may travel over plain HTTP.'
        );
    }

    // ── Webhooks ──────────────────────────────────────────────────────

    private function checkWebhooks(): void
    {
        $this->section('Webhooks and integrations');

        foreach (['sms' => 'SMS_WEBHOOK_SECRET', 'ivr' => 'IVR_WEBHOOK_SECRET'] as $channel => $key) {
            $this->assert(
                "{$key} is set",
                (string) config("services.{$channel}.webhook_secret", '') !== '',
                "Without it, /api/{$channel}/* refuses ALL traffic in production. That is the safe failure, "
                .'but it looks like an outage if the gateway is live. Set the same value at the provider.'
            );
        }

        $hosts = config('services.short_links.allowed_hosts', []);
        $ownHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $this->assert(
            'Short-link allowlist includes this host',
            in_array($ownHost, array_map('strtolower', $hosts), true),
            "SHORT_LINK_ALLOWED_HOSTS does not contain '{$ownHost}', so links to your own domain would show the interstitial."
        );

        $sms = app(SmsService::class);
        $this->expect(
            'SMS gateway is configured',
            $sms->isConfigured(),
            'Gateway "'.$sms->gateway().'" has no credentials. Phone OTP cannot send; email auth is unaffected.'
        );
    }

    // ── Storage ───────────────────────────────────────────────────────

    private function checkStorage(): void
    {
        $this->section('Storage');

        $this->assert(
            'storage/ is writable',
            is_writable(storage_path()),
            'The application cannot write logs, cache or uploads.'
        );

        $this->expect(
            'public/storage symlink exists',
            is_link(public_path('storage')) || is_dir(public_path('storage')),
            'Run php artisan storage:link, or uploaded images will 404.'
        );
    }

    // ── Queue ─────────────────────────────────────────────────────────

    private function checkQueue(): void
    {
        $this->section('Queue');

        $connection = (string) config('queue.default');
        $this->expect(
            'Queue is not synchronous',
            $connection !== 'sync',
            'QUEUE_CONNECTION=sync sends mail inline, so a slow SMTP host stalls the sign-up request.'
        );

        if ($connection === 'database' && Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->count();
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

            $this->expect(
                'No large backlog of queued jobs',
                $pending < 100,
                "{$pending} jobs are waiting. Is a worker running? Verification and reset mail is queued."
            );

            $this->expect(
                'No failed jobs',
                $failed === 0,
                "{$failed} failed jobs. Check with php artisan queue:failed."
            );
        }
    }

    // ── Output ────────────────────────────────────────────────────────

    private function section(string $name): void
    {
        $this->line("  <options=bold>{$name}</>");
    }

    private function assert(string $name, bool $ok, string $detail): void
    {
        $ok ? $this->ok($name) : $this->blocking($name, $detail);
    }

    private function expect(string $name, bool $ok, string $detail): void
    {
        if ($ok) {
            $this->ok($name);

            return;
        }
        $this->results[] = ['level' => 'warn', 'name' => $name, 'detail' => $detail];
        $this->line("    <fg=yellow>!</> {$name}");
    }

    private function ok(string $name, ?string $note = null): void
    {
        $this->results[] = ['level' => 'pass', 'name' => $name, 'detail' => ''];
        $this->line("    <fg=green>✓</> {$name}".($note ? " <fg=gray>({$note})</>" : ''));
    }

    private function blocking(string $name, string $detail): void
    {
        $this->results[] = ['level' => 'fail', 'name' => $name, 'detail' => $detail];
        $this->line("    <fg=red>✗</> {$name}");
    }

    private function report(): int
    {
        $fails = array_filter($this->results, fn ($r) => $r['level'] === 'fail');
        $warns = array_filter($this->results, fn ($r) => $r['level'] === 'warn');
        $passes = array_filter($this->results, fn ($r) => $r['level'] === 'pass');

        $this->newLine();

        foreach ($fails as $r) {
            $this->line("  <fg=red;options=bold>BLOCKING</> {$r['name']}");
            $this->line("           <fg=gray>{$r['detail']}</>");
        }
        foreach ($warns as $r) {
            $this->line("  <fg=yellow;options=bold>WARNING</>  {$r['name']}");
            $this->line("           <fg=gray>{$r['detail']}</>");
        }

        $this->newLine();
        $this->line(sprintf(
            '  %d passed, %d warning(s), %d blocking',
            count($passes), count($warns), count($fails)
        ));
        $this->newLine();

        if ($fails !== []) {
            return self::FAILURE;
        }

        return ($this->option('strict') && $warns !== []) ? self::FAILURE : self::SUCCESS;
    }
}
