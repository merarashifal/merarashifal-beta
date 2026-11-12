<?php
/**
 * MeraRashifal Pricing Page & Shortcode Engine (High-Conversion Promo Edition)
 * Shortcodes: [mr_pricing], [mr_pricing_table], [mr_pricing_plans]
 *
 * Sequence:
 * 1. AI Chat Credits (Niyati AI Tokens)
 * 2. Individual Reports (9 Single Reports)
 * 3. Report Combination (Value Bundles & Multi-Dossier Suites)
 * 4. Trust Strip & FAQs
 *
 * Designed with ultra-compact height, high information density, and zero fluff for maximum conversion.
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
        $og_image_url    = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/reports/kundli_predictions.png');

        $meta_title       = 'MeraRashifal Pricing | Vedic Astrology Reports, Bundles & Niyati AI Chat Tokens';
        $meta_description = 'Transparent pricing for MeraRashifal Vedic astrology reports from ₹99, value bundle packages from ₹449 & Niyati AI chat token packs from ₹40. 100% accurate Swiss Ephemeris calculations.';
        $meta_keywords    = 'vedic astrology pricing, astrology reports price, astrology bundle reports, buy kundli report bundle, kundali matching cost, niyati ai chat tokens, horoscope report price, palm reading price, janam kundali master report price, dasha timing report, merarashifal pricing';

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
     * Get Niyati Credit Packs Data
     */
    public static function get_niyati_packs() {
        $raw = get_option('niyati_credit_packs', []);
        $packs = [
            'pack_starter' => [
                'key'           => 'pack_starter',
                'title_en'      => 'Starter Pack',
                'title_hi'      => 'आरम्भ पैक',
                'base_price'    => 50,
                'offer_price'   => 40,
                'discount_pct'  => 20,
                'questions'     => 60,
                'base_credits'  => 40,
                'bonus_credits' => 20,
                'rate_per_q'    => '0.67',
                'badge'         => 'Popular Starter',
                'is_featured'   => false,
                'features'      => [
                    '60 In-Depth AI Questions (40 + 20 FREE)',
                    'Lifetime Validity • Zero Expiry',
                    'Lagna, Rashi & Dasha Calculations'
                ]
            ],
            'pack_value' => [
                'key'           => 'pack_value',
                'title_en'      => 'Shubh Labh Pack',
                'title_hi'      => 'शुभ लाभ ऑफर',
                'base_price'    => 150,
                'offer_price'   => 100,
                'discount_pct'  => 33,
                'questions'     => 120,
                'base_credits'  => 100,
                'bonus_credits' => 20,
                'rate_per_q'    => '0.83',
                'badge'         => 'Best Value • 33% OFF',
                'is_featured'   => true,
                'features'      => [
                    '120 In-Depth Consultations (100 + 20 FREE)',
                    'Multiple Family Profiles Supported',
                    'Highest AI Reasoning & Priority Access'
                ]
            ],
            'pack_mega' => [
                'key'           => 'pack_mega',
                'title_en'      => 'Maharatna Pack',
                'title_hi'      => 'महारत्न पैक',
                'base_price'    => 350,
                'offer_price'   => 199,
                'discount_pct'  => 43,
                'questions'     => 310,
                'base_credits'  => 250,
                'bonus_credits' => 60,
                'rate_per_q'    => '0.64',
                'badge'         => 'Mega Saver • 43% OFF',
                'is_featured'   => false,
                'features'      => [
                    '310 Astrological Questions (Lowest Rate: ₹0.64/Q)',
                    'Unlimited Birth Profiles & Memory',
                    'Marriage, Career & Transit Deep Checks'
                ]
            ]
        ];

        if (!empty($raw) && is_array($raw)) {
            foreach ($packs as $k => &$p) {
                if (isset($raw[$k])) {
                    $saved = $raw[$k];
                    if (isset($saved['offer_price'])) $p['offer_price'] = (float)$saved['offer_price'];
                    if (isset($saved['base_price']))  $p['base_price']  = (float)$saved['base_price'];
                    if (isset($saved['base_credits']) && isset($saved['bonus_credits'])) {
                        $p['base_credits']  = (int)$saved['base_credits'];
                        $p['bonus_credits'] = (int)$saved['bonus_credits'];
                        $p['questions']     = $p['base_credits'] + $p['bonus_credits'];
                        if ($p['questions'] > 0) {
                            $p['rate_per_q'] = number_format($p['offer_price'] / $p['questions'], 2);
                        }
                    }
                }
            }
            unset($p);
        }

        return $packs;
    }

    /**
     * Get Report Bundles Data
     */
    public static function get_bundles() {
        $img_reports_base = defined('MR_PLUGIN_URL') ? MR_PLUGIN_URL . 'assets/images/reports/' : content_url('/plugins/mr-subscription-reports/assets/images/reports/');

        return [
            [
                'id'            => 'bundle_cosmic_trinity',
                'title'         => '360° Annual & Lifetime Mastery Trio',
                'hindi_title'   => 'सम्पूर्ण वार्षिक एवं जीवन सिद्धि कॉम्बो',
                'tagline'       => '3 Full Master Dossiers + 30 Bonus AI Consultations',
                'badge'         => 'BEST BUY • SAVE 91%',
                'badge_class'   => 'bg-amber-600 text-white',
                'tier'          => 'trio',
                'landing_slug'  => 'yearly-horoscope-report',
                'price_sale'    => 278,
                'price_reg'     => 3027,
                'discount_pct'  => 91,
                'chat_credits'  => 30,
                'total_pages'   => '72 Pages Total',
                'is_featured'   => true,
                'reports'       => [
                    [
                        'name'      => 'Yearly Horoscope Detailed Long Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'yearly_horoscope_long.png',
                        'desc'      => '12-Month annual transit forecast, career, wealth & remedies.'
                    ],
                    [
                        'name'      => 'Master Vedic Dasha Life Timing Report',
                        'pages'     => '20 Pages',
                        'image'     => $img_reports_base . 'mahadasha_timing.png',
                        'desc'      => '120-Year Vimshottari Mahadasha timeline & turning points.'
                    ],
                    [
                        'name'      => 'Master Janma Kundali & Harmonics Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'kundli_predictions.png',
                        'desc'      => 'Full birth chart, 16 Shodashvarga harmonics & Shadbala strengths.'
                    ]
                ],
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
                'price_sale'    => 261,
                'price_reg'     => 2827,
                'discount_pct'  => 91,
                'chat_credits'  => 30,
                'total_pages'   => '66 Pages Total',
                'is_featured'   => false,
                'reports'       => [
                    [
                        'name'      => 'Master Janma Kundali & Harmonics Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'kundli_predictions.png',
                        'desc'      => 'Complete birth chart analysis, yogas & 16 divisional harmonics.'
                    ],
                    [
                        'name'      => 'Master Vedic Dasha Life Timing Report',
                        'pages'     => '20 Pages',
                        'image'     => $img_reports_base . 'mahadasha_timing.png',
                        'desc'      => 'Active Vimshottari Dasha cycles and event timing predictions.'
                    ],
                    [
                        'name'      => 'Master Kundali Dosh & Remedial Audit Report',
                        'pages'     => '20 Pages',
                        'image'     => $img_reports_base . 'dosh_remedial.png',
                        'desc'      => 'Manglik, Kaal Sarp, Pitra Dosh & authentic Vedic remedies.'
                    ]
                ],
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
                'price_sale'    => 192,
                'price_reg'     => 2013,
                'discount_pct'  => 90,
                'chat_credits'  => 15,
                'total_pages'   => '46 Pages Total',
                'is_featured'   => false,
                'reports'       => [
                    [
                        'name'      => 'Yearly Horoscope Detailed Long Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'yearly_horoscope_long.png',
                        'desc'      => '12-Month detailed annual transit & Varshaphal forecast.'
                    ],
                    [
                        'name'      => 'Master Vedic Dasha Life Timing Report',
                        'pages'     => '20 Pages',
                        'image'     => $img_reports_base . 'mahadasha_timing.png',
                        'desc'      => 'Vimshottari Mahadasha & Antardasha transition cycles.'
                    ]
                ],
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
                'price_sale'    => 278,
                'price_reg'     => 3527,
                'discount_pct'  => 92,
                'chat_credits'  => 30,
                'total_pages'   => '74 Pages Total',
                'is_featured'   => false,
                'reports'       => [
                    [
                        'name'      => 'Master Hast Rekha & Palmistry Analysis Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'palmistry_report.png',
                        'desc'      => 'Samudrik Shastra, major lines, mounts & 5-phase age timeline.'
                    ],
                    [
                        'name'      => 'Master Janma Kundali & Harmonics Report',
                        'pages'     => '26 Pages',
                        'image'     => $img_reports_base . 'kundli_predictions.png',
                        'desc'      => 'Full birth chart analysis, 16 Shodashvarga harmonics.'
                    ],
                    [
                        'name'      => 'Master Numerology & Vedic Integration Report',
                        'pages'     => '22 Pages',
                        'image'     => $img_reports_base . 'numerology.png',
                        'desc'      => '3x3 Lo Shu energy grid, life path numbers & practical arrow.'
                    ]
                ],
                'cta_text'      => 'Order Trinity (3 Reports) • ₹278 →',
            ],
        ];
    }

    /**
     * Get All 9 Individual Reports
     */
    public static function get_reports() {
        $img_reports_base = defined('MR_PLUGIN_URL') ? MR_PLUGIN_URL . 'assets/images/reports/' : content_url('/plugins/mr-subscription-reports/assets/images/reports/');

        $reports = [
            'yearly_horoscope_long' => [
                'id'            => 'yearly_horoscope_long',
                'slug'          => 'yearly-horoscope-report',
                'name'          => 'Yearly Horoscope Detailed Long Report',
                'hindi_name'    => 'विस्तृत वार्षिक राशिफल एवं वर्षफल (2026-2027)',
                'category'      => 'annual',
                'badge'         => 'Best Seller',
                'badge_class'   => 'bg-amber-500 text-white',
                'pages'         => '26 Pages',
                'image'         => $img_reports_base . 'yearly_horoscope_long.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'highlights'    => ['12-Month Detailed Timeline', 'Career, Wealth & Love Milestones', 'Planetary Yogas & Dasha Impact', 'Personalized Shastric Remedies'],
                'button_text'   => 'Get Report Now →'
            ],
            'kundli_predictions' => [
                'id'            => 'kundli_predictions',
                'slug'          => 'janam-kundali-master-report',
                'name'          => 'Master Janma Kundali & Harmonics Report',
                'hindi_name'    => 'मास्टर जन्म पत्रिका एवं षोडशवर्ग चक्र',
                'category'      => 'kundli',
                'badge'         => 'Most Popular',
                'badge_class'   => 'bg-orange-600 text-white',
                'pages'         => '26 Pages',
                'image'         => $img_reports_base . 'kundli_predictions.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'highlights'    => ['Complete Janma Kundali Chart', '16 Divisional Harmonics', 'Shadbala Potency & Strengths', 'Favorable Yogas & Timing'],
                'button_text'   => 'Get Report Now →'
            ],
            'mahadasha_timing' => [
                'id'            => 'mahadasha_timing',
                'slug'          => 'dasha-timing-report',
                'name'          => 'Master Vedic Dasha Life Timing Report',
                'hindi_name'    => 'मास्टर वैदिक महादशा एवं काल चक्र',
                'category'      => 'kundli',
                'badge'         => 'Timing Mastery',
                'badge_class'   => 'bg-purple-600 text-white',
                'pages'         => '20 Pages',
                'image'         => $img_reports_base . 'mahadasha_timing.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'highlights'    => ['120-Year Vimshottari Table', 'Active Mahadasha Deep Dive', 'Career & Wealth Windows', 'Remedial Protection Guidelines'],
                'button_text'   => 'Get Report Now →'
            ],
            'dosh_remedial' => [
                'id'            => 'dosh_remedial',
                'slug'          => 'kundali-dosh-report',
                'name'          => 'Master Kundali Dosh & Remedial Audit Report',
                'hindi_name'    => 'मास्टर कुण्डली दोष एवं प्रामाणिक निवारण',
                'category'      => 'remedies',
                'badge'         => 'Remedial Audit',
                'badge_class'   => 'bg-rose-600 text-white',
                'pages'         => '20 Pages',
                'image'         => $img_reports_base . 'dosh_remedial.png',
                'price_sale'    => 79,
                'price_reg'     => 799,
                'discount'      => '90% OFF',
                'highlights'    => ['Manglik & Kaal Sarp Audit', 'Pitra Dosh & Sade Sati Timeline', 'Root Cause Planetary Analysis', 'Vedic Mantras, Yantras & Upayas'],
                'button_text'   => 'Get Report Now →'
            ],
            'kundli_matching' => [
                'id'            => 'kundli_matching',
                'slug'          => 'kundali-and-gun-matching-report',
                'name'          => 'Master Kundali Matching (Gun Milan) Report',
                'hindi_name'    => 'मास्टर कुण्डली मिलान एवं 36 गुण विश्लेषण',
                'category'      => 'remedies',
                'badge'         => 'Vivah Edition',
                'badge_class'   => 'bg-pink-600 text-white',
                'pages'         => '24 Pages',
                'image'         => $img_reports_base . 'kundli_matching.png',
                'price_sale'    => 99,
                'price_reg'     => 1299,
                'discount'      => '92% OFF',
                'highlights'    => ['36 Guna Ashtakoot Score', 'Manglik Balance Audit', 'Bhakoot & Nadi Dosh Analysis', 'Relationship Longevity & Harmony'],
                'button_text'   => 'Get Report Now →'
            ],
            'palmistry_report' => [
                'id'            => 'palmistry_report',
                'slug'          => 'palm-reading-report',
                'name'          => 'Master Hast Rekha & Palmistry Analysis Report',
                'hindi_name'    => 'मास्टर हस्तरेखा एवं सामुद्रिक शास्त्र',
                'category'      => 'samudrik',
                'badge'         => 'Palmistry Edition',
                'badge_class'   => 'bg-emerald-600 text-white',
                'pages'         => '26 Pages',
                'image'         => $img_reports_base . 'palmistry_report.png',
                'price_sale'    => 99,
                'price_reg'     => 1499,
                'discount'      => '93% OFF',
                'highlights'    => ['Major Lines & Mount Topography', 'Career Velocity & Wealth Indicators', 'Health, Lifeline & Vitality', '5-Phase Age Milestone Timeline'],
                'button_text'   => 'Get Report Now →'
            ],
            'numerology_report' => [
                'id'            => 'numerology_report',
                'slug'          => 'numerology-report',
                'name'          => 'Master Numerology & Vedic Integration Report',
                'hindi_name'    => 'मास्टर अंक ज्योतिष एवं लो-शू ग्रिड',
                'category'      => 'samudrik',
                'badge'         => 'Sankhya Shastra',
                'badge_class'   => 'bg-indigo-600 text-white',
                'pages'         => '22 Pages',
                'image'         => $img_reports_base . 'numerology.png',
                'price_sale'    => 99,
                'price_reg'     => 999,
                'discount'      => '90% OFF',
                'highlights'    => ['Lo Shu 3x3 Energy Grid Matrix', 'Driver (Mulank) & Conductor (Bhagyank)', 'Lucky Dates, Colors & Gemstones', 'Personalized 90-Day Trajectory'],
                'button_text'   => 'Get Report Now →'
            ],
            'monthly_horoscope_vedic' => [
                'id'            => 'monthly_horoscope_vedic',
                'slug'          => 'monthly-horoscope-report',
                'name'          => 'Monthly Horoscope & Transit Subscription Report',
                'hindi_name'    => 'मासिक राशिफल एवं गोचर रणनीति',
                'category'      => 'annual',
                'badge'         => 'Monthly Edition',
                'badge_class'   => 'bg-blue-600 text-white',
                'pages'         => '13 Pages',
                'image'         => $img_reports_base . 'monthly_horoscope_vedic.png',
                'price_sale'    => 49,
                'price_reg'     => 499,
                'discount'      => '90% OFF',
                'highlights'    => ['Month-by-Month Planetary Transits', 'Career, Wealth & Health Ratings', 'Auspicious Days & Shubh Muhurat', 'Remedial Mantras & Guidance'],
                'button_text'   => 'Get Report Now →'
            ],
            'yearly_horoscope_vedic' => [
                'id'            => 'yearly_horoscope_vedic',
                'slug'          => 'yearly-horoscope-snapshot-report',
                'name'          => 'Yearly Horoscope Short Report (2026)',
                'hindi_name'    => 'वार्षिक राशिफल स्नैपशॉट (2026)',
                'category'      => 'annual',
                'badge'         => '100% FREE',
                'badge_class'   => 'bg-teal-600 text-white',
                'pages'         => '5 Pages',
                'image'         => $img_reports_base . 'yearly_horoscope_vedic.png',
                'price_sale'    => 0,
                'price_reg'     => 499,
                'discount'      => '100% FREE',
                'highlights'    => ['Key Planetary Transits Overview', '4 Life Pillars Summary', 'Auspicious Timing Windows', 'Instant Free Download'],
                'button_text'   => 'Get Free Report →'
            ],
        ];

        // Live Synchronize with Report Pricing & Catalog Manager
        if (class_exists('MR_Report_Pricing_Admin')) {
            $cat_cfg = MR_Report_Pricing_Admin::get_config();
            foreach ($reports as $key => &$r) {
                $matched = $cat_cfg[$key] ?? null;
                if (!$matched) {
                    if ($key === 'numerology' && isset($cat_cfg['numerology_report'])) {
                        $matched = $cat_cfg['numerology_report'];
                    } elseif ($key === 'numerology_report' && isset($cat_cfg['numerology'])) {
                        $matched = $cat_cfg['numerology'];
                    }
                }
                if ($matched) {
                    if (!empty($matched['name'])) {
                        $r['name'] = $matched['name'];
                    }
                    if (!empty($matched['pages'])) {
                        $r['pages'] = $matched['pages'];
                    }
                    $sale = floatval($matched['sale_price'] ?? 0);
                    $list = floatval($matched['list_price'] ?? 0);
                    $r['price_sale'] = $sale;
                    $r['price_reg']  = $list;
                    if ($sale <= 0) {
                        $r['discount']    = '100% FREE';
                        $r['button_text'] = 'Get Free Report →';
                        $r['badge']       = '100% FREE';
                    } elseif ($list > $sale && $list > 0) {
                        $pct = round((($list - $sale) / $list) * 100);
                        $r['discount']    = $pct . '% OFF';
                        $r['button_text'] = 'Buy Now • ₹' . intval($sale) . ' →';
                    }
                }
            }
            unset($r);
        }

        return array_values($reports);
    }

    /**
     * Generate Schema JSON string
     */
    public static function get_schema_json() {
        $canonical_url   = home_url('/pricing/');
        $site_name       = get_bloginfo('name') ?: 'MeraRashifal';
        $site_logo       = content_url('/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png');
        $og_image_url    = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/reports/kundli_predictions.png');

        $bundles_list = self::get_bundles();
        $reports_list = self::get_reports();
        $niyati_packs = self::get_niyati_packs();

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
                'name'            => 'Authentic Vedic Astrological Reports & Bundles Catalog',
                'numberOfItems'   => count($schema_items),
                'itemListElement' => $schema_items
            ],
        ];

        return json_encode(['@context' => 'https://schema.org', '@graph' => $schema_graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Render the shortcode output without site header/menu and without site footer
     */
    public static function render_shortcode($atts = []) {
        $atts = shortcode_atts([
            'show_hero'    => 'yes',
            'show_tokens'  => 'yes',
            'show_reports' => 'yes',
            'show_bundles' => 'yes',
            'show_trust'   => 'yes',
            'show_faq'     => 'yes',
            'show_schema'  => 'yes',
        ], $atts, 'mr_pricing');

        $bundles_list  = self::get_bundles();
        $reports_list  = self::get_reports();
        $niyati_packs  = self::get_niyati_packs();
        $niyati_avatar = home_url('/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg');

        ob_start();
        ?>
        <!-- MeraRashifal High-Conversion Pricing Container -->
        <div class="mr-pricing-app font-sans text-[#2D221D] antialiased" style="width:100%; box-sizing:border-box; background-color:#FDF8F1;">

            <!-- Google Fonts & FontAwesome -->
            <link rel="preconnect" href="https://fonts.googleapis.com"/>
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet"/>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
            
            <!-- Tailwind CSS (Loads safely) -->
            <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
            <script>
            if (typeof tailwind !== 'undefined') {
                tailwind.config = {
                  darkMode: "class",
                  theme: {
                    extend: {
                      colors: {
                        "sacred-orange": "#F58220",
                        "sacred-gold": "#D9A441",
                        "deep-maroon": "#7A1F3D",
                        "primary": "#964900",
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
              /* Full screen edge-to-edge breakout for Pricing Page to eliminate side whitespace */
              html, body.page-id-7313 {
                overflow-x: hidden !important;
              }
              body.page-id-7313 #page,
              body.page-id-7313 .site,
              body.page-id-7313 .site-content,
              body.page-id-7313 #primary,
              body.page-id-7313 #main,
              body.page-id-7313 .ast-container,
              body.page-id-7313 .entry-content,
              body.page-id-7313 .elementor-7313,
              body.page-id-7313 .elementor-element-6d128a8,
              body.page-id-7313 .elementor-element-6d128a8 > .e-con-inner,
              body.page-id-7313 .e-con-boxed,
              body.page-id-7313 .e-con {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-sizing: border-box !important;
              }

              .mr-pricing-app {
                width: 100vw !important;
                position: relative !important;
                left: 50% !important;
                right: 50% !important;
                margin-left: -50vw !important;
                margin-right: -50vw !important;
                box-sizing: border-box !important;
                background-color: #FDF8F1 !important;
              }

              .mr-pricing-wide-container {
                width: 100% !important;
                max-width: 1560px !important;
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
              @media (min-width: 1440px) {
                .mr-pricing-wide-container {
                  padding-left: 3.5rem !important;
                  padding-right: 3.5rem !important;
                }
              }

              /* Micro-interactions & 3D styling */
              .mr-pricing-app .halo-card {
                transition: transform 0.25s ease, box-shadow 0.25s ease;
              }
              .mr-pricing-app .halo-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 16px 30px -8px rgba(150, 73, 0, 0.15);
              }
              .mr-pricing-app .halo-featured {
                box-shadow: 0 14px 30px -8px rgba(245, 130, 32, 0.28), 0 0 0 2px #F58220;
              }
              .mr-pricing-app .halo-featured:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px -8px rgba(245, 130, 32, 0.38), 0 0 0 2.5px #F58220;
              }

              .book-cover-img {
                transition: transform 0.35s ease, filter 0.35s ease;
                filter: drop-shadow(0 8px 16px rgba(0,0,0,0.18));
              }
              .report-card:hover .book-cover-img {
                transform: scale(1.04) rotate(-0.5deg);
                filter: drop-shadow(0 14px 24px rgba(150, 73, 0, 0.25));
              }
              .combo-cover-thumb {
                transition: transform 0.2s ease;
                filter: drop-shadow(0 3px 6px rgba(0,0,0,0.14));
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
              <span class="inline-block bg-[#fff0e0] border border-[#f7cfab] text-[#964900] px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2.5">
                <i class="fa-solid fa-om text-[#F58220] mr-1"></i> Authentic Vedic Wisdom &bull; पारदर्शी मूल्य
              </span>
              <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#2D221D] leading-tight">
                Vedic Astrology Reports &amp; AI Consultations
              </h1>
              <p class="text-xs sm:text-sm text-gray-600 mt-1.5 max-w-2xl mx-auto">
                NASA JPL Swiss Ephemeris precision &bull; Instant delivery &bull; Lifetime token validity &bull; 100% Confidential
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
                      <img src="<?php echo esc_url($niyati_avatar); ?>" class="w-7 h-7 rounded-full border border-orange-500 object-cover" alt="Niyati"/>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-stretch">
                  <?php foreach ($niyati_packs as $pack): 
                    $is_feat = !empty($pack['is_featured']);
                  ?>
                    <div class="halo-card <?php echo $is_feat ? 'halo-featured bg-gradient-to-b from-[#fffbf5] to-white relative' : 'bg-white border border-[#ebd8cb]'; ?> rounded-2xl p-5 flex flex-col justify-between">
                      <div>
                        <div class="flex items-center justify-between gap-1 mb-2.5">
                          <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold <?php echo $is_feat ? 'bg-orange-600 text-white' : 'bg-[#fff0e2] text-[#964900] border border-orange-200'; ?>">
                            <?php echo esc_html($pack['badge']); ?>
                          </span>
                          <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                            <?php echo esc_html($pack['discount_pct']); ?>% OFF
                          </span>
                        </div>

                        <div class="flex items-baseline justify-between">
                          <div>
                            <h3 class="font-serif font-bold text-lg text-[#2D221D] leading-tight">
                              <?php echo esc_html($pack['title_en']); ?>
                            </h3>
                            <span class="text-[11px] text-gray-500"><?php echo esc_html($pack['title_hi']); ?></span>
                          </div>
                          <div class="text-right">
                            <span class="text-2xl font-extrabold text-[#964900] font-serif">₹<?php echo esc_html($pack['offer_price']); ?></span>
                            <span class="text-xs text-gray-400 line-through block">₹<?php echo esc_html($pack['base_price']); ?></span>
                          </div>
                        </div>

                        <!-- Question Count Strip -->
                        <div class="bg-[#fff7ee] border border-[#f5dbc6] rounded-xl px-3 py-2 my-3.5 text-center">
                          <span class="text-sm font-bold text-[#964900]"><?php echo esc_html($pack['questions']); ?> Questions Total</span>
                          <span class="text-[11px] text-gray-600 block">(<?php echo esc_html($pack['base_credits']); ?> Base + <strong class="text-emerald-700"><?php echo esc_html($pack['bonus_credits']); ?> FREE Bonus</strong> &bull; ₹<?php echo esc_html($pack['rate_per_q']); ?>/Q)</span>
                        </div>

                        <ul class="space-y-2 text-xs text-gray-600 mb-5">
                          <?php foreach ($pack['features'] as $f): ?>
                            <li class="flex items-center gap-2">
                              <i class="fa-solid fa-circle-check text-emerald-600 text-[11px] shrink-0"></i>
                              <span><?php echo esc_html($f); ?></span>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>

                      <button 
                        type="button" 
                        onclick="handleNiyatiPackBuy('<?php echo esc_attr($pack['key']); ?>', this)"
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs tracking-wide text-center transition flex items-center justify-center gap-1.5 <?php echo $is_feat ? 'bg-orange-600 hover:bg-orange-700 text-white shadow-xs' : 'bg-[#964900] hover:bg-[#7c3d00] text-white'; ?>">
                        <i class="fa-solid fa-coins text-xs"></i>
                        <span>Recharge for ₹<?php echo esc_html($pack['offer_price']); ?></span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                      </button>
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
                      Click <strong>"Buy Now"</strong> on any report to enter your birth details and download your 3D synthesized PDF dossier.
                    </p>
                  </div>

                  <!-- Category Filter Pills -->
                  <div class="flex flex-wrap items-center gap-1.5" id="report-filter-bar">
                    <button type="button" onclick="filterReports('all', this)" class="report-filter-btn active bg-[#964900] text-white px-3.5 py-1.5 rounded-lg text-xs font-bold shadow-xs">
                      All (9)
                    </button>
                    <button type="button" onclick="filterReports('kundli', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      Kundali (2)
                    </button>
                    <button type="button" onclick="filterReports('annual', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      Transits (3)
                    </button>
                    <button type="button" onclick="filterReports('remedies', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      Remedies (2)
                    </button>
                    <button type="button" onclick="filterReports('samudrik', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      Palmistry (2)
                    </button>
                  </div>
                </div>

                <!-- 3-Column Full-Width Reports Grid with Feature Book Covers -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 xl:gap-6" id="reports-grid-container">
                  <?php foreach ($reports_list as $report): 
                    $target_url = home_url('/reports/' . $report['slug'] . '/');
                    $is_free = ($report['price_sale'] == 0);
                    $discount = $report['price_reg'] > 0 ? round((($report['price_reg'] - $report['price_sale']) / $report['price_reg']) * 100) : 0;
                  ?>
                    <div class="report-card halo-card bg-white border border-[#ebd8cb] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-2xs" data-category="<?php echo esc_attr($report['category']); ?>">
                      <div>
                        <!-- Card Header with Badges -->
                        <div class="flex items-center justify-between gap-1 mb-2.5">
                          <span class="<?php echo esc_attr($report['badge_class']); ?> px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                            <?php echo esc_html($report['badge']); ?>
                          </span>
                          <span class="text-[11px] text-gray-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded font-semibold flex items-center gap-1">
                            <i class="fa-regular fa-file-lines text-orange-500"></i>
                            <?php echo esc_html($report['pages']); ?>
                          </span>
                        </div>

                        <!-- 3D Feature Book Cover Image Frame -->
                        <div class="relative my-3 flex items-center justify-center p-3 rounded-xl bg-gradient-to-b from-[#FFFDF9] via-[#FAF3EC] to-[#F5E8D9] border border-[#ECD8C8] overflow-hidden group">
                          <?php if (!empty($report['image'])): ?>
                            <img 
                              src="<?php echo esc_url($report['image']); ?>" 
                              alt="<?php echo esc_attr($report['name']); ?>" 
                              class="book-cover-img h-44 sm:h-48 md:h-52 object-contain mx-auto" 
                              loading="lazy" 
                            />
                          <?php else: ?>
                            <div class="h-44 sm:h-48 flex items-center justify-center text-gray-400">
                              <i class="fa-solid fa-book-open text-4xl text-amber-300"></i>
                            </div>
                          <?php endif; ?>
                          <span class="absolute bottom-2 right-2 bg-black/60 backdrop-blur-xs text-white text-[10px] px-2 py-0.5 rounded font-mono">
                            PDF Dossier
                          </span>
                        </div>

                        <!-- Title & Hindi Subtitle -->
                        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2D221D] leading-snug line-clamp-2 min-h-[2.8rem]">
                          <a href="<?php echo esc_url($target_url); ?>" class="hover:text-orange-600 transition">
                            <?php echo esc_html($report['name']); ?>
                          </a>
                        </h3>
                        <div class="text-xs text-[#7A1F3D] font-semibold mt-1 mb-3">
                          <?php echo esc_html($report['hindi_name']); ?>
                        </div>

                        <!-- Key Inclusions Chips -->
                        <div class="flex flex-wrap gap-1 mb-4">
                          <?php foreach ($report['highlights'] as $hl): ?>
                            <span class="bg-[#fcf5ed] border border-[#f2ded1] text-[#6d4f3b] text-[11px] px-2 py-0.5 rounded">
                              &bull; <?php echo esc_html($hl); ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      </div>

                      <!-- Card Bottom Action Row -->
                      <div class="pt-3 border-t border-[#f2e1d5] flex items-center justify-between gap-2">
                        <div>
                          <?php if ($is_free): ?>
                            <span class="text-xl font-extrabold text-emerald-600 font-serif">FREE</span>
                            <span class="text-xs text-gray-400 line-through block">₹<?php echo esc_html($report['price_reg']); ?></span>
                          <?php else: ?>
                            <div class="flex items-baseline gap-1.5">
                              <span class="text-2xl font-extrabold text-[#964900] font-serif">₹<?php echo esc_html($report['price_sale']); ?></span>
                              <span class="text-xs text-gray-400 line-through">₹<?php echo esc_html($report['price_reg']); ?></span>
                            </div>
                            <?php if ($discount > 0): ?>
                              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded inline-block">
                                SAVE <?php echo esc_html($discount); ?>%
                              </span>
                            <?php endif; ?>
                          <?php endif; ?>
                        </div>

                        <a 
                          href="<?php echo esc_url($target_url); ?>" 
                          class="bg-[#F58220] hover:bg-orange-600 text-white font-bold text-xs py-2 px-4 rounded-xl transition inline-flex items-center gap-1.5 shadow-xs">
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
                      Report Combos &amp; Master Suites <span class="text-sm font-sans font-normal text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded font-bold">Save up to 91%</span>
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">
                      Complementary multi-report packages. Each combo clearly outlines every dossier provided below, complete with <strong>free Niyati AI consult credits.</strong>
                    </p>
                  </div>
                </div>

                <!-- 2-Column Wide Bundle Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                  <?php foreach ($bundles_list as $bundle): 
                    $bundle_url = home_url('/reports/' . $bundle['landing_slug'] . '/?tier=' . $bundle['tier']);
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
                              <a href="<?php echo esc_url($bundle_url); ?>" class="hover:text-orange-600 transition">
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
                            <?php foreach ($bundle['reports'] as $item): 
                              $is_array = is_array($item);
                              $rep_name  = $is_array ? $item['name'] : $item;
                              $rep_pages = $is_array && !empty($item['pages']) ? $item['pages'] : '';
                              $rep_image = $is_array && !empty($item['image']) ? $item['image'] : '';
                              $rep_desc  = $is_array && !empty($item['desc']) ? $item['desc'] : '';
                            ?>
                              <div class="flex items-center gap-3 bg-white p-2.5 rounded-lg border border-[#ECD9C9] shadow-2xs">
                                <?php if (!empty($rep_image)): ?>
                                  <img 
                                    src="<?php echo esc_url($rep_image); ?>" 
                                    alt="<?php echo esc_attr($rep_name); ?>" 
                                    class="w-10 h-14 sm:w-12 sm:h-16 object-contain combo-cover-thumb shrink-0 rounded" 
                                    loading="lazy" 
                                  />
                                <?php else: ?>
                                  <div class="w-10 h-14 bg-amber-50 rounded flex items-center justify-center text-amber-600 shrink-0">
                                    <i class="fa-solid fa-file-lines text-lg"></i>
                                  </div>
                                <?php endif; ?>

                                <div class="min-w-0 flex-1">
                                  <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-xs sm:text-sm text-gray-900 leading-tight">
                                      <?php echo esc_html($rep_name); ?>
                                    </span>
                                    <?php if (!empty($rep_pages)): ?>
                                      <span class="bg-amber-100 text-amber-900 text-[10px] px-1.5 py-0.2 rounded font-bold shrink-0">
                                        <?php echo esc_html($rep_pages); ?>
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                  <?php if (!empty($rep_desc)): ?>
                                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">
                                      <?php echo esc_html($rep_desc); ?>
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
                        href="<?php echo esc_url($bundle_url); ?>" 
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
            <!-- 5. COMPACT 1-LINE TRUST STRIP                             -->
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
                    <span class="font-semibold">3-Min Email PDF Delivery</span>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- 6. COMPACT FAQ ACCORDION                                  -->
            <!-- ========================================================= -->
            <?php if ($atts['show_faq'] === 'yes'): ?>
            <section id="faq-section" class="py-8 border-t border-[#ebd8cb]">
              <div class="mr-pricing-wide-container max-w-4xl mx-auto">
                <h3 class="font-serif font-bold text-xl text-center text-[#2D221D] mb-4">
                  Pricing &amp; Delivery FAQs
                </h3>

                <div class="space-y-2.5">
                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>What are Astrology Report Combos and what benefits do they offer?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      Astrology Report Combos combine 2 or 3 complementary master dossiers at up to 91% off, giving you complete coverage across Birth Chart, Life Timing, and Remedies, plus up to 30 free Niyati AI Chat credits (lifetime validity) for follow-up questions.
                    </p>
                  </details>

                  <details class="group bg-white border border-[#ebd8cb] rounded-xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-gray-800 flex items-center justify-between list-none">
                      <span>How are reports delivered after payment?</span>
                      <i class="fa-solid fa-chevron-down text-[10px] text-orange-600 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="mt-2 text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                      Your chart is synthesized using Swiss Ephemeris algorithms. Your 13–26 page PDF report arrives in your email inbox within 3–5 minutes and is stored permanently in your MeraRashifal account.
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

            <!-- Interactive Client Scripts -->
            <script>
            function filterReports(category, btn) {
              var container = btn.closest('.mr-pricing-app');
              if (!container) container = document;
              var buttons = container.querySelectorAll('.report-filter-btn');
              buttons.forEach(function(b) {
                b.classList.remove('bg-[#964900]', 'text-white', 'font-bold');
                b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'font-medium');
              });

              btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'font-medium');
              btn.classList.add('bg-[#964900]', 'text-white', 'font-bold');

              var cards = container.querySelectorAll('.report-card');
              cards.forEach(function(card) {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                  card.style.display = 'flex';
                } else {
                  card.style.display = 'none';
                }
              });
            }

            function handleNiyatiPackBuy(packKey, btn) {
              if (typeof window.buyNiyatiPackDirect === 'function') {
                window.buyNiyatiPackDirect(packKey, btn);
              } else if (typeof window.openNiyatiRecharge === 'function') {
                window.openNiyatiRecharge(packKey);
              } else if (typeof window.openNiyatiChat === 'function') {
                window.openNiyatiChat();
              } else {
                var trigger = document.getElementById('niyati-floating-trigger');
                if (trigger) {
                  trigger.click();
                } else {
                  alert('Niyati AI Chat is loading. Please try again in a few seconds.');
                }
              }
            }
            </script>
        </div>
        <!-- End MeraRashifal High-Conversion Pricing Container -->
        <?php
        return ob_get_clean();
    }
}
