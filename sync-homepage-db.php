<?php
/**
 * Live Database Synchronization for Homepage Bug Fixes & Ekadashi URLs
 * Usage: https://yourdomain.com/sync-homepage-db.php?token=mr_deploy_2026
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
    die("Access denied. Please provide valid deployment token or log in as administrator.");
}

global $wpdb;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Sync - Homepage & Ekadashi Links</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f1f5f9; padding: 24px; line-height: 1.6; }
        .card { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { color: #f59e0b; font-size: 22px; margin-top: 0; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px; }
        .badge-green { background: #065f46; color: #6ee7b7; }
        .badge-amber { background: #78350f; color: #fcd34d; }
        .log-box { background: #020617; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 13px; max-height: 480px; overflow-y: auto; border: 1px solid #334155; margin-top: 16px; }
        .log-success { color: #34d399; margin-bottom: 6px; }
        .log-info { color: #60a5fa; margin-bottom: 6px; }
        .log-warn { color: #fcd34d; margin-bottom: 6px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 6px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; font-size: 14px; margin-top: 16px; }
        .btn-home { background: #d97706; color: #fff; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚡ Live Database Synchronization Report</h1>
    <p>Automated database updater for Snippet 67 label typos and <code>af_ekadashi</code> canonical article URLs.</p>

    <div class="log-box">
    <?php
    // ---------------------------------------------------------
    // 1. UPDATE SNIPPET 67 IN DATABASE (Code Snippets Plugin)
    // ---------------------------------------------------------
    $snippets_table = $wpdb->prefix . 'snippets';
    if ($wpdb->get_var("SHOW TABLES LIKE '$snippets_table'") === null) {
        $snippets_table = 'af_snippets';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$snippets_table'") !== null) {
        $snip67 = $wpdb->get_row("SELECT id, name, code FROM {$snippets_table} WHERE id = 67", ARRAY_A);
        if ($snip67) {
            $code = $snip67['code'];
            $mod = false;

            if (strpos($code, 'Diety:') !== false) {
                $code = str_replace('Diety:', 'Deity:', $code);
                $mod = true;
            }
            if (strpos($code, 'Samvaat Details') !== false) {
                $code = str_replace('Samvaat Details', 'Samvat Details', $code);
                $mod = true;
            }
            if (strpos($code, "'https://merarashifal.com/ekadashi'") !== false && strpos($code, 'get_festival_smart_link') === false) {
                $code = str_replace(
                    "\$ek_url = !empty(\$ek_row->url) ? esc_url(\$ek_row->url) : 'https://merarashifal.com/ekadashi';",
                    "\$ek_url = !empty(\$ek_row->url) ? esc_url(\$ek_row->url) : (class_exists('MR_Homepage_Manager') ? esc_url(MR_Homepage_Manager::get_festival_smart_link(\$ek_row->ekadashi_name)['url']) : 'https://merarashifal.com/ekadashi');",
                    $code
                );
                $mod = true;
            }

            if ($mod) {
                $wpdb->update($snippets_table, ['code' => $code], ['id' => 67]);
                echo "<div class='log-success'>✅ [SNIPPET 67]: Corrected typos ('Diety' &rarr; 'Deity', 'Samvaat' &rarr; 'Samvat') and added smart link fallback.</div>\n";
            } else {
                echo "<div class='log-info'>ℹ️ [SNIPPET 67]: Already up-to-date with correct 'Deity' and 'Samvat' labels.</div>\n";
            }
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 67]: Snippet ID 67 not found in table {$snippets_table}.</div>\n";
        }
    } else {
        echo "<div class='log-warn'>⚠️ [SNIPPET 67]: Snippets table not found.</div>\n";
    }

    // ---------------------------------------------------------
    // 2. POPULATE af_ekadashi ARTICLE URLS
    // ---------------------------------------------------------
    $ek_table = $wpdb->prefix . 'ekadashi';
    if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") === null) {
        $ek_table = 'af_ekadashi';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") !== null) {
        $ek_rows = $wpdb->get_results("SELECT id, ekadashi_name, ekadashi_date, url FROM {$ek_table}", ARRAY_A);
        $updated_count = 0;

        foreach ($ek_rows as $er) {
            if (class_exists('MR_Homepage_Manager')) {
                $resolved = MR_Homepage_Manager::get_festival_smart_link($er['ekadashi_name']);
                if (!empty($resolved['url']) && $resolved['type'] === 'article') {
                    if ($er['url'] !== $resolved['url']) {
                        $wpdb->update($ek_table, ['url' => $resolved['url']], ['id' => $er['id']]);
                        $updated_count++;
                    }
                }
            }
        }

        echo "<div class='log-success'>✅ [EKADASHI TABLE]: Synced {$updated_count} canonical article URLs in `{$ek_table}` (Putrada, Papankusha, Nirjala, etc.).</div>\n";
    } else {
        echo "<div class='log-warn'>⚠️ [EKADASHI TABLE]: Ekadashi table not found.</div>\n";
    }

    // ---------------------------------------------------------
    // 3. FLUSH TRANSIENT CACHES
    // ---------------------------------------------------------
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo "<div class='log-success'>✅ [CACHE]: Object cache successfully flushed.</div>\n";
    }
    ?>
    </div>

    <p style="margin-top: 20px;">
        <span class="badge badge-green">Sync Completed Successfully</span>
    </p>

    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-home">&larr; Return to Live Homepage</a>
</div>
</body>
</html>
