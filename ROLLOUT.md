# Mkulima Forum — Rollout Runbook

**Updated:** 2026-07-13 · **State:** Code staging-ready (72 backend tests passing, flutter analyze clean, admin build clean, smoke script 20/20 locally).

This is the operational checklist to take Mkulima Forum from this repo to production. Work top-to-bottom; items marked ⛔ block launch, items marked ⚠️ can follow within the first weeks.

---

## 1. Credentials & External Services (⛔ gather these first)

| Service | Env vars | Status | Notes |
|---|---|---|---|
| SMS gateway (Africa's Talking) | `AFRICASTALKING_*` | ⛔ **missing** | **Launch blocker:** OTP codes are currently only logged, not sent — no farmer can register without this. Wire delivery in `AuthController::requestOtp` (TODO marked) + `SmsService`. |
| OpenWeather | `OPENWEATHER_API_KEY` | ✅ set | Verify plan quota fits traffic (30-min cache per location keeps calls low). |
| Gemini (scanner, Mkulima AI, Kagua Dawa label check) | `GEMINI_API_KEY`, `GEMINI_MODEL` | ✅ set | Confirm billing + rate limits on the key used in prod. |
| M-Pesa | `MPESA_*` | ⚠️ sandbox | Confirm Tanzania (Vodacom OpenAPI) vs Kenya (Daraja) — service was written against Daraja-style flows. Run one real end-to-end money test before enabling escrow publicly. |
| Tigo Pesa | `TIGOPESA_*` | ⚠️ sandbox | Same as above. |
| FCM push | Firebase project | ⚠️ missing | `push_tokens` table exists; no sender wired yet. |

## 2. Server & Environment (⛔)

- [ ] Provision host with PHP 8.3+, PostgreSQL, Redis (cache/queue/rate-limits), Nginx + HTTPS (Let's Encrypt), supervisor.
- [ ] `.env` production values: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<host>`, `DB_CONNECTION=pgsql`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`. **Never** reuse dev `APP_KEY`.
- [ ] `LOG_LEVEL=warning` + log rotation. Note: failed weather calls log the upstream URL **including the API key** (cURL error text) — either scrub in a log formatter or accept keys-in-logs risk with restricted log access.
- [ ] Error monitoring (Sentry/Flare) + uptime check on `/api/health`.
- [ ] Database backups (daily dump + WAL/point-in-time if possible) and a tested restore.

## 3. Deploy Runbook (backend + admin + landing)

```bash
# 1. Ship code
git clone <repo> && cd mkulima-forum
composer install --no-dev --optimize-autoloader

# 2. Configure
cp .env.production .env   # after filling real secrets — file is gitignored
php artisan key:generate  # first deploy only
php artisan storage:link

# 3. Database
php artisan migrate --force           # 40 migrations
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=TenantSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=FeatureFlagSeeder --force
# ⚠️ Do NOT run DatabaseSeeder in production — it seeds demo products/threads.

# 4. Optimize
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 5. Workers
php artisan queue:work redis --daemon   # under supervisor
# php artisan reverb:start              # only if realtime features enabled

# 6. Admin dashboard (serve dist/ under /admin)
cd admin-dashboard && echo "VITE_API_URL=https://<host>/api" > .env.production \
  && npm ci && npm run build            # deploy dist/

# 7. Smoke test (read-only, run every deploy)
./scripts/smoke.sh https://<host>       # must print "20 passed, 0 failed"
```

**Immediately after first deploy:**
- [ ] Log in as the seeded admin (`admin@mkulima.forum`) and **change the password** (`admin123` is public in this repo).
- [ ] Register a real test farmer via OTP end-to-end (proves SMS gateway).
- [ ] Confirm `dev_code` is absent from the OTP response (it is gated to local/testing — verify anyway).

## 4. Data Population (⛔ before marketing, else screens are honest-but-empty)

- [ ] **Market prices:** appoint price recorders or import from Wizara ya Kilimo; enter via Admin → Market Prices. Entries older than 14 days are flagged "stale" automatically.
- [ ] **Kagua Dawa registry:** load the official TPRI (pesticides) and TFRA (fertilizers) lists via Admin → Input Safety → Registry. Until populated, the API honestly answers "orodha bado inajazwa — hakiki na TPRI".
- [ ] **Forum categories:** review the seeded categories; add regional/crop communities as needed.
- [ ] **Verified experts:** approve initial agronomists (sets `is_verified_expert` badge) so expert answers exist from day one.
- [ ] Remove/replace demo marketplace products if `CategoryProductSeeder` was ever run on this DB.

## 5. Mobile App Release

```bash
cd mkulima_app
flutter build apk --release --dart-define=API_URL=https://<host>/api
cp build/app/outputs/flutter-apk/app-release.apk ../public/app/mkulima-forum.apk
```
- [ ] The landing page "Pakua APK" button points at `/app/mkulima-forum.apk` — the copy above makes it live.
- [ ] Android signing keys generated and stored securely (`android/key.properties`).
- [ ] Play Store listing (screenshots of Home hero, Kagua Dawa, Soko) — sideload APK works meanwhile.
- [ ] Smoke the release APK on a low-end device: register → scan a plant → search "mahindi" → check Bei za Masoko → Kagua Dawa lookup.

## 6. Post-launch Monitoring (first 2 weeks)

- [ ] Watch Gemini spend (scanner + bot + label checks are the main drivers; both are throttled per-user).
- [ ] Review moderation queues daily: Admin → Moderation (content) and Admin → Input Safety (counterfeit alerts).
- [ ] Check `laravel.log` for `Weather API error` / `Label extraction failed` spikes.
- [ ] Confirm rate limits hold (`throttle` uses Redis in prod — shared across servers).

## 7. Rollback

- Code: deploy previous git tag, `php artisan config:cache && route:cache`.
- Migrations in this release are **additive only** (new tables: drone_bookings, yield_estimates, market_prices, notification_reads, reports, registered_inputs, counterfeit_alerts) — safe to roll code back without rolling back the DB.
- Keep the previous APK; the API remains backward-compatible with it except the removed fake `/escrow/*` endpoints (old APKs show an honest error state on the escrow screen).

## 8. Known Gaps (accepted for v1)

- OTP/SMS delivery requires §1 gateway (blocker); email login works for admins meanwhile.
- English UI toggle not yet implemented (Swahili-first; strings centralized ready for ARB l10n).
- Push notifications not wired (in-app notifications work).
- Payments live-tested only in sandbox.
- IoT feature-flagged off; yield photo analysis returns honest 501.
