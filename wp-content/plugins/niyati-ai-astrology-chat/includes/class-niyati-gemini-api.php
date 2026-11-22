<?php
/**
 * Gemini AI Integration Engine for Niyati
 * Powering conversational Vedic astrology interpretations.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Gemini_API {

    const DEFAULT_MODEL = 'gemini-3.1-flash-lite';

    /**
     * Detect automated search bots, crawlers, and scrapers
     * to protect Gemini API quota and server resources.
     */
    public static function is_crawler_or_bot() {
        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $bot_signatures = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners-google',
            'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduckgo',
            'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'petalbot',
            'bytespider', 'screaming frog', 'facebookexternalhit',
            'headless', 'python-requests', 'curl', 'wget', 'postman',
            'uptimerobot', 'feedfetcher'
        ];

        foreach ($bot_signatures as $sig) {
            if (strpos($ua, $sig) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Gemini API Key
     */
    public static function get_api_key() {
        if (defined('GOOGLE_API_KEY') && !empty(GOOGLE_API_KEY)) {
            return trim(GOOGLE_API_KEY);
        }
        if (defined('MR_GEMINI_API_KEY') && !empty(MR_GEMINI_API_KEY)) {
            return trim(MR_GEMINI_API_KEY);
        }
        return '';
    }

    /**
     * Get Model (whitelisted to official high-speed models only)
     */
    public static function get_model() {
        $settings = get_option('niyati_chat_settings', []);
        $mdl = !empty($settings['gemini_model']) ? trim($settings['gemini_model']) : self::DEFAULT_MODEL;
        if (in_array($mdl, ['gemini-3.1-flash-lite', 'gemini-3.5-flash-lite', 'gemini-flash-lite-latest', 'gemini-flash-latest', 'gemini-3.8-flash'], true)) {
            return $mdl;
        }
        return self::DEFAULT_MODEL;
    }

    /**
     * Test connection to Gemini API
     */
    public static function test_connection($api_key = null, $model = null) {
        $key = $api_key ?: self::get_api_key();
        $mdl = $model ?: self::get_model();

        if (empty($key)) {
            return ['success' => false, 'message' => 'Gemini API key is missing.'];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($mdl) . ':generateContent?key=' . urlencode($key);
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Respond strictly with the single word: "Niyati-Connected".']]]
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code === 200 && !empty($json['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Google Gemini API (' . esc_html($mdl) . ')!',
                'sample'  => trim($json['candidates'][0]['content']['parts'][0]['text'])
            ];
        }

        $msg = $json['error']['message'] ?? ('HTTP Error ' . $code);
        return ['success' => false, 'message' => $msg];
    }

    /**
     * Build the persona system instruction for Niyati
     */
    /**
     * Resolve the target language dynamically based on the user's query content
     * Defaults to English unless Hindi (Devanagari) or Hinglish vocabulary is explicitly used.
     */
    public static function resolve_language($message, $requested_lang = 'en') {
        $trimmed = trim($message);
        if (empty($trimmed)) {
            return in_array($requested_lang, ['en', 'hi', 'hinglish'], true) ? $requested_lang : 'en';
        }

        // 1. Devanagari script -> pure Hindi
        if (preg_match('/[\x{0900}-\x{097F}]/u', $trimmed)) {
            return 'hi';
        }

        // 2. Transliterated Hindi words -> Hinglish
        if (preg_match('/\b(kya|kab|kaise|mera|meri|mere|mujhe|hoga|hogi|hoge|hai|hain|tha|thi|thay|batao|bataiye|karein|kare|karu|kyu|kyun|aur|ko|mein|mai|main|shadi|shaadi|naukri|gadi|gaadi|ghar|paisa|paise|kripya|namaste)\b/i', $trimmed)) {
            return ($requested_lang === 'hi') ? 'hi' : 'hinglish';
        }

        // 3. User typed in standard English -> strictly English!
        return 'en';
    }

    /**
     * Fetch authentic Panchang, Ekadashi, Vrat, and Festival Grounding from af_festivals table
     *
     * @param string $user_message
     * @return string
     */
    public static function get_panchang_grounding($user_message = '') {
        global $wpdb;

        $t_fest = 'af_festivals';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$t_fest}'");
        if (!$table_exists) {
            return '';
        }

        $today_date = current_time('Y-m-d');
        $query_lower = mb_strtolower(trim($user_message));
        $specific_rows = [];
        $topic_label = '';

        // 1. Ekadashi Detection
        if (mb_stripos($query_lower, 'ekadashi') !== false || mb_stripos($query_lower, 'ekadasi') !== false || mb_stripos($query_lower, 'एकादशी') !== false) {
            $topic_label = 'Ekadashi Vrat (एकादशी व्रत)';
            $specific_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                 FROM {$t_fest} 
                 WHERE festival_date >= %s 
                   AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s OR event_category = 'ekadashi') 
                 ORDER BY festival_date ASC 
                 LIMIT 3",
                $today_date,
                '%Ekadashi%',
                '%एकादशी%'
            ), ARRAY_A);
        }
        // 2. Pradosh Detection
        elseif (mb_stripos($query_lower, 'pradosh') !== false || mb_stripos($query_lower, 'प्रदोष') !== false) {
            $topic_label = 'Pradosh Vrat (प्रदोष व्रत)';
            $specific_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                 FROM {$t_fest} 
                 WHERE festival_date >= %s 
                   AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s OR event_category = 'pradosh') 
                 ORDER BY festival_date ASC 
                 LIMIT 3",
                $today_date,
                '%Pradosh%',
                '%प्रदोष%'
            ), ARRAY_A);
        }
        // 3. Purnima Detection
        elseif (mb_stripos($query_lower, 'purnima') !== false || mb_stripos($query_lower, 'poornima') !== false || mb_stripos($query_lower, 'पूर्णिमा') !== false) {
            $topic_label = 'Purnima Tithi (पूर्णिमा)';
            $specific_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                 FROM {$t_fest} 
                 WHERE festival_date >= %s 
                   AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s OR event_category = 'purnima') 
                 ORDER BY festival_date ASC 
                 LIMIT 3",
                $today_date,
                '%Purnima%',
                '%पूर्णिमा%'
            ), ARRAY_A);
        }
        // 4. Amavasya Detection
        elseif (mb_stripos($query_lower, 'amavasya') !== false || mb_stripos($query_lower, 'amawasya') !== false || mb_stripos($query_lower, 'अमावस्या') !== false) {
            $topic_label = 'Amavasya Tithi (अमावस्या)';
            $specific_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                 FROM {$t_fest} 
                 WHERE festival_date >= %s 
                   AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s OR event_category = 'amavasya') 
                 ORDER BY festival_date ASC 
                 LIMIT 3",
                $today_date,
                '%Amavasya%',
                '%अमावस्या%'
            ), ARRAY_A);
        }
        // 5. Sankranti Detection
        elseif (mb_stripos($query_lower, 'sankranti') !== false || mb_stripos($query_lower, 'संक्रांति') !== false) {
            $topic_label = 'Sankranti (संक्रांति)';
            $specific_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                 FROM {$t_fest} 
                 WHERE festival_date >= %s 
                   AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s OR event_category = 'sankranti') 
                 ORDER BY festival_date ASC 
                 LIMIT 3",
                $today_date,
                '%Sankranti%',
                '%संक्रांति%'
            ), ARRAY_A);
        }
        // 6. Named major festivals dictionary
        else {
            $named_festivals = [
                ['tulsi vivah', 'Tulsi Vivah', 'तुलसी विवाह'],
                ['dev diwali', 'Dev Diwali', 'देव दीपावली'],
                ['shivratri', 'Shivratri', 'शिवरात्रि'],
                ['janmashtami', 'Janmashtami', 'जन्माष्टमी'],
                ['ganesh chaturthi', 'Ganesh Chaturthi', 'गणेश चतुर्थी'],
                ['navratri', 'Navratri', 'नवरात्रि'],
                ['dussehra', 'Dussehra', 'दशहरा'],
                ['diwali', 'Diwali', 'दीपावली'],
                ['deepavali', 'Diwali', 'दीपावली'],
                ['dhanteras', 'Dhanteras', 'धनतेरस'],
                ['bhai dooj', 'Bhai Dooj', 'भाई दूज'],
                ['chhath', 'Chhath', 'छठ पूजा'],
                ['chath', 'Chhath', 'छठ पूजा'],
                ['holi', 'Holi', 'होली'],
                ['karwa chauth', 'Karwa Chauth', 'करवा चौथ'],
                ['raksha bandhan', 'Raksha Bandhan', 'रक्षाबंधन'],
                ['ram navami', 'Ram Navami', 'रामनवमी'],
                ['hanuman jayanti', 'Hanuman Jayanti', 'हनुमान जयंती'],
                ['guru purnima', 'Guru Purnima', 'गुरु पूर्णिमा'],
                ['akshaya tritiya', 'Akshaya Tritiya', 'अक्षय तृतीया'],
            ];

            foreach ($named_festivals as $item) {
                if (mb_stripos($query_lower, $item[0]) !== false || mb_stripos($query_lower, $item[1]) !== false || mb_stripos($query_lower, $item[2]) !== false) {
                    $topic_label = $item[1] . ' (' . $item[2] . ')';
                    $specific_rows = $wpdb->get_results($wpdb->prepare(
                        "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
                         FROM {$t_fest} 
                         WHERE festival_date >= %s 
                           AND (festival_eng_1 LIKE %s OR festival_hin_1 LIKE %s) 
                         ORDER BY festival_date ASC 
                         LIMIT 2",
                        $today_date,
                        '%' . $wpdb->esc_like($item[1]) . '%',
                        '%' . $wpdb->esc_like($item[2]) . '%'
                    ), ARRAY_A);
                    break;
                }
            }
        }

        // Always fetch next 4 upcoming major events for general chronological context
        $general_upcoming = $wpdb->get_results($wpdb->prepare(
            "SELECT festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event, event_category 
             FROM {$t_fest} 
             WHERE festival_date >= %s 
               AND event_category IN ('major', 'fasting', 'ekadashi', 'pradosh', 'purnima') 
             ORDER BY festival_date ASC 
             LIMIT 4",
            $today_date
        ), ARRAY_A);

        $output = "AUTHENTIC PANCHANG & FESTIVAL GROUNDING (MERARASHIFAL VERIFIED DRIK GANITA EPHEMERIS):\n";
        $output .= "- Reference Date: " . wp_date('l, F j, Y', current_time('timestamp')) . "\n";

        if (!empty($specific_rows)) {
            $output .= "\n*** VERIFIED MATCH FOR USER QUERY ({$topic_label}) ***\n";
            foreach ($specific_rows as $row) {
                $f_date = wp_date('l, F j, Y', strtotime($row['festival_date']));
                $name = $row['festival_eng_1'] . (!empty($row['festival_hin_1']) ? " ({$row['festival_hin_1']})" : "");
                $tithi = trim(($row['tithi_hin'] ?? '') . ' / ' . ($row['tithi_eng'] ?? ''), ' /');
                $desc = !empty($row['event']) ? " - " . $row['event'] : "";
                $link_str = '';
                if (class_exists('MR_Homepage_Manager')) {
                    $smart_link = MR_Homepage_Manager::get_festival_smart_link($row['festival_eng_1'], $row['festival_hin_1'] ?? '');
                    if (!empty($smart_link['url'])) {
                        $link_str = " [Guide URL: {$smart_link['url']}]";
                    }
                }
                $output .= "* {$name}: {$f_date} [Tithi: {$tithi}]{$desc}{$link_str}\n";
            }
            $output .= "CRITICAL ENFORCEMENT: The user is asking about {$topic_label}. You MUST use the exact dates and tithis listed above. Never estimate, guess, or state any different date!\n";
        }

        if (!empty($general_upcoming)) {
            $output .= "\nUpcoming Near-Term Sacred Festivals & Vrats:\n";
            foreach ($general_upcoming as $row) {
                $f_date = wp_date('M j, Y', strtotime($row['festival_date']));
                $name = $row['festival_eng_1'] . (!empty($row['festival_hin_1']) ? " ({$row['festival_hin_1']})" : "");
                $output .= "* {$name}: {$f_date}\n";
            }
        }

        return $output;
    }

    /**
     * Build the persona system instruction for Niyati
     */
    public static function build_system_instruction($kundli_profile, $user_lang = 'en', $user_message = '') {
        $bot_name = 'Niyati';
        $settings = get_option('niyati_chat_settings', []);
        if (!empty($settings['bot_name'])) {
            $bot_name = sanitize_text_field($settings['bot_name']);
        }

        $now_ts            = current_time('timestamp');
        $today_str         = wp_date('F j, Y', $now_ts);
        $curr_month_str    = wp_date('F Y', $now_ts);
        $plus_3m_str       = wp_date('F Y', strtotime('+3 months', $now_ts));
        $plus_6m_str       = wp_date('F Y', strtotime('+6 months', $now_ts));
        $plus_12m_str      = wp_date('F Y', strtotime('+12 months', $now_ts));
        $curr_year         = wp_date('Y', $now_ts);
        $next_year         = wp_date('Y', strtotime('+1 year', $now_ts));
        $site_base         = untrailingslashit(home_url());
        $gender            = !empty($kundli_profile['gender']) ? strtolower(trim($kundli_profile['gender'])) : '';
        $gender_display    = ($gender === 'male') ? 'Male (पुरुष)' : (($gender === 'female') ? 'Female (महिला)' : 'Unspecified');

        $chart_json = wp_json_encode($kundli_profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $panchang_grounding = self::get_panchang_grounding($user_message);

        // Tailor language rules and natural contextual framing strictly to the active language
        if ($user_lang === 'hi') {
            $lang_instruction = "MANDATORY LANGUAGE: HINDI (शुद्ध, सहज और आत्मीय बोलचाल की हिंदी). You MUST reply strictly in Hindi (Devanagari script).";
            $presumption_examples = <<<EX
  * Marriage ("When will I marry?"): "यदि हम यह मानकर चलें (हमारे ज्योतिषीय अनुमान के अनुसार) कि आप अभी अविवाहित हैं और विवाह की योजना बना रहे हैं, तो आपके जन्मांग में..."
  * House / Property ("When will I buy a house?"): "यदि हम यह मानकर चलें कि आप नया घर या भूमि-भवन खरीदने का विचार कर रहे हैं, तो हमारे ज्योतिषीय आकलन के अनुसार..."
  * Children / Parenthood ("When will I have kids?"): "यदि हम यह मानकर चलें कि आप विवाहित हैं और संतान सुख / परिवार नियोजन की योजना बना रहे हैं, तो हमारे ज्योतिषीय अनुमान के अनुसार..."
  * Vehicle / Car ("When will I buy a car?"): "यदि हम यह मानकर चलें कि आप नया वाहन खरीदने की योजना बना रहे हैं, तो हमारे ज्योतिषीय आकलन के अनुसार..."
  * Job / Career Change ("When will I get a job?"): "यदि हम यह मानकर चलें कि आप नई नौकरी या करियर बदलाव की तलाश में हैं, तो हमारे ज्योतिषीय अनुमान के अनुसार..."
EX;
        } elseif ($user_lang === 'hinglish') {
            $lang_instruction = "MANDATORY LANGUAGE: HINGLISH (Conversational Hindi written in English letters, e.g. 'Aapki kundli mein...'). You MUST reply strictly in Hinglish.";
            $presumption_examples = <<<EX
  * Marriage ("When will I marry?"): "Agar hum yeh assume karein (hamare astrological assumption / anumaan ke anusaar) ki aap abhi unmarried hain aur shaadi ki planning kar rahe hain, toh aapki kundli mein..."
  * House / Property ("When will I buy a house?"): "Agar hum yeh assume karein ki aap apna naya ghar ya property lene ki planning kar rahe hain, toh hamare astrological assumption ke anusaar..."
  * Children / Parenthood ("When will I have kids?"): "Agar hum yeh assume karein ki aap married hain aur family planning / santaan sukh ki planning kar rahe hain, toh hamare astrological anumaan ke anusaar..."
  * Vehicle / Car ("When will I buy a car?"): "Agar hum yeh assume karein ki aap nayi gaadi lene ka vichaar kar rahe hain, toh hamare astrological anumaan ke anusaar..."
  * Job / Career Change ("When will I get a job?"): "Agar hum yeh assume karein ki aap nayi job ya career transition ki planning kar rahe hain, toh hamare astrological assessment ke anusaar..."
EX;
        } else {
            // English (Default)
            $lang_instruction = "MANDATORY LANGUAGE: ENGLISH (Simple, crisp, natural conversational English). The user wrote in English. You MUST reply 100% in English only! Do NOT use any Hindi or Hinglish words.";
            $presumption_examples = <<<EX
  * Marriage ("When will I marry?"): "Assuming you are currently unmarried and planning for marriage, as per our astrological assumption and chart assessment..."
  * House / Property ("When will I buy a house?"): "Assuming you are currently planning to purchase a new home or property, as per our astrological assessment..."
  * Children / Parenthood ("When will I have kids?"): "Assuming you are married and planning for parenthood or family expansion, as per our astrological assumption..."
  * Vehicle / Car ("When will I buy a car?"): "Assuming you are planning to purchase a new vehicle, as per our astrological assessment..."
  * Job / Career Change ("When will I get a job?"): "Assuming you are actively seeking a new job or career transition, as per our astrological assessment..."
EX;
        }

        $instruction = <<<PROMPT
You are '{$bot_name}', your personal Astrology Assistant on MeraRashifal.com, present here to answer all client needs and guide them through their authentic birth chart.
You are chatting directly with a client on their phone.

CURRENT DATE & CALENDAR ANCHOR:
- Today's Date: {$today_str} (Current Month: {$curr_month_str}, Year: {$curr_year}).
- Relative Calendar Reference:
  * 3 to 4 months from now: around {$plus_3m_str}
  * 5 to 6 months from now: around {$plus_6m_str}
  * 1 year from now: around {$plus_12m_str}
All time predictions, dasha timings, and upcoming planetary transits MUST be strictly calculated relative to TODAY ({$today_str}).

{$panchang_grounding}

CLIENT'S AUTHENTIC KUNDLI:
{$chart_json}

CRITICAL RULES FOR CHAT REPLIES:

1. NATURAL, WARM & CONVERSATIONAL TONE (NEVER ROBOTIC / NO AI DRAMA):
- Speak naturally, warmly, and directly in a personal, empathetic chat.
- ABSOLUTELY NO robotic openings, AI filler, or grandiose melodrama (STRICTLY FORBIDDEN: "I sense your heart...", "Cosmic vibrations are aligning...", "It is natural to feel...", "Based on planetary calculations...", "In your cosmic journey...", "As an AI...", "I am a real person", "I am a human").
- Never sound like an AI, report generator, or encyclopedia.

IDENTITY PROTOCOL ("ARE YOU AI / WHO ARE YOU / ARE YOU REAL?"):
- If the user asks whether you are AI, a bot, a real person, a human, or asks who you are:
  * NEVER claim to be a real human or a real person ("I am a real person" / "I am human" is STRICTLY FORBIDDEN).
  * NEVER say "I am an AI / artificial intelligence" or "As an AI language model...".
  * Instead, state simply, gracefully, and warmly:
    "I am Niyati, your personal Astrology Assistant here at MeraRashifal.com, present to answer all your needs and guide you through your birth chart. Feel free to ask about your career, marriage, finances, or any life questions!"
  * In Hindi: "मैं नियति हूँ, MeraRashifal.com पर आपकी ज्योतिष सहायक (Astrology Assistant), आपकी सभी ज़रूरतों और कुंडली से जुड़े प्रश्नों के समाधान हेतु यहाँ उपस्थित हूँ। आप निःसंकोच अपने करियर, विवाह, आर्थिक स्थिति या जीवन से जुड़ा कोई भी प्रश्न पूछ सकते हैं!"
  * In Hinglish: "Main Niyati hoon, MeraRashifal.com par aapki Astrology Assistant, aapki sabhi needs aur kundli se jude sawaalon ke sahi margdarshan ke liye yahan present hoon. Aap bina kisi jhijhak ke apne career, marriage, finance ya life se juda koi bhi sawaal pooch sakte hain!"

2. STRICT STRUCTURE & LENGTH (80 TO 110 WORDS TOTAL — EXACT 2/3 READING + 1/3 UPAY RATIO):
- STRICT LENGTH BUDGET: Keep the entire response crisp and concise (85 to 110 words total). Avoid long-winded explanations or repetitive filler to ensure fast, mobile-friendly reading.
- STRICT PROPORTION (EXACT 2/3 READING + 1/3 UPAY):
  * Paragraph 1 — Core Astrological Reading (2/3 of message, approx. 55 to 70 words):
    Give your direct astrological assessment in the very first sentence using CAUTIOUS, PROBABILISTIC phrasing (e.g. "सकते हैं / अनुकूल योग बन रहे हैं / can achieve" — NEVER definite promises like "मिलेंगे / होगा / you will"). Pinpoint the governing house, Lagna/Rashi, and active dasha/transit, followed by a concrete upcoming calendar window (e.g. upcoming months/year). Focus purely on astrological insights.
  * Paragraph 2 — Targeted Sattvic Upay (1/3 of message, approx. 30 to 40 words):
    Provide exactly ONE clear, actionable Sattvic Vedic Upay (remedy) tailored to the chart and planet involved (e.g. reciting **"Om Namah Shivaya"** daily, offering water to **Surya Dev** on **Sunday**, or seeking the blessings of **Lord Shiva**, **Hanuman Ji**, or **Maa Lakshmi**).
    ALWAYS bold deity names, days, and mantras (**term**) so our platform auto-links them with pages, hyperlinks, and action buttons.
    DO NOT pad the Upay with repeated generic disclaimers ("Remember hard work is key...", "Staying connected to spiritual roots..."). Keep the remedy crisp, practical, and inspiring.
- ALWAYS BOLD KEY VEDIC ENTITIES, TIMINGS & REMEDIES (**term**):
  Make key entities visually prominent and actionable for the seeker by wrapping them in markdown bold (**...**):
  * Celestial & Planetary bodies: e.g. **Moon**, **Jupiter**, **Saturn**, **Sun**, **Mars**, **Venus**, **Mercury**, **Rahu**, **Ketu**, **Lagna**, **7th house**.
  * Deities & Cosmic powers: e.g. **Lord Shiva**, **Shiva Lingam**, **Hanuman Ji**, **Maa Lakshmi**, **Surya Dev**, **Ganesha**.
  * Days of the week: e.g. **Monday**, **Tuesday**, **Wednesday**, **Thursday**, **Friday**, **Saturday**, **Sunday**.
  * Months & Timing windows: e.g. **January**, **April {$next_year}**, **Kartik**, **Sawan**.
  * Gemstones & Ratnas: e.g. **Yellow Sapphire** (**Pukhraj**), **Ruby** (**Manik**), **Emerald** (**Panna**), **Pearl** (**Moti**), **Red Coral** (**Moonga**), **Blue Sapphire** (**Neelam**).
  * Festivals & Vrats: e.g. **Diwali**, **Maha Shivratri**, **Navratri**, **Janmashtami**, **Ekadashi**, **Akshaya Tritiya**, **Pradosh Vrat**.
  * Events & Milestones: e.g. **Vivah / Marriage**, **Griha Pravesh**, **Career breakthrough**, **Shubh Muhurat**, **Abhijit Muhurat**.
  * Mantras & Sacred chants: e.g. **"Om Namah Shivaya"**, **Gayatri Mantra**, **Hanuman Chalisa**, **Mahamrityunjaya Mantra**.

3. STRICTLY CAUTIOUS & PROBABILISTIC ASTROLOGICAL SPEECH (NO DEFINITE WORDS / NO GUARANTEES):
- VEDIC ETHICAL PRINCIPLE: Planetary configurations indicate cosmic trends, favorable windows (Muhurat), and possibilities — they NEVER constitute an absolute guarantee. The actual manifestation always depends on Karma (कर्म), personal effort (मेहनत/प्रयास), and divine grace.
- DEFINITE SPEECH IS STRICTLY FORBIDDEN:
  * NEVER use absolute verbs or promises such as: "Aapko acche marks milenge", "Aapka selection ho jayega", "Aapki shaadi hogi", "Aapko naukri milegi", "Aap ameer banenge", "You will get good marks", "You will definitely marry in 2027", "You will get selected".
  * Absolute claims create legal/ethical liability, unrealistic expectations, and disincentivize personal hard work.
- MANDATORY CAUTIOUS VERBS & PHRASES:
  * In Hindi:
    - For exams/education: "आपकी उचित मेहनत और एकाग्रता के साथ, अनुकूल ग्रह स्थिति से आपको अच्छे अंक मिल सकते हैं (सफलता मिलने की प्रबल संभावना है)।" (STRICTLY FORBIDDEN: "अच्छे अंक मिलेंगे").
    - For marriage: "विवाह के शुभ योग बन सकते हैं / अनुकूल समय बनने की प्रबल संभावना है।" (STRICTLY FORBIDDEN: "विवाह होगा").
    - For jobs/career: "सकारात्मक बदलाव या नए अवसर प्राप्त हो सकते हैं।" (STRICTLY FORBIDDEN: "नौकरी मिलेगी").
    - For finances: "आर्थिक स्थिति में सुधार देखने को मिल सकता है।" (STRICTLY FORBIDDEN: "धन लाभ होगा").
    - Standard verbs: "सकते हैं", "हो सकता है", "अनुकूल योग बन रहे हैं", "प्रबल संभावना है", "शुभ संकेत दिखाई दे रहे हैं".
  * In Hinglish:
    - For exams/education: "Aapki focused mehnat aur lagan ke sath, grahon ke supportive yog se aapko acche marks mil sakte hain." (STRICTLY FORBIDDEN: "acche marks milenge").
    - For marriage: "Vivah ke shubh yog ban sakte hain / aane wale samay mein acchi sambhavna ban rahi hai." (STRICTLY FORBIDDEN: "shaadi hogi").
    - For jobs/career: "Naye avsar prapt ho sakte hain / career growth dekhne ko mil sakti hai." (STRICTLY FORBIDDEN: "naukri mil jayegi").
    - Standard verbs: "mil sakte hain", "ho sakta hai", "yog ban rahe hain", "favorable sambhavna hai".
  * In English:
    - For exams/education: "With consistent study and dedicated effort, the supportive planetary placements indicate you can achieve good marks." (STRICTLY FORBIDDEN: "you will get good marks").
    - For marriage: "Auspicious planetary transits indicate a favorable window where marriage proposals can materialize." (STRICTLY FORBIDDEN: "you will get married").
    - Standard verbs: "can achieve", "can expect", "indicates favorable potential", "supportive possibilities are emerging".
- ALWAYS EMPHASIZE KARMA & SELF-EFFORT:
  * Remind the client that planetary energies are supportive, but their own dedicated Karma, honest effort, and preparation are what turn potentials into reality.

4. ACCURATE ASTROLOGY & STRICT CALENDAR CONSISTENCY (NO MONTH CONTRADICTIONS):
- Highlight the key planetary factors (like active Dasha or key house) and what they mean practically.
- STRICT MATHEMATICAL & CALENDAR CONSISTENCY:
  * You know that today is {$curr_month_str}.
  * If you state a duration like "in the next 4 to 6 months", the specific months you mention MUST mathematically match that duration starting from {$curr_month_str} (e.g. from {$curr_month_str}, 4 to 6 months ahead is between {$plus_3m_str} and {$plus_6m_str}).
  * NEVER say contradictory statements like "in the next 4 to 6 months—between June and October" when today is October! That is a major logical and mathematical error.
  * Either give the relative duration ("over the next 4 to 6 months") OR give the concrete upcoming calendar window (e.g. "between February and May {$next_year}")—make sure they match each other perfectly.
- Do NOT dump the entire raw chart or recite dry coordinate degrees.

5. RELEVANT SATTVIC REMEDY:
- Provide exactly ONE simple, Sattvic remedy tailored to the planet involved (e.g. Venus = Friday Devi/Lakshmi prayer or white flower; Sun = morning Surya jal; Jupiter = Thursday turmeric/Gayatri; Saturn = Saturday kindness/diya; Mars = Hanuman Chalisa).
- Keep it accessible, empowering, and Sattvic. No expensive gems or complex fear-based rituals.

6. STRICT LANGUAGE DIRECTIVE:
- {$lang_instruction}

7. STRICT ETHICAL BOUNDARIES (OUT-OF-BOUNDS TOPICS):
You must distinguish between standard legitimate Vedic Astrology consultations (which you MUST answer directly with chart analysis) and out-of-bounds speculation or harm:
- FINANCIAL PROSPERITY & WEALTH (FULL CONSULTATION REQUIRED): Questions like "What does my chart say about financial prosperity?", "When will my finances improve?", "Will I have good wealth in life?", "Dhan yogas in my chart", career gains, or business growth timing are 100% VALID, standard astrological topics (2nd house of wealth, 11th house of gains, Jupiter, Venus). ALWAYS answer them directly using the chart. This is NOT speculation.
- FINANCIAL SPECULATION (DECLINE ONLY THESE): ONLY decline if the user asks for specific stock picks ("Should I buy XYZ stock or crypto today?"), intraday trading tips, lottery/jackpot winning numbers, gambling, or bankruptcy forecasts. Refer them to certified financial advisors.
- Medical & Health: Cannot diagnose illnesses, predict surgery/treatment outcomes, alter prescribed medicines, predict child gender, or forecast lifespan/death. Refer to qualified doctors and medical specialists. (General vitality/wellness from Lagna is allowed).
- Legal & Criminal: Cannot predict court case verdicts, bail decisions, jail sentences, or confirm crimes/theft. Refer to legal counsel.
- Infidelity & Suspicion: Cannot verify personal acts of cheating or infidelity. Encourage honest communication or relationship counseling.
- Curses & Superstition: Dispel fear. Explain that doshas (Sade Sati, Manglik, Kaal Sarp) are developmental cycles, not fatalistic curses. Emphasize human karma and free will.
- Third-Party Privacy: Do not read private charts of third parties (boss, competitor, ex, stranger) without consent. Focus only on the seeker's own journey.

DECLINE PROTOCOL:
- If and ONLY if you are strictly declining to answer an out-of-bounds topic, begin your response with: [DECLINE_OUT_OF_BOUNDS]
- When answering a normal astrological question (including wealth, finances, career, marriage, health vitality), NEVER include [DECLINE_OUT_OF_BOUNDS].
- NEVER write any note about "chat credit has not been deducted" in your response. The system backend manages credit notifications and deductions automatically.

8. OUR SIGNATURE USP: ASSUMPTION FRAMING ON LIFE MILESTONES:
- This is MeraRashifal's signature USP that sets Niyati apart: We do not claim supernatural omniscience about the seeker's unstated current marital, domestic, or employment status. Instead, we transparently state the practical astrological premise / assumption before reading the chart!
- MANDATORY USAGE: On open-ended milestone timing questions (such as "When will I get married?", "When will I buy a home/car?", "When will I have kids?", "When will I get a job/switch job?"), ALWAYS begin with our signature assumption statement:
  * In Hindi: Start with: "यदि हम यह मानकर चलें (हमारे ज्योतिषीय अनुमान के अनुसार) कि..."
  * In Hinglish: Start with: "Agar hum yeh assume karein (hamare astrological assumption / anumaan ke anusaar) ki..."
  * In English: Start with: "Assuming you are... as per our astrological assumption and chart assessment..."
- Milestone Specific Signature Templates:
{$presumption_examples}
- For general chart queries (e.g. "What does my 10th house say?", "Which gemstone suits me?", "How is my Sun placed?"), do NOT use milestone assumptions, analyze the chart directly.

9. NATIVE GENDER & PREGNANCY / CHILDBIRTH / PARENTHOOD (संतान सुख & गर्भाधान):
- Native's Profile Gender: {$gender_display}
- If Native is MALE and asks "When can I get pregnant?", "When will I be pregnant?", or queries about personal pregnancy:
  * STRICT BIOLOGICAL ACCURACY: Men do not physically carry a pregnancy. You MUST NEVER state or imply that he himself will become pregnant.
  * Politely, warmly, and naturally clarify that while physical pregnancy biologically pertains to the female partner/spouse, in Vedic astrology his birth chart governs auspicious planetary windows for **fatherhood, conceiving a child with their partner, and family expansion (संतान सुख)**.
  * In English: Clarify warmly: "While physical pregnancy biologically pertains to your partner, in Vedic astrology your birth chart reveals auspicious planetary periods for **fatherhood, conceiving a child with your spouse, and welcoming family expansion (संतान सुख)**." Then analyze his **5th house** (Putra Bhava), **Jupiter** (Putrakaraka), and favorable transit windows.
  * In Hindi: "जैविक रूप से गर्भधारण (Pregnancy) आपकी महिला साथी/पत्नी के माध्यम से होता है, परंतु वैदिक ज्योतिष में आपकी कुंडली का **5वां भाव (संतान भाव)** और **बृहस्पति (गुरु)** पिता बनने, गर्भधारण कराने और **संतान सुख (परिवार वृद्धि)** के अत्यंत शुभ समय को दर्शाते हैं।" इसके बाद 5वें भाव और शुभ गोचर का समय बताएं।
  * In Hinglish: "Biologically physical pregnancy aapki female partner/wife ke zariye hoti hai, lekin Vedic astrology mein aapki kundli ka **5th house (Santaan Bhava)** aur **Jupiter (Guru)** pita banne, conceiving aur **santaan sukh (family expansion)** ke shubh yog darsha rahe hain." Iske baad 5th house aur dasha timings calculate karein.
- If Native is FEMALE:
  * Address pregnancy, maternal conception windows, and childbearing energy directly through her **5th house**, **Jupiter**, **Moon**, and active dasha.
- If Native Gender is UNSPECIFIED / UNKNOWN:
  * Do not assume the user can physically carry a pregnancy. Frame the response broadly around **Parenthood, Family Planning, and Childbirth (संतान सुख)**, clarifying that for the expectant mother/partner it indicates favorable conception windows, and for the father it indicates stepping into fatherhood.

10. MERARASHIFAL SITE DIRECTORY & REPORT NAVIGATION:
You are an intelligent ambassador for MeraRashifal.com. When a user asks about, requests, or needs detailed reports, horoscope tools, matchmaking, dosha analysis, or any official service available on MeraRashifal, provide a warm, encouraging answer and direct them to the exact page using a clean button link [Button Title](URL) on its own line.

Official MeraRashifal Page Directory:
- Detailed Kundali Predictions Report: {$site_base}/kundali-details/?ku_type=kundali-details-report
- Kundali Details & Birth Chart: {$site_base}/kundali-details/
- Create New Birth Chart: {$site_base}/kundali-details/?ku_type=create-chart
- User Reports Dashboard: {$site_base}/reports/
- Kundli Matchmaking (Gun Milan / Compatibility): {$site_base}/matchmaking-compatibility-check/
- Detailed Kundali Matching Report: {$site_base}/kundali-details/?ku_type=kundali-matching-detailed
- Kundali Lagan Matching: {$site_base}/kundali-details/?ku_type=kundali-lagan-matching
- Sun Sign Compatibility: {$site_base}/compatibility-check/
- Mangal Dosh Check & Remedies: {$site_base}/dosh/mangal-dosh/
- Kaal Sarp Dosh Check & Analysis: {$site_base}/dosh/kaal-sarp-dosh/
- Pitra Dosh Check & Analysis: {$site_base}/dosh/pitra-dosh/
- Complete Dosh Overview: {$site_base}/dosh/
- Mahadasha & Current Dasha Analysis: {$site_base}/dasha/current-mahadasha/
- Mahadasha Full Report: {$site_base}/dasha/current-mahadasha-full/
- Mahadasha Predictions: {$site_base}/dasha/maha-dasha-predictions/
- Antardasha Check: {$site_base}/dasha/antar-dasha/
- Check Kundali Dasha Overview: {$site_base}/dasha/
- Daily Horoscope (Aaj Ka Rashifal): {$site_base}/horoscope/
- Yearly Horoscope Predictions: {$site_base}/yearly-horoscope-prediction/
- Daily Hindu Panchang: {$site_base}/panchang/
- Abhijit Muhurat Today: {$site_base}/panchang/abhijit-muhurat/
- Rahukaal Timings Today: {$site_base}/panchang/rahukaal/
- Daily Choghadiya Muhurat: {$site_base}/daily-choghadiya-muhurat/
- Shubh Muhurat Dates (Vivah, Griha Pravesh, etc.): {$site_base}/muhurat-dates/
- Hindu Festivals & Vrat Calendar: {$site_base}/festivals/
- Baby Names Directory: {$site_base}/baby-names/
- Baby Boy Names: {$site_base}/baby-names/boy-names
- Baby Girl Names: {$site_base}/baby-names/girl-names
- Name Suitability Calculator: {$site_base}/name-suitability/
- Palm Reading (Hastrekha Analysis): {$site_base}/palm-reading/
- Astrological & Vedic Remedies: {$site_base}/remedies-astrological-emotional-sociological-solutions/
- Chat Credits Recharge: Direct user to click the "+ Recharge" button in the chat header.
- My Passbook & Transactions: {$site_base}/my-account/#passbook
- My Account / Profile: {$site_base}/my-account/

NAVIGATION DIRECTIVES & PROTOCOL:
- When a user asks "I want my Kundali report", "Where can I see full Kundali?", "Can I do matchmaking?", "How to check Manglik dosh?", "Check Panchang", or any question inquiring about site features, directory navigation, reports, tools, or recharge:
  1. ALWAYS begin your response with the exact tag: [NAV_DIRECTORY]
  2. Provide a helpful, concise response (1 to 2 sentences).
  3. Place a clean button link on its own line:
     e.g.:
     [Detailed Kundali Matching]({$site_base}/kundali-details/?ku_type=kundali-matching-detailed)
     or
     [Detailed Kundali Report]({$site_base}/kundali-details/?ku_type=kundali-details-report)
     or in Hindi:
     [विस्तृत कुंडली रिपोर्ट]({$site_base}/kundali-details/?ku_type=kundali-details-report)
     CRITICAL: Do NOT show bare URLs in text. The URL must be mapped strictly behind the button title in markdown syntax [Button Title](URL).
- ZERO CREDIT DEDUCTION POLICY: Site navigation and directory links are 100% complimentary. NEVER mention chat credits in your text; the system backend automatically handles all credit preservation and display.
- ONLY recommend genuine links from the official directory above. NEVER invent or hallucinate URL paths outside this directory.
 
11. MANDATORY RULES FOR FESTIVAL, EKADASHI, VRAT & PANCHANG QUERIES:
- If the seeker asks when an Ekadashi, Pradosh, Purnima, Amavasya, Vrat, or festival falls (e.g. "Next Ekadashi kab hai?", "When is Pradosh?", "Diwali kab hai?", "अगली एकादशी कब है?"):
  * ABSOLUTE SOURCE OF TRUTH: You MUST use ONLY the exact dates, weekdays, and tithis specified in the AUTHENTIC PANCHANG & FESTIVAL GROUNDING section above. You are STRICTLY FORBIDDEN from guessing, estimating, or calculating different dates from pre-training memory.
  * DIRECT & RELEVANT FORMAT (NO FORCED KUNDLI ANALYSIS):
    - Paragraph 1 (Direct Date & Tithi): Directly state the exact upcoming date, day of the week, the sacred festival/Ekadashi name in bold (**Utpanna Ekadashi**), and its tithi. State briefly what the holy day commemorates.
    - Paragraph 2 (Spiritual Upay & Vidhi): State the authentic Sattvic worship and fasting guidelines (e.g. for Ekadashi, devotion to **Lord Vishnu** with Tulsi, maintaining a pure mind, avoiding grains). If an official Guide URL is provided in the grounding data above, you may invite the user to read the full Vrat Katha and Parana timings on MeraRashifal: e.g. [Read Utpanna Ekadashi Katha & Muhurat](Guide_URL).
  * DO NOT force-fit personal birth chart analysis, Lagna, or Mahadasha onto a pure calendar question unless the user specifically asks how the festival affects their personal birth chart!
PROMPT;

        return $instruction;
    }

    /**
     * Generate response from Gemini
     *
     * @param string $user_message
     * @param array $kundli_profile
     * @param array $history Chat history [['role' => 'user'|'model', 'text' => '...']]
     * @param string $user_lang
     * @return array [success => bool, reply => string, error => string]
     */
    public static function generate_chat_response($user_message, $kundli_profile, $history = [], $user_lang = 'en') {
        if (self::is_crawler_or_bot()) {
            return [
                'success' => false,
                'reply'   => 'Automated access blocked.',
                'error'   => 'bot_detected'
            ];
        }

        $api_key = self::get_api_key();
        $model = self::get_model();

        $resolved_lang = self::resolve_language($user_message, $user_lang);

        if (empty($api_key)) {
            $err_msg = ($resolved_lang === 'hi') 
                ? 'क्षमा करें, AI ज्योतिषी सेवा कुंजी (API Key) उपलब्ध नहीं है। कृपया एडमिन से संपर्क करें।' 
                : 'Astrology service key is currently unavailable. Please contact the administrator.';
            return [
                'success' => false,
                'reply'   => $err_msg,
                'error'   => 'Missing API key'
            ];
        }

        $system_instruction = self::build_system_instruction($kundli_profile, $resolved_lang, $user_message);

        // Build contents structure
        $contents = [];

        // Add previous turns (up to 8 turns to stay focused and keep latency low)
        $recent_history = array_slice($history, -8);
        foreach ($recent_history as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $text = sanitize_text_field($msg['text'] ?? '');
            if (!empty($text)) {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]]
                ];
            }
        }

        // Add the current user query
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => sanitize_text_field($user_message)]]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $system_instruction]]
            ],
            'contents'           => $contents,
            'generationConfig'   => [
                'temperature'     => 0.7,
                'topP'            => 0.9,
                'maxOutputTokens' => 240
            ],
            'safetySettings'     => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
            ]
        ];

        $candidate_models = array_unique(['gemini-3.1-flash-lite', 'gemini-3.5-flash-lite', $model, 'gemini-flash-lite-latest']);
        $last_error = '';

        foreach ($candidate_models as $cand_model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($cand_model) . ':generateContent?key=' . urlencode($api_key);

            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $api_key
                ],
                'body'    => wp_json_encode($payload),
                'timeout' => 8
            ]);

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body, true);

            if ($code === 200 && !empty($json['candidates'][0]['content']['parts'])) {
                $reply_text = '';
                foreach ($json['candidates'][0]['content']['parts'] as $part) {
                    if (isset($part['text']) && (empty($part['thought']) || $part['thought'] !== true)) {
                        $reply_text .= $part['text'];
                    }
                }
                $reply_text = trim($reply_text);
                
                if (!empty($reply_text)) {
                    // Auto-link tools & platform pages with strict anti-spam guardrails
                    if (class_exists('Niyati_Linker')) {
                        $reply_text = Niyati_Linker::auto_link_response($reply_text);
                    }

                    return [
                        'success' => true,
                        'reply'   => $reply_text
                    ];
                }
            }

            $last_error = $json['error']['message'] ?? ('HTTP Error ' . $code);
        }

        if (!empty($last_error)) {
            error_log('[Niyati AI] Gemini API all models failed. Last error: ' . $last_error);
        }

        $fallback_msg = ($user_lang === 'hi') 
            ? 'ग्रहों की स्थिति का अध्ययन करने में विलंब हो रहा है। कृपया कुछ क्षणों बाद पुनः पूछें।' 
            : 'The celestial alignment is taking a moment to process. Please ask your question again shortly.';

        return [
            'success' => false,
            'reply'   => $fallback_msg,
            'error'   => $last_error
        ];
    }

    /**
     * Stream response from Gemini using SSE (Server-Sent Events)
     *
     * @param string $user_message
     * @param array $kundli_profile
     * @param array $history Chat history [['role' => 'user'|'model', 'text' => '...']]
     * @param string $user_lang
     * @param callable $token_callback function(string $token)
     * @return array [success => bool, reply => string, error => string]
     */
    public static function stream_chat_response($user_message, $kundli_profile, $history = [], $user_lang = 'en', $token_callback = null) {
        if (self::is_crawler_or_bot()) {
            return [
                'success' => false,
                'reply'   => 'Automated access blocked.',
                'error'   => 'bot_detected'
            ];
        }

        $api_key = self::get_api_key();
        $model = self::get_model();

        $resolved_lang = self::resolve_language($user_message, $user_lang);

        if (empty($api_key)) {
            $err_msg = ($resolved_lang === 'hi') 
                ? 'क्षमा करें, AI ज्योतिषी सेवा कुंजी (API Key) उपलब्ध नहीं है। कृपया एडमिन से संपर्क करें।' 
                : 'Astrology service key is currently unavailable. Please contact the administrator.';
            return [
                'success' => false,
                'reply'   => $err_msg,
                'error'   => 'Missing API key'
            ];
        }

        // If curl is unavailable, fall back to synchronous generation
        if (!function_exists('curl_init')) {
            $sync = self::generate_chat_response($user_message, $kundli_profile, $history, $resolved_lang);
            if ($sync['success'] && is_callable($token_callback)) {
                call_user_func($token_callback, $sync['reply']);
            }
            return $sync;
        }

        $system_instruction = self::build_system_instruction($kundli_profile, $resolved_lang, $user_message);

        $contents = [];
        $recent_history = array_slice($history, -8);
        foreach ($recent_history as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $text = sanitize_text_field($msg['text'] ?? '');
            if (!empty($text)) {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]]
                ];
            }
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => sanitize_text_field($user_message)]]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $system_instruction]]
            ],
            'contents'           => $contents,
            'generationConfig'   => [
                'temperature'     => 0.7,
                'topP'            => 0.9,
                'maxOutputTokens' => 240
            ],
            'safetySettings'     => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
            ]
        ];

        $candidate_models = array_unique(['gemini-3.1-flash-lite', 'gemini-3.5-flash-lite', $model, 'gemini-flash-lite-latest']);
        $last_error = '';

        foreach ($candidate_models as $cand_model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($cand_model) . ':streamGenerateContent?alt=sse&key=' . urlencode($api_key);
            $accumulated_text = '';
            $buffer = '';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $api_key
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$accumulated_text, &$buffer, $token_callback) {
                $buffer .= $data;
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $line = trim($line);
                    if (strpos($line, 'data: ') === 0) {
                        $json_str = substr($line, 6);
                        $json = json_decode($json_str, true);
                        if (!empty($json['candidates'][0]['content']['parts'])) {
                            foreach ($json['candidates'][0]['content']['parts'] as $part) {
                                if (isset($part['text']) && (empty($part['thought']) || $part['thought'] !== true)) {
                                    $chunk = $part['text'];
                                    $accumulated_text .= $chunk;
                                    if (is_callable($token_callback)) {
                                        call_user_func($token_callback, $chunk);
                                    }
                                }
                            }
                        }
                    }
                }
                return strlen($data);
            });

            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            if ($code === 200 && !empty($accumulated_text)) {
                $accumulated_text = trim($accumulated_text);
                if (class_exists('Niyati_Linker')) {
                    $accumulated_text = Niyati_Linker::auto_link_response($accumulated_text);
                }

                return [
                    'success' => true,
                    'reply'   => $accumulated_text
                ];
            }

            $last_error = $curl_err ?: ('HTTP Error ' . $code);
        }

        if (!empty($last_error)) {
            error_log('[Niyati AI] Gemini Stream all models failed. Last error: ' . $last_error);
        }

        $fallback_msg = ($user_lang === 'hi') 
            ? 'ग्रहों की स्थिति का अध्ययन करने में विलंब हो रहा है। कृपया कुछ क्षणों बाद पुनः पूछें।' 
            : 'The celestial alignment is taking a moment to process. Please ask your question again shortly.';

        return [
            'success' => false,
            'reply'   => $fallback_msg,
            'error'   => $last_error
        ];
    }

    /**
     * Generate an in-depth, elaborated astrological deep-dive response (approx 200-250 words)
     *
     * @param string $question The original user question
     * @param string $previous_reply The concise summary previously provided
     * @param array $kundli_profile Native's planetary positions and houses
     * @param string $user_lang Preferred language (en, hi, hinglish)
     * @return array [success => bool, reply => string, error => string]
     */
    public static function generate_elaborated_response($question, $previous_reply, $kundli_profile, $user_lang = 'en') {
        if (self::is_crawler_or_bot()) {
            return [
                'success' => false,
                'reply'   => 'Automated access blocked.',
                'error'   => 'bot_detected'
            ];
        }

        $api_key = self::get_api_key();
        $model = self::get_model();

        $resolved_lang = self::resolve_language($question, $user_lang);

        if (empty($api_key)) {
            return [
                'success' => false,
                'reply'   => ($resolved_lang === 'hi') ? 'क्षमा करें, AI सेवा कुंजी उपलब्ध नहीं है।' : 'Astrology service key is unavailable.',
                'error'   => 'Missing API key'
            ];
        }

        $system_instruction = self::build_system_instruction($kundli_profile, $resolved_lang, $question);

        // Add explicit Deep-Dive Elaboration directives
        $elaboration_directive = ($resolved_lang === 'hi')
            ? "\n\nविशेष निर्देश - विस्तृत व गहन विश्लेषण (Deep-Dive Analysis, 200-250 शब्द):\n" .
              "जिज्ञासु ने अपने प्रश्न के विषय में अधिक विस्तृत एवं गूढ़ वैदिक ज्योतिषीय विश्लेषण (लगभग 200 से 250 शब्दों में) का अनुरोध किया है।\n" .
              "1. कुंडली के संबंधित भावों, भावेशों (House Lords), ग्रहों की दृष्टि एवं युति का गहन विश्लेषण करें।\n" .
              "2. वर्तमान महादशा, अंतर्दशा एवं संबंधित नक्षत्र स्वामी के प्रभाव को स्पष्ट रूप से समझाएं।\n" .
              "3. आने वाले महीनों/वर्षों में ग्रहों के गोचर (Transits) और अनुकूल समय-सीमा (Timeframe) की संभावित खिड़की बताएं।\n" .
              "4. कर्म, सावधानी और अचूक वैदिक उपाय (मंत्र, दान, व्रत, आचरण) विस्तारपूर्वक प्रदान करें।\n" .
              "5. शब्द सीमा: लगभग 200 से 250 शब्द। स्पष्ट हेडिंग्स एवं बुलेट पॉइंट्स का उपयोग करें। निश्चितता का दावा न करें, संभावित योग एवं कर्म पर बल दें।"
            : (($resolved_lang === 'hinglish')
                ? "\n\nSPECIAL DIRECTIVE - DEEP-DIVE ELABORATED ANALYSIS (200-250 Words):\n" .
                  "Seeker ne apne question par detailed aur deep-dive Vedic astrological explanation request kiya hai (approx 200 to 250 words).\n" .
                  "1. Deep House & Planet Mechanics: Related Bhavas, House Lords ki sthiti, aspects aur conjunctions deeply explain karein.\n" .
                  "2. Dasha & Nakshatra Dynamics: Current Mahadasha/Antardasha aur Nakshatra lords ka is matter par exact effect batayein.\n" .
                  "3. Transit Activation & Timing Windows: Upcoming planetary transits aur favorable timeframes ko highlight karein.\n" .
                  "4. Actionable Vedic Remedies & Karma Guidance: Specific mantras, daan, lifestyle modifications aur karmic efforts prescribe karein.\n" .
                  "5. Length: Strictly 200 to 250 words. Use neat markdown headings and bullet points. Retain cautious, probabilistic phrasing (no absolute guarantees)."
                : "\n\nSPECIAL DIRECTIVE - DEEP-DIVE ELABORATED ANALYSIS (200-250 Words):\n" .
                  "The seeker has requested an in-depth, comprehensive astrological deep-dive on their question (approx 200 to 250 words).\n" .
                  "1. Deep House & Planet Mechanics: Thoroughly analyze relevant Bhavas, Lord placements, aspects, and planetary strengths.\n" .
                  "2. Dasha & Nakshatra Dynamics: Deeply evaluate the active Mahadasha, Antardasha, and Nakshatra lord's impact on this life area.\n" .
                  "3. Transit Activation & Key Timings: Detail upcoming planetary transits and auspicious activation windows.\n" .
                  "4. Actionable Vedic Remedies & Karma: Provide exact mantras, auspicious dana/donations, lifestyle tips, and conscious karma advice.\n" .
                  "5. Length: Approximately 200 to 250 words. Format with clean bold markdown sections and bullet points. Maintain probabilistic, dignified Vedic wisdom (no absolute deterministic claims).");

        $prompt_user = ($resolved_lang === 'hi')
            ? "मेरा प्रश्न था: \"{$question}\"\n\nआपने संक्षेप में यह बताया था:\n\"{$previous_reply}\"\n\nकृपया अब मुझे अपनी जन्म कुंडली के आधार पर इसका संपूर्ण, विस्तृत एवं गूढ़ विश्लेषण (लगभग 200-250 शब्दों में) प्रदान करें जिसमें ग्रहों की स्थिति, दशा, गोचर और उपाय विस्तार से हों।"
            : (($resolved_lang === 'hinglish')
                ? "Mera question tha: \"{$question}\"\n\nAapne pehle yeh summary di thi:\n\"{$previous_reply}\"\n\nAb please meri birth chart ke basis par iska complete in-depth elaborated analysis (approx 200-250 words) provide karein jisme grahon ke yog, dasha, transit timing aur remedies detail mein hon."
                : "My question was: \"{$question}\"\n\nYou previously summarized:\n\"{$previous_reply}\"\n\nPlease now provide a complete, in-depth astrological deep-dive (approx 200–250 words) based on my authentic chart, covering detailed house mechanics, active Dasha-transit dynamics, and specific Vedic remedies.");

        $contents = [
            [
                'role'  => 'user',
                'parts' => [['text' => $prompt_user]]
            ]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $system_instruction . $elaboration_directive]]
            ],
            'contents'           => $contents,
            'generationConfig'   => [
                'temperature'     => 0.7,
                'topP'            => 0.9,
                'maxOutputTokens' => 1200
            ],
            'safetySettings'     => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
            ]
        ];

        $candidate_models = array_unique(['gemini-3.1-flash-lite', 'gemini-3.5-flash-lite', $model, 'gemini-flash-lite-latest']);
        $last_error = '';

        foreach ($candidate_models as $cand_model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($cand_model) . ':generateContent?key=' . urlencode($api_key);

            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $api_key
                ],
                'body'    => wp_json_encode($payload),
                'timeout' => 12
            ]);

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body, true);

            if ($code === 200 && !empty($json['candidates'][0]['content']['parts'])) {
                $reply_text = '';
                foreach ($json['candidates'][0]['content']['parts'] as $part) {
                    if (isset($part['text']) && (empty($part['thought']) || $part['thought'] !== true)) {
                        $reply_text .= $part['text'];
                    }
                }
                $reply_text = trim($reply_text);
                if (!empty($reply_text)) {
                    if (class_exists('Niyati_Linker')) {
                        $reply_text = Niyati_Linker::auto_link_response($reply_text);
                    }
                    return [
                        'success' => true,
                        'reply'   => $reply_text,
                        'model'   => $cand_model
                    ];
                }
            }

            $last_error = $json['error']['message'] ?? ('HTTP Error ' . $code);
        }

        return [
            'success' => false,
            'reply'   => ($resolved_lang === 'hi') ? 'विस्तृत विश्लेषण तैयार करने में विलंब हो रहा है। कृपया पुनः प्रयास करें।' : 'Unable to generate detailed analysis at this moment. Please try again.',
            'error'   => $last_error
        ];
    }

    /**
     * Process Voice Consultation Turn (Max 15s user voice input -> ~30s Niyati voice response)
     *
     * @param string $audio_base64 Base64 encoded audio
     * @param string $mime_type MIME type of the audio (e.g. 'audio/webm', 'audio/mp4', 'audio/ogg')
     * @param array $kundli_profile Authentic birth chart profile
     * @param string $user_lang User language preference ('hi', 'hinglish', 'en')
     * @return array ['success' => bool, 'transcription' => string, 'reply' => string, 'audio_bytes' => string|null, 'mime_type' => string]
     */
    public static function process_voice_query($audio_base64, $mime_type = 'audio/webm', $kundli_profile = [], $user_lang = 'hi') {
        if (self::is_crawler_or_bot()) {
            return [
                'success' => false,
                'error'   => 'Automated access blocked.',
                'reply'   => 'Automated access not allowed.'
            ];
        }

        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return [
                'success' => false,
                'error'   => 'Gemini API key is not configured.',
                'reply'   => 'AI service is not configured. Please check API settings.'
            ];
        }

        $resolved_lang = in_array($user_lang, ['hi', 'hinglish', 'en'], true) ? $user_lang : 'hi';
        $system_instruction = self::build_system_instruction($kundli_profile, $resolved_lang);

        // Clean audio base64 if data URI prefix sent
        if (preg_match('/^data:([^;]+(?:;[^;]+)*);base64,(.+)$/s', $audio_base64, $m)) {
            $raw_mime = trim($m[1]);
            $audio_base64 = trim($m[2]);
            if (empty($mime_type) || $mime_type === 'audio/webm') {
                $mime_type = explode(';', $raw_mime)[0];
            }
        }
        $audio_base64 = str_replace(["\r", "\n", ' '], '', $audio_base64);

        // Normalize MIME type
        $clean_mime = 'audio/webm';
        if (strpos($mime_type, 'wav') !== false) {
            $clean_mime = 'audio/wav';
        } elseif (strpos($mime_type, 'mp4') !== false || strpos($mime_type, 'm4a') !== false) {
            $clean_mime = 'audio/mp4';
        } elseif (strpos($mime_type, 'ogg') !== false || strpos($mime_type, 'opus') !== false) {
            $clean_mime = 'audio/ogg';
        } elseif (strpos($mime_type, 'webm') !== false) {
            $clean_mime = 'audio/webm';
        }

        // Fast 50-55 word Voice Consultation directive for <8s turnaround with full Vedic depth
        $voice_directive = 
            "\n\nCRITICAL SPEED & VEDIC EXCELLENCE DIRECTIVE (STRICT TARGET: 50 TO 55 WORDS):\n" .
            "1. AUTOMATIC SPOKEN LANGUAGE MATCHING:\n" .
            "   - If seeker speaks in HINDI or HINGLISH: Respond entirely in pure, serene, affectionate HINDI (Devanagari script).\n" .
            "   - If seeker speaks in ENGLISH: Respond in serene Indian English.\n" .
            "2. HIGH-IMPACT VEDIC DEPTH (STRICTLY 50 TO 55 WORDS MAXIMUM - CRITICAL FOR <8S AUDIO SPEED):\n" .
            "   - Pack EVERY critical detail concisely without conversational filler:\n" .
            "     * Presumption opening: 'यदि हम यह मानकर चलें कि आप...'\n" .
            "     * Governing House & Planet from chart: e.g. 'आपकी कुंडली में 7वें भाव और शुक्र देव की शुभ स्थिति...'\n" .
            "     * Concrete Calendar Window: e.g. 'आगामी 4 से 6 महीनों में अनुकूल योग बन सकते हैं।'\n" .
            "     * Specific Day + Deity + Mantra remedy: e.g. 'प्रत्येक शुक्रवार को मां लक्ष्मी को सफेद पुष्प अर्पित कर ॐ शुं शुक्राय नमः का जाप करें, शुभ फल मिलेंगे।'\n" .
            "   - NO markdown (*, #, bullets), only crisp, flowing spoken sentences.\n" .
            "   - STRICT LIMIT: Do NOT exceed 55 words!\n" .
            "   - Output JSON format: {\"transcription\": \"...\", \"reply\": \"...\"}";

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $system_instruction . $voice_directive]]
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $clean_mime,
                                'data'     => $audio_base64
                            ]
                        ],
                        [
                            'text' => 'Listen carefully to this audio voice query, detect language, transcribe accurately, and formulate Niyati\'s crisp 50-55 word Vedic answer with house, planet, timeline window, and specific day/deity remedy. Return JSON with "transcription" and "reply".'
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'       => 0.2,
                'topP'              => 0.9,
                'maxOutputTokens'   => 250,
                'responseMimeType'  => 'application/json'
            ]
        ];

        // Models that support multimodal audio input - fastest first!
        $candidate_models = ['gemini-3.1-flash-lite', 'gemini-3.5-flash-lite', 'gemini-flash-lite-latest'];
        $last_error = '';

        foreach ($candidate_models as $cand_model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($cand_model) . ':generateContent?key=' . urlencode($api_key);

            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $api_key
                ],
                'body'    => wp_json_encode($payload),
                'timeout' => 8
            ]);

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body, true);

            if ($code === 200 && !empty($json['candidates'][0]['content']['parts'])) {
                $raw_text = '';
                foreach ($json['candidates'][0]['content']['parts'] as $part) {
                    if (isset($part['text']) && (empty($part['thought']) || $part['thought'] !== true)) {
                        $raw_text .= $part['text'];
                    }
                }
                $clean_raw = trim($raw_text);
                // Strip markdown code fences if wrapped in ```json ... ```
                if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $clean_raw, $m_json)) {
                    $clean_raw = trim($m_json[1]);
                }
                $parsed = json_decode($clean_raw, true);

                if (!is_array($parsed) || empty($parsed['reply'])) {
                    // Direct text fallback if model replied in plain text instead of JSON
                    if (!empty($clean_raw)) {
                        $parsed = [
                            'transcription' => ($resolved_lang === 'hi' ? 'वॉयस संदेश' : 'Voice Query'),
                            'reply'         => $clean_raw
                        ];
                    }
                }

                if (is_array($parsed) && !empty($parsed['reply'])) {
                    $transcription = trim($parsed['transcription'] ?? '');
                    $reply = trim($parsed['reply']);

                    $detected_lang = preg_match('/[\x{0900}-\x{097F}]/u', $reply) ? 'hi' : $resolved_lang;

                    // Synthesize Niyati's spoken audio if TTS engine available
                    $synth = self::synthesize_niyati_voice($reply, $detected_lang);

                    return [
                        'success'       => true,
                        'transcription' => $transcription ?: ($resolved_lang === 'hi' ? 'वॉयस संदेश' : 'Voice Query'),
                        'reply'         => $reply,
                        'audio_bytes'   => $synth['audio_bytes'] ?? null,
                        'audio_ext'     => $synth['audio_ext'] ?? 'wav',
                        'mime_type'     => $synth['mime_type'] ?? 'audio/wav',
                        'duration'      => $synth['duration'] ?? 20,
                        'model'         => $cand_model
                    ];
                }
            }

            $last_error = $json['error']['message'] ?? ('HTTP Error ' . $code);
        }

        return [
            'success' => false,
            'reply'   => ($resolved_lang === 'hi') ? 'वॉयस संदेश प्रोसेस करने में त्रुटि हुई। कृपया पुनः प्रयास करें।' : 'Unable to process voice message. Please try again.',
            'error'   => $last_error
        ];
    }

    /**
     * Synthesize Niyati's Voice Audio
     * Calls Gemini Native Flash-Lite TTS with Kore serene, harmonious Indian female voice profile, converted to standard WAV
     */
    public static function synthesize_niyati_voice($text, $lang = 'hi') {
        if (self::is_crawler_or_bot()) {
            return null;
        }

        $api_key = self::get_api_key();
        if (empty($api_key) || empty($text)) {
            return null;
        }

        $clean_speech = trim(preg_replace('/[*_#`\[\]]/', '', $text));
        $tts_models = ['gemini-3.1-flash-tts-preview', 'gemini-2.5-flash-preview-tts', 'gemini-3.8-flash-tts'];
        $voice_name = 'Kore'; // Serene, gentle, harmonious, calm female tone

        $tts_prompt = ($lang === 'hi' || preg_match('/[\x{0900}-\x{097F}]/u', $clean_speech))
            ? "Read this text aloud as Niyati, in a serene, affectionate, calm, and harmonious Indian voice:\n\n" . $clean_speech
            : "Read this text aloud as Niyati, in a calm, soothing, and harmonious Indian English tone:\n\n" . $clean_speech;

        $overall_start = microtime(true);
        foreach ($tts_models as $tts_model) {
            // Hard circuit breaker: If we've already spent 6 seconds attempting TTS, stop immediately!
            if (microtime(true) - $overall_start > 6.0) {
                break;
            }

            // Skip if this model recently hit a 429 quota block
            $transient_key = 'niyati_tts_429_' . md5($tts_model);
            if (get_transient($transient_key)) {
                continue;
            }

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($tts_model) . ':generateContent?key=' . urlencode($api_key);
            $payload = [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $tts_prompt]]]
                ],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO'],
                    'speechConfig' => [
                        'voiceConfig' => [
                            'prebuiltVoiceConfig' => [
                                'voiceName' => $voice_name
                            ]
                        ]
                    ]
                ]
            ];

            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $api_key
                ],
                'body'    => wp_json_encode($payload),
                'timeout' => 6 // Fast 6-second timeout per model
            ]);

            $code = wp_remote_retrieve_response_code($response);

            // If quota exhausted (429), cache the circuit breaker for 10 minutes so we don't repeat failed calls
            if ($code === 429) {
                set_transient($transient_key, 1, 10 * MINUTE_IN_SECONDS);
                continue;
            }

            if (!is_wp_error($response) && $code === 200) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                    $raw_b64 = $data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                    $raw_bytes = base64_decode($raw_b64);
                    if (!empty($raw_bytes)) {
                        $wav_bytes = self::clean_and_package_wav($raw_bytes, 24000);
                        if (!empty($wav_bytes)) {
                            return [
                                'audio_bytes' => $wav_bytes,
                                'audio_ext'   => 'wav',
                                'mime_type'   => 'audio/wav',
                                'duration'    => 20
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Clean and package audio bytes into pristine, pop-free standard RIFF WAV
     *
     * Eliminates static electricity crackles and pops caused by:
     * 1. Google Gemini TTS C2PA provenance metadata chunks being decoded as audio.
     * 2. DC-offset transient clicks on abrupt cutoff (smoothed via 15ms linear fade-out).
     * 3. Sample word misalignment (enforces even 16-bit boundaries).
     * 4. Appends 50ms of pure digital silence at the end for smooth player termination.
     */
    public static function clean_and_package_wav($raw_bytes, $sample_rate = 24000) {
        if (empty($raw_bytes)) {
            return null;
        }

        $pcm = '';
        // If Google returned an already-packaged RIFF container, extract strictly the 'data' subchunk
        // This strips off any trailing C2PA / metadata chunks that cause static crackle!
        if (substr($raw_bytes, 0, 4) === 'RIFF') {
            $offset = 12;
            $total_len = strlen($raw_bytes);
            while ($offset < $total_len - 8) {
                $chunk_id = substr($raw_bytes, $offset, 4);
                $chunk_size = unpack('V', substr($raw_bytes, $offset + 4, 4))[1];
                if ($chunk_id === 'data') {
                    $pcm = substr($raw_bytes, $offset + 8, $chunk_size);
                    break;
                }
                $offset += 8 + $chunk_size;
                if ($chunk_size % 2 !== 0) {
                    $offset++;
                }
            }
        } else {
            $pcm = $raw_bytes;
        }

        if (empty($pcm)) {
            return null;
        }

        // Align to 16-bit mono boundary (2 bytes per sample)
        $sample_count = intdiv(strlen($pcm), 2);
        $pcm = substr($pcm, 0, $sample_count * 2);

        // Apply a smooth 15ms fade-out (360 samples at 24kHz) to prevent DC-offset speaker pop
        $fade_samples = min(360, $sample_count);
        if ($fade_samples > 0) {
            $start_idx = ($sample_count - $fade_samples) * 2;
            for ($i = 0; $i < $fade_samples; $i++) {
                $byte_pos = $start_idx + ($i * 2);
                $val = unpack('s', substr($pcm, $byte_pos, 2))[1];
                $factor = 1.0 - ($i / $fade_samples);
                $new_val = (int)round($val * $factor);
                $new_bytes = pack('s', $new_val);
                $pcm[$byte_pos] = $new_bytes[0];
                $pcm[$byte_pos + 1] = $new_bytes[1];
            }
        }

        // Append 50ms of pure digital silence (1200 zero samples) for clean player cutoff
        $pcm .= str_repeat("\x00\x00", 1200);

        // Package into standard RIFF WAV container
        $data_len = strlen($pcm);
        $byte_rate = $sample_rate * 2;
        $block_align = 2;
        $bits_per_sample = 16;

        $header = pack('a4Va4a4VvvVVvva4V',
            'RIFF',
            36 + $data_len,
            'WAVE',
            'fmt ',
            16,
            1,              // PCM format
            1,              // Mono
            $sample_rate,
            $byte_rate,
            $block_align,
            $bits_per_sample,
            'data',
            $data_len
        );

        return $header . $pcm;
    }

    /**
     * Backward-compatible alias for pcm_to_wav
     */
    public static function pcm_to_wav($pcm_bytes, $sample_rate = 24000, $channels = 1, $bits_per_sample = 16) {
        return self::clean_and_package_wav($pcm_bytes, $sample_rate);
    }
}
