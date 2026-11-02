<?php
/**
 * High-Converting Immersive Report Landing Page Engine
 * MeraRashifal Brand Design System Edition (Dynamic Engine for all 9 Reports)
 *
 * Palette:
 * - Sacred Orange: #F58220 (CTA buttons, icons, highlights)
 * - Deep Maroon:   #7A1F3D (Headings, premium sections, borders)
 * - Gold:          #D9A441 (Badges, zodiac symbols, ratings, borders)
 * - Cream:         #FDF8F1 (Primary background)
 * - White:         #FFFFFF (Cards, secondary buttons)
 * - Dark Brown:    #3A312D (Body text, subtext)
 * - Soft Green:    #2CB67D (Discounts, positive checks)
 * - Soft Blue:     #4B7BEC (Information tags)
 *
 * Typography:
 * - Headings: Playfair Display / Georgia (Elegant Serif)
 * - Body: Poppins / Inter / Sans-serif (Modern Sans Serif)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Report_Landing {

    public static function init() {
        // Register Shortcodes
        add_shortcode('mr_report_landing', [__CLASS__, 'render_landing_page']);
        add_shortcode('mr_landingpage', [__CLASS__, 'render_landing_page']);

        // Hook SEO Metadata & Schema in <head>
        add_action('wp_head', [__CLASS__, 'inject_seo_head'], 1);

        // Filter Document Title for Standard & Yoast SEO
        add_filter('pre_get_document_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('document_title_parts', [__CLASS__, 'filter_document_title_parts'], 99);
        add_filter('wpseo_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_metadesc', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_canonical', [__CLASS__, 'filter_canonical_url'], 99);
        add_filter('wpseo_robots', [__CLASS__, 'filter_robots'], 99);
        add_filter('wpseo_opengraph_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_opengraph_desc', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_opengraph_url', [__CLASS__, 'filter_canonical_url'], 99);
        add_filter('wpseo_opengraph_image', [__CLASS__, 'filter_og_image'], 99);
        add_filter('wpseo_opengraph_site_name', [__CLASS__, 'filter_site_name'], 99);
        add_filter('wpseo_twitter_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_twitter_description', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_twitter_image', [__CLASS__, 'filter_og_image'], 99);
        add_filter('wpseo_json_ld_output', [__CLASS__, 'filter_yoast_json_ld'], 99);

        // 301 Redirect old top-level URLs to /reports/[slug]/
        add_action('template_redirect', [__CLASS__, 'handle_backward_compatibility_redirects'], 1);
    }

    /**
     * 301 Redirect old top-level report URLs (/palm-reading-report/, etc.) to /reports/[slug]/
     */
    public static function handle_backward_compatibility_redirects() {
        if (is_admin()) {
            return;
        }

        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        $uri_parts = explode('?', $uri);
        $path = trim($uri_parts[0], '/');

        // Strip subfolder if WordPress is installed in a subdirectory (e.g. /merarashifal/)
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = trim(substr($path, strlen($site_path)), '/');
        }

        $slug_map = MR_Report_Landing_Data::get_slug_map();
        if (isset($slug_map[$path])) {
            $target_slug = ($path === 'landingpage') ? 'yearly-horoscope-report' : $path;
            $target_url = home_url('/reports/' . $target_slug . '/');
            if (!empty($_SERVER['QUERY_STRING'])) {
                $target_url .= '?' . $_SERVER['QUERY_STRING'];
            }
            wp_redirect($target_url, 301);
            exit;
        }
    }

    /**
     * Detect current report key based on active post slug, query parameter, or shortcode atts
     */
    public static function get_current_report_key($atts = []) {
        if (!empty($atts['slug']) && $atts['slug'] !== 'auto') {
            $map = MR_Report_Landing_Data::get_slug_map();
            return $map[$atts['slug']] ?? $atts['slug'];
        }

        global $post;
        if ($post && is_a($post, 'WP_Post')) {
            $map = MR_Report_Landing_Data::get_slug_map();
            $post_name = $post->post_name;
            if (isset($map[$post_name])) {
                return $map[$post_name];
            }

            // Check if post content has shortcode with slug
            if (has_shortcode($post->post_content, 'mr_report_landing')) {
                if (preg_match('/\[mr_report_landing[^\]]*slug=["\']([^"\']+)["\']/i', $post->post_content, $matches)) {
                    $slug = $matches[1];
                    return $map[$slug] ?? $slug;
                }
            }
        }

        if (isset($_GET['report'])) {
            $rep = sanitize_key($_GET['report']);
            $map = MR_Report_Landing_Data::get_slug_map();
            return $map[$rep] ?? $rep;
        }

        return 'yearly_horoscope_long';
    }

    /**
     * Check if currently viewing any of the 9 report landing pages
     */
    public static function is_report_landing_page() {
        if (is_admin()) {
            return false;
        }
        global $post;
        if (!$post || !is_a($post, 'WP_Post')) {
            return false;
        }
        $map = MR_Report_Landing_Data::get_slug_map();
        if (isset($map[$post->post_name])) {
            return true;
        }
        if (has_shortcode($post->post_content, 'mr_report_landing') || has_shortcode($post->post_content, 'mr_landingpage')) {
            return true;
        }
        return false;
    }

    /**
     * Filter document title
     */
    public static function filter_document_title($title) {
        if (self::is_report_landing_page()) {
            $key = self::get_current_report_key();
            $data = MR_Report_Landing_Data::get($key);
            if (!empty($data['meta_title'])) {
                return $data['meta_title'];
            }
        }
        return $title;
    }

    /**
     * Filter document title parts array
     */
    public static function filter_document_title_parts($parts) {
        if (self::is_report_landing_page()) {
            $key = self::get_current_report_key();
            $data = MR_Report_Landing_Data::get($key);
            if (!empty($data['meta_title'])) {
                $parts['title'] = $data['meta_title'];
            }
        }
        return $parts;
    }

    /**
     * Filter meta description for Yoast SEO & standard feeds
     */
    public static function filter_meta_description($desc) {
        if (self::is_report_landing_page()) {
            $key = self::get_current_report_key();
            $data = MR_Report_Landing_Data::get($key);
            if (!empty($data['meta_description'])) {
                return $data['meta_description'];
            }
        }
        return $desc;
    }

    /**
     * Filter canonical URL for Yoast SEO
     */
    public static function filter_canonical_url($url) {
        if (self::is_report_landing_page()) {
            $key = self::get_current_report_key();
            $data = MR_Report_Landing_Data::get($key);
            return home_url('/reports/' . $data['page_slug'] . '/');
        }
        return $url;
    }

    /**
     * Filter robots meta tag for Yoast SEO
     */
    public static function filter_robots($robots) {
        if (self::is_report_landing_page()) {
            return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        }
        return $robots;
    }

    /**
     * Filter OpenGraph / Twitter Image URL for Yoast SEO
     */
    public static function filter_og_image($img) {
        if (self::is_report_landing_page()) {
            $key = self::get_current_report_key();
            $plugin_dir = defined('MR_REPORTS_DIR') ? MR_REPORTS_DIR : (WP_PLUGIN_DIR . '/mr-subscription-reports/');
            $report_img_rel = 'assets/images/reports/' . $key . '.png';
            if (file_exists($plugin_dir . $report_img_rel)) {
                return home_url('/wp-content/plugins/mr-subscription-reports/' . $report_img_rel);
            }
        }
        return $img;
    }

    /**
     * Filter site name for OpenGraph
     */
    public static function filter_site_name($name) {
        if (self::is_report_landing_page()) {
            return 'MeraRashifal';
        }
        return $name;
    }

    /**
     * Suppress Yoast generic Schema on Report landing pages to avoid duplicate schemas
     */
    public static function filter_yoast_json_ld($data) {
        if (self::is_report_landing_page()) {
            return false;
        }
        return $data;
    }

    /**
     * Inject complete 23-Point SEO Meta Tags, OpenGraph, Twitter Cards & Schema.org JSON-LD
     */
    public static function inject_seo_head() {
        if (!self::is_report_landing_page()) {
            return;
        }

        $key = self::get_current_report_key();
        $data = MR_Report_Landing_Data::get($key);
        $canonical_url = home_url('/reports/' . $data['page_slug'] . '/');
        $site_name = get_bloginfo('name') ?: 'MeraRashifal';
        $logo_url = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/logo.png');
        if (!file_exists(ABSPATH . 'wp-content/plugins/mr-subscription-reports/assets/images/logo.png')) {
            $logo_url = 'https://merarashifal.com/wp-content/uploads/2023/11/cropped-mera-rashifal-logo-1.png';
        }

        // Dynamic Report 3D Cover Artwork for Rich Social Sharing (WhatsApp, FB, Twitter, LinkedIn)
        $plugin_dir = defined('MR_REPORTS_DIR') ? MR_REPORTS_DIR : (WP_PLUGIN_DIR . '/mr-subscription-reports/');
        $report_img_rel = 'assets/images/reports/' . $key . '.png';
        if (file_exists($plugin_dir . $report_img_rel)) {
            $report_img_url = home_url('/wp-content/plugins/mr-subscription-reports/' . $report_img_rel);
        } else {
            $report_img_url = $logo_url;
        }

        // Build Related Reports ItemList
        $related_keys = $data['related_reports'] ?? [];
        $related_items = [];
        $rel_pos = 0;
        foreach ($related_keys as $rel_key) {
            $rel_data = MR_Report_Landing_Data::get($rel_key);
            if (!empty($rel_data['name'])) {
                $rel_pos++;
                $rel_url = home_url('/reports/' . $rel_data['page_slug'] . '/');
                $related_items[] = [
                    '@type'    => 'ListItem',
                    'position' => $rel_pos,
                    'name'     => $rel_data['name'],
                    'url'      => $rel_url,
                ];
            }
        }

        // Build Comprehensive Schema.org Graph (Organization, WebSite, WebPage, BreadcrumbList, Product, Niyati Tokens, ItemList, FAQPage)
        $graph = [
            // 1. Organization Schema
            [
                '@type'       => 'Organization',
                '@id'         => home_url('/#organization'),
                'name'        => 'MeraRashifal',
                'url'         => home_url('/'),
                'logo'        => [
                    '@type'      => 'ImageObject',
                    '@id'        => home_url('/#logo'),
                    'inLanguage' => 'en-US',
                    'url'        => $logo_url,
                    'contentUrl' => $logo_url,
                    'width'      => 512,
                    'height'     => 512,
                    'caption'    => 'MeraRashifal Sacred Vedic Astrology & Samudrik Publications',
                ],
                'image'       => [
                    '@id' => home_url('/#logo'),
                ],
                'sameAs'      => [
                    'https://twitter.com/merarashifal',
                    'https://www.facebook.com/merarashifal',
                    'https://www.instagram.com/merarashifal',
                    'https://www.youtube.com/@merarashifal',
                ],
            ],
            // 2. WebSite Schema
            [
                '@type'       => 'WebSite',
                '@id'         => home_url('/#website'),
                'url'         => home_url('/'),
                'name'        => 'MeraRashifal',
                'description' => 'Authentic Vedic Astrology, Janma Kundali & Horoscope Publications',
                'publisher'   => [
                    '@id' => home_url('/#organization'),
                ],
                'inLanguage'  => 'en-US',
            ],
            // 3. WebPage (ItemPage) Schema
            [
                '@type'       => 'ItemPage',
                '@id'         => $canonical_url . '#webpage',
                'url'         => $canonical_url,
                'name'        => $data['meta_title'],
                'description' => $data['meta_description'],
                'isPartOf'    => [
                    '@id' => home_url('/#website'),
                ],
                'breadcrumb'  => [
                    '@id' => $canonical_url . '#breadcrumbs',
                ],
                'mainEntity'  => [
                    '@id' => $canonical_url . '#product',
                ],
                'inLanguage'  => 'en-US',
            ],
            // 4. BreadcrumbList Schema
            [
                '@type'           => 'BreadcrumbList',
                '@id'             => $canonical_url . '#breadcrumbs',
                'itemListElement' => [
                    [
                        '@type'    => 'ListItem',
                        'position' => 1,
                        'name'     => 'Home',
                        'item'     => home_url('/'),
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 2,
                        'name'     => 'Astrological Reports',
                        'item'     => home_url('/reports/'),
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 3,
                        'name'     => $data['name'],
                        'item'     => $canonical_url,
                    ],
                ],
            ],
            // 5. Product (Primary Vedic Report) Schema
            [
                '@type'           => 'Product',
                '@id'             => $canonical_url . '#product',
                'name'            => $data['name'],
                'description'     => $data['meta_description'],
                'image'           => [
                    $report_img_url,
                ],
                'sku'             => $data['code'] ?? ('MR_' . strtoupper(substr(md5($key), 0, 6))),
                'mpn'             => $data['code'] ?? ('MR_' . strtoupper(substr(md5($key), 0, 6))),
                'brand'           => [
                    '@type' => 'Brand',
                    'name'  => 'MeraRashifal',
                ],
                'offers'          => [
                    '@type'           => 'Offer',
                    'url'             => $canonical_url,
                    'priceCurrency'   => 'INR',
                    'price'           => (string)$data['price_sale'],
                    'priceValidUntil' => '2027-12-31',
                    'availability'    => 'https://schema.org/InStock',
                    'itemCondition'   => 'https://schema.org/NewCondition',
                    'seller'          => [
                        '@id' => home_url('/#organization'),
                    ],
                ],
                'aggregateRating' => [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => '4.9',
                    'reviewCount' => '1250',
                    'bestRating'  => '5',
                    'worstRating' => '1',
                ],
            ],
            // 6. Product (Niyati AI Consultation Tokens) Schema
            [
                '@type'           => 'Product',
                '@id'             => home_url('/#niyati-tokens'),
                'name'            => 'Niyati AI Vedic Astrological Consultation Tokens',
                'description'     => 'Interactive conversational Vedic AI astrologer tokens for instant birth chart queries, transit interpretations, and personalized remedies.',
                'image'           => [
                    home_url('/wp-content/plugins/mr-subscription-reports/assets/images/niyati-avatar.png'),
                ],
                'brand'           => [
                    '@type' => 'Brand',
                    'name'  => 'MeraRashifal',
                ],
                'offers'          => [
                    '@type'           => 'Offer',
                    'url'             => home_url('/pricing/'),
                    'priceCurrency'   => 'INR',
                    'price'           => '99',
                    'priceValidUntil' => '2027-12-31',
                    'availability'    => 'https://schema.org/InStock',
                    'itemCondition'   => 'https://schema.org/NewCondition',
                    'seller'          => [
                        '@id' => home_url('/#organization'),
                    ],
                ],
                'aggregateRating' => [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => '4.95',
                    'reviewCount' => '3420',
                    'bestRating'  => '5',
                    'worstRating' => '1',
                ],
            ],
            // 7. ItemList (Catalog of Related Astrological Dossiers) Schema
            [
                '@type'           => 'ItemList',
                '@id'             => $canonical_url . '#related-reports',
                'name'            => 'Recommended Vedic Astrological Dossiers',
                'itemListElement' => $related_items,
            ],
        ];

        // 8. FAQPage Schema
        if (!empty($data['faqs']) && is_array($data['faqs'])) {
            $faq_entities = [];
            foreach ($data['faqs'] as $faq) {
                if (!empty($faq['q']) && !empty($faq['a'])) {
                    $faq_entities[] = [
                        '@type'          => 'Question',
                        'name'           => $faq['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => wp_strip_all_tags($faq['a']),
                        ],
                    ];
                }
            }
            if (!empty($faq_entities)) {
                $graph[] = [
                    '@type'      => 'FAQPage',
                    '@id'        => $canonical_url . '#faq',
                    'mainEntity' => $faq_entities,
                ];
            }
        }

        $schema_json = json_encode([
            '@context' => 'https://schema.org/',
            '@graph'   => $graph,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        ?>
        <!-- MeraRashifal SEO Metadata & Social Graph -->
        <meta name="description" content="<?php echo esc_attr($data['meta_description']); ?>">
        <meta name="keywords" content="<?php echo esc_attr($data['meta_keywords']); ?>">
        <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="product">
        <meta property="og:locale" content="en_US">
        <meta property="og:locale:alternate" content="hi_IN">
        <meta property="og:locale:alternate" content="en_IN">
        <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
        <meta property="og:title" content="<?php echo esc_attr($data['meta_title']); ?>">
        <meta property="og:description" content="<?php echo esc_attr($data['meta_description']); ?>">
        <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
        <meta property="og:image" content="<?php echo esc_url($report_img_url); ?>">
        <meta property="og:image:secure_url" content="<?php echo esc_url($report_img_url); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:alt" content="<?php echo esc_attr($data['name']); ?>">
        <meta property="product:price:amount" content="<?php echo esc_attr($data['price_sale']); ?>">
        <meta property="product:price:currency" content="INR">
        <meta property="product:availability" content="in stock">

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@merarashifal">
        <meta name="twitter:creator" content="@merarashifal">
        <meta name="twitter:title" content="<?php echo esc_attr($data['meta_title']); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($data['meta_description']); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($report_img_url); ?>">
        <meta name="twitter:image:alt" content="<?php echo esc_attr($data['name']); ?>">

        <!-- Schema.org JSON-LD (Organization, WebSite, WebPage, BreadcrumbList, Product, Niyati Tokens, ItemList & FAQPage) -->
        <script type="application/ld+json">
<?php echo $schema_json; ?>
        </script>
        <?php
    }

    /**
     * Render the Full-Width Landing Page adhering strictly to MeraRashifal Design System
     */
    public static function render_landing_page($atts = []) {
        $atts = shortcode_atts([
            'slug' => 'auto',
        ], $atts);

        // Ensure frontend assets (CSS, JS, Google Places, FontAwesome) are loaded
        if (class_exists('MR_Frontend')) {
            MR_Frontend::enqueue_assets();
        }

        $report_key = self::get_current_report_key($atts);
        $data = MR_Report_Landing_Data::get($report_key);

        $report_slug          = $data['report_slug'];

        // Live synchronize with admin-managed cards & testimonials from Catalog Manager
        $all_catalog_cards = class_exists('MR_Catalog_Manager') ? MR_Catalog_Manager::get_all_cards() : [];
        if (!empty($all_catalog_cards[$report_slug]['testimonials']) && is_array($all_catalog_cards[$report_slug]['testimonials'])) {
            $data['testimonials'] = $all_catalog_cards[$report_slug]['testimonials'];
        }

        $report_title         = $data['name'];
        $report_pages         = $data['pages'];
        $report_price         = number_format($data['price_sale']);
        $report_regular_price = number_format($data['price_regular']);
        $catalog_url          = home_url('/reports/');
        $user_id              = get_current_user_id();
        $has_token            = ($user_id && class_exists('MR_Tokens')) ? MR_Tokens::has_token($user_id, $report_slug) : false;

        // Dynamic Bundle Configuration
        $bundle_cfg         = class_exists('MR_Bundles') ? MR_Bundles::get_bundle_for_report($report_slug) : null;
        $global_bundle_opts = class_exists('MR_Bundles') ? MR_Bundles::get_global_settings() : ['enabled' => true];
        $bundles_active     = ($bundle_cfg && !empty($bundle_cfg['bundle_enabled']) && !empty($global_bundle_opts['enabled']));

        $tier_single = $bundles_active ? ($bundle_cfg['tier_single'] ?? []) : [];
        $tier_duo    = $bundles_active ? ($bundle_cfg['tier_duo'] ?? []) : [];
        $tier_trio   = $bundles_active ? ($bundle_cfg['tier_trio'] ?? []) : [];

        // Fetch other sister reports for cross-promotion
        $all_reports = MR_Report_Landing_Data::get_all();
        $related_reports = [];
        foreach ($all_reports as $k => $item) {
            if ($k !== $report_slug && count($related_reports) < 4) {
                $related_reports[] = $item;
            }
        }

        ob_start();
        ?>
        <!-- Import MeraRashifal Brand Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            /* Hide Astra Default Page Title Header */
            .page-id-7272 .entry-header,
            header.entry-header,
            .entry-header,
            .ast-single-post .entry-header {
                display: none !important;
            }

            /* Full Viewport Breakout in MeraRashifal Cream Theme */
            .mr-landing-breakout {
                width: 100vw;
                position: relative;
                left: 50%;
                right: 50%;
                margin-left: -50vw;
                margin-right: -50vw;
                background-color: #FDF8F1;
                background-image: 
                    radial-gradient(120% 70% at 50% 0%, #FFF5E7 0%, #FDF8F1 60%, #FAF1E4 100%);
                color: #3A312D;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                overflow-x: hidden;
                box-sizing: border-box;
                line-height: 1.65;
            }
            .mr-landing-breakout * {
                box-sizing: border-box;
            }
            .mr-landing-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 24px;
            }

            /* Typography */
            .mr-landing-breakout h1,
            .mr-landing-breakout h2,
            .mr-landing-breakout h3,
            .mr-landing-breakout h4,
            .mr-landing-breakout h5,
            .mr-landing-breakout h6 {
                font-family: 'Playfair Display', Georgia, serif;
                color: #7A1F3D !important;
                margin-top: 0;
            }
            .mr-landing-breakout p {
                color: #3A312D;
                font-size: 15.5px;
                line-height: 1.7;
            }
            .mr-landing-breakout strong,
            .mr-landing-breakout b {
                color: #7A1F3D;
            }

            .mr-landing-h1 {
                font-size: clamp(32px, 4.4vw, 54px) !important;
                line-height: 1.15 !important;
                font-weight: 800 !important;
                color: #7A1F3D !important;
                margin: 14px 0 18px 0 !important;
                letter-spacing: -0.015em;
            }
            .mr-landing-gold-highlight {
                color: #D9A441;
                display: inline;
            }
            .mr-landing-h2 {
                font-size: clamp(26px, 3.4vw, 38px) !important;
                line-height: 1.25 !important;
                font-weight: 700 !important;
                color: #7A1F3D !important;
                margin: 0 0 14px 0 !important;
                letter-spacing: -0.01em;
            }
            .mr-landing-sub {
                font-size: clamp(16px, 1.6vw, 18px);
                color: #554841 !important;
                line-height: 1.7;
                max-width: 720px;
                margin: 0;
            }

            /* Badges & Tags */
            .mr-tag-eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #FFF3E1;
                border: 1px solid #D9A441;
                color: #7A1F3D;
                font-family: 'Poppins', sans-serif;
                font-size: 11.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.14em;
                padding: 6px 14px;
                border-radius: 9999px;
            }
            .mr-tag-eyebrow i {
                color: #D9A441;
            }

            /* Top Bar */
            .mr-landing-topbar {
                padding: 24px 0 10px 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
            }
            .mr-back-link {
                color: #6B5E57;
                text-decoration: none;
                font-size: 13.5px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: color 0.2s;
            }
            .mr-back-link:hover {
                color: #F58220;
            }

            /* Breadcrumbs Navigation */
            .mr-breadcrumb-nav {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: #8C7E77;
                flex-wrap: wrap;
            }
            .mr-crumb-link {
                color: #6B5E57;
                text-decoration: none;
                font-weight: 500;
                transition: color 0.2s;
            }
            .mr-crumb-link:hover {
                color: #F58220;
                text-decoration: underline;
            }
            .mr-crumb-sep {
                color: #D9A441;
                font-size: 12px;
                font-weight: bold;
            }
            .mr-crumb-current {
                color: #7A1F3D;
                font-weight: 600;
            }

            /* Hero Grid */
            .mr-hero-grid {
                display: grid;
                grid-template-columns: 1.2fr 0.9fr;
                gap: 44px;
                align-items: center;
                padding: 30px 0 60px 0;
            }
            @media (max-width: 960px) {
                .mr-hero-grid {
                    grid-template-columns: 1fr;
                    gap: 36px;
                    padding-bottom: 40px;
                }
            }

            /* CTA Buttons */
            .mr-btn-hero-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                background: #F58220;
                color: #FFFFFF !important;
                font-family: 'Poppins', sans-serif;
                font-weight: 700;
                font-size: 16px;
                padding: 16px 36px;
                border-radius: 9999px;
                text-decoration: none;
                box-shadow: 0 10px 25px rgba(245, 130, 32, 0.35);
                transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
                border: none;
                cursor: pointer;
            }
            .mr-btn-hero-primary:hover {
                background: #E06D10;
                transform: translateY(-2px);
                box-shadow: 0 14px 32px rgba(245, 130, 32, 0.45), 0 0 16px rgba(217, 164, 65, 0.3);
            }
            .mr-btn-hero-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                background: #FFFFFF;
                border: 2px solid #F58220;
                color: #F58220 !important;
                font-family: 'Poppins', sans-serif;
                font-weight: 700;
                font-size: 15px;
                padding: 15px 30px;
                border-radius: 9999px;
                text-decoration: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
                transition: background-color 0.2s, color 0.2s, transform 0.2s, box-shadow 0.2s;
                cursor: pointer;
            }
            .mr-btn-hero-secondary:hover {
                background: #FFF4E8;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(245, 130, 32, 0.15);
            }

            /* Price Display */
            .mr-hero-price-wrap {
                display: flex;
                align-items: baseline;
                gap: 14px;
                margin: 22px 0 10px 0;
                flex-wrap: wrap;
            }
            .mr-hero-price {
                font-size: 38px;
                font-weight: 800;
                font-family: 'Playfair Display', serif;
                color: #7A1F3D;
            }
            .mr-hero-old-price {
                font-size: 20px;
                color: #8C7E77;
                text-decoration: line-through;
            }
            .mr-hero-discount-pill {
                background: #E8F8F0;
                border: 1px solid #2CB67D;
                color: #2CB67D;
                font-size: 13px;
                font-weight: 700;
                padding: 4px 12px;
                border-radius: 9999px;
            }

            /* Interactive Bundle Selector (MeraRashifal Brand Edition) */
            .mr-bundle-box {
                background: #FFFDF9;
                border: 1.5px solid rgba(217, 164, 65, 0.45);
                border-radius: 18px;
                padding: 18px 20px 20px 20px;
                margin: 22px 0 16px 0;
                box-shadow: 0 8px 24px rgba(122, 31, 61, 0.05);
            }
            .mr-bundle-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 14px;
                flex-wrap: wrap;
                gap: 8px;
            }
            .mr-bundle-heading-title {
                font-size: 13.5px;
                font-weight: 700;
                color: #7A1F3D;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mr-bundle-save-pill {
                background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                color: #FFFFFF;
                font-size: 11px;
                font-weight: 700;
                padding: 3px 10px;
                border-radius: 9999px;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .mr-bundle-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }
            @media (max-width: 900px) {
                .mr-bundle-grid {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }
            }
            .mr-bundle-item {
                background: #FFFFFF;
                border: 2px solid #EADCCB;
                border-radius: 14px;
                padding: 14px 14px 12px 14px;
                cursor: pointer;
                position: relative;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                user-select: none;
            }
            .mr-bundle-item:hover {
                border-color: #D9A441;
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(217, 164, 65, 0.18);
            }
            .mr-bundle-item.active {
                border-color: #7A1F3D !important;
                background: #FFF9F2 !important;
                box-shadow: 0 8px 25px rgba(122, 31, 61, 0.12), 0 0 0 2px #7A1F3D;
            }
            .mr-bundle-item.mr-bundle-best-buy {
                border-color: #D9A441;
            }
            .mr-bundle-item.mr-bundle-best-buy.active {
                border-color: #F58220 !important;
                box-shadow: 0 8px 25px rgba(245, 130, 32, 0.22), 0 0 0 2px #F58220;
            }
            .mr-bundle-ribbon {
                position: absolute;
                top: -10px;
                right: 12px;
                background: linear-gradient(135deg, #F58220 0%, #D9A441 100%);
                color: #FFFFFF;
                font-size: 10px;
                font-weight: 800;
                padding: 2px 8px;
                border-radius: 9999px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                box-shadow: 0 2px 6px rgba(245, 130, 32, 0.3);
                white-space: nowrap;
            }
            .mr-bundle-top-row {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 4px;
            }
            .mr-bundle-radio {
                width: 17px;
                height: 17px;
                border-radius: 50%;
                border: 2px solid #C4B5A5;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: all 0.2s ease;
            }
            .mr-bundle-item.active .mr-bundle-radio {
                border-color: #7A1F3D;
                background: #7A1F3D;
            }
            .mr-bundle-item.active .mr-bundle-radio::after {
                content: '';
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background: #FFFFFF;
            }
            .mr-bundle-name {
                font-size: 12.5px;
                font-weight: 700;
                color: #3A312D;
                line-height: 1.3;
            }
            .mr-bundle-item.active .mr-bundle-name {
                color: #7A1F3D;
            }
            .mr-bundle-price-row {
                display: flex;
                align-items: baseline;
                gap: 6px;
                margin: 5px 0 3px 0;
            }
            .mr-bundle-sale {
                font-size: 22px;
                font-weight: 800;
                font-family: 'Poppins', sans-serif;
                color: #7A1F3D;
                line-height: 1;
            }
            .mr-bundle-strike {
                font-size: 12px;
                color: #8C7E77;
                text-decoration: line-through;
            }
            .mr-bundle-desc {
                font-size: 11px;
                color: #6B5E57;
                line-height: 1.4;
                margin-top: 3px;
            }
            .mr-bundle-chat-tag {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 10.5px;
                font-weight: 700;
                color: #0284C7;
                background: #E0F2FE;
                padding: 2px 7px;
                border-radius: 5px;
                margin-top: 5px;
                width: fit-content;
            }
            .mr-bundle-tag-neutral {
                font-size: 10px;
                font-weight: 600;
                color: #64748B;
                background: #F1F5F9;
                padding: 1px 6px;
                border-radius: 4px;
                margin-top: 5px;
                display: inline-block;
                width: fit-content;
            }

            /* Trust Row */
            .mr-trust-row {
                display: flex;
                align-items: center;
                gap: 16px;
                margin-top: 24px;
                font-size: 13px;
                color: #6B5E57;
                flex-wrap: wrap;
            }
            .mr-trust-row span {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .mr-trust-row i {
                color: #F58220;
            }

            /* Right Emblem Stage */
            .mr-emblem-stage {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 440px;
            }
            .mr-emblem-glow {
                position: absolute;
                width: 320px;
                height: 320px;
                background: radial-gradient(circle, rgba(217, 164, 65, 0.3) 0%, rgba(245, 130, 32, 0.12) 50%, transparent 75%);
                filter: blur(28px);
                z-index: 1;
                pointer-events: none;
            }
            .mr-orbit-circle-outer {
                position: absolute;
                width: 420px;
                height: 420px;
                border: 1.5px dashed rgba(217, 164, 65, 0.45);
                border-radius: 50%;
                z-index: 2;
                animation: mrSpinSlow 90s linear infinite;
            }
            .mr-orbit-circle-inner {
                position: absolute;
                width: 340px;
                height: 340px;
                border: 1px solid rgba(122, 31, 61, 0.2);
                border-radius: 50%;
                z-index: 2;
            }
            @keyframes mrSpinSlow {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .mr-report-card-mockup {
                position: relative;
                z-index: 3;
                background: #FFFFFF;
                border: 2px solid #D9A441;
                border-radius: 24px;
                box-shadow: 0 20px 50px rgba(122, 31, 61, 0.12), 0 10px 25px rgba(0, 0, 0, 0.06);
                padding: 36px 30px;
                width: 100%;
                max-width: 380px;
                text-align: center;
            }
            .mr-mockup-badge {
                position: absolute;
                top: -14px;
                left: 50%;
                transform: translateX(-50%);
                background: #7A1F3D;
                color: #FFFFFF;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                padding: 5px 16px;
                border-radius: 9999px;
                white-space: nowrap;
                border: 1px solid #D9A441;
                box-shadow: 0 4px 12px rgba(122, 31, 61, 0.25);
            }
            .mr-mockup-icon {
                width: 72px;
                height: 72px;
                margin: 10px auto 16px auto;
                background: radial-gradient(circle, #FFF4E1 0%, #FFE6BE 100%);
                border: 2px solid #D9A441;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #F58220;
                font-size: 32px;
                box-shadow: 0 8px 20px rgba(217, 164, 65, 0.25);
            }

            /* Metric Bar */
            .mr-metric-bar {
                background: #FFFFFF;
                border-top: 1px solid rgba(217, 164, 65, 0.3);
                border-bottom: 1px solid rgba(217, 164, 65, 0.3);
                padding: 34px 0;
                margin: 10px 0 50px 0;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            }
            .mr-metric-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
                text-align: center;
            }
            @media (max-width: 768px) {
                .mr-metric-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }
            }
            .mr-metric-number {
                font-family: 'Playfair Display', serif;
                font-size: clamp(26px, 3vw, 36px);
                font-weight: 800;
                color: #7A1F3D;
                line-height: 1.1;
                margin-bottom: 4px;
            }
            .mr-metric-label {
                font-size: 13px;
                color: #554841;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            /* Section Spacing */
            .mr-section-wrap {
                padding: 40px 0;
            }

            /* Chapters Grid (Cards) - Center Aligned */
            .mr-chapters-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 24px;
                margin-top: 38px;
            }
            .mr-chapter-card {
                background: #FFFFFF;
                border: 1px solid rgba(217, 164, 65, 0.25);
                border-radius: 20px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
                transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
                position: relative;
                flex: 0 1 calc(33.333% - 18px);
                min-width: 280px;
                max-width: 380px;
                text-align: center;
                box-sizing: border-box;
            }
            .mr-chapter-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 14px 35px rgba(217, 164, 65, 0.16);
                border-color: #D9A441;
            }
            .mr-chapter-num {
                display: inline-block;
                font-family: 'Poppins', sans-serif;
                font-size: 11.5px;
                font-weight: 700;
                color: #7A1F3D;
                background: #FFF3E1;
                border: 1px solid #D9A441;
                padding: 4px 14px;
                border-radius: 8px;
                margin: 0 auto 16px auto;
                letter-spacing: 0.06em;
            }
            .mr-chapter-title {
                font-size: 19px !important;
                font-weight: 700 !important;
                color: #7A1F3D !important;
                margin: 0 0 10px 0 !important;
                line-height: 1.35;
                text-align: center;
            }
            .mr-chapter-desc {
                font-size: 14.5px !important;
                color: #3A312D !important;
                line-height: 1.65;
                margin: 0;
                text-align: center;
            }
            @media (max-width: 960px) {
                .mr-chapter-card {
                    flex: 0 1 calc(50% - 14px);
                    max-width: 48%;
                }
            }
            @media (max-width: 640px) {
                .mr-chapter-card {
                    flex: 1 1 100%;
                    max-width: 100%;
                }
            }

            /* TOC Grid (Table of Contents) */
            .mr-toc-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
                gap: 20px;
                margin-top: 38px;
            }
            .mr-toc-item {
                background: #FFFFFF;
                border: 1px solid rgba(217, 164, 65, 0.25);
                border-radius: 16px;
                padding: 22px 24px;
                display: flex;
                align-items: flex-start;
                gap: 16px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
            }
            .mr-toc-page-pill {
                background: #FFF3E1;
                border: 1px solid #D9A441;
                color: #7A1F3D;
                font-weight: 700;
                font-size: 11.5px;
                padding: 4px 10px;
                border-radius: 6px;
                white-space: nowrap;
                margin-top: 2px;
            }

            /* Comparison Matrix (Card Design) */
            .mr-matrix-wrap {
                background: #FFFFFF;
                border: 1.5px solid rgba(217, 164, 65, 0.35);
                border-radius: 24px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
                overflow: hidden;
                margin-top: 38px;
            }
            .mr-matrix-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                border-bottom: 1px solid #F1E7D8;
            }
            .mr-matrix-row:last-child {
                border-bottom: none;
            }
            .mr-matrix-head {
                font-size: 13.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                padding: 20px 26px;
            }
            .mr-matrix-cell {
                padding: 20px 26px;
                font-size: 15px;
                display: flex;
                align-items: flex-start;
                gap: 14px;
            }
            .mr-cell-other {
                color: #6B5E57;
                background: #FAF6EF;
                border-right: 1px solid #F1E7D8;
            }
            .mr-cell-mr {
                color: #3A312D;
                background: #FFFFFF;
            }
            .mr-cross-icon {
                color: #C2410C;
                font-size: 16px;
                font-weight: 900;
                flex-shrink: 0;
                margin-top: 2px;
            }
            .mr-check-icon {
                color: #2CB67D;
                font-size: 16px;
                font-weight: 900;
                flex-shrink: 0;
                margin-top: 2px;
            }

            /* Cross-Promotion Banner (Niyati AI Chat) */
            .mr-promo-banner {
                background: linear-gradient(135deg, #FFFFFF 0%, #FFF7EE 100%);
                border: 1.5px solid rgba(217, 164, 65, 0.4);
                border-left: 6px solid #7A1F3D;
                border-radius: 24px;
                padding: 38px 34px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 30px;
                flex-wrap: wrap;
                box-shadow: 0 12px 35px rgba(217, 164, 65, 0.15);
                margin: 40px 0;
            }
            .mr-promo-content {
                flex: 1;
                min-width: 280px;
            }
            .mr-promo-title {
                font-size: 25px !important;
                font-weight: 700 !important;
                color: #7A1F3D !important;
                margin: 0 0 10px 0 !important;
            }
            .mr-promo-text {
                font-size: 15px;
                color: #3A312D;
                margin: 0;
                line-height: 1.65;
            }

            /* Related Reports Grid */
            /* Frequently Bought Together - Stand Out & Bold Design */
            .mr-fbt-wrap {
                background: linear-gradient(180deg, #FFFFFF 0%, #FFFBF5 60%, #FFF5E7 100%);
                border: 2px solid #D9A441;
                border-radius: 28px;
                padding: 44px 34px 40px 34px;
                box-shadow: 0 18px 45px rgba(122, 31, 61, 0.08), 0 4px 16px rgba(217, 164, 65, 0.12);
                margin: 48px 0;
                position: relative;
                overflow: hidden;
            }
            .mr-fbt-wrap::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 280px;
                height: 280px;
                background: radial-gradient(circle, rgba(245, 130, 32, 0.1) 0%, transparent 70%);
                pointer-events: none;
            }
            .mr-fbt-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 22px;
                margin-top: 36px;
            }
            @media (max-width: 1200px) {
                .mr-fbt-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }
            }
            @media (max-width: 640px) {
                .mr-fbt-grid {
                    grid-template-columns: 1fr;
                    gap: 18px;
                }
                .mr-fbt-wrap {
                    padding: 28px 16px;
                    border-radius: 20px;
                }
            }
            .mr-fbt-card {
                background: #FFFFFF;
                border: 1.5px solid rgba(217, 164, 65, 0.28);
                border-radius: 20px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
                transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            }
            .mr-fbt-card:hover {
                transform: translateY(-6px);
                border-color: #D9A441;
                box-shadow: 0 18px 40px rgba(217, 164, 65, 0.25);
            }
            .mr-fbt-img-link {
                display: block;
                text-decoration: none;
                position: relative;
            }
            .mr-fbt-img-wrap {
                width: 100%;
                aspect-ratio: 16 / 11;
                background: #FDF8F1;
                position: relative;
                overflow: hidden;
                border-bottom: 1px solid rgba(217, 164, 65, 0.18);
            }
            .mr-fbt-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.35s ease;
            }
            .mr-fbt-card:hover .mr-fbt-img {
                transform: scale(1.05);
            }
            .mr-fbt-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(4px);
                border: 1px solid rgba(217, 164, 65, 0.35);
                color: #7A1F3D;
                font-size: 11px;
                font-weight: 700;
                padding: 3px 9px;
                border-radius: 8px;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            }
            .mr-fbt-badge i {
                color: #D9A441;
                font-size: 11px;
            }
            .mr-fbt-content {
                padding: 18px 20px 20px 20px;
                display: flex;
                flex-direction: column;
                flex: 1;
            }
            .mr-fbt-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: 16.5px !important;
                font-weight: 700 !important;
                color: #7A1F3D !important;
                margin: 0 0 8px 0 !important;
                line-height: 1.35 !important;
                min-height: 44px;
            }
            .mr-fbt-title a {
                color: #7A1F3D !important;
                text-decoration: none !important;
                transition: color 0.2s ease;
            }
            .mr-fbt-title a:hover {
                color: #F58220 !important;
            }
            .mr-fbt-desc {
                font-size: 12.5px !important;
                color: #554841 !important;
                line-height: 1.55 !important;
                margin: 0 0 16px 0 !important;
                min-height: 38px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .mr-fbt-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: auto;
                padding-top: 14px;
                border-top: 1px dashed rgba(217, 164, 65, 0.3);
                gap: 10px;
            }
            .mr-fbt-price-col {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            .mr-fbt-price-main {
                display: flex;
                align-items: baseline;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif !important;
                color: #7A1F3D !important;
                font-weight: 800 !important;
                line-height: 1;
            }
            .mr-fbt-symbol {
                font-size: 15px;
                font-weight: 700;
                margin-right: 1px;
            }
            .mr-fbt-val {
                font-size: 21px;
                font-weight: 800;
            }
            .mr-fbt-strike {
                font-size: 12px;
                color: #8C7E77;
                text-decoration: line-through;
                font-family: 'Poppins', sans-serif !important;
            }
            .mr-fbt-discount-tag {
                background: #E8F8F0;
                color: #2CB67D;
                font-size: 10.5px;
                font-weight: 700;
                padding: 1px 6px;
                border-radius: 4px;
                display: inline-block;
                width: fit-content;
                font-family: 'Poppins', sans-serif;
            }
            .mr-fbt-cta-btn {
                background: linear-gradient(135deg, #F58220 0%, #E6680D 100%) !important;
                color: #FFFFFF !important;
                text-decoration: none !important;
                padding: 9px 16px !important;
                border-radius: 9999px !important;
                font-weight: 700 !important;
                font-size: 12.5px !important;
                font-family: 'Poppins', sans-serif !important;
                box-shadow: 0 4px 14px rgba(245, 130, 32, 0.32) !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                transition: all 0.2s ease !important;
                white-space: nowrap !important;
            }
            .mr-fbt-cta-btn:hover {
                background: linear-gradient(135deg, #E6680D 0%, #C2410C 100%) !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 18px rgba(245, 130, 32, 0.45) !important;
                color: #FFFFFF !important;
            }

            /* FAQ Accordion */
            .mr-faq-list {
                max-width: 900px;
                margin: 38px auto 0 auto;
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            .mr-faq-item {
                background: #FFFFFF;
                border: 1px solid rgba(217, 164, 65, 0.25);
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .mr-faq-item.active {
                border-color: #D9A441;
                box-shadow: 0 8px 24px rgba(217, 164, 65, 0.12);
            }
            .mr-faq-q {
                padding: 22px 26px;
                font-size: 17px;
                font-weight: 700;
                color: #7A1F3D;
                font-family: 'Playfair Display', serif;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: space-between;
                user-select: none;
            }
            .mr-faq-icon {
                font-size: 20px;
                color: #F58220;
                transition: transform 0.25s;
                font-weight: 700;
            }
            .mr-faq-item.active .mr-faq-icon {
                transform: rotate(45deg);
            }
            .mr-faq-a {
                display: none;
                padding: 0 26px 24px 26px;
                font-size: 15px;
                color: #3A312D;
                line-height: 1.7;
            }

            /* Bottom Sticky Floating CTA Bar - MeraRashifal Brand Design System */
            .mr-sticky-cta-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: rgba(253, 248, 241, 0.98);
                border-top: 2px solid rgba(217, 164, 65, 0.45);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                z-index: 9998;
                padding: 12px 24px;
                box-shadow: 0 -8px 28px rgba(0, 0, 0, 0.09);
                transform: translateY(120%);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), bottom 0.2s ease;
                box-sizing: border-box;
            }
            .mr-sticky-cta-bar.visible {
                transform: translateY(0);
            }
            .mr-sticky-cta-inner {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                padding-right: 170px; /* Leave comfortable room for floating Niyati chat button on desktop */
                box-sizing: border-box;
            }
            .mr-sticky-info {
                flex: 1;
                text-align: left;
                min-width: 0;
            }
            .mr-sticky-title {
                font-weight: 700;
                color: #7A1F3D;
                font-size: 16px;
                font-family: 'Playfair Display', serif;
                line-height: 1.3;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .mr-sticky-meta {
                font-size: 12.5px;
                color: #6B5E57;
                margin-top: 2px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .mr-sticky-action-center {
                flex: 1;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            .mr-sticky-cta-btn {
                padding: 13px 32px !important;
                font-size: 15px !important;
                white-space: nowrap !important;
                box-shadow: 0 4px 18px rgba(245, 130, 32, 0.4) !important;
                border-radius: 25px !important;
            }
            .mr-sticky-price-wrap {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                text-align: right;
            }
            .mr-sticky-price {
                font-size: 22px;
                font-weight: 800;
                color: #7A1F3D;
                font-family: 'Playfair Display', serif;
                line-height: 1.1;
            }
            .mr-sticky-badge {
                font-size: 11.5px;
                color: #2CB67D;
                font-weight: 700;
                margin-top: 2px;
            }

            /* Responsive Media Queries */
            @media (max-width: 1024px) {
                .mr-sticky-cta-bar {
                    bottom: 60px; /* Sit safely above .wp-bottom-menu */
                }
                .mr-sticky-cta-inner {
                    padding-right: 0 !important;
                }
            }

            @media (max-width: 768px) {
                body.mr-sticky-active #niyati-launcher,
                body:has(#mr-landing-sticky-bar.visible) #niyati-launcher {
                    bottom: 185px !important;
                    transition: bottom 0.3s ease;
                }
                .mr-sticky-cta-bar {
                    padding: 10px 16px !important;
                    bottom: 60px;
                }
                .mr-sticky-cta-inner {
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    text-align: center;
                    padding-right: 0 !important;
                }
                .mr-sticky-info {
                    flex: none;
                    width: 100%;
                    text-align: center;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                }
                .mr-sticky-title {
                    font-size: 13.5px;
                    max-width: 70%;
                }
                .mr-sticky-meta {
                    font-size: 12px;
                    margin-top: 0;
                }
                .mr-sticky-action-center {
                    flex: none;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .mr-sticky-cta-btn {
                    width: 100% !important;
                    max-width: 340px !important;
                    margin: 0 auto !important;
                    padding: 12px 18px !important;
                    font-size: 14.5px !important;
                }
                .mr-sticky-price-wrap {
                    display: none; /* Merged into button text */
                }
            }

            /* Mobile & Tablet General Responsiveness */
            @media (max-width: 640px) {
                .mr-landing-container {
                    padding: 0 16px;
                }
                .mr-btn-hero-primary, .mr-btn-hero-secondary {
                    width: 100%;
                }
                .mr-matrix-row {
                    grid-template-columns: 1fr;
                }
                .mr-cell-other {
                    border-right: none;
                    border-bottom: 1px solid #F1E7D8;
                }
            }

            /* Hide Astra Default Page Title, Breadcrumbs and Sidebars across all report landing pages */
            header.entry-header,
            .entry-header,
            .ast-single-post .entry-header,
            .hfe-breadcrumbs-widget,
            .ast-breadcrumbs,
            .sidebar-main,
            #secondary,
            .ast-site-sidebar,
            aside#secondary {
                display: none !important;
            }
            .content-area,
            #primary {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .site-content {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            /* =========================================================
               Birth Details Modal - MeraRashifal Brand Design System
               ========================================================= */
            #mr-details-modal.mr-modal {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(45, 30, 24, 0.65) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                z-index: 9999999 !important;
                display: none;
                align-items: center !important;
                justify-content: center !important;
                padding: 16px !important;
                box-sizing: border-box !important;
            }
            #mr-details-modal.mr-modal.is-open {
                display: flex !important;
            }
            #mr-details-modal .mr-modal-content {
                background: #FFFFFF !important;
                border: 2px solid #D9A441 !important;
                border-radius: 24px !important;
                box-shadow: 0 25px 60px rgba(122, 31, 61, 0.16), 0 10px 25px rgba(0, 0, 0, 0.08) !important;
                color: #3A312D !important;
                max-width: 580px !important;
                width: 100% !important;
                padding: 34px 32px !important;
                position: relative !important;
                max-height: 92vh !important;
                overflow-y: auto !important;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                box-sizing: border-box !important;
                scrollbar-width: thin !important;
                scrollbar-color: #D9A441 #FDF8F1 !important;
            }
            #mr-details-modal .mr-modal-content::-webkit-scrollbar {
                width: 6px !important;
            }
            #mr-details-modal .mr-modal-content::-webkit-scrollbar-thumb {
                background: #D9A441 !important;
                border-radius: 9999px !important;
            }
            #mr-details-modal .mr-modal-content::-webkit-scrollbar-track {
                background: #FDF8F1 !important;
            }

            #mr-details-modal .mr-modal-close {
                position: absolute !important;
                top: 18px !important;
                right: 20px !important;
                width: 36px !important;
                height: 36px !important;
                border-radius: 50% !important;
                background: #FAF5EE !important;
                border: 1.5px solid rgba(217, 164, 65, 0.35) !important;
                color: #7A1F3D !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 20px !important;
                cursor: pointer !important;
                line-height: 1 !important;
                transition: all 0.2s ease !important;
                box-sizing: border-box !important;
            }
            #mr-details-modal .mr-modal-close:hover {
                background: #F58220 !important;
                color: #FFFFFF !important;
                border-color: #F58220 !important;
                transform: rotate(90deg) !important;
            }

            #mr-details-modal .mr-modal-brand-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: 24px !important;
                font-weight: 800 !important;
                color: #7A1F3D !important;
                margin: 8px 0 6px 0 !important;
                letter-spacing: -0.015em !important;
            }
            #mr-details-modal .mr-modal-sub {
                font-size: 13.5px !important;
                color: #554841 !important;
                line-height: 1.55 !important;
                margin: 0 !important;
            }

            #mr-details-modal .mr-form-group {
                margin-bottom: 16px !important;
                text-align: left !important;
            }
            #mr-details-modal .mr-form-group label {
                display: block !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                color: #3A312D !important;
                margin-bottom: 6px !important;
                height: 20px !important;
                line-height: 20px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                font-family: 'Poppins', sans-serif !important;
            }
            #mr-details-modal input,
            #mr-details-modal select {
                width: 100% !important;
                height: 48px !important;
                line-height: 46px !important;
                padding: 0 16px !important;
                background: #FAF7F2 !important;
                border: 1.5px solid #E8DFD3 !important;
                border-radius: 12px !important;
                color: #3A312D !important;
                font-size: 14.5px !important;
                font-family: 'Poppins', sans-serif !important;
                box-sizing: border-box !important;
                vertical-align: middle !important;
                transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s !important;
            }
            #mr-details-modal select {
                appearance: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                background-color: #FAF7F2 !important;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='18' height='18' fill='none' stroke='%237A1F3D' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
                background-repeat: no-repeat !important;
                background-position: right 14px center !important;
                background-size: 14px 14px !important;
                padding-right: 38px !important;
                cursor: pointer !important;
                line-height: normal !important;
            }
            #mr-details-modal input[type="date"],
            #mr-details-modal input[type="time"] {
                display: flex !important;
                align-items: center !important;
                line-height: normal !important;
            }
            #mr-details-modal input::placeholder {
                color: #8C7E77 !important;
                opacity: 1 !important;
            }
            #mr-details-modal input:focus,
            #mr-details-modal select:focus {
                outline: none !important;
                background: #FFFFFF !important;
                border-color: #F58220 !important;
                box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.18) !important;
            }

            #mr-details-modal input.mr-input-error,
            #mr-details-modal select.mr-input-error {
                border-color: #E63946 !important;
                background-color: #FFF5F5 !important;
                box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.22) !important;
            }

            #mr-details-modal .mr-input-hint {
                font-size: 11.5px !important;
                color: #6B5E57 !important;
                margin-top: 5px !important;
                display: flex !important;
                align-items: flex-start !important;
                gap: 6px !important;
                line-height: 1.4 !important;
            }
            #mr-details-modal .mr-input-hint i {
                flex-shrink: 0 !important;
                margin-top: 2px !important;
            }

            #mr-details-modal .mr-form-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 14px !important;
                margin-bottom: 16px !important;
            }
            #mr-details-modal .mr-half {
                margin-bottom: 0 !important;
            }

            #mr-details-modal .mr-btn-brand-submit {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 10px !important;
                width: 100% !important;
                background: #F58220 !important;
                color: #FFFFFF !important;
                font-family: 'Poppins', sans-serif !important;
                font-size: 16px !important;
                font-weight: 700 !important;
                padding: 16px 24px !important;
                border-radius: 9999px !important;
                border: none !important;
                cursor: pointer !important;
                box-shadow: 0 10px 25px rgba(245, 130, 32, 0.35) !important;
                transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s !important;
                text-decoration: none !important;
                box-sizing: border-box !important;
            }
            #mr-details-modal .mr-btn-brand-submit:hover {
                background: #E06D10 !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 14px 32px rgba(245, 130, 32, 0.45), 0 0 16px rgba(217, 164, 65, 0.3) !important;
            }

            #mr-details-modal .mr-modal-footer-trust {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 14px !important;
                margin-top: 16px !important;
                font-size: 12px !important;
                color: #6B5E57 !important;
                flex-wrap: wrap !important;
            }
            #mr-details-modal .mr-modal-footer-trust i {
                color: #F58220 !important;
            }

            /* Autocomplete Results Styling */
            #mr-details-modal .mr-city-results {
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                right: 0 !important;
                background: #FFFFFF !important;
                border: 1.5px solid #D9A441 !important;
                border-radius: 12px !important;
                box-shadow: 0 16px 36px rgba(122, 31, 61, 0.12) !important;
                max-height: 220px !important;
                overflow-y: auto !important;
                z-index: 999999999 !important;
                margin-top: 4px !important;
                padding: 6px 0 !important;
            }
            #mr-details-modal .mr-city-item {
                padding: 10px 16px !important;
                font-size: 13.5px !important;
                color: #3A312D !important;
                cursor: pointer !important;
                transition: background 0.15s, color 0.15s !important;
            }
            #mr-details-modal .mr-city-item:hover {
                background: #FFF5E7 !important;
                color: #7A1F3D !important;
            }

            @media (max-width: 600px) {
                #mr-details-modal.mr-modal {
                    padding: 12px !important;
                }
                #mr-details-modal .mr-modal-content {
                    padding: 24px 18px !important;
                    border-radius: 20px !important;
                    max-height: 94vh !important;
                    width: 100% !important;
                }
                #mr-details-modal .mr-form-row {
                    grid-template-columns: 1fr !important;
                    gap: 14px !important;
                }
            }
        </style>

        <div class="mr-landing-breakout">
            <div class="mr-landing-container">

                <!-- Top Bar & Breadcrumb -->
                <div class="mr-landing-topbar">
                    <nav class="mr-breadcrumb-nav" aria-label="Breadcrumb">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="mr-crumb-link"><i class="fa-solid fa-house" style="font-size:11px;"></i> Home</a>
                        <span class="mr-crumb-sep">›</span>
                        <a href="<?php echo esc_url($catalog_url); ?>" class="mr-crumb-link">Astrological Reports</a>
                        <span class="mr-crumb-sep">›</span>
                        <span class="mr-crumb-current"><?php echo esc_html($report_title); ?></span>
                    </nav>
                    <div class="mr-tag-eyebrow">
                        <i class="fa-solid fa-sun"></i> <?php echo esc_html($data['eyebrow']); ?>
                    </div>
                </div>

                <!-- 1. Hero Section -->
                <section class="mr-hero-grid">
                    <div>
                        <h1 class="mr-landing-h1">
                            <?php echo esc_html($data['title_prefix']); ?> <br>
                            <span class="mr-landing-gold-highlight"><?php echo esc_html($data['title_highlight']); ?></span>
                        </h1>
                        <p class="mr-landing-sub">
                            <?php echo wp_kses_post($data['hero_sub']); ?>
                        </p>

                        <!-- Dynamic Bundle Package Selector or Standard Price -->
                        <?php if ($bundles_active && !$has_token): 
                            $trio_paired = !empty($tier_trio['paired_reports']) ? (array)$tier_trio['paired_reports'] : [];
                            $duo_paired  = !empty($tier_duo['paired_reports']) ? (array)$tier_duo['paired_reports'] : [];
                            $trio_reports = array_merge([$report_slug], $trio_paired);
                            $duo_reports  = array_merge([$report_slug], $duo_paired);
                            $single_reports = [$report_slug];

                            $trio_price = !empty($tier_trio['price_sale']) ? floatval($tier_trio['price_sale']) : 599;
                            $trio_reg   = !empty($tier_trio['price_regular']) ? floatval($tier_trio['price_regular']) : 2097;
                            $trio_cred  = isset($tier_trio['chat_credits']) ? intval($tier_trio['chat_credits']) : 30;
                            $trio_title = !empty($tier_trio['title']) ? $tier_trio['title'] : 'Cosmic Trinity (Pack of 3 + AI Chat)';

                            $duo_price  = !empty($tier_duo['price_sale']) ? floatval($tier_duo['price_sale']) : 449;
                            $duo_reg    = !empty($tier_duo['price_regular']) ? floatval($tier_duo['price_regular']) : 1198;
                            $duo_cred   = isset($tier_duo['chat_credits']) ? intval($tier_duo['chat_credits']) : 15;
                            $duo_title  = !empty($tier_duo['title']) ? $tier_duo['title'] : 'Smart Duo (Pack of 2)';

                            $single_price = !empty($tier_single['price_sale']) ? floatval($tier_single['price_sale']) : floatval($data['price_sale']);
                            $single_reg   = !empty($tier_single['price_regular']) ? floatval($tier_single['price_regular']) : floatval($data['price_regular']);
                            $single_title = !empty($tier_single['title']) ? $tier_single['title'] : 'Single Report';
                            $single_cred  = isset($tier_single['chat_credits']) ? intval($tier_single['chat_credits']) : 0;
                        ?>
                            <!-- Interactive 3-Tier Bundle Selector Widget -->
                            <div class="mr-bundle-box" id="mr-bundle-selector-wrap">
                                <div class="mr-bundle-heading">
                                    <span class="mr-bundle-heading-title">
                                        <i class="fa-solid fa-gift" style="color:#D9A441;"></i> Select Your Astrological Package:
                                    </span>
                                    <span class="mr-bundle-save-pill">
                                        <i class="fa-solid fa-bolt"></i> Best Value Guaranteed
                                    </span>
                                </div>

                                <div class="mr-bundle-grid" id="mr-bundle-cards-grid">
                                    <!-- Tier 1: Single Report -->
                                    <div class="mr-bundle-item" 
                                         data-tier="single" 
                                         data-price="<?php echo esc_attr($single_price); ?>" 
                                         data-regular="<?php echo esc_attr($single_reg); ?>" 
                                         data-credits="<?php echo esc_attr($single_cred); ?>" 
                                         data-reports="<?php echo esc_attr(wp_json_encode($single_reports)); ?>" 
                                         data-title="<?php echo esc_attr($single_title); ?>" 
                                         data-btn-label="Get Single Report Now &bull; ₹<?php echo number_format($single_price); ?> &rarr;">
                                        <div class="mr-bundle-top-row">
                                            <span class="mr-bundle-radio"></span>
                                            <span class="mr-bundle-name"><?php echo esc_html($single_title); ?></span>
                                        </div>
                                        <div class="mr-bundle-price-row">
                                            <span class="mr-bundle-sale">₹<?php echo number_format($single_price); ?></span>
                                            <?php if ($single_reg > $single_price): ?>
                                                <span class="mr-bundle-strike">₹<?php echo number_format($single_reg); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mr-bundle-desc">1 In-Depth Dossier (<?php echo esc_html($report_pages); ?>)</div>
                                        <?php if (!empty($tier_single['badge'])): ?>
                                            <span class="mr-bundle-tag-neutral"><?php echo esc_html($tier_single['badge']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tier 2: Smart Duo (Pack of 2) -->
                                    <?php if (!empty($tier_duo['enabled'])): ?>
                                    <div class="mr-bundle-item" 
                                         data-tier="duo" 
                                         data-price="<?php echo esc_attr($duo_price); ?>" 
                                         data-regular="<?php echo esc_attr($duo_reg); ?>" 
                                         data-credits="<?php echo esc_attr($duo_cred); ?>" 
                                         data-reports="<?php echo esc_attr(wp_json_encode($duo_reports)); ?>" 
                                         data-title="<?php echo esc_attr($duo_title); ?>" 
                                         data-btn-label="Get Smart Duo (2 Reports + <?php echo esc_attr($duo_cred); ?> AI Questions) &bull; ₹<?php echo number_format($duo_price); ?> &rarr;">
                                        <?php if (!empty($tier_duo['badge'])): ?>
                                            <span class="mr-bundle-ribbon" style="background:#ea580c;"><?php echo esc_html($tier_duo['badge']); ?></span>
                                        <?php endif; ?>
                                        <div class="mr-bundle-top-row">
                                            <span class="mr-bundle-radio"></span>
                                            <span class="mr-bundle-name"><?php echo esc_html($duo_title); ?></span>
                                        </div>
                                        <div class="mr-bundle-price-row">
                                            <span class="mr-bundle-sale">₹<?php echo number_format($duo_price); ?></span>
                                            <?php if ($duo_reg > $duo_price): ?>
                                                <span class="mr-bundle-strike">₹<?php echo number_format($duo_reg); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mr-bundle-desc">2 Sacred Reports (45+ Pages Total)</div>
                                        <?php if (!empty($duo_cred)): ?>
                                            <div class="mr-bundle-chat-tag">
                                                <i class="fa-solid fa-comments"></i> +<?php echo esc_html($duo_cred); ?> Niyati AI Questions
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Tier 3: Cosmic Trinity (Pack of 3 + AI Chat) - BEST BUY (Active by default) -->
                                    <?php if (!empty($tier_trio['enabled'])): ?>
                                    <div class="mr-bundle-item mr-bundle-best-buy active" 
                                         data-tier="trio" 
                                         data-price="<?php echo esc_attr($trio_price); ?>" 
                                         data-regular="<?php echo esc_attr($trio_reg); ?>" 
                                         data-credits="<?php echo esc_attr($trio_cred); ?>" 
                                         data-reports="<?php echo esc_attr(wp_json_encode($trio_reports)); ?>" 
                                         data-title="<?php echo esc_attr($trio_title); ?>" 
                                         data-btn-label="Get Best Buy VIP Suite (3 Reports + <?php echo esc_attr($trio_cred); ?> AI Chat) &bull; ₹<?php echo number_format($trio_price); ?> &rarr;">
                                        <span class="mr-bundle-ribbon"><i class="fa-solid fa-crown" style="font-size:9px;"></i> <?php echo esc_html($tier_trio['badge'] ?? 'BEST BUY'); ?></span>
                                        <div class="mr-bundle-top-row">
                                            <span class="mr-bundle-radio"></span>
                                            <span class="mr-bundle-name"><?php echo esc_html($trio_title); ?></span>
                                        </div>
                                        <div class="mr-bundle-price-row">
                                            <span class="mr-bundle-sale" style="color:#F58220;">₹<?php echo number_format($trio_price); ?></span>
                                            <?php if ($trio_reg > $trio_price): ?>
                                                <span class="mr-bundle-strike">₹<?php echo number_format($trio_reg); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mr-bundle-desc">3 Complete Sacred Reports (65+ Pages Total)</div>
                                        <?php if (!empty($trio_cred)): ?>
                                            <div class="mr-bundle-chat-tag" style="background:#FEF3C7; color:#B45309;">
                                                <i class="fa-solid fa-wand-magic-sparkles"></i> +<?php echo esc_html($trio_cred); ?> Niyati AI Questions
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Price display (Standard fallback) -->
                            <div class="mr-hero-price-wrap">
                                <span class="mr-hero-price">₹<?php echo esc_html($report_price); ?></span>
                                <span class="mr-hero-old-price">₹<?php echo esc_html($report_regular_price); ?></span>
                                <span class="mr-hero-discount-pill"><?php echo esc_html($data['discount_text']); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Dual Action Buttons -->
                        <div style="display:flex; gap:14px; flex-wrap:wrap; margin-top:22px;">
                            <?php if ($has_token): ?>
                                <button type="button" class="mr-btn-hero-primary mr-btn-generate-direct" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" data-price="<?php echo esc_attr($data['price_sale']); ?>">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Report (1 Token Available) &rarr;
                                </button>
                            <?php elseif ($bundles_active && !empty($tier_trio['enabled'])): ?>
                                <button type="button" class="mr-btn-hero-primary mr-btn-buy-alacarte mr-btn-bundle-order" id="mr-hero-bundle-btn" 
                                        data-slug="<?php echo esc_attr($report_slug); ?>" 
                                        data-order-type="bundle" 
                                        data-bundle-tier="trio" 
                                        data-price="<?php echo esc_attr($trio_price); ?>" 
                                        data-bundle-title="<?php echo esc_attr($trio_title); ?>" 
                                        data-chat-credits="<?php echo esc_attr($trio_cred); ?>" 
                                        data-bundle-reports="<?php echo esc_attr(wp_json_encode($trio_reports)); ?>" 
                                        data-title="<?php echo esc_attr($trio_title); ?>">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> <span id="mr-hero-bundle-btn-text">Get Best Buy VIP Suite (3 Reports + <?php echo esc_html($trio_cred); ?> AI Chat) &bull; ₹<?php echo number_format($trio_price); ?> &rarr;</span>
                                </button>
                            <?php else: ?>
                                <button type="button" class="mr-btn-hero-primary mr-btn-buy-alacarte" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" data-price="<?php echo esc_attr($data['price_sale']); ?>">
                                    <i class="fa-solid fa-bolt"></i> Get Your Detailed Report Now &rarr;
                                </button>
                            <?php endif; ?>

                            <button type="button" class="mr-btn-hero-secondary mr-link-sample" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" style="border-color: rgba(245, 130, 32, 0.45); color: #B45309; background: #FFF9F2;">
                                <i class="fa-regular fa-eye"></i> Preview Sample Report
                            </button>

                            <button type="button" class="mr-btn-hero-secondary" onclick="if(window.openNiyatiChat){window.openNiyatiChat('<?php echo esc_js($report_slug); ?>', '<?php echo esc_js($report_title); ?>');}else{var btn=document.querySelector('#niyati-launcher, #niyati-chat-toggle, .niyati-chat-button');if(btn)btn.click();else window.location.href='<?php echo esc_url(home_url('/')); ?>';}">
                                <i class="fa-solid fa-comments"></i> Ask Niyati AI First
                            </button>
                        </div>

                        <!-- Trust signals -->
                        <div class="mr-trust-row">
                            <span><i class="fa-solid fa-shield-halved"></i> 100% Private &amp; Secure</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-envelope"></i> Instant PDF &amp; Email Delivery</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-file-lines"></i> Comprehensive <?php echo esc_html($report_pages); ?></span>
                        </div>
                    </div>

                    <!-- Right Visual Emblem Card -->
                    <div class="mr-emblem-stage">
                        <div class="mr-emblem-glow"></div>
                        <div class="mr-orbit-circle-outer"></div>
                        <div class="mr-orbit-circle-inner"></div>

                        <div class="mr-report-card-mockup">
                            <div class="mr-mockup-badge">
                                <i class="fa-solid fa-crown" style="color:#D9A441; margin-right:4px;"></i> <?php echo esc_html($data['badge']); ?>
                            </div>
                            <div class="mr-mockup-icon">
                                <i class="<?php echo esc_attr($data['emblem_icon']); ?>"></i>
                            </div>
                            <h3 style="font-family:'Playfair Display', serif; font-size:21px; font-weight:800; color:#7A1F3D; margin:0 0 6px 0; line-height:1.25;">
                                <?php echo esc_html($data['name']); ?>
                            </h3>
                            <div style="font-size:12px; color:#D9A441; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:18px;">
                                <?php echo esc_html($data['cover_badge']); ?>
                            </div>

                            <div style="background:#FFF9F0; border-radius:14px; padding:14px; text-align:left; font-size:13px; color:#554841; border:1px solid rgba(217, 164, 65, 0.2);">
                                <?php foreach ($data['highlights'] as $hl): ?>
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
                                        <i class="fa-solid fa-circle-check" style="color:#2CB67D; flex-shrink:0;"></i> <span><?php echo esc_html($hl); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Optional: Palm Scanner Integration for Palmistry Report -->
                <?php if (!empty($data['has_palm_scanner'])): ?>
                    <section class="mr-section-wrap" style="padding-top:0;">
                        <div style="text-align:center; margin-bottom:24px;">
                            <span class="mr-tag-eyebrow"><i class="fa-solid fa-camera"></i> Free Interactive Samudrik Scanner</span>
                            <h2 class="mr-landing-h2" style="margin-top:12px;">Scan Your Palm For An Instant Free Snapshot</h2>
                            <p class="mr-landing-sub" style="margin:0 auto;">
                                Upload a photo of your dominant palm below to receive a free 6-point Samudrik snapshot reading. You can unlock the complete 26-page master publication dossier anytime.
                            </p>
                        </div>
                        <div style="background:#FFFFFF; border:2px solid #D9A441; border-radius:24px; padding:24px; box-shadow:0 12px 35px rgba(217, 164, 65, 0.12);">
                            <?php echo do_shortcode('[mr_palm_reading show_header="no"]'); ?>
                        </div>
                    </section>
                <?php endif; ?>

            </div><!-- .mr-landing-container -->

            <!-- 2. Full-Width Proof Metric Bar -->
            <div class="mr-metric-bar">
                <div class="mr-landing-container">
                    <div class="mr-metric-grid">
                        <div class="mr-metric-item">
                            <div class="mr-metric-number"><?php echo esc_html($data['stats']['stat1_val']); ?></div>
                            <div class="mr-metric-label"><?php echo esc_html($data['stats']['stat1_lbl']); ?></div>
                        </div>
                        <div class="mr-metric-item">
                            <div class="mr-metric-number"><?php echo esc_html($data['stats']['stat2_val']); ?></div>
                            <div class="mr-metric-label"><?php echo esc_html($data['stats']['stat2_lbl']); ?></div>
                        </div>
                        <div class="mr-metric-item">
                            <div class="mr-metric-number"><?php echo esc_html($data['stats']['stat3_val']); ?></div>
                            <div class="mr-metric-label"><?php echo esc_html($data['stats']['stat3_lbl']); ?></div>
                        </div>
                        <div class="mr-metric-item">
                            <div class="mr-metric-number"><?php echo esc_html($data['stats']['stat4_val']); ?></div>
                            <div class="mr-metric-label"><?php echo esc_html($data['stats']['stat4_lbl']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mr-landing-container">

                <!-- 3. What's Inside (Chapters / Modules) -->
                <section class="mr-section-wrap">
                    <div style="text-align:center;">
                        <span class="mr-tag-eyebrow"><i class="fa-solid fa-compass"></i> Exhaustive Vedic Compilation</span>
                        <h2 class="mr-landing-h2" style="margin-top:14px;">What&#39;s Inside Your <?php echo esc_html($report_pages); ?> Dossier</h2>
                        <p class="mr-landing-sub" style="margin:0 auto;">
                            Mathematically calculated from sacred Shastras and verified celestial coordinates specific to your exact moment and coordinates.
                        </p>
                    </div>

                    <div class="mr-chapters-grid">
                        <?php foreach ($data['modules'] as $mod): ?>
                            <div class="mr-chapter-card">
                                <span class="mr-chapter-num"><?php echo esc_html($mod['tag']); ?></span>
                                <h3 class="mr-chapter-title"><?php echo esc_html($mod['title']); ?></h3>
                                <p class="mr-chapter-desc"><?php echo esc_html($mod['desc']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- 4. Table of Contents Breakdown -->
                <?php if (!empty($data['toc'])): ?>
                    <section class="mr-section-wrap" style="padding-top:0;">
                        <div style="text-align:center;">
                            <span class="mr-tag-eyebrow"><i class="fa-solid fa-list-ol"></i> Page by Page Structure</span>
                            <h2 class="mr-landing-h2" style="margin-top:14px;">Complete Report Index &amp; Roadmap</h2>
                            <p class="mr-landing-sub" style="margin:0 auto;">Every section is meticulously crafted for clarity, depth, and actionable wisdom.</p>
                        </div>

                        <div class="mr-toc-grid">
                            <?php foreach ($data['toc'] as $toc_item): ?>
                                <div class="mr-toc-item">
                                    <span class="mr-toc-page-pill"><?php echo esc_html($toc_item['pages']); ?></span>
                                    <div>
                                        <h3 style="font-size:16px; font-weight:700; color:#7A1F3D; margin:0 0 5px 0;"><?php echo esc_html($toc_item['title']); ?></h3>
                                        <p style="font-size:13.5px; color:#554841; margin:0; line-height:1.5;"><?php echo esc_html($toc_item['summary']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 5. Comparative Matrix (Why MeraRashifal Beats Generic Reports) -->
                <?php if (!empty($data['why_different'])): ?>
                    <section class="mr-section-wrap" style="padding-top:0;">
                        <div style="text-align:center;">
                            <span class="mr-tag-eyebrow"><i class="fa-solid fa-scale-balanced"></i> The Quality Contrast</span>
                            <h2 class="mr-landing-h2" style="margin-top:14px;">Why This Beats A Generic Online Reading</h2>
                            <p class="mr-landing-sub" style="margin:0 auto;">See why thousands of seekers rely on MeraRashifal&#39;s Vedic computation engine.</p>
                        </div>

                        <div class="mr-matrix-wrap">
                            <div class="mr-matrix-row">
                                <div class="mr-matrix-head" style="color:#6B5E57; background:#F5EFE4;">Other Generic Automated Reports</div>
                                <div class="mr-matrix-head" style="color:#7A1F3D; background:#FFF3E1;">MeraRashifal Master Dossier</div>
                            </div>

                            <?php foreach ($data['why_different'] as $usp): ?>
                                <div class="mr-matrix-row">
                                    <div class="mr-matrix-cell mr-cell-other">
                                        <span class="mr-cross-icon">&#x2715;</span>
                                        <div>Generic statements without verified scripture cross-references or precision calculations.</div>
                                    </div>
                                    <div class="mr-matrix-cell mr-cell-mr">
                                        <span class="mr-check-icon">&#x2713;</span>
                                        <div><strong><?php echo esc_html($usp['title']); ?></strong>: <?php echo esc_html($usp['desc']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 6. Cross-Promotion: Niyati AI Astrology Chat -->
                <div class="mr-promo-banner" id="mr-promo-niyati">
                    <div class="mr-promo-content">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                            <span class="mr-tag-eyebrow" style="background:#FFF3E1; color:#7A1F3D; border-color:#D9A441;">
                                <i class="fa-solid fa-sparkles" style="color:#D9A441;"></i> Conversational Vedic Astrologer
                            </span>
                        </div>
                        <h3 class="mr-promo-title">It Doesn&#39;t End at the PDF &mdash; Meet Niyati AI</h3>
                        <p class="mr-promo-text">
                            Have questions about your report? Wondering what an upcoming transit, dasha, or dosh means for your specific job change or relationship? Chat directly with <strong>Niyati</strong>, our specialized Vedic AI astrologer, to ask unlimited follow-ups, what-ifs, and timing queries anytime.
                        </p>
                    </div>
                    <div>
                        <button type="button" class="mr-btn-hero-primary" onclick="if(window.openNiyatiChat){window.openNiyatiChat('<?php echo esc_js($report_slug); ?>', '<?php echo esc_js($report_title); ?>');}else{var btn=document.querySelector('#niyati-launcher, #niyati-chat-toggle, .niyati-chat-button');if(btn)btn.click();else window.location.href='<?php echo esc_url(home_url('/')); ?>';}">
                            <i class="fa-solid fa-comments"></i> Ask Niyati AI Now &rarr;
                        </button>
                    </div>
                </div>

                <!-- 7. Sister Reports Cross-Promotion (Frequently Bought Together) -->
                <?php if (!empty($related_reports)): ?>
                    <section class="mr-fbt-wrap">
                        <div style="text-align:center;">
                            <span class="mr-tag-eyebrow" style="background:#FFF3E1; color:#7A1F3D; border:1.5px solid #D9A441;">
                                <i class="fa-solid fa-sparkles" style="color:#D9A441;"></i> Complete Your Cosmic Library
                            </span>
                            <h2 class="mr-landing-h2" style="margin-top:14px; color:#7A1F3D;">Frequently Bought Together</h2>
                            <p class="mr-landing-sub" style="margin:0 auto; color:#554841;">Enhance your astrological clarity with our hand-crafted, publication-grade sister dossiers.</p>
                        </div>

                        <!-- 1-Click Best Buy Bundle Deal Box -->
                        <?php if ($bundles_active && !empty($tier_trio['enabled']) && !$has_token): ?>
                            <div style="background: linear-gradient(135deg, #FFF9F0 0%, #FFF2DF 100%); border: 2px dashed #D9A441; border-radius: 18px; padding: 20px 28px; margin: 26px auto 32px auto; max-width: 860px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: 0 8px 24px rgba(217, 164, 65, 0.15);">
                                <div style="text-align: left; max-width: 540px;">
                                    <span style="background: linear-gradient(135deg, #F58220 0%, #E6680D 100%); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-crown"></i> <?php echo esc_html($tier_trio['badge'] ?? 'BEST BUY VIP BUNDLE'); ?>
                                    </span>
                                    <h3 style="margin: 8px 0 4px 0; font-size: 18px; font-weight: 800; color: #7A1F3D;">
                                        <?php echo esc_html($trio_title); ?>
                                    </h3>
                                    <p style="margin: 0; font-size: 13px; color: #554841; line-height: 1.45;">
                                        Unlock this report + 2 sister dossiers + <strong><?php echo esc_html($trio_cred); ?> Niyati AI Questions</strong> for only <strong>₹<?php echo number_format($trio_price); ?></strong> (Save over 70%).
                                    </p>
                                </div>
                                <div>
                                    <button type="button" class="mr-fbt-cta-btn mr-btn-bundle-order"
                                            data-slug="<?php echo esc_attr($report_slug); ?>"
                                            data-order-type="bundle"
                                            data-bundle-tier="trio"
                                            data-price="<?php echo esc_attr($trio_price); ?>"
                                            data-bundle-title="<?php echo esc_attr($trio_title); ?>"
                                            data-chat-credits="<?php echo esc_attr($trio_cred); ?>"
                                            data-bundle-reports="<?php echo esc_attr(wp_json_encode($trio_reports)); ?>"
                                            data-title="<?php echo esc_attr($trio_title); ?>"
                                            style="padding: 13px 28px !important; font-size: 14px !important; box-shadow: 0 6px 20px rgba(245, 130, 32, 0.4) !important; cursor:pointer;">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Order Bundle (₹<?php echo number_format($trio_price); ?>) &rarr;
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mr-fbt-grid">
                            <?php 
                            foreach ($related_reports as $rel): 
                                $rel_slug = $rel['report_slug'];
                                $rel_img = !empty($all_catalog_cards[$rel_slug]['image_url']) ? $all_catalog_cards[$rel_slug]['image_url'] : (MR_PLUGIN_URL . 'assets/images/reports/' . $rel_slug . '.png');
                                $rel_sale_price = isset($all_catalog_cards[$rel_slug]['price_sale']) ? $all_catalog_cards[$rel_slug]['price_sale'] : $rel['price_sale'];
                                $rel_reg_price  = isset($all_catalog_cards[$rel_slug]['price_regular']) ? $all_catalog_cards[$rel_slug]['price_regular'] : $rel['price_regular'];
                                $rel_discount   = isset($all_catalog_cards[$rel_slug]['discount_text']) ? $all_catalog_cards[$rel_slug]['discount_text'] : ($rel['discount_text'] ?? '');
                                $rel_pages      = isset($all_catalog_cards[$rel_slug]['pages_tag']) ? $all_catalog_cards[$rel_slug]['pages_tag'] : $rel['pages'];
                                $rel_url        = home_url('/reports/' . $rel['page_slug'] . '/');
                            ?>
                                <div class="mr-fbt-card">
                                    <!-- Artwork Cover with Page Count Badge -->
                                    <a href="<?php echo esc_url($rel_url); ?>" class="mr-fbt-img-link">
                                        <div class="mr-fbt-img-wrap">
                                            <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel['name']); ?>" loading="lazy" decoding="async" width="160" height="200" class="mr-fbt-img">
                                            <?php if (!empty($rel_pages)): ?>
                                                <span class="mr-fbt-badge">
                                                    <i class="fa-regular fa-file-lines"></i> <?php echo esc_html($rel_pages); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </a>

                                    <!-- Content -->
                                    <div class="mr-fbt-content">
                                        <h3 class="mr-fbt-title">
                                            <a href="<?php echo esc_url($rel_url); ?>">
                                                <?php echo esc_html($rel['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="mr-fbt-desc">
                                            <?php echo esc_html(wp_trim_words($rel['hero_sub'], 14)); ?>
                                        </p>

                                        <!-- Footer with Price & CTA -->
                                        <div class="mr-fbt-footer">
                                            <div class="mr-fbt-price-col">
                                                <div class="mr-fbt-price-main">
                                                    <span class="mr-fbt-symbol">₹</span>
                                                    <span class="mr-fbt-val"><?php echo number_format($rel_sale_price); ?></span>
                                                </div>
                                                <?php if ($rel_reg_price > $rel_sale_price): ?>
                                                    <span class="mr-fbt-strike">₹<?php echo number_format($rel_reg_price); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($rel_discount)): ?>
                                                    <span class="mr-fbt-discount-tag"><?php echo esc_html($rel_discount); ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <a href="<?php echo esc_url($rel_url); ?>" class="mr-fbt-cta-btn">
                                                <span>View Report</span> <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 8. Real Client Testimonials -->
                <?php if (!empty($data['testimonials'])): ?>
                    <section class="mr-section-wrap" style="padding-top:0;">
                        <div style="text-align:center;">
                            <span class="mr-tag-eyebrow"><i class="fa-solid fa-users"></i> Client Experiences</span>
                            <h2 class="mr-landing-h2" style="margin-top:14px;">What Seekers Are Saying</h2>
                            <p class="mr-landing-sub" style="margin:0 auto;">Verified reviews from clients who consulted MeraRashifal.</p>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-top:38px;">
                            <?php foreach ($data['testimonials'] as $rev): ?>
                                <div style="background:#FFFFFF; border:1px solid rgba(217, 164, 65, 0.25); border-radius:20px; padding:30px; box-shadow:0 8px 25px rgba(0,0,0,0.04);">
                                    <div style="color:#D9A441; font-size:16px; margin-bottom:14px;">
                                        <?php echo str_repeat('★', intval($rev['rating'] ?? 5)); ?>
                                    </div>
                                    <p style="font-size:15px; color:#3A312D; font-style:italic; line-height:1.7; margin:0 0 16px 0;">
                                        &ldquo;<?php echo esc_html($rev['quote']); ?>&rdquo;
                                    </p>
                                    <div style="font-size:14px; font-weight:700; color:#7A1F3D;"><?php echo esc_html($rev['name']); ?></div>
                                    <div style="font-size:12.5px; color:#6B5E57;"><?php echo esc_html($rev['loc']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 9. Interactive FAQ Accordion -->
                <?php if (!empty($data['faqs'])): ?>
                    <section class="mr-section-wrap" style="padding-top:0;">
                        <div style="text-align:center;">
                            <span class="mr-tag-eyebrow"><i class="fa-solid fa-circle-question"></i> Common Questions</span>
                            <h2 class="mr-landing-h2" style="margin-top:14px;">Frequently Asked Questions</h2>
                            <p class="mr-landing-sub" style="margin:0 auto;">Everything you need to know about your personalized reading.</p>
                        </div>

                        <div class="mr-faq-list">
                            <?php foreach ($data['faqs'] as $idx => $faq): ?>
                                <div class="mr-faq-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                                    <div class="mr-faq-q">
                                        <span><?php echo esc_html($faq['q']); ?></span>
                                        <span class="mr-faq-icon">+</span>
                                    </div>
                                    <div class="mr-faq-a" style="<?php echo ($idx === 0) ? 'display:block;' : ''; ?>">
                                        <?php echo esc_html($faq['a']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 10. Final Bottom Conversion Box -->
                <div style="background: radial-gradient(120% 160% at 50% 100%, #FFF5E7 0%, #FFFFFF 60%, #FDF8F1 100%); border: 2px solid rgba(217, 164, 65, 0.4); border-radius: 28px; padding: 60px 32px; text-align: center; margin: 40px 0 80px 0; box-shadow: 0 16px 45px rgba(217, 164, 65, 0.16);">
                    <h2 class="mr-landing-h2" style="font-size:clamp(28px, 3.8vw, 44px) !important;">
                        Your Stars Have Already Charted The Path.<br>
                        <span class="mr-landing-gold-highlight">Discover What The Cosmic Blueprint Holds For You.</span>
                    </h2>
                    <p class="mr-landing-sub" style="margin: 14px auto 28px auto;">
                        Unlock your comprehensive <?php echo esc_html($report_pages); ?> Vedic roadmap and read it in minutes.
                    </p>

                    <div>
                        <button type="button" class="mr-btn-hero-primary mr-btn-buy-alacarte" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" data-price="<?php echo esc_attr($data['price_sale']); ?>" style="font-size:17px; padding:18px 46px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Your <?php echo esc_html($data['title_prefix']); ?> (₹<?php echo esc_html($report_price); ?>) &rarr;
                        </button>
                    </div>

                    <div style="margin-top:24px; font-size:13px; color:#6B5E57; display:flex; justify-content:center; align-items:center; gap:22px; flex-wrap:wrap;">
                        <span><i class="fa-solid fa-certificate" style="color:#D9A441;"></i> Reviewed by Vedic Council</span>
                        <span><i class="fa-solid fa-lock" style="color:#D9A441;"></i> 256-Bit SSL Encrypted Checkout</span>
                        <span><i class="fa-solid fa-envelope" style="color:#D9A441;"></i> Instant Email Delivery</span>
                    </div>
                </div>

            </div><!-- .mr-landing-container -->
        </div><!-- .mr-landing-breakout -->

        <!-- Sticky Floating Bottom CTA Bar (Appears on Scroll) -->
        <div class="mr-sticky-cta-bar" id="mr-landing-sticky-bar">
            <div class="mr-sticky-cta-inner">
                <div class="mr-sticky-info">
                    <div class="mr-sticky-title"><?php echo esc_html($data['name']); ?></div>
                    <div class="mr-sticky-meta"><?php echo esc_html($report_pages); ?> &bull; Authentic Shastric Analysis</div>
                </div>
                <div class="mr-sticky-action-center">
                    <?php if ($has_token): ?>
                        <button type="button" class="mr-btn-hero-primary mr-btn-generate-direct mr-sticky-cta-btn" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" data-price="<?php echo esc_attr($data['price_sale']); ?>">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Report (1 Token Available) &rarr;
                        </button>
                    <?php elseif ($bundles_active && !empty($tier_trio['enabled'])): ?>
                        <button type="button" class="mr-btn-hero-primary mr-btn-buy-alacarte mr-btn-bundle-order mr-sticky-cta-btn" 
                                data-slug="<?php echo esc_attr($report_slug); ?>" 
                                data-order-type="bundle" 
                                data-bundle-tier="trio" 
                                data-price="<?php echo esc_attr($trio_price); ?>" 
                                data-bundle-title="<?php echo esc_attr($trio_title); ?>" 
                                data-chat-credits="<?php echo esc_attr($trio_cred); ?>" 
                                data-bundle-reports="<?php echo esc_attr(wp_json_encode($trio_reports)); ?>" 
                                data-title="<?php echo esc_attr($trio_title); ?>">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Order Best Buy Suite &bull; ₹<?php echo number_format($trio_price); ?> &rarr;
                        </button>
                    <?php else: ?>
                        <button type="button" class="mr-btn-hero-primary mr-btn-buy-alacarte mr-sticky-cta-btn" data-slug="<?php echo esc_attr($report_slug); ?>" data-title="<?php echo esc_attr($report_title); ?>" data-price="<?php echo esc_attr($data['price_sale']); ?>">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Order Report &bull; ₹<?php echo esc_html($report_price); ?> &rarr;
                        </button>
                    <?php endif; ?>
                </div>
                <div class="mr-sticky-price-wrap">
                    <span class="mr-sticky-price">₹<?php echo esc_html($bundles_active && !empty($tier_trio['enabled']) && !$has_token ? number_format($trio_price) : $report_price); ?></span>
                    <span class="mr-sticky-badge"><i class="fa-solid fa-bolt"></i> Instant Delivery</span>
                </div>
            </div>
        </div>

        <!-- Render Universal Birth Details Modal & Sticky Toast Banner -->
        <?php if (class_exists('MR_Frontend')) {
            echo MR_Frontend::render_details_capture_and_banner_modal($report_title, $report_slug);
            echo MR_Frontend::render_sample_preview_modal();
        } ?>

        <script>
        window.mrCurrentReportContext = {
            slug: '<?php echo esc_js($report_slug); ?>',
            title: '<?php echo esc_js($report_title); ?>'
        };

        (function($) {
            $(document).ready(function() {
                // Interactive Bundle Card Selection Handler
                $(document).on('click', '.mr-bundle-item', function() {
                    var item = $(this);
                    $('.mr-bundle-item').removeClass('active');
                    item.addClass('active');

                    var tier     = item.data('tier');
                    var price    = item.data('price');
                    var credits  = item.data('credits');
                    var title    = item.data('title');
                    var reports  = item.data('reports');
                    var btnLabel = item.data('btn-label');

                    var heroBtn = $('#mr-hero-bundle-btn');
                    if (heroBtn.length) {
                        heroBtn.data('order-type', (tier === 'single' ? 'alacarte' : 'bundle'))
                               .data('bundle-tier', tier)
                               .data('price', price)
                               .data('bundle-title', title)
                               .data('chat-credits', credits)
                               .data('bundle-reports', reports)
                               .data('title', title);
                        heroBtn.attr('data-price', price);
                        heroBtn.attr('data-bundle-tier', tier);
                        heroBtn.attr('data-order-type', (tier === 'single' ? 'alacarte' : 'bundle'));
                        $('#mr-hero-bundle-btn-text').html(btnLabel);
                    }

                    // Sync sticky bar
                    var stickyBtn = $('#mr-landing-sticky-bar .mr-sticky-cta-btn');
                    if (stickyBtn.length) {
                        stickyBtn.data('order-type', (tier === 'single' ? 'alacarte' : 'bundle'))
                                 .data('bundle-tier', tier)
                                 .data('price', price)
                                 .data('bundle-title', title)
                                 .data('chat-credits', credits)
                                 .data('bundle-reports', reports)
                                 .data('title', title);
                        stickyBtn.attr('data-price', price);
                        stickyBtn.attr('data-bundle-tier', tier);
                        stickyBtn.attr('data-order-type', (tier === 'single' ? 'alacarte' : 'bundle'));
                        var stickyText = (tier === 'trio') 
                            ? 'Order Best Buy Suite &bull; ₹' + Number(price).toLocaleString() + ' &rarr;' 
                            : ((tier === 'duo') 
                                ? 'Order Duo Pack &bull; ₹' + Number(price).toLocaleString() + ' &rarr;' 
                                : 'Order Report &bull; ₹' + Number(price).toLocaleString() + ' &rarr;');
                        stickyBtn.html('<i class="fa-solid fa-wand-magic-sparkles"></i> ' + stickyText);
                        $('#mr-landing-sticky-bar .mr-sticky-price').text('₹' + Number(price).toLocaleString());
                    }
                });

                // Order & Bundle Checkout Redirect Handler
                $(document).on('click', '.mr-btn-buy-alacarte, .mr-btn-bundle-order, .mr-fbt-cta-btn', function(e) {
                    if ($(this).is('a')) return;
                    e.preventDefault();
                    var btn = $(this);
                    var slug = btn.data('slug') || btn.attr('data-slug') || '<?php echo esc_js($report_slug); ?>';
                    var tier = btn.data('bundle-tier') || btn.attr('data-bundle-tier') || 'single';
                    var checkoutUrl = '<?php echo esc_js(home_url('/checkout/')); ?>';
                    var sep = (checkoutUrl.indexOf('?') !== -1) ? '&' : '?';
                    window.location.href = checkoutUrl + sep + 'report=' + encodeURIComponent(slug) + '&tier=' + encodeURIComponent(tier);
                });
                // Direct Generation button (for users who already purchased or hold tokens)
                $(document).on('click', '.mr-btn-generate-direct', function(e) {
                    e.preventDefault();
                    var slug = $(this).data('slug') || (window.mrCurrentReportContext && window.mrCurrentReportContext.slug) || '';
                    var title = $(this).data('title') || (window.mrCurrentReportContext && window.mrCurrentReportContext.title) || '';
                    if (typeof window.openDetailsModal === 'function') {
                        window.openDetailsModal(slug, title);
                    } else {
                        var modal = $('#mr-details-modal');
                        if (modal.length) {
                            if (slug) $('#mr_report_slug').val(slug);
                            if (title) $('#mr-modal-report-title').text('Generate ' + title);
                            modal.addClass('is-open').fadeIn(150);
                        }
                    }
                });

                // Interactive FAQ Accordion
                $('.mr-faq-q').on('click', function() {
                    var item = $(this).closest('.mr-faq-item');
                    var answer = item.find('.mr-faq-a');
                    
                    if (item.hasClass('active')) {
                        item.removeClass('active');
                        answer.slideUp(200);
                    } else {
                        $('.mr-faq-item').removeClass('active');
                        $('.mr-faq-a').slideUp(200);
                        item.addClass('active');
                        answer.slideDown(200);
                    }
                });

                // Sticky Bottom Bar appear on scroll past 450px and align above mobile bottom menu
                var stickyBar = $('#mr-landing-sticky-bar');

                function updateStickyBarPosition() {
                    if (!stickyBar.length) return;
                    var bottomMenu = $('.wp-bottom-menu:visible');
                    var isMob = $(window).width() <= 768;
                    var menuH = (bottomMenu.length && bottomMenu.css('display') !== 'none') ? (bottomMenu.outerHeight() || 60) : 0;
                    
                    if (menuH > 0) {
                        stickyBar.css('bottom', menuH + 'px');
                    } else {
                        if ($(window).width() <= 1024 && $('.wp-bottom-menu').length) {
                            stickyBar.css('bottom', '60px');
                            menuH = 60;
                        } else {
                            stickyBar.css('bottom', '0px');
                        }
                    }

                    // On mobile, if sticky bar is visible, smoothly elevate floating Niyati launcher so it never overlaps
                    var niyatiLauncher = $('#niyati-launcher');
                    if (niyatiLauncher.length && isMob) {
                        if (stickyBar.hasClass('visible')) {
                            var barH = stickyBar.outerHeight() || 95;
                            niyatiLauncher.css('bottom', (menuH + barH + 14) + 'px');
                        } else {
                            niyatiLauncher.css('bottom', '');
                        }
                    }
                }

                $(window).on('scroll', function() {
                    if ($(window).scrollTop() > 450) {
                        stickyBar.addClass('visible');
                        $('body').addClass('mr-sticky-active');
                    } else {
                        stickyBar.removeClass('visible');
                        $('body').removeClass('mr-sticky-active');
                    }
                    updateStickyBarPosition();
                });

                $(window).on('resize orientationchange load', updateStickyBarPosition);
                updateStickyBarPosition();
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
}
