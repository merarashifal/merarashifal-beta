<?php
/**
 * MeraRashifal Customizable Bucket Value Discount & In-Cart Upsell Engine
 *
 * Implements dynamic, fully customizable tiered cart value / quantity discounts
 * across all product categories:
 * - Single Reports (e.g. Prompt to add 2nd report or upgrade to Bundle pack for X% off)
 * - Chat Credits (e.g. Prompt to add 100 bonus credits for flat / % off)
 * - Combo Deals (e.g. Prompt to add remedial guide or upsell item for flat / % off)
 * - Subscription Packs (e.g. Prompt to upgrade to annual VIP pass for X% off)
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Bucket_Discount {

    const OPTION_KEY = 'mr_bucket_discount_rules';
    const TWO_REPORT_DISCOUNT_PERCENT = 25.0;

    /**
     * Product Categories supported for bucket / upsell rules
     */
    public static function get_supported_categories() {
        return [
            'reports'       => __('Astrology Reports (Single Reports)', 'merarashifal'),
            'chat_credits'  => __('AI Chat Consultation Credits', 'merarashifal'),
            'combo_deals'   => __('Combo Deals / Multi-Report Bundles', 'merarashifal'),
            'subscriptions' => __('Subscription Packs', 'merarashifal'),
            'all'           => __('All Products (Universal Rule)', 'merarashifal'),
        ];
    }

    /**
     * Default starter rules out-of-the-box
     */
    public static function get_default_rules() {
        return [
            'rule_reports_bundle' => [
                'id'             => 'rule_reports_bundle',
                'title'          => __('Report to Bundle / Multi-Report Upgrade', 'merarashifal'),
                'category'       => 'reports',
                'min_cart_value' => 0.0,
                'headline'       => __('🎁 Special In-Cart Upgrade: Add Companion Report or Bundle!', 'merarashifal'),
                'subtext'        => __('Add another companion report or upgrade to complete bundle to unlock an instant 25% discount on your entire bill!', 'merarashifal'),
                'upsell_type'    => 'companion_selector', // 'companion_selector' | 'fixed_item' | 'bundle'
                'upsell_name'    => __('5-Report Complete Master Bundle Upgrade', 'merarashifal'),
                'upsell_slug'    => 'bundle_5_reports',
                'upsell_price'   => 199.0,
                'discount_type'  => 'percent', // 'percent' | 'flat'
                'discount_value' => 25.0,
                'status'         => 'active',
                'priority'       => 10,
            ],
            'rule_chat_booster' => [
                'id'             => 'rule_chat_booster',
                'title'          => __('Chat Credits Power Booster (+100 Questions)', 'merarashifal'),
                'category'       => 'chat_credits',
                'min_cart_value' => 0.0,
                'headline'       => __('⚡ Double Your AI Astrologer Queries & Save 20%!', 'merarashifal'),
                'subtext'        => __('Add 100 extra lifetime consultation credits to your order and get flat 20% off your entire recharge billing.', 'merarashifal'),
                'upsell_type'    => 'fixed_item',
                'upsell_name'    => __('100 Bonus AI Chat Consultation Credits', 'merarashifal'),
                'upsell_slug'    => 'chat_credits_100',
                'upsell_price'   => 99.0,
                'discount_type'  => 'percent',
                'discount_value' => 20.0,
                'status'         => 'active',
                'priority'       => 20,
            ],
            'rule_combo_booster' => [
                'id'             => 'rule_combo_booster',
                'title'          => __('Combo Deal Vedic Remedial Booster', 'merarashifal'),
                'category'       => 'combo_deals',
                'min_cart_value' => 99.0,
                'headline'       => __('🌟 Complete Your Combo Deal & Get Flat ₹100 OFF!', 'merarashifal'),
                'subtext'        => __('Add the personalized Vedic Remedial Guide to your combo and instantly deduct flat ₹100 from your bill.', 'merarashifal'),
                'upsell_type'    => 'fixed_item',
                'upsell_name'    => __('Personalized Kundli Dosh Remedial Guide + 50 Credits', 'merarashifal'),
                'upsell_slug'    => 'combo_remedial_booster',
                'upsell_price'   => 149.0,
                'discount_type'  => 'flat',
                'discount_value' => 100.0,
                'status'         => 'active',
                'priority'       => 30,
            ],
            'rule_sub_vip' => [
                'id'             => 'rule_sub_vip',
                'title'          => __('Subscription to Annual VIP Pass Upgrade', 'merarashifal'),
                'category'       => 'subscriptions',
                'min_cart_value' => 0.0,
                'headline'       => __('👑 Upgrade to Annual VIP Pass & Get 30% OFF Total Billing!', 'merarashifal'),
                'subtext'        => __('Enjoy unlimited daily forecasts, monthly reports, and priority AI chat access all year round.', 'merarashifal'),
                'upsell_type'    => 'fixed_item',
                'upsell_name'    => __('Upgrade to Annual VIP Astro Pass (All Access)', 'merarashifal'),
                'upsell_slug'    => 'vip_annual_upgrade',
                'upsell_price'   => 499.0,
                'discount_type'  => 'percent',
                'discount_value' => 30.0,
                'status'         => 'active',
                'priority'       => 40,
            ],
        ];
    }

    /**
     * Get all configured bucket discount rules
     *
     * @return array
     */
    public static function get_rules() {
        $saved = get_option(self::OPTION_KEY, null);
        if (!is_array($saved) || empty($saved)) {
            $default = self::get_default_rules();
            update_option(self::OPTION_KEY, $default);
            return $default;
        }
        return $saved;
    }

    /**
     * Get a specific rule by ID
     *
     * @param string $rule_id
     * @return array|null
     */
    public static function get_rule($rule_id) {
        $rules = self::get_rules();
        return $rules[$rule_id] ?? null;
    }

    /**
     * Save or update a bucket discount rule
     *
     * @param array $data
     * @return string Rule ID
     */
    public static function save_rule($data) {
        $rules = self::get_rules();

        $rule_id = !empty($data['id']) ? sanitize_key($data['id']) : ('rule_' . time() . '_' . wp_rand(100, 999));
        $category = sanitize_key($data['category'] ?? 'reports');
        if (!array_key_exists($category, self::get_supported_categories())) {
            $category = 'reports';
        }

        $rules[$rule_id] = [
            'id'             => $rule_id,
            'title'          => sanitize_text_field($data['title'] ?? 'Custom Upsell Rule'),
            'category'       => $category,
            'min_cart_value' => max(0.0, floatval($data['min_cart_value'] ?? 0.0)),
            'headline'       => sanitize_text_field($data['headline'] ?? 'Special In-Cart Offer!'),
            'subtext'        => sanitize_textarea_field($data['subtext'] ?? ''),
            'upsell_type'    => in_array($data['upsell_type'] ?? '', ['companion_selector', 'fixed_item', 'bundle'], true) ? $data['upsell_type'] : 'fixed_item',
            'upsell_name'    => sanitize_text_field($data['upsell_name'] ?? 'Upsell Item'),
            'upsell_slug'    => sanitize_key($data['upsell_slug'] ?? 'upsell_item'),
            'upsell_price'   => max(0.0, floatval($data['upsell_price'] ?? 99.0)),
            'discount_type'  => ($data['discount_type'] ?? 'percent') === 'flat' ? 'flat' : 'percent',
            'discount_value' => max(0.0, floatval($data['discount_value'] ?? 25.0)),
            'status'         => ($data['status'] ?? 'active') === 'paused' ? 'paused' : 'active',
            'priority'       => intval($data['priority'] ?? 10),
        ];

        update_option(self::OPTION_KEY, $rules);
        return $rule_id;
    }

    /**
     * Delete a bucket rule
     *
     * @param string $rule_id
     * @return bool
     */
    public static function delete_rule($rule_id) {
        $rules = self::get_rules();
        if (isset($rules[$rule_id])) {
            unset($rules[$rule_id]);
            update_option(self::OPTION_KEY, $rules);
            return true;
        }
        return false;
    }

    /**
     * Toggle status between active and paused
     *
     * @param string $rule_id
     * @return string New status
     */
    public static function toggle_rule_status($rule_id) {
        $rules = self::get_rules();
        if (isset($rules[$rule_id])) {
            $new_status = ($rules[$rule_id]['status'] === 'active') ? 'paused' : 'active';
            $rules[$rule_id]['status'] = $new_status;
            update_option(self::OPTION_KEY, $rules);
            return $new_status;
        }
        return 'not_found';
    }

    /**
     * Find best matching active rule for a cart
     *
     * @param string $category Product category ('reports', 'chat_credits', 'combo_deals', 'subscriptions')
     * @param float $cart_value Current pre-tax cart base amount
     * @param string $current_slug Slug of product currently in cart
     * @return array|null Matching rule or null
     */
    public static function get_matching_rule($category, $cart_value = 0.0, $current_slug = '') {
        $rules = self::get_rules();
        $cart_value = floatval($cart_value);

        // Sort by priority ascending
        uasort($rules, function($a, $b) {
            return ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10);
        });

        foreach ($rules as $rule) {
            if (($rule['status'] ?? 'active') !== 'active') {
                continue;
            }

            // Category matching
            if ($rule['category'] !== 'all' && $rule['category'] !== $category) {
                continue;
            }

            // Cart value threshold
            if (!empty($rule['min_cart_value']) && $cart_value < floatval($rule['min_cart_value'])) {
                continue;
            }

            return $rule;
        }

        return null;
    }

    /**
     * Calculate discount for a rule based on total combined billing before taxes
     *
     * @param array|string $rule Rule array or Rule ID
     * @param float $combined_subtotal Total pre-tax amount of primary + upsell product
     * @return array [
     *      'eligible'        => bool,
     *      'discount_amount' => float,
     *      'net_pretax'      => float,
     *      'rule_label'      => string,
     *      'discount_type'   => string,
     *      'discount_value'  => float
     * ]
     */
    public static function calculate_discount($rule, $combined_subtotal) {
        if (is_string($rule)) {
            $rule = self::get_rule($rule);
        }

        $combined_subtotal = floatval($combined_subtotal);

        if (!$rule || $combined_subtotal <= 0) {
            return [
                'eligible'        => false,
                'discount_amount' => 0.0,
                'net_pretax'      => $combined_subtotal,
                'rule_label'      => '',
                'discount_type'   => 'percent',
                'discount_value'  => 0.0,
            ];
        }

        $type = $rule['discount_type'] ?? 'percent';
        $val  = floatval($rule['discount_value'] ?? 25.0);

        if ($type === 'flat') {
            $discount_amount = min($combined_subtotal, round($val, 2));
            $label = sprintf(__('Bucket Discount (-₹%s OFF)', 'merarashifal'), number_format($discount_amount, 2));
        } else {
            $discount_amount = round(($combined_subtotal * $val) / 100.0, 2);
            $label = sprintf(__('Bucket Privilege (%s%% OFF)', 'merarashifal'), $val);
        }

        $net_pretax = max(0.00, round($combined_subtotal - $discount_amount, 2));

        return [
            'eligible'        => true,
            'discount_amount' => $discount_amount,
            'net_pretax'      => $net_pretax,
            'rule_label'      => $label,
            'discount_type'   => $type,
            'discount_value'  => $val,
            'rule_id'         => $rule['id'] ?? '',
        ];
    }

    // =========================================================================
    // BACKWARD COMPATIBILITY HELPERS
    // =========================================================================

    /**
     * Check if a cart / checkout qualifies for the 2-report upsell prompt
     */
    public static function is_upsell_eligible($tier, $included_reports = []) {
        if ($tier === 'single' && count($included_reports) <= 1) {
            return true;
        }
        return false;
    }

    /**
     * Calculate Bucket Tier Discount on Pre-Tax items (Legacy helper)
     */
    public static function calculate_bucket_discount($reports_list, $base_subtotal) {
        $count = is_array($reports_list) ? count(array_unique(array_filter($reports_list))) : 1;
        $base_subtotal = floatval($base_subtotal);

        // Fetch matching report rule if available
        $rule = self::get_matching_rule('reports', $base_subtotal);

        if ($count >= 2 && $base_subtotal > 0) {
            if ($rule) {
                return self::calculate_discount($rule, $base_subtotal);
            }

            $percent = self::TWO_REPORT_DISCOUNT_PERCENT;
            $discount_amount = round(($base_subtotal * $percent) / 100.0, 2);
            $net_pretax = max(0.00, round($base_subtotal - $discount_amount, 2));

            return [
                'eligible'        => true,
                'percent'         => $percent,
                'discount_amount' => $discount_amount,
                'net_pretax'      => $net_pretax,
                'rule_label'      => sprintf(__('Multi-Report Bucket Privilege (%d%% OFF)', 'merarashifal'), $percent),
            ];
        }

        return [
            'eligible'        => false,
            'percent'         => 0.0,
            'discount_amount' => 0.00,
            'net_pretax'      => $base_subtotal,
            'rule_label'      => '',
        ];
    }

    /**
     * Get candidate companion reports for in-cart 1-click upsell
     */
    public static function get_companion_upsell_candidates($current_slug = '') {
        $all_reports = [
            'kundli_predictions' => [
                'slug'        => 'kundli_predictions',
                'name'        => 'Vedic Kundli & Life Predictions Report',
                'hindi_name'  => 'सम्पूर्ण जन्म कुंडली एवं जीवन भविष्यफल',
                'icon'        => 'fa-solid fa-chart-pie',
                'price'       => 99,
                'pages'       => '20 Pages',
                'highlight'   => 'Life trajectory, yogas & planet strengths',
            ],
            'mahadasha_timing' => [
                'slug'        => 'mahadasha_timing',
                'name'        => 'Vedic Mahadasha & Life Timing Report',
                'hindi_name'  => 'महादशा एवं अंतर्दशा समय चक्र',
                'icon'        => 'fa-solid fa-clock',
                'price'       => 99,
                'pages'       => '22 Pages',
                'highlight'   => 'Vimshottari cycles & timing of major events',
            ],
            'dosh_remedial' => [
                'slug'        => 'dosh_remedial',
                'name'        => 'Kundli Dosh & Vedic Remedial Report',
                'hindi_name'  => 'कुंडली दोष एवं वैदिक निवारण',
                'icon'        => 'fa-solid fa-shield-halved',
                'price'       => 99,
                'pages'       => '16 Pages',
                'highlight'   => 'Mangal, Kaal Sarp, Sade Sati & remedial mantras',
            ],
            'kundli_matching' => [
                'slug'        => 'kundli_matching',
                'name'        => 'Kundli Matching & Relationship Report',
                'hindi_name'  => 'कुंडली मिलान एवं वैवाहिक भविष्य',
                'icon'        => 'fa-solid fa-heart',
                'price'       => 99,
                'pages'       => '18 Pages',
                'highlight'   => '36 Gun Milan, Ashtakoot & compatibility',
            ],
            'yearly_horoscope_long' => [
                'slug'        => 'yearly_horoscope_long',
                'name'        => 'Yearly Varshaphal Detailed Report',
                'hindi_name'  => 'वार्षिक वर्षफल विस्तृत रिपोर्ट (2026)',
                'icon'        => 'fa-solid fa-calendar-days',
                'price'       => 99,
                'pages'       => '26 Pages',
                'highlight'   => 'Month-by-month career, wealth & relationship roadmap',
            ],
            'palmistry_report' => [
                'slug'        => 'palmistry_report',
                'name'        => 'Master Hast Rekha & Palmistry Report',
                'hindi_name'  => 'हस्तरेखा एवं सामुद्रिक शास्त्र विश्लेषण',
                'icon'        => 'fa-solid fa-hand',
                'price'       => 99,
                'pages'       => '20 Pages',
                'highlight'   => 'AI vision scan of mounts, lines & 5-phase age graph',
            ],
            'numero_report' => [
                'slug'        => 'numero_report',
                'name'        => 'Master Numerology & Lo Shu Grid',
                'hindi_name'  => 'अंकशास्त्र एवं लो-शू ग्रिड रिपोर्ट',
                'icon'        => 'fa-solid fa-arrow-up-9-1',
                'price'       => 99,
                'pages'       => '22 Pages',
                'highlight'   => 'Core destiny numbers, 8-1-6 planes & lucky dates',
            ],
            'numerology_report' => [
                'slug'        => 'numerology_report',
                'name'        => 'Master Numerology & Lo Shu Grid',
                'hindi_name'  => 'अंकशास्त्र एवं लो-शू ग्रिड रिपोर्ट',
                'icon'        => 'fa-solid fa-arrow-up-9-1',
                'price'       => 99,
                'pages'       => '22 Pages',
                'highlight'   => 'Core destiny numbers, 8-1-6 planes & lucky dates',
            ],
        ];

        // Filter out the currently selected report
        if (!empty($current_slug)) {
            if ($current_slug === 'numero_report' || $current_slug === 'numerology' || $current_slug === 'numerology_report') {
                unset($all_reports['numero_report'], $all_reports['numerology_report']);
            } elseif (isset($all_reports[$current_slug])) {
                unset($all_reports[$current_slug]);
            }
        }

        return $all_reports;
    }
}
