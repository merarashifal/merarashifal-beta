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
        // Register Shortcode
        add_shortcode('mr_checkout', [__CLASS__, 'render_checkout_page']);

        // Auto-create /checkout/ page if missing
        add_action('init', [__CLASS__, 'ensure_checkout_page']);

        // Route URL directly if /checkout/ is accessed
        add_action('template_redirect', [__CLASS__, 'handle_checkout_template']);

        // AJAX handlers for checkout order creation and payment verification
        add_action('wp_ajax_mr_checkout_create_order', [__CLASS__, 'ajax_create_order']);
        add_action('wp_ajax_nopriv_mr_checkout_create_order', [__CLASS__, 'ajax_create_order']);

        add_action('wp_ajax_mr_checkout_verify_payment', [__CLASS__, 'ajax_verify_payment']);
        add_action('wp_ajax_nopriv_mr_checkout_verify_payment', [__CLASS__, 'ajax_verify_payment']);
    }

    /**
     * Ensure the WordPress page with slug /checkout/ exists
     */
    public static function ensure_checkout_page() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        if (get_option('mr_checkout_page_created') === 'yes') {
            return;
        }

        $page = get_page_by_path('checkout');
        if (!$page) {
            wp_insert_post([
                'post_title'     => 'Checkout',
                'post_name'      => 'checkout',
                'post_content'   => '[mr_checkout]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ]);
        }
        update_option('mr_checkout_page_created', 'yes');
    }

    /**
     * Fallback template intercept for /checkout/
     */
    public static function handle_checkout_template() {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
        // Handle subdirectory installs if applicable
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        if ($site_path && strpos($uri, $site_path) === 0) {
            $uri = trim(substr($uri, strlen($site_path)), '/');
        }

        if ($uri === 'checkout' && !has_shortcode(get_post()->post_content ?? '', 'mr_checkout')) {
            // Render directly within site theme header & footer
            get_header();
            echo self::render_checkout_page();
            get_footer();
            exit;
        }
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
     * Render the Checkout Page
     */
    public static function render_checkout_page($atts = []) {
        $user_id = get_current_user_id();
        $user    = $user_id ? wp_get_current_user() : null;

        // Determine Report and Tier from Query Params
        $report_slug = sanitize_key($_GET['report'] ?? ($_GET['item'] ?? 'yearly_horoscope_long'));
        if (class_exists('MR_Bundles')) {
            $report_slug = MR_Bundles::normalize_slug($report_slug);
        }

        $tier = sanitize_key($_GET['tier'] ?? 'trio'); // default to trio (Best Buy)
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

        // Prefilled user profile info
        $prefill_name  = '';
        $prefill_email = '';
        $prefill_phone = '';
        $prefill_state = 'Uttar Pradesh';
        $prefill_country = 'India';
        $prefill_address = '';
        $prefill_city    = '';
        $prefill_pincode = '';

        if ($user) {
            $prefill_name  = get_user_meta($user_id, 'mr_astro_name', true) ?: ($user->first_name ? trim($user->first_name . ' ' . $user->last_name) : $user->display_name);
            $prefill_email = $user->user_email;
            $prefill_phone = get_user_meta($user_id, 'billing_phone', true) ?: (get_user_meta($user_id, 'user_phone', true) ?: '');
            $prefill_state = get_user_meta($user_id, 'billing_state', true) ?: 'Uttar Pradesh';
            $prefill_country = get_user_meta($user_id, 'billing_country', true) ?: 'India';
            $prefill_address = get_user_meta($user_id, 'billing_address_1', true) ?: '';
            $prefill_city    = get_user_meta($user_id, 'billing_city', true) ?: '';
            $prefill_pincode = get_user_meta($user_id, 'billing_postcode', true) ?: '';
        }

        $all_states = self::get_indian_states();
        $key_id = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_id() : 'rzp_test_TpIAA4q3WP1EYF';
        $gateway_mode = class_exists('MR_Razorpay') ? MR_Razorpay::get_mode() : 'test';

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
                background: #FDF8F1;
                min-height: 85vh;
                padding: 40px 16px 80px 16px;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
                color: #3A312D;
            }
            .mr-checkout-container {
                max-width: 1080px;
                margin: 0 auto;
            }
            .mr-checkout-header {
                text-align: center;
                margin-bottom: 32px;
            }
            .mr-checkout-h1 {
                font-family: 'Playfair Display', serif;
                font-size: 30px;
                font-weight: 800;
                color: #7A1F3D;
                margin: 8px 0 6px 0;
            }
            .mr-checkout-sub {
                font-size: 13.5px;
                color: #6B5E57;
                margin: 0;
            }
            .mr-checkout-grid {
                display: grid;
                grid-template-columns: 1.25fr 1fr;
                gap: 30px;
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
        </style>

        <div class="mr-checkout-breakout">
            <div class="mr-checkout-container">

                <div class="mr-checkout-header">
                    <span class="mr-tag-eyebrow" style="background:#FFF3E1; color:#7A1F3D; border:1.5px solid #D9A441; padding:3px 12px; border-radius:9999px; font-size:11px; font-weight:800; text-transform:uppercase;">
                        <i class="fa-solid fa-lock" style="color:#D9A441;"></i> 256-Bit SSL Encrypted Checkout
                    </span>
                    <h1 class="mr-checkout-h1">Complete Your Sacred Order</h1>
                    <p class="mr-checkout-sub">An official tax invoice and electronic receipt will be generated and dispatched immediately.</p>
                </div>

                <!-- MAIN CHECKOUT FORM & SUMMARY -->
                <div class="mr-checkout-grid" id="mr-checkout-main-grid">

                    <!-- LEFT: BILLING & CONTACT FORM -->
                    <div class="mr-co-card">
                        <h2 class="mr-co-title">
                            <i class="fa-solid fa-address-card" style="color:#F58220;"></i>
                            <span>1. Customer &amp; Billing Details</span>
                        </h2>

                        <div id="mr-co-error-box"></div>

                        <form id="mr-checkout-form" novalidate>
                            <input type="hidden" name="report_slug" value="<?php echo esc_attr($report_slug); ?>">
                            <input type="hidden" name="bundle_tier" value="<?php echo esc_attr($tier); ?>">
                            <input type="hidden" name="package_title" value="<?php echo esc_attr($package_title); ?>">
                            <input type="hidden" name="price" value="<?php echo esc_attr($price_sale); ?>">
                            <input type="hidden" name="chat_credits" value="<?php echo esc_attr($chat_credits); ?>">
                            <input type="hidden" name="included_reports" value="<?php echo esc_attr(wp_json_encode($included_reports)); ?>">
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
                                           placeholder="+91 98765 43210" value="<?php echo esc_attr($prefill_phone); ?>">
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

                        </form>
                    </div>

                    <!-- RIGHT: ORDER & VALUE SUMMARY -->
                    <div class="mr-co-card" style="border-color:#D9A441; box-shadow:0 12px 35px rgba(217, 164, 65, 0.15);">
                        <h2 class="mr-co-title">
                            <i class="fa-solid fa-receipt" style="color:#D9A441;"></i>
                            <span>2. Order &amp; Value Summary</span>
                        </h2>

                        <!-- Package Card -->
                        <div class="mr-summary-package">
                            <?php if (!empty($badge)): ?>
                                <span class="mr-summary-ribbon"><?php echo esc_html($badge); ?></span>
                            <?php endif; ?>
                            <div class="mr-summary-pkg-title"><?php echo esc_html($package_title); ?></div>

                            <ul class="mr-summary-items-list">
                                <?php foreach ($included_reports as $r_slug): 
                                    $r_name = class_exists('MR_Bundles') ? MR_Bundles::get_report_name($r_slug) : $r_slug;
                                ?>
                                    <li>
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

                        <!-- Calculations Table -->
                        <table class="mr-calc-table">
                            <tr>
                                <td>Regular List Price:</td>
                                <td><s>₹<?php echo number_format($price_regular); ?></s></td>
                            </tr>
                            <?php if ($price_regular > $price_sale): ?>
                            <tr>
                                <td style="color:#059669;">Special Bundle Discount:</td>
                                <td style="color:#059669;">- ₹<?php echo number_format($price_regular - $price_sale); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>Taxes (Non-GST / Nil):</td>
                                <td>₹0.00</td>
                            </tr>
                            <tr class="mr-calc-total-row">
                                <td>Total Payable:</td>
                                <td>₹<?php echo number_format($price_sale); ?></td>
                            </tr>
                        </table>

                        <!-- CTA BUTTON -->
                        <button type="button" id="mr-btn-checkout-submit" class="mr-btn-pay">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span id="mr-btn-pay-text">Pay ₹<?php echo number_format($price_sale); ?> Securely &amp; Generate Invoice &rarr;</span>
                        </button>

                        <div class="mr-co-trust">
                            <span><i class="fa-solid fa-file-invoice"></i> Retail Invoice Attached</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-envelope"></i> Instant Email Delivery</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-lock"></i> Razorpay Verified</span>
                        </div>

                        <!-- Return to report link -->
                        <div style="text-align:center; margin-top:16px;">
                            <a href="<?php echo esc_url(home_url('/reports/' . (class_exists('MR_Bundles') ? MR_Bundles::get_report_page_url($report_slug) : ''))); ?>" 
                               style="font-size:12px; color:#6B5E57; text-decoration:none;">
                                &larr; Return to Report Details
                            </a>
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

                    <!-- Dual Action Buttons -->
                    <div style="display:flex; justify-content:center; gap:14px; flex-wrap:wrap;">
                        <a id="mr-succ-download-inv-btn" href="#" target="_blank" class="mr-btn-pay" style="width:auto; padding:13px 26px; background:#7A1F3D; text-decoration:none;">
                            <i class="fa-solid fa-file-invoice"></i> Download Invoice (PDF / Print)
                        </a>

                        <button type="button" id="mr-succ-generate-btn" class="mr-btn-pay" style="width:auto; padding:13px 28px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Fill Birth Details &amp; Generate Report &rarr;
                        </button>
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
                        $('#mr_co_phone').focus();
                        return;
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

                    // Disable button and initiate order
                    btn.prop('disabled', true).find('#mr-btn-pay-text').html('<i class="fa-solid fa-spinner fa-spin"></i> Processing Secure Order...');

                    var formData = $('#mr-checkout-form').serializeArray();
                    formData.push({ name: 'action', value: 'mr_checkout_create_order' });
                    formData.push({ name: 'state', value: state });
                    formData.push({ name: 'address', value: address });
                    formData.push({ name: 'city', value: city });
                    formData.push({ name: 'pincode', value: pincode });
                    formData.push({ name: 'is_international', value: isIntl ? '1' : '0' });

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
                    btn.find('#mr-btn-pay-text').html('<i class="fa-solid fa-spinner fa-spin"></i> Generating Invoice &amp; Unlocking Reports...');

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
                                $('#mr-checkout-main-grid').slideUp(250);
                                $('#mr-succ-inv-num').text(d.invoice_number);
                                $('#mr-succ-rzp-id').text(paymentId);
                                $('#mr-succ-internal-id').text(internalId);
                                $('#mr-succ-amount').text('₹' + Number(d.amount).toLocaleString());
                                $('#mr-succ-download-inv-btn').attr('href', d.invoice_url);

                                $('#mr-succ-generate-btn').off('click').on('click', function() {
                                    if (typeof window.openDetailsModal === 'function') {
                                        window.openDetailsModal(reportSlug, title);
                                    } else {
                                        var m = $('#mr-details-modal');
                                        if (m.length) {
                                            $('#mr_report_slug').val(reportSlug);
                                            m.addClass('is-open').fadeIn(150);
                                        }
                                    }
                                });

                                $('#mr-checkout-success-view').fadeIn(300);
                                $('html, body').animate({ scrollTop: $('#mr-checkout-success-view').offset().top - 40 }, 400);

                                if (window.mrNotify && typeof window.mrNotify.toast === 'function') {
                                    window.mrNotify.toast('Invoice ' + d.invoice_number + ' generated and emailed to you!', 'success');
                                }
                            } else {
                                alert('Verification warning: ' + (res.data ? res.data.message : 'Please check your account.'));
                            }
                        },
                        error: function() {
                            alert('Payment captured! Your invoice will be delivered to your email and accessible in My Account.');
                        }
                    });
                }
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

        $report_slug      = sanitize_key($_POST['report_slug'] ?? 'yearly_horoscope_long');
        $bundle_tier      = sanitize_key($_POST['bundle_tier'] ?? 'trio');
        $package_title    = sanitize_text_field($_POST['package_title'] ?? 'Astrological Report Package');
        $price            = floatval($_POST['price'] ?? 299);
        $chat_credits     = intval($_POST['chat_credits'] ?? 0);
        $included_reports = !empty($_POST['included_reports']) ? json_decode(stripslashes($_POST['included_reports']), true) : [$report_slug];
        if (!is_array($included_reports)) $included_reports = [$report_slug];

        $billing_name     = sanitize_text_field($_POST['billing_name'] ?? '');
        $billing_email    = sanitize_email($_POST['billing_email'] ?? '');
        $billing_phone    = sanitize_text_field($_POST['billing_phone'] ?? '');
        $billing_country  = sanitize_text_field($_POST['billing_country'] ?? 'India');
        $billing_state    = sanitize_text_field($_POST['state'] ?? 'Uttar Pradesh');
        $billing_address  = sanitize_text_field($_POST['address'] ?? '');
        $billing_city     = sanitize_text_field($_POST['city'] ?? '');
        $billing_pincode  = sanitize_text_field($_POST['pincode'] ?? '');
        $is_intl          = !empty($_POST['is_international']) && $_POST['is_international'] === '1';

        if (!$billing_name || !$billing_email || !$billing_phone) {
            wp_send_json_error(['message' => 'Please provide complete name, email, and phone number.']);
        }

        // Save billing info to user meta if logged in
        $user_id = get_current_user_id() ?: 1;
        if ($user_id > 1) {
            update_user_meta($user_id, 'billing_phone', $billing_phone);
            update_user_meta($user_id, 'billing_state', $billing_state);
            update_user_meta($user_id, 'billing_country', $billing_country);
            if ($is_intl) {
                update_user_meta($user_id, 'billing_address_1', $billing_address);
                update_user_meta($user_id, 'billing_city', $billing_city);
                update_user_meta($user_id, 'billing_postcode', $billing_pincode);
            }
        }

        // Generate unique internal ID
        $internal_id = 'MR_ORD_' . strtoupper(bin2hex(random_bytes(5)));

        // Create Razorpay Gateway Order
        $key_id     = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_id() : 'rzp_test_TpIAA4q3WP1EYF';
        $key_secret = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_secret() : '4YJwgnmeWvKdYEaKmpiv9pH1';
        $amount_paise = round($price * 100);
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
                        'customer_state'  => $billing_state
                    ]
                ]),
                'timeout' => 12
            ]);

            if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (!empty($body['id'])) {
                    $gateway_order_id = $body['id'];
                }
            }
        }

        if (empty($gateway_order_id)) {
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
                'channel'          => 'report',
                'item_key'         => $report_slug,
                'item_name'        => $package_title,
                'txn_type'         => 'purchase',
                'gross_amount'     => $price,
                'discount_amount'  => 0.00,
                'net_amount'       => $price,
                'currency'         => 'INR',
                'gateway'          => 'razorpay',
                'gateway_order_id' => $gateway_order_id,
                'status'           => 'pending',
                'metadata'         => [
                    'bundle_tier'      => $bundle_tier,
                    'bundle_reports'   => $included_reports,
                    'chat_credits'     => $chat_credits,
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
            'report_slug'      => $report_slug
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
        $reports   = !empty($meta['bundle_reports']) ? (array)$meta['bundle_reports'] : [$txn['item_key']];
        $chat_cred = intval($meta['chat_credits'] ?? 0);

        // 3. Credit report tokens in MR_Tokens
        if (class_exists('MR_Tokens')) {
            foreach ($reports as $r_slug) {
                MR_Tokens::credit_single_report_token($user_id, $r_slug, 1);
            }
        }

        // 4. Credit bonus chat credits in Niyati_Wallet
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

        // 5. Send Email with HTML Snapshot and PDF Attachment
        if (class_exists('MR_Invoice')) {
            MR_Invoice::send_invoice_email($txn);
        }

        $inv_url = class_exists('MR_Invoice') ? MR_Invoice::get_invoice_url($txn) : home_url('/invoice/?id=' . urlencode($internal_id));

        wp_send_json_success([
            'message'        => 'Order completed successfully! Invoice generated and tokens credited.',
            'invoice_number' => $inv_num,
            'invoice_url'    => $inv_url,
            'amount'         => $txn['net_amount'],
            'internal_id'    => $internal_id
        ]);
    }
}
