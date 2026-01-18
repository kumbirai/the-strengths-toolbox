<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ValidateRobotsTxt extends Command
{
    protected $signature = 'seo:validate-robots-txt';

    protected $description = 'Validate robots.txt file';

    public function handle(): int
    {
        $this->info('Validating robots.txt...');
        $this->newLine();

        $url = config('app.url').'/robots.txt';

        try {
            $response = Http::get($url);

            if (! $response->successful()) {
                $this->error("Failed to fetch robots.txt: HTTP {$response->status()}");

                return 1;
            }

            $content = $response->body();
            $this->info('✓ robots.txt is accessible');
            $this->newLine();
            $this->line('Content:');
            $this->line($content);
            $this->newLine();

            // Basic validation
            $errors = [];

            // Check for sitemap
            if (! str_contains($content, 'Sitemap:')) {
                $errors[] = 'Missing Sitemap declaration';
            }

            // Check for User-agent
            if (! str_contains($content, 'User-agent:')) {
                $errors[] = 'Missing User-agent declaration';
            }

            // Check for common syntax errors
            if (preg_match('/Disallow:\s*$/', $content)) {
                $errors[] = 'Found empty Disallow directive (should be Disallow: /)';
            }

            if (empty($errors)) {
                $this->info('✓ robots.txt is valid!');

                return 0;
            }

            $this->error('Found issues:');
            foreach ($errors as $error) {
                $this->line("  ✗ {$error}");
            }

            return 1;
        } catch (\Exception $e) {
            $this->error("Error validating robots.txt: {$e->getMessage()}");

            return 1;
        }
    }
}
