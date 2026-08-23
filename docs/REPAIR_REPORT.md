# Mkulima Forum — End-to-End Repair Report

23 August 2026. Tests: **196 passing** (was 181). Every fix below was verified
against a running Laravel server with real HTTP calls, except where the row
says otherwise.

**What I could not do, stated first.** I have no Dart toolchain and no
physical device, so nothing here was compiled, installed, or tapped. `flutter
analyze` and `flutter build` have never run against this code — every route to
a toolchain is blocked from this environment, which I tested again this
session. The Flutter changes are verified by static checks (token resolution,
unused imports, delimiter balance across 67 files), by unit tests I wrote for
you to run, and by checking each contract against the real API payload. That
is not the same as running it. `mkulima_app/scripts/verify.sh` runs the whole
build-and-test sequence in one command.

---

## 1. Bugs found, with root cause

### 1.1 The login screen showed SokoMoto's logo — P0

`mkulima_app/assets/images/app_icon.jpg` **was** the SokoMoto logo: black "S",
orange chevron, the word "sokomoto". Login and register both rendered it. No
text reference to SokoMoto existed anywhere in the code, which is why a search
would not have found it — the wrong company's mark was sitting behind a
correctly-named file.

### 1.2 The public website's emblem was a photograph of an error screen — P0

`public/images/logo-icon.jpg` and `public/images/logo.jpg` (byte-identical)
were a **photo of a phone displaying a DioException stack trace** — the
"Kamera / Ghalari" buttons and the MDN status-code link are visible in it. This
is the file `routes/web.php` serves as `emblem_url` on every public page. It
has presumably been live for some time.

### 1.3 The Android launcher icon was the default Flutter logo — P1

`mipmap-*/ic_launcher.png` at all five densities was Flutter's blue bird. The
app on the home screen did not identify itself as Mkulima Forum. No adaptive
icon existed either, so Android 8+ rendered a shrunken legacy icon.

### 1.4 Notifications crashed on open — P0

```
type 'List<dynamic>' is not a subtype of type 'FutureOr<Map<String, dynamic>>'
```

`getNotifications()` was declared `Future<Map<String, dynamic>>` and returned
`response.data['notifications']`. The API sends
`{"notifications": [...], "unread_count": n}`, so that expression is a **List**.
The declared type and the returned value disagreed, and nothing caught it
because the value crossed the boundary as `dynamic`. It threw before the screen
saw a single row. Confirmed against the live payload.

Second defect on the same screen: even had it parsed, the screen then did
`data['notifications']` on the already-unwrapped list — a second crash waiting
behind the first.

### 1.5 Every farmer was offered a Seller Dashboard that refuses them — P0

`profile_screen.dart`:

```dart
if (user.role == 'farmer' || user.role == 'agrodealer')
    ... Dashibodi ya Muuzaji
```

The condition **includes farmers**. `SellerController` allows only
`seller, agrodealer, admin, superadmin`. So the app showed the entry to the
largest group of users on the platform and the API answered 403. Case A in your
brief — the client should never have called it.

The deeper cause: there was no such thing as *applying* to sell. An account was
either a farmer or a seller, with no middle. "Applied", "waiting", "refused and
why" had nowhere to live, so the UI had nothing truthful to draw.

### 1.6 `/seller/products` and `/seller/orders` had no authorization at all — P1

Only `dashboard()` checked the role. The other two scoped by `user_id` — so no
data leaked — but answered **200 with an empty list** to a farmer. "You are not
a seller" and "you have no products" were indistinguishable.

### 1.7 The seller dashboard was broken for real sellers too — P1

`getSellerDashboard()` returned `response.data['stats']` — the inner object —
while `SellerDashboardScreen` read `data['stats']` and `data['recent_orders']`
off the result. Both came back null. An approved seller reaching the dashboard
saw an empty screen with no error to explain it.

### 1.8 Starting a forum thread always failed — P0

Flutter sent `category_id`. The endpoint requires `forum_category_id`.

```
422 {"errors":{"forum_category_id":["The forum category id field is required."]}}
```

Neither side was wrong in isolation, which is why it survived. `Anzisha Thread`
could not have worked for anyone. Verified live, before and after.

### 1.9 Raw DioException text was shown to users in nine places — P0

`_error = e.toString()` in `orders_screen`, `marketplace_screen`,
`seller_dashboard_screen`, and six paths in `auth_provider`. That is the source
of the "read more at developer.mozilla.org" text on the screen.

Worse, the error handling that did exist matched **substrings of a stack
trace**: `if (msg.contains('401'))`. An OTP code containing "422", or a UUID
containing "404", picks the wrong branch.

`ApiService.formatError` — which the rest of the app did route through — fell
through to `error.message` and then `error.toString()` for anything it did not
recognise, so it leaked too.

### 1.10 The login 503 — P0, cause identified

`/auth/login/email` has no 503 path; it returns 200/401/403 only. The 503 comes
from the **phone/OTP** half of the same login screen. `auth.otp_enabled`
defaults to **false in production**, and `requestOtp` answers:

```
503 {"message": "OTP authentication is disabled."}
```

The backend was doing exactly the right thing and saying so in a translated
sentence. The app threw that away and printed the DioException instead. It read
as a broken login; it was a deliberate feature flag with no UI for it.

Confirmed by testing both paths live: email login returns 200, OTP returns 200
locally (where the flag defaults on) and 503 under production settings.

### 1.11 Google sign-in — P1, configuration not code

```
GoogleSignInException(clientConfigurationError,
  serverClientId must be provided on Android)
```

The code already reads `String.fromEnvironment('GOOGLE_SERVER_CLIENT_ID')`. The
APK was built **without** that `--dart-define`, so it passed null and
`google_sign_in` 7.x threw. The button rendered unconditionally, so it was
guaranteed to fail on tap.

### 1.12 Apple sign-in — P1, same shape

`APPLE_SERVICE_ID` not passed at build time; the code threw an `Exception` with
that text and the app interpolated it into the message shown to the user.

### 1.13 The plant scanner — P1, cause narrowed, not closed

The 503 and the message you saw come from `DiseaseScannerController`, which is
honest by design: it records a `failed` scan rather than inventing a diagnosis.
The failure is upstream — `runGeminiInference` returns null.

The Gemini call fails with **HTTP 403**. I cannot tell you whether that is your
key, because `generativelanguage.googleapis.com` is blocked from this
environment (connection refused at the proxy, `curl` returns 000). A 403 from
Google means one of exactly three things, and they need different fixes. See
§6.

### 1.14 AI failures were not being recorded — P1

```
Failed to log AI error usage: FOREIGN KEY constraint failed
```

`logUsage()` and `logError()` both hardcoded `'tenant_id' => 1`. On any
installation whose first tenant is not id 1, every insert failed the foreign
key and was swallowed by its own catch. AI failures went unrecorded precisely
when something was wrong.

### 1.15 The notifications badge was a lie — P2

`badge: '3'` was hardcoded on the profile row for every account, always. It sent
farmers to an empty screen looking for three things.

### 1.16 KYC was shown unexplained, and gates nothing — P2

The profile showed `KYC Verification / PENDING` to farmers with no explanation.
Tracing `kyc_status` through the backend: it is collected, displayed and
administered, but **no endpoint requires it** — not escrow, not the wallet, not
selling. It is inert.

### 1.17 A deploy password is sitting in a tracked config file — P1 security

`.claude/settings.local.json` line 38 contains
`export DEPLOY_SSH_PASSWORD='SokoMoto#255'` in plaintext. Rotate it and move it
out of the repository.

### 1.18 Two things I checked and found NOT broken

Reporting these so you know they were looked at rather than assumed.

- **`User::$fillable` mass-assignment guard is intact.** `kyc_status` appears at
  line 67 inside `PRIVILEGED_ATTRIBUTES`, not `$fillable`. I misread it once and
  checked before reporting.
- **Registration does set `status => 'active'`** on all three creation paths
  (email, OTP, social). An early "Account is not active" 403 in my testing was
  my own test fixture, not a product bug.
- **`/wallet/balance` and `/kyc/status`** do not return the `wallet` / `kyc` keys
  the client looks for — but the `?? response.data` fallback catches it and both
  work. The key names are wrong; the behaviour is not. Left alone, noted here.

---

## 2. What was repaired

### Branding

Extracted the emblem from `public/images/brand-banner.png` — the only correct
brand asset in the repository — and generated a clean circular mark from it.

- `mkulima_app/assets/images/mkulima_logo.png` — new, transparent outside the circle
- `mkulima_app/lib/widgets/mkulima_logo.dart` — one widget, used by login,
  register and splash. There is no longer a loose asset-path string for the
  wrong file to hide behind. Falls back to an icon if the asset fails to decode,
  so a missing logo cannot put a red error box on the login screen.
- `app_icon.jpg` **overwritten** with the correct mark as well as replaced in
  code, so a stale reference or a cached asset cannot restore SokoMoto's logo
- `public/images/logo-icon.jpg`, `logo.jpg` — error-screen photo replaced
- Android launcher icons regenerated at all five densities, plus a proper
  **adaptive icon** (`mipmap-anydpi-v26/ic_launcher.xml`, foreground inside the
  66% safe zone, monochrome variant for themed icons) and `values/colors.xml`
- Splash screen now shows the logo instead of a generic `Icons.grass_outlined`

One thing I cannot fix: **the banner image reads "Shinki. Jifunze. Endelea."**
It should be "Shiriki". It is baked into the PNG and needs redrawing.

### Roles and selling

New: `seller_applications` table, `SellerApplication` model,
`SellerStatus` service, `SellerApplicationController`, Swahili + English
strings, and 12 tests.

```
GET  /api/seller/status                        open to every authenticated user
POST /api/seller/application                   throttle 5/hour
POST /api/seller/applications/{uuid}/review    admin only
```

`GET /api/auth/me` and every authentication response now carry the same block:

```json
"seller": { "state": "none|pending|rejected|approved",
            "can_sell": false, "can_apply": true, "application": null }
```

The app no longer derives anything from the role string. Verified live end to
end: farmer applies → pending → dashboard still 403 → admin approves → role
granted in the same transaction → dashboard returns 200.

Security properties, each with a test: an applicant cannot approve themselves
(403); `status` is not mass-assignable, so posting `"status":"approved"` with
the application still lands in `pending`; approval and the role grant cannot
come apart, because coming apart reproduces the original 403 exactly.

Flutter: `SellerState` model, `BecomeSellerScreen` (the onboarding form),
`SellerStatusCard` (pending / rejected / not-yet cards), route `/become-seller`,
and the profile's business section rewritten to draw from `auth.seller.canSell`.
A malformed or absent seller block falls back to **cannot sell**, never to can.

Also: `SellerController` now uses `Roles::SELLERS` instead of a second
hand-maintained copy of the same list, and all three seller endpoints share one
guard whose 403 body carries the seller state — so the app can explain the
refusal and offer the application form instead of printing an exception.

### Error handling

New `lib/core/api_error.dart`. One place that maps any failure to a Swahili
sentence, keeping status code, Laravel field errors and the `X-Request-Id`
correlation id for logs.

| Status | Shown to the user |
|---|---|
| no response | Hakikisha una intaneti kisha ujaribu tena. |
| 401 | Muda wa kuingia umeisha. Tafadhali ingia tena. |
| 403 | Huna ruhusa ya kutumia huduma hii. |
| 429 | Umejaribu mara nyingi mno. Subiri kidogo kisha ujaribu tena. |
| 502/503/504 | Huduma haipatikani kwa sasa. Tafadhali jaribu tena baada ya muda mfupi. |
| other 5xx | Kuna tatizo la mfumo. Tafadhali jaribu tena. |

Laravel's own message is preferred for 4xx — it is already translated per the
user's `preferred_language`, so "OTP authentication is disabled" arrives in
Swahili. It is deliberately **not** used for 5xx, where the body may be an
Nginx HTML page or a stack trace, and never when the body carries `exception`
or `trace` keys.

`ApiService.formatError` delegates to it, which fixed every existing call site
at once. The nine `e.toString()` sites were replaced, and the
`msg.contains('401')` substring matching replaced with real status-code
switches.

Login now answers 401 and 404 identically — a different message for each turns
the login form into a way to discover which addresses are registered.

### Notifications

`AppNotification` and `NotificationFeed` typed models. The feed accepts the real
envelope, a bare list, and a paginated `{data:{data:[...]}}` shape, so the
screen is not what discovers pagination the day someone adds it. Booleans are
read however the database spelled them — MySQL tinyint, SQLite bool, JSON
string.

Screen rewritten: optimistic read-marking with rollback, real empty and error
states, pull-to-refresh that works on an empty list, brand colours instead of
hardcoded `0xFF2E7D32`, and **Swahili relative timestamps** ("Dakika 5
zilizopita") instead of the raw ISO8601 string it used to print under every row.

8 unit tests, using the payload captured from the live server.

### API contracts

Audited all 51 methods in `ApiService`. Fifteen indexed into `response.data`
while declaring a concrete return type — the same shape as the notifications
crash. All fifteen now go through `_asMap` / `_asList`, which cannot throw. I
then probed each endpoint against the live server to check the envelope keys
were real; results in §1.18.

### Social sign-in

`lib/core/social_auth_config.dart`. Buttons are drawn only for providers this
build carries credentials for — including the "AU JISAJILI NA" divider, which
otherwise sat above nothing. Provider exceptions are never interpolated into
user-visible text: `GoogleSignInException` prints its own configuration
diagnostics.

This is Option B from your brief. Option A needs credentials only you can
obtain — §6.

### AI scanner

Cannot be closed from here, but made diagnosable:

- `php artisan mkulima:ai-check` — new command, runs a real call and names the
  cause. `--image=path` runs the vision path specifically.
- The scanner log line now carries a classified reason
  (`api_key_rejected`, `quota_exhausted`, `network_unreachable`,
  `model_not_found`, …) alongside crop type and user id
- `catch (\Exception)` widened to `\Throwable` — a `TypeError` inside the
  provider adapter was escaping to become a raw 500
- A response that succeeds but returns non-JSON is now logged distinctly; it
  was indistinguishable from a network failure
- `tenant_id` in AI usage logging derived from the user instead of hardcoded to 1

**On offline-first:** there is currently no on-device AI. `tflite_flutter` is
commented out of `pubspec.yaml` and `DiseaseScannerController` says plainly
"v1 is cloud-only … We do not pretend to run local inference." That is honest,
and I left it honest rather than adding a fake local path. Restoring offline
inference is real work — a model file, a preprocessing pipeline, and a
confidence threshold for when to defer to the cloud.

---

## 3. Files changed

**Flutter — new (8)**

```
lib/core/api_error.dart              lib/models/app_notification.dart
lib/core/social_auth_config.dart     lib/models/seller_state.dart
lib/widgets/mkulima_logo.dart        lib/screens/become_seller_screen.dart
test/models/app_notification_test.dart
test/models/seller_state_test.dart
```

**Flutter — modified (11)**

```
lib/services/api_service.dart        lib/screens/login_screen.dart
lib/providers/auth_provider.dart     lib/screens/register_screen.dart
lib/core/app_router.dart             lib/screens/splash_screen.dart
lib/screens/profile_screen.dart      lib/screens/forum_screen.dart
lib/screens/notifications_screen.dart (rewritten)
lib/screens/orders_screen.dart       lib/screens/marketplace_screen.dart
lib/screens/seller_dashboard_screen.dart
```

**Flutter — assets**

```
assets/images/mkulima_logo.png                    new
assets/images/app_icon.jpg                        overwritten (was SokoMoto)
android/.../mipmap-{m,h,xh,xxh,xxxh}dpi/ic_launcher.png            regenerated
android/.../mipmap-*/ic_launcher_foreground.png                    new
android/.../mipmap-anydpi-v26/ic_launcher.xml                      new
android/.../values/colors.xml                                      new
```

**Laravel — new (7)**

```
database/migrations/2026_08_23_000001_create_seller_applications_table.php
app/Models/SellerApplication.php
app/Services/Seller/SellerStatus.php
app/Http/Controllers/Api/Seller/SellerApplicationController.php
app/Console/Commands/AiCheck.php
lang/{en,sw}/seller.php
lang/{en,sw}/scanner.php
tests/Feature/SellerOnboardingTest.php        12 tests
tests/Feature/ForumThreadContractTest.php      3 tests
```

**Laravel — modified (5)**

```
app/Http/Controllers/Api/AuthController.php          unified user payload
app/Http/Controllers/Api/SellerController.php        shared guard, Roles::SELLERS
app/Http/Controllers/Api/DiseaseScannerController.php failure classification
app/Services/AI/AIService.php                        tenant resolution
routes/api_seller.php                                application routes
public/images/{logo-icon,logo}.jpg                   error photo replaced
```

---

## 4. Feature status

| Feature | Before | After | Tested |
|---|---|---|---|
| Email login | Worked; 503 blamed on it | Unchanged; the 503 was OTP | Yes — live HTTP |
| Phone OTP | 503, shown as DioException | 503 with the server's Swahili message | Yes — live HTTP |
| Google login | Button always threw | Hidden unless configured at build | No — needs credentials |
| Apple login | Button always threw | Hidden unless configured at build | No — needs credentials |
| Registration | Worked | Unchanged | Yes — live HTTP |
| Password reset | Worked | Error handling no longer leaks | Yes — test suite |
| Notifications | Crashed on open | Typed models, 8 unit tests | Payload verified; **Dart tests not run** |
| Seller dashboard (farmer) | Raw 403 | Not offered; card explains and offers onboarding | Yes — 12 tests + live |
| Seller dashboard (seller) | Empty, wrong parsing | Envelope corrected | Yes — live HTTP |
| Seller onboarding | Did not exist | Full apply → review → approve flow | Yes — 12 tests + live |
| Forum threads | Always 422 | Field name corrected | Yes — 3 tests + live |
| Plant scanner | Generic failure | Same failure, now diagnosable | Cause narrowed to Gemini 403 |
| Branding | SokoMoto logo | Mkulima Forum throughout | Assets verified visually |
| Launcher icon | Flutter logo | Mkulima Forum + adaptive | Rendered and inspected |
| Error messages | Raw exceptions | Swahili, one layer | Yes — mapping reviewed |
| My Orders | Reported fine | Only the error leak fixed | No — untouched by design |

"Tested" means an assertion ran. Nothing in this table was tested on a phone.

---

## 5. Still needs you

**Credentials I cannot obtain**

1. **`GOOGLE_SERVER_CLIENT_ID`** — the OAuth **Web** client id from Google
   Cloud, not the Android one. Android matches its client by package name
   (`app.mkulimaforum.mobile`) and SHA-1, and the Android id is never named in
   code. Passing the Android id here is the most common way this breaks.
   Register the release SHA-1 **and** SHA-256 from your production keystore.
2. **`APPLE_SERVICE_ID`** — the Services ID (e.g. `app.mkulimaforum.signin`),
   plus Team ID, Key ID and the `.p8` private key on the backend, and
   `https://mkulimaforum.app/api/auth/apple/callback` registered as a Return URL.
3. **Gemini** — run `php artisan mkulima:ai-check` on the server. Three causes
   for a 403: the key is invalid; the *Generative Language API* is not enabled
   on that Google Cloud project; or the key has an HTTP-referrer/IP restriction
   that excludes your server. Check in that order.
4. **`MAIL_PASSWORD` and `DB_PASSWORD`** — still empty in `.env.production`.

**Build command, with everything**

```bash
flutter build apk --release \
  --dart-define=API_URL=https://mkulimaforum.app/api \
  --dart-define=GOOGLE_SERVER_CLIENT_ID=<web client id> \
  --dart-define=APPLE_SERVICE_ID=app.mkulimaforum.signin
```

Omit a define and that button simply does not appear — which is the intended
behaviour, not a failure.

**Decisions for you**

- **KYC.** It currently gates nothing. Either wire it to Mkulima Pay and Escrow
  and say so on the screen, or fold it entirely into seller verification. I have
  moved it under the seller section and relabelled it "Uthibitisho wa Muuzaji"
  with plain-language status, which is right if it is seller KYC. If it is
  financial KYC it belongs under Mkulima Pay instead.
- **The banner typo** — "Shinki" should be "Shiriki".
- **Rotate `SokoMoto#255`** and remove it from `.claude/settings.local.json`.
- **Seller review UI.** The endpoint exists and is admin-gated; the React admin
  dashboard has no screen for it yet, so approvals currently need an API call.

**Not started, and why**

- Offline TensorFlow Lite inference — a real project, not a repair
- `firebase_messaging` / push — commented out of `pubspec.yaml`; the backend
  `PushNotificationService` is complete and waiting
- Sentry — needs `composer require`, which is blocked here
- Order lifecycle testing across all nine states — needs seeded orders in each
  state; I did not want to change working flows to create them

---

## 6. First things to do

1. `cd mkulima_app && ./scripts/verify.sh` — this is the step I could not run.
   Expect analyzer findings; I wrote a lot of Dart without a compiler.
2. `php artisan migrate --force` — the new `seller_applications` table.
3. `php artisan mkulima:ai-check` — settles the scanner in five seconds.
4. Build with all three defines and check the login screen: correct logo, and
   only the social buttons you have credentials for.
5. Work through the visual checklist in `docs/FLUTTER_HANDOFF.md` §6.
