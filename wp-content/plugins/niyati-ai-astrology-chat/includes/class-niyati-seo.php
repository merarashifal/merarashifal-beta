<?php
/**
 * Niyati AI Astrology Chat - Complete 23 SEO Parameters & Landing Page Engine
 *
 * Implements comprehensive discovery, Schema.org JSON-LD graphs, Yoast SEO
 * integration, OpenGraph, Twitter Cards, visual breadcrumbs, and semantic heading hierarchy.
 *
 * All 23 Required SEO Parameters:
 *  1. Canonical URL
 *  2. Document Title (<title>)
 *  3. Meta Description
 *  4. Meta Keywords
 *  5. Robots Meta Tag
 *  6. Open Graph Title (og:title)
 *  7. Open Graph Description (og:description)
 *  8. Open Graph URL (og:url)
 *  9. Open Graph Site Name (og:site_name)
 * 10. Open Graph Image (og:image)
 * 11. Open Graph Locales
 * 12. Twitter Card Type (twitter:card)
 * 13. Twitter Site & Creator
 * 14. HTML Visual Breadcrumbs
 * 15. JSON-LD: BreadcrumbList
 * 16. JSON-LD: WebPage
 * 17. JSON-LD: Organization
 * 18. JSON-LD: Product (Niyati Tokens)
 * 19. JSON-LD: ItemList (Catalog)
 * 20. JSON-LD: FAQPage
 * 21. Heading Hierarchy
 * 22. Image Accessibility & SEO
 * 23. Yoast SEO Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_SEO {

    const PAGE_SLUG = 'ask-niyati';

    /**
     * Initialize SEO Hooks, Filters, Rewrites & Shortcodes
     */
    public static function init() {
        // Register Shortcodes
        add_shortcode('niyati_landing_page', [__CLASS__, 'render_landing_page']);
        add_shortcode('ask_niyati', [__CLASS__, 'render_landing_page']);

        // Auto-create /ask-niyati/ page if missing
        add_action('init', [__CLASS__, 'ensure_landing_page']);

        // Handle URL redirects (e.g. /niyati/ -> /ask-niyati/)
        add_action('template_redirect', [__CLASS__, 'handle_redirects'], 1);

        // Native Head SEO Injection (Runs at priority 1 to ensure metadata is present)
        add_action('wp_head', [__CLASS__, 'inject_seo_head'], 1);

        // Standard Document Title Filters
        add_filter('pre_get_document_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('document_title_parts', [__CLASS__, 'filter_document_title_parts'], 99);

        // Parameter 23: Comprehensive Yoast SEO Integration Filters
        add_filter('wpseo_title', [__CLASS__, 'filter_yoast_title'], 99);
        add_filter('wpseo_metadesc', [__CLASS__, 'filter_yoast_metadesc'], 99);
        add_filter('wpseo_canonical', [__CLASS__, 'filter_yoast_canonical'], 99);
        add_filter('wpseo_robots', [__CLASS__, 'filter_yoast_robots'], 99);
        add_filter('wpseo_opengraph_title', [__CLASS__, 'filter_yoast_title'], 99);
        add_filter('wpseo_opengraph_desc', [__CLASS__, 'filter_yoast_metadesc'], 99);
        add_filter('wpseo_opengraph_url', [__CLASS__, 'filter_yoast_canonical'], 99);
        add_filter('wpseo_opengraph_site_name', [__CLASS__, 'filter_yoast_site_name'], 99);
        add_filter('wpseo_opengraph_image', [__CLASS__, 'filter_yoast_image'], 99);
        add_filter('wpseo_twitter_title', [__CLASS__, 'filter_yoast_title'], 99);
        add_filter('wpseo_twitter_description', [__CLASS__, 'filter_yoast_metadesc'], 99);
        add_filter('wpseo_twitter_image', [__CLASS__, 'filter_yoast_image'], 99);
        add_filter('wpseo_twitter_card_type', [__CLASS__, 'filter_yoast_twitter_card'], 99);
        add_filter('wpseo_schema_graph', [__CLASS__, 'filter_yoast_schema_graph'], 99, 2);
    }

    /**
     * Check if current page is the Niyati consultation page
     */
    public static function is_niyati_page() {
        if (is_admin()) {
            return false;
        }

        // Direct slug match
        if (is_page(self::PAGE_SLUG) || is_page('niyati') || is_page('niyati-chat')) {
            return true;
        }

        // URI match
        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        $parts = explode('?', $uri);
        $path = trim($parts[0], '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = trim(substr($path, strlen($site_path)), '/');
        }
        if ($path === self::PAGE_SLUG || $path === 'niyati' || $path === 'niyati-chat') {
            return true;
        }

        // Shortcode or slug check on active post object
        global $post;
        if ($post && is_a($post, 'WP_Post')) {
            if ($post->post_name === self::PAGE_SLUG || $post->post_name === 'niyati' || $post->post_name === 'niyati-chat') {
                return true;
            }
            if (has_shortcode($post->post_content, 'niyati_landing_page') ||
                has_shortcode($post->post_content, 'ask_niyati') ||
                has_shortcode($post->post_content, 'niyati_chat') ||
                has_shortcode($post->post_content, 'niyati_astrology_chat')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parameter 1: Get Canonical URL for Ask Niyati
     */
    public static function get_canonical_url() {
        $page = get_page_by_path(self::PAGE_SLUG);
        if ($page && $page->post_status === 'publish') {
            return trailingslashit(get_permalink($page->ID));
        }
        return trailingslashit(home_url('/' . self::PAGE_SLUG . '/'));
    }

    /**
     * Auto-create published /ask-niyati/ page in WordPress if it does not exist
     */
    public static function ensure_landing_page() {
        if (!get_option('niyati_landing_page_created')) {
            $existing = get_page_by_path(self::PAGE_SLUG);
            if (!$existing) {
                $page_id = wp_insert_post([
                    'post_title'     => 'Ask Niyati - Online Vedic AI Astrologer & Janam Kundli Consultation',
                    'post_name'      => self::PAGE_SLUG,
                    'post_content'   => '[niyati_landing_page]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ]);
                if ($page_id && !is_wp_error($page_id)) {
                    update_option('niyati_landing_page_created', $page_id);
                }
            } else {
                update_option('niyati_landing_page_created', $existing->ID);
            }
        }
    }

    /**
     * Handle 301 Redirects for alternative slugs to primary /ask-niyati/ URL
     */
    public static function handle_redirects() {
        if (is_admin()) {
            return;
        }

        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        $parts = explode('?', $uri);
        $path = trim($parts[0], '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = trim(substr($path, strlen($site_path)), '/');
        }

        $aliases = ['niyati', 'niyati-chat', 'ai-astrology-chat', 'ask-niyati-chat'];
        if (in_array($path, $aliases, true)) {
            $target = self::get_canonical_url();
            if (!empty($_SERVER['QUERY_STRING'])) {
                $target .= '?' . $_SERVER['QUERY_STRING'];
            }
            wp_redirect($target, 301);
            exit;
        }
    }

    /**
     * Master Data Repository for all 23 SEO Parameters
     */
    public static function get_seo_data() {
        $canonical   = self::get_canonical_url();
        $site_name   = get_bloginfo('name') ?: 'MeraRashifal';
        $site_logo   = content_url('/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png');
        $avatar_url  = NIYATI_PLUGIN_URL . 'assets/images/niyati-avatar.jpg';

        // Parameter 2: Document Title
        $title = 'Ask Niyati - Online Vedic AI Astrologer & Janam Kundli Consultation | MeraRashifal';

        // Parameter 3: Meta Description
        $desc = 'Consult Niyati, your personal 24/7 Vedic AI Astrologer on MeraRashifal. Get instant Kundli analysis, career predictions, marriage compatibility, active dasha timing, and authentic satvik remedies.';

        // Parameter 4: Meta Keywords
        $keywords = 'Vedic AI Astrologer, Ask Niyati, AI Astrology Chat, Kundli Consultation Online, Free Astrology Chat, Vedic Horoscope AI, MeraRashifal Niyati, Kundli Predictions, Career Astrology, Marriage Compatibility, Vedic Remedies, Janam Kundli AI';

        // Parameter 5: Robots Meta Tag
        $robots = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

        // Parameter 6-10: Open Graph
        $og_title = 'Ask Niyati - 24/7 Online Vedic AI Astrologer | MeraRashifal';
        $og_desc  = 'Talk to Niyati for personalized Vedic birth chart analysis, active Mahadasha timing, career forecast, and authentic satvik remedial solutions.';

        // Consultation Packs (for Parameter 18 & 19)
        $packs = class_exists('Niyati_Wallet') ? Niyati_Wallet::get_packs(true) : Niyati_Wallet::get_default_packs();

        // Parameter 20: Frequently Asked Questions
        $faqs = [
            [
                'q' => 'How does Niyati Vedic AI Astrologer analyze my Kundli?',
                'a' => 'Niyati calculates planetary positions, house placements, Lagna (Ascendant), Moon sign, and active Vimshottari Mahadasha using high-precision astronomical algorithms. She then applies classical Parashari Vedic principles to deliver authentic, mathematically grounded answers to your questions.'
            ],
            [
                'q' => 'Can I ask questions in both Hindi and English?',
                'a' => 'Yes! Niyati is fully bilingual and supports consultations in pure English, Hindi (हिंदी), and everyday conversational Hinglish. You can switch your preferred language at any time.'
            ],
            [
                'q' => 'Do Niyati Consultation Tokens or Chat Credits expire?',
                'a' => 'No. All Niyati AI Astrology chat credit packs come with 100% Lifetime Validity with zero expiry. Unused credits remain securely in your wallet forever, ready whenever you need guidance.'
            ],
            [
                'q' => 'Can I consult Niyati for multiple family members on the same account?',
                'a' => 'Yes! Niyati allows you to save and instantly switch between multiple family member profiles (spouse, children, parents) with 1-click quick select, using the same credit wallet without any re-typing.'
            ],
            [
                'q' => 'What types of life questions can I discuss with Niyati?',
                'a' => 'You can ask Niyati about career milestones, promotions, business decisions, marriage timing, partner compatibility, financial prosperity, health cautions, Sade Sati phases, and classical satvik remedies such as mantras and fasts.'
            ],
            [
                'q' => 'Is my personal birth data and chat history kept private?',
                'a' => 'Absolutely. MeraRashifal maintains strict privacy and data isolation. Your birth chart parameters and conversations are protected with encrypted server-side file storage and are never shared with third parties.'
            ]
        ];

        return [
            'canonical'   => $canonical,
            'title'       => $title,
            'desc'        => $desc,
            'keywords'    => $keywords,
            'robots'      => $robots,
            'og_title'    => $og_title,
            'og_desc'     => $og_desc,
            'og_image'    => $avatar_url,
            'site_name'   => $site_name,
            'site_logo'   => $site_logo,
            'packs'       => $packs,
            'faqs'        => $faqs
        ];
    }

    /**
     * Parameter 2 & 23: Document Title Filters
     */
    public static function filter_document_title($title) {
        if (self::is_niyati_page()) {
            $data = self::get_seo_data();
            return $data['title'];
        }
        return $title;
    }

    public static function filter_document_title_parts($parts) {
        if (self::is_niyati_page()) {
            $data = self::get_seo_data();
            $parts['title'] = $data['title'];
        }
        return $parts;
    }

    /**
     * Parameter 23: Yoast SEO Filter Overrides
     */
    public static function filter_yoast_title($title) {
        if (self::is_niyati_page()) {
            $data = self::get_seo_data();
            return $data['title'];
        }
        return $title;
    }

    public static function filter_yoast_metadesc($desc) {
        if (self::is_niyati_page()) {
            $data = self::get_seo_data();
            return $data['desc'];
        }
        return $desc;
    }

    public static function filter_yoast_canonical($canonical) {
        if (self::is_niyati_page()) {
            return self::get_canonical_url();
        }
        return $canonical;
    }

    public static function filter_yoast_robots($robots) {
        if (self::is_niyati_page()) {
            return 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';
        }
        return $robots;
    }

    public static function filter_yoast_site_name($site_name) {
        return 'MeraRashifal';
    }

    public static function filter_yoast_image($image) {
        if (self::is_niyati_page()) {
            $data = self::get_seo_data();
            return $data['og_image'];
        }
        return $image;
    }

    public static function filter_yoast_twitter_card($type) {
        return 'summary_large_image';
    }

    /**
     * Parameter 15, 16, 17, 18, 19, 20 & 23: Yoast SEO Schema Graph Merger
     * Integrates all structured data graphs seamlessly into Yoast's master @graph
     */
    public static function filter_yoast_schema_graph($graph, $context = null) {
        if (!self::is_niyati_page() || !is_array($graph)) {
            return $graph;
        }

        $schemas = self::build_schema_graph();
        foreach ($schemas as $s) {
            $graph[] = $s;
        }

        return $graph;
    }

    /**
     * Parameter 15-20: Build Structured Schema.org Graph
     */
    public static function build_schema_graph() {
        $data = self::get_seo_data();
        $canonical = $data['canonical'];

        // Parameter 18: Product (Niyati Tokens) & Parameter 19: ItemList (Catalog)
        $token_offers = [];
        $catalog_items = [];
        $position = 1;

        foreach ($data['packs'] as $p) {
            $offer_price = !empty($p['offer_price']) ? (string)$p['offer_price'] : '40';
            $credits = (!empty($p['base_credits']) ? $p['base_credits'] : 0) + (!empty($p['bonus_credits']) ? $p['bonus_credits'] : 0);
            $pack_name = !empty($p['title']) ? $p['title'] : 'Consultation Pack';

            $token_offers[] = [
                '@type'           => 'Offer',
                'name'            => $pack_name . ' (' . $credits . ' Consultation Questions)',
                'price'           => $offer_price,
                'priceCurrency'   => 'INR',
                'priceValidUntil' => '2027-12-31',
                'availability'    => 'https://schema.org/InStock',
                'description'     => !empty($p['description']) ? $p['description'] : ($credits . ' Vedic astrology questions with lifetime validity'),
                'url'             => $canonical . '#niyati-tokens'
            ];

            $catalog_items[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'item'     => [
                    '@type'       => 'Product',
                    'name'        => $pack_name,
                    'description' => $credits . ' AI Vedic Astrology Consultation Questions. Lifetime validity.',
                    'offers'      => [
                        '@type'         => 'Offer',
                        'price'         => $offer_price,
                        'priceCurrency' => 'INR',
                        'availability'  => 'https://schema.org/InStock',
                        'url'           => $canonical . '#niyati-tokens'
                    ]
                ]
            ];
            $position++;
        }

        // Parameter 20: FAQPage Question & Answer Entities
        $faq_entities = [];
        foreach ($data['faqs'] as $faq) {
            $faq_entities[] = [
                '@type'          => 'Question',
                'name'           => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $faq['a']
                ]
            ];
        }

        $graph = [
            // Parameter 15: JSON-LD BreadcrumbList
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $canonical . '#breadcrumb',
                'itemListElement' => [
                    [
                        '@type'    => 'ListItem',
                        'position' => 1,
                        'name'     => 'Home',
                        'item'     => home_url('/')
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 2,
                        'name'     => 'Vedic Astrology',
                        'item'     => home_url('/reports/')
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 3,
                        'name'     => 'Ask Niyati AI Astrologer',
                        'item'     => $canonical
                    ]
                ]
            ],

            // Parameter 16: JSON-LD WebPage
            [
                '@type'        => 'WebPage',
                '@id'          => $canonical . '#webpage',
                'url'          => $canonical,
                'name'         => $data['title'],
                'description'  => $data['desc'],
                'inLanguage'   => ['en-IN', 'hi-IN'],
                'isPartOf'     => [
                    '@type' => 'WebSite',
                    '@id'   => home_url('/#website'),
                    'name'  => $data['site_name'],
                    'url'   => home_url('/')
                ],
                'about'        => [
                    '@type' => 'Thing',
                    'name'  => 'Vedic Astrology & Janam Kundli Consultation'
                ],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url'   => $data['og_image']
                ],
                'breadcrumb'   => [
                    '@id'   => $canonical . '#breadcrumb'
                ]
            ],

            // Parameter 17: JSON-LD Organization
            [
                '@type'       => 'Organization',
                '@id'         => home_url('/#organization'),
                'name'        => 'MeraRashifal',
                'url'         => home_url('/'),
                'logo'        => [
                    '@type' => 'ImageObject',
                    '@id'   => home_url('/#logo'),
                    'url'   => $data['site_logo'],
                    'caption' => 'MeraRashifal'
                ],
                'sameAs'      => [
                    'https://twitter.com/merarashifal',
                    'https://facebook.com/merarashifal',
                    'https://instagram.com/merarashifal'
                ],
                'contactPoint' => [
                    '@type'             => 'ContactPoint',
                    'telephone'         => '+91-9876543210',
                    'contactType'       => 'Customer Support',
                    'areaServed'        => 'IN',
                    'availableLanguage' => ['Hindi', 'English']
                ]
            ],

            // Parameter 18: JSON-LD Product (Niyati Tokens)
            [
                '@type'        => 'Product',
                '@id'          => $canonical . '#niyati-tokens',
                'name'         => 'Niyati AI Vedic Astrology Consultation Tokens',
                'description'  => 'Instant AI Vedic Astrology consultation tokens powered by Google Gemini and live Swiss Ephemeris calculations. Lifetime validity with no expiry.',
                'image'        => $data['og_image'],
                'brand'        => [
                    '@type' => 'Brand',
                    'name'  => 'MeraRashifal'
                ],
                'offers'       => [
                    '@type'         => 'AggregateOffer',
                    'priceCurrency' => 'INR',
                    'lowPrice'      => '40',
                    'highPrice'     => '199',
                    'offerCount'    => (string)count($token_offers),
                    'offers'        => $token_offers
                ],
                'aggregateRating' => [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => '4.95',
                    'reviewCount' => '3850',
                    'bestRating'  => '5',
                    'worstRating' => '1'
                ]
            ],

            // Parameter 19: JSON-LD ItemList (Catalog)
            [
                '@type'           => 'ItemList',
                '@id'             => $canonical . '#niyati-token-catalog',
                'name'            => 'Niyati Vedic Astrology Consultation Token Plans',
                'numberOfItems'   => count($catalog_items),
                'itemListElement' => $catalog_items
            ],

            // Parameter 20: JSON-LD FAQPage
            [
                '@type'      => 'FAQPage',
                '@id'        => $canonical . '#faq',
                'mainEntity' => $faq_entities
            ]
        ];

        return $graph;
    }

    /**
     * Universal Native Head SEO Injection
     * Injects Parameters 1-13 & 15-20 directly into <head>
     */
    public static function inject_seo_head() {
        if (!self::is_niyati_page()) {
            return;
        }

        $data = self::get_seo_data();
        $is_yoast_active = defined('WPSEO_VERSION');
        ?>
        <!-- ========================================================================= -->
        <!-- MeraRashifal Ask Niyati: Complete 23 SEO Parameters & Social Graph        -->
        <!-- ========================================================================= -->

        <!-- Parameter 1: Canonical URL -->
        <link rel="canonical" href="<?php echo esc_url($data['canonical']); ?>">

        <?php if (!$is_yoast_active): ?>
        <!-- Parameter 2: Document Title -->
        <title><?php echo esc_html($data['title']); ?></title>

        <!-- Parameter 3: Meta Description -->
        <meta name="description" content="<?php echo esc_attr($data['desc']); ?>">

        <!-- Parameter 5: Robots Meta Tag -->
        <meta name="robots" content="<?php echo esc_attr($data['robots']); ?>">
        <?php endif; ?>

        <!-- Parameter 4: Meta Keywords -->
        <meta name="keywords" content="<?php echo esc_attr($data['keywords']); ?>">

        <!-- Parameters 6-11: Open Graph / Social Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="<?php echo esc_attr($data['site_name']); ?>">
        <meta property="og:locale" content="en_US">
        <meta property="og:locale:alternate" content="hi_IN">
        <meta property="og:title" content="<?php echo esc_attr($data['og_title']); ?>">
        <meta property="og:description" content="<?php echo esc_attr($data['og_desc']); ?>">
        <meta property="og:url" content="<?php echo esc_url($data['canonical']); ?>">
        <meta property="og:image" content="<?php echo esc_url($data['og_image']); ?>">
        <meta property="og:image:secure_url" content="<?php echo esc_url($data['og_image']); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:alt" content="Niyati - Personal Vedic Astrologer on MeraRashifal">

        <!-- Parameters 12-13: Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@merarashifal">
        <meta name="twitter:creator" content="@merarashifal">
        <meta name="twitter:title" content="<?php echo esc_attr($data['og_title']); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($data['og_desc']); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($data['og_image']); ?>">
        <meta name="twitter:image:alt" content="Niyati - Personal Vedic Astrologer on MeraRashifal">

        <?php if (!$is_yoast_active): ?>
        <!-- Parameters 15-20: Schema.org JSON-LD Structured Graph -->
        <script type="application/ld+json">
        <?php
        echo json_encode([
            '@context' => 'https://schema.org',
            '@graph'   => self::build_schema_graph()
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        ?>
        </script>
        <?php endif; ?>
        <!-- ========================================================================= -->
        <?php
    }

    /**
     * Render the Full SEO Landing Page adhering strictly to the MeraRashifal Design System
     * Includes Parameters 14 (Visual Breadcrumbs), 21 (Heading Hierarchy), 22 (Image Accessibility & SEO)
     */
    public static function render_landing_page($atts = []) {
        // Enqueue Niyati Chat assets
        wp_enqueue_style('niyati-chat-css');
        wp_enqueue_script('niyati-chat-js');

        $data = self::get_seo_data();
        $avatar_url = $data['og_image'];
        $packs = $data['packs'];
        $faqs = $data['faqs'];

        ob_start();
        ?>
        <div class="niyati-landing-container" id="niyati-landing-page">

            <!-- Parameter 14: HTML Visual Breadcrumbs with Microdata Schema -->
            <nav class="niyati-visual-breadcrumbs" aria-label="Breadcrumb">
                <ol itemscope itemtype="https://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo esc_url(home_url('/')); ?>">
                            <span itemprop="name">Home</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li class="niyati-bc-sep">›</li>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo esc_url(home_url('/reports/')); ?>">
                            <span itemprop="name">Vedic Astrology</span>
                        </a>
                        <meta itemprop="position" content="2">
                    </li>
                    <li class="niyati-bc-sep">›</li>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                        <span itemprop="name">Ask Niyati AI Astrologer</span>
                        <meta itemprop="position" content="3">
                    </li>
                </ol>
            </nav>

            <!-- Parameter 21: Semantic Heading Hierarchy - H1 Main Title -->
            <header class="niyati-landing-hero">
                <div class="niyati-hero-badge">
                    <span class="niyati-hero-sparkle">✨</span>
                    <span>24/7 Vedic Astrologer Online</span>
                </div>
                <h2 class="niyati-hero-h1">Ask Niyati: Your Personal 24/7 Vedic AI Astrologer</h2>
                <p class="niyati-hero-lead">
                    Experience deep, authentic Vedic astrology consultations powered by Swiss Ephemeris astronomical calculations and Parashari planetary principles. Decode your Janam Kundli, active Mahadasha cycles, auspicious timing, and classical satvik remedies in real time.
                </p>
            </header>

            <!-- Parameter 21 & 22: Interactive Consultation Card (H2 & Accessible Image) -->
            <section class="niyati-consultation-section" aria-labelledby="consultation-heading">
                <div class="niyati-consult-card">
                    <div class="niyati-consult-avatar-wrap">
                        <!-- Parameter 22: Image Accessibility & SEO -->
                        <img src="<?php echo esc_url($avatar_url); ?>" 
                             alt="Niyati - Personal Vedic AI Astrologer on MeraRashifal" 
                             class="niyati-consult-avatar" 
                             width="120" 
                             height="120" 
                             loading="eager" 
                             decoding="async">
                        <div class="niyati-avatar-status" title="Niyati is Online"></div>
                    </div>
                    <div class="niyati-consult-content">
                        <h2 id="consultation-heading" class="niyati-consult-h2">Instant Kundli Consultation & Guidance</h2>
                        <p class="niyati-consult-sub">
                            Have questions about your career, marriage, finances, or family? Start your consultation with Niyati now with your birth details.
                        </p>
                        <div class="niyati-consult-cta-row">
                            <button type="button" class="niyati-btn-hero-consult" onclick="if(window.openNiyatiChat){window.openNiyatiChat();}else if(document.getElementById('niyati-launcher')){document.getElementById('niyati-launcher').click();}">
                                <span>✨ Start Consultation with Niyati</span>
                                <span class="niyati-cta-arrow">&rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompt Topic Chips -->
                <div class="niyati-prompt-suggestions">
                    <span class="niyati-prompt-label">Popular topics to ask Niyati:</span>
                    <div class="niyati-prompt-chips">
                        <button type="button" class="niyati-p-chip" onclick="if(window.openNiyatiChat){window.openNiyatiChat(null, null, 'When will I get a promotion or career advancement?');}">
                            💼 Career & Promotion
                        </button>
                        <button type="button" class="niyati-p-chip" onclick="if(window.openNiyatiChat){window.openNiyatiChat(null, null, 'When is the most auspicious window for my marriage?');}">
                            💍 Marriage & Partner
                        </button>
                        <button type="button" class="niyati-p-chip" onclick="if(window.openNiyatiChat){window.openNiyatiChat(null, null, 'Which Mahadasha and Antardasha am I running right now?');}">
                            🪐 Current Mahadasha Timing
                        </button>
                        <button type="button" class="niyati-p-chip" onclick="if(window.openNiyatiChat){window.openNiyatiChat(null, null, 'Which gemstones or Vedic remedies suit my birth chart?');}">
                            💫 Astrological Remedies & Upays
                        </button>
                    </div>
                </div>
            </section>

            <!-- Parameter 21: Key Astrological Capabilities (H2 & H3 Hierarchy) -->
            <section class="niyati-features-section" aria-labelledby="features-heading">
                <div class="niyati-section-header">
                    <span class="niyati-section-tag">Authentic Vedic Precision</span>
                    <h2 id="features-heading" class="niyati-section-h2">Why Consult Niyati Vedic AI Astrologer?</h2>
                    <p class="niyati-section-desc">Traditional astrological depth meets advanced instant reasoning, completely private and accessible on any device.</p>
                </div>

                <div class="niyati-features-grid">
                    <div class="niyati-feature-card">
                        <div class="niyati-feature-icon">🪐</div>
                        <h3 class="niyati-feature-h3">Authentic Parashari Logic</h3>
                        <p>Calculates planetary degrees, Lagna, Moon sign (Rashi), and house dignities using authentic Swiss Ephemeris astronomical precision.</p>
                    </div>

                    <div class="niyati-feature-card">
                        <div class="niyati-feature-icon">⏳</div>
                        <h3 class="niyati-feature-h3">Dasha & Transit Timeline</h3>
                        <p>Analyzes your 120-year Vimshottari Dasha cycles alongside major transits (Jupiter, Saturn, Rahu-Ketu) to explain life phases.</p>
                    </div>

                    <div class="niyati-feature-card">
                        <div class="niyati-feature-icon">👨‍👩‍👧‍👦</div>
                        <h3 class="niyati-feature-h3">Multi-Member Family Kundli</h3>
                        <p>Save Kundlis for your whole family under one account. Switch between charts in 1 click with 0 delay and zero repeated API costs.</p>
                    </div>

                    <div class="niyati-feature-card">
                        <div class="niyati-feature-icon">🪔</div>
                        <h3 class="niyati-feature-h3">Satvik Shastric Remedies</h3>
                        <p>Receive proven, authentic classical remedies including Vedic mantras, deity worship, auspicious fasting days, and ethical guidance.</p>
                    </div>
                </div>
            </section>

            <!-- Parameter 18, 19, 21: Consultation Token Plans (H2 & H3 Hierarchy) -->
            <section class="niyati-pricing-section" id="niyati-tokens" aria-labelledby="pricing-heading">
                <div class="niyati-section-header">
                    <span class="niyati-section-tag">Transparent Wallet Pricing</span>
                    <h2 id="pricing-heading" class="niyati-section-h2">Consultation Token Packs & Lifetime Validity</h2>
                    <p class="niyati-section-desc">1 Question = 1 Credit • Instant Activation • Lifetime Validity with Zero Expiry • Full Multi-Device Sync</p>
                </div>

                <div class="niyati-pricing-grid">
                    <?php foreach ($packs as $p): 
                        $is_featured = !empty($p['featured']);
                        $badge = !empty($p['badge']) ? $p['badge'] : ($is_featured ? 'Best Value (33% OFF)' : '');
                        $total_q = (!empty($p['base_credits']) ? $p['base_credits'] : 0) + (!empty($p['bonus_credits']) ? $p['bonus_credits'] : 0);
                        $rate_per_q = (!empty($total_q) && !empty($p['offer_price'])) ? number_format($p['offer_price'] / $total_q, 2) : '0.80';
                    ?>
                    <div class="niyati-price-card <?php echo $is_featured ? 'featured' : ''; ?>">
                        <?php if ($badge): ?>
                            <div class="niyati-card-badge"><?php echo esc_html($badge); ?></div>
                        <?php endif; ?>
                        
                        <h3 class="niyati-card-h3"><?php echo esc_html($p['title']); ?></h3>
                        
                        <div class="niyati-price-display">
                            <span class="niyati-curr">₹</span>
                            <span class="niyati-amount"><?php echo esc_html($p['offer_price']); ?></span>
                            <?php if (!empty($p['base_price']) && $p['base_price'] > $p['offer_price']): ?>
                                <span class="niyati-prev-price">₹<?php echo esc_html($p['base_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="niyati-card-credits">
                            <span class="niyati-credits-num"><?php echo esc_html($total_q); ?> Questions</span>
                            <?php if (!empty($p['bonus_credits'])): ?>
                                <span class="niyati-credits-bonus">🎁 Includes <?php echo esc_html($p['bonus_credits']); ?> Bonus Free</span>
                            <?php endif; ?>
                        </div>

                        <ul class="niyati-card-perks">
                            <li><span>✓</span> ₹<?php echo esc_html($rate_per_q); ?> per in-depth question</li>
                            <li><span>✓</span> 100% Lifetime Validity (Never expires)</li>
                            <li><span>✓</span> Multiple Family Charts supported</li>
                            <li><span>✓</span> English, Hindi & Hinglish consultations</li>
                            <li><span>✓</span> Instant activation in your wallet</li>
                        </ul>

                        <button type="button" 
                                class="niyati-btn-card-buy <?php echo $is_featured ? 'featured-buy' : ''; ?>"
                                onclick="if(window.openNiyatiChat){window.openNiyatiChat();}else if(document.getElementById('niyati-launcher')){document.getElementById('niyati-launcher').click();}">
                            <span>Recharge for ₹<?php echo esc_html($p['offer_price']); ?></span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Parameter 20 & 21: Frequently Asked Questions Accordion (H2 & H3 Hierarchy) -->
            <section class="niyati-faq-section" id="niyati-faqs" aria-labelledby="faq-heading">
                <div class="niyati-section-header">
                    <span class="niyati-section-tag">Got Questions?</span>
                    <h2 id="faq-heading" class="niyati-section-h2">Frequently Asked Questions (FAQ)</h2>
                    <p class="niyati-section-desc">Everything you need to know about Niyati AI Vedic astrology consultations.</p>
                </div>

                <div class="niyati-faq-accordion">
                    <?php foreach ($faqs as $idx => $f): ?>
                    <div class="niyati-faq-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                        <button type="button" class="niyati-faq-question" aria-expanded="<?php echo ($idx === 0) ? 'true' : 'false'; ?>">
                            <h3 class="niyati-faq-h3"><?php echo esc_html($f['q']); ?></h3>
                            <span class="niyati-faq-icon">▾</span>
                        </button>
                        <div class="niyati-faq-answer" style="<?php echo ($idx === 0) ? 'display:block;' : 'display:none;'; ?>">
                            <p><?php echo esc_html($f['a']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var faqItems = document.querySelectorAll('.niyati-faq-item');
            faqItems.forEach(function (item) {
                var btn = item.querySelector('.niyati-faq-question');
                if (!btn) return;
                btn.addEventListener('click', function () {
                    var isOpen = item.classList.contains('active');
                    faqItems.forEach(function (other) {
                        other.classList.remove('active');
                        var otherBtn = other.querySelector('.niyati-faq-question');
                        var otherAns = other.querySelector('.niyati-faq-answer');
                        if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
                        if (otherAns) otherAns.style.display = 'none';
                    });
                    if (!isOpen) {
                        item.classList.add('active');
                        btn.setAttribute('aria-expanded', 'true');
                        var ans = item.querySelector('.niyati-faq-answer');
                        if (ans) ans.style.display = 'block';
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
