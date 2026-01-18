<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Security audit command
 */
class SecurityAudit extends Command
{
    protected $signature = 'security:audit';

    protected $description = 'Perform security audit of the application';

    public function handle(): int
    {
        $this->info('Running security audit...');
        $this->newLine();

        $checks = [
            'env_file' => $this->checkEnvFile(),
            'csrf_protection' => $this->checkCsrfProtection(),
            'rate_limiting' => $this->checkRateLimiting(),
            'input_validation' => $this->checkInputValidation(),
            'error_handling' => $this->checkErrorHandling(),
            'sensitive_data' => $this->checkSensitiveData(),
        ];

        $this->displayResults($checks);

        $failed = array_filter($checks, fn ($c) => ! $c['passed']);

        return empty($failed) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function checkEnvFile(): array
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        if (! File::exists($envPath)) {
            return [
                'passed' => false,
                'message' => '.env file not found',
            ];
        }

        $envContent = File::get($envPath);

        $issues = [];

        if (str_contains($envContent, 'APP_DEBUG=true')) {
            $issues[] = 'APP_DEBUG should be false in production';
        }

        if (str_contains($envContent, 'APP_KEY=') && ! str_contains($envContent, 'APP_KEY=base64:')) {
            $issues[] = 'APP_KEY not set';
        }

        if (empty($issues)) {
            return ['passed' => true, 'message' => 'Environment file secure'];
        }

        return [
            'passed' => false,
            'message' => implode(', ', $issues),
        ];
    }

    protected function checkCsrfProtection(): array
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);

        // Check if forms have CSRF protection
        $hasCsrf = str_contains($content, '@csrf') ||
                   str_contains($content, 'csrf_token') ||
                   str_contains($content, 'X-CSRF-TOKEN');

        if ($hasCsrf) {
            return ['passed' => true, 'message' => 'CSRF protection found'];
        }

        return ['passed' => false, 'message' => 'CSRF protection not found'];
    }

    protected function checkRateLimiting(): array
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);

        $hasRateLimiting = str_contains($content, 'rate.limit.forms');

        if ($hasRateLimiting) {
            return ['passed' => true, 'message' => 'Rate limiting configured'];
        }

        return ['passed' => false, 'message' => 'Rate limiting not configured'];
    }

    protected function checkInputValidation(): array
    {
        $contactController = app_path('Http/Controllers/Web/ContactController.php');

        if (! File::exists($contactController)) {
            return ['passed' => false, 'message' => 'ContactController not found'];
        }

        $content = File::get($contactController);

        $hasValidation = str_contains($content, 'validate(') ||
                        str_contains($content, 'Request::validate');

        if ($hasValidation) {
            return ['passed' => true, 'message' => 'Input validation found'];
        }

        return ['passed' => false, 'message' => 'Input validation not found'];
    }

    protected function checkErrorHandling(): array
    {
        $handlerFile = app_path('Exceptions/Handler.php');

        if (! File::exists($handlerFile)) {
            return ['passed' => false, 'message' => 'Exception Handler not found'];
        }

        $content = File::get($handlerFile);

        $hasLogging = str_contains($content, 'Log::') ||
                     str_contains($content, 'log(');

        if ($hasLogging) {
            return ['passed' => true, 'message' => 'Error logging configured'];
        }

        return ['passed' => false, 'message' => 'Error logging not configured'];
    }

    protected function checkSensitiveData(): array
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return ['passed' => false, 'message' => '.env file not found'];
        }

        $content = File::get($envPath);

        $sensitivePatterns = [
            'password.*=.*password',
            'secret.*=.*secret',
            'key.*=.*test',
        ];

        $issues = [];
        foreach ($sensitivePatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $content)) {
                $issues[] = 'Potential default credentials found';
                break;
            }
        }

        if (empty($issues)) {
            return ['passed' => true, 'message' => 'No default credentials found'];
        }

        return [
            'passed' => false,
            'message' => implode(', ', $issues),
        ];
    }

    protected function displayResults(array $checks): void
    {
        $this->info('Security Audit Results:');
        $this->newLine();

        foreach ($checks as $check => $result) {
            $status = $result['passed'] ? '✓' : '✗';
            $color = $result['passed'] ? 'green' : 'red';

            $this->line("  {$status} ".ucfirst(str_replace('_', ' ', $check)).": {$result['message']}");
        }

        $this->newLine();

        $passed = count(array_filter($checks, fn ($c) => $c['passed']));
        $total = count($checks);

        $this->info("Summary: {$passed}/{$total} checks passed");
    }
}
