<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Setup production environment
 */
class SetupProduction extends Command
{
    protected $signature = 'setup:production 
                            {--force : Force overwrite existing .env}';

    protected $description = 'Setup production environment configuration';

    public function handle(): int
    {
        $this->info('Setting up production environment...');
        $this->newLine();

        // Check if .env exists
        if (File::exists(base_path('.env')) && ! $this->option('force')) {
            if (! $this->confirm('.env file already exists. Overwrite?', false)) {
                $this->info('Setup cancelled.');

                return Command::SUCCESS;
            }
        }

        // Copy example file
        $examplePath = base_path('.env.example');
        $envPath = base_path('.env');

        if (! File::exists($examplePath)) {
            $this->error('.env.example file not found!');

            return Command::FAILURE;
        }

        File::copy($examplePath, $envPath);
        $this->info('✓ Created .env file from .env.example');

        // Generate app key if not set
        $envContent = File::get($envPath);
        if (str_contains($envContent, 'APP_KEY=') && ! str_contains($envContent, 'APP_KEY=base64:')) {
            $this->call('key:generate', ['--force' => true]);
            $this->info('✓ Generated application key');
        }

        // Set production values
        $this->setProductionValues($envPath);

        $this->newLine();
        $this->info('Production environment setup complete!');
        $this->warn('Please review and update .env file with your production values:');
        $this->line('  - Database credentials');
        $this->line('  - Mail configuration');
        $this->line('  - Cache driver (Redis recommended)');
        $this->line('  - Calendly URL');
        $this->line('  - APP_URL');

        return Command::SUCCESS;
    }

    protected function setProductionValues(string $envPath): void
    {
        $envContent = File::get($envPath);

        $replacements = [
            'APP_ENV=local' => 'APP_ENV=production',
            'APP_DEBUG=true' => 'APP_DEBUG=false',
            'APP_URL=http://localhost' => 'APP_URL=https://thestrengthstoolbox.com',
            'CACHE_STORE=file' => 'CACHE_STORE=redis',
            'SESSION_DRIVER=file' => 'SESSION_DRIVER=database',
            'LOG_CHANNEL=stack' => 'LOG_CHANNEL=stack',
            'LOG_LEVEL=debug' => 'LOG_LEVEL=error',
        ];

        foreach ($replacements as $search => $replace) {
            if (str_contains($envContent, $search)) {
                $envContent = str_replace($search, $replace, $envContent);
            }
        }

        // Add production-specific settings if not present
        $productionSettings = [
            'SESSION_SECURE_COOKIE=true',
            'COOKIE_SAMESITE=lax',
        ];

        foreach ($productionSettings as $setting) {
            if (! str_contains($envContent, $setting)) {
                $envContent .= "\n{$setting}";
            }
        }

        File::put($envPath, $envContent);
        $this->info('✓ Set production environment values');
    }
}
