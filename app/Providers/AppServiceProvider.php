<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\ContactMessage;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // On Vercel the filesystem is read-only except /tmp.
        // The api/index.php entry point already calls $app->useStoragePath('/tmp/storage'),
        // but we also patch the compiled view path here for safety.
        $compiledPath = $_ENV['VIEW_COMPILED_PATH'] ?? getenv('VIEW_COMPILED_PATH') ?: null;

        if ($compiledPath) {
            $this->app['config']->set(
                'view.compiled',
                $compiledPath
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Use our custom teal pagination view everywhere
        Paginator::defaultView('vendor.pagination.aruvi');

        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::all()->pluck('value', 'key');

                // Resolve branding paths: prefer uploads/branding/ upload,
                // fall back to images/default/ if no uploaded file exists there.
                $settings = $this->resolveBrandingDefaults($settings);

                view()->share('settings', $settings);
            }
        } catch (\Throwable $e) {
            view()->share('settings', collect());
        }

        try {
            if (Schema::hasTable('contact_messages')) {
                view()->composer('layouts.admin', function ($view) {
                    $view->with('unreadMessageCount', ContactMessage::where('status', 'new')->count());
                });
            }
        } catch (\Throwable $e) {
            // Ignore bootstrap errors when the database is unavailable or not migrated yet.
        }
    }

    /**
     * For each branding key, if the stored value points to a file that no longer
     * exists (or no value is set), fall back to the corresponding default image.
     */
    private function resolveBrandingDefaults(\Illuminate\Support\Collection $settings): \Illuminate\Support\Collection
    {
        $defaults = [
            'site_logo'        => '/images/default/logo.ico',
            'admin_logo'       => '/images/default/logo.ico',
            'site_brand_image' => '/images/default/brand.png',
        ];

        foreach ($defaults as $key => $defaultPath) {
            $value = $settings->get($key, '');

            // If value is empty or the file doesn't exist on disk → use default
            if (empty($value) || !file_exists(public_path(ltrim($value, '/')))) {
                $settings->put($key, $defaultPath);
            }
        }

        return $settings;
    }
}
