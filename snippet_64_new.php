<?php
/*
====================================================
MERA RASHIFAL - PANCHANG ENGINE (V3 COMPACT DASHBOARD)
Clean, compact 2-column layout matching Horoscope aesthetic
- Fits in half the vertical height (zero clutter / no nested boxes)
- Sleek 2-panel spec-sheet presentation (Core Panchang & Muhurat/Celestial)
- Quick horizontal indicator ribbon for instant daily essentials
- Full Hindi localization parity & Yoast SEO integration
Shortcode: [dynamic_panchang]
====================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

/* ====================================================
1. REWRITE RULES
==================================================== */
add_action('init', function () {
    // Hindi Rules
    add_rewrite_rule('^panchang/hi/?$', 'index.php?pagename=panchang&pc_lang=hi', 'top');
    add_rewrite_rule('^panchang/([^/]+)/hi/?$', 'index.php?pagename=panchang&pc_any=$matches[1]&pc_lang=hi', 'top');
    add_rewrite_rule('^panchang/([^/]+)/([^/]+)/hi/?$', 'index.php?pagename=panchang&pc_any=$matches[1]&pc_date=$matches[2]&pc_lang=hi', 'top');

    // Default Rules
    add_rewrite_rule('^panchang/?$', 'index.php?pagename=panchang', 'top');
    add_rewrite_rule('^panchang/([^/]+)/?$', 'index.php?pagename=panchang&pc_any=$matches[1]', 'top');
    add_rewrite_rule('^panchang/([^/]+)/([^/]+)/?$', 'index.php?pagename=panchang&pc_any=$matches[1]&pc_date=$matches[2]', 'top');
}, 10);

/* ====================================================
2. QUERY VARS
==================================================== */
add_filter('query_vars', function ($vars) {
    $vars[] = 'pc_any';
    $vars[] = 'pc_date';
    $vars[] = 'pc_lang';
    return $vars;
});

/* ====================================================
3. HELPERS
==================================================== */
if (!function_exists('mr_pc_sections_fixed')) {
function mr_pc_sections_fixed() {
    return [
        'overview',
        'tithi',
        'abhijit-muhurat',
        'rahukaal',
        'nakshatra',
        'karana',
        'yoga',
        'sun-moon-timing',
        'year-samvat',
        'choghadiya-muhurat'
    ];
}
}

if (!function_exists('mr_pc_format_date_localized')) {
function mr_pc_format_date_localized($date_str, $is_hi = false) {
    $ts = strtotime($date_str);
    if (!$ts) return $date_str;

    if (!$is_hi) {
        return date_i18n('l, j F Y', $ts);
    }

    $days_hi = [
        'Sunday' => 'रविवार', 'Monday' => 'सोमवार', 'Tuesday' => 'मंगलवार',
        'Wednesday' => 'बुधवार', 'Thursday' => 'गुरुवार', 'Friday' => 'शुक्रवार', 'Saturday' => 'शनिवार'
    ];
    $months_hi = [
        'January' => 'जनवरी', 'February' => 'फ़रवरी', 'March' => 'मार्च',
        'April' => 'अप्रैल', 'May' => 'मई', 'June' => 'जून',
        'July' => 'जुलाई', 'August' => 'अगस्त', 'September' => 'सितम्बर',
        'October' => 'अक्टूबर', 'November' => 'नवम्बर', 'December' => 'दिसम्बर'
    ];

    $day_en = date('l', $ts);
    $month_en = date('F', $ts);
    $day_hi = $days_hi[$day_en] ?? $day_en;
    $month_hi = $months_hi[$month_en] ?? $month_en;
    $day_num = date('j', $ts);
    $year = date('Y', $ts);

    return "{$day_hi}, {$day_num} {$month_hi} {$year}";
}
}

if (!function_exists('mr_pc_get_section_meta')) {
function mr_pc_get_section_meta($section) {
    $meta = [
        'overview' => [
            'icon' => '📅',
            'en'   => 'Daily Panchang',
            'hi'   => 'दैनिक पंचांग',
            'desc_en' => 'Complete Vedic Panchang overview with Tithi, Nakshatra, Muhurat & planetary timings.',
            'desc_hi' => 'आज का सम्पूर्ण वैदिक पंचांग: तिथि, नक्षत्र, करण, योग एवं शुभ-अशुभ मुहूर्त।'
        ],
        'tithi' => [
            'icon' => '🌙',
            'en'   => 'Tithi & Paksha',
            'hi'   => 'तिथि एवं पक्ष',
            'desc_en' => 'Lunar day details, start/end timing, ruling deity, and religious significance.',
            'desc_hi' => 'वर्तमान चंद्र तिथि, पक्ष, प्रारंभ व समाप्ति समय, देवता एवं धार्मिक महत्व।'
        ],
        'abhijit-muhurat' => [
            'icon' => '🕰️',
            'en'   => 'Abhijit Muhurat',
            'hi'   => 'अभिजीत मुहूर्त',
            'desc_en' => 'The most auspicious time of the day for new beginnings and important ventures.',
            'desc_hi' => 'दिन का सर्वाधिक शुभ मुहूर्त जो सभी प्रकार के मांगलिक व नवीन कार्यों के लिए श्रेष्ठ है।'
        ],
        'rahukaal' => [
            'icon' => '⚠️',
            'en'   => 'Rahukaal & Inauspicious Times',
            'hi'   => 'राहुकाल एवं अशुभ समय',
            'desc_en' => 'Inauspicious hours including Rahukaal, Gulika, and Yamakanta to avoid for major tasks.',
            'desc_hi' => 'राहुकाल, गुलिक काल एवं यमकंटक समय, जिनमें महत्वपूर्ण कार्यों से बचना चाहिए।'
        ],
        'nakshatra' => [
            'icon' => '✨',
            'en'   => 'Nakshatra',
            'hi'   => 'नक्षत्र',
            'desc_en' => 'Vedic lunar constellation, ruling planet, presiding deity, and active duration.',
            'desc_hi' => 'दैनिक वैदिक नक्षत्र, स्वामी ग्रह, आराध्य देवता, समय एवं शुभ दिशा।'
        ],
        'karana' => [
            'icon' => '🌿',
            'en'   => 'Karana',
            'hi'   => 'करण',
            'desc_en' => 'Half lunar day division influencing actions, enterprise, and daily productivity.',
            'desc_hi' => 'तिथि का आधा भाग (करण), इसकी प्रकृति, स्वामी देवता और उपयुक्त कार्य।'
        ],
        'yoga' => [
            'icon' => '🕉️',
            'en'   => 'Yoga',
            'hi'   => 'योग',
            'desc_en' => 'Solar-lunar alignment combinations determining auspiciousness of activities.',
            'desc_hi' => 'सूर्य एवं चंद्र की विशिष्ट कोणीय दूरी से निर्मित शुभ-अशुभ योग।'
        ],
        'sun-moon-timing' => [
            'icon' => '☀️',
            'en'   => 'Sun & Moon Timings',
            'hi'   => 'सूर्य एवं चंद्र समय',
            'desc_en' => 'Precise Sunrise, Sunset, Moonrise, and Moonset calculations for daily rituals.',
            'desc_hi' => 'सूर्योदय, सूर्यास्त, चंद्रोदय एवं चंद्रास्त का सटीक दैनिक समय एवं राशि विवरण।'
        ],
        'year-samvat' => [
            'icon' => '📜',
            'en'   => 'Hindu Year & Samvat',
            'hi'   => 'संवत एवं काल गणना',
            'desc_en' => 'Traditional Hindu calendar eras: Vikram Samvat, Shaka Samvat, and Kali Era.',
            'desc_hi' => 'सनातन काल गणना: विक्रम संवत, शक संवत, कलि संवत एवं संवत्सर नाम।'
        ],
        'choghadiya-muhurat' => [
            'icon' => '🗓️',
            'en'   => 'Choghadiya Muhurat',
            'hi'   => 'चौघड़िया मुहूर्त',
            'desc_en' => 'Auspicious and inauspicious time divisions across day and night for journey and work.',
            'desc_hi' => 'दिन एवं रात का चौघड़िया: शुभ, लाभ, अमृत, चर, रोग, काल एवं उद्वेग समय।'
        ]
    ];
    return $meta[$section] ?? [
        'icon' => '📅',
        'en'   => ucwords(str_replace('-', ' ', $section)),
        'hi'   => ucwords(str_replace('-', ' ', $section)),
        'desc_en' => '',
        'desc_hi' => ''
    ];
}
}

/* ====================================================
4. MAIN SHORTCODE: [dynamic_panchang]
==================================================== */
add_shortcode('dynamic_panchang', function () {

    global $wpdb;

    $info = mr_pc_get_active_path_info();
    $section = $info['section'];
    $date    = $info['date'];
    $is_hi   = $info['is_hi'];
    $pc_lang = $is_hi ? 'hi' : 'en';

    $today = current_time('Y-m-d');
    $selected_date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : $today;

    $yesterday = gmdate('Y-m-d', strtotime($selected_date . ' -1 day'));
    $tomorrow  = gmdate('Y-m-d', strtotime($selected_date . ' +1 day'));

    $chog_min = '2025-01-01';
    $chog_max = '2026-12-31';

    if ($section === 'choghadiya-muhurat') {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM af_choghadiya_full_response WHERE choghadiya_date = %s AND language = %s LIMIT 1",
                $selected_date,
                $pc_lang
            )
        );
        if (!$row) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM af_choghadiya_full_response WHERE choghadiya_date = %s LIMIT 1",
                    $selected_date
                )
            );
        }
        $range = $wpdb->get_row("SELECT MIN(choghadiya_date) as min_d, MAX(choghadiya_date) as max_d FROM af_choghadiya_full_response");
        if ($range) {
            if (!empty($range->min_d)) $chog_min = $range->min_d;
            if (!empty($range->max_d)) $chog_max = $range->max_d;
        }
    } else {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM af_panchang_data WHERE api_date = %s AND language = %s LIMIT 1",
                $selected_date,
                $pc_lang
            )
        );
        if (!$row) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM af_panchang_data WHERE api_date = %s LIMIT 1",
                    $selected_date
                )
            );
        }
        if (!$row && empty($date)) {
            $latest = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM af_panchang_data WHERE language = %s ORDER BY api_date DESC LIMIT 1",
                    $pc_lang
                )
            );
            if ($latest) {
                $row = $latest;
                $selected_date = $latest->api_date;
                $yesterday = gmdate('Y-m-d', strtotime($selected_date . ' -1 day'));
                $tomorrow  = gmdate('Y-m-d', strtotime($selected_date . ' +1 day'));
            }
        }
    }

    $active_meta = mr_pc_get_section_meta($section);
    $active_title = $is_hi ? $active_meta['hi'] : $active_meta['en'];
    $active_desc  = $is_hi ? $active_meta['desc_hi'] : $active_meta['desc_en'];
    $formatted_date = mr_pc_format_date_localized($selected_date, $is_hi);

    // Language switch URLs
    $section_slug = ($section !== 'overview') ? $section . '/' : '';
    $date_slug    = $date ? $date . '/' : '';
    $en_url       = home_url('/panchang/' . $section_slug . $date_slug);
    $hi_url       = home_url('/panchang/' . $section_slug . $date_slug . 'hi/');

    ob_start();
?>

<style>
/* ====================================================
   MERA RASHIFAL - COMPACT PANCHANG DASHBOARD
   Sleek 2-column tabular presentation (Zero clutter)
==================================================== */
.mr-panchang-wrap {
    background-color: #FFFBEB !important;
    font-family: 'Noto Sans', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1E293B;
    border-radius: 14px;
    border: 1px solid #FDE68A;
    padding: 16px 20px;
    box-shadow: 0 4px 16px rgba(180, 83, 9, 0.04);
    margin: 0 auto 25px auto;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}

/* Header Area */
.mr-pc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding-bottom: 10px;
    margin-bottom: 12px;
    border-bottom: 1px dashed #FCD34D;
}
.mr-pc-title-area h1 {
    font-size: 22px !important;
    font-weight: 800 !important;
    color: #B45309 !important;
    margin: 0 !important;
    line-height: 1.25 !important;
}
.mr-pc-date-sub {
    font-size: 13.5px;
    font-weight: 600;
    color: #D97706;
    margin-top: 2px;
}
.mr-pc-back-link {
    font-size: 13px;
    font-weight: 700;
    color: #B45309 !important;
    text-decoration: none !important;
}
.mr-pc-back-link:hover { text-decoration: underline !important; }

/* Compact Controls Toolbar */
.mr-pc-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: #FEF3C7;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 6px 10px;
    margin-bottom: 12px;
}
.mr-pc-btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
}
.mr-pc-pill {
    padding: 5px 12px;
    background: #FFFFFF;
    color: #78350F !important;
    border: 1px solid #FDE68A;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none !important;
    transition: all 0.15s ease;
    cursor: pointer;
}
.mr-pc-pill:hover {
    background: #FEF3C7;
    color: #B45309 !important;
}
.mr-pc-pill.active {
    background: #B45309;
    color: #FFFFFF !important;
    border-color: #B45309;
}
.mr-pc-datepicker {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 6px;
    padding: 2px 6px;
}
.mr-pc-datepicker input[type="date"] {
    border: none;
    background: transparent;
    font-size: 12px;
    color: #1E293B;
    outline: none;
    padding: 3px 0;
}
.mr-pc-datepicker button {
    background: #B45309;
    color: #fff;
    border: none;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
}
.mr-pc-lang-box {
    display: inline-flex;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 6px;
    padding: 2px;
}
.mr-pc-lang-btn {
    border: none;
    background: transparent;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 700;
    color: #78350F;
    border-radius: 4px;
    text-decoration: none !important;
}
.mr-pc-lang-btn.active {
    background: #B45309;
    color: #FFFFFF !important;
}

/* Category Navigation Tabs */
.mr-pc-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #FCD34D;
}
.mr-pc-tab-link {
    padding: 4px 10px;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #78350F !important;
    text-decoration: none !important;
    transition: all 0.15s;
    white-space: nowrap;
}
.mr-pc-tab-link:hover {
    background: #FEF3C7;
    color: #B45309 !important;
}
.mr-pc-tab-link.active {
    background: #B45309;
    color: #FFFFFF !important;
    border-color: #B45309;
}

/* 4-Item Compact Quick Indicator Ribbon */
.mr-pc-quick-strip {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-left: 4px solid #F59E0B;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 14px;
}
@media (min-width: 768px) {
    .mr-pc-quick-strip {
        grid-template-columns: repeat(4, 1fr);
    }
}
.mr-pc-quick-item {
    font-size: 12.5px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mr-pc-quick-item strong {
    color: #78350F;
    font-weight: 700;
}

/* 2-Column Main Spec Grid (Half height of before) */
.mr-pc-main-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
@media (min-width: 768px) {
    .mr-pc-main-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.mr-pc-panel {
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(180, 83, 9, 0.03);
}
.mr-pc-panel-head {
    background: #FEF3C7;
    padding: 8px 14px;
    font-size: 14px;
    font-weight: 800;
    color: #78350F;
    border-bottom: 1px solid #FDE68A;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mr-pc-panel-head a {
    color: #B45309 !important;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none !important;
}
.mr-pc-panel-head a:hover { text-decoration: underline !important; }

/* Clean Spec-Sheet Rows */
.mr-pc-list-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 14px;
    border-bottom: 1px solid #FEF3C7;
    font-size: 13px;
    line-height: 1.4;
}
.mr-pc-list-row:nth-child(even) {
    background: #FFFDF7;
}
.mr-pc-list-row:last-child {
    border-bottom: none;
}
.mr-pc-list-label {
    color: #B45309;
    font-weight: 700;
    min-width: 110px;
    flex-shrink: 0;
}
.mr-pc-list-val {
    color: #1E293B;
    text-align: right;
    font-weight: 600;
    flex-grow: 1;
}
.mr-pc-list-val strong {
    color: #78350F;
}

/* Badges */
.mr-pc-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 5px;
    vertical-align: middle;
}
.mr-pc-badge-green { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
.mr-pc-badge-red { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
.mr-pc-badge-gold { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

/* Callout Note */
.mr-pc-note {
    background: #FFFBEB;
    border-top: 1px dashed #FDE68A;
    padding: 8px 14px;
    font-size: 12.5px;
    color: #78350F;
    line-height: 1.5;
}

/* Choghadiya Table Compact */
.mr-pc-chog-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 10px 14px;
    margin-top: 10px;
}
.mr-pc-table {
    width: 100%;
    border-collapse: collapse;
    background: #FFFFFF;
}
.mr-pc-table th {
    background: #FEF3C7;
    color: #78350F;
    font-weight: 800;
    font-size: 12.5px;
    padding: 7px 10px;
    text-align: center;
    border-bottom: 1px solid #FDE68A;
}
.mr-pc-table td {
    padding: 7px 10px;
    font-size: 12.5px;
    border-bottom: 1px solid #FEF3C7;
    text-align: center;
    color: #1E293B;
}
.mr-pc-table tr:hover td { background: #FFFBEB; }
.mr-pc-table tr:last-child td { border-bottom: none; }

@media (max-width: 600px) {
    .mr-pc-header { flex-direction: column; align-items: flex-start; }
    .mr-pc-toolbar { flex-direction: column; align-items: stretch; }
    .mr-pc-list-row { flex-direction: column; align-items: flex-start; gap: 2px; }
    .mr-pc-list-val { text-align: left; }
    .mr-pc-quick-strip { grid-template-columns: 1fr; }
}
</style>

<div class="mr-panchang-wrap">

    <!-- Top Header -->
    <div class="mr-pc-header">
        <div class="mr-pc-title-area">
            <h1><?php echo $active_meta['icon']; ?> <?php echo esc_html($active_title); ?></h1>
            <div class="mr-pc-date-sub">
                📅 <?php echo esc_html($formatted_date); ?>
                <?php if (!empty($row->masa_paksha)): ?>
                    • <?php echo esc_html($row->masa_paksha); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($section !== 'overview'): ?>
            <div>
                <a href="<?php echo home_url('/panchang/' . ($is_hi ? 'hi/' : '')); ?>" class="mr-pc-back-link">
                    ← <?php echo $is_hi ? 'सम्पूर्ण पंचांग' : 'Full Panchang'; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Controls Toolbar -->
    <div class="mr-pc-toolbar">
        <div class="mr-pc-btn-group">
            <?php 
            $y_url = home_url('/panchang/' . $section_slug . $yesterday . '/' . ($is_hi ? 'hi/' : ''));
            $t_url = home_url('/panchang/' . $section_slug . ($is_hi ? 'hi/' : ''));
            $tm_url = home_url('/panchang/' . $section_slug . $tomorrow . '/' . ($is_hi ? 'hi/' : ''));
            $is_today_active = ($selected_date === $today && empty($date));
            ?>
            <a class="mr-pc-pill" id="mr-pc-btn-yesterday" href="<?php echo esc_url($y_url); ?>">
                <?php echo $is_hi ? 'कल (बीता)' : 'Yesterday'; ?>
            </a>
            <a class="mr-pc-pill <?php echo $is_today_active ? 'active' : ''; ?>" id="mr-pc-btn-today" href="<?php echo esc_url($t_url); ?>">
                <?php echo $is_hi ? 'आज' : 'Today'; ?>
            </a>
            <a class="mr-pc-pill" id="mr-pc-btn-tomorrow" href="<?php echo esc_url($tm_url); ?>">
                <?php echo $is_hi ? 'कल (आने वाला)' : 'Tomorrow'; ?>
            </a>
        </div>

        <!-- Date Picker -->
        <div class="mr-pc-datepicker">
            <input type="date" id="pcDate" value="<?php echo esc_attr($selected_date); ?>"
                <?php if($section === 'choghadiya-muhurat'): ?>
                    min="<?php echo esc_attr($chog_min); ?>" max="<?php echo esc_attr($chog_max); ?>"
                <?php endif; ?>
            >
            <button type="button" onclick="mrPcDate('<?php echo esc_js($section); ?>')">
                <?php echo $is_hi ? 'देखें' : 'Go'; ?>
            </button>
        </div>

        <!-- Language Switcher -->
        <div class="mr-pc-lang-box">
            <a href="<?php echo esc_url($en_url); ?>" class="mr-pc-lang-btn <?php echo !$is_hi ? 'active' : ''; ?>">EN</a>
            <a href="<?php echo esc_url($hi_url); ?>" class="mr-pc-lang-btn <?php echo $is_hi ? 'active' : ''; ?>">हिन्दी</a>
        </div>
    </div>

    <!-- Category Tabs Navigation -->
    <div class="mr-pc-tabs">
        <?php 
        $all_sections = mr_pc_sections_fixed();
        foreach ($all_sections as $sec_key):
            $s_meta = mr_pc_get_section_meta($sec_key);
            $s_title = $is_hi ? $s_meta['hi'] : $s_meta['en'];
            $s_active = ($sec_key === $section) ? 'active' : '';
            $s_path = ($sec_key === 'overview') ? '' : $sec_key . '/';
            $s_date_part = ($selected_date !== $today) ? $selected_date . '/' : '';
            $s_url = home_url('/panchang/' . $s_path . $s_date_part . ($is_hi ? 'hi/' : ''));
        ?>
            <a href="<?php echo esc_url($s_url); ?>" class="mr-pc-tab-link <?php echo $s_active; ?>">
                <?php echo $s_meta['icon']; ?> <?php echo esc_html($s_title); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!$row): ?>
        <div style="background:#fff; border:1px solid #FDE68A; border-radius:8px; padding:20px; text-align:center;">
            <p style="margin:0; font-weight:700; color:#B45309;">
                <?php echo $is_hi ? 'इस तारीख के लिए पंचांग डेटा उपलब्ध नहीं है।' : 'Panchang data is not available for this date.'; ?>
            </p>
        </div>
    <?php else: ?>

        <?php if ($section === 'overview'): ?>

            <!-- 1-Line Quick Indicator Strip -->
            <div class="mr-pc-quick-strip">
                <div class="mr-pc-quick-item">
                    <span>🌅</span>
                    <div><strong><?php echo $is_hi ? 'सूर्योदय/अस्त:' : 'Sun Rise/Set:'; ?></strong> <?php echo esc_html($row->sun_rise ?? '—'); ?> / <?php echo esc_html($row->sun_set ?? '—'); ?></div>
                </div>
                <div class="mr-pc-quick-item">
                    <span>🌙</span>
                    <div><strong><?php echo $is_hi ? 'चंद्रोदय/अस्त:' : 'Moon Rise/Set:'; ?></strong> <?php echo esc_html($row->moon_rise ?? '—'); ?> / <?php echo esc_html($row->moon_set ?? '—'); ?></div>
                </div>
                <div class="mr-pc-quick-item">
                    <span>🟢</span>
                    <div><strong><?php echo $is_hi ? 'अभिजीत:' : 'Abhijit:'; ?></strong> <span class="mr-pc-badge mr-pc-badge-green"><?php echo esc_html(($row->abhijit_muhurta_start ?? '—') . ' - ' . ($row->abhijit_muhurta_end ?? '—')); ?></span></div>
                </div>
                <div class="mr-pc-quick-item">
                    <span>🔴</span>
                    <div><strong><?php echo $is_hi ? 'राहुकाल:' : 'Rahu Kaal:'; ?></strong> <span class="mr-pc-badge mr-pc-badge-red"><?php echo esc_html($row->rahukaal ?? '—'); ?></span></div>
                </div>
            </div>

            <!-- 2-Panel Main Spec Layout (Saves ~60% height vs multiple boxes) -->
            <div class="mr-pc-main-grid">

                <!-- Panel 1: Core Panchang Elements (पंचांग के मुख्य अंग) -->
                <div class="mr-pc-panel">
                    <div class="mr-pc-panel-head">
                        <span>📜 <?php echo $is_hi ? 'पंचांग के मुख्य 5 अंग' : 'Core Panchang Elements'; ?></span>
                        <a href="<?php echo esc_url(home_url('/panchang/tithi/' . ($selected_date !== $today ? $selected_date.'/' : '') . ($is_hi ? 'hi/' : ''))); ?>">
                            <?php echo $is_hi ? 'विस्तार से →' : 'Details →'; ?>
                        </a>
                    </div>
                    <div class="mr-pc-panel-body">
                        <!-- Tithi -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🌙 <?php echo $is_hi ? 'तिथि (Tithi)' : 'Tithi'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->tithi_name ?? '—'); ?></strong>
                                <?php if (!empty($row->tithi_type)): ?>(<?php echo esc_html($row->tithi_type); ?>)<?php endif; ?>
                                <?php if (!empty($row->tithi_end)): ?> • <small><?php echo esc_html($row->tithi_end); ?> तक</small><?php endif; ?>
                            </span>
                        </div>
                        <!-- Nakshatra -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">✨ <?php echo $is_hi ? 'नक्षत्र (Nakshatra)' : 'Nakshatra'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->nakshatra_name ?? '—'); ?></strong>
                                <?php if (!empty($row->nakshatra_lord)): ?>(स्वामी: <?php echo esc_html($row->nakshatra_lord); ?>)<?php endif; ?>
                                <?php if (!empty($row->nakshatra_end)): ?> • <small><?php echo esc_html($row->nakshatra_end); ?> तक</small><?php endif; ?>
                            </span>
                        </div>
                        <!-- Karana -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🌿 <?php echo $is_hi ? 'करण (Karana)' : 'Karana'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->karana_name ?? '—'); ?></strong>
                                <?php if (!empty($row->karana_end)): ?>(<?php echo esc_html($row->karana_end); ?> तक)<?php endif; ?>
                                <?php if (!empty($row->karana_next)): ?>, आगामी: <?php echo esc_html($row->karana_next); ?><?php endif; ?>
                            </span>
                        </div>
                        <!-- Yoga -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🕉️ <?php echo $is_hi ? 'योग (Yoga)' : 'Yoga'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->yoga_name ?? '—'); ?></strong>
                                <?php if (!empty($row->yoga_end)): ?> • <small><?php echo esc_html($row->yoga_end); ?> तक</small><?php endif; ?>
                            </span>
                        </div>
                        <!-- Vara -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">📅 <?php echo $is_hi ? 'वार (Day)' : 'Day / Vara'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->vaara ?? $row->day_name ?? '—'); ?></strong>
                            </span>
                        </div>
                        <!-- Samvat -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🏛️ <?php echo $is_hi ? 'संवत (Samvat)' : 'Samvat'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong>विक्रम: <?php echo esc_html($row->year_vikram ?? '—'); ?></strong>
                                <?php if (!empty($row->year_vikram_samvaat_name)): ?>(<?php echo esc_html($row->year_vikram_samvaat_name); ?>)<?php endif; ?>
                                • शक: <?php echo esc_html($row->year_saka ?? '—'); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($row->tithi_meaning)): ?>
                        <div class="mr-pc-note">
                            💡 <strong><?php echo $is_hi ? 'आज का विशेष महत्व:' : 'Today\'s Significance:'; ?></strong> <?php echo esc_html($row->tithi_meaning); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Panel 2: Muhurat & Celestial Status (शुभ-अशुभ मुहूर्त एवं ग्रह स्थिति) -->
                <div class="mr-pc-panel">
                    <div class="mr-pc-panel-head">
                        <span>🕰️ <?php echo $is_hi ? 'शुभ-अशुभ मुहूर्त एवं ग्रह स्थिति' : 'Muhurat & Planetary Status'; ?></span>
                        <a href="<?php echo esc_url(home_url('/panchang/abhijit-muhurat/' . ($selected_date !== $today ? $selected_date.'/' : '') . ($is_hi ? 'hi/' : ''))); ?>">
                            <?php echo $is_hi ? 'मुहूर्त विवरण →' : 'All Muhurats →'; ?>
                        </a>
                    </div>
                    <div class="mr-pc-panel-body">
                        <!-- Abhijit Muhurat -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🟢 <?php echo $is_hi ? 'अभिजीत मुहूर्त' : 'Abhijit Muhurat'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html(($row->abhijit_muhurta_start ?? '—') . ' - ' . ($row->abhijit_muhurta_end ?? '—')); ?></strong>
                                <span class="mr-pc-badge mr-pc-badge-green"><?php echo $is_hi ? 'सर्वश्रेष्ठ शुभ' : 'Auspicious'; ?></span>
                            </span>
                        </div>
                        <!-- Rahu Kaal -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🔴 <?php echo $is_hi ? 'राहुकाल (वर्जित)' : 'Rahu Kaal'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->rahukaal ?? '—'); ?></strong>
                                <span class="mr-pc-badge mr-pc-badge-red"><?php echo $is_hi ? 'अशुभ समय' : 'Inauspicious'; ?></span>
                            </span>
                        </div>
                        <!-- Gulika & Yamakanta -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">⏳ <?php echo $is_hi ? 'गुलिक / यमकंटक' : 'Gulika / Yama'; ?></span>
                            <span class="mr-pc-list-val">
                                गुलिक: <?php echo esc_html($row->gulika ?? '—'); ?> • यम: <?php echo esc_html($row->yamakanta ?? '—'); ?>
                            </span>
                        </div>
                        <!-- Disha Shool -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🧭 <?php echo $is_hi ? 'दिशा शूल' : 'Disha Shool'; ?></span>
                            <span class="mr-pc-list-val">
                                <strong><?php echo esc_html($row->disha_shool ?? '—'); ?></strong>
                                <?php if (!empty($row->nakshatra_auspicious_disha)): ?>
                                    (शुभ दिशा: <?php echo esc_html(trim($row->nakshatra_auspicious_disha, '[]"\' ')); ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                        <!-- Sun & Moon Zodiac -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">☀️🌙 <?php echo $is_hi ? 'सूर्य / चंद्र राशि' : 'Sun / Moon Sign'; ?></span>
                            <span class="mr-pc-list-val">
                                सूर्य राशि: <strong><?php echo esc_html($row->sun_zodiac ?? '—'); ?></strong> • चंद्र राशि: <strong><?php echo esc_html($row->rasi_name ?? '—'); ?></strong>
                            </span>
                        </div>
                        <!-- Full/New Moon -->
                        <div class="mr-pc-list-row">
                            <span class="mr-pc-list-label">🌕🌑 <?php echo $is_hi ? 'पूर्णिमा / अमावस्या' : 'Full / New Moon'; ?></span>
                            <span class="mr-pc-list-val">
                                पूर्णिमा: <?php echo esc_html($row->next_full_moon ?? '—'); ?> • अमावस्या: <?php echo esc_html($row->next_new_moon ?? '—'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="mr-pc-note" style="background:#FFF9F9; border-top-color:#FECDD3; color:#9F1239;">
                        ⚠️ <strong><?php echo $is_hi ? 'ज्योतिषीय नियम:' : 'Notice:'; ?></strong> <?php echo $is_hi ? 'राहुकाल में शुभ कार्य या यात्रा आरंभ न करें। नए कार्यों के लिए अभिजीत मुहूर्त का उपयोग करें।' : 'Avoid starting new ventures or travel during Rahukaal. Use Abhijit Muhurat for success.'; ?>
                    </div>
                </div>

            </div>

            <!-- Compact Choghadiya Banner Strip -->
            <div class="mr-pc-chog-strip">
                <div>
                    <strong style="color:#78350F; font-size:14px;">🗓️ <?php echo $is_hi ? 'आज का चौघड़िया मुहूर्त (दिन व रात)' : 'Today\'s Day & Night Choghadiya Timings'; ?></strong>
                    <div style="font-size:12px; color:#475569; margin-top:2px;">
                        <?php echo $is_hi ? 'शुभ, लाभ, अमृत, चर, रोग, काल और उद्वेग मुहूर्त समय सारणी' : 'Shubh, Labh, Amrit, Char, Rog, Kaal and Udveg time table'; ?>
                    </div>
                </div>
                <a href="<?php echo esc_url(home_url('/panchang/choghadiya-muhurat/' . ($selected_date !== $today ? $selected_date.'/' : '') . ($is_hi ? 'hi/' : ''))); ?>" class="mr-pc-pill active">
                    <?php echo $is_hi ? 'चौघड़िया तालिका देखें &rarr;' : 'View Choghadiya Table &rarr;'; ?>
                </a>
            </div>

        <?php elseif ($section === 'choghadiya-muhurat'): ?>

            <!-- COMPACT CHOGHADIYA TABLE VIEW -->
            <div class="mr-pc-panel" style="margin-bottom:15px;">
                <div class="mr-pc-panel-head">
                    <span>☀️ <?php echo $is_hi ? 'दिन का चौघड़िया (Day Choghadiya)' : 'Day Choghadiya Timings'; ?></span>
                    <span style="font-size:12.5px; color:#B45309;"><?php echo esc_html($formatted_date); ?></span>
                </div>
                <table class="mr-pc-table">
                    <thead>
                        <tr>
                            <th><?php echo $is_hi ? 'मुहूर्त' : 'Muhurat'; ?></th>
                            <th><?php echo $is_hi ? 'समय (प्रारंभ - समाप्ति)' : 'Time (Start - End)'; ?></th>
                            <th><?php echo $is_hi ? 'प्रकृति' : 'Quality'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i=1; $i<=8; $i++): 
                            $s_key="day_{$i}_start"; $e_key="day_{$i}_end"; $m_key="day_{$i}_muhurat"; $t_key="day_{$i}_type";
                            $s_val = $row->$s_key ?? ''; $e_val = $row->$e_key ?? ''; $m_val = $row->$m_key ?? ''; $t_val = trim($row->$t_key ?? '');
                            if (empty($s_val)) continue;
                            $b_class = 'mr-pc-badge-gold';
                            if (in_array($t_val, ['शुभ', 'Shubh', 'Good', 'अमृत', 'लाभ'])) $b_class = 'mr-pc-badge-green';
                            elseif (in_array($t_val, ['अशुभ', 'Ashubh', 'Bad', 'रोग', 'काल', 'उद्वेग'])) $b_class = 'mr-pc-badge-red';
                        ?>
                        <tr>
                            <td><strong style="color:#78350F; font-size:13.5px;"><?php echo esc_html($m_val); ?></strong></td>
                            <td><?php echo esc_html($s_val); ?> — <?php echo esc_html($e_val); ?></td>
                            <td><span class="mr-pc-badge <?php echo $b_class; ?>"><?php echo esc_html($t_val); ?></span></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="mr-pc-panel">
                <div class="mr-pc-panel-head">
                    <span>🌙 <?php echo $is_hi ? 'रात का चौघड़िया (Night Choghadiya)' : 'Night Choghadiya Timings'; ?></span>
                    <span style="font-size:12.5px; color:#B45309;"><?php echo esc_html($formatted_date); ?></span>
                </div>
                <table class="mr-pc-table">
                    <thead>
                        <tr>
                            <th><?php echo $is_hi ? 'मुहूर्त' : 'Muhurat'; ?></th>
                            <th><?php echo $is_hi ? 'समय (प्रारंभ - समाप्ति)' : 'Time (Start - End)'; ?></th>
                            <th><?php echo $is_hi ? 'प्रकृति' : 'Quality'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i=1; $i<=8; $i++): 
                            $s_key="night_{$i}_start"; $e_key="night_{$i}_end"; $m_key="night_{$i}_muhurat"; $t_key="night_{$i}_type";
                            $s_val = $row->$s_key ?? ''; $e_val = $row->$e_key ?? ''; $m_val = $row->$m_key ?? ''; $t_val = trim($row->$t_key ?? '');
                            if (empty($s_val)) continue;
                            $b_class = 'mr-pc-badge-gold';
                            if (in_array($t_val, ['शुभ', 'Shubh', 'Good', 'अमृत', 'लाभ'])) $b_class = 'mr-pc-badge-green';
                            elseif (in_array($t_val, ['अशुभ', 'Ashubh', 'Bad', 'रोग', 'काल', 'उद्वेग'])) $b_class = 'mr-pc-badge-red';
                        ?>
                        <tr>
                            <td><strong style="color:#78350F; font-size:13.5px;"><?php echo esc_html($m_val); ?></strong></td>
                            <td><?php echo esc_html($s_val); ?> — <?php echo esc_html($e_val); ?></td>
                            <td><span class="mr-pc-badge <?php echo $b_class; ?>"><?php echo esc_html($t_val); ?></span></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <!-- SUB-SECTION FOCUSED COMPACT VIEW -->
            <div class="mr-pc-panel">
                <div class="mr-pc-panel-head">
                    <span><?php echo $active_meta['icon']; ?> <?php echo esc_html($active_title); ?></span>
                    <span style="font-size:12.5px; color:#B45309;"><?php echo esc_html($formatted_date); ?></span>
                </div>
                <div class="mr-pc-panel-body">
                    <?php switch ($section):

                        case 'tithi': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'तिथि' : 'Tithi'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->tithi_name ?? '—'); ?></strong></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'पक्ष' : 'Paksha'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_type ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'तिथि संख्या' : 'Number'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_number ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'देवता' : 'Deity'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_diety ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'प्रारंभ - समाप्ति' : 'Start - End'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_start ?? '—'); ?> से <?php echo esc_html($row->tithi_end ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'विशेष' : 'Special'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_special ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'आगामी तिथि' : 'Next Tithi'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->tithi_next ?? '—'); ?></span></div>
                            <?php if (!empty($row->tithi_meaning)): ?>
                                <div class="mr-pc-note">💡 <strong><?php echo $is_hi ? 'महत्व:' : 'Significance:'; ?></strong> <?php echo esc_html($row->tithi_meaning); ?></div>
                            <?php endif; ?>
                        <?php break; ?>

                        <?php case 'rahukaal': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'राहुकाल समय' : 'Rahukaal'; ?></span><span class="mr-pc-list-val"><strong style="color:#DC2626;"><?php echo esc_html($row->rahukaal ?? '—'); ?></strong> <span class="mr-pc-badge mr-pc-badge-red"><?php echo $is_hi ? 'अशुभ' : 'Inauspicious'; ?></span></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'गुलिक काल' : 'Gulika'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->gulika ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'यमकंटक' : 'Yamakanta'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->yamakanta ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'वार (दिन)' : 'Vara'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->vaara ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'दिशा शूल' : 'Disha Shool'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->disha_shool ?? '—'); ?></span></div>
                            <div class="mr-pc-note" style="background:#FFF9F9; border-top-color:#FECDD3; color:#9F1239;">🚫 <strong><?php echo $is_hi ? 'परामर्श:' : 'Advisory:'; ?></strong> <?php echo $is_hi ? 'राहुकाल में नवीन खरीदारी, अनुबंध या यात्रा प्रारंभ करने से बचें।' : 'Avoid starting important tasks or travels during Rahukaal.'; ?></div>
                        <?php break; ?>

                        <?php case 'abhijit-muhurat': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'अभिजीत मुहूर्त' : 'Abhijit Muhurat'; ?></span><span class="mr-pc-list-val"><strong style="color:#16A34A;"><?php echo esc_html(($row->abhijit_muhurta_start ?? '') . ' - ' . ($row->abhijit_muhurta_end ?? '')); ?></strong> <span class="mr-pc-badge mr-pc-badge-green"><?php echo $is_hi ? 'सर्वोत्तम शुभ' : 'Auspicious'; ?></span></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'वार (दिन)' : 'Vara'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->vaara ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'दिशा शूल' : 'Disha Shool'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->disha_shool ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'अगली पूर्णिमा' : 'Full Moon'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->next_full_moon ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'अगली अमावस्या' : 'New Moon'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->next_new_moon ?? '—'); ?></span></div>
                            <div class="mr-pc-note" style="background:#F0FDF4; border-top-color:#86EFAC; color:#166534;">✨ <strong><?php echo $is_hi ? 'शुभ फल:' : 'Auspicious Window:'; ?></strong> <?php echo $is_hi ? 'अभिजीत मुहूर्त भगवान विष्णु का प्रिय समय है, यह सभी शुभ कार्यों के लिए उत्तम है।' : 'Abhijit Muhurat is universally auspicious for commerce, travel, and new starts.'; ?></div>
                        <?php break; ?>

                        <?php case 'nakshatra': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'नक्षत्र' : 'Nakshatra'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->nakshatra_name ?? '—'); ?></strong> (सं. <?php echo esc_html($row->nakshatra_number ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'स्वामी ग्रह' : 'Lord'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->nakshatra_lord ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'देवता' : 'Deity'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->nakshatra_diety ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'प्रारंभ - समाप्ति' : 'Start - End'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->nakshatra_start ?? '—'); ?> से <?php echo esc_html($row->nakshatra_end ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'शुभ दिशा' : 'Disha'; ?></span><span class="mr-pc-list-val"><?php echo esc_html(trim($row->nakshatra_auspicious_disha ?? '', '[]"\' ')); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'आगामी नक्षत्र' : 'Next'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->nakshatra_next ?? '—'); ?></span></div>
                            <?php if (!empty($row->nakshatra_summary) || !empty($row->nakshatra_meaning)): ?>
                                <div class="mr-pc-note">🌟 <strong><?php echo $is_hi ? 'नक्षत्र फल:' : 'Insights:'; ?></strong> <?php echo esc_html($row->nakshatra_summary ?? $row->nakshatra_meaning); ?></div>
                            <?php endif; ?>
                        <?php break; ?>

                        <?php case 'karana': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'करण' : 'Karana'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->karana_name ?? '—'); ?></strong> (सं. <?php echo esc_html($row->karana_number ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'प्रकार / स्वभाव' : 'Type'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->karana_type ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'स्वामी ग्रह / देवता' : 'Lord / Deity'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->karana_lord ?? '—'); ?> / <?php echo esc_html($row->karana_diety ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'प्रारंभ - समाप्ति' : 'Start - End'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->karana_start ?? '—'); ?> से <?php echo esc_html($row->karana_end ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'आगामी करण' : 'Next'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->karana_next ?? '—'); ?></span></div>
                        <?php break; ?>

                        <?php case 'yoga': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'योग' : 'Yoga'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->yoga_name ?? '—'); ?></strong> (सं. <?php echo esc_html($row->yoga_number ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'प्रारंभ - समाप्ति' : 'Start - End'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->yoga_start ?? '—'); ?> से <?php echo esc_html($row->yoga_end ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'आगामी योग' : 'Next'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->yoga_next ?? '—'); ?></span></div>
                            <?php if (!empty($row->yoga_meaning)): ?>
                                <div class="mr-pc-note">🕉️ <strong><?php echo $is_hi ? 'योग फल:' : 'Significance:'; ?></strong> <?php echo esc_html($row->yoga_meaning); ?></div>
                            <?php endif; ?>
                        <?php break; ?>

                        <?php case 'sun-moon-timing': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'सूर्योदय / सूर्यास्त' : 'Sunrise / Sunset'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->sun_rise ?? '—'); ?> / <?php echo esc_html($row->sun_set ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'चंद्रोदय / चंद्रास्त' : 'Moonrise / Moonset'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->moon_rise ?? '—'); ?> / <?php echo esc_html($row->moon_set ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'सूर्य राशि / नक्षत्र' : 'Sun Zodiac'; ?></span><span class="mr-pc-list-val">राशि: <?php echo esc_html($row->sun_zodiac ?? '—'); ?> • नक्षत्र: <?php echo esc_html($row->sun_nakshatra ?? '—'); ?></span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'चंद्र राशि' : 'Moon Sign'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->rasi_name ?? '—'); ?></strong></span></div>
                        <?php break; ?>

                        <?php case 'year-samvat': ?>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'विक्रम संवत' : 'Vikram Samvat'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->year_vikram ?? '—'); ?></strong> (नाम: <?php echo esc_html($row->year_vikram_samvaat_name ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'शक संवत' : 'Shaka Samvat'; ?></span><span class="mr-pc-list-val"><strong><?php echo esc_html($row->year_saka ?? '—'); ?></strong> (नाम: <?php echo esc_html($row->year_saka_samvaat_name ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'कलि संवत' : 'Kali Samvat'; ?></span><span class="mr-pc-list-val"><?php echo esc_html($row->year_kali ?? '—'); ?> (नाम: <?php echo esc_html($row->year_kali_samvaat_name ?? '—'); ?>)</span></div>
                            <div class="mr-pc-list-row"><span class="mr-pc-list-label"><?php echo $is_hi ? 'अयन / ऋतु' : 'Ayana / Ritu'; ?></span><span class="mr-pc-list-val"><?php echo esc_html(($row->masa_ayana ?? '') . ' / ' . ($row->masa_ritu ?? '')); ?></span></div>
                        <?php break; ?>

                    <?php endswitch; ?>
                </div>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
function mrPcDate(section){
    var input = document.getElementById('pcDate');
    if(!input || !input.value){ return; }

    var d = input.value;
    var langSuffix = '<?php echo $is_hi ? "hi/" : ""; ?>';

    if(section === 'overview'){
        window.location = '/panchang/' + d + (langSuffix ? '/' + langSuffix : '');
    }else{
        window.location = '/panchang/' + section + '/' + d + (langSuffix ? '/' + langSuffix : '');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var d = new Date();
    var localYear = d.getFullYear();
    var localMonth = String(d.getMonth() + 1).padStart(2, '0');
    var localDay = String(d.getDate()).padStart(2, '0');
    var localDate = localYear + '-' + localMonth + '-' + localDay;
    
    var langSuffix = '<?php echo $is_hi ? "hi/" : ""; ?>';
    var basePath = "/panchang/<?php echo ($section !== 'overview') ? esc_js($section) . '/' : ''; ?>";
    
    var btnYesterday = document.getElementById("mr-pc-btn-yesterday");
    var btnToday = document.getElementById("mr-pc-btn-today");
    var btnTomorrow = document.getElementById("mr-pc-btn-tomorrow");
    
    if (btnToday) {
        btnToday.href = basePath + (langSuffix ? langSuffix : '');
    }
    
    var yesterdayDate = new Date(d);
    yesterdayDate.setDate(d.getDate() - 1);
    if (btnYesterday) {
        var yM = String(yesterdayDate.getMonth() + 1).padStart(2, '0');
        var yD = String(yesterdayDate.getDate()).padStart(2, '0');
        btnYesterday.href = basePath + yesterdayDate.getFullYear() + "-" + yM + "-" + yD + (langSuffix ? '/' + langSuffix : '');
    }
    
    var tomorrowDate = new Date(d);
    tomorrowDate.setDate(d.getDate() + 1);
    if (btnTomorrow) {
        var tM = String(tomorrowDate.getMonth() + 1).padStart(2, '0');
        var tD = String(tomorrowDate.getDate()).padStart(2, '0');
        btnTomorrow.href = basePath + tomorrowDate.getFullYear() + "-" + tM + "-" + tD + (langSuffix ? '/' + langSuffix : '');
    }
});

(function() {
    var section = '<?php echo esc_js($section !== 'overview' ? $section : ''); ?>';
    var parts = window.location.pathname.replace(/^\/|\/$/g, '').split('/');
    var urlDate = '';
    if (parts.length > 1 && /^\d{4}-\d{2}-\d{2}$/.test(parts[1])) urlDate = parts[1];
    if (parts.length > 2 && /^\d{4}-\d{2}-\d{2}$/.test(parts[2])) urlDate = parts[2];
    
    var dateObj = urlDate ? new Date(urlDate + "T00:00:00") : new Date();
    var formatter = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    var formattedDate = formatter.format(dateObj); 

    var seoTitle = "";
    var seoDesc = "";
    var isHi = <?php echo $is_hi ? 'true' : 'false'; ?>;

    if (isHi) {
        switch(section) {
            case 'rahukaal':
                seoTitle = "आज का राहुकाल - " + formattedDate + " | पंचांग | MeraRashifal.com";
                seoDesc = "आज का राहुकाल समय " + formattedDate + " के लिए। सटीक दैनिक पंचांग विवरण और ज्योतिषीय जानकारी केवल MeraRashifal.com पर।";
                break;
            case 'tithi':
                seoTitle = "आज की तिथि और पंचांग - " + formattedDate + " | MeraRashifal.com";
                seoDesc = "तिथि, पंचांग और वैदिक ज्योतिष के लिए आपका दैनिक मार्गदर्शन " + formattedDate + " के लिए। सटीक डेटा MeraRashifal.com द्वारा।";
                break;
            case 'abhijit-muhurat':
                seoTitle = "आज का अभिजीत मुहूर्त - " + formattedDate + " | पंचांग | MeraRashifal.com";
                seoDesc = "आज के लिए सटीक अभिजीत मुहूर्त समय खोजें, " + formattedDate + "। अपने महत्वपूर्ण कार्यों के लिए सबसे शुभ समय की पहचान करें।";
                break;
            case 'choghadiya-muhurat':
                seoTitle = "आज का चौघड़िया मुहूर्त - " + formattedDate + " | दैनिक पंचांग | MeraRashifal.com";
                seoDesc = "आज के चौघड़िया मुहूर्त समय देखें " + formattedDate + " के लिए। शुभ योजना बनाने के लिए सटीक दिन और रात का चौघड़िया।";
                break;
        }
    } else {
        switch(section) {
            case 'rahukaal':
                seoTitle = "Rahukaal Today - " + formattedDate + " | Panchang | MeraRashifal.com";
                seoDesc = "Check today’s Rahukaal timings for " + formattedDate + ". Get accurate daily Panchang details and in-depth astrological insights only at MeraRashifal.com.";
                break;
            case 'tithi':
                seoTitle = "Today’s Tithi & Panchang - " + formattedDate + " | MeraRashifal.com";
                seoDesc = "Your daily guide to Tithi, Panchang, and Vedic astrology for " + formattedDate + ". Clean, precise, and reliable data provided by MeraRashifal.com.";
                break;
            case 'abhijit-muhurat':
                seoTitle = "Today’s Abhijit Muhurat - " + formattedDate + " | Panchang | MeraRashifal.com";
                seoDesc = "Find the exact Abhijit Muhurat timings for today, " + formattedDate + ". Consult our daily Panchang on MeraRashifal.com to identify the most auspicious window for your new beginnings and important tasks.";
                break;
            case 'choghadiya-muhurat':
                seoTitle = "Today's Choghadiya Muhurat - " + formattedDate + " | Daily Panchang | MeraRashifal.com";
                seoDesc = "View today's Choghadiya Muhurat timings for " + formattedDate + ". Get accurate Day and Night Choghadiya details for auspicious planning on MeraRashifal.com.";
                break;
        }
    }

    if (seoTitle) {
        document.title = seoTitle;
        var meta = document.querySelector('meta[name="description"]');
        if (meta) { meta.setAttribute('content', seoDesc); }
        var ogTitle = document.querySelector('meta[property="og:title"]');
        if(ogTitle) ogTitle.setAttribute('content', seoTitle);
        var ogDesc = document.querySelector('meta[property="og:description"]');
        if(ogDesc) ogDesc.setAttribute('content', seoDesc);
    }
})();
</script>

<?php
    return ob_get_clean();
});

/* ====================================================
   5. SEO & PATH INTEGRATION (YOAST SERVER-SIDE)
==================================================== */
if (!function_exists('mr_pc_get_active_path_info')) {
function mr_pc_get_active_path_info() {
    $uri_path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $site_path = trim((string) parse_url(home_url(), PHP_URL_PATH), '/');
    if ($site_path !== '' && strpos($uri_path, $site_path) === 0) {
        $uri_path = trim(substr($uri_path, strlen($site_path)), '/');
    }
    
    $parts = array_values(array_filter(explode('/', $uri_path)));
    $is_panchang = (!empty($parts[0]) && $parts[0] === 'panchang');
    
    $section = 'overview';
    $date = '';
    $is_hi = false;
    
    if ($is_panchang) {
        foreach ($parts as $idx => $part) {
            if ($idx === 0) continue;
            if ($part === 'hi') {
                $is_hi = true;
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $part)) {
                $date = $part;
            } else {
                $section = sanitize_title($part);
            }
        }
    }
    
    $allowed = mr_pc_sections_fixed();
    if (!in_array($section, $allowed, true)) {
        $section = 'overview';
    }
    
    return [
        'is_panchang' => $is_panchang,
        'section'     => $section,
        'date'        => $date,
        'is_hi'       => $is_hi,
        'parts'       => $parts
    ];
}
}

if (!function_exists('mr_pc_is_panchang_request')) {
function mr_pc_is_panchang_request() {
    if (is_page('panchang') || get_query_var('pagename') === 'panchang' || get_query_var('pc_any')) {
        return true;
    }
    if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/panchang') !== false) {
        return true;
    }
    return false;
}
}

if (!function_exists('mr_pc_get_seo_data')) {
function mr_pc_get_seo_data() {
    $info = mr_pc_get_active_path_info();
    if (!$info['is_panchang'] && !mr_pc_is_panchang_request()) {
        return null;
    }

    $section  = $info['section'];
    $date_val = $info['date'];
    $is_hi    = $info['is_hi'];

    $seo_date = current_time('F j, Y');
    $date_suffix = '';
    if ($date_val && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_val)) {
        $seo_date = date('F j, Y', strtotime($date_val));
        $date_suffix = $date_val . '/';
    }

    $og_img = 'https://merarashifal.com/wp-content/uploads/2026/03/Daily-Panchang.webp';

    // Build canonical
    $canonical_path = '/panchang/';
    if ($section !== 'overview') {
        $canonical_path .= $section . '/';
    }
    if ($date_suffix) {
        $canonical_path .= $date_suffix;
    }
    if ($is_hi) {
        $canonical_path .= 'hi/';
    }
    $canonical_url = home_url($canonical_path);

    $data = [
        'title'     => '',
        'desc'      => '',
        'canonical' => $canonical_url,
        'image'     => $og_img,
        'url'       => $canonical_url
    ];

    if ($is_hi) {
        switch ($section) {
            case 'rahukaal':
                $data['title'] = "आज का राहुकाल - {$seo_date} | राहु काल समय | MeraRashifal.com";
                $data['desc']  = "आज का राहुकाल समय {$seo_date} के लिए। अशुभ समय और दिशा शूल की जानकारी MeraRashifal.com के दैनिक पंचांग पर देखें।";
                break;
            case 'tithi':
                $data['title'] = "आज की तिथि और पंचांग - {$seo_date} | हिन्दू पंचांग | MeraRashifal.com";
                $data['desc']  = "आज की तिथि, पक्ष, समाप्ति समय और धार्मिक महत्व {$seo_date} के लिए। सटीक वैदिक पंचांग विवरण MeraRashifal.com पर।";
                break;
            case 'abhijit-muhurat':
                $data['title'] = "आज का अभिजीत मुहूर्त - {$seo_date} | शुभ मुहूर्त | MeraRashifal.com";
                $data['desc']  = "आज का अभिजीत मुहूर्त समय {$seo_date} के लिए खोजें। नए कार्यों और शुभ आरंभ के लिए सबसे श्रेष्ठ समय MeraRashifal.com पर।";
                break;
            case 'nakshatra':
                $data['title'] = "आज का नक्षत्र - {$seo_date} | दैनिक वैदिक नक्षत्र | MeraRashifal.com";
                $data['desc']  = "आज का नक्षत्र समय, स्वामी ग्रह एवं चरण विवरण {$seo_date} के लिए। दैनिक नक्षत्र स्थिति MeraRashifal.com पर।";
                break;
            case 'karana':
                $data['title'] = "आज का करण - {$seo_date} | दैनिक पंचांग | MeraRashifal.com";
                $data['desc']  = "आज का सक्रिय करण विवरण {$seo_date} के लिए। विष्टि (भद्रा), शकुनि और शुभ करणों का समय MeraRashifal.com पर देखें।";
                break;
            case 'yoga':
                $data['title'] = "आज का योग - {$seo_date} | शुभ एवं अशुभ योग | MeraRashifal.com";
                $data['desc']  = "आज का ज्योतिषीय योग {$seo_date} के लिए। सिद्धि, अमृत, और व्याघात योग समय MeraRashifal.com पर।";
                break;
            case 'sun-moon-timing':
                $data['title'] = "आज का सूर्योदय और चंद्रोदय समय - {$seo_date} | MeraRashifal.com";
                $data['desc']  = "आज का सूर्योदय, सूर्यास्त, चंद्रोदय और चंद्रास्त का सटीक समय {$seo_date} के लिए MeraRashifal.com पर।";
                break;
            case 'year-samvat':
                $data['title'] = "हिन्दू संवत एवं वर्ष विवरण - {$seo_date} | MeraRashifal.com";
                $data['desc']  = "वर्तमान विक्रम संवत, शक संवत, अयन और ऋतु विवरण {$seo_date} के लिए MeraRashifal.com पर देखें।";
                break;
            case 'choghadiya-muhurat':
                $data['title'] = "आज का चौघड़िया मुहूर्त - {$seo_date} | दिन एवं रात चौघड़िया | MeraRashifal.com";
                $data['desc']  = "आज के दिन और रात का चौघड़िया मुहूर्त {$seo_date} के लिए। शुभ, लाभ, अमृत और काल समय MeraRashifal.com पर।";
                break;
            default: // overview / hi
                $data['title'] = "आज का पंचांग - {$seo_date} | दैनिक पंचांग, शुभ मुहूर्त एवं राहुकाल - MeraRashifal.com";
                $data['desc']  = "{$seo_date} का सम्पूर्ण वैदिक पंचांग। आज की तिथि, नक्षत्र, करण, योग, राहुकाल और शुभ मुहूर्त की सटीक जानकारी MeraRashifal.com पर देखें।";
                break;
        }
    } else {
        switch ($section) {
            case 'rahukaal':
                $data['title'] = "Today's Rahukaal Timing - {$seo_date} | Inauspicious Hours - MeraRashifal.com";
                $data['desc']  = "Find exact Rahukaal timings for {$seo_date}. Avoid inauspicious hours for important tasks, journeys, and ventures with MeraRashifal.com's daily Panchang.";
                break;
            case 'tithi':
                $data['title'] = "Today's Tithi & Panchang - {$seo_date} | Hindu Tithi Today - MeraRashifal.com";
                $data['desc']  = "Check today's Tithi for {$seo_date}. Detailed insights into current lunar day, Tithi start/end times, ruling deity, and religious significance on MeraRashifal.com.";
                break;
            case 'abhijit-muhurat':
                $data['title'] = "Today's Abhijit Muhurat - {$seo_date} | Auspicious Timings - MeraRashifal.com";
                $data['desc']  = "Get today's Abhijit Muhurat timing for {$seo_date}. The most powerful auspicious window of the day for new beginnings, investments, and success on MeraRashifal.com.";
                break;
            case 'nakshatra':
                $data['title'] = "Today's Nakshatra - {$seo_date} | Daily Vedic Constellation - MeraRashifal.com";
                $data['desc']  = "Discover today's Nakshatra for {$seo_date}. Accurate birth star timings, charan, ruling planet, and cosmic influences on MeraRashifal.com.";
                break;
            case 'karana':
                $data['title'] = "Today's Karana - {$seo_date} | Half Lunar Day Astrology - MeraRashifal.com";
                $data['desc']  = "Check today's active Karana for {$seo_date}. Complete details on Vishti (Bhadra), Shakuni, Chatushpada, and auspicious Karanas on MeraRashifal.com.";
                break;
            case 'yoga':
                $data['title'] = "Today's Yoga - {$seo_date} | Auspicious & Inauspicious Yogas - MeraRashifal.com";
                $data['desc']  = "Know today's astrological Yoga for {$seo_date}. Calculate solar-lunar yoga, auspicious Siddhi/Amrit yogas, and malefic periods on MeraRashifal.com.";
                break;
            case 'sun-moon-timing':
                $data['title'] = "Today's Sun & Moon Timing - {$seo_date} | Sunrise & Moonrise - MeraRashifal.com";
                $data['desc']  = "Accurate Sunrise, Sunset, Moonrise, and Moonset timings for {$seo_date}. Plan your daily prayers, fasts, and rituals with MeraRashifal.com.";
                break;
            case 'year-samvat':
                $data['title'] = "Hindu Year & Samvat - {$seo_date} | Vikram & Shaka Samvat - MeraRashifal.com";
                $data['desc']  = "Explore current Hindu calendar year, Vikram Samvat, Shaka Samvat, Ayana, and Ritu details for {$seo_date} on MeraRashifal.com.";
                break;
            case 'choghadiya-muhurat':
                $data['title'] = "Today's Choghadiya Muhurat - {$seo_date} | Day & Night - MeraRashifal.com";
                $data['desc']  = "View today's Day and Night Choghadiya Muhurat for {$seo_date}. Shubh, Labh, Amrit, and Kaal timings for successful daily planning on MeraRashifal.com.";
                break;
            default: // overview
                $data['title'] = "Today's Panchang - {$seo_date} | Daily Tithi & Muhurat - MeraRashifal.com";
                $data['desc']  = "Check today's complete Vedic Panchang for {$seo_date}. Get accurate Tithi, Nakshatra, Rahukaal, Shubh Muhurat, and Sunrise/Sunset timings on MeraRashifal.com.";
                break;
        }
    }

    return $data;
}
}

// Hook Yoast SEO Filters
add_filter('wpseo_title', function($title) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

add_filter('pre_get_document_title', function($title) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

add_filter('wpseo_metadesc', function($desc) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $desc;
}, 100);

add_filter('wpseo_canonical', function($canonical) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['canonical'])) return $seo['canonical'];
    }
    return $canonical;
}, 100);

add_filter('wpseo_opengraph_title', function($og_title) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $og_title;
}, 100);

add_filter('wpseo_opengraph_desc', function($og_desc) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $og_desc;
}, 100);

add_filter('wpseo_opengraph_url', function($og_url) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['url'])) return $seo['url'];
    }
    return $og_url;
}, 100);

add_filter('wpseo_opengraph_image', function($og_img) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $og_img;
}, 100);

add_filter('wpseo_twitter_title', function($tw_title) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $tw_title;
}, 100);

add_filter('wpseo_twitter_description', function($tw_desc) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $tw_desc;
}, 100);

add_filter('wpseo_twitter_image', function($tw_img) {
    if (mr_pc_is_panchang_request()) {
        $seo = mr_pc_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $tw_img;
}, 100);

// Demote or adapt top H1 for sub-sections
add_filter('the_content', function($content) {
    if (mr_pc_is_panchang_request()) {
        $info = mr_pc_get_active_path_info();
        $section = $info['section'];
        $is_hi = $info['is_hi'];

        if ($section !== 'overview') {
            $section_titles_en = [
                'tithi' => "Today's Tithi & Panchang (तिथि)",
                'rahukaal' => "Today's Rahu Kaal Timing (राहु काल)",
                'abhijit-muhurat' => "Today's Abhijit Muhurat (अभिजीत मुहूर्त)",
                'nakshatra' => "Today's Nakshatra Details (नक्षत्र)",
                'karana' => "Today's Karana Details (करण)",
                'yoga' => "Today's Yoga Details (योग)",
                'sun-moon-timing' => "Today's Sun & Moon Timing (सूर्योदय और चंद्रोदय)",
                'year-samvat' => "Hindu Year & Samvat Details (संवत)",
                'choghadiya-muhurat' => "Today's Choghadiya Muhurat (चौघड़िया मुहूर्त)"
            ];
            $section_titles_hi = [
                'tithi' => "आज की तिथि और पंचांग (Tithi)",
                'rahukaal' => "आज का राहुकाल समय (Rahu Kaal)",
                'abhijit-muhurat' => "आज का अभिजीत मुहूर्त (Abhijit Muhurat)",
                'nakshatra' => "आज का नक्षत्र विवरण (Nakshatra)",
                'karana' => "आज का करण विवरण (Karana)",
                'yoga' => "आज का योग विवरण (Yoga)",
                'sun-moon-timing' => "सूर्योदय और चंद्रोदय समय (Sun & Moon Timing)",
                'year-samvat' => "हिन्दू संवत एवं वर्ष विवरण (Year / Samvat)",
                'choghadiya-muhurat' => "आज का चौघड़िया मुहूर्त (Choghadiya)"
            ];

            $new_title = ($is_hi && isset($section_titles_hi[$section])) 
                ? $section_titles_hi[$section] 
                : ($section_titles_en[$section] ?? "Today's Panchang: {$section}");

            $content = preg_replace(
                '/(<h1[^>]*>).*?(<\/h1>)/is',
                '$1' . esc_html($new_title) . '$2',
                $content,
                1
            );
        } elseif ($is_hi) {
            $content = preg_replace(
                '/(<h1[^>]*>).*?(<\/h1>)/is',
                '$1' . 'आज का पंचांग: समय और शुभता का संगम (दैनिक पंचांग)' . '$2',
                $content,
                1
            );
        }
    }
    return $content;
}, 9999);