#!/usr/bin/env bash
# Emulator smoke test for the Mkulima Forum APK. Usage: apk-smoke.sh <apk>
set -u
APK="$1"
PKG=app.mkulimaforum.mobile
OUT=smoke
mkdir -p "$OUT"

shot() {
  adb exec-out screencap -p > "$OUT/$1.png"
  python3 - "$OUT/$1.png" "$1" <<'PY'
import base64, io, sys
from PIL import Image
im = Image.open(sys.argv[1]).convert('RGB')
im = im.resize((270, int(im.height * 270 / im.width)))
buf = io.BytesIO(); im.save(buf, 'PNG', optimize=True)
print(f"MKSHOT-BEGIN {sys.argv[2]}")
print(base64.b64encode(buf.getvalue()).decode())
print(f"MKSHOT-END {sys.argv[2]}")
PY
}

# Tap the centre of the first on-screen element whose text or description
# matches $1. Flutter exposes its semantics to UIAutomator.
tap_text() {
  adb shell uiautomator dump /sdcard/ui.xml >/dev/null 2>&1
  adb pull /sdcard/ui.xml "$OUT/ui.xml" >/dev/null 2>&1
  python3 - "$OUT/ui.xml" "$1" <<'PY' | { read -r x y && [ -n "$x" ] && adb shell input tap "$x" "$y" && echo "tapped '$2' at $x,$y" || echo "not found: $2"; }
import re, sys
xml = open(sys.argv[1], encoding='utf-8', errors='ignore').read()
want = sys.argv[2].lower()
for node in re.finditer(r'<node [^>]*>', xml):
    n = node.group(0)
    label = ' '.join(re.findall(r'(?:text|content-desc)="([^"]*)"', n)).lower()
    if want in label:
        b = re.search(r'bounds="\[(\d+),(\d+)\]\[(\d+),(\d+)\]"', n)
        if b:
            x1, y1, x2, y2 = map(int, b.groups())
            print((x1 + x2) // 2, (y1 + y2) // 2); break
PY
}

alive() { adb shell pidof "$PKG" >/dev/null; }

adb logcat -c
adb install -r "$APK" || exit 1
adb shell monkey -p "$PKG" -c android.intent.category.LAUNCHER 1 >/dev/null
sleep 25
shot 01-launch

tap_text "Ruka" "Ruka"; sleep 8; shot 02-home
tap_text "Soko" "Soko"; sleep 8; shot 03-soko
tap_text "Jukwaa" "Jukwaa"; sleep 8; shot 04-jukwaa
tap_text "Wasifu" "Wasifu"; sleep 5; shot 05-wasifu
tap_text "Ingia Sasa" "Ingia Sasa"; sleep 4; shot 06-login
adb shell input keyevent KEYCODE_BACK; sleep 2
tap_text "Kagua" "Kagua"; sleep 6; shot 07-kagua

adb logcat -d > "$OUT/logcat.txt"
echo "---- app errors ----"
grep -E "FATAL EXCEPTION|AndroidRuntime|flutter.*(Error|Exception)|E/flutter" "$OUT/logcat.txt" | head -60 || true

if ! alive; then echo "APP NOT RUNNING at end of smoke test"; exit 1; fi
if grep -q "FATAL EXCEPTION" "$OUT/logcat.txt"; then echo "CRASH detected"; exit 1; fi
echo "SMOKE OK"
