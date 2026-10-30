<?php
/**
 * Master Definitions: Vedic & Western Packs, Reports & A la Carte Pricing
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Packs {

    /**
     * Master list of all reports (Vedic, Western, Tarot, Numerology)
     */
    public static function get_reports() {
        return [
            // --- Vedic Reports ---
            'yearly_horoscope_long' => [
                'name' => 'Yearly Horoscope Detailed Long Report',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-calendar-days',
                'dashicon' => 'dashicons-calendar-alt',
                'price_usd' => 4.99,
                'price_cad' => 6.99,
                'pages' => '26 Pages',
                'description' => 'Comprehensive 26-page 12-month Vedic Varshaphal forecast covering career, wealth, relationships, and finances.'
            ],
            'yearly_horoscope_vedic' => [
                'name' => 'Yearly Horoscope Short Report (2026)',
                'system' => 'vedic',
                'category' => 'Free Lead Magnet',
                'icon' => 'fa-solid fa-calendar',
                'dashicon' => 'dashicons-calendar-alt',
                'price_usd' => 0.00,
                'price_cad' => 0.00,
                'pages' => '5 Pages',
                'description' => 'Essential 5-page annual overview with key planetary transits and auspicious timing windows.'
            ],
            'monthly_horoscope_vedic' => [
                'name' => 'Monthly Horoscope Report (Vedic)',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-calendar',
                'dashicon' => 'dashicons-calendar',
                'price_usd' => 2.50,
                'price_cad' => 3.50,
                'pages' => '10-15 pages',
                'description' => 'Detailed month-by-month planetary transits, auspicious days, and guidance.'
            ],
            'kundli_predictions' => [
                'name' => 'Vedic Kundli & Life Predictions Report',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-chart-pie',
                'dashicon' => 'dashicons-chart-pie',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '15-20 pages',
                'description' => 'Full birth chart analysis, planetary strengths, yogas, and lifetime trajectory.'
            ],
            'mahadasha_timing' => [
                'name' => 'Vedic Mahadasha & Antardasha Life Timing Report',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-clock',
                'dashicon' => 'dashicons-clock',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '20-25 pages',
                'description' => 'Deep dive into your active Vimshottari Dasha cycles and event timing predictions.'
            ],
            'dosh_remedial' => [
                'name' => 'Vedic Kundli Dosh & Remedial Report',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-shield-halved',
                'dashicon' => 'dashicons-shield',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '12-16 pages',
                'description' => 'Identification of Manglik, Kaal Sarp, Pitra or Sade Sati doshas with personalized remedies.'
            ],
            'kundli_matching' => [
                'name' => 'Kundli Matching (Gun Milan) & Relationship Predictions Report',
                'system' => 'vedic',
                'category' => 'Vedic Reading',
                'icon' => 'fa-solid fa-ring',
                'dashicon' => 'dashicons-heart',
                'price_usd' => 5.99,
                'price_cad' => 8.39,
                'pages' => '15-20 pages',
                'description' => 'Ashtakoot matching with 36 Gunas, Manglik dosha compatibility, and longevity analysis.'
            ],

            // --- Western Reports ---
            'yearly_horoscope_western' => [
                'name' => 'Yearly Horoscope Report (Western)',
                'system' => 'western',
                'category' => 'Western Reading',
                'icon' => 'fa-solid fa-sun',
                'dashicon' => 'dashicons-star-filled',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '20-25 pages',
                'description' => 'Solar return chart, major outer planet transits, progressions, and year ahead forecast.'
            ],
            'monthly_horoscope_western' => [
                'name' => 'Monthly Horoscope Report (Western)',
                'system' => 'western',
                'category' => 'Western Reading',
                'icon' => 'fa-solid fa-moon',
                'dashicon' => 'dashicons-calendar',
                'price_usd' => 2.50,
                'price_cad' => 3.50,
                'pages' => '10-14 pages',
                'description' => 'New and full moon cycles, retrograde phases, and house transits for the upcoming month.'
            ],
            'natal_chat_reading' => [
                'name' => 'Natal Chat Reading Report',
                'system' => 'western',
                'category' => 'Western Chat Readings',
                'icon' => 'fa-solid fa-comments',
                'dashicon' => 'dashicons-format-chat',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '15-20 pages',
                'description' => 'In-depth psychological natal chart reading with dialogue-style interpretations of placements.'
            ],
            'synastry_chart_reading' => [
                'name' => 'Synastry Chart Reading Report',
                'system' => 'western',
                'category' => 'Western Chat Readings',
                'icon' => 'fa-solid fa-handshake-angle',
                'dashicon' => 'dashicons-groups',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '15-20 pages',
                'description' => 'Two charts overlaid: planetary aspects, chemistry, communication styles, and friction points.'
            ],
            'transit_report' => [
                'name' => 'Transit Report',
                'system' => 'western',
                'category' => 'Western Reading',
                'icon' => 'fa-solid fa-route',
                'dashicon' => 'dashicons-location-alt',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '14-18 pages',
                'description' => 'Active celestial movements and how planetary transits trigger your natal houses.'
            ],

            // --- Tarot & Numerology (Universal) ---
            'tarot_reading' => [
                'name' => 'Tarot Reading of the Day Report',
                'system' => 'universal',
                'category' => 'Tarot',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'dashicon' => 'dashicons-visibility',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '8-12 pages',
                'description' => 'Intuitive 3-card spread analyzing mind, body, spirit and immediate practical guidance.'
            ],
            'numerology_report' => [
                'name' => 'Master Numerology & Vedic Integration Report',
                'system' => 'universal',
                'category' => 'General & Numerology',
                'icon' => 'fa-solid fa-star',
                'dashicon' => 'dashicons-star-filled',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '22 Pages',
                'description' => '22-page Sankhya Shastra audit: Lo Shu grid, core numbers, 8-1-6 arrow, and 90-day roadmap.'
            ],
            'numerology' => [
                'name' => 'Numerology Report',
                'system' => 'universal',
                'category' => 'General Report',
                'icon' => 'fa-solid fa-calculator',
                'dashicon' => 'dashicons-calculator',
                'price_usd' => 3.99,
                'price_cad' => 5.59,
                'pages' => '12-16 pages',
                'description' => 'Analysis of Life Path, Destiny, Soul Urge numbers, name vibrations, and lucky dates/colors.'
            ],
            'palmistry_report' => [
                'name' => 'Master Hast Rekha & Palmistry Analysis Report',
                'system' => 'universal',
                'category' => 'Palmistry & Samudrik',
                'icon' => 'fa-solid fa-hand',
                'dashicon' => 'dashicons-admin-customizer',
                'price_usd' => 4.99,
                'price_cad' => 6.99,
                'pages' => '20 Pages',
                'description' => 'Comprehensive 20-page Hast Rekha & Samudrik Shastra analysis: Major lines, Mounts, Career, Wealth & 5-phase age timeline.'
            ],
        ];
    }

    /**
     * Master list of subscription packs (Vedic and Western)
     */
    public static function get_packs() {
        return [
            // ==================== VEDIC ASTROLOGY PACKS ====================
            'monthly_vedic' => [
                'system' => 'vedic',
                'cycle' => 'monthly',
                'name' => 'Monthly Pack (Vedic Astrology)',
                'badge' => 'Flexible',
                'price_usd' => 6.99,
                'price_cad' => 8.99,
                'duration_months' => 1,
                'duration_label' => '1 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_vedic' => 1,
                    'monthly_horoscope_vedic' => 2,
                    'kundli_predictions' => 2,
                    'mahadasha_timing' => 2,
                    'dosh_remedial' => 2,
                    'tarot_reading' => 2,
                    'numerology' => 1,
                ],
            ],
            'quarterly_vedic' => [
                'system' => 'vedic',
                'cycle' => 'quarterly',
                'name' => 'Quarterly Pack (Vedic Astrology)',
                'badge' => 'Save 10%',
                'price_usd' => 14.99,
                'price_cad' => 21.99,
                'duration_months' => 3,
                'duration_label' => '3 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_vedic' => 2,
                    'monthly_horoscope_vedic' => 4,
                    'kundli_predictions' => 4,
                    'mahadasha_timing' => 4,
                    'dosh_remedial' => 4,
                    'tarot_reading' => 4,
                    'numerology' => 2,
                ],
            ],
            'half_yearly_vedic' => [
                'system' => 'vedic',
                'cycle' => 'halfyearly',
                'name' => 'Half Yearly Pack (Vedic Astrology)',
                'badge' => 'Save 15%',
                'price_usd' => 24.99,
                'price_cad' => 37.99,
                'duration_months' => 6,
                'duration_label' => '6 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_vedic' => 4,
                    'monthly_horoscope_vedic' => 6,
                    'kundli_predictions' => 6,
                    'mahadasha_timing' => 6,
                    'dosh_remedial' => 6,
                    'tarot_reading' => 6,
                    'numerology' => 4,
                ],
            ],
            'yearly_vedic' => [
                'system' => 'vedic',
                'cycle' => 'yearly',
                'name' => 'Yearly Pack (Vedic Astrology)',
                'badge' => 'Best Value (Save 20%)',
                'price_usd' => 40.99,
                'price_cad' => 62.99,
                'duration_months' => 12,
                'duration_label' => '12 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_vedic' => 6,
                    'monthly_horoscope_vedic' => 12,
                    'kundli_predictions' => 12,
                    'mahadasha_timing' => 12,
                    'dosh_remedial' => 12,
                    'tarot_reading' => 12,
                    'numerology' => 8,
                ],
            ],

            // ==================== WESTERN ASTROLOGY PACKS ====================
            'monthly_western' => [
                'system' => 'western',
                'cycle' => 'monthly',
                'name' => 'Monthly Pack (Western Astrology)',
                'badge' => 'Flexible',
                'price_usd' => 6.99,
                'price_cad' => 8.99,
                'duration_months' => 1,
                'duration_label' => '1 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_western' => 1,
                    'monthly_horoscope_western' => 2,
                    'natal_chat_reading' => 2,
                    'synastry_chart_reading' => 2,
                    'transit_report' => 2,
                    'tarot_reading' => 2,
                    'numerology' => 1,
                ],
            ],
            'quarterly_western' => [
                'system' => 'western',
                'cycle' => 'quarterly',
                'name' => 'Quarterly Pack (Western Astrology)',
                'badge' => 'Save 10%',
                'price_usd' => 14.99,
                'price_cad' => 21.99,
                'duration_months' => 3,
                'duration_label' => '3 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_western' => 2,
                    'monthly_horoscope_western' => 4,
                    'natal_chat_reading' => 4,
                    'synastry_chart_reading' => 4,
                    'transit_report' => 4,
                    'tarot_reading' => 4,
                    'numerology' => 2,
                ],
            ],
            'half_yearly_western' => [
                'system' => 'western',
                'cycle' => 'halfyearly',
                'name' => 'Half Yearly Pack (Western Astrology)',
                'badge' => 'Save 15%',
                'price_usd' => 24.99,
                'price_cad' => 37.99,
                'duration_months' => 6,
                'duration_label' => '6 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_western' => 4,
                    'monthly_horoscope_western' => 6,
                    'natal_chat_reading' => 6,
                    'synastry_chart_reading' => 6,
                    'transit_report' => 6,
                    'tarot_reading' => 6,
                    'numerology' => 4,
                ],
            ],
            'yearly_western' => [
                'system' => 'western',
                'cycle' => 'yearly',
                'name' => 'Yearly Pack (Western Astrology)',
                'badge' => 'Best Value (Save 20%)',
                'price_usd' => 40.99,
                'price_cad' => 62.99,
                'duration_months' => 12,
                'duration_label' => '12 Calendar Month',
                'quotas' => [
                    'yearly_horoscope_western' => 6,
                    'monthly_horoscope_western' => 12,
                    'natal_chat_reading' => 12,
                    'synastry_chart_reading' => 12,
                    'transit_report' => 12,
                    'tarot_reading' => 12,
                    'numerology' => 8,
                ],
            ],
        ];
    }

    public static function get_pack($pack_key) {
        $packs = self::get_packs();
        // Support legacy keys if needed
        if ($pack_key === 'monthly') $pack_key = 'monthly_vedic';
        if ($pack_key === 'quarterly') $pack_key = 'quarterly_vedic';
        if ($pack_key === 'half_yearly') $pack_key = 'half_yearly_vedic';
        if ($pack_key === 'yearly') $pack_key = 'yearly_vedic';
        return $packs[$pack_key] ?? null;
    }

    public static function get_report($report_key) {
        $reports = self::get_reports();
        if ($report_key === 'yearly_horoscope') $report_key = 'yearly_horoscope_vedic';
        if ($report_key === 'monthly_horoscope') $report_key = 'monthly_horoscope_vedic';
        return $reports[$report_key] ?? null;
    }

    public static function get_packs_by_system($system = 'vedic') {
        $all = self::get_packs();
        $filtered = [];
        foreach ($all as $k => $p) {
            if ($p['system'] === $system) {
                $filtered[$k] = $p;
            }
        }
        return $filtered;
    }
}
