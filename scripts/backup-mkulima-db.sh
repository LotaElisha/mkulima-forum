#!/bin/bash
# Dump the Mkulima Forum Postgres database, then write a checksum and a
# row-count manifest. Restore is a separate script and never targets this
# database: scripts/verify-postgres-backup.sh loads the dump into
# ${DB_DATABASE}_restore_check and drops that database afterwards.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

if [ -z "${ENV_FILE:-}" ]; then
  if [ -f /opt/data/projects/mkulima-forum/.env ]; then
    ENV_FILE="/opt/data/projects/mkulima-forum/.env"
  else
    ENV_FILE="${REPO_ROOT}/.env"
  fi
fi

BACKUP_DIR="${BACKUP_DIR:-/opt/backups/mkulima-forum}"
RETENTION="${RETENTION:-7}"

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

if [ ! -f "${ENV_FILE}" ]; then
  echo "Missing env file: ${ENV_FILE}" >&2
  exit 1
fi

DB_CONNECTION="$(read_env DB_CONNECTION)"
DB_HOST="$(read_env DB_HOST)"
DB_PORT="$(read_env DB_PORT)"
DB_NAME="$(read_env DB_DATABASE)"
DB_USER="$(read_env DB_USERNAME)"
DB_PASSWORD="$(read_env DB_PASSWORD)"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"

if [ "${DB_CONNECTION}" != "pgsql" ]; then
  echo "Refusing to dump DB_CONNECTION=${DB_CONNECTION}. PostgreSQL is the only supported database." >&2
  exit 1
fi

if [ -z "${DB_NAME}" ] || [ -z "${DB_USER}" ]; then
  echo "DB_DATABASE and DB_USERNAME must be set in ${ENV_FILE}" >&2
  exit 1
fi

export PGPASSWORD="${DB_PASSWORD}"
PSQL=(psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${DB_NAME}" -v ON_ERROR_STOP=1)

mkdir -p "${BACKUP_DIR}"
STAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/mkulima_forum_${STAMP}.sql.gz"
MANIFEST="${BACKUP_FILE}.manifest"

TABLES=(
  users social_accounts tenants products orders order_items
  forum_categories forum_threads forum_replies market_prices community_channels
  disease_scans farms wallets wallet_transactions escrows escrow_ledger
  seller_applications registered_inputs config_settings landing_settings
)

echo "Counting rows in ${DB_NAME}"
: > "${MANIFEST}"
for table in "${TABLES[@]}"; do
  exists="$("${PSQL[@]}" -tAc "SELECT to_regclass('public.${table}')")"
  if [ -z "${exists}" ]; then
    printf '%s\tabsent\n' "${table}" >> "${MANIFEST}"
    continue
  fi
  count="$("${PSQL[@]}" -tAc "SELECT count(*) FROM public.${table}")"
  printf '%s\t%s\n' "${table}" "${count}" >> "${MANIFEST}"
done

echo "Starting backup: ${BACKUP_FILE}"
pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${DB_NAME}" \
  --format=plain --clean --if-exists --no-owner --no-privileges \
  | gzip -c > "${BACKUP_FILE}"

(
  cd "${BACKUP_DIR}"
  sha256sum "$(basename "${BACKUP_FILE}")" > "$(basename "${BACKUP_FILE}").sha256"
)
ln -sfn "$(basename "${BACKUP_FILE}")" "${BACKUP_DIR}/latest.sql.gz"
cp -f "${MANIFEST}" "${BACKUP_DIR}/latest.sql.gz.manifest"
cp -f "${BACKUP_FILE}.sha256" "${BACKUP_DIR}/latest.sql.gz.sha256"

# Keep the newest RETENTION dumps and their sidecars. Never delete latest.sql.gz.
mapfile -t OLD < <(ls -1t "${BACKUP_DIR}"/mkulima_forum_*.sql.gz 2>/dev/null | tail -n +"$((RETENTION + 1))" || true)
for old in "${OLD[@]:-}"; do
  [ -n "${old}" ] || continue
  rm -f "${old}" "${old}.manifest" "${old}.sha256"
done

echo "Backup completed: ${BACKUP_FILE}"
ls -lh "${BACKUP_FILE}"
echo "Manifest: ${MANIFEST}"
