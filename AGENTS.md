# Working on Mkulima Forum

Read this before changing anything. It exists because a pre-launch audit hardened
a number of things that look like friction and are not — several of them will
read as bugs to an agent that has not been told why they are there.

## Layout

| Path | What it is |
|---|---|
| `app/`, `routes/`, `database/` | Laravel 13 / PHP 8.4 API and web tier |
| `mkulima_app/` | Flutter client (Android + web; there is no `ios/`) |
| `admin-dashboard/` | React + Vite admin SPA, built into `public/admin` |
| `resources/views/` | Blade public site |
| `docs/` | `FLUTTER_HANDOFF.md`, `CONFIGURATION.md` |
| `MKULIMA_FORUM_AUDIT.md` | Every finding, classified P0–P3 |
| `GO_LIVE_CHECKLIST.md` | What is done and what still blocks launch |

The users are Tanzanian farmers on cheap Android phones and metered data. The UI
language is Swahili. Design floor is 360px wide.

## Commands

```bash
php artisan test                      # 181 tests, all passing - keep it that way
php artisan mkulima:preflight         # gates a deploy on every blocking item
cd mkulima_app && ./scripts/verify.sh # pub get, codegen, analyze, test, APK
```

The Flutter client takes exactly one build-time variable:

```bash
flutter build apk --release --dart-define=API_URL=https://mkulimaforum.app/api
```

`.app`, not `.com`. The server does not serve `.com`.

## Do not undo these

Each was a real finding. Reverting one reopens the hole it closed.

- **Uploads go through `App\Support\UploadRules`**, which whitelists by
  extension *and* sniffed MIME type. Laravel's `image` rule accepts SVG, which
  is a scriptable document. Do not replace this with `image` or `mimes:` alone.
- **Privileged attributes are out of `User::$fillable`** — role, status,
  is_active, is_verified_expert, kyc_status, both verification timestamps and
  password. Writes go through `User::provision()` / `setPrivileged()`. Five
  tests assert that escalation via mass assignment fails. If a `create()` call
  is silently dropping a field, that is the guard working.
- **Password reset and email endpoints answer identically for known and unknown
  addresses.** That is anti-enumeration, not a missing error case.
- **Webhooks require a signature** (`VerifyWebhookSignature`, `hash_equals`) and
  refuse outright in production when no secret is configured.
- **`/c/{slug}` checks a host allowlist** and shows an interstitial for anything
  off it, so the platform's own domain cannot be lent to a phishing link.
- **Release builds refuse to sign without `android/key.properties`.** Debug keys
  must never sign a published APK.
- **Never merge two accounts automatically.** `AccountIdentityService` refuses
  when an identity belongs to a different account. Merging moves farm records
  and wallet balances, and must not be reachable by someone holding a phone
  number. See `docs/FLUTTER_HANDOFF.md` §3.
- **Secrets are encrypted at rest and never returned to the frontend.** The
  admin configuration centre masks existing values and supports rotation
  without revealing the old one. Log `SMS webhook secret rotated`, never the
  secret. Sensitive settings are Super Admin only — and note `User::can()` is
  overridden to return true for admin *or* superadmin, so it cannot tell the
  two apart. Do not use it for a secrets gate.

## Configuration

Operational settings live in the database and are edited at
**Admin → System → Configuration**, not by hand-editing `.env`:

```php
setting('mail.smtp_host', env('MAIL_HOST'));
```

`.env` is bootstrap and fallback only. This matters because `config:cache` makes
`env()` return null at request time, so any request-time `env()` read is broken
in production. `SettingsServiceProvider` overlays the database onto runtime
config each request. Full detail in `docs/CONFIGURATION.md`.

`auth.otp_enabled` is off by default. Phone/OTP sign-in and phone linking are
architecturally complete behind `App\Contracts\SmsProvider` but dark until an
SMS provider is credentialled. Email auth is production-ready and phone auth was
never allowed to gate the launch.

## Design

Roughly **90% white**, green used strategically — CTAs, nav, badges, icons. A
large green field is wrong. Mobile-first from 360px. 44px minimum touch targets,
48px on primary actions, nothing below 13px. Icons are inline SVG
(`resources/views/components/icon.blade.php`) rather than an icon font, because
a ligature font that fails to load prints `check_circle` as literal text on the
page — which is exactly what used to happen.

Swahili strings run longer than their English equivalents. Check for clipping.
