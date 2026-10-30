<?php
/**
 * Report Sales Cards & Catalog Customization Manager
 * Handles admin configuration, media changer, badge assignment, ordering, and frontend sales grid rendering.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Catalog_Manager {

    const OPTION_KEY = 'mr_catalog_cards_settings';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_submenu'], 35);
        add_action('admin_post_mr_save_catalog_cards', [__CLASS__, 'handle_save_cards']);
        add_action('admin_post_mr_reset_catalog_cards', [__CLASS__, 'handle_reset_cards']);

        // Register Sales Grid Shortcode
        add_shortcode('mr_reports_sales_grid', [__CLASS__, 'render_sales_grid']);
        add_shortcode('mr_reports', [__CLASS__, 'render_sales_grid']); // Compatibility for existing /reports page

        // Catalog Page SEO & Meta Head Injection
        add_action('wp_head', [__CLASS__, 'inject_catalog_seo_head'], 1);
        add_filter('pre_get_document_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('document_title_parts', [__CLASS__, 'filter_document_title_parts'], 99);
        add_filter('wpseo_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_metadesc', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_canonical', [__CLASS__, 'filter_canonical_url'], 99);
        add_filter('wpseo_robots', [__CLASS__, 'filter_robots'], 99);
        add_filter('wpseo_opengraph_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_opengraph_desc', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_opengraph_url', [__CLASS__, 'filter_canonical_url'], 99);
        add_filter('wpseo_opengraph_site_name', function() { return 'MeraRashifal'; }, 99);
        add_filter('wpseo_twitter_title', [__CLASS__, 'filter_document_title'], 99);
        add_filter('wpseo_twitter_description', [__CLASS__, 'filter_meta_description'], 99);
        add_filter('wpseo_json_ld_output', function($data) {
            if (self::is_catalog_page()) {
                return false;
            }
            return $data;
        }, 99);
    }

    public static function register_admin_submenu() {
        $hook = add_submenu_page(
            'mr-subscriptions',
            __('Sales Cards & Catalog Manager', 'merarashifal'),
            __('Sales Cards Manager', 'merarashifal'),
            'manage_options',
            'mr-reports-catalog-manager',
            [__CLASS__, 'render_admin_page']
        );

        add_action('admin_print_scripts-' . $hook, function() {
            wp_enqueue_media();
        });
    }

    /**
     * List of supported single badges
     */
    public static function get_badge_options() {
        return [
            'none'         => __('None (No Badge)', 'merarashifal'),
            'best_seller'  => __('Best Seller (Red-Orange Ribbon)', 'merarashifal'),
            'top_selling'  => __('Top Selling (Amber-Gold Ribbon)', 'merarashifal'),
            'new_report'   => __('New Report (Emerald Green Ribbon)', 'merarashifal'),
            'in_demand'    => __('In Demand (Crimson Coral Ribbon)', 'merarashifal'),
            'for_you'      => __('For You (Sky Blue Ribbon)', 'merarashifal'),
            'recommended'  => __('Recommended (Royal Purple Ribbon)', 'merarashifal'),
        ];
    }

    /**
     * Badge CSS and Label lookup
     */
    public static function get_badge_data($badge_key) {
        $badges = [
            'best_seller' => [
                'label' => 'BEST SELLER',
                'class' => 'mr-badge-best-seller',
            ],
            'top_selling' => [
                'label' => 'TOP SELLING',
                'class' => 'mr-badge-top-selling',
            ],
            'new_report' => [
                'label' => 'NEW REPORT',
                'class' => 'mr-badge-new-report',
            ],
            'in_demand' => [
                'label' => 'IN DEMAND',
                'class' => 'mr-badge-in-demand',
            ],
            'for_you' => [
                'label' => 'FOR YOU',
                'class' => 'mr-badge-for-you',
            ],
            'recommended' => [
                'label' => 'RECOMMENDED',
                'class' => 'mr-badge-recommended',
            ],
        ];

        return isset($badges[$badge_key]) ? $badges[$badge_key] : null;
    }

    /**
     * Default cards structure matching the user's design image
     */
    public static function get_default_cards() {
        $img_base = MR_PLUGIN_URL . 'assets/images/reports/';

        return [
            'yearly_horoscope_long' => [
                'id'             => 'yearly_horoscope_long',
                'code'           => 'MR_YHLR',
                'order'          => 1,
                'name'           => 'Yearly Horoscope Detailed Long Report',
                'sub_text'       => 'Comprehensive 26-page 12-month Vedic Varshaphal forecast covering career, wealth, relationships and finances.',
                'category'       => 'Horoscope',
                'badge'          => 'best_seller',
                'pages_tag'      => '26 Pages',
                'image_url'      => $img_base . 'yearly_horoscope_long.png',
                'highlights'     => [
                    '12 Month Predictions',
                    'Career, Love, Finance',
                    'Health & Travel',
                    'Lucky Dates & Remedies'
                ],
                'price_sale'     => 299,
                'price_regular'  => 1499,
                'discount_text'  => 'Save 80%',
                'price_note'     => 'Only Today',
                'button_text'    => 'Get My Personalized Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'yearly_horoscope_vedic' => [
                'id'             => 'yearly_horoscope_vedic',
                'code'           => 'MR_YHSR',
                'order'          => 2,
                'name'           => 'Yearly Horoscope Short Report (2026)',
                'sub_text'       => 'Essential 5-page annual overview with key planetary transits and auspicious timing windows.',
                'category'       => 'Horoscope',
                'badge'          => 'none',
                'pages_tag'      => '5 Pages (Free)',
                'image_url'      => $img_base . 'yearly_horoscope_vedic.png',
                'highlights'     => [
                    'Key Predictions',
                    'Major Planetary Transits',
                    'Important Dates',
                    'Quick Guidance'
                ],
                'price_sale'     => 0,
                'price_regular'  => 499,
                'discount_text'  => '100% FREE',
                'price_note'     => '',
                'button_text'    => 'Get Free Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'kundli_predictions' => [
                'id'             => 'kundli_predictions',
                'code'           => 'MR_JKHR',
                'order'          => 3,
                'name'           => 'Master Janma Kundali & Harmonics Report',
                'sub_text'       => 'Full 26-page birth chart analysis, 16 Shodashvarga harmonics, Shadbala metrics, yogas and lifetime trajectory.',
                'category'       => 'Kundali',
                'badge'          => 'none',
                'pages_tag'      => '26 Pages',
                'image_url'      => $img_base . 'kundli_predictions.png',
                'highlights'     => [
                    'Detailed Birth Chart',
                    '16 Divisional Charts',
                    'Yogas & Doshas',
                    'Life Path Analysis'
                ],
                'price_sale'     => 99,
                'price_regular'  => 999,
                'discount_text'  => 'Save 90%',
                'price_note'     => '',
                'button_text'    => 'Generate My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'mahadasha_timing' => [
                'id'             => 'mahadasha_timing',
                'code'           => 'MR_VDLT',
                'order'          => 4,
                'name'           => 'Master Vedic Dasha Life Timing Report',
                'sub_text'       => 'Deep dive into your active Vimshottari Dasha cycles and event timing predictions.',
                'category'       => 'Dasha',
                'badge'          => 'none',
                'pages_tag'      => '26 Pages',
                'image_url'      => $img_base . 'mahadasha_timing.png',
                'highlights'     => [
                    'Current & Future Dasha',
                    'Major Life Events',
                    'Career & Finance Timing',
                    'Remedies for Challenges'
                ],
                'price_sale'     => 99,
                'price_regular'  => 999,
                'discount_text'  => 'Save 90%',
                'price_note'     => '',
                'button_text'    => 'Generate My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'dosh_remedial' => [
                'id'             => 'dosh_remedial',
                'code'           => 'MR_KDRA',
                'order'          => 5,
                'name'           => 'Master Kundali Dosh & Remedial Audit Report',
                'sub_text'       => 'Clinical analysis of Manglik, Kaal Sarp, Pitra, or Sade Sati doshas with personalized Shastric remedies.',
                'category'       => 'Kundali',
                'badge'          => 'none',
                'pages_tag'      => '20 Pages',
                'image_url'      => $img_base . 'dosh_remedial.png',
                'highlights'     => [
                    'Detailed Dosh Analysis',
                    'Impact on Life',
                    'Remedies & Upayas',
                    'Expert Guidance'
                ],
                'price_sale'     => 99,
                'price_regular'  => 799,
                'discount_text'  => 'Save 88%',
                'price_note'     => '',
                'button_text'    => 'Generate My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'kundli_matching' => [
                'id'             => 'kundli_matching',
                'code'           => 'MR_KMMR',
                'order'          => 6,
                'name'           => 'Master Kundali Matching (Gun Milan) Report',
                'sub_text'       => '24-page Ashtakoot matching with 36 Gunas, Manglik compatibility, Papasamaya load balance and more.',
                'category'       => 'Matching',
                'badge'          => 'none',
                'pages_tag'      => '24 Pages',
                'image_url'      => $img_base . 'kundli_matching.png',
                'highlights'     => [
                    '36 Guna Matching',
                    'Manglik Analysis',
                    'Relationship Compatibility',
                    'Strengths & Challenges'
                ],
                'price_sale'     => 99,
                'price_regular'  => 1499,
                'discount_text'  => 'Save 93%',
                'price_note'     => '',
                'button_text'    => 'Generate My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'numerology' => [
                'id'             => 'numerology',
                'code'           => 'MR_NMVI',
                'order'          => 7,
                'name'           => 'Master Numerology & Vedic Integration Report',
                'sub_text'       => '22-page Sankhya Shastra audit: Lo Shu 3x3 energy grid, core life paths, 8-1-6 Practical Arrow and 90-day roadmap.',
                'category'       => 'Numerology',
                'badge'          => 'none',
                'pages_tag'      => '22 Pages',
                'image_url'      => $img_base . 'numerology.png',
                'highlights'     => [
                    'Lo Shu Grid Analysis',
                    'Life Path & Destiny Number',
                    'Strengths & Weaknesses',
                    'Personalized Guidance'
                ],
                'price_sale'     => 99,
                'price_regular'  => 599,
                'discount_text'  => 'Save 83%',
                'price_note'     => '',
                'button_text'    => 'Generate My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'monthly_horoscope_vedic' => [
                'id'             => 'monthly_horoscope_vedic',
                'code'           => 'MR_MHTS',
                'order'          => 8,
                'name'           => 'Monthly Horoscope & Transit Subscription',
                'sub_text'       => 'Detailed 13-page month-by-month planetary transits, auspicious days, sector ratings and guidance.',
                'category'       => 'Transit',
                'badge'          => 'none',
                'pages_tag'      => '13 Pages',
                'image_url'      => $img_base . 'monthly_horoscope_vedic.png',
                'highlights'     => [
                    'Monthly Predictions',
                    'Planetary Transits',
                    'Auspicious Dates',
                    'Actionable Guidance'
                ],
                'price_sale'     => 99,
                'price_regular'  => 499,
                'discount_text'  => 'Save 80%',
                'price_note'     => '',
                'button_text'    => 'Get My Report →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
            'palmistry_report' => [
                'id'             => 'palmistry_report',
                'code'           => 'MR_PLMR',
                'order'          => 9,
                'name'           => 'Master Hast Rekha & Palmistry Analysis Report',
                'sub_text'       => 'Comprehensive 26-page Hast Rekha & Samudrik Shastra analysis: Major lines, Mounts, Career, Wealth & 5-phase age timeline.',
                'category'       => 'Palmistry',
                'badge'          => 'new_report',
                'pages_tag'      => '26 Pages',
                'image_url'      => $img_base . 'palmistry_report.png',
                'highlights'     => [
                    'Heart, Head, Life & Fate Lines',
                    'Vedic Mounts & Energy Topography',
                    'Career & Wealth Breakthrough Timing',
                    'Personalized Upay & Age Roadmap'
                ],
                'price_sale'     => 299,
                'price_regular'  => 1499,
                'discount_text'  => 'Save 80%',
                'price_note'     => 'Only Today',
                'button_text'    => 'Get My Palm Reading →',
                'preview_text'   => 'Preview Sample Report',
                'is_active'      => 1,
            ],
        ];
    }

    /**
     * Retrieve all cards sorted by display order
     */
    public static function get_all_cards() {
        $saved = get_option(self::OPTION_KEY, null);
        $defaults = self::get_default_cards();

        if (!is_array($saved) || empty($saved)) {
            $saved = $defaults;
            update_option(self::OPTION_KEY, $saved);
        } else {
            // Ensure any new default cards like palmistry_report are present
            foreach ($defaults as $dk => $dv) {
                if (!isset($saved[$dk])) {
                    $saved[$dk] = $dv;
                }
            }
        }

        // Ensure testimonials exist on every card
        if (class_exists('MR_Report_Landing_Data')) {
            foreach ($saved as $k => &$c) {
                if (!isset($c['testimonials']) || !is_array($c['testimonials'])) {
                    $def = MR_Report_Landing_Data::get($k);
                    $c['testimonials'] = $def['testimonials'] ?? [];
                }
            }
            unset($c);
        }

        // Normalize preview_text across all cards
        foreach ($saved as $k => &$c) {
            if (empty($c['preview_text']) || stripos($c['preview_text'], 'Sample Pages') !== false) {
                $c['preview_text'] = 'Preview Sample Report';
            }
        }
        unset($c);

        // Live synchronize with Report Pricing & Catalog config if defined
        if (function_exists('mr_get_report_catalog_config')) {
            $cat_config = mr_get_report_catalog_config();
            foreach ($saved as $k => &$c) {
                if (isset($cat_config[$k])) {
                    if (isset($cat_config[$k]['sale_price'])) {
                        $c['price_sale'] = $cat_config[$k]['sale_price'];
                    }
                    if (isset($cat_config[$k]['list_price'])) {
                        $c['price_regular'] = $cat_config[$k]['list_price'];
                        if ($c['price_regular'] > $c['price_sale'] && $c['price_regular'] > 0) {
                            $pct = round((($c['price_regular'] - $c['price_sale']) / $c['price_regular']) * 100);
                            $c['discount_text'] = 'Save ' . $pct . '%';
                        }
                    }
                    if (isset($cat_config[$k]['active'])) {
                        $c['is_active'] = $cat_config[$k]['active'];
                    }
                    if (!empty($cat_config[$k]['name'])) {
                        $c['name'] = $cat_config[$k]['name'];
                    }
                    if (!empty($cat_config[$k]['pages'])) {
                        $c['pages_tag'] = $cat_config[$k]['pages'];
                    }
                }
            }
            unset($c);
        }

        // Sort by order ascending
        uasort($saved, function($a, $b) {
            $oa = isset($a['order']) ? (int)$a['order'] : 999;
            $ob = isset($b['order']) ? (int)$b['order'] : 999;
            return $oa - $ob;
        });

        return $saved;
    }

    /**
     * Handle Admin Form Save
     */
    public static function handle_save_cards() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied.', 'merarashifal'));
        }

        check_admin_referer('mr_save_catalog_cards_nonce', 'mr_nonce');

        $raw_cards = isset($_POST['mr_cards']) && is_array($_POST['mr_cards']) ? $_POST['mr_cards'] : [];
        $existing = self::get_all_cards();
        $updated = [];

        foreach ($raw_cards as $slug => $data) {
            $slug = sanitize_key($slug);
            $highlights = [];
            if (!empty($data['highlights']) && is_array($data['highlights'])) {
                foreach ($data['highlights'] as $hl) {
                    $hl_clean = sanitize_text_field(trim($hl));
                    if ($hl_clean !== '') {
                        $highlights[] = $hl_clean;
                    }
                }
            }

            // Ensure strictly one badge
            $badge = sanitize_key(isset($data['badge']) ? $data['badge'] : 'none');
            $valid_badges = array_keys(self::get_badge_options());
            if (!in_array($badge, $valid_badges, true)) {
                $badge = 'none';
            }

            // Sanitize testimonials (What Seekers Are Saying)
            $testimonials = [];
            if (!empty($data['testimonials']) && is_array($data['testimonials'])) {
                foreach ($data['testimonials'] as $t) {
                    $quote  = sanitize_textarea_field(trim($t['quote'] ?? ''));
                    $name   = sanitize_text_field(trim($t['name'] ?? ''));
                    $loc    = sanitize_text_field(trim($t['loc'] ?? ''));
                    $rating = isset($t['rating']) ? max(1, min(5, (int)$t['rating'])) : 5;
                    if (!empty($quote) || !empty($name)) {
                        $testimonials[] = [
                            'quote'  => $quote,
                            'name'   => $name,
                            'loc'    => $loc,
                            'rating' => $rating,
                        ];
                    }
                }
            }

            $updated[$slug] = [
                'id'             => $slug,
                'code'           => sanitize_text_field(isset($data['code']) ? $data['code'] : ''),
                'order'          => isset($data['order']) ? (int)$data['order'] : 10,
                'name'           => sanitize_text_field(isset($data['name']) ? $data['name'] : ''),
                'sub_text'       => sanitize_textarea_field(isset($data['sub_text']) ? $data['sub_text'] : ''),
                'category'       => sanitize_text_field(isset($data['category']) ? $data['category'] : 'Horoscope'),
                'badge'          => $badge,
                'pages_tag'      => sanitize_text_field(isset($data['pages_tag']) ? $data['pages_tag'] : ''),
                'image_url'      => esc_url_raw(isset($data['image_url']) ? $data['image_url'] : ''),
                'highlights'     => $highlights,
                'price_sale'     => isset($data['price_sale']) ? floatval($data['price_sale']) : 0,
                'price_regular'  => isset($data['price_regular']) ? floatval($data['price_regular']) : 0,
                'discount_text'  => sanitize_text_field(isset($data['discount_text']) ? $data['discount_text'] : ''),
                'price_note'     => sanitize_text_field(isset($data['price_note']) ? $data['price_note'] : ''),
                'button_text'    => sanitize_text_field(isset($data['button_text']) ? $data['button_text'] : 'Generate My Report →'),
                'preview_text'   => sanitize_text_field(isset($data['preview_text']) ? $data['preview_text'] : 'Preview Sample Report'),
                'is_active'      => !empty($data['is_active']) ? 1 : 0,
                'testimonials'   => $testimonials,
            ];
        }

        // Merge any cards not posted to preserve integrity
        foreach ($existing as $ex_slug => $ex_data) {
            if (!isset($updated[$ex_slug])) {
                $updated[$ex_slug] = $ex_data;
            }
        }

        update_option(self::OPTION_KEY, $updated);

        wp_redirect(add_query_arg(['page' => 'mr-reports-catalog-manager', 'saved' => '1'], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Reset to Defaults
     */
    public static function handle_reset_cards() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied.', 'merarashifal'));
        }

        check_admin_referer('mr_reset_catalog_cards_nonce', 'mr_nonce');

        update_option(self::OPTION_KEY, self::get_default_cards());

        wp_redirect(add_query_arg(['page' => 'mr-reports-catalog-manager', 'reset' => '1'], admin_url('admin.php')));
        exit;
    }

    /**
     * Render Admin Manager Dashboard Page
     */
    public static function render_admin_page() {
        $cards = self::get_all_cards();
        $badge_options = self::get_badge_options();
        $categories = ['Horoscope', 'Kundali', 'Matching', 'Dasha', 'Numerology', 'Transit'];
        $sales_page_url = home_url('/reports-catalog/');
        ?>
        <div class="wrap mr-admin-catalog-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-grid-view" style="font-size:28px; line-height:30px; margin-right:8px; color:#f59e0b;"></span>
                <?php _e('Report Sales Cards & Catalog Customizer', 'merarashifal'); ?>
            </h1>

            <a href="<?php echo esc_url($sales_page_url); ?>" target="_blank" class="page-title-action" style="background:#10b981; color:#fff; border-color:#059669; font-weight:600;">
                <span class="dashicons dashicons-external" style="font-size:16px; vertical-align:middle; line-height:1.2;"></span>
                <?php _e('View Live Sales Page', 'merarashifal'); ?>
            </a>

            <?php if (isset($_GET['saved'])): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981;">
                    <p><strong><?php _e('✓ Report card settings saved successfully!', 'merarashifal'); ?></strong> The sales page has been updated with your new images, order, badges, and pricing.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['reset'])): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong><?php _e('✓ Cards reset to default layout!', 'merarashifal'); ?></strong> All 8 astrology reports restored to default image covers, ordering, and copy.</p>
                </div>
            <?php endif; ?>

            <div class="mr-admin-intro-box">
                <div class="mr-admin-intro-col">
                    <h3><?php _e('Customize Sales Page Report Cards', 'merarashifal'); ?></h3>
                    <p><?php _e('Configure the title, sub-text, cover image, order priority, pricing, and promotional badges (Best Seller, Top Selling, New Report, In Demand, For You, Recommended). Exactly one badge can be assigned per card.', 'merarashifal'); ?></p>
                </div>
                <div class="mr-admin-intro-actions">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Are you sure you want to reset all cards to default values? Any custom text/images will be overwritten.');">
                        <?php wp_nonce_field('mr_reset_catalog_cards_nonce', 'mr_nonce'); ?>
                        <input type="hidden" name="action" value="mr_reset_catalog_cards">
                        <button type="submit" class="button button-secondary" style="color:#ef4444; border-color:#fca5a5;">
                            <span class="dashicons dashicons-image-rotate" style="vertical-align:middle;"></span> <?php _e('Reset to Defaults', 'merarashifal'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mr-cards-form">
                <?php wp_nonce_field('mr_save_catalog_cards_nonce', 'mr_nonce'); ?>
                <input type="hidden" name="action" value="mr_save_catalog_cards">

                <div class="mr-cards-accordion">
                    <?php 
                    $idx = 0;
                    foreach ($cards as $slug => $card): 
                        $idx++;
                        $badge_data = self::get_badge_data($card['badge']);
                        $badge_label = $badge_data ? $badge_data['label'] : 'No Badge';
                    ?>
                        <div class="mr-card-editor-card" data-slug="<?php echo esc_attr($slug); ?>">
                            
                            <!-- Card Header Bar -->
                            <div class="mr-card-editor-head">
                                <div class="mr-card-head-left">
                                    <span class="mr-order-badge">#<?php echo esc_html($card['order']); ?></span>
                                    <span class="mr-code-tag"><?php echo esc_html($card['code']); ?></span>
                                    <strong class="mr-card-name-label"><?php echo esc_html($card['name']); ?></strong>
                                    <span class="mr-cat-pill-admin"><?php echo esc_html($card['category']); ?></span>
                                    <?php if ($card['badge'] !== 'none'): ?>
                                        <span class="mr-badge-preview-tag <?php echo esc_attr($badge_data['class']); ?>"><?php echo esc_html($badge_label); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mr-card-head-right">
                                    <span class="mr-price-tag">₹<?php echo esc_html($card['price_sale']); ?> <del>₹<?php echo esc_html($card['price_regular']); ?></del></span>
                                    <span class="mr-status-pill <?php echo !empty($card['is_active']) ? 'active' : 'hidden'; ?>">
                                        <?php echo !empty($card['is_active']) ? '● Active' : '○ Hidden'; ?>
                                    </span>
                                    <button type="button" class="button mr-btn-toggle-card">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Body / Edit Fields -->
                            <div class="mr-card-editor-body">
                                <div class="mr-editor-grid">
                                    
                                    <!-- Left Column: Thumbnail Image & Media Uploader -->
                                    <div class="mr-editor-col-image">
                                        <label><strong><?php _e('Card Cover Image', 'merarashifal'); ?></strong></label>
                                        <div class="mr-thumb-container">
                                            <img src="<?php echo esc_url($card['image_url']); ?>" alt="<?php echo esc_attr($card['name']); ?>" class="mr-thumb-preview js-thumb-preview">
                                        </div>
                                        <div class="mr-image-input-wrap">
                                            <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][image_url]" value="<?php echo esc_url($card['image_url']); ?>" class="regular-text js-image-url" style="width:100%; margin-bottom:8px;" placeholder="https://...">
                                            <button type="button" class="button button-secondary js-upload-btn" style="width:100%;">
                                                <span class="dashicons dashicons-upload" style="vertical-align:middle;"></span> <?php _e('Choose / Upload Image', 'merarashifal'); ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Right Column: Card Attributes -->
                                    <div class="mr-editor-col-fields">
                                        
                                        <!-- Row 1: Order, Code, Active -->
                                        <div class="mr-form-row-3">
                                            <div>
                                                <label><strong><?php _e('Display Order', 'merarashifal'); ?>:</strong></label>
                                                <input type="number" name="mr_cards[<?php echo esc_attr($slug); ?>][order]" value="<?php echo esc_attr($card['order']); ?>" class="small-text" min="1" max="99" required>
                                                <p class="description"><?php _e('Lower numbers appear first', 'merarashifal'); ?></p>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Internal Code', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][code]" value="<?php echo esc_attr($card['code']); ?>" class="regular-text" style="width:120px;" placeholder="MR_XXXX" required>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Promotion Badge (Only 1 allowed)', 'merarashifal'); ?>:</strong></label>
                                                <select name="mr_cards[<?php echo esc_attr($slug); ?>][badge]" class="mr-badge-select">
                                                    <?php foreach ($badge_options as $b_key => $b_label): ?>
                                                        <option value="<?php echo esc_attr($b_key); ?>" <?php selected($card['badge'], $b_key); ?>>
                                                            <?php echo esc_html($b_label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Visibility', 'merarashifal'); ?>:</strong></label>
                                                <label style="display:block; margin-top:5px;">
                                                    <input type="checkbox" name="mr_cards[<?php echo esc_attr($slug); ?>][is_active]" value="1" <?php checked((int)$card['is_active'], 1); ?>>
                                                    <?php _e('Show on Sales Page', 'merarashifal'); ?>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Row 2: Name, Category, Pages Tag -->
                                        <div class="mr-form-row-3">
                                            <div style="flex:2;">
                                                <label><strong><?php _e('Report Title / Name', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][name]" value="<?php echo esc_attr($card['name']); ?>" class="large-text" required>
                                            </div>
                                            <div style="flex:1;">
                                                <label><strong><?php _e('Filter Category', 'merarashifal'); ?>:</strong></label>
                                                <select name="mr_cards[<?php echo esc_attr($slug); ?>][category]">
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($card['category'], $cat); ?>><?php echo esc_html($cat); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div style="flex:1;">
                                                <label><strong><?php _e('Page Count Tag', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][pages_tag]" value="<?php echo esc_attr($card['pages_tag']); ?>" class="regular-text" placeholder="e.g. 26 Pages" required>
                                            </div>
                                        </div>

                                        <!-- Row 3: Subtext / Short description -->
                                        <div style="margin-bottom:12px;">
                                            <label><strong><?php _e('Subtitle / Short Description', 'merarashifal'); ?>:</strong></label>
                                            <textarea name="mr_cards[<?php echo esc_attr($slug); ?>][sub_text]" rows="2" class="large-text" required><?php echo esc_textarea($card['sub_text']); ?></textarea>
                                        </div>

                                        <!-- Row 4: 4 Feature Highlights / Bullets -->
                                        <div style="margin-bottom:12px;">
                                            <label><strong><?php _e('Key Feature Highlights (4 Bullets with Checkmarks)', 'merarashifal'); ?>:</strong></label>
                                            <div class="mr-bullets-grid">
                                                <?php for ($i = 0; $i < 4; $i++): 
                                                    $val = isset($card['highlights'][$i]) ? $card['highlights'][$i] : '';
                                                ?>
                                                    <div class="mr-bullet-item">
                                                        <span class="dashicons dashicons-yes-alt text-green" style="color:#10b981; margin-right:4px;"></span>
                                                        <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][highlights][]" value="<?php echo esc_attr($val); ?>" placeholder="<?php echo sprintf(__('Bullet #%d', 'merarashifal'), $i + 1); ?>" class="regular-text" style="width:90%;">
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <!-- Row 5: Pricing & Discounts -->
                                        <div class="mr-form-row-4">
                                            <div>
                                                <label><strong><?php _e('Sale Price (₹)', 'merarashifal'); ?>:</strong></label>
                                                <input type="number" step="1" name="mr_cards[<?php echo esc_attr($slug); ?>][price_sale]" value="<?php echo esc_attr($card['price_sale']); ?>" class="small-text" style="width:90px;" required>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Regular Price (₹)', 'merarashifal'); ?>:</strong></label>
                                                <input type="number" step="1" name="mr_cards[<?php echo esc_attr($slug); ?>][price_regular]" value="<?php echo esc_attr($card['price_regular']); ?>" class="small-text" style="width:90px;" required>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Discount Tag', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][discount_text]" value="<?php echo esc_attr($card['discount_text']); ?>" class="regular-text" style="width:110px;" placeholder="Save 80%">
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Price Note', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][price_note]" value="<?php echo esc_attr($card['price_note']); ?>" class="regular-text" style="width:110px;" placeholder="Only Today">
                                            </div>
                                        </div>

                                        <!-- Row 6: Button Texts -->
                                        <div class="mr-form-row-2" style="margin-top:10px;">
                                            <div>
                                                <label><strong><?php _e('Primary CTA Button Text', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][button_text]" value="<?php echo esc_attr($card['button_text']); ?>" class="regular-text" style="width:100%;" required>
                                            </div>
                                            <div>
                                                <label><strong><?php _e('Sample Preview Link Text', 'merarashifal'); ?>:</strong></label>
                                                <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][preview_text]" value="<?php echo esc_attr($card['preview_text']); ?>" class="regular-text" style="width:100%;" required>
                                            </div>
                                        </div>

                                        <!-- Row 7: Testimonials (What Seekers Are Saying) -->
                                        <div style="margin-top:16px; background:#fffbf2; border:1px solid #fed7aa; border-radius:8px; padding:16px;">
                                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                                <label style="font-size:13px; font-weight:700; color:#7A1F3D;">
                                                    <span class="dashicons dashicons-format-quote" style="vertical-align:middle; color:#ea580c;"></span>
                                                    <?php _e('What Seekers Are Saying (Customer Testimonials)', 'merarashifal'); ?>
                                                </label>
                                                <span style="font-size:11px; color:#78350f; background:#fef3c7; padding:2px 8px; border-radius:4px; font-weight:600;">
                                                    <?php _e('Live on this report landing page', 'merarashifal'); ?>
                                                </span>
                                            </div>

                                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px;">
                                                <?php 
                                                $existing_testimonials = isset($card['testimonials']) && is_array($card['testimonials']) ? $card['testimonials'] : [];
                                                if (empty($existing_testimonials) && class_exists('MR_Report_Landing_Data')) {
                                                    $def_data = MR_Report_Landing_Data::get($slug);
                                                    $existing_testimonials = $def_data['testimonials'] ?? [];
                                                }
                                                for ($t_idx = 0; $t_idx < 3; $t_idx++): 
                                                    $t_item = isset($existing_testimonials[$t_idx]) ? $existing_testimonials[$t_idx] : [];
                                                    $t_quote = $t_item['quote'] ?? '';
                                                    $t_name  = $t_item['name'] ?? '';
                                                    $t_loc   = $t_item['loc'] ?? '';
                                                    $t_rating = isset($t_item['rating']) ? (int)$t_item['rating'] : 5;
                                                ?>
                                                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:6px; padding:10px 12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                                            <strong style="font-size:11.5px; color:#1e293b;"><?php echo sprintf(__('Review #%d', 'merarashifal'), $t_idx + 1); ?></strong>
                                                            <select name="mr_cards[<?php echo esc_attr($slug); ?>][testimonials][<?php echo $t_idx; ?>][rating]" style="font-size:11px; padding:1px 6px; height:24px;">
                                                                <option value="5" <?php selected($t_rating, 5); ?>>★★★★★ (5 Stars)</option>
                                                                <option value="4" <?php selected($t_rating, 4); ?>>★★★★☆ (4 Stars)</option>
                                                                <option value="3" <?php selected($t_rating, 3); ?>>★★★☆☆ (3 Stars)</option>
                                                            </select>
                                                        </div>
                                                        <div style="margin-bottom:6px;">
                                                            <textarea name="mr_cards[<?php echo esc_attr($slug); ?>][testimonials][<?php echo $t_idx; ?>][quote]" rows="2" style="width:100%; font-size:11.5px;" placeholder="<?php esc_attr_e('Client review quote...', 'merarashifal'); ?>"><?php echo esc_textarea($t_quote); ?></textarea>
                                                        </div>
                                                        <div style="display:flex; gap:6px;">
                                                            <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][testimonials][<?php echo $t_idx; ?>][name]" value="<?php echo esc_attr($t_name); ?>" placeholder="<?php esc_attr_e('Client Name', 'merarashifal'); ?>" style="flex:1; font-size:11px;">
                                                            <input type="text" name="mr_cards[<?php echo esc_attr($slug); ?>][testimonials][<?php echo $t_idx; ?>][loc]" value="<?php echo esc_attr($t_loc); ?>" placeholder="<?php esc_attr_e('Location (e.g. Pune, India)', 'merarashifal'); ?>" style="flex:1; font-size:11px;">
                                                        </div>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sticky Bottom Action Bar -->
                <div class="mr-admin-save-bar">
                    <button type="submit" class="button button-primary button-large" style="font-size:15px; padding:6px 24px; height:auto; background:#f59e0b; border-color:#d97706; text-shadow:none;">
                        <span class="dashicons dashicons-saved" style="vertical-align:middle; line-height:1.2;"></span> <?php _e('Save All Sales Cards Settings', 'merarashifal'); ?>
                    </button>
                    <a href="<?php echo esc_url($sales_page_url); ?>" target="_blank" class="button button-secondary button-large" style="margin-left:12px;">
                        <span class="dashicons dashicons-visibility" style="vertical-align:middle;"></span> <?php _e('Preview Sales Page', 'merarashifal'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Admin Styles & WP Media Script -->
        <style>
            .mr-admin-catalog-wrap { max-width: 1200px; margin: 20px 20px 60px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .mr-admin-intro-box { background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #f59e0b; padding: 16px 20px; border-radius: 6px; margin: 20px 0; display: flex; justify-content: space-between; align-items: center; }
            .mr-admin-intro-col h3 { margin: 0 0 6px 0; font-size: 16px; color: #1e293b; }
            .mr-admin-intro-col p { margin: 0; color: #64748b; font-size: 13px; }
            
            .mr-cards-accordion { display: flex; flex-direction: column; gap: 14px; margin-bottom: 30px; }
            .mr-card-editor-card { background: #ffffff; border: 1px solid #dcdcde; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; transition: border-color 0.2s; }
            .mr-card-editor-card:hover { border-color: #f59e0b; }
            .mr-card-editor-head { background: #f8fafc; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; cursor: pointer; user-select: none; }
            .mr-card-head-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
            .mr-order-badge { background: #1e293b; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
            .mr-code-tag { background: #e2e8f0; color: #0f172a; font-family: monospace; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 4px; border: 1px solid #cbd5e1; }
            .mr-card-name-label { font-size: 15px; color: #0f172a; }
            .mr-cat-pill-admin { background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 12px; border: 1px solid #fde68a; }
            .mr-badge-preview-tag { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }
            .mr-badge-best-seller { background: linear-gradient(135deg, #e11d48, #f97316); }
            .mr-badge-top-selling { background: linear-gradient(135deg, #f59e0b, #d97706); }
            .mr-badge-new-report { background: linear-gradient(135deg, #10b981, #059669); }
            .mr-badge-in-demand { background: linear-gradient(135deg, #ec4899, #be185d); }
            .mr-badge-for-you { background: linear-gradient(135deg, #06b6d4, #0284c7); }
            .mr-badge-recommended { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }

            .mr-card-head-right { display: flex; align-items: center; gap: 12px; }
            .mr-price-tag { font-weight: 700; color: #0f172a; font-size: 14px; }
            .mr-price-tag del { color: #94a3b8; font-weight: normal; margin-left: 4px; font-size: 12px; }
            .mr-status-pill { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; }
            .mr-status-pill.active { background: #dcfce7; color: #166534; }
            .mr-status-pill.hidden { background: #f1f5f9; color: #64748b; }
            
            .mr-card-editor-body { padding: 20px; display: block; }
            .mr-editor-grid { display: grid; grid-template-columns: 220px 1fr; gap: 24px; }
            .mr-thumb-container { width: 100%; height: 210px; background: #0b1020; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; margin-bottom: 10px; }
            .mr-thumb-preview { width: 100%; height: 100%; object-fit: cover; }

            .mr-form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .mr-form-row-3 { display: flex; gap: 16px; margin-bottom: 12px; align-items: flex-start; }
            .mr-form-row-4 { display: flex; gap: 16px; margin-bottom: 12px; align-items: flex-start; }
            .mr-bullets-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
            .mr-bullet-item { display: flex; align-items: center; }

            .mr-admin-save-bar { position: sticky; bottom: 0; background: #ffffff; padding: 16px 24px; border-top: 1px solid #c3c4c7; box-shadow: 0 -4px 15px rgba(0,0,0,0.08); z-index: 99; display: flex; align-items: center; border-radius: 6px; }

            /* Checkbox style guarantee */
            .mr-card-editor-card input[type="checkbox"] { width: 18px; height: 18px; vertical-align: middle; cursor: pointer; accent-color: #f59e0b; }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Toggle accordion cards
                $('.mr-card-editor-head').on('click', function(e) {
                    if ($(e.target).closest('button').length) return;
                    $(this).siblings('.mr-card-editor-body').slideToggle(200);
                    $(this).find('.mr-btn-toggle-card .dashicons').toggleClass('dashicons-arrow-up-alt2 dashicons-arrow-down-alt2');
                });
                $('.mr-btn-toggle-card').on('click', function() {
                    var head = $(this).closest('.mr-card-editor-head');
                    head.siblings('.mr-card-editor-body').slideToggle(200);
                    $(this).find('.dashicons').toggleClass('dashicons-arrow-up-alt2 dashicons-arrow-down-alt2');
                });

                // WP Media Library Uploader
                $('.js-upload-btn').on('click', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var container = btn.closest('.mr-editor-col-image');
                    var inputField = container.find('.js-image-url');
                    var imgPreview = container.find('.js-thumb-preview');

                    var frame = wp.media({
                        title: 'Select or Upload Report Cover Image',
                        button: { text: 'Use this cover image' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        inputField.val(attachment.url);
                        imgPreview.attr('src', attachment.url);
                    });

                    frame.open();
                });
            });
        </script>
        <?php
    }

    /**
     * Frontend Sales Grid Renderer
     * Shortcode: [mr_reports_sales_grid]
     */
    public static function render_sales_grid($atts) {
        $cards = self::get_all_cards();
        $user_id = get_current_user_id();
        $tokens = $user_id ? MR_Tokens::get_user_tokens($user_id) : [];

        // Collect available categories
        $categories = ['All Reports'];
        foreach ($cards as $c) {
            if (!empty($c['is_active']) && !empty($c['category']) && !in_array($c['category'], $categories, true)) {
                $categories[] = $c['category'];
            }
        }

        ob_start();
        ?>
        <!-- Import MeraRashifal Brand Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <div class="mr-sales-page-container">
            
            <!-- On-Page Breadcrumb Navigation -->
            <nav class="mr-breadcrumb-nav" aria-label="Breadcrumb">
                <ol class="mr-breadcrumb-list">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><i class="fa-solid fa-house"></i> <?php _e('Home', 'merarashifal'); ?></a></li>
                    <li class="mr-breadcrumb-sep"><i class="fa-solid fa-chevron-right"></i></li>
                    <li class="active" aria-current="page"><span><?php _e('Astrological Reports', 'merarashifal'); ?></span></li>
                </ol>
            </nav>

            <!-- Header Section -->
            <div class="mr-sales-header-row">
                <div class="mr-sales-header-left">
                    <h1 class="mr-sales-title"><?php _e('Personalized Vedic Astrological Reports', 'merarashifal'); ?></h1>
                    <p class="mr-sales-subtitle"><?php _e('Publication-grade, personalized Vedic life maps and palmistry dossiers calculated with precision Swiss Ephemeris mathematics.', 'merarashifal'); ?></p>
                </div>

                <!-- Category Filter Pills -->
                <div class="mr-sales-filters-wrap">
                    <?php foreach ($categories as $index => $cat): 
                        $filter_slug = ($cat === 'All Reports') ? 'all' : strtolower(str_replace(' ', '-', $cat));
                    ?>
                        <button type="button" class="mr-sales-filter-pill <?php echo ($index === 0) ? 'active' : ''; ?>" data-category="<?php echo esc_attr($filter_slug); ?>">
                            <?php if ($cat === 'Horoscope'): ?>
                                <i class="fa-solid fa-compass"></i>
                            <?php elseif ($cat === 'Kundali'): ?>
                                <i class="fa-solid fa-shapes"></i>
                            <?php elseif ($cat === 'Matching'): ?>
                                <i class="fa-solid fa-heart"></i>
                            <?php elseif ($cat === 'Dasha'): ?>
                                <i class="fa-solid fa-clock"></i>
                            <?php elseif ($cat === 'Numerology'): ?>
                                <i class="fa-solid fa-hashtag"></i>
                            <?php elseif ($cat === 'Transit'): ?>
                                <i class="fa-solid fa-planet-ringed fa-moon"></i>
                            <?php elseif ($cat === 'Palmistry'): ?>
                                <i class="fa-solid fa-hand"></i>
                            <?php endif; ?>
                            <span><?php echo esc_html($cat); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reports Sales Grid -->
            <div class="mr-sales-grid">
                <?php 
                $child_slug_map = [
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

                foreach ($cards as $slug => $card): 
                    if (empty($card['is_active'])) {
                        continue;
                    }
                    $cat_slug = strtolower(str_replace(' ', '-', $card['category']));
                    $badge_data = self::get_badge_data($card['badge']);
                    $rem = isset($tokens[$slug]) ? $tokens[$slug]['remaining'] : 0;
                    $child_slug = $child_slug_map[$slug] ?? str_replace('_', '-', $slug);
                    $report_page_url = home_url('/reports/' . $child_slug . '/');
                ?>
                    <div class="mr-sales-card" data-category="<?php echo esc_attr($cat_slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-code="<?php echo esc_attr($card['code']); ?>" data-price="<?php echo esc_attr($card['price_sale']); ?>">
                        
                        <!-- Top Corner Badge / Ribbon (strictly single badge) -->
                        <?php if ($badge_data): ?>
                            <div class="mr-sales-ribbon <?php echo esc_attr($badge_data['class']); ?>">
                                <span><?php echo esc_html($badge_data['label']); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Page Count Badge (Top Right) -->
                        <?php if (!empty($card['pages_tag'])): ?>
                            <div class="mr-sales-pages-tag">
                                <i class="fa-regular fa-file-lines"></i> <?php echo esc_html($card['pages_tag']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Card Cover Image (clickable) -->
                        <a href="<?php echo esc_url($report_page_url); ?>" class="mr-sales-img-wrap" style="display:block;">
                            <img src="<?php echo esc_url($card['image_url']); ?>" alt="<?php echo esc_attr($card['name']); ?>" loading="lazy" decoding="async" width="340" height="340" class="mr-sales-img">
                        </a>

                        <!-- Card Info -->
                        <div class="mr-sales-content">
                            <h3 class="mr-sales-card-title">
                                <a href="<?php echo esc_url($report_page_url); ?>" style="color:inherit; text-decoration:none;">
                                    <?php echo esc_html($card['name']); ?>
                                </a>
                            </h3>
                            <p class="mr-sales-card-sub"><?php echo esc_html($card['sub_text']); ?></p>

                            <!-- 4 Highlights / Bullets -->
                            <?php if (!empty($card['highlights']) && is_array($card['highlights'])): ?>
                                <ul class="mr-sales-highlights">
                                    <?php foreach ($card['highlights'] as $hl): ?>
                                        <li>
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span><?php echo esc_html($hl); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <!-- Pricing Block -->
                            <div class="mr-sales-price-row">
                                <div class="mr-price-figures">
                                    <span class="mr-price-sale">₹<?php echo esc_html($card['price_sale']); ?></span>
                                    <?php if (!empty($card['price_regular']) && $card['price_regular'] > $card['price_sale']): ?>
                                        <span class="mr-price-strike">₹<?php echo esc_html($card['price_regular']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mr-discount-group">
                                    <?php if (!empty($card['discount_text'])): ?>
                                        <span class="mr-discount-tag"><?php echo esc_html($card['discount_text']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($card['price_note'])): ?>
                                        <span class="mr-price-note"><?php echo esc_html($card['price_note']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mr-sales-actions">
                                <?php if ($user_id && $rem > 0): ?>
                                    <button type="button" class="mr-sales-btn-primary mr-btn-generate-direct" data-slug="<?php echo esc_attr($slug); ?>" data-title="<?php echo esc_attr($card['name']); ?>">
                                        <?php echo esc_html($card['button_text']); ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($report_page_url); ?>" class="mr-sales-btn-primary" style="text-decoration:none; display:block; text-align:center;">
                                        <?php echo esc_html($card['button_text']); ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Sample Preview Link -->
                                <button type="button" class="mr-sales-preview-link mr-link-sample" data-title="<?php echo esc_attr($card['name']); ?>" data-slug="<?php echo esc_attr($slug); ?>">
                                    <i class="fa-regular fa-eye"></i> <?php echo esc_html($card['preview_text']); ?>
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Authority Trust Strip -->
            <div class="mr-catalog-trust-strip">
                <div class="mr-trust-col">
                    <div class="mr-trust-icon-box"><i class="fa-solid fa-atom"></i></div>
                    <div class="mr-trust-text">
                        <h4><?php _e('Swiss Ephemeris Precision', 'merarashifal'); ?></h4>
                        <p><?php _e('100% mathematical planetary coordinates aligned with NASA JPL astro-vectors and true Lahiri Ayanamsha.', 'merarashifal'); ?></p>
                    </div>
                </div>
                <div class="mr-trust-col">
                    <div class="mr-trust-icon-box"><i class="fa-solid fa-scroll"></i></div>
                    <div class="mr-trust-text">
                        <h4><?php _e('Classical Parashari Shastra', 'merarashifal'); ?></h4>
                        <p><?php _e('Exhaustive Shodashvarga 16 divisional charts, Vimshottari Mahadasha cycles, and verified astrological remedies.', 'merarashifal'); ?></p>
                    </div>
                </div>
                <div class="mr-trust-col">
                    <div class="mr-trust-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="mr-trust-text">
                        <h4><?php _e('Bank-Grade Privacy & Security', 'merarashifal'); ?></h4>
                        <p><?php _e('Your birth details remain strictly confidential with 256-bit SSL encryption and instant private PDF generation.', 'merarashifal'); ?></p>
                    </div>
                </div>
            </div>

            <!-- FAQ Accordion Section -->
            <div class="mr-catalog-faq-section" id="faq">
                <div class="mr-faq-header">
                    <div style="display:inline-flex; align-items:center; gap:6px; background:#FFF3E1; color:#7A1F3D; border:1px solid #D9A441; padding:4px 14px; border-radius:999px; font-size:12px; font-weight:700; margin-bottom:12px; letter-spacing:0.04em;">
                        <i class="fa-solid fa-circle-question" style="color:#D9A441;"></i> <?php _e('Vedic Astrological Guidance', 'merarashifal'); ?>
                    </div>
                    <h2 class="mr-faq-main-title"><?php _e('Frequently Asked Questions', 'merarashifal'); ?></h2>
                    <p class="mr-faq-sub"><?php _e('Everything you need to know about our certified Vedic astrology calculations, instant report delivery, and Shastric precision.', 'merarashifal'); ?></p>
                </div>

                <div class="mr-faq-accordion-container">
                    <?php 
                    $catalog_faqs = self::get_catalog_faqs();
                    foreach ($catalog_faqs as $f_idx => $faq): 
                        $is_first = ($f_idx === 0);
                    ?>
                        <div class="mr-faq-item <?php echo $is_first ? 'active' : ''; ?>">
                            <button type="button" class="mr-faq-question" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>">
                                <span><?php echo esc_html($faq['q']); ?></span>
                                <span class="mr-faq-icon"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="mr-faq-answer" style="<?php echo $is_first ? 'display:block;' : ''; ?>">
                                <p><?php echo esc_html($faq['a']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Render Modals -->
        <?php echo MR_Frontend::render_simulated_pg_modal(); ?>
        <?php echo MR_Frontend::render_details_capture_and_banner_modal(); ?>
        <?php echo MR_Frontend::render_sample_preview_modal(); ?>

        <script>
            jQuery(document).ready(function($) {
                // Client-side Category Filter
                $('.mr-sales-filter-pill').on('click', function() {
                    $('.mr-sales-filter-pill').removeClass('active');
                    $(this).addClass('active');

                    var cat = $(this).data('category');
                    if (cat === 'all') {
                        $('.mr-sales-card').fadeIn(200);
                    } else {
                        $('.mr-sales-card').hide();
                        $('.mr-sales-card[data-category="' + cat + '"]').fadeIn(200);
                    }
                });

                // FAQ Accordion Toggle
                $('.mr-faq-question').on('click', function() {
                    var item = $(this).closest('.mr-faq-item');
                    var answer = item.find('.mr-faq-answer');
                    var isExpanded = item.hasClass('active');

                    if (isExpanded) {
                        item.removeClass('active');
                        $(this).attr('aria-expanded', 'false');
                        answer.slideUp(200);
                    } else {
                        $('.mr-faq-item').removeClass('active');
                        $('.mr-faq-question').attr('aria-expanded', 'false');
                        $('.mr-faq-answer').slideUp(200);

                        item.addClass('active');
                        $(this).attr('aria-expanded', 'true');
                        answer.slideDown(200);
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if currently viewing the main reports catalog page (/reports/)
     */
    public static function is_catalog_page() {
        if (is_admin()) {
            return false;
        }

        // Avoid triggering on individual report landing pages (/reports/janam-kundali-master-report/, etc.)
        if (class_exists('MR_Report_Landing') && MR_Report_Landing::is_report_landing_page()) {
            return false;
        }

        global $post;
        if ($post && is_a($post, 'WP_Post')) {
            if ($post->post_name === 'reports' || $post->ID === 7224) {
                return true;
            }
            if (has_shortcode($post->post_content, 'mr_reports') || has_shortcode($post->post_content, 'mr_reports_sales_grid') || has_shortcode($post->post_content, 'mr_reports_catalog')) {
                return true;
            }
        }

        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        $uri_parts = explode('?', $uri);
        $path = trim($uri_parts[0], '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        if ($site_path && strpos($path, $site_path) === 0) {
            $path = trim(substr($path, strlen($site_path)), '/');
        }

        return ($path === 'reports');
    }

    /**
     * Master FAQ Data for Reports Catalog Page
     */
    public static function get_catalog_faqs() {
        return [
            [
                'q' => 'What makes MeraRashifal\'s Vedic astrological reports authentic and accurate?',
                'a' => 'MeraRashifal utilizes high-precision computational Swiss Ephemeris data cross-referenced with classical Vedic Parashari and Jaimini Shastras. Every report factors in exact geographic coordinates (latitude and longitude) and precise planetary degrees with Chitrapaksha (Lahiri) Ayanamsha to guarantee 100% mathematical and interpretive accuracy.'
            ],
            [
                'q' => 'Which birth details are required to generate my personalized report?',
                'a' => 'To generate your customized report, you only need to provide your Full Name, Gender, Date of Birth, Exact Time of Birth, and Place of Birth (City, State, Country). If you do not know your exact birth time, our automated engine adjusts for solar sunrise calculations or you can consult Niyati AI for birth chart rectification guidance.'
            ],
            [
                'q' => 'How quickly will I receive my generated horoscope or kundali report?',
                'a' => 'Generation is instantaneous. Once you submit your birth details, our high-speed computational engine compiles your full 20+ page publication-grade PDF within 30 to 60 seconds. You can view and download it immediately in your browser, and a permanent copy is automatically emailed to you and saved to your Astro Account.'
            ],
            [
                'q' => 'Can I preview a sample report before ordering?',
                'a' => 'Yes. Every report card in our catalog features a "Preview Sample Report" button. Clicking this opens a live, multi-page sample viewer displaying the exact depth, Vedic charts, divisional analysis, and layout you will receive in your personal report.'
            ],
            [
                'q' => 'What is the difference between the Janma Kundali Master Report and Yearly Varshaphal?',
                'a' => 'The Janma Kundali Master Report is a foundational 26-page lifetime blueprint covering all 16 Shodashvarga divisional harmonic charts, planetary Shadbala strength, Raja Yogas, and lifetime milestones. The Yearly Varshaphal (2026) report focuses exclusively on a 12-month month-by-month transit forecast, Tajika annual solar return chart, and immediate career, financial, and health timings for the year.'
            ],
            [
                'q' => 'Do these reports include astrological remedies, gemstones, and dosha nivaran?',
                'a' => 'Yes. All our personalized reports include authentic, certified Vedic remedies (Upayas) specifically tailored to benefic and afflicted planets in your natal chart. Recommendations include auspicious gemstones, Vedic mantras, Rudraksha suitability, charity rituals (Daan), and corrective remedies for Manglik, Kaal Sarp, and Sade Sati doshas.'
            ],
            [
                'q' => 'Where can I find and download my previously generated reports?',
                'a' => 'All reports you order or unlock are permanently archived in your personal MeraRashifal Astro Account under "My Generated Reports". You can log in at any time to re-download or print your PDF dossiers without additional charges.'
            ]
        ];
    }

    /**
     * Filter Document Title for SEO
     */
    public static function filter_document_title($title) {
        if (self::is_catalog_page()) {
            return 'Personalized Vedic Astrology Reports & Kundali Dossiers | MeraRashifal';
        }
        return $title;
    }

    /**
     * Filter Document Title Parts Array
     */
    public static function filter_document_title_parts($parts) {
        if (self::is_catalog_page()) {
            $parts['title'] = 'Personalized Vedic Astrology Reports & Kundali Dossiers | MeraRashifal';
        }
        return $parts;
    }

    /**
     * Filter Meta Description for Yoast & Custom SEO
     */
    public static function filter_meta_description($desc) {
        if (self::is_catalog_page()) {
            return 'Explore authentic, publication-grade Vedic astrological reports calculated with Swiss Ephemeris accuracy. Get Janma Kundali, Yearly Horoscope, Dasha Timing, Gun Milan & Palmistry dossiers.';
        }
        return $desc;
    }

    /**
     * Filter Canonical URL
     */
    public static function filter_canonical_url($url) {
        if (self::is_catalog_page()) {
            return home_url('/reports/');
        }
        return $url;
    }

    /**
     * Filter Robots Tag
     */
    public static function filter_robots($robots) {
        if (self::is_catalog_page()) {
            return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        }
        return $robots;
    }

    /**
     * Inject complete Catalog SEO Head Tags, OpenGraph, Twitter Cards & Schema.org JSON-LD
     */
    public static function inject_catalog_seo_head() {
        if (!self::is_catalog_page()) {
            return;
        }

        $canonical_url = home_url('/reports/');
        $site_name = get_bloginfo('name') ?: 'MeraRashifal';
        $meta_title = 'Personalized Vedic Astrology Reports & Kundali Dossiers | MeraRashifal';
        $meta_desc = 'Explore authentic, publication-grade Vedic astrological reports calculated with Swiss Ephemeris accuracy. Get Janma Kundali, Yearly Horoscope, Dasha Timing, Gun Milan & Palmistry dossiers.';
        $meta_keywords = 'vedic astrology reports, online kundali, janam kundali pdf, yearly horoscope 2026, gun milan matching, dasha timing report, kundali dosh remedies, palm reading report';

        // Catalog Featured Artwork
        $featured_img_url = home_url('/wp-content/uploads/vedic-astrological-reports-catalog.png');
        if (!file_exists(ABSPATH . 'wp-content/uploads/vedic-astrological-reports-catalog.png')) {
            $featured_img_url = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/reports/kundli_predictions.png');
        }

        // Build CollectionPage / ItemList Schema of all active reports
        $cards = self::get_all_cards();
        $child_slug_map = [
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

        $item_elements = [];
        $pos = 0;
        foreach ($cards as $slug => $card) {
            if (empty($card['is_active'])) {
                continue;
            }
            $pos++;
            $child_slug = $child_slug_map[$slug] ?? str_replace('_', '-', $slug);
            $item_url = home_url('/reports/' . $child_slug . '/');

            $item_elements[] = [
                '@type'    => 'ListItem',
                'position' => $pos,
                'item'     => [
                    '@type'       => 'Product',
                    '@id'         => $item_url . '#product',
                    'name'        => $card['name'],
                    'description' => $card['sub_text'],
                    'url'         => $item_url,
                    'image'       => $card['image_url'],
                    'brand'       => [
                        '@type' => 'Brand',
                        'name'  => 'MeraRashifal',
                    ],
                    'offers'      => [
                        '@type'           => 'Offer',
                        'url'             => $item_url,
                        'priceCurrency'   => 'INR',
                        'price'           => (string)$card['price_sale'],
                        'priceValidUntil' => '2027-12-31',
                        'availability'    => 'https://schema.org/InStock',
                        'itemCondition'   => 'https://schema.org/NewCondition',
                    ],
                    'aggregateRating' => [
                        '@type'       => 'AggregateRating',
                        'ratingValue' => '4.9',
                        'reviewCount' => '1250',
                        'bestRating'  => '5',
                        'worstRating' => '1',
                    ],
                ],
            ];
        }

        // Schema.org Graph
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
                    'url'        => $featured_img_url,
                    'contentUrl' => $featured_img_url,
                    'width'      => 512,
                    'height'     => 512,
                    'caption'    => 'MeraRashifal Sacred Vedic Astrology & Samudrik Publications',
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
                'name'        => $site_name,
                'description' => 'Authentic Vedic Astrology, Janma Kundali & Horoscope Publications',
                'publisher'   => [
                    '@id' => home_url('/#organization'),
                ],
                'inLanguage'  => 'en-US',
            ],
            // 3. BreadcrumbList Schema
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $canonical_url . '#breadcrumbs',
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
                        'item'     => $canonical_url,
                    ],
                ],
            ],
            // 4. CollectionPage with ItemList
            [
                '@type'       => 'CollectionPage',
                '@id'         => $canonical_url . '#catalog',
                'url'         => $canonical_url,
                'name'        => $meta_title,
                'description' => $meta_desc,
                'isPartOf'    => [
                    '@id' => home_url('/#website'),
                ],
                'breadcrumb'  => [
                    '@id' => $canonical_url . '#breadcrumbs',
                ],
                'mainEntity'  => [
                    '@type'           => 'ItemList',
                    'numberOfItems'   => count($item_elements),
                    'itemListElement' => $item_elements,
                ],
            ],
            // 5. Product (Niyati AI Consultation Tokens)
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
        ];

        // Add FAQPage Schema
        $faqs = self::get_catalog_faqs();
        $faq_entities = [];
        foreach ($faqs as $f) {
            $faq_entities[] = [
                '@type'          => 'Question',
                'name'           => $f['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $f['a'],
                ],
            ];
        }
        $graph[] = [
            '@type'      => 'FAQPage',
            '@id'        => $canonical_url . '#faq',
            'mainEntity' => $faq_entities,
        ];

        $schema_json = json_encode([
            '@context' => 'https://schema.org/',
            '@graph'   => $graph,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        ?>
        <!-- MeraRashifal Reports Catalog SEO Metadata & Social Graph -->
        <meta name="description" content="<?php echo esc_attr($meta_desc); ?>">
        <meta name="keywords" content="<?php echo esc_attr($meta_keywords); ?>">
        <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:locale" content="en_US">
        <meta property="og:locale:alternate" content="hi_IN">
        <meta property="og:locale:alternate" content="en_IN">
        <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
        <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>">
        <meta property="og:description" content="<?php echo esc_attr($meta_desc); ?>">
        <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
        <meta property="og:image" content="<?php echo esc_url($featured_img_url); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:alt" content="MeraRashifal Vedic Astrological Reports Catalog">

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@merarashifal">
        <meta name="twitter:creator" content="@merarashifal">
        <meta name="twitter:title" content="<?php echo esc_attr($meta_title); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($meta_desc); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($featured_img_url); ?>">
        <meta name="twitter:image:alt" content="MeraRashifal Vedic Astrological Reports Catalog">

        <!-- Schema.org JSON-LD (BreadcrumbList, CollectionPage, ItemList, FAQPage) -->
        <script type="application/ld+json">
<?php echo $schema_json; ?>
        </script>
        <?php
    }
}
