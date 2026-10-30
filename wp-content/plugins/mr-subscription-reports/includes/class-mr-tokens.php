<?php
/**
 * Token & Subscription Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Tokens {

    /**
     * Assign a pack to a user
     */
    public static function assign_pack($user_id, $pack_key, $assigned_by = 0) {
        global $wpdb;

        $pack = MR_Packs::get_pack($pack_key);
        if (!$pack) {
            return new WP_Error('invalid_pack', __('Selected pack does not exist.', 'merarashifal'));
        }

        $now = current_time('mysql');
        $expiry = date('Y-m-d H:i:s', strtotime("+" . $pack['duration_months'] . " month", strtotime($now)));

        $table_subs = $wpdb->prefix . 'mr_user_subscriptions';
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';

        // 1. Mark existing active subscriptions as expired/superseded
        $wpdb->update(
            $table_subs,
            ['status' => 'expired'],
            ['user_id' => $user_id, 'status' => 'active']
        );

        // 2. Insert new subscription
        $wpdb->insert($table_subs, [
            'user_id' => $user_id,
            'pack_key' => $pack_key,
            'pack_name' => $pack['name'],
            'status' => 'active',
            'start_date' => $now,
            'expiry_date' => $expiry,
            'assigned_by' => $assigned_by,
            'created_at' => $now,
        ]);

        $sub_id = $wpdb->insert_id;

        // 3. Credit tokens for all reports
        foreach ($pack['quotas'] as $report_slug => $quota) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
                $user_id,
                $report_slug
            ));

            if ($existing) {
                // Add new quota to remaining tokens
                $new_total = $existing->tokens_total + $quota;
                $new_remaining = $existing->tokens_remaining + $quota;

                $wpdb->update(
                    $table_tokens,
                    [
                        'subscription_id' => $sub_id,
                        'tokens_total' => $new_total,
                        'tokens_remaining' => $new_remaining,
                        'updated_at' => $now,
                    ],
                    ['id' => $existing->id]
                );
            } else {
                $wpdb->insert($table_tokens, [
                    'user_id' => $user_id,
                    'subscription_id' => $sub_id,
                    'report_slug' => $report_slug,
                    'tokens_total' => $quota,
                    'tokens_used' => 0,
                    'tokens_remaining' => $quota,
                    'updated_at' => $now,
                ]);
            }
        }

        return $sub_id;
    }

    /**
     * Get active subscription for a user
     */
    public static function get_active_subscription($user_id) {
        global $wpdb;
        $table_subs = $wpdb->prefix . 'mr_user_subscriptions';
        $now = current_time('mysql');

        $sub = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_subs WHERE user_id = %d AND status = 'active' AND expiry_date >= %s ORDER BY id DESC LIMIT 1",
            $user_id,
            $now
        ));

        return $sub;
    }

    /**
     * Get user's token balances across all reports
     */
    public static function get_user_tokens($user_id) {
        global $wpdb;
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';
        $reports = MR_Packs::get_reports();

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT report_slug, tokens_total, tokens_used, tokens_remaining FROM $table_tokens WHERE user_id = %d",
            $user_id
        ), OBJECT_K);

        $balances = [];
        foreach ($reports as $slug => $data) {
            $row = $rows[$slug] ?? null;
            $balances[$slug] = [
                'name' => $data['name'],
                'icon' => $data['icon'],
                'description' => $data['description'],
                'total' => $row ? (int)$row->tokens_total : 0,
                'used' => $row ? (int)$row->tokens_used : 0,
                'remaining' => $row ? (int)$row->tokens_remaining : 0,
            ];
        }

        return $balances;
    }

    /**
     * Check if user has at least 1 token for a given report
     */
    public static function has_token($user_id, $report_slug) {
        global $wpdb;
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';

        $remaining = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT tokens_remaining FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
            $user_id,
            $report_slug
        ));

        return $remaining > 0;
    }

    /**
     * Deduct 1 token for a report
     */
    public static function deduct_token($user_id, $report_slug) {
        global $wpdb;
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, tokens_used, tokens_remaining FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
            $user_id,
            $report_slug
        ));

        if (!$row || (int)$row->tokens_remaining <= 0) {
            return false;
        }

        $updated = $wpdb->update(
            $table_tokens,
            [
                'tokens_used' => (int)$row->tokens_used + 1,
                'tokens_remaining' => (int)$row->tokens_remaining - 1,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $row->id]
        );

        return $updated !== false;
    }

    /**
     * Admin manual override for tokens
     */
    public static function set_user_token($user_id, $report_slug, $remaining) {
        global $wpdb;
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
            $user_id,
            $report_slug
        ));

        if ($existing) {
            $diff = $remaining - (int)$existing->tokens_remaining;
            $wpdb->update(
                $table_tokens,
                [
                    'tokens_total' => max(0, (int)$existing->tokens_total + $diff),
                    'tokens_remaining' => max(0, $remaining),
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $existing->id]
            );
        } else {
            $wpdb->insert($table_tokens, [
                'user_id' => $user_id,
                'report_slug' => $report_slug,
                'tokens_total' => max(0, $remaining),
                'tokens_used' => 0,
                'tokens_remaining' => max(0, $remaining),
                'updated_at' => current_time('mysql')
            ]);
        }
    }

    /**
     * Credit single report token (for a la carte purchase via simulated PG)
     */
    public static function credit_single_report_token($user_id, $report_slug, $count = 1) {
        global $wpdb;
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';
        $now = current_time('mysql');

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_tokens WHERE user_id = %d AND report_slug = %s",
            $user_id,
            $report_slug
        ));

        if ($existing) {
            $wpdb->update(
                $table_tokens,
                [
                    'tokens_total' => (int)$existing->tokens_total + $count,
                    'tokens_remaining' => (int)$existing->tokens_remaining + $count,
                    'updated_at' => $now,
                ],
                ['id' => $existing->id]
            );
        } else {
            $wpdb->insert($table_tokens, [
                'user_id' => $user_id,
                'subscription_id' => null,
                'report_slug' => $report_slug,
                'tokens_total' => $count,
                'tokens_used' => 0,
                'tokens_remaining' => $count,
                'updated_at' => $now,
            ]);
        }
        return true;
    }
}
