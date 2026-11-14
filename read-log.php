<?php
header('Content-Type: text/plain');
$log = __DIR__ . '/wp-content/debug.log';
if (file_exists($log)) {
    $lines = file($log);
    echo "=== LAST 30 LINES OF DEBUG.LOG ===\n";
    echo implode("", array_slice($lines, -30));
} else {
    echo "debug.log does not exist in wp-content/\n";
}
