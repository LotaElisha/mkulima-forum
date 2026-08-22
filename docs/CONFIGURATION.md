# Production Configuration

How settings are stored, resolved and secured, and which ones can never leave the server.

## Why not just write to `.env`

An admin form that rewrites `.env` looks simplest and is a trap:

- `php artisan config:cache` bakes `env()` into a cached array. After that, `env()`
  returns `null` everywhere else in the application, so anything reading `env()` at
  request time is already broken in production.
- Rewriting `.env` needs the web user to have write access to a file that also holds
  `APP_KEY` and the database password.
- Two web workers can rewrite it concurrently and lose each other's changes.
- There is no history, no author, and no way to know who changed what.

## The architecture

```
Admin dashboard  →  ConfigurationController  →  SettingsManager  →  config_settings table
                                                       ↓
                                         SettingsServiceProvider::boot()
                                                       ↓
                                          Laravel runtime config()
```

`SettingsServiceProvider` overlays the database onto Laravel's configuration on every
request. The cached config supplies the bootstrap defaults from `.env`; the database is
layered on top. A setting changed in the dashboard therefore applies **immediately**,
with no cache clear, no deploy and no SSH — and `php artisan config:cache` stays safe to
run.

`.env` remains the fallback, exactly as intended:

```php
setting('mail.smtp_host', env('MAIL_HOST'));
```

Resolution order for every setting: **database → `.env` (per the schema) → the caller's
default**.

## Reading a setting

Prefer Laravel's own `config()` for anything with a `configPath` — the provider has
already overlaid the database onto it, so ordinary framework code such as `Mail` picks
up dashboard changes with no modification:

```php
config('mail.mailers.smtp.host');   // database value if set, else .env
```

Use the `setting()` helper for values with no config path of their own, or when you
deliberately want to bypass the overlay:

```php
setting('app.support_email', 'hello@mkulimaforum.app');
```

## Adding a setting

One entry in `App\Settings\SettingsSchema`. The API, the validation, the masking, the
runtime overlay and the admin UI are all derived from it — there is no second place to
edit:

```php
new ManagedSetting(
    key: 'mail.smtp_host',
    group: self::GROUP_MAIL,
    label: 'SMTP host',
    configPath: 'mail.mailers.smtp.host',
    envKey: 'MAIL_HOST',
    rules: ['nullable', 'string', 'max:255'],
),
```

The schema is a **whitelist**. A key that is not listed cannot be written through the
admin API at all, so a crafted request cannot reach an arbitrary config path such as the
database password.

## Managed in Admin → System → Configuration

| Group | Settings |
|---|---|
| General | App name, **APP_URL**, support email/phone, timezone, default language |
| Email | Driver, SMTP host/port/username/**password**, encryption, from address/name |
| SMS | Provider, sender ID, Africa's Talking username/**API key**, Twilio SID/**token**/from, **webhook secret** |
| IVR | **Webhook secret** |
| Short links | Allowed redirect hosts |

**Bold** = secret: encrypted at rest, never returned to the client, superadmin only.

## Must stay in `.env`

These cannot be database-managed, and the reasons are not stylistic:

| Setting | Why |
|---|---|
| `DB_*` | Chicken-and-egg — the settings live *in* the database. |
| `APP_KEY` | It is the key the secrets are encrypted with. Storing it beside them defeats encryption entirely. |
| `APP_ENV`, `APP_DEBUG` | Read during bootstrap, before any database connection exists. |
| `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION` | Needed to boot the very services the settings layer depends on. |

## Security model

- **Encrypted at rest.** Secrets pass through `Crypt` in the `ConfigSetting` model's
  accessor/mutator. The raw column never contains plaintext.
- **Write-only.** The API returns `value: null` and `is_set: true|false` for a secret —
  not a mask, not a prefix. An operator can tell "configured" from "empty" without the
  value leaving the server even once. A blank field on save means "leave unchanged", so
  the value never has to be re-typed to edit its neighbours.
- **Superadmin only.** Every secret is `superadminOnly`. The check deliberately does not
  use `$user->can()`: `User::can()` is overridden in this codebase to return true for
  anyone whose role is `admin` **or** `superadmin`, so it cannot distinguish the two.
  `system.secrets.manage` is also kept out of the admin permission list in
  `RolesAndPermissionsSeeder` for the same reason.
- **High-impact confirmation.** Changing `APP_URL` reconfigures how every emailed link
  reaches users, so it returns `409` until the client re-sends with
  `confirm_high_impact`.
- **Validated server-side** against the schema's own rules before anything is written.
- **Rate limited**: test email 6/10min, test SMS 3/10min, secret rotation 5/hour.
- **Audit logged.** Every change records the actor, IP, user agent and timestamp. A
  non-secret records its before and after; a secret records only
  `SMTP password rotated` — never the value.

## Rotating a webhook secret

`Admin → System → Configuration → SMS` (or IVR) → **Rotate secret**. The new value is
shown **once**, because it has to be pasted into the provider's dashboard. After that
response it is only ever stored encrypted.

Half of this handshake lives outside the application: the same value must be set at
Africa's Talking / the IVR provider, sent as the `X-Webhook-Signature` header. Without
it, `/api/sms/*` and `/api/ivr/*` refuse all traffic in production — the safe failure,
but it presents as an outage if the gateway is already live.

## Verifying a deployment

```bash
php artisan mkulima:preflight
```

Eighteen checks covering application, database, mail, auth, integrations, storage and
queue. Exits non-zero on anything blocking, so it can gate a deploy script. The same
checks render at **Admin → System → Configuration → Readiness**, from one shared service,
so the terminal and the dashboard cannot disagree.

A green mail configuration is not proof of delivery. Use **Send a test email** — empty
SMTP credentials do not error, mail simply never arrives, and the first person to notice
is a farmer who cannot recover their account.

## Queue worker

Verification and reset mail is queued so a slow SMTP host cannot stall a sign-up request.
That means **nothing sends unless a worker is running**. The dashboard reports the
backlog; it cannot start the process. Supervisor:

```ini
[program:mkulima-worker]
command=php /opt/data/projects/mkulima-forum/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/opt/data/projects/mkulima-forum
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/mkulima-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start mkulima-worker:*
```

After each deploy: `php artisan queue:restart` so workers pick up new code.

## Not yet implemented

Stated plainly rather than left to be discovered:

- **Error tracking.** No tracker is installed, so production failures surface only in
  `storage/logs`. Requires `composer require sentry/sentry-laravel` plus a DSN.
- **Backups.** No automated backup exists. `spatie/laravel-backup` is the usual choice.
  Both need a composer install, which could not be run in the audit environment.
- **Queue control from the dashboard.** Reporting only. Starting, restarting and
  supervising a worker is the process manager's job, and pretending otherwise would give
  a false sense of control.
