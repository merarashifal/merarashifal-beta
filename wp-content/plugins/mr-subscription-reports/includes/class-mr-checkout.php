<?php
/**
 * MeraRashifal High-Converting Checkout Engine
 * Provides dedicated /checkout/ flow, prefilled customer data,
 * smart India (State-required) vs International (Full address) validation,
 * Razorpay capture, automated non-GST retail invoice generation,
 * email dispatch with PDF attachment, and passbook synchronization.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Checkout {

    public static function init() {
        // Register Shortcodes: [mr_checkout], [merarashifal_checkout], [mr_report_checkout]
        add_shortcode('mr_checkout', [__CLASS__, 'render_checkout_page']);
        add_shortcode('merarashifal_checkout', [__CLASS__, 'render_checkout_page']);
        add_shortcode('mr_report_checkout', [__CLASS__, 'render_checkout_page']);

        // AJAX handlers for checkout order creation and payment verification
        add_action('wp_ajax_mr_checkout_create_order', [__CLASS__, 'ajax_create_order']);
        add_action('wp_ajax_nopriv_mr_checkout_create_order', [__CLASS__, 'ajax_create_order']);

        add_action('wp_ajax_mr_checkout_verify_payment', [__CLASS__, 'ajax_verify_payment']);
        add_action('wp_ajax_nopriv_mr_checkout_verify_payment', [__CLASS__, 'ajax_verify_payment']);

        add_action('wp_ajax_mr_checkout_apply_coupon', [__CLASS__, 'ajax_apply_coupon']);
        add_action('wp_ajax_nopriv_mr_checkout_apply_coupon', [__CLASS__, 'ajax_apply_coupon']);

        // Prevent WordPress from mistaking ?name= parameter on checkout as a single post slug (which causes 404)
        add_filter('request', [__CLASS__, 'prevent_reserved_query_vars_clash'], 1);

        // Self-healing fallback: support bare "mr_checkout" without brackets in Elementor or post content
        add_filter('elementor/widget/render_content', [__CLASS__, 'filter_elementor_shortcode_widget'], 10, 2);
        add_filter('the_content', [__CLASS__, 'filter_bare_shortcode_content'], 5);
    }

    /**
     * Self-healing Elementor widget filter:
     * If user enters "mr_checkout" without square brackets in Elementor Shortcode widget,
     * seamlessly evaluate [mr_checkout] instead of printing plain text.
     */
    public static function filter_elementor_shortcode_widget($widget_content, $widget) {
        if (!is_object($widget)) {
            return $widget_content;
        }
        $name = method_exists($widget, 'get_name') ? $widget->get_name() : '';
        if ($name === 'shortcode') {
            $raw_sc = method_exists($widget, 'get_settings') ? trim((string) $widget->get_settings('shortcode')) : '';
            $clean = trim($raw_sc, "[] \t\n\r\0\x0B");
            if (in_array($clean, ['mr_checkout', 'merarashifal_checkout', 'mr_report_checkout'], true)) {
                return do_shortcode('[' . $clean . ']');
            }
        }
        return $widget_content;
    }

    /**
     * Self-healing post content filter:
     * If content contains standalone "mr_checkout" without brackets, auto-wrap in brackets.
     */
    public static function filter_bare_shortcode_content($content) {
        if (empty($content) || !is_string($content)) {
            return $content;
        }
        if (strpos($content, 'mr_checkout') !== false || strpos($content, 'merarashifal_checkout') !== false || strpos($content, 'mr_report_checkout') !== false) {
            $content = preg_replace('/(?<!\[)\b(mr_checkout|merarashifal_checkout|mr_report_checkout)\b(?!\])/i', '[$1]', $content);
        }
        return $content;
    }

    /**
     * Prevent WordPress reserved query vars (like name) from triggering 404 on the checkout page
     */
    public static function prevent_reserved_query_vars_clash($query_vars) {
        if (!empty($query_vars['pagename']) && $query_vars['pagename'] === 'checkout' && isset($query_vars['name'])) {
            unset($query_vars['name']);
        }
        return $query_vars;
    }

    /**
     * Dynamically detect and retrieve the checkout page URL
     *
     * Resolves the checkout page URL in priority order:
     * 1. Explicit admin configured option `mr_checkout_page_id`
     * 2. Auto-detection of any published WordPress page containing the [mr_checkout] shortcode
     * 3. Fallback: If a published page with slug 'checkout' exists, use it, else fallback to home_url('/checkout/')
     *
     * @param string $report_slug Optional report slug to pre-select
     * @param string $tier Optional bundle tier (single, duo, trio)
     * @return string Full checkout URL with query parameters if provided
     */
    public static function get_checkout_url($report_slug = '', $tier = '') {
        $base_url = '';

        // 1. Explicit admin configured page ID
        $page_id = intval(get_option('mr_checkout_page_id', 0));
        if ($page_id > 0) {
            $permalink = get_permalink($page_id);
            if ($permalink && !is_wp_error($permalink)) {
                $base_url = $permalink;
            }
        }

        // 1b. Explicit admin configured custom URL
        if (empty($base_url)) {
            $custom_url = get_option('mr_checkout_page_url', '');
            if (!empty($custom_url)) {
                $base_url = esc_url($custom_url);
            }
        }

        // 2. Auto-detect any published page in WordPress containing the shortcode
        if (empty($base_url)) {
            global $wpdb;
            $found_id = $wpdb->get_var("
                SELECT ID FROM {$wpdb->posts}
                WHERE post_status = 'publish'
                  AND post_type = 'page'
                  AND (
                      post_content LIKE '%[mr_checkout%' 
                      OR post_content LIKE '%[merarashifal_checkout%' 
                      OR post_content LIKE '%[mr_report_checkout%'
                  )
                ORDER BY ID ASC
                LIMIT 1
            ");
            if ($found_id) {
                $permalink = get_permalink(intval($found_id));
                if ($permalink && !is_wp_error($permalink)) {
                    $base_url = $permalink;
                }
            }
        }

        // 3. Fallback: Check if a published page exists with slug 'checkout'
        if (empty($base_url)) {
            $page = get_page_by_path('checkout');
            if ($page && $page->post_status === 'publish') {
                $base_url = get_permalink($page->ID);
            } else {
                $base_url = home_url('/checkout/');
            }
        }

        if (!empty($report_slug)) {
            $params = ['report' => $report_slug];
            if (!empty($tier)) {
                $params['tier'] = $tier;
            }
            return add_query_arg($params, $base_url);
        }

        return $base_url;
    }

    /**
     * Master list of 36 Indian States and Union Territories
     */
    public static function get_indian_states() {
        return [
            'Uttar Pradesh', 'Delhi', 'Maharashtra', 'Karnataka', 'Haryana',
            'Gujarat', 'West Bengal', 'Rajasthan', 'Tamil Nadu', 'Telangana',
            'Madhya Pradesh', 'Bihar', 'Punjab', 'Kerala', 'Andhra Pradesh',
            'Odisha', 'Jharkhand', 'Chhattisgarh', 'Assam', 'Himachal Pradesh',
            'Uttarakhand', 'Goa', 'Chandigarh', 'Jammu and Kashmir', 'Puducherry',
            'Tripura', 'Manipur', 'Meghalaya', 'Nagaland', 'Mizoram',
            'Sikkim', 'Arunachal Pradesh', 'Ladakh', 'Andaman and Nicobar Islands',
            'Dadra and Nagar Haveli and Daman and Diu', 'Lakshadweep'
        ];
    }

    /**
     * Master definitions and active coupons
     */
    public static function get_available_coupons() {
        $default_coupons = [
            'WELCOME10' => [
                'code'        => 'WELCOME10',
                'type'        => 'percent',
                'value'       => 10,
                'min_spend'   => 40,
                'description' => '10% Instant Discount on All Reports & Consultations',
                'applies_to'  => 'all'
            ],
            'NIYATI20' => [
                'code'        => 'NIYATI20',
                'type'        => 'percent',
                'value'       => 20,
                'min_spend'   => 50,
                'description' => 'Special 20% Off on Niyati AI Consultations & Chat Credits',
                'applies_to'  => 'chat_credits'
            ],
            'FLAT50' => [
                'code'        => 'FLAT50',
                'type'        => 'fixed',
                'value'       => 50,
                'min_spend'   => 100,
                'description' => 'Flat ₹50 Instant Discount on Orders ₹100 or above',
                'applies_to'  => 'all'
            ],
            'SHUBH30' => [
                'code'        => 'SHUBH30',
                'type'        => 'percent',
                'value'       => 30,
                'min_spend'   => 199,
                'description' => 'Auspicious 30% Festive Discount on Mega Packs & Trio Bundles',
                'applies_to'  => 'all'
            ]
        ];

        $saved = get_option('mr_checkout_coupons', null);
        if ($saved === null || !is_array($saved) || empty($saved)) {
            $saved = $default_coupons;
            update_option('mr_checkout_coupons', $saved);
        }

        return apply_filters('mr_checkout_available_coupons', $saved);
    }

    /**
     * Validate a coupon code against item type and price
    /**
     * Validate a coupon code against item type and price using MR_Coupons engine
     */
    public static function validate_coupon($code, $item_type = 'all', $gross_amount = 0.0, $extra_context = []) {
        $code = strtoupper(trim(sanitize_text_field($code)));
        if (empty($code)) {
            return ['valid' => false, 'message' => 'Please enter a coupon code.'];
        }

        // Map item_type to standard product scope
        $product_type = 'reports';
        if ($item_type === 'chat_credits' || $item_type === 'chat_recharge') {
            $product_type = 'chat_credits';
        } elseif ($item_type === 'subscription' || $item_type === 'subscriptions') {
            $product_type = 'subscriptions';
        }

        if (class_exists('MR_Coupons')) {
            $context = array_merge([
                'subtotal_pretax' => floatval($gross_amount),
                'product_type'    => $product_type,
                'item_slug'       => '',
                'user_id'         => get_current_user_id(),
                'user_email'      => '',
                'user_phone'      => '',
            ], $extra_context);

            $res = MR_Coupons::validate_coupon($code, $context);
            if (!empty($res['valid'])) {
                $discount = floatval($res['discount_amount']);
                if (($gross_amount - $discount) < 1.0) {
                    $discount = max(0, $gross_amount - 1.0);
                }
                return [
                    'valid'           => true,
                    'coupon_id'       => $res['coupon_id'],
                    'code'            => $res['coupon_code'],
                    'type'            => $res['discount_type'],
                    'value'           => $res['discount_value'],
                    'discount_amount' => $discount,
                    'new_total'       => max(1.0, round($gross_amount - $discount, 2)),
                    'title'           => $res['title'],
                    'message'         => $res['message']
                ];
            } else {
                return $res;
            }
        }

        $coupons = self::get_available_coupons();
        if (!isset($coupons[$code])) {
            return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        $c = $coupons[$code];
        $min_spend = floatval($c['min_spend'] ?? 0);
        if ($gross_amount < $min_spend) {
            return [
                'valid'   => false,
                'message' => sprintf('Coupon %s requires a minimum order value of ₹%s.', $code, number_format($min_spend, 2))
            ];
        }

        $applies_to = $c['applies_to'] ?? 'all';
        if ($applies_to !== 'all' && $applies_to !== $item_type) {
            return [
                'valid'   => false,
                'message' => 'This coupon is not applicable to the selected package.'
            ];
        }

        $discount = 0.0;
        if ($c['type'] === 'percent') {
            $discount = round(($gross_amount * floatval($c['value'])) / 100, 2);
        } else {
            $discount = min($gross_amount, floatval($c['value']));
        }

        if (($gross_amount - $discount) < 1.0) {
            $discount = max(0, $gross_amount - 1.0);
        }

        $new_total = round($gross_amount - $discount, 2);

        return [
            'valid'           => true,
            'coupon_id'       => 0,
            'code'            => $code,
            'type'            => $c['type'],
            'value'           => $c['value'],
            'discount_amount' => $discount,
            'new_total'       => $new_total,
            'description'     => $c['description'] ?? '',
            'message'         => sprintf('Coupon %s applied successfully! You saved ₹%s.', $code, number_format($discount, 2))
        ];
    }

    /**
     * AJAX Action: Validate & Apply Coupon Code
     */
    public static function ajax_apply_coupon() {
        check_ajax_referer('mr_reports_nonce', 'nonce');

        $code        = sanitize_text_field($_POST['coupon_code'] ?? '');
        $item_type   = sanitize_key($_POST['item_type'] ?? 'report_bundle');
        $item_slug   = sanitize_key($_POST['item_slug'] ?? '');
        $gross_price = floatval($_POST['gross_amount'] ?? ($_POST['price'] ?? 0));
        $email       = sanitize_email($_POST['email'] ?? '');
        $phone       = sanitize_text_field($_POST['phone'] ?? '');

        $context = [
            'item_slug'  => $item_slug,
            'user_email' => $email,
            'user_phone' => $phone,
            'user_id'    => get_current_user_id()
        ];

        $res = self::validate_coupon($code, $item_type, $gross_price, $context);

        if ($res['valid']) {
            wp_send_json_success($res);
        } else {
            wp_send_json_error($res);
        }
    }

    /**
     * Render the Checkout Page via Shortcode: [mr_checkout]
     *
     * Supported shortcode attributes:
     * - report: Default report slug if not present in URL query params (e.g. [mr_checkout report="yearly_horoscope_long"])
     * - tier: Default tier if not present in URL query params (single, duo, trio)
     *
     * @param array $atts Shortcode attributes
     * @return string Checkout HTML markup
     */
    public static function render_checkout_page($atts = []) {
        $atts = shortcode_atts([
            'report' => '',
            'tier'   => '',
        ], $atts, 'mr_checkout');

        $user_id = get_current_user_id();
        $user    = $user_id ? wp_get_current_user() : null;

        $type = !empty($_GET['type']) ? sanitize_key($_GET['type']) : '';
        $item = !empty($_GET['item']) ? sanitize_key($_GET['item']) : '';
        $is_chat_recharge = ($type === 'chat_credits' || $item === 'chat_recharge' || !empty($_GET['recharge']));
        $pack_key = !empty($_GET['pack']) 
            ? sanitize_key($_GET['pack']) 
            : (!empty($_GET['recharge']) ? sanitize_key($_GET['recharge']) : 'pack_value');

        $pack_desc = '';
        $validity_label = 'Lifetime';

        if ($is_chat_recharge) {
            $packs = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_packs(false) : [];
            if (!empty($packs[$pack_key])) {
                $pack = $packs[$pack_key];
            } else {
                $pack = reset($packs) ?: null;
                $pack_key = $pack ? $pack['key'] : 'pack_value';
            }

            if ($pack) {
                $package_title = $pack['title'];
                $price_sale    = floatval($pack['offer_price']);
                $price_regular = floatval($pack['base_price']);
                $chat_credits  = intval($pack['total_credits']);
                $validity_label= $pack['validity_label'] ?? 'Lifetime';
                $badge         = $pack['badge'] ?? '';
                $pack_desc     = $pack['description'] ?? '';
            } else {
                $package_title = 'Niyati AI Chat Credits Recharge';
                $price_sale    = 100;
                $price_regular = 150;
                $chat_credits  = 120;
                $badge         = 'Best Value';
            }

            $report_slug      = 'chat_credits';
            $tier             = 'single';
            $included_reports = [];
        } else {
            // Determine Report and Tier from Query Params or Shortcode Attributes
            $report_slug = !empty($_GET['report']) 
                ? sanitize_key($_GET['report']) 
                : (!empty($_GET['item']) 
                    ? sanitize_key($_GET['item']) 
                    : (!empty($atts['report']) ? sanitize_key($atts['report']) : 'yearly_horoscope_long'));
            if (class_exists('MR_Bundles')) {
                $report_slug = MR_Bundles::normalize_slug($report_slug);
            }

            $tier = !empty($_GET['tier']) 
                ? sanitize_key($_GET['tier']) 
                : (!empty($atts['tier']) ? sanitize_key($atts['tier']) : 'trio'); // default to trio (Best Buy)
            if (!in_array($tier, ['single', 'duo', 'trio'], true)) {
                $tier = 'trio';
            }

            // Fetch bundle configuration
            $bundle_cfg = class_exists('MR_Bundles') ? MR_Bundles::get_bundle_for_report($report_slug) : [];
            $tier_cfg   = $bundle_cfg['tier_' . $tier] ?? ($bundle_cfg['tier_single'] ?? []);

            // Package attributes
            $package_title = $tier_cfg['title'] ?? 'Astrological Report Package';
            $price_sale    = floatval($tier_cfg['price_sale'] ?? 299);
            $price_regular = floatval($tier_cfg['price_regular'] ?? 599);
            $chat_credits  = intval($tier_cfg['chat_credits'] ?? 0);
            $badge         = $tier_cfg['badge'] ?? '';

            // Reports included
            $paired = !empty($tier_cfg['paired_reports']) ? (array)$tier_cfg['paired_reports'] : [];
            $included_reports = array_merge([$report_slug], $paired);
        }

        $prefill_name  = !empty($_GET['customer_name']) 
            ? sanitize_text_field($_GET['customer_name']) 
            : (!empty($_GET['c_name']) 
                ? sanitize_text_field($_GET['c_name']) 
                : (!empty($_GET['name']) ? sanitize_text_field($_GET['name']) : ''));

        $prefill_email = !empty($_GET['customer_email']) 
            ? sanitize_email($_GET['customer_email']) 
            : (!empty($_GET['c_email']) 
                ? sanitize_email($_GET['c_email']) 
                : (!empty($_GET['email']) ? sanitize_email($_GET['email']) : ''));
        $prefill_phone = '';
        $prefill_state = 'Uttar Pradesh';
        $prefill_country = 'India';
        $prefill_address = '';
        $prefill_city    = '';
        $prefill_pincode = '';

        if ($user) {
            $prefill_name  = get_user_meta($user_id, 'mr_astro_name', true) ?: ($user->first_name ? trim($user->first_name . ' ' . $user->last_name) : $user->display_name);
            $prefill_email = $user->user_email;
            $raw_phone     = get_user_meta($user_id, 'billing_phone', true) ?: (get_user_meta($user_id, 'user_phone', true) ?: '');
            $clean_phone   = preg_replace('/\D/', '', $raw_phone);
            if (strlen($clean_phone) === 12 && substr($clean_phone, 0, 2) === '91') {
                $clean_phone = substr($clean_phone, 2);
            }
            if (strlen($clean_phone) > 10) {
                $clean_phone = substr($clean_phone, -10);
            }
            $prefill_phone = (strlen($clean_phone) === 10 && preg_match('/^[5-9][0-9]{9}$/', $clean_phone)) ? $clean_phone : '';
            $prefill_state = get_user_meta($user_id, 'billing_state', true) ?: 'Uttar Pradesh';
            $prefill_country = get_user_meta($user_id, 'billing_country', true) ?: 'India';
            $prefill_address = get_user_meta($user_id, 'billing_address_1', true) ?: '';
            $prefill_city    = get_user_meta($user_id, 'billing_city', true) ?: '';
            $prefill_pincode = get_user_meta($user_id, 'billing_postcode', true) ?: '';
        }

        $all_states = self::get_indian_states();
        $key_id = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_id() : '';
        $gateway_mode = class_exists('MR_Razorpay') ? MR_Razorpay::get_mode() : 'test';

        // Retrieve active coupons for the "Offers Available" modal
        $modal_coupons = [];
        if (class_exists('MR_Coupons')) {
            $db_coupons = MR_Coupons::get_all_coupons(['status' => 'active']);
            if (!empty($db_coupons)) {
                foreach ($db_coupons as $dbc) {
                    $modal_coupons[$dbc['code']] = [
                        'code'        => $dbc['code'],
                        'title'       => $dbc['title'],
                        'description' => $dbc['description'],
                        'type'        => $dbc['discount_type'],
                        'value'       => floatval($dbc['discount_value']),
                        'min_order'   => floatval($dbc['min_order_value']),
                        'max_cap'     => floatval($dbc['max_discount_cap']),
                        'scope'       => $dbc['product_scope'],
                    ];
                }
            }
        }
        if (empty($modal_coupons)) {
            $fallback_coupons = self::get_available_coupons();
            foreach ($fallback_coupons as $fb_code => $fb) {
                $modal_coupons[$fb_code] = [
                    'code'        => $fb_code,
                    'title'       => $fb['description'] ?? $fb_code,
                    'description' => $fb['description'] ?? '',
                    'type'        => $fb['type'] ?? 'percent',
                    'value'       => floatval($fb['value'] ?? 0),
                    'min_order'   => floatval($fb['min_spend'] ?? 0),
                    'max_cap'     => 0,
                    'scope'       => $fb['applies_to'] ?? 'all',
                ];
            }
        }

        ob_start();
        ?>
        <!-- Import Brand Typography & FontAwesome -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        <style>
            .mr-checkout-breakout {
                background: #FFFBEB;
                min-height: 85vh;
                padding: 30px 16px 80px 16px;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
                color: #3A312D;
            }
            .mr-checkout-container {
                max-width: 1100px;
                margin: 0 auto;
            }
            .mr-checkout-grid {
                display: grid;
                grid-template-columns: 1fr 1.15fr;
                gap: 28px;
                align-items: start;
            }
            @media (max-width: 900px) {
                .mr-checkout-grid {
                    grid-template-columns: 1fr;
                    gap: 24px;
                }
            }
            .mr-co-card {
                background: #FFFFFF;
                border: 1px solid #EADCCB;
                border-radius: 18px;
                padding: 28px 30px;
                box-shadow: 0 10px 30px rgba(122, 31, 61, 0.05);
            }
            .mr-co-title {
                font-size: 17px;
                font-weight: 700;
                color: #7A1F3D;
                margin: 0 0 18px 0;
                display: flex;
                align-items: center;
                gap: 8px;
                border-bottom: 1.5px solid #F6EDE3;
                padding-bottom: 12px;
            }
            .mr-form-group {
                margin-bottom: 16px;
            }
            .mr-form-group label {
                display: block;
                font-size: 12.5px;
                font-weight: 600;
                color: #554841;
                margin-bottom: 6px;
            }
            .mr-form-group label span.req {
                color: #DC2626;
            }
            .mr-form-input, .mr-form-select {
                width: 100%;
                box-sizing: border-box;
                padding: 11px 14px;
                border: 1.5px solid #E2D7CC;
                border-radius: 10px;
                font-family: 'Poppins', sans-serif;
                font-size: 13.5px;
                color: #1E293B;
                background: #FFFFFF;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .mr-form-input:focus, .mr-form-select:focus {
                border-color: #F58220;
                outline: none;
                box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.15);
            }
            .mr-form-row-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 14px;
            }
            @media (max-width: 600px) {
                .mr-form-row-2 {
                    grid-template-columns: 1fr;
                    gap: 12px;
                }
            }
            .mr-intl-notice {
                background: #EFF6FF;
                border-left: 3px solid #3B82F6;
                padding: 10px 14px;
                border-radius: 0 8px 8px 0;
                font-size: 12px;
                color: #1E40AF;
                margin-bottom: 14px;
                line-height: 1.45;
            }

            /* Right Summary Column */
            .mr-summary-package {
                background: linear-gradient(135deg, #FFF9F2 0%, #FFF3E1 100%);
                border: 1.5px solid #D9A441;
                border-radius: 14px;
                padding: 18px 20px;
                margin-bottom: 20px;
                position: relative;
            }
            .mr-summary-ribbon {
                position: absolute;
                top: -10px;
                right: 14px;
                background: linear-gradient(135deg, #F58220 0%, #D9A441 100%);
                color: #ffffff;
                font-size: 10px;
                font-weight: 800;
                padding: 2px 10px;
                border-radius: 9999px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mr-summary-pkg-title {
                font-size: 16px;
                font-weight: 800;
                color: #7A1F3D;
                margin: 4px 0 8px 0;
            }
            .mr-summary-items-list {
                list-style: none;
                padding: 0;
                margin: 0 0 10px 0;
            }
            .mr-summary-items-list li {
                font-size: 12.5px;
                color: #554841;
                margin-bottom: 6px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .mr-summary-items-list i {
                color: #2CB67D;
                flex-shrink: 0;
            }
            .mr-summary-credits-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #E0F2FE;
                color: #0369A1;
                font-size: 11.5px;
                font-weight: 700;
                padding: 3px 10px;
                border-radius: 6px;
                margin-top: 4px;
            }
            .mr-calc-table {
                width: 100%;
                border-collapse: collapse;
                margin: 18px 0;
            }
            .mr-calc-table td {
                padding: 7px 0;
                font-size: 13px;
                color: #554841;
            }
            .mr-calc-table td:last-child {
                text-align: right;
                font-weight: 600;
                color: #1E293B;
            }
            .mr-calc-total-row td {
                border-top: 1.5px solid #EADCCB;
                padding-top: 12px;
                font-size: 18px;
                font-weight: 800;
                color: #7A1F3D;
            }
            .mr-btn-pay {
                width: 100%;
                background: linear-gradient(135deg, #F58220 0%, #E6680D 100%);
                color: #ffffff;
                border: none;
                border-radius: 12px;
                padding: 16px 20px;
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 6px 20px rgba(245, 130, 32, 0.35);
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .mr-btn-pay:hover {
                background: linear-gradient(135deg, #E6680D 0%, #C2410C 100%);
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(245, 130, 32, 0.45);
            }
            .mr-co-trust {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                margin-top: 16px;
                font-size: 11.5px;
                color: #8C7E77;
                flex-wrap: wrap;
            }
            .mr-co-trust span {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .mr-co-trust i {
                color: #D9A441;
            }
            #mr-co-error-box {
                background: #FEF2F2;
                border: 1px solid #FECACA;
                color: #B91C1C;
                padding: 10px 14px;
                border-radius: 8px;
                font-size: 12.5px;
                margin-bottom: 14px;
                display: none;
            }

            /* Success State Overlay */
            #mr-checkout-success-view {
                display: none;
                text-align: center;
                padding: 30px 20px;
            }
            .mr-success-icon-wrap {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                background: #ECFDF5;
                color: #10B981;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                margin-bottom: 18px;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
            }

            /* Modals - Background Scroll Lock */
            body.mr-modal-open {
                overflow: hidden !important;
            }
            body.mr-modal-open header,
            body.mr-modal-open .elementor-location-header,
            body.mr-modal-open .ast-primary-header-bar,
            body.mr-modal-open .main-header-bar,
            body.mr-modal-open .elementor-widget-nav-menu,
            body.mr-modal-open .elementor-nav-menu--main {
                z-index: 1 !important;
            }

            /* Modals - MeraRashifal Brand Design System */
            .mr-modal {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(30, 20, 15, 0.72) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                z-index: 99999999 !important;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                box-sizing: border-box;
                overscroll-behavior: contain !important;
            }
            .mr-modal-content {
                background: #FFFFFF !important;
                border: 2px solid #D9A441;
                border-radius: 24px;
                box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35), 0 10px 25px rgba(0, 0, 0, 0.15);
                color: #3A312D;
                max-width: 540px;
                width: 100%;
                padding: 34px 32px;
                position: relative;
                z-index: 100000000 !important;
                max-height: 90vh;
                overflow-y: auto;
                overscroll-behavior: contain !important;
                touch-action: pan-y;
                -webkit-overflow-scrolling: touch;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                box-sizing: border-box;
            }

            /* Offers Modal Styles */
            .mr-offers-modal-backdrop {
                display: none;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(26, 16, 11, 0.72) !important;
                z-index: 99999999 !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                align-items: center;
                justify-content: center;
                padding: 16px;
                overscroll-behavior: contain !important;
            }
            .mr-offers-modal-backdrop.is-open {
                display: flex;
            }
            .mr-offers-modal-card {
                background: #FFFFFF;
                border: 1.5px solid #EADCCB;
                border-radius: 18px;
                max-width: 540px;
                width: 100%;
                max-height: 85vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 25px 60px rgba(122, 31, 61, 0.25);
                overflow: hidden;
                overscroll-behavior: contain !important;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-y;
                animation: mrOffersPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes mrOffersPop {
                0% { opacity: 0; transform: scale(0.94) translateY(12px); }
                100% { opacity: 1; transform: scale(1) translateY(0); }
            }
            .mr-offer-item-card {
                border: 1.5px dashed #D9A441;
                background: #FFFDF9;
                border-radius: 12px;
                padding: 14px 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                transition: all 0.2s ease;
            }
            .mr-offer-item-card:hover {
                border-color: #F58220;
                background: #FFF9F2;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(245, 130, 32, 0.1);
            }
            .mr-offer-code-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #FFF3E1;
                color: #7A1F3D;
                border: 1px solid #D9A441;
                font-family: monospace;
                font-size: 13px;
                font-weight: 800;
                padding: 3px 9px;
                border-radius: 6px;
                letter-spacing: 0.5px;
            }
            .mr-btn-apply-offer {
                background: #7A1F3D;
                color: #ffffff;
                border: none;
                border-radius: 8px;
                padding: 8px 16px;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s;
                white-space: nowrap;
            }
            .mr-btn-apply-offer:hover {
                background: #F58220;
                color: #ffffff;
            }
            .mr-btn-offer-applied {
                background: #ECFDF5;
                color: #065F46;
                border: 1px solid #A7F3D0;
                border-radius: 8px;
                padding: 8px 16px;
                font-size: 12px;
                font-weight: 700;
                cursor: default;
                white-space: nowrap;
            }

            /* Fullscreen Unlocking Freeze Overlay */
            html.mr-freeze-scroll, body.mr-freeze-scroll {
                overflow: hidden !important;
                height: 100% !important;
            }
            .mr-unlocking-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(26, 16, 11, 0.88);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                z-index: 99999999;
                align-items: center;
                justify-content: center;
                padding: 20px;
                user-select: none;
                -webkit-user-select: none;
                pointer-events: all;
            }
            .mr-unlocking-modal-card {
                background: #FFFFFF;
                border: 2px solid #D9A441;
                border-radius: 24px;
                max-width: 490px;
                width: 100%;
                padding: 38px 30px;
                text-align: center;
                box-shadow: 0 30px 80px rgba(0,0,0,0.45);
                animation: mrUnlockPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes mrUnlockPop {
                0% { opacity: 0; transform: scale(0.92) translateY(16px); }
                100% { opacity: 1; transform: scale(1) translateY(0); }
            }
            .mr-unlocking-icon-wrap {
                width: 86px;
                height: 86px;
                border-radius: 50%;
                background: linear-gradient(135deg, #FFF3E1 0%, #FEF3C7 100%);
                border: 2px solid #F58220;
                color: #EA580C;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 38px;
                margin: 0 auto 22px auto;
                box-shadow: 0 8px 25px rgba(245, 130, 32, 0.25);
                position: relative;
            }
            .mr-unlocking-pulse-ring {
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                border: 2px solid #F58220;
                animation: mrPulseRing 1.8s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
            }
            @keyframes mrPulseRing {
                0% { transform: scale(0.95); opacity: 0.8; }
                50% { transform: scale(1.25); opacity: 0; }
                100% { transform: scale(0.95); opacity: 0; }
            }
            .mr-unlocking-title {
                font-family: 'Playfair Display', serif;
                font-size: 22px;
                font-weight: 800;
                color: #7A1F3D;
                margin: 0 0 10px 0;
                line-height: 1.35;
            }
            .mr-unlocking-subtitle {
                font-size: 15px;
                font-weight: 700;
                color: #D97706;
                margin: 0 0 16px 0;
            }
            .mr-unlocking-status-box {
                background: #FFF9F2;
                border: 1px solid #FED7AA;
                border-radius: 12px;
                padding: 12px 16px;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .mr-unlocking-spinner {
                color: #EA580C;
                font-size: 16px;
            }
            .mr-unlocking-footer-note {
                font-size: 11.5px;
                color: #8C7E77;
                line-height: 1.5;
            }
        </style>

        <div class="mr-checkout-breakout">
            <div class="mr-checkout-container">

                <!-- MAIN CHECKOUT GRID: SUMMARY ON LEFT, CUSTOMER & BILLING ON RIGHT -->
                <div class="mr-checkout-grid" id="mr-checkout-main-grid">

                    <!-- LEFT: ORDER & VALUE SUMMARY -->
                    <div class="mr-co-card" style="border-color:#D9A441; box-shadow:0 12px 35px rgba(217, 164, 65, 0.15);">
                        <h2 class="mr-co-title">
                            <i class="fa-solid fa-receipt" style="color:#D9A441;"></i>
                            <span>Order &amp; Value Summary</span>
                        </h2>

                        <!-- Package Card -->
                        <?php if ($is_chat_recharge): ?>
                            <div class="mr-summary-package" style="background: linear-gradient(135deg, #FFF9F2 0%, #FEF3C7 100%); border: 1.5px solid #F58220;">
                                <?php if (!empty($badge)): ?>
                                    <span class="mr-summary-ribbon" style="background: linear-gradient(135deg, #F58220 0%, #E6680D 100%);"><?php echo esc_html($badge); ?></span>
                                <?php endif; ?>
                                <div class="mr-summary-pkg-title" style="color:#7A1F3D; font-size:18px;"><?php echo esc_html($package_title); ?></div>

                                <div style="display:flex; align-items:center; gap:12px; margin:12px 0 14px 0; background:#FFFFFF; padding:10px 14px; border-radius:10px; border:1px solid #FED7AA; box-shadow:0 2px 8px rgba(245, 130, 32, 0.08);">
                                    <div style="width:40px; height:40px; border-radius:50%; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                                        <i class="fa-solid fa-comments"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:16px; font-weight:800; color:#7A1F3D;"><?php echo esc_html($chat_credits); ?> Consultation Questions</div>
                                        <div style="font-size:11.5px; color:#9A3412; font-weight:600;"><i class="fa-solid fa-bolt"></i> Instant Credit &bull; <?php echo esc_html($validity_label ?? 'Lifetime Validity'); ?></div>
                                    </div>
                                </div>

                                <ul class="mr-summary-items-list" style="margin-bottom:0;">
                                    <li>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Ask deep queries on birth-chart, mahadasha &amp; remedies</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>AI precision powered by authentic Kundali &amp; planetary transits</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Official tax invoice emailed immediately with PDF receipt</span>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $clean_pkg_title = html_entity_decode($package_title, ENT_QUOTES, 'UTF-8');
                        $checkout_cat = $is_chat_recharge ? 'chat_credits' : ((!empty($_GET['type']) && $_GET['type'] === 'subscription') ? 'subscriptions' : (($tier === 'duo' || $tier === 'trio' || (!empty($_GET['type']) && $_GET['type'] === 'combo')) ? 'combo_deals' : 'reports'));
                        $active_bucket_rule = class_exists('MR_Bucket_Discount') ? MR_Bucket_Discount::get_matching_rule($checkout_cat, $price_sale, $report_slug) : null;
                        $candidates = ($active_bucket_rule && ($active_bucket_rule['upsell_type'] ?? '') === 'companion_selector') ? MR_Bucket_Discount::get_companion_upsell_candidates($report_slug) : [];
                        ?>

                        <!-- 1. CART ITEM (CONFIRMED REPORT / BUNDLE IN CART) -->
                        <?php if (!$is_chat_recharge): ?>
                            <div class="mr-summary-package" style="background:#FFFFFF; border:1.5px solid #EADCCB; border-radius:16px; padding:20px 22px; margin-bottom:16px; box-shadow:0 6px 20px rgba(122, 31, 61, 0.06); position:relative;">
                                <!-- Cart Status Header -->
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; border-bottom:1.5px dashed #F1E5D8; padding-bottom:10px;">
                                    <span style="display:inline-flex; align-items:center; gap:6px; background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; font-size:11px; font-weight:800; padding:4px 10px; border-radius:9999px; text-transform:uppercase; letter-spacing:0.5px;">
                                        <i class="fa-solid fa-cart-shopping" style="color:#10B981;"></i> In Your Cart
                                    </span>
                                    <?php if (!empty($badge)): ?>
                                        <span style="background:linear-gradient(135deg, #F58220 0%, #D9A441 100%); color:#ffffff; font-size:10px; font-weight:800; padding:3px 10px; border-radius:9999px; text-transform:uppercase; letter-spacing:0.5px;">
                                            <?php echo esc_html($badge); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="mr-summary-pkg-title" id="mr-summary-main-title" style="font-size:16.5px; font-weight:800; color:#7A1F3D; margin:0 0 12px 0; line-height:1.4;">
                                    <?php echo esc_html($clean_pkg_title); ?>
                                </div>

                                <ul class="mr-summary-items-list" id="mr-summary-items-ul" style="margin-bottom:12px;">
                                    <?php foreach ($included_reports as $r_slug): 
                                        $r_name = class_exists('MR_Bundles') ? MR_Bundles::get_report_name($r_slug) : $r_slug;
                                    ?>
                                        <li class="mr-included-base-report">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span><?php echo esc_html($r_name); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <?php if ($chat_credits > 0): ?>
                                    <div class="mr-summary-credits-badge">
                                        <i class="fa-solid fa-wand-magic-sparkles" style="color:#F58220;"></i>
                                        <span><strong>+<?php echo esc_html($chat_credits); ?> Niyati AI Consultations</strong> Included</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- 2. OPTIONAL IN-CART UPGRADE / ADD-ON DEAL (PLACED DIRECTLY BELOW CART ITEM) -->
                        <?php if ($active_bucket_rule): 
                            $rule_disc_lbl = ($active_bucket_rule['discount_type'] === 'flat') 
                                ? ('Save Flat ₹' . number_format($active_bucket_rule['discount_value'], 0)) 
                                : ($active_bucket_rule['discount_value'] . '% OFF Total Bill');
                        ?>
                            <div id="mr-bucket-nudge-box" style="background:#FFFDF9; border:1.5px dashed #F59E0B; border-radius:14px; padding:16px 18px; margin-bottom:18px; box-shadow:0 3px 12px rgba(245,158,11,0.08); transition:all 0.2s ease;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                    <span style="background:#FEF3C7; color:#B45309; border:1px solid #FDE68A; font-size:10.5px; font-weight:800; text-transform:uppercase; padding:3px 9px; border-radius:9999px; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:5px;">
                                        <i class="fa-solid fa-gift" style="color:#D97706;"></i> Optional In-Cart Upgrade
                                    </span>
                                    <span style="font-size:11.5px; font-weight:800; color:#B45309; background:#FFFBEB; border:1px solid #FCD34D; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                        <i class="fa-solid fa-bolt" style="color:#D97706;"></i> <?php echo esc_html($rule_disc_lbl); ?>
                                    </span>
                                </div>

                                <div style="font-size:14px; font-weight:800; color:#7A1F3D; margin-bottom:4px; line-height:1.35;">
                                    <?php echo esc_html($active_bucket_rule['headline']); ?>
                                </div>

                                <?php if (!empty($active_bucket_rule['subtext'])): ?>
                                <p style="font-size:11.5px; color:#6B5E57; margin:0 0 12px 0; line-height:1.45;">
                                    <?php echo esc_html($active_bucket_rule['subtext']); ?>
                                </p>
                                <?php endif; ?>

                                <?php if (($active_bucket_rule['upsell_type'] ?? '') === 'companion_selector' && !empty($candidates)): ?>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                        <select id="mr_companion_select" class="mr-form-select" style="flex:1; min-width:200px; padding:9px 12px; font-size:12.5px; border:1.5px solid #FDBA74; border-radius:8px; background:#FFFFFF;">
                                            <option value="">-- Choose Companion Report (+<?php echo esc_attr($rule_disc_lbl); ?>) --</option>
                                            <?php foreach ($candidates as $cand_slug => $cand): 
                                                $cand_p = floatval($cand['price'] ?? 99);
                                                $cand_r = 999;
                                                if (class_exists('MR_Bundles')) {
                                                    $cb = MR_Bundles::get_bundle_for_report($cand_slug);
                                                    if (!empty($cb['tier_single']['price_regular'])) {
                                                        $cand_r = floatval($cb['tier_single']['price_regular']);
                                                    }
                                                    if (!empty($cb['tier_single']['price_sale'])) {
                                                        $cand_p = floatval($cb['tier_single']['price_sale']);
                                                    }
                                                }
                                            ?>
                                                <option value="<?php echo esc_attr($cand_slug); ?>" data-name="<?php echo esc_attr($cand['name']); ?>" data-price="<?php echo esc_attr($cand_p); ?>" data-reg="<?php echo esc_attr($cand_r); ?>">
                                                    <?php echo esc_html($cand['name']); ?> (₹<?php echo esc_html($cand_p); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" id="mr-btn-add-companion" style="background:linear-gradient(135deg, #EA580C 0%, #C2410C 100%); color:#ffffff; border:none; border-radius:8px; padding:9px 16px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; box-shadow:0 3px 8px rgba(234,88,12,0.25);">
                                            <i class="fa-solid fa-plus"></i> Add &amp; Save
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div style="display:flex; align-items:center; justify-content:space-between; background:#FFFFFF; padding:12px 14px; border-radius:10px; border:1px solid #FED7AA; gap:12px; flex-wrap:wrap; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                                        <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:200px;">
                                            <div style="width:36px; height:36px; border-radius:8px; background:#FFF3E1; color:#EA580C; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                                                <i class="fa-solid fa-shield-halved"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:700; color:#7A1F3D; font-size:13px; line-height:1.35;">
                                                    <?php echo esc_html($active_bucket_rule['upsell_name']); ?>
                                                </div>
                                                <div style="font-size:11.5px; color:#9A3412; font-weight:600; margin-top:2px;">
                                                    Add-on: ₹<?php echo number_format($active_bucket_rule['upsell_price'], 2); ?> &bull; <span style="color:#059669; font-weight:700;">Unlocks <?php echo esc_html($rule_disc_lbl); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="mr-btn-add-fixed-upsell" style="background:linear-gradient(135deg, #EA580C 0%, #C2410C 100%); color:#ffffff; border:none; border-radius:8px; padding:9px 16px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; box-shadow:0 3px 8px rgba(234,88,12,0.25);">
                                            <i class="fa-solid fa-plus"></i> Add &amp; Save
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Coupon / Promo Code Input Box -->
                        <div class="mr-coupon-section" style="margin-bottom:18px; background:#FFFFFF; border:1.5px dashed #D9A441; border-radius:12px; padding:12px 14px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <label for="mr_coupon_code" style="font-size:12px; font-weight:700; color:#7A1F3D; margin:0; display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-tags" style="color:#F58220;"></i> Have a Promo / Coupon Code?
                                </label>
                                <button type="button" id="mr-view-coupons-hint" style="background:transparent; border:none; padding:0; font-size:11px; color:#059669; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; text-decoration:underline;">
                                    <i class="fa-solid fa-gift"></i> Offers Available &rarr;
                                </button>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <input type="text" id="mr_coupon_code" placeholder="e.g. JYOTISH25 / FLAT50" style="flex:1; text-transform:uppercase; padding:9px 12px; border:1.5px solid #E2D7CC; border-radius:8px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:700; letter-spacing:0.5px;" autocomplete="off" value="<?php echo esc_attr(!empty($_GET['coupon']) ? sanitize_text_field($_GET['coupon']) : ''); ?>">
                                <button type="button" id="mr-btn-apply-coupon" style="background:#7A1F3D; color:#ffffff; border:none; border-radius:8px; padding:0 16px; font-weight:700; font-size:12.5px; cursor:pointer; transition:all 0.2s;">
                                    Apply
                                </button>
                            </div>
                            <div id="mr-coupon-status" style="display:none; font-size:12px; margin-top:8px; padding:6px 10px; border-radius:6px;"></div>
                            <!-- Applied Coupon Pill -->
                            <div id="mr-applied-coupon-pill" style="display:none; align-items:center; justify-content:space-between; background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:6px 12px; border-radius:8px; margin-top:8px; font-size:12px; font-weight:600;">
                                <span id="mr-applied-coupon-label"><i class="fa-solid fa-circle-check"></i> Code applied</span>
                                <button type="button" id="mr-btn-remove-coupon" style="background:transparent; border:none; color:#DC2626; cursor:pointer; font-weight:700; font-size:11.5px; text-decoration:underline;">✕ Remove</button>
                            </div>
                        </div>

                        <!-- Calculations Table -->
                        <?php 
                        $fmt_co_reg      = class_exists('MR_Bundles') ? MR_Bundles::format_price($price_regular) : number_format($price_regular);
                        $fmt_co_discount = class_exists('MR_Bundles') ? MR_Bundles::format_price($price_regular - $price_sale) : number_format($price_regular - $price_sale);
                        $fmt_co_sale     = class_exists('MR_Bundles') ? MR_Bundles::format_price($price_sale) : number_format($price_sale);
                        ?>
                        <table class="mr-calc-table">
                            <tr>
                                <td>Listed Price:</td>
                                <td><s>₹<span id="mr-calc-reg-price"><?php echo esc_html($fmt_co_reg); ?></span></s></td>
                            </tr>
                            <tr id="mr-row-special-discount" <?php if ($price_regular <= $price_sale) echo 'style="display:none;"'; ?>>
                                <td style="color:#059669;"><?php echo $is_chat_recharge ? 'Special Offer Discount:' : 'Special Bundle Discount:'; ?></td>
                                <td style="color:#059669;">- ₹<span id="mr-disp-bundle-discount"><?php echo esc_html($fmt_co_discount); ?></span></td>
                            </tr>
                            <!-- Gross Amount row: Listed Price - Special Bundle Discount = Gross Amount -->
                            <tr id="mr-row-gross-amount" style="border-top:1.5px dashed #E2D7CC; border-bottom:1.5px dashed #E2D7CC; background:#FFFBF5; font-weight:700;">
                                <td style="color:#7A1F3D; font-size:13.5px;">Gross Amount:</td>
                                <td style="color:#7A1F3D; font-size:13.5px;">₹<span id="mr-disp-gross-amount"><?php echo esc_html($fmt_co_sale); ?></span></td>
                            </tr>
                            <!-- Special Offer Promo (In-Cart Upgrade): active only when cart upgraded AND no external coupon applied -->
                            <tr id="mr-row-bucket-discount" style="display:none; color:#EA580C; font-weight:600;">
                                <td id="mr-disp-bucket-label">Special Offer Promo (25% OFF):</td>
                                <td>- ₹<span id="mr-disp-bucket-val">0</span></td>
                            </tr>
                            <!-- Selected Promo Code Discount: active when coupon code is applied (replaces special upgrade offer) -->
                            <tr id="mr-row-coupon-discount" style="display:none; color:#059669; font-weight:600;">
                                <td>Promo Code Discount (<span id="mr-disp-coupon-code"></span>):</td>
                                <td>- ₹<span id="mr-disp-coupon-val">0</span></td>
                            </tr>
                            <tr>
                                <td>Taxes &amp; GST:</td>
                                <td>₹0.00</td>
                            </tr>
                            <tr class="mr-calc-total-row">
                                <td>Total Payable:</td>
                                <td>₹<span id="mr-disp-total-pay"><?php echo esc_html($fmt_co_sale); ?></span></td>
                            </tr>
                        </table>

                        <div class="mr-co-trust" style="margin-top:18px;">
                            <span><i class="fa-solid fa-file-invoice"></i> Retail Invoice Attached</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-envelope"></i> Instant Email Delivery</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-lock"></i> Razorpay Verified</span>
                        </div>

                        <!-- Return to details link -->
                        <div style="text-align:center; margin-top:16px;">
                            <?php if ($is_chat_recharge): ?>
                                <a href="<?php echo esc_url(home_url('/#open-niyati')); ?>" 
                                   style="font-size:12px; color:#6B5E57; text-decoration:none;">
                                    &larr; Return to Niyati AI Chat
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url(home_url('/reports/' . (class_exists('MR_Bundles') ? MR_Bundles::get_report_page_url($report_slug) : ''))); ?>" 
                                   style="font-size:12px; color:#6B5E57; text-decoration:none;">
                                    &larr; Return to Report Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT: CUSTOMER & BILLING DETAILS -->
                    <div class="mr-co-card">
                        <h2 class="mr-co-title">
                            <i class="fa-solid fa-address-card" style="color:#F58220;"></i>
                            <span>Customer &amp; Billing Details</span>
                        </h2>

                        <div id="mr-co-error-box"></div>

                        <form id="mr-checkout-form" novalidate>
                            <input type="hidden" name="report_slug" value="<?php echo esc_attr($report_slug); ?>">
                            <input type="hidden" name="bundle_tier" value="<?php echo esc_attr($tier); ?>">
                            <input type="hidden" name="package_title" value="<?php echo esc_attr($clean_pkg_title); ?>">
                            <input type="hidden" name="price" value="<?php echo esc_attr($price_sale); ?>">
                            <input type="hidden" name="chat_credits" value="<?php echo esc_attr($chat_credits); ?>">
                            <input type="hidden" name="included_reports" value="<?php echo esc_attr(wp_json_encode($included_reports)); ?>">
                            <input type="hidden" name="item_type" id="mr_item_type" value="<?php echo $is_chat_recharge ? 'chat_credits' : 'report_bundle'; ?>">
                            <input type="hidden" name="pack_key" id="mr_pack_key" value="<?php echo esc_attr($pack_key ?? ''); ?>">
                            <input type="hidden" name="applied_coupon" id="mr_applied_coupon" value="">
                            <input type="hidden" name="coupon_id" id="mr_coupon_id" value="">
                            <input type="hidden" name="coupon_discount" id="mr_coupon_discount" value="0">
                            <input type="hidden" name="companion_slug" id="mr_companion_slug" value="">
                            <input type="hidden" name="bucket_discount" id="mr_bucket_discount" value="0">
                            <input type="hidden" name="bucket_rule_id" id="mr_bucket_rule_id" value="">
                            <input type="hidden" name="upsell_item_key" id="mr_upsell_item_key" value="">
                            <input type="hidden" name="upsell_item_name" id="mr_upsell_item_name" value="">
                            <input type="hidden" name="upsell_item_price" id="mr_upsell_item_price" value="0">
                            <input type="hidden" name="visitor_id" id="mr_visitor_id" value="<?php echo esc_attr(!empty($_GET['visitor_id']) ? sanitize_text_field($_GET['visitor_id']) : (!empty($_COOKIE['niyati_visitor_id']) ? sanitize_text_field($_COOKIE['niyati_visitor_id']) : '')); ?>">
                            <?php wp_nonce_field('mr_reports_nonce', 'checkout_nonce'); ?>

                            <div class="mr-form-group">
                                <label for="mr_co_name">Full Name <span class="req">*</span></label>
                                <input type="text" id="mr_co_name" name="billing_name" class="mr-form-input" required 
                                       placeholder="e.g. Rahul Sharma" value="<?php echo esc_attr($prefill_name); ?>">
                            </div>

                            <div class="mr-form-row-2">
                                <div class="mr-form-group">
                                    <label for="mr_co_email">Email Address (For Invoice &amp; Report) <span class="req">*</span></label>
                                    <input type="email" id="mr_co_email" name="billing_email" class="mr-form-input" required 
                                           placeholder="rahul@example.com" value="<?php echo esc_attr($prefill_email); ?>">
                                </div>
                                <div class="mr-form-group">
                                    <label for="mr_co_phone">Mobile Number <span class="req">*</span></label>
                                    <input type="tel" id="mr_co_phone" name="billing_phone" class="mr-form-input" required 
                                           inputmode="numeric" pattern="[5-9][0-9]{9}" maxlength="10"
                                           placeholder="e.g. 9876543210" value="<?php echo esc_attr($prefill_phone); ?>" autocomplete="tel">
                                    <div id="mr-phone-error-hint" style="display:none; color:#DC2626; font-size:11.5px; font-weight:600; margin-top:4px;"></div>
                                </div>
                            </div>

                            <!-- COUNTRY SELECTOR -->
                            <div class="mr-form-group">
                                <label for="mr_co_country">Billing Country <span class="req">*</span></label>
                                <select id="mr_co_country" name="billing_country" class="mr-form-select">
                                    <option value="India" <?php selected($prefill_country, 'India'); ?>>🇮🇳 India (भारत)</option>
                                    <option value="United States" <?php selected($prefill_country, 'United States'); ?>>🇺🇸 United States</option>
                                    <option value="Canada" <?php selected($prefill_country, 'Canada'); ?>>🇨🇦 Canada</option>
                                    <option value="United Kingdom" <?php selected($prefill_country, 'United Kingdom'); ?>>🇬🇧 United Kingdom</option>
                                    <option value="Australia" <?php selected($prefill_country, 'Australia'); ?>>🇦🇺 Australia</option>
                                    <option value="United Arab Emirates" <?php selected($prefill_country, 'United Arab Emirates'); ?>>🇦🇪 United Arab Emirates</option>
                                    <option value="Singapore" <?php selected($prefill_country, 'Singapore'); ?>>🇸🇬 Singapore</option>
                                    <option value="Germany" <?php selected($prefill_country, 'Germany'); ?>>🇩🇪 Germany</option>
                                    <option value="Other International" <?php selected(!in_array($prefill_country, ['India', 'United States', 'Canada', 'United Kingdom', 'Australia', 'United Arab Emirates', 'Singapore', 'Germany'], true), true); ?>>🌍 Other International Country</option>
                                </select>
                            </div>

                            <!-- INDIA SPECIFIC: STATE SELECTOR (REQUIRED) -->
                            <div id="mr_india_state_wrap" class="mr-form-group">
                                <label for="mr_co_state_india">Billing State (Mandatory for Retail Bill) <span class="req">*</span></label>
                                <select id="mr_co_state_india" name="billing_state_india" class="mr-form-select">
                                    <?php foreach ($all_states as $s): ?>
                                        <option value="<?php echo esc_attr($s); ?>" <?php selected($prefill_state, $s); ?>>
                                             <?php echo esc_html($s); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="font-size:11px; color:#8C7E77; margin-top:4px;">
                                    <i class="fa-solid fa-circle-check" style="color:#2CB67D;"></i> As per Indian regulations, full street address is not required for digital astrological reports.
                                </div>
                            </div>

                            <!-- INTERNATIONAL FULL ADDRESS (AUTO REVEALED IF OUTSIDE INDIA) -->
                            <div id="mr_intl_address_wrap" style="display:none; background:#FFFDF9; border:1px solid #EADCCB; border-radius:12px; padding:16px 18px; margin-top:14px;">
                                <div class="mr-intl-notice">
                                    <i class="fa-solid fa-earth-americas"></i> <strong>International Order Compliance:</strong> Complete postal address is required for cross-border digital service invoices.
                                </div>

                                <div class="mr-form-group">
                                    <label for="mr_co_intl_address">Street Address <span class="req">*</span></label>
                                    <input type="text" id="mr_co_intl_address" name="billing_address_intl" class="mr-form-input" 
                                           placeholder="Apartment, Suite, Street Address" value="<?php echo esc_attr($prefill_address); ?>">
                                </div>

                                <div class="mr-form-row-2">
                                    <div class="mr-form-group">
                                        <label for="mr_co_intl_city">City <span class="req">*</span></label>
                                        <input type="text" id="mr_co_intl_city" name="billing_city_intl" class="mr-form-input" 
                                               placeholder="e.g. Toronto / New York" value="<?php echo esc_attr($prefill_city); ?>">
                                    </div>
                                    <div class="mr-form-group">
                                        <label for="mr_co_intl_state">State / Province <span class="req">*</span></label>
                                        <input type="text" id="mr_co_intl_state" name="billing_state_intl" class="mr-form-input" 
                                               placeholder="e.g. Ontario / California" value="<?php echo esc_attr($prefill_state !== 'Uttar Pradesh' ? $prefill_state : ''); ?>">
                                    </div>
                                </div>

                                <div class="mr-form-group" style="margin-bottom:0;">
                                    <label for="mr_co_intl_postcode">Postal / ZIP Code <span class="req">*</span></label>
                                    <input type="text" id="mr_co_intl_postcode" name="billing_pincode_intl" class="mr-form-input" 
                                           placeholder="e.g. M5V 2T6" value="<?php echo esc_attr($prefill_pincode); ?>">
                                </div>
                            </div>

                            <!-- Single Primary Pay CTA Button -->
                            <div style="margin-top:20px; padding-top:16px; border-top:1.5px solid #F6EDE3;">
                                <!-- Mandatory DPDP & Statutory Policy Consent Checkbox -->
                                <div class="mr-checkout-consent-box" style="margin-bottom:14px; padding:10px 14px; background:#FFFBF5; border:1px solid #F6EDE3; border-radius:8px;">
                                    <label for="mr_checkout_dpdp_consent" style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:12px; color:#475569; line-height:1.5; margin:0; user-select:none;">
                                        <input type="checkbox" id="mr_checkout_dpdp_consent" name="dpdp_consent" value="1" required style="margin-top:2px; accent-color:#F58220; width:15px; height:15px; flex-shrink:0; cursor:pointer;" />
                                        <span>
                                            I agree to the <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>" target="_blank" rel="noopener" style="color:#7A1F3D; font-weight:700; text-decoration:underline;">Terms &amp; Conditions</a>, <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" target="_blank" rel="noopener" style="color:#7A1F3D; font-weight:700; text-decoration:underline;">Privacy Policy</a>, and <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>" target="_blank" rel="noopener" style="color:#7A1F3D; font-weight:700; text-decoration:underline;">Refund Policy</a>, and confirm that I am 18 years of age or older.
                                        </span>
                                    </label>
                                    <div id="mr-checkout-consent-error" style="display:none; color:#DC2626; font-size:11px; font-weight:600; margin-top:5px; padding-left:23px;">
                                        ⚠ Please accept the Terms &amp; Policies and confirm you are 18+ to proceed.
                                    </div>
                                </div>

                                <button type="button" id="mr-btn-checkout-submit" class="mr-btn-pay" style="width:100%;">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span id="mr-btn-pay-text">Pay ₹<span id="mr-btn-pay-amt"><?php echo esc_html($fmt_co_sale); ?></span> Securely &rarr;</span>
                                </button>
                                <div style="text-align:center; font-size:11.5px; color:#8C7E77; margin-top:8px;">
                                    <i class="fa-solid fa-lock" style="color:#2CB67D;"></i> 256-Bit SSL Encrypted &bull; Instant Delivery
                                </div>
                            </div>

                        </form>
                    </div>

                </div>

                <!-- OFFERS & PROMO CODES MODAL -->
                <div id="mr-offers-modal" class="mr-offers-modal-backdrop">
                    <div class="mr-offers-modal-card">
                        <div style="padding:18px 22px 14px 22px; border-bottom:1.5px solid #F6EDE3; display:flex; justify-content:space-between; align-items:center; background:#FFFBF5;">
                            <div>
                                <div style="font-size:11px; font-weight:800; color:#D97706; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-sparkles"></i> Exclusive Deals
                                </div>
                                <h3 style="margin:3px 0 0 0; font-size:18px; font-weight:800; color:#7A1F3D; font-family:'Playfair Display',serif;">
                                    Available Offers &amp; Promo Codes
                                </h3>
                            </div>
                            <button type="button" id="mr-offers-modal-close" style="background:transparent; border:none; font-size:24px; line-height:1; color:#94A3B8; cursor:pointer; padding:4px 8px; border-radius:8px;">&times;</button>
                        </div>

                        <!-- Policy Notice: Only 1 promo active at a time -->
                        <div style="padding:10px 22px; background:#FEF3C7; border-bottom:1px solid #FDE68A; font-size:11.5px; color:#92400E; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-circle-info" style="color:#D97706; font-size:14px; flex-shrink:0;"></i>
                            <span><strong>Offer Policy:</strong> Only 1 promo offer can be active at one time. Applying a new code replaces any active promo.</span>
                        </div>

                        <div id="mr-offers-modal-list" style="padding:18px 22px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:12px;">
                            <?php if (!empty($active_bucket_rule)): 
                                $special_disc_txt = ($active_bucket_rule['discount_type'] === 'flat') 
                                    ? ('Flat ₹' . number_format($active_bucket_rule['discount_value'], 0) . ' OFF') 
                                    : ($active_bucket_rule['discount_value'] . '% OFF');
                            ?>
                                <!-- Special In-Cart Upgrade Banner in Modal -->
                                <div class="mr-offer-item-card" style="border:1.5px solid #EA580C; background:#FFF7ED;">
                                    <div>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="background:#EA580C; color:#fff; font-size:10px; font-weight:800; padding:2px 8px; border-radius:9999px; text-transform:uppercase;">
                                                <i class="fa-solid fa-gift"></i> Special Offer
                                            </span>
                                            <span style="font-weight:700; color:#7A1F3D; font-size:13px;"><?php echo esc_html($special_disc_txt); ?> on In-Cart Upgrade</span>
                                        </div>
                                        <div style="font-size:12px; color:#C2410C; font-weight:600; margin-top:3px;"><?php echo esc_html($active_bucket_rule['headline']); ?></div>
                                        <p style="margin:4px 0 0 0; font-size:11px; color:#6B5E57; line-height:1.4;">
                                            <?php echo esc_html($active_bucket_rule['subtext'] ?: 'Upgrade cart with a companion report to unlock special savings on your entire order.'); ?>
                                        </p>
                                    </div>
                                    <div id="mr-modal-special-tag" style="text-align:right; flex-shrink:0;">
                                        <span style="font-size:11px; font-weight:700; color:#EA580C; background:#FFEDD5; padding:4px 9px; border-radius:6px; display:inline-block;">In-Cart Offer</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($modal_coupons)): ?>
                                <?php foreach ($modal_coupons as $c_code => $c_data): 
                                    $val_str = ($c_data['type'] === 'flat' || $c_data['type'] === 'fixed') 
                                        ? ('₹' . number_format($c_data['value'], 0) . ' OFF') 
                                        : ($c_data['value'] . '% OFF');
                                ?>
                                    <div class="mr-offer-item-card">
                                        <div style="flex:1;">
                                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                                <span class="mr-offer-code-badge"><i class="fa-solid fa-scissors"></i> <?php echo esc_html($c_code); ?></span>
                                                <span style="font-size:12px; font-weight:800; color:#059669;"><?php echo esc_html($val_str); ?></span>
                                            </div>
                                            <div style="font-size:13px; font-weight:700; color:#1E293B;"><?php echo esc_html($c_data['title']); ?></div>
                                            <?php if (!empty($c_data['description']) && $c_data['description'] !== $c_data['title']): ?>
                                                <div style="font-size:11.5px; color:#64748B; margin-top:2px;"><?php echo esc_html($c_data['description']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($c_data['min_order'] > 0): ?>
                                                <div style="font-size:11px; color:#94A3B8; margin-top:2px;">
                                                    <i class="fa-solid fa-cart-shopping"></i> Valid on orders ₹<?php echo number_format($c_data['min_order']); ?> or more
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="flex-shrink:0;">
                                            <button type="button" class="mr-btn-apply-offer" data-code="<?php echo esc_attr($c_code); ?>">
                                                Apply Code &rarr;
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align:center; padding:20px; color:#64748B; font-size:13px;">
                                    No active promo codes currently available. Check back soon!
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="padding:14px 22px; border-top:1.5px solid #F6EDE3; background:#FFFDF9; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11.5px; color:#6B5E57;"><i class="fa-solid fa-shield-halved" style="color:#059669;"></i> Guaranteed Best Value</span>
                            <button type="button" id="mr-offers-modal-dismiss" style="background:#7A1F3D; color:#fff; border:none; border-radius:8px; padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer;">Close</button>
                        </div>
                    </div>
                </div>

                <!-- UNLOCKING / FINALIZING FULLSCREEN FREEZE OVERLAY -->
                <div id="mr-checkout-unlocking-overlay" class="mr-unlocking-overlay" style="display:none;">
                    <div class="mr-unlocking-modal-card">
                        <div class="mr-unlocking-icon-wrap">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span class="mr-unlocking-pulse-ring"></span>
                        </div>
                        <h3 class="mr-unlocking-title">
                            Kindly wait, we are unlocking your purchase...
                        </h3>
                        <p class="mr-unlocking-subtitle">
                            Thank You for shopping with us.
                        </p>
                        <div class="mr-unlocking-status-box">
                            <i class="fa-solid fa-circle-notch fa-spin mr-unlocking-spinner"></i>
                            <span id="mr-unlocking-status-text" style="font-size:13px; font-weight:700; color:#7A1F3D;">
                                Assigning retail invoice &amp; activating report credits...
                            </span>
                        </div>
                        <div class="mr-unlocking-footer-note">
                            <i class="fa-solid fa-lock" style="color:#059669;"></i> Please do not refresh, close, or navigate away from this screen.
                        </div>
                    </div>
                </div>

                <!-- POST-PURCHASE SUCCESS VIEW (REVEALED ON PAYMENT) -->
                <div id="mr-checkout-success-view" class="mr-co-card" style="max-width:760px; margin:0 auto;">
                    <div class="mr-success-icon-wrap">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <h2 style="font-family:'Playfair Display', serif; font-size:28px; font-weight:800; color:#7A1F3D; margin:0 0 8px 0;">
                        Payment Successful!
                    </h2>
                    <p style="color:#554841; font-size:14.5px; margin:0 0 22px 0;">
                        Your retail invoice and order confirmation have been generated and dispatched to your email.
                    </p>

                    <!-- Transaction details summary box -->
                    <div style="background:#FFFDF9; border:1px solid #D9A441; border-radius:14px; padding:20px 24px; text-align:left; margin-bottom:28px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:13px;">
                            <div>
                                <span style="color:#64748b; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">Invoice Number:</span><br>
                                <strong id="mr-succ-inv-num" style="color:#7A1F3D; font-size:15px; font-family:monospace;">MT/26-27/...</strong>
                            </div>
                            <div>
                                <span style="color:#64748b; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">Razorpay Payment ID:</span><br>
                                <strong id="mr-succ-rzp-id" style="color:#1E293B; font-family:monospace;">pay_...</strong>
                            </div>
                            <div>
                                <span style="color:#64748b; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">Internal Order ID:</span><br>
                                <strong id="mr-succ-internal-id" style="color:#1E293B; font-family:monospace;">MR_ORD_...</strong>
                            </div>
                            <div>
                                <span style="color:#64748b; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">Total Paid:</span><br>
                                <strong id="mr-succ-amount" style="color:#059669; font-size:15px;">₹...</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Recharge Fulfillment Success Banner -->
                    <div id="mr-succ-chat-feature-wrap" style="display:none; margin:24px auto; max-width:680px; text-align:left;">
                        <div style="background: linear-gradient(135deg, #FFF7ED 0%, #FEF3C7 100%); border: 1.5px solid #F58220; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 15px rgba(245, 130, 32, 0.1);">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:48px; height:48px; border-radius:50%; background:#F58220; color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; box-shadow:0 4px 10px rgba(245, 130, 32, 0.25);">
                                    <i class="fa-solid fa-comments"></i>
                                </div>
                                <div>
                                    <div style="font-size:16px; font-weight:800; color:#7A1F3D;">
                                        <span id="mr-succ-chat-credits-count">0</span> Consultation Questions Credited to Your Wallet!
                                    </div>
                                    <p style="margin:4px 0 0 0; font-size:12.5px; color:#6B5E57; line-height:1.45;">
                                        Your questions have lifetime validity. You can start asking Niyati right now. Your official tax invoice has also been dispatched to your email.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bundle Fulfillment Queue (When order includes 2 or 3 reports) -->
                    <div id="mr-succ-bundle-queue-wrap" style="display:none; margin:24px auto; max-width:680px; text-align:left;">
                        <div style="background:#FFF9F0; border:1.5px solid #D9A441; border-radius:14px; padding:14px 18px; margin-bottom:16px;">
                            <div style="font-weight:700; color:#7A1F3D; font-size:14px; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-layer-group" style="color:#F58220;"></i>
                                <span>Your Package Reports (<span id="mr-succ-bundle-count">2 Reports</span>):</span>
                            </div>
                            <p style="margin:4px 0 0 0; font-size:12px; color:#6B5E57;">
                                Click below to generate each report in your package. You can use the same birth details or enter different details for another person!
                            </p>
                        </div>
                        <div id="mr-succ-bundle-items-list" style="display:flex; flex-direction:column; gap:10px;">
                            <!-- Dynamically generated items -->
                        </div>
                    </div>

                    <!-- Dual / Triple Action Buttons -->
                    <div style="display:flex; justify-content:center; gap:14px; flex-wrap:wrap;">
                        <a id="mr-succ-download-inv-btn" href="#" target="_blank" class="mr-btn-pay" style="width:auto; padding:13px 26px; background:#7A1F3D; text-decoration:none;">
                            <i class="fa-solid fa-file-invoice"></i> Download Invoice (PDF / Print)
                        </a>

                        <button type="button" id="mr-succ-generate-btn" class="mr-btn-pay" style="width:auto; padding:13px 28px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Fill Birth Details &amp; Generate Report &rarr;
                        </button>

                        <a id="mr-succ-chat-btn" href="<?php echo esc_url(home_url('/?open_chat=1#open-niyati')); ?>" class="mr-btn-pay" style="display:none; width:auto; padding:13px 28px; background:linear-gradient(135deg, #F58220 0%, #E6680D 100%); text-decoration:none;">
                            <i class="fa-solid fa-comments"></i> Start Asking Niyati Now &rarr;
                        </a>
                    </div>

                    <p style="font-size:12px; color:#8C7E77; margin-top:20px;">
                        A copy of this invoice is also preserved in your <a href="<?php echo esc_url(home_url('/my-account/#passbook')); ?>" style="color:#F58220; font-weight:600;">My Account Passbook</a>.
                    </p>
                </div>

            </div>
        </div>

        <!-- RENDER MODALS (Details Modal for instant generation) -->
        <?php if (class_exists('MR_Frontend')) {
            echo MR_Frontend::render_details_capture_and_banner_modal('', $report_slug);
            echo MR_Frontend::render_simulated_pg_modal();
        } ?>

        <!-- CHECKOUT JAVASCRIPT ENGINE -->
        <script>
        (function($) {
            $(document).ready(function() {
                var countrySelect = $('#mr_co_country');
                var indiaWrap     = $('#mr_india_state_wrap');
                var intlWrap      = $('#mr_intl_address_wrap');

                function toggleAddressFields() {
                    var c = countrySelect.val();
                    if (c === 'India') {
                        indiaWrap.slideDown(150);
                        intlWrap.slideUp(150);
                    } else {
                        indiaWrap.slideUp(150);
                        intlWrap.slideDown(150);
                    }
                }
                countrySelect.on('change', toggleAddressFields);
                toggleAddressFields();

                $('#mr-offers-modal').appendTo('body');
                $('#mr-checkout-unlocking-overlay').appendTo('body');
                $('#mr-details-modal').appendTo('body');
                $('.mr-modal').appendTo('body');

                var baseReportPrice   = <?php echo floatval($price_sale); ?>;
                var baseReportReg     = <?php echo floatval($price_regular); ?>;
                var primarySlug       = '<?php echo esc_js($report_slug); ?>';
                var originalTitle     = '<?php echo esc_js(html_entity_decode($package_title, ENT_QUOTES, 'UTF-8')); ?>';
                var baseBundleReports = <?php echo json_encode(array_values($included_reports)); ?>;
                var activeBucketRule  = <?php echo json_encode($active_bucket_rule); ?>;
                var companionSlug    = '';
                var companionName    = '';
                var companionPrice   = 0;
                var companionReg     = 0;
                var upsellSlug       = '';
                var upsellName       = '';
                var upsellPrice      = 0;
                var bucketDiscount   = 0;
                var couponDiscount   = 0;
                var appliedCoupon    = { code: '', id: 0, type: '', value: 0, cap: 0 };
                var hasCartUpgrade   = false;

                function formatInr(val) {
                    return (Math.round(val * 100) / 100).toLocaleString('en-IN', { maximumFractionDigits: 2 });
                }

                // Master recalculation engine
                function recalcCheckoutTotals() {
                    var activeAddonSlug  = upsellSlug || companionSlug;
                    var activeAddonPrice = upsellPrice || companionPrice;
                    var activeAddonName  = upsellName || companionName;
                    var activeAddonReg   = companionSlug ? companionReg : (upsellSlug ? (upsellPrice * 2) : 0);

                    // 1. Total Listed Price
                    var totalListedPrice = baseReportReg + activeAddonReg;

                    // 2. Base Gross Amount = sale price of primary + addon
                    var grossAmount = baseReportPrice + (activeAddonSlug ? activeAddonPrice : 0);

                    // Special Bundle Discount = Listed Price - Gross Amount
                    var bundleDiscount = Math.max(0, totalListedPrice - grossAmount);

                    // 3. Active Promo evaluation: Strictly ONLY ONE PROMO ACTIVE AT A TIME
                    var activePromoDiscount = 0;

                    if (appliedCoupon.code) {
                        // A promo code is selected! Special offer upgrade discount is suppressed.
                        bucketDiscount = 0;
                        $('#mr-row-bucket-discount').slideUp(120);

                        // Calculate coupon discount on Gross Amount
                        if (appliedCoupon.type === 'percent') {
                            couponDiscount = Math.round(((grossAmount * appliedCoupon.value) / 100.0) * 100) / 100;
                            if (appliedCoupon.cap && appliedCoupon.cap > 0) {
                                couponDiscount = Math.min(couponDiscount, appliedCoupon.cap);
                            }
                        } else {
                            couponDiscount = Math.min(grossAmount, appliedCoupon.value);
                        }
                        couponDiscount = Math.min(couponDiscount, grossAmount);

                        activePromoDiscount = couponDiscount;
                        $('#mr-disp-coupon-code').text(appliedCoupon.code);
                        $('#mr-disp-coupon-val').text(formatInr(couponDiscount));
                        $('#mr-row-coupon-discount').slideDown(150);

                        $('#mr-applied-coupon-label').html('<i class="fa-solid fa-circle-check" style="color:#059669;"></i> Coupon <strong>' + appliedCoupon.code + '</strong> applied (-₹' + formatInr(couponDiscount) + ')');
                        $('#mr-applied-coupon-pill').slideDown(150);
                    } else {
                        couponDiscount = 0;
                        $('#mr-row-coupon-discount').slideUp(120);
                        $('#mr-applied-coupon-pill').slideUp(120);

                        // If user has upgraded cart and no external coupon is applied, apply Special Offer Promo
                        if (activeAddonSlug && grossAmount > 0) {
                            if (activeBucketRule) {
                                if (activeBucketRule.discount_type === 'flat') {
                                    bucketDiscount = Math.min(grossAmount, parseFloat(activeBucketRule.discount_value) || 0);
                                    $('#mr-disp-bucket-label').text('Special Offer Promo (-₹' + formatInr(bucketDiscount) + '):');
                                } else {
                                    var pct = parseFloat(activeBucketRule.discount_value) || 25;
                                    bucketDiscount = Math.round(((grossAmount * pct) / 100.0) * 100) / 100;
                                    $('#mr-disp-bucket-label').text('Special Offer Promo (' + pct + '% OFF):');
                                }
                            } else {
                                bucketDiscount = Math.round((grossAmount * 0.25) * 100) / 100;
                                $('#mr-disp-bucket-label').text('Special Offer Promo (25% OFF):');
                            }
                            activePromoDiscount = bucketDiscount;
                            $('#mr-disp-bucket-val').text(formatInr(bucketDiscount));
                            $('#mr-row-bucket-discount').slideDown(150);
                        } else {
                            bucketDiscount = 0;
                            $('#mr-row-bucket-discount').slideUp(120);
                        }
                    }

                    // 4. Net Total Payable (Min ₹1 so gateway order can generate)
                    var netPayable = Math.max(1, Math.round((grossAmount - activePromoDiscount) * 100) / 100);

                    // Update UI elements
                    $('#mr-calc-reg-price').text(formatInr(totalListedPrice));
                    $('#mr-disp-bundle-discount').text(formatInr(bundleDiscount));
                    $('#mr-disp-gross-amount').text(formatInr(grossAmount));
                    $('#mr-disp-total-pay').text(formatInr(netPayable));
                    $('#mr-btn-pay-amt').text(formatInr(netPayable));
                    $('.mr-btn-pay-amt-dup').text(formatInr(netPayable));

                    // Update modal offer buttons
                    $('.mr-btn-apply-offer').each(function() {
                        var c = $(this).data('code');
                        if (appliedCoupon.code && appliedCoupon.code === c) {
                            $(this).removeClass('mr-btn-apply-offer').addClass('mr-btn-offer-applied').prop('disabled', true).html('<i class="fa-solid fa-circle-check"></i> Applied');
                        }
                    });
                    $('.mr-btn-offer-applied').each(function() {
                        var c = $(this).data('code');
                        if (!appliedCoupon.code || appliedCoupon.code !== c) {
                            $(this).removeClass('mr-btn-offer-applied').addClass('mr-btn-apply-offer').prop('disabled', false).html('Apply Code &rarr;');
                        }
                    });

                    // Synchronize Form Hidden Fields
                    $('#mr-checkout-form input[name="price"]').val(grossAmount);
                    $('#mr_bucket_discount').val(bucketDiscount);
                    $('#mr_bucket_rule_id').val(activeBucketRule ? activeBucketRule.id : '');
                    $('#mr_upsell_item_key').val(activeAddonSlug);
                    $('#mr_upsell_item_name').val(activeAddonName);
                    $('#mr_upsell_item_price').val(activeAddonPrice);
                    $('#mr_companion_slug').val(companionSlug);
                    $('#mr_coupon_discount').val(couponDiscount);
                    $('#mr_applied_coupon').val(appliedCoupon.code || '');
                    $('#mr_coupon_id').val(appliedCoupon.id || '');

                    var incReports = (baseBundleReports && baseBundleReports.length) ? baseBundleReports.slice() : [primarySlug];
                    if (companionSlug && incReports.indexOf(companionSlug) === -1) {
                        incReports.push(companionSlug);
                    }
                    $('#mr-checkout-form input[name="included_reports"]').val(JSON.stringify(incReports));
                }

                // Handler: Add Catalog Companion Report
                $('#mr-btn-add-companion').on('click', function(e) {
                    e.preventDefault();
                    var select = $('#mr_companion_select');
                    var selSlug = select.val();
                    if (!selSlug) {
                        select.css('border-color', '#DC2626').focus();
                        return;
                    }
                    select.css('border-color', '#FDBA74');

                    var opt = select.find('option:selected');
                    companionSlug  = selSlug;
                    companionName  = opt.data('name') || selSlug;
                    companionPrice = parseFloat(opt.data('price')) || 99;
                    companionReg   = parseFloat(opt.data('reg')) || 999;
                    hasCartUpgrade = true;

                    // If a promo code was previously applied, clear it so special offer promo applies
                    if (appliedCoupon.code) {
                        appliedCoupon = { code: '', id: 0, type: '', value: 0, cap: 0 };
                        $('#mr_coupon_code').val('');
                        $('#mr-coupon-status').slideUp(100);
                    }

                    $('#mr-summary-companion-item').remove();
                    var tag = activeBucketRule ? ((activeBucketRule.discount_type === 'flat') ? ('-₹' + formatInr(activeBucketRule.discount_value)) : (activeBucketRule.discount_value + '% OFF')) : '25% OFF';
                    var compItem = $(
                        '<li id="mr-summary-companion-item" style="background:#FFF7ED; border:1.5px dashed #F97316; border-radius:10px; padding:8px 12px; margin-top:8px; display:flex; justify-content:space-between; align-items:center;">' +
                            '<span style="color:#7A1F3D; font-weight:700; font-size:12.5px;">' +
                                '<i class="fa-solid fa-gift" style="color:#EA580C; margin-right:5px;"></i>' + companionName + ' ' +
                                '<span style="font-size:10px; background:#EA580C; color:#ffffff; padding:2px 7px; border-radius:9999px; text-transform:uppercase; margin-left:4px;">' + tag + '</span>' +
                            '</span>' +
                            '<button type="button" id="mr-btn-remove-companion" style="background:transparent; border:none; color:#DC2626; font-size:11px; font-weight:700; cursor:pointer; text-decoration:underline;">✕ Remove</button>' +
                        '</li>'
                    );
                    $('#mr-summary-items-ul').append(compItem);

                    var newTitle = originalTitle + ' + ' + companionName;
                    $('#mr-summary-main-title').text(newTitle);
                    $('#mr-checkout-form input[name="package_title"]').val(newTitle);

                    $('#mr-bucket-nudge-box').slideUp(150);
                    recalcCheckoutTotals();

                    if (window.mrNotify && typeof window.mrNotify.toast === 'function') {
                        window.mrNotify.toast(companionName + ' added! Special offer promo applied.', 'success');
                    }
                });

                // Handler: Add Fixed / Bundle Upsell Item
                $('#mr-btn-add-fixed-upsell').on('click', function(e) {
                    e.preventDefault();
                    if (!activeBucketRule) return;

                    upsellSlug  = activeBucketRule.upsell_slug;
                    upsellName  = activeBucketRule.upsell_name;
                    upsellPrice = parseFloat(activeBucketRule.upsell_price) || 0;
                    hasCartUpgrade = true;

                    // If a promo code was previously applied, clear it so special offer promo applies
                    if (appliedCoupon.code) {
                        appliedCoupon = { code: '', id: 0, type: '', value: 0, cap: 0 };
                        $('#mr_coupon_code').val('');
                        $('#mr-coupon-status').slideUp(100);
                    }

                    $('#mr-summary-companion-item').remove();
                    var tag = (activeBucketRule.discount_type === 'flat') ? ('-₹' + formatInr(activeBucketRule.discount_value)) : (activeBucketRule.discount_value + '% OFF');
                    var upsellItem = $(
                        '<li id="mr-summary-companion-item" style="background:#FFF7ED; border:1.5px dashed #F97316; border-radius:10px; padding:8px 12px; margin-top:8px; display:flex; justify-content:space-between; align-items:center;">' +
                            '<span style="color:#7A1F3D; font-weight:700; font-size:12.5px;">' +
                                '<i class="fa-solid fa-gift" style="color:#EA580C; margin-right:5px;"></i>' + upsellName + ' ' +
                                '<span style="font-size:10px; background:#EA580C; color:#ffffff; padding:2px 7px; border-radius:9999px; text-transform:uppercase; margin-left:4px;">' + tag + '</span>' +
                            '</span>' +
                            '<button type="button" id="mr-btn-remove-companion" style="background:transparent; border:none; color:#DC2626; font-size:11px; font-weight:700; cursor:pointer; text-decoration:underline;">✕ Remove</button>' +
                        '</li>'
                    );
                    $('#mr-summary-items-ul').append(upsellItem);

                    var newTitle = originalTitle + ' + ' + upsellName;
                    $('#mr-summary-main-title').text(newTitle);
                    $('#mr-checkout-form input[name="package_title"]').val(newTitle);

                    $('#mr-bucket-nudge-box').slideUp(150);
                    recalcCheckoutTotals();

                    if (window.mrNotify && typeof window.mrNotify.toast === 'function') {
                        window.mrNotify.toast(upsellName + ' added! Special offer promo applied.', 'success');
                    }
                });

                // Handler: Remove Companion / Upsell item
                $(document).on('click', '#mr-btn-remove-companion', function(e) {
                    e.preventDefault();
                    companionSlug  = '';
                    companionName  = '';
                    companionPrice = 0;
                    companionReg   = 0;
                    upsellSlug     = '';
                    upsellName     = '';
                    upsellPrice    = 0;
                    hasCartUpgrade = false;

                    $('#mr-summary-companion-item').remove();
                    $('#mr-summary-main-title').text(originalTitle);
                    $('#mr-checkout-form input[name="package_title"]').val(originalTitle);
                    if ($('#mr_companion_select').length) {
                        $('#mr_companion_select').val('');
                    }
                    $('#mr-bucket-nudge-box').slideDown(150);

                    recalcCheckoutTotals();
                });

                // Coupon code handler via MR_Coupons engine
                function applyCouponCode(code) {
                    var codeClean = $.trim(code).toUpperCase();
                    var itemType = $('#mr_item_type').val() || 'reports';
                    var statusEl = $('#mr-coupon-status');

                    if (!codeClean) {
                        statusEl.css({ background: '#FEF2F2', border: '1px solid #FECACA', color: '#B91C1C' })
                                .text('Please enter a coupon code.').slideDown(120);
                        return;
                    }

                    var applyBtn = $('#mr-btn-apply-coupon');
                    applyBtn.prop('disabled', true).text('Checking...');

                    var currentGross = baseReportPrice + (companionSlug ? companionPrice : (upsellSlug ? upsellPrice : 0));

                    $.ajax({
                        url: (typeof mr_reports_vars !== 'undefined' ? mr_reports_vars.ajax_url : '<?php echo esc_js(admin_url('admin-ajax.php')); ?>'),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'mr_checkout_apply_coupon',
                            coupon_code: codeClean,
                            item_type: itemType,
                            item_slug: primarySlug,
                            gross_amount: currentGross,
                            email: $.trim($('#mr_co_email').val()),
                            phone: $.trim($('#mr_co_phone').val()),
                            nonce: $('#mr-checkout-form input[name="checkout_nonce"]').val()
                        },
                        success: function(res) {
                            applyBtn.prop('disabled', false).text('Apply');
                            if (res.success) {
                                var d = res.data;
                                appliedCoupon = {
                                    code: d.code,
                                    id: d.coupon_id || 0,
                                    type: d.type || 'percent',
                                    value: parseFloat(d.value) || 0,
                                    cap: parseFloat(d.max_discount_cap) || 0
                                };

                                recalcCheckoutTotals();

                                var successMsg = d.message || 'Coupon applied successfully!';
                                if (hasCartUpgrade) {
                                    successMsg += ' (Replaced special offer promo)';
                                }
                                statusEl.css({ background: '#ECFDF5', border: '1px solid #A7F3D0', color: '#065F46' })
                                        .text(successMsg).slideDown(120);
                                setTimeout(function() { statusEl.slideUp(200); }, 3500);
                            } else {
                                statusEl.css({ background: '#FEF2F2', border: '1px solid #FECACA', color: '#B91C1C' })
                                        .text(res.data && res.data.message ? res.data.message : 'Invalid coupon code.').slideDown(120);
                            }
                        },
                        error: function() {
                            applyBtn.prop('disabled', false).text('Apply');
                            statusEl.css({ background: '#FEF2F2', border: '1px solid #FECACA', color: '#B91C1C' })
                                    .text('Error verifying coupon. Please try again.').slideDown(120);
                        }
                    });
                }

                $('#mr-btn-apply-coupon').on('click', function(e) {
                    e.preventDefault();
                    applyCouponCode($('#mr_coupon_code').val());
                });

                $('#mr_coupon_code').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        applyCouponCode($(this).val());
                    }
                });

                $('#mr-btn-remove-coupon').on('click', function(e) {
                    e.preventDefault();
                    appliedCoupon = { code: '', id: 0, type: '', value: 0, cap: 0 };
                    $('#mr_coupon_code').val('');
                    $('#mr-coupon-status').slideUp(120);

                    // User rule: if selected promo is removed then apply special offer promo if applicable
                    recalcCheckoutTotals();

                    if (hasCartUpgrade && window.mrNotify && typeof window.mrNotify.toast === 'function') {
                        window.mrNotify.toast('Promo code removed. Special In-Cart Upgrade offer re-applied!', 'info');
                    }
                });

                // Offers Modal Triggers
                $('#mr-view-coupons-hint').on('click', function(e) {
                    e.preventDefault();
                    $('#mr-offers-modal').addClass('is-open');
                    if (typeof window.mrLockBackgroundScroll === 'function') {
                        window.mrLockBackgroundScroll();
                    } else {
                        $('html, body').addClass('mr-modal-open');
                    }
                });
                $('#mr-offers-modal-close, #mr-offers-modal-dismiss').on('click', function(e) {
                    e.preventDefault();
                    $('#mr-offers-modal').removeClass('is-open');
                    if (typeof window.mrUnlockBackgroundScroll === 'function') {
                        window.mrUnlockBackgroundScroll();
                    } else {
                        $('html, body').removeClass('mr-modal-open');
                    }
                });
                $('#mr-offers-modal').on('click', function(e) {
                    if ($(e.target).is('#mr-offers-modal')) {
                        $('#mr-offers-modal').removeClass('is-open');
                        if (typeof window.mrUnlockBackgroundScroll === 'function') {
                            window.mrUnlockBackgroundScroll();
                        } else {
                            $('html, body').removeClass('mr-modal-open');
                        }
                    }
                });
                $(document).on('click', '.mr-btn-apply-offer', function(e) {
                    e.preventDefault();
                    var code = $(this).data('code');
                    if (code) {
                        $('#mr_coupon_code').val(code);
                        applyCouponCode(code);
                        $('#mr-offers-modal').removeClass('is-open');
                        if (typeof window.mrUnlockBackgroundScroll === 'function') {
                            window.mrUnlockBackgroundScroll();
                        } else {
                            $('html, body').removeClass('mr-modal-open');
                        }
                    }
                });


                // Auto apply if coupon pre-filled in URL
                if ($('#mr_coupon_code').val()) {
                    applyCouponCode($('#mr_coupon_code').val());
                }

                // Mobile Number Input: Clean non-digits & limit length, DO NOT check count while typing
                $('#mr_co_phone').on('input', function() {
                    var raw = $(this).val();
                    var isIndia = (countrySelect.val() === 'India');
                    var cleaned = raw.replace(/\D/g, '');

                    // Limit to 10 digits for India, 15 for international
                    var maxLen = isIndia ? 10 : 15;
                    if (cleaned.length > maxLen) {
                        cleaned = cleaned.substring(0, maxLen);
                    }

                    if (raw !== cleaned) {
                        $(this).val(cleaned);
                    }

                    // While typing, clear any previous error and reset border
                    $('#mr-phone-error-hint').slideUp(100);
                    $(this).css('border-color', '');
                });

                // Paste handler: clean digits & limit length without showing error
                $('#mr_co_phone').on('paste', function(e) {
                    var clipboard = (e.originalEvent && e.originalEvent.clipboardData) ? e.originalEvent.clipboardData.getData('text') : (window.clipboardData ? window.clipboardData.getData('Text') : '');
                    if (!clipboard) return;
                    e.preventDefault();
                    var isIndia = (countrySelect.val() === 'India');
                    var digits = clipboard.replace(/\D/g, '');
                    if (isIndia && digits.length === 12 && digits.startsWith('91')) {
                        digits = digits.substring(2);
                    }
                    var maxLen = isIndia ? 10 : 15;
                    digits = digits.substring(0, maxLen);
                    $(this).val(digits);
                    $('#mr-phone-error-hint').slideUp(100);
                    $(this).css('border-color', '');
                });

                // Validate mobile number count ONLY when leaving the input box (blur)
                $('#mr_co_phone').on('blur', function() {
                    var val = $.trim($(this).val()).replace(/\D/g, '');
                    var isIndia = (countrySelect.val() === 'India');

                    if (!val) {
                        // Empty field handled on submit
                        $('#mr-phone-error-hint').slideUp(120);
                        $(this).css('border-color', '');
                        return;
                    }

                    if (isIndia) {
                        if (val.length !== 10 || !/^[5-9][0-9]{9}$/.test(val)) {
                            $('#mr-phone-error-hint').text('Please enter a valid 10-digit mobile number starting with 5, 6, 7, 8, or 9').slideDown(120);
                            $(this).css('border-color', '#DC2626');
                        } else {
                            $('#mr-phone-error-hint').slideUp(120);
                            $(this).css('border-color', '');
                        }
                    } else {
                        if (val.length < 7 || val.length > 15) {
                            $('#mr-phone-error-hint').text('Please enter a valid contact number (7 to 15 digits)').slideDown(120);
                            $(this).css('border-color', '#DC2626');
                        } else {
                            $('#mr-phone-error-hint').slideUp(120);
                            $(this).css('border-color', '');
                        }
                    }
                });

                // Clear consent error dynamically upon checking
                $('#mr_checkout_dpdp_consent').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#mr-checkout-consent-error').slideUp(100);
                    }
                });

                // Submit Checkout Button Click
                $('#mr-btn-checkout-submit').on('click', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var errBox = $('#mr-co-error-box');
                    errBox.hide().text('');

                    var name    = $.trim($('#mr_co_name').val());
                    var email   = $.trim($('#mr_co_email').val());
                    var phone   = $.trim($('#mr_co_phone').val());
                    var country = countrySelect.val();

                    if (!name) {
                        errBox.text('Please enter your full name.').slideDown(150);
                        $('#mr_co_name').focus();
                        return;
                    }
                    if (!email || email.indexOf('@') === -1) {
                        errBox.text('Please enter a valid email address to receive your invoice.').slideDown(150);
                        $('#mr_co_email').focus();
                        return;
                    }
                    if (!phone) {
                        errBox.text('Please enter your mobile number.').slideDown(150);
                        $('#mr-phone-error-hint').text('Please enter your mobile number.').slideDown(120);
                        $('#mr_co_phone').css('border-color', '#DC2626').focus();
                        return;
                    }
                    var cleanPhone = phone.replace(/\D/g, '');
                    if (country === 'India') {
                        if (cleanPhone.length !== 10 || !/^[5-9][0-9]{9}$/.test(cleanPhone)) {
                            errBox.text('Please enter a valid 10-digit mobile number starting with 5, 6, 7, 8, or 9 (numbers only, no special characters).').slideDown(150);
                            $('#mr-phone-error-hint').text('Please enter a valid 10-digit mobile number starting with 5, 6, 7, 8, or 9').slideDown(120);
                            $('#mr_co_phone').css('border-color', '#DC2626').focus();
                            return;
                        }
                    } else {
                        if (cleanPhone.length < 7 || cleanPhone.length > 15) {
                            errBox.text('Please enter a valid mobile number (7 to 15 digits).').slideDown(150);
                            $('#mr-phone-error-hint').text('Please enter a valid contact number (7 to 15 digits)').slideDown(120);
                            $('#mr_co_phone').css('border-color', '#DC2626').focus();
                            return;
                        }
                    }

                    var state = '';
                    var address = '';
                    var city = '';
                    var pincode = '';
                    var isIntl = (country !== 'India');

                    if (!isIntl) {
                        state = $('#mr_co_state_india').val();
                        if (!state) {
                            errBox.text('Please select your state.').slideDown(150);
                            $('#mr_co_state_india').focus();
                            return;
                        }
                    } else {
                        address = $.trim($('#mr_co_intl_address').val());
                        city    = $.trim($('#mr_co_intl_city').val());
                        state   = $.trim($('#mr_co_intl_state').val());
                        pincode = $.trim($('#mr_co_intl_postcode').val());

                        if (!address || !city || !state || !pincode) {
                            errBox.text('International orders require complete street address, city, state/province, and postal code.').slideDown(150);
                            return;
                        }
                    }

                    // Mandatory DPDP & Statutory Policy Consent Validation
                    var isConsentGiven = $('#mr_checkout_dpdp_consent').is(':checked');
                    if (!isConsentGiven) {
                        $('#mr-checkout-consent-error').slideDown(120);
                        errBox.text('Please accept the Terms & Conditions, Privacy Policy, and Refund Policy and confirm you are 18+ to proceed.').slideDown(150);
                        $('#mr_checkout_dpdp_consent').focus();
                        return;
                    } else {
                        $('#mr-checkout-consent-error').slideUp(100);
                    }

                    // Disable button and initiate order
                    btn.prop('disabled', true).find('#mr-btn-pay-text').html('<i class="fa-solid fa-spinner fa-spin"></i> Processing Secure Order...');

                    var formData = $('#mr-checkout-form').serializeArray();
                    formData.push({ name: 'action', value: 'mr_checkout_create_order' });
                    formData.push({ name: 'state', value: state });
                    formData.push({ name: 'address', value: address });
                    formData.push({ name: 'city', value: city });
                    formData.push({ name: 'pincode', value: pincode });
                    formData.push({ name: 'is_international', value: isIntl ? '1' : '0' });
                    formData.push({ name: 'dpdp_consent', value: isConsentGiven ? '1' : '0' });

                    $.ajax({
                        url: (typeof mr_reports_vars !== 'undefined' ? mr_reports_vars.ajax_url : '<?php echo esc_js(admin_url('admin-ajax.php')); ?>'),
                        type: 'POST',
                        dataType: 'json',
                        data: formData,
                        success: function(res) {
                            if (!res.success) {
                                btn.prop('disabled', false).find('#mr-btn-pay-text').html('Pay &amp; Generate Invoice &rarr;');
                                errBox.text(res.data && res.data.message ? res.data.message : 'Error preparing order. Please try again.').slideDown(150);
                                return;
                            }

                            var orderData = res.data;

                            // Handle Razorpay Live / Test modal
                            var options = {
                                key: orderData.key_id || '<?php echo esc_js($key_id); ?>',
                                amount: orderData.amount_paise,
                                currency: 'INR',
                                name: '<?php echo esc_js(MR_Invoice::COMPANY_NAME); ?>',
                                description: orderData.package_title,
                                image: '<?php echo esc_js(MR_PLUGIN_URL . 'assets/images/logo.png'); ?>',
                                order_id: orderData.gateway_order_id,
                                prefill: {
                                    name: name,
                                    email: email,
                                    contact: phone
                                },
                                theme: {
                                    color: '#7A1F3D'
                                },
                                handler: function(response) {
                                    // Verify payment and finalize invoice
                                    verifyPayment(orderData.internal_id, response.razorpay_payment_id, response.razorpay_signature, orderData.report_slug, orderData.package_title);
                                },
                                modal: {
                                    ondismiss: function() {
                                        btn.prop('disabled', false).find('#mr-btn-pay-text').html('Pay &amp; Generate Invoice &rarr;');
                                    }
                                }
                            };

                            try {
                                var rzp = new Razorpay(options);
                                rzp.open();
                            } catch (e) {
                                // Fallback for simulated PG test mode
                                console.warn('Razorpay checkout fallback triggered:', e);
                                verifyPayment(orderData.internal_id, 'pay_sim_' + Math.random().toString(36).substring(2, 10), '', orderData.report_slug, orderData.package_title);
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false).find('#mr-btn-pay-text').html('Pay &amp; Generate Invoice &rarr;');
                            errBox.text('Server network error. Please try again.').slideDown(150);
                        }
                    });
                });

                // Finalize Payment and Transition to Success View
                function verifyPayment(internalId, paymentId, signature, reportSlug, title) {
                    var btn = $('#mr-btn-checkout-submit');
                    btn.find('#mr-btn-pay-text').html('<i class="fa-solid fa-spinner fa-spin"></i> Finalizing Invoice &amp; Unlocking...');

                    // Freeze website with requested overlay and message
                    var freezeOverlay = $('#mr-checkout-unlocking-overlay');
                    if (freezeOverlay.length) {
                        freezeOverlay.find('#mr-unlocking-status-text').html('Assigning retail invoice &amp; activating report credits...');
                        freezeOverlay.find('.mr-unlocking-spinner').removeClass('fa-circle-check').addClass('fa-circle-notch fa-spin').css('color', '#EA580C');
                        freezeOverlay.css('display', 'flex').hide().fadeIn(200);
                        $('html, body').addClass('mr-freeze-scroll');
                    }

                    $.ajax({
                        url: (typeof mr_reports_vars !== 'undefined' ? mr_reports_vars.ajax_url : '<?php echo esc_js(admin_url('admin-ajax.php')); ?>'),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'mr_checkout_verify_payment',
                            internal_id: internalId,
                            payment_id: paymentId,
                            signature: signature,
                            nonce: $('#mr-checkout-form input[name="checkout_nonce"]').val()
                        },
                        success: function(res) {
                            if (res.success) {
                                var d = res.data;

                                if (freezeOverlay.length) {
                                    $('#mr-unlocking-status-text').html('<i class="fa-solid fa-circle-check" style="color:#059669;"></i> Purchase unlocked successfully! Preparing your report dashboard...');
                                    freezeOverlay.find('.mr-unlocking-spinner').removeClass('fa-circle-notch fa-spin').addClass('fa-circle-check').css('color', '#059669');
                                }

                                setTimeout(function() {
                                    if (freezeOverlay.length) {
                                        freezeOverlay.fadeOut(250, function() {
                                            $('html, body').removeClass('mr-freeze-scroll');
                                        });
                                    }

                                    $('#mr-checkout-main-grid').slideUp(150);
                                    $('#mr-succ-inv-num').text(d.invoice_number);
                                    $('#mr-succ-rzp-id').text(paymentId);
                                    $('#mr-succ-internal-id').text(internalId);
                                    $('#mr-succ-amount').text('₹' + (Math.round(Number(d.amount) * 100) / 100).toLocaleString('en-IN', { maximumFractionDigits: 2 }));
                                    $('#mr-succ-download-inv-btn').attr('href', d.invoice_url);

                                    if (d.is_chat_recharge) {
                                        // Chatbot Recharge Fulfillment View
                                        $('#mr-succ-bundle-queue-wrap').hide();
                                        $('#mr-succ-generate-btn').hide();
                                        $('#mr-succ-chat-credits-count').text(d.credits_added || '<?php echo esc_js($chat_credits); ?>');
                                        $('#mr-succ-chat-feature-wrap').show();
                                        $('#mr-succ-chat-btn').show();

                                        // Instantly refresh Niyati chat widget wallet & quota UI
                                        if (typeof window.niyatiRefreshWallet === 'function') {
                                            window.niyatiRefreshWallet();
                                        }

                                        if (window.mrNotify && typeof window.mrNotify.toast === 'function') {
                                            window.mrNotify.toast('Wallet recharged successfully! Questions credited with lifetime validity.', 'success');
                                        }
                                    } else if (d.reports && d.reports.length > 1) {
                                        // Multiple reports in bundle: Show itemized fulfillment queue & start sequential flow
                                        $('#mr-succ-generate-btn').hide();
                                        $('#mr-succ-chat-feature-wrap').hide();
                                        $('#mr-succ-chat-btn').hide();
                                        var queueList = $('#mr-succ-bundle-items-list');
                                        queueList.empty();
                                        $('#mr-succ-bundle-count').text(d.reports.length + ' Reports');

                                        for (var i = 0; i < d.reports.length; i++) {
                                            var r = d.reports[i];
                                            var isFirst = (i === 0);
                                            var btnBg = isFirst ? '#F58220' : '#7A1F3D';
                                            var badgeBg = isFirst ? '#FFF3E1' : '#F1F5F9';
                                            var badgeColor = isFirst ? '#7A1F3D' : '#475569';
                                            var badgeText = isFirst ? 'Report 1 (Primary Report)' : ('Report ' + (i + 1) + ' of ' + d.reports.length);

                                            var itemCard = $(
                                                '<div class="mr-queue-card" id="mr-queue-card-' + r.slug + '" style="background:#FFFFFF; border:1.5px solid #E8DCCF; border-radius:12px; padding:15px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; box-shadow:0 3px 10px rgba(122,31,61,0.04);">' +
                                                    '<div style="max-width:440px;">' +
                                                        '<span style="font-size:10.5px; font-weight:800; background:' + badgeBg + '; color:' + badgeColor + '; padding:2px 8px; border-radius:9999px; text-transform:uppercase;">' + badgeText + '</span>' +
                                                        '<div style="font-size:15px; font-weight:700; color:#1E293B; margin-top:4px;">' + r.name + '</div>' +
                                                        '<div id="mr-queue-sub-' + r.slug + '" style="font-size:12px; color:#64748B; margin-top:2px;">' + (isFirst ? 'Enter primary birth details to start generation' : 'Waiting for Report 1 details') + '</div>' +
                                                    '</div>' +
                                                    '<div id="mr-queue-action-' + r.slug + '">' +
                                                        '<button type="button" class="mr-btn-pay mr-btn-queue-action" data-slug="' + r.slug + '" data-title="' + r.name + '" data-index="' + i + '" style="width:auto; padding:9px 18px; font-size:12px; background:' + btnBg + '; cursor:pointer;">' +
                                                            '<i class="fa-solid fa-wand-magic-sparkles"></i> ' + (isFirst ? 'Enter Details &amp; Start &rarr;' : 'Enter Details &rarr;') +
                                                        '</button>' +
                                                    '</div>' +
                                                '</div>'
                                            );
                                            queueList.append(itemCard);
                                        }

                                        queueList.off('click', '.mr-btn-queue-action').on('click', '.mr-btn-queue-action', function(e) {
                                            e.preventDefault();
                                            var b = $(this);
                                            var idx = parseInt(b.data('index'), 10);
                                            if (!isNaN(idx) && typeof window.mrBundleFlow !== 'undefined' && typeof window.mrBundleFlow.openStep === 'function') {
                                                window.mrBundleFlow.openStep(idx);
                                            } else {
                                                var s = b.data('slug');
                                                var t = b.data('title');
                                                if (typeof window.openDetailsModal === 'function') {
                                                    window.openDetailsModal(s, t);
                                                } else {
                                                    var m = $('#mr-details-modal');
                                                    if (m.length) {
                                                        if (!m.parent().is('body')) m.appendTo('body');
                                                        $('#mr_report_slug').val(s);
                                                        $('#mr-modal-report-title').text(t);
                                                        m.addClass('is-open').fadeIn(150);
                                                        if (typeof window.mrLockBackgroundScroll === 'function') {
                                                            window.mrLockBackgroundScroll();
                                                        } else {
                                                            $('html, body').addClass('mr-modal-open');
                                                        }
                                                    }
                                                }
                                            }
                                        });

                                        $('#mr-succ-bundle-queue-wrap').show();

                                        // Launch automated sequential bundle flow: Report 1 input first immediately
                                        if (typeof window.mrBundleFlow !== 'undefined' && typeof window.mrBundleFlow.init === 'function') {
                                            window.mrBundleFlow.init(d.reports);
                                        }
                                    } else {
                                        // Single report: Auto-launch birth details modal immediately
                                        $('#mr-succ-chat-feature-wrap').hide();
                                        $('#mr-succ-chat-btn').hide();
                                        var openSingle = function() {
                                            if (typeof window.openDetailsModal === 'function') {
                                                window.openDetailsModal(reportSlug, title);
                                            } else {
                                                var m = $('#mr-details-modal');
                                                if (m.length) {
                                                    if (!m.parent().is('body')) m.appendTo('body');
                                                    $('#mr_report_slug').val(reportSlug);
                                                    m.addClass('is-open').fadeIn(150);
                                                    if (typeof window.mrLockBackgroundScroll === 'function') {
                                                        window.mrLockBackgroundScroll();
                                                    } else {
                                                        $('html, body').addClass('mr-modal-open');
                                                    }
                                                }
                                            }
                                        };
                                        var singleTitle = (d.reports && d.reports[0] && d.reports[0].name) ? d.reports[0].name : title;
                                        $('#mr-succ-generate-btn').html('<i class="fa-solid fa-wand-magic-sparkles"></i> Fill Birth Details for ' + singleTitle + ' &rarr;').show().off('click').on('click', openSingle);
                                        openSingle();
                                    }

                                    $('#mr-checkout-success-view').fadeIn(200);
                                    $('html, body').animate({ scrollTop: $('#mr-checkout-success-view').offset().top - 40 }, 300);

                                    if (!d.is_chat_recharge && window.mrNotify && typeof window.mrNotify.toast === 'function') {
                                        window.mrNotify.toast('Payment verified! Please provide your birth details below.', 'success');
                                    }
                                }, 500);
                            } else {
                                if (freezeOverlay.length) {
                                    freezeOverlay.fadeOut(200, function() {
                                        $('html, body').removeClass('mr-freeze-scroll');
                                    });
                                }
                                btn.prop('disabled', false).find('#mr-btn-pay-text').html('Pay &amp; Generate Invoice &rarr;');
                                alert('Verification notice: ' + (res.data ? res.data.message : 'Please check your account.'));
                            }
                        },
                        error: function(xhr, status, error) {
                            if (freezeOverlay.length) {
                                freezeOverlay.fadeOut(200, function() {
                                    $('html, body').removeClass('mr-freeze-scroll');
                                });
                            }
                            btn.prop('disabled', false).find('#mr-btn-pay-text').html('Pay &amp; Generate Invoice &rarr;');
                            alert('Payment captured! Your invoice will be delivered to your email and accessible in My Account.');
                            window.location.href = '<?php echo esc_js(home_url('/my-account/#passbook')); ?>';
                        }
                    });
                }

                // Chat recharge success button: Open chat widget immediately
                $('#mr-succ-chat-btn').on('click', function(e) {
                    if (typeof window.openNiyatiChat === 'function') {
                        e.preventDefault();
                        window.openNiyatiChat();
                        return false;
                    }
                    var launcher = $('#niyati-launcher');
                    if (launcher.length) {
                        e.preventDefault();
                        launcher.trigger('click');
                        return false;
                    }
                    // If widget is not loaded on current page, store intent in sessionStorage and proceed to home anchor
                    try { sessionStorage.setItem('niyati_auto_open', '1'); } catch(err) {}
                });
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Action: Create Checkout Order & Register Internal Transaction
     */
    public static function ajax_create_order() {
        check_ajax_referer('mr_reports_nonce', 'checkout_nonce');

        $item_type    = sanitize_key($_POST['item_type'] ?? 'report_bundle');
        $pack_key     = sanitize_key($_POST['pack_key'] ?? 'pack_value');
        $coupon_code  = sanitize_text_field($_POST['applied_coupon'] ?? '');
        $visitor_id   = sanitize_text_field($_POST['visitor_id'] ?? '');

        if ($item_type === 'chat_credits') {
            $report_slug      = 'chat_credits';
            $bundle_tier      = 'single';
            $included_reports = [];

            $packs = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_packs(false) : [];
            if (!empty($packs[$pack_key])) {
                $pack = $packs[$pack_key];
            } else {
                $pack = reset($packs) ?: null;
                $pack_key = $pack ? $pack['key'] : 'pack_value';
            }

            if ($pack) {
                $package_title = $pack['title'];
                $price         = floatval($pack['offer_price']);
                $chat_credits  = intval($pack['total_credits']);
            } else {
                $package_title = 'Niyati AI Chat Credits Recharge';
                $price         = 100.0;
                $chat_credits  = 120;
            }
            $channel  = 'chat_recharge';
            $item_key = $pack_key;
        } else {
            $report_slug      = sanitize_key($_POST['report_slug'] ?? 'yearly_horoscope_long');
            $bundle_tier      = sanitize_key($_POST['bundle_tier'] ?? 'trio');
            $package_title    = sanitize_text_field($_POST['package_title'] ?? 'Astrological Report Package');
            $price            = floatval($_POST['price'] ?? 299);
            $chat_credits     = intval($_POST['chat_credits'] ?? 0);
            $included_reports = !empty($_POST['included_reports']) ? json_decode(stripslashes($_POST['included_reports']), true) : [$report_slug];
            if (!is_array($included_reports)) $included_reports = [$report_slug];

            // Authoritative verification against MR_Bundles dynamic pricing engine
            if (class_exists('MR_Bundles')) {
                $bundle_cfg = MR_Bundles::get_bundle_for_report($report_slug);
                $tier_key   = 'tier_' . $bundle_tier;
                if (!empty($bundle_cfg[$tier_key])) {
                    $tier_info = $bundle_cfg[$tier_key];
                    $paired    = !empty($tier_info['paired_reports']) ? (array)$tier_info['paired_reports'] : [];
                    $expected_tier_reports = array_merge([$report_slug], $paired);
                    // Authoritatively ensure all tier reports are present, preserving any companion report added by user
                    $included_reports = array_values(array_unique(array_merge($expected_tier_reports, $included_reports)));

                    if (!empty($tier_info['price_sale'])) {
                        $price        = floatval($tier_info['price_sale']);
                        $chat_credits = intval($tier_info['chat_credits'] ?? $chat_credits);
                        if (!empty($tier_info['title'])) {
                            $package_title = $tier_info['title'];
                        }
                    }
                }
            }
            $channel  = 'report';
            $item_key = $report_slug;
        }

        $billing_name     = sanitize_text_field($_POST['billing_name'] ?? '');
        $billing_email    = sanitize_email($_POST['billing_email'] ?? '');
        $raw_phone        = sanitize_text_field($_POST['billing_phone'] ?? '');
        $billing_phone    = preg_replace('/\D/', '', $raw_phone);
        if (strlen($billing_phone) === 12 && substr($billing_phone, 0, 2) === '91') {
            $billing_phone = substr($billing_phone, 2);
        }
        $billing_country  = sanitize_text_field($_POST['billing_country'] ?? 'India');
        $billing_state    = sanitize_text_field($_POST['state'] ?? 'Uttar Pradesh');
        $billing_address  = sanitize_text_field($_POST['address'] ?? '');
        $billing_city     = sanitize_text_field($_POST['city'] ?? '');
        $billing_pincode  = sanitize_text_field($_POST['pincode'] ?? '');
        $is_intl          = !empty($_POST['is_international']) && $_POST['is_international'] === '1';

        if (!$billing_name || !$billing_email || !$billing_phone) {
            wp_send_json_error(['message' => 'Please provide complete name, email, and mobile number.']);
        }
        if (!is_email($billing_email)) {
            wp_send_json_error(['message' => 'Please enter a valid email address.']);
        }
        if (!$is_intl) {
            if (strlen($billing_phone) !== 10 || !preg_match('/^[5-9][0-9]{9}$/', $billing_phone)) {
                wp_send_json_error(['message' => 'Please enter a valid 10-digit mobile number starting with 5, 6, 7, 8, or 9 (numbers only, no special characters).']);
            }
        } else {
            if (strlen($billing_phone) < 7 || strlen($billing_phone) > 15) {
                wp_send_json_error(['message' => 'Please enter a valid international mobile/contact number (7 to 15 digits).']);
            }
        }

        // Validate Mandatory DPDP & Statutory Policy Consent
        $dpdp_consent = !empty($_POST['dpdp_consent']) && ($_POST['dpdp_consent'] === '1' || $_POST['dpdp_consent'] === 1 || $_POST['dpdp_consent'] === true);
        if (!$dpdp_consent) {
            wp_send_json_error(['message' => 'Please agree to the Terms & Conditions, Privacy Policy, and Refund Policy, and confirm you are 18+ to proceed.']);
        }

        $user_id = get_current_user_id() ?: 1;
        if ($user_id > 1) {
            update_user_meta($user_id, 'billing_phone', $billing_phone);
            update_user_meta($user_id, 'billing_state', $billing_state);
            update_user_meta($user_id, 'billing_country', $billing_country);
            update_user_meta($user_id, 'mr_dpdp_checkout_consent_at', current_time('mysql'));
            update_user_meta($user_id, 'mr_dpdp_age_affirmed_18', '1');
            if ($is_intl) {
                update_user_meta($user_id, 'billing_address_1', $billing_address);
                update_user_meta($user_id, 'billing_city', $billing_city);
                update_user_meta($user_id, 'billing_postcode', $billing_pincode);
            }
        }

        // Process Dynamic Bucket Upsell & Total Billing Discount
        $companion_slug   = sanitize_key($_POST['companion_slug'] ?? '');
        $bucket_rule_id   = sanitize_key($_POST['bucket_rule_id'] ?? '');
        $upsell_item_key  = sanitize_key($_POST['upsell_item_key'] ?? $companion_slug);
        $upsell_item_name = sanitize_text_field($_POST['upsell_item_name'] ?? '');
        $upsell_price     = floatval($_POST['upsell_item_price'] ?? 0);
        $bucket_discount  = 0.00;
        $bucket_label     = '';

        if (!empty($upsell_item_key) || !empty($companion_slug)) {
            // If catalog companion report
            if (!empty($companion_slug)) {
                if (!in_array($companion_slug, $included_reports, true)) {
                    $included_reports[] = $companion_slug;
                }
                $cand_list = class_exists('MR_Bucket_Discount') ? MR_Bucket_Discount::get_companion_upsell_candidates($report_slug) : [];
                $companion_info  = $cand_list[$companion_slug] ?? null;
                $upsell_price    = $companion_info ? floatval($companion_info['price']) : 99.0;
                $upsell_item_name= $companion_info['name'] ?? $companion_slug;
            }

            if ($upsell_price > 0) {
                $pre_tax_base = $price + $upsell_price;
                if (!empty($upsell_item_name)) {
                    $package_title .= ' + ' . $upsell_item_name;
                }

                if (!empty($bucket_rule_id) && class_exists('MR_Bucket_Discount')) {
                    $rule = MR_Bucket_Discount::get_rule($bucket_rule_id);
                    if ($rule) {
                        $disc_res = MR_Bucket_Discount::calculate_discount($rule, $pre_tax_base);
                        if (!empty($disc_res['eligible'])) {
                            $bucket_discount = floatval($disc_res['discount_amount']);
                            $bucket_label    = $disc_res['rule_label'] ?? '';
                        }
                    }
                } elseif (class_exists('MR_Bucket_Discount')) {
                    $bucket_res = MR_Bucket_Discount::calculate_bucket_discount($included_reports, $pre_tax_base);
                    if (!empty($bucket_res['eligible'])) {
                        $bucket_discount = floatval($bucket_res['discount_amount']);
                        $bucket_label    = $bucket_res['rule_label'] ?? '';
                    }
                }
                $price = $pre_tax_base; // Base pre-tax gross amount
            }
        }

        // Validate Coupon server-side (Enforce: Strictly ONE promo active at a time)
        $coupon_code     = strtoupper(trim(sanitize_text_field($_POST['applied_coupon'] ?? '')));
        $discount_amount = 0.00;
        $applied_code    = '';
        $coupon_id       = 0;

        if (!empty($coupon_code)) {
            // User rule: When a promo code is applied, it replaces the in-cart special upgrade offer
            $bucket_discount = 0.00;
            $bucket_label    = '';
            $taxable_base    = max(0.00, $price);

            $val_res = self::validate_coupon($coupon_code, $item_type, $taxable_base, [
                'user_email' => $billing_email,
                'user_phone' => $billing_phone,
                'user_id'    => $user_id,
                'item_slug'  => $report_slug
            ]);
            if (!empty($val_res['valid'])) {
                $discount_amount = floatval($val_res['discount_amount']);
                $applied_code    = $val_res['code'];
                $coupon_id       = intval($val_res['coupon_id'] ?? 0);
            }
        }

        $gross_amount   = round($price, 2);
        $total_discount = round($bucket_discount + $discount_amount, 2);
        $net_amount     = max(1.0, round($gross_amount - $total_discount, 2));

        // Generate unique internal ID
        $internal_id = 'MR_ORD_' . strtoupper(bin2hex(random_bytes(5)));

        // Create Razorpay Gateway Order
        $key_id       = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_id() : '';
        $key_secret   = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_secret() : '';
        $amount_paise = round($net_amount * 100);
        $gateway_order_id = '';

        if (!empty($key_id) && !empty($key_secret)) {
            $res = wp_remote_post('https://api.razorpay.com/v1/orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret),
                    'Content-Type'  => 'application/json'
                ],
                'body' => wp_json_encode([
                    'amount'   => $amount_paise,
                    'currency' => 'INR',
                    'receipt'  => $internal_id,
                    'notes'    => [
                        'package_title'   => $package_title,
                        'customer_name'   => $billing_name,
                        'customer_email'  => $billing_email,
                        'customer_state'  => $billing_state,
                        'item_type'       => $item_type,
                        'coupon_code'     => $applied_code
                    ]
                ]),
                'timeout' => 12
            ]);

            if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (!empty($body['id'])) {
                    $gateway_order_id = $body['id'];
                }
            } else {
                $err_desc = 'Payment gateway error.';
                if (is_wp_error($res)) {
                    $err_desc = 'Gateway network error: ' . $res->get_error_message();
                } else {
                    $body = json_decode(wp_remote_retrieve_body($res), true);
                    if (!empty($body['error']['description'])) {
                        $err_desc = 'Razorpay Error: ' . $body['error']['description'];
                    } else {
                        $err_desc = 'Gateway error (HTTP ' . wp_remote_retrieve_response_code($res) . ')';
                    }
                }
                error_log('[MR_Checkout] Razorpay order creation failed: ' . $err_desc);
                wp_send_json_error(['message' => $err_desc . '. Please verify your Razorpay API Key and Secret.']);
                return;
            }
        } elseif (MR_Razorpay::get_mode() === 'live') {
            wp_send_json_error(['message' => 'Razorpay live credentials are not configured in wp-config.php.']);
            return;
        }

        if (empty($gateway_order_id)) {
            // Local sandbox fallback only when live keys are not configured
            $gateway_order_id = 'order_' . bin2hex(random_bytes(7));
        }

        // Create initial transaction record
        if (class_exists('MR_Transactions')) {
            MR_Transactions::create_transaction([
                'internal_txn_id'  => $internal_id,
                'user_id'          => $user_id,
                'customer_name'    => $billing_name,
                'customer_email'   => $billing_email,
                'customer_phone'   => $billing_phone,
                'channel'          => $channel,
                'item_key'         => $item_key,
                'item_name'        => $package_title,
                'txn_type'         => 'purchase',
                'gross_amount'     => $gross_amount,
                'discount_amount'  => $total_discount,
                'net_amount'       => $net_amount,
                'currency'         => 'INR',
                'gateway'          => 'razorpay',
                'gateway_order_id' => $gateway_order_id,
                'status'           => 'pending',
                'metadata'         => [
                    'item_type'        => $item_type,
                    'pack_key'         => $pack_key,
                    'coupon_code'      => $applied_code,
                    'coupon_id'        => $coupon_id,
                    'coupon_discount'  => $discount_amount,
                    'bucket_discount'  => $bucket_discount,
                    'bucket_rule_id'   => $bucket_rule_id,
                    'bucket_label'     => $bucket_label,
                    'upsell_item_key'  => $upsell_item_key,
                    'upsell_item_name' => $upsell_item_name,
                    'upsell_price'     => $upsell_price,
                    'companion_slug'   => $companion_slug,
                    'bundle_tier'      => $bundle_tier,
                    'bundle_reports'   => $included_reports,
                    'chat_credits'     => $chat_credits,
                    'visitor_id'       => $visitor_id,
                    'billing_name'     => $billing_name,
                    'billing_email'    => $billing_email,
                    'billing_phone'    => $billing_phone,
                    'billing_country'  => $billing_country,
                    'billing_state'    => $billing_state,
                    'billing_address'  => $billing_address,
                    'billing_city'     => $billing_city,
                    'billing_pincode'  => $billing_pincode,
                    'is_international' => $is_intl
                ]
            ]);
        }

        wp_send_json_success([
            'internal_id'      => $internal_id,
            'gateway_order_id' => $gateway_order_id,
            'key_id'           => $key_id,
            'amount_paise'     => $amount_paise,
            'package_title'    => $package_title,
            'report_slug'      => $report_slug,
            'net_amount'       => $net_amount,
            'is_chat_recharge' => ($item_type === 'chat_credits')
        ]);
    }

    /**
     * AJAX Action: Verify Razorpay Payment, Assign Sequential Invoice & Fulfill
     */
    public static function ajax_verify_payment() {
        check_ajax_referer('mr_reports_nonce', 'nonce');

        $internal_id = sanitize_text_field($_POST['internal_id'] ?? '');
        $payment_id  = sanitize_text_field($_POST['payment_id'] ?? '');
        $signature   = sanitize_text_field($_POST['signature'] ?? '');

        if (!$internal_id) {
            wp_send_json_error(['message' => 'Missing transaction ID.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $txn = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE internal_txn_id = %s LIMIT 1", $internal_id), ARRAY_A);
        if (!$txn) {
            wp_send_json_error(['message' => 'Transaction record not found.']);
        }

        // Security Verification: Cryptographic HMAC SHA256 Signature Check
        $gateway_order_id = $txn['gateway_order_id'] ?? '';
        $is_simulation = (strpos($payment_id, 'pay_sim_') !== false || empty($signature));

        if ($is_simulation) {
            // Only allow simulated payment if WP_DEBUG is explicitly enabled (local testing)
            if (!defined('WP_DEBUG') || !WP_DEBUG) {
                wp_send_json_error(['message' => 'Simulated test payments are disabled in live production.']);
            }
        } else {
            // Verify HMAC SHA256 signature for live Razorpay payments
            $secrets_to_try = [];
            if (class_exists('MR_Razorpay')) {
                $active_sec = MR_Razorpay::get_active_key_secret();
                $live_sec   = MR_Razorpay::get_live_key_secret();
                $test_sec   = MR_Razorpay::get_test_key_secret();
                $secrets_to_try = array_unique(array_filter([$active_sec, $live_sec, $test_sec]));
            }

            if (!empty($secrets_to_try) && !empty($gateway_order_id)) {
                $sig_valid = false;
                foreach ($secrets_to_try as $sec) {
                    $expected_sig = hash_hmac('sha256', $gateway_order_id . '|' . $payment_id, $sec);
                    if (hash_equals($expected_sig, $signature)) {
                        $sig_valid = true;
                        break;
                    }
                }
                if (!$sig_valid) {
                    wp_send_json_error(['message' => 'Payment signature verification failed. Transaction was not confirmed by Razorpay.']);
                }
            }
        }

        // 1. Mark transaction complete
        if (class_exists('MR_Transactions')) {
            MR_Transactions::complete_transaction(
                $internal_id,
                $payment_id,
                $signature,
                ['completed_via' => 'checkout_page']
            );
        }

        // 2. Assign unique sequential Invoice Number MT/26-27/11/00001
        $inv_num = class_exists('MR_Invoice') ? MR_Invoice::assign_invoice_to_transaction($txn['id']) : 'MT/26-27/11/00001';

        // Refresh txn data
        $txn = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d LIMIT 1", $txn['id']), ARRAY_A);
        $meta = !empty($txn['metadata']) ? (is_array($txn['metadata']) ? $txn['metadata'] : json_decode($txn['metadata'], true)) : [];

        $user_id   = intval($txn['user_id'] ?: (get_current_user_id() ?: 1));
        $chat_cred = intval($meta['chat_credits'] ?? 0);
        $is_chat_recharge = ($txn['channel'] === 'chat_recharge' || ($meta['item_type'] ?? '') === 'chat_credits');

        // Atomically commit coupon redemption & enforce FCFS limits
        if (!empty($meta['coupon_id']) && class_exists('MR_Coupons')) {
            MR_Coupons::commit_redemption(intval($meta['coupon_id']), [
                'user_id'            => $user_id,
                'user_email'         => $txn['customer_email'],
                'user_phone'         => $txn['customer_phone'],
                'internal_txn_id'    => $internal_id,
                'gateway_payment_id' => $payment_id,
                'subtotal_pretax'    => floatval($txn['gross_amount']),
                'discount_applied'   => floatval($meta['coupon_discount'] ?? 0),
                'tax_amount'         => 0.00,
                'total_paid'         => floatval($txn['net_amount']),
                'product_type'       => $is_chat_recharge ? 'chat_credits' : 'reports',
            ]);
        }

        // 3. Fulfill: Chat Recharge or Report Bundles
        if ($is_chat_recharge) {
            $visitor_id = !empty($meta['visitor_id']) 
                ? sanitize_text_field($meta['visitor_id']) 
                : (!empty($_COOKIE['niyati_visitor_id']) ? sanitize_text_field($_COOKIE['niyati_visitor_id']) : ('user_' . $user_id));

            $pack_key = $meta['pack_key'] ?? $txn['item_key'];
            $credits_added = $chat_cred;
            if ($credits_added <= 0 && class_exists('Niyati_Wallet')) {
                $packs = Niyati_Wallet::get_packs(false);
                if (!empty($packs[$pack_key]['total_credits'])) {
                    $credits_added = intval($packs[$pack_key]['total_credits']);
                } else {
                    $credits_added = 120;
                }
            }

            if (class_exists('Niyati_Wallet')) {
                Niyati_Wallet::add_credits(
                    $visitor_id,
                    $user_id,
                    $credits_added,
                    floatval($txn['net_amount']),
                    $payment_id,
                    $pack_key,
                    'razorpay',
                    'Chat Recharge: ' . $txn['item_name'],
                    0,
                    ['invoice_number' => $inv_num, 'coupon' => $meta['coupon_code'] ?? '']
                );
            }

            // Asynchronously dispatch PDF Invoice generation & Email delivery
            if (class_exists('MR_Invoice')) {
                MR_Invoice::dispatch_async_invoice_email($txn['id']);
            }

            $inv_url = class_exists('MR_Invoice') ? MR_Invoice::get_invoice_url($txn) : home_url('/invoice/?id=' . urlencode($internal_id));

            wp_send_json_success([
                'message'          => 'Wallet recharged successfully! Questions credited with lifetime validity.',
                'invoice_number'   => $inv_num,
                'invoice_url'      => $inv_url,
                'amount'           => $txn['net_amount'],
                'internal_id'      => $internal_id,
                'is_chat_recharge' => true,
                'credits_added'    => $credits_added,
                'reports'          => []
            ]);
        }

        // Report Bundles Fulfillment
        $reports     = !empty($meta['bundle_reports']) ? (array)$meta['bundle_reports'] : [$txn['item_key']];
        $bundle_tier = $meta['bundle_tier'] ?? 'single';

        // Fail-safe: if tier is duo or trio but reports array has <= 1 item, recover from MR_Bundles
        if (($bundle_tier === 'duo' || $bundle_tier === 'trio') && count($reports) <= 1 && class_exists('MR_Bundles')) {
            $bundle_cfg = MR_Bundles::get_bundle_for_report($txn['item_key']);
            $tier_cfg   = $bundle_cfg['tier_' . $bundle_tier] ?? [];
            $paired     = !empty($tier_cfg['paired_reports']) ? (array)$tier_cfg['paired_reports'] : [];
            $reports    = array_values(array_unique(array_merge([$txn['item_key']], $paired, $reports)));
            $meta['bundle_reports'] = $reports;
            $wpdb->update($table, ['metadata' => wp_json_encode($meta)], ['id' => $txn['id']]);
        }

        // Credit report tokens in MR_Tokens for ALL reports in the bundle
        if (class_exists('MR_Tokens')) {
            foreach ($reports as $r_slug) {
                MR_Tokens::credit_single_report_token($user_id, $r_slug, 1);
            }
        }

        // Credit bonus chat credits in Niyati_Wallet if report bundle included them
        if ($chat_cred > 0 && class_exists('Niyati_Wallet')) {
            $visitor_id = !empty($_COOKIE['niyati_visitor_id']) ? sanitize_text_field($_COOKIE['niyati_visitor_id']) : ('user_' . $user_id);
            Niyati_Wallet::add_credits(
                $visitor_id,
                $user_id,
                $chat_cred,
                floatval($txn['net_amount']),
                $payment_id,
                'checkout_bundle',
                'razorpay',
                'Checkout Order: ' . $txn['item_name'],
                0,
                ['invoice_number' => $inv_num, 'reports' => $reports]
            );
        }

        // Asynchronously dispatch PDF Invoice generation & Email delivery in server background
        if (class_exists('MR_Invoice')) {
            MR_Invoice::dispatch_async_invoice_email($txn['id']);
        }

        $inv_url = class_exists('MR_Invoice') ? MR_Invoice::get_invoice_url($txn) : home_url('/invoice/?id=' . urlencode($internal_id));

        $reports_data = [];
        foreach ($reports as $r_slug) {
            $reports_data[] = [
                'slug'      => $r_slug,
                'name'      => class_exists('MR_Bundles') ? MR_Bundles::get_report_name($r_slug) : $r_slug,
                'page_url'  => class_exists('MR_Bundles') ? MR_Bundles::get_report_page_url($r_slug) : home_url('/reports/' . $r_slug . '/'),
                'has_token' => true,
            ];
        }

        wp_send_json_success([
            'message'        => 'Order completed successfully! Invoice generated and tokens credited.',
            'invoice_number' => $inv_num,
            'invoice_url'    => $inv_url,
            'amount'         => $txn['net_amount'],
            'internal_id'    => $internal_id,
            'reports'        => $reports_data,
            'bundle_tier'    => $meta['bundle_tier'] ?? 'single'
        ]);
    }
}
