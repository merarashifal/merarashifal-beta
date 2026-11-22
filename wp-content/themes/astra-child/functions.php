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

// Load MeraRashifal Baby Name Astrological & Numerology Engine
require_once get_stylesheet_directory() . '/inc/class-mr-baby-astro-engine.php';
MR_Baby_Astro_Engine::instance();

// Load MeraRashifal Baby Name Gemini AI Prompt Service & Auto-Seeding Pipeline
require_once get_stylesheet_directory() . '/inc/class-mr-baby-gemini-service.php';

// Load MeraRashifal Baby Name 11-Section Template Renderer
require_once get_stylesheet_directory() . '/inc/class-mr-baby-template-renderer.php';
MR_Baby_Template_Renderer::init();

// Load MeraRashifal Baby Name Clean Religion Hubs, SEO & Sitemaps
require_once get_stylesheet_directory() . '/inc/class-mr-baby-seo-rewrites.php';
MR_Baby_SEO_Rewrites::instance();

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
   PERFORMANCE: PRECONNECT, DEFER SCRIPTS, DEQUEUE UNUSED ASSETS & ADSENSE LOADER
   ============================================================================== */

// 1. Preconnect to Font & CDN Domains in <head>
add_action('wp_head', function() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <?php
}, 1);

// 2. Remove unnecessary jquery-migrate on frontend (saves 11 KB, zero console impact)
add_action('wp_default_scripts', function($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
    }
});

// 3. Defer all non-critical Elementor, Astra & Plugin scripts to eliminate TBT
add_filter('script_loader_tag', function($tag, $handle, $src) {
    if (is_admin()) return $tag;

    // Never defer jQuery core, inline configs, or navigation menu controller (smartmenus)
    if ($handle === 'jquery' || $handle === 'jquery-core' || strpos($handle, 'smartmenus') !== false || strpos($src, 'smartmenus') !== false) {
        return $tag;
    }

    $defer_handles = [
        'elementor-frontend',
        'elementor-pro-frontend',
        'elementor-pro-elements-handlers',
        'elementor-pro-webpack-pro-runtime',
        'elementor-webpack-runtime',
        'wp-bottom-menu',
        'astra-theme-js',
        'mr-reports',
        'mr-account',
        'mr-cookie-consent',
        'imagesloaded',
        'flatpickr',
        'wp-i18n',
        'wp-hooks',
        'google-maps-places',
        'global-city-autocomplete',
        'razorpay-checkout',
        'sbi_scripts',
        'sbi-scripts',
        'niyati-chat-js'
    ];

    if (in_array($handle, $defer_handles, true) || strpos($src, 'elementor') !== false) {
        if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }
    }

    return $tag;
}, 20, 3);

// 4. Dequeue Unused Styles & Duplicate Font Awesome on Front Page (Reduces CSS from 45 to ~25)
add_action('wp_enqueue_scripts', function() {
    if (is_front_page()) {
        // Dequeue Instagram Feed styles (not visible above the fold on homepage)
        wp_dequeue_style('sbi_styles');
        wp_dequeue_style('sbi-tokens-local');
        wp_dequeue_style('sb-elementor-shared-style');
        // Dequeue Flatpickr CSS (only needed on Kundli/calculator pages)
        wp_dequeue_style('flatpickr');
        // Dequeue duplicate FontAwesome from WP Bottom Menu (uses main FontAwesome)
        wp_dequeue_style('font-awesome-wpbm');
        // Dequeue unused Elementor Pro blockquote widget CSS
        wp_dequeue_style('hfe-widget-blockquote');
        // Dequeue Razorpay on homepage where no direct checkout occurs (saves 212 KB JS)
        wp_dequeue_script('razorpay-checkout');
        wp_dequeue_script('mr-razorpay-checkout');
    }
}, 9999);

// 5. Delay Google AdSense & Third-Party Trackers Until User Interaction
// Eliminates 1,500ms+ of Total Blocking Time (TBT) on Mobile
add_action('wp_footer', function() {
    if (is_admin()) return;
    ?>
    <script>
    (function() {
        var adsLoaded = false;
        function loadAdSense() {
            if (adsLoaded) return;
            adsLoaded = true;
            window.removeEventListener('scroll', loadAdSense, {passive:true});
            window.removeEventListener('touchstart', loadAdSense, {passive:true});
            window.removeEventListener('mousemove', loadAdSense, {passive:true});
            window.removeEventListener('keydown', loadAdSense, {passive:true});
            var s = document.createElement('script');
            s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2729656633217680';
            s.async = true;
            s.crossOrigin = 'anonymous';
            document.head.appendChild(s);
        }
        window.addEventListener('scroll', loadAdSense, {passive:true, once:true});
        window.addEventListener('touchstart', loadAdSense, {passive:true, once:true});
        window.addEventListener('mousemove', loadAdSense, {passive:true, once:true});
        window.addEventListener('keydown', loadAdSense, {passive:true, once:true});
        setTimeout(loadAdSense, 3800);
    })();
    </script>
    <?php
}, 999);

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

        // 1. Rewrite heavy full-sized Zodiac cards (~100KB each) to optimized 150x150 thumbnails (~8KB each)
        if (preg_match('/src=([\'"])([^\'"]*\/([a-zA-Z]+-?[cC]ropped))\.webp\1/i', $attrs, $zm)) {
            if (stripos($zm[2], '-150x150') === false && stripos($zm[2], '-300x300') === false) {
                $attrs = str_replace($zm[0], 'src=' . $zm[1] . $zm[2] . '-150x150.webp' . $zm[1], $attrs);
                if (stripos($attrs, 'width=') === false) {
                    $attrs .= ' width="150" height="150"';
                }
            }
        }

        // 2. Rewrite heavy full-sized Muhurat cards (~100KB each) to 150x150 thumbnails (~18KB each)
        if (preg_match('/src=([\'"])([^\'"]*\/([a-zA-Z\-]+-Muhurat))\.webp\1/i', $attrs, $mm)) {
            if (stripos($mm[2], '-150x150') === false && stripos($mm[2], '-300x300') === false) {
                $attrs = str_replace($mm[0], 'src=' . $mm[1] . $mm[2] . '-150x150.webp' . $mm[1], $attrs);
                if (stripos($attrs, 'width=') === false) {
                    $attrs .= ' width="150" height="150"';
                }
            }
        }

        // 3. Rewrite header logo PNG (189KB) to WebP (51KB)
        if (stripos($attrs, 'logo_for_webstie_2_-_Cropped-removebg-preview-1.png') !== false) {
            $attrs = str_replace('logo_for_webstie_2_-_Cropped-removebg-preview-1.png', 'logo_for_webstie_2_-_Cropped-removebg-preview-1.webp', $attrs);
        }

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
   ACCESSIBILITY ENHANCEMENTS (WCAG AA & LIGHTHOUSE 95+)
   ============================================================================== */

add_action('wp_footer', function() {
    if (is_admin()) return;
    ?>
    <script>
    (function(){
        function enhanceA11y(){
            // 1. Ensure primary content has main landmark
            var primary = document.getElementById('primary');
            if (primary && !primary.getAttribute('role') && !document.querySelector('main')) {
                primary.setAttribute('role', 'main');
            }
            // 2. Form Inputs aria-labels
            var dob = document.getElementById('input1') || document.querySelector('.input-dob');
            if (dob && !dob.getAttribute('aria-label')) dob.setAttribute('aria-label', 'Date of Birth');

            var tob = document.getElementById('input4') || document.querySelector('.input-tob');
            if (tob && !tob.getAttribute('aria-label')) tob.setAttribute('aria-label', 'Time of Birth');

            var city = document.querySelector('input.js-city-autocomplete, input[data-places="city"]');
            if (city && !city.getAttribute('aria-label')) city.setAttribute('aria-label', 'Birth City or Location');

            // 3. Select fields
            var selects = {
                'alphabet': 'Select Starting Alphabet',
                'gender': 'Select Gender',
                'religion': 'Select Religion',
                'rashi': 'Select Rashi or Zodiac Sign'
            };
            Object.keys(selects).forEach(function(name){
                var el = document.querySelector('select[name="' + name + '"]');
                if (el && !el.getAttribute('aria-label')) {
                    el.setAttribute('aria-label', selects[name]);
                }
            });

            // 4. Image links without discernible text
            document.querySelectorAll('a img').forEach(function(img){
                var a = img.closest('a');
                if (a && !a.getAttribute('aria-label') && !a.innerText.trim()) {
                    var alt = img.getAttribute('alt') || img.getAttribute('title') || 'Astrology Report or Service';
                    a.setAttribute('aria-label', alt);
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enhanceA11y);
        } else {
            enhanceA11y();
        }
    })();
    </script>
    <style>
    /* WCAG AA Color Contrast Adjustments for Gold/Amber Text on Light Backgrounds */
    .text-amber-500, .text-yellow-500, [class*="badge-gold"], .mr-rsc-badge-discount {
        color: #92400e !important;
    }
    </style>
    <?php
}, 99);

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

/* ==============================================================================
   SOCIAL MEDIA OPEN GRAPH & TWITTER CARD PREVIEW ENGINE
   Guarantees crisp, rich link previews on WhatsApp, Facebook, LinkedIn, X, Telegram
   ============================================================================== */

function mr_get_contextual_og_image() {
    $theme_og_base = 'https://merarashifal.com/wp-content/themes/astra-child/assets/og-images/';
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    // 1. Homepage
    if (is_front_page()) {
        return $theme_og_base . 'og-default-main.jpg';
    }

    // 2. Horoscope / Sun Sign pages (/horoscope/, /aaj-ka-rashifal/, /zodiac/)
    if (strpos($uri, '/horoscope') !== false || strpos($uri, '/rashifal') !== false) {
        return $theme_og_base . 'og-horoscope-main.jpg';
    }

    // 3. Panchang & Muhurat pages (/panchang/, /choghadiya/, /tithi/, /rahukaal/, /muhurat/)
    if (strpos($uri, '/panchang') !== false || strpos($uri, '/choghadiya') !== false || strpos($uri, '/muhurat') !== false || strpos($uri, '/tithi') !== false || strpos($uri, '/rahukaal') !== false) {
        return $theme_og_base . 'og-panchang-main.jpg';
    }

    // 4. Kundali, Gun Milan, Dasha & Dosh pages (/kundli/, /dasha/, /dosh/, /matching/)
    if (strpos($uri, '/kundli') !== false || strpos($uri, '/dasha') !== false || strpos($uri, '/dosh') !== false || strpos($uri, '/matching') !== false) {
        return $theme_og_base . 'og-kundli-dasha.jpg';
    }

    // 5. Baby Names (/baby-names/)
    if (strpos($uri, '/baby-names') !== false) {
        return $theme_og_base . 'og-baby-names.jpg';
    }

    // 6. Reports Store / Pricing (/reports/, /pricing/, /horoscope-report/)
    if (strpos($uri, '/reports') !== false || strpos($uri, '/pricing') !== false || strpos($uri, '/horoscope-report') !== false) {
        return $theme_og_base . 'og-reports-store.jpg';
    }

    // 7. Single Post: Use featured image ONLY if it is a valid JPEG/PNG
    if (is_singular('post')) {
        $thumb_id = get_post_thumbnail_id();
        if ($thumb_id) {
            $img_url = wp_get_attachment_url($thumb_id);
            if ($img_url && preg_match('/\.(jpe?g|png)$/i', $img_url)) {
                return $img_url;
            }
        }
    }

    // 8. Global Fallback
    return $theme_og_base . 'og-default-main.jpg';
}

// Override Yoast Open Graph Image with contextual 1.91:1 JPEG
add_filter('wpseo_opengraph_image', 'mr_get_contextual_og_image', 9999);
add_filter('wpseo_twitter_image', 'mr_get_contextual_og_image', 9999);

// Enforce image/jpeg mime type so WhatsApp/FB never read image/webp
add_filter('wpseo_opengraph_image_type', function() {
    return 'image/jpeg';
}, 9999);

// Enforce correct dimensions for 1024x536 card
add_filter('wpseo_opengraph_image_width', function() {
    return 1024;
}, 9999);

add_filter('wpseo_opengraph_image_height', function() {
    return 536;
}, 9999);

// Set social locale to Hindi/India for high priority on Indian WhatsApp/FB clients
add_filter('wpseo_locale', function() {
    return 'hi_IN';
}, 9999);

// Ensure og:image:secure_url is present for HTTPS compatibility
add_action('wpseo_opengraph', function() {
    $og_img = mr_get_contextual_og_image();
    echo '<meta property="og:image:secure_url" content="' . esc_url($og_img) . '" />' . "\n";
}, 30);

/**
 * Safari & Mobile WebKit Navigation Helper:
 * Ensures dropdown menus open properly on touch/iPad/iPhone without prematurely triggering link navigation.
 */
add_action('wp_footer', function() {
    if (is_admin()) return;
    ?>
    <script id="mrf-safari-nav-helper">
    (function() {
        var isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (window.matchMedia && window.matchMedia('(pointer: coarse)').matches);
        if (!isTouchDevice) return;

        document.addEventListener('DOMContentLoaded', function() {
            var parentItems = document.querySelectorAll('.elementor-nav-menu--main li.menu-item-has-children > a');
            
            parentItems.forEach(function(anchor) {
                var parentLi = anchor.parentElement;
                
                anchor.addEventListener('click', function(e) {
                    // Check if sub-menu is already visible/open
                    var isExpanded = parentLi.classList.contains('mrf-touch-open') || 
                                     anchor.getAttribute('aria-expanded') === 'true' ||
                                     parentLi.classList.contains('highlighted');

                    if (!isExpanded) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Close sibling open menus
                        var siblings = parentLi.parentElement.querySelectorAll('li.mrf-touch-open');
                        siblings.forEach(function(sib) {
                            if (sib !== parentLi) {
                                sib.classList.remove('mrf-touch-open');
                                var sibSub = sib.querySelector('ul.sub-menu');
                                if (sibSub) sibSub.style.display = '';
                            }
                        });

                        parentLi.classList.add('mrf-touch-open');
                        anchor.setAttribute('aria-expanded', 'true');
                        
                        var subMenu = parentLi.querySelector('ul.sub-menu');
                        if (subMenu) {
                            subMenu.style.display = 'block';
                            subMenu.style.visibility = 'visible';
                            subMenu.style.opacity = '1';
                        }
                    }
                });
            });

            // Tap anywhere outside to close dropdowns
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.menu-item-has-children')) {
                    document.querySelectorAll('li.mrf-touch-open').forEach(function(li) {
                        li.classList.remove('mrf-touch-open');
                        var sub = li.querySelector('ul.sub-menu');
                        if (sub && !li.classList.contains('highlighted')) {
                            sub.style.display = '';
                            sub.style.visibility = '';
                            sub.style.opacity = '';
                        }
                        var a = li.querySelector('a');
                        if (a) a.setAttribute('aria-expanded', 'false');
                    });
                }
            });

            // Universal Date & Time Picker Tap Trigger for Safari & WebKit
            // Allows tapping ANY part of the date/time input to open the native picker immediately
            document.addEventListener('click', function(e) {
                var target = e.target;
                if (target && target.matches && target.matches('input[type="date"], input[type="time"], input[type="datetime-local"]')) {
                    if (typeof target.showPicker === 'function') {
                        try {
                            target.showPicker();
                        } catch (err) {}
                    }
                }
            });

            // Set sensible birthdate limits if missing on DOB fields
            var dobInputs = document.querySelectorAll('input[type="date"][name*="dob"], input[type="date"][id*="dob"]');
            var todayStr = new Date().toISOString().split('T')[0];
            dobInputs.forEach(function(inp) {
                if (!inp.getAttribute('max')) {
                    inp.setAttribute('max', todayStr);
                }
                if (!inp.getAttribute('min')) {
                    inp.setAttribute('min', '1920-01-01');
                }
            });
        });
    })();
    </script>
    <?php
}, 998);

