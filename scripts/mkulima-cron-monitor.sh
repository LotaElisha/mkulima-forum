#!/bin/bash
# Mkulima Forum cron health monitor
log_dir="/var/log"
alert_email="${ALERT_EMAIL:-}"
failures=0

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

check_log "${log_dir}/mkulima-market-prices-sync.log" "Market prices sync" "Sync completed" 1440
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

