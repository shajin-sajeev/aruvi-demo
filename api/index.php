<?php

/**
 * Vercel serverless entry point for Laravel.
 *
 * Vercel's filesystem is read-only at runtime (except /tmp).
 * All writable paths (views cache, logs, sessions, config cache)
 * are redirected to /tmp which persists for the lifetime of the
 * serverless instance (wiped on cold start).
 *
 * On every cold start this file:
 *   1. Creates writable directories under /tmp
 *   2. Removes the build-time config cache (it has a dummy APP_KEY/DB)
 *   3. Boots Laravel
 *   4. Caches config using the real Vercel env vars (once per cold start)
 *   5. Runs migrate --force (idempotent — safe to run every cold start)
 *   6. Seeds the database if the settings table is empty (first deploy only)
 *   7. Handles the HTTP request
 *
 * Manual artisan endpoint (for debugging):
 *   GET /__artisan?cmd=migrate&token=YOUR_ARTISAN_TOKEN
 *   GET /__artisan?cmd=db:seed&token=YOUR_ARTISAN_TOKEN
 *   GET /__artisan?cmd=migrate:status&token=YOUR_ARTISAN_TOKEN
 */

// ── 1. Create writable directories under /tmp ─────────────────────────────────
$tmpStorage = '/tmp/storage';

foreach ([
    $tmpStorage,
    "$tmpStorage/framework",
    "$tmpStorage/framework/views",
    "$tmpStorage/framework/cache",
    "$tmpStorage/framework/cache/data",
    "$tmpStorage/framework/sessions",
    "$tmpStorage/logs",
    "$tmpStorage/app",
    "$tmpStorage/app/public",
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// ── 2. Inject storage path before Laravel boots ───────────────────────────────
$_ENV['APP_STORAGE_PATH']    = $tmpStorage;
$_SERVER['APP_STORAGE_PATH'] = $tmpStorage;
putenv("APP_STORAGE_PATH={$tmpStorage}");

// ── 3. Remove build-time config cache ────────────────────────────────────────
// The build step runs artisan with a dummy APP_KEY/SQLite DB.
// We delete that stale cache so Laravel re-reads the real Vercel env vars.
$buildConfigCache = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($buildConfigCache)) {
    @unlink($buildConfigCache);
}

// ── 4. Bootstrap Laravel ──────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Tell Laravel to use /tmp/storage for all writable paths
$app->useStoragePath($tmpStorage);

// ── 5. Manual artisan endpoint ────────────────────────────────────────────────
// Allows running artisan commands via HTTP without SSH access.
// Secured by ARTISAN_TOKEN env var — set this in the Vercel dashboard.
// Usage:
//   https://your-app.vercel.app/__artisan?cmd=migrate&token=YOUR_SECRET
//   https://your-app.vercel.app/__artisan?cmd=db:seed&token=YOUR_SECRET
//   https://your-app.vercel.app/__artisan?cmd=migrate:status&token=YOUR_SECRET
//   https://your-app.vercel.app/__artisan?cmd=migrate:fresh&seed=1&token=YOUR_SECRET
$artisanToken = $_ENV['ARTISAN_TOKEN'] ?? getenv('ARTISAN_TOKEN');
$requestUri   = $_SERVER['REQUEST_URI'] ?? '';

if (str_starts_with(parse_url($requestUri, PHP_URL_PATH) ?? '', '/__artisan')) {
    $providedToken = $_GET['token'] ?? '';

    // Token is required and must match
    if (empty($artisanToken) || ! hash_equals((string) $artisanToken, (string) $providedToken)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden. Provide a valid ?token= parameter.']);
        exit;
    }

    $allowedCommands = [
        'migrate',
        'migrate:status',
        'migrate:fresh',
        'migrate:rollback',
        'db:seed',
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'optimize:clear',
    ];

    $cmd = $_GET['cmd'] ?? 'migrate:status';

    if (! in_array($cmd, $allowedCommands, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error'    => "Command '{$cmd}' is not allowed.",
            'allowed'  => $allowedCommands,
        ]);
        exit;
    }

    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

    ob_start();

    $args = ['--force' => true, '--ansi' => false];

    // If ?seed=1 is passed alongside migrate:fresh, also seed
    if ($cmd === 'migrate:fresh' && ! empty($_GET['seed'])) {
        $args['--seed'] = true;
    }
    // db:seed uses DatabaseSeeder by default; optionally pass ?class=
    if ($cmd === 'db:seed' && ! empty($_GET['class'])) {
        $args['--class'] = $_GET['class'];
    }

    try {
        $exitCode = $kernel->call($cmd, $args);
        $output   = ob_get_clean();
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'command'   => $cmd,
            'exit_code' => $exitCode,
            'output'    => $output,
        ], JSON_PRETTY_PRINT);
    } catch (\Throwable $e) {
        $output = ob_get_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'command' => $cmd,
            'error'   => $e->getMessage(),
            'output'  => $output,
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// ── 6. Cold-start: cache config + run migrate ─────────────────────────────────
// /tmp is wiped between cold starts, so /tmp/.booted won't exist on a fresh instance.
// migrate is fully idempotent — it only runs pending migrations.
if (! file_exists('/tmp/.booted')) {
    try {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

        // Cache config with the real Vercel env vars now that /tmp is writable
        $kernel->call('config:cache');

        // Run all pending migrations (safe to run on every cold start)
        $kernel->call('migrate', ['--force' => true]);

        file_put_contents('/tmp/.booted', date('Y-m-d H:i:s'));
    } catch (\Throwable $e) {
        error_log('[Vercel] Boot (migrate) failed: ' . $e->getMessage());
    }
}

// ── 7. Seed once — only when settings table is empty (first deploy) ───────────
// Uses the Laravel kernel instead of raw PDO so there's no duplicate connection logic.
// The /tmp/.seeded flag prevents re-seeding on every cold start.
if (! file_exists('/tmp/.seeded')) {
    try {
        $kernel ??= $app->make(\Illuminate\Contracts\Console\Kernel::class);

        // Check if already seeded by querying settings count via Eloquent
        $settingsCount = \Illuminate\Support\Facades\DB::table('settings')->count();

        if ($settingsCount === 0) {
            $kernel->call('db:seed', [
                '--class' => 'DatabaseSeeder',
                '--force' => true,
            ]);
            error_log('[Vercel] Database seeded successfully.');
        } else {
            error_log('[Vercel] Seeding skipped — settings table already has data.');
        }

        file_put_contents('/tmp/.seeded', date('Y-m-d H:i:s'));
    } catch (\Throwable $e) {
        error_log('[Vercel] Seeding failed: ' . $e->getMessage());
    }
}

// ── 8. Handle the HTTP request ────────────────────────────────────────────────
$app->handleRequest(\Illuminate\Http\Request::capture());
