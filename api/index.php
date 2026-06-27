<?php

/**
 * Vercel serverless entry point for Laravel.
 *
 * Vercel's filesystem is read-only at runtime (except /tmp).
 * This file bootstraps Laravel and rewrites paths that require
 * write access (views cache, logs, sessions) to /tmp.
 */

// ── Writable paths: redirect to /tmp ─────────────────────────────────────────
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

// ── Override storage path env before Laravel boots ───────────────────────────
$_ENV['APP_STORAGE_PATH'] = $tmpStorage;
$_SERVER['APP_STORAGE_PATH'] = $tmpStorage;
putenv("APP_STORAGE_PATH={$tmpStorage}");

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Redirect storage_path() to /tmp so compiled views/cache/logs are writable.
$app->useStoragePath($tmpStorage);

// ── Run migrations once per cold start ───────────────────────────────────────
// /tmp is wiped between cold starts so this runs at most once per instance.
// migrate is idempotent — it skips already-applied migrations.
if (! file_exists('/tmp/.migrated')) {
    try {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->call('migrate', ['--force' => true]);
        file_put_contents('/tmp/.migrated', date('Y-m-d H:i:s'));
    } catch (\Throwable $e) {
        error_log('[Vercel] Migration failed: ' . $e->getMessage());
    }
}

// ── Seed once: only if settings table is empty (first deploy) ────────────────
if (! file_exists('/tmp/.seeded')) {
    try {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
                $_ENV['DB_PORT'] ?? getenv('DB_PORT'),
                $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE')
            ),
            $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME'),
            $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD'),
            [PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $count = $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();

        if ((int) $count === 0) {
            $kernel ??= $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('db:seed', ['--force' => true]);
        }

        file_put_contents('/tmp/.seeded', date('Y-m-d H:i:s'));
    } catch (\Throwable $e) {
        error_log('[Vercel] Seeding failed: ' . $e->getMessage());
    }
}

// ── Handle the incoming request ───────────────────────────────────────────────
$app->handleRequest(\Illuminate\Http\Request::capture());
