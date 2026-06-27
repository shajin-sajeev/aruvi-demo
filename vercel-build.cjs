'use strict';

/**
 * Vercel build script for Laravel + Vite
 *
 * Vercel's build container runs this script during deployment.
 * The vercel-php runtime installs PHP as part of function bundling —
 * PHP may NOT be in PATH when this build script runs.
 *
 * Safe approach:
 *   1. npm ci        — install JS dependencies (Node.js only)
 *   2. vite build    — compile CSS/JS → public/build/
 *   3. composer install --no-scripts  — install PHP deps WITHOUT running
 *                                       @php artisan package:discover
 *                                       (avoids PHP-not-found errors)
 *
 * Everything that needs PHP (config:cache, package:discover, migrate, db:seed)
 * runs inside api/index.php at cold start when PHP IS available.
 */

const { existsSync, writeFileSync } = require('node:fs');
const { execFileSync } = require('node:child_process');
const { join } = require('node:path');
const https = require('node:https');
const { createWriteStream } = require('node:fs');
const { pipeline } = require('node:stream');

// ── Environment ───────────────────────────────────────────────────────────────
const env = {
  ...process.env,
  npm_config_cache: join(process.cwd(), '.npm'),
  COMPOSER_CACHE_DIR: join(process.cwd(), '.composer-cache'),
};

// npm is 'npm' on Linux (Vercel), 'npm.cmd' on Windows (local dev)
const NPM = process.platform === 'win32' ? 'npm.cmd' : 'npm';
const NPM_SHELL = process.platform === 'win32'; // .cmd files need shell:true on Windows

// ── Helpers ───────────────────────────────────────────────────────────────────
function run(cmd, args, useShell = false) {
  console.log(`\n==> ${cmd} ${args.join(' ')}`);
  execFileSync(cmd, args, { stdio: 'inherit', shell: useShell, env });
}

function download(url, dest) {
  return new Promise((resolve, reject) => {
    console.log(`\n==> Downloading ${url}`);
    const file = createWriteStream(dest);
    https.get(url, (res) => {
      if (res.statusCode !== 200) {
        file.close();
        reject(new Error(`HTTP ${res.statusCode} while downloading ${url}`));
        return;
      }
      pipeline(res, file, (err) => (err ? reject(err) : resolve()));
    }).on('error', (err) => { file.close(); reject(err); });
  });
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {

  // ── Step 1: Install Node.js / JS dependencies ─────────────────────────────
  console.log('\n[1/3] Installing JS dependencies...');
  run(NPM, existsSync('package-lock.json') ? ['ci'] : ['install'], NPM_SHELL);

  // ── Step 2: Compile Vite assets (CSS + JS → public/build/) ────────────────
  console.log('\n[2/3] Building Vite assets...');
  run(NPM, ['run', 'build'], NPM_SHELL);

  // ── Step 3: Install PHP dependencies via Composer ─────────────────────────
  console.log('\n[3/3] Installing PHP dependencies (Composer)...');

  if (!existsSync('composer.phar')) {
    await download(
      'https://getcomposer.org/download/latest-stable/composer.phar',
      'composer.phar'
    );
  } else {
    console.log('==> composer.phar already present');
  }

  // Write a minimal .env so that if PHP IS available, Composer post-install
  // scripts don't crash looking for APP_KEY. Harmless if PHP is not in PATH.
  if (!existsSync('.env')) {
    writeFileSync('.env', [
      'APP_NAME="Aruvi on the Cliff"',
      'APP_ENV=production',
      'APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
      'APP_DEBUG=false',
      'DB_CONNECTION=sqlite',
      'DB_DATABASE=/tmp/build.sqlite',
    ].join('\n'));
    console.log('==> Minimal .env written');
  }

  // --no-scripts: skip @php artisan package:discover and other post-install
  // scripts that require PHP in PATH. package:discover runs in api/index.php
  // at cold start via the optimize step.
  run('php', [
    'composer.phar', 'install',
    '--no-dev',
    '--no-scripts',
    '--optimize-autoloader',
    '--no-interaction',
    '--no-progress',
    '--prefer-dist',
  ]);

  console.log('\n✓ Build complete.\n');
}

main().catch((err) => {
  console.error(`\n✗ Build failed: ${err.message}\n`);
  process.exit(1);
});
