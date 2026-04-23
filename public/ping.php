<?php
// Minimal ping: if this runs you get "ok" and ping.log is created. Delete after use.
file_put_contents(__DIR__ . '/ping.log', date('c') . " OK\n", FILE_APPEND);
header('Content-Type: text/plain');
echo 'ok';
