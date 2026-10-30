<?php
/**
 * Niyati Intelligent Ethical Guardrail Engine (Tier 1 & Tier 2 Protection)
 * 
 * Intercepts sensitive, out-of-bounds questions across 6 ethical categories:
 * 1. Medical, Health, & Pregnancy Predictions
 * 2. Financial, Wealth, & Trading Speculation
 * 3. Legal, Litigation, & Criminal Matters
 * 4. Relationships, Infidelity, & Absolute Breakups
 * 5. Fear, Superstition, & "Doom" Astrological Curses
 * 6. Third-Party Privacy & Control
 * 
 * Policy: Guarded queries are answered with supportive framing and professional boundaries.
 * CRUCIAL: NO credits or quotas are deducted for guarded queries.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Guardrails {

    const CAT_IDENTITY      = 'identity';
    const CAT_MEDICAL       = 'medical';
    const CAT_FINANCIAL     = 'financial';
    const CAT_LEGAL         = 'legal';
    const CAT_RELATIONSHIPS = 'relationships';
    const CAT_SUPERSTITION  = 'superstition';
    const CAT_PRIVACY       = 'privacy';

    /**
     * Check if user message triggers any Tier 1 guardrail
     *
     * @param string $message
     * @param string $lang 'en', 'hi', or 'hinglish'
     * @return array [triggered => bool, category => string|null, reply => string]
     */
    public static function check_message($message, $lang = 'en') {
        $text = trim($message);
        if (empty($text)) {
            return ['triggered' => false, 'category' => null, 'reply' => ''];
        }

        $normalized = mb_strtolower($text, 'UTF-8');

        // 0. Identity & Nature ("Are you AI", "Are you a real person", "Who are you")
        if (self::matches_identity($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_IDENTITY,
                'reply'     => self::get_response(self::CAT_IDENTITY, $lang, $normalized)
            ];
        }

        // 1. Medical & Pregnancy (Highest Ethical & Legal Priority)
        if (self::matches_medical($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_MEDICAL,
                'reply'     => self::get_response(self::CAT_MEDICAL, $lang, $normalized)
            ];
        }

        // 2. Financial, Wealth & Speculation (Stocks, Crypto, Lottery, Gambling)
        if (self::matches_financial($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_FINANCIAL,
                'reply'     => self::get_response(self::CAT_FINANCIAL, $lang, $normalized)
            ];
        }

        // 3. Legal, Litigation & Criminal Matters
        if (self::matches_legal($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_LEGAL,
                'reply'     => self::get_response(self::CAT_LEGAL, $lang, $normalized)
            ];
        }

        // 4. Relationships, Infidelity & Accusations
        if (self::matches_relationships($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_RELATIONSHIPS,
                'reply'     => self::get_response(self::CAT_RELATIONSHIPS, $lang, $normalized)
            ];
        }

        // 5. Fear, Superstition & "Doom" Astrological Curses
        if (self::matches_superstition($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_SUPERSTITION,
                'reply'     => self::get_response(self::CAT_SUPERSTITION, $lang, $normalized)
            ];
        }

        // 6. Third-Party Privacy & Snooping
        if (self::matches_privacy($normalized)) {
            return [
                'triggered' => true,
                'category'  => self::CAT_PRIVACY,
                'reply'     => self::get_response(self::CAT_PRIVACY, $lang, $normalized)
            ];
        }

        return ['triggered' => false, 'category' => null, 'reply' => ''];
    }

    /**
     * 0. Match Identity / "Are you AI / Are you human / Who are you" queries
     */
    private static function matches_identity($t) {
        if (preg_match('/\b(are you|r u|are u)\s+(an?\s+)?(ai|a\.i\.|bot|robot|artificial intelligence|machine|computer|program|software|real|human|real person|real human|alive)\b/i', $t) ||
            preg_match('/\b(who are you|who r u|what are you|tell me about yourself|are you (real|an?) astrologer)\b/i', $t) ||
            preg_match('/(क्या\s*(आप|तुम)\s*(एक\s*)?(ai|एआई|रोबोट|इंसान|असली|मानव|जीवित)|आप\s*कौन\s*हैं|तुम\s*कौन\s*हो|kya\s*(aap|tum)\s*(ek\s*)?(ai|robot|insaan|real|asli)|aap\s*kaun\s*ho|tum\s*kaun\s*ho)/iu', $t)) {
            return true;
        }
        return false;
    }

    /**
     * 1. Match Medical, Health, & Pregnancy Predictions
     */
    private static function matches_medical($t) {
        // Death / Lifespan / Terminal illness
        if (preg_match('/\b(when will (my )?(i|he|she|father|mother|brother|sister|husband|wife|parent|someone) (die|pass away)|how long will (my )?(i|he|she|father|mother|husband|wife) live|lifespan|death prediction|predict (death|mortality|lifespan)|end is near|stage 4 cancer|recover from (cancer|tumor)|die from (cancer|illness|disease))\b/i', $t) ||
            preg_match('/\b(die|death|pass away|lifespan)\b.*?\b(when|how long|cancer|illness|age)\b/i', $t) ||
            preg_match('/(कब मरूंगी|कब मरूंगा|कब मरेंगे|मौत कब होगी|मृत्यु कब होगी|कितनी उम्र है|आयु कितनी है|उम्र कितनी है|kab marunga|kab marenge|maut kab hogi|mrityu kab hogi|kitni umar hai|aayu kitni hai)/iu', $t)) {
            return true;
        }

        // Gender of unborn child (Strictly Prohibited by Law)
        if (preg_match('/\b(boy or (a )?girl|baby boy or (a )?(baby )?girl|gender of (the |my |a )?(baby|child|unborn|fetus)|conceive a (boy|girl)|predict (the )?gender|gender prediction)\b/i', $t) ||
            preg_match('/\b(boy|son)\b.*?\bor\b.*?\b(girl|daughter)\b/i', $t) ||
            preg_match('/(बेटा होगा या बेटी|लड़का होगा या लड़की|पुत्र होगा या पुत्री|गर्भ में लड़का|गर्भ में लड़की|ladka hoga ya ladki|beta hoga ya beti|putra hoga ya putri|beta ya beti|ladka ya ladki)/iu', $t)) {
            return true;
        }

        // Conception / Fertility timelines
        if (preg_match('/\b(when will i conceive|fertility outcome|cure my infertility|ivf success)\b/i', $t) ||
            preg_match('/(गर्भधारण कब होगा|संतान सुख कब मिलेगा|ivf सफल होगा)/iu', $t)) {
            return true;
        }

        // Surgery & Medical Procedures
        if (preg_match('/\b(will (my |the )?surgery be successful|surgery safe|knee replacement go well|operation successful|bypass surgery)\b/i', $t) ||
            preg_match('/(सर्जरी सफल होगी|ऑपरेशन सफल होगा|surgery safal hogi|operation safe hai)/iu', $t)) {
            return true;
        }

        // Medications & Stopping prescribed drugs
        if (preg_match('/\b(stop (taking )?(my )?(medication|medicine|pills|treatment)|alter medication)\b/i', $t) ||
            preg_match('/(दवा बंद कर|दवाई छोड़|medicine band kar|dawa chhod)/iu', $t)) {
            return true;
        }

        // Disease diagnosis & Cures
        if (preg_match('/\b(cure (my )?(cancer|autoimmune|disease|diabetes)|diagnose (my )?(illness|disease)|genetic disease|outgrow (their )?asthma|heal (my )?allergy|cure (my )?paralysis|disability in old age)\b/i', $t) ||
            preg_match('/(कैंसर ठीक होगा|बीमारी ठीक होगी|एलर्जी ठीक होगी|लकवा ठीक होगा)/iu', $t)) {
            return true;
        }

        // Clinical Mental Health & Suicide / Self-Harm
        if (preg_match('/\b(depression caused by (weak |planet|saturn|rahu)|diagnose my depression|bipolar|schizophrenia|mental disorder caused by)\b/i', $t) ||
            preg_match('/(डिप्रेशन किस ग्रह से|मानसिक रोग किस ग्रह)/iu', $t)) {
            return true;
        }

        // Physical trauma / accidents
        if (preg_match('/\b(sudden accident happening|will i face an accident|accident this month)\b/i', $t) ||
            preg_match('/(दुर्घटना योग|एक्सीडेंट होगा|durghatna yog|accident yog)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * 2. Match Financial, Wealth, & Trading Speculation
     */
    private static function matches_financial($t) {
        // Lottery & Gambling
        if (preg_match('/\b(lottery number|win (the )?lottery|jackpot numbers|win tonight\'s draw|online gambling|recover money lost in gambling|satta|matka|casino)\b/i', $t) ||
            preg_match('/(लॉटरी नंबर|लॉटरी लगेगी|जुआ में जीता|सट्टा मटका|lottery number|satta matka|lottery lagne ka yog)/iu', $t)) {
            return true;
        }

        // Specific Stock Picks, Market Trends & Crypto Tips
        if (preg_match('/\b(stock(s)?|share(s)?|share market|stock market|sensex|nifty|market)\b.*\b(go up|go down|rise|fall|crash|boom|bullish|bearish|tomorrow|today|next week|price|trend)\b/i', $t) ||
            preg_match('/\b(will (the )?(stock(s)?|market|share market|sensex|nifty) (go up|go down|rise|fall|crash|boom))\b/i', $t) ||
            preg_match('/\b(day trading|intraday|option trading|f&o|futures and options|crypto|bitcoin|btc|eth)\b/i', $t) ||
            preg_match('/\b((invest in|buy|put all savings into) (bitcoin|crypto|btc|eth)|which specific stock to buy|which stock will boom|stock tips|intraday trading make me rich|mutual fund 30% return|buy this specific (property|land) for quick profit)\b/i', $t) ||
            preg_match('/(स्टॉक|शेयर बाजार|शेयर मार्केट|निफ्टी|सेंसेक्स|कल शेयर|शेयर बढ़ेगा|शेयर गिरेगा|मार्केट क्रैश|share market|share badhega|share girega|market upar|nifty|sensex)/iu', $t)) {
            return true;
        }

        // Bankruptcy & Insolvency Forecasts
        if (preg_match('/\b(will my business go bankrupt|declare bankruptcy|insolvent|clear all my debts next month)\b/i', $t) ||
            preg_match('/(बिजनेस दिवालिया|कंपनी डूब जाएगी|business bankrupt|diwaliya nikal jayega)/iu', $t)) {
            return true;
        }

        // Hidden treasure / Lost physical money
        if (preg_match('/\b(hidden treasure|find lost money|gupat dhan)\b/i', $t) ||
            preg_match('/(गुप्त धन|गड़ा हुआ धन|gupt dhan)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * 3. Match Legal, Litigation, & Criminal Matters
     */
    private static function matches_legal($t) {
        // Court Verdicts & Judge Decisions
        if (preg_match('/\b(will i win (my |the )?court case|judge grant (me )?bail|court dispute settlement|will i go to jail|court verdict|verdict on friday|sent to prison)\b/i', $t) ||
            preg_match('/(कोर्ट केस जीतूंगा|कोर्ट केस जीतेंगे|जेल जाऊंगा|जेल होगी|जमानत मिलेगी|बेल मिलेगी|court case jeetenge|jail hogi|bail milegi)/iu', $t)) {
            return true;
        }

        // Police & Criminal Charges
        if (preg_match('/\b(will police arrest me|police complaint against me|prove my innocence in (false )?criminal case|acquitted of cybercrime|arrest warrant)\b/i', $t) ||
            preg_match('/(पुलिस गिरफ्तार करेगी|पुलिस पकड़ लेगी|झूठे केस में फंसा|police pakad legi|police arrest karegi)/iu', $t)) {
            return true;
        }

        // Criminal Accusations / Fraud / Theft
        if (preg_match('/\b(did (my )?partner commit fraud|who stole (my |the )?(car|money|items)|signature forged|defamation lawsuit win)\b/i', $t) ||
            preg_match('/(किसने चोरी की|चोर कौन है|पार्टनर ने फ्रॉड किया|kisne chori ki|chor kon hai)/iu', $t)) {
            return true;
        }

        // Child Custody in Court
        if (preg_match('/\b(get custody of my children|child custody battle|court award custody)\b/i', $t) ||
            preg_match('/(बच्चों की कस्टडी मिलेगी|कस्टडी केस)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * 4. Match Relationships, Infidelity, & Absolute Breakups
     */
    private static function matches_relationships($t) {
        // Infidelity & Cheating Verification
        if (preg_match('/\b(is (my )?(husband|wife|spouse|partner|boyfriend|girlfriend).*?(cheating|having an affair|unfaithful)|secret affair|cheat on me|is (he|she) cheating|infidelity|extramarital affair|extra-marital)\b/i', $t) ||
            preg_match('/(पति|पत्नी|पार्टनर|जीवनसाथी|बॉयफ्रेंड|गर्लफ्रेंड|पतिदेव|शौहर|बीवी).*?(अफेयर|affair|चक्कर|धोखा|cheating)/iu', $t) ||
            preg_match('/(अफेयर चल रहा|चक्कर चल रहा|धोखा दे रहा|धोखा दे रही|अवैध संबंध|najayaz sambandh|affair chal raha|dhoka de raha|dhoka de rahi)/iu', $t)) {
            return true;
        }

        // Secret Relationships / Breaking up marriages
        if (preg_match('/\b(secret lover leave (their )?spouse|hiding a secret past|prior marriage from me)\b/i', $t) ||
            preg_match('/(प्रेमी अपनी पत्नी को छोड़ेगा|गुप्त शादी छिपा रखी)/iu', $t)) {
            return true;
        }

        // Absolute Relationship Doom / In-law Conspiracies
        if (preg_match('/\b(is there zero hope left in marriage|in-laws plotting to ruin my marriage|arranged marriage turn out to be abusive)\b/i', $t) ||
            preg_match('/(ससुराल वाले बर्बाद करने की साजिश|शादी में कोई उम्मीद नहीं)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * 5. Match Fear, Superstition, & "Doom" Astrological Curses
     */
    private static function matches_superstition($t) {
        // Cursed / Doomed Life
        if (preg_match('/\b(life completely cursed|doomed to fail|destined to live in poverty|born on eclipse bad luck|will (my )?bad karma destroy|am i cursed)\b/i', $t) ||
            preg_match('/(जिंदगी बर्बाद हो गई|मेरी किस्मत में सिर्फ बर्बादी|जिंदगी में हमेशा गरीबी|life cursed hai|zindagi barbad ho gayi)/iu', $t)) {
            return true;
        }

        // Black Magic & Evil Curses & Tantra
        if (preg_match('/\b(black magic|evil eye|voodoo|hex|curse|occult spell|someone casting|jadu tona|kala jadu|tantra mantra|totka|vashikaran)\b/i', $t) ||
            preg_match('/(काला जादू|जादू टोना|तंत्र मंत्र|टोटका|वशीकरण|बुरी नजर|मूट मारना|अघोरी क्रिया|kala jadu|jadu tona|tantra mantra|buri nazar|vashikaran)/iu', $t)) {
            return true;
        }

        // Fatalistic Dosha Terror (Manglik = Spouse will die, Kaal Sarp = Ruined)
        if (preg_match('/\b(manglik dosha (mean |cause )?spouse will (definitely )?die|kaal sarp (dosha )?(make me|cause) doom|rahu mahadasha pure evil)\b/i', $t) ||
            preg_match('/(मांगलिक दोष से जीवनसाथी मर जाएगा|कालसर्प से जीवन नष्ट|manglik dosh se pati mar jayega)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * 6. Match Third-Party Privacy & Control
     */
    private static function matches_privacy($t) {
        // Snooping on Crush / Ex / Boss / Competitor / Third party
        if (preg_match('/\b(crush|boss|colleague|neighbor|neighbour|competitor|rival|teacher|politician|celebrity|ex)\b.*?\b(chart|kundli|horoscope|strategy|weakness|secret)\b/i', $t) ||
            preg_match('/\b(check|read|hack|steal|spy|scan)\b.*?\b(crush|boss|colleague|neighbor|neighbour|competitor|rival|celebrity|ex|stalker)\b/i', $t) ||
            (preg_match('/\b(without (them|him|her) knowing|without permission|without consent|secretly|bina bataye)\b/i', $t) && preg_match('/\b(chart|kundli|horoscope)\b/i', $t)) ||
            preg_match('/(बॉस|क्रश|कंपटीटर|पड़ोसी|प्रतिद्वंद्वी|ex|boss|crush|competitor).*?(कुंडली|kundli|chart|कमजोरी|कमजोरियां|strategy|secret|रणनीति|बिना बताए|bina bataye)/iu', $t)) {
            return true;
        }

        return false;
    }

    /**
     * Get Canonically Approved Guardrail Response
     * Matches the user's selected language (en, hi, hinglish)
     */
    public static function get_response($category, $lang = 'en', $normalized_query = '') {
        $credit_note = self::get_credit_preservation_note($lang);

        switch ($category) {
            case self::CAT_IDENTITY:
                if ($lang === 'hi') {
                    return "मैं नियति हूँ, MeraRashifal.com पर आपकी ज्योतिष सहायक (Astrology Assistant), आपकी सभी ज़रूरतों और कुंडली से जुड़े प्रश्नों के समाधान हेतु यहाँ उपस्थित हूँ। आप निःसंकोच अपने करियर, विवाह, आर्थिक स्थिति या जीवन से जुड़ा कोई भी प्रश्न पूछ सकते हैं!" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Main Niyati hoon, MeraRashifal.com par aapki Astrology Assistant, aapki sabhi needs aur kundli se jude sawaalon ke sahi margdarshan ke liye yahan present hoon. Aap bina kisi jhijhak ke apne career, marriage, finance ya life se juda koi bhi sawaal pooch sakte hain!" . $credit_note;
                } else {
                    return "I am Niyati, your personal Astrology Assistant here at MeraRashifal.com, present to answer all your needs and guide you through your birth chart. Feel free to ask about your career, marriage, finances, or any life questions!" . $credit_note;
                }

            case self::CAT_MEDICAL:
                if ($lang === 'hi') {
                    // Check if it's baby gender
                    if (strpos($normalized_query, 'gender') !== false || strpos($normalized_query, 'लड़का') !== false || strpos($normalized_query, 'बेटा') !== false) {
                        return "नियति गर्भस्थ शिशु के लिंग का पूर्वानुमान नहीं करती है। हर संतान ईश्वर का अमूल्य उपहार है। गर्भावस्था और स्वास्थ्य से जुड़े सभी परीक्षणों हेतु कृपया अपने स्त्री रोग विशेषज्ञ (Gynecologist) से परामर्श लें।" . $credit_note;
                    }
                    return "स्वास्थ्य, गंभीर बीमारियों, सर्जरी या जीवन-मृत्यु के संबंध में नियति चिकित्सीय परामर्श नहीं दे सकती। शारीरिक व मानसिक स्वास्थ्य के लिए कृपया पूर्णतः अपने विशेषज्ञ चिकित्सकों (Doctors) एवं मेडिकल टीम पर भरोसा रखें।" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    if (strpos($normalized_query, 'gender') !== false || strpos($normalized_query, 'ladka') !== false || strpos($normalized_query, 'beta') !== false) {
                        return "Niyati unborn baby ke gender ka prediction nahi karti hai. Har child ek anmol gift hai. Pregnancy aur health related guidance ke liye please apne gynecologist ya doctor se consult karein." . $credit_note;
                    }
                    return "Niyati medical diagnosis, surgery outcomes ya life-and-death predictions provide nahi karti. Kisi bhi physical ya mental health condition ke liye please apne licensed doctor ya medical specialist par bharosa karein." . $credit_note;
                } else {
                    if (strpos($normalized_query, 'gender') !== false || strpos($normalized_query, 'boy or girl') !== false) {
                        return "Niyati strictly does not predict the gender of an unborn child. Every child is a precious blessing. For all pre-natal tracking and maternal health guidance, please consult your qualified obstetrician or healthcare provider." . $credit_note;
                    }
                    return "Niyati cannot offer medical diagnoses, surgical forecasts, or life-and-death predictions. For all physical and psychological health concerns, please rely entirely on your qualified doctors and medical team." . $credit_note;
                }

            case self::CAT_FINANCIAL:
                if ($lang === 'hi') {
                    return "ज्योतिष जीवन में संयम और विस्तार के दौर को इंगित कर सकता है, परंतु नियति किसी विशिष्ट शेयर, क्रिप्टोकरेंसी, लॉटरी नंबर या सट्टेबाजी पर सलाह नहीं देती। किसी भी वित्तीय निर्णय से पूर्व प्रमाणित वित्तीय सलाहकार (SEBI Certified Financial Advisor) से परामर्श अवश्य लें।" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Astrology financial cycles aur timing highlight kar sakti hai, lekin Niyati specific stock tips, crypto recommendations, lottery numbers ya intraday trading advice provide nahi karti. Monetary investments ke liye certified financial planner se consult karein." . $credit_note;
                } else {
                    return "While astrology highlights general cycles of caution or expansion, Niyati cannot provide stock tips, crypto recommendations, lottery numbers, or day-trading advice. Please consult a certified financial advisor before making monetary investments." . $credit_note;
                }

            case self::CAT_LEGAL:
                if ($lang === 'hi') {
                    return "ग्रहों की दशाएं जीवन में तनाव या राहत के कालखंड दर्शा सकती हैं, परंतु अदालती मामलों, जमानत, जेल या मुकदमों के निर्णय केवल न्यायिक प्रक्रिया व साक्ष्यों पर निर्भर करते हैं। कृपया अपने विधिक सलाहकार (Advocate) पर भरोसा रखें।" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Planetary transits personal challenges ya relief ke phases show kar sakte hain, lekin court cases, bail aur legal verdicts purely judicial evidence aur law par depend karte hain. Apne case ke liye please apne legal counsel par rely karein." . $credit_note;
                } else {
                    return "While planetary transits can indicate periods of stress or resolution, Niyati cannot predict court rulings, bail decisions, or criminal verdicts. Legal proceedings depend strictly on evidence and law—please rely on your defense counsel." . $credit_note;
                }

            case self::CAT_RELATIONSHIPS:
                if ($lang === 'hi') {
                    return "जन्म पत्रिका रिश्तों में संवाद और सामंजस्य के दौर को दर्शा सकती है, परंतु नियति किसी व्यक्ति के व्यक्तिगत आचरण या बेवफाई (Cheating) की पुष्टि नहीं करती। आपसी विश्वास और स्पष्टता केवल खुले संवाद व पारिवारिक परामर्श (Counseling) से संभव है।" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Kundli emotional dynamics aur communication gaps ko highlight kar sakti hai, lekin Niyati personal infidelity ya cheating confirm nahi karti. Trust aur clarity ke liye partner ke saath honest baat karein ya professional relationship counseling lein." . $credit_note;
                } else {
                    return "Birth charts can reflect emotional friction or communication cycles, but Niyati cannot verify personal actions like infidelity or dishonesty. Real clarity comes through honest, compassionate dialogue with your partner or professional relationship counseling." . $credit_note;
                }

            case self::CAT_SUPERSTITION:
                if ($lang === 'hi') {
                    return "वैदिक ज्योतिष में कालसर्प, मांगलिक या साढ़ेसाती जैसे योग कोई अभिशाप या 'बर्बादी' नहीं हैं—ये जीवन में धैर्य, अनुशासन और परिपक्वता विकसित करने के चरण हैं। भाग्य से अधिक आपका कर्म और सकारात्मक निर्णय प्रबल हैं। निर्भय रहें!" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Vedic astrology mein Sade Sati ya planetary doshas koi curse ya barbadi nahi hain—ye personal discipline, patience aur self-awareness develop karne ke transformative phases hain. Aapke positive karma aur choices hamesha sabse powerful hain." . $credit_note;
                } else {
                    return "In modern Vedic wisdom, combinations like Sade Sati, Manglik, or Kaal Sarp are not curses—they are transformative phases meant to build resilience, patience, and character. Your conscious choices, karma, and free will always shape your destiny!" . $credit_note;
                }

            case self::CAT_PRIVACY:
            default:
                if ($lang === 'hi') {
                    return "व्यक्तिगत गोपनीयता (Privacy) के सम्मान हेतु नियति का अध्ययन केवल आपके स्वयं के जीवन और मार्गदर्शन तक सीमित है। नियति किसी अन्य व्यक्ति (बॉस, परिचित या प्रतिस्पर्धी) की बिना अनुमति गुप्त जानकारी या कुंडली विश्लेषण नहीं करती।" . $credit_note;
                } elseif ($lang === 'hinglish') {
                    return "Personal privacy ka respect karte hue, Niyati sirf aapke personal journey aur self-reflection par focus karti hai. Third-party individuals (boss, competitors, ex-partners) ke private charts secretly analyze karna Niyati ki ethical policy ke against hai." . $credit_note;
                } else {
                    return "Out of deep respect for personal privacy and boundaries, Niyati focuses readings strictly on your own life journey and self-growth. Analyzing the private charts or secret intentions of third parties without consent goes against Niyati's ethical guidelines." . $credit_note;
                }
        }
    }

    /**
     * Credit preservation note appended to guardrail replies
     */
    public static function get_credit_preservation_note($lang = 'en') {
        if ($lang === 'hi') {
            return "\n\n*(सूचना: इस प्रश्न के लिए आपके चैट क्रेडिट में से कोई कटौती नहीं की गई है। आप करियर, विवाह, अथवा व्यक्तिगत विकास से जुड़े प्रश्न पूछ सकते हैं।)*";
        } elseif ($lang === 'hinglish') {
            return "\n\n*(Note: Is sawaal ke liye aapka chat credit deduct nahi kiya gaya hai. Aap career, marriage ya personal guidance se jude sawaal pooch sakte hain.)*";
        }
        return "\n\n*(Note: Your chat credit has not been deducted for this query. You are welcome to ask about your career, marriage, or personal growth.)*";
    }

    /**
     * Check if an LLM-generated reply is an ethical guardrail decline
     */
    public static function is_guardrail_response($text) {
        if (empty($text)) return false;
        
        $markers = [
            'cannot offer medical prognoses',
            'cannot predict specific pregnancy',
            'does not predict the gender',
            'cannot diagnose medical conditions',
            'cannot give stock tips',
            'cannot predict daily stock',
            'cannot predict stock market',
            'cannot predict short-term market',
            'cannot predict stock',
            'cannot predict share price',
            'cannot forecast short-term market',
            'short-term market trading involves',
            'cannot predict random games of chance',
            'cannot predict specific business failures',
            'cannot determine legal verdicts',
            'cannot investigate illegal acts',
            'cannot confirm personal actions like infidelity',
            'does not look at or predict lifespans',
            'out of respect for personal privacy',
            'against niyati\'s ethical guidelines',
            'niyati strictly does not look at',
            'niyati cannot offer medical',
            'niyati cannot predict',
            'your personal astrology assistant',
            'your astrology assistant',
            'आपकी ज्योतिष सहायक',
            'aapki astrology assistant'
        ];

        $lower = mb_strtolower($text, 'UTF-8');
        foreach ($markers as $marker) {
            if (mb_strpos($lower, $marker) !== false) {
                return true;
            }
        }
        return false;
    }
}
