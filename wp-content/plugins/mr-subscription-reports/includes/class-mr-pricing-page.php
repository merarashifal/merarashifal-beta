<?php
/**
 * MeraRashifal Pricing Page & Shortcode Engine (High-Conversion Promo Edition)
 * Shortcodes: [mr_pricing], [mr_pricing_table], [mr_pricing_plans]
 *
 * Sequence:
 * 1. Compact Hero with Quick Switcher Tabs
 * 2. Niyati AI Chat Credits & Tokens (Synced with Niyati_Wallet)
 * 3. Individual Astrological Reports (3x3 Matrix, #FDF6E3 background, Category Tabs, Synced with Catalog)
 * 4. Report Combos & Value Bundles (Save 90%+, Synced with MR_Bundles)
 * 5. Astrology Subscriptions & Memberships (Vedic Membership Plans, Synced with MR_Packs)
 * 6. Trust Strip & FAQs
 *
 * Preserves Elementor & Astra Theme Header & Footer with zero style bleed (Tailwind preflight disabled).
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Pricing_Page {

    public static function init() {
        // Register Shortcodes
        add_shortcode('mr_pricing', [__CLASS__, 'render_shortcode']);
        add_shortcode('mr_pricing_table', [__CLASS__, 'render_shortcode']);
        add_shortcode('mr_pricing_plans', [__CLASS__, 'render_shortcode']);

        // Head metadata injection when viewing the /pricing/ page
        add_action('wp_head', [__CLASS__, 'inject_pricing_seo_head'], 1);
    }

    /**
     * Check if viewing the dedicated /pricing/ page
     */
    public static function is_pricing_page() {
        if (is_admin()) {
            return false;
        }
        global $post;
        if ($post && is_a($post, 'WP_Post')) {
            if ($post->post_name === 'pricing' || has_shortcode($post->post_content, 'mr_pricing') || has_shortcode($post->post_content, 'mr_pricing_table')) {
                return true;
            }
        }
        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        return (strpos($uri, 'pricing') !== false);
    }

    /**
     * Inject SEO Meta & JSON-LD into <head> for the pricing page
     */
    public static function inject_pricing_seo_head() {
        if (!self::is_pricing_page()) {
            return;
        }

        $canonical_url   = home_url('/pricing/');
        $site_name       = get_bloginfo('name') ?: 'MeraRashifal';
        $og_image_url    = content_url('/uploads/2026/11/Mera-Rashifal-2027_-Himalayan-Zodiac-Sunrise.png');

        $meta_title       = 'MeraRashifal Pricing | Vedic Astrology Reports, Bundles, Subscriptions & Niyati AI Chat Tokens';
        $meta_description = 'Transparent pricing for MeraRashifal Vedic astrology reports from ₹99, value bundle packages from ₹192, membership subscriptions, and Niyati AI chat tokens from ₹40. 100% Swiss Ephemeris accuracy.';
        $meta_keywords    = 'vedic astrology pricing, astrology reports price, astrology bundle reports, astrology subscription plans, buy kundli report bundle, kundali matching cost, niyati ai chat tokens, horoscope report price, palm reading price, janam kundali master report price, dasha timing report, merarashifal pricing';

        $schema_json = self::get_schema_json();
        ?>
        <!-- MeraRashifal Pricing SEO Metadata -->
        <meta name="description" content="<?php echo esc_attr($meta_description); ?>"/>
        <meta name="keywords" content="<?php echo esc_attr($meta_keywords); ?>"/>
        <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>"/>
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1"/>
        <meta property="og:locale" content="en_US"/>
        <meta property="og:locale:alternate" content="hi_IN"/>
        <meta property="og:type" content="website"/>
        <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>"/>
        <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>"/>
        <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>"/>
        <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>"/>
        <meta property="og:image" content="<?php echo esc_url($og_image_url); ?>"/>
        <meta name="twitter:card" content="summary_large_image"/>
        <meta name="twitter:site" content="@merarashifal"/>
        <meta name="twitter:title" content="<?php echo esc_attr($meta_title); ?>"/>
        <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>"/>
        <meta name="twitter:image" content="<?php echo esc_url($og_image_url); ?>"/>
        <script type="application/ld+json">
<?php echo $schema_json; ?>
        </script>
        <?php
    }

    /**
     * Get Niyati Credit Packs Data (Synced dynamically with Niyati_Wallet)
     */
    public static function get_niyati_packs() {
        if (class_exists('Niyati_Wallet')) {
            $wallet_packs = Niyati_Wallet::get_packs(true);
            if (!empty($wallet_packs) && is_array($wallet_packs)) {
                $formatted = [];
                foreach ($wallet_packs as $k => $p) {
                    $total_c = intval($p['total_credits'] ?? 60);
                    $offer_p = floatval($p['offer_price'] ?? 40);
                    $base_p  = floatval($p['base_price'] ?? 50);
                    $per_q   = ($total_c > 0) ? round($offer_p / $total_c, 2) : $offer_p;
                    $disc_p  = ($base_p > $offer_p && $base_p > 0) ? round((($base_p - $offer_p) / $base_p) * 100) : 0;

                    $formatted[$k] = [
                        'key'           => $p['key'] ?? $k,
                        'title_en'      => $p['title'] ?? 'Niyati Chat Pack',
                        'title_hi'      => $p['title_hi'] ?? '',
                        'offer_price'   => $offer_p,
                        'base_price'    => $base_p,
                        'total_credits' => $total_c,
                        'base_credits'  => intval($p['base_credits'] ?? $total_c),
                        'bonus_credits' => intval($p['bonus_credits'] ?? 0),
                        'per_q'         => $per_q,
                        'discount_pct'  => $disc_p,
                        'badge'         => $p['badge'] ?? (($k === 'pack_starter') ? 'Most Popular' : (($k === 'pack_value') ? 'Best Value' : 'Mega Saver')),
                        'is_featured'   => !empty($p['featured']) || ($k === 'pack_value'),
                        'validity'      => !empty($p['validity_label']) ? $p['validity_label'] : 'Lifetime Validity',
                        'description'   => $p['description'] ?? "{$total_c} Consultation Questions • 100% Lifetime Validity • Instant Activation",
                    ];
                }
                return $formatted;
            }
        }

        // Fallback default credit packs
        return [
            'pack_starter' => [
                'key'           => 'pack_starter',
                'title_en'      => 'Starter Pack (आरम्भ पैक)',
                'offer_price'   => 40,
                'base_price'    => 50,
                'total_credits' => 60,
                'base_credits'  => 40,
                'bonus_credits' => 20,
                'per_q'         => 0.67,
                'discount_pct'  => 20,
                'badge'         => 'Popular Choice',
                'is_featured'   => false,
                'validity'      => 'Lifetime Validity',
                'description'   => '40 Base + 20 Free Bonus • 60 Questions Total • Valid Lifetime',
            ],
            'pack_value' => [
                'key'           => 'pack_value',
                'title_en'      => 'Shubh Labh Offer (शुभ लाभ ऑफर)',
                'offer_price'   => 100,
                'base_price'    => 150,
                'total_credits' => 120,
                'base_credits'  => 100,
                'bonus_credits' => 20,
                'per_q'         => 0.83,
                'discount_pct'  => 33,
                'badge'         => 'Best Value (33% OFF)',
                'is_featured'   => true,
                'validity'      => 'Lifetime Validity',
                'description'   => '100 Base + 20 Free Bonus • 120 Questions Total • Valid Lifetime',
            ],
            'pack_mega' => [
                'key'           => 'pack_mega',
                'title_en'      => 'Maharatna Pack (महारत्न पैक)',
                'offer_price'   => 199,
                'base_price'    => 350,
                'total_credits' => 310,
                'base_credits'  => 250,
                'bonus_credits' => 60,
                'per_q'         => 0.64,
                'discount_pct'  => 43,
                'badge'         => 'Mega Saver (43% OFF)',
                'is_featured'   => false,
                'validity'      => 'Lifetime Validity',
                'description'   => '250 Base + 60 Free Bonus • 310 Questions Total • Valid Lifetime',
            ]
        ];
    }

    /**
     * Get All 9 Individual Reports (Synced dynamically with MR_Catalog_Manager & MR_Reports_Showcase)
     */
    public static function get_reports() {
        $plugin_img_base = defined('MR_PLUGIN_URL') 
            ? MR_PLUGIN_URL . 'assets/images/reports/' 
            : content_url('/plugins/mr-subscription-reports/assets/images/reports/');

        // High-resolution updated master covers
        $img_yearly_long = content_url('/uploads/2026/11/Mera-Rashifal-2027_-Himalayan-Zodiac-Sunrise.png');
        $img_yearly_vedic = content_url('/uploads/2026/11/2027-Yearly-Horoscope-Under-the-Zodiac-Wheel.png');
        $img_kundli_pred = content_url('/uploads/2026/11/Gemini_Generated_Image_s4glkgs4glkgs4gl.png');

        // Master baseline taxonomy & definitions
        $reports = [
            'yearly_horoscope_long' => [
                'id'            => 'yearly_horoscope_long',
                'slug'          => 'yearly-horoscope-report',
                'name'          => 'Yearly Horoscope Detailed Long Report',
                'hindi_name'    => 'विस्तृत वार्षिक राशिफल एवं वर्षफल (2026-2027)',
                'category'      => 'horoscope',
                'subcategory'   => 'yearly-horoscope',
                'badge'         => 'Best Seller',
                'badge_class'   => 'bg-amber-500 text-white',
                'pages'         => '26 Pages',
                'image'         => $img_yearly_long,
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-yearly-horoscope-long.html'),
                'highlights'    => ['12-Month Detailed Timeline', 'Career, Wealth & Love Milestones', 'Planetary Yogas & Dasha Impact', 'Personalized Shastric Remedies'],
                'button_text'   => 'Get My Report →'
            ],
            'yearly_horoscope_vedic' => [
                'id'            => 'yearly_horoscope_vedic',
                'slug'          => 'yearly-horoscope-snapshot-report',
                'name'          => 'Yearly Horoscope Short Report (2026)',
                'hindi_name'    => 'संक्षिप्त वार्षिक राशिफल एवं गोचर सार',
                'category'      => 'horoscope',
                'subcategory'   => 'yearly-horoscope',
                'badge'         => 'Fast Overview',
                'badge_class'   => 'bg-emerald-600 text-white',
                'pages'         => '5 Pages',
                'image'         => $img_yearly_vedic,
                'price_sale'    => 49,
                'price_reg'     => 499,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-yearly-horoscope-vedic.html'),
                'highlights'    => ['Key Predictions', 'Major Planetary Transits', 'Important Dates', 'Quick Guidance'],
                'button_text'   => 'Get My Report →'
            ],
            'monthly_horoscope_vedic' => [
                'id'            => 'monthly_horoscope_vedic',
                'slug'          => 'monthly-horoscope-report',
                'name'          => 'Monthly Horoscope & Transit Subscription Report',
                'hindi_name'    => 'मासिक गोचर एवं राशिफल विश्लेषण',
                'category'      => 'horoscope',
                'subcategory'   => 'monthly-horoscope',
                'badge'         => 'Transit Guide',
                'badge_class'   => 'bg-blue-600 text-white',
                'pages'         => '12 Pages',
                'image'         => $plugin_img_base . 'monthly_horoscope_vedic.png',
                'price_sale'    => 79,
                'price_reg'     => 499,
                'discount'      => '84% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-monthly-horoscope-vedic.html'),
                'highlights'    => ['Monthly Planetary Transits', 'Moon Sign Dynamic Impact', 'Shubh Muhurat Calendar', 'Auspicious Days & Upayas'],
                'button_text'   => 'Get My Report →'
            ],
            'kundli_predictions' => [
                'id'            => 'kundli_predictions',
                'slug'          => 'janam-kundali-master-report',
                'name'          => 'Master Janma Kundali & Harmonics Report',
                'hindi_name'    => 'मास्टर जन्म पत्रिका एवं षोडशवर्ग चक्र',
                'category'      => 'kundali',
                'subcategory'   => 'kundali-janam',
                'badge'         => 'Most Popular',
                'badge_class'   => 'bg-orange-600 text-white',
                'pages'         => '26 Pages',
                'image'         => $img_kundli_pred,
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-kundli-predictions.html'),
                'highlights'    => ['Complete Janma Kundali Chart', '16 Divisional Harmonics', 'Shadbala Potency & Strengths', 'Favorable Yogas & Timing'],
                'button_text'   => 'Get My Report →'
            ],
            'mahadasha_timing' => [
                'id'            => 'mahadasha_timing',
                'slug'          => 'dasha-timing-report',
                'name'          => 'Master Vedic Dasha Life Timing Report',
                'hindi_name'    => 'मास्टर वैदिक महादशा एवं काल चक्र',
                'category'      => 'kundali',
                'subcategory'   => 'kundali-dasha',
                'badge'         => 'Timing Mastery',
                'badge_class'   => 'bg-purple-600 text-white',
                'pages'         => '26 Pages',
                'image'         => $plugin_img_base . 'mahadasha_timing.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-mahadasha-timing.html'),
                'highlights'    => ['120-Year Vimshottari Table', 'Active Mahadasha Deep Dive', 'Career & Wealth Windows', 'Remedial Protection Guidelines'],
                'button_text'   => 'Get My Report →'
            ],
            'dosh_remedial' => [
                'id'            => 'dosh_remedial',
                'slug'          => 'kundali-dosh-report',
                'name'          => 'Master Kundali Dosh & Remedial Audit Report',
                'hindi_name'    => 'मास्टर कुण्डली दोष एवं प्रामाणिक निवारण',
                'category'      => 'kundali',
                'subcategory'   => 'kundali-dosh',
                'badge'         => 'Remedial Audit',
                'badge_class'   => 'bg-rose-600 text-white',
                'pages'         => '20 Pages',
                'image'         => $plugin_img_base . 'dosh_remedial.png',
                'price_sale'    => 79,
                'price_reg'     => 799,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-dosh-remedial.html'),
                'highlights'    => ['Manglik & Kaal Sarp Audit', 'Pitra Dosh & Sade Sati Timeline', 'Root Cause Planetary Analysis', 'Vedic Mantras, Yantras & Upayas'],
                'button_text'   => 'Get My Report →'
            ],
            'kundli_matching' => [
                'id'            => 'kundli_matching',
                'slug'          => 'kundali-and-gun-matching-report',
                'name'          => 'Master Kundali Matching (Gun Milan) Report',
                'hindi_name'    => 'मास्टर कुण्डली मिलान एवं 36 गुण विश्लेषण',
                'category'      => 'kundali',
                'subcategory'   => 'kundali-matching',
                'badge'         => 'Vivah Edition',
                'badge_class'   => 'bg-pink-600 text-white',
                'pages'         => '24 Pages',
                'image'         => $plugin_img_base . 'kundli_matching.png',
                'price_sale'    => 99,
                'price_reg'     => 1299,
                'discount'      => '92% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-kundli-matching.html'),
                'highlights'    => ['36 Guna Ashtakoot Score', 'Manglik Balance Audit', 'Bhakoot & Nadi Dosh Analysis', 'Relationship Longevity & Harmony'],
                'button_text'   => 'Get My Report →'
            ],
            'numerology_report' => [
                'id'            => 'numerology_report',
                'slug'          => 'numerology-report',
                'name'          => 'Master Numerology & Vedic Integration Report',
                'hindi_name'    => 'मास्टर अंक ज्योतिष एवं लो-शू ग्रिड',
                'category'      => 'numerology',
                'subcategory'   => 'numerology-main',
                'badge'         => 'Sankhya Shastra',
                'badge_class'   => 'bg-indigo-600 text-white',
                'pages'         => '22 Pages',
                'image'         => $plugin_img_base . 'numerology.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-numerology.html'),
                'highlights'    => ['Lo Shu 3x3 Energy Grid Matrix', 'Driver (Mulank) & Conductor (Bhagyank)', 'Lucky Dates, Colors & Gemstones', 'Personalized 90-Day Trajectory'],
                'button_text'   => 'Get My Report →'
            ],
            'palmistry_report' => [
                'id'            => 'palmistry_report',
                'slug'          => 'palm-reading-report',
                'name'          => 'Master Hast Rekha & Palmistry Analysis Report',
                'hindi_name'    => 'मास्टर हस्तरेखा एवं सामुद्रिक शास्त्र',
                'category'      => 'palmistry',
                'subcategory'   => 'palmistry-main',
                'badge'         => 'Samudrik Shastra',
                'badge_class'   => 'bg-emerald-600 text-white',
                'pages'         => '26 Pages',
                'image'         => $plugin_img_base . 'palmistry_report.png',
                'price_sale'    => 99,
                'price_reg'     => 1499,
                'discount'      => '93% OFF',
                'sample_url'    => content_url('/plugins/mr-subscription-reports/samples/sample-palmistry-report.html'),
                'highlights'    => ['Major Lines & Mount Topography', 'Career Velocity & Wealth Indicators', 'Health, Lifeline & Vitality', '5-Phase Age Milestone Timeline'],
                'button_text'   => 'Get My Report →'
            ],
        ];

        // Sync with MR_Catalog_Manager database settings if active
        if (class_exists('MR_Catalog_Manager')) {
            $cards = MR_Catalog_Manager::get_all_cards();
            foreach ($reports as $key => &$r) {
                $lookup_key = $key;
                if ($key === 'numerology_report' && !isset($cards['numerology_report']) && isset($cards['numerology'])) {
                    $lookup_key = 'numerology';
                }
                if (!empty($cards[$lookup_key])) {
                    $card = $cards[$lookup_key];
                    if (!empty($card['name'])) $r['name'] = $card['name'];
                    if (!empty($card['image_url'])) $r['image'] = $card['image_url'];
                    if (!empty($card['pages_tag'])) $r['pages'] = $card['pages_tag'];
                    if (isset($card['price_sale'])) $r['price_sale'] = floatval($card['price_sale']);
                    if (isset($card['price_regular'])) $r['price_reg'] = floatval($card['price_regular']);
                    if (!empty($card['discount_text'])) $r['discount'] = $card['discount_text'];
                    if (!empty($card['highlights']) && is_array($card['highlights'])) $r['highlights'] = $card['highlights'];
                    if (!empty($card['sample_url'])) $r['sample_url'] = $card['sample_url'];
                }
            }
            unset($r);
        }

        return array_values($reports);
    }

    /**
     * Get Report Combos & Value Bundles (Synced with MR_Bundles)
     */
    public static function get_bundles() {
        $reports_list = self::get_reports();
        $rep_map = [];
        foreach ($reports_list as $r) {
            $rep_map[$r['id']] = $r;
        }

        $bundles = [
            [
                'id'            => 'bundle_annual_timing_trio',
                'title'         => 'Yearly Forecast + Kundli + Timing Master Trio',
                'hindi_title'   => 'वार्षिक वर्षफल, कुण्डली एवं दशा महात्रयी',
                'tagline'       => '3 Full Master Dossiers + 30 Bonus AI Consultations',
                'badge'         => 'BEST VALUE • SAVE 91%',
                'badge_class'   => 'bg-amber-600 text-white',
                'tier'          => 'trio',
                'landing_slug'  => 'yearly-horoscope-report',
                'primary_slug'  => 'yearly_horoscope_long',
                'price_sale'    => 278,
                'price_reg'     => 2997,
                'discount_pct'  => 91,
                'chat_credits'  => 30,
                'total_pages'   => '72 Pages Total',
                'is_featured'   => true,
                'report_keys'   => ['yearly_horoscope_long', 'mahadasha_timing', 'kundli_predictions'],
                'cta_text'      => 'Order Combo (3 Reports) • ₹278 →',
            ],
            [
                'id'            => 'bundle_kundli_remedial_trio',
                'title'         => 'Complete Kundli, Timing & Remedial Suite',
                'hindi_title'   => 'कुण्डली, दशा चक्र एवं सम्पूर्ण दोष निवारण',
                'tagline'       => 'Birth Chart + Dasha Cycles + Shastric Upayas',
                'badge'         => 'CLINICAL SUITE • SAVE 91%',
                'badge_class'   => 'bg-purple-700 text-white',
                'tier'          => 'trio',
                'landing_slug'  => 'janam-kundali-master-report',
                'primary_slug'  => 'kundli_predictions',
                'price_sale'    => 261,
                'price_reg'     => 2797,
                'discount_pct'  => 91,
                'chat_credits'  => 30,
                'total_pages'   => '72 Pages Total',
                'is_featured'   => false,
                'report_keys'   => ['kundli_predictions', 'mahadasha_timing', 'dosh_remedial'],
                'cta_text'      => 'Order Combo (3 Reports) • ₹261 →',
            ],
            [
                'id'            => 'bundle_smart_duo',
                'title'         => 'Yearly Forecast + Dasha Timing Smart Duo',
                'hindi_title'   => 'वार्षिक भविष्यवाणी एवं दशा कालचक्र युगल',
                'tagline'       => 'Annual Transits + 120-Year Vimshottari Timeline',
                'badge'         => 'SMART DUO • SAVE 90%',
                'badge_class'   => 'bg-orange-600 text-white',
                'tier'          => 'duo',
                'landing_slug'  => 'yearly-horoscope-report',
                'primary_slug'  => 'yearly_horoscope_long',
                'price_sale'    => 192,
                'price_reg'     => 1998,
                'discount_pct'  => 90,
                'chat_credits'  => 15,
                'total_pages'   => '52 Pages Total',
                'is_featured'   => false,
                'report_keys'   => ['yearly_horoscope_long', 'mahadasha_timing'],
                'cta_text'      => 'Order Duo (2 Reports) • ₹192 →',
            ],
            [
                'id'            => 'bundle_palmistry_trinity',
                'title'         => 'Triple Divination: Palmistry + Kundli + Numerology',
                'hindi_title'   => 'त्रिवेणी संगम: हस्तरेखा, कुण्डली एवं अंक ज्योतिष',
                'tagline'       => 'Triple Divination Synthesis (Hand, Charts & Numbers)',
                'badge'         => '3-IN-1 TRINITY • SAVE 92%',
                'badge_class'   => 'bg-emerald-700 text-white',
                'tier'          => 'trio',
                'landing_slug'  => 'palm-reading-report',
                'primary_slug'  => 'palmistry_report',
                'price_sale'    => 278,
                'price_reg'     => 3497,
                'discount_pct'  => 92,
                'chat_credits'  => 30,
                'total_pages'   => '74 Pages Total',
                'is_featured'   => false,
                'report_keys'   => ['palmistry_report', 'kundli_predictions', 'numerology_report'],
                'cta_text'      => 'Order Trinity (3 Reports) • ₹278 →',
            ],
        ];

        // Enrich bundle items with live images and pages from rep_map
        foreach ($bundles as &$b) {
            $items = [];
            foreach ($b['report_keys'] as $rk) {
                if (!empty($rep_map[$rk])) {
                    $items[] = [
                        'name'  => $rep_map[$rk]['name'],
                        'pages' => $rep_map[$rk]['pages'],
                        'image' => $rep_map[$rk]['image'],
                        'desc'  => !empty($rep_map[$rk]['highlights'][0]) ? $rep_map[$rk]['highlights'][0] : '',
                    ];
                }
            }
            $b['reports'] = $items;
        }
        unset($b);

        return $bundles;
    }

    /**
     * Get Vedic Astrology Subscription Membership Plans (Synced with MR_Packs)
     */
    public static function get_subscription_packs() {
        return [
            'monthly_vedic' => [
                'key'            => 'monthly_vedic',
                'cycle'          => 'monthly',
                'name'           => 'Monthly Vedic Membership',
                'hindi_name'     => 'मासिक वैदिक सदस्यता',
                'tagline'        => 'Flexible month-by-month access for transits and life guidance',
                'badge'          => 'Flexible Monthly',
                'badge_class'    => 'bg-blue-600 text-white',
                'price_inr'      => 499,
                'price_usd'      => 6.99,
                'duration_label' => '1 Month Access',
                'chat_credits'   => 20,
                'is_featured'    => false,
                'quotas'         => [
                    'Yearly Horoscope Snapshot Report' => '1 Report Included',
                    'Monthly Horoscope Transit Reports' => '2 Reports Included',
                    'Master Janma Kundali & Harmonics' => '2 Reports Included',
                    'Vedic Dasha Life Timing Reports' => '2 Reports Included',
                    'Kundali Dosh & Remedial Audits'   => '2 Reports Included',
                    'Master Numerology Analysis'       => '1 Report Included',
                ],
                'cta_text'       => 'Subscribe Monthly • ₹499 →',
            ],
            'quarterly_vedic' => [
                'key'            => 'quarterly_vedic',
                'cycle'          => 'quarterly',
                'name'           => 'Quarterly Vedic Membership',
                'hindi_name'     => 'त्रैमासिक वैदिक सदस्यता',
                'tagline'        => 'Quarterly tracking for major planetary transitions and events',
                'badge'          => 'Save 15%',
                'badge_class'    => 'bg-emerald-600 text-white',
                'price_inr'      => 1199,
                'price_usd'      => 14.99,
                'duration_label' => '3 Months Access',
                'chat_credits'   => 60,
                'is_featured'    => true,
                'quotas'         => [
                    'Yearly Horoscope Reports'         => '2 Reports Included',
                    'Monthly Horoscope Transit Reports' => '4 Reports Included',
                    'Master Janma Kundali & Harmonics' => '4 Reports Included',
                    'Vedic Dasha Life Timing Reports' => '4 Reports Included',
                    'Kundali Dosh & Remedial Audits'   => '4 Reports Included',
                    'Master Numerology & Palmistry'    => '2 Reports Included',
                ],
                'cta_text'       => 'Subscribe Quarterly • ₹1,199 →',
            ],
            'half_yearly_vedic' => [
                'key'            => 'half_yearly_vedic',
                'cycle'          => 'halfyearly',
                'name'           => 'Half-Yearly Vedic Membership',
                'hindi_name'     => 'अर्धवार्षिक वैदिक सदस्यता',
                'tagline'        => '6 months of deep cosmic alignment and unlimited consultation',
                'badge'          => 'Save 25%',
                'badge_class'    => 'bg-purple-600 text-white',
                'price_inr'      => 1999,
                'price_usd'      => 24.99,
                'duration_label' => '6 Months Access',
                'chat_credits'   => 120,
                'is_featured'    => false,
                'quotas'         => [
                    'Yearly Horoscope Reports'         => '4 Reports Included',
                    'Monthly Horoscope Transit Reports' => '6 Reports Included',
                    'Master Janma Kundali & Harmonics' => '6 Reports Included',
                    'Vedic Dasha Life Timing Reports' => '6 Reports Included',
                    'Kundali Dosh & Remedial Audits'   => '6 Reports Included',
                    'Master Numerology & Palmistry'    => '4 Reports Included',
                ],
                'cta_text'       => 'Subscribe 6 Months • ₹1,999 →',
            ],
            'yearly_vedic' => [
                'key'            => 'yearly_vedic',
                'cycle'          => 'yearly',
                'name'           => 'Annual VIP Vedic Membership',
                'hindi_name'     => 'वार्षिक वीआईपी सम्पूर्ण सदस्यता',
                'tagline'        => '365 days of comprehensive astrological coverage for the whole family',
                'badge'          => 'Best Value • Save 40%',
                'badge_class'    => 'bg-amber-600 text-white',
                'price_inr'      => 3199,
                'price_usd'      => 40.99,
                'duration_label' => '12 Months Access',
                'chat_credits'   => 250,
                'is_featured'    => false,
                'quotas'         => [
                    'Yearly Horoscope Detailed Reports' => '6 Reports Included',
                    'Monthly Horoscope Transit Reports' => '12 Reports Included',
                    'Master Janma Kundali & Harmonics' => '12 Reports Included',
                    'Vedic Dasha Life Timing Reports' => '12 Reports Included',
                    'Kundali Dosh & Remedial Audits'   => '12 Reports Included',
                    'Numerology & Palmistry Reports'   => '8 Reports Included',
                ],
                'cta_text'       => 'Subscribe 1 Year • ₹3,199 →',
            ],
        ];
    }

    /**
     * Generate Schema JSON string
     */
    public static function get_schema_json() {
        $canonical_url   = home_url('/pricing/');
        $site_name       = get_bloginfo('name') ?: 'MeraRashifal';
        $site_logo       = content_url('/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png');
        $og_image_url    = content_url('/uploads/2026/11/Mera-Rashifal-2027_-Himalayan-Zodiac-Sunrise.png');

        $bundles_list = self::get_bundles();
        $reports_list = self::get_reports();

        $schema_items = [];
        $pos = 1;

        foreach ($bundles_list as $b) {
            $bundle_landing_url = home_url('/reports/' . $b['landing_slug'] . '/?tier=' . $b['tier']);
            $schema_items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'item'     => [
                    '@type'        => 'Product',
                    '@id'          => $bundle_landing_url . '#product',
                    'name'         => $b['title'],
                    'description'  => $b['tagline'] . ' (' . $b['total_pages'] . ' + ' . $b['chat_credits'] . ' AI Credits)',
                    'image'        => $og_image_url,
                    'url'          => $bundle_landing_url,
                    'offers'       => [
                        '@type'           => 'Offer',
                        'price'           => (string)$b['price_sale'],
                        'priceCurrency'   => 'INR',
                        'priceValidUntil' => '2027-12-31',
                        'availability'    => 'https://schema.org/InStock',
                    ],
                ]
            ];
        }

        foreach ($reports_list as $r) {
            $item_url = home_url('/reports/' . $r['slug'] . '/');
            $schema_items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'item'     => [
                    '@type'        => 'Product',
                    '@id'          => $item_url . '#product',
                    'name'         => $r['name'],
                    'image'        => $r['image'],
                    'url'          => $item_url,
                    'offers'       => [
                        '@type'           => 'Offer',
                        'price'           => (string)$r['price_sale'],
                        'priceCurrency'   => 'INR',
                        'priceValidUntil' => '2027-12-31',
                        'availability'    => 'https://schema.org/InStock',
                    ],
                ]
            ];
        }

        $schema_graph = [
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $canonical_url . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Pricing & Plans', 'item' => $canonical_url]
                ]
            ],
            [
                '@type'        => 'WebPage',
                '@id'          => $canonical_url . '#webpage',
                'url'          => $canonical_url,
                'name'         => 'MeraRashifal Pricing | Vedic Astrology Reports, Bundles & Niyati AI Chat Tokens',
                'isPartOf'     => ['@type' => 'WebSite', '@id' => home_url('/#website'), 'name' => $site_name, 'url' => home_url('/')]
            ],
            [
                '@type'           => 'ItemList',
                '@id'             => $canonical_url . '#reports-catalog',
                'name'            => 'Authentic Vedic Astrological Reports, Bundles & Subscriptions Catalog',
                'numberOfItems'   => count($schema_items),
                'itemListElement' => $schema_items
            ],
        ];

        return json_encode(['@context' => 'https://schema.org', '@graph' => $schema_graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Render the shortcode output without breaking theme header/menu and footer
     */
    public static function render_shortcode($atts = []) {
        $atts = shortcode_atts([
            'show_hero'          => 'yes',
            'show_tokens'        => 'yes',
            'show_reports'       => 'yes',
            'show_bundles'       => 'yes',
            'show_subscriptions' => 'yes',
            'show_trust'         => 'yes',
            'show_faq'           => 'yes',
            'show_schema'        => 'yes',
        ], $atts, 'mr_pricing');

        $bundles_list       = self::get_bundles();
        $reports_list       = self::get_reports();
        $niyati_packs       = self::get_niyati_packs();
        $subscription_packs = self::get_subscription_packs();
        $niyati_avatar      = home_url('/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg');

        ob_start();
        ?>
        <!-- MeraRashifal High-Conversion Pricing Container -->
        <div class="mr-pricing-app font-sans text-[#2D221D] antialiased" style="width:100%; box-sizing:border-box; background-color:#FFFBEB;">

            <!-- Google Fonts & FontAwesome -->
            <link rel="preconnect" href="https://fonts.googleapis.com"/>
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet"/>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
            
            <!-- Tailwind CSS (Scoped safely with preflight: false to strictly preserve Astra & Elementor Header/Footer) -->
            <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
            <script>
            if (typeof tailwind !== 'undefined') {
                tailwind.config = {
                  darkMode: "class",
                  corePlugins: {
                    preflight: false, // Critical to preserve Elementor & Astra Theme Header & Footer!
                  },
                  theme: {
                    extend: {
                      colors: {
                        "sacred-orange": "#F58220",
                        "sacred-gold": "#D9A441",
                        "deep-maroon": "#7A1F3D",
                        "primary": "#964900",
                        "primary-hover": "#7a3b00",
                      },
                      fontFamily: {
                        serif: ["Playfair Display", "Georgia", "serif"],
                        sans: ["Inter", "system-ui", "sans-serif"]
                      }
                    }
                  }
                };
            }
            </script>

            <style>
              /* Scoped styling to ensure perfect layout inside theme */
              .mr-pricing-app {
                box-sizing: border-box !important;
                background-color: #FFFBEB !important;
                width: 100% !important;
                position: relative !important;
              }

              .mr-pricing-wide-container {
                width: 100% !important;
                max-width: 1440px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                box-sizing: border-box !important;
              }
              @media (min-width: 640px) {
                .mr-pricing-wide-container {
                  padding-left: 1.5rem !important;
                  padding-right: 1.5rem !important;
                }
              }
              @media (min-width: 1024px) {
                .mr-pricing-wide-container {
                  padding-left: 2.5rem !important;
                  padding-right: 2.5rem !important;
                }
              }

              /* Individual Reports 3x3 Card Styling (#FDF6E3 background as requested) */
              .mr-pricing-report-card {
                background-color: #FDF6E3 !important;
                border: 1.5px solid #E8DCCF !important;
                border-radius: 18px !important;
                box-shadow: 0 4px 14px rgba(122, 31, 61, 0.05) !important;
                transition: transform 0.22s ease, box-shadow 0.22s ease !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                padding: 1.25rem !important;
              }
              .mr-pricing-report-card:hover {
                transform: translateY(-4px) !important;
                box-shadow: 0 14px 28px rgba(122, 31, 61, 0.12) !important;
                border-color: #D9A441 !important;
              }

              /* Center-aligned image container for crisp full fitting */
              .mr-report-img-box {
                background: #FFFFFF !important;
                border: 1px solid #E8DCCF !important;
                border-radius: 14px !important;
                height: 195px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
                margin: 0.75rem 0 !important;
                padding: 0.5rem !important;
              }
              .mr-report-cover-img {
                max-height: 100% !important;
                max-width: 100% !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                margin: 0 auto !important;
                transition: transform 0.3s ease !important;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.12)) !important;
              }
              .mr-pricing-report-card:hover .mr-report-cover-img {
                transform: scale(1.04) !important;
              }

              /* Bold & bigger heading */
              .mr-report-card-heading {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-weight: 800 !important;
                font-size: 1.25rem !important;
                line-height: 1.35 !important;
                color: #2D221D !important;
              }

              /* Filter buttons */
              .mr-cat-filter-btn {
                background-color: #FFFFFF;
                color: #554841;
                border: 1.5px solid #D8C7B8;
                padding: 0.45rem 1rem;
                border-radius: 9999px;
                font-size: 0.8125rem;
                font-weight: 600;
                transition: all 0.2s ease;
                cursor: pointer;
              }
              .mr-cat-filter-btn:hover {
                border-color: #F58220;
                color: #7A1F3D;
                background-color: #FFF9F2;
              }
              .mr-cat-filter-btn.active {
                background-color: #7A1F3D !important;
                color: #FFFFFF !important;
                border-color: #7A1F3D !important;
                box-shadow: 0 4px 10px rgba(122, 31, 61, 0.25) !important;
              }

              /* Subcategory Filter Pills */
              .mr-subcat-filter-bar {
                display: none;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px dashed #E8DCCF;
              }
              .mr-subcat-filter-btn {
                background: #FFF;
                color: #7A1F3D;
                border: 1px solid #E8DCCF;
                padding: 0.25rem 0.75rem;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.15s ease;
              }
              .mr-subcat-filter-btn.active, .mr-subcat-filter-btn:hover {
                background: #F58220;
                color: #FFF;
                border-color: #F58220;
              }

              /* Combo cover thumbnails */
              .combo-cover-thumb {
                transition: transform 0.2s ease;
                filter: drop-shadow(0 2px 4px rgba(0,0,0,0.12));
              }
              .combo-cover-thumb:hover {
                transform: scale(1.08);
              }
            </style>

            <?php if ($atts['show_schema'] === 'yes'): ?>
                <script type="application/ld+json">
<?php echo self::get_schema_json(); ?>
                </script>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 1. COMPACT HERO / QUICK JUMP (LOW SCROLL HEADER)          -->
            <!-- ========================================================= -->
            <?php if ($atts['show_hero'] === 'yes'): ?>
            <div class="pt-8 pb-5 px-4 mr-pricing-wide-container text-center">
              <span class="inline-block bg-[#FFF0E0] border border-[#F7CFAB] text-[#964900] px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2.5">
                <i class="fa-solid fa-om text-[#F58220] mr-1"></i> Authentic Vedic Wisdom &bull; पारदर्शी मूल्य
              </span>
              <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#2D221D] leading-tight">
                Vedic Astrology Reports, Combos &amp; AI Consultations
              </h1>
              <p class="text-xs sm:text-sm text-gray-600 mt-1.5 max-w-2xl mx-auto">
                NASA JPL Swiss Ephemeris precision &bull; Instant 60-sec delivery &bull; Lifetime token validity &bull; 100% Confidential
              </p>

              <!-- Quick Switcher Tabs -->
              <div class="flex flex-wrap items-center justify-center gap-2 mt-5">
                <a href="#section-chat-tokens" class="bg-[#964900] hover:bg-[#7c3d00] text-white px-4 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                  <i class="fa-solid fa-comments text-amber-300"></i>
                  <span>1. AI Chat Credits (₹40)</span>
                </a>
                <a href="#section-individual-reports" class="bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                  <i class="fa-solid fa-file-invoice text-orange-600"></i>
                  <span>2. Single Reports (₹99)</span>
                </a>
                <a href="#section-report-bundles" class="bg-white hover:bg-orange-50 border border-amber-400 text-amber-900 px-4 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                  <i class="fa-solid fa-gift text-amber-600"></i>
                  <span>3. Report Combos (Save 90%+)</span>
                </a>
                <a href="#section-subscriptions" class="bg-white hover:bg-purple-50 border border-purple-400 text-purple-900 px-4 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                  <i class="fa-solid fa-crown text-purple-600"></i>
                  <span>4. Memberships &amp; Subscriptions</span>
                </a>
              </div>
            </div>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 2. SEQUENCE #1: NIYATI AI CHAT TOKENS                     -->
            <!-- ========================================================= -->
            <?php if ($atts['show_tokens'] === 'yes'): ?>
            <section id="section-chat-tokens" class="py-8">
              <div class="mr-pricing-wide-container">
                <!-- Section Title Strip -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-[#ebd8cb] pb-3 mb-6 gap-2">
                  <div>
                    <div class="flex items-center gap-2">
                      <img src="<?php echo esc_url($niyati_avatar); ?>" class="w-8 h-8 rounded-full border border-orange-500 object-cover" alt="Niyati"/>
                      <h2 class="font-serif text-xl sm:text-2xl font-bold text-[#2D221D]">
                        Niyati AI Chat Credits <span class="text-sm font-sans font-normal text-gray-500">(नियति AI ज्योतिष टोकन्स)</span>
                      </h2>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                      Ask personal astrology questions 24/7. Instant chart analysis, Lagna, Dasha &amp; transits. <strong>Lifetime validity.</strong>
                    </p>
                  </div>
                  <!-- Try Free Link -->
                  <button type="button" onclick="window.openNiyatiChat ? window.openNiyatiChat() : null" class="self-start sm:self-auto text-xs font-bold text-orange-600 hover:text-orange-700 bg-orange-50 border border-orange-200 px-3.5 py-1.5 rounded-lg transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                    <span>Try 1 Free Daily Question →</span>
                  </button>
                </div>

                <!-- 3 Clean Chat Packs Side-by-Side -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                  <?php foreach ($niyati_packs as $pack): 
                    $is_feat = !empty($pack['is_featured']);
                    $recharge_url = home_url('/checkout/?type=chat_credits&pack=' . $pack['key']);
                  ?>
                    <div class="relative bg-white border <?php echo $is_feat ? 'border-2 border-orange-500 shadow-md ring-2 ring-orange-200' : 'border-[#ebd8cb]'; ?> rounded-2xl p-5 flex flex-col justify-between transition hover:-translate-y-1">
                      <?php if ($is_feat): ?>
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-orange-600 to-amber-600 text-white text-[10px] uppercase font-bold tracking-wider px-3 py-0.5 rounded-full shadow-xs">
                          <?php echo esc_html($pack['badge']); ?>
                        </span>
                      <?php endif; ?>

                      <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                          <h3 class="font-serif font-bold text-lg text-[#2D221D]">
                            <?php echo esc_html($pack['title_en']); ?>
                          </h3>
                          <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                            Save <?php echo esc_html($pack['discount_pct']); ?>%
                          </span>
                        </div>

                        <!-- Price Tag -->
                        <div class="flex items-baseline gap-2 mb-3">
                          <span class="text-3xl font-extrabold text-[#964900] font-serif">₹<?php echo esc_html($pack['offer_price']); ?></span>
                          <span class="text-sm text-gray-400 line-through">₹<?php echo esc_html($pack['base_price']); ?></span>
                          <span class="text-[11px] text-gray-500 font-medium">Just ₹<?php echo esc_html($pack['per_q']); ?>/question</span>
                        </div>

                        <!-- Credit Inclusions -->
                        <div class="bg-[#FFF9F2] border border-[#F5DFCE] rounded-xl p-3 mb-4 text-xs space-y-1.5">
                          <div class="flex items-center gap-2 font-bold text-gray-800">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                            <span><?php echo esc_html($pack['total_credits']); ?> Questions Total</span>
                          </div>
                          <?php if (!empty($pack['bonus_credits'])): ?>
                            <div class="flex items-center gap-2 text-emerald-700 pl-5 text-[11px]">
                              <span>&bull; <?php echo esc_html($pack['base_credits']); ?> Base + <?php echo esc_html($pack['bonus_credits']); ?> Free Bonus</span>
                            </div>
                          <?php endif; ?>
                          <div class="flex items-center gap-2 text-gray-600 pl-5 text-[11px]">
                            <span>&bull; <?php echo esc_html($pack['validity']); ?></span>
                          </div>
                          <div class="flex items-center gap-2 text-gray-600 pl-5 text-[11px]">
                            <span>&bull; Instant 24/7 AI Astrologer Consultations</span>
                          </div>
                        </div>
                      </div>

                      <a 
                        href="<?php echo esc_url($recharge_url); ?>" 
                        onclick="if(window.buyNiyatiPackDirect){ event.preventDefault(); window.buyNiyatiPackDirect('<?php echo esc_js($pack['key']); ?>', this); }"
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs tracking-wide text-center transition flex items-center justify-center gap-1.5 <?php echo $is_feat ? 'bg-orange-600 hover:bg-orange-700 text-white shadow-xs' : 'bg-[#964900] hover:bg-[#7c3d00] text-white'; ?>">
                        <span>Recharge for ₹<?php echo esc_html($pack['offer_price']); ?></span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 3. SEQUENCE #2: INDIVIDUAL ASTROLOGY REPORTS              -->
            <!-- ========================================================= -->
            <?php if ($atts['show_reports'] === 'yes'): ?>
            <section id="section-individual-reports" class="py-10 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container">
                <!-- Section Title & Filter Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-[#ebd8cb] pb-3 mb-6 gap-3">
                  <div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-[#2D221D]">
                      Individual Astrological Reports <span class="text-sm font-sans font-normal text-gray-500">(Authentic Vedic Dossiers)</span>
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">
                      Computed mathematically via Swiss Ephemeris. Click <strong>"Get My Report"</strong> to enter birth details and download your personalized dossier.
                    </p>
                  </div>

                  <!-- Category Filter Pills (Top of Widget as requested) -->
                  <div class="flex flex-wrap items-center gap-1.5" id="report-filter-bar">
                    <button type="button" onclick="filterPricingReports('all', '', this)" class="mr-cat-filter-btn active">
                      All Reports (9)
                    </button>
                    <button type="button" onclick="filterPricingReports('horoscope', '', this)" class="mr-cat-filter-btn" data-cat="horoscope">
                      Horoscope Report (3)
                    </button>
                    <button type="button" onclick="filterPricingReports('kundali', '', this)" class="mr-cat-filter-btn" data-cat="kundali">
                      Kundali Report (4)
                    </button>
                    <button type="button" onclick="filterPricingReports('numerology', '', this)" class="mr-cat-filter-btn" data-cat="numerology">
                      Numerology Report (1)
                    </button>
                    <button type="button" onclick="filterPricingReports('palmistry', '', this)" class="mr-cat-filter-btn" data-cat="palmistry">
                      Palmistry Report (1)
                    </button>
                  </div>
                </div>

                <!-- Subcategory Filter Bars (Revealed dynamically on category click) -->
                <div id="subcat-bar-horoscope" class="mr-subcat-filter-bar">
                  <span class="text-xs font-bold text-[#7A1F3D] mr-2">Filter Horoscope:</span>
                  <button type="button" class="mr-subcat-filter-btn active" onclick="filterPricingSubcat('horoscope', 'all', this)">All Horoscopes</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('horoscope', 'yearly-horoscope', this)">Yearly Horoscope</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('horoscope', 'monthly-horoscope', this)">Monthly Horoscope</button>
                </div>

                <div id="subcat-bar-kundali" class="mr-subcat-filter-bar">
                  <span class="text-xs font-bold text-[#7A1F3D] mr-2">Filter Kundali:</span>
                  <button type="button" class="mr-subcat-filter-btn active" onclick="filterPricingSubcat('kundali', 'all', this)">All Kundali</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('kundali', 'kundali-janam', this)">Janma Kundali</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('kundali', 'kundali-dasha', this)">Dasha Life Timing</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('kundali', 'kundali-dosh', this)">Kundali Dosh &amp; Remedies</button>
                  <button type="button" class="mr-subcat-filter-btn" onclick="filterPricingSubcat('kundali', 'kundali-matching', this)">Kundali Matching (Gun Milan)</button>
                </div>

                <!-- 3x3 Responsive Reports Matrix (#FDF6E3 card background, centered clean images) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 xl:gap-6 mt-6" id="reports-grid-container">
                  <?php foreach ($reports_list as $report): 
                    $checkout_url = class_exists('MR_Checkout') 
                        ? MR_Checkout::get_checkout_url($report['id'], 'single') 
                        : home_url('/checkout/?report=' . $report['id'] . '&tier=single');
                    $is_free = ($report['price_sale'] == 0);
                  ?>
                    <div class="mr-pricing-report-card" data-category="<?php echo esc_attr($report['category']); ?>" data-subcategory="<?php echo esc_attr($report['subcategory']); ?>">
                      <div>
                        <!-- Card Header with Badges -->
                        <div class="flex items-center justify-between gap-1 mb-2">
                          <span class="<?php echo esc_attr($report['badge_class']); ?> px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                            <?php echo esc_html($report['badge']); ?>
                          </span>
                          <span class="text-[11px] text-gray-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded font-semibold flex items-center gap-1">
                            <i class="fa-regular fa-file-lines text-orange-500"></i>
                            <?php echo esc_html($report['pages']); ?>
                          </span>
                        </div>

                        <!-- Perfectly Centered Cover Image Box (Fits desktop & mobile cleanly with no cutoff) -->
                        <div class="mr-report-img-box">
                          <?php if (!empty($report['image'])): ?>
                            <img 
                              src="<?php echo esc_url($report['image']); ?>" 
                              alt="<?php echo esc_attr($report['name']); ?>" 
                              class="mr-report-cover-img" 
                              loading="lazy" 
                            />
                          <?php else: ?>
                            <div class="flex items-center justify-center text-gray-400">
                              <i class="fa-solid fa-book-open text-4xl text-amber-300"></i>
                            </div>
                          <?php endif; ?>
                        </div>

                        <!-- Bold & Bigger Heading -->
                        <h3 class="mr-report-card-heading line-clamp-2 min-h-[3rem] mt-1">
                          <a href="<?php echo esc_url($checkout_url); ?>" class="hover:text-orange-600 transition">
                            <?php echo esc_html($report['name']); ?>
                          </a>
                        </h3>
                        <div class="text-xs text-[#7A1F3D] font-semibold mt-0.5 mb-2.5">
                          <?php echo esc_html($report['hindi_name']); ?>
                        </div>

                        <!-- Key Inclusions Chips -->
                        <div class="flex flex-wrap gap-1 mb-3">
                          <?php foreach (array_slice($report['highlights'], 0, 3) as $hl): ?>
                            <span class="bg-[#FFFDF9] border border-[#ECD9C9] text-[#6d4f3b] text-[11px] px-2 py-0.5 rounded">
                              &bull; <?php echo esc_html($hl); ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      </div>

                      <!-- Card Bottom Action Row (Sample Preview + Buy) -->
                      <div class="pt-3 border-t border-[#E8DCCF]">
                        <div class="flex items-center justify-between mb-2.5">
                          <div>
                            <?php if ($is_free): ?>
                              <span class="text-xl font-extrabold text-emerald-600 font-serif">FREE</span>
                            <?php else: ?>
                              <span class="text-2xl font-extrabold text-[#7A1F3D] font-serif">₹<?php echo esc_html($report['price_sale']); ?></span>
                              <span class="text-xs text-gray-400 line-through ml-1">₹<?php echo esc_html($report['price_reg']); ?></span>
                              <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded ml-1"><?php echo esc_html($report['discount']); ?></span>
                            <?php endif; ?>
                          </div>

                          <?php if (!empty($report['sample_url'])): ?>
                            <button 
                              type="button" 
                              class="text-xs text-orange-600 hover:text-orange-800 font-bold underline cursor-pointer"
                              onclick="window.mrOpenSampleModal ? window.mrOpenSampleModal('<?php echo esc_js($report['id']); ?>', '<?php echo esc_js($report['sample_url']); ?>') : window.open('<?php echo esc_url($report['sample_url']); ?>', '_blank')">
                              <i class="fa-regular fa-eye mr-1"></i>Sample
                            </button>
                          <?php endif; ?>
                        </div>

                        <a 
                          href="<?php echo esc_url($checkout_url); ?>" 
                          class="w-full bg-[#F58220] hover:bg-orange-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
                          <span><?php echo esc_html($report['button_text']); ?></span>
                          <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 4. SEQUENCE #3: REPORT COMBINATIONS / BUNDLES             -->
            <!-- ========================================================= -->
            <?php if ($atts['show_bundles'] === 'yes'): ?>
            <section id="section-report-bundles" class="py-10 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container">
                <!-- Section Title Strip -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-[#ebd8cb] pb-3 mb-6 gap-2">
                  <div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-[#2D221D]">
                      Report Combos &amp; Master Suites <span class="text-sm font-sans font-normal text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded font-bold">Save up to 92%</span>
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">
                      Complementary multi-report packages. Each combo clearly outlines every dossier provided below, complete with <strong>free Niyati AI consult credits.</strong>
                    </p>
                  </div>
                </div>

                <!-- 2-Column Wide Bundle Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                  <?php foreach ($bundles_list as $bundle): 
                    $bundle_checkout_url = class_exists('MR_Checkout') 
                        ? MR_Checkout::get_checkout_url($bundle['primary_slug'], $bundle['tier']) 
                        : home_url('/checkout/?report=' . $bundle['primary_slug'] . '&tier=' . $bundle['tier']);
                    $is_feat = !empty($bundle['is_featured']);
                  ?>
                    <div class="halo-card <?php echo $is_feat ? 'halo-featured bg-gradient-to-b from-[#fffbf5] to-white relative border-2 border-orange-500' : 'bg-white border border-[#ebd8cb]'; ?> rounded-2xl p-5 sm:p-6 flex flex-col justify-between shadow-2xs">
                      <div>
                        <!-- Badge Row -->
                        <div class="flex items-center justify-between gap-1 mb-3">
                          <span class="<?php echo esc_attr($bundle['badge_class']); ?> px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                            <?php echo esc_html($bundle['badge']); ?>
                          </span>
                          <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded flex items-center gap-1">
                            <i class="fa-solid fa-gift text-emerald-600"></i> +<?php echo esc_html($bundle['chat_credits']); ?> Free AI Credits
                          </span>
                        </div>

                        <!-- Bundle Title & Pricing Row -->
                        <div class="flex items-baseline justify-between gap-3">
                          <div>
                            <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2D221D] leading-tight">
                              <a href="<?php echo esc_url($bundle_checkout_url); ?>" class="hover:text-orange-600 transition">
                                <?php echo esc_html($bundle['title']); ?>
                              </a>
                            </h3>
                            <div class="text-xs text-[#7A1F3D] font-semibold mt-0.5"><?php echo esc_html($bundle['hindi_title']); ?></div>
                          </div>
                          <div class="text-right shrink-0">
                            <span class="text-3xl font-extrabold text-[#964900] font-serif">₹<?php echo esc_html($bundle['price_sale']); ?></span>
                            <span class="text-xs text-gray-400 line-through block">₹<?php echo esc_html($bundle['price_reg']); ?></span>
                          </div>
                        </div>

                        <p class="text-xs text-gray-600 mt-1.5 mb-3 leading-snug">
                          <?php echo esc_html($bundle['tagline']); ?> &bull; <strong><?php echo esc_html($bundle['total_pages']); ?></strong>
                        </p>

                        <!-- Included Reports Breakdown Showcase -->
                        <div class="bg-[#FFF9F2] border border-[#F5DFCE] rounded-xl p-3 sm:p-3.5 mb-4">
                          <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#964900] flex items-center gap-1.5">
                              <i class="fa-solid fa-layer-group text-orange-600"></i> Included in this Combo (<?php echo count($bundle['reports']); ?> Reports):
                            </span>
                            <span class="text-[10px] font-bold text-gray-500 bg-white border border-[#ECD9C9] px-2 py-0.5 rounded">
                              <?php echo esc_html($bundle['total_pages']); ?>
                            </span>
                          </div>
                          
                          <div class="space-y-2">
                            <?php foreach ($bundle['reports'] as $item): ?>
                              <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-[#ECD9C9] shadow-2xs">
                                <?php if (!empty($item['image'])): ?>
                                  <img 
                                    src="<?php echo esc_url($item['image']); ?>" 
                                    alt="<?php echo esc_attr($item['name']); ?>" 
                                    class="w-10 h-14 sm:w-11 sm:h-15 object-contain combo-cover-thumb shrink-0 rounded" 
                                    loading="lazy" 
                                  />
                                <?php else: ?>
                                  <div class="w-10 h-14 bg-amber-50 rounded flex items-center justify-center text-amber-600 shrink-0">
                                    <i class="fa-solid fa-file-lines text-lg"></i>
                                  </div>
                                <?php endif; ?>

                                <div class="min-w-0 flex-1">
                                  <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-xs text-gray-900 leading-tight">
                                      <?php echo esc_html($item['name']); ?>
                                    </span>
                                    <?php if (!empty($item['pages'])): ?>
                                      <span class="bg-amber-100 text-amber-900 text-[10px] px-1.5 py-0.2 rounded font-bold shrink-0">
                                        <?php echo esc_html($item['pages']); ?>
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                  <?php if (!empty($item['desc'])): ?>
                                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">
                                      <?php echo esc_html($item['desc']); ?>
                                    </p>
                                  <?php endif; ?>
                                </div>
                                <i class="fa-solid fa-circle-check text-emerald-600 text-sm shrink-0 mr-1"></i>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>

                        <!-- Bonus AI Credits Notice -->
                        <div class="flex items-center gap-2 p-2.5 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-xl text-xs text-amber-950 mb-4">
                          <i class="fa-solid fa-wand-magic-sparkles text-orange-600 text-sm shrink-0"></i>
                          <div>
                            <strong>Free Bonus Included:</strong> <?php echo esc_html($bundle['chat_credits']); ?> Niyati AI Astrology Questions (Lifetime Validity &bull; 24/7 Access)
                          </div>
                        </div>
                      </div>

                      <!-- Buy CTA -->
                      <a 
                        href="<?php echo esc_url($bundle_checkout_url); ?>" 
                        class="w-full py-3 px-4 rounded-xl font-bold text-xs tracking-wide text-center transition flex items-center justify-center gap-2 <?php echo $is_feat ? 'bg-orange-600 hover:bg-orange-700 text-white shadow-xs' : 'bg-[#964900] hover:bg-[#7c3d00] text-white'; ?>">
                        <span><?php echo esc_html($bundle['cta_text']); ?></span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 5. SEQUENCE #4: ASTROLOGY SUBSCRIPTION MEMBERSHIPS        -->
            <!-- ========================================================= -->
            <?php if ($atts['show_subscriptions'] === 'yes'): ?>
            <section id="section-subscriptions" class="py-10 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container">
                <!-- Section Title Strip -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-[#ebd8cb] pb-3 mb-6 gap-2">
                  <div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-[#2D221D]">
                      Astrology Membership Plans <span class="text-sm font-sans font-normal text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded font-bold">Vedic Subscriptions</span>
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">
                      Continuous cosmic guidance for you and your family. Includes ongoing monthly transit dossiers, birth chart analyses, and AI consultation questions.
                    </p>
                  </div>
                </div>

                <!-- 4 Subscription Cards (Monthly, Quarterly, Half-Yearly, Yearly) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                  <?php foreach ($subscription_packs as $sub): 
                    $sub_checkout_url = home_url('/checkout/?subscription=' . $sub['key']);
                    $is_feat = !empty($sub['is_featured']);
                  ?>
                    <div class="bg-white border <?php echo $is_feat ? 'border-2 border-purple-600 shadow-md ring-2 ring-purple-100' : 'border-[#ebd8cb]'; ?> rounded-2xl p-5 flex flex-col justify-between transition hover:-translate-y-1">
                      <div>
                        <!-- Header badge -->
                        <div class="flex items-center justify-between gap-1 mb-2">
                          <span class="<?php echo esc_attr($sub['badge_class']); ?> px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                            <?php echo esc_html($sub['badge']); ?>
                          </span>
                          <span class="text-[11px] font-bold text-gray-500 bg-gray-50 px-2 py-0.5 rounded">
                            <?php echo esc_html($sub['duration_label']); ?>
                          </span>
                        </div>

                        <h3 class="font-serif font-bold text-lg text-[#2D221D] leading-snug">
                          <?php echo esc_html($sub['name']); ?>
                        </h3>
                        <div class="text-xs text-[#7A1F3D] font-semibold mt-0.5 mb-2.5">
                          <?php echo esc_html($sub['hindi_name']); ?>
                        </div>

                        <!-- Price Tag -->
                        <div class="flex items-baseline gap-1.5 mb-3">
                          <span class="text-3xl font-extrabold text-[#7A1F3D] font-serif">₹<?php echo esc_html($sub['price_inr']); ?></span>
                          <span class="text-xs text-gray-500 font-medium">/ <?php echo esc_html($sub['duration_label']); ?></span>
                        </div>

                        <!-- Quota List -->
                        <div class="bg-[#FAF5FF] border border-[#E9D5FF] rounded-xl p-3 mb-4 text-xs space-y-1.5">
                          <div class="font-bold text-purple-950 flex items-center gap-1.5 mb-1">
                            <i class="fa-solid fa-crown text-purple-600 text-xs"></i>
                            <span>Package Quotas Included:</span>
                          </div>
                          <?php foreach ($sub['quotas'] as $q_name => $q_count): ?>
                            <div class="flex items-center justify-between text-gray-700 text-[11px] border-b border-purple-100/60 pb-1">
                              <span><?php echo esc_html($q_name); ?></span>
                              <strong class="text-purple-800 shrink-0 ml-1"><?php echo esc_html($q_count); ?></strong>
                            </div>
                          <?php endforeach; ?>
                          <div class="pt-1 flex items-center justify-between text-emerald-800 font-bold text-[11px]">
                            <span><i class="fa-solid fa-comments text-emerald-600 mr-1"></i>Niyati AI Questions:</span>
                            <span>+<?php echo esc_html($sub['chat_credits']); ?> Credits</span>
                          </div>
                        </div>
                      </div>

                      <a 
                        href="<?php echo esc_url($sub_checkout_url); ?>" 
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs tracking-wide text-center transition flex items-center justify-center gap-1.5 <?php echo $is_feat ? 'bg-purple-700 hover:bg-purple-800 text-white shadow-xs' : 'bg-[#7A1F3D] hover:bg-[#601830] text-white'; ?>">
                        <span><?php echo esc_html($sub['cta_text']); ?></span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 6. COMPACT 1-LINE TRUST STRIP                             -->
            <!-- ========================================================= -->
            <?php if ($atts['show_trust'] === 'yes'): ?>
            <div class="py-6 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center text-xs text-gray-700">
                  <div class="bg-white border border-[#ebd8cb] rounded-xl py-2.5 px-3 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-compass text-orange-600 text-sm"></i>
                    <span class="font-semibold">Swiss Ephemeris Accuracy</span>
                  </div>
                  <div class="bg-white border border-[#ebd8cb] rounded-xl py-2.5 px-3 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock text-amber-600 text-sm"></i>
                    <span class="font-semibold">100% Privacy &amp; 256-Bit SSL</span>
                  </div>
                  <div class="bg-white border border-[#ebd8cb] rounded-xl py-2.5 px-3 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600 text-sm"></i>
                    <span class="font-semibold">Lifetime Token Validity</span>
                  </div>
                  <div class="bg-white border border-[#ebd8cb] rounded-xl py-2.5 px-3 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-envelope-circle-check text-blue-600 text-sm"></i>
                    <span class="font-semibold">Instant Email PDF Delivery</span>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 7. COMPACT FAQ ACCORDION                                  -->
            <!-- ========================================================= -->
            <?php if ($atts['show_faq'] === 'yes'): ?>
            <section id="faq-section" class="py-8 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container max-w-4xl mx-auto">
                <h3 class="font-serif font-bold text-xl text-center text-[#2D221D] mb-4">
                  Pricing, Subscriptions &amp; Delivery FAQs
                </h3>

                <div class="space-y-2.5">
                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>What are Astrology Report Combos and what benefits do they offer?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      Astrology Report Combos combine 2 or 3 complementary master dossiers at up to 92% off, giving you complete coverage across Birth Chart, Life Timing, and Remedies, plus up to 30 free Niyati AI Chat credits (lifetime validity) for follow-up questions.
                    </p>
                  </details>

                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>How do Vedic Membership Subscriptions work?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      Vedic Memberships provide recurring report quotas (Monthly Transits, Janma Kundali, Dasha Life Timing, and Remedial audits) that can be generated at any point during your active period, plus large bundles of Niyati AI Chat credits for personal queries.
                    </p>
                  </details>

                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>How are reports delivered after payment?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      Your chart is synthesized using Swiss Ephemeris algorithms. Your 20–26 page PDF report arrives in your email inbox within 60 seconds and is stored permanently in your MeraRashifal account passbook.
                    </p>
                  </details>

                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>Do Niyati AI Chat Tokens expire?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      No. All tokens come with 100% Lifetime Validity. Unused questions remain in your wallet forever.
                    </p>
                  </details>

                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>What payment methods are supported?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      We accept UPI (GPay, PhonePe, Paytm), Credit/Debit Cards (Visa, MasterCard, RuPay), and Net Banking across 50+ banks via 256-bit SSL Razorpay.
                    </p>
                  </details>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <!-- Render Sample Modal DOM if MR_Reports_Showcase is available -->
            <?php 
            if (class_exists('MR_Reports_Showcase')) {
                echo MR_Reports_Showcase::render_sample_modal_dom();
            }
            ?>

            <!-- Interactive Client Scripts for Filtering -->
            <script>
            function filterPricingReports(category, subcategory, btn) {
              var container = btn.closest('.mr-pricing-app');
              if (!container) container = document;

              // Toggle category active state
              var buttons = container.querySelectorAll('.mr-cat-filter-btn');
              buttons.forEach(function(b) {
                b.classList.remove('active');
              });
              btn.classList.add('active');

              // Toggle subcategory bars
              var subHoroscope = document.getElementById('subcat-bar-horoscope');
              var subKundali = document.getElementById('subcat-bar-kundali');
              if (subHoroscope) subHoroscope.style.display = (category === 'horoscope') ? 'flex' : 'none';
              if (subKundali) subKundali.style.display = (category === 'kundali') ? 'flex' : 'none';

              // Filter cards
              var cards = container.querySelectorAll('.mr-pricing-report-card');
              cards.forEach(function(card) {
                var cardCat = card.getAttribute('data-category');
                var cardSub = card.getAttribute('data-subcategory');
                var match = false;

                if (category === 'all') {
                  match = true;
                } else if (subcategory && subcategory !== 'all') {
                  match = (cardSub === subcategory);
                } else {
                  match = (cardCat === category);
                }

                card.style.display = match ? 'flex' : 'none';
              });
            }

            function filterPricingSubcat(category, subcategory, btn) {
              var bar = btn.closest('.mr-subcat-filter-bar');
              if (bar) {
                var btns = bar.querySelectorAll('.mr-subcat-filter-btn');
                btns.forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
              }

              var container = btn.closest('.mr-pricing-app');
              if (!container) container = document;

              var cards = container.querySelectorAll('.mr-pricing-report-card');
              cards.forEach(function(card) {
                var cardCat = card.getAttribute('data-category');
                var cardSub = card.getAttribute('data-subcategory');
                var match = false;

                if (subcategory === 'all') {
                  match = (cardCat === category);
                } else {
                  match = (cardSub === subcategory);
                }

                card.style.display = match ? 'flex' : 'none';
              });
            }
            </script>
        </div>
        <!-- End MeraRashifal High-Conversion Pricing Container -->
        <?php
        return ob_get_clean();
    }
}
