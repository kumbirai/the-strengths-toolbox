<?php
// One-time: run database seeders (no SSH). Requires RUN_DEPLOY=1 in .env. DELETE after use.
$debugLog = __DIR__ . '/run-migrate-debug.log';
file_put_contents($debugLog, date('c') . " do-seed.php started\n", FILE_APPEND);

$envPath = dirname(__DIR__) . '/.env';
$allowRun = false;
if (file_exists($envPath) && is_readable($envPath)) {
    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = $lines ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (preg_match('/^RUN_DEPLOY\s*=\s*(.+)$/', $line, $m)) {
            $v = strtolower(trim($m[1], " \t\"'"));
            if ($v === '1' || $v === 'true' || $v === 'on') $allowRun = true;
            break;
        }
    }
}

if (!$allowRun) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Forbidden. Add RUN_DEPLOY=1 to .env, run once, then remove RUN_DEPLOY and delete this file.';
    exit;
}

try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);

    file_put_contents($debugLog, date('c') . " db:seed completed\n", FILE_APPEND);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Seed completed. Delete this file and remove RUN_DEPLOY from .env.';
} catch (Throwable $e) {
    file_put_contents($debugLog, date('c') . " Seed error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Error: ' . $e->getMessage() . "\nFile: " . $e->getFile() . ' (' . $e->getLine() . ")\n";
}
