<?php

/**
 * Vercel serverless entry point for Laravel.
 *
 * Vercel's filesystem is read-only except /tmp.
 * This file:
 *   1. Creates writable dirs under /tmp/storage
 *   2. Boots Laravel with /tmp/storage as the storage path
 *   3. On cold start: runs migrate (idempotent) and seeds if empty
 *   4. Handles the HTTP request normally
 *
 * Manual artisan endpoint (secured by ARTISAN_TOKEN env var):
 *   https://your-app.vercel.app/__artisan?cmd=migrate&token=SECRET
 *   https://your-app.vercel.app/__artisan?cmd=db:seed&token=SECRET
 *   https://your-app.vercel.app/__artisan?cmd=migrate:status&token=SECRET
 */

// ── 1. Writable dirs in /tmp ──────────────────────────────────────────────────
$tmpStorage = '/tmp/storage';

foreach ([
    "$tmpStorage/framework/views",
    "$tmpStorage/framework/cache/data",
    "$tmpStorage/framework/sessions",
    "$tmpStorage/logs",
    "$tmpStorage/app/public",
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// ── 2. Tell Laravel to use /tmp/storage before anything loads ─────────────────
putenv("APP_STORAGE_PATH=$tmpStorage");
$_ENV['APP_STORAGE_PATH']    = $tmpStorage;
$_SERVER['APP_STORAGE_PATH'] = $tmpStorage;

// Nuke any stale bootstrap/cache/*.php (config, routes, events, services, packages)
// These are read-only on Vercel and may have been built with wrong values.
foreach (glob(__DIR__ . '/../bootstrap/cache/*.php') as $f) {
    @unlink($f);
}

// ── 3. Bootstrap Laravel ──────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($tmpStorage);

// ── 4. Manual artisan endpoint ────────────────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

if ($uri === '/__artisan') {
    $token    = $_ENV['ARTISAN_TOKEN'] ?? getenv('ARTISAN_TOKEN') ?? '';
    $provided = $_GET['token'] ?? '';

    if (empty($token) || ! hash_equals($token, $provided)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden. Set ARTISAN_TOKEN env var and pass ?token=']);
        exit;
    }

    $allowed = [
        'migrate', 'migrate:status', 'migrate:fresh',
        'migrate:rollback', 'db:seed',
        'cache:clear', 'config:clear', 'route:clear',
        'view:clear', 'optimize:clear',
    ];

    $cmd = $_GET['cmd'] ?? 'migrate:status';

    if (! in_array($cmd, $allowed, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => "Not allowed: $cmd", 'allowed' => $allowed]);
        exit;
    }

    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    ob_start();
    $args = ['--force' => true, '--ansi' => false];
    if ($cmd === 'migrate:fresh' && ! empty($_GET['seed'])) {
        $args['--seed'] = true;
    }
    if ($cmd === 'db:seed' && ! empty($_GET['class'])) {
        $args['--class'] = $_GET['class'];
    }
    try {
        $exit   = $kernel->call($cmd, $args);
        $output = ob_get_clean();
        http_response_code($exit === 0 ? 200 : 500);
    } catch (\Throwable $e) {
        $output = ob_get_clean() . "\nERROR: " . $e->getMessage();
        http_response_code(500);
    }
    header('Content-Type: application/json');
    echo json_encode(['cmd' => $cmd, 'output' => $output], JSON_PRETTY_PRINT);
    exit;
}

// ── 5. Cold-start: migrate + seed (runs once per serverless instance) ─────────
if (! file_exists('/tmp/.booted')) {
    try {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->call('migrate', ['--force' => true]);
        file_put_contents('/tmp/.booted', date('c'));
    } catch (\Throwable $e) {
        error_log('[Vercel migrate] ' . $e->getMessage());
    }
}

if (! file_exists('/tmp/.seeded')) {
    try {
        $seeded = \Illuminate\Support\Facades\DB::table('settings')->exists();
        if (! $seeded) {
            $kernel ??= $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        }
        file_put_contents('/tmp/.seeded', date('c'));
    } catch (\Throwable $e) {
        error_log('[Vercel seed] ' . $e->getMessage());
    }
}

// ── 6. Handle the request ─────────────────────────────────────────────────────
$app->handleRequest(\Illuminate\Http\Request::capture());
