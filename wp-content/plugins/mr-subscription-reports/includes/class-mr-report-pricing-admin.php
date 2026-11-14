<?php
/**
 * MeraRashifal Report Pricing & Catalog CRUD Manager
 * Single authoritative source of truth for all report pricing, active/suspended status, and catalog metadata.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Report_Pricing_Admin {

    const OPTION_KEY = 'mr_report_pricing_config';

    public static function init() {
        if (is_admin()) {
            add_action('admin_menu', [__CLASS__, 'register_admin_submenu'], 99);
            add_action('admin_post_mr_save_report_pricing', [__CLASS__, 'handle_save_pricing']);
            add_action('admin_post_mr_add_new_report', [__CLASS__, 'handle_add_new_report']);
            add_action('admin_action_mr_toggle_report_status', [__CLASS__, 'handle_toggle_status']);
            add_action('admin_action_mr_delete_custom_report', [__CLASS__, 'handle_delete_report']);
        }

        add_action('init', [__CLASS__, 'register_catalog_shortcodes'], 20);
        add_action('wp_footer', [__CLASS__, 'sync_frontend_catalog_prices_script'], 9999);
    }

    /**
     * Master Default Definitions for Core Built-in Batch Reports
     */
    public static function get_default_definitions() {
        return [
            'yearly_horoscope_long' => [
                'internal_code' => 'MR_YHLR',
                'name'          => 'Yearly Horoscope Detailed Long Report',
                'slug'          => 'yearly_horoscope_long',
                'pages'         => '26 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-calendar-alt',
                'list_price'    => 1499,
                'sale_price'    => 299,
                'active'        => 1,
                'description'   => 'Comprehensive 26-page 12-month Vedic Varshaphal forecast covering career, wealth, relationships, and finances.',
                'is_system'     => 1,
            ],
            'yearly_horoscope_vedic' => [
                'internal_code' => 'MR_YHSR',
                'name'          => 'Yearly Horoscope Short Report (2026)',
                'slug'          => 'yearly_horoscope_vedic',
                'pages'         => '5 Pages (Free)',
                'category'      => 'Free Lead Magnet',
                'icon'          => 'dashicons-calendar-alt',
                'list_price'    => 499,
                'sale_price'    => 0,
                'active'        => 1,
                'description'   => 'Essential 5-page annual overview with key planetary transits, 4 life pillars, and auspicious timing windows.',
                'is_system'     => 1,
            ],
            'kundli_predictions' => [
                'internal_code' => 'MR_JKLR',
                'name'          => 'Master Janma Kundali & Harmonics Report',
                'slug'          => 'kundli_predictions',
                'pages'         => '26 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-chart-pie',
                'list_price'    => 999,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => 'Full 26-page birth chart analysis, 16 Shodashvarga harmonics, Shadbala power metrics, yogas, and lifetime trajectory.',
                'is_system'     => 1,
            ],
            'mahadasha_timing' => [
                'internal_code' => 'MR_MDTR',
                'name'          => 'Master Vedic Dasha Life Timing Report',
                'slug'          => 'mahadasha_timing',
                'pages'         => '20 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-clock',
                'list_price'    => 999,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => 'Deep dive 20-page audit into your active Vimshottari Dasha cycles and event timing predictions.',
                'is_system'     => 1,
            ],
            'dosh_remedial' => [
                'internal_code' => 'MR_KDRR',
                'name'          => 'Master Kundali Dosh & Remedial Audit Report',
                'slug'          => 'dosh_remedial',
                'pages'         => '20 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-shield',
                'list_price'    => 799,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => 'Clinical diagnosis of Manglik, Kaal Sarp, Pitra, or Sade Sati doshas with personalized Shastric remedies.',
                'is_system'     => 1,
            ],
            'kundli_matching' => [
                'internal_code' => 'MR_KMMR',
                'name'          => 'Master Kundali Matching (Gun Milan) Report',
                'slug'          => 'kundli_matching',
                'pages'         => '24 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-heart',
                'list_price'    => 1499,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => '24-page Ashtakoot matching with 36 Gunas, Manglik compatibility, Papasamaya load balance, and dual Dasha sync.',
                'is_system'     => 1,
            ],
            'numerology_report' => [
                'internal_code' => 'MR_NMVR',
                'name'          => 'Master Numerology & Vedic Integration Report',
                'slug'          => 'numerology_report',
                'pages'         => '22 Pages',
                'category'      => 'General & Numerology',
                'icon'          => 'dashicons-star-filled',
                'list_price'    => 599,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => '22-page Sankhya Shastra audit: Lo Shu 3x3 energy grid, core life paths, 8-1-6 Practical Arrow, and 90-day roadmap.',
                'is_system'     => 1,
            ],
            'palmistry_report' => [
                'internal_code' => 'MR_PLMR',
                'name'          => 'Master Hast Rekha & Palmistry Analysis Report',
                'slug'          => 'palmistry_report',
                'pages'         => '26 Pages',
                'category'      => 'Palmistry & Samudrik',
                'icon'          => 'dashicons-admin-customizer',
                'list_price'    => 1499,
                'sale_price'    => 299,
                'active'        => 1,
                'description'   => 'Comprehensive 20-page Hast Rekha & Samudrik Shastra analysis: Major lines, Mounts, Career, Wealth & 5-phase age timeline.',
                'is_system'     => 1,
            ],
            'monthly_horoscope_vedic' => [
                'internal_code' => 'MR_MHTR',
                'name'          => 'Monthly Horoscope & Transit Subscription Report',
                'slug'          => 'monthly_horoscope_vedic',
                'pages'         => '13 Pages',
                'category'      => 'Vedic Reading',
                'icon'          => 'dashicons-calendar',
                'list_price'    => 499,
                'sale_price'    => 99,
                'active'        => 1,
                'description'   => 'Detailed 13-page month-by-month planetary transits, auspicious days, sector ratings, and guidance.',
                'is_system'     => 1,
            ],
        ];
    }

    /**
     * Standard category options
     */
    public static function get_standard_categories() {
        return [
            'Vedic Reading',
            'Horoscope',
            'Kundali',
            'Dasha',
            'Remedial Reading',
            'Matchmaking',
            'General & Numerology',
            'Palmistry & Samudrik',
            'Free Lead Magnet',
            'Custom Consultation',
        ];
    }

    /**
     * Get Master Catalog Configuration (Merges DB option with system defaults)
     */
    public static function get_config() {
        $defaults = self::get_default_definitions();
        $saved = get_option(self::OPTION_KEY, []);

        if (!is_array($saved) || empty($saved)) {
            return $defaults;
        }

        // Merge saved overrides into defaults
        foreach ($defaults as $slug => $def) {
            if (isset($saved[$slug]) && is_array($saved[$slug])) {
                $defaults[$slug] = array_merge($def, $saved[$slug]);
            }
        }

        // Append custom user-added reports
        foreach ($saved as $slug => $item) {
            if (!isset($defaults[$slug]) && is_array($item)) {
                $item['slug'] = $slug;
                $item['is_system'] = 0;
                $defaults[$slug] = $item;
            }
        }

        return $defaults;
    }

    /**
     * Synchronize Master Pricing with Sales Cards Settings
     */
    public static function sync_with_catalog_cards($config = null) {
        if ($config === null) {
            $config = self::get_config();
        }

        $cards = get_option('mr_catalog_cards_settings', []);
        if (!is_array($cards)) {
            $cards = [];
        }

        $changed = false;
        foreach ($config as $slug => $cfg) {
            $sale_price = floatval($cfg['sale_price'] ?? 0);
            $list_price = floatval($cfg['list_price'] ?? 0);
            $is_active  = !empty($cfg['active']) ? 1 : 0;
            $name       = $cfg['name'] ?? $slug;
            $pages_tag  = $cfg['pages'] ?? '';
            $category   = $cfg['category'] ?? 'Vedic Reading';

            if (!isset($cards[$slug])) {
                // Seed new card
                $img_base = defined('MR_PLUGIN_URL') ? MR_PLUGIN_URL . 'assets/images/reports/' : '';
                $cards[$slug] = [
                    'id'            => $slug,
                    'code'          => $cfg['internal_code'] ?? 'MR_CUST',
                    'order'         => count($cards) + 1,
                    'name'          => $name,
                    'sub_text'      => $cfg['description'] ?? '',
                    'category'      => $category,
                    'badge'         => 'none',
                    'pages_tag'     => $pages_tag,
                    'image_url'     => $img_base . 'kundli_predictions.png',
                    'highlights'    => ['Detailed Predictions', 'Accurate Timings', 'Remedies & Insights', 'Lifetime Archive'],
                    'price_sale'    => $sale_price,
                    'price_regular' => $list_price,
                    'discount_text' => ($list_price > $sale_price && $list_price > 0) ? 'Save ' . round((($list_price - $sale_price) / $list_price) * 100) . '%' : '',
                    'price_note'    => '',
                    'button_text'   => 'Get My Report →',
                    'preview_text'  => 'Preview Sample Report',
                    'is_active'     => $is_active,
                    'testimonials'  => [],
                ];
                $changed = true;
            } else {
                // Update pricing and status
                $cards[$slug]['price_sale']    = $sale_price;
                $cards[$slug]['price_regular'] = $list_price;
                $cards[$slug]['is_active']     = $is_active;
                $cards[$slug]['name']          = $name;
                if (!empty($pages_tag)) {
                    $cards[$slug]['pages_tag'] = $pages_tag;
                }
                if ($list_price > $sale_price && $list_price > 0) {
                    $cards[$slug]['discount_text'] = 'Save ' . round((($list_price - $sale_price) / $list_price) * 100) . '%';
                }
                $changed = true;
            }
        }

        if ($changed) {
            update_option('mr_catalog_cards_settings', $cards);
        }
    }

    /**
     * Register Submenu Page
     */
    public static function register_admin_submenu() {
        add_submenu_page(
            'mr-subscriptions',
            __('Report Pricing & Catalog Manager', 'merarashifal'),
            __('Report Pricing & Catalog', 'merarashifal'),
            'manage_options',
            'mr-report-pricing',
            [__CLASS__, 'render_admin_page']
        );
    }

    /**
     * Handle Batch Save / Update
     */
    public static function handle_save_pricing() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'merarashifal'));
        }
        check_admin_referer('mr_save_pricing_nonce', 'mr_nonce');

        $current_config = self::get_config();
        $new_config = [];

        if (!empty($_POST['reports']) && is_array($_POST['reports'])) {
            foreach ($_POST['reports'] as $slug => $data) {
                $slug = sanitize_key($slug);
                $is_sys = !empty($current_config[$slug]['is_system']) ? 1 : 0;

                $new_config[$slug] = [
                    'name'          => sanitize_text_field($data['name'] ?? ($current_config[$slug]['name'] ?? $slug)),
                    'internal_code' => sanitize_text_field($data['internal_code'] ?? ($current_config[$slug]['internal_code'] ?? '')),
                    'slug'          => $slug,
                    'pages'         => sanitize_text_field($data['pages'] ?? ($current_config[$slug]['pages'] ?? '')),
                    'category'      => sanitize_text_field($data['category'] ?? ($current_config[$slug]['category'] ?? 'Vedic Reading')),
                    'icon'          => $current_config[$slug]['icon'] ?? 'dashicons-media-document',
                    'list_price'    => max(0, floatval($data['list_price'] ?? 0)),
                    'sale_price'    => max(0, floatval($data['sale_price'] ?? 0)),
                    'active'        => isset($data['active']) ? 1 : 0,
                    'description'   => sanitize_textarea_field($data['description'] ?? ($current_config[$slug]['description'] ?? '')),
                    'is_system'     => $is_sys,
                ];
            }
        }

        // Preserve any custom reports not posted
        foreach ($current_config as $ex_slug => $ex_data) {
            if (!isset($new_config[$ex_slug])) {
                $new_config[$ex_slug] = $ex_data;
            }
        }

        update_option(self::OPTION_KEY, $new_config);
        self::sync_with_catalog_cards($new_config);

        wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'updated' => '1'], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Add New Report (Create)
     */
    public static function handle_add_new_report() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'merarashifal'));
        }
        check_admin_referer('mr_add_report_nonce', 'mr_nonce');

        $name = sanitize_text_field($_POST['new_report_name'] ?? '');
        $raw_slug = sanitize_text_field($_POST['new_report_slug'] ?? '');
        $slug = sanitize_key($raw_slug ?: sanitize_title($name));

        if (empty($name) || empty($slug)) {
            wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'error' => 'empty_fields'], admin_url('admin.php')));
            exit;
        }

        $config = self::get_config();
        if (isset($config[$slug])) {
            wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'error' => 'duplicate_slug'], admin_url('admin.php')));
            exit;
        }

        $code = sanitize_text_field($_POST['new_report_code'] ?? '');
        if (empty($code)) {
            $code = 'MR_' . strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $slug), 0, 4));
        }

        $config[$slug] = [
            'name'          => $name,
            'slug'          => $slug,
            'internal_code' => $code,
            'category'      => sanitize_text_field($_POST['new_report_category'] ?? 'Vedic Reading'),
            'pages'         => sanitize_text_field($_POST['new_report_pages'] ?? '20 Pages'),
            'icon'          => 'dashicons-media-document',
            'list_price'    => max(0, floatval($_POST['new_report_list_price'] ?? 999)),
            'sale_price'    => max(0, floatval($_POST['new_report_sale_price'] ?? 99)),
            'active'        => isset($_POST['new_report_active']) ? 1 : 0,
            'description'   => sanitize_textarea_field($_POST['new_report_desc'] ?? ''),
            'is_system'     => 0,
        ];

        update_option(self::OPTION_KEY, $config);
        self::sync_with_catalog_cards($config);

        wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'added' => '1', 'slug' => $slug], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Suspend / Reactivate Status Toggle
     */
    public static function handle_toggle_status() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'merarashifal'));
        }

        $slug = sanitize_key($_GET['slug'] ?? '');
        check_admin_referer('mr_toggle_' . $slug);

        $config = self::get_config();
        if (!isset($config[$slug])) {
            wp_die(__('Report not found.', 'merarashifal'));
        }

        $current_state = !empty($config[$slug]['active']) ? 1 : 0;
        $new_state     = $current_state ? 0 : 1;
        $config[$slug]['active'] = $new_state;

        update_option(self::OPTION_KEY, $config);
        self::sync_with_catalog_cards($config);

        $msg = $new_state ? 'reactivated' : 'suspended';
        wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'status_changed' => $msg, 'slug' => $slug], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Delete Custom Report
     */
    public static function handle_delete_report() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'merarashifal'));
        }

        $slug = sanitize_key($_GET['slug'] ?? '');
        check_admin_referer('mr_delete_' . $slug);

        $config = self::get_config();
        if (!isset($config[$slug])) {
            wp_die(__('Report not found.', 'merarashifal'));
        }

        // Protect core system reports
        if (!empty($config[$slug]['is_system'])) {
            wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'error' => 'cannot_delete_system'], admin_url('admin.php')));
            exit;
        }

        unset($config[$slug]);
        update_option(self::OPTION_KEY, $config);

        // Also clean from catalog cards
        $cards = get_option('mr_catalog_cards_settings', []);
        if (isset($cards[$slug])) {
            unset($cards[$slug]);
            update_option('mr_catalog_cards_settings', $cards);
        }

        wp_safe_redirect(add_query_arg(['page' => 'mr-report-pricing', 'deleted' => '1'], admin_url('admin.php')));
        exit;
    }

    /**
     * Render Admin Manager Dashboard Page
     */
    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user.', 'merarashifal'));
        }

        $reports = self::get_config();
        $categories = self::get_standard_categories();

        // Status counts
        $total_count     = count($reports);
        $active_count    = 0;
        $suspended_count = 0;
        $free_count      = 0;

        foreach ($reports as $r) {
            if (!empty($r['active'])) {
                $active_count++;
            } else {
                $suspended_count++;
            }
            if (floatval($r['sale_price'] ?? 0) == 0) {
                $free_count++;
            }
        }

        $current_tab = sanitize_key($_GET['tab'] ?? 'all');
        if (!in_array($current_tab, ['all', 'active', 'suspended'], true)) {
            $current_tab = 'all';
        }

        // Filter reports by tab
        $filtered_reports = [];
        foreach ($reports as $slug => $r) {
            $is_act = !empty($r['active']);
            if ($current_tab === 'active' && !$is_act) {
                continue;
            }
            if ($current_tab === 'suspended' && $is_act) {
                continue;
            }
            $filtered_reports[$slug] = $r;
        }

        // Notices
        $updated        = isset($_GET['updated']) && $_GET['updated'] === '1';
        $added          = isset($_GET['added']) && $_GET['added'] === '1';
        $deleted        = isset($_GET['deleted']) && $_GET['deleted'] === '1';
        $status_changed = sanitize_key($_GET['status_changed'] ?? '');
        $error          = sanitize_key($_GET['error'] ?? '');
        ?>
        <div class="wrap mr-pricing-catalog-wrap" style="max-width: 1360px; margin-top:20px;">
            <style>
                .mr-pricing-catalog-wrap {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
                }
                .mr-admin-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 15px;
                    margin-bottom: 22px;
                }
                .mr-header-title-box h1 {
                    font-size: 26px;
                    font-weight: 800;
                    color: #0f172a;
                    margin: 0 0 6px 0;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .mr-header-title-box p {
                    margin: 0;
                    font-size: 13.5px;
                    color: #64748b;
                }
                .mr-stat-chips {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                }
                .mr-chip {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    padding: 8px 16px;
                    border-radius: 10px;
                    font-size: 13px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
                }
                .mr-chip strong {
                    font-size: 15px;
                    color: #0f172a;
                }
                .mr-chip-active { border-color: #a7f3d0; background: #f0fdf4; color: #166534; }
                .mr-chip-suspended { border-color: #fecdd3; background: #fff1f2; color: #9f1239; }
                .mr-filter-nav {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 16px;
                    border-bottom: 1px solid #e2e8f0;
                    padding-bottom: 12px;
                }
                .mr-tabs {
                    display: flex;
                    gap: 8px;
                }
                .mr-tab-link {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 8px 16px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 600;
                    color: #475569;
                    background: #f1f5f9;
                    transition: all 0.15s ease;
                }
                .mr-tab-link:hover {
                    background: #e2e8f0;
                    color: #0f172a;
                }
                .mr-tab-link.active {
                    background: #0f172a;
                    color: #ffffff;
                }
                .mr-tab-link .badge {
                    background: rgba(0,0,0,0.12);
                    padding: 2px 7px;
                    border-radius: 12px;
                    font-size: 11px;
                }
                .mr-tab-link.active .badge {
                    background: rgba(255,255,255,0.25);
                    color: #fff;
                }
                .mr-table-card {
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
                    overflow: hidden;
                    margin-bottom: 30px;
                }
                .mr-table-card table.wp-list-table {
                    border: none;
                }
                .mr-table-card thead th {
                    background: #f8fafc;
                    border-bottom: 2px solid #e2e8f0;
                    color: #334155;
                    font-weight: 700;
                    font-size: 12.5px;
                    padding: 12px 14px;
                }
                .mr-table-card tbody td {
                    padding: 12px 14px;
                    vertical-align: middle;
                    border-bottom: 1px solid #f1f5f9;
                }
                .mr-table-card tbody tr:hover {
                    background: #fafaf9;
                }
                .mr-status-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    font-size: 11px;
                    font-weight: 700;
                    padding: 4px 10px;
                    border-radius: 50px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .mr-status-active {
                    background: #ecfdf5;
                    color: #047857;
                    border: 1px solid #a7f3d0;
                }
                .mr-status-suspended {
                    background: #fff1f2;
                    color: #e11d48;
                    border: 1px solid #fecdd3;
                }
                .mr-btn-action {
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                    font-size: 11.5px;
                    font-weight: 600;
                    padding: 4px 10px;
                    border-radius: 6px;
                    text-decoration: none;
                    transition: all 0.15s ease;
                }
                .mr-btn-suspend {
                    background: #fff1f2;
                    color: #be123c;
                    border: 1px solid #fecdd3;
                }
                .mr-btn-suspend:hover {
                    background: #ffe4e6;
                    color: #9f1239;
                }
                .mr-btn-reactivate {
                    background: #ecfdf5;
                    color: #047857;
                    border: 1px solid #a7f3d0;
                }
                .mr-btn-reactivate:hover {
                    background: #d1fae5;
                    color: #065f46;
                }
                .mr-btn-delete {
                    background: #f1f5f9;
                    color: #64748b;
                    border: 1px solid #cbd5e1;
                }
                .mr-btn-delete:hover {
                    background: #fee2e2;
                    color: #b91c1c;
                    border-color: #fca5a5;
                }
                .mr-btn-primary-hero {
                    background: linear-gradient(135deg, #F58220 0%, #D9A441 100%) !important;
                    border: none !important;
                    color: #fff !important;
                    font-weight: 700 !important;
                    padding: 10px 22px !important;
                    font-size: 14px !important;
                    border-radius: 8px !important;
                    box-shadow: 0 4px 12px rgba(245, 130, 32, 0.25) !important;
                    cursor: pointer !important;
                    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
                }
                .mr-btn-primary-hero:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 6px 16px rgba(245, 130, 32, 0.35) !important;
                }
                .mr-add-modal-card {
                    background: #ffffff;
                    border: 1.5px solid #fed7aa;
                    border-radius: 14px;
                    padding: 24px;
                    box-shadow: 0 10px 30px rgba(245, 130, 32, 0.08);
                    margin-bottom: 26px;
                }
                .mr-desc-expander {
                    cursor: pointer;
                    color: #0284c7;
                    font-size: 11.5px;
                    font-weight: 600;
                    margin-top: 4px;
                    display: inline-block;
                }
                .mr-desc-expander:hover {
                    text-decoration: underline;
                }
            </style>

            <!-- 1. Header & Live Metric Chips -->
            <div class="mr-admin-header">
                <div class="mr-header-title-box">
                    <h1>
                        <span class="dashicons dashicons-tag" style="font-size:28px; width:28px; height:28px; color:#F58220;"></span>
                        Master Report Pricing &amp; Catalog Manager
                    </h1>
                    <p>
                        Single authoritative master dashboard to define report prices, page counts, categories, descriptions, and toggle <strong>Active / Suspended</strong> status.
                    </p>
                </div>
                <div class="mr-stat-chips">
                    <div class="mr-chip">
                        <span>Total Reports:</span>
                        <strong><?php echo $total_count; ?></strong>
                    </div>
                    <div class="mr-chip mr-chip-active">
                        <span>● Active Live:</span>
                        <strong><?php echo $active_count; ?></strong>
                    </div>
                    <div class="mr-chip mr-chip-suspended">
                        <span>⏸ Suspended:</span>
                        <strong><?php echo $suspended_count; ?></strong>
                    </div>
                    <div class="mr-chip">
                        <span>Free Lead Magnets:</span>
                        <strong><?php echo $free_count; ?></strong>
                    </div>
                </div>
            </div>

            <!-- Flash Notices -->
            <?php if ($updated): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">✅ Success: Master report pricing, categories, and catalog configurations updated successfully!</p>
                </div>
            <?php elseif ($added): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">🎉 New report added successfully to catalog and sales cards!</p>
                </div>
            <?php elseif ($deleted): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">🗑️ Report deleted successfully from catalog.</p>
                </div>
            <?php elseif ($status_changed === 'suspended'): ?>
                <div class="notice notice-warning is-dismissible" style="border-left-color:#f59e0b; padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">⏸ Report has been <strong>SUSPENDED</strong>. It is now hidden from customer sales cards, catalog, and checkout.</p>
                </div>
            <?php elseif ($status_changed === 'reactivated'): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">▶ Report has been <strong>REACTIVATED</strong>. It is now live and purchasable across the website.</p>
                </div>
            <?php elseif ($error === 'cannot_delete_system'): ?>
                <div class="notice notice-error is-dismissible" style="padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">⚠️ Core built-in system reports cannot be deleted. You can safely <strong>Suspend</strong> them instead.</p>
                </div>
            <?php elseif ($error === 'duplicate_slug'): ?>
                <div class="notice notice-error is-dismissible" style="padding:10px 14px;">
                    <p style="margin:0; font-weight:600;">⚠️ A report with this Slug / Unique ID already exists. Please choose a unique slug.</p>
                </div>
            <?php endif; ?>

            <!-- 2. Action Bar & Filter Navigation -->
            <div class="mr-filter-nav">
                <div class="mr-tabs">
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'mr-report-pricing', 'tab' => 'all'], admin_url('admin.php'))); ?>" class="mr-tab-link <?php echo $current_tab === 'all' ? 'active' : ''; ?>">
                        All Reports <span class="badge"><?php echo $total_count; ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'mr-report-pricing', 'tab' => 'active'], admin_url('admin.php'))); ?>" class="mr-tab-link <?php echo $current_tab === 'active' ? 'active' : ''; ?>">
                        Active Live <span class="badge"><?php echo $active_count; ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'mr-report-pricing', 'tab' => 'suspended'], admin_url('admin.php'))); ?>" class="mr-tab-link <?php echo $current_tab === 'suspended' ? 'active' : ''; ?>">
                        Suspended <span class="badge"><?php echo $suspended_count; ?></span>
                    </a>
                </div>

                <div>
                    <button type="button" id="mr-toggle-add-btn" class="button button-primary" style="font-weight:700; height:36px; display:inline-flex; align-items:center; gap:6px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px; line-height:22px;"></span>
                        Add New Report
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mr-reports-catalog-manager')); ?>" class="button button-secondary" style="height:36px; line-height:34px; margin-left:6px;" title="Go to Sales Cards creative content manager">
                        Sales Cards Manager ↗
                    </a>
                </div>
            </div>

            <!-- 3. Add New Report Drawer / Card (Hidden by default, toggled via button) -->
            <div id="mr-add-report-drawer" class="mr-add-modal-card" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                    <div>
                        <h3 style="margin:0 0 4px 0; font-size:17px; font-weight:800; color:#7A1F3D;">
                            ➕ Introduce a New Report to MeraRashifal Catalog
                        </h3>
                        <p style="margin:0; font-size:13px; color:#6B5E57;">
                            Configure initial pricing, internal code, and description. This will automatically seed a matching sales card in Sales Cards Manager.
                        </p>
                    </div>
                    <button type="button" id="mr-close-add-btn" class="button button-small" style="color:#64748b;">✖ Close</button>
                </div>

                <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="mr_add_new_report">
                    <?php wp_nonce_field('mr_add_report_nonce', 'mr_nonce'); ?>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Report Display Name *</label>
                            <input type="text" name="new_report_name" id="mr-new-name" placeholder="e.g. Master Career & Profession Transit Report" class="large-text" required style="width:100%;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Unique ID / Slug *</label>
                            <input type="text" name="new_report_slug" id="mr-new-slug" placeholder="e.g. career_transit_report" class="large-text" required style="width:100%;">
                            <span style="font-size:11px; color:#64748b;">Lowercase letters, numbers, and underscores only.</span>
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Internal Ref Code</label>
                            <input type="text" name="new_report_code" id="mr-new-code" placeholder="e.g. MR_CPTR" class="regular-text" style="width:100%;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Category</label>
                            <select name="new_report_category" style="width:100%;">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Pages Count Tag</label>
                            <input type="text" name="new_report_pages" placeholder="e.g. 24 Pages" value="20 Pages" style="width:100%;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">List Price (₹) *</label>
                            <input type="number" step="1" min="0" name="new_report_list_price" placeholder="999" value="999" required style="width:100%;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Sale Price (₹) *</label>
                            <input type="number" step="1" min="0" name="new_report_sale_price" placeholder="99" value="99" required style="width:100%;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Initial Status</label>
                            <label style="display:inline-flex; align-items:center; gap:6px; margin-top:8px; font-weight:600; font-size:13px;">
                                <input type="checkbox" name="new_report_active" value="1" checked>
                                <span>Active (Immediately Visible)</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Short Description / Summary</label>
                        <textarea name="new_report_desc" rows="2" style="width:100%; border-radius:6px;" placeholder="Comprehensive Vedic astrology audit exploring planetary movements..."></textarea>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" id="mr-cancel-add-btn" class="button">Cancel</button>
                        <button type="submit" class="button button-primary" style="font-weight:700; padding:4px 18px;">Create &amp; Activate Report</button>
                    </div>
                </form>
            </div>

            <!-- 4. Main Batch Editable Reports Table -->
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mr-pricing-batch-form">
                <input type="hidden" name="action" value="mr_save_report_pricing">
                <?php wp_nonce_field('mr_save_pricing_nonce', 'mr_nonce'); ?>

                <div class="mr-table-card">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 110px;">Ref Code</th>
                                <th style="width: 180px;">Fixed ID (Slug)</th>
                                <th style="width: 270px;">Report Display Name</th>
                                <th style="width: 140px;">Category</th>
                                <th style="width: 105px;">Pages</th>
                                <th style="width: 115px;">List Price (₹)</th>
                                <th style="width: 115px;">Sale Price (₹)</th>
                                <th style="width: 110px;">Discount</th>
                                <th style="width: 140px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filtered_reports)): ?>
                                <tr>
                                    <td colspan="10" style="text-align:center; padding:36px 14px; color:#64748b;">
                                        No reports found under this filter view.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filtered_reports as $slug => $r): 
                                    $list = floatval($r['list_price'] ?? 0);
                                    $sale = floatval($r['sale_price'] ?? 0);
                                    $is_act = !empty($r['active']);
                                    $is_sys = !empty($r['is_system']);
                                    $discount_pct = ($list > $sale && $list > 0) ? round((($list - $sale) / $list) * 100) : 0;
                                    $toggle_url = wp_nonce_url(admin_url('admin.php?action=mr_toggle_report_status&slug=' . $slug), 'mr_toggle_' . $slug);
                                    $delete_url = wp_nonce_url(admin_url('admin.php?action=mr_delete_custom_report&slug=' . $slug), 'mr_delete_' . $slug);
                                ?>
                                    <tr data-slug="<?php echo esc_attr($slug); ?>" style="<?php echo !$is_act ? 'opacity:0.75; background:#fff1f2;' : ''; ?>">
                                        <!-- Status Badge -->
                                        <td>
                                            <?php if ($is_act): ?>
                                                <span class="mr-status-pill mr-status-active" title="Report is live and purchasable">● Active</span>
                                            <?php else: ?>
                                                <span class="mr-status-pill mr-status-suspended" title="Report is suspended and hidden">⏸ Suspended</span>
                                            <?php endif; ?>
                                            <input type="hidden" name="reports[<?php echo esc_attr($slug); ?>][active]" value="<?php echo $is_act ? '1' : '0'; ?>" class="mr-hidden-active-input">
                                        </td>

                                        <!-- Ref Code -->
                                        <td>
                                            <input type="text" name="reports[<?php echo esc_attr($slug); ?>][internal_code]" value="<?php echo esc_attr($r['internal_code'] ?? ''); ?>" style="width:100%; font-family:monospace; font-weight:700; font-size:11.5px; text-transform:uppercase;">
                                        </td>

                                        <!-- Slug -->
                                        <td>
                                            <code style="background:#f1f5f9; color:#0f172a; padding:3px 6px; border-radius:4px; font-size:11px; word-break:break-all;">
                                                <?php echo esc_html($slug); ?>
                                            </code>
                                            <?php if ($is_sys): ?>
                                                <span title="Core System Report" style="font-size:10px; color:#94a3b8; display:block; margin-top:2px;">[Core System]</span>
                                            <?php else: ?>
                                                <span title="Custom User Report" style="font-size:10px; color:#0284c7; display:block; margin-top:2px;">[Custom Report]</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Display Name & Description Expander -->
                                        <td>
                                            <input type="text" name="reports[<?php echo esc_attr($slug); ?>][name]" value="<?php echo esc_attr($r['name']); ?>" style="width:100%; font-weight:700; font-size:13px;" required>
                                            <span class="mr-desc-expander" onclick="jQuery('#mr-desc-row-<?php echo esc_js($slug); ?>').toggle();">
                                                ✎ Edit Description
                                            </span>
                                            <div id="mr-desc-row-<?php echo esc_attr($slug); ?>" style="display:none; margin-top:8px;">
                                                <textarea name="reports[<?php echo esc_attr($slug); ?>][description]" rows="2" style="width:100%; font-size:11.5px; border-radius:6px;" placeholder="Report summary..."><?php echo esc_textarea($r['description'] ?? ''); ?></textarea>
                                            </div>
                                        </td>

                                        <!-- Category Dropdown -->
                                        <td>
                                            <select name="reports[<?php echo esc_attr($slug); ?>][category]" style="width:100%; font-size:12px;">
                                                <?php 
                                                $cur_cat = $r['category'] ?? 'Vedic Reading';
                                                $cat_found = false;
                                                foreach ($categories as $cat): 
                                                    if ($cur_cat === $cat) $cat_found = true;
                                                ?>
                                                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($cur_cat, $cat); ?>><?php echo esc_html($cat); ?></option>
                                                <?php endforeach; ?>
                                                <?php if (!$cat_found && !empty($cur_cat)): ?>
                                                    <option value="<?php echo esc_attr($cur_cat); ?>" selected><?php echo esc_html($cur_cat); ?></option>
                                                <?php endif; ?>
                                            </select>
                                        </td>

                                        <!-- Pages Tag -->
                                        <td>
                                            <input type="text" name="reports[<?php echo esc_attr($slug); ?>][pages]" value="<?php echo esc_attr($r['pages'] ?? ''); ?>" style="width:100%; font-size:12px; font-weight:600;" placeholder="26 Pages">
                                        </td>

                                        <!-- List Price -->
                                        <td>
                                            <div style="display:flex; align-items:center;">
                                                <span style="color:#64748b; font-weight:bold; margin-right:4px;">₹</span>
                                                <input type="number" step="1" min="0" class="mr-input-list-price" name="reports[<?php echo esc_attr($slug); ?>][list_price]" value="<?php echo esc_attr($list); ?>" style="width:75px; font-weight:600;" required>
                                            </div>
                                        </td>

                                        <!-- Sale Price -->
                                        <td>
                                            <div style="display:flex; align-items:center;">
                                                <span style="color:#16a34a; font-weight:bold; margin-right:4px;">₹</span>
                                                <input type="number" step="1" min="0" class="mr-input-sale-price" name="reports[<?php echo esc_attr($slug); ?>][sale_price]" value="<?php echo esc_attr($sale); ?>" style="width:75px; font-weight:800; color:<?php echo $sale == 0 ? '#047857' : '#16a34a'; ?>;" required>
                                            </div>
                                            <?php if ($sale == 0): ?>
                                                <span style="font-size:10px; font-weight:800; color:#047857; text-transform:uppercase;">FREE</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Discount Badge -->
                                        <td>
                                            <span class="mr-discount-badge" style="background:#dcfce7; color:#15803d; font-size:11px; padding:3px 7px; border-radius:10px; font-weight:700; white-space:nowrap;">
                                                <?php echo $sale == 0 ? '100% FREE' : ($discount_pct > 0 ? $discount_pct . '% OFF' : 'Standard'); ?>
                                            </span>
                                        </td>

                                        <!-- Quick Actions (Suspend / Reactivate / Delete) -->
                                        <td style="text-align:right;">
                                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                                <?php if ($is_act): ?>
                                                    <a href="<?php echo esc_url($toggle_url); ?>" class="mr-btn-action mr-btn-suspend" title="Suspend report: Hides it from catalog and orders" onclick="return confirm('Suspend this report? Customers will not be able to order it.');">
                                                        ⏸ Suspend
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo esc_url($toggle_url); ?>" class="mr-btn-action mr-btn-reactivate" title="Reactivate report: Makes it live again">
                                                        ▶ Reactivate
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (!$is_sys): ?>
                                                    <a href="<?php echo esc_url($delete_url); ?>" class="mr-btn-action mr-btn-delete" title="Permanently delete custom report" onclick="return confirm('Permanently delete this custom report? This action cannot be undone.');">
                                                        🗑️
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Sticky / Footer Save Bar -->
                    <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <p style="margin:0; font-size:13px; color:#64748b;">
                            <em>💡 Tip: Setting List Price higher than Sale Price creates an automatic strike-through discount badge across sales cards and checkout.</em>
                        </p>
                        <button type="submit" class="button button-primary mr-btn-primary-hero">
                            Save All Changes &amp; Sync Across Site
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
        (function($) {
            $(document).ready(function() {
                // Toggle Add New Report Drawer
                $('#mr-toggle-add-btn').on('click', function(e) {
                    e.preventDefault();
                    $('#mr-add-report-drawer').slideToggle(200);
                    $('#mr-new-name').focus();
                });
                $('#mr-close-add-btn, #mr-cancel-add-btn').on('click', function(e) {
                    e.preventDefault();
                    $('#mr-add-report-drawer').slideUp(150);
                });

                // Auto slugify name if slug field is empty
                $('#mr-new-name').on('input', function() {
                    var val = $(this).val();
                    var slugInput = $('#mr-new-slug');
                    if (!slugInput.data('user-edited')) {
                        var slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                        slugInput.val(slug);
                        var codeInput = $('#mr-new-code');
                        if (!codeInput.data('user-edited')) {
                            codeInput.val('MR_' + slug.replace(/[^a-z0-9]/gi, '').substring(0, 4).toUpperCase());
                        }
                    }
                });
                $('#mr-new-slug').on('input', function() { $(this).data('user-edited', true); });
                $('#mr-new-code').on('input', function() { $(this).data('user-edited', true); });

                // Live discount badge updater
                $('tr[data-slug]').each(function() {
                    var row = $(this);
                    var listInput = row.find('.mr-input-list-price');
                    var saleInput = row.find('.mr-input-sale-price');
                    var badge = row.find('.mr-discount-badge');

                    function updateBadge() {
                        var l = parseFloat(listInput.val()) || 0;
                        var s = parseFloat(saleInput.val()) || 0;
                        if (s === 0) {
                            badge.text('100% FREE').css({'background': '#dcfce7', 'color': '#15803d'});
                        } else if (l > s && l > 0) {
                            var pct = Math.round(((l - s) / l) * 100);
                            badge.text(pct + '% OFF').css({'background': '#dcfce7', 'color': '#15803d'});
                        } else {
                            badge.text('Standard').css({'background': '#f1f5f9', 'color': '#64748b'});
                        }
                    }

                    listInput.on('input change', updateBadge);
                    saleInput.on('input change', updateBadge);
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Frontend Catalog Display Override Script
     */
    public static function sync_frontend_catalog_prices_script() {
        if (!is_page('reports-catalog')) {
            return;
        }

        $reports = self::get_config();
        ?>
        <script>
        (function($) {
            $(document).ready(function() {
                var pricingConfig = <?php echo json_encode($reports); ?>;

                $('.mr-alacarte-card').each(function() {
                    var card = $(this);
                    var slug = card.data('slug');

                    if (pricingConfig[slug]) {
                        var cfg = pricingConfig[slug];

                        // If suspended by admin, hide or disable card
                        if (cfg.active === 0) {
                            card.hide();
                            return;
                        }

                        if (cfg.name) {
                            card.data('title', cfg.name);
                            card.find('.mr-card-title').text(cfg.name);
                        }

                        var listPrice = parseFloat(cfg.list_price);
                        var salePrice = parseFloat(cfg.sale_price);

                        var discountHtml = '';
                        if (listPrice > salePrice && listPrice > 0) {
                            var pct = Math.round(((listPrice - salePrice) / listPrice) * 100);
                            discountHtml = '<span style="text-decoration:line-through; color:#94a3b8; font-size:14px; margin-right:6px;">₹' + listPrice + '</span>' +
                                           '<span style="font-weight:700; color:#10b981; font-size:22px;">₹' + salePrice + '</span>' +
                                           '<span style="margin-left:8px; background:#dcfce7; color:#15803d; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:700;">' + pct + '% OFF</span>';
                        } else {
                            discountHtml = '<span style="font-weight:700; color:#10b981; font-size:22px;">₹' + salePrice + '</span>';
                        }

                        card.find('.mr-price-display').html(discountHtml);
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Register Custom Catalog Shortcodes
     */
    public static function register_catalog_shortcodes() {
        add_shortcode('mr_reports', [__CLASS__, 'render_custom_reports_catalog']);
        add_shortcode('mr_reports_listing', [__CLASS__, 'render_custom_reports_catalog']);
        remove_shortcode('mr_reports_catalog');
        add_shortcode('mr_reports_catalog', [__CLASS__, 'render_custom_reports_catalog']);
        remove_shortcode('mr_alacarte_catalog');
        add_shortcode('mr_alacarte_catalog', [__CLASS__, 'render_custom_reports_catalog']);
    }

    /**
     * Catalog Grid Renderer
     */
    public static function render_custom_reports_catalog($atts) {
        if (class_exists('MR_Catalog_Manager')) {
            return MR_Catalog_Manager::render_sales_grid($atts);
        }
        return '';
    }
}

// Global Backward-Compatibility Helpers (handled by snippet or class methods)
// Delegated to MR_Report_Pricing_Admin::get_default_definitions() and snippet 88

