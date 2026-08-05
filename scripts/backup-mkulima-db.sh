#!/bin/bash
set -e
BACKUP_DIR="/opt/backups/mkulima-forum"
DB_NAME="mkulima_forum"
DB_USER="mkulima"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/mkulima_forum_${DATE}.sql.gz"
ENV_FILE="/opt/data/projects/mkulima-forum/.env"
if [ -f "${ENV_FILE}" ]; then
    PASS=$(awk -F= '/^DB_PASSWORD=/ {print substr($0, index($0, $2))}' "${ENV_FILE}")
    export PGPASSWORD="${PASS}"
fi
mkdir -p "${BACKUP_DIR}"
echo "Starting backup: ${BACKUP_FILE}"
pg_dump -h 127.0.0.1 -U "${DB_USER}" -d "${DB_NAME}" --clean --no-owner --no-privileges | gzip > "${BACKUP_FILE}"
ls -t "${BACKUP_DIR}"/mkulima_forum_*.sql.gz | tail -n +8 | xargs -r rm -f
echo "Backup completed: ${BACKUP_FILE}"
ls -lh "${BACKUP_FILE}"