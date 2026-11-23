<?php
/**
 * Google API Hits & Consumption Diagnostic Audit Tool
 * Access: https://merarashifal.com/audit-google-hits.php?token=mr_deploy_2026
 */

define('WP_USE_THEMES', false);
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once __DIR__ . '/wp-load.php';
} else {
    die("wp-load.php not found.");
}

$is_admin = function_exists('current_user_can') && current_user_can('manage_options');
$authorized = $is_admin || (isset($_GET['token']) && $_GET['token'] === 'mr_deploy_2026');

if (!$authorized) {
    http_response_code(403);
    die("Access denied.");
}

header('Content-Type: text/plain; charset=utf-8');

global $wpdb;

echo "====================================================\n";
echo "GOOGLE API CONSUMPTION & LEAK AUDIT REPORT\n";
echo "Server Time: " . current_time('mysql') . " (Timezone: " . wp_timezone_string() . ")\n";
echo "Site URL: " . home_url() . "\n";
echo "====================================================\n\n";

// 1. ACTIVE API KEYS CONFIGURED IN WP-CONFIG
echo "1. CONFIGURED GOOGLE KEYS IN WP-CONFIG:\n";
echo "----------------------------------------------------\n";
$maps_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
$gemini_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : (defined('MR_GEMINI_API_KEY') ? MR_GEMINI_API_KEY : '');
echo "GOOGLE_MAPS_API_KEY: " . ($maps_key ? (substr($maps_key, 0, 10) . '...' . substr($maps_key, -5)) : 'NOT DEFINED') . "\n";
echo "GOOGLE_API_KEY (Gemini): " . ($gemini_key ? (substr($gemini_key, 0, 10) . '...' . substr($gemini_key, -5)) : 'NOT DEFINED') . "\n\n";

// 2. CHECK SCHEDULED CRON JOBS
echo "2. SCHEDULED CRON JOBS (Potential Automated Background Hits):\n";
echo "----------------------------------------------------\n";
$crons = _get_cron_array();
$cron_found = 0;
if ($crons) {
    foreach ($crons as $ts => $hooks) {
        foreach ($hooks as $hook => $details) {
            if (strpos($hook, 'mrcb') !== false || strpos($hook, 'niyati') !== false || strpos($hook, 'gemini') !== false || strpos($hook, 'baby') !== false || strpos($hook, 'report') !== false) {
                echo sprintf("%-30s | Next Run: %s\n", $hook, wp_date('Y-m-d H:i:s', $ts));
                $cron_found++;
            }
        }
    }
}
if (!$cron_found) {
    echo "No content-bot, niyati, or report crons currently queued in wp_cron.\n";
}
echo "\n";

// 3. AUDIT CONTENT BOT QUEUE (MRCB)
echo "3. CONTENT BOT (MRCB) GENERATION ACTIVITY (Last 72 Hours):\n";
echo "----------------------------------------------------\n";
$mrcb_table = $wpdb->prefix . 'mrcb_queue';
if ($wpdb->get_var("SHOW TABLES LIKE '$mrcb_table'") === null) {
    $mrcb_table = 'af_mrcb_queue';
}
if ($wpdb->get_var("SHOW TABLES LIKE '$mrcb_table'") !== null) {
    $recent_mrcb = $wpdb->get_results(
        "SELECT id, post_type, topic_title_hi, focus_keyword, admin_status, last_generated_at, created_at 
         FROM {$mrcb_table} 
         WHERE (last_generated_at >= DATE_SUB(NOW(), INTERVAL 3 DAY) OR created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY))
         ORDER BY id DESC LIMIT 20",
        ARRAY_A
    );
    if (!empty($recent_mrcb)) {
        echo "Found " . count($recent_mrcb) . " articles generated/created in the last 72 hours:\n";
        foreach ($recent_mrcb as $row) {
            echo "- ID: {$row['id']} | Type: {$row['post_type']} | Status: {$row['admin_status']} | Generated: {$row['last_generated_at']}\n";
            echo "  Title: {$row['topic_title_hi']}\n";
        }
    } else {
        echo "Zero articles generated or queued in the last 72 hours in {$mrcb_table}.\n";
    }
} else {
    echo "Table {$mrcb_table} does not exist.\n";
}
echo "\n";

// 4. AUDIT NIYATI CHAT TURNS & GUEST CHAT LOGS
echo "4. NIYATI AI CHAT ACTIVITY (Last 48 Hours):\n";
echo "----------------------------------------------------\n";
$chat_table = $wpdb->prefix . 'niyati_chat_history';
if ($wpdb->get_var("SHOW TABLES LIKE '$chat_table'") === null) {
    $chat_table = 'af_niyati_chat_history';
}
if ($wpdb->get_var("SHOW TABLES LIKE '$chat_table'") !== null) {
    $chat_counts = $wpdb->get_results(
        "SELECT DATE(created_at) as chat_date, COUNT(*) as total_turns, COUNT(DISTINCT user_id) as distinct_users 
         FROM {$chat_table} 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
         GROUP BY DATE(created_at) 
         ORDER BY chat_date DESC",
        ARRAY_A
    );
    if (!empty($chat_counts)) {
        foreach ($chat_counts as $c) {
            echo "Date: {$c['chat_date']} | Turns: {$c['total_turns']} | Users: {$c['distinct_users']}\n";
        }
    } else {
        echo "Zero chat messages logged in {$chat_table} in the last 72 hours.\n";
    }
} else {
    echo "Table {$chat_table} does not exist.\n";
}
echo "\n";

// 5. AUDIT BABY NAME PROFILES GENERATED
echo "5. BABY NAMES PROFILES AI ENRICHMENT (Last 72 Hours):\n";
echo "----------------------------------------------------\n";
$baby_profiles = $wpdb->prefix . 'baby_name_profiles';
if ($wpdb->get_var("SHOW TABLES LIKE '$baby_profiles'") === null) {
    $baby_profiles = 'af_baby_name_profiles';
}
if ($wpdb->get_var("SHOW TABLES LIKE '$baby_profiles'") !== null) {
    $has_created_col = $wpdb->get_results("SHOW COLUMNS FROM {$baby_profiles} LIKE 'created_at'");
    if (!empty($has_created_col)) {
        $recent_baby = $wpdb->get_results(
            "SELECT name_slug, gender, religion, created_at 
             FROM {$baby_profiles} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
             ORDER BY id DESC LIMIT 15",
            ARRAY_A
        );
        echo "Found " . count($recent_baby) . " baby profiles generated in last 72h.\n";
        foreach ($recent_baby as $rb) {
            echo "- {$rb['name_slug']} ({$rb['gender']}) at {$rb['created_at']}\n";
        }
    } else {
        $total_profiles = $wpdb->get_var("SELECT COUNT(*) FROM {$baby_profiles}");
        echo "Total cached baby profiles: {$total_profiles} (created_at column not present).\n";
    }
} else {
    echo "Table {$baby_profiles} does not exist.\n";
}
echo "\n";

// 6. AUDIT PAID / CUSTOM ASTROLOGY REPORTS
echo "6. ASTROLOGY REPORT GENERATION ORDERS:\n";
echo "----------------------------------------------------\n";
$reports_table = $wpdb->prefix . 'mr_report_orders';
if ($wpdb->get_var("SHOW TABLES LIKE '$reports_table'") === null) {
    $reports_table = 'af_mr_report_orders';
}
if ($wpdb->get_var("SHOW TABLES LIKE '$reports_table'") !== null) {
    $recent_rep = $wpdb->get_results(
        "SELECT id, order_status, report_type, created_at 
         FROM {$reports_table} 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
         ORDER BY id DESC LIMIT 10",
        ARRAY_A
    );
    if (!empty($recent_rep)) {
        foreach ($recent_rep as $rr) {
            echo "- Report Order ID: {$rr['id']} | Type: {$rr['report_type']} | Status: {$rr['order_status']} | Created: {$rr['created_at']}\n";
        }
    } else {
        echo "Zero report orders in the last 72 hours.\n";
    }
} else {
    echo "Table {$reports_table} does not exist.\n";
}
echo "\n====================================================\n";
echo "AUDIT COMPLETE.\n";
echo "====================================================\n";
