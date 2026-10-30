<?php
/**
 * Universal Financial Transaction Ledger & Passbook Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Transactions {

    protected static $table_checked = false;

    /**
     * Ensure database table exists
     */
    public static function create_table() {
        if (self::$table_checked && get_option('mr_txn_table_migrated_v2') === 'yes') {
            return;
        }
        self::$table_checked = true;

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            internal_txn_id varchar(64) NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            customer_name varchar(150) NOT NULL DEFAULT '',
            customer_email varchar(150) NOT NULL DEFAULT '',
            customer_phone varchar(30) NOT NULL DEFAULT '',
            channel varchar(40) NOT NULL DEFAULT 'report',
            item_key varchar(80) NOT NULL DEFAULT '',
            item_name varchar(200) NOT NULL DEFAULT '',
            txn_type varchar(30) NOT NULL DEFAULT 'purchase',
            gross_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            discount_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            net_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            currency varchar(10) NOT NULL DEFAULT 'INR',
            gateway varchar(30) NOT NULL DEFAULT 'razorpay',
            gateway_mode varchar(10) NOT NULL DEFAULT 'test',
            gateway_order_id varchar(100) NOT NULL DEFAULT '',
            gateway_payment_id varchar(100) NOT NULL DEFAULT '',
            gateway_signature varchar(255) NOT NULL DEFAULT '',
            status varchar(30) NOT NULL DEFAULT 'pending',
            drop_stage varchar(60) NOT NULL DEFAULT '',
            drop_url varchar(255) NOT NULL DEFAULT '',
            recovery_status varchar(30) NOT NULL DEFAULT 'unrecovered',
            remarketed_at datetime DEFAULT NULL,
            failure_reason text DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY internal_txn_id (internal_txn_id),
            KEY user_id (user_id),
            KEY customer_email (customer_email),
            KEY channel (channel),
            KEY item_key (item_key),
            KEY txn_type (txn_type),
            KEY gateway_mode (gateway_mode),
            KEY gateway_order_id (gateway_order_id),
            KEY gateway_payment_id (gateway_payment_id),
            KEY status (status),
            KEY drop_stage (drop_stage),
            KEY recovery_status (recovery_status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Idempotent column check for existing tables
        if (get_option('mr_txn_table_migrated_v2') !== 'yes') {
            $existing_cols = $wpdb->get_col("DESC $table", 0);
            if (!empty($existing_cols)) {
                if (!in_array('drop_stage', $existing_cols, true)) {
                    $wpdb->query("ALTER TABLE $table ADD COLUMN drop_stage varchar(60) NOT NULL DEFAULT '' AFTER status, ADD KEY drop_stage (drop_stage)");
                }
                if (!in_array('drop_url', $existing_cols, true)) {
                    $wpdb->query("ALTER TABLE $table ADD COLUMN drop_url varchar(255) NOT NULL DEFAULT '' AFTER drop_stage");
                }
                if (!in_array('recovery_status', $existing_cols, true)) {
                    $wpdb->query("ALTER TABLE $table ADD COLUMN recovery_status varchar(30) NOT NULL DEFAULT 'unrecovered' AFTER drop_url, ADD KEY recovery_status (recovery_status)");
                }
                if (!in_array('remarketed_at', $existing_cols, true)) {
                    $wpdb->query("ALTER TABLE $table ADD COLUMN remarketed_at datetime DEFAULT NULL AFTER recovery_status");
                }
            }
            update_option('mr_txn_table_migrated_v2', 'yes');
        }
        update_option('mr_txn_table_created', 'yes');
    }

    /**
     * Generate unique internal transaction reference
     */
    public static function generate_internal_id() {
        return 'MR-TXN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    /**
     * Record a new transaction (Status: pending)
     */
    public static function create_transaction($data = []) {
        global $wpdb;
        self::create_table();

        $internal_txn_id = !empty($data['internal_txn_id']) ? sanitize_text_field($data['internal_txn_id']) : self::generate_internal_id();
        $user_id         = !empty($data['user_id']) ? (int)$data['user_id'] : get_current_user_id();

        // Snapshot customer details
        $customer_name  = !empty($data['customer_name']) ? sanitize_text_field($data['customer_name']) : '';
        $customer_email = !empty($data['customer_email']) ? sanitize_email($data['customer_email']) : '';
        $customer_phone = !empty($data['customer_phone']) ? sanitize_text_field($data['customer_phone']) : '';

        if ($user_id && (empty($customer_name) || empty($customer_email))) {
            $user = get_userdata($user_id);
            if ($user) {
                if (empty($customer_name)) $customer_name = $user->display_name ?: $user->user_login;
                if (empty($customer_email)) $customer_email = $user->user_email;
            }
        }

        $gross_amount    = isset($data['gross_amount']) ? floatval($data['gross_amount']) : (isset($data['amount']) ? floatval($data['amount']) : 0.00);
        $discount_amount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : 0.00;
        $net_amount      = isset($data['net_amount']) ? floatval($data['net_amount']) : ($gross_amount - $discount_amount);

        $mode = !empty($data['gateway_mode']) ? sanitize_text_field($data['gateway_mode']) : (class_exists('MR_Razorpay') ? MR_Razorpay::get_mode() : 'test');

        $metadata = isset($data['metadata']) ? (is_array($data['metadata']) ? wp_json_encode($data['metadata']) : $data['metadata']) : null;

        $insert_data = [
            'internal_txn_id'    => $internal_txn_id,
            'user_id'            => $user_id,
            'customer_name'      => $customer_name,
            'customer_email'     => $customer_email,
            'customer_phone'     => $customer_phone,
            'channel'            => !empty($data['channel']) ? sanitize_key($data['channel']) : 'report',
            'item_key'           => !empty($data['item_key']) ? sanitize_key($data['item_key']) : '',
            'item_name'          => !empty($data['item_name']) ? sanitize_text_field($data['item_name']) : '',
            'txn_type'           => !empty($data['txn_type']) ? sanitize_key($data['txn_type']) : 'purchase',
            'gross_amount'       => $gross_amount,
            'discount_amount'    => $discount_amount,
            'net_amount'         => $net_amount,
            'currency'           => !empty($data['currency']) ? strtoupper(sanitize_text_field($data['currency'])) : 'INR',
            'gateway'            => !empty($data['gateway']) ? sanitize_key($data['gateway']) : 'razorpay',
            'gateway_mode'       => $mode,
            'gateway_order_id'   => !empty($data['gateway_order_id']) ? sanitize_text_field($data['gateway_order_id']) : '',
            'gateway_payment_id' => !empty($data['gateway_payment_id']) ? sanitize_text_field($data['gateway_payment_id']) : '',
            'gateway_signature'  => !empty($data['gateway_signature']) ? sanitize_text_field($data['gateway_signature']) : '',
            'status'             => !empty($data['status']) ? sanitize_key($data['status']) : 'pending',
            'drop_stage'         => !empty($data['drop_stage']) ? sanitize_text_field($data['drop_stage']) : '',
            'drop_url'           => !empty($data['drop_url']) ? esc_url_raw($data['drop_url']) : '',
            'recovery_status'    => !empty($data['recovery_status']) ? sanitize_key($data['recovery_status']) : 'unrecovered',
            'remarketed_at'      => !empty($data['remarketed_at']) ? sanitize_text_field($data['remarketed_at']) : null,
            'failure_reason'     => !empty($data['failure_reason']) ? sanitize_text_field($data['failure_reason']) : null,
            'metadata'           => $metadata,
            'created_at'         => current_time('mysql'),
            'updated_at'         => current_time('mysql'),
        ];

        $table = $wpdb->prefix . 'mr_transactions';
        $inserted = $wpdb->insert($table, $insert_data);

        if ($inserted === false) {
            return false;
        }

        return [
            'id'              => $wpdb->insert_id,
            'internal_txn_id' => $internal_txn_id
        ];
    }

    /**
     * Mark transaction as successful
     */
    public static function complete_transaction($order_or_internal_id, $payment_id, $signature = '', $extra_meta = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        // Find by internal ID or gateway order ID
        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE internal_txn_id = %s OR gateway_order_id = %s LIMIT 1",
            $order_or_internal_id,
            $order_or_internal_id
        ), ARRAY_A);

        if (!$txn) {
            return false;
        }

        $existing_meta = !empty($txn['metadata']) ? json_decode($txn['metadata'], true) : [];
        if (!is_array($existing_meta)) $existing_meta = [];
        if (!empty($extra_meta)) {
            $existing_meta = array_merge($existing_meta, $extra_meta);
        }

        // If transaction was previously abandoned or in recovery, mark as recovered
        $recovery_status = (!empty($txn['status']) && $txn['status'] === 'abandoned') || (!empty($txn['recovery_status']) && in_array($txn['recovery_status'], ['contacted', 'unrecovered'], true))
            ? 'recovered'
            : ($txn['recovery_status'] ?: 'unrecovered');

        $update_data = [
            'gateway_payment_id' => sanitize_text_field($payment_id),
            'gateway_signature'  => sanitize_text_field($signature),
            'status'             => 'success',
            'recovery_status'    => $recovery_status,
            'metadata'           => !empty($existing_meta) ? wp_json_encode($existing_meta) : null,
            'updated_at'         => current_time('mysql')
        ];

        $wpdb->update($table, $update_data, ['id' => $txn['id']]);

        return array_merge($txn, $update_data);
    }

    /**
     * Mark an order/transaction as abandoned with drop-off stage and URL
     *
     * @param string $order_or_internal_id Gateway order id or internal txn id
     * @param string $drop_stage e.g. 'gateway_modal_dismissed', 'auth_modal_prompted', 'details_form_dropped', 'payment_timeout'
     * @param string $drop_url URL where user dropped off
     * @param array $extra_meta Extra details
     * @return array|false Updated transaction or false
     */
    public static function mark_abandoned($order_or_internal_id, $drop_stage = 'gateway_modal_dismissed', $drop_url = '', $extra_meta = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE internal_txn_id = %s OR gateway_order_id = %s LIMIT 1",
            $order_or_internal_id,
            $order_or_internal_id
        ), ARRAY_A);

        if (!$txn) {
            return false;
        }

        // Never overwrite a completed purchase or refund
        if (in_array($txn['status'], ['success', 'refunded', 'partial_refund'], true)) {
            return false;
        }

        $existing_meta = !empty($txn['metadata']) ? json_decode($txn['metadata'], true) : [];
        if (!is_array($existing_meta)) $existing_meta = [];
        if (!empty($extra_meta)) {
            $existing_meta = array_merge($existing_meta, $extra_meta);
        }
        $existing_meta['abandoned_at'] = current_time('mysql');

        $update_data = [
            'status'          => 'abandoned',
            'drop_stage'      => sanitize_text_field($drop_stage),
            'drop_url'        => !empty($drop_url) ? esc_url_raw($drop_url) : esc_url_raw($txn['drop_url']),
            'failure_reason'  => 'Cart abandoned at checkout: ' . sanitize_text_field($drop_stage),
            'recovery_status' => (!empty($txn['recovery_status']) && $txn['recovery_status'] !== 'unrecovered') ? $txn['recovery_status'] : 'unrecovered',
            'metadata'        => wp_json_encode($existing_meta),
            'updated_at'      => current_time('mysql')
        ];

        $wpdb->update($table, $update_data, ['id' => $txn['id']]);

        return array_merge($txn, $update_data);
    }

    /**
     * Auto-transition stale 'pending' transactions to 'abandoned'
     *
     * @param int $minutes Age in minutes (default 30 mins)
     * @return int Number of updated rows
     */
    public static function auto_expire_pending_to_abandoned($minutes = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

        $query = $wpdb->prepare(
            "UPDATE $table 
             SET status = 'abandoned', 
                 drop_stage = IF(drop_stage IS NULL OR drop_stage = '', 'payment_timeout', drop_stage), 
                 failure_reason = 'Payment timeout: order abandoned without completion',
                 recovery_status = IF(recovery_status IS NULL OR recovery_status = '', 'unrecovered', recovery_status),
                 updated_at = %s 
             WHERE status = 'pending' AND created_at <= %s",
            current_time('mysql'),
            $cutoff
        );

        $affected = $wpdb->query($query);
        return (int)$affected;
    }

    /**
     * Update recovery remarketing status for an abandoned cart
     */
    public static function update_recovery_status($id_or_txn, $new_status = 'contacted', $note = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d OR internal_txn_id = %s LIMIT 1",
            is_numeric($id_or_txn) ? (int)$id_or_txn : 0,
            sanitize_text_field($id_or_txn)
        ), ARRAY_A);

        if (!$txn) return false;

        $meta = !empty($txn['metadata']) ? json_decode($txn['metadata'], true) : [];
        if (!is_array($meta)) $meta = [];
        if (!empty($note)) {
            $meta['recovery_notes'][] = [
                'note' => sanitize_text_field($note),
                'by'   => wp_get_current_user()->display_name ?: 'Staff',
                'at'   => current_time('mysql')
            ];
        }

        $update_data = [
            'recovery_status' => sanitize_key($new_status),
            'remarketed_at'   => current_time('mysql'),
            'metadata'        => wp_json_encode($meta),
            'updated_at'      => current_time('mysql')
        ];

        return $wpdb->update($table, $update_data, ['id' => $txn['id']]);
    }

    /**
     * Get summary metrics for Abandoned Carts & Remarketing Dashboard
     */
    public static function get_abandoned_summary() {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        // Auto-expire transactions pending > 30 mins first
        self::auto_expire_pending_to_abandoned(30);

        $total_abandoned = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'abandoned'");
        $total_value     = floatval($wpdb->get_var("SELECT SUM(gross_amount) FROM $table WHERE status = 'abandoned'")) ?: 0.00;
        $unrecovered     = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'abandoned' AND recovery_status = 'unrecovered'");
        $contacted       = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'abandoned' AND recovery_status = 'contacted'");
        $recovered_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE recovery_status = 'recovered'");
        $recovered_val   = floatval($wpdb->get_var("SELECT SUM(gross_amount) FROM $table WHERE recovery_status = 'recovered'")) ?: 0.00;

        $recovery_rate = ($total_abandoned + $recovered_count) > 0 
            ? round(($recovered_count / ($total_abandoned + $recovered_count)) * 100, 1) 
            : 0;

        $channels = $wpdb->get_results("SELECT channel, COUNT(*) as cnt, SUM(gross_amount) as total_amt FROM $table WHERE status = 'abandoned' GROUP BY channel", ARRAY_A);
        $stages   = $wpdb->get_results("SELECT drop_stage, COUNT(*) as cnt FROM $table WHERE status = 'abandoned' GROUP BY drop_stage", ARRAY_A);

        return [
            'total_abandoned' => $total_abandoned,
            'total_value'     => $total_value,
            'unrecovered'     => $unrecovered,
            'contacted'       => $contacted,
            'recovered_count' => $recovered_count,
            'recovered_value' => $recovered_val,
            'recovery_rate'   => $recovery_rate,
            'channels'        => $channels,
            'stages'          => $stages,
        ];
    }

    /**
     * Mark transaction as failed
     */
    public static function fail_transaction($order_or_internal_id, $reason = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE internal_txn_id = %s OR gateway_order_id = %s LIMIT 1",
            $order_or_internal_id,
            $order_or_internal_id
        ));

        if (!$txn) {
            return false;
        }

        return $wpdb->update($table, [
            'status'         => 'failed',
            'failure_reason' => sanitize_text_field($reason),
            'updated_at'     => current_time('mysql')
        ], ['id' => $txn->id]);
    }

    /**
     * Process a refund on a transaction
     *
     * @param string $order_or_payment_id Gateway order id, payment id, or internal txn id
     * @param string $refund_id Gateway refund ID (e.g. rfnd_xxx)
     * @param float|null $refund_amount Amount refunded (null for full net amount)
     * @param string $reason Reason or intimation message
     * @param array $extra_meta Additional metadata (e.g. ARN, speed)
     * @return array|false Updated transaction array or false
     */
    public static function refund_transaction($order_or_payment_id, $refund_id = '', $refund_amount = null, $reason = '', $extra_meta = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE internal_txn_id = %s OR gateway_order_id = %s OR gateway_payment_id = %s LIMIT 1",
            $order_or_payment_id,
            $order_or_payment_id,
            $order_or_payment_id
        ), ARRAY_A);

        if (!$txn) {
            return false;
        }

        $existing_meta = !empty($txn['metadata']) ? json_decode($txn['metadata'], true) : [];
        if (!is_array($existing_meta)) {
            $existing_meta = [];
        }

        $refund_amt = ($refund_amount !== null && floatval($refund_amount) > 0) ? floatval($refund_amount) : floatval($txn['net_amount']);

        $refund_meta = [
            'refund_id'     => sanitize_text_field($refund_id),
            'refund_amount' => $refund_amt,
            'refund_reason' => sanitize_text_field($reason),
            'refunded_at'   => current_time('mysql'),
        ];

        $merged_meta = array_merge($existing_meta, $refund_meta, $extra_meta);

        // Status is 'refunded' (or 'partial_refund' if less than net amount)
        $new_status = ($refund_amt >= floatval($txn['net_amount'])) ? 'refunded' : 'partial_refund';

        $update_data = [
            'status'     => $new_status,
            'metadata'   => wp_json_encode($merged_meta),
            'updated_at' => current_time('mysql')
        ];

        $wpdb->update($table, $update_data, ['id' => $txn['id']]);

        // Revoke associated credits or entitlements
        self::handle_refund_revocation($txn, $refund_meta);

        return array_merge($txn, $update_data);
    }

    /**
     * Revoke tokens, subscriptions, or chat credits when a refund is recorded
     */
    public static function handle_refund_revocation($txn, $refund_meta = []) {
        global $wpdb;
        $channel = $txn['channel'];
        $user_id = (int)$txn['user_id'];
        $meta = !empty($txn['metadata']) ? json_decode($txn['metadata'], true) : [];

        // 1. AI Chat Credits Revocation
        if ($channel === 'chat_credit') {
            $visitor_id = $meta['visitor_id'] ?? '';
            $credits_added = isset($meta['credits_added']) ? (int)$meta['credits_added'] : 0;

            if ($credits_added > 0 && class_exists('Niyati_Wallet')) {
                Niyati_Wallet::revoke_credits($visitor_id, $user_id, $credits_added, $txn['gateway_payment_id']);
            }
        }

        // 2. Report Token Revocation
        if ($channel === 'report' && $user_id > 0) {
            $report_slug = $txn['item_key'];
            if ($report_slug && class_exists('MR_Tokens')) {
                $table_tokens = $wpdb->prefix . 'mr_user_tokens';
                $token_row = $wpdb->get_row($wpdb->prepare(
                    "SELECT id, tokens_total, tokens_remaining FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
                    $user_id,
                    $report_slug
                ));
                if ($token_row && (int)$token_row->tokens_remaining > 0) {
                    $new_rem = max(0, (int)$token_row->tokens_remaining - 1);
                    $new_tot = max(0, (int)$token_row->tokens_total - 1);
                    $wpdb->update($table_tokens, [
                        'tokens_total'     => $new_tot,
                        'tokens_remaining' => $new_rem,
                        'updated_at'       => current_time('mysql')
                    ], ['id' => $token_row->id]);
                }
            }
        }

        // 3. Subscription Pack Revocation
        if ($channel === 'subscription' && $user_id > 0) {
            $table_subs = $wpdb->prefix . 'mr_user_subscriptions';
            $wpdb->update($table_subs, [
                'status' => 'refunded'
            ], [
                'user_id'  => $user_id,
                'pack_key' => $txn['item_key']
            ]);
        }
    }

    /**
     * Fetch user transactions for passbook (by User ID or Email match)
     */
    public static function get_user_transactions($user_id, $limit = 50, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $user = get_userdata($user_id);
        $email = $user ? trim($user->user_email) : '';

        if (!empty($email)) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d OR (customer_email = %s AND customer_email != '') ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $user_id,
                $email,
                $limit,
                $offset
            ), ARRAY_A);
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ), ARRAY_A);
    }

    /**
     * Fetch transactions with admin filters
     */
    public static function get_filtered_transactions($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $where = ['1=1'];
        $params = [];

        if (!empty($args['channel']) && $args['channel'] !== 'all') {
            $where[] = 'channel = %s';
            $params[] = sanitize_key($args['channel']);
        }

        if (!empty($args['status']) && $args['status'] !== 'all') {
            $where[] = 'status = %s';
            $params[] = sanitize_key($args['status']);
        }

        if (!empty($args['gateway_mode']) && $args['gateway_mode'] !== 'all') {
            $where[] = 'gateway_mode = %s';
            $params[] = sanitize_key($args['gateway_mode']);
        }

        if (!empty($args['txn_type']) && $args['txn_type'] !== 'all') {
            $where[] = 'txn_type = %s';
            $params[] = sanitize_key($args['txn_type']);
        }

        if (!empty($args['recovery_status']) && $args['recovery_status'] !== 'all') {
            $where[] = 'recovery_status = %s';
            $params[] = sanitize_key($args['recovery_status']);
        }

        if (!empty($args['drop_stage']) && $args['drop_stage'] !== 'all') {
            $where[] = 'drop_stage = %s';
            $params[] = sanitize_key($args['drop_stage']);
        }

        if (!empty($args['search'])) {
            $s = '%' . $wpdb->esc_like(trim($args['search'])) . '%';
            $where[] = '(internal_txn_id LIKE %s OR gateway_order_id LIKE %s OR gateway_payment_id LIKE %s OR customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s OR item_name LIKE %s OR drop_stage LIKE %s)';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $limit = isset($args['limit']) ? (int)$args['limit'] : 25;
        $offset = isset($args['offset']) ? (int)$args['offset'] : 0;

        $where_sql = implode(' AND ', $where);

        $total = $wpdb->get_var(!empty($params) ? $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE $where_sql", $params) : "SELECT COUNT(*) FROM $table WHERE $where_sql");

        $query = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $rows = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

        return [
            'total' => (int)$total,
            'rows'  => $rows
        ];
    }

    /**
     * Get revenue and volume summary
     */
    public static function get_summary($gateway_mode = 'all') {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $where = "status = 'success'";
        $where_failed = "status = 'failed'";
        $where_refunded = "status IN ('refunded', 'partial_refund')";

        if ($gateway_mode !== 'all') {
            $where .= $wpdb->prepare(" AND gateway_mode = %s", $gateway_mode);
            $where_failed .= $wpdb->prepare(" AND gateway_mode = %s", $gateway_mode);
            $where_refunded .= $wpdb->prepare(" AND gateway_mode = %s", $gateway_mode);
        }

        $revenue = $wpdb->get_var("SELECT SUM(net_amount) FROM $table WHERE $where") ?: 0;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where") ?: 0;
        $failed = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_failed") ?: 0;
        $refunded_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_refunded") ?: 0;
        $refunded_amount = $wpdb->get_var("SELECT SUM(net_amount) FROM $table WHERE $where_refunded") ?: 0;

        return [
            'total_revenue'   => floatval($revenue),
            'success_count'   => (int)$count,
            'failed_count'    => (int)$failed,
            'refunded_count'  => (int)$refunded_count,
            'refunded_amount' => floatval($refunded_amount),
        ];
    }
}
