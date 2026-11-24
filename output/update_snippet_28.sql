-- Update Snippet #28 (Zodiac Compatibility Engine)
UPDATE `af_snippets` SET `code` = '/**
 * Title: Zodiac Compatibility Engine & Shortcode
 * Shortcodes: [zodiac_compatibility_engine], [compatibility_results]
 * REST Route: /af/compat/v1/lookup
 * Version: 2.0.0
 */
if (!defined(''ABSPATH'')) exit;

/* ==========================================================================
   1. REST API ENDPOINT: /af/compat/v1/lookup
   ========================================================================== */
add_action(''rest_api_init'', function () {
    register_rest_route(''af/compat/v1'', ''/lookup'', [
        ''methods''             => ''GET'',
        ''permission_callback'' => ''__return_true'',
        ''callback''            => ''mr_compat_api_lookup_handler''
    ]);
});

function mr_compat_api_lookup_handler(\\WP_REST_Request $req) {
    global $wpdb;

    $your_sun       = sanitize_text_field($req->get_param(''your_sun'') ?: $req->get_param(''from''));
    $partner_sun    = sanitize_text_field($req->get_param(''partner_sun'') ?: $req->get_param(''to''));
    $partner_gender = sanitize_text_field($req->get_param(''partner_gender'') ?: $req->get_param(''partner_sex'') ?: $req->get_param(''to_gender''));
    $your_sex       = sanitize_text_field($req->get_param(''your_sex'') ?: $req->get_param(''from_gender''));
    $lang           = sanitize_text_field($req->get_param(''lang'') ?: ''en'');

    if (!$your_sun || !$partner_sun) {
        return [''ok'' => false, ''error'' => ''missing_params'', ''message'' => ''Both sun signs are required.''];
    }

    // Normalize Language
    $lang = strtolower(trim($lang));
    $language = ($lang === ''hindi'' || $lang === ''hi'') ? ''hi'' : ''en'';

    // Normalize Genders
    if ($partner_gender) {
        $p_gen = strtolower(trim($partner_gender));
        if ($p_gen === ''female'') {
            $gender_a = ''male'';
            $gender_b = ''female'';
        } else {
            $gender_a = ''female'';
            $gender_b = ''male'';
        }
    } elseif ($your_sex) {
        $y_gen = strtolower(trim($your_sex));
        if ($y_gen === ''female'') {
            $gender_a = ''female'';
            $gender_b = ''male'';
        } else {
            $gender_a = ''male'';
            $gender_b = ''female'';
        }
    } else {
        $gender_a = ''male'';
        $gender_b = ''female'';
    }

    $sign_a = ucfirst(strtolower(trim($your_sun)));
    $sign_b = ucfirst(strtolower(trim($partner_sun)));

    $table_pref = $wpdb->prefix . ''zodiac_compatibility'';
    $table_raw  = ''zodiac_compatibility'';
    $table = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pref)) ? $table_pref : $table_raw;

    // Primary gendered match
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table}
         WHERE sign_a = %s AND gender_a = %s
           AND sign_b = %s AND gender_b = %s
           AND language = %s LIMIT 1",
        $sign_a, $gender_a, $sign_b, $gender_b, $language
    ), ARRAY_A);

    // Fallback: reverse genders if not found
    if (!$row) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE sign_a = %s AND sign_b = %s
               AND language = %s LIMIT 1",
            $sign_a, $sign_b, $language
        ), ARRAY_A);
    }

    if (!$row) {
        return [''ok'' => false, ''found'' => false, ''table_used'' => $table];
    }

    $clean = function($k) use ($row) {
        return isset($row[$k]) ? wp_kses_post($row[$k]) : '''';
    };

    // Parse FAQ JSON safely
    $faq_list = [];
    if (!empty($row[''faq_json''])) {
        $decoded = json_decode($row[''faq_json''], true);
        if (is_array($decoded)) {
            $faq_list = $decoded;
        }
    }

    $scores = [
        ''general''       => (float)($row[''score_general''] ?? 0),
        ''love''          => (float)($row[''score_love''] ?? 0),
        ''friendship''    => (float)($row[''score_friendship''] ?? 0),
        ''trust''         => (float)($row[''score_trust''] ?? 0),
        ''dependability'' => (float)($row[''score_dependability''] ?? 0),
        ''longevity''     => (float)($row[''score_longevity''] ?? 0),
        ''marriage''      => (float)($row[''score_marriage''] ?? 0),
        ''kids''          => (float)($row[''score_kids''] ?? 0),
        ''finance''       => (float)($row[''score_finance''] ?? 0),
    ];

    $texts = [
        ''general''       => $clean(''general_text''),
        ''love''          => $clean(''love_text''),
        ''friendship''    => $clean(''friendship_text''),
        ''trust''         => $clean(''trust_text''),
        ''dependability'' => $clean(''dependability_text''),
        ''longevity''     => $clean(''longevity_text''),
        ''marriage''      => $clean(''marriage_text''),
        ''kids''          => $clean(''kids_text''),
        ''finance''       => $clean(''finance_text''),
    ];

    $metadata = [
        ''slug''            => $clean(''slug''),
        ''element_pair''    => $clean(''element_pair''),
        ''meta_title''      => $clean(''meta_title''),
        ''meta_description''=> $clean(''meta_description''),
        ''verdict_summary'' => $clean(''verdict_summary''),
        ''key_strength''    => $clean(''key_strength''),
        ''key_challenge''   => $clean(''key_challenge''),
        ''faq''             => $faq_list,
    ];

    return [
        ''ok''         => true,
        ''found''      => true,
        ''table_used'' => $table,
        ''params''     => [
            ''sign_a''   => $sign_a,
            ''gender_a'' => $gender_a,
            ''sign_b''   => $sign_b,
            ''gender_b'' => $gender_b,
            ''language'' => $language,
        ],
        ''metadata''   => $metadata,
        ''texts''      => $texts,
        ''scores''     => $scores,
    ];
}

/* ==========================================================================
   2. SHORTCODE: [zodiac_compatibility_engine] & [compatibility_results]
   ========================================================================== */
add_shortcode(''zodiac_compatibility_engine'', ''mr_render_zodiac_compatibility_engine'');
add_shortcode(''compatibility_results'', ''mr_render_zodiac_compatibility_engine'');

function mr_render_zodiac_compatibility_engine($atts = []) {
    $signs_data = [
        ''Aries''       => [''en'' => ''Aries'',       ''hi'' => ''मेष'',     ''icon'' => ''♈''],
        ''Taurus''      => [''en'' => ''Taurus'',      ''hi'' => ''वृषभ'',    ''icon'' => ''♉''],
        ''Gemini''      => [''en'' => ''Gemini'',      ''hi'' => ''मिथुन'',   ''icon'' => ''♊''],
        ''Cancer''      => [''en'' => ''Cancer'',      ''hi'' => ''कर्क'',     ''icon'' => ''♋''],
        ''Leo''         => [''en'' => ''Leo'',         ''hi'' => ''सिंह'',     ''icon'' => ''♌''],
        ''Virgo''       => [''en'' => ''Virgo'',       ''hi'' => ''कन्या'',    ''icon'' => ''♍''],
        ''Libra''       => [''en'' => ''Libra'',       ''hi'' => ''तुला'',     ''icon'' => ''♎''],
        ''Scorpio''     => [''en'' => ''Scorpio'',     ''hi'' => ''वृश्चिक'',  ''icon'' => ''♏''],
        ''Sagittarius'' => [''en'' => ''Sagittarius'', ''hi'' => ''धनु'',      ''icon'' => ''♐''],
        ''Capricorn''   => [''en'' => ''Capricorn'',   ''hi'' => ''मकर'',     ''icon'' => ''♑''],
        ''Aquarius''    => [''en'' => ''Aquarius'',    ''hi'' => ''कुंभ'',     ''icon'' => ''♒''],
        ''Pisces''      => [''en'' => ''Pisces'',      ''hi'' => ''मीन'',     ''icon'' => ''♓''],
    ];

    // Read initial query params from URL if present
    $def_your_sun       = sanitize_text_field($_GET[''your_sun''] ?? ''Aries'');
    $def_partner_sun    = sanitize_text_field($_GET[''partner_sun''] ?? ''Leo'');
    $def_partner_gender = sanitize_text_field($_GET[''partner_gender''] ?? ''female'');
    $def_lang           = sanitize_text_field($_GET[''lang''] ?? ''en'');
    $has_query          = !empty($_GET[''your_sun'']) && !empty($_GET[''partner_sun'']);

    $rest_url = esc_url_raw(rest_url(''af/compat/v1/lookup''));

    ob_start();
    ?>
    <div class="mr-compat-engine" id="mr-compat-engine" data-rest="<?php echo $rest_url; ?>">
        <style>
            .mr-compat-engine {
                --mr-bg: #FFFBEB;
                --mr-card-bg: #FFFFFF;
                --mr-border: #FDE68A;
                --mr-primary: #B45309;
                --mr-primary-hover: #92400E;
                --mr-amber-dark: #78350F;
                --mr-text: #1E293B;
                --mr-muted: #64748B;
                --mr-shadow: 0 4px 20px -2px rgba(180, 83, 9, 0.08);
                font-family: ''Noto Sans'', ''Noto Sans Devanagari'', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background-color: var(--mr-bg);
                border: 1px solid var(--mr-border);
                border-radius: 18px;
                padding: 24px;
                margin: 20px auto;
                max-width: 1100px;
                box-sizing: border-box;
                color: var(--mr-text);
            }
            .mr-compat-engine * { box-sizing: border-box; }

            /* Hide legacy duplicate Elementor form & heading if present on page */
            .elementor-element-13164f9,
            .elementor-element-63d6b55,
            .elementor-form[name="compatibility"] {
                display: none !important;
            }

            /* Header toolbar */
            .mr-compat-header {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 24px;
                padding-bottom: 18px;
                border-bottom: 1px solid var(--mr-border);
            }
            .mr-compat-title-wrap h2 {
                margin: 0 0 6px 0;
                font-size: 26px;
                font-weight: 800;
                color: var(--mr-amber-dark);
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .mr-compat-subtitle {
                margin: 0;
                font-size: 14px;
                color: var(--mr-muted);
            }

            /* Language Toggle */
            .mr-lang-toggle {
                display: inline-flex;
                background: #FEEBC8;
                padding: 3px;
                border-radius: 999px;
                border: 1px solid var(--mr-border);
            }
            .mr-lang-btn {
                border: none;
                background: transparent;
                padding: 6px 14px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                color: var(--mr-amber-dark);
                transition: all 0.2s ease;
            }
            .mr-lang-btn.active {
                background: var(--mr-primary);
                color: #FFFFFF;
                box-shadow: 0 2px 6px rgba(180, 83, 9, 0.25);
            }

            /* Form Card */
            .mr-compat-form-card {
                background: var(--mr-card-bg);
                border: 1px solid var(--mr-border);
                border-radius: 14px;
                padding: 20px;
                box-shadow: var(--mr-shadow);
                margin-bottom: 24px;
            }
            .mr-compat-grid-form {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 16px;
                align-items: flex-end;
            }
            .mr-form-group {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .mr-form-label {
                font-size: 13px;
                font-weight: 700;
                color: var(--mr-amber-dark);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mr-select {
                width: 100%;
                height: 44px;
                padding: 0 14px;
                background: #FFFDF7;
                border: 1.5px solid #FCD34D;
                border-radius: 10px;
                font-size: 15px;
                color: var(--mr-text);
                font-weight: 500;
                outline: none;
                cursor: pointer;
                transition: all 0.2s;
            }
            .mr-select:focus {
                border-color: var(--mr-primary);
                box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.15);
                background: #FFFFFF;
            }
            .mr-btn-submit {
                height: 44px;
                background: linear-gradient(135deg, #B45309 0%, #D97706 100%);
                color: #FFFFFF;
                border: none;
                border-radius: 10px;
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);
                transition: all 0.2s;
            }
            .mr-btn-submit:hover {
                background: linear-gradient(135deg, #92400E 0%, #B45309 100%);
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(180, 83, 9, 0.35);
            }
            .mr-btn-submit:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            /* Results Section */
            .mr-compat-results {
                display: none;
                animation: mrFadeIn 0.35s ease;
            }
            .mr-compat-results.active { display: block; }
            @keyframes mrFadeIn {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Hero Banner */
            .mr-hero-banner {
                background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
                border: 1px solid var(--mr-border);
                border-radius: 16px;
                padding: 24px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 24px;
                margin-bottom: 24px;
                box-shadow: var(--mr-shadow);
            }
            .mr-score-dial {
                width: 105px;
                height: 105px;
                border-radius: 50%;
                background: #FFFFFF;
                border: 4px solid var(--mr-primary);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 14px rgba(180, 83, 9, 0.2);
                flex-shrink: 0;
            }
            .mr-score-dial-val {
                font-size: 28px;
                font-weight: 900;
                color: var(--mr-primary);
                line-height: 1;
            }
            .mr-score-dial-lbl {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--mr-muted);
                margin-top: 4px;
            }
            .mr-hero-info {
                flex: 1;
                min-width: 250px;
            }
            .mr-element-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #FDE68A;
                color: #78350F;
                font-size: 12px;
                font-weight: 700;
                padding: 4px 12px;
                border-radius: 999px;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mr-hero-title {
                font-size: 22px;
                font-weight: 800;
                color: var(--mr-amber-dark);
                margin: 0 0 8px 0;
                line-height: 1.3;
            }
            .mr-hero-verdict {
                font-size: 14.5px;
                line-height: 1.6;
                color: #334155;
                margin: 0;
            }

            /* Strengths & Challenges */
            .mr-highlights-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }
            .mr-highlight-card {
                border-radius: 14px;
                padding: 18px 20px;
                border: 1px solid;
            }
            .mr-highlight-card.strength {
                background: #F0FDF4;
                border-color: #BBF7D0;
                color: #14532D;
            }
            .mr-highlight-card.challenge {
                background: #FFF7ED;
                border-color: #FFEDD5;
                color: #7C2D12;
            }
            .mr-highlight-title {
                font-size: 14px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin: 0 0 8px 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .mr-highlight-desc {
                font-size: 14px;
                line-height: 1.55;
                margin: 0;
            }

            /* Score Breakdown Grid */
            .mr-scores-section {
                background: var(--mr-card-bg);
                border: 1px solid var(--mr-border);
                border-radius: 14px;
                padding: 20px;
                margin-bottom: 24px;
                box-shadow: var(--mr-shadow);
            }
            .mr-section-heading {
                font-size: 18px;
                font-weight: 800;
                color: var(--mr-amber-dark);
                margin: 0 0 16px 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .mr-scores-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 14px 24px;
            }
            .mr-score-bar-row {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .mr-score-bar-top {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
            }
            .mr-score-bar-label {
                font-size: 13.5px;
                font-weight: 600;
                color: var(--mr-text);
            }
            .mr-score-bar-num {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--mr-amber-dark);
                font-variant-numeric: tabular-nums;
            }
            .mr-score-bar-track {
                width: 100%;
                height: 10px;
                background: #FEF3C7;
                border-radius: 999px;
                overflow: hidden;
            }
            .mr-score-bar-fill {
                height: 100%;
                border-radius: 999px;
                transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* 9 In-Depth Dimension Cards */
            .mr-insights-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }
            .mr-insight-card {
                background: var(--mr-card-bg);
                border: 1px solid var(--mr-border);
                border-radius: 14px;
                padding: 18px 20px;
                box-shadow: var(--mr-shadow);
                transition: transform 0.2s;
            }
            .mr-insight-card:hover {
                transform: translateY(-2px);
            }
            .mr-insight-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
                padding-bottom: 8px;
                border-bottom: 1px dashed var(--mr-border);
            }
            .mr-insight-title {
                font-size: 15px;
                font-weight: 800;
                color: var(--mr-amber-dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .mr-insight-badge {
                font-size: 12px;
                font-weight: 700;
                padding: 2px 8px;
                border-radius: 6px;
                background: #FEF3C7;
                color: var(--mr-primary);
            }
            .mr-insight-body {
                font-size: 14px;
                line-height: 1.6;
                color: #334155;
                margin: 0;
            }

            /* FAQ Accordion */
            .mr-faq-section {
                background: var(--mr-card-bg);
                border: 1px solid var(--mr-border);
                border-radius: 14px;
                padding: 20px;
                box-shadow: var(--mr-shadow);
            }
            .mr-faq-item {
                border-bottom: 1px solid #F3F4F6;
            }
            .mr-faq-item:last-child { border-bottom: none; }
            .mr-faq-question {
                width: 100%;
                text-align: left;
                background: none;
                border: none;
                padding: 14px 6px;
                font-size: 15px;
                font-weight: 700;
                color: var(--mr-text);
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }
            .mr-faq-question:hover { color: var(--mr-primary); }
            .mr-faq-arrow {
                font-size: 12px;
                transition: transform 0.2s;
            }
            .mr-faq-item.open .mr-faq-arrow {
                transform: rotate(180deg);
            }
            .mr-faq-answer {
                display: none;
                padding: 0 6px 14px 6px;
                font-size: 14px;
                line-height: 1.6;
                color: #475569;
            }
            .mr-faq-item.open .mr-faq-answer { display: block; }

            /* Loading Spinner */
            .mr-spinner {
                display: inline-block;
                width: 18px;
                height: 18px;
                border: 2px solid rgba(255,255,255,0.4);
                border-radius: 50%;
                border-top-color: #FFFFFF;
                animation: mrSpin 0.7s linear infinite;
            }
            @keyframes mrSpin { to { transform: rotate(360deg); } }

            @media (max-width: 680px) {
                /* Maximize usable width and eliminate wasted margins on mobile */
                .elementor-element.elementor-element-2b58b0d,
                .elementor-296 .e-con-boxed,
                .elementor-296 .e-con-full,
                .elementor-296 .elementor-element {
                    padding-left: 2px !important;
                    padding-right: 2px !important;
                }
                .mr-compat-engine {
                    padding: 12px 8px;
                    margin: 4px 0 60px 0;
                    border-radius: 12px;
                    width: 100%;
                    max-width: 100%;
                }
                .mr-compat-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 12px;
                    margin-bottom: 16px;
                    padding-bottom: 14px;
                }
                .mr-compat-title-wrap h2 {
                    font-size: 20px;
                }
                .mr-compat-form-card,
                .mr-scores-section,
                .mr-hero-banner,
                .mr-insight-card,
                .mr-highlight-card,
                .mr-faq-section {
                    padding: 14px 10px;
                    border-radius: 12px;
                    margin-bottom: 14px;
                }
                .mr-hero-banner {
                    flex-direction: column;
                    text-align: center;
                    gap: 14px;
                }
                .mr-hero-info {
                    text-align: center;
                    min-width: 100%;
                }
                .mr-hero-title {
                    font-size: 19px;
                }
                .mr-scores-grid {
                    grid-template-columns: 1fr;
                    gap: 12px;
                }
                .mr-highlights-grid,
                .mr-insights-grid {
                    grid-template-columns: 1fr;
                    gap: 12px;
                }
                .mr-score-dial {
                    width: 86px;
                    height: 86px;
                }
                .mr-score-dial-val {
                    font-size: 24px;
                }
            }
        </style>

        <!-- Header Toolbar -->
        <div class="mr-compat-header">
            <div class="mr-compat-title-wrap">
                <h2 id="mr-title-text">
                    <span>✨</span>
                    <span class="mr-txt-en">Zodiac Compatibility Calculator</span>
                    <span class="mr-txt-hi" style="display:none;">राशि अनुकूलता कैलकुलेटर</span>
                </h2>
                <p class="mr-compat-subtitle">
                    <span class="mr-txt-en">Check love, marriage, trust, and relationship synergy between any two sun signs</span>
                    <span class="mr-txt-hi" style="display:none;">दो राशियों के बीच प्रेम, विवाह, विश्वास और दीर्घकालिक सामंजस्य की संपूर्ण जांच करें</span>
                </p>
            </div>
            <div class="mr-lang-toggle" role="radiogroup" aria-label="Language">
                <button type="button" class="mr-lang-btn <?php echo $def_lang === ''en'' ? ''active'' : ''''; ?>" data-lang="en">English</button>
                <button type="button" class="mr-lang-btn <?php echo $def_lang === ''hi'' ? ''active'' : ''''; ?>" data-lang="hi">हिन्दी</button>
            </div>
        </div>

        <!-- Form Card -->
        <div class="mr-compat-form-card">
            <form id="mr-compat-form" method="GET" action="">
                <input type="hidden" name="lang" id="mr-input-lang" value="<?php echo esc_attr($def_lang); ?>">

                <div class="mr-compat-grid-form">
                    <!-- Your Sun Sign -->
                    <div class="mr-form-group">
                        <label class="mr-form-label" for="mr-your-sun">
                            <span class="mr-txt-en">Your Sun Sign</span>
                            <span class="mr-txt-hi" style="display:none;">आपकी राशि</span>
                        </label>
                        <select name="your_sun" id="mr-your-sun" class="mr-select" required>
                            <?php foreach ($signs_data as $key => $s): ?>
                                <option value="<?php echo $key; ?>" <?php selected($def_your_sun, $key); ?>>
                                    <?php echo $s[''icon''] . '' '' . $s[''en''] . '' ('' . $s[''hi''] . '')''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Partner''s Sun Sign -->
                    <div class="mr-form-group">
                        <label class="mr-form-label" for="mr-partner-sun">
                            <span class="mr-txt-en">Partner''s Sun Sign</span>
                            <span class="mr-txt-hi" style="display:none;">साथी की राशि</span>
                        </label>
                        <select name="partner_sun" id="mr-partner-sun" class="mr-select" required>
                            <?php foreach ($signs_data as $key => $s): ?>
                                <option value="<?php echo $key; ?>" <?php selected($def_partner_sun, $key); ?>>
                                    <?php echo $s[''icon''] . '' '' . $s[''en''] . '' ('' . $s[''hi''] . '')''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Partner''s Gender -->
                    <div class="mr-form-group">
                        <label class="mr-form-label" for="mr-partner-gender">
                            <span class="mr-txt-en">Partner''s Gender</span>
                            <span class="mr-txt-hi" style="display:none;">साथी का लिंग</span>
                        </label>
                        <select name="partner_gender" id="mr-partner-gender" class="mr-select" required>
                            <option value="female" <?php selected($def_partner_gender, ''female''); ?>>
                                Female (महिला)
                            </option>
                            <option value="male" <?php selected($def_partner_gender, ''male''); ?>>
                                Male (पुरुष)
                            </option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="mr-form-group">
                        <button type="submit" id="mr-btn-calc" class="mr-btn-submit">
                            <span class="mr-btn-icon">⚡</span>
                            <span class="mr-btn-text">
                                <span class="mr-txt-en">Check Compatibility</span>
                                <span class="mr-txt-hi" style="display:none;">अनुकूलता जांचें</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Dynamic Results Container -->
        <div id="mr-compat-results" class="mr-compat-results" aria-live="polite">
            <!-- Will be populated dynamically via JavaScript -->
        </div>
    </div>

    <!-- Client Script -->
    <script>
    (function(){
        const root = document.getElementById(''mr-compat-engine'');
        if (!root) return;

        const restURL = root.getAttribute(''data-rest'');
        const form = document.getElementById(''mr-compat-form'');
        const resultsEl = document.getElementById(''mr-compat-results'');
        const submitBtn = document.getElementById(''mr-btn-calc'');
        const langInput = document.getElementById(''mr-input-lang'');
        const langBtns = root.querySelectorAll(''.mr-lang-btn'');

        let currentLang = langInput.value || ''en'';

        // Dimension labels mapping
        const labelsMap = {
            en: {
                general: ''Overall Match'',
                love: ''Love & Romance'',
                friendship: ''Friendship'',
                trust: ''Trust & Faith'',
                dependability: ''Dependability'',
                longevity: ''Longevity'',
                marriage: ''Marriage Life'',
                kids: ''Parenting & Kids'',
                finance: ''Financial Synergy'',
                scoresHeading: ''Detailed Compatibility Ratings'',
                insightsHeading: ''Astrological Dimension Breakdown'',
                faqHeading: ''Frequently Asked Questions'',
                strengthTitle: ''Key Synergy & Strength'',
                challengeTitle: ''Growth Area & Challenge'',
                calcBtn: ''Check Compatibility'',
                loading: ''Analyzing Cosmic Compatibility…'',
                errorMsg: ''Could not load compatibility data. Please try again.''
            },
            hi: {
                general: ''समग्र तालमेल'',
                love: ''प्रेम व आकर्षण'',
                friendship: ''मित्रता व समझ'',
                trust: ''विश्वास व निष्ठा'',
                dependability: ''विश्वसनीयता व संबल'',
                longevity: ''दीर्घकालिक स्थायित्व'',
                marriage: ''वैवाहिक जीवन'',
                kids: ''संतान सुख व परवरिश'',
                finance: ''वित्तीय सामंजस्य'',
                scoresHeading: ''विस्तृत अनुकूलता रेटिंग'',
                insightsHeading: ''ज्योतिषीय आयामों का विस्तृत विश्लेषण'',
                faqHeading: ''अक्सर पूछे जाने वाले प्रश्न'',
                strengthTitle: ''रिश्ते की मुख्य मजबूती'',
                challengeTitle: ''सुधार व सजगता का क्षेत्र'',
                calcBtn: ''अनुकूलता जांचें'',
                loading: ''कुंडली अनुकूलता का विश्लेषण हो रहा है…'',
                errorMsg: ''डेटा लोड करने में समस्या हुई। कृपया पुनः प्रयास करें।''
            }
        };

        function setLanguage(lang) {
            currentLang = lang;
            langInput.value = lang;
            langBtns.forEach(btn => {
                btn.classList.toggle(''active'', btn.getAttribute(''data-lang'') === lang);
            });
            root.querySelectorAll(''.mr-txt-en'').forEach(el => el.style.display = (lang === ''en'' ? '''' : ''none''));
            root.querySelectorAll(''.mr-txt-hi'').forEach(el => el.style.display = (lang === ''hi'' ? '''' : ''none''));
        }

        langBtns.forEach(btn => {
            btn.addEventListener(''click'', function(){
                const newLang = this.getAttribute(''data-lang'');
                if (newLang === currentLang) return;
                setLanguage(newLang);
                if (resultsEl.classList.contains(''active'')) {
                    triggerFetch();
                }
            });
        });

        function getScoreColor(val) {
            if (val >= 7.5) return ''linear-gradient(90deg, #10B981, #059669)'';
            if (val >= 6.0) return ''linear-gradient(90deg, #F59E0B, #D97706)'';
            return ''linear-gradient(90deg, #F43F5E, #E11D48)'';
        }

        async function triggerFetch() {
            const your_sun = document.getElementById(''mr-your-sun'').value;
            const partner_sun = document.getElementById(''mr-partner-sun'').value;
            const partner_gender = document.getElementById(''mr-partner-gender'').value;

            // Sync URL query parameters
            const urlObj = new URL(location.href);
            urlObj.searchParams.set(''your_sun'', your_sun);
            urlObj.searchParams.set(''partner_sun'', partner_sun);
            urlObj.searchParams.set(''partner_gender'', partner_gender);
            urlObj.searchParams.set(''lang'', currentLang);
            history.replaceState(null, '''', urlObj.toString());

            // Loading state
            submitBtn.disabled = true;
            submitBtn.querySelector(''.mr-btn-icon'').innerHTML = ''<span class="mr-spinner"></span>'';
            resultsEl.classList.add(''active'');
            resultsEl.innerHTML = `<div style="text-align:center; padding:40px; color:var(--mr-amber-dark); font-weight:700;"><span class="mr-spinner" style="border-top-color:var(--mr-primary); border-color:#FDE68A; width:30px; height:30px; margin-bottom:12px;"></span><br>${labelsMap[currentLang].loading}</div>`;

            const queryParams = new URLSearchParams({
                your_sun: your_sun,
                partner_sun: partner_sun,
                partner_gender: partner_gender,
                lang: currentLang
            });

            try {
                const res = await fetch(`${restURL}?${queryParams.toString()}`, { credentials: ''same-origin'' });
                const json = await res.json();

                if (!json.ok || !json.found) {
                    resultsEl.innerHTML = `<div style="background:#FFF1F2; border:1px solid #FECDD3; border-radius:12px; padding:18px; color:#BE123C; text-align:center;">${labelsMap[currentLang].errorMsg}</div>`;
                    return;
                }

                renderResults(json);
            } catch (err) {
                resultsEl.innerHTML = `<div style="background:#FFF1F2; border:1px solid #FECDD3; border-radius:12px; padding:18px; color:#BE123C; text-align:center;">${labelsMap[currentLang].errorMsg}</div>`;
            } finally {
                submitBtn.disabled = false;
                submitBtn.querySelector(''.mr-btn-icon'').innerHTML = ''⚡'';
            }
        }

        function renderResults(res) {
            const m = res.metadata || {};
            const scores = res.scores || {};
            const texts = res.texts || {};
            const lang = res.params.language || currentLang;
            const l = labelsMap[lang] || labelsMap.en;

            const genScore = typeof scores.general === ''number'' ? scores.general : 7.0;
            const genPct = Math.round(genScore * 10);

            // Scores order
            const dimKeys = [''general'', ''love'', ''friendship'', ''trust'', ''dependability'', ''longevity'', ''marriage'', ''kids'', ''finance''];

            // Build Dimension Cards HTML
            let insightsHtml = '''';
            dimKeys.forEach(k => {
                const sVal = typeof scores[k] === ''number'' ? scores[k].toFixed(1) : ''—'';
                const bodyTxt = texts[k] || '''';
                const title = l[k] || k;
                insightsHtml += `
                    <div class="mr-insight-card">
                        <div class="mr-insight-header">
                            <h4 class="mr-insight-title">
                                <span>✦</span> ${title}
                            </h4>
                            <span class="mr-insight-badge">${sVal} / 10</span>
                        </div>
                        <p class="mr-insight-body">${bodyTxt}</p>
                    </div>
                `;
            });

            // Build Progress Bars HTML
            let barsHtml = '''';
            dimKeys.forEach(k => {
                const sVal = typeof scores[k] === ''number'' ? scores[k] : 0;
                const pct = Math.min(100, Math.max(0, sVal * 10));
                const title = l[k] || k;
                const fillGrad = getScoreColor(sVal);
                barsHtml += `
                    <div class="mr-score-bar-row">
                        <div class="mr-score-bar-top">
                            <span class="mr-score-bar-label">${title}</span>
                            <span class="mr-score-bar-num">${sVal.toFixed(1)} / 10</span>
                        </div>
                        <div class="mr-score-bar-track">
                            <div class="mr-score-bar-fill" style="width:${pct}%; background:${fillGrad};"></div>
                        </div>
                    </div>
                `;
            });

            // Build FAQ HTML & Schema.org JSON-LD
            let faqHtml = '''';
            let schemaJsonLd = '''';
            if (Array.isArray(m.faq) && m.faq.length > 0) {
                let faqItems = '''';
                const schemaEntities = [];
                m.faq.forEach((item, idx) => {
                    faqItems += `
                        <div class="mr-faq-item ${idx === 0 ? ''open'' : ''''}">
                            <button type="button" class="mr-faq-question" aria-expanded="${idx === 0 ? ''true'' : ''false''}">
                                <span>${item.question}</span>
                                <span class="mr-faq-arrow">▼</span>
                            </button>
                            <div class="mr-faq-answer">${item.answer}</div>
                        </div>
                    `;
                    schemaEntities.push({
                        "@type": "Question",
                        "name": item.question,
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": item.answer
                        }
                    });
                });

                faqHtml = `
                    <div class="mr-faq-section">
                        <h3 class="mr-section-heading"><span>❓</span> ${l.faqHeading}</h3>
                        <div class="mr-faq-list">${faqItems}</div>
                    </div>
                `;

                schemaJsonLd = `
                    <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "FAQPage",
                        "mainEntity": ${JSON.stringify(schemaEntities)}
                    }
                    <\\/script>
                `;
            }

            resultsEl.innerHTML = `
                <!-- Hero Banner -->
                <div class="mr-hero-banner">
                    <div class="mr-score-dial">
                        <span class="mr-score-dial-val">${genPct}%</span>
                        <span class="mr-score-dial-lbl">Score</span>
                    </div>
                    <div class="mr-hero-info">
                        <div class="mr-element-badge">🔥 ${m.element_pair || ''Elemental Synergy''}</div>
                        <h3 class="mr-hero-title">${m.meta_title || ''''}</h3>
                        <p class="mr-hero-verdict">${m.verdict_summary || ''''}</p>
                    </div>
                </div>

                <!-- Strengths & Challenges -->
                <div class="mr-highlights-grid">
                    <div class="mr-highlight-card strength">
                        <h4 class="mr-highlight-title"><span>🛡️</span> ${l.strengthTitle}</h4>
                        <p class="mr-highlight-desc">${m.key_strength || ''''}</p>
                    </div>
                    <div class="mr-highlight-card challenge">
                        <h4 class="mr-highlight-title"><span>⚖️</span> ${l.challengeTitle}</h4>
                        <p class="mr-highlight-desc">${m.key_challenge || ''''}</p>
                    </div>
                </div>

                <!-- Score Progress Bars -->
                <div class="mr-scores-section">
                    <h3 class="mr-section-heading"><span>📊</span> ${l.scoresHeading}</h3>
                    <div class="mr-scores-grid">${barsHtml}</div>
                </div>

                <!-- 9 Astrological Dimension Cards -->
                <div class="mr-insights-section">
                    <h3 class="mr-section-heading" style="margin-bottom:18px;"><span>📖</span> ${l.insightsHeading}</h3>
                    <div class="mr-insights-grid">${insightsHtml}</div>
                </div>

                <!-- FAQ Section -->
                ${faqHtml}
                ${schemaJsonLd}
            `;

            // Setup FAQ accordion toggles
            resultsEl.querySelectorAll(''.mr-faq-question'').forEach(qBtn => {
                qBtn.addEventListener(''click'', function(){
                    const item = this.closest(''.mr-faq-item'');
                    const isOpen = item.classList.contains(''open'');
                    item.classList.toggle(''open'', !isOpen);
                    this.setAttribute(''aria-expanded'', !isOpen ? ''true'' : ''false'');
                });
            });

            // Smooth scroll into results
            setTimeout(() => {
                resultsEl.scrollIntoView({ behavior: ''smooth'', block: ''nearest'' });
            }, 100);
        }

        // Intercept any external Elementor form on the page if present
        document.addEventListener(''submit'', function(ev){
            const extForm = ev.target;
            if (!(extForm instanceof HTMLFormElement)) return;
            if (extForm.id === ''mr-compat-form'') return;

            const ySun = extForm.querySelector(''[name*="your_sun"]'');
            const pSun = extForm.querySelector(''[name*="partner_sun"]'');
            if (!ySun || !pSun) return;

            ev.preventDefault();
            ev.stopPropagation();
            ev.stopImmediatePropagation();

            if (ySun.value) document.getElementById(''mr-your-sun'').value = ySun.value;
            if (pSun.value) document.getElementById(''mr-partner-sun'').value = pSun.value;

            const pGender = extForm.querySelector(''[name*="partner_gender"]'') || extForm.querySelector(''[name*="partner_sex"]'');
            const ySex = extForm.querySelector(''[name*="your_sex"]'');
            if (pGender) {
                document.getElementById(''mr-partner-gender'').value = pGender.value.toLowerCase();
            } else if (ySex) {
                document.getElementById(''mr-partner-gender'').value = (ySex.value.toLowerCase() === ''male'' ? ''female'' : ''male'');
            }

            const lField = extForm.querySelector(''[name*="lang"]'');
            if (lField) {
                const lv = lField.value.toLowerCase();
                setLanguage((lv === ''hindi'' || lv === ''hi'') ? ''hi'' : ''en'');
            }

            triggerFetch();
            return false;
        }, true);

        form.addEventListener(''submit'', function(ev){
            ev.preventDefault();
            triggerFetch();
        });

        // Auto-run if URL already contains params or default
        <?php if ($has_query): ?>
        triggerFetch();
        <?php endif; ?>
    })();
    </script>
    <?php
    return ob_get_clean();
}
', `modified` = NOW() WHERE `id` = 28;
