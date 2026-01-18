<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backup database
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database 
                            {--path=storage/backups : Backup storage path}
                            {--compress : Compress backup file}';

    protected $description = 'Create database backup';

    public function handle(): int
    {
        $path = $this->option('path');
        $compress = $this->option('compress');

        $this->info('Creating database backup...');

        try {
            $connection = DB::connection();
            $database = config("database.connections.{$connection->getName()}.database");
            $filename = "backup_{$database}_".Carbon::now()->format('Y-m-d_His').'.sql';

            $fullPath = base_path($path);
            if (! is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $filePath = $fullPath.'/'.$filename;

            // Get database credentials
            $host = config("database.connections.{$connection->getName()}.host");
            $username = config("database.connections.{$connection->getName()}.username");
            $password = config("database.connections.{$connection->getName()}.password");

            // Create backup using mysqldump
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Compress if requested
            if ($compress) {
                $compressedPath = $filePath.'.gz';
                exec("gzip -c {$filePath} > {$compressedPath}");
                unlink($filePath);
                $filePath = $compressedPath;
                $filename .= '.gz';
            }

            $fileSize = filesize($filePath);
            $fileSizeFormatted = $this->formatBytes($fileSize);

            $this->info('✓ Backup created successfully!');
            $this->line("  File: {$filename}");
            $this->line("  Size: {$fileSizeFormatted}");
            $this->line("  Path: {$filePath}");

            // Clean old backups (keep last 7 days)
            $this->cleanOldBackups($fullPath);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function cleanOldBackups(string $path): void
    {
        $files = glob($path.'/backup_*.sql*');
        $cutoff = Carbon::now()->subDays(7);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff->timestamp) {
                unlink($file);
                $this->line('  Deleted old backup: '.basename($file));
            }
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
