<?php

namespace App\Observers;

use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;

class BlogPostObserver
{
    public function created(BlogPost $post): void
    {
        SitemapController::clearCache();
    }

    public function updated(BlogPost $post): void
    {
        SitemapController::clearCache();
    }

    public function deleted(BlogPost $post): void
    {
        SitemapController::clearCache();
    }
}
