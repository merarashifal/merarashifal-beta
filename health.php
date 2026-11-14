<?php
/**
 * Server Health & Quick Diagnostics
 */
header('Content-Type: text/plain');
echo "=== SERVER STATUS ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Document Root: " . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'unknown') . "\n";
echo "wp-config.php: " . (file_exists(__DIR__ . '/wp-config.php') ? "EXISTS" : "MISSING") . "\n";
echo ".htaccess: " . (file_exists(__DIR__ . '/.htaccess') ? "EXISTS" : "MISSING") . "\n";
echo "Uploads dir: " . (is_dir(__DIR__ . '/wp-content/uploads') ? "EXISTS" : "MISSING") . "\n";
echo "=====================\n";
