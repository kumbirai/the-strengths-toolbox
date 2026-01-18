<?php

namespace App\Observers;

use App\Http\Controllers\SitemapController;
use App\Models\Page;

class PageObserver
{
    public function created(Page $page): void
    {
        SitemapController::clearCache();
    }

    public function updated(Page $page): void
    {
        SitemapController::clearCache();
    }

    public function deleted(Page $page): void
    {
        SitemapController::clearCache();
    }
}
