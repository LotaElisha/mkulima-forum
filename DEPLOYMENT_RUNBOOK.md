# Mkulima Forum — Production Deployment Runbook

## Server
- **Host:** `147.79.115.194` (srv1772095.hstgr.cloud)
- **OS:** Ubuntu 24.04
- **Domains:** `mkulimaforum.app`, `www.mkulimaforum.app`
- **Project root:** `/opt/data/projects/mkulima-forum`
- **Web root:** `/opt/data/projects/mkulima-forum/public`
- **Admin dashboard:** `/opt/data/projects/mkulima-forum/admin-dashboard/dist`
- **PHP:** 8.4 FPM
- **Database:** PostgreSQL 16 (`mkulima_forum`)
- **Web server:** Nginx

## Admin access
- **Admin dashboard:** `https://mkulimaforum.app/admin/`
- **Admin email:** `admin@mkulima.forum`
- **Admin password:** *(stored securely, changed from default)*
- **Mobile APK:** `https://mkulimaforum.app/app/mkulima-forum.apk`

## Daily cron jobs (UTC)
| Time | Task | Log |
|---|---|---|
| 04:00 | PostgreSQL backup (`backup-mkulima-db.sh`) | `/var/log/mkulima-db-backup.log` |
| 06:00 | Market prices sync from RATIN (`market-prices:sync`) | `/var/log/mkulima-market-prices-sync.log` |
| 07:00 | Weather cache refresh (`weather:cache`) | `/var/log/mkulima-weather-cache.log` |
| 08:00 | SSL certificate expiry monitor (`ssl-monitor-mkulima.sh`) | `/var/log/mkulima-ssl-monitor.log` |
| 09:00 | Cron health monitor (`mkulima-cron-monitor.sh`) | `/var/log/mkulima-cron-monitor.log` |
| Every 5 min | Uptime endpoint monitor (`mkulima-uptime-monitor.sh`) | `/var/log/mkulima-uptime.log` |

## Monitoring scripts
- `/usr/local/bin/backup-mkulima-db.sh`
- `/usr/local/bin/ssl-monitor-mkulima.sh`
- `/usr/local/bin/mkulima-cron-monitor.sh`
- `/usr/local/bin/mkulima-uptime-monitor.sh`

## Backup retention
- Location: `/opt/backups/mkulima-forum/`
- Retention: 7 daily backups
- Format: `mkulima_forum_YYYYMMDD_HHMMSS.sql.gz`

## Useful commands
```bash
# SSH to server
ssh root@147.79.115.194

# Check application status
cd /opt/data/projects/mkulima-forum
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Run smoke tests
bash scripts/smoke.sh https://mkulimaforum.app

# Re-sync market prices manually
php artisan market-prices:sync --country=Tanzania

# Refresh weather cache manually
php artisan weather:cache

# Backup database manually
/usr/local/bin/backup-mkulima-db.sh

# Reload Nginx
nginx -t && systemctl reload nginx

# Check logs
tail -f /var/log/mkulima-uptime.log
tail -f /var/log/mkulima-market-prices-sync.log
tail -f /var/log/mkulima-weather-cache.log
```

## Mobile app build
```bash
cd /opt/data/projects/mkulima-forum
bash scripts/build-mobile.sh
# Or with custom URL:
API_URL=https://mkulimaforum.app/api bash scripts/build-mobile.sh
```

## Admin dashboard build
```bash
cd /opt/data/projects/mkulima-forum/admin-dashboard
echo "VITE_API_URL=https://mkulimaforum.app/api" > .env.production
npm install
npm run build
```

## External integrations
- **Market prices:** RATIN / EAGC (`https://ratin.net/ratinapp/api`)
- **Weather:** Open-Meteo (`https://api.open-meteo.com`)
- **Exchange rate:** `RATIN_USD_TO_TZS_RATE=2600` in `.env`

## Git repository
- `https://github.com/LotaElisha/mkulima-forum`
- Primary branch: `master`

## Security notes
- Admin password changed from default.
- SSL certificate managed by Certbot (auto-renewal enabled via systemd timer).
- PostgreSQL backups retained for 7 days.
- Uptime monitoring runs every 5 minutes.
