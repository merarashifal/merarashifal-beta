<?php
/**
 * Server Health & WordPress Error Diagnostics
 */
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SERVER STATUS ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Document Root: " . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'unknown') . "\n";
echo "wp-config.php: " . (file_exists(__DIR__ . '/wp-config.php') ? "EXISTS" : "MISSING") . "\n";
echo ".htaccess: " . (file_exists(__DIR__ . '/.htaccess') ? "EXISTS" : "MISSING") . "\n";
echo "=====================\n\n";

echo "=== ATTEMPTING WORDPRESS BOOTSTRAP ===\n";
define('WP_USE_THEMES', false);
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

try {
    require_once __DIR__ . '/wp-load.php';
    echo "SUCCESS: WordPress Loaded!\n";
    echo "Site URL: " . get_site_url() . "\n";
    echo "Active Theme: " . wp_get_theme()->get('Name') . "\n";
} catch (Throwable $e) {
    echo "FATAL ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

$log = __DIR__ . '/wp-content/debug.log';
if (file_exists($log)) {
    echo "\n=== LAST 20 LINES OF DEBUG.LOG ===\n";
    $lines = file($log);
    echo implode("", array_slice($lines, -20));
}
