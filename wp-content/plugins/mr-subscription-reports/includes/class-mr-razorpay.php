<?php
/**
 * MeraRashifal Razorpay Gateway Manager & Key Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Razorpay {

    const DEFAULT_TEST_KEY_ID     = 'rzp_test_TpIAA4q3WP1EYF';
    const DEFAULT_TEST_KEY_SECRET = '4YJwgnmeWvKdYEaKmpiv9pH1';

    /**
     * Get active gateway mode: 'test' or 'live'
     */
    public static function get_mode() {
        return get_option('mr_razorpay_mode', 'test');
    }

    /**
     * Get Test Key ID
     */
    public static function get_test_key_id() {
        $val = get_option('mr_razorpay_test_key_id', self::DEFAULT_TEST_KEY_ID);
        return !empty($val) ? trim($val) : self::DEFAULT_TEST_KEY_ID;
    }

    /**
     * Get Test Key Secret
     */
    public static function get_test_key_secret() {
        $val = get_option('mr_razorpay_test_key_secret', self::DEFAULT_TEST_KEY_SECRET);
        return !empty($val) ? trim($val) : self::DEFAULT_TEST_KEY_SECRET;
    }

    /**
     * Get Live Key ID
     */
    public static function get_live_key_id() {
        return trim(get_option('mr_razorpay_live_key_id', ''));
    }

    /**
     * Get Live Key Secret
     */
    public static function get_live_key_secret() {
        return trim(get_option('mr_razorpay_live_key_secret', ''));
    }

    /**
     * Get Webhook Secret
     */
    public static function get_webhook_secret() {
        return trim(get_option('mr_razorpay_webhook_secret', ''));
    }

    /**
     * Get active Key ID based on active mode
     */
    public static function get_active_key_id() {
        if (self::get_mode() === 'live') {
            $live_key = self::get_live_key_id();
            if (!empty($live_key)) {
                return $live_key;
            }
        }
        return self::get_test_key_id();
    }

    /**
     * Get active Key Secret based on active mode
     */
    public static function get_active_key_secret() {
        if (self::get_mode() === 'live') {
            $live_secret = self::get_live_key_secret();
            if (!empty($live_secret)) {
                return $live_secret;
            }
        }
        return self::get_test_key_secret();
    }

    /**
     * Test connection to Razorpay API with given key and secret
     */
    public static function test_connection($key_id = '', $key_secret = '') {
        if (empty($key_id)) {
            $key_id = self::get_active_key_id();
        }
        if (empty($key_secret)) {
            $key_secret = self::get_active_key_secret();
        }

        if (empty($key_id) || empty($key_secret)) {
            return [
                'success' => false,
                'message' => __('Key ID or Key Secret is missing.', 'merarashifal')
            ];
        }

        $url = 'https://api.razorpay.com/v1/payments?count=1';
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret)
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => __('Network error: ', 'merarashifal') . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 200) {
            $is_live = strpos($key_id, 'rzp_live_') === 0;
            $mode_str = $is_live ? 'LIVE Production' : 'TEST Sandbox';
            return [
                'success' => true,
                'message' => sprintf(__('Connection Verified! Authenticated with Razorpay (%s).', 'merarashifal'), $mode_str)
            ];
        }

        $err_msg = isset($data['error']['description']) ? $data['error']['description'] : __('Authentication failed (HTTP ' . $status_code . '). Check your credentials.', 'merarashifal');

        return [
            'success' => false,
            'message' => $err_msg
        ];
    }

    /**
     * Poll Razorpay API and synchronize payment & refund state for a given payment ID
     *
     * @param string $payment_id Razorpay payment ID (e.g. pay_...)
     * @return array|false Updated transaction or false
     */
    public static function sync_payment($payment_id) {
        if (empty($payment_id)) {
            return false;
        }

        $key_id     = self::get_active_key_id();
        $key_secret = self::get_active_key_secret();

        if (empty($key_id) || empty($key_secret)) {
            return false;
        }

        $url = 'https://api.razorpay.com/v1/payments/' . urlencode($payment_id);
        $res = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret)
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
            return false;
        }

        $payment = json_decode(wp_remote_retrieve_body($res), true);
        if (empty($payment) || !isset($payment['status'])) {
            return false;
        }

        // 1. If Refunded on Razorpay
        if ($payment['status'] === 'refunded' || (!empty($payment['amount_refunded']) && $payment['amount_refunded'] > 0)) {
            $refund_amt = floatval($payment['amount_refunded']) / 100;
            $refund_id = '';
            $arn = '';

            // Fetch refund details to get exact refund_id and ARN
            $rf_res = wp_remote_get($url . '/refunds', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret)
                ],
                'timeout' => 10
            ]);

            if (!is_wp_error($rf_res) && wp_remote_retrieve_response_code($rf_res) === 200) {
                $rf_data = json_decode(wp_remote_retrieve_body($rf_res), true);
                if (!empty($rf_data['items'][0])) {
                    $latest_rf = $rf_data['items'][0];
                    $refund_id = $latest_rf['id'] ?? '';
                    $arn       = $latest_rf['acquirer_data']['arn'] ?? '';
                }
            }

            if (class_exists('MR_Transactions')) {
                return MR_Transactions::refund_transaction(
                    $payment_id,
                    $refund_id,
                    $refund_amt,
                    'Razorpay API Sync: ' . ($payment['refund_status'] ?? 'full'),
                    ['arn' => $arn, 'source' => 'api_sync']
                );
            }
        }

        // 2. If Captured on Razorpay but still pending in our DB
        if ($payment['status'] === 'captured' && class_exists('MR_Transactions')) {
            return MR_Transactions::complete_transaction(
                $payment['order_id'] ?? $payment_id,
                $payment_id,
                '',
                ['source' => 'api_sync']
            );
        }

        return $payment;
    }

    /**
     * Bulk reconcile recent transactions against Razorpay API
     *
     * @param int $limit
     * @return int Number of transactions checked
     */
    public static function sync_recent_transactions($limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txns = $wpdb->get_results($wpdb->prepare(
            "SELECT gateway_payment_id FROM $table WHERE gateway_payment_id != '' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT %d",
            $limit
        ), ARRAY_A);

        $synced = 0;
        foreach ($txns as $t) {
            $pid = trim($t['gateway_payment_id']);
            if (!empty($pid)) {
                self::sync_payment($pid);
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Initialize REST routes and hooks
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_webhook_route']);
    }

    /**
     * Register REST API webhook endpoint for Razorpay
     */
    public static function register_webhook_route() {
        register_rest_route('mr/v1', '/razorpay-webhook', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle incoming Razorpay Webhook events
     */
    public static function handle_webhook(WP_REST_Request $request) {
        $raw_body  = $request->get_body();
        $signature = $request->get_header('x_razorpay_signature');
        $secret    = self::get_webhook_secret();

        // If a webhook secret is configured, verify HMAC signature
        if (!empty($secret)) {
            $expected_signature = hash_hmac('sha256', $raw_body, $secret);
            if (!hash_equals($expected_signature, (string)$signature)) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Invalid webhook signature.'
                ], 401);
            }
        }

        $data = json_decode($raw_body, true);
        if (empty($data) || !is_array($data)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid JSON payload.'
            ], 400);
        }

        $event   = $data['event'] ?? '';
        $payload = $data['payload'] ?? [];

        // 1. Handle Refund Processed / Payment Refunded / Refund Created
        if ($event === 'refund.processed' || $event === 'payment.refunded' || $event === 'refund.created') {
            $refund_entity  = $payload['refund']['entity'] ?? [];
            $payment_entity = $payload['payment']['entity'] ?? [];

            $payment_id    = !empty($payment_entity['id']) ? $payment_entity['id'] : ($refund_entity['payment_id'] ?? '');
            $refund_id     = $refund_entity['id'] ?? '';
            $refund_amount = isset($refund_entity['amount']) ? (floatval($refund_entity['amount']) / 100) : null;
            $arn           = $refund_entity['acquirer_data']['arn'] ?? '';
            $status        = $refund_entity['status'] ?? 'processed';
            $reason        = !empty($refund_entity['notes']['reason']) ? $refund_entity['notes']['reason'] : ('Razorpay Webhook: ' . $event . ' (' . $status . ')');

            if (!empty($payment_id) && class_exists('MR_Transactions')) {
                $res = MR_Transactions::refund_transaction(
                    $payment_id,
                    $refund_id,
                    $refund_amount,
                    $reason,
                    [
                        'arn'   => $arn,
                        'event' => $event,
                        'mode'  => self::get_mode()
                    ]
                );

                return new WP_REST_Response([
                    'status'  => 'success',
                    'message' => 'Refund event recorded successfully.',
                    'txn'     => $res ? $res['internal_txn_id'] : null
                ], 200);
            }
        }

        // Handle Refund Failed
        if ($event === 'refund.failed') {
            $refund_entity  = $payload['refund']['entity'] ?? [];
            $payment_id     = $refund_entity['payment_id'] ?? '';
            $refund_id      = $refund_entity['id'] ?? '';

            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'Refund failed notification logged.'
            ], 200);
        }

        // 2. Handle Payment Captured (Automated fail-safe if frontend drop-off occurred)
        if ($event === 'payment.captured') {
            $payment_entity = $payload['payment']['entity'] ?? [];
            $payment_id     = $payment_entity['id'] ?? '';
            $order_id       = $payment_entity['order_id'] ?? '';

            if (!empty($payment_id) && class_exists('MR_Transactions')) {
                MR_Transactions::complete_transaction(
                    $order_id ?: $payment_id,
                    $payment_id,
                    $signature ?: '',
                    ['captured_via' => 'webhook']
                );
            }

            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'Payment capture acknowledged.'
            ], 200);
        }

        // 3. Handle Payment Failed
        if ($event === 'payment.failed') {
            $payment_entity = $payload['payment']['entity'] ?? [];
            $payment_id     = $payment_entity['id'] ?? '';
            $order_id       = $payment_entity['order_id'] ?? '';
            $err_desc       = $payment_entity['error_description'] ?? 'Payment failed on gateway';

            if (class_exists('MR_Transactions')) {
                MR_Transactions::fail_transaction(
                    $order_id ?: $payment_id,
                    $err_desc
                );
            }

            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'Payment failure logged.'
            ], 200);
        }

        return new WP_REST_Response([
            'status'  => 'ignored',
            'message' => 'Unhandled event: ' . $event
        ], 200);
    }
}
