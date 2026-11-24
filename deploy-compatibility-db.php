<?php
/**
 * Automated Database Migration & Data Population for Zodiac Compatibility
 * Usage: https://merarashifal.com/deploy-compatibility-db.php?token=mr_deploy_2026
 */

@ini_set('max_execution_time', 300);
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
    <title>Zodiac Compatibility Database Deployer</title>
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
    <h1>⚡ Zodiac Compatibility Database &amp; Snippet Deployer</h1>
    <p>Automated migration, 576-pair content population, and Snippet #28 deployment.</p>

    <div class="log-box">
    <?php
    // ---------------------------------------------------------
    // 1. LOCATE COMPATIBILITY TABLE
    // ---------------------------------------------------------
    $table_pref = $wpdb->prefix . 'zodiac_compatibility';
    $table_raw  = 'zodiac_compatibility';
    $target_table = null;

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_pref}'") === $table_pref) {
        $target_table = $table_pref;
    } elseif ($wpdb->get_var("SHOW TABLES LIKE '{$table_raw}'") === $table_raw) {
        $target_table = $table_raw;
    }

    if (!$target_table) {
        // Create table from scratch if neither exists
        $target_table = $table_pref;
        $create_sql = "CREATE TABLE `{$target_table}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sign_a` varchar(30) NOT NULL,
          `gender_a` varchar(10) NOT NULL,
          `sign_b` varchar(30) NOT NULL,
          `gender_b` varchar(10) NOT NULL,
          `language` varchar(10) NOT NULL,
          `slug` varchar(200) NOT NULL DEFAULT '',
          `element_pair` varchar(60) NOT NULL DEFAULT '',
          `meta_title` varchar(160) NOT NULL DEFAULT '',
          `meta_description` varchar(255) NOT NULL DEFAULT '',
          `verdict_summary` text DEFAULT NULL,
          `key_strength` text DEFAULT NULL,
          `key_challenge` text DEFAULT NULL,
          `faq_json` longtext DEFAULT NULL,
          `general_text` text DEFAULT NULL,
          `love_text` text DEFAULT NULL,
          `friendship_text` text DEFAULT NULL,
          `trust_text` text DEFAULT NULL,
          `dependability_text` text DEFAULT NULL,
          `longevity_text` text DEFAULT NULL,
          `marriage_text` text DEFAULT NULL,
          `kids_text` text DEFAULT NULL,
          `finance_text` text DEFAULT NULL,
          `score_general` decimal(3,1) DEFAULT NULL,
          `score_love` decimal(3,1) DEFAULT NULL,
          `score_friendship` decimal(3,1) DEFAULT NULL,
          `score_trust` decimal(3,1) DEFAULT NULL,
          `score_dependability` decimal(3,1) DEFAULT NULL,
          `score_longevity` decimal(3,1) DEFAULT NULL,
          `score_marriage` decimal(3,1) DEFAULT NULL,
          `score_kids` decimal(3,1) DEFAULT NULL,
          `score_finance` decimal(3,1) DEFAULT NULL,
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_slug_lang` (`slug`,`language`),
          KEY `idx_lookup` (`sign_a`,`gender_a`,`sign_b`,`gender_b`,`language`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $wpdb->query($create_sql);
        echo "<div class='log-success'>✅ Created new compatibility table: `{$target_table}`.</div>\n";
    } else {
        echo "<div class='log-info'>ℹ️ Found existing compatibility table: `{$target_table}`.</div>\n";
    }

    // ---------------------------------------------------------
    // 2. RUN SCHEMA MIGRATION (ADD MISSING COLUMNS & INDEX)
    // ---------------------------------------------------------
    $cols = $wpdb->get_col("SHOW COLUMNS FROM `{$target_table}`");
    $needed_cols = [
        'slug'             => "VARCHAR(200) NOT NULL DEFAULT '' AFTER `language`",
        'element_pair'     => "VARCHAR(60) NOT NULL DEFAULT '' AFTER `slug`",
        'meta_title'       => "VARCHAR(160) NOT NULL DEFAULT '' AFTER `element_pair`",
        'meta_description' => "VARCHAR(255) NOT NULL DEFAULT '' AFTER `meta_title`",
        'verdict_summary'  => "TEXT NULL AFTER `meta_description`",
        'key_strength'     => "TEXT NULL AFTER `verdict_summary`",
        'key_challenge'    => "TEXT NULL AFTER `key_strength`",
        'faq_json'         => "LONGTEXT NULL AFTER `key_challenge`",
        'updated_at'       => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `score_finance`",
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

    // Check Index
    $indexes = $wpdb->get_results("SHOW INDEX FROM `{$target_table}` WHERE Key_name = 'idx_slug_lang'", ARRAY_A);
    if (empty($indexes)) {
        $wpdb->query("ALTER TABLE `{$target_table}` ADD INDEX `idx_slug_lang` (`slug`, `language`)");
        echo "<div class='log-success'>✅ Added index `idx_slug_lang` (`slug`, `language`) to `{$target_table}`.</div>\n";
    }

    if ($altered === 0) {
        echo "<div class='log-info'>ℹ️ Schema is fully up to date on `{$target_table}`.</div>\n";
    }

    // ---------------------------------------------------------
    // 3. POPULATE 576 ROWS OF COMPATIBILITY DATA
    // ---------------------------------------------------------
    $sql_file = __DIR__ . '/output/populate_zodiac_compatibility.sql';
    if (!file_exists($sql_file)) {
        $sql_file = __DIR__ . '/populate_zodiac_compatibility.sql';
    }

    if (file_exists($sql_file)) {
        echo "<div class='log-info'>ℹ️ Reading population SQL file: " . esc_html(basename($sql_file)) . " (" . round(filesize($sql_file) / 1024 / 1024, 2) . " MB)...</div>\n";
        
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
                    if ($target_table !== 'zodiac_compatibility') {
                        $buffer = str_replace('`zodiac_compatibility`', "`{$target_table}`", $buffer);
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
            echo "<div class='log-err'>❌ Could not open SQL file for reading.</div>\n";
        }
    } else {
        echo "<div class='log-warn'>⚠️ SQL population file not found at: {$sql_file}</div>\n";
    }

    // ---------------------------------------------------------
    // 4. UPGRADE SNIPPET #28 IN DATABASE
    // ---------------------------------------------------------
    $snippets_table = $wpdb->prefix . 'snippets';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$snippets_table}'") === null) {
        $snippets_table = 'af_snippets';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '{$snippets_table}'") !== null) {
        $snip28_file = __DIR__ . '/snippet_28_new.php';
        if (!file_exists($snip28_file)) {
            $snip28_file = __DIR__ . '/scripts/snippet_28_zodiac_compatibility.php';
        }

        if (file_exists($snip28_file)) {
            $code28 = file_get_contents($snip28_file);
            $code28 = preg_replace('/^<\?php\s*/', '', $code28);
            $code28 = preg_replace('/\?>\s*$/', '', $code28);

            $existing28 = $wpdb->get_row("SELECT id FROM `{$snippets_table}` WHERE id = 28");
            if ($existing28) {
                $up28 = $wpdb->update($snippets_table, [
                    'name'   => 'Zodiac Compatibility Engine (V2 Bilingual)',
                    'code'   => $code28,
                    'status' => 1
                ], ['id' => 28]);
                echo "<div class='log-success'>✅ Snippet #28 updated in `{$snippets_table}` and activated.</div>\n";
            } else {
                $ins28 = $wpdb->insert($snippets_table, [
                    'id'     => 28,
                    'name'   => 'Zodiac Compatibility Engine (V2 Bilingual)',
                    'code'   => $code28,
                    'status' => 1
                ]);
                echo "<div class='log-success'>✅ Snippet #28 inserted into `{$snippets_table}` and activated.</div>\n";
            }
        } else {
            echo "<div class='log-warn'>⚠️ Snippet 28 source file not found.</div>\n";
        }
    }

    // ---------------------------------------------------------
    // 5. VERIFICATION QUERY
    // ---------------------------------------------------------
    $en_sample = $wpdb->get_row("SELECT id, slug, element_pair, meta_title, verdict_summary FROM `{$target_table}` WHERE sign_a='Aries' AND sign_b='Leo' AND language='en' LIMIT 1", ARRAY_A);
    $hi_sample = $wpdb->get_row("SELECT id, slug, element_pair, meta_title, verdict_summary FROM `{$target_table}` WHERE sign_a='Aries' AND sign_b='Leo' AND language='hi' LIMIT 1", ARRAY_A);

    if ($en_sample && !empty($en_sample['slug'])) {
        echo "<div class='log-success'>✅ English Row Verified (ID {$en_sample['id']}): {$en_sample['meta_title']} [{$en_sample['element_pair']}]</div>\n";
    } else {
        echo "<div class='log-warn'>⚠️ English sample row check incomplete.</div>\n";
    }

    if ($hi_sample && !empty($hi_sample['slug'])) {
        echo "<div class='log-success'>✅ Hindi Row Verified (ID {$hi_sample['id']}): {$hi_sample['meta_title']} [{$hi_sample['element_pair']}]</div>\n";
    } else {
        echo "<div class='log-warn'>⚠️ Hindi sample row check incomplete.</div>\n";
    }

    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$target_table}` WHERE slug != ''");
    echo "<div class='log-success'>📊 Total Fully Populated Compatibility Rows: <strong>{$total_count}</strong> / 576.</div>\n";

    // ---------------------------------------------------------
    // 6. CACHE FLUSH
    // ---------------------------------------------------------
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "<div class='log-success'>✅ PHP OPcache reset.</div>\n";
    }
    ?>
    </div>

    <div style="margin-top: 24px; display: flex; gap: 12px;">
        <a href="<?php echo esc_url(home_url('/compatibility-check/')); ?>" class="btn btn-home" target="_blank">View Compatibility Page &rarr;</a>
    </div>
</div>
</body>
</html>
