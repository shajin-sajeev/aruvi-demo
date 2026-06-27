<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "App booted OK\n";
echo "HeroSlide count: " . App\Models\HeroSlide::count() . "\n";
$slides = App\Models\HeroSlide::all(['id','title','button_url','is_active','sort_order']);
foreach ($slides as $s) {
    echo "  #{$s->id} | {$s->title} | url=" . ($s->button_url ?: 'NULL') . " | active=" . ($s->is_active ? 'yes' : 'no') . "\n";
}
