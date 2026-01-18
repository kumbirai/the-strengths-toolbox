<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheProduction extends Command
{
    protected $signature = 'cache:production';

    protected $description = 'Cache all for production (config, routes, views)';

    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->warn('This command is intended for production use!');
            if (! $this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info('Caching all for production...');
        $this->cacheService->cacheAll();
        $this->info('All caches created successfully!');

        return 0;
    }
}
