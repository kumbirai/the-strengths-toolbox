<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestCoreWebVitals extends Command
{
    protected $signature = 'performance:test-web-vitals {url?}';

    protected $description = 'Test Core Web Vitals using PageSpeed Insights API';

    public function handle(): int
    {
        $url = $this->argument('url') ?? config('app.url');

        $this->info("Testing Core Web Vitals for: {$url}");
        $this->newLine();

        // Note: Requires Google PageSpeed Insights API key
        $apiKey = config('services.google.pagespeed_api_key');

        if (! $apiKey) {
            $this->warn('Google PageSpeed Insights API key not configured.');
            $this->info('Set GOOGLE_PAGESPEED_API_KEY in .env');
            $this->info('Or test manually at: https://pagespeed.web.dev/');

            return 1;
        }

        try {
            $response = Http::get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
                'url' => $url,
                'key' => $apiKey,
                'category' => 'performance',
            ]);

            if (! $response->successful()) {
                $this->error('Failed to fetch PageSpeed data');

                return 1;
            }

            $data = $response->json();
            $this->displayResults($data);

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");

            return 1;
        }
    }

    protected function displayResults(array $data): void
    {
        $metrics = $data['lighthouseResult']['audits'] ?? [];

        // LCP
        $lcp = $metrics['largest-contentful-paint'] ?? null;
        if ($lcp) {
            $lcpValue = $lcp['numericValue'] / 1000; // Convert to seconds
            $status = $lcpValue < 2.5 ? '✓' : '✗';
            $this->line("{$status} LCP (Largest Contentful Paint): {$lcpValue}s (Target: < 2.5s)");
        }

        // FID
        $fid = $metrics['max-potential-fid'] ?? null;
        if ($fid) {
            $fidValue = $fid['numericValue'];
            $status = $fidValue < 100 ? '✓' : '✗';
            $this->line("{$status} FID (First Input Delay): {$fidValue}ms (Target: < 100ms)");
        }

        // CLS
        $cls = $metrics['cumulative-layout-shift'] ?? null;
        if ($cls) {
            $clsValue = $cls['numericValue'];
            $status = $clsValue < 0.1 ? '✓' : '✗';
            $this->line("{$status} CLS (Cumulative Layout Shift): {$clsValue} (Target: < 0.1)");
        }

        $this->newLine();
        $this->info('Full report: '.($data['lighthouseResult']['finalUrl'] ?? ''));
    }
}
