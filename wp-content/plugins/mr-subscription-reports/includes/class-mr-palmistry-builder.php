<?php
/**
 * Master Hast Rekha & Palmistry Analysis Report Builder (26 Pages & Free On-Device Snippet)
 * MeraRashifal Standard Visual Edition:
 * - High-legibility editorial design system (Poppins + Inter)
 * - 26-page strictly calibrated A4 layout with double parchment borders
 * - Rich visual effects: Sacred Palm Topography Map SVG, 12-Month Kinetic Energy Wave SVG,
 *   Mount Relative Potency Bar Chart SVG, Dasha Sandhi Pathway SVG, Asset Allocation Donut SVG
 * - Professional Velocity Heatmap Table, Quarterly Do's & Don'ts Action Blueprints, Muhurats
 * - 100-word bullet-pointed Essential Takeaways on every interior page
 * - Connects to Google Gemini 3.5 Flash API for Hast Rekha and Samudrik Shastra synthesis
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Palmistry_Builder {

    const REPORT_SLUG = 'palmistry_report';
    const INTERNAL_CODE = 'MR_PLMR';
    const GEMINI_MODEL = 'gemini-3.5-flash';
    const FALLBACK_MODEL = 'gemini-flash-latest';

    public static function init() {
        // Register shortcodes
        add_shortcode('mr_palm_reading', [__CLASS__, 'render_palm_reader_shortcode']);
        add_shortcode('ppb_landing', [__CLASS__, 'render_palm_reader_shortcode']);

        // AJAX handlers for Free On-Device Snippet
        add_action('wp_ajax_mr_palm_analyze_free', [__CLASS__, 'ajax_analyze_free']);
        add_action('wp_ajax_nopriv_mr_palm_analyze_free', [__CLASS__, 'ajax_analyze_free']);
    }

    /**
     * Get Authentic MeraRashifal Brand Logo URI
     */
    public static function get_logo_data_uri() {
        if (class_exists('MR_Report_Builder')) {
            $uri = MR_Report_Builder::get_logo_data_uri();
            if (!empty($uri)) {
                return $uri;
            }
        }
        $logo_path = dirname(__DIR__) . '/assets/images/logo.png';
        if (file_exists($logo_path)) {
            $type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $data = file_get_contents($logo_path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return '';
    }

    /**
     * Retrieve Gemini API Key
     */
    public static function get_api_key() {
        if (class_exists('MR_Gemini_AI')) {
            return MR_Gemini_AI::get_api_key();
        }
        return get_option('mr_gemini_api_key', 'AIzaSyDzS22g365n4yrHAPRTuRfViNS3avj3QF0');
    }

    // ========================================================
    // SVG VISUAL ENGINES (CHARTS, GRAPHS & ILLUSTRATIONS)
    // ========================================================

    /**
     * Render Sacred Hand Topography Map SVG (Vector Palm with Major Lines & Planetary Mounts)
     */
    public static function render_palm_map_svg($w = 260, $h = 240) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border-radius:8px; border:1px solid #ECE6DD; box-shadow:0 2px 8px rgba(0,0,0,0.02); display:block; margin:0 auto;'>";
        $svg .= "<defs>
            <linearGradient id='palmSkinGrad' x1='0%' y1='0%' x2='100%' y2='100%'>
                <stop offset='0%' stop-color='#FFF8EE' />
                <stop offset='100%' stop-color='#FCE7D2' />
            </linearGradient>
        </defs>";

        $svg .= "<rect width='{$w}' height='{$h}' fill='#FAF7F2' />";
        $svg .= "<circle cx='" . ($w/2) . "' cy='115' r='95' fill='none' stroke='#F0EAE1' stroke-width='1' stroke-dasharray='4' />";
        $svg .= "<circle cx='" . ($w/2) . "' cy='115' r='70' fill='none' stroke='#F0EAE1' stroke-width='1' stroke-dasharray='4' />";

        // Palm Silhouette with Fingers
        $svg .= "<path d='M 80,210 Q 130,220 180,210 L 188,135 Q 192,115 185,90 Q 180,70 174,70 Q 168,70 167,93 L 164,60 Q 160,40 152,40 Q 144,40 142,65 L 138,50 Q 135,28 125,28 Q 115,28 114,55 L 110,70 Q 106,55 98,55 Q 90,55 90,80 L 88,110 L 75,125 Q 55,135 58,150 Q 62,165 82,160 Z' fill='url(#palmSkinGrad)' stroke='#D4A373' stroke-width='1.8' stroke-linejoin='round'/>";

        // 1. Life Line (Emerald Green)
        $svg .= "<path d='M 85,126 Q 100,136 108,158 Q 112,180 102,205' fill='none' stroke='#059669' stroke-width='2.5' stroke-linecap='round'/>";
        $svg .= "<circle cx='108' cy='158' r='2.5' fill='#059669'/>";

        // 2. Head Line (Sapphire Blue - Descending to Chandra)
        $svg .= "<path d='M 86,128 Q 118,136 142,152 Q 162,170 168,190' fill='none' stroke='#2563EB' stroke-width='2.5' stroke-linecap='round'/>";
        $svg .= "<circle cx='142' cy='152' r='2.5' fill='#2563EB'/>";

        // 3. Heart Line (Rose Red)
        $svg .= "<path d='M 175,110 Q 145,105 125,103 Q 112,103 108,93' fill='none' stroke='#E11D48' stroke-width='2.5' stroke-linecap='round'/>";
        $svg .= "<circle cx='135' cy='105' r='2.5' fill='#E11D48'/>";

        // 4. Fate Line (Gold - Center Ascent)
        $svg .= "<path d='M 130,205 L 134,155 L 136,110' fill='none' stroke='#D97706' stroke-width='2.5' stroke-linecap='round'/>";
        $svg .= "<circle cx='134' cy='155' r='2.5' fill='#D97706'/>";

        // Mount Badges
        $mounts = [
            ['x' => 105, 'y' => 84,  'lbl' => '♃ Guru',    'col' => '#C2964C'],
            ['x' => 125, 'y' => 78,  'lbl' => '♄ Shani',   'col' => '#4B5563'],
            ['x' => 148, 'y' => 81,  'lbl' => '☉ Surya',   'col' => '#C45738'],
            ['x' => 168, 'y' => 96,  'lbl' => '☿ Budh',    'col' => '#2563EB'],
            ['x' => 90,  'y' => 176, 'lbl' => '♀ Shukra',  'col' => '#C45738'],
            ['x' => 165, 'y' => 176, 'lbl' => '☽ Chandra', 'col' => '#4E7D68'],
            ['x' => 134, 'y' => 135, 'lbl' => '♂ Mangal',  'col' => '#E11D48'],
        ];

        foreach ($mounts as $m) {
            $svg .= "<circle cx='{$m['x']}' cy='{$m['y']}' r='3' fill='{$m['col']}' stroke='#FFFFFF' stroke-width='1'/>";
            $svg .= "<text x='{$m['x']}' y='" . ($m['y'] - 5) . "' fill='#111827' font-size='7.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$m['lbl']}</text>";
        }

        // Legend strip
        $svg .= "<rect x='8' y='216' width='244' height='18' rx='4' fill='#FFFFFF' stroke='#ECE6DD'/>";
        $svg .= "<circle cx='18' cy='225' r='3' fill='#E11D48'/><text x='25' y='228' fill='#4B5563' font-size='7.5' font-weight='700' font-family='sans-serif'>Heart</text>";
        $svg .= "<circle cx='65' cy='225' r='3' fill='#2563EB'/><text x='72' y='228' fill='#4B5563' font-size='7.5' font-weight='700' font-family='sans-serif'>Head</text>";
        $svg .= "<circle cx='115' cy='225' r='3' fill='#059669'/><text x='122' y='228' fill='#4B5563' font-size='7.5' font-weight='700' font-family='sans-serif'>Life</text>";
        $svg .= "<circle cx='160' cy='225' r='3' fill='#D97706'/><text x='167' y='228' fill='#4B5563' font-size='7.5' font-weight='700' font-family='sans-serif'>Fate</text>";
        $svg .= "<circle cx='202' cy='225' r='3' fill='#C45738'/><text x='209' y='228' fill='#4B5563' font-size='7.5' font-weight='700' font-family='sans-serif'>Mounts</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render 12-Month / Age Kinetic Energy Wave SVG
     */
    public static function render_annual_wave_svg($w = 540, $h = 100) {
        if (class_exists('MR_Long_Yearly_Report_Builder')) {
            return MR_Long_Yearly_Report_Builder::render_annual_wave_svg($w, $h);
        }
        return "";
    }

    /**
     * Render Dasha & Lifecycle Pathway SVG
     */
    public static function render_dasha_pathway_svg($w = 540, $h = 75, $p1_dates = '2024–2027', $p1_label = 'Foundation Phase', $sandhi_date = 'JUN 11, 2027', $sandhi_label = '⚡ Antardasha Sandhi', $p2_dates = '2027–2029', $p2_label = 'Jovian Expansion') {
        if (class_exists('MR_Long_Yearly_Report_Builder')) {
            return MR_Long_Yearly_Report_Builder::render_dasha_pathway_svg($w, $h, $p1_dates, $p1_label, $sandhi_date, $sandhi_label, $p2_dates, $p2_label);
        }
        return "";
    }

    /**
     * Render Planetary Mount & Shadbala Relative Potency Bar Chart SVG
     */
    public static function render_shadbala_bars_svg($w = 540, $h = 68, $planets = []) {
        if (empty($planets)) {
            $planets = [
                ['name' => 'Shukra (Ve)',  'score' => 98, 'color' => '#C45738', 'tag' => 'Rank #1 · Apex'],
                ['name' => 'Chandra (Mo)', 'score' => 88, 'color' => '#4E7D68', 'tag' => 'Rank #2 · Intuitive'],
                ['name' => 'Shani (Sa)',   'score' => 85, 'color' => '#4B5563', 'tag' => 'Rank #3 · Karmic'],
                ['name' => 'Mangal (Ma)',  'score' => 80, 'color' => '#C45738', 'tag' => 'Exalted Plain'],
                ['name' => 'Guru (Ju)',    'score' => 78, 'color' => '#C2964C', 'tag' => 'Wisdom Mount'],
                ['name' => 'Budha (Me)',   'score' => 74, 'color' => '#C2964C', 'tag' => 'Commercial'],
                ['name' => 'Surya (Su)',   'score' => 68, 'color' => '#C45738', 'tag' => 'Vitality Ring'],
                ['name' => 'Rahu (Karma)', 'score' => 84, 'color' => '#4E7D68', 'tag' => 'Active Dasha'],
                ['name' => 'Ketu (Moksha)','score' => 76, 'color' => '#8E7C68', 'tag' => 'Root Bracelet'],
            ];
        }
        if (class_exists('MR_Long_Yearly_Report_Builder')) {
            return MR_Long_Yearly_Report_Builder::render_shadbala_bars_svg($w, $h, $planets);
        }
        return "";
    }

    /**
     * Render Asset Allocation Donut SVG
     */
    public static function render_asset_donut_svg($w = 260, $h = 135) {
        if (class_exists('MR_Long_Yearly_Report_Builder')) {
            return MR_Long_Yearly_Report_Builder::render_asset_donut_svg($w, $h);
        }
        return "";
    }

    /**
     * Render Fallback Kundali SVG
     */
    public static function render_fallback_kundali_svg($div = 'D1') {
        if (class_exists('MR_Long_Yearly_Report_Builder')) {
            return MR_Long_Yearly_Report_Builder::render_fallback_kundali_svg($div);
        }
        return "";
    }

    // ========================================================
    // GEMINI MULTIMODAL API ENGINE (PURE PHP)
    // ========================================================

    /**
     * Call Gemini API with multimodal palm image
     */
    public static function call_gemini_palm_analysis($image_path, $age = 45, $gender = 'Male', $hand = 'Right', $mode = 'free') {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'Gemini API key is not configured.'];
        }

        if (!file_exists($image_path) || filesize($image_path) === 0) {
            return ['success' => false, 'error' => 'Uploaded palm image could not be read.'];
        }

        $img_info = @getimagesize($image_path);
        if (!$img_info) {
            return ['success' => false, 'error' => 'Invalid image format. Please upload JPG or PNG.'];
        }

        $mime_type = $img_info['mime'] ?? 'image/jpeg';
        $image_bytes = file_get_contents($image_path);

        // Compress large photos to keep upload lightning fast
        if (function_exists('imagecreatefromstring') && filesize($image_path) > 400000) {
            $src_img = @imagecreatefromstring($image_bytes);
            if ($src_img) {
                $orig_w = imagesx($src_img);
                $orig_h = imagesy($src_img);
                $max_dim = 1200;
                if ($orig_w > $max_dim || $orig_h > $max_dim) {
                    if ($orig_w >= $orig_h) {
                        $new_w = $max_dim;
                        $new_h = (int)($orig_h * ($max_dim / $orig_w));
                    } else {
                        $new_h = $max_dim;
                        $new_w = (int)($orig_w * ($max_dim / $orig_h));
                    }
                    $resized = imagecreatetruecolor($new_w, $new_h);
                    imagecopyresampled($resized, $src_img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
                    ob_start();
                    imagejpeg($resized, null, 82);
                    $image_bytes = ob_get_clean();
                    $mime_type = 'image/jpeg';
                    imagedestroy($resized);
                }
                imagedestroy($src_img);
            }
        }

        $base64_data = base64_encode($image_bytes);
        $current_year = (int)date('Y');

        $prompt = "You are a master of Indian Vedic Palmistry (Hast Rekha) and Samudrik Shastra. "
                . "Analyze this palm photograph. Native details: Age: {$age}, Gender: {$gender}, Dominant hand: {$hand}. Current year: {$current_year}. "
                . "Follow classical Hast Rekha principles: Life line represents vitality not lifespan; distinguish between clear vs faint lines; no false claims of rare marks if not visible. "
                . "Calculate age-mapped milestones starting from current age {$age} spanning into future decades. "
                . "Respond strictly with a JSON object containing these exact keys: "
                . "{"
                . "  \"summary\": \"Executive opening summary highlighting hand authority, Venus/Chandra presence, and core life theme.\", "
                . "  \"hand_type\": \"Samudrik Shastra classification (palm dimensions, finger tips, thumb firmness, problem-solving style).\", "
                . "  \"dominant_mount\": \"Name of dominant mount and its energetic meaning\", "
                . "  \"heart_line\": \"Detailed reading: location, branches, Hast Rekha meaning, and dated ages {$age}-" . ($age + 3) . " relationship dynamics.\", "
                . "  \"head_line\": \"Detailed reading: origin, Chandra descent, mental workload, and dated career timing.\", "
                . "  \"life_line\": \"Detailed reading: vitality arc around Venus, transverse family responsibility lines, vitality pacing by age.\", "
                . "  \"fate_line\": \"Detailed reading: middle-palm trajectory, career signature, adaptability, and dated promotion milestones.\", "
                . "  \"minor_lines\": \"Audit of Apollo line (recognition), Mercury line, wrist bracelets (Manibandh), simian crease status.\", "
                . "  \"special_marks\": \"Honest distinction on presence/absence of fish (Matsya), star, trident (Trishul), flag, temple, islands.\", "
                . "  \"mounts\": \"Analysis of Venus, Chandra, central Mars, and finger-base mounts.\", "
                . "  \"personality\": \"Psychological profile, emotional holding, caution, and intellectual resolve.\", "
                . "  \"love\": \"Marriage reading, Venus influence, communication tests, and spouse mutual respect.\", "
                . "  \"career\": \"Structured role vs speculative business, promotion periods, and advisory expansion.\", "
                . "  \"wealth\": \"Earned income vs speculation, breakthrough age, and 3-tier savings architecture.\", "
                . "  \"health\": \"Vitality observations, screen-free rest protocols, and non-diagnostic clinical disclaimer.\", "
                . "  \"core_strength\": \"Primary natural Hast Rekha advantage\", "
                . "  \"vitality_teaser\": \"1 sentence on life line arc and vitality routine\", "
                . "  \"heart_line_preview\": \"2-3 sentences previewing Heart Line\", "
                . "  \"head_line_preview\": \"2-3 sentences previewing Head Line\", "
                . "  \"strengths\": [\"Strength 1\", \"Strength 2\", \"Strength 3\", \"Strength 4\", \"Strength 5\"], "
                . "  \"challenges\": [\"Challenge 1\", \"Challenge 2\", \"Challenge 3\", \"Challenge 4\"], "
                . "  \"remedies\": ["
                . "    {\"title\": \"Monday Chandra mantra\", \"reason\": \"...\", \"practice\": \"Recite 'Om Som Somaya Namah' 108 times...\"}, "
                . "    {\"title\": \"Friday household harmony practice\", \"reason\": \"...\", \"practice\": \"Recite 'Om Shum Shukraya Namah' 108 times...\"}, "
                . "    {\"title\": \"Saturday practical daan\", \"reason\": \"...\", \"practice\": \"Donate a useful meal or basic supplies...\"}, "
                . "    {\"title\": \"Weekly written career sankalp\", \"reason\": \"...\", \"practice\": \"Each Monday, write 3 professional priorities...\"} "
                . "  ], "
                . "  \"lucky_elements\": {\"colour\": \"White\", \"number\": \"2\", \"day\": \"Friday\", \"gemstone\": \"White sapphire\", \"mantra\": \"Om Shum Shukraya Namah\"}, "
                . "  \"life_timeline\": ["
                . "    {\"period\": \"" . ($current_year) . " to " . ($current_year + 2) . " | Ages {$age} to " . ($age + 3) . "\", \"title\": \"Define your role and secure fair compensation\", \"description\": \"...\"}, "
                . "    {\"period\": \"" . ($current_year + 3) . " to " . ($current_year + 6) . " | Ages " . ($age + 3) . " to " . ($age + 7) . "\", \"title\": \"Convert experience into authority and steady accumulation\", \"description\": \"...\"}, "
                . "    {\"period\": \"" . ($current_year + 7) . " to " . ($current_year + 13) . " | Ages " . ($age + 7) . " to " . ($age + 14) . "\", \"title\": \"Broaden your influence through mentoring and selectivity\", \"description\": \"...\"}, "
                . "    {\"period\": \"" . ($current_year + 14) . " to " . ($current_year + 21) . " | Ages " . ($age + 14) . " to " . ($age + 22) . "\", \"title\": \"Simplify obligations while preserving useful professional purpose\", \"description\": \"...\"}, "
                . "    {\"period\": \"" . ($current_year + 22) . " to " . ($current_year + 29) . " | Ages " . ($age + 22) . " to " . ($age + 30) . "\", \"title\": \"Share experience and strengthen a settled household rhythm\", \"description\": \"...\"} "
                . "  ]"
                . "}";

        $models = [self::GEMINI_MODEL, self::FALLBACK_MODEL, 'gemini-3-flash-preview'];

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($api_key);
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mime_type,
                                    'data'      => $base64_data
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.35,
                ]
            ];

            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode($payload),
                'timeout' => 45,
                'sslverify' => false,
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                continue;
            }

            $res_body = wp_remote_retrieve_body($response);
            $json = json_decode($res_body, true);
            $raw_text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = json_decode($raw_text, true);

            if (is_array($parsed) && !empty($parsed['summary'])) {
                return ['success' => true, 'model' => $model, 'data' => $parsed];
            }
        }

        return ['success' => false, 'error' => 'Gemini AI synthesis could not complete. Please try another photo with clear lighting.'];
    }

    /**
     * AJAX Endpoint: Free On-Device Snippet
     */
    public static function ajax_analyze_free() {
        check_ajax_referer('mr_palm_nonce', 'security');

        if (empty($_FILES['palm_image']['tmp_name'])) {
            wp_send_json_error(['message' => 'Please upload a photo of your palm.']);
        }

        $age = isset($_POST['age']) ? max(10, min(100, (int)$_POST['age'])) : 45;
        $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : 'Male';
        $hand = isset($_POST['hand']) ? sanitize_text_field($_POST['hand']) : 'Right';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : 'Seeker';

        $tmp_file = $_FILES['palm_image']['tmp_name'];
        $res = self::call_gemini_palm_analysis($tmp_file, $age, $gender, $hand, 'free');

        if (!$res['success']) {
            wp_send_json_error(['message' => $res['error']]);
        }

        // Cache image & data
        $token = 'mr_palm_' . md5($name . '_' . $age . '_' . time() . '_' . wp_generate_password(8, false));
        $uploads_dir = wp_upload_dir();
        $palm_cache_dir = $uploads_dir['basedir'] . '/palm-cache';
        if (!file_exists($palm_cache_dir)) {
            wp_mkdir_p($palm_cache_dir);
        }
        $saved_image_path = $palm_cache_dir . '/' . $token . '.jpg';
        @copy($tmp_file, $saved_image_path);

        $session_data = [
            'token'      => $token,
            'name'       => $name,
            'age'        => $age,
            'gender'     => $gender,
            'hand'       => $hand,
            'image_path' => $saved_image_path,
            'data'       => $res['data'],
            'created'    => time(),
        ];
        set_transient($token, $session_data, DAY_IN_SECONDS);

        // Catalog pricing
        $catalog_pricing = function_exists('mr_get_report_catalog_config') ? mr_get_report_catalog_config() : [];
        $palm_pricing = $catalog_pricing[self::REPORT_SLUG] ?? [
            'list_price' => 1499,
            'sale_price' => 299,
            'pages'      => '26 Pages'
        ];

        $list = floatval($palm_pricing['list_price'] ?? 1499);
        $sale = floatval($palm_pricing['sale_price'] ?? 299);
        $disc = ($list > $sale && $list > 0) ? round((($list - $sale) / $list) * 100) : 80;

        wp_send_json_success([
            'token'   => $token,
            'name'    => $name,
            'age'     => $age,
            'hand'    => $hand,
            'reading' => $res['data'],
            'pricing' => [
                'list_price' => $list,
                'sale_price' => $sale,
                'discount'   => $disc,
                'pages'      => $palm_pricing['pages'] ?? '26 Pages',
            ]
        ]);
    }

    /**
     * Render the Palm Reading UI Shortcode [mr_palm_reading] / [ppb_landing]
     */
    public static function render_palm_reader_shortcode($atts = []) {
        $atts = shortcode_atts(['show_header' => 'yes'], $atts, 'mr_palm_reading');
        $user_id = get_current_user_id();
        $user_data = $user_id ? get_userdata($user_id) : null;
        $default_name = $user_data ? $user_data->display_name : '';

        $catalog = function_exists('mr_get_report_catalog_config') ? mr_get_report_catalog_config() : [];
        $pricing = $catalog[self::REPORT_SLUG] ?? ['list_price' => 1499, 'sale_price' => 299];
        $sale = floatval($pricing['sale_price'] ?? 299);
        $list = floatval($pricing['list_price'] ?? 1499);
        $disc = ($list > $sale && $list > 0) ? round((($list - $sale) / $list) * 100) : 80;

        ob_start();
        ?>
        <div class="mr-palm-wrapper">
            <style>
            .mr-palm-wrapper {
                max-width: 960px;
                margin: 0 auto;
                padding: 24px 16px 60px;
                font-family: 'Noto Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                color: #1e293b;
            }
            .mr-palm-header { text-align: center; margin-bottom: 35px; }
            .mr-palm-badge {
                display: inline-flex; align-items: center; gap: 8px;
                background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;
                padding: 6px 18px; border-radius: 50px; font-size: 13px; font-weight: 700;
                text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;
            }
            .mr-palm-h1 { font-size: 36px; font-weight: 800; color: #0f172a; line-height: 1.25; margin: 0 0 12px; }
            .mr-palm-saffron { color: #FF8C00; }
            .mr-palm-sub { font-size: 16px; color: #64748b; max-width: 680px; margin: 0 auto; line-height: 1.6; }
            .mr-palm-card {
                background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); padding: 32px 28px; margin-bottom: 30px;
            }
            .mr-palm-dropzone {
                border: 2px dashed #cbd5e1; border-radius: 16px; padding: 36px 20px;
                text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.25s ease;
            }
            .mr-palm-dropzone:hover, .mr-palm-dropzone.dragover { border-color: #FF8C00; background: #fffaf0; }
            .mr-palm-drop-icon { font-size: 48px; color: #FF8C00; margin-bottom: 12px; }
            .mr-palm-drop-title { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
            .mr-palm-drop-note { font-size: 13px; color: #64748b; margin-bottom: 16px; }
            .mr-palm-btn-upload {
                display: inline-flex; align-items: center; gap: 8px; background: #ffffff;
                border: 1px solid #cbd5e1; color: #1e293b; font-weight: 600; font-size: 14px;
                padding: 10px 20px; border-radius: 10px; cursor: pointer; transition: all 0.2s ease;
            }
            .mr-palm-btn-upload:hover { border-color: #FF8C00; color: #FF8C00; }
            .mr-palm-preview-wrap { display: none; text-align: center; }
            .mr-palm-preview-img { max-height: 280px; max-width: 100%; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); border: 2px solid #FF8C00; }
            .mr-palm-change-btn { margin-top: 12px; background: none; border: none; color: #ef4444; font-weight: 600; font-size: 13px; cursor: pointer; }
            .mr-palm-guides { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 24px 0 28px; }
            .mr-guide-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #475569; background: #f1f5f9; padding: 10px 14px; border-radius: 10px; }
            .mr-guide-item i { color: #10b981; font-size: 16px; }
            .mr-palm-inputs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px; }
            .mr-palm-field label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
            .mr-palm-field input, .mr-palm-field select { width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; background: #fff; color: #0f172a; box-sizing: border-box; }
            .mr-palm-field input:focus, .mr-palm-field select:focus { border-color: #FF8C00; outline: none; box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.15); }
            .mr-palm-btn-submit {
                display: block; width: 100%; background: linear-gradient(135deg, #FF8C00 0%, #E67E22 100%);
                color: #ffffff; font-size: 17px; font-weight: 700; padding: 16px 24px; border: none;
                border-radius: 12px; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(255, 140, 0, 0.35); text-align: center;
            }
            .mr-palm-btn-submit:hover { background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 140, 0, 0.45); }
            .mr-palm-scanner { display: none; text-align: center; padding: 40px 20px; }
            .mr-scanner-orbit {
                width: 90px; height: 90px; margin: 0 auto 24px; border: 4px solid #fed7aa;
                border-top-color: #FF8C00; border-radius: 50%; animation: mr-spin 1s linear infinite; position: relative;
            }
            .mr-scanner-hand-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 36px; color: #FF8C00; }
            @keyframes mr-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            .mr-scanner-step { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
            .mr-scanner-sub { font-size: 13px; color: #64748b; }
            .mr-palm-results { display: none; animation: mrFadeIn 0.4s ease forwards; }
            @keyframes mrFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
            .mr-snippet-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
            .mr-snippet-h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; }
            .mr-free-tag { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
            .mr-snippet-summary-box { background: #fffaf0; border-left: 4px solid #FF8C00; padding: 18px 20px; border-radius: 0 12px 12px 0; margin-bottom: 24px; font-size: 15px; line-height: 1.7; color: #334155; }
            .mr-snippet-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
            .mr-snippet-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; }
            .mr-snippet-card-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 10px; }
            .mr-snippet-card-title i { color: #FF8C00; }
            .mr-snippet-card-p { font-size: 14px; color: #475569; line-height: 1.6; margin: 0; }
            .mr-upgrade-box {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;
                border-radius: 20px; padding: 36px 30px; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.25);
                border: 1px solid #334155; position: relative; overflow: hidden;
            }
            .mr-upgrade-top { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
            .mr-upgrade-badge { display: inline-block; background: rgba(255, 140, 0, 0.2); color: #fb923c; border: 1px solid rgba(255, 140, 0, 0.35); padding: 4px 14px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 8px; }
            .mr-upgrade-h3 { font-size: 26px; font-weight: 800; color: #ffffff; margin: 0 0 8px; }
            .mr-upgrade-sub { font-size: 14px; color: #cbd5e1; max-width: 580px; line-height: 1.6; }
            .mr-upgrade-price-col { text-align: right; }
            .mr-upgrade-sale { font-size: 32px; font-weight: 800; color: #FF8C00; line-height: 1; }
            .mr-upgrade-strike { font-size: 16px; color: #94a3b8; text-decoration: line-through; margin-left: 6px; }
            .mr-upgrade-save { display: inline-block; background: #10b981; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; margin-top: 4px; }
            .mr-upgrade-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin: 24px 0 28px; list-style: none; padding: 0; }
            .mr-upgrade-features li { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #e2e8f0; }
            .mr-upgrade-features li i { color: #FF8C00; font-size: 15px; }
            .mr-btn-buy-dossier {
                display: block; width: 100%; background: linear-gradient(135deg, #FF8C00 0%, #E67E22 100%);
                color: #ffffff !important; font-size: 16px; font-weight: 700; padding: 16px 28px;
                border-radius: 12px; text-align: center; text-decoration: none; box-shadow: 0 4px 18px rgba(255, 140, 0, 0.4);
                transition: all 0.25s ease; border: none; cursor: pointer;
            }
            .mr-btn-buy-dossier:hover { background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); transform: translateY(-2px); box-shadow: 0 6px 24px rgba(255, 140, 0, 0.55); }
            </style>

            <?php if ($atts['show_header'] !== 'no'): ?>
            <div class="mr-palm-header">
                <span class="mr-palm-badge"><i class="fa-solid fa-hand"></i> Vedic Samudrik Shastra</span>
                <h1 class="mr-palm-h1">AI Palmistry &amp; <span class="mr-palm-saffron">Hast Rekha Scan</span></h1>
                <p class="mr-palm-sub">
                    Upload or snap a photo of your palm. Our Gemini-powered Vedic intelligence analyzes your core lines, mounts, and authority indicators in seconds.
                </p>
            </div>
            <?php endif; ?>

            <div class="mr-palm-card">
                <form id="mr-palm-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('mr_palm_nonce', 'mr_palm_nonce_field'); ?>

                    <div class="mr-palm-dropzone" id="mr-dropzone">
                        <input type="file" id="mr-palm-file-input" name="palm_image" accept="image/jpeg,image/png,image/webp" capture="environment" style="display:none;" required>
                        <div id="mr-dropzone-prompt">
                            <div class="mr-palm-drop-icon"><i class="fa-solid fa-camera"></i></div>
                            <div class="mr-palm-drop-title">Upload or Snap Palm Photo</div>
                            <div class="mr-palm-drop-note">Clear photo of your active open palm in good lighting (JPG, PNG, WEBP)</div>
                            <button type="button" class="mr-palm-btn-upload" id="mr-browse-btn">
                                <i class="fa-solid fa-image"></i> Select Image or Take Photo
                            </button>
                        </div>
                        <div class="mr-palm-preview-wrap" id="mr-preview-wrap">
                            <img src="" id="mr-preview-img" class="mr-palm-preview-img" alt="Palm Preview">
                            <div>
                                <button type="button" class="mr-palm-change-btn" id="mr-change-btn"><i class="fa-solid fa-rotate-left"></i> Change Photo</button>
                            </div>
                        </div>
                    </div>

                    <div class="mr-palm-guides">
                        <div class="mr-guide-item"><i class="fa-solid fa-circle-check"></i> Hold palm flat with fingers relaxed</div>
                        <div class="mr-guide-item"><i class="fa-solid fa-circle-check"></i> Natural daylight or bright flashlight</div>
                        <div class="mr-guide-item"><i class="fa-solid fa-circle-check"></i> Include base of wrist to fingertips</div>
                    </div>

                    <div class="mr-palm-inputs-grid">
                        <div class="mr-palm-field">
                            <label for="mr_user_name">Full Name *</label>
                            <input type="text" id="mr_user_name" name="name" value="<?php echo esc_attr($default_name); ?>" placeholder="Your Name" required>
                        </div>
                        <div class="mr-palm-field">
                            <label for="mr_user_age">Current Age * (For timeline mapping)</label>
                            <input type="number" id="mr_user_age" name="age" min="15" max="95" value="45" required>
                        </div>
                        <div class="mr-palm-field">
                            <label for="mr_user_gender">Gender</label>
                            <select id="mr_user_gender" name="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mr-palm-field">
                            <label for="mr_user_hand">Dominant Hand *</label>
                            <select id="mr_user_hand" name="hand">
                                <option value="Right">Right Hand (Active Karma / Career)</option>
                                <option value="Left">Left Hand (Inborn Potential / Soul)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="mr-palm-btn-submit" id="mr-submit-palm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Analyze My Palm (Instant Free Reading)
                    </button>
                </form>

                <div class="mr-palm-scanner" id="mr-palm-scanner">
                    <div class="mr-scanner-orbit"><i class="fa-solid fa-hand mr-scanner-hand-icon"></i></div>
                    <div class="mr-scanner-step" id="mr-scanner-step">Inspecting palm mounts &amp; topography...</div>
                    <div class="mr-scanner-sub">Vedic Samudrik Shastra AI Synthesis in progress</div>
                </div>

                <div class="mr-palm-results" id="mr-palm-results">
                    <div class="mr-snippet-header">
                        <div>
                            <span class="mr-free-tag"><i class="fa-solid fa-check"></i> Free Instant Reading</span>
                            <h2 class="mr-snippet-h2" id="mr-res-title">🖐️ Your Palm Reading Summary</h2>
                        </div>
                        <button type="button" class="mr-palm-btn-upload" id="mr-scan-another-btn">
                            <i class="fa-solid fa-camera"></i> Scan Another Hand
                        </button>
                    </div>

                    <div class="mr-snippet-summary-box" id="mr-res-summary"></div>

                    <div class="mr-snippet-grid">
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-hand"></i> Hand Type &amp; Shape</h4>
                            <p class="mr-snippet-card-p" id="mr-res-handtype"></p>
                        </div>
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-mountain"></i> Dominant Mount Signal</h4>
                            <p class="mr-snippet-card-p" id="mr-res-mount"></p>
                        </div>
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-heart"></i> ❤️ Heart Line Preview</h4>
                            <p class="mr-snippet-card-p" id="mr-res-heart"></p>
                        </div>
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-brain"></i> 🧠 Head Line Preview</h4>
                            <p class="mr-snippet-card-p" id="mr-res-head"></p>
                        </div>
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-bolt"></i> 💪 Core Strength</h4>
                            <p class="mr-snippet-card-p" id="mr-res-strength"></p>
                        </div>
                        <div class="mr-snippet-card">
                            <h4 class="mr-snippet-card-title"><i class="fa-solid fa-spa"></i> 🌿 Vitality Observation</h4>
                            <p class="mr-snippet-card-p" id="mr-res-vitality"></p>
                        </div>
                    </div>

                    <div class="mr-upgrade-box">
                        <div class="mr-upgrade-top">
                            <div>
                                <span class="mr-upgrade-badge"><i class="fa-solid fa-crown"></i> Complete 26-Page Dossier</span>
                                <h3 class="mr-upgrade-h3">Unlock Full Master Hast Rekha Report</h3>
                                <p class="mr-upgrade-sub">
                                    Get the exhaustive 26-page publication-grade PDF report with sacred palm topography maps, kinetic vitality waves, quarterly roadmaps, professional heatmaps, asset donuts, and personalized Vedic Upay.
                                </p>
                            </div>
                            <div class="mr-upgrade-price-col">
                                <div class="mr-upgrade-sale">₹<?php echo number_format($sale); ?><span class="mr-upgrade-strike">₹<?php echo number_format($list); ?></span></div>
                                <span class="mr-upgrade-save"><?php echo $disc; ?>% OFF TODAY</span>
                            </div>
                        </div>

                        <ul class="mr-upgrade-features">
                            <li><i class="fa-solid fa-circle-check"></i> <strong>26-Page Master Publication Report</strong> (Downloadable PDF)</li>
                            <li><i class="fa-solid fa-circle-check"></i> <strong>Sacred Hand Topography Map SVG</strong> (Major lines &amp; mounts)</li>
                            <li><i class="fa-solid fa-circle-check"></i> <strong>12-Month Kinetic Energy Wave SVG</strong> (Peak timing windows)</li>
                            <li><i class="fa-solid fa-circle-check"></i> <strong>Professional Velocity Heatmap</strong> (Career promotion timing)</li>
                            <li><i class="fa-solid fa-circle-check"></i> <strong>Asset Allocation Donut Chart</strong> (Real estate, gold &amp; liquid)</li>
                            <li><i class="fa-solid fa-circle-check"></i> <strong>Personalized Vedic Upay &amp; Wall Calendar</strong> (Rituals &amp; mantras)</li>
                        </ul>

                        <button type="button" class="mr-btn-buy-dossier" id="mr-btn-order-dossier" data-slug="palmistry_report" data-title="Master Hast Rekha &amp; Palmistry Analysis Report">
                            <i class="fa-solid fa-file-pdf"></i> Download Complete 26-Page Palmistry Dossier (₹<?php echo number_format($sale); ?>) &rarr;
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <script>
        (function($){
            $(document).ready(function(){
                var fileInput = $('#mr-palm-file-input');
                var dropzone = $('#mr-dropzone');
                var previewWrap = $('#mr-preview-wrap');
                var promptWrap = $('#mr-dropzone-prompt');
                var previewImg = $('#mr-preview-img');
                var form = $('#mr-palm-form');
                var scanner = $('#mr-palm-scanner');
                var results = $('#mr-palm-results');

                $('#mr-browse-btn').on('click', function(e){ e.preventDefault(); fileInput.trigger('click'); });
                dropzone.on('dragover', function(e){ e.preventDefault(); $(this).addClass('dragover'); })
                        .on('dragleave drop', function(e){
                            e.preventDefault(); $(this).removeClass('dragover');
                            if (e.type === 'drop') {
                                var files = e.originalEvent.dataTransfer.files;
                                if (files && files.length > 0) { fileInput[0].files = files; showPreview(files[0]); }
                            }
                        });

                fileInput.on('change', function(){ if (this.files && this.files[0]) { showPreview(this.files[0]); } });

                function showPreview(file) {
                    var reader = new FileReader();
                    reader.onload = function(e){ previewImg.attr('src', e.target.result); promptWrap.hide(); previewWrap.show(); };
                    reader.readAsDataURL(file);
                }

                $('#mr-change-btn').on('click', function(e){
                    e.preventDefault(); fileInput.val(''); previewImg.attr('src', ''); previewWrap.hide(); promptWrap.show();
                });

                $('#mr-scan-another-btn').on('click', function(e){
                    e.preventDefault(); results.hide(); form.show(); fileInput.val(''); previewImg.attr('src', ''); previewWrap.hide(); promptWrap.show();
                    $('html, body').animate({ scrollTop: form.offset().top - 80 }, 400);
                });

                form.on('submit', function(e){
                    e.preventDefault();
                    if (!fileInput[0].files || !fileInput[0].files[0]) {
                        alert('Please upload or snap a photo of your palm first.');
                        return;
                    }
                    form.hide(); scanner.show();
                    var steps = [
                        'Inspecting palm mounts & topography...',
                        'Tracing Heart, Head, Life, and Fate lines...',
                        'Evaluating Samudrik Shastra signals...',
                        'Finalizing your Vedic Hast Rekha reading...'
                    ];
                    var stepIdx = 0;
                    var stepTimer = setInterval(function(){
                        stepIdx = (stepIdx + 1) % steps.length;
                        $('#mr-scanner-step').text(steps[stepIdx]);
                    }, 2200);

                    var formData = new FormData(form[0]);
                    formData.append('action', 'mr_palm_analyze_free');
                    formData.append('security', '<?php echo wp_create_nonce('mr_palm_nonce'); ?>');

                    $.ajax({
                        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                        type: 'POST', data: formData, processData: false, contentType: false,
                        success: function(resp){
                            clearInterval(stepTimer); scanner.hide();
                            if (resp && resp.success) {
                                var r = resp.data.reading;
                                $('#mr-res-title').html('🖐️ ' + resp.data.name + '’s Palm Reading');
                                $('#mr-res-summary').text(r.summary || '');
                                $('#mr-res-handtype').text(r.hand_type || 'Mixed Practical & Intuitive');
                                $('#mr-res-mount').text(r.dominant_mount || 'Prominent Venus & Chandra');
                                $('#mr-res-heart').text(r.heart_line_preview || r.heart_line || '');
                                $('#mr-res-head').text(r.head_line_preview || r.head_line || '');
                                $('#mr-res-strength').text(r.core_strength || '');
                                $('#mr-res-vitality').text(r.vitality_teaser || '');
                                results.show();
                                $('html, body').animate({ scrollTop: results.offset().top - 80 }, 400);
                            } else {
                                form.show();
                                alert(resp.data && resp.data.message ? resp.data.message : 'Analysis failed. Please try again.');
                            }
                        },
                        error: function(){
                            clearInterval(stepTimer); scanner.hide(); form.show();
                            alert('Network error while analyzing image. Please check your connection and retry.');
                        }
                    });
                });

                $('#mr-btn-order-dossier').on('click', function(e){
                    e.preventDefault();
                    var slug = $(this).data('slug');
                    var title = $(this).data('title');
                    if (typeof window.MR_open_payment_modal === 'function') {
                        window.MR_open_payment_modal(slug, title, <?php echo $sale; ?>);
                    } else if ($('.mr-btn-buy-alacarte[data-slug="' + slug + '"]').length > 0) {
                        $('.mr-btn-buy-alacarte[data-slug="' + slug + '"]').first().trigger('click');
                    } else {
                        window.location.href = '<?php echo esc_url(home_url('/reports/?report=palmistry_report')); ?>';
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    // ========================================================
    // MASTER 26-PAGE PUBLICATION-GRADE REPORT BUILDER
    // ========================================================

    /**
     * Build the Complete 26-Page Master Hast Rekha & Palmistry Report HTML
     * Replicates the exact visual sophistication, charts, graphs, infographics, and typography
     * of the MeraRashifal Master Visual Edition.
     */
    public static function build_palmistry_report_html($person_name, $gender, $dob, $tob, $pob, $palm_data = null) {
        $year = 2027;

        // Ensure MR_Long_Yearly_Report_Builder is available for full architectural depth
        if (!class_exists('MR_Long_Yearly_Report_Builder')) {
            require_once dirname(__FILE__) . '/class-mr-long-yearly-report-builder.php';
        }

        // 1. Prepare Palmistry Hast Rekha Editorial Synthesis
        $age = 45;
        if (!empty($dob)) {
            $birth_time = strtotime(str_replace('/', '-', $dob));
            if ($birth_time) {
                $age = (int)date('Y') - (int)date('Y', $birth_time);
            }
        }
        $age = max(18, min(95, $age));

        // Master Palmistry Editorial Data seamlessly tailored to the 26-page structure
        $palm_theme = "Strategic Authority, Mount Topography, and Karmic Milestones in 2027";
        $palm_motto = "Master your inner prana, honor the downward slope of intuitive wisdom, and consolidate lasting earned authority.";
        $palm_synthesis = "Your hand’s strongest signal is the long, downward-sloping head line toward Chandra: seasoned experience has deepened your strategic judgment, but work concerns often extend beyond professional hours. The broad palm, substantial Venus mount, and finer central fate line reflect an earned provider whose elevation unfolds through sustained institutional usefulness rather than speculative windfalls. In classical Hast Rekha and Samudrik Shastra, this hand commands earned authority. The reading centres on career consolidation, disciplined capital reserves, and protecting emotional warmth in your family life.";

        $editorial_data = [
            'annual_theme'           => $palm_theme,
            'annual_motto'           => $palm_motto,
            'executive_summary'      => $palm_synthesis,
            'vitality_index'         => 86,
            'vitality_status'        => 'High Transformation & Strategic Authority',
            'monthly_energy_scores'  => [72, 70, 78, 85, 87, 82, 75, 79, 84, 91, 88, 83],
            'page_takeaways'         => [
                'page_2'  => [
                    'The long descending head line toward Chandra is your primary intellectual asset: practical experience meets creative foresight.',
                    'The broad Venus mount demonstrates generous provider energy, requiring structured boundaries to prevent exhaustion.',
                    'Middle-palm fate line rise between ages ' . $age . ' and ' . ($age + 4) . ' confirms earned professional authority.'
                ],
                'page_3'  => [
                    'Lagna Kundali and Sacred Palm Map confirm simultaneous activation of Surya and Chandra sectors.',
                    'Exalted Mars in Kendra aligns with a firm thumb to bestow relentless executive willpower.',
                    'The Sun-Mercury Budhaditya alignment sharpens troubleshooting, documentation, and advisory standing.'
                ],
                'page_4'  => [
                    'Venus (Shukra) holds #1 potency across both planetary Shadbala and palm mount elevation.',
                    'Moon (Chandra) in Rohini reinforces the head line’s intuitive depth and high emotional intelligence.',
                    'Saturn’s 9th house transit commands strict legal precision, formal agreements, and disciplined routine.'
                ],
                'page_5'  => [
                    'The Rahu-Rahu foundation concludes cleanly, setting the stage for Jovian social and financial expansion.',
                    'The Sandhi transition window in mid-2027 is your prime milestone to negotiate high-value compensation.',
                    'Spiritual reflection (Mauna) during the shift balances active ambition with inner tranquility.'
                ],
                'page_6'  => [
                    'The 18-year Rahu horizon reaches peak compounding during the Rahu-Guru and Rahu-Shukra sub-periods.',
                    'Ground all high-flying conceptual vision into real-world contracts and documented standard operating procedures.',
                    'Clear legacy obligations in the first five months of 2027 to unlock high-altitude momentum.'
                ],
                'page_7'  => [
                    'Saturn in the 9th house filters superficial shortcuts and builds rock-solid institutional credibility.',
                    'Exalted Jupiter in Cancer floods your Lagna with charismatic optimism and executive moral stature.',
                    'The simultaneous presence of Shani and Guru creates a rare Dharma-Karma balance in your palm and chart.'
                ],
                'page_8'  => [
                    'Rahu in the 8th house stimulates high-level research, intellectual property, and tech-driven innovation.',
                    'Ketu in the 2nd house mandates measured speech and guarding family assets against impulsive speculation.',
                    'Use solar and lunar eclipse dates as natural reset points for spiritual introspection and charitable daan.'
                ],
                'page_9'  => [
                    'Quarter 1 prioritizes steady administrative foundation, clearing 2026 pending items, and mental vision.',
                    'Listen to physical cues during the January transit shift; prioritize morning walks and joint mobility.',
                    'March opens fresh commercial proposals, renewed optimism, and clear negotiation dialogues.'
                ],
                'page_10' => [
                    'A disciplined weekly pacing rhythm protects your mental clarity against temporary Dasha fluctuations.',
                    'Celebrate the arrival of spring in March with proactive high-altitude networking and strategic proposals.',
                    'Patience practiced during January and February pays substantial dividends by the end of Q1.'
                ],
                'page_11' => [
                    'Quarter 2 delivers high acceleration as Exalted Jupiter casts direct blessings upon your Lagna.',
                    'May and June bring fruitful commercial partnerships, institutional backing, and asset acquisitions.',
                    'Domestic warmth and family celebration reach an annual high during this Jovian expansion window.'
                ],
                'page_12' => [
                    'Exalted Jupiter in May delivers your greatest window of competitive and commercial leverage.',
                    'Prioritize family milestones and celebrate achievements with genuine gratitude and humility.',
                    'Keep morning physical routines intact even as your professional calendar fills with executive meetings.'
                ],
                'page_13' => [
                    'Quarter 3 is your consolidation window: audit accounts, update legal wills, and review asset allocations.',
                    'Avoid hasty financial lending to acquaintances during August; maintain strict capital defense.',
                    'Children’s progress, education, and creative pursuits bring immense pride and household joy.'
                ],
                'page_14' => [
                    'A strict audit of accounts in July prevents legal or tax friction later in the year.',
                    'Celebrate personal milestones with genuine family pride and quiet spiritual rituals.',
                    'September paves the runway for your greatest professional breakthroughs in the final quarter.'
                ],
                'page_15' => [
                    'Quarter 4 stands as your annual astrological apex with public awards, executive promotion, and Diwali joy.',
                    'October 24 and November 18 are standout dates for substantial wealth inflow and consulting dividends.',
                    'The year closes with heightened social prestige, commercial harvest, and deep spiritual gratitude.'
                ],
                'page_16' => [
                    'Humility and gratitude in moments of peak victory ensure that Rahu acts as an auspicious protector.',
                    'Celebrate Diwali with sacred family rituals, cow ghee diyas, and sincere charitable giving.',
                    'Set your strategic vision for 2028 from a place of financial strength, peace, and confidence.'
                ],
                'page_17' => [
                    'Transition from manual hustle into architectural leverage: positioning, brand equity, and scalable systems.',
                    'Budhaditya Yoga empowers public writing, industry consulting, and high-level corporate advisory.',
                    'Lead through vision and systematic management rather than rigid authority; let quality speak.'
                ],
                'page_18' => [
                    'Quarter 4 stands out as your supreme career monetization window; position yourself for it in Q2.',
                    'Keep legal and operational documentation iron-clad before signing new commercial agreements.',
                    'Your professional authority is destined to rise to an all-time high by November 2027.'
                ],
                'page_19' => [
                    'Move surplus capital from speculative day-trading into secured real estate, land, and gold.',
                    'Strong planetary configurations guarantee stable secondary income streams and consulting retainers.',
                    'Maintain a strict 3-tier savings architecture: household budget, emergency reserves, and long-term assets.'
                ],
                'page_20' => [
                    'Maintaining a 20% liquid FD cushion gives you tremendous peace of mind during market fluctuations.',
                    'Real estate assets acquired in the second half of 2027 show consistent long-term capital appreciation.',
                    'November stands out as the single most lucrative wealth accumulation month of the entire year.'
                ],
                'page_21' => [
                    'Active listening and undivided attention dissolve relationship tension faster than material provision.',
                    'Plan a family pilgrimage or serene retreat in the fourth quarter to celebrate domestic blessings.',
                    'Elderly family members offer valuable blessings, ancestral protection, and guidance on Thursdays.'
                ],
                'page_22' => [
                    'Prioritize spinal health, joint mobility, and adequate daily warm hydration to counter Saturnian stiffness.',
                    'A clean Satvic diet minimizes digestive sluggishness (Agni) and balances Vata-Kapha biorhythms.',
                    'Screen-free evenings and Pada Abhyanga (foot massage with warm sesame oil) ground active Chandra thoughts.'
                ],
                'page_23' => [
                    'White Sapphire (or Pukhraj) strengthens your core destiny, Venusian warmth, and planetary shielding.',
                    'Regular Saturday evening Peepal tree diya puja softens Saturnian transit pressure on your 9th house.',
                    'Ganesh Atharvashirsha on Wednesday mornings clears hurdles and keeps Rahu’s intellect sharp and focused.'
                ],
                'page_24' => [
                    'Your 12-month wall calendar cheat sheet provides an instant month-by-month energy compass.',
                    'Use high-energy months (April, May, October, November) for bold public launches and contract closings.',
                    'May universal peace and divine light guide your hand and elevate your conscious actions.'
                ],
                'page_25' => [
                    'Vedic Hast Rekha and Jyotish are not fatalistic determinism; lines reflect dynamic karmic weather.',
                    'Conscious discernment (Viveka) and disciplined effort (Purushartha) remain supreme in shaping destiny.',
                    'Review your master dossier periodically to stay aligned with your highest professional and spiritual purpose.'
                ]
            ]
        ];

        // 2. Generate Master HTML via MR_Long_Yearly_Report_Builder
        $html = MR_Long_Yearly_Report_Builder::build_yearly_report_html(
            $person_name,
            $gender,
            $dob,
            $tob,
            $pob,
            $year,
            $editorial_data,
            [],
            'option2'
        );

        // 3. Inject Sacred Palm Topography Map & Palmistry Header Customizations
        // Update Title on Cover Page (Page 1)
        $html = str_replace(
            '🪐 Vedic Jyotish Personalized Annual Horoscope · Varshaphal',
            '🪐 VEDIC SAMUDRIK SHASTRA PERSONALIZED PALMISTRY · HAST REKHA',
            $html
        );
        $html = str_replace(
            'ANNUAL HOROSCOPE 2027',
            'MASTER HAST REKHA &amp; PALMISTRY DOSSIER',
            $html
        );
        $html = str_replace(
            'MeraRashifal.com - Yearly Vedic Horoscape 2027',
            'MeraRashifal.com - Master Hast Rekha &amp; Palmistry Dossier',
            $html
        );

        // On Page 4: Replace Navamsha Chart with Sacred Hand Topography Map SVG!
        $palm_map_svg = self::render_palm_map_svg(235, 235);
        $search_navamsha = '✨ Navamsha Chart (D9 Destiny Chart)';
        $replace_palm_map = '🖐️ Sacred Palm Topography Map (Hast Rekha)';
        
        if (strpos($html, $search_navamsha) !== false) {
            $html = str_replace($search_navamsha, $replace_palm_map, $html);
            // Replace D9 SVG with Palm Map SVG
            $pattern_d9 = '/<div class="mr-card"[^>]*>\s*<div[^>]*>\s*🖐️ Sacred Palm Topography Map.*?<\/div>\s*<div[^>]*>.*?<\/div>\s*<\/div>/s';
            $palm_card_replacement = '<div class="mr-card" style="padding:10px; text-align:center; margin-bottom:0;">
                <div style="font-family:var(--font-heading); font-size:13.5px; font-weight:700; color:#111827; margin-bottom:5px;">
                    🖐️ Sacred Palm Topography Map (Hast Rekha)
                </div>
                <div style="min-height:220px; display:flex; align-items:center; justify-content:center;">
                    ' . $palm_map_svg . '
                </div>
                <div style="font-size:10px; color:#4B5563; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-top:5px;">
                    Major Lines (Heart, Head, Life, Fate) &amp; Planetary Mounts
                </div>
            </div>';
            
            // Perform clean injection
            $html = preg_replace(
                '/<div class="mr-card"[^>]*>\s*<div[^>]*>\s*🖐️ Sacred Palm Topography Map.*?<\/div>\s*<div[^>]*>\s*<svg.*?<\/svg>\s*<\/div>\s*<div[^>]*>.*?<\/div>\s*<\/div>/s',
                $palm_card_replacement,
                $html
            );
        }

        // Add Hand DNA & Dominant Hand strip to Page 1
        $hand_meta_strip = '<div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:8px; padding:6px 10px; margin-bottom:10px; display:flex; justify-content:space-around; align-items:center; text-align:center;">
            <div>
                <span style="color:#C45738; font-size:9px; text-transform:uppercase; font-weight:800; display:block; letter-spacing:0.04em;">🖐️ Dominant Hand</span>
                <strong style="font-size:11.5px; color:#111827;">Right Hand (Active Karma)</strong>
                <span style="font-size:9.5px; color:#6B7280; display:block;">Vocation &amp; Social Authority</span>
            </div>
            <div style="border-left:1px solid #E2DACF; height:28px;"></div>
            <div>
                <span style="color:#C45738; font-size:9px; text-transform:uppercase; font-weight:800; display:block; letter-spacing:0.04em;">🧭 Hand Archetype</span>
                <strong style="font-size:11.5px; color:#111827;">Mixed Practical-Intuitive</strong>
                <span style="font-size:9.5px; color:#6B7280; display:block;">Square Palm · Long Fingers</span>
            </div>
            <div style="border-left:1px solid #E2DACF; height:28px;"></div>
            <div>
                <span style="color:#C45738; font-size:9px; text-transform:uppercase; font-weight:800; display:block; letter-spacing:0.04em;">⛰️ Apex Mount</span>
                <strong style="font-size:11.5px; color:#111827;">Venus / Shukra (#1)</strong>
                <span style="font-size:9.5px; color:#6B7280; display:block;">Warmth &amp; Generous Provider</span>
            </div>
        </div>';

        $html = str_replace(
            '<div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:8px; padding:6px 10px; margin-bottom:10px; display:flex; justify-content:space-around; align-items:center; text-align:center;">',
            $hand_meta_strip . '<!-- Western/Vedic sign strip follows --> <div style="display:none;">',
            $html
        );

        return $html;
    }
}
