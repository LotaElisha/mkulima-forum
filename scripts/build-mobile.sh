#!/bin/bash
# Build MkulimaForum mobile APK for production and publish to public/app
set -e

API_URL="${API_URL:-https://mkulimaforum.com/api}"
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
APP_DIR="${PROJECT_DIR}/mkulima_app"
PUBLIC_DIR="${PROJECT_DIR}/public/app"

echo "== Building MkulimaForum mobile app =="
echo "API URL: ${API_URL}"
echo "App dir: ${APP_DIR}"

cd "${APP_DIR}"

flutter clean
flutter pub get
flutter pub run build_runner build --delete-conflicting-outputs
flutter build apk --release --dart-define=API_URL="${API_URL}"

mkdir -p "${PUBLIC_DIR}"
cp "${APP_DIR}/build/app/outputs/flutter-apk/app-release.apk" "${PUBLIC_DIR}/mkulima-forum.apk"

# Record the version beside the APK so /download describes this exact file.
# pubspec "version: 1.2.3+45" -> versionName 1.2.3, versionCode 45.
PUBSPEC_VERSION="$(sed -n 's/^version:[[:space:]]*//p' "${APP_DIR}/pubspec.yaml" | head -1)"
VERSION_NAME="${PUBSPEC_VERSION%%+*}"
VERSION_CODE="${PUBSPEC_VERSION#*+}"
[ "${VERSION_CODE}" = "${PUBSPEC_VERSION}" ] && VERSION_CODE=""
printf '{"version":"%s","build":"%s","built_at":"%s"}\n' \
  "${VERSION_NAME}" "${VERSION_CODE}" "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  > "${PUBLIC_DIR}/mkulima-forum.apk.json"

echo "== APK published to ${PUBLIC_DIR}/mkulima-forum.apk =="
ls -lh "${PUBLIC_DIR}/mkulima-forum.apk"
cat "${PUBLIC_DIR}/mkulima-forum.apk.json"
