<?php
// First line: write immediately so we know the script is executed (no closures yet).
$debugLog = __DIR__ . '/run-migrate-debug.log';
file_put_contents($debugLog, date('c') . " Step 0 script started\n", FILE_APPEND);

$log = function ($msg) use ($debugLog) {
    file_put_contents($debugLog, date('c') . " $msg\n", FILE_APPEND | LOCK_EX);
};

register_shutdown_function(function () use ($debugLog) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        @file_put_contents($debugLog, date('c') . " FATAL: {$err['message']} in {$err['file']} on line {$err['line']}\n", FILE_APPEND | LOCK_EX);
    }
});

$log('Step 1 PHP ' . PHP_VERSION . ' __DIR__=' . __DIR__);

$envPath = dirname(__DIR__) . '/.env';
$log('Step 2 env path=' . $envPath . ' exists=' . (file_exists($envPath) ? 'yes' : 'no'));

$allowRun = false;
if (file_exists($envPath) && is_readable($envPath)) {
    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = $lines ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (preg_match('/^RUN_DEPLOY\s*=\s*(.+)$/', $line, $m)) {
            $v = strtolower(trim($m[1], " \t\"'"));
            if ($v === '1' || $v === 'true' || $v === 'on') {
                $allowRun = true;
            }
            break;
        }
    }
}
$log('Step 3 RUN_DEPLOY allowRun=' . ($allowRun ? 'yes' : 'no'));

if (!$allowRun) {
    $log('Step 4 exit 403');
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Forbidden. Add RUN_DEPLOY=1 to .env (project root), run once, then remove RUN_DEPLOY and delete this file.';
    exit;
}

try {
    $log('Step 5 define LARAVEL_START');
    define('LARAVEL_START', microtime(true));

    $vendor = __DIR__ . '/../vendor/autoload.php';
    $log('Step 6 vendor=' . $vendor . ' is_file=' . (is_file($vendor) ? 'yes' : 'no'));
    if (!is_file($vendor)) {
        throw new RuntimeException('vendor/autoload.php not found. Path: ' . $vendor);
    }

    $log('Step 7 require vendor');
    require $vendor;

    $log('Step 8 bootstrap/app.php');
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $log('Step 9 kernel bootstrap');
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $log('Step 10 migrate --force');
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

    $log('Step 11 done');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Migrations completed. Delete this file and run-migrate-debug.log, and remove RUN_DEPLOY from .env.';
} catch (Throwable $e) {
    $log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Error: ' . $e->getMessage() . "\nFile: " . $e->getFile() . ' (' . $e->getLine() . ")\n";
    echo "Details in run-migrate-debug.log\n";
}
