<?php
// Enqueue parent and child theme styles
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );
function astra_child_enqueue_styles() {
    // Parent style
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );

    // Child style (optional, if you want extra CSS file)
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'), wp_get_theme()->get('Version') );
}

// Eliminate WordPress top admin bar completely on the frontend
add_filter('show_admin_bar', '__return_false');
add_action('get_header', function() {
    remove_action('wp_head', '_admin_bar_bump_cb');
});

// Load Homepage Placement & Content Manager
require_once get_stylesheet_directory() . '/inc/class-mr-homepage-manager.php';

// Ensure Reports Showcase shortcode engine is loaded
if (!class_exists('MR_Reports_Showcase') && file_exists(WP_PLUGIN_DIR . '/mr-subscription-reports/includes/class-mr-reports-showcase.php')) {
    require_once WP_PLUGIN_DIR . '/mr-subscription-reports/includes/class-mr-reports-showcase.php';
    add_action('init', ['MR_Reports_Showcase', 'init']);
}

/* ==============================================================================
   SECURITY HARDENING: REST API USER ENUMERATION & VERSION CONCEALMENT
   ============================================================================== */

// Block REST API user listing for unauthenticated requests
add_filter('rest_endpoints', function($endpoints) {
    if (!is_user_logged_in()) {
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }
        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
});

// Conceal WordPress Version
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// Block author archive page redirection and show 404 or redirect to home
add_action('template_redirect', function() {
    if (is_author()) {
        wp_safe_redirect(home_url(), 301);
        exit;
    }
});

/* ==============================================================================
   PERFORMANCE: DEFER HEAVY NON-CRITICAL SCRIPTS & NATIVE LAZY LOADING
   ============================================================================== */

// Defer non-critical scripts to eliminate Total Blocking Time (TBT)
add_filter('script_loader_tag', function($tag, $handle, $src) {
    if (is_admin()) {
        return $tag;
    }

    $defer_scripts = [
        'google-maps-places',
        'global-city-autocomplete',
        'razorpay-checkout',
        'sbi_scripts',
        'sbi-scripts',
        'niyati-chat-js',
        'flatpickr',
    ];

    if (in_array($handle, $defer_scripts, true)) {
        if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }
    }

    return $tag;
}, 10, 3);

// Dequeue Razorpay on homepage where no direct checkout occurs (saves 212 KB JS)
add_action('wp_enqueue_scripts', function() {
    if (is_front_page()) {
        wp_dequeue_script('razorpay-checkout');
        wp_dequeue_script('mr-razorpay-checkout');
    }
}, 100);

// Optimize Above-The-Fold LCP Image & Smart Lazy-Loading for Below-The-Fold
add_filter('the_content', 'mr_optimize_images_and_lcp', 99);
add_filter('widget_text', 'mr_optimize_images_and_lcp', 99);
add_filter('elementor/frontend/the_content', 'mr_optimize_images_and_lcp', 99);

function mr_optimize_images_and_lcp($content) {
    if (is_admin() || empty($content)) {
        return $content;
    }

    $first_img = true;

    $content = preg_replace_callback('/<img\b([^>]+)>/i', function($matches) use (&$first_img) {
        $attrs = $matches[1];
        $is_lcp = false;

        // Auto-inject missing width and height for common aspect ratios if missing
        if (stripos($attrs, 'width=') === false && stripos($attrs, 'height=') === false) {
            if (preg_match('/src=[\'"][^\'"]*300x300[^\'"]*[\'"]/i', $attrs)) {
                $attrs .= ' width="300" height="300"';
            } elseif (preg_match('/src=[\'"][^\'"]*150x150[^\'"]*[\'"]/i', $attrs)) {
                $attrs .= ' width="150" height="150"';
            }
        }

        // Identify the above-the-fold hero banner
        if ($first_img || stripos($attrs, 'ea-rashifal') !== false || stripos($attrs, 'final banner') !== false || stripos($attrs, 'logo') !== false) {
            $is_lcp = true;
            $first_img = false;
        }

        if ($is_lcp) {
            // LCP Hero Banner must NEVER be lazy loaded - must load eager with high priority
            $attrs = preg_replace('/\bloading=[\'"][^\'"]*[\'"]/i', '', $attrs);
            return '<img fetchpriority="high" loading="eager" decoding="async" ' . trim($attrs) . '>';
        }

        // Below-the-fold images: apply loading="lazy" if not present
        if (stripos($attrs, 'loading=') === false) {
            return '<img loading="lazy" decoding="async" ' . trim($attrs) . '>';
        }

        return '<img ' . trim($attrs) . '>';
    }, $content);

    return $content;
}




