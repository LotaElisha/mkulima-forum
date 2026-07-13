---
name: verify
description: Build, run and drive Mkulima Forum's three surfaces (Laravel API, Flutter app, React admin) to verify changes end-to-end.
---

# Verifying Mkulima Forum

Three surfaces; drive whichever the diff touches.

## Laravel API (primary surface)

```bash
php artisan migrate:fresh --seed          # seeds roles, admin, products, forum, flags
php artisan serve --port=8000 --no-reload # IMPORTANT: --no-reload; the watcher
                                          # dies with "Address already in use"
                                          # after PHP file edits mid-session
```

Handles:
- Admin login: `POST /api/auth/login` `{"email":"admin@mkulima.forum","password":"admin123"}`
- Farmer: `POST /api/auth/otp/request` `{"phone":"255712345678","purpose":"register"}` →
  response includes `dev_code` (local/testing only) → `POST /api/auth/otp/verify`
  with `code`, `name`, `country_code:"tz"` → token.
- Weather needs `OPENWEATHER_API_KEY` in .env (set); first upstream call can
  time out (~10s) — retry once, then it caches.

## Admin dashboard (React, port 3020)

`preview_start` name `admin-dashboard` (see `.claude/launch.json`). Vite proxies
`/api` → localhost:8000, base path `/admin/`. Login form is at `/admin/login`
(prefilled email). Pages using `prompt()/confirm()` (Moderation, Escrows,
Market Prices): stub `window.prompt/confirm` via preview_eval before clicking.

## Flutter app (web, port 5051)

`preview_start` name `flutter-web` (launch.json; `--dart-define=API_URL=http://localhost:8000/api`).
Debug web build takes 60–90s after "Server started"; the page shows the
"Inapakia Mkulima Forum..." loader until the engine boots — wait and reload once.
- Resize preview to `mobile` (375x812) — desktop-short viewports trigger
  onboarding overflow stripes.
- Flutter renders to canvas: `preview_click` selectors don't work. Enable
  semantics first (`document.querySelector('flt-semantics-placeholder').click()`),
  then click `flt-semantics[aria-label=...]` nodes, or dispatch
  PointerEvent down/up + MouseEvent click at screenshot coordinates.
- Text fields render real `<input>`s once focused: click the field, then set
  value via the native setter + dispatch `input` event.

## Gotchas

- Killing/starting flutter-web: port 5051 must be free (`lsof -ti:5051 | xargs kill -9`).
- `php artisan test` exists (56 tests) but is CI's job — verification means
  driving the endpoints/UI above.
