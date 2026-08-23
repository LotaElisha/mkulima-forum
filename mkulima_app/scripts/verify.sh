#!/usr/bin/env bash
#
# Everything the audit environment could not run.
#
# The cloud audit had no Dart toolchain and every route to one was blocked, so
# `flutter analyze` and `flutter build` have never run against this code. This
# script is that missing step, in order, failing loudly at the first problem
# rather than carrying a broken artifact forward.
#
#   ./scripts/verify.sh                 # production API
#   API_URL=http://10.0.2.2:8000/api ./scripts/verify.sh
#   SKIP_BUILD=1 ./scripts/verify.sh    # analyze and test only, no APK
#
set -euo pipefail

cd "$(dirname "$0")/.."

API_URL="${API_URL:-https://mkulimaforum.app/api}"

step() { printf '\n\033[1;32m==>\033[0m \033[1m%s\033[0m\n' "$1"; }
die()  { printf '\n\033[1;31mFAILED:\033[0m %s\n' "$1" >&2; exit 1; }

command -v flutter >/dev/null 2>&1 || die "flutter is not on PATH"

step "Toolchain"
flutter --version

step "Dependencies"
flutter pub get

# Not optional. Five generated files are committed (models/*.g.dart,
# services/local_database.g.dart) and Drift and json_serializable will disagree
# with them the moment a schema moves. Regenerating first means the analyzer is
# reading truth rather than a stale artifact — a stale .g.dart produces errors
# that read like a broken model and send you looking in the wrong file.
step "Code generation"
dart run build_runner build --delete-conflicting-outputs

step "Static analysis"
flutter analyze || die "analyze reported problems - fix them before building"

step "Tests"
flutter test || die "tests failed"

if [ "${SKIP_BUILD:-0}" = "1" ]; then
  step "Build skipped (SKIP_BUILD=1)"
  exit 0
fi

# The release build refuses to run without android/key.properties - that is
# android/app/build.gradle rejecting debug signing keys on purpose, not a
# misconfiguration. Say so here rather than letting Gradle's stack trace be the
# first explanation anyone reads.
if [ ! -f android/key.properties ]; then
  cat >&2 <<'MSG'

  android/key.properties is missing, so a release build will be refused.
  This is deliberate: debug keys must never sign a published APK.

  Create android/key.properties (it is git-ignored):

      storeFile=/absolute/path/to/mkulima-release.jks
      storePassword=...
      keyAlias=mkulima
      keyPassword=...

  Or run with SKIP_BUILD=1 to stop after analysis and tests.

MSG
  exit 1
fi

step "Release APK  (API_URL=$API_URL)"
flutter build apk --release --dart-define=API_URL="$API_URL"

step "Done"
ls -lh build/app/outputs/flutter-apk/*.apk 2>/dev/null || true
printf '\nNext: install on a device and work through the visual checklist in\ndocs/FLUTTER_HANDOFF.md section 6. Static checks cannot see a screen.\n\n'
