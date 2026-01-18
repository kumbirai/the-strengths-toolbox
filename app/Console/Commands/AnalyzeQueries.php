<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeQueries extends Command
{
    protected $signature = 'db:analyze-queries';

    protected $description = 'Analyze database queries for optimization opportunities';

    public function handle(): int
    {
        $this->info('Analyzing database queries...');

        // Enable query logging
        DB::enableQueryLog();

        // Simulate common page loads
        $this->info('Simulating homepage load...');
        try {
            app(\App\Http\Controllers\Web\HomeController::class)->index();
        } catch (\Exception $e) {
            $this->warn("Homepage simulation failed: {$e->getMessage()}");
        }

        $this->info('Simulating blog listing...');
        try {
            app(\App\Http\Controllers\Web\BlogController::class)->index(request());
        } catch (\Exception $e) {
            $this->warn("Blog listing simulation failed: {$e->getMessage()}");
        }

        $this->info('Simulating page load...');
        try {
            $pageController = app(\App\Http\Controllers\Web\PageController::class);
            $pageController->show(request(), 'about-us');
        } catch (\Exception $e) {
            $this->warn("Page load simulation failed: {$e->getMessage()}");
        }

        // Get queries
        $queries = DB::getQueryLog();

        $this->newLine();
        $this->info('Total queries: '.count($queries));
        $this->newLine();

        // Analyze for N+1 problems
        $this->analyzeNPlusOne($queries);

        // Show slow queries
        $this->showSlowQueries($queries);

        return 0;
    }

    protected function analyzeNPlusOne(array $queries): void
    {
        $this->info('Analyzing for N+1 query problems...');

        // Group queries by table
        $queriesByTable = [];
        foreach ($queries as $query) {
            if (preg_match('/from `?(\w+)`?/i', $query['query'], $matches)) {
                $table = $matches[1];
                $queriesByTable[$table] = ($queriesByTable[$table] ?? 0) + 1;
            }
        }

        // Find tables with many queries (potential N+1)
        foreach ($queriesByTable as $table => $count) {
            if ($count > 10) {
                $this->warn("Potential N+1 problem: {$table} queried {$count} times");
            }
        }
    }

    protected function showSlowQueries(array $queries): void
    {
        $this->info('Slow queries (>100ms):');

        $slowQueries = array_filter($queries, fn ($q) => $q['time'] > 100);

        if (empty($slowQueries)) {
            $this->info('No slow queries found!');

            return;
        }

        foreach ($slowQueries as $query) {
            $this->line("Time: {$query['time']}ms");
            $this->line("Query: {$query['query']}");
            $this->newLine();
        }
    }
}
