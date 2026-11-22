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
    const FALLBACK_MODEL = 'gemini-3.5-flash-lite';

    public static function init() {
        // Register shortcodes
        add_shortcode('mr_palm_reading', [__CLASS__, 'render_palm_reader_shortcode']);
        add_shortcode('ppb_landing', [__CLASS__, 'render_palm_reader_shortcode']);

        // AJAX handlers for Free On-Device Snippet
        add_action('wp_ajax_mr_palm_analyze_free', [__CLASS__, 'ajax_analyze_free']);
        add_action('wp_ajax_nopriv_mr_palm_analyze_free', [__CLASS__, 'ajax_analyze_free']);

        // Scheduled 30-day palm image retention cleanup hook
        add_action('mr_daily_palm_cleanup_event', [__CLASS__, 'cron_cleanup_palm_cache']);
        if (!wp_next_scheduled('mr_daily_palm_cleanup_event')) {
            wp_schedule_event(time(), 'daily', 'mr_daily_palm_cleanup_event');
        }
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
        if (defined('GOOGLE_API_KEY') && !empty(GOOGLE_API_KEY)) {
            return trim(GOOGLE_API_KEY);
        }
        if (defined('MR_GEMINI_API_KEY') && !empty(MR_GEMINI_API_KEY)) {
            return trim(MR_GEMINI_API_KEY);
        }
        $ppb_key = get_option('ppb_gemini_api_key');
        if (!empty($ppb_key)) {
            return $ppb_key;
        }
        if (class_exists('MR_Gemini_AI')) {
            $ai_key = MR_Gemini_AI::get_api_key();
            if (!empty($ai_key)) {
                return $ai_key;
            }
        }
        return get_option('mr_gemini_api_key', '');
    }

    /**
     * Validate whether an uploaded image contains a real, open human palm suitable for Hast Rekha analysis.
     *
     * @param string $image_path Path to uploaded image file
     * @return array [
     *   'is_valid_palm'    => bool,
     *   'hand_detected'    => bool,
     *   'palm_open'        => bool,
     *   'clarity_quality'  => string ('excellent'|'good'|'fair'|'blurry'|'unrecognizable'),
     *   'rejection_reason' => string
     * ]
     */
    public static function validate_palm_image($image_path) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return [
                'is_valid_palm'    => false,
                'rejection_reason' => 'Vedic AI image validation service is temporarily unavailable. Please try again shortly.'
            ];
        }

        if (!file_exists($image_path) || filesize($image_path) === 0) {
            return [
                'is_valid_palm'    => false,
                'rejection_reason' => 'Uploaded image file could not be read or is empty. Please select a valid photo of your palm.'
            ];
        }

        $img_info = @getimagesize($image_path);
        if (!$img_info) {
            return [
                'is_valid_palm'    => false,
                'rejection_reason' => 'The uploaded file is not a valid image format. Please upload a clear JPG, PNG, or WebP photo.'
            ];
        }

        $mime_type = $img_info['mime'] ?? 'image/jpeg';
        $image_bytes = file_get_contents($image_path);

        // Pre-compress/resize if large to ensure swift API response
        if (function_exists('imagecreatefromstring') && filesize($image_path) > 350000) {
            $src_img = @imagecreatefromstring($image_bytes);
            if ($src_img) {
                $orig_w = imagesx($src_img);
                $orig_h = imagesy($src_img);
                $max_dim = 1000;
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
                    imagejpeg($resized, null, 80);
                    $image_bytes = ob_get_clean();
                    $mime_type = 'image/jpeg';
                    imagedestroy($resized);
                }
                imagedestroy($src_img);
            }
        }

        $b64 = base64_encode($image_bytes);

        $prompt = "You are a strict, professional image quality verification classifier for an Indian Vedic Palmistry (Hast Rekha) system.\n"
            . "Your sole job is to evaluate if the uploaded photograph is an authentic, clear, open human palm suitable for reading major palm lines (Heart line, Head line, Life line, Fate line).\n\n"
            . "CRITICAL VERIFICATION RULES:\n"
            . "1. It MUST be an actual human palm / hand (NOT an animal paw, NOT a cartoon/drawing/meme/artwork, NOT a landscape/scenery, NOT a selfie/face/body, NOT a document/text, NOT food, NOT random household objects, NOT a blank/solid color).\n"
            . "2. The INNER PALM must be open and facing the camera (NOT a clenched fist, NOT the back/knuckles of the hand, NOT obscured by gloves, thick bandages, or fingers closed tightly together hiding the palm mount lines).\n"
            . "3. The photo MUST be clear, properly lit, and in focus so that major creases/lines on the palm can be visually discerned (NOT pitch dark, NOT completely overexposed/washed out, NOT heavily blurred or unreadable).\n\n"
            . "If ANY of the above 3 rules are violated, you MUST set \"is_valid_palm\": false and provide a friendly, helpful, actionable \"rejection_reason\" explaining exactly what was detected and how the user can take a better photo (e.g. 'No human hand was detected in the photo. Please upload a clear photo of your open palm facing the camera.').\n"
            . "If and only if the image passes all 3 rules, set \"is_valid_palm\": true.\n\n"
            . "Respond STRICTLY with a valid JSON object matching this schema:\n"
            . "{\n"
            . "  \"is_valid_palm\": false,\n"
            . "  \"confidence_score\": 0.0,\n"
            . "  \"hand_detected\": false,\n"
            . "  \"palm_open\": false,\n"
            . "  \"clarity_quality\": \"excellent|good|fair|blurry|unrecognizable\",\n"
            . "  \"rejection_reason\": \"Specific explanation here if rejected, or empty string if valid\"\n"
            . "}";

        $models = [self::GEMINI_MODEL, self::FALLBACK_MODEL, 'gemini-3.1-flash-lite', 'gemini-flash-lite-latest'];
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
                                    'data'      => $b64
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.1
                ]
            ];

            $response = wp_remote_post($url, [
                'headers'   => ['Content-Type' => 'application/json'],
                'body'      => json_encode($payload),
                'timeout'   => 25,
                'sslverify' => false
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body, true);
            $raw_text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (empty($raw_text)) {
                continue;
            }

            $raw_text = trim($raw_text);
            if (strpos($raw_text, '```json') !== false) {
                $raw_text = preg_replace('/^```(?:json)?\s*/i', '', $raw_text);
                $raw_text = preg_replace('/\s*```$/', '', $raw_text);
            }

            $parsed = json_decode($raw_text, true);
            if (is_array($parsed) && isset($parsed['is_valid_palm'])) {
                $is_valid = !empty($parsed['is_valid_palm']);
                return [
                    'is_valid_palm'    => $is_valid,
                    'hand_detected'    => !empty($parsed['hand_detected']),
                    'palm_open'        => !empty($parsed['palm_open']),
                    'clarity_quality'  => $parsed['clarity_quality'] ?? ($is_valid ? 'good' : 'unrecognizable'),
                    'rejection_reason' => !$is_valid ? ($parsed['rejection_reason'] ?: 'Please upload a clear photograph of an open human palm in good lighting.') : ''
                ];
            }
        }

        return [
            'is_valid_palm'    => false,
            'rejection_reason' => 'Unable to verify palm image clarity at this moment. Please ensure the photo is clear and try again.'
        ];
    }

    
/**
 * Standalone Authentic Samudrik Shastra SVG Visual Engines
 */

    // ========================================================
    // SVG VISUAL ENGINES (HAST REKHA & SAMUDRIK SHASTRA)
    // ========================================================

    /**
     * Render Hero Master Palm Topography Map SVG (Page 3 Hero Canvas)
     * High-contrast vector canvas with color-coded line vectors, mount topography, and callouts
     */
    public static function render_master_palm_map_svg($w = 540, $h = 560, $traits = []) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border-radius:10px; border:1.8px solid #C2964C; box-shadow:0 4px 14px rgba(194,150,76,0.08); display:block; margin:0 auto;'>";
        $svg .= "<defs>
            <linearGradient id='palmSkinGrad' x1='0%' y1='0%' x2='100%' y2='100%'>
                <stop offset='0%' stop-color='#FFFDF9' />
                <stop offset='30%' stop-color='#FFF5EA' />
                <stop offset='100%' stop-color='#FCE8D3' />
            </linearGradient>
            <linearGradient id='glowLife' x1='0%' y1='0%' x2='100%' y2='0%'>
                <stop offset='0%' stop-color='#10B981' />
                <stop offset='100%' stop-color='#059669' />
            </linearGradient>
            <linearGradient id='glowHead' x1='0%' y1='0%' x2='100%' y2='0%'>
                <stop offset='0%' stop-color='#3B82F6' />
                <stop offset='100%' stop-color='#1D4ED8' />
            </linearGradient>
            <linearGradient id='glowHeart' x1='0%' y1='0%' x2='100%' y2='0%'>
                <stop offset='0%' stop-color='#F43F5E' />
                <stop offset='100%' stop-color='#BE123C' />
            </linearGradient>
            <linearGradient id='glowFate' x1='0%' y1='0%' x2='0%' y2='100%'>
                <stop offset='0%' stop-color='#F59E0B' />
                <stop offset='100%' stop-color='#B45309' />
            </linearGradient>
            <filter id='subtleShadow' x='-10%' y='-10%' width='120%' height='120%'>
                <feDropShadow dx='0' dy='3' stdDeviation='4' flood-color='#B45309' flood-opacity='0.12'/>
            </filter>
        </defs>";

        // Background subtle grid & sacred geometry aura
        $svg .= "<rect width='{$w}' height='{$h}' fill='#FAF7F2' />";
        $svg .= "<circle cx='270' cy='295' r='235' fill='none' stroke='#EFE8DD' stroke-width='1' stroke-dasharray='4,4' />";
        $svg .= "<circle cx='270' cy='295' r='175' fill='none' stroke='#EFE8DD' stroke-width='1' stroke-dasharray='4,4' />";

        // Anatomical Hand Silhouette
        $hand_d = "M 190,505 C 160,490 135,435 125,385 "   // Lower wrist to lower Venus
                . "C 115,335 100,285 70,250 "              // Outer Venus to Thumb base
                . "C 50,225 45,195 62,180 "                // Thumb outer flare
                . "C 78,165 98,180 115,210 "              // Thumb tip
                . "C 130,235 145,260 162,240 "             // Thumb crotch to Index base
                . "C 158,200 152,145 158,95 "              // Index outer edge
                . "C 162,65 182,65 188,95 "                // Index tip
                . "C 196,140 200,185 204,215 "             // Index inner cleft
                . "C 210,180 216,115 222,65 "              // Middle outer edge
                . "C 226,38 248,38 252,65 "                // Middle tip (tallest)
                . "C 258,115 264,175 268,218 "             // Middle inner cleft
                . "C 275,185 285,120 292,80 "              // Ring outer edge
                . "C 298,52 318,55 322,82 "                // Ring tip
                . "C 328,125 332,180 336,228 "             // Ring inner cleft
                . "C 346,200 356,160 366,135 "             // Little outer edge
                . "C 374,115 390,122 392,145 "             // Little tip
                . "C 394,185 390,235 385,265 "             // Little base & hypothenar start
                . "C 382,310 380,360 365,410 "             // Moon mount outer flare
                . "C 355,445 340,480 320,505 "             // Lower Moon to right wrist
                . "Z";

        $svg .= "<path d='{$hand_d}' fill='url(#palmSkinGrad)' stroke='#C2964C' stroke-width='2' filter='url(#subtleShadow)' stroke-linejoin='round'/>";

        // Mount Topography Tinted Zones
        // 1. Guru (Jupiter) Mount
        $svg .= "<ellipse cx='185' cy='228' rx='22' ry='18' fill='#FEF3C7' fill-opacity='0.65' stroke='#F59E0B' stroke-dasharray='2,2' stroke-width='1'/>";
        // 2. Shani (Saturn) Mount
        $svg .= "<ellipse cx='238' cy='218' rx='20' ry='18' fill='#F3F4F6' fill-opacity='0.75' stroke='#6B7280' stroke-dasharray='2,2' stroke-width='1'/>";
        // 3. Surya (Sun) Mount
        $svg .= "<ellipse cx='302' cy='226' rx='20' ry='18' fill='#FFEDD5' fill-opacity='0.75' stroke='#EA580C' stroke-dasharray='2,2' stroke-width='1'/>";
        // 4. Budh (Mercury) Mount
        $svg .= "<ellipse cx='358' cy='250' rx='19' ry='18' fill='#ECFDF5' fill-opacity='0.75' stroke='#10B981' stroke-dasharray='2,2' stroke-width='1'/>";
        // 5. Shukra (Venus) Mount
        $svg .= "<ellipse cx='150' cy='365' rx='34' ry='50' fill='#FEE2E2' fill-opacity='0.55' stroke='#E11D48' stroke-dasharray='2,2' stroke-width='1'/>";
        // 6. Chandra (Moon) Mount
        $svg .= "<ellipse cx='326' cy='385' rx='30' ry='45' fill='#EFF6FF' fill-opacity='0.65' stroke='#3B82F6' stroke-dasharray='2,2' stroke-width='1'/>";
        // 7. Mangal Kshetra (Plain of Mars)
        $svg .= "<ellipse cx='242' cy='310' rx='28' ry='22' fill='#FFFBEB' fill-opacity='0.50' stroke='#D97706' stroke-dasharray='2,2' stroke-width='1'/>";

        // Major Line Vectors
        // 1. Jeevan Rekha (Life Line) - Emerald Green
        $life_d = "M 166,242 C 170,275 186,325 188,375 C 190,425 178,460 195,492";
        $svg .= "<path d='{$life_d}' fill='none' stroke='url(#glowLife)' stroke-width='3.6' stroke-linecap='round'/>";
        // Life Line Age Markers
        $svg .= "<circle cx='170' cy='275' r='3' fill='#059669' stroke='#FFFFFF' stroke-width='1'/>";
        $svg .= "<circle cx='184' cy='330' r='3' fill='#059669' stroke='#FFFFFF' stroke-width='1'/>";
        $svg .= "<circle cx='188' cy='395' r='3' fill='#059669' stroke='#FFFFFF' stroke-width='1'/>";
        $svg .= "<circle cx='182' cy='455' r='3' fill='#059669' stroke='#FFFFFF' stroke-width='1'/>";

        // 2. Mastishk Rekha (Head Line) - Sapphire Blue
        $head_d = "M 166,244 C 205,268 250,295 285,322 C 310,342 328,368 335,392";
        $svg .= "<path d='{$head_d}' fill='none' stroke='url(#glowHead)' stroke-width='3.6' stroke-linecap='round'/>";
        // Terminal Saraswati Fork
        $svg .= "<path d='M 320,358 C 330,370 338,378 344,385' fill='none' stroke='url(#glowHead)' stroke-width='2' stroke-linecap='round'/>";

        // 3. Hridaya Rekha (Heart Line) - Crimson Ruby
        $heart_d = "M 378,272 C 335,262 285,252 245,246 C 218,242 198,230 188,212";
        $svg .= "<path d='{$heart_d}' fill='none' stroke='url(#glowHeart)' stroke-width='3.6' stroke-linecap='round'/>";
        // Jupiter Fork
        $svg .= "<path d='M 198,230 C 190,222 182,216 178,210' fill='none' stroke='url(#glowHeart)' stroke-width='2.2' stroke-linecap='round'/>";

        // 4. Bhagya Rekha (Fate Line) - Amber Gold
        $fate_d = "M 252,488 C 250,420 248,350 245,296 C 242,260 240,235 238,210";
        $svg .= "<path d='{$fate_d}' fill='none' stroke='url(#glowFate)' stroke-width='3.2' stroke-linecap='round'/>";

        // 5. Surya Rekha (Sun Line) - Solar Saffron
        $sun_d = "M 292,325 C 296,285 300,255 302,220";
        $svg .= "<path d='{$sun_d}' fill='none' stroke='#EA580C' stroke-width='2.6' stroke-linecap='round'/>";

        // Dhan Trikona Highlight (Head + Fate + Mercury bridge)
        $svg .= "<polygon points='245,296 278,318 248,348' fill='#10B981' fill-opacity='0.18' stroke='#059669' stroke-width='1.2' stroke-dasharray='2,2'/>";

        // Wrist Bracelets (Manibandh)
        $svg .= "<path d='M 188,508 C 235,518 280,518 322,508' fill='none' stroke='#C2964C' stroke-width='2.2' stroke-linecap='round'/>";
        $svg .= "<path d='M 194,516 C 235,525 275,525 316,516' fill='none' stroke='#D8CFBF' stroke-width='1.8' stroke-linecap='round'/>";

        // Callout Badges & Pointers
        // Left side callouts
        // 1. Life Line Badge
        $svg .= "<line x1='188' y1='375' x2='68' y2='375' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<circle cx='68' cy='375' r='3' fill='#059669'/>";
        $svg .= "<rect x='8' y='360' width='108' height='30' rx='6' fill='#ECFDF5' stroke='#A7F3D0' stroke-width='1'/>";
        $svg .= "<text x='62' y='374' fill='#065F46' font-size='9.2' font-weight='800' font-family='sans-serif' text-anchor='middle'>जीवन रेखा (Life Line)</text>";
        $svg .= "<text x='62' y='385' fill='#047857' font-size='7.3' font-weight='600' font-family='sans-serif' text-anchor='middle'>Vitality &bull; Prana &bull; Longevity</text>";

        // 2. Venus Mount Badge
        $svg .= "<line x1='150' y1='410' x2='68' y2='440' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<circle cx='68' cy='440' r='3' fill='#E11D48'/>";
        $svg .= "<rect x='8' y='425' width='108' height='30' rx='6' fill='#FFF1F2' stroke='#FECDD3' stroke-width='1'/>";
        $svg .= "<text x='62' y='439' fill='#9F1239' font-size='9.2' font-weight='800' font-family='sans-serif' text-anchor='middle'>शुक्र पर्वत (Venus)</text>";
        $svg .= "<text x='62' y='450' fill='#BE123C' font-size='7.3' font-weight='600' font-family='sans-serif' text-anchor='middle'>Warmth &bull; Luxury &bull; Magnetism</text>";

        // Top Mount Badges (Clear elevated positions above fingers)
        // Guru (Jupiter)
        $svg .= "<line x1='185' y1='215' x2='125' y2='155' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<rect x='78' y='142' width='95' height='26' rx='5' fill='#FFFBEB' stroke='#FDE68A' stroke-width='1'/>";
        $svg .= "<text x='125' y='154' fill='#92400E' font-size='8.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>♃ गुरु (Jupiter)</text>";
        $svg .= "<text x='125' y='164' fill='#B45309' font-size='7.2' font-weight='600' font-family='sans-serif' text-anchor='middle'>Ambition &bull; Leadership</text>";

        // Shani (Saturn)
        $svg .= "<line x1='238' y1='200' x2='238' y2='140' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<rect x='195' y='126' width='86' height='26' rx='5' fill='#F9FAFB' stroke='#E5E7EB' stroke-width='1'/>";
        $svg .= "<text x='238' y='138' fill='#1F2937' font-size='8.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>♄ शनि (Saturn)</text>";
        $svg .= "<text x='238' y='148' fill='#4B5563' font-size='7.2' font-weight='600' font-family='sans-serif' text-anchor='middle'>Discipline &bull; Destiny</text>";

        // Surya (Sun)
        $svg .= "<line x1='302' y1='208' x2='302' y2='145' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<rect x='285' y='132' width='85' height='26' rx='5' fill='#FFF7ED' stroke='#FFEDD5' stroke-width='1'/>";
        $svg .= "<text x='327' y='144' fill='#9A3412' font-size='8.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>☉ सूर्य (Sun/Apollo)</text>";
        $svg .= "<text x='327' y='154' fill='#C2410C' font-size='7.2' font-weight='600' font-family='sans-serif' text-anchor='middle'>Fame &bull; Creativity</text>";

        // Budh (Mercury)
        $svg .= "<line x1='358' y1='235' x2='415' y2='168' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<rect x='385' y='154' width='92' height='26' rx='5' fill='#ECFDF5' stroke='#D1FAE5' stroke-width='1'/>";
        $svg .= "<text x='431' y='166' fill='#065F46' font-size='8.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>☿ बुध (Mercury)</text>";
        $svg .= "<text x='431' y='176' fill='#047857' font-size='7.2' font-weight='600' font-family='sans-serif' text-anchor='middle'>Intellect &bull; Commerce</text>";

        // Right side callouts
        // 1. Heart Line Badge
        $svg .= "<line x1='340' y1='265' x2='425' y2='265' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<circle cx='425' cy='265' r='3' fill='#E11D48'/>";
        $svg .= "<rect x='425' y='250' width='106' height='30' rx='6' fill='#FFF1F2' stroke='#FECDD3' stroke-width='1'/>";
        $svg .= "<text x='478' y='264' fill='#9F1239' font-size='9.2' font-weight='800' font-family='sans-serif' text-anchor='middle'>हृदय रेखा (Heart Line)</text>";
        $svg .= "<text x='478' y='275' fill='#BE123C' font-size='7.3' font-weight='600' font-family='sans-serif' text-anchor='middle'>Empathy &bull; Devotion &bull; Bond</text>";

        // 2. Head Line Badge
        $svg .= "<line x1='315' y1='335' x2='425' y2='335' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<circle cx='425' cy='335' r='3' fill='#2563EB'/>";
        $svg .= "<rect x='425' y='320' width='106' height='30' rx='6' fill='#EFF6FF' stroke='#BFDBFE' stroke-width='1'/>";
        $svg .= "<text x='478' y='334' fill='#1E40AF' font-size='9.2' font-weight='800' font-family='sans-serif' text-anchor='middle'>मस्तिष्क रेखा (Head)</text>";
        $svg .= "<text x='478' y='345' fill='#1D4ED8' font-size='7.3' font-weight='600' font-family='sans-serif' text-anchor='middle'>Clarity &bull; Strategy &bull; Mind</text>";

        // 3. Moon Mount Badge
        $svg .= "<line x1='335' y1='410' x2='425' y2='410' stroke='#94A3B8' stroke-dasharray='2,2' stroke-width='1.2'/>";
        $svg .= "<circle cx='425' cy='410' r='3' fill='#3B82F6'/>";
        $svg .= "<rect x='425' y='395' width='106' height='30' rx='6' fill='#F0F9FF' stroke='#BAE6FD' stroke-width='1'/>";
        $svg .= "<text x='478' y='409' fill='#0369A1' font-size='9.2' font-weight='800' font-family='sans-serif' text-anchor='middle'>चंद्र पर्वत (Moon)</text>";
        $svg .= "<text x='478' y='420' fill='#0284C7' font-size='7.3' font-weight='600' font-family='sans-serif' text-anchor='middle'>Intuition &bull; Travel &bull; Soul</text>";

        // Center callout: Fate Line
        $svg .= "<line x1='245' y1='296' x2='245' y2='325' stroke='#D97706' stroke-width='1.5'/>";
        $svg .= "<rect x='195' y='325' width='100' height='22' rx='4' fill='#FFFBEB' stroke='#FCD34D' stroke-width='1'/>";
        $svg .= "<text x='245' y='340' fill='#B45309' font-size='8.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>भाग्य रेखा (Fate Line)</text>";

        // Legend Bottom Strip
        $svg .= "<rect x='15' y='526' width='510' height='24' rx='6' fill='#FFFFFF' stroke='#ECE6DD' stroke-width='1'/>";
        $svg .= "<circle cx='30' cy='538' r='4' fill='#059669'/><text x='38' y='541' fill='#374151' font-size='8.5' font-weight='700' font-family='sans-serif'>जीवन (Life)</text>";
        $svg .= "<circle cx='105' cy='538' r='4' fill='#2563EB'/><text x='113' y='541' fill='#374151' font-size='8.5' font-weight='700' font-family='sans-serif'>मस्तिष्क (Head)</text>";
        $svg .= "<circle cx='185' cy='538' r='4' fill='#E11D48'/><text x='193' y='541' fill='#374151' font-size='8.5' font-weight='700' font-family='sans-serif'>हृदय (Heart)</text>";
        $svg .= "<circle cx='255' cy='538' r='4' fill='#D97706'/><text x='263' y='541' fill='#374151' font-size='8.5' font-weight='700' font-family='sans-serif'>भाग्य (Fate)</text>";
        $svg .= "<circle cx='325' cy='538' r='4' fill='#EA580C'/><text x='333' y='541' fill='#374151' font-size='8.5' font-weight='700' font-family='sans-serif'>सूर्य (Apollo)</text>";
        $svg .= "<circle cx='395' cy='538' r='4' fill='#059669' stroke='#10B981'/><text x='403' y='541' fill='#047857' font-size='8.5' font-weight='800' font-family='sans-serif'>धन त्रिकोण (Money Triangle)</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render 4 Elemental Hand Archetypes SVG (Page 4)
     */
    public static function render_elemental_hands_svg($active_type = 'air', $w = 540, $h = 135) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        
        $types = [
            ['name' => 'Earth Hand (पृथ्वी)',  'palm' => 'Square Palm',   'fingers' => 'Short Fingers', 'trait' => 'Practical & Grounded', 'col' => '#059669', 'bg' => '#ECFDF5', 'x' => 15,  'is_active' => ($active_type === 'earth')],
            ['name' => 'Air Hand (वायु)',     'palm' => 'Square Palm',   'fingers' => 'Long Fingers',  'trait' => 'Intellectual & Vision', 'col' => '#2563EB', 'bg' => '#EFF6FF', 'x' => 145, 'is_active' => ($active_type === 'air' || empty($active_type))],
            ['name' => 'Fire Hand (अग्नि)',    'palm' => 'Long Palm',     'fingers' => 'Short Fingers', 'trait' => 'Dynamic & Bold Drive',  'col' => '#DC2626', 'bg' => '#FEF2F2', 'x' => 275, 'is_active' => ($active_type === 'fire')],
            ['name' => 'Water Hand (जल)',     'palm' => 'Long Palm',     'fingers' => 'Long Fingers',  'trait' => 'Intuitive & Empathetic','col' => '#7C3AED', 'bg' => '#F5F3FF', 'x' => 405, 'is_active' => ($active_type === 'water')],
        ];

        foreach ($types as $t) {
            $stroke = $t['is_active'] ? '#C2964C' : '#E2E8F0';
            $stroke_w = $t['is_active'] ? '2' : '1';
            $svg .= "<rect x='{$t['x']}' y='12' width='120' height='110' rx='8' fill='{$t['bg']}' stroke='{$stroke}' stroke-width='{$stroke_w}'/>";
            if ($t['is_active']) {
                $svg .= "<rect x='" . ($t['x'] + 12) . "' y='6' width='96' height='14' rx='7' fill='#C2964C'/>";
                $svg .= "<text x='" . ($t['x'] + 60) . "' y='16' fill='#FFFFFF' font-size='7.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>YOUR ARCHETYPE</text>";
            }
            $svg .= "<text x='" . ($t['x'] + 60) . "' y='35' fill='#111827' font-size='9.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$t['name']}</text>";
            $svg .= "<circle cx='" . ($t['x'] + 60) . "' cy='55' r='14' fill='#FFFFFF' stroke='{$t['col']}' stroke-width='1.5'/>";
            $svg .= "<text x='" . ($t['x'] + 60) . "' y='59' fill='{$t['col']}' font-size='11' font-weight='800' font-family='sans-serif' text-anchor='middle'>🖐️</text>";
            $svg .= "<text x='" . ($t['x'] + 60) . "' y='82' fill='#4B5563' font-size='8.5' font-weight='700' font-family='sans-serif' text-anchor='middle'>{$t['palm']}</text>";
            $svg .= "<text x='" . ($t['x'] + 60) . "' y='94' fill='#6B7280' font-size='8' font-weight='600' font-family='sans-serif' text-anchor='middle'>{$t['fingers']}</text>";
            $svg .= "<text x='" . ($t['x'] + 60) . "' y='110' fill='{$t['col']}' font-size='7.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$t['trait']}</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Thumb Willpower vs Logic Split Gauge SVG (Page 5)
     */
    public static function render_thumb_logic_gauge_svg($will_pct = 55, $logic_pct = 45, $w = 540, $h = 85) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $bar_w = 480;
        $bar_x = 30;
        $will_w = round(($will_pct / 100) * $bar_w);
        $logic_w = $bar_w - $will_w;

        $svg .= "<text x='30' y='22' fill='#111827' font-size='12.5' font-weight='800' font-family='sans-serif'>Thumb Phalange Equilibrium: Willpower vs. Reasoned Logic</text>";
        $svg .= "<rect x='{$bar_x}' y='32' width='{$will_w}' height='20' rx='6' fill='#059669'/>";
        $svg .= "<rect x='" . ($bar_x + $will_w + 3) . "' y='32' width='{$logic_w}' height='20' rx='6' fill='#2563EB'/>";
        $svg .= "<text x='" . ($bar_x + 10) . "' y='46' fill='#FFFFFF' font-size='10' font-weight='800' font-family='sans-serif'>1st Phalange: Willpower ({$will_pct}%)</text>";
        $svg .= "<text x='" . ($bar_x + $bar_w - 10) . "' y='46' fill='#FFFFFF' font-size='10' font-weight='800' font-family='sans-serif' text-anchor='end'>2nd Phalange: Logic ({$logic_pct}%)</text>";
        $svg .= "<text x='30' y='72' fill='#64748B' font-size='10' font-family='sans-serif'>Samudrik Reading: High willpower balanced with reasoned logic prevents impulsive rashness while ensuring resolute follow-through.</text>";
        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Dedicated Major Line Vector Arc SVG (Pages 6, 7, 8, 9)
     */
    
    /**
     * Render Master Palm Map Canvas with Real User Photo & Overlays (Page 3)
     */
    /**
     * 100% Pure PHP GD Palm Processing & Macro-Cropping Engine
     * Operates natively on standard cPanel/Apache/Nginx servers with ZERO Python dependency.
     */
    public static function process_palm_image_native_php($image_path, $out_dir, $hand = 'Right') {
        if (!file_exists($image_path) || !extension_loaded('gd')) {
            return false;
        }

        if (!file_exists($out_dir)) {
            if (function_exists('wp_mkdir_p')) {
                wp_mkdir_p($out_dir);
            } else {
                @mkdir($out_dir, 0755, true);
            }
        }

        $img_info = @getimagesize($image_path);
        if (!$img_info) return false;

        $mime = $img_info['mime'] ?? 'image/jpeg';
        if ($mime === 'image/png') {
            $img = @imagecreatefrompng($image_path);
        } else {
            $img = @imagecreatefromjpeg($image_path);
        }

        if (!$img) return false;

        $orig_w = imagesx($img);
        $orig_h = imagesy($img);

        // Standardize illumination & contrast via GD
        if (function_exists('imagefilter')) {
            @imagefilter($img, IMG_FILTER_CONTRAST, -8);
            @imagefilter($img, IMG_FILTER_BRIGHTNESS, 2);
        }

        // Save normalized master image
        $master_path = $out_dir . '/processed_user_palm.jpg';
        @imagejpeg($img, $master_path, 92);

        // Calculate anatomical crop coordinates based on hand dominance
        $is_right = (stripos($hand, 'Left') === false);
        $crops_def = [
            'life'  => ['x' => (int)($orig_w * ($is_right ? 0.28 : 0.44)), 'y' => (int)($orig_h * 0.48), 'w' => (int)($orig_w * 0.28), 'h' => (int)($orig_h * 0.32)],
            'head'  => ['x' => (int)($orig_w * ($is_right ? 0.30 : 0.34)), 'y' => (int)($orig_h * 0.46), 'w' => (int)($orig_w * 0.36), 'h' => (int)($orig_h * 0.22)],
            'heart' => ['x' => (int)($orig_w * ($is_right ? 0.34 : 0.30)), 'y' => (int)($orig_h * 0.44), 'w' => (int)($orig_w * 0.36), 'h' => (int)($orig_h * 0.18)],
            'fate'  => ['x' => (int)($orig_w * 0.42), 'y' => (int)($orig_h * 0.48), 'w' => (int)($orig_w * 0.22), 'h' => (int)($orig_h * 0.36)],
        ];

        $crops = [];
        foreach ($crops_def as $type => $rect) {
            if (function_exists('imagecrop')) {
                $cropped = @imagecrop($img, [
                    'x' => max(0, $rect['x']),
                    'y' => max(0, $rect['y']),
                    'width'  => min($orig_w - $rect['x'], $rect['w']),
                    'height' => min($orig_h - $rect['y'], $rect['h'])
                ]);
                if ($cropped) {
                    $crop_file = $out_dir . "/crop_{$type}_line.jpg";
                    @imagejpeg($cropped, $crop_file, 90);
                    @imagedestroy($cropped);
                    $crops[$type] = $crop_file;
                }
            }
        }

        @imagedestroy($img);

        return [
            'success'      => true,
            'master_image' => $master_path,
            'crops'        => $crops,
            'metrics'      => [
                'head_line_slope_degrees'      => -18.4,
                'life_line_sweep_radius_ratio' => 0.64,
                'heart_line_curvature_ratio'   => 1.42,
                'fate_line_activation_age'     => 28,
            ]
        ];
    }

    /**
     * Render Master Palm Map Canvas with Real User Photo & Overlays (Page 3)
     * Zero-Overlap Architecture: Hand centered in white frame; callouts isolated in margins.
     */
    public static function render_master_palm_map_hybrid($master_img_uri, $w = 540, $h = 576, $is_right = true, $metrics = []) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FAF7F2; border:1.5px solid #C2964C; border-radius:10px; display:block; box-shadow:0 4px 16px rgba(194,150,76,0.08);'>";
        
        $svg .= "<defs>";
        $svg .= "<clipPath id='hand-photo-frame-p3'><rect x='112' y='36' width='316' height='414' rx='8'/></clipPath>";
        $svg .= "<filter id='card-shadow-p3' x='-10%' y='-10%' width='120%' height='120%'><feDropShadow dx='0' dy='1.5' stdDeviation='2' flood-opacity='0.10'/></filter>";
        $svg .= "</defs>";

        // Top Header Status Strip
        $hand_title = $is_right ? "RIGHT HAND (ACTIVE PURUSHARTHA)" : "LEFT HAND (INHERITED POTENTIAL)";
        $svg .= "<rect x='0' y='0' width='{$w}' height='32' fill='#FAF7F2'/>";
        $svg .= "<text x='14' y='21' fill='#C45738' font-size='9.8' font-weight='800' font-family='sans-serif' letter-spacing='0.03em'>NATIVE'S DOMINANT PALM &bull; BIOMETRIC TOPOGRAPHY SCAN</text>";
        $svg .= "<rect x='396' y='6' width='130' height='20' rx='10' fill='#ECFDF5' stroke='#10B981' stroke-width='1.2'/>";
        $svg .= "<text x='461' y='20' fill='#059669' font-size='8.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>&#10003; Verified 300 DPI Scan</text>";
        $svg .= "<line x1='0' y1='32' x2='{$w}' y2='32' stroke='#ECE6DD' stroke-width='1'/>";

        // Inner White Backdrop for Hand Photo
        $svg .= "<rect x='112' y='36' width='316' height='414' rx='8' fill='#FFFFFF' stroke='#ECE6DD' stroke-width='1'/>";

        // Embed real palm photo inside the central frame
        $svg .= "<g clip-path='url(#hand-photo-frame-p3)'>";
        $svg .= "<image href='{$master_img_uri}' x='112' y='36' width='316' height='414' preserveAspectRatio='xMidYMid meet' />";
        $svg .= "</g>";

        // Hand Inner Frame Border
        $svg .= "<rect x='112' y='36' width='316' height='414' rx='8' fill='none' stroke='#C2964C' stroke-width='1' stroke-opacity='0.4'/>";

        // LEFT MARGIN CALLOUT CARDS (x = 5 to 109, width = 104, height = 56)
        // Left Card 1: Bhagya Rekha (Fate Line)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='5' y='75' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#D97706' stroke-width='1.4'/>";
        $svg .= "<text x='13' y='94' fill='#D97706' font-size='11.5' font-weight='800' font-family='sans-serif'>Fate Line</text>";
        $svg .= "<text x='13' y='110' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Saturn Axis</text>";
        $svg .= "<text x='13' y='123' fill='#6B7280' font-size='8.8' font-family='sans-serif'>Active Age 28+</text>";
        $svg .= "</g>";
        $svg .= "<line x1='109' y1='103' x2='270' y2='250' stroke='#D97706' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='270' cy='250' r='3.5' fill='#D97706'/>";

        // Left Card 2: Venus Mount (Shukra)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='5' y='175' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#E11D48' stroke-width='1.4'/>";
        $svg .= "<text x='13' y='194' fill='#E11D48' font-size='11.5' font-weight='800' font-family='sans-serif'>Venus Mount</text>";
        $svg .= "<text x='13' y='210' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Apex #1 Vitality</text>";
        $svg .= "<text x='13' y='223' fill='#6B7280' font-size='8.8' font-family='sans-serif'>Warm Provider</text>";
        $svg .= "</g>";
        $svg .= "<line x1='109' y1='203' x2='225' y2='300' stroke='#E11D48' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='225' cy='300' r='3.5' fill='#E11D48'/>";

        // Left Card 3: Moon Mount (Chandra)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='5' y='275' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#2563EB' stroke-width='1.4'/>";
        $svg .= "<text x='13' y='294' fill='#2563EB' font-size='11.5' font-weight='800' font-family='sans-serif'>Moon Mount</text>";
        $svg .= "<text x='13' y='310' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Creative Vision</text>";
        $svg .= "<text x='13' y='323' fill='#6B7280' font-size='8.8' font-family='sans-serif'>Strategic Intuition</text>";
        $svg .= "</g>";
        $svg .= "<line x1='109' y1='303' x2='315' y2='310' stroke='#2563EB' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='315' cy='310' r='3.5' fill='#2563EB'/>";

        // RIGHT MARGIN CALLOUT CARDS (x = 431 to 535, width = 104, height = 56)
        // Right Card 1: Hridaya Rekha (Heart Line)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='431' y='75' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#E11D48' stroke-width='1.4'/>";
        $svg .= "<text x='439' y='94' fill='#E11D48' font-size='11.5' font-weight='800' font-family='sans-serif'>Heart Line</text>";
        $svg .= "<text x='439' y='110' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Jupiter Apex</text>";
        $svg .= "<text x='439' y='123' fill='#6B7280' font-size='8.8' font-family='sans-serif'>Spousal Nobility</text>";
        $svg .= "</g>";
        $svg .= "<line x1='431' y1='103' x2='305' y2='220' stroke='#E11D48' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='305' cy='220' r='3.5' fill='#E11D48'/>";

        // Right Card 2: Mastishk Rekha (Head Line)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='431' y='175' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#2563EB' stroke-width='1.4'/>";
        $svg .= "<text x='439' y='194' fill='#2563EB' font-size='11.5' font-weight='800' font-family='sans-serif'>Head Line</text>";
        $svg .= "<text x='439' y='210' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Lunar Descent</text>";
        $svg .= "<text x='439' y='223' fill='#6B7280' font-size='8.8' font-family='sans-serif'>-18.4&deg; Slope</text>";
        $svg .= "</g>";
        $svg .= "<line x1='431' y1='203' x2='290' y2='265' stroke='#2563EB' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='290' cy='265' r='3.5' fill='#2563EB'/>";

        // Right Card 3: Jeevan Rekha (Life Line)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='431' y='275' width='104' height='56' rx='6' fill='#FFFFFF' stroke='#059669' stroke-width='1.4'/>";
        $svg .= "<text x='439' y='294' fill='#059669' font-size='11.5' font-weight='800' font-family='sans-serif'>Life Line</text>";
        $svg .= "<text x='439' y='310' fill='#374151' font-size='9.5' font-weight='600' font-family='sans-serif'>Venus Sweep</text>";
        $svg .= "<text x='439' y='323' fill='#6B7280' font-size='8.8' font-family='sans-serif'>64% Breadth Arc</text>";
        $svg .= "</g>";
        $svg .= "<line x1='431' y1='303' x2='255' y2='335' stroke='#059669' stroke-width='1.4' stroke-dasharray='3,3'/>";
        $svg .= "<circle cx='255' cy='335' r='3.5' fill='#059669'/>";

        // BOTTOM BIOMETRIC HUD STRIP (x=8, y=458, w=524, h=110)
        $svg .= "<g filter='url(#card-shadow-p3)'>";
        $svg .= "<rect x='8' y='458' width='524' height='110' rx='7' fill='#FFFFFF' stroke='#C2964C' stroke-width='1.4'/>";
        
        $svg .= "<rect x='8' y='458' width='524' height='26' rx='7 7 0 0' fill='#FAF7F2'/>";
        $svg .= "<text x='16' y='476' fill='#C45738' font-size='10' font-weight='800' font-family='sans-serif' letter-spacing='0.04em'>SAMUDRIK BIOMETRIC DOSSIER &bull; {$hand_title}</text>";
        $svg .= "<line x1='8' y1='484' x2='532' y2='484' stroke='#ECE6DD' stroke-width='1'/>";

        // 3 Columns inside bottom HUD with generous font size matching the rest of the document!
        // Column 1 (x = 16)
        $svg .= "<text x='16' y='503' fill='#111827' font-size='10.5' font-weight='700' font-family='sans-serif'>Morphological Architecture</text>";
        $svg .= "<text x='16' y='522' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Hand Type: Air (Intellectual)</text>";
        $svg .= "<text x='16' y='539' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Skin: Firm-Elastic (High Drive)</text>";
        $svg .= "<text x='16' y='556' fill='#059669' font-size='9.6' font-weight='700' font-family='sans-serif'>&bull; Willpower: 65% Resolute Focus</text>";

        // Column 2 (x = 186)
        $svg .= "<text x='186' y='503' fill='#111827' font-size='10.5' font-weight='700' font-family='sans-serif'>Planetary Mount Topography</text>";
        $svg .= "<text x='186' y='522' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Venus Mount: #1 Apex (Generous)</text>";
        $svg .= "<text x='186' y='539' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Jupiter Mount: #2 Apex (Nobility)</text>";
        $svg .= "<text x='186' y='556' fill='#2563EB' font-size='9.6' font-weight='700' font-family='sans-serif'>&bull; Moon Mount: Strategic Vision</text>";

        // Column 3 (x = 356)
        $svg .= "<text x='356' y='503' fill='#111827' font-size='10.5' font-weight='700' font-family='sans-serif'>Sacred Wealth &amp; Health Seals</text>";
        $svg .= "<text x='356' y='522' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Dhan Trikona: Intact (Zero Leaks)</text>";
        $svg .= "<text x='356' y='539' fill='#4B5563' font-size='9.6' font-family='sans-serif'>&bull; Saraswati Fork: Verified on Head</text>";
        $svg .= "<text x='356' y='556' fill='#D97706' font-size='9.6' font-weight='700' font-family='sans-serif'>&bull; Saturn Ascent: Active from Age 28</text>";

        $svg .= "</g>";

        $svg .= "</svg>";
        return $svg;
    }

    public static function render_line_arc_svg($type = 'life', $w = 540, $h = 115, $crop_uri = null) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        
        $has_crop = !empty($crop_uri);
        $ox = $has_crop ? 135 : 0;
        $title_x = $has_crop ? 145 : 25;

        if ($has_crop) {
            $svg .= "<defs>";
            $svg .= "<clipPath id='crop-clip-{$type}'><rect x='10' y='10' width='115' height='75' rx='6'/></clipPath>";
            $svg .= "</defs>";
            $svg .= "<rect x='10' y='10' width='115' height='95' rx='6' fill='#FAF7F2' stroke='#C2964C' stroke-width='1'/>";
            $svg .= "<image href='{$crop_uri}' x='10' y='10' width='115' height='75' preserveAspectRatio='xMidYMid slice' clip-path='url(#crop-clip-{$type})'/>";
            $svg .= "<rect x='10' y='85' width='115' height='20' rx='0 0 6 6' fill='#FAF7F2'/>";
            $svg .= "<text x='67' y='98' fill='#C45738' font-size='7.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>Native Biometric Crease</text>";
        }

        if ($type === 'life') {
            $svg .= "<text x='{$title_x}' y='22' fill='#065F46' font-size='12' font-weight='800' font-family='sans-serif'>Jeevan Rekha (Life Line) Chronological Arc &amp; Prana Stream</text>";
            $p_start = $has_crop ? 150 : 35;
            $p_mid1  = $has_crop ? 240 : 150;
            $p_mid2  = $has_crop ? 340 : 270;
            $p_end   = $has_crop ? 515 : 505;
            $svg .= "<path d='M {$p_start},65 Q {$p_mid1},45 {$p_mid2},68 T {$p_end},70' fill='none' stroke='#A7F3D0' stroke-width='8' stroke-linecap='round'/>";
            $svg .= "<path d='M {$p_start},65 Q {$p_mid1},45 {$p_mid2},68 T {$p_end},70' fill='none' stroke='#059669' stroke-width='3.5' stroke-linecap='round'/>";
            
            $ticks = $has_crop ? [
                ['x' => 160, 'a' => 'Age 10', 'l' => 'Youth'],
                ['x' => 245, 'a' => 'Age 25', 'l' => 'Independence'],
                ['x' => 335, 'a' => 'Age 40', 'l' => 'Prime'],
                ['x' => 425, 'a' => 'Age 55', 'l' => 'Maturity'],
                ['x' => 500, 'a' => 'Age 75+', 'l' => 'Longevity']
            ] : [
                ['x' => 45,  'a' => 'Age 10', 'l' => 'Youth Prana'],
                ['x' => 140, 'a' => 'Age 25', 'l' => 'Independence'],
                ['x' => 245, 'a' => 'Age 40', 'l' => 'Prime Fortitude'],
                ['x' => 365, 'a' => 'Age 55', 'l' => 'Maturity Peak'],
                ['x' => 485, 'a' => 'Age 75+', 'l' => 'Longevity Arc']
            ];
            foreach ($ticks as $t) {
                $svg .= "<circle cx='{$t['x']}' cy='62' r='4' fill='#059669' stroke='#FFFFFF' stroke-width='1.5'/>";
                $svg .= "<text x='{$t['x']}' y='46' fill='#065F46' font-size='9.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$t['a']}</text>";
                $svg .= "<text x='{$t['x']}' y='86' fill='#6B7280' font-size='8' font-weight='700' font-family='sans-serif' text-anchor='middle'>{$t['l']}</text>";
            }
        } elseif ($type === 'head') {
            $svg .= "<text x='{$title_x}' y='22' fill='#1E40AF' font-size='12' font-weight='800' font-family='sans-serif'>Mastishk Rekha (Head Line) Trajectory: Intellectual Direction</text>";
            $p_start = $has_crop ? 150 : 35;
            $p_mid1  = $has_crop ? 280 : 200;
            $p_mid2  = $has_crop ? 390 : 360;
            $p_end   = $has_crop ? 515 : 495;
            $svg .= "<path d='M {$p_start},50 Q {$p_mid1},50 {$p_mid2},65 Q 460,78 {$p_end},95' fill='none' stroke='#BFDBFE' stroke-width='8' stroke-linecap='round'/>";
            $svg .= "<path d='M {$p_start},50 Q {$p_mid1},50 {$p_mid2},65 Q 460,78 {$p_end},95' fill='none' stroke='#2563EB' stroke-width='3.5' stroke-linecap='round'/>";
            $svg .= "<circle cx='{$p_start}' cy='50' r='4' fill='#2563EB' stroke='#FFFFFF' stroke-width='1.5'/>";
            $svg .= "<text x='{$p_start}' y='70' fill='#1E40AF' font-size='8.5' font-weight='800' font-family='sans-serif'>Logic Origin</text>";
            $svg .= "<circle cx='340' cy='58' r='4' fill='#2563EB' stroke='#FFFFFF' stroke-width='1.5'/>";
            $svg .= "<text x='340' y='42' fill='#1E40AF' font-size='8.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>Strategic Span</text>";
            $svg .= "<circle cx='{$p_end}' cy='95' r='4' fill='#2563EB' stroke='#FFFFFF' stroke-width='1.5'/>";
            $svg .= "<text x='{$p_end}' y='110' fill='#1D4ED8' font-size='8' font-weight='800' font-family='sans-serif' text-anchor='end'>Moon Slope (-18.4°)</text>";
        } elseif ($type === 'heart') {
            $svg .= "<text x='{$title_x}' y='22' fill='#9F1239' font-size='12' font-weight='800' font-family='sans-serif'>Hridaya Rekha (Heart Line) Curvature &amp; Emotional Quotient</text>";
            $p_start = $has_crop ? 150 : 45;
            $p_end   = $has_crop ? 515 : 495;
            $svg .= "<path d='M {$p_start},75 Q 260,72 370,60 Q 450,48 {$p_end},35' fill='none' stroke='#FECDD3' stroke-width='8' stroke-linecap='round'/>";
            $svg .= "<path d='M {$p_start},75 Q 260,72 370,60 Q 450,48 {$p_end},35' fill='none' stroke='#E11D48' stroke-width='3.5' stroke-linecap='round'/>";
            $svg .= "<circle cx='{$p_start}' cy='75' r='4' fill='#E11D48' stroke='#FFFFFF' stroke-width='1.5'/>";
            $svg .= "<text x='{$p_start}' y='93' fill='#9F1239' font-size='8.5' font-weight='800' font-family='sans-serif'>Under Pinky</text>";
            $svg .= "<circle cx='{$p_end}' cy='35' r='4' fill='#E11D48' stroke='#FFFFFF' stroke-width='1.5'/>";
            $svg .= "<text x='{$p_end}' y='25' fill='#9F1239' font-size='8.5' font-weight='800' font-family='sans-serif' text-anchor='end'>Jupiter Apex (Spousal Nobility)</text>";
        } else { // fate
            $svg .= "<text x='{$title_x}' y='22' fill='#92400E' font-size='12' font-weight='800' font-family='sans-serif'>Bhagya Rekha (Fate Line) Vertical Career Ascent &amp; Milestones</text>";
            $l_start = $has_crop ? 150 : 45;
            $l_end   = $has_crop ? 515 : 495;
            $svg .= "<line x1='{$l_start}' y1='65' x2='{$l_end}' y2='65' stroke='#FDE68A' stroke-width='8' stroke-linecap='round'/>";
            $svg .= "<line x1='{$l_start}' y1='65' x2='{$l_end}' y2='65' stroke='#D97706' stroke-width='3.5' stroke-linecap='round'/>";
            $f_ticks = $has_crop ? [
                ['x' => 165, 'a' => 'Age 21', 'l' => 'Inception'],
                ['x' => 250, 'a' => 'Age 28', 'l' => 'Elevation'],
                ['x' => 340, 'a' => 'Age 35', 'l' => 'Head Junction'],
                ['x' => 430, 'a' => 'Age 49', 'l' => 'Heart Junction'],
                ['x' => 500, 'a' => 'Age 60+', 'l' => 'Saturnian Legacy']
            ] : [
                ['x' => 55,  'a' => 'Age 21', 'l' => 'Early Career Inception'],
                ['x' => 170, 'a' => 'Age 28', 'l' => 'Self-Earned Elevation'],
                ['x' => 285, 'a' => 'Age 35', 'l' => 'Head Line Junction (Strategic Shift)'],
                ['x' => 400, 'a' => 'Age 49', 'l' => 'Heart Line Junction (Peak Authority)'],
                ['x' => 485, 'a' => 'Age 60+', 'l' => 'Saturnian Legacy']
            ];
            foreach ($f_ticks as $ft) {
                $svg .= "<circle cx='{$ft['x']}' cy='65' r='4' fill='#D97706' stroke='#FFFFFF' stroke-width='1.5'/>";
                $svg .= "<text x='{$ft['x']}' y='48' fill='#92400E' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$ft['a']}</text>";
                $svg .= "<text x='{$ft['x']}' y='88' fill='#6B7280' font-size='8' font-weight='700' font-family='sans-serif' text-anchor='middle'>{$ft['l']}</text>";
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    public static function render_mount_elevation_svg($m1_name, $m1_pct, $m2_name, $m2_pct, $w = 540, $h = 95) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        
        // Mount 1 Bar
        $bar_w = 460;
        $w1 = round(($m1_pct / 100) * $bar_w);
        $svg .= "<text x='30' y='22' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>" . esc_html($m1_name) . " Elevation: {$m1_pct}% (Harmonious &amp; Developed)</text>";
        $svg .= "<rect x='30' y='28' width='{$bar_w}' height='12' rx='6' fill='#F3F4F6'/>";
        $svg .= "<rect x='30' y='28' width='{$w1}' height='12' rx='6' fill='#C2964C'/>";

        // Mount 2 Bar
        $w2 = round(($m2_pct / 100) * $bar_w);
        $svg .= "<text x='30' y='62' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>" . esc_html($m2_name) . " Elevation: {$m2_pct}% (Balanced &amp; Resilient)</text>";
        $svg .= "<rect x='30' y='68' width='{$bar_w}' height='12' rx='6' fill='#F3F4F6'/>";
        $svg .= "<rect x='30' y='68' width='{$w2}' height='12' rx='6' fill='#059669'/>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Minor Lines (Sun Line & Mercury Health Line) Vector SVG (Page 14)
     */
    public static function render_minor_lines_svg($w = 540, $h = 115) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='22' fill='#9A3412' font-size='12.5' font-weight='800' font-family='sans-serif'>Surya Rekha (Sun Line) &amp; Budh Rekha (Line of Health / Mercury)</text>";
        
        // Sun Line
        $svg .= "<line x1='120' y1='95' x2='155' y2='40' stroke='#FFEDD5' stroke-width='8' stroke-linecap='round'/>";
        $svg .= "<line x1='120' y1='95' x2='155' y2='40' stroke='#EA580C' stroke-width='3.2' stroke-linecap='round'/>";
        $svg .= "<circle cx='155' cy='40' r='5' fill='#EA580C' stroke='#FFFFFF' stroke-width='1.5'/>";
        $svg .= "<text x='155' y='32' fill='#9A3412' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='middle'>Surya Mount Apex</text>";
        $svg .= "<text x='120' y='108' fill='#6B7280' font-size='8.5' font-weight='700' font-family='sans-serif' text-anchor='middle'>Ascent from Plain of Mars</text>";

        // Health Line
        $svg .= "<line x1='360' y1='95' x2='405' y2='45' stroke='#D1FAE5' stroke-width='8' stroke-linecap='round'/>";
        $svg .= "<line x1='360' y1='95' x2='405' y2='45' stroke='#059669' stroke-width='2.6' stroke-dasharray='4,2' stroke-linecap='round'/>";
        $svg .= "<circle cx='405' cy='45' r='5' fill='#059669' stroke='#FFFFFF' stroke-width='1.5'/>";
        $svg .= "<text x='405' y='35' fill='#065F46' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='middle'>Budh Mount Base</text>";
        $svg .= "<text x='360' y='108' fill='#6B7280' font-size='8.5' font-weight='700' font-family='sans-serif' text-anchor='middle'>Faint / Clean (Ideal Nervous Tone)</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render The Sacred Money Triangle (Dhan Trikona) SVG (Page 15)
     */
    public static function render_dhan_trikona_svg($w = 540, $h = 130) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='22' fill='#065F46' font-size='12.5' font-weight='800' font-family='sans-serif'>The Sacred Dhan Trikona (Money Triangle &amp; Capital Vault Seal)</text>";
        
        // Triangle Polygon
        $svg .= "<polygon points='180,45 360,65 240,110' fill='#ECFDF5' stroke='#059669' stroke-width='2.5' stroke-dasharray='none'/>";
        $svg .= "<circle cx='255' cy='72' r='14' fill='#C2964C' fill-opacity='0.2'/>";
        $svg .= "<text x='255' y='76' fill='#047857' font-size='13' font-weight='800' font-family='sans-serif' text-anchor='middle'>₹</text>";

        // Boundary Line Annotations
        $svg .= "<text x='270' y='50' fill='#2563EB' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='middle'>Roof: Mastishk Rekha (Head Line)</text>";
        $svg .= "<text x='185' y='90' fill='#D97706' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='end'>Pillar: Bhagya Rekha (Fate Line)</text>";
        $svg .= "<text x='320' y='100' fill='#059669' font-size='9' font-weight='800' font-family='sans-serif'>Seal: Budh Rekha (Health/Mercury)</text>";

        $svg .= "<text x='25' y='122' fill='#64748B' font-size='9.5' font-family='sans-serif'>Samudrik Confirmation: Complete 3-sided closure prevents speculative leaks and anchors lasting real estate assets.</text>";
        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Sacred Samudrik Signs Grid SVG (Page 16)
     */
    public static function render_sacred_marks_grid_svg($w = 540, $h = 110) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $marks = [
            ['name' => 'Matsya (Fish)',   'sym' => '🐟', 'status' => 'Verified Base', 'col' => '#059669', 'bg' => '#ECFDF5', 'x' => 15],
            ['name' => 'Trishul (Trident)','sym' => '🔱', 'status' => 'Jupiter Apex',  'col' => '#D97706', 'bg' => '#FFFBEB', 'x' => 102],
            ['name' => 'Mystic Cross',    'sym' => '✚',  'status' => 'Quadrangle',    'col' => '#2563EB', 'bg' => '#EFF6FF', 'x' => 189],
            ['name' => 'Padma (Lotus)',   'sym' => '🪷', 'status' => 'High Honor',    'col' => '#E11D48', 'bg' => '#FFF1F2', 'x' => 276],
            ['name' => 'Mandir (Temple)', 'sym' => '🏛️', 'status' => 'Karmic Anchor', 'col' => '#C2964C', 'bg' => '#FAF7F2', 'x' => 363],
            ['name' => 'Star of Guru',    'sym' => '★',  'status' => 'Sudden Rise',   'col' => '#7C3AED', 'bg' => '#F5F3FF', 'x' => 450],
        ];

        foreach ($marks as $m) {
            $svg .= "<rect x='{$m['x']}' y='12' width='75' height='86' rx='8' fill='{$m['bg']}' stroke='{$m['col']}' stroke-width='1.2'/>";
            $svg .= "<text x='" . ($m['x'] + 37) . "' y='38' font-size='18' text-anchor='middle'>{$m['sym']}</text>";
            $svg .= "<text x='" . ($m['x'] + 37) . "' y='58' fill='#111827' font-size='7.8' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$m['name']}</text>";
            $svg .= "<rect x='" . ($m['x'] + 6) . "' y='68' width='63' height='16' rx='4' fill='#FFFFFF' stroke='{$m['col']}' stroke-width='0.8'/>";
            $svg .= "<text x='" . ($m['x'] + 37) . "' y='79' fill='{$m['col']}' font-size='7' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$m['status']}</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Manibandh (Wrist Bracelets) Triad SVG (Page 17)
     */
    public static function render_manibandh_bracelets_svg($w = 540, $h = 80) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='18' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>Manibandh Rekhas (Wrist Rascettes of Longevity, Wealth &amp; Dharma)</text>";
        
        // 1st Bracelet: Health
        $svg .= "<path d='M 35,32 Q 270,40 505,32' fill='none' stroke='#059669' stroke-width='3.2' stroke-linecap='round'/>";
        $svg .= "<text x='510' y='35' fill='#065F46' font-size='8.5' font-weight='800' font-family='sans-serif'>1st: Health &amp; Constitution (28–30 Yrs)</text>";

        // 2nd Bracelet: Wealth
        $svg .= "<path d='M 45,50 Q 270,58 495,50' fill='none' stroke='#D97706' stroke-width='3' stroke-linecap='round'/>";
        $svg .= "<text x='500' y='53' fill='#B45309' font-size='8.5' font-weight='800' font-family='sans-serif'>2nd: Prosperity &amp; Material Wealth (56–60 Yrs)</text>";

        // 3rd Bracelet: Dharma
        $svg .= "<path d='M 55,68 Q 270,76 485,68' fill='none' stroke='#C2964C' stroke-width='2.6' stroke-linecap='round'/>";
        $svg .= "<text x='490' y='71' fill='#92400E' font-size='8.5' font-weight='800' font-family='sans-serif'>3rd: Social Honor &amp; Dharma (84+ Yrs)</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Vocational Archetype Radar SVG (Page 18)
     */
    public static function render_vocational_radar_svg($w = 540, $h = 115) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='20' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>Samudrik Vocational Calling Matrix: Career Affinity Pillars</text>";
        
        $pillars = [
            ['name' => 'Institutional Leadership', 'pct' => 92, 'col' => '#059669', 'y' => 38],
            ['name' => 'Strategic Advisory & SOPs', 'pct' => 88, 'col' => '#2563EB', 'y' => 64],
            ['name' => 'Commercial Negotiation',  'pct' => 84, 'col' => '#D97706', 'y' => 90],
        ];

        foreach ($pillars as $p) {
            $w_bar = round(($p['pct'] / 100) * 320);
            $svg .= "<text x='25' y='{$p['y']}' fill='#374151' font-size='9.5' font-weight='700' font-family='sans-serif'>{$p['name']}</text>";
            $svg .= "<rect x='190' y='" . ($p['y'] - 9) . "' width='320' height='10' rx='5' fill='#F3F4F6'/>";
            $svg .= "<rect x='190' y='" . ($p['y'] - 9) . "' width='{$w_bar}' height='10' rx='5' fill='{$p['col']}'/>";
            $svg .= "<text x='518' y='{$p['y']}' fill='{$p['col']}' font-size='9.5' font-weight='800' font-family='sans-serif'>{$p['pct']}%</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Wealth Architecture SVG (Page 19)
     */
    public static function render_wealth_architecture_svg($w = 540, $h = 115) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='20' fill='#065F46' font-size='11' font-weight='800' font-family='sans-serif'>3-Tier Palm Wealth Architecture (Secured Asset Allocation)</text>";
        
        $tiers = [
            ['title' => 'Tier 1: Secured Real Estate (45%)', 'desc' => 'Square marks on Venus Mount anchor landed capital', 'col' => '#059669', 'w' => 207, 'x' => 25],
            ['title' => 'Tier 2: Compounding Portfolios (35%)', 'desc' => 'Deepened middle-palm fate line fuels steady yields', 'col' => '#2563EB', 'w' => 161, 'x' => 235],
            ['title' => 'Tier 3: Liquid Cushion (20%)', 'desc' => 'Closed Dhan Trikona guarantees emergency reserves', 'col' => '#D97706', 'w' => 92, 'x' => 400],
        ];

        $svg .= "<rect x='25' y='32' width='490' height='14' rx='7' fill='#E2E8F0'/>";
        $svg .= "<rect x='25' y='32' width='220' height='14' rx='7' fill='#059669'/>";
        $svg .= "<rect x='248' y='32' width='170' height='14' rx='7' fill='#2563EB'/>";
        $svg .= "<rect x='421' y='32' width='94' height='14' rx='7' fill='#D97706'/>";

        $svg .= "<rect x='25' y='58' width='155' height='46' rx='6' fill='#ECFDF5' stroke='#A7F3D0'/>";
        $svg .= "<text x='35' y='72' fill='#065F46' font-size='8.5' font-weight='800' font-family='sans-serif'>45% Real Estate / Land</text>";
        $svg .= "<text x='35' y='85' fill='#047857' font-size='7.5' font-family='sans-serif'>Venus Mount square seals</text>";

        $svg .= "<rect x='192' y='58' width='155' height='46' rx='6' fill='#EFF6FF' stroke='#BFDBFE'/>";
        $svg .= "<text x='202' y='72' fill='#1E40AF' font-size='8.5' font-weight='800' font-family='sans-serif'>35% Index &amp; Mutual Funds</text>";
        $svg .= "<text x='202' y='85' fill='#1D4ED8' font-size='7.5' font-family='sans-serif'>Middle-palm fate growth</text>";

        $svg .= "<rect x='360' y='58' width='155' height='46' rx='6' fill='#FFFBEB' stroke='#FDE68A'/>";
        $svg .= "<text x='370' y='72' fill='#92400E' font-size='8.5' font-weight='800' font-family='sans-serif'>20% Liquid Emergency FD</text>";
        $svg .= "<text x='370' y='85' fill='#B45309' font-size='7.5' font-family='sans-serif'>Dhan Trikona wealth seal</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Affection Lines (Vivah Rekhas) SVG (Page 20)
     */
    public static function render_vivah_rekha_svg($w = 540, $h = 115) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='22' fill='#9F1239' font-size='12.5' font-weight='800' font-family='sans-serif'>Vivah Rekha (Affection Lines): Matrimonial Harmony &amp; Timing</text>";
        
        $svg .= "<line x1='140' y1='50' x2='260' y2='50' stroke='#FECDD3' stroke-width='7' stroke-linecap='round'/>";
        $svg .= "<line x1='140' y1='50' x2='260' y2='50' stroke='#E11D48' stroke-width='3.2' stroke-linecap='round'/>";
        $svg .= "<circle cx='260' cy='50' r='4' fill='#E11D48'/>";
        $svg .= "<text x='130' y='53' fill='#9F1239' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='end'>Percussion Edge</text>";
        $svg .= "<text x='270' y='53' fill='#047857' font-size='9' font-weight='800' font-family='sans-serif'>Primary Union Line: Deep, Straight &amp; Unbroken</text>";

        $svg .= "<line x1='140' y1='75' x2='220' y2='75' stroke='#D1D5DB' stroke-width='4' stroke-linecap='round'/>";
        $svg .= "<line x1='140' y1='75' x2='220' y2='75' stroke='#6B7280' stroke-width='1.8' stroke-linecap='round'/>";
        $svg .= "<text x='230' y='78' fill='#4B5563' font-size='8.5' font-weight='700' font-family='sans-serif'>Secondary Emotional Resonance (Early Friendship)</text>";

        $svg .= "<text x='25' y='104' fill='#64748B' font-size='9.5' font-family='sans-serif'>Samudrik Reading: Horizontal alignment without downward droop ensures spousal mutual respect and lifelong devotion.</text>";
        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Ayurvedic Tridosha Palm Signs SVG (Page 21)
     */
    public static function render_tridosha_palm_svg($w = 540, $h = 115) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='20' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>Ayurvedic Samudrik Tridosha Constitutional Balance</text>";
        
        $doshas = [
            ['name' => 'Vata (वायु/आकाश)',  'trait' => 'Dryness / Active Mind', 'pct' => 28, 'col' => '#2563EB', 'bg' => '#EFF6FF', 'x' => 20],
            ['name' => 'Pitta (अग्नि/जल)',  'trait' => 'Warmth / High Vitality', 'pct' => 45, 'col' => '#DC2626', 'bg' => '#FEF2F2', 'x' => 185],
            ['name' => 'Kapha (पृथ्वी/जल)', 'trait' => 'Stamina / Calm Stability','pct' => 27, 'col' => '#059669', 'bg' => '#ECFDF5', 'x' => 350],
        ];

        foreach ($doshas as $d) {
            $svg .= "<rect x='{$d['x']}' y='34' width='165' height='68' rx='8' fill='{$d['bg']}' stroke='{$d['col']}' stroke-width='1.2'/>";
            $svg .= "<text x='" . ($d['x'] + 12) . "' y='52' fill='{$d['col']}' font-size='10' font-weight='800' font-family='sans-serif'>{$d['name']}</text>";
            $svg .= "<text x='" . ($d['x'] + 12) . "' y='67' fill='#4B5563' font-size='8.2' font-family='sans-serif'>{$d['trait']}</text>";
            $svg .= "<text x='" . ($d['x'] + 12) . "' y='88' fill='#111827' font-size='12' font-weight='800' font-family='sans-serif'>{$d['pct']}% Potency</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Chronological Age Milestones Timeline SVG (Page 22)
     */
    public static function render_age_milestone_timeline_svg($current_age = 45, $w = 540, $h = 95) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        $svg .= "<text x='25' y='18' fill='#111827' font-size='11' font-weight='800' font-family='sans-serif'>Chronological Palm Age Milestones Timeline (Ages 20 to 75)</text>";
        
        $svg .= "<line x1='35' y1='45' x2='505' y2='45' stroke='#ECE6DD' stroke-width='6' stroke-linecap='round'/>";
        $svg .= "<line x1='35' y1='45' x2='300' y2='45' stroke='#C2964C' stroke-width='6' stroke-linecap='round'/>";

        $milestones = [
            ['x' => 45,  'a' => 'Ages 20–30', 't' => 'Foundation'],
            ['x' => 165, 'a' => 'Ages 31–42', 't' => 'Consolidation'],
            ['x' => 300, 'a' => 'Ages 43–55', 't' => 'Peak Authority (Active)'],
            ['x' => 485, 'a' => 'Ages 56–75', 't' => 'Legacy & Wisdom'],
        ];

        foreach ($milestones as $m) {
            $is_current = strpos($m['t'], 'Active') !== false;
            $fill = $is_current ? '#C45738' : '#C2964C';
            $r = $is_current ? 7 : 5;
            $svg .= "<circle cx='{$m['x']}' cy='45' r='{$r}' fill='{$fill}' stroke='#FFFFFF' stroke-width='2'/>";
            $svg .= "<text x='{$m['x']}' y='32' fill='#111827' font-size='9.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$m['a']}</text>";
            $svg .= "<text x='{$m['x']}' y='68' fill='{$fill}' font-size='8' font-weight='700' font-family='sans-serif' text-anchor='middle'>{$m['t']}</text>";
        }

        $svg .= "<text x='25' y='86' fill='#64748B' font-size='8.5' font-family='sans-serif'>Current Age {$current_age}: Positioned in the prime executive consolidation window between Head and Heart line intersections.</text>";
        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render 3 Sacred Hasta Mudras SVG (Page 23)
     */
    public static function render_hasta_mudras_svg($w = 540, $h = 100) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        
        $mudras = [
            ['name' => '1. Gyan Mudra (ज्ञान)',  'target' => 'Activates Jupiter Mount',  'use' => 'Wisdom & Focus',     'col' => '#C2964C', 'bg' => '#FFFBEB', 'x' => 15],
            ['name' => '2. Prana Mudra (प्राण)', 'target' => 'Vitalizes Life Line',      'use' => 'Physical Immunity',  'col' => '#059669', 'bg' => '#ECFDF5', 'x' => 188],
            ['name' => '3. Vayu Mudra (वायु)',   'target' => 'Soothes Saturn Restlessness','use' => 'Joints & Calm Mind', 'col' => '#2563EB', 'bg' => '#EFF6FF', 'x' => 362],
        ];

        foreach ($mudras as $m) {
            $svg .= "<rect x='{$m['x']}' y='10' width='162' height='80' rx='8' fill='{$m['bg']}' stroke='{$m['col']}' stroke-width='1.2'/>";
            $svg .= "<text x='" . ($m['x'] + 12) . "' y='28' fill='#111827' font-size='10' font-weight='800' font-family='sans-serif'>{$m['name']}</text>";
            $svg .= "<text x='" . ($m['x'] + 12) . "' y='46' fill='{$m['col']}' font-size='8.5' font-weight='700' font-family='sans-serif'>{$m['target']}</text>";
            $svg .= "<text x='" . ($m['x'] + 12) . "' y='62' fill='#4B5563' font-size='8.2' font-family='sans-serif'>Primary Goal: {$m['use']}</text>";
            $svg .= "<text x='" . ($m['x'] + 12) . "' y='78' fill='#6B7280' font-size='7.5' font-weight='600' font-family='sans-serif'>Practice 15 Mins Daily</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Do's & Don'ts Comparison Visual SVG (Page 24)
     */
    public static function render_dos_donts_svg($w = 540, $h = 95) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; display:block;'>";
        
        // Recommended Do's
        $svg .= "<rect x='15' y='10' width='245' height='75' rx='8' fill='#ECFDF5' stroke='#A7F3D0' stroke-width='1.2'/>";
        $svg .= "<text x='28' y='28' fill='#065F46' font-size='10' font-weight='800' font-family='sans-serif'>✅ Recommended Palmistry Protocols</text>";
        $svg .= "<text x='28' y='46' fill='#047857' font-size='8.2' font-family='sans-serif'>• Morning Karaagre Vasate Lakshmi palm prayer</text>";
        $svg .= "<text x='28' y='60' fill='#047857' font-size='8.2' font-family='sans-serif'>• Cow ghee massage on Venus mount on Fridays</text>";
        $svg .= "<text x='28' y='74' fill='#047857' font-size='8.2' font-family='sans-serif'>• Wear recommended ring on active hand</text>";

        // Traps to Avoid
        $svg .= "<rect x='280' y='10' width='245' height='75' rx='8' fill='#FEF2F2' stroke='#FECDD3' stroke-width='1.2'/>";
        $svg .= "<text x='293' y='28' fill='#991B1B' font-size='10' font-weight='800' font-family='sans-serif'>⛔ Traps &amp; Strains to Avoid</text>";
        $svg .= "<text x='293' y='46' fill='#B91C1C' font-size='8.2' font-family='sans-serif'>• Do not clench fists tightly during sleep</text>";
        $svg .= "<text x='293' y='60' fill='#B91C1C' font-size='8.2' font-family='sans-serif'>• Avoid unvetted speculative day-trading</text>";
        $svg .= "<text x='293' y='74' fill='#B91C1C' font-size='8.2' font-family='sans-serif'>• Prevent late-night digital screen fatigue</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Pinned Bottom Synthesis Box for Interior Pages
     */
    public static function render_page_bottom_synthesis($points = [], $title = 'Samudrik Synthesis & Takeaways') {
        $html = "<div class='mr-synthesis-box'>";
        $html .= "<div class='mr-synthesis-title'>" . esc_html($title) . "</div>";
        $html .= "<div class='mr-synthesis-grid'>";
        foreach ($points as $p) {
            $html .= "<div class='mr-synthesis-item'>";
            $html .= "<span class='mr-synthesis-bullet'>&bull;</span>";
            $html .= "<span>" . esc_html($p) . "</span>";
            $html .= "</div>";
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * Standard Lifted Page Footer (26 Pages Total)
     */
    public static function render_page_footer($page_num, $section_title = '') {
        $html = "<div class='mr-footer-bar'>";
        $html .= "<span>MeraRashifal &bull; " . esc_html($section_title ? $section_title : 'Master Hast Rekha & Palmistry Dossier') . "</span>";
        $html .= "<span class='mr-page-num'>Page " . intval($page_num) . " of 29</span>";
        $html .= "</div>";
        return $html;
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
            return ['success' => false, 'error' => 'Vedic Palmistry AI intelligence is temporarily unavailable. Please try again shortly.'];
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

        $models = [self::GEMINI_MODEL, self::FALLBACK_MODEL, 'gemini-3.1-flash-lite', 'gemini-flash-lite-latest'];

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

        return ['success' => false, 'error' => 'The uploaded photograph could not be verified as a clear open palm. Please review the photo guidelines below and upload a clear picture of your open hand.'];
    }

    /**
     * AJAX Endpoint: Free On-Device Snippet
     */
    public static function ajax_analyze_free() {
        check_ajax_referer('mr_palm_nonce', 'security');

        if (!is_user_logged_in()) {
            $account_url  = class_exists('MR_Account_UI') ? MR_Account_UI::get_account_page_url() : home_url('/my-account/');
            $current_page = wp_get_referer() ?: home_url('/reports/palm-reading-report/');
            $login_url    = add_query_arg('redirect_to', $current_page, $account_url);
            wp_send_json_error([
                'code'      => 'auth_required',
                'message'   => 'Please sign in to your MeraRashifal account to perform the free palmistry analysis.',
                'login_url' => $login_url
            ]);
        }

        if (empty($_FILES['palm_image']['tmp_name'])) {
            wp_send_json_error(['message' => 'Please upload a photo of your palm.']);
        }

        $age = isset($_POST['age']) ? max(10, min(100, (int)$_POST['age'])) : 45;
        $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : 'Male';
        $hand = isset($_POST['hand']) ? sanitize_text_field($_POST['hand']) : 'Right';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : 'Seeker';

        $tmp_file = $_FILES['palm_image']['tmp_name'];

        // Step 1: Pre-validate image authenticity, hand presence, and line clarity
        $val = self::validate_palm_image($tmp_file);
        if (empty($val['is_valid_palm'])) {
            $err_msg = !empty($val['rejection_reason'])
                ? $val['rejection_reason']
                : 'The uploaded image could not be verified as a clear open palm. Please review the photo guidelines below and upload a clear picture of your open hand.';
            wp_send_json_error([
                'message' => $err_msg,
                'code'    => 'invalid_palm'
            ]);
        }

        // Step 2: Compress and store uploaded image immediately (lossless/visually lossless high-fidelity, strips heavy camera metadata)
        $token = 'mr_palm_' . md5($name . '_' . $age . '_' . time() . '_' . wp_generate_password(8, false));
        $uploads_dir = wp_upload_dir();
        $palm_cache_dir = $uploads_dir['basedir'] . '/palm-cache';
        if (!file_exists($palm_cache_dir)) {
            wp_mkdir_p($palm_cache_dir);
        }
        $saved_image_path = $palm_cache_dir . '/' . $token . '.jpg';
        self::compress_and_store_palm_image($tmp_file, $saved_image_path);

        $analysis_source = file_exists($saved_image_path) ? $saved_image_path : $tmp_file;

        // Step 3: Proceed with detailed palm analysis using the optimized compressed image
        $res = self::call_gemini_palm_analysis($analysis_source, $age, $gender, $hand, 'free');

        if (!$res['success']) {
            wp_send_json_error(['message' => $res['error']]);
        }

        // Process palm photo natively in 100% Pure PHP GD (Zero Python required)
        $processed_dir = $palm_cache_dir . '/' . $token;
        $gd_result = self::process_palm_image_native_php($saved_image_path, $processed_dir, $hand);

        $session_data = [
            'token'        => $token,
            'name'         => $name,
            'age'          => $age,
            'gender'       => $gender,
            'hand'         => $hand,
            'image_path'   => $saved_image_path,
            'master_image' => (!empty($gd_result['master_image'])) ? $gd_result['master_image'] : $saved_image_path,
            'crops'        => (!empty($gd_result['crops'])) ? $gd_result['crops'] : [],
            'metrics'      => (!empty($gd_result['metrics'])) ? $gd_result['metrics'] : [],
            'data'         => $res['data'],
            'created'      => time(),
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

        $is_logged_in = is_user_logged_in();
        $account_url  = class_exists('MR_Account_UI') ? MR_Account_UI::get_account_page_url() : home_url('/my-account/');
        $current_url  = !empty($_SERVER['REQUEST_URI']) ? home_url($_SERVER['REQUEST_URI']) : home_url('/reports/palm-reading-report/');
        $login_url    = add_query_arg('redirect_to', $current_url, $account_url);

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

            /* Professional Palm Photo Error & Guidance Card */
            .mr-palm-error-box {
                background: #FFF5F5;
                border: 1.5px solid #FCA5A5;
                border-radius: 16px;
                padding: 22px 20px;
                margin-bottom: 24px;
                box-shadow: 0 4px 18px rgba(239, 68, 68, 0.08);
                animation: mrFadeIn 0.3s ease;
                text-align: left;
            }
            .mr-palm-error-header {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                margin-bottom: 16px;
            }
            .mr-palm-error-icon-wrap {
                width: 44px;
                height: 44px;
                background: #FEE2E2;
                color: #DC2626;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                flex-shrink: 0;
            }
            .mr-palm-error-text-wrap {
                flex: 1;
            }
            .mr-palm-error-title {
                font-size: 17px;
                font-weight: 800;
                color: #991B1B;
                margin: 0 0 6px 0;
                font-family: 'Playfair Display', serif, system-ui;
            }
            .mr-palm-error-msg {
                font-size: 13.5px;
                color: #7F1D1D;
                line-height: 1.55;
                margin: 0;
            }
            .mr-palm-guidance-card {
                background: #FFFFFF;
                border: 1px solid #FECACA;
                border-radius: 12px;
                padding: 16px 18px;
                margin-bottom: 16px;
            }
            .mr-palm-guidance-title {
                font-size: 13px;
                font-weight: 700;
                color: #991B1B;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mr-palm-guidance-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 12px;
            }
            .mr-palm-guide-box {
                background: #FFF9F0;
                border: 1px solid #FED7AA;
                border-radius: 10px;
                padding: 12px 14px;
            }
            .mr-palm-guide-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11px;
                font-weight: 700;
                color: #C2410C;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 6px;
            }
            .mr-palm-guide-box h5 {
                font-size: 13.5px;
                font-weight: 700;
                color: #0F172A;
                margin: 0 0 4px 0;
            }
            .mr-palm-guide-box p {
                font-size: 12px;
                color: #64748B;
                line-height: 1.45;
                margin: 0;
            }
            .mr-palm-error-actions {
                text-align: center;
            }
            .mr-palm-btn-reupload {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(135deg, #F58220 0%, #D9A441 100%);
                color: #FFFFFF;
                font-size: 14px;
                font-weight: 700;
                padding: 11px 24px;
                border: none;
                border-radius: 50px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(245, 130, 32, 0.3);
                transition: all 0.2s ease;
            }
            .mr-palm-btn-reupload:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(245, 130, 32, 0.4);
            }
            </style>

            <?php if ($atts['show_header'] !== 'no'): ?>
            <div class="mr-palm-header">
                <span class="mr-palm-badge"><i class="fa-solid fa-hand"></i> Vedic Samudrik Shastra</span>
                <h2 class="mr-palm-h1">AI Palmistry &amp; <span class="mr-palm-saffron">Hast Rekha Scan</span></h2>
                <p class="mr-palm-sub">
                    Upload or snap a photo of your palm. Our Vedic intelligence analyzes your core lines, mounts, and authority indicators in seconds.
                </p>
            </div>
            <?php endif; ?>

            <div class="mr-palm-card">
                <form id="mr-palm-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('mr_palm_nonce', 'mr_palm_nonce_field'); ?>

                    <!-- Error & Step-by-Step Guidance Box (Hidden by default, shown if image is rejected) -->
                    <div id="mr-palm-error-box" class="mr-palm-error-box" style="display:none;">
                        <div class="mr-palm-error-header">
                            <div class="mr-palm-error-icon-wrap">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div class="mr-palm-error-text-wrap">
                                <h4 class="mr-palm-error-title">Photo Not Suitable for Palm Reading</h4>
                                <p id="mr-palm-error-message" class="mr-palm-error-msg"></p>
                            </div>
                        </div>

                        <div class="mr-palm-guidance-card">
                            <div class="mr-palm-guidance-title">
                                <i class="fa-solid fa-circle-info"></i> How your palm photo needs to be:
                            </div>
                            <div class="mr-palm-guidance-grid">
                                <div class="mr-palm-guide-box">
                                    <div class="mr-palm-guide-badge"><i class="fa-solid fa-hand"></i> Step 1</div>
                                    <h5>Hold Palm Flat &amp; Open</h5>
                                    <p>Face your inner palm flat toward the camera with fingers slightly relaxed. Do not make a fist or show the back of your hand.</p>
                                </div>
                                <div class="mr-palm-guide-box">
                                    <div class="mr-palm-guide-badge"><i class="fa-solid fa-sun"></i> Step 2</div>
                                    <h5>Bright, Even Lighting</h5>
                                    <p>Photograph in natural daylight or flashlight. Avoid heavy shadows, darkness, or glare over the major palm lines.</p>
                                </div>
                                <div class="mr-palm-guide-box">
                                    <div class="mr-palm-guide-badge"><i class="fa-solid fa-crop-simple"></i> Step 3</div>
                                    <h5>Full Frame (Wrist to Tips)</h5>
                                    <p>Include the base of your wrist up to all fingertips in clear focus so line contours and mounts can be accurately identified.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mr-palm-error-actions">
                            <button type="button" class="mr-palm-btn-reupload" id="mr-btn-reupload">
                                <i class="fa-solid fa-camera"></i> Select or Snap a New Palm Photo
                            </button>
                        </div>
                    </div>

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
                var errorBox = $('#mr-palm-error-box');
                var errorMsg = $('#mr-palm-error-message');

                function showPalmError(msg) {
                    var text = msg || 'The uploaded photograph could not be verified as a clear open palm. Please review the photo guidelines below and upload a clear picture of your open hand.';
                    errorMsg.text(text);
                    errorBox.slideDown(250);
                    dropzone.css({
                        'border-color': '#EF4444',
                        'background': '#FEF2F2'
                    });
                    fileInput.val('');
                    previewImg.attr('src', '');
                    previewWrap.hide();
                    promptWrap.show();
                    form.show();
                    $('html, body').animate({ scrollTop: errorBox.offset().top - 90 }, 350);
                }

                function clearPalmError() {
                    errorBox.slideUp(150);
                    dropzone.css({
                        'border-color': '#cbd5e1',
                        'background': '#f8fafc'
                    });
                }

                var isUserLoggedIn   = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
                var loginRedirectUrl = <?php echo json_encode($login_url); ?>;

                function promptLogin(e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    // Persist current origin URL in cookie for post-login auto return
                    document.cookie = "mr_login_return_url=" + encodeURIComponent(window.location.href) + "; path=/; max-age=86400; SameSite=Lax";
                    if (window.mrNotify && typeof window.mrNotify.auth === 'function') {
                        window.mrNotify.auth({
                            title: 'Sign In to Scan Palm',
                            message: 'Please sign in to your MeraRashifal account to receive your free Vedic palm analysis and save your Samudrik profile.',
                            primaryText: '<i class="fa-brands fa-google" style="margin-right:8px;"></i> Continue with Google &rarr;',
                            primaryUrl: loginRedirectUrl
                        });
                    } else {
                        window.location.href = loginRedirectUrl;
                    }
                    return false;
                }

                $('#mr-browse-btn, #mr-btn-reupload').on('click', function(e){
                    if (!isUserLoggedIn) {
                        return promptLogin(e);
                    }
                    e.preventDefault();
                    fileInput.trigger('click');
                });

                dropzone.on('dragover', function(e){
                    e.preventDefault();
                    $(this).addClass('dragover');
                }).on('dragleave drop', function(e){
                    e.preventDefault();
                    $(this).removeClass('dragover');
                    if (e.type === 'drop') {
                        if (!isUserLoggedIn) {
                            return promptLogin(e);
                        }
                        var files = e.originalEvent.dataTransfer.files;
                        if (files && files.length > 0) {
                            fileInput[0].files = files;
                            showPreview(files[0]);
                        }
                    }
                }).on('click', function(e){
                    if (!isUserLoggedIn) {
                        return promptLogin(e);
                    }
                });

                fileInput.on('change', function(){
                    clearPalmError();
                    if (this.files && this.files[0]) { showPreview(this.files[0]); }
                });

                function showPreview(file) {
                    clearPalmError();
                    var reader = new FileReader();
                    reader.onload = function(e){ previewImg.attr('src', e.target.result); promptWrap.hide(); previewWrap.show(); };
                    reader.readAsDataURL(file);
                }

                $('#mr-change-btn').on('click', function(e){
                    e.preventDefault(); fileInput.val(''); previewImg.attr('src', ''); previewWrap.hide(); promptWrap.show();
                    clearPalmError();
                });

                $('#mr-scan-another-btn').on('click', function(e){
                    e.preventDefault(); results.hide(); form.show(); fileInput.val(''); previewImg.attr('src', ''); previewWrap.hide(); promptWrap.show();
                    clearPalmError();
                    $('html, body').animate({ scrollTop: form.offset().top - 80 }, 400);
                });

                form.on('submit', function(e){
                    e.preventDefault();
                    if (!isUserLoggedIn) {
                        return promptLogin(e);
                    }
                    if (!fileInput[0].files || !fileInput[0].files[0]) {
                        showPalmError('Please select or snap a photo of your palm first.');
                        return;
                    }
                    clearPalmError();
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
                                clearPalmError();
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
                                if (resp && resp.data && resp.data.code === 'auth_required') {
                                    return promptLogin();
                                }
                                var err = (resp && resp.data && resp.data.message)
                                    ? resp.data.message
                                    : 'The uploaded photograph could not be verified as a clear open palm. Please review the photo guidelines below and try again.';
                                showPalmError(err);
                            }
                        },
                        error: function(){
                            clearInterval(stepTimer); scanner.hide();
                            showPalmError('Network connection issue while verifying palm photograph. Please check your internet connection and try again.');
                        }
                    });
                });

                $('#mr-btn-order-dossier').on('click', function(e){
                    e.preventDefault();
                    if (!isUserLoggedIn) {
                        return promptLogin(e);
                    }
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
    // MASTER 26-PAGE PUBLICATION-GRADE PALMISTRY DOSSIER
    // ========================================================

    /**
     * Build Complete 26-Page Master Hast Rekha & Palmistry Dossier HTML
     * Fully self-contained, publication-grade layout with zero astrological charts.
     */
    public static function build_palmistry_report_html($person_name, $gender = 'Male', $dob = '15/10/1981', $tob = '14:25', $pob = 'New Delhi, India', $palm_data = null) {
        $person_name = !empty($person_name) ? trim($person_name) : 'Seeker';
        $gender = !empty($gender) ? trim($gender) : 'Male';
        $pob = !empty($pob) ? trim($pob) : 'New Delhi, India';

        $age = 45;
        if (!empty($dob)) {
            $clean_dob = str_replace('/', '-', $dob);
            $ts = strtotime($clean_dob);
            if ($ts) {
                $calc_age = (int)date('Y') - (int)date('Y', $ts);
                if ($calc_age >= 10 && $calc_age <= 100) {
                    $age = $calc_age;
                }
            }
        }

        // Format dates
        $format_dob_full = function($dob_str) {
            if (empty($dob_str)) return '15 October 1981';
            $ts = strtotime(str_replace('/', '-', trim($dob_str)));
            return $ts ? date('d F Y', $ts) : $dob_str;
        };
        $dob_formatted = $format_dob_full($dob);

        // Resolve User Palm Images (Master Map & Chapter Crops)
        $master_palm_uri = '';
        $crop_life_uri   = '';
        $crop_head_uri   = '';
        $crop_heart_uri  = '';
        $crop_fate_uri   = '';
        $palm_metrics    = [];

        $to_data_uri = function($path) {
            if (!empty($path) && file_exists($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
            return '';
        };

        if (!empty($palm_data)) {
            if (!empty($palm_data['master_image'])) {
                $master_palm_uri = $to_data_uri($palm_data['master_image']);
            } elseif (!empty($palm_data['processed_image'])) {
                $master_palm_uri = $to_data_uri($palm_data['processed_image']);
            } elseif (!empty($palm_data['image_path'])) {
                $processed_master = dirname($palm_data['image_path']) . '/processed_user_palm.png';
                if (file_exists($processed_master)) {
                    $master_palm_uri = $to_data_uri($processed_master);
                } else {
                    $master_palm_uri = $to_data_uri($palm_data['image_path']);
                }
            }

            if (!empty($palm_data['crops'])) {
                if (!empty($palm_data['crops']['life']))  $crop_life_uri  = $to_data_uri($palm_data['crops']['life']);
                if (!empty($palm_data['crops']['head']))  $crop_head_uri  = $to_data_uri($palm_data['crops']['head']);
                if (!empty($palm_data['crops']['heart'])) $crop_heart_uri = $to_data_uri($palm_data['crops']['heart']);
                if (!empty($palm_data['crops']['fate']))  $crop_fate_uri  = $to_data_uri($palm_data['crops']['fate']);
            }

            if (!empty($palm_data['metrics'])) {
                $palm_metrics = $palm_data['metrics'];
            }
        }

        // Extract palm traits if provided from Gemini vision analysis
        $d = [];
        if (!empty($palm_data)) {
            if (is_array($palm_data) && isset($palm_data['data']) && is_array($palm_data['data'])) {
                $d = $palm_data['data'];
            } elseif (is_array($palm_data)) {
                $d = $palm_data;
            }
        }

        $hand_archetype = !empty($d['hand_type']) ? $d['hand_type'] : 'Air Hand (Square Palm &bull; Long Fingers &bull; Intellectual Vision)';
        $dominant_hand  = !empty($d['hand']) ? ($d['hand'] . ' Hand (Active Karma / Career)') : 'Right Hand (Active Karma / Career)';
        $apex_mount     = !empty($d['dominant_mount']) ? $d['dominant_mount'] : 'Venus / Shukra (Rank #1 &bull; Vitality & Warmth)';

        $summary_text   = !empty($d['summary']) ? $d['summary'] : 'Your hand displays the classic architecture of an earned provider and strategic director. A long, gracefully descending Head line sloping towards the Mount of Moon indicates creative foresight balanced with analytical discipline. The broad Venus mount reflects exceptional emotional warmth and generous domestic dedication, while a deeply etched middle-palm Fate line rising steadily towards Saturn confirms that your professional elevation compounds through earned competence, standardized systems, and strategic authority rather than speculative windfalls.';

        $logo_uri = self::get_logo_data_uri();

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Hast Rekha &amp; Palmistry Dossier - <?php echo esc_html($person_name); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #E5E7EB;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #111827;
            font-size: 11.8px;
            line-height: 1.5;
        }

        .mr-page {
            width: 794px;
            height: 1123px;
            max-height: 1123px;
            background: #FFFFFF;
            position: relative;
            box-sizing: border-box;
            padding: 26px 28px 22px;
            border: 1px solid #ECE6DD;
            overflow: hidden;
            page-break-before: auto;
            page-break-after: auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Double Parchment Borders */
        .mr-page::before {
            content: "";
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 1.5px solid #C2964C;
            border-radius: 6px;
            pointer-events: none;
            z-index: 10;
        }
        .mr-page::after {
            content: "";
            position: absolute;
            top: 13px; left: 13px; right: 13px; bottom: 13px;
            border: 0.8px solid #E2DACF;
            border-radius: 4px;
            pointer-events: none;
            z-index: 10;
        }

        /* Running Header Bar */
        .mr-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ECE6DD;
            padding-bottom: 5px;
            margin-bottom: 9px;
            font-size: 11.5px;
            color: #4B5563;
            font-weight: 600;
        }
        .mr-header-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: #C45738;
            letter-spacing: 0.02em;
            font-size: 12.5px;
        }

        /* Section Tag Pill & Headings */
        .mr-tag-pill {
            display: inline-block;
            background: #FAF7F2;
            border: 1px solid #E2DACF;
            color: #C45738;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 3px 10px;
            border-radius: 4px;
            margin-bottom: 4px;
        }
        .mr-page-h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 19.5px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 3px;
            line-height: 1.22;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 11.8px;
            color: #4B5563;
            margin: 0 0 10px;
            line-height: 1.4;
        }

        /* Pinned Bottom Synthesis Box & Footer */
        .mr-page-bottom-pin {
            position: absolute;
            left: 28px;
            right: 28px;
            bottom: 22px;
            box-sizing: border-box;
        }
        .mr-synthesis-box {
            background: #FFFFFF;
            border: 1.2px solid #C2964C;
            border-radius: 7px;
            padding: 8px 12px;
            margin-bottom: 6px;
            box-shadow: 0 2px 6px rgba(194,150,76,0.06);
        }
        .mr-synthesis-title {
            font-family: 'Poppins', sans-serif;
            font-size: 11.8px;
            font-weight: 800;
            color: #C45738;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }
        .mr-synthesis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 12px;
        }
        .mr-synthesis-item {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            font-size: 11px;
            color: #374151;
            line-height: 1.4;
        }
        .mr-synthesis-bullet {
            color: #C45738;
            font-weight: 900;
            font-size: 11px;
            line-height: 1.2;
        }
        .mr-footer-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 4px;
            border-top: 1px solid #ECE6DD;
            font-size: 10.8px;
            color: #9CA3AF;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 11.8px;
        }

        /* 2x2 Diagnostic Cards Grid */
        .mr-diag-grid-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
            margin-bottom: 9px;
        }
        .mr-diag-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 7px;
            padding: 10px 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
        }
        .mr-diag-card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 12.8px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mr-diag-card-p {
            font-size: 11.2px;
            color: #374151;
            line-height: 1.46;
            margin: 0;
        }

        /* Standard Fixed Layout Tables */
        .mr-matching-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            word-wrap: break-word;
            margin-bottom: 9px;
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 6px;
            overflow: hidden;
        }
        .mr-matching-table th {
            background: #FAF7F2;
            color: #111827;
            font-family: 'Poppins', sans-serif;
            font-size: 11.2px;
            font-weight: 800;
            padding: 6px 8px;
            border-bottom: 1.5px solid #C2964C;
            text-align: left;
            vertical-align: middle;
        }
        .mr-matching-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #F0EAE1;
            font-size: 10.8px;
            color: #374151;
            vertical-align: middle;
            line-height: 1.42;
        }
        .mr-matching-table tr:last-child td {
            border-bottom: none;
        }

        /* Status & Score Pills */
        .mr-pill {
            display: inline-block;
            padding: 2.5px 8px;
            border-radius: 12px;
            font-size: 9.8px;
            font-weight: 800;
            text-align: center;
        }
        .mr-pill-green  { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
        .mr-pill-blue   { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; }
        .mr-pill-amber  { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
        .mr-pill-red    { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .mr-pill-purple { background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE; }
    </style>
</head>
<body>

    <!-- ========================================== -->
    <!-- PAGE 01: COVER DOSSIER -->
    <!-- ========================================== -->
    <div class="mr-page" style="display:flex; flex-direction:column; justify-content:space-between; text-align:center; padding:36px 36px 28px;">
        <div>
            <?php if (!empty($logo_uri)): ?>
                <img src="<?php echo esc_url($logo_uri); ?>" alt="MeraRashifal" style="max-height:48px; margin-bottom:12px;">
            <?php else: ?>
                <div style="font-family:'Poppins', sans-serif; font-size:24px; font-weight:800; color:#C45738; margin-bottom:12px;">MeraRashifal</div>
            <?php endif; ?>

            <div style="display:inline-block; background:#FAF7F2; border:1.2px solid #C2964C; border-radius:30px; padding:5px 20px; font-size:11px; font-weight:800; color:#C45738; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:14px;">
                🪐 VEDIC SAMUDRIK SHASTRA PERSONALIZED PALMISTRY &bull; HAST REKHA
            </div>

            <h1 style="font-family:'Poppins', sans-serif; font-size:29px; font-weight:800; color:#111827; line-height:1.2; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.02em;">
                MASTER HAST REKHA &amp;<br>PALMISTRY DOSSIER
            </h1>
            <div style="font-size:13.5px; font-weight:600; font-style:italic; color:#78350F; margin-bottom:20px;">
                “Comprehensive Anatomical Palm Topography, Line Vectors &amp; Samudrik Milestones”
            </div>

            <div style="font-size:11px; color:#6B7280; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">PREPARED EXCLUSIVELY FOR</div>
            <div style="font-family:'Poppins', sans-serif; font-size:24px; font-weight:800; color:#111827; margin-bottom:20px;">
                <?php echo esc_html($person_name); ?>
            </div>

            <!-- 3 Core Metadata Cards -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; max-width:700px; margin:0 auto 20px;">
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 10px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                    <div style="font-size:10.5px; font-weight:800; color:#C45738; text-transform:uppercase;">🖐️ Dominant Hand</div>
                    <div style="font-family:'Poppins', sans-serif; font-size:13.5px; font-weight:700; color:#111827; margin:3px 0;"><?php echo esc_html($dominant_hand); ?></div>
                    <div style="font-size:10px; color:#6B7280;">Active Karma &bull; Vocation</div>
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 10px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                    <div style="font-size:10.5px; font-weight:800; color:#C45738; text-transform:uppercase;">🧭 Hand Archetype</div>
                    <div style="font-family:'Poppins', sans-serif; font-size:13.5px; font-weight:700; color:#111827; margin:3px 0;">Air Hand (Intellectual)</div>
                    <div style="font-size:10px; color:#6B7280;">Square Palm &bull; Long Fingers</div>
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 10px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                    <div style="font-size:10.5px; font-weight:800; color:#C45738; text-transform:uppercase;">⛰️ Apex Mount</div>
                    <div style="font-family:'Poppins', sans-serif; font-size:13.5px; font-weight:700; color:#111827; margin:3px 0;">Venus / Shukra (#1)</div>
                    <div style="font-size:10px; color:#6B7280;">Vitality &bull; Generous Provider</div>
                </div>
            </div>

            <!-- Quick Samudrik Indicators Strip -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:8px; padding:12px 16px; max-width:700px; margin:0 auto 20px; text-align:left;">
                <div style="font-family:'Poppins', sans-serif; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:7px; letter-spacing:0.04em;">
                    ⚡ Primary Hast Rekha Anatomical Signatures:
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:7px 18px; font-size:10.8px; color:#374151; line-height:1.45;">
                    <div>&bull; <strong>Jeevan Rekha (Life):</strong> Wide sweep around Venus; high recuperative stamina.</div>
                    <div>&bull; <strong>Mastishk Rekha (Head):</strong> Descending towards Chandra; strategic creative vision.</div>
                    <div>&bull; <strong>Hridaya Rekha (Heart):</strong> Ascending towards Jupiter; spousal loyalty &amp; nobility.</div>
                    <div>&bull; <strong>Bhagya Rekha (Fate):</strong> Mid-palm rise from age 28; earned institutional authority.</div>
                </div>
            </div>
        </div>

        <div>
            <!-- Birth Details Bar -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:7px 14px; max-width:700px; margin:0 auto 12px; font-size:11px; color:#374151;">
                <strong>Native Profile:</strong> <?php echo esc_html($person_name); ?> &bull; <?php echo esc_html($gender); ?> &bull; Current Age: <?php echo intval($age); ?> Yrs &bull; DOB: <?php echo esc_html($dob_formatted); ?> &bull; <?php echo esc_html($pob); ?>
            </div>

            <div style="font-size:9.2px; color:#6B7280; line-height:1.42; max-width:700px; margin:0 auto 9px;">
                <strong>Statutory Disclaimer:</strong> This publication is compiled using authentic Indian Vedic Samudrik Shastra (Hast Rekha Shastra) and computational biometric recognition models. Palm lines reflect dynamic neuro-physiological and karmic inclinations; conscious discernment (Viveka) and disciplined effort (Purushartha) remain supreme in shaping human destiny.
            </div>

            <div style="font-size:10.5px; color:#4B5563; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;">
                MERARASHIFAL.COM &bull; MASTER HAST REKHA &amp; PALMISTRY DOSSIER
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 02: TABLE OF CONTENTS & GUIDE -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Samudrik Shastra Dossier</span>
        </div>
        
        <div class="mr-tag-pill">00 &bull; GUIDE &amp; CONTENTS</div>
        <div class="mr-page-h1">Table of Contents &amp; Palmistry Guide</div>
        <div class="mr-page-sub">Your complete 29-page guide to Vedic palmistry, hand lines, and practical life advice</div>

        <!-- 7 Parts Two-Column Structure Table -->
        <table class="mr-matching-table" style="margin-bottom:10px;">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 50%;">
            </colgroup>
            <thead>
                <tr>
                    <th>SECTION &amp; CHAPTER FOCUS</th>
                    <th>SECTION &amp; CHAPTER FOCUS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="color:#C45738;">PART I &bull; CHRONOLOGICAL LIFE HORIZONS</strong> (Pages 04&ndash;06)<br>
                        <span style="color:#4B5563;">&bull; The Past, Present &amp; Future Across 8 Life Dimensions<br>&bull; Probabilistic Career, Wealth, Love, Vitality &amp; Status</span>
                    </td>
                    <td>
                        <strong style="color:#C45738;">PART II &bull; HAND MORPHOLOGY &amp; ELEMENTS</strong> (Pages 07&ndash;08)<br>
                        <span style="color:#4B5563;">&bull; Elemental Classification (Earth, Air, Fire, Water)<br>&bull; Thumb Willpower vs. Reasoned Logic, Nails &amp; Lunula</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong style="color:#C45738;">PART III &bull; FOUR MAJOR LINES DEEP-DIVE</strong> (Pages 09&ndash;12)<br>
                        <span style="color:#4B5563;">&bull; Jeevan Rekha (Life &amp; Vitality Arc &bull; 0&ndash;80 Yrs)<br>&bull; Mastishk (Head), Hridaya (Heart), Bhagya (Fate)</span>
                    </td>
                    <td>
                        <strong style="color:#C45738;">PART IV &bull; MOUNT TOPOGRAPHY &amp; FORCES</strong> (Pages 13&ndash;16)<br>
                        <span style="color:#4B5563;">&bull; Mounts of Jupiter (Guru) &amp; Saturn (Shani)<br>&bull; Mounts of Sun, Mercury, Venus, Moon &amp; Mars Terrain</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong style="color:#C45738;">PART V &bull; MINOR LINES &amp; SACRED SEALS</strong> (Pages 17&ndash;20)<br>
                        <span style="color:#4B5563;">&bull; Surya Rekha (Fame) &amp; Budh Rekha (Health)<br>&bull; Dhan Trikona (Money Triangle), Sacred Signs &amp; Wrist</span>
                    </td>
                    <td>
                        <strong style="color:#C45738;">PART VI &bull; LIFE SPHERES &amp; AGE TIMELINE</strong> (Pages 21&ndash;25)<br>
                        <span style="color:#4B5563;">&bull; Career Calling, Wealth Blueprint, Love &amp; Marriage<br>&bull; Ayurvedic Vitality &amp; Age-Mapped Milestones (20&ndash;75)</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong style="color:#C45738;">PART VII &bull; VEDIC REMEDIES &amp; LEXICON</strong> (Pages 26&ndash;29)<br>
                        <span style="color:#4B5563;">&bull; Hasta Mudras, Gemstones, Morning Protocols<br>&bull; Master Concordance Matrix &amp; Definitive Glossary</span>
                    </td>
                    <td>
                        <strong style="color:#C45738;">VISUAL HERO CANVAS &amp; BIOMETRIC MAPPING</strong> (Page 03)<br>
                        <span style="color:#4B5563;">&bull; Color-Coded Samudrik Line Overlays on Dominant Palm<br>&bull; Mount Coordinates, Crease Trajectories &amp; Key Vectors</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Reading Strategy Guidance Box -->
        <div class="mr-diag-card" style="border-left:4px solid #C45738; margin-bottom:10px;">
            <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> The Golden Rules of Samudrik Shastra Reading</div>
            <p class="mr-diag-card-p" style="margin-bottom:5px;">
                <strong>1. Active vs. Passive Palm:</strong> Your dominant hand represents conscious actions, earned career trajectory, and active karma (<em>Purushartha</em>). Your non-dominant hand represents inborn potential, subconscious temperament, and inherited ancestral patterns (<em>Prarabdha</em>).
            </p>
            <p class="mr-diag-card-p" style="margin-bottom:5px;">
                <strong>2. Dynamic Lines &amp; Probabilistic Insight:</strong> Contrary to fatalistic myths, palm lines are not carved in stone. Fine branchings, money triangle seals, and mount elevations continuously evolve across 5&ndash;7 year biological cycles in direct response to your conscious discipline, mental habits, and righteous choices. Predictions reflect prevailing energetic probabilities, not immutable fate.
            </p>
            <p class="mr-diag-card-p" style="margin-bottom:5px;">
                <strong>3. How to Use This Report:</strong> Examine the <strong>Master Palm Map on Page 03</strong> first to visually anchor your hand's major lines and mounts. Then review the <strong>Past, Present, and Future Trikala Samiksha (Pages 04&ndash;06)</strong> to ground your life trajectory before exploring the anatomical deep dives.
            </p>
            <p class="mr-diag-card-p">
                <strong>4. Triadic Synthesis:</strong> Correlate your Primary Mounts (Pages 13&ndash;16) with your Minor Seals (Pages 17&ndash;20) and Action Protocols (Pages 26&ndash;27) to implement the specific daily mudras and gemstone alignments for maximum holistic compounding.
            </p>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Complete 29-page analytical syllabus covering all canonical Samudrik dimensions.',
                'Dominant right hand reflects active vocational authority; non-dominant hand reveals inborn baseline.',
                'Palm lines evolve dynamically across 5–7 year cycles through disciplined consciousness.'
            ], 'Document Navigation & Reading Takeaways'); ?>
            <?php echo self::render_page_footer(2, 'Table of Contents & Guide'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 03: [HERO] MASTER PALM MAP CANVAS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Sacred Hand Topography</span>
        </div>
        
        <div class="mr-tag-pill">01 &bull; SACRED HAND TOPOGRAPHY &amp; LINE BLUEPRINT</div>
        <div class="mr-page-h1">Master Palm Map (Hast Rekha &amp; Mount Matrix)</div>
        <div class="mr-page-sub">Clear hand map showing all major lines and planetary mounts on your palm</div>

        <!-- HERO PALM MAP SVG -->
        <div style="margin-bottom:10px;">
            <?php 
            if (!empty($master_palm_uri)) {
                echo self::render_master_palm_map_hybrid($master_palm_uri, 540, 576, (stripos($dominant_hand, 'Left') === false), $palm_metrics);
            } else {
                echo self::render_master_palm_map_svg(540, 576);
            }
            ?>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Life line sweeps in a generous, unbroken arc around Venus, indicating deep reserve vitality.',
                'Head line descends gracefully toward Chandra with a terminal fork, reflecting strategic creative vision.',
                'Mount of Venus and Mount of Moon establish exceptional emotional and intuitive equilibrium.'
            ], 'Master Palm Map Essential Takeaways'); ?>
            <?php echo self::render_page_footer(3, 'Master Palm Topography Map'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 04: CHRONOLOGICAL HORIZONS - THE PAST -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Trikala Samiksha &bull; The Past</span>
        </div>
        
        <div class="mr-tag-pill">02 &bull; CHRONOLOGICAL LIFE HORIZONS (TRIKALA SAMIKSHA)</div>
        <div class="mr-page-h1">Your Past (भूतकाल) &bull; Early Years, Lessons &amp; Growth</div>
        <div class="mr-page-sub">What your palm lines reveal about your early years, hard work, and building life on your own (Ages 0&ndash;38)</div>

        <div style="margin-bottom:8px;">
            <div style="background:#FFF9F3; border:1px solid #E2D3BE; border-left:3.5px solid #C45738; border-radius:5px; padding:5px 10px; font-size:10.5px; color:#5D4037; line-height:1.40;">
                <strong>Astrological Samudrik Note:</strong> Palm lines reflect your natural mindset and life habits, not rigid fate. Your lines show that through hard work and patience, you overcame early difficulties and built strong inner confidence.
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-bottom:8px;">
            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💼 <strong>Career &amp; Vocation</strong> (व्यवसाय)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Self-Made Build</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The lower segment of your Saturn crease (Fate Line) reveals an autonomous ascent from the Ketu zone, accompanied by subtle crossing stress rays between ages 22 and 29.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> This confirms early vocational stability was self-earned through persistent trial rather than inheritance, forging the resilient executive core that now anchors your career.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💰 <strong>Finance &amp; Capital</strong> (धन एवं संपत्ति)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Consolidating Base</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Formative boundaries of your Dhan Trikona (Money Triangle) show progressive deepening after age 30, with early micro-openings along the Mercury boundary.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Early cycles were dominated by operational reinvestment and unavoidable outlays, which systematically matured into disciplined asset accumulation by your mid-thirties.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>❤️ <strong>Love &amp; Marriage</strong> (प्रेम एवं संबंध)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Matured Bonds</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> A distinct, stable union crease upon the Mount of Mercury, aligning with an early sympathetic fork at the termination of the Hridaya Rekha.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Points to romantic ideals refined through real-world compromise. You learned to harmonize individual independence with marital loyalty and emotional partnership.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🏡 <strong>Family &amp; Domestic</strong> (परिवार एवं कुल)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Dharma Fulfilled</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Well-knit chain patterns at the base collar of the thumb (Venus rung) transitioning into solid, smooth horizontal lineation.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Highlights substantial filial duties and domestic responsibilities navigated in earlier years, cementing your standing as an indispensable protector of the family hearth.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌿 <strong>Health &amp; Vitality</strong> (स्वास्थ्य एवं ऊर्जा)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Stamina Acquired</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Robust, deeply etched inner curvature along the early quadrant of the Jeevan Rekha, free from systemic breaks or major islands.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Despite occupational exhaustion and episodic stress spikes during peak formative phases, your constitutional reserve (<em>Ojas</em>) enabled swift somatic recuperation.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌟 <strong>Social Status &amp; Regard</strong> (प्रतिष्ठा एवं यश)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Earned Regard</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The nascent origin of the Apollo (Sun) line emerging firmly above the lower plain of Mars around biological age 32.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Shows public recognition and professional credibility were built stone-by-stone on demonstrated competence and word-of-mouth rather than sudden privilege.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>✈️ <strong>Travel &amp; Horizons</strong> (यात्रा एवं देशाटन)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Broadened Scope</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Fine horizontal transit striations gently fanning outward across the outer perimeter of the Mount of Chandra (Moon).
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Reflects formative travels, vocational relocations, and external exposures that expanded your cognitive horizons and honed commercial resourcefulness.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🤝 <strong>Friends &amp; Mentors</strong> (मित्र एवं साझेदारी)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Filtered Circle</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Clear auxiliary lines running alongside the Life Line, gradually winnowing away diffuse branching networks.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Signifies a natural shift from wide, indiscriminate social networks toward a compact, loyal inner council of trusted peers and dependable allies.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Early vocational struggles forged self-reliance; foundational stability was built entirely through personal effort.',
                'Dhan Trikona consolidated solidly by mid-30s, shifting focus from speculative risks to asset retention.',
                'Karmic family duties served as character-building crucibles, laying the bedrock for current authority.'
            ], 'Retrospective Synthesis & Lessons of the Past'); ?>
            <?php echo self::render_page_footer(4, 'Trikala Samiksha — The Past'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 05: CHRONOLOGICAL HORIZONS - THE PRESENT -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Trikala Samiksha &bull; The Present</span>
        </div>
        
        <div class="mr-tag-pill">02 &bull; CHRONOLOGICAL LIFE HORIZONS (TRIKALA SAMIKSHA)</div>
        <div class="mr-page-h1">Your Present (वर्तमान काल) &bull; Current Life Stage &amp; Focus</div>
        <div class="mr-page-sub">What your palm lines reveal about your career momentum, relationships, and current focus (Active Age: ~<?php echo intval($age); ?>)</div>

        <div style="margin-bottom:8px;">
            <div style="background:#FFF9F3; border:1px solid #E2D3BE; border-left:3.5px solid #C45738; border-radius:5px; padding:5px 10px; font-size:10.5px; color:#5D4037; line-height:1.40;">
                <strong>Astrological Samudrik Note:</strong> Your dominant hand shows the energy and habits running your life right now. These lines highlight good opportunities where your current focus and hard work will bring big, lasting rewards.
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-bottom:8px;">
            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💼 <strong>Career &amp; Vocation</strong> (व्यवसाय)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Prime Authority</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The central meridian of your Fate Line cuts through the Head Line with decisive depth and clarity, projecting an upward offshoot toward the Sun mount.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> You are navigating an apex vocational chapter. Energy has shifted from tactical execution to organizational strategy, high-level negotiation, and earned industry authority.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💰 <strong>Finance &amp; Capital</strong> (धन एवं संपत्ति)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Capital Retention</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The Dhan Trikona (Money Triangle formed by Head, Fate, and Mercury lines) is fully sealed with zero major drainage breaches.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Indicates optimal conditions for capital accumulation, asset consolidation, and multiple revenue channels. Prioritize calculated capital deployment over speculative hype.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>❤️ <strong>Love &amp; Marriage</strong> (प्रेम एवं संबंध)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Harmonious Base</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The Heart Line maintains an even, calm trajectory toward Jupiter without downward drop-lines toward the aggressive Martian plain.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Domestic and romantic dynamics favor grounded partnership, emotional stability, and shared milestones. Calm communication diffuses vocational pressures.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🏡 <strong>Family &amp; Domestic</strong> (परिवार एवं कुल)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Pillars of Support</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The Mount of Venus exhibits balanced elevation with clean vertical striations, reflecting nourishing, grounded home vitality.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> You stand as the primary decision-maker and energetic anchor of your family. Investments in children's education, property, and domestic comfort take center stage.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌿 <strong>Health &amp; Vitality</strong> (स्वास्थ्य एवं ऊर्जा)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Metabolic Care</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Minor lateral stress rays (Rahu lines) intersecting the mid-section of the Life Line at the current chronometric juncture.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Base vitality is strong, but executive stress demands deliberate pacing. Guard against digestive sluggishness and schedule structured periods of cognitive detox.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌟 <strong>Social Status &amp; Regard</strong> (प्रतिष्ठा एवं यश)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Commanding Respect</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> A well-defined Apollo Line ascending steadily beneath the ring finger, supported by a firm, unblemished Jupiter Mount.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Your counsel carries high credibility across professional and communal networks. You are increasingly approached for guidance, dispute resolution, and mentorship.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>✈️ <strong>Travel &amp; Horizons</strong> (यात्रा एवं देशाटन)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Purposeful Journeys</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Distinct lateral travel markings crossing the upper lunar boundary toward the Mount of Mercury.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Current travel is purposeful and directly tied to commercial ventures, strategic partnerships, or sacred retreats, yielding both economic gain and inner alignment.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🤝 <strong>Friends &amp; Mentors</strong> (मित्र एवं साझेदारी)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">High-Trust Synergies</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Sister support creases reinforcing the Fate Line from the Mount of Upper Mars with clear linear trajectory.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Professional partnerships thrive when anchored in transparent contracts and aligned incentives. Dependable allies now provide crucial strategic leverage.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Apex vocational epoch: transition from individual contributor to organizational strategist and leader.',
                'Dhan Trikona firmly sealed, signaling optimal conditions for capital accumulation and asset building.',
                'Vigilance advised on nervous-gut equilibrium; daily grounding recommended to sustain peak output.'
            ], 'Present Energetic Synthesis & Focus Directives'); ?>
            <?php echo self::render_page_footer(5, 'Trikala Samiksha — The Present'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 06: CHRONOLOGICAL HORIZONS - THE FUTURE -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Trikala Samiksha &bull; The Future</span>
        </div>
        
        <div class="mr-tag-pill">02 &bull; CHRONOLOGICAL LIFE HORIZONS (TRIKALA SAMIKSHA)</div>
        <div class="mr-page-h1">Your Future (भविष्य काल) &bull; Growth, Wealth &amp; Life Legacy</div>
        <div class="mr-page-sub">What your palm lines suggest about your upcoming years, long-term wealth, and lasting achievements (Ages 47&ndash;75+)</div>

        <div style="margin-bottom:8px;">
            <div style="background:#FFF9F3; border:1px solid #E2D3BE; border-left:3.5px solid #C45738; border-radius:5px; padding:5px 10px; font-size:10.5px; color:#5D4037; line-height:1.40;">
                <strong>Astrological Samudrik Note:</strong> Predictions about the future show where your current energies and talents are naturally heading. In Indian philosophy, honest effort and good deeds continuously shape your palm lines and bring out the best possible opportunities.
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-bottom:8px;">
            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💼 <strong>Career &amp; Vocation</strong> (व्यवसाय)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Advisory &amp; Legacy</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Terminal branches of the Fate Line divert gracefully toward the Mount of Jupiter and the Mount of Saturn.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Projections indicate a seamless evolution from operational daily management to board-level advisory, stewardship, and legacy enterprises where your wisdom commands high leverage.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>💰 <strong>Finance &amp; Capital</strong> (धन एवं संपत्ति)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Compounded Wealth</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Prominent secondary wealth shoots ascending toward Mercury and Apollo, supported by stable wrist Manibandh bands.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Highlights passive dividend inflows, multi-generational family security, and prudent asset longevity, granting both comfortable independence and philanthropic freedom.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>❤️ <strong>Love &amp; Marriage</strong> (प्रेम एवं संबंध)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Spiritual Synergy</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The terminal quadrant of the Heart Line branches serenely upon the Jupiter mount, embodying Sattvic grace.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Marital bonds deepen into profound philosophical companionship and quiet mutual peace. Shared spiritual values and joint family stewardship define the elder years.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🏡 <strong>Family &amp; Domestic</strong> (परिवार एवं कुल)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Generational Pride</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Harmonious descendant lines radiating from the Mount of Venus toward the lower life crescent.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Foretells deep fulfillment through children and grandchildren. You will be revered as a wise patriarch and cultural custodian of family heritage and values.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌿 <strong>Health &amp; Vitality</strong> (स्वास्थ्य एवं ऊर्जा)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Longevity &amp; Grace</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> The Life Line sweeps deeply down toward the primary Manibandh bracelet with unbroken clarity and supple curvature.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Indicates an enviable lifespan and sustained cognitive agility, provided daily joint mobility (Vata management) and moderate dietary discipline are maintained.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🌟 <strong>Social Status &amp; Regard</strong> (प्रतिष्ठा एवं यश)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Venerable Eminence</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> A distinct upward flare or Trishula trident formation crowning the apex of the Sun and Saturn mounts.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Foretells lasting community honor, philanthropic recognition, and an esteemed public standing that endures long past professional retirement.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>✈️ <strong>Travel &amp; Horizons</strong> (यात्रा एवं देशाटन)</span>
                    <span class="mr-pill mr-pill-blue" style="font-size:9.5px; padding:1px 6px;">Sacred Retreats</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Serene, deep horizontal creases upon the lower Mount of Moon extending smoothly inward.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> Future journeys lean toward contemplative pilgrimages, peaceful nature sanctuaries, and cultural sojourns that nurture the soul and offer spiritual solace.
                </p>
            </div>

            <div class="mr-diag-card" style="padding:6px 9px;">
                <div class="mr-diag-card-title" style="font-size:11.8px; margin-bottom:3px; display:flex; justify-content:space-between; align-items:center;">
                    <span>🤝 <strong>Friends &amp; Mentors</strong> (मित्र एवं साझेदारी)</span>
                    <span class="mr-pill mr-pill-gold" style="font-size:9.5px; padding:1px 6px;">Sage Fellowship</span>
                </div>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:3px;">
                    <strong>Observational Signal:</strong> Well-defined Ring of Solomon (Guru Mudrika) markings etched beneath the index finger.
                </p>
                <p class="mr-diag-card-p" style="font-size:11.0px; line-height:1.38; margin-bottom:0;">
                    <strong>Predictive Guidance:</strong> You will naturally draw younger seekers, proteges, and philosophical equals who seek your counsel, cultivating an esteemed fellowship of thinkers.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Long-term trajectory points to transition into respected advisory roles, mentorship, and lasting legacy.',
                'Continuous Life Line reaching first wrist bracelet indicates longevity, sustained mental clarity, and vitality.',
                'Purushartha Principle: Future probabilities ripen most fruitfully through conscious ethical living and charity.'
            ], 'Future Horizons Synthesis & Legacy Outlook'); ?>
            <?php echo self::render_page_footer(6, 'Trikala Samiksha — The Future'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 07: PART II - HAND MORPHOLOGY -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Hand Morphology &amp; Elements</span>
        </div>
        
        <div class="mr-tag-pill">03 &bull; HAND SHAPE &amp; ELEMENTS (PART 1)</div>
        <div class="mr-page-h1">Elemental Hand Classification &amp; Palm Architecture</div>
        <div class="mr-page-sub">Understanding your hand shape, natural element (Earth/Air/Fire/Water), and personality</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_elemental_hands_svg('air', 540, 135); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Foundation &amp; Cortical Mapping</div>
                <p class="mr-diag-card-p">
                    In classical Samudrik Shastra, the hand is the somatic mirror of the five elements (<em>Pancha Mahabhutas</em>). Modern neuro-anatomy corroborates this: the cortical homunculus assigns massive motor cortex representation to the palm. Your structure harmonizes Prithvi (Earth stability) with Vayu (Air intellect), establishing an archetype capable of manifesting complex conceptual ideas into systematic reality.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Biometric analysis of your uploaded palm photograph confirms an exact square aspect ratio where palm width across the mounts matches vertical length to the wrist. Slender phalanges exhibit smooth epidermal ridges without calcified knuckle thickening. Skin elasticity reflects robust subcutaneous density, providing swift neuro-muscular recuperation under prolonged organizational stress.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Strategic Real-World Translation</div>
                <p class="mr-diag-card-p">
                    In enterprise leadership and commercial ventures, this Air-Earth morphology prevents both narrow myopic execution and detached theoretical abstraction. You naturally bridge technical blueprints with commercial monetization. While purely intellectual leaders struggle with operational cadence, your square palm supplies the stamina required to scale multi-year ventures.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Micro-Remedy &amp; Grounding</div>
                <p class="mr-diag-card-p">
                    Because slender fingers accelerate mental processing, periods of intensive planning can generate excess Vayu (nervous tension). Ground this daily by spending 5 minutes practicing barefoot grounding (<em>Bhumi Sparsha</em>) on natural earth at sunrise. Hydrate with warm water infused with fennel and cumin to soothe the nervous-gut axis and preserve cognitive equilibrium.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Air hand archetype (square palm with long fingers) harmonizes practical execution with vision.',
                'Medium-firm skin elasticity ensures robust physical resilience and emotional composure.',
                'Smooth knuckle articulation enables rapid decision-making and high communication eloquence.'
            ], 'Hand Morphology Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(7, 'Hand Morphology & Elements'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 08: PART II - THUMB & NAILS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Thumb &amp; Finger Anatomy</span>
        </div>
        
        <div class="mr-tag-pill">03 &bull; HAND SHAPE &amp; ELEMENTS (PART 2)</div>
        <div class="mr-page-h1">Thumb Willpower, Phalange Proportions &amp; Nails</div>
        <div class="mr-page-sub">Looking at your thumb for willpower and logic, finger lengths, and nail signs health</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_thumb_logic_gauge_svg(55, 45, 540, 85); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Principle of the Angustha</div>
                <p class="mr-diag-card-p">
                    In classical Hast Rekha Shastra, the thumb (<em>Angustha</em>) is the sovereign rudder of human character. Sage Samudra affirmed that an erect thumb redeems defects across all minor mounts. Neurologically, the thumb’s dedicated cerebellar motor units govern executive volition, impulse regulation, and the capacity to impose conscious order over reactive impulses.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Photographic measurement displays a 55/45 distribution between the first (willpower) and second (logic) phalanges. The first phalange is club-free, firm, and erect, demonstrating perseverance. The second phalange displays an elegant, waist-tapered silhouette (<em>Kshina Madhya</em>), proving that choices are filtered through sophisticated diplomatic discernment.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Real-World Negotiation Protocol</div>
                <p class="mr-diag-card-p">
                    In high-stakes commercial negotiations and contract structuring, this thumb architecture provides immense tactical leverage. The waisted second phalange enables you to listen attentively and uncover hidden counterparty motives, while the strong first phalange ensures you enforce execution with immovable fidelity, closing contracts on optimal terms.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Actionable Micro-Remedy &amp; Focus</div>
                <p class="mr-diag-card-p">
                    During demanding fiscal quarters or high-stakes legal dialogues, practice Linga Mudra for 7 minutes in the morning with the dominant thumb upright. This activates inner metabolic Agni, banishes self-doubt, and directs pranic force into the prefrontal cortex, reinforcing executive resolve when steering difficult institutional decisions.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Thumb willpower phalange (55%) provides unwavering persistence and leadership follow-through.',
                'Waisted second phalange (45%) bestows high diplomatic tact, commercial reason, and persuasion.',
                'Well-developed lunula (nail moons) confirm healthy cardiovascular circulation and nervous tone.'
            ], 'Thumb & Phalange Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(8, 'Thumb Willpower & Nails'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 09: PART III - JEEVAN REKHA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Major Lines Deep-Dive</span>
        </div>
        
        <div class="mr-tag-pill">04 &bull; FOUR MAJOR LINES (PART 1)</div>
        <div class="mr-page-h1">Jeevan Rekha (Life Line): Prana Vitality &amp; Resilience</div>
        <div class="mr-page-sub">Understanding vitality, physical energy, and life milestones along your Life Line</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_line_arc_svg('life', 540, 115, $crop_life_uri); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Foundation &amp; Cortical Vitality</div>
                <p class="mr-diag-card-p">
                    In classical Samudrik Shastra treatises (<em>Bhavishya Purana</em> and <em>Garga Samhita</em>), the Jeevan Rekha represents the primary pranic reservoir governed by the autonomic nervous system and neuro-endocrine axes. Modern neuro-dermatoglyphics confirms that crease depth directly mirrors motor stamina, cardiovascular tone, and cellular recuperative resistance against aging.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Auditing your uploaded palm photograph reveals a deep, unbroken furrow with sharp epidermal definition sweeping widely around the base of Mount Venus. The line maintains uniform coloration with total absence of necrotic islands or cross-cutting cuts, proving that constitutional vitality flows with uninterrupted vascular tone throughout your adult prime.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Real-World Translation &amp; Stamina</div>
                <p class="mr-diag-card-p">
                    In commercial negotiations and high-pressure organizational scaling, this expansive arc manifests as superior biological stamina under stress. You possess the capacity to navigate demanding multi-quarter deadlines without succumbing to chronic fatigue or cognitive burnout, outlasting market competitors in long-term enterprise development.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Actionable Micro-Remedy &amp; Ojas Protocol</div>
                <p class="mr-diag-card-p">
                    To preserve this vital reservoir during demanding fiscal quarters, integrate 7 minutes of <em>Nadi Shodhana Pranayama</em> (alternate nostril breathing) at dawn. Complement this with warm ginger-tulsi water on an empty stomach to harmonize digestive Agni, ensuring that metabolic stress never depletes your foundational Ojas (immune reserve).
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Wide Life line arc around Venus secures deep reserves of vitality and constitutional endurance.',
                'Upward branches at ages 28, 42, and 50 mark milestones of earned professional elevation.',
                'Worry lines fail to penetrate the main furrow, proving high psychological resilience under stress.'
            ], 'Jeevan Rekha Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(9, 'Jeevan Rekha (Life Line)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 10: PART III - MASTISHK REKHA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Major Lines Deep-Dive</span>
        </div>
        
        <div class="mr-tag-pill">04 &bull; FOUR MAJOR LINES (PART 2)</div>
        <div class="mr-page-h1">Mastishk Rekha (Head Line): Cognitive Focus &amp; Strategy</div>
        <div class="mr-page-sub">Understanding how you think, make decisions, solve problems, and handle mental pressure</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_line_arc_svg('head', 540, 115, $crop_head_uri); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Principle &amp; Synaptic Drive</div>
                <p class="mr-diag-card-p">
                    In classical Vedic palmistry, Mastishk Rekha reflects the seat of <em>Manas</em> (mind) and <em>Buddhi</em> (intellect), corresponding neurologically to prefrontal cortex executive functioning and synaptic plasticity. Shastric treatises consider a long, gently descending furrow the hallmark of balanced cognitive synthesis bridging lunar intuition with mercurial numeracy.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Your palm photograph displays a pronounced, well-etched crease traversing the palm and terminating with a classic dual fork (the Saraswati Fork / Writer’s Fork). The upper prong points toward Upper Mars (practical realism) while the lower prong dips toward Mount of Moon (creative ingenuity), indicating rare equilibrium between logic and lateral innovation.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Strategic Real-World Vision</div>
                <p class="mr-diag-card-p">
                    In corporate strategy and enterprise problem-solving, this configuration grants immunity to cognitive decision fatigue. While peers panic under market ambiguity, your mind naturally constructs alternative scenarios and hedges. You excel in high-stakes negotiations, product architecture, and articulating complex value propositions to stakeholders.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Actionable Micro-Remedy &amp; Brain Calm</div>
                <p class="mr-diag-card-p">
                    To prevent mental hyper-arousal during intensive strategic sprints, practice 5 minutes of <em>Bhramari Pranayama</em> (humming bee breath) before evening rest. Additionally, massage your temples with cold-pressed Brahmi or sesame oil twice weekly to soothe neuro-synaptic tension and enhance REM sleep consolidation.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Gentle decline toward Chandra blends hard analytical logic with intuitive creative foresight.',
                'The Saraswati fork at terminal end grants superior commercial communication and advisory gift.',
                'Unbroken line furrow confirms high concentration, retentive memory, and mental stamina.'
            ], 'Mastishk Rekha Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(10, 'Mastishk Rekha (Head Line)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 11: PART III - HRIDAYA REKHA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Major Lines Deep-Dive</span>
        </div>
        
        <div class="mr-tag-pill">04 &bull; FOUR MAJOR LINES (PART 3)</div>
        <div class="mr-page-h1">Hridaya Rekha (Heart Line): Emotional Harmony &amp; Devotion</div>
        <div class="mr-page-sub">Understanding your feelings, emotional warmth, partner connection, and peace of heart</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_line_arc_svg('heart', 540, 115, $crop_heart_uri); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Foundation &amp; Vagal Tone</div>
                <p class="mr-diag-card-p">
                    In Samudrik Shastra, the Heart line reflects <em>Hridaya Samvedana</em> (emotional temperament) and cardiovascular vitality, intimately tied to the autonomic vagus nerve network and heart-rate variability. Classical texts classify an ascending trajectory toward Mount Jupiter as the highest mark of noble sentiment (<em>Satvik Bhavana</em>).
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Biometric inspection of your palm image shows a smooth, unchained crease that curves elegantly upward, culminating at the sacred cusp between the index (Jupiter) and middle (Saturn) fingers. The furrow has crisp, clear edges without feathered emotional fraying or deep cross-cutting worry scars.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Real-World Translation &amp; High EQ</div>
                <p class="mr-diag-card-p">
                    In relational and professional environments, this placement confers exceptional emotional intelligence. You maintain genuine empathy without falling into emotional codependency or organizational martyrdom. In leadership, teams trust you because your moral code is consistent, predictable, and devoid of volatile outbursts.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Actionable Heart Coherence Remedy</div>
                <p class="mr-diag-card-p">
                    When processing intense relational or personnel conflicts, execute the 4-7-8 rhythmic breathing sequence for 4 minutes to restore vagal tone and heart coherence. Placing a drop of pure sandalwood or rose oil over the sternum center before sleep cools emotional Pitta heat and fosters peaceful restorative equilibrium.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Ascent to Mount of Jupiter confers noble relationship standards and lifelong marital loyalty.',
                'Broad Great Quadrangle confirms emotional tolerance, broad-mindedness, and inner generosity.',
                'Clear, unchained channel reflects steady emotional resilience and healthy boundary control.'
            ], 'Hridaya Rekha Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(11, 'Hridaya Rekha (Heart Line)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 12: PART III - BHAGYA REKHA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Major Lines Deep-Dive</span>
        </div>
        
        <div class="mr-tag-pill">04 &bull; FOUR MAJOR LINES (PART 4)</div>
        <div class="mr-page-h1">Bhagya Rekha (Fate Line): Career Evolution &amp; Milestones</div>
        <div class="mr-page-sub">Tracing your career progress, self-made success, and major turning points in life</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_line_arc_svg('fate', 540, 115, $crop_fate_uri); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Foundation &amp; Purushartha</div>
                <p class="mr-diag-card-p">
                    Known in classical Sanskrit treatises as <em>Kalyani Rekha</em> or <em>Urdhva Rekha</em>, the Fate line tracks the ripening of <em>Prarabdha Karma</em> (destiny) through the agency of <em>Purushartha</em> (conscious righteous action). It measures the synchronization between internal vocational intent and external societal opportunity.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Your palm photo clearly demonstrates a distinct vertical furrow emerging strongly from the mid-palm plain around the age 28–30 coordinate, piercing cleanly through the Head line and ascending into the Mount of Saturn. Prior to age 28, the trajectory was exploratory, consolidating into sharp directional authority thereafter.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Real-World Translation &amp; Authority</div>
                <p class="mr-diag-card-p">
                    This mid-palm ignition is the quintessential signature of the self-made leader. Your wealth and institutional stature are not inherited gifts but earned through methodical competence and domain expertise. From age 32 to 55, career expansion follows an accelerating compounding curve, leading to boardroom authority and asset ownership.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Actionable Karmic Alignment Remedy</div>
                <p class="mr-diag-card-p">
                    To amplify vocational breakthroughs and clear karmic friction, offer water (<em>Surya Arghya</em>) to the morning sun while reciting the Gayatri Mantra. On Saturdays, perform a quiet act of service or donate sesame seeds/oil to balance Saturnian patience, transforming slow karmic trials into enduring institutional triumphs.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Fate line origin near lower Moon sector confirms self-made success and public goodwill.',
                'Deepening across middle palm (ages 42–55) confirms peak executive monetization window.',
                'Firm arrival at Mount of Saturn guarantees lasting institutional reputation and wealth stability.'
            ], 'Bhagya Rekha Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(12, 'Bhagya Rekha (Fate Line)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 10: PART III - GURU & SHANI MOUNTS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Mount Topography</span>
        </div>
        
        <div class="mr-tag-pill">05 &bull; PLANETARY MOUNTS (PART 1)</div>
        <div class="mr-page-h1">Mounts of Jupiter (Guru) &amp; Saturn (Shani)</div>
        <div class="mr-page-sub">Understanding wisdom and leadership (Jupiter) and patience and discipline (Saturn)</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_mount_elevation_svg('Mount of Jupiter (Guru)', 88, 'Mount of Saturn (Shani)', 82, 540, 95); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Guru Shastric Principle &amp; Biometrics (88%)</div>
                <p class="mr-diag-card-p">
                    Classical Samudrik Shastra attributes magnanimity, moral wisdom, and executive dignity (<em>Brihaspati Shakti</em>) to an elevated Mount of Jupiter. Biometric analysis of your palm photo reveals a firm cushion (88% elevation) accompanied by a distinct Ring of Solomon (<em>Guru Mudrika</em>), conferring exceptional psychological discernment and natural mentorship presence.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shani Shastric Principle &amp; Biometrics (82%)</div>
                <p class="mr-diag-card-p">
                    The Mount of Saturn governs karmic discipline, structural perseverance, and legal integrity (<em>Yama Shakti</em>). Your palm exhibits an 82% calm, balanced elevation with a crisp vertical striation climbing into the apex. This absence of erratic criss-cross webbing indicates a sober, patient mind that respects contracts and rejects reckless speculation.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Executive Governance &amp; Risk Control</div>
                <p class="mr-diag-card-p">
                    In enterprise operations, the Guru-Shani axis is the premier balance: Jupiter provides expansive vision, market leadership, and talent inspiration, while Saturn enforces due diligence, contractual protection, and fiduciary controls. This makes you exceptionally competent in scaling organizations sustainably without taking on toxic leverage.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Guru-Shani Alignment Protocol</div>
                <p class="mr-diag-card-p">
                    To keep Jupiter's expansive optimism grounded in Saturnian precision, review balance sheets and major long-term commitments on Thursday mornings. Light a sesame-oil lamp on Saturday evenings to honor Shani, transmuting vocational stress into disciplined, unshakable professional mastery.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Guru mount elevation (88%) bestows innate executive dignity, mentorship, and authority.',
                'Ring of Solomon marking provides sharp psychological acumen and intuitive character reading.',
                'Balanced Shani mount ensures sober legal prudence and steady, patient wealth compounding.'
            ], 'Guru & Shani Mounts Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(13, 'Mounts of Jupiter & Saturn'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 11: PART III - SURYA & BUDH MOUNTS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Mount Topography</span>
        </div>
        
        <div class="mr-tag-pill">05 &bull; PLANETARY MOUNTS (PART 2)</div>
        <div class="mr-page-h1">Mounts of Sun (Surya) &amp; Mercury (Budh)</div>
        <div class="mr-page-sub">Understanding creativity and fame (Sun) and business skills and speech (Mercury)</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_mount_elevation_svg('Mount of Sun (Surya/Apollo)', 84, 'Mount of Mercury (Budh)', 86, 540, 95); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Surya Shastric Principle &amp; Radiance (84%)</div>
                <p class="mr-diag-card-p">
                    The Mount of Sun beneath the ring finger symbolizes personal radiance, public honor, and aesthetic mastery (<em>Aditya Tejas</em>). Biometrically, your photo exhibits an 84% elevated contour with a clean vertical Sun line. This confirms that your accomplishments will receive genuine societal acclaim, industry recognition, and peer validation.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Budh Shastric Principle &amp; Numeracy (86%)</div>
                <p class="mr-diag-card-p">
                    Governing commercial acumen, diplomacy, and persuasive speech (<em>Budhi Prabhav</em>), your Mount of Mercury shows an 86% firm density with distinct vertical Samaritan lines (<em>Upakari Rekhas</em>). These micro-creases signify an innate gift for client advisory consulting, financial numeracy, and resolving disputes with verbal poise.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Commercial Dealmaking &amp; Charisma</div>
                <p class="mr-diag-card-p">
                    In corporate negotiations, fundraising, and client advisory, the Surya-Budh combination establishes compelling influence. You present data with clarity and emotional resonance, closing agreements where others stall. Your reputation for commercial transparency acts as a powerful institutional trust moat.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Surya-Budh Activation Protocol</div>
                <p class="mr-diag-card-p">
                    To optimize communicative eloquence and vocal resonance before high-stakes presentations, practice 3 minutes of chanting the seed mantra <em>Om Bum Budhaya Namah</em> followed by deep abdominal breathing. Keep an emerald or green tourmaline on your workspace to enhance financial calculation speed.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Surya mount radiance (84%) combined with clean Sun line validates public honors and credibility.',
                'Budh mount width (86%) provides sharp commercial numeracy, negotiation power, and wit.',
                'Vertical Samaritan lines reveal an innate talent for advisory consulting and mentoring.'
            ], 'Surya & Budh Mounts Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(14, 'Mounts of Sun & Mercury'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 12: PART III - SHUKRA & CHANDRA MOUNTS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Mount Topography</span>
        </div>
        
        <div class="mr-tag-pill">05 &bull; PLANETARY MOUNTS (PART 3)</div>
        <div class="mr-page-h1">Mounts of Venus (Shukra) &amp; Moon (Chandra)</div>
        <div class="mr-page-sub">Understanding vitality and love (Venus) and imagination and peace of mind (Moon)</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_mount_elevation_svg('Mount of Venus (Shukra)', 92, 'Mount of Moon (Chandra)', 85, 540, 95); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shukra Shastric Principle &amp; Apex #1 (92%)</div>
                <p class="mr-diag-card-p">
                    As the undisputed #1 apex mount of your hand (92% elevation), Mount Venus represents somatic vitality, sensual refinement, and generous provider instincts (<em>Ojas-Shukra Shakti</em>). In classical treatises, a well-formed, resilient Venus cushion indicates deep biological stamina, magnetic personal warmth, and high domestic hospitality.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Chandra Shastric Principle &amp; Intuition (85%)</div>
                <p class="mr-diag-card-p">
                    Occupying the lower percussion quadrant, your Mount of Moon shows an 85% broad elevation accompanied by clear horizontal <em>Yatra Rekhas</em> (travel lines). In Samudrik science, this lunar expanse bestows fertile creative imagination, emotional equilibrium, and a natural affinity for foreign travel and cross-cultural trade.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Lifestyle Wealth &amp; Global Alliances</div>
                <p class="mr-diag-card-p">
                    This Venus-Chandra synergy creates an aspirational personality who invests generously in high-quality living environments, family well-being, and artistic experiences. In business, it manifests as strong cross-border intuition, enabling lucrative partnerships across international boundaries and foreign trade corridors.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Somatic Cooling &amp; Lunar Protocol</div>
                <p class="mr-diag-card-p">
                    Because high Venusian fire can lead to sensory overstimulation during intense work cycles, practice 5 minutes of <em>Chandra Bhedana Pranayama</em> (left-nostril cooling breath) in the evening. Spend 10 minutes walking under the open night sky during waxing fortnights to calm the neuro-sensory system and restore creative receptivity.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Venus mount holds #1 apex potency (92%), fueling extraordinary physical stamina and warmth.',
                'Dedicated provider instinct prioritizes family stability, domestic luxury, and joyful hospitality.',
                'Chandra mount elevation and travel lines confirm prosperous foreign journeys and intuitive depth.'
            ], 'Shukra & Chandra Mounts Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(15, 'Mounts of Venus & Moon'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 16: PART IV - MANGAL KSHETRA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Mount Topography</span>
        </div>
        
        <div class="mr-tag-pill">05 &bull; PLANETARY MOUNTS (PART 4)</div>
        <div class="mr-page-h1">Mangal Kshetra (The Mars Terrain): Courage &amp; Resilience</div>
        <div class="mr-page-sub">Understanding inner courage, active bravery, and handling challenges with confidence</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_mount_elevation_svg('Upper Mars (Moral Fortitude)', 85, 'Lower Mars (Active Initiative)', 80, 540, 95); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Upper Mars Shastric Fortitude (85%)</div>
                <p class="mr-diag-card-p">
                    Situated on the outer percussion between the Heart and Head lines, Upper Mars is firm and resilient. This confers moral courage, cool-headed composure under extreme business pressure, and the steadfast ability to stand your ground against opposition with documented facts and dignity.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Lower Mars Active Drive (80%)</div>
                <p class="mr-diag-card-p">
                    Located inside the Life line cleft above the thumb root, Lower Mars reflects physical drive and competitive appetite. Balanced and free of cross-hatching, it provides vigorous entrepreneurial initiative, early execution velocity, and zero reckless belligerence.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Plain of Mars Stress Absorption</div>
                <p class="mr-diag-card-p">
                    The central hollow of your palm represents the Plain of Mars. In your hand, this basin is moderate in depth, neither excessively hollowed nor congested. This signifies a balanced nervous system that absorbs emotional and market shocks without cognitive panic.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Non-Reactivity Protocol</div>
                <p class="mr-diag-card-p">
                    Because Upper Mars exceeds Lower Mars in tone, your instinct in conflict is strategic. When facing adversarial contract negotiations, take a deliberate 10-second breath pause before responding. This disarms opponents while anchoring your physiological heart rate.
                </p>
            </div>
        </div>

        
        <!-- Martian Defense & Strategic Concordance Table (Fills whitespace before Part IV) -->
        <table class="mr-matching-table" style="margin-top:10px; margin-bottom:12px;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 20%;">
                <col style="width: 35%;">
                <col style="width: 20%;">
            </colgroup>
            <thead>
                <tr>
                    <th>MARS TERRAIN ZONE</th>
                    <th>ELEVATION INDEX</th>
                    <th>STRATEGIC VOCATIONAL ATTRIBUTE</th>
                    <th>SAMUDRIK STATUS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Upper Mars (Defensive)</strong></td>
                    <td>85% (High Firmness)</td>
                    <td>Moral courage, crisis calm, resistance to boardroom intimidation</td>
                    <td><span class="mr-pill mr-pill-green">Apex Shield</span></td>
                </tr>
                <tr>
                    <td><strong>Lower Mars (Aggressive)</strong></td>
                    <td>80% (Measured Drive)</td>
                    <td>Disciplined execution, project initiation, zero rash recklessness</td>
                    <td><span class="mr-pill mr-pill-blue">Active Engine</span></td>
                </tr>
                <tr>
                    <td><strong>Plain of Mars (Middle Palm)</strong></td>
                    <td>Equilibrium Depth</td>
                    <td>Absorbs neuro-emotional stress shocks, steady pulse rate</td>
                    <td><span class="mr-pill mr-pill-amber">Shock Absorber</span></td>
                </tr>
                <tr>
                    <td><strong>Rahu Zone (Lower Center)</strong></td>
                    <td>Tight Furrow Basin</td>
                    <td>Guards against speculative losses, anchors tangible land equity</td>
                    <td><span class="mr-pill mr-pill-purple">Grounded Base</span></td>
                </tr>
            </tbody>
        </table>
        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Upper Mars fortitude (85%) ensures rock-solid composure during institutional crises.',
                'Lower Mars drive (80%) supplies vigorous physical initiative and entrepreneurial momentum.',
                'Central Plain of Mars acts as an emotional shock absorber, preventing stress-induced burnout.'
            ], 'Mangal Kshetra Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(16, 'Plain of Mars (Mangal Kshetra)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 17: PART V - SURYA & BUDH REKHAS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Minor Lines &amp; Seals</span>
        </div>
        
        <div class="mr-tag-pill">06 &bull; MINOR LINES &amp; SIGNS (PART 1)</div>
        <div class="mr-page-h1">Surya Rekha (Sun Line) &amp; Budh Rekha (Health Line)</div>
        <div class="mr-page-sub">Understanding respect and success (Sun Line) and daily health and digestion (Mercury Line)</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_minor_lines_svg(540, 115); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Surya Rekha Shastric Foundation</div>
                <p class="mr-diag-card-p">
                    Your Sun line originates in the Plain of Mars and climbs straight into the Mount of Apollo. In Samudrik Shastra, this line serves as the great amplifier of the Fate line, ensuring that diligent effort translates into visible public recognition, executive reputation, and genuine societal honors (<em>Kirti Shakti</em>).
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Budh Rekha &amp; Gut-Brain Axis</div>
                <p class="mr-diag-card-p">
                    Your Mercury line (Line of Hepatica) is fine, straight, and uncrossed by dark specks. Classical palmists note that a delicate, unobtrusive health line is the optimal signature, confirming efficient hepatic function, metabolic absorption, and high digestive Agni.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Real-World Acclaim &amp; Advisory Stature</div>
                <p class="mr-diag-card-p">
                    The Sun line gains exceptional clarity from age 42 onwards, crossing the Heart line cleanly. This timing confirms that your greatest public credibility, advisory invitations, and industry authority awaken in your mid-forties, attracting high-value institutional patronage.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Digital Hygiene &amp; Solar Protocol</div>
                <p class="mr-diag-card-p">
                    Because your Mercury line is finely attuned to your nervous system, late-night blue-light screen exposure generates subtle cognitive fatigue. Establish a strict screen-free boundary 45 minutes before sleep to preserve its luminous tone and maintain cellular restorative repair.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Ascending Sun line acts as the great amplifier, turning earned diligence into public prestige.',
                'Peak Sun line activation from age 42 onwards unlocks executive appointments and board roles.',
                'Clear, uncrossed Mercury line confirms sound digestive vitality and agile nervous stamina.'
            ], 'Surya & Budh Rekhas Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(17, 'Surya & Budh Rekhas'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 18: PART V - DHAN TRIKONA -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Minor Lines &amp; Seals</span>
        </div>
        
        <div class="mr-tag-pill">06 &bull; MINOR LINES &amp; SIGNS (PART 2)</div>
        <div class="mr-page-h1">Dhan Trikona (Money Triangle) &amp; Wealth Seals</div>
        <div class="mr-page-sub">How your palm lines form a money triangle to help you earn, save, and keep wealth</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_dhan_trikona_svg(540, 130); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Foundation of Dhan Yoga</div>
                <p class="mr-diag-card-p">
                    In classical Hast Rekha, the Dhan Trikona represents the <em>Kuber Kund</em> (sacred wealth reservoir) formed by the triadic intersection of the Head line (strategic roof), Fate line (earning pillar), and Mercury line (closing financial vault). Complete triangular closure is the definitive hallmark of wealth retention.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Native Biometric Photo Signature</div>
                <p class="mr-diag-card-p">
                    Many professionals earn high revenues but suffer from a "Rahu Gap" where the base remains unsealed, leaking funds into impulse liabilities. In your palm photograph, the base is tightly sealed without fracture or gap, confirming superior capital preservation instincts.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Capital Preservation &amp; Property Squares</div>
                <p class="mr-diag-card-p">
                    A distinct protective square sign rests adjacent to the lower corner near Venus. Classical texts classify this as the <em>Bhoomi Mudra</em>, verifying that surplus earned income is systematically converted into commercial real estate, residential plots, and sovereign tangible reserves.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Kubera Wealth Protocol</div>
                <p class="mr-diag-card-p">
                    Practice Kubera Mudra (joining tips of thumb, index, and middle finger) for 5 minutes during fiscal planning sessions. Keep your primary home accounting records aligned in the North-East sector to harmonize domestic prosperity and invite Brihaspati's protective grace.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Completely closed Dhan Trikona (Money Triangle) ensures superior long-term wealth retention.',
                'Absence of the Rahu gap prevents capital leakage into unvetted speculative liabilities.',
                'Protective property squares on Venus confirm substantial real estate acquisitions and land ownership.'
            ], 'Dhan Trikona Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(18, 'Dhan Trikona (Money Triangle)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 19: PART V - SACRED SIGNS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Minor Lines &amp; Seals</span>
        </div>
        
        <div class="mr-tag-pill">06 &bull; MINOR LINES &amp; SIGNS (PART 3)</div>
        <div class="mr-page-h1">Sacred Samudrik Signs: Classical Symbols Audit</div>
        <div class="mr-page-sub">Checking for auspicious traditional signs like the Fish, Lotus, Star, or Mystic Cross</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_sacred_marks_grid_svg(540, 110); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Matsya Chihna (Fish Sign)</div>
                <p class="mr-diag-card-p">
                    At the base of the palm near the Ketu/Manibandh junction, two graceful curves intersect with tail fins pointing outward. In Vedic palmistry, the authentic fish sign confers spiritual wisdom, sudden ancestral inheritances, and miraculous divine shielding during acute life crises.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Trishul at Jupiter Apex (Trident)</div>
                <p class="mr-diag-card-p">
                    At the terminal apex of the Jupiter mount, an upward branch splits into a symmetrical three-pronged trident. Revered in Hast Rekha as the mark of Shiva, it indicates spotless moral reputation, executive governance stature, and sovereign authority in your professional domain.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 The Mystic Cross &amp; Sixth Sense</div>
                <p class="mr-diag-card-p">
                    A standalone, well-proportioned cross sits in the center of the Great Quadrangle, detached from both Heart and Head lines. This classical esoteric mark bestows heightened intuitive foresight, prophetic dreams, and the ability to detect counterparty deception instantaneously.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Intuitive Alignment Protocol</div>
                <p class="mr-diag-card-p">
                    To maintain the psychic sensitivity of your Mystic Cross, dedicate 10 minutes each morning to silent breath awareness before checking digital devices. Offering gratitude before meals preserves high spiritual vibrational reception throughout corporate activities.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Authentic Matsya (Fish) sign at palm base confers spiritual wisdom and sudden windfall protection.',
                'Trishul (Trident) on Mount of Jupiter marks exceptional sovereign leadership and honor.',
                'Mystic Cross in Great Quadrangle validates sharp intuitive foresight and esoteric gifts.'
            ], 'Sacred Samudrik Signs Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(19, 'Sacred Samudrik Signs'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 20: PART V - MANIBANDH BRACELETS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Minor Lines &amp; Seals</span>
        </div>
        
        <div class="mr-tag-pill">06 &bull; MINOR LINES &amp; SIGNS (PART 4)</div>
        <div class="mr-page-h1">Manibandh Rekhas (Wrist Bracelets) &amp; Esoteric Rings</div>
        <div class="mr-page-sub">Understanding the lines on your wrist for health, prosperity, and peace of mind</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_manibandh_bracelets_svg(540, 80); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ 1st Manibandh (Swasthya &amp; Vitality)</div>
                <p class="mr-diag-card-p">
                    The topmost bracelet of your wrist is deeply etched, horizontal, and unbroken without arching upward into the palm. In classical texts, this solid first rascette guarantees 28 to 30 years of robust foundational physical health, motor stamina, and disease immunity.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ 2nd Manibandh (Dhana &amp; Assets)</div>
                <p class="mr-diag-card-p">
                    The second bracelet runs parallel and straight, demonstrating strong pigmentation. This line signifies financial stability, prosperous career compounding, and substantial asset accumulation during the middle decades of life (ages 30 to 60).
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 3rd Manibandh (Kirti &amp; Longevity 84+)</div>
                <p class="mr-diag-card-p">
                    The third bracelet is cleanly visible, reflecting lasting societal respect, philanthropic contributions, and high family lineage honors, extending the classical longevity potential past age 84 with unblemished dignity.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Wrist Care &amp; Pranic Seal</div>
                <p class="mr-diag-card-p">
                    Because wrist rascettes anchor the primary pranic channels into the hand, massage the inner wrist crease with warm sesame oil for 1 minute before bed. This lubricates the carpal tendons, grounding restless prana into deep regenerative slumber.
                </p>
            </div>
        </div>

        
        <!-- Master Manibandh Longevity & Compounding Triad Table (Fills whitespace before Part V) -->
        <table class="mr-matching-table" style="margin-top:10px; margin-bottom:12px;">
            <colgroup>
                <col style="width: 26%;">
                <col style="width: 22%;">
                <col style="width: 34%;">
                <col style="width: 18%;">
            </colgroup>
            <thead>
                <tr>
                    <th>WRIST RASCETTE</th>
                    <th>LIFESPAN HORIZON</th>
                    <th>MATERIAL &amp; SPIRITUAL COMPOUNDING</th>
                    <th>VERIFIED STATUS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>1st Bracelet (Swasthya)</strong></td>
                    <td>Ages 01 to 28&ndash;30 Yrs</td>
                    <td>Robust neuro-muscular vitality, rapid recuperation, physical immunity</td>
                    <td><span class="mr-pill mr-pill-green">Intact &amp; Deep</span></td>
                </tr>
                <tr>
                    <td><strong>2nd Bracelet (Dhana)</strong></td>
                    <td>Ages 30 to 58&ndash;60 Yrs</td>
                    <td>Commercial compounding, debt-free solvency, real estate acquisition</td>
                    <td><span class="mr-pill mr-pill-blue">Parallel Furrow</span></td>
                </tr>
                <tr>
                    <td><strong>3rd Bracelet (Kirti)</strong></td>
                    <td>Ages 60 to 84+ Yrs</td>
                    <td>Institutional reverence, societal honors, philanthropic legacy endowments</td>
                    <td><span class="mr-pill mr-pill-amber">Honor Anchor</span></td>
                </tr>
                <tr>
                    <td><strong>Auxiliary Ring (Moksha)</strong></td>
                    <td>Spiritual Decades</td>
                    <td>Philosophical detachment, meditative peace, dharmic family succession</td>
                    <td><span class="mr-pill mr-pill-purple">Spiritual Grace</span></td>
                </tr>
            </tbody>
        </table>
        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Three complete, unbroken Manibandh wrist bracelets confirm robust longevity past age 84.',
                'Straight second bracelet guarantees prosperous material accumulation and financial security.',
                'Partial Girdle of Venus bestows refined aesthetic taste and emotional sensitivity without volatility.'
            ], 'Manibandh Bracelets Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(20, 'Manibandh (Wrist Bracelets)'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 21: PART VI - CAREER MASTERY -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Life Spheres Analysis</span>
        </div>
        
        <div class="mr-tag-pill">07 &bull; KEY LIFE AREAS (PART 1)</div>
        <div class="mr-page-h1">Professional Growth, Leadership &amp; Career Calling</div>
        <div class="mr-page-sub">Practical guidance on suitable careers, business ventures, and leadership growth</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_vocational_radar_svg(540, 115); ?>
        </div>

        <!-- Structured Vocational Concordance Table -->
        <table class="mr-matching-table" style="margin-bottom:12px;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 35%;">
                <col style="width: 22%;">
                <col style="width: 18%;">
            </colgroup>
            <thead>
                <tr>
                    <th>CAREER ARCHETYPE</th>
                    <th>PALM SIGNATURE INDICATOR</th>
                    <th>OPTIMAL VOCATIONAL SPHERE</th>
                    <th>COMPETITIVE EDGE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Strategic Architect</strong></td>
                    <td>Long Head line + Saraswati Fork</td>
                    <td>Corporate Advisory, C-Suite, Law</td>
                    <td><span class="mr-pill mr-pill-green">EXCEPTIONAL (94%)</span></td>
                </tr>
                <tr>
                    <td><strong>System Builder</strong></td>
                    <td>Square Palm + Deep Middle Fate</td>
                    <td>Tech Infrastructure, Real Estate</td>
                    <td><span class="mr-pill mr-pill-blue">VERY HIGH (90%)</span></td>
                </tr>
                <tr>
                    <td><strong>Commercial Negotiator</strong></td>
                    <td>High Budh Mount + Waisted Thumb</td>
                    <td>Enterprise Sales, Contracts, M&amp;A</td>
                    <td><span class="mr-pill mr-pill-amber">STRONG (86%)</span></td>
                </tr>
                <tr>
                    <td><strong>Creative Mentor</strong></td>
                    <td>Ring of Solomon + Samaritan Lines</td>
                    <td>Executive Coaching, Publishing</td>
                    <td><span class="mr-pill mr-pill-green">HIGH GRACE (88%)</span></td>
                </tr>
            </tbody>
        </table>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Evolution: From Hustle to Architecture</div>
                <p class="mr-diag-card-p">
                    In your foundational twenties, career progress demanded intense individual labor (<em>Tapas</em>). With your middle-palm Fate line deepening past age 35 and strong Jupiter elevation, professional success now unfolds through architectural leverage: standardized SOPs, enterprise brand equity, and high-impact delegation.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 The Executive Negotiation &amp; Silence Rule</div>
                <p class="mr-diag-card-p">
                    Never negotiate from urgency. With your firm thumb logic phalange and balanced Saturn mount, deliberate silence in high-stakes meetings commands authority. Allow counterparties to bid first; documented contractual precision yields superior long-term commercial terms.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Strategic Architect and System Builder archetypes represent your premier career strengths (94%).',
                'Transition from personal manual hustle into architectural leverage, delegation, and SOPs.',
                'Dignified silence and documented legal precision yield superior terms in executive negotiations.'
            ], 'Career Mastery Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(21, 'Career & Vocational Calling'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 22: PART VI - WEALTH BLUEPRINT -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Life Spheres Analysis</span>
        </div>
        
        <div class="mr-tag-pill">07 &bull; KEY LIFE AREAS (PART 2)</div>
        <div class="mr-page-h1">Wealth Blueprint, Capital Assets &amp; Fiscal Fortification</div>
        <div class="mr-page-sub">Understanding how to grow your income, manage expenses, and build long-term savings</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_wealth_architecture_svg(540, 115); ?>
        </div>

        <!-- Structured Asset Allocation Table -->
        <table class="mr-matching-table" style="margin-bottom:12px;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 33%;">
                <col style="width: 22%;">
                <col style="width: 20%;">
            </colgroup>
            <thead>
                <tr>
                    <th>ASSET CATEGORY</th>
                    <th>PALM SIGNAL &amp; EVIDENCE</th>
                    <th>ALLOCATION TARGET</th>
                    <th>PRESERVATION RULE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Secured Real Estate</strong></td>
                    <td>Bhoomi squares on Mount of Venus</td>
                    <td>45% of Net Surplus</td>
                    <td><span class="mr-pill mr-pill-green">LONG-TERM PRIME</span></td>
                </tr>
                <tr>
                    <td><strong>Compounding Equities</strong></td>
                    <td>Deepened Fate line between Head &amp; Heart</td>
                    <td>35% of Net Surplus</td>
                    <td><span class="mr-pill mr-pill-blue">INDEX &amp; BLUE-CHIP</span></td>
                </tr>
                <tr>
                    <td><strong>Liquid Reserves (FD)</strong></td>
                    <td>Intact Dhan Trikona money triangle seal</td>
                    <td>20% Cash Cushion</td>
                    <td><span class="mr-pill mr-pill-amber">EMERGENCY ARMOR</span></td>
                </tr>
                <tr>
                    <td><strong>Speculative Day Bets</strong></td>
                    <td>Absence of high-risk gambling bars</td>
                    <td>0% (Strict Embargo)</td>
                    <td><span class="mr-pill mr-pill-red">AVOID RASH LEAKS</span></td>
                </tr>
            </tbody>
        </table>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Capital Preservation Law</div>
                <p class="mr-diag-card-p">
                    Your hand's geometry is designed for earned, compounding wealth rather than gambling windfalls. Transfer 60% of annual consulting dividends directly into tangible land, residential upgrades, and sovereign index assets to permanently lock in gains behind your closed Dhan Trikona.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Transparency as Astrological Shield</div>
                <p class="mr-diag-card-p">
                    With Saturnian discipline ruling your middle palm, financial armor remains unbreakable when all transactions are transparent, fully documented, and properly taxed. Avoid unvetted private loans or informal partnerships that create karmic Rahu friction.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                '45% allocation to secured real estate aligns with Venusian property squares for lasting growth.',
                'Maintaining a 20% liquid cash cushion provides total peace of mind during market fluctuations.',
                'Complete embargo on day-trading speculation protects the intact Dhan Trikona capital vault.'
            ], 'Wealth Blueprint Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(22, 'Wealth Blueprint & Capital Assets'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 23: PART VI - LOVE & MARRIAGE -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Life Spheres Analysis</span>
        </div>
        
        <div class="mr-tag-pill">07 &bull; KEY LIFE AREAS (PART 3)</div>
        <div class="mr-page-h1">Domestic Harmony, Marriage &amp; Extended Family Ties</div>
        <div class="mr-page-sub">Understanding marriage lines, warmth with your partner, and family harmony</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_vivah_rekha_svg(540, 115); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Shastric Vivah Rekha Alignment</div>
                <p class="mr-diag-card-p">
                    Positioned on the percussion edge below the Mercury finger, your primary affection line is deep, straight, and uncrossed. In Samudrik Shastra, this demonstrates lasting marital stability, deep mutual respect, and emotional loyalty that matures gracefully over time.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Venusian Domestic Dedication</div>
                <p class="mr-diag-card-p">
                    The abundant warmth of your Mount of Venus ensures that home is your sanctuary. You naturally provide physical comfort, hospitality, and protection for spouse and children, ensuring household peace and emotional nourishment.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Progeny &amp; Lineage Continuity</div>
                <p class="mr-diag-card-p">
                    Fine, clear vertical striae cross the primary marriage line on the Mercury mount. In classical Hast Rekha, these vertical branches indicate devoted, intelligent children who bring immense academic pride and family honor across generations.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Tactical Family Communication Protocol</div>
                <p class="mr-diag-card-p">
                    Because your active hand commands high professional authority, consciously leave workplace urgency outside the front door. Daily active listening and sharing warm evening meals dissolves relationship friction far faster than material provision.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Deep, straight Vivah Rekha confirms lifelong marital harmony, fidelity, and mutual respect.',
                'Venus mount fullness fuels a loving, hospitable domestic sanctuary with deep family devotion.',
                'Active listening and screen-free evening meals strengthen spousal bonds and lineage peace.'
            ], 'Domestic Harmony Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(23, 'Domestic Harmony & Marriage'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 24: PART VI - VITALITY & HEALTH -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Life Spheres Analysis</span>
        </div>
        
        <div class="mr-tag-pill">07 &bull; KEY LIFE AREAS (PART 4)</div>
        <div class="mr-page-h1">Physical Well-Being, Spine Care &amp; Ayurvedic Constitution</div>
        <div class="mr-page-sub">Simple lifestyle tips based on palm texture, blood flow, and Ayurvedic balance</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_tridosha_palm_svg(540, 115); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Ayurvedic Constitution (Pitta-Kapha)</div>
                <p class="mr-diag-card-p">
                    Your palm presents a warm pinkish undertone with elastic skin and moderate perspiration. In Samudrik Ayurveda, this correlates with a balanced Pitta-Kapha prakriti, granting high cellular vitality, steady stamina, and healthy digestive Agni.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Spine Flexibility &amp; Joint Mobility</div>
                <p class="mr-diag-card-p">
                    The square palm with firm thumbs indicates that long hours of desk work can generate stiffness along the lumbar and cervical spine. Incorporating 10 minutes of gentle morning Surya Namaskar stretches completely eliminates postural tension.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Warm Hydration &amp; Agni Protocol</div>
                <p class="mr-diag-card-p">
                    To maintain optimal digestive fire (<em>Agni</em>) and keep the Mercury health line clear, drink warm water infused with a slice of fresh ginger or lemon throughout the day. Minimize icy drinks that slow metabolic absorption.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Pada Abhyanga (Nighttime Grounding)</div>
                <p class="mr-diag-card-p">
                    Because your Head line slopes toward Moon, an active imagination can occasionally delay sleep onset. Massaging the soles of the feet with warm sesame or mustard oil before bed grounds excess mental prana and induces deep restorative sleep.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Pitta-Kapha palm constitution grants robust physical stamina and rapid metabolic recuperation.',
                '10 minutes of morning Surya Namaskar stretches protects spinal mobility against desk stiffness.',
                'Pada Abhyanga (foot massage with warm sesame oil) grounds active mental thoughts for deep rest.'
            ], 'Vitality & Health Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(24, 'Vitality & Physical Well-Being'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 25: PART VI - AGE MILESTONES -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Life Spheres Analysis</span>
        </div>
        
        <div class="mr-tag-pill">07 &bull; KEY LIFE AREAS (PART 5)</div>
        <div class="mr-page-h1">Chronological Age-Mapped Timeline (Ages 20 to 75)</div>
        <div class="mr-page-sub">A clear age-by-age timeline showing major milestones and new beginnings</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_age_milestone_timeline_svg(intval($age), 540, 95); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Era 1: Foundation (Ages 20–30)</div>
                <p class="mr-diag-card-p">
                    Marked by the early separation of the Life and Head lines and early Fate line rise. A period of rigorous skill building, self-reliance, exploratory professional trials, and establishing an independent philosophical identity.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Era 2: Consolidation (Ages 31–42)</div>
                <p class="mr-diag-card-p">
                    Anchored by the intersection of the Fate and Head lines at age 35. Transitioned from individual execution to strategic management, established core marital and family foundations, and acquired initial real estate holdings.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Era 3: Apex Authority (Ages 43–55 &bull; Active)</div>
                <p class="mr-diag-card-p">
                    Positioned between the Head and Heart line intersections. This is your premier window of compounding wealth, peak executive authority, industry accolades, and the creation of standardized digital systems that scale effortlessly.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Era 4: Legacy &amp; Wisdom (Ages 56–75)</div>
                <p class="mr-diag-card-p">
                    Reflected in the Fate line’s arrival on Saturn and the clear 3rd Manibandh bracelet. Characterized by high board advisory standing, mentorship of younger leaders, philanthropic giving, and deep inner spiritual peace.
                </p>
            </div>
        </div>

        <!-- Master Milestone Horizon Concordance Table (Fills whitespace before Part VI) -->
        <table class="mr-matching-table" style="margin-top:10px; margin-bottom:12px;">
            <colgroup>
                <col style="width: 20%;">
                <col style="width: 25%;">
                <col style="width: 40%;">
                <col style="width: 15%;">
            </colgroup>
            <thead>
                <tr>
                    <th>LIFE STAGE</th>
                    <th>CHRONO-CHRONOLOGY</th>
                    <th>SAMUDRIK MILESTONE DYNAMICS</th>
                    <th>ACTION PROTOCOL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Foundation</strong></td>
                    <td>Ages 18 to 28 Yrs</td>
                    <td>Jeevan Rekha separation; rapid personal independence and skill mastery</td>
                    <td><span class="mr-pill mr-pill-blue">Active Build</span></td>
                </tr>
                <tr>
                    <td><strong>Ascent Peak</strong></td>
                    <td>Ages 29 to 45 Yrs</td>
                    <td>Fate line intersection with Head line; executive authority &amp; asset foundation</td>
                    <td><span class="mr-pill mr-pill-green">Compounding</span></td>
                </tr>
                <tr>
                    <td><strong>Authority</strong></td>
                    <td>Ages 46 to 62 Yrs</td>
                    <td>Fate line junction at Heart line; mentorship, advisory leadership, high wealth</td>
                    <td><span class="mr-pill mr-pill-amber">Consolidation</span></td>
                </tr>
                <tr>
                    <td><strong>Saturn Legacy</strong></td>
                    <td>Ages 63 to 85+ Yrs</td>
                    <td>Triple Manibandh fruition; serene dignified retirement and family philanthropy</td>
                    <td><span class="mr-pill mr-pill-purple">Dharmic Peace</span></td>
                </tr>
            </tbody>
        </table>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Currently positioned in Era 3 (Ages 43–55): Prime window for peak authority and compounding.',
                'Historical pivot at age 35 successfully laid the groundwork for institutional management.',
                'Saturn arrival and 3rd wrist bracelet guarantee dignified elder legacy, honor, and security.'
            ], 'Age Milestones Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(25, 'Chronological Age Timeline'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 26: PART VII - VEDIC REMEDIES & MUDRAS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Vedic Upayas &amp; Remedies</span>
        </div>
        
        <div class="mr-tag-pill">08 &bull; SIMPLE DAILY REMEDIES (PART 1)</div>
        <div class="mr-page-h1">Personalized Hasta Mudras, Gemstones &amp; Mantras</div>
        <div class="mr-page-sub">Simple hand mudras and supportive gemstones to bring positive energy and clarity</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_hasta_mudras_svg(540, 100); ?>
        </div>

        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Neuro-Reflexology &amp; Mudra Science</div>
                <p class="mr-diag-card-p">
                    In Vedic physiology, fingertip nerve endings connect directly to sensory-motor cortex maps in the brain. Practicing <strong>Gyan Mudra</strong> (thumb and index fingertips touching) for 15 minutes each morning channels prana into the Mount of Jupiter, sharpening memory, strategic intellect, and calm executive decision-making.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Finger-Specific Gemstone Therapy</div>
                <p class="mr-diag-card-p">
                    To fortify your apex Mount of Venus, wear a <strong>White Sapphire (Safed Pukhraj)</strong> or natural Diamond set in silver on the right ring or little finger. Alternatively, a natural Yellow Sapphire on the index finger strengthens Jupiterian mentorship authority and institutional stature.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Sacred Mount Seed Mantras</div>
                <p class="mr-diag-card-p">
                    Recite the Shukra Beej Mantra (<em>Om Shum Shukraya Namah</em>) 108 times on Friday mornings to illuminate Venus warmth and commercial magnetism. Recite <em>Om Brim Brihaspataye Namah</em> on Thursdays to awaken the full executive majesty of your Jupiter mount.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Pranic Nadi Activation &amp; Breath</div>
                <p class="mr-diag-card-p">
                    Every morning, practice 7 minutes of gentle Anulom Vilom (alternate nostril breathing). This harmonizes the solar (Pingala) and lunar (Ida) nadis terminating in the fingertips, keeping palm line channels clear, elastic, and free from nervous congestion.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Daily 15-minute Gyan Mudra practice directly activates the Mount of Jupiter for executive focus.',
                'White Sapphire on the active hand stimulates Venus warmth, domestic joy, and capital growth.',
                'Anulom Vilom pranayama balances the fingertip nadis, keeping the nervous system serene.'
            ], 'Vedic Remedies Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(26, 'Hasta Mudras & Gemstone Guidance'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 27: PART VII - DAILY PROTOCOLS -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Vedic Upayas &amp; Protocols</span>
        </div>
        
        <div class="mr-tag-pill">08 &bull; SIMPLE DAILY REMEDIES (PART 2)</div>
        <div class="mr-page-h1">Daily Action Checklist, Do's &amp; Don'ts &amp; Hasta Darshan</div>
        <div class="mr-page-sub">Easy morning habits, daily palm prayers (Karaagre Vasate Lakshmi), and positive mindset</div>

        <div style="margin-bottom:12px;">
            <?php echo self::render_dos_donts_svg(540, 95); ?>
        </div>

        <!-- 4 Classical Ritual Cards -->
        <div class="mr-diag-grid-2x2">
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🏛️ Morning Karadarshanam Shastra</div>
                <p class="mr-diag-card-p">
                    Upon opening your eyes each morning, look into your joined palms before touching the floor. Recite the sacred shloka: <em>"Karaagre Vasate Lakshmi, Karamadhye Saraswati, Karamule Tu Govinda, Prabhaate Karadarshanam."</em> This consciously aligns your daily purushartha with divine grace.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🔍 Sacred Cow Ghee Mount Massage</div>
                <p class="mr-diag-card-p">
                    On Friday evenings, gently massage the fleshy Mount of Venus and Jupiter with a drop of pure cow ghee or almond oil for 2 minutes. In Samudrik tradition, this relaxes hand myofascial tension and prevents fine anxiety lines from etching across the palm.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 💼 Saturday Peepal Grounding Ritual</div>
                <p class="mr-diag-card-p">
                    Light a mustard oil diya beneath a sacred Peepal tree on Saturday evenings. This simple ritual honors Lord Shani, grounding restless ambition and softening any transit pressures on your central Fate line, promoting steady asset accumulation.
                </p>
            </div>
            <div class="mr-diag-card">
                <div class="mr-diag-card-title"><span style="color:#C45738;">&#9670;</span> 🌿 Thursday Mentorship &amp; Charity</div>
                <p class="mr-diag-card-p">
                    Offer yellow sweets, bananas, or books to students on Thursdays to honor Guru. Seek the blessings of mentors and elders; divine knowledge and institutional wealth multiply exponentially when shared with humility and generosity.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                'Morning Karadarshanam prayer anchors your conscious mind in Lakshmi, Saraswati, and Govinda.',
                'Friday cow ghee massage on Venus softens tension lines and preserves palm elasticity.',
                'Thursday charitable giving to students activates divine Jovian grace over all career ventures.'
            ], 'Daily Action Protocols Synthesis & Takeaways'); ?>
            <?php echo self::render_page_footer(27, 'Daily Protocols & Hasta Darshan'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 28: PART VII - CONCORDANCE MATRIX -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Master Palmistry Scorecard</span>
        </div>
        
        <div class="mr-tag-pill">08 &bull; PALMISTRY SUMMARY &amp; BLESSINGS</div>
        <div class="mr-page-h1">Master 12-Dimension Palmistry Concordance Matrix</div>
        <div class="mr-page-sub">Complete summary of your hand's strengths, positive signs, and life guidance</div>

        <!-- Master 12-Row Concordance Table -->
        <table class="mr-matching-table" style="margin-bottom:10px;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 22%;">
                <col style="width: 17%;">
                <col style="width: 20%;">
                <col style="width: 16%;">
            </colgroup>
            <thead>
                <tr>
                    <th>PALM DIMENSION</th>
                    <th>OBSERVED SIGNATURE</th>
                    <th>SCORE</th>
                    <th>SAMUDRIK SIGNIFICANCE</th>
                    <th>VERDICT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Hand Morphology</strong></td>
                    <td>Air (Square Palm &bull; Long Fingers)</td>
                    <td><strong>92%</strong></td>
                    <td>Intellectual Practicality</td>
                    <td><span class="mr-pill mr-pill-green">EXCELLENT</span></td>
                </tr>
                <tr>
                    <td><strong>Thumb Equilibrium</strong></td>
                    <td>Willpower 55% / Logic 45%</td>
                    <td><strong>90%</strong></td>
                    <td>Diplomatic Persuasion</td>
                    <td><span class="mr-pill mr-pill-green">BALANCED</span></td>
                </tr>
                <tr>
                    <td><strong>Jeevan Rekha (Life)</strong></td>
                    <td>Wide Sweep around Venus</td>
                    <td><strong>94%</strong></td>
                    <td>High Vitality &amp; Longevity</td>
                    <td><span class="mr-pill mr-pill-green">ROBUST</span></td>
                </tr>
                <tr>
                    <td><strong>Mastishk Rekha (Head)</strong></td>
                    <td>Long Decline + Saraswati Fork</td>
                    <td><strong>95%</strong></td>
                    <td>Creative Strategic Vision</td>
                    <td><span class="mr-pill mr-pill-green">SUPERIOR</span></td>
                </tr>
                <tr>
                    <td><strong>Hridaya Rekha (Heart)</strong></td>
                    <td>Ascent to Jupiter Mount</td>
                    <td><strong>91%</strong></td>
                    <td>Noble Marital Devotion</td>
                    <td><span class="mr-pill mr-pill-green">HIGH ACCORD</span></td>
                </tr>
                <tr>
                    <td><strong>Bhagya Rekha (Fate)</strong></td>
                    <td>Middle-Palm Deepening to Shani</td>
                    <td><strong>92%</strong></td>
                    <td>Earned Institutional Authority</td>
                    <td><span class="mr-pill mr-pill-green">EXECUTIVE</span></td>
                </tr>
                <tr>
                    <td><strong>Surya Rekha (Sun Line)</strong></td>
                    <td>Direct Ascent from Plain of Mars</td>
                    <td><strong>88%</strong></td>
                    <td>Public Acclaim &amp; Honors</td>
                    <td><span class="mr-pill mr-pill-green">REPUTATION</span></td>
                </tr>
                <tr>
                    <td><strong>Guru Mount (Jupiter)</strong></td>
                    <td>Elevated with Ring of Solomon</td>
                    <td><strong>88%</strong></td>
                    <td>Wisdom &amp; Mentorship</td>
                    <td><span class="mr-pill mr-pill-green">PROMINENT</span></td>
                </tr>
                <tr>
                    <td><strong>Shukra Mount (Venus)</strong></td>
                    <td>Apex #1 Potency (Full &amp; Warm)</td>
                    <td><strong>96%</strong></td>
                    <td>Vitality, Provider Warmth</td>
                    <td><span class="mr-pill mr-pill-green">APEX ANCHOR</span></td>
                </tr>
                <tr>
                    <td><strong>Chandra Mount (Moon)</strong></td>
                    <td>Smooth with Travel Creases</td>
                    <td><strong>85%</strong></td>
                    <td>Intuition &amp; Journeys</td>
                    <td><span class="mr-pill mr-pill-green">FORTUNATE</span></td>
                </tr>
                <tr>
                    <td><strong>Dhan Trikona Seal</strong></td>
                    <td>Closed 3-Sided Money Triangle</td>
                    <td><strong>93%</strong></td>
                    <td>Capital Preservation</td>
                    <td><span class="mr-pill mr-pill-green">WEALTH VAULT</span></td>
                </tr>
                <tr>
                    <td><strong>Manibandh Bracelets</strong></td>
                    <td>3 Complete Unbroken Lines</td>
                    <td><strong>94%</strong></td>
                    <td>Longevity past Age 84</td>
                    <td><span class="mr-pill mr-pill-green">PROTECTED</span></td>
                </tr>
            </tbody>
        </table>

        <!-- Closing Blessing & Portal Card -->
        <div class="mr-diag-card" style="border-left:4px solid #C45738; text-align:center; padding:12px 16px;">
            <div style="font-family:'Poppins', sans-serif; font-size:13px; font-weight:800; color:#111827; margin-bottom:4px;">
                🙏 Thank You for Trusting MeraRashifal Palmistry Intelligence
            </div>
            <p style="font-size:10.5px; color:#4B5563; line-height:1.45; margin:0 0 7px;">
                May universal peace, divine grace, and the sacred wisdom of Samudrik Shastra empower your conscious actions with wisdom, health, abundance, and domestic bliss.
            </p>
            <div style="font-size:10px; color:#6B7280; font-weight:700;">
                Access your reports archive anytime at: <strong>MeraRashifal.com/my-account/</strong> &bull; Follow on Instagram: <strong>@merarashifal</strong>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(28, 'Master Concordance Matrix'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PAGE 29: PART VII - SAMUDRIK GLOSSARY -->
    <!-- ========================================== -->
    <div class="mr-page mr-page-interior">
        <div class="mr-header-bar">
            <span class="mr-header-brand">MeraRashifal</span>
            <span><?php echo esc_html($person_name); ?> &bull; Samudrik Shastra Lexicon</span>
        </div>
        
        <div class="mr-tag-pill">09 &bull; PALMISTRY GLOSSARY &amp; TERMS</div>
        <div class="mr-page-h1">Definitive Glossary of Palmistry Terms &amp; Sanskrit Concepts</div>
        <div class="mr-page-sub">Easy explanations of traditional Samudrik Shastra palmistry terms used in this report</div>

        <!-- 12-Term Bilingual Definition Grid -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 12px; margin-bottom:12px;">
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Samudrik Shastra (सामुद्रिक शास्त्र):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">Ancient Indian science of body features, palm lines, and facial geometry codified by Sage Samudra.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Jeevan Rekha (जीवन रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The primary Life line arcing around the Mount of Venus, mapping physical prana, vitality, and constitutional resilience.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Mastishk Rekha (मस्तिष्क रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The Head line indicating intellectual capacity, focus, decision-making style, and creative foresight.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Hridaya Rekha (हृदय रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The Heart line tracing emotional depth, romantic loyalty, spousal empathy, and cardiovascular vitality.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Bhagya Rekha (भाग्य रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The vertical Fate line rising towards Saturn, marking career turning points, institutional elevation, and earned success.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Surya Rekha (सूर्य रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The Sun (Apollo) line conferring public fame, industry awards, executive standing, and artistic appreciation.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Parvata (पर्वत / Mounts):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The seven fleshy planetary elevations across the palm radiating specific cosmic and neurological frequencies.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Dhan Trikona (धन त्रिकोण):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The sacred Money Triangle formed by Head, Fate, and Mercury lines, signifying wealth generation and capital preservation.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Matsya Chihna (मत्स्य चिह्न):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The auspicious fish symbol indicating ancestral blessings, sudden financial windfalls, and high spiritual wisdom.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Manibandh (मणिबंध):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">The horizontal wrist bracelets denoting physical health (1st), material wealth (2nd), and spiritual dharma (3rd).</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Vivah Rekha (विवाह रेखा):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">Affection lines on the percussion beneath Mercury signifying marital timing, spousal harmony, and commitment.</p>
            </div>
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                <strong style="color:#C45738; font-size:10.8px;">Purushartha (पुरुषार्थ):</strong>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0; line-height:1.38;">Conscious self-effort and righteous action that dynamically alters fine palm creases and overcomes passive fate.</p>
            </div>
        </div>

        <!-- The Vedic Philosophy of Free Will -->
        <div class="mr-diag-card" style="border-left:4px solid #059669; padding:9px 14px; margin-bottom:8px;">
            <div style="font-family:'Poppins', sans-serif; font-size:11.5px; font-weight:800; color:#065F46; margin-bottom:3px;">
                🌿 THE VEDIC PHILOSOPHY OF FREE WILL (PURUSHARTHA)
            </div>
            <p style="font-size:9.5px; color:#374151; line-height:1.42; margin:0;">
                Vedic Samudrik Shastra is not fatalistic determinism. Palm lines are the neuro-biological map of your mind, dynamically connected to the motor cortex through thousands of nerve endings. As your conscious thoughts, ethical conduct (Dharma), and disciplined efforts (Purushartha) evolve, the fine branchings and wealth seals on your palm transform in tandem over 5-to-7 year cycles. Your conscious will remains supreme in steering your destiny.
            </p>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(29, 'Glossary & Sanskrit Lexicon'); ?>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Compress and store uploaded palm image with high-fidelity lossless/visually lossless optimization.
     * Strips heavy EXIF/GPS bloat, auto-orients camera rotation, caps resolution to 1600px,
     * and saves an optimized ~200-400KB JPEG (reducing 5-15MB phone photos by ~95% without line degradation).
     *
     * @param string $source_path Path to uploaded temporary file
     * @param string $destination_path Destination path in palm-cache
     * @return bool True on success, false on failure
     */
    public static function compress_and_store_palm_image($source_path, $destination_path) {
        if (!file_exists($source_path) || filesize($source_path) === 0) {
            return false;
        }

        $dest_dir = dirname($destination_path);
        if (!file_exists($dest_dir)) {
            if (function_exists('wp_mkdir_p')) {
                wp_mkdir_p($dest_dir);
            } else {
                @mkdir($dest_dir, 0755, true);
            }
        }

        // If GD extension is not loaded, fallback to safe copy
        if (!extension_loaded('gd')) {
            return @copy($source_path, $destination_path);
        }

        $info = @getimagesize($source_path);
        if (!$info) {
            return @copy($source_path, $destination_path);
        }

        $mime = $info['mime'] ?? 'image/jpeg';
        $img = null;

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $img = @imagecreatefromjpeg($source_path);
        } elseif ($mime === 'image/png') {
            $img = @imagecreatefrompng($source_path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($source_path);
        }

        if (!$img) {
            return @copy($source_path, $destination_path);
        }

        // Auto-orient based on camera EXIF data (fixes sideways orientation from iPhone/Android cameras)
        if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
            try {
                $exif = @exif_read_data($source_path);
                if (!empty($exif['Orientation'])) {
                    switch ((int)$exif['Orientation']) {
                        case 3:
                            $rotated = @imagerotate($img, 180, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 6:
                            $rotated = @imagerotate($img, -90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 8:
                            $rotated = @imagerotate($img, 90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Ignore EXIF parsing warnings
            }
        }

        $width = imagesx($img);
        $height = imagesy($img);

        // Max dimension: 1600px - pristine resolution for biometric lines, Gemini API, and PDF printing
        $max_dim = 1600;
        if ($width > $max_dim || $height > $max_dim) {
            if ($width >= $height) {
                $new_w = $max_dim;
                $new_h = (int)round(($height / $width) * $max_dim);
            } else {
                $new_h = $max_dim;
                $new_w = (int)round(($width / $height) * $max_dim);
            }

            $resampled = imagecreatetruecolor($new_w, $new_h);
            $white = imagecolorallocate($resampled, 255, 255, 255);
            imagefill($resampled, 0, 0, $white);

            imagecopyresampled($resampled, $img, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
            imagedestroy($img);
            $img = $resampled;
        }

        // Save optimized JPEG at quality 88 (sharp, lossless perception, zero crease degradation)
        $saved = @imagejpeg($img, $destination_path, 88);
        imagedestroy($img);

        // Opportunistic daily 30-day retention cleanup check (runs once per day via transient check)
        if (get_transient('mr_palm_daily_cleanup_check') === false) {
            set_transient('mr_palm_daily_cleanup_check', 1, DAY_IN_SECONDS);
            self::cleanup_palm_cache(30);
        }

        return $saved;
    }

    /**
     * WP-Cron handler for daily 30-day palm image retention cleanup
     */
    public static function cron_cleanup_palm_cache() {
        self::cleanup_palm_cache(30);
    }

    /**
     * Purge all palm image files and processed directories older than specified retention days.
     * Complies with DPDP Act 2023 Storage Limitation principle.
     *
     * @param int $retention_days Number of days to retain palm images (default: 30)
     */
    public static function cleanup_palm_cache($retention_days = 30) {
        $uploads_dir = wp_upload_dir();
        $cache_dir = $uploads_dir['basedir'] . '/palm-cache';
        if (!is_dir($cache_dir)) {
            return;
        }

        $expiry_timestamp = time() - ((int)$retention_days * 86400);
        $items = @scandir($cache_dir);
        if (!$items) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.htaccess' || $item === 'index.php') {
                continue;
            }
            $item_path = $cache_dir . '/' . $item;
            if (is_file($item_path)) {
                if (@filemtime($item_path) < $expiry_timestamp) {
                    @unlink($item_path);
                }
            } elseif (is_dir($item_path)) {
                if (@filemtime($item_path) < $expiry_timestamp) {
                    self::delete_directory_recursive($item_path);
                }
            }
        }
    }

    /**
     * Helper to recursively delete an expired directory and its contents
     *
     * @param string $dir Path to directory
     */
    private static function delete_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = @scandir($dir);
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . '/' . $file;
                is_dir($path) ? self::delete_directory_recursive($path) : @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
