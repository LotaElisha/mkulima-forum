# VPS deploy files

These are the Nginx, supervisor, and cron files for the Ubuntu VPS. They are
not installed by themselves. The steps, including Postgres, PHP 8.4, mail,
and `APP_DEBUG=false`, are in [`DEPLOYMENT_RUNBOOK.md`](../DEPLOYMENT_RUNBOOK.md).

| File | Installed as |
|---|---|
| `nginx/mkulimaforum.conf` | `/etc/nginx/sites-available/mkulimaforum.conf`. `/admin` is rewritten to `index.php` so a leftover `public/admin` directory cannot 403 the Filament panel. |
| `supervisor/mkulima-queue.conf` | `/etc/supervisor/conf.d/mkulima-queue.conf` |
| `cron/mkulima-forum` | `/etc/cron.d/mkulima-forum` |
