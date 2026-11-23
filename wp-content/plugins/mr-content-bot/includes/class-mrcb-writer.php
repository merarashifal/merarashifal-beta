<?php
/**
 * MeraRashifal Content Bot - Gemini AI Writer Engine
 * 3-Pass Hindi Festival & Astrology Article Generator with Entity Tagging and SEO Optimization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_Writer {

    const DEFAULT_MODEL = 'gemini-3-flash-preview';

    /**
     * Get Gemini API key
     */
    public static function get_api_key() {
        // Priority 1: wp-config.php constants (Secure & Protected)
        if (defined('GOOGLE_API_KEY') && !empty(GOOGLE_API_KEY)) {
            return trim(GOOGLE_API_KEY);
        }
        if (defined('MR_GEMINI_API_KEY') && !empty(MR_GEMINI_API_KEY)) {
            return trim(MR_GEMINI_API_KEY);
        }

        // Priority 2: Plugin-specific setting if set
        $mrcb_settings = get_option( 'mrcb_settings', [] );
        if ( ! empty( $mrcb_settings['gemini_api_key'] ) ) {
            return trim( $mrcb_settings['gemini_api_key'] );
        }

        // Priority 3: Reuse Niyati chat settings if available
        $niyati_settings = get_option( 'niyati_chat_settings', [] );
        if ( ! empty( $niyati_settings['gemini_api_key'] ) ) {
            return trim( $niyati_settings['gemini_api_key'] );
        }

        // Priority 4: Check mr_gemini_api_key option
        $fallback = get_option( 'mr_gemini_api_key', '' );
        if ( ! empty( $fallback ) ) {
            return trim( $fallback );
        }

        return '';
    }

    /**
     * Get Gemini Model
     */
    public static function get_model() {
        $mrcb_settings = get_option( 'mrcb_settings', [] );
        if ( ! empty( $mrcb_settings['gemini_model'] ) ) {
            return trim( $mrcb_settings['gemini_model'] );
        }
        $niyati_settings = get_option( 'niyati_chat_settings', [] );
        if ( ! empty( $niyati_settings['gemini_model'] ) ) {
            return trim( $niyati_settings['gemini_model'] );
        }
        return self::DEFAULT_MODEL;
    }

    /**
     * Call Gemini API with model fallback chain
     */
    public static function call_gemini( $prompt, $system_instruction = '', $temperature = 0.7, $max_tokens = 3000 ) {
        $api_key = self::get_api_key();
        if ( empty( $api_key ) ) {
            return [ 'success' => false, 'error' => 'Gemini API key is not configured.' ];
        }

        $active_model = self::get_model();
        $candidate_models = array_unique( [ $active_model, 'gemini-3-flash-preview', 'gemini-3.5-flash-lite', 'gemini-flash-lite-latest' ] );

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [ [ 'text' => $prompt ] ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => $temperature,
                'topP'            => 0.9,
                'maxOutputTokens' => $max_tokens,
            ],
            'safetySettings' => [
                [ 'category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE' ],
                [ 'category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE' ],
                [ 'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE' ],
                [ 'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE' ],
            ]
        ];

        if ( ! empty( $system_instruction ) ) {
            $payload['system_instruction'] = [
                'parts' => [ [ 'text' => $system_instruction ] ]
            ];
        }

        $last_error = '';

        for ( $attempt = 1; $attempt <= 2; $attempt++ ) {
            foreach ( $candidate_models as $cand_model ) {
                $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode( $cand_model ) . ':generateContent?key=' . urlencode( $api_key );

                $response = wp_remote_post( $url, [
                    'headers' => [ 'Content-Type' => 'application/json' ],
                    'body'    => wp_json_encode( $payload ),
                    'timeout' => 60,
                ] );

                if ( is_wp_error( $response ) ) {
                    $last_error = $response->get_error_message();
                    continue;
                }

                $code = wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );
                $json = json_decode( $body, true );

                if ( $code === 200 && ! empty( $json['candidates'][0]['content']['parts'] ) ) {
                    $text = '';
                    foreach ( $json['candidates'][0]['content']['parts'] as $part ) {
                        if ( isset( $part['text'] ) && ( empty( $part['thought'] ) || $part['thought'] !== true ) ) {
                            $text .= $part['text'];
                        }
                    }
                    $text = trim( $text );
                    if ( ! empty( $text ) ) {
                        return [
                            'success' => true,
                            'text'    => $text,
                            'model'   => $cand_model
                        ];
                    }
                }

                $last_error = $json['error']['message'] ?? ( 'HTTP ' . $code . ': ' . substr( $body, 0, 200 ) );

                if ( $code === 503 || $code === 429 ) {
                    usleep( 1500000 );
                }
            }

            if ( $attempt === 1 ) {
                sleep( 2 );
            }
        }

        return [ 'success' => false, 'error' => $last_error ];
    }

    /**
     * Test connection to Gemini API
     */
    public static function test_connection() {
        $res = self::call_gemini( 'Reply strictly with: "MeraRashifal-Writer-OK"', '', 0.2, 50 );
        if ( $res['success'] ) {
            return [
                'success' => true,
                'message' => 'Gemini API is connected successfully! (Model: ' . esc_html( $res['model'] ) . ')',
                'sample'  => $res['text']
            ];
        }
        return [
            'success' => false,
            'message' => 'Connection failed: ' . esc_html( $res['error'] )
        ];
    }

    /**
     * Sanitize and format focus keyword strictly as clean Devanagari Hindi target (2-3 words)
     * Strips Latin tag words (muhurat, vidhi, katha, upay, date, etc.) and repetitive tag combos
     */
    /**
     * Sanitize and format focus keyword strictly as clean Devanagari Hindi target (2-4 words)
     * Strips Latin tag words and never truncates mid-word
     */
    public static function sanitize_focus_keyword( $keyword, $festival_name_hi = '', $topic_title_hi = '', $topic_angle = '' ) {
        $clean = trim( (string) $keyword );

        // Strip Latin tag words often appended in blueprint mashups
        $strip_latin = [
            'muhurat', 'vidhi', 'katha', 'upay', 'date', 'shubh', 'timing', 'timings',
            'puja', 'pujan', 'calendar', 'fast', 'vrat', 'aaj', 'ka', 'ki', 'ke', 'niyam',
            'samagri', 'arghya', 'time', 'rules', 'importance', 'significance'
        ];
        $pattern = '/\b(' . implode( '|', $strip_latin ) . ')\b/i';
        $clean = trim( preg_replace( $pattern, '', $clean ) );

        // Strip Devanagari tag lists if appended at end like "मुहूर्त विधि कथा उपाय"
        $clean = trim( preg_replace( '/(मुहूर्त|विधि|कथा|उपाय|नियम|सामग्री|आरती)\s+(मुहूर्त|विधि|कथा|उपाय|नियम|सामग्री|आरती)/u', '', $clean ) );

        // Clean up multiple spaces
        $clean = trim( preg_replace( '/\s+/u', ' ', $clean ) );

        $year = wp_date( 'Y' );

        // If empty after stripping or contains no Devanagari, build clean Hindi keyphrase
        if ( ! preg_match( '/[\x{0900}-\x{097F}]/u', $clean ) || mb_strlen( $clean ) < 3 ) {
            if ( ! empty( $festival_name_hi ) ) {
                if ( strpos( $topic_angle, 'kundali' ) !== false || ( $topic_title_hi && mb_strpos( $topic_title_hi, 'उपाय' ) !== false ) ) {
                    $clean = $festival_name_hi . ' उपाय';
                } elseif ( strpos( $topic_angle, 'katha' ) !== false || ( $topic_title_hi && mb_strpos( $topic_title_hi, 'कथा' ) !== false ) ) {
                    $clean = $festival_name_hi . ' व्रत कथा';
                } else {
                    $clean = $festival_name_hi . ' ' . $year;
                }
            } elseif ( ! empty( $topic_title_hi ) ) {
                // Extract clean meaningful keyphrase without ever cutting words mid-character!
                $parts = preg_split( '/[:\-?|।]/u', $topic_title_hi );
                $phrase = trim( $parts[0] );
                // Strip numbers and generic counting words like "5 चीजें", "4 नियम"
                $phrase = preg_replace( '/\b\d+\s*(?:चीजें|नियम|उपाय|गलतियां|बातें|रास्ते)\b/u', '', $phrase );
                $words = array_values( array_filter( preg_split( '/\s+/u', $phrase ) ) );
                if ( count( $words ) > 4 ) {
                    $words = array_slice( $words, 0, 4 );
                }
                $clean = implode( ' ', $words );
            }
        }

        // Strip trailing grammatical stop words so keyword ends on a complete noun (e.g. "तुलसी के पौधे के पास", not "पौधे के")
        $clean = preg_replace( '/\s+(?:ये|के|की|का|में|पर|न|भी|को|से|है|हैं)$/u', '', $clean );

        // Guarantee clean Devanagari and trim punctuation (unicode-safe)
        $clean = preg_replace( '/^[\s\-:,।|]+|[\s\-:,।|]+$/u', '', $clean );
        if ( empty( $clean ) ) {
            $clean = ! empty( $festival_name_hi ) ? ( $festival_name_hi . ' ' . $year ) : 'दैनिक पंचांग ' . $year;
        }

        return $clean;
    }

    /**
     * Senior Digital Magazine Editor Persona & Anti-AI Detection Editorial Manifesto
     */
    public static function get_editorial_persona() {
        return <<<PROMPT
You are a veteran Hindi cultural columnist and investigative features writer (like a senior editor at Navbharat Times, Dainik Bhaskar, or BBC Hindi).
You write for real human readers who want engaging, grounded, and practical astrology insights.
Your text MUST defeat AI detectors (ZeroGPT, CopyLeaks) by achieving 95%+ Human Score (<= 10% AI detection).

CORE HUMAN JOURNALISTIC RULES:
1. प्राकृतिक व्याकरण एवं उच्च संयोजक अनुपात (HIGH FUNCTION-WORD RATIO > 40%):
   - AI रोबोट्स सिर्फ भारी संज्ञाओं (Nouns) की भरमार करते हैं, जबकि असली इंसान स्वाभाविक संयोजक और व्याकरणिक शब्दों का खुलकर प्रयोग करते हैं।
   - वाक्य में संबंधबोधक और संयोजक अवश्य लगाएं: 'अगर... तो', 'जब... तब', 'क्योंकि', 'इसलिए', 'साथ ही', 'की वजह से', 'के लिए', 'परंतु', 'हालांकि', 'भी', 'ही', 'तो'।
   - कभी भी कटे-फटे या आधे-अधूरे नारे जैसे वाक्य न लिखें (उदा: "बात बहुत साफ है", "नियम सीधे हैं", "यह सजा नहीं है" जैसे कृत्रिम AI तकियाकलाम भूलकर भी न लिखें)।

2. स्वाभाविक वाक्य प्रवाह और विविधता (NATURAL SENTENCE BURSTINESS):
   - इंसानी लेखन में वाक्य की लंबाई एक जैसी नहीं होती।
   - कुछ वाक्य मध्यम लंबाई (14 से 20 शब्द) के हों जो बात को गहराई दें।
   - कुछ वाक्य छोटे और विचारोत्तेजक (7 से 11 शब्द) हों, जैसे बीच में कोई सहज सवाल पूछना ("लेकिन क्या हर किसी के लिए यह समय सचमुच बुरा होता है?").
   - 25 शब्दों से बड़ा कोई भारी वाक्य न बनाएं।

3. प्रतिबंधित AI तकियाकलाम एवं उपदेशात्मक शैली (BANNED AI CLICHÉS & MORALIZING TROPES):
   - भूलकर भी ये कृत्रिम AI जुमले न लिखें:
     - 'यह सजा नहीं है' / 'यह सीख का समय है'
     - 'बात बहुत साफ है' / 'नियम सीधे हैं' / 'डरने की कोई बात नहीं है'
     - 'इस दौरान' / 'इसके अलावा' / 'विशेष रूप से' / 'व्यवहारिक रूप से देखें तो'
     - 'ज्योतिषीय मान्यताओं के अनुसार' (इसके स्थान पर "ज्योतिष के जानकारों का कहना है" या "परंपरा में" लिखें)
     - 'सकारात्मक दिशा' / 'नकारात्मक प्रभाव' / 'सकारात्मक ऊर्जा' / 'चमत्कारी लाभ' / 'संजीवनी का काम करता है'
     - 'नीचे दिए गए उपाय' / 'आइए जानते हैं'

4. रोजमर्रा की सहज हिंदी शब्दावली (SIMPLE CONTEMPORARY VOCABULARY):
   - भारी संस्कृतनिष्ठ पांडित्य के बजाय सहज और बोलचाल की भाषा लिखें:
     - 'समयावधि' -> 'समय' या 'दौर'
     - 'आत्म-मूल्यांकन' -> 'अपने काम की समीक्षा'
     - 'कष्टकारी' -> 'कठिन' या 'मुश्किल'
     - 'समयबद्धता' -> 'समय की पाबंदी'
     - 'उत्तरदायित्वों' -> 'जिम्मेदारियां'
     - 'प्रातःकालीन' -> 'सुबह के समय'
     - 'प्रज्वलित करें' -> 'जलाएं'
     - 'प्रतिकूलता' -> 'बुरा असर'
     - 'समीप' -> 'पास'
PROMPT;
    }

    /**
     * PASS 1: Generate comprehensive Hindi draft tailored to Topic Angle with Anti-AI standards
     */
    public static function generate_pass1_draft( $topic_title_hi, $festival_name_hi, $topic_angle, $target_word_count = 1100, $focus_keyword = '', $festival_name_en = '' ) {
        $system = self::get_editorial_persona();
        $year   = wp_date( 'Y' );

        // Sanitize focus keyword to eliminate any appended tag mashups or word chopping
        $focus_keyword = self::sanitize_focus_keyword( $focus_keyword, $festival_name_hi, $topic_title_hi, $topic_angle );

        $is_evergreen = empty( $festival_name_hi ) || in_array( $topic_angle, [ 'guide', 'vastu', 'puja_basics', 'astrology', 'sacred_items', 'lifestyle' ], true );

        $kw_instruction = '';
        if ( ! empty( $focus_keyword ) ) {
            $en_part = ! empty( $festival_name_en ) ? " ({$festival_name_en})" : '';
            $kw_instruction = <<<KWGUIDE
6. YOAST SEO एवं कीवर्ड नियम (Strict Yoast SEO Rules):
   - मुख्य फोकस कीवर्ड (Focus Keyphrase): "{$focus_keyword}"
   - पहले पैराग्राफ में कीवर्ड: पहले पैराग्राफ के पहले 1-2 वाक्यों में "{$focus_keyword}"{$en_part} को बिल्कुल स्वाभाविक और अर्थपूर्ण वाक्य में शामिल करें।
   - सबहेडिंग्स में कीवर्ड: किसी एक या दो H2 सबहेडिंग्स में "{$focus_keyword}" को स्वाभाविक रूप से शामिल करें। हर हेडिंग में कीवर्ड ठूंसने की सख्त मनाही है।
   - कीवर्ड घनत्व (Keyphrase Density): पूरे लेख में यह कीवर्ड 3 से 5 बार अलग-अलग संदर्भों में स्वाभाविक रूप से आना चाहिए।
   - वाक्यों की लंबाई: हर वाक्य 8 से 13 शब्दों का हो। 16 शब्दों से बड़ा कोई वाक्य न लिखें।
   - AI तकियाकलामों ('इस दौरान', 'इसके अलावा', 'विशेष रूप से', 'व्यवहारिक रूप से देखें तो') का प्रयोग बिल्कुल न करें।
KWGUIDE;
        }

        // Specific Angle & Pillar Guidance
        $angle_instructions = '';

        if ( $is_evergreen ) {
            $angle_instructions = <<<EVERGREENGUIDE
[सदाबहार विषय - VASTU / DAILY PUJA / JYOTISH LIFESTYLE GUIDELINES]:
- यह एक सदाबहार (Evergreen / Vastu / Daily Guidance) आलेख है।
- **अत्यंत महत्वपूर्ण:** इसमें किसी काल्पनिक संक्रांति, ग्रहण या मनगढ़ंत एकमुश्त मुहूर्त (जैसे 'दोपहर 01:28 बजे' या 'महापुण्य काल') का उल्लेख भूलकर भी न करें!
- इसके स्थान पर दैनिक जीवन में अपनाए जाने वाले व्यावहारिक नियम, सही समय (जैसे प्रातःकाल सूर्योदय के समय, स्नान के बाद, या संध्या वेला), सही दिशा (जैसे उत्तर, पूर्व, ईशान कोण) और वास्तु सावधानियां बिंदुवार समझाएं।
- हर बिंदु में स्पष्ट कारण और शास्त्रीय/व्यावहारिक समाधान दें।
- भाषा गरिमापूर्ण, व्यावहारिक और घरेलू जीवन के अनुकूल हो।
EVERGREENGUIDE;
        } elseif ( strpos( $topic_angle, 'muhurat' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: शुभ मुहूर्त, तिथि गणना एवं व्यावहारिक तैयारी]
- **अनिवार्य तारीख नियम:** पूजा, मुहूर्त या पारण का समय बताते समय हमेशा स्पष्ट **कैलेंडर तारीख और वार** (जैसे: 'गुरुवार, 26 नवंबर 2026') का स्पष्ट उल्लेख करें ताकि पाठक को ठीक से पता हो कि यह समय किस दिन लागू होगा।
- सही तिथि का आरंभ और समापन समय, सटीक घंटे और मिनट के साथ दें। पंचांग भेद हो तो उसे शांत और स्पष्ट भाषा में सुलझाएं।
- घर में पूजन की व्यावहारिक तैयारी और आवश्यक सामग्री की सरल सूची दें।
- दिनचर्या और व्रत पालन के व्यावहारिक नियम बताएं।
GUIDE;
        } elseif ( strpos( $topic_angle, 'kundali' ) !== false || strpos( $topic_angle, 'remedies' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: ज्योतिषीय समझ, जीवनशैली और राशि उपाय]
- अंधविश्वास या चमत्कारी दावों से पूरी तरह बचें।
- कुंडली के संदर्भ में नवग्रह शांति को सकारात्मक कर्मों और दान-पुण्य से जोड़कर समझाएं।
- 12 राशियों के लिए सरल, सात्विक व व्यावहारिक उपाय (दान, संयम, विशेष रंग, सात्विक आहार)।
GUIDE;
        } elseif ( strpos( $topic_angle, 'temples' ) !== false || strpos( $topic_angle, 'deity' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: सांस्कृतिक परंपरा, इष्टदेव और प्रमुख तीर्थ]
- पर्व से जुड़ी आध्यात्मिक भावना और लोकपरंपरा का संतुलित विवरण।
- मुख्य मंत्र और उनके व्यावहारिक अर्थ।
- देश के प्रमुख ऐतिहासिक व सिद्ध मंदिरों की परंपरा और वहां के उत्सव की वास्तविक झलक।
- घर पर शांतिपूर्ण ध्यान और आराधना का सीधा तरीका।
GUIDE;
        } elseif ( strpos( $topic_angle, 'vidhi_katha' ) !== false || strpos( $topic_angle, 'katha' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: प्रामाणिक पौराणिक कथा एवं सरल पूजन विधि]
- कथा को रोचक लेकिन स्वाभाविक संवाद शैली में प्रस्तुत करें, जिसमें जीवन मूल्य और नैतिक संदेश प्रमुख हों।
- घर पर की जाने वाली सरल और प्रामाणिक पूजन विधि (चरणबद्ध तरीके से)।
- व्रत पारण का सही समय और सात्विक भोजन के नियम।
- पारंपरिक आरती व प्रार्थना।
GUIDE;
        } else {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: पर्व का समग्र सांस्कृतिक, पंचांगीय व व्यावहारिक विश्लेषण]
- जब भी मुहूर्त या समय बताएं, तो अनिवार्य रूप से कैलेंडर तारीख और वार का स्पष्ट उल्लेख करें।
- तिथि, शुभ समय और पूजा का सही क्रम।
- सामाजिक व पारिवारिक महत्व और जीवनशैली में सकारात्मक बदलाव।
GUIDE;
        }

        $prompt = <<<PROMPT
विषय: "{$topic_title_hi}"
पर्व / संदर्भ: "{$festival_name_hi}"
दृष्टिकोण (Angle): "{$topic_angle}"
लक्ष्य शब्द संख्या: लगभग {$target_word_count} शब्द

विशेष संपादकीय दिशा-निर्देश:
{$angle_instructions}

संरचना नियम (Strict Structure Guidelines):
1. मुख्य शीर्षक (H1): स्पष्ट, गरिमापूर्ण और स्वाभाविक हिंदी शीर्षक (कीवर्ड स्टफिंग न हो)।
2. संतुलित शुरुआत: पहले 2-3 वाक्यों में सीधे विषय, पंचांग संदर्भ या पाठकों की मुख्य जिज्ञासा पर आएं। कोई काव्यात्मक मौसम, ठंड या अगरबत्ती की खुशबू जैसे बनावटी वाक्य न लिखें।
3. मुख्य विषयवस्तु (3 से 5 H2 शीर्षकों में):
   - हर सेक्शन में बिल्कुल नई और काम की जानकारी हो।
   - वाक्यों की लंबाई में विविधता रखें (छोटे और मध्यम वाक्यों का स्वाभाविक मिश्रण)।
   - पैराग्राफ्स की लंबाई प्राकृतिक रखें; मशीनी एकरूपता न हो।
4. स्वाभाविक समापन: कोई रोबोटिक 'निष्कर्ष' न लिखें। एक सकारात्मक, व्यावहारिक विचार या मंगलकामना के साथ समाप्त करें।
5. FAQ (अक्सर पूछे जाने वाले सवाल): ठीक 3 व्यावहारिक प्रश्न और उनके 2-3 वाक्यों के स्पष्ट उत्तर।
6. व्यावहारिक संदर्भ (Natural Astrological References):
   - प्रसंग अनुसार आलेख में 2-3 प्रमुख ज्योतिषीय शब्दों का स्वाभाविक वाक्य में प्रयोग करें:
     - पर्व/पूजा/मुहूर्त के लिए: 'दैनिक पंचांग', 'शुभ मुहूर्त', 'चौघड़िया'
     - ग्रह/दोष/उपाय के लिए: 'जन्म कुंडली', 'दैनिक राशिफल', 'ग्रह शांति'
   - वाक्य पूरी तरह स्वाभाविक होने चाहिए (जैसे: "शुभ समय जानने के लिए दैनिक पंचांग और मुहूर्त देखना फलदायी होता है।" या "ग्रहों की स्थिति समझने के लिए अपनी जन्म कुंडली का विश्लेषण जरूरी होता है।")।
{$kw_instruction}

प्रारूप: Markdown में लिखें (#, ##, ### का प्रयोग करें)।
PROMPT;

        return self::call_gemini( $prompt, $system, 0.82, 3500 );
    }

    /**
     * PASS 2: Senior Digital Magazine Editor - Aggressive Anti-AI Humanizer & Yoast Readability Polish
     */
    public static function generate_pass2_humanizer( $raw_draft, $focus_keyword = '' ) {
        $kw_guard = '';
        if ( ! empty( $focus_keyword ) ) {
            $kw_guard = "- मुख्य कीवर्ड '{$focus_keyword}' को पहले पैराग्राफ और सबहेडिंग्स में सुरक्षित रखें। इसे हटाएं नहीं और न ही इसमें कोई अतिरिक्त शब्द जोड़ें।";
        }

        $prompt = <<<PROMPT
तुम एक अत्यंत सख्त, वरिष्ठ हिंदी संपादक हो। तुम्हारा कार्य नीचे दिए गए आलेख को पूरी तरह से मशीनी AI भाषा से मुक्त करके 100% मानवीय (Zero AI Detectable) शैली में बदलना है।

संपादकीय निर्देश (CRITICAL RULES):
1. प्राकृतिक व्याकरण एवं उच्च संयोजक अनुपात (HIGH FUNCTION-WORD RATIO):
   - वाक्यों में स्वाभाविक संबंधबोधक और संयोजक अवश्य लगाएं: 'अगर... तो', 'जब... तब', 'क्योंकि', 'इसलिए', 'साथ ही', 'की वजह से', 'के लिए', 'परंतु', 'हालांकि', 'भी', 'ही', 'तो'।
   - कृत्रिम AI तकियाकलाम जैसे "बात बहुत साफ है", "नियम सीधे हैं", "यह सजा नहीं है", "डरने की कोई बात नहीं है" को पूरी तरह हटा दें।

2. वाक्य संतुलन (Sentence Balance):
   - 25 शब्दों से अधिक लंबे किसी भी वाक्य को पूर्णविराम (।) लगाकर दो छोटे वाक्यों में तोड़ें।
   - पूरे लेख में वाक्य लंबाई 10 से 18 शब्दों की स्वाभाविक विविधता में होनी चाहिए।

3. प्रतिबंधित AI शब्दों को जड़ से मिटाएं (Purge AI Signal Words):
   - 'इस दौरान'
   - 'इसके अलावा'
   - 'विशेष रूप से'
   - 'व्यवहारिक रूप से देखें तो'
   - 'ज्योतिषीय मान्यताओं के अनुसार' (इसके स्थान पर 'ज्योतिष के जानकारों के अनुसार' या 'शास्त्रों में' लिखें)
   - 'नीचे दिए गए' / 'मदद करेंगे' / 'महत्वपूर्ण भूमिका'
   - 'सकारात्मक दिशा' / 'नकारात्मक प्रभाव' / 'सकारात्मक ऊर्जा'
   इन सभी शब्दों को तुरंत हटाकर वाक्य को सरल और सीधा बनाएं।

4. भारी संस्कृतनिष्ठ शब्दों को आसान बनाएं (Simplify Vocabulary):
   - 'आत्म-मूल्यांकन' -> 'अपने काम की समीक्षा'
   - 'समयावधि' -> 'समय' या 'दौर'
   - 'समयबद्धता' -> 'समय की पाबंदी'
   - 'उत्तरदायित्वों' -> 'जिम्मेदारियां'
   - 'प्रातःकालीन' -> 'सुबह के समय'
   - 'कष्टकारी' -> 'कठिन'
   - 'प्रज्वलित करें' -> 'जलाएं'
   - 'समीप' -> 'पास'
   - 'प्रतिकूलता' -> 'बुरा असर'

5. कोई भी कोलन वाले फॉर्मूले (जैसे 'प्रातःकालीन जल अर्पण:') न रखें। साधारण 1, 2, 3 नंबर देकर 2-2 वाक्यों में बात लिखें।

{$kw_guard}

केवल और केवल शुद्ध परिष्कृत (Humanized) Markdown आलेख ही लौटाएं; कोई अपनी भूमिका, 'यहाँ है आपका लेख' जैसी टिप्पणी न लिखें।

मूल आलेख:
{$raw_draft}
PROMPT;

        return self::call_gemini( $prompt, '', 0.75, 3500 );
    }

    /**
     * PASS 3: Clean & Subtle Editorial Formatting
     */
    public static function generate_pass3_entity_tagger( $humanized_draft ) {
        $prompt = <<<PROMPT
नीचे दिए गए हिंदी लेख में केवल आवश्यक और स्वाभाविक संपादकीय फॉर्मेटिंग (Editorial Formatting) सुनिश्चित करें:

नियम:
1. केवल प्रामाणिक संस्कृत श्लोक और बीज मंत्रों को *इटैलिक* करें (जैसे: *ॐ नमो भगवते वासुदेवाय*, *ॐ नमः शिवाय*)।
2. महत्वपूर्ण तिथियों, समय-सीमाओं या प्रमुख शीर्षकों को आवश्यकतानुसार **बोल्ड** करें (जैसे: **17 नवंबर 2026**, **प्रदोष काल**)।
3. अत्यधिक या गैर-जरूरी बोल्ड टैग्स न लगाएं। हर वाक्य में 3-4 शब्द बोल्ड करने से लेख मशीनी और अप्राकृतिक लगता है। सामान्य भाषा को सहज और साफ रहने दें।
4. लेख के शब्दों, संरचना और वाक्यों में कोई बदलाव न करें।

केवल शुद्ध Markdown आलेख लौटाएं, कोई अतिरिक्त टिप्पणी न लिखें।

आलेख:
{$humanized_draft}
PROMPT;

        return self::call_gemini( $prompt, '', 0.3, 3500 );
    }

    /**
     * Generate SEO metadata (Focus Keyword, Meta Title, Meta Description, English Slug, English Tags)
     */
    public static function generate_seo_metadata( $topic_title_hi, $festival_name_hi, $final_content, $preset_focus_kw = '' ) {
        $year        = function_exists( 'wp_date' ) ? wp_date( 'Y' ) : date( 'Y' );
        $raw_default = ! empty( $preset_focus_kw ) ? $preset_focus_kw : ( $festival_name_hi ? $festival_name_hi . ' ' . $year : $topic_title_hi );
        $default_kw  = self::sanitize_focus_keyword( $raw_default, $festival_name_hi, $topic_title_hi );

        $prompt = <<<PROMPT
निम्नलिखित हिंदी ब्लॉग पोस्ट के लिए Yoast SEO अनुकूल मेटाडाटा तैयार करें:

विषय: {$topic_title_hi}
पर्व: {$festival_name_hi}
वर्ष: {$year}
सुझावित मुख्य कीवर्ड: "{$default_kw}"

सामग्री का संक्षिप्त अंश:
"""
{$final_content}
"""

कृपया JSON फॉर्मेट में उत्तर दें:
{
  "focus_keyword": "हिंदी में 2 से 3 शब्दों का मुख्य कीवर्ड जो लेख का सटीक विषय हो (उदा: {$default_kw})",
  "meta_title": "SEO टाइटल 50-60 अक्षरों के अंदर जो अनिवार्य रूप से मुख्य कीवर्ड से शुरू हो (उदा: {$default_kw}: शुभ मुहूर्त, पूजन विधि और उपाय)",
  "meta_desc": "SEO मेटा विवरण ठीक 130-150 अक्षरों में जिसमें मुख्य कीवर्ड शुरू में आए (कड़ाई से 154 अक्षरों से छोटा रखें)",
  "slug": "clean-english-url-slug-strictly-in-english-without-hindi-characters",
  "tags": "strictly-english-only-comma-separated-tags"
}

महत्वपूर्ण नियम:
1. "focus_keyword" वही हिंदी शब्द होना चाहिए जो लेख के शीर्षक, पहले पैराग्राफ और सबहेडिंग में मौजूद है (उदा: {$default_kw})। कोई अंग्रेजी शब्द या टैग न जोड़ें।
2. "meta_title" अनिवार्य रूप से "focus_keyword" से ही शुरू होना चाहिए और 60 अक्षरों से कम होना चाहिए।
3. "meta_desc" में "focus_keyword" शामिल होना चाहिए और लंबाई 130 से 150 अक्षरों के बीच होनी चाहिए (154 से अधिक होने पर Yoast त्रुटि देता है)।
4. "tags" strictly 100% ENGLISH ONLY (lowercase words separated by comma). Never write Hindi or Devanagari in tags!
5. "slug" strictly clean English words separated by hyphens.
6. केवल शुद्ध JSON लौटाएं। कोई Markdown बैक-टिक्स या अतिरिक्त टेक्स्ट न लिखें।
PROMPT;

        $res = self::call_gemini( $prompt, '', 0.4, 800 );
        $data = [];
        if ( $res['success'] ) {
            $raw_json = trim( $res['text'] );
            $raw_json = preg_replace( '/^```json\s*/i', '', $raw_json );
            $raw_json = preg_replace( '/^```\s*/i', '', $raw_json );
            $raw_json = preg_replace( '/\s*```$/', '', $raw_json );
            $data = json_decode( $raw_json, true );
        }

        if ( ! is_array( $data ) ) {
            $data = [];
        }

        // 1. Focus Keyword sanitization (strictly clean Hindi target)
        $focus_kw = self::sanitize_focus_keyword( trim( $data['focus_keyword'] ?? '' ), $festival_name_hi, $topic_title_hi );
        if ( empty( $focus_kw ) ) {
            $focus_kw = $default_kw;
        }
        $data['focus_keyword'] = $focus_kw;

        // 2. SEO Title: Must start with focus keyword & stay under 60 characters
        $meta_title = trim( $data['meta_title'] ?? '' );
        if ( empty( $meta_title ) ) {
            $meta_title = $focus_kw . ': शुभ मुहूर्त, पूजन विधि और उपाय';
        } elseif ( mb_strpos( $meta_title, $focus_kw ) !== 0 ) {
            $clean_t = preg_replace( '/^[\s\-:|।]+|[\s\-:|।]+$/u', '', str_replace( $focus_kw, '', $meta_title ) );
            $meta_title = $focus_kw . ': ' . $clean_t;
        }
        if ( mb_strlen( $meta_title ) > 60 ) {
            $meta_title = mb_substr( $meta_title, 0, 57 ) . '...';
        }
        $data['meta_title'] = $meta_title;

        // 3. Meta Description: Must contain focus keyword & strictly 125-150 characters (Yoast safe limit: <= 154)
        $meta_desc = trim( strip_tags( $data['meta_desc'] ?? '' ) );
        if ( empty( $meta_desc ) ) {
            $meta_desc = $focus_kw . ' का शुभ मुहूर्त, संपूर्ण पूजन विधि, व्रत कथा और कुंडली ग्रह शांति के अचूक उपाय जानें।';
        } elseif ( mb_strpos( $meta_desc, $focus_kw ) === false ) {
            $meta_desc = $focus_kw . ' - ' . $meta_desc;
        }
        if ( mb_strlen( $meta_desc ) > 154 ) {
            $meta_desc = mb_substr( $meta_desc, 0, 148 );
            $last_space = mb_strrpos( $meta_desc, ' ' );
            if ( $last_space !== false && $last_space > 120 ) {
                $meta_desc = mb_substr( $meta_desc, 0, $last_space ) . '।';
            }
        }
        $data['meta_desc'] = $meta_desc;

        // 4. Code-level sanitization: Guarantee tags are 100% English
        $fest_slug = sanitize_title( ! empty( $festival_name_hi ) ? $festival_name_hi : 'festival' );
        if ( ! empty( $data['tags'] ) ) {
            $raw_tags = explode( ',', $data['tags'] );
            $clean_tags = [];
            foreach ( $raw_tags as $t ) {
                $cleaned = preg_replace( '/[^a-zA-Z0-9\s\-]/', '', $t );
                $cleaned = strtolower( trim( preg_replace( '/\s+/', ' ', $cleaned ) ) );
                if ( ! empty( $cleaned ) && strlen( $cleaned ) >= 2 ) {
                    $clean_tags[] = $cleaned;
                }
            }
            if ( ! empty( $clean_tags ) ) {
                $data['tags'] = implode( ', ', array_unique( $clean_tags ) );
            } else {
                $data['tags'] = $fest_slug . ' ' . $year . ', puja vidhi, shubh muhurat, festivals';
            }
        } else {
            $data['tags'] = $fest_slug . ' ' . $year . ', puja vidhi, shubh muhurat, festivals';
        }

        // 5. Guarantee slug is clean English
        if ( ! empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['slug'] );
        } else {
            $data['slug'] = $fest_slug . '-' . $year . '-puja-vidhi';
        }

        return $data;
    }

    const CANONICAL_SITE_URL = 'https://merarashifal.com';

    /**
     * Get live site base URL (strictly https://merarashifal.com for all article links)
     */
    public static function get_site_base_url() {
        return self::CANONICAL_SITE_URL;
    }

    /**
     * Deterministic scrubber to eliminate AI idioms & clichés from generated text
     */
    public static function scrub_ai_cliches( $content ) {
        if ( empty( $content ) || ! is_string( $content ) ) {
            return $content;
        }

        $cliche_replacements = [
            '/\bइस दौरान,?\s*/u'                          => '',
            '/\bइसके अलावा,?\s*/u'                         => '',
            '/\bविशेष रूप से,?\s*/u'                        => '',
            '/\bव्यवहारिक रूप से(?: देखें तो)?,?\s*/u'      => '',
            '/\bज्योतिषीय मान्यताओं के अनुसार,?\s*/u'      => 'शास्त्रों में ',
            '/\bनीचे दिए गए[^\n:]+:\s*/u'                  => '',
            '/\bमहत्वपूर्ण भूमिका निभाता है/u'             => 'असर डालता है',
            '/\bसकारात्मक दिशा में मुड़ जाता है/u'          => 'सकारात्मक हो जाता है',
            '/\bनकारात्मक प्रभाव\b/u'                      => 'बुरा असर',
            '/\bसकारात्मक ऊर्जा\b/u'                       => 'सकारात्मक माहौल',
            '/\bकष्टकारी\b/u'                              => 'कठिन',
            '/\bप्रातःकालीन\b/u'                           => 'सुबह के समय',
            '/\bसमयावधि\b/u'                               => 'समय',
            '/\bसमयबद्धता\b/u'                             => 'समय की पाबंदी',
            '/\bउत्तरदायित्वों\b/u'                        => 'जिम्मेदारियों',
            '/\bआत्म-मूल्यांकन\b/u'                        => 'खुद को परखने',
            '/\bआइए जानते हैं(?: कि)?/u'                   => '',
            '/\bजैसा कि हम सभी जानते हैं,?\s*/u'          => '',
            '/\bनिष्कर्ष के रूप में,?\s*/u'                => '',
            '/\bआज के इस आधुनिक युग में,?\s*/u'           => 'आज के समय में',
            '/\bडिजिटल युग में,?\s*/u'                    => 'आजकल',
            '/\bपरिवर्तन प्रकृति का नियम है[।,]?\s*/u'      => '',
            '/\bअंधकार से प्रकाश की ओर[।,]?\s*/u'          => '',
            '/\bऊर्जा के संचार का प्रतीक है[।,]?\s*/u'      => '',
            '/\bचमत्कारी लाभ(?: मिलते हैं| होता है)?/u'     => 'अच्छे परिणाम मिलते हैं',
            '/\bसंजीवनी का काम करता है/u'                  => 'मददगार साबित होता है',
            '/\b(यह|यह कोई)\s*सजा\s*नहीं\s*है[।,]\s*/u'       => '',
            '/\b(यह)\s*सीख\s*का\s*समय\s*है[।,]\s*/u'          => '',
            '/\bबात\s*बहुत\s*साफ\s*है[।,]\s*/u'               => '',
            '/\bनियम\s*सीधे\s*हैं[।,]\s*/u'                   => '',
            '/\bडरने\s*की\s*कोई\s*बात\s*नहीं\s*है[।,]\s*/u'   => '',
            '/\bडरें\s*नहीं,?\s*समझें\b/u'                    => 'समझें',
            '/\bप्रज्वलित\s*करें\b/u'                         => 'जलाएं',
            '/\bसमीप\b/u'                                     => 'पास',
            '/\bप्रतिकूलता\b/u'                               => 'बुरा असर',
        ];

        foreach ( $cliche_replacements as $pattern => $replacement ) {
            $content = preg_replace( $pattern, $replacement, $content );
        }

        // Automated sentence splitter: split sentences with > 20 words at conjunctions
        $lines = explode( "\n", $content );
        $processed_lines = [];
        foreach ( $lines as $line ) {
            if ( preg_match( '/^#+/', $line ) || empty( trim( $line ) ) ) {
                $processed_lines[] = $line;
                continue;
            }

            $sentences = preg_split( '/(।)/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE );
            $rebuilt_line = '';
            for ( $i = 0; $i < count( $sentences ); $i += 2 ) {
                $s = $sentences[$i] ?? '';
                $danda = $sentences[$i+1] ?? '';
                $words = preg_split( '/\s+/u', trim( $s ) );
                if ( count( $words ) > 20 ) {
                    $split_pattern = '/(,\s*(?:और|लेकिन|परंतु|जिससे|ताकि|क्योंकि)\s+)/u';
                    if ( preg_match( $split_pattern, $s ) ) {
                        $s = preg_replace( $split_pattern, '। ', $s, 1 );
                    }
                }
                $rebuilt_line .= $s . $danda;
            }
            $processed_lines[] = $rebuilt_line;
        }

        return implode( "\n", $processed_lines );
    }

    /**
     * Deep scrubber to replace any localhost or local test URL with https://merarashifal.com
     */
    public static function clean_localhost_urls( $content ) {
        if ( empty( $content ) || ! is_string( $content ) ) {
            return $content;
        }
        $pattern = '#https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(?:/merarashifal)?#i';
        return preg_replace( $pattern, self::CANONICAL_SITE_URL, $content );
    }

    /**
     * Normalize URL to match canonical live domain https://merarashifal.com
     * Strips localhost, local directories, and guarantees live production links
     */
    public static function normalize_internal_url( $raw_url ) {
        if ( empty( $raw_url ) ) {
            return self::CANONICAL_SITE_URL . '/';
        }
        $parsed = function_exists( 'wp_parse_url' ) ? wp_parse_url( $raw_url ) : parse_url( $raw_url );
        $path = ! empty( $parsed['path'] ) ? $parsed['path'] : '/';
        // Strip out local folder prefix e.g. /merarashifal
        $clean_path = preg_replace( '#^/merarashifal#i', '', $path );
        $clean_path = '/' . ltrim( $clean_path, '/' );
        return self::CANONICAL_SITE_URL . $clean_path;
    }

    /**
     * Inject internal links from mr_link_map into final markdown / HTML content
     * Fully compliant with Yoast SEO internal links detection
     */
    public static function inject_internal_links( $content, $topic_title = '', $topic_angle = '' ) {
        global $wpdb;
        $links = [];
        if ( ! empty( $wpdb ) && class_exists( 'MRCB_DB_Setup' ) ) {
            $t_links = MRCB_DB_Setup::table_link_map();
            $links   = $wpdb->get_results( "SELECT * FROM {$t_links} WHERE is_active = 1", ARRAY_A ) ?: [];
        }

        $site_base = self::get_site_base_url();

        // Core fallback links pointing directly to live site
        if ( empty( $links ) ) {
            $links = [
                [ 'url' => $site_base . '/panchang/', 'title_hi' => 'दैनिक हिंदू पंचांग', 'keywords_hi' => 'दैनिक पंचांग, आज का पंचांग, हिंदू पंचांग, पंचांग', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/kundali-details/', 'title_hi' => 'जन्म कुंडली एवं जन्म पत्रिका', 'keywords_hi' => 'जन्म कुंडली, जन्मपत्रिका, जन्म पत्रिका, कुंडली', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/panchang/abhijit-muhurat/', 'title_hi' => 'आज का अभिजित मुहूर्त', 'keywords_hi' => 'अभिजित मुहूर्त, अभिजीत मुहूर्त, शुभ मुहूर्त', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/daily-choghadiya-muhurat/', 'title_hi' => 'दैनिक चौघड़िया मुहूर्त', 'keywords_hi' => 'चौघड़िया मुहूर्त, दैनिक चौघड़िया, चौघड़िया', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/horoscope/', 'title_hi' => 'आज का राशिफल', 'keywords_hi' => 'दैनिक राशिफल, आज का राशिफल, राशिफल', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/matchmaking-compatibility-check/', 'title_hi' => 'कुंडली मिलान (गुण मिलान)', 'keywords_hi' => 'कुंडली मिलान, गुण मिलान, विवाह मिलान', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/dosh/mangal-dosh/', 'title_hi' => 'मांगलिक दोष जांच एवं उपाय', 'keywords_hi' => 'मांगलिक दोष, मंगल दोष', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/dosh/kaal-sarp-dosh/', 'title_hi' => 'कालसर्प दोष निवारण', 'keywords_hi' => 'कालसर्प दोष', 'link_type' => 'tool' ],
                [ 'url' => $site_base . '/festivals/', 'title_hi' => 'हिंदू व्रत एवं त्योहार कैलेंडर', 'keywords_hi' => 'त्योहार कैलेंडर, व्रत त्योहार, हिंदू व्रत', 'link_type' => 'tool' ],
            ];
        }

        // 1. CONTEXTUAL INTELLIGENCE: Determine article theme
        $context_str = mb_strtolower( $topic_title . ' ' . $topic_angle . ' ' . mb_substr( $content, 0, 600 ) );
        $is_astro    = preg_match( '/(शनि|राहु|केतु|दोष|कुंडली|ग्रह|साढ़े साती|ढैय्या|उपाय)/u', $context_str );
        $is_marriage = preg_match( '/(विवाह|शादी|मिलान|वर-वधू)/u', $context_str );

        // Sort links so contextually relevant tools are evaluated first
        usort( $links, function( $a, $b ) use ( $is_astro, $is_marriage ) {
            $a_score = 0;
            $b_score = 0;
            if ( $is_astro ) {
                if ( strpos( $a['url'], 'kundali' ) !== false || strpos( $a['url'], 'dosh' ) !== false ) $a_score += 10;
                if ( strpos( $b['url'], 'kundali' ) !== false || strpos( $b['url'], 'dosh' ) !== false ) $b_score += 10;
            } elseif ( $is_marriage ) {
                if ( strpos( $a['url'], 'matchmaking' ) !== false ) $a_score += 10;
                if ( strpos( $b['url'], 'matchmaking' ) !== false ) $b_score += 10;
            } else {
                if ( strpos( $a['url'], 'panchang' ) !== false || strpos( $a['url'], 'choghadiya' ) !== false ) $a_score += 10;
                if ( strpos( $b['url'], 'panchang' ) !== false || strpos( $b['url'], 'choghadiya' ) !== false ) $b_score += 10;
            }
            return $b_score <=> $a_score;
        } );

        $linked_urls = [];
        $max_injected_links = 3;
        $injected_count = 0;

        // Split into lines so links are ONLY injected into normal body paragraphs
        $lines = explode( "\n", $content );
        $processed_lines = [];

        foreach ( $lines as $line ) {
            $trimmed = trim( $line );
            // Skip headings, blockquotes, HTML divs, list items
            if ( empty( $trimmed ) || preg_match( '/^(?:#+|>|<div|<\/div|<p|<\/p|\d+\.|\*|\-)/u', $trimmed ) ) {
                $processed_lines[] = $line;
                continue;
            }

            if ( $injected_count < $max_injected_links ) {
                foreach ( $links as $link ) {
                    if ( $injected_count >= $max_injected_links ) break;
                    $clean_url = self::normalize_internal_url( $link['url'] );
                    if ( in_array( $clean_url, $linked_urls, true ) ) continue;

                    $keywords = array_filter( array_map( 'trim', explode( ',', $link['keywords_hi'] ?? '' ) ) );
                    foreach ( $keywords as $kw ) {
                        if ( mb_strlen( $kw ) < 3 ) continue;

                        $pattern = '/(?<!\[)(?<!href=")(?<!href=\')(?<![\p{L}\p{N}])(' . preg_quote( $kw, '/' ) . ')(?![\p{L}\p{N}])(?![^<]*>)(?![^\[]*\])/u';

                        if ( preg_match( $pattern, $line ) ) {
                            $replacement = '<a href="' . esc_url( $clean_url ) . '" title="' . esc_attr( $link['title_hi'] ?? '' ) . '">$1</a>';
                            $line = preg_replace( $pattern, $replacement, $line, 1 );
                            $linked_urls[] = $clean_url;
                            $injected_count++;
                            break;
                        }
                    }
                }
            }
            $processed_lines[] = $line;
        }

        $content = implode( "\n", $processed_lines );

        // Fallback: If fewer than 2 links were matched naturally, inject a natural sentence after the first H2
        if ( $injected_count < 2 ) {
            $panchang_url = $site_base . '/panchang/';
            $kundali_url  = $site_base . '/kundali-details/';
            $natural_ref = '';

            if ( $is_astro && ! in_array( $kundali_url, $linked_urls, true ) ) {
                $natural_ref = "\n\nशास्त्रों के अनुसार अपनी <a href=\"" . esc_url( $kundali_url ) . "\">जन्म कुंडली</a> में ग्रहों और साढ़े साती की स्थिति देखकर उपाय करना अधिक फलदायी होता है।\n\n";
                $linked_urls[] = $kundali_url;
            } elseif ( ! in_array( $panchang_url, $linked_urls, true ) ) {
                $natural_ref = "\n\nशास्त्रों के अनुसार शुभ कार्य और पूजन से पहले <a href=\"" . esc_url( $panchang_url ) . "\">दैनिक पंचांग</a> और शुभ मुहूर्त की गणना देखना श्रेयस्कर माना जाता है।\n\n";
                $linked_urls[] = $panchang_url;
            }

            if ( ! empty( $natural_ref ) ) {
                if ( preg_match( '/(##\s+[^\n]+\n+)/u', $content ) ) {
                    $content = preg_replace( '/(##\s+[^\n]+\n+)/u', '$1' . $natural_ref, $content, 1 );
                } elseif ( preg_match( '/(^#[^\n]+\n+[^\n]+\n+)/um', $content ) ) {
                    $content = preg_replace( '/(^#[^\n]+\n+[^\n]+\n+)/um', '$1' . $natural_ref, $content, 1 );
                } else {
                    $content .= $natural_ref;
                }
            }
        }

        // 2. MID-ARTICLE CROSS-PROMOTION TAB (High-CTR Contextual Card)
        $promo_card = '';
        if ( $is_astro ) {
            $promo_card = "\n\n<div class=\"mrcb-crosspromo-box\">\n<span class=\"mrcb-promo-badge\">विशेष ज्योतिषीय सेवा।</span>\n<p class=\"mrcb-promo-content\">👉 <strong>अपनी कुंडली का विश्लेषण करें:</strong> क्या आपकी जन्म पत्रिका में भी ग्रह दोष का प्रभाव है? <a href=\"" . esc_url( $site_base . '/kundali-details/' ) . "\">मुफ्त जन्म कुंडली देखें और सटीक उपाय जानें।</a></p>\n</div>\n\n";
        } elseif ( $is_marriage ) {
            $promo_card = "\n\n<div class=\"mrcb-crosspromo-box\">\n<span class=\"mrcb-promo-badge\">विवाह एवं कुंडली मिलान।</span>\n<p class=\"mrcb-promo-content\">👉 <strong>गुण मिलान रिपोर्ट:</strong> वर-वधू के 36 गुणों और मांगलिक दोष की विस्तृत जांच के लिए <a href=\"" . esc_url( $site_base . '/matchmaking-compatibility-check/' ) . "\">मुफ्त कुंडली मिलान करें और रिपोर्ट पाएं।</a></p>\n</div>\n\n";
        } else {
            $promo_card = "\n\n<div class=\"mrcb-crosspromo-box\">\n<span class=\"mrcb-promo-badge\">शुभ मुहूर्त एवं पंचांग।</span>\n<p class=\"mrcb-promo-content\">👉 <strong>आज का शुभ समय:</strong> पूजा, अनुष्ठान और शुभ कार्यों के लिए आज का राहुकाल, अभिजीत मुहूर्त और चौघड़िया जानने के लिए <a href=\"" . esc_url( $site_base . '/panchang/' ) . "\">दैनिक हिंदू पंचांग देखें।</a></p>\n</div>\n\n";
        }

        // Insert after the second H2 section
        $h2_count = 0;
        $content = preg_replace_callback( '/(^##\s+[^\n]+)/m', function( $matches ) use ( &$h2_count, $promo_card ) {
            $h2_count++;
            if ( $h2_count === 2 ) {
                return $promo_card . $matches[0];
            }
            return $matches[0];
        }, $content );

        if ( $h2_count < 2 ) {
            $content .= $promo_card;
        }

        // 3. BOTTOM CROSS-PROMOTION SERVICES TABS (Responsive Navigation Grid)
        $services_tabs = "\n\n<div class=\"mrcb-services-tabs\">\n<h4>🔮 मेरा राशिफल विशेष ज्योतिषीय सेवाएं।</h4>\n<div class=\"mrcb-tabs-grid\">\n" .
            "<a href=\"" . esc_url( $site_base . '/panchang/' ) . "\" class=\"mrcb-tab-item\">📅 दैनिक पंचांग: आज का शुभ मुहूर्त व चौघड़िया देखें।</a>\n" .
            "<a href=\"" . esc_url( $site_base . '/kundali-details/' ) . "\" class=\"mrcb-tab-item\">✨ मुफ्त जन्म कुंडली: अपनी पत्रिका और ग्रह स्थिति जानें।</a>\n" .
            "<a href=\"" . esc_url( $site_base . '/daily-choghadiya-muhurat/' ) . "\" class=\"mrcb-tab-item\">⏰ चौघड़िया मुहूर्त: पूजा व यात्रा के शुभ समय की जांच करें।</a>\n" .
            "<a href=\"" . esc_url( $site_base . '/horoscope/' ) . "\" class=\"mrcb-tab-item\">♈ आज का राशिफल: अपनी राशि का दैनिक भविष्यफल पढ़ें।</a>\n" .
            "<a href=\"" . esc_url( $site_base . '/matchmaking-compatibility-check/' ) . "\" class=\"mrcb-tab-item\">💖 कुंडली मिलान: विवाह के लिए 36 गुणों का मिलान करें।</a>\n" .
            "<a href=\"" . esc_url( $site_base . '/festivals/' ) . "\" class=\"mrcb-tab-item\">🪔 व्रत व त्योहार: संपूर्ण हिंदू पंचांग कैलेंडर देखें।</a>\n" .
            "</div>\n</div>\n\n";

        // Insert before FAQ heading if present, otherwise append at the end
        $faq_pos = preg_match( '/(^##\s+.*?(?:अक्सर पूछे जाने वाले सवाल|FAQ|प्रश्नोत्तरी).*?$)/mui', $content, $m, PREG_OFFSET_CAPTURE );
        if ( $faq_pos && isset( $m[0][1] ) ) {
            $offset = $m[0][1];
            $content = substr( $content, 0, $offset ) . $services_tabs . substr( $content, $offset );
        } else {
            $content .= $services_tabs;
        }

        return self::clean_localhost_urls( $content );
    }

    /**
     * Convert markdown draft to clean, structured, and beautiful WordPress HTML
     */
    public static function markdown_to_html( $markdown ) {
        // 1. Normalize line breaks
        $text = str_replace( ["\r\n", "\r"], "\n", $markdown );

        // 2. Strip out any top H1 (# Title) since WordPress uses the post title as H1
        $text = preg_replace( '/^#\s+[^\n]+(?:\n+|$)/m', '', $text );

        // 3. Strip isolated asterisk lines, empty bullets, and standalone dashes
        $text = preg_replace( '/^[ \t]*[\*\-_]{1,2}[ \t]*$/m', '', $text );
        $text = preg_replace( '/^[ \t]*[\*\-_]{3,}[ \t]*$/m', '<hr />', $text );

        // 4. Clean dangling unclosed ** at line ends or starts
        $text = preg_replace( '/(?<!\*)\*\*$/m', '', $text );
        $text = preg_replace( '/^\*\*(?!\*)/m', '', $text );

        // 5. Separate FAQ Section boundary to prevent list/heading clobbering
        $faq_pattern = '/^[ \t]*(?:#+\s*|\*+\s*|\*\*)*\s*(?:अक्सर पूछे जाने वाले (?:प्रश्न|सवाल)|व्यावहारिक (?:प्रश्न|सवाल) और उत्तर|FAQ|बार-बार पूछे जाने वाले (?:प्रश्न|सवाल)|प्रश्नोत्तरी)[^\n]*/mui';
        $has_faq = preg_match( $faq_pattern, $text, $faq_match, PREG_OFFSET_CAPTURE );

        $body_text = $text;
        $faq_text  = '';
        if ( $has_faq ) {
            $offset    = $faq_match[0][1];
            $body_text = substr( $text, 0, $offset );
            $faq_text  = substr( $text, $offset + strlen( $faq_match[0][0] ) );
        }

        // Helper for markdown inline & block parsing on article body
        $parse_blocks = function( $md ) {
            // Headings
            $md = preg_replace( '/^####\s+(.+)$/m', '<h4>$1</h4>', $md );
            $md = preg_replace( '/^###\s+(.+)$/m', '<h3>$1</h3>', $md );
            $md = preg_replace( '/^##\s+(.+)$/m', '<h2>$1</h2>', $md );

            // Bullet lists (* item or - item)
            $md = preg_replace_callback( '/((?:^[ \t]*[\*\-][ \t]+[^\n]+(?:\n|$))+)/m', function( $m ) {
                $lines = explode( "\n", trim( $m[1] ) );
                $items = [];
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) || $line === '*' || $line === '-' ) continue;
                    $clean = preg_replace( '/^[\*\-][ \t]+/', '', $line );
                    if ( ! empty( $clean ) ) {
                        $items[] = '  <li>' . $clean . '</li>';
                    }
                }
                return ! empty( $items ) ? "<ul>\n" . implode( "\n", $items ) . "\n</ul>\n" : '';
            }, $md );

            // Ordered lists (1. item)
            $md = preg_replace_callback( '/((?:^[ \t]*\d+\.[ \t]+[^\n]+(?:\n|$))+)/m', function( $m ) {
                $lines = explode( "\n", trim( $m[1] ) );
                $items = [];
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) ) continue;
                    $clean = preg_replace( '/^\d+\.[ \t]+/', '', $line );
                    if ( ! empty( $clean ) ) {
                        $items[] = '  <li>' . $clean . '</li>';
                    }
                }
                return ! empty( $items ) ? "<ol>\n" . implode( "\n", $items ) . "\n</ol>\n" : '';
            }, $md );

            // Bold & Italic
            $md = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $md );
            $md = preg_replace( '/(?<!\w)\*([^\*\n]+?)\*(?!\w)/u', '<em>$1</em>', $md );
            $md = preg_replace( '/(?<!\w)_([^\_\n]+?)_(?!\w)/u', '<em>$1</em>', $md );

            // Markdown links [text](url)
            $md = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $md );

            // Standalone sacred mantra boxes
            $md = preg_replace( '/^[ \t]*<em>(ॐ[^<\n]+)<\/em>[ \t]*$/mu', '<div class="mrcb-mantra-box"><em>$1</em></div>', $md );
            $md = preg_replace( '/^[ \t]*<strong>(ॐ[^<\n]+)<\/strong>[ \t]*$/mu', '<div class="mrcb-mantra-box"><em>$1</em></div>', $md );

            // Paragraphs
            $blocks = preg_split( '/\n\s*\n/', trim( $md ) );
            $html_blocks = [];
            foreach ( $blocks as $block ) {
                $block = trim( $block );
                if ( empty( $block ) ) continue;
                if ( preg_match( '/^<(h2|h3|h4|hr|ul|ol|div|blockquote)/i', $block ) ) {
                    $html_blocks[] = $block;
                } else {
                    $html_blocks[] = '<p class="mrcb-article-p">' . nl2br( $block ) . '</p>';
                }
            }
            return implode( "\n\n", $html_blocks );
        };

        $body_html = $parse_blocks( $body_text );

        // Parse FAQ cards bulletproof
        $faq_html = '';
        if ( ! empty( $faq_text ) ) {
            $faq_cards = [];
            $clean_faq = preg_replace( '/^[ \t]*(?:#+\s*|\*+\s*|\*\*)*\s*(?:अक्सर पूछे जाने वाले (?:प्रश्न|सवाल)|व्यावहारिक (?:प्रश्न|सवाल) और उत्तर|FAQ|बार-बार पूछे जाने वाले (?:प्रश्न|सवाल)|प्रश्नोत्तरी)[^\n]*\n+/mui', '', trim( $faq_text ) );

            // Split items by question markers or ### subheadings
            $items = preg_split( '/(?=(?:^|\n)[ \t]*(?:###|##|\*\*)?(?:(?:\d+[\.\)]|\*|\-)\s*)?(?:प्रश्न|सवाल|\bQ\d*\b|\*\*\d+[\.\)])[ :\-]*)|(?=(?:^|\n)[ \t]*###\s+)/mu', trim( $clean_faq ), -1, PREG_SPLIT_NO_EMPTY );
            if ( count( $items ) <= 1 ) {
                $items = preg_split( '/\n\s*\n/', trim( $clean_faq ), -1, PREG_SPLIT_NO_EMPTY );
            }

            foreach ( $items as $item ) {
                $item = trim( $item );
                if ( empty( $item ) ) continue;

                // Case A: Has "उत्तर:" / "Ans:" separator
                if ( preg_match( '/^(.*?)(?:\n|<br\s*\/?>)\s*(?:\*\*)?(?:उत्तर|जवाब|\bAns\b|\bA\b)[ :\-]+(.*)$/su', $item, $m ) ) {
                    $q = trim( strip_tags( $m[1] ) );
                    $a = trim( $m[2] );
                }
                // Case B: First line is question (ends with ? or newline) and following lines are answer
                elseif ( preg_match( '/^(.*?[\?？]|.*?[^\n]+)(?:\n|<br\s*\/?>)(.+)$/su', $item, $m ) ) {
                    $q = trim( strip_tags( $m[1] ) );
                    $a = trim( $m[2] );
                } else {
                    continue;
                }

                $q = preg_replace( '/^[ \t]*#+[ \t]*/', '', $q );
                $q = preg_replace( '/^(?:\*\*)?(?:(?:\d+[\.\)]|\*|\-)\s*)?(?:प्रश्न|सवाल|\bQ\d*\b)?[ :\-.]*/u', '', $q );
                $q = trim( $q, " *:\t\n\r" );
                $a = preg_replace( '/^(?:\*\*)?(?:उत्तर|जवाब|\bAns\b|\bA\b)?[ :\-.]*/u', '', $a );
                $a = trim( $a, " *:\t\n\r" );
                $a = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $a );
                $a = preg_replace( '/(?<!\w)\*([^\*\n]+?)\*(?!\w)/u', '<em>$1</em>', $a );

                if ( ! empty( $q ) && ! empty( $a ) ) {
                    $faq_cards[] = '<div class="mrcb-faq-card"><div class="mrcb-faq-q">❓ ' . htmlspecialchars( $q, ENT_QUOTES, 'UTF-8' ) . '</div><div class="mrcb-faq-a">' . nl2br( $a ) . '</div></div>';
                }
            }

            if ( ! empty( $faq_cards ) ) {
                $faq_html = "\n\n<div class=\"mrcb-faq-section\">\n<h3 class=\"mrcb-faq-header\">अक्सर पूछे जाने वाले प्रश्न (FAQ)</h3>\n" . implode( "\n", $faq_cards ) . "\n</div>";
            }
        }

        return self::clean_localhost_urls( $body_html . $faq_html );
    }

    /**
     * Run full 3-pass generation pipeline for a specific queue item
     */
    public static function generate_for_queue_item( $queue_id ) {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();
        $t_fest  = MRCB_DB_Setup::table_festivals();

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_queue} WHERE id = %d", $queue_id ), ARRAY_A );
        if ( ! $item ) {
            return [ 'success' => false, 'error' => 'Queue item not found.' ];
        }

        $festival_name_hi = '';
        $festival_name_en = '';
        if ( ! empty( $item['festival_id'] ) ) {
            $fest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fest} WHERE id = %d", $item['festival_id'] ), ARRAY_A );
            if ( $fest ) {
                $festival_name_hi = $fest['festival_name_hi'] ?: $fest['festival_name_en'];
                $festival_name_en = $fest['festival_name_en'] ?: '';
            }
        }

        // Determine primary clean focus keyword upfront (strictly clean Devanagari Hindi target)
        $raw_kw   = ! empty( $item['focus_keyword'] ) ? trim( $item['focus_keyword'] ) : '';
        $focus_kw = self::sanitize_focus_keyword( $raw_kw, $festival_name_hi, $item['topic_title_hi'], $item['topic_angle'] );

        // PASS 1: Raw Draft
        $pass1_res = self::generate_pass1_draft(
            $item['topic_title_hi'],
            $festival_name_hi,
            $item['topic_angle'] ?: 'katha',
            $item['word_count_target'] ?: 950,
            $focus_kw,
            $festival_name_en
        );
        if ( ! $pass1_res['success'] ) {
            return [ 'success' => false, 'error' => 'Pass 1 failed: ' . $pass1_res['error'] ];
        }
        $pass1_text = self::clean_localhost_urls( $pass1_res['text'] );

        // PASS 2: Humanizer
        $pass2_res = self::generate_pass2_humanizer( $pass1_text, $focus_kw );
        $pass2_text = $pass2_res['success'] ? self::clean_localhost_urls( $pass2_res['text'] ) : $pass1_text;

        // PASS 3: Entity Tagging
        $pass3_res = self::generate_pass3_entity_tagger( $pass2_text );
        $pass3_text = $pass3_res['success'] ? self::clean_localhost_urls( $pass3_res['text'] ) : $pass2_text;

        // Scrub AI cliches & Inject Internal Links
        $clean_pass3   = self::scrub_ai_cliches( $pass3_text );
        $final_content = self::clean_localhost_urls( self::inject_internal_links( $clean_pass3, $item['topic_title_hi'] ?? '', $item['topic_angle'] ?? '' ) );

        // Generate SEO Meta
        $seo_meta = self::generate_seo_metadata( $item['topic_title_hi'], $festival_name_hi, $final_content, $focus_kw );

        $focus_kw   = $seo_meta['focus_keyword'] ?? $focus_kw;
        $meta_title = $seo_meta['meta_title'] ?? mb_substr( $item['topic_title_hi'], 0, 60 );
        $meta_desc  = $seo_meta['meta_desc'] ?? mb_substr( strip_tags( $final_content ), 0, 150 );
        $slug       = $seo_meta['slug'] ?? sanitize_title( $item['topic_title_hi'] );
        $tags       = $seo_meta['tags'] ?? '';

        // Save back to queue
        $wpdb->update(
            $t_queue,
            [
                'ai_draft'            => $pass1_text,
                'ai_draft_humanized'  => $pass2_text,
                'ai_draft_tagged'     => $final_content,
                'focus_keyword'       => $focus_kw,
                'ai_meta_title'       => $meta_title,
                'ai_meta_desc'        => $meta_desc,
                'ai_tags'             => $tags,
                'wp_slug'             => $slug,
                'generation_attempts' => (int) $item['generation_attempts'] + 1,
                'last_generated_at'   => current_time( 'mysql' ),
                'admin_status'        => 'pending_review',
            ],
            [ 'id' => $queue_id ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ],
            [ '%d' ]
        );

        return [
            'success'       => true,
            'queue_id'      => $queue_id,
            'focus_keyword' => $focus_kw,
            'slug'          => $slug,
            'word_count'    => str_word_count( strip_tags( $final_content ) ),
        ];
    }
}
