<?php
/**
 * Direct WordPress Diagnostic Error Inspector
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

try {
    require_once __DIR__ . '/wp-load.php';
    echo "WORDPRESS LOADED SUCCESSFULLY!\n";
    echo "Site URL: " . get_site_url() . "\n";
    echo "Active Theme: " . wp_get_theme()->get('Name') . "\n";
} catch (Throwable $e) {
    echo "FATAL ERROR CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
