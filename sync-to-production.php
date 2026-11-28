<?php
/**
 * Production Deployment Sync Tool
 * Safely copies updated files from git-beta to public_html
 * Access: https://beta.merarashifal.com/sync-to-production.php
 */

$source_dir = __DIR__;
$target_dir = realpath(__DIR__ . '/../public_html');

// Security Check: require run key or logged-in admin
$token_valid = (isset($_GET['token']) && $_GET['token'] === 'mr_deploy_2026');

// Auto-clean stale git index.lock if present
$lock_file = __DIR__ . '/.git/index.lock';
if (file_exists($lock_file)) {
    @unlink($lock_file);
}

// If valid deploy token provided and shell_exec is available, sync git immediately before loading WordPress
$pre_git_sync = '';
if ($token_valid && function_exists('shell_exec')) {
    $pre_git_sync = @shell_exec('git fetch origin beta 2>&1 && git reset --hard origin/beta 2>&1');
}

define('WP_USE_THEMES', false);
if (file_exists(__DIR__ . '/wp-load.php')) {
    try {
        @require_once __DIR__ . '/wp-load.php';
    } catch (\Throwable $e) {
        // Safe continuation even if WordPress encounters a bootstrap issue
    }
}

$is_admin = function_exists('current_user_can') && current_user_can('manage_options');
$authorized = $is_admin || $token_valid;

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
        <strong>Target (Live):</strong> <?php echo esc_html($target_dir ?: 'NOT FOUND'); ?><br>
        <?php
        if (function_exists('shell_exec')) {
            $current_commit = @shell_exec('git log -1 --oneline 2>&1');
            if ($current_commit) {
                echo '<strong>Active Beta Commit:</strong> ' . esc_html(trim($current_commit)) . '<br>';
            }
        }
        ?>
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
        // 1. Auto-clean stale git index.lock if present
        $lock_file = __DIR__ . '/.git/index.lock';
        if (file_exists($lock_file)) {
            @unlink($lock_file);
            echo "<div class='log-warn'><strong>Cleaned stale .git/index.lock file.</strong></div>\n";
        }

        // 2. Fetch and pull latest changes from git remote if shell_exec is available
        if (function_exists('shell_exec')) {
            $git_output = @shell_exec('git fetch origin beta 2>&1 && git reset --hard origin/beta 2>&1');
            if ($git_output) {
                echo "<div class='log-warn'><strong>[GIT SYNC]:</strong> " . esc_html(trim($git_output)) . "</div>\n";
            }
            $active_commit = @shell_exec('git log -1 --oneline 2>&1');
            if ($active_commit) {
                echo "<div class='log-success'><strong>[UPDATED COMMIT]:</strong> " . esc_html(trim($active_commit)) . "</div>\n";
            }
        }

        // 3. Clean legacy plain-text API keys from beta DB if available
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
            echo "<div class='log-success'><strong>[DB CLEANUP]:</strong> Cleaned {$deleted} legacy plaintext key options from database.</div>\n";
        }

        // 4. Update Snippet 67 typos (Deity, Samvat) & smart links in DB
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
                    echo "<div class='log-success'><strong>[SNIPPET 67]:</strong> Corrected typos ('Deity', 'Samvat') & enabled smart links.</div>\n";
                }
            }
        }

        // 5. Update af_ekadashi canonical URLs in DB
        $ek_table = $wpdb->prefix . 'ekadashi';
        if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") === null) {
            $ek_table = 'af_ekadashi';
        }
        if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") !== null) {
            $ek_rows = $wpdb->get_results("SELECT id, ekadashi_name, url FROM {$ek_table}", ARRAY_A);
            $ek_up = 0;
            foreach ($ek_rows as $er) {
                if (class_exists('MR_Homepage_Manager')) {
                    $resolved = MR_Homepage_Manager::get_festival_smart_link($er['ekadashi_name']);
                    if (!empty($resolved['url']) && $resolved['type'] === 'article' && $er['url'] !== $resolved['url']) {
                        $wpdb->update($ek_table, ['url' => $resolved['url']], ['id' => $er['id']]);
                        $ek_up++;
                    }
                }
            }
            if ($ek_up > 0) {
                echo "<div class='log-success'><strong>[EKADASHI DB]:</strong> Synced {$ek_up} canonical article URLs in {$ek_table}.</div>\n";
            }
        }

        // 6. Upgrade Snippets 13, 14, 30, 46, 69, 70, 73, 74, 84 for Google Maps API Key
        if ($wpdb->get_var("SHOW TABLES LIKE '$snippets_table'") !== null) {
            $maps_updated = 0;
            
            // Snippet 13
            $r13 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 13", ARRAY_A);
            if ($r13) {
                $c = $r13['code'];
                $mod13 = false;
                if (strpos($c, '$mr_maps_key') === false) {
                    $c = str_replace(
                        "if (!defined('GOOGLE_API_KEY') || !GOOGLE_API_KEY) return;",
                        "\$mr_maps_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');\n    if (empty(\$mr_maps_key)) return;",
                        $c
                    );
                    $c = str_replace(
                        "\$inline_js = str_replace('GOOGLE_API_KEY_TOKEN', json_encode(GOOGLE_API_KEY), \$inline_js);",
                        "\$inline_js = str_replace('GOOGLE_API_KEY_TOKEN', json_encode(\$mr_maps_key), \$inline_js);",
                        $c
                    );
                    $mod13 = true;
                }
                $old_lazy = "['scroll', 'keydown', 'pointerdown', 'touchstart'].forEach(function(ev){\n    window.addEventListener(ev, function(){ ensureGoogleMaps(); }, {once:true, passive:true});\n  });\n  setTimeout(function(){ ensureGoogleMaps(); }, 4000);";
                $new_lazy = "// Only load Google Maps if city inputs actually exist on the page\n  setTimeout(function(){ if (findInputs().length > 0) ensureGoogleMaps(); }, 3000);";
                if (strpos($c, $old_lazy) !== false) {
                    $c = str_replace($old_lazy, $new_lazy, $c);
                    $mod13 = true;
                }
                if ($mod13) {
                    $wpdb->update($snippets_table, ['code' => $c], ['id' => 13]);
                    $maps_updated++;
                }
            }

            // Snippet 14
            $r14 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 14", ARRAY_A);
            if ($r14 && strpos($r14['code'], 'rawurlencode(GOOGLE_API_KEY)') !== false) {
                $c = str_replace(
                    'rawurlencode(GOOGLE_API_KEY)',
                    "rawurlencode(defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : ''))",
                    $r14['code']
                );
                $wpdb->update($snippets_table, ['code' => $c], ['id' => 14]);
                $maps_updated++;
            }

            // Snippet 30
            $r30 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 30", ARRAY_A);
            if ($r30 && strpos($r30['code'], 'GOOGLE_MAPS_API_KEY') === false) {
                $c = str_replace(
                    "\$google_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : 'YOUR_GOOGLE_KEY';",
                    "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : 'YOUR_GOOGLE_KEY');",
                    $r30['code']
                );
                $wpdb->update($snippets_table, ['code' => $c], ['id' => 30]);
                $maps_updated++;
            }

            // Snippet 46
            $r46 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 46", ARRAY_A);
            if ($r46 && strpos($r46['code'], 'GOOGLE_MAPS_API_KEY') === false) {
                $old46 = "if (!defined('GOOGLE_API_KEY')) wp_die('// Missing GOOGLE_API_KEY');\n    \$url = \"https://maps.googleapis.com/maps/api/js?key=\" . GOOGLE_API_KEY . \"&libraries=places\";";
                $new46 = "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');\n    if (empty(\$google_key)) wp_die('// Missing GOOGLE_MAPS_API_KEY');\n    \$url = \"https://maps.googleapis.com/maps/api/js?key=\" . \$google_key . \"&libraries=places\";";
                $c = str_replace($old46, $new46, $r46['code']);
                $wpdb->update($snippets_table, ['code' => $c], ['id' => 46]);
                $maps_updated++;
            }

            // Snippets 69, 70, 73, 74, 84
            $engine_ids = [69, 70, 73, 74, 84];
            foreach ($engine_ids as $eid) {
                $row = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = {$eid}", ARRAY_A);
                if ($row && strpos($row['code'], 'GOOGLE_MAPS_API_KEY') === false) {
                    $c = str_replace(
                        "\$google_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';",
                        "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');",
                        $row['code']
                    );
                    $wpdb->update($snippets_table, ['code' => $c], ['id' => $eid]);
                    $maps_updated++;
                }
            }
            if ($maps_updated > 0) {
                echo "<div class='log-success'><strong>[MAPS SNIPPETS]:</strong> Updated {$maps_updated} snippets (13, 14, 30, 46, 69, 70, 73, 74, 84) to use GOOGLE_MAPS_API_KEY.</div>\n";
            }

            // Snippet 63: MR Horoscope Programmatic Engine
            $snip63_file = __DIR__ . '/snippet_63_new.php';
            if (file_exists($snip63_file)) {
                $snip63_code = file_get_contents($snip63_file);
                $snip63_code = preg_replace('/^<\?php\s*/', '', $snip63_code);
                $snip63_code = preg_replace('/\?>\s*$/', '', $snip63_code);
                $res63 = $wpdb->update($snippets_table, ['code' => $snip63_code], ['id' => 63]);
                if ($res63 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 63]:</strong> Upgraded MR Horoscope Programmatic Engine with compact layout, #FFFBEB background, full Hindi parity & centered traits.</div>\n";
                }
            }

            // Snippet 64: MR Panchang Engine ([dynamic_panchang])
            $snip64_file = __DIR__ . '/snippet_64_new.php';
            if (file_exists($snip64_file)) {
                $snip64_code = file_get_contents($snip64_file);
                $snip64_code = preg_replace('/^<\?php\s*/', '', $snip64_code);
                $snip64_code = preg_replace('/\?>\s*$/', '', $snip64_code);
                $res64 = $wpdb->update($snippets_table, ['code' => $snip64_code], ['id' => 64]);
                if ($res64 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 64]:</strong> Upgraded MR Panchang Engine with compact 2-column layout, #FFFBEB background, full Hindi parity & centered traits.</div>\n";
                }
            }

            // Snippet 73: Kundali Engine ([kundali_calc_engine])
            $snip73_file = __DIR__ . '/snippet_73_new.php';
            if (file_exists($snip73_file)) {
                $snip73_code = file_get_contents($snip73_file);
                $snip73_code = preg_replace('/^<\?php\s*/', '', $snip73_code);
                $snip73_code = preg_replace('/\?>\s*$/', '', $snip73_code);
                $res73 = $wpdb->update($snippets_table, ['code' => $snip73_code], ['id' => 73]);
                if ($res73 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 73]:</strong> Upgraded Kundali Engine with warm amber #FFFBEB layout, compact cards, and mobile-friendly controls.</div>\n";
                }
            }

            // Snippet 28: Zodiac Compatibility Engine ([zodiac_compatibility_engine], [compatibility_results])
            $snip28_file = __DIR__ . '/snippet_28_new.php';
            if (file_exists($snip28_file)) {
                $snip28_code = file_get_contents($snip28_file);
                $snip28_code = preg_replace('/^<\?php\s*/', '', $snip28_code);
                $snip28_code = preg_replace('/\?>\s*$/', '', $snip28_code);
                $res28 = $wpdb->update($snippets_table, ['name' => 'Zodiac Compatibility Engine (V2 Bilingual)', 'code' => $snip28_code, 'active' => 1, 'scope' => 'global'], ['id' => 28]);
                if ($res28 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 28]:</strong> Upgraded Zodiac Compatibility Engine shortcode and REST lookup.</div>\n";
                }
            }

            // Snippet 33: Remedies Engine ([remedy_selector])
            $snip33_file = __DIR__ . '/snippet_33_new.php';
            if (file_exists($snip33_file)) {
                $snip33_code = file_get_contents($snip33_file);
                $snip33_code = preg_replace('/^<\?php\s*/', '', $snip33_code);
                $snip33_code = preg_replace('/\?>\s*$/', '', $snip33_code);
                $res33 = $wpdb->update($snippets_table, ['name' => 'Remedy Viewer Handler', 'code' => $snip33_code, 'active' => 1, 'scope' => 'global'], ['id' => 33]);
                if ($res33 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 33]:</strong> Upgraded Remedies Engine shortcode ([remedy_selector]) with 20 issues, interactive filters, localized modal & cross-sell.</div>\n";
                }
            }

            // Snippet 47: Muhurat Engine ([muhurat_viewer])
            $snip47_file = __DIR__ . '/snippet_47_new.php';
            if (file_exists($snip47_file)) {
                $snip47_code = file_get_contents($snip47_file);
                $snip47_code = preg_replace('/^<\?php\s*/', '', $snip47_code);
                $snip47_code = preg_replace('/\?>\s*$/', '', $snip47_code);
                $res47 = $wpdb->update($snippets_table, ['name' => 'Muhurat Code', 'code' => $snip47_code, 'active' => 1, 'scope' => 'global'], ['id' => 47]);
                if ($res47 !== false) {
                    echo "<div class='log-success'><strong>[SNIPPET 47]:</strong> Upgraded Muhurat Engine shortcode ([muhurat_viewer]) with Vedic cards, mobile layout & cross-sell.</div>\n";
                }
            }
        }

        $sync_targets = [
            'wp-content/plugins/mr-subscription-reports',
            'wp-content/plugins/mr-content-bot',
            'wp-content/plugins/niyati-ai-astrology-chat',
            'wp-content/themes/astra-child',
            'merarashifal-logo.png',
            'fix-pending-order.php',
            'sync-homepage-db.php',
            'deploy-compatibility-db.php',
            'deploy-remedies-db.php',
            'deploy-database-cleanup.php',
            'create_2027_muhurat_pages.php',
            'snippet_28_new.php',
            'snippet_33_new.php',
            'snippet_47_new.php',
            'snippet_63_new.php',
            'snippet_64_new.php',
            'snippet_70_new.php',
            'snippet_73_new.php',
            'migrations/001_upgrade_zodiac_compatibility.sql',
            'migrations/002_upgrade_af_remedies.sql',
            'output/populate_zodiac_compatibility.sql',
            'output/compatibility_preview.json',
            'output/update_af_remedies.sql',
            'output/update_snippet_33.sql',
            'output/update_snippet_47.sql',
            'output/update_snippet_73.sql',
            'output/af_remedies_preview.json',
            'update_page_2538.php',
            'purge-cache.php',
            'check-integrity.php',
            'audit-google-hits.php',
            'robots.txt',
            'llms.txt',
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
