#!/bin/bash
# SSL expiry check for Mkulima Forum
domain="mkulimaforum.app"
threshold_days=7
expiry_date=$(openssl x509 -in /etc/letsencrypt/live/${domain}/fullchain.pem -noout -enddate | cut -d= -f2)
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

