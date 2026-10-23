#!/bin/bash
# SSL expiry check. The certificate name follows APP_URL's host so the same
# script works for mkulimaforum.app and mkulimaforum.com. MONITOR_DOMAIN
# overrides that host. A www host falls back to the apex certificate when
# that is the directory Certbot created.
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

APP_URL="${APP_URL:-https://mkulimaforum.com}"
host="${APP_URL#*://}"
host="${host%%/*}"
host="${host%%:*}"
domain="${MONITOR_DOMAIN:-${host}}"
threshold_days=7

cert_path=""
for candidate in "${domain}" "${domain#www.}"; do
  [ -n "${candidate}" ] || continue
  if [ -f "/etc/letsencrypt/live/${candidate}/fullchain.pem" ]; then
    cert_path="/etc/letsencrypt/live/${candidate}/fullchain.pem"
    domain="${candidate}"
    break
  fi
done

if [ "${MONITOR_DRY_RUN:-}" = "1" ]; then
  printf 'domain=%s\n' "${domain}"
  printf 'cert=%s\n' "${cert_path:-/etc/letsencrypt/live/${domain}/fullchain.pem}"
  exit 0
fi

if [ -z "${cert_path}" ]; then
  msg="ALERT: no TLS certificate for ${domain} under /etc/letsencrypt/live"
  echo "${msg}"
  logger -t ssl-monitor "${msg}"
  exit 1
fi

expiry_date=$(openssl x509 -in "${cert_path}" -noout -enddate | cut -d= -f2)
expiry_epoch=$(date -d "${expiry_date}" +%s)
now_epoch=$(date +%s)
days_left=$(( (expiry_epoch - now_epoch) / 86400 ))

if [ "${days_left}" -le "${threshold_days}" ]; then
    msg="ALERT: SSL certificate for ${domain} expires in ${days_left} days (${expiry_date})"
    echo "${msg}"
    logger -t ssl-monitor "${msg}"
    if command -v mail >/dev/null 2>&1 && [ -n "${ALERT_EMAIL:-}" ]; then
        echo "SSL certificate for ${domain} expires in ${days_left} days (${expiry_date}). Renew with: certbot renew" | mail -s "SSL Alert: ${domain}" "${ALERT_EMAIL}"
    fi
    exit 1
else
    msg="OK: SSL certificate for ${domain} valid for ${days_left} days"
    echo "${msg}"
    logger -t ssl-monitor "${msg}"
    exit 0
fi
