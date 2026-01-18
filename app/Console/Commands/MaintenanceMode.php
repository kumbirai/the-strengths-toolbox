<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Comprehensive maintenance mode management
 */
class MaintenanceMode extends Command
{
    protected $signature = 'maintenance:manage 
                            {action : enable|disable|status}
                            {--message= : Custom maintenance message}
                            {--retry=60 : Retry after seconds}';

    protected $description = 'Manage application maintenance mode';

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'enable' => $this->enable(),
            'disable' => $this->disable(),
            'status' => $this->status(),
            default => $this->error("Invalid action: {$action}. Use: enable, disable, or status"),
        };
    }

    protected function enable(): int
    {
        $message = $this->option('message') ?? 'We are performing scheduled maintenance. Please check back soon.';
        $retry = (int) $this->option('retry');

        Artisan::call('down', [
            '--message' => $message,
            '--retry' => $retry,
        ]);

        $this->info('Maintenance mode enabled.');
        $this->line("Message: {$message}");
        $this->line("Retry after: {$retry} seconds");

        return Command::SUCCESS;
    }

    protected function disable(): int
    {
        Artisan::call('up');

        $this->info('Maintenance mode disabled.');

        return Command::SUCCESS;
    }

    protected function status(): int
    {
        $downFile = storage_path('framework/down');

        if (file_exists($downFile)) {
            $data = json_decode(file_get_contents($downFile), true);
            $message = $data['message'] ?? 'N/A';
            $retry = $data['retry'] ?? 'N/A';
            $this->info('Maintenance mode is ENABLED');
            $this->line("Message: {$message}");
            $this->line("Retry: {$retry} seconds");
        } else {
            $this->info('Maintenance mode is DISABLED');
        }

        return Command::SUCCESS;
    }
}
