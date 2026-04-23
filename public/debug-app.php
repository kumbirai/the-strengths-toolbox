<?php
/**
 * One-time debug: see why the Laravel app returns 500.
 * Visit /debug-app.php once, then DELETE this file.
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

try {
    define('LARAVEL_START', microtime(true));

    if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (" . $e->getLine() . ")\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
