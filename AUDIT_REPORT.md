# Mkulima Forum — Audit & Remediation Report

**Date:** 2026-07-12 · **Scope:** Full-stack audit (Laravel API + Flutter app + React admin) and remediation of critical findings.
**Prior work:** See `REDESIGN.md` for Phases 0–3 (2026-07-06/07). This report covers the audit pass that followed.

---

## 1. Executive Summary

**Current state.** Mkulima Forum is a three-surface monorepo: Laravel 13 API (Sanctum auth, Spatie RBAC, escrow/wallet with tested money paths), a Flutter farmer app (29 screens, offline-first drift DB, go_router), and a React 18 admin dashboard. The core commerce loop (browse → order → pay → escrow → release) and the forum (votes, regions, expert answers) were already solid and test-covered.

**Major problems found in this audit** (all fixed):

1. **Fake weather in production.** Duplicate route registrations meant `/api/weather/current` was served by a demo controller generating temperatures with `rand()`, while `/weather/forecast` and `/weather/report` called methods that didn't exist (500 on every request). The honest `WeatherService` existed but was unused by the controller. **Additionally**, a NOT NULL constraint on `weather_cache.forecast_data` made every current-weather cache write throw, which was silently swallowed — so even with a valid OpenWeather key the API always answered "unavailable".
2. **A second, fake escrow API.** `/api/escrow/*` returned hardcoded escrows from cache with **no ownership check on release** — and the Flutter escrow screen was wired to it, showing farmers fictitious contracts. The real, tested API (`/api/payments/escrows`) was unused by the app. The real API itself had a latent 500 (`ledger` vs `ledgerEntries` relation) and an unbracketed `orWhere` in `myEscrows`.
3. **Fabricated data across demo endpoints:** IoT sensors (invented readings), drone bookings (hardcoded "DRN-123456"), yield photo "analysis" (`rand()` plant counts and confidence), SMS auto-replies (invented crop prices and weather), notifications (mark-as-read did nothing).
4. **Security gaps:** any logged-in user could send SMS to any phone number; no throttling on login endpoints; three route files used closures (breaks `route:cache` in production).
5. **Missing core features:** market prices module did not exist at all; no way to report misleading content and no moderation queue.
6. **UI confusion:** the AI assistant had three names (Mkulima Bot / Mtaalamu wa AI / AI Chat); the bottom navigation had no Forum tab.

**Production-readiness status:** Backend is staging-ready: 56 tests / 226 assertions pass, `migrate:fresh --seed` works, `route:cache` works, no fabricated data remains on any endpoint. Flutter analyzes clean; admin dashboard builds. **Remaining blockers for production** are operational, not code: real M-Pesa/Tigo credentials + live sandbox testing, an SMS gateway (OTP delivery is still logged, not sent), deployment of the API host, and populating market prices with real data feeds.

## 2. Fixes & Features Delivered (this session)

| # | Area | What was done |
|---|---|---|
| 1 | Weather | `WeatherController` rewritten onto `WeatherService`: real OpenWeather data, stale-cache fallback flagged `is_stale`, honest `available:false` state; duplicate/broken routes removed; cache-write bug fixed (nullable columns + cache failure can no longer break serving); 6 new tests |
| 2 | Escrow | Fake `/escrow/*` API deleted; Flutter screen rebuilt on `/payments/escrows` with real confirm-delivery and refund-request actions; `ledgerEntries` 500 fixed; `myEscrows` query bracketed + `direction` field added |
| 3 | Drone | Bookings now persist in `drone_bookings` (real leads, owner-scoped listing); catalog labelled as indicative platform pricing; feature-flag gated |
| 4 | IoT | All endpoints answer honestly (503 behind disabled `iot_sensors` flag; no invented sensors); flag default now `false` |
| 5 | Yield | Estimates persist in `yield_estimates` with real history; openly labelled `reference_table` with a Swahili disclaimer (no fake "confidence"); photo analysis returns honest 501; Flutter screen updated (was reading a removed field → would crash) |
| 6 | SMS | `BEI <zao>` replies from real `market_prices` rows (or an honest apology); `HALI <mkoa>` replies from `WeatherService`; `/sms/send` now admin-only |
| 7 | Notifications | Real read-state via `notification_reads` table; unread counts are genuine; derived from real order events |
| 8 | Market prices | **New module**: `market_prices` table, public API with commodity/region/market/date filters, `latest`-per-market mode, trend (up/down/stable), `is_stale` flag after 14 days, admin CRUD, admin dashboard page, Flutter screen (Huduma grid + `/market-prices` route) |
| 9 | Moderation | **New module**: polymorphic `reports` (threads, replies, products, users), duplicate-report guard, throttled; admin queue with resolve (hide content / disable listing / suspend user) and dismiss; hidden content vanishes from public feeds; admin dashboard page |
| 10 | Security | Throttle on login (10/min), OTP request (5/min), OTP verify (10/min), reports (10/hr); SMS send admin-gated; KYC/notifications/seller closures converted to controllers (route caching verified); KYC re-submission guarded while pending/verified |
| 11 | Flutter UX | Single name **Mkulima AI** everywhere; bottom nav now Soko / **Jukwaa** / Huduma / **Mkulima AI** / Wasifu; dead code removed (`GuestFeaturesScreen`, `LoginScreenPlaceholder`, always-on fake unread dot); weather screen honest states (unavailable + stale banner) with location picker; `withOpacity` deprecations fixed |

## 3. Feature Status Matrix

| Feature | Before audit | Problem | Action taken | Status now | Remaining work |
|---|---|---|---|---|---|
| Auth (OTP + email) | Working | No login throttle | Throttles added | **Working** | Wire real SMS gateway for OTP delivery |
| RBAC / roles | Working | Spatie perms seeded but not per-route | — (documented) | Working | Adopt `permission:` middleware per route |
| Marketplace products/orders | Working | — | — | Working | Reviews, saved products, WhatsApp contact |
| Escrow + payments | Partially working | Fake parallel API; 500 on status; app on fake API | Fixed + rewired | **Working (sandbox)** | Live M-Pesa/Tigo credentials + E2E money test |
| Wallet | Working (tested) | — | — | Working | Production deposit/withdraw via PSP only |
| Forum (votes, regions, experts) | Working | No reporting/moderation | Reports module added | **Improved** | Nested replies, mentions, polls, saved posts |
| Weather | **Broken/fake** | rand() data; 500s; cache bug | Rewritten on real service | **Working** | Verify OpenWeather key quota in prod |
| Market prices | **Missing** | — | Full module built | **Working** | Real data feed (Wizara ya Kilimo/market agents) |
| Mkulima AI (bot) | Working | Triple naming | Unified naming | **Improved** | Image input, link-outs to forum/experts |
| Disease scanner | Working (Gemini) | — | — | Working | Accuracy validation with agronomists |
| Services engine (vet/agronomist/soil) | Working (tested) | — | — | Working | Availability calendar UI |
| Logistics + warehouse | Working (tested) | — | — | Working | Flutter screens |
| Drone | **Fake** | Cache/demo bookings | DB-backed bookings | **Working (leads)** | Operator dispatch workflow |
| IoT | **Fake** | Invented sensors | Honest 503 + flag off | **Honest placeholder** | Real device integration (Phase 4+) |
| Yield estimate | **Fake-ish** | rand() confidence, fake photo AI | Reference-table + disclaimer + history | **Working (labelled)** | Real agronomic model per region |
| Notifications | **Fake read-state** | No persistence | `notification_reads` | **Working** | Push (FCM) sender, event-driven records |
| SMS auto-reply | **Fake** | Invented prices/weather | Real data sources | **Working** | Africa's Talking gateway integration |
| Moderation/reporting | **Missing** | — | Full module built | **Working** | Auto-flag heuristics, moderator role |
| Admin dashboard | Working | No moderation/prices pages | 2 pages added | **Improved** | Code-splitting (784 kB bundle) |
| KYC | Partially working | No document upload; resubmit races | Resubmission guard | Partially working | Document/photo upload + storage |
| Global search | Missing | Scout/Meilisearch installed, unused | — | Missing | Wire Scout to products/threads |
| Localization (EN toggle) | Missing | Swahili-only strings | — | Missing | ARB-based l10n |

## 4. Change Log

**New files**
- `app/Http/Controllers/Api/MarketPriceController.php`, `app/Models/MarketPrice.php`
- `app/Http/Controllers/Api/ReportController.php`, `app/Models/Report.php`
- `app/Http/Controllers/Api/NotificationController.php`, `KycController.php`, `SellerController.php`
- `app/Models/DroneBooking.php`, `app/Models/YieldEstimate.php`
- Migrations: `2026_07_12_000001` (drone_bookings + yield_estimates), `..._000002` (market_prices), `..._000003` (notification_reads), `..._000004` (reports)
- Tests: `WeatherApiTest` (6), `MarketPriceTest` (4), `ReportModerationTest` (5), `HonestEndpointsTest` (6)
- Flutter: `lib/screens/market_prices_screen.dart`
- Admin: `src/pages/Moderation.jsx`, `src/pages/MarketPrices.jsx`

**Modified files**
- `routes/api.php` — removed duplicate weather + admin-features registrations and fake `/escrow/*` routes; added reports, market-prices; throttles on auth; admin gate on SMS send
- `routes/api_kyc.php`, `api_notifications.php`, `api_seller.php` — closures → controllers (route caching now works)
- `app/Http/Controllers/Api/WeatherController.php` — rewritten (real data only)
- `app/Http/Controllers/Api/DroneController.php`, `IoTController.php`, `YieldController.php` — de-faked
- `app/Http/Controllers/Api/SmsController.php` — real price/weather replies
- `app/Http/Controllers/Api/Payments/PaymentController.php` — relation fix, query fix, `direction`
- `app/Services/WeatherService.php` — cache-write isolation
- `database/migrations/..._create_weather_cache_table.php` — nullable json columns
- `database/seeders/FeatureFlagSeeder.php` — IoT off, honest descriptions
- Flutter: `home_screen.dart` (new nav, dead code removed), `escrow_screen.dart` (real API + actions), `weather_screen.dart` (new contract + honest states), `yield_screen.dart` (new contract), `features_screen.dart`, `strings.dart`, `mkulima_bot_screen.dart`, `profile_screen.dart` (Mkulima AI naming), `api_service.dart` (weather report), 6 screens (`withOpacity` → `withValues`)
- Admin: `App.jsx`, `Layout.jsx` (new pages/nav)

**Removed files**
- `app/Http/Controllers/Api/EscrowController.php` (fake, unauthorised)

**API changes**
- Removed: `POST/GET /api/escrow/*` (use `/api/payments/escrows*`)
- Changed: `GET /api/weather/current|forecast|advisory|report` — new honest response shapes (`available`, `is_stale`)
- Added: `GET /api/market-prices`, `GET /api/market-prices/filters`, `POST/PUT/DELETE /api/admin/market-prices*`, `POST /api/reports`, `GET/POST /api/admin/reports*`
- Yield estimate response: `confidence_score`, `factors`, `recommendations` removed; `method`, `disclaimer`, `id` added

**Environment variables** — unchanged (`OPENWEATHER_API_KEY`, `GEMINI_API_KEY`/`GEMINI_MODEL`, M-Pesa/Tigo vars as before).

## 5. Setup Documentation

```bash
# Backend (PHP 8.3+, Composer)
cp .env.example .env            # or use existing .env
composer install
php artisan key:generate
touch database/database.sqlite  # dev; use PostgreSQL in production (DB_CONNECTION)
php artisan migrate --seed      # 38 migrations + roles, admin, categories, demo products, feature flags
php artisan serve               # http://localhost:8000

# Tests
php artisan test                # 56 tests / 226 assertions

# Flutter app (Flutter ≥ 3.5)
cd mkulima_app
flutter pub get
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api     # Android emulator
flutter build apk --dart-define=API_URL=https://<prod-host>/api

# Admin dashboard (Node 18+)
cd admin-dashboard
npm install
npm run dev                     # VITE_API_URL in .env / .env.production
npm run build
```

Storage: `php artisan storage:link` for avatar/scan uploads (public disk). Queue: `php artisan queue:work` (database driver). Websockets: `php artisan reverb:start` if realtime is enabled.

## 6. Production Checklist

- [x] Authentication: OTP + email login, throttled; `dev_code` gated to local/testing
- [x] Permissions: role middleware on admin/seller/SMS routes; ownership checks on products, orders, escrows, bookings, conversations
- [x] No fabricated data on any endpoint (weather, prices, escrow, IoT, drone, yield, notifications, SMS)
- [x] Migrations: `migrate:fresh --seed` clean (38 migrations)
- [x] Route caching (`route:cache`) verified
- [x] Error handling: honest 5xx/`available:false` states, retry UI in app
- [x] Tests: 56 backend, flutter analyze clean, admin build clean
- [ ] **Credentials required:** `OPENWEATHER_API_KEY` (verify quota), `GEMINI_API_KEY`, M-Pesa (confirm TZ Vodacom OpenAPI vs Daraja/KE), Tigo Pesa, SMS gateway (Africa's Talking) for OTP + auto-replies
- [ ] OTP SMS delivery — currently logged, not sent (`AuthController::requestOtp` TODO)
- [ ] Market price data feed — module ready; needs real price recorders/imports
- [ ] Deployment: HTTPS host, `APP_DEBUG=false`, PostgreSQL, backups, monitoring (e.g. Sentry), log rotation
- [ ] Push notifications (FCM server key) + notification fan-out
- [ ] File storage in production (S3/compatible) for avatars/scans/KYC docs
- [ ] Rate-limit store on Redis for multi-server deployments
