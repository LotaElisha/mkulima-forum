// Assembles dist/ for the Hostinger static deploy, after `vite build`.
//
// Hostinger's Vite build runs `npm install && npm run build` and nothing else:
// no `composer install`, so there is no vendor/ and `php artisan` cannot boot.
// When PHP and vendor/ are both present (a developer machine, the VPS), the
// home page is rendered fresh with `mkulima:export-landing`. Otherwise the
// committed snapshot dist/index.html is kept as-is. Refresh that snapshot by
// running `APP_URL=https://mkulimaforum.com npm run build` locally after
// changing the home page, so the snapshot carries production URLs.

import { cpSync, existsSync, mkdirSync } from 'node:fs';
import { spawnSync } from 'node:child_process';

mkdirSync('dist', { recursive: true });
cpSync('public/build', 'dist', { recursive: true });

// The home page links these by absolute path; a static host has no Laravel to
// serve them from public/.
for (const entry of ['images', 'docs', 'favicon.ico', 'robots.txt']) {
    if (existsSync(`public/${entry}`)) {
        cpSync(`public/${entry}`, `dist/${entry}`, { recursive: true });
    }
}

const php = spawnSync('php', ['-v'], { stdio: 'ignore' });
const canRender = php.status === 0 && existsSync('vendor/autoload.php');

if (canRender) {
    const run = spawnSync('php', ['artisan', 'mkulima:export-landing', 'dist/index.html'], { stdio: 'inherit' });
    if (run.status !== 0) {
        process.exit(run.status ?? 1);
    }
} else if (existsSync('dist/index.html')) {
    console.log('PHP or vendor/ not available: keeping the committed dist/index.html snapshot.');
} else {
    console.error('dist/index.html is missing and cannot be rendered without PHP and vendor/.');
    process.exit(1);
}
