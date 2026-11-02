<?php
/**
 * Automated Database URL Normalizer & Elementor Cache Cleaner
 * Run once via browser: https://beta.merarashifal.com/fix-urls.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options') && !isset($_GET['run_fix'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fix URLs</title><style>body{font-family:sans-serif;padding:40px;line-height:1.6;max-width:600px;margin:auto;}</style></head><body>';
    echo '<h2>MeraRashifal Automated URL Normalizer</h2>';
    echo '<p>This will automatically replace all <code>localhost/merarashifal</code> URLs in the database with <strong>' . esc_html($_SERVER['HTTP_HOST']) . '</strong> and regenerate Elementor styling.</p>';
    echo '<p><a href="?run_fix=1" style="display:inline-block;background:#964900;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Run Automated URL Fix Now</a></p>';
    echo '</body></html>';
    exit;
}

global $wpdb;

$old_url_http  = 'http://localhost/merarashifal';
$old_url_https = 'https://localhost/merarashifal';
$new_url       = (is_ssl() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

$tables = [
    $wpdb->prefix . 'options'     => ['option_value'],
    $wpdb->prefix . 'posts'       => ['post_content', 'post_excerpt', 'guid'],
    $wpdb->prefix . 'postmeta'    => ['meta_value'],
    $wpdb->prefix . 'comments'    => ['comment_content'],
    $wpdb->prefix . 'commentmeta' => ['meta_value'],
];

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>URL Fix Running...</title><style>body{font-family:monospace;padding:30px;background:#1e1e1e;color:#85e89d;line-height:1.5;}</style></head><body>';
echo "<h3>Target: Replacing '{$old_url_http}' with '{$new_url}'</h3>\n";

// Update siteurl and home directly
update_option('siteurl', $new_url);
update_option('home', $new_url);
echo "✓ Options siteurl and home updated to: {$new_url}<br>\n";

// Function to recursively replace inside serialized data
function recursive_unserialize_replace($from, $to, $data, $serialised = false) {
    try {
        if (is_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                $data = recursive_unserialize_replace($from, $to, $unserialized, true);
            } else {
                $data = str_replace($from, $to, $data);
            }
        } elseif (is_array($data)) {
            $_tmp = [];
            foreach ($data as $key => $value) {
                $_tmp[$key] = recursive_unserialize_replace($from, $to, $value, false);
            }
            $data = $_tmp;
        } elseif (is_object($data)) {
            $_tmp = $data;
            $props = get_object_vars($data);
            foreach ($props as $key => $value) {
                $_tmp->$key = recursive_unserialize_replace($from, $to, $value, false);
            }
            $data = $_tmp;
        }
        if ($serialised) {
            return serialize($data);
        }
    } catch (\Exception $error) {
        // Fallback
    }
    return $data;
}

// Process tables
foreach ($tables as $table => $columns) {
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
    if (!$exists) continue;

    $primary_key = 'id';
    $cols = $wpdb->get_results("SHOW COLUMNS FROM `{$table}`");
    foreach ($cols as $col) {
        if ($col->Key === 'PRI') {
            $primary_key = $col->Field;
            break;
        }
    }

    $count = 0;
    foreach ($columns as $column) {
        // Simple replace for un-serialized columns
        $wpdb->query("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, '{$old_url_http}', '{$new_url}') WHERE `{$column}` LIKE '%{$old_url_http}%'");
        $wpdb->query("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, '{$old_url_https}', '{$new_url}') WHERE `{$column}` LIKE '%{$old_url_https}%'");

        // Handle serialized strings (e.g. Elementor data and widget settings)
        $rows = $wpdb->get_results("SELECT `{$primary_key}`, `{$column}` FROM `{$table}` WHERE `{$column}` LIKE '%s:%'");
        foreach ($rows as $row) {
            $original = $row->$column;
            $edited = recursive_unserialize_replace($old_url_http, $new_url, $original);
            $edited = recursive_unserialize_replace($old_url_https, $new_url, $edited);
            if ($original !== $edited) {
                $wpdb->update($table, [$column => $edited], [$primary_key => $row->$primary_key]);
                $count++;
            }
        }
    }
    echo "✓ Processed {$table} (Serialized blocks fixed: {$count})<br>\n";
}

// Clear Elementor CSS cache
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    echo "✓ Elementor CSS Cache Cleared & Regenerated<br>\n";
}

echo "<br><strong style='color:#fff;'>All URLs have been automatically replaced!</strong><br>";
echo "<p><a href='" . esc_url($new_url) . "' style='color:#58a6ff;'>Go to Homepage</a> | <a href='?delete_self=1' style='color:#f85149;'>Delete this fix-urls.php script</a></p>";

if (isset($_GET['delete_self'])) {
    @unlink(__FILE__);
    echo "✓ fix-urls.php deleted safely.<br>";
}

echo '</body></html>';
