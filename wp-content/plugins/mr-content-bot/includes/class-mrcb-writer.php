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
     * System Persona "Meera" with Strict Non-AI & Natural Hindi Manifesto
     */
    public static function get_meera_persona() {
        return <<<PROMPT
तुम 'मीरा' हो — MeraRashifal.com की मुख्य सांस्कृतिक एवं वैदिक लेखिका।
तुम्हारा लेखन 100% स्वाभाविक, आत्मीय, मानवीय और रसमय होना चाहिए। ऐसा प्रतीत हो जैसे कोई सुविज्ञ, स्नेही और सुसंस्कृत भारतीय महिला अपने पाठकों से सीधे हृदय से संवाद कर रही हो।

🚫 कड़े निषेध (STRICTLY FORBIDDEN - AI CLICHÉS):
1. कभी भी मशीनी घोषणाओं से शुरुआत न करें:
   - "आज हम जानेंगे...", "इस लेख में हम बताएंगे...", "आइए जानते हैं...", "नमस्कार दोस्तों...", "जैसा कि हम सभी जानते हैं..."
2. रोबोटिक निबंध वाले कनेक्टर्स पूरी तरह प्रतिबंधित हैं:
   - "निष्कर्ष के रूप में", "संक्षेप में कहें तो", "इस प्रकार", "उपरोक्त बातों से", "आइए अब चर्चा करते हैं", "यह स्पष्ट है कि", "अतः यह कहा जा सकता है"
3. घिसे-पिटे अनुवादित वाक्य न लिखें:
   - "यह महत्वपूर्ण है कि आप ध्यान रखें", "यह याद रखना चाहिए", "धूमधाम से मनाया जाता है"
4. हर बुलेट पॉइंट को एक ही शब्द से शुरू करने वाली मशीनी सूचियां न बनाएं।

✨ आवश्यक मानवीय एवं प्राकृतिक लेखन तत्व (HUMAN WRITING PRINCIPLES):
1. भावपूर्ण व संवेदी शुरुआत (Sensory Hook): लेख का पहला पैराग्राफ त्योहार की जीवंत अनुभूति, प्रातःकाल की शीतलता, धूप-दीप और कपूर की सुगंध, ताज़े गेंदे के फूलों की महक या बचपन की पारिवारिक स्मृति से शुरू हो।
2. सहज वाक्य विन्यास: वाक्य न तो बहुत लंबे और जटिल हों, न ही रोबोट की तरह छोटे-छोटे और नीरस। वाक्यों में एक स्वाभाविक लय (cadence) और मिठास हो।
3. संक्षिप्त पैराग्राफ: प्रत्येक पैराग्राफ केवल 2 से 3 वाक्यों का हो। इससे मोबाइल पर पढ़ना बेहद आसान रहता है।
4. वैदिक प्रामाणिकता व श्लोक: जहाँ संदर्भ आए, वहाँ प्रामाणिक संस्कृत मंत्र या श्लोक *इटैलिक* में दें और तुरंत उसका सरल, हृदयस्पर्शी हिंदी अर्थ समझाएं।
5. ज्योतिषीय सूक्ष्मता (MeraRashifal USP):
   - केवल सामान्य बातें न कहें, बल्कि बताएं कि कुंडली का कौन-सा भाव (Bhav), कौन-सा ग्रह (जैसे शनि, मंगल, शुक्र, चंद्रमा, राहु-केतु) इस पर्व से प्रभावित होता है।
   - व्यावहारिक और सात्विक उपाय बताएं (जैसे विशेष रंग, अन्न दान, दीप प्रज्वलन की दिशा)।
PROMPT;
    }

    /**
     * PASS 1: Generate comprehensive Hindi draft tailored to Topic Angle and 4 Pillars
     */
    public static function generate_pass1_draft( $topic_title_hi, $festival_name_hi, $topic_angle, $target_word_count = 1200 ) {
        $system = self::get_meera_persona();

        // Specific Angle & Pillar Guidance
        $angle_instructions = '';

        if ( strpos( $topic_angle, 'muhurat' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - स्तंभ 3: शुभ मुहूर्त, तिथि एवं तैयारी]
- पहले पैराग्राफ में पर्व के आगमन की आहट और शुभ बेला का सुंदर चित्रण करें।
- सही तिथि, आरंभ व समाप्ति समय, प्रदोष काल, स्थिर लग्न (वृषभ/सिंह) और अमृत चौघड़िया मुहूर्त का स्पष्ट उल्लेख करें।
- आवश्यक पूजन सामग्री की व्यावहारिक सूची (रोली, मौली, अक्षत, पंचामृत, ऋतुफल, दीपक) दें।
- घर की शुद्धि और प्रातःकाल की पूर्व-तैयारी के नियम बताएं।
GUIDE;
        } elseif ( strpos( $topic_angle, 'kundali' ) !== false || strpos( $topic_angle, 'remedies' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - स्तंभ 4: कुंडली ग्रह दोष निवारण एवं 12 राशि उपाय - MeraRashifal USP]
- इस पावन तिथि के विशेष खगोलीय व ज्योतिषीय प्रभाव को समझाएं।
- कुंडली के किन-किन दोषों (जैसे शनि की साढ़ेसाती/ढैय्या, मंगल दोष, कालसर्प दोष, राहु-केतु पीड़ा, या दांपत्य भाव दोष) में यह पर्व संजीवनी का काम करता है।
- मेष से लेकर मीन तक सभी 12 राशियों के लिए विशिष्ट, सात्विक व अचूक उपाय (क्या दान करें, कौन-सा मंत्र जपें, किस रंग के वस्त्र पहनें)।
- क्या न करें (सावधानियां) ताकि पूजा का पूर्ण फल मिले।
GUIDE;
        } elseif ( strpos( $topic_angle, 'temples' ) !== false || strpos( $topic_angle, 'deity' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - स्तंभ 2: इष्टदेव का स्वरूप, बीज मंत्र एवं सिद्ध मंदिर]
- इष्टदेव के अलौकिक, करुणामयी और वरद स्वरूप का भक्तिमय वर्णन।
- प्रमुख कल्याणकारी बीज मंत्र और स्तुति, उनके अर्थ सहित।
- भारतवर्ष में स्थित 5 प्रमुख चमत्कारी व प्राचीन सिद्ध मंदिरों का परिचय, जहाँ इस पर्व पर विशेष ऊर्जा प्रवाहित होती है।
- घर के मंदिर में देवता का आवाहन व ध्यान कैसे करें।
GUIDE;
        } elseif ( strpos( $topic_angle, 'vidhi_katha' ) !== false || strpos( $topic_angle, 'katha' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - स्तंभ 1 व 3: संपूर्ण पौराणिक व्रत कथा, पूजन विधि व आरती]
- पारंपरिक कथा वाचन की शैली में संपूर्ण प्रामाणिक व्रत कथा (पात्रों के संवाद और जीवन संदेश सहित)।
- स्टेप-बाय-स्टेप षोडशोपचार पूजन विधि (आवाहन से लेकर पुष्पांजलि तक)।
- व्रत का पारण (व्रत खोलने) का सटीक समय और सात्विक भोजन के नियम।
- पावन आरती और क्षमा प्रार्थना मंत्र।
GUIDE;
        } elseif ( strpos( $topic_angle, 'practical_astrology' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - Tier 2 स्तंभ 3 व 4: व्यावहारिक पूजन गाइड + ज्योतिषीय उपाय]
- तिथि, शुभ चौघड़िया मुहूर्त और पूजा का श्रेष्ठ समय।
- संक्षिप्त व प्रमाणिक पूजन विधि और आवश्यक सामग्री।
- कुंडली के ग्रह दोष निवारण और राशि अनुसार 3 से 4 प्रमुख प्रभावशाली उपाय।
GUIDE;
        } elseif ( strpos( $topic_angle, 'spiritual_katha' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - Tier 2 स्तंभ 1 व 2: पौराणिक कथा + इष्टदेव व सिद्ध तीर्थ]
- पावन व्रत कथा का संपूर्ण आध्यात्मिक व नैतिक रहस्य।
- इष्टदेव के कल्याणकारी स्वरूप का ध्यान और प्रभावशाली स्तोत्र।
- प्रसिद्ध तीर्थ स्थल व दर्शन की महिमा।
GUIDE;
        } elseif ( strpos( $topic_angle, 'master_all_in_one' ) !== false ) {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - Tier 3: ऑल-इन-वन संपूर्ण महा-गाइड (चारों स्तंभों का समन्वय)]
संरचना स्पष्ट H2 शीर्षकों में होनी चाहिए:
1. तिथि और शुभ मुहूर्त (Pillar 3)
2. संपूर्ण पौराणिक व्रत कथा और इसका संदेश (Pillar 1)
3. पूजन सामग्री और स्टेप-बाय-स्टेप पूजन विधि (Pillar 2 + 3)
4. ज्योतिषीय महत्व: कुंडली ग्रह शांति और राशि अनुसार उपाय (Pillar 4 - MeraRashifal USP)
5. पारण के नियम और सावधानियां
GUIDE;
        } else {
            $angle_instructions = <<<GUIDE
[मुख्य फोकस - सांस्कृतिक व ज्योतिषीय समग्र विश्लेषण]
- पर्व की सांस्कृतिक जड़ें, लोकपरंपराएं और सही मुहूर्त।
- पारिवारिक व सामाजिक महत्व।
- राशि अनुसार कल्याणकारी उपाय और सावधानियां।
GUIDE;
        }

        $prompt = <<<PROMPT
विषय: "{$topic_title_hi}"
त्योहार / संदर्भ: "{$festival_name_hi}"
दृष्टिकोण (Angle): "{$topic_angle}"
लक्ष्य शब्द संख्या: लगभग {$target_word_count} शब्द

विशेष दिशा-निर्देश:
{$angle_instructions}

आवश्यक संरचना:
1. मुख्य शीर्षक (H1): आकर्षक, भावनात्मक एवं SEO-अनुकूल हिंदी शीर्षक।
2. भावपूर्ण प्रस्तावना: 2-3 पंक्तियों में पर्व के उल्लास, वातावरण या आध्यात्मिक भाव का सजीव चित्रण। (रोबोटिक घोषणाएं न लिखें!)
3. मुख्य विषयवस्तु (3 से 5 H2 शीर्षकों में): विषय के अनुसार गहन, रोचक और प्रमाणिक विवरण। प्रत्येक पैराग्राफ अधिकतम 2-3 वाक्यों का हो।
4. आत्मीय समापन: पाठक और उनके परिवार के लिए मंगलकामना भरा संदेश।
5. बार-बार पूछे जाने वाले प्रश्न (FAQ): ठीक 3 प्रश्न और उनके सटीक, संक्षिप्त उत्तर (प्रत्येक उत्तर 2-3 वाक्यों में)।

प्रारूप: Markdown में लिखें। (शीर्षकों के लिए #, ##, ### का प्रयोग करें)।
PROMPT;

        return self::call_gemini( $prompt, $system, 0.7, 3500 );
    }

    /**
     * PASS 2: Senior Hindi Editor Humanizer - Aggressively eradicate AI cadence & restore natural soul
     */
    public static function generate_pass2_humanizer( $raw_draft ) {
        $prompt = <<<PROMPT
तुम एक अत्यंत वरिष्ठ, प्रबुद्ध भारतीय हिंदी संपादक हो। तुम्हारा कार्य नीचे दिए गए आलेख को पूरी तरह 'ह्यूमानाइज़' (Humanize) करना है ताकि इसमें किसी भी मशीन या AI का लेशमात्र भी आभास न रहे।

संपादकीय जांच सूची (Strict Editorial Checklist):
1. मशीन जैसी भाषा हटाओ: यदि कहीं भी "इस लेख में", "आइए जानते हैं", "निष्कर्ष", "संक्षेप में", "इस प्रकार", "जैसा कि हम जानते हैं" दिखे, तो उसे पूरी तरह हटाकर सहज, आत्मीय भाषा लाएं।
2. पैराग्राफ को छोटा और सुगम बनाओ: सुनिश्चित करो कि कोई भी पैराग्राफ 2 से 3 वाक्यों से बड़ा न हो। बड़े अनुच्छेदों को तुरंत तोड़ें।
3. वाक्यों का स्वाभाविक प्रवाह: भाषा ऐसी लगे जैसे कोई अपना स्नेही, ज्ञानी व्यक्ति बैठकर प्रेमपूर्वक समझा रहा हो।
4. तथ्यों की सुरक्षा: सभी तिथियां, मुहूर्त के समय, मंत्र, कथा का सार और FAQ यथावत रहें।
5. केवल और केवल शुद्ध परिष्कृत (Humanized) Markdown आलेख ही लौटाएं; कोई अपनी भूमिका, 'यहाँ है आपका लेख' जैसी टिप्पणी बिल्कुल न लिखें।

मूल आलेख:
{$raw_draft}
PROMPT;

        return self::call_gemini( $prompt, '', 0.6, 3500 );
    }

    /**
     * PASS 3: Entity Tagging pass - bold deities/places/festivals, italicize mantras
     */
    public static function generate_pass3_entity_tagger( $humanized_draft ) {
        $prompt = <<<PROMPT
नीचे दिए गए हिंदी लेख में सनातन संस्कृति के विशिष्ट तत्वों (Entities) को निम्नलिखित नियमों के अनुसार Markdown फॉर्मेट में हाईलाइट करें:

नियम:
1. देवी-देवताओं के नाम **बोल्ड** करें (जैसे: **माँ लक्ष्मी**, **भगवान श्रीकृष्ण**, **श्री गणेश**, **हनुमान जी**, **भगवान शिव**)।
2. प्रमुख त्योहारों एवं तिथियों के नाम **बोल्ड** करें (जैसे: **दीपावली**, **धनतेरस**, **कार्तिक अमावस्या**, **प्रदोष काल**)।
3. पावन तीर्थों एवं ऐतिहासिक/पौराणिक स्थानों के नाम **बोल्ड** करें (जैसे: **अयोध्या**, **काशी**, **मथुरा**, **वृंदावन**, **हरिद्वार**)।
4. सभी संस्कृत श्लोक, बीज मंत्र एवं स्तोत्र पंक्तियों को *इटैलिक* करें (जैसे: *ॐ महालक्ष्म्यै नमः*, *ॐ नमो भगवते वासुदेवाय*)।
5. महत्वपूर्ण पूजन विधियों व संस्कारों को **बोल्ड** करें (जैसे: **षोडशोपचार पूजा**, **दीपदान**, **आरती**)।
6. लेख की संरचना, शीर्षकों और शब्दों में कोई भी बदलाव न करें; केवल ऊपर बताए गए अनुसार बोल्ड (`**...**`) और इटैलिक (`*...*`) टैग्स लगाएं।

आलेख:
{$humanized_draft}

कृपया केवल टैग किया हुआ पूर्ण आलेख ही लौटाएं, कोई अन्य व्याख्या न लिखें।
PROMPT;

        return self::call_gemini( $prompt, '', 0.3, 3500 );
    }

    /**
     * Generate SEO metadata (Focus Keyword, Meta Title, Meta Description, English Slug, English Tags)
     */
    public static function generate_seo_metadata( $topic_title_hi, $festival_name_hi, $final_content ) {
        $year = wp_date( 'Y' );
        $prompt = <<<PROMPT
निम्नलिखित हिंदी ब्लॉग पोस्ट के लिए Yoast SEO अनुकूल मेटाडाटा तैयार करें:

विषय: {$topic_title_hi}
पर्व: {$festival_name_hi}
वर्ष: {$year}

सामग्री का संक्षिप्त अंश:
"""
{$final_content}
"""

कृपया JSON फॉर्मेट में उत्तर दें:
{
  "focus_keyword": "अंग्रेजी या हिंग्लिश में मुख्य कीवर्ड (उदा: dhanteras 2026 या diwali 2026 puja vidhi)",
  "meta_title": "SEO टाइटल 60 अक्षरों के अंदर (उदा: धनतेरस 2026: शुभ मुहूर्त, पूजन विधि और आरती | मेरा राशिफल)",
  "meta_desc": "SEO मेटा विवरण 150-160 अक्षरों में जिसमें मुख्य कीवर्ड स्वाभाविक रूप से शामिल हो",
  "slug": "clean-english-url-slug-strictly-in-english-without-hindi-characters (e.g. dhanteras-2026-shubh-muhurat-puja-vidhi)",
  "tags": "strictly-english-only-comma-separated-tags (e.g. dhanteras 2026, dhanteras puja vidhi, shubh muhurat, diwali festival, katha)"
}

महत्वपूर्ण नियम:
1. "tags" strictly 100% ENGLISH ONLY (lowercase words separated by comma). Never write Hindi or Devanagari in tags!
2. "slug" strictly clean English words separated by hyphens.
3. केवल शुद्ध JSON लौटाएं। कोई Markdown बैक-टिक्स या अतिरिक्त टेक्स्ट न लिखें।
PROMPT;

        $res = self::call_gemini( $prompt, '', 0.4, 800 );
        if ( ! $res['success'] ) {
            return false;
        }

        $raw_json = trim( $res['text'] );
        // Strip markdown fences if present
        $raw_json = preg_replace( '/^```json\s*/i', '', $raw_json );
        $raw_json = preg_replace( '/^```\s*/i', '', $raw_json );
        $raw_json = preg_replace( '/\s*```$/', '', $raw_json );

        $data = json_decode( $raw_json, true );
        if ( ! is_array( $data ) ) {
            $fest_slug = sanitize_title( ! empty( $festival_name_hi ) ? $festival_name_hi : 'festival' );
            return [
                'focus_keyword' => $fest_slug . ' ' . $year,
                'meta_title'    => mb_substr( $topic_title_hi, 0, 55 ) . ' | MeraRashifal',
                'meta_desc'     => mb_substr( strip_tags( $final_content ), 0, 155 ),
                'slug'          => $fest_slug . '-' . $year . '-puja-vidhi',
                'tags'          => $fest_slug . ', ' . $fest_slug . ' ' . $year . ', puja vidhi, shubh muhurat, festivals'
            ];
        }

        // Code-level sanitization: Guarantee tags are 100% English
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
                $fest_slug = sanitize_title( $festival_name_hi ?: 'festival' );
                $data['tags'] = $fest_slug . ' ' . $year . ', puja vidhi, shubh muhurat, festivals';
            }
        }

        // Guarantee slug is clean English
        if ( ! empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['slug'] );
        }

        return $data;
    }

    /**
     * Inject internal links from mr_link_map into final markdown / HTML content
     */
    public static function inject_internal_links( $content ) {
        global $wpdb;
        $t_links = MRCB_DB_Setup::table_link_map();

        $links = $wpdb->get_results( "SELECT * FROM {$t_links} WHERE is_active = 1", ARRAY_A );
        if ( empty( $links ) ) {
            return $content;
        }

        $linked_urls = [];
        $max_injected_links = 3;
        $injected_count = 0;

        foreach ( $links as $link ) {
            if ( $injected_count >= $max_injected_links ) {
                break;
            }

            if ( in_array( $link['url'], $linked_urls, true ) ) {
                continue;
            }

            $keywords = array_filter( array_map( 'trim', explode( ',', $link['keywords_hi'] ) ) );
            foreach ( $keywords as $kw ) {
                if ( mb_strlen( $kw ) < 3 ) {
                    continue;
                }

                // Match first occurrence outside markdown headings (#) and existing links
                $pattern = '/(?<!\[)(?<!href=")(?<!href=\')\b(' . preg_quote( $kw, '/' ) . ')\b(?![^<]*>)(?![^\[]*\])/u';

                if ( preg_match( $pattern, $content ) ) {
                    $replacement = '<a href="' . esc_url( $link['url'] ) . '" title="' . esc_attr( $link['title_hi'] ) . '">$1</a>';
                    $content = preg_replace( $pattern, $replacement, $content, 1 );
                    $linked_urls[] = $link['url'];
                    $injected_count++;
                    break;
                }
            }
        }

        // Add a styled "यह भी देखें" (Also Check) box at the bottom if tool links exist
        $tools = array_filter( $links, function( $l ) { return $l['link_type'] === 'tool'; } );
        if ( ! empty( $tools ) ) {
            shuffle( $tools );
            $selected_tools = array_slice( $tools, 0, 3 );
            $tool_box = "\n\n<div class=\"mrcb-tools-box\"><h4>🔮 मेरा राशिफल विशेष सेवाएँ:</h4><p>";
            $tool_links = [];
            foreach ( $selected_tools as $t ) {
                $tool_links[] = "👉 <a href=\"" . esc_url( $t['url'] ) . "\"><strong>" . esc_html( $t['title_hi'] ) . "</strong></a>";
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
        if ( ! empty( $item['festival_id'] ) ) {
            $fest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fest} WHERE id = %d", $item['festival_id'] ), ARRAY_A );
            if ( $fest ) {
                $festival_name_hi = $fest['festival_name_hi'] ?: $fest['festival_name_en'];
            }
        }

        // PASS 1: Raw Draft
        $pass1_res = self::generate_pass1_draft(
            $item['topic_title_hi'],
            $festival_name_hi,
            $item['topic_angle'] ?: 'katha',
            $item['word_count_target'] ?: 950
        );
        if ( ! $pass1_res['success'] ) {
            return [ 'success' => false, 'error' => 'Pass 1 failed: ' . $pass1_res['error'] ];
        }
        $pass1_text = $pass1_res['text'];

        // PASS 2: Humanizer
        $pass2_res = self::generate_pass2_humanizer( $pass1_text );
        $pass2_text = $pass2_res['success'] ? $pass2_res['text'] : $pass1_text;

        // PASS 3: Entity Tagging
        $pass3_res = self::generate_pass3_entity_tagger( $pass2_text );
        $pass3_text = $pass3_res['success'] ? $pass3_res['text'] : $pass2_text;

        // Inject Internal Links
        $final_content = self::inject_internal_links( $pass3_text );

        // Generate SEO Meta
        $seo_meta = self::generate_seo_metadata( $item['topic_title_hi'], $festival_name_hi, $final_content );

        $focus_kw   = $seo_meta['focus_keyword'] ?? ( $festival_name_hi . ' ' . wp_date( 'Y' ) );
        $meta_title = $seo_meta['meta_title'] ?? mb_substr( $item['topic_title_hi'], 0, 60 );
        $meta_desc  = $seo_meta['meta_desc'] ?? mb_substr( strip_tags( $final_content ), 0, 155 );
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
