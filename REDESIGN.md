# MkulimaForum — Platform Audit & Redesign Document

**Date:** 2026-07-06 · **Author:** Codebase audit (Claude) · **Status:** Phases 0–2 executed (see §7); Phase 3 scaffolded

This document reconciles the actual codebase against the target architecture in `MkulimaForum_ Modern Agri Hub/` (requirements v1.0 + 16k-word SAD), records what works, what is broken or stubbed, and defines the redesign plan.

---

## 1. Current State — What Exists

The repo contains four surfaces:

| Surface | Stack | State |
|---|---|---|
| Backend API | Laravel 11, Sanctum, Spatie permissions, PostgreSQL, Reverb | Most complete. ~546 route lines across `api.php`, `api_kyc.php`, `api_notifications.php`, `api_seller.php`. Controllers exist for auth/OTP, marketplace, forum, disease scanner, wallet/escrow, payments (M-Pesa, Tigo Pesa), weather, SMS/IVR, IoT, drone, yield, plus an Admin API suite (catalog, vendors, HR, POS, financial reports, feature flags). 26 migrations align with the 20 models. |
| Flutter farmer app | Flutter ≥3.5, Provider + drift (offline DB), dio | 27 screens (~8,700 LOC) + POS screen. Auth, cart, connectivity providers. Works as an MVP shell. |
| Admin dashboard | React 18 + Vite + Tailwind, react-query v3 | 15 pages (Dashboard, Users, Vendors, Orders, Escrows, KYC, Catalog, POS, HR, Financial Reports, Feature Flags, Analytics, Settings, Profiles, Login). Single `Layout` component. |
| Architecture docs | `MkulimaForum_ Modern Agri Hub/` | Requirements (EF-001…EF-008), structure design, full SAD draft, research corpus. Target: 4 countries, 5 pillars, 6 service categories, RAG/pgvector, voice-first. |

## 2. Audit Findings — What's Broken, Stubbed, or Inconsistent

### 2.1 Critical (blocks "it works")

1. **All external integrations are unconfigured.** `.env` has empty `GEMINI_API_KEY`, `MPESA_CONSUMER_KEY/SECRET/PASSKEY`, `TIGOPESA_API_KEY/SECRET`. Disease scanner falls through TFLite (hard stub returning `null`) → Gemini (no key → `null`). Payments run in sandbox mode with no credentials. Nothing AI- or payment-related can succeed end-to-end today.
2. **API base URL mismatch across clients.** Backend `APP_URL=https://mkulima.hudumapro.com`; Flutter hardcodes `https://console.hudumapro.com/api` in `main.dart`; admin dashboard production env points at `http://76.13.56.180:8000/api` (plain HTTP to a raw IP — insecure and likely dead). Three surfaces, three different backends.
3. **Weather fallback fabricates data.** `WeatherService` returns `rand()`-generated temperatures/humidity when the upstream call fails. For a farming app this is actively harmful — farmers may act on invented forecasts. Must be replaced with cached-last-known + explicit "stale/unavailable" state.
4. **Disease scanner TFLite path is a placeholder** (`runTfliteInference` returns `null`), and the Flutter side has `tflite_flutter`, `google_generative_ai`, `flutter_mpesa`, and push-notification deps **commented out** in `pubspec.yaml`. The on-device hybrid described in EF-002 does not exist.

### 2.2 High (architecture debt)

5. **Requirements coverage gap.** Of EF-001…EF-008, implemented: Marketplace (EF-001, basic), Disease Scanner (EF-002, cloud-only stub), Forum (EF-003, basic threads/replies — no upvoting, expert badges, or regional sub-forums). Missing entirely: Logistics/Transport (EF-005), Warehouse (EF-006), Veterinary (EF-007), Soil Testing (EF-008). Agronomist (EF-004) has a controller but no booking/calendar/consultation model. Roughly 40% of the required feature surface exists.
6. **Flutter navigation is ad-hoc.** `go_router` is declared but unused; screens push each other with `Navigator.push(MaterialPageRoute(...))`. No route table, no deep links, no auth guards. `flutter_bloc` and `retrofit` are also declared but unused — the app actually runs on Provider + hand-rolled dio calls. `lib/widgets/` is empty; there is no shared component library, so 27 screens duplicate UI.
7. **Multi-tenancy is nominal.** A `Tenant` model/migration exists but country-scoping (TZ/KE/UG/RW per the SAD) isn't enforced through queries, routes, or roles.
8. **`routes/flutter/` is an empty directory** — dead scaffolding.
9. **Admin dashboard uses react-query v3** (EOL; v5 is `@tanstack/react-query`) and has no auth-refresh/error-boundary story.

### 2.3 Medium

10. Backend untestable in this environment (no PHP/composer here); `tests/` content is effectively the Laravel default — no feature tests over payments, escrow, or scanner.
11. Root `README.md` is stock Laravel — no setup docs for a 3-surface monorepo.
12. Escrow/Wallet services exist but ledger integrity (double-entry, idempotency on payment callbacks) needs verification before real money flows.
13. No CI beyond a single `.github` workflow dir; no lint/test gates for Flutter or React.

## 3. Redesign Decisions

**Keep the stack.** Laravel 11 + Flutter + React is sound and matches your other projects (SokoMoto, TAHA). The SAD's Laravel 13/FrankenPHP/pgvector ambitions are Phase-3 concerns; do not replatform now.

**One API host, one contract.** Standardize on `https://mkulima.hudumapro.com/api`. Flutter reads base URL from `--dart-define` (dev/staging/prod flavors); admin uses `VITE_API_URL` with HTTPS only. Publish a single OpenAPI spec from the Laravel routes and treat it as the contract for both clients.

**Flutter app restructure (the actual "redesign"):**
- Adopt `go_router` with a typed route table, auth redirect guard, and bottom-nav shell (Home / Soko / Forum / Scanner / Akaunti — Swahili-first labels).
- Extract a design system into `lib/widgets/`: `MkColors` (keep seed `#2E7D32`), typography scale, `MkCard`, `MkButton`, `MkEmptyState`, `MkOfflineBanner`, product/thread/order tiles. Kill per-screen duplication.
- Feature-first folders: `lib/features/{marketplace,forum,scanner,wallet,services,weather}/` each with `data/`, `state/`, `ui/`. Drop unused deps (`flutter_bloc`, `retrofit`) or commit to them — recommendation: stay on Provider now, migrate hot paths to Riverpod later if needed.
- Offline-first properly: drift as source of truth for catalog/forum reads, write-behind queue for orders/posts, connectivity provider drives sync.

**Backend hardening:**
- Delete the weather `rand()` fallback → serve last cached reading with `is_stale: true`, or 503 with a clear message.
- Disease scanner: ship cloud-only (Gemini 2.x) as v1; make `runTfliteInference` return honestly "unsupported" and remove the pretense. On-device model is Phase 3.
- Payment callbacks: enforce idempotency keys + signature verification on M-Pesa/Tigo webhooks; add feature tests for escrow hold → release → refund.
- Enforce tenant/country scope via a global query scope + middleware.
- Add the missing service-module skeletons (bookings table + provider profiles + availability) shared by Agronomist/Vet/Soil-testing — one generic `service_bookings` design serves EF-004/007/008; Logistics and Warehouse (EF-005/006) are separate, later.

**Admin dashboard:** migrate to `@tanstack/react-query` v5, add role-gated routes, and wire Analytics/FinancialReports to the real report endpoints (verify they aren't rendering placeholder data).

## 4. Execution Roadmap

**Phase 0 — Make it run (1 week).** Fix the three base-URL mismatches; obtain and set Gemini + M-Pesa sandbox credentials; remove weather fake data; write a real monorepo README (setup for all 3 surfaces); smoke-test auth → browse → order → pay(sandbox) → escrow end-to-end.

**Phase 1 — Flutter redesign (2–3 weeks).** go_router shell + design system + feature folders; re-skin the 5 core flows (marketplace, forum, scanner, wallet, profile) on the new components; Swahili/English strings via `flutter_localizations` + ARB files; offline sync for catalog/forum.

**Phase 2 — Feature completion (3–4 weeks).** Forum upgrades (upvotes, expert badges, regional sub-forums); generic services booking engine (agronomist, vet, soil testing); seller/agrodealer dashboard polish; admin dashboard react-query v5 migration; payment webhook hardening + feature tests; CI (Pint/PHPUnit, `flutter analyze`/test, eslint/build) on every PR.

**Phase 3 — SAD ambitions (later).** Logistics + warehouse marketplaces; RAG knowledge base (pgvector) fed by forum content; on-device TFLite scanner; IVR/voice-first flows beyond current logging; Kenya expansion (Daraja, KEPHIS compliance).

## 5. Weakest Assumptions / Failure Points

1. **Payments are the highest-risk unknown.** The M-Pesa (Daraja/KE) and Tigo Pesa services are written but untested against live sandboxes, and the escrow ledger has no test coverage. Do not open real money flows until Phase 2 hardening is done. Note: Daraja is Kenya M-Pesa; Tanzania M-Pesa (Vodacom OpenAPI) is a different integration — verify which market `MpesaService` actually targets before launch.
2. **Server reality unverified.** This audit is static; whether `mkulima.hudumapro.com` is deployed, migrated, and seeded is unknown. Phase 0 must start with a live health-check.
3. **Scanner accuracy.** Gemini-only diagnosis has no measured accuracy on East African crops; the SAD's 85% target is aspirational. Pilot with agronomist-verified feedback loops before marketing the feature.

## 7. Execution Status (2026-07-06)

**Phase 0 — done.** URLs unified (Flutter `--dart-define=API_URL`, admin `.env.production` → HTTPS host); weather `rand()` fallback replaced with stale-cache + honest unavailable state; scanner is honestly cloud-only (failed scans recorded as `failed`, 503 returned); Gemini + OpenWeather keys set in all env files, scanner model now configurable via `GEMINI_MODEL` (set to `gemini-2.5-flash`, key verified live); pubspec pruned; README rewritten. *Outstanding:* delete empty `routes/flutter/` (blocked by sandbox), verify OpenWeather key locally, live server smoke test.

**Phase 1 — core done.** `lib/core/app_router.dart` (go_router, `MaterialApp.router`, splash/onboarding via `context.go`); `lib/core/theme.dart` (MkColors/MkRadii design tokens); `lib/widgets/` seeded (`MkEmptyState`, `MkOfflineBanner`); `lib/core/strings.dart` bilingual strings. *Outstanding:* migrate the 27 screens' inline styles/strings onto the tokens/widgets incrementally (mechanical, needs `flutter analyze` locally), ARB-based l10n.

**Phase 2 — done.** Forum: `forum_votes` table (one toggleable vote per user — replaced unlimited-increment upvotes), regional sub-forums (`region` on threads/categories, filters), expert badges (`users.is_verified_expert`, `expert_title`, `mark-expert-answer` endpoint). Services engine: `service_providers` + `service_bookings` (agronomist/vet/soil-testing shared model), full directory/booking/status/rating API under `/api/services/*`. Payments: **critical bug fixed** — escrows migration enum was uppercase while the service wrote lowercase statuses (every write would fail on Postgres); schema rebuilt to match code incl. missing columns (`buyer_id`, `transaction_reference`, `paid_at`, `hold_until`, `failure_reason`); M-Pesa callback now idempotent (row lock + status guard); `tests/Feature/EscrowFlowTest.php` covers create/success/duplicate/failure. Admin: migrated to `@tanstack/react-query` v5 (build verified). CI: flutter analyze/test + admin build jobs added; coverage gate paused until suite grows.

**Phase 3 — EF-005/006 implemented (2026-07-07).** Logistics: `LogisticsController` — transporter directory (verified+available only), transporter registration, freight lifecycle open → quoted → accepted → in_transit → delivered with role-scoped transitions (requester accepts/cancels, assigned transporter moves transit states), locked quote race (`lockForUpdate`), post-delivery one-shot rating feeding transporter aggregate. Warehouse: `WarehouseController` — directory/show, registration, booking with capacity check + cost calc (tons × TZS/ton/month × ceil months), transitions pending → confirmed → stored → withdrawn with transactional capacity accounting (confirm reserves, withdraw/confirmed-cancel releases). Models `Transporter`, `FreightRequest`, `Warehouse`, `WarehouseBooking`; migration adds `transporters.rating_count`, `freight_requests.requester_rating/review`. Routes under `/api/logistics/*` and `/api/warehouses/*`; Flutter `ApiService` gained matching methods. Covered by `tests/Feature/LogisticsWarehouseTest.php` (8 tests). RAG knowledge base, on-device TFLite, and Kenya expansion remain future work.

**Wallet hardening (2026-07-07).** Fixed: (1) `/wallet/deposit` credited balance with **no payment** — now 503 in production (must flow through `/payments/initiate`); sandbox-only simulation kept for dev. (2) `/wallet/withdraw` route pointed at a **nonexistent controller method** (500 on every call) — implemented, production-gated like deposit. (3) `Wallet::transferTo` had no transaction/locking — now wrapped in `DB::transaction` with id-ordered `lockForUpdate` (deadlock-safe) and balance re-check under lock. (4) Self-transfer and unknown-recipient rejected; failed transfer no longer returns "Transfer successful". Covered by `tests/Feature/WalletTest.php` (4 tests). Suite now **28 tests / 97 assertions, all passing** (verified in sandbox, PHP 8.4 + sqlite, 2026-07-07).

**Admin dashboard (2026-07-07).** `ErrorBoundary` wraps the layout (one crashed page no longer blanks the app); `AuthContext` carries the verified `/auth/me` user; `RequireRole` gates HR, Financial Reports and Feature Flags to `superadmin` with an explicit access notice. Analytics/FinancialReports verified as wired to real `/api/admin/*` endpoints. Build verified (vite, sandbox).

**Flutter redesign executed (2026-07-07, session 2).** Shell reworked to the five REDESIGN §3 pillars: Soko / **Jukwaa** (was missing from tabs) / Huduma / Kagua / Wasifu; AI-chat tab removed (agronomist stays reachable via Huduma grid and `/agronomist`); `MkOfflineBanner` now wraps the shell body; dead code deleted (`GuestFeaturesScreen`, `LoginScreenPlaceholder`). Component library grown: `MkProductTile` (handles Product model + raw API map), `MkThreadTile` (reply/view/upvote counts, region chip, expert badge — surfaces the Phase-2 forum features in UI), plus existing `MkCard`/`MkButton`/`MkEmptyState`/`MkOfflineBanner`. **All 24 screens with hardcoded `0xFF2E7D32`/`0xFF1B5E20` migrated to `MkColors` tokens** (definitions now exist only in `core/theme.dart`); marketplace/forum/scanner also moved onto `MkEmptyState`/`MkButton`/`MkStrings` (titles, nav labels, search hint, error/empty states centralized). Verified by cross-reference (every `MkStrings.*`/`MkColors.*` usage resolves; no stale class refs); still needs `flutter analyze` + visual pass locally. Remaining: ARB-based l10n, drift write-behind queue for offline orders/posts.

**Mkulima Bot (2026-07-07).** Multi-turn AI chatbot & farm advisor. Backend: `bot_conversations`/`bot_messages` tables, `MkulimaBotService` (Gemini multi-turn `contents` with 20-message history window, `system_instruction` with Swahili-first advisor persona, keyword-RAG over verified KB docs, optional region weather grounding via `WeatherService`, honest 503 without key/on failure — failed turns persist nothing), `MkulimaBotController` under `/api/bot/*` (chat/list/show/delete, owner-scoped). `AgronomistController` hardcoded `gemini-2.0-flash` fixed to use `GEMINI_MODEL` config. `tests/Feature/MkulimaBotTest.php` (7 tests, faked Gemini: history-in-request assertion, cross-user 404s, no-persist-on-failure). Suite: **35 tests / 119 assertions**. Flutter: `MkulimaBotScreen` (bubbles, suggested Swahili prompts, typing indicator, new-chat action, auth-gated send) at `/bot`, `ApiService.botChat/botConversations/...`, Huduma grid entry (Free tier); single-shot "Mtaalamu wa AI" kept as separate Pro entry. KB seeding + pgvector semantic RAG remain Phase 3.

**Flutter router (2026-07-07).** `buildAppRouter(auth)` registers all no-arg screens as typed paths (`/soko`, `/forum`, `/scanner`, `/weather`, `/wallet`, …) with `refreshListenable` on `AuthProvider` and a redirect guard sending unauthenticated users to `/login?from=…` for the 9 protected paths. Arg-taking screens (product detail, payment, upgrade, thread detail) stay imperative from their parents. `lib/widgets/` gained `MkCard` and `MkButton` (loading-state CTA). *Unverified in sandbox — run `flutter analyze` locally.*

**Backend verified in sandbox (PHP 8.4 + sqlite, 2026-07-06):** `migrate:fresh` runs all 29 migrations clean; **9 tests / 26 assertions pass** (escrow create/success/duplicate-idempotency/failure, forum vote toggle, region filter, full service booking flow incl. rating); all 144 API routes resolve. Three more code/schema mismatches were found and fixed during this run: `escrow_ledger` was a status-log table while the service writes financial entries (rebuilt as a financial ledger); `EscrowService::createEscrow` never set `tenant_id` (fails outside an authenticated request — webhooks); `MpesaService`/`TigoPesaService` crashed on empty env values (null into typed string properties). `routes/flutter/` deleted.

**RBAC/auth audit (2026-07-06):** Found and fixed: (1) **`dev_code` leaked the OTP in the API response** — anyone could authenticate as any phone number; now gated to local/testing debug builds only. (2) **Spatie roles were never seeded** — `assignRole()` threw on every registration; added `RolesAndPermissionsSeeder` (12 roles, permission matrix) wired into `DatabaseSeeder`, plus a `firstOrCreate` guard in the register path. (3) **Role list chaos** — users-table enum rejected `buyer/seller/logistics/superadmin` that validation accepted; column is now a string and `App\Support\Roles` is the single source of truth used by validation everywhere. (4) **Escrows had no `uuid` column** while all payment endpoints query by uuid — added with auto-generation. (5) `UserFactory` didn't match the custom users schema — rebuilt. Verified by `tests/Feature/AuthRbacTest.php` (7 tests: OTP non-leak, role assignment on register, staff roles not self-registerable, admin gate allow/deny, seller gate, product ownership). Suite now **16 tests / 36 assertions, all passing**, and `migrate:fresh --seed` works end-to-end. Design note: authorization is middleware + inline `role` checks; Spatie permissions are seeded but not yet enforced per-route — adopting `permission:` middleware is a Phase 3 refinement.

**Still needs a local machine:** `flutter analyze` + app run, OpenWeather key verification (returned empty body through the sandbox proxy), and deployment — `https://mkulima.hudumapro.com/api/health` did not respond, so the production server appears not to be serving this app yet.

## 6. File-Level Fix List (Phase 0 quick reference)

- `mkulima_app/lib/main.dart:20` — replace hardcoded baseUrl with `String.fromEnvironment('API_URL')`.
- `admin-dashboard/.env.production` — `VITE_API_URL=https://mkulima.hudumapro.com/api`.
- `app/Services/WeatherService.php:~250` — remove `rand()` fallback.
- `app/Http/Controllers/Api/DiseaseScannerController.php:129` — honest TFLite unsupported path.
- `mkulima_app/pubspec.yaml` — remove or activate commented deps; remove unused `flutter_bloc`, `retrofit`.
- `routes/flutter/` — delete empty dir.
- `.env` — populate `GEMINI_API_KEY`, M-Pesa/Tigo sandbox creds.
- `README.md` — replace stock Laravel readme with monorepo setup guide.
