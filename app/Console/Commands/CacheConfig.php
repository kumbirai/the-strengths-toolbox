<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheConfig extends Command
{
    protected $signature = 'config:cache-production';

    protected $description = 'Cache configuration files for production';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->warn('Config caching should only be used in production!');
            if (! $this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info('Caching configuration files...');
        $this->call('config:cache');
        $this->info('Configuration cached successfully!');

        return 0;
    }
}
