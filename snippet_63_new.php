<?php
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
if (!function_exists('mr_render_zodiac_landing')) {
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
}

/**
 * HELPER: Render Zodiac Sign Detail Page
 */
if (!function_exists('mr_render_zodiac_detail')) {
function mr_render_zodiac_detail($sign) {
    global $wpdb;

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

    $row_en = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM af_zodiac_base WHERE LOWER(sun_sign)=LOWER(%s) AND lang='en' LIMIT 1",
        $sign
    ));

    if (!$row_en) {
        return "<div class='mr-horoscope-wrap' style='background:#FFFBEB;padding:30px;border-radius:12px;text-align:center;'><h1>Sign Not Found</h1><p>Sorry, we couldn't find details for " . esc_html($sign) . ".</p></div>";
    }

    $row_hi = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM af_zodiac_base WHERE zodiac_id=%d AND lang='hi' LIMIT 1",
        $row_en->zodiac_id
    ));

    $zodiac_hi_map = [
        'aries' => 'मेष', 'taurus' => 'वृषभ', 'gemini' => 'मिथुन', 'cancer' => 'कर्क',
        'leo' => 'सिंह', 'virgo' => 'कन्या', 'libra' => 'तुला', 'scorpio' => 'वृश्चिक',
        'sagittarius' => 'धनु', 'capricorn' => 'मकर', 'aquarius' => 'कुंभ', 'pisces' => 'मीन'
    ];
    $sign_hi = $zodiac_hi_map[$sign_key] ?? $sign;

    ob_start();
    ?>
    <style>
        .mr-horoscope-wrap {
            background-color: #FFFBEB !important;
            font-family: 'Noto Sans', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1E293B;
            border-radius: 16px;
            border: 1px solid #FDE68A;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(180, 83, 9, 0.05);
            margin: 0 auto 30px auto;
            width: 100%;
            box-sizing: border-box;
        }
        .mr-z-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px dashed #D97706;
        }
        .mr-z-img-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #D97706;
            background: #fff;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.15);
        }
        .mr-z-title-wrap { flex: 1; }
        .mr-z-title-wrap h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #78350F;
            line-height: 1.3;
        }
        .mr-z-title-wrap p {
            margin: 6px 0 0;
            color: #D97706;
            font-weight: 700;
            font-size: 16px;
        }
        .mr-z-lang-toggle {
            display: inline-flex;
            background: #FEF3C7;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #FDE68A;
            margin-bottom: 20px;
        }
        .mr-z-lang-btn {
            background: transparent;
            border: none;
            color: #78350F;
            padding: 6px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .mr-z-lang-btn.active {
            background: #B45309;
            color: #fff;
            box-shadow: 0 2px 6px rgba(180, 83, 9, 0.25);
        }
        .mr-zodiac-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 18px 0;
        }
        .mr-zodiac-item {
            background: #ffffff;
            padding: 12px 14px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            border: 1px solid #FEF3C7;
            border-top: 3px solid #D97706;
        }
        .mr-z-label { font-size: 11px; font-weight: 700; color: #B45309; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px; text-align: center; }
        .mr-z-value { font-size: 15px; font-weight: 700; color: #1E293B; text-align: center; }
        .mr-lang-content { display: none; animation: mrFadeIn 0.3s ease; }
        .mr-lang-content.active { display: block; }
        @keyframes mrFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        .mr-z-desc { line-height: 1.75; color: #334155; font-size: 15px; margin-bottom: 20px; }
        .mr-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 28px;
            background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
            color: #FFFFFF !important;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);
        }
        .mr-btn-action:hover {
            background: linear-gradient(135deg, #B45309 0%, #78350F 100%);
            transform: translateY(-1px);
        }
        @media (max-width: 600px) {
            .mr-z-header { flex-direction: column; text-align: center; }
            .mr-z-img-large { width: 75px; height: 75px; }
            .mr-z-title-wrap h1 { font-size: 22px; }
            .mr-horoscope-wrap { padding: 16px 12px; }
        }
    </style>

    <div class="mr-horoscope-wrap">
        <div class="mr-z-header">
            <?php if ($img_url): ?>
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($sign); ?>" class="mr-z-img-large">
            <?php endif; ?>
            <div class="mr-z-title-wrap">
                <div class="mr-lang-content mr-lang-en active">
                    <h1><?php echo esc_html(ucfirst($sign)); ?> Personality & Traits</h1>
                    <p>✨ <?php echo esc_html($row_en->value_1_text ?? ''); ?></p>
                </div>
                <div class="mr-lang-content mr-lang-hi">
                    <h1><?php echo esc_html($sign_hi); ?> व्यक्तित्व एवं स्वभाव</h1>
                    <p>✨ <?php echo esc_html($row_hi->value_1_text ?? $row_en->value_1_text ?? ''); ?></p>
                </div>
            </div>
        </div>

        <div class="mr-z-lang-toggle">
            <button class="mr-z-lang-btn active" data-lang="en">EN</button>
            <button class="mr-z-lang-btn" data-lang="hi">हिन्दी</button>
        </div>

        <!-- ENGLISH CONTENT -->
        <div class="mr-lang-content mr-lang-en active" id="content-detail-en">
            <div class="mr-z-desc"><?php echo wp_kses_post($row_en->description ?? ''); ?></div>
            <div class="mr-zodiac-details-grid">
                <?php for($i=1;$i<=13;$i++): 
                    $v="value_$i"; $t="value_".$i."_text";
                    if(!empty($row_en->$v) || !empty($row_en->$t)): ?>
                    <div class="mr-zodiac-item">
                        <span class="mr-z-label"><?php echo esc_html($row_en->$v); ?></span>
                        <span class="mr-z-value"><?php echo esc_html($row_en->$t); ?></span>
                    </div>
                <?php endif; endfor; ?>
            </div>
            <div style="margin-top: 25px; text-align: center;">
                <a href="/horoscope/<?php echo esc_attr($sign); ?>" class="mr-btn-action">
                    View Today's <?php echo ucfirst($sign); ?> Horoscope &rarr;
                </a>
            </div>
        </div>

        <!-- HINDI CONTENT -->
        <div class="mr-lang-content mr-lang-hi" id="content-detail-hi">
            <?php if ($row_hi): ?>
                <div class="mr-z-desc"><?php echo wp_kses_post($row_hi->description ?? ''); ?></div>
                <div class="mr-zodiac-details-grid">
                    <?php for($i=1;$i<=13;$i++): 
                        $v="value_$i"; $t="value_".$i."_text";
                        if(!empty($row_hi->$v) || !empty($row_hi->$t)): ?>
                        <div class="mr-zodiac-item">
                            <span class="mr-z-label"><?php echo esc_html($row_hi->$v); ?></span>
                            <span class="mr-z-value"><?php echo esc_html($row_hi->$t); ?></span>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            <?php else: ?>
                <div class="mr-z-desc"><?php echo wp_kses_post($row_en->description ?? ''); ?></div>
            <?php endif; ?>
            <div style="margin-top: 25px; text-align: center;">
                <a href="/horoscope/<?php echo esc_attr($sign); ?>?lang=hi" class="mr-btn-action">
                    आज का <?php echo esc_html($sign_hi); ?> राशिफल देखें &rarr;
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const btns = document.querySelectorAll('.mr-z-lang-btn');
        function setDetailLang(lang) {
            btns.forEach(b => b.classList.toggle('active', b.getAttribute('data-lang') === lang));
            document.querySelectorAll('.mr-lang-en').forEach(el => el.classList.toggle('active', lang === 'en'));
            document.querySelectorAll('.mr-lang-hi').forEach(el => el.classList.toggle('active', lang === 'hi'));
        }
        btns.forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                setDetailLang(lang);
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.history.replaceState({}, '', url);
            });
        });
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('lang') === 'hi') {
            setDetailLang('hi');
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}

/**
 * HELPER: Format Hindi Date string from Y-m-d
 */
if (!function_exists('mr_format_date_hindi')) {
function mr_format_date_hindi($date_str) {
    $ts = strtotime($date_str);
    if (!$ts) return $date_str;
    $day = date('j', $ts);
    $month_en = date('F', $ts);
    $year = date('Y', $ts);
    $hi_months = [
        'January' => 'जनवरी', 'February' => 'फ़रवरी', 'March' => 'मार्च',
        'April' => 'अप्रैल', 'May' => 'मई', 'June' => 'जून',
        'July' => 'जुलाई', 'August' => 'अगस्त', 'September' => 'सितम्बर',
        'October' => 'अक्टूबर', 'November' => 'नवम्बर', 'December' => 'दिसम्बर'
    ];
    $month_hi = $hi_months[$month_en] ?? $month_en;
    return "{$day} {$month_hi} {$year}";
}
}

/**
 * MAIN SHORTCODE: [dynamic_horoscope]
 */
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

    // --- DATA FETCHING WITH CACHE & GRACEFUL FALLBACK ---
    // 1. Base Details (EN & HI)
    $cache_base_en = "mr_zb_base_en_" . md5($sign);
    $base_en = get_transient($cache_base_en);
    if ($base_en === false) {
        $base_en = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_zodiac_base WHERE LOWER(sun_sign)=LOWER(%s) AND lang='en' LIMIT 1", $sign));
        if (!$base_en) {
            $base_en = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_zodiac_base WHERE LOWER(sun_sign)=LOWER(%s) LIMIT 1", $sign));
        }
        if ($base_en) set_transient($cache_base_en, $base_en, 12 * HOUR_IN_SECONDS);
    }

    $base_hi = null;
    if ($base_en && !empty($base_en->zodiac_id)) {
        $cache_base_hi = "mr_zb_base_hi_" . md5((string)$base_en->zodiac_id);
        $base_hi = get_transient($cache_base_hi);
        if ($base_hi === false) {
            $base_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_zodiac_base WHERE zodiac_id=%d AND lang='hi' LIMIT 1", $base_en->zodiac_id));
            if ($base_hi) set_transient($cache_base_hi, $base_hi, 12 * HOUR_IN_SECONDS);
        }
    }

    // 2. Daily Horoscope (EN & HI)
    $cache_daily_en = "mr_dh_en_" . md5($sign . $selected_date);
    $daily = get_transient($cache_daily_en);
    if ($daily === false) {
        $daily = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s AND language='en' LIMIT 1", $sign, $selected_date));
        if (!$daily) {
            $daily = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s LIMIT 1", $sign, $selected_date));
        }
        // Fallback for local testing or before cron runs: pull latest available date
        if (!$daily && empty($parts[1])) {
            $latest_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND language='en' ORDER BY prediction_date DESC LIMIT 1", $sign));
            if ($latest_row) {
                $daily = $latest_row;
                $selected_date = $latest_row->prediction_date;
                $yesterday_link = date('Y-m-d', strtotime($selected_date.' -1 day'));
                $tomorrow_link  = date('Y-m-d', strtotime($selected_date.' +1 day'));
            }
        }
        if ($daily) set_transient($cache_daily_en, $daily, 12 * HOUR_IN_SECONDS);
    }

    $cache_daily_hi = "mr_dh_hi_" . md5($sign . $selected_date);
    $daily_hi = get_transient($cache_daily_hi);
    if ($daily_hi === false) {
        $daily_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s AND language='hi' LIMIT 1", $sign, $selected_date));
        if (!$daily_hi && $daily) {
            // Check fallback for hi
            $daily_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM Daily_Horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND prediction_date=%s AND language='hi' LIMIT 1", $sign, $daily->prediction_date));
        }
        if ($daily_hi) set_transient($cache_daily_hi, $daily_hi, 12 * HOUR_IN_SECONDS);
    }

    // 3. Weekly Horoscope (EN & HI) - using correct column 'lang'
    $cache_weekly_en = "mr_wh_en_" . md5($sign . $today);
    $weekly = get_transient($cache_weekly_en);
    if ($weekly === false) {
        $weekly = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s AND lang='en' ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        if (!$weekly) {
            $weekly = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        }
        if (!$weekly) {
            // Fallback to latest
            $weekly = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign));
        }
        if ($weekly) set_transient($cache_weekly_en, $weekly, 12 * HOUR_IN_SECONDS);
    }

    $cache_weekly_hi = "mr_wh_hi_" . md5($sign . $today);
    $weekly_hi = get_transient($cache_weekly_hi);
    if ($weekly_hi === false) {
        $weekly_hi = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_weekly_horoscope WHERE LOWER(zodiac_name)=LOWER(%s) AND week_start_date <= %s AND lang='hi' ORDER BY week_year DESC, week_number DESC LIMIT 1", $sign, $today));
        if (!$weekly_hi && $weekly) {
            $weekly_hi = $weekly;
        }
        if ($weekly_hi) set_transient($cache_weekly_hi, $weekly_hi, 12 * HOUR_IN_SECONDS);
    }

    // Zodiac Maps
    $zodiac_en_map = [
        'aries' => 'Aries', 'taurus' => 'Taurus', 'gemini' => 'Gemini', 'cancer' => 'Cancer',
        'leo' => 'Leo', 'virgo' => 'Virgo', 'libra' => 'Libra', 'scorpio' => 'Scorpio',
        'sagittarius' => 'Sagittarius', 'capricorn' => 'Capricorn', 'aquarius' => 'Aquarius', 'pisces' => 'Pisces'
    ];
    $zodiac_hi_map = [
        'aries' => 'मेष', 'taurus' => 'वृषभ', 'gemini' => 'मिथुन', 'cancer' => 'कर्क',
        'leo' => 'सिंह', 'virgo' => 'कन्या', 'libra' => 'तुला', 'scorpio' => 'वृश्चिक',
        'sagittarius' => 'धनु', 'capricorn' => 'मकर', 'aquarius' => 'कुंभ', 'pisces' => 'मीन'
    ];
    $zodiac_dates_en = [
        'aries' => 'March 21 - April 19', 'taurus' => 'April 20 - May 20', 'gemini' => 'May 21 - June 20',
        'cancer' => 'June 21 - July 22', 'leo' => 'July 23 - August 22', 'virgo' => 'August 23 - September 22',
        'libra' => 'September 23 - October 22', 'scorpio' => 'October 23 - November 21', 'sagittarius' => 'November 22 - December 21',
        'capricorn' => 'December 22 - January 19', 'aquarius' => 'January 20 - February 18', 'pisces' => 'February 19 - March 20'
    ];
    $zodiac_dates_hi = [
        'aries' => '21 मार्च - 19 अप्रैल', 'taurus' => '20 अप्रैल - 20 मई', 'gemini' => '21 मई - 20 जून',
        'cancer' => '21 जून - 22 जुलाई', 'leo' => '23 जुलाई - 22 अगस्त', 'virgo' => '23 अगस्त - 22 सितम्बर',
        'libra' => '23 सितम्बर - 22 अक्टूबर', 'scorpio' => '23 अक्टूबर - 21 नवम्बर', 'sagittarius' => '22 नवम्बर - 21 दिसम्बर',
        'capricorn' => '22 दिसम्बर - 19 जनवरी', 'aquarius' => '20 जनवरी - 18 फ़रवरी', 'pisces' => '19 फ़रवरी - 20 मार्च'
    ];

    $sign_name_en = $zodiac_en_map[$sign] ?? ucfirst($sign);
    $sign_name_hi = $zodiac_hi_map[$sign] ?? $sign_name_en;
    $date_range_en = $zodiac_dates_en[$sign] ?? '';
    $date_range_hi = $zodiac_dates_hi[$sign] ?? $date_range_en;

    // Sector Definitions
    $sector_defs = [
        'career'       => ['en' => 'Career',        'hi' => 'करियर',         'icon' => '💼'],
        'finances'     => ['en' => 'Finance',       'hi' => 'वित्त एवं धन',   'icon' => '💰'],
        'relationship' => ['en' => 'Love',          'hi' => 'प्रेम व संबंध', 'icon' => '❤️'],
        'health'       => ['en' => 'Health',        'hi' => 'स्वास्थ्य',      'icon' => '🌿'],
        'status'       => ['en' => 'Status & Mind', 'hi' => 'स्थिति व मन',   'icon' => '🌟'],
        'travel'       => ['en' => 'Travel',        'hi' => 'यात्रा',         'icon' => '✈️'],
        'family'       => ['en' => 'Family',        'hi' => 'परिवार',         'icon' => '🏡'],
        'friends'      => ['en' => 'Friends',       'hi' => 'मित्र',          'icon' => '🤝']
    ];

    ob_start();
?>

<style>
/* ====================================================
   MERA RASHIFAL - DYNAMIC HOROSCOPE COMPACT SMART STYLES
   Background: #FFFBEB | Standard Fonts & Colors
==================================================== */
.mr-horoscope-wrap {
    background-color: #FFFBEB !important;
    font-family: 'Noto Sans', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1E293B;
    border-radius: 16px;
    border: 1px solid #FDE68A;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(180, 83, 9, 0.05);
    margin: 0 auto 30px auto;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}
@media (min-width: 768px) {
    .mr-horoscope-wrap {
        padding: 24px 28px;
    }
}

/* Header Area */
.mr-top-bar {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px dashed #FCD34D;
}
@media (min-width: 768px) {
    .mr-top-bar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.mr-title-block h1 {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #B45309 !important;
    margin: 0 !important;
    line-height: 1.25 !important;
    letter-spacing: -0.3px;
}
@media (min-width: 768px) {
    .mr-title-block h1 {
        font-size: 30px !important;
    }
}

.mr-zodiac-dates {
    display: inline-block;
    color: #D97706 !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    margin-left: 6px;
}
@media (min-width: 768px) {
    .mr-zodiac-dates {
        font-size: 17px !important;
    }
}

/* Navigation & Controls Bar */
.mr-controls-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    background: #FEF3C7;
    padding: 8px 12px;
    border-radius: 12px;
    border: 1px solid #FDE68A;
    margin-bottom: 18px;
}

.mr-nav-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex: 1 1 auto;
}

.mr-pill-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 14px;
    background: #FFFFFF;
    color: #78350F !important;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.mr-pill-btn:hover {
    background: #FEF3C7;
    color: #B45309 !important;
    border-color: #F59E0B;
}
.mr-pill-btn.active {
    background: #B45309;
    color: #FFFFFF !important;
    border-color: #B45309;
    box-shadow: 0 2px 6px rgba(180, 83, 9, 0.25);
}

.mr-date-picker-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 3px 6px 3px 8px;
}
.mr-date-picker-wrap input[type="date"] {
    border: none;
    background: transparent;
    font-size: 13px;
    color: #1E293B;
    font-family: inherit;
    outline: none;
    padding: 4px 0;
}
.mr-date-picker-wrap button {
    background: #B45309;
    color: #fff;
    border: none;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
}
.mr-date-picker-wrap button:hover {
    background: #78350F;
}

/* Language Switcher */
.mr-lang-switcher {
    display: inline-flex;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 3px;
    margin-left: auto;
}
.mr-lang-toggle-btn {
    border: none;
    background: transparent;
    padding: 5px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #78350F;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.mr-lang-toggle-btn.active {
    background: #B45309;
    color: #FFFFFF;
}

/* Lucky Indicators Ribbon (Compact Horizontal Bar) */
.mr-lucky-ribbon {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-left: 5px solid #F59E0B;
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
}
.mr-lucky-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13.5px;
    color: #334155;
}
.mr-lucky-item strong {
    color: #78350F;
    font-weight: 700;
}
.mr-color-chip {
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.15);
    vertical-align: middle;
}
.mr-num-badge {
    display: inline-block;
    background: #FEF3C7;
    color: #92400E;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12.5px;
    border: 1px solid #FDE68A;
}

/* 2-Column Responsive Sector Grid */
.mr-sectors-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 18px;
}
@media (min-width: 680px) {
    .mr-sectors-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
}

.mr-sector-card {
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 2px 8px rgba(180, 83, 9, 0.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.mr-sector-card:hover {
    box-shadow: 0 4px 14px rgba(180, 83, 9, 0.08);
    border-color: #F59E0B;
}

.mr-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #FEF3C7;
}

.mr-card-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 15px;
    font-weight: 700;
    color: #1E293B;
}
.mr-card-icon {
    font-size: 16px;
}

.mr-score-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.mr-mini-bar {
    width: 38px;
    height: 5px;
    background: rgba(0,0,0,0.08);
    border-radius: 3px;
    overflow: hidden;
}
.mr-mini-bar-fill {
    height: 100%;
    border-radius: 3px;
}

.mr-card-body {
    font-size: 14px;
    line-height: 1.6;
    color: #334155;
    margin: 0;
}

/* Summary Box */
.mr-summary-box {
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-left: 5px solid #D97706;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 22px;
    box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
}
.mr-summary-title {
    font-size: 14px;
    font-weight: 800;
    color: #B45309;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mr-summary-text {
    font-size: 14.5px;
    line-height: 1.7;
    color: #1E293B;
    margin: 0;
}

/* About Zodiac Section */
.mr-about-section {
    background: #FFFFFF;
    border: 1px solid #FDE68A;
    border-radius: 12px;
    padding: 18px 20px;
    margin-top: 10px;
}
.mr-about-header {
    font-size: 18px;
    font-weight: 800;
    color: #78350F;
    margin: 0 0 10px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #FEF3C7;
}
.mr-about-desc {
    font-size: 14px;
    line-height: 1.7;
    color: #334155;
    margin-bottom: 14px;
}
.mr-traits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}
@media (min-width: 768px) {
    .mr-traits-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
.mr-trait-chip {
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 13px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.mr-trait-key {
    font-weight: 700;
    color: #B45309;
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
    text-align: center;
}
.mr-trait-val {
    color: #1E293B;
    font-weight: 600;
    display: block;
    text-align: center;
}

/* Visibility Control for Language Switching */
.mr-lang-hi {
    display: none !important;
}
body.mr-active-lang-hi .mr-lang-hi {
    display: block !important;
}
body.mr-active-lang-hi .mr-lang-hi.mr-inline {
    display: inline !important;
}
body.mr-active-lang-hi .mr-lang-hi.mr-flex {
    display: flex !important;
}
body.mr-active-lang-hi .mr-lang-hi.mr-grid {
    display: grid !important;
}
body.mr-active-lang-hi .mr-lang-en {
    display: none !important;
}
</style>

<div class="mr-horoscope-wrap" id="mr-horoscope-content">

    <!-- Top Header: Title & Dates -->
    <div class="mr-top-bar">
        <div class="mr-title-block">
            <!-- English Title -->
            <h1 class="mr-lang-en">
                <?php echo esc_html($sign_name_en); ?> Horoscope
                <?php if ($date_range_en): ?>
                    <span class="mr-zodiac-dates">(<?php echo esc_html($date_range_en); ?>)</span>
                <?php endif; ?>
            </h1>
            <!-- Hindi Title -->
            <h1 class="mr-lang-hi">
                <?php echo esc_html($sign_name_hi); ?> राशिफल
                <?php if ($date_range_hi): ?>
                    <span class="mr-zodiac-dates">(<?php echo esc_html($date_range_hi); ?>)</span>
                <?php endif; ?>
            </h1>
        </div>
    </div>

    <!-- Controls Bar: Navigation, Date Picker, Language Toggle -->
    <div class="mr-controls-bar">
        <div class="mr-nav-group">
            <!-- Yesterday -->
            <a class="mr-pill-btn" id="mr-btn-yesterday" href="/horoscope/<?php echo esc_attr($sign); ?>/<?php echo esc_attr($yesterday_link); ?>">
                <span class="mr-lang-en">Yesterday</span>
                <span class="mr-lang-hi mr-inline">कल (बीता)</span>
            </a>
            <!-- Today -->
            <a class="mr-pill-btn <?php echo ($mode !== 'weekly' && empty($parts[1])) ? 'active' : ''; ?>" id="mr-btn-today" href="/horoscope/<?php echo esc_attr($sign); ?>">
                <span class="mr-lang-en">Today</span>
                <span class="mr-lang-hi mr-inline">आज</span>
            </a>
            <!-- Tomorrow -->
            <a class="mr-pill-btn" id="mr-btn-tomorrow" href="/horoscope/<?php echo esc_attr($sign); ?>/<?php echo esc_attr($tomorrow_link); ?>">
                <span class="mr-lang-en">Tomorrow</span>
                <span class="mr-lang-hi mr-inline">कल (आने वाला)</span>
            </a>
            <!-- This Week -->
            <a class="mr-pill-btn <?php echo ($mode === 'weekly') ? 'active' : ''; ?>" href="/horoscope/<?php echo esc_attr($sign); ?>/weekly-horoscope">
                <span class="mr-lang-en">This Week</span>
                <span class="mr-lang-hi mr-inline">इस सप्ताह</span>
            </a>
        </div>

        <?php if ($mode !== 'weekly'): ?>
        <div class="mr-date-picker-wrap">
            <input type="date" id="mrDate" value="<?php echo esc_attr($selected_date); ?>">
            <button type="button" onclick="mrLoadDate('<?php echo esc_js($sign); ?>')">
                <span class="mr-lang-en">Check</span>
                <span class="mr-lang-hi mr-inline">देखें</span>
            </button>
        </div>
        <?php endif; ?>

        <!-- Language Switcher -->
        <div class="mr-lang-switcher">
            <button type="button" class="mr-lang-toggle-btn active" data-lang="en">EN</button>
            <button type="button" class="mr-lang-toggle-btn" data-lang="hi">हिन्दी</button>
        </div>
    </div>

    <?php
    // Helper closure to render content block (for daily or weekly)
    $render_prediction_content = function($data, $lang_mode, $is_weekly, $fields, $selected_date, $today) {
        if (empty($data)) {
            $msg = ($lang_mode === 'hi') ? 'इस तारीख के लिए राशिफल अभी उपलब्ध नहीं है।' : 'Horoscope is currently being prepared for this date.';
            return "<div style='background:#fff;padding:20px;border-radius:10px;border:1px solid #FDE68A;text-align:center;'><p>{$msg}</p></div>";
        }

        $is_hi = ($lang_mode === 'hi');
        $lucky_color = $data->lucky_color ?? '';
        $lucky_color_code = $data->lucky_color_code ?? '';
        $lucky_num1 = $data->lucky_number_1 ?? '';
        $lucky_num2 = $data->lucky_number_2 ?? '';
        $overall_summary = $is_weekly ? ($data->total_text ?? '') : ($data->total_response ?? '');

        ob_start();
        ?>

        <!-- Subheading with Date / Week info -->
        <div style="margin-bottom: 12px; font-size: 15px; font-weight: 700; color: #78350F; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <?php if ($is_weekly): ?>
                    <span><?php echo $is_hi ? 'साप्ताहिक राशिफल' : "This Week's Horoscope"; ?>: </span>
                    <span style="color:#B45309; font-weight:600;">
                        <?php echo esc_html(date_i18n('d M', strtotime($data->week_start_date))); ?> - <?php echo esc_html(date_i18n('d M, Y', strtotime($data->week_end_date))); ?>
                    </span>
                <?php else: ?>
                    <span><?php echo $is_hi ? 'दैनिक राशिफल' : 'Daily Horoscope'; ?>: </span>
                    <span style="color:#B45309; font-weight:600;">
                        <?php 
                        if ($is_hi) {
                            echo esc_html(mr_format_date_hindi($selected_date));
                        } else {
                            echo esc_html(date_i18n('d F Y', strtotime($selected_date)));
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lucky Indicators Ribbon -->
        <?php if (!empty($lucky_color) || !empty($lucky_num1)): ?>
        <div class="mr-lucky-ribbon">
            <div class="mr-lucky-item">
                <span style="font-size:16px;">🍀</span>
                <strong><?php echo $is_hi ? 'शुभ संकेत:' : 'Lucky Indicators:'; ?></strong>
            </div>
            <?php if (!empty($lucky_color)): ?>
            <div class="mr-lucky-item">
                <strong><?php echo $is_hi ? 'शुभ रंग:' : 'Lucky Color:'; ?></strong>
                <span><?php echo esc_html($lucky_color); ?></span>
                <?php if (!empty($lucky_color_code)): ?>
                    <span class="mr-color-chip" style="background-color: <?php echo esc_attr($lucky_color_code); ?>;"></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($lucky_num1)): ?>
            <div class="mr-lucky-item">
                <strong><?php echo $is_hi ? 'शुभ अंक:' : 'Lucky Numbers:'; ?></strong>
                <span class="mr-num-badge"><?php echo esc_html($lucky_num1); ?></span>
                <?php if (!empty($lucky_num2)): ?>
                    <span class="mr-num-badge"><?php echo esc_html($lucky_num2); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 2-Column Responsive Sector Grid -->
        <div class="mr-sectors-grid">
            <?php foreach ($fields as $field_key => $f_info):
                $score = $data->{$field_key.'_score'} ?? '';
                $text  = $is_weekly ? ($data->{$field_key.'_text'} ?? '') : ($data->{$field_key.'_response'} ?? '');
                if (!$score && !$text) continue;

                $s_num = is_numeric($score) ? (int)$score : 0;
                if ($s_num <= 25) {
                    $b_color = '#DC2626'; $b_bg = '#FEE2E2';
                } elseif ($s_num <= 49) {
                    $b_color = '#D97706'; $b_bg = '#FEF3C7';
                } elseif ($s_num <= 74) {
                    $b_color = '#B45309'; $b_bg = '#FEF9C3';
                } else {
                    $b_color = '#16A34A'; $b_bg = '#DCFCE7';
                }
                $field_label = $is_hi ? $f_info['hi'] : $f_info['en'];
            ?>
            <div class="mr-sector-card">
                <div>
                    <div class="mr-card-header">
                        <div class="mr-card-title">
                            <span class="mr-card-icon"><?php echo $f_info['icon']; ?></span>
                            <span><?php echo esc_html($field_label); ?></span>
                        </div>
                        <?php if ($score !== ''): ?>
                        <div class="mr-score-pill" style="color: <?php echo esc_attr($b_color); ?>; background: <?php echo esc_attr($b_bg); ?>;">
                            <div class="mr-mini-bar">
                                <div class="mr-mini-bar-fill" style="width: <?php echo esc_attr(min(100, max(5, $s_num))); ?>%; background: <?php echo esc_attr($b_color); ?>;"></div>
                            </div>
                            <span><?php echo esc_html($score); ?>/100</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="mr-card-body"><?php echo esc_html($text); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Overall Summary Box -->
        <?php if (!empty($overall_summary)): ?>
        <div class="mr-summary-box">
            <div class="mr-summary-title">
                <span>✨</span>
                <span><?php echo $is_hi ? ($is_weekly ? 'साप्ताहिक सारांश' : 'दैनिक सारांश') : ($is_weekly ? 'Weekly Summary' : 'Daily Summary'); ?></span>
            </div>
            <p class="mr-summary-text"><?php echo esc_html($overall_summary); ?></p>
        </div>
        <?php endif; ?>

        <?php
        return ob_get_clean();
    };
    ?>

    <!-- ==================== ENGLISH PREDICTION CONTENT ==================== -->
    <div class="mr-lang-en">
        <?php echo $render_prediction_content($mode === 'weekly' ? $weekly : $daily, 'en', $mode === 'weekly', $sector_defs, $selected_date, $today); ?>
    </div>

    <!-- ==================== HINDI PREDICTION CONTENT ==================== -->
    <div class="mr-lang-hi">
        <?php 
        $target_hi_data = ($mode === 'weekly') ? ($weekly_hi ?: $weekly) : ($daily_hi ?: $daily);
        echo $render_prediction_content($target_hi_data, 'hi', $mode === 'weekly', $sector_defs, $selected_date, $today); 
        ?>
    </div>

    <!-- ==================== ABOUT ZODIAC SECTION (BILINGUAL) ==================== -->
    <!-- English About Section -->
    <?php if ($base_en): ?>
    <div class="mr-about-section mr-lang-en">
        <h2 class="mr-about-header">About <?php echo esc_html($sign_name_en); ?></h2>
        <?php if (!empty($base_en->description)): ?>
            <p class="mr-about-desc"><?php echo esc_html($base_en->description); ?></p>
        <?php endif; ?>
        <ul class="mr-traits-grid">
            <?php for($i=1;$i<=13;$i++):
                $v="value_$i"; $t="value_".$i."_text";
                if(!empty($base_en->$v) || !empty($base_en->$t)): ?>
                <li class="mr-trait-chip">
                    <span class="mr-trait-key"><?php echo esc_html($base_en->$v ?? ''); ?></span>
                    <span class="mr-trait-val"><?php echo esc_html($base_en->$t ?? ''); ?></span>
                </li>
            <?php endif; endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Hindi About Section -->
    <?php if ($base_hi || $base_en): 
        $active_hi_base = $base_hi ?: $base_en;
    ?>
    <div class="mr-about-section mr-lang-hi">
        <h2 class="mr-about-header"><?php echo esc_html($sign_name_hi); ?> राशि के बारे में</h2>
        <?php if (!empty($active_hi_base->description)): ?>
            <p class="mr-about-desc"><?php echo esc_html($active_hi_base->description); ?></p>
        <?php endif; ?>
        <ul class="mr-traits-grid">
            <?php for($i=1;$i<=13;$i++):
                $v="value_$i"; $t="value_".$i."_text";
                if(!empty($active_hi_base->$v) || !empty($active_hi_base->$t)): ?>
                <li class="mr-trait-chip">
                    <span class="mr-trait-key"><?php echo esc_html($active_hi_base->$v ?? ''); ?></span>
                    <span class="mr-trait-val"><?php echo esc_html($active_hi_base->$t ?? ''); ?></span>
                </li>
            <?php endif; endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>

<script>
function mrLoadDate(sign) {
    var d = document.getElementById('mrDate').value;
    if (d) { 
        var url = new URL(window.location.origin + '/horoscope/' + sign + '/' + d);
        var currentLang = (document.body.classList.contains('mr-active-lang-hi') ? 'hi' : 'en');
        if (currentLang === 'hi') {
            url.searchParams.set('lang', 'hi');
        }
        window.location = url.pathname + url.search;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var d = new Date();
    var localYear = d.getFullYear();
    var localMonth = String(d.getMonth() + 1).padStart(2, '0');
    var localDay = String(d.getDate()).padStart(2, '0');
    var localDate = localYear + '-' + localMonth + '-' + localDay;
    var sign = "<?php echo esc_js($sign); ?>";
    
    // ----- LANGUAGE TOGGLE LOGIC -----
    var langBtns = document.querySelectorAll('.mr-lang-toggle-btn');
    function applyLanguage(lang) {
        if (lang === 'hi') {
            document.body.classList.add('mr-active-lang-hi');
        } else {
            document.body.classList.remove('mr-active-lang-hi');
        }
        langBtns.forEach(function(b) {
            b.classList.toggle('active', b.getAttribute('data-lang') === lang);
        });

        // Update nav links to preserve language query parameter
        var links = document.querySelectorAll('.mr-pill-btn, .mr-date-picker-wrap button');
        document.querySelectorAll('.mr-pill-btn').forEach(function(btn) {
            var href = btn.getAttribute('href');
            if (href) {
                var urlObj = new URL(href, window.location.origin);
                if (lang === 'hi') {
                    urlObj.searchParams.set('lang', 'hi');
                } else {
                    urlObj.searchParams.delete('lang');
                }
                btn.setAttribute('href', urlObj.pathname + urlObj.search);
            }
        });
    }

    var urlParams = new URLSearchParams(window.location.search);
    var initLang = urlParams.get('lang') || 'en';
    applyLanguage(initLang);

    langBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var lang = this.getAttribute('data-lang');
            applyLanguage(lang);
            var newUrl = new URL(window.location.href);
            if (lang === 'hi') {
                newUrl.searchParams.set('lang', 'hi');
            } else {
                newUrl.searchParams.delete('lang');
            }
            window.history.replaceState({}, '', newUrl);
        });
    });

    // Auto-update links with client local date
    var btnYesterday = document.getElementById("mr-btn-yesterday");
    var btnToday = document.getElementById("mr-btn-today");
    var btnTomorrow = document.getElementById("mr-btn-tomorrow");
    var langSuffix = (initLang === 'hi' ? '?lang=hi' : '');
    
    if (btnToday) {
        btnToday.href = "/horoscope/" + sign + "/" + localDate + langSuffix;
    }
    
    var yesterdayDate = new Date(d);
    yesterdayDate.setDate(d.getDate() - 1);
    if (btnYesterday) {
        var yM = String(yesterdayDate.getMonth() + 1).padStart(2, '0');
        var yD = String(yesterdayDate.getDate()).padStart(2, '0');
        btnYesterday.href = "/horoscope/" + sign + "/" + yesterdayDate.getFullYear() + "-" + yM + "-" + yD + langSuffix;
    }
    
    var tomorrowDate = new Date(d);
    tomorrowDate.setDate(d.getDate() + 1);
    if (btnTomorrow) {
        var tM = String(tomorrowDate.getMonth() + 1).padStart(2, '0');
        var tD = String(tomorrowDate.getDate()).padStart(2, '0');
        btnTomorrow.href = "/horoscope/" + sign + "/" + tomorrowDate.getFullYear() + "-" + tM + "-" + tD + langSuffix;
    }

    // Scroll smoothly to horoscope container if navigating to specific sign/date
    setTimeout(function() {
        var content = document.getElementById("mr-horoscope-content");
        if (content && window.location.pathname.indexOf('/horoscope/') !== -1) {
            var y = content.getBoundingClientRect().top + window.pageYOffset - 70;
            window.scrollTo({top: y, behavior: 'smooth'});
        }
    }, 250);
});
</script>
<?php
    return ob_get_clean();
});

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
