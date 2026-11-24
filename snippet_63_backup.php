if (!defined('ABSPATH')) exit;

/*
Use shortcode: [dynamic_horoscope]
Then Save Permalinks once.
*/

add_action('init', function () {
    // specific deep match for safety against 404s
    add_rewrite_rule(
        '^horoscope/([^/]+)/([^/]+)/?$',
        'index.php?pagename=horoscope&mr_path=$matches[1]/$matches[2]',
        'top'
    );
    // fallback base match
    add_rewrite_rule(
        '^horoscope/([^/]+)/?$',
        'index.php?pagename=horoscope&mr_path=$matches[1]',
        'top'
    );
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'mr_path';
    return $vars;
});

/**
 * HELPER: Render Zodiac Sign Landing Page (JS SEO & Link Rewriting)
 */
function mr_render_zodiac_landing() {
    ob_start();
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const allowedSigns = ['aries','taurus','gemini','cancer','leo','virgo','libra','scorpio','sagittarius','capricorn','aquarius','pisces'];
        
        // Rewrite links in the existing Elementor grid
        const links = document.querySelectorAll('.elementor-widget-image a, .elementor-image a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                // Extract sign from href (handles absolute, relative, and trailing slashes)
                const parts = href.replace(/\/$/, '').split('/');
                const lastPart = parts.pop().toLowerCase();
                
                if (allowedSigns.includes(lastPart)) {
                    link.setAttribute('href', '/horoscope/zodiac_sign/' + lastPart + '/');
                }
            }
        });

        // Also check captions/alt text if links weren't found or were different
        const containers = document.querySelectorAll('.elementor-widget-image, .wp-caption');
        containers.forEach(container => {
            const text = container.innerText.toLowerCase();
            const img = container.querySelector('img');
            const alt = img ? img.getAttribute('alt').toLowerCase() : '';
            
            allowedSigns.forEach(sign => {
                if (text.includes(sign) || alt.includes(sign)) {
                    const link = container.querySelector('a');
                    if (link) {
                        link.setAttribute('href', '/horoscope/zodiac_sign/' + sign + '/');
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * HELPER: Render Zodiac Sign Detail Page
 */
function mr_render_zodiac_detail($sign) {
    global $wpdb;

    // Zodiac Image Map (Reused from home panchang.php)
    $zodiac_images = [
        'aries' => 'https://merarashifal.com/wp-content/uploads/2026/03/Aries-Cropped.webp',
        'taurus' => 'https://merarashifal.com/wp-content/uploads/2026/03/Taurus-Cropped.webp',
        'gemini' => 'https://merarashifal.com/wp-content/uploads/2026/03/Gemini-Cropped.webp',
        'cancer' => 'https://merarashifal.com/wp-content/uploads/2026/03/Cancer-Cropped.webp',
        'leo' => 'https://merarashifal.com/wp-content/uploads/2026/03/Leo-Cropped.webp',
        'virgo' => 'https://merarashifal.com/wp-content/uploads/2026/03/virgo-Cropped.webp',
        'libra' => 'https://merarashifal.com/wp-content/uploads/2026/03/Libra-cropped.webp',
        'scorpio' => 'https://merarashifal.com/wp-content/uploads/2026/03/Scorpio-cropped.webp',
        'sagittarius' => 'https://merarashifal.com/wp-content/uploads/2026/03/Sagittarius-Cropped.webp',
        'capricorn' => 'https://merarashifal.com/wp-content/uploads/2026/03/Capricon-Cropped.webp',
        'aquarius' => 'https://merarashifal.com/wp-content/uploads/2026/03/Aquarius-Cropped.webp',
        'pisces' => 'https://merarashifal.com/wp-content/uploads/2026/03/Pisces-cropped.webp'
    ];
    $sign_key = strtolower($sign);
    $img_url = $zodiac_images[$sign_key] ?? '';

    // Fetch EN data
    $row_en = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM af_zodiac_base WHERE LOWER(sun_sign)=LOWER(%s) AND lang='en' LIMIT 1",
        $sign
    ));

    if (!$row_en) {
        return "<div class='mr-horoscope'><h1>Sign Not Found</h1><p>Sorry, we couldn't find details for " . esc_html($sign) . ".</p></div>";
    }

    // Fetch HI data
    $row_hi = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM af_zodiac_base WHERE zodiac_id=%d AND lang='hi' LIMIT 1",
        $row_en->zodiac_id
    ));

    ob_start();
    ?>
    <style>
        .af-zodiac-detail-wrapper {
            font-family: 'Noto Sans', system-ui, sans-serif;
            background: #fdf6e3;
            border-radius: 12px;
            border: 1px solid #e8dcc8;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .af-z-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #673AB7;
        }
        .af-z-img-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #673AB7;
            background: #fff;
            object-fit: cover;
        }
        .af-z-title-wrap { flex: 1; }
        .af-z-title-wrap h1 { margin: 0; font-size: 28px; color: #28527A; }
        .af-z-title-wrap p { margin: 5px 0 0; color: #FF6F3C; font-weight: 700; font-size: 18px; }

        .af-z-lang-toggle {
            display: flex;
            background: #ede7f6;
            padding: 4px;
            border-radius: 8px;
            width: fit-content;
            margin-bottom: 25px;
        }
        .af-z-lang-btn {
            background: transparent;
            border: none;
            color: #673AB7;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .af-z-lang-btn.active {
            background: #673AB7;
            color: #fff;
        }

        .af-zodiac-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin: 20px 0;
        }
        .af-zodiac-item {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #673AB7;
        }
        .af-z-label { font-size: 12px; font-weight: 700; color: #673AB7; text-transform: uppercase; margin-bottom: 6px; }
        .af-z-value { font-size: 17px; font-weight: 700; color: #FF6F3C; }

        .af-lang-content { display: none; animation: afFadeIn 0.4s ease; }
        .af-lang-content.active { display: block; }
        @keyframes afFadeIn { from { opacity: 0; } to { opacity: 1; } }

        .af-z-desc { line-height: 1.8; color: #444; font-size: 16px; margin-bottom: 25px; }
        
        @media (max-width: 600px) {
            .af-z-header { flex-direction: column; text-align: center; }
            .af-z-img-large { width: 80px; height: 80px; }
            .af-z-title-wrap h1 { font-size: 24px; }
            .af-zodiac-detail-wrapper { padding: 12px 8px; }
            .af-zodiac-item { padding: 12px; }
        }
    </style>

    <div class="af-zodiac-detail-wrapper">
        <div class="af-z-header">
            <?php if ($img_url): ?>
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($sign); ?>" class="af-z-img-large">
            <?php endif; ?>
            <div class="af-z-title-wrap">
                <h1><?php echo esc_html(ucfirst($sign)); ?> Personality & Traits</h1>
                <p>✨ <?php echo esc_html($row_en->value_1_text ?? ''); ?></p>
            </div>
        </div>

        <div class="af-z-lang-toggle">
            <button class="af-z-lang-btn active" data-lang="en">EN</button>
            <button class="af-z-lang-btn" data-lang="hi">हिन्दी</button>
        </div>

        <!-- ENGLISH CONTENT -->
        <div id="content-en" class="af-lang-content active">
            <div class="af-z-desc"><?php echo wp_kses_post($row_en->description ?? ''); ?></div>
            <div class="af-zodiac-details-grid">
                <?php for($i=1;$i<=13;$i++): 
                    $v="value_$i"; $t="value_".$i."_text";
                    if(!empty($row_en->$v) || !empty($row_en->$t)): ?>
                    <div class="af-zodiac-item">
                        <span class="af-z-label"><?php echo esc_html($row_en->$v); ?></span>
                        <span class="af-z-value"><?php echo esc_html($row_en->$t); ?></span>
                    </div>
                <?php endif; endfor; ?>
            </div>
        </div>

        <!-- HINDI CONTENT -->
        <?php if ($row_hi): ?>
        <div id="content-hi" class="af-lang-content">
            <div class="af-z-desc"><?php echo wp_kses_post($row_hi->description ?? ''); ?></div>
            <div class="af-zodiac-details-grid">
                <?php for($i=1;$i<=13;$i++): 
                    $v="value_$i"; $t="value_".$i."_text";
                    if(!empty($row_hi->$v) || !empty($row_hi->$t)): ?>
                    <div class="af-zodiac-item">
                        <span class="af-z-label"><?php echo esc_html($row_hi->$v); ?></span>
                        <span class="af-z-value"><?php echo esc_html($row_hi->$t); ?></span>
                    </div>
                <?php endif; endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 40px; text-align: center;">
            <a href="/horoscope/<?php echo esc_attr($sign); ?>" class="mr-btn" style="padding: 15px 35px; font-size: 18px; width: auto; display: inline-flex;">
                View Today's <?php echo ucfirst($sign); ?> Horoscope
            </a>
        </div>
    </div>

    <script>
    // Language Toggle Logic
    document.querySelectorAll('.af-z-lang-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang');
            document.querySelectorAll('.af-z-lang-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.af-lang-content').forEach(c => c.classList.remove('active'));
            const target = document.getElementById('content-' + lang);
            if (target) target.classList.add('active');
        });
    });

    // Link Rewriting Logic (maintain context)
    document.addEventListener("DOMContentLoaded", function() {
        const allowedSigns = ['aries','taurus','gemini','cancer','leo','virgo','libra','scorpio','sagittarius','capricorn','aquarius','pisces'];
        const links = document.querySelectorAll('.elementor-widget-image a, .elementor-image a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                const parts = href.replace(/\/$/, '').split('/');
                const lastPart = parts.pop().toLowerCase();
                if (allowedSigns.includes(lastPart)) {
                    link.setAttribute('href', '/horoscope/zodiac_sign/' + lastPart + '/');
                }
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('dynamic_horoscope', function () {

    global $wpdb;

    list($path, $parts) = mr_horoscope_get_active_path_parts();
    $first_part = !empty($parts[0]) ? sanitize_key($parts[0]) : '';

    if ($first_part === 'zodiac_sign' || $first_part === 'zodia_sign') {
        $sub_sign = !empty($parts[1]) ? sanitize_key($parts[1]) : '';
        if ($sub_sign) {
            return mr_render_zodiac_detail($sub_sign);
        } else {
            return mr_render_zodiac_landing();
        }
    }

    $sign = $first_part;

    $allowed = [
        'aries','taurus','gemini','cancer','leo','virgo',
        'libra','scorpio','sagittarius','capricorn','aquarius','pisces'
    ];

    if (empty($sign) || !in_array($sign, $allowed, true)) {
        // Base Page outputs dynamic JS SEO hook since content comes from page building.
        ob_start();
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var d = new Date();
            var formatter = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            var formattedDate = formatter.format(d);
            var seoTitle = "Aaj Ka Rashifal: Today Horoscope - " + formattedDate + " | MeraRashifal.com";
            var seoDesc = "Check Aaj Ka Rashifal for " + formattedDate + ". Get accurate Rashifal today and today horoscope for all zodiac signs. Plan your day with MeraRashifal.com's expert insights.";
            
            document.title = seoTitle;
            var meta = document.querySelector('meta[name="description"]');
            if (meta) { meta.setAttribute('content', seoDesc); } else {
                var newMeta = document.createElement('meta');
                newMeta.setAttribute('name', 'description'); newMeta.setAttribute('content', seoDesc);
                document.head.appendChild(newMeta);
            }
            var ogTitle = document.querySelector('meta[property="og:title"]');
            if(ogTitle) ogTitle.setAttribute('content', seoTitle);
            var ogDesc = document.querySelector('meta[property="og:description"]');
            if(ogDesc) ogDesc.setAttribute('content', seoDesc);

            // Scroll to the list of sunsigns
            setTimeout(function() {
                var links = document.querySelectorAll('a');
                var target = null;
                var allowedSigns = ['aries','taurus','gemini','cancer','leo','virgo','libra','scorpio','sagittarius','capricorn','aquarius','pisces'];
                for (var i = 0; i < links.length; i++) {
                    var href = links[i].getAttribute('href');
                    if (href) {
                        var parts = href.replace(/\/$/, '').split('/');
                        var lastPart = parts.pop().toLowerCase();
                        if (allowedSigns.includes(lastPart)) {
                            target = links[i].closest('.elementor-widget') || links[i];
                            break;
                        }
                    }
                }
                
                if (target) {
                    var y = target.getBoundingClientRect().top + window.pageYOffset - 100;
                    window.scrollTo({top: y, behavior: 'smooth'});
                }
            }, 300);
        });
        </script>
        <?php
        return ob_get_clean();
    }

    $mode = 'daily';
    $selected_date = current_time('Y-m-d');

    if (!empty($parts[1])) {
        if ($parts[1] === 'weekly-horoscope') {
            $mode = 'weekly';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $parts[1])) {
            $selected_date = $parts[1];
        }
    }

    $today = current_time('Y-m-d');
    $yesterday_link = date('Y-m-d', strtotime($selected_date.' -1 day'));
    $tomorrow_link  = date('Y-m-d', strtotime($selected_date.' +1 day'));

    // --- CACHED DATA FETCHING ---
    // 1. Base Details
    $cache_base = "mr_zb_base_" . md5($sign);
    $base = get_transient($cache_base);
    if ($base === false) {
        $base = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_zodiac_base WHERE LOWER(sun_sign)=LOWER(%s) LIMIT 1", $sign));
        if ($base) set_transient($cache_base, $base, 12 * HOUR_IN_SECONDS);
    }

    // 2. Daily Horoscope (EN & HI)
    $cache_daily_en = "mr_dh_en_" . md5($sign . $selected_date);
    $daily = get_transient($cache_daily_en);
    if ($daily === false) {
        $daily = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s AND language='en' LIMIT 1", $sign, $selected_date));
        if (!$daily) { // Fallback
            $daily = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s LIMIT 1", $sign, $selected_date));
        }
        if ($daily) set_transient($cache_daily_en, $daily, 12 * HOUR_IN_SECONDS);
    }

    $cache_daily_hi = "mr_dh_hi_" . md5($sign . $selected_date);
    $daily_hi = get_transient($cache_daily_hi);
    if ($daily_hi === false) {
        $daily_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s AND language='hi' LIMIT 1", $sign, $selected_date));
        if ($daily_hi) set_transient($cache_daily_hi, $daily_hi, 12 * HOUR_IN_SECONDS);
    }

    // 3. Weekly Horoscope (EN & HI)
    $cache_weekly_en = "mr_wh_en_" . md5($sign . $today);
    $weekly = get_transient($cache_weekly_en);
    if ($weekly === false) {
        $weekly = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s AND language='en' ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        if (!$weekly) { // Fallback
            $weekly = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        }
        if ($weekly) set_transient($cache_weekly_en, $weekly, 12 * HOUR_IN_SECONDS);
    }

    $cache_weekly_hi = "mr_wh_hi_" . md5($sign . $today);
    $weekly_hi = get_transient($cache_weekly_hi);
    if ($weekly_hi === false) {
        $weekly_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s AND language='hi' ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        if ($weekly_hi) set_transient($cache_weekly_hi, $weekly_hi, 12 * HOUR_IN_SECONDS);
    }

    ob_start();
?>

<style>
:root {
    --primary-purple: #3B1E54;
    --secondary-orange: #D65A31;
    --accent-red: #A91D3A;
    --highlight-yellow: #FFD369;
    --neutral-text: #222831;
    --bg-sober: #FFFBEB;
}

.af-z-lang-toggle { display: flex; background: #ede7f6; padding: 4px; border-radius: 8px; width: fit-content; margin: 0 auto 20px auto; }
.af-z-lang-btn {
    background: transparent; border: none; color: #673AB7; padding: 8px 16px; border-radius: 6px; cursor: pointer;
    font-size: 14px; font-weight: 700; transition: all 0.2s;
}
.af-z-lang-btn.active { background: #673AB7; color: #fff; }
.af-lang-content { display: none; animation: afFadeIn 0.4s ease; }
.af-lang-content.active { display: block; }
@keyframes afFadeIn { from { opacity: 0; } to { opacity: 1; } }

.mr-horoscope {
    background-color: var(--bg-sober);
    font-family: 'Noto Sans', sans-serif;
    color: var(--neutral-text);
    padding: 10px 5px;
    line-height: 1.8;
    max-width: 1000px;
    margin: 0 auto;
}
@media (min-width: 480px) {
    .mr-horoscope { padding: 15px; }
}

h1.mr-title {
    color: #FF8C00 !important;
    font-family: 'Noto Sans', sans-serif;
    border-bottom: 4px solid var(--highlight-yellow);
    padding-bottom: 8px;
    text-transform: capitalize;
}

.mr-horoscope h2 {
    color: var(--primary-purple);
    font-family: 'Noto Sans', sans-serif;
    font-size: 24px;
    margin-top: 30px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0d9c8;
    padding-bottom: 5px;
}

.mr-date-nav {
    display: flex;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.mr-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    border: 0;
    position: relative;
    overflow: hidden;
    border-radius: 10rem;
    transition: all 0.2s;
    font-weight: 600;
    font-size: 13px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    color: rgb(37, 37, 37) !important;
    z-index: 0;
    box-shadow: 0 0px 7px -5px rgba(0, 0, 0, 0.5);
    flex: 1 1 auto;
    white-space: nowrap;
}

.mr-btn:hover {
    color: rgb(33, 0, 85) !important;
}

.mr-btn:active {
    transform: scale(0.97);
}

.mr-btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: #fff;
    z-index: -2;
    transition: background 0.2s;
}

.mr-btn:hover::before {
    background: rgb(255, 245, 245);
}

.mr-btn::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: linear-gradient(
        90deg,
        rgba(255, 128, 128, 1) 0%,
        rgba(255, 179, 102, 1) 49%,
        rgba(204, 153, 255, 1) 100%
    );
    border-radius: 40rem;
    width: 8rem;
    height: 8rem;
    transition: 0.4s;
    filter: blur(15px);
    animation: mrEffect infinite 3s linear;
    opacity: 0.6;
    z-index: -1;
}

.mr-btn:hover::after {
    width: 6rem;
    height: 6rem;
}

@keyframes mrEffect {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

@media (min-width: 480px) {
    .mr-date-nav {
        justify-content: flex-start;
        gap: 10px;
    }
    .mr-btn {
        flex: none;
        padding: 10px 18px;
        font-size: 14px;
    }
}

.mr-calendar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    background: #fff;
    padding: 12px 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
@media (min-width: 480px) {
    .mr-calendar { padding: 15px; }
}
.mr-calendar input[type="date"] {
    flex: 1;
    min-width: 0;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Noto Sans', sans-serif;
    font-size: 14px;
    color: var(--neutral-text);
    outline: none;
}
.mr-calendar .mr-btn {
    flex: none;
}
.mr-calendar input[type="date"]:focus {
    border-color: var(--primary-purple);
}

.mr-sector {
    background: #fff;
    padding: 15px 10px;
    margin-bottom: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
@media (min-width: 480px) {
    .mr-sector { padding: 20px; }
}

.mr-sector-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 18px;
    color: var(--primary-purple);
}

.mr-orange {
    color: var(--secondary-orange);
    font-weight: bold;
    background: var(--bg-sober);
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 14px;
}

.mr-score-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}
.mr-progress-bg {
    width: 60px;
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    display: block; /* always show */
}
@media (min-width: 480px) {
    .mr-progress-bg {
        width: 120px;
    }
}
.mr-progress-fill {
    height: 100%;
    background-color: var(--secondary-orange);
    border-radius: 4px;
}

.mr-summary {
    background: var(--highlight-yellow);
    color: var(--neutral-text);
    padding: 15px 12px;
    border-radius: 10px;
    font-weight: 500;
    margin-top: 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
@media (min-width: 480px) {
    .mr-summary { padding: 20px; }
}

.mr-traits {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    list-style-type: none;
    padding: 0;
    margin: 0;
}
@media (min-width: 768px) {
    .mr-traits {
        grid-template-columns: repeat(3, 1fr);
    }
}
.mr-traits li {
    background: #fff;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    border-left: 5px solid var(--secondary-orange);
    margin-bottom: 0px; 
    text-align: center;
    font-size: 14px;
}
.mr-colon {
    display: none;
}
@media (min-width: 480px) {
    .mr-traits li {
        text-align: left;
    }
    .mr-colon {
        display: inline;
    }
}
.mr-traits li strong {
    color: var(--primary-purple);
    font-size: 14px;
}

h1.mr-title {
    font-size: 28px !important;
    font-weight: 900 !important;
    line-height: 1.3 !important;
    margin-top: 0;
    margin-bottom: 20px;
    color: #FF8C00 !important;
    text-align: center;
    display: table;
    margin-left: auto;
    margin-right: auto;
}
span.mr-zodiac-dates {
    display: block;
    margin-top: 5px;
    color: #FF8C00 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
}
@media (min-width: 768px) {
    h1.mr-title {
        font-size: 34px !important;
        text-align: left;
        margin-left: 0;
        margin-right: auto;
    }
    span.mr-zodiac-dates {
        display: inline;
        margin-left: 10px;
        margin-top: 0;
        font-size: 24px !important;
    }
}
</style>

<?php
$zodiac_dates = [
    'aries' => 'March 21 to April 19',
    'taurus' => 'April 20 to May 20',
    'gemini' => 'May 21 to June 20',
    'cancer' => 'June 21 to July 22',
    'leo' => 'July 23 to August 22',
    'virgo' => 'August 23 to September 22',
    'libra' => 'September 23 to October 22',
    'scorpio' => 'October 23 to November 21',
    'sagittarius' => 'November 22 to December 21',
    'capricorn' => 'December 22 to January 19',
    'aquarius' => 'January 20 to February 18',
    'pisces' => 'February 19 to March 20'
];
$date_range = $zodiac_dates[$sign] ?? '';
?>

<div class="mr-horoscope" id="mr-horoscope-content">

<h1 class="mr-title">
    <?php echo esc_html(ucfirst($sign)); ?> Horoscope 
    <?php if ($date_range): ?>
        <span class="mr-zodiac-dates">(<?php echo esc_html($date_range); ?>)</span>
    <?php endif; ?>
</h1>

<div class="mr-date-nav">
<a class="mr-btn" id="mr-btn-yesterday" href="/horoscope/<?php echo $sign; ?>/<?php echo $yesterday_link; ?>">Yesterday</a>
<a class="mr-btn" id="mr-btn-today" href="/horoscope/<?php echo $sign; ?>">Today</a>
<a class="mr-btn" id="mr-btn-tomorrow" href="/horoscope/<?php echo $sign; ?>/<?php echo $tomorrow_link; ?>">Tomorrow</a>
<a class="mr-btn" href="/horoscope/<?php echo $sign; ?>/weekly-horoscope">This Week</a>
</div>

<?php if ($mode !== 'weekly'): ?>
<div class="mr-calendar">
<input type="date" id="mrDate" value="<?php echo esc_attr($selected_date); ?>">
<button class="mr-btn" onclick="mrLoadDate('<?php echo esc_js($sign); ?>')">Check</button>
</div>
<?php endif; ?>

<?php
$fields = [
'status'=>'Status',
'career'=>'Career',
'relationship'=>'Love',
'finances'=>'Finance',
'health'=>'Health',
'travel'=>'Travel',
'family'=>'Family',
'friends'=>'Friends'
];
?>

<?php
$render_block = function($data, $mode, $selected_date, $today, $fields) {
    if (empty($data)) return '';
    ob_start();
    if ($mode === 'weekly'): ?>
        <h2>This Week's Horoscope (<?php echo esc_html(date_i18n('d M', strtotime($data->week_start_date))); ?> - <?php echo esc_html(date_i18n('d M, Y', strtotime($data->week_end_date))); ?>)</h2>
        <?php foreach($fields as $k=>$label):
            $score = $data->{$k.'_score'} ?? '';
            $text = $data->{$k.'_text'} ?? '';
            if($score || $text): 
                $s_val = (int)$score;
                $bar_color = ($s_val <= 20) ? 'var(--accent-red)' : (($s_val <= 49) ? 'var(--secondary-orange)' : (($s_val <= 69) ? 'var(--highlight-yellow)' : '#4CAF50'));
        ?>
        <div class="mr-sector">
            <div class="mr-sector-head">
                <strong><?php echo esc_html($label); ?></strong>
                <div class="mr-score-wrap">
                    <?php if(is_numeric($score) && $score > 0): ?>
                    <div class="mr-progress-bg"><div class="mr-progress-fill" style="width: <?php echo esc_attr(min(100, $s_val)); ?>%; background-color: <?php echo esc_attr($bar_color); ?>;"></div></div>
                    <?php endif; ?>
                    <span class="mr-orange"><?php echo esc_html($score); ?>/100</span>
                </div>
            </div>
            <p><?php echo esc_html($text); ?></p>
        </div>
        <?php endif; endforeach; ?>
        
        <?php if (!empty($data->lucky_color) || !empty($data->lucky_number_1)): ?>
        <div class="mr-sector">
            <div class="mr-sector-head"><strong>Lucky Indicators</strong></div>
            <ul class="mr-traits">
                <?php if(!empty($data->lucky_color)): ?>
                <li><strong>Lucky Color<span class="mr-colon">:</span></strong> <?php echo esc_html($data->lucky_color); ?>
                    <?php if(!empty($data->lucky_color_code)): ?>
                    <span style="display:inline-block; width:16px; height:16px; background-color:<?php echo esc_attr($data->lucky_color_code); ?>; border-radius:50%; margin-left:8px; vertical-align:middle; border:1px solid #ddd;"></span>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
                <?php if(!empty($data->lucky_number_1)): ?>
                <li><strong>Lucky Numbers<span class="mr-colon">:</span></strong> <?php echo esc_html($data->lucky_number_1); ?>
                    <?php if(!empty($data->lucky_number_2)): echo ', ' . esc_html($data->lucky_number_2); endif; ?>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        <p class="mr-summary"><?php echo esc_html($data->total_text ?? ''); ?></p>
    <?php else: ?>
        <h2 id="mr-day-title"><?php echo ($selected_date === $today) ? 'Today Horoscope' : esc_html(date_i18n('d F Y', strtotime($selected_date))); ?></h2>
        <?php if (!empty($data->lucky_color) || !empty($data->lucky_number_1)): ?>
        <div class="mr-sector">
            <div class="mr-sector-head"><strong>Lucky Indicators</strong></div>
            <ul class="mr-traits">
                <?php if(!empty($data->lucky_color)): ?>
                <li><strong>Lucky Color<span class="mr-colon">:</span></strong> <?php echo esc_html($data->lucky_color); ?>
                    <?php if(!empty($data->lucky_color_code)): ?>
                    <span style="display:inline-block; width:16px; height:16px; background-color:<?php echo esc_attr($data->lucky_color_code); ?>; border-radius:50%; margin-left:8px; vertical-align:middle; border:1px solid #ddd;"></span>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
                <?php if(!empty($data->lucky_number_1)): ?>
                <li><strong>Lucky Numbers<span class="mr-colon">:</span></strong> <?php echo esc_html($data->lucky_number_1); ?>
                    <?php if(!empty($data->lucky_number_2)): echo ', ' . esc_html($data->lucky_number_2); endif; ?>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php foreach($fields as $k=>$label):
            $score = $data->{$k.'_score'} ?? '';
            $text = $data->{$k.'_response'} ?? '';
            if($score || $text): 
                $s_val = (int)$score;
                $bar_color = ($s_val <= 20) ? 'var(--accent-red)' : (($s_val <= 49) ? 'var(--secondary-orange)' : (($s_val <= 69) ? 'var(--highlight-yellow)' : '#4CAF50'));
        ?>
        <div class="mr-sector">
            <div class="mr-sector-head">
                <strong><?php echo esc_html($label); ?></strong>
                <div class="mr-score-wrap">
                    <?php if(is_numeric($score) && $score > 0): ?>
                    <div class="mr-progress-bg"><div class="mr-progress-fill" style="width: <?php echo esc_attr(min(100, $s_val)); ?>%; background-color: <?php echo esc_attr($bar_color); ?>;"></div></div>
                    <?php endif; ?>
                    <span class="mr-orange"><?php echo esc_html($score); ?>/100</span>
                </div>
            </div>
            <p><?php echo esc_html($text); ?></p>
        </div>
        <?php endif; endforeach; ?>
        <p class="mr-summary"><?php echo esc_html($data->total_response ?? ''); ?></p>
    <?php endif;
    return ob_get_clean();
};
?>

<?php if (($mode === 'weekly' && !empty($weekly_hi)) || ($mode !== 'weekly' && !empty($daily_hi))): ?>
<div class="af-z-lang-toggle" style="margin-bottom:20px; display:flex;">
    <button class="af-z-lang-btn active" data-lang="en">EN</button>
    <button class="af-z-lang-btn" data-lang="hi">हिन्दी</button>
</div>
<?php endif; ?>

<div id="content-en" class="af-lang-content active">
    <?php echo $render_block($mode === 'weekly' ? $weekly : $daily, $mode, $selected_date, $today, $fields); ?>
</div>

<?php if (($mode === 'weekly' && !empty($weekly_hi)) || ($mode !== 'weekly' && !empty($daily_hi))): ?>
<div id="content-hi" class="af-lang-content">
    <?php echo $render_block($mode === 'weekly' ? $weekly_hi : $daily_hi, $mode, $selected_date, $today, $fields); ?>
</div>
<?php endif; ?>

<?php if($base): ?>
<h2>About <?php echo ucfirst($sign); ?></h2>
<p><?php echo esc_html($base->description ?? ''); ?></p>
<ul class="mr-traits">
<?php for($i=1;$i<=13;$i++):
$v="value_$i"; $t="value_".$i."_text";
if(!empty($base->$v)||!empty($base->$t)): ?>
<li><strong><?php echo esc_html($base->$v ?? ''); ?></strong><span class="mr-colon">:</span> <?php echo esc_html($base->$t ?? ''); ?></li>
<?php endif; endfor; ?>
</ul>
<?php endif; ?>

</div>

<script>
function mrLoadDate(sign){
let d=document.getElementById('mrDate').value;
if(d){ window.location='/horoscope/'+sign+'/'+d; }
}

// Ensure timezone synchronization using client device clock natively and safely
document.addEventListener("DOMContentLoaded", function() {
    var d = new Date();
    var localYear = d.getFullYear();
    var localMonth = String(d.getMonth() + 1).padStart(2, '0');
    var localDay = String(d.getDate()).padStart(2, '0');
    var localDate = localYear + '-' + localMonth + '-' + localDay;
    
    var sign = "<?php echo esc_js($sign); ?>";
    
    // ----- LANGUAGE TOGGLE LOGIC -----
    var langBtns = document.querySelectorAll('.af-z-lang-btn');
    if (langBtns.length > 0) {
        var urlParams = new URLSearchParams(window.location.search);
        var activeLang = urlParams.get('lang') || 'en';
        
        function activateLang(lang) {
            langBtns.forEach(function(b) { b.classList.remove('active'); });
            var targetBtn = document.querySelector('.af-z-lang-btn[data-lang="'+lang+'"]');
            if(targetBtn) targetBtn.classList.add('active');
            
            var contents = document.querySelectorAll('.af-lang-content');
            contents.forEach(function(c) { c.classList.remove('active'); });
            var targetContent = document.getElementById('content-' + lang);
            if(targetContent) targetContent.classList.add('active');
        }
        
        activateLang(activeLang); // Set on load based on URL

        langBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var lang = this.getAttribute('data-lang');
                activateLang(lang);
                
                var newUrl = new URL(window.location.href);
                newUrl.searchParams.set('lang', lang);
                window.history.pushState({}, '', newUrl);
            });
        });
    }
    // ---------------------------------

    
    // Auto-update links so navigation flawlessly mirrors user's real regional timestamp
    var btnYesterday = document.getElementById("mr-btn-yesterday");
    var btnToday = document.getElementById("mr-btn-today");
    var btnTomorrow = document.getElementById("mr-btn-tomorrow");
    
    if (btnToday) {
        btnToday.href = "/horoscope/" + sign + "/" + localDate;
    }
    
    var yesterdayDate = new Date(d);
    yesterdayDate.setDate(d.getDate() - 1);
    if (btnYesterday) {
        var yM = String(yesterdayDate.getMonth() + 1).padStart(2, '0');
        var yD = String(yesterdayDate.getDate()).padStart(2, '0');
        btnYesterday.href = "/horoscope/" + sign + "/" + yesterdayDate.getFullYear() + "-" + yM + "-" + yD;
    }
    
    var tomorrowDate = new Date(d);
    tomorrowDate.setDate(d.getDate() + 1);
    if (btnTomorrow) {
        var tm = String(tomorrowDate.getMonth() + 1).padStart(2, '0');
        var td = String(tomorrowDate.getDate()).padStart(2, '0');
        btnTomorrow.href = "/horoscope/" + sign + "/" + tomorrowDate.getFullYear() + "-" + tm + "-" + td;
    }

    <?php if ($mode !== 'weekly'): ?>
        <?php if (empty($parts[1])): ?>
            // Route /horoscope/<sign>/ detected without explicit parameters. Assess server baseline vs user reality.
            var serverDate = "<?php echo esc_js($selected_date); ?>";
            if (serverDate !== localDate) {
                window.location.replace('/horoscope/' + sign + '/' + localDate);
            }
        <?php else: ?>
            // We have a direct path parameter! Update UI label securely if their local date aligns cleanly.
            var currentlyViewing = "<?php echo esc_js($selected_date); ?>";
            if (currentlyViewing === localDate) {
                var titleEl = document.getElementById("mr-day-title");
                if (titleEl) {
                    titleEl.innerText = "Today Horoscope";
                }
            }
        <?php endif; ?>
    <?php endif; ?>

    // ----- SEO OVERRIDE USING DEVICE DATE -----
    var zodiacMap = {
        'aries': 'मेष', 'taurus': 'वृषभ', 'gemini': 'मिथुन', 'cancer': 'कर्क',
        'leo': 'सिंह', 'virgo': 'कन्या', 'libra': 'तुला', 'scorpio': 'वृश्चिक',
        'sagittarius': 'धनु', 'capricorn': 'मकर', 'aquarius': 'कुंभ', 'pisces': 'मीन'
    };
    
    var mode = "<?php echo esc_js($mode); ?>";
    
    // Determine the active date purely on JS explicit params, falling back to local time if explicit isn't set.
    var urlDate = '';
    var partsPath = window.location.pathname.replace(/^\/|\/$/g, '').split('/');
    if (partsPath.length > 2 && /^\d{4}-\d{2}-\d{2}$/.test(partsPath[2])) { urlDate = partsPath[2]; }
    
    var effectiveDate = urlDate ? new Date(urlDate + "T00:00:00") : new Date(); // use local clock explicitly if "today" root
    var formatter = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    var formattedDate = formatter.format(effectiveDate);
    var formattedMonth = new Intl.DateTimeFormat('en-US', { month: 'long' }).format(effectiveDate);
    var formattedYear = effectiveDate.getFullYear();
    
    var zodEn = sign.charAt(0).toUpperCase() + sign.slice(1);
    var zodHi = zodiacMap[sign.toLowerCase()];

    var seoTitle = "";
    var seoDesc = "";

    if (mode === 'weekly') {
        seoTitle = "Weekly Horoscope: " + zodEn + " (" + zodHi + ") Rashifal - " + formattedMonth + " " + formattedYear + " | MeraRashifal.com";
        seoDesc = "Your weekly " + zodEn + " (" + zodHi + ") horoscope for this week of " + formattedMonth + ". Get detailed weekly Rashifal today and astrological predictions to stay ahead with MeraRashifal.com.";
    } else {
        seoTitle = zodEn + " (" + zodHi + ") Aaj Ka Rashifal - Today Horoscope " + formattedDate + " | MeraRashifal.com";
        seoDesc = "Read your " + zodEn + " (" + zodHi + ") Aaj Ka Rashifal for " + formattedDate + ". Explore your today horoscope in Hindi/English, covering health, wealth, and career on MeraRashifal.com.";
    }

    document.title = seoTitle;
    var metaObj = document.querySelector('meta[name="description"]');
    if (metaObj) { metaObj.setAttribute('content', seoDesc); } else {
        var nMeta = document.createElement('meta');
        nMeta.setAttribute('name', 'description'); nMeta.setAttribute('content', seoDesc);
        document.head.appendChild(nMeta);
    }
    var ogTitleObj = document.querySelector('meta[property="og:title"]');
    if(ogTitleObj) ogTitleObj.setAttribute('content', seoTitle);
    var ogDescObj = document.querySelector('meta[property="og:description"]');
    if(ogDescObj) ogDescObj.setAttribute('content', seoDesc);

    // Scroll to the content
    setTimeout(function() {
        var content = document.getElementById("mr-horoscope-content");
        if (content) {
            var y = content.getBoundingClientRect().top + window.pageYOffset - 80;
            window.scrollTo({top: y, behavior: 'smooth'});
        }
    }, 300);
});
</script>
<?php
    return ob_get_clean();
});

/* ====================================================
   SEO & PATH INTEGRATION (YOAST SERVER-SIDE)
==================================================== */
function mr_horoscope_get_active_path_parts() {
    $path = get_query_var('mr_path') ?: get_query_var('zodiac_sign');
    if (!$path && !empty($_SERVER['REQUEST_URI'])) {
        $uri_path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $site_path = trim((string) parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path !== '' && strpos($uri_path, $site_path) === 0) {
            $uri_path = trim(substr($uri_path, strlen($site_path)), '/');
        }
        if (preg_match('#^horoscope(?:/(.*))?$#i', $uri_path, $m)) {
            $path = $m[1] ?? '';
        }
    }
    $path = trim((string)$path, '/');
    $parts = array_values(array_filter(explode('/', $path)));
    return [$path, $parts];
}

function mr_horoscope_seo_zodiac_map() {
    return [
        'aries' => 'मेष', 'taurus' => 'वृषभ', 'gemini' => 'मिथुन', 'cancer' => 'कर्क',
        'leo' => 'सिंह', 'virgo' => 'कन्या', 'libra' => 'तुला', 'scorpio' => 'वृश्चिक',
        'sagittarius' => 'धनु', 'capricorn' => 'मकर', 'aquarius' => 'कुंभ', 'pisces' => 'मीन'
    ];
}

function mr_horoscope_get_seo_data() {
    list($path, $parts) = mr_horoscope_get_active_path_parts();
    
    $sign = !empty($parts[0]) ? strtolower($parts[0]) : '';
    $mode = 'daily';
    $date_val = '';
    $is_zodiac_sign_path = false;

    if ($sign === 'zodiac_sign' || $sign === 'zodia_sign') {
        $is_zodiac_sign_path = true;
        $sign = !empty($parts[1]) ? strtolower($parts[1]) : '';
    } elseif (!empty($parts[1])) {
        if ($parts[1] === 'weekly-horoscope') {
            $mode = 'weekly';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $parts[1])) {
            $date_val = $parts[1];
        }
    }
    
    $zodiac_images = [
        'aries'       => 'https://merarashifal.com/wp-content/uploads/2026/03/Aries-Cropped.webp',
        'taurus'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Taurus-Cropped.webp',
        'gemini'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Gemini-Cropped.webp',
        'cancer'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Cancer-Cropped.webp',
        'leo'         => 'https://merarashifal.com/wp-content/uploads/2026/03/Leo-Cropped.webp',
        'virgo'       => 'https://merarashifal.com/wp-content/uploads/2026/03/virgo-Cropped.webp',
        'libra'       => 'https://merarashifal.com/wp-content/uploads/2026/03/Libra-cropped.webp',
        'scorpio'     => 'https://merarashifal.com/wp-content/uploads/2026/03/Scorpio-cropped.webp',
        'sagittarius' => 'https://merarashifal.com/wp-content/uploads/2026/03/Sagittarius-Cropped.webp',
        'capricorn'   => 'https://merarashifal.com/wp-content/uploads/2026/03/Capricon-Cropped.webp',
        'aquarius'    => 'https://merarashifal.com/wp-content/uploads/2026/03/Aquarius-Cropped.webp',
        'pisces'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Pisces-cropped.webp'
    ];

    $default_image = 'https://merarashifal.com/wp-content/uploads/2026/03/Aries-Cropped.webp';
    $data = [
        'title'     => '',
        'desc'      => '',
        'canonical' => home_url('/horoscope/'),
        'image'     => $default_image,
        'url'       => home_url('/horoscope/'),
    ];
    $zodiac_map = mr_horoscope_seo_zodiac_map();

    if ($is_zodiac_sign_path) {
        if (empty($sign)) {
            $data['title']     = "Zodiac Signs Personality & Traits - All 12 Signs | MeraRashifal.com";
            $data['desc']      = "Discover the personality traits, strengths, and weaknesses of all 12 zodiac signs. Detailed astrological insights into your character on MeraRashifal.com.";
            $data['canonical'] = home_url('/horoscope/zodiac_sign/');
            $data['url']       = home_url('/horoscope/zodiac_sign/');
        } else {
            $zodiac_en = ucfirst($sign);
            $zodiac_hi = $zodiac_map[$sign] ?? '';
            $data['title']     = "{$zodiac_en} ({$zodiac_hi}) Personality Traits & Characteristics | MeraRashifal.com";
            $data['desc']      = "Learn all about {$zodiac_en} ({$zodiac_hi}) personality traits, core characteristics, and zodiac sign facts. Deep dive into the nature of {$zodiac_en} on MeraRashifal.com.";
            $data['canonical'] = home_url('/horoscope/zodiac_sign/' . $sign . '/');
            $data['url']       = home_url('/horoscope/zodiac_sign/' . $sign . '/');
            if (isset($zodiac_images[$sign])) {
                $data['image'] = $zodiac_images[$sign];
            }
        }
        return $data;
    }
    
    if (empty($sign) || !array_key_exists($sign, $zodiac_map)) {
        $seo_date = current_time('F j, Y'); 
        $data['title']     = "Aaj Ka Rashifal: Today Horoscope - {$seo_date} | MeraRashifal.com";
        $data['desc']      = "Check Aaj Ka Rashifal for {$seo_date}. Get accurate Rashifal today and today horoscope for all zodiac signs. Plan your day with MeraRashifal.com's expert insights.";
        $data['canonical'] = home_url('/horoscope/');
        $data['url']       = home_url('/horoscope/');
        return $data;
    }
    
    $zodiac_en = ucfirst($sign);
    $zodiac_hi = $zodiac_map[$sign];
    if (isset($zodiac_images[$sign])) {
        $data['image'] = $zodiac_images[$sign];
    }
    
    if ($mode === 'weekly') {
        $seo_month = current_time('F');
        $seo_year  = current_time('Y');
        $data['title']     = "Weekly Horoscope: {$zodiac_en} ({$zodiac_hi}) Rashifal - {$seo_month} {$seo_year} | MeraRashifal.com";
        $data['desc']      = "Your weekly {$zodiac_en} ({$zodiac_hi}) horoscope for this week of {$seo_month}. Get detailed weekly Rashifal today and astrological predictions to stay ahead with MeraRashifal.com.";
        $data['canonical'] = home_url('/horoscope/' . $sign . '/weekly-horoscope/');
        $data['url']       = home_url('/horoscope/' . $sign . '/weekly-horoscope/');
    } else {
        $seo_date = current_time('F j, Y');
        $date_suffix = '';
        if ($date_val && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_val)) {
            $seo_date = date('F j, Y', strtotime($date_val));
            $date_suffix = $date_val . '/';
        }
        $data['title']     = "{$zodiac_en} ({$zodiac_hi}) Aaj Ka Rashifal - Today Horoscope {$seo_date} | MeraRashifal.com";
        $data['desc']      = "Read your {$zodiac_en} ({$zodiac_hi}) Aaj Ka Rashifal for {$seo_date}. Explore your today horoscope in Hindi/English, covering health, wealth, and career on MeraRashifal.com.";
        $data['canonical'] = home_url('/horoscope/' . $sign . '/' . $date_suffix);
        $data['url']       = home_url('/horoscope/' . $sign . '/' . $date_suffix);
    }
    
    return $data;
}

function mr_is_horoscope_request() {
    if (is_page('horoscope') || get_query_var('pagename') === 'horoscope' || get_query_var('mr_path') || get_query_var('zodiac_sign')) {
        return true;
    }
    if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/horoscope') !== false) {
        return true;
    }
    return false;
}

// Yoast Title
add_filter('wpseo_title', function($title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

// Pre get document title fallback
add_filter('pre_get_document_title', function($title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

// Yoast Meta Description
add_filter('wpseo_metadesc', function($desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $desc;
}, 100);

// Yoast Canonical URL
add_filter('wpseo_canonical', function($canonical) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['canonical'])) return $seo['canonical'];
    }
    return $canonical;
}, 100);

// OpenGraph & Twitter
add_filter('wpseo_opengraph_title', function($og_title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $og_title;
}, 100);

add_filter('wpseo_opengraph_desc', function($og_desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $og_desc;
}, 100);

add_filter('wpseo_opengraph_url', function($og_url) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['url'])) return $seo['url'];
    }
    return $og_url;
}, 100);

add_filter('wpseo_opengraph_image', function($og_image) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $og_image;
}, 100);

add_filter('wpseo_twitter_title', function($tw_title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $tw_title;
}, 100);

add_filter('wpseo_twitter_description', function($tw_desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $tw_desc;
}, 100);

add_filter('wpseo_twitter_image', function($tw_image) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $tw_image;
}, 100);

// Demote widget 5b5e0fe to h2 when viewing sign subpages
add_filter('elementor/widget/render_content', function($widget_content, $widget) {
    if ($widget->get_id() === '5b5e0fe') {
        list($path, $parts) = mr_horoscope_get_active_path_parts();
        if (!empty($parts)) {
            $widget_content = preg_replace('/<h1(\s+[^>]*)?>/i', '<h2$1>', $widget_content);
            $widget_content = preg_replace('/<\/h1>/i', '</h2>', $widget_content);
        }
    }
    return $widget_content;
}, 10, 2);

/* ====================================================
   SEO & PATH INTEGRATION (YOAST SERVER-SIDE)
==================================================== */
if (!function_exists('mr_horoscope_get_active_path_parts')) {
function mr_horoscope_get_active_path_parts() {
    $path = get_query_var('mr_path') ?: get_query_var('zodiac_sign');
    if (!$path && !empty($_SERVER['REQUEST_URI'])) {
        $uri_path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $site_path = trim((string) parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path !== '' && strpos($uri_path, $site_path) === 0) {
            $uri_path = trim(substr($uri_path, strlen($site_path)), '/');
        }
        if (preg_match('#^horoscope(?:/(.*))?$#i', $uri_path, $m)) {
            $path = $m[1] ?? '';
        }
    }
    $path = trim((string)$path, '/');
    $parts = array_values(array_filter(explode('/', $path)));
    return [$path, $parts];
}
}

if (!function_exists('mr_horoscope_seo_zodiac_map')) {
function mr_horoscope_seo_zodiac_map() {
    return [
        'aries' => 'मेष', 'taurus' => 'वृषभ', 'gemini' => 'मिथुन', 'cancer' => 'कर्क',
        'leo' => 'सिंह', 'virgo' => 'कन्या', 'libra' => 'तुला', 'scorpio' => 'वृश्चिक',
        'sagittarius' => 'धनु', 'capricorn' => 'मकर', 'aquarius' => 'कुंभ', 'pisces' => 'मीन'
    ];
}
}

if (!function_exists('mr_horoscope_get_seo_data')) {
function mr_horoscope_get_seo_data() {
    list($path, $parts) = mr_horoscope_get_active_path_parts();
    
    $sign = !empty($parts[0]) ? strtolower($parts[0]) : '';
    $mode = 'daily';
    $date_val = '';
    $is_zodiac_sign_path = false;

    if ($sign === 'zodiac_sign' || $sign === 'zodia_sign') {
        $is_zodiac_sign_path = true;
        $sign = !empty($parts[1]) ? strtolower($parts[1]) : '';
    } elseif (!empty($parts[1])) {
        if ($parts[1] === 'weekly-horoscope') {
            $mode = 'weekly';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $parts[1])) {
            $date_val = $parts[1];
        }
    }
    
    $zodiac_images = [
        'aries'       => 'https://merarashifal.com/wp-content/uploads/2026/03/Aries-Cropped.webp',
        'taurus'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Taurus-Cropped.webp',
        'gemini'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Gemini-Cropped.webp',
        'cancer'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Cancer-Cropped.webp',
        'leo'         => 'https://merarashifal.com/wp-content/uploads/2026/03/Leo-Cropped.webp',
        'virgo'       => 'https://merarashifal.com/wp-content/uploads/2026/03/virgo-Cropped.webp',
        'libra'       => 'https://merarashifal.com/wp-content/uploads/2026/03/Libra-cropped.webp',
        'scorpio'     => 'https://merarashifal.com/wp-content/uploads/2026/03/Scorpio-cropped.webp',
        'sagittarius' => 'https://merarashifal.com/wp-content/uploads/2026/03/Sagittarius-Cropped.webp',
        'capricorn'   => 'https://merarashifal.com/wp-content/uploads/2026/03/Capricon-Cropped.webp',
        'aquarius'    => 'https://merarashifal.com/wp-content/uploads/2026/03/Aquarius-Cropped.webp',
        'pisces'      => 'https://merarashifal.com/wp-content/uploads/2026/03/Pisces-cropped.webp'
    ];

    $default_image = 'https://merarashifal.com/wp-content/uploads/2026/03/Aries-Cropped.webp';
    $data = [
        'title'     => '',
        'desc'      => '',
        'canonical' => home_url('/horoscope/'),
        'image'     => $default_image,
        'url'       => home_url('/horoscope/'),
    ];
    $zodiac_map = mr_horoscope_seo_zodiac_map();

    if ($is_zodiac_sign_path) {
        if (empty($sign)) {
            $data['title']     = "Zodiac Signs Personality & Traits - All 12 Signs | MeraRashifal.com";
            $data['desc']      = "Discover the personality traits, strengths, and weaknesses of all 12 zodiac signs. Detailed astrological insights into your character on MeraRashifal.com.";
            $data['canonical'] = home_url('/horoscope/zodiac_sign/');
            $data['url']       = home_url('/horoscope/zodiac_sign/');
        } else {
            $zodiac_en = ucfirst($sign);
            $zodiac_hi = $zodiac_map[$sign] ?? '';
            $data['title']     = "{$zodiac_en} ({$zodiac_hi}) Personality Traits & Characteristics | MeraRashifal.com";
            $data['desc']      = "Learn all about {$zodiac_en} ({$zodiac_hi}) personality traits, core characteristics, and zodiac sign facts. Deep dive into the nature of {$zodiac_en} on MeraRashifal.com.";
            $data['canonical'] = home_url('/horoscope/zodiac_sign/' . $sign . '/');
            $data['url']       = home_url('/horoscope/zodiac_sign/' . $sign . '/');
            if (isset($zodiac_images[$sign])) {
                $data['image'] = $zodiac_images[$sign];
            }
        }
        return $data;
    }
    
    if (empty($sign) || !array_key_exists($sign, $zodiac_map)) {
        $seo_date = current_time('F j, Y'); 
        $data['title']     = "Aaj Ka Rashifal: Today Horoscope - {$seo_date} | MeraRashifal.com";
        $data['desc']      = "Check Aaj Ka Rashifal for {$seo_date}. Get accurate Rashifal today and today horoscope for all zodiac signs. Plan your day with MeraRashifal.com's expert insights.";
        $data['canonical'] = home_url('/horoscope/');
        $data['url']       = home_url('/horoscope/');
        return $data;
    }
    
    $zodiac_en = ucfirst($sign);
    $zodiac_hi = $zodiac_map[$sign];
    if (isset($zodiac_images[$sign])) {
        $data['image'] = $zodiac_images[$sign];
    }
    
    if ($mode === 'weekly') {
        $seo_month = current_time('F');
        $seo_year  = current_time('Y');
        $data['title']     = "Weekly Horoscope: {$zodiac_en} ({$zodiac_hi}) Rashifal - {$seo_month} {$seo_year} | MeraRashifal.com";
        $data['desc']      = "Your weekly {$zodiac_en} ({$zodiac_hi}) horoscope for this week of {$seo_month}. Get detailed weekly Rashifal today and astrological predictions to stay ahead with MeraRashifal.com.";
        $data['canonical'] = home_url('/horoscope/' . $sign . '/weekly-horoscope/');
        $data['url']       = home_url('/horoscope/' . $sign . '/weekly-horoscope/');
    } else {
        $seo_date = current_time('F j, Y');
        $date_suffix = '';
        if ($date_val && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_val)) {
            $seo_date = date('F j, Y', strtotime($date_val));
            $date_suffix = $date_val . '/';
        }
        $data['title']     = "{$zodiac_en} ({$zodiac_hi}) Aaj Ka Rashifal - Today Horoscope {$seo_date} | MeraRashifal.com";
        $data['desc']      = "Read your {$zodiac_en} ({$zodiac_hi}) Aaj Ka Rashifal for {$seo_date}. Explore your today horoscope in Hindi/English, covering health, wealth, and career on MeraRashifal.com.";
        $data['canonical'] = home_url('/horoscope/' . $sign . '/' . $date_suffix);
        $data['url']       = home_url('/horoscope/' . $sign . '/' . $date_suffix);
    }
    
    return $data;
}
}

if (!function_exists('mr_is_horoscope_request')) {
function mr_is_horoscope_request() {
    if (is_page('horoscope') || get_query_var('pagename') === 'horoscope' || get_query_var('mr_path') || get_query_var('zodiac_sign')) {
        return true;
    }
    if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/horoscope') !== false) {
        return true;
    }
    return false;
}
}

// Yoast Title
add_filter('wpseo_title', function($title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

// Pre get document title fallback
add_filter('pre_get_document_title', function($title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $title;
}, 100);

// Yoast Meta Description
add_filter('wpseo_metadesc', function($desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $desc;
}, 100);

// Yoast Canonical URL
add_filter('wpseo_canonical', function($canonical) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['canonical'])) return $seo['canonical'];
    }
    return $canonical;
}, 100);

// OpenGraph & Twitter
add_filter('wpseo_opengraph_title', function($og_title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $og_title;
}, 100);

add_filter('wpseo_opengraph_desc', function($og_desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $og_desc;
}, 100);

add_filter('wpseo_opengraph_url', function($og_url) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['url'])) return $seo['url'];
    }
    return $og_url;
}, 100);

add_filter('wpseo_opengraph_image', function($og_image) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $og_image;
}, 100);

add_filter('wpseo_twitter_title', function($tw_title) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['title'])) return $seo['title'];
    }
    return $tw_title;
}, 100);

add_filter('wpseo_twitter_description', function($tw_desc) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['desc'])) return $seo['desc'];
    }
    return $tw_desc;
}, 100);

add_filter('wpseo_twitter_image', function($tw_image) {
    if (mr_is_horoscope_request()) {
        $seo = mr_horoscope_get_seo_data();
        if ($seo && !empty($seo['image'])) return $seo['image'];
    }
    return $tw_image;
}, 100);

// Demote widget 'Click On Your Sun Sign' H1 to H2 when viewing sign subpages
add_filter('the_content', function($content) {
    if (mr_is_horoscope_request()) {
        list($path, $parts) = mr_horoscope_get_active_path_parts();
        if (!empty($parts)) {
            $content = preg_replace('/<h1([^>]*)>(\s*Click On Your Sun Sign\s*)<\/h1>/i', '<h2$1>$2</h2>', $content);
        }
    }
    return $content;
}, 9999);

