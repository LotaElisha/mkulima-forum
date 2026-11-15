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
            self::GROUP_GENERAL => 'Jumla',
            self::GROUP_MAIL => 'Barua pepe (SMTP)',
            self::GROUP_SMS => 'SMS',
            self::GROUP_IVR => 'IVR',
            self::GROUP_LINKS => 'Viungo vifupi',
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
                label: 'Jina la programu',
                configPath: 'app.name',
                envKey: 'APP_NAME',
                rules: ['required', 'string', 'max:80'],
            ),
            new ManagedSetting(
                key: 'app.url',
                group: self::GROUP_GENERAL,
                label: 'Anwani ya tovuti (URL)',
                configPath: 'app.url',
                envKey: 'APP_URL',
                rules: ['required', 'url', 'max:255'],
                help: 'Kila kiungo cha uthibitisho, cha kubadilisha nenosiri na kila URL kwenye barua pepe hujengwa kutoka hapa. '
                    .'Thamani isiyo sahihi inapeleka kila mtumiaji kwenye seva isiyo sahihi.',
                superadminOnly: true,
                highImpact: true,
            ),
            new ManagedSetting(
                key: 'app.support_email',
                group: self::GROUP_GENERAL,
                label: 'Barua pepe ya msaada',
                rules: ['nullable', 'email', 'max:255'],
                help: 'Inaonyeshwa kwenye kurasa za umma kama anwani ya mawasiliano.',
            ),
            new ManagedSetting(
                key: 'app.support_phone',
                group: self::GROUP_GENERAL,
                label: 'Simu ya msaada',
                rules: ['nullable', 'string', 'max:32'],
            ),
            new ManagedSetting(
                key: 'app.timezone',
                group: self::GROUP_GENERAL,
                label: 'Saa za eneo (chaguo-msingi)',
                configPath: 'app.timezone',
                envKey: 'APP_TIMEZONE',
                rules: ['required', 'timezone'],
                options: ['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'Africa/Kigali', 'UTC'],
            ),
            new ManagedSetting(
                key: 'app.locale',
                group: self::GROUP_GENERAL,
                label: 'Lugha ya chaguo-msingi',
                configPath: 'app.locale',
                envKey: 'APP_LOCALE',
                rules: ['required', 'string', 'in:sw,en'],
                options: ['sw', 'en'],
                help: 'Lugha ya barua pepe na SMS za mfumo pale mtumiaji hajachagua lugha.',
            ),

            // ── Mail ──────────────────────────────────────────────────
            new ManagedSetting(
                key: 'mail.mailer',
                group: self::GROUP_MAIL,
                label: 'Njia ya kutuma barua pepe',
                configPath: 'mail.default',
                envKey: 'MAIL_MAILER',
                rules: ['required', 'string', 'in:smtp,log,array,sendmail'],
                options: ['smtp', 'log', 'sendmail'],
                help: '"log" huandika barua kwenye kumbukumbu za mfumo badala ya kuzituma. Usiitumie kwenye mfumo halisi.',
            ),
            new ManagedSetting(
                key: 'mail.smtp_host',
                group: self::GROUP_MAIL,
                label: 'Seva ya SMTP',
                configPath: 'mail.mailers.smtp.host',
                envKey: 'MAIL_HOST',
                rules: ['nullable', 'string', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.smtp_port',
                group: self::GROUP_MAIL,
                label: 'Port ya SMTP',
                type: 'integer',
                configPath: 'mail.mailers.smtp.port',
                envKey: 'MAIL_PORT',
                rules: ['nullable', 'integer', 'between:1,65535'],
                help: '587 kwa STARTTLS, 465 kwa TLS ya moja kwa moja.',
            ),
            new ManagedSetting(
                key: 'mail.smtp_username',
                group: self::GROUP_MAIL,
                label: 'Jina la mtumiaji la SMTP',
                configPath: 'mail.mailers.smtp.username',
                envKey: 'MAIL_USERNAME',
                rules: ['nullable', 'string', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.smtp_password',
                group: self::GROUP_MAIL,
                label: 'Nenosiri la SMTP',
                type: 'secret',
                configPath: 'mail.mailers.smtp.password',
                envKey: 'MAIL_PASSWORD',
                rules: ['nullable', 'string', 'max:512'],
                help: 'Gmail inahitaji nenosiri la programu (app password), si nenosiri la akaunti. Linahifadhiwa likiwa limesimbwa na halionyeshwi tena.',
                secret: true,
                superadminOnly: true,
            ),
            new ManagedSetting(
                key: 'mail.smtp_encryption',
                group: self::GROUP_MAIL,
                label: 'Usimbaji (encryption)',
                configPath: 'mail.mailers.smtp.encryption',
                envKey: 'MAIL_ENCRYPTION',
                rules: ['nullable', 'string', 'in:tls,ssl,none'],
                options: ['tls', 'ssl', 'none'],
            ),
            new ManagedSetting(
                key: 'mail.from_address',
                group: self::GROUP_MAIL,
                label: 'Anwani ya mtumaji',
                configPath: 'mail.from.address',
                envKey: 'MAIL_FROM_ADDRESS',
                rules: ['nullable', 'email', 'max:255'],
            ),
            new ManagedSetting(
                key: 'mail.from_name',
                group: self::GROUP_MAIL,
                label: 'Jina la mtumaji',
                configPath: 'mail.from.name',
                envKey: 'MAIL_FROM_NAME',
                rules: ['nullable', 'string', 'max:120'],
            ),

            // ── SMS ───────────────────────────────────────────────────
            new ManagedSetting(
                key: 'sms.provider',
                group: self::GROUP_SMS,
                label: 'Mtoa huduma wa SMS',
                configPath: 'services.sms.provider',
                envKey: 'SMS_PROVIDER',
                rules: ['required', 'string', 'in:africastalking,twilio,log'],
                options: ['africastalking', 'twilio', 'log'],
                help: '"log" huandika ujumbe kwenye kumbukumbu za mfumo badala ya kuutuma.',
            ),
            new ManagedSetting(
                key: 'sms.sender_id',
                group: self::GROUP_SMS,
                label: 'Jina la mtumaji wa SMS (Sender ID)',
                configPath: 'services.sms.sender_id',
                envKey: 'SMS_SENDER_ID',
                rules: ['nullable', 'string', 'max:11'],
                help: 'Lazima lisajiliwe TCRA kabla ujumbe haujafika Tanzania.',
            ),
            new ManagedSetting(
                key: 'sms.africastalking_username',
                group: self::GROUP_SMS,
                label: "Jina la mtumiaji la Africa's Talking",
                configPath: 'services.africastalking.username',
                envKey: 'AFRICASTALKING_USERNAME',
                rules: ['nullable', 'string', 'max:120'],
            ),
            new ManagedSetting(
                key: 'sms.africastalking_api_key',
                group: self::GROUP_SMS,
                label: "API key ya Africa's Talking",
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
                label: 'Auth token ya Twilio',
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
                label: 'Namba ya kutuma ya Twilio',
                configPath: 'services.twilio.from',
                envKey: 'TWILIO_FROM',
                rules: ['nullable', 'string', 'max:32'],
            ),
            new ManagedSetting(
                key: 'sms.webhook_secret',
                group: self::GROUP_SMS,
                label: 'Siri ya webhook ya SMS',
                type: 'secret',
                configPath: 'services.sms.webhook_secret',
                envKey: 'SMS_WEBHOOK_SECRET',
                rules: ['nullable', 'string', 'min:16', 'max:512'],
                help: 'Weka thamani hiyo hiyo kwa mtoa huduma; inatumwa kama kichwa cha X-Webhook-Signature. '
                    .'Bila hiyo, /api/sms/* inakataa maombi yote kwenye mfumo halisi.',
                secret: true,
                superadminOnly: true,
            ),

            // ── IVR ───────────────────────────────────────────────────
            new ManagedSetting(
                key: 'ivr.webhook_secret',
                group: self::GROUP_IVR,
                label: 'Siri ya webhook ya IVR',
                type: 'secret',
                configPath: 'services.ivr.webhook_secret',
                envKey: 'IVR_WEBHOOK_SECRET',
                rules: ['nullable', 'string', 'min:16', 'max:512'],
                help: 'Bila hiyo, /api/ivr/* inakataa maombi yote kwenye mfumo halisi.',
                secret: true,
                superadminOnly: true,
            ),

            // ── Short links ───────────────────────────────────────────
            new ManagedSetting(
                key: 'links.allowed_hosts',
                group: self::GROUP_LINKS,
                label: 'Tovuti zinazoruhusiwa kuelekezwa',
                type: 'json',
                configPath: 'services.short_links.allowed_hosts',
                envKey: 'SHORT_LINK_ALLOWED_HOSTS',
                rules: ['array'],
                help: 'Tovuti ambazo kiungo kifupi kinaweza kuelekeza moja kwa moja, pamoja na subdomain zake. '
                    .'Nyingine zote zinaonyesha ukurasa wa kati unaotaja mahali kiungo kinaenda, ili '
                    .'tovuti hii isitumike kwa utapeli (phishing).',
            ),
        ];
    }
}
