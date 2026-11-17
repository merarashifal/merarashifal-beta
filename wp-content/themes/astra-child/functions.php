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

/* ==============================================================================
   SEO & INDEXING ENGINE: CANONICAL HARDENING & PARAMETER URL CONTROLS
   Fixes GSC Crawl Waste (274,908 URLs) & Canonical Issues (46,069 URLs)
   ============================================================================== */

// 1. Identify if current request has crawl-waste / filter query parameters
function mr_is_filter_or_parameter_request() {
    $filter_params = [
        'religion', 'rashi', 'nakshatra', 'f_year', 'f_month', 'f_lang',
        'sort', 'filter', 'gender', 'order', 'orderby', 'view'
    ];
    foreach ($filter_params as $param) {
        if (!empty($_GET[$param])) {
            return true;
        }
    }
    return false;
}

// 2. Enforce 'noindex, follow' on all filter/parameter queries
add_filter('wpseo_robots', function($robots) {
    if (mr_is_filter_or_parameter_request()) {
        return 'noindex, follow';
    }
    return $robots;
}, 9999);

add_action('wp_head', function() {
    if (mr_is_filter_or_parameter_request()) {
        if (!defined('WPSEO_VERSION')) {
            echo '<meta name="robots" content="noindex, follow" />' . "\n";
        }
    }
}, 1);

// 3. Strict Canonical URL Sanitization (Strip all parameters & ensure self-canonical)
add_filter('wpseo_canonical', 'mr_sanitize_canonical_url', 9999);
add_filter('get_canonical_url', 'mr_sanitize_canonical_url', 9999);

function mr_sanitize_canonical_url($canonical) {
    if (empty($canonical)) {
        return $canonical;
    }
    // Strip query string completely
    $parsed = parse_url($canonical);
    $path = $parsed['path'] ?? '/';
    // Ensure trailing slash for directory style URLs
    if (pathinfo($path, PATHINFO_EXTENSION) === '' && substr($path, -1) !== '/') {
        $path .= '/';
    }
    // Reconstruct clean canonical with HTTPS
    $clean_canonical = 'https://merarashifal.com' . $path;
    return $clean_canonical;
}

// 4. Schema.org Structured Data (Organization, WebSite & Breadcrumbs)
add_action('wp_head', function() {
    if (is_admin()) return;

    $home_url = home_url('/');
    
    // WebSite & Organization Schema on Homepage
    if (is_front_page()) {
        $org_schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $home_url . '#organization',
                    'name' => 'MeraRashifal',
                    'url' => $home_url,
                    'logo' => [
                        '@type' => 'ImageObject',
                        '@id' => $home_url . '#logo',
                        'url' => 'https://merarashifal.com/wp-content/themes/astra-child/assets/merarashifal-logo.png',
                        'caption' => 'MeraRashifal - Authentic Vedic Astrology'
                    ]
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $home_url . '#website',
                    'url' => $home_url,
                    'name' => 'MeraRashifal',
                    'publisher' => ['@id' => $home_url . '#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => $home_url . '?s={search_term_string}',
                        'query-input' => 'required name=search_term_string'
                    ]
                ]
            ]
        ];
        echo '<script type="application/ld+json">' . json_encode($org_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }

    // BreadcrumbList Schema on Baby Names, Horoscope, Panchang
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/baby-names/') !== false || strpos($uri, '/horoscope/') !== false || strpos($uri, '/panchang/') !== false) {
        $clean_path = trim(parse_url($uri, PHP_URL_PATH), '/');
        if (!empty($clean_path)) {
            $parts = array_filter(explode('/', $clean_path));
            $items = [];
            $current_path = $home_url;
            $items[] = [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $home_url
            ];
            $pos = 2;
            foreach ($parts as $part) {
                $current_path .= $part . '/';
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => ucwords(str_replace(['-', '_'], ' ', urldecode($part))),
                    'item' => $current_path
                ];
            }
            $breadcrumb_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $items
            ];
            echo '<script type="application/ld+json">' . json_encode($breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }
}, 20);

// 5. Dynamic Horoscope & Panchang Metadata Freshness (Never show outdated date)
add_filter('wpseo_title', function($title) {
    if (is_admin()) return $title;

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $dt = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $today_en = $dt->format('d F Y');

    if (is_front_page()) {
        return "आज का राशिफल ({$today_en}) | दैनिक राशिफल, आज का पंचांग & कुंडली | MeraRashifal";
    }

    if (strpos($uri, '/panchang') !== false) {
        if (strpos($uri, '/tithi') !== false) {
            return "आज की तिथि ({$today_en}) | Today Tithi & Shubh Muhurat - MeraRashifal";
        } elseif (strpos($uri, '/rahukaal') !== false) {
            return "आज का राहुकाल समय ({$today_en}) | Today Rahu Kaal Timing - MeraRashifal";
        } elseif (strpos($uri, '/nakshatra') !== false) {
            return "आज का नक्षत्र ({$today_en}) | Today Nakshatra & Charan - MeraRashifal";
        } elseif (strpos($uri, '/choghadiya') !== false) {
            return "आज का चौघड़िया ({$today_en}) | Day & Night Choghadiya Muhurat - MeraRashifal";
        } elseif (strpos($uri, '/abhijit') !== false) {
            return "आज का अभिजीत मुहूर्त ({$today_en}) | Abhijit Muhurat Timing - MeraRashifal";
        }
        return "आज का पंचांग ({$today_en}) | Today Panchang, Tithi, Shubh Muhurat - MeraRashifal";
    }

    return $title;
}, 999);
