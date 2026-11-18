<?php
/**
 * MeraRashifal Baby Name Engine: 11-Section Master Profile Template Renderer
 * Class: MR_Baby_Template_Renderer
 *
 * Implements the comprehensive 800–1,200 word blueprint designed to eliminate
 * thin-content indexing penalties while providing rich Vedic astrology,
 * Chaldean numerology, psychological narratives, and JSON-LD schema.
 * Full bilingual support (English & Hindi) toggled smoothly without page reloads.
 *
 * @package MeraRashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Baby_Template_Renderer
{
    /**
     * Initializes shortcode filtering and content overriding to guarantee seamless rendering on live server
     * across Elementor widgets, Code Snippets database, and standard WordPress templates.
     */
    public static function init()
    {
        // 1. Direct shortcode handler interception (executes inside Elementor and standard WP)
        add_action('wp_loaded', [__CLASS__, 'intercept_shortcode_handler'], 999);
        add_action('init', [__CLASS__, 'intercept_shortcode_handler'], 999);

        // 2. Shortcode output filter
        add_filter('do_shortcode_tag', [__CLASS__, 'filter_shortcode_output'], 100, 4);

        // 3. Post content filter
        add_filter('the_content', [__CLASS__, 'filter_page_content'], 9999);
    }

    /**
     * Directly intercepts the registered baby_name_engine shortcode callback in global $shortcode_tags
     */
    public static function intercept_shortcode_handler()
    {
        global $shortcode_tags;
        if (isset($shortcode_tags['baby_name_engine']) && empty($shortcode_tags['baby_name_engine_wrapped'])) {
            $original_cb = $shortcode_tags['baby_name_engine'];
            $shortcode_tags['baby_name_engine_wrapped'] = true;

            $shortcode_tags['baby_name_engine'] = function($atts = [], $content = null, $tag = 'baby_name_engine') use ($original_cb) {
                $page      = get_query_var('baby_page');
                $name_slug = get_query_var('baby_name');
                $gender    = get_query_var('gender');
                $char      = get_query_var('alphabet');
                $religion  = get_query_var('religion') ?: ($_GET['religion'] ?? 'Hindu');

                // Direct URI fallback if query vars not yet initialized
                if (empty($name_slug)) {
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    if (preg_match('#/baby-names/([a-z])/(boy|girl)/([^/?#]+)#i', $uri, $matches)) {
                        $char      = $matches[1];
                        $gender    = $matches[2];
                        $name_slug = $matches[3];
                        $page      = 'name_detail';
                    }
                }

                if ($page === 'name_detail' && !empty($name_slug)) {
                    $gender = !empty($gender) ? $gender : 'boy';
                    $char   = !empty($char) ? $char : substr($name_slug, 0, 1);
                    return self::render_detail($name_slug, $gender, $char, $religion);
                }

                if (is_callable($original_cb)) {
                    return call_user_func($original_cb, $atts, $content, $tag);
                }
                return '';
            };
        }
    }

    /**
     * Intercepts [baby_name_engine] shortcode on name_detail requests
     */
    public static function filter_shortcode_output($output, $tag, $attr, $m)
    {
        if (trim($tag) === 'baby_name_engine') {
            $page      = get_query_var('baby_page');
            $name_slug = get_query_var('baby_name');

            if (empty($name_slug)) {
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (preg_match('#/baby-names/([a-z])/(boy|girl)/([^/?#]+)#i', $uri, $matches)) {
                    $name_slug = $matches[3];
                    $page      = 'name_detail';
                }
            }

            if ($page === 'name_detail' && !empty($name_slug)) {
                // If output already has modern template, don't duplicate
                if (strpos($output, 'mr-baby-detail-wrap') !== false) {
                    return $output;
                }
                $gender   = get_query_var('gender') ?: 'boy';
                $char     = get_query_var('alphabet') ?: substr($name_slug, 0, 1);
                $religion = get_query_var('religion') ?: ($_GET['religion'] ?? 'Hindu');
                return self::render_detail($name_slug, $gender, $char, $religion);
            }
        }
        return $output;
    }

    /**
     * Intercepts the post content at highest priority to guarantee modern 11-section template replacement
     */
    public static function filter_page_content($content)
    {
        $page      = get_query_var('baby_page');
        $name_slug = get_query_var('baby_name');

        if (empty($name_slug)) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/baby-names/([a-z])/(boy|girl)/([^/?#]+)#i', $uri, $matches)) {
                $name_slug = $matches[3];
                $page      = 'name_detail';
            }
        }

        if ($page === 'name_detail' && !empty($name_slug)) {
            if (strpos($content, 'mr-baby-detail-wrap') !== false) {
                return $content;
            }
            $gender   = get_query_var('gender') ?: 'boy';
            $char     = get_query_var('alphabet') ?: substr($name_slug, 0, 1);
            $religion = get_query_var('religion') ?: ($_GET['religion'] ?? 'Hindu');

            $new_markup = self::render_detail($name_slug, $gender, $char, $religion);

            // Replace old banner card and wrapper with support for unquoted / minified attributes
            if (strpos($content, 'name-detail-banner-card') !== false) {
                $pattern = '/<div[^>]*class=["\']?baby-detail-wrapper["\']?[^>]*>.*?<\/div>\s*<\/div>\s*(?:<div[^>]*class=["\']?similar-names-section["\']?[^>]*>.*?<\/div>)?/s';
                $replaced = preg_replace($pattern, $new_markup, $content, 1);
                if (!empty($replaced) && $replaced !== $content) {
                    return $replaced;
                }
            }

            if (strpos($content, '[baby_name_engine]') !== false) {
                return str_replace('[baby_name_engine]', $new_markup, $content);
            }
        }
        return $content;
    }

    /**
     * Renders the complete 11-section master baby name detail page
     *
     * @param string $name_slug Name slug or raw name
     * @param string $gender 'boy' or 'girl'
     * @param string $letter Alphabet character (e.g. 'T')
     * @param string $religion 'Hindu', 'Muslim', 'Christian', etc.
     * @return string Complete HTML markup
     */
    public static function render_detail($name_slug, $gender = 'boy', $letter = '', $religion = 'Hindu')
    {
        $gender_clean   = !empty($gender) ? ucfirst(strtolower($gender)) : 'Boy';
        $religion_clean = !empty($religion) ? ucfirst(strtolower($religion)) : 'Hindu';
        $name_clean     = ucfirst(strtolower(trim($name_slug)));

        if (empty($letter)) {
            $letter = strtoupper(substr($name_clean, 0, 1));
        }

        // Fetch or AI-enrich master profile via Gemini Service (<50ms on cache hit)
        $profile = MR_Baby_Gemini_Service::instance()->get_or_generate_profile($name_clean, $gender_clean, $religion_clean);

        if (empty($profile)) {
            return '<div class="mr-baby-error"><p class="lang-en">Unable to load astrological profile for this name at this moment. Please try again shortly.</p><p class="lang-hi">इस समय इस नाम का ज्योतिषीय विवरण लोड करने में असमर्थ। कृपया कुछ समय बाद पुनः प्रयास करें।</p></div>';
        }

        ob_start();
        self::render_styles();
        ?>
        <div class="mr-baby-detail-wrap" id="mr-baby-top">
            <?php
            // Top Language Toggle Bar (replaces breadcrumb)
            self::render_top_lang_bar();

            // 1. Header & Badges
            self::render_section_1_header($profile);

            // 2. Linguistic Etymology & Phonetics
            self::render_section_2_etymology($profile);

            // 3. Vedic Astrology Module
            self::render_section_3_vedic($profile);

            // 4. Numerology Matrix
            self::render_section_4_numerology($profile);

            // 5. Auspicious Attributes Table
            self::render_section_5_attributes($profile);

            // 6. Psychological & Character Traits
            self::render_section_6_psychology($profile);

            // 7. Career & Success Outlook
            self::render_section_7_career($profile);

            // 8. Zodiac Compatibility & Elemental Harmony
            self::render_section_8_compatibility($profile);

            // 9. Sibling & Variant Links (Auto-Validated)
            self::render_section_9_siblings_and_variants($profile, $gender_clean);

            // 10. Astro Action Hub (Conversion CTAs)
            self::render_section_10_action_hub($profile);

            // 11. Bilingual FAQ Section & JSON-LD Schema
            self::render_section_11_faqs_and_schema($profile);
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Prominent Top Language Toggle Bar
     * Positioned cleanly on top of the content area where the breadcrumb was.
     */
    private static function render_top_lang_bar()
    {
        ?>
        <div class="mr-baby-top-lang-bar">
            <div class="mr-lang-switch-box">
                <span class="mr-lang-hint">
                    <span class="lang-en">🌐 Read in:</span>
                    <span class="lang-hi">🌐 भाषा चुनें:</span>
                </span>
                <div class="mr-lang-pill-group">
                    <button type="button" class="mr-lang-btn active" data-lang="en" id="mr-btn-en" onclick="switchBabyLang('en', this)">
                        English
                    </button>
                    <button type="button" class="mr-lang-btn" data-lang="hi" id="mr-btn-hi" onclick="switchBabyLang('hi', this)">
                        हिंदी
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Section 1: Header & Badges
     */
    private static function render_section_1_header($profile)
    {
        $name        = esc_html($profile['name']);
        $script      = esc_html($profile['script'] ?? $name);
        $gender      = esc_html($profile['gender_suitability'] ?? 'Unisex');
        $origin      = esc_html($profile['primary_origin'] ?? 'Hindu / Sanskrit');
        $origin_hi   = esc_html(self::translate_to_hindi($profile['primary_origin'] ?? 'Hindu / Sanskrit'));
        $badge       = esc_html($profile['popularity_badge'] ?? 'Trending Classic');
        $badge_hi    = esc_html(self::translate_to_hindi($profile['popularity_badge'] ?? 'Trending Classic'));
        $rashi       = esc_html($profile['vedic_astrology']['rashi_moon_sign'] ?? '');
        $rashi_hi    = esc_html(self::get_rashi_hindi($rashi));
        $nakshatra   = esc_html($profile['vedic_astrology']['nakshatra'] ?? '');
        $nakshatra_hi= esc_html(self::get_nakshatra_hindi($nakshatra));
        $chaldean_no = (int)($profile['numerology']['chaldean_number'] ?? 1);
        $planet      = esc_html($profile['vedic_astrology']['ruling_planet'] ?? '');
        $planet_hi   = esc_html(self::get_planet_hindi($planet));
        ?>
        <header class="mr-baby-header-card">
            <div class="mr-baby-badge-strip">
                <span class="mr-badge mr-badge-gender <?php echo sanitize_html_class(strtolower($profile['gender'] ?? 'boy')); ?>">
                    <span class="lang-en"><?php echo $profile['gender'] === 'Girl' ? '👧 Girl Name' : '👦 Boy Name'; ?></span>
                    <span class="lang-hi"><?php echo $profile['gender'] === 'Girl' ? '👧 लड़की का नाम' : '👦 लड़के का नाम'; ?></span>
                </span>
                <span class="mr-badge mr-badge-origin">
                    <span class="lang-en">🕉️ <?php echo $origin; ?></span>
                    <span class="lang-hi">🕉️ <?php echo $origin_hi; ?></span>
                </span>
                <span class="mr-badge mr-badge-status">
                    <span class="lang-en">⭐ Popular & Meaningful Name</span>
                    <span class="lang-hi">⭐ लोकप्रिय एवं अर्थपूर्ण नाम</span>
                </span>
                <span class="mr-badge mr-badge-comprehensive">
                    <span class="lang-en">📚 Complete Name Details</span>
                    <span class="lang-hi">📚 नाम की पूरी जानकारी</span>
                </span>
            </div>

            <div class="mr-baby-title-group">
                <h1 class="mr-baby-title">
                    <span class="lang-en">
                        <span class="en-name"><?php echo $name; ?></span>
                        <?php if ($script && $script !== $name): ?>
                            <span class="script-tag" title="Devanagari Script"><?php echo $script; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="lang-hi">
                        <span class="en-name"><?php echo $script ?: $name; ?></span>
                        <span class="script-tag" title="English Transliteration"><?php echo $name; ?></span>
                    </span>
                </h1>
                <p class="mr-baby-sublead">
                    <span class="lang-en">Complete meaning, origin, Rashi, Nakshatra, lucky numbers and personality details of the name <strong><?php echo $name; ?></strong>.</span>
                    <span class="lang-hi"><strong><?php echo $script ?: $name; ?></strong> नाम का पूरा अर्थ, उत्पत्ति, राशि, नक्षत्र, शुभ अंक और स्वभाव की संपूर्ण जानकारी।</span>
                </p>
            </div>

            <div class="mr-baby-quick-strip">
                <div class="qs-item">
                    <span class="qs-label">
                        <span class="lang-en">Moon Sign (Rashi)</span>
                        <span class="lang-hi">चंद्र राशि</span>
                    </span>
                    <strong class="qs-val">
                        <span class="lang-en"><?php echo $rashi; ?></span>
                        <span class="lang-hi"><?php echo $rashi_hi; ?></span>
                    </strong>
                </div>
                <div class="qs-item">
                    <span class="qs-label">
                        <span class="lang-en">Birth Star (Nakshatra)</span>
                        <span class="lang-hi">जन्म नक्षत्र</span>
                    </span>
                    <strong class="qs-val">
                        <span class="lang-en"><?php echo $nakshatra; ?></span>
                        <span class="lang-hi"><?php echo $nakshatra_hi; ?></span>
                    </strong>
                </div>
                <div class="qs-item">
                    <span class="qs-label">
                        <span class="lang-en">Lucky Number</span>
                        <span class="lang-hi">शुभ अंक</span>
                    </span>
                    <strong class="qs-val num-highlight">
                        <span class="lang-en">Number <?php echo $chaldean_no; ?></span>
                        <span class="lang-hi">अंक <?php echo $chaldean_no; ?></span>
                    </strong>
                </div>
                <div class="qs-item">
                    <span class="qs-label">
                        <span class="lang-en">Ruling Planet</span>
                        <span class="lang-hi">स्वामी ग्रह</span>
                    </span>
                    <strong class="qs-val">
                        <span class="lang-en"><?php echo $planet; ?></span>
                        <span class="lang-hi"><?php echo $planet_hi; ?></span>
                    </strong>
                </div>
            </div>
        </header>
        <?php
    }

    /**
     * Section 2: Name Meaning & Origin (Simple, clear English for Indian parents)
     */
    private static function render_section_2_etymology($profile)
    {
        $etym        = $profile['etymology'] ?? [];
        $meaning_en  = esc_html($etym['literal_meaning'] ?? '');
        $meaning_hi  = esc_html(self::translate_to_hindi($etym['literal_meaning'] ?? ''));
        $syllables   = !empty($etym['syllables']) ? implode(' • ', array_map('esc_html', (array)$etym['syllables'])) : esc_html($profile['name']);
        $syl_count   = (int)($etym['syllable_count'] ?? 2);
        $ipa         = esc_html($etym['ipa_pronunciation'] ?? '');
        $phonetic    = esc_html($etym['phonetic_respelling'] ?? $profile['name']);
        $origin_en   = esc_html($profile['primary_origin'] ?? 'Hindu / Sanskrit');
        $origin_hi   = esc_html(self::translate_to_hindi($profile['primary_origin'] ?? 'Hindu / Sanskrit'));
        $name        = esc_html($profile['name']);
        $script      = esc_html($profile['script'] ?? $name);
        ?>
        <section class="mr-baby-section mr-section-etymology">
            <h2 class="mr-section-title">
                <span class="icon">📖</span>
                <span class="lang-en">Name Meaning, Origin & Pronunciation</span>
                <span class="lang-hi">नाम का अर्थ, उत्पत्ति और सही उच्चारण</span>
            </h2>

            <div class="mr-etymology-meaning-box">
                <div class="meaning-icon">💫</div>
                <div class="meaning-body">
                    <h3>
                        <span class="lang-en">What Does the Name <?php echo $name; ?> Mean?</span>
                        <span class="lang-hi"><?php echo $script ?: $name; ?> नाम का क्या अर्थ है?</span>
                    </h3>
                    <p class="meaning-text">
                        <span class="lang-en"><?php echo $meaning_en; ?></span>
                        <span class="lang-hi"><?php echo $meaning_hi; ?></span>
                    </p>
                </div>
            </div>

            <div class="mr-etymology-grid">
                <div class="etym-card">
                    <span class="etym-label">
                        <span class="lang-en">Language & Origin</span>
                        <span class="lang-hi">मूल भाषा और उत्पत्ति</span>
                    </span>
                    <strong class="etym-val">
                        <span class="lang-en"><?php echo $origin_en; ?></span>
                        <span class="lang-hi"><?php echo $origin_hi; ?></span>
                    </strong>
                    <span class="etym-desc">
                        <span class="lang-en">Where this name comes from</span>
                        <span class="lang-hi">यह नाम कहाँ से आया है</span>
                    </span>
                </div>
                <div class="etym-card">
                    <span class="etym-label">
                        <span class="lang-en">Parts of Name</span>
                        <span class="lang-hi">वर्ण एवं मात्राएं</span>
                    </span>
                    <strong class="etym-val"><?php echo $syllables; ?></strong>
                    <span class="etym-desc">
                        <span class="lang-en"><?php echo $syl_count; ?> <?php echo $syl_count === 1 ? 'part' : 'parts'; ?> (smooth to say)</span>
                        <span class="lang-hi"><?php echo $syl_count; ?> भागों में आसान उच्चारण</span>
                    </span>
                </div>
                <div class="etym-card">
                    <span class="etym-label">
                        <span class="lang-en">How to Pronounce</span>
                        <span class="lang-hi">सही उच्चारण</span>
                    </span>
                    <strong class="etym-val"><?php echo $phonetic; ?></strong>
                    <span class="etym-desc">
                        <span class="lang-en">Simple speaking guide</span>
                        <span class="lang-hi">बोलने का आसान तरीका</span>
                    </span>
                </div>
                <?php if ($ipa): ?>
                <div class="etym-card">
                    <span class="etym-label">
                        <span class="lang-en">Sound Key</span>
                        <span class="lang-hi">ध्वनि संकेत</span>
                    </span>
                    <strong class="etym-val ipa-font"><?php echo $ipa; ?></strong>
                    <span class="etym-desc">
                        <span class="lang-en">Clear international sound key</span>
                        <span class="lang-hi">सटीक उच्चारण ध्वनि</span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Section 3: Vedic Astrology Module
     */
    private static function render_section_3_vedic($profile)
    {
        $v          = $profile['vedic_astrology'] ?? [];
        $rashi      = esc_html($v['rashi_moon_sign'] ?? '');
        $rashi_hi   = esc_html(self::get_rashi_hindi($rashi));
        $nak        = esc_html($v['nakshatra'] ?? '');
        $nak_hi     = esc_html(self::get_nakshatra_hindi($nak));
        $pada       = (int)($v['nakshatra_pada'] ?? 1);
        $planet     = esc_html($v['ruling_planet'] ?? '');
        $planet_hi  = esc_html(self::get_planet_hindi($planet));
        $element    = esc_html($v['vedic_element'] ?? '');
        $element_hi = esc_html(self::get_element_hindi($element));
        $deity      = esc_html($v['rashi_deity'] ?? '');
        $deity_hi   = esc_html(self::get_deity_hindi($deity));
        $sound      = esc_html($v['auspicious_starting_syllable'] ?? '');
        ?>
        <section class="mr-baby-section mr-section-vedic">
            <h2 class="mr-section-title">
                <span class="icon">🪐</span>
                <span class="lang-en">Vedic Astrology & Kundli Details</span>
                <span class="lang-hi">वैदिक ज्योतिष एवं कुंडली विवरण</span>
            </h2>
            <p class="section-intro">
                <span class="lang-en">In Vedic astrology, every baby's name sound connects with a specific Moon Sign (Rashi), Birth Star (Nakshatra), and ruling planet:</span>
                <span class="lang-hi">वैदिक ज्योतिष के अनुसार नाम का पहला अक्षर बच्चे की चंद्र राशि, जन्म नक्षत्र और स्वामी ग्रह से जुड़ा होता है:</span>
            </p>

            <div class="mr-vedic-grid">
                <div class="vedic-item">
                    <div class="v-icon">🌙</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Moon Sign (Rashi)</span>
                            <span class="lang-hi">चंद्र राशि</span>
                        </span>
                        <h4 class="v-title">
                            <span class="lang-en"><?php echo $rashi; ?></span>
                            <span class="lang-hi"><?php echo $rashi_hi; ?></span>
                        </h4>
                        <p class="v-desc">
                            <span class="lang-en">Shapes your baby's nature, emotional balance, and natural habits.</span>
                            <span class="lang-hi">बच्चे के स्वभाव, मन की शांति और स्वाभाविक आदतों को दर्शाता है।</span>
                        </p>
                    </div>
                </div>

                <div class="vedic-item">
                    <div class="v-icon">✨</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Birth Star (Nakshatra) & Pada</span>
                            <span class="lang-hi">जन्म नक्षत्र एवं चरण</span>
                        </span>
                        <h4 class="v-title">
                            <span class="lang-en"><?php echo $nak; ?> &bull; Pada <?php echo $pada; ?></span>
                            <span class="lang-hi"><?php echo $nak_hi; ?> &bull; चरण <?php echo $pada; ?></span>
                        </h4>
                        <p class="v-desc">
                            <span class="lang-en">The birth star that guides your child's talent, energy, and life journey.</span>
                            <span class="lang-hi">जन्म नक्षत्र जो बच्चे की प्रतिभा, ऊर्जा और जीवन के मार्ग को दिशा देता है।</span>
                        </p>
                    </div>
                </div>

                <div class="vedic-item">
                    <div class="v-icon">👑</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Ruling Planet (Graha)</span>
                            <span class="lang-hi">स्वामी ग्रह (राशेश)</span>
                        </span>
                        <h4 class="v-title">
                            <span class="lang-en"><?php echo $planet; ?></span>
                            <span class="lang-hi"><?php echo $planet_hi; ?></span>
                        </h4>
                        <p class="v-desc">
                            <span class="lang-en">Brings natural confidence, intelligence, and leadership qualities.</span>
                            <span class="lang-hi">बच्चे में आत्मविश्वास, बुद्धि और नेतृत्व क्षमता का विकास करता है।</span>
                        </p>
                    </div>
                </div>

                <div class="vedic-item">
                    <div class="v-icon">🌪️</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Nature Element (Tattva)</span>
                            <span class="lang-hi">तत्व (पंचतत्व)</span>
                        </span>
                        <h4 class="v-title">
                            <span class="lang-en"><?php echo $element; ?></span>
                            <span class="lang-hi"><?php echo $element_hi; ?></span>
                        </h4>
                        <p class="v-desc">
                            <span class="lang-en">Helps maintain calm thinking, good health, and quick learning.</span>
                            <span class="lang-hi">शांत स्वभाव, अच्छी सेहत और त्वरित निर्णय क्षमता बनाए रखने में सहायक।</span>
                        </p>
                    </div>
                </div>

                <div class="vedic-item">
                    <div class="v-icon">🙏</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Protector Deity (Ishta Devata)</span>
                            <span class="lang-hi">आराध्य देव</span>
                        </span>
                        <h4 class="v-title">
                            <span class="lang-en"><?php echo $deity; ?></span>
                            <span class="lang-hi"><?php echo $deity_hi; ?></span>
                        </h4>
                        <p class="v-desc">
                            <span class="lang-en">Brings divine blessings, good fortune, and positive energy for life.</span>
                            <span class="lang-hi">ईश्वरीय आशीर्वाद, सौभाग्य और जीवन में सकारात्मक ऊर्जा प्रदान करते हैं।</span>
                        </p>
                    </div>
                </div>

                <div class="vedic-item">
                    <div class="v-icon">🗣️</div>
                    <div class="v-content">
                        <span class="v-label">
                            <span class="lang-en">Lucky Starting Sound (Akshar)</span>
                            <span class="lang-hi">शुभ नामाक्षर</span>
                        </span>
                        <h4 class="v-title"><?php echo $sound; ?></h4>
                        <p class="v-desc">
                            <span class="lang-en">Auspicious letter sound as per traditional Namkaran ceremony.</span>
                            <span class="lang-hi">नामकरण संस्कार अनुसार शुभ अक्षर जो अनुकूल प्रभाव लाता है।</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Section 4: Numerology Matrix (with one-liner explanations)
     */
    private static function render_section_4_numerology($profile)
    {
        $num            = $profile['numerology'] ?? [];
        $meanings       = $num['meanings'] ?? MR_Baby_Astro_Engine::get_numerology_meanings();
        $name           = esc_html($profile['name']);
        $script         = esc_html($profile['script'] ?? $name);

        $chaldean_no    = (int)($num['chaldean_number'] ?? 1);
        $chaldean_comp  = (int)($num['chaldean_compound'] ?? 1);
        $pythagoras_no  = (int)($num['pythagorean_number'] ?? 1);
        $soul_urge_no   = (int)($num['soul_urge_number'] ?? 1);
        $personality_no = (int)($num['personality_number'] ?? 1);
        $expression_no  = (int)($num['expression_number'] ?? 1);

        $life_path_en   = esc_html($num['life_path_resonance'] ?? '');
        $life_path_hi   = esc_html(self::translate_to_hindi($num['life_path_resonance'] ?? ''));
        ?>
        <section class="mr-baby-section mr-section-numerology">
            <h2 class="mr-section-title">
                <span class="icon">🔢</span>
                <span class="lang-en">Lucky Numbers & Numerology Analysis</span>
                <span class="lang-hi">शुभ अंक एवं अंकशास्त्र विश्लेषण</span>
            </h2>
            <p class="section-intro">
                <span class="lang-en">Numerology reveals the lucky energy numbers hidden in the letters of <strong><?php echo $name; ?></strong> using both Indian (Chaldean) and modern (Pythagorean) systems:</span>
                <span class="lang-hi">अंकशास्त्र <strong><?php echo $script ?: $name; ?></strong> नाम के अक्षरों में छिपे शुभ अंकों को भारतीय कीरो और पाइथागोरस प्रणाली से स्पष्ट करता है:</span>
            </p>

            <div class="mr-numerology-grid">
                <!-- Chaldean Destiny -->
                <div class="num-card primary-num">
                    <div class="num-badge">
                        <span class="lang-en">Main Lucky Number</span>
                        <span class="lang-hi">मुख्य शुभ अंक</span>
                    </div>
                    <div class="num-value"><?php echo $chaldean_no; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Chaldean Lucky Number</span>
                        <span class="lang-hi">कीरो भाग्य अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['chaldean_number'] ?? ''); ?></span>
                        <span class="lang-hi">यह नाम का सबसे महत्वपूर्ण अंक है जो बच्चे के भाग्य, सफलता और मुख्य जीवन उद्देश्य को दर्शाता है।</span>
                    </p>
                </div>

                <!-- Chaldean Compound -->
                <div class="num-card">
                    <div class="num-badge">
                        <span class="lang-en">Compound Total</span>
                        <span class="lang-hi">संयुक्त अंक</span>
                    </div>
                    <div class="num-value"><?php echo $chaldean_comp; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Compound Number</span>
                        <span class="lang-hi">संयुक्त यौगिक अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['chaldean_compound'] ?? ''); ?></span>
                        <span class="lang-hi">सभी अक्षरों के अंकों का कुल योग, जो बच्चे की भीतरी शक्तियों और गुप्त प्रतिभाओं को दिखाता है।</span>
                    </p>
                </div>

                <!-- Pythagorean Expression -->
                <div class="num-card">
                    <div class="num-badge">
                        <span class="lang-en">Western System</span>
                        <span class="lang-hi">पाइथागोरस अंक</span>
                    </div>
                    <div class="num-value"><?php echo $pythagoras_no; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Pythagorean Number</span>
                        <span class="lang-hi">पाइथागोरस अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['pythagorean_number'] ?? ''); ?></span>
                        <span class="lang-hi">यह अंक बच्चे के सीखने के तरीके, नए विचारों और दुनिया के प्रति नजरिए को बताता है।</span>
                    </p>
                </div>

                <!-- Soul Urge -->
                <div class="num-card">
                    <div class="num-badge">
                        <span class="lang-en">Heart's Wish</span>
                        <span class="lang-hi">दिल की इच्छा</span>
                    </div>
                    <div class="num-value"><?php echo $soul_urge_no; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Soul Urge (Heart's Desire)</span>
                        <span class="lang-hi">आत्म अभिलाषा अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['soul_urge_number'] ?? ''); ?></span>
                        <span class="lang-hi">नाम के स्वरों (A, E, I, O, U) से बना अंक, जो बच्चे की भीतरी पसंद और खुशियों को बताता है।</span>
                    </p>
                </div>

                <!-- Personality Number -->
                <div class="num-card">
                    <div class="num-badge">
                        <span class="lang-en">First Impression</span>
                        <span class="lang-hi">बाहरी प्रभाव</span>
                    </div>
                    <div class="num-value"><?php echo $personality_no; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Personality Number</span>
                        <span class="lang-hi">व्यक्तित्व छवि अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['personality_number'] ?? ''); ?></span>
                        <span class="lang-hi">लोग आपके बच्चे को पहली मुलाकात में कैसे देखते हैं और उसका समाज पर क्या प्रभाव पड़ता है।</span>
                    </p>
                </div>

                <!-- Expression Number -->
                <div class="num-card">
                    <div class="num-badge">
                        <span class="lang-en">Talent & Work</span>
                        <span class="lang-hi">कर्म व सफलता</span>
                    </div>
                    <div class="num-value"><?php echo $expression_no; ?></div>
                    <h4 class="num-title">
                        <span class="lang-en">Expression Number</span>
                        <span class="lang-hi">अभिव्यक्ति एवं कर्म अंक</span>
                    </h4>
                    <p class="num-meaning">
                        <span class="lang-en"><?php echo esc_html($meanings['expression_number'] ?? ''); ?></span>
                        <span class="lang-hi">यह अंक बच्चे की प्राकृतिक प्रतिभाओं को जीवन में बड़ी उपलब्धियों और सफलता में बदलने में मदद करता है।</span>
                    </p>
                </div>
            </div>

            <?php if (!empty($life_path_en)): ?>
            <div class="mr-life-path-box">
                <span class="badge-tag">
                    <span class="lang-en">Summary</span>
                    <span class="lang-hi">संक्षेप</span>
                </span>
                <h4>
                    <span class="lang-en">Life Path & Planetary Summary</span>
                    <span class="lang-hi">जीवन पथ एवं ग्रह प्रभाव सारांश</span>
                </h4>
                <p>
                    <span class="lang-en"><?php echo $life_path_en; ?></span>
                    <span class="lang-hi"><?php echo $life_path_hi; ?></span>
                </p>
            </div>
            <?php endif; ?>
        </section>
        <?php
    }

    /**
     * Section 5: Auspicious Attributes Table
     */
    private static function render_section_5_attributes($profile)
    {
        $a             = $profile['auspicious_attributes'] ?? [];
        $lucky_numbers = !empty($a['lucky_numbers']) ? implode(', ', (array)$a['lucky_numbers']) : '3, 6, 9';
        $lucky_colors  = (array)($a['lucky_colors'] ?? ['White', 'Light Blue', 'Silver']);
        $lucky_days    = (array)($a['lucky_days'] ?? ['Friday', 'Wednesday']);
        $days_en       = implode(', ', $lucky_days);
        $days_hi       = self::translate_days_to_hindi($lucky_days);
        $gemstone_en   = esc_html($a['lucky_gemstone'] ?? 'Diamond / White Sapphire (Heera)');
        $gemstone_hi   = esc_html(self::translate_to_hindi($a['lucky_gemstone'] ?? 'Diamond / White Sapphire (Heera)'));
        $metal_en      = esc_html($a['lucky_metal'] ?? 'Silver and Platinum');
        $metal_hi      = esc_html(self::translate_to_hindi($a['lucky_metal'] ?? 'Silver and Platinum'));
        $direction_en  = esc_html($a['favorable_direction'] ?? 'West');
        $direction_hi  = esc_html(self::translate_direction_to_hindi($a['favorable_direction'] ?? 'West'));
        ?>
        <section class="mr-baby-section mr-section-attributes">
            <h2 class="mr-section-title">
                <span class="icon">💎</span>
                <span class="lang-en">Lucky Colors, Gemstone, Days & Auspicious Details</span>
                <span class="lang-hi">शुभ रंग, रत्न, दिन एवं भाग्यशाली विवरण</span>
            </h2>
            <p class="section-intro">
                <span class="lang-en">Using these lucky colors, favorable days, and elements brings positive vibes and good fortune for your child:</span>
                <span class="lang-hi">इन शुभ रंगों, दिनों और धातुओं को अपनाने से सकारात्मक ऊर्जा में वृद्धि होती है और शुभ फल प्राप्त होते हैं:</span>
            </p>

            <div class="mr-attributes-table-wrap">
                <table class="mr-attributes-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">🎯</span>
                                <span class="lang-en">Lucky Numbers</span>
                                <span class="lang-hi">शुभ अंक</span>
                            </th>
                            <td><strong><?php echo esc_html($lucky_numbers); ?></strong></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">🎨</span>
                                <span class="lang-en">Lucky Colors</span>
                                <span class="lang-hi">शुभ रंग</span>
                            </th>
                            <td>
                                <div class="colors-wrap lang-en">
                                    <?php foreach ($lucky_colors as $color): ?>
                                        <span class="color-pill"><?php echo esc_html($color); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="colors-wrap lang-hi">
                                    <?php foreach ($lucky_colors as $color): ?>
                                        <span class="color-pill"><?php echo esc_html(self::translate_to_hindi($color)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">📅</span>
                                <span class="lang-en">Lucky Days</span>
                                <span class="lang-hi">शुभ दिन (वार)</span>
                            </th>
                            <td>
                                <span class="lang-en"><strong><?php echo esc_html($days_en); ?></strong> (Best for starting school & ceremonies)</span>
                                <span class="lang-hi"><strong><?php echo esc_html($days_hi); ?></strong> (पढ़ाई और शुभ कार्यों के लिए श्रेष्ठ)</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">💍</span>
                                <span class="lang-en">Lucky Gemstone</span>
                                <span class="lang-hi">शुभ रत्न</span>
                            </th>
                            <td>
                                <span class="lang-en"><strong><?php echo $gemstone_en; ?></strong> (Consult an astrologer before wearing)</span>
                                <span class="lang-hi"><strong><?php echo $gemstone_hi; ?></strong> (रत्न पहनने से पहले ज्योतिषी से सलाह अवश्य लें)</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">🪙</span>
                                <span class="lang-en">Lucky Metal</span>
                                <span class="lang-hi">शुभ धातु</span>
                            </th>
                            <td>
                                <span class="lang-en"><strong><?php echo $metal_en; ?></strong></span>
                                <span class="lang-hi"><strong><?php echo $metal_hi; ?></strong></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <span class="attr-icon">🧭</span>
                                <span class="lang-en">Lucky Direction</span>
                                <span class="lang-hi">शुभ दिशा</span>
                            </th>
                            <td>
                                <span class="lang-en"><strong><?php echo $direction_en; ?></strong> (Best direction for study desk and bedroom)</span>
                                <span class="lang-hi"><strong><?php echo $direction_hi; ?></strong> (पढ़ाई की मेज और कमरे के लिए सर्वोत्तम दिशा)</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
    }

    /**
     * Section 6: Psychological & Character Traits
     */
    private static function render_section_6_psychology($profile)
    {
        $narrative_en  = $profile['personality_and_character_insights'] ?? '';
        $paragraphs_en = array_filter(array_map('trim', explode("\n", $narrative_en)));
        if (empty($paragraphs_en)) {
            $paragraphs_en = [$narrative_en];
        }

        $paragraphs_hi = [];
        foreach ($paragraphs_en as $p) {
            if (!empty($p)) {
                $paragraphs_hi[] = self::translate_to_hindi($p);
            }
        }
        ?>
        <section class="mr-baby-section mr-section-psychology">
            <h2 class="mr-section-title">
                <span class="icon">🧠</span>
                <span class="lang-en">Personality, Nature & Character Qualities</span>
                <span class="lang-hi">स्वभाव, आदतें और व्यक्तित्व की विशेषताएं</span>
            </h2>
            <div class="mr-narrative-content lang-en">
                <?php foreach ($paragraphs_en as $p): ?>
                    <?php if (!empty($p)): ?>
                        <p><?php echo esc_html($p); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="mr-narrative-content lang-hi">
                <?php foreach ($paragraphs_hi as $p): ?>
                    <?php if (!empty($p)): ?>
                        <p><?php echo esc_html($p); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Section 7: Career & Success Outlook
     */
    private static function render_section_7_career($profile)
    {
        $narrative_en  = $profile['career_and_success_predictions'] ?? '';
        $paragraphs_en = array_filter(array_map('trim', explode("\n", $narrative_en)));
        if (empty($paragraphs_en)) {
            $paragraphs_en = [$narrative_en];
        }

        $paragraphs_hi = [];
        foreach ($paragraphs_en as $p) {
            if (!empty($p)) {
                $paragraphs_hi[] = self::translate_to_hindi($p);
            }
        }
        ?>
        <section class="mr-baby-section mr-section-career">
            <h2 class="mr-section-title">
                <span class="icon">💼</span>
                <span class="lang-en">Best Career Fields, Studies & Success</span>
                <span class="lang-hi">करियर, शिक्षा और सफलता के श्रेष्ठ क्षेत्र</span>
            </h2>
            <div class="mr-narrative-content lang-en">
                <?php foreach ($paragraphs_en as $p): ?>
                    <?php if (!empty($p)): ?>
                        <p><?php echo esc_html($p); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="mr-narrative-content lang-hi">
                <?php foreach ($paragraphs_hi as $p): ?>
                    <?php if (!empty($p)): ?>
                        <p><?php echo esc_html($p); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Section 8: Zodiac Compatibility & Elemental Harmony
     */
    private static function render_section_8_compatibility($profile)
    {
        $v                  = $profile['vedic_astrology'] ?? [];
        $element_en         = esc_html($v['vedic_element'] ?? 'Air');
        $element_hi         = esc_html(self::get_element_hindi($element_en));
        $compatibility_en   = esc_html($v['favorable_rashis_and_elements'] ?? 'Harmonious with compatible elemental zodiac signs.');
        $compatibility_hi   = esc_html(self::translate_to_hindi($compatibility_en));
        $dual_market_en     = esc_html($profile['global_metadata']['dual_market_appeal'] ?? '');
        $dual_market_hi     = !empty($dual_market_en) ? esc_html(self::translate_to_hindi($dual_market_en)) : '';
        ?>
        <section class="mr-baby-section mr-section-compatibility">
            <h2 class="mr-section-title">
                <span class="icon">❤️</span>
                <span class="lang-en">Friendly Zodiac Signs & Best Match</span>
                <span class="lang-hi">अनुकूल राशियां और मित्रता</span>
            </h2>

            <div class="mr-compat-cards-grid">
                <div class="mr-compat-card">
                    <h4>
                        <span class="lang-en">Zodiac & Nature Compatibility (<?php echo $element_en; ?> Element)</span>
                        <span class="lang-hi">राशि व स्वभाव सामंजस्य (<?php echo $element_hi; ?> तत्व)</span>
                    </h4>
                    <p>
                        <span class="lang-en"><?php echo $compatibility_en; ?></span>
                        <span class="lang-hi"><?php echo $compatibility_hi; ?></span>
                    </p>
                </div>

                <?php if ($dual_market_en): ?>
                <div class="mr-compat-card">
                    <h4>
                        <span class="lang-en">Modern & Global Popularity</span>
                        <span class="lang-hi">आधुनिक एवं वैश्विक पसंद</span>
                    </h4>
                    <p>
                        <span class="lang-en"><?php echo $dual_market_en; ?></span>
                        <span class="lang-hi"><?php echo $dual_market_hi; ?></span>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Section 9: Sibling & Variant Links (Auto-Validated, Zero 404s)
     */
    private static function render_section_9_siblings_and_variants($profile, $current_gender)
    {
        $variants = (array)($profile['global_metadata']['variant_spellings'] ?? []);
        $brothers = (array)($profile['sibling_recommendations']['brother_names'] ?? []);
        $sisters  = (array)($profile['sibling_recommendations']['sister_names'] ?? []);
        ?>
        <section class="mr-baby-section mr-section-recommendations">
            <h2 class="mr-section-title">
                <span class="icon">👶</span>
                <span class="lang-en">Matching Names for Brothers & Sisters</span>
                <span class="lang-hi">भाई-बहन के मिलते-जुलते सुंदर नाम</span>
            </h2>
            <p class="section-intro">
                <span class="lang-en">Looking for matching sibling names or alternate spellings? Explore these handpicked options:</span>
                <span class="lang-hi">क्या आप भाई-बहन के लिए मिलते-जुलते नाम या अन्य स्पेलिंग खोज रहे हैं? यहाँ देखें:</span>
            </p>

            <?php if (!empty($variants)): ?>
            <div class="rec-group">
                <h4 class="rec-group-title">
                    <span class="lang-en">Similar Spellings & Pronunciations</span>
                    <span class="lang-hi">वैकल्पिक स्पेलिंग और उच्चारण</span>
                </h4>
                <div class="pills-container">
                    <?php foreach ($variants as $var):
                        $v_clean = trim($var);
                        if (empty($v_clean)) continue;
                        $first_l = strtolower(substr($v_clean, 0, 1));
                        $url = home_url("/baby-names/{$first_l}/" . strtolower($current_gender) . "/" . strtolower(urlencode($v_clean)) . "/");
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="name-pill pill-variant">
                            <?php echo esc_html($v_clean); ?> <span class="arr">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($brothers)): ?>
            <div class="rec-group">
                <h4 class="rec-group-title">
                    <span class="lang-en">👦 Matching Brother Names</span>
                    <span class="lang-hi">👦 भाई के लिए सुंदर नाम</span>
                </h4>
                <div class="pills-container">
                    <?php foreach ($brothers as $bro):
                        $b_clean = trim($bro);
                        if (empty($b_clean)) continue;
                        $first_l = strtolower(substr($b_clean, 0, 1));
                        $url = home_url("/baby-names/{$first_l}/boy/" . strtolower(urlencode($b_clean)) . "/");
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="name-pill pill-boy">
                            <?php echo esc_html($b_clean); ?> <span class="arr">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sisters)): ?>
            <div class="rec-group">
                <h4 class="rec-group-title">
                    <span class="lang-en">👧 Matching Sister Names</span>
                    <span class="lang-hi">👧 बहन के लिए सुंदर नाम</span>
                </h4>
                <div class="pills-container">
                    <?php foreach ($sisters as $sis):
                        $s_clean = trim($sis);
                        if (empty($s_clean)) continue;
                        $first_l = strtolower(substr($s_clean, 0, 1));
                        $url = home_url("/baby-names/{$first_l}/girl/" . strtolower(urlencode($s_clean)) . "/");
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="name-pill pill-girl">
                            <?php echo esc_html($s_clean); ?> <span class="arr">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
        <?php
    }

    /**
     * Section 10: Astro Action Hub (Conversion CTAs)
     */
    private static function render_section_10_action_hub($profile)
    {
        $name          = esc_html($profile['name']);
        $script        = esc_html($profile['script'] ?? $name);
        $ns_url        = home_url('/name-suitability/');
        $horoscope_url = home_url('/horoscope/');
        $baby_base     = home_url('/baby-names/');
        ?>
        <section class="mr-baby-section mr-section-hub">
            <div class="mr-hub-banner">
                <div class="hub-content">
                    <span class="hub-badge">
                        <span class="lang-en">🌟 Free Astrological Calculators</span>
                        <span class="lang-hi">🌟 निशुल्क ज्योतिषीय कैलकुलेटर</span>
                    </span>
                    <h3>
                        <span class="lang-en">Check Name Compatibility with Date of Birth</span>
                        <span class="lang-hi">जन्म तिथि के साथ नाम का सामंजस्य जांचें</span>
                    </h3>
                    <p>
                        <span class="lang-en">Find out if <strong><?php echo $name; ?></strong> perfectly matches your baby's exact date of birth using our computerized Lo-Shu Grid and Kundli match calculator.</span>
                        <span class="lang-hi">जानें कि क्या <strong><?php echo $script ?: $name; ?></strong> नाम आपके बच्चे की जन्म तारीख के अनुसार पूरी तरह शुभ है। हमारे लो-शू ग्रिड और कुंडली कैलकुलेटर का उपयोग करें।</span>
                    </p>
                    <div class="hub-buttons">
                        <a href="<?php echo esc_url($ns_url); ?>" class="mr-btn mr-btn-primary">
                            <span class="lang-en">Check Lo-Shu Grid &rarr;</span>
                            <span class="lang-hi">लो-शू ग्रिड चेक करें &rarr;</span>
                        </a>
                        <a href="<?php echo esc_url($baby_base); ?>" class="mr-btn mr-btn-secondary">
                            <span class="lang-en">Find Names by Nakshatra</span>
                            <span class="lang-hi">नक्षत्र अनुसार नाम खोजें</span>
                        </a>
                        <a href="<?php echo esc_url($horoscope_url); ?>" class="mr-btn mr-btn-outline">
                            <span class="lang-en">Read Today's Rashifal</span>
                            <span class="lang-hi">आज का राशिफल पढ़ें</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Section 11: Bilingual FAQ Section & JSON-LD Schema
     */
    private static function render_section_11_faqs_and_schema($profile)
    {
        $faqs   = (array)($profile['faqs'] ?? []);
        $name   = esc_html($profile['name']);
        $script = esc_html($profile['script'] ?? $name);

        if (empty($faqs)) {
            return;
        }

        $schema_entries = [];
        ?>
        <section class="mr-baby-section mr-section-faqs">
            <h2 class="mr-section-title">
                <span class="icon">❓</span>
                <span class="lang-en">Frequently Asked Questions About <?php echo $name; ?></span>
                <span class="lang-hi"><?php echo $script ?: $name; ?> नाम से संबंधित अक्सर पूछे जाने वाले प्रश्न</span>
            </h2>

            <div class="mr-faq-accordion">
                <?php foreach ($faqs as $i => $item):
                    $q_en = esc_html($item['question'] ?? '');
                    $a_en = esc_html($item['answer'] ?? '');
                    if (empty($q_en) || empty($a_en)) continue;

                    $q_hi = esc_html(self::translate_to_hindi($q_en));
                    $a_hi = esc_html(self::translate_to_hindi($a_en));

                    $schema_entries[] = [
                        '@type'          => 'Question',
                        'name'           => $q_en,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => $a_en
                        ]
                    ];
                ?>
                <details class="mr-faq-item" <?php echo $i === 0 ? 'open' : ''; ?>>
                    <summary class="mr-faq-question">
                        <span>
                            <span class="lang-en"><?php echo $q_en; ?></span>
                            <span class="lang-hi"><?php echo $q_hi; ?></span>
                        </span>
                        <span class="faq-toggle-icon">+</span>
                    </summary>
                    <div class="mr-faq-answer">
                        <div class="lang-en"><p><?php echo $a_en; ?></p></div>
                        <div class="lang-hi"><p><?php echo $a_hi; ?></p></div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </section>

        <?php
        // Inject Google Search Console Compliant FAQPage JSON-LD Schema
        if (!empty($schema_entries)):
            $faq_schema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $schema_entries
            ];
            echo '<script type="application/ld+json">' . wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        endif;
    }

    /**
     * Mobile-first responsive CSS styling and instant bilingual toggler
     */
    public static function render_styles()
    {
        static $styles_rendered = false;
        if ($styles_rendered) return;
        $styles_rendered = true;
        ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;600;700&display=swap" rel="stylesheet">
        <style id="mr-baby-blueprint-styles">
            :root {
                --primary-orange: #FF8C00;
                --primary-orange-hover: #E67E00;
                --accent-gold: #D4AF37;
                --border-gold: #FDE68A;
                --border-gold-light: #FEF3C7;
                --heading-purple: #673AB7;
                --bg-page: #FFFBEB;
                --bg-card: #FFFFFF;
                --bg-light-gold: #FFFDF5;
                --text-dark: #1F2937;
                --text-grey: #6B7280;
                --text-body: #2D3748;
                --boy-color: #1E88E5;
                --boy-bg: #E3F2FD;
                --boy-border: #BBDEFB;
                --girl-color: #D81B60;
                --girl-bg: #FCE4EC;
                --girl-border: #F8BBD0;
                --radius-md: 12px;
                --radius-lg: 16px;
                --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                --shadow-sm: 0 4px 6px rgba(0,0,0,0.04);
                --shadow-md: 0 10px 25px rgba(0,0,0,0.08);
            }

            /* ========================================================
               BILINGUAL LANGUAGE TOGGLING ENGINE
               ======================================================== */
            .lang-hi {
                display: none !important;
            }

            .baby-engine-container.lang-hi-active .lang-en,
            .mr-baby-detail-wrap.lang-hi-active .lang-en,
            body.lang-hi-active .lang-en {
                display: none !important;
            }

            .baby-engine-container.lang-hi-active .lang-hi,
            .mr-baby-detail-wrap.lang-hi-active .lang-hi,
            body.lang-hi-active .lang-hi {
                display: inline !important;
            }

            .baby-engine-container.lang-hi-active div.lang-hi,
            .mr-baby-detail-wrap.lang-hi-active div.lang-hi,
            .baby-engine-container.lang-hi-active p.lang-hi,
            .mr-baby-detail-wrap.lang-hi-active p.lang-hi,
            .baby-engine-container.lang-hi-active section.lang-hi,
            .mr-baby-detail-wrap.lang-hi-active section.lang-hi {
                display: block !important;
            }

            .baby-engine-container.lang-hi-active tr.lang-hi,
            .mr-baby-detail-wrap.lang-hi-active tr.lang-hi {
                display: table-row !important;
            }

            .baby-engine-container.lang-hi-active span.mr-badge,
            .mr-baby-detail-wrap.lang-hi-active span.mr-badge {
                display: inline-flex !important;
            }

            .mr-baby-detail-wrap {
                max-width: 1040px;
                margin: 0 auto;
                padding: 16px 16px 40px 16px;
                font-family: 'Noto Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                color: var(--text-dark);
                line-height: 1.8;
            }

            /* Breadcrumb Navigation */
            .mr-baby-breadcrumb {
                margin-bottom: 24px;
                font-size: 0.92rem;
                font-family: 'Noto Sans', sans-serif;
            }
            .mr-baby-breadcrumb ol {
                list-style: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
            }
            .mr-baby-breadcrumb a {
                color: var(--text-grey);
                text-decoration: none;
                transition: var(--transition);
            }
            .mr-baby-breadcrumb a:hover {
                color: var(--primary-orange);
                text-decoration: underline;
            }
            .mr-baby-breadcrumb .sep {
                color: #CBD5E1;
            }
            .mr-baby-breadcrumb strong {
                color: var(--primary-orange);
                font-weight: 700;
            }

            /* 1. Header Banner Card (MeraRashifal Signature Saffron & Gold Theme) */
            .mr-baby-header-card {
                background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
                border: 1px solid var(--border-gold);
                border-radius: var(--radius-lg);
                padding: 36px 30px;
                margin-bottom: 32px;
                box-shadow: var(--shadow-sm);
                position: relative;
                overflow: hidden;
            }
            .mr-baby-header-card::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: radial-gradient(circle at 10% 20%, rgba(255, 140, 0, 0.06) 0%, transparent 60%);
                pointer-events: none;
            }
            .mr-baby-badge-strip {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 18px;
            }
            .mr-badge {
                display: inline-flex;
                align-items: center;
                padding: 5px 14px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 700;
                letter-spacing: 0.02em;
            }
            .mr-badge-gender.boy {
                background: var(--boy-bg);
                color: var(--boy-color);
                border: 1px solid var(--boy-border);
            }
            .mr-badge-gender.girl {
                background: var(--girl-bg);
                color: var(--girl-color);
                border: 1px solid var(--girl-border);
            }
            .mr-badge-origin {
                background: #FFFBEB;
                color: #B45309;
                border: 1px solid var(--border-gold);
            }
            .mr-badge-status {
                background: #FEF3C7;
                color: #92400E;
                border: 1px solid var(--border-gold);
            }
            .mr-badge-comprehensive {
                background: #FAF5FF;
                color: var(--heading-purple);
                border: 1px solid #E9D5FF;
            }

            .mr-baby-title {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 14px;
                margin: 0 0 12px 0;
                font-family: 'Outfit', sans-serif !important;
                font-size: 2.8rem;
                font-weight: 800;
                line-height: 1.2;
                color: var(--primary-orange) !important;
                letter-spacing: 1px;
                text-shadow: 1px 2px 4px rgba(0,0,0,0.04);
            }
            .mr-baby-title .script-tag {
                font-family: 'Noto Sans', sans-serif;
                font-size: 1.8rem;
                font-weight: 700;
                color: var(--heading-purple);
                background: #FFFFFF;
                border: 1px solid var(--border-gold);
                padding: 2px 14px;
                border-radius: 8px;
            }
            .mr-baby-sublead {
                color: #4B5563;
                font-family: 'Noto Sans', sans-serif;
                font-size: 1.05rem;
                margin: 0 0 26px 0;
                max-width: 820px;
                line-height: 1.7;
            }
            .mr-baby-sublead strong {
                color: var(--primary-orange);
            }

            .mr-baby-quick-strip {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 14px;
                background: #FFFFFF;
                border: 1px solid var(--border-gold);
                border-radius: 10px;
                padding: 18px 20px;
                box-shadow: var(--shadow-sm);
            }
            .qs-item { display: flex; flex-direction: column; }
            .qs-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--text-grey);
                font-weight: 700;
                margin-bottom: 3px;
            }
            .qs-val {
                font-family: 'Outfit', sans-serif;
                font-size: 1.15rem;
                font-weight: 700;
                color: var(--text-dark);
            }
            .qs-val.num-highlight {
                color: var(--primary-orange);
                font-size: 1.25rem;
                font-weight: 800;
            }

            /* General Master Section Card */
            .mr-baby-section {
                background: var(--bg-card);
                border: 1px solid var(--border-gold);
                border-radius: var(--radius-md);
                padding: 30px;
                margin-bottom: 30px;
                box-shadow: var(--shadow-sm);
                transition: var(--transition);
            }
            .mr-baby-section:hover {
                box-shadow: var(--shadow-md);
                border-color: var(--accent-gold);
            }
            .mr-section-title {
                font-family: 'Outfit', sans-serif !important;
                font-size: 1.55rem !important;
                font-weight: 700 !important;
                color: var(--heading-purple) !important;
                margin: 0 0 16px 0;
                display: flex;
                align-items: center;
                gap: 10px;
                border-bottom: 2px solid var(--border-gold-light);
                padding-bottom: 12px;
            }
            .mr-section-title .icon {
                font-size: 1.6rem;
            }
            .section-intro {
                color: var(--text-grey);
                font-size: 0.98rem;
                margin: 0 0 22px 0;
                line-height: 1.7;
            }

            /* 2. Etymology Meaning Box */
            .mr-etymology-meaning-box {
                display: flex;
                gap: 16px;
                background: #FFFBEB;
                border: 1px dashed #F59E0B;
                border-left: 5px solid #D97706;
                border-radius: 8px;
                padding: 22px;
                margin-bottom: 22px;
            }
            .meaning-icon {
                font-size: 2rem;
                line-height: 1;
            }
            .meaning-body h3 {
                font-family: 'Outfit', sans-serif;
                margin: 0 0 6px 0;
                font-size: 1.2rem;
                font-weight: 700;
                color: #B45309;
            }
            .meaning-text {
                font-family: 'Noto Sans', sans-serif;
                margin: 0;
                color: #78350F;
                font-size: 1.05rem;
                line-height: 1.75;
                font-weight: 500;
            }

            .mr-etymology-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
            }
            .etym-card {
                background: var(--bg-light-gold);
                border: 1px solid var(--border-gold);
                border-radius: 8px;
                padding: 16px 18px;
                display: flex;
                flex-direction: column;
            }
            .etym-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                color: var(--text-grey);
                margin-bottom: 4px;
                font-weight: 700;
            }
            .etym-val {
                font-family: 'Outfit', sans-serif;
                font-size: 1.2rem;
                font-weight: 700;
                color: var(--heading-purple);
                margin-bottom: 4px;
            }
            .etym-desc {
                font-size: 0.85rem;
                color: var(--text-grey);
            }
            .ipa-font {
                font-family: "Lucida Sans Unicode", "Arial Unicode MS", sans-serif;
                color: var(--primary-orange);
                font-weight: 700;
            }

            /* 3. Vedic Astrology Grid */
            .mr-vedic-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 18px;
            }
            .vedic-item {
                display: flex;
                gap: 14px;
                background: var(--bg-light-gold);
                border: 1px solid var(--border-gold);
                border-radius: 10px;
                padding: 18px;
                transition: var(--transition);
            }
            .vedic-item:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-sm);
                border-color: var(--accent-gold);
            }
            .v-icon {
                font-size: 1.9rem;
                line-height: 1;
            }
            .v-content { flex: 1; }
            .v-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                font-weight: 700;
                color: var(--text-grey);
                display: block;
                margin-bottom: 4px;
            }
            .v-title {
                font-family: 'Outfit', sans-serif;
                font-size: 1.22rem;
                font-weight: 700;
                color: var(--primary-orange);
                margin: 0 0 4px 0;
            }
            .v-desc {
                font-family: 'Noto Sans', sans-serif;
                font-size: 0.88rem;
                color: #4B5563;
                margin: 0;
                line-height: 1.5;
            }

            /* 4. Numerology Grid */
            .mr-numerology-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 18px;
                margin-bottom: 22px;
            }
            .num-card {
                background: var(--bg-light-gold);
                border: 1px solid var(--border-gold);
                border-radius: 10px;
                padding: 20px;
                display: flex;
                flex-direction: column;
                transition: var(--transition);
            }
            .num-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-sm);
                border-color: var(--accent-gold);
            }
            .num-card.primary-num {
                background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
                border-color: var(--border-gold);
            }
            .num-badge {
                align-self: flex-start;
                font-size: 0.7rem;
                text-transform: uppercase;
                background: #FEF3C7;
                color: #B45309;
                border: 1px solid var(--border-gold);
                padding: 3px 9px;
                border-radius: 4px;
                font-weight: 700;
                margin-bottom: 12px;
            }
            .num-value {
                font-family: 'Outfit', sans-serif;
                font-size: 3rem;
                font-weight: 800;
                color: var(--primary-orange);
                line-height: 1;
                margin-bottom: 8px;
            }
            .num-title {
                font-family: 'Outfit', sans-serif;
                font-size: 1.15rem;
                font-weight: 700;
                margin: 0 0 6px 0;
                color: var(--heading-purple);
            }
            .num-meaning {
                font-family: 'Noto Sans', sans-serif;
                font-size: 0.86rem;
                color: #4B5563;
                margin: 0;
                line-height: 1.55;
            }

            .mr-life-path-box {
                background: #FFFBEB;
                border: 1px solid var(--border-gold);
                border-left: 5px solid var(--primary-orange);
                border-radius: 8px;
                padding: 22px;
            }
            .mr-life-path-box .badge-tag {
                font-size: 0.75rem;
                text-transform: uppercase;
                background: #FEF3C7;
                color: #B45309;
                border: 1px solid var(--border-gold);
                padding: 3px 9px;
                border-radius: 4px;
                font-weight: 700;
            }
            .mr-life-path-box h4 {
                font-family: 'Outfit', sans-serif;
                margin: 8px 0 6px 0;
                color: #B45309;
                font-size: 1.2rem;
                font-weight: 700;
            }
            .mr-life-path-box p {
                font-family: 'Noto Sans', sans-serif;
                margin: 0;
                color: #78350F;
                font-size: 0.98rem;
                line-height: 1.7;
            }

            /* 5. Auspicious Attributes Table */
            .mr-attributes-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .mr-attributes-table {
                width: 100%;
                border-collapse: collapse;
            }
            .mr-attributes-table th, .mr-attributes-table td {
                padding: 14px 18px;
                border-bottom: 1px solid var(--border-gold-light);
                text-align: left;
                font-size: 0.98rem;
            }
            .mr-attributes-table tr:nth-child(even) td,
            .mr-attributes-table tr:nth-child(even) th {
                background-color: var(--bg-light-gold);
            }
            .mr-attributes-table th {
                width: 260px;
                color: var(--text-grey);
                font-weight: 700;
            }
            .mr-attributes-table td {
                color: var(--text-dark);
                font-weight: 500;
            }
            .attr-icon {
                margin-right: 6px;
                font-size: 1.1rem;
            }
            .colors-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
            .color-pill {
                background: #FFFFFF;
                border: 1px solid var(--border-gold);
                padding: 3px 12px;
                border-radius: 14px;
                font-size: 0.88rem;
                font-weight: 600;
                color: #4B5563;
            }

            /* 6 & 7. Psychological Insights & Career Outlook */
            .mr-narrative-content p {
                font-family: 'Noto Sans', sans-serif;
                font-size: 1.05rem;
                color: var(--text-body);
                line-height: 1.85;
                margin: 0 0 18px 0;
            }
            .mr-narrative-content p:last-child { margin-bottom: 0; }

            /* 8. Compatibility Cards */
            .mr-compat-cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 18px;
            }
            .mr-compat-card {
                background: var(--bg-light-gold);
                border: 1px solid var(--border-gold);
                border-radius: 10px;
                padding: 22px;
            }
            .mr-compat-card h4 {
                font-family: 'Outfit', sans-serif;
                margin: 0 0 8px 0;
                color: var(--primary-orange);
                font-size: 1.15rem;
                font-weight: 700;
            }
            .mr-compat-card p {
                font-family: 'Noto Sans', sans-serif;
                margin: 0;
                font-size: 0.95rem;
                color: #4B5563;
                line-height: 1.7;
            }

            /* 9. Sibling & Variant Pills */
            .rec-group { margin-bottom: 24px; }
            .rec-group:last-child { margin-bottom: 0; }
            .rec-group-title {
                font-family: 'Outfit', sans-serif;
                font-size: 1rem;
                color: var(--heading-purple);
                font-weight: 700;
                margin: 0 0 12px 0;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .pills-container { display: flex; flex-wrap: wrap; gap: 10px; }
            .name-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 18px;
                border-radius: 30px;
                font-family: 'Outfit', sans-serif;
                font-size: 0.92rem;
                font-weight: 700;
                text-decoration: none;
                transition: var(--transition);
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            }
            .name-pill .arr { font-size: 0.85rem; transition: transform 0.15s; }
            .name-pill:hover .arr { transform: translateX(3px); }

            .pill-variant {
                background: #FFFFFF;
                color: var(--primary-orange);
                border: 1px solid var(--border-gold);
            }
            .pill-variant:hover {
                background: var(--primary-orange);
                color: #FFFFFF;
                border-color: var(--primary-orange);
                transform: translateY(-2px);
            }
            .pill-boy {
                background: #FFFFFF;
                color: var(--boy-color);
                border: 1px solid var(--boy-border);
            }
            .pill-boy:hover {
                background: var(--boy-color);
                color: #FFFFFF;
                border-color: var(--boy-color);
                transform: translateY(-2px);
            }
            .pill-girl {
                background: #FFFFFF;
                color: var(--girl-color);
                border: 1px solid var(--girl-border);
            }
            .pill-girl:hover {
                background: var(--girl-color);
                color: #FFFFFF;
                border-color: var(--girl-color);
                transform: translateY(-2px);
            }

            /* 10. Astro Action Hub (Signature Purple & Gold Standard) */
            .mr-hub-banner {
                background: var(--heading-purple);
                color: #FFFFFF;
                border-radius: var(--radius-md);
                padding: 36px 28px;
                text-align: center;
                box-shadow: var(--shadow-md);
            }
            .hub-badge {
                display: inline-block;
                background: rgba(255, 193, 7, 0.2);
                color: #FFC107;
                border: 1px solid rgba(255, 193, 7, 0.4);
                padding: 4px 14px;
                border-radius: 20px;
                font-size: 0.82rem;
                font-weight: 700;
                margin-bottom: 14px;
            }
            .hub-content h3 {
                font-family: 'Outfit', sans-serif;
                font-size: 1.9rem;
                font-weight: 800;
                color: #FFC107;
                margin: 0 0 10px 0;
            }
            .hub-content p {
                font-family: 'Noto Sans', sans-serif;
                max-width: 650px;
                margin: 0 auto 24px auto;
                font-size: 1.02rem;
                color: rgba(255, 255, 255, 0.92);
                line-height: 1.7;
            }
            .hub-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
            }
            .mr-btn {
                display: inline-block;
                padding: 12px 24px;
                border-radius: 8px;
                font-family: 'Outfit', sans-serif;
                font-size: 0.96rem;
                font-weight: 700;
                text-decoration: none;
                transition: var(--transition);
            }
            .mr-btn-primary {
                background: #FFC107;
                color: #1F2937;
                box-shadow: 0 4px 12px rgba(255, 193, 7, 0.35);
            }
            .mr-btn-primary:hover {
                background: var(--primary-orange);
                color: #FFFFFF;
                transform: translateY(-2px);
            }
            .mr-btn-secondary {
                background: #FFFFFF;
                color: var(--heading-purple);
            }
            .mr-btn-secondary:hover {
                background: #FEF3C7;
                color: #1F2937;
                transform: translateY(-2px);
            }
            .mr-btn-outline {
                background: transparent;
                color: #FFFFFF;
                border: 1px solid rgba(255, 255, 255, 0.4);
            }
            .mr-btn-outline:hover {
                background: rgba(255, 255, 255, 0.15);
                transform: translateY(-2px);
            }

            /* 11. Frequently Asked Questions (FAQ) Accordion */
            .mr-faq-accordion {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .mr-faq-item {
                border: 1px solid var(--border-gold);
                border-radius: 8px;
                overflow: hidden;
                background: var(--bg-light-gold);
                transition: var(--transition);
            }
            .mr-faq-item:hover {
                border-color: var(--accent-gold);
            }
            .mr-faq-question {
                padding: 16px 20px;
                font-family: 'Outfit', sans-serif;
                font-weight: 700;
                font-size: 1.05rem;
                color: var(--text-dark);
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                user-select: none;
                list-style: none;
                background: var(--bg-light-gold);
                transition: var(--transition);
            }
            .mr-faq-question::-webkit-details-marker { display: none; }
            .mr-faq-item[open] .mr-faq-question {
                color: var(--heading-purple);
                background: #FFFBEB;
                border-bottom: 1px solid var(--border-gold-light);
            }
            .faq-toggle-icon {
                font-size: 1.4rem;
                line-height: 1;
                color: var(--primary-orange);
                font-weight: 800;
                transition: transform 0.2s ease;
            }
            .mr-faq-item[open] .faq-toggle-icon {
                transform: rotate(45deg);
                color: var(--heading-purple);
            }
            .mr-faq-answer {
                padding: 16px 20px 20px 20px;
                color: #475569;
                font-family: 'Noto Sans', sans-serif;
                font-size: 0.98rem;
                line-height: 1.8;
                background: #FFFFFF;
            }
            .mr-faq-answer p { margin: 0; }

            /* Top Language Toggle Bar (Prominent at top of content) */
            .mr-baby-top-lang-bar {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                margin-bottom: 20px;
                padding: 4px 0;
            }
            .mr-lang-switch-box {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                background: #FFFFFF;
                border: 1px solid var(--border-gold);
                padding: 6px 14px;
                border-radius: 50px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .mr-lang-hint {
                font-size: 0.88rem;
                font-weight: 700;
                color: var(--text-grey);
                display: inline-flex;
                align-items: center;
            }
            .mr-lang-pill-group {
                display: inline-flex;
                background: #FEF3C7;
                border-radius: 30px;
                padding: 3px;
                gap: 4px;
            }
            .mr-lang-btn {
                border: none;
                background: transparent;
                padding: 6px 16px;
                border-radius: 20px;
                font-family: 'Outfit', 'Noto Sans', sans-serif;
                font-size: 0.88rem;
                font-weight: 700;
                color: #78350F;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .mr-lang-btn:hover {
                color: var(--primary-orange);
            }
            .mr-lang-btn.active {
                background: var(--primary-orange);
                color: #FFFFFF !important;
                box-shadow: 0 2px 8px rgba(255, 140, 0, 0.35);
            }

            /* Breadcrumb Navigation - Suppress all redundant visual breadcrumbs */
            .mr-baby-breadcrumb,
            .ast-breadcrumbs,
            .entry-header .ast-breadcrumbs {
                display: none !important;
            }

            /* Mobile Responsiveness */
            @media (max-width: 768px) {
                .mr-baby-top-lang-bar { justify-content: center; }
                .mr-baby-header-card { padding: 24px 18px; }
                .mr-baby-title { font-size: 2.1rem; }
                .mr-baby-title .script-tag { font-size: 1.4rem; }
                .mr-baby-section { padding: 20px 16px; }
                .mr-attributes-table th { width: 140px; }
                .hub-content h3 { font-size: 1.5rem; }
            }
        </style>
        <script>
        // Ensure seamless client-side bilingual switching on click without page reload
        window.switchBabyLang = function(lang, btn) {
            var isHi = (lang === 'hi');
            var containers = document.querySelectorAll('.baby-engine-container, .mr-baby-detail-wrap, body');
            containers.forEach(function(el) {
                if (isHi) {
                    el.classList.add('lang-hi-active');
                } else {
                    el.classList.remove('lang-hi-active');
                }
            });

            // Synchronize active states across all language toggle buttons
            document.querySelectorAll('.mr-lang-btn, .af-z-lang-btn').forEach(function(b) {
                var bLang = b.getAttribute('data-lang');
                if (!bLang) {
                    bLang = (b.textContent.trim().indexOf('हिंदी') !== -1) ? 'hi' : 'en';
                }
                if (bLang === lang) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            try { localStorage.setItem('mr_baby_lang', lang); } catch(e) {}
        };

        // Auto-restore saved language preference on page load
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var saved = localStorage.getItem('mr_baby_lang');
                if (saved === 'hi') {
                    window.switchBabyLang('hi');
                }
            } catch(e) {}
        });
        </script>
        <?php
    }

    /* ========================================================
       TRANSLATION & LOCALIZATION HELPER METHODS
       ======================================================== */

    private static $runtime_translations = [];

    /**
     * Translates English text to authentic Hindi using cached Google Translate GTX endpoint.
     * Strictly preserves Latin digits (0-9) to honor the "excluding number" directive.
     */
    public static function translate_to_hindi($text)
    {
        if (empty($text)) return '';
        $text = trim($text);

        if (isset(self::$runtime_translations[$text])) {
            return self::$runtime_translations[$text];
        }

        $cache_key = 'mrb_hi_' . md5($text);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            self::$runtime_translations[$text] = $cached;
            return $cached;
        }

        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=hi&dt=t';
        $response = wp_remote_post($url, [
            'timeout'    => 5,
            'sslverify'  => false,
            'user-agent' => 'Mozilla/5.0',
            'body'       => ['q' => $text]
        ]);

        if (is_wp_error($response)) {
            self::$runtime_translations[$text] = $text;
            return $text;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!empty($data[0])) {
            $translated = '';
            foreach ($data[0] as $segment) {
                if (isset($segment[0])) {
                    $translated .= $segment[0];
                }
            }
            if (!empty($translated)) {
                // Strictly preserve all numbers in Latin digits (0-9)
                $devanagari_digits = ['०','१','२','३','४','५','६','७','८','९'];
                $latin_digits      = ['0','1','2','3','4','5','6','7','8','9'];
                $translated        = str_replace($devanagari_digits, $latin_digits, $translated);

                set_transient($cache_key, $translated, DAY_IN_SECONDS * 60);
                self::$runtime_translations[$text] = $translated;
                return $translated;
            }
        }

        self::$runtime_translations[$text] = $text;
        set_transient($cache_key, $text, HOUR_IN_SECONDS);
        return $text;
    }

    public static function get_rashi_hindi($rashi)
    {
        $map = [
            'Aries' => 'मेष (Aries)', 'Mesh' => 'मेष (Mesh)',
            'Taurus' => 'वृषभ (Taurus)', 'Vrishabh' => 'वृषभ (Vrishabh)',
            'Gemini' => 'मिथुन (Gemini)', 'Mithun' => 'मिथुन (Mithun)',
            'Cancer' => 'कर्क (Cancer)', 'Karka' => 'कर्क (Karka)',
            'Leo' => 'सिंह (Leo)', 'Simha' => 'सिंह (Simha)', 'Singh' => 'सिंह (Singh)',
            'Virgo' => 'कन्या (Virgo)', 'Kanya' => 'कन्या (Kanya)',
            'Libra' => 'तुला (Libra)', 'Tula' => 'तुला (Tula)',
            'Scorpio' => 'वृश्चिक (Scorpio)', 'Vrishchik' => 'वृश्चिक (Vrishchik)',
            'Sagittarius' => 'धनु (Sagittarius)', 'Dhanu' => 'धनु (Dhanu)',
            'Capricorn' => 'मकर (Capricorn)', 'Makar' => 'मकर (Makar)',
            'Aquarius' => 'कुंभ (Aquarius)', 'Kumbh' => 'कुंभ (Kumbh)',
            'Pisces' => 'मीन (Pisces)', 'Meen' => 'मीन (Meen)'
        ];
        foreach ($map as $k => $v) {
            if (stripos($rashi, $k) !== false) return $v;
        }
        return self::translate_to_hindi($rashi);
    }

    public static function get_planet_hindi($planet)
    {
        $map = [
            'Sun' => 'सूर्य (Sun)', 'Surya' => 'सूर्य (Surya)',
            'Moon' => 'चंद्रमा (Moon)', 'Chandra' => 'चंद्रमा (Chandra)',
            'Mars' => 'मंगल (Mars)', 'Mangal' => 'मंगल (Mangal)',
            'Mercury' => 'बुध (Mercury)', 'Budha' => 'बुध (Budha)',
            'Jupiter' => 'बृहस्पति (Jupiter)', 'Guru' => 'बृहस्पति (Guru)',
            'Venus' => 'शुक्र (Venus)', 'Shukra' => 'शुक्र (Shukra)',
            'Saturn' => 'शनि (Saturn)', 'Shani' => 'शनि (Shani)',
            'Rahu' => 'राहु (Rahu)', 'Ketu' => 'केतु (Ketu)'
        ];
        foreach ($map as $k => $v) {
            if (stripos($planet, $k) !== false) return $v;
        }
        return self::translate_to_hindi($planet);
    }

    public static function get_element_hindi($el)
    {
        $map = [
            'Air' => 'वायु तत्व (Air)', 'Vayu' => 'वायु तत्व (Vayu)',
            'Fire' => 'अग्नि तत्व (Fire)', 'Agni' => 'अग्नि तत्व (Agni)',
            'Earth' => 'पृथ्वी तत्व (Earth)', 'Prithvi' => 'पृथ्वी तत्व (Prithvi)',
            'Water' => 'जल तत्व (Water)', 'Jal' => 'जल तत्व (Jal)'
        ];
        foreach ($map as $k => $v) {
            if (stripos($el, $k) !== false) return $v;
        }
        return self::translate_to_hindi($el);
    }

    public static function get_nakshatra_hindi($nak)
    {
        $map = [
            'Ashwini' => 'अश्विनी', 'Bharani' => 'भरणी', 'Krittika' => 'कृत्तिका',
            'Rohini' => 'रोहिणी', 'Mrigashira' => 'मृगशिरा', 'Ardra' => 'आर्द्रा',
            'Punarvasu' => 'पुनर्वसु', 'Pushya' => 'पुष्य', 'Ashlesha' => 'आश्लेषा',
            'Magha' => 'मघा', 'Purva Phalguni' => 'पूर्वाफाल्गुनी', 'Uttara Phalguni' => 'उत्तराफाल्गुनी',
            'Hasta' => 'हस्त', 'Chitra' => 'चित्रा', 'Swati' => 'स्वाति',
            'Vishakha' => 'विशाखा', 'Anuradha' => 'अनुराधा', 'Jyeshtha' => 'ज्येष्ठा',
            'Mula' => 'मूल', 'Purva Ashadha' => 'पूर्वाषाढ़ा', 'Uttara Ashadha' => 'उत्तराषाढ़ा',
            'Shravana' => 'श्रवण', 'Dhanishta' => 'धनिष्ठा', 'Shatabhisha' => 'शतभिषा',
            'Purva Bhadrapada' => 'पूर्वभाद्रपद', 'Uttara Bhadrapada' => 'उत्तरभाद्रपद',
            'Revati' => 'रेवती'
        ];
        foreach ($map as $k => $v) {
            if (stripos($nak, $k) !== false) return $v . ' (' . $k . ')';
        }
        return self::translate_to_hindi($nak);
    }

    public static function get_deity_hindi($deity)
    {
        $map = [
            'Kartikeya' => 'भगवान कार्तिकेय', 'Shiva' => 'भगवान शिव',
            'Lakshmi' => 'माँ लक्ष्मी', 'Vishnu' => 'भगवान विष्णु',
            'Ganesha' => 'भगवान गणेश', 'Parvati' => 'माँ पार्वती',
            'Surya' => 'भगवान सूर्य', 'Hanuman' => 'भगवान हनुमान'
        ];
        $matches = [];
        foreach ($map as $k => $v) {
            if (stripos($deity, $k) !== false) $matches[] = $v;
        }
        if (!empty($matches)) {
            return implode(' / ', array_unique($matches));
        }
        return self::translate_to_hindi($deity);
    }

    public static function translate_days_to_hindi($days)
    {
        $map = [
            'Sunday' => 'रविवार', 'Monday' => 'सोमवार', 'Tuesday' => 'मंगलवार',
            'Wednesday' => 'बुधवार', 'Thursday' => 'गुरुवार', 'Friday' => 'शुक्रवार',
            'Saturday' => 'शनिवार'
        ];
        $res = [];
        foreach ((array)$days as $d) {
            $trimmed = trim($d);
            $res[] = $map[$trimmed] ?? $trimmed;
        }
        return implode(', ', $res);
    }

    public static function translate_direction_to_hindi($dir)
    {
        $map = [
            'East' => 'पूर्व दिशा', 'West' => 'पश्चिम दिशा',
            'North' => 'उत्तर दिशा', 'South' => 'दक्षिण दिशा',
            'North-East' => 'ईशान कोण (उत्तर-पूर्व)', 'North-West' => 'वायव्य कोण (उत्तर-पश्चिम)',
            'South-East' => 'आग्नेय कोण (दक्षिण-पूर्व)', 'South-West' => 'नैऋत्य कोण (दक्षिण-पश्चिम)'
        ];
        return $map[trim($dir)] ?? self::translate_to_hindi($dir);
    }
}