<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->ensureStoragePathsExist();
        $this->ensurePublicPathForRootDeployment();

        $this->app->singleton(\App\Services\SearchService::class);
        $this->app->singleton(\App\Services\SchemaService::class);
    }

    /**
     * When using root deployment (public contents in public_html/), point Laravel's public path
     * at public_html so Vite finds build/manifest.json and assets.
     */
    private function ensurePublicPathForRootDeployment(): void
    {
        $manifestInPublic = $this->app->basePath('public/build/manifest.json');
        $manifestInPublicHtml = $this->app->basePath('public_html/build/manifest.json');

        if (! is_file($manifestInPublic) && is_file($manifestInPublicHtml)) {
            $this->app->usePublicPath($this->app->basePath('public_html'));
        }

        if (($customPath = env('APP_PUBLIC_PATH')) && is_dir($customPath)) {
            $this->app->usePublicPath($customPath);
        }
    }

    /**
     * Ensure required storage directories exist (e.g. on cPanel/GoDaddy where they may be missing after deploy).
     * Prevents "Please provide a valid cache path" when storage/framework/views is absent.
     */
    private function ensureStoragePathsExist(): void
    {
        $base = $this->app->storagePath();
        $paths = [
            $base . '/framework/views',
            $base . '/framework/cache/data',
            $base . '/framework/sessions',
            $base . '/logs',
        ];
        foreach ($paths as $path) {
            if (! is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);

        // Register observers
        \App\Models\Page::observe(\App\Observers\PageObserver::class);
        \App\Models\BlogPost::observe(\App\Observers\BlogPostObserver::class);

        // Enable query logging in development
        if (app()->environment('local')) {
            \DB::listen(function ($query) {
                \Log::debug($query->sql);
                \Log::debug($query->bindings);
                \Log::debug($query->time);
            });
        }
    }
}
