<?php
/**
 * Dynamic Bundle & Upsell Engine
 * Handles customizable report bundling (Single, Duo, Trio / Best Buy),
 * pairing sister reports, dynamic pricing, and bonus AI chat credits.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Bundles {

    const OPTION_KEY = 'mr_report_bundles_config';
    const GLOBAL_OPTION_KEY = 'mr_report_bundles_global';
    const CHAT_CREDIT_RATE = 1.0; // ₹1 per AI chat question credit

    /**
     * Sourced Standalone Price Master: Fetches current standalone Sale Price and Listing MRP from Sales Cards Manager
     */
    public static function get_standalone_pricing($slug) {
        $slug = self::normalize_slug($slug);
        $all_reports = self::get_available_reports();
        $meta = $all_reports[$slug] ?? [];

        $name = $meta['name'] ?? ucwords(str_replace('_', ' ', $slug));
        $sale = floatval($meta['def_price'] ?? 299);
        $reg  = floatval($meta['def_reg'] ?? 599);

        if (class_exists('MR_Catalog_Manager')) {
            $cards = MR_Catalog_Manager::get_all_cards();
            $card = $cards[$slug] ?? [];
            if (empty($card) && ($slug === 'numerology' || $slug === 'numerology_report')) {
                $card = $cards['numerology'] ?? ($cards['numerology_report'] ?? []);
            }
            if (!empty($card)) {
                if (isset($card['price_sale'])) {
                    $sale = floatval($card['price_sale']);
                }
                if (isset($card['price_regular'])) {
                    $reg = floatval($card['price_regular']);
                }
                if (!empty($card['name'])) {
                    $name = $card['name'];
                }
            }
        }

        return [
            'slug'          => $slug,
            'name'          => $name,
            'price_sale'    => $sale,
            'price_regular' => $reg,
        ];
    }

    /**
     * Clean price formatter (preserves decimals if fractional, e.g. 193.5 or 200)
     */
    public static function format_price($price) {
        $float = floatval($price);
        if (floor($float) == $float) {
            return number_format($float, 0);
        }
        return rtrim(rtrim(number_format($float, 2, '.', ''), '0'), '.');
    }

    /**
     * Dynamic Bundle Tier Pricing Calculator
     *
     * Formula per user specification:
     * Combined Regular / Listing Price = Sum(Standalone Listing Price) + (Chat Credits * ₹1)
     * Combined Regular Sale Price    = Sum(Standalone Sale Price) + (Chat Credits * ₹1)
     * Bundle Sale Price              = Combined Regular Sale Price * (1 - Discount % / 100)
     * Savings                        = Combined Regular / Listing Price - Bundle Sale Price
     * Overall Discount %             = (Savings / Combined Regular / Listing Price) * 100
     */
    public static function calculate_tier_pricing($report_slugs, $chat_credits = 0, $discount_pct = 0) {
        if (!is_array($report_slugs)) {
            $report_slugs = !empty($report_slugs) ? [$report_slugs] : [];
        }
        $chat_credits  = max(0, intval($chat_credits));
        $discount_pct  = max(0, min(99, floatval($discount_pct)));
        $credits_value = round($chat_credits * self::CHAT_CREDIT_RATE, 2);

        $sum_sale    = 0.0;
        $sum_regular = 0.0;
        $breakdown   = [];

        foreach ($report_slugs as $slug) {
            if (empty($slug)) continue;
            $p = self::get_standalone_pricing($slug);
            $sum_sale    += $p['price_sale'];
            $sum_regular += $p['price_regular'];
            $breakdown[]  = $p;
        }

        $combined_sale    = round($sum_sale + $credits_value, 2);
        $combined_regular = round($sum_regular + $credits_value, 2);

        // Apply configured bundle discount % on combined sale price
        $bundle_sale = round($combined_sale * (1 - ($discount_pct / 100)), 2);
        $savings     = max(0, round($combined_regular - $bundle_sale, 2));
        $overall_discount_pct = ($combined_regular > 0) ? round(($savings / $combined_regular) * 100) : 0;

        return [
            'sum_sale'             => $sum_sale,
            'sum_regular'          => $sum_regular,
            'chat_credits'         => $chat_credits,
            'credits_value'        => $credits_value,
            'combined_sale'        => $combined_sale,
            'combined_regular'     => $combined_regular,
            'discount_pct'         => $discount_pct,
            'price_sale'           => $bundle_sale,
            'price_regular'        => $combined_regular,
            'savings'              => $savings,
            'overall_discount_pct' => $overall_discount_pct,
            'breakdown'            => $breakdown,
        ];
    }

    /**
     * Dynamically synchronize tier pricing with current Sales Cards Manager standalone prices
     */
    public static function apply_live_pricing_sync($cfg, $report_slug) {
        $slug = self::normalize_slug($report_slug);

        // 1. Single Report Tier Sync (Exact standalone master prices from Sales Cards Manager)
        $single_credits = isset($cfg['tier_single']['chat_credits']) ? intval($cfg['tier_single']['chat_credits']) : 0;
        $single_calc    = self::calculate_tier_pricing([$slug], 0, 0);
        $cfg['tier_single']['price_sale']           = $single_calc['sum_sale'];
        $cfg['tier_single']['price_regular']        = $single_calc['sum_regular'];
        $cfg['tier_single']['discount_pct']         = 0;
        $cfg['tier_single']['chat_credits']         = $single_credits;
        $cfg['tier_single']['overall_discount_pct'] = ($single_calc['sum_regular'] > 0) ? round((($single_calc['sum_regular'] - $single_calc['sum_sale']) / $single_calc['sum_regular']) * 100) : 0;

        // 2. Duo Report Tier Sync
        $duo_paired  = !empty($cfg['tier_duo']['paired_reports']) ? (array)$cfg['tier_duo']['paired_reports'] : [];
        $duo_credits = isset($cfg['tier_duo']['chat_credits']) ? intval($cfg['tier_duo']['chat_credits']) : 15;
        $duo_disc    = isset($cfg['tier_duo']['discount_pct']) ? floatval($cfg['tier_duo']['discount_pct']) : 10;
        $duo_all     = array_values(array_filter(array_merge([$slug], $duo_paired)));
        $duo_calc    = self::calculate_tier_pricing($duo_all, $duo_credits, $duo_disc);

        $cfg['tier_duo']['price_sale']           = $duo_calc['price_sale'];
        $cfg['tier_duo']['price_regular']        = $duo_calc['price_regular'];
        $cfg['tier_duo']['discount_pct']         = $duo_disc;
        $cfg['tier_duo']['savings']              = $duo_calc['savings'];
        $cfg['tier_duo']['overall_discount_pct'] = $duo_calc['overall_discount_pct'];
        $cfg['tier_duo']['combined_sale']        = $duo_calc['combined_sale'];

        // 3. Trio Report Tier Sync
        $trio_paired  = !empty($cfg['tier_trio']['paired_reports']) ? (array)$cfg['tier_trio']['paired_reports'] : [];
        $trio_credits = isset($cfg['tier_trio']['chat_credits']) ? intval($cfg['tier_trio']['chat_credits']) : 30;
        $trio_disc    = isset($cfg['tier_trio']['discount_pct']) ? floatval($cfg['tier_trio']['discount_pct']) : 15;
        $trio_all     = array_values(array_filter(array_merge([$slug], $trio_paired)));
        $trio_calc    = self::calculate_tier_pricing($trio_all, $trio_credits, $trio_disc);

        $cfg['tier_trio']['price_sale']           = $trio_calc['price_sale'];
        $cfg['tier_trio']['price_regular']        = $trio_calc['price_regular'];
        $cfg['tier_trio']['discount_pct']         = $trio_disc;
        $cfg['tier_trio']['savings']              = $trio_calc['savings'];
        $cfg['tier_trio']['overall_discount_pct'] = $trio_calc['overall_discount_pct'];
        $cfg['tier_trio']['combined_sale']        = $trio_calc['combined_sale'];

        // If badge text is generic or has a legacy discount, update badge text with fresh overall discount
        $badge = $cfg['tier_trio']['badge'] ?? '';
        if (empty($badge) || preg_match('/BEST BUY\s*-\s*SAVE\s*\d+%/i', $badge)) {
            $cfg['tier_trio']['badge'] = 'BEST BUY - SAVE ' . $trio_calc['overall_discount_pct'] . '%';
        }

        return $cfg;
    }

    /**
     * Master report directory with titles and aliases for pairing dropdowns
     */
    public static function get_available_reports() {
        return [
            'yearly_horoscope_long'   => [
                'name'      => 'Yearly Horoscope Detailed Report (2026-2027)',
                'slug'      => 'yearly_horoscope_long',
                'page_slug' => 'yearly-horoscope-report',
                'icon'      => 'fa-solid fa-calendar-days',
                'pages'     => '26 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'kundli_predictions'      => [
                'name'      => 'Janam Kundli & Life Predictions Report',
                'slug'      => 'kundli_predictions',
                'page_slug' => 'janam-kundali-master-report',
                'icon'      => 'fa-solid fa-chart-pie',
                'pages'     => '26 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'mahadasha_timing'        => [
                'name'      => 'Vedic Mahadasha & Antardasha Timing Report',
                'slug'      => 'mahadasha_timing',
                'page_slug' => 'dasha-timing-report',
                'icon'      => 'fa-solid fa-clock',
                'pages'     => '25 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'dosh_remedial'           => [
                'name'      => 'Vedic Kundli Dosh & Remedial Report',
                'slug'      => 'dosh_remedial',
                'page_slug' => 'kundali-dosh-report',
                'icon'      => 'fa-solid fa-shield-halved',
                'pages'     => '20 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'kundli_matching'         => [
                'name'      => 'Kundli Matching & Relationship Predictions Report',
                'slug'      => 'kundli_matching',
                'page_slug' => 'kundali-and-gun-matching-report',
                'icon'      => 'fa-solid fa-ring',
                'pages'     => '20 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'numerology'              => [
                'name'      => 'Master Numerology & Vedic Integration Report',
                'slug'      => 'numerology',
                'page_slug' => 'numerology-report',
                'icon'      => 'fa-solid fa-star',
                'pages'     => '22 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'palmistry_report'        => [
                'name'      => 'Master Hast Rekha & Palmistry Analysis Report',
                'slug'      => 'palmistry_report',
                'page_slug' => 'palm-reading-report',
                'icon'      => 'fa-solid fa-hand',
                'pages'     => '20 Pages',
                'def_price' => 299,
                'def_reg'   => 599,
            ],
            'monthly_horoscope_vedic' => [
                'name'      => 'Monthly Horoscope Report (Vedic)',
                'slug'      => 'monthly_horoscope_vedic',
                'page_slug' => 'monthly-horoscope-report',
                'icon'      => 'fa-solid fa-calendar',
                'pages'     => '15 Pages',
                'def_price' => 199,
                'def_reg'   => 399,
            ],
            'yearly_horoscope_vedic'  => [
                'name'      => 'Yearly Horoscope Short Report (2026)',
                'slug'      => 'yearly_horoscope_vedic',
                'page_slug' => 'yearly-horoscope-snapshot-report',
                'icon'      => 'fa-solid fa-calendar-check',
                'pages'     => '5 Pages',
                'def_price' => 0,
                'def_reg'   => 199,
            ],
        ];
    }

    /**
     * Normalize report slug aliases to canonical keys
     */
    public static function normalize_slug($slug) {
        $slug = sanitize_key($slug);
        if ($slug === 'numerology_report' || $slug === 'numerology-report' || $slug === 'numero_report') {
            return 'numerology';
        }
        if ($slug === 'palm-reading-report') {
            return 'palmistry_report';
        }
        if ($slug === 'yearly-horoscope-report' || $slug === 'landingpage') {
            return 'yearly_horoscope_long';
        }
        if ($slug === 'janam-kundali-master-report') {
            return 'kundli_predictions';
        }
        if ($slug === 'dasha-timing-report') {
            return 'mahadasha_timing';
        }
        if ($slug === 'kundali-dosh-report') {
            return 'dosh_remedial';
        }
        if ($slug === 'kundali-and-gun-matching-report') {
            return 'kundli_matching';
        }
        if ($slug === 'yearly-horoscope-snapshot-report') {
            return 'yearly_horoscope_vedic';
        }
        if ($slug === 'monthly-horoscope-report') {
            return 'monthly_horoscope_vedic';
        }
        return $slug;
    }

    /**
     * Get global bundle settings
     */
    public static function get_global_settings() {
        $defaults = [
            'enabled'                => true,
            'default_duo_credits'    => 15,
            'default_trio_credits'   => 30,
            'default_currency_symbol'=> '₹',
            'show_chat_credit_badge' => true,
            'show_fbt_bundle_box'    => true,
            'show_hero_selector'     => true,
        ];
        $saved = get_option(self::GLOBAL_OPTION_KEY, []);
        return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
    }

    /**
     * Save global settings
     */
    public static function save_global_settings($settings) {
        return update_option(self::GLOBAL_OPTION_KEY, $settings);
    }

    /**
     * Generate default configuration for any report
     */
    public static function get_default_config_for($report_slug) {
        $slug = self::normalize_slug($report_slug);
        $all_reports = self::get_available_reports();
        $base_meta = $all_reports[$slug] ?? [
            'name'      => ucwords(str_replace('_', ' ', $slug)),
            'def_price' => 299,
            'def_reg'   => 599,
        ];

        // Sensible default pairings per report
        $default_pairings = [
            'yearly_horoscope_long' => [
                'duo_paired'  => 'mahadasha_timing',
                'trio_paired' => ['mahadasha_timing', 'kundli_predictions'],
                'duo_title'   => 'Yearly Forecast + Mahadasha Timing Duo',
                'trio_title'  => '360° Vedic Annual & Lifetime Mastery Trio',
            ],
            'kundli_predictions' => [
                'duo_paired'  => 'mahadasha_timing',
                'trio_paired' => ['mahadasha_timing', 'dosh_remedial'],
                'duo_title'   => 'Janam Kundli + Mahadasha Timing Duo',
                'trio_title'  => 'Complete Kundli, Timing & Remedial Suite',
            ],
            'mahadasha_timing' => [
                'duo_paired'  => 'kundli_predictions',
                'trio_paired' => ['kundli_predictions', 'yearly_horoscope_long'],
                'duo_title'   => 'Dasha Timing + Janam Kundli Duo',
                'trio_title'  => 'Complete Predictive Life Cycles Suite',
            ],
            'dosh_remedial' => [
                'duo_paired'  => 'kundli_predictions',
                'trio_paired' => ['kundli_predictions', 'mahadasha_timing'],
                'duo_title'   => 'Kundli Dosh & Janam Kundli Duo',
                'trio_title'  => 'Full Kundli, Planetary Dosh & Remedy Suite',
            ],
            'kundli_matching' => [
                'duo_paired'  => 'kundli_predictions',
                'trio_paired' => ['kundli_predictions', 'dosh_remedial'],
                'duo_title'   => 'Gun Milan & Birth Chart Compatibility Duo',
                'trio_title'  => 'Matrimonial Destiny & Planetary Harmony Suite',
            ],
            'numerology' => [
                'duo_paired'  => 'yearly_horoscope_long',
                'trio_paired' => ['yearly_horoscope_long', 'kundli_predictions'],
                'duo_title'   => 'Numerology Audit + Yearly Forecast Duo',
                'trio_title'  => 'Sankhya Shastra & Vedic Horoscope Triad',
            ],
            'palmistry_report' => [
                'duo_paired'  => 'kundli_predictions',
                'trio_paired' => ['kundli_predictions', 'numerology'],
                'duo_title'   => 'Hast Rekha & Janam Kundli Duo',
                'trio_title'  => 'Cosmic Trinity: Palmistry + Kundli + Numerology',
            ],
            'monthly_horoscope_vedic' => [
                'duo_paired'  => 'yearly_horoscope_long',
                'trio_paired' => ['yearly_horoscope_long', 'mahadasha_timing'],
                'duo_title'   => 'Monthly & Annual Forecast Duo',
                'trio_title'  => '12-Month Real-Time Astrological Guidance Suite',
            ],
            'yearly_horoscope_vedic' => [
                'duo_paired'  => 'yearly_horoscope_long',
                'trio_paired' => ['yearly_horoscope_long', 'kundli_predictions'],
                'duo_title'   => 'Essential Snapshot + 26-Page Deep Dive Duo',
                'trio_title'  => 'Full Vedic Annual & Natal Chart Suite',
            ],
        ];

        $pair = $default_pairings[$slug] ?? [
            'duo_paired'  => ($slug === 'kundli_predictions') ? 'mahadasha_timing' : 'kundli_predictions',
            'trio_paired' => [($slug === 'kundli_predictions') ? 'mahadasha_timing' : 'kundli_predictions', 'dosh_remedial'],
            'duo_title'   => 'Smart Astrological Duo (2 Reports)',
            'trio_title'  => 'Complete Astrological Suite (3 Reports + AI Chat)',
        ];

        $base_meta   = self::get_standalone_pricing($slug);
        $single_sale = floatval($base_meta['price_sale']);
        $single_reg  = floatval($base_meta['price_regular']);

        $duo_paired_arr  = [$pair['duo_paired']];
        $duo_calc        = self::calculate_tier_pricing(array_merge([$slug], $duo_paired_arr), 15, 10);

        $trio_paired_arr = (array)$pair['trio_paired'];
        $trio_calc       = self::calculate_tier_pricing(array_merge([$slug], $trio_paired_arr), 30, 15);

        return [
            'report_slug'    => $slug,
            'bundle_enabled' => true,
            'tier_single'    => [
                'enabled'              => true,
                'title'                => 'Single Report',
                'subtitle'             => 'Complete ' . esc_html($base_meta['name']),
                'price_regular'        => $single_reg,
                'price_sale'           => $single_sale,
                'badge'                => 'Standard',
                'chat_credits'         => 0,
                'discount_pct'         => 0,
                'overall_discount_pct' => ($single_reg > 0) ? round((($single_reg - $single_sale) / $single_reg) * 100) : 0,
            ],
            'tier_duo'       => [
                'enabled'              => true,
                'title'                => $pair['duo_title'],
                'subtitle'             => '2 In-Depth Dossiers + 15 AI Follow-up Questions',
                'paired_reports'       => $duo_paired_arr,
                'discount_pct'         => 10,
                'price_regular'        => $duo_calc['price_regular'],
                'price_sale'           => $duo_calc['price_sale'],
                'savings'              => $duo_calc['savings'],
                'overall_discount_pct' => $duo_calc['overall_discount_pct'],
                'combined_sale'        => $duo_calc['combined_sale'],
                'badge'                => 'POPULAR',
                'chat_credits'         => 15,
                'highlights'           => '2 Publication-Grade PDF Reports &bull; 15 Niyati AI Chat Credits Included',
            ],
            'tier_trio'      => [
                'enabled'              => true,
                'title'                => $pair['trio_title'],
                'subtitle'             => '3 Complete Master Reports + 30 AI Chat Credits',
                'is_best_buy'          => true,
                'paired_reports'       => $trio_paired_arr,
                'discount_pct'         => 15,
                'price_regular'        => $trio_calc['price_regular'],
                'price_sale'           => $trio_calc['price_sale'],
                'savings'              => $trio_calc['savings'],
                'overall_discount_pct' => $trio_calc['overall_discount_pct'],
                'combined_sale'        => $trio_calc['combined_sale'],
                'badge'                => 'BEST BUY - SAVE ' . $trio_calc['overall_discount_pct'] . '%',
                'chat_credits'         => 30,
                'highlights'           => '3 Complete Sacred Reports (65+ Pages Total) &bull; 30 Niyati AI Interactive Consultations &bull; Highest Value',
            ]
        ];
    }

    /**
     * Get all bundle configs from database, dynamically synced with Sales Cards Manager
     */
    public static function get_all_bundles() {
        $saved = get_option(self::OPTION_KEY, []);
        $all_reports = self::get_available_reports();
        $configs = [];

        foreach ($all_reports as $slug => $meta) {
            $def = self::get_default_config_for($slug);
            if (!empty($saved[$slug]) && is_array($saved[$slug])) {
                $merged = array_replace_recursive($def, $saved[$slug]);
            } else {
                $merged = $def;
            }
            $configs[$slug] = self::apply_live_pricing_sync($merged, $slug);
        }

        return $configs;
    }

    /**
     * Get specific bundle configuration for a report (always dynamically live-synced)
     */
    public static function get_bundle_for_report($report_slug) {
        $slug  = self::normalize_slug($report_slug);
        $saved = get_option(self::OPTION_KEY, []);
        $def   = self::get_default_config_for($slug);

        if (!empty($saved[$slug]) && is_array($saved[$slug])) {
            $merged = array_replace_recursive($def, $saved[$slug]);
        } else {
            $merged = $def;
        }

        return self::apply_live_pricing_sync($merged, $slug);
    }

    /**
     * Save bundle configuration for a specific report or multiple reports
     */
    public static function save_bundle_config($report_slug, $data) {
        $slug = self::normalize_slug($report_slug);
        $saved = get_option(self::OPTION_KEY, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        $saved[$slug] = $data;
        return update_option(self::OPTION_KEY, $saved);
    }

    /**
     * Reset all bundle configurations to smart defaults
     */
    public static function reset_to_defaults() {
        return delete_option(self::OPTION_KEY);
    }

    /**
     * Helper to retrieve report title by slug
     */
    public static function get_report_name($slug) {
        $slug = self::normalize_slug($slug);
        $reports = self::get_available_reports();
        return $reports[$slug]['name'] ?? ucwords(str_replace('_', ' ', $slug));
    }

    /**
     * Helper to retrieve report icon by slug
     */
    public static function get_report_icon($slug) {
        $slug = self::normalize_slug($slug);
        $reports = self::get_available_reports();
        return $reports[$slug]['icon'] ?? 'fa-solid fa-scroll';
    }

    /**
     * Helper to retrieve report page URL by slug
     */
    public static function get_report_page_url($slug) {
        $slug = self::normalize_slug($slug);
        $reports = self::get_available_reports();
        $page_slug = $reports[$slug]['page_slug'] ?? $slug;
        return home_url('/reports/' . $page_slug . '/');
    }
}
