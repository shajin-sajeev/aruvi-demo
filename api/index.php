<?php

// ── Show ALL errors so we can see what is breaking on Vercel ─────────────────
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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

// ── 2. Tell Laravel to use /tmp/storage ──────────────────────────────────────
putenv("APP_STORAGE_PATH=$tmpStorage");
$_ENV['APP_STORAGE_PATH']    = $tmpStorage;
$_SERVER['APP_STORAGE_PATH'] = $tmpStorage;

// Nuke any stale bootstrap/cache php files
foreach (glob(__DIR__ . '/../bootstrap/cache/*.php') ?: [] as $f) {
    @unlink($f);
}

// ── 3. Debug: dump key env vars so we can see what Vercel has injected ────────
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

if ($uri === '/__debug') {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version'    => PHP_VERSION,
        'APP_ENV'        => getenv('APP_ENV'),
        'APP_KEY_set'    => ! empty(getenv('APP_KEY')),
        'APP_KEY_prefix' => substr((string) getenv('APP_KEY'), 0, 7),
        'DB_CONNECTION'  => getenv('DB_CONNECTION'),
        'DB_HOST'        => getenv('DB_HOST') ?: getenv('PGHOST'),
        'DB_DATABASE'    => getenv('DB_DATABASE') ?: getenv('PGDATABASE'),
        'DB_USERNAME'    => getenv('DB_USERNAME') ?: getenv('PGUSER'),
        'DB_PASSWORD_set'=> ! empty(getenv('DB_PASSWORD') ?: getenv('PGPASSWORD')),
        'DATABASE_URL_set'=> ! empty(getenv('DATABASE_URL')),
        'VIEW_COMPILED_PATH' => getenv('VIEW_COMPILED_PATH'),
        'APP_STORAGE_PATH'   => getenv('APP_STORAGE_PATH'),
        'tmp_dirs_writable'  => [
            '/tmp'                              => is_writable('/tmp'),
            '/tmp/storage/framework/views'      => is_dir("$tmpStorage/framework/views"),
            '/tmp/storage/framework/cache/data' => is_dir("$tmpStorage/framework/cache/data"),
        ],
        'bootstrap_cache_files' => array_map('basename', glob(__DIR__ . '/../bootstrap/cache/*.php') ?: []),
        'vendor_autoload_exists' => file_exists(__DIR__ . '/../vendor/autoload.php'),
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── 4. Bootstrap Laravel ──────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($tmpStorage);

// ── 5. Manual artisan endpoint ────────────────────────────────────────────────
if ($uri === '/__artisan') {
    $token    = (string) (getenv('ARTISAN_TOKEN') ?: '');
    $provided = (string) ($_GET['token'] ?? '');

    if ($token === '' || ! hash_equals($token, $provided)) {
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
        $output = ob_get_clean() . "\nEXCEPTION: " . $e->getMessage()
            . "\n" . $e->getFile() . ':' . $e->getLine()
            . "\n" . $e->getTraceAsString();
        http_response_code(500);
    }
    header('Content-Type: application/json');
    echo json_encode(['cmd' => $cmd, 'output' => $output], JSON_PRETTY_PRINT);
    exit;
}

// ── 6. Cold-start: migrate + seed ────────────────────────────────────────────
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

// ── 7. Handle the request ─────────────────────────────────────────────────────
$app->handleRequest(\Illuminate\Http\Request::capture());
