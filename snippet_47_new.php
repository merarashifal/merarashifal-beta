<?php
// Snippet 47: Muhurat Code (MeraRashifal Modern Vedic Engine)

if (!function_exists('mr_final_muhurat_viewer_theme')) {
    add_shortcode('muhurat_viewer', 'mr_final_muhurat_viewer_theme');
    add_shortcode('griha_pravesh_muhurat', 'mr_griha_pravesh_shortcode');
    add_shortcode('vivah_muhurat', 'mr_vivah_muhurat_shortcode');
    add_shortcode('vehicle_muhurat', 'mr_vehicle_muhurat_shortcode');
    add_shortcode('property_muhurat', 'mr_property_muhurat_shortcode');

    function mr_griha_pravesh_shortcode($atts = []) {
        $atts = is_array($atts) ? $atts : [];
        $atts['occasion'] = 'Griha Pravesh';
        return mr_final_muhurat_viewer_theme($atts);
    }

    function mr_vivah_muhurat_shortcode($atts = []) {
        $atts = is_array($atts) ? $atts : [];
        $atts['occasion'] = 'Vivah Muhurat';
        return mr_final_muhurat_viewer_theme($atts);
    }

    function mr_vehicle_muhurat_shortcode($atts = []) {
        $atts = is_array($atts) ? $atts : [];
        $atts['occasion'] = 'Vehicle Purchase';
        return mr_final_muhurat_viewer_theme($atts);
    }

    function mr_property_muhurat_shortcode($atts = []) {
        $atts = is_array($atts) ? $atts : [];
        $atts['occasion'] = 'Property Purchase';
        return mr_final_muhurat_viewer_theme($atts);
    }

    function mr_final_muhurat_viewer_theme($atts = []) {
        global $wpdb;

        $atts = shortcode_atts([
            'occasion'     => '',
            'ym'           => '',
            'year'         => '',
            'lang'         => 'en',
            'show_icons'   => 'auto',
            'show_cta'     => 'yes',
            'months_count' => '1',
        ], $atts, 'muhurat_viewer');

        $table = $wpdb->prefix . 'muhurat';

        if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table)))) {
            return "<p style='color:red; text-align:center;'>Error: table {$table} not found.</p>";
        }

        // Occasion categories metadata with modern icons, emojis, and descriptions
        $categories = [
            'Vivah Muhurat' => [
                'slug'            => 'vivah-muhurat',
                'emoji'           => '💍',
                'label_en'        => 'Vivah Muhurat',
                'sub_en'          => 'Auspicious Wedding Dates',
                'label_hi'        => 'विवाह मुहूर्त',
                'sub_hi'          => 'शुभ विवाह लग्न व नक्षत्र',
                'icon'            => home_url('/wp-content/uploads/2025/11/Vivah-Mahurat.webp'),
                'fallback_icon'   => 'https://merarashifal.com/wp-content/uploads/2025/11/Vivah-Mahurat.webp',
                'report_title_en' => 'Personalized Kundali & Gun Matching Report',
                'report_title_hi' => 'वर-वधू कुंडली मिलान एवं 36 गुण मिलान रिपोर्ट',
                'report_desc_en'  => 'Ensure lifelong marital bliss with deep Ashtakoot Guna Milan and Mangal Dosha analysis.',
                'report_desc_hi'  => 'विवाह से पूर्व 36 गुण मिलान, मांगलिक दोष एवं भकूट दोष की संपूर्ण वैदिक जांच कराएं।',
                'report_link'     => home_url('/reports/kundali-and-gun-matching-report/'),
                'report_btn_en'   => 'Get Matching Report &rarr;',
                'report_btn_hi'   => 'विवाह कुंडली मिलान रिपोर्ट &rarr;',
            ],
            'Griha Pravesh' => [
                'slug'            => 'griha-pravesh',
                'emoji'           => '🏡',
                'label_en'        => 'Griha Pravesh',
                'sub_en'          => 'Housewarming Muhurat',
                'label_hi'        => 'गृह प्रवेश मुहूर्त',
                'sub_hi'          => 'नए घर में प्रवेश का शुभ समय',
                'icon'            => home_url('/wp-content/uploads/2025/11/Griha-Pravesh.webp'),
                'fallback_icon'   => 'https://merarashifal.com/wp-content/uploads/2025/11/Griha-Pravesh.webp',
                'report_title_en' => 'Personalized Janam Kundali Master Report',
                'report_title_hi' => 'व्यक्तिगत जन्म कुंडली महा-विश्लेषण रिपोर्ट',
                'report_desc_en'  => 'Align your home entry with favorable planetary transits according to the head of household.',
                'report_desc_hi'  => 'गृहस्वामी की जन्म कुंडली अनुसार सर्वाधिक शुभ लग्न एवं नक्षत्र चुनकर सुख-समृद्धि सुनिश्चित करें।',
                'report_link'     => home_url('/reports/janam-kundali-master-report/'),
                'report_btn_en'   => 'Get Master Kundali &rarr;',
                'report_btn_hi'   => 'मास्टर जन्म कुंडली प्राप्त करें &rarr;',
            ],
            'Vehicle Purchase' => [
                'slug'            => 'vehicle-purchase',
                'emoji'           => '🚗',
                'label_en'        => 'Vehicle Purchase',
                'sub_en'          => 'Car & Bike Muhurat',
                'label_hi'        => 'वाहन खरीद मुहूर्त',
                'sub_hi'          => 'कार व बाइक डिलीवरी शुभ समय',
                'icon'            => home_url('/wp-content/uploads/2025/11/Vehicle-Purchase.webp'),
                'fallback_icon'   => 'https://merarashifal.com/wp-content/uploads/2025/11/Vehicle-Purchase.webp',
                'report_title_en' => 'Numerology & Lucky Vehicle Numbers Report',
                'report_title_hi' => 'अंक ज्योतिष एवं वाहन नंबर अनुकूलता रिपोर्ट',
                'report_desc_en'  => 'Ensure accident-free travel by syncing your vehicle delivery date and number with your birth numbers.',
                'report_desc_hi'  => 'अपने मूलांक एवं भाग्यशाली अंक अनुसार वाहन नंबर व डिलीवरी का सबसे शुभ मुहूर्त चुनें।',
                'report_link'     => home_url('/reports/numerology-report/'),
                'report_btn_en'   => 'Get Numerology Report &rarr;',
                'report_btn_hi'   => 'अंक ज्योतिष रिपोर्ट प्राप्त करें &rarr;',
            ],
            'Property Purchase' => [
                'slug'            => 'property-purchase',
                'emoji'           => '🏢',
                'label_en'        => 'Property Purchase',
                'sub_en'          => 'Plot, Flat & Land Registry',
                'label_hi'        => 'संपत्ति खरीद मुहूर्त',
                'sub_hi'          => 'जमीन, मकान व फ्लैट रजिस्ट्री',
                'icon'            => home_url('/wp-content/uploads/2025/11/Property-Purchase.webp'),
                'fallback_icon'   => 'https://merarashifal.com/wp-content/uploads/2025/11/Property-Purchase.webp',
                'report_title_en' => 'Wealth & Property Astro-Consultation Report',
                'report_title_hi' => 'भूमि, भवन एवं संपत्ति ज्योतिष परामर्श रिपोर्ट',
                'report_desc_en'  => 'Verify 4th house yogas and Bhoomi Karaka Mars transits to prevent disputes and maximize asset growth.',
                'report_desc_hi'  => 'कुंडली के चतुर्थ भाव व भूमि कारक मंगल के प्रभाव का विश्लेषण कराकर संपत्ति निवेश को फलदायी बनाएं।',
                'report_link'     => home_url('/reports/janam-kundali-master-report/'),
                'report_btn_en'   => 'Get Property Report &rarr;',
                'report_btn_hi'   => 'संपत्ति रिपोर्ट प्राप्त करें &rarr;',
            ],
        ];

        // Slug / alias map
        $occasion_map = [
            'vivah'             => 'Vivah Muhurat',
            'vivah-muhurat'     => 'Vivah Muhurat',
            'vivah-mahurat'     => 'Vivah Muhurat',
            'vivah muhurat'     => 'Vivah Muhurat',
            'marriage'          => 'Vivah Muhurat',
            'wedding'           => 'Vivah Muhurat',
            'विवाह'             => 'Vivah Muhurat',
            'विवाह मुहूर्त'     => 'Vivah Muhurat',
            'griha-pravesh'     => 'Griha Pravesh',
            'griha pravesh'     => 'Griha Pravesh',
            'grihapravesh'      => 'Griha Pravesh',
            'housewarming'      => 'Griha Pravesh',
            'गृह प्रवेश'        => 'Griha Pravesh',
            'गृह प्रवेश मुहूर्त' => 'Griha Pravesh',
            'property-purchase' => 'Property Purchase',
            'property purchase' => 'Property Purchase',
            'property'          => 'Property Purchase',
            'संपत्ति खरीद'      => 'Property Purchase',
            'vehicle-purchase'  => 'Vehicle Purchase',
            'vehicle purchase'  => 'Vehicle Purchase',
            'vehicle'           => 'Vehicle Purchase',
            'वाहन खरीद'         => 'Vehicle Purchase',
        ];

        // 1. Language Resolution (English is primary and default)
        $lang = 'en';
        if (!empty($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
        } elseif (!empty($atts['lang'])) {
            $lang = sanitize_text_field($atts['lang']);
        }
        if (!in_array($lang, ['en', 'hi'])) {
            $lang = 'en';
        }

        // 2. Occasion Resolution
        $raw_occasion = '';
        if (isset($_GET['occasion']) && $_GET['occasion'] !== '') {
            $raw_occasion = sanitize_text_field($_GET['occasion']);
        } elseif (!empty($atts['occasion'])) {
            $raw_occasion = sanitize_text_field($atts['occasion']);
        }

        $selected_occasion = '';
        if ($raw_occasion !== '') {
            $clean_key = strtolower(trim(str_replace(['_', '+'], '-', $raw_occasion)));
            if (isset($occasion_map[$clean_key])) {
                $selected_occasion = $occasion_map[$clean_key];
            } else {
                foreach (array_keys($categories) as $cat) {
                    if (strcasecmp($cat, str_replace('-', ' ', $raw_occasion)) === 0) {
                        $selected_occasion = $cat;
                        break;
                    }
                }
            }
        }

        // 3. Whether to show icons selector
        $show_icons = $atts['show_icons'];
        if ($show_icons === 'auto') {
            $show_icons = empty($atts['occasion']) ? 'yes' : 'no';
        }

        // Current Month & Year
        $current_ym   = date('Y-m');
        $current_year = date('Y');

        // URL helper
        $build_url = function($override = []) {
            $params = ['occasion', 'lang', 'ym', 'year'];
            $base = [];
            foreach ($params as $p) {
                if (isset($_GET[$p]) && $_GET[$p] !== '') {
                    $base[$p] = sanitize_text_field($_GET[$p]);
                }
            }
            return esc_url(add_query_arg(array_merge($base, $override)));
        };

        // Dictionaries for Hindi Localization
        $hi_days = [
            'Sunday' => 'रविवार', 'Monday' => 'सोमवार', 'Tuesday' => 'मंगलवार',
            'Wednesday' => 'बुधवार', 'Thursday' => 'गुरुवार', 'Friday' => 'शुक्रवार', 'Saturday' => 'शनिवार'
        ];
        $hi_months = [
            'January' => 'जनवरी', 'February' => 'फरवरी', 'March' => 'मार्च', 'April' => 'अप्रैल',
            'May' => 'मई', 'June' => 'जून', 'July' => 'जुलाई', 'August' => 'अगस्त',
            'September' => 'सितंबर', 'October' => 'अक्टूबर', 'November' => 'नवंबर', 'December' => 'दिसंबर'
        ];
        $hi_signs_dict = [
            'Water, Earth, Air Signs'  => 'जल, पृथ्वी व वायु राशियाँ (कर्क, वृश्चिक, मीन, वृषभ, कन्या, मकर, मिथुन, तुला, कुंभ)',
            'Earth, Water Signs'       => 'पृथ्वी व जल राशियाँ (वृषभ, कन्या, मकर, कर्क, वृश्चिक, मीन)',
            'Air, Earth Signs'         => 'वायु व पृथ्वी राशियाँ (मिथुन, तुला, कुंभ, वृषभ, कन्या, मकर)',
            'Fire, Air Signs'          => 'अग्नि व वायु राशियाँ (मेष, सिंह, धनु, मिथुन, तुला, कुंभ)',
            'Fire, Earth Signs'        => 'अग्नि व पृथ्वी राशियाँ (मेष, सिंह, धनु, वृषभ, कन्या, मकर)',
            'Water Signs'              => 'जल तत्व राशियाँ (कर्क, वृश्चिक, मीन)',
            'Air Signs'                => 'वायु तत्व राशियाँ (मिथुन, तुला, कुंभ)',
            'Earth Signs'              => 'पृथ्वी तत्व राशियाँ (वृषभ, कन्या, मकर)',
            'Fire Signs'               => 'अग्नि तत्व राशियाँ (मेष, सिंह, धनु)',
            'All Signs'                => 'सभी 12 राशियाँ (All Signs)',
            'Gemini, Libra, Aquarius'  => 'मिथुन, तुला, कुंभ (Air Signs)',
            'Cancer, Scorpio, Pisces'  => 'कर्क, वृश्चिक, मीन (Water Signs)',
            'Taurus, Virgo, Capricorn' => 'वृषभ, कन्या, मकर (Earth Signs)',
            'Aries, Leo, Sagittarius'  => 'मेष, सिंह, धनु (Fire Signs)',
        ];

        // Master Report products for Cross-Sell
        $report_products = [
            [
                'title'     => ($lang === 'hi') ? 'मास्टर जन्म कुंडली एवं हार्मोनिक्स रिपोर्ट' : 'Master Janma Kundali & Harmonics Report',
                'url'       => 'https://merarashifal.com/reports/janam-kundali-master-report/',
                'local_url' => home_url('/reports/janam-kundali-master-report/'),
                'img_file'  => '/wp-content/uploads/2026/11/janma-kundali-harmonics-report.webp',
                'sale_price'=> '₹99',
                'mrp'       => '₹999',
                'discount'  => '90% OFF',
                'pages'     => ($lang === 'hi') ? '26 पृष्ठ' : '26 Pages',
                'badge'     => 'MOST POPULAR'
            ],
            [
                'title'     => ($lang === 'hi') ? 'वर-वधू कुंडली मिलान (36 गुण मिलान) रिपोर्ट' : 'Master Kundali Matching (Gun Milan) Report',
                'url'       => 'https://merarashifal.com/reports/kundali-and-gun-matching-report/',
                'local_url' => home_url('/reports/kundali-and-gun-matching-report/'),
                'img_file'  => '/wp-content/uploads/2026/11/kundali-matching-gun-milan-report.webp',
                'sale_price'=> '₹99',
                'mrp'       => '₹1,499',
                'discount'  => '93% OFF',
                'pages'     => ($lang === 'hi') ? '24 पृष्ठ' : '24 Pages',
                'badge'     => 'VEDIC MATCH'
            ],
            [
                'title'     => ($lang === 'hi') ? 'वार्षिक राशिफल 2027 एवं गोचर रिपोर्ट' : 'Yearly Horoscope 2027 Transit Report',
                'url'       => 'https://merarashifal.com/reports/yearly-horoscope-report/',
                'local_url' => home_url('/reports/yearly-horoscope-report/'),
                'img_file'  => '/wp-content/uploads/2026/11/yearly-horoscope-2027-report.webp',
                'sale_price'=> '₹99',
                'mrp'       => '₹999',
                'discount'  => '90% OFF',
                'pages'     => ($lang === 'hi') ? '30 पृष्ठ' : '30 Pages',
                'badge'     => 'NEW 2027'
            ],
            [
                'title'     => ($lang === 'hi') ? 'अंक ज्योतिष एवं वैदिक न्यूमरोलॉजी रिपोर्ट' : 'Master Numerology & Vedic Integration Report',
                'url'       => 'https://merarashifal.com/reports/numerology-report/',
                'local_url' => home_url('/reports/numerology-report/'),
                'img_file'  => '/wp-content/uploads/2026/11/numerology-vedic-integration-report.webp',
                'sale_price'=> '₹99',
                'mrp'       => '₹599',
                'discount'  => '83% OFF',
                'pages'     => ($lang === 'hi') ? '22 पृष्ठ' : '22 Pages',
                'badge'     => 'BESTSELLER'
            ]
        ];

        $is_local = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);

        ob_start();
?>
<style>
/* ===================================================
 * MERARASHIFAL MUHURAT ENGINE - ULTRA-COMPACT VEDIC THEME
 * =================================================== */
.mru-wrap {
    max-width: 1040px;
    margin: 0 auto 24px;
    padding: 12px 10px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
    color: #1F2937;
    background: #FFFBEB;
    border: 1px solid #FCD34D;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(217, 119, 6, 0.05);
    box-sizing: border-box;
}
.mru-wrap * { box-sizing: border-box; }

/* HEADER BANNER */
.mru-header {
    text-align: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #FDE68A;
}
.mru-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}
.mru-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #FEF3C7;
    color: #92400E;
    font-size: 11px;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 16px;
    border: 1px solid #FCD34D;
    text-transform: uppercase;
}
.mru-lang-box {
    display: inline-flex;
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    border-radius: 8px;
    padding: 2px;
    gap: 2px;
}
.mru-lang-btn {
    border: none;
    background: transparent;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #78350F;
    border-radius: 6px;
    text-decoration: none !important;
    transition: all 0.15s ease;
    cursor: pointer;
    line-height: 1.2;
}
.mru-lang-btn:hover {
    color: #8C2514;
}
.mru-lang-btn.active {
    background: #8C2514;
    color: #FFFFFF !important;
    box-shadow: 0 1px 4px rgba(140, 37, 20, 0.2);
}
.mru-header-title {
    font-size: 20px;
    font-weight: 900;
    color: #8C2514;
    margin: 0 0 4px;
    line-height: 1.25;
}
.mru-header-sub {
    font-size: 12.5px;
    color: #78350F;
    margin: 0;
    line-height: 1.35;
}

/* OCCASION CARDS (COMPACT HERO SELECTOR) */
.mru-icons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 14px;
}
@media (max-width: 680px) {
    .mru-icons { grid-template-columns: repeat(2, 1fr); gap: 6px; }
}
.mru-card {
    background: #FFFFFF;
    border: 1.5px solid #FDE68A;
    border-radius: 10px;
    padding: 8px 6px;
    text-align: center;
    cursor: pointer;
    text-decoration: none !important;
    color: #451A03;
    transition: all 0.15s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 1px 3px rgba(180, 83, 9, 0.03);
    position: relative;
}
.mru-card:hover {
    border-color: #D97706;
    transform: translateY(-2px);
    background: #FFFDF7;
}
.mru-card.active {
    background: #FEF3C7;
    border-color: #8C2514;
    box-shadow: 0 0 0 1.5px #8C2514;
}
.mru-card-img-wrap {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    margin-bottom: 5px;
    position: relative;
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.mru-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.mru-card-title {
    font-size: 12.5px;
    font-weight: 800;
    color: #8C2514;
    line-height: 1.2;
    margin-bottom: 1px;
}
.mru-card-sub {
    font-size: 10px;
    color: #78350F;
    line-height: 1.15;
    opacity: 0.85;
}
.mru-card-active-pill {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #8C2514;
    color: #FFFFFF;
    font-size: 8.5px;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 6px;
    line-height: 1.1;
}

/* COMPACT SWITCHER (PILLS) */
.mru-switcher {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
    justify-content: center;
}
.mru-switch-btn {
    padding: 5px 12px;
    border-radius: 16px;
    background: #FFFFFF;
    border: 1px solid #FCD34D;
    font-size: 12px;
    font-weight: 700;
    color: #78350F;
    text-decoration: none !important;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mru-switch-btn:hover {
    border-color: #8C2514;
    color: #8C2514;
}
.mru-switch-btn.active {
    background: #8C2514;
    color: #FFFFFF;
    border-color: #8C2514;
}

/* INITIAL PROMPT (Zero-Scroll First Visit) */
.mru-initial-prompt {
    text-align: center;
    padding: 18px 14px;
    background: #FFFFFF;
    border-radius: 12px;
    border: 1.5px dashed #FCD34D;
    margin: 8px 0 18px;
}
.mru-prompt-icon {
    font-size: 26px;
    margin-bottom: 4px;
    display: inline-block;
}
.mru-initial-prompt h3 {
    font-size: 16px;
    font-weight: 800;
    color: #8C2514;
    margin: 0 0 4px;
}
.mru-initial-prompt p {
    font-size: 13px;
    color: #78350F;
    margin: 0;
    max-width: 580px;
    margin: 0 auto;
    line-height: 1.4;
}

/* TIGHT CONTROLS BAR */
.mru-controls {
    background: #FFFFFF;
    border: 1px solid #FCD34D;
    padding: 8px 12px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
.mru-ctrl-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.mru-ctrl-label {
    font-size: 12px;
    font-weight: 800;
    color: #78350F;
}
.mru-select {
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #FCD34D;
    background: #FFFDF7;
    font-size: 12px;
    font-weight: 700;
    color: #451A03;
    height: 30px;
    cursor: pointer;
    outline: none;
}
.mru-year-pills {
    display: inline-flex;
    gap: 2px;
    background: #FEF3C7;
    padding: 2px;
    border-radius: 6px;
    border: 1px solid #FDE68A;
}
.mru-year-pill {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 800;
    background: transparent;
    border: none;
    color: #78350F;
    text-decoration: none !important;
    transition: all 0.15s ease;
    cursor: pointer;
}
.mru-year-pill.active {
    background: #8C2514;
    color: #FFFFFF !important;
}
.mru-btn {
    padding: 4px 10px;
    border-radius: 6px;
    background: #FFFDF7;
    border: 1px solid #FCD34D;
    cursor: pointer;
    text-decoration: none !important;
    color: #78350F;
    font-weight: 700;
    font-size: 11.5px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.mru-btn:hover {
    background: #FEF3C7;
    color: #8C2514;
}
.mru-btn[disabled] {
    opacity: 0.35;
    pointer-events: none;
}
.mru-btn-gold {
    background: #8C2514;
    color: #FFFFFF !important;
    border-color: #8C2514;
}
@media (max-width: 600px) {
    .mru-controls { flex-direction: column; align-items: stretch; gap: 8px; }
    .mru-ctrl-group { justify-content: space-between; }
    .mru-select { flex: 1; }
}

/* MONTH HEADER */
.mru-month-header {
    background: #FFFFFF;
    border: 1px solid #FCD34D;
    border-left: 4px solid #8C2514;
    border-radius: 8px;
    padding: 8px 12px;
    margin: 12px 0 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
}
.mru-month-title {
    font-size: 15px;
    font-weight: 800;
    color: #8C2514;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mru-badge-count {
    font-size: 11px;
    font-weight: 800;
    background: #FEF3C7;
    color: #92400E;
    padding: 2px 8px;
    border-radius: 12px;
    border: 1px solid #FDE68A;
}

/* ===================================================
 * COMPACT VEDIC CARDS GRID (MOBILE & DESKTOP COMPACT)
 * =================================================== */
.mru-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}
@media (max-width: 768px) {
    .mru-cards-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}

.mru-item-card {
    background: #FFFFFF;
    border: 1px solid #FCD34D;
    border-radius: 10px;
    padding: 10px 12px;
    box-shadow: 0 1px 4px rgba(180, 83, 9, 0.04);
    display: flex;
    flex-direction: column;
    gap: 7px;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
.mru-item-card:hover {
    border-color: #D97706;
    box-shadow: 0 3px 8px rgba(180, 83, 9, 0.08);
}

/* CARD TOP: DATE & TIMING IN ONE COMPACT ROW */
.mru-item-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding-bottom: 7px;
    border-bottom: 1px dashed #FDE68A;
}
.mru-head-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.mru-date-num {
    background: #FEF3C7;
    color: #8C2514;
    font-size: 17px;
    font-weight: 900;
    line-height: 1;
    padding: 5px 7px;
    border-radius: 6px;
    border: 1px solid #FCD34D;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
}
.mru-date-col {
    display: flex;
    flex-direction: column;
}
.mru-date-txt {
    font-size: 13.5px;
    font-weight: 800;
    color: #1F2937;
    line-height: 1.2;
}
.mru-day-txt {
    font-size: 11.5px;
    font-weight: 700;
    color: #8C2514;
    line-height: 1.2;
}

/* TIMING BADGE */
.mru-timing-badge {
    background: #FFFDF7;
    border: 1px solid #FCD34D;
    border-radius: 6px;
    padding: 4px 8px;
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}
.mru-timing-label {
    font-size: 9.5px;
    font-weight: 800;
    color: #B45309;
    text-transform: uppercase;
    line-height: 1.1;
}
.mru-timing-val {
    font-size: 12px;
    font-weight: 800;
    color: #15803D;
    line-height: 1.2;
    white-space: nowrap;
}

/* COMPACT 2-COLUMN INLINE SPECS */
.mru-item-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 10px;
    background: #FFFDF7;
    padding: 6px 9px;
    border-radius: 6px;
    border: 1px solid #FEF3C7;
}
.mru-spec-row {
    display: flex;
    align-items: baseline;
    gap: 4px;
    font-size: 12px;
}
.mru-spec-row-wide {
    grid-column: 1 / -1;
    border-top: 1px dashed #FEF3C7;
    padding-top: 3px;
    margin-top: 2px;
}
.mru-spec-k {
    color: #78350F;
    font-weight: 600;
    font-size: 11px;
    white-space: nowrap;
    flex-shrink: 0;
}
.mru-spec-v {
    color: #1F2937;
    font-weight: 700;
    font-size: 12px;
    line-height: 1.25;
}

/* EMPTY STATE */
.mru-no-data {
    background: #FFFFFF;
    padding: 24px;
    border-radius: 12px;
    border: 1.5px dashed #FCD34D;
    text-align: center;
    color: #78350F;
    margin-bottom: 20px;
}
.mru-no-data-icon { font-size: 30px; margin-bottom: 6px; }
.mru-no-data-txt { font-size: 14px; font-weight: 600; margin: 0; }

/* ===================================================
 * KUNDALI IDENTICAL CROSS-SELL (mr-ku-crosssell-wrap)
 * =================================================== */
.mr-ku-crosssell-wrap {
    margin: 28px 0 10px 0;
    padding: 18px;
    background: #FFFFFF;
    border: 1.5px solid #FCD34D;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(180, 83, 9, 0.05);
}
.mr-ku-crosssell-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 8px;
    border-bottom: 1px dashed #FDE68A;
    padding-bottom: 10px;
}
.mr-ku-crosssell-title {
    font-size: 16px;
    font-weight: 900;
    color: #8C2514;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mr-ku-crosssell-badge {
    font-size: 11px;
    font-weight: 800;
    background: #FEF3C7;
    color: #B45309;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #FCD34D;
    text-transform: uppercase;
}
.mr-ku-crosssell-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
@media (max-width: 900px) {
    .mr-ku-crosssell-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 480px) {
    .mr-ku-crosssell-grid { grid-template-columns: 1fr; gap: 10px; }
}
.mr-ku-crosssell-card {
    background: #FFFDF7;
    border: 1.5px solid #FDE68A;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none !important;
    color: inherit;
    transition: all 0.22s ease;
    box-shadow: 0 2px 6px rgba(180, 83, 9, 0.03);
}
.mr-ku-crosssell-card:hover {
    transform: translateY(-3px);
    border-color: #8C2514;
    box-shadow: 0 8px 18px rgba(140, 37, 20, 0.1);
}
.mr-ku-crosssell-img-box {
    position: relative;
    background: #FEF3C7;
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #FDE68A;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 160px;
}
.mr-ku-crosssell-img {
    max-width: 140px;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.12));
    transition: transform 0.25s ease;
}
.mr-ku-crosssell-card:hover .mr-ku-crosssell-img {
    transform: scale(1.05);
}
.mr-ku-crosssell-content {
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-grow: 1;
}
.mr-ku-crosssell-tags {
    display: flex;
    gap: 6px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.mr-ku-tag-discount {
    background: #DCFCE7;
    color: #166534;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #86EFAC;
}
.mr-ku-tag-pages {
    background: #FEF3C7;
    color: #92400E;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #FDE68A;
}
.mr-ku-crosssell-name {
    font-size: 13px;
    font-weight: 800;
    color: #1F2937;
    line-height: 1.35;
    margin-bottom: 10px;
}
.mr-ku-crosssell-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px dashed #FDE68A;
}
.mr-ku-crosssell-price-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.mr-ku-crosssell-price {
    font-size: 16px;
    font-weight: 900;
    color: #8C2514;
}
.mr-ku-crosssell-mrp {
    font-size: 11.5px;
    color: #9CA3AF;
    text-decoration: line-through;
}
.mr-ku-crosssell-btn {
    background: #8C2514;
    color: #FFFFFF;
    font-size: 11px;
    font-weight: 800;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: background 0.15s ease;
}
.mr-ku-crosssell-card:hover .mr-ku-crosssell-btn {
    background: #6E1A0D;
}
</style>

<div class="mru-wrap" id="mr-muhurat-container">

    <!-- HEADER TITLE & BADGE -->
    <div class="mru-header">
        <div class="mru-header-top">
            <span class="mru-header-badge">✨ <?php echo ($lang === 'hi') ? 'वैदिक पंचांग एवं शुभ मुहूर्त' : 'Vedic Panchang & Auspicious Timings'; ?></span>
            <div class="mru-lang-box">
                <a href="<?php echo $build_url(['lang' => 'en']); ?>" class="mru-lang-btn <?php echo ($lang === 'en') ? 'active' : ''; ?>">English</a>
                <a href="<?php echo $build_url(['lang' => 'hi']); ?>" class="mru-lang-btn <?php echo ($lang === 'hi') ? 'active' : ''; ?>">हिन्दी</a>
            </div>
        </div>
        <h2 class="mru-header-title">
            <?php 
            if (!empty($selected_occasion) && isset($categories[$selected_occasion])) {
                echo esc_html(($lang === 'hi') ? $categories[$selected_occasion]['label_hi'] : $categories[$selected_occasion]['label_en']);
            } else {
                echo ($lang === 'hi') ? 'शुभ मुहूर्त चयन करें' : 'Select Shubh Muhurat';
            }
            ?>
        </h2>
        <p class="mru-header-sub">
            <?php 
            if (!empty($selected_occasion) && isset($categories[$selected_occasion])) {
                echo esc_html(($lang === 'hi') ? $categories[$selected_occasion]['sub_hi'] : $categories[$selected_occasion]['sub_en']);
            } else {
                echo ($lang === 'hi') 
                    ? 'विवाह, गृह प्रवेश, वाहन एवं संपत्ति खरीद के सबसे शुभ नक्षत्र, तिथि एवं चौघड़िया मुहूर्त देखें।'
                    : 'Discover the most auspicious nakshatras, tithis, and timings for wedding, housewarming, vehicle and property purchase.';
            }
            ?>
        </p>
    </div>

    <?php if ($show_icons === 'yes'): ?>
        <!-- OCCASION SELECTOR CARDS -->
        <div class="mru-icons">
        <?php foreach ($categories as $cat_name => $cat_meta):
            $is_active = ($selected_occasion === $cat_name);
            $card_url  = $build_url(['occasion' => $cat_name]);
            $title_txt = ($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en'];
            $sub_txt   = ($lang === 'hi') ? $cat_meta['sub_hi'] : $cat_meta['sub_en'];
        ?>
            <a href="<?php echo esc_url($card_url); ?>" class="mru-card <?php echo $is_active ? 'active' : ''; ?>">
                <?php if ($is_active): ?>
                    <span class="mru-card-active-pill"><?php echo ($lang === 'hi') ? 'चयनित' : 'ACTIVE'; ?></span>
                <?php endif; ?>
                <div class="mru-card-img-wrap">
                    <img src="<?php echo esc_url($cat_meta['icon']); ?>" 
                         onerror="this.onerror=null; this.src='<?php echo esc_url($cat_meta['fallback_icon']); ?>';" 
                         alt="<?php echo esc_attr($title_txt); ?>" 
                         loading="lazy">
                </div>
                <div class="mru-card-title"><?php echo esc_html($title_txt); ?></div>
                <div class="mru-card-sub"><?php echo esc_html($sub_txt); ?></div>
            </a>
        <?php endforeach; ?>
        </div>
    <?php elseif (!empty($selected_occasion)): ?>
        <!-- COMPACT OCCASION SWITCHER (PILLS) -->
        <div class="mru-switcher">
        <?php foreach ($categories as $cat_name => $cat_meta):
            $is_active   = ($selected_occasion === $cat_name);
            $switch_url  = $build_url(['occasion' => $cat_name]);
            $switch_text = ($lang === 'hi') ? ($cat_meta['emoji'] . ' ' . $cat_meta['label_hi']) : ($cat_meta['emoji'] . ' ' . $cat_meta['label_en']);
        ?>
            <a href="<?php echo esc_url($switch_url); ?>" class="mru-switch-btn <?php echo $is_active ? 'active' : ''; ?>">
                <?php echo esc_html($switch_text); ?>
            </a>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // =========================================================
    // FIRST VISIT / ZERO-SCROLL RULE:
    // If no occasion is selected, show friendly prompt & cross-sell immediately!
    // =========================================================
    if (!$selected_occasion):
        $prompt_title_en = "Please select any Shubh Muhurat category above";
        $prompt_title_hi = "कृपया ऊपर दिए गए विकल्पों में से किसी एक शुभ मुहूर्त का चयन करें";
        $prompt_desc_en  = "Click on Wedding (Vivah), Housewarming (Griha Pravesh), Vehicle, or Property above to view auspicious dates and timings for the current month.";
        $prompt_desc_hi  = "विवाह, गृह प्रवेश, वाहन या संपत्ति खरीद में से किसी भी विषय पर क्लिक करें और वर्तमान माह की शुभ तिथियां व नक्षत्र देखें।";
    ?>
        <div class="mru-initial-prompt">
            <span class="mru-prompt-icon">🪐</span>
            <h3><?php echo ($lang === 'hi') ? $prompt_title_hi : $prompt_title_en; ?></h3>
            <p><?php echo ($lang === 'hi') ? $prompt_desc_hi : $prompt_desc_en; ?></p>
        </div>

        <!-- Render Master Reports Cross-Sell Directly on Initial Screen -->
        <div class="mr-ku-crosssell-wrap">
            <div class="mr-ku-crosssell-header">
                <h3 class="mr-ku-crosssell-title">⚡ <?php echo ($lang === 'hi') ? 'अनुशंसित व्यक्तिगत वैदिक रिपोर्ट्स' : 'Recommended Master Reports for You'; ?></h3>
                <span class="mr-ku-crosssell-badge"><?php echo ($lang === 'hi') ? '100% सटीक विश्लेषण' : 'Personalized Vedic Readings'; ?></span>
            </div>
            <div class="mr-ku-crosssell-grid">
                <?php foreach ($report_products as $p):
                    $target_url = $is_local ? $p['local_url'] : $p['url'];
                    $img_src    = home_url($p['img_file']);
                ?>
                    <a href="<?php echo esc_url($target_url); ?>" class="mr-ku-crosssell-card" target="_blank" rel="noopener">
                        <div class="mr-ku-crosssell-img-box">
                            <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($p['title']); ?>" class="mr-ku-crosssell-img" loading="lazy">
                        </div>
                        <div class="mr-ku-crosssell-content">
                            <div>
                                <div class="mr-ku-crosssell-tags">
                                    <span class="mr-ku-tag-discount"><?php echo esc_html($p['discount']); ?></span>
                                    <span class="mr-ku-tag-pages"><?php echo esc_html($p['pages']); ?></span>
                                </div>
                                <div class="mr-ku-crosssell-name"><?php echo esc_html($p['title']); ?></div>
                            </div>
                            <div class="mr-ku-crosssell-bottom">
                                <div class="mr-ku-crosssell-price-row">
                                    <span class="mr-ku-crosssell-price"><?php echo esc_html($p['sale_price']); ?></span>
                                    <span class="mr-ku-crosssell-mrp"><?php echo esc_html($p['mrp']); ?></span>
                                </div>
                                <div class="mr-ku-crosssell-btn"><?php echo ($lang === 'hi') ? 'रिपोर्ट देखें &rarr;' : 'Get Report &rarr;'; ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
        return ob_get_clean();
    endif;

    // =========================================================
    // OCCASION DATA PROCESSING
    // =========================================================
    // Fetch all available months for this occasion
    $months = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT DATE_FORMAT(gregorian_date,'%Y-%m')
             FROM {$table}
             WHERE occasion=%s AND lang_code=%s ORDER BY 1 ASC",
             $selected_occasion, $lang
        )
    );

    if (empty($months)) {
        $months = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT DATE_FORMAT(gregorian_date,'%Y-%m')
                 FROM {$table}
                 WHERE occasion=%s ORDER BY 1 ASC",
                 $selected_occasion
            )
        );
    }

    if (empty($months)) {
        echo "<div class='mru-no-data'><div class='mru-no-data-icon'>⚠️</div><p class='mru-no-data-txt'>" . 
             (($lang === 'hi') ? 'इस श्रेणी के लिए अभी डेटा उपलब्ध नहीं है।' : 'No data recorded for this occasion yet.') . 
             "</p></div></div>";
        return ob_get_clean();
    }

    // Default Month Selection (project current month or nearest upcoming)
    $default_ym = '';
    $query_year = isset($_GET['year']) ? sanitize_text_field($_GET['year']) : (!empty($atts['year']) ? sanitize_text_field($atts['year']) : '');
    if ($query_year !== '') {
        $year_prefix = $query_year . '-';
        $year_months = array_filter($months, function($m) use ($year_prefix) {
            return strpos($m, $year_prefix) === 0;
        });
        if (!empty($year_months)) {
            $default_ym = reset($year_months);
        }
    }

    if (!$default_ym) {
        if (in_array($current_ym, $months)) {
            $default_ym = $current_ym;
        } else {
            $future_months = array_filter($months, function($m) use ($current_ym) {
                return $m >= $current_ym;
            });
            $default_ym = !empty($future_months) ? reset($future_months) : $months[0];
        }
    }

    $query_ym    = isset($_GET['ym']) ? sanitize_text_field($_GET['ym']) : (!empty($atts['ym']) ? sanitize_text_field($atts['ym']) : '');
    $selected_ym = ($query_ym && in_array($query_ym, $months)) ? $query_ym : $default_ym;

    $idx = array_search($selected_ym, $months);
    if ($idx === false) $idx = 0;

    $prev_ym = ($idx > 0) ? $months[$idx - 1] : $months[0];
    $next_ym = ($idx < count($months) - 1) ? $months[$idx + 1] : end($months);

    $months_to_show = max(1, intval($atts['months_count']));
    $display_months = array_slice($months, $idx, $months_to_show);

    $range_start = $display_months[0] . "-01";
    $range_end   = date("Y-m-t", strtotime(end($display_months) . "-01"));

    // Query rows for displayed month(s)
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE occasion=%s
             AND gregorian_date BETWEEN %s AND %s
             AND lang_code=%s
             ORDER BY gregorian_date ASC",
             $selected_occasion, $range_start, $range_end, $lang
        )
    );

    if (empty($rows)) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}
                 WHERE occasion=%s
                 AND gregorian_date BETWEEN %s AND %s
                 ORDER BY gregorian_date ASC",
                 $selected_occasion, $range_start, $range_end
            )
        );
    }

    $grouped = [];
    foreach ($rows as $r) {
        $k = date("Y-m", strtotime($r->gregorian_date));
        $grouped[$k][] = $r;
    }

    $fmt_date = function($d) use ($lang, $hi_months) {
        $t = DateTime::createFromFormat("Y-m-d", $d);
        if (!$t) return $d;
        $day  = $t->format("d");
        $m_en = $t->format("F");
        $yr   = $t->format("Y");
        if ($lang === 'hi' && isset($hi_months[$m_en])) {
            return "{$day} {$hi_months[$m_en]} {$yr}";
        }
        return "{$day} " . $t->format("M") . " {$yr}";
    };

    $cat_meta    = isset($categories[$selected_occasion]) ? $categories[$selected_occasion] : null;
    $occ_heading = ($cat_meta && $lang === 'hi') ? $cat_meta['label_hi'] : $selected_occasion;
    $active_year = substr($selected_ym, 0, 4);

    // Available distinct years from months
    $available_years = array_unique(array_map(function($m) { return substr($m, 0, 4); }, $months));
    sort($available_years);
    ?>

    <!-- SLEEK CONTROLS BAR -->
    <div class="mru-controls">
        <!-- Language & Year Group -->
        <div class="mru-ctrl-group">
            <div class="mru-lang-box">
                <a href="<?php echo $build_url(['lang' => 'en']); ?>" class="mru-lang-btn <?php echo ($lang === 'en') ? 'active' : ''; ?>">English</a>
                <a href="<?php echo $build_url(['lang' => 'hi']); ?>" class="mru-lang-btn <?php echo ($lang === 'hi') ? 'active' : ''; ?>">हिन्दी</a>
            </div>

            <span style="color:#FCD34D;">•</span>

            <span class="mru-ctrl-label">📅 <?php echo ($lang === 'hi') ? 'वर्ष:' : 'Year:'; ?></span>
            <div class="mru-year-pills">
                <?php foreach ($available_years as $yr): 
                    // Pick first month of that year
                    $yr_months = array_filter($months, function($m) use ($yr) { return strpos($m, $yr . '-') === 0; });
                    $target_ym = !empty($yr_months) ? reset($yr_months) : $selected_ym;
                ?>
                    <a href="<?php echo $build_url(['ym' => $target_ym]); ?>" 
                       class="mru-year-pill <?php echo ($active_year === $yr) ? 'active' : ''; ?>">
                        <?php echo esc_html($yr); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Month Navigation Group -->
        <div class="mru-ctrl-group">
            <select id="mruMonth" class="mru-select" style="min-width: 140px;">
                <?php foreach ($months as $m): 
                    $m_time   = strtotime($m . '-01');
                    $eng_name = date('F Y', $m_time);
                    $eng_m    = date('F', $m_time);
                    $y_str    = date('Y', $m_time);
                    $hi_name  = (isset($hi_months[$eng_m]) ? $hi_months[$eng_m] : $eng_m) . ' ' . $y_str;
                    $label    = ($lang === 'hi') ? $hi_name : $eng_name;
                ?>
                    <option value="<?php echo esc_attr($m); ?>" <?php selected($selected_ym, $m); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <a class="mru-btn" href="<?php echo $build_url(['ym' => $prev_ym]); ?>" <?php if ($idx === 0) echo "disabled"; ?>>
                &larr; <?php echo ($lang === 'hi') ? 'पिछला' : 'Prev'; ?>
            </a>
            <a class="mru-btn <?php echo ($selected_ym === $current_ym) ? 'mru-btn-gold' : ''; ?>" href="<?php echo $build_url(['ym' => $current_ym]); ?>">
                <?php echo ($lang === 'hi') ? 'वर्तमान माह' : 'Current Month'; ?>
            </a>
            <a class="mru-btn" href="<?php echo $build_url(['ym' => $next_ym]); ?>" <?php if ($idx >= count($months) - 1) echo "disabled"; ?>>
                <?php echo ($lang === 'hi') ? 'अगला' : 'Next'; ?> &rarr;
            </a>
        </div>
    </div>

    <!-- RESULTS SECTION -->
    <div id="mru-results">
    <?php foreach ($display_months as $dm):
        $month_ts = strtotime($dm . "-01");
        $m_en     = date("F", $month_ts);
        $m_yr     = date("Y", $month_ts);
        $month_display = ($lang === 'hi' && isset($hi_months[$m_en])) ? "{$hi_months[$m_en]} {$m_yr} – {$occ_heading}" : "{$m_en} {$m_yr} – {$occ_heading}";
        $count = !empty($grouped[$dm]) ? count($grouped[$dm]) : 0;
    ?>
        <div class="mru-month-header">
            <span class="mru-month-title">
                <span>🗓️</span>
                <span><?php echo esc_html($month_display); ?></span>
            </span>
            <span class="mru-badge-count">
                <?php echo ($lang === 'hi') ? "कुल {$count} शुभ मुहूर्त" : "{$count} Auspicious Dates"; ?>
            </span>
        </div>

        <?php if (empty($grouped[$dm])): ?>
            <div class="mru-no-data">
                <div class="mru-no-data-icon">🌙</div>
                <p class="mru-no-data-txt">
                    <?php echo ($lang === 'hi') 
                        ? 'इस माह में कोई शुभ मुहूर्त दर्ज नहीं है। कृपया अगला अथवा पिछला माह देखें।' 
                        : 'No auspicious dates recorded for this month. Please browse next or previous months.'; ?>
                </p>
            </div>
        <?php else: ?>
            <!-- MODERN VEDIC CARDS GRID -->
            <div class="mru-cards-grid">
            <?php foreach ($grouped[$dm] as $r):
                $day_str = ($lang === 'hi' && isset($hi_days[$r->day_name])) ? $hi_days[$r->day_name] : $r->day_name;
                $nak     = ($lang === 'hi' && !empty($r->nakshatra_hi)) ? $r->nakshatra_hi : $r->nakshatra;
                $tit     = ($lang === 'hi' && !empty($r->tithi_hi)) ? $r->tithi_hi : $r->tithi;
                $moon    = ($lang === 'hi' && !empty($r->moon_position_hi)) ? $r->moon_position_hi : $r->moon_position;
                if ($lang === 'hi') {
                    $raw_signs     = !empty($r->good_for_sun_signs_hi) ? $r->good_for_sun_signs_hi : $r->good_for_sun_signs;
                    $signs_display = isset($hi_signs_dict[$raw_signs]) ? $hi_signs_dict[$raw_signs] : $raw_signs;
                } else {
                    $signs_display = !empty($r->good_for_sun_signs) ? $r->good_for_sun_signs : ($r->good_for_sun_signs_hi ?: '-');
                }
                $clean_timing = str_replace(["\x96", "\xe2\x80\x93", "\ufffd", ' - '], ' – ', $r->muhurat_timing_ist);
            ?>
                <div class="mru-item-card">
                    <!-- Compact Top Bar: Date Left, Timing Right -->
                    <div class="mru-item-head">
                        <div class="mru-head-left">
                            <span class="mru-date-num"><?php echo esc_html(date('d', strtotime($r->gregorian_date))); ?></span>
                            <div class="mru-date-col">
                                <div class="mru-date-txt"><?php echo esc_html($fmt_date($r->gregorian_date)); ?></div>
                                <div class="mru-day-txt"><?php echo esc_html($day_str); ?></div>
                            </div>
                        </div>
                        <div class="mru-timing-badge">
                            <span class="mru-timing-label">⏰ <?php echo ($lang === 'hi') ? 'शुभ मुहूर्त' : 'Shubh Muhurat'; ?></span>
                            <div class="mru-timing-val"><?php echo esc_html($clean_timing); ?></div>
                        </div>
                    </div>

                    <!-- Compact 2-Column Inline Specs -->
                    <div class="mru-item-specs">
                        <div class="mru-spec-row">
                            <span class="mru-spec-k">🌟 <?php echo ($lang === 'hi') ? 'नक्षत्र' : 'Nakshatra'; ?>:</span>
                            <span class="mru-spec-v"><?php echo esc_html($nak ?: '-'); ?></span>
                        </div>
                        <div class="mru-spec-row">
                            <span class="mru-spec-k">🌔 <?php echo ($lang === 'hi') ? 'तिथि' : 'Tithi'; ?>:</span>
                            <span class="mru-spec-v"><?php echo esc_html($tit ?: '-'); ?></span>
                        </div>
                        <div class="mru-spec-row">
                            <span class="mru-spec-k">🌙 <?php echo ($lang === 'hi') ? 'चंद्र' : 'Moon Sign'; ?>:</span>
                            <span class="mru-spec-v"><?php echo esc_html($moon ?: '-'); ?></span>
                        </div>
                        <div class="mru-spec-row mru-spec-row-wide">
                            <span class="mru-spec-k">♈ <?php echo ($lang === 'hi') ? 'अनुकूल' : 'Auspicious For'; ?>:</span>
                            <span class="mru-spec-v"><?php echo esc_html($signs_display ?: '-'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- MASTER REPORTS CROSS-SELL (IDENTICAL TO KUNDALI) -->
    <?php if ($atts['show_cta'] !== 'no'): ?>
    <div class="mr-ku-crosssell-wrap">
        <div class="mr-ku-crosssell-header">
            <h3 class="mr-ku-crosssell-title">⚡ <?php echo ($lang === 'hi') ? 'अनुशंसित व्यक्तिगत वैदिक रिपोर्ट्स' : 'Recommended Master Reports for You'; ?></h3>
            <span class="mr-ku-crosssell-badge"><?php echo ($lang === 'hi') ? '100% सटीक विश्लेषण' : 'Personalized Vedic Readings'; ?></span>
        </div>
        <div class="mr-ku-crosssell-grid">
            <?php foreach ($report_products as $p):
                $target_url = $is_local ? $p['local_url'] : $p['url'];
                $img_src    = home_url($p['img_file']);
            ?>
                <a href="<?php echo esc_url($target_url); ?>" class="mr-ku-crosssell-card" target="_blank" rel="noopener">
                    <div class="mr-ku-crosssell-img-box">
                        <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($p['title']); ?>" class="mr-ku-crosssell-img" loading="lazy">
                    </div>
                    <div class="mr-ku-crosssell-content">
                        <div>
                            <div class="mr-ku-crosssell-tags">
                                <span class="mr-ku-tag-discount"><?php echo esc_html($p['discount']); ?></span>
                                <span class="mr-ku-tag-pages"><?php echo esc_html($p['pages']); ?></span>
                            </div>
                            <div class="mr-ku-crosssell-name"><?php echo esc_html($p['title']); ?></div>
                        </div>
                        <div class="mr-ku-crosssell-bottom">
                            <div class="mr-ku-crosssell-price-row">
                                <span class="mr-ku-crosssell-price"><?php echo esc_html($p['sale_price']); ?></span>
                                <span class="mr-ku-crosssell-mrp"><?php echo esc_html($p['mrp']); ?></span>
                            </div>
                            <div class="mr-ku-crosssell-btn"><?php echo ($lang === 'hi') ? 'रिपोर्ट देखें &rarr;' : 'Get Report &rarr;'; ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// Month dropdown change
document.getElementById("mruMonth") && document.getElementById("mruMonth").addEventListener("change", function() {
    let p = new URLSearchParams(window.location.search);
    p.set("ym", this.value);
    sessionStorage.setItem("mru_focus", "mru-results");
    window.location.search = p.toString();
});

// Smooth scroll retention on language & filter buttons
document.querySelectorAll(".mru-lang-btn, .mru-year-pill").forEach(function(el) {
    el.addEventListener("click", function() {
        sessionStorage.setItem("mru_focus", "mr-muhurat-container");
    });
});

// Smooth scroll restoration after month/year change
document.addEventListener("DOMContentLoaded", function() {
    let targetId = sessionStorage.getItem("mru_focus");
    if (targetId) {
        const el = document.getElementById(targetId);
        if (el) el.scrollIntoView({behavior: "smooth", block: "start"});
        sessionStorage.removeItem("mru_focus");
    }
});
</script>
<?php
        return ob_get_clean();
    }
}
