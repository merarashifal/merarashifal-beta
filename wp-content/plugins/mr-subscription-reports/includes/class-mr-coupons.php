<?php
/**
 * MeraRashifal Universal Coupon & Discount Engine
 *
 * Handles flat and percentage discounts on pre-tax values,
 * strict product-wise / category scoping (reports, chat credits, subscriptions, extensible),
 * first-come first-served (FCFS) global limits (e.g. 100 redemptions),
 * per-user limits, and atomic concurrency-safe commit.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Coupons {

    const TABLE_COUPONS = 'mr_coupons';
    const TABLE_REDEMPTIONS = 'mr_coupon_redemptions';

    protected static $tables_checked = false;

    /**
     * Create required database tables with idempotent checks
     */
    public static function create_tables() {
        if (self::$tables_checked || get_option('mr_coupons_db_v1') === 'yes') {
            return;
        }
        self::$tables_checked = true;

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_coupons = $wpdb->prefix . self::TABLE_COUPONS;
        $table_redemptions = $wpdb->prefix . self::TABLE_REDEMPTIONS;

        // 1. Coupons master table
        $sql_coupons = "CREATE TABLE IF NOT EXISTS {$table_coupons} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            code VARCHAR(50) NOT NULL,
            title VARCHAR(150) NOT NULL,
            description TEXT DEFAULT NULL,
            discount_type ENUM('flat', 'percent') NOT NULL DEFAULT 'percent',
            discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            max_discount_cap DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            min_order_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            product_scope VARCHAR(50) NOT NULL DEFAULT 'all',
            eligible_items LONGTEXT DEFAULT NULL,
            excluded_items LONGTEXT DEFAULT NULL,
            total_usage_limit INT(11) NOT NULL DEFAULT 0,
            total_redeemed_count INT(11) NOT NULL DEFAULT 0,
            per_user_limit INT(11) NOT NULL DEFAULT 1,
            starts_at DATETIME DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            status ENUM('active', 'paused', 'expired') NOT NULL DEFAULT 'active',
            is_secret TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code_unique (code),
            KEY status_dates (status, starts_at, expires_at),
            KEY product_scope (product_scope),
            KEY is_secret (is_secret)
        ) {$charset_collate};";

        // 2. Redemptions audit & per-user tracking table
        $sql_redemptions = "CREATE TABLE IF NOT EXISTS {$table_redemptions} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            coupon_id BIGINT(20) UNSIGNED NOT NULL,
            coupon_code VARCHAR(50) NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            user_email VARCHAR(150) NOT NULL DEFAULT '',
            user_phone VARCHAR(30) NOT NULL DEFAULT '',
            internal_txn_id VARCHAR(64) NOT NULL,
            gateway_payment_id VARCHAR(100) DEFAULT '',
            subtotal_pretax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount_applied DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            product_type VARCHAR(40) NOT NULL DEFAULT 'reports',
            redeemed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY coupon_id (coupon_id),
            KEY coupon_code (coupon_code),
            KEY user_id (user_id),
            KEY user_email (user_email),
            KEY user_phone (user_phone),
            KEY internal_txn_id (internal_txn_id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_coupons);
        dbDelta($sql_redemptions);

        // Ensure is_secret column exists on existing installations
        self::ensure_secret_column();

        // Seed initial sample coupons if table is empty
        self::seed_default_coupons();

        // Ensure active secret test coupon exists
        self::get_or_create_secret_test_coupon();

        update_option('mr_coupons_db_v1', 'yes');
    }

    /**
     * Ensure is_secret column exists on the coupons table
     */
    public static function ensure_secret_column() {
        global $wpdb;
        $table_coupons = $wpdb->prefix . self::TABLE_COUPONS;
        $existing_cols = $wpdb->get_col("DESC {$table_coupons}", 0);
        if (!empty($existing_cols) && !in_array('is_secret', $existing_cols, true)) {
            $wpdb->query("ALTER TABLE {$table_coupons} ADD COLUMN is_secret tinyint(1) NOT NULL DEFAULT 0 AFTER status, ADD KEY is_secret (is_secret)");
        }
    }

    /**
     * Seed starter coupons for launch
     */
    protected static function seed_default_coupons() {
        global $wpdb;
        $table_coupons = $wpdb->prefix . self::TABLE_COUPONS;
        $count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table_coupons}");
        if ($count > 0) {
            return;
        }

        // 1. JYOTISH25: 25% off on Reports (100 uses max, 1 per user)
        $wpdb->insert($table_coupons, [
            'code'                 => 'JYOTISH25',
            'title'                => '25% Off on Astrology Reports',
            'description'          => 'Enjoy 25% instant discount on any Vedic or Western astrology report.',
            'discount_type'        => 'percent',
            'discount_value'       => 25.00,
            'max_discount_cap'     => 150.00,
            'min_order_value'      => 99.00,
            'product_scope'        => 'reports',
            'total_usage_limit'    => 100,
            'total_redeemed_count' => 0,
            'per_user_limit'       => 1,
            'status'               => 'active',
            'created_at'           => current_time('mysql'),
        ]);

        // 2. FLAT50: Flat ₹50 off on orders >= ₹199 (100 uses max, 1 per user)
        $wpdb->insert($table_coupons, [
            'code'                 => 'FLAT50',
            'title'                => 'Flat ₹50 Off Celebration Offer',
            'description'          => 'Get ₹50 flat discount on orders of ₹199 or more.',
            'discount_type'        => 'flat',
            'discount_value'       => 50.00,
            'max_discount_cap'     => 50.00,
            'min_order_value'      => 199.00,
            'product_scope'        => 'all',
            'total_usage_limit'    => 100,
            'total_redeemed_count' => 0,
            'per_user_limit'       => 1,
            'status'               => 'active',
            'created_at'           => current_time('mysql'),
        ]);

        // 3. CHAT20: 20% off on Niyati Chat Credits (100 uses max)
        $wpdb->insert($table_coupons, [
            'code'                 => 'CHAT20',
            'title'                => '20% Off Niyati AI Chat Tokens',
            'description'          => 'Recharge Niyati chat tokens with 20% savings.',
            'discount_type'        => 'percent',
            'discount_value'       => 20.00,
            'max_discount_cap'     => 100.00,
            'min_order_value'      => 80.00,
            'product_scope'        => 'chat_credits',
            'total_usage_limit'    => 100,
            'total_redeemed_count' => 0,
            'per_user_limit'       => 1,
            'status'               => 'active',
            'created_at'           => current_time('mysql'),
        ]);
    }

    /**
     * Get list of supported discountable product types (extensible)
     */
    public static function get_supported_product_types() {
        $types = [
            'all'           => __('All Products', 'merarashifal'),
            'reports'       => __('Astrological Reports & Suites', 'merarashifal'),
            'chat_credits'  => __('Niyati AI Chat Credits', 'merarashifal'),
            'subscriptions' => __('Subscription Passes & Packs', 'merarashifal'),
        ];
        return apply_filters('mr_discountable_product_types', $types);
    }

    /**
     * Look up coupon by code
     *
     * @param string $code
     * @return array|null
     */
    public static function get_coupon_by_code($code) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        $clean_code = strtoupper(trim(sanitize_text_field($code)));
        if (empty($clean_code)) {
            return null;
        }

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE UPPER(code) = %s LIMIT 1",
            $clean_code
        ), ARRAY_A);

        return $row ?: null;
    }

    /**
     * Look up coupon by ID
     */
    public static function get_coupon_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        $id = (int)$id;
        if ($id <= 0) return null;

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
        return $row ?: null;
    }

    /**
     * Validate Coupon & Calculate Pre-Tax Discount
     *
     * @param string $code
     * @param array $context [
     *      'subtotal_pretax' => float,
     *      'product_type'    => string ('reports'|'chat_credits'|'subscriptions'),
     *      'item_slug'       => string,
     *      'cart_items'      => array,
     *      'user_id'         => int,
     *      'user_email'      => string,
     *      'user_phone'      => string,
     * ]
     * @return array Validation result [
     *      'valid'           => bool,
     *      'message'         => string,
     *      'discount_amount' => float,
     *      'new_subtotal'    => float,
     *      'coupon'          => array|null
     * ]
     */
    public static function validate_coupon($code, $context = []) {
        $code = strtoupper(trim(sanitize_text_field($code)));
        if (empty($code)) {
            return [
                'valid'   => false,
                'message' => __('Please enter a valid coupon code.', 'merarashifal'),
            ];
        }

        $coupon = self::get_coupon_by_code($code);
        if (!$coupon) {
            return [
                'valid'   => false,
                'message' => __('Invalid coupon code. Please verify and try again.', 'merarashifal'),
            ];
        }

        // 1. Status Check
        if ($coupon['status'] !== 'active') {
            return [
                'valid'   => false,
                'message' => __('This coupon code is no longer active.', 'merarashifal'),
            ];
        }

        // 2. Date Range Check
        $now = current_time('mysql');
        if (!empty($coupon['starts_at']) && $coupon['starts_at'] > $now) {
            return [
                'valid'   => false,
                'message' => __('This promotional coupon has not started yet.', 'merarashifal'),
            ];
        }
        if (!empty($coupon['expires_at']) && $coupon['expires_at'] < $now) {
            return [
                'valid'   => false,
                'message' => __('This coupon code has expired.', 'merarashifal'),
            ];
        }

        // 3. Global Redemption Limit (First-Come, First-Served)
        $limit = (int)$coupon['total_usage_limit'];
        $used  = (int)$coupon['total_redeemed_count'];
        if ($limit > 0 && $used >= $limit) {
            return [
                'valid'   => false,
                'message' => sprintf(
                    __('This coupon code was limited to %d redemptions and is now fully claimed (first-come, first-served basis).', 'merarashifal'),
                    $limit
                ),
            ];
        }

        // 4. Per-User Usage Limit Check
        $per_user_limit = (int)$coupon['per_user_limit'];
        if ($per_user_limit > 0) {
            $user_id    = (int)($context['user_id'] ?? get_current_user_id());
            $user_email = sanitize_email($context['user_email'] ?? '');
            $user_phone = sanitize_text_field($context['user_phone'] ?? '');

            $user_used = self::get_user_redemption_count($coupon['id'], $user_id, $user_email, $user_phone);
            if ($user_used >= $per_user_limit) {
                return [
                    'valid'   => false,
                    'message' => sprintf(
                        __('You have already redeemed this coupon code the maximum allowed number of times (%d time%s).', 'merarashifal'),
                        $per_user_limit,
                        $per_user_limit > 1 ? 's' : ''
                    ),
                ];
            }
        }

        // 5. Product Scope Check
        $req_scope    = $coupon['product_scope'];
        $cart_product = sanitize_key($context['product_type'] ?? 'reports');
        if ($req_scope !== 'all' && $req_scope !== $cart_product) {
            $type_names = self::get_supported_product_types();
            $scope_name = $type_names[$req_scope] ?? $req_scope;
            return [
                'valid'   => false,
                'message' => sprintf(
                    __('This coupon code is valid only for %s.', 'merarashifal'),
                    $scope_name
                ),
            ];
        }

        // 5b. Specific Item Inclusions / Exclusions
        $item_slug = sanitize_key($context['item_slug'] ?? '');
        if (!empty($coupon['eligible_items'])) {
            $eligible = json_decode($coupon['eligible_items'], true);
            if (is_array($eligible) && !empty($eligible) && !empty($item_slug)) {
                if (!in_array($item_slug, $eligible, true)) {
                    return [
                        'valid'   => false,
                        'message' => __('This coupon code is not applicable to the selected item.', 'merarashifal'),
                    ];
                }
            }
        }

        if (!empty($coupon['excluded_items'])) {
            $excluded = json_decode($coupon['excluded_items'], true);
            if (is_array($excluded) && !empty($excluded) && !empty($item_slug)) {
                if (in_array($item_slug, $excluded, true)) {
                    return [
                        'valid'   => false,
                        'message' => __('This coupon code cannot be used with this item.', 'merarashifal'),
                    ];
                }
            }
        }

        $is_secret = !empty($coupon['is_secret']);

        // 6. Minimum Pre-Tax Order Value Check (Bypassed for secret test promo)
        $subtotal = floatval($context['subtotal_pretax'] ?? 0);
        $min_val  = floatval($coupon['min_order_value']);
        if (!$is_secret && $min_val > 0 && $subtotal < $min_val) {
            return [
                'valid'   => false,
                'message' => sprintf(
                    __('Minimum order value of ₹%s is required to apply this coupon.', 'merarashifal'),
                    number_format($min_val, 2)
                ),
            ];
        }

        // 7. Calculate Discount on Pre-Tax Subtotal
        $discount_type  = $coupon['discount_type'];
        $discount_value = floatval($coupon['discount_value']);
        $max_cap        = floatval($coupon['max_discount_cap']);
        $discount_amt   = 0.00;

        if ($is_secret || ($discount_type === 'percent' && $discount_value >= 100.0)) {
            // Secret testing promo: adjust discount so payable total is exactly ₹1.00
            $discount_amt = max(0.00, $subtotal - 1.00);
            $new_subtotal = max(1.00, round($subtotal - $discount_amt, 2));
            $msg = sprintf(
                __('Secret Test Promo "%s" applied! Order total adjusted to ₹1.00 for live payment gateway verification.', 'merarashifal'),
                $coupon['code']
            );
        } else {
            if ($discount_type === 'flat') {
                $discount_amt = min($discount_value, $subtotal);
            } elseif ($discount_type === 'percent') {
                $raw_discount = ($subtotal * $discount_value) / 100.0;
                if ($max_cap > 0) {
                    $raw_discount = min($raw_discount, $max_cap);
                }
                $discount_amt = min($raw_discount, $subtotal);
            }

            $discount_amt = round($discount_amt, 2);
            $new_subtotal = max(0.00, round($subtotal - $discount_amt, 2));

            $savings_str = '₹' . number_format($discount_amt, 2);
            $msg = sprintf(
                __('Coupon "%s" applied! You saved %s on your order.', 'merarashifal'),
                $coupon['code'],
                $savings_str
            );
        }

        return [
            'valid'           => true,
            'message'         => $msg,
            'coupon_id'       => (int)$coupon['id'],
            'coupon_code'     => $coupon['code'],
            'title'           => $coupon['title'],
            'discount_type'   => $discount_type,
            'discount_value'  => $discount_value,
            'discount_amount' => $discount_amt,
            'new_subtotal'    => $new_subtotal,
            'is_secret'       => $is_secret ? 1 : 0,
            'coupon'          => $coupon,
        ];
    }

    /**
     * Get how many times a user (by ID, email or phone) has redeemed a coupon
     */
    public static function get_user_redemption_count($coupon_id, $user_id = 0, $email = '', $phone = '') {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_REDEMPTIONS;
        $coupon_id = (int)$coupon_id;

        $clauses = [];
        $params  = [$coupon_id];

        if ($user_id > 0) {
            $clauses[] = "user_id = %d";
            $params[]  = $user_id;
        }
        if (!empty($email)) {
            $clauses[] = "LOWER(user_email) = %s";
            $params[]  = strtolower(trim($email));
        }
        if (!empty($phone)) {
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($clean_phone) >= 10) {
                $clauses[] = "user_phone LIKE %s";
                $params[]  = '%' . substr($clean_phone, -10);
            }
        }

        if (empty($clauses)) {
            return 0;
        }

        $where_or = implode(' OR ', $clauses);
        $sql = "SELECT COUNT(*) FROM {$table} WHERE coupon_id = %d AND ({$where_or})";

        return (int)$wpdb->get_var($wpdb->prepare($sql, $params));
    }

    /**
     * Concurrency-Safe Atomic Redemption Commit (First-Come, First-Served Guard)
     *
     * @param int $coupon_id
     * @param array $redemption_data [
     *      'user_id'             => int,
     *      'user_email'          => string,
     *      'user_phone'          => string,
     *      'internal_txn_id'     => string,
     *      'gateway_payment_id'  => string,
     *      'subtotal_pretax'     => float,
     *      'discount_applied'    => float,
     *      'tax_amount'          => float,
     *      'total_paid'          => float,
     *      'product_type'        => string
     * ]
     * @return bool True if committed successfully, false if usage limit reached or failure
     */
    public static function commit_redemption($coupon_id, $redemption_data) {
        global $wpdb;
        $table_coupons     = $wpdb->prefix . self::TABLE_COUPONS;
        $table_redemptions = $wpdb->prefix . self::TABLE_REDEMPTIONS;
        $coupon_id         = (int)$coupon_id;

        if ($coupon_id <= 0) {
            return false;
        }

        // Atomic increment protected by row condition:
        // total_redeemed_count is incremented ONLY IF total_usage_limit = 0 OR total_redeemed_count < total_usage_limit
        $sql = "UPDATE {$table_coupons} 
                SET total_redeemed_count = total_redeemed_count + 1 
                WHERE id = %d 
                  AND (total_usage_limit = 0 OR total_redeemed_count < total_usage_limit)";
        
        $updated = $wpdb->query($wpdb->prepare($sql, $coupon_id));

        if (!$updated) {
            // Reached capacity or coupon not found
            return false;
        }

        // Fetch code for audit record
        $code = (string)$wpdb->get_var($wpdb->prepare("SELECT code FROM {$table_coupons} WHERE id = %d", $coupon_id));

        // Insert permanent redemption record
        $wpdb->insert($table_redemptions, [
            'coupon_id'          => $coupon_id,
            'coupon_code'        => $code,
            'user_id'            => (int)($redemption_data['user_id'] ?? 0),
            'user_email'         => sanitize_email($redemption_data['user_email'] ?? ''),
            'user_phone'         => sanitize_text_field($redemption_data['user_phone'] ?? ''),
            'internal_txn_id'    => sanitize_text_field($redemption_data['internal_txn_id'] ?? ''),
            'gateway_payment_id' => sanitize_text_field($redemption_data['gateway_payment_id'] ?? ''),
            'subtotal_pretax'    => floatval($redemption_data['subtotal_pretax'] ?? 0.00),
            'discount_applied'   => floatval($redemption_data['discount_applied'] ?? 0.00),
            'tax_amount'         => floatval($redemption_data['tax_amount'] ?? 0.00),
            'total_paid'         => floatval($redemption_data['total_paid'] ?? 0.00),
            'product_type'       => sanitize_key($redemption_data['product_type'] ?? 'reports'),
            'redeemed_at'        => current_time('mysql'),
        ]);

        return true;
    }

    /**
     * Get All Coupons with optional filters
     */
    public static function get_all_coupons($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;

        $where = ['1=1'];
        $params = [];

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $params[] = sanitize_key($args['status']);
        }
        if (isset($args['is_secret'])) {
            $where[] = "is_secret = %d";
            $params[] = !empty($args['is_secret']) ? 1 : 0;
        } elseif (empty($args['include_secret'])) {
            // By default, exclude secret testing coupons from general and public listings
            $where[] = "is_secret = 0";
        }
        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like(trim($args['search'])) . '%';
            $where[] = "(code LIKE %s OR title LIKE %s)";
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = implode(' AND ', $where);
        $order_by = !empty($args['orderby']) ? sanitize_sql_orderby($args['orderby']) : 'id DESC';

        if (!empty($params)) {
            $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$order_by}", $params);
        } else {
            $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$order_by}";
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Save or update coupon
     */
    public static function save_coupon($data, $id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        $id = (int)$id;

        $code = strtoupper(trim(sanitize_text_field($data['code'] ?? '')));
        if (empty($code)) {
            return new WP_Error('empty_code', __('Coupon code cannot be empty.', 'merarashifal'));
        }

        // Check duplicate code
        $dup_check = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE UPPER(code) = %s AND id != %d",
            $code,
            $id
        ));
        if ($dup_check) {
            return new WP_Error('duplicate_code', __('A coupon with this code already exists.', 'merarashifal'));
        }

        $record = [
            'code'                 => $code,
            'title'                => sanitize_text_field($data['title'] ?? $code),
            'description'          => sanitize_textarea_field($data['description'] ?? ''),
            'discount_type'        => in_array($data['discount_type'] ?? '', ['flat', 'percent'], true) ? $data['discount_type'] : 'percent',
            'discount_value'       => max(0.00, floatval($data['discount_value'] ?? 0)),
            'max_discount_cap'     => max(0.00, floatval($data['max_discount_cap'] ?? 0)),
            'min_order_value'      => max(0.00, floatval($data['min_order_value'] ?? 0)),
            'product_scope'        => sanitize_key($data['product_scope'] ?? 'all'),
            'eligible_items'       => !empty($data['eligible_items']) ? (is_array($data['eligible_items']) ? wp_json_encode($data['eligible_items']) : $data['eligible_items']) : null,
            'excluded_items'       => !empty($data['excluded_items']) ? (is_array($data['excluded_items']) ? wp_json_encode($data['excluded_items']) : $data['excluded_items']) : null,
            'total_usage_limit'    => max(0, intval($data['total_usage_limit'] ?? 0)),
            'per_user_limit'       => max(1, intval($data['per_user_limit'] ?? 1)),
            'starts_at'            => !empty($data['starts_at']) ? sanitize_text_field($data['starts_at']) : null,
            'expires_at'           => !empty($data['expires_at']) ? sanitize_text_field($data['expires_at']) : null,
            'status'               => in_array($data['status'] ?? '', ['active', 'paused', 'expired'], true) ? $data['status'] : 'active',
        ];

        if ($id > 0) {
            $wpdb->update($table, $record, ['id' => $id]);
            return $id;
        } else {
            $record['total_redeemed_count'] = 0;
            $record['created_at'] = current_time('mysql');
            $wpdb->insert($table, $record);
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete coupon
     */
    public static function delete_coupon($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        return (bool)$wpdb->delete($table, ['id' => (int)$id]);
    }

    /**
     * Get recent redemptions log
     */
    public static function get_recent_redemptions($limit = 100, $coupon_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_REDEMPTIONS;
        $limit = max(1, min(500, intval($limit)));

        if ($coupon_id > 0) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} WHERE coupon_id = %d ORDER BY redeemed_at DESC LIMIT %d",
                $coupon_id,
                $limit
            ), ARRAY_A);
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY redeemed_at DESC LIMIT %d",
            $limit
        ), ARRAY_A);
    }

    /**
     * Generate a cryptographically secure 6-character alphanumeric code
     * Excludes ambiguous characters (0, O, 1, I) to ensure ease of typing
     *
     * @param int $length
     * @return string High-entropy uppercase alphanumeric code
     */
    public static function generate_secret_code($length = 6) {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $max = strlen($chars) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, $max)];
        }
        return $code;
    }

    /**
     * Get or create the secret ₹1 live testing promo coupon
     *
     * @return array Secret coupon record
     */
    public static function get_or_create_secret_test_coupon() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        self::ensure_secret_column();

        $existing = $wpdb->get_row("SELECT * FROM {$table} WHERE is_secret = 1 LIMIT 1", ARRAY_A);
        if ($existing) {
            return $existing;
        }

        // Generate high-entropy 6-character alphanumeric code (e.g. X9K7M2)
        $code = self::generate_secret_code(6);

        $wpdb->insert($table, [
            'code'                 => $code,
            'title'                => 'Secret Live Gateway Test Promo (₹1 Total)',
            'description'          => 'Secret 100% test promo code for verifying live Razorpay transactions and webhooks. Clamps cart total to exactly ₹1.00.',
            'discount_type'        => 'percent',
            'discount_value'       => 100.00,
            'max_discount_cap'     => 0.00,
            'min_order_value'      => 0.00,
            'product_scope'        => 'all',
            'total_usage_limit'    => 0, // Unlimited for administrative live testing
            'total_redeemed_count' => 0,
            'per_user_limit'       => 999,
            'status'               => 'active',
            'is_secret'            => 1,
            'created_at'           => current_time('mysql'),
        ]);

        return self::get_coupon_by_id($wpdb->insert_id);
    }

    /**
     * Regenerate or customize the secret test promo code
     *
     * @param string $new_code Optional specific code, or random if empty
     * @return array Updated secret coupon record
     */
    public static function regenerate_secret_test_code($new_code = '') {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COUPONS;
        $current = self::get_or_create_secret_test_coupon();

        $new_code = strtoupper(trim(sanitize_text_field($new_code)));
        if (empty($new_code)) {
            $new_code = self::generate_secret_code(6);
        }

        $wpdb->update($table, [
            'code'       => $new_code,
            'status'     => 'active',
            'updated_at' => current_time('mysql'),
        ], ['id' => $current['id']]);

        return self::get_coupon_by_id($current['id']);
    }
}
