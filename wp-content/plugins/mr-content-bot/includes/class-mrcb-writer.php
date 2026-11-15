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
        $candidate_models = array_unique( [ $active_model, 'gemini-3.5-flash-lite', 'gemini-3.1-flash-lite', 'gemini-3.5-flash' ] );

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
     * Senior Digital Magazine Editor Persona & Anti-AI Detection Editorial Manifesto
     */
    public static function get_editorial_persona() {
        return <<<PROMPT
You are a senior professional digital magazine editor and experienced cultural astrologer writing for MeraRashifal.com.

CORE EDITORIAL IDENTITY & VOICE:
- Tone: Trustworthy, warm, informative, and mature.
- Authority: The reader must feel that an experienced astrologer or seasoned digital magazine editor is explaining the topic with grounded clarity, genuine depth, and practical perspective.
- Avoid Sensationalism & Exaggerations: Completely avoid superstitious hyperbole (e.g. "चमत्कारी लाभ", "सारे संकट पल भर में दूर", "ब्रह्मांड की दुर्लभ ऊर्जाएं", "जिंदगी पूरी तरह बदल जाएगी", "संजीवनी का काम करता है"). Present rituals and astrology with dignity, emotional grounding, mental peace, and practical lifestyle discipline.
- Non-Promotional: Keep the article thoroughly engaging without sounding promotional, salesy, or commercial.
- Avoid Spiritual Clichés: Do NOT overuse tired spiritual clichés, dramatic mythological metaphors, or theatrical expressions.

ANTI-AI DETECTION MANIFESTO (CRITICAL FOR ZERO AI DETECTABILITY):
1. AVOID AI-STYLE INTRODUCTIONS:
   - STRICTLY FORBIDDEN: Melodramatic sensory/weather openings (e.g., "कार्तिक मास की ठिठुरती रातों में जब श्वेत चाँदनी आँगन को नहलाती है, धूप-दीप और कपूर की सुगंध...").
   - STRICTLY FORBIDDEN: Mechanical AI announcements ("आज हम जानेंगे...", "इस लेख में हम बताएंगे...", "आइए जानते हैं...", "नमस्कार पाठकों...", "जैसा कि हम सभी जानते हैं...").
   - HOW TO START: Open directly with the calendar context, why this specific date matters, common dilemmas people face (like tithi calculation or calendar differences), or the real-world significance of the tradition.

2. AVOID AI-STYLE CONCLUSIONS:
   - STRICTLY FORBIDDEN: Formulaic summaries ("निष्कर्ष के रूप में", "संक्षेप में कहें तो", "इस प्रकार", "उपरोक्त बातों से", "अतः यह कहा जा सकता है").
   - HOW TO END: Conclude naturally with a thoughtful practical tip, a balanced perspective on faith and self-discipline, or simple warm wishes.

3. HUMAN RHYTHM, SENTENCE LENGTH & YOAST READABILITY:
   - AI writes sentences of uniform length and predictable structure. You must break this pattern completely!
   - Sentence Length (Yoast Readability Rule): Over 80% of sentences must be concise (between 8 and 16 words). Do NOT drag compound sentences past 20 words. Whenever an idea is complete, place a full stop (।) immediately. Break long multi-clause thoughts into two crisp, punchy sentences.
   - Mix very short, crisp sentences (3 to 6 words) for emphasis. (e.g., "समय का ध्यान रखें।", "यह नियम काफी व्यावहारिक है।", "संयम ही इसका मूल आधार है।")
   - Transition Words (Yoast Readability Rule): Ensure natural sentence flow by incorporating standard Hindi transition connectors (e.g. "इसलिए", "हालांकि", "इसके अलावा", "साथ ही", "दरअसल", "विशेष रूप से", "परिणामस्वरूप", "इसके साथ ही", "वस्तुतः", "अतः") in 25% to 30% of sentences.
   - Vary paragraph sizes naturally: some paragraphs should be 1-2 sentences, some 3-4 sentences. Never stick to a rigid template!

4. NATURAL CONTEMPORARY HINDI (NOT TRANSLATED ENGLISH):
   - Write in authentic, fluent contemporary Hindi (सहज, व्यावहारिक, मानक हिंदी) as used by India's top publication editors (e.g. Navbharat Times, Dainik Bhaskar, Amar Ujala editorial).
   - Use natural terms: "तारीख", "तिथि", "समय", "मान्यता", "संयम", "तैयारी", "परंपरा", "दिनचर्या", "सावधानी", "पूजा स्थल".
   - Absolutely NO literal or mechanical translations from English (avoid "यह महत्वपूर्ण है कि आप ध्यान रखें", "यह याद रखना चाहिए").
   - Avoid repetitive wording across sentences.

5. FRESH INFORMATION IN EVERY SECTION:
   - Every single section (H2/H3) must introduce new facts, practical timings, specific steps, or genuine insights. Never repeat introductory points in subsequent sections.
PROMPT;
    }

    /**
     * PASS 1: Generate comprehensive Hindi draft tailored to Topic Angle with Anti-AI standards
     */
    public static function generate_pass1_draft( $topic_title_hi, $festival_name_hi, $topic_angle, $target_word_count = 1100, $focus_keyword = '', $festival_name_en = '' ) {
        $system = self::get_editorial_persona();

        $kw_instruction = '';
        if ( ! empty( $focus_keyword ) ) {
            $en_part = ! empty( $festival_name_en ) ? " ({$festival_name_en})" : '';
            $kw_instruction = <<<KWGUIDE
6. YOAST SEO एवं कीवर्ड नियम (Strict Yoast SEO Rules):
   - मुख्य फोकस कीवर्ड (Focus Keyphrase): "{$focus_keyword}"
   - पहले पैराग्राफ में कीवर्ड (Keyphrase in Introduction): लेख के सबसे पहले पैराग्राफ के पहले 1-2 वाक्यों में मुख्य कीवर्ड "{$focus_keyword}"{$en_part} को अनिवार्य रूप से शामिल करें (जैसे: "{$focus_keyword}{$en_part} का पावन पर्व...").
   - सबहेडिंग्स में कीवर्ड (Keyphrase in Subheadings): कम से कम 2 सबहेडिंग्स (H2) में मुख्य कीवर्ड "{$focus_keyword}" का स्वाभाविक प्रयोग करें (जैसे: "## {$focus_keyword} का शुभ मुहूर्त और तिथि", "## {$focus_keyword}: पूजन सामग्री और विधि").
   - कीवर्ड घनत्व (Keyphrase Density): पूरे लेख में यह कीवर्ड 4 से 6 बार अलग-अलग अनुच्छेदों में स्वाभाविक रूप से आना चाहिए (न ज्यादा, न कम).
   - वाक्यों की लंबाई (Sentence Length): 80% से अधिक वाक्य 10 से 16 शब्दों के होने चाहिए। किसी भी वाक्य को 20 शब्दों से अधिक लंबा न खींचें। जहाँ बात पूरी हो, तुरंत पूर्णविराम (।) लगाएं।
   - संयोजक शब्द (Transition Words): पठनीयता के लिए 'इसलिए', 'हालांकि', 'इसके अलावा', 'साथ ही', 'दरअसल', 'विशेष रूप से', 'परिणामस्वरूप', 'इसके साथ ही' का स्वाभाविक प्रयोग करें।
KWGUIDE;
        }

        // Specific Angle & Pillar Guidance
        $angle_instructions = '';

        if ( strpos( $topic_angle, 'muhurat' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस: शुभ मुहूर्त, तिथि गणना एवं व्यावहारिक तैयारी]
- पहले पैराग्राफ में सीधे तारीख और पंचांग संदर्भ से शुरुआत करें। कोई नाटकीय मौसम या चांदनी का वर्णन न करें।
- सही तिथि का आरंभ और समापन समय, प्रदोष काल व निशीथ काल की सटीक समय-सीमा दें। पंचांग भेद (तिथि को लेकर संशय) हो तो उसे शांत और स्पष्ट भाषा में सुलझाएं।
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
- तिथि, शुभ चौघड़िया मुहूर्त और पूजा का श्रेष्ठ समय।
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
1. तिथि और शुभ मुहूर्त
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
1. मुख्य शीर्षक (H1): स्पष्ट, गरिमापूर्ण और स्वाभाविक हिंदी शीर्षक।
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
            $kw_guard = "- मुख्य कीवर्ड '{$focus_keyword}' को पहले पैराग्राफ और सबहेडिंग्स में सुरक्षित रखें। इसे हटाएं नहीं।";
        }

        $prompt = <<<PROMPT
तुम एक अत्यंत अनुभवी, सीनियर डिजिटल मैगज़ीन संपादक (Senior Digital Magazine Editor) हो। तुम्हारा कार्य नीचे दिए गए ड्राफ्ट को पूरी तरह से प्राकृतिक, मानवीय, AI-मुक्त (Zero AI Detectable) और Yoast Readability मानकों के अनुरूप परिष्कृत करना है।

संपादकीय जांच सूची (Anti-AI & Readability Polish Checklist):
1. कृत्रिम नाटकीयता और अतिशयोक्ति को जड़ से मिटाएं:
   - यदि कहीं भी "ठिठुरती रातें", "श्वेत चांदनी", "अलौकिक शांति", "ब्रह्मांड की ऊर्जा", "दुर्लभ महासंयोग", "अचूक चमत्कार", "सारे कष्ट दूर" जैसी बनावटी या अतिशयोक्तिपूर्ण भाषा दिखे, तो उसे तुरंत हटाकर सहज, परिपक्व और प्रामाणिक शैली में लिखें।
2. वाक्यों की लंबाई एवं बर्स्टीनेस (Sentence Length & Human Rhythm):
   - 80% से अधिक वाक्य छोटे और मध्यम (10 से 16 शब्द) होने चाहिए।
   - 20 शब्दों से अधिक लंबे वाक्यों को दो छोटे वाक्यों में तोड़ें। बात पूरी होते ही तुरंत पूर्णविराम (।) लगाएं।
   - छोटे, सीधे और धारदार वाक्यों (3-6 शब्द) का प्रयोग करें, फिर मध्यम और विचारपूर्ण वाक्यों को लाएं।
   - पैराग्राफ की लंबाई में स्वाभाविक विविधता रखें (1-3 वाक्य प्रति पैराग्राफ)।
3. स्वाभाविक संयोजक शब्द (Transition Words for Readability):
   - वाक्यों और विचारों के बीच स्वाभाविक प्रवाह के लिए हिंदी के संयोजक शब्दों (जैसे: इसलिए, हालांकि, इसके अलावा, साथ ही, दरअसल, विशेष रूप से, परिणामस्वरूप, इसके साथ ही) का सहज प्रयोग करें।
4. समकालीन स्वाभाविक हिंदी:
   - भाषा ऐसी लगे जैसे कोई वरिष्ठ पत्रकार या अनुभवी विद्वान पाठक से आमने-सामने बैठकर व्यावहारिक रूप से बात कर रहा हो।
   - अनुवादित या किताबी हिंदी न हो।
5. मशीनी कनेक्टर्स हटाएं:
   - "इस लेख में", "आइए जानते हैं", "निष्कर्ष के रूप में", "इस प्रकार", "जैसा कि हम जानते हैं", "अतः यह कहा जा सकता है" जैसे मशीनी कनेक्टर्स को पूरी तरह हटा दें।
6. नए तथ्यों का प्रवाह:
   - हर पैराग्राफ और हेडिंग में नई, उपयोगी जानकारी हो। दोहराव न हो।
7. तथ्यों और कीवर्ड की सुरक्षा:
   {$kw_guard}
   - सभी तारीखें, समय, मुहूर्त, मंत्र और उपयोगी जानकारियां पूरी तरह सुरक्षित रहें।

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
        $default_kw = ! empty( $preset_focus_kw ) ? $preset_focus_kw : ( $festival_name_hi ? $festival_name_hi . ' ' . $year : mb_substr( $topic_title_hi, 0, 25 ) );

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
  "focus_keyword": "हिंदी में 2 से 4 शब्दों का मुख्य कीवर्ड जो लेख का सटीक विषय हो (उदा: {$default_kw})",
  "meta_title": "SEO टाइटल 50-60 अक्षरों के अंदर जो अनिवार्य रूप से मुख्य कीवर्ड से शुरू हो (उदा: {$default_kw}: शुभ मुहूर्त, पूजन विधि और उपाय | मेरा राशिफल)",
  "meta_desc": "SEO मेटा विवरण ठीक 130-150 अक्षरों में जिसमें मुख्य कीवर्ड शुरू में आए (कड़ाई से 154 अक्षरों से छोटा रखें)",
  "slug": "clean-english-url-slug-strictly-in-english-without-hindi-characters",
  "tags": "strictly-english-only-comma-separated-tags"
}

महत्वपूर्ण नियम:
1. "focus_keyword" वही हिंदी शब्द होना चाहिए जो लेख के शीर्षक, पहले पैराग्राफ और सबहेडिंग में मौजूद है (उदा: {$default_kw})।
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
        $focus_kw = trim( $data['focus_keyword'] ?? '' );
        if ( empty( $focus_kw ) ) {
            $focus_kw = $default_kw;
        }
        $data['focus_keyword'] = $focus_kw;

        // 2. SEO Title: Must start with focus keyword & stay under 60 characters
        $meta_title = trim( $data['meta_title'] ?? '' );
        if ( empty( $meta_title ) ) {
            $meta_title = $focus_kw . ': शुभ मुहूर्त, पूजन विधि और उपाय | मेरा राशिफल';
        } elseif ( mb_strpos( $meta_title, $focus_kw ) === false ) {
            $meta_title = $focus_kw . ': ' . $meta_title;
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
        // 1. Strip out any top H1 (# Title) since WordPress uses the post title as H1
        $text = preg_replace( '/^#\s+.+$\n+/m', '', $markdown );

        // 2. Normalize line breaks
        $text = str_replace( ["\r\n", "\r"], "\n", $text );

        // 3. Headings: H2, H3, H4
        $text = preg_replace( '/^####\s+(.+)$/m', '<h4>$1</h4>', $text );
        $text = preg_replace( '/^###\s+(.+)$/m', '<h3>$1</h3>', $text );
        $text = preg_replace( '/^##\s+(.+)$/m', '<h2>$1</h2>', $text );

        // 4. Horizontal rules
        $text = preg_replace( '/^---$/m', '<hr />', $text );

        // 5. Parse unordered bullet lists (* item or - item) BEFORE touching inline asterisks!
        // We match blocks of lines that start with * or - and wrap them in <ul><li>...</li></ul>
        $text = preg_replace_callback( '/((?:^[ \t]*[\*\-][ \t]+[^\n]+(?:\n|$))+)/m', function( $matches ) {
            $lines = explode( "\n", trim( $matches[1] ) );
            $items = [];
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( empty( $line ) ) continue;
                $clean = preg_replace( '/^[\*\-][ \t]+/', '', $line );
                $items[] = '  <li>' . $clean . '</li>';
            }
            return "<ul>\n" . implode( "\n", $items ) . "\n</ul>\n";
        }, $text );

        // 6. Parse ordered numbered lists (1. item)
        $text = preg_replace_callback( '/((?:^[ \t]*\d+\.[ \t]+[^\n]+(?:\n|$))+)/m', function( $matches ) {
            $lines = explode( "\n", trim( $matches[1] ) );
            $items = [];
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( empty( $line ) ) continue;
                $clean = preg_replace( '/^\d+\.[ \t]+/', '', $line );
                $items[] = '  <li>' . $clean . '</li>';
            }
            return "<ol>\n" . implode( "\n", $items ) . "\n</ol>\n";
        }, $text );

        // 7. Bold entities: **term**
        $text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );

        // 8. Italic entities / mantras: *term* or _term_ (ensuring no clobbering)
        $text = preg_replace( '/(?<!\w)\*([^\*\n]+?)\*(?!\w)/u', '<em>$1</em>', $text );
        $text = preg_replace( '/(?<!\w)_([^\_\n]+?)_(?!\w)/u', '<em>$1</em>', $text );

        // 9. Markdown links [text](url)
        $text = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text );

        // 10. Standalone sacred mantra lines: detect blocks that are solely mantras (e.g. <em>ॐ...</em>)
        $text = preg_replace( '/^[ \t]*<em>(ॐ[^<\n]+)<\/em>[ \t]*$/mu', '<div class="mrcb-mantra-box"><em>$1</em></div>', $text );

        // 11. Split into blocks and process FAQ cards vs normal paragraphs
        $blocks = preg_split( '/\n\s*\n/', trim( $text ) );
        $html_blocks = [];
        $in_faq_mode = false;
        $faq_cards = [];

        foreach ( $blocks as $block ) {
            $block = trim( $block );
            if ( empty( $block ) ) {
                continue;
            }

            // Check if this block is an FAQ heading
            if ( preg_match( '/<h[23]>[^<]*(?:FAQ|अक्सर पूछे जाने वाले प्रश्न|बार-बार पूछे जाने वाले प्रश्न|प्रश्नोत्तरी)[^<]*<\/h[23]>/iu', $block ) ) {
                $in_faq_mode = true;
                $html_blocks[] = '<div class="mrcb-faq-section"><h3 class="mrcb-faq-header">अक्सर पूछे जाने वाले प्रश्न (FAQ)</h3>';
                continue;
            }

            // If in FAQ mode, check if block contains Question / Answer
            if ( $in_faq_mode ) {
                if ( preg_match( '/(?:<strong>)?(?:प्रश्न|Q|Q\d+)[ :\-]+(.*?)(?:<\/strong>)?(?:\n|<br\s*\/?>)\s*(?:उत्तर|Ans|A)[ :\-]+(.*)/su', $block, $faq_m ) ) {
                    $q = trim( strip_tags( $faq_m[1] ) );
                    $a = trim( $faq_m[2] );
                    $faq_cards[] = '<div class="mrcb-faq-card"><div class="mrcb-faq-q">❓ ' . $q . '</div><div class="mrcb-faq-a">' . $a . '</div></div>';
                    continue;
                } else {
                    // Not an FAQ card or FAQ section ended
                    if ( ! empty( $faq_cards ) ) {
                        $html_blocks[] = implode( "\n", $faq_cards ) . "\n</div>";
                        $faq_cards = [];
                        $in_faq_mode = false;
                    }
                }
            }

            // If block is already a block element like <h2>, <h3>, <ul>, <ol>, <hr>, <div>, <blockquote>
            if ( preg_match( '/^<(h2|h3|h4|hr|ul|ol|div|blockquote)/i', $block ) ) {
                $html_blocks[] = $block;
            } else {
                // Regular paragraph with generous line breaks and spacing class
                $html_blocks[] = '<p class="mrcb-article-p">' . nl2br( $block ) . '</p>';
            }
        }

        // Close FAQ section if still open at end of text
        if ( $in_faq_mode && ! empty( $faq_cards ) ) {
            $html_blocks[] = implode( "\n", $faq_cards ) . "\n</div>";
        }

        return implode( "\n\n", $html_blocks );
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

        $year = wp_date( 'Y' );
        // Determine primary focus keyword upfront for consistency across title, introduction, subheadings & Yoast
        $focus_kw = ! empty( $item['focus_keyword'] ) ? trim( $item['focus_keyword'] ) : '';
        if ( empty( $focus_kw ) ) {
            $focus_kw = ! empty( $festival_name_hi ) ? ( $festival_name_hi . ' ' . $year ) : mb_substr( $item['topic_title_hi'], 0, 25 );
        }

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
