-- Update Snippet #73 (Kundali Engine & Cross-Sell)
UPDATE `af_snippets` SET `code` = '/**
 * Unified Kundali Engine for MeraRashifal (V2 Redesigned)
 * Shortcode: [kundali_calc_engine]
 * Supports: Kundali Matching, Create Chart, House Explain, Kundali Details
 * Harmonized with Horoscope Snippet 63 and Panchang Snippet 64
 * Palette: #FFFBEB warm amber, #FDE68A gold border, #B45309 / #78350F deep amber text
 */

if (!defined(''ABSPATH'')) {
    exit;
}

/* ====================================================
1. CONSTANTS & API KEYS
==================================================== */
$api_key = defined(''VEDICASTRO_API_KEY'') ? VEDICASTRO_API_KEY : ''8d9d828d-3f9b-531a-a640-88c00469c300'';
if (!defined(''AF_KUNDALI_API_KEY'')) {
    define(''AF_KUNDALI_API_KEY'', $api_key);
}

/* ====================================================
2. SEO OPTIMIZATION (Yoast Compatible)
==================================================== */
if (!function_exists(''kundali_get_seo_data'')) {
function kundali_get_seo_data() {
    $type = isset($_GET[''ku_type'']) ? sanitize_text_field($_GET[''ku_type'']) : '''';
    $data = [
        ''default'' => [
            ''title'' => ''Free Kundali & Matching Online | Vedic Astrology - MeraRashifal.com'',
            ''desc''  => ''Generate free Kundali, check Ashtakoot Kundali Matching, get your birth chart, and read personalized astrological predictions.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-default.webp''
        ],
        ''kundali-matching-short'' => [
            ''title'' => ''Free Kundali Matching | Ashtakoot Guna Milan - MeraRashifal.com'',
            ''desc''  => ''Check your Kundali matching score online. Get free Ashtakoot Guna Milan for marriage based on Vedic Astrology.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-matching.webp''
        ],
        ''kundali-matching-detailed'' => [
            ''title'' => ''Detailed Kundali Matching Report Online - MeraRashifal.com'',
            ''desc''  => ''Get a comprehensive Kundali matching report including planetary details and Astro compatibility scores for marriage.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-detailed-match.webp''
        ],
        ''kundali-lagan-matching'' => [
            ''title'' => ''Kundali Aggregate Matching & Manglik Dosh Check - MeraRashifal.com'',
            ''desc''  => ''Check your aggregate Kundali matching score, including Mangal Dosh, Pitra Dosh, and Kaalsarp Dosh analysis for marriage.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-lagan-match.webp''
        ],
        ''kundali-nakshatra-match'' => [
            ''title'' => ''Nakshatra Compatibility Match Online - MeraRashifal.com'',
            ''desc''  => ''Check marriage compatibility based purely on the Nakshatra (birth star) of the boy and girl.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-nakshatra-match.webp''
        ],
        ''create-chart'' => [
            ''title'' => ''Free Janam Kundali | Vedic Birth Chart (Lagna) - MeraRashifal.com'',
            ''desc''  => ''Generate your free online Janam Kundali and Lagna Chart (D1) based on authentic Vedic planetary calculations.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-chart.webp''
        ],
        ''house-explain'' => [
            ''title'' => ''Planetary Positions & House Analysis - MeraRashifal.com'',
            ''desc''  => ''Understand the planetary positions, degrees, and house placements in your birth chart.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-planets.webp''
        ],
        ''kundali-details-report'' => [
            ''title'' => ''Personalised Kundali Predictions & Life Report - MeraRashifal.com'',
            ''desc''  => ''Read detailed personal characteristics, career, health, and relationship predictions based on your Kundali houses.'',
            ''img''   => ''https://merarashifal.com/wp-content/uploads/2026/05/kundali-report.webp''
        ]
    ];

    return $data[$type] ?? $data[''default''];
}
}

// Hook into Yoast SEO Title & Description Filters
add_filter(''wpseo_title'', function ($title) {
    if (isset($_GET[''ku_type''])) {
        $seo = kundali_get_seo_data();
        return $seo[''title''];
    }
    return $title;
}, 99);

add_filter(''wpseo_metadesc'', function ($desc) {
    if (isset($_GET[''ku_type''])) {
        $seo = kundali_get_seo_data();
        return $seo[''desc''];
    }
    return $desc;
}, 99);

// Add OpenGraph Tags for Social Sharing
add_action(''wp_head'', function () {
    if (isset($_GET[''ku_type''])) {
        $seo = kundali_get_seo_data();
        echo ''<meta property="og:title" content="'' . esc_attr($seo[''title'']) . ''" />'' . "\\n";
        echo ''<meta property="og:description" content="'' . esc_attr($seo[''desc'']) . ''" />'' . "\\n";
        echo ''<meta property="og:image" content="'' . esc_url($seo[''img'']) . ''" />'' . "\\n";
        echo ''<meta name="twitter:card" content="summary_large_image" />'' . "\\n";
        echo ''<meta name="twitter:title" content="'' . esc_attr($seo[''title'']) . ''" />'' . "\\n";
        echo ''<meta name="twitter:description" content="'' . esc_attr($seo[''desc'']) . ''" />'' . "\\n";
        echo ''<meta name="twitter:image" content="'' . esc_url($seo[''img'']) . ''" />'' . "\\n";
    }
}, 5);

// Analytics tracking
add_action(''wp_head'', function () {
    if (isset($_GET[''ku_type''])) {
        ?>
        <script>
        (function() {
            window.addEventListener(''load'', function() {
                if (typeof gtag === ''function'') {
                    gtag(''event'', ''page_view'', { page_title: document.title, page_location: window.location.href, page_path: window.location.pathname });
                } else if (typeof ga === ''function'') {
                    ga(''send'', ''pageview'');
                }
            });
        })();
        </script>
        <?php
    }
}, 100);

/* ====================================================
3. SHARED STYLES FOR KUNDALI ENGINE
==================================================== */
if (!function_exists(''mr_ku_print_styles'')) {
function mr_ku_print_styles() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <style>
    /* Global Container */
    .mr-ku-wrap {
        background-color: #FFFBEB !important;
        font-family: ''Noto Sans'', ''Noto Sans Devanagari'', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #1E293B;
        border-radius: 14px;
        border: 1px solid #FDE68A;
        padding: 16px 20px;
        box-shadow: 0 4px 16px rgba(180, 83, 9, 0.04);
        max-width: 900px;
        margin: 15px auto;
        box-sizing: border-box;
    }
    @media (max-width: 600px) {
        .mr-ku-wrap {
            padding: 12px 14px;
            border-radius: 10px;
        }
    }

    /* City Autocomplete Ring Loader */
    .mr-city-loader {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 20px !important;
        height: 20px !important;
        pointer-events: none !important;
        z-index: 15 !important;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .mr-city-loader.is-active { display: inline-flex !important; }
    .mr-city-spinner-ring {
        width: 18px !important;
        height: 18px !important;
        border: 2.2px solid rgba(217, 119, 6, 0.22) !important;
        border-top-color: #D97706 !important;
        border-right-color: #B45309 !important;
        border-radius: 50% !important;
        display: inline-block !important;
        box-sizing: border-box !important;
        animation: mrCitySpinRing 0.65s linear infinite !important;
    }
    @keyframes mrCitySpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    /* Top Header */
    .mr-ku-header {
        text-align: center;
        margin-bottom: 22px;
        padding-bottom: 14px;
        border-bottom: 1px dashed #FCD34D;
    }
    .mr-ku-header h1 {
        font-size: 22px !important;
        font-weight: 800 !important;
        color: #B45309 !important;
        margin: 0 0 6px 0 !important;
        letter-spacing: -0.2px;
    }
    @media (min-width: 768px) {
        .mr-ku-header h1 { font-size: 25px !important; }
    }
    .mr-ku-header p {
        font-size: 13.5px;
        color: #78350F;
        margin: 0;
        font-weight: 600;
    }

    /* Landing Tools Grid */
    .mr-ku-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 14px;
        margin-bottom: 15px;
    }
    @media (max-width: 600px) {
        .mr-ku-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
    .mr-ku-card {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 10px;
        padding: 16px 14px;
        text-align: center;
        text-decoration: none !important;
        color: inherit;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.03);
    }
    .mr-ku-card:hover {
        transform: translateY(-2px);
        border-color: #D97706;
        box-shadow: 0 6px 16px rgba(180, 83, 9, 0.08);
        background: #FFFDF7;
    }
    .mr-ku-card-icon {
        font-size: 26px;
        margin-bottom: 6px;
    }
    .mr-ku-card h3 {
        font-size: 15.5px !important;
        font-weight: 800 !important;
        color: #78350F !important;
        margin: 0 0 6px 0 !important;
        line-height: 1.3;
    }
    .mr-ku-card p {
        font-size: 12.5px;
        color: #64748B;
        line-height: 1.45;
        margin: 0 0 14px 0;
        flex-grow: 1;
    }
    .mr-ku-card-btn {
        display: inline-block;
        padding: 6px 14px;
        background: #FEF3C7;
        color: #B45309 !important;
        border: 1px solid #FDE68A;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        transition: all 0.15s ease;
    }
    .mr-ku-card:hover .mr-ku-card-btn {
        background: #B45309;
        color: #FFFFFF !important;
        border-color: #B45309;
    }

    /* Compact Grid for Footer Tool Navigation */
    .mr-ku-compact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 15px;
    }
    @media (max-width: 820px) {
        .mr-ku-compact-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }
    @media (max-width: 540px) {
        .mr-ku-compact-grid {
            grid-template-columns: 1fr;
            gap: 9px;
        }
    }
    .mr-ku-compact-card {
        background: linear-gradient(180deg, #FFFFFF 0%, #FFFDF9 100%);
        border: 1px solid #FDE68A;
        border-radius: 10px;
        padding: 10px 12px;
        text-decoration: none !important;
        color: #78350F !important;
        transition: all 0.2s ease;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.03);
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 52px;
        position: relative;
    }
    .mr-ku-compact-card:hover {
        background: #FFFDF7;
        border-color: #D97706;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.1);
    }
    .mr-ku-compact-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        border: 1px solid rgba(217, 119, 6, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 1px 3px rgba(180, 83, 9, 0.06);
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .mr-ku-compact-card:hover .mr-ku-compact-icon {
        transform: scale(1.08);
        background: linear-gradient(135deg, #FDE68A, #FCD34D);
    }
    .mr-ku-compact-info {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-width: 0;
        gap: 2px;
        text-align: left;
    }
    .mr-ku-compact-title {
        font-size: 13px;
        font-weight: 800;
        color: #78350F;
        line-height: 1.3;
        transition: color 0.15s ease;
    }
    .mr-ku-compact-card:hover .mr-ku-compact-title {
        color: #B45309;
    }
    .mr-ku-compact-desc {
        font-size: 11px;
        font-weight: 600;
        color: #92400E;
        opacity: 0.82;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mr-ku-compact-arrow {
        width: 24px;
        height: 24px;
        min-width: 24px;
        border-radius: 50%;
        background: #FEF3C7;
        color: #B45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        transition: all 0.2s ease;
        margin-left: auto;
    }
    .mr-ku-compact-card:hover .mr-ku-compact-arrow {
        background: #B45309;
        color: #FFFFFF;
        transform: translateX(3px);
    }

    /* Forms */
    .mr-ku-form-container {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.03);
    }
    @media (max-width: 600px) {
        .mr-ku-form-container {
            padding: 14px;
        }
    }
    .mr-ku-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 600px) {
        .mr-ku-form-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
    .mr-ku-field {
        display: flex;
        flex-direction: column;
    }
    .mr-ku-field.full-width {
        grid-column: 1 / -1;
    }
    .mr-ku-field label {
        font-size: 11.5px;
        font-weight: 700;
        color: #78350F;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 5px;
    }
    .mr-ku-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #FDE68A;
        border-radius: 8px;
        font-size: 13.5px;
        background: #FFFDF7;
        color: #1E293B;
        box-sizing: border-box;
        height: 42px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .mr-ku-input:focus {
        outline: none;
        border-color: #D97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
        background: #FFFFFF;
    }
    .mr-ku-section-title {
        grid-column: 1 / -1;
        background: #FEF3C7;
        color: #78350F;
        border: 1px solid #FDE68A;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 800;
        text-align: center;
        margin-top: 6px;
        letter-spacing: 0.3px;
    }
    .mr-ku-submit {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #D97706, #B45309);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        font-weight: 800;
        font-size: 15px;
        margin-top: 16px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.2);
        transition: all 0.2s ease;
    }
    .mr-ku-submit:hover {
        background: linear-gradient(135deg, #B45309, #92400E);
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.3);
    }

    /* Result Layout & Top Bar */
    .mr-ku-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
        border-bottom: 1px dashed #FCD34D;
        padding-bottom: 12px;
    }
    .mr-ku-back-link {
        color: #B45309 !important;
        font-weight: 700;
        font-size: 12.5px;
        text-decoration: none !important;
    }
    .mr-ku-back-link:hover { text-decoration: underline !important; }
    .mr-ku-lang-box {
        display: inline-flex;
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 6px;
        padding: 2px;
    }
    .mr-ku-lang-btn {
        border: none;
        background: transparent;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #78350F;
        border-radius: 4px;
        text-decoration: none !important;
    }
    .mr-ku-lang-btn.active {
        background: #B45309;
        color: #FFFFFF !important;
    }

    /* Score Hero Block */
    .mr-ku-score-hero {
        background: linear-gradient(135deg, #B45309, #D97706);
        border-radius: 12px;
        padding: 22px 18px;
        text-align: center;
        color: #FFFFFF;
        margin-bottom: 18px;
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.15);
        position: relative;
        overflow: hidden;
    }
    .mr-ku-score-hero-val {
        font-size: 48px;
        font-weight: 900;
        line-height: 1;
        margin: 6px 0;
    }
    .mr-ku-score-hero-max {
        font-size: 20px;
        opacity: 0.85;
    }
    .mr-ku-score-hero-label {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.95;
    }

    /* Callout Box */
    .mr-ku-callout {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-left: 4px solid #F59E0B;
        border-radius: 8px;
        padding: 12px 16px;
        color: #78350F;
        font-size: 13.5px;
        line-height: 1.5;
        margin-bottom: 18px;
    }

    /* Badges */
    .mr-ku-badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }
    .mr-ku-badge-green { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
    .mr-ku-badge-red { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
    .mr-ku-badge-gold { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

    /* Factors Grid (Ashtakoot) */
    .mr-ku-factors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }
    .mr-ku-factor-card {
        background: #FFFFFF;
        border-radius: 10px;
        border: 1px solid #FDE68A;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.02);
        display: flex;
        flex-direction: column;
    }
    .mr-ku-factor-head {
        background: #FEF3C7;
        padding: 8px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #FDE68A;
    }
    .mr-ku-factor-title {
        color: #78350F;
        font-weight: 800;
        font-size: 12.5px;
        text-transform: uppercase;
    }
    .mr-ku-factor-split {
        display: flex;
        border-bottom: 1px solid #FEF3C7;
    }
    .mr-ku-split-box {
        flex: 1;
        text-align: center;
        padding: 6px 8px;
        background: #FFFDF7;
    }
    .mr-ku-split-box:first-child {
        border-right: 1px solid #FEF3C7;
    }
    .mr-ku-split-label {
        font-size: 10px;
        color: #B45309;
        text-transform: uppercase;
        font-weight: 800;
        margin-bottom: 2px;
    }
    .mr-ku-split-val {
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
    }
    .mr-ku-factor-desc {
        padding: 8px 12px;
        font-size: 11.5px;
        color: #64748B;
        line-height: 1.4;
        background: #FFFFFF;
        flex-grow: 1;
    }

    /* Dosha Grid */
    .mr-ku-dosha-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    .mr-ku-dosha-card {
        background: #FFFFFF;
        border-radius: 10px;
        border: 1px solid #FDE68A;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.02);
        display: flex;
        flex-direction: column;
    }
    .mr-ku-dosha-head {
        background: #FEF3C7;
        color: #78350F;
        font-weight: 800;
        font-size: 12.5px;
        padding: 8px 12px;
        border-bottom: 1px solid #FDE68A;
    }
    .mr-ku-dosha-body {
        padding: 10px 12px;
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.4;
        flex-grow: 1;
    }

    /* Responsive Chart Container */
    .mr-ku-chart-container {
        text-align: center;
        background: #FFFFFF;
        padding: 10px;
        border-radius: 12px;
        margin: 15px auto;
        max-width: 480px;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #FDE68A;
        box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
        overflow: hidden;
    }
    .mr-ku-chart-container svg {
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        display: block;
        margin: 0 auto;
    }

    /* House Predictions Accordion */
    .mr-ku-house-card {
        background: #FFFFFF;
        border-radius: 10px;
        border: 1px solid #FDE68A;
        overflow: hidden;
        margin-bottom: 8px;
        box-shadow: 0 1px 3px rgba(180, 83, 9, 0.02);
        transition: all 0.2s ease;
    }
    .mr-ku-house-card.is-open {
        border-color: #D97706;
        box-shadow: 0 3px 10px rgba(180, 83, 9, 0.07);
    }
    .mr-ku-house-head {
        background: #FEF3C7;
        padding: 9px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease;
        gap: 8px;
    }
    .mr-ku-house-head:hover {
        background: #FDE68A;
    }
    .mr-ku-house-head-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .mr-ku-house-head-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .mr-ku-house-title {
        color: #78350F;
        font-weight: 800;
        font-size: 13.5px;
        margin: 0;
    }
    .mr-ku-house-badge {
        background: #FFFFFF;
        color: #B45309;
        border: 1px solid #FDE68A;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.3;
    }
    .mr-ku-house-arrow {
        font-size: 10px;
        color: #B45309;
        transition: transform 0.25s ease;
        display: inline-block;
    }
    .mr-ku-house-card.is-open .mr-ku-house-arrow {
        transform: rotate(180deg);
    }
    .mr-ku-house-body {
        display: none;
        border-top: 1px solid #FDE68A;
    }
    .mr-ku-house-card.is-open .mr-ku-house-body {
        display: block;
        animation: mrKuFadeDown 0.2s ease-out;
    }
    @keyframes mrKuFadeDown {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .mr-ku-house-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 8px 12px;
        background: #FFFDF7;
        border-bottom: 1px solid #FEF3C7;
    }
    .mr-ku-spec-item {
        flex: 1 1 28%;
        min-width: 75px;
    }
    .mr-ku-spec-k {
        font-size: 10px;
        color: #B45309;
        text-transform: uppercase;
        font-weight: 800;
    }
    .mr-ku-spec-v {
        font-size: 12px;
        font-weight: 700;
        color: #1E293B;
    }
    .mr-ku-house-pred {
        padding: 10px 12px;
        font-size: 13px;
        color: #334155;
        line-height: 1.5;
    }

    /* Planetary Positions */
    .mr-ku-planets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    .mr-ku-planet-card {
        background: #FFFFFF;
        border-radius: 10px;
        border: 1px solid #FDE68A;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.02);
    }
    .mr-ku-planet-head {
        background: #FEF3C7;
        color: #78350F;
        padding: 8px 12px;
        font-weight: 800;
        font-size: 13px;
        text-align: center;
        border-bottom: 1px solid #FDE68A;
    }
    .mr-ku-planet-body {
        display: flex;
        justify-content: space-between;
        padding: 10px 8px;
        text-align: center;
    }
    .mr-ku-planet-col {
        flex: 1;
    }
    .mr-ku-planet-col:nth-child(2) {
        border-left: 1px solid #FEF3C7;
        border-right: 1px solid #FEF3C7;
    }
    .mr-ku-planet-k {
        font-size: 10px;
        color: #B45309;
        text-transform: uppercase;
        font-weight: 800;
    }
    .mr-ku-planet-v {
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
        margin-top: 2px;
    }

    /* Astrological Profiles */
    .mr-ku-profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .mr-ku-profile-card {
        background: #FFFFFF;
        border-radius: 12px;
        border: 1px solid #FDE68A;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.03);
    }
    .mr-ku-profile-head {
        background: #FEF3C7;
        color: #78350F;
        padding: 10px 14px;
        font-weight: 800;
        font-size: 13.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #FDE68A;
    }
    .mr-ku-profile-inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 12px 14px;
    }
    .mr-ku-profile-item-k {
        font-size: 10px;
        color: #B45309;
        text-transform: uppercase;
        font-weight: 800;
    }
    .mr-ku-profile-item-v {
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
        text-transform: capitalize;
    }

    /* Section Subheadings */
    .mr-ku-subheading {
        color: #B45309;
        font-size: 18px;
        font-weight: 800;
        text-align: center;
        margin: 28px 0 16px 0;
        border-bottom: 1px dashed #FCD34D;
        padding-bottom: 8px;
    }

    /* FAQs */
    .mr-ku-faq-wrap {
        margin-top: 30px;
        padding-top: 18px;
        border-top: 1px dashed #FCD34D;
    }
    .mr-ku-faq-title {
        text-align: center;
        color: #B45309;
        font-size: 18px;
        font-weight: 800;
        margin: 0 0 14px 0;
    }
    .mr-ku-faq-item {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 10px;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .mr-ku-faq-q {
        background: #FEF3C7;
        padding: 10px 14px;
        font-weight: 800;
        color: #78350F;
        font-size: 13.5px;
        border-bottom: 1px solid #FDE68A;
    }
    .mr-ku-faq-a {
        padding: 12px 14px;
        font-size: 13px;
        color: #334155;
        line-height: 1.6;
    }
    .mr-ku-faq-a.hi {
        border-top: 1px dashed #FDE68A;
        background: #FFFDF7;
        color: #78350F;
    }
    .mr-ku-lang-badge {
        display: inline-block;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 700;
        border-radius: 4px;
        margin-right: 6px;
        vertical-align: middle;
    }
    .mr-ku-lang-en { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .mr-ku-lang-hi { background: #FFEDD5; color: #C2410C; border: 1px solid #FDBA74; }

    /* Cross-Sell Recommended Master Reports (1 row desktop, horizontal row cards on mobile) */
    .mr-ku-crosssell-wrap {
        margin: 24px 0 16px 0;
        padding: 16px;
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
    }
    .mr-ku-crosssell-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        flex-wrap: wrap;
        gap: 6px;
    }
    .mr-ku-crosssell-title {
        font-size: 15px;
        font-weight: 900;
        color: #78350F;
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
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #FDE68A;
    }
    .mr-ku-crosssell-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .mr-ku-crosssell-card {
        background: #FFFDF7;
        border: 1px solid #FDE68A;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        text-decoration: none !important;
        color: inherit;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 1px 3px rgba(180, 83, 9, 0.02);
    }
    .mr-ku-crosssell-card:hover {
        transform: translateY(-2px);
        border-color: #D97706;
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.12);
    }
    .mr-ku-crosssell-img-box {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        background: #FEF3C7;
        overflow: hidden;
    }
    .mr-ku-crosssell-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.25s ease;
    }
    .mr-ku-crosssell-card:hover .mr-ku-crosssell-img {
        transform: scale(1.03);
    }
    .mr-ku-crosssell-content {
        padding: 10px 10px 12px 10px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }
    .mr-ku-crosssell-tags {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }
    .mr-ku-tag-discount {
        background: #FFB300;
        color: #FFFFFF;
        font-size: 10px;
        font-weight: 900;
        padding: 0px 3px;
        border-radius: 4px;
        letter-spacing: 0.2px;
        display: inline-block;
    }
    .mr-ku-tag-pages {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
        font-size: 10px;
        font-weight: 900;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }
    .mr-ku-crosssell-name {
        font-size: 12.5px;
        font-weight: 700;
        color: #78350F;
        line-height: 1.4;
        margin-bottom: 8px;
        word-break: normal;
        overflow-wrap: break-word;
    }
    .mr-ku-crosssell-bottom {
        margin-top: auto;
    }
    .mr-ku-crosssell-price-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 8px;
    }
    .mr-ku-crosssell-price {
        font-size: 15px;
        font-weight: 800;
        color: #B45309;
    }
    .mr-ku-crosssell-mrp {
        font-size: 11.5px;
        color: #94A3B8;
        text-decoration: line-through;
    }
    .mr-ku-crosssell-btn {
        background: linear-gradient(135deg, #D97706, #B45309);
        color: #FFFFFF !important;
        text-align: center;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        display: block;
        transition: background 0.15s ease;
    }
    .mr-ku-crosssell-btn:hover {
        background: linear-gradient(135deg, #B45309, #92400E);
    }

    /* Mobile: Horizontal format stacked one below the other */
    @media (max-width: 680px) {
        .mr-ku-crosssell-wrap {
            padding: 12px;
        }
        .mr-ku-crosssell-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .mr-ku-crosssell-card {
            flex-direction: row;
            align-items: center;
        }
        .mr-ku-crosssell-img-box {
            width: 105px;
            min-width: 105px;
            max-width: 105px;
            height: 105px;
            padding: 8px;
            box-sizing: border-box;
            background: transparent;
        }
        .mr-ku-crosssell-img {
            border-radius: 8px;
        }
        .mr-ku-crosssell-content {
            padding: 10px 12px 10px 2px;
            gap: 4px;
        }
        .mr-ku-crosssell-name {
            font-size: 13.5px;
            margin-bottom: 4px;
        }
        .mr-ku-crosssell-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 4px;
        }
        .mr-ku-crosssell-price-row {
            margin-bottom: 0;
        }
        .mr-ku-crosssell-btn {
            padding: 6px 12px;
            font-size: 11.5px;
            white-space: nowrap;
        }
    }
    </style>
    <?php
}
}

/* ====================================================
4. THE UNIFIED ENGINE SHORTCODE: [kundali_calc_engine]
==================================================== */
add_shortcode(''kundali_calc_engine'', function () {
    $ku_type = isset($_GET[''ku_type'']) ? sanitize_text_field($_GET[''ku_type'']) : '''';
    $ku_slug = isset($_GET[''ku_slug'']) ? sanitize_text_field($_GET[''ku_slug'']) : '''';
    $ku_lang = isset($_GET[''ku_lang'']) ? sanitize_text_field($_GET[''ku_lang'']) : ''en'';

    ob_start();
    mr_ku_print_styles();

    echo ''<div class="mr-ku-wrap">'';

    if (!$ku_type) {
        kundali_display_landing_page();
    } elseif ($ku_slug) {
        kundali_display_result($ku_type, $ku_slug, $ku_lang);
    } else {
        kundali_display_form($ku_type);
    }

    // Append Detailed FAQs
    kundali_display_faq($ku_type);

    echo ''</div>''; // End .mr-ku-wrap

    return ob_get_clean();
});

/* ====================================================
5. LANDING PAGE
==================================================== */
if (!function_exists(''kundali_display_landing_page'')) {
function kundali_display_landing_page($is_main_page = true) {
    $tools = [
        [''type'' => ''create-chart'', ''icon'' => ''📊'', ''title'' => ''Create Birth Chart'', ''desc'' => ''Generate your free precise Vedic birth chart (Lagna Chart / D1).'', ''btn'' => ''Get Chart'', ''subtitle'' => ''Lagna (D1) Vedic Chart''],
        [''type'' => ''kundali-details-report'', ''icon'' => ''🔮'', ''title'' => ''Kundali Predictions'', ''desc'' => ''Read detailed personal characteristics and life predictions based on your houses.'', ''btn'' => ''Read Predictions'', ''subtitle'' => ''12 Houses Life Forecast''],
        [''type'' => ''house-explain'', ''icon'' => ''🪐'', ''title'' => ''Planetary Positions'', ''desc'' => ''Understand the planetary placements, signs, and exact degrees in your Kundali.'', ''btn'' => ''View Planets'', ''subtitle'' => ''Signs, Houses & Degrees''],
        [''type'' => ''kundali-matching-short'', ''icon'' => ''💍'', ''title'' => ''Ashtakoot Matching'', ''desc'' => ''Check 36-point Guna Milan matching score for marriage compatibility.'', ''btn'' => ''Match Now'', ''subtitle'' => ''36 Guna Milan Score''],
        [''type'' => ''kundali-matching-detailed'', ''icon'' => ''📜'', ''title'' => ''Detailed Matching'', ''desc'' => ''Comprehensive couple matching including individual planetary astro details.'', ''btn'' => ''Detailed Match'', ''subtitle'' => ''Planetary Compatibility''],
        [''type'' => ''kundali-lagan-matching'', ''icon'' => ''⚡'', ''title'' => ''Aggregate Matching'', ''desc'' => ''Check compatibility including Mangal Dosh, Pitra Dosh, and Kaalsarp.'', ''btn'' => ''Check Dosh'', ''subtitle'' => ''Manglik & Dosha Audit'']
    ];

    if ($is_main_page) {
        ?>
        <div class="mr-ku-header">
            <h1>🔮 Free Vedic Kundali &amp; Horoscope Tools</h1>
            <p>Select an authentic Vedic astrology calculator or matching report below</p>
        </div>
        <div class="mr-ku-grid">
            <?php foreach ($tools as $t): ?>
                <a href="<?php echo esc_url(add_query_arg(''ku_type'', $t[''type''], get_permalink())); ?>" class="mr-ku-card">
                    <div>
                        <div class="mr-ku-card-icon"><?php echo $t[''icon'']; ?></div>
                        <h3><?php echo esc_html($t[''title'']); ?></h3>
                        <p><?php echo esc_html($t[''desc'']); ?></p>
                    </div>
                    <div>
                        <span class="mr-ku-card-btn"><?php echo esc_html($t[''btn'']); ?> &rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    } else {
        ?>
        <div class="mr-ku-compact-grid">
            <?php foreach ($tools as $t): ?>
                <a href="<?php echo esc_url(add_query_arg(''ku_type'', $t[''type''], get_permalink())); ?>" class="mr-ku-compact-card">
                    <div class="mr-ku-compact-icon"><?php echo $t[''icon'']; ?></div>
                    <div class="mr-ku-compact-info">
                        <span class="mr-ku-compact-title"><?php echo esc_html($t[''title'']); ?></span>
                        <span class="mr-ku-compact-desc"><?php echo esc_html($t[''subtitle''] ?? ''''); ?></span>
                    </div>
                    <div class="mr-ku-compact-arrow">&rarr;</div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
}

/* ====================================================
5B. RECOMMENDED MASTER REPORTS CROSS-SELL
- 1 Row on Desktop (repeat(4, 1fr))
- 2x2 Grid on Mobile (repeat(2, 1fr))
==================================================== */
if (!function_exists(''kundali_display_cross_sell'')) {
function kundali_display_cross_sell() {
    if (function_exists(''mr_ku_print_styles'')) {
        mr_ku_print_styles();
    }
    $products = [
        [
            ''title''     => ''Master Janma Kundali & Harmonics Report'',
            ''url''       => ''https://merarashifal.com/reports/janam-kundali-master-report/'',
            ''local_url'' => home_url(''/reports/janam-kundali-master-report/''),
            ''img_id''    => 7398,
            ''img_file''  => ''/wp-content/uploads/2026/11/janma-kundali-harmonics-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹999'',
            ''discount''  => ''90% OFF'',
            ''pages''     => ''26 Pages''
        ],
        [
            ''title''     => ''Master Kundali Matching (Gun Milan) Report'',
            ''url''       => ''https://merarashifal.com/reports/kundali-and-gun-matching-report/'',
            ''local_url'' => home_url(''/reports/kundali-and-gun-matching-report/''),
            ''img_id''    => 7394,
            ''img_file''  => ''/wp-content/uploads/2026/11/kundali-matching-gun-milan-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹1,499'',
            ''discount''  => ''93% OFF'',
            ''pages''     => ''24 Pages''
        ],
        [
            ''title''     => ''Master Numerology & Vedic Integration Report'',
            ''url''       => ''https://merarashifal.com/reports/numerology-report/'',
            ''local_url'' => home_url(''/reports/numerology-report/''),
            ''img_id''    => 7393,
            ''img_file''  => ''/wp-content/uploads/2026/11/numerology-vedic-integration-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹599'',
            ''discount''  => ''83% OFF'',
            ''pages''     => ''22 Pages''
        ],
        [
            ''title''     => ''Master Hast Rekha & Palmistry Analysis Report'',
            ''url''       => ''https://merarashifal.com/reports/palm-reading-report/'',
            ''local_url'' => home_url(''/reports/palm-reading-report/''),
            ''img_id''    => 7399,
            ''img_file''  => ''/wp-content/uploads/2026/11/Master-Palmistry-Analysis-Report.webp'',
            ''sale_price''=> ''₹299'',
            ''mrp''       => ''₹1,499'',
            ''discount''  => ''80% OFF'',
            ''pages''     => ''26 Pages''
        ]
    ];

    $is_local = (strpos($_SERVER[''HTTP_HOST''] ?? '''', ''localhost'') !== false);
    ?>
    <div class="mr-ku-crosssell-wrap">
        <div class="mr-ku-crosssell-header">
            <h3 class="mr-ku-crosssell-title">⚡ Recommended Master Reports for You</h3>
            <span class="mr-ku-crosssell-badge">Personalized Vedic Readings</span>
        </div>
        <div class="mr-ku-crosssell-grid">
            <?php foreach ($products as $p):
                $target_url = $is_local ? $p[''local_url''] : $p[''url''];
                $img_src = '''';
                if (!empty($p[''img_id''])) {
                    $img_src = wp_get_attachment_image_url($p[''img_id''], ''medium_large'') ?: wp_get_attachment_url($p[''img_id'']);
                }
                if (!$img_src) {
                    $img_src = home_url($p[''img_file'']);
                }
            ?>
                <a href="<?php echo esc_url($target_url); ?>" class="mr-ku-crosssell-card" target="_blank" rel="noopener">
                    <div class="mr-ku-crosssell-img-box">
                        <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($p[''title'']); ?>" class="mr-ku-crosssell-img" loading="lazy">
                    </div>
                    <div class="mr-ku-crosssell-content">
                        <div>
                            <div class="mr-ku-crosssell-tags">
                                <span class="mr-ku-tag-discount"><?php echo esc_html($p[''discount'']); ?></span>
                                <span class="mr-ku-tag-pages"><?php echo esc_html($p[''pages'']); ?></span>
                            </div>
                            <div class="mr-ku-crosssell-name"><?php echo esc_html($p[''title'']); ?></div>
                        </div>
                        <div class="mr-ku-crosssell-bottom">
                            <div class="mr-ku-crosssell-price-row">
                                <span class="mr-ku-crosssell-price"><?php echo esc_html($p[''sale_price'']); ?></span>
                                <span class="mr-ku-crosssell-mrp"><?php echo esc_html($p[''mrp'']); ?></span>
                            </div>
                            <div class="mr-ku-crosssell-btn">Get Report &rarr;</div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
}

// Standalone cross-sell shortcode
add_shortcode(''mr_report_cross_sell'', function () {
    ob_start();
    mr_ku_print_styles();
    kundali_display_cross_sell();
    return ob_get_clean();
});

/* ====================================================
6. FORMS
==================================================== */
if (!function_exists(''kundali_display_form'')) {
function kundali_display_form($type) {
    $google_key = defined(''GOOGLE_MAPS_API_KEY'') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined(''GOOGLE_API_KEY'') ? GOOGLE_API_KEY : '''');
    $seo = kundali_get_seo_data();
    $is_couple = in_array($type, [''kundali-matching-short'', ''kundali-matching-detailed'', ''kundali-lagan-matching'']);
    $is_nakshatra = ($type === ''kundali-nakshatra-match'');
    
    $tool_titles = [
        ''kundali-matching-short'' => ''💍 Ashtakoot Matching (36-Point Guna Milan)'',
        ''kundali-matching-detailed'' => ''📜 Detailed Kundali Matching Report'',
        ''kundali-lagan-matching'' => ''⚡ Aggregate Matching & Manglik Dosh Check'',
        ''kundali-nakshatra-match'' => ''✨ Nakshatra Compatibility Match'',
        ''create-chart'' => ''📊 Free Vedic Birth Chart (Lagna Chart)'',
        ''house-explain'' => ''🪐 Planetary Positions & Degrees Analysis'',
        ''kundali-details-report'' => ''🔮 Personalised Kundali Predictions & Life Report''
    ];
    $display_title = $tool_titles[$type] ?? $seo[''title''];
    ?>

    <div class="mr-ku-topbar">
        <a href="<?php echo esc_url(remove_query_arg([''ku_type'', ''ku_slug'', ''ku_lang''])); ?>" class="mr-ku-back-link">
            &larr; Back to All Astrology Tools
        </a>
    </div>

    <div class="mr-ku-form-container">
        <div class="mr-ku-header" style="border-bottom:none; margin-bottom:14px; padding-bottom:0;">
            <h1><?php echo esc_html($display_title); ?></h1>
            <p>Enter birth details below for precise Vedic calculations</p>
        </div>

        <form method="POST" action="">
            <div class="mr-ku-form-grid">
                <?php if ($is_nakshatra): ?>
                    <div class="mr-ku-field">
                        <label>Boy''s Nakshatra (Star)</label>
                        <select name="boy_star" class="mr-ku-input" required>
                            <?php for($i=1; $i<=27; $i++) echo "<option value=''$i''>Nakshatra $i</option>"; ?>
                        </select>
                    </div>
                    <div class="mr-ku-field">
                        <label>Girl''s Nakshatra (Star)</label>
                        <select name="girl_star" class="mr-ku-input" required>
                            <?php for($i=1; $i<=27; $i++) echo "<option value=''$i''>Nakshatra $i</option>"; ?>
                        </select>
                    </div>
                <?php elseif ($is_couple): ?>
                    <!-- BOY DETAILS -->
                    <div class="mr-ku-section-title">👦 Boy''s Birth Details</div>
                    <div class="mr-ku-field">
                        <label>Date of Birth</label>
                        <input type="date" name="b_dob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field">
                        <label>Time of Birth</label>
                        <input type="time" name="b_tob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field full-width" style="position:relative;">
                        <label>Place of Birth</label>
                        <input type="text" id="b_place" class="mr-ku-input" placeholder="Search birth city or district..." required>
                    </div>
                    <input type="hidden" name="b_lat" id="b_lat">
                    <input type="hidden" name="b_lon" id="b_lon">
                    <input type="hidden" name="b_tz" id="b_tz" value="5.5">

                    <!-- GIRL DETAILS -->
                    <div class="mr-ku-section-title">👧 Girl''s Birth Details</div>
                    <div class="mr-ku-field">
                        <label>Date of Birth</label>
                        <input type="date" name="g_dob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field">
                        <label>Time of Birth</label>
                        <input type="time" name="g_tob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field full-width" style="position:relative;">
                        <label>Place of Birth</label>
                        <input type="text" id="g_place" class="mr-ku-input" placeholder="Search birth city or district..." required>
                    </div>
                    <input type="hidden" name="g_lat" id="g_lat">
                    <input type="hidden" name="g_lon" id="g_lon">
                    <input type="hidden" name="g_tz" id="g_tz" value="5.5">

                <?php else: ?>
                    <!-- SINGLE DETAILS -->
                    <div class="mr-ku-field">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field">
                        <label>Time of Birth</label>
                        <input type="time" name="tob" class="mr-ku-input" required>
                    </div>
                    <div class="mr-ku-field full-width" style="position:relative;">
                        <label>Place of Birth</label>
                        <input type="text" id="place" class="mr-ku-input" placeholder="Search birth city or district..." required>
                    </div>
                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lon" id="lon">
                    <input type="hidden" name="tz" id="tz" value="5.5">
                <?php endif; ?>

                <div class="mr-ku-field full-width">
                    <label>Report Language</label>
                    <select name="lang" class="mr-ku-input">
                        <option value="en" selected>English</option>
                        <option value="hi">Hindi (हिन्दी)</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="ku_type" value="<?php echo esc_attr($type); ?>">
            <input type="hidden" name="ku_action" value="submit_form">
            <input type="hidden" name="ku_current_url" value="<?php echo esc_url((isset($_SERVER[''HTTPS'']) && $_SERVER[''HTTPS''] === ''on'' ? ''https'' : ''http'') . ''://'' . $_SERVER[''HTTP_HOST''] . explode(''?'', $_SERVER[''REQUEST_URI''])[0]); ?>">
            <!-- VAPT Security: Honeypot to block automated bot submissions -->
            <input type="text" name="ku_honeypot" value="" style="display:none !important;" tabindex="-1" autocomplete="off">
            <?php wp_nonce_field(''ku_submit_nonce'', ''ku_nonce''); ?>
            <button type="submit" class="mr-ku-submit">⚡ Generate Report</button>
        </form>
    </div>

    <?php if (!$is_nakshatra): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($google_key); ?>&libraries=places"></script>
    <script>
        document.addEventListener(''DOMContentLoaded'', function () {
            function initAutocomplete(inputId, latId, lonId, tzId) {
                const input = document.getElementById(inputId);
                if (!input) return;

                let wrap = input.parentElement;
                if (!wrap.classList.contains(''mr-city-input-wrap'')) {
                    wrap.style.position = ''relative'';
                }
                let loader = wrap.querySelector(''.mr-city-loader'');
                if (!loader) {
                    loader = document.createElement(''span'');
                    loader.className = ''mr-city-loader'';
                    loader.innerHTML = ''<span class="mr-city-spinner-ring"></span>'';
                    input.insertAdjacentElement(''afterend'', loader);
                }

                function showLoader() {
                    loader.classList.add(''is-active'');
                    loader.style.display = ''inline-flex'';
                }

                function hideLoader() {
                    loader.classList.remove(''is-active'');
                    loader.style.display = ''none'';
                }

                let autocomplete;
                function ensureAC() {
                    if (autocomplete || typeof google === ''undefined'' || !google.maps || !google.maps.places) return;
                    try {
                        autocomplete = new google.maps.places.Autocomplete(input, { types: [''(cities)''] });
                        autocomplete.addListener(''place_changed'', function () {
                            const place = autocomplete.getPlace();
                            if (!place || !place.geometry) return;
                            document.getElementById(latId).value = place.geometry.location.lat();
                            document.getElementById(lonId).value = place.geometry.location.lng();
                            if (typeof place.utc_offset_minutes !== ''undefined'') {
                                document.getElementById(tzId).value = place.utc_offset_minutes / 60;
                            }
                            hideLoader();
                        });
                    } catch(e) { console.warn(e); }
                }

                ensureAC();
                input.addEventListener(''focus'', ensureAC);

                input.addEventListener(''input'', function () {
                    ensureAC();
                    if (this.value.trim().length >= 2) {
                        showLoader();
                    } else {
                        hideLoader();
                    }
                });

                document.addEventListener(''click'', function(e) {
                    if (e.target && e.target.closest(''.pac-item'')) {
                        hideLoader();
                    }
                });
            }

            <?php if ($is_couple): ?>
                initAutocomplete(''b_place'', ''b_lat'', ''b_lon'', ''b_tz'');
                initAutocomplete(''g_place'', ''g_lat'', ''g_lon'', ''g_tz'');
            <?php else: ?>
                initAutocomplete(''place'', ''lat'', ''lon'', ''tz'');
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
    <?php
}
}

/* ====================================================
7. BACKEND HANDLER (FORM SUBMISSION)
==================================================== */
add_action(''init'', function () {
    if (isset($_POST[''ku_action'']) && $_POST[''ku_action''] === ''submit_form'') {
        // VAPT: CSRF Protection
        if (!isset($_POST[''ku_nonce'']) || !wp_verify_nonce($_POST[''ku_nonce''], ''ku_submit_nonce'')) {
            wp_die(''Security verification failed. Please try again.'', ''Security Error'', [''response'' => 403]);
        }

        // VAPT: Honeypot Anti-Spam Check
        if (!empty($_POST[''ku_honeypot''])) {
            wp_die(''Invalid submission detected.'', ''Security Error'', [''response'' => 403]);
        }

        $type = sanitize_text_field($_POST[''ku_type'']);
        $lang = sanitize_text_field($_POST[''lang'']);
        $slug = '''';
        $data = [];

        if ($type === ''kundali-nakshatra-match'') {
            $data = [
                ''boy_star'' => intval($_POST[''boy_star'']),
                ''girl_star'' => intval($_POST[''girl_star''])
            ];
            $slug = "star-{$data[''boy_star'']}-{$data[''girl_star'']}";
        } elseif (in_array($type, [''kundali-matching-short'', ''kundali-matching-detailed'', ''kundali-lagan-matching''])) {
            // VAPT: Strict Type Casting & Sanitization for Geolocation Data
            $data = [
                ''boy_dob'' => date(''d/m/Y'', strtotime(sanitize_text_field($_POST[''b_dob'']))),
                ''boy_tob'' => sanitize_text_field($_POST[''b_tob'']),
                ''boy_lat'' => floatval($_POST[''b_lat'']),
                ''boy_lon'' => floatval($_POST[''b_lon'']),
                ''boy_tz'' => floatval($_POST[''b_tz'']),
                ''girl_dob'' => date(''d/m/Y'', strtotime(sanitize_text_field($_POST[''g_dob'']))),
                ''girl_tob'' => sanitize_text_field($_POST[''g_tob'']),
                ''girl_lat'' => floatval($_POST[''g_lat'']),
                ''girl_lon'' => floatval($_POST[''g_lon'']),
                ''girl_tz'' => floatval($_POST[''g_tz''])
            ];
            $slug = date(''dmY'', strtotime(sanitize_text_field($_POST[''b_dob'']))) . ''-'' . date(''dmY'', strtotime(sanitize_text_field($_POST[''g_dob''])));
        } else {
            // VAPT: Strict Type Casting for Individual
            $data = [
                ''dob'' => date(''d/m/Y'', strtotime(sanitize_text_field($_POST[''dob'']))),
                ''tob'' => sanitize_text_field($_POST[''tob'']),
                ''lat'' => floatval($_POST[''lat'']),
                ''lon'' => floatval($_POST[''lon'']),
                ''tz'' => floatval($_POST[''tz''])
            ];
            $slug = date(''dmY'', strtotime(sanitize_text_field($_POST[''dob''])));
        }

        $base_url = !empty($_POST[''ku_current_url'']) ? esc_url_raw($_POST[''ku_current_url'']) : (wp_get_referer() ?: home_url());

        $redirect_url = add_query_arg([
            ''ku_type'' => $type,
            ''ku_slug'' => $slug,
            ''ku_lang'' => $lang
        ], $base_url);

        set_transient("kundali_{$type}_{$slug}", $data, 12 * HOUR_IN_SECONDS);
        wp_redirect($redirect_url);
        exit;
    }
});

/* ====================================================
8. RESULT DISPLAY & API LOGIC
==================================================== */
if (!function_exists(''kundali_display_result'')) {
function kundali_display_result($type, $slug, $lang) {
    $data = get_transient("kundali_{$type}_{$slug}");
    if (!$data) {
        $restart_url = esc_url(remove_query_arg([''ku_slug'', ''ku_lang'']));
        echo "<div style=''text-align:center; padding:40px 20px; background:#fff; border:1px solid #FDE68A; border-radius:10px;''>";
        echo "<p style=''margin:0 0 12px 0; font-weight:700; color:#B45309;''>Your calculation session has expired.</p>";
        echo "<a href=''" . $restart_url . "'' class=''mr-ku-card-btn''>&larr; Start New Calculation</a>";
        echo "</div>";
        return;
    }

    $endpoints = [
        ''kundali-matching-short'' => ''matching/ashtakoot'',
        ''kundali-matching-detailed'' => ''matching/ashtakoot-with-astro-details'',
        ''kundali-lagan-matching'' => ''matching/aggregate-match'',
        ''kundali-nakshatra-match'' => ''matching/nakshatra-match'',
        ''create-chart'' => ''horoscope/chart-image'',
        ''house-explain'' => ''horoscope/divisional-charts'',
        ''kundali-details-report'' => ''horoscope/personal-characteristics''
    ];

    $endpoint = $endpoints[$type] ?? '''';
    if (!$endpoint) return;

    $api_args = array_merge($data, [''api_key'' => AF_KUNDALI_API_KEY, ''lang'' => $lang]);

    // Extra args for specific endpoints
    if ($type === ''create-chart'') {
        $api_args[''div''] = ''D1'';
        $api_args[''color''] = ''%23b45309''; // Warm amber stroke color for chart
        $api_args[''style''] = ''north'';
    } elseif ($type === ''house-explain'') {
        $api_args[''div''] = ''D1'';
        $api_args[''response_type''] = ''planet_object'';
    }

    // Construct full URL
    $api_url = "https://api.vedicastroapi.com/v3-json/" . $endpoint;
    $api_url = add_query_arg($api_args, $api_url);

    // API Caching Logic
    $cache_key = "ku_api_res_" . md5($api_url);
    $body = get_transient($cache_key);

    if (!$body) {
        $resp = wp_remote_get($api_url, [''timeout'' => 45]);
        if (is_wp_error($resp)) {
            echo "<div style=''text-align:center; padding:30px; background:#fff; border:1px solid #FDE68A; border-radius:10px;''>";
            echo "<h3 style=''color:#B45309; margin:0 0 8px 0;''>Connection Notice</h3>";
            echo "<p style=''color:#64748B; margin:0;''>Unable to connect to Vedic ephemeris servers. Please try again shortly.</p>";
            echo "</div>";
            return;
        }
        $body = wp_remote_retrieve_body($resp);
        
        if ($type === ''create-chart'' && strpos($body, ''<svg'') !== false) {
            set_transient($cache_key, $body, 12 * HOUR_IN_SECONDS);
        } else {
            $check = json_decode($body, true);
            if (isset($check[''response''])) {
                set_transient($cache_key, $body, 12 * HOUR_IN_SECONDS);
            }
        }
    }

    // Tool Titles Mapping
    $tool_titles = [
        ''kundali-matching-short'' => ''💍 Ashtakoot Matching (36-Point Guna Milan)'',
        ''kundali-matching-detailed'' => ''📜 Detailed Kundali Matching Report'',
        ''kundali-lagan-matching'' => ''⚡ Aggregate Matching & Manglik Dosh'',
        ''kundali-nakshatra-match'' => ''✨ Nakshatra Compatibility Match'',
        ''create-chart'' => ''📊 Free Vedic Birth Chart (Lagna Chart)'',
        ''house-explain'' => ''🪐 Planetary Positions & House Analysis'',
        ''kundali-details-report'' => ''🔮 Personalised Kundali Predictions''
    ];
    $current_title = $tool_titles[$type] ?? ''Kundali Report'';

    // Top Bar: Back Link & Language Toggle
    $en_url = esc_url(add_query_arg(''ku_lang'', ''en''));
    $hi_url = esc_url(add_query_arg(''ku_lang'', ''hi''));
    ?>
    <div class="mr-ku-topbar">
        <a href="<?php echo esc_url(remove_query_arg([''ku_type'', ''ku_slug'', ''ku_lang''])); ?>" class="mr-ku-back-link">
            &larr; Back to All Astrology Tools
        </a>
        <div class="mr-ku-lang-box">
            <a href="<?php echo $en_url; ?>" class="mr-ku-lang-btn <?php echo ($lang === ''en'') ? ''active'' : ''''; ?>">EN</a>
            <a href="<?php echo $hi_url; ?>" class="mr-ku-lang-btn <?php echo ($lang === ''hi'') ? ''active'' : ''''; ?>">हिन्दी</a>
        </div>
    </div>

    <div class="mr-ku-header">
        <h1><?php echo esc_html($current_title); ?></h1>
        <p>Vedic astrological calculation results</p>
    </div>
    <?php

    // Render SVG for Create Chart
    if ($type === ''create-chart'') {
        if (strpos($body, ''<svg'') !== false) {
            // Ensure SVG is responsive with viewBox
            if (strpos($body, ''viewBox'') === false) {
                $body = preg_replace(''/<svg\\s+height="(\\d+)"\\s+width="(\\d+)"/i'', ''<svg viewBox="0 0 $2 $1" width="100%" height="auto" preserveAspectRatio="xMidYMid meet"'', $body);
                $body = preg_replace(''/<svg\\s+width="(\\d+)"\\s+height="(\\d+)"/i'', ''<svg viewBox="0 0 $1 $2" width="100%" height="auto" preserveAspectRatio="xMidYMid meet"'', $body);
            }
            // Ensure width is 100% and height auto
            $body = preg_replace(''/<svg([^>]*)width="\\d+"([^>]*)height="\\d+"/i'', ''<svg$1width="100%"$2height="auto"'', $body);
            $body = preg_replace(''/<svg([^>]*)height="\\d+"([^>]*)width="\\d+"/i'', ''<svg$1height="auto"$2width="100%"'', $body);

            echo "<div class=''mr-ku-chart-container''>";
            echo $body;
            echo "</div>";
            
            kundali_display_cross_sell();

            echo "<div class=''mr-ku-subheading''>✨ Explore Other Free Astrology Tools</div>";
            kundali_display_landing_page(false);
            return;
        }
    }

    $json = json_decode($body, true);
    $res = $json[''response''] ?? null;

    if (!$res) {
        echo "<div style=''text-align:center; padding:30px; background:#fff; border:1px solid #FDE68A; border-radius:10px;''>";
        echo "<h3 style=''color:#B45309; margin:0 0 8px 0;''>Data Notice</h3>";
        echo "<p style=''color:#64748B; margin:0;''>Could not retrieve planetary data for these coordinates. Please verify your birth details.</p>";
        echo "</div>";
        return;
    }

    // Bot summary response banner if present
    if (isset($res[''bot_response'']) && !in_array($type, [''kundali-matching-short'', ''kundali-matching-detailed'', ''kundali-nakshatra-match'', ''kundali-lagan-matching''])) {
        echo "<div class=''mr-ku-callout''>";
        echo "<strong>💡 Summary:</strong> " . esc_html($res[''bot_response'']);
        echo "</div>";
    }

    // 1. KUNDALI LAGAN MATCHING (AGGREGATE MATCH + DOSHA)
    if ($type === ''kundali-lagan-matching'') {
        // Scores Hero Strip
        $scores = [];
        if (isset($res[''score''])) $scores[''Overall Match''] = $res[''score''] . ''/100'';
        if (isset($res[''ashtakoot_score''])) $scores[''Ashtakoot Score''] = $res[''ashtakoot_score''];
        if (isset($res[''dashkoot_score''])) $scores[''Dashkoot Score''] = $res[''dashkoot_score''];

        if (!empty($scores)) {
            echo "<div style=''display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:18px;''>";
            foreach ($scores as $label => $val) {
                echo "<div class=''mr-ku-score-hero'' style=''padding:16px; margin-bottom:0;''>";
                echo "<div class=''mr-ku-score-hero-label''>" . esc_html($label) . "</div>";
                echo "<div class=''mr-ku-score-hero-val'' style=''font-size:36px; margin:4px 0;''>" . esc_html($val) . "</div>";
                echo "</div>";
            }
            echo "</div>";
        }

        // Conclusion / Bot Response
        if (!empty($res[''bot_response'']) || !empty($res[''extended_response''])) {
            echo "<div class=''mr-ku-callout''>";
            if (!empty($res[''bot_response''])) {
                echo "<div style=''font-weight:800; color:#B45309; margin-bottom:4px; font-size:14.5px;''>💡 " . esc_html($res[''bot_response'']) . "</div>";
            }
            if (!empty($res[''extended_response''])) {
                echo "<div style=''color:#475569; font-size:13px; line-height:1.5;''>" . esc_html($res[''extended_response'']) . "</div>";
            }
            echo "</div>";
        }

        // Dosha Grid
        echo "<div class=''mr-ku-subheading'' style=''margin-top:10px;''>⚡ Comprehensive Dosha Compatibility</div>";
        $doshas_config = [
            ''Mangal Dosh'' => [''key'' => ''mangaldosh'', ''points'' => ''mangaldosh_points''],
            ''Pitra Dosh'' => [''key'' => ''pitradosh'', ''points'' => ''pitradosh_points''],
            ''Kaalsarp Dosh'' => [''key'' => ''kaalsarpdosh'', ''points'' => ''kaalsarp_points''],
            ''Manglik (Saturn)'' => [''key'' => ''manglikdosh_saturn'', ''points'' => ''manglikdosh_saturn_points''],
            ''Manglik (Rahu/Ketu)'' => [''key'' => ''manglikdosh_rahuketu'', ''points'' => ''manglikdosh_rahuketu_points''],
            ''Rajju Dosh'' => [''key'' => ''rajjudosh'', ''points'' => ''rajjudosh_points''],
            ''Vedha Dosh'' => [''key'' => ''vedhadosh'', ''points'' => ''vedhadosh_points''],
        ];

        echo "<div class=''mr-ku-dosha-grid''>";
        foreach ($doshas_config as $label => $keys) {
            $val = $res[$keys[''key'']] ?? null;
            if ($val === null) continue;
            
            $points = $res[$keys[''points'']] ?? [];
            $boy_pt = isset($points[''boy'']) ? $points[''boy''] : ''-'';
            $girl_pt = isset($points[''girl'']) ? $points[''girl''] : ''-'';

            if (is_bool($boy_pt)) $boy_pt = $boy_pt ? ''Present'' : ''Absent'';
            if (is_bool($girl_pt)) $girl_pt = $girl_pt ? ''Present'' : ''Absent'';
            
            $is_warning = true;
            $sentence = "";
            if (is_bool($val)) {
                $is_warning = $val;
                $sentence = $val ? ''Dosh Present'' : ''No Dosh (Clean)'';
            } else {
                $sentence = ucfirst(strtolower((string)$val));
                $lower_val = strtolower($sentence);
                if (strpos($lower_val, ''not favorable'') !== false || strpos($lower_val, ''not favourable'') !== false) {
                    $is_warning = true;
                } elseif (strpos($lower_val, ''favorable'') !== false || strpos($lower_val, ''favourable'') !== false || strpos($lower_val, ''good for marriage'') !== false) {
                    $is_warning = false;
                } else {
                    $is_warning = !(strpos($lower_val, ''no '') !== false || strpos($lower_val, ''doesn\\''t'') !== false || strpos($lower_val, ''does not'') !== false || $lower_val === ''false'');
                }
            }

            $badge_class = $is_warning ? ''mr-ku-badge-red'' : ''mr-ku-badge-green'';
            $badge_text  = $is_warning ? ''Warning'' : ''Favorable'';

            echo "<div class=''mr-ku-dosha-card''>";
            echo "<div class=''mr-ku-dosha-head'' style=''display:flex; justify-content:space-between; align-items:center;''>";
            echo "<span>" . esc_html($label) . "</span>";
            echo "<span class=''mr-ku-badge {$badge_class}''>" . esc_html($badge_text) . "</span>";
            echo "</div>";
            
            if ($boy_pt !== ''-'' || $girl_pt !== ''-'') {
                echo "<div class=''mr-ku-factor-split''>";
                echo "<div class=''mr-ku-split-box''><div class=''mr-ku-split-label''>Boy</div><div class=''mr-ku-split-val''>" . esc_html($boy_pt) . "</div></div>";
                echo "<div class=''mr-ku-split-box''><div class=''mr-ku-split-label''>Girl</div><div class=''mr-ku-split-val''>" . esc_html($girl_pt) . "</div></div>";
                echo "</div>";
            }
            
            echo "<div class=''mr-ku-dosha-body'' style=''background:" . ($is_warning ? ''#FFFDFD'' : ''#F7FCF9'') . "; color:" . ($is_warning ? ''#991B1B'' : ''#166534'') . ";''>";
            echo esc_html($sentence);
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";

    // 2. KUNDALI DETAILS REPORT (HOUSE PREDICTIONS ACCORDION)
    } elseif ($type === ''kundali-details-report'') {
        echo "<div style=''display:flex; flex-direction:column; gap:8px;''>";
        foreach ($res as $house) {
            $h_num = esc_html($house[''current_house'']);
            $verb_loc = esc_html($house[''verbal_location'']);
            $pred = esc_html($house[''personalised_prediction'']);
            $zodiac = esc_html($house[''current_zodiac'']);
            $lord = esc_html($house[''lord_of_zodiac'']);
            $lord_str = esc_html($house[''lord_strength'']);
            $l_z_loc = esc_html($house[''lord_zodiac_location'']);
            $l_h_loc = esc_html($house[''lord_house_location'']);

            echo "<div class=''mr-ku-house-card''>";
            echo "<div class=''mr-ku-house-head'' onclick=''mrKuToggleHouse(this);'' role=''button'' tabindex=''0''>";
            echo "<div class=''mr-ku-house-head-left''>";
            echo "<h3 class=''mr-ku-house-title''>🏛️ House " . $h_num . "</h3>";
            if ($verb_loc) {
                echo "<span class=''mr-ku-house-badge''>" . $verb_loc . "</span>";
            }
            echo "</div>";
            echo "<div class=''mr-ku-house-head-right''>";
            echo "<span class=''mr-ku-house-arrow''>▼</span>";
            echo "</div>";
            echo "</div>";

            echo "<div class=''mr-ku-house-body''>";
            echo "<div class=''mr-ku-house-specs''>";
            $items = [
                ''Zodiac'' => $zodiac,
                ''Lord'' => $lord,
                ''Strength'' => $lord_str,
                ''Sign Pos.'' => $l_z_loc,
                ''House Pos.'' => $l_h_loc
            ];
            foreach ($items as $k => $v) {
                if ($v !== '''') {
                    echo "<div class=''mr-ku-spec-item''>";
                    echo "<div class=''mr-ku-spec-k''>" . esc_html($k) . "</div>";
                    echo "<div class=''mr-ku-spec-v''>" . esc_html($v) . "</div>";
                    echo "</div>";
                }
            }
            echo "</div>";

            echo "<div class=''mr-ku-house-pred''>";
            echo "<div style=''font-size:11px; font-weight:800; color:#B45309; text-transform:uppercase; margin-bottom:4px;''>Prediction &amp; Influences</div>";
            echo esc_html($pred);
            echo "</div>";
            echo "</div>"; // End .mr-ku-house-body
            echo "</div>"; // End .mr-ku-house-card
        }
        echo "</div>";

        echo "<script>
        function mrKuToggleHouse(head) {
            var card = head.closest(''.mr-ku-house-card'');
            if (!card) return;
            var isOpen = card.classList.contains(''is-open'');
            var allCards = document.querySelectorAll(''.mr-ku-house-card'');
            allCards.forEach(function(c) {
                c.classList.remove(''is-open'');
            });
            if (!isOpen) {
                card.classList.add(''is-open'');
            }
        }
        </script>";

    // 3. HOUSE EXPLAIN (PLANETARY POSITIONS)
    } elseif ($type === ''house-explain'') {
        echo "<div class=''mr-ku-planets-grid''>";
        foreach ($res as $key => $planet) {
            if (is_numeric($key)) {
                $name = esc_html($planet[''full_name'']);
                $zod = esc_html($planet[''zodiac'']);
                $hou = esc_html($planet[''house'']);
                $deg = number_format($planet[''local_degree''], 2) . "°";
                
                echo "<div class=''mr-ku-planet-card''>";
                echo "<div class=''mr-ku-planet-head''>🪐 " . $name . "</div>";
                echo "<div class=''mr-ku-planet-body''>";
                echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>Zodiac</div><div class=''mr-ku-planet-v''>" . $zod . "</div></div>";
                echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>House</div><div class=''mr-ku-planet-v''>" . $hou . "</div></div>";
                echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>Degree</div><div class=''mr-ku-planet-v''>" . $deg . "</div></div>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";

    // 4. ASHTAKOOT & DETAILED MATCHING
    } elseif (isset($res[''score''])) {
        $factors = [''tara'', ''gana'', ''yoni'', ''bhakoot'', ''grahamaitri'', ''vasya'', ''nadi'', ''varna'', ''dina'', ''mahendra'', ''sthree'', ''rasi'', ''rasiathi'', ''rajju'', ''vedha''];
        
        $max_score = 0;
        foreach ($factors as $f) {
            if (isset($res[$f])) {
                $max_score += floatval($res[$f][''full_score''] ?? 0);
            }
        }
        if ($max_score == 0) $max_score = 36;

        $total_score = $res[''score''];

        // Hero Score Card
        echo "<div class=''mr-ku-score-hero''>";
        echo "<div class=''mr-ku-score-hero-label''>Ashtakoot Guna Milan Score</div>";
        echo "<div class=''mr-ku-score-hero-val''>" . esc_html($total_score) . "<span class=''mr-ku-score-hero-max''> / " . esc_html($max_score) . "</span></div>";
        
        $status_label = ($total_score >= 24) ? ''Excellent Compatibility'' : (($total_score >= 18) ? ''Average / Acceptable Match'' : ''Low Compatibility (Remedies Advised)'');
        echo "<div style=''font-size:13.5px; font-weight:700; opacity:0.95;''>" . esc_html($status_label) . "</div>";
        echo "</div>";

        // Bot Response
        if (!empty($res[''bot_response''])) {
            echo "<div class=''mr-ku-callout''>";
            echo "<strong>💡 Astrological Analysis:</strong> " . esc_html($res[''bot_response'']);
            echo "</div>";
        }

        // Factors Grid
        echo "<div class=''mr-ku-subheading'' style=''margin-top:10px;''>8 Koota Parameters (अष्टकूट विवरण)</div>";
        echo "<div class=''mr-ku-factors-grid''>";
        foreach ($factors as $f) {
            if (isset($res[$f])) {
                $item = $res[$f];
                $name = $item[''name''] ?? ucfirst($f);
                $score = $item[$f] ?? 0;
                $full = $item[''full_score''] ?? 0;
                $desc = $item[''description''] ?? '''';
                
                $boy_val = $item["boy_{$f}"] ?? ''-'';
                $girl_val = $item["girl_{$f}"] ?? ''-'';
                
                if ($f === ''bhakoot'') {
                    $boy_val = $item[''boy_rasi_name''] ?? ''-'';
                    $girl_val = $item[''girl_rasi_name''] ?? ''-'';
                }
                if ($f === ''grahamaitri'') {
                    $boy_val = $item[''boy_lord''] ?? ''-'';
                    $girl_val = $item[''girl_lord''] ?? ''-'';
                }

                $ratio = ($full > 0) ? (floatval($score) / floatval($full)) : 0;
                $badge_class = ($ratio >= 0.8) ? ''mr-ku-badge-green'' : (($ratio >= 0.4) ? ''mr-ku-badge-gold'' : ''mr-ku-badge-red'');
                
                echo "<div class=''mr-ku-factor-card''>";
                echo "<div class=''mr-ku-factor-head''>";
                echo "<span class=''mr-ku-factor-title''>" . esc_html($name) . "</span>";
                echo "<span class=''mr-ku-badge {$badge_class}''>" . esc_html($score) . " / " . esc_html($full) . "</span>";
                echo "</div>";

                if ($boy_val !== ''-'' || $girl_val !== ''-'') {
                    echo "<div class=''mr-ku-factor-split''>";
                    echo "<div class=''mr-ku-split-box''><div class=''mr-ku-split-label''>Boy</div><div class=''mr-ku-split-val''>" . esc_html(ucfirst($boy_val)) . "</div></div>";
                    echo "<div class=''mr-ku-split-box''><div class=''mr-ku-split-label''>Girl</div><div class=''mr-ku-split-val''>" . esc_html(ucfirst($girl_val)) . "</div></div>";
                    echo "</div>";
                }
                
                if ($desc) {
                    echo "<div class=''mr-ku-factor-desc''>" . esc_html(ucfirst($desc)) . "</div>";
                }
                
                echo "</div>";
            }
        }
        echo "</div>";
        
        // Detailed Astro Profiles
        if (isset($res[''boy_astro_details'']) && isset($res[''girl_astro_details''])) {
            echo "<div class=''mr-ku-subheading''>Astrological Compatibility Profiles</div>";
            echo "<div class=''mr-ku-profile-grid''>";
            
            // Boy Dashboard
            echo "<div class=''mr-ku-profile-card''>";
            echo "<div class=''mr-ku-profile-head''>👦 Boy''s Astro Profile</div>";
            $boy = $res[''boy_astro_details''];
            $profile_keys = [
                ''Rasi'' => $boy[''rasi''] ?? '''',
                ''Nakshatra'' => ($boy[''nakshatra''] ?? '''') . " (Pada " . ($boy[''nakshatra_pada''] ?? '''') . ")",
                ''Ascendant'' => $boy[''ascendant_sign''] ?? '''',
                ''Gana'' => $boy[''gana''] ?? '''',
                ''Yoni'' => $boy[''yoni''] ?? '''',
                ''Vasya'' => $boy[''vasya''] ?? '''',
                ''Nadi'' => $boy[''nadi''] ?? '''',
                ''Varna'' => $boy[''varna''] ?? '''',
                ''Paya'' => $boy[''paya''] ?? '''',
                ''Tatva'' => $boy[''tatva''] ?? '''',
                ''Birth Dasa'' => ($boy[''birth_dasa''] ?? '''') . " (End: " . ($boy[''birth_dasa_time''] ?? '''') . ")",
                ''Current Dasa'' => ($boy[''current_dasa''] ?? '''') . " (End: " . ($boy[''current_dasa_time''] ?? '''') . ")",
                ''Lucky Gems'' => isset($boy[''lucky_gem'']) ? implode('', '', $boy[''lucky_gem'']) : '''',
                ''Lucky Colors'' => isset($boy[''lucky_colors'']) ? implode('', '', $boy[''lucky_colors'']) : '''',
                ''Lucky Numbers'' => isset($boy[''lucky_num'']) ? implode('', '', $boy[''lucky_num'']) : '''',
                ''Name Letters'' => isset($boy[''lucky_name_start'']) ? implode('', '', $boy[''lucky_name_start'']) : '''',
            ];

            echo "<div class=''mr-ku-profile-inner''>";
            foreach ($profile_keys as $k => $v) {
                if (!empty($v) && $v !== '' (Pada )'' && $v !== '' (End: )'') {
                    echo "<div><div class=''mr-ku-profile-item-k''>" . esc_html($k) . "</div><div class=''mr-ku-profile-item-v''>" . esc_html($v) . "</div></div>";
                }
            }
            echo "</div>";
            echo "</div>";

            // Girl Dashboard
            echo "<div class=''mr-ku-profile-card''>";
            echo "<div class=''mr-ku-profile-head''>👧 Girl''s Astro Profile</div>";
            $girl = $res[''girl_astro_details''];
            $profile_keys_g = [
                ''Rasi'' => $girl[''rasi''] ?? '''',
                ''Nakshatra'' => ($girl[''nakshatra''] ?? '''') . " (Pada " . ($girl[''nakshatra_pada''] ?? '''') . ")",
                ''Ascendant'' => $girl[''ascendant_sign''] ?? '''',
                ''Gana'' => $girl[''gana''] ?? '''',
                ''Yoni'' => $girl[''yoni''] ?? '''',
                ''Vasya'' => $girl[''vasya''] ?? '''',
                ''Nadi'' => $girl[''nadi''] ?? '''',
                ''Varna'' => $girl[''varna''] ?? '''',
                ''Paya'' => $girl[''paya''] ?? '''',
                ''Tatva'' => $girl[''tatva''] ?? '''',
                ''Birth Dasa'' => ($girl[''birth_dasa''] ?? '''') . " (End: " . ($girl[''birth_dasa_time''] ?? '''') . ")",
                ''Current Dasa'' => ($girl[''current_dasa''] ?? '''') . " (End: " . ($girl[''current_dasa_time''] ?? '''') . ")",
                ''Lucky Gems'' => isset($girl[''lucky_gem'']) ? implode('', '', $girl[''lucky_gem'']) : '''',
                ''Lucky Colors'' => isset($girl[''lucky_colors'']) ? implode('', '', $girl[''lucky_colors'']) : '''',
                ''Lucky Numbers'' => isset($girl[''lucky_num'']) ? implode('', '', $girl[''lucky_num'']) : '''',
                ''Name Letters'' => isset($girl[''lucky_name_start'']) ? implode('', '', $girl[''lucky_name_start'']) : '''',
            ];

            echo "<div class=''mr-ku-profile-inner''>";
            foreach ($profile_keys_g as $k => $v) {
                if (!empty($v) && $v !== '' (Pada )'' && $v !== '' (End: )'') {
                    echo "<div><div class=''mr-ku-profile-item-k''>" . esc_html($k) . "</div><div class=''mr-ku-profile-item-v''>" . esc_html($v) . "</div></div>";
                }
            }
            echo "</div>";
            echo "</div>"; 
            echo "</div>"; 
        }

        // Planetary Details
        if (isset($res[''boy_planetary_details'']) && isset($res[''girl_planetary_details''])) {
            echo "<div class=''mr-ku-subheading''>Planetary Alignments</div>";

            $render_planets = function($planets, $title, $icon) {
                echo "<div style=''margin-bottom:18px;''>";
                echo "<div style=''font-size:13px; font-weight:800; color:#B45309; margin-bottom:8px; text-transform:uppercase;''>{$icon} {$title}</div>";
                echo "<div class=''mr-ku-planets-grid''>";
                foreach ($planets as $p) {
                    $name = $p[''full_name''] ?? '''';
                    $zod = $p[''zodiac''] ?? '''';
                    $house = $p[''house''] ?? '''';
                    $deg = isset($p[''local_degree'']) ? number_format($p[''local_degree''], 2) . ''°'' : '''';
                    $nak = $p[''nakshatra''] ?? '''';
                    
                    if (!$name) continue;

                    echo "<div class=''mr-ku-planet-card''>";
                    echo "<div class=''mr-ku-planet-head''>{$name}</div>";
                    echo "<div class=''mr-ku-planet-body''>";
                    echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>Zodiac</div><div class=''mr-ku-planet-v''>{$zod}</div></div>";
                    echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>House</div><div class=''mr-ku-planet-v''>{$house}</div></div>";
                    echo "<div class=''mr-ku-planet-col''><div class=''mr-ku-planet-k''>Degree</div><div class=''mr-ku-planet-v''>{$deg}</div></div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
            };

            $render_planets($res[''boy_planetary_details''], "Boy''s Planetary Positions", "👦");
            $render_planets($res[''girl_planetary_details''], "Girl''s Planetary Positions", "👧");
        }
    }

    kundali_display_cross_sell();

    echo "<div class=''mr-ku-subheading''>✨ Explore Other Free Astrology Tools</div>";
    kundali_display_landing_page(false);
}
}

/* ====================================================
9. BILINGUAL FAQ SECTION
==================================================== */
if (!function_exists(''kundali_display_faq'')) {
function kundali_display_faq($type) {
    $is_matching = in_array($type, [''kundali-matching-short'', ''kundali-matching-detailed'', ''kundali-lagan-matching'', ''kundali-nakshatra-match'']);
    ?>
    <div class="mr-ku-faq-wrap">
        <h2 class="mr-ku-faq-title">Frequently Asked Questions (सामान्य प्रश्न)</h2>

        <?php if ($is_matching): ?>
            <!-- MATCHING FAQS -->
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">What is Ashtakoot Guna Milan? (अष्टकूट गुण मिलान क्या है?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> Ashtakoot Milan is a Vedic astrology method that compares 8 different parameters (Kootas) between the boy and girl to determine marriage compatibility. A score out of 36 is calculated. A score above 18 is considered acceptable.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> अष्टकूट मिलान वैदिक ज्योतिष की एक विधि है जिसमें विवाह अनुकूलता निर्धारित करने के लिए लड़के और लड़की के बीच 8 विभिन्न मापदंडों (कूटों) की तुलना की जाती है। 36 में से स्कोर की गणना की जाती है। 18 से ऊपर के स्कोर को विवाह के लिए अनुकूल माना जाता है।</div>
            </div>
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">How does Mangal Dosh affect marriage? (मंगल दोष विवाह को कैसे प्रभावित करता है?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> Mangal Dosh (Manglik) occurs when Mars is placed in specific houses in the birth chart. It can cause friction or delays in marriage. If both partners are Manglik, the dosh is often considered canceled or balanced out.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> मंगल दोष तब होता है जब मंगल जन्म कुंडली के कुछ विशिष्ट भावों में स्थित होता है। यह विवाह में देरी या तनाव का कारण बन सकता है। यदि दोनों साथी मांगलिक हैं, तो दोष अक्सर संतुलित या रद्द माना जाता है।</div>
            </div>
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">What is Nadi Dosh and is it severe? (नाड़ी दोष क्या है और क्या यह गंभीर है?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> Nadi represents health and progeny. If both partners have the same Nadi (Adi, Madhya, or Antya), Nadi Dosh is formed, resulting in a deduction of 8 points. It is considered severe, especially regarding childbirth, but can be mitigated with specific astrological remedies.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> नाड़ी स्वास्थ्य और संतान का प्रतिनिधित्व करती है। यदि दोनों भागीदारों की नाड़ी समान है, तो नाड़ी दोष बनता है, जिससे 8 अंकों की कटौती होती है। इसे संतानोत्पत्ति के संबंध में गंभीर माना जाता है, लेकिन विशिष्ट ज्योतिषीय उपायों से इसे कम किया जा सकता है।</div>
            </div>
        <?php else: ?>
            <!-- GENERAL/CHART FAQS -->
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">What is a Lagna Chart (D1)? (लग्न कुंडली (D1) क्या है?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> The Lagna or D1 Chart is the primary birth chart in Vedic astrology. It maps the precise positions of the planets, sun, and moon in the 12 zodiac signs at the exact moment and location of your birth. It serves as the foundation for all astrological predictions.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> लग्न या D1 चार्ट वैदिक ज्योतिष में प्राथमिक जन्म कुंडली है। यह आपके जन्म के सटीक समय और स्थान पर 12 राशियों में ग्रहों, सूर्य और चंद्रमा की सटीक स्थिति को दर्शाता है। यह सभी ज्योतिषीय भविष्यवाणियों की नींव के रूप में कार्य करता है।</div>
            </div>
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">Why is the exact Time of Birth important? (जन्म का सटीक समय क्यों महत्वपूर्ण है?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> In Vedic Astrology, the Ascendant (Lagna) changes roughly every 2 hours, and detailed divisional charts change every few minutes. An accurate time of birth ensures that your houses and planetary degrees are calculated correctly for precise predictions.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> वैदिक ज्योतिष में, लग्न (Ascendant) लगभग हर 2 घंटे में बदलता है, और विस्तृत सूक्ष्म कुंडलियाँ हर कुछ मिनटों में बदलती हैं। जन्म का सटीक समय यह सुनिश्चित करता है कि सटीक भविष्यवाणियों के लिए आपके भावों और ग्रहों की डिग्री की गणना सही ढंग से की गई है।</div>
            </div>
            <div class="mr-ku-faq-item">
                <div class="mr-ku-faq-q">How do planetary houses affect my life? (ग्रहों के भाव मेरे जीवन को कैसे प्रभावित करते हैं?)</div>
                <div class="mr-ku-faq-a"><span class="mr-ku-lang-badge mr-ku-lang-en">EN</span> Your Kundali is divided into 12 houses, each representing different aspects of life such as career, wealth, marriage, and health. The planet ruling a house and the planets occupying it heavily influence events and characteristics related to that area of your life.</div>
                <div class="mr-ku-faq-a hi"><span class="mr-ku-lang-badge mr-ku-lang-hi">HI</span> आपकी कुंडली 12 भावों में विभाजित है, जिनमें से प्रत्येक जीवन के विभिन्न पहलुओं जैसे करियर, धन, विवाह और स्वास्थ्य का प्रतिनिधित्व करता है। किसी भाव पर शासन करने वाला ग्रह और उसमें बैठे ग्रह आपके जीवन के उस क्षेत्र से जुड़ी घटनाओं और विशेषताओं को गहराई से प्रभावित करते हैं।</div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
}', `active` = 1 WHERE `id` = 73;
