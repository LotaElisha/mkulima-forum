// Assembles dist/ after `vite build`, for a machine that has PHP and vendor/.
//
// Hostinger also runs this build from master, in a Node-only environment, as
// a static "Vite" deploy attached to mkulimaforum.com. That domain serves the
// real Laravel app. A successful static build there replaces the live API and
// admin with one HTML page, which is what happened on 2026-10-07. So without
// PHP and vendor/ this script fails on purpose, and Hostinger keeps serving
// what it already has. Do not add a fallback that lets it succeed.

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
} else {
    console.error('Refusing to build dist/ without PHP and vendor/: a static deploy would replace the live Laravel site.');
    process.exit(1);
}
