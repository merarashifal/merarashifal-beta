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
        if ($slug === 'numerology_report' || $slug === 'numerology-report') {
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

        $single_sale = floatval($base_meta['def_price']);
        $single_reg  = floatval($base_meta['def_reg']);

        return [
            'report_slug'    => $slug,
            'bundle_enabled' => true,
            'tier_single'    => [
                'enabled'       => true,
                'title'         => 'Single Report',
                'subtitle'      => 'Complete ' . esc_html($base_meta['name']),
                'price_regular' => $single_reg,
                'price_sale'    => $single_sale,
                'badge'         => 'Standard',
                'chat_credits'  => 0,
            ],
            'tier_duo'       => [
                'enabled'        => true,
                'title'          => $pair['duo_title'],
                'subtitle'       => '2 In-Depth Dossiers + 15 AI Follow-up Questions',
                'paired_reports' => [$pair['duo_paired']],
                'price_regular'  => 1198,
                'price_sale'     => 449,
                'badge'          => 'POPULAR',
                'chat_credits'   => 15,
                'highlights'     => '2 Publication-Grade PDF Reports &bull; 15 Niyati AI Chat Credits Included',
            ],
            'tier_trio'      => [
                'enabled'        => true,
                'title'          => $pair['trio_title'],
                'subtitle'       => '3 Complete Master Reports + 30 AI Chat Credits',
                'is_best_buy'    => true,
                'paired_reports' => $pair['trio_paired'],
                'price_regular'  => 2097,
                'price_sale'     => 599,
                'badge'          => 'BEST BUY - SAVE 71%',
                'chat_credits'   => 30,
                'highlights'     => '3 Complete Sacred Reports (65+ Pages Total) &bull; 30 Niyati AI Interactive Consultations &bull; Highest Value',
            ]
        ];
    }

    /**
     * Get all bundle configs from database, merging with defaults
     */
    public static function get_all_bundles() {
        $saved = get_option(self::OPTION_KEY, []);
        $all_reports = self::get_available_reports();
        $configs = [];

        foreach ($all_reports as $slug => $meta) {
            $def = self::get_default_config_for($slug);
            if (!empty($saved[$slug]) && is_array($saved[$slug])) {
                $configs[$slug] = array_replace_recursive($def, $saved[$slug]);
            } else {
                $configs[$slug] = $def;
            }
        }

        return $configs;
    }

    /**
     * Get specific bundle configuration for a report
     */
    public static function get_bundle_for_report($report_slug) {
        $slug = self::normalize_slug($report_slug);
        $all = self::get_all_bundles();
        return $all[$slug] ?? self::get_default_config_for($slug);
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
