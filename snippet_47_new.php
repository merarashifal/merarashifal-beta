<?php
function mr_final_muhurat_viewer_theme_v2($atts = []) {
    global $wpdb;

    $atts = shortcode_atts([
        'occasion'   => '',
        'year'       => '',
        'ym'         => '',
        'lang'       => '',
        'show_icons' => 'yes',
        'show_cta'   => 'yes',
    ], $atts, 'muhurat_viewer');

    $table = $wpdb->prefix . 'muhurat';

    if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table)))) {
        return "<p style='color:red; text-align:center;'>Error: table {$table} not found.</p>";
    }

    // Occasion categories metadata
    $categories = [
        'Vivah Muhurat' => [
            'slug'      => 'vivah-muhurat',
            'label_en'  => 'Vivah Muhurat (Wedding)',
            'label_hi'  => 'विवाह मुहूर्त',
            'icon'      => 'https://merarashifal.com/wp-content/uploads/2025/11/Vivah-Mahurat.webp',
            'hub_url'   => '/vivah-muhurat-2027/',
            'report_title_en' => 'Personalized Kundali & Gun Matching Report',
            'report_title_hi' => 'वर-वधू कुंडली मिलान एवं गुण मिलान रिपोर्ट',
            'report_desc_en'  => 'Ensure marital harmony, Mangal Dosha check, and planetary compatibility before fixing the wedding date.',
            'report_desc_hi'  => 'विवाह की तिथि पक्की करने से पूर्व 36 गुण मिलान, मांगलिक दोष निवारण एवं संपूर्ण कुंडली विश्लेषण प्राप्त करें।',
            'report_link'     => '/reports/kundali-and-gun-matching-report/',
            'report_btn_en'   => 'Get Kundali Matching Report',
            'report_btn_hi'   => 'विवाह कुंडली मिलान रिपोर्ट प्राप्त करें',
        ],
        'Griha Pravesh' => [
            'slug'      => 'griha-pravesh',
            'label_en'  => 'Griha Pravesh (Housewarming)',
            'label_hi'  => 'गृह प्रवेश मुहूर्त',
            'icon'      => 'https://merarashifal.com/wp-content/uploads/2025/11/Griha-Pravesh.webp',
            'hub_url'   => '/griha-pravesh-muhurat-2027/',
            'report_title_en' => 'Personalized Griha Pravesh & Kundali Report',
            'report_title_hi' => 'व्यक्तिगत गृह प्रवेश एवं जन्म कुंडली रिपोर्ट',
            'report_desc_en'  => 'Select the most auspicious lagna and nakshatra according to the house owner’s Janam Kundali for lifelong peace and prosperity.',
            'report_desc_hi'  => 'घर के मुखिया की जन्म कुंडली अनुसार सबसे शुभ लग्न एवं नक्षत्र चुनकर सुख-समृद्धि एवं शांति सुनिश्चित करें।',
            'report_link'     => '/reports/janam-kundali-master-report/',
            'report_btn_en'   => 'Get Master Kundali Report',
            'report_btn_hi'   => 'मास्टर जन्म कुंडली रिपोर्ट प्राप्त करें',
        ],
        'Property Purchase' => [
            'slug'      => 'property-purchase',
            'label_en'  => 'Property Purchase (Land/Flat)',
            'label_hi'  => 'संपत्ति खरीद मुहूर्त',
            'icon'      => 'https://merarashifal.com/wp-content/uploads/2025/11/Property-Purchase.webp',
            'hub_url'   => '/vehicle-property-purchase-muhurat-2027/',
            'report_title_en' => 'Wealth & Property Astro-Consultation Report',
            'report_title_hi' => 'संपत्ति एवं धन-समृद्धि ज्योतिष परामर्श रिपोर्ट',
            'report_desc_en'  => 'Verify favorable 4th house and planetary yogas to prevent legal disputes and ensure lasting real estate prosperity.',
            'report_desc_hi'  => 'अपनी कुंडली के चतुर्थ भाव एवं भूमि योग का विश्लेषण कराकर संपत्ति निवेश को विवाद-मुक्त एवं फलदायी बनाएं।',
            'report_link'     => '/reports/janam-kundali-master-report/',
            'report_btn_en'   => 'Order Property Astro Report',
            'report_btn_hi'   => 'संपत्ति ज्योतिष रिपोर्ट प्राप्त करें',
        ],
        'Vehicle Purchase' => [
            'slug'      => 'vehicle-purchase',
            'label_en'  => 'Vehicle Purchase (Car/Bike)',
            'label_hi'  => 'वाहन खरीद मुहूर्त',
            'icon'      => 'https://merarashifal.com/wp-content/uploads/2025/11/Vehicle-Purchase.webp',
            'hub_url'   => '/vehicle-property-purchase-muhurat-2027/',
            'report_title_en' => 'Personalized Vehicle Muhurat & Kundali Report',
            'report_title_hi' => 'वाहन खरीद शुभ मुहूर्त एवं कुंडली विश्लेषण',
            'report_desc_en'  => 'Choose the safest, most fortunate delivery time for your vehicle based on planetary transits and your natal chart.',
            'report_desc_hi'  => 'वाहन डिलीवरी एवं पंजीकरण के लिए अपनी राशि अनुसार सबसे सुरक्षित एवं लाभकारी समय का चयन करें।',
            'report_link'     => '/reports/janam-kundali-master-report/',
            'report_btn_en'   => 'Get Kundali Analysis',
            'report_btn_hi'   => 'व्यक्तिगत कुंडली परामर्श प्राप्त करें',
        ],
    ];

    // Canonical occasion map for incoming slugs, filenames, and queries
    $occasion_map = [
        'vivah'             => 'Vivah Muhurat',
        'vivah-muhurat'     => 'Vivah Muhurat',
        'vivah-mahurat'     => 'Vivah Muhurat',
        'vivah muhurat'     => 'Vivah Muhurat',
        'vivah mahurat'     => 'Vivah Muhurat',
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

    // Determine Language
    $lang = 'en';
    if (!empty($_GET['lang'])) {
        $lang = sanitize_text_field($_GET['lang']);
    } elseif (!empty($atts['lang'])) {
        $lang = sanitize_text_field($atts['lang']);
    }
    if (!in_array($lang, ['en', 'hi'])) {
        $lang = 'en';
    }

    // Determine Occasion
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
            // Case insensitive direct match
            foreach (array_keys($categories) as $cat) {
                if (strcasecmp($cat, str_replace('-', ' ', $raw_occasion)) === 0) {
                    $selected_occasion = $cat;
                    break;
                }
            }
        }
    }

    // Determine Year / Month filter
    $requested_year = !empty($atts['year']) ? sanitize_text_field($atts['year']) : (isset($_GET['year']) ? sanitize_text_field($_GET['year']) : '');
    $requested_ym   = isset($_GET['ym']) ? sanitize_text_field($_GET['ym']) : (!empty($atts['ym']) ? sanitize_text_field($atts['ym']) : '');

    // URL Builder helper
    $build_url = function($override = []) {
        $params = ['occasion', 'lang', 'ym', 'year'];
        $base = [];
        foreach ($params as $p) {
            if (isset($_GET[$p])) {
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
    margin: 0 auto 30px;
    padding: 12px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2c2523;
}
.mru-heading-group {
    text-align: center;
    margin-bottom: 20px;
}
.mru-heading-group h2 {
    font-size: 26px;
    color: #8c2514;
    margin: 0 0 6px;
    font-weight: 700;
}
.mru-heading-group p {
    font-size: 15px;
    color: #6b584e;
    margin: 0;
}

/* 2027 HUBS QUICK BAR */
.mru-hub-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 22px;
}
.mru-hub-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: #fff8f0;
    border: 1px solid #e7cdb8;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: #8c2514;
    text-decoration: none;
    transition: all 0.2s ease;
}
.mru-hub-pill:hover, .mru-hub-pill.active {
    background: #8c2514;
    color: #fff;
    border-color: #8c2514;
    box-shadow: 0 2px 8px rgba(140, 37, 20, 0.25);
}

/* OCCASION CARDS */
.mru-icons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 820px) {
    .mru-icons {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}
@media (max-width: 460px) {
    .mru-icons {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
}
.mru-card {
    background: #FAF6F0;
    border: 2px solid #e7dfd5;
    border-radius: 12px;
    padding: 12px 10px;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    color: #4a3b32;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.mru-card:hover {
    transform: translateY(-2px);
    border-color: #8c2514;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.mru-card.active {
    background: #fff5ee;
    border-color: #8c2514;
    box-shadow: 0 0 0 2px #8c2514;
}
.mru-card img {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    object-fit: cover;
    margin-bottom: 8px;
    border: 1px solid #ebd9c8;
    background: #fff;
}
@media (max-width: 600px) {
    .mru-card img { width: 64px; height: 64px; }
}
.mru-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #2c2523;
    line-height: 1.3;
}
.mru-card-sub {
    font-size: 12px;
    color: #8c2514;
    margin-top: 3px;
    font-weight: 600;
}

/* CONTROL BAR */
.mru-controls {
    background: #FAF6F0;
    border: 1px solid #e7dfd5;
    padding: 12px 16px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.mru-controls label {
    font-weight: 700;
    color: #5a4633;
    font-size: 14px;
    margin-right: 6px;
}
.mru-select {
    padding: 7px 12px;
    border-radius: 6px;
    border: 1px solid #cfc8bc;
    background: #fff;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}
.mru-month-box {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.mru-month-field {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #cfc8bc;
    background: #fff;
    font-size: 14px;
    height: 36px;
    color: #333;
}
.mru-btn {
    padding: 7px 14px;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #cfc8bc;
    cursor: pointer;
    text-decoration: none;
    color: #5a4633;
    font-weight: 600;
    font-size: 13px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
}
.mru-btn:hover { background: #f0e8de; }
.mru-btn[disabled] { opacity: 0.4; pointer-events: none; }
.mru-btn-gold {
    background: #8c2514;
    color: #fff;
    border-color: #8c2514;
}
.mru-btn-gold:hover {
    background: #731e0f;
    color: #fff;
}

@media (max-width: 720px) {
    .mru-controls {
        flex-direction: column;
        align-items: stretch;
    }
    .mru-month-box {
        justify-content: space-between;
    }
    .mru-month-field {
        flex: 1 1 auto;
        min-width: 130px;
    }
}

/* RESULTS HEADINGS */
.mru-month-h2 {
    font-size: 20px;
    font-weight: 700;
    color: #8c2514;
    margin: 24px 0 12px;
    border-bottom: 2px solid #ebd9c8;
    padding-bottom: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.mru-count-badge {
    font-size: 12px;
    font-weight: 600;
    background: #e7dfd5;
    color: #5a4633;
    padding: 3px 8px;
    border-radius: 12px;
}

/* TABLE */
.mru-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    background: #FAF6F0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.mru-table th {
    background: #ebd9c8;
    padding: 11px 12px;
    font-weight: 700;
    text-align: left;
    color: #5a4633;
    font-size: 13px;
    border-bottom: 1px solid #dccebc;
}
.mru-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #ebd9c8;
    color: #2c2523;
    font-size: 14px;
    vertical-align: top;
}
.mru-table tr:nth-child(even) td {
    background: #f4ece1;
}

/* MOBILE TABLE STACK */
@media (max-width: 760px) {
    .mru-table { border: 0; background: transparent; box-shadow: none; }
    .mru-table thead { display: none; }
    .mru-table tbody, .mru-table tr { display: block; }
    .mru-table tr {
        border: 1px solid #e7dfd5;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 14px;
        background: #FAF6F0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .mru-table td {
        border: 0;
        border-bottom: 1px dashed #ebd9c8;
        padding: 7px 0;
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
        width: 45%;
        padding-right: 8px;
    }
    .mru-cell-val {
        width: 55%;
        text-align: right;
        color: #2c2523;
    }
    .mru-month-h2 { font-size: 18px; }
}

/* HIGH CONVERTING CTA CARD */
.mru-cta-card {
    background: linear-gradient(135deg, #fff9f2 0%, #faefe3 100%);
    border: 2px solid #e5c3a3;
    border-radius: 14px;
    padding: 20px;
    margin: 28px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    box-shadow: 0 4px 14px rgba(140, 37, 20, 0.08);
}
@media (max-width: 680px) {
    .mru-cta-card {
        flex-direction: column;
        text-align: center;
        padding: 16px;
    }
}
.mru-cta-info h3 {
    margin: 0 0 6px;
    font-size: 18px;
    color: #8c2514;
    font-weight: 700;
}
.mru-cta-info p {
    margin: 0;
    font-size: 14px;
    color: #5a4633;
    line-height: 1.45;
}
.mru-cta-btn {
    background: #8c2514;
    color: #fff !important;
    padding: 11px 22px;
    border-radius: 8px;
    font-size: 14px;
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
    box-shadow: 0 4px 10px rgba(140, 37, 20, 0.4);
}
</style>

<div class="mru-wrap">

<?php if ($atts['show_icons'] !== 'no'): ?>
    <!-- 2027 HUBS SHORTCUT PILLS -->
    <div class="mru-hub-pills">
        <span style="font-size:13px; font-weight:700; color:#5a4633; align-self:center;">
            <?php echo ($lang === 'hi') ? '⭐ 2027 शुभ मुहूर्त हब:' : '⭐ 2027 Muhurat Hubs:'; ?>
        </span>
        <a class="mru-hub-pill <?php echo ($selected_occasion === 'Vivah Muhurat') ? 'active' : ''; ?>" href="/vivah-muhurat-2027/">
            💍 <?php echo ($lang === 'hi') ? 'विवाह मुहूर्त 2027' : 'Vivah Muhurat 2027'; ?>
        </a>
        <a class="mru-hub-pill <?php echo ($selected_occasion === 'Griha Pravesh') ? 'active' : ''; ?>" href="/griha-pravesh-muhurat-2027/">
            🏡 <?php echo ($lang === 'hi') ? 'गृह प्रवेश 2027' : 'Griha Pravesh 2027'; ?>
        </a>
        <a class="mru-hub-pill <?php echo (in_array($selected_occasion, ['Vehicle Purchase', 'Property Purchase'])) ? 'active' : ''; ?>" href="/vehicle-property-purchase-muhurat-2027/">
            🚗 <?php echo ($lang === 'hi') ? 'वाहन एवं संपत्ति 2027' : 'Vehicle & Property 2027'; ?>
        </a>
    </div>

    <!-- OCCASION SELECTOR CARDS -->
    <div class="mru-icons">
    <?php foreach ($categories as $cat_name => $cat_meta):
        $is_active = ($selected_occasion === $cat_name);
        $card_url = $build_url(['occasion' => $cat_name, 'lang' => $lang]);
    ?>
        <a href="<?php echo esc_url($card_url); ?>" class="mru-card <?php echo $is_active ? 'active' : ''; ?>" data-occasion="<?php echo esc_attr($cat_name); ?>">
            <img src="<?php echo esc_url($cat_meta['icon']); ?>" alt="<?php echo esc_attr(($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en']); ?>">
            <div class="mru-card-title"><?php echo esc_html(($lang === 'hi') ? $cat_meta['label_hi'] : $cat_meta['label_en']); ?></div>
            <div class="mru-card-sub"><?php echo ($lang === 'hi') ? '2026-2027 शुभ तिथियां' : '2026-2027 Dates'; ?></div>
        </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// If no occasion selected, display gentle welcome prompt
if (!$selected_occasion) {
    $prompt_en = "Please select any Shubh Muhurat category above to view complete auspicious dates, nakshatra, and tithi for 2026 and 2027.";
    $prompt_hi = "कृपया ऊपर दिए गए विकल्पों में से किसी एक शुभ मुहूर्त का चयन करें और 2026-2027 की संपूर्ण शुभ तिथियां, नक्षत्र एवं समय देखें।";
    echo "<div style='text-align:center; padding:30px 15px; background:#FAF6F0; border-radius:10px; border:1px solid #ebd9c8; margin-top:10px;'>";
    echo "<p style='font-size:16px; color:#5a4633; margin:0; font-weight:600;'>" . (($lang === 'hi') ? $prompt_hi : $prompt_en) . "</p>";
    echo "</div></div>";
    return ob_get_clean();
}

// Fetch available months from DB
$months = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT DISTINCT DATE_FORMAT(gregorian_date,'%Y-%m')
         FROM {$table}
         WHERE occasion=%s AND lang_code=%s ORDER BY 1 ASC",
         $selected_occasion, $lang
    )
);

if (empty($months)) {
    // Fallback if specific lang has no rows
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

// Logical default month calculation
$current_ym = date('Y-m');
$default_ym = '';

if ($requested_year !== '') {
    // User or shortcode asked for a specific year (e.g. 2027)
    $year_prefix = $requested_year . '-';
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

// Fallback to requested month if valid
$selected_ym = ($requested_ym && in_array($requested_ym, $months)) ? $requested_ym : $default_ym;
$idx = array_search($selected_ym, $months);
if ($idx === false) $idx = 0;

$prev_ym = $idx > 0 ? $months[$idx - 1] : $months[0];
$next_ym = $idx < count($months) - 1 ? $months[$idx + 1] : end($months);

// Display 3 consecutive months
$display_months = array_slice($months, $idx, 3);
$range_start = $display_months[0] . "-01";
$range_end   = date("Y-m-t", strtotime(end($display_months) . "-01"));

// Query rows
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
    // If no rows in current language, query any language
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

// Localized headings
$cat_meta = isset($categories[$selected_occasion]) ? $categories[$selected_occasion] : null;
$occ_heading = ($cat_meta && $lang === 'hi') ? $cat_meta['label_hi'] : $selected_occasion;

$th_date = ($lang === 'hi') ? 'दिनांक' : 'Date';
$th_day  = ($lang === 'hi') ? 'दिन' : 'Day';
$th_time = ($lang === 'hi') ? 'शुभ मुहूर्त समय' : 'Muhurat Timing';
$th_nak  = ($lang === 'hi') ? 'नक्षत्र' : 'Nakshatra';
$th_tit  = ($lang === 'hi') ? 'तिथि' : 'Tithi';
$th_moon = ($lang === 'hi') ? 'चंद्र स्थिति' : 'Moon Position';
$th_sign = ($lang === 'hi') ? 'अनुकूल राशियाँ' : 'Good For Sun Signs';

?>

<!-- CONTROL BAR -->
<div class="mru-controls">
    <!-- Language Toggle -->
    <div>
        <label><?php echo ($lang === 'hi') ? 'भाषा (Language):' : 'Language:'; ?></label>
        <form id="langForm" method="get" style="display:inline;">
            <input type="hidden" name="occasion" value="<?php echo esc_attr($selected_occasion); ?>">
            <input type="hidden" name="ym" value="<?php echo esc_attr($selected_ym); ?>">
            <?php if (!empty($requested_year)): ?>
                <input type="hidden" name="year" value="<?php echo esc_attr($requested_year); ?>">
            <?php endif; ?>
            <select id="mru-lang" name="lang" class="mru-select">
                <option value="en" <?php selected($lang, 'en'); ?>>English</option>
                <option value="hi" <?php selected($lang, 'hi'); ?>>हिंदी</option>
            </select>
        </form>
    </div>

    <!-- Month Navigation -->
    <div class="mru-month-box">
        <input type="month"
               id="mruMonth"
               class="mru-month-field"
               value="<?php echo esc_attr($selected_ym); ?>"
               min="<?php echo esc_attr($months[0]); ?>"
               max="<?php echo esc_attr(end($months)); ?>">

        <a class="mru-btn" href="<?php echo $build_url(['ym' => $prev_ym]); ?>" <?php if ($idx === 0) echo "disabled"; ?>>
            &larr; <?php echo ($lang === 'hi') ? 'पिछला' : 'Prev'; ?>
        </a>
        <a class="mru-btn mru-btn-gold" href="<?php echo $build_url(['ym' => $default_ym]); ?>">
            <?php echo ($lang === 'hi') ? 'वर्तमान' : 'Current'; ?>
        </a>
        <a class="mru-btn" href="<?php echo $build_url(['ym' => $next_ym]); ?>" <?php if ($idx >= count($months) - 1) echo "disabled"; ?>>
            <?php echo ($lang === 'hi') ? 'अगला' : 'Next'; ?> &rarr;
        </a>
    </div>
</div>

<div id="mru-results">

<?php foreach ($display_months as $dm):
    $month_ts = strtotime($dm . "-01");
    $month_name_en = date("F Y", $month_ts);
    
    // Hindi month name translation
    $hi_months = [
        'January' => 'जनवरी', 'February' => 'फरवरी', 'March' => 'मार्च', 'April' => 'अप्रैल',
        'May' => 'मई', 'June' => 'जून', 'July' => 'जुलाई', 'August' => 'अगस्त',
        'September' => 'सितंबर', 'October' => 'अक्टूबर', 'November' => 'नवंबर', 'December' => 'दिसंबर'
    ];
    $m_en = date("F", $month_ts);
    $m_yr = date("Y", $month_ts);
    $month_display = ($lang === 'hi' && isset($hi_months[$m_en])) ? "{$hi_months[$m_en]} {$m_yr} – {$occ_heading}" : "{$month_name_en} – {$occ_heading}";
    $count = !empty($grouped[$dm]) ? count($grouped[$dm]) : 0;
?>
<h3 class="mru-month-h2">
    <span><?php echo esc_html($month_display); ?></span>
    <span class="mru-count-badge"><?php echo ($lang === 'hi') ? "कुल {$count} मुहूर्त" : "{$count} Auspicious Dates"; ?></span>
</h3>

<?php if (empty($grouped[$dm])): ?>
    <div style="background:#FAF6F0; padding:15px; border-radius:8px; border:1px dashed #ebd9c8; text-align:center; color:#6b584e; margin-bottom:20px;">
        <?php echo ($lang === 'hi') ? 'इस माह में कोई विशेष मुहूर्त उपलब्ध नहीं है।' : 'No auspicious muhurat entries recorded for this month.'; ?>
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
            <?php echo esc_html(($lang === 'hi') ? $cat_meta['report_btn_hi'] : $cat_meta['report_btn_en']); ?> &rarr;
        </a>
    </div>
</div>
<?php endif; ?>

</div>

<script>
// Month picker change
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

// Smooth scroll restoration
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
