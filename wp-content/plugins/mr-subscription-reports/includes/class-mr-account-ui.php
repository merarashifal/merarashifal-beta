<?php
/**
 * Frontend User Account Dashboard, Astrological Profile, Reports Archive & Passbook
 * Shortcodes: [mr_user_account], [mr_account], [mr_account_dashboard]
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Account_UI {

    public static function init() {
        // Register Shortcodes
        add_shortcode('mr_user_account', [__CLASS__, 'render_account_page']);
        add_shortcode('mr_account', [__CLASS__, 'render_account_page']);
        add_shortcode('mr_account_dashboard', [__CLASS__, 'render_account_page']);

        // Assets
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);


        // AJAX handlers for saving details
        add_action('wp_ajax_mr_save_account_astro_details', [__CLASS__, 'ajax_save_astro_details']);
        add_action('wp_ajax_mr_save_account_settings', [__CLASS__, 'ajax_save_settings']);
        add_action('wp_ajax_mr_get_passbook_page', [__CLASS__, 'ajax_get_passbook_page']);
        add_action('wp_ajax_mr_submit_support_ticket', [__CLASS__, 'ajax_submit_support_ticket']);
        add_action('wp_ajax_nopriv_mr_submit_support_ticket', [__CLASS__, 'ajax_submit_support_ticket']);

        // DPDP Act 2023 Data Principal Handlers
        add_action('wp_ajax_mr_dpdp_export_data', [__CLASS__, 'ajax_dpdp_export_data']);
        add_action('wp_ajax_mr_dpdp_erase_account', [__CLASS__, 'ajax_dpdp_erase_account']);
        add_action('wp_ajax_mr_dpdp_get_erase_challenge', [__CLASS__, 'ajax_get_erase_challenge']);

        // Strict Barrier: Block non-admin customers from entering WP Admin
        add_action('init', [__CLASS__, 'restrict_admin_area_access']);
        add_action('admin_init', [__CLASS__, 'restrict_admin_area_access']);
        add_filter('login_redirect', [__CLASS__, 'filter_login_redirect'], 99, 3);

        // Block WP Login page completely - redirect to Google OAuth / Account page
        add_filter('login_url', [__CLASS__, 'filter_login_url'], 99, 3);
        add_action('login_init', [__CLASS__, 'prevent_wp_login_display'], 1);
        add_action('wp_logout', [__CLASS__, 'on_user_logout']);

        // Suppress default WordPress "Set your password" email for customer and social login signups
        add_filter('wp_new_user_notification_email', [__CLASS__, 'disable_customer_new_user_email'], 99, 3);
        remove_action('register_new_user', 'wp_send_new_user_notifications');

        // Nextend Social Login redirect filters to preserve and prioritize customer origin service page
        add_filter('google_login_redirect_url', [__CLASS__, 'filter_nsl_redirect'], 99, 2);
        add_filter('google_register_redirect_url', [__CLASS__, 'filter_nsl_redirect'], 99, 2);
        add_filter('nsl_googledefault_last_location_redirect', [__CLASS__, 'filter_nsl_redirect'], 99, 2);
        add_filter('nsl_google_last_location_redirect', [__CLASS__, 'filter_nsl_redirect'], 99, 2);

        // Immediate bounce from /my-account/ if user is already logged in with return target
        add_action('template_redirect', [__CLASS__, 'check_login_return_redirect'], 1);

        // Eliminate WordPress Admin Bar for non-admin customers
        add_filter('show_admin_bar', [__CLASS__, 'filter_show_admin_bar']);

        // Apply account page body class for background styling
        add_filter('body_class', [__CLASS__, 'filter_body_classes']);

        // SEO Meta, Canonical, Robots & OpenGraph parameter hooks
        add_action('wp_head', [__CLASS__, 'render_account_seo_meta'], 1);
        add_filter('pre_get_document_title', [__CLASS__, 'filter_account_title'], 99);
        add_filter('wpseo_title', [__CLASS__, 'filter_account_title'], 99);
        add_filter('wpseo_metadesc', [__CLASS__, 'filter_account_metadesc'], 99);
        add_filter('wpseo_robots', [__CLASS__, 'filter_account_robots'], 99);
    }

    /**
     * Check if current request is the customer account page
     */
    public static function is_account_page() {
        if (is_admin()) return false;
        if (is_page('my-account') || is_page('account') || is_page(7235)) {
            return true;
        }
        if (!empty($GLOBALS['post']) && (has_shortcode($GLOBALS['post']->post_content, 'mr_user_account') || has_shortcode($GLOBALS['post']->post_content, 'mr_account'))) {
            return true;
        }
        $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($req_uri, '/my-account') !== false || strpos($req_uri, '/account') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Filter document and Yoast SEO title for Astro Account page
     */
    public static function filter_account_title($title) {
        if (self::is_account_page()) {
            return 'My Astro Account & Vedic Reports Archive | MeraRashifal';
        }
        return $title;
    }

    /**
     * Filter meta description for Astro Account page
     */
    public static function filter_account_metadesc($desc) {
        if (self::is_account_page()) {
            return 'Access your personalized Vedic birth chart, 20+ page horoscope PDF reports, saved birth details, and transaction passbook on MeraRashifal.';
        }
        return $desc;
    }

    /**
     * Ensure robots indexing is enabled for the account portal landing
     */
    public static function filter_account_robots($robots) {
        if (self::is_account_page()) {
            return 'index, follow';
        }
        return $robots;
    }

    /**
     * Output full SEO Meta, Canonical, OpenGraph, Twitter Cards, and Schema.org Breadcrumb JSON-LD
     */
    public static function render_account_seo_meta() {
        if (!self::is_account_page()) return;

        $page_url = self::get_account_page_url();
        $title    = 'My Astro Account & Vedic Reports Archive | MeraRashifal';
        $desc     = 'Access your personalized Vedic birth chart, 20+ page horoscope PDF reports, saved birth details, and transaction passbook on MeraRashifal.';
        $img_url  = 'https://merarashifal.com/wp-content/themes/astra-child/assets/og-images/og-default-main.jpg';
        ?>
        <!-- MeraRashifal SEO & OpenGraph Meta Parameters -->
        <meta name="description" content="<?php echo esc_attr($desc); ?>" />
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
        <link rel="canonical" href="<?php echo esc_url($page_url); ?>" />
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo esc_attr($title); ?>" />
        <meta property="og:description" content="<?php echo esc_attr($desc); ?>" />
        <meta property="og:url" content="<?php echo esc_url($page_url); ?>" />
        <meta property="og:site_name" content="MeraRashifal" />
        <meta property="og:image" content="<?php echo esc_url($img_url); ?>" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="<?php echo esc_attr($title); ?>" />
        <meta name="twitter:description" content="<?php echo esc_attr($desc); ?>" />
        <meta name="twitter:image" content="<?php echo esc_url($img_url); ?>" />
        
        <!-- Schema.org JSON-LD Structured Data: WebPage & BreadcrumbList -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@graph": [
                {
                    "@type": "WebPage",
                    "@id": "<?php echo esc_url($page_url); ?>#webpage",
                    "url": "<?php echo esc_url($page_url); ?>",
                    "name": "<?php echo esc_attr($title); ?>",
                    "description": "<?php echo esc_attr($desc); ?>",
                    "isPartOf": {
                        "@type": "WebSite",
                        "@id": "<?php echo esc_url(home_url('/')); ?>#website",
                        "url": "<?php echo esc_url(home_url('/')); ?>",
                        "name": "MeraRashifal"
                    },
                    "breadcrumb": {
                        "@id": "<?php echo esc_url($page_url); ?>#breadcrumb"
                    }
                },
                {
                    "@type": "BreadcrumbList",
                    "@id": "<?php echo esc_url($page_url); ?>#breadcrumb",
                    "itemListElement": [
                        {
                            "@type": "ListItem",
                            "position": 1,
                            "name": "Home",
                            "item": "<?php echo esc_url(home_url('/')); ?>"
                        },
                        {
                            "@type": "ListItem",
                            "position": 2,
                            "name": "My Astro Account",
                            "item": "<?php echo esc_url($page_url); ?>"
                        }
                    ]
                }
            ]
        }
        </script>
        <?php
    }

    /**
     * Filter body classes on account pages to set #FFFBEB background
     */
    public static function filter_body_classes($classes) {
        if (is_page('my-account') || is_page('account') || is_page(7235) || (!empty($GLOBALS['post']) && (has_shortcode($GLOBALS['post']->post_content, 'mr_user_account') || has_shortcode($GLOBALS['post']->post_content, 'mr_account')))) {
            $classes[] = 'mr-account-page-active';
        }
        return $classes;
    }



    /**
     * Resolve the Account Page URL
     * Checks if a published page contains the shortcode or has the slug 'my-account' / 'account'
     */
    public static function get_account_page_url() {
        static $resolved_url = null;
        if ($resolved_url !== null) {
            return $resolved_url;
        }

        global $wpdb;

        // 1. Look for page containing shortcode
        $page_id = $wpdb->get_var("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'page' 
              AND post_status = 'publish' 
              AND (post_content LIKE '%[mr_user_account%' OR post_content LIKE '%[mr_account%')
            LIMIT 1
        ");

        if ($page_id) {
            $resolved_url = get_permalink($page_id);
            return $resolved_url;
        }

        // 2. Look for page with slug 'my-account' or 'account'
        $slug_page = get_page_by_path('my-account');
        if (!$slug_page) {
            $slug_page = get_page_by_path('account');
        }
        if (!$slug_page) {
            $slug_page = get_page_by_path('accounts');
        }

        if ($slug_page && $slug_page->post_status === 'publish') {
            $resolved_url = get_permalink($slug_page->ID);
            return $resolved_url;
        }

        // 3. Fallback
        $resolved_url = home_url('/my-account/');
        return $resolved_url;
    }

    /**
     * Enqueue Assets for Account UI and Header Shortcut
     */
    public static function enqueue_assets() {
        // Enqueue FontAwesome if not already enqueued
        if (!wp_style_is('mr-font-awesome', 'enqueued')) {
            wp_enqueue_style(
                'mr-font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                [],
                '6.4.0'
            );
        }

        // Enqueue MeraRashifal Brand Typography: Playfair Display (Serif) & Poppins (Sans)
        if (!wp_style_is('mr-brand-fonts', 'enqueued')) {
            wp_enqueue_style(
                'mr-brand-fonts',
                'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Poppins:wght@400;500;600;700;800&display=swap',
                [],
                null
            );
        }

        if (self::is_account_page()) {
            $css_file = dirname(dirname(__FILE__)) . '/assets/css/mr-account.css';
            $js_file  = dirname(dirname(__FILE__)) . '/assets/js/mr-account.js';

            $css_ver = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
            $js_ver  = file_exists($js_file) ? filemtime($js_file) : '1.0.0';

            wp_enqueue_style(
                'mr-account-css',
                plugins_url('assets/css/mr-account.css', dirname(__FILE__)),
                ['mr-font-awesome', 'mr-brand-fonts'],
                $css_ver
            );

            $bg_custom_css = "
                body.mr-account-page-active,
                body.page-id-7235,
                body:has(.mr-account-dashboard),
                .mr-account-page-active #page,
                .mr-account-page-active .site,
                .mr-account-page-active .site-content,
                .mr-account-page-active #primary,
                .mr-account-page-active #main,
                .mr-account-page-active .ast-container,
                .mr-account-page-active .entry-content,
                .mr-account-page-active .ast-separate-container,
                .mr-account-page-active .ast-separate-container .ast-article-single,
                .mr-account-page-active .ast-separate-container .ast-article-post,
                .mr-account-page-wrapper,
                .mr-account-dashboard {
                    background-color: #FDF8F1 !important;
                    background: #FDF8F1 !important;
                }

                /* Prevent theme container horizontal offset & ensure 100% centering on mobile */
                html, body.mr-account-page-active {
                    overflow-x: hidden !important;
                    max-width: 100% !important;
                }
                body.mr-account-page-active #page,
                body.mr-account-page-active .site-content,
                body.mr-account-page-active #primary,
                body.mr-account-page-active #main {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    box-sizing: border-box !important;
                    overflow-x: hidden !important;
                }
                body.mr-account-page-active .ast-container,
                body.mr-account-page-active .entry-content,
                body.mr-account-page-active .ast-separate-container .ast-article-single,
                body.mr-account-page-active .elementor-7235,
                body.mr-account-page-active .elementor-element-aa14421 {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-left: auto !important;
                    margin-right: auto !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }
            ";
            wp_add_inline_style('mr-account-css', $bg_custom_css);

            wp_enqueue_script(
                'mr-account-js',
                plugins_url('assets/js/mr-account.js', dirname(__FILE__)),
                ['jquery'],
                $js_ver,
                true
            );

            // Google Maps Places API is loaded on-demand via Snippet #13 when city field is interacted with.
            // Static enqueue removed to eliminate 394 KB JS overhead on page loads.

            $user_id = get_current_user_id();
            $account_url = self::get_account_page_url();
            $loader_url = content_url('/uploads/2025/11/loader.webp');

            wp_localize_script('mr-account-js', 'mr_account_vars', [
                'ajax_url'     => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('mr_account_nonce'),
                'is_logged_in' => is_user_logged_in(),
                'account_url'  => $account_url,
                'loader_url'   => $loader_url,
                'login_url'    => wp_login_url($account_url),
                'logout_url'   => wp_logout_url($account_url),
                'user_id'      => $user_id,
            ]);
        }
    }

    /**
     * Inject Accounts Shortcut Icon in Top Menu Bar
     */
    public static function inject_account_menu_item($items, $args) {
        // Only target the main menu or primary nav location
        $target_menu = false;
        if (is_object($args)) {
            if ((isset($args->theme_location) && in_array($args->theme_location, ['primary', 'main-menu'])) ||
                (isset($args->menu) && (in_array($args->menu, ['main-menu', 'Main Menu', '255', 255]))) ||
                (isset($args->menu_id) && strpos($args->menu_id, 'main-menu') !== false)) {
                $target_menu = true;
            }
        }

        // If no specific target matched, but it's the main header navigation
        if (!$target_menu && is_object($args) && isset($args->menu_class) && strpos($args->menu_class, 'elementor-nav-menu') !== false) {
            $target_menu = true;
        }

        if (!$target_menu) {
            return $items;
        }

        // Avoid double injection
        if (strpos($items, 'mr-nav-account-item') !== false) {
            return $items;
        }

        $account_url = self::get_account_page_url();

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $first_name = !empty($current_user->user_firstname) ? $current_user->user_firstname : explode(' ', $current_user->display_name)[0];
            $avatar_url = get_avatar_url($current_user->ID, ['size' => 48]);
            $logout_url = wp_logout_url(self::get_account_page_url());

            $account_item = '
            <li id="menu-item-mr-account" class="menu-item menu-item-has-children mr-nav-account-item">
                <a href="' . esc_url($account_url) . '" class="menu-link mr-nav-account-link" title="My Astro Account">
                    <span class="mr-nav-avatar-wrap">
                        <img src="' . esc_url($avatar_url) . '" alt="' . esc_attr($first_name) . '" class="mr-nav-avatar-img" />
                    </span>
                    <span class="mr-nav-account-label">' . esc_html($first_name) . '</span>
                    <span role="button" class="dropdown-menu-toggle ast-header-navigation-arrow" tabindex="0" aria-label="Account Menu Toggle"></span>
                </a>
                <ul class="sub-menu mr-nav-account-submenu">
                    <li class="menu-item mr-sub-user-header">
                        <div class="mr-user-mini-card">
                            <img src="' . esc_url($avatar_url) . '" alt="" class="mr-mini-avatar" />
                            <div class="mr-mini-info">
                                <strong class="mr-mini-name">' . esc_html($current_user->display_name) . '</strong>
                                <span class="mr-mini-email">' . esc_html($current_user->user_email) . '</span>
                            </div>
                        </div>
                    </li>
                    <li class="menu-item"><a href="' . esc_url($account_url . '#astrology') . '" class="menu-link">My Profile &amp; Kundali</a></li>
                    <li class="menu-item"><a href="' . esc_url($account_url . '#reports') . '" class="menu-link">My Generated Reports</a></li>
                    <li class="menu-item"><a href="' . esc_url($account_url . '#passbook') . '" class="menu-link">Transaction Passbook</a></li>
                    <li class="menu-item"><a href="' . esc_url($account_url . '#settings') . '" class="menu-link">Settings</a></li>
                    <li class="menu-item"><a href="' . esc_url($account_url . '#contact') . '" class="menu-link">Contact Us</a></li>
                    <li class="menu-item mr-menu-divider"></li>
                    <li class="menu-item"><a href="' . esc_url($logout_url) . '" class="menu-link mr-nav-logout-btn">Log Out</a></li>
                </ul>
            </li>';
        } else {
            // Logged out shortcut: redirect to /my-account/ preserving customer origin destination
            $curr = !is_admin() && !empty($_SERVER['REQUEST_URI']) ? home_url($_SERVER['REQUEST_URI']) : $account_url;
            $clean_curr = trim(parse_url($curr, PHP_URL_PATH) ?? '', '/');
            $clean_acc  = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');
            $signin_url = ($clean_curr !== $clean_acc && stripos($clean_curr, 'wp-login') === false && stripos($clean_curr, 'wp-admin') === false)
                ? add_query_arg('redirect_to', $curr, $account_url)
                : $account_url;

            $account_item = '
            <li id="menu-item-mr-account" class="menu-item mr-nav-account-item mr-nav-logged-out">
                <a href="' . esc_url($signin_url) . '" class="menu-link mr-nav-account-link" title="Sign In / Astro Account">
                    <span class="mr-nav-account-label">Sign In</span>
                </a>
            </li>';
        }

        return $items . $account_item;
    }

    /**
     * Get Direct Google OAuth Login Action URL
     * Bypasses WordPress login form completely while preserving target return URL
     */
    public static function get_login_action_url($redirect_to = '') {
        $account_url = self::get_account_page_url();
        if (empty($redirect_to)) {
            if (!empty($_GET['redirect_to'])) {
                $redirect_to = esc_url_raw($_GET['redirect_to']);
            } elseif (!empty($_GET['redirect'])) {
                $redirect_to = esc_url_raw($_GET['redirect']);
            } elseif (!empty($_COOKIE['mr_login_return_url'])) {
                $redirect_to = esc_url_raw($_COOKIE['mr_login_return_url']);
            } elseif (!is_admin() && !empty($_SERVER['REQUEST_URI'])) {
                $curr = home_url($_SERVER['REQUEST_URI']);
                $clean_curr = trim(parse_url($curr, PHP_URL_PATH) ?? '', '/');
                $clean_acc  = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');
                if ($clean_curr !== $clean_acc && stripos($clean_curr, 'wp-login') === false && stripos($clean_curr, 'wp-admin') === false) {
                    $redirect_to = $curr;
                }
            }
        }
        $target = !empty($redirect_to) ? $redirect_to : $account_url;

        // Direct Nextend Social Login Google OAuth endpoint
        $login_base = site_url('wp-login.php', 'login');
        if (class_exists('NextendSocialLogin')) {
            $nsl_url = NextendSocialLogin::getLoginUrl();
            if (!empty($nsl_url)) {
                $login_base = $nsl_url;
            }
        }
        return add_query_arg([
            'loginSocial' => 'google',
            'redirect'    => $target
        ], $login_base);
    }

    /**
     * Filter wp_login_url() for frontend customer flows
     * Redirects frontend customers to Google OAuth with return destination, while preserving standard login for /wp-admin
     */
    public static function filter_login_url($login_url, $redirect = '', $force_reauth = false) {
        // 1. If this login request is for WordPress Admin (/wp-admin/), DO NOT intercept!
        // Allow the administrator to access the normal WordPress login page.
        if (!empty($redirect) && (stripos($redirect, 'wp-admin') !== false || stripos($redirect, 'admin') !== false)) {
            return $login_url;
        }
        if (is_admin() || (isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], 'wp-admin') !== false)) {
            return $login_url;
        }

        // 2. Only for frontend / customer redirect targets:
        $account_url = self::get_account_page_url();
        $target = !empty($redirect) ? $redirect : (!empty($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : (!empty($_GET['redirect']) ? esc_url_raw($_GET['redirect']) : (!empty($_COOKIE['mr_login_return_url']) ? esc_url_raw($_COOKIE['mr_login_return_url']) : $account_url)));

        $login_base = site_url('wp-login.php', 'login');
        if (class_exists('NextendSocialLogin')) {
            $nsl_url = NextendSocialLogin::getLoginUrl();
            if (!empty($nsl_url)) {
                $login_base = $nsl_url;
            }
        }
        return add_query_arg([
            'loginSocial' => 'google',
            'redirect'    => $target
        ], $login_base);
    }

    /**
     * Strict Barrier: Disallow Non-Admin / Subscriber Customers from accessing WP Admin
     * Redirects any non-administrative customer to /my-account/
     */
    public static function restrict_admin_area_access() {
        // 1. Never block background operations: AJAX, Cron, REST API, CLI, or XML-RPC
        if (wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST) || (defined('WP_CLI') && WP_CLI) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
            return;
        }

        // 2. Allow logout action through unimpeded
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'logout') {
            return;
        }

        // 3. Check if current request is inside WP Admin environment
        $is_in_admin = is_admin();
        if (!$is_in_admin && isset($_SERVER['REQUEST_URI'])) {
            $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($uri_path && strpos($uri_path, '/wp-admin') !== false) {
                $is_in_admin = true;
            }
        }

        if ($is_in_admin && is_user_logged_in()) {
            // Check if user has administrative or editorial staff privileges
            if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
                // Customer / Subscriber attempting to enter WP Admin
                wp_safe_redirect(self::get_account_page_url());
                exit;
            }
        }
    }

    /**
     * Filter login redirect: ensure customers land back on the exact page they came from (reports, pricing, catalog)
     * and only land on /my-account/ if no specific service was requested.
     */
    public static function filter_login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (is_a($user, 'WP_User')) {
            // Staff / Administrators -> follow normal admin redirect
            if (user_can($user, 'manage_options') || user_can($user, 'edit_posts')) {
                return $redirect_to;
            }

            // Customer / Subscriber flow:
            // Check if user came from a specific service page (report, pricing, catalog, etc.)
            $target = '';
            if (!empty($requested_redirect_to) && stripos($requested_redirect_to, 'wp-admin') === false && stripos($requested_redirect_to, 'wp-login') === false) {
                $target = $requested_redirect_to;
            } elseif (!empty($_REQUEST['redirect_to']) && stripos($_REQUEST['redirect_to'], 'wp-admin') === false) {
                $target = esc_url_raw($_REQUEST['redirect_to']);
            } elseif (!empty($_REQUEST['redirect']) && stripos($_REQUEST['redirect'], 'wp-admin') === false) {
                $target = esc_url_raw($_REQUEST['redirect']);
            } elseif (!empty($_COOKIE['mr_login_return_url'])) {
                $target = esc_url_raw($_COOKIE['mr_login_return_url']);
            } elseif (!empty($redirect_to) && stripos($redirect_to, 'wp-admin') === false && stripos($redirect_to, 'wp-login') === false) {
                $target = $redirect_to;
            }

            if (!empty($target)) {
                if (strpos($target, '%') !== false) {
                    $target = rawurldecode($target);
                }
                $account_url = self::get_account_page_url();
                $clean_target  = trim(parse_url($target, PHP_URL_PATH) ?? '', '/');
                $clean_account = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');

                // If target is NOT the account page, redirect customer directly back to their service page!
                if (!empty($clean_target) && $clean_target !== $clean_account && stripos($clean_target, 'wp-login') === false && stripos($clean_target, 'wp-admin') === false) {
                    if (isset($_COOKIE['mr_login_return_url']) && !headers_sent()) {
                        setcookie('mr_login_return_url', '', time() - 3600, '/');
                    }
                    return $target;
                }
            }

            return self::get_account_page_url();
        }
        return $redirect_to;
    }

    /**
     * Filter Nextend Social Login redirect URLs to prioritize customer origin service page
     */
    public static function filter_nsl_redirect($redirect, $provider = null) {
        $target = '';
        if (!empty($_GET['redirect_to'])) {
            $target = esc_url_raw($_GET['redirect_to']);
        } elseif (!empty($_GET['redirect'])) {
            $target = esc_url_raw($_GET['redirect']);
        } elseif (!empty($_COOKIE['mr_login_return_url'])) {
            $target = esc_url_raw($_COOKIE['mr_login_return_url']);
        }

        if (!empty($target)) {
            if (strpos($target, '%') !== false) {
                $target = rawurldecode($target);
            }
            $account_url = self::get_account_page_url();
            $clean_target  = trim(parse_url($target, PHP_URL_PATH) ?? '', '/');
            $clean_account = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');
            if (!empty($clean_target) && $clean_target !== $clean_account && stripos($clean_target, 'wp-login') === false && stripos($clean_target, 'wp-admin') === false) {
                if (isset($_COOKIE['mr_login_return_url']) && !headers_sent()) {
                    setcookie('mr_login_return_url', '', time() - 3600, '/');
                }
                return $target;
            }
        }
        return $redirect;
    }

    /**
     * Immediately bounce logged-in users from /my-account/ back to their originating service page
     */
    public static function check_login_return_redirect() {
        if (!is_user_logged_in() || is_admin()) {
            return;
        }

        // Only act if on /my-account/
        if (!self::is_account_page()) {
            return;
        }

        $return_url = '';
        if (!empty($_GET['redirect_to'])) {
            $return_url = esc_url_raw($_GET['redirect_to']);
        } elseif (!empty($_GET['redirect'])) {
            $return_url = esc_url_raw($_GET['redirect']);
        } elseif (!empty($_COOKIE['mr_login_return_url'])) {
            $return_url = esc_url_raw($_COOKIE['mr_login_return_url']);
        }

        if (!empty($return_url)) {
            if (strpos($return_url, '%') !== false) {
                $return_url = rawurldecode($return_url);
            }
            $account_url = self::get_account_page_url();
            $clean_ret = trim(parse_url($return_url, PHP_URL_PATH) ?? '', '/');
            $clean_acc = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');

            if (!empty($clean_ret) && $clean_ret !== $clean_acc && stripos($clean_ret, 'wp-login') === false && stripos($clean_ret, 'wp-admin') === false) {
                if (isset($_COOKIE['mr_login_return_url']) && !headers_sent()) {
                    setcookie('mr_login_return_url', '', time() - 3600, '/');
                }
                wp_safe_redirect($return_url);
                exit;
            }
        }
    }

    /**
     * Filter WordPress Admin Bar: strictly hide for non-admin customers
     */
    public static function filter_show_admin_bar($show) {
        if (!is_user_logged_in()) {
            return false;
        }
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            return false;
        }
        return $show;
    }

    /**
     * Suppress default WordPress "Set your password" registration email for customers and social login users
     * Customers authenticate via 1-Click Google OAuth and do not use or need a WordPress password.
     */
    public static function disable_customer_new_user_email($wp_new_user_notification_email, $user, $blogname) {
        // Allow notifications if an administrator explicitly creates an editor/admin inside /wp-admin/
        if (is_admin() && $user && (user_can($user, 'manage_options') || user_can($user, 'edit_posts'))) {
            return $wp_new_user_notification_email;
        }
        // Suppress completely for all customers, social signups, and frontend registrations
        return false;
    }

    /**
     * Prevent WordPress Default Login Page from Displaying for Customers
     * Customers are routed to Google OAuth or Account Page, while administrators can access wp-admin
     */
    public static function prevent_wp_login_display() {
        // 1. Allow Nextend Social Login OAuth callback & handshake
        if (!empty($_REQUEST['loginSocial']) || (isset($_GET['interim_login']) && $_GET['interim_login'] === 'nsl')) {
            return;
        }

        // 2. CRITICAL: Allow standard WordPress actions (logout, postpass, lostpassword, resetpass, rp, etc.)
        // Must be checked BEFORE any logged-in check because the user is still authenticated when clicking "Log Out"
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

        // If a customer/subscriber hits the reset password screen (action=rp, resetpass, lostpassword), redirect them to /my-account/
        if (in_array($action, ['resetpass', 'rp', 'lostpassword', 'retrievepassword'], true)) {
            $user_login = isset($_GET['login']) ? sanitize_user($_GET['login']) : '';
            if (!empty($user_login)) {
                $check_user = get_user_by('login', $user_login);
                if ($check_user && !user_can($check_user, 'manage_options') && !user_can($check_user, 'edit_posts')) {
                    wp_safe_redirect(self::get_account_page_url());
                    exit;
                }
            } elseif (is_user_logged_in() && !current_user_can('manage_options') && !current_user_can('edit_posts')) {
                wp_safe_redirect(self::get_account_page_url());
                exit;
            } elseif (!is_user_logged_in() && empty($user_login)) {
                // If not an admin trying to reset, bounce to account login
                wp_safe_redirect(self::get_account_page_url());
                exit;
            }
        }

        if (in_array($action, ['logout', 'postpass', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'confirmaction'], true)) {
            return;
        }

        // 3. If user is ALREADY LOGGED IN as a customer/subscriber, never let them see wp-login.php or route into wp-admin
        if (is_user_logged_in()) {
            if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
                wp_safe_redirect(self::get_account_page_url());
                exit;
            }
        }

        // 4. Allow form submission (POST) so admin can log in with username and password
        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['log']) || isset($_POST['pwd']))) {
            return;
        }

        // 5. ADMIN ACCESS: If visiting /wp-admin/ or redirect_to points to wp-admin, DO NOT block wp-login.php!
        $redirect_to = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
        if (!empty($redirect_to) && (stripos($redirect_to, 'wp-admin') !== false || stripos($redirect_to, 'admin') !== false)) {
            return; // Allow standard WP login page to show for admin
        }

        // 6. Allow explicit admin query flags or referer from wp-admin
        if (isset($_GET['admin']) || isset($_GET['wp_admin']) || isset($_GET['admin_login']) || isset($_GET['merarashifal_admin'])) {
            return;
        }
        if (!empty($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], 'wp-admin') !== false) {
            return;
        }

        // 7. Direct wp-login.php visit without any redirect_to (likely admin accessing login page)
        if (empty($redirect_to)) {
            if (is_user_logged_in()) {
                if (current_user_can('manage_options') || current_user_can('edit_posts')) {
                    wp_safe_redirect(admin_url());
                    exit;
                } else {
                    wp_safe_redirect(self::get_account_page_url());
                    exit;
                }
            }
            // Allow WP login page to display so administrator can log in
            return;
        }

        // 8. If already logged in:
        if (is_user_logged_in()) {
            if (current_user_can('manage_options') || current_user_can('edit_posts')) {
                wp_safe_redirect(admin_url());
                exit;
            }
            wp_safe_redirect(self::get_account_page_url());
            exit;
        }

        // 9. CUSTOMERS / FRONTEND USERS:
        // Only if redirect_to points to a frontend customer URL (e.g. /my-account/), redirect to Google OAuth
        if (class_exists('NextendSocialLogin')) {
            $login_base = site_url('wp-login.php', 'login');
            $nsl_url = NextendSocialLogin::getLoginUrl();
            if (!empty($nsl_url)) {
                $login_base = $nsl_url;
            }
            $google_oauth_url = add_query_arg([
                'loginSocial' => 'google',
                'redirect'    => $redirect_to
            ], $login_base);

            wp_redirect($google_oauth_url);
            exit;
        }

        // Fallback for customer: redirect to Account page
        wp_safe_redirect(self::get_account_page_url());
        exit;
    }

    /**
     * Handle user logout cleanly: redirect to requested target or account sign-in page without showing WP login page
     */
    public static function on_user_logout() {
        $redirect = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : self::get_account_page_url();
        wp_safe_redirect($redirect);
        exit;
    }


    /**
     * Render the Full Accounts Page via Shortcode
     * [mr_user_account] / [mr_account]
     */
    public static function render_account_page($atts = []) {
        nocache_headers();
        if (!is_user_logged_in()) {
            return self::render_logged_out_view();
        }

        // If user just completed login and came with a redirect_to or a return cookie, bounce them back to their service page!
        $return_url = '';
        if (!empty($_GET['redirect_to'])) {
            $return_url = esc_url_raw($_GET['redirect_to']);
        } elseif (!empty($_GET['redirect'])) {
            $return_url = esc_url_raw($_GET['redirect']);
        } elseif (!empty($_COOKIE['mr_login_return_url'])) {
            $return_url = esc_url_raw($_COOKIE['mr_login_return_url']);
        }

        if (!empty($return_url)) {
            $account_url = self::get_account_page_url();
            $clean_ret = trim(parse_url($return_url, PHP_URL_PATH) ?? '', '/');
            $clean_acc = trim(parse_url($account_url, PHP_URL_PATH) ?? '', '/');
            if (!empty($clean_ret) && $clean_ret !== $clean_acc && stripos($clean_ret, 'wp-login') === false && stripos($clean_ret, 'wp-admin') === false) {
                if (isset($_COOKIE['mr_login_return_url']) && !headers_sent()) {
                    setcookie('mr_login_return_url', '', time() - 3600, '/');
                }
                wp_safe_redirect($return_url);
                exit;
            }
        }

        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        // Retrieve user astrological metadata
        $astro_name   = get_user_meta($user_id, 'mr_astro_name', true) ?: $user->display_name;
        $astro_gender = get_user_meta($user_id, 'mr_astro_gender', true) ?: 'Male';
        $astro_dob    = get_user_meta($user_id, 'mr_astro_dob', true) ?: '';
        $astro_tob    = get_user_meta($user_id, 'mr_astro_tob', true) ?: '';
        $astro_pob    = get_user_meta($user_id, 'mr_astro_pob', true) ?: '';
        $astro_lat    = get_user_meta($user_id, 'mr_astro_lat', true) ?: '';
        $astro_lon    = get_user_meta($user_id, 'mr_astro_lon', true) ?: '';
        $astro_tz     = get_user_meta($user_id, 'mr_astro_tz', true) ?: '5.5';
        $user_phone   = get_user_meta($user_id, 'user_phone', true) ?: (get_user_meta($user_id, 'billing_phone', true) ?: '');

        // User preferences
        $pref_chart  = get_user_meta($user_id, 'mr_pref_chart', true) ?: 'north_indian';
        $pref_system = get_user_meta($user_id, 'mr_pref_system', true) ?: 'vedic';
        $pref_notify = get_user_meta($user_id, 'mr_pref_notify', true) ?: '1';

        // DPDP Act 2023 - Data Principal Nominee & Consent preferences
        $nominee_name     = get_user_meta($user_id, 'mr_dpdp_nominee_name', true) ?: '';
        $nominee_email    = get_user_meta($user_id, 'mr_dpdp_nominee_email', true) ?: '';
        $nominee_phone    = get_user_meta($user_id, 'mr_dpdp_nominee_phone', true) ?: '';
        $nominee_relation = get_user_meta($user_id, 'mr_dpdp_nominee_relation', true) ?: '';
        $consent_profile  = get_user_meta($user_id, 'mr_dpdp_consent_profiling', true) !== '0' ? '1' : '0';
        $consent_ai       = get_user_meta($user_id, 'mr_dpdp_consent_ai_chat', true) !== '0' ? '1' : '0';
        $consent_comms    = get_user_meta($user_id, 'mr_dpdp_consent_comms', true) !== '0' ? '1' : '0';

        // DPDP Act 2023 - Cookie & Storage Preferences
        $cookie_personalization = get_user_meta($user_id, 'mr_cookie_personalization', true) !== '0' ? '1' : '0';
        $cookie_analytics       = get_user_meta($user_id, 'mr_cookie_analytics', true) !== '0' ? '1' : '0';
        $cookie_marketing       = get_user_meta($user_id, 'mr_cookie_marketing', true) !== '0' ? '1' : '0';

        // DPDP Erasure Dynamic Security Challenge (Anti-bot word & 50-sentence pool)
        $erase_challenge  = self::generate_dpdp_erase_challenge($user_id);

        // Calculate Vedic Sun / Moon Sign if DOB is available
        $calculated_rashi = !empty($astro_dob) ? self::calculate_vedic_sun_sign($astro_dob) : null;

        // Fetch user reports
        global $wpdb;
        $table_reports = $wpdb->prefix . 'mr_generated_reports';
        $reports = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_reports 
            WHERE user_id = %d 
            ORDER BY id DESC
        ", $user_id));

        // Fetch Niyati AI Astrologer Credit Wallet stats
        $niyati_wallet = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_wallet('', $user_id) : null;
        $niyati_balance   = $niyati_wallet ? intval($niyati_wallet->credits_balance) : 0;
        $niyati_purchased = $niyati_wallet ? intval($niyati_wallet->total_purchased) : 0;
        $niyati_consumed  = $niyati_wallet ? intval($niyati_wallet->total_consumed) : 0;
        $niyati_validity  = ($niyati_wallet && !empty($niyati_wallet->validity_expires_at) && $niyati_wallet->validity_expires_at !== '0000-00-00 00:00:00') ? date('d M Y', strtotime($niyati_wallet->validity_expires_at)) : 'Lifetime';

        // Fetch unified ledger & load initial 25 entries for initial view (zero DOM bloat)
        $unified_ledger       = self::get_user_unified_ledger($user_id);
        $passbook_data        = self::filter_and_paginate_ledger($unified_ledger, 'all', 1, 25);
        $count_all_ledger     = $passbook_data['counts']['all'];
        $count_credits_ledger = $passbook_data['counts']['credits'];
        $count_orders_ledger  = $passbook_data['counts']['orders'];

        // User transactions for spent stats
        $table_txns = $wpdb->prefix . 'mr_transactions';
        $txns = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_txns 
            WHERE user_id = %d OR customer_email = %s 
            ORDER BY id DESC
        ", $user_id, $user->user_email));

        // Calculate transaction statistics
        $total_spent = 0;
        $total_success = 0;
        foreach ($txns as $t) {
            if ($t->status === 'success') {
                $total_spent += floatval($t->net_amount);
                $total_success++;
            }
        }

        // Active Subscription check
        $active_sub = class_exists('MR_Tokens') ? MR_Tokens::get_active_subscription($user_id) : null;
        $avatar_url = get_avatar_url($user_id, ['size' => 72]);
        $logout_url = wp_logout_url(self::get_account_page_url());
        $loader_url = content_url('/uploads/2025/11/loader.webp');

        ob_start();
        ?>
        <div class="mr-account-page-wrapper" style="background-color: #FFFBEB !important; width: 100%; min-height: 80vh; padding: 15px 0;">
            <style>
                body, #page, .site, .site-content, #primary, #main, .ast-container, .entry-content, .ast-separate-container, .ast-separate-container .ast-article-single, .ast-separate-container .ast-article-post, .mr-account-page-wrapper, .mr-account-dashboard {
                    background-color: #FFFBEB !important;
                    background: #FFFBEB !important;
                }
            </style>
            <div class="mr-account-dashboard">
            
            <!-- On-Page SEO Breadcrumb -->
            <nav class="mr-account-breadcrumb" aria-label="Breadcrumb">
                <ol class="mr-breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <li class="mr-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="item" class="mr-breadcrumb-link">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <span itemprop="name">Home</span>
                        </a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <li class="mr-breadcrumb-sep" aria-hidden="true">&#8250;</li>
                    <li class="mr-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="<?php echo esc_url(self::get_account_page_url()); ?>" itemprop="item" class="mr-breadcrumb-link">
                            <span itemprop="name">Astro Account</span>
                        </a>
                        <meta itemprop="position" content="2" />
                    </li>
                    <li class="mr-breadcrumb-sep" aria-hidden="true">&#8250;</li>
                    <li class="mr-breadcrumb-item mr-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span itemprop="name" id="mr-breadcrumb-active-label">My Reports</span>
                        <meta itemprop="position" content="3" />
                    </li>
                </ol>
            </nav>

            <!-- Hero Profile Header (Compact) -->
            <div class="mr-account-hero-card">
                <div class="mr-hero-profile-row">
                    <div class="mr-hero-avatar-area">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" class="mr-hero-avatar-img" />
                        <span class="mr-online-indicator" title="Connected via Google OAuth"></span>
                    </div>

                    <div class="mr-hero-details">
                        <div class="mr-hero-badges-row">
                            <?php if ($active_sub): ?>
                                <span class="mr-badge-vip"><?php echo esc_html($active_sub->pack_name); ?></span>
                            <?php else: ?>
                                <span class="mr-badge-standard">Verified Member</span>
                            <?php endif; ?>
                            <span class="mr-badge-oauth">Google Linked</span>
                        </div>
                        <h2 class="mr-hero-name"><?php echo esc_html($user->display_name); ?></h2>
                        <p class="mr-hero-email">
                            <?php echo esc_html($user->user_email); ?>
                            <?php if (!empty($user_phone)): ?>
                                &bull; <?php echo esc_html($user_phone); ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Header Action Buttons: Contact Us, Settings, Log Out -->
                    <div class="mr-hero-actions">
                        <button type="button" class="mr-btn-action-pill mr-btn-contact" id="mr-tab-btn-contact-trigger" title="Reach Astro Support & Consultation">
                            Contact Us
                        </button>
                        <button type="button" class="mr-btn-action-pill mr-btn-settings" id="mr-tab-btn-settings-trigger" title="Account &amp; DPDP Privacy Preferences">
                            Settings &amp; Privacy
                        </button>
                        <a href="<?php echo esc_url($logout_url); ?>" class="mr-btn-action-pill mr-btn-logout" title="Safely log out of your session">
                            Log Out
                        </a>
                    </div>
                </div>

                <!-- Navigation Tabs Bar -->
                <div class="mr-account-nav-tabs">
                    <button type="button" class="mr-tab-btn active" data-tab="astrology">
                        <span>Astrological Details</span>
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="reports">
                        <span>My Reports</span>
                        <span class="mr-tab-count"><?php echo count($reports); ?></span>
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="passbook">
                        <span>Transaction Passbook</span>
                        <span class="mr-tab-count"><?php echo count($unified_ledger); ?></span>
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="settings">
                        <span>Settings &amp; Privacy</span>
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="contact">
                        <span>Contact Us</span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: Astrological & Personal Details -->
            <div class="mr-account-tab-pane active" id="mr-pane-astrology">
                <div class="mr-pane-grid">
                    
                    <!-- Left: Form to manage details -->
                    <div class="mr-pane-col-main">
                        <div class="mr-account-card">
                            <div class="mr-card-header">
                                <div class="mr-card-icon-badge mr-loader-badge">
                                    <img src="<?php echo esc_url($loader_url); ?>" alt="MeraRashifal" class="mr-spin-loader" />
                                </div>
                                <div>
                                    <h3 class="mr-card-title">Vedic Astrological &amp; Birth Details</h3>
                                    <p class="mr-card-sub">Saved coordinates are automatically pre-filled into all your horoscope and Kundali reports.</p>
                                </div>
                            </div>

                            <form id="mr-astro-details-form" class="mr-form-styled">
                                <div class="mr-form-row mr-cols-2">
                                    <div class="mr-form-group">
                                        <label for="mr_account_name">Display / Account Name *</label>
                                        <div class="mr-input-wrap">
                                            <input type="text" id="mr_account_name" name="account_name" value="<?php echo esc_attr($user->display_name); ?>" required />
                                        </div>
                                    </div>
                                    <div class="mr-form-group">
                                        <label for="mr_account_email">Registered Email (Google)</label>
                                        <div class="mr-input-wrap">
                                            <input type="email" id="mr_account_email" value="<?php echo esc_attr($user->user_email); ?>" disabled class="mr-input-readonly" />
                                        </div>
                                        <small class="mr-input-hint">Synced with Google Account</small>
                                    </div>
                                </div>

                                <div class="mr-form-row mr-cols-2">
                                    <div class="mr-form-group">
                                        <label for="mr_user_phone">Phone / WhatsApp Number</label>
                                        <div class="mr-input-wrap">
                                            <input type="text" id="mr_user_phone" name="user_phone" value="<?php echo esc_attr($user_phone); ?>" placeholder="+91 9876543210" />
                                        </div>
                                    </div>
                                    <div class="mr-form-group">
                                        <label for="mr_astro_gender">Gender for Chart Calculations *</label>
                                        <div class="mr-input-wrap">
                                            <select id="mr_astro_gender" name="astro_gender">
                                                <option value="Male" <?php selected($astro_gender, 'Male'); ?>>Male</option>
                                                <option value="Female" <?php selected($astro_gender, 'Female'); ?>>Female</option>
                                                <option value="Other" <?php selected($astro_gender, 'Other'); ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mr-form-divider">
                                    <span>Birth Coordinates (जन्म विवरण)</span>
                                </div>

                                <!-- Compact 3-Column Birth Triad (DOB | TOB | POB) -->
                                <div class="mr-form-row mr-cols-3">
                                    <div class="mr-form-group">
                                        <label for="mr_astro_dob">Date of Birth (जन्म तिथि) *</label>
                                        <div class="mr-input-wrap">
                                            <input type="date" id="mr_astro_dob" name="astro_dob" value="<?php echo esc_attr($astro_dob); ?>" required />
                                        </div>
                                    </div>
                                    <div class="mr-form-group">
                                        <label for="mr_astro_tob">Time of Birth (जन्म समय) *</label>
                                        <div class="mr-input-wrap">
                                            <input type="time" id="mr_astro_tob" name="astro_tob" value="<?php echo esc_attr($astro_tob); ?>" step="60" required />
                                        </div>
                                    </div>
                                    <div class="mr-form-group">
                                        <label for="mr_astro_pob">Place of Birth (जन्म स्थान) *</label>
                                        <div class="mr-input-wrap mr-pob-search-container mr-city-input-wrap" style="position:relative;">
                                            <input type="text" id="mr_astro_pob" name="astro_pob" value="<?php echo esc_attr($astro_pob); ?>" placeholder="City, State, Country..." autocomplete="off" required class="mr-city-input" />
                                            <span class="mr-city-loader" id="mr_astro_pob_loader"><span class="mr-city-spinner-ring"></span></span>
                                            <div id="mr_pob_dropdown" class="mr-pob-dropdown"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden Geocoded Coordinates -->
                                <input type="hidden" id="mr_astro_lat" name="astro_lat" value="<?php echo esc_attr($astro_lat); ?>" />
                                <input type="hidden" id="mr_astro_lon" name="astro_lon" value="<?php echo esc_attr($astro_lon); ?>" />
                                <input type="hidden" id="mr_astro_tz" name="astro_tz" value="<?php echo esc_attr($astro_tz); ?>" />

                                <!-- Coordinates Status Pill -->
                                <div class="mr-coords-status" id="mr-coords-status" style="<?php echo empty($astro_lat) ? 'display:none;' : ''; ?>">
                                    <div class="mr-coords-pill">
                                        <span>Latitude: <strong id="mr-display-lat"><?php echo esc_html($astro_lat); ?></strong></span>
                                        &bull;
                                        <span>Longitude: <strong id="mr-display-lon"><?php echo esc_html($astro_lon); ?></strong></span>
                                        &bull;
                                        <span>Timezone: <strong id="mr-display-tz">GMT +<?php echo esc_html($astro_tz); ?></strong></span>
                                    </div>
                                </div>

                                <div class="mr-form-actions">
                                    <button type="submit" class="mr-btn-glow-gold" id="mr-btn-save-astro">
                                        Save Astrological Details
                                    </button>
                                    <span id="mr-save-feedback" class="mr-save-feedback"></span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Sidebar: Astrological Snapshot & Quick Cards -->
                    <div class="mr-pane-col-side">
                        
                        <!-- Rashi Snapshot Card -->
                        <div class="mr-account-card mr-card-snapshot">
                            <div class="mr-snapshot-header">
                                <img src="<?php echo esc_url($loader_url); ?>" alt="Vedic Astro" class="mr-snapshot-loader-img mr-spin-loader" />
                                <h4>Astrological Snapshot</h4>
                            </div>
                            <div class="mr-snapshot-body">
                                <div class="mr-snapshot-item">
                                    <span class="mr-snap-label">Solar Sign (सूर्य राशि):</span>
                                    <strong class="mr-snap-val" id="mr-snap-rashi"><?php echo esc_html($calculated_rashi['name'] ?? 'Set DOB to calculate'); ?></strong>
                                </div>
                                <div class="mr-snapshot-item">
                                    <span class="mr-snap-label">Element (तत्व):</span>
                                    <strong class="mr-snap-val" id="mr-snap-elem"><?php echo esc_html($calculated_rashi['element'] ?? '—'); ?></strong>
                                </div>
                                <div class="mr-snapshot-item">
                                    <span class="mr-snap-label">Ruling Planet (स्वामी ग्रह):</span>
                                    <strong class="mr-snap-val" id="mr-snap-ruler"><?php echo esc_html($calculated_rashi['ruler'] ?? '—'); ?></strong>
                                </div>
                                <div class="mr-snapshot-item">
                                    <span class="mr-snap-label">Ayanamsha System:</span>
                                    <strong class="mr-snap-val">Vedic Lahiri (Chitra Paksha)</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Instant Action / Reports shortcut -->
                        <div class="mr-account-card mr-card-cta">
                            <h4>Need an In-depth Reading?</h4>
                            <p>Generate high-precision Kundali, Mahadasha, Dosh Remedial, or Yearly Horoscope PDF reports.</p>
                            <a href="<?php echo esc_url(home_url('/kundali-details/?ku_type=kundali-details-report')); ?>" class="mr-btn-glow-gold" style="display:block; text-align:center; margin-top:10px; text-decoration:none;">
                                Generate Kundali Report &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 2: My Generated Reports Archive -->
            <div class="mr-account-tab-pane" id="mr-pane-reports">
                <div class="mr-account-card">
                    <div class="mr-card-header mr-flex-between">
                        <div class="mr-flex-start">
                            <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                            <div>
                                <h3 class="mr-card-title">Generated Astrological Reports Archive</h3>
                                <p class="mr-card-sub">Access and download all your personalized Vedic &amp; Western PDF reports generated till now.</p>
                            </div>
                        </div>
                        <a href="<?php echo esc_url(home_url('/reports-catalog/')); ?>" class="mr-btn-header-cta" title="Generate New Astrological Report">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Generate New Report</span>
                        </a>
                    </div>

                    <!-- Available Prepaid Report Tokens Queue (e.g. from Bundles or Packs) -->
                    <?php 
                    $user_tokens = class_exists('MR_Tokens') ? MR_Tokens::get_user_tokens($user_id) : [];
                    $available_tokens = [];
                    foreach ($user_tokens as $atk_slug => $tk) {
                        $rem = intval($tk['remaining'] ?? ($tk['tokens_remaining'] ?? 0));
                        if ($rem > 0) {
                            $available_tokens[$atk_slug] = $tk;
                        }
                    }
                    if (!empty($available_tokens)):
                    ?>
                        <div class="mr-available-tokens-banner" style="background: linear-gradient(135deg, #FFF9F0 0%, #FFF2DF 100%); border: 2px solid #D9A441; border-radius: 16px; padding: 18px 22px; margin-bottom: 24px; box-shadow: 0 6px 20px rgba(217,164,65,0.15);">
                            <div style="font-weight: 800; color: #7A1F3D; font-size: 15px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-wand-magic-sparkles" style="color: #F58220;"></i>
                                <span>Unused Prepaid Report Tokens Ready to Generate:</span>
                            </div>
                            <p style="margin: 0 0 14px 0; font-size: 12.5px; color: #554841; line-height: 1.45;">
                                You have active prepaid tokens ready. Click below to enter birth details (for yourself or another person):
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($available_tokens as $atk_slug => $atk): 
                                    $atk_name = !empty($atk['name']) ? $atk['name'] : (class_exists('MR_Bundles') ? MR_Bundles::get_report_name($atk_slug) : ucwords(str_replace('_', ' ', $atk_slug)));
                                    $atk_rem  = intval($atk['remaining'] ?? ($atk['tokens_remaining'] ?? 1));
                                ?>
                                    <div style="background: #FFFFFF; border: 1.5px solid #EADCCB; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                        <div>
                                            <div style="font-weight: 700; color: #1E293B; font-size: 14.5px;"><?php echo esc_html($atk_name); ?></div>
                                            <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; margin-top: 3px;">
                                                <i class="fa-solid fa-ticket"></i> <?php echo esc_html($atk_rem); ?> Prepaid Token<?php echo $atk_rem > 1 ? 's' : ''; ?> Available
                                            </span>
                                        </div>
                                        <div>
                                            <button type="button" class="mr-btn-generate-direct" data-slug="<?php echo esc_attr($atk_slug); ?>" data-title="<?php echo esc_attr($atk_name); ?>" style="padding: 9px 18px; font-size: 12.5px; border-radius: 8px; cursor: pointer; background: #7A1F3D; color: #fff; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(122,31,61,0.2);">
                                                <i class="fa-solid fa-wand-magic-sparkles"></i> Enter Details &amp; Generate &rarr;
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($reports)): ?>
                        <div class="mr-empty-state-box">
                            <div class="mr-empty-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" style="width:40px; height:40px; opacity:0.6;" /></div>
                            <h4>No Reports Generated Yet</h4>
                            <p>You haven't generated any astrological reports with this account yet. Select your desired report from our catalog to get started!</p>
                            <a href="<?php echo esc_url(home_url('/reports-catalog/')); ?>" class="mr-btn-glow-gold" style="display:inline-block; margin-top:14px; text-decoration:none;">
                                Browse Reports Catalog &rarr;
                            </a>
                        </div>
                    <?php else: 
                        $rep_per_page = 10;
                        $total_reports = count($reports);
                        $rep_total_pages = ceil($total_reports / $rep_per_page);
                    ?>
                        <div class="mr-reports-table-responsive">
                            <table class="mr-account-table">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th>Native Details</th>
                                        <th>Birth Coordinates</th>
                                        <th>Generated Date</th>
                                        <th class="mr-col-status">Status</th>
                                        <th class="mr-col-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $idx => $r): 
                                        $rep_page_num = floor($idx / $rep_per_page) + 1;
                                    ?>
                                        <tr class="mr-report-row" data-page="<?php echo $rep_page_num; ?>" style="<?php echo $rep_page_num > 1 ? 'display:none;' : ''; ?>">
                                            <td>
                                                <div class="mr-report-cell-title">
                                                    <strong><?php echo esc_html($r->report_title); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mr-native-name"><?php echo esc_html($r->person_name); ?></div>
                                                <div class="mr-native-gender"><?php echo esc_html($r->gender); ?></div>
                                            </td>
                                            <td>
                                                <div class="mr-cell-datetime"><?php echo esc_html($r->dob); ?> &bull; <?php echo esc_html($r->tob); ?></div>
                                                <div class="mr-cell-pob"><?php echo esc_html($r->pob); ?></div>
                                            </td>
                                            <td>
                                                <span class="mr-cell-date"><?php echo esc_html(date_i18n('d M Y, h:i A', strtotime($r->created_at))); ?></span>
                                            </td>
                                            <td class="mr-col-status">
                                                <span class="mr-status-pill mr-status-ready" title="Report successfully generated and ready to download">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <span>Ready</span>
                                                </span>
                                            </td>
                                            <td class="mr-col-action">
                                                <?php $report_link = class_exists('MR_Report_Downloader') ? MR_Report_Downloader::ensure_clean_url($r) : $r->pdf_url; ?>
                                                <a href="<?php echo esc_url($report_link); ?>" target="_blank" class="mr-btn-view-pdf" title="Download Report PDF">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                    <span>View / Download</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($rep_total_pages > 1): ?>
                            <div class="mr-pagination-wrap" id="mr-reports-pagination" data-total-pages="<?php echo $rep_total_pages; ?>" data-current-page="1">
                                <button type="button" class="mr-page-btn mr-prev-page" disabled>&laquo; Prev</button>
                                <div class="mr-page-numbers">
                                    <?php for ($p = 1; $p <= $rep_total_pages; $p++): ?>
                                        <button type="button" class="mr-page-num <?php echo ($p === 1) ? 'active' : ''; ?>" data-page="<?php echo $p; ?>"><?php echo $p; ?></button>
                                    <?php endfor; ?>
                                </div>
                                <button type="button" class="mr-page-btn mr-next-page">&raquo; Next</button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 3: Transaction Passbook -->
            <div class="mr-account-tab-pane" id="mr-pane-passbook">
                <!-- Top Niyati Credit Wallet & Activity Card -->
                <div class="mr-passbook-hero-card">
                    <div class="mr-passbook-hero-top">
                        <div class="mr-passbook-hero-identity">
                            <span class="mr-passbook-hero-eyebrow">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#F58220" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                                <span>NIYATI AI VEDIC ASTROLOGER WALLET</span>
                            </span>
                            <h3 class="mr-passbook-hero-title">Live Chat &amp; Consultation Credits</h3>
                            <div class="mr-passbook-balance-badge-wrap">
                                <div class="mr-passbook-balance-badge">
                                    <span class="mr-balance-num"><?php echo number_format($niyati_balance); ?></span>
                                    <span class="mr-balance-unit">Questions Available</span>
                                </div>
                                <span class="mr-passbook-validity-pill">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>Validity: <?php echo esc_html($niyati_validity); ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Wallet Lifetime Stats -->
                        <div class="mr-passbook-stats-grid">
                            <div class="mr-stat-tile">
                                <span class="mr-stat-label">Lifetime Awarded</span>
                                <span class="mr-stat-val mr-stat-plus">+<?php echo number_format($niyati_purchased); ?> <small>Credits</small></span>
                            </div>
                            <div class="mr-stat-tile">
                                <span class="mr-stat-label">Total Consumed</span>
                                <span class="mr-stat-val mr-stat-minus">-<?php echo number_format($niyati_consumed); ?> <small>Asked</small></span>
                            </div>
                            <div class="mr-stat-tile">
                                <span class="mr-stat-label">Total Spent</span>
                                <span class="mr-stat-val">₹<?php echo number_format($total_spent, 2); ?></span>
                            </div>
                        </div>

                        <!-- Quick Action CTAs -->
                        <div class="mr-passbook-hero-actions">
                            <button type="button" class="mr-btn-ask-niyati" onclick="window.NiyatiChat && window.NiyatiChat.open ? window.NiyatiChat.open() : null;" title="Chat with Niyati AI Vedic Astrologer">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                <span>Ask Niyati &rarr;</span>
                            </button>
                            <button type="button" class="mr-btn-recharge-credits" onclick="window.NiyatiChat && window.NiyatiChat.openWalletModal ? window.NiyatiChat.openWalletModal() : (window.NiyatiChat && window.NiyatiChat.open ? window.NiyatiChat.open() : null);" title="Recharge Question Credits">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>+ Recharge</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table Card -->
                <div class="mr-account-card" style="margin-top: 20px;">
                    <div class="mr-card-header" style="flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 class="mr-card-title">Transaction Passbook &amp; Activity History</h3>
                            <p class="mr-passbook-filter-hint" id="mr-passbook-filter-hint">Showing complete chronological ledger of report orders, credit recharges, and chat sessions.</p>
                        </div>

                        <!-- Filter Pills with Purpose & Clear Count -->
                        <div class="mr-passbook-filters">
                            <button type="button" class="mr-passbook-filter-btn active" data-filter="all" title="View all report purchases, credit top-ups, and chat consultations">
                                <span>All Activity</span>
                                <span class="mr-filter-count"><?php echo $count_all_ledger; ?></span>
                            </button>
                            <button type="button" class="mr-passbook-filter-btn" data-filter="credits" title="View only Niyati AI credit additions and daily chat consumption">
                                <span>AI Chat Credits ⚡</span>
                                <span class="mr-filter-count"><?php echo $count_credits_ledger; ?></span>
                            </button>
                            <button type="button" class="mr-passbook-filter-btn" data-filter="orders" title="View only paid reports, subscriptions, and invoice receipts">
                                <span>Orders &amp; Receipts ₹</span>
                                <span class="mr-filter-count"><?php echo $count_orders_ledger; ?></span>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($unified_ledger)): ?>
                        <div class="mr-empty-state-box">
                            <div class="mr-empty-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" style="width:40px; height:40px; opacity:0.6;" /></div>
                            <h4>No Transactions in Passbook</h4>
                            <p>You have not made any purchases or token transactions yet.</p>
                            <a href="javascript:void(0);" onclick="window.NiyatiChat && window.NiyatiChat.open ? window.NiyatiChat.open() : null;" class="mr-btn-glow-gold" style="display:inline-block; margin-top:14px; text-decoration:none;">
                                Consult Vedic Astrologer &rarr;
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mr-reports-table-responsive mr-passbook-container">
                            <table class="mr-account-table" id="mr-passbook-ledger-table">
                                <thead>
                                    <tr>
                                        <th style="width: 17%;">Date &amp; Time</th>
                                        <th style="width: 43%;">Activity &amp; Reference</th>
                                        <th style="width: 14%; text-align: center;">Credits Impact</th>
                                        <th style="width: 12%; text-align: right;">Amount</th>
                                        <th class="mr-col-status" style="width: 14%; text-align: center;">Status &amp; Invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo self::render_passbook_rows_html($passbook_data['items']); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- On-Demand Pagination Container (AJAX 25-item slices) -->
                        <div id="mr-passbook-pagination-container">
                            <?php echo self::render_passbook_pagination_html($passbook_data['total_pages'], 1); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 4: Settings -->
            <div class="mr-account-tab-pane" id="mr-pane-settings">
                <div class="mr-account-card">
                    <div class="mr-card-header">
                        <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                        <div>
                            <h3 class="mr-card-title">Account Settings &amp; DPDP Privacy Preferences</h3>
                            <p class="mr-card-sub">Customize your astrological calculation methods, report delivery, and DPDP Cookie &amp; Storage preferences.</p>
                        </div>
                    </div>

                    <form id="mr-account-settings-form" class="mr-form-styled">
                        <div class="mr-form-row mr-cols-2">
                            <div class="mr-form-group">
                                <label for="mr_pref_chart">Preferred Birth Chart Format</label>
                                <div class="mr-input-wrap">
                                    <select id="mr_pref_chart" name="pref_chart">
                                        <option value="north_indian" <?php selected($pref_chart, 'north_indian'); ?>>North Indian (Diamond Kundali - Default)</option>
                                        <option value="south_indian" <?php selected($pref_chart, 'south_indian'); ?>>South Indian (Square Chart)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mr-form-group">
                                <label for="mr_pref_system">Astrological Tradition</label>
                                <div class="mr-input-wrap">
                                    <select id="mr_pref_system" name="pref_system">
                                        <option value="vedic" <?php selected($pref_system, 'vedic'); ?>>Vedic Sidereal (Lahiri Ayanamsha)</option>
                                        <option value="western" <?php selected($pref_system, 'western'); ?>>Western Tropical System</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mr-form-divider">
                            <span>Security &amp; Google OAuth Authentication</span>
                        </div>

                        <div class="mr-oauth-info-box">
                            <div class="mr-oauth-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" /></div>
                            <div>
                                <h4 style="margin:0 0 4px 0; color:#101820; font-weight:700;">Google Authentication Linked</h4>
                                <p style="margin:0; font-size:13px; color:#64748B;">Your account is authenticated safely via Google OAuth 2.0. No separate password needed. Your sessions are securely encrypted.</p>
                            </div>
                        </div>

                        <div class="mr-form-divider">
                            <span>DPDP Act 2023 &bull; Section 14: Data Principal Nomination</span>
                        </div>

                        <p class="mr-settings-field-hint" style="font-size:13px; color:#64748B; margin-top:-6px; margin-bottom:16px; line-height:1.5;">
                            Under Section 14 of the Digital Personal Data Protection Act, 2023 (DPDP Act), you have the statutory right to nominate an individual who shall, in the event of death or incapacity, exercise your data rights and access your astrological archives.
                        </p>

                        <div class="mr-form-row mr-cols-2">
                            <div class="mr-form-group">
                                <label for="mr_dpdp_nominee_name">Nominee Full Name</label>
                                <div class="mr-input-wrap">
                                    <input type="text" id="mr_dpdp_nominee_name" name="nominee_name" value="<?php echo esc_attr($nominee_name); ?>" placeholder="e.g. Aditi Sharma" />
                                </div>
                            </div>
                            <div class="mr-form-group">
                                <label for="mr_dpdp_nominee_relation">Relationship to Nominee</label>
                                <div class="mr-input-wrap">
                                    <select id="mr_dpdp_nominee_relation" name="nominee_relation">
                                        <option value="" <?php selected($nominee_relation, ''); ?>>-- Select Relationship --</option>
                                        <option value="Spouse" <?php selected($nominee_relation, 'Spouse'); ?>>Spouse</option>
                                        <option value="Son / Daughter" <?php selected($nominee_relation, 'Son / Daughter'); ?>>Son / Daughter</option>
                                        <option value="Parent" <?php selected($nominee_relation, 'Parent'); ?>>Parent</option>
                                        <option value="Sibling" <?php selected($nominee_relation, 'Sibling'); ?>>Sibling</option>
                                        <option value="Legal Heir / Relative" <?php selected($nominee_relation, 'Legal Heir / Relative'); ?>>Legal Heir / Relative</option>
                                        <option value="Other" <?php selected($nominee_relation, 'Other'); ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mr-form-row mr-cols-2">
                            <div class="mr-form-group">
                                <label for="mr_dpdp_nominee_email">Nominee Email Address</label>
                                <div class="mr-input-wrap">
                                    <input type="email" id="mr_dpdp_nominee_email" name="nominee_email" value="<?php echo esc_attr($nominee_email); ?>" placeholder="nominee@example.com" />
                                </div>
                            </div>
                            <div class="mr-form-group">
                                <label for="mr_dpdp_nominee_phone">Nominee Phone Number</label>
                                <div class="mr-input-wrap">
                                    <input type="tel" id="mr_dpdp_nominee_phone" name="nominee_phone" value="<?php echo esc_attr($nominee_phone); ?>" placeholder="+91 98765 43210" />
                                </div>
                            </div>
                        </div>

                        <div class="mr-form-divider">
                            <span>DPDP Act 2023 &bull; Section 6(4) &amp; Section 9: Consent Governance</span>
                        </div>

                        <p class="mr-settings-field-hint" style="font-size:13px; color:#64748B; margin-top:-6px; margin-bottom:14px; line-height:1.5;">
                            Under Section 6(4) of the DPDP Act 2023, you retain the continuous statutory right to manage or withdraw consent for data processing. Under Section 9, the statutory age of majority in India is 18 years; behavioral profiling or targeted advertising directed at minors is strictly prohibited.
                        </p>

                        <div class="mr-checkbox-group mr-dpdp-consent-group" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                            <label class="mr-toggle-label">
                                <input type="checkbox" name="consent_profile" id="mr_consent_profile" value="1" <?php checked($consent_profile, '1'); ?> />
                                <span><strong>Astrological Kundli Calculations &amp; Profiling:</strong> Processing date, time, and coordinates of birth for planetary transit charts, Kundli matchings, and personalized horoscope forecasts.</span>
                            </label>
                            <label class="mr-toggle-label">
                                <input type="checkbox" name="consent_ai" id="mr_consent_ai" value="1" <?php checked($consent_ai, '1'); ?> />
                                <span><strong>Niyati AI Astrologer Session Memory:</strong> Retaining encrypted Vedic conversation history across devices for personalized astrological continuity.</span>
                            </label>
                            <label class="mr-toggle-label">
                                <input type="checkbox" name="consent_comms" id="mr_consent_comms" value="1" <?php checked($consent_comms, '1'); ?> />
                                <span><strong>Planetary Transit Alerts &amp; PDF Report Delivery:</strong> Receiving instant transactional email copies of generated PDF reports, astrological transit updates, and order receipts.</span>
                            </label>
                        </div>

                        <div class="mr-form-divider" id="mr-cookie-settings-section">
                            <span>DPDP Act 2023 &bull; Cookie &amp; Storage Preferences</span>
                        </div>

                        <p class="mr-settings-field-hint" style="font-size:13px; color:#64748B; margin-top:-6px; margin-bottom:14px; line-height:1.5;">
                            Under India's DPDP Act 2023, you retain granular statutory control over non-essential browser cookies and local device storage. You can customize which categories you permit below:
                        </p>

                        <div class="mr-checkbox-group mr-dpdp-consent-group" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                            <!-- Category 1: Strictly Necessary (Locked ON) -->
                            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:12px 14px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;">
                                        <strong style="font-size:13px; color:#1E293B;">Strictly Necessary Cookies</strong>
                                        <span style="font-size:11px; font-weight:700; background:#DCFCE7; color:#166534; padding:2px 7px; border-radius:9999px;">Always Active</span>
                                    </div>
                                    <p style="margin:0; font-size:12px; color:#64748B; line-height:1.45;">
                                        Essential for core website operation, Google OAuth session authentication, cart security, and passbook fraud detection. Cannot be turned off.
                                    </p>
                                </div>
                                <div style="flex-shrink:0; padding-top:2px;">
                                    <input type="checkbox" checked disabled style="accent-color:#16A34A; width:17px; height:17px; cursor:not-allowed;" />
                                </div>
                            </div>

                            <!-- Category 2: Personalization & Astrological Memory -->
                            <label class="mr-toggle-label" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:12px 14px; margin:0; display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="cookie_personalization" id="mr_cookie_personalization" value="1" <?php checked($cookie_personalization, '1'); ?> style="margin-top:2px;" />
                                <span>
                                    <strong>Personalization &amp; Astrological Memory:</strong> Remembers your birth chart preferences (North vs South Indian charts, Vedic Sidereal Ayanamsha), saved birth city, and Niyati AI conversation context for seamless astrological continuity.
                                </span>
                            </label>

                            <!-- Category 3: Analytics & Performance Metrics -->
                            <label class="mr-toggle-label" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:12px 14px; margin:0; display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="cookie_analytics" id="mr_cookie_analytics" value="1" <?php checked($cookie_analytics, '1'); ?> style="margin-top:2px;" />
                                <span>
                                    <strong>Analytics &amp; Performance Metrics:</strong> Allows aggregated and anonymous measurement of horoscope traffic, page load speeds, and Vedic computation accuracy (via Google Analytics / Site Kit).
                                </span>
                            </label>

                            <!-- Category 4: Marketing & Sponsored Ad Targeting -->
                            <label class="mr-toggle-label" style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:12px 14px; margin:0; display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="cookie_marketing" id="mr_cookie_marketing" value="1" <?php checked($cookie_marketing, '1'); ?> style="margin-top:2px;" />
                                <span>
                                    <strong>Marketing &amp; Sponsored Ad Targeting:</strong> Used by Google AdSense to deliver relevant ads. In compliance with Section 9 of the DPDP Act 2023, targeted advertising or behavioral tracking directed at minors is strictly prohibited.
                                </span>
                            </label>
                        </div>

                        <div class="mr-form-actions" style="margin-top:20px;">
                            <button type="submit" class="mr-btn-glow-gold" id="mr-btn-save-settings">
                                Save Preferences &amp; DPDP Settings
                            </button>
                            <span id="mr-settings-feedback" class="mr-save-feedback"></span>
                        </div>
                    </form>

                    <!-- DPDP Section 11: Right to Access / Data Export Card -->
                    <div class="mr-account-card mr-dpdp-card" style="margin-top:24px;">
                        <div class="mr-card-header">
                            <div class="mr-card-icon-badge mr-badge-icon-blue">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </div>
                            <div>
                                <div class="mr-dpdp-badge mr-dpdp-badge-blue">DPDP Act 2023 &bull; Section 11</div>
                                <h3 class="mr-card-title">Right to Access &amp; Data Portability</h3>
                                <p class="mr-card-sub">Export and download a complete, machine-readable JSON copy of all personal, astrological, and transaction data stored with MeraRashifal Technologies.</p>
                            </div>
                        </div>
                        <div class="mr-dpdp-card-content">
                            <ul class="mr-dpdp-data-summary-list">
                                <li><strong>Identity &amp; Profile:</strong> Account details, registered email, and contact info.</li>
                                <li><strong>Astrological Vault:</strong> Birth date, exact time, geographic coordinates, and calculated Rashi.</li>
                                <li><strong>Saved Family Kundlis:</strong> All saved family birth profiles stored in your personal vault.</li>
                                <li><strong>Niyati AI History:</strong> Transcript summary of past astrological consultations and questions.</li>
                                <li><strong>Transaction Ledger:</strong> Complete retail invoices, token purchases, and payment references.</li>
                            </ul>
                            <div class="mr-dpdp-card-actions" style="margin-top:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                <button type="button" class="mr-btn-outline-gold" id="mr-btn-dpdp-export">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    <span>Download My Data Archive (.JSON)</span>
                                </button>
                                <span id="mr-dpdp-export-feedback" class="mr-save-feedback"></span>
                            </div>
                        </div>
                    </div>

                    <!-- DPDP Section 12: Right to Erasure / Account Deletion Danger Zone -->
                    <div class="mr-account-card mr-dpdp-card mr-dpdp-danger-card" style="margin-top:24px;">
                        <div class="mr-card-header">
                            <div class="mr-card-icon-badge mr-badge-icon-red">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </div>
                            <div>
                                <div class="mr-dpdp-badge mr-dpdp-badge-red">DPDP Act 2023 &bull; Section 12</div>
                                <h3 class="mr-card-title" style="color:#991B1B;">Right to Erasure &amp; Account Deletion</h3>
                                <p class="mr-card-sub">Permanently delete your account, saved astrological records, and Niyati AI conversation history.</p>
                            </div>
                        </div>
                        <div class="mr-dpdp-card-content">
                            <p style="font-size:13.5px; color:#475569; line-height:1.55; margin-bottom:14px;">
                                You have the statutory right under Section 12 of the DPDP Act 2023 to request the permanent erasure of your personal data. Once deleted, your saved Kundlis, consultation history, and unused credit tokens cannot be recovered.
                            </p>
                            
                            <button type="button" class="mr-btn-danger-outline" id="mr-btn-dpdp-erase-open">
                                <span>Request Account Erasure &amp; Data Deletion &darr;</span>
                            </button>

                            <!-- Collapsible Inline Confirmation Container -->
                            <div id="mr-dpdp-erase-confirm-container" class="mr-dpdp-confirm-box" style="display:none; margin-top:16px;">
                                <div class="mr-dpdp-warning-notice">
                                    <strong>⚠ Permanent &amp; Irreversible Action:</strong>
                                    <ul style="margin:8px 0 10px 18px; padding:0; font-size:13px; color:#991B1B; line-height:1.5;">
                                        <li>Your user account and Google OAuth authentication link will be terminated immediately.</li>
                                        <li>All saved family Kundlis and birth chart profiles will be permanently purged from server disk.</li>
                                        <li>Your complete Niyati AI chat history will be permanently deleted.</li>
                                        <li><em>Statutory Audit Exemption:</em> In compliance with Indian GST, taxation, and company audit regulations, past financial transaction records are retained in an anonymized, non-personally identifiable format.</li>
                                    </ul>
                                </div>

                                <div class="mr-dpdp-challenge-wrapper">
                                    <div class="mr-dpdp-challenge-header">
                                        <span class="mr-dpdp-shield-pill">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            Anti-Bot Runtime Verification
                                        </span>
                                        <button type="button" class="mr-btn-refresh-challenge" id="mr-btn-refresh-challenge" title="Generate another security phrase and verification word">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:4px;"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                                            <span>Change Phrase</span>
                                        </button>
                                    </div>
                                    <p class="mr-dpdp-challenge-sentence" id="mr-dpdp-challenge-sentence" style="font-size:13.5px; font-weight:600; color:#1E293B; margin:8px 0 12px 0; line-height:1.6;">
                                        <?php echo $erase_challenge['sentence_html']; ?>
                                    </p>
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                                    <input type="text" id="mr-dpdp-erase-confirm-input" data-expected="<?php echo esc_attr($erase_challenge['word']); ?>" placeholder="Type <?php echo esc_attr($erase_challenge['word']); ?>" class="mr-input-danger-text" autocomplete="off" style="max-width:240px;" />
                                    <button type="button" class="mr-btn-danger-solid" id="mr-btn-dpdp-erase-confirm" disabled>
                                        <span>Permanently Erase My Account</span>
                                    </button>
                                    <button type="button" class="mr-btn-secondary" id="mr-btn-dpdp-erase-cancel">
                                        <span>Cancel</span>
                                    </button>
                                </div>
                                <div id="mr-dpdp-erase-feedback" class="mr-save-feedback" style="margin-top:10px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- DPDP Section 13 & 15: Grievance Redressal & DPBI Notice Card -->
                    <div class="mr-account-card mr-dpdp-card mr-dpdp-info-card" style="margin-top:24px;">
                        <div class="mr-card-header">
                            <div class="mr-card-icon-badge mr-badge-icon-green">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div>
                                <div class="mr-dpdp-badge mr-dpdp-badge-green">DPDP Act 2023 &bull; Section 13 &amp; 15</div>
                                <h3 class="mr-card-title">Grievance Redressal &amp; DPBI Rights</h3>
                                <p class="mr-card-sub">Statutory contact details of the Data Fiduciary and escalation rights to the Data Protection Board of India.</p>
                            </div>
                        </div>
                        <div class="mr-dpdp-card-content" style="font-size:13px; line-height:1.6; color:#475569;">
                            <div class="mr-dpdp-fiduciary-grid">
                                <div><strong>Data Fiduciary:</strong> MeraRashifal Technologies</div>
                                <div><strong>Grievance &amp; DPO Officer:</strong> <a href="mailto:grievance@merarashifal.com" style="color:#2563EB;">grievance@merarashifal.com</a></div>
                                <div><strong>Statutory Address:</strong> Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, Uttar Pradesh, 201014</div>
                                <div><strong>Redressal SLA:</strong> Inquiries acknowledged within 24 hours and addressed within statutory timelines.</div>
                            </div>
                            <div style="margin-top:12px; padding:10px 14px; background:#F8FAFC; border-left:3px solid #16A34A; border-radius:0 6px 6px 0; font-size:12.5px; color:#334155;">
                                <strong>DPBI Escalation:</strong> If you are not satisfied with the resolution provided by our Grievance Officer, you have the statutory right under Section 15 of the DPDP Act 2023 to register a complaint directly with the <strong>Data Protection Board of India (DPBI)</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Contact Us -->
            <div class="mr-account-tab-pane" id="mr-pane-contact">
                <div class="mr-pane-grid">
                    <div class="mr-pane-col-main">
                        <div class="mr-account-card mr-support-card">
                            <div class="mr-card-header">
                                <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                                <div>
                                    <h3 class="mr-card-title">MeraRashifal Astro Support &amp; Helpdesk</h3>
                                    <p class="mr-card-sub">Ask any question regarding your Vedic reports, birth chart data, or consultations. Our senior team emails the reply directly to your inbox.</p>
                                </div>
                            </div>

                            <!-- Helpdesk Info Strip -->
                            <div class="mr-support-info-strip">
                                <div class="mr-support-info-chip">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F58220" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <span>Official Email: <strong>support@merarashifal.com</strong></span>
                                </div>
                                <div class="mr-support-info-chip">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>Hours: Mon–Sat, 9 AM–8 PM IST &bull; <strong>Response &lt; 2 hrs</strong></span>
                                </div>
                            </div>

                            <!-- User Context Note -->
                            <div class="mr-support-user-meta">
                                <span>Submitting as: <strong><?php echo esc_html($astro_name ?: $user->display_name); ?></strong> (<?php echo esc_html($user->user_email); ?>)</span>
                            </div>

                            <!-- Question Form + 1 Attachment Option -->
                            <form id="mr-support-ticket-form" method="post" enctype="multipart/form-data" novalidate class="mr-compact-support-form">
                                <input type="hidden" id="mr_support_name" name="support_name" value="<?php echo esc_attr($astro_name ?: $user->display_name); ?>" />
                                <input type="hidden" id="mr_support_email" name="support_email" value="<?php echo esc_attr($user->user_email); ?>" />
                                <input type="hidden" id="mr_support_phone" name="support_phone" value="<?php echo esc_attr($user_phone); ?>" />
                                <input type="hidden" id="mr_support_category" name="support_category" value="Astrological Support &amp; Consultation" />
                                <input type="hidden" id="mr_support_subject" name="support_subject" value="" />

                                <!-- Your Question Input Box -->
                                <div class="mr-form-group">
                                    <label for="mr_support_message" class="mr-field-label">
                                        Your Question / Query <span class="mr-req">*</span>
                                    </label>
                                    <textarea id="mr_support_message" name="support_message" class="mr-input-textarea" rows="4" placeholder="Enter your question or issue here (e.g. question regarding your Kundali report, birth time update, payment reference, or consultation)..." required></textarea>
                                </div>

                                <!-- 1 Attachment Option (Picture / Screenshot) -->
                                <div class="mr-form-group mr-attachment-group">
                                    <label class="mr-field-label">
                                        Attach Picture or Screenshot 
                                        <span class="mr-sub-label">(Optional &bull; 1 Image &bull; Max 5MB &bull; JPG, PNG, WEBP)</span>
                                    </label>
                                    
                                    <input type="file" id="mr_support_attachment" name="support_attachment" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" />
                                    
                                    <div class="mr-file-dropzone" id="mr-file-dropzone" tabindex="0" role="button" aria-label="Upload Screenshot or Picture">
                                        <div class="mr-dropzone-inner" id="mr-dropzone-prompt">
                                            <div class="mr-dropzone-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                            </div>
                                            <p class="mr-dropzone-title">Click to upload or drag &amp; drop a picture</p>
                                            <span class="mr-dropzone-sub">PNG, JPG, or WEBP up to 5MB (1 picture allowed)</span>
                                        </div>

                                        <div class="mr-file-preview-card" id="mr-file-preview-card" style="display: none;">
                                            <div class="mr-file-preview-thumb-wrap">
                                                <img id="mr-file-preview-img" src="" alt="Attachment Preview" class="mr-file-preview-img" />
                                            </div>
                                            <div class="mr-file-preview-meta">
                                                <span class="mr-file-preview-name" id="mr-file-name">filename.png</span>
                                                <span class="mr-file-preview-size" id="mr-file-size">0 KB</span>
                                            </div>
                                            <button type="button" class="mr-btn-clear-attachment" id="mr-btn-clear-attachment" title="Remove attachment" aria-label="Remove attachment">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="mr-file-upload-error" class="mr-file-error-msg" style="display: none;"></div>
                                </div>

                                <div class="mr-form-actions-bar">
                                    <button type="submit" class="mr-btn-glow-gold" id="mr-btn-submit-support">
                                        <span class="mr-btn-text">Send Question to Support &rarr;</span>
                                        <span class="mr-btn-spinner" style="display: none;">
                                            <img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" /> Sending to support@merarashifal.com...
                                        </span>
                                    </button>
                                    <span class="mr-support-safe-note">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                        Delivered directly to <strong>support@merarashifal.com</strong>
                                    </span>
                                </div>

                                <div id="mr-support-ticket-feedback" class="mr-support-ticket-feedback" style="display: none; margin-top: 14px;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </div><!-- /.mr-account-page-wrapper -->
        <?php
        return ob_get_clean();
    }

    /**
     * Render Logged Out View when user visits account page without active session
     * Built on MeraRashifal Brand Design System:
     * - Mobile-first zero-scroll login (Google Sign In & T&C above the fold)
     * - Desktop 2-column split hero (Sign In on left, quick product offerings on right)
     * - Sacred Orange #F58220, Deep Maroon #7A1F3D, Gold #D9A441, Cream #FDF8F1, Dark Brown #3A312D
     * - Minimal flat SVG vector icons (strictly no cartoon emojis)
     */
    public static function render_logged_out_view() {
        $redirect_to = !empty($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : (!empty($_GET['redirect']) ? esc_url_raw($_GET['redirect']) : '');
        if (empty($redirect_to) && !empty($_COOKIE['mr_login_return_url'])) {
            $redirect_to = esc_url_raw($_COOKIE['mr_login_return_url']);
        }
        $login_url   = self::get_login_action_url($redirect_to);
        $loader_url  = content_url('/uploads/2025/11/loader.webp');
        $img_base    = defined('MR_PLUGIN_URL') ? MR_PLUGIN_URL . 'assets/images/reports/' : content_url('/plugins/mr-subscription-reports/assets/images/reports/');
        $tnc_url     = home_url('/terms-and-conditions/');
        $privacy_url = home_url('/privacy-policy/');

        // Quick feature offerings for the right hero side showcase
        $hero_quick_offerings = [
            [
                'slug'         => 'yearly_horoscope_long',
                'title'        => 'Yearly Varshaphal (2026)',
                'badge'        => '26 Pages • Best Seller',
                'badge_suffix' => 'Best Seller',
                'badge_cls'    => 'mr-tag-orange',
                'desc'         => '12-month career, finance, health & remedies timeline.',
                'price'        => '₹99',
                'url'          => home_url('/yearly-horoscope-report/'),
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'
            ],
            [
                'slug'         => 'kundli_predictions',
                'title'        => 'Master Janma Kundali',
                'badge'        => '26 Pages • Top Rated',
                'badge_suffix' => 'Top Rated',
                'badge_cls'    => 'mr-tag-gold',
                'desc'         => 'Full birth chart, 16 divisional harmonics & Shadbala.',
                'price'        => '₹99',
                'url'          => home_url('/janam-kundali-master-report/'),
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#D9A441" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="3" x2="21" y2="21"/><line x1="21" y1="3" x2="3" y2="21"/><polygon points="12,3 21,12 12,21 3,12"/></svg>'
            ],
            [
                'slug'         => '',
                'title'        => 'Ask Niyati AI (24/7)',
                'badge'        => 'Live Astrologer',
                'badge_suffix' => 'Live Astrologer',
                'badge_cls'    => 'mr-tag-maroon',
                'desc'         => 'Real-time Vedic AI conversation for instant guidance.',
                'price'        => 'Live Chat',
                'url'          => '#niyati-chat',
                'is_chat'      => true,
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/></svg>'
            ],
            [
                'slug'         => 'mahadasha_timing',
                'title'        => 'Vedic Dasha Life Timing',
                'badge'        => '20 Pages • Timing',
                'badge_suffix' => 'Timing',
                'badge_cls'    => 'mr-tag-purple',
                'desc'         => 'Vimshottari Dasha cycles and event timing predictions.',
                'price'        => '₹99',
                'url'          => home_url('/dasha-timing-report/'),
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#7B61FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
            ],
            [
                'slug'         => 'dosh_remedial',
                'title'        => 'Kundali Dosh & Remedial',
                'badge'        => '20 Pages • Remedies',
                'badge_suffix' => 'Remedies',
                'badge_cls'    => 'mr-tag-pink',
                'desc'         => 'Manglik, Kaal Sarp, Sade Sati & authentic upayas.',
                'price'        => '₹79',
                'url'          => home_url('/kundali-dosh-report/'),
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#E66BA5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'
            ],
            [
                'slug'         => '',
                'title'        => 'Daily Rashifal & Panchang',
                'badge'        => 'Free Daily Forecast',
                'badge_suffix' => 'Free Daily Forecast',
                'badge_cls'    => 'mr-tag-green',
                'desc'         => 'Planetary Moon transits, Tithi & Shubh Muhurat.',
                'price'        => '100% Free',
                'url'          => home_url('/horoscope/'),
                'icon_svg'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#2CB67D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
            ],
        ];

        // Curated master reports for detailed catalog showcase below
        $showcase_reports = [
            [
                'id'         => 'yearly_horoscope_long',
                'title'      => 'Yearly Horoscope Detailed Long Report',
                'subtitle'   => 'Comprehensive 26-page 12-month Vedic Varshaphal forecast covering career, wealth, relationships, and finances.',
                'pages'      => '26 Pages',
                'badge'      => 'Best Seller',
                'badge_cls'  => 'mr-badge-bestseller',
                'image'      => $img_base . 'yearly_horoscope_long.png',
                'price'      => '₹99',
                'orig_price' => '₹999',
                'discount'   => 'Save 90%',
                'url'        => home_url('/yearly-horoscope-report/'),
                'highlights' => ['12-Month Detailed Timeline', 'Career, Wealth & Love Milestones', 'Planetary Yogas & Dasha Impact', 'Personalized Shastric Remedies']
            ],
            [
                'id'         => 'kundli_predictions',
                'title'      => 'Master Janma Kundali & Harmonics Report',
                'subtitle'   => 'In-depth 26-page birth chart analysis, 16 Shodashvarga harmonics, Shadbala strengths, and lifetime trajectory.',
                'pages'      => '26 Pages',
                'badge'      => 'Top Rated',
                'badge_cls'  => 'mr-badge-toprated',
                'image'      => $img_base . 'kundli_predictions.png',
                'price'      => '₹99',
                'orig_price' => '₹999',
                'discount'   => 'Save 90%',
                'url'        => home_url('/janam-kundali-master-report/'),
                'highlights' => ['Complete Janma Kundali Chart', '16 Divisional Harmonics', 'Shadbala Planetary Strengths', 'Favorable Yogas & Timing']
            ],
            [
                'id'         => 'mahadasha_timing',
                'title'      => 'Master Vedic Dasha Life Timing Report',
                'subtitle'   => 'Deep dive 20-page audit into your active Vimshottari Dasha cycles and event timing predictions.',
                'pages'      => '20 Pages',
                'badge'      => 'Essential',
                'badge_cls'  => 'mr-badge-essential',
                'image'      => $img_base . 'mahadasha_timing.png',
                'price'      => '₹99',
                'orig_price' => '₹999',
                'discount'   => 'Save 90%',
                'url'        => home_url('/dasha-timing-report/'),
                'highlights' => ['Current & Future Mahadashas', 'Critical Career Windows', 'Wealth & Marriage Timing', 'Remedial Protection Guidelines']
            ],
            [
                'id'         => 'dosh_remedial',
                'title'      => 'Master Kundali Dosh & Remedial Audit Report',
                'subtitle'   => 'Clinical diagnosis of Manglik, Kaal Sarp, Pitra, or Sade Sati doshas with personalized Shastric remedies.',
                'pages'      => '20 Pages',
                'badge'      => 'Remedial',
                'badge_cls'  => 'mr-badge-remedial',
                'image'      => $img_base . 'dosh_remedial.png',
                'price'      => '₹79',
                'orig_price' => '₹799',
                'discount'   => 'Save 90%',
                'url'        => home_url('/kundali-dosh-report/'),
                'highlights' => ['Manglik & Kaal Sarp Audit', 'Pitra Dosh & Sade Sati Timeline', 'Root Cause Planetary Analysis', 'Vedic Mantras, Yantras & Upayas']
            ],
            [
                'id'         => 'kundli_matching',
                'title'      => 'Master Kundali Matching (Gun Milan) Report',
                'subtitle'   => '24-page Ashtakoot matching with 36 Gunas, Manglik compatibility, Papasamaya load balance, and dual Dasha sync.',
                'pages'      => '24 Pages',
                'badge'      => 'Marriage Milan',
                'badge_cls'  => 'mr-badge-milan',
                'image'      => $img_base . 'kundli_matching.png',
                'price'      => '₹99',
                'orig_price' => '₹1,299',
                'discount'   => 'Save 92%',
                'url'        => home_url('/kundali-and-gun-matching-report/'),
                'highlights' => ['36 Guna Ashtakoot Score', 'Manglik Compatibility Check', 'Bhakoot & Nadi Dosh Analysis', 'Relationship Longevity & Harmony']
            ],
            [
                'id'         => 'numerology_report',
                'title'      => 'Master Numerology & Vedic Integration Report',
                'subtitle'   => '22-page Sankhya Shastra audit: Lo Shu 3x3 energy grid, core life paths, 8-1-6 Practical Arrow, and 90-day roadmap.',
                'pages'      => '22 Pages',
                'badge'      => 'Sankhya Shastra',
                'badge_cls'  => 'mr-badge-numero',
                'image'      => $img_base . 'numerology.png',
                'price'      => '₹99',
                'orig_price' => '₹999',
                'discount'   => 'Save 90%',
                'url'        => home_url('/numerology-report/'),
                'highlights' => ['Lo Shu Grid Energy Matrix', 'Driver & Conductor Numbers', 'Lucky Dates, Colors & Gemstones', 'Personalized 90-Day Trajectory']
            ],
        ];

        // Live synchronize with Master Report Pricing & Catalog Manager configuration
        if (class_exists('MR_Report_Pricing_Admin')) {
            $cat_config = MR_Report_Pricing_Admin::get_config();

            // Sync Hero Quick Offerings
            foreach ($hero_quick_offerings as &$off) {
                if (!empty($off['slug'])) {
                    $s = $off['slug'];
                    $cfg = $cat_config[$s] ?? null;
                    if ($cfg) {
                        $sale = floatval($cfg['sale_price'] ?? 0);
                        $off['price'] = ($sale <= 0) ? '100% Free' : ('₹' . intval($sale));
                        if (!empty($cfg['pages']) && !empty($off['badge_suffix'])) {
                            $off['badge'] = $cfg['pages'] . ' • ' . $off['badge_suffix'];
                        }
                    }
                }
            }
            unset($off);

            // Sync Showcase Reports
            foreach ($showcase_reports as &$rep) {
                $s = $rep['id'];
                $cfg = $cat_config[$s] ?? null;
                if (!$cfg) {
                    if ($s === 'numerology' && isset($cat_config['numerology_report'])) {
                        $cfg = $cat_config['numerology_report'];
                    } elseif ($s === 'numerology_report' && isset($cat_config['numerology'])) {
                        $cfg = $cat_config['numerology'];
                    }
                }
                if ($cfg) {
                    if (!empty($cfg['name'])) {
                        $rep['title'] = $cfg['name'];
                    }
                    if (!empty($cfg['pages'])) {
                        $rep['pages'] = $cfg['pages'];
                    }
                    if (!empty($cfg['description'])) {
                        $rep['subtitle'] = $cfg['description'];
                    }
                    $sale = floatval($cfg['sale_price'] ?? 0);
                    $list = floatval($cfg['list_price'] ?? 0);
                    $rep['price'] = ($sale <= 0) ? '100% Free' : ('₹' . intval($sale));
                    $rep['orig_price'] = '₹' . number_format($list);
                    if ($list > $sale && $list > 0) {
                        $rep['discount'] = 'Save ' . round((($list - $sale) / $list) * 100) . '%';
                    } else {
                        $rep['discount'] = '';
                    }
                }
            }
            unset($rep);
        }

        // Interactive Vedic Tools & AI Services
        $interactive_tools = [
            [
                'icon_svg'=> '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/></svg>',
                'title'   => 'Niyati AI Astrologer',
                'badge'   => '24/7 Live Vedic AI',
                'desc'    => 'Consult in real-time with Niyati for personalized horoscope queries, instant Shastric remedies, and daily guidance.',
                'btn_txt' => 'Chat with Niyati Now',
                'is_chat' => true,
                'url'     => '#niyati-chat'
            ],
            [
                'icon_svg'=> '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>',
                'title'   => 'Daily Rashifal Forecasts',
                'badge'   => 'Free Daily Horoscope',
                'desc'    => 'Read accurate daily planetary forecasts for all 12 Rashi signs based on active Moon positions and nakshatra transits.',
                'btn_txt' => 'Read Today\'s Rashifal →',
                'is_chat' => false,
                'url'     => home_url('/horoscope/')
            ],
            [
                'icon_svg'=> '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#D9A441" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                'title'   => 'Daily Vedic Panchang',
                'badge'   => 'Shubh Muhurat',
                'desc'    => 'Live Tithi, Nakshatra, Yoga, Karana, Rahu Kaal, and Choghadiya timings to plan your important life decisions.',
                'btn_txt' => 'Check Today\'s Panchang →',
                'is_chat' => false,
                'url'     => home_url('/panchang/')
            ],
            [
                'icon_svg'=> '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#7A1F3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="3" x2="21" y2="21"/><line x1="21" y1="3" x2="3" y2="21"/><polygon points="12,3 21,12 12,21 3,12"/></svg>',
                'title'   => 'Free Kundali Details',
                'badge'   => 'Birth Chart Analysis',
                'desc'    => 'Generate your authentic Vedic Lagna and Navamsha charts with exact planetary degrees and house placements.',
                'btn_txt' => 'View Kundali Details →',
                'is_chat' => false,
                'url'     => home_url('/kundali-details/')
            ]
        ];

        ob_start();
        ?>
        <div class="mr-account-page-wrapper mr-loggedout-page-wrapper" style="background-color: #FDF8F1 !important; width: 100%; max-width: 100%; min-height: 80vh; padding: 10px 0 50px 0; box-sizing: border-box; overflow-x: hidden;">
            <style>
                body, #page, .site, .site-content, #primary, #main, .ast-container, .entry-content, .ast-separate-container, .ast-separate-container .ast-article-single, .ast-separate-container .ast-article-post, .mr-account-page-wrapper, .mr-account-dashboard {
                    background-color: #FDF8F1 !important;
                    background: #FDF8F1 !important;
                }
                @media (max-width: 768px) {
                    .ast-container, .entry-content, .ast-separate-container .ast-article-single, #primary, #main, .site-content {
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        box-sizing: border-box !important;
                    }
                }
            </style>

            <div class="mr-account-dashboard mr-loggedout-main-container">

                <!-- On-Page SEO Breadcrumb -->
                <nav class="mr-account-breadcrumb" aria-label="Breadcrumb">
                    <ol class="mr-breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
                        <li class="mr-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="item" class="mr-breadcrumb-link">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                <span itemprop="name">Home</span>
                            </a>
                            <meta itemprop="position" content="1" />
                        </li>
                        <li class="mr-breadcrumb-sep" aria-hidden="true">&#8250;</li>
                        <li class="mr-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="<?php echo esc_url(self::get_account_page_url()); ?>" itemprop="item" class="mr-breadcrumb-link">
                                <span itemprop="name">Astro Account</span>
                            </a>
                            <meta itemprop="position" content="2" />
                        </li>
                        <li class="mr-breadcrumb-sep" aria-hidden="true">&#8250;</li>
                        <li class="mr-breadcrumb-item mr-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span itemprop="name">Sign In</span>
                            <meta itemprop="position" content="3" />
                        </li>
                    </ol>
                </nav>

                <!-- 1. Hero Split Layout: Immediate Sign-In on Left + Product Offerings on Right -->
                <div class="mr-hero-split-grid">

                    <!-- Left Column: Primary Sign In Portal Card (Zero Scroll on Mobile) -->
                    <div class="mr-hero-col-signin">
                        <?php if (isset($_GET['mr_dpdp_erased'])): ?>
                            <div class="mr-dpdp-erased-banner" style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 2px 8px rgba(6,95,70,0.06);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <span>Your account, astrological data, and AI history have been completely erased in accordance with Section 12 of the DPDP Act 2023.</span>
                            </div>
                        <?php endif; ?>
                        <div class="mr-login-box mr-account-card" id="mr-login-card">
                            <div class="mr-login-badge">
                                <img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" />
                                <span>Vedic Astrology Portal</span>
                            </div>
                            <h1 class="mr-login-title">Sign In to Your <span class="mr-text-orange">Astro Portal</span></h1>
                            <p class="mr-login-sub">
                                Access your personalized Vedic birth chart, certified 20+ page reports, and 24/7 Niyati AI history.
                            </p>

                            <!-- One Click Google Sign In Button -->
                            <div class="mr-google-login-action">
                                <a href="<?php echo esc_url($login_url); ?>" id="mr-google-signin-btn" class="mr-btn-google-sign-in mr-btn-google-disabled" aria-disabled="true" title="Please accept Terms &amp; Conditions below to continue">
                                    <svg class="mr-google-icon" viewBox="0 0 24 24" width="22" height="22">
                                        <path fill="#4285F4" d="M20.64 12.2c0-.64-.06-1.25-.16-1.84H12v3.48h4.84c-.2 1.13-.84 2.08-1.8 2.72v2.26h2.91c1.7-1.57 2.69-3.87 2.69-6.62z"/>
                                        <path fill="#34A853" d="M12 21c2.43 0 4.47-.8 5.96-2.18l-2.91-2.26c-.8.54-1.84.86-3.05.86-2.34 0-4.33-1.58-5.04-3.71H3.96v2.33C5.44 18.98 8.48 21 12 21z"/>
                                        <path fill="#FBBC05" d="M6.96 13.71c-.18-.54-.28-1.12-.28-1.71s.1-1.17.28-1.71V7.96H3.96C3.35 9.17 3 10.55 3 12s.35 2.83.96 4.04l3-2.33z"/>
                                        <path fill="#EA4335" d="M12 6.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C16.46 3.89 14.43 3 12 3 8.48 3 5.44 5.02 3.96 7.96l3 2.33c.71-2.13 2.7-3.71 5.04-3.71z"/>
                                    </svg>
                                    <span>Continue with Google</span>
                                </a>
                            </div>

                            <!-- Mandatory Terms & Conditions Checkbox -->
                            <div class="mr-terms-consent-wrapper" id="mr-terms-consent-wrapper">
                                <label class="mr-terms-checkbox-label" for="mr-agree-terms-checkbox">
                                    <input type="checkbox" id="mr-agree-terms-checkbox" name="agree_terms" value="1" />
                                    <span class="mr-custom-checkbox-box">
                                        <svg class="mr-check-svg" viewBox="0 0 14 14" width="11" height="11">
                                            <path d="M2.5 7L5.5 10L11.5 3.5" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                        </svg>
                                    </span>
                                    <span class="mr-terms-text">
                                        I agree to all <a href="<?php echo esc_url($tnc_url); ?>" target="_blank" rel="noopener">Terms &amp; Conditions</a> including <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Privacy Policy</a> of MeraRashifal.
                                    </span>
                                </label>
                                <div id="mr-terms-warning-msg" class="mr-terms-warning-msg" style="display: none;">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span>Please check the box above to accept the Terms &amp; Conditions and Privacy Policy.</span>
                                </div>
                            </div>

                            <!-- Trust Assurance Row with Clean SVGs (No Cartoon Emojis) -->
                            <div class="mr-login-trust-row">
                                <span class="mr-trust-badge-item">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#2CB67D" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <span>256-Bit SSL</span>
                                </span>
                                <span class="mr-trust-sep">•</span>
                                <span class="mr-trust-badge-item">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#4B7BEC" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>Zero Stored Passwords</span>
                                </span>
                                <span class="mr-trust-sep">•</span>
                                <span class="mr-trust-badge-item">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#F58220" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>1-Click Access</span>
                                </span>
                            </div>
                        </div>
                    </div><!-- /.mr-hero-col-signin -->

                    <!-- Right Column: Product Offerings & Quick Access Hub -->
                    <div class="mr-hero-col-products">
                        <div class="mr-hero-side-card">
                            <div class="mr-side-header">
                                <span class="mr-side-pill">MeraRashifal Ecosystem</span>
                                <h2 class="mr-side-title">Authentic Vedic Offerings</h2>
                                <p class="mr-side-sub">Handcrafted with authentic Parashari calculations, precision Swiss Ephemeris, and deep Shastric wisdom.</p>
                            </div>

                            <div class="mr-quick-offerings-grid">
                                <?php foreach ($hero_quick_offerings as $offering): ?>
                                    <div class="mr-offering-tile">
                                        <div class="mr-offering-icon-wrap">
                                            <?php echo $offering['icon_svg']; ?>
                                        </div>
                                        <div class="mr-offering-content">
                                            <div class="mr-offering-top">
                                                <h4 class="mr-offering-name"><?php echo esc_html($offering['title']); ?></h4>
                                                <span class="mr-offering-badge <?php echo esc_attr($offering['badge_cls']); ?>"><?php echo esc_html($offering['badge']); ?></span>
                                            </div>
                                            <p class="mr-offering-desc"><?php echo esc_html($offering['desc']); ?></p>
                                            <div class="mr-offering-bottom">
                                                <span class="mr-offering-price"><?php echo esc_html($offering['price']); ?></span>
                                                <?php if (!empty($offering['is_chat'])): ?>
                                                    <button type="button" class="mr-offering-link mr-trigger-niyati-btn" id="mr-trigger-niyati-tile">
                                                        <span>Consult Now →</span>
                                                    </button>
                                                <?php else: ?>
                                                    <a href="<?php echo esc_url($offering['url']); ?>" class="mr-offering-link" target="_blank" rel="noopener">
                                                        <span>Explore →</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div><!-- /.mr-hero-col-products -->

                </div><!-- /.mr-hero-split-grid -->

                <!-- 2. Product Cross-Promotion Section: Flagship Master PDF Reports -->
                <div class="mr-showcase-section">
                    <div class="mr-showcase-header">
                        <div class="mr-section-pill">
                            <img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" />
                            <span>Certified Vedic Publications</span>
                        </div>
                        <h2 class="mr-showcase-title">Explore Our Flagship <span class="mr-text-orange">Master PDF Reports</span></h2>
                        <p class="mr-showcase-subtitle">
                            In-depth 20 to 26 page Shastric calculations, planetary transit timelines, and personalized Shastric remedies compiled by our advanced Vedic calculation engine.
                        </p>
                    </div>

                    <div class="mr-showcase-reports-grid">
                        <?php foreach ($showcase_reports as $report): ?>
                            <div class="mr-report-showcase-card">
                                <div class="mr-report-card-top">
                                    <span class="mr-report-page-tag"><?php echo esc_html($report['pages']); ?></span>
                                    <span class="mr-report-badge-ribbon <?php echo esc_attr($report['badge_cls']); ?>"><?php echo esc_html($report['badge']); ?></span>
                                </div>
                                <div class="mr-report-card-visual">
                                    <img src="<?php echo esc_url($report['image']); ?>" alt="<?php echo esc_attr($report['title']); ?>" loading="lazy" />
                                </div>
                                <div class="mr-report-card-content">
                                    <h3 class="mr-report-title"><?php echo esc_html($report['title']); ?></h3>
                                    <p class="mr-report-desc"><?php echo esc_html($report['subtitle']); ?></p>

                                    <ul class="mr-report-highlights-list">
                                        <?php foreach ($report['highlights'] as $highlight): ?>
                                            <li>
                                                <svg viewBox="0 0 16 16" width="13" height="13" fill="#D9A441"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>
                                                <span><?php echo esc_html($highlight); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <div class="mr-report-card-footer">
                                        <div class="mr-report-pricing">
                                            <span class="mr-price-sale"><?php echo esc_html($report['price']); ?></span>
                                            <span class="mr-price-orig"><?php echo esc_html($report['orig_price']); ?></span>
                                            <span class="mr-discount-badge"><?php echo esc_html($report['discount']); ?></span>
                                        </div>
                                        <a href="<?php echo esc_url($report['url']); ?>" class="mr-btn-report-explore" target="_blank" rel="noopener">
                                            <span>Explore Report</span>
                                            <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Interactive Vedic Tools & Live AI Services -->
                <div class="mr-showcase-section mr-interactive-tools-section">
                    <div class="mr-showcase-header">
                        <div class="mr-section-pill">
                            <img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" />
                            <span>Real-Time Vedic Guidance</span>
                        </div>
                        <h2 class="mr-showcase-title">Explore Interactive <span class="mr-text-orange">Vedic Services &amp; AI</span></h2>
                        <p class="mr-showcase-subtitle">
                            Instant answers, daily horoscopes, Panchang calculations, and 24/7 AI consultations at your fingertips.
                        </p>
                    </div>

                    <div class="mr-interactive-tools-grid">
                        <?php foreach ($interactive_tools as $tool): ?>
                            <div class="mr-interactive-tool-card <?php echo !empty($tool['is_chat']) ? 'mr-tool-card-niyati' : ''; ?>">
                                <div class="mr-tool-header">
                                    <div class="mr-tool-icon-box"><?php echo $tool['icon_svg']; ?></div>
                                    <span class="mr-tool-badge"><?php echo esc_html($tool['badge']); ?></span>
                                </div>
                                <h3 class="mr-tool-title"><?php echo esc_html($tool['title']); ?></h3>
                                <p class="mr-tool-desc"><?php echo esc_html($tool['desc']); ?></p>

                                <?php if (!empty($tool['is_chat'])): ?>
                                    <button type="button" class="mr-btn-tool-action mr-trigger-niyati-btn" id="mr-trigger-niyati-showcase">
                                        <span><?php echo esc_html($tool['btn_txt']); ?></span>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($tool['url']); ?>" class="mr-btn-tool-action" target="_blank" rel="noopener">
                                        <span><?php echo esc_html($tool['btn_txt']); ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. Trust & Quality Pillars with Clean Flat SVGs -->
                <div class="mr-trust-pillars-section">
                    <div class="mr-trust-grid">
                        <div class="mr-trust-item">
                            <div class="mr-trust-icon-wrap">
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#D9A441" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                            </div>
                            <div class="mr-trust-content">
                                <h4>Authentic Vedic Shastras</h4>
                                <p>Strict adherence to Parashari, Jaimini, and Lahiri Ayanamsha mathematical algorithms.</p>
                            </div>
                        </div>
                        <div class="mr-trust-item">
                            <div class="mr-trust-icon-wrap">
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#2CB67D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div class="mr-trust-content">
                                <h4>100% Private &amp; Vault-Secured</h4>
                                <p>Your birth data and generated reports remain strictly isolated with encrypted token downloads.</p>
                            </div>
                        </div>
                        <div class="mr-trust-item">
                            <div class="mr-trust-icon-wrap">
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            </div>
                            <div class="mr-trust-content">
                                <h4>Instant 60-Second Compilation</h4>
                                <p>Instant delivery of comprehensive 20 to 26 page PDFs directly to your account vault.</p>
                            </div>
                        </div>
                        <div class="mr-trust-item">
                            <div class="mr-trust-icon-wrap">
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#7A1F3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div class="mr-trust-content">
                                <h4>Transparent Vedic Guidance</h4>
                                <p>Affordable, accessible astrology designed to illuminate career, relationships, and life path.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Bottom Call to Action Prompt -->
                <div class="mr-loggedout-bottom-cta">
                    <div class="mr-bottom-cta-text">
                        <h3>Ready to Begin Your Astrological Journey?</h3>
                        <p>Sign in with Google above to calculate your birth chart, unlock your report vault, and consult with Niyati AI.</p>
                    </div>
                    <button type="button" class="mr-btn-scroll-to-signin" id="mr-btn-scroll-signin">
                        <span>Sign In with Google ⬆️</span>
                    </button>
                </div>

            </div><!-- /.mr-account-dashboard -->
        </div><!-- /.mr-account-page-wrapper -->
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler: Save Astrological and Personal Details
     */
    public static function ajax_save_astro_details() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to update your account details.']);
        }

        $account_name = sanitize_text_field($_POST['account_name'] ?? '');
        $user_phone   = sanitize_text_field($_POST['user_phone'] ?? '');
        $astro_gender = sanitize_text_field($_POST['astro_gender'] ?? 'Male');
        $astro_dob    = sanitize_text_field($_POST['astro_dob'] ?? '');
        $astro_tob    = sanitize_text_field($_POST['astro_tob'] ?? '');
        $astro_pob    = sanitize_text_field($_POST['astro_pob'] ?? '');
        $astro_lat    = sanitize_text_field($_POST['astro_lat'] ?? '');
        $astro_lon    = sanitize_text_field($_POST['astro_lon'] ?? '');
        $astro_tz     = sanitize_text_field($_POST['astro_tz'] ?? '5.5');

        if (!empty($account_name)) {
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $account_name
            ]);
            update_user_meta($user_id, 'first_name', explode(' ', $account_name)[0]);
            update_user_meta($user_id, 'mr_astro_name', $account_name);
        }

        if (!empty($user_phone)) {
            update_user_meta($user_id, 'user_phone', $user_phone);
            update_user_meta($user_id, 'billing_phone', $user_phone);
        }

        update_user_meta($user_id, 'mr_astro_gender', $astro_gender);
        update_user_meta($user_id, 'mr_astro_dob', $astro_dob);
        update_user_meta($user_id, 'mr_astro_tob', $astro_tob);
        update_user_meta($user_id, 'mr_astro_pob', $astro_pob);
        update_user_meta($user_id, 'mr_astro_lat', $astro_lat);
        update_user_meta($user_id, 'mr_astro_lon', $astro_lon);
        update_user_meta($user_id, 'mr_astro_tz', $astro_tz);

        // Recalculate Vedic sign
        $rashi = !empty($astro_dob) ? self::calculate_vedic_sun_sign($astro_dob) : null;

        wp_send_json_success([
            'message' => 'Astrological details saved successfully!',
            'rashi'   => $rashi
        ]);
    }

    /**
     * AJAX handler: Save Settings, Preferences & DPDP Data Principal Nomination / Consents
     */
    public static function ajax_save_settings() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to update preferences.']);
        }

        $pref_chart  = sanitize_text_field($_POST['pref_chart'] ?? 'north_indian');
        $pref_system = sanitize_text_field($_POST['pref_system'] ?? 'vedic');
        $pref_notify = isset($_POST['pref_notify']) && ($_POST['pref_notify'] === '1' || $_POST['pref_notify'] === 1) ? '1' : '0';

        // DPDP Act 2023 - Section 14: Data Principal Nominee
        $nominee_name     = sanitize_text_field($_POST['nominee_name'] ?? '');
        $nominee_email    = sanitize_email($_POST['nominee_email'] ?? '');
        $nominee_phone    = sanitize_text_field($_POST['nominee_phone'] ?? '');
        $nominee_relation = sanitize_text_field($_POST['nominee_relation'] ?? '');

        // DPDP Act 2023 - Section 6(4): Consent Governance
        $consent_profile  = isset($_POST['consent_profile']) && ($_POST['consent_profile'] === '1' || $_POST['consent_profile'] === 1) ? '1' : '0';
        $consent_ai       = isset($_POST['consent_ai']) && ($_POST['consent_ai'] === '1' || $_POST['consent_ai'] === 1) ? '1' : '0';
        $consent_comms    = isset($_POST['consent_comms']) && ($_POST['consent_comms'] === '1' || $_POST['consent_comms'] === 1) ? '1' : '0';

        update_user_meta($user_id, 'mr_pref_chart', $pref_chart);
        update_user_meta($user_id, 'mr_pref_system', $pref_system);
        update_user_meta($user_id, 'mr_pref_notify', $pref_notify);

        update_user_meta($user_id, 'mr_dpdp_nominee_name', $nominee_name);
        update_user_meta($user_id, 'mr_dpdp_nominee_email', $nominee_email);
        update_user_meta($user_id, 'mr_dpdp_nominee_phone', $nominee_phone);
        update_user_meta($user_id, 'mr_dpdp_nominee_relation', $nominee_relation);

        update_user_meta($user_id, 'mr_dpdp_consent_profiling', $consent_profile);
        update_user_meta($user_id, 'mr_dpdp_consent_ai_chat', $consent_ai);
        update_user_meta($user_id, 'mr_dpdp_consent_comms', $consent_comms);

        // DPDP Act 2023 - Cookie & Storage Preferences
        $cookie_personalization = isset($_POST['cookie_personalization']) && ($_POST['cookie_personalization'] === '1' || $_POST['cookie_personalization'] === 1) ? '1' : '0';
        $cookie_analytics       = isset($_POST['cookie_analytics']) && ($_POST['cookie_analytics'] === '1' || $_POST['cookie_analytics'] === 1) ? '1' : '0';
        $cookie_marketing       = isset($_POST['cookie_marketing']) && ($_POST['cookie_marketing'] === '1' || $_POST['cookie_marketing'] === 1) ? '1' : '0';

        update_user_meta($user_id, 'mr_cookie_personalization', $cookie_personalization);
        update_user_meta($user_id, 'mr_cookie_analytics', $cookie_analytics);
        update_user_meta($user_id, 'mr_cookie_marketing', $cookie_marketing);

        wp_send_json_success([
            'message' => 'Preferences and DPDP Data Principal settings updated successfully!',
            'cookie_consent' => [
                'necessary'       => true,
                'personalization' => $cookie_personalization === '1',
                'analytics'       => $cookie_analytics === '1',
                'marketing'       => $cookie_marketing === '1',
                'status'          => 'custom',
                'timestamp'       => gmdate('c'),
            ]
        ]);
    }

    /**
     * AJAX: Export Data Principal Personal Data Archive (DPDP Act 2023 - Section 11)
     */
    public static function ajax_dpdp_export_data() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to export your personal data archive.']);
        }

        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(['message' => 'User profile could not be loaded.']);
        }

        global $wpdb;

        // 1. Astrological Profile & Preferences
        $astro_data = [
            'full_name'         => get_user_meta($user_id, 'mr_astro_name', true) ?: $user->display_name,
            'gender'            => get_user_meta($user_id, 'mr_astro_gender', true) ?: '',
            'date_of_birth'     => get_user_meta($user_id, 'mr_astro_dob', true) ?: '',
            'time_of_birth'     => get_user_meta($user_id, 'mr_astro_tob', true) ?: '',
            'place_of_birth'    => get_user_meta($user_id, 'mr_astro_pob', true) ?: '',
            'latitude'          => get_user_meta($user_id, 'mr_astro_lat', true) ?: '',
            'longitude'         => get_user_meta($user_id, 'mr_astro_lon', true) ?: '',
            'timezone'          => get_user_meta($user_id, 'mr_astro_tz', true) ?: '5.5',
            'phone'             => get_user_meta($user_id, 'user_phone', true) ?: (get_user_meta($user_id, 'billing_phone', true) ?: ''),
            'preferred_chart'   => get_user_meta($user_id, 'mr_pref_chart', true) ?: 'north_indian',
            'preferred_system'  => get_user_meta($user_id, 'mr_pref_system', true) ?: 'vedic',
            'email_alerts'      => get_user_meta($user_id, 'mr_pref_notify', true) ?: '1',
        ];

        // 2. DPDP Statutory Rights (Nominee & Consents)
        $dpdp_rights = [
            'statutory_framework'     => 'Digital Personal Data Protection Act, 2023 (DPDP Act), Government of India',
            'data_fiduciary'          => 'MeraRashifal Technologies (Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, U.P., 201014)',
            'data_protection_officer' => 'grievance@merarashifal.com',
            'nominee' => [
                'name'         => get_user_meta($user_id, 'mr_dpdp_nominee_name', true) ?: 'Not Nominated',
                'email'        => get_user_meta($user_id, 'mr_dpdp_nominee_email', true) ?: '',
                'phone'        => get_user_meta($user_id, 'mr_dpdp_nominee_phone', true) ?: '',
                'relationship' => get_user_meta($user_id, 'mr_dpdp_nominee_relation', true) ?: '',
            ],
            'consents_granted' => [
                'astrological_profiling' => get_user_meta($user_id, 'mr_dpdp_consent_profiling', true) !== '0',
                'ai_chat_session_memory' => get_user_meta($user_id, 'mr_dpdp_consent_ai_chat', true) !== '0',
                'service_alerts'         => get_user_meta($user_id, 'mr_dpdp_consent_comms', true) !== '0',
            ],
            'cookie_storage_preferences' => [
                'necessary_cookies'       => true,
                'personalization_storage' => get_user_meta($user_id, 'mr_cookie_personalization', true) !== '0',
                'analytics_storage'       => get_user_meta($user_id, 'mr_cookie_analytics', true) !== '0',
                'marketing_storage'       => get_user_meta($user_id, 'mr_cookie_marketing', true) !== '0',
            ],
            'escalation_notice' => 'Under Section 15 of the DPDP Act 2023, the Data Principal has the right to escalate any unresolved grievances to the Data Protection Board of India (DPBI).'
        ];

        // 3. Saved Family Kundlis
        $saved_kundlis = [];
        if (class_exists('Niyati_Storage')) {
            $saved_kundlis = Niyati_Storage::get_saved_kundlis($user_id);
        }

        // 4. Niyati AI Chat Summary
        $chat_data = [];
        if (class_exists('Niyati_Storage')) {
            $chat = Niyati_Storage::get_active_chat($user_id);
            if (!empty($chat['success'])) {
                $chat_data = [
                    'active_profile' => $chat['active_profile_name'] ?? '',
                    'message_count'  => count($chat['messages'] ?? []),
                    'last_active'    => !empty($chat['updated_at']) ? date('c', $chat['updated_at']) : '',
                    'messages'       => $chat['messages'] ?? []
                ];
            }
        }

        // 5. Niyati Credit Wallet
        $wallet_data = [];
        if (class_exists('Niyati_Wallet')) {
            $wallet = Niyati_Wallet::get_wallet('', $user_id);
            if ($wallet) {
                $wallet_data = [
                    'credits_balance' => intval($wallet->credits_balance),
                    'total_purchased' => intval($wallet->total_purchased),
                    'total_consumed'  => intval($wallet->total_consumed),
                    'expires_at'      => $wallet->validity_expires_at
                ];
            }
        }

        // 6. Generated Reports Archive
        $table_reports = $wpdb->prefix . 'mr_generated_reports';
        $reports = $wpdb->get_results($wpdb->prepare("
            SELECT report_type, title, language, pdf_url, created_at 
            FROM $table_reports 
            WHERE user_id = %d 
            ORDER BY id DESC
        ", $user_id), ARRAY_A);

        // 7. Financial Transactions & Invoices (Audit Trail)
        $table_txns = $wpdb->prefix . 'mr_transactions';
        $txns = $wpdb->get_results($wpdb->prepare("
            SELECT internal_txn_id, channel, gateway, gross_amount, net_amount, status, invoice_number, created_at 
            FROM $table_txns 
            WHERE user_id = %d OR customer_email = %s 
            ORDER BY id DESC
        ", $user_id, $user->user_email), ARRAY_A);

        $export_payload = [
            'export_meta' => [
                'export_id'      => 'DPDP-' . $user_id . '-' . time(),
                'generated_at'   => current_time('mysql'),
                'iso_timestamp'  => current_time('c'),
                'fiduciary'      => 'MeraRashifal Technologies',
                'jurisdiction'   => 'Republic of India (DPDP Act, 2023)',
            ],
            'data_principal' => [
                'user_id'         => $user_id,
                'email'           => $user->user_email,
                'registered_date' => $user->user_registered,
            ],
            'astrological_profile'    => $astro_data,
            'dpdp_statutory_rights'   => $dpdp_rights,
            'saved_family_kundlis'    => $saved_kundlis,
            'niyati_ai_conversations' => $chat_data,
            'ai_credit_wallet'        => $wallet_data,
            'reports_archive'         => $reports ?: [],
            'transaction_ledger'      => $txns ?: [],
        ];

        wp_send_json_success([
            'filename' => 'merarashifal-data-principal-export-' . $user_id . '.json',
            'data'     => $export_payload
        ]);
    }

    /**
     * Generate dynamic anti-bot challenge phrase and randomized sentence for DPDP Erasure
     * Pool of 50 varied sentences where {WORD} placement and grammatical structure shifts.
     */
    public static function generate_dpdp_erase_challenge($user_id) {
        $words = [
            'PURGE', 'ERASE', 'EXPUNGE', 'TERMINATE', 'DESTROY', 'REVOKE', 'DISCARD', 'OBLITERATE',
            'CLEANSE', 'ABOLISH', 'MUKTI', 'NIRVANA', 'PRALAYA', 'SAMAPTI', 'TYAGA', 'VILAYA',
            'DISMISS', 'AUTHORIZE', 'OVERWRITE', 'RELEASE', 'NULLIFY', 'SHRED', 'UNBIND', 'EXTINGUISH',
            'WITHDRAW', 'FORGET', 'CANCEL', 'PERMANENT', 'SECURE', 'CERTIFY', 'SANCTION', 'ARCHIVE',
            'DISCONNECT', 'DETACH', 'SEVER', 'ELIMINATE', 'SCRUB', 'RESET', 'FORFEIT', 'REDUCE',
            'EXPIRE', 'REMOVE', 'VOID', 'SHASTRA', 'NAKSHATRA', 'VIMSHOTTARI', 'JYOTISH', 'CHAKRA',
            'SANKALPA', 'MOKSHA'
        ];

        // Combine random word with 3-digit security integer to eliminate bot dictionary attacks
        $random_word = $words[array_rand($words)] . '-' . wp_rand(100, 999);

        // 50 diverse sentence templates with varied word positioning (front, middle, pre-end, end)
        $sentences = [
            'As an anti-bot security safeguard, please verify your erasure request by typing {WORD} below:',
            'To verify this is an intentional manual human action, enter {WORD} into the field:',
            'Security verification active: please input {WORD} in capital letters to authorize account destruction:',
            'Preventing automated deletion attempts: confirm your manual consent by typing {WORD} as displayed:',
            'For fraud protection and anti-bot validation, enter the security phrase {WORD} to continue:',
            'To authenticate this irreversible action and ensure anti-tamper security, write {WORD} below:',
            'Human confirmation required: type {WORD} into the verification box to proceed with data erasure:',
            'Anti-malware protocol engaged: authorize the deletion of your records by submitting {WORD}:',
            'Security checkpoint: type the verification code {WORD} in all capital letters to validate your request:',
            'To certify that you are manually requesting this wipe, please enter {WORD} in the confirmation field:',
            'Type {WORD} in the box below to authorize the irreversible removal of all your data:',
            'Enter {WORD} precisely as highlighted to affirm your decision to permanently delete this account:',
            'Please write {WORD} into the space provided if you truly wish to wipe your astrological vault:',
            'Input {WORD} in capital letters below to authorize the permanent deletion of your profile:',
            'Submit {WORD} to authenticate and execute the irreversible erasure of your customer profile:',
            'Typing {WORD} into the confirmation box will permanently destroy all stored Kundli archives:',
            'By entering {WORD} below, you authorize our servers to immediately wipe your consultation history:',
            'Provide the security token {WORD} to validate your legal request for account termination:',
            'Write {WORD} in the entry field to finalize the deletion of your account and credentials:',
            'Key in {WORD} below to authorize the permanent disconnection of your Google authentication:',
            'To exercise statutory erasure under Section 12 of the DPDP Act 2023, enter {WORD} below:',
            'In accordance with DPDP statutory requirements, confirm the complete erasure of your data by typing {WORD}:',
            'Statutory Data Principal verification: input {WORD} to execute your right to be forgotten:',
            'Under Section 12 of India\'s DPDP Act, confirm permanent data destruction by entering {WORD}:',
            'To finalize your statutory Data Principal erasure directive, type the verification word {WORD}:',
            'Section 12 DPDP compliance check: authenticate the permanent deletion of your personal data by typing {WORD}:',
            'Fiduciary erasure mandate: authorize the purging of your stored personal attributes by entering {WORD}:',
            'Statutory audit notice acknowledged: please confirm data destruction by typing {WORD} as highlighted:',
            'Exercising your Right to Erasure requires typing {WORD} into the confirmation field below:',
            'To formally revoke your data fiduciary profile under Indian privacy law, enter {WORD}:',
            'Please confirm the permanent destruction of your Vedic birth charts and calculations by typing {WORD}:',
            'To permanently wipe your saved family Kundlis and planetary transit records, enter {WORD}:',
            'Authorize the complete dissolution and erasure of your astrological archives by typing {WORD}:',
            'Before we permanently wipe your Vedic profile and consultation logs, verify by entering {WORD}:',
            'To purge all astrological birth details, planetary charts, and saved horoscopes, type {WORD}:',
            'Your horoscope vaults and family Kundlis will be unrecoverable: confirm by submitting {WORD}:',
            'Ensure you want to permanently discard your saved Kundlis and reports by entering {WORD}:',
            'Dissolving your Vedic consultation vault requires you to type {WORD} into the field below:',
            'To irreversibly clear your birth time, coordinates, and Rashi profile, please enter {WORD}:',
            'Wiping all astrological report archives and personal records requires entering {WORD}:',
            'Before we purge your Niyati AI conversation history and unused credits, verify by entering {WORD}:',
            'To delete all Niyati AI astrologer chat sessions and reset your profile, write {WORD} below:',
            'Permanently purging your AI consultation memory and wallet balance requires typing {WORD}:',
            'To confirm that you understand this action cannot be undone, please type {WORD} into the box:',
            'Final authorization required: type {WORD} to immediately terminate your account and wipe all records:',
            'This action is irreversible and immediate: please type {WORD} as shown to verify:',
            'To execute account closure and erase all linked Google OAuth tokens, enter {WORD}:',
            'Certify that you wish to surrender all active tokens and erase your account by entering {WORD}:',
            'Confirm the immediate and permanent termination of your MeraRashifal account by typing {WORD}:',
            'Final verification step: enter the exact phrase {WORD} to begin the automated erasure sequence:'
        ];

        $sentence_template = $sentences[array_rand($sentences)];
        $highlight_html = '<mark class="mr-dpdp-highlight-word">' . esc_html($random_word) . '</mark>';
        $sentence_html  = str_replace('{WORD}', $highlight_html, esc_html($sentence_template));

        // Store challenge in transient and usermeta (valid for 30 minutes)
        if ($user_id > 0) {
            set_transient('mr_dpdp_erase_token_' . $user_id, $random_word, 30 * MINUTE_IN_SECONDS);
            update_user_meta($user_id, 'mr_dpdp_erase_token', $random_word);
        }

        return [
            'word'          => $random_word,
            'sentence_html' => $sentence_html,
            'raw_sentence'  => str_replace('{WORD}', $random_word, $sentence_template)
        ];
    }

    /**
     * AJAX endpoint to fetch a fresh erasure challenge
     */
    public static function ajax_get_erase_challenge() {
        check_ajax_referer('mr_account_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in.']);
        }
        $challenge = self::generate_dpdp_erase_challenge($user_id);
        wp_send_json_success($challenge);
    }

    /**
     * AJAX: Right to Erasure / Account Deletion (DPDP Act 2023 - Section 12)
     */
    public static function ajax_dpdp_erase_account() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to initiate account erasure.']);
        }

        // Safety Barrier: Prevent deleting administrative accounts through frontend
        if (current_user_can('manage_options') || current_user_can('edit_posts') || $user_id === 1) {
            wp_send_json_error([
                'message' => 'Administrative accounts cannot be erased via the customer portal for site security. Please perform administrative user operations from the secure management console.'
            ]);
        }

        $phrase = trim($_POST['confirm_phrase'] ?? '');
        $expected = get_transient('mr_dpdp_erase_token_' . $user_id) ?: get_user_meta($user_id, 'mr_dpdp_erase_token', true);

        if (empty($expected) || strcasecmp($phrase, $expected) !== 0) {
            $new_challenge = self::generate_dpdp_erase_challenge($user_id);
            wp_send_json_error([
                'message'       => 'Security verification phrase mismatch or expired. A fresh anti-bot verification phrase has been generated below.',
                'new_challenge' => $new_challenge
            ]);
        }

        $user = get_userdata($user_id);
        $user_email = $user ? $user->user_email : '';

        global $wpdb;

        // 1. Purge Niyati AI Chat Files and Family Kundlis from server disk
        if (class_exists('Niyati_Storage')) {
            $base_dir = Niyati_Storage::get_base_dir();
            $kundli_file = trailingslashit($base_dir) . 'kundli/user_' . $user_id . '_kundlis.json';
            if (file_exists($kundli_file)) {
                @unlink($kundli_file);
            }

            $chat_file = trailingslashit($base_dir) . 'chats/user_' . $user_id . '.json';
            if (file_exists($chat_file)) {
                @unlink($chat_file);
            }

            // Purge any archives matching user_{user_id}
            $archive_dir = trailingslashit($base_dir) . 'archive';
            if (is_dir($archive_dir)) {
                $archives = glob($archive_dir . '/*user_' . $user_id . '*');
                if (is_array($archives)) {
                    foreach ($archives as $arch) {
                        @unlink($arch);
                    }
                }
            }

            // Purge any uploaded palm images from palm-cache
            $uploads = wp_upload_dir();
            $palm_cache = trailingslashit($uploads['basedir']) . 'palm-cache';
            if (is_dir($palm_cache)) {
                $palm_files = glob($palm_cache . '/*user_' . $user_id . '*');
                if (is_array($palm_files)) {
                    foreach ($palm_files as $pf) {
                        @unlink($pf);
                    }
                }
            }
        }

        // 2. Anonymize Financial Records (Statutory Indian GST & Taxation audit compliance)
        // Financial transaction rows must be retained for legal tax audit, but personal identifying data is completely anonymized.
        $table_txns = $wpdb->prefix . 'mr_transactions';
        $anonymized_email = 'erased_' . substr(md5($user_id . (defined('AUTH_KEY') ? AUTH_KEY : 'dpdp_salt')), 0, 10) . '@anonymized.dpdp.merarashifal.com';
        
        $wpdb->query($wpdb->prepare("
            UPDATE $table_txns 
            SET user_id = 0,
                customer_name = 'Anonymized Data Principal',
                customer_email = %s,
                customer_phone = '',
                metadata = NULL
            WHERE user_id = %d OR customer_email = %s
        ", $anonymized_email, $user_id, $user_email));

        // 3. Purge Generated Reports Records
        $table_reports = $wpdb->prefix . 'mr_generated_reports';
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table_reports 
            WHERE user_id = %d
        ", $user_id));

        // 4. Purge Niyati Wallets & Credit Ledger
        $table_wallets = $wpdb->prefix . 'niyati_wallets';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_wallets'") === $table_wallets) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_wallets WHERE user_id = %d", $user_id));
        }
        $table_ledger = $wpdb->prefix . 'niyati_credit_ledger';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_ledger'") === $table_ledger) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_ledger WHERE user_id = %d", $user_id));
        }

        // 5. Delete transient & WordPress User
        delete_transient('mr_dpdp_erase_token_' . $user_id);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($user_id);

        // 6. Terminate session
        wp_logout();

        wp_send_json_success([
            'message'      => 'Your account and personal data have been completely erased in compliance with Section 12 of the DPDP Act 2023.',
            'redirect_url' => add_query_arg(['mr_dpdp_erased' => '1'], self::get_account_page_url())
        ]);
    }

    /**
     * Compute Vedic Sun Sign (Surya Rashi) from DOB
     */
    public static function calculate_vedic_sun_sign($dob_str) {
        $time = strtotime($dob_str);
        if (!$time) {
            return null;
        }

        $month = intval(date('n', $time));
        $day   = intval(date('j', $time));

        // Vedic Sidereal Sun transit ranges
        $zodiac = [
            ['name' => 'Mesha (Aries - मेष)', 'element' => 'Fire (अग्नि)', 'ruler' => 'Mars (मंगल)', 'from_m' => 4, 'from_d' => 14, 'to_m' => 5, 'to_d' => 14],
            ['name' => 'Vrishabha (Taurus - वृषभ)', 'element' => 'Earth (पृथ्वी)', 'ruler' => 'Venus (शुक्र)', 'from_m' => 5, 'from_d' => 15, 'to_m' => 6, 'to_d' => 14],
            ['name' => 'Mithuna (Gemini - मिथुन)', 'element' => 'Air (वायु)', 'ruler' => 'Mercury (बुध)', 'from_m' => 6, 'from_d' => 15, 'to_m' => 7, 'to_d' => 15],
            ['name' => 'Karka (Cancer - कर्क)', 'element' => 'Water (जल)', 'ruler' => 'Moon (चंद्र)', 'from_m' => 7, 'from_d' => 16, 'to_m' => 8, 'to_d' => 16],
            ['name' => 'Simha (Leo - सिंह)', 'element' => 'Fire (अग्नि)', 'ruler' => 'Sun (सूर्य)', 'from_m' => 8, 'from_d' => 17, 'to_m' => 9, 'to_d' => 16],
            ['name' => 'Kanya (Virgo - कन्या)', 'element' => 'Earth (पृथ्वी)', 'ruler' => 'Mercury (बुध)', 'from_m' => 9, 'from_d' => 17, 'to_m' => 10, 'to_d' => 16],
            ['name' => 'Tula (Libra - तुला)', 'element' => 'Air (वायु)', 'ruler' => 'Venus (शुक्र)', 'from_m' => 10, 'from_d' => 17, 'to_m' => 11, 'to_d' => 15],
            ['name' => 'Vrischika (Scorpio - वृश्चिक)', 'element' => 'Water (जल)', 'ruler' => 'Mars (मंगल)', 'from_m' => 11, 'from_d' => 16, 'to_m' => 12, 'to_d' => 15],
            ['name' => 'Dhanu (Sagittarius - धनु)', 'element' => 'Fire (अग्नि)', 'ruler' => 'Jupiter (बृहस्पति)', 'from_m' => 12, 'from_d' => 16, 'to_m' => 1, 'to_d' => 13],
            ['name' => 'Makara (Capricorn - मकर)', 'element' => 'Earth (पृथ्वी)', 'ruler' => 'Saturn (शनि)', 'from_m' => 1, 'from_d' => 14, 'to_m' => 2, 'to_d' => 12],
            ['name' => 'Kumbha (Aquarius - कुंभ)', 'element' => 'Air (वायु)', 'ruler' => 'Saturn (शनि)', 'from_m' => 2, 'from_d' => 13, 'to_m' => 3, 'to_d' => 13],
            ['name' => 'Meena (Pisces - मीन)', 'element' => 'Water (जल)', 'ruler' => 'Jupiter (बृहस्पति)', 'from_m' => 3, 'from_d' => 14, 'to_m' => 4, 'to_d' => 13],
        ];

        foreach ($zodiac as $z) {
            if ($z['from_m'] === $z['to_m']) {
                if ($month === $z['from_m'] && $day >= $z['from_d'] && $day <= $z['to_d']) {
                    return $z;
                }
            } else {
                if (($month === $z['from_m'] && $day >= $z['from_d']) || ($month === $z['to_m'] && $day <= $z['to_d'])) {
                    return $z;
                }
            }
        }

        return $zodiac[0];
    }

    /**
     * Build Consolidated Unified Ledger (Orders + Niyati Credit Awards + Grouped Chat Usages)
     */
    public static function get_user_unified_ledger($user_id) {
        global $wpdb;
        $user = get_userdata($user_id);
        if (!$user) {
            return [];
        }

        // Fetch user financial transactions
        $table_txns = $wpdb->prefix . 'mr_transactions';
        $txns = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_txns 
            WHERE user_id = %d OR customer_email = %s 
            ORDER BY id DESC
        ", $user_id, $user->user_email));

        // Fetch Niyati AI Astrologer Credit Wallet & Ledger
        $niyati_wallet = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_wallet('', $user_id) : null;
        $niyati_visitor_id = $niyati_wallet ? $niyati_wallet->visitor_id : '';
        $credit_ledger = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_user_credit_ledger($user_id, $niyati_visitor_id) : [];

        // Index credit ledger items by payment ref_id to avoid double counting
        $credit_by_ref = [];
        foreach ($credit_ledger as $item) {
            if (!empty($item['ref_id'])) {
                $credit_by_ref[$item['ref_id']] = $item;
            }
        }

        $unified_ledger = [];
        $consumed_payment_ids = [];

        // 1. Process Financial Orders & Subscriptions
        foreach ($txns as $t) {
            $pay_id = !empty($t->gateway_payment_id) ? $t->gateway_payment_id : '';
            $matched_credit = (!empty($pay_id) && isset($credit_by_ref[$pay_id])) ? $credit_by_ref[$pay_id] : null;

            $meta = !empty($t->metadata) ? json_decode($t->metadata, true) : [];
            $bonus_credits = !empty($meta['chat_credits']) ? intval($meta['chat_credits']) : 0;
            if (!$bonus_credits && $matched_credit && $matched_credit['credits'] > 0) {
                $bonus_credits = $matched_credit['credits'];
            }

            $channel_name = ($t->channel === 'subscription') ? 'Subscription Pack' : (($t->channel === 'chat_credit') ? 'Niyati AI Chat' : 'Report Order');
            $groups = ['orders'];

            if ($t->channel === 'chat_credit' || $bonus_credits > 0) {
                $groups[] = 'credits';
            }

            $badge_class = 'mr-badge-muted';
            $credits_badge = 'Included';
            if ($bonus_credits > 0) {
                $credits_badge = "+{$bonus_credits} Questions";
                $badge_class = 'mr-badge-credit-plus';
            }

            $invoice_no = $t->invoice_number ?? '';
            // Auto-assign sequential non-GST retail invoice if order was paid and missing invoice number
            if (empty($invoice_no) && $t->status === 'success' && class_exists('MR_Invoice')) {
                $invoice_no = MR_Invoice::assign_invoice_to_transaction($t->id);
                $t->invoice_number = $invoice_no;
            }

            $invoice_url = '';
            if ($t->status === 'success' || !empty($invoice_no) || !empty($t->internal_txn_id)) {
                if (class_exists('MR_Invoice')) {
                    $invoice_url = MR_Invoice::get_invoice_url((array)$t);
                } else {
                    $k = substr(md5(($t->internal_txn_id ?? '') . ($t->customer_email ?? '') . AUTH_KEY), 0, 12);
                    $invoice_url = add_query_arg(['mr_invoice' => 1, 'id' => ($t->internal_txn_id ?? ''), 'k' => $k], home_url('/'));
                }
            }

            $subtitle = !empty($invoice_no) ? ('Invoice: ' . $invoice_no) : (!empty($pay_id) ? $pay_id : '');
            if ($bonus_credits > 0 && $t->channel !== 'chat_credit') {
                $subtitle .= ' • Includes ' . $bonus_credits . ' Free Niyati Questions';
            }

            $status_class = ($t->status === 'success') ? 'mr-status-ready' : (($t->status === 'refunded') ? 'mr-status-refund' : 'mr-status-pending');

            $unified_ledger[] = [
                'id'            => 'ORD_' . $t->id,
                'type'          => 'order',
                'groups'        => $groups,
                'timestamp'     => strtotime($t->created_at),
                'date'          => date('d M Y', strtotime($t->created_at)),
                'time'          => date('h:i A', strtotime($t->created_at)),
                'time_window'   => date('h:i A', strtotime($t->created_at)),
                'ref_id'        => !empty($t->internal_txn_id) ? $t->internal_txn_id : $pay_id,
                'item_category' => $channel_name,
                'title'         => $t->item_name,
                'subtitle'      => $subtitle,
                'credits_num'   => $bonus_credits,
                'credits_badge' => $credits_badge,
                'badge_class'   => $badge_class,
                'amount'        => floatval($t->net_amount),
                'amount_text'   => ($t->net_amount > 0) ? '₹' . number_format($t->net_amount, 2) : 'Free',
                'status'        => $t->status,
                'status_label'  => ($t->status === 'success') ? 'Success' : ucfirst($t->status),
                'status_class'  => $status_class,
                'invoice_no'    => $invoice_no,
                'invoice_url'   => $invoice_url
            ];

            if (!empty($pay_id)) {
                $consumed_payment_ids[$pay_id] = true;
            }
        }

        // 2. Add remaining Niyati Credit Usages & standalone Credit Awards
        foreach ($credit_ledger as $c) {
            if (!empty($c['ref_id']) && isset($consumed_payment_ids[$c['ref_id']])) {
                continue; // Already unified with order row
            }

            $is_usage = ($c['type'] === 'credit_usage');
            $groups = ['credits'];

            $status_class = ($c['status'] === 'completed' || $c['status'] === 'consumed') ? 'mr-status-ready' : (($c['status'] === 'refunded') ? 'mr-status-refund' : 'mr-status-pending');

            $unified_ledger[] = [
                'id'            => $c['id'],
                'type'          => $c['type'],
                'groups'        => $groups,
                'timestamp'     => $c['timestamp'],
                'date'          => $c['date_formatted'],
                'time'          => $c['time_formatted'],
                'time_window'   => $c['time_window'],
                'ref_id'        => $c['ref_id'],
                'item_category' => $is_usage ? 'Niyati AI Consultation' : 'Niyati AI Chat',
                'title'         => $c['title'],
                'subtitle'      => $c['subtitle'],
                'credits_num'   => $c['credits'],
                'credits_badge' => $c['impact_text'],
                'badge_class'   => $c['badge_class'],
                'amount'        => $c['amount'],
                'amount_text'   => $c['amount_text'],
                'status'        => $c['status'],
                'status_label'  => $c['status_label'],
                'status_class'  => $status_class,
                'invoice_no'    => ''
            ];
        }

        // Sort all entries in chronological descending order
        usort($unified_ledger, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $unified_ledger;
    }

    /**
     * Filter and slice ledger with 25 entries per page limit
     */
    public static function filter_and_paginate_ledger($ledger, $filter = 'all', $page = 1, $per_page = 25) {
        $count_all = count($ledger);
        $count_credits = 0;
        $count_orders = 0;
        $matched = [];

        foreach ($ledger as $item) {
            $is_credits = in_array('credits', $item['groups']);
            $is_orders  = in_array('orders', $item['groups']);

            if ($is_credits) $count_credits++;
            if ($is_orders)  $count_orders++;

            if ($filter === 'all' || ($filter === 'credits' && $is_credits) || ($filter === 'orders' && $is_orders)) {
                $matched[] = $item;
            }
        }

        $total_matched = count($matched);
        $total_pages   = max(1, (int) ceil($total_matched / $per_page));
        $page          = max(1, min($page, $total_pages));
        $offset        = ($page - 1) * $per_page;
        $slice         = array_slice($matched, $offset, $per_page);

        return [
            'items'         => $slice,
            'total_matched' => $total_matched,
            'total_pages'   => $total_pages,
            'page'          => $page,
            'per_page'      => $per_page,
            'counts'        => [
                'all'     => $count_all,
                'credits' => $count_credits,
                'orders'  => $count_orders,
            ]
        ];
    }

    /**
     * Render passbook table / card rows HTML (Zero DOM Bloat - Exactly 25 items max)
     */
    public static function render_passbook_rows_html($items) {
        if (empty($items)) {
            return '<tr class="mr-empty-row"><td colspan="5"><div class="mr-empty-state-box" style="padding:30px 20px;"><h4>No Records Found</h4><p>There are no transactions matching the selected filter.</p></div></td></tr>';
        }

        ob_start();
        foreach ($items as $t): ?>
            <tr class="mr-txn-row">
                <!-- Cell 1: Date & Time -->
                <td class="mr-col-date">
                    <div class="mr-cell-date"><strong><?php echo esc_html($t['date']); ?></strong></div>
                    <div class="mr-cell-time"><?php echo esc_html($t['time_window']); ?></div>
                </td>

                <!-- Cell 2: Item Description, Channel, & Reference ID -->
                <td class="mr-col-item">
                    <div class="mr-item-top-row">
                        <span class="mr-channel-badge mr-category-<?php echo esc_attr(sanitize_title($t['item_category'])); ?>">
                            <?php echo esc_html($t['item_category']); ?>
                        </span>
                        <!-- Mobile status pill shown next to category on mobile -->
                        <span class="mr-status-pill mr-mobile-only <?php echo esc_attr($t['status_class']); ?>">
                            <?php echo esc_html($t['status_label']); ?>
                        </span>
                    </div>

                    <div class="mr-item-name"><strong><?php echo esc_html($t['title']); ?></strong></div>

                    <?php if (!empty($t['subtitle'])): ?>
                        <div class="mr-item-subtitle"><?php echo esc_html($t['subtitle']); ?></div>
                    <?php endif; ?>

                    <div class="mr-item-meta-row">
                        <code class="mr-code-badge" title="Transaction / Order Reference ID"><?php echo esc_html($t['ref_id']); ?></code>
                        <?php if (!empty($t['invoice_no'])): ?>
                            <span class="mr-invoice-tag" title="Official Retail Invoice"><?php echo esc_html($t['invoice_no']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($t['invoice_url'])): ?>
                            <a href="<?php echo esc_url($t['invoice_url']); ?>" target="_blank" rel="noopener noreferrer" class="mr-btn-invoice-link" title="Download & Print Tax Invoice (PDF / Receipt)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span>Download Invoice &darr;</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Cell 3: Credits Impact -->
                <td class="mr-col-credits">
                    <span class="mr-mobile-label">Impact:</span>
                    <span class="mr-credit-pill <?php echo esc_attr($t['badge_class']); ?>">
                        <?php echo esc_html($t['credits_badge']); ?>
                    </span>
                </td>

                <!-- Cell 4: Amount -->
                <td class="mr-col-amount">
                    <span class="mr-mobile-label">Amount:</span>
                    <span class="mr-amount-val"><?php echo esc_html($t['amount_text']); ?></span>
                </td>

                <!-- Cell 5: Status & Invoice -->
                <td class="mr-col-status mr-desktop-only">
                    <div class="mr-status-cell-wrap">
                        <span class="mr-status-pill <?php echo esc_attr($t['status_class']); ?>">
                            <?php if ($t['status'] === 'success' || $t['status'] === 'completed' || $t['status'] === 'consumed'): ?>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span><?php echo esc_html($t['status_label']); ?></span>
                            <?php elseif ($t['status'] === 'refunded'): ?>
                                <span>Refunded</span>
                            <?php else: ?>
                                <span><?php echo esc_html($t['status_label']); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($t['invoice_url'])): ?>
                            <a href="<?php echo esc_url($t['invoice_url']); ?>" target="_blank" rel="noopener noreferrer" class="mr-btn-invoice-pill" title="Download Official Invoice">
                                <span>Invoice &darr;</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach;
        return ob_get_clean();
    }

    /**
     * Render passbook pagination buttons HTML
     */
    public static function render_passbook_pagination_html($total_pages, $current_page = 1) {
        if ($total_pages <= 1) {
            return '';
        }

        ob_start(); ?>
        <div class="mr-pagination-wrap" id="mr-passbook-pagination" data-total-pages="<?php echo $total_pages; ?>" data-current-page="<?php echo $current_page; ?>">
            <button type="button" class="mr-page-btn mr-prev-page" <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>>&laquo; Prev</button>
            <div class="mr-page-numbers">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <button type="button" class="mr-page-num <?php echo ($p == $current_page) ? 'active' : ''; ?>" data-page="<?php echo $p; ?>"><?php echo $p; ?></button>
                <?php endfor; ?>
            </div>
            <button type="button" class="mr-page-btn mr-next-page" <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>>&raquo; Next</button>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: On-demand Passbook pagination and tab filtering (loads initial 25 per tab)
     */
    public static function ajax_get_passbook_page() {
        check_ajax_referer('mr_account_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to view transactions.']);
        }

        $filter   = isset($_POST['filter']) ? sanitize_key($_POST['filter']) : 'all';
        $page     = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = 25; // Strict 25 entries per page

        $ledger = self::get_user_unified_ledger($user_id);
        $data   = self::filter_and_paginate_ledger($ledger, $filter, $page, $per_page);

        $rows_html       = self::render_passbook_rows_html($data['items']);
        $pagination_html = self::render_passbook_pagination_html($data['total_pages'], $data['page']);

        wp_send_json_success([
            'rows_html'       => $rows_html,
            'pagination_html' => $pagination_html,
            'total_matched'   => $data['total_matched'],
            'total_pages'     => $data['total_pages'],
            'current_page'    => $data['page'],
            'filter'          => $filter,
            'counts'          => $data['counts']
        ]);
    }

    /**
     * AJAX: Submit Customer Support Ticket with single picture attachment
     * Delivers inquiry and attachment directly to support@merarashifal.com
     */
    public static function ajax_submit_support_ticket() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $name     = sanitize_text_field($_POST['support_name'] ?? '');
        $email    = sanitize_email($_POST['support_email'] ?? '');
        $phone    = sanitize_text_field($_POST['support_phone'] ?? '');
        $category = sanitize_text_field($_POST['support_category'] ?? 'Astrological Consultation & Support');
        $subject  = sanitize_text_field($_POST['support_subject'] ?? '');
        $message  = sanitize_textarea_field($_POST['support_message'] ?? ($_POST['support_question'] ?? ''));

        $user_id = get_current_user_id();
        if ($user_id > 0) {
            $current_user = get_userdata($user_id);
            if (empty($name) && $current_user) {
                $name = $current_user->display_name;
            }
            if (empty($email) && $current_user) {
                $email = $current_user->user_email;
            }
        }

        if (empty($name)) {
            $name = 'Valued Customer';
        }
        if (empty($email) || !is_email($email)) {
            $email = 'support@merarashifal.com';
        }

        if (empty($message)) {
            wp_send_json_error(['message' => 'Please enter your question before submitting.']);
        }

        if (empty($subject)) {
            $trimmed = wp_trim_words($message, 8, '...');
            $subject = 'Support Question from ' . $name . ': ' . ($trimmed ?: 'General Astro Query');
        }

        $user_id = get_current_user_id();
        $attachments = [];
        $temp_filepath = '';
        $original_filename = '';

        // Handle single image attachment if provided
        if (!empty($_FILES['support_attachment']) && !empty($_FILES['support_attachment']['name'])) {
            $file = $_FILES['support_attachment'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                wp_send_json_error(['message' => 'File upload error (Code: ' . intval($file['error']) . '). Please try again or submit without an attachment.']);
            }

            // Enforce max 5MB size limit
            $max_bytes = 5 * 1024 * 1024;
            if ($file['size'] > $max_bytes) {
                wp_send_json_error(['message' => 'The uploaded picture exceeds the 5MB size limit. Please choose a smaller image.']);
            }

            // Allowed extensions & mime types - single picture only
            $file_info = wp_check_filetype($file['name'], [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'          => 'image/png',
                'webp'         => 'image/webp',
            ]);

            if (empty($file_info['ext']) || empty($file_info['type'])) {
                wp_send_json_error(['message' => 'Invalid file format. Only single picture attachments (JPG, JPEG, PNG, WEBP) are allowed.']);
            }

            // Double check MIME if finfo is available
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mime, $allowed_mimes, true)) {
                    wp_send_json_error(['message' => 'Invalid image content detected. Please upload a valid PNG, JPG, or WEBP image.']);
                }
            }

            // Safe staging directory for email attachment
            $upload_dir = wp_upload_dir();
            $staging_dir = trailingslashit($upload_dir['basedir']) . 'mr-support-temp/';
            if (!file_exists($staging_dir)) {
                wp_mkdir_p($staging_dir);
                @file_put_contents($staging_dir . '.htaccess', "Options -Indexes\n<Files *>\nDeny from all\n</Files>");
            }

            $original_filename = sanitize_file_name($file['name']);
            $temp_filename = 'ticket_' . time() . '_' . wp_generate_password(8, false) . '.' . $file_info['ext'];
            $temp_filepath = $staging_dir . $temp_filename;

            if (!move_uploaded_file($file['tmp_name'], $temp_filepath)) {
                wp_send_json_error(['message' => 'Could not save the uploaded attachment. Please try again.']);
            }

            $attachments[] = $temp_filepath;
        }

        // Support desk recipient
        $to = 'support@merarashifal.com';
        $email_subject = sprintf('[Support Ticket: %s] %s - %s', $category, $subject, $name);

        $html_body = self::render_support_ticket_email_html([
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'category'      => $category,
            'subject'       => $subject,
            'message'       => $message,
            'user_id'       => $user_id,
            'has_file'      => !empty($temp_filepath),
            'filename'      => $original_filename,
            'ip'            => !empty($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'Unknown',
            'submitted_at'  => current_time('d M Y, h:i A') . ' IST',
        ]);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $email . '>',
        ];

        $mail_sent = wp_mail($to, $email_subject, $html_body, $headers, $attachments);

        // Clean up temporary attachment file immediately after dispatch
        if (!empty($temp_filepath) && file_exists($temp_filepath)) {
            @unlink($temp_filepath);
        }

        if ($mail_sent) {
            wp_send_json_success([
                'message' => 'Thank you! Your question has been emailed directly to support@merarashifal.com. Our astrological support team will review and reply to ' . esc_html($email) . ' shortly.'
            ]);
        } else {
            wp_send_json_error([
                'message' => 'We could not dispatch your question through the automated mailer at this moment. Please email your query directly to support@merarashifal.com.'
            ]);
        }
    }

    /**
     * Render Branded HTML Email for Support Ticket
     */
    public static function render_support_ticket_email_html($data) {
        $name         = esc_html($data['name']);
        $email        = esc_html($data['email']);
        $phone        = !empty($data['phone']) ? esc_html($data['phone']) : 'Not Provided';
        $category     = esc_html($data['category']);
        $subject      = esc_html($data['subject']);
        $message      = nl2br(esc_html($data['message']));
        $user_id      = !empty($data['user_id']) ? intval($data['user_id']) : null;
        $has_file     = !empty($data['has_file']);
        $filename     = esc_html($data['filename']);
        $submitted_at = esc_html($data['submitted_at']);
        $ip           = esc_html($data['ip']);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Support Ticket: <?php echo $subject; ?></title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1E293B;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F8FAFC; padding: 30px 15px;">
                <tr>
                    <td align="center">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 620px; background: #FFFFFF; border-radius: 14px; overflow: hidden; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                            
                            <!-- Header Accent Bar -->
                            <tr>
                                <td height="6" style="background: linear-gradient(90deg, #F58220 0%, #D9A441 50%, #7A1F3D 100%);"></td>
                            </tr>

                            <!-- Brand Header -->
                            <tr>
                                <td style="padding: 24px 30px 20px; border-bottom: 1px solid #F1F5F9; background: #FFFDF9;">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td>
                                                <h1 style="margin: 0; font-size: 22px; color: #7A1F3D; font-weight: 800; letter-spacing: -0.3px;">MeraRashifal Support</h1>
                                                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748B;">Official Customer Helpdesk &amp; Technical Guidance</p>
                                            </td>
                                            <td align="right">
                                                <span style="display: inline-block; background: #FEF3C7; color: #92400E; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 20px; border: 1px solid #FDE68A;">
                                                    New Ticket
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Ticket Category & Subject -->
                            <tr>
                                <td style="padding: 24px 30px 16px;">
                                    <div style="background: #F8FAFC; border-left: 4px solid #F58220; padding: 14px 18px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #F58220; letter-spacing: 0.5px; margin-bottom: 4px;">
                                            Category: <?php echo $category; ?>
                                        </div>
                                        <h2 style="margin: 0; font-size: 17px; color: #0F172A; font-weight: 700; line-height: 1.4;">
                                            <?php echo $subject; ?>
                                        </h2>
                                    </div>

                                    <!-- Customer Details Grid -->
                                    <table width="100%" cellspacing="0" cellpadding="8" border="0" style="font-size: 13.5px; background: #FFFFFF; border: 1px solid #EDF2F7; border-radius: 8px; margin-bottom: 20px;">
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td width="35%" style="color: #64748B; font-weight: 600; padding: 10px 14px; background: #FAFBFD;">Customer Name:</td>
                                            <td style="color: #0F172A; font-weight: 700; padding: 10px 14px;"><?php echo $name; ?></td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="color: #64748B; font-weight: 600; padding: 10px 14px; background: #FAFBFD;">Email Address:</td>
                                            <td style="color: #0F172A; font-weight: 600; padding: 10px 14px;">
                                                <a href="mailto:<?php echo $email; ?>" style="color: #2563EB; text-decoration: none;"><?php echo $email; ?></a>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="color: #64748B; font-weight: 600; padding: 10px 14px; background: #FAFBFD;">Contact Number:</td>
                                            <td style="color: #0F172A; padding: 10px 14px;"><?php echo $phone; ?></td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #F1F5F9;">
                                            <td style="color: #64748B; font-weight: 600; padding: 10px 14px; background: #FAFBFD;">Customer Account:</td>
                                            <td style="color: #0F172A; padding: 10px 14px;">
                                                <?php echo $user_id ? 'Logged In (User ID #' . $user_id . ')' : 'Guest Seeker'; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748B; font-weight: 600; padding: 10px 14px; background: #FAFBFD;">Submitted At:</td>
                                            <td style="color: #475569; padding: 10px 14px; font-size: 12.5px;"><?php echo $submitted_at; ?> (IP: <?php echo $ip; ?>)</td>
                                        </tr>
                                    </table>

                                    <!-- Customer Message Box -->
                                    <div style="margin-top: 10px; margin-bottom: 20px;">
                                        <div style="font-size: 12.5px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                            Issue Description:
                                        </div>
                                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px 20px; font-size: 14px; line-height: 1.65; color: #1E293B;">
                                            <?php echo $message; ?>
                                        </div>
                                    </div>

                                    <!-- Attachment notice -->
                                    <?php if ($has_file): ?>
                                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center;">
                                        <span style="font-size: 13px; color: #1E40AF; font-weight: 600;">
                                            &#128206; <strong>Attached Picture:</strong> <?php echo $filename; ?> (Included as email attachment)
                                        </span>
                                    </div>
                                    <?php endif; ?>

                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background: #FAFBFD; border-top: 1px solid #F1F5F9; padding: 20px 30px; text-align: center;">
                                    <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748B; font-weight: 600;">
                                        MeraRashifal Technologies &bull; Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, U.P., 201014
                                    </p>
                                    <p style="margin: 0; font-size: 11.5px; color: #94A3B8;">
                                        Support Desk: <a href="mailto:support@merarashifal.com" style="color: #F58220; text-decoration: none;">support@merarashifal.com</a> &bull; Response SLA: under 2 hours
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
