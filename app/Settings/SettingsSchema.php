<?php

namespace App\Settings;

/**
 * The catalogue of settings an administrator may manage from the dashboard.
 *
 * Deliberately a whitelist. Anything not listed here cannot be written through
 * the admin API at all, so a crafted request cannot reach an arbitrary config
 * key. Infrastructure-level values (database credentials, APP_KEY, cache and
 * session drivers) are intentionally absent — see README for why they must stay
 * in .env.
 */
final class SettingsSchema
{
    public const GROUP_GENERAL = 'general';

    public const GROUP_MAIL = 'mail';

    public const GROUP_SMS = 'sms';

    public const GROUP_IVR = 'ivr';

    public const GROUP_LINKS = 'links';

    /** @return array<string, ManagedSetting> keyed by setting key */
    public static function all(): array
    {
        static $cached = null;

        return $cached ??= collect(self::definitions())
            ->keyBy(fn (ManagedSetting $s) => $s->key)
            ->all();
    }

    public static function find(string $key): ?ManagedSetting
    {
        return self::all()[$key] ?? null;
    }

    /** @return array<int, ManagedSetting> */
    public static function inGroup(string $group): array
    {
        return array_values(array_filter(self::all(), fn ($s) => $s->group === $group));
    }

    /** @return array<int, string> */
    public static function groups(): array
    {
        return [
            self::GROUP_GENERAL,
            self::GROUP_MAIL,
            self::GROUP_SMS,
            self::GROUP_IVR,
            self::GROUP_LINKS,
        ];
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            self::GROUP_GENERAL => 'General',
            self::GROUP_MAIL => 'Email / SMTP',
            self::GROUP_SMS => 'SMS',
            self::GROUP_IVR => 'IVR',
            self::GROUP_LINKS => 'Short links',
            default => ucfirst($group),
        };
    }

    /** @return array<int, ManagedSetting> */
    private static function definitions(): array
    {
        return [
            // ── General ───────────────────────────────────────────────
            new ManagedSetting(
                key: 'app.name',
                group: self::GROUP_GENERAL,
                label: 'Application name',
                configPath: 'app.name',
                envKey: 'APP_NAME',
                rules: ['required', 'string', 'max:80'],
            ),
            new ManagedSetting(
                key: 'app.url',
                group: self::GROUP_GENERAL,
                label: 'Application URL',
                configPath: 'app.url',
                envKey: 'APP_URL',
                rules: ['required', 'url', 'max:255'],
                help: 'Every verification link, password-reset link and emailed URL is built from this. '
                    .'A wrong value sends every user to the wrong server.',
                superadminOnly: true,
                highImpact: true,
            ),
            new ManagedSetting(
                key: 'app.support_email',
                group: self::GROUP_GENERAL,
                label: 'Support email',
                rules: ['nullable', 'email', 'max:255'],
                help: 'Shown on public pages as the contact address.',
            ),
            new ManagedSetting(
                key: 'app.support_phone',
                group: self::GROUP_GENERAL,
                label: 'Support phone',
                rules: ['nullable', 'string', 'max:32'],
            ),
            new ManagedSetting(
                key: 'app.timezone',
                group: self::GROUP_GENERAL,
                label: 'Default timezone',
                configPath: 'app.timezone',
                envKey: 'APP_TIMEZONE',
                rules: ['required', 'timezone'],
                options: ['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'Africa/Kigali', 'UTC'],
            ),
            new ManagedSetting(
                key: 'app.locale',
                group: self::GROUP_GENERAL,
                label: 'Default language',
                configPath: 'app.locale',
                envKey: 'APP_LOCALE',
                rules: ['required', 'string', 'in:sw,en'],
                options: ['sw', 'en'],
                help: 'Language used for system emails and SMS when a user has no preference.',
            ),

            // ── Mail ──────────────────────────────────────────────────
            new ManagedSetting(
                key: 'mail.mailer',
                group: self::GROUP_MAIL,
                label: 'Mail driver',
                configPath: 'mail.default',
                envKey: 'MAIL_MAILER',
                rules: ['required', 'string', 'in:smtp,log,array,sendmail'],
                options: ['smtp', 'log', 'sendmail'],
                help: '"log" writes mail to the application log instead of sending it. Never use it in production.',
            ),
            new ManagedSetting(
                key: 'mail.smtp_host',
                group: self::GROUP_MAIL,
                label: 'SMTP host',
                configPath: 'mail.mailers.smtp.host',
                envKey: 'MAIL_HOST',
                rules: ['nullable', 'string', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.smtp_port',
                group: self::GROUP_MAIL,
                label: 'SMTP port',
                type: 'integer',
                configPath: 'mail.mailers.smtp.port',
                envKey: 'MAIL_PORT',
                rules: ['nullable', 'integer', 'between:1,65535'],
                help: '587 for STARTTLS, 465 for implicit TLS.',
            ),
            new ManagedSetting(
                key: 'mail.smtp_username',
                group: self::GROUP_MAIL,
                label: 'SMTP username',
                configPath: 'mail.mailers.smtp.username',
                envKey: 'MAIL_USERNAME',
                rules: ['nullable', 'string', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.smtp_password',
                group: self::GROUP_MAIL,
                label: 'SMTP password',
                type: 'secret',
                configPath: 'mail.mailers.smtp.password',
                envKey: 'MAIL_PASSWORD',
                rules: ['nullable', 'string', 'max:512'],
                help: 'Gmail requires an app password, not the account password. Stored encrypted and never shown again.',
                secret: true,
                superadminOnly: true,
            ),
            new ManagedSetting(
                key: 'mail.smtp_encryption',
                group: self::GROUP_MAIL,
                label: 'Encryption',
                configPath: 'mail.mailers.smtp.encryption',
                envKey: 'MAIL_ENCRYPTION',
                rules: ['nullable', 'string', 'in:tls,ssl,none'],
                options: ['tls', 'ssl', 'none'],
            ),
            new ManagedSetting(
                key: 'mail.from_address',
                group: self::GROUP_MAIL,
                label: 'From address',
                configPath: 'mail.from.address',
                envKey: 'MAIL_FROM_ADDRESS',
                rules: ['nullable', 'email', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.from_name',
                group: self::GROUP_MAIL,
                label: 'From name',
                configPath: 'mail.from.name',
                envKey: 'MAIL_FROM_NAME',
                rules: ['nullable', 'string', 'max:120'],
            ),

            // ── SMS ───────────────────────────────────────────────────
            new ManagedSetting(
                key: 'sms.provider',
                group: self::GROUP_SMS,
                label: 'SMS provider',
                configPath: 'services.sms.provider',
                envKey: 'SMS_PROVIDER',
                rules: ['required', 'string', 'in:africastalking,twilio,log'],
                options: ['africastalking', 'twilio', 'log'],
                help: '"log" writes messages to the application log instead of sending them.',
            ),
            new ManagedSetting(
                key: 'sms.sender_id',
                group: self::GROUP_SMS,
                label: 'Sender ID',
                configPath: 'services.sms.sender_id',
                envKey: 'SMS_SENDER_ID',
                rules: ['nullable', 'string', 'max:11'],
                help: 'Must be registered with TCRA before it will deliver in Tanzania.',
            ),
            new ManagedSetting(
                key: 'sms.africastalking_username',
                group: self::GROUP_SMS,
                label: "Africa's Talking username",
                configPath: 'services.africastalking.username',
                envKey: 'AFRICASTALKING_USERNAME',
                rules: ['nullable', 'string', 'max:120'],
            ),
            new ManagedSetting(
                key: 'sms.africastalking_api_key',
                group: self::GROUP_SMS,
                label: "Africa's Talking API key",
                type: 'secret',
                configPath: 'services.africastalking.api_key',
                envKey: 'AFRICASTALKING_API_KEY',
                rules: ['nullable', 'string', 'max:512'],
                secret: true,
                superadminOnly: true,
            ),
            new ManagedSetting(
                key: 'sms.twilio_sid',
                group: self::GROUP_SMS,
                label: 'Twilio SID',
                configPath: 'services.twilio.sid',
                envKey: 'TWILIO_SID',
                rules: ['nullable', 'string', 'max:120'],
            ),
            new ManagedSetting(
                key: 'sms.twilio_token',
                group: self::GROUP_SMS,
                label: 'Twilio auth token',
                type: 'secret',
                configPath: 'services.twilio.token',
                envKey: 'TWILIO_TOKEN',
                rules: ['nullable', 'string', 'max:512'],
                secret: true,
                superadminOnly: true,
            ),
            new ManagedSetting(
                key: 'sms.twilio_from',
                group: self::GROUP_SMS,
                label: 'Twilio from number',
                configPath: 'services.twilio.from',
                envKey: 'TWILIO_FROM',
                rules: ['nullable', 'string', 'max:32'],
            ),
            new ManagedSetting(
                key: 'sms.webhook_secret',
                group: self::GROUP_SMS,
                label: 'SMS webhook secret',
                type: 'secret',
                configPath: 'services.sms.webhook_secret',
                envKey: 'SMS_WEBHOOK_SECRET',
                rules: ['nullable', 'string', 'min:16', 'max:512'],
                help: 'Set the same value at the provider, sent as the X-Webhook-Signature header. '
                    .'Without it, /api/sms/* refuses all traffic in production.',
                secret: true,
                superadminOnly: true,
            ),

            // ── IVR ───────────────────────────────────────────────────
            new ManagedSetting(
                key: 'ivr.webhook_secret',
                group: self::GROUP_IVR,
                label: 'IVR webhook secret',
                type: 'secret',
                configPath: 'services.ivr.webhook_secret',
                envKey: 'IVR_WEBHOOK_SECRET',
                rules: ['nullable', 'string', 'min:16', 'max:512'],
                help: 'Without it, /api/ivr/* refuses all traffic in production.',
                secret: true,
                superadminOnly: true,
            ),

            // ── Short links ───────────────────────────────────────────
            new ManagedSetting(
                key: 'links.allowed_hosts',
                group: self::GROUP_LINKS,
                label: 'Allowed redirect hosts',
                type: 'json',
                configPath: 'services.short_links.allowed_hosts',
                envKey: 'SHORT_LINK_ALLOWED_HOSTS',
                rules: ['array'],
                help: 'Hosts a short link may redirect straight to. Subdomains are included. '
                    .'Anything else shows an interstitial naming the destination, so this domain '
                    .'cannot be borrowed for a phishing redirect.',
            ),
        ];
    }
}
