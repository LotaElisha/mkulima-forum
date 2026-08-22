<?php

namespace App\Settings;

use App\Models\ConfigSetting;
use App\Models\User;
use App\Services\Spine\AuditTrail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Reads and writes administrator-managed settings.
 *
 * Sits on top of the existing config_settings table and ConfigSetting model,
 * which already encrypt secrets at rest — this adds the schema whitelist, the
 * env fallback, the runtime config overlay and audit logging that records what
 * changed without ever recording a secret.
 *
 * Resolution order for any setting:
 *   1. the database value, if one has been saved
 *   2. the env value named by the schema  (the bootstrap fallback)
 *   3. the caller's own default
 */
class SettingsManager
{
    private const CACHE_KEY = 'managed_settings:all';

    private const CACHE_TTL = 3600;

    private ?bool $tableConfirmed = null;

    public function __construct(private readonly AuditTrail $audit) {}

    /**
     * Every stored value, keyed by setting key. Cached as a plain array rather
     * than as Eloquent models, so a cache hit costs no hydration and no
     * accidental writes.
     *
     * @return array<string, mixed>
     */
    public function stored(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            if (! $this->tableAvailable()) {
                return [];
            }

            return ConfigSetting::query()
                ->whereIn('key', array_keys(SettingsSchema::all()))
                ->get()
                // ->value runs the model accessor, which decrypts and casts.
                ->mapWithKeys(fn (ConfigSetting $s) => [$s->key => $s->value])
                ->all();
        });
    }

    /** Resolve one setting: database, then env, then the given default. */
    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->stored();

        if (array_key_exists($key, $stored) && $stored[$key] !== null && $stored[$key] !== '') {
            return $stored[$key];
        }

        $definition = SettingsSchema::find($key);

        if ($definition?->envKey) {
            $fromEnv = env($definition->envKey);
            if ($fromEnv !== null && $fromEnv !== '') {
                return $this->castFromEnv($definition, $fromEnv);
            }
        }

        return $default;
    }

    /**
     * Save one setting.
     *
     * Returns the audit-safe description of what changed. Secrets are recorded
     * as a rotation, never as a value — the audit log is read by more people
     * than the settings screen is.
     */
    public function set(string $key, mixed $value, ?User $actor = null): array
    {
        $definition = SettingsSchema::find($key);

        if (! $definition) {
            // The schema is a whitelist: an unknown key cannot be written, so a
            // crafted request cannot reach an arbitrary config path.
            throw new \InvalidArgumentException("Unknown setting [{$key}].");
        }

        $previous = $this->get($key);

        $setting = ConfigSetting::firstOrNew(['key' => $key]);
        $setting->group = $definition->group;
        $setting->type = $definition->type;
        $setting->is_encrypted = $definition->secret;
        $setting->description = $definition->label;
        $setting->value = $value;
        $setting->version = ($setting->version ?? 0) + 1;
        $setting->updated_by = $actor?->id;
        $setting->save();

        $this->flush();

        $change = $definition->secret
            ? ['setting' => $key, 'change' => $definition->label.' rotated']
            : [
                'setting' => $key,
                'from' => $this->auditSafe($previous),
                'to' => $this->auditSafe($value),
            ];

        $this->audit->record(
            $setting,
            $setting->wasRecentlyCreated ? 'setting.created' : 'setting.updated',
            $definition->secret ? ['value' => '[redacted]'] : ['value' => $this->auditSafe($previous)],
            $definition->secret ? ['value' => '[rotated]'] : ['value' => $this->auditSafe($value)],
            $actor,
            'admin'
        );

        return $change;
    }

    /**
     * Record operational state — "when did a test email last succeed", "when
     * did SMS last fail".
     *
     * Deliberately separate from set(): the schema is a strict whitelist of
     * things an administrator may EDIT, and these are things the system
     * OBSERVES. Routing them through set() would either force fake schema
     * entries or force the whitelist open. They are also not audit-logged,
     * because "the system noticed something" is not a configuration change and
     * would drown the real entries.
     */
    public function recordState(string $key, mixed $value): void
    {
        if (! $this->tableAvailable()) {
            return;
        }

        $setting = ConfigSetting::firstOrNew(['key' => $key]);
        $setting->group = 'state';
        $setting->type = 'string';
        $setting->is_encrypted = false;
        $setting->description = 'System-recorded state';
        $setting->value = $value;
        $setting->version = ($setting->version ?? 0) + 1;
        $setting->save();

        Cache::forget("config_state:{$key}");
    }

    /** Read operational state recorded by recordState(). */
    public function state(string $key, mixed $default = null): mixed
    {
        if (! $this->tableAvailable()) {
            return $default;
        }

        return Cache::remember(
            "config_state:{$key}",
            300,
            fn () => ConfigSetting::where('key', $key)->first()?->value
        ) ?? $default;
    }

    /** Remove a stored value so the setting falls back to env. */
    public function forget(string $key, ?User $actor = null): void
    {
        $setting = ConfigSetting::where('key', $key)->first();

        if (! $setting) {
            return;
        }

        $this->audit->record($setting, 'setting.cleared', null, ['value' => '[cleared]'], $actor, 'admin');
        $setting->delete();
        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        // The legacy ConfigRegistry caches per key and a public bundle; both
        // must go or the old readers serve a stale value for up to an hour.
        Cache::forget('config_settings:public');
        foreach (array_keys(SettingsSchema::all()) as $key) {
            Cache::forget("config_setting:{$key}");
        }
    }

    /**
     * Apply stored settings over Laravel's runtime configuration.
     *
     * This is what makes database-managed settings work under
     * `php artisan config:cache`. The cached config file holds the env-derived
     * values baked in at cache time; this overlays the database on top of it on
     * every request, so a setting changed in the dashboard takes effect without
     * anyone clearing a cache or SSHing anywhere.
     */
    public function applyToRuntimeConfig(): void
    {
        if (! $this->tableAvailable()) {
            return;
        }

        $stored = $this->stored();

        foreach (SettingsSchema::all() as $key => $definition) {
            if (! $definition->configPath) {
                continue;
            }
            if (! array_key_exists($key, $stored)) {
                continue;
            }

            $value = $stored[$key];
            if ($value === null || $value === '') {
                continue;
            }

            config([$definition->configPath => $this->normalise($definition, $value)]);
        }
    }

    /** @return array<string, mixed> */
    private function normaliseAll(): array
    {
        return $this->stored();
    }

    private function normalise(ManagedSetting $definition, mixed $value): mixed
    {
        // "none" is how the UI expresses "no SMTP encryption"; Laravel wants null.
        if ($definition->key === 'mail.smtp_encryption' && $value === 'none') {
            return null;
        }

        return $value;
    }

    private function castFromEnv(ManagedSetting $definition, mixed $raw): mixed
    {
        return match ($definition->type) {
            'integer' => (int) $raw,
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'json' => is_array($raw)
                ? $raw
                : array_values(array_filter(array_map('trim', explode(',', (string) $raw)))),
            default => $raw,
        };
    }

    /** Keep audit entries readable and bounded, and never leak a long blob. */
    private function auditSafe(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $value = (string) ($value ?? '');

        return $value === '' ? '(empty)' : mb_strimwidth($value, 0, 200, '…');
    }

    /**
     * Settings are read during boot, which also happens before the first
     * migration has ever run and inside `artisan migrate` itself.
     *
     * A "yes" is memoised for the life of this instance and never re-checked.
     * A "no" is memoised only on the instance — deliberately NOT in a static —
     * because a static would survive the whole PHP process: one boot while the
     * database was briefly unreachable, or before the first migration, and
     * every setting would read as unset until the worker was restarted. Under
     * php-fpm that could be hours of the platform silently ignoring its own
     * configuration. This bug was real: it made the settings invisible to the
     * entire test suite, because the provider boots before RefreshDatabase
     * migrates.
     */
    private function tableAvailable(): bool
    {
        if ($this->tableConfirmed === true) {
            return true;
        }

        try {
            $exists = DB::connection()->getSchemaBuilder()->hasTable('config_settings');
        } catch (Throwable) {
            return false;
        }

        if ($exists) {
            $this->tableConfirmed = true;
        }

        return $exists;
    }
}
