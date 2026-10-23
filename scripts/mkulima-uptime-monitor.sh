#!/bin/bash
# Uptime check. The host comes from APP_URL (environment, then the app .env)
# so staging (mkulimaforum.app) and production (mkulimaforum.com) share this
# script. MONITOR_BASE_URL overrides APP_URL when set.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

if [ -z "${ENV_FILE:-}" ]; then
  if [ -f /opt/data/projects/mkulima-forum/.env ]; then
    ENV_FILE="/opt/data/projects/mkulima-forum/.env"
  elif [ -f "${REPO_ROOT}/.env" ]; then
    ENV_FILE="${REPO_ROOT}/.env"
  fi
fi

read_env() {
  local key="$1"
  local line
  line="$(grep -E "^${key}=" "${ENV_FILE}" | tail -n 1 || true)"
  local val="${line#*=}"
  val="${val%\"}"
  val="${val#\"}"
  val="${val%\'}"
  val="${val#\'}"
  printf '%s' "${val}"
}

if [ -z "${APP_URL:-}" ] && [ -n "${ENV_FILE:-}" ] && [ -f "${ENV_FILE}" ]; then
  APP_URL="$(read_env APP_URL)"
fi

BASE="${MONITOR_BASE_URL:-${APP_URL:-https://mkulimaforum.com}}"
BASE="${BASE%/}"

URLS=(
    "${BASE}/api/health"
    "${BASE}/"
    "${BASE}/admin/"
    "${BASE}/api/market-prices"
    "${BASE}/api/weather/report?location=Dodoma"
)

if [ "${MONITOR_DRY_RUN:-}" = "1" ]; then
  printf '%s\n' "${URLS[@]}"
  exit 0
fi

failures=0
for url in "${URLS[@]}"; do
    code=$(curl -s -o /dev/null -m 15 -w "%{http_code}" "${url}")
    ts=$(date -Iseconds)
    if [ "${code}" -ge 200 ] && [ "${code}" -lt 400 ]; then
        echo "${ts} OK ${code} ${url}"
        echo "${ts} OK ${code} ${url}" | logger -t mkulima-uptime
    else
        echo "${ts} FAIL ${code} ${url}"
        echo "${ts} FAIL ${code} ${url}" | logger -t mkulima-uptime
        failures=$((failures + 1))
    fi
done

if [ "${failures}" -gt 0 ]; then
    msg="ALERT: ${failures} Mkulima Forum endpoint(s) are down"
    ts=$(date -Iseconds)
    echo "${ts} ${msg}"
    echo "${ts} ${msg}" | logger -t mkulima-uptime
    if command -v mail >/dev/null 2>&1 && [ -n "${ALERT_EMAIL:-}" ]; then
        echo "${msg}. Check logs with: journalctl -t mkulima-uptime" | mail -s "Mkulima Forum Uptime Alert" "${ALERT_EMAIL}"
    fi
    exit 1
fi

ts=$(date -Iseconds)
echo "${ts} All Mkulima Forum endpoints healthy"
echo "${ts} All Mkulima Forum endpoints healthy" | logger -t mkulima-uptime
exit 0
