-- Update Snippet #33 (Remedies Selector & Engine)
UPDATE `af_snippets` SET `code` = '/**
 * Title: Remedies Selector with Filters + Detail View + Responsive (Vedic Theme Redesign)
 * Version: 2.1.0
 * Description: Redesigned interactive remedies engine for MeraRashifal featuring 20 categorized
 *              life issues, advanced filters, AI quick answers, action checklists, FAQs,
 *              mobile bottom-sheet drawer modal, and 4 Master Reports cross-sell integration.
 */

if (!defined(''ABSPATH'')) exit;

/* ====================================================
 * 1. ISSUE DEFINITIONS (20 LIFE ISSUES)
 * ==================================================== */
if (!function_exists(''mr_get_all_remedy_issues'')) {
function mr_get_all_remedy_issues() {
    return [
        // Career & Business
        [
            ''key''      => ''Job Search'',
            ''slug''     => ''job-search'',
            ''title_en'' => ''Job Search'',
            ''title_hi'' => ''नई नौकरी व रोजगार'',
            ''cat''      => ''career'',
            ''icon''     => ''💼'',
            ''desc''     => ''Interviews, resume visibility & career breaks''
        ],
        [
            ''key''      => ''Job Promotion'',
            ''slug''     => ''job-promotion'',
            ''title_en'' => ''Job Promotion'',
            ''title_hi'' => ''पदोन्नति व तरक्की'',
            ''cat''      => ''career'',
            ''icon''     => ''📈'',
            ''desc''     => ''Executive elevation, appraisals & boss rapport''
        ],
        [
            ''key''      => ''Business Loss or Slow Down'',
            ''slug''     => ''business-loss-slow-down'',
            ''title_en'' => ''Business Loss / Slump'',
            ''title_hi'' => ''व्यापार में मंदी व हानि'',
            ''cat''      => ''career'',
            ''icon''     => ''🏢'',
            ''desc''     => ''Cash flow, market revival & trade growth''
        ],
        [
            ''key''      => ''Relations At Work'',
            ''slug''     => ''relations-at-work'',
            ''title_en'' => ''Workplace Relations'',
            ''title_hi'' => ''ऑफिस में सहकर्मियों से अनबन'',
            ''cat''      => ''career'',
            ''icon''     => ''👥'',
            ''desc''     => ''Colleague friction, office politics & reputation''
        ],

        // Love & Marriage
        [
            ''key''      => ''Love'',
            ''slug''     => ''love'',
            ''title_en'' => ''Love & Romance'',
            ''title_hi'' => ''प्रेम संबंध व आकर्षण'',
            ''cat''      => ''love'',
            ''icon''     => ''💖'',
            ''desc''     => ''Misunderstandings, emotional distance & harmony''
        ],
        [
            ''key''      => ''Marriage Issues'',
            ''slug''     => ''marriage-issues'',
            ''title_en'' => ''Marriage Issues'',
            ''title_hi'' => ''वैवाहिक जीवन में कलह'',
            ''cat''      => ''love'',
            ''icon''     => ''💍'',
            ''desc''     => ''Spousal conflict, in-law friction & marital peace''
        ],
        [
            ''key''      => ''Domestic Issues'',
            ''slug''     => ''domestic-issues'',
            ''title_en'' => ''Domestic Issues'',
            ''title_hi'' => ''गृह क्लेश व पारिवारिक अशांति'',
            ''cat''      => ''love'',
            ''icon''     => ''🏡'',
            ''desc''     => ''Household peace, property harmony & calmness''
        ],

        // Wealth & Property
        [
            ''key''      => ''Savings'',
            ''slug''     => ''savings'',
            ''title_en'' => ''Savings & Expenses'',
            ''title_hi'' => ''धन संचय व बचत की कमी'',
            ''cat''      => ''wealth'',
            ''icon''     => ''💰'',
            ''desc''     => ''Curtailing sudden drains & building assets''
        ],
        [
            ''key''      => ''Loans'',
            ''slug''     => ''loans'',
            ''title_en'' => ''Loans & Liabilities'',
            ''title_hi'' => ''कर्ज व आर्थिक देनदारी'',
            ''cat''      => ''wealth'',
            ''icon''     => ''💳'',
            ''desc''     => ''Debt freedom, repayment hurdles & relief''
        ],
        [
            ''key''      => ''Home Buying'',
            ''slug''     => ''home-buying'',
            ''title_en'' => ''Home Buying'',
            ''title_hi'' => ''मकान व भूमि क्रय'',
            ''cat''      => ''wealth'',
            ''icon''     => ''🏠'',
            ''desc''     => ''Real estate registry, loans & dream home hurdles''
        ],
        [
            ''key''      => ''Property Disputes'',
            ''slug''     => ''property-disputes'',
            ''title_en'' => ''Property Disputes'',
            ''title_hi'' => ''पैतृक संपत्ति व जमीन विवाद'',
            ''cat''      => ''wealth'',
            ''icon''     => ''📜'',
            ''desc''     => ''Inheritance conflicts, land registry & partition''
        ],

        // Health & Protection
        [
            ''key''      => ''Health'',
            ''slug''     => ''health'',
            ''title_en'' => ''Health & Vitality'',
            ''title_hi'' => ''स्वास्थ्य व आरोग्य'',
            ''cat''      => ''health'',
            ''icon''     => ''🩺'',
            ''desc''     => ''Chronic exhaustion, wellness & vital energy''
        ],
        [
            ''key''      => ''Parents Health'',
            ''slug''     => ''parents-health'',
            ''title_en'' => ''Parents Health'',
            ''title_hi'' => ''माता-पिता का स्वास्थ्य'',
            ''cat''      => ''health'',
            ''icon''     => ''👵'',
            ''desc''     => ''Elderly care, paternal & maternal longevity''
        ],
        [
            ''key''      => ''Evil Eye'',
            ''slug''     => ''evil-eye'',
            ''title_en'' => ''Evil Eye & Negativity'',
            ''title_hi'' => ''बुरी नज़र व नकारात्मक ऊर्जा'',
            ''cat''      => ''health'',
            ''icon''     => ''🧿'',
            ''desc''     => ''Psychic protection, aura clearing & nazar dosha''
        ],
        [
            ''key''      => ''Law Cases'',
            ''slug''     => ''law-cases'',
            ''title_en'' => ''Law Cases & Courts'',
            ''title_hi'' => ''कोर्ट-कचहरी व कानूनी विवाद'',
            ''cat''      => ''health'',
            ''icon''     => ''⚖️'',
            ''desc''     => ''Litigation delays, false accusations & legal victory''
        ],

        // Education & Travel
        [
            ''key''      => ''Studies'',
            ''slug''     => ''studies'',
            ''title_en'' => ''Studies & Exams'',
            ''title_hi'' => ''पढ़ाई व एकाग्रता में रुकावट'',
            ''cat''      => ''education'',
            ''icon''     => ''📚'',
            ''desc''     => ''Concentration, exam fear & academic retention''
        ],
        [
            ''key''      => ''Study Abroad'',
            ''slug''     => ''study-abroad'',
            ''title_en'' => ''Study Abroad & Visa'',
            ''title_hi'' => ''विदेश में उच्च शिक्षा व वीज़ा'',
            ''cat''      => ''education'',
            ''icon''     => ''✈️'',
            ''desc''     => ''Visa processing, foreign admission & scholarships''
        ],

        // Family & Social
        [
            ''key''      => ''Children Issues'',
            ''slug''     => ''children-issues'',
            ''title_en'' => ''Children Issues'',
            ''title_hi'' => ''संतान संबंधी चिंताएं'',
            ''cat''      => ''family'',
            ''icon''     => ''👶'',
            ''desc''     => ''Child wellness, behavioral friction & progeny blessings''
        ],
        [
            ''key''      => ''Sibling Relations'',
            ''slug''     => ''sibling-relations'',
            ''title_en'' => ''Sibling Relations'',
            ''title_hi'' => ''भाई-बहनों से अनबन'',
            ''cat''      => ''family'',
            ''icon''     => ''🫂'',
            ''desc''     => ''Brother-sister bonding, jealousy & mutual warmth''
        ],
        [
            ''key''      => ''Friendship Issues'',
            ''slug''     => ''friendship-issues'',
            ''title_en'' => ''Friendship Issues'',
            ''title_hi'' => ''मित्रता में मतभेद'',
            ''cat''      => ''family'',
            ''icon''     => ''🤝'',
            ''desc''     => ''Peer betrayal, social trust & genuine alliances''
        ]
    ];
}
}

/* ====================================================
 * 2. CONTENT FORMATTING HELPERS
 * ==================================================== */
if (!function_exists(''mr_format_remedy_text'')) {
function mr_format_remedy_text($text) {
    if (empty($text)) return '''';
    $lines = explode("\\n", str_replace(["\\r\\n", "\\r"], "\\n", $text));
    $html = '''';
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Match prefix with colon (e.g. "🔴 मूल ज्योतिषीय कारण: text" or "📿 वैदिक मंत्र साधना: text")
        if (preg_match(''/^([^\\:\\：]+[\\:\\：])\\s*(.*)$/u'', $line, $matches)) {
            $prefix = $matches[1];
            $rest = $matches[2];
            $html .= ''<div class="mr-line-item"><strong class="mr-line-label">'' . esc_html($prefix) . ''</strong> <span class="mr-line-val">'' . esc_html($rest) . ''</span></div>'';
        } else {
            $html .= ''<div class="mr-line-item">'' . esc_html($line) . ''</div>'';
        }
    }
    return $html;
}
}

if (!function_exists(''mr_format_card_summary'')) {
function mr_format_card_summary($summary) {
    if (empty($summary)) return '''';
    $lines = explode("\\n", str_replace(["\\r\\n", "\\r"], "\\n", $summary));
    $html = ''<div class="mr-card-summary-list">'';
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (preg_match(''/^([^\\:\\：]+[\\:\\：])\\s*(.*)$/u'', $line, $matches)) {
            $prefix = $matches[1];
            $rest = $matches[2];
            $html .= ''<div class="mr-cs-item"><strong class="mr-cs-label">'' . esc_html($prefix) . ''</strong> <span>'' . esc_html($rest) . ''</span></div>'';
        } else {
            $html .= ''<div class="mr-cs-item"><span>'' . esc_html($line) . ''</span></div>'';
        }
    }
    $html .= ''</div>'';
    return $html;
}
}

/* ====================================================
 * 3. SHORTCODE: [remedy_selector]
 * ==================================================== */
add_shortcode(''remedy_selector'', function () {
    $issues = mr_get_all_remedy_issues();
    ob_start();
    ?>
    <div id="mr-remedies-engine" class="mr-remedy-wrap">
        <!-- Hero Header -->
        <div class="mr-remedy-header">
            <span class="mr-remedy-badge">⚡ वैदिक ज्योतिष एवं संपूर्ण जीवन समाधान | Vedic Solutions</span>
            <h1 class="mr-remedy-main-title">समस्त जीवन समस्याओं के अचूक वैदिक व व्यावहारिक उपाय</h1>
            <p class="mr-remedy-subtitle">
                ग्रह शांति (Astrological), सामाजिक सामंजस्य (Sociological) और मानसिक संतुलन (Emotional) का त्रिवेणी संगम — अपनी समस्या चुनें और तुरंत सटीक उपाय प्राप्त करें।
            </p>
        </div>

        <!-- Issue Category Navigator & Search -->
        <div class="mr-issue-nav-box">
            <div class="mr-issue-nav-top">
                <div class="mr-issue-tabs-wrap">
                    <div class="mr-issue-tabs" id="mr-issue-cats">
                        <button type="button" class="mr-issue-tab is-active" data-cat="all">🌟 सभी विषय (All 20)</button>
                        <button type="button" class="mr-issue-tab" data-cat="career">💼 करियर व व्यापार</button>
                        <button type="button" class="mr-issue-tab" data-cat="love">💖 प्रेम व विवाह</button>
                        <button type="button" class="mr-issue-tab" data-cat="wealth">💰 धन व संपत्ति</button>
                        <button type="button" class="mr-issue-tab" data-cat="health">🩺 स्वास्थ्य व सुरक्षा</button>
                        <button type="button" class="mr-issue-tab" data-cat="education">📚 शिक्षा व विदेश</button>
                        <button type="button" class="mr-issue-tab" data-cat="family">👶 परिवार व मित्र</button>
                    </div>
                </div>
                <div class="mr-issue-search-wrap">
                    <span class="mr-search-icon">🔍</span>
                    <input type="text" id="mr-issue-search" placeholder="समस्या खोजें (उदा: Love, Job, कर्ज, Health...)" autocomplete="off">
                </div>
            </div>

            <!-- 20 Issues Grid (Mobile Non-Clipping Chips) -->
            <div class="mr-issue-grid" id="mr-issue-grid">
                <?php foreach ($issues as $iss): ?>
                    <button type="button" 
                            class="mr-issue-card" 
                            data-issue="<?php echo esc_attr($iss[''key'']); ?>"
                            data-cat="<?php echo esc_attr($iss[''cat'']); ?>"
                            data-slug="<?php echo esc_attr($iss[''slug'']); ?>">
                        <span class="mr-issue-icon"><?php echo $iss[''icon'']; ?></span>
                        <div class="mr-issue-info">
                            <span class="mr-issue-title-hi"><?php echo esc_html($iss[''title_hi'']); ?></span>
                            <span class="mr-issue-title-en"><?php echo esc_html($iss[''title_en'']); ?></span>
                        </div>
                        <span class="mr-issue-check">✓</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div class="mr-remedy-filter-bar" id="mr-filter-bar" style="display:none;">
            <div class="mr-filter-header">
                <span class="mr-filter-title">🎯 फ़िल्टर करें (Filter Remedies)</span>
                <span class="mr-active-issue-indicator" id="mr-active-issue-label">चयनित विषय: <strong>-</strong></span>
            </div>

            <div class="mr-filter-inputs">
                <!-- Sun Sign Selector -->
                <div class="mr-filter-group mr-filter-sign-group">
                    <label for="mr-filter-sign">♈ राशि (Sun Sign):</label>
                    <div class="mr-select-wrap">
                        <select id="mr-filter-sign">
                            <option value="">सभी 12 राशियां (All Signs)</option>
                            <option value="Aries">♈ मेष (Aries)</option>
                            <option value="Taurus">♉ वृषभ (Taurus)</option>
                            <option value="Gemini">♊ मिथुन (Gemini)</option>
                            <option value="Cancer">♋ कर्क (Cancer)</option>
                            <option value="Leo">♌ सिंह (Leo)</option>
                            <option value="Virgo">♍ कन्या (Virgo)</option>
                            <option value="Libra">♎ तुला (Libra)</option>
                            <option value="Scorpio">♏ वृश्चिक (Scorpio)</option>
                            <option value="Sagittarius">♐ धनु (Sagittarius)</option>
                            <option value="Capricorn">♑ मकर (Capricorn)</option>
                            <option value="Aquarius">♒ कुंभ (Aquarius)</option>
                            <option value="Pisces">♓ मीन (Pisces)</option>
                        </select>
                    </div>
                </div>

                <!-- Gender Pills -->
                <div class="mr-filter-group mr-filter-pills-group">
                    <label>लिंग (Gender):</label>
                    <div class="mr-pills-row" id="mr-filter-gender">
                        <button type="button" class="mr-pill-btn is-active" data-val="">सभी (All)</button>
                        <button type="button" class="mr-pill-btn" data-val="Boy">बालक/युवक (Boy)</button>
                        <button type="button" class="mr-pill-btn" data-val="Girl">बालिका/युवती (Girl)</button>
                    </div>
                </div>

                <!-- Language Pills -->
                <div class="mr-filter-group mr-filter-pills-group">
                    <label>भाषा (Language):</label>
                    <div class="mr-pills-row" id="mr-filter-lang">
                        <button type="button" class="mr-pill-btn is-active" data-val="">सभी (All)</button>
                        <button type="button" class="mr-pill-btn" data-val="Hindi">हिन्दी (Hindi)</button>
                        <button type="button" class="mr-pill-btn" data-val="English">English</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Results Container -->
        <div id="remedy-results" class="mr-remedy-results-container">
            <div class="mr-empty-placeholder">
                <span class="mr-placeholder-icon">🪐</span>
                <h3>कृपया ऊपर दी गई सूची से अपनी समस्या का चयन करें</h3>
                <p>ऊपर किसी भी विषय पर क्लिक करें (जैसे Love, Job Search, Health, Loans आदि) और अपनी राशि अनुसार संपूर्ण ज्योतिषीय समाधान देखें।</p>
            </div>
        </div>

        <!-- Master Reports Cross-Sell -->
        <?php
        if (function_exists(''kundali_display_cross_sell'')) {
            kundali_display_cross_sell();
        }
        ?>

        <!-- Mobile-First Slide-Up Drawer Modal for Remedy Details -->
        <div id="mr-remedy-modal" class="mr-modal-wrap" style="display:none;" aria-hidden="true">
            <div class="mr-modal-backdrop" id="mr-modal-backdrop"></div>
            <div class="mr-modal-sheet" role="dialog" aria-modal="true">
                <div class="mr-modal-drag-bar"></div>
                <div class="mr-modal-header">
                    <div class="mr-modal-header-info">
                        <span class="mr-modal-badge" id="mr-modal-badge">📜 संपूर्ण समाधान</span>
                        <h3 class="mr-modal-title" id="mr-modal-title">वैदिक ज्योतिषीय उपाय एवं समाधान</h3>
                    </div>
                    <button type="button" class="mr-modal-close-btn" id="mr-modal-close-x" aria-label="बंद करें">✕</button>
                </div>
                <div class="mr-modal-body" id="mr-modal-content">
                    <!-- Injected via AJAX -->
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

/* ====================================================
 * 4. AJAX: REMEDIES LIST (af_get_remedies)
 * ==================================================== */
add_action(''wp_ajax_get_remedies'', ''af_get_remedies'');
add_action(''wp_ajax_nopriv_get_remedies'', ''af_get_remedies'');

function af_get_remedies() {
    global $wpdb;

    $issue    = isset($_POST[''issue'']) ? sanitize_text_field($_POST[''issue'']) : '''';
    $sun_sign = isset($_POST[''sun_sign'']) ? sanitize_text_field($_POST[''sun_sign'']) : '''';
    $gender   = isset($_POST[''gender'']) ? sanitize_text_field($_POST[''gender'']) : '''';
    $language = isset($_POST[''language'']) ? sanitize_text_field($_POST[''language'']) : '''';
    $remedyId = isset($_POST[''remedy_id'']) ? intval($_POST[''remedy_id'']) : 0;

    if (!$issue) {
        wp_send_json_error([''msg'' => ''कृपया किसी एक समस्या का चयन करें (No issue selected)'']);
    }

    // Map Hindi sign names if query is passed in English
    $sign_map_en_hi = [
        ''Aries'' => ''मेष'', ''Taurus'' => ''वृषभ'', ''Gemini'' => ''मिथुन'', ''Cancer'' => ''कर्क'',
        ''Leo'' => ''सिंह'', ''Virgo'' => ''कन्या'', ''Libra'' => ''तुला'', ''Scorpio'' => ''वृश्चिक'',
        ''Sagittarius'' => ''धनु'', ''Capricorn'' => ''मकर'', ''Aquarius'' => ''कुंभ'', ''Pisces'' => ''मीन''
    ];

    $where  = ["issue = %s"];
    $params = [$issue];

    if ($sun_sign) {
        if (isset($sign_map_en_hi[$sun_sign])) {
            $where[] = "(sun_sign = %s OR sun_sign = %s)";
            $params[] = $sun_sign;
            $params[] = $sign_map_en_hi[$sun_sign];
        } else {
            $where[] = "sun_sign = %s";
            $params[] = $sun_sign;
        }
    }

    if ($gender) {
        $where[]  = "gender = %s";
        $params[] = $gender;
    }

    if ($language) {
        $where[]  = "language = %s";
        $params[] = $language;
    }

    $sql = "SELECT remedy_id, sun_sign, gender, issue, language, slug, ruling_planet, 
                   suggested_gemstone, suggested_pooja, summary 
            FROM af_remedies 
            WHERE " . implode(" AND ", $where) . " 
            ORDER BY remedy_id ASC";

    $results = $wpdb->get_results($wpdb->prepare($sql, $params));

    ob_start();

    if ($results) {
        echo "<div class=''mr-cards-count-bar''>";
        echo "<span>कुल उपलब्ध उपाय: <strong>" . count($results) . "</strong> (" . esc_html($issue) . ")</span>";
        echo "</div>";

        echo "<div class=''mr-remedy-cards-grid''>";
        foreach ($results as $row) {
            $is_hi = ($row->language === ''Hindi'');
            $zodiac_icons = [
                ''Aries'' => ''♈'', ''मेष'' => ''♈'', ''Taurus'' => ''♉'', ''वृषभ'' => ''♉'',
                ''Gemini'' => ''♊'', ''मिथुन'' => ''♊'', ''Cancer'' => ''♋'', ''कर्क'' => ''♋'',
                ''Leo'' => ''♌'', ''सिंह'' => ''♌'', ''Virgo'' => ''♍'', ''कन्या'' => ''♍'',
                ''Libra'' => ''♎'', ''तुला'' => ''♎'', ''Scorpio'' => ''♏'', ''वृश्चिक'' => ''♏'',
                ''Sagittarius'' => ''♐'', ''धनु'' => ''♐'', ''Capricorn'' => ''♑'', ''मकर'' => ''♑'',
                ''Aquarius'' => ''♒'', ''कुंभ'' => ''♒'', ''Pisces'' => ''♓'', ''मीन'' => ''♓''
            ];
            $icon = $zodiac_icons[$row->sun_sign] ?? ''🪐'';
            $gender_label = ($row->gender === ''Boy'') ? ($is_hi ? ''बालक/युवक'' : ''Boy'') : ($is_hi ? ''बालिका/युवती'' : ''Girl'');
            $ruler = !empty($row->ruling_planet) ? $row->ruling_planet : ($is_hi ? ''राशि स्वामी'' : ''Ruler'');

            echo "<div class=''mr-remedy-card'' id=''remedy-card-{$row->remedy_id}'' data-id=''{$row->remedy_id}''>";
            
            // Card Top Badges
            echo "<div class=''mr-card-top''>";
            echo "  <div class=''mr-card-sign-badge''>";
            echo "    <span class=''mr-z-glyph''>{$icon}</span>";
            echo "    <strong>{$row->sun_sign}</strong>";
            echo "  </div>";
            echo "  <div class=''mr-card-meta-tags''>";
            echo "    <span class=''mr-tag mr-tag-gender''>{$gender_label}</span>";
            echo "    <span class=''mr-tag mr-tag-lang''>{$row->language}</span>";
            echo "  </div>";
            echo "</div>";

            // Planet Ruler & Issue Title
            echo "<div class=''mr-card-ruler-row''>";
            echo "  <span class=''mr-ruler-pill''>🪐 " . ($is_hi ? "स्वामी: " : "Ruler: ") . "<strong>" . esc_html($ruler) . "</strong></span>";
            echo "  <span class=''mr-issue-tag''>" . esc_html($row->issue) . "</span>";
            echo "</div>";

            // Gemstone & Pooja Quick Highlights
            echo "<div class=''mr-card-highlights''>";
            if (!empty($row->suggested_gemstone)) {
                echo "<div class=''mr-hl-item''><span class=''mr-hl-icon''>💎</span> <span class=''mr-hl-label''>" . ($is_hi ? "रत्न:" : "Gem:") . "</span> <strong>" . esc_html($row->suggested_gemstone) . "</strong></div>";
            }
            if (!empty($row->suggested_pooja)) {
                echo "<div class=''mr-hl-item''><span class=''mr-hl-icon''>🪔</span> <span class=''mr-hl-label''>" . ($is_hi ? "पूजा:" : "Pooja:") . "</span> <strong>" . esc_html($row->suggested_pooja) . "</strong></div>";
            }
            echo "</div>";

            // Summary 3-Point Micro-List
            if (!empty($row->summary)) {
                echo mr_format_card_summary($row->summary);
            }

            // Action Button (Triggers Drawer Modal)
            echo "<div class=''mr-card-footer''>";
            echo "  <button type=''button'' class=''mr-view-detail-btn view-detail'' data-id=''{$row->remedy_id}''>";
            echo "    <span>" . ($is_hi ? "⚡ अचूक उपाय एवं समाधान देखें" : "⚡ View Detailed Remedies") . "</span>";
            echo "    <span class=''mr-btn-arrow''>&rarr;</span>";
            echo "  </button>";
            echo "</div>";

            echo "</div>"; // .mr-remedy-card
        }
        echo "</div>"; // .mr-remedy-cards-grid
    } else {
        echo "<div class=''mr-no-results-box''>";
        echo "  <span class=''mr-no-res-icon''>🔍</span>";
        echo "  <h3>इस चयन के लिए कोई उपाय उपलब्ध नहीं है</h3>";
        echo "  <p>कृपया फ़िल्टर में ''All'' चुनें या किसी अन्य राशि/लिंग का चयन करें।</p>";
        echo "</div>";
    }

    $html = ob_get_clean();
    wp_send_json_success([''html'' => $html, ''remedy_id'' => $remedyId]);
}

/* ====================================================
 * 5. AJAX: SINGLE REMEDY DETAIL (af_get_remedy_detail)
 * ==================================================== */
add_action(''wp_ajax_get_remedy_detail'', ''af_get_remedy_detail'');
add_action(''wp_ajax_nopriv_get_remedy_detail'', ''af_get_remedy_detail'');

function af_get_remedy_detail() {
    global $wpdb;

    $id = isset($_POST[''id'']) ? intval($_POST[''id'']) : 0;
    if (!$id) wp_send_json_error([''msg'' => ''अमान्य उपाय आईडी (Invalid remedy ID)'']);

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_remedies WHERE remedy_id = %d", $id));
    if (!$row) wp_send_json_error([''msg'' => ''उपाय विवरण प्राप्त नहीं हुआ (Not found)'']);

    $is_hi = ($row->language === ''Hindi'');
    $gender_label = ($row->gender === ''Boy'') ? ($is_hi ? ''बालक/युवक'' : ''Boy'') : ($is_hi ? ''बालिका/युवती'' : ''Girl'');

    // Issue translation mapping for modal badge
    $issue_titles_hi = [
        ''Job Search'' => ''नई नौकरी व रोजगार'',
        ''Job Promotion'' => ''पदोन्नति व तरक्की'',
        ''Business Loss or Slow Down'' => ''व्यापार में मंदी व हानि'',
        ''Relations At Work'' => ''कार्यक्षेत्र में संबंध'',
        ''Love'' => ''प्रेम संबंध व आकर्षण'',
        ''Marriage Issues'' => ''वैवाहिक जीवन में कलह'',
        ''Domestic Issues'' => ''गृह क्लेश व अशांति'',
        ''Savings'' => ''धन संचय व बचत'',
        ''Loans'' => ''कर्ज व देनदारी'',
        ''Home Buying'' => ''मकान व भूमि क्रय'',
        ''Property Disputes'' => ''संपत्ति विवाद'',
        ''Law Cases'' => ''कोर्ट केस व विवाद'',
        ''Health'' => ''स्वास्थ्य व रोग'',
        ''Parents Health'' => ''माता-पिता का स्वास्थ्य'',
        ''Evil Eye'' => ''नजर दोष व नकारात्मक ऊर्जा'',
        ''Studies'' => ''पढ़ाई व एकाग्रता'',
        ''Study Abroad'' => ''विदेश में उच्च शिक्षा व वीज़ा'',
        ''Children Issues'' => ''संतान संबंधी चिंताएं'',
        ''Sibling Relations'' => ''भाई-बहनों से अनबन'',
        ''Friendship Issues'' => ''मित्रता में मतभेद''
    ];
    $display_issue = ($is_hi && isset($issue_titles_hi[$row->issue])) ? $issue_titles_hi[$row->issue] : $row->issue;

    // Parse Checklists & FAQs
    $checklist = [];
    if (!empty($row->action_checklist_json)) {
        $checklist = json_decode($row->action_checklist_json, true) ?: [];
    }

    $faqs = [];
    if (!empty($row->faq_json)) {
        $faqs = json_decode($row->faq_json, true) ?: [];
    }

    ob_start();
    ?>
    <div class="mr-detail-modal-inner">
        <!-- 0. Header Meta Bar -->
        <div class="mr-modal-meta-bar">
            <span class="mr-modal-pill mr-mp-sign">♈ <strong><?php echo esc_html($row->sun_sign); ?></strong></span>
            <span class="mr-modal-pill mr-mp-ruler">🪐 <?php echo $is_hi ? "स्वामी:" : "Ruler:"; ?> <strong><?php echo esc_html($row->ruling_planet); ?></strong></span>
            <span class="mr-modal-pill mr-mp-gender">👤 <?php echo esc_html($gender_label); ?></span>
            <span class="mr-modal-pill mr-mp-issue">🎯 <?php echo esc_html($display_issue); ?></span>
        </div>

        <!-- 1. Quick Answer Highlight Box -->
        <?php if (!empty($row->quick_answer)): ?>
            <div class="mr-quick-answer-card">
                <div class="mr-qa-header">
                    <span class="mr-qa-icon">🌟</span>
                    <strong><?php echo $is_hi ? "त्वरित समाधान एवं मुख्य सूत्र (Quick Strategy)" : "Astrological Root Cause & Key Strategy"; ?></strong>
                </div>
                <div class="mr-qa-body">
                    <?php echo mr_format_remedy_text($row->quick_answer); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. Gemstone & Pooja Overview Cards -->
        <div class="mr-prescribed-grid">
            <?php if (!empty($row->suggested_gemstone)): ?>
                <div class="mr-prescribed-card mr-gem-card">
                    <span class="mr-p-icon">💎</span>
                    <div class="mr-p-content">
                        <span class="mr-p-label"><?php echo $is_hi ? "अनुशंसित शुभ रत्न" : "Suggested Gemstone"; ?></span>
                        <div class="mr-p-val"><?php echo esc_html($row->suggested_gemstone); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($row->suggested_pooja)): ?>
                <div class="mr-prescribed-card mr-pooja-card">
                    <span class="mr-p-icon">🪔</span>
                    <div class="mr-p-content">
                        <span class="mr-p-label"><?php echo $is_hi ? "अनुशंसित पूजा विधान" : "Prescribed Temple Pooja"; ?></span>
                        <div class="mr-p-val"><?php echo esc_html($row->suggested_pooja); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Astrological Remedies -->
        <?php if (!empty($row->astrological_remedies)): ?>
            <div class="mr-detail-section mr-section-astro">
                <h4 class="mr-sec-title">
                    <span class="mr-sec-icon">🪐</span>
                    <?php echo $is_hi ? "वैदिक ज्योतिषीय उपाय एवं अनुष्ठान (Astrological Rituals)" : "Vedic Astrological Remedies & Rituals"; ?>
                </h4>
                <div class="mr-sec-content">
                    <?php echo mr_format_remedy_text($row->astrological_remedies); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. Social & Practical Remedies -->
        <?php if (!empty($row->social_remedies)): ?>
            <div class="mr-detail-section mr-section-social">
                <h4 class="mr-sec-title">
                    <span class="mr-sec-icon">🤝</span>
                    <?php echo $is_hi ? "सामाजिक व व्यावहारिक समाधान (Practical Adjustments)" : "Social & Practical Lifestyle Adjustments"; ?>
                </h4>
                <div class="mr-sec-content">
                    <?php echo mr_format_remedy_text($row->social_remedies); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 5. Emotional & Psychological Remedies -->
        <?php if (!empty($row->emotional_remedies)): ?>
            <div class="mr-detail-section mr-section-emotional">
                <h4 class="mr-sec-title">
                    <span class="mr-sec-icon">🧘</span>
                    <?php echo $is_hi ? "भावनात्मक व मानसिक शांति (Emotional Balance)" : "Emotional Grounding & Psychological Balance"; ?>
                </h4>
                <div class="mr-sec-content">
                    <?php echo mr_format_remedy_text($row->emotional_remedies); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 6. Action Checklist -->
        <?php if (!empty($checklist) && is_array($checklist)): ?>
            <div class="mr-detail-section mr-section-checklist">
                <h4 class="mr-sec-title">
                    <span class="mr-sec-icon">📋</span>
                    <?php echo $is_hi ? "दैनिक कार्यसूची व क्रियान्वयन (Action Checklist)" : "Immediate Action Checklist"; ?>
                </h4>
                <div class="mr-checklist-items">
                    <?php foreach ($checklist as $step): ?>
                        <div class="mr-chk-card">
                            <div class="mr-chk-num"><?php echo esc_html($step[''step''] ?? ''•''); ?></div>
                            <div class="mr-chk-body">
                                <div class="mr-chk-head">
                                    <strong class="mr-chk-title"><?php echo esc_html($step[''title''] ?? ''''); ?></strong>
                                    <div class="mr-chk-tags">
                                        <?php if (!empty($step[''timing''])): ?>
                                            <span class="mr-chk-timing">⏰ <?php echo esc_html($step[''timing'']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($step[''frequency''])): ?>
                                            <span class="mr-chk-freq">🔄 <?php echo esc_html($step[''frequency'']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="mr-chk-act"><?php echo esc_html($step[''action''] ?? ''''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 7. FAQs Accordion -->
        <?php if (!empty($faqs) && is_array($faqs)): ?>
            <div class="mr-detail-section mr-section-faq">
                <h4 class="mr-sec-title">
                    <span class="mr-sec-icon">❓</span>
                    <?php echo $is_hi ? "अक्सर पूछे जाने वाले प्रश्न (Frequently Asked Questions)" : "Frequently Asked Questions"; ?>
                </h4>
                <div class="mr-faq-accordion">
                    <?php foreach ($faqs as $idx => $f): ?>
                        <div class="mr-faq-item <?php echo ($idx === 0) ? ''is-open'' : ''''; ?>">
                            <button type="button" class="mr-faq-q-btn">
                                <span><?php echo esc_html($f[''question''] ?? ''''); ?></span>
                                <span class="mr-faq-toggle-icon"><?php echo ($idx === 0) ? ''−'' : ''+''; ?></span>
                            </button>
                            <div class="mr-faq-a-box" style="<?php echo ($idx === 0) ? ''display:block;'' : ''display:none;''; ?>">
                                <p><?php echo nl2br(esc_html($f[''answer''] ?? '''')); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 8. Personalized Kundali CTA Box -->
        <div class="mr-kundali-cta-box">
            <span class="mr-cta-sparkle">✨</span>
            <div class="mr-cta-text-wrap">
                <strong><?php echo $is_hi ? "अपनी व्यक्तिगत जन्मकुंडली का संपूर्ण विश्लेषण चाहते हैं?" : "Need a Personalized Birth Chart Analysis?"; ?></strong>
                <p><?php echo $is_hi ? "अपनी जन्म तिथि, समय व स्थान अनुसार सटीक ग्रह स्थिति, महादशा व विशिष्ट उपाय जानें।" : "Unlock precise planetary positions, active dasha periods, and custom remedies mapped to your birth data."; ?></p>
            </div>
            <a href="https://merarashifal.com/reports/janam-kundali-master-report/" target="_blank" class="mr-cta-kundali-btn">
                <?php echo $is_hi ? "मास्टर जन्म कुंडली रिपोर्ट देखें →" : "Get Master Kundali Report →"; ?>
            </a>
        </div>

        <!-- Bottom Close Action -->
        <div class="mr-detail-bottom-close">
            <button type="button" class="mr-bottom-close-btn" id="mr-modal-bottom-close">
                ✕ <?php echo $is_hi ? "विवरण बंद करें (Close Details)" : "Close Details"; ?>
            </button>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    $modal_title = $is_hi 
        ? "{$row->sun_sign} राशि के लिए वैदिक उपाय" 
        : "Vedic Remedies for {$row->sun_sign}";
    $modal_badge = $is_hi 
        ? "📜 संपूर्ण समाधान" 
        : "📜 Complete Vedic Remedies";

    wp_send_json_success([
        ''html'' => $html,
        ''title'' => $modal_title,
        ''badge'' => $modal_badge,
        ''is_hi'' => $is_hi
    ]);
}

/* ====================================================
 * 6. FRONTEND SCRIPTS & STYLES (FOOTER)
 * ==================================================== */
add_action(''wp_footer'', function () {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const engine = document.getElementById("mr-remedies-engine");
        if (!engine) return;

        const container = document.getElementById("remedy-results");
        const activeLabel = document.getElementById("mr-active-issue-label");
        const signSelect = document.getElementById("mr-filter-sign");
        const searchInput = document.getElementById("mr-issue-search");
        const catTabs = document.querySelectorAll(".mr-issue-tab");
        const issueCards = document.querySelectorAll(".mr-issue-card");
        const genderPills = document.querySelectorAll("#mr-filter-gender .mr-pill-btn");
        const langPills = document.querySelectorAll("#mr-filter-lang .mr-pill-btn");

        // Modal Drawer elements
        const modal = document.getElementById("mr-remedy-modal");
        const modalBackdrop = document.getElementById("mr-modal-backdrop");
        const modalCloseX = document.getElementById("mr-modal-close-x");
        const modalBody = document.getElementById("mr-modal-content");
        const modalTitle = document.getElementById("mr-modal-title");
        const modalBadge = document.getElementById("mr-modal-badge");

        // State
        let currentIssue = "";
        let currentSign = "";
        let currentGender = "";
        let currentLanguage = "";

        // Backward-compatible issue mapping
        const legacyIssueMap = {
            "love": "Love",
            "health": "Health",
            "jobsearch": "Job Search",
            "study": "Studies",
            "studyabroad": "Study Abroad",
            "jobpromotion": "Job Promotion"
        };

        // Modal Open / Close Logic
        function openRemedyModal(id, titleText="", badgeText="") {
            if (!modal || !modalBody) return;
            modal.style.display = "flex";
            document.body.classList.add("mr-modal-open");
            if (titleText && modalTitle) modalTitle.textContent = titleText;
            if (badgeText && modalBadge) modalBadge.textContent = badgeText;
            modalBody.innerHTML = 
                ''<div class="mr-detail-loader">'' +
                ''  <div class="mr-spin-ring"></div>'' +
                ''  <span>उपाय विवरण लोड हो रहा है...</span>'' +
                ''</div>'';

            updateUrl(currentIssue, currentSign, currentGender, currentLanguage, id);

            const postData = new URLSearchParams({
                action: "get_remedy_detail",
                id: id
            });

            fetch("<?php echo admin_url(''admin-ajax.php''); ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: postData.toString()
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    modalBody.innerHTML = resData.data.html;
                    if (resData.data.title && modalTitle) {
                        modalTitle.textContent = resData.data.title;
                    }
                    if (resData.data.badge && modalBadge) {
                        modalBadge.textContent = resData.data.badge;
                    }

                    // Bottom close button inside loaded body
                    const bClose = document.getElementById("mr-modal-bottom-close");
                    if (bClose) {
                        bClose.addEventListener("click", closeRemedyModal);
                    }

                    // FAQs accordion inside modal
                    modalBody.querySelectorAll(".mr-faq-q-btn").forEach(qBtn => {
                        qBtn.addEventListener("click", function() {
                            const item = qBtn.closest(".mr-faq-item");
                            const ans = item.querySelector(".mr-faq-a-box");
                            const icon = item.querySelector(".mr-faq-toggle-icon");
                            const isOpen = item.classList.contains("is-open");

                            if (isOpen) {
                                item.classList.remove("is-open");
                                ans.style.display = "none";
                                icon.textContent = "+";
                            } else {
                                item.classList.add("is-open");
                                ans.style.display = "block";
                                icon.textContent = "−";
                            }
                        });
                    });
                } else {
                    modalBody.innerHTML = ''<p class="mr-err-msg">विवरण लोड नहीं हो सका।</p>'';
                }
            })
            .catch(err => {
                modalBody.innerHTML = ''<p class="mr-err-msg">सर्वर से संपर्क करने में समस्या हुई।</p>'';
            });
        }

        function closeRemedyModal() {
            if (!modal) return;
            modal.style.display = "none";
            document.body.classList.remove("mr-modal-open");
            updateUrl(currentIssue, currentSign, currentGender, currentLanguage);
        }

        if (modalBackdrop) modalBackdrop.addEventListener("click", closeRemedyModal);
        if (modalCloseX) modalCloseX.addEventListener("click", closeRemedyModal);

        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape" && modal && modal.style.display !== "none") {
                closeRemedyModal();
            }
        });

        // URL Helpers
        function updateUrl(issue, sign='''', gender='''', lang='''', remedyId='''') {
            const url = new URL(window.location.href);
            if (issue) url.searchParams.set("issue", issue);
            if (sign) url.searchParams.set("sun_sign", sign); else url.searchParams.delete("sun_sign");
            if (gender) url.searchParams.set("gender", gender); else url.searchParams.delete("gender");
            if (lang) url.searchParams.set("language", lang); else url.searchParams.delete("language");
            if (remedyId) url.searchParams.set("remedy_id", remedyId); else url.searchParams.delete("remedy_id");
            window.history.pushState({}, "", url);
        }

        function setActiveIssue(issueName, autoScroll=false) {
            if (!issueName) return;
            currentIssue = issueName;
            issueCards.forEach(card => {
                if (card.dataset.issue.toLowerCase() === issueName.toLowerCase()) {
                    card.classList.add("is-selected");
                } else {
                    card.classList.remove("is-selected");
                }
            });

            const filterBar = document.getElementById("mr-filter-bar");
            if (filterBar) {
                filterBar.style.display = "block";
            }

            if (activeLabel) {
                activeLabel.innerHTML = ''चयनित विषय: <strong>'' + issueName + ''</strong>'';
            }

            if (autoScroll && filterBar) {
                filterBar.scrollIntoView({ behavior: "smooth", block: "start" });
            }

            loadRemedies(currentIssue, currentSign, currentGender, currentLanguage);
        }

        function loadRemedies(issue, sign='''', gender='''', lang='''', remedyId='''') {
            container.innerHTML = 
                ''<div class="mr-loading-box">'' +
                ''  <div class="mr-spin-ring"></div>'' +
                ''  <p><strong>'' + issue + ''</strong> के लिए ज्योतिषीय उपाय लोड हो रहे हैं...</p>'' +
                ''</div>'';

            const bodyParams = new URLSearchParams({
                action: "get_remedies",
                issue: issue,
                sun_sign: sign,
                gender: gender,
                language: lang,
                remedy_id: remedyId
            });

            fetch("<?php echo admin_url(''admin-ajax.php''); ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: bodyParams.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = data.data.html;
                    attachCardEvents();

                    if (remedyId) {
                        openRemedyModal(remedyId);
                    }
                } else {
                    container.innerHTML = ''<div class="mr-no-results-box"><p>'' + (data.data?.msg || ''त्रुटि हुई'') + ''</p></div>'';
                }
            })
            .catch(err => {
                container.innerHTML = ''<div class="mr-no-results-box"><p>डेटा लोड करने में असमर्थ। कृपया पुनः प्रयास करें।</p></div>'';
            });
        }

        function attachCardEvents() {
            document.querySelectorAll(".mr-view-detail-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const id = btn.dataset.id;
                    const card = document.getElementById("remedy-card-" + id);
                    const signName = card?.querySelector(".mr-card-sign-badge strong")?.textContent?.trim() || "";
                    const langBadge = card?.querySelector(".mr-tag-lang")?.textContent?.trim() || "";
                    const isHindiCard = (langBadge === "Hindi" || /[^\\u0000-\\u007F]/.test(signName));

                    const titleText = isHindiCard
                        ? (signName ? (signName + " राशि के लिए वैदिक उपाय") : "वैदिक ज्योतिषीय उपाय")
                        : (signName ? ("Vedic Remedies for " + signName) : "Vedic Astrology Remedies");
                    const badgeText = isHindiCard ? "📜 संपूर्ण समाधान" : "📜 Complete Vedic Remedies";

                    openRemedyModal(id, titleText, badgeText);
                });
            });
        }

        // Issue Card Click
        issueCards.forEach(card => {
            card.addEventListener("click", function() {
                const iss = card.dataset.issue;
                updateUrl(iss, currentSign, currentGender, currentLanguage);
                setActiveIssue(iss, true);
            });
        });

        // Category Tabs Click
        catTabs.forEach(tab => {
            tab.addEventListener("click", function() {
                catTabs.forEach(t => t.classList.remove("is-active"));
                tab.classList.add("is-active");
                const cat = tab.dataset.cat;

                issueCards.forEach(card => {
                    if (cat === "all" || card.dataset.cat === cat) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });

        // Search Input Filter
        if (searchInput) {
            searchInput.addEventListener("input", function() {
                const q = searchInput.value.toLowerCase().trim();
                issueCards.forEach(card => {
                    const hi = card.querySelector(".mr-issue-title-hi").textContent.toLowerCase();
                    const en = card.querySelector(".mr-issue-title-en").textContent.toLowerCase();
                    const key = card.dataset.issue.toLowerCase();

                    if (!q || hi.includes(q) || en.includes(q) || key.includes(q)) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        }

        // Sun Sign Filter
        if (signSelect) {
            signSelect.addEventListener("change", function() {
                currentSign = signSelect.value;
                if (currentIssue) {
                    updateUrl(currentIssue, currentSign, currentGender, currentLanguage);
                    loadRemedies(currentIssue, currentSign, currentGender, currentLanguage);
                }
            });
        }

        // Gender Pills
        genderPills.forEach(p => {
            p.addEventListener("click", function() {
                genderPills.forEach(x => x.classList.remove("is-active"));
                p.classList.add("is-active");
                currentGender = p.dataset.val;
                if (currentIssue) {
                    updateUrl(currentIssue, currentSign, currentGender, currentLanguage);
                    loadRemedies(currentIssue, currentSign, currentGender, currentLanguage);
                }
            });
        });

        // Language Pills
        langPills.forEach(p => {
            p.addEventListener("click", function() {
                langPills.forEach(x => x.classList.remove("is-active"));
                p.classList.add("is-active");
                currentLanguage = p.dataset.val;
                if (currentIssue) {
                    updateUrl(currentIssue, currentSign, currentGender, currentLanguage);
                    loadRemedies(currentIssue, currentSign, currentGender, currentLanguage);
                }
            });
        });

        // Initial Load from URL params
        const urlParams = new URLSearchParams(window.location.search);
        let initIssue = urlParams.get("issue") || "";
        if (initIssue && legacyIssueMap[initIssue.toLowerCase()]) {
            initIssue = legacyIssueMap[initIssue.toLowerCase()];
        }

        let initSign  = urlParams.get("sun_sign") || "";
        let initGen   = urlParams.get("gender") || "";
        let initLang  = urlParams.get("language") || "";
        let initRemId = urlParams.get("remedy_id") || "";

        if (initSign && signSelect) signSelect.value = initSign;
        currentSign = initSign;

        if (initGen) {
            genderPills.forEach(p => {
                if (p.dataset.val === initGen) {
                    genderPills.forEach(x => x.classList.remove("is-active"));
                    p.classList.add("is-active");
                }
            });
            currentGender = initGen;
        }

        if (initLang) {
            langPills.forEach(p => {
                if (p.dataset.val === initLang) {
                    langPills.forEach(x => x.classList.remove("is-active"));
                    p.classList.add("is-active");
                }
            });
            currentLanguage = initLang;
        }

        if (initIssue) {
            setActiveIssue(initIssue, false);
            if (initRemId) {
                openRemedyModal(initRemId);
            }
        } else {
            const filterBar = document.getElementById("mr-filter-bar");
            if (filterBar) {
                filterBar.style.display = "none";
            }
        }
    });
    </script>

    <style>
    /* ====================================================
     * VEDIC REMEDIES ENGINE STYLES (MeraRashifal Mobile-First Design)
     * ==================================================== */

    /* Hide legacy Elementor 6 buttons row */
    .elementor-element-87f0c4b, 
    .elementor-element[data-id="87f0c4b"],
    .elementor-element-861093f, 
    [data-id="861093f"] {
        display: none !important;
    }

    /* Body lock when modal is open */
    body.mr-modal-open {
        overflow: hidden !important;
    }

    .mr-remedy-wrap {
        background-color: #FFFBEB !important;
        font-family: ''Noto Sans'', ''Noto Sans Devanagari'', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        color: #1E293B !important;
        border-radius: 16px !important;
        border: 1px solid #FDE68A !important;
        padding: 24px 20px !important;
        box-shadow: 0 4px 20px rgba(180, 83, 9, 0.05) !important;
        margin: 20px auto 35px auto !important;
        max-width: 1100px !important;
        box-sizing: border-box !important;
        line-height: 1.5;
    }
    @media (max-width: 768px) {
        .mr-remedy-wrap {
            padding: 16px 12px !important;
            border-radius: 12px !important;
            margin: 10px auto 25px auto !important;
        }
    }

    /* Header */
    .mr-remedy-header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px dashed #FCD34D;
    }
    .mr-remedy-badge {
        display: inline-block;
        background: #FEF3C7;
        color: #B45309;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 50px;
        border: 1px solid #FDE68A;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
    }
    .mr-remedy-main-title {
        font-size: 22px !important;
        font-weight: 800 !important;
        color: #B45309 !important;
        margin: 0 0 8px 0 !important;
        letter-spacing: -0.3px;
        line-height: 1.3 !important;
    }
    @media (min-width: 768px) {
        .mr-remedy-main-title { font-size: 28px !important; }
    }
    .mr-remedy-subtitle {
        font-size: 13px;
        color: #78350F;
        margin: 0 auto;
        max-width: 780px;
        font-weight: 500;
        line-height: 1.5;
    }

    /* Issue Navigator Box */
    .mr-issue-nav-box {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 18px;
        box-shadow: 0 2px 10px rgba(180, 83, 9, 0.03);
    }
    .mr-issue-nav-top {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #FEF3C7;
    }
    @media (min-width: 768px) {
        .mr-issue-nav-top {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }

    /* Swipeable Category Tabs on Mobile */
    .mr-issue-tabs-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .mr-issue-tabs-wrap::-webkit-scrollbar { display: none; }
    .mr-issue-tabs {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        min-width: max-content;
    }
    .mr-issue-tab {
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        color: #78350F;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .mr-issue-tab:hover {
        background: #FEF3C7;
        border-color: #D97706;
    }
    .mr-issue-tab.is-active {
        background: linear-gradient(135deg, #B45309 0%, #D97706 100%);
        color: #FFFFFF;
        border-color: #B45309;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.25);
    }

    .mr-issue-search-wrap {
        position: relative;
        width: 100%;
    }
    @media (min-width: 768px) {
        .mr-issue-search-wrap {
            max-width: 280px;
            flex-shrink: 0;
        }
    }
    .mr-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        opacity: 0.6;
    }
    #mr-issue-search {
        width: 100%;
        padding: 7px 12px 7px 30px;
        font-size: 12.5px;
        border: 1px solid #FDE68A;
        border-radius: 50px;
        background: #FFFDF7;
        color: #1E293B;
        outline: none;
        box-sizing: border-box;
    }
    #mr-issue-search:focus {
        border-color: #D97706;
        box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.15);
    }

    /* 20 Issues Grid (Mobile Clean Chips without Truncation) */
    .mr-issue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 8px;
        max-height: 380px;
        overflow-y: auto;
        padding-right: 4px;
    }
    @media (max-width: 600px) {
        .mr-issue-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            max-height: 320px;
        }
    }
    .mr-issue-grid::-webkit-scrollbar { width: 5px; }
    .mr-issue-grid::-webkit-scrollbar-thumb { background: #FCD34D; border-radius: 4px; }

    .mr-issue-card {
        background: #FFFDF7;
        border: 1px solid #FDE68A;
        border-radius: 10px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        text-align: left;
        transition: all 0.18s ease;
        position: relative;
        min-height: 54px;
        box-sizing: border-box;
    }
    .mr-issue-card:hover {
        border-color: #D97706;
        background: #FFFFFF;
        box-shadow: 0 3px 8px rgba(180, 83, 9, 0.08);
        transform: translateY(-1px);
    }
    .mr-issue-card.is-selected {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        border-color: #D97706;
        box-shadow: 0 0 0 2px #D97706;
    }
    .mr-issue-icon {
        font-size: 20px;
        flex-shrink: 0;
        line-height: 1;
    }
    .mr-issue-info {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        overflow: hidden;
    }
    .mr-issue-title-hi {
        font-size: 11.5px;
        font-weight: 700;
        color: #78350F;
        line-height: 1.25;
        white-space: normal;
        word-break: break-word;
    }
    .mr-issue-title-en {
        font-size: 10px;
        color: #64748B;
        line-height: 1.2;
        margin-top: 2px;
        white-space: normal;
        word-break: break-word;
    }
    .mr-issue-check {
        display: none;
        color: #B45309;
        font-weight: 900;
        font-size: 13px;
        flex-shrink: 0;
    }
    .mr-issue-card.is-selected .mr-issue-check { display: inline-block; }

    /* Filter Bar */
    .mr-remedy-filter-bar {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 20px;
    }
    .mr-filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #FCD34D;
        font-size: 12.5px;
        flex-wrap: wrap;
        gap: 6px;
    }
    .mr-filter-title {
        font-weight: 700;
        color: #78350F;
    }
    .mr-active-issue-indicator {
        font-size: 12px;
        color: #B45309;
        background: #FEF3C7;
        padding: 2px 10px;
        border-radius: 50px;
        border: 1px solid #FDE68A;
    }
    .mr-filter-inputs {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    @media (max-width: 600px) {
        .mr-filter-inputs {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
    }
    .mr-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 150px;
    }
    .mr-filter-group label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .mr-select-wrap {
        position: relative;
        width: 100%;
    }
    #mr-filter-sign {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        background: #FFFDF7;
        font-size: 12.5px;
        color: #1E293B;
        cursor: pointer;
        outline: none;
        width: 100%;
        box-sizing: border-box;
        min-height: 38px;
    }
    #mr-filter-sign:focus { border-color: #D97706; }
    .mr-pills-row {
        display: flex;
        gap: 4px;
        width: 100%;
    }
    .mr-pill-btn {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        padding: 8px 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        flex: 1;
        text-align: center;
        white-space: nowrap;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    .mr-pill-btn:hover { background: #FEF3C7; border-color: #FDE68A; }
    .mr-pill-btn.is-active {
        background: #B45309;
        color: #FFFFFF;
        border-color: #B45309;
        box-shadow: 0 1px 4px rgba(180, 83, 9, 0.2);
    }

    /* Remedies Results Area */
    .mr-remedy-results-container {
        margin-bottom: 25px;
    }
    .mr-cards-count-bar {
        font-size: 12px;
        color: #78350F;
        margin-bottom: 12px;
        padding: 2px 4px;
    }
    .mr-remedy-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 14px;
    }
    @media (max-width: 600px) {
        .mr-remedy-cards-grid { grid-template-columns: 1fr; }
    }

    /* Individual Remedy Card */
    .mr-remedy-card {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(180, 83, 9, 0.03);
    }
    .mr-remedy-card:hover {
        border-color: #D97706;
        box-shadow: 0 6px 18px rgba(180, 83, 9, 0.08);
        transform: translateY(-2px);
    }
    .mr-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .mr-card-sign-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 15px;
        font-weight: 800;
        color: #B45309;
    }
    .mr-z-glyph { font-size: 18px; }
    .mr-card-meta-tags {
        display: flex;
        gap: 4px;
    }
    .mr-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .mr-tag-gender { background: #EEF2FF; color: #4338CA; }
    .mr-tag-lang { background: #F0FDF4; color: #15803D; }

    .mr-card-ruler-row {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .mr-ruler-pill {
        font-size: 11px;
        background: #FEF3C7;
        color: #78350F;
        padding: 2px 8px;
        border-radius: 50px;
        border: 1px solid #FDE68A;
    }
    .mr-issue-tag {
        font-size: 11px;
        background: #F1F5F9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 50px;
    }

    .mr-card-highlights {
        background: #FFFBEB;
        border: 1px dashed #FDE68A;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .mr-hl-item {
        font-size: 11.5px;
        color: #1E293B;
        line-height: 1.35;
    }
    .mr-hl-icon { font-size: 12px; }
    .mr-hl-label { color: #78350F; font-weight: 600; }

    /* 3-Point Card Summary Micro-List */
    .mr-card-summary-list {
        background: #F8FAFC;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 11.5px;
        color: #334155;
        line-height: 1.4;
    }
    .mr-cs-item {
        display: flex;
        gap: 4px;
        align-items: flex-start;
    }
    .mr-cs-label {
        color: #78350F;
        font-weight: 700;
        flex-shrink: 0;
    }

    .mr-card-footer {
        margin-top: auto;
    }
    .mr-view-detail-btn {
        width: 100%;
        background: linear-gradient(135deg, #B45309 0%, #D97706 100%);
        border: none;
        color: #FFFFFF !important;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.2);
    }
    .mr-view-detail-btn:hover {
        background: linear-gradient(135deg, #92400E 0%, #B45309 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(180, 83, 9, 0.3);
    }
    .mr-btn-arrow { font-size: 14px; transition: transform 0.2s ease; }
    .mr-view-detail-btn:hover .mr-btn-arrow { transform: translateX(3px); }

    /* ====================================================
     * MOBILE BOTTOM SHEET & MODAL STYLES
     * ==================================================== */
    .mr-modal-wrap {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        box-sizing: border-box;
    }
    @media (max-width: 768px) {
        .mr-modal-wrap {
            padding: 0;
            align-items: flex-end; /* Bottom sheet on mobile */
        }
    }
    .mr-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }
    .mr-modal-sheet {
        position: relative;
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 16px;
        width: 100%;
        max-width: 740px;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        z-index: 2;
        overflow: hidden;
        animation: mrModalFadeIn 0.25s ease-out;
    }
    @media (max-width: 768px) {
        .mr-modal-sheet {
            max-height: 92vh;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
            animation: mrSheetSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
    }
    @keyframes mrModalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes mrSheetSlideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }

    .mr-modal-drag-bar {
        width: 44px;
        height: 4px;
        background: #E2E8F0;
        border-radius: 3px;
        margin: 8px auto 2px auto;
        display: none;
    }
    @media (max-width: 768px) {
        .mr-modal-drag-bar { display: block; }
    }

    .mr-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 18px;
        border-bottom: 1px solid #FEF3C7;
        background: #FFFBEB;
    }
    .mr-modal-badge {
        font-size: 10.5px;
        font-weight: 700;
        color: #B45309;
        display: block;
        margin-bottom: 2px;
    }
    .mr-modal-title {
        font-size: 16px !important;
        font-weight: 800 !important;
        color: #78350F !important;
        margin: 0 !important;
        line-height: 1.3 !important;
    }
    .mr-modal-close-btn {
        background: #FEF3C7;
        border: 1px solid #FDE68A;
        color: #78350F;
        font-size: 14px;
        font-weight: 800;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .mr-modal-close-btn:hover {
        background: #FDE68A;
        color: #B45309;
    }

    .mr-modal-body {
        padding: 16px 18px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        flex-grow: 1;
    }
    @media (max-width: 600px) {
        .mr-modal-body { padding: 12px 14px; }
    }

    /* Modal Content Elements */
    .mr-modal-meta-bar {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .mr-modal-pill {
        font-size: 11px;
        padding: 3px 9px;
        border-radius: 50px;
        border: 1px solid #FDE68A;
        background: #FFFDF7;
        color: #78350F;
    }
    .mr-mp-sign { background: #FEF3C7; font-weight: 700; }

    /* Quick Answer */
    .mr-quick-answer-card {
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-left: 4px solid #D97706;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 14px;
    }
    .mr-qa-header {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #78350F;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px dashed #FDE68A;
    }
    .mr-qa-body {
        font-size: 12px;
        color: #1E293B;
        line-height: 1.5;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    /* Line Item formatting */
    .mr-line-item {
        line-height: 1.5;
    }
    .mr-line-label {
        color: #78350F;
        font-weight: 700;
    }

    /* Prescribed Grid */
    .mr-prescribed-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 14px;
    }
    @media (max-width: 500px) {
        .mr-prescribed-grid { grid-template-columns: 1fr; }
    }
    .mr-prescribed-card {
        background: #FFFDF7;
        border: 1px solid #FDE68A;
        border-radius: 8px;
        padding: 10px 12px;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .mr-p-icon { font-size: 20px; flex-shrink: 0; }
    .mr-p-label { font-size: 10.5px; color: #64748B; display: block; margin-bottom: 1px; }
    .mr-p-val { font-size: 12px; font-weight: 700; color: #B45309; }

    /* Sections */
    .mr-detail-section {
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .mr-sec-title {
        font-size: 13px !important;
        font-weight: 800 !important;
        color: #78350F !important;
        margin: 0 0 8px 0 !important;
        display: flex;
        align-items: center;
        gap: 6px;
        padding-bottom: 6px;
        border-bottom: 1px dashed #FEF3C7;
    }
    .mr-sec-content {
        font-size: 12px;
        color: #334155;
        line-height: 1.55;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Checklist */
    .mr-checklist-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .mr-chk-card {
        background: #FFFDF7;
        border: 1px solid #FEF3C7;
        border-radius: 6px;
        padding: 8px 10px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .mr-chk-num {
        background: #B45309;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 800;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .mr-chk-body { flex-grow: 1; }
    .mr-chk-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 3px;
    }
    .mr-chk-title { font-size: 12px; color: #78350F; }
    .mr-chk-tags { display: flex; gap: 4px; font-size: 10px; }
    .mr-chk-timing { background: #FEF3C7; color: #92400E; padding: 1px 6px; border-radius: 3px; }
    .mr-chk-freq { background: #E0E7FF; color: #3730A3; padding: 1px 6px; border-radius: 3px; }
    .mr-chk-act { font-size: 11.5px; color: #475569; margin: 0; line-height: 1.45; }

    /* FAQs */
    .mr-faq-accordion {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .mr-faq-item {
        border: 1px solid #FEF3C7;
        border-radius: 6px;
        overflow: hidden;
    }
    .mr-faq-q-btn {
        width: 100%;
        background: #FFFDF7;
        border: none;
        padding: 8px 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #78350F;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .mr-faq-q-btn:hover { background: #FEF3C7; }
    .mr-faq-a-box {
        padding: 8px 12px 10px 12px;
        background: #FFFFFF;
        border-top: 1px dashed #FDE68A;
        font-size: 11.5px;
        color: #334155;
        line-height: 1.5;
    }
    .mr-faq-a-box p { margin: 0; }

    /* Kundali CTA Box inside modal */
    .mr-kundali-cta-box {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        border: 1px solid #D97706;
        border-radius: 10px;
        padding: 12px 14px;
        margin: 16px 0 12px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .mr-cta-sparkle { font-size: 24px; flex-shrink: 0; }
    .mr-cta-text-wrap {
        flex-grow: 1;
        min-width: 200px;
    }
    .mr-cta-text-wrap strong {
        font-size: 12.5px;
        color: #78350F;
        display: block;
        margin-bottom: 2px;
    }
    .mr-cta-text-wrap p {
        font-size: 11px;
        color: #92400E;
        margin: 0;
        line-height: 1.35;
    }
    .mr-cta-kundali-btn {
        background: #B45309;
        color: #FFFFFF !important;
        font-size: 11.5px;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 6px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: background 0.15s ease;
    }
    .mr-cta-kundali-btn:hover {
        background: #78350F;
    }

    .mr-detail-bottom-close {
        text-align: center;
        margin: 14px 0 6px 0;
    }
    .mr-bottom-close-btn {
        background: #FEF3C7;
        border: 1px solid #FDE68A;
        color: #78350F;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 20px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .mr-bottom-close-btn:hover {
        background: #FDE68A;
        color: #B45309;
    }

    /* Loaders & Empty States */
    .mr-empty-placeholder, .mr-no-results-box {
        text-align: center;
        background: #FFFFFF;
        border: 1px dashed #FDE68A;
        border-radius: 12px;
        padding: 28px 16px;
    }
    .mr-placeholder-icon, .mr-no-res-icon {
        font-size: 32px;
        display: block;
        margin-bottom: 8px;
    }
    .mr-empty-placeholder h3, .mr-no-results-box h3 {
        font-size: 15px;
        color: #78350F;
        margin: 0 0 6px 0;
    }
    .mr-empty-placeholder p, .mr-no-results-box p {
        font-size: 12px;
        color: #64748B;
        max-width: 500px;
        margin: 0 auto;
    }
    .mr-loading-box, .mr-detail-loader {
        text-align: center;
        padding: 25px 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #78350F;
        font-size: 12.5px;
    }
    .mr-spin-ring {
        width: 26px;
        height: 26px;
        border: 3px solid rgba(217, 119, 6, 0.2);
        border-top-color: #B45309;
        border-right-color: #D97706;
        border-radius: 50%;
        animation: mrSpinRing 0.7s linear infinite;
    }
    @keyframes mrSpinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    
    /* ====================================================
     * RECOMMENDED MASTER REPORTS CROSS-SELL (Same as Kundali Engine)
     * ==================================================== */
    .mr-ku-crosssell-wrap {
        margin: 24px 0 16px 0;
        padding: 16px;
        background: #FFFFFF;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
        box-sizing: border-box;
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
        box-sizing: border-box;
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
        box-sizing: border-box;
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
        padding: 1px 5px;
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
        padding: 1px 6px;
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
    @media (max-width: 768px) {
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
            font-size: 13px;
            margin-bottom: 4px;
            line-height: 1.35;
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
});
', `active` = 1 WHERE `id` = 33;
