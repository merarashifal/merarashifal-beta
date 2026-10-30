<?php
/**
 * Niyati Storage Engine: Disk-Based File Storage & Archiving
 *
 * Stores all chat conversations and family Kundli profiles on server disk as structured
 * JSON files rather than cluttering the MySQL database.
 * Supports cross-device sync, rolling session buffers, Gzip archival, and automated cleanup.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Storage {

    const BASE_FOLDER    = 'niyati-data';
    const KUNDLI_FOLDER  = 'kundli';
    const CHATS_FOLDER   = 'chats';
    const ARCHIVE_FOLDER = 'archive';

    const MAX_ACTIVE_MESSAGES = 60; // Max messages retained in active session file before rolling

    /**
     * Get root storage directory path on server disk
     */
    public static function get_base_dir() {
        $upload_dir = wp_upload_dir();
        $base = trailingslashit($upload_dir['basedir']) . self::BASE_FOLDER;
        if (!file_exists($base)) {
            wp_mkdir_p($base);
            self::protect_directory($base);
        }
        return $base;
    }

    /**
     * Get subfolder path (kundli, chats, archive)
     */
    public static function get_sub_dir($folder) {
        $base = self::get_base_dir();
        $sub = trailingslashit($base) . $folder;
        if (!file_exists($sub)) {
            wp_mkdir_p($sub);
        }
        return $sub;
    }

    /**
     * Secure directory with .htaccess and empty index.php to prevent direct web HTTP access
     * Note: PHP scripts on server can still read and write freely.
     */
    public static function protect_directory($dir) {
        $htaccess = trailingslashit($dir) . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "# Deny direct HTTP access to Niyati private data\n"
                     . "<IfModule mod_authz_core.c>\n"
                     . "    Require all denied\n"
                     . "</IfModule>\n"
                     . "<IfModule !mod_authz_core.c>\n"
                     . "    Order deny,allow\n"
                     . "    Deny from all\n"
                     . "</IfModule>\n";
            @file_put_contents($htaccess, $content);
        }

        $index_php = trailingslashit($dir) . 'index.php';
        if (!file_exists($index_php)) {
            @file_put_contents($index_php, "<?php\n// Silence is golden.\n");
        }
    }

    /* =========================================================================
     * 1. FAMILY KUNDLI PROFILES STORAGE (Single JSON per user on server disk)
     * ========================================================================= */

    /**
     * Get deterministic profile hash for a specific birth profile
     */
    public static function get_profile_hash($birth_data) {
        $name = strtolower(trim($birth_data['name'] ?? 'user'));
        $dob  = trim($birth_data['dob'] ?? '');
        $tob  = trim($birth_data['tob'] ?? '12:00');
        $lat  = round(floatval($birth_data['lat'] ?? 28.6139), 3);
        $lon  = round(floatval($birth_data['lon'] ?? 77.2090), 3);
        return md5("{$name}_{$dob}_{$tob}_{$lat}_{$lon}");
    }

    /**
     * Get user Kundlis file path
     */
    protected static function get_kundli_file_path($user_id) {
        $dir = self::get_sub_dir(self::KUNDLI_FOLDER);
        return trailingslashit($dir) . 'user_' . absint($user_id) . '_kundlis.json';
    }

    /**
     * Retrieve all saved family Kundlis for a logged-in user
     *
     * @param int $user_id
     * @param int  $user_id
     * @param bool $keyed If true, returns associative array keyed by hash. Default false (sequential array).
     * @return array Array of profiles
     */
    public static function get_saved_kundlis($user_id, $keyed = false) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return [];
        }

        $file = self::get_kundli_file_path($user_id);
        if (!file_exists($file)) {
            return [];
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return [];
        }

        $json = json_decode($raw, true);
        if (!is_array($json) || empty($json['profiles'])) {
            return [];
        }

        return $keyed ? $json['profiles'] : array_values($json['profiles']);
    }

    /**
     * Look up an existing family member's Kundli profile for a user
     *
     * @param int $user_id
     * @param array $birth_data
     * @return array|null Returns profile array if found, null if not
     */
    public static function find_saved_kundli($user_id, $birth_data) {
        $profiles = self::get_saved_kundlis($user_id, true);
        if (empty($profiles)) {
            return null;
        }

        $hash = self::get_profile_hash($birth_data);
        if (isset($profiles[$hash])) {
            return $profiles[$hash];
        }

        return null;
    }

    /**
     * Save or update a family member's Kundli profile in the user's JSON file
     *
     * @param int $user_id
     * @param array $birth_data
     * @param array $profile_data
     * @return bool
     */
    public static function save_kundli_profile($user_id, $birth_data, $profile_data) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }

        $file = self::get_kundli_file_path($user_id);
        $data = [
            'user_id'    => $user_id,
            'updated_at' => time(),
            'profiles'   => []
        ];

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) {
                $existing = json_decode($raw, true);
                if (is_array($existing) && !empty($existing['profiles'])) {
                    $data['profiles'] = $existing['profiles'];
                }
            }
        }

        $hash = self::get_profile_hash($birth_data);
        $profile_entry = array_merge($birth_data, $profile_data);
        $profile_entry['profile_hash'] = $hash;
        $profile_entry['cached_at']    = time();

        $data['profiles'][$hash] = $profile_entry;
        $data['updated_at']      = time();

        return (bool) @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /* =========================================================================
     * 2. CHAT STORAGE & CROSS-DEVICE SYNC (Single file on disk per user)
     * ========================================================================= */

    /**
     * Get active chat file path for a user
     */
    protected static function get_chat_file_path($user_id) {
        $dir = self::get_sub_dir(self::CHATS_FOLDER);
        return trailingslashit($dir) . 'user_' . absint($user_id) . '.json';
    }

    /**
     * Load active chat conversation for a user
     *
     * @param int $user_id
     * @return array [success => bool, messages => array, chart_token => string, updated_at => int]
     */
    public static function get_active_chat($user_id) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return ['success' => false, 'messages' => [], 'chart_token' => ''];
        }

        $file = self::get_chat_file_path($user_id);
        if (!file_exists($file)) {
            return ['success' => false, 'messages' => [], 'chart_token' => ''];
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return ['success' => false, 'messages' => [], 'chart_token' => ''];
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return ['success' => false, 'messages' => [], 'chart_token' => ''];
        }

        return [
            'success'             => true,
            'messages'            => $json['messages'] ?? [],
            'chart_token'         => $json['chart_token'] ?? '',
            'active_profile_hash' => $json['active_profile_hash'] ?? '',
            'active_profile_name' => $json['active_profile_name'] ?? '',
            'updated_at'          => $json['updated_at'] ?? 0,
        ];
    }

    /**
     * Initialize or update active chat metadata (e.g. when Kundli is calculated or greeting sent)
     */
    public static function set_active_chat_session($user_id, $chart_token, $profile_hash = '', $profile_name = '', $initial_greeting = '') {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }

        $file = self::get_chat_file_path($user_id);
        $data = [
            'user_id'             => $user_id,
            'chart_token'         => $chart_token,
            'active_profile_hash' => $profile_hash,
            'active_profile_name' => $profile_name,
            'created_at'          => time(),
            'updated_at'          => time(),
            'messages'            => []
        ];

        if (!empty($initial_greeting)) {
            $data['messages'][] = [
                'role'      => 'model',
                'text'      => $initial_greeting,
                'timestamp' => time()
            ];
        }

        return (bool) @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * Append user prompt and Gemini reply to user's active chat file
     *
     * @param int $user_id
     * @param string $user_message
     * @param string $bot_reply
     * @param array $extra_meta
     * @return bool
     */
    public static function append_chat_turn($user_id, $user_message, $bot_reply, $extra_meta = []) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }

        $file = self::get_chat_file_path($user_id);
        $data = [
            'user_id'    => $user_id,
            'created_at' => time(),
            'updated_at' => time(),
            'messages'   => []
        ];

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) {
                $existing = json_decode($raw, true);
                if (is_array($existing)) {
                    $data = $existing;
                }
            }
        }

        $now = time();
        if (!empty($user_message)) {
            $data['messages'][] = [
                'role'      => 'user',
                'text'      => $user_message,
                'timestamp' => $now
            ];
        }

        if (!empty($bot_reply)) {
            $model_turn = [
                'role'      => 'model',
                'text'      => $bot_reply,
                'timestamp' => $now
            ];
            if (!empty($extra_meta['order_btn'])) {
                $model_turn['order_btn'] = $extra_meta['order_btn'];
            }
            $data['messages'][] = $model_turn;
        }

        // Rolling buffer: if messages exceed MAX_ACTIVE_MESSAGES, keep only the latest turns
        if (count($data['messages']) > self::MAX_ACTIVE_MESSAGES) {
            $data['messages'] = array_slice($data['messages'], -self::MAX_ACTIVE_MESSAGES);
        }

        $data['updated_at'] = $now;

        return (bool) @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /* =========================================================================
     * 3. CHAT ARCHIVING & COMPRESSION LIFECYCLE (Gzip .json.gz)
     * ========================================================================= */

    /**
     * Archive an active chat session to a compressed .json.gz file and reset active session
     *
     * @param int $user_id
     * @return string|false Return archive file path if created, false on error
     */
    public static function archive_active_chat($user_id) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }

        $file = self::get_chat_file_path($user_id);
        if (!file_exists($file)) {
            return false;
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return false;
        }

        $json = json_decode($raw, true);
        if (!is_array($json) || empty($json['messages'])) {
            @unlink($file);
            return false;
        }

        // Year/Month archive directory
        $archive_base = self::get_sub_dir(self::ARCHIVE_FOLDER);
        $year_month_dir = trailingslashit($archive_base) . date('Y/m');
        if (!file_exists($year_month_dir)) {
            wp_mkdir_p($year_month_dir);
        }

        $timestamp = date('Ymd_His');
        $archive_file = trailingslashit($year_month_dir) . "user_{$user_id}_{$timestamp}.json.gz";

        // Compress using level 9 Gzip
        $compressed = gzencode($raw, 9);
        if ($compressed) {
            @file_put_contents($archive_file, $compressed, LOCK_EX);
        }

        // Delete active file so user starts fresh next session
        @unlink($file);

        return $archive_file;
    }

    /**
     * Automated WP-Cron Cleanup: Purge archived .json.gz files older than retention threshold
     *
     * @param int $retention_days Default 90 days
     * @return int Number of purged files
     */
    public static function cleanup_old_archives($retention_days = 90) {
        $retention_days = max(7, absint($retention_days));
        $cutoff_time = time() - ($retention_days * DAY_IN_SECONDS);

        $archive_base = self::get_sub_dir(self::ARCHIVE_FOLDER);
        if (!file_exists($archive_base)) {
            return 0;
        }

        $deleted_count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($archive_base, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && substr($item->getFilename(), -8) === '.json.gz') {
                if ($item->getMTime() < $cutoff_time) {
                    @unlink($item->getRealPath());
                    $deleted_count++;
                }
            } elseif ($item->isDir()) {
                // Remove empty directory
                @rmdir($item->getRealPath());
            }
        }

        return $deleted_count;
    }
}
