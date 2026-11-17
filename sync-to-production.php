<?php
/**
 * Production Deployment Sync Tool
 * Safely copies updated files from git-beta to public_html
 * Access: https://beta.merarashifal.com/sync-to-production.php
 */

define('WP_USE_THEMES', false);
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once __DIR__ . '/wp-load.php';
}

$source_dir = __DIR__;
$target_dir = realpath(__DIR__ . '/../public_html');

// Security Check: require run key or logged-in admin
$is_admin = function_exists('current_user_can') && current_user_can('manage_options');
$authorized = $is_admin || (isset($_GET['token']) && $_GET['token'] === 'mr_deploy_2026');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deploy to Production (public_html)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f1f5f9; padding: 24px; line-height: 1.6; }
        .card { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { color: #f59e0b; font-size: 24px; margin-top: 0; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        .badge-green { background: #065f46; color: #6ee7b7; }
        .badge-amber { background: #78350f; color: #fcd34d; }
        .badge-red { background: #881337; color: #fda4af; }
        .paths { background: #0f172a; border-radius: 8px; padding: 14px; font-family: monospace; font-size: 13px; margin: 16px 0; border: 1px solid #334155; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; font-size: 15px; }
        .btn-primary { background: #d97706; color: #fff; transition: background 0.2s; }
        .btn-primary:hover { background: #b45309; }
        .log-box { background: #020617; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 12px; max-height: 420px; overflow-y: auto; border: 1px solid #334155; margin-top: 20px; }
        .log-success { color: #34d399; }
        .log-skip { color: #94a3b8; }
        .log-warn { color: #f59e0b; }
        .log-err { color: #f87171; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 Deploy Beta Changes &rarr; Production (public_html)</h1>
    
    <div class="paths">
        <strong>Source (Beta):</strong> <?php echo esc_html($source_dir); ?><br>
        <strong>Target (Live):</strong> <?php echo esc_html($target_dir ?: 'NOT FOUND'); ?>
    </div>

    <?php
    if (!$target_dir || !is_dir($target_dir)) {
        echo '<p><span class="badge badge-red">Error: public_html directory not found adjacent to git-beta</span></p>';
        echo '</div></body></html>';
        exit;
    }

    if (!is_writable($target_dir)) {
        echo '<p><span class="badge badge-amber">Warning: public_html is not directly writable. Checking permissions...</span></p>';
    }

    $do_sync = isset($_GET['action']) && $_GET['action'] === 'sync' && $authorized;
    ?>

    <?php if (!$do_sync): ?>
        <p>This automated tool synchronizes the latest verified updates from <strong>git-beta</strong> directly into your live production directory (<strong>public_html</strong>).</p>
        
        <div style="background: rgba(217, 119, 6, 0.1); border-left: 4px solid #d97706; padding: 12px 16px; border-radius: 4px; margin: 18px 0;">
            <strong style="color: #f59e0b;">Shielded Safety Protections:</strong><br>
            The following files are strictly preserved and will <strong>NEVER</strong> be overwritten:
            <ul style="margin: 6px 0 0 20px; padding: 0;">
                <li><code>wp-config.php</code> (Keeps your live database credentials completely untouched)</li>
                <li><code>.htaccess</code> (Keeps your live server rewrite rules untouched)</li>
                <li><code>wp-content/uploads/</code> (Keeps all media files, images, and user uploads safe)</li>
                <li><code>.git/</code>, <code>scratch/</code>, <code>*.zip</code>, <code>*.log</code></li>
            </ul>
        </div>

        <p>
            <a href="?action=sync&token=mr_deploy_2026" class="btn btn-primary" onclick="return confirm('Deploy all verified Beta code to LIVE public_html now?');">
                ⚡ Deploy to Production (public_html) Now
            </a>
        </p>
    <?php else: ?>
        <p><span class="badge badge-green">Sync in progress...</span></p>
        
        <div class="log-box">
        <?php
        // 1. Try pulling latest git changes from origin/beta
        if (function_exists('exec')) {
            $git_out = [];
            $git_ret = -1;
            @exec('git pull origin beta 2>&1', $git_out, $git_ret);
            if (!empty($git_out)) {
                echo "<div class='log-warn'><strong>Git Pull Output:</strong> " . esc_html(implode(' | ', $git_out)) . "</div>\n";
            }
        }
        $sync_targets = [
            'wp-content/plugins/mr-subscription-reports',
            'wp-content/plugins/mr-content-bot',
            'wp-content/plugins/niyati-ai-astrology-chat',
            'wp-content/themes/astra-child',
            'merarashifal-logo.png',
            'fix-pending-order.php',
            'purge-cache.php',
            'robots.txt',
        ];

        $ignored_patterns = [
            'wp-config.php',
            '.htaccess',
            'wp-content/uploads',
            '.git',
            'scratch',
            '.log',
            'sync-to-production.php',
            'probe-sync.php',
            'fix-urls.php',
        ];

        $copied_count = 0;
        $skipped_count = 0;
        $error_count = 0;

        function copy_recursive($src, $dst, &$copied_count, &$skipped_count, &$error_count, $ignored_patterns) {
            foreach ($ignored_patterns as $ign) {
                if (strpos($src, $ign) !== false) {
                    echo "<div class='log-skip'>[SKIP - PROTECTED] " . esc_html($src) . "</div>\n";
                    $skipped_count++;
                    return;
                }
            }

            if (is_dir($src)) {
                if (!file_exists($dst)) {
                    if (!@mkdir($dst, 0755, true)) {
                        echo "<div class='log-err'>[DIR CREATE FAIL] " . esc_html($dst) . "</div>\n";
                        $error_count++;
                        return;
                    }
                }
                $files = scandir($src);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        copy_recursive($src . '/' . $file, $dst . '/' . $file, $copied_count, $skipped_count, $error_count, $ignored_patterns);
                    }
                }
            } elseif (is_file($src)) {
                $dir = dirname($dst);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0755, true);
                }
                
                // Compare checksums to avoid unnecessary writes
                if (file_exists($dst) && md5_file($src) === md5_file($dst)) {
                    // Already identical
                    return;
                }

                if (@copy($src, $dst)) {
                    echo "<div class='log-success'>[SYNCED] " . esc_html(str_replace(__DIR__, '', $src)) . " &rarr; " . esc_html(basename($dst)) . "</div>\n";
                    $copied_count++;
                } else {
                    echo "<div class='log-err'>[COPY FAIL] " . esc_html($src) . " &rarr; " . esc_html($dst) . "</div>\n";
                    $error_count++;
                }
            }
        }

        foreach ($sync_targets as $rel_path) {
            $src = $source_dir . '/' . $rel_path;
            $dst = $target_dir . '/' . $rel_path;

            if (file_exists($src)) {
                echo "<div class='log-warn'><strong>Syncing: " . esc_html($rel_path) . "</strong></div>\n";
                copy_recursive($src, $dst, $copied_count, $skipped_count, $error_count, $ignored_patterns);
            } else {
                echo "<div class='log-err'>Source not found: " . esc_html($src) . "</div>\n";
            }
        }

        // Flush OPcache
        if (function_exists('opcache_reset')) {
            @opcache_reset();
            echo "<div class='log-success'>[CACHE] OPcache reset successfully.</div>\n";
        }

        // Trigger production cache purge endpoint
        $purge_url = 'https://merarashifal.com/purge-cache.php?token=mr_deploy_2026';
        $purge_res = @file_get_contents($purge_url);
        if ($purge_res !== false) {
            echo "<div class='log-success'>[PURGE] Live Cache Purge executed: <pre style='margin:4px 0; color:#34d399;'>" . esc_html(trim($purge_res)) . "</pre></div>\n";
        } else {
            echo "<div class='log-warn'>[PURGE] Note: Purge endpoint called, check response manually.</div>\n";
        }

        echo "</div>";

        echo "<h3 style='color:#34d399; margin-top:20px;'>✅ Sync Complete!</h3>";
        echo "<p>Files Updated: <strong>{$copied_count}</strong> | Errors: <strong>{$error_count}</strong></p>";
        echo "<p><a href='https://merarashifal.com/' target='_blank' class='btn btn-primary'>🌐 Visit Live Site (merarashifal.com)</a></p>";
        ?>
    <?php endif; ?>
</div>
</body>
</html>
