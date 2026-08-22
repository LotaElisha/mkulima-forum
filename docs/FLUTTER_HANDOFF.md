# Mkulima Forum — Flutter Handoff (for Antigravity)

Everything needed to build, run and **visually verify** the Flutter client
outside this audit environment.

**Why this document exists.** The audit environment had no Dart toolchain and
every route to one was blocked — `storage.googleapis.com`, `dl.google.com`,
`pub.dev` and the GitHub archive all refused. So `flutter pub get`,
`flutter analyze` and `flutter build` were never run here. What *was* run is
listed under [Static checks already done](#static-checks-already-done); those
are not a build, and this document does not pretend otherwise. Section
[Verification checklist](#verification-checklist) is the work that is still
genuinely outstanding.

Repository root for the app: `mkulima_app/`.

---

## 1. Environment

| Requirement | Value | Where it comes from |
|---|---|---|
| Dart SDK | `>=3.8.0 <4.0.0` | `pubspec.yaml` |
| Flutter | any channel shipping Dart 3.8+ (3.32 or newer) | derived from the SDK constraint |
| Java | 17 | `android/app/build.gradle` — `sourceCompatibility`/`jvmTarget` |
| compileSdk | 36 | `android/app/build.gradle` |
| Application ID | `app.mkulimaforum.mobile` | `android/app/build.gradle` |

```bash
cd mkulima_app
flutter --version          # confirm Dart >= 3.8
flutter pub get
dart run build_runner build --delete-conflicting-outputs
flutter analyze
```

`build_runner` is not optional. Five generated files are committed
(`lib/models/{farm,order,product,user}.g.dart`,
`lib/services/local_database.g.dart`) and Drift/json_serializable will
disagree with them if the schema moved. Regenerate before analysing so the
analyzer is reading truth.

---

## 2. The one configuration knob

There is exactly one build-time variable, and it has a single definition:

```dart
// lib/providers/auth_provider.dart
const String kApiBaseUrl = String.fromEnvironment(
  'API_URL',
  defaultValue: 'https://mkulimaforum.app/api',
);
```

`lib/main.dart` reads it (`ApiService(baseUrl: kApiBaseUrl)`) and the Apple
sign-in redirect derives from it. Nothing else hardcodes a host — that is
enforced, not aspirational: the previous default was `mkulimaforum.com`, a
domain the server does not serve, fixed in commit `17fcd29a`.

### Build commands

```bash
# Production — this is the correct URL. .app, not .com.
flutter build apk --release \
  --dart-define=API_URL=https://mkulimaforum.app/api

# Local backend, Android emulator (10.0.2.2 is the host from inside the emulator)
flutter run --dart-define=API_URL=http://10.0.2.2:8000/api

# Local backend, physical device on the same wifi
flutter run --dart-define=API_URL=http://192.168.x.x:8000/api

# Split per ABI — roughly a third the download size, worth it on Tanzanian data
flutter build apk --release --split-per-abi \
  --dart-define=API_URL=https://mkulimaforum.app/api
```

`APP_URL` in `.env.production` is `https://mkulimaforum.app` and matches the
Nginx server names in `DEPLOYMENT_RUNBOOK.md`. Use `.app` everywhere.

### Release signing

`android/app/build.gradle` **throws** on a release build when
`android/key.properties` is absent:

> `Release signing requires android/key.properties; debug keys are forbidden.`

That is deliberate — the APK currently on the download page is signed with a
test key and the page says so. Create `android/key.properties` (git-ignored):

```properties
storeFile=/absolute/path/to/mkulima-release.jks
storePassword=…
keyAlias=mkulima
keyPassword=…
```

Note `namespace = "com.example.mkulima_app"` in `android/app/build.gradle`.
It is only the generated `R` class package so it does not block a Play
listing (`applicationId` is what Play checks, and that is correct), but it is
worth renaming before the store submission.

---

## 3. What was added this round: account identity linking

### The problem being solved

OTP registration keyed on phone number alone. A farmer who signed up with an
email address in January and later signed in with their phone in March got a
**second, empty account** — different farm records, different orders,
different wallet. Nothing in the product could tell the two apart, and the
longer the platform ran the more expensive the mess became.

The backend fix is `App\Services\Auth\AccountIdentityService`, four rules
applied in order:

1. If the identity is already on an account, that is the account. Always.
2. If someone is signed in and the identity is unclaimed, attach it to the
   account they are already using.
3. If the identity belongs to a **different** account, refuse and say so.
   Never merge automatically — merging moves farm records and wallet balances,
   and an attacker holding one phone number must not be able to trigger it.
4. Only when nothing matches and nobody is signed in, create.

### New/changed Flutter files

| File | Status | What it does |
|---|---|---|
| `lib/screens/account_identities_screen.dart` | new | `AccountIdentitiesScreen` + `LinkPhoneScreen` |
| `lib/services/api_service.dart` | +6 methods | the identity endpoints |
| `lib/core/app_router.dart` | +2 routes | `/identities`, `/identities/link-phone`, both guarded |
| `lib/screens/profile_screen.dart` | +1 section | "Akaunti → Barua pepe na Simu", placed first |

`AccountIdentitiesScreen` lists email, phone and social identities as cards
with verified badges, offers resend-verification for an unverified email, and
gates unlink behind the account password. `LinkPhoneScreen` is two steps:
`+255` prefix with a 9-digit field, then a 6-digit code that auto-submits on
the sixth digit, with a 60-second resend countdown.

---

## 4. API contract

Base: `{API_URL}` → `https://mkulimaforum.app/api`.
All six require `Authorization: Bearer {sanctum token}`.
Throttles are per route and stated below; exceeding one returns **429** with
Laravel's standard `Retry-After` header.

### `GET /auth/identities`

**200**
```json
{
  "identities": {
    "email":  { "value": "amina@example.com", "verified": true,
                "pending": null, "can_unlink": true },
    "phone":  { "value": "255712345678", "verified": true, "can_unlink": true },
    "social": [ { "provider": "google", "email": "amina@gmail.com" } ]
  }
}
```

`can_unlink` is computed server-side and is false when removing that identity
would leave no way into the account. Trust it; do not recompute it in the
client.

### `POST /auth/phone/link/request` — throttle 5/10min

Body: `{ "phone": "255712345678" }` — regex `^255[0-9]{9}$`, no `+`, no
leading zero.

| Status | Body | Meaning |
|---|---|---|
| 200 | `{ "message": "...", "expires_in": 600 }` | code sent |
| 200 | `+ "dev_code": "123456"` | **local/testing with `APP_DEBUG=true` only.** Never in production |
| 422 | `{ "message": "phone_already_linked" }` | already on this account and verified |
| 422 | `{ "message": ..., "errors": { "phone": [...] } }` | belongs to another account — refused before spending an SMS |
| 503 | `{ "message": "otp_disabled" }` | `auth.otp_enabled` is off |
| 503 | `{ "message": "otp_unavailable" }` | production with no SMS provider configured, or the send failed |
| 429 | `{ "message": "otp_rate_limited" }` | OTP service throttle (separate from the route throttle) |

### `POST /auth/phone/link/confirm` — throttle 10/10min

Body: `{ "phone": "255712345678", "code": "123456" }` (`code` exactly 6 chars)

| Status | Body |
|---|---|
| 200 | `{ "message": "phone_linked", "identities": { …same shape as above… } }` |
| 422 | `{ "message": "otp_invalid" }` |
| 429 | `{ "message": "otp_attempts" }` — too many verification attempts |
| 503 | `{ "message": "otp_disabled" }` |
| 422 | validation error on `phone` when the number was claimed between request and confirm |

The 200 returns the full refreshed identities map, so the caller does not
need a second round trip.

### `DELETE /auth/phone/link` — throttle 5/10min

Body: `{ "current_password": "…" }`

| Status | Body |
|---|---|
| 200 | `{ "message": "phone_unlinked", "identities": { … } }` |
| 422 | `{ "message": "no_phone_linked" }` |
| 422 | `{ "errors": { "current_password": [...] } }` — wrong password |
| 422 | `{ "message": "last_credential" }` — this is the only way in; refused |

### `GET /auth/email/status`

**200**
```json
{ "email": "amina@example.com", "email_verified": true,
  "pending_email": null, "phone": "255712345678", "phone_verified": true }
```

### `POST /auth/email/resend` — throttle 3/10min

**200** `{ "message": "verification_sent" }` — always, whether or not there was
anything to send. Do not infer account state from this response.

> The `message` values above are translation keys resolved server-side from
> `lang/{sw,en}/auth_flows.php` per the user's `preferred_language`, so what
> arrives is Swahili or English prose. `ApiService.formatError` surfaces
> `response.data['message']` verbatim, which is why the screens show the
> server's wording rather than inventing their own.

### Prerequisite: `auth.otp_enabled`

Phone linking is dark until this is switched on. It lives in the config
registry, not `.env` — **Admin → System → Configuration**, or:

```php
setting('auth.otp_enabled', true);
```

With it off, every link endpoint answers 503 and the screen shows the
server's message. That is the intended launch state: email auth is
production-ready, phone OTP waits on an SMS provider contract, and it was
never allowed to gate the launch.

---

## 5. Static checks already done

Run in the audit environment with a purpose-built checker
(`MkColors` token resolution, unused local imports, delimiter balance) across
61 hand-written Dart files:

- every `MkColors` token resolves — most used `primary`(95), `muted`(24),
  `charcoal`(20), `danger`(19), `border`(12), `surface`(11)
- no unused local imports
- all delimiters balanced

**This is not a build.** It cannot catch a type error, a missing `const`, an
API that moved between package versions, or anything about how the thing
looks. Treat it as a syntax floor, nothing more.

Two bugs it did *not* catch, found by reading and fixed by hand — a reminder
of exactly what this class of checking misses:

- `MkColors.charcoal` had been aliased to the brand green while also serving
  as text ink, producing green-on-green invisible button labels.
- The unlink confirmation dialog disabled its confirm button on
  `controller.text.isEmpty` inside a `StatefulBuilder` with no `onChanged`,
  so the dialog never rebuilt as the user typed and the button stayed
  permanently disabled.

---

## 6. Verification checklist

This is the outstanding work. Nothing below has been done.

### Build

- [ ] `flutter pub get` resolves with no version conflicts
- [ ] `dart run build_runner build --delete-conflicting-outputs` succeeds
- [ ] `flutter analyze` — **zero** errors. Warnings triaged, not ignored
- [ ] `flutter build apk --release --dart-define=API_URL=https://mkulimaforum.app/api`
- [ ] APK installs and opens on a real device

### Screenshot and review each screen

At **360px** width first — that is the floor, and the cheapest phone in the
target market. Then 390 and 430.

- [ ] `/identities` — loaded state, all three identity types present
- [ ] `/identities` — loading skeletons (throttle the network to see them)
- [ ] `/identities` — error state, and the retry button actually retries
- [ ] `/identities` — email unverified, showing the resend affordance
- [ ] `/identities/link-phone` step 1 — phone entry
- [ ] `/identities/link-phone` step 2 — code entry with the resend countdown
- [ ] Unlink dialog — button disabled empty, enabled once typed *(the bug above)*
- [ ] `/profile` — the new "Akaunti" section sits above "Miamala na Malipo"
- [ ] Login, register, home, marketplace, scanner, community, profile

### What to check in each screenshot

- [ ] **Theme** — white surfaces, green reserved for CTAs, nav, badges and
      icons. Roughly 90% white. Any large green field is wrong
- [ ] **Contrast** — no green text on a green ground anywhere *(this exact
      bug shipped once already)*
- [ ] **Touch targets** — 44px minimum, 48px on primary actions
- [ ] **Text** — nothing below 13px; no clipping or ellipsis on Swahili
      strings, which run longer than the English
- [ ] **No horizontal overflow** — no yellow-and-black overflow stripes at any
      width
- [ ] **Loading** — skeletons, not a bare spinner on a white page
- [ ] **Empty** — says what to do next, not just "no data"
- [ ] **Errors** — the server's message, in the user's language, with a retry

### Functional passes

- [ ] Register → verification email arrives → link verifies → app reflects it
- [ ] Forgot password → reset link → sign in with the new password
- [ ] Link a phone with `auth.otp_enabled` **off** → 503 message, no crash
- [ ] Link a phone with it **on**, local backend → `dev_code` auto-fills → linked
- [ ] Link a number owned by another account → refused, clear message,
      **no second account created** — verify in the `users` table
- [ ] Unlink with the wrong password → refused
- [ ] Unlink when phone is the only credential → refused with `last_credential`
- [ ] Airplane mode → offline behaviour (Drift) → reconnect → sync

### Known gaps, deliberate

- [ ] `firebase_messaging` is commented out of `pubspec.yaml`. Push
      notifications do not work in the app, while the backend has a complete
      `PushNotificationService` waiting for them
- [ ] `tflite_flutter`, maps and geolocation are likewise commented out
- [ ] Drone and IoT screens are labelled "Inakuja" — the backend answers 503
      by design, there is no operator network or device fleet yet

---

## 7. If something does not build

The most likely failures, in order:

1. **Generated files out of date** → `dart run build_runner build
   --delete-conflicting-outputs`. Drift and json_serializable both write into
   `lib/`, and a stale `.g.dart` produces errors that read like a broken
   model rather than a stale artifact.
2. **`sqlite3_flutter_libs` NDK** → it needs the NDK the Flutter plugin pins;
   `flutter doctor --android-licenses` and a matching NDK install.
3. **Release build throws on signing** → that is `android/app/build.gradle`
   refusing debug keys, by design. Create `android/key.properties`.
4. **Everything 401 at runtime** → check `API_URL` reached the build.
   `String.fromEnvironment` silently falls back to its default, so a
   misspelled `--dart-define` fails quietly rather than loudly.
