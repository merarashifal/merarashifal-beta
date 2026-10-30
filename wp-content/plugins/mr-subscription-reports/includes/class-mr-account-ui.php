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

        // Strict Barrier: Block non-admin customers from entering WP Admin
        add_action('init', [__CLASS__, 'restrict_admin_area_access']);
        add_action('admin_init', [__CLASS__, 'restrict_admin_area_access']);
        add_filter('login_redirect', [__CLASS__, 'filter_login_redirect'], 99, 3);

        // Block WP Login page completely - redirect to Google OAuth / Account page
        add_filter('login_url', [__CLASS__, 'filter_login_url'], 99, 3);
        add_action('login_init', [__CLASS__, 'prevent_wp_login_display'], 1);
        add_action('wp_logout', [__CLASS__, 'on_user_logout']);

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
        $img_url  = content_url('/uploads/2025/11/loader.webp');
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

        // Google Maps Places API for City Autocomplete if key is configured
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
            }
        }

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
            'logout_url'   => wp_logout_url(home_url()),
            'user_id'      => $user_id,
        ]);
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
            $logout_url = wp_logout_url(home_url());

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
            // Logged out shortcut icon
            $login_url = self::get_login_action_url();
            $account_item = '
            <li id="menu-item-mr-account" class="menu-item mr-nav-account-item mr-nav-logged-out">
                <a href="' . esc_url($login_url) . '" class="menu-link mr-nav-account-link" title="Sign In / Astro Account">
                    <span class="mr-nav-account-label">Sign In</span>
                </a>
            </li>';
        }

        return $items . $account_item;
    }

    /**
     * Get Direct Google OAuth Login Action URL
     * Bypasses WordPress login form completely
     */
    public static function get_login_action_url() {
        $account_url = self::get_account_page_url();
        if (class_exists('NextendSocialLogin') && NextendSocialLogin::isProviderEnabled('google')) {
            return add_query_arg([
                'loginSocial' => 'google',
                'redirect'    => urlencode($account_url)
            ], site_url('wp-login.php', 'login'));
        }
        return $account_url;
    }

    /**
     * Filter wp_login_url() for frontend customer flows
     * Redirects frontend customers to Google OAuth, while preserving standard login for /wp-admin
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
        $target = !empty($redirect) ? $redirect : $account_url;

        if (class_exists('NextendSocialLogin') && NextendSocialLogin::isProviderEnabled('google')) {
            return add_query_arg([
                'loginSocial' => 'google',
                'redirect'    => urlencode($target)
            ], site_url('wp-login.php', 'login'));
        }
        return $account_url;
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

        // 2. Check if current request is inside WP Admin environment
        $is_in_admin = is_admin();
        if (!$is_in_admin && isset($_SERVER['REQUEST_URI'])) {
            $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($uri_path && (strpos($uri_path, '/wp-admin') !== false || strpos($uri_path, '/wp-login.php') !== false)) {
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
     * Filter login redirect: ensure customers always land on /my-account/ and never into wp-admin
     */
    public static function filter_login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (is_a($user, 'WP_User')) {
            if (!user_can($user, 'manage_options') && !user_can($user, 'edit_posts')) {
                return self::get_account_page_url();
            }
        }
        return $redirect_to;
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
     * Prevent WordPress Default Login Page from Displaying for Customers
     * Customers are routed to Google OAuth or Account Page, while administrators can access wp-admin
     */
    public static function prevent_wp_login_display() {
        // 1. Allow Nextend Social Login OAuth callback & handshake
        if (!empty($_REQUEST['loginSocial']) || (isset($_GET['interim_login']) && $_GET['interim_login'] === 'nsl')) {
            return;
        }

        // 2. If user is ALREADY LOGGED IN as a customer/subscriber, never let them see wp-login.php or route into wp-admin
        if (is_user_logged_in()) {
            if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
                wp_safe_redirect(self::get_account_page_url());
                exit;
            }
        }

        // 3. Allow standard WordPress actions (logout, postpass, lostpassword, resetpass, rp, etc.)
        if (isset($_GET['action']) && in_array($_GET['action'], ['logout', 'postpass', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'confirmaction'])) {
            return;
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
        if (class_exists('NextendSocialLogin') && NextendSocialLogin::isProviderEnabled('google')) {
            $google_oauth_url = add_query_arg([
                'loginSocial' => 'google',
                'redirect'    => urlencode($redirect_to)
            ], site_url('wp-login.php', 'login'));

            wp_redirect($google_oauth_url);
            exit;
        }

        // Fallback for customer: redirect to Account page
        wp_safe_redirect(self::get_account_page_url());
        exit;
    }

    /**
     * Handle user logout cleanly: redirect to home without showing login page
     */
    public static function on_user_logout() {
        wp_safe_redirect(home_url('/'));
        exit;
    }


    /**
     * Render the Full Accounts Page via Shortcode
     * [mr_user_account] / [mr_account]
     */
    public static function render_account_page($atts = []) {
        if (!is_user_logged_in()) {
            return self::render_logged_out_view();
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

        // Fetch user transactions
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
        $logout_url = wp_logout_url(home_url());
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
                        <button type="button" class="mr-btn-action-pill mr-btn-settings" id="mr-tab-btn-settings-trigger" title="Account & Astro Preferences">
                            Settings
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
                        <span class="mr-tab-count"><?php echo count($txns); ?></span>
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="settings">
                        <span>Settings</span>
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
                <div class="mr-account-card">
                    <div class="mr-card-header">
                        <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                        <div>
                            <h3 class="mr-card-title">My Transaction Passbook &amp; Payment History</h3>
                            <p class="mr-card-sub">Complete chronological ledger of your purchases, subscription renewals, and payments.</p>
                        </div>
                    </div>

                    <?php if (empty($txns)): ?>
                        <div class="mr-empty-state-box">
                            <div class="mr-empty-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" style="width:40px; height:40px; opacity:0.6;" /></div>
                            <h4>No Transactions in Passbook</h4>
                            <p>You have not made any purchases or token transactions yet.</p>
                            <a href="javascript:void(0);" onclick="window.NiyatiChat && window.NiyatiChat.open ? window.NiyatiChat.open() : null;" class="mr-btn-glow-gold" style="display:inline-block; margin-top:14px; text-decoration:none;">
                                Consult Vedic Astrologer &rarr;
                            </a>
                        </div>
                    <?php else: 
                        $per_page = 10;
                        $total_txns = count($txns);
                        $total_pages = ceil($total_txns / $per_page);
                    ?>
                        <div class="mr-reports-table-responsive">
                            <table class="mr-account-table">
                                <thead>
                                    <tr>
                                        <th>Date &amp; Time</th>
                                        <th>Transaction ID</th>
                                        <th>Service / Item</th>
                                        <th>Amount</th>
                                        <th class="mr-col-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($txns as $idx => $t): 
                                        $status_class = ($t->status === 'success') ? 'mr-status-ready' : (($t->status === 'refunded') ? 'mr-status-refund' : 'mr-status-pending');
                                        $channel_name = ($t->channel === 'subscription') ? 'Subscription Pack' : (($t->channel === 'chat_credit') ? 'AI Astro Chat' : 'Report Order');
                                        $page_num = floor($idx / $per_page) + 1;
                                    ?>
                                        <tr class="mr-txn-row" data-page="<?php echo $page_num; ?>" style="<?php echo $page_num > 1 ? 'display:none;' : ''; ?>">
                                            <td>
                                                <div class="mr-cell-date"><strong><?php echo date('d M Y', strtotime($t->created_at)); ?></strong></div>
                                                <div class="mr-cell-time" style="font-size:11px; color:#94a3b8;"><?php echo date('h:i A', strtotime($t->created_at)); ?></div>
                                            </td>
                                            <td>
                                                <code class="mr-code-badge"><?php echo esc_html($t->internal_txn_id); ?></code>
                                                <?php if (!empty($t->gateway_payment_id)): ?>
                                                    <div class="mr-gateway-meta"><?php echo esc_html($t->gateway_payment_id); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="mr-channel-badge"><?php echo esc_html($channel_name); ?></div>
                                                <div class="mr-item-name"><strong><?php echo esc_html($t->item_name); ?></strong></div>
                                            </td>
                                            <td>
                                                <span class="mr-amount-val">₹<?php echo number_format($t->net_amount, 2); ?></span>
                                            </td>
                                            <td class="mr-col-status">
                                                <span class="mr-status-pill <?php echo esc_attr($status_class); ?>">
                                                    <?php if ($t->status === 'success'): ?>
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                        <span>Success</span>
                                                    <?php elseif ($t->status === 'refunded'): ?>
                                                        <span>Refunded</span>
                                                    <?php else: ?>
                                                        <span><?php echo esc_html(ucfirst($t->status)); ?></span>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="mr-pagination-wrap" id="mr-passbook-pagination" data-total-pages="<?php echo $total_pages; ?>" data-current-page="1">
                                <button type="button" class="mr-page-btn mr-prev-page" disabled>&laquo; Prev</button>
                                <div class="mr-page-numbers">
                                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                        <button type="button" class="mr-page-num <?php echo ($p === 1) ? 'active' : ''; ?>" data-page="<?php echo $p; ?>"><?php echo $p; ?></button>
                                    <?php endfor; ?>
                                </div>
                                <button type="button" class="mr-page-btn mr-next-page">&raquo; Next</button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 4: Settings -->
            <div class="mr-account-tab-pane" id="mr-pane-settings">
                <div class="mr-account-card">
                    <div class="mr-card-header">
                        <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                        <div>
                            <h3 class="mr-card-title">Account Settings &amp; Preferences</h3>
                            <p class="mr-card-sub">Customize your astrological calculation methods, report delivery, and account security.</p>
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
                            <span>Alerts &amp; Notifications</span>
                        </div>

                        <div class="mr-checkbox-group">
                            <label class="mr-toggle-label">
                                <input type="checkbox" name="pref_notify" value="1" <?php checked($pref_notify, '1'); ?> />
                                <span>Receive instant email copies when a new astrological report is generated.</span>
                            </label>
                        </div>

                        <div class="mr-form-actions" style="margin-top:16px;">
                            <button type="submit" class="mr-btn-glow-gold" id="mr-btn-save-settings">
                                Save Settings
                            </button>
                            <span id="mr-settings-feedback" class="mr-save-feedback"></span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 5: Contact Us -->
            <div class="mr-account-tab-pane" id="mr-pane-contact">
                <div class="mr-pane-grid">
                    <div class="mr-pane-col-main">
                        <div class="mr-account-card">
                            <div class="mr-card-header">
                                <div class="mr-card-icon-badge mr-loader-badge"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader" /></div>
                                <div>
                                    <h3 class="mr-card-title">MeraRashifal Astro Support &amp; Guidance</h3>
                                    <p class="mr-card-sub">Need help with your report interpretation, birth time correction, or transactions? We are here for you.</p>
                                </div>
                            </div>

                            <div class="mr-contact-channels-grid">
                                <div class="mr-contact-channel-card">
                                    <div class="mr-channel-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" /></div>
                                    <h4>WhatsApp Support</h4>
                                    <p>Instant chat support for Kundali queries and report assistance.</p>
                                    <a href="https://wa.me/919999999999?text=Hello%20MeraRashifal%20Team%2C%20I%20need%20assistance%20with%20my%20account" target="_blank" class="mr-btn-contact-action mr-whatsapp-btn">
                                        Chat on WhatsApp &rarr;
                                    </a>
                                </div>

                                <div class="mr-contact-channel-card">
                                    <div class="mr-channel-icon"><img src="<?php echo esc_url($loader_url); ?>" alt="" class="mr-spin-loader-sm" /></div>
                                    <h4>Official Email Helpdesk</h4>
                                    <p>Send detailed queries along with your birth data or transaction reference.</p>
                                    <a href="mailto:rashifalmera@gmail.com?subject=Support%20Request%20-%20MeraRashifal%20Account" class="mr-btn-contact-action mr-email-btn">
                                        Email Support &rarr;
                                    </a>
                                </div>
                            </div>

                            <div class="mr-office-hours-banner">
                                <span><strong>Support Working Hours:</strong> Monday – Saturday, 9:00 AM – 8:00 PM IST (GMT +5:30). Usual response time: under 2 hours.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mr-pane-col-side">
                        <div class="mr-account-card">
                            <h4>Quick Links</h4>
                            <ul class="mr-quick-links-list">
                                <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>">About MeraRashifal &rarr;</a></li>
                                <li><a href="<?php echo esc_url(home_url('/kundali-details/?ku_type=kundali-details-report')); ?>">Kundali Predictions &rarr;</a></li>
                                <li><a href="<?php echo esc_url(home_url('/horoscope/')); ?>">Daily Horoscope &rarr;</a></li>
                                <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>">Terms of Service &rarr;</a></li>
                                <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy &rarr;</a></li>
                            </ul>
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
        $login_url   = self::get_login_action_url();
        $loader_url  = content_url('/uploads/2025/11/loader.webp');
        $img_base    = defined('MR_PLUGIN_URL') ? MR_PLUGIN_URL . 'assets/images/reports/' : content_url('/plugins/mr-subscription-reports/assets/images/reports/');
        $tnc_url     = home_url('/terms-and-conditions/');
        $privacy_url = home_url('/privacy-policy/');

        // Quick feature offerings for the right hero side showcase
        $hero_quick_offerings = [
            [
                'title'     => 'Yearly Varshaphal (2026)',
                'badge'     => '26 Pages • Best Seller',
                'badge_cls' => 'mr-tag-orange',
                'desc'      => '12-month career, finance, health & remedies timeline.',
                'price'     => '₹299',
                'url'       => home_url('/yearly-horoscope-report/'),
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'
            ],
            [
                'title'     => 'Master Janma Kundali',
                'badge'     => '26 Pages • Top Rated',
                'badge_cls' => 'mr-tag-gold',
                'desc'      => 'Full birth chart, 16 divisional harmonics & Shadbala.',
                'price'     => '₹99',
                'url'       => home_url('/janam-kundali-master-report/'),
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#D9A441" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="3" x2="21" y2="21"/><line x1="21" y1="3" x2="3" y2="21"/><polygon points="12,3 21,12 12,21 3,12"/></svg>'
            ],
            [
                'title'     => 'Ask Niyati AI (24/7)',
                'badge'     => 'Live Astrologer',
                'badge_cls' => 'mr-tag-maroon',
                'desc'      => 'Real-time Vedic AI conversation for instant guidance.',
                'price'     => 'Live Chat',
                'url'       => '#niyati-chat',
                'is_chat'   => true,
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#F58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/></svg>'
            ],
            [
                'title'     => 'Vedic Dasha Life Timing',
                'badge'     => '26 Pages • Timing',
                'badge_cls' => 'mr-tag-purple',
                'desc'      => 'Vimshottari Dasha cycles and event timing predictions.',
                'price'     => '₹99',
                'url'       => home_url('/dasha-timing-report/'),
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#7B61FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
            ],
            [
                'title'     => 'Kundali Dosh & Remedial',
                'badge'     => '20 Pages • Remedies',
                'badge_cls' => 'mr-tag-pink',
                'desc'      => 'Manglik, Kaal Sarp, Sade Sati & authentic upayas.',
                'price'     => '₹99',
                'url'       => home_url('/kundali-dosh-report/'),
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#E66BA5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'
            ],
            [
                'title'     => 'Daily Rashifal & Panchang',
                'badge'     => 'Free Daily Forecast',
                'badge_cls' => 'mr-tag-green',
                'desc'      => 'Planetary Moon transits, Tithi & Shubh Muhurat.',
                'price'     => '100% Free',
                'url'       => home_url('/horoscope/'),
                'icon_svg'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#2CB67D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
            ],
        ];

        // Curated master reports for detailed catalog showcase below
        $showcase_reports = [
            [
                'id'         => 'yearly_horoscope_long',
                'title'      => 'Yearly Horoscope Detailed Report (2026-2027)',
                'subtitle'   => 'Comprehensive 26-page annual Vedic Varshaphal forecast covering career, wealth, health, and family.',
                'pages'      => '26 Pages',
                'badge'      => 'Best Seller',
                'badge_cls'  => 'mr-badge-bestseller',
                'image'      => $img_base . 'yearly_horoscope_long.png',
                'price'      => '₹299',
                'orig_price' => '₹1,499',
                'discount'   => 'Save 80%',
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
                'subtitle'   => 'Deep dive into active Vimshottari Mahadasha cycles, Antardasha transitions, and milestone timing.',
                'pages'      => '26 Pages',
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
                'subtitle'   => 'Comprehensive audit of Manglik, Kaal Sarp, Pitra, and Shani Sade Sati doshas with authenticated Vedic remedies.',
                'pages'      => '20 Pages',
                'badge'      => 'Remedial',
                'badge_cls'  => 'mr-badge-remedial',
                'image'      => $img_base . 'dosh_remedial.png',
                'price'      => '₹99',
                'orig_price' => '₹799',
                'discount'   => 'Save 88%',
                'url'        => home_url('/kundali-dosh-report/'),
                'highlights' => ['Manglik & Kaal Sarp Audit', 'Pitra Dosh & Sade Sati Timeline', 'Root Cause Planetary Analysis', 'Vedic Mantras, Yantras & Upayas']
            ],
            [
                'id'         => 'kundli_matching',
                'title'      => 'Master Kundali Matching & 36 Gun Milan Report',
                'subtitle'   => 'Comprehensive 24-page Ashtakoot Gun Milan with Manglik compatibility balance and marital longevity analysis.',
                'pages'      => '24 Pages',
                'badge'      => 'Marriage Milan',
                'badge_cls'  => 'mr-badge-milan',
                'image'      => $img_base . 'kundli_matching.png',
                'price'      => '₹99',
                'orig_price' => '₹1,499',
                'discount'   => 'Save 93%',
                'url'        => home_url('/kundali-and-gun-matching-report/'),
                'highlights' => ['36 Guna Ashtakoot Score', 'Manglik Compatibility Check', 'Bhakoot & Nadi Dosh Analysis', 'Relationship Longevity & Harmony']
            ],
            [
                'id'         => 'numerology',
                'title'      => 'Master Numerology & Vedic Integration Report',
                'subtitle'   => '22-page Sankhya Shastra audit with 3x3 Lo Shu energy grid, Destiny numbers, and 90-day actionable roadmap.',
                'pages'      => '22 Pages',
                'badge'      => 'Sankhya Shastra',
                'badge_cls'  => 'mr-badge-numero',
                'image'      => $img_base . 'numerology.png',
                'price'      => '₹99',
                'orig_price' => '₹599',
                'discount'   => 'Save 83%',
                'url'        => home_url('/numerology-report/'),
                'highlights' => ['Lo Shu Grid Energy Matrix', 'Driver & Conductor Numbers', 'Lucky Dates, Colors & Gemstones', 'Personalized 90-Day Trajectory']
            ],
        ];

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
     * AJAX handler: Save Settings & Preferences
     */
    public static function ajax_save_settings() {
        check_ajax_referer('mr_account_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'Please log in to update preferences.']);
        }

        $pref_chart  = sanitize_text_field($_POST['pref_chart'] ?? 'north_indian');
        $pref_system = sanitize_text_field($_POST['pref_system'] ?? 'vedic');
        $pref_notify = isset($_POST['pref_notify']) ? '1' : '0';

        update_user_meta($user_id, 'mr_pref_chart', $pref_chart);
        update_user_meta($user_id, 'mr_pref_system', $pref_system);
        update_user_meta($user_id, 'mr_pref_notify', $pref_notify);

        wp_send_json_success([
            'message' => 'Preferences updated successfully!'
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
}
