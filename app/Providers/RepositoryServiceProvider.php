<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\Form;
use App\Models\Page;
use App\Repositories\BlogPostRepository;
use App\Repositories\FormRepository;
use App\Repositories\PageRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PageRepository::class, function ($app) {
            return new PageRepository(new Page);
        });

        $this->app->singleton(BlogPostRepository::class, function ($app) {
            return new BlogPostRepository(new BlogPost);
        });

        $this->app->singleton(FormRepository::class, function ($app) {
            return new FormRepository(new Form);
        });
    }

    public function boot(): void
    {
        //
    }
}
