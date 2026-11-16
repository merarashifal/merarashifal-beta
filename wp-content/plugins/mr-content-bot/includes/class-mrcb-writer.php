<?php
/**
 * MeraRashifal Content Bot - Gemini AI Writer Engine
 * 3-Pass Hindi Festival & Astrology Article Generator with Entity Tagging and SEO Optimization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_Writer {

    const DEFAULT_MODEL = 'gemini-3.5-flash-lite';

    /**
     * Get Gemini API key
     */
    public static function get_api_key() {
        // First check plugin-specific setting if set
        $mrcb_settings = get_option( 'mrcb_settings', [] );
        if ( ! empty( $mrcb_settings['gemini_api_key'] ) ) {
            return trim( $mrcb_settings['gemini_api_key'] );
        }

        // Second, reuse Niyati chat settings if available
        $niyati_settings = get_option( 'niyati_chat_settings', [] );
        if ( ! empty( $niyati_settings['gemini_api_key'] ) ) {
            return trim( $niyati_settings['gemini_api_key'] );
        }

        // Third, check mr_gemini_api_key
        $fallback = get_option( 'mr_gemini_api_key', '' );
        if ( ! empty( $fallback ) ) {
            return trim( $fallback );
        }

        // Hardcoded verified fallback key
        return 'AIzaSyDzS22g365n4yrHAPRTuRfViNS3avj3QF0';
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
        $candidate_models = array_unique( [ $active_model, 'gemini-3.5-flash-lite', 'gemini-3.1-flash-lite', 'gemini-3.7-flash', 'gemini-3.5-flash' ] );

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

        foreach ( $candidate_models as $cand_model ) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode( $cand_model ) . ':generateContent?key=' . urlencode( $api_key );

            $response = wp_remote_post( $url, [
                'headers' => [ 'Content-Type' => 'application/json' ],
                'body'    => wp_json_encode( $payload ),
                'timeout' => 45,
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
        $clean = trim( preg_replace( '/\s+/', ' ', $clean ) );

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
                $clean = mb_substr( $topic_title_hi, 0, 25 );
            }
        }

        // Guarantee clean Devanagari and trim punctuation (unicode-safe)
        $clean = preg_replace( '/^[\s\-:,।|]+|[\s\-:,।|]+$/u', '', $clean );
        if ( empty( $clean ) ) {
            $clean = ! empty( $festival_name_hi ) ? ( $festival_name_hi . ' ' . $year ) : 'त्योहार ' . $year;
        }

        return $clean;
    }

    /**
     * Senior Digital Magazine Editor Persona & Anti-AI Detection Editorial Manifesto
     */
    public static function get_editorial_persona() {
        return <<<PROMPT
You are a senior Hindi features editor and cultural columnist writing for MeraRashifal.com (जैसे दैनिक भास्कर के रसरंग, नवभारत टाइम्स के विशेष संपादकीय या बीबीसी हिंदी के वरिष्ठ लेखक).
Your writing must read 100% authentically human, intellectually grounded, and achieve ZERO (<15%) AI detection score on all major AI detectors (ZeroGPT, CopyLeaks, GPTZero).

STRICT BAN ON 25 LLM HINDI CLICHÉS (ZERO TOLERANCE - DO NOT USE ANY OF THESE):
1. "...का पावन पर्व है" / "...का पावन अवसर है"
2. "अत्यंत महत्वपूर्ण माना गया है" / "विशेष महत्व रखता है" / "विशेष महत्व है"
3. "यह हमें अंधकार से प्रकाश की ओर बढ़ने की प्रेरणा देता है"
4. "यह हमें सिखाता है कि परिवर्तन प्रकृति का नियम है"
5. "अनुशासित जीवनशैली ही इस पर्व का वास्तविक उद्देश्य है"
6. "परिणामस्वरूप, यह संक्रांति आत्मा के शोधन का..."
7. "धार्मिक ग्रंथों में सूर्य की गति को काल का चक्र माना गया है"
8. "ऊर्जा के संचार का प्रतीक है" / "सकारात्मक ऊर्जा का संचार होता है"
9. "जैसा कि हम सभी जानते हैं"
10. "आइए जानते हैं" / "आइए विस्तार से समझें" / "इस लेख में हम बताएंगे"
11. "चमत्कारी लाभ" / "अचूक चमत्कार" / "दिव्य अनुभूति"
12. "संजीवनी का काम करता है"
13. "शुभ फल की प्राप्ति होती है" / "पुण्य फल की प्राप्ति"
14. "जीवन में सुख-समृद्धि और खुशहाली आती है"
15. "पापों का नाश होता है" / "समस्त पाप धुल जाते हैं"
16. "अखंड सौभाग्य की प्राप्ति होती है"
17. "मनोकामनाएं पूर्ण होती हैं" / "हर मनोकामना पूरी होगी"
18. "समस्त कष्टों का निवारण होता है" / "सारे संकट पल भर में दूर"
19. "निष्कर्ष के रूप में" / "संक्षेप में कहें तो" / "अतः यह कहा जा सकता है"
20. "आज के इस आधुनिक युग में" / "आज की भागदौड़ भरी जिंदगी में"
21. "ठिठुरती रातें" / "श्वेत चांदनी" / "कपूर और अगरबत्ती की सुगंध"
22. "ब्रह्मांड की दुर्लभ ऊर्जाएं" / "अलौकिक संयोग"
23. "अद्वितीय स्थान रखता है"
24. "श्रद्धा और भक्ति के साथ"
25. "आध्यात्मिक यात्रा का आरंभ"

HOW TO WRITE AS A REAL HUMAN JOURNALIST & SENIOR COLUMNIST:
1. CONVERSATIONAL, GROUNDED & ANALYTICAL VOICE:
   - Use natural Hindi phrasing: "सीधी बात यह है", "अक्सर लोग यहीं चूक करते हैं", "गौर करने वाली बात यह है", "आमतौर पर", "सच्चाई यह है", "तार्किक रूप से देखें तो", "दिखावे से ज्यादा जरूरी है संयम"।
   - Avoid mechanical, textbook, or priest-like preaching. Speak directly to the thoughtful reader.
2. HIGH BURSTINESS & RHYTHM VARIATION:
   - AI writes monotonous sentences of predictable length. Break this completely!
   - Mix ultra-short 2-5 word micro-sentences for emphasis: "संयम जरूरी है।" "वजह बहुत साफ है।" "दिखावे से बचें।" "तैयारी पहले से रखें।" "जल्दबाजी बिल्कुल न करें।" "नियम स्पष्ट हैं।"
   - Ask thought-provoking rhetorical questions: "क्या सिर्फ औपचारिकता निभाना ही काफी है?" "आखिर इस परंपरा के पीछे की असली सोच क्या है?"
   - Alternate between crisp observations (1 sentence) and reflective explanations (2-3 sentences). Never write uniform paragraphs.
3. MODERN READER PERSPECTIVE (DIGITAL LIFESTYLE & WELLNESS CONNECTION):
   - Connect ancient rituals to modern reality: How fasting or screen-free puja calms digital anxiety; why seasonal shifts (like Sankranti in November) require dietary changes like sesame (तिल) and jaggery (गुड़) for body warmth and immunity; why charity is an ego detox.
4. EXACT PANCHANG TIMINGS (MANDATORY REAL HOURS & MINUTES):
   - Never write vague lines like "दोपहर के समय से होगी" or "सुबह का समय शुभ है".
   - Mandate specific hours and minutes based on traditional astronomical calculations:
     e.g., "संक्रांति का क्षण: दोपहर 01:28 बजे", "महापुण्य काल: दोपहर 01:28 से 03:15 बजे तक", "पुण्य काल: दोपहर 01:28 से शाम 05:45 बजे तक", "सर्वश्रेष्ठ पूजन समय: सुबह 07:15 से 09:30 बजे तक"।
5. NATURAL HEADINGS (STRICTLY NO KEYWORD STUFFING):
   - Headings must look like newspaper or magazine editorial headlines, NOT SEO spam!
   - Never stuff keywords into every heading.
PROMPT;
    }

    /**
     * PASS 1: Generate comprehensive Hindi draft tailored to Topic Angle with Anti-AI standards
     */
    public static function generate_pass1_draft( $topic_title_hi, $festival_name_hi, $topic_angle, $target_word_count = 1100, $focus_keyword = '', $festival_name_en = '' ) {
        $system = self::get_editorial_persona();
        $year   = wp_date( 'Y' );

        // Sanitize focus keyword to eliminate any appended tag mashups
        $focus_keyword = self::sanitize_focus_keyword( $focus_keyword, $festival_name_hi, $topic_title_hi, $topic_angle );

        $kw_instruction = '';
        if ( ! empty( $focus_keyword ) ) {
            $en_part = ! empty( $festival_name_en ) ? " ({$festival_name_en})" : '';
            $kw_instruction = <<<KWGUIDE
6. YOAST SEO एवं कीवर्ड नियम (Strict Yoast SEO Rules):
   - मुख्य फोकस कीवर्ड (Focus Keyphrase): "{$focus_keyword}"
   - पहले पैराग्राफ में कीवर्ड: पहले पैराग्राफ के पहले 1-2 वाक्यों में "{$focus_keyword}"{$en_part} को बिना किसी बनावटीपन के स्वाभाविक रूप से लाएं (जैसे: "वर्ष {$year} में {$focus_keyword} को लेकर श्रद्धालुओं में विशेष उत्सुकता है...", या "पंचांग के अनुसार {$focus_keyword} का समय इस बार बेहद खास माना जा रहा है...").
   - सबहेडिंग्स में कीवर्ड: किसी एक या दो H2 सबहेडिंग्स में "{$focus_keyword}" को स्वाभाविक रूप से शामिल करें (जैसे: "## {$focus_keyword}: पंचांग समय और शुभ मुहूर्त", "## {$focus_keyword} पर पूजन की सही विधि")। हर हेडिंग में कीवर्ड ठूंसने (Keyword Stuffing) की सख्त मनाही है।
   - कीवर्ड घनत्व (Keyphrase Density): पूरे लेख में यह कीवर्ड 3 से 5 बार अलग-अलग अनुच्छेदों में स्वाभाविक रूप से आना चाहिए।
   - वाक्यों की लंबाई: 80% से अधिक वाक्य 10 से 16 शब्दों के हों। बात पूरी होते ही पूर्णविराम (।) लगाएं।
   - संयोजक शब्द: 'इसलिए', 'हालांकि', 'इसके अलावा', 'साथ ही', 'दरअसल', 'विशेष रूप से', 'परिणामस्वरूप' का सहज प्रयोग करें।
KWGUIDE;
        }

        // Specific Angle & Pillar Guidance
        $angle_instructions = '';

        if ( strpos( $topic_angle, 'muhurat' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: शुभ मुहूर्त, तिथि गणना एवं व्यावहारिक तैयारी]
- पहले पैराग्राफ में सीधे तारीख और पंचांग संदर्भ से शुरुआत करें। कोई नाटकीय मौसम या चांदनी का वर्णन न करें।
- सही तिथि का आरंभ और समापन समय, सटीक घंटे और मिनट के साथ दें। पंचांग भेद (तिथि को लेकर संशय) हो तो उसे शांत और स्पष्ट भाषा में सुलझाएं।
- घर में पूजन की व्यावहारिक तैयारी और आवश्यक सामग्री की सरल सूची दें।
- दिनचर्या और व्रत पालन के व्यावहारिक नियम बताएं।
GUIDE;
        } elseif ( strpos( $topic_angle, 'kundali' ) !== false || strpos( $topic_angle, 'remedies' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: ज्योतिषीय समझ, जीवनशैली और राशि उपाय]
- अंधविश्वास या चमत्कारी दावों से पूरी तरह बचें।
- इस तिथि के खगोलीय व मानसिक प्रभाव को समझाएं (मन की शांति, एकाग्रता, आत्म-संयम)।
- कुंडली के संदर्भ में शनि, राहु या चंद्रमा की शांति को सकारात्मक कर्मों और दान-पुण्य से जोड़कर समझाएं।
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
        } elseif ( strpos( $topic_angle, 'practical_astrology' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: व्यावहारिक पूजन गाइड + संतुलित ज्योतिषीय उपाय]
- तिथि, शुभ चौघड़िया मुहूर्त और पूजा का श्रेष्ठ समय (सटीक घंटे और मिनट)।
- संक्षिप्त व प्रमाणिक पूजन विधि और आवश्यक सामग्री।
- कुंडली के ग्रह दोष निवारण और राशि अनुसार 3 से 4 प्रमुख प्रभावशाली उपाय।
GUIDE;
        } elseif ( strpos( $topic_angle, 'spiritual_katha' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: पौराणिक कथा + इष्टदेव व सिद्ध तीर्थ]
- पावन व्रत कथा का संपूर्ण आध्यात्मिक व नैतिक रहस्य।
- इष्टदेव के कल्याणकारी स्वरूप का ध्यान और प्रभावशाली स्तोत्र।
- प्रसिद्ध तीर्थ स्थल व दर्शन की महिमा।
GUIDE;
        } elseif ( strpos( $topic_angle, 'master_all_in_one' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: ऑल-इन-वन संपूर्ण महा-गाइड]
संरचना स्पष्ट H2 शीर्षकों में होनी चाहिए:
1. तिथि और शुभ मुहूर्त (सटीक समय)
2. संपूर्ण पौराणिक व्रत कथा और इसका संदेश
3. पूजन सामग्री और स्टेप-बाय-स्टेप पूजन विधि
4. ज्योतिषीय महत्व: ग्रह शांति और राशि अनुसार उपाय
5. पारण के नियम और सावधानियां
GUIDE;
        } else {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: पर्व का समग्र सांस्कृतिक, पंचांगीय व व्यावहारिक विश्लेषण]
- तिथि, शुभ समय और पूजा का सही क्रम।
- सामाजिक व पारिवारिक महत्व।
- जीवनशैली में सकारात्मक बदलाव और सावधानियां।
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
तुम एक अत्यंत अनुभवी, वरिष्ठ हिंदी डिजिटल मैगज़ीन संपादक (Senior Digital Features Editor) हो। तुम्हारा कार्य नीचे दिए गए ड्राफ्ट को पूरी तरह से प्राकृतिक, मानवीय, AI-मुक्त (Zero AI Detectable) और Yoast Readability मानकों के अनुरूप परिष्कृत करना है।

संपादकीय जांच सूची (Anti-AI & Readability Polish Checklist):
1. 25 प्रतिबंधित AI मुहावरों को जड़ से हटाएं (CRITICAL):
   - यदि कहीं भी "का पावन पर्व है", "अत्यंत महत्वपूर्ण माना गया है", "अंधकार से प्रकाश की ओर", "परिवर्तन प्रकृति का नियम है", "अनुशासित जीवनशैली ही वास्तविक उद्देश्य है", "ऊर्जा के संचार का प्रतीक है", "जैसा कि हम सभी जानते हैं", "आइए जानते हैं", "चमत्कारी लाभ", "संजीवनी का काम करता है", "सुख-समृद्धि और खुशहाली आती है", "पापों का नाश होता है", "अखंड सौभाग्य", "मनोकामनाएं पूर्ण होती हैं", "सारे संकट दूर", "निष्कर्ष के रूप में", "आज के इस आधुनिक युग में", "ठिठुरती रातें" जैसी भाषा दिखे—तो उसे तुरंत हटाकर सहज, प्रौढ़ और विचारशील पत्रकारिता शैली में बदलें।
2. वाक्यों की लंबाई एवं बर्स्टीनेस (Burstiness & Human Rhythm):
   - 80% से अधिक वाक्य छोटे और मध्यम (10 से 16 शब्द) होने चाहिए।
   - 20 शब्दों से अधिक लंबे वाक्यों को दो छोटे वाक्यों में तोड़ें। बात पूरी होते ही तुरंत पूर्णविराम (।) लगाएं।
   - छोटे, सीधे और धारदार वाक्यों (3-6 शब्द) का प्रयोग करें (उदा: "संयम जरूरी है।", "दिखावे से बचें।", "नियम स्पष्ट हैं।")।
   - पैराग्राफ की लंबाई में स्वाभाविक विविधता रखें (1-3 वाक्य प्रति पैराग्राफ)।
3. स्वाभाविक संयोजक शब्द (Transition Words for Readability):
   - वाक्यों और विचारों के बीच स्वाभाविक प्रवाह के लिए हिंदी के संयोजक शब्दों (जैसे: इसलिए, हालांकि, इसके अलावा, साथ ही, दरअसल, विशेष रूप से, परिणामस्वरूप, इसके साथ ही) का सहज प्रयोग करें।
4. समकालीन स्वाभाविक हिंदी:
   - भाषा ऐसी लगे जैसे कोई वरिष्ठ पत्रकार या अनुभवी विद्वान पाठक से आमने-सामने बैठकर व्यावहारिक रूप से बात कर रहा हो।
5. तथ्यों और कीवर्ड की सुरक्षा:
   {$kw_guard}
   - सभी तारीखें, समय (सटीक घंटे-मिनट), मुहूर्त, मंत्र और उपयोगी जानकारियां पूरी तरह सुरक्षित रहें।

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
        $year = wp_date( 'Y' );
        $raw_default = ! empty( $preset_focus_kw ) ? $preset_focus_kw : ( $festival_name_hi ? $festival_name_hi . ' ' . $year : mb_substr( $topic_title_hi, 0, 25 ) );
        $default_kw = self::sanitize_focus_keyword( $raw_default, $festival_name_hi, $topic_title_hi );

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

    /**
     * Normalize URL to match current site's home domain (avoids localhost vs live domain issues)
     */
    public static function normalize_internal_url( $raw_url ) {
        if ( empty( $raw_url ) ) {
            return home_url();
        }
        $parsed = function_exists( 'wp_parse_url' ) ? wp_parse_url( $raw_url ) : parse_url( $raw_url );
        $path = ! empty( $parsed['path'] ) ? $parsed['path'] : '/';
        // Strip out local folder prefix if moved to live domain
        $clean_path = preg_replace( '#^/merarashifal#i', '', $path );
        return home_url( $clean_path );
    }

    /**
     * Inject internal links from mr_link_map into final markdown / HTML content
     * Fully compliant with Yoast SEO internal links detection
     */
    public static function inject_internal_links( $content ) {
        global $wpdb;
        $t_links = MRCB_DB_Setup::table_link_map();

        $links = ! empty( $wpdb ) ? $wpdb->get_results( "SELECT * FROM {$t_links} WHERE is_active = 1", ARRAY_A ) : [];

        // Fallback default core links if database link map table is empty
        if ( empty( $links ) ) {
            $links = [
                [ 'url' => home_url( '/panchang/' ), 'title_hi' => 'दैनिक हिंदू पंचांग', 'keywords_hi' => 'पंचांग, दैनिक पंचांग, शुभ मुहूर्त, आज का पंचांग, तिथि, नक्षत्र, चौघड़िया', 'link_type' => 'tool' ],
                [ 'url' => home_url( '/kundali-details/' ), 'title_hi' => 'जन्म कुंडली एवं जन्म पत्रिका', 'keywords_hi' => 'कुंडली, जन्मपत्री, जन्म कुंडली, ग्रह शांति', 'link_type' => 'tool' ],
                [ 'url' => home_url( '/dosh/mangal-dosh/' ), 'title_hi' => 'मांगलिक दोष जांच एवं उपाय', 'keywords_hi' => 'मांगलिक दोष, मंगल दोष, मांगलिक', 'link_type' => 'tool' ],
                [ 'url' => home_url( '/dosh/kaal-sarp-dosh/' ), 'title_hi' => 'कालसर्प दोष निवारण', 'keywords_hi' => 'कालसर्प दोष, काल सर्प', 'link_type' => 'tool' ],
                [ 'url' => home_url( '/horoscope/' ), 'title_hi' => 'दैनिक राशिफल', 'keywords_hi' => 'राशिफल, दैनिक राशिफल, राशि अनुसार, 12 राशियों', 'link_type' => 'tool' ],
                [ 'url' => home_url( '/festivals/' ), 'title_hi' => 'हिंदू व्रत एवं त्योहार कैलेंडर', 'keywords_hi' => 'त्योहार कैलेंडर, व्रत त्योहार, हिंदू व्रत, पावन पर्व', 'link_type' => 'tool' ],
            ];
        }

        $linked_urls = [];
        $max_injected_links = 3;
        $injected_count = 0;

        foreach ( $links as $link ) {
            if ( $injected_count >= $max_injected_links ) {
                break;
            }

            $clean_url = self::normalize_internal_url( $link['url'] );
            if ( in_array( $clean_url, $linked_urls, true ) ) {
                continue;
            }

            $keywords = array_filter( array_map( 'trim', explode( ',', $link['keywords_hi'] ) ) );
            foreach ( $keywords as $kw ) {
                if ( mb_strlen( $kw ) < 3 ) {
                    continue;
                }

                // Unicode-safe word boundary matching for Devanagari Hindi characters
                $pattern = '/(?<!\[)(?<!href=")(?<!href=\')(?<![\p{L}\p{N}])(' . preg_quote( $kw, '/' ) . ')(?![\p{L}\p{N}])(?![^<]*>)(?![^\[]*\])/u';

                if ( preg_match( $pattern, $content ) ) {
                    $replacement = '<a href="' . esc_url( $clean_url ) . '" title="' . esc_attr( $link['title_hi'] ) . '">$1</a>';
                    $content = preg_replace( $pattern, $replacement, $content, 1 );
                    $linked_urls[] = $clean_url;
                    $injected_count++;
                    break;
                }
            }
        }

        // Guaranteed In-Text Internal Links: If no keyword was matched, add an in-text editorial reference
        if ( $injected_count === 0 ) {
            $panchang_url = home_url( '/panchang/' );
            $kundali_url  = home_url( '/kundali-details/' );
            $editorial_callout = "\n\n> 💡 **संपादकीय सुझाव:** इस तिथि के शुभ मुहूर्त, चौघड़िया और पंचांग गणना की सटीक जानकारी के लिए हमारा [<a href=\"" . esc_url( $panchang_url ) . "\"><strong>दैनिक हिंदू पंचांग</strong></a>] देखें। यदि आप ग्रह दोष शांति के उपाय जानना चाहते हैं, तो अपनी [<a href=\"" . esc_url( $kundali_url ) . "\"><strong>जन्म कुंडली</strong></a>] का विश्लेषण अवश्य करें।\n\n";

            // Insert callout right after the first H2 heading
            if ( preg_match( '/(##\s+[^\n]+\n)/u', $content ) ) {
                $content = preg_replace( '/(##\s+[^\n]+\n)/u', '$1' . $editorial_callout, $content, 1 );
            } else {
                $content .= $editorial_callout;
            }
        }

        // Add styled "यह भी देखें" (Also Check) box at the bottom with normalized URLs
        $tools = array_filter( $links, function( $l ) { return ( $l['link_type'] ?? '' ) === 'tool'; } );
        if ( ! empty( $tools ) ) {
            shuffle( $tools );
            $selected_tools = array_slice( $tools, 0, 3 );
            $tool_box = "\n\n<div class=\"mrcb-tools-box\"><h4>🔮 मेरा राशिफल विशेष सेवाएँ:</h4><p>";
            $tool_links = [];
            foreach ( $selected_tools as $t ) {
                $clean_tool_url = self::normalize_internal_url( $t['url'] );
                $tool_links[] = "👉 <a href=\"" . esc_url( $clean_tool_url ) . "\"><strong>" . esc_html( $t['title_hi'] ) . "</strong></a>";
            }
            $tool_box .= implode( " &nbsp;•&nbsp; ", $tool_links ) . "</p></div>\n\n";
            $content .= $tool_box;
        }

        return $content;
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

        return $body_html . $faq_html;
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
        $pass1_text = $pass1_res['text'];

        // PASS 2: Humanizer
        $pass2_res = self::generate_pass2_humanizer( $pass1_text, $focus_kw );
        $pass2_text = $pass2_res['success'] ? $pass2_res['text'] : $pass1_text;

        // PASS 3: Entity Tagging
        $pass3_res = self::generate_pass3_entity_tagger( $pass2_text );
        $pass3_text = $pass3_res['success'] ? $pass3_res['text'] : $pass2_text;

        // Inject Internal Links
        $final_content = self::inject_internal_links( $pass3_text );

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
