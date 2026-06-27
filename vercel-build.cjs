'use strict';

/**
 * Vercel build script for Laravel + Vite
 *
 * Vercel's build container has Node.js but NO PHP in PATH.
 * PHP is only available inside the serverless function at runtime.
 *
 * Therefore this script only does two things:
 *   1. npm ci       — install JS devDependencies
 *   2. vite build   — compile CSS/JS → public/build/
 *
 * vendor/ is committed to the repository so no composer install is needed.
 * All PHP work (package:discover, config:cache, migrate, db:seed) happens
 * inside api/index.php on the first cold start.
 */

const { existsSync } = require('node:fs');
const { execFileSync } = require('node:child_process');
const { join } = require('node:path');

const env = {
  ...process.env,
  npm_config_cache: join(process.cwd(), '.npm'),
};

// npm is 'npm' on Linux (Vercel); 'npm.cmd' on Windows (local)
const NPM = process.platform === 'win32' ? 'npm.cmd' : 'npm';
// .cmd files on Windows require shell:true
const NPM_SHELL = process.platform === 'win32';

function run(cmd, args, useShell = false) {
  console.log(`\n==> ${cmd} ${args.join(' ')}`);
  execFileSync(cmd, args, { stdio: 'inherit', shell: useShell, env });
}

// ── Step 1: Install JS dependencies ──────────────────────────────────────────
console.log('\n[1/2] Installing JS dependencies...');
run(NPM, existsSync('package-lock.json') ? ['ci'] : ['install'], NPM_SHELL);

// ── Step 2: Build Vite assets ─────────────────────────────────────────────────
console.log('\n[2/2] Building Vite assets...');
run(NPM, ['run', 'build'], NPM_SHELL);

console.log('\n✓ Build complete.\n');
