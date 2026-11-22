<?php
// Snippet 47: Muhurat Code (Fully Dynamic Shortcode - Current Month Default, No Duplicate Breadcrumb, No Hardcoded Year URLs)

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
            'lang'         => 'hi',
            'show_icons'   => 'auto',
            'show_cta'     => 'yes',
            'months_count' => '1',
        ], $atts, 'muhurat_viewer');

        $table = $wpdb->prefix . 'muhurat';

        if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table)))) {
            return "<p style='color:red; text-align:center;'>Error: table {$table} not found.</p>";
        }

        // Occasion categories metadata
        $categories = [
            'Vivah Muhurat' => [
                'slug'            => 'vivah-muhurat',
                'label_en'        => 'Vivah Muhurat (Wedding)',
                'label_hi'        => 'विवाह मुहूर्त',
                'icon'            => 'https://merarashifal.com/wp-content/uploads/2025/11/Vivah-Mahurat.webp',
                'report_title_en' => 'Personalized Kundali & Gun Matching Report',
                'report_title_hi' => 'वर-वधू कुंडली मिलान एवं गुण मिलान रिपोर्ट',
                'report_desc_en'  => 'Ensure marital harmony, 36 Guna Milan, and Mangal Dosha check before finalizing wedding dates.',
                'report_desc_hi'  => 'विवाह की तिथि पक्की करने से पूर्व 36 गुण मिलान, मांगलिक दोष एवं भकूट दोष की सूक्ष्म जांच कराएं।',
                'report_link'     => '/reports/kundali-and-gun-matching-report/',
                'report_btn_en'   => 'Get Kundali Matching Report &rarr;',
                'report_btn_hi'   => 'विवाह कुंडली मिलान रिपोर्ट प्राप्त करें &rarr;',
            ],
            'Griha Pravesh' => [
                'slug'            => 'griha-pravesh',
                'label_en'        => 'Griha Pravesh (Housewarming)',
                'label_hi'        => 'गृह प्रवेश मुहूर्त',
                'icon'            => 'https://merarashifal.com/wp-content/uploads/2025/11/Griha-Pravesh.webp',
                'report_title_en' => 'Personalized Griha Pravesh & Kundali Report',
                'report_title_hi' => 'व्यक्तिगत गृह प्रवेश एवं जन्म कुंडली रिपोर्ट',
                'report_desc_en'  => 'Select the most auspicious lagna and nakshatra according to the house owner’s Janam Kundali for lifelong peace and prosperity.',
                'report_desc_hi'  => 'घर के मुखिया की जन्म कुंडली अनुसार सबसे शुभ लग्न एवं नक्षत्र चुनकर सुख-समृद्धि एवं शांति सुनिश्चित करें।',
                'report_link'     => '/reports/janam-kundali-master-report/',
                'report_btn_en'   => 'Get Master Kundali Report &rarr;',
                'report_btn_hi'   => 'मास्टर जन्म कुंडली रिपोर्ट प्राप्त करें &rarr;',
            ],
            'Vehicle Purchase' => [
                'slug'            => 'vehicle-purchase',
                'label_en'        => 'Vehicle Purchase (Car/Bike)',
                'label_hi'        => 'वाहन खरीद मुहूर्त',
                'icon'            => 'https://merarashifal.com/wp-content/uploads/2025/11/Vehicle-Purchase.webp',
                'report_title_en' => 'Personalized Vehicle Muhurat & Kundali Report',
                'report_title_hi' => 'वाहन खरीद शुभ मुहूर्त एवं कुंडली विश्लेषण',
                'report_desc_en'  => 'Choose the safest, most fortunate delivery time for your vehicle based on planetary transits and your natal chart.',
                'report_desc_hi'  => 'वाहन डिलीवरी एवं पंजीकरण के लिए अपनी राशि व शुक्र ग्रह अनुसार सबसे सुरक्षित समय का चयन करें।',
                'report_link'     => '/reports/janam-kundali-master-report/',
                'report_btn_en'   => 'Get Kundali Analysis &rarr;',
                'report_btn_hi'   => 'व्यक्तिगत कुंडली परामर्श प्राप्त करें &rarr;',
            ],
            'Property Purchase' => [
                'slug'            => 'property-purchase',
                'label_en'        => 'Property Purchase (Land/Flat)',
                'label_hi'        => 'संपत्ति खरीद मुहूर्त',
                'icon'            => 'https://merarashifal.com/wp-content/uploads/2025/11/Property-Purchase.webp',
                'report_title_en' => 'Wealth & Property Astro-Consultation Report',
                'report_title_hi' => 'संपत्ति एवं धन-समृद्धि ज्योतिष परामर्श रिपोर्ट',
                'report_desc_en'  => 'Verify favorable 4th house and planetary yogas to prevent legal disputes and ensure lasting real estate prosperity.',
                'report_desc_hi'  => 'अपनी कुंडली के चतुर्थ भाव एवं भूमि योग का विश्लेषण कराकर संपत्ति निवेश को विवाद-मुक्त एवं फलदायी बनाएं।',
                'report_link'     => '/reports/janam-kundali-master-report/',
                'report_btn_en'   => 'Order Property Astro Report &rarr;',
                'report_btn_hi'   => 'संपत्ति ज्योतिष रिपोर्ट प्राप्त करें &rarr;',
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

        // 1. Language Resolution
        $lang = 'hi';
        if (!empty($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
        } elseif (!empty($atts['lang'])) {
            $lang = sanitize_text_field($atts['lang']);
        }
        if (!in_array($lang, ['hi', 'en'])) {
            $lang = 'hi';
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
            // Show icon cards if no specific occasion is hard-coded in the shortcode
            $show_icons = empty($atts['occasion']) ? 'yes' : 'no';
        }

        // Current Month
        $current_ym = date('Y-m'); // e.g. "2026-11"
        $current_year = date('Y'); // e.g. "2026"

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

        ob_start();
?>
<style>
.mru-wrap {
    max-width: 1040px;
    margin: 0 auto 24px;
    padding: 8px 12px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", Arial, sans-serif;
    color: #2c2523;
}
/* OCCASION TABS / CARDS */
.mru-icons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
@media (max-width: 768px) {
    .mru-icons { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 420px) {
    .mru-icons { grid-template-columns: repeat(2, 1fr); gap: 8px; }
}
.mru-card {
    background: #FAF6F0;
    border: 2px solid #e7dfd5;
    border-radius: 12px;
    padding: 10px 8px;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    color: #4a3b32;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mru-card:hover {
    border-color: #8c2514;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(140, 37, 20, 0.12);
}
.mru-card.active {
    background: #fff6ee;
    border-color: #8c2514;
    box-shadow: 0 0 0 2px #8c2514;
}
.mru-card img {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    object-fit: cover;
    margin-bottom: 6px;
    border: 1px solid #ebd9c8;
    background: #fff;
}
.mru-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #2c2523;
    line-height: 1.25;
}

/* OCCASION QUICK SWITCHER PILLS (When icons hidden or in header) */
.mru-switcher {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
    justify-content: center;
}
.mru-switch-btn {
    padding: 6px 14px;
    border-radius: 20px;
    background: #FAF6F0;
    border: 1px solid #cfc8bc;
    font-size: 13px;
    font-weight: 600;
    color: #5a4633;
    text-decoration: none;
    transition: all 0.15s ease;
}
.mru-switch-btn:hover, .mru-switch-btn.active {
    background: #8c2514;
    color: #fff;
    border-color: #8c2514;
}

/* CONTROLS BAR */
.mru-controls {
    background: #FAF6F0;
    border: 1px solid #ebd9c8;
    padding: 10px 14px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}
.mru-ctrl-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.mru-ctrl-label {
    font-size: 13px;
    font-weight: 700;
    color: #5a4633;
}
.mru-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #cfc8bc;
    background: #fff;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    height: 34px;
}
.mru-month-input {
    padding: 5px 8px;
    border-radius: 6px;
    border: 1px solid #cfc8bc;
    background: #fff;
    font-size: 13px;
    height: 34px;
    color: #333;
}
.mru-btn {
    padding: 6px 12px;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #cfc8bc;
    cursor: pointer;
    text-decoration: none;
    color: #5a4633;
    font-weight: 600;
    font-size: 12px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
}
.mru-btn:hover { background: #f0e8de; }
.mru-btn[disabled] { opacity: 0.35; pointer-events: none; }
.mru-btn-gold {
    background: #8c2514;
    color: #fff !important;
    border-color: #8c2514;
}
.mru-btn-gold:hover { background: #731e0f; }

/* YEAR QUICK JUMP PILLS */
.mru-year-pills {
    display: inline-flex;
    gap: 4px;
}
.mru-year-pill {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    background: #fff;
    border: 1px solid #cfc8bc;
    color: #5a4633;
    text-decoration: none;
}
.mru-year-pill.active {
    background: #8c2514;
    color: #fff;
    border-color: #8c2514;
}

@media (max-width: 680px) {
    .mru-controls { flex-direction: column; align-items: stretch; }
    .mru-ctrl-group { justify-content: space-between; }
    .mru-month-input { flex: 1 1 auto; }
}

/* RESULTS HEADINGS */
.mru-month-header {
    font-size: 20px;
    font-weight: 700;
    color: #8c2514;
    margin: 18px 0 10px;
    padding-bottom: 6px;
    border-bottom: 2px solid #ebd9c8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.mru-badge {
    font-size: 12px;
    font-weight: 600;
    background: #e7dfd5;
    color: #5a4633;
    padding: 3px 8px;
    border-radius: 12px;
}

/* TABLE - DESKTOP */
.mru-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background: #FAF6F0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.mru-table th {
    background: #ebd9c8;
    padding: 10px 11px;
    font-weight: 700;
    text-align: left;
    color: #5a4633;
    font-size: 13px;
    border-bottom: 1px solid #dccebc;
}
.mru-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #ebd9c8;
    color: #2c2523;
    font-size: 13px;
    vertical-align: top;
}
.mru-table tr:nth-child(even) td { background: #f4ece1; }

/* TABLE - MOBILE RESPONSIVE CARD STACK (Zero horizontal scroll) */
@media (max-width: 768px) {
    .mru-table { border: 0; background: transparent; box-shadow: none; display: block; }
    .mru-table thead { display: none; }
    .mru-table tbody, .mru-table tr { display: block; }
    .mru-table tr {
        border: 1px solid #e7dfd5;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
        background: #FAF6F0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .mru-table td {
        border: 0;
        border-bottom: 1px dashed #ebd9c8;
        padding: 6px 0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        font-size: 13px;
    }
    .mru-table td:last-child { border-bottom: 0; }
    .mru-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #731e0f;
        width: 44%;
        padding-right: 6px;
        text-align: left;
    }
    .mru-cell-val {
        width: 56%;
        text-align: right;
        color: #2c2523;
    }
    .mru-month-header { font-size: 17px; }
}

/* HIGH CONVERTING CTA CARD */
.mru-cta-card {
    background: linear-gradient(135deg, #fff9f2 0%, #faefe3 100%);
    border: 2px solid #e5c3a3;
    border-radius: 12px;
    padding: 18px;
    margin: 22px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 4px 12px rgba(140, 37, 20, 0.08);
}
@media (max-width: 680px) {
    .mru-cta-card {
        flex-direction: column;
        text-align: center;
        padding: 16px;
    }
}
.mru-cta-info h3 {
    margin: 0 0 5px;
    font-size: 16px;
    color: #8c2514;
    font-weight: 700;
}
.mru-cta-info p {
    margin: 0;
    font-size: 13px;
    color: #5a4633;
    line-height: 1.45;
}
.mru-cta-btn {
    background: #8c2514;
    color: #fff !important;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(140, 37, 20, 0.3);
    transition: all 0.2s ease;
    display: inline-block;
}
.mru-cta-btn:hover {
    background: #6e1a0d;
    transform: translateY(-1px);
}
</style>

<div class="mru-wrap">

<?php if ($show_icons === 'yes'): ?>
    <!-- OCCASION SELECTOR CARDS -->
    <div class="mru-icons">
    <?php foreach ($categories as $cat_name => $cat_meta):
        $is_active = ($selected_occasion === $cat_name);
        $card_url = $build_url(['occasion' => $cat_name]);
    ?>
        <a href="<?php echo esc_url($card_url); ?>" class="mru-card <?php echo $is_active ? 'active' : ''; ?>">
            <img src="<?php echo esc_url($cat_meta['icon']); ?>" alt="<?php echo esc_attr(($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en']); ?>">
            <div class="mru-card-title"><?php echo esc_html(($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en']); ?></div>
        </a>
    <?php endforeach; ?>
    </div>
<?php elseif (!empty($selected_occasion)): ?>
    <!-- COMPACT OCCASION SWITCHER -->
    <div class="mru-switcher">
    <?php foreach ($categories as $cat_name => $cat_meta):
        $is_active = ($selected_occasion === $cat_name);
        $switch_url = $build_url(['occasion' => $cat_name]);
    ?>
        <a href="<?php echo esc_url($switch_url); ?>" class="mru-switch-btn <?php echo $is_active ? 'active' : ''; ?>">
            <?php echo esc_html(($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en']); ?>
        </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// If no occasion selected, show friendly prompt
if (!$selected_occasion) {
    $prompt_en = "Please select any Shubh Muhurat category above to view auspicious dates and timings for the current month.";
    $prompt_hi = "कृपया ऊपर दिए गए विकल्पों में से किसी एक शुभ मुहूर्त का चयन करें और वर्तमान माह की शुभ तिथियां एवं समय देखें।";
    echo "<div style='text-align:center; padding:25px 15px; background:#FAF6F0; border-radius:10px; border:1px solid #ebd9c8; margin-top:10px;'>";
    echo "<p style='font-size:15px; color:#5a4633; margin:0; font-weight:600;'>" . (($lang === 'hi') ? $prompt_hi : $prompt_en) . "</p>";
    echo "</div></div>";
    return ob_get_clean();
}

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
    echo "<p style='text-align:center; padding:20px; font-weight:600; color:#8c2514;'>No data available for this occasion yet.</p></div>";
    return ob_get_clean();
}

// -------------------------------------------------------------
// DEFAULT MONTH SELECTION: Current month (date('Y-m')) projected!
// -------------------------------------------------------------
$default_ym = '';

// Check if user specifically passed a year parameter (e.g. ?year=2027)
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
        // ALWAYS project the CURRENT MONTH when opening the page!
        $default_ym = $current_ym;
    } else {
        // If current month has no entries, pick the nearest upcoming month
        $future_months = array_filter($months, function($m) use ($current_ym) {
            return $m >= $current_ym;
        });
        $default_ym = !empty($future_months) ? reset($future_months) : $months[0];
    }
}

// Check if user specifically queried a month via ?ym=YYYY-MM
$query_ym = isset($_GET['ym']) ? sanitize_text_field($_GET['ym']) : (!empty($atts['ym']) ? sanitize_text_field($atts['ym']) : '');
$selected_ym = ($query_ym && in_array($query_ym, $months)) ? $query_ym : $default_ym;

$idx = array_search($selected_ym, $months);
if ($idx === false) $idx = 0;

$prev_ym = ($idx > 0) ? $months[$idx - 1] : $months[0];
$next_ym = ($idx < count($months) - 1) ? $months[$idx + 1] : end($months);

// Determine which months to display
$months_to_show = max(1, intval($atts['months_count']));
$display_months = array_slice($months, $idx, $months_to_show);

$range_start = $display_months[0] . "-01";
$range_end   = date("Y-m-t", strtotime(end($display_months) . "-01"));

// Query rows for the displayed month(s)
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

$fmt = function($d) {
    $t = DateTime::createFromFormat("Y-m-d", $d);
    return $t ? $t->format("d/m/Y") : $d;
};

$cat_meta = isset($categories[$selected_occasion]) ? $categories[$selected_occasion] : null;
$occ_heading = ($cat_meta && $lang === 'hi') ? $cat_meta['label_hi'] : $selected_occasion;

// Localized strings
$th_date = ($lang === 'hi') ? 'दिनांक (Date)' : 'Date';
$th_day  = ($lang === 'hi') ? 'दिन (Day)' : 'Day';
$th_time = ($lang === 'hi') ? 'शुभ मुहूर्त समय' : 'Muhurat Timing';
$th_nak  = ($lang === 'hi') ? 'नक्षत्र' : 'Nakshatra';
$th_tit  = ($lang === 'hi') ? 'तिथि' : 'Tithi';
$th_moon = ($lang === 'hi') ? 'चंद्र स्थिति' : 'Moon Position';
$th_sign = ($lang === 'hi') ? 'अनुकूल राशियाँ' : 'Good For Sun Signs';

$hi_months = [
    'January' => 'जनवरी', 'February' => 'फरवरी', 'March' => 'मार्च', 'April' => 'अप्रैल',
    'May' => 'मई', 'June' => 'जून', 'July' => 'जुलाई', 'August' => 'अगस्त',
    'September' => 'सितंबर', 'October' => 'अक्टूबर', 'November' => 'नवंबर', 'December' => 'दिसंबर'
];

$active_year = substr($selected_ym, 0, 4);
?>

<!-- CONTROL BAR -->
<div class="mru-controls">
    <!-- Language & Year Selector -->
    <div class="mru-ctrl-group">
        <label class="mru-ctrl-label"><?php echo ($lang === 'hi') ? 'भाषा:' : 'Lang:'; ?></label>
        <form id="langForm" method="get" style="display:inline;">
            <input type="hidden" name="occasion" value="<?php echo esc_attr($selected_occasion); ?>">
            <input type="hidden" name="ym" value="<?php echo esc_attr($selected_ym); ?>">
            <select id="mru-lang" name="lang" class="mru-select">
                <option value="hi" <?php selected($lang, 'hi'); ?>>हिंदी</option>
                <option value="en" <?php selected($lang, 'en'); ?>>English</option>
            </select>
        </form>

        <span style="color:#cfc8bc;">|</span>

        <!-- Year Quick Switcher -->
        <span class="mru-ctrl-label"><?php echo ($lang === 'hi') ? 'वर्ष:' : 'Year:'; ?></span>
        <div class="mru-year-pills">
            <a href="<?php echo $build_url(['ym' => '2026-11']); ?>" class="mru-year-pill <?php echo ($active_year === '2026') ? 'active' : ''; ?>">2026</a>
            <a href="<?php echo $build_url(['ym' => '2027-01']); ?>" class="mru-year-pill <?php echo ($active_year === '2027') ? 'active' : ''; ?>">2027</a>
        </div>
    </div>

    <!-- Month Navigation Buttons & Picker -->
    <div class="mru-ctrl-group">
        <!-- Safari & Universal Month Selector Dropdown -->
        <select id="mruMonth" class="mru-select" style="min-width: 140px; font-weight: 600;">
            <?php foreach ($months as $m): 
                $m_time = strtotime($m . '-01');
                $eng_name = date('F Y', $m_time);
                $eng_m = date('F', $m_time);
                $y_str = date('Y', $m_time);
                $hi_name = (isset($hi_months[$eng_m]) ? $hi_months[$eng_m] : $eng_m) . ' ' . $y_str;
                $label = ($lang === 'hi') ? $hi_name : $eng_name;
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

<div id="mru-results">

<?php foreach ($display_months as $dm):
    $month_ts = strtotime($dm . "-01");
    $m_en = date("F", $month_ts);
    $m_yr = date("Y", $month_ts);
    $month_display = ($lang === 'hi' && isset($hi_months[$m_en])) ? "{$hi_months[$m_en]} {$m_yr} – {$occ_heading}" : "{$m_en} {$m_yr} – {$occ_heading}";
    $count = !empty($grouped[$dm]) ? count($grouped[$dm]) : 0;
?>
<div class="mru-month-header">
    <span><?php echo esc_html($month_display); ?></span>
    <span class="mru-badge"><?php echo ($lang === 'hi') ? "कुल {$count} मुहूर्त" : "{$count} Auspicious Dates"; ?></span>
</div>

<?php if (empty($grouped[$dm])): ?>
    <div style="background:#FAF6F0; padding:18px; border-radius:8px; border:1px dashed #ebd9c8; text-align:center; color:#6b584e; margin-bottom:18px;">
        <?php echo ($lang === 'hi') ? 'इस माह में कोई मुहूर्त उपलब्ध नहीं है। कृपया अगला अथवा पिछला माह देखें।' : 'No auspicious muhurat entries recorded for this month. Please browse next or previous months.'; ?>
    </div>
<?php else: ?>
<table class="mru-table">
    <thead>
        <tr>
            <th><?php echo esc_html($th_date); ?></th>
            <th><?php echo esc_html($th_day); ?></th>
            <th><?php echo esc_html($th_time); ?></th>
            <th><?php echo esc_html($th_nak); ?></th>
            <th><?php echo esc_html($th_tit); ?></th>
            <th><?php echo esc_html($th_moon); ?></th>
            <th><?php echo esc_html($th_sign); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($grouped[$dm] as $r):
        $nak = ($lang === 'hi' && !empty($r->nakshatra_hi)) ? $r->nakshatra_hi : $r->nakshatra;
        $tit = ($lang === 'hi' && !empty($r->tithi_hi)) ? $r->tithi_hi : $r->tithi;
        $moon = ($lang === 'hi' && !empty($r->moon_position_hi)) ? $r->moon_position_hi : $r->moon_position;
        $signs = ($lang === 'hi' && !empty($r->good_for_sun_signs_hi)) ? $r->good_for_sun_signs_hi : $r->good_for_sun_signs;
    ?>
        <tr>
            <td data-label="<?php echo esc_attr($th_date); ?>"><span class="mru-cell-val" style="font-weight:700; color:#8c2514;"><?php echo esc_html($fmt($r->gregorian_date)); ?></span></td>
            <td data-label="<?php echo esc_attr($th_day); ?>"><span class="mru-cell-val"><?php echo esc_html($r->day_name); ?></span></td>
            <td data-label="<?php echo esc_attr($th_time); ?>"><span class="mru-cell-val" style="font-weight:600;"><?php echo esc_html($r->muhurat_timing_ist); ?></span></td>
            <td data-label="<?php echo esc_attr($th_nak); ?>"><span class="mru-cell-val"><?php echo esc_html($nak); ?></span></td>
            <td data-label="<?php echo esc_attr($th_tit); ?>"><span class="mru-cell-val"><?php echo esc_html($tit); ?></span></td>
            <td data-label="<?php echo esc_attr($th_moon); ?>"><span class="mru-cell-val"><?php echo esc_html($moon); ?></span></td>
            <td data-label="<?php echo esc_attr($th_sign); ?>"><span class="mru-cell-val"><?php echo esc_html($signs); ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php endforeach; ?>

</div>

<?php if ($atts['show_cta'] !== 'no' && $cat_meta): ?>
<!-- HIGH-INTENT CONVERSION CTA -->
<div class="mru-cta-card">
    <div class="mru-cta-info">
        <h3>✨ <?php echo esc_html(($lang === 'hi') ? $cat_meta['report_title_hi'] : $cat_meta['report_title_en']); ?></h3>
        <p><?php echo esc_html(($lang === 'hi') ? $cat_meta['report_desc_hi'] : $cat_meta['report_desc_en']); ?></p>
    </div>
    <div>
        <a class="mru-cta-btn" href="<?php echo esc_url($cat_meta['report_link']); ?>">
            <?php echo esc_html(($lang === 'hi') ? $cat_meta['report_btn_hi'] : $cat_meta['report_btn_en']); ?>
        </a>
    </div>
</div>
<?php endif; ?>

</div>

<script>
// Month input change
document.getElementById("mruMonth") && document.getElementById("mruMonth").addEventListener("change", function() {
    let p = new URLSearchParams(window.location.search);
    p.set("ym", this.value);
    sessionStorage.setItem("mru_focus", "mru-results");
    window.location.search = p.toString();
});

// Language change
document.getElementById("mru-lang") && document.getElementById("mru-lang").addEventListener("change", function() {
    sessionStorage.setItem("mru_focus", "mru-results");
    document.getElementById("langForm").submit();
});

// Auto-scroll restoration
document.addEventListener("DOMContentLoaded", function() {
    if (sessionStorage.getItem("mru_focus")) {
        const el = document.getElementById("mru-results");
        if (el) el.scrollIntoView({behavior: "smooth"});
        sessionStorage.removeItem("mru_focus");
    }
});
</script>
<?php
        return ob_get_clean();
    }
}
