<?php
/**
 * Automated Database Migration, Content Population & Snippet Deployer for MeraRashifal Remedies
 * Usage: https://merarashifal.com/deploy-remedies-db.php?token=mr_deploy_2026
 *    or: https://beta.merarashifal.com/deploy-remedies-db.php?token=mr_deploy_2026
 */

@ini_set('max_execution_time', 600);
@ini_set('memory_limit', '512M');

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
    <title>Remedies Engine & Database Deployer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f1f5f9; padding: 24px; line-height: 1.6; }
        .card { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { color: #f59e0b; font-size: 22px; margin-top: 0; }
        .log-box { background: #020617; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 13px; max-height: 600px; overflow-y: auto; border: 1px solid #334155; margin-top: 16px; }
        .log-success { color: #34d399; margin-bottom: 6px; }
        .log-info { color: #60a5fa; margin-bottom: 6px; }
        .log-warn { color: #fcd34d; margin-bottom: 6px; }
        .log-err { color: #f87171; margin-bottom: 6px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 6px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; font-size: 14px; margin-top: 16px; }
        .btn-home { background: #d97706; color: #fff; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚡ Remedies Engine Database &amp; Snippets Deployer</h1>
    <p>Automated schema migration, 960-row content update, and Snippet #33 &amp; #73 deployment.</p>

    <div class="log-box">
    <?php
    // ---------------------------------------------------------
    // 1. LOCATE REMEDIES TABLE
    // ---------------------------------------------------------
    $table_pref = $wpdb->prefix . 'remedies';
    $table_raw  = 'af_remedies';
    $target_table = null;

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_raw}'") === $table_raw) {
        $target_table = $table_raw;
    } elseif ($wpdb->get_var("SHOW TABLES LIKE '{$table_pref}'") === $table_pref) {
        $target_table = $table_pref;
    }

    if (!$target_table) {
        echo "<div class='log-err'>❌ Could not find af_remedies or {$table_pref} table!</div>\n";
    } else {
        echo "<div class='log-info'>ℹ️ Found remedies table: `{$target_table}`.</div>\n";

        // ---------------------------------------------------------
        // 2. RUN SCHEMA MIGRATION (ADD MISSING COLUMNS & INDEXES)
        // ---------------------------------------------------------
        $cols = $wpdb->get_col("SHOW COLUMNS FROM `{$target_table}`");
        $needed_cols = [
            'slug'                  => "VARCHAR(220) NULL AFTER `language`",
            'issue_slug'            => "VARCHAR(80) NULL AFTER `slug`",
            'ruling_planet'         => "VARCHAR(80) NULL AFTER `issue_slug`",
            'meta_title'            => "VARCHAR(160) NULL AFTER `ruling_planet`",
            'meta_description'      => "VARCHAR(255) NULL AFTER `meta_title`",
            'quick_answer'          => "TEXT NULL AFTER `meta_description`",
            'action_checklist_json' => "JSON NULL AFTER `quick_answer`",
            'faq_json'              => "JSON NULL AFTER `action_checklist_json`",
        ];

        $altered = 0;
        foreach ($needed_cols as $col_name => $col_def) {
            if (!in_array($col_name, $cols, true)) {
                $alter_sql = "ALTER TABLE `{$target_table}` ADD COLUMN `{$col_name}` {$col_def}";
                $wpdb->query($alter_sql);
                echo "<div class='log-success'>✅ Added column `{$col_name}` to `{$target_table}`.</div>\n";
                $altered++;
            }
        }

        // Indexes
        $idx_slug = $wpdb->get_results("SHOW INDEX FROM `{$target_table}` WHERE Key_name = 'idx_slug_lang'", ARRAY_A);
        if (empty($idx_slug)) {
            $wpdb->query("ALTER TABLE `{$target_table}` ADD INDEX `idx_slug_lang` (`slug`, `language`)");
            echo "<div class='log-success'>✅ Added index `idx_slug_lang` (`slug`, `language`).</div>\n";
        }

        $idx_lookup = $wpdb->get_results("SHOW INDEX FROM `{$target_table}` WHERE Key_name = 'idx_af_remedies_lookup'", ARRAY_A);
        if (empty($idx_lookup)) {
            $wpdb->query("ALTER TABLE `{$target_table}` ADD INDEX `idx_af_remedies_lookup` (`sun_sign`, `gender`, `issue`, `language`)");
            echo "<div class='log-success'>✅ Added index `idx_af_remedies_lookup`.</div>\n";
        }

        if ($altered === 0) {
            echo "<div class='log-info'>ℹ️ Schema columns are up to date on `{$target_table}`.</div>\n";
        }

        // ---------------------------------------------------------
        // 3. POPULATE / UPDATE 960 ROWS OF REMEDIES DATA
        // ---------------------------------------------------------
        $sql_file = __DIR__ . '/output/update_af_remedies.sql';
        if (!file_exists($sql_file)) {
            $sql_file = __DIR__ . '/update_af_remedies.sql';
        }

        if (file_exists($sql_file)) {
            echo "<div class='log-info'>ℹ️ Reading remedies update SQL file: " . esc_html(basename($sql_file)) . " (" . round(filesize($sql_file) / 1024 / 1024, 2) . " MB)...</div>\n";

            $handle = fopen($sql_file, 'r');
            if ($handle) {
                $wpdb->query("START TRANSACTION");
                $stmts_run = 0;
                $buffer = '';

                while (($line = fgets($handle)) !== false) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || $trimmed === 'START TRANSACTION;' || $trimmed === 'COMMIT;') {
                        continue;
                    }
                    $buffer .= $line;
                    if (str_ends_with($trimmed, ';')) {
                        if ($target_table !== 'af_remedies') {
                            $buffer = str_replace('`af_remedies`', "`{$target_table}`", $buffer);
                        }
                        $res = $wpdb->query($buffer);
                        if ($res !== false) {
                            $stmts_run++;
                        }
                        $buffer = '';
                    }
                }
                fclose($handle);
                $wpdb->query("COMMIT");
                echo "<div class='log-success'>✅ Executed {$stmts_run} update statements on `{$target_table}`.</div>\n";
            } else {
                echo "<div class='log-err'>❌ Could not open remedies SQL file for reading.</div>\n";
            }
        } else {
            echo "<div class='log-warn'>⚠️ SQL update file not found at: {$sql_file}</div>\n";
        }

        $populated_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$target_table}` WHERE slug IS NOT NULL AND quick_answer IS NOT NULL");
        echo "<div class='log-success'>📊 Total Fully Updated Remedies Rows: <strong>{$populated_count}</strong>.</div>\n";
    }

    // ---------------------------------------------------------
    // 4. UPGRADE SNIPPET #33 (REMEDIES ENGINE) IN DATABASE
    // ---------------------------------------------------------
    $snippets_table = $wpdb->prefix . 'snippets';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$snippets_table}'") === null) {
        $snippets_table = 'af_snippets';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '{$snippets_table}'") !== null) {
        $snip33_file = __DIR__ . '/snippet_33_new.php';
        if (file_exists($snip33_file)) {
            $code33 = file_get_contents($snip33_file);
            $code33 = preg_replace('/^<\?php\s*/', '', $code33);
            $code33 = preg_replace('/\?>\s*$/', '', $code33);

            $existing33 = $wpdb->get_row("SELECT id FROM `{$snippets_table}` WHERE id = 33");
            if ($existing33) {
                $up33 = $wpdb->update($snippets_table, [
                    'name'   => 'Remedy Viewer Handler',
                    'code'   => $code33,
                    'active' => 1,
                    'scope'  => 'global'
                ], ['id' => 33]);
                echo "<div class='log-success'>✅ Snippet #33 updated in `{$snippets_table}` and activated. Result: " . var_export($up33, true) . "</div>\n";
            } else {
                $ins33 = $wpdb->insert($snippets_table, [
                    'id'     => 33,
                    'name'   => 'Remedy Viewer Handler',
                    'code'   => $code33,
                    'active' => 1,
                    'scope'  => 'global'
                ]);
                echo "<div class='log-success'>✅ Snippet #33 inserted into `{$snippets_table}` and activated. Result: " . var_export($ins33, true) . "</div>\n";
            }
        }

        // ---------------------------------------------------------
        // 5. UPGRADE SNIPPET #73 (KUNDALI ENGINE CROSS-SELL) IN DATABASE
        // ---------------------------------------------------------
        $snip73_file = __DIR__ . '/snippet_73_new.php';
        if (file_exists($snip73_file)) {
            $code73 = file_get_contents($snip73_file);
            $code73 = preg_replace('/^<\?php\s*/', '', $code73);
            $code73 = preg_replace('/\?>\s*$/', '', $code73);

            $existing73 = $wpdb->get_row("SELECT id FROM `{$snippets_table}` WHERE id = 73");
            if ($existing73) {
                $up73 = $wpdb->update($snippets_table, [
                    'name'   => 'Kundali Engine',
                    'code'   => $code73,
                    'active' => 1,
                    'scope'  => 'global'
                ], ['id' => 73]);
                echo "<div class='log-success'>✅ Snippet #73 updated in `{$snippets_table}` and activated. Result: " . var_export($up73, true) . "</div>\n";
            }
        }

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            echo "<div class='log-success'>✅ WordPress object cache flushed.</div>\n";
        }
    }

    // ---------------------------------------------------------
    // 6. OPCACHE RESET
    // ---------------------------------------------------------
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "<div class='log-success'>✅ PHP OPcache reset.</div>\n";
    }

    // ---------------------------------------------------------
    // 7. PURGE LITESPEED / CLOUDFLARE CACHE IF HOOKS AVAILABLE
    // ---------------------------------------------------------
    if (has_action('litespeed_purge_all')) {
        do_action('litespeed_purge_all');
        echo "<div class='log-success'>✅ LiteSpeed Cache purged.</div>\n";
    }
    ?>
    </div>

    <div style="margin-top: 24px; display: flex; gap: 12px;">
        <a href="<?php echo esc_url(home_url('/remedies-astrological-emotional-sociological-solutions/')); ?>" class="btn btn-home" target="_blank">View Live Remedies Page &rarr;</a>
    </div>
</div>
</body>
</html>
