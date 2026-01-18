<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Test all forms for functionality
 */
class TestForms extends Command
{
    protected $signature = 'test:forms {--url=http://localhost:8000}';

    protected $description = 'Test all forms for proper functionality';

    public function handle(): int
    {
        $baseUrl = $this->option('url');
        $this->info("Testing forms on: {$baseUrl}");
        $this->newLine();

        $results = [
            'contact' => $this->testContactForm($baseUrl),
            'ebook' => $this->testEbookForm($baseUrl),
        ];

        $this->displayResults($results);

        $failed = array_filter($results, fn ($r) => ! $r['success']);

        return empty($failed) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function testContactForm(string $baseUrl): array
    {
        $this->info('Testing Contact Form...');

        try {
            // Get CSRF token
            $response = Http::get("{$baseUrl}/contact");
            $csrfToken = $this->extractCsrfToken($response->body());

            if (! $csrfToken) {
                return ['success' => false, 'error' => 'Could not extract CSRF token'];
            }

            // Test form submission
            $response = Http::asForm()
                ->withHeaders(['X-CSRF-TOKEN' => $csrfToken])
                ->post("{$baseUrl}/contact", [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone' => '+1234567890',
                    'subject' => 'Test Subject',
                    'message' => 'This is a test message with more than 10 characters.',
                ]);

            if ($response->successful()) {
                $this->line('  ✓ Contact form submission successful');

                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => "Status: {$response->status()}, Body: ".substr($response->body(), 0, 200),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function testEbookForm(string $baseUrl): array
    {
        $this->info('Testing eBook Signup Form...');

        try {
            // Get CSRF token
            $response = Http::get($baseUrl);
            $csrfToken = $this->extractCsrfToken($response->body());

            if (! $csrfToken) {
                return ['success' => false, 'error' => 'Could not extract CSRF token'];
            }

            // Test form submission
            $response = Http::asJson()
                ->withHeaders([
                    'X-CSRF-TOKEN' => $csrfToken,
                    'Accept' => 'application/json',
                ])
                ->post("{$baseUrl}/ebook-signup", [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);

            if ($response->successful()) {
                $this->line('  ✓ eBook form submission successful');

                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => "Status: {$response->status()}, Body: ".substr($response->body(), 0, 200),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function extractCsrfToken(string $html): ?string
    {
        if (preg_match('/name="csrf-token"\s+content="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('Test Results:');
        $this->newLine();

        foreach ($results as $form => $result) {
            if ($result['success']) {
                $this->line("  ✓ {$form}: PASSED");
            } else {
                $this->error("  ✗ {$form}: FAILED");
                if (isset($result['error'])) {
                    $this->line("    Error: {$result['error']}");
                }
            }
        }
    }
}
