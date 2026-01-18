<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheViews extends Command
{
    protected $signature = 'view:cache-production';

    protected $description = 'Cache views for production';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->warn('View caching should only be used in production!');
            if (! $this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info('Caching views...');
        $this->call('view:cache');
        $this->info('Views cached successfully!');

        return 0;
    }
}
