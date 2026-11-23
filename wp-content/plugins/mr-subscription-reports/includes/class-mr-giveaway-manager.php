<?php
/**
 * MeraRashifal Admin Giveaway & Token Top-Up Manager
 *
 * Handles complimentary admin top-ups for Report Tokens and Niyati AI Chat Tokens.
 * Ensures all giveaways are accounted for in both the user's personal passbook
 * and the administrator's master financial transaction ledger (af_mr_transactions).
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Giveaway_Manager {

    /**
     * Initialize giveaway hooks and AJAX endpoints
     */
    public static function init() {
        add_action('wp_ajax_mr_search_users_for_giveaway', [__CLASS__, 'ajax_search_users']);
        add_action('wp_ajax_mr_execute_giveaway_topup', [__CLASS__, 'ajax_execute_giveaway']);
        add_action('wp_ajax_mr_get_user_giveaway_history', [__CLASS__, 'ajax_get_user_history']);
    }

    /**
     * Search users by Email, Phone Number, Display Name, or Username
     *
     * @param string $term Search query (minimum 2 characters)
     * @param int $limit Maximum results to return
     * @return array List of matched user profiles with token balances
     */
    public static function search_users($term, $limit = 20) {
        global $wpdb;

        $term = trim(sanitize_text_field($term));
        if (strlen($term) < 2) {
            return [];
        }

        $limit = max(1, min(50, (int)$limit));
        $like = '%' . $wpdb->esc_like($term) . '%';
        $user_ids = [];

        // 1. Search Core WordPress Users (email, display_name, user_login, user_nicename)
        $users_table = $wpdb->users;
        $matched_users = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$users_table} 
             WHERE user_email LIKE %s 
                OR display_name LIKE %s 
                OR user_login LIKE %s 
                OR user_nicename LIKE %s 
             LIMIT %d",
            $like, $like, $like, $like, $limit
        ));

        if (!empty($matched_users)) {
            $user_ids = array_merge($user_ids, array_map('intval', $matched_users));
        }

        // 2. Search Usermeta for Phone Numbers
        $usermeta_table = $wpdb->usermeta;
        $phone_keys = ['billing_phone', 'phone', 'mobile', 'digits_phone', 'user_phone', 'mr_phone'];
        $placeholders = implode(',', array_fill(0, count($phone_keys), '%s'));

        $query_meta = $wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$usermeta_table} 
             WHERE meta_key IN ({$placeholders}) 
               AND meta_value LIKE %s 
             LIMIT %d",
            array_merge($phone_keys, [$like, $limit])
        );

        $matched_phone_users = $wpdb->get_col($query_meta);
        if (!empty($matched_phone_users)) {
            $user_ids = array_merge($user_ids, array_map('intval', $matched_phone_users));
        }

        // 3. Search Niyati Chat Wallets Table (if table exists)
        $table_wallets = $wpdb->prefix . 'niyati_credit_wallets';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_wallets}'") === $table_wallets) {
            $wallet_matches = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT user_id FROM {$table_wallets} 
                 WHERE user_id > 0 
                   AND (phone LIKE %s OR email LIKE %s OR visitor_id LIKE %s) 
                 LIMIT %d",
                $like, $like, $like, $limit
            ));
            if (!empty($wallet_matches)) {
                $user_ids = array_merge($user_ids, array_map('intval', $wallet_matches));
            }
        }

        // Deduplicate and cap to limit
        $user_ids = array_unique($user_ids);
        if (empty($user_ids)) {
            return [];
        }

        $user_ids = array_slice($user_ids, 0, $limit);

        // Build rich profiles
        $results = [];
        foreach ($user_ids as $uid) {
            $user = get_userdata($uid);
            if (!$user) {
                continue;
            }

            // Retrieve phone number
            $phone = '';
            foreach ($phone_keys as $pk) {
                $meta_phone = get_user_meta($uid, $pk, true);
                if (!empty($meta_phone)) {
                    $phone = $meta_phone;
                    break;
                }
            }

            // Fallback phone from Niyati wallet
            if (empty($phone) && $wpdb->get_var("SHOW TABLES LIKE '{$table_wallets}'") === $table_wallets) {
                $wallet_phone = $wpdb->get_var($wpdb->prepare(
                    "SELECT phone FROM {$table_wallets} WHERE user_id = %d AND phone != '' LIMIT 1",
                    $uid
                ));
                if (!empty($wallet_phone)) {
                    $phone = $wallet_phone;
                }
            }

            // Report Token balances
            $report_tokens = class_exists('MR_Tokens') ? MR_Tokens::get_user_tokens($uid) : [];
            $total_reports_remaining = 0;
            if (!empty($report_tokens)) {
                foreach ($report_tokens as $t) {
                    $total_reports_remaining += intval($t['remaining'] ?? 0);
                }
            }

            // Niyati Chat Credit balances
            $chat_balance = 0;
            $chat_validity = 'No active credits';
            if (class_exists('Niyati_Wallet')) {
                $chat_balance = Niyati_Wallet::get_balance('user_' . $uid, $uid);
                $val_info = Niyati_Wallet::get_validity_info('user_' . $uid, $uid);
                $chat_validity = $val_info['label'] ?? 'Lifetime';
            }

            $results[] = [
                'user_id'                 => $uid,
                'display_name'            => $user->display_name ?: $user->user_login,
                'user_email'              => $user->user_email,
                'user_phone'              => $phone ?: 'Not provided',
                'user_registered'         => date('d M Y', strtotime($user->user_registered)),
                'total_reports_remaining' => $total_reports_remaining,
                'report_tokens'           => $report_tokens,
                'chat_balance'            => $chat_balance,
                'chat_validity'           => $chat_validity,
            ];
        }

        return $results;
    }

    /**
     * Execute complimentary giveaway / top-up for a user
     *
     * @param array $args [
     *     'user_id'            => int (Required),
     *     'report_tokens'      => array (slug => count, e.g. ['kundli_predictions' => 2]),
     *     'chat_credits'       => int (Number of AI chat queries to add),
     *     'chat_validity_days' => int (0 for Lifetime),
     *     'reason'             => string (Admin note / reason for audit),
     *     'admin_id'           => int (Defaults to current admin user)
     * ]
     * @return array Result [success => bool, message => string, txn_id => string]
     */
    public static function execute_giveaway($args) {
        global $wpdb;

        $user_id = isset($args['user_id']) ? intval($args['user_id']) : 0;
        if ($user_id <= 0) {
            return ['success' => false, 'message' => __('Invalid user selected.', 'merarashifal')];
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return ['success' => false, 'message' => __('User does not exist.', 'merarashifal')];
        }

        $admin_id = !empty($args['admin_id']) ? intval($args['admin_id']) : get_current_user_id();
        $admin_user = get_userdata($admin_id);
        $admin_name = $admin_user ? ($admin_user->display_name ?: $admin_user->user_login) : 'Admin';

        $report_tokens_input = isset($args['report_tokens']) && is_array($args['report_tokens']) ? $args['report_tokens'] : [];
        $chat_credits        = isset($args['chat_credits']) ? max(0, intval($args['chat_credits'])) : 0;
        $chat_validity_days  = isset($args['chat_validity_days']) ? max(0, intval($args['chat_validity_days'])) : 0;
        $reason              = !empty($args['reason']) ? sanitize_text_field($args['reason']) : 'Complimentary Admin Giveaway';

        // Filter out zero / negative report token counts
        $sanitized_report_tokens = [];
        $total_report_tokens_added = 0;
        foreach ($report_tokens_input as $slug => $count) {
            $cnt = intval($count);
            if ($cnt > 0) {
                $clean_slug = sanitize_key($slug);
                $sanitized_report_tokens[$clean_slug] = $cnt;
                $total_report_tokens_added += $cnt;
            }
        }

        // Validation: must give at least 1 report token OR 1 chat credit
        if ($total_report_tokens_added <= 0 && $chat_credits <= 0) {
            return [
                'success' => false,
                'message' => __('Please specify at least 1 Report Token or 1 Chat Query Point to credit.', 'merarashifal'),
            ];
        }

        // 1. Generate unique internal transaction ID
        $txn_id = 'GIVEAWAY-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        // 2. Fetch User Phone (for transaction snapshot)
        $customer_phone = '';
        $phone_keys = ['billing_phone', 'phone', 'mobile', 'digits_phone', 'user_phone'];
        foreach ($phone_keys as $pk) {
            $val = get_user_meta($user_id, $pk, true);
            if (!empty($val)) {
                $customer_phone = $val;
                break;
            }
        }

        // 3. Credit Report Tokens
        $credited_reports_summary = [];
        if (!empty($sanitized_report_tokens) && class_exists('MR_Tokens')) {
            $all_reports = class_exists('MR_Packs') ? MR_Packs::get_reports() : [];
            foreach ($sanitized_report_tokens as $slug => $count) {
                MR_Tokens::credit_single_report_token($user_id, $slug, $count);
                $rep_name = $all_reports[$slug]['name'] ?? ucwords(str_replace('_', ' ', $slug));
                $credited_reports_summary[] = "{$count}x {$rep_name}";
            }
        }

        // 4. Credit Niyati AI Chat Tokens
        $credited_chat_summary = '';
        if ($chat_credits > 0) {
            $visitor_id = 'user_' . $user_id;
            if (class_exists('Niyati_Wallet')) {
                Niyati_Wallet::add_credits(
                    $visitor_id,
                    $user_id,
                    $chat_credits,
                    0.00,
                    $txn_id,
                    'admin_giveaway',
                    'admin_giveaway',
                    'Admin Giveaway: ' . $reason,
                    $chat_validity_days,
                    ['admin_id' => $admin_id, 'reason' => $reason]
                );
            }
            $credited_chat_summary = "{$chat_credits} AI Chat Queries (" . ($chat_validity_days > 0 ? "{$chat_validity_days} Days" : "Lifetime") . ")";
        }

        // Build composite item title for the passbook and master ledger
        $item_parts = [];
        if (!empty($credited_reports_summary)) {
            $item_parts[] = implode(', ', $credited_reports_summary);
        }
        if (!empty($credited_chat_summary)) {
            $item_parts[] = $credited_chat_summary;
        }
        $item_title = '🎁 Free Giveaway: ' . implode(' + ', $item_parts);

        // 5. Record in Universal Master Financial Transaction Ledger (af_mr_transactions)
        if (class_exists('MR_Transactions')) {
            $meta_payload = [
                'type'                    => 'admin_giveaway',
                'admin_id'                => $admin_id,
                'admin_name'              => $admin_name,
                'reason'                  => $reason,
                'report_tokens_credited'  => $sanitized_report_tokens,
                'total_reports_credited'  => $total_report_tokens_added,
                'chat_credits_credited'   => $chat_credits,
                'chat_validity_days'      => $chat_validity_days,
                'timestamp'               => current_time('mysql'),
            ];

            MR_Transactions::create_transaction([
                'internal_txn_id'    => $txn_id,
                'user_id'            => $user_id,
                'customer_name'      => $user->display_name ?: $user->user_login,
                'customer_email'     => $user->user_email,
                'customer_phone'     => $customer_phone,
                'channel'            => 'admin_giveaway',
                'item_key'           => 'complimentary_topup',
                'item_name'          => $item_title,
                'txn_type'           => 'giveaway',
                'gross_amount'       => 0.00,
                'discount_amount'    => 0.00,
                'net_amount'         => 0.00,
                'currency'           => 'INR',
                'gateway'            => 'admin_manual',
                'gateway_mode'       => 'live',
                'gateway_order_id'   => 'GIVEAWAY_ORDER',
                'gateway_payment_id' => 'FREE_' . time(),
                'gateway_signature'  => 'ADMIN_MANUAL_AUTHORIZED',
                'status'             => 'completed',
                'metadata'           => $meta_payload,
            ]);
        }

        // Log admin activity audit
        error_log(sprintf(
            '[MeraRashifal Giveaway] Admin %s (ID %d) credited user %s (ID %d) with %s. Txn: %s. Reason: %s',
            $admin_name,
            $admin_id,
            $user->user_email,
            $user_id,
            $item_title,
            $txn_id,
            $reason
        ));

        // Get fresh balances
        $fresh_reports = class_exists('MR_Tokens') ? MR_Tokens::get_user_tokens($user_id) : [];
        $fresh_chat = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_balance('user_' . $user_id, $user_id) : 0;

        return [
            'success'            => true,
            'txn_id'             => $txn_id,
            'item_title'         => $item_title,
            'message'            => sprintf(
                __('Successfully credited %s to %s! Recorded as free giveaway in Master Ledger (Ref: %s).', 'merarashifal'),
                $item_title,
                $user->display_name,
                $txn_id
            ),
            'fresh_report_tokens'=> $fresh_reports,
            'fresh_chat_balance' => $fresh_chat,
        ];
    }

    /**
     * AJAX handler: Search users for giveaway
     */
    public static function ajax_search_users() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'merarashifal')], 403);
        }

        check_ajax_referer('mr_giveaway_admin_nonce', 'nonce');

        $term = sanitize_text_field($_GET['term'] ?? ($_POST['term'] ?? ''));
        $users = self::search_users($term, 15);

        wp_send_json_success(['users' => $users]);
    }

    /**
     * AJAX handler: Execute giveaway top-up
     */
    public static function ajax_execute_giveaway() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'merarashifal')], 403);
        }

        check_ajax_referer('mr_giveaway_admin_nonce', 'nonce');

        $user_id            = intval($_POST['user_id'] ?? 0);
        $chat_credits       = intval($_POST['chat_credits'] ?? 0);
        $chat_validity_days = intval($_POST['chat_validity_days'] ?? 0);
        $reason             = sanitize_text_field($_POST['reason'] ?? '');
        $report_tokens      = isset($_POST['report_tokens']) && is_array($_POST['report_tokens']) ? $_POST['report_tokens'] : [];

        $res = self::execute_giveaway([
            'user_id'            => $user_id,
            'report_tokens'      => $report_tokens,
            'chat_credits'       => $chat_credits,
            'chat_validity_days' => $chat_validity_days,
            'reason'             => $reason,
            'admin_id'           => get_current_user_id(),
        ]);

        if (!empty($res['success'])) {
            wp_send_json_success($res);
        } else {
            wp_send_json_error(['message' => $res['message'] ?? __('Failed to credit giveaway.', 'merarashifal')]);
        }
    }

    /**
     * AJAX handler: Fetch user's past giveaway transactions
     */
    public static function ajax_get_user_history() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'merarashifal')], 403);
        }

        check_ajax_referer('mr_giveaway_admin_nonce', 'nonce');

        $user_id = intval($_GET['user_id'] ?? 0);
        if ($user_id <= 0) {
            wp_send_json_error(['message' => __('Invalid user.', 'merarashifal')]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE user_id = %d 
               AND (channel = 'admin_giveaway' OR txn_type = 'giveaway') 
             ORDER BY id DESC 
             LIMIT 10",
            $user_id
        ), ARRAY_A);

        wp_send_json_success(['history' => $rows ?: []]);
    }

    /**
     * Render Admin Dashboard Page for Free Giveaways & Token Top-Up
     */
    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'merarashifal'));
        }

        global $wpdb;
        $nonce = wp_create_nonce('mr_giveaway_admin_nonce');

        // Master Giveaway Stats
        $table_txns = $wpdb->prefix . 'mr_transactions';
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_giveaways,
                COUNT(DISTINCT user_id) as total_users,
                SUM(CASE WHEN channel = 'admin_giveaway' OR txn_type = 'giveaway' THEN 1 ELSE 0 END) as giveaway_count
            FROM {$table_txns}
            WHERE channel = 'admin_giveaway' OR txn_type = 'giveaway'
        ");

        $total_giveaways = intval($stats->total_giveaways ?? 0);
        $total_users = intval($stats->total_users ?? 0);

        // Pre-selected user via GET if any
        $preselected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $preselected_user_json = 'null';
        if ($preselected_user_id > 0) {
            $user_matches = self::search_users(strval($preselected_user_id), 1);
            if (!empty($user_matches)) {
                $preselected_user_json = wp_json_encode($user_matches[0]);
            }
        }

        // Available active report types
        $available_reports = [
            'kundli_predictions'      => ['name' => 'Janam Kundli & Life Predictions', 'icon' => 'dashicons-chart-pie', 'badge' => 'Kundli'],
            'yearly_horoscope_long'   => ['name' => 'Yearly Horoscope Long Report', 'icon' => 'dashicons-calendar-alt', 'badge' => 'Yearly'],
            'kundli_matching'         => ['name' => 'Kundli Matching & Gun Milan', 'icon' => 'dashicons-heart', 'badge' => 'Match'],
            'palmistry_report'        => ['name' => 'Palmistry Reading Report', 'icon' => 'dashicons-visibility', 'badge' => 'Palm'],
            'numerology_report'       => ['name' => 'Numerology & Life Path Report', 'icon' => 'dashicons-calculator', 'badge' => 'Numero'],
            'mahadasha_timing'        => ['name' => 'Mahadasha & Antardasha Timing', 'icon' => 'dashicons-clock', 'badge' => 'Dasha'],
            'dosh_remedial'           => ['name' => 'Kundali Dosh & Remedies Report', 'icon' => 'dashicons-shield', 'badge' => 'Dosh'],
            'monthly_horoscope_vedic' => ['name' => 'Monthly Horoscope (Vedic)', 'icon' => 'dashicons-calendar', 'badge' => 'Monthly'],
        ];

        ?>
        <div class="wrap" style="max-width:1200px;">
            <!-- Header Section -->
            <div style="background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:#ffffff; padding:24px 28px; border-radius:12px; margin-top:15px; margin-bottom:22px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                    <div>
                        <h1 style="color:#ffffff; font-size:24px; font-weight:800; margin:0 0 6px 0; display:flex; align-items:center; gap:10px;">
                            <span>🎁</span> Free Token Top-Up & Giveaway Manager
                        </h1>
                        <p style="margin:0; color:#94a3b8; font-size:13px; max-width:750px; line-height:1.5;">
                            Search any user by <strong>Email ID</strong> or <strong>Phone Number</strong>. Credit complimentary Report Tokens and Niyati AI Chat Tokens in real time. All giveaways are logged in both the <strong>Master Financial Ledger (₹0.00)</strong> and the user's Passbook.
                        </p>
                    </div>
                    <div style="display:flex; gap:12px;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mr-transactions&channel=admin_giveaway')); ?>" class="button button-secondary" style="background:rgba(255,255,255,0.12); color:#ffffff; border-color:rgba(255,255,255,0.25); height:36px; line-height:34px; font-weight:600;">
                            <span class="dashicons dashicons-book" style="vertical-align:middle; margin-top:-2px;"></span> View Giveaway Ledger
                        </a>
                    </div>
                </div>

                <!-- KPI Metric Pills -->
                <div style="display:flex; gap:18px; margin-top:20px; flex-wrap:wrap; border-top:1px solid rgba(255,255,255,0.1); padding-top:16px;">
                    <div style="background:rgba(255,255,255,0.06); padding:10px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);">
                        <div style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px;">Giveaways Issued</div>
                        <div style="font-size:22px; font-weight:800; color:#38bdf8; margin-top:2px;"><?php echo number_format($total_giveaways); ?></div>
                    </div>
                    <div style="background:rgba(255,255,255,0.06); padding:10px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);">
                        <div style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px;">Users Rewarded</div>
                        <div style="font-size:22px; font-weight:800; color:#34d399; margin-top:2px;"><?php echo number_format($total_users); ?></div>
                    </div>
                    <div style="background:rgba(255,255,255,0.06); padding:10px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);">
                        <div style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; letter-spacing:0.5px;">Financial Value</div>
                        <div style="font-size:22px; font-weight:800; color:#fbbf24; margin-top:2px;">₹0.00 <span style="font-size:12px; font-weight:600; color:#94a3b8;">(Complimentary)</span></div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Search User Section -->
            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:22px 24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); margin-bottom:22px;">
                <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 6px 0; display:flex; align-items:center; gap:8px;">
                    <span style="background:#0284c7; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:12px;">1</span>
                    Find User to Reward
                </h2>
                <p style="color:#64748b; font-size:13px; margin:0 0 14px 0;">
                    Search by <strong>Email Address</strong> (e.g. <code>customer@gmail.com</code>) or <strong>Phone Number</strong> (e.g. <code>9876543210</code>) or Name.
                </p>

                <div style="position:relative; max-width:650px;">
                    <div style="display:flex; gap:10px;">
                        <div style="position:relative; flex:1;">
                            <span class="dashicons dashicons-search" style="position:absolute; left:12px; top:11px; color:#94a3b8; font-size:18px;"></span>
                            <input type="text" id="mr-user-search-input" placeholder="Type email, phone number, or name..." autocomplete="off" style="width:100%; height:42px; padding-left:38px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:14px;" />
                            <span id="mr-search-spinner" class="spinner" style="position:absolute; right:10px; top:10px; margin:0;"></span>
                        </div>
                        <button type="button" id="mr-btn-trigger-search" class="button button-primary" style="height:42px; padding:0 20px; font-size:14px; font-weight:600; border-radius:8px;">
                            Search
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div id="mr-user-search-dropdown" style="display:none; position:absolute; left:0; right:0; top:46px; background:#ffffff; border:1.5px solid #cbd5e1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.12); z-index:9999; max-height:320px; overflow-y:auto;">
                    </div>
                </div>
            </div>

            <!-- Step 2: Selected User Profile & Balances Card -->
            <div id="mr-selected-user-card" style="display:none; background:#ffffff; border:2px solid #38bdf8; border-radius:10px; padding:22px 24px; box-shadow:0 2px 8px rgba(14,165,233,0.1); margin-bottom:22px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:15px;">
                    <div style="display:flex; gap:16px; align-items:center;">
                        <div id="mr-user-avatar-badge" style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                            U
                        </div>
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <h3 id="mr-user-name" style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">User Name</h3>
                                <span id="mr-user-id-badge" style="background:#f1f5f9; color:#475569; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #e2e8f0;">UID: #0</span>
                            </div>
                            <div style="display:flex; gap:16px; margin-top:4px; font-size:12.5px; color:#64748b; flex-wrap:wrap;">
                                <span><span class="dashicons dashicons-email" style="font-size:14px; vertical-align:middle;"></span> <strong id="mr-user-email">-</strong></span>
                                <span><span class="dashicons dashicons-phone" style="font-size:14px; vertical-align:middle;"></span> <strong id="mr-user-phone">-</strong></span>
                                <span><span class="dashicons dashicons-calendar-alt" style="font-size:14px; vertical-align:middle;"></span> Member Since: <strong id="mr-user-registered">-</strong></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="mr-btn-change-user" class="button button-small" style="color:#64748b;">
                        Change User &times;
                    </button>
                </div>

                <!-- Current Balances Display -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px; margin-top:18px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px 18px;">
                    <div>
                        <div style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; letter-spacing:0.5px;">📄 Report Tokens Remaining</div>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                            <span id="mr-live-report-tokens-count" style="font-size:24px; font-weight:800; color:#0284c7;">0</span>
                            <span style="font-size:12px; color:#64748b;">Tokens across all reports</span>
                        </div>
                        <div id="mr-report-tokens-breakdown" style="display:flex; flex-wrap:wrap; gap:5px; margin-top:8px;"></div>
                    </div>

                    <div style="border-left:1px solid #e2e8f0; padding-left:18px;">
                        <div style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; letter-spacing:0.5px;">💬 Niyati AI Chat Balance</div>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                            <span id="mr-live-chat-credits-count" style="font-size:24px; font-weight:800; color:#7e22ce;">0</span>
                            <span style="font-size:12px; color:#64748b;">Queries available</span>
                        </div>
                        <div style="font-size:12px; color:#6b21a8; font-weight:600; margin-top:8px;">
                            Validity: <span id="mr-live-chat-validity">Lifetime</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Top-Up Controls Form -->
            <div id="mr-giveaway-controls-card" style="display:none; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); margin-bottom:22px;">
                <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
                    <span style="background:#059669; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:12px;">2</span>
                    Select Tokens & Query Points to Credit
                </h2>

                <form id="mr-form-giveaway" onsubmit="return false;">
                    <input type="hidden" id="mr-giveaway-user-id" value="0" />

                    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px; flex-wrap:wrap;">
                        <!-- Left Column: Report Tokens -->
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h3 style="margin:0; font-size:14px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:6px;">
                                    <span class="dashicons dashicons-media-document" style="color:#0284c7;"></span> Report Tokens
                                </h3>
                                <div style="display:flex; gap:6px;">
                                    <button type="button" class="button button-small mr-preset-report-btn" data-add="1" title="Add 1 token to all reports">+1 All</button>
                                    <button type="button" class="button button-small mr-preset-report-btn" data-add="2" title="Add 2 tokens to all reports">+2 All</button>
                                    <button type="button" class="button button-small mr-preset-report-btn" data-add="0" title="Clear all report inputs">Reset</button>
                                </div>
                            </div>

                            <p style="font-size:12px; color:#64748b; margin:0 0 14px 0;">Enter the number of free report generations to grant for each report type:</p>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <?php foreach ($available_reports as $slug => $r_info): ?>
                                    <div style="background:#ffffff; border:1px solid #cbd5e1; border-radius:6px; padding:10px; display:flex; justify-content:space-between; align-items:center;">
                                        <div style="flex:1; padding-right:8px;">
                                            <span style="font-size:10px; font-weight:700; background:#e0f2fe; color:#0369a1; padding:1px 5px; border-radius:3px; text-transform:uppercase;">
                                                <?php echo esc_html($r_info['badge']); ?>
                                            </span>
                                            <div style="font-size:12px; font-weight:600; color:#1e293b; margin-top:3px; line-height:1.3;">
                                                <?php echo esc_html($r_info['name']); ?>
                                            </div>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            <input type="number" min="0" max="99" value="0" name="report_tokens[<?php echo esc_attr($slug); ?>]" class="mr-report-token-input" style="width:55px; text-align:center; height:32px; font-weight:700; border-radius:4px;" />
                                            <button type="button" class="button button-small mr-step-add-btn" data-target="report_tokens[<?php echo esc_attr($slug); ?>]" style="padding:0 7px; height:32px; line-height:30px; font-weight:700;">+1</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Right Column: Niyati AI Chat Queries -->
                        <div style="background:#fdf4ff; border:1px solid #f0abfc; border-radius:8px; padding:18px;">
                            <h3 style="margin:0 0 12px 0; font-size:14px; font-weight:700; color:#7e22ce; display:flex; align-items:center; gap:6px;">
                                <span class="dashicons dashicons-format-chat" style="color:#a855f7;"></span> Niyati AI Chat Tokens (Query Points)
                            </h3>

                            <p style="font-size:12px; color:#86198f; margin:0 0 14px 0;">Select how many questions the user can ask Niyati without payment:</p>

                            <!-- Quick Preset Buttons -->
                            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
                                <button type="button" class="button mr-chat-preset-btn" data-val="5" style="font-weight:700; border-radius:6px;">+5 Questions</button>
                                <button type="button" class="button mr-chat-preset-btn" data-val="10" style="font-weight:700; border-radius:6px;">+10 Questions</button>
                                <button type="button" class="button mr-chat-preset-btn" data-val="25" style="font-weight:700; border-radius:6px;">+25 Questions</button>
                                <button type="button" class="button mr-chat-preset-btn" data-val="50" style="font-weight:700; border-radius:6px;">+50 Questions</button>
                                <button type="button" class="button mr-chat-preset-btn" data-val="100" style="font-weight:700; border-radius:6px;">+100 Questions</button>
                            </div>

                            <div style="margin-bottom:16px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#581c87; margin-bottom:4px;">Questions Count:</label>
                                <input type="number" id="mr-input-chat-credits" min="0" max="999" value="0" style="width:100%; height:38px; border-radius:6px; font-size:15px; font-weight:700; color:#6b21a8;" />
                            </div>

                            <div style="margin-bottom:16px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#581c87; margin-bottom:4px;">Credits Validity Window:</label>
                                <select id="mr-select-chat-validity" style="width:100%; height:38px; border-radius:6px; font-size:13px; font-weight:600;">
                                    <option value="0" selected>👑 Lifetime / No Expiry (Recommended)</option>
                                    <option value="30">30 Days</option>
                                    <option value="90">90 Days</option>
                                    <option value="180">180 Days (6 Months)</option>
                                    <option value="365">365 Days (1 Year)</option>
                                </select>
                            </div>

                            <div style="background:#ffffff; border:1px dashed #d8b4fe; border-radius:6px; padding:10px 12px; font-size:11.5px; color:#7e22ce;">
                                💡 <strong>Stacking:</strong> If user already has active queries, the new count will stack onto their existing balance.
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Audit Reason & Authorization -->
                    <div style="margin-top:20px; background:#fffbeb; border:1.5px solid #fde68a; border-radius:8px; padding:16px 20px;">
                        <label for="mr-input-reason" style="display:block; font-size:12.5px; font-weight:700; color:#92400e; margin-bottom:6px;">
                            <span class="dashicons dashicons-shield" style="font-size:14px; vertical-align:middle;"></span> Audit Note / Justification (Logged in Master Ledger):
                        </label>
                        <input type="text" id="mr-input-reason" placeholder="e.g. Customer Support Resolution, Live System Verification, VIP Loyalty Top-Up" style="width:100%; height:38px; border-radius:6px; border:1.5px solid #fcd34d; font-size:13px;" />
                    </div>

                    <!-- Action Button & Feedback -->
                    <div style="margin-top:22px; display:flex; align-items:center; gap:16px;">
                        <button type="button" id="mr-btn-submit-giveaway" class="button button-primary button-hero" style="background:#059669; border-color:#047857; font-weight:800; font-size:15px; padding:0 32px; height:48px; border-radius:8px; box-shadow:0 4px 10px rgba(5,150,105,0.25);">
                            <span class="dashicons dashicons-awards" style="vertical-align:middle; margin-top:-3px;"></span> Credit Tokens as Free Giveaway
                        </button>
                        <span id="mr-giveaway-spinner" class="spinner" style="float:none; margin:0;"></span>
                    </div>

                    <div id="mr-giveaway-status-alert" style="display:none; margin-top:18px; padding:14px 18px; border-radius:8px; font-size:13.5px; font-weight:600;"></div>
                </form>
            </div>

            <!-- Past Giveaway Audit History for Selected User -->
            <div id="mr-user-history-card" style="display:none; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:22px 24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 12px 0;">
                    📜 Recent Giveaways for This User
                </h3>
                <div id="mr-user-history-table-container">
                    <p style="color:#94a3b8; font-size:13px;">Loading history...</p>
                </div>
            </div>
        </div>

        <!-- Embedded Interactive JavaScript Logic -->
        <script>
        jQuery(document).ready(function($) {
            var selectedUser = null;
            var searchTimeout = null;
            var nonce = '<?php echo esc_js($nonce); ?>';
            var preselectedUser = <?php echo $preselected_user_json; ?>;

            function renderUserCard(u) {
                selectedUser = u;
                $('#mr-giveaway-user-id').val(u.user_id);
                $('#mr-user-name').text(u.display_name);
                $('#mr-user-id-badge').text('UID: #' + u.user_id);
                $('#mr-user-email').text(u.user_email);
                $('#mr-user-phone').text(u.user_phone || 'None');
                $('#mr-user-registered').text(u.user_registered);
                $('#mr-user-avatar-badge').text((u.display_name || 'U').charAt(0).toUpperCase());

                // Update live counters
                $('#mr-live-report-tokens-count').text(u.total_reports_remaining || 0);
                $('#mr-live-chat-credits-count').text(u.chat_balance || 0);
                $('#mr-live-chat-validity').text(u.chat_validity || 'Lifetime');

                // Breakdown pills
                var $bd = $('#mr-report-tokens-breakdown').empty();
                if (u.report_tokens) {
                    $.each(u.report_tokens, function(slug, r) {
                        if (r.remaining > 0) {
                            $bd.append($('<span></span>', {
                                text: r.name + ': ' + r.remaining,
                                css: {
                                    fontSize: '11px',
                                    fontWeight: '700',
                                    background: '#e0f2fe',
                                    color: '#0369a1',
                                    padding: '2px 7px',
                                    borderRadius: '4px',
                                    border: '1px solid #bae6fd'
                                }
                            }));
                        }
                    });
                }
                if ($bd.is(':empty')) {
                    $bd.html('<span style="font-size:11px; color:#94a3b8; font-style:italic;">No active report tokens</span>');
                }

                $('#mr-selected-user-card').slideDown(200);
                $('#mr-giveaway-controls-card').slideDown(200);
                $('#mr-user-history-card').slideDown(200);

                // Fetch history
                loadUserHistory(u.user_id);
            }

            function loadUserHistory(userId) {
                var $container = $('#mr-user-history-table-container');
                $container.html('<p style="color:#64748b; font-size:12px;">Loading past giveaways...</p>');

                $.get(ajaxurl, {
                    action: 'mr_get_user_giveaway_history',
                    nonce: nonce,
                    user_id: userId
                }, function(res) {
                    if (res && res.success && res.data && res.data.history) {
                        var rows = res.data.history;
                        if (rows.length === 0) {
                            $container.html('<p style="color:#94a3b8; font-size:12px; font-style:italic; margin:0;">No previous complimentary giveaways recorded for this user.</p>');
                            return;
                        }
                        var html = '<table class="widefat striped" style="border:1px solid #e2e8f0; font-size:12px;">' +
                            '<thead><tr>' +
                            '<th>Date</th>' +
                            '<th>Transaction Ref</th>' +
                            '<th>Items Credited</th>' +
                            '<th>Admin Notes</th>' +
                            '<th>Amount</th>' +
                            '<th>Status</th>' +
                            '</tr></thead><tbody>';
                        $.each(rows, function(i, r) {
                            var meta = {};
                            try { meta = JSON.parse(r.metadata || '{}'); } catch(e){}
                            html += '<tr>' +
                                '<td>' + r.created_at + '</td>' +
                                '<td><code style="font-size:11px;">' + r.internal_txn_id + '</code></td>' +
                                '<td><strong>' + r.item_name + '</strong></td>' +
                                '<td><span style="color:#64748b;">' + (meta.reason || 'Admin Giveaway') + ' (by ' + (meta.admin_name || 'Admin') + ')</span></td>' +
                                '<td><span style="color:#059669; font-weight:700;">FREE (₹0.00)</span></td>' +
                                '<td><span style="color:#059669; font-weight:700;">🎁 Credited</span></td>' +
                                '</tr>';
                        });
                        html += '</tbody></table>';
                        $container.html(html);
                    }
                });
            }

            // Real-time Search debounce
            $('#mr-user-search-input').on('input', function() {
                var term = $(this).val().trim();
                clearTimeout(searchTimeout);
                if (term.length < 2) {
                    $('#mr-user-search-dropdown').hide().empty();
                    return;
                }
                $('#mr-search-spinner').addClass('is-active');
                searchTimeout = setTimeout(function() {
                    performSearch(term);
                }, 300);
            });

            $('#mr-btn-trigger-search').on('click', function() {
                var term = $('#mr-user-search-input').val().trim();
                if (term.length >= 2) {
                    $('#mr-search-spinner').addClass('is-active');
                    performSearch(term);
                }
            });

            function performSearch(term) {
                $.get(ajaxurl, {
                    action: 'mr_search_users_for_giveaway',
                    nonce: nonce,
                    term: term
                }, function(res) {
                    $('#mr-search-spinner').removeClass('is-active');
                    var $dropdown = $('#mr-user-search-dropdown').empty();
                    if (res && res.success && res.data && res.data.users) {
                        var users = res.data.users;
                        if (users.length === 0) {
                            $dropdown.html('<div style="padding:12px 16px; font-size:13px; color:#64748b;">No users found matching "<strong>' + $('<div>').text(term).html() + '</strong>".</div>').show();
                            return;
                        }
                        $.each(users, function(i, u) {
                            var $item = $('<div style="padding:10px 16px; border-bottom:1px solid #f1f5f9; cursor:pointer; display:flex; justify-content:space-between; align-items:center;"></div>');
                            $item.hover(
                                function(){ $(this).css('background', '#f0f9ff'); },
                                function(){ $(this).css('background', '#ffffff'); }
                            );
                            var left = '<div>' +
                                '<strong style="color:#0f172a; font-size:13px;">' + u.display_name + '</strong> ' +
                                '<span style="font-size:11px; color:#64748b;">(' + u.user_email + ')</span>' +
                                '<div style="font-size:11.5px; color:#64748b; margin-top:2px;">Phone: <strong>' + u.user_phone + '</strong> | UID: #' + u.user_id + '</div>' +
                                '</div>';
                            var right = '<div style="text-align:right;">' +
                                '<span style="background:#e0f2fe; color:#0369a1; font-size:11px; font-weight:700; padding:2px 7px; border-radius:4px;">' + u.total_reports_remaining + ' Reports</span> ' +
                                '<span style="background:#f3e8ff; color:#7e22ce; font-size:11px; font-weight:700; padding:2px 7px; border-radius:4px;">' + u.chat_balance + ' Queries</span>' +
                                '</div>';
                            $item.html(left + right);
                            $item.on('click', function() {
                                renderUserCard(u);
                                $dropdown.hide();
                                $('#mr-user-search-input').val(u.display_name + ' (' + u.user_email + ')');
                            });
                            $dropdown.append($item);
                        });
                        $dropdown.show();
                    }
                }).fail(function() {
                    $('#mr-search-spinner').removeClass('is-active');
                });
            }

            // Close search dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#mr-user-search-input, #mr-user-search-dropdown, #mr-btn-trigger-search').length) {
                    $('#mr-user-search-dropdown').hide();
                }
            });

            // Change user
            $('#mr-btn-change-user').on('click', function() {
                $('#mr-selected-user-card').slideUp(150);
                $('#mr-giveaway-controls-card').slideUp(150);
                $('#mr-user-history-card').slideUp(150);
                $('#mr-user-search-input').val('').focus();
                selectedUser = null;
            });

            // Preset Report buttons
            $('.mr-preset-report-btn').on('click', function() {
                var add = parseInt($(this).data('add'), 10);
                $('.mr-report-token-input').each(function() {
                    if (add === 0) {
                        $(this).val(0);
                    } else {
                        var curr = parseInt($(this).val(), 10) || 0;
                        $(this).val(curr + add);
                    }
                });
            });

            // Micro step +1 buttons
            $('.mr-step-add-btn').on('click', function() {
                var target = $(this).data('target');
                var $input = $('input[name="' + target + '"]');
                var curr = parseInt($input.val(), 10) || 0;
                $input.val(curr + 1);
            });

            // Chat preset buttons
            $('.mr-chat-preset-btn').on('click', function() {
                var val = parseInt($(this).data('val'), 10);
                var curr = parseInt($('#mr-input-chat-credits').val(), 10) || 0;
                $('#mr-input-chat-credits').val(curr + val);
            });

            // Form Submit / Execute Giveaway
            $('#mr-btn-submit-giveaway').on('click', function() {
                if (!selectedUser) {
                    alert('Please search and select a user first.');
                    return;
                }

                var userId = parseInt($('#mr-giveaway-user-id').val(), 10);
                var chatCredits = parseInt($('#mr-input-chat-credits').val(), 10) || 0;
                var chatValidity = parseInt($('#mr-select-chat-validity').val(), 10) || 0;
                var reason = $('#mr-input-reason').val().trim() || 'Complimentary Admin Giveaway';

                var reportTokens = {};
                var totalReports = 0;
                $('.mr-report-token-input').each(function() {
                    var cnt = parseInt($(this).val(), 10) || 0;
                    if (cnt > 0) {
                        var name = $(this).attr('name').replace('report_tokens[', '').replace(']', '');
                        reportTokens[name] = cnt;
                        totalReports += cnt;
                    }
                });

                if (totalReports <= 0 && chatCredits <= 0) {
                    alert('Please enter at least 1 Report Token or 1 Chat Query Point to credit.');
                    return;
                }

                var confirmMsg = 'Are you sure you want to credit ' +
                    (totalReports > 0 ? totalReports + ' Report Tokens ' : '') +
                    (totalReports > 0 && chatCredits > 0 ? 'and ' : '') +
                    (chatCredits > 0 ? chatCredits + ' Chat Queries ' : '') +
                    'to ' + selectedUser.display_name + ' as a FREE giveaway?';

                if (!confirm(confirmMsg)) {
                    return;
                }

                var $btn = $('#mr-btn-submit-giveaway').prop('disabled', true);
                var $spinner = $('#mr-giveaway-spinner').addClass('is-active');
                var $alert = $('#mr-giveaway-status-alert').hide().removeClass('notice-success notice-error');

                $.post(ajaxurl, {
                    action: 'mr_execute_giveaway_topup',
                    nonce: nonce,
                    user_id: userId,
                    report_tokens: reportTokens,
                    chat_credits: chatCredits,
                    chat_validity_days: chatValidity,
                    reason: reason
                }, function(res) {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');

                    if (res && res.success && res.data) {
                        $alert.css({ background: '#ecfdf5', color: '#065f46', border: '1.5px solid #a7f3d0' })
                            .html('<strong>✔ Giveaway Success!</strong> ' + res.data.message + '<br><span style="font-size:12px; color:#047857;">Internal Reference: <code>' + res.data.txn_id + '</code></span>')
                            .slideDown();

                        // Reset inputs
                        $('.mr-report-token-input').val(0);
                        $('#mr-input-chat-credits').val(0);

                        // Update counters live on screen
                        if (res.data.fresh_report_tokens) {
                            selectedUser.report_tokens = res.data.fresh_report_tokens;
                            var newTotal = 0;
                            $.each(res.data.fresh_report_tokens, function(k, v) { newTotal += (v.remaining || 0); });
                            $('#mr-live-report-tokens-count').text(newTotal);

                            var $bd = $('#mr-report-tokens-breakdown').empty();
                            $.each(res.data.fresh_report_tokens, function(slug, r) {
                                if (r.remaining > 0) {
                                    $bd.append($('<span></span>', {
                                        text: r.name + ': ' + r.remaining,
                                        css: { fontSize: '11px', fontWeight: '700', background: '#e0f2fe', color: '#0369a1', padding: '2px 7px', borderRadius: '4px', border: '1px solid #bae6fd' }
                                    }));
                                }
                            });
                        }
                        if (res.data.fresh_chat_balance !== undefined) {
                            $('#mr-live-chat-credits-count').text(res.data.fresh_chat_balance);
                        }

                        // Refresh history
                        loadUserHistory(userId);
                    } else {
                        var err = (res && res.data && res.data.message) ? res.data.message : 'Unknown error occurred.';
                        $alert.css({ background: '#fef2f2', color: '#991b1b', border: '1.5px solid #fecaca' })
                            .html('<strong>✖ Error:</strong> ' + $('<div>').text(err).html())
                            .slideDown();
                    }
                }).fail(function(xhr) {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    $alert.css({ background: '#fef2f2', color: '#991b1b', border: '1.5px solid #fecaca' })
                        .html('<strong>✖ Request Failed:</strong> Server returned an error (' + xhr.status + ').')
                        .slideDown();
                });
            });

            // Handle pre-selected user if passed via URL
            if (preselectedUser) {
                renderUserCard(preselectedUser);
            }
        });
        </script>
        <?php
    }
}
