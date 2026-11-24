/*
====================================================
MERA RASHIFAL - PANCHANG ENGINE (RESTORED + MULTILINGUAL)
For WP Code Snippets
Shortcode: [dynamic_panchang]
After saving: Settings > Permalinks > Save Changes
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

/* ====================================================
4. SHORTCODE
==================================================== */
add_shortcode('dynamic_panchang', function () {

    global $wpdb;

    $pc_any  = get_query_var('pc_any');
    $pc_date = get_query_var('pc_date');
    $pc_lang = get_query_var('pc_lang', 'en'); // English by default
    $is_hi   = ($pc_lang === 'hi');

    $allowed = mr_pc_sections_fixed();
    $section = 'overview';
    $date = '';

    $info = mr_pc_get_active_path_info();
    $section = $info['section'];
    $date    = $info['date'];
    $is_hi   = $info['is_hi'];

    $today = current_time('Y-m-d');
    $selected_date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : $today;

    $yesterday = gmdate('Y-m-d', strtotime($selected_date . ' -1 day'));
    $tomorrow  = gmdate('Y-m-d', strtotime($selected_date . ' +1 day'));

    $chog_min = '2025-01-01'; // Default fallbacks
    $chog_max = '2026-12-31';

    if ($section === 'choghadiya-muhurat') {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM af_choghadiya_full_response WHERE choghadiya_date = %s AND language = %s LIMIT 1",
                $selected_date,
                $pc_lang
            )
        );
        $range = $wpdb->get_row("SELECT MIN(choghadiya_date) as min_d, MAX(choghadiya_date) as max_d FROM af_choghadiya_full_response");
        if ($range) {
            $chog_min = $range->min_d;
            $chog_max = $range->max_d;
        }
    } else {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM af_panchang_data WHERE api_date = %s AND language = %s LIMIT 1",
                $selected_date,
                $pc_lang
            )
        );
    }

    ob_start();
    ?>

<style>
:root{
--primary-purple:#3B1E54;
--secondary-orange:#D65A31;
--neutral-text:#222831;
--bg-sober:#FFFBEB;
}

.mr-horoscope{
background:#FFFBEB;
font-family:'Noto Sans',sans-serif;
color:var(--neutral-text);
padding:12px 12px 40px 12px;
line-height:1.6;
max-width:1000px;
margin:0 auto;
}

/* Language Toggle Styling */
.mr-lang-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.mr-lang-toggle {
    display: flex;
    background: #eee2b8;
    padding: 3px;
    border-radius: 50px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
.mr-lang-btn {
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    color: var(--primary-purple) !important;
    transition: all 0.2s ease;
}
.mr-lang-btn.active {
    background: var(--primary-purple);
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(59, 30, 84, 0.3);
}

.mr-date-nav,
.mr-section-nav{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:6px;
margin-bottom:10px;
}

.mr-btn{
display:block;
padding:7px 6px;
border-radius:6px;
text-decoration:none;
font-weight:600;
font-size:12px;
line-height:1.2;
text-align:center;
border:none;
cursor:pointer;
color:#fff !important;
background:var(--secondary-orange);
white-space:nowrap;
}

.mr-btn-alt{
background:var(--primary-purple);
}

.mr-date-nav input{
padding:7px 10px;
border:1px solid #d6d6d6;
border-radius:6px;
width:100%;
min-width:100%;
background:#fff;
font-size:13px;
box-sizing:border-box;
cursor:pointer;
}

.mr-more-btn{
background:#3B1E54;
color:#fff !important;
font-size:12px;
padding:5px 12px;
border-radius:4px;
text-decoration:none;
font-weight:600;
display:inline-block;
}
.mr-more-btn:hover{
text-decoration:underline;
}

.mr-section-header{
display:flex;
justify-content:space-between;
align-items:center;
margin:0 0 8px;
}

.mr-sector,
.mr-card{
background:#FFFBEB;
padding:14px;
margin-bottom:10px;
border-radius:8px;
border:1px solid #eee2b8;
}

.mr-score-grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:8px;
margin-bottom:12px;
}

.mr-section h2{
font-size:25px;
margin:0;
color:var(--primary-purple);
}

.mr-section h2 a {
color:var(--primary-purple);
text-decoration:none;
font-size:inherit !important;
font-weight:800 !important;
}

.mr-section h2 a:hover {
text-decoration:underline;
}

.mr-section p{
margin:0 0 8px;
}

@media(max-width:768px){
.mr-date-nav,
.mr-section-nav{
grid-template-columns:repeat(4,1fr);
gap:5px;
}

.mr-btn{
font-size:11px;
padding:7px 4px;
}

.mr-score-grid{
grid-template-columns:repeat(2,1fr);
}
}

@media(max-width:480px){
.mr-date-nav,
.mr-section-nav{
grid-template-columns:repeat(4,1fr);
gap:4px;
}

.mr-btn{
font-size:10px;
padding:6px 3px;
border-radius:5px;
}

.mr-date-nav input{
font-size:12px;
padding:6px 8px;
}

.mr-score-grid{
grid-template-columns:1fr;
}

.mr-section h2{
font-size:22px;
}
}

/* Choghadiya Table Styles */
.mr-chog-nav-sticky {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #fff;
    padding: 10px 0;
    margin: -12px -12px 15px -12px;
    display: flex;
    justify-content: center;
    gap: 15px;
    border-bottom: 2px solid var(--primary-purple);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    opacity: 0.95;
}
.mr-chog-nav-sticky a {
    background: var(--primary-purple);
    color: #fff !important;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.2s;
}
.mr-chog-nav-sticky a:hover {
    transform: scale(1.05);
}
.mr-chog-wrap h3 {
    font-size: 20px;
    color: var(--secondary-orange);
    margin: 20px 0 10px;
    border-bottom: 2px solid var(--secondary-orange);
    padding-bottom: 5px;
    scroll-margin-top: 80px; /* Offset for sticky nav */
}
.mr-chog-grid {
    display: grid;
    grid-template-columns: 1.8fr 0.6fr 0.6fr !important;
    border: 3px solid #d6c4a8 !important;
    border-radius: 12px;
    overflow: hidden;
    background: transparent !important;
    margin-top: 10px;
}
.mr-chog-head {
    background: var(--primary-purple) !important;
    color: #fff !important;
    font-weight: 800;
    padding: 15px 10px;
    text-align: center;
    font-size: 16px;
    border-right: 1px solid rgba(255,255,255,0.3);
}
.mr-chog-head:last-child { border-right: none; }

.mr-chog-cell {
    padding: 15px 10px;
    border-bottom: 2px solid #d6c4a8 !important;
    border-right: 2px solid #d6c4a8 !important;
    text-align: center;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333 !important;
}
.mr-chog-cell:nth-child(3n) { border-right: none !important; }
.mr-chog-grid > div:nth-last-child(-n+3) { border-bottom: none !important; }

.mr-chog-type {
    padding: 6px 14px !important;
    border-radius: 4px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #fff !important;
    display: inline-block !important;
    text-align: center !important;
    min-width: 80px !important;
    line-height: 1 !important;
}
/* Shubh (Green) */
.type-shubh, .type-good, .type-%e0%a4%b6%e0%a5%81%e0%a4%ad { 
    background-color: #2E7D32 !important; 
    color: #ffffff !important;
}
/* Ashubh (Red) */
.type-ashubh, .type-bad, .type-%e0%a4%85%e0%a4%b6%e0%a5%81%e0%a4%ad { 
    background-color: #C62828 !important; 
    color: #ffffff !important;
}

@media(max-width:480px) {
    .mr-chog-nav-sticky {
        padding: 8px 0;
        gap: 10px;
    }
    .mr-chog-nav-sticky a {
        padding: 6px 15px;
        font-size: 12px;
    }
    .mr-chog-grid {
        grid-template-columns: 1.8fr 0.6fr 0.6fr !important;
    }
    .mr-chog-head, .mr-chog-cell {
        font-size: 12px !important;
        padding: 12px 4px !important;
    }
    .mr-chog-type {
        padding: 4px 8px !important;
        font-size: 12px !important;
    }
}
</style>

<div class="mr-horoscope">

<!-- Language Toggle Header -->
<div class="mr-lang-header">
    <div class="mr-panchang-title" style="font-size:20px; font-weight:800; color:var(--primary-purple);">
        <?php echo ucwords(str_replace('-', ' ', $section)); ?>
    </div>
    <div class="mr-lang-toggle">
        <?php
        $base_url = home_url('/panchang/') . ($section !== 'overview' ? $section . '/' : '') . ($date ? $date . '/' : '');
        $en_url = $base_url;
        $hi_url = $base_url . 'hi/';
        ?>
        <a href="<?php echo esc_url($en_url); ?>" 
           class="mr-lang-btn <?php echo !$is_hi ? 'active' : ''; ?>"
           onclick="if(typeof gtag === 'function') { gtag('event', 'language_change', {'lang': 'en'}); }">
           English
        </a>
        <a href="<?php echo esc_url($hi_url); ?>" 
           class="mr-lang-btn <?php echo $is_hi ? 'active' : ''; ?>"
           onclick="if(typeof gtag === 'function') { gtag('event', 'language_change', {'lang': 'hi'}); }">
           Hindi
        </a>
    </div>
</div>

<!-- TOP DATE NAV -->
<div class="mr-date-nav">

<a class="mr-btn" id="mr-btn-yesterday" href="/panchang/<?php echo ($section !== 'overview') ? esc_attr($section) . '/' : ''; ?><?php echo esc_attr($yesterday); ?><?php echo $is_hi ? '/hi/' : ''; ?>">
Yesterday
</a>

<a class="mr-btn" id="mr-btn-today" href="/panchang/<?php echo ($section !== 'overview') ? esc_attr($section) . '/' : ''; ?><?php echo $is_hi ? 'hi/' : ''; ?>">
Today
</a>

<a class="mr-btn" id="mr-btn-tomorrow" href="/panchang/<?php echo ($section !== 'overview') ? esc_attr($section) . '/' : ''; ?><?php echo esc_attr($tomorrow); ?><?php echo $is_hi ? '/hi/' : ''; ?>">
Tomorrow
</a>

<input type="date" id="pcDate" class="mr-btn" style="color:var(--neutral-text) !important; background:#fff; border:1px solid #d6d6d6;" 
    value="<?php echo esc_attr($selected_date); ?>" 
    <?php if($section === 'choghadiya-muhurat'): ?>
        min="<?php echo esc_attr($chog_min); ?>" 
        max="<?php echo esc_attr($chog_max); ?>"
    <?php endif; ?>
    onchange="mrPcDate('<?php echo esc_js($section); ?>')">

</div>

<?php if (!$row): ?>

<div class="mr-sector">
No Panchang data available for this date.
</div>

<?php else: ?>

<?php switch ($section):

case 'tithi': ?>
<div class="mr-section">
<h2>Tithi - Panchang</h2>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->tithi_name); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->tithi_number); ?></p>
<p><strong>Type:</strong> <?php echo esc_html($row->tithi_type); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->tithi_diety); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->tithi_start); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->tithi_end); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->tithi_meaning); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->tithi_special); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->tithi_next); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'rahukaal': ?>
<div class="mr-section">
<h2>Rahukaal - Panchang</h2>
<div class="mr-sector">
<p><strong>Vara:</strong> <?php echo esc_html($row->vaara); ?></p>
<p><strong>Disha Shool:</strong> <?php echo esc_html($row->disha_shool); ?></p>
<p><strong>Rahukaal:</strong> <?php echo esc_html($row->rahukaal); ?></p>
<p><strong>Gulika:</strong> <?php echo esc_html($row->gulika); ?></p>
<p><strong>Yamakanta:</strong> <?php echo esc_html($row->yamakanta); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'nakshatra': ?>
<div class="mr-section">
<h2>Nakshatra - Panchang</h2>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->nakshatra_name); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->nakshatra_number); ?></p>
<p><strong>Lord:</strong> <?php echo esc_html($row->nakshatra_lord); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->nakshatra_diety); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->nakshatra_start); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->nakshatra_end); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->nakshatra_meaning); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->nakshatra_special); ?></p>
<p><strong>Auspicious Disha:</strong> <?php echo esc_html($row->nakshatra_auspicious_disha); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->nakshatra_next); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'abhijit-muhurat': ?>
<div class="mr-section">
<h2>Abhijit Muhurat - Panchang</h2>
<div class="mr-sector">
<p><strong>Vara:</strong> <?php echo esc_html($row->vaara); ?></p>
<p><strong>Disha Shool:</strong> <?php echo esc_html($row->disha_shool); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->abhijit_muhurta_start); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->abhijit_muhurta_end); ?></p>
<p><strong>Next Full Moon:</strong> <?php echo esc_html($row->next_full_moon); ?></p>
<p><strong>Next New Moon:</strong> <?php echo esc_html($row->next_new_moon); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'karana': ?>
<div class="mr-section">
<h2>Karana - Panchang</h2>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->karana_name); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->karana_number); ?></p>
<p><strong>Type:</strong> <?php echo esc_html($row->karana_type); ?></p>
<p><strong>Lord:</strong> <?php echo esc_html($row->karana_lord); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->karana_diety); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->karana_start); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->karana_end); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->karana_special); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->karana_next); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'yoga': ?>
<div class="mr-section">
<h2>Yoga - Panchang</h2>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->yoga_name); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->yoga_number); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->yoga_start); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->yoga_end); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->yoga_meaning); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->yoga_special); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->yoga_next); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'sun-moon-timing': ?>
<div class="mr-section">
<h2>Sun & Moon Timing</h2>
<div class="mr-score-grid">
<div class="mr-card"><strong>Rashi</strong><br><?php echo esc_html($row->rasi_name); ?></div>
<div class="mr-card"><strong>Sun Zodiac</strong><br><?php echo esc_html($row->sun_zodiac); ?></div>
<div class="mr-card"><strong>Sunrise</strong><br><?php echo esc_html($row->sun_rise); ?></div>
<div class="mr-card"><strong>Sunset</strong><br><?php echo esc_html($row->sun_set); ?></div>
<div class="mr-card"><strong>Moonrise</strong><br><?php echo esc_html($row->moon_rise); ?></div>
<div class="mr-card"><strong>Moonset</strong><br><?php echo esc_html($row->moon_set); ?></div>
</div>
</div>
<?php break; ?>

<?php case 'year-samvat': ?>
<div class="mr-section">
<h2>Years / Samvat - Panchang</h2>
<div class="mr-sector">
<p><strong>Vikram Year:</strong> <?php echo esc_html($row->year_vikram); ?></p>
<p><strong>Vikram Samvat Name:</strong> <?php echo esc_html($row->year_vikram_samvaat_name); ?></p>
<p><strong>Kali:</strong> <?php echo esc_html($row->year_kali); ?></p>
<p><strong>Kali Samvat Name:</strong> <?php echo esc_html($row->year_kali_samvaat_name); ?></p>
<p><strong>Saka:</strong> <?php echo esc_html($row->year_saka); ?></p>
<p><strong>Saka Samvat Name:</strong> <?php echo esc_html($row->year_saka_samvaat_name); ?></p>
</div>
</div>
<?php break; ?>

<?php case 'choghadiya-muhurat': ?>
<div class="mr-chog-nav-sticky">
    <a href="#day-chog">☀️ Day</a>
    <a href="#night-chog">🌙 Night</a>
</div>

<div class="mr-section">
    <h2>Choghadiya Muhurat - <?php echo esc_html(date('F j, Y', strtotime($selected_date))); ?></h2>
    
    <div class="mr-chog-wrap" id="day-chog">
        <h3>Day Choghadiya</h3>
        <div class="mr-chog-grid">
            <div class="mr-chog-head">Time</div>
            <div class="mr-chog-head">Muhurat</div>
            <div class="mr-chog-head">Quality</div>
            
            <?php for($i=1;$i<=8;$i++): 
                $s_key="day_{$i}_start"; $e_key="day_{$i}_end"; $m_key="day_{$i}_muhurat"; $t_key="day_{$i}_type";
                $s_val = $row->$s_key ?? '';
                $e_val = $row->$e_key ?? '';
                $m_val = $row->$m_key ?? '';
                $t_val = trim($row->$t_key ?? '');
                
                $q_bg = 'background-color:#757575 !important; color:#ffffff !important;';
                if ($t_val == 'शुभ') {
                    $q_bg = 'background-color:#2E7D32 !important; color:#ffffff !important;';
                } elseif ($t_val == 'अशुभ') {
                    $q_bg = 'background-color:#C62828 !important; color:#ffffff !important;';
                } elseif ($t_val == 'अच्छा') {
                    $q_bg = 'background-color:#FFD700 !important; color:#000000 !important;'; // Yellow with Black text
                }

                if(!empty($s_val)): ?>
                <div class="mr-chog-cell" style="flex-direction:column; gap:6px; font-size:14px; align-items:flex-start; text-align:left;">
                    <span style="display:flex; align-items:center; gap:5px;">☀️ <b>Start:</b> <?php echo esc_html($s_val); ?></span>
                    <span style="display:flex; align-items:center; gap:5px;">🌙 <b>End:</b> <?php echo esc_html($e_val); ?></span>
                </div>
                <div class="mr-chog-cell"><b style="font-size:16px; color:var(--primary-purple);"><?php echo esc_html($m_val); ?></b></div>
                <div class="mr-chog-cell"><span class="mr-chog-type" style="<?php echo esc_attr($q_bg); ?>"><?php echo esc_html($t_val); ?></span></div>
            <?php endif; endfor; ?>
        </div>
    </div>

    <div class="mr-chog-wrap" id="night-chog" style="margin-top:40px;">
        <h3>Night Choghadiya</h3>
        <div class="mr-chog-grid">
            <div class="mr-chog-head">Time</div>
            <div class="mr-chog-head">Muhurat</div>
            <div class="mr-chog-head">Quality</div>
            
            <?php for($i=1;$i<=8;$i++): 
                $s_key="night_{$i}_start"; $e_key="night_{$i}_end"; $m_key="night_{$i}_muhurat"; $t_key="night_{$i}_type";
                $s_val = $row->$s_key ?? '';
                $e_val = $row->$e_key ?? '';
                $m_val = $row->$m_key ?? '';
                $t_val = trim($row->$t_key ?? '');

                $q_bg = 'background-color:#757575 !important; color:#ffffff !important;';
                if ($t_val == 'शुभ') {
                    $q_bg = 'background-color:#2E7D32 !important; color:#ffffff !important;';
                } elseif ($t_val == 'अशुभ') {
                    $q_bg = 'background-color:#C62828 !important; color:#ffffff !important;';
                } elseif ($t_val == 'अच्छा') {
                    $q_bg = 'background-color:#FFD700 !important; color:#000000 !important;'; // Yellow with Black text
                }

                if(!empty($s_val)): ?>
                <div class="mr-chog-cell" style="flex-direction:column; gap:6px; font-size:14px; align-items:flex-start; text-align:left;">
                    <span style="display:flex; align-items:center; gap:5px;">☀️ <b>Start:</b> <?php echo esc_html($s_val); ?></span>
                    <span style="display:flex; align-items:center; gap:5px;">🌙 <b>End:</b> <?php echo esc_html($e_val); ?></span>
                </div>
                <div class="mr-chog-cell"><b style="font-size:16px; color:var(--primary-purple);"><?php echo esc_html($m_val); ?></b></div>
                <div class="mr-chog-cell"><span class="mr-chog-type" style="<?php echo esc_attr($q_bg); ?>"><?php echo esc_html($t_val); ?></span></div>
            <?php endif; endfor; ?>
        </div>
    </div>
</div>
<?php break; ?>

<?php default: ?>
<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/">Date & Day</a></h2>
</div>
<div class="mr-sector">
<p><strong>Date:</strong> <?php echo esc_html($row->api_date ?? ''); ?></p>
<p><strong>Day:</strong> <?php echo esc_html($row->day_name ?? ''); ?></p>
<p><strong>Full Date:</strong> <?php echo esc_html($row->api_date_text ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/tithi/">Tithi</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/tithi/">More</a>
</div>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->tithi_name ?? ''); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->tithi_number ?? ''); ?></p>
<p><strong>Type:</strong> <?php echo esc_html($row->tithi_type ?? ''); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->tithi_diety ?? ''); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->tithi_start ?? ''); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->tithi_end ?? ''); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->tithi_meaning ?? ''); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->tithi_special ?? ''); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->tithi_next ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/abhijit-muhurat/">Abhijit Muhurat</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/abhijit-muhurat/">More</a>
</div>
<div class="mr-sector">
<p><strong>Vara:</strong> <?php echo esc_html($row->vaara ?? ''); ?></p>
<p><strong>Disha Shool:</strong> <?php echo esc_html($row->disha_shool ?? ''); ?></p>
<p><strong>Abhijit (start):</strong> <?php echo esc_html($row->abhijit_muhurta_start ?? ''); ?></p>
<p><strong>Abhijit (end):</strong> <?php echo esc_html($row->abhijit_muhurta_end ?? ''); ?></p>
<p><strong>Next Full Moon:</strong> <?php echo esc_html($row->next_full_moon ?? ''); ?></p>
<p><strong>Next New Moon:</strong> <?php echo esc_html($row->next_new_moon ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/rahukaal/">Rahukaal</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/rahukaal/">More</a>
</div>
<div class="mr-sector">
<p><strong>Vara:</strong> <?php echo esc_html($row->vaara ?? ''); ?></p>
<p><strong>Disha Shool:</strong> <?php echo esc_html($row->disha_shool ?? ''); ?></p>
<p><strong>Rahukaal:</strong> <?php echo esc_html($row->rahukaal ?? ''); ?></p>
<p><strong>Gulika:</strong> <?php echo esc_html($row->gulika ?? ''); ?></p>
<p><strong>Yamakanta:</strong> <?php echo esc_html($row->yamakanta ?? ''); ?></p>
<p><strong>Next Full Moon:</strong> <?php echo esc_html($row->next_full_moon ?? ''); ?></p>
<p><strong>Next New Moon:</strong> <?php echo esc_html($row->next_new_moon ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/nakshatra/">Nakshatra</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/nakshatra/">More</a>
</div>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->nakshatra_name ?? ''); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->nakshatra_number ?? ''); ?></p>
<p><strong>Lord:</strong> <?php echo esc_html($row->nakshatra_lord ?? ''); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->nakshatra_diety ?? ''); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->nakshatra_start ?? ''); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->nakshatra_end ?? ''); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->nakshatra_meaning ?? ''); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->nakshatra_special ?? ''); ?></p>
<p><strong>Auspicious Disha:</strong> <?php echo esc_html($row->nakshatra_auspicious_disha ?? ''); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->nakshatra_next ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/karana/">Karana</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/karana/">More</a>
</div>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->karana_name ?? ''); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->karana_number ?? ''); ?></p>
<p><strong>Type:</strong> <?php echo esc_html($row->karana_type ?? ''); ?></p>
<p><strong>Lord:</strong> <?php echo esc_html($row->karana_lord ?? ''); ?></p>
<p><strong>Deity:</strong> <?php echo esc_html($row->karana_diety ?? ''); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->karana_start ?? ''); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->karana_end ?? ''); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->karana_special ?? ''); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->karana_next ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/yoga/">Yoga</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/yoga/">More</a>
</div>
<div class="mr-sector">
<p><strong>Name:</strong> <?php echo esc_html($row->yoga_name ?? ''); ?></p>
<p><strong>Number:</strong> <?php echo esc_html($row->yoga_number ?? ''); ?></p>
<p><strong>Start:</strong> <?php echo esc_html($row->yoga_start ?? ''); ?></p>
<p><strong>End:</strong> <?php echo esc_html($row->yoga_end ?? ''); ?></p>
<p><strong>Meaning:</strong> <?php echo esc_html($row->yoga_meaning ?? ''); ?></p>
<p><strong>Special:</strong> <?php echo esc_html($row->yoga_special ?? ''); ?></p>
<p><strong>Next:</strong> <?php echo esc_html($row->yoga_next ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/sun-moon-timing/">Sun & Moon Timing</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/sun-moon-timing/">More</a>
</div>
<div class="mr-sector">
<p><strong>Rashi (Rashi Name):</strong> <?php echo esc_html($row->rasi_name ?? ''); ?></p>
<p><strong>Sun Zodiac:</strong> <?php echo esc_html($row->sun_zodiac ?? ''); ?></p>
<p><strong>Sun Nakshatra:</strong> <?php echo esc_html($row->sun_nakshatra ?? ''); ?></p>
<p><strong>Sun Rashi No.:</strong> <?php echo esc_html($row->sun_rashi_no ?? ''); ?></p>
<p><strong>Sun Nakshatra No.:</strong> <?php echo esc_html($row->sun_nakshatra_no ?? ''); ?></p>
<p><strong>Sun Deg @ Rise:</strong> <?php echo esc_html($row->sun_deg_at_rise ?? ''); ?></p>
<p><strong>Moon Degree:</strong> <?php echo esc_html($row->moon_degree ?? ''); ?></p>
<p><strong>Sunrise:</strong> <?php echo esc_html($row->sun_rise ?? ''); ?></p>
<p><strong>Sunset:</strong> <?php echo esc_html($row->sun_set ?? ''); ?></p>
<p><strong>Moonrise:</strong> <?php echo esc_html($row->moon_rise ?? ''); ?></p>
<p><strong>Moonset:</strong> <?php echo esc_html($row->moon_set ?? ''); ?></p>
</div>
</div>

<div class="mr-section">
<div class="mr-section-header">
<h2><a href="https://merarashifal.com/panchang/year-samvat/">Years / Samvat</a></h2>
<a class="mr-more-btn" href="https://merarashifal.com/panchang/year-samvat/">More</a>
</div>
<div class="mr-sector">
<p><strong>Vikram Year:</strong> <?php echo esc_html($row->year_vikram ?? ''); ?></p>
<p><strong>Vikram Samvat No.:</strong> <?php echo esc_html($row->year_vikram_samvaat_no ?? ''); ?></p>
<p><strong>Vikram Samvat Name:</strong> <?php echo esc_html($row->year_vikram_samvaat_name ?? ''); ?></p>
<p><strong>Kali:</strong> <?php echo esc_html($row->year_kali ?? ''); ?></p>
<p><strong>Kali Samvat No.:</strong> <?php echo esc_html($row->year_kali_samvaat_no ?? ''); ?></p>
<p><strong>Kali Samvat Name:</strong> <?php echo esc_html($row->year_kali_samvaat_name ?? ''); ?></p>
<p><strong>Saka:</strong> <?php echo esc_html($row->year_saka ?? ''); ?></p>
<p><strong>Saka Samvat No.:</strong> <?php echo esc_html($row->year_saka_samvaat_no ?? ''); ?></p>
<p><strong>Saka Samvat Name:</strong> <?php echo esc_html($row->year_saka_samvaat_name ?? ''); ?></p>
</div>
</div>
<?php break; ?>

<?php endswitch; ?>

<!-- BOTTOM SECTION NAV -->
<div class="mr-section-nav">

<?php if ($section !== 'overview') : ?>
<a class="mr-btn mr-btn-alt" href="/panchang/<?php echo $is_hi ? 'hi/' : ''; ?>">Overview</a>
<?php endif; ?>

<a class="mr-btn mr-btn-alt" href="/panchang/tithi/<?php echo $is_hi ? 'hi/' : ''; ?>">Tithi</a>
<a class="mr-btn mr-btn-alt" href="/panchang/rahukaal/<?php echo $is_hi ? 'hi/' : ''; ?>">Rahukaal</a>
<a class="mr-btn mr-btn-alt" href="/panchang/nakshatra/<?php echo $is_hi ? 'hi/' : ''; ?>">Nakshatra</a>
<a class="mr-btn mr-btn-alt" href="/panchang/abhijit-muhurat/<?php echo $is_hi ? 'hi/' : ''; ?>">Abhijit</a>
<a class="mr-btn mr-btn-alt" href="/panchang/karana/<?php echo $is_hi ? 'hi/' : ''; ?>">Karana</a>
<a class="mr-btn mr-btn-alt" href="/panchang/yoga/<?php echo $is_hi ? 'hi/' : ''; ?>">Yoga</a>
<a class="mr-btn mr-btn-alt" href="/panchang/sun-moon-timing/<?php echo $is_hi ? 'hi/' : ''; ?>">Sun/Moon</a>
<a class="mr-btn mr-btn-alt" href="/panchang/year-samvat/<?php echo $is_hi ? 'hi/' : ''; ?>">Samvat</a>

</div>

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
    
    var btnYesterday = document.getElementById("mr-btn-yesterday");
    var btnToday = document.getElementById("mr-btn-today");
    var btnTomorrow = document.getElementById("mr-btn-tomorrow");
    
    if (btnToday) {
        btnToday.href = basePath + localDate + (langSuffix ? '/' + langSuffix : '');
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
        var tm = String(tomorrowDate.getMonth() + 1).padStart(2, '0');
        var td = String(tomorrowDate.getDate()).padStart(2, '0');
        btnTomorrow.href = basePath + tomorrowDate.getFullYear() + "-" + tm + "-" + td + (langSuffix ? '/' + langSuffix : '');
    }

    <?php if (empty($date)): ?>
        var serverDate = "<?php echo esc_js($selected_date); ?>";
        if (serverDate !== localDate) {
            window.location.replace(basePath + localDate + (langSuffix ? '/' + langSuffix : ''));
        }
    <?php endif; ?>

    // Auto-scroll to content if no hash is present
    if (!window.location.hash) {
        setTimeout(function() {
            var el = document.querySelector('.mr-date-nav');
            if (el) {
                var offset = 80; // Offset for sticky headers if any
                var bodyRect = document.body.getBoundingClientRect().top;
                var elementRect = el.getBoundingClientRect().top;
                var elementPosition = elementRect - bodyRect;
                var offsetPosition = elementPosition - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }, 500);
    }
});

(function() {
    var section = '<?php echo esc_js($section !== 'overview' ? $section : ''); ?>';
    if (!section) return; 
    
    var parts = window.location.pathname.replace(/^\/|\/$/g, '').split('/');
    var urlDate = '';
    if (parts.length > 1 && /^\d{4}-\d{2}-\d{2}$/.test(parts[1])) urlDate = parts[1];
    if (parts.length > 2 && /^\d{4}-\d{2}-\d{2}$/.test(parts[2])) urlDate = parts[2];
    
    var dateObj;
    if (urlDate) {
        dateObj = new Date(urlDate + "T00:00:00");
    } else {
        dateObj = new Date();
    }
    
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
        if (meta) {
            meta.setAttribute('content', seoDesc);
        }
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

            // Replace the main H1 heading text
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

