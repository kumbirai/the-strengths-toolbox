<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheRoutes extends Command
{
    protected $signature = 'route:cache-production';

    protected $description = 'Cache routes for production';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->warn('Route caching should only be used in production!');
            if (! $this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info('Caching routes...');
        $this->call('route:cache');
        $this->info('Routes cached successfully!');

        return 0;
    }
}
