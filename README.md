# MkulimaForum — Agricultural Super App for East Africa

Multivendor marketplace, farmers forum, AI plant disease scanner, wallet/escrow payments, weather advisories, and admin operations — built for Tanzanian (and later EAC) smallholder farmers.

> **Start here:** [`REDESIGN.md`](REDESIGN.md) — full platform audit, redesign decisions, and phased roadmap.
> Target architecture and requirements live in `MkulimaForum_ Modern Agri Hub/`.

## Monorepo Layout

| Path | What it is | Stack |
|---|---|---|
| `/` (root) | Backend API | Laravel 13, Sanctum, Spatie Permission, PostgreSQL only |
| `mkulima_app/` | Farmer mobile app | Flutter ≥3.5, Provider, drift (offline-first), dio |
| `admin-dashboard/` | Admin web panel | React 18, Vite, Tailwind |
| `MkulimaForum_ Modern Agri Hub/` | Architecture & requirements docs | — |

## Backend (Laravel API)

PostgreSQL is the only supported database. Create a role and two databases
(the second is what `php artisan test` migrates; it must not be the application
database):

```bash
sudo -u postgres psql -c "CREATE USER mkulima WITH PASSWORD 'secret' CREATEDB;"
sudo -u postgres psql -c "CREATE DATABASE mkulima_forum OWNER mkulima;"
sudo -u postgres psql -c "CREATE DATABASE mkulima_test OWNER mkulima;"
composer install
cp .env.example .env && php artisan key:generate
# .env.example is already DB_CONNECTION=pgsql. Set DB_PASSWORD, then:
php artisan migrate --seed
php artisan serve   # http://localhost:8000
php artisan test    # uses mkulima_test, from phpunit.xml
```

Production is the Ubuntu VPS in [`DEPLOYMENT_RUNBOOK.md`](DEPLOYMENT_RUNBOOK.md).
Before a deploy: `php artisan mkulima:preflight`. To count users and content
without changing them: `php artisan mkulima:census`.

Required `.env` keys for full functionality (all currently empty — features degrade honestly without them):

- `GEMINI_API_KEY` — plant disease scanner (returns 503 without it)
- `OPENWEATHER_API_KEY` — weather (serves stale cache / unavailable without it; never fabricates data)
- `MPESA_CONSUMER_KEY/SECRET/PASSKEY`, `TIGOPESA_API_KEY/SECRET` — payments (sandbox mode)

Route files: `routes/api.php` (core), `api_kyc.php`, `api_notifications.php`, `api_seller.php`. Health check: `GET /api/health`.

## Flutter App

```bash
cd mkulima_app
flutter pub get
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api        # Android emulator → local API
flutter build apk --dart-define=API_URL=https://mkulimaforum.com/api
```

The API base URL is injected via `--dart-define=API_URL` (defaults to production). No hardcoded hosts.

## Admin Dashboard

```bash
cd admin-dashboard
npm install
npm run dev     # http://localhost:3020, proxies VITE_API_URL (default /api)
npm run build   # uses .env.production → https://mkulimaforum.com/api
```

## Canonical API Host

All clients target **`https://mkulimaforum.com/api`** in production. Change it in one place per client: `--dart-define=API_URL` (Flutter), `.env.production` (admin), `APP_URL` in `.env` (backend).

## Status

Phase 0 (make it run) is in progress — see `REDESIGN.md` §4 for the roadmap and §6 for the fix list.

## Production configuration

Most operational settings are managed from **Admin → System → Configuration** rather than
by editing `.env` on the server: SMTP credentials, SMS and IVR provider settings, webhook
secrets, short-link allowed hosts, and application URLs.

Settings resolve **database → `.env` → default**, and are overlaid onto Laravel's runtime
config on every request, so they work correctly under `php artisan config:cache` and take
effect with no cache clear and no deploy.

Secrets are encrypted at rest, never returned to the client, restricted to superadmins,
and audit-logged as a rotation rather than a value.

Verify any environment with:

```bash
php artisan mkulima:preflight
```

Full detail — including which settings must stay in `.env` and why, the security model,
and queue worker setup — is in [`docs/CONFIGURATION.md`](docs/CONFIGURATION.md).
