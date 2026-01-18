<?php

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate and cache the XML sitemap';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        // Clear existing cache
        SitemapController::clearCache();

        // Generate new sitemap (this will cache it)
        $controller = app(SitemapController::class);
        $sitemap = $controller->index();

        $this->info('Sitemap generated successfully!');
        $this->info('Access at: '.route('sitemap'));

        return 0;
    }
}
