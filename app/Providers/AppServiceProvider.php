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
        $this->app->singleton(\App\Services\SearchService::class);
        $this->app->singleton(\App\Services\SchemaService::class);
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
                \Log::info($query->sql);
                \Log::info($query->bindings);
                \Log::info($query->time);
            });
        }
    }
}
