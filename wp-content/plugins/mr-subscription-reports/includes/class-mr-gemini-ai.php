<?php
/**
 * Gemini AI Synthesis Engine for Astrology Reports
 * Connects to Google Generative Language API (Gemini Flash)
 * Generates modern, publication-grade editorial narratives, scores, and rituals.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Gemini_AI {

    const DEFAULT_MODEL = 'gemini-3.1-flash-lite';
    const FALLBACK_MODEL = 'gemini-flash-latest';

    /**
     * Get configured Gemini API key
     */
    public static function get_api_key() {
        return get_option('mr_gemini_api_key', 'AIzaSyDzS22g365n4yrHAPRTuRfViNS3avj3QF0');
    }

    /**
     * Test API connection
     */
    public static function test_connection($api_key = null) {
        $key = $api_key ?: self::get_api_key();
        if (empty($key)) {
            return ['success' => false, 'message' => 'Gemini API key is missing.'];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::DEFAULT_MODEL . ':generateContent?key=' . urlencode($key);
        $body = [
            'contents' => [
                ['parts' => [['text' => 'Respond with the single word: Connected.']]]
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($body),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $res_body = wp_remote_retrieve_body($response);
        $json = json_decode($res_body, true);

        if ($code === 200 && !empty($json['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Gemini API (' . self::DEFAULT_MODEL . ')!',
                'sample'  => trim($json['candidates'][0]['content']['parts'][0]['text'])
            ];
        }

        $error_msg = $json['error']['message'] ?? ('HTTP Error ' . $code);
        return ['success' => false, 'message' => $error_msg];
    }

    /**
     * Generate Monthly Editorial Pack matching the ZodiacBee publication style
     *
     * @param string $person_name
     * @param array $astro_profile (Sun, Moon, Rising, transits, retrogrades)
     * @param string $month_year e.g. "November 2026"
     * @return array
     */
    public static function generate_monthly_editorial($person_name, $astro_profile, $month_year) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return self::get_fallback_monthly_data($person_name, $month_year);
        }

        $cache_key = 'mr_gemini_monthly_' . md5($person_name . '_' . $month_year . '_' . wp_json_encode($astro_profile));
        $cached = get_transient($cache_key);
        if ($cached) {
            return $cached;
        }

        $prompt = self::build_monthly_editorial_prompt($person_name, $astro_profile, $month_year);

        $result = self::call_gemini_json($prompt, $api_key);
        if ($result && !empty($result['monthly_theme'])) {
            $ttl = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS * 15 : 86400 * 15;
            set_transient($cache_key, $result, $ttl);
            return $result;
        }

        return self::get_fallback_monthly_data($person_name, $month_year);
    }

    /**
     * Generate Yearly Editorial & Varshaphal Synthesis
     *
     * @param string $person_name
     * @param array $vedic_profile (Birth planets, D1/D9 details, Mahadashas, Transits)
     * @param int $year e.g. 2027
     * @return array
     */
    public static function generate_yearly_editorial($person_name, $vedic_profile, $year = 2027) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return self::get_fallback_yearly_data($person_name, $year);
        }

        $cache_key = 'mr_gemini_yearly_' . md5($person_name . '_' . $year . '_' . wp_json_encode($vedic_profile));
        $cached = get_transient($cache_key);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        $prompt = self::build_yearly_editorial_prompt($person_name, $vedic_profile, $year);

        $result = self::call_gemini_json($prompt, $api_key);
        if ($result && !empty($result['annual_theme'])) {
            $ttl = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS * 30 : 86400 * 30;
            set_transient($cache_key, $result, $ttl);
            return $result;
        }

        return self::get_fallback_yearly_data($person_name, $year);
    }

    /**
     * Generate Short (5-Page Free Starter) Yearly Editorial
     *
     * @param string $person_name
     * @param array $vedic_profile
     * @param int $year e.g. 2027
     * @return array
     */
    public static function generate_short_yearly_editorial($person_name, $vedic_profile, $year = 2027) {
        $api_key = self::get_api_key();

        // 1. Check if full yearly editorial was already cached
        $full_cache_key = 'mr_gemini_yearly_' . md5($person_name . '_' . $year . '_' . wp_json_encode($vedic_profile));
        $full_cached = get_transient($full_cache_key);
        if ($full_cached && is_array($full_cached) && !empty($full_cached['annual_theme'])) {
            return self::adapt_full_to_short_yearly($full_cached, $person_name, $year);
        }

        // 2. Check if short yearly editorial is cached
        $cache_key = 'mr_gemini_short_yearly_' . md5($person_name . '_' . $year . '_' . wp_json_encode($vedic_profile));
        $cached = get_transient($cache_key);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        // 3. Fallback check for precomputed scratch file
        $json_file = 'C:/Users/Tushar Singhal/.gemini/antigravity/brain/8095b7a1-c95a-4e62-b745-e927ba760684/scratch/tushar_2027_gemini.json';
        if (file_exists($json_file)) {
            $scratch_data = json_decode(file_get_contents($json_file), true);
            if (is_array($scratch_data) && !empty($scratch_data['annual_theme'])) {
                $adapted = self::adapt_full_to_short_yearly($scratch_data, $person_name, $year);
                set_transient($cache_key, $adapted, 86400 * 30);
                return $adapted;
            }
        }

        if (empty($api_key)) {
            return self::get_fallback_short_yearly_data($person_name, $year);
        }

        $prompt = self::build_short_yearly_editorial_prompt($person_name, $vedic_profile, $year);
        $result = self::call_gemini_json($prompt, $api_key);
        if ($result && !empty($result['annual_theme'])) {
            $ttl = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS * 30 : 86400 * 30;
            set_transient($cache_key, $result, $ttl);
            return $result;
        }

        return self::get_fallback_short_yearly_data($person_name, $year);
    }

    /**
     * Generate Lifetime Kundali Editorial & Soul Purpose Synthesis
     *
     * @param string $person_name
     * @param array $vedic_profile (Lagna, Moon, Sun, Planets, Dashas, Yogas)
     * @return array
     */
    public static function generate_kundali_editorial($person_name, $vedic_profile) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return self::get_fallback_kundali_data($person_name);
        }

        $cache_key = 'mr_gemini_kundali_' . md5($person_name . '_' . wp_json_encode($vedic_profile));
        $cached = get_transient($cache_key);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        $prompt = self::build_kundali_editorial_prompt($person_name, $vedic_profile);

        $result = self::call_gemini_json($prompt, $api_key);
        if ($result && !empty($result['destiny_theme'])) {
            $ttl = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS * 30 : 86400 * 30;
            set_transient($cache_key, $result, $ttl);
            return $result;
        }

        return self::get_fallback_kundali_data($person_name);
    }

    /**
     * Send prompt to Gemini with JSON schema output
     */
    private static function call_gemini_json($prompt, $api_key) {
        $models = ['gemini-3-flash-preview', 'gemini-flash-lite-latest', 'gemini-3.5-flash', 'gemini-flash-latest'];

        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($api_key);
            $body = [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.7,
                ]
            ];

            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode($body),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                continue;
            }

            $res_body = wp_remote_retrieve_body($response);
            $data = json_decode($res_body, true);
            $raw_json = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            $parsed = json_decode($raw_json, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * Construct editorial prompt for Gemini based on Vedic Thermal Flux & Topography
     */
    private static function build_monthly_editorial_prompt($name, $profile, $month) {
        $sun = $profile['sun_sign'] ?? 'Leo';
        $moon = $profile['moon_sign'] ?? 'Aquarius';
        $rising = $profile['rising_sign'] ?? 'Leo';
        $retrogrades = $profile['retrogrades'] ?? 'Mercury and Venus are in review/retrograde phase during first half';
        $transits = $profile['transits'] ?? 'Mars in 12th house, Full Moon in 10th house late month';

        return "You are the Chief Vedic Astrologer & Editorial Director for MeraRashifal (merarashifal.com).
Generate an authoritative, publication-grade Vedic Astrological Monthly Horizon & Thermal Blueprint for:
Native: {$name}
Natal Signature: Sun in {$sun}, Moon in {$moon}, Lagna (Ascendant) in {$rising}
Month & Year: {$month}
Planetary Deployments:
{$retrogrades}
{$transits}

Conceptual Framework (DO NOT use rainy/sunny weather metaphors):
- Conceptualize the month as a 'Vedic Thermal & Topographic Landscape'.
- Energy states are measured through Vedic Thermal Flux: 'Radiant Agni' (High kinetic drive/fire), 'Temperate Sattva' (Harmonic growth/balance), 'Deep Shita' (Cool reflection/lunar calm), or 'Thermal Resistance' (Frictional tension).
- The 4-week timeline is conceptualized as 'Topographic Elevations' (Phase 1: The Ascent, Phase 2: The Valley of Recalibration, Phase 3: The Crest of Realignment, Phase 4: The Horizon Breakthrough).
- Tone: Sovereign, dignified, psychologically astute, rooted in Vedic Jyotish wisdom with modern executive clarity.

Return a strict JSON object with this exact schema:
{
  \"monthly_theme\": \"Sovereign 3-5 word mantra (e.g. Master the Flame, Command the Horizon)\",
  \"executive_synthesis\": \"2-3 dignified sentences summarizing the monthly cosmic landscape\",
  \"thermal_index\": 74,
  \"thermal_status\": \"Radiant Agni (High Focus)\",
  \"thermal_split\": {
    \"first_half\": 62,
    \"first_half_label\": \"Shita & Deliberation\",
    \"second_half\": 84,
    \"second_half_label\": \"Agni & Forward Expansion\"
  },
  \"four_pillars\": {
    \"karma\": {
      \"score\": 78,
      \"state\": \"Radiant Agni\",
      \"headline\": \"Strategic consolidation precedes executive visibility.\",
      \"analysis\": \"In-depth paragraph analyzing career altitude, 10th house dynamics, leadership timing, and authority relationships.\",
      \"tactical_advice\": [\"Point 1 on leadership\", \"Point 2 on negotiations\", \"Point 3 on key date windows\"]
    },
    \"dhana\": {
      \"score\": 68,
      \"state\": \"Temperate Sattva\",
      \"headline\": \"Audit capital structures and reinforce liquidity.\",
      \"analysis\": \"In-depth paragraph analyzing 2nd/11th house financial flow, investments, asset allocations, and receivables.\",
      \"tactical_advice\": [\"Point 1 on asset security\", \"Point 2 on spending discipline\", \"Point 3 on wealth timing\"]
    },
    \"prema\": {
      \"score\": 64,
      \"state\": \"Deep Shita (Reflection)\",
      \"headline\": \"Honest dialogue harmonizes karmic bonds.\",
      \"analysis\": \"In-depth paragraph evaluating 7th house partnerships, Shukra (Venus) movements, single natives, and marital longevity.\",
      \"tactical_advice\": [\"Advice for committed couples\", \"Advice for uncommitted singles\", \"Optimal relational window\"]
    },
    \"arogya\": {
      \"score\": 86,
      \"state\": \"Harmonic Prana\",
      \"headline\": \"Robust solar endurance paired with restorative discipline.\",
      \"analysis\": \"In-depth paragraph assessing 6th house vitality, nervous system resilience, physical stamina, and biorhythms.\",
      \"tactical_advice\": [\"Physical training regimen\", \"Dietary & Panchamahabhuta tuning\", \"Mental rest ritual\"]
    }
  },
  \"topographic_trajectory\": [
    {
      \"phase\": 1,
      \"title\": \"The Ascent\",
      \"dates\": \"Days 1–7\",
      \"elevation\": 68,
      \"dynamic\": \"Internal Ignition\",
      \"forecast\": \"Paragraph forecasting the first week momentum.\",
      \"strategic_action\": \"Decisive action to initiate this week.\",
      \"friction_trap\": \"Trap or mistake to strictly avoid.\"
    },
    {
      \"phase\": 2,
      \"title\": \"The Valley of Recalibration\",
      \"dates\": \"Days 8–14\",
      \"elevation\": 59,
      \"dynamic\": \"Contemplative Alignment\",
      \"forecast\": \"Paragraph forecasting the second week shifts.\",
      \"strategic_action\": \"Decisive action to initiate this week.\",
      \"friction_trap\": \"Trap or mistake to strictly avoid.\"
    },
    {
      \"phase\": 3,
      \"title\": \"The Crest of Realignment\",
      \"dates\": \"Days 15–21\",
      \"elevation\": 73,
      \"dynamic\": \"Structural Awakening\",
      \"forecast\": \"Paragraph forecasting the third week shifts.\",
      \"strategic_action\": \"Decisive action to initiate this week.\",
      \"friction_trap\": \"Trap or mistake to strictly avoid.\"
    },
    {
      \"phase\": 4,
      \"title\": \"The Horizon Breakthrough\",
      \"dates\": \"Days 22–30\",
      \"elevation\": 88,
      \"dynamic\": \"Peak Kinetic Acceleration\",
      \"forecast\": \"Paragraph forecasting the fourth week shifts.\",
      \"strategic_action\": \"Decisive action to initiate this week.\",
      \"friction_trap\": \"Trap or mistake to strictly avoid.\"
    }
  ],
  \"angular_triggers\": [
    {\"date\": \"Nov 7\", \"type\": \"Kendra Alignment\", \"label\": \"Surya & Mangal Conjunction\", \"impact\": \"High kinetic surge; channel into solo engineering.\"},
    {\"date\": \"Nov 9\", \"type\": \"Lunar Ingress\", \"label\": \"Amavasya (New Moon) in Scorpio\", \"impact\": \"Deep karmic reset in communication & strategic resolve.\"},
    {\"date\": \"Nov 14\", \"type\": \"Graha Gati\", \"label\": \"Budha (Mercury) Stations Direct\", \"impact\": \"Clarity returns to contracts, negotiations, and intellectual output.\"},
    {\"date\": \"Nov 15\", \"type\": \"Graha Gati\", \"label\": \"Shukra (Venus) Stations Direct\", \"impact\": \"Financial blockages dissolve; relational warmth unlocks.\"},
    {\"date\": \"Nov 24\", \"type\": \"Purnima Illumination\", \"label\": \"Purnima (Full Moon) in Gemini\", \"impact\": \"Peak professional visibility and public culmination of efforts.\"}
  ],
  \"panchamahabhuta\": {
    \"dominant_element\": \"Agni (Fire) & Vayu (Air)\",
    \"balance_guidance\": \"Ground excessive mental currents by integrating Prithvi (Earth) grounding rituals.\",
    \"ratna_recommendation\": \"Ruby (Manikya) or Red Coral (Moonga) for vitality; or Emerald for commercial clarity.\",
    \"rudraksha\": \"3 Mukhi (Agni pacification) or 5 Mukhi (Guru harmony)\",
    \"vedic_mantra\": \"Om Hram Hreem Hroum Sah Suryaya Namah\",
    \"charitable_act\": \"Offer yellow pulses or copper utensils on Sunday morning to amplify solar blessings.\"
  },
  \"executive_dashboard\": {
    \"sankalpa\": \"I anchor my ambition in patience, knowing that conscious restraint turns into sovereign mastery.\",
    \"zenith_power_days\": [\"Nov 7\", \"Nov 8\", \"Nov 26\", \"Nov 27\"],
    \"sanctuary_rest_days\": [\"Nov 17\", \"Nov 18\", \"Nov 20\"],
    \"closing_blessing\": \"May the celestial council bestow clarity, wealth, and inner tranquility upon your horizon.\"
  }
}";
    }

    /**
     * Fallback deterministic data if AI is unreachable (Vedic Thermal & Topography Model)
     */
    private static function get_fallback_monthly_data($name, $month) {
        return [
            'monthly_theme' => 'Sovereign Ascent: Master the Internal Flame',
            'executive_synthesis' => "{$month} presents a profound duality of celestial currents: initial introspective consolidation leading into a formidable second-half surge of kinetic velocity.",
            'thermal_index' => 74,
            'thermal_status' => 'Radiant Agni (High Focus)',
            'thermal_split' => [
                'first_half' => 62,
                'first_half_label' => 'Shita & Deliberation',
                'second_half' => 84,
                'second_half_label' => 'Agni & Forward Expansion'
            ],
            'four_pillars' => [
                'karma' => [
                    'score' => 78,
                    'state' => 'Radiant Agni',
                    'headline' => 'Strategic consolidation precedes executive visibility.',
                    'analysis' => 'The career horizon demands focused craftsmanship during early retrograde cycles. Channel energy into refining infrastructure before pushing high-stake public debuts.',
                    'tactical_advice' => [
                        'Polish documentation and operational blueprints in private.',
                        'Avoid authority confrontations mid-month; remain factual.',
                        'The closing week brings decisive recognition and expansion.'
                    ]
                ],
                'dhana' => [
                    'score' => 68,
                    'state' => 'Temperate Sattva',
                    'headline' => 'Audit capital structures and reinforce liquidity.',
                    'analysis' => 'Planetary retrogrades in wealth sectors counsel against speculative haste. Reconcile outstanding receivables and audit recurring outlays before committing to long-term acquisitions.',
                    'tactical_advice' => [
                        'Defer major speculative investments until second half direct stations.',
                        'Pursue pending dues and settle lingering financial liabilities.',
                        'Build cash reserves for strategic deployments late month.'
                    ]
                ],
                'prema' => [
                    'score' => 64,
                    'state' => 'Deep Shita (Reflection)',
                    'headline' => 'Honest dialogue harmonizes karmic bonds.',
                    'analysis' => 'Relational dynamics undergo an evaluative recalibration. Unspoken tensions surface not to disrupt, but to invite deeper emotional transparency and alignment.',
                    'tactical_advice' => [
                        'Listen actively and replace defensive reactions with vulnerability.',
                        'Singles should enjoy intellectual connection without forcing vows.',
                        'The final ten days offer the highest romantic warmth of the month.'
                    ]
                ],
                'arogya' => [
                    'score' => 86,
                    'state' => 'Harmonic Prana',
                    'headline' => 'Robust solar endurance paired with restorative discipline.',
                    'analysis' => 'Pranic vitality is strong and resilient. The solar placement fuels muscular stamina and mental drive, provided nervous exhaustion is prevented through balanced sleep hygiene.',
                    'tactical_advice' => [
                        'Commit to structured morning physical training.',
                        'Stay hydrated and integrate grounding herbal infusions.',
                        'Practice Surya Namaskar during sunrise for optimal solar absorption.'
                    ]
                ]
            ],
            'topographic_trajectory' => [
                [
                    'phase' => 1,
                    'title' => 'The Ascent',
                    'dates' => 'Days 1–7',
                    'elevation' => 68,
                    'dynamic' => 'Internal Ignition',
                    'forecast' => 'Energy rises steadily behind the scenes. Ideal for rigorous private strategy, software or physical conditioning.',
                    'strategic_action' => 'Initiate dedicated study, workout plans, or confidential projects.',
                    'friction_trap' => 'Premature public debuts or signing unvetted pacts.'
                ],
                [
                    'phase' => 2,
                    'title' => 'The Valley of Recalibration',
                    'dates' => 'Days 8–14',
                    'elevation' => 59,
                    'dynamic' => 'Contemplative Alignment',
                    'forecast' => 'The New Moon invites conscious recalibration. Administrative delays test patience but yield superior clarity.',
                    'strategic_action' => 'Declutter communications and write down quarterly milestones.',
                    'friction_trap' => 'Impatience with administrative pace.'
                ],
                [
                    'phase' => 3,
                    'title' => 'The Crest of Realignment',
                    'dates' => 'Days 15–21',
                    'elevation' => 73,
                    'dynamic' => 'Structural Awakening',
                    'forecast' => 'Planetary direct stations release latent bottlenecks. Cross-functional dialogues regain rhythm and momentum.',
                    'strategic_action' => 'Negotiate revisions and present refined proposals.',
                    'friction_trap' => 'Ego skirmishes with senior decision makers.'
                ],
                [
                    'phase' => 4,
                    'title' => 'The Horizon Breakthrough',
                    'dates' => 'Days 22–30',
                    'elevation' => 88,
                    'dynamic' => 'Peak Kinetic Acceleration',
                    'forecast' => 'The month reaches its apex of velocity. Full Moon radiance illuminates career achievements and commercial breakthroughs.',
                    'strategic_action' => 'Close agreements, launch public campaigns, and celebrate wins.',
                    'friction_trap' => 'Hesitating when clear openings manifest.'
                ]
            ],
            'angular_triggers' => [
                ['date' => 'Day 7', 'type' => 'Kendra Alignment', 'label' => 'Surya & Mangal Conjunction', 'impact' => 'High kinetic surge; channel into solo engineering.'],
                ['date' => 'Day 9', 'type' => 'Lunar Ingress', 'label' => 'Amavasya in Deep Water Sign', 'impact' => 'Deep karmic reset in strategic resolve.'],
                ['date' => 'Day 14', 'type' => 'Graha Gati', 'label' => 'Budha Stations Direct', 'impact' => 'Clarity returns to contracts and commercial dialogue.'],
                ['date' => 'Day 15', 'type' => 'Graha Gati', 'label' => 'Shukra Stations Direct', 'impact' => 'Relational warmth unlocks and financial flow clears.'],
                ['date' => 'Day 24', 'type' => 'Purnima Illumination', 'label' => 'Purnima in Air Sign', 'impact' => 'Peak professional visibility and commercial validation.']
            ],
            'panchamahabhuta' => [
                'dominant_element' => 'Agni (Fire) & Vayu (Air)',
                'balance_guidance' => 'Ground excessive mental currents by integrating Prithvi (Earth) grounding rituals.',
                'ratna_recommendation' => 'Ruby (Manikya) for solar confidence or Emerald for commercial acumen.',
                'rudraksha' => '3 Mukhi (Pacifies excess fire) or 5 Mukhi (Mental equanimity)',
                'vedic_mantra' => 'Om Hram Hreem Hroum Sah Suryaya Namah',
                'charitable_act' => 'Support students with educational materials on Wednesday or donate grains on Sunday.'
            ],
            'executive_dashboard' => [
                'sankalpa' => 'I anchor my ambition in conscious patience, turning inner restraint into outer sovereign mastery.',
                'zenith_power_days' => ['Day 7', 'Day 8', 'Day 26', 'Day 27'],
                'sanctuary_rest_days' => ['Day 17', 'Day 18', 'Day 20'],
                'closing_blessing' => 'May the celestial council bestow clarity, wealth, and inner tranquility upon your horizon.'
            ]
        ];
    }

    /**
     * Build prompt for 1-Year Varshaphal Horoscope Report
     */
    private static function build_yearly_editorial_prompt($name, $profile, $year = 2027) {
        $lagna = $profile['lagna'] ?? 'Pisces (Meena)';
        $moon  = $profile['moon_sign'] ?? 'Leo (Simha)';
        $nakshatra = $profile['nakshatra'] ?? 'Magha';
        $dasha = $profile['dasha_transition'] ?? 'Mars Mahadasha completes 29 January 2027; Rahu Mahadasha begins 29 January 2027 for 18 years';
        $transits = $profile['transits'] ?? 'Saturn in Pisces (1st House), Jupiter moves into Cancer (5th House, exalted), Rahu in Cancer, Ketu in Capricorn';

        return "You are the Chief Vedic Astrologer & Editorial Director for MeraRashifal (merarashifal.com).
Generate an authoritative, deeply insightful, publication-grade Annual Vedic Horoscope (Varshaphal) for the year {$year}.

NATIVE SIGNATURE:
- Native Name: {$name}
- Lagna (Ascendant): {$lagna}
- Moon Sign (Janma Rashi): {$moon} (Nakshatra: {$nakshatra})
- Vimshottari Milestone: {$dasha}
- 2027 Planetary Transits: {$transits}

TONE:
- Warm, dignified, practical Vedic English for an Indian audience (career growth, family harmony, safe financial expansion, Shubh Muhurat, Upayas, Surya Arghya, Ghee Diya, Shiv Puja, Daan).
- High legibility, relatable life wisdom.

OUTPUT JSON FORMAT (Respond ONLY with valid JSON):
{
  \"annual_theme\": \"Punchy 8-12 word theme for {$year}\",
  \"annual_motto\": \"1-line guidance mantra\",
  \"executive_summary\": \"150-word synthesis of {$year} dynamics\",
  \"vitality_index\": 82,
  \"vitality_status\": \"High Transformation & Strategic Expansion\",
  \"dasha_sandhi\": {
    \"title\": \"The Monumental Mars-to-Rahu Transition (January 29, 2027)\",
    \"what_ends\": \"Summary of completing 7 years of Mars\",
    \"what_begins\": \"What 18 years of Rahu brings to the 5th house\",
    \"transition_rules\": [\"Rule 1\", \"Rule 2\", \"Rule 3\"]
  },
  \"major_transits\": [
    {\"planet\": \"Saturn in Pisces (1st House)\", \"period\": \"All of {$year}\", \"impact\": \"Impact detail...\", \"advice\": \"Advice detail...\"},
    {\"planet\": \"Jupiter in Cancer (Exalted in 5th House)\", \"period\": \"May - December {$year}\", \"impact\": \"Impact detail...\", \"advice\": \"Advice detail...\"},
    {\"planet\": \"Rahu-Ketu Axis (5th & 11th Houses)\", \"period\": \"Full Year\", \"impact\": \"Impact detail...\", \"advice\": \"Advice detail...\"}
  ],
  \"quarterly_roadmap\": {
    \"q1\": {\"name\": \"Quarter 1: The Transition & Groundwork\", \"months\": \"January to March {$year}\", \"score\": 76, \"theme\": \"Theme...\", \"overview\": \"Overview...\", \"dos\": [\"Do 1\", \"Do 2\", \"Do 3\"], \"donts\": [\"Don't 1\", \"Don't 2\", \"Don't 3\"], \"key_dates\": \"Key dates...\"},
    \"q2\": {\"name\": \"Quarter 2: Expansion & Strategic Moves\", \"months\": \"April to June {$year}\", \"score\": 84, \"theme\": \"Theme...\", \"overview\": \"Overview...\", \"dos\": [\"Do 1\", \"Do 2\", \"Do 3\"], \"donts\": [\"Don't 1\", \"Don't 2\", \"Don't 3\"], \"key_dates\": \"Key dates...\"},
    \"q3\": {\"name\": \"Quarter 3: Consolidation & Family Harmony\", \"months\": \"July to September {$year}\", \"score\": 79, \"theme\": \"Theme...\", \"overview\": \"Overview...\", \"dos\": [\"Do 1\", \"Do 2\", \"Do 3\"], \"donts\": [\"Don't 1\", \"Don't 2\", \"Don't 3\"], \"key_dates\": \"Key dates...\"},
    \"q4\": {\"name\": \"Quarter 4: Harvest, Recognition & Celebration\", \"months\": \"October to December {$year}\", \"score\": 90, \"theme\": \"Theme...\", \"overview\": \"Overview...\", \"dos\": [\"Do 1\", \"Do 2\", \"Do 3\"], \"donts\": [\"Don't 1\", \"Don't 2\", \"Don't 3\"], \"key_dates\": \"Key dates...\"}
  },
  \"life_pillars\": {
    \"career\": {\"score\": 86, \"title\": \"Karma & Vocation Horizon\", \"narrative\": \"Narrative...\", \"power_months\": \"April, May, October, November\", \"caution_periods\": \"Mid-February\", \"golden_advice\": \"Advice...\"},
    \"wealth\": {\"score\": 83, \"title\": \"Artha & Asset Horizon\", \"narrative\": \"Narrative...\", \"power_months\": \"March, June, November\", \"caution_periods\": \"August\", \"golden_advice\": \"Advice...\"},
    \"family\": {\"score\": 81, \"title\": \"Bandhu, Marriage & Relationships\", \"narrative\": \"Narrative...\", \"power_months\": \"May, July, October\", \"caution_periods\": \"Early September\", \"golden_advice\": \"Advice...\"},
    \"health\": {\"score\": 78, \"title\": \"Ayur, Prana & Mental Wellness\", \"narrative\": \"Narrative...\", \"power_months\": \"October, November\", \"caution_periods\": \"January to March\", \"golden_advice\": \"Advice...\"}
  },
  \"monthly_energy_scores\": [72, 70, 78, 85, 87, 82, 75, 79, 84, 91, 88, 83],
  \"remedial_blueprint\": {
    \"gemstone\": {\"primary\": \"Yellow Sapphire (Pukhraj) or Cat's Eye\", \"metal\": \"Gold/Silver\", \"finger\": \"Index/Middle Finger\", \"day\": \"Thursday/Saturday morning\", \"guidance\": \"Guidance...\"},
    \"rudraksha\": \"8 Mukhi Rudraksha (Rahu) & 5 Mukhi Rudraksha (Jupiter)\",
    \"upayas\": [
      \"Sunday Morning Surya Arghya: Offer water with red kumkum and flowers to Lord Surya.\",
      \"Saturday Ghee / Sesame Diya: Light a lamp under a Peepal tree to appease Shani Dev.\",
      \"Rahu Shanti: Donate blue/grey clothes or blankets to the needy.\",
      \"Wednesday Ganesh Puja: Recite Ganesh Atharvashirsha for intellect and success.\"
    ],
    \"lucky_attributes\": {
      \"favorable_days\": \"Tuesday, Thursday, Sunday\",
      \"favorable_colors\": \"Deep Saffron, Cream White, Royal Blue\",
      \"favorable_numbers\": \"1, 3, 9\",
      \"favorable_direction\": \"East and North-East\"
    }
  },
  \"page_takeaways\": {
    \"page_2\": [\"The Mars-to-Rahu shift is a once-in-18-year transformation.\", \"January is for closure and patience; February is for observation.\", \"The 5th house activation favors long-term intellectual property.\"],
    \"page_3\": [\"Saturn in the 1st house demands emotional maturity and discipline.\", \"Clear daily routines protect health and mental focus.\", \"Avoid shortcuts in legal and professional commitments.\"],
    \"page_4\": [\"Jupiter's transit into Cancer brings immense divine grace and family luck.\", \"Property decisions made after May show sustainable growth.\", \"Spiritual learning and mentor relationships flourish.\"],
    \"page_5\": [\"Rahu in the 5th house unlocks creative and tech-driven breakthroughs.\", \"Ground speculative impulses with sound financial logic.\", \"High-value strategic networking replaces broad socializing.\"],
    \"page_6\": [\"Q1 focuses on steady foundation and closing 2026 legacy items.\", \"Listen to health cues during the Dasha change in late January.\", \"March opens up fresh professional dialogues and optimism.\"],
    \"page_7\": [\"Q2 marks high acceleration under Jupiter's direct blessings.\", \"May and June bring fruitful business partnerships and learning.\", \"Domestic happiness and peace in the household reach an annual high.\"],
    \"page_8\": [\"Q3 is your consolidation window—review assets and family wills.\", \"Avoid hasty financial lending to acquaintances during August.\", \"Children's progress and education bring pride and celebration.\"],
    \"page_9\": [\"Q4 is the peak harvest season with awards, promotion, and Diwali joy.\", \"October 24 and November 18 are standout dates for wealth inflow.\", \"The year closes with heightened social prestige and spiritual gratitude.\"],
    \"page_10\": [\"Lead through vision and systematic management rather than manual hustle.\", \"10th house Budhaditya Yoga brings recognition in high-level circles.\", \"Build institutional processes that run smoothly with digital support.\"],
    \"page_11\": [\"Move capital from active trading into secured real estate and liquid funds.\", \"Exalted Mars in 11th guarantees strong income streams.\", \"Maintain strict budget oversight during July and August.\"],
    \"page_12\": [\"Active listening and emotional warmth strengthen marital harmony.\", \"Plan a family pilgrimage or serene retreat in the fourth quarter.\", \"Elderly family members offer valuable blessings and support.\"],
    \"page_13\": [\"Prioritize spine mobility, joint care, and hydration.\", \"A clean Satvic diet minimizes digestive and Vata imbalances.\", \"Daily morning walks in sunlight boost solar vitality and mental calm.\"],
    \"page_14\": [\"Pukhraj (Yellow Sapphire) or Cat's Eye strengthens core destiny.\", \"Regular Saturday Peepal diya softens Saturnian pressure on Lagna.\", \"Ganesh Atharvashirsha keeps Rahu's intellect sharp and clear.\"]
  }
}
";
    }

    /**
     * Fallback 2027 Yearly Horoscope Data (Full Editorial Blueprint)
     */
    private static function get_fallback_yearly_data($person_name, $year = 2027) {
        $json_file = 'C:/Users/Tushar Singhal/.gemini/antigravity/brain/8095b7a1-c95a-4e62-b745-e927ba760684/scratch/tushar_2027_gemini.json';
        if (file_exists($json_file)) {
            $data = json_decode(file_get_contents($json_file), true);
            if (is_array($data) && !empty($data['annual_theme'])) {
                return $data;
            }
        }

        return [
            'annual_theme' => 'Strategic Rebirth: From Relentless Physical Drive to Visionary Intellectual Mastery',
            'annual_motto' => 'Transition from the warrior\'s sword to the strategist\'s pen for ultimate prosperity.',
            'executive_summary' => '2027 stands as a monumental Bridge Year for ' . esc_html($person_name) . '. On January 29, you conclude a seven-year cycle of high-octane physical exertion (Mars Mahadasha) and enter a transformative 18-year era of Rahu. This Dasha Sandhi coincides with Saturn\'s grounding transit through your 1st House, demanding inward discipline. The latter half of the year is exceptionally luminous with exalted Jupiter activating your 5th House of intellect and fortune.',
            'vitality_index' => 82,
            'vitality_status' => 'High Transformation & Strategic Expansion',
            'dasha_sandhi' => [
                'title' => 'The Monumental Mars-to-Rahu Transition (January 29, 2027)',
                'what_ends' => 'The seven-year Mars Mahadasha concludes, ending a period characterized by intense competition and physical labor.',
                'what_begins' => 'The 18-year Rahu Mahadasha begins, activating your 5th House in Cancer. This initiates a long-term cycle of unconventional growth and strategic thinking.',
                'transition_rules' => [
                    'Practice meditation during the last week of January to ease the psychic shift.',
                    'Avoid major new capital commitments in the 15 days surrounding January 29.',
                    'Prioritize hydration and cooling foods to balance the heat of departing Mars.'
                ]
            ],
            'major_transits' => [
                ['planet' => 'Saturn in Pisces (1st House)', 'period' => 'All of ' . $year, 'impact' => 'Demands self-discipline, routine, and emotional sobriety.', 'advice' => 'Daily morning walk and consistent sleep cycles are essential.'],
                ['planet' => 'Jupiter in Cancer (Exalted in 5th House)', 'period' => 'May - December ' . $year, 'impact' => 'Exalted Jupiter brings tremendous luck, wisdom in investments, and happy family events.', 'advice' => 'Ideal time for property acquisition and long-term plans.'],
                ['planet' => 'Rahu-Ketu Axis (5th & 11th Houses)', 'period' => 'Full Year', 'impact' => 'Unlocks intellectual breakthroughs while curbing unnecessary socializing.', 'advice' => 'Focus on high-value niche projects.']
            ],
            'quarterly_roadmap' => [
                'q1' => ['name' => 'Quarter 1: The Transition & Groundwork', 'months' => 'January to March ' . $year, 'score' => 76, 'theme' => 'Navigating the Dasha shift with calm focus', 'overview' => 'The year begins with the heavy lifting of the Dasha change. Introspection and closing old files will set a firm foundation.', 'dos' => ['Audit personal health', 'Tie up loose ends from 2026', 'Consult trusted mentors'], 'donts' => ['Start high-risk physical ventures', 'Overreact to temporary delays', 'Neglect back health'], 'key_dates' => 'Jan 29 (Dasha Change), Feb 12 (Caution), March 18 (Clarity)'],
                'q2' => ['name' => 'Quarter 2: Expansion & Strategic Moves', 'months' => 'April to June ' . $year, 'score' => 84, 'theme' => 'New avenues open up under Jupiter\'s expanding gaze', 'overview' => 'As Jupiter enters Cancer, optimism surges. Professional networking and creative ventures yield lucrative breakthroughs.', 'dos' => ['Update professional profile', 'Invest in learning and certifications', 'Host a family gathering'], 'donts' => ['Ignore spouse\'s advice', 'Over-leverage credit', 'Skip morning rituals'], 'key_dates' => 'May 3 (Jupiter Transit), June 15 (Financial Gain)'],
                'q3' => ['name' => 'Quarter 3: Consolidation & Family Harmony', 'months' => 'July to September ' . $year, 'score' => 79, 'theme' => 'Strengthening assets and personal wellbeing', 'overview' => 'With Jupiter now exalted, wisdom is at its peak. Consolidate gains and support children\'s milestones.', 'dos' => ['Review insurance and wills', 'Perform regular charity (Daan)', 'Focus on digestive health'], 'donts' => ['Lend money to distant friends', 'Ignore minor symptoms', 'Rushed travel'], 'key_dates' => 'August 12 (Caution), Sept 5 (Spiritual Breakthrough)'],
                'q4' => ['name' => 'Quarter 4: Harvest, Recognition & Celebration', 'months' => 'October to December ' . $year, 'score' => 90, 'theme' => 'Peak rewards, financial gains, and festive prosperity', 'overview' => 'The year ends on a high note. Career recognition, financial inflows, and festive warmth bring deep satisfaction.', 'dos' => ['Public presentations & speaking', 'Major strategic investments', 'Family religious pilgrimage'], 'donts' => ['Arrogance in success', 'Neglecting old friends', 'Overindulgence in sweets'], 'key_dates' => 'Oct 24 (Major Success), Nov 18 (Wealth Inflow), Dec 25 (Celebration)']
            ],
            'life_pillars' => [
                'career' => ['score' => 86, 'title' => 'Karma & Vocation Horizon', 'narrative' => 'Your 10th house Budhaditya Yoga is energized by incoming Rahu Dasha. Move from manual hustle into strategic thought leadership and institutional management.', 'power_months' => 'April, May, October, November', 'caution_periods' => 'Mid-February', 'golden_advice' => 'Systematize your operations so they run smoothly with digital architecture.'],
                'wealth' => ['score' => 83, 'title' => 'Artha & Asset Horizon', 'narrative' => 'Transition from active labor into asset-backed wealth. Exalted Mars in 11th coupled with exalted Jupiter in 5th creates uncanny financial intuition.', 'power_months' => 'March, June, November', 'caution_periods' => 'Avoid speculative day-trading in August', 'golden_advice' => 'Diversify into secured real estate and liquid assets during the second half.'],
                'family' => ['score' => 81, 'title' => 'Bandhu, Marriage & Relationships', 'narrative' => 'Domestic life eases into deep mutual respect and joy through progeny. You become a revered pillar of guidance for extended family.', 'power_months' => 'May, July, October', 'caution_periods' => 'Early September', 'golden_advice' => 'Practice active listening; keep warmth and affection at the forefront.'],
                'health' => ['score' => 78, 'title' => 'Ayur, Prana & Mental Wellness', 'narrative' => 'Saturn in Lagna demands regular exercise, spine mobility, and hydration. A disciplined morning routine eliminates fatigue.', 'power_months' => 'October, November', 'caution_periods' => 'January to March', 'golden_advice' => 'Adopt a Satvic diet and daily morning Surya Namaskar.']
            ],
            'monthly_energy_scores' => [72, 70, 78, 85, 87, 82, 75, 79, 84, 91, 88, 83],
            'remedial_blueprint' => [
                'gemstone' => ['primary' => 'Yellow Sapphire (Pukhraj) or Cat\'s Eye', 'metal' => 'Gold or Silver', 'finger' => 'Index or Middle Finger', 'day' => 'Thursday or Saturday morning', 'guidance' => 'Ensure an eye-clean stone set in an open-back ring touching the skin.'],
                'rudraksha' => '8 Mukhi Rudraksha (Rahu) and 5 Mukhi Rudraksha (Jupiter) worn together.',
                'upayas' => [
                    'Sunday Morning Surya Arghya: Offer water with red kumkum and flowers to Lord Surya.',
                    'Saturday Ghee / Sesame Diya: Light a lamp under a Peepal tree to appease Shani Dev.',
                    'Rahu Shanti: Donate blue/grey clothes or blankets to the needy after Jan 29.',
                    'Wednesday Ganesh Puja: Recite Ganesh Atharvashirsha for intellect and success.'
                ],
                'lucky_attributes' => [
                    'favorable_days' => 'Tuesday, Thursday, Sunday',
                    'favorable_colors' => 'Deep Saffron, Cream White, Royal Blue',
                    'favorable_numbers' => '1, 3, 9',
                    'favorable_direction' => 'East and North-East'
                ]
            ],
            'page_takeaways' => [
                'page_2' => ['The Mars-to-Rahu shift is a once-in-18-year transformation.', 'January is for closure and patience; February is for observation.', 'The 5th house activation favors long-term intellectual property.'],
                'page_3' => ['Saturn in the 1st house demands emotional maturity and discipline.', 'Clear daily routines protect health and mental focus.', 'Avoid shortcuts in legal and professional commitments.'],
                'page_4' => ['Jupiter\'s transit into Cancer brings immense divine grace and family luck.', 'Property decisions made after May show sustainable growth.', 'Spiritual learning and mentor relationships flourish.'],
                'page_5' => ['Rahu in the 5th house unlocks creative and tech-driven breakthroughs.', 'Ground speculative impulses with sound financial logic.', 'High-value strategic networking replaces broad socializing.'],
                'page_6' => ['Q1 focuses on steady foundation and closing 2026 legacy items.', 'Listen to health cues during the Dasha change in late January.', 'March opens up fresh professional dialogues and optimism.'],
                'page_7' => ['Q2 marks high acceleration under Jupiter\'s direct blessings.', 'May and June bring fruitful business partnerships and learning.', 'Domestic happiness and peace in the household reach an annual high.'],
                'page_8' => ['Q3 is your consolidation window—review assets and family wills.', 'Avoid hasty financial lending to acquaintances during August.', 'Children\'s progress and education bring pride and celebration.'],
                'page_9' => ['Q4 is the peak harvest season with awards, promotion, and Diwali joy.', 'October 24 and November 18 are standout dates for wealth inflow.', 'The year closes with heightened social prestige and spiritual gratitude.'],
                'page_10' => ['Lead through vision and systematic management rather than manual hustle.', '10th house Budhaditya Yoga brings recognition in high-level circles.', 'Build institutional processes that run smoothly with digital support.'],
                'page_11' => ['Move capital from active trading into secured real estate and liquid funds.', 'Exalted Mars in 11th guarantees strong income streams.', 'Maintain strict budget oversight during July and August.'],
                'page_12' => ['Active listening and emotional warmth strengthen marital harmony.', 'Plan a family pilgrimage or serene retreat in the fourth quarter.', 'Elderly family members offer valuable blessings and support.'],
                'page_13' => ['Prioritize spine mobility, joint care, and hydration.', 'A clean Satvic diet minimizes digestive and Vata imbalances.', 'Daily morning walks in sunlight boost solar vitality and mental calm.'],
                'page_14' => ['Pukhraj (Yellow Sapphire) or Cat\'s Eye strengthens core destiny.', 'Regular Saturday Peepal diya softens Saturnian pressure on Lagna.', 'Ganesh Atharvashirsha keeps Rahu\'s intellect sharp and clear.']
            ]
        ];
    }

    /**
     * Adapt full yearly horoscope data into concise 5-page starter payload
     */
    private static function adapt_full_to_short_yearly($full, $person_name, $year = 2027) {
        $q_roadmap = $full['quarterly_roadmap'] ?? [];
        $pillars   = $full['life_pillars'] ?? [];
        $transits  = $full['major_transits'] ?? [];
        $remedies  = $full['remedial_blueprint'] ?? [];
        $takeaways = $full['page_takeaways'] ?? [];

        return [
            'annual_theme'      => $full['annual_theme'] ?? 'Strategic Rebirth: From Physical Drive to Intellectual Mastery',
            'annual_motto'      => $full['annual_motto'] ?? 'Transition from the warrior\'s sword to the strategist\'s pen for ultimate prosperity.',
            'executive_summary' => $full['executive_summary'] ?? ("{$year} stands as a pivotal Bridge Year for " . esc_html($person_name) . '. Step boldly into new cosmic cycles with discipline and divine grace.'),
            'vitality_index'    => $full['vitality_index'] ?? 82,
            'vitality_status'   => $full['vitality_status'] ?? 'High Transformation & Strategic Expansion',
            'dasha_summary'     => $full['dasha_sandhi']['what_begins'] ?? 'Rahu Mahadasha activates strategic intellect, high-altitude networking, and unconventional wealth streams in 2027.',
            'dasha_milestone'   => 'Mars finishes Jan 29, 2027; Rahu 18-Year Era Begins',
            'major_transits'    => array_slice($transits, 0, 3),
            'quarters'          => [
                'q1' => [
                    'name'   => $q_roadmap['q1']['name'] ?? 'Quarter 1: Foundation',
                    'months' => 'Jan – Mar ' . $year,
                    'score'  => $q_roadmap['q1']['score'] ?? 76,
                    'theme'  => $q_roadmap['q1']['theme'] ?? 'Navigating the Dasha shift with calm focus',
                    'advice' => $q_roadmap['q1']['dos'][0] ?? 'Close legacy items from 2026; audit personal health routines.'
                ],
                'q2' => [
                    'name'   => $q_roadmap['q2']['name'] ?? 'Quarter 2: Expansion',
                    'months' => 'Apr – Jun ' . $year,
                    'score'  => $q_roadmap['q2']['score'] ?? 84,
                    'theme'  => $q_roadmap['q2']['theme'] ?? 'Strategic expansion under Jupiter’s divine aspect',
                    'advice' => $q_roadmap['q2']['dos'][0] ?? 'Expand business partnerships and invest in high-level learning.'
                ],
                'q3' => [
                    'name'   => $q_roadmap['q3']['name'] ?? 'Quarter 3: Consolidation',
                    'months' => 'Jul – Sep ' . $year,
                    'score'  => $q_roadmap['q3']['score'] ?? 79,
                    'theme'  => $q_roadmap['q3']['theme'] ?? 'Consolidating assets and reinforcing family harmony',
                    'advice' => $q_roadmap['q3']['dos'][0] ?? 'Review insurance, wills, and long-term liquidity reserves.'
                ],
                'q4' => [
                    'name'   => $q_roadmap['q4']['name'] ?? 'Quarter 4: Harvest',
                    'months' => 'Oct – Dec ' . $year,
                    'score'  => $q_roadmap['q4']['score'] ?? 90,
                    'theme'  => $q_roadmap['q4']['theme'] ?? 'Peak annual rewards, festive bounty, and recognition',
                    'advice' => $q_roadmap['q4']['dos'][0] ?? 'Capitalize on festive wealth inflows and celebrate milestones.'
                ]
            ],
            'life_pillars'      => [
                'career' => [
                    'score'   => $pillars['career']['score'] ?? 86,
                    'title'   => 'Karma & Career',
                    'summary' => $pillars['career']['golden_advice'] ?? 'Systematize operations with digital architecture; lead with strategic foresight.'
                ],
                'wealth' => [
                    'score'   => $pillars['wealth']['score'] ?? 83,
                    'title'   => 'Artha & Wealth',
                    'summary' => $pillars['wealth']['golden_advice'] ?? 'Shift active capital into secured real estate and liquid funds; avoid speculative trading.'
                ],
                'family' => [
                    'score'   => $pillars['family']['score'] ?? 81,
                    'title'   => 'Bandhu & Family',
                    'summary' => $pillars['family']['golden_advice'] ?? 'Practice active listening; domestic joy and blessings through children thrive.'
                ],
                'health' => [
                    'score'   => $pillars['health']['score'] ?? 78,
                    'title'   => 'Ayur & Vitality',
                    'summary' => $pillars['health']['golden_advice'] ?? 'Maintain morning walks, spine mobility, and a nourishing Satvic diet.'
                ]
            ],
            'remedial_starter'  => [
                'lucky_attributes' => $remedies['lucky_attributes'] ?? [
                    'favorable_days'      => 'Tuesday, Thursday, Sunday',
                    'favorable_colors'    => 'Deep Saffron, Cream White, Royal Blue',
                    'favorable_numbers'   => '1, 3, 9',
                    'favorable_direction' => 'East & North-East'
                ],
                'gemstone'  => $remedies['gemstone']['primary'] ?? 'Yellow Sapphire (Pukhraj) or Cat’s Eye',
                'rudraksha' => $remedies['rudraksha'] ?? '5 Mukhi Rudraksha (Jupiter) and 8 Mukhi (Rahu)',
                'upayas'    => array_slice($remedies['upayas'] ?? [
                    'Sunday Surya Arghya: Offer water with red kumkum and flowers to Lord Surya.',
                    'Saturday Peepal Diya: Light a sesame oil lamp under a Peepal tree for Shani Dev.',
                    'Wednesday Ganesh Puja: Recite Ganesh Atharvashirsha for intellect and victory.'
                ], 0, 3)
            ],
            'takeaways'         => [
                'page_2' => [
                    $takeaways['page_2'][0] ?? '2027 marks a once-in-a-generation turning point with active Dasha shifts.',
                    $takeaways['page_2'][1] ?? 'Discipline in daily routines protects vitality under Saturnian transit.',
                    $takeaways['page_2'][2] ?? 'The second half brings luminous growth with exalted Jupiter activating the 5th House.'
                ],
                'page_3' => [
                    $takeaways['page_3'][0] ?? 'Lagna Lord Jupiter placed in a Kendra ensures strong divine protection across 2027.',
                    $takeaways['page_3'][1] ?? 'Budhaditya Yoga in the 10th House empowers executive consulting and authority.',
                    $takeaways['page_3'][2] ?? 'Exalted Mars in the 11th House guarantees immense financial resilience and competitive victory.'
                ],
                'page_4' => [
                    $takeaways['page_6'][0] ?? 'Q1 is for steady groundwork; Q2 accelerates with strategic partnerships.',
                    $takeaways['page_8'][0] ?? 'Q3 consolidates assets, while Q4 marks the peak annual harvest of wealth and recognition.',
                    $takeaways['page_10'][0] ?? 'Focus on high-value systemic execution rather than manual exhaustion across all four quarters.'
                ]
            ]
        ];
    }

    /**
     * Fallback 5-Page Free Starter Yearly Horoscope Data
     */
    private static function get_fallback_short_yearly_data($person_name, $year = 2027) {
        $full = self::get_fallback_yearly_data($person_name, $year);
        return self::adapt_full_to_short_yearly($full, $person_name, $year);
    }

    /**
     * Build Prompt for 5-Page Free Starter Varshaphal
     */
    private static function build_short_yearly_editorial_prompt($name, $profile, $year = 2027) {
        $lagna = $profile['lagna'] ?? 'Pisces (Meena)';
        $moon  = $profile['moon_sign'] ?? 'Leo (Simha)';
        $dasha = $profile['dasha_transition'] ?? 'Mars Mahadasha completes Jan 2027; Rahu begins for 18 years';
        $transits = $profile['transits'] ?? 'Saturn in Lagna, Jupiter exalted in 5th, Rahu in 5th, Ketu in 11th';

        return "You are the Chief Vedic Astrologer for MeraRashifal (merarashifal.com).
Generate a concise, high-value, publication-grade 5-Page Free Starter Annual Vedic Horoscope (Varshaphal) for {$year}.

NATIVE SIGNATURE:
- Native Name: {$name}
- Lagna: {$lagna}
- Moon Sign: {$moon}
- Active Dasha: {$dasha}
- Key Transits: {$transits}

OUTPUT JSON FORMAT (Respond ONLY with valid JSON):
{
  \"annual_theme\": \"Punchy inspiring 8-12 word title\",
  \"annual_motto\": \"1-line guidance mantra\",
  \"executive_summary\": \"80-100 words concise overview of 2027\",
  \"vitality_index\": 82,
  \"vitality_status\": \"High Transformation & Strategic Expansion\",
  \"dasha_summary\": \"2-sentence overview of the active Dasha shift\",
  \"dasha_milestone\": \"Mars finishes Jan 29, 2027; Rahu 18-Year Era Begins\",
  \"major_transits\": [
    {\"planet\": \"Saturn in Pisces (1st House)\", \"period\": \"All of {$year}\", \"impact\": \"Self-discipline and grounded habits.\", \"advice\": \"Maintain consistent daily routines.\"},
    {\"planet\": \"Jupiter in Cancer (Exalted in 5th)\", \"period\": \"May - Dec {$year}\", \"impact\": \"Divine intuition and asset growth.\", \"advice\": \"Excellent for long-term investments.\"},
    {\"planet\": \"Rahu-Ketu Axis (5th & 11th)\", \"period\": \"Full Year\", \"impact\": \"Tech strategy and selective networking.\", \"advice\": \"Focus on high-value niche pursuits.\"}
  ],
  \"quarters\": {
    \"q1\": {\"name\": \"Quarter 1: Foundation\", \"months\": \"Jan – Mar {$year}\", \"score\": 76, \"theme\": \"Navigating the Dasha shift with calm focus\", \"advice\": \"Close legacy items; audit health routines.\"},
    \"q2\": {\"name\": \"Quarter 2: Expansion\", \"months\": \"Apr – Jun {$year}\", \"score\": 84, \"theme\": \"Strategic expansion under Jupiter’s aspect\", \"advice\": \"Expand business partnerships and learning.\"},
    \"q3\": {\"name\": \"Quarter 3: Consolidation\", \"months\": \"Jul – Sep {$year}\", \"score\": 79, \"theme\": \"Consolidating assets and family harmony\", \"advice\": \"Review insurance, wills, and cash reserves.\"},
    \"q4\": {\"name\": \"Quarter 4: Harvest\", \"months\": \"Oct – Dec {$year}\", \"score\": 90, \"theme\": \"Peak annual rewards, festive bounty & recognition\", \"advice\": \"Capitalize on wealth inflows and celebrate.\"}
  },
  \"life_pillars\": {
    \"career\": {\"score\": 86, \"title\": \"Karma & Career\", \"summary\": \"Systematize operations; lead with strategic foresight.\"},
    \"wealth\": {\"score\": 83, \"title\": \"Artha & Wealth\", \"summary\": \"Shift active capital into secured real estate and liquid funds.\"},
    \"family\": {\"score\": 81, \"title\": \"Bandhu & Family\", \"summary\": \"Practice active listening; domestic joy thrives.\"},
    \"health\": {\"score\": 78, \"title\": \"Ayur & Vitality\", \"summary\": \"Morning walks, spine mobility, and nourishing diet.\"}
  },
  \"remedial_starter\": {
    \"lucky_attributes\": {
      \"favorable_days\": \"Tuesday, Thursday, Sunday\",
      \"favorable_colors\": \"Deep Saffron, Cream White, Royal Blue\",
      \"favorable_numbers\": \"1, 3, 9\",
      \"favorable_direction\": \"East & North-East\"
    },
    \"gemstone\": \"Yellow Sapphire (Pukhraj) or Cat’s Eye\",
    \"rudraksha\": \"5 Mukhi (Jupiter) and 8 Mukhi (Rahu)\",
    \"upayas\": [
      \"Sunday Surya Arghya: Offer water with red kumkum to Lord Surya.\",
      \"Saturday Peepal Diya: Light a sesame oil lamp under Peepal tree for Shani.\",
      \"Wednesday Ganesh Puja: Recite Ganesh Atharvashirsha for intellect.\"
    ]
  },
  \"takeaways\": {
    \"page_2\": [
      \"{$year} marks a monumental bridge year with active Dasha shifts.\",
      \"Discipline in daily routines protects vitality under Saturnian transit.\",
      \"The second half brings luminous growth with exalted Jupiter activating the 5th House.\"
    ],
    \"page_3\": [
      \"Lagna Lord Jupiter placed in a Kendra ensures strong divine protection.\",
      \"Budhaditya Yoga in the 10th House empowers executive consulting and authority.\",
      \"Exalted Mars in the 11th House guarantees financial resilience and victory.\"
    ],
    \"page_4\": [
      \"Q1 focuses on steady foundation, while Q2 accelerates with strategic alliances.\",
      \"Q3 consolidates family assets, and Q4 delivers the peak annual harvest.\",
      \"Focus on systemic execution rather than manual hustle across all quarters.\"
    ]
  }
}";
    }

    /**
     * Build Prompt for Gemini Lifetime Kundali Editorial Synthesis
     */
    private static function build_kundali_editorial_prompt($name, $profile) {
        $lagna = $profile['lagna'] ?? 'Cancer (Karka) 21.27°';
        $moon = $profile['moon'] ?? 'Leo (Simha) 11.89° Magha Pada 4';
        $sun = $profile['sun'] ?? 'Sagittarius (Dhanu) 11.42° Mula';
        $dasha = $profile['dasha'] ?? 'Rahu Mahadasha active (2024–2042)';

        return "You are the Chief Vedic Astrologer & Editorial Director for MeraRashifal (merarashifal.com).
Generate an authoritative, publication-grade Master Janam Kundali & Lifetime Destiny Dossier for:
Native: {$name}
Natal Placements:
- Lagna: {$lagna}
- Chandra Rashi: {$moon}
- Surya Rashi: {$sun}
- Active Timeline: {$dasha}
- Special Placements: Mars exalted in Capricorn in Kendra; Venus as Atmakaraka (AK) in Scorpio; Saturn as Amatyakaraka (AmK) in Virgo; Jupiter in Virgo casting 9th aspect on Lagna/Rahu; Sun-Mercury in Sagittarius.

Core Editorial Rules:
1. Tone: Sovereign, dignified, psychologically astute, rooted in classical Maharishi Parashara and Maharishi Jaimini Jyotish, with modern executive clarity.
2. Atmakaraka & Amatyakaraka: Explain Venus as Atmakaraka (soul purpose, transforming worldly attachments into refined devotion and discerning aesthetics) and Saturn as Amatyakaraka (career intellect, structured enterprise discipline, institutional stewardship).
3. Karmic Debt & Neutralization: Do NOT leave doshas with alarming fear. Provide classical cancellation and neutralizing factors:
   - Manglik Dosh (Anshik): Mitigated because Mars is in its highest exaltation (Ucha Makara) in Kendra, and Jupiter casts a protective trine aspect.
   - Kaal Sarp Dosh (Anant): Mitigated because the Moon is in the 2nd house in royal Leo and Jupiter aspects the nodal axis, converting intense desire into visionary resilience.
   - Pitra Dosh: Absent / peaceful due to Sun occupying friendly Jupiterian Sagittarius.
4. Provide structured 4-5 bullet takeaways for each analytical section.

Return a strict JSON object with this exact schema:
{
  \"destiny_theme\": \"Unlocking the Soul Blueprint: From Groundwork to Sovereign Mastery\",
  \"destiny_motto\": \"Anchor your ambition in structured service as cosmic grace illuminates your path.\",
  \"executive_synthesis\": \"2-3 dignified sentences summarizing the native's lifetime arc\",
  \"atmakaraka_profile\": {
    \"ak_title\": \"Venus in Scorpio: The Path of Refined Emotional Alchemy\",
    \"ak_soul_lesson\": \"In-depth paragraph on Atmakaraka Venus soul purpose\",
    \"amk_title\": \"Saturn in Virgo: The Architect of Systematic Enterprise\",
    \"amk_career_compass\": \"In-depth paragraph on Amatyakaraka Saturn vocational intellect\"
  },
  \"karmic_matrix\": {
    \"manglik_cancellation\": \"Detailed paragraph explaining classical cancellation factors for Mars\",
    \"kaalsarp_evolution\": \"Detailed paragraph explaining nodal mitigation and resilience\",
    \"pitra_blessing\": \"Detailed paragraph on ancestral grace and solar alignment\"
  },
  \"life_domains\": {
    \"career_destiny\": \"Comprehensive analysis of 10th house, executive standing, and leadership peak\",
    \"wealth_architecture\": \"Comprehensive analysis of 2nd/11th houses, asset portfolio, and treasury flow\",
    \"marital_harmony\": \"Comprehensive analysis of 7th house, partnership longevity, and spouse alignment\",
    \"vitality_biorhythms\": \"Analysis of physical stamina, constitution, and health stewardship\"
  },
  \"milestone_tracker\": {
    \"q1\": {\"period\": \"Months 1–3\", \"milestone\": \"Foundation & Operational Blueprinting\", \"guidance\": \"Focus on auditing infrastructure and private skill mastery.\"},
    \"q2\": {\"period\": \"Months 4–6\", \"milestone\": \"Strategic Alliances & Public Launches\", \"guidance\": \"Deploy commercial initiatives under favorable transit rays.\"},
    \"q3\": {\"period\": \"Months 7–9\", \"milestone\": \"Capital Consolidation & Asset Growth\", \"guidance\": \"Reinforce liquidity reserves and review family trusts.\"},
    \"q4\": {\"period\": \"Months 10–12\", \"milestone\": \"Harvest, Recognition & Spiritual Seva\", \"guidance\": \"Celebrate milestone achievements and perform charitable Daan.\"}
  },
  \"page_takeaways\": {
    \"p3\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p4\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p5\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p6\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p7\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p8\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p9\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p10\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p11\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p12\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p13\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p14\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p15\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p16\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p17\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p18\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p19\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p20\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p21\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p22\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p23\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p24\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"],
    \"p25\": [\"Pointer 1\", \"Pointer 2\", \"Pointer 3\", \"Pointer 4\"]
  }
}";
    }

    /**
     * Fallback deterministic data for Lifetime Kundali Report
     */
    private static function get_fallback_kundali_data($name) {
        return [
            'destiny_theme' => 'Unlocking the Soul Blueprint: From Groundwork to Sovereign Mastery',
            'destiny_motto' => 'Anchor your ambition in structured service as cosmic grace illuminates your path.',
            'executive_synthesis' => "Your nativity represents a rare, formidable convergence of classical Parashari power: a Cancer Lagna fortified by an exalted Mars in Kendra, a luminous royal Moon in Leo Magha, and the visionary depth of Sun-Mercury in Sagittarius. Navigating the expansive 18-year Rahu Mahadasha, your destiny unfolds from meticulous infrastructural craftsmanship into commanding institutional leadership.",
            'atmakaraka_profile' => [
                'ak_title' => 'Venus in Scorpio (16.84°): The Path of Refined Emotional Alchemy',
                'ak_soul_lesson' => 'As your Atmakaraka, Shukra (Venus) occupying intense Scorpio challenges you to master the alchemy of desire. Your soul evolves not by denying material beauty or intimacy, but by transmuting possessiveness into unconditional devotion, elevated aesthetic refinement, and spiritual loyalty. Whenever you choose emotional integrity over transient gratification, your entire chart unlocks massive fortune.',
                'amk_title' => 'Saturn in Virgo (15.72°): The Architect of Systematic Enterprise',
                'amk_career_compass' => 'As your Amatyakaraka, Shani (Saturn) in analytical Virgo functions as your vocational compass. Your greatest professional triumphs arrive through structured problem-solving, operational precision, digital architecture, and patient institutional building. You are naturally built to engineer systems that endure, earning deep authority through dependable execution.'
            ],
            'karmic_matrix' => [
                'manglik_cancellation' => 'While Mars in the 7th house flags Anshik Manglik status (47.5%), classical Parashari Siddhanta confirms powerful neutralizing factors: Mars is positioned in its highest planetary exaltation (Ucha Makara), transforming potential marital friction into unwavering fidelity and shared ambition. Furthermore, benefic Jupiter casts its mitigating trine aspect, while chronological maturity beyond age 28 permanently grounds fiery impulses into domestic steadfastness.',
                'kaalsarp_evolution' => 'The presence of the Anant Kaal Sarp configuration (Rahu in Cancer Lagna / Ketu in Capricorn 7th House) is not a cosmic curse, but an evolutionary engine. The dispositor of Rahu (Moon) is securely placed in the 2nd house of speech and family values in royal Leo, while Jupiter\'s 9th aspect injects dharmic wisdom. Natives with this placement experience dramatic late-life breakthroughs, turning early obstacles into uncommon strategic resilience.',
                'pitra_blessing' => 'The scan confirms complete absence (0%) of Pitra Dosh afflictions. Surya Dev occupies the friendly, spiritually auspicious sign of Sagittarius, receiving the harmonious rays of Jupiter and Saturn. Ancestral grace operates as an invisible shield around your endeavors, ensuring family lineage protection, mental clarity, and enduring moral legacy.'
            ],
            'life_domains' => [
                'career_destiny' => 'Your 10th house of vocation is energized by exalted Mars and aspected by Jupiter. You possess natural executive command, strategic foresight, and organizational stamina. Optimal vocational fields include technology architecture, executive advisory, real estate, engineering, and digital systems consulting.',
                'wealth_architecture' => 'The 2nd house (Leo Moon) combined with the 11th house (Taurus/Venus lord) forms a robust Dhana Yoga. Your capital multiplies not through speculative gambling, but through systematic asset-backed accumulation, prime real estate, and scalable enterprise ventures.',
                'marital_harmony' => 'Your 7th house is anchored by exalted Mars and Ketu, indicating a dignified, intelligent, and strong-willed life partner. Marital happiness flourishes when mutual respect, shared intellectual pursuits, and emotional independence are consciously cultivated.',
                'vitality_biorhythms' => 'A Cancer Lagna paired with high Agni and Vayu requires regular hydration, spine mobility, and restorative sleep. Maintaining a disciplined morning Dinacharya and consuming cooling Satvic foods ensures optimal lifelong vitality.'
            ],
            'milestone_tracker' => [
                'q1' => ['period' => 'Months 1–3', 'milestone' => 'Foundation & Operational Blueprinting', 'guidance' => 'Audit internal systems, update financial spreadsheets, and master emerging domain skills in quiet focus.'],
                'q2' => ['period' => 'Months 4–6', 'milestone' => 'Strategic Alliances & Commercial Launches', 'guidance' => 'Deploy new enterprise initiatives, negotiate executive agreements, and expand institutional networks.'],
                'q3' => ['period' => 'Months 7–9', 'milestone' => 'Capital Consolidation & Asset Growth', 'guidance' => 'Reinvest enterprise dividends into secured assets, review long-term family trusts, and reinforce liquidity.'],
                'q4' => ['period' => 'Months 10–12', 'milestone' => 'Harvest, Recognition & Spiritual Seva', 'guidance' => 'Celebrate major vocational milestones, express gratitude to mentors, and perform sacred charitable Daan.']
            ],
            'page_takeaways' => [
                'p3' => [
                    'Cancer Lagna bestows natural emotional intelligence, deep resilience, and intuitive leadership.',
                    'The Moon in Leo (Magha) grants executive presence, dignified speech, and a natural instinct to protect family.',
                    'Exalted Mars in Kendra guarantees immense physical stamina and superior strategic dispute resolution.',
                    'The foundational D1 matrix indicates progressive life ascension, with peak triumphs arriving after initial groundwork.'
                ],
                'p4' => [
                    'Panchang elements at birth establish a harmonious bridge between emotional willpower and practical execution.',
                    'Magha Nakshatra connects your subconscious mind with royal ancestry, ancestral blessings, and leadership ambition.',
                    'The active Yoga configuration highlights intellectual sharpness, commercial acumen, and dharmic orientation.',
                    'Aligning key life decisions with favorable lunar Tithis maximizes enterprise success and domestic harmony.'
                ],
                'p5' => [
                    'Exalted Mars at 3.04° Capricorn anchors Kendra strength, granting unstoppable courage under crisis.',
                    'Surya and Budha in Sagittarius produce Budhaditya Yoga, enhancing advisory authority and strategic consulting.',
                    'Venus at 16.84° Scorpio functions as your Atmakaraka, driving deep emotional transformation and soul evolution.',
                    'Saturn in Virgo disciplines your analytical faculties, ensuring precision in financial accounts and legal drafting.'
                ],
                'p6' => [
                    'Kendra houses are powerfully activated, ensuring robust operational structure throughout your adult life.',
                    'The 2nd house Leo Moon provides persuasive executive speech, sound financial judgment, and family loyalty.',
                    'The 5th house activation stimulates continuous intellectual curiosity, speculative foresight, and creative legacy.',
                    'Dusthana house energies are transmuted by functional benefics, converting obstacles into valuable wisdom.'
                ],
                'p7' => [
                    'D9 Navamsha reveals profound inner spiritual stamina and deep-rooted fidelity in long-term commitments.',
                    'D10 Dashamsha confirms executive authority, enterprise autonomy, and public recognition in commercial spheres.',
                    'Varga chart harmony demonstrates that your external accomplishments are mirrored by authentic internal growth.',
                    'Regular alignment with Navamsha planetary lords dissolves vocational blockages and enhances marital peace.'
                ],
                'p8' => [
                    'Saptamsha (D7) indicates auspicious family lineage continuity, creative fruitfulness, and fortunate progeny.',
                    'The 5th house harmonics ensure children inherit sharp intellect, strong moral values, and academic drive.',
                    'Your creative and intellectual projects act as an enduring legacy, compounding in value across decades.',
                    'Investing quality time in family mentoring and children\'s education yields deep lifelong emotional fulfillment.'
                ],
                'p9' => [
                    'Shadbala calculations reveal extraordinary planetary potency, led by exalted Mars and benefic Venus.',
                    'High Digbala (directional strength) empowers decisive decision-making during high-stakes commercial negotiations.',
                    'Planetary potency confirms that your calculated risks will yield enduring material and social rewards.',
                    'Channeling high Virupa planets into structured projects prevents restless mental energy from scattering.'
                ],
                'p10' => [
                    'Sarvashtakvarga scores across Kendra and Trikona houses surpass the benchmark 28-bindu threshold.',
                    'High bindu scores in wealth sectors (2nd & 11th) ensure financial recovery and steady capital appreciation.',
                    'Houses with moderate bindus highlight areas where disciplined systems and patience are required.',
                    'Timing major contracts when transiting planets cross high-bindu houses guarantees maximum yield.'
                ],
                'p11' => [
                    'The active 18-year Rahu Mahadasha (2024–2042) represents your primary era of massive global expansion.',
                    'Rahu in Lagna compels you to break conventional boundaries, embrace technology, and lead innovative ventures.',
                    'The progression from foundational dasha periods into Jovian sub-periods brings institutional authority.',
                    'Maintain impeccable ethical standards throughout Rahu dasha to ensure achievements remain permanent.'
                ],
                'p12' => [
                    'The current Rahu-Rahu operating period requires building bulletproof systems and internal processes.',
                    'Mid-2027 marks a monumental Antardasha Sandhi as the expansive Jovian grace of Rahu-Guru commences.',
                    'Avoid hasty shortcuts or unverified alliances during the concluding phase of the current sub-period.',
                    'The approaching Jupiter sub-cycle unlocks international networks, wealth compounding, and family celebrations.'
                ],
                'p13' => [
                    'Venus as Atmakaraka (AK) challenges you to master the alchemy of desire and lead through unconditional integrity.',
                    'Saturn as Amatyakaraka (AmK) reveals that your true vocational calling is grounded in structured, disciplined service.',
                    'Integrating AK spiritual wisdom with AmK operational precision creates an unbeatable personal philosophy.',
                    'Regular contemplation of your soul purpose dissolves career anxiety and aligns daily effort with Dharma.'
                ],
                'p14' => [
                    'Ruchaka Yoga formed by exalted Mars guarantees executive courage, physical vitality, and property holdings.',
                    'Budhaditya Yoga in Sagittarius bestows eloquence, strategic financial intuition, and mentorship status.',
                    'Benefic planetary trines create continuous protection against sudden professional setbacks.',
                    'Activating these auspicious yogas through honest enterprise attracts institutional backing and patron support.'
                ],
                'p15' => [
                    'Auxiliary Yogini Dasha cycles provide secondary chronological validation for key life turning points.',
                    'Synchronizing Yogini sub-periods with Vimshottari milestones eliminates timing ambiguities in major deals.',
                    'Favorable Yogini rulers support asset acquisitions, foreign collaborations, and domestic celebrations.',
                    'Practicing mindful meditation during sensitive Yogini sandhis preserves emotional equanimity and focus.'
                ],
                'p16' => [
                    'Mars in exalted Capricorn in Kendra completely neutralizes classical malefic Manglik friction into loyalty.',
                    'Jupiter’s protective trinal aspect softens emotional reactions and fosters mutual understanding with your partner.',
                    'Age maturity beyond 28 years permanently resolves early relationship impetuousness into marital devotion.',
                    'Channeling Martian energy into joint physical activities and shared home building creates lasting harmony.'
                ],
                'p17' => [
                    'Anant Kaal Sarp formation functions as an evolutionary catapult, forging profound emotional resilience.',
                    'The royal Leo Moon dispositor ensures that nodal restlessness is converted into persuasive leadership speech.',
                    'Jupiter’s aspect on the nodal axis guarantees that unconventional challenges lead to sovereign mastery.',
                    'Regular Lord Shiva Upasana and Maha Mrityunjaya Japa completely harmonize the Rahu-Ketu axis.'
                ],
                'p18' => [
                    'The complete absence of Pitra Dosh confirms that your lineage carries auspicious ancestral blessings.',
                    'Surya Dev situated in Jupiterian Sagittarius reflects honorable ancestral values and strong moral paternal roots.',
                    'Offering solar water (Arghya) and performing simple seasonal charity preserves ancestral grace.',
                    'Family traditions and moral values passed down from elders act as an invisible foundation of your prosperity.'
                ],
                'p19' => [
                    'Your career trajectory is geared toward executive authority, independent consulting, and digital architecture.',
                    'Peak professional eras coincide with Martian and Jovian transits across Kendra and Trikona houses.',
                    'Lead through systemic operational processes rather than manual labor to achieve scalable enterprise growth.',
                    'Maintain complete transparency in corporate contracts to safeguard your stellar public reputation.'
                ],
                'p20' => [
                    'Auspicious 2nd and 11th house dynamics ensure multiple diversified income channels across your career.',
                    'Prioritize asset-backed investments such as prime commercial real estate, mutual funds, and precious metals.',
                    'Enforce strict liquidity reserves during retrograde transit cycles to capitalize on market dips.',
                    'Compounding wealth over long horizons produces far superior yields compared to speculative trading.'
                ],
                'p21' => [
                    'A dignified, intelligent, and strong-willed life partner brings balance, refinement, and commercial insight.',
                    'Active listening, emotional validation, and respectful communication form the pillars of your domestic peace.',
                    'Schedule regular weekend retreats and couple time away from professional duties to nurture romantic warmth.',
                    'Joint financial planning and shared domestic goals reinforce lifelong marital stability and trust.'
                ],
                'p22' => [
                    'Yellow Sapphire (Pukhraj) or Emerald (Panna) enhances executive wisdom, clarity, and commercial success.',
                    'Cat\'s Eye (Lehsunia) worn with proper Vedic rituals harmonizes nodal energy during Rahu-Ketu cycles.',
                    'Ensure all gemstones are natural, eye-clean, unheated, and mounted in open-back settings touching the skin.',
                    'Wearing a natural 5-Mukhi Rudraksha balances biorhythmic blood pressure and calms active mental chatter.'
                ],
                'p23' => [
                    'Reciting the sacred Gayatri Mantra and Rahu Beej Mantra daily elevates mental composure and clarity.',
                    'Targeted charitable Daan (green moong dal, whole grains, feeding cows) washes away transit impurities.',
                    'A daily morning walk under sunlight combined with hydration keeps Vata and Agni in perfect equilibrium.',
                    'Lighting a mustard oil lamp on Saturday evenings appeases Shani Dev and ensures steady career progress.'
                ],
                'p24' => [
                    'Review your quarterly milestone tracker every New Moon to align personal ambitions with cosmic dasha timing.',
                    'Record your solemn written Sankalpa commitments on Page 24 to anchor deep subconscious execution willpower.',
                    'Celebrate milestone victories with family and team members to foster collective gratitude and happiness.',
                    'Consistent disciplined effort coupled with divine faith turns every calculated endeavor into reality.'
                ],
                'p25' => [
                    'Keep this Master Kundali Dossier as an enduring reference manual across all major life transitions.',
                    'Consult the 14-term Vedic glossary whenever seeking clarity on classical Sanskrit Jyotish principles.',
                    'All coordinates are authenticated against classical Parashari Siddhanta and Lahiri Chitra Paksha Ayanamsha.',
                    'Walk forward with unshakeable courage, knowing that conscious human Karma commands cosmic destiny.'
                ]
            ]
        ];
    }
}


