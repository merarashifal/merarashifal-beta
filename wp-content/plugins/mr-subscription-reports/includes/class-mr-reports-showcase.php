<?php
/**
 * MeraRashifal Reports Showcase Engine
 * Provides shortcodes and renderers for:
 * 1. 3X2 Format with Horizontal Scroll ([mr_reports_3x2_scroll], [mr_reports_showcase layout="3x2_scroll"])
 * 2. 2X2 Vertical Grid Catalog ([mr_reports_2x2_grid], [mr_reports_showcase layout="2x2"])
 * 3. Vertical Listing one below the other ([mr_reports_vertical_list], [mr_reports_showcase layout="vertical"])
 * 
 * Features:
 * - Feature image with zoom effect & badge
 * - Report name in bold
 * - Complete details on desktop & tablet (highlights, full description, pricing)
 * - Limited description on mobile devices for fast, clean browsing
 * - Sample report preview buttons on ALL shortcodes with built-in modal viewer
 * - Listing price (strikethrough) and Sales price
 * - Direct button linking to particular report page
 * - Touch swipe, keyboard, & arrow navigation
 * - Self-contained CSS & JS with unique instance scoping
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Reports_Showcase {

    private static $instance_count = 0;
    private static $assets_rendered = false;

    /**
     * Initialize shortcodes and hooks
     */
    public static function init() {
        // Main parameterized shortcode
        add_shortcode('mr_reports_showcase', [__CLASS__, 'shortcode_showcase']);

        // 3X2 Horizontal Scroll Shortcodes
        add_shortcode('mr_reports_3x2_scroll', [__CLASS__, 'shortcode_3x2_scroll']);
        add_shortcode('mr_reports_carousel', [__CLASS__, 'shortcode_3x2_scroll']);
        add_shortcode('mr_reports_grid_scroll', [__CLASS__, 'shortcode_3x2_scroll']);

        // 2X2 Vertical Grid Shortcodes
        add_shortcode('mr_reports_2x2_grid', [__CLASS__, 'shortcode_2x2_grid']);
        add_shortcode('mr_reports_2x2', [__CLASS__, 'shortcode_2x2_grid']);
        add_shortcode('mr_reports_grid_2x2', [__CLASS__, 'shortcode_2x2_grid']);

        // Vertical Listing (Stacked One Below the Other) Shortcodes
        add_shortcode('mr_reports_vertical_list', [__CLASS__, 'shortcode_vertical_list']);
        add_shortcode('mr_reports_list', [__CLASS__, 'shortcode_vertical_list']);
        add_shortcode('mr_reports_vertical', [__CLASS__, 'shortcode_vertical_list']);
    }

    /**
     * Map of report keys to child page slugs and defaults
     */
    public static function get_child_slug_map() {
        return [
            'yearly_horoscope_long'   => 'yearly-horoscope-report',
            'yearly_horoscope_vedic'  => 'yearly-horoscope-snapshot-report',
            'kundli_predictions'      => 'janam-kundali-master-report',
            'mahadasha_timing'        => 'dasha-timing-report',
            'dosh_remedial'           => 'kundali-dosh-report',
            'kundli_matching'         => 'kundali-and-gun-matching-report',
            'numerology'              => 'numerology-report',
            'monthly_horoscope_vedic' => 'monthly-horoscope-report',
            'palmistry_report'        => 'palm-reading-report',
        ];
    }

    /**
     * Map of report keys to categories and subcategories
     */
    public static function get_report_taxonomy() {
        return [
            'yearly_horoscope_long'   => [
                'category'         => 'horoscope',
                'category_name'    => 'Horoscope Report',
                'subcategory'      => 'yearly-horoscope',
                'subcategory_name' => 'Yearly Horoscope',
            ],
            'yearly_horoscope_vedic'  => [
                'category'         => 'horoscope',
                'category_name'    => 'Horoscope Report',
                'subcategory'      => 'yearly-horoscope',
                'subcategory_name' => 'Yearly Horoscope',
            ],
            'monthly_horoscope_vedic' => [
                'category'         => 'horoscope',
                'category_name'    => 'Horoscope Report',
                'subcategory'      => 'monthly-horoscope',
                'subcategory_name' => 'Monthly Horoscope',
            ],
            'kundli_predictions'      => [
                'category'         => 'kundali',
                'category_name'    => 'Kundali Report',
                'subcategory'      => 'kundali-janam',
                'subcategory_name' => 'Janma Kundali',
            ],
            'mahadasha_timing'        => [
                'category'         => 'kundali',
                'category_name'    => 'Kundali Report',
                'subcategory'      => 'kundali-dasha',
                'subcategory_name' => 'Dasha Timing',
            ],
            'dosh_remedial'           => [
                'category'         => 'kundali',
                'category_name'    => 'Kundali Report',
                'subcategory'      => 'kundali-dosh',
                'subcategory_name' => 'Kundali Dosh',
            ],
            'kundli_matching'         => [
                'category'         => 'kundali',
                'category_name'    => 'Kundali Report',
                'subcategory'      => 'kundali-matching',
                'subcategory_name' => 'Kundali Matching',
            ],
            'numerology'              => [
                'category'         => 'numerology',
                'category_name'    => 'Numerology Report',
                'subcategory'      => 'numerology-main',
                'subcategory_name' => 'Numerology',
            ],
            'numerology_report'       => [
                'category'         => 'numerology',
                'category_name'    => 'Numerology Report',
                'subcategory'      => 'numerology-main',
                'subcategory_name' => 'Numerology',
            ],
            'palmistry_report'        => [
                'category'         => 'palmistry',
                'category_name'    => 'Palmistry Report',
                'subcategory'      => 'palmistry-main',
                'subcategory_name' => 'Palmistry',
            ],
        ];
    }

    /**
     * Retrieve all active, distinct reports with complete metadata and live URLs
     */
    public static function get_reports($limit = -1, $category = '') {
        $slug_map = self::get_child_slug_map();
        $tax_map  = self::get_report_taxonomy();
        $reports = [];
        $seen_slugs = [];

        // Primary: Fetch from MR_Catalog_Manager if active
        if (class_exists('MR_Catalog_Manager')) {
            $cards = MR_Catalog_Manager::get_all_cards();
        } else {
            $cards = [];
        }

        // Fallback default definitions if catalog manager is empty
        $plugin_img_base = defined('MR_PLUGIN_URL') 
            ? MR_PLUGIN_URL . 'assets/images/reports/' 
            : content_url('/plugins/mr-subscription-reports/assets/images/reports/');

        $defaults = [
            'yearly_horoscope_long' => [
                'name'          => 'Yearly Horoscope Detailed Long Report',
                'sub_text'      => 'Comprehensive 26-page 12-month Vedic Varshaphal forecast covering career, wealth, relationships and finances.',
                'image_url'     => content_url('/uploads/2026/11/Mera-Rashifal-2027_-Himalayan-Zodiac-Sunrise.png'),
                'price_sale'    => 99,
                'price_regular' => 999,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '26 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    '12-Month Detailed Life Forecast',
                    'Career, Wealth & Relationship Timing',
                    'Auspicious Windows & Lucky Dates',
                    'Consecrated Shastric Remedies',
                ],
                'is_active'     => 1,
            ],
            'yearly_horoscope_vedic' => [
                'name'          => 'Yearly Horoscope Short Report (2026)',
                'sub_text'      => 'Essential 5-page annual overview with key planetary transits and auspicious timing windows.',
                'image_url'     => content_url('/uploads/2026/11/2027-Yearly-Horoscope-Under-the-Zodiac-Wheel.png'),
                'price_sale'    => 49,
                'price_regular' => 499,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '5 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Key Annual Life Predictions',
                    'Major Planetary Transits & Effects',
                    'Important Dates & Milestones',
                    'Actionable Vedic Guidance',
                ],
                'is_active'     => 1,
            ],
            'kundli_predictions' => [
                'name'          => 'Master Janma Kundali & Harmonics Report',
                'sub_text'      => 'Full 26-page birth chart analysis, 16 Shodashvarga harmonics, Shadbala metrics, yogas and lifetime trajectory.',
                'image_url'     => content_url('/uploads/2026/11/Gemini_Generated_Image_s4glkgs4glkgs4gl.png'),
                'price_sale'    => 99,
                'price_regular' => 999,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '26 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Exhaustive Birth Chart & 16 Harmonics',
                    'Shadbala & Ashtakvarga Strength Scores',
                    'Raj Yogas & Dosh Analysis',
                    'Comprehensive Lifetime Trajectory',
                ],
                'is_active'     => 1,
            ],
            'mahadasha_timing' => [
                'name'          => 'Master Vedic Dasha Life Timing Report',
                'sub_text'      => 'Deep dive into your active Vimshottari Dasha cycles and event timing predictions.',
                'image_url'     => $plugin_img_base . 'mahadasha_timing.png',
                'price_sale'    => 99,
                'price_regular' => 999,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '26 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Current & Future Vimshottari Cycles',
                    'Major Life Breakthrough Timing',
                    'Career, Health & Finance Turning Points',
                    'Custom Remedial Countermeasures',
                ],
                'is_active'     => 1,
            ],
            'dosh_remedial' => [
                'name'          => 'Master Kundali Dosh & Remedial Audit Report',
                'sub_text'      => 'Clinical analysis of Manglik, Kaal Sarp, Pitra, or Sade Sati doshas with personalized Shastric remedies.',
                'image_url'     => $plugin_img_base . 'dosh_remedial.png',
                'price_sale'    => 79,
                'price_regular' => 799,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '20 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Manglik, Kaal Sarp & Pitra Dosh Audit',
                    'Sade Sati & Kemdrum Evaluation',
                    'Exact Shastric Impact Analysis',
                    'Authentic Vedic Remedies & Mantras',
                ],
                'is_active'     => 1,
            ],
            'kundli_matching' => [
                'name'          => 'Master Kundali Matching (Gun Milan) Report',
                'sub_text'      => '24-page Ashtakoot matching with 36 Gunas, Manglik compatibility, Papasamaya load balance and more.',
                'image_url'     => $plugin_img_base . 'kundli_matching.png',
                'price_sale'    => 99,
                'price_regular' => 1299,
                'discount_text' => 'Save 92%',
                'pages_tag'     => '24 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    '36 Guna Ashtakoot Compatibility',
                    'Manglik Dosh Balance Assessment',
                    'Emotional, Mental & Financial Harmony',
                    'Matrimonial Peace & Stability Upayas',
                ],
                'is_active'     => 1,
            ],
            'numerology' => [
                'name'          => 'Master Numerology & Vedic Integration Report',
                'sub_text'      => '22-page Sankhya Shastra audit: Lo Shu 3x3 energy grid, core life paths, 8-1-6 Practical Arrow and 90-day roadmap.',
                'image_url'     => $plugin_img_base . 'numerology.png',
                'price_sale'    => 99,
                'price_regular' => 999,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '22 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Lo Shu 3x3 Grid & Core Energy Path',
                    'Destiny, Soul & Life Path Numbers',
                    'Name Spelling Vibration Optimization',
                    'Auspicious Lucky Dates & Colors',
                ],
                'is_active'     => 1,
            ],
            'monthly_horoscope_vedic' => [
                'name'          => 'Monthly Horoscope & Transit Subscription Report',
                'sub_text'      => 'Detailed 13-page month-by-month planetary transits, auspicious days, sector ratings and guidance.',
                'image_url'     => $plugin_img_base . 'monthly_horoscope_vedic.png',
                'price_sale'    => 49,
                'price_regular' => 499,
                'discount_text' => 'Save 90%',
                'pages_tag'     => '13 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Month-by-Month Planetary Transits',
                    'Career & Financial Fluctuations',
                    'Shubh Muhurats & Auspicious Days',
                    'Monthly Practical Remedies',
                ],
                'is_active'     => 1,
            ],
            'palmistry_report' => [
                'name'          => 'Master Hast Rekha & Palmistry Analysis Report',
                'sub_text'      => 'Comprehensive 26-page Hast Rekha & Samudrik Shastra analysis: Major lines, Mounts, Career, Wealth & 5-phase age timeline.',
                'image_url'     => $plugin_img_base . 'palmistry_report.png',
                'price_sale'    => 99,
                'price_regular' => 1499,
                'discount_text' => 'Save 93%',
                'pages_tag'     => '26 Pages',
                'preview_text'  => 'Preview Sample Report',
                'highlights'    => [
                    'Major Heart, Head, Life & Fate Lines',
                    'Vedic Mounts & Energy Topography',
                    'Career & Wealth Breakthrough Timing',
                    'Samudrik Shastra Remedial Roadmap',
                ],
                'is_active'     => 1,
            ],
        ];

        // Merge sources
        $source_cards = !empty($cards) ? $cards : $defaults;

        foreach ($source_cards as $key => $card) {
            // Check active status
            if (isset($card['is_active']) && empty($card['is_active'])) {
                continue;
            }

            // Deduplicate by target slug (e.g. numerology vs numerology_report)
            $target_slug = $slug_map[$key] ?? str_replace('_', '-', $key);
            if (isset($seen_slugs[$target_slug])) {
                continue;
            }
            $seen_slugs[$target_slug] = true;

            // Filter category if specified
            if (!empty($category) && !empty($card['category']) && strcasecmp($category, $card['category']) !== 0) {
                continue;
            }

            // Determine accurate report page URL
            $page = get_page_by_path($target_slug) ?: get_page_by_path('reports/' . $target_slug);
            if ($page) {
                $page_url = get_permalink($page->ID);
            } else {
                $page_url = home_url('/reports/' . $target_slug . '/');
            }

            // Prices
            $price_sale = isset($card['price_sale']) ? floatval($card['price_sale']) : 99;
            $price_reg  = isset($card['price_regular']) ? floatval($card['price_regular']) : 999;
            $discount   = !empty($card['discount_text']) ? $card['discount_text'] : '';
            if (empty($discount) && $price_reg > $price_sale && $price_reg > 0) {
                $pct = round((($price_reg - $price_sale) / $price_reg) * 100);
                $discount = 'Save ' . $pct . '%';
            }

            // Image URL resolution
            $image_url = !empty($card['image_url']) ? $card['image_url'] : '';
            if (empty($image_url) && isset($defaults[$key]['image_url'])) {
                $image_url = $defaults[$key]['image_url'];
            }

            // Report description (full)
            $description = !empty($card['sub_text']) ? $card['sub_text'] : (!empty($card['description']) ? $card['description'] : '');
            if (empty($description) && isset($defaults[$key]['sub_text'])) {
                $description = $defaults[$key]['sub_text'];
            }

            // Highlights
            $highlights = !empty($card['highlights']) && is_array($card['highlights']) 
                ? $card['highlights'] 
                : ($defaults[$key]['highlights'] ?? []);

            // Sample preview link text & URL
            $preview_text = !empty($card['preview_text']) ? $card['preview_text'] : 'Preview Sample Report';
            $sample_report_url = !empty($card['sample_report_url']) ? $card['sample_report_url'] : '';

            $tax = $tax_map[$key] ?? [
                'category'         => 'horoscope',
                'category_name'    => 'Horoscope Report',
                'subcategory'      => 'all',
                'subcategory_name' => 'General',
            ];

            $reports[] = [
                'key'               => $key,
                'slug'              => $target_slug,
                'name'              => $card['name'] ?? 'Vedic Astrology Report',
                'category'          => $tax['category'],
                'subcategory'       => $tax['subcategory'],
                'category_name'     => $tax['category_name'],
                'subcategory_name'  => $tax['subcategory_name'],
                'description'       => $description,
                'highlights'        => $highlights,
                'image_url'         => $image_url,
                'price_sale'        => $price_sale,
                'price_regular'     => $price_reg,
                'discount_text'     => $discount,
                'pages_tag'         => $card['pages_tag'] ?? 'Detailed PDF',
                'page_url'          => $page_url,
                'button_text'       => !empty($card['button_text']) ? $card['button_text'] : 'Get My Report →',
                'preview_text'      => $preview_text,
                'sample_report_url' => $sample_report_url,
            ];
        }

        // Apply limit if specified
        if ($limit > 0 && count($reports) > $limit) {
            $reports = array_slice($reports, 0, $limit);
        }

        return $reports;
    }

    /**
     * Unified Shortcode Handler
     */
    public static function shortcode_showcase($atts) {
        $atts = shortcode_atts([
            'layout'           => '3x2_scroll', // '3x2_scroll', '2x2', or 'vertical'
            'title'            => 'Vedic Astrology Master Reports',
            'subtitle'         => 'Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms.',
            'limit'            => -1,
            'category'         => '',
            'button_text'      => '',
            'show_description' => 'yes',
            'show_header'      => '',
            'show_filter'      => 'yes',
            'columns'          => 2,
            'cta_url'          => home_url('/pricing/'),
            'cta_text'         => 'Browse All Reports & Bundles',
            'class'            => '',
        ], $atts, 'mr_reports_showcase');

        if ($atts['layout'] === 'vertical' && $atts['show_header'] === '') {
            $atts['show_header'] = 'no'; // Remove header by default for vertical list
        } elseif ($atts['show_header'] === '') {
            $atts['show_header'] = 'yes';
        }

        if ($atts['layout'] === '2x2' || $atts['layout'] === '2x2_grid' || $atts['layout'] === 'grid_2x2') {
            return self::render_2x2_grid($atts);
        }

        if ($atts['layout'] === 'vertical' || $atts['layout'] === 'list') {
            return self::render_vertical_list($atts);
        }

        return self::render_3x2_scroll($atts);
    }

    /**
     * 3X2 Horizontal Scroll Shortcode Handler
     */
    public static function shortcode_3x2_scroll($atts) {
        $atts = (array) $atts;
        $atts['layout'] = '3x2_scroll';
        return self::shortcode_showcase($atts);
    }

    /**
     * 2X2 Vertical Grid Shortcode Handler
     */
    public static function shortcode_2x2_grid($atts) {
        $atts = (array) $atts;
        $atts['layout'] = '2x2';
        return self::shortcode_showcase($atts);
    }

    /**
     * Vertical List Shortcode Handler
     * Heading removed by default, sorting/filters on top, and cards displayed 2 side-by-side
     */
    public static function shortcode_vertical_list($atts) {
        $atts = (array) $atts;
        $atts['layout'] = 'vertical';
        if (!isset($atts['show_header'])) {
            $atts['show_header'] = 'no'; // Heading removed by default
        }
        if (!isset($atts['columns'])) {
            $atts['columns'] = 4; // 4-column matrix on desktop
        }
        if (!isset($atts['show_filter'])) {
            $atts['show_filter'] = 'yes'; // Sorting/filter on top
        }
        if (!isset($atts['show_description'])) {
            $atts['show_description'] = 'yes'; // Show report description (4 lines on mobile)
        }
        return self::shortcode_showcase($atts);
    }

    /**
     * Render 3X2 Layout with Horizontal Scroll
     */
    public static function render_3x2_scroll($args = []) {
        self::$instance_count++;
        $instance_id = 'mr-rsc-3x2-' . self::$instance_count;

        $title            = $args['title'] ?? 'Vedic Astrology Master Reports';
        $subtitle         = $args['subtitle'] ?? 'Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms.';
        $limit            = isset($args['limit']) ? intval($args['limit']) : -1;
        $category         = $args['category'] ?? '';
        $custom_btn       = $args['button_text'] ?? '';
        $show_header      = !empty($args['show_header']) && $args['show_header'] !== 'no';
        $cta_url          = $args['cta_url'] ?? home_url('/pricing/');
        $cta_text         = $args['cta_text'] ?? 'Browse All Reports & Bundles';
        $extra_class      = $args['class'] ?? '';

        $reports = self::get_reports($limit, $category);
        if (empty($reports)) {
            return '<!-- No active reports found -->';
        }

        ob_start();
        self::render_embedded_styles();
        ?>
        <div class="mr-rsc-wrapper mr-rsc-3x2-wrapper <?php echo esc_attr($extra_class); ?>" id="<?php echo esc_attr($instance_id); ?>">
            
            <?php if ($show_header): ?>
                <div class="mr-rsc-header">
                    <div class="mr-rsc-header-left">
                        <div class="mr-rsc-title-wrap">
                            <span class="mr-rsc-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                                    <path d="M6 6h10"></path>
                                    <path d="M6 10h10"></path>
                                    <path d="M6 14h7"></path>
                                </svg>
                            </span>
                            <h2 class="mr-rsc-title"><?php echo esc_html($title); ?></h2>
                        </div>
                        <?php if (!empty($subtitle)): ?>
                            <p class="mr-rsc-subtitle"><?php echo esc_html($subtitle); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mr-rsc-header-actions">
                        <?php if (!empty($cta_url) && !empty($cta_text)): ?>
                            <a href="<?php echo esc_url($cta_url); ?>" class="mr-rsc-cta-link">
                                <span><?php echo esc_html($cta_text); ?></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        <?php endif; ?>

                        <!-- Arrow Navigation Controls -->
                        <div class="mr-rsc-nav-controls">
                            <button type="button" class="mr-rsc-nav-btn mr-rsc-prev" aria-label="<?php esc_attr_e('Scroll Left', 'merarashifal'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button type="button" class="mr-rsc-nav-btn mr-rsc-next" aria-label="<?php esc_attr_e('Scroll Right', 'merarashifal'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 3X2 Horizontal Scroll Track Container -->
            <div class="mr-rsc-scroll-container">
                <div class="mr-rsc-grid-3x2" role="region" aria-label="<?php esc_attr_e('Astrology Reports Grid', 'merarashifal'); ?>" tabindex="0">
                    <?php foreach ($reports as $rep): 
                        $btn_label = !empty($custom_btn) ? $custom_btn : $rep['button_text'];
                        $price_sale_fmt = number_format($rep['price_sale'], 0);
                        $price_reg_fmt  = number_format($rep['price_regular'], 0);
                    ?>
                        <article class="mr-rsc-card">
                            <!-- Feature Image Wrap -->
                            <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-card-media" aria-label="<?php echo esc_attr($rep['name']); ?>">
                                <?php if (!empty($rep['pages_tag'])): ?>
                                    <span class="mr-rsc-badge mr-rsc-badge-pages"><?php echo esc_html($rep['pages_tag']); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($rep['discount_text'])): ?>
                                    <span class="mr-rsc-badge mr-rsc-badge-discount"><?php echo esc_html($rep['discount_text']); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($rep['image_url'])): ?>
                                    <img src="<?php echo esc_url($rep['image_url']); ?>" 
                                         alt="<?php echo esc_attr($rep['name']); ?>" 
                                         loading="lazy" 
                                         decoding="async" 
                                         class="mr-rsc-img" />
                                <?php else: ?>
                                    <div class="mr-rsc-img-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                    </div>
                                <?php endif; ?>
                            </a>

                            <!-- Card Body -->
                            <div class="mr-rsc-card-body">
                                <h3 class="mr-rsc-card-title">
                                    <a href="<?php echo esc_url($rep['page_url']); ?>">
                                        <?php echo esc_html($rep['name']); ?>
                                    </a>
                                </h3>

                                <?php if (!empty($rep['description'])): ?>
                                    <p class="mr-rsc-card-desc"><?php echo esc_html($rep['description']); ?></p>
                                <?php endif; ?>

                                <!-- Price Row -->
                                <div class="mr-rsc-card-pricing">
                                    <div class="mr-rsc-price-box">
                                        <span class="mr-rsc-price-sale">₹<?php echo esc_html($price_sale_fmt); ?></span>
                                        <?php if ($rep['price_regular'] > $rep['price_sale']): ?>
                                            <span class="mr-rsc-price-reg">₹<?php echo esc_html($price_reg_fmt); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="mr-rsc-delivery-tag">Instant PDF Delivery</span>
                                </div>

                                <!-- Action Buttons: Get Report + Preview Sample -->
                                <div class="mr-rsc-card-action">
                                    <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-btn">
                                        <span><?php echo esc_html($btn_label); ?></span>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </a>

                                    <button type="button" class="mr-rsc-sample-btn mr-link-sample" data-title="<?php echo esc_attr($rep['name']); ?>" data-slug="<?php echo esc_attr($rep['key']); ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span><?php echo esc_html($rep['preview_text']); ?></span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mobile Swipe Hint -->
            <div class="mr-rsc-scroll-hint">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 5l7 7-7 7M3 12h18"/></svg>
                <span>Swipe horizontally to view all <?php echo count($reports); ?> reports</span>
            </div>
        </div>

        <script>
        (function() {
            var wrap = document.getElementById('<?php echo esc_js($instance_id); ?>');
            if (!wrap) return;
            var grid = wrap.querySelector('.mr-rsc-grid-3x2');
            var prevBtn = wrap.querySelector('.mr-rsc-prev');
            var nextBtn = wrap.querySelector('.mr-rsc-next');

            function getScrollStep() {
                var card = grid.querySelector('.mr-rsc-card');
                if (!card) return grid.clientWidth * 0.75;
                return (card.offsetWidth + 18) * (window.innerWidth > 1024 ? 3 : (window.innerWidth > 640 ? 2 : 1));
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    grid.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    grid.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
                });
            }

            function checkArrows() {
                if (!prevBtn || !nextBtn) return;
                var maxScroll = grid.scrollWidth - grid.clientWidth - 5;
                prevBtn.disabled = grid.scrollLeft <= 5;
                nextBtn.disabled = grid.scrollLeft >= maxScroll;
            }
            grid.addEventListener('scroll', checkArrows, { passive: true });
            checkArrows();
            window.addEventListener('resize', checkArrows, { passive: true });
        })();
        </script>
        <?php
        self::render_sample_modal_dom();
        return ob_get_clean();
    }

    /**
     * Render 2X2 Vertical Grid Layout
     * Complete details on desktop & tablet (highlights, full description, prices, dual buttons)
     * Limited description on mobile for fast, clean browsing
     */
    public static function render_2x2_grid($args = []) {
        self::$instance_count++;
        $instance_id = 'mr-rsc-2x2-' . self::$instance_count;

        $title            = $args['title'] ?? 'Vedic Astrology Master Reports';
        $subtitle         = $args['subtitle'] ?? 'Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms and verified by traditional pandits.';
        $limit            = isset($args['limit']) ? intval($args['limit']) : -1;
        $category         = $args['category'] ?? '';
        $custom_btn       = $args['button_text'] ?? '';
        $show_header      = !empty($args['show_header']) && $args['show_header'] !== 'no';
        $cta_url          = $args['cta_url'] ?? home_url('/pricing/');
        $cta_text         = $args['cta_text'] ?? 'Browse All Reports & Bundles';
        $extra_class      = $args['class'] ?? '';

        $reports = self::get_reports($limit, $category);
        if (empty($reports)) {
            return '<!-- No active reports found -->';
        }

        ob_start();
        self::render_embedded_styles();
        ?>
        <div class="mr-rsc-wrapper mr-rsc-2x2-wrapper <?php echo esc_attr($extra_class); ?>" id="<?php echo esc_attr($instance_id); ?>">
            
            <?php if ($show_header): ?>
                <div class="mr-rsc-header">
                    <div class="mr-rsc-header-left">
                        <div class="mr-rsc-title-wrap">
                            <span class="mr-rsc-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                            </span>
                            <h2 class="mr-rsc-title"><?php echo esc_html($title); ?></h2>
                        </div>
                        <?php if (!empty($subtitle)): ?>
                            <p class="mr-rsc-subtitle"><?php echo esc_html($subtitle); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($cta_url) && !empty($cta_text)): ?>
                        <div class="mr-rsc-header-actions">
                            <a href="<?php echo esc_url($cta_url); ?>" class="mr-rsc-cta-link">
                                <span><?php echo esc_html($cta_text); ?></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- 2X2 Vertical Grid Container -->
            <div class="mr-rsc-grid-2x2">
                <?php foreach ($reports as $rep): 
                    $btn_label = !empty($custom_btn) ? $custom_btn : $rep['button_text'];
                    $price_sale_fmt = number_format($rep['price_sale'], 0);
                    $price_reg_fmt  = number_format($rep['price_regular'], 0);
                ?>
                    <article class="mr-rsc-grid-card">
                        <!-- Top: Feature Media Banner -->
                        <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-grid-media" aria-label="<?php echo esc_attr($rep['name']); ?>">
                            <?php if (!empty($rep['pages_tag'])): ?>
                                <span class="mr-rsc-badge mr-rsc-badge-pages">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <?php echo esc_html($rep['pages_tag']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($rep['discount_text'])): ?>
                                <span class="mr-rsc-badge mr-rsc-badge-discount"><?php echo esc_html($rep['discount_text']); ?></span>
                            <?php endif; ?>

                            <?php if (!empty($rep['image_url'])): ?>
                                <img src="<?php echo esc_url($rep['image_url']); ?>" 
                                     alt="<?php echo esc_attr($rep['name']); ?>" 
                                     loading="lazy" 
                                     decoding="async" 
                                     class="mr-rsc-grid-img" />
                            <?php else: ?>
                                <div class="mr-rsc-img-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- Card Content -->
                        <div class="mr-rsc-grid-body">
                            <!-- Name in Bold -->
                            <h3 class="mr-rsc-grid-title">
                                <a href="<?php echo esc_url($rep['page_url']); ?>">
                                    <?php echo esc_html($rep['name']); ?>
                                </a>
                            </h3>

                            <!-- Complete Details for Desktop & Tablet -->
                            <div class="mr-rsc-details-desktop">
                                <?php if (!empty($rep['description'])): ?>
                                    <p class="mr-rsc-grid-desc-full"><?php echo esc_html($rep['description']); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($rep['highlights']) && is_array($rep['highlights'])): ?>
                                    <ul class="mr-rsc-highlights">
                                        <?php foreach ($rep['highlights'] as $hl): ?>
                                            <li>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                <span><?php echo esc_html($hl); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>

                            <!-- Limited Description for Mobile -->
                            <div class="mr-rsc-details-mobile">
                                <?php if (!empty($rep['description'])): ?>
                                    <p class="mr-rsc-grid-desc-limited"><?php echo esc_html($rep['description']); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Pricing Block -->
                            <div class="mr-rsc-grid-pricing">
                                <div class="mr-rsc-price-box">
                                    <span class="mr-rsc-price-sale">₹<?php echo esc_html($price_sale_fmt); ?></span>
                                    <?php if ($rep['price_regular'] > $rep['price_sale']): ?>
                                        <span class="mr-rsc-price-reg">₹<?php echo esc_html($price_reg_fmt); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="mr-rsc-delivery-tag">Instant PDF in 60s</span>
                            </div>

                            <!-- Dual Action Buttons -->
                            <div class="mr-rsc-grid-actions">
                                <?php 
                                $desktop_grid_btn = trim(str_replace(['→', '&rarr;', '->'], '', $btn_label ?: 'Get My Personalized Report'));
                                ?>
                                <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-btn">
                                    <span class="mr-rsc-btn-txt-desktop"><?php echo esc_html($desktop_grid_btn); ?></span>
                                    <span class="mr-rsc-btn-txt-mobile">Get My Report</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>

                                <button type="button" class="mr-rsc-sample-btn mr-link-sample" data-title="<?php echo esc_attr($rep['name']); ?>" data-slug="<?php echo esc_attr($rep['key']); ?>">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span class="mr-rsc-btn-txt-desktop"><?php echo esc_html($rep['preview_text'] ?: 'Preview Sample Report'); ?></span>
                                    <span class="mr-rsc-btn-txt-mobile">Preview Sample</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        self::render_sample_modal_dom();
        return ob_get_clean();
    }

    /**
     * Render Vertical Listing of Reports (one below the other)
     */
    public static function render_vertical_list($args = []) {
        self::$instance_count++;
        $instance_id = 'mr-rsc-vert-' . self::$instance_count;

        $title            = $args['title'] ?? 'Vedic Astrology Master Reports';
        $subtitle         = $args['subtitle'] ?? 'Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms.';
        $limit            = isset($args['limit']) ? intval($args['limit']) : -1;
        $category         = $args['category'] ?? '';
        $custom_btn       = $args['button_text'] ?? '';
        $show_header      = !empty($args['show_header']) && $args['show_header'] !== 'no';
        $show_desc        = !isset($args['show_description']) || ($args['show_description'] !== 'no' && $args['show_description'] !== false);
        $show_filter      = !isset($args['show_filter']) || ($args['show_filter'] !== 'no' && $args['show_filter'] !== false);
        $cta_url          = $args['cta_url'] ?? home_url('/pricing/');
        $cta_text         = $args['cta_text'] ?? 'Browse All Reports & Bundles';
        $extra_class      = $args['class'] ?? '';

        $reports = self::get_reports($limit, $category);
        if (empty($reports)) {
            return '<!-- No active reports found -->';
        }

        // Calculate dynamic category and subcategory counts
        $counts = [
            'all'              => count($reports),
            'horoscope'        => 0,
            'kundali'          => 0,
            'numerology'       => 0,
            'palmistry'        => 0,
            'yearly-horoscope' => 0,
            'monthly-horoscope'=> 0,
            'kundali-janam'    => 0,
            'kundali-dasha'    => 0,
            'kundali-dosh'     => 0,
            'kundali-matching' => 0,
        ];

        foreach ($reports as $r) {
            $cat = $r['category'] ?? '';
            $sub = $r['subcategory'] ?? '';
            if (isset($counts[$cat])) {
                $counts[$cat]++;
            }
            if (isset($counts[$sub])) {
                $counts[$sub]++;
            }
        }

        ob_start();
        self::render_embedded_styles();
        ?>
        <div class="mr-rsc-wrapper mr-rsc-vertical-wrapper <?php echo esc_attr($extra_class); ?>" id="<?php echo esc_attr($instance_id); ?>">
            
            <?php if ($show_header): ?>
                <div class="mr-rsc-header">
                    <div class="mr-rsc-header-left">
                        <div class="mr-rsc-title-wrap">
                            <span class="mr-rsc-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                                    <path d="M6 6h10"></path>
                                    <path d="M6 10h10"></path>
                                    <path d="M6 14h7"></path>
                                </svg>
                            </span>
                            <h2 class="mr-rsc-title"><?php echo esc_html($title); ?></h2>
                        </div>
                        <?php if (!empty($subtitle)): ?>
                            <p class="mr-rsc-subtitle"><?php echo esc_html($subtitle); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($cta_url) && !empty($cta_text)): ?>
                        <div class="mr-rsc-header-actions">
                            <a href="<?php echo esc_url($cta_url); ?>" class="mr-rsc-cta-link">
                                <span><?php echo esc_html($cta_text); ?></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_filter): ?>
                <!-- Category Sorting & Filtering Tabs (Top of Widget) -->
                <div class="mr-rsc-filter-bar">
                    <div class="mr-rsc-tabs" role="tablist" aria-label="<?php esc_attr_e('Filter Reports by Category', 'merarashifal'); ?>">
                        <button type="button" class="mr-rsc-tab active" data-filter="all">
                            <span class="mr-rsc-tab-label">All Reports</span>
                            <span class="mr-rsc-tab-count"><?php echo esc_html($counts['all']); ?></span>
                        </button>
                        <button type="button" class="mr-rsc-tab" data-filter="horoscope">
                            <span class="mr-rsc-tab-label">Horoscope Report</span>
                            <span class="mr-rsc-tab-count"><?php echo esc_html($counts['horoscope']); ?></span>
                        </button>
                        <button type="button" class="mr-rsc-tab" data-filter="kundali">
                            <span class="mr-rsc-tab-label">Kundali Report</span>
                            <span class="mr-rsc-tab-count"><?php echo esc_html($counts['kundali']); ?></span>
                        </button>
                        <button type="button" class="mr-rsc-tab" data-filter="numerology">
                            <span class="mr-rsc-tab-label">Numerology Report</span>
                            <span class="mr-rsc-tab-count"><?php echo esc_html($counts['numerology']); ?></span>
                        </button>
                        <button type="button" class="mr-rsc-tab" data-filter="palmistry">
                            <span class="mr-rsc-tab-label">Palmistry Report</span>
                            <span class="mr-rsc-tab-count"><?php echo esc_html($counts['palmistry']); ?></span>
                        </button>
                    </div>

                    <!-- Dynamic Sub-Filter Row -->
                    <div class="mr-rsc-subfilters" style="display: none;">
                        <!-- Horoscope Sub-Filters: Yearly and Monthly -->
                        <div class="mr-rsc-subgroup" data-group="horoscope" style="display: none;">
                            <button type="button" class="mr-rsc-subpill active" data-subfilter="all">
                                <span>All Horoscope (<?php echo esc_html($counts['horoscope']); ?>)</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="yearly-horoscope">
                                <span>Yearly Horoscope (<?php echo esc_html($counts['yearly-horoscope']); ?>)</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="monthly-horoscope">
                                <span>Monthly Horoscope (<?php echo esc_html($counts['monthly-horoscope']); ?>)</span>
                            </button>
                        </div>

                        <!-- Kundali Sub-Filters -->
                        <div class="mr-rsc-subgroup" data-group="kundali" style="display: none;">
                            <button type="button" class="mr-rsc-subpill active" data-subfilter="all">
                                <span>All Kundali Reports (<?php echo esc_html($counts['kundali']); ?>)</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="kundali-janam">
                                <span>Janma Kundali</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="kundali-dasha">
                                <span>Dasha Timing</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="kundali-dosh">
                                <span>Kundali Dosh</span>
                            </button>
                            <button type="button" class="mr-rsc-subpill" data-subfilter="kundali-matching">
                                <span>Kundali Matching</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Vertical Stack of Reports (2 Cards Side-by-Side on Desktop/Tablet) -->
            <div class="mr-rsc-vertical-stack">
                <?php foreach ($reports as $index => $rep): 
                    $btn_label = !empty($custom_btn) ? $custom_btn : $rep['button_text'];
                    $price_sale_fmt = number_format($rep['price_sale'], 0);
                    $price_reg_fmt  = number_format($rep['price_regular'], 0);
                ?>
                    <article class="mr-rsc-vert-card" 
                             data-category="<?php echo esc_attr($rep['category'] ?? ''); ?>" 
                             data-subcategory="<?php echo esc_attr($rep['subcategory'] ?? ''); ?>" 
                             data-key="<?php echo esc_attr($rep['key']); ?>">
                        <!-- Left: Feature Image (Vertically Center Aligned to Card Height) -->
                        <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-vert-media" aria-label="<?php echo esc_attr($rep['name']); ?>">
                            <?php if (!empty($rep['discount_text'])): ?>
                                <span class="mr-rsc-badge mr-rsc-badge-discount"><?php echo esc_html($rep['discount_text']); ?></span>
                            <?php endif; ?>

                            <?php if (!empty($rep['image_url'])): ?>
                                <img src="<?php echo esc_url($rep['image_url']); ?>" 
                                     alt="<?php echo esc_attr($rep['name']); ?>" 
                                     loading="lazy" 
                                     decoding="async" 
                                     class="mr-rsc-vert-img" />
                            <?php else: ?>
                                <div class="mr-rsc-img-placeholder">
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- Right: Info, Pricing & CTA Buttons -->
                        <div class="mr-rsc-vert-body">
                            <div class="mr-rsc-vert-meta">
                                <?php if (!empty($rep['pages_tag'])): ?>
                                    <span class="mr-rsc-vert-tag">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        <?php echo esc_html($rep['pages_tag']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Heading Bold & Bigger (at least twice the size) -->
                            <h3 class="mr-rsc-vert-title">
                                <a href="<?php echo esc_url($rep['page_url']); ?>">
                                    <?php echo esc_html($rep['name']); ?>
                                </a>
                            </h3>

                            <?php if ($show_desc && !empty($rep['description'])): ?>
                                <p class="mr-rsc-vert-desc"><?php echo esc_html($rep['description']); ?></p>
                            <?php endif; ?>

                            <!-- Pricing Row -->
                            <div class="mr-rsc-vert-price-row">
                                <div class="mr-rsc-price-box">
                                    <span class="mr-rsc-price-sale">₹<?php echo esc_html($price_sale_fmt); ?></span>
                                    <?php if ($rep['price_regular'] > $rep['price_sale']): ?>
                                        <span class="mr-rsc-price-reg">₹<?php echo esc_html($price_reg_fmt); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="mr-rsc-delivery-tag">Instant PDF in 60s</span>
                            </div>

                            <!-- Dual Action Buttons -->
                            <div class="mr-rsc-vert-btns-wrap">
                                <?php 
                                $desktop_btn_label = trim(str_replace(['→', '&rarr;', '->'], '', $btn_label ?: 'Get My Personalized Report'));
                                ?>
                                <a href="<?php echo esc_url($rep['page_url']); ?>" class="mr-rsc-btn mr-rsc-btn-vert">
                                    <span class="mr-rsc-btn-txt-desktop"><?php echo esc_html($desktop_btn_label); ?></span>
                                    <span class="mr-rsc-btn-txt-mobile">Get My Report</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>

                                <button type="button" class="mr-rsc-sample-btn mr-rsc-sample-btn-vert mr-link-sample" data-title="<?php echo esc_attr($rep['name']); ?>" data-slug="<?php echo esc_attr($rep['key']); ?>">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span class="mr-rsc-btn-txt-desktop"><?php echo esc_html($rep['preview_text'] ?: 'Preview Sample Report'); ?></span>
                                    <span class="mr-rsc-btn-txt-mobile">Preview Sample</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($show_filter): ?>
            <!-- Interactive Category & Subcategory Filter Script -->
            <script>
            (function() {
                var wrap = document.getElementById('<?php echo esc_js($instance_id); ?>');
                if (!wrap) return;

                var tabs = wrap.querySelectorAll('.mr-rsc-tab');
                var subWrap = wrap.querySelector('.mr-rsc-subfilters');
                var subGroups = wrap.querySelectorAll('.mr-rsc-subgroup');
                var subPills = wrap.querySelectorAll('.mr-rsc-subpill');
                var cards = wrap.querySelectorAll('.mr-rsc-vert-card');

                var currentCategory = 'all';
                var currentSub = 'all';

                function filterCards() {
                    cards.forEach(function(card) {
                        var cat = card.getAttribute('data-category') || '';
                        var sub = card.getAttribute('data-subcategory') || '';

                        var matchCat = (currentCategory === 'all' || cat === currentCategory);
                        var matchSub = (currentSub === 'all' || sub === currentSub);

                        if (matchCat && matchSub) {
                            card.classList.remove('mr-rsc-hidden');
                        } else {
                            card.classList.add('mr-rsc-hidden');
                        }
                    });
                }

                tabs.forEach(function(tab) {
                    tab.addEventListener('click', function() {
                        var cat = tab.getAttribute('data-filter');
                        currentCategory = cat;
                        currentSub = 'all';

                        tabs.forEach(function(t) { t.classList.remove('active'); });
                        tab.classList.add('active');

                        var hasSub = false;
                        subGroups.forEach(function(g) {
                            if (g.getAttribute('data-group') === cat) {
                                g.style.display = 'flex';
                                hasSub = true;
                                var pills = g.querySelectorAll('.mr-rsc-subpill');
                                pills.forEach(function(p, i) {
                                    p.classList.toggle('active', i === 0);
                                });
                            } else {
                                g.style.display = 'none';
                            }
                        });

                        if (subWrap) {
                            subWrap.style.display = hasSub ? 'flex' : 'none';
                        }

                        filterCards();
                    });
                });

                subPills.forEach(function(pill) {
                    pill.addEventListener('click', function() {
                        var sub = pill.getAttribute('data-subfilter');
                        currentSub = sub;

                        var parentGroup = pill.closest('.mr-rsc-subgroup');
                        if (parentGroup) {
                            parentGroup.querySelectorAll('.mr-rsc-subpill').forEach(function(p) {
                                p.classList.remove('active');
                            });
                        }
                        pill.classList.add('active');

                        filterCards();
                    });
                });
            })();
            </script>
        <?php endif; ?>
        <?php
        self::render_sample_modal_dom();
        return ob_get_clean();
    }

    /**
     * Render the Sample Preview Modal DOM & Interactive JS
     * Ensures sample reports can be previewed seamlessly on ANY page
     */
    public static function render_sample_modal_dom() {
        static $modal_rendered = false;
        if ($modal_rendered) {
            return;
        }
        $modal_rendered = true;

        $ajax_url = admin_url('admin-ajax.php');
        ?>
        <div id="mr-rsc-sample-modal" class="mr-rsc-modal" style="display:none;" aria-hidden="true" role="dialog">
            <div class="mr-rsc-modal-overlay"></div>
            <div class="mr-rsc-modal-box">
                <!-- Modal Header -->
                <div class="mr-rsc-modal-header">
                    <div class="mr-rsc-modal-title-wrap">
                        <span class="mr-rsc-modal-badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            Sample Preview
                        </span>
                        <h3 id="mr-rsc-modal-title" class="mr-rsc-modal-heading">Astrological Report Preview</h3>
                    </div>
                    <div class="mr-rsc-modal-actions">
                        <a href="#" id="mr-rsc-modal-buy-btn" class="mr-rsc-modal-cta">
                            <span>Get This Report Now &rarr;</span>
                        </a>
                        <button type="button" class="mr-rsc-modal-close" aria-label="Close preview">&times;</button>
                    </div>
                </div>

                <!-- Modal Viewer Body with Loader -->
                <div class="mr-rsc-modal-body">
                    <div id="mr-rsc-modal-loader" class="mr-rsc-modal-loader">
                        <div class="mr-rsc-spinner"></div>
                        <p>Loading publication-grade sample report...</p>
                    </div>
                    <iframe id="mr-rsc-modal-iframe" src="about:blank" frameborder="0"></iframe>
                </div>

                <!-- Modal Footer -->
                <div class="mr-rsc-modal-footer">
                    <div class="mr-rsc-modal-note">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f58220" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>Authentic Vedic sample report. Your personalized report will be generated using your exact birth coordinates.</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var modal = document.getElementById('mr-rsc-sample-modal');
            if (!modal) return;
            var overlay = modal.querySelector('.mr-rsc-modal-overlay');
            var closeBtn = modal.querySelector('.mr-rsc-modal-close');
            var iframe = document.getElementById('mr-rsc-modal-iframe');
            var loader = document.getElementById('mr-rsc-modal-loader');
            var titleEl = document.getElementById('mr-rsc-modal-title');
            var buyBtn = document.getElementById('mr-rsc-modal-buy-btn');
            var ajaxUrl = <?php echo json_encode($ajax_url); ?>;

            function openModal(slug, title, reportUrl) {
                if (titleEl) titleEl.textContent = title + ' (Sample Preview)';
                if (buyBtn && reportUrl) buyBtn.setAttribute('href', reportUrl);
                if (loader) loader.style.display = 'flex';
                if (iframe) {
                    iframe.onload = function() {
                        if (loader) loader.style.display = 'none';
                    };
                    iframe.src = ajaxUrl + '?action=mr_view_sample_report&slug=' + encodeURIComponent(slug);
                }
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.style.display = 'none';
                if (iframe) iframe.src = 'about:blank';
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.mr-link-sample');
                if (btn) {
                    e.preventDefault();
                    var slug = btn.getAttribute('data-slug') || '';
                    var title = btn.getAttribute('data-title') || 'Astrological Report';
                    var article = btn.closest('article');
                    var reportUrl = article ? (article.querySelector('a[href]') ? article.querySelector('a[href]').href : '#') : '#';
                    openModal(slug, title, reportUrl);
                }
            });

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (overlay) overlay.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        })();
        </script>
        <?php
    }

    /**
     * Self-contained, scoped CSS
     */
    public static function render_embedded_styles() {
        if (self::$assets_rendered) {
            return;
        }
        self::$assets_rendered = true;
        ?>
        <style id="mr-reports-showcase-styles">
        /* ==========================================================================
           MeraRashifal Reports Showcase (3X2 Scroll, 2X2 Grid & Vertical List)
           Scoped styles to prevent conflicts with themes/Elementor
           ========================================================================== */
        
        .mr-rsc-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 16px;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #221a16;
        }

        .mr-rsc-wrapper *, 
        .mr-rsc-wrapper *::before, 
        .mr-rsc-wrapper *::after {
            box-sizing: border-box;
        }

        /* Header Bar */
        .mr-rsc-header {
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-bottom: 1px solid rgba(120, 53, 15, 0.15);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        @media (min-width: 640px) {
            .mr-rsc-header {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }

        .mr-rsc-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mr-rsc-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f58220;
            background: #fff8f6;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid rgba(245, 130, 32, 0.2);
        }

        .mr-rsc-title {
            margin: 0;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            color: #964900;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        @media (min-width: 768px) {
            .mr-rsc-title {
                font-size: 30px;
            }
        }

        .mr-rsc-subtitle {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            color: #564336;
            line-height: 1.45;
            max-width: 720px;
        }

        .mr-rsc-header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }

        .mr-rsc-cta-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #7d5700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .mr-rsc-cta-link:hover {
            color: #f58220;
            text-decoration: underline;
        }

        /* Navigation Buttons */
        .mr-rsc-nav-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .mr-rsc-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(120, 53, 15, 0.2);
            background: #ffffff;
            color: #964900;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
        }
        .mr-rsc-nav-btn:hover:not(:disabled) {
            background: #964900;
            color: #ffffff;
            border-color: #964900;
            box-shadow: 0 4px 12px rgba(150, 73, 0, 0.25);
            transform: scale(1.05);
        }
        .mr-rsc-nav-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ==========================================================================
           3X2 HORIZONTAL SCROLL GRID
           Two rows, 3 columns visible on desktop, horizontally scrolling
           ========================================================================== */
        .mr-rsc-scroll-container {
            position: relative;
            width: 100%;
        }

        .mr-rsc-grid-3x2 {
            display: grid;
            grid-template-rows: repeat(2, minmax(0, 1fr));
            grid-auto-flow: column;
            grid-auto-columns: calc((100% - 36px) / 3); /* 3 cards visible on desktop */
            gap: 18px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            padding: 6px 4px 18px 4px;
            scrollbar-width: thin;
            scrollbar-color: #f58220 rgba(120, 53, 15, 0.1);
        }

        @media (max-width: 1024px) and (min-width: 641px) {
            .mr-rsc-grid-3x2 {
                grid-auto-columns: calc((100% - 18px) / 2); /* 2 cards visible on tablet */
            }
        }
        @media (max-width: 640px) {
            .mr-rsc-grid-3x2 {
                grid-auto-columns: calc(84% - 10px); /* 1 card + peek next on mobile */
                gap: 14px;
                padding-bottom: 12px;
            }
        }

        .mr-rsc-grid-3x2::-webkit-scrollbar {
            height: 7px;
        }
        .mr-rsc-grid-3x2::-webkit-scrollbar-track {
            background: rgba(120, 53, 15, 0.08);
            border-radius: 10px;
        }
        .mr-rsc-grid-3x2::-webkit-scrollbar-thumb {
            background: #f58220;
            border-radius: 10px;
        }

        /* Card Container (3x2) */
        .mr-rsc-card {
            scroll-snap-align: start;
            background: #ffffff;
            border: 1px solid rgba(217, 119, 6, 0.22);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 16px rgba(120, 53, 15, 0.05);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .mr-rsc-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(120, 53, 15, 0.12);
            border-color: rgba(245, 130, 32, 0.55);
        }

        .mr-rsc-card-media {
            position: relative;
            display: block;
            width: 100%;
            height: 150px;
            background: #fff8f6;
            overflow: hidden;
            text-decoration: none;
        }
        @media (min-width: 640px) {
            .mr-rsc-card-media {
                height: 160px;
            }
        }
        .mr-rsc-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            display: block;
        }
        .mr-rsc-card:hover .mr-rsc-img {
            transform: scale(1.06);
        }

        .mr-rsc-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f58220;
            background: linear-gradient(135deg, #fff8f6 0%, #ffdeaa 100%);
        }

        /* Badges */
        .mr-rsc-badge {
            position: absolute;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 8px;
            border-radius: 6px;
            z-index: 2;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mr-rsc-badge-pages {
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.94);
            color: #78350f;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            border: 1px solid rgba(120, 53, 15, 0.15);
        }
        .mr-rsc-badge-discount {
            top: 10px;
            right: 10px;
            background: #059669;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
        }

        /* Card Body */
        .mr-rsc-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
            gap: 10px;
            background: #ffffff;
        }

        .mr-rsc-card-title {
            margin: 0;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.3;
            color: #964900;
        }
        .mr-rsc-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .mr-rsc-card-title a:hover {
            color: #f58220;
        }

        .mr-rsc-card-desc {
            margin: 0;
            font-size: 12.5px;
            color: #564336;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 36px;
        }

        /* Pricing Block */
        .mr-rsc-card-pricing,
        .mr-rsc-grid-pricing {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px solid rgba(120, 53, 15, 0.1);
        }
        .mr-rsc-price-box {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .mr-rsc-price-sale {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 21px;
            font-weight: 800;
            color: #964900;
            line-height: 1;
        }
        .mr-rsc-price-reg {
            font-size: 13px;
            color: #9ca3af;
            text-decoration: line-through;
            font-weight: 500;
        }
        .mr-rsc-delivery-tag {
            font-size: 11px;
            font-weight: 600;
            color: #7d5700;
            white-space: nowrap;
        }

        /* Action Buttons */
        .mr-rsc-card-action,
        .mr-rsc-grid-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 6px;
        }
        .mr-rsc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f58220 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            box-shadow: 0 2px 8px rgba(245, 130, 32, 0.28);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .mr-rsc-btn:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.4);
            transform: translateY(-1px);
        }
        .mr-rsc-btn:active {
            transform: scale(0.98);
        }

        /* Sample Preview Button (Present in ALL shortcodes) */
        .mr-rsc-sample-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 8px 14px;
            border-radius: 10px;
            background: #fff8f6;
            color: #964900;
            font-size: 12px;
            font-weight: 600;
            border: 1px dashed rgba(150, 73, 0, 0.35);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }
        .mr-rsc-sample-btn:hover {
            background: #ffedd5;
            color: #7a3b00;
            border-color: #f58220;
            border-style: solid;
        }

        /* Responsive Button Labels (Desktop vs Mobile) */
        .mr-rsc-btn-txt-desktop {
            display: inline;
        }
        .mr-rsc-btn-txt-mobile {
            display: none;
        }

        .mr-rsc-scroll-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #7d5700;
            margin-top: 10px;
            text-align: center;
        }
        @media (min-width: 641px) {
            .mr-rsc-scroll-hint {
                display: none;
            }
        }

        /* ==========================================================================
           2X2 VERTICAL GRID LAYOUT
           2 Columns on Desktop & Tablet, Full Details (Desktop) vs Limited (Mobile)
           ========================================================================== */
        .mr-rsc-grid-2x2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
        }
        @media (min-width: 641px) {
            .mr-rsc-grid-2x2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .mr-rsc-grid-card {
            background: #ffffff;
            border: 1px solid rgba(217, 119, 6, 0.22);
            border-radius: 18px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 18px rgba(120, 53, 15, 0.05);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .mr-rsc-grid-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(120, 53, 15, 0.12);
            border-color: rgba(245, 130, 32, 0.55);
        }

        .mr-rsc-grid-media {
            position: relative;
            display: block;
            width: 100%;
            height: 180px;
            background: #fff8f6;
            overflow: hidden;
            text-decoration: none;
        }
        @media (min-width: 641px) {
            .mr-rsc-grid-media {
                height: 210px;
            }
        }
        .mr-rsc-grid-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            display: block;
        }
        .mr-rsc-grid-card:hover .mr-rsc-grid-img {
            transform: scale(1.05);
        }

        .mr-rsc-grid-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
        }

        .mr-rsc-grid-title {
            margin: 0;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.3;
            color: #964900;
        }
        @media (min-width: 768px) {
            .mr-rsc-grid-title {
                font-size: 21px;
            }
        }
        .mr-rsc-grid-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .mr-rsc-grid-title a:hover {
            color: #f58220;
        }

        /* Desktop & Tablet Details */
        .mr-rsc-details-desktop {
            display: block;
        }
        .mr-rsc-details-mobile {
            display: none;
        }

        .mr-rsc-grid-desc-full {
            margin: 0 0 12px 0;
            font-size: 13px;
            color: #564336;
            line-height: 1.5;
        }

        .mr-rsc-highlights {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .mr-rsc-highlights li {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            color: #374151;
            line-height: 1.35;
        }

        /* Mobile View: Limited Description for fast mobile scanning */
        @media (max-width: 640px) {
            .mr-rsc-details-desktop {
                display: none !important;
            }
            .mr-rsc-details-mobile {
                display: block !important;
            }
            .mr-rsc-grid-desc-limited {
                margin: 0 0 8px 0;
                font-size: 12.5px;
                color: #564336;
                line-height: 1.45;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .mr-rsc-grid-body {
                padding: 16px;
                gap: 10px;
            }
        }

        /* ==========================================================================
           TOP SORTING & FILTERING TABS (Vedic Amber & Gold Themed)
           ========================================================================== */
        .mr-rsc-filter-bar {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .mr-rsc-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .mr-rsc-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 9999px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            color: #78350f;
            background: #ffffff;
            border: 1px solid rgba(120, 53, 15, 0.22);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .mr-rsc-tab:hover {
            background: #fff8f6;
            border-color: #f58220;
            color: #964900;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(245, 130, 32, 0.15);
        }

        .mr-rsc-tab.active {
            background: linear-gradient(135deg, #f58220 0%, #d97706 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(245, 130, 32, 0.35);
            transform: translateY(-1px);
        }

        .mr-rsc-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            background: rgba(120, 53, 15, 0.08);
            color: inherit;
        }

        .mr-rsc-tab.active .mr-rsc-tab-count {
            background: rgba(255, 255, 255, 0.28);
            color: #ffffff;
        }

        /* Sub-Filter Container & Pills */
        .mr-rsc-subfilters {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 2px;
        }

        .mr-rsc-subgroup {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(253, 246, 227, 0.95);
            border: 1px dashed rgba(217, 119, 6, 0.45);
            border-radius: 9999px;
            animation: mrRscFadeIn 0.22s ease forwards;
        }

        @keyframes mrRscFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mr-rsc-subpill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            color: #964900;
            background: #ffffff;
            border: 1px solid rgba(217, 119, 6, 0.3);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .mr-rsc-subpill:hover {
            background: #fff8f6;
            border-color: #964900;
            color: #78350f;
        }

        .mr-rsc-subpill.active {
            background: #78350f;
            color: #ffffff;
            border-color: #78350f;
            box-shadow: 0 2px 8px rgba(120, 53, 15, 0.25);
        }

/* ==========================================================================
           VERTICAL LISTING LAYOUT - 4-COLUMN COMPACT DOSSIER MATRIX
           Desktop: 4 Cards per row (Compact Publication Dossier Format)
           Tablet: 3 Cards per row (120px book cover)
           Mobile: 2 Cards per row (100px compact book cover)
           ========================================================================== */
        .mr-rsc-vertical-stack {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 16px;
        }

        /* Card Container (Base / Desktop 4-Column Matrix) */
        .mr-rsc-vert-card {
            background: #FDF6E3 !important;
            border: 1px solid rgba(217, 119, 6, 0.28) !important;
            border-radius: 14px;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(120, 53, 15, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, opacity 0.25s ease;
            box-sizing: border-box;
            height: 100%;
        }
        .mr-rsc-vert-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(120, 53, 15, 0.12);
            border-color: rgba(245, 130, 32, 0.55);
        }
        .mr-rsc-vert-card.mr-rsc-hidden {
            display: none !important;
        }

        /* ==========================================================================
           Dossier Cover Format - Compact, Centered & 3D Shadowed (Never oversized)
           ========================================================================== */
        .mr-rsc-vert-media {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 130px !important;
            max-width: 130px !important;
            min-width: 130px !important;
            height: 130px !important;
            aspect-ratio: 1 / 1 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 10px;
            overflow: visible;
            flex-shrink: 0 !important;
            align-self: center !important;
            margin: 0 auto 6px auto !important;
            padding: 0;
            box-sizing: border-box;
            text-decoration: none;
        }

        /* The Image element: publication cover with soft drop shadow */
        .mr-rsc-vert-img {
            width: 130px !important;
            height: 130px !important;
            max-width: 130px !important;
            max-height: 130px !important;
            min-width: 130px !important;
            min-height: 130px !important;
            aspect-ratio: 1 / 1 !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid rgba(217, 119, 6, 0.22) !important;
            border-radius: 10px !important;
            box-shadow: 0 6px 18px rgba(120, 53, 15, 0.16) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .mr-rsc-vert-card:hover .mr-rsc-vert-img {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 10px 24px rgba(245, 130, 32, 0.25) !important;
        }

        /* Discount Badge inside Media Container */
        .mr-rsc-vert-media .mr-rsc-badge-discount {
            top: -4px !important;
            right: -6px !important;
            font-size: 9px !important;
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-weight: 800 !important;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4) !important;
            z-index: 2;
        }

        /* Body column */
        .mr-rsc-vert-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 6px;
            min-width: 0;
            background: transparent !important;
        }

        .mr-rsc-vert-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .mr-rsc-vert-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            background: rgba(254, 243, 199, 0.85);
            color: #92400e;
            white-space: nowrap;
        }

        /* Heading Bold for Desktop 4-Column Matrix (15.5-16.5px) */
        .mr-rsc-vert-title {
            margin: 0;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 15.5px !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            color: #964900;
            text-align: center;
            letter-spacing: -0.01em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }
        @media (min-width: 1400px) {
            .mr-rsc-vert-title {
                font-size: 16.5px !important;
            }
        }
        .mr-rsc-vert-title a {
            color: inherit;
            font-weight: 800;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .mr-rsc-vert-title a:hover {
            color: #f58220;
        }

        .mr-rsc-vert-desc {
            margin: 0;
            font-size: 11.5px !important;
            color: #564336;
            line-height: 1.35 !important;
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 2.7em;
        }

        .mr-rsc-vert-price-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 6px;
            padding-top: 5px;
            border-top: 1px solid rgba(120, 53, 15, 0.12);
            margin-top: auto;
        }
        .mr-rsc-vert-price-row .mr-rsc-price-sale {
            font-size: 16px !important;
            font-weight: 800;
            color: #964900;
        }
        .mr-rsc-vert-price-row .mr-rsc-price-reg {
            font-size: 11.5px !important;
            color: #9ca3af;
            text-decoration: line-through;
            margin-left: 3px;
        }
        .mr-rsc-vert-price-row .mr-rsc-delivery-tag {
            font-size: 10px !important;
        }

        .mr-rsc-vert-btns-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 4px;
        }
        .mr-rsc-btn-vert {
            width: 100%;
            padding: 8px 10px !important;
            font-size: 11.5px !important;
            font-weight: 700;
            border-radius: 7px !important;
            text-align: center;
            justify-content: center;
        }
        .mr-rsc-sample-btn-vert {
            width: 100%;
            padding: 6px 8px !important;
            font-size: 10.5px !important;
            font-weight: 600;
            border-radius: 6px !important;
            text-align: center;
            justify-content: center;
        }

        /* Tablet / Medium Screens (641px - 1023px): 3 columns */
        @media (max-width: 1023px) and (min-width: 641px) {
            .mr-rsc-vertical-stack {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 14px;
            }
            .mr-rsc-vert-card {
                padding: 12px 10px;
            }
            .mr-rsc-vert-media {
                width: 120px !important;
                max-width: 120px !important;
                height: 120px !important;
            }
            .mr-rsc-vert-img {
                width: 120px !important;
                height: 120px !important;
                max-width: 120px !important;
                max-height: 120px !important;
            }
            .mr-rsc-vert-title {
                font-size: 15px !important;
            }
        }

        /* ==========================================================================
           MOBILE 2X2 MATRIX GRID (Screens <= 640px)
           Compact Book Covers (100px), Perfectly Fitted, No Giant Images
           ========================================================================== */
        @media (max-width: 640px) {
            /* Maximize available screen width - eliminate excessive side padding */
            .mr-rsc-wrapper {
                padding: 10px 4px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .mr-rsc-filter-bar {
                margin-bottom: 14px !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .mr-rsc-tabs {
                gap: 6px !important;
                justify-content: flex-start !important;
                overflow-x: auto !important;
                flex-wrap: nowrap !important;
                padding: 2px 2px 6px 2px !important;
                -webkit-overflow-scrolling: touch !important;
                scrollbar-width: none !important;
            }
            .mr-rsc-tabs::-webkit-scrollbar {
                display: none !important;
            }

            .mr-rsc-tab {
                padding: 6px 12px !important;
                font-size: 12px !important;
                flex-shrink: 0 !important;
            }
            .mr-rsc-tab-count {
                min-width: 18px !important;
                height: 18px !important;
                font-size: 10px !important;
            }

            .mr-rsc-subgroup {
                padding: 4px 8px !important;
                gap: 5px !important;
                overflow-x: auto !important;
                flex-wrap: nowrap !important;
                scrollbar-width: none !important;
            }
            .mr-rsc-subgroup::-webkit-scrollbar {
                display: none !important;
            }

            .mr-rsc-subpill {
                padding: 4px 10px !important;
                font-size: 11px !important;
                flex-shrink: 0 !important;
            }

            .mr-rsc-vertical-stack {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .mr-rsc-vert-card {
                padding: 8px 6px !important;
                gap: 6px !important;
                border-radius: 10px !important;
                flex-direction: column !important;
                align-items: stretch !important;
                justify-content: space-between !important;
                height: 100% !important;
                background: #FDF6E3 !important;
                box-sizing: border-box !important;
            }

            /* Compact 100px centered book cover on mobile (never oversized) */
            .mr-rsc-vert-media {
                width: 100px !important;
                max-width: 100px !important;
                min-width: 100px !important;
                height: 100px !important;
                aspect-ratio: 1 / 1 !important;
                margin: 0 auto 4px auto !important;
                align-self: center !important;
                border-radius: 8px !important;
                overflow: visible !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }

            .mr-rsc-vert-img {
                width: 100px !important;
                height: 100px !important;
                max-width: 100px !important;
                max-height: 100px !important;
                aspect-ratio: 1 / 1 !important;
                object-fit: cover !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 12px rgba(120, 53, 15, 0.15) !important;
                border: 1px solid rgba(217, 119, 6, 0.2) !important;
            }

            .mr-rsc-vert-media .mr-rsc-badge-discount {
                top: -3px !important;
                right: -4px !important;
                font-size: 8.5px !important;
                padding: 2px 5px !important;
                border-radius: 4px !important;
                font-weight: 800 !important;
            }

            .mr-rsc-vert-body {
                width: 100% !important;
                gap: 5px !important;
                flex-grow: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .mr-rsc-vert-meta {
                gap: 4px !important;
                margin-bottom: 1px !important;
            }
            .mr-rsc-vert-tag {
                font-size: 9px !important;
                padding: 1px 5px !important;
                border-radius: 4px !important;
            }

            /* 2-line title in bold, neat font size for 2-column mobile matrix */
            .mr-rsc-vert-title {
                font-size: 13px !important;
                font-weight: 800 !important;
                line-height: 1.22 !important;
                margin: 0 !important;
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                min-height: 2.45em; /* Ensures title height consistency */
            }

            /* 4 lines of description with ellipsis (...) */
            .mr-rsc-vert-desc {
                display: -webkit-box !important;
                -webkit-line-clamp: 4 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                font-size: 11px !important;
                line-height: 1.35 !important;
                color: #564336 !important;
                margin: 3px 0 4px 0 !important;
                word-break: break-word !important;
            }

            .mr-rsc-vert-price-row {
                padding-top: 4px !important;
                margin-top: auto !important;
                border-top: 1px solid rgba(120, 53, 15, 0.1) !important;
                display: flex !important;
                align-items: baseline !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
                gap: 4px !important;
            }
            .mr-rsc-vert-price-row .mr-rsc-price-sale {
                font-size: 14.5px !important;
                font-weight: 800 !important;
            }
            .mr-rsc-vert-price-row .mr-rsc-price-reg {
                font-size: 11px !important;
                margin-left: 3px !important;
            }
            .mr-rsc-vert-price-row .mr-rsc-delivery-tag {
                display: none !important;
            }

            /* Toggle responsive button text */
            .mr-rsc-btn-txt-desktop {
                display: none !important;
            }
            .mr-rsc-btn-txt-mobile {
                display: inline !important;
            }

            /* Compact thumb-friendly action buttons - single neat line */
            .mr-rsc-vert-btns-wrap {
                display: flex !important;
                flex-direction: column !important;
                gap: 4px !important;
                margin-top: 4px !important;
            }
            .mr-rsc-btn-vert {
                padding: 7px 4px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                line-height: 1.2 !important;
                border-radius: 6px !important;
                justify-content: center !important;
                white-space: nowrap !important;
                letter-spacing: -0.01em !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 4px !important;
                box-sizing: border-box !important;
            }
            .mr-rsc-btn-vert svg {
                width: 12px !important;
                height: 12px !important;
                flex-shrink: 0 !important;
            }
            .mr-rsc-sample-btn-vert {
                padding: 5px 4px !important;
                font-size: 10px !important;
                font-weight: 600 !important;
                line-height: 1.2 !important;
                border-radius: 6px !important;
                justify-content: center !important;
                white-space: nowrap !important;
                letter-spacing: -0.01em !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 4px !important;
                box-sizing: border-box !important;
            }
            .mr-rsc-sample-btn-vert svg {
                width: 11px !important;
                height: 11px !important;
                flex-shrink: 0 !important;
            }
        }

        /* Extra small phones (<= 360px) */
        @media (max-width: 360px) {
            .mr-rsc-wrapper {
                padding: 8px 2px !important;
            }
            .mr-rsc-vertical-stack {
                gap: 6px !important;
            }
            .mr-rsc-vert-card {
                padding: 6px 4px !important;
            }
            .mr-rsc-vert-title {
                font-size: 12px !important;
            }
            .mr-rsc-vert-desc {
                font-size: 10px !important;
                line-height: 1.3 !important;
                -webkit-line-clamp: 3 !important;
            }
            .mr-rsc-btn-vert {
                font-size: 10px !important;
                padding: 6px 3px !important;
            }
            .mr-rsc-sample-btn-vert {
                font-size: 9px !important;
                padding: 4px 3px !important;
            }
        }

        /* ==========================================================================
           MODAL SAMPLE PREVIEW VIEWER (Interactive on ALL pages)
           ========================================================================== */
        .mr-rsc-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .mr-rsc-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
        }
        .mr-rsc-modal-box {
            position: relative;
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            height: 90vh;
            max-height: 850px;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 10;
        }
        .mr-rsc-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: #FFFBEB;
            border-bottom: 1px solid #FDE68A;
            flex-shrink: 0;
        }
        .mr-rsc-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mr-rsc-modal-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #FEF3C7;
            color: #92400E;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .mr-rsc-modal-heading {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #964900;
            font-family: "Playfair Display", Georgia, serif;
        }
        .mr-rsc-modal-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .mr-rsc-modal-cta {
            background: linear-gradient(135deg, #f58220 0%, #ea580c 100%);
            color: #ffffff !important;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
        }
        .mr-rsc-modal-cta:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        }
        .mr-rsc-modal-close {
            background: transparent;
            border: none;
            font-size: 26px;
            line-height: 1;
            color: #78350F;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 6px;
            transition: background 0.15s ease;
        }
        .mr-rsc-modal-close:hover {
            background: rgba(120, 53, 15, 0.1);
        }
        .mr-rsc-modal-body {
            position: relative;
            flex-grow: 1;
            width: 100%;
            background: #f8fafc;
        }
        .mr-rsc-modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        .mr-rsc-modal-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            z-index: 5;
            color: #964900;
            font-size: 14px;
            font-weight: 600;
        }
        .mr-rsc-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid #FEF3C7;
            border-top-color: #F58220;
            border-radius: 50%;
            animation: mr-rsc-spin 0.8s linear infinite;
            margin-bottom: 12px;
        }
        @keyframes mr-rsc-spin {
            to { transform: rotate(360deg); }
        }
        .mr-rsc-modal-footer {
            padding: 10px 18px;
            background: #FFFBEB;
            border-top: 1px solid #FDE68A;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .mr-rsc-modal-note {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #78350F;
            font-weight: 500;
            text-align: center;
        }
        </style>
        <?php
    }
}
