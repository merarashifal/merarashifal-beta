<?php
/**
 * Niyati Credit Wallet & Transaction Ledger
 * Manages user paid balances, credit deduction, pack definitions, and validity stacking.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Wallet {

    const TABLE_WALLETS = 'niyati_credit_wallets';
    const TABLE_TRANSACTIONS = 'niyati_credit_transactions';

    /**
     * Create or upgrade required database tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_wallets = $wpdb->prefix . self::TABLE_WALLETS;
        $table_txns = $wpdb->prefix . self::TABLE_TRANSACTIONS;

        $sql_wallets = "CREATE TABLE IF NOT EXISTS {$table_wallets} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            visitor_id VARCHAR(100) NOT NULL,
            email VARCHAR(100) DEFAULT '',
            phone VARCHAR(25) DEFAULT '',
            credits_balance INT(11) NOT NULL DEFAULT 0,
            total_purchased INT(11) NOT NULL DEFAULT 0,
            total_consumed INT(11) NOT NULL DEFAULT 0,
            validity_expires_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY visitor_id (visitor_id),
            KEY user_id (user_id),
            KEY phone (phone)
        ) {$charset_collate};";

        $sql_txns = "CREATE TABLE IF NOT EXISTS {$table_txns} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id VARCHAR(100) NOT NULL,
            order_id VARCHAR(100) DEFAULT '',
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            visitor_id VARCHAR(100) NOT NULL,
            pack_key VARCHAR(50) NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            credits_added INT(11) NOT NULL DEFAULT 0,
            validity_days INT(11) NOT NULL DEFAULT 0,
            payment_gateway VARCHAR(50) NOT NULL DEFAULT 'razorpay',
            payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
            pack_snapshot LONGTEXT DEFAULT NULL,
            raw_response LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY transaction_id (transaction_id),
            KEY visitor_id (visitor_id),
            KEY user_id (user_id),
            KEY payment_status (payment_status)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_wallets);
        dbDelta($sql_txns);

        // Ensure columns exist on upgrade
        self::ensure_columns();
    }

    /**
     * Check and add columns if upgrading an existing installation
     */
    public static function ensure_columns() {
        global $wpdb;
        $table_wallets = $wpdb->prefix . self::TABLE_WALLETS;
        $table_txns = $wpdb->prefix . self::TABLE_TRANSACTIONS;

        $wallet_cols = $wpdb->get_col("DESC {$table_wallets}", 0);
        if (!empty($wallet_cols) && !in_array('validity_expires_at', $wallet_cols)) {
            $wpdb->query("ALTER TABLE {$table_wallets} ADD COLUMN validity_expires_at DATETIME DEFAULT NULL AFTER total_consumed");
        }

        $txn_cols = $wpdb->get_col("DESC {$table_txns}", 0);
        if (!empty($txn_cols)) {
            if (!in_array('validity_days', $txn_cols)) {
                $wpdb->query("ALTER TABLE {$table_txns} ADD COLUMN validity_days INT(11) NOT NULL DEFAULT 0 AFTER credits_added");
            }
            if (!in_array('pack_snapshot', $txn_cols)) {
                $wpdb->query("ALTER TABLE {$table_txns} ADD COLUMN pack_snapshot LONGTEXT DEFAULT NULL AFTER payment_status");
            }
        }
    }

    /**
     * Get or create wallet row
     */
    public static function get_wallet($visitor_id, $user_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_WALLETS;

        if ($user_id > 0) {
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d LIMIT 1", $user_id));
            if ($row) {
                return $row;
            }
        }

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE visitor_id = %s LIMIT 1", $visitor_id));
        if ($row) {
            if ($user_id > 0 && empty($row->user_id)) {
                $wpdb->update($table, ['user_id' => $user_id], ['id' => $row->id]);
                $row->user_id = $user_id;
            }
            return $row;
        }

        // Create new wallet row
        $wpdb->insert($table, [
            'user_id'             => $user_id,
            'visitor_id'          => $visitor_id,
            'credits_balance'     => 0,
            'total_purchased'     => 0,
            'total_consumed'      => 0,
            'validity_expires_at' => null,
            'created_at'          => current_time('mysql'),
            'updated_at'          => current_time('mysql'),
        ]);

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $wpdb->insert_id));
    }

    /**
     * Get paid credits balance (validity aware)
     */
    public static function get_balance($visitor_id, $user_id = 0) {
        $wallet = self::get_wallet($visitor_id, $user_id);
        if (!$wallet) {
            return 0;
        }

        // Check if validity expired
        if (!empty($wallet->validity_expires_at) && $wallet->validity_expires_at !== '0000-00-00 00:00:00') {
            if (strtotime($wallet->validity_expires_at) < current_time('timestamp')) {
                return 0; // Expired
            }
        }

        return max(0, intval($wallet->credits_balance));
    }

    /**
     * Get validity info for user
     */
    public static function get_validity_info($visitor_id, $user_id = 0) {
        $wallet = self::get_wallet($visitor_id, $user_id);
        if (!$wallet || empty($wallet->validity_expires_at) || $wallet->validity_expires_at === '0000-00-00 00:00:00') {
            return [
                'has_expiry'   => false,
                'is_expired'   => false,
                'expires_at'   => null,
                'days_left'    => null,
                'label'        => 'Lifetime / No Expiry'
            ];
        }

        $exp_time = strtotime($wallet->validity_expires_at);
        $now = current_time('timestamp');
        $is_expired = ($exp_time < $now);
        $days_left = max(0, ceil(($exp_time - $now) / DAY_IN_SECONDS));

        return [
            'has_expiry'   => true,
            'is_expired'   => $is_expired,
            'expires_at'   => $wallet->validity_expires_at,
            'days_left'    => $days_left,
            'label'        => $is_expired ? 'Expired' : "{$days_left} Days Left"
        ];
    }

    /**
     * Credit purchased tokens with Multi-Pack & Validity Stacking support
     *
     * @param string $visitor_id
     * @param int $user_id
     * @param int $credits
     * @param float $amount
     * @param string $txn_id
     * @param string $pack_key
     * @param string $gateway
     * @param mixed $raw
     * @param int $validity_days (0 for Lifetime)
     * @param array $pack_snapshot (Immutable snapshot of the pack at checkout time)
     * @return int New balance
     */
    public static function add_credits($visitor_id, $user_id, $credits, $amount, $txn_id, $pack_key, $gateway = 'razorpay', $raw = '', $validity_days = 0, $pack_snapshot = []) {
        global $wpdb;
        $table_wallets = $wpdb->prefix . self::TABLE_WALLETS;
        $table_txns = $wpdb->prefix . self::TABLE_TRANSACTIONS;

        $wallet = self::get_wallet($visitor_id, $user_id);

        // 1. Credit Stacking (No limit on buying multiple packs or same pack multiple times)
        $new_balance = intval($wallet->credits_balance) + intval($credits);
        $new_total_purchased = intval($wallet->total_purchased) + intval($credits);

        // 2. Validity Stacking:
        // If pack has validity_days > 0, stack on top of existing active validity, or start from now
        $new_expiry = $wallet->validity_expires_at;

        if ($validity_days > 0) {
            $now_ts = current_time('timestamp');
            $current_exp_ts = !empty($wallet->validity_expires_at) ? strtotime($wallet->validity_expires_at) : 0;

            if ($current_exp_ts > $now_ts) {
                // Stack on top of current future expiry
                $stacked_ts = strtotime("+{$validity_days} days", $current_exp_ts);
            } else {
                // Set from now
                $stacked_ts = strtotime("+{$validity_days} days", $now_ts);
            }
            $new_expiry = date('Y-m-d H:i:s', $stacked_ts);
        }

        $wpdb->update(
            $table_wallets,
            [
                'credits_balance'     => $new_balance,
                'total_purchased'     => $new_total_purchased,
                'validity_expires_at' => $new_expiry,
                'updated_at'          => current_time('mysql')
            ],
            ['id' => $wallet->id]
        );

        // 3. Record completed transaction with Immutable Pack Snapshot
        $wpdb->insert($table_txns, [
            'transaction_id'  => $txn_id,
            'user_id'         => $user_id,
            'visitor_id'      => $visitor_id,
            'pack_key'        => $pack_key,
            'amount'          => floatval($amount),
            'currency'        => 'INR',
            'credits_added'   => intval($credits),
            'validity_days'   => intval($validity_days),
            'payment_gateway' => $gateway,
            'payment_status'  => 'completed',
            'pack_snapshot'   => wp_json_encode($pack_snapshot),
            'raw_response'    => is_string($raw) ? $raw : wp_json_encode($raw),
            'created_at'      => current_time('mysql')
        ]);

        return $new_balance;
    }

    /**
     * Revoke credits on refund
     *
     * @param string $visitor_id
     * @param int $user_id
     * @param int $credits_to_revoke
     * @param string $txn_id
     * @return int New balance
     */
    public static function revoke_credits($visitor_id, $user_id = 0, $credits_to_revoke = 0, $txn_id = '') {
        global $wpdb;
        $table_wallets = $wpdb->prefix . self::TABLE_WALLETS;
        $table_txns = $wpdb->prefix . self::TABLE_TRANSACTIONS;

        $wallet = self::get_wallet($visitor_id, $user_id);
        if (!$wallet) {
            return 0;
        }

        $current = intval($wallet->credits_balance);
        $new_balance = max(0, $current - intval($credits_to_revoke));

        $wpdb->update(
            $table_wallets,
            [
                'credits_balance' => $new_balance,
                'updated_at'      => current_time('mysql')
            ],
            ['id' => $wallet->id]
        );

        if (!empty($txn_id)) {
            $wpdb->update(
                $table_txns,
                [
                    'payment_status' => 'refunded'
                ],
                ['transaction_id' => $txn_id]
            );
        }

        return $new_balance;
    }

    /**
     * Deduct 1 credit for a question
     */
    public static function consume_credit($visitor_id, $user_id = 0) {
        global $wpdb;
        $table_wallets = $wpdb->prefix . self::TABLE_WALLETS;

        $wallet = self::get_wallet($visitor_id, $user_id);
        $current = intval($wallet->credits_balance);

        if ($current <= 0) {
            return false;
        }

        // Check if validity expired
        if (!empty($wallet->validity_expires_at) && $wallet->validity_expires_at !== '0000-00-00 00:00:00') {
            if (strtotime($wallet->validity_expires_at) < current_time('timestamp')) {
                return false;
            }
        }

        $new_balance = $current - 1;
        $new_consumed = intval($wallet->total_consumed) + 1;

        $wpdb->update(
            $table_wallets,
            [
                'credits_balance' => $new_balance,
                'total_consumed'  => $new_consumed,
                'updated_at'      => current_time('mysql')
            ],
            ['id' => $wallet->id]
        );

        return $new_balance;
    }

    /**
     * Get pre-configured default credit packs
     */
    public static function get_default_packs() {
        return [
            'pack_starter' => [
                'key'           => 'pack_starter',
                'title'         => 'Starter Pack (आरम्भ पैक)',
                'base_price'    => 50,
                'offer_price'   => 40,
                'base_credits'  => 40,
                'bonus_credits' => 20,
                'validity_days' => 0, // 0 = Lifetime
                'badge'         => 'Popular',
                'featured'      => 0,
                'active'        => 1,
                'description'   => '40 Base + 20 Free Bonus • 60 Questions Total • Instant Activation • Lifetime'
            ],
            'pack_value' => [
                'key'           => 'pack_value',
                'title'         => 'Shubh Labh Offer (शुभ लाभ ऑफर)',
                'base_price'    => 150,
                'offer_price'   => 100,
                'base_credits'  => 100,
                'bonus_credits' => 20,
                'validity_days' => 0, // 0 = Lifetime
                'badge'         => 'Best Value (33% OFF)',
                'featured'      => 1,
                'active'        => 1,
                'description'   => '100 Base + 20 Free Bonus • 120 Questions Total • Just ₹0.83/Question • Lifetime'
            ],
            'pack_mega' => [
                'key'           => 'pack_mega',
                'title'         => 'Maharatna Pack (महारत्न पैक)',
                'base_price'    => 350,
                'offer_price'   => 199,
                'base_credits'  => 250,
                'bonus_credits' => 60,
                'validity_days' => 0, // 0 = Lifetime
                'badge'         => 'Mega Saver (43% OFF)',
                'featured'      => 0,
                'active'        => 1,
                'description'   => '250 Base + 60 Free Bonus • 310 Questions Total • Valid Lifetime'
            ]
        ];
    }

    /**
     * Get all active packs with calculated discount, total credits, and unit price
     */
    public static function get_packs($only_active = true) {
        $saved = get_option('niyati_credit_packs', null);
        if ($saved === null || !is_array($saved) || empty($saved)) {
            $saved = self::get_default_packs();
            update_option('niyati_credit_packs', $saved);
        }

        $processed = [];
        foreach ($saved as $k => $pack) {
            if ($only_active && empty($pack['active'])) {
                continue;
            }

            $base_price    = floatval($pack['base_price'] ?? 0);
            $offer_price   = floatval($pack['offer_price'] ?? 0);
            $base_credits  = intval($pack['base_credits'] ?? 0);
            $bonus_credits = intval($pack['bonus_credits'] ?? 0);
            $validity_days = intval($pack['validity_days'] ?? 0);
            $total_credits = $base_credits + $bonus_credits;

            // Calculate savings and discount percentage
            $discount_pct = 0;
            if ($base_price > $offer_price && $base_price > 0) {
                $discount_pct = round((($base_price - $offer_price) / $base_price) * 100);
            }

            // Calculate cost per question
            $rate_per_question = ($total_credits > 0) ? round($offer_price / $total_credits, 2) : $offer_price;

            $pack['total_credits']     = $total_credits;
            $pack['validity_days']     = $validity_days;
            $pack['discount_pct']      = $discount_pct;
            $pack['savings_amount']    = max(0, $base_price - $offer_price);
            $pack['rate_per_question'] = $rate_per_question;
            $pack['bonus_label']       = ($bonus_credits > 0) ? "+{$bonus_credits} Extra Free" : '';
            $pack['validity_label']    = ($validity_days > 0) ? "{$validity_days} Days" : 'Lifetime';

            $processed[$k] = $pack;
        }

        return $processed;
    }

    /**
     * Get a single pack by key
     */
    public static function get_pack($key) {
        $packs = self::get_packs(false);
        return $packs[$key] ?? null;
    }

    /**
     * Toggle pack active/suspended status
     */
    public static function toggle_pack_status($key) {
        $packs = get_option('niyati_credit_packs', self::get_default_packs());
        if (isset($packs[$key])) {
            $packs[$key]['active'] = empty($packs[$key]['active']) ? 1 : 0;
            update_option('niyati_credit_packs', $packs);
            return $packs[$key]['active'];
        }
        return false;
    }
}
