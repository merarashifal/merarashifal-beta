<?php
/**
 * Production Cache Purge Endpoint with WPStackCache Native Hook
 */
if (!isset($_GET['token']) || $_GET['token'] !== 'mr_deploy_2026') {
    http_response_code(403);
    die('Unauthorized');
}

header('Content-Type: text/plain; charset=utf-8');

// 1. Reset OPcache
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "OPcache: RESET\n";
}

// 2. Load WordPress
$wp_load = __DIR__ . '/wp-load.php';
if (file_exists($wp_load)) {
    require_once $wp_load;

    // 3. Flush WP Object Cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo "WP Object Cache: FLUSHED\n";
    }

    // 4. Native 20i WPStackCache Purge
    if (class_exists('WPStackCache')) {
        $sc = new WPStackCache();
        if (method_exists($sc, 'all_purge_cache')) {
            $sc->all_purge_cache();
            echo "WPStackCache: all_purge_cache() SUCCESS\n";
        }
        if (method_exists($sc, 'post_purge_cache')) {
            $sc->post_purge_cache(7313);
            echo "WPStackCache: post_purge_cache(7313) SUCCESS\n";
        }
        if (method_exists($sc, 'purge')) {
            $sc->purge('https://merarashifal.com/');
            $sc->purge('https://merarashifal.com/pricing/');
            $sc->purge('https://merarashifal.com/pricing');
            echo "WPStackCache: purge(/ and /pricing/) SUCCESS\n";
        }
    }

    // 5. Clean post cache in WP
    if (function_exists('clean_post_cache')) {
        clean_post_cache(7313);
        echo "WP clean_post_cache(7313): SUCCESS\n";
    }

    // 6. Security: Wipe legacy plain-text API keys from wp_options
    global $wpdb;
    if (!empty($wpdb)) {
        $keys_to_clean = [
            'mr_gemini_api_key',
            'mr_vedic_api_key',
            'mr_browserless_token',
            'mr_razorpay_live_key_id',
            'mr_razorpay_live_key_secret',
            'mr_razorpay_test_key_id',
            'mr_razorpay_test_key_secret',
        ];
        $placeholders = implode("','", array_map('esc_sql', $keys_to_clean));
        $deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name IN ('{$placeholders}')");
        echo "Database Cleanup: {$deleted} legacy plain-text key records removed from database.\n";
    }

    // 7. Ensure Niyati floating chat widget is enabled in database
    $niyati_settings = get_option('niyati_chat_settings', []);
    if (empty($niyati_settings['floating_enabled']) || $niyati_settings['floating_enabled'] !== '1') {
        $niyati_settings['floating_enabled'] = '1';
        update_option('niyati_chat_settings', $niyati_settings);
        echo "Niyati Settings: floating_enabled updated to '1'\n";
    } else {
        echo "Niyati Settings: floating_enabled is already '1'\n";
    }

    // 8. Verify Razorpay Gateway Status
    if (!class_exists('MR_Razorpay')) {
        $rzp_file = __DIR__ . '/wp-content/plugins/mr-subscription-reports/includes/class-mr-razorpay.php';
        if (file_exists($rzp_file)) {
            require_once $rzp_file;
        }
    }
    if (class_exists('MR_Razorpay')) {
        $rzp_test = MR_Razorpay::test_connection();
        echo "Razorpay Active Key ID: " . MR_Razorpay::get_active_key_id() . "\n";
        echo "Razorpay Secret SHA1: " . (defined('Razorpay_Live_Secret_Key') ? sha1(Razorpay_Live_Secret_Key) : 'none') . "\n";
        echo "Razorpay Connection: " . ($rzp_test['success'] ? 'SUCCESS' : ('FAILED: ' . $rzp_test['message'])) . "\n";
    }

    echo "ALL PURGE ROUTINES COMPLETED\n";
}
