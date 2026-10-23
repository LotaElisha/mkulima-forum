# Mkulima Forum — VPS deployment runbook

Production runs on an Ubuntu VPS with PostgreSQL. It does not run on Hostinger
Web or Cloud hosting. Those plans have MySQL and no supervised worker.
`mkulimaforum.com` may still answer from Hostinger until DNS is moved. Do not
deploy this application onto that Hostinger account.

The files in `deploy/` match the steps below. Redis is not required yet.
Queue and cache stay on Postgres until Redis is installed and answering.

## Target

| | |
|---|---|
| OS | Ubuntu 24.04 |
| PHP | 8.4 FPM (8.3 is the minimum Laravel accepts; CI runs 8.4) |
| Database | PostgreSQL 16, database `mkulima_forum`, user `mkulima` |
| Web server | Nginx, site root `.../public` |
| Queue | `QUEUE_CONNECTION=database`, one supervised `queue:work` process |
| Scheduler | cron `schedule:run` every minute |
| App URL | `https://mkulimaforum.com` |
| Debug | `APP_DEBUG=false` |
| Project root | `/opt/data/projects/mkulima-forum` |

The previously documented host is `147.79.115.194`. Confirm it is the VPS
you intend to serve, and that it is not the Hostinger edge currently answering
the domain, before you change DNS.

## 1. Install packages

```bash
sudo apt update
sudo apt install -y nginx postgresql postgresql-contrib php8.4-fpm php8.4-cli \
  php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath \
  php8.4-intl php8.4-readline unzip supervisor certbot python3-certbot-nginx git
sudo update-alternatives --set php /usr/bin/php8.4
php -v   # must print PHP 8.4
```

Confirm the FPM socket matches `deploy/nginx/mkulimaforum.conf`:

```bash
ls /run/php/php8.4-fpm.sock
```

## 2. Create the database

```bash
sudo -u postgres psql <<'SQL'
CREATE USER mkulima WITH PASSWORD 'replace-with-a-long-password' CREATEDB;
CREATE DATABASE mkulima_forum OWNER mkulima;
SQL
```

`CREATEDB` is only so `scripts/verify-postgres-backup.sh` can build a side
database named `mkulima_forum_restore_check` and drop it. The script refuses
to drop `mkulima_forum`.

Local and CI tests use a second database, `mkulima_test`, user `mkulima`,
password `secret`. Do not point `.env` at `mkulima_test`.

## 3. Put the code on the server

```bash
sudo mkdir -p /opt/data/projects
sudo git clone https://github.com/LotaElisha/mkulima-forum.git /opt/data/projects/mkulima-forum
cd /opt/data/projects/mkulima-forum
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mkulimaforum.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mkulima_forum
DB_USERNAME=mkulima
DB_PASSWORD=replace-with-a-long-password

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=hello@mkulimaforum.com
MAIL_PASSWORD=the-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@mkulimaforum.com
MAIL_FROM_NAME=MkulimaForum

LOG_LEVEL=error
```

`MAIL_PASSWORD` is a Gmail app password, not the mailbox password. An empty
password does not raise an error; mail is simply never delivered.
`mkulima:preflight` fails the deploy while it is empty.

Leave `QUEUE_CONNECTION=database`. Do not set `redis` until `redis-cli ping`
prints `PONG`.

```bash
php artisan migrate --force
php artisan db:seed --class=TenantSeeder --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

Set `ADMIN_EMAIL` and a 12+ character `ADMIN_PASSWORD`, then:

```bash
php artisan db:seed --class=AdminUserSeeder --force
```

Count what is actually in the database. This command only prints counts:

```bash
php artisan mkulima:census
php artisan mkulima:census --json
```

## 4. Gate the deploy

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan mkulima:preflight
```

Preflight must exit 0 before Nginx is pointed at the app and before DNS
moves. It checks PostgreSQL, `APP_DEBUG=false`, `APP_URL`, mail, the queue
tables, and the webhook secrets. It cannot see supervisor. After the worker
is started, run it again.

`SESSION_SECURE_COOKIE` is read by Laravel as `session.secure`. Set it before
`config:cache`.

## 5. Nginx, TLS, supervisor, cron

```bash
# If Certbot has already added a `listen 443` server to the installed file,
# do not overwrite that file with this HTTP-only copy. Add the same
# `location ^~ /admin { rewrite ^ /index.php last; }` block to the TLS
# server. A leftover `public/admin` directory makes `/admin` return 403
# until that block is in the server that answers the request.
sudo rm -rf /opt/data/projects/mkulima-forum/public/admin
sudo cp deploy/nginx/mkulimaforum.conf /etc/nginx/sites-available/mkulimaforum.conf
sudo ln -sfn /etc/nginx/sites-available/mkulimaforum.conf /etc/nginx/sites-enabled/mkulimaforum.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

sudo certbot --nginx -d mkulimaforum.com -d www.mkulimaforum.com
sudo systemctl enable --now certbot.timer

sudo cp deploy/supervisor/mkulima-queue.conf /etc/supervisor/conf.d/mkulima-queue.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mkulima-queue
sudo supervisorctl status mkulima-queue

sudo cp deploy/cron/mkulima-forum /etc/cron.d/mkulima-forum
sudo chown root:root /etc/cron.d/mkulima-forum
sudo chmod 644 /etc/cron.d/mkulima-forum
```

The worker command is `queue:work database`. After every deploy that changes
PHP code:

```bash
php artisan queue:restart
```

Supervisor restarts the process within the hour as well (`--max-time=3600`).

## 6. Backup and a proven restore

```bash
sudo mkdir -p /opt/backups/mkulima-forum
sudo ENV_FILE=/opt/data/projects/mkulima-forum/.env \
  BACKUP_DIR=/opt/backups/mkulima-forum \
  /opt/data/projects/mkulima-forum/scripts/backup-mkulima-db.sh

sudo ENV_FILE=/opt/data/projects/mkulima-forum/.env \
  BACKUP_DIR=/opt/backups/mkulima-forum \
  /opt/data/projects/mkulima-forum/scripts/verify-postgres-backup.sh
```

The verify script restores `latest.sql.gz` into `mkulima_forum_restore_check`,
compares counts with the manifest, and drops the side database. Cron runs the
backup at 04:00 UTC and the verify at 04:30 UTC.

Dumps are `mkulima_forum_YYYYMMDD_HHMMSS.sql.gz` plus `.manifest` and
`.sha256`. Seven dumps are kept.

## 7. Move DNS off Hostinger

Only after preflight is green, the worker is running, and a restore check has
passed on the VPS:

1. On the current Hostinger site, if it is still the database you care about,
   count the rows before you change anything. This branch will not connect to
   MySQL. Use the checkout that is running there, or the database client:
   users, products, orders, forum threads and replies, market prices, farms,
   wallets, wallet transactions, escrows, disease scans.
2. If those counts are not zero, load that data into `mkulima_forum` before
   changing DNS. `scripts/backup-mkulima-db.sh` only dumps PostgreSQL.
3. Lower the DNS TTL, then point `mkulimaforum.com` and `www` at the VPS.
4. `curl -sI https://mkulimaforum.com/api/health` should show the VPS PHP, not
   the Hostinger `panel: hpanel` header.
5. Run `bash scripts/smoke.sh https://mkulimaforum.com`.
6. Run `php artisan mkulima:census` again and keep the output. Phase 1 uses
   those numbers.

Staff admin is Filament at `/admin`. Do not deploy a React build into
`public/admin`: that directory is served as static files and would hide the
panel. After `composer install`, publish Filament's assets and run migrations:

```bash
php artisan migrate --force
php artisan filament:assets
```

The React app under `admin-dashboard/` is not the live panel. Its Vite build
writes `admin-dashboard/dist`, which is gitignored and is not the document root.

Do not advertise `https://mkulimaforum.com/app/mkulima-forum.apk`. `/download`
links an APK only when a real zip-based APK is on disk.

## Scheduler and monitors

| When (UTC) | What |
|---|---|
| Every minute | `php artisan schedule:run` |
| 04:00 | `scripts/backup-mkulima-db.sh` |
| 04:30 | `scripts/verify-postgres-backup.sh` |
| 06:00 | `market-prices:sync` (no-ops unless `RATIN_SYNC_ENABLED=true`) |
| 07:00 | `weather:cache` |
| 08:00 | `ssl-monitor-mkulima.sh` |
| 09:00 | `mkulima-cron-monitor.sh` |
| Every 5 min | `mkulima-uptime-monitor.sh` (weather report skips Gemini unless `WEATHER_ADVISORY_AI=true`) |

Uptime and SSL checks read `APP_URL` from
`/opt/data/projects/mkulima-forum/.env`. Staging
(`https://www.mkulimaforum.app`) and production
(`https://mkulimaforum.com`) use the same cron file. Re-copy
`deploy/cron/mkulima-forum` after this change so cron stops calling the old
copies in `/usr/local/bin`, which still had `.com` written into them.
`MONITOR_BASE_URL` overrides the uptime host. `MONITOR_DOMAIN` overrides the
certificate directory name when it is not the `APP_URL` host (a `www` host
also tries the apex certificate).

## Redis, later

When Redis is installed and `redis-cli ping` prints `PONG`, you may set
`CACHE_STORE=redis` and `QUEUE_CONNECTION=redis`, change the supervisor
command from `queue:work database` to `queue:work redis`, then
`php artisan config:cache`, `supervisorctl restart mkulima-queue`, and
`php artisan mkulima:preflight`. Until that ping works, leave both on
`database`.
