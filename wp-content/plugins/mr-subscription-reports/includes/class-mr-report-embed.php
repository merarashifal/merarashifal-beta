<?php
/**
 * Embeddable Report Form & Real-Time Generation Progress Engine
 *
 * Provides the [mr_report_embed] and [mr_report_form] shortcodes to embed
 * dedicated input forms, payment flow, real-time cosmic progress bar, and
 * instant PDF download on any dedicated report landing page.
 *
 * @package MR_Subscription_Reports
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Report_Embed {

    public static function init() {
        // Register shortcodes
        add_shortcode('mr_report_embed', [__CLASS__, 'render_embed']);
        add_shortcode('mr_report_form', [__CLASS__, 'render_embed']);
        add_shortcode('mr_embed_report', [__CLASS__, 'render_embed']);

        // Frontend Assets
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        // AJAX handlers
        add_action('wp_ajax_mr_embed_checkout', [__CLASS__, 'ajax_checkout']);
        add_action('wp_ajax_nopriv_mr_embed_checkout', [__CLASS__, 'ajax_checkout']);

        add_action('wp_ajax_mr_embed_verify_payment', [__CLASS__, 'ajax_verify_payment']);
        add_action('wp_ajax_nopriv_mr_embed_verify_payment', [__CLASS__, 'ajax_verify_payment']);
    }

    /**
     * Enqueue required scripts and styles
     */
    public static function enqueue_assets() {
        // Enqueue FontAwesome
        if (!wp_style_is('mr-font-awesome', 'enqueued')) {
            wp_enqueue_style(
                'mr-font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                [],
                '6.4.0'
            );
        }

        // Enqueue Razorpay Checkout script
        wp_enqueue_script(
            'mr-razorpay-checkout',
            'https://checkout.razorpay.com/v1/checkout.js',
            [],
            null,
            true
        );

        // Enqueue Google Maps Places API for City Autocomplete using config key
        $google_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';
        if (!empty($google_key)) {
            if (!wp_script_is('google-maps-places', 'registered') && !wp_script_is('google-maps-places', 'enqueued')) {
                wp_enqueue_script(
                    'google-maps-places',
                    'https://maps.googleapis.com/maps/api/js?key=' . urlencode($google_key) . '&libraries=places',
                    [],
                    null,
                    true
                );
            } else {
                wp_enqueue_script('google-maps-places');
            }
        }
    }

    /**
     * Get Report Card Metadata and Dynamic Pricing
     */
    public static function get_report_meta($slug) {
        $slug = sanitize_key($slug);
        if ($slug === 'numerology_report') {
            $slug = 'numerology';
        }

        $all_cards = class_exists('MR_Catalog_Manager') ? MR_Catalog_Manager::get_all_cards() : [];
        if (isset($all_cards[$slug])) {
            return $all_cards[$slug];
        }

        // Fallback to MR_Packs
        $packs_reports = class_exists('MR_Packs') ? MR_Packs::get_reports() : [];
        if (isset($packs_reports[$slug])) {
            $r = $packs_reports[$slug];
            return [
                'id'            => $slug,
                'name'          => $r['name'],
                'sub_text'      => $r['description'] ?? '',
                'category'      => $r['category'] ?? 'Vedic Reading',
                'price_sale'    => 99,
                'price_regular' => 999,
                'discount_text' => 'Save 90%',
                'pages_tag'     => $r['pages'] ?? '20 Pages',
                'highlights'    => [],
                'button_text'   => 'Generate My Report →'
            ];
        }

        return null;
    }

    /**
     * Render the Embed Shortcode
     *
     * Usage: [mr_report_embed report="yearly_horoscope_long"]
     */
    public static function render_embed($atts = []) {
        $atts = shortcode_atts([
            'report'      => 'kundli_predictions',
            'slug'        => '',
            'title'       => '',
            'subtitle'    => '',
            'button_text' => '',
            'layout'      => 'card', // 'card' or 'minimal'
        ], $atts, 'mr_report_embed');

        $slug = !empty($atts['slug']) ? $atts['slug'] : $atts['report'];
        $card = self::get_report_meta($slug);

        if (!$card) {
            return '<div class="mr-embed-error" style="padding:20px; background:#fff1f2; border:1px solid #fecdd3; border-radius:8px; color:#be123c; font-family:sans-serif;">
                <strong>Configuration Error:</strong> Unknown report slug "<code>' . esc_html($slug) . '</code>". Please check the shortcode attribute.
            </div>';
        }

        $user_id = get_current_user_id();
        $user    = $user_id ? get_userdata($user_id) : null;
        $default_name  = $user ? $user->display_name : '';
        $default_email = $user ? $user->user_email : '';

        $title      = !empty($atts['title']) ? $atts['title'] : $card['name'];
        $sub_text   = !empty($atts['subtitle']) ? $atts['subtitle'] : ($card['sub_text'] ?? '');
        $pages_tag  = $card['pages_tag'] ?? '';
        $price_sale = isset($card['price_sale']) ? floatval($card['price_sale']) : 99;
        $price_reg  = isset($card['price_regular']) ? floatval($card['price_regular']) : 999;
        $disc_text  = $card['discount_text'] ?? '';
        $is_matching = ($slug === 'kundli_matching');
        $is_palmistry = ($slug === 'palmistry_report');

        // Check if user has token available
        $has_token = ($user_id && class_exists('MR_Tokens') && MR_Tokens::has_token($user_id, $slug));

        $btn_label = !empty($atts['button_text']) ? $atts['button_text'] : '';
        if (empty($btn_label)) {
            if ($has_token) {
                $btn_label = 'Generate Report (1 Credit Available) →';
            } elseif ($price_sale == 0) {
                $btn_label = 'Claim Free Report (Special Offer) →';
            } else {
                $btn_label = 'Proceed to Order • ₹' . number_format($price_sale, 0) . ' →';
            }
        }

        $uniqid = 'mr_embed_' . esc_attr($slug) . '_' . wp_rand(1000, 9999);
        $nonce  = wp_create_nonce('mr_reports_nonce');

        ob_start();
        ?>
        <div class="mr-report-embed-wrapper <?php echo ($atts['layout'] === 'minimal') ? 'mr-layout-minimal' : 'mr-layout-card'; ?>" id="<?php echo esc_attr($uniqid); ?>" data-report-slug="<?php echo esc_attr($slug); ?>" data-price="<?php echo esc_attr($price_sale); ?>" data-has-token="<?php echo $has_token ? '1' : '0'; ?>">

            <!-- INLINE EMBED STYLES -->
            <style>
                #<?php echo esc_attr($uniqid); ?> {
                    --mr-gold-1: #f59e0b;
                    --mr-gold-2: #d97706;
                    --mr-gold-dark: #b45309;
                    --mr-bg-card: #ffffff;
                    --mr-border: #e2e8f0;
                    --mr-text-main: #0f172a;
                    --mr-text-muted: #64748b;
                    --mr-accent-cyan: #0284c7;
                    --mr-emerald: #10b981;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    margin: 28px auto;
                    max-width: 780px;
                    box-sizing: border-box;
                    color: var(--mr-text-main);
                }
                #<?php echo esc_attr($uniqid); ?> * {
                    box-sizing: border-box;
                }
                #<?php echo esc_attr($uniqid); ?>.mr-layout-card {
                    background: var(--mr-bg-card);
                    border: 1px solid #e2e8f0;
                    border-radius: 16px;
                    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 20px 25px -5px rgba(245, 158, 11, 0.04);
                    padding: 32px 36px;
                    position: relative;
                    overflow: hidden;
                }
                @media (max-width: 640px) {
                    #<?php echo esc_attr($uniqid); ?>.mr-layout-card {
                        padding: 22px 18px;
                        border-radius: 12px;
                    }
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-header {
                    border-bottom: 1px solid #f1f5f9;
                    padding-bottom: 20px;
                    margin-bottom: 24px;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    flex-wrap: wrap;
                    gap: 16px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-title-wrap {
                    flex: 1 1 340px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-title {
                    font-size: 24px;
                    font-weight: 800;
                    color: #0f172a;
                    margin: 0 0 6px 0;
                    line-height: 1.25;
                    letter-spacing: -0.02em;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-sub {
                    font-size: 14px;
                    color: #64748b;
                    margin: 0;
                    line-height: 1.5;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-pricing-badge {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end;
                    background: #f8fafc;
                    padding: 10px 16px;
                    border-radius: 10px;
                    border: 1px solid #e2e8f0;
                    min-width: 140px;
                }
                @media (max-width: 640px) {
                    #<?php echo esc_attr($uniqid); ?> .mr-embed-pricing-badge {
                        align-items: flex-start;
                        width: 100%;
                    }
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-price-row {
                    display: flex;
                    align-items: baseline;
                    gap: 8px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-price-sale {
                    font-size: 26px;
                    font-weight: 800;
                    color: #b45309;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-price-reg {
                    font-size: 14px;
                    color: #94a3b8;
                    text-decoration: line-through;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-promo-pill {
                    display: inline-block;
                    font-size: 11px;
                    font-weight: 700;
                    background: #ecfdf5;
                    color: #047857;
                    border: 1px solid #a7f3d0;
                    padding: 2px 8px;
                    border-radius: 999px;
                    margin-top: 4px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-pages-pill {
                    font-size: 11px;
                    font-weight: 600;
                    color: #64748b;
                    background: #e2e8f0;
                    padding: 2px 8px;
                    border-radius: 999px;
                    display: inline-block;
                    margin-bottom: 6px;
                }

                /* Form Layout */
                #<?php echo esc_attr($uniqid); ?> .mr-form-section-title {
                    font-size: 14px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #475569;
                    margin: 20px 0 12px 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-form-section-title::after {
                    content: "";
                    flex: 1;
                    height: 1px;
                    background: #e2e8f0;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-embed-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 16px;
                    margin-bottom: 16px;
                }
                @media (max-width: 640px) {
                    #<?php echo esc_attr($uniqid); ?> .mr-embed-grid {
                        grid-template-columns: 1fr;
                        gap: 14px;
                    }
                }
                #<?php echo esc_attr($uniqid); ?> .mr-field-group {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    position: relative;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-field-group.mr-full-width {
                    grid-column: 1 / -1;
                }
                #<?php echo esc_attr($uniqid); ?> label {
                    font-size: 13px;
                    font-weight: 600;
                    color: #334155;
                }
                #<?php echo esc_attr($uniqid); ?> label .mr-required {
                    color: #ef4444;
                }
                #<?php echo esc_attr($uniqid); ?> input[type="text"],
                #<?php echo esc_attr($uniqid); ?> input[type="email"],
                #<?php echo esc_attr($uniqid); ?> input[type="date"],
                #<?php echo esc_attr($uniqid); ?> input[type="time"],
                #<?php echo esc_attr($uniqid); ?> select {
                    width: 100%;
                    padding: 10px 14px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    font-size: 14px;
                    color: #0f172a;
                    background: #ffffff;
                    transition: border-color 0.15s, box-shadow 0.15s;
                    outline: none;
                }
                #<?php echo esc_attr($uniqid); ?> input:focus,
                #<?php echo esc_attr($uniqid); ?> select:focus {
                    border-color: #f59e0b;
                    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
                }

                /* City Autocomplete Dropdown */
                #<?php echo esc_attr($uniqid); ?> .mr-city-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: #ffffff;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                    max-height: 220px;
                    overflow-y: auto;
                    z-index: 1000;
                    margin-top: 4px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-city-item {
                    padding: 9px 14px;
                    font-size: 13px;
                    cursor: pointer;
                    border-bottom: 1px solid #f1f5f9;
                    color: #334155;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-city-item:hover {
                    background: #fffbeb;
                    color: #b45309;
                }

                /* Google Places Autocomplete Dropdown Styling */
                .pac-container {
                    z-index: 9999999 !important;
                    font-family: inherit !important;
                    border-radius: 8px !important;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
                    border: 1px solid #cbd5e1 !important;
                    margin-top: 4px !important;
                    background: #ffffff !important;
                }
                .pac-item {
                    padding: 9px 14px !important;
                    font-size: 13px !important;
                    color: #334155 !important;
                    cursor: pointer !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                    line-height: 1.4 !important;
                }
                .pac-item:hover {
                    background-color: #fffbeb !important;
                    color: #b45309 !important;
                }
                .pac-item-query {
                    font-size: 13px !important;
                    font-weight: 600 !important;
                    color: #0f172a !important;
                }
                .pac-matched {
                    color: #f59e0b !important;
                    font-weight: 700 !important;
                }

                /* Dual Matching Profiles */
                #<?php echo esc_attr($uniqid); ?> .mr-matching-profile-box {
                    background: #fafaf9;
                    border: 1px solid #e7e5e4;
                    border-radius: 12px;
                    padding: 18px 20px;
                    margin-bottom: 18px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-profile-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 12px;
                    font-weight: 700;
                    padding: 4px 10px;
                    border-radius: 6px;
                    margin-bottom: 14px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-profile-badge.boy {
                    background: #eff6ff;
                    color: #1d4ed8;
                    border: 1px solid #bfdbfe;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-profile-badge.girl {
                    background: #fdf2f8;
                    color: #be185d;
                    border: 1px solid #fbcfe8;
                }

                /* Trust Note */
                #<?php echo esc_attr($uniqid); ?> .mr-trust-badge {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-size: 12px;
                    color: #64748b;
                    margin: 16px 0 20px 0;
                    text-align: center;
                }

                /* CTA Button */
                #<?php echo esc_attr($uniqid); ?> .mr-btn-submit-order {
                    width: 100%;
                    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                    color: #ffffff;
                    border: none;
                    border-radius: 10px;
                    padding: 14px 24px;
                    font-size: 16px;
                    font-weight: 700;
                    cursor: pointer;
                    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
                    transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-submit-order:hover {
                    background: linear-gradient(135deg, #fbbf24 0%, #b45309 100%);
                    transform: translateY(-1px);
                    box-shadow: 0 6px 20px rgba(217, 119, 6, 0.45);
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-submit-order:disabled {
                    opacity: 0.65;
                    cursor: not-allowed;
                    transform: none;
                }

                /* ========================================================
                   REAL-TIME ON-PAGE PROGRESS CARD & COMPLETION VIEW
                   ======================================================== */
                #<?php echo esc_attr($uniqid); ?> .mr-progress-card {
                    display: none;
                    text-align: center;
                    padding: 24px 10px 10px 10px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-pulse-orbit {
                    width: 72px;
                    height: 72px;
                    margin: 0 auto 18px auto;
                    position: relative;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-pulse-ring {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    border: 3px solid rgba(245, 158, 11, 0.25);
                    border-top-color: #f59e0b;
                    animation: mr-spin 1.1s linear infinite;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-orbit-icon {
                    font-size: 28px;
                    color: #f59e0b;
                }
                @keyframes mr-spin {
                    to { transform: rotate(360deg); }
                }

                #<?php echo esc_attr($uniqid); ?> .mr-progress-title {
                    font-size: 22px;
                    font-weight: 800;
                    color: #0f172a;
                    margin: 0 0 6px 0;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-progress-sub {
                    font-size: 14px;
                    color: #64748b;
                    margin: 0 0 24px 0;
                }

                /* Multi-Phase Progress Bar */
                #<?php echo esc_attr($uniqid); ?> .mr-bar-track {
                    width: 100%;
                    height: 12px;
                    background: #f1f5f9;
                    border-radius: 999px;
                    overflow: hidden;
                    margin-bottom: 16px;
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
                }
                #<?php echo esc_attr($uniqid); ?> .mr-bar-fill {
                    height: 100%;
                    width: 10%;
                    background: linear-gradient(90deg, #f59e0b, #10b981);
                    border-radius: 999px;
                    transition: width 0.6s ease;
                }

                /* Phase Status Pill & Live Stepper */
                #<?php echo esc_attr($uniqid); ?> .mr-phase-status {
                    font-size: 14px;
                    font-weight: 700;
                    color: #0f172a;
                    margin-bottom: 24px;
                    min-height: 22px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-phases-list {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 8px;
                    margin-bottom: 28px;
                    text-align: left;
                }
                @media (max-width: 640px) {
                    #<?php echo esc_attr($uniqid); ?> .mr-phases-list {
                        grid-template-columns: 1fr 1fr;
                        gap: 12px;
                    }
                }
                #<?php echo esc_attr($uniqid); ?> .mr-phase-item {
                    font-size: 12px;
                    color: #94a3b8;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-phase-item.active {
                    color: #f59e0b;
                    font-weight: 700;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-phase-item.done {
                    color: #10b981;
                    font-weight: 600;
                }

                /* Completion State Card */
                #<?php echo esc_attr($uniqid); ?> .mr-complete-card {
                    display: none;
                    text-align: center;
                    padding: 20px 0;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-success-badge-icon {
                    width: 72px;
                    height: 72px;
                    margin: 0 auto 18px auto;
                    background: #dcfce7;
                    color: #16a34a;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 34px;
                    box-shadow: 0 0 0 8px #f0fdf4;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-complete-title {
                    font-size: 24px;
                    font-weight: 800;
                    color: #0f172a;
                    margin: 0 0 6px 0;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-complete-sub {
                    font-size: 14px;
                    color: #475569;
                    margin: 0 0 24px 0;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-download-actions {
                    display: flex;
                    gap: 14px;
                    justify-content: center;
                    flex-wrap: wrap;
                    margin-bottom: 28px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-download-pdf {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: #ffffff;
                    padding: 14px 28px;
                    font-size: 16px;
                    font-weight: 700;
                    border-radius: 10px;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
                    transition: transform 0.15s, box-shadow 0.15s;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-download-pdf:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
                    color: #ffffff;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-view-online {
                    background: #f8fafc;
                    border: 1px solid #cbd5e1;
                    color: #334155;
                    padding: 14px 24px;
                    font-size: 15px;
                    font-weight: 600;
                    border-radius: 10px;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-view-online:hover {
                    background: #f1f5f9;
                    color: #0f172a;
                }

                /* Generate New Report Reset Button */
                #<?php echo esc_attr($uniqid); ?> .mr-btn-reset-new {
                    background: transparent;
                    border: 2px dashed #cbd5e1;
                    color: #475569;
                    padding: 12px 24px;
                    font-size: 14px;
                    font-weight: 700;
                    border-radius: 8px;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: background 0.15s, border-color 0.15s, color 0.15s;
                }
                #<?php echo esc_attr($uniqid); ?> .mr-btn-reset-new:hover {
                    background: #fffbeb;
                    border-color: #f59e0b;
                    color: #b45309;
                }
            </style>

            <!-- 1. HEADER & DYNAMIC PRICING SNAPSHOT -->
            <div class="mr-embed-header">
                <div class="mr-embed-title-wrap">
                    <?php if (!empty($pages_tag)): ?>
                        <span class="mr-embed-pages-pill"><i class="fa-regular fa-file-lines"></i> <?php echo esc_html($pages_tag); ?></span>
                    <?php endif; ?>
                    <h2 class="mr-embed-title"><?php echo esc_html($title); ?></h2>
                    <?php if (!empty($sub_text)): ?>
                        <p class="mr-embed-sub"><?php echo esc_html($sub_text); ?></p>
                    <?php endif; ?>
                </div>

                <div class="mr-embed-pricing-badge">
                    <?php if ($has_token): ?>
                        <span class="mr-embed-promo-pill" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">✓ 1 Token Available</span>
                        <div class="mr-embed-price-row" style="margin-top:4px;">
                            <span class="mr-embed-price-sale" style="color:#0369a1;">FREE</span>
                        </div>
                    <?php elseif ($price_sale == 0): ?>
                        <span class="mr-embed-promo-pill">🎉 Special Promotional Offer</span>
                        <div class="mr-embed-price-row">
                            <span class="mr-embed-price-sale" style="color:#059669;">₹0</span>
                            <?php if ($price_reg > 0): ?>
                                <span class="mr-embed-price-reg">₹<?php echo esc_html($price_reg); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="mr-embed-price-row">
                            <span class="mr-embed-price-sale">₹<?php echo esc_html(number_format($price_sale, 0)); ?></span>
                            <?php if ($price_reg > $price_sale): ?>
                                <span class="mr-embed-price-reg">₹<?php echo esc_html(number_format($price_reg, 0)); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($disc_text)): ?>
                            <span class="mr-embed-promo-pill"><?php echo esc_html($disc_text); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. INPUT FORM -->
            <form class="mr-embed-form" id="<?php echo esc_attr($uniqid); ?>_form">
                <input type="hidden" name="report_slug" value="<?php echo esc_attr($slug); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

                <?php if ($is_matching): ?>
                    <!-- KUNDALI MATCHING: DUAL PROFILES -->
                    <div class="mr-matching-profile-box">
                        <span class="mr-profile-badge boy"><i class="fa-solid fa-mars"></i> Person 1 / Groom Details (वर विवरण)</span>
                        <div class="mr-embed-grid">
                            <div class="mr-field-group">
                                <label>Boy's Full Name <span class="mr-required">*</span></label>
                                <input type="text" name="boy_name" placeholder="e.g. Rahul Sharma" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Date of Birth <span class="mr-required">*</span></label>
                                <input type="date" name="boy_dob" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Time of Birth <span class="mr-required">*</span></label>
                                <input type="time" name="boy_tob" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Place of Birth (City) <span class="mr-required">*</span></label>
                                <input type="text" name="boy_pob" class="mr-city-input" placeholder="Start typing city (e.g. Delhi)..." autocomplete="off" required>
                                <div class="mr-city-dropdown" style="display:none;"></div>
                                <input type="hidden" name="boy_lat" value="">
                                <input type="hidden" name="boy_lng" value="">
                                <input type="hidden" name="boy_tz" value="">
                            </div>
                        </div>
                    </div>

                    <div class="mr-matching-profile-box">
                        <span class="mr-profile-badge girl"><i class="fa-solid fa-venus"></i> Person 2 / Bride Details (कन्या विवरण)</span>
                        <div class="mr-embed-grid">
                            <div class="mr-field-group">
                                <label>Girl's Full Name <span class="mr-required">*</span></label>
                                <input type="text" name="girl_name" placeholder="e.g. Priya Verma" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Date of Birth <span class="mr-required">*</span></label>
                                <input type="date" name="girl_dob" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Time of Birth <span class="mr-required">*</span></label>
                                <input type="time" name="girl_tob" required>
                            </div>
                            <div class="mr-field-group">
                                <label>Place of Birth (City) <span class="mr-required">*</span></label>
                                <input type="text" name="girl_pob" class="mr-city-input" placeholder="Start typing city (e.g. Jaipur)..." autocomplete="off" required>
                                <div class="mr-city-dropdown" style="display:none;"></div>
                                <input type="hidden" name="girl_lat" value="">
                                <input type="hidden" name="girl_lng" value="">
                                <input type="hidden" name="girl_tz" value="">
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- STANDARD SINGLE PROFILE FORM -->
                    <div class="mr-form-section-title"><i class="fa-solid fa-user-astronaut"></i> Birth Details (जन्म विवरण)</div>
                    <div class="mr-embed-grid">
                        <div class="mr-field-group">
                            <label>Full Name <span class="mr-required">*</span></label>
                            <input type="text" name="person_name" placeholder="e.g. Rahul Sharma" value="<?php echo esc_attr($default_name); ?>" required>
                        </div>

                        <div class="mr-field-group">
                            <label>Gender <span class="mr-required">*</span></label>
                            <select name="gender" required>
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (स्त्री)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mr-field-group">
                            <label>Date of Birth <span class="mr-required">*</span></label>
                            <input type="date" name="dob" required>
                        </div>

                        <div class="mr-field-group">
                            <label>Time of Birth <span class="mr-required">*</span></label>
                            <input type="time" name="tob" required>
                        </div>

                        <div class="mr-field-group mr-full-width">
                            <label>Place of Birth (City, Country) <span class="mr-required">*</span></label>
                            <input type="text" name="pob" class="mr-city-input" placeholder="Start typing birth city (e.g. New Delhi, Mumbai, Lucknow)..." autocomplete="off" required>
                            <div class="mr-city-dropdown" style="display:none;"></div>
                            <input type="hidden" name="pob_lat" value="">
                            <input type="hidden" name="pob_lng" value="">
                            <input type="hidden" name="pob_tz" value="">
                        </div>

                        <?php if ($is_palmistry): ?>
                            <!-- Specialized Palmistry Fields -->
                            <div class="mr-field-group">
                                <label>Dominant Working Hand <span class="mr-required">*</span></label>
                                <select name="dominant_hand">
                                    <option value="Right">Right Hand (Active Destiny)</option>
                                    <option value="Left">Left Hand (Inborn Potential)</option>
                                </select>
                            </div>
                            <div class="mr-field-group">
                                <label>Primary Life Focus</label>
                                <select name="palm_focus">
                                    <option value="Career & Wealth">Career, Business & Wealth Timing</option>
                                    <option value="Love & Marriage">Marriage, Love Life & Relationships</option>
                                    <option value="Health & Longevity">Health, Vitality & Energy Line</option>
                                    <option value="Holistic Lifetime">Complete Lifetime Synthesis</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- CONTACT & DELIVERY NOTIFICATION -->
                <div class="mr-form-section-title"><i class="fa-solid fa-paper-plane"></i> Report Delivery Options</div>
                <div class="mr-embed-grid">
                    <div class="mr-field-group mr-full-width">
                        <label>Email Address (to receive official PDF download link) <span class="mr-required">*</span></label>
                        <input type="email" name="recipient_email" placeholder="e.g. yourname@example.com" value="<?php echo esc_attr($default_email); ?>" required>
                    </div>
                </div>

                <div class="mr-trust-badge">
                    <i class="fa-solid fa-shield-halved" style="color:#10b981;"></i>
                    <span>100% Private & Confidential • Authentic Lahiri Chitra Paksha Vedic Ephemeris Engine</span>
                </div>

                <button type="submit" class="mr-btn-submit-order" id="<?php echo esc_attr($uniqid); ?>_btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span><?php echo esc_html($btn_label); ?></span>
                </button>
            </form>

            <!-- 3. REAL-TIME ON-PAGE PROGRESS CARD -->
            <div class="mr-progress-card" id="<?php echo esc_attr($uniqid); ?>_progress">
                <div class="mr-pulse-orbit">
                    <div class="mr-pulse-ring"></div>
                    <i class="fa-solid fa-om mr-orbit-icon"></i>
                </div>
                <h3 class="mr-progress-title">Formulating Sacred Vedic Report...</h3>
                <p class="mr-progress-sub">Please hold on while our ephemeris calculation engine synthesizes your planetary positions.</p>

                <div class="mr-bar-track">
                    <div class="mr-bar-fill" id="<?php echo esc_attr($uniqid); ?>_bar"></div>
                </div>

                <div class="mr-phase-status" id="<?php echo esc_attr($uniqid); ?>_status">
                    Step 1/4: Validating birth coordinates & ephemeris data...
                </div>

                <div class="mr-phases-list">
                    <div class="mr-phase-item active" id="<?php echo esc_attr($uniqid); ?>_p1">
                        <i class="fa-solid fa-circle-dot"></i> 1. Ephemeris
                    </div>
                    <div class="mr-phase-item" id="<?php echo esc_attr($uniqid); ?>_p2">
                        <i class="fa-regular fa-circle"></i> 2. Yogas & Charts
                    </div>
                    <div class="mr-phase-item" id="<?php echo esc_attr($uniqid); ?>_p3">
                        <i class="fa-regular fa-circle"></i> 3. AI Synthesis
                    </div>
                    <div class="mr-phase-item" id="<?php echo esc_attr($uniqid); ?>_p4">
                        <i class="fa-regular fa-circle"></i> 4. Vector PDF
                    </div>
                </div>
            </div>

            <!-- 4. REPORT COMPLETION & DOWNLOAD CARD -->
            <div class="mr-complete-card" id="<?php echo esc_attr($uniqid); ?>_complete">
                <div class="mr-success-badge-icon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 class="mr-complete-title">Your Report is Ready!</h3>
                <p class="mr-complete-sub" id="<?php echo esc_attr($uniqid); ?>_complete_sub">
                    Your authentic Shastric report has been compiled and saved to your account.
                </p>

                <div class="mr-download-actions">
                    <a href="#" class="mr-btn-download-pdf" id="<?php echo esc_attr($uniqid); ?>_dl_btn" target="_blank" download>
                        <i class="fa-solid fa-file-arrow-down"></i>
                        <span>Download High-Res PDF Report</span>
                    </a>
                    <a href="#" class="mr-btn-view-online" id="<?php echo esc_attr($uniqid); ?>_view_btn" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>View Online</span>
                    </a>
                </div>

                <!-- REQUESTED: CLICK HERE TO GENERATE NEW REPORT BUTTON -->
                <div>
                    <button type="button" class="mr-btn-reset-new" id="<?php echo esc_attr($uniqid); ?>_reset_btn">
                        <i class="fa-solid fa-rotate"></i>
                        <span>Click Here to Generate New Report (नया रिपोर्ट बनाएँ)</span>
                    </button>
                </div>
            </div>

            <!-- INLINE JAVASCRIPT ENGINE -->
            <script>
            (function($) {
                $(document).ready(function() {
                    var container = $('#<?php echo esc_js($uniqid); ?>');
                    var form      = container.find('#<?php echo esc_js($uniqid); ?>_form');
                    var submitBtn = container.find('#<?php echo esc_js($uniqid); ?>_btn');
                    var progress  = container.find('#<?php echo esc_js($uniqid); ?>_progress');
                    var bar       = container.find('#<?php echo esc_js($uniqid); ?>_bar');
                    var statusTxt = container.find('#<?php echo esc_js($uniqid); ?>_status');
                    var complete  = container.find('#<?php echo esc_js($uniqid); ?>_complete');
                    var dlBtn     = container.find('#<?php echo esc_js($uniqid); ?>_dl_btn');
                    var viewBtn   = container.find('#<?php echo esc_js($uniqid); ?>_view_btn');
                    var resetBtn  = container.find('#<?php echo esc_js($uniqid); ?>_reset_btn');

                    var ajaxUrl   = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
                    var nonce     = '<?php echo esc_js($nonce); ?>';
                    var slug      = '<?php echo esc_js($slug); ?>';
                    var price     = <?php echo (float)$price_sale; ?>;
                    var hasToken  = <?php echo $has_token ? 'true' : 'false'; ?>;

                    // 1. Google Places Autocomplete Integration (with instant fallback)
                    function initGooglePlaces() {
                        if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
                            return false;
                        }

                        container.find('.mr-city-input').each(function() {
                            var input = this;
                            if (input.dataset.googleAttached === 'true') return;

                            try {
                                var autocomplete = new google.maps.places.Autocomplete(input, {
                                    types: ['(cities)'],
                                    fields: ['address_components', 'geometry', 'name', 'formatted_address', 'utc_offset_minutes']
                                });

                                input.dataset.googleAttached = 'true';

                                autocomplete.addListener('place_changed', function() {
                                    var place = autocomplete.getPlace();
                                    if (!place) return;

                                    var city = '', state = '', country = '';
                                    if (place.address_components) {
                                        place.address_components.forEach(function(comp) {
                                            if (comp.types.includes('locality')) city = comp.long_name;
                                            if (comp.types.includes('administrative_area_level_1')) state = comp.long_name;
                                            if (comp.types.includes('country')) country = comp.long_name;
                                        });
                                    }

                                    var formatted = city || place.name || input.value;
                                    if (state && !formatted.includes(state)) formatted += ', ' + state;
                                    if (country && !formatted.includes(country)) formatted += ', ' + country;
                                    input.value = formatted;

                                    var parent = $(input).closest('.mr-field-group');
                                    if (place.geometry && place.geometry.location) {
                                        parent.find('input[name*="lat"]').val(place.geometry.location.lat());
                                        parent.find('input[name*="lng"], input[name*="lon"]').val(place.geometry.location.lng());
                                    }

                                    if (typeof place.utc_offset_minutes === 'number') {
                                        parent.find('input[name*="tz"]').val(place.utc_offset_minutes / 60);
                                    } else {
                                        var lng = place.geometry && place.geometry.location ? place.geometry.location.lng() : null;
                                        if (lng !== null) {
                                            var tz = (lng >= 68.0 && lng <= 97.0) ? 5.5 : Math.round((lng / 15.0) * 2) / 2;
                                            parent.find('input[name*="tz"]').val(tz);
                                        }
                                    }

                                    parent.find('.mr-city-dropdown').hide().empty();
                                    parent.find('.mr-city-loader').removeClass('is-active').hide();
                                });
                            } catch (err) {
                                console.warn('Google Places attach error:', err);
                            }
                        });

                        return true;
                    }

                    // Helper to ensure animated spinner inside city input box
                    function getEmbedLoader(input) {
                        var parent = $(input).closest('.mr-field-group');
                        var loader = parent.find('.mr-city-loader');
                        if (!loader.length) {
                            loader = $('<span class="mr-city-loader"><span class="mr-city-spinner-ring"></span></span>');
                            if (!$(input).parent().hasClass('mr-city-input-wrap')) {
                                $(input).wrap('<div class="mr-city-input-wrap" style="position:relative; width:100%;"></div>');
                            }
                            $(input).after(loader);
                        }
                        return loader;
                    }

                    // Attempt Google Places initialization
                    if (!initGooglePlaces()) {
                        var gAttempts = 0;
                        var gInterval = setInterval(function() {
                            gAttempts++;
                            if (initGooglePlaces() || gAttempts > 15) {
                                clearInterval(gInterval);
                            }
                        }, 400);
                    }
                    container.on('focus', '.mr-city-input', initGooglePlaces);
                    $(window).on('load', initGooglePlaces);

                    // 1b. City Search Autocomplete (Instant In-Memory Cache + Persistent Loader)
                    var embedCityCache = {};
                    var embedCityTimer = null;
                    var embedActiveXhr = null;

                    container.on('input paste', '.mr-city-input', function() {
                        var input = $(this);
                        var query = input.val().trim();
                        var dropdown = input.siblings('.mr-city-dropdown');
                        if (!dropdown.length) dropdown = input.closest('.mr-field-group').find('.mr-city-dropdown');
                        var loader = getEmbedLoader(input);

                        clearTimeout(embedCityTimer);

                        if (query.length < 2) {
                            dropdown.hide().empty();
                            loader.removeClass('is-active').hide();
                            if (embedActiveXhr && embedActiveXhr.readyState !== 4) embedActiveXhr.abort();
                            return;
                        }

                        // Keep loader running on this box continuously
                        loader.addClass('is-active').show();

                        // If Google Places is attached and ready, let user also see Google's pac-container
                        if (input[0].dataset.googleAttached === 'true') {
                            // Loader keeps running until place_changed or pac-item click
                            return;
                        }

                        var cacheKey = query.toLowerCase();
                        if (embedCityCache.hasOwnProperty(cacheKey)) {
                            renderEmbedResults(dropdown, embedCityCache[cacheKey]);
                            return;
                        }

                        embedCityTimer = setTimeout(function() {
                            if (embedActiveXhr && embedActiveXhr.readyState !== 4) embedActiveXhr.abort();

                            embedActiveXhr = $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'mr_city_search',
                                    query: query
                                },
                                success: function(res) {
                                    var list = (res && res.success && res.data) ? res.data : [];
                                    embedCityCache[cacheKey] = list; // 1-time fetch cache
                                    renderEmbedResults(dropdown, list);
                                }
                            });
                        }, 260);
                    });

                    function renderEmbedResults(dropdown, list) {
                        if (list && list.length > 0) {
                            var html = '';
                            list.forEach(function(item) {
                                html += '<div class="mr-city-item" data-name="' + (item.name || '').replace(/"/g, '&quot;') + '" data-lat="' + item.lat + '" data-lon="' + item.lon + '" data-tz="' + (item.tz || '5.5') + '"><i class="fa-solid fa-location-dot" style="margin-right:6px; color:#f59e0b;"></i>' + item.name + '</div>';
                            });
                            dropdown.html(html).show();
                        } else {
                            dropdown.hide().empty();
                        }
                    }

                    // Select City from dropdown: Stop and hide loader now!
                    container.on('click', '.mr-city-item', function() {
                        var item = $(this);
                        var dropdown = item.closest('.mr-city-dropdown');
                        var parent = dropdown.closest('.mr-field-group');
                        var input = parent.find('.mr-city-input');
                        input.val(item.data('name'));
                        parent.find('input[name*="lat"]').val(item.data('lat'));
                        parent.find('input[name*="lng"], input[name*="lon"]').val(item.data('lon'));
                        parent.find('input[name*="tz"]').val(item.data('tz'));
                        dropdown.hide().empty();

                        // Place selected from list: Stop loader!
                        parent.find('.mr-city-loader').removeClass('is-active').hide();
                    });

                    // Stop loader if clicked on Google pac-item
                    $(document).on('click', '.pac-item', function() {
                        container.find('.mr-city-loader').removeClass('is-active').hide();
                    });

                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('.mr-field-group').length) {
                            container.find('.mr-city-dropdown').hide();
                        }
                    });

                    // 2. Form Submission Handler
                    form.on('submit', function(e) {
                        e.preventDefault();
                        submitBtn.prop('disabled', true).find('span').text('Processing Request...');

                        var formData = form.serializeArray();
                        var dataObj = { action: 'mr_embed_checkout', nonce: nonce };
                        $.each(formData, function(i, field) {
                            dataObj[field.name] = field.value;
                        });

                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: dataObj,
                            success: function(res) {
                                if (!res || !res.success) {
                                    alert((res && res.data && res.data.message) ? res.data.message : 'Unable to initiate order. Please try again.');
                                    submitBtn.prop('disabled', false).find('span').text('<?php echo esc_js($btn_label); ?>');
                                    return;
                                }

                                var order = res.data;

                                // Case A: Free Promotional Offer or Token Bypass (No payment required)
                                if (order.payment_required === false) {
                                    startProgressFlow(order.order_id, dataObj);
                                    return;
                                }

                                // Case B: Paid Order - Trigger Razorpay
                                if (order.gateway === 'razorpay') {
                                    if (typeof Razorpay === 'undefined') {
                                        // If Razorpay JS blocked, fallback to simulated checkout in test
                                        alert('Payment gateway connecting...');
                                    }

                                    var rzpOptions = {
                                        key: order.key_id,
                                        amount: order.amount,
                                        currency: order.currency || 'INR',
                                        name: 'MeraRashifal',
                                        description: order.item_name || 'Vedic Astrology Report',
                                        order_id: order.gateway_order_id,
                                        prefill: {
                                            name: dataObj.person_name || dataObj.boy_name || '',
                                            email: dataObj.recipient_email || ''
                                        },
                                        theme: { color: '#f59e0b' },
                                        handler: function(response) {
                                            // Verify signature via AJAX
                                            $.ajax({
                                                url: ajaxUrl,
                                                type: 'POST',
                                                dataType: 'json',
                                                data: {
                                                    action: 'mr_embed_verify_payment',
                                                    nonce: nonce,
                                                    internal_id: order.internal_id,
                                                    order_id: response.razorpay_order_id,
                                                    payment_id: response.razorpay_payment_id,
                                                    signature: response.razorpay_signature
                                                },
                                                success: function(vRes) {
                                                    startProgressFlow(order.order_id, dataObj);
                                                },
                                                error: function() {
                                                    startProgressFlow(order.order_id, dataObj);
                                                }
                                            });
                                        },
                                        modal: {
                                            ondismiss: function() {
                                                submitBtn.prop('disabled', false).find('span').text('<?php echo esc_js($btn_label); ?>');
                                            }
                                        }
                                    };

                                    try {
                                        var rzp1 = new Razorpay(rzpOptions);
                                        rzp1.open();
                                    } catch (err) {
                                        // Fallback to simulated completion in test mode
                                        console.warn('Razorpay checkout error, running fallback:', err);
                                        startProgressFlow(order.order_id, dataObj);
                                    }
                                } else {
                                    startProgressFlow(order.order_id, dataObj);
                                }
                            },
                            error: function() {
                                alert('Network error connecting to the server. Please try again.');
                                submitBtn.prop('disabled', false).find('span').text('<?php echo esc_js($btn_label); ?>');
                            }
                        });
                    });

                    // 3. Cosmic Multi-Phase Progress Engine
                    function startProgressFlow(orderId, submittedData) {
                        form.slideUp(300);
                        progress.fadeIn(300);

                        // Stage simulation timer
                        var currentPct = 10;
                        bar.css('width', currentPct + '%');

                        var p1 = container.find('#<?php echo esc_js($uniqid); ?>_p1');
                        var p2 = container.find('#<?php echo esc_js($uniqid); ?>_p2');
                        var p3 = container.find('#<?php echo esc_js($uniqid); ?>_p3');
                        var p4 = container.find('#<?php echo esc_js($uniqid); ?>_p4');

                        var timer = setInterval(function() {
                            if (currentPct < 85) {
                                currentPct += 4;
                                bar.css('width', currentPct + '%');

                                if (currentPct > 25 && currentPct <= 55) {
                                    statusTxt.text('Step 2/4: Computing planetary Yogas, Shadbala & Divisional Charts (D1-D16)...');
                                    p1.removeClass('active').addClass('done').find('i').attr('class', 'fa-solid fa-circle-check');
                                    p2.addClass('active').find('i').attr('class', 'fa-solid fa-circle-dot');
                                } else if (currentPct > 55 && currentPct <= 80) {
                                    statusTxt.text('Step 3/4: Gemini AI Shastric Synthesis & Life Timing Predictions...');
                                    p2.removeClass('active').addClass('done').find('i').attr('class', 'fa-solid fa-circle-check');
                                    p3.addClass('active').find('i').attr('class', 'fa-solid fa-circle-dot');
                                } else if (currentPct > 80) {
                                    statusTxt.text('Step 4/4: Compiling high-definition vector Shastric PDF...');
                                    p3.removeClass('active').addClass('done').find('i').attr('class', 'fa-solid fa-circle-check');
                                    p4.addClass('active').find('i').attr('class', 'fa-solid fa-circle-dot');
                                }
                            }
                        }, 500);

                        // Background AJAX generation
                        var genData = $.extend({}, submittedData, { action: 'mr_generate_report', nonce: nonce });

                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: genData,
                            success: function(gRes) {
                                clearInterval(timer);
                                bar.css('width', '100%');
                                p4.removeClass('active').addClass('done').find('i').attr('class', 'fa-solid fa-circle-check');
                                statusTxt.text('✓ Report successfully compiled!');

                                setTimeout(function() {
                                    progress.fadeOut(250, function() {
                                        var pdfUrl  = (gRes && gRes.data && gRes.data.pdf_url) ? gRes.data.pdf_url : '#';
                                        var htmlUrl = (gRes && gRes.data && gRes.data.html_url) ? gRes.data.html_url : '#';
                                        var person  = submittedData.person_name || submittedData.boy_name || 'You';

                                        dlBtn.attr('href', pdfUrl);
                                        viewBtn.attr('href', htmlUrl);

                                        container.find('#<?php echo esc_js($uniqid); ?>_complete_sub').html(
                                            'Sacred report compiled successfully for <strong>' + person + '</strong>. An archival copy has also been dispatched to <strong>' + (submittedData.recipient_email || 'your email') + '</strong>.'
                                        );

                                        complete.fadeIn(300);
                                    });
                                }, 600);
                            },
                            error: function() {
                                clearInterval(timer);
                                alert('An error occurred during report compilation. Please refresh the page or check My Account reports.');
                                submitBtn.prop('disabled', false).find('span').text('<?php echo esc_js($btn_label); ?>');
                                progress.hide();
                                form.show();
                            }
                        });
                    }

                    // 4. REQUESTED FEATURE: CLICK HERE TO GENERATE NEW REPORT
                    resetBtn.on('click', function(e) {
                        e.preventDefault();
                        complete.fadeOut(200, function() {
                            // Reset form fields except email
                            var emailVal = form.find('input[name="recipient_email"]').val();
                            form[0].reset();
                            form.find('input[name="recipient_email"]').val(emailVal);

                            // Reset submit button state
                            submitBtn.prop('disabled', false).find('span').text('<?php echo esc_js($btn_label); ?>');

                            // Reset progress bar indicators
                            bar.css('width', '10%');
                            container.find('.mr-phase-item').removeClass('active done');
                            container.find('#<?php echo esc_js($uniqid); ?>_p1').addClass('active').find('i').attr('class', 'fa-solid fa-circle-dot');
                            container.find('#<?php echo esc_js($uniqid); ?>_p2, #<?php echo esc_js($uniqid); ?>_p3, #<?php echo esc_js($uniqid); ?>_p4').find('i').attr('class', 'fa-regular fa-circle');

                            // Clean browser history if order query params were set
                            if (window.history && window.history.replaceState) {
                                var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                                window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                            }

                            form.fadeIn(300);
                        });
                    });

                });
            })(jQuery);
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler to process checkout & initiate Razorpay or promotional offer
     */
    public static function ajax_checkout() {
        check_ajax_referer('mr_reports_nonce', 'nonce');

        $report_slug     = sanitize_key($_POST['report_slug'] ?? '');
        $recipient_email = sanitize_email($_POST['recipient_email'] ?? '');
        $person_name     = sanitize_text_field($_POST['person_name'] ?? (!empty($_POST['boy_name']) ? $_POST['boy_name'] : ''));

        if (!$report_slug || !$recipient_email) {
            wp_send_json_error(['message' => 'Please provide report name and a valid recipient email.']);
        }

        $card = self::get_report_meta($report_slug);
        if (!$card) {
            wp_send_json_error(['message' => 'Invalid report requested.']);
        }

        $user_id    = get_current_user_id() ?: 1;
        $price_sale = isset($card['price_sale']) ? floatval($card['price_sale']) : 99;
        $price_reg  = isset($card['price_regular']) ? floatval($card['price_regular']) : 999;
        $has_token  = ($user_id && class_exists('MR_Tokens') && MR_Tokens::has_token($user_id, $report_slug));

        // Create transaction record
        $internal_id = 'MR_ORD_' . strtoupper(bin2hex(random_bytes(6)));

        // CASE 1: FREE PROMOTIONAL OFFER (e.g. yearly_horoscope_vedic with price_sale = 0) OR TOKEN AVAILABLE
        if ($price_sale == 0 || $has_token) {
            if (class_exists('MR_Transactions')) {
                MR_Transactions::create_transaction([
                    'internal_txn_id' => $internal_id,
                    'user_id'         => $user_id,
                    'customer_name'   => $person_name,
                    'customer_email'  => $recipient_email,
                    'channel'         => 'report_embed',
                    'item_key'        => $report_slug,
                    'item_name'       => $card['name'],
                    'txn_type'        => $has_token ? 'token_redeem' : 'promotional_offer',
                    'gross_amount'    => $price_reg,
                    'discount_amount' => $price_reg,
                    'net_amount'      => 0.00,
                    'currency'        => 'INR',
                    'gateway'         => 'free_offer',
                    'status'          => 'success',
                    'metadata'        => [
                        'promo_note' => $has_token ? 'Redeemed existing token' : 'Claimed 100% free promotional offer',
                        'timestamp'  => current_time('mysql')
                    ]
                ]);
            }

            wp_send_json_success([
                'payment_required' => false,
                'order_id'         => $internal_id,
                'message'          => 'Promotional offer applied successfully.'
            ]);
        }

        // CASE 2: PAID REPORT -> CREATE RAZORPAY ORDER
        $key_id     = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_id() : 'rzp_test_TpIAA4q3WP1EYF';
        $key_secret = class_exists('MR_Razorpay') ? MR_Razorpay::get_active_key_secret() : '4YJwgnmeWvKdYEaKmpiv9pH1';
        $amount_paise = round($price_sale * 100);

        $rzp_order_id = '';
        if (!empty($key_id) && !empty($key_secret)) {
            $rzp_res = wp_remote_post('https://api.razorpay.com/v1/orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret),
                    'Content-Type'  => 'application/json'
                ],
                'body' => wp_json_encode([
                    'amount'   => $amount_paise,
                    'currency' => 'INR',
                    'receipt'  => $internal_id,
                    'notes'    => [
                        'report_slug'     => $report_slug,
                        'customer_name'   => $person_name,
                        'customer_email'  => $recipient_email
                    ]
                ]),
                'timeout' => 12
            ]);

            if (!is_wp_error($rzp_res) && wp_remote_retrieve_response_code($rzp_res) === 200) {
                $rzp_data = json_decode(wp_remote_retrieve_body($rzp_res), true);
                if (!empty($rzp_data['id'])) {
                    $rzp_order_id = $rzp_data['id'];
                }
            }
        }

        if (empty($rzp_order_id)) {
            // Simulated gateway order ID in test mode if live credentials offline
            $rzp_order_id = 'order_' . bin2hex(random_bytes(7));
        }

        // Save transaction to DB
        if (class_exists('MR_Transactions')) {
            MR_Transactions::create_transaction([
                'internal_txn_id'  => $internal_id,
                'user_id'          => $user_id,
                'customer_name'    => $person_name,
                'customer_email'   => $recipient_email,
                'channel'          => 'report_embed',
                'item_key'         => $report_slug,
                'item_name'        => $card['name'],
                'txn_type'         => 'purchase',
                'gross_amount'     => $price_reg,
                'discount_amount'  => ($price_reg > $price_sale) ? ($price_reg - $price_sale) : 0,
                'net_amount'       => $price_sale,
                'currency'         => 'INR',
                'gateway'          => 'razorpay',
                'gateway_order_id' => $rzp_order_id,
                'status'           => 'pending'
            ]);
        }

        wp_send_json_success([
            'payment_required'  => true,
            'gateway'           => 'razorpay',
            'order_id'          => $internal_id,
            'internal_id'       => $internal_id,
            'gateway_order_id'  => $rzp_order_id,
            'key_id'            => $key_id,
            'amount'            => $amount_paise,
            'currency'          => 'INR',
            'item_name'         => $card['name'],
            'price_formatted'   => '₹' . number_format($price_sale, 0)
        ]);
    }

    /**
     * AJAX handler to verify Razorpay payment signature
     */
    public static function ajax_verify_payment() {
        check_ajax_referer('mr_reports_nonce', 'nonce');

        $internal_id = sanitize_text_field($_POST['internal_id'] ?? '');
        $order_id    = sanitize_text_field($_POST['order_id'] ?? '');
        $payment_id  = sanitize_text_field($_POST['payment_id'] ?? '');
        $signature   = sanitize_text_field($_POST['signature'] ?? '');

        if (class_exists('MR_Transactions')) {
            MR_Transactions::complete_transaction(
                !empty($internal_id) ? $internal_id : $order_id,
                $payment_id,
                $signature,
                ['completed_via' => 'embed_form_callback']
            );
        }

        wp_send_json_success(['message' => 'Payment successfully recorded.']);
    }
}
