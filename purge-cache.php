<?php
/**
 * Production Cache Purge Endpoint
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

    // 4. Purge post caches to trigger StackCache edge CDN purge
    $pages = ['pricing', 'reports'];
    foreach ($pages as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            clean_post_cache($page->ID);
            do_action('clean_post_cache', $page->ID);
            do_action('save_post', $page->ID, $page, true);
            echo "Post Cache: PURGED for {$slug} (ID: {$page->ID})\n";
        }
    }

    // Specifically purge ID 7313 (Pricing page)
    clean_post_cache(7313);
    do_action('clean_post_cache', 7313);
    echo "Post Cache: PURGED for ID 7313\n";

    // 5. Purge Autoptimize Cache
    if (class_exists('autoptimizeCache')) {
        autoptimizeCache::clearall();
        echo "Autoptimize Cache: CLEARED\n";
    }

    // 6. StackCache hooks
    do_action('stackcache_purge_cache');
    echo "StackCache action fired\n";
}

echo "SUCCESS: ALL CACHES CLEARED\n";
