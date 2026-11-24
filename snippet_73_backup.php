<?php

/**
 * Unified Kundali Engine for MeraRashifal
 * Shortcode: [kundali_calc_engine]
 * Supports: Kundali Matching, Create Chart, House Explain, Kundali Details
 */

if (!defined('ABSPATH'))
    exit;

/* ====================================================
1. CONSTANTS & API KEYS
==================================================== */
$api_key = defined('VEDICASTRO_API_KEY') ? VEDICASTRO_API_KEY : '8d9d828d-3f9b-531a-a640-88c00469c300';
define('AF_KUNDALI_API_KEY', $api_key);

/* ====================================================
2. SCALABLE REWRITE RULES & QUERY VARS
==================================================== */
// Removed: Engine is now purely shortcode-based without dynamic URL routing.

/* ====================================================
3. SEO OPTIMIZATION (Yoast Compatible)
==================================================== */
function kundali_get_seo_data() {
    $type = isset($_GET['ku_type']) ? sanitize_text_field($_GET['ku_type']) : '';
    $data = [
        'default' => [
            'title' => 'Free Kundali & Matching Online | Vedic Astrology - MeraRashifal.com',
            'desc'  => 'Generate free Kundali, check Ashtakoot Kundali Matching, get your birth chart, and read personalized astrological predictions.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-default.webp'
        ],
        'kundali-matching-short' => [
            'title' => 'Free Kundali Matching | Ashtakoot Guna Milan - MeraRashifal.com',
            'desc'  => 'Check your Kundali matching score online. Get free Ashtakoot Guna Milan for marriage based on Vedic Astrology.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-matching.webp'
        ],
        'kundali-matching-detailed' => [
            'title' => 'Detailed Kundali Matching Report Online - MeraRashifal.com',
            'desc'  => 'Get a comprehensive Kundali matching report including planetary details and Astro compatibility scores for marriage.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-detailed-match.webp'
        ],
        'kundali-lagan-matching' => [
            'title' => 'Kundali Aggregate Matching & Manglik Dosh Check - MeraRashifal.com',
            'desc'  => 'Check your aggregate Kundali matching score, including Mangal Dosh, Pitra Dosh, and Kaalsarp Dosh analysis for marriage.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-lagan-match.webp'
        ],
        'kundali-nakshatra-match' => [
            'title' => 'Nakshatra Compatibility Match Online - MeraRashifal.com',
            'desc'  => 'Check marriage compatibility based purely on the Nakshatra (birth star) of the boy and girl.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-nakshatra.webp'
        ],
        'create-chart' => [
            'title' => 'Create Free Birth Chart | Online Kundali Maker - MeraRashifal.com',
            'desc'  => 'Generate your free Vedic birth chart (Kundali) online. View your precise planetary positions and Lagna chart.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-chart.webp'
        ],
        'house-explain' => [
            'title' => 'Kundali House Explanation & Planetary Positions - MeraRashifal.com',
            'desc'  => 'Understand the planetary positions in your Kundali. Get a detailed breakdown of planets, their zodiac signs, and houses.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-houses.webp'
        ],
        'kundali-details-report' => [
            'title' => 'Personalized Kundali Predictions & Characteristics - MeraRashifal.com',
            'desc'  => 'Read detailed personal characteristics and predictions based on the planetary lords of your Kundali houses.',
            'img'   => 'https://merarashifal.com/wp-content/uploads/2026/05/kundali-predictions.webp'
        ]
    ];
    return $data[$type] ?? $data['default'];
}

// Force Yoast SEO
add_filter('wpseo_title', function($title) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['title'];
    return $title;
}, 9999);

add_filter('wpseo_metadesc', function($desc) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['desc'];
    return $desc;
}, 9999);

add_filter('wpseo_opengraph_image', function($img) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['img'];
    return $img;
}, 1000);

add_filter('wpseo_twitter_image', function($img) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['img'];
    return $img;
}, 1000);

// Self-referencing canonical for ku_type tools
add_filter('wpseo_canonical', function($canonical) {
    if (is_page('kundali-details') || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/kundali-details') !== false)) {
        if (!empty($_GET['ku_type'])) {
            return home_url('/kundali-details/?ku_type=' . sanitize_text_field($_GET['ku_type']));
        }
        return home_url('/kundali-details/');
    }
    return $canonical;
}, 1000);

add_filter('wpseo_opengraph_title', function($t) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['title'];
    return $t;
}, 1000);

add_filter('wpseo_opengraph_desc', function($d) {
    if (isset($_GET['ku_type'])) return kundali_get_seo_data()['desc'];
    return $d;
}, 1000);

add_filter('wpseo_opengraph_url', function($u) {
    if (!empty($_GET['ku_type'])) {
        return home_url('/kundali-details/?ku_type=' . sanitize_text_field($_GET['ku_type']));
    }
    return $u;
}, 1000);

// Social Meta Force
add_action('wp_head', function() {
    if (isset($_GET['ku_type'])) {
        $seo = kundali_get_seo_data();
        echo "\n<!-- Kundali Engine SEO -->\n";
        echo '<meta property="og:title" content="' . esc_attr($seo['title']) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($seo['desc']) . '" />' . "\n";
        echo '<meta property="og:image" content="' . esc_url($seo['img']) . '" />' . "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($seo['img']) . '" />' . "\n";
        echo '<meta name="keywords" content="Kundali Matching, Birth Chart, Vedic Astrology, Kundali" />' . "\n";
        echo "<!-- End Kundali Engine SEO -->\n";
    }
}, 15);

// Google Analytics Force Tracking
add_action('wp_footer', function() {
    if (isset($_GET['ku_type'])) {
        ?>
        <script>
        (function() {
            window.addEventListener('load', function() {
                if (typeof gtag === 'function') {
                    gtag('event', 'page_view', { page_title: document.title, page_location: window.location.href, page_path: window.location.pathname });
                } else if (typeof ga === 'function') {
                    ga('send', 'pageview');
                }
            });
        })();
        </script>
        <?php
    }
}, 100);

/* ====================================================
4. THE UNIFIED ENGINE SHORTCODE
==================================================== */
add_shortcode('kundali_calc_engine', function () {
    $ku_type = isset($_GET['ku_type']) ? sanitize_text_field($_GET['ku_type']) : '';
    $ku_slug = isset($_GET['ku_slug']) ? sanitize_text_field($_GET['ku_slug']) : '';
    $ku_lang = isset($_GET['ku_lang']) ? sanitize_text_field($_GET['ku_lang']) : 'en';

    ob_start();

    if (!$ku_type) {
        kundali_display_landing_page();
    } elseif ($ku_slug) {
        kundali_display_result($ku_type, $ku_slug, $ku_lang);
    } else {
        kundali_display_form($ku_type);
    }

    // Append Detailed FAQs
    kundali_display_faq($ku_type);

    return ob_get_clean();
});

/* ====================================================
5. LANDING PAGE
==================================================== */
function kundali_display_landing_page($is_main_page = true) {
    $seo = kundali_get_seo_data();
    
    $tools = [
        ['type' => 'create-chart', 'title' => 'Create Birth Chart', 'desc' => 'Generate your free precise Vedic birth chart (Lagna Chart / D1).', 'btn' => 'Get Chart'],
        ['type' => 'kundali-details-report', 'title' => 'Kundali Predictions', 'desc' => 'Read detailed personal characteristics and life predictions based on your houses.', 'btn' => 'Read Predictions'],
        ['type' => 'house-explain', 'title' => 'Planetary Positions', 'desc' => 'Understand the planetary placements and degrees in your Kundali.', 'btn' => 'View Planets'],
        ['type' => 'kundali-matching-short', 'title' => 'Ashtakoot Matching', 'desc' => 'Check 36-point Guna Milan matching score for marriage.', 'btn' => 'Match Now'],
        ['type' => 'kundali-matching-detailed', 'title' => 'Detailed Matching', 'desc' => 'Comprehensive couple matching including individual planetary astro details.', 'btn' => 'Match Now'],
        ['type' => 'kundali-lagan-matching', 'title' => 'Aggregate Matching', 'desc' => 'Check compatibility including Mangal Dosh, Pitra Dosh, and Kaalsarp.', 'btn' => 'Match Now']
    ];

    if ($is_main_page) {
        ?>
        <style>
        .mr-city-loader { position: absolute !important; right: 12px !important; top: 50% !important; transform: translateY(-50%) !important; width: 20px !important; height: 20px !important; pointer-events: none !important; z-index: 15 !important; display: none; align-items: center; justify-content: center; }
        .mr-city-loader.is-active { display: inline-flex !important; }
        .mr-city-spinner-ring { width: 18px !important; height: 18px !important; border: 2.2px solid rgba(245, 130, 32, 0.22) !important; border-top-color: #F58220 !important; border-right-color: #D9A441 !important; border-radius: 50% !important; display: inline-block !important; box-sizing: border-box !important; animation: mrCitySpinRing 0.65s linear infinite !important; }
        @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

            .ku-grid-full { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; padding: 20px 0; font-family: 'Noto Sans', sans-serif; }
            .ku-card-full { background: #fff; border: 1px solid #D4AF37; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s; text-decoration: none; color: inherit; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
            .ku-card-full:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); border-color: #800000; }
            .ku-card-full h3 { color: #800000; margin: 0 0 10px 0; font-size: 20px; }
            .ku-card-full p { font-size: 14px; color: #6B7280; line-height: 1.5; margin:0; }
            .ku-btn { display: inline-block; margin-top: 15px; padding: 8px 20px; background: #800000; color: #fff; border-radius: 6px; font-weight: 600; font-size: 14px; }
        </style>
        <div style="text-align:center; margin-bottom: 30px;">
            <h1 style="color:#6D28D9; font-weight:800;">Kundali & Matching Services</h1>
            <p style="color:#4a5568;">Select a free Vedic Astrology report below</p>
        </div>
        <div class="ku-grid-full">
            <?php foreach ($tools as $t): ?>
                <a href="<?php echo esc_url(add_query_arg('ku_type', $t['type'], get_permalink())); ?>" class="ku-card-full">
                    <h3><?php echo esc_html($t['title']); ?></h3>
                    <p><?php echo esc_html($t['desc']); ?></p>
                    <span class="ku-btn"><?php echo esc_html($t['btn']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    } else {
        ?>
        <style>
        .mr-city-loader { position: absolute !important; right: 12px !important; top: 50% !important; transform: translateY(-50%) !important; width: 20px !important; height: 20px !important; pointer-events: none !important; z-index: 15 !important; display: none; align-items: center; justify-content: center; }
        .mr-city-loader.is-active { display: inline-flex !important; }
        .mr-city-spinner-ring { width: 18px !important; height: 18px !important; border: 2.2px solid rgba(245, 130, 32, 0.22) !important; border-top-color: #F58220 !important; border-right-color: #D9A441 !important; border-radius: 50% !important; display: inline-block !important; box-sizing: border-box !important; animation: mrCitySpinRing 0.65s linear infinite !important; }
        @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

            .ku-grid-compact { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 10px 0; font-family: 'Noto Sans', sans-serif; }
            @media (min-width: 600px) { .ku-grid-compact { grid-template-columns: repeat(3, 1fr); gap: 15px; } }
            @media (min-width: 1024px) { .ku-grid-compact { grid-template-columns: repeat(4, 1fr); gap: 20px; } }
            .ku-card-compact { background: #fff; border: 1px solid #D4AF37; padding: 15px 10px; border-radius: 8px; text-align: center; transition: all 0.3s; text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: center; min-height: 60px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
            .ku-card-compact:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); border-color: #800000; }
            .ku-card-compact h3 { color: #800000; margin: 0; font-size: 13px; font-weight: 700; line-height: 1.3; }
        </style>
        <div class="ku-grid-compact">
            <?php foreach ($tools as $t): ?>
                <a href="<?php echo esc_url(add_query_arg('ku_type', $t['type'], get_permalink())); ?>" class="ku-card-compact">
                    <h3><?php echo esc_html($t['title']); ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

/* ====================================================
6. FORMS
==================================================== */
function kundali_display_form($type) {
    $google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');
    $seo = kundali_get_seo_data();
    $is_couple = in_array($type, ['kundali-matching-short', 'kundali-matching-detailed', 'kundali-lagan-matching']);
    $is_nakshatra = ($type === 'kundali-nakshatra-match');
    
    ?>
    <style>
        .mr-city-loader { position: absolute !important; right: 12px !important; top: 50% !important; transform: translateY(-50%) !important; width: 20px !important; height: 20px !important; pointer-events: none !important; z-index: 15 !important; display: none; align-items: center; justify-content: center; }
        .mr-city-loader.is-active { display: inline-flex !important; }
        .mr-city-spinner-ring { width: 18px !important; height: 18px !important; border: 2.2px solid rgba(245, 130, 32, 0.22) !important; border-top-color: #F58220 !important; border-right-color: #D9A441 !important; border-radius: 50% !important; display: inline-block !important; box-sizing: border-box !important; animation: mrCitySpinRing 0.65s linear infinite !important; }
        @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        :root { --p-purple: #6D28D9; --p-orange: #FF5733; --p-beige: #FFFBEB; }
        .ku-container { font-family: 'Noto Sans', sans-serif; max-width: <?php echo ($is_couple) ? '800px' : '600px'; ?>; margin: 15px auto; background: var(--p-beige); padding: 20px; border-radius: 20px; border: 1px solid #FDE68A; box-shadow: 0 15px 35px rgba(109, 40, 217, 0.1); }
        .ku-title h1 { color: var(--p-purple); font-size: 24px; font-weight: 800; text-align:center; margin-bottom:20px; }
        .ku-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media(max-width:600px) { .ku-grid { grid-template-columns: 1fr; } }
        .ku-field label { display: block; font-weight: 800; font-size: 12px; color: var(--p-purple); text-transform: uppercase; margin-bottom: 6px; }
        .ku-input { width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; background: #fff; box-sizing: border-box; height: 50px; }
        .ku-input:focus { outline: none; border-color: var(--p-orange); }
        .ku-submit { width: 100%; padding: 15px; background: linear-gradient(135deg, #FF6B4A, #FF4B2B); color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 18px; margin-top: 20px; cursor: pointer; }
        .section-title { grid-column: 1 / -1; background:#6D28D9; color:#fff; padding:10px 15px; border-radius:8px; font-weight:bold; text-align:center; margin-top:10px; }
        .full-width { grid-column: 1 / -1; }
    </style>

    <div class="ku-container">
        <div class="ku-title"><h1><?php echo esc_html($seo['title']); ?></h1></div>
        <form method="POST" action="">
            <div class="ku-grid">
                <?php if ($is_nakshatra): ?>
                    <div class="ku-field">
                        <label>Boy's Nakshatra (Star)</label>
                        <select name="boy_star" class="ku-input" required>
                            <?php for($i=1; $i<=27; $i++) echo "<option value='$i'>Nakshatra $i</option>"; ?>
                        </select>
                    </div>
                    <div class="ku-field">
                        <label>Girl's Nakshatra (Star)</label>
                        <select name="girl_star" class="ku-input" required>
                            <?php for($i=1; $i<=27; $i++) echo "<option value='$i'>Nakshatra $i</option>"; ?>
                        </select>
                    </div>
                <?php elseif ($is_couple): ?>
                    <!-- BOY DETAILS -->
                    <div class="section-title">Boy's Details</div>
                    <div class="ku-field"><label>Date of Birth</label><input type="date" name="b_dob" class="ku-input" required></div>
                    <div class="ku-field"><label>Time of Birth</label><input type="time" name="b_tob" class="ku-input" required></div>
                    <div class="ku-field full-width">
                        <label>Place of Birth</label>
                        <input type="text" id="b_place" class="ku-input" placeholder="Search city..." required>
                    </div>
                    <input type="hidden" name="b_lat" id="b_lat">
                    <input type="hidden" name="b_lon" id="b_lon">
                    <input type="hidden" name="b_tz" id="b_tz" value="5.5">

                    <!-- GIRL DETAILS -->
                    <div class="section-title">Girl's Details</div>
                    <div class="ku-field"><label>Date of Birth</label><input type="date" name="g_dob" class="ku-input" required></div>
                    <div class="ku-field"><label>Time of Birth</label><input type="time" name="g_tob" class="ku-input" required></div>
                    <div class="ku-field full-width">
                        <label>Place of Birth</label>
                        <input type="text" id="g_place" class="ku-input" placeholder="Search city..." required>
                    </div>
                    <input type="hidden" name="g_lat" id="g_lat">
                    <input type="hidden" name="g_lon" id="g_lon">
                    <input type="hidden" name="g_tz" id="g_tz" value="5.5">

                <?php else: ?>
                    <!-- SINGLE DETAILS -->
                    <div class="ku-field"><label>Date of Birth</label><input type="date" name="dob" class="ku-input" required></div>
                    <div class="ku-field"><label>Time of Birth</label><input type="time" name="tob" class="ku-input" required></div>
                    <div class="ku-field full-width">
                        <label>Place of Birth</label>
                        <input type="text" id="place" class="ku-input" placeholder="Search city..." required>
                    </div>
                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lon" id="lon">
                    <input type="hidden" name="tz" id="tz" value="5.5">
                <?php endif; ?>

                <div class="ku-field full-width">
                    <label>Language Preferred</label>
                    <select name="lang" class="ku-input">
                        <option value="en" selected>English</option>
                        <option value="hi">Hindi (हिन्दी)</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="ku_type" value="<?php echo esc_attr($type); ?>">
            <input type="hidden" name="ku_action" value="submit_form">
            <input type="hidden" name="ku_current_url" value="<?php echo esc_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0]); ?>">
            <!-- VAPT Security: Honeypot to block automated bot submissions -->
            <input type="text" name="ku_honeypot" value="" style="display:none !important;" tabindex="-1" autocomplete="off">
            <?php wp_nonce_field('ku_submit_nonce', 'ku_nonce'); ?>
            <button type="submit" class="ku-submit">Generate Report</button>
        </form>
    </div>

    <?php if (!$is_nakshatra): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_key; ?>&libraries=places"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initAutocomplete(inputId, latId, lonId, tzId) {
                const input = document.getElementById(inputId);
                if (!input) return;

                let wrap = input.parentElement;
                if (!wrap.classList.contains('mr-city-input-wrap')) {
                    wrap.style.position = 'relative';
                }
                let loader = wrap.querySelector('.mr-city-loader');
                if (!loader) {
                    loader = document.createElement('span');
                    loader.className = 'mr-city-loader';
                    loader.innerHTML = '<span class="mr-city-spinner-ring"></span>';
                    input.insertAdjacentElement('afterend', loader);
                }

                function showLoader() {
                    loader.classList.add('is-active');
                    loader.style.display = 'inline-flex';
                }

                function hideLoader() {
                    loader.classList.remove('is-active');
                    loader.style.display = 'none';
                }

                let autocomplete;
                function ensureAC() {
                    if (autocomplete || typeof google === 'undefined' || !google.maps || !google.maps.places) return;
                    try {
                        autocomplete = new google.maps.places.Autocomplete(input, { types: ['(cities)'] });
                        autocomplete.addListener('place_changed', function () {
                            const place = autocomplete.getPlace();
                            if (!place || !place.geometry) return;
                            document.getElementById(latId).value = place.geometry.location.lat();
                            document.getElementById(lonId).value = place.geometry.location.lng();
                            if (typeof place.utc_offset_minutes !== 'undefined') {
                                document.getElementById(tzId).value = place.utc_offset_minutes / 60;
                            }
                            hideLoader();
                        });
                    } catch(e) { console.warn(e); }
                }

                ensureAC();
                input.addEventListener('focus', ensureAC);

                input.addEventListener('input', function () {
                    ensureAC();
                    if (this.value.trim().length >= 2) {
                        showLoader();
                    } else {
                        hideLoader();
                    }
                });

                document.addEventListener('click', function(e) {
                    if (e.target && e.target.closest('.pac-item')) {
                        hideLoader();
                    }
                });
            }

            <?php if ($is_couple): ?>
                initAutocomplete('b_place', 'b_lat', 'b_lon', 'b_tz');
                initAutocomplete('g_place', 'g_lat', 'g_lon', 'g_tz');
            <?php else: ?>
                initAutocomplete('place', 'lat', 'lon', 'tz');
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
    <?php
}

/* ====================================================
7. BACKEND HANDLER (FORM SUBMISSION)
==================================================== */
add_action('init', function () {
    if (isset($_POST['ku_action']) && $_POST['ku_action'] === 'submit_form') {
        // VAPT: CSRF Protection
        if (!isset($_POST['ku_nonce']) || !wp_verify_nonce($_POST['ku_nonce'], 'ku_submit_nonce')) {
            wp_die('Security verification failed. Please try again.', 'Security Error', ['response' => 403]);
        }

        // VAPT: Honeypot Anti-Spam Check
        if (!empty($_POST['ku_honeypot'])) {
            wp_die('Invalid submission detected.', 'Security Error', ['response' => 403]);
        }

        $type = sanitize_text_field($_POST['ku_type']);
        $lang = sanitize_text_field($_POST['lang']);
        $slug = '';
        $data = [];

        if ($type === 'kundali-nakshatra-match') {
            $data = [
                'boy_star' => intval($_POST['boy_star']),
                'girl_star' => intval($_POST['girl_star'])
            ];
            $slug = "star-{$data['boy_star']}-{$data['girl_star']}";
        } elseif (in_array($type, ['kundali-matching-short', 'kundali-matching-detailed', 'kundali-lagan-matching'])) {
            // VAPT: Strict Type Casting & Sanitization for Geolocation Data
            $data = [
                'boy_dob' => date('d/m/Y', strtotime(sanitize_text_field($_POST['b_dob']))),
                'boy_tob' => sanitize_text_field($_POST['b_tob']),
                'boy_lat' => floatval($_POST['b_lat']),
                'boy_lon' => floatval($_POST['b_lon']),
                'boy_tz' => floatval($_POST['b_tz']),
                'girl_dob' => date('d/m/Y', strtotime(sanitize_text_field($_POST['g_dob']))),
                'girl_tob' => sanitize_text_field($_POST['g_tob']),
                'girl_lat' => floatval($_POST['g_lat']),
                'girl_lon' => floatval($_POST['g_lon']),
                'girl_tz' => floatval($_POST['g_tz'])
            ];
            $slug = date('dmY', strtotime(sanitize_text_field($_POST['b_dob']))) . '-' . date('dmY', strtotime(sanitize_text_field($_POST['g_dob'])));
        } else {
            // VAPT: Strict Type Casting for Individual
            $data = [
                'dob' => date('d/m/Y', strtotime(sanitize_text_field($_POST['dob']))),
                'tob' => sanitize_text_field($_POST['tob']),
                'lat' => floatval($_POST['lat']),
                'lon' => floatval($_POST['lon']),
                'tz' => floatval($_POST['tz'])
            ];
            $slug = date('dmY', strtotime(sanitize_text_field($_POST['dob'])));
        }

        $base_url = !empty($_POST['ku_current_url']) ? esc_url_raw($_POST['ku_current_url']) : (wp_get_referer() ?: home_url());

        $redirect_url = add_query_arg([
            'ku_type' => $type,
            'ku_slug' => $slug,
            'ku_lang' => $lang
        ], $base_url);

        set_transient("kundali_{$type}_{$slug}", $data, 12 * HOUR_IN_SECONDS);
        wp_redirect($redirect_url);
        exit;
    }
});

/* ====================================================
8. RESULT DISPLAY & API LOGIC
==================================================== */
function kundali_display_result($type, $slug, $lang) {
    $data = get_transient("kundali_{$type}_{$slug}");
    if (!$data) {
        $restart_url = esc_url(remove_query_arg(['ku_slug', 'ku_lang']));
        echo "<div style='text-align:center; padding:50px;'>Session expired. <a href='" . $restart_url . "'>Restart here</a>.</div>";
        return;
    }

    $endpoints = [
        'kundali-matching-short' => 'matching/ashtakoot',
        'kundali-matching-detailed' => 'matching/ashtakoot-with-astro-details',
        'kundali-lagan-matching' => 'matching/aggregate-match',
        'kundali-nakshatra-match' => 'matching/nakshatra-match',
        'create-chart' => 'horoscope/chart-image',
        'house-explain' => 'horoscope/divisional-charts',
        'kundali-details-report' => 'horoscope/personal-characteristics'
    ];

    $endpoint = $endpoints[$type] ?? '';
    if (!$endpoint) return;

    $api_args = array_merge($data, ['api_key' => AF_KUNDALI_API_KEY, 'lang' => $lang]);

    // Extra args for specific endpoints
    if ($type === 'create-chart') {
        $api_args['div'] = 'D1';
        $api_args['color'] = '%23ff3366';
        $api_args['style'] = 'north';
    } elseif ($type === 'house-explain') {
        $api_args['div'] = 'D1';
        $api_args['response_type'] = 'planet_object';
    }

    // Construct full URL
    $api_url = "https://api.vedicastroapi.com/v3-json/" . $endpoint;
    $api_url = add_query_arg($api_args, $api_url);

    // API Caching Logic
    $cache_key = "ku_api_res_" . md5($api_url);
    $body = get_transient($cache_key);

    if (!$body) {
        $resp = wp_remote_get($api_url, ['timeout' => 45]);
        if (is_wp_error($resp)) {
            echo "<div style='text-align:center;'><h3>Error</h3><p>Connection failed.</p></div>";
            return;
        }
        $body = wp_remote_retrieve_body($resp);
        
        if ($type === 'create-chart' && strpos($body, '<svg') !== false) {
            set_transient($cache_key, $body, 12 * HOUR_IN_SECONDS);
        } else {
            $check = json_decode($body, true);
            if (isset($check['response'])) {
                set_transient($cache_key, $body, 12 * HOUR_IN_SECONDS);
            }
        }
    }

    // Tool Titles Mapping
    $tool_titles = [
        'kundali-matching-short' => 'Ashtakoot Matching (36-Point Guna Milan)',
        'kundali-matching-detailed' => 'Detailed Kundali Matching Report',
        'kundali-lagan-matching' => 'Aggregate Matching & Manglik Dosh',
        'kundali-nakshatra-match' => 'Nakshatra Compatibility Match',
        'create-chart' => 'Free Vedic Birth Chart (Lagna Chart)',
        'house-explain' => 'Planetary Positions & Degrees',
        'kundali-details-report' => 'Personalised Kundali Predictions'
    ];
    $current_title = $tool_titles[$type] ?? 'Kundali Report';

    echo "<div style='max-width:800px; margin:20px auto; font-family:\"Noto Sans\", sans-serif;'>";

    // H1 Heading
    echo "<h1 style='color:#6D28D9; font-weight:800; text-align:center; margin-bottom:20px; font-size:28px; border-bottom:2px solid #FDE68A; padding-bottom:15px;'>" . esc_html($current_title) . "</h1>";

    // Top Bar: Back Link & Language Toggle
    echo "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px;'>";
    echo "<a href='" . esc_url(remove_query_arg(['ku_type', 'ku_slug', 'ku_lang'])) . "' style='color:#6D28D9; font-weight:bold; text-decoration:none;'>&larr; Back to Tools</a>";
    
    // Language Toggle
    $en_url = esc_url(add_query_arg('ku_lang', 'en'));
    $hi_url = esc_url(add_query_arg('ku_lang', 'hi'));
    echo "<div style='background:#f1f5f9; padding:4px; border-radius:8px; display:inline-flex;'>";
    echo "<a href='{$en_url}' style='padding:6px 15px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:bold; " . ($lang === 'en' ? "background:#6D28D9; color:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);" : "color:#64748b;") . "'>English</a>";
    echo "<a href='{$hi_url}' style='padding:6px 15px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:bold; " . ($lang === 'hi' ? "background:#6D28D9; color:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);" : "color:#64748b;") . "'>हिन्दी</a>";
    echo "</div>";
    echo "</div>";

    // Render SVG for Create Chart
    if ($type === 'create-chart') {
        if (strpos($body, '<svg') !== false) {
            echo "<div style='text-align:center; background:#fff; padding:20px; border-radius:12px; margin:20px auto; max-width:600px; box-shadow:0 10px 30px rgba(0,0,0,0.1);'>";
            echo $body;
            echo "</div>";
            
            echo "<h3 style='text-align:center; margin-top:50px; margin-bottom:10px; color:#6D28D9; border-top:2px solid #FDE68A; padding-top:30px;'>Explore Other Astrology Tools</h3>";
            kundali_display_landing_page(false);
            echo "</div>";
            return;
        }
    }

    $json = json_decode($body, true);
    $res = $json['response'] ?? null;

    if (!$res) {
        echo "<div style='text-align:center;'><h3>API Error</h3><p>Could not retrieve data.</p></div>";
        return;
    }

    if (isset($res['bot_response'])) {
        echo "<div style='background:linear-gradient(135deg, #6D28D9, #3B1C5A); color:#fff; padding:25px; border-radius:12px; text-align:center; font-size:22px; font-weight:bold; box-shadow:0 10px 30px rgba(109, 40, 217, 0.2); margin-bottom:25px;'>";
        echo esc_html($res['bot_response']);
        echo "</div>";
    }
    // Inject responsive CSS for tool output
    echo "<style>
        .mr-city-loader { position: absolute !important; right: 12px !important; top: 50% !important; transform: translateY(-50%) !important; width: 20px !important; height: 20px !important; pointer-events: none !important; z-index: 15 !important; display: none; align-items: center; justify-content: center; }
        .mr-city-loader.is-active { display: inline-flex !important; }
        .mr-city-spinner-ring { width: 18px !important; height: 18px !important; border: 2.2px solid rgba(245, 130, 32, 0.22) !important; border-top-color: #F58220 !important; border-right-color: #D9A441 !important; border-radius: 50% !important; display: inline-block !important; box-sizing: border-box !important; animation: mrCitySpinRing 0.65s linear infinite !important; }
        @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .ku-factors-grid { display: grid; gap: 10px; grid-template-columns: 1fr; }
    .ku-profile-grid { display: grid; gap: 15px; grid-template-columns: 1fr; }
    .ku-profile-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ku-planets-grid { display: grid; gap: 10px; grid-template-columns: 1fr; }
    .ku-dosha-grid { display: grid; gap: 10px; grid-template-columns: 1fr; width: 100%; }
    
    .ku-card-pad { padding: 15px; }
    .ku-card-head { padding: 12px 15px; font-size: 14px; }
    .ku-card-split { padding: 8px 10px; }
    
    @media (min-width: 768px) {
        .ku-factors-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; }
        .ku-profile-grid { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }
        .ku-profile-inner { gap: 15px; }
        .ku-planets-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .ku-dosha-grid { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        
        .ku-card-pad { padding: 25px; }
        .ku-card-head { padding: 12px 15px; font-size: 15px; }
        .ku-card-split { padding: 10px 15px; }
    }
    </style>";

    // Render logic for different responses
    if ($type === 'kundali-lagan-matching') {
        echo "<div style='display:flex; flex-direction:column; gap:20px;'>";
        
        // Scores Row
        echo "<div style='display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:15px;'>";
        $scores = [];
        if (isset($res['score'])) $scores['Overall Match'] = $res['score'] . '/100';
        if (isset($res['ashtakoot_score'])) $scores['Ashtakoot Score'] = $res['ashtakoot_score'];
        if (isset($res['dashkoot_score'])) $scores['Dashkoot Score'] = $res['dashkoot_score'];
        
        foreach ($scores as $label => $val) {
            echo "<div style='background:linear-gradient(135deg, #6D28D9, #4C1D95); color:#fff; border-radius:12px; padding:20px; text-align:center; box-shadow:0 8px 15px rgba(109,40,217,0.15);'>";
            echo "<div style='font-size:14px; text-transform:uppercase; font-weight:800; letter-spacing:1px; opacity:0.8; margin-bottom:5px;'>{$label}</div>";
            echo "<div style='font-size:32px; font-weight:900;'>{$val}</div>";
            echo "</div>";
        }
        echo "</div>";

        // Dosha Row
        echo "<div style='display:flex; flex-wrap:wrap; gap:10px; background:#fff; padding:20px; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 4px 12px rgba(0,0,0,0.02);'>";
        echo "<div style='flex:1 1 100%; font-weight:800; color:#334155; margin-bottom:10px; font-size:16px; border-bottom:1px solid #f1f5f9; padding-bottom:8px;'>Dosha Analysis</div>";
        
        $doshas_config = [
            'Mangal Dosh' => ['key' => 'mangaldosh', 'points' => 'mangaldosh_points'],
            'Pitra Dosh' => ['key' => 'pitradosh', 'points' => 'pitradosh_points'],
            'Kaalsarp Dosh' => ['key' => 'kaalsarpdosh', 'points' => 'kaalsarp_points'],
            'Manglik (Saturn)' => ['key' => 'manglikdosh_saturn', 'points' => 'manglikdosh_saturn_points'],
            'Manglik (Rahu/Ketu)' => ['key' => 'manglikdosh_rahuketu', 'points' => 'manglikdosh_rahuketu_points'],
            'Rajju Dosh' => ['key' => 'rajjudosh', 'points' => 'rajjudosh_points'],
            'Vedha Dosh' => ['key' => 'vedhadosh', 'points' => 'vedhadosh_points'],
        ];

        echo "<div class='ku-dosha-grid'>";
        foreach ($doshas_config as $label => $keys) {
            $val = $res[$keys['key']] ?? null;
            if ($val === null) continue;
            
            $points = $res[$keys['points']] ?? [];
            $boy_pt = isset($points['boy']) ? $points['boy'] : '-';
            $girl_pt = isset($points['girl']) ? $points['girl'] : '-';

            // Format Points
            if (is_bool($boy_pt)) $boy_pt = $boy_pt ? 'Present' : 'Absent';
            if (is_bool($girl_pt)) $girl_pt = $girl_pt ? 'Present' : 'Absent';
            
            // Format Sentence
            $is_warning = true;
            $sentence = "";
            if (is_bool($val)) {
                $is_warning = $val;
                $sentence = $val ? 'Present' : 'Absent';
            } else {
                $sentence = ucfirst(strtolower((string)$val));
                $lower_val = strtolower($sentence);
                if (strpos($lower_val, 'not favorable') !== false || strpos($lower_val, 'not favourable') !== false) {
                    $is_warning = true;
                } elseif (strpos($lower_val, 'favorable') !== false || strpos($lower_val, 'favourable') !== false || strpos($lower_val, 'good for marriage') !== false) {
                    $is_warning = false;
                } else {
                    $is_warning = !(strpos($lower_val, 'no ') !== false || strpos($lower_val, 'doesn\'t') !== false || strpos($lower_val, 'does not') !== false || $lower_val === 'false');
                }
            }

            $accent = $is_warning ? '#EF4444' : '#10B981';
            $bg = $is_warning ? '#FEF2F2' : '#F0FDF4';

            echo "<div style='background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.02); display:flex; flex-direction:column;'>";
            
            // Header
            echo "<div class='ku-card-head' style='background:linear-gradient(to right, #6D28D9, #4C1D95); color:#fff; font-weight:800;'>{$label}</div>";
            
            // Boy / Girl Points
            if ($boy_pt !== '-' || $girl_pt !== '-') {
                echo "<div style='display:flex; border-bottom:1px solid #e2e8f0;'>";
                // Boy
                echo "<div class='ku-card-split' style='flex:1; text-align:center; border-right:1px solid #e2e8f0; background:#EFF6FF;'>";
                echo "<div style='font-size:11px; color:#3B82F6; text-transform:uppercase; font-weight:800; margin-bottom:4px;'>Boy</div>";
                echo "<div style='font-size:15px; font-weight:800; color:#1E3A8A;'>" . esc_html($boy_pt) . "</div>";
                echo "</div>";
                // Girl
                echo "<div class='ku-card-split' style='flex:1; text-align:center; background:#FDF4FF;'>";
                echo "<div style='font-size:11px; color:#D946EF; text-transform:uppercase; font-weight:800; margin-bottom:4px;'>Girl</div>";
                echo "<div style='font-size:15px; font-weight:800; color:#701A75;'>" . esc_html($girl_pt) . "</div>";
                echo "</div>";
                echo "</div>";
            }
            
            // Sentence Output
            echo "<div class='ku-card-pad' style='background:{$bg}; color:{$accent}; font-weight:600; line-height:1.5; flex-grow:1; font-size:14px;'>";
            echo esc_html($sentence);
            echo "</div>";
            
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";

        // Extended Response & Bot Response
        if (!empty($res['extended_response']) || !empty($res['bot_response'])) {
            echo "<div style='background:#FFFBEB; border-left:4px solid #F59E0B; padding:20px; border-radius:0 12px 12px 0; color:#475569; font-size:15px; line-height:1.6; box-shadow:0 4px 10px rgba(0,0,0,0.02);'>";
            echo "<h4 style='margin:0 0 10px 0; color:#D97706; font-size:16px;'>Conclusion</h4>";
            if (!empty($res['bot_response'])) {
                echo "<p style='font-weight:bold; margin-top:0; color:#B45309;'>" . esc_html($res['bot_response']) . "</p>";
            }
            if (!empty($res['extended_response'])) {
                echo esc_html($res['extended_response']);
            }
            echo "</div>";
        }
        
        echo "</div>";
    } elseif ($type === 'kundali-details-report') {
        echo "<div style='display:flex; flex-direction:column; gap:12px;'>";
        foreach ($res as $house) {
            $h_num = esc_html($house['current_house']);
            $verb_loc = esc_html($house['verbal_location']);
            $pred = esc_html($house['personalised_prediction']);
            $zodiac = esc_html($house['current_zodiac']);
            $lord = esc_html($house['lord_of_zodiac']);
            $lord_str = esc_html($house['lord_strength']);
            $l_z_loc = esc_html($house['lord_zodiac_location']);
            $l_h_loc = esc_html($house['lord_house_location']);

            echo "<div style='background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05); border:1px solid #e2e8f0;'>";
            
            // Header
            echo "<div style='background:linear-gradient(135deg, #6D28D9, #4C1D95); padding:10px 15px; color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:5px;'>";
            echo "<h3 style='margin:0; font-size:17px; font-weight:800; color:#fff;'>House {$h_num}</h3>";
            echo "<span style='font-size:13px; font-weight:600; background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:12px;'>{$verb_loc}</span>";
            echo "</div>";

            // Details Flex Box
            echo "<div style='padding:12px 15px;'>";
            echo "<div style='display:flex; flex-wrap:wrap; gap:10px; margin-bottom:12px; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #e2e8f0;'>";
            
            $items = [
                'Zodiac' => $zodiac,
                'Lord' => $lord,
                'Lord Strength' => $lord_str,
                'Zodiac Loc.' => $l_z_loc,
                'House Loc.' => $l_h_loc
            ];
            foreach ($items as $k => $v) {
                if ($v !== '') {
                    echo "<div style='flex:1 1 28%; min-width:80px;'>";
                    echo "<div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800; line-height:1; margin-bottom:3px;'>{$k}</div>";
                    echo "<div style='font-weight:700; color:#0f172a; font-size:13px; line-height:1.2;'>{$v}</div>";
                    echo "</div>";
                }
            }
            echo "</div>";

            // Prediction
            echo "<div style='position:relative; padding-left:12px; border-left:3px solid #FF5733;'>";
            echo "<h4 style='margin:0 0 4px 0; color:#334155; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;'>Prediction</h4>";
            echo "<p style='margin:0; color:#475569; line-height:1.45; font-size:14px;'>{$pred}</p>";
            echo "</div>";

            echo "</div></div>";
        }
        echo "</div>";
    } elseif ($type === 'house-explain') {
        echo "<div style='display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:15px;'>";
        foreach ($res as $key => $planet) {
            if (is_numeric($key)) {
                $name = esc_html($planet['full_name']);
                $zod = esc_html($planet['zodiac']);
                $hou = esc_html($planet['house']);
                $deg = number_format($planet['local_degree'], 2) . "°";
                
                echo "<div style='background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.04); border:1px solid #e2e8f0;'>";
                echo "<div style='background:linear-gradient(135deg, #6D28D9, #4C1D95); color:#fff; padding:12px 15px; font-weight:800; font-size:16px; text-align:center;'>{$name}</div>";
                echo "<div style='padding:15px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;'>";
                
                echo "<div style='text-align:center; flex:1;'>";
                echo "<div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800;'>Zodiac</div>";
                echo "<div style='font-size:14px; font-weight:700; color:#0f172a; margin-top:3px;'>{$zod}</div>";
                echo "</div>";
                
                echo "<div style='text-align:center; flex:1; border-left:1px solid #f1f5f9; border-right:1px solid #f1f5f9; padding:0 5px;'>";
                echo "<div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800;'>House</div>";
                echo "<div style='font-size:14px; font-weight:700; color:#0f172a; margin-top:3px;'>{$hou}</div>";
                echo "</div>";
                
                echo "<div style='text-align:center; flex:1;'>";
                echo "<div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800;'>Degree</div>";
                echo "<div style='font-size:14px; font-weight:700; color:#0f172a; margin-top:3px;'>{$deg}</div>";
                echo "</div>";
                
                echo "</div></div>";
            }
        }
        echo "</div>";
    } elseif (isset($res['score'])) {
        // Handle Ashtakoot or Nakshatra match responses
        $factors = ['tara', 'gana', 'yoni', 'bhakoot', 'grahamaitri', 'vasya', 'nadi', 'varna', 'dina', 'mahendra', 'sthree', 'rasi', 'rasiathi', 'rajju', 'vedha'];
        
        // Calculate max score dynamically
        $max_score = 0;
        foreach ($factors as $f) {
            if (isset($res[$f])) {
                $max_score += floatval($res[$f]['full_score'] ?? 0);
            }
        }
        if ($max_score == 0) $max_score = 36; // fallback

        // Main Score Hero Block
        $total_score = $res['score'];
        echo "<div style='background:linear-gradient(135deg, #FF6B4A, #D9381E); border-radius:16px; padding:30px; text-align:center; color:#fff; margin-bottom:25px; box-shadow:0 10px 25px rgba(217,56,30,0.25); position:relative; overflow:hidden;'>";
        echo "<div style='position:absolute; top:-30px; right:-20px; opacity:0.1; font-size:180px; font-weight:900;'>{$total_score}</div>";
        echo "<div style='font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:2px; margin-bottom:10px; opacity:0.9;'>Total Compatibility Score</div>";
        echo "<div style='font-size:64px; font-weight:900; line-height:1;'>{$total_score}<span style='font-size:24px; opacity:0.7;'> / {$max_score}</span></div>";
        echo "</div>";

        // Bot Response
        if (!empty($res['bot_response'])) {
            echo "<div style='background:#FFFBEB; border-left:4px solid #F59E0B; padding:15px; border-radius:0 12px 12px 0; color:#B45309; font-size:16px; font-weight:bold; margin-bottom:25px; box-shadow:0 4px 6px rgba(0,0,0,0.02);'>";
            echo esc_html($res['bot_response']);
            echo "</div>";
        }

        // Factors Grid
        echo "<div class='ku-factors-grid' style='margin-bottom:30px;'>";
        foreach ($factors as $f) {
            if (isset($res[$f])) {
                $item = $res[$f];
                $name = $item['name'] ?? ucfirst($f);
                $score = $item[$f] ?? 0;
                $full = $item['full_score'] ?? 0;
                $desc = $item['description'] ?? '';
                
                // Get Boy/Girl Specific factors
                $boy_val = $item["boy_{$f}"] ?? '-';
                $girl_val = $item["girl_{$f}"] ?? '-';
                
                // For Bhakoot, the keys are boy_rasi_name / girl_rasi_name
                if ($f === 'bhakoot') {
                    $boy_val = $item['boy_rasi_name'] ?? '-';
                    $girl_val = $item['girl_rasi_name'] ?? '-';
                }
                // For Grahamaitri, the keys are boy_lord / girl_lord
                if ($f === 'grahamaitri') {
                    $boy_val = $item['boy_lord'] ?? '-';
                    $girl_val = $item['girl_lord'] ?? '-';
                }

                $ratio = ($full > 0) ? (floatval($score) / floatval($full)) : 0;
                $accent = ($ratio >= 0.8) ? '#10B981' : (($ratio >= 0.4) ? '#F59E0B' : '#EF4444');
                $bg = ($ratio >= 0.8) ? '#F0FDF4' : (($ratio >= 0.4) ? '#FFFBEB' : '#FEF2F2');
                
                echo "<div style='background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.02); display:flex; flex-direction:column;'>";
                
                // Header
                echo "<div class='ku-card-head' style='display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0;'>";
                echo "<div style='color:#334155; font-weight:800; text-transform:uppercase;'>" . esc_html($name) . "</div>";
                echo "<div style='background:{$bg}; color:{$accent}; padding:3px 10px; border-radius:20px; font-size:13px; font-weight:900;'>" . esc_html($score) . "/" . esc_html($full) . "</div>";
                echo "</div>";

                // Split Panel
                if ($boy_val !== '-' || $girl_val !== '-') {
                    echo "<div style='display:flex; border-bottom:1px solid #e2e8f0;'>";
                    // Boy
                    echo "<div class='ku-card-split' style='flex:1; text-align:center; border-right:1px solid #e2e8f0; background:#EFF6FF;'>";
                    echo "<div style='font-size:10px; color:#3B82F6; text-transform:uppercase; font-weight:800; margin-bottom:2px;'>Boy</div>";
                    echo "<div style='font-size:14px; font-weight:800; color:#1E3A8A;'>" . esc_html(ucfirst($boy_val)) . "</div>";
                    echo "</div>";
                    // Girl
                    echo "<div class='ku-card-split' style='flex:1; text-align:center; background:#FDF4FF;'>";
                    echo "<div style='font-size:10px; color:#D946EF; text-transform:uppercase; font-weight:800; margin-bottom:2px;'>Girl</div>";
                    echo "<div style='font-size:14px; font-weight:800; color:#701A75;'>" . esc_html(ucfirst($girl_val)) . "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                
                // Description Footer
                if ($desc) {
                    echo "<div class='ku-card-head' style='color:#64748b; line-height:1.4; background:#f8fafc; font-weight:600;'>";
                    echo esc_html(ucfirst($desc));
                    echo "</div>";
                }
                
                echo "</div>";
            }
        }
        echo "</div>";
        
        // Render Planetary details if available (Detailed match)
        if (isset($res['boy_astro_details']) && isset($res['girl_astro_details'])) {
            echo "<h3 style='margin:10px 0 20px 0; color:#6D28D9; font-size:24px; font-weight:800; text-align:center; border-bottom:2px solid #FDE68A; padding-bottom:10px;'>Astrological Profiles</h3>";
            echo "<div class='ku-profile-grid'>";
            
            // Boy Dashboard
            echo "<div class='ku-card-pad' style='background:linear-gradient(to bottom, #EFF6FF, #fff); border-radius:16px; border:1px solid #BFDBFE; box-shadow:0 8px 20px rgba(59,130,246,0.08);'>";
            echo "<h4 style='color:#1D4ED8; margin:0 0 20px 0; font-size:20px; font-weight:900; text-transform:uppercase; border-bottom:2px solid #BFDBFE; padding-bottom:10px;'>Boy's Profile</h4>";
            
            $boy = $res['boy_astro_details'];
            $profile_keys = [
                'Rasi' => $boy['rasi'] ?? '',
                'Nakshatra' => ($boy['nakshatra'] ?? '') . " (Pada " . ($boy['nakshatra_pada'] ?? '') . ")",
                'Ascendant' => $boy['ascendant_sign'] ?? '',
                'Gana' => $boy['gana'] ?? '',
                'Yoni' => $boy['yoni'] ?? '',
                'Vasya' => $boy['vasya'] ?? '',
                'Nadi' => $boy['nadi'] ?? '',
                'Varna' => $boy['varna'] ?? '',
                'Paya' => $boy['paya'] ?? '',
                'Tatva' => $boy['tatva'] ?? '',
                'Birth Dasa' => ($boy['birth_dasa'] ?? '') . " (End: " . ($boy['birth_dasa_time'] ?? '') . ")",
                'Current Dasa' => ($boy['current_dasa'] ?? '') . " (End: " . ($boy['current_dasa_time'] ?? '') . ")",
                'Lucky Gems' => isset($boy['lucky_gem']) ? implode(', ', $boy['lucky_gem']) : '',
                'Lucky Colors' => isset($boy['lucky_colors']) ? implode(', ', $boy['lucky_colors']) : '',
                'Lucky Numbers' => isset($boy['lucky_num']) ? implode(', ', $boy['lucky_num']) : '',
                'Name Letters' => isset($boy['lucky_name_start']) ? implode(', ', $boy['lucky_name_start']) : '',
            ];

            echo "<div class='ku-profile-inner'>";
            foreach ($profile_keys as $k => $v) {
                if (!empty($v) && $v !== ' (Pada )' && $v !== ' (End: )') {
                    echo "<div><div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800;'>{$k}</div><div style='font-size:14px; font-weight:700; color:#1e293b; text-transform:capitalize;'>" . esc_html($v) . "</div></div>";
                }
            }
            echo "</div>";
            echo "</div>";

            // Girl Dashboard
            echo "<div class='ku-card-pad' style='background:linear-gradient(to bottom, #FDF4FF, #fff); border-radius:16px; border:1px solid #FBCFE8; box-shadow:0 8px 20px rgba(217,70,239,0.08);'>";
            echo "<h4 style='color:#C026D3; margin:0 0 20px 0; font-size:20px; font-weight:900; text-transform:uppercase; border-bottom:2px solid #FBCFE8; padding-bottom:10px;'>Girl's Profile</h4>";
            
            $girl = $res['girl_astro_details'];
            $profile_keys_g = [
                'Rasi' => $girl['rasi'] ?? '',
                'Nakshatra' => ($girl['nakshatra'] ?? '') . " (Pada " . ($girl['nakshatra_pada'] ?? '') . ")",
                'Ascendant' => $girl['ascendant_sign'] ?? '',
                'Gana' => $girl['gana'] ?? '',
                'Yoni' => $girl['yoni'] ?? '',
                'Vasya' => $girl['vasya'] ?? '',
                'Nadi' => $girl['nadi'] ?? '',
                'Varna' => $girl['varna'] ?? '',
                'Paya' => $girl['paya'] ?? '',
                'Tatva' => $girl['tatva'] ?? '',
                'Birth Dasa' => ($girl['birth_dasa'] ?? '') . " (End: " . ($girl['birth_dasa_time'] ?? '') . ")",
                'Current Dasa' => ($girl['current_dasa'] ?? '') . " (End: " . ($girl['current_dasa_time'] ?? '') . ")",
                'Lucky Gems' => isset($girl['lucky_gem']) ? implode(', ', $girl['lucky_gem']) : '',
                'Lucky Colors' => isset($girl['lucky_colors']) ? implode(', ', $girl['lucky_colors']) : '',
                'Lucky Numbers' => isset($girl['lucky_num']) ? implode(', ', $girl['lucky_num']) : '',
                'Name Letters' => isset($girl['lucky_name_start']) ? implode(', ', $girl['lucky_name_start']) : '',
            ];

            echo "<div class='ku-profile-inner'>";
            foreach ($profile_keys_g as $k => $v) {
                if (!empty($v) && $v !== ' (Pada )' && $v !== ' (End: )') {
                    echo "<div><div style='font-size:10px; color:#64748b; text-transform:uppercase; font-weight:800;'>{$k}</div><div style='font-size:14px; font-weight:700; color:#1e293b; text-transform:capitalize;'>" . esc_html($v) . "</div></div>";
                }
            }
            echo "</div>";
            echo "</div>"; 
            
            echo "</div>"; 
        }

        // Planetary Details
        if (isset($res['boy_planetary_details']) && isset($res['girl_planetary_details'])) {
            echo "<h3 style='margin:40px 0 20px 0; color:#6D28D9; font-size:24px; font-weight:800; text-align:center; border-bottom:2px solid #FDE68A; padding-bottom:10px;'>Planetary Positions</h3>";

            $render_planets = function($planets, $bg_color, $border_color, $title_color, $title) {
                echo "<div class='ku-card-pad' style='background:{$bg_color}; border-radius:16px; border:1px solid {$border_color}; margin-bottom:25px;'>";
                echo "<h4 style='color:{$title_color}; margin:0 0 15px 0; font-size:18px; text-transform:uppercase;'>{$title}</h4>";
                echo "<div class='ku-planets-grid'>";
                foreach ($planets as $p) {
                    $name = $p['full_name'] ?? '';
                    $zod = $p['zodiac'] ?? '';
                    $house = $p['house'] ?? '';
                    $deg = isset($p['local_degree']) ? number_format($p['local_degree'], 2) . '°' : '';
                    $nak = $p['nakshatra'] ?? '';
                    
                    if (!$name) continue;

                    echo "<div style='background:#fff; border-radius:8px; padding:12px; border:1px solid {$border_color}; box-shadow:0 2px 4px rgba(0,0,0,0.02);'>";
                    echo "<div style='font-weight:900; color:{$title_color}; border-bottom:1px solid #f1f5f9; padding-bottom:5px; margin-bottom:8px;'>{$name}</div>";
                    echo "<div style='display:flex; justify-content:space-between; margin-bottom:3px;'><span style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:800;'>Zodiac</span><span style='font-weight:700; font-size:12px;'>{$zod}</span></div>";
                    echo "<div style='display:flex; justify-content:space-between; margin-bottom:3px;'><span style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:800;'>House</span><span style='font-weight:700; font-size:12px;'>{$house}</span></div>";
                    echo "<div style='display:flex; justify-content:space-between; margin-bottom:3px;'><span style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:800;'>Degree</span><span style='font-weight:700; font-size:12px;'>{$deg}</span></div>";
                    echo "<div style='display:flex; justify-content:space-between;'><span style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:800;'>Nak.</span><span style='font-weight:700; font-size:12px;'>{$nak}</span></div>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
            };

            $render_planets($res['boy_planetary_details'], '#EFF6FF', '#BFDBFE', '#1D4ED8', 'Boy\'s Planets');
            $render_planets($res['girl_planetary_details'], '#FDF4FF', '#FBCFE8', '#C026D3', 'Girl\'s Planets');
        }
    }

    echo "<h3 style='text-align:center; margin-top:50px; margin-bottom:10px; color:#6D28D9; border-top:2px solid #FDE68A; padding-top:30px;'>Explore Other Astrology Tools</h3>";
    kundali_display_landing_page(false);

    echo "</div>";
}

/* ====================================================
9. BILINGUAL FAQ SECTION
==================================================== */
function kundali_display_faq($type) {
    // Determine context based on tool type
    $is_matching = in_array($type, ['kundali-matching-short', 'kundali-matching-detailed', 'kundali-lagan-matching', 'kundali-nakshatra-match']);
    
    ?>
    <style>
        .mr-city-loader { position: absolute !important; right: 12px !important; top: 50% !important; transform: translateY(-50%) !important; width: 20px !important; height: 20px !important; pointer-events: none !important; z-index: 15 !important; display: none; align-items: center; justify-content: center; }
        .mr-city-loader.is-active { display: inline-flex !important; }
        .mr-city-spinner-ring { width: 18px !important; height: 18px !important; border: 2.2px solid rgba(245, 130, 32, 0.22) !important; border-top-color: #F58220 !important; border-right-color: #D9A441 !important; border-radius: 50% !important; display: inline-block !important; box-sizing: border-box !important; animation: mrCitySpinRing 0.65s linear infinite !important; }
        @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .ku-faq-wrap { max-width: 800px; margin: 40px auto; font-family: 'Noto Sans', sans-serif; }
        .ku-faq-title { text-align: center; color: #6D28D9; font-size: 24px; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #FDE68A; padding-bottom: 10px; }
        .ku-faq-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow: hidden; }
        .ku-faq-q { background: #f8fafc; padding: 15px 20px; font-weight: bold; color: #1e293b; border-bottom: 1px solid #e2e8f0; font-size: 16px; }
        .ku-faq-a { padding: 20px; font-size: 15px; color: #475569; line-height: 1.6; }
        .ku-faq-a.hi { border-top: 1px dashed #cbd5e1; background: #FFFBEB; font-family: 'Hind', sans-serif; font-size: 16px; color: #334155; }
        .lang-badge { display: inline-block; padding: 2px 6px; font-size: 11px; font-weight: bold; border-radius: 4px; margin-right: 8px; vertical-align: middle; }
        .badge-en { background: #DBEAFE; color: #1E40AF; }
        .badge-hi { background: #FFEDD5; color: #C2410C; }
    </style>
    <div class="ku-faq-wrap">
        <h2 class="ku-faq-title">Frequently Asked Questions (सामान्य प्रश्न)</h2>

        <?php if ($is_matching): ?>
            <!-- MATCHING FAQS -->
            <div class="ku-faq-item">
                <div class="ku-faq-q">What is Ashtakoot Guna Milan? (अष्टकूट गुण मिलान क्या है?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> Ashtakoot Milan is a Vedic astrology method that compares 8 different parameters (Kootas) between the boy and girl to determine marriage compatibility. A score out of 36 is calculated. A score above 18 is considered acceptable.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> अष्टकूट मिलान वैदिक ज्योतिष की एक विधि है जिसमें विवाह अनुकूलता निर्धारित करने के लिए लड़के और लड़की के बीच 8 विभिन्न मापदंडों (कूटों) की तुलना की जाती है। 36 में से स्कोर की गणना की जाती है। 18 से ऊपर के स्कोर को विवाह के लिए अनुकूल माना जाता है।</div>
            </div>
            <div class="ku-faq-item">
                <div class="ku-faq-q">How does Mangal Dosh affect marriage? (मंगल दोष विवाह को कैसे प्रभावित करता है?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> Mangal Dosh (Manglik) occurs when Mars is placed in specific houses in the birth chart. It can cause friction or delays in marriage. If both partners are Manglik, the dosh is often considered canceled or balanced out.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> मंगल दोष तब होता है जब मंगल जन्म कुंडली के कुछ विशिष्ट भावों में स्थित होता है। यह विवाह में देरी या तनाव का कारण बन सकता है। यदि दोनों साथी मांगलिक हैं, तो दोष अक्सर संतुलित या रद्द माना जाता है।</div>
            </div>
            <div class="ku-faq-item">
                <div class="ku-faq-q">What is Nadi Dosh and is it severe? (नाड़ी दोष क्या है और क्या यह गंभीर है?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> Nadi represents health and progeny. If both partners have the same Nadi (Adi, Madhya, or Antya), Nadi Dosh is formed, resulting in a deduction of 8 points. It is considered severe, especially regarding childbirth, but can be mitigated with specific astrological remedies.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> नाड़ी स्वास्थ्य और संतान का प्रतिनिधित्व करती है। यदि दोनों भागीदारों की नाड़ी समान है, तो नाड़ी दोष बनता है, जिससे 8 अंकों की कटौती होती है। इसे संतानोत्पत्ति के संबंध में गंभीर माना जाता है, लेकिन विशिष्ट ज्योतिषीय उपायों से इसे कम किया जा सकता है।</div>
            </div>
        <?php else: ?>
            <!-- GENERAL/CHART FAQS -->
            <div class="ku-faq-item">
                <div class="ku-faq-q">What is a Lagna Chart (D1)? (लग्न कुंडली (D1) क्या है?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> The Lagna or D1 Chart is the primary birth chart in Vedic astrology. It maps the precise positions of the planets, sun, and moon in the 12 zodiac signs at the exact moment and location of your birth. It serves as the foundation for all astrological predictions.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> लग्न या D1 चार्ट वैदिक ज्योतिष में प्राथमिक जन्म कुंडली है। यह आपके जन्म के सटीक समय और स्थान पर 12 राशियों में ग्रहों, सूर्य और चंद्रमा की सटीक स्थिति को दर्शाता है। यह सभी ज्योतिषीय भविष्यवाणियों की नींव के रूप में कार्य करता है।</div>
            </div>
            <div class="ku-faq-item">
                <div class="ku-faq-q">Why is the exact Time of Birth important? (जन्म का सटीक समय क्यों महत्वपूर्ण है?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> In Vedic Astrology, the Ascendant (Lagna) changes roughly every 2 hours, and detailed divisional charts change every few minutes. An accurate time of birth ensures that your houses and planetary degrees are calculated correctly for precise predictions.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> वैदिक ज्योतिष में, लग्न (Ascendant) लगभग हर 2 घंटे में बदलता है, और विस्तृत सूक्ष्म कुंडलियाँ हर कुछ मिनटों में बदलती हैं। जन्म का सटीक समय यह सुनिश्चित करता है कि सटीक भविष्यवाणियों के लिए आपके भावों और ग्रहों की डिग्री की गणना सही ढंग से की गई है।</div>
            </div>
            <div class="ku-faq-item">
                <div class="ku-faq-q">How do planetary houses affect my life? (ग्रहों के भाव मेरे जीवन को कैसे प्रभावित करते हैं?)</div>
                <div class="ku-faq-a"><span class="lang-badge badge-en">EN</span> Your Kundali is divided into 12 houses, each representing different aspects of life such as career, wealth, marriage, and health. The planet ruling a house and the planets occupying it heavily influence events and characteristics related to that area of your life.</div>
                <div class="ku-faq-a hi"><span class="lang-badge badge-hi">HI</span> आपकी कुंडली 12 भावों में विभाजित है, जिनमें से प्रत्येक जीवन के विभिन्न पहलुओं जैसे करियर, धन, विवाह और स्वास्थ्य का प्रतिनिधित्व करता है। किसी भाव पर शासन करने वाला ग्रह और उसमें बैठे ग्रह आपके जीवन के उस क्षेत्र से जुड़ी घटनाओं और विशेषताओं को गहराई से प्रभावित करते हैं।</div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

