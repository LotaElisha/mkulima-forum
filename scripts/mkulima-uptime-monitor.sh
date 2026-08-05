#!/bin/bash
# Uptime / heartbeat monitor for Mkulima Forum

URLS=(
    "https://mkulimaforum.app/api/health"
    "https://mkulimaforum.app/"
    "https://mkulimaforum.app/admin/"
    "https://mkulimaforum.app/api/market-prices"
    "https://mkulimaforum.app/api/weather/report?location=Dodoma"
)

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
