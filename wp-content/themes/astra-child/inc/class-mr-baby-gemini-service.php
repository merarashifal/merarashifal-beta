<?php
/**
 * MeraRashifal Baby Name Engine: Gemini AI Prompt Service & Auto-Seeding Pipeline
 * Class: MR_Baby_Gemini_Service
 *
 * Handles AI enrichment via Google Gemini API in structured JSON mode,
 * permanent write-through caching in af_baby_name_profiles, and self-expanding
 * database seeding into af_baby_names for zero-404 internal link integrity.
 *
 * @package MeraRashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Baby_Gemini_Service
{
    private static $instance = null;

    /**
     * Candidate models in priority order for maximum uptime and latency optimization
     */
    private $candidate_models = [
        'gemini-3.1-flash-lite',
        'gemini-flash-lite-latest',
        'gemini-3.5-flash-lite',
        'gemini-3.5-flash',
        'gemini-3.8-flash'
    ];

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Auto-verify profiles table exists
        if (class_exists('MR_Baby_Astro_Engine')) {
            MR_Baby_Astro_Engine::instance()->ensure_profiles_table();
        }
    }

    /**
     * Retrieves the Gemini API Key from wp-config or database options
     */
    public function get_api_key()
    {
        if (defined('GOOGLE_API_KEY') && !empty(GOOGLE_API_KEY)) {
            return trim(GOOGLE_API_KEY);
        }

        $db_key = get_option('mr_gemini_api_key', '');
        if (!empty($db_key)) {
            return trim($db_key);
        }

        $niyati_settings = get_option('niyati_chat_settings', []);
        if (!empty($niyati_settings['gemini_api_key'])) {
            return trim($niyati_settings['gemini_api_key']);
        }

        return '';
    }

    /**
     * Retrieves or generates the full 800-1,200 word Master Baby Name Profile.
     * Implements write-through caching: Cache hit (<50ms) vs First-generation AI enrichment.
     *
     * @param string $name Name string (e.g. "Tushar")
     * @param string $gender "Boy", "Girl", or "Unisex"
     * @param string $religion "Hindu", "Muslim", "Christian", "Sikh", "General"
     * @param bool $force_refresh Skip cache if true
     * @return array Complete master JSON profile
     */
    public function get_or_generate_profile($name, $gender = 'Boy', $religion = 'Hindu', $force_refresh = false)
    {
        global $wpdb;

        $clean_name = ucfirst(strtolower(trim($name)));
        $name_slug  = sanitize_title($clean_name);
        $gender     = ucfirst(strtolower(trim($gender)));
        $religion   = ucfirst(strtolower(trim($religion)));

        // 1. Resolve or establish record in af_baby_names
        $baby_name_row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM af_baby_names WHERE LOWER(name) = LOWER(%s) LIMIT 1",
            $clean_name
        ));

        if (!$baby_name_row) {
            $wpdb->insert('af_baby_names', [
                'name'                 => $clean_name,
                'gender'               => $gender,
                'starting_letter'      => strtoupper(substr($clean_name, 0, 1)),
                'meaning'              => 'Traditional ' . $religion . ' name',
                'religion'             => $religion,
                'score'                => 50,
                'numerology_processed' => 1
            ]);
            $baby_name_id = $wpdb->insert_id;
        } else {
            $baby_name_id = (int)$baby_name_row->id;
            if (empty($gender) && !empty($baby_name_row->gender)) {
                $gender = ucfirst(strtolower($baby_name_row->gender));
            }
            if (empty($religion) && !empty($baby_name_row->religion)) {
                $religion = ucfirst(strtolower($baby_name_row->religion));
            }
        }

        // 2. Check Write-Through Cache (af_baby_name_profiles)
        if (!$force_refresh) {
            $cached_profile = $wpdb->get_row($wpdb->prepare(
                "SELECT profile_json FROM af_baby_name_profiles WHERE baby_name_id = %d OR name_slug = %s LIMIT 1",
                $baby_name_id,
                $name_slug
            ));

            if ($cached_profile && !empty($cached_profile->profile_json)) {
                $decoded = json_decode($cached_profile->profile_json, true);
                if (is_array($decoded) && !empty($decoded['name'])) {
                    return $decoded;
                }
            }
        }

        // 3. Step 1 (Deterministic Core): Compute Vedic Astrology and Numerology
        $astro_engine = MR_Baby_Astro_Engine::instance();
        $astro_core   = $astro_engine->compute_astro_profile($clean_name, $gender, $religion);

        // 4. Step 2 (AI Enrichment): Call Gemini API in JSON Mode
        $ai_enrichment = $this->call_gemini_enrichment($clean_name, $gender, $religion, $astro_core);

        // 5. Merge Deterministic Vedic Core with AI Enrichment
        $master_profile = $this->build_master_payload($clean_name, $gender, $religion, $astro_core, $ai_enrichment);

        // 6. Step 4 (Self-Expanding DB Pipeline): Auto-seed variants and siblings
        $this->auto_seed_related_names($master_profile, $clean_name, $religion);

        // 7. Calculate Word Count across all narrative sections
        $word_count = $this->calculate_profile_word_count($master_profile);

        // 8. Step 3 (Write-Through Cache): Persist Master JSON in af_baby_name_profiles
        $master_json = wp_json_encode($master_profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $existing_profile_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM af_baby_name_profiles WHERE baby_name_id = %d LIMIT 1",
            $baby_name_id
        ));

        $data_to_store = [
            'baby_name_id'      => $baby_name_id,
            'name_slug'         => $name_slug,
            'gender'            => $gender,
            'religion'          => $religion,
            'rashi'             => $astro_core['vedic_astrology']['rashi_moon_sign'],
            'nakshatra'         => $astro_core['vedic_astrology']['nakshatra'],
            'numerology_number' => (int)$astro_core['numerology']['chaldean_number'],
            'profile_json'      => $master_json,
            'word_count'        => $word_count
        ];

        if ($existing_profile_id) {
            $wpdb->update('af_baby_name_profiles', $data_to_store, ['id' => $existing_profile_id]);
        } else {
            $wpdb->insert('af_baby_name_profiles', $data_to_store);
        }

        return $master_profile;
    }

    /**
     * Dispatches structured prompt to Google Gemini API with fallback candidate chain
     */
    private function call_gemini_enrichment($name, $gender, $religion, array $astro_core)
    {
        $api_key = $this->get_api_key();
        if (empty($api_key)) {
            return $this->get_fallback_ai_data($name, $gender, $religion, $astro_core);
        }

        $system_instruction = "You are an authority on Vedic onomastics, Sanskrit etymology, and modern Indian diaspora naming conventions. " .
            "You generate deep, culturally authentic, and academically rigorous baby name profiles. " .
            "Output strictly valid JSON with no markdown wrapping, no backticks, and exactly according to the required schema.";

        $rashi      = $astro_core['vedic_astrology']['rashi_moon_sign'];
        $nakshatra  = $astro_core['vedic_astrology']['nakshatra'];
        $planet     = $astro_core['vedic_astrology']['ruling_planet'];
        $element    = $astro_core['vedic_astrology']['vedic_element'];
        $chaldean   = $astro_core['numerology']['chaldean_number'];
        $pythagoras = $astro_core['numerology']['pythagorean_number'];

        $prompt = "Generate an extensive, 1,000-word comprehensive profile for the baby name '{$name}' (Gender: {$gender}, Religion: {$religion}).\n\n" .
            "Vedic & Numerology Anchors (incorporate deterministically without contradicting):\n" .
            "- Moon Sign (Rashi): {$rashi}\n" .
            "- Nakshatra: {$nakshatra}\n" .
            "- Ruling Planet (Graha Swami): {$planet}\n" .
            "- Vedic Element: {$element}\n" .
            "- Chaldean Destiny Number: {$chaldean}\n" .
            "- Pythagorean Expression Number: {$pythagoras}\n\n" .
            "Requirements for Content Depth:\n" .
            "1. 'personality_and_character_insights': Write 3 detailed paragraphs analyzing behavioral psychology, emotional resilience, and interpersonal strengths.\n" .
            "2. 'career_and_success_predictions': Write 3 detailed paragraphs evaluating natural vocational aptitudes, leadership acumen, and financial habits.\n" .
            "3. 'faqs': Provide 5 comprehensive FAQ items with detailed, helpful answers (at least 60 words per answer).\n" .
            "4. 'sibling_recommendations': 4 brother and 4 sister names that phonetically harmonise with '{$name}'.\n\n" .
            "Output JSON Schema:\n" .
            "{\n" .
            '  "script": "Devanagari or native script (e.g. तुषार)",' . "\n" .
            '  "primary_origin": "Linguistic root (e.g. Sanskrit / Hindu)",' . "\n" .
            '  "gender_suitability": "' . ($gender === 'Boy' ? 'Male Only' : 'Female Only') . '",' . "\n" .
            '  "popularity_badge": "Trending Classic | Ancient Vedic | Modern Heritage",' . "\n" .
            '  "etymology": {' . "\n" .
            '    "literal_meaning": "Deep definition, mythological allusions, and poetic imagery",' . "\n" .
            '    "syllables": ["Syllable1", "Syllable2"],' . "\n" .
            '    "syllable_count": 2,' . "\n" .
            '    "ipa_pronunciation": "IPA phonetic notation",' . "\n" .
            '    "phonetic_respelling": "English phonetic respelling (e.g. Too-shahr)"' . "\n" .
            '  },' . "\n" .
            '  "personality_and_character_insights": "3 rich paragraphs...",' . "\n" .
            '  "career_and_success_predictions": "3 rich paragraphs...",' . "\n" .
            '  "global_metadata": {' . "\n" .
            '    "popularity_india": "Status in India across regions",' . "\n" .
            '    "popularity_us": "Status in North America and Global Diaspora",' . "\n" .
            '    "variant_spellings": ["Variant1", "Variant2", "Variant3"],' . "\n" .
            '    "nicknames": ["Nick1", "Nick2"],' . "\n" .
            '    "dual_market_appeal": "Ease of cross-cultural articulation and modern adaptability"' . "\n" .
            '  },' . "\n" .
            '  "sibling_recommendations": {' . "\n" .
            '    "brother_names": ["Name1", "Name2", "Name3", "Name4"],' . "\n" .
            '    "sister_names": ["Name1", "Name2", "Name3", "Name4"]' . "\n" .
            '  },' . "\n" .
            '  "faqs": [' . "\n" .
            '    {"question": "What is the meaning and origin of the name ' . $name . '?", "answer": "..."},' . "\n" .
            '    {"question": "Which Rashi and Nakshatra govern ' . $name . '?", "answer": "..."},' . "\n" .
            '    {"question": "What is the lucky numerology number for ' . $name . '?", "answer": "..."},' . "\n" .
            '    {"question": "What are the personality traits associated with ' . $name . '?", "answer": "..."},' . "\n" .
            '    {"question": "What are ideal brother and sister names for ' . $name . '?", "answer": "..."}' . "\n" .
            '  ]' . "\n" .
            "}";

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $system_instruction]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature'        => 0.4
            ]
        ];

        $payload_json = wp_json_encode($payload);

        foreach ($this->candidate_models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($api_key);

            $response = wp_remote_post($url, [
                'method'      => 'POST',
                'timeout'     => 25,
                'sslverify'   => false,
                'headers'     => ['Content-Type' => 'application/json'],
                'body'        => $payload_json
            ]);

            if (is_wp_error($response)) {
                error_log("[MR_Baby_Gemini_Service] Model {$model} error: " . $response->get_error_message());
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            if ($code === 200) {
                $resp_data = json_decode($body, true);
                $raw_text  = $resp_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $clean_raw = trim($raw_text);

                // Strip accidental markdown backticks if any
                $clean_raw = preg_replace('/^```(?:json)?\s*/i', '', $clean_raw);
                $clean_raw = preg_replace('/\s*```$/i', '', $clean_raw);

                $parsed = json_decode($clean_raw, true);
                if (is_array($parsed) && !empty($parsed['script'])) {
                    return $parsed;
                }
            } else {
                error_log("[MR_Baby_Gemini_Service] Model {$model} returned HTTP {$code}: " . substr($body, 0, 200));
            }
        }

        // Fallback if all API models fail
        return $this->get_fallback_ai_data($name, $gender, $religion, $astro_core);
    }

    /**
     * Merges deterministic Astro Core and AI data into complete specification schema
     */
    private function build_master_payload($name, $gender, $religion, array $astro_core, array $ai_data)
    {
        return [
            'name'                               => $name,
            'script'                             => $ai_data['script'] ?? $name,
            'primary_origin'                     => $ai_data['primary_origin'] ?? ($religion . ' / Sanskrit'),
            'gender_suitability'                 => $ai_data['gender_suitability'] ?? ($gender === 'Boy' ? 'Male Only' : 'Female Only'),
            'popularity_badge'                   => $ai_data['popularity_badge'] ?? 'Trending Classic',

            'etymology'                          => [
                'literal_meaning'    => $ai_data['etymology']['literal_meaning'] ?? "Sacred {$religion} name representing auspicious beginnings.",
                'syllables'          => $ai_data['etymology']['syllables'] ?? [$name],
                'syllable_count'     => (int)($ai_data['etymology']['syllable_count'] ?? 2),
                'ipa_pronunciation'  => $ai_data['etymology']['ipa_pronunciation'] ?? '',
                'phonetic_respelling'=> $ai_data['etymology']['phonetic_respelling'] ?? $name
            ],

            'vedic_astrology'                    => $astro_core['vedic_astrology'],
            'numerology'                         => $astro_core['numerology'],
            'auspicious_attributes'              => $astro_core['auspicious_attributes'],

            'personality_and_character_insights' => $ai_data['personality_and_character_insights'] ?? '',
            'career_and_success_predictions'     => $ai_data['career_and_success_predictions'] ?? '',

            'global_metadata'                    => [
                'popularity_india'   => $ai_data['global_metadata']['popularity_india'] ?? 'High across traditional households',
                'popularity_us'      => $ai_data['global_metadata']['popularity_us'] ?? 'Cherished heritage name in global diaspora',
                'variant_spellings'  => (array)($ai_data['global_metadata']['variant_spellings'] ?? []),
                'nicknames'          => (array)($ai_data['global_metadata']['nicknames'] ?? []),
                'dual_market_appeal' => $ai_data['global_metadata']['dual_market_appeal'] ?? 'Well-suited for both domestic and international contexts.'
            ],

            'sibling_recommendations'            => [
                'brother_names'      => (array)($ai_data['sibling_recommendations']['brother_names'] ?? []),
                'sister_names'       => (array)($ai_data['sibling_recommendations']['sister_names'] ?? [])
            ],

            'faqs'                               => (array)($ai_data['faqs'] ?? [])
        ];
    }

    /**
     * Auto-seeds missing variant spellings and sibling names into af_baby_names
     * Guarantees all internal links resolve with 100% integrity (zero 404s)
     */
    private function auto_seed_related_names(array $profile, $parent_name, $religion)
    {
        global $wpdb;

        $names_to_check = [];

        // 1. Variant spellings (same gender)
        $gender = $profile['gender_suitability'] === 'Female Only' ? 'Girl' : 'Boy';
        if (!empty($profile['global_metadata']['variant_spellings'])) {
            foreach ($profile['global_metadata']['variant_spellings'] as $var_name) {
                $names_to_check[] = [
                    'name'   => trim($var_name),
                    'gender' => $gender,
                    'type'   => 'variant'
                ];
            }
        }

        // 2. Brother names
        if (!empty($profile['sibling_recommendations']['brother_names'])) {
            foreach ($profile['sibling_recommendations']['brother_names'] as $bro_name) {
                $names_to_check[] = [
                    'name'   => trim($bro_name),
                    'gender' => 'Boy',
                    'type'   => 'brother'
                ];
            }
        }

        // 3. Sister names
        if (!empty($profile['sibling_recommendations']['sister_names'])) {
            foreach ($profile['sibling_recommendations']['sister_names'] as $sis_name) {
                $names_to_check[] = [
                    'name'   => trim($sis_name),
                    'gender' => 'Girl',
                    'type'   => 'sister'
                ];
            }
        }

        foreach ($names_to_check as $item) {
            $raw_name = ucfirst(strtolower($item['name']));
            if (empty($raw_name) || strlen($raw_name) < 2) {
                continue;
            }

            // Check if already in af_baby_names
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM af_baby_names WHERE LOWER(name) = LOWER(%s) LIMIT 1",
                $raw_name
            ));

            if (!$exists) {
                $meaning_desc = $item['type'] === 'variant'
                    ? "Traditional variant spelling of {$parent_name} in {$religion} heritage."
                    : "Traditional {$religion} baby {$item['gender']} name harmoniously paired with {$parent_name}.";

                $wpdb->insert('af_baby_names', [
                    'name'                 => $raw_name,
                    'gender'               => $item['gender'],
                    'starting_letter'      => strtoupper(substr($raw_name, 0, 1)),
                    'meaning'              => $meaning_desc,
                    'religion'             => $religion,
                    'score'                => 50,
                    'numerology_processed' => 0
                ]);
            }
        }
    }

    /**
     * Calculates total narrative word count across the entire generated profile
     */
    public function calculate_profile_word_count(array $profile)
    {
        $text_blocks = [];
        $text_blocks[] = $profile['name'] ?? '';
        $text_blocks[] = $profile['etymology']['literal_meaning'] ?? '';
        $text_blocks[] = $profile['vedic_astrology']['favorable_rashis_and_elements'] ?? '';
        $text_blocks[] = $profile['numerology']['life_path_resonance'] ?? '';
        $text_blocks[] = $profile['personality_and_character_insights'] ?? '';
        $text_blocks[] = $profile['career_and_success_predictions'] ?? '';
        $text_blocks[] = $profile['global_metadata']['dual_market_appeal'] ?? '';

        if (!empty($profile['faqs'])) {
            foreach ($profile['faqs'] as $faq) {
                $text_blocks[] = $faq['question'] ?? '';
                $text_blocks[] = $faq['answer'] ?? '';
            }
        }

        $full_text = strip_tags(implode(' ', $text_blocks));
        return str_word_count($full_text);
    }

    /**
     * Provides graceful fallback structure if all AI API endpoints are temporarily unavailable
     */
    private function get_fallback_ai_data($name, $gender, $religion, array $astro_core)
    {
        $rashi    = $astro_core['vedic_astrology']['rashi_moon_sign'];
        $planet   = $astro_core['vedic_astrology']['ruling_planet'];
        $chaldean = $astro_core['numerology']['chaldean_number'];

        return [
            'script' => $name,
            'primary_origin' => $religion . ' / Sanskrit',
            'gender_suitability' => $gender === 'Boy' ? 'Male Only' : 'Female Only',
            'popularity_badge' => 'Classic Heritage',
            'etymology' => [
                'literal_meaning' => "Auspicious {$religion} name signifying dignity, strength, and divine blessings.",
                'syllables' => [$name],
                'syllable_count' => 2,
                'ipa_pronunciation' => '/' . strtolower($name) . '/',
                'phonetic_respelling' => $name
            ],
            'personality_and_character_insights' => "Individuals bearing the name {$name} resonate deeply with the governing rays of {$planet} and the vibration of {$rashi}. They possess natural poise, balanced emotional intelligence, and enduring integrity.",
            'career_and_success_predictions' => "In vocational spheres, {$name} excels where visionary leadership, methodical dedication, and ethical values are rewarded, empowered by Chaldean destiny vibration {$chaldean}.",
            'global_metadata' => [
                'popularity_india' => 'Widely respected classic across India',
                'popularity_us' => 'Beloved cultural heritage name',
                'variant_spellings' => [],
                'nicknames' => [],
                'dual_market_appeal' => 'Effortlessly pronounced across both Eastern and Western communities.'
            ],
            'sibling_recommendations' => [
                'brother_names' => [],
                'sister_names' => []
            ],
            'faqs' => [
                [
                    'question' => "What is the Vedic significance of {$name}?",
                    'answer' => "The name {$name} is governed by {$rashi} and {$planet}, carrying positive vibrational resonance."
                ]
            ]
        ];
    }
}
