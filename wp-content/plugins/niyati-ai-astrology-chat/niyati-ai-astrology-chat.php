<?php
/**
 * Plugin Name: Niyati - AI Vedic Astrology Chatbot
 * Plugin URI: https://merarashifal.com
 * Description: Conversational AI Astrologer (Niyati) powered by Google Gemini and VedicAstroAPI. Provides authentic Kundli analysis, Vimshottari dasha predictions, Dosha evaluation, customizable pre-defined questions by language, and free question limits for new vs regular users.
 * Version: 1.3.0
 * Author: MeraRashifal Team
 * Author URI: https://merarashifal.com
 * Text Domain: merarashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NIYATI_VERSION', '1.3.0');
define('NIYATI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NIYATI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require Components
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-limits.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-wallet.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-guardrails.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-vedic-api.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-gemini-api.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-linker.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-nudge.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-storage.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-rest-api.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-seo.php';
require_once NIYATI_PLUGIN_DIR . 'includes/class-niyati-admin.php';

// Activation Hook: Initialize Default Settings, Tables, and Daily Cleanup Cron
register_activation_hook(__FILE__, function () {
    Niyati_Wallet::create_tables();
    Niyati_SEO::ensure_landing_page();
    if (!wp_next_scheduled('niyati_daily_archive_cleanup_hook')) {
        wp_schedule_event(time(), 'daily', 'niyati_daily_archive_cleanup_hook');
    }
    if (get_option('niyati_chat_settings') === false) {
        $defaults = Niyati_REST_API::get_settings();
        $defaults['gemini_api_key'] = Niyati_Gemini_API::get_api_key();
        $defaults['vedic_api_key'] = Niyati_Vedic_API::get_api_key();
        $defaults['gemini_model'] = 'gemini-3.1-flash-lite';
        update_option('niyati_chat_settings', $defaults);
    }
});

// Deactivation Hook: Clear Scheduled Cron
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('niyati_daily_archive_cleanup_hook');
});

// Initialize Components
add_action('plugins_loaded', function () {
    // Ensure daily archive cleanup is scheduled
    if (!wp_next_scheduled('niyati_daily_archive_cleanup_hook')) {
        wp_schedule_event(time(), 'daily', 'niyati_daily_archive_cleanup_hook');
    }

    add_action('niyati_daily_archive_cleanup_hook', function () {
        if (class_exists('Niyati_Storage')) {
            $settings = Niyati_REST_API::get_settings();
            $retention = isset($settings['archive_retention_days']) ? absint($settings['archive_retention_days']) : 90;
            Niyati_Storage::cleanup_old_archives($retention);
        }
    });

    if (get_option('niyati_chat_settings') === false) {
        $defaults = Niyati_REST_API::get_settings();
        $defaults['gemini_api_key'] = Niyati_Gemini_API::get_api_key();
        $defaults['vedic_api_key'] = Niyati_Vedic_API::get_api_key();
        $defaults['gemini_model'] = 'gemini-3.1-flash-lite';
        update_option('niyati_chat_settings', $defaults);
    }

    // REST API endpoints
    Niyati_REST_API::init();

    // 23 SEO Parameters & Landing Page Engine
    Niyati_SEO::init();

    // Admin Panel
    if (is_admin()) {
        Niyati_Admin::init();
    }
});

// Clear client chat session cookie flag on WordPress logout
add_action('wp_logout', function () {
    if (!headers_sent()) {
        $cookie_path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
        $cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        setcookie('niyati_logged_out', '1', time() + 3600, $cookie_path, $cookie_domain, is_ssl(), false);
    }
});

/**
 * Register Frontend Assets & Shortcodes
 */
class Niyati_Frontend {

    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_action('wp_footer', [__CLASS__, 'render_floating_widget']);
        add_shortcode('niyati_chat', [__CLASS__, 'render_shortcode']);
        add_shortcode('niyati_astrology_chat', [__CLASS__, 'render_shortcode']);
    }

    public static function register_assets() {
        $css_ver = file_exists(NIYATI_PLUGIN_DIR . 'assets/css/niyati-chat.css') ? filemtime(NIYATI_PLUGIN_DIR . 'assets/css/niyati-chat.css') : NIYATI_VERSION;
        $js_ver  = file_exists(NIYATI_PLUGIN_DIR . 'assets/js/niyati-chat.js') ? filemtime(NIYATI_PLUGIN_DIR . 'assets/js/niyati-chat.js') : NIYATI_VERSION;

        wp_register_style(
            'niyati-chat-css',
            NIYATI_PLUGIN_URL . 'assets/css/niyati-chat.css',
            [],
            $css_ver
        );

        wp_register_script(
            'niyati-chat-js',
            NIYATI_PLUGIN_URL . 'assets/js/niyati-chat.js',
            [],
            $js_ver,
            true
        );

        $settings = Niyati_REST_API::get_settings();
        $curr_user_id = get_current_user_id();
        $curr_user = $curr_user_id ? wp_get_current_user() : null;
        $visitor_id = class_exists('Niyati_Limits') ? Niyati_Limits::get_visitor_id() : '';
        $initial_quota = class_exists('Niyati_Limits') ? Niyati_Limits::check_quota($visitor_id) : null;
        $initial_paid_credits = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_balance($visitor_id, $curr_user_id) : 0;
        $initial_validity = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_validity_info($visitor_id, $curr_user_id) : null;

        $redirect_to = home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
        $login_url   = wp_login_url($redirect_to ?: home_url('/'));
        $is_logged_in = is_user_logged_in() && ($curr_user_id > 0);

        wp_localize_script('niyati-chat-js', 'niyatiConfig', [
            'rest_url'             => esc_url_raw(rest_url('niyati/v1')),
            'nonce'                => wp_create_nonce('wp_rest'),
            'user_id'              => $curr_user_id,
            'is_logged_in'         => $is_logged_in,
            'login_url'            => esc_url_raw($login_url),
            'user_name'            => $curr_user ? ($curr_user->display_name ?: $curr_user->user_login) : '',
            'user_email'           => $curr_user ? $curr_user->user_email : '',
            'bot_name'             => $settings['bot_name'] ?? 'Niyati',
            'avatar_url'           => NIYATI_PLUGIN_URL . 'assets/images/niyati-avatar.jpg',
            'loader_url'           => content_url('/uploads/2025/11/loader.webp'),
            'theme'                => $settings['theme_color'] ?? '#FF8C00',
            'initial_paid_credits' => $initial_paid_credits,
            'initial_quota'        => $initial_quota,
            'initial_validity'     => $initial_validity,
            'visitor_id'           => $visitor_id,
            'nudge'                => class_exists('Niyati_Nudge') ? Niyati_Nudge::get_active_nudge() : ['enabled' => false],
        ]);

        // Inject dynamic theme color and #FFFBEB background CSS
        $custom_color = $settings['theme_color'] ?? '#FF8C00';
        $custom_css = ":root { --niyati-primary: {$custom_color}; --niyati-primary-dark: #B3541E; --niyati-bg: #FFFBEB; }";
        wp_add_inline_style('niyati-chat-css', $custom_css);
    }

    public static function render_floating_widget() {
        $settings = Niyati_REST_API::get_settings();
        if (empty($settings['floating_enabled']) || $settings['floating_enabled'] !== '1') {
            return;
        }

        wp_enqueue_style('niyati-chat-css');
        wp_enqueue_script('niyati-chat-js');
    }

    public static function render_shortcode($atts = []) {
        $atts = (array)$atts;
        if (!empty($atts['view']) && in_array($atts['view'], ['button', 'btn', 'inline'], true)) {
            wp_enqueue_style('niyati-chat-css');
            wp_enqueue_script('niyati-chat-js');

            return '<div class="niyati-embedded-chat-trigger" style="margin:20px 0;">'
                 . '<button onclick="if(window.document.getElementById(\'niyati-launcher\')){window.document.getElementById(\'niyati-launcher\').click();}" class="button button-primary" style="background:var(--niyati-primary, #d97706); border:none; padding:12px 24px; border-radius:24px; font-weight:600; color:#fff; cursor:pointer;">'
                 . '✨ Chat with Niyati (Vedic Astrologer)'
                 . '</button>'
                 . '</div>';
        }

        if (class_exists('Niyati_SEO')) {
            return Niyati_SEO::render_landing_page($atts);
        }

        wp_enqueue_style('niyati-chat-css');
        wp_enqueue_script('niyati-chat-js');

        return '<div class="niyati-embedded-chat-trigger" style="margin:20px 0;">'
             . '<button onclick="if(window.document.getElementById(\'niyati-launcher\')){window.document.getElementById(\'niyati-launcher\').click();}" class="button button-primary" style="background:var(--niyati-primary, #d97706); border:none; padding:12px 24px; border-radius:24px; font-weight:600; color:#fff; cursor:pointer;">'
             . '✨ Chat with Niyati (Vedic Astrologer)'
             . '</button>'
             . '</div>';
    }
}

Niyati_Frontend::init();
