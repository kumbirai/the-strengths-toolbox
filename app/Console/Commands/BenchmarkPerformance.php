<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Benchmark application performance
 */
class BenchmarkPerformance extends Command
{
    protected $signature = 'benchmark:performance 
                            {--url=https://localhost:8000 : Application URL}
                            {--iterations=10 : Number of requests}';

    protected $description = 'Benchmark application performance metrics';

    public function handle(): int
    {
        $url = $this->option('url');
        $iterations = (int) $this->option('iterations');

        $this->info("Benchmarking performance: {$url}");
        $this->info("Iterations: {$iterations}");
        $this->newLine();

        $results = [
            'homepage' => $this->benchmarkRoute("{$url}/", $iterations),
            'blog' => $this->benchmarkRoute("{$url}/blog", $iterations),
            'contact' => $this->benchmarkRoute("{$url}/contact", $iterations),
            'health' => $this->benchmarkRoute("{$url}/health", $iterations),
        ];

        $this->displayResults($results);

        // Check if performance targets are met
        $targetsMet = $this->checkPerformanceTargets($results);

        return $targetsMet ? Command::SUCCESS : Command::FAILURE;
    }

    protected function benchmarkRoute(string $url, int $iterations): array
    {
        $times = [];
        $errors = 0;

        $this->line("Benchmarking: {$url}");

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $start = microtime(true);
                $response = Http::timeout(10)->get($url);
                $end = microtime(true);

                $time = ($end - $start) * 1000; // Convert to milliseconds
                $times[] = $time;

                if (! $response->successful()) {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }

        if (empty($times)) {
            return [
                'success' => false,
                'error' => 'All requests failed',
            ];
        }

        return [
            'success' => true,
            'avg_time' => array_sum($times) / count($times),
            'min_time' => min($times),
            'max_time' => max($times),
            'errors' => $errors,
            'success_rate' => (($iterations - $errors) / $iterations) * 100,
        ];
    }

    protected function checkPerformanceTargets(array $results): bool
    {
        $targetsMet = true;
        $targetTime = 3000; // 3 seconds in milliseconds

        $this->newLine();
        $this->info('Performance Targets:');
        $this->line("  Target: < {$targetTime}ms (3 seconds)");
        $this->newLine();

        foreach ($results as $route => $result) {
            if (! $result['success']) {
                $this->error("  ✗ {$route}: FAILED - {$result['error']}");
                $targetsMet = false;

                continue;
            }

            $avgTime = $result['avg_time'];
            $met = $avgTime < $targetTime;

            if ($met) {
                $this->line("  ✓ {$route}: {$avgTime}ms (PASS)");
            } else {
                $this->error("  ✗ {$route}: {$avgTime}ms (FAIL - exceeds target)");
                $targetsMet = false;
            }
        }

        return $targetsMet;
    }

    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('Benchmark Results:');
        $this->newLine();

        $headers = ['Route', 'Avg (ms)', 'Min (ms)', 'Max (ms)', 'Errors', 'Success Rate'];
        $rows = [];

        foreach ($results as $route => $result) {
            if (! $result['success']) {
                $rows[] = [
                    $route,
                    'N/A',
                    'N/A',
                    'N/A',
                    'N/A',
                    'FAILED',
                ];

                continue;
            }

            $rows[] = [
                $route,
                number_format($result['avg_time'], 2),
                number_format($result['min_time'], 2),
                number_format($result['max_time'], 2),
                $result['errors'],
                number_format($result['success_rate'], 1).'%',
            ];
        }

        $this->table($headers, $rows);
    }
}
