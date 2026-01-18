<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Test all routes for accessibility
 */
class TestRoutes extends Command
{
    protected $signature = 'test:routes';

    protected $description = 'Test all web routes for accessibility';

    public function handle(): int
    {
        $this->info('Testing all web routes...');
        $this->newLine();

        $routes = $this->getWebRoutes();
        $results = [];

        foreach ($routes as $route) {
            $results[$route['name']] = $this->testRoute($route);
        }

        $this->displayResults($results);

        $failed = array_filter($results, fn ($r) => ! $r['accessible']);

        return empty($failed) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function getWebRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            if ($route->getName() && str_starts_with($route->getName(), 'admin.') === false) {
                $routes[] = [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                ];
            }
        }

        return $routes;
    }

    protected function testRoute(array $route): array
    {
        $methods = array_filter($route['methods'], fn ($m) => $m !== 'HEAD');

        if (empty($methods)) {
            return ['accessible' => false, 'error' => 'No testable methods'];
        }

        $method = in_array('GET', $methods) ? 'GET' : $methods[0];

        // Check if route requires parameters
        if (preg_match('/\{[^}]+\}/', $route['uri'])) {
            return ['accessible' => true, 'note' => 'Requires parameters - manual test needed'];
        }

        try {
            $response = $this->call($method, $route['uri']);

            return [
                'accessible' => $response->getStatusCode() < 500,
                'status' => $response->getStatusCode(),
            ];
        } catch (\Exception $e) {
            return [
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function displayResults(array $results): void
    {
        $passed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($results as $name => $result) {
            if (isset($result['note'])) {
                $this->line("  ⚠ {$name}: SKIPPED ({$result['note']})");
                $skipped++;
            } elseif ($result['accessible']) {
                $status = isset($result['status']) ? " [{$result['status']}]" : '';
                $this->line("  ✓ {$name}: PASSED{$status}");
                $passed++;
            } else {
                $error = isset($result['error']) ? " - {$result['error']}" : '';
                $this->error("  ✗ {$name}: FAILED{$error}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Summary: {$passed} passed, {$failed} failed, {$skipped} skipped");
    }
}
