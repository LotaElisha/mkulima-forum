# Mkulima Forum — Go-Live Checklist

Last verified: 22 August 2026, against `master` (merge commit `db2e7438`).

## Run this first

```bash
php artisan mkulima:preflight
```

It checks every blocking item below against the live environment and exits
non-zero if anything is wrong, so it can gate a deploy script. Written because
the expensive failures here are silent: mail with no password does not error,
it just never sends, and the first person to notice is a farmer who cannot
recover their account.

`[x]` items were completed and verified during the pre-launch audit.
`[ ]` items need a credential, a decision, or access to infrastructure.

Full findings: [`MKULIMA_FORUM_AUDIT.md`](./MKULIMA_FORUM_AUDIT.md)

---

## Blocks launch

Merged to `master` as `db2e7438` — the branch your runbook deploys from.

- [ ] **Set `MAIL_PASSWORD` in `.env.production`.** Still empty — this is the one
      remaining item that will break a launch. Gmail SMTP needs an app password, not the
      account password. `mkulima:preflight` fails on it. Verify with a real end-to-end send.
- [ ] **Set `DB_PASSWORD` in `.env.production`.** Also empty.
- [x] **`APP_URL` confirmed** as `https://mkulimaforum.app` — matches the Nginx domains in
      `DEPLOYMENT_RUNBOOK.md`. The Flutter client defaulted to `mkulimaforum.com`, a host the
      server does not serve; fixed in `17fcd29a`.
- [ ] **Run the new migration** — `2026_08_22_000001_create_password_reset_and_email_change_tables`.
      Without it, every password-reset request throws.
- [ ] **Start the queue worker** under supervisor (`php artisan queue:work`).
      Verification and reset mail is queued; with no worker, nothing sends.
- [x] **`SMS_WEBHOOK_SECRET` and `IVR_WEBHOOK_SECRET` generated** and written to
      `.env.production` (backup taken first). **You still need to set the same values at
      Africa's Talking / the IVR provider**, sent as the `X-Webhook-Signature` header.
- [x] `SHORT_LINK_ALLOWED_HOSTS`, `SANCTUM_TOKEN_EXPIRATION` and `SMS_PROVIDER` set.
- [x] Password reset, email verification, password change, and proof-of-ownership email change
- [x] Upload validation hardened across all seven upload paths
- [x] Webhook signature verification and per-IP throttling
- [x] Token lifetime consistent between the API response and `config/sanctum.php`
- [x] Tenant resolution no longer depends on hardcoded row ids
- [x] `/download` no longer links to a nonexistent web build

## Database

- [ ] Production database provisioned, credentials in `.env.production`
      (currently configured as PostgreSQL on `127.0.0.1:5433`)
- [ ] `php artisan migrate --force` run against production (`mkulima:preflight` verifies)
- [ ] Seeders run: `TenantSeeder`, `RolesAndPermissionsSeeder`, `SpineSeeder`,
      `RegulatoryAuthoritySeeder`, `FeatureFlagSeeder`
- [ ] `ADMIN_EMAIL` and a 12+ character `ADMIN_PASSWORD` set before `AdminUserSeeder`
      (it refuses to run without them — this is deliberate)
- [ ] **Automated backups configured and a restore tested.** Not currently set up.
- [ ] Index review under production data volume — check `market_prices`,
      `verification_scans`, `analytics_events`
- [x] 55 migrations present and applying cleanly from scratch
- [x] Test suite: 155 passing, 45 new, no regressions

## Security

- [x] No `.env` file tracked in git (verified)
- [x] No API keys in the Flutter client (verified)
- [x] Upload validation whitelists by extension and sniffed MIME type; SVG refused everywhere
- [x] Rate limiting on auth, OTP, password reset, uploads, search and weather
- [x] HSTS on HTTPS responses, `X-Frame-Options: SAMEORIGIN`, `nosniff`
- [x] CSP with per-request nonces
- [x] Password reset endpoints do not leak whether an account exists
- [x] Ownership scoping verified on farms, wallet, bot conversations and moderation
- [x] **`git rm -r --cached admin-dashboard/node_modules`** — done; 15,503 files untracked,
      `.gitignore` already covered the path (files remain on disk)
- [x] **Stale APKs out of the web root** — the two old test builds (343 MB) were moved to
      `_to_delete/stale-apks/`. One current APK remains and the download page reads it from
      disk. Delete that folder yourself when you're ready; I cannot remove files.
- [ ] Rotate `GEMINI_API_KEY` and `OPENWEATHER_API_KEY` if they were ever shared
- [ ] HTTPS enforced at the web server, with a valid certificate and auto-renewal
- [ ] CORS reviewed for the production origin
- [x] Host allowlist on `/c/{slug}` — set `SHORT_LINK_ALLOWED_HOSTS` for production;
      anything else shows an interstitial naming the destination
- [x] Privileged attributes out of `User::$fillable` — role, status, is_active,
      is_verified_expert, kyc_status, both verification timestamps and password. Writes go
      through `User::provision()` / `setPrivileged()`; five tests assert escalation fails
- [x] Default vendor password `password123` removed — vendors without a supplied password
      now get a random credential and a reset link

## Authentication

- [x] Register with email
- [x] Verify email (signed, expiring links)
- [x] Log in with email
- [x] Reset a forgotten password
- [x] Change password (requires current password, signs out other devices)
- [x] Change email securely (staged, password-gated, re-verified)
- [x] Resend verification, throttled
- [x] Social sign-in (Google, Apple) — code paths verified
- [ ] Phone + OTP — architecture complete behind `App\Contracts\SmsProvider`;
      enable via `auth.otp_enabled` once an SMS provider is credentialled.
      **Not a launch blocker** — email auth is production-ready.
- [x] **Account linking** — a signed-in user verifying an unclaimed number extends their
      existing account instead of creating a second one; a number on another account is
      refused rather than merged. Four endpoints under `/api/auth/`, ten tests.
- [ ] Enable phone linking in the app UI (backend is ready; the Flutter screens for
      `/api/auth/phone/link/*` are not built yet)

## Application

- [x] Public routes all return 200 (13 pages verified)
- [x] `/api/health` responds
- [x] No JavaScript console errors on any public page
- [x] No horizontal overflow at 360, 375, 390, 412, 430, 768, 820 or 1024px
- [x] Touch targets at or above 44px across the public tier
- [x] Icons no longer depend on a network request
- [x] `npm run build` fixed — it rendered `view('landing')`, a view nothing has routed to
      since the multi-page rebuild, so every deploy shipped a stale `dist/index.html`. Now
      runs `php artisan mkulima:export-landing`, which renders the live home page and fails
      loudly rather than writing a broken file
- [ ] `php artisan config:cache route:cache view:cache` in production
- [ ] `APP_DEBUG=false` confirmed in production
- [ ] Application version set (`config('app.version')`) — the download page reads it

## Observability

- [ ] **Error tracking configured** (Sentry, Bugsnag or equivalent). Not currently set up;
      launching without it means learning about failures from users.
- [ ] Log rotation configured (`LOG_LEVEL=error` is already correct for production)
- [ ] Uptime monitoring on `/api/health` and `/up`
- [ ] Analytics — `AnalyticsEvent` model exists; confirm events are actually written

## Mobile app

- [ ] **`flutter analyze && flutter build apk --dart-define=API_URL=https://mkulimaforum.app/api`**
      Nothing Dart was compiled — every route to a toolchain was blocked from the audit
      environment (storage.googleapis.com, dl.google.com, pub.dev and the GitHub archive all
      refused). Static checks pass: every `MkColors` token resolves, no unused local imports,
      all delimiters balanced. That is not a build.
- [ ] Screenshot the rebuilt screens and review
- [ ] Signed release build with a production keystore (the current APK is signed with a
      test key and the download page says so)
- [ ] **`firebase_messaging` is commented out of `pubspec.yaml`** — push notifications do
      not work, while the backend has a complete `PushNotificationService`
- [x] `iot_screen` and `drone_screen` no longer open on an error — both entry points carry
      an "Inakuja" label and explain rather than failing
- [ ] Verify offline behaviour (Drift) on a real device with the network off
- [x] Auth token stored in `flutter_secure_storage`, not `SharedPreferences`
- [x] Bottom navigation already matches the target architecture
      (Home / Marketplace / Scan / Community / Profile)

## Content and legal

- [ ] `/privacy` and `/terms` reviewed by counsel — registration now records consent to both
- [ ] Impact metrics on `/impact` are currently null placeholders; publish real numbers or
      remove the section
- [ ] Confirm the "120K+ farmers" figure on the home page is real before launch
- [ ] Contact email `hello@mkulimaforum.com` is monitored

## Post-launch (first week)

- [ ] Watch the error tracker daily
- [ ] Check `sms_logs` for delivery failures once SMS is live
- [ ] Check the `failed_jobs` table for queued mail that did not send
- [ ] Watch OpenWeather and Gemini quota consumption
- [ ] Review `audit_logs` for anything unexpected
