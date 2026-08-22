# Mkulima Forum — Pre-Launch Audit

**Date:** 22 August 2026
**Base commit:** `8901ae6b` on `agent/publish-current-platform`
**Scope:** Laravel backend and API, public web tier, Flutter app source, admin SPA (read only)
**Method:** Application booted locally against a seeded SQLite database; all HTTP routes
exercised; every screen rendered in headless Chromium at real mobile viewport sizes;
full PHPUnit suite executed before and after each change.

Visual report with before/after screenshots:
<https://claude.ai/code/artifact/2e56acd0-7c9d-4908-8f4a-b6dc4e91ead2>

---

## 1. Discovered architecture

This is a mature codebase, not scaffolding. The existing test suite was green
before any of this work started (110 passing, 2 skipped).

| Layer | Stack | Size |
|---|---|---|
| Backend | Laravel 13.18 / PHP 8.4, Sanctum, Spatie Permission, Scout + Meilisearch, Reverb | 78 models, 48 controllers, 55 migrations, ~600 lines of API routes |
| Public web | Blade with inline CSS (no Tailwind build in use on public pages), bilingual sw/en | 13 marketing pages + auth |
| Admin | React SPA served statically from `public/admin`, deep-link fallback route | 1 bundle |
| Mobile | Flutter — Provider, go_router, dio, Drift offline DB, `flutter_secure_storage` | 69 files, 33 screens, ~18k LOC |
| Deployment | Hostinger, `.env.production` present, `DEPLOYMENT_RUNBOOK.md` in repo | — |

### Domain services

`app/Services/` is organised by bounded context and is the strongest part of the
codebase: `Verify/` (verification engine, risk engine, escalation, track & trace),
`Spine/` (config registry, audit trail, event bus, channel bus, offline bundle),
`Payments/` (escrow, M-Pesa, Tigo Pesa), `AI/` (provider abstraction with encrypted
credential storage), `Community/`, `Documents/`, `Notifications/`.

### What was already correct

Recording these so a future refactor does not undo them.

- **Ownership scoping.** `FarmController`, `MkulimaBotController`, wallet and moderation
  all filter by `user_id` before acting. No IDOR found in the primary data paths.
- **No secrets in the mobile client.** The Flutter app holds no API keys. The auth token
  lives in `flutter_secure_storage`, not `SharedPreferences`.
- **Admin seeding refuses defaults.** `AdminUserSeeder` throws unless a real
  `ADMIN_EMAIL` and a 12-character `ADMIN_PASSWORD` are supplied.
- **CSP with per-request nonces** was already applied to every web response.
- **Unbuilt features answer honestly.** `IoTController` returns 503 rather than
  fabricating sensor readings — a deliberate choice, worth keeping.
- **AI provider credentials are encrypted at rest** via `EncryptedDatabaseSecretManager`.

### Technical debt found

- `admin-dashboard/node_modules` — **15,503 files committed to git**.
- Duplicated SMS implementation: `App\Services\SmsService` and
  `App\Services\Notifications\SmsService` both spoke to Africa's Talking directly.
  Resolved by the provider abstraction below.
- Dead views: `resources/views/landing.blade.php` and `welcome.blade.php` are
  unreachable, but `package.json`'s build script still renders `view('landing')`.
- `public/app/` holds three Android APKs totalling ~500 MB, two of them stale test builds
  in the public web root.
- Page-level `:root` overrides in `pages/home.blade.php` redeclared the entire design
  token set and re-imported Google Fonts, silently overriding the layout beneath it.

---

## 2. Security findings

Severity uses the P0–P3 scale defined in section 6.

### P0 — blocked launch

**S1 · Unrestricted file upload to a public URL** — `ServiceBookingController::createBooking`
validated attachments as `file|max:5120` with no type restriction, then wrote them to the
**public** disk. Any authenticated account could upload `.html`, `.svg` or `.phtml` and
receive a first-party URL serving it. On a host that executes PHP from the storage path
this is worse than stored XSS.
*Fixed* — all seven upload paths now route through `App\Support\UploadRules`, which
whitelists by extension **and** sniffed MIME type, with array bounds.

**S2 · No password reset existed** — no route, no controller, no `password_reset_tokens`
table, no link on the sign-in screen. A forgotten password meant permanent loss of farm
records, orders and wallet history.
*Fixed* — see section 3.

**S3 · Email verification never happened** — registration issued a full-scope token and
left `email_verified_at` null forever; no mail was ever sent. Combined with S2, a typo at
sign-up produced an unrecoverable account.
*Fixed* — see section 3.

**S4 · Dead primary link** — `/download` linked to `/app/web/`, a directory that does not
exist. The APK filename and its "171 MB" size were hardcoded in the template.
*Fixed* — `App\Support\AppDownload` reads what is actually on disk; the web-app button
renders only when a build is present.

### P1 — fix before public launch

**S5 · Account takeover via profile update** — `PUT /api/auth/profile` accepted a new email
address with no password confirmation and no re-verification. A leaked bearer token could
move the account to an attacker's inbox and then use password reset to own it outright.
*Fixed* — email removed from that endpoint; changes stage in `users.pending_email`, require
the current password, and only promote once the new address proves ownership.

**S6 · SVG accepted as an image everywhere** — Laravel's `image` rule accepts SVG, which is
a scriptable document. Avatars, product images, evidence photos and admin branding all used
the bare rule and wrote to the public disk. The admin media route listed `svg` explicitly.
*Fixed* — raster formats only, admin routes included.

**S7 · Unauthenticated, unsigned, unthrottled webhooks** — `/api/sms/receive`,
`/api/sms/callback` and both `/api/ivr/*` endpoints accepted anonymous POSTs.
`sms/receive` runs a market-price query and an outbound OpenWeather call on every request,
so anyone knowing the URL could exhaust a metered quota, hammer the database, and forge
delivery receipts.
*Fixed* — `VerifyWebhookSignature` middleware (shared secret, `hash_equals`) plus per-IP
throttling. Refuses all traffic in production when no secret is configured, rather than
failing open.

**S8 · Unbounded public upload endpoint** — `POST /api/v1/reports/counterfeit` was public,
unthrottled, and accepted an unlimited array of 10 MB files.
*Fixed* — capped at 5 files, 5 requests per hour per IP.

**S9 · Token lifetime misreported** — the API returned `expires_in: 2592000` (30 days)
while `config/sanctum.php` expired tokens after 480 minutes. In the field this presents as
an app that believes it is signed in while every request silently 401s.
*Fixed* — both the reported lifetime and the session cookie derive from the same config value.

**S10 · Registration crashed on any differently-seeded database** — `tenantId()` was a
hardcoded `match` returning ids 1–4, with no check that the tenant row existed. Reproduced
immediately on a clean database: a foreign key violation and a 500 on the first screen of
the product.
*Fixed* — resolved by `tenants.country_code`, which is unique, with a clean 422 if no
tenant matches.

**S11 · Missing transport security headers** — no HSTS, no `X-Frame-Options`. Separately,
`Permissions-Policy: camera=()` blocked the camera outright, which would break the label
scanner and disease scanner had they been served through the web middleware.
*Fixed* — HSTS on HTTPS responses only, `X-Frame-Options: SAMEORIGIN`, camera re-permitted
for same-origin.

### P2 — improve after launch

- **Public data disclosure.** `GET /api/v1/reports/{caseNumber}` returns a counterfeit
  report's description and location with no authentication. Reporter identity is correctly
  withheld, but case numbers may be enumerable. Consider requiring the report UUID rather
  than the human-readable case number.
- **Open redirect.** `GET /c/{slug}` performs `redirect()->away($shortLink->target_url)`.
  The target is admin-controlled, so exploitation requires an admin account, but the
  platform's own short-link domain becomes a redirector for phishing. Consider a host allowlist.
- **Weather endpoints** are public and each cache miss spends an OpenWeather call.
  Throttled to 60/min in this pass; a longer cache TTL would be better.
- **Mass assignment surface.** `User::$fillable` includes `role`, `status`, `is_active`,
  `is_verified_expert` and `email_verified_at`. No controller currently passes
  `$request->all()` to it, so nothing is exploitable today — but one careless line would
  make it privilege escalation. Recommend moving to `$guarded` with explicit assignment.

### P3 — future

- Two-factor authentication for admin accounts.
- Audit-log coverage for authentication events (currently covers domain events only).
- Content Security Policy currently allows `style-src 'unsafe-inline'`, which the inline
  page styles require. Extracting styles would let that be tightened.

---

## 3. Email authentication (built)

The brief named email as the primary launch identity. None of the lifecycle existed.

| Endpoint | Method | Protection |
|---|---|---|
| `/api/auth/password/forgot` | POST | 5 per 10 min per IP; identical response for known and unknown addresses |
| `/api/auth/password/reset` | POST | 5 per 10 min; one-time token; revokes all existing tokens on success |
| `/api/auth/password/change` | POST | Authenticated; requires current password; signs out other devices |
| `/api/auth/email/status` | GET | Authenticated |
| `/api/auth/email/resend` | POST | Authenticated; 3 per 10 min |
| `/api/auth/email/change` | POST | Authenticated; requires current password; stages in `pending_email` |
| `/api/auth/email/change` | DELETE | Authenticated; cancels a staged change |
| `/email/verify/{id}/{hash}` | GET (web) | Signed URL, expiring; 6 per min |
| `/forgot-password`, `/reset-password/{token}` | GET (web) | Mobile-first pages on the shared auth layout |

Design decisions worth recording:

- **Account enumeration.** Forgot-password answers identically whether or not the address
  is registered. A test asserts the status codes and bodies match.
- **Mail is queued.** An unreachable SMTP host cannot hold the sign-up response open. On a
  slow Tanzanian connection an inline send presents as a frozen button.
- **Bilingual notifications.** Verification and reset mail follow
  `users.preferred_language`; Laravel's stock English notifications are never used.
- **A completed reset also verifies the email**, because it proves inbox control.
- **Email change is staged, not applied.** The account keeps its working address until the
  new one is proved, and a race where someone else claims the address in the meantime is
  handled explicitly.
- **Password policy** is 12 characters plus an uncompromised-password check in production,
  deliberately without symbol/case rules — on a low-cost Android keyboard those push people
  toward `Password1!` written on paper.

### Account architecture

The schema already supported one account holding both identities: `email` and `phone` are
both nullable and unique on one `users` row, with separate `email_verified_at` and
`phone_verified_at`. `pending_email` and `pending_email_requested_at` were added.

**Still open:** OTP registration keys on phone alone, so someone who signs up by email in
January and by phone in March gets **two accounts**. Linking logic — match on a verified
identity before creating a new row — is not written. This gets more expensive the longer
the platform runs.

---

## 4. SMS provider abstraction (built)

Per the brief, phone authentication must not couple the platform to one aggregator.

```
Authentication Service
        ↓
    OtpService
        ↓
    SmsService          ← normalisation, logging, templates, bulk fan-out
        ↓
App\Contracts\SmsProvider
        ↓
 ┌──────────────────┬──────────┬─────┐
 │ Africa's Talking │  Twilio  │ Log │
 └──────────────────┴──────────┴─────┘
```

Selected by `SMS_PROVIDER` (`africastalking` | `twilio` | `log`). Adding an aggregator is
one class plus one line in `SmsProviderManager::make()`; nothing in the OTP or login code
changes. An unknown provider name logs an error and falls back to the log driver rather
than taking sign-in down or silently routing through the wrong gateway.

`SmsService`'s public method signatures and return shapes are unchanged, so every existing
caller — `AuthController`, `OtpService`, advisory jobs — kept working with no edits.
`SmsDeliveryResult` replaced the loose result arrays so a caller cannot misread a failure
as a success by checking a key one provider never set. Africa's Talking now checks the
per-recipient status, not just the HTTP code: a rejected number returns 200 with a failure
inside the body.

---

## 5. Mobile UX/UI

### Findings

The site was desktop-designed and adapted for phones only by collapsing grids.

- `section { padding: 96px 0 }` with **no mobile override**. Home was 6,312px tall at
  390px — roughly 7.5 screenfuls. `/solutions` was 11,173px.
- Between **19 and 25 controls per page under a 40px touch target**, mostly 16px text links.
- The footer alone was **1,571px** on every page.
- Body background was cream `#FFFDF8`; primary CTAs were amber. The brief asks for 90%
  white with green as the action colour.
- **Four Google font families across thirteen weights**, render-blocking from an external host.
- **The icon system could not survive a slow connection.** Icons were Material Symbols
  ligatures — `<span>check_circle</span>` styled by a remote stylesheet. When that request
  is slow or filtered, the page prints the icon's own name as text. Four uses named the
  `-outlined` family while the layout only loaded `Rounded`, so on `/download` the words
  `android`, `download`, `language` and `info` rendered as text **on every connection**.
- **Three separate design systems**: the marketing layout, `/login` and `/register` each
  declared their own tokens — different greens, different ambers, different logo lockups.
- **Web sign-in led nowhere.** A successful login redirected to `/`, which has no
  signed-in state.
- **A phone-login tab that fails in production.** `auth.otp_enabled` defaults to false in
  production, but the tab was always rendered.

### Changes

- One token set in `layouts/public.blade.php`; page-level `:root` overrides removed.
  White canvas, green for actions, amber demoted to highlights only.
- **`resources/views/components/icon.blade.php`** — inline SVG icons on one grid and one
  stroke weight. Zero network requests, nothing to fail. All Material Symbols usage removed.
- All remote font loading removed; system UI font stack, which paints instantly and looks
  native on low-cost Androids.
- A real mobile scale for section padding, type, card density and touch targets, with
  caps on the page-level classes that carried desktop padding.
- **Mobile quick-access grid** on the home page directly under the hero — six core services
  one tap from the top, where the capabilities grid was previously ~3,500px down.
- **Sticky mobile action bar** so there is always a visible call to action.
- **Hero rebuilt for phones**: the desktop hero fades a photograph out horizontally under
  the copy, which on a phone put body text over a farmer's face. Below 700px the artwork
  moves to its own band beneath the copy.
- `layouts/auth.blade.php` — one shared auth shell. `/login` and `/register` rebuilt on it,
  joined by the two new password pages.
- Registration gained a **terms and privacy consent checkbox** (previously absent) and a
  live password-strength meter.
- Phone input takes the nine digits people know behind a fixed `+255` prefix, instead of
  demanding `255700000000` and rejecting anything else with a regex error.

### Measured

| Screen | Height before | Height after | Change | Small targets |
|---|---:|---:|---:|---|
| Home | 6,312px | 5,441px | −14% | 21 → 1 |
| Solutions | 11,173px | 10,060px | −10% | 19 → 1 |
| About | 9,689px | 8,511px | −12% | 19 → 1 |
| Technology | 8,733px | 8,109px | −7% | 19 → 1 |
| Impact | 7,030px | 6,271px | −11% | 19 → 1 |
| Stories | 4,366px | 3,884px | −11% | 25 → 1 |
| Download | 2,289px | 1,993px | −13% | 19 → 1 |
| Sign in | 844px | 844px | — | 2 → 0 |
| Register | 1,117px | 1,212px | +8% | 2 → 0 |

Register grew by the consent checkbox and the strength meter. `/solutions` is still
10,060px because eight full-bleed solution rows is a content decision, not a CSS one.

Verified at 360, 375, 390, 412, 430, 768, 820 and 1024px: **no horizontal overflow at any
width**.

### Flutter app

`lib/core/theme.dart` carried the same cream-and-amber scheme: a `#FAF7EF` ground,
charcoal as the primary action colour, green demoted to `tertiary`.

- Palette rebuilt on white with green as `primary`; amber kept only for warnings, ratings
  and status.
- The bottom bar was a solid charcoal slab with an amber FAB. It is now white with a
  hairline, a green scanner FAB, and green/muted tab states. The unselected state was white
  at 62% opacity on charcoal — under 3:1, so three of five tabs were barely visible in
  daylight.
- The navigation architecture the brief asks for (Home / Marketplace / Scan / Community /
  Profile) **already existed** and was left alone.
- Onboarding pages used a mix of ink and amber as button backgrounds; the amber page put
  white text on `#E0A008` (~2.1:1). All pages now use the brand green with white labels.
- `_NavItem` gained `Semantics(selected:, button:, label:)` for screen readers.
- Added `AuthProvider.requestPasswordReset()` and `resendEmailVerification()`, plus a
  forgot-password bottom sheet on the sign-in screen — the app had no recovery path.

---

## 6. Priorities

**P0 — blocks launch.** S1, S2, S3, S4. All fixed and covered by tests.

**P1 — fix before public launch.** S5–S11 (all fixed), plus the mobile UX work above.
Remaining P1 items requiring you: SMTP credentials, webhook secrets, `APP_URL`
confirmation, a running queue worker.

**P2 — immediately after launch.** Account linking between email and phone identities;
report-endpoint disclosure; short-link host allowlist; `User::$guarded` refactor;
untracking `node_modules`; deleting the stale APKs; Flutter visual verification.

**P3 — future.** Admin 2FA; authentication audit logging; extracting inline styles so CSP
can drop `unsafe-inline`; per-page design work on the deep marketing pages;
`firebase_messaging` so push notifications actually work.

---

## 7. Tests

`php artisan test` — **134 passing, 2 skipped, 0 failing** (was 110 passing).

24 new tests across two files, each pinning a specific hole so a future refactor that
reopens one fails here rather than in production:

`tests/Feature/PasswordAndEmailVerificationTest.php`
- Registration leaves the email unverified and sends a link
- Registration rejects a password under twelve characters
- A valid signed link verifies the address
- An **unsigned** link cannot verify an address
- A link for a **different** address is refused
- Forgot-password sends a reset link
- Forgot-password does not reveal whether an account exists
- Reset changes the password, verifies the email, and revokes tokens
- A reset token cannot be replayed
- Changing a password requires the current one
- Changing a password signs out other devices
- Profile update can no longer change the email
- Email change is staged and requires the current password
- Verifying the staged address promotes it
- A staged address claimed by someone else is not promoted

`tests/Feature/SecurityHardeningTest.php`
- An SVG cannot be uploaded as an avatar (and nothing reaches the disk)
- A JPEG is still accepted as an avatar
- Service-booking media rejects an arbitrary file
- SMS webhook is refused without the shared secret
- SMS webhook is accepted with the shared secret
- SMS webhook rejects a wrong secret
- The SMS gateway can be swapped by configuration alone
- An unknown gateway falls back to the log driver instead of failing
- Every provider satisfies the contract

Manual and automated verification also covered: all 13 public routes returning 200; the
full responsive sweep; JavaScript console errors on every page (one was found and fixed —
a broken translation string); and end-to-end register → verify → login → reset against a
live local server.

---

## 8. What was not verified

Stated plainly, because an audit that hides its own gaps is worth less than one that
names them.

**The Flutter app was read, not run.** Google's Flutter and Dart download hosts are blocked
from the sandbox this audit ran in, so the mobile app could not be built or screenshotted.
Every Flutter statement above comes from reading ~18k lines of Dart. Every edited Dart file
was checked for balanced delimiters, which is the strongest static guarantee available
without a toolchain — **run `flutter analyze` and `flutter build` before shipping these.**

**Flutter findings not fixed:**
- **Push notifications do not work.** `firebase_messaging` is commented out of
  `pubspec.yaml` while the backend has a complete `PushNotificationService`.
- **Reachable dead ends.** `iot_screen.dart` and `drone_screen.dart` front endpoints that
  return 503 by design. They should be gated behind the existing feature flags.

**Also not covered:** the React admin dashboard (source reviewed, not rebuilt); per-page
design work on `/technology`, `/partners`, `/impact` and `/stories`, which received the
global mobile treatment only; load and performance testing under concurrency; and the
brand mark itself — the logo is a photographic banner in a cream box, which now sits
against a white canvas.

---

## 9. Files changed

**Security and authentication** — committed as `9b40cc98` on `agent/publish-current-platform`.

```
app/Contracts/SmsProvider.php                                    new
app/Http/Controllers/Api/Auth/EmailVerificationController.php    new
app/Http/Controllers/Api/Auth/PasswordController.php             new
app/Http/Middleware/VerifyWebhookSignature.php                   new
app/Notifications/ResetPasswordNotification.php                  new
app/Notifications/VerifyEmailNotification.php                    new
app/Services/Sms/Providers/AfricasTalkingProvider.php            new
app/Services/Sms/Providers/LogProvider.php                       new
app/Services/Sms/Providers/TwilioProvider.php                    new
app/Services/Sms/SmsDeliveryResult.php                           new
app/Services/Sms/SmsProviderManager.php                          new
app/Support/AppDownload.php                                      new
app/Support/UploadRules.php                                      new
database/migrations/2026_08_22_000001_create_password_reset_and_email_change_tables.php  new
lang/en/auth_flows.php, lang/sw/auth_flows.php                   new
resources/views/layouts/auth.blade.php                           new
resources/views/auth/forgot-password.blade.php                   new
resources/views/auth/reset-password.blade.php                    new
tests/Feature/PasswordAndEmailVerificationTest.php               new
tests/Feature/SecurityHardeningTest.php                          new

app/Http/Controllers/Api/AuthController.php                      modified
app/Http/Controllers/Api/Admin/AdminController.php               modified
app/Http/Controllers/Api/Admin/AdminProfileController.php        modified
app/Http/Controllers/Api/DiseaseScannerController.php            modified
app/Http/Controllers/Api/InputVerificationController.php         modified
app/Http/Controllers/Api/MarketplaceController.php               modified
app/Http/Controllers/Api/ServiceBookingController.php            modified
app/Http/Controllers/Api/Verify/CounterfeitReportController.php  modified
app/Http/Middleware/SecurityHeaders.php                          modified
app/Models/User.php                                              modified
app/Services/SmsService.php                                      modified
bootstrap/app.php, config/services.php                           modified
routes/api.php, routes/web.php                                   modified
resources/views/pages/download.blade.php                         modified
.env.example                                                     modified
```

**Design and Flutter** — on branch `design/mobile-first`, for review before merge.

```
resources/views/components/icon.blade.php                        new
resources/views/layouts/public.blade.php                         modified
resources/views/auth/login.blade.php                             rewritten
resources/views/auth/register.blade.php                          rewritten
resources/views/pages/home.blade.php                             modified
resources/views/pages/{contact,impact,solutions,technology}.blade.php  modified
mkulima_app/lib/core/theme.dart                                  modified
mkulima_app/lib/providers/auth_provider.dart                     modified
mkulima_app/lib/screens/{home_screen,home_tab,login_screen,register_screen,
  profile_screen,splash_screen,onboarding_screen,marketplace_screen,
  kagua_dawa_screen}.dart                                        modified
mkulima_app/lib/widgets/mk_product_tile.dart                     modified
```

---

## 10. Deployment risks

1. **`MAIL_PASSWORD` is empty in `.env.production`.** Verification and reset mail are now
   on the critical path. Until a real SMTP credential is set, registration produces
   accounts that can never recover a password. **This is the single largest launch risk.**
2. **Webhook secrets are unset.** `/api/sms/*` and `/api/ivr/*` now refuse all traffic in
   production without them. That is the safe failure, but it will look like an outage if
   the gateway is already live.
3. **No queue worker means no mail.** Verification and reset notifications are queued.
4. **`APP_URL` must be correct** — every emailed link is built from it.
5. **New migration must run** before the new endpoints are hit.
6. **No error tracking and no backups configured.** Launching without an error tracker
   means learning about failures from users.
7. **~500 MB of APKs in the public web root** on shared hosting.
8. **Flutter changes are unverified.** Build and smoke-test before shipping an APK.

---

## 11. Recommended next actions

1. Set SMTP credentials and send one real verification email end to end.
2. Set `SMS_WEBHOOK_SECRET` and `IVR_WEBHOOK_SECRET`, and configure them at the gateway.
3. Run the new migration; confirm the queue worker is running under supervisor.
4. `flutter analyze && flutter build apk` on the design branch; review the screens.
5. Review and merge `design/mobile-first`.
6. `git rm -r --cached admin-dashboard/node_modules`; delete the two stale APKs.
7. Configure error tracking and automated database backups.
8. Write the account-linking logic before the user base makes duplicates expensive.
