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
            $sc->purge('https://merarashifal.com/pricing/');
            $sc->purge('https://merarashifal.com/pricing');
            echo "WPStackCache: purge(/pricing/) SUCCESS\n";
        }
    }

    // 5. Clean post cache in WP
    if (function_exists('clean_post_cache')) {
        clean_post_cache(7313);
        echo "WP clean_post_cache(7313): SUCCESS\n";
    }

    echo "ALL PURGE ROUTINES COMPLETED\n";
}
