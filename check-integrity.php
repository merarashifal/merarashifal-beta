<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

echo "PHP Version: " . phpversion() . "\n";
$err_file = __DIR__ . '/error_log';
if (file_exists($err_file)) {
    echo "=== RECENT ERROR LOG (LAST 20 LINES) ===\n";
    $lines = file($err_file);
    echo implode("", array_slice($lines, -20));
    echo "========================================\n\n";
}

$beta_dir = __DIR__;
$live_dir = realpath(__DIR__ . '/../public_html');

$files = [
    'wp-content/themes/astra-child/functions.php',
    'robots.txt',
    'purge-cache.php',
    'sync-to-production.php',
    'wp-content/plugins/mr-subscription-reports/includes/class-mr-pricing-page.php',
    'wp-content/plugins/mr-subscription-reports/includes/class-mr-reports-showcase.php',
    'wp-content/plugins/mr-subscription-reports/includes/class-mr-checkout.php',
    'wp-content/plugins/mr-subscription-reports/includes/class-mr-razorpay.php',
    'wp-content/plugins/mr-content-bot/includes/class-mrcb-writer.php',
    'wp-content/plugins/mr-content-bot/includes/class-mrcb-scheduler.php',
    'wp-content/plugins/mr-content-bot/includes/class-mrcb-publisher.php',
    'wp-content/plugins/niyati-ai-astrology-chat/includes/class-niyati-rest-api.php',
    'wp-content/plugins/niyati-ai-astrology-chat/niyati-ai-astrology-chat.php',
    'wp-content/plugins/niyati-ai-astrology-chat/assets/js/niyati-chat.js',
    'wp-content/plugins/niyati-ai-astrology-chat/assets/css/niyati-chat.css',
    'wp-content/themes/astra-child/page-templates/template-pricing.php',
    'wp-content/themes/astra-child/page-templates/template-newhomepage.php',
    'merarashifal-logo.png',
    'fix-pending-order.php',
];

echo "=== SERVER INTEGRITY CHECK ===\n";
echo "Date: " . date('Y-m-d H:i:s') . " UTC\n";
echo "Beta Dir: " . $beta_dir . "\n";
echo "Live Dir: " . ($live_dir ?: 'NOT FOUND') . "\n\n";

printf("%-45s | %-12s | %-12s | %s\n", "FILE", "BETA STATUS", "LIVE STATUS", "INTEGRITY");
echo str_repeat("-", 85) . "\n";

$all_synced = true;

foreach ($files as $rel) {
    $beta_path = $beta_dir . '/' . $rel;
    $live_path = $live_dir ? ($live_dir . '/' . $rel) : '';

    $beta_exists = file_exists($beta_path);
    $live_exists = $live_path && file_exists($live_path);

    $beta_hash = $beta_exists ? md5_file($beta_path) : 'NONE';
    $live_hash = $live_exists ? md5_file($live_path) : 'NONE';

    $beta_size = $beta_exists ? filesize($beta_path) : 0;
    $live_size = $live_exists ? filesize($live_path) : 0;

    $match = ($beta_exists && $live_exists && $beta_hash === $live_hash);
    if (!$match) {
        $all_synced = false;
    }

    $b_status = $beta_exists ? ($beta_size . ' B') : 'MISSING';
    $l_status = $live_exists ? ($live_size . ' B') : 'MISSING';
    $result   = $match ? "MATCH (OK)" : ($beta_exists && $live_exists ? "DIFF CONTENT" : "NOT ON LIVE");

    printf("%-45s | %-12s | %-12s | %s\n", basename($rel), $b_status, $l_status, $result);
}

echo str_repeat("-", 85) . "\n";
echo $all_synced ? "RESULT: 100% IN SYNC - ALL FILES MATCH ON BETA AND LIVE!\n" : "RESULT: SOME FILES DIFFER OR PENDING DEPLOY.\n";

if (file_exists($live_dir . '/wp-load.php')) {
    require_once $live_dir . '/wp-load.php';
    global $wpdb;
    $active = get_option('active_plugins', []);
    echo "\n=== LIVE DATABASE ACTIVE PLUGINS ===\n";
    foreach ($active as $p) {
        if (strpos($p, 'niyati') !== false) {
            echo " -> [ACTIVE] " . $p . "\n";
        }
    }
    $settings = get_option('niyati_chat_settings', []);
    echo "Live floating_enabled setting: " . var_export($settings['floating_enabled'] ?? 'NOT SET', true) . "\n";

    if (isset($_GET['fix_floating']) && $_GET['fix_floating'] === '1') {
        $settings['floating_enabled'] = '1';
        update_option('niyati_chat_settings', $settings);
        echo "FIX APPLIED: floating_enabled updated to '1' in live database!\n";
        $refreshed = get_option('niyati_chat_settings', []);
        echo "Verified new setting: " . var_export($refreshed['floating_enabled'] ?? 'NOT SET', true) . "\n";
    }
}
