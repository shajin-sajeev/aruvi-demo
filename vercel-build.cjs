'use strict';

/**
 * Vercel build script for Laravel + Vite
 *
 * Execution order:
 *   1. npm ci           — install JS deps
 *   2. vite build       — compile CSS/JS assets → public/build/
 *   3. Download Composer (once, cached in .composer-cache)
 *   4. composer install --no-dev  — PHP deps → vendor/
 *   5. php artisan cache — config/routes/views cached for fast cold starts
 */

const { existsSync, writeFileSync } = require('node:fs');
const { execFileSync } = require('node:child_process');
const { join } = require('node:path');
const https = require('node:https');
const { createWriteStream } = require('node:fs');
const { pipeline } = require('node:stream');

// ── Environment passed to every child process ─────────────────────────────────
const env = {
  ...process.env,
  npm_config_cache: join(process.cwd(), '.npm'),
  COMPOSER_CACHE_DIR: join(process.cwd(), '.composer-cache'),
  // Use SQLite during build so artisan never tries to reach Neon
  APP_ENV: 'production',
  DB_CONNECTION: 'sqlite',
  DB_DATABASE: '/tmp/build-dummy.sqlite',
};

// On Vercel (Linux) the binary is 'npm'; Windows needs 'npm.cmd'
const NPM = process.platform === 'win32' ? 'npm.cmd' : 'npm';
const PHP = 'php';

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
        reject(new Error(`HTTP ${res.statusCode} downloading ${url}`));
        return;
      }
      pipeline(res, file, (err) => (err ? reject(err) : resolve()));
    }).on('error', (err) => { file.close(); reject(err); });
  });
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {

  // ── 1. JS dependencies ────────────────────────────────────────────────────
  console.log('\n[1/5] Installing JS dependencies...');
  // npm.cmd on Windows requires shell:true; on Linux 'npm' is a real binary
  const npmShell = process.platform === 'win32';
  run(NPM, existsSync('package-lock.json') ? ['ci'] : ['install'], npmShell);

  // ── 2. Vite build ─────────────────────────────────────────────────────────
  console.log('\n[2/5] Building Vite assets...');
  run(NPM, ['run', 'build'], npmShell);

  // ── 3. Download Composer ──────────────────────────────────────────────────
  console.log('\n[3/5] Setting up Composer...');
  if (!existsSync('composer.phar')) {
    await download(
      'https://getcomposer.org/download/latest-stable/composer.phar',
      'composer.phar'
    );
  } else {
    console.log('==> composer.phar already cached');
  }

  // ── 4. PHP dependencies ───────────────────────────────────────────────────
  console.log('\n[4/5] Running composer install...');
  run(PHP, [
    'composer.phar', 'install',
    '--no-dev',
    '--optimize-autoloader',
    '--no-interaction',
    '--no-progress',
    '--prefer-dist',
  ]);

  // ── 5. Laravel caches (config / routes / views / events) ─────────────────
  console.log('\n[5/5] Caching Laravel config, routes, views...');

  // Ensure a .env exists so artisan can boot (real secrets come from Vercel env vars)
  if (!existsSync('.env')) {
    const key = process.env.APP_KEY || 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=';
    writeFileSync('.env', [
      `APP_NAME="Aruvi on the Cliff"`,
      `APP_ENV=production`,
      `APP_KEY=${key}`,
      `APP_DEBUG=false`,
      `DB_CONNECTION=sqlite`,
      `DB_DATABASE=/tmp/build-dummy.sqlite`,
      `CACHE_STORE=array`,
      `SESSION_DRIVER=cookie`,
      `QUEUE_CONNECTION=sync`,
    ].join('\n'));
    console.log('==> Temporary .env written for artisan');
  }

  // NOTE: config:cache is intentionally skipped here.
  // Caching config at build time would bake in the dummy DB/APP_KEY values.
  // The real config is cached by api/index.php on first cold start using
  // the actual Vercel environment variables.
  // Routes, views, and events are safe to cache — they don't contain secrets.
  for (const cmd of ['route:cache', 'view:cache', 'event:cache']) {
    try {
      run(PHP, ['artisan', cmd]);
    } catch (e) {
      // Cache failures are non-fatal — app still boots without them
      console.warn(`==> Warning: artisan ${cmd} failed (non-fatal): ${e.message}`);
    }
  }

  console.log('\n✓ Build complete.\n');
}

main().catch((err) => {
  console.error(`\n✗ Build failed: ${err.message}\n`);
  process.exit(1);
});
