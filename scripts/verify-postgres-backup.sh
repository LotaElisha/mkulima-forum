#!/bin/bash
# Restore a backup into a side database, compare row counts to the manifest
# written by backup-mkulima-db.sh, then drop the side database.
#
# This script never drops or writes to DB_DATABASE. The side database name
# is always ${DB_DATABASE}_restore_check.
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
BACKUP_FILE="${1:-${BACKUP_DIR}/latest.sql.gz}"

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

if [ ! -f "${BACKUP_FILE}" ]; then
  echo "Backup not found: ${BACKUP_FILE}" >&2
  exit 1
fi

DB_HOST="$(read_env DB_HOST)"
DB_PORT="$(read_env DB_PORT)"
DB_NAME="$(read_env DB_DATABASE)"
DB_USER="$(read_env DB_USERNAME)"
DB_PASSWORD="$(read_env DB_PASSWORD)"
DB_CONNECTION="$(read_env DB_CONNECTION)"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
CHECK_DB="${DB_NAME}_restore_check"

if [ "${DB_CONNECTION}" != "pgsql" ]; then
  echo "Refusing to restore DB_CONNECTION=${DB_CONNECTION}." >&2
  exit 1
fi

if [ -z "${DB_NAME}" ] || [ "${CHECK_DB}" = "${DB_NAME}" ] || [[ "${CHECK_DB}" != *_restore_check ]]; then
  echo "Refusing to restore into '${CHECK_DB}'." >&2
  exit 1
fi

MANIFEST="${BACKUP_FILE}.manifest"
if [ ! -f "${MANIFEST}" ] && [ -f "${BACKUP_DIR}/latest.sql.gz.manifest" ] && [ "$(basename "${BACKUP_FILE}")" = "latest.sql.gz" ]; then
  MANIFEST="${BACKUP_DIR}/latest.sql.gz.manifest"
fi

if [ ! -f "${MANIFEST}" ]; then
  echo "No manifest next to ${BACKUP_FILE}. Take the backup with scripts/backup-mkulima-db.sh." >&2
  exit 1
fi

checksum_dir="$(cd "$(dirname "${BACKUP_FILE}")" && pwd)"
checksum_name="$(basename "${BACKUP_FILE}")"
if [ -f "${checksum_dir}/${checksum_name}.sha256" ]; then
  (cd "${checksum_dir}" && sha256sum -c "${checksum_name}.sha256")
fi

export PGPASSWORD="${DB_PASSWORD}"
CREATED=0

cleanup() {
  if [ "${CREATED}" -eq 1 ]; then
    dropdb --if-exists -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" "${CHECK_DB}" || true
  fi
}
trap cleanup EXIT

echo "Creating empty database ${CHECK_DB}"
dropdb --if-exists -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" "${CHECK_DB}"
if ! createdb -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" "${CHECK_DB}"; then
  echo "createdb failed. On the VPS, as the postgres superuser, run:" >&2
  echo "  ALTER USER ${DB_USER} CREATEDB;" >&2
  exit 1
fi
CREATED=1

echo "Restoring ${BACKUP_FILE} into ${CHECK_DB}"
gunzip -c "${BACKUP_FILE}" | psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${CHECK_DB}" -v ON_ERROR_STOP=1 >/dev/null

echo "Comparing counts with ${MANIFEST}"
failed=0
while IFS="$(printf '\t')" read -r table expected; do
  [ -n "${table}" ] || continue
  if [ "${expected}" = "absent" ]; then
    exists="$(psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${CHECK_DB}" -tAc "SELECT to_regclass('public.${table}')")"
    if [ -n "${exists}" ]; then
      echo "MISMATCH ${table}: manifest says absent, restore has the table" >&2
      failed=1
    fi
    continue
  fi
  actual="$(psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${CHECK_DB}" -tAc "SELECT count(*) FROM public.${table}")"
  if [ "${actual}" != "${expected}" ]; then
    echo "MISMATCH ${table}: backup counted ${expected}, restore has ${actual}" >&2
    failed=1
  else
    echo "OK ${table}=${actual}"
  fi
done < "${MANIFEST}"

if [ "${failed}" -ne 0 ]; then
  echo "Restore verification failed. ${CHECK_DB} will be dropped." >&2
  exit 1
fi

echo "Restore verification passed. Dropping ${CHECK_DB}."
