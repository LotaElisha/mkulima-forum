#!/bin/bash
# Mkulima Forum cron health monitor.
# CRON_FILE and LOG_DIR override the installed paths (used by tests).
log_dir="${LOG_DIR:-/var/log}"
alert_email="${ALERT_EMAIL:-}"
failures=0

if [ -n "${CRON_FILE:-}" ]; then
    cron_file="${CRON_FILE}"
elif [ -f /etc/cron.d/mkulima-forum ]; then
    cron_file="/etc/cron.d/mkulima-forum"
else
    cron_file="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/deploy/cron/mkulima-forum"
fi

# An uncommented market-prices:sync line means the daily job is scheduled.
market_sync_scheduled() {
    [ -f "${cron_file}" ] || return 1
    grep -Eq '^[[:space:]]*[^#[:space:]].*market-prices:sync' "${cron_file}"
}

# Check market prices sync log
check_log() {
    local logfile="$1"
    local name="$2"
    local pattern="$3"
    local minutes="$4"

    if [ ! -f "${logfile}" ]; then
        echo "WARNING: ${name} log file not found: ${logfile}"
        failures=$((failures + 1))
        return
    fi

    local recent_lines
    recent_lines=$(find "${logfile}" -mmin -${minutes} 2>/dev/null)
    if [ -z "${recent_lines}" ]; then
        echo "ALERT: ${name} has not run in the last ${minutes} minutes"
        failures=$((failures + 1))
        return
    fi

    if grep -q "${pattern}" "${logfile}"; then
        echo "OK: ${name} ran recently"
    else
        echo "ALERT: ${name} ran but did not complete successfully"
        failures=$((failures + 1))
    fi
}

# A commented-out cron line is the off switch and is healthy.
# An active line that only printed "RATIN sync is off" is also healthy:
# an older installed crontab can still invoke the command while the env
# flag is false. Do not treat either case as a failed sync.
check_market_sync() {
    local logfile="${log_dir}/mkulima-market-prices-sync.log"
    local name="Market prices sync"
    local minutes=1440

    if ! market_sync_scheduled; then
        echo "OK: ${name} is off (market-prices:sync is commented out)"
        return
    fi

    if [ ! -f "${logfile}" ]; then
        echo "ALERT: ${name} is scheduled but log file not found: ${logfile}"
        failures=$((failures + 1))
        return
    fi

    local recent_lines
    recent_lines=$(find "${logfile}" -mmin -${minutes} 2>/dev/null)
    if [ -z "${recent_lines}" ]; then
        echo "ALERT: ${name} has not run in the last ${minutes} minutes"
        failures=$((failures + 1))
        return
    fi

    # The last line is this run. An older "Sync completed" or "sync is off"
    # higher in the file must not hide a later failure.
    local last
    last=$(tail -n 30 "${logfile}" | awk 'NF { line = $0 } END { print line }')
    if printf '%s\n' "${last}" | grep -q "Sync completed"; then
        echo "OK: ${name} ran recently"
    elif printf '%s\n' "${last}" | grep -q "RATIN sync is off"; then
        echo "OK: ${name} is disabled (RATIN_SYNC_ENABLED). No prices were written."
    else
        echo "ALERT: ${name} ran but did not complete successfully"
        failures=$((failures + 1))
    fi
}

check_market_sync
check_log "${log_dir}/mkulima-weather-cache.log" "Weather cache" "Cached weather for" 1440
check_log "${log_dir}/mkulima-ssl-monitor.log" "SSL monitor" "OK:" 1440

if [ "${failures}" -gt 0 ]; then
    echo "ALERT: ${failures} Mkulima Forum cron job(s) need attention" | logger -t mkulima-cron-monitor
    if command -v mail >/dev/null 2>&1 && [ -n "${alert_email}" ]; then
        echo "${failures} Mkulima Forum cron job(s) need attention. Check logs in ${log_dir}/mkulima-*.log" | mail -s "Mkulima Forum Cron Alert" "${alert_email}"
    fi
    exit 1
fi

echo "OK: All Mkulima Forum cron jobs healthy" | logger -t mkulima-cron-monitor
exit 0

