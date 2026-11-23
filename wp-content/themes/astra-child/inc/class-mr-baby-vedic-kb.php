<?php
/**
 * MeraRashifal Baby Name Engine: High-Density Vedic Knowledge Base & Deterministic Content Generator
 * Class: MR_Baby_Vedic_KB
 *
 * Provides comprehensive, authentic, deterministic Vedic astrology and numerology
 * narrative generators for all 27 Nakshatras, 12 Rashis, and 9 Numerological Life Path Numbers.
 * Eliminates thin-content penalties across all 94,400+ baby names with zero external API calls.
 *
 * @package MeraRashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Baby_Vedic_KB
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 27 Nakshatras Complete Encyclopedic Knowledge Base
     */
    private $nakshatra_kb = [
        'Ashwini' => [
            'hi' => 'अश्विनी',
            'lord' => 'Ketu (केतु)',
            'deity' => 'Ashwini Kumaras (दिव्य चिकित्सक अश्विनी कुमार)',
            'symbol' => "Horse's Head (अश्व का मुख - ऊर्जा और गतिशीलता)",
            'traits_en' => 'Energetic, swift, pioneering, and blessed with natural healing abilities.',
            'traits_hi' => 'ऊर्जावान, तीव्र बुद्धि, साहसी और दूसरों की सहायता करने वाले स्वभाव के धनी।',
            'personality_en' => 'Children born under Ashwini Nakshatra possess tremendous vitality, radiant enthusiasm, and an eagerness to explore new horizons. Governed by the divine physician twin deities, the Ashwini Kumaras, they exhibit quick reflexes, sharp wit, and a deeply benevolent nature. They make friends effortlessly and are admired for their honesty and courage.',
            'personality_hi' => 'अश्विनी नक्षत्र में जन्मे बच्चे अद्भुत स्फूर्ति, उत्साह और नई चीजों को जानने की तीव्र उत्सुकता रखते हैं। दिव्य चिकित्सक अश्विनी कुमारों के प्रभाव से इनके स्वभाव में दूसरों के प्रति गहरी करुणा और सहायता की भावना होती है। इनकी निर्णय क्षमता तेज होती है और ये अपने निष्कपट व्यवहार से सभी के प्रिय बन जाते हैं।',
            'career_en' => 'With the swift and pioneering nature of Ketu, they thrive in medicine, healthcare, scientific research, aviation, engineering, athletics, and dynamic entrepreneurship.',
            'career_hi' => 'केतु और अश्विनी के शुभ प्रभाव से ये चिकित्सा, स्वास्थ्य सेवा, वैज्ञानिक अनुसंधान, विमानन, खेलकूद, तकनीकी सेवाओं और साहसिक व्यापार में असाधारण सफलता प्राप्त करते हैं।'
        ],
        'Bharani' => [
            'hi' => 'भरणी',
            'lord' => 'Venus (शुक्र)',
            'deity' => 'Lord Yama (धर्मराज यम - सत्य और कर्तव्य के प्रतीक)',
            'symbol' => 'Yoni / Vessel (सृजन और पोषण का प्रतीक)',
            'traits_en' => 'Determined, creative, loyal, and driven by a strong sense of moral truth.',
            'traits_hi' => 'दृढ़निश्चयी, रचनात्मक, कर्तव्यनिष्ठ और नैतिक मूल्यों पर टिके रहने वाले।',
            'personality_en' => 'Bharani Nakshatra bestows an innate sense of responsibility, immense willpower, and an artistic flair. Under the divine lordship of Venus and the moral stewardship of Yama, children of this star are fiercely honest, protective of loved ones, and capable of enduring hardships with quiet grace.',
            'personality_hi' => 'भरणी नक्षत्र के बच्चे जन्मजात कर्तव्यनिष्ठ, अत्यधिक दृढ़निश्चयी और रचनात्मक स्वभाव के होते हैं। शुक्र की कलात्मक दृष्टि और धर्मराज यम के प्रभाव से इनमें सच का साथ देने का अटूट साहस होता है। ये अपने परिजनों और मित्रों के प्रति अत्यंत समर्पित रहते हैं।',
            'career_en' => 'They excel in legal professions, judiciary, creative arts, filmmaking, public administration, architecture, and philanthropic organizations.',
            'career_hi' => 'इनके लिए कानून, न्यायपालिका, कला, सिनेमा, वास्तुकला, प्रशासनिक सेवाओं और सामाजिक कल्याण के क्षेत्रों में उन्नति के विशेष मार्ग खुलते हैं।'
        ],
        'Krittika' => [
            'hi' => 'कृत्तिका',
            'lord' => 'Sun (सूर्य)',
            'deity' => 'Lord Agni (अग्नि देव - शुद्धि और तेजस्विता)',
            'symbol' => 'Flame / Razor (अग्नि शिखा - तीक्ष्ण बुद्धि और तेज)',
            'traits_en' => 'Sharp-minded, dignified, courageous, and committed to truth and perfection.',
            'traits_hi' => 'तेजस्वी, स्वाभिमानी, साहसी और सत्य एवं पूर्णता के प्रति निष्ठावान।',
            'personality_en' => 'Ruled by the Sun and the Fire God Agni, Krittika children possess a brilliant intellect and natural magnetic dignity. They radiate warmth and leadership, cutting through illusions with razor-sharp clarity. Though they have high standards, their heart is generous and fiercely protective.',
            'personality_hi' => 'भगवान सूर्य और अग्नि देव की कृपा से कृत्तिका नक्षत्र के बच्चे तेजस्वी बुद्धि और प्राकृतिक नेतृत्व क्षमता से परिपूर्ण होते हैं। इनका स्वाभिमान और ईमानदारी अत्यंत प्रबल होती है। ये किसी भी कार्य को पूरी निष्ठा और बारीकी से पूरा करना पसंद करते हैं।',
            'career_en' => 'Their natural brilliance and fiery focus suit defense, administrative civil services, surgery, culinary arts, corporate governance, and political leadership.',
            'career_hi' => 'रक्षा सेवाओं, सिविल सेवा, शल्य चिकित्सा (सर्जरी), प्रशासन, कला और कॉर्पोरेट नेतृत्व में ये अपने पराक्रम और बुद्धिमत्ता का लोहा मनवाते हैं।'
        ],
        'Rohini' => [
            'hi' => 'रोहिणी',
            'lord' => 'Moon (चंद्रमा)',
            'deity' => 'Lord Brahma / Prajapati (सृष्टिकर्ता ब्रह्मा)',
            'symbol' => 'Chariot / Cart (सफलता और संपन्नता की यात्रा)',
            'traits_en' => 'Charming, graceful, artistic, and blessed with emotional depth and fertility.',
            'traits_hi' => 'आकर्षक, सौम्य, कलाप्रिय, भावनात्मक रूप से समृद्ध और मिलनसार।',
            'personality_en' => 'Rohini is the most cherished star of the Moon, imbuing children with exquisite charm, gentle speech, and a deep appreciation for beauty, nature, and family. They possess a soothing presence that brings comfort to everyone around them and a fertile imagination that excels in creative expression.',
            'personality_hi' => 'चंद्रमा के अत्यंत प्रिय नक्षत्र रोहिणी में जन्मे बच्चे अत्यंत रूपवान, मधुरभाषी और संवेदनशील होते हैं। इनके व्यक्तित्व में एक विशेष आकर्षण और सुकून होता है जो परिवार और मित्रों को स्वतः आकर्षित करता है। कला, साहित्य और प्रकृति के प्रति इनका गहरा लगाव होता है।',
            'career_en' => 'Their natural aesthetics and popularity make them successful in fine arts, music, design, agriculture, hospitality, luxury retail, and psychological counseling.',
            'career_hi' => 'संगीत, ललित कला, फैशन व इंटीरियर डिजाइन, कृषि, आतिथ्य सत्कार (हॉस्पिटैलिटी) और परामर्श सेवाओं में ये असाधारण प्रतिष्ठा प्राप्त करते हैं।'
        ],
        'Mrigashira' => [
            'hi' => 'मृगशिरा',
            'lord' => 'Mars (मंगल)',
            'deity' => 'Lord Soma / Chandra (अमृत और शीतलता के देव)',
            'symbol' => "Deer's Head (मृग - जिज्ञासा और निरंतर खोज)",
            'traits_en' => 'Inquisitive, gentle, intelligent, and perpetually seeking knowledge and truth.',
            'traits_hi' => 'जिज्ञासु, सौम्य, खोजी प्रवृत्ति, बुद्धिमान और अध्ययनशील।',
            'personality_en' => 'Represented by the gentle deer, children of Mrigashira are born seekers with an insatiable thirst for knowledge. Governed by Mars but cooled by Soma, they combine intellectual agility with a gentle, noble disposition. They are delightful conversationalists who love traveling and learning.',
            'personality_hi' => 'मृग के प्रतीक वाला यह नक्षत्र बच्चों को असीम जिज्ञासा और त्वरित सीखने की क्षमता प्रदान करता है। मंगल के पराक्रम और सोम देव की शीतलता से ये ज्ञानवान और सौम्य दोनों होते हैं। नई जगहों की यात्रा करना और नई विधाएं सीखना इनका स्वाभाविक शौक होता है।',
            'career_en' => 'They thrive in journalism, scientific research, travel, telecommunications, software engineering, writing, and environmental sciences.',
            'career_hi' => 'पत्रकारिता, अनुसंधान, संचार माध्यमों, सॉफ्टवेयर विकास, यात्रा व पर्यटन, तथा साहित्य के क्षेत्र में ये अपनी गहरी छाप छोड़ते हैं।'
        ],
        'Ardra' => [
            'hi' => 'आर्द्रा',
            'lord' => 'Rahu (राहु)',
            'deity' => 'Lord Rudra (भगवान शिव का संहारक व शोधक रूप)',
            'symbol' => 'Teardrop / Diamond (संघर्ष से निकलने वाली चमक)',
            'traits_en' => 'Transformative, deeply intuitive, intellectually sharp, and emotionally resilient.',
            'traits_hi' => 'परिवर्तनशील, अंतर्ज्ञानी, कुशाग्र बुद्धि और प्रतिकूलताओं से सीखने वाले।',
            'personality_en' => 'Ardra represents the purifying storm that clears the air for fresh creation. Governed by Lord Rudra, children of this star possess profound depth, emotional resilience, and an ability to master complex concepts that puzzle others. They emerge stronger and wiser from every life experience.',
            'personality_hi' => 'भगवान रुद्र के प्रभाव से आर्द्रा नक्षत्र के बच्चे असाधारण बौद्धिक गहराई और समस्याओं को जड़ से समझने की क्षमता रखते हैं। ये जीवन की चुनौतियों से कभी नहीं घबराते, बल्कि हर अनुभव से परिपक्व होकर एक चमकदार हीरे की तरह निखरते हैं।',
            'career_en' => 'Their analytical depth suits artificial intelligence, computer programming, electrical engineering, psychology, medical research, and investigative fields.',
            'career_hi' => 'आर्टिफिशियल इंटेलिजेंस (AI), डेटा साइंस, इलेक्ट्रॉनिक इंजीनियरिंग, मनोविज्ञान, गहन चिकित्सा अनुसंधान और अन्वेषण में ये अग्रणी रहते हैं।'
        ],
        'Punarvasu' => [
            'hi' => 'पुनर्वसु',
            'lord' => 'Jupiter (बृहस्पति / गुरु)',
            'deity' => 'Goddess Aditi (देवमाता अदिति - असीम उदारता और संरक्षण)',
            'symbol' => 'Bow and Quiver (लक्ष्य संधान और पुनरागमन)',
            'traits_en' => 'Virtuous, benevolent, optimistic, and blessed with the power of renewal.',
            'traits_hi' => 'सदाचारी, परोपकारी, आशावादी और निरंतर प्रगतिशील।',
            'personality_en' => 'Punarvasu literally translates to "return of the light." Blessed by Jupiter and Mother Aditi, children born here possess immense goodness, spiritual faith, and a joyful, forgiving heart. Even after setbacks, they bounce back with multiplied strength, spreading positivity everywhere.',
            'personality_hi' => 'पुनर्वसु का अर्थ है "पुनः प्रकाश का आगमन"। देवगुरु बृहस्पति और माता अदिति के आशीर्वाद से ये बच्चे अत्यंत सदाचारी, उदार और सकारात्मक सोच वाले होते हैं। ये जीवन में कठिन समय में भी कभी धैर्य नहीं खोते और परिवार को सदैव संबल प्रदान करते हैं।',
            'career_en' => 'They find high success in education, teaching, philosophy, banking, law, publishing, civil aviation, and international trade.',
            'career_hi' => 'शिक्षा, अध्यापन, उच्च बैंकिंग, कानून, प्रकाशन, विमानन और सामाजिक मार्गदर्शन के क्षेत्रों में ये उच्च प्रतिष्ठा और सम्मान पाते हैं।'
        ],
        'Pushya' => [
            'hi' => 'पुष्य',
            'lord' => 'Saturn (शनि)',
            'deity' => 'Lord Brihaspati (ज्ञान और धर्म के अधिष्ठाता)',
            'symbol' => 'Flower / Circle / Milk Pail (पोषण और समृद्धि)',
            'traits_en' => 'Most auspicious, nurturing, disciplined, spiritually profound, and wise.',
            'traits_hi' => 'परम शुभ, पोषक स्वभाव, अनुशासित, धार्मिक और गंभीर बुद्धि के धनी।',
            'personality_en' => 'Regarded as the most spiritually auspicious of all 27 stars, Pushya bestows wisdom, unwavering moral integrity, and a nurturing disposition. Under Saturn’s discipline and Brihaspati’s wisdom, these children are patient, deeply respectful of elders, and committed to doing good.',
            'personality_hi' => 'वैदिक ज्योतिष में पुष्य को समस्त नक्षत्रों का राजा और सर्वाधिक कल्याणकारी माना गया है। देवगुरु के ज्ञान और शनि के अनुशासन से ये बच्चे अत्यंत शांत, गंभीर और संस्कारी होते हैं। परिवार की परंपराओं का आदर करना और सभी की भलाई सोचना इनका मूल स्वभाव होता है।',
            'career_en' => 'They thrive in governance, religious leadership, educational institution administration, healthcare, real estate, and finance.',
            'career_hi' => 'प्रशासन, न्याय सेवा, शिक्षण संस्थानों के संचालन, चिकित्सा, वित्त प्रबंधन और लोक कल्याणकारी संगठनों में ये सर्वोच्च पदों को सुशोभित करते हैं।'
        ],
        'Ashlesha' => [
            'hi' => 'आश्लेषा',
            'lord' => 'Mercury (बुध)',
            'deity' => 'Nagas / Sarpas (दिव्य नाग देव - गूढ़ ज्ञान और सम्मोहन)',
            'symbol' => 'Coiled Serpent (गूढ़ प्रज्ञा और सूक्ष्म समझ)',
            'traits_en' => 'Intuitive, perceptive, strategically brilliant, and deeply protective.',
            'traits_hi' => 'तीव्र अंतर्दृष्टि, रणनीतिक बुद्धि, सतर्क और अत्यधिक प्रभावशाली।',
            'personality_en' => 'Symbolized by the coiled serpent and ruled by Mercury, Ashlesha children possess penetrating psychological insight and a razor-sharp mind. They can read between the lines instantly and are master strategists. Fiercely protective of their loved ones, they never forget a kindness.',
            'personality_hi' => 'नाग देवों के संरक्षण और बुध के प्रभाव से आश्लेषा नक्षत्र के बच्चे गहरी मनोवैज्ञानिक समझ और रणनीतिक बुद्धि रखते हैं। ये किसी भी व्यक्ति या परिस्थिति की तह तक तुरंत पहुंच जाते हैं। अपने परिजनों की सुरक्षा और मान-सम्मान के प्रति ये सदैव सतर्क रहते हैं।',
            'career_en' => 'They excel in diplomacy, politics, strategic consulting, astrology, pharmaceutical chemistry, intelligence analysis, and executive leadership.',
            'career_hi' => 'कूटनीति, विदेश सेवा, गुप्तचर विश्लेषण, कॉर्पोरेट रणनीति, रसायन शास्त्र और उच्च प्रबंधकीय पदों पर इनकी बुद्धिमत्ता विशेष रंग लाती है।'
        ],
        'Magha' => [
            'hi' => 'मघा',
            'lord' => 'Ketu (केतु)',
            'deity' => 'Pitrus (दिव्य पूर्वज - वंश परंपरा और पैतृक आशीर्वाद)',
            'symbol' => 'Royal Throne (राजसिंहासन - नेतृत्व और अधिकार)',
            'traits_en' => 'Regal, noble, traditional, commanding, and blessed with ancestral pride.',
            'traits_hi' => 'राजसी ठाठ, स्वाभिमानी, नेतृत्ववान, परंपरावादी और कर्तव्यनिष्ठ।',
            'personality_en' => 'Magha connects directly to royal lineage and ancestral blessings. Children born under this star possess natural authority, high self-esteem, and a regal bearing. They uphold moral duties with dignity, dislike pettiness, and naturally command respect from peers and elders alike.',
            'personality_hi' => 'मघा नक्षत्र का संबंध राजसत्ता और पितृ आशीर्वाद से है। इस नक्षत्र में जन्मे बच्चे जन्मजात नेतृत्व, राजसी गरिमा और स्वाभिमान से युक्त होते हैं। ये किसी के अधीन रहकर काम करने के बजाय अपने स्वतंत्र विचारों और उच्च आदर्शों से समाज को दिशा देते हैं।',
            'career_en' => 'Their natural command aligns with politics, administrative governance, high corporate leadership, heritage curation, and large-scale enterprise management.',
            'career_hi' => 'राजनीति, उच्च प्रशासनिक सेवा (IAS/IPS), बहुराष्ट्रीय कंपनियों के शीर्ष प्रबंधन, इतिहास व पुरातत्व तथा उद्योग जगत में ये अग्रणी भूमिका निभाते हैं।'
        ],
        'Purva Phalguni' => [
            'hi' => 'पूर्वाफाल्गुनी',
            'lord' => 'Venus (शुक्र)',
            'deity' => 'Bhaga (सौभाग्य, समृद्धि और आनंद के देवता)',
            'symbol' => 'Front legs of a bed (विश्राम, आनंद और उत्सव)',
            'traits_en' => 'Affectionate, joyful, charismatic, socially vibrant, and lovers of beauty.',
            'traits_hi' => 'आनंदप्रिय, स्नेही, आकर्षक, मिलनसार और कला एवं सौंदर्य के प्रेमी।',
            'personality_en' => 'Governed by Venus and Bhaga, the deity of fortune, Purva Phalguni children are naturally charming, warm-hearted, and joyous. They possess an artistic soul, love celebrations, and bring sunshine into the lives of others. They believe in living life with enthusiasm, generosity, and style.',
            'personality_hi' => 'शुक्र और भाग्य के देवता भग की अनुकंपा से पूर्वाफाल्गुनी नक्षत्र के बच्चे अत्यंत हंसमुख, मिलनसार और आकर्षक होते हैं। ये जहां भी जाते हैं, वहां प्रसन्नता और सकारात्मकता का संचार करते हैं। संगीत, अभिनय, कला और सुरुचिपूर्ण जीवनशैली इनकी पहचान होती है।',
            'career_en' => 'They excel in entertainment, performing arts, public relations, luxury branding, hospitality, event management, and diplomacy.',
            'career_hi' => 'सिनेमा, संगीत, जनसंपर्क (PR), फैशन डिजाइनिंग, होटल प्रबंधन, इवेंट मैनेजमेंट और अंतरराष्ट्रीय संबंधों में ये बहुत लोकप्रिय होते हैं।'
        ],
        'Uttara Phalguni' => [
            'hi' => 'उत्तराफाल्गुनी',
            'lord' => 'Sun (सूर्य)',
            'deity' => 'Aryaman (मैत्री, संरक्षा और निष्ठा के देवता)',
            'symbol' => 'Back legs of a bed (स्थिर सुख और दीर्घायु संबंध)',
            'traits_en' => 'Generous, steadfast, honorable, helpful, and gifted with enduring leadership.',
            'traits_hi' => 'परोपकारी, निष्ठावान, सम्मानित, मित्रवत्सल और स्थिर नेतृत्व के धनी।',
            'personality_en' => 'Under the royal solar energy of the Sun and Aryaman, deity of friendship and contracts, these children are deeply ethical, generous, and reliable. They are pillars of strength for family and friends, honoring every promise they make with unwavering integrity.',
            'personality_hi' => 'सूर्य देव और देव अर्यमा के आशीर्वाद से उत्तराफाल्गुनी के बच्चे अत्यंत विश्वसनीय, दयालु और दृढ संकल्पी होते हैं। ये अपने दिए हुए वचन को निभाने के लिए सदैव तत्पर रहते हैं। मित्रों की सहायता करना और समाज में न्याय की स्थापना करना इनका जीवनोद्देश्य होता है।',
            'career_en' => 'Their integrity shines in judiciary, civil governance, philanthropy, corporate administration, banking, and social reform.',
            'career_hi' => 'न्यायपालिका, उच्च प्रशासनिक पद, बैंकिंग, समाज सुधार, चैरिटेबल ट्रस्ट और बड़े कॉर्पोरेट घरानों के संचालन में ये विशेष ख्याति पाते हैं।'
        ],
        'Hasta' => [
            'hi' => 'हस्त',
            'lord' => 'Moon (चंद्रमा)',
            'deity' => 'Lord Savitur / Surya (सृजन और ज्ञान की किरणें देने वाले सूर्य)',
            'symbol' => 'Open Hand (कला, कौशल और आशीर्वाद की मुद्रा)',
            'traits_en' => 'Skilled, witty, dexterous, compassionate, and master of craft and detail.',
            'traits_hi' => 'हस्तकला में निपुण, हाजिरजवाब, कर्मठ, संवेदनशील और कुशल रणनीतिकार।',
            'personality_en' => 'Hasta is symbolized by the open hand, conferring exceptional dexterity, practical intelligence, and healing touch. Governed by the Moon and Savitur, these children are cheerful, quick-witted, and love crafting, drawing, or building things with their own hands.',
            'personality_hi' => 'हस्त नक्षत्र का प्रतीक खुली हथेली है, जो कला, शिल्प और परोपकार को दर्शाती है। चंद्रमा और सविता देव की कृपा से ये बच्चे अत्यंत बुद्धिमान, हाजिरजवाब और किसी भी व्यावहारिक कार्य को कुशलता से करने में माहिर होते हैं। इनकी कल्पनाशक्ति अत्यंत समृद्ध होती है।' ,
            'career_en' => 'They shine in surgery, architecture, software UI/UX, graphic design, writing, astrology, craftsmanship, and healing therapies.',
            'career_hi' => 'शल्य चिकित्सा (सर्जरी), वास्तुकला, ग्राफिक डिजाइन, सॉफ्टवेयर विकास, ज्योतिष, हस्तशिल्प और लेखन में इनकी कलात्मकता की प्रशंसा होती है।'
        ],
        'Chitra' => [
            'hi' => 'चित्रा',
            'lord' => 'Mars (मंगल)',
            'deity' => 'Tvashtar / Vishwakarma (देवताओं के दिव्य शिल्पकार)',
            'symbol' => 'Bright Jewel / Pearl (चमकता हुआ मोती - अनुपम सुंदरता)',
            'traits_en' => 'Creative genius, charismatic, perfectionist, dynamic, and visionaries.',
            'traits_hi' => 'सृजनशील, आकर्षक, पूर्णतावादी, तेजस्वी और सौंदर्य के पारखी।',
            'personality_en' => 'Chitra literally means "the brilliant masterpiece." Ruled by cosmic architect Vishwakarma and vibrant Mars, children born here possess artistic genius, an eye for exquisite elegance, and relentless dedication to perfection. They stand out in any gathering like a polished diamond.',
            'personality_hi' => 'चित्रा का शाब्दिक अर्थ है "अद्भुत चित्र या कृति"। देव शिल्पी विश्वकर्मा और पराक्रमी मंगल के प्रभाव से ये बच्चे अद्वितीय रचनात्मकता और सौंदर्य-बोध के धनी होते हैं। ये जो भी काम हाथ में लेते हैं, उसे मास्टरपीस बनाकर ही दम लेते हैं।',
            'career_en' => 'They thrive in engineering, architecture, industrial design, cinema, haute couture fashion, structural innovation, and media production.',
            'career_hi' => 'इंजीनियरिंग, आर्किटेक्चर, फैशन व ज्वेलरी डिजाइन, फिल्म निर्देशन, मीडिया प्रोडक्शन और नवीन आविष्कारों में ये सफलता के नए कीर्तिमान स्थापित करते हैं।'
        ],
        'Swati' => [
            'hi' => 'स्वाति',
            'lord' => 'Rahu (राहु)',
            'deity' => 'Lord Vayu (पवन देव - स्वतंत्रता, ज्ञान और जीवन शक्ति)',
            'symbol' => 'Shoot of Plant / Coral (लचीलापन, अनुकूलन और स्वतंत्रता)',
            'traits_en' => 'Independent, flexible, diplomatic, eloquent, and deeply intuitive.',
            'traits_hi' => 'स्वतंत्र विचारक, लचीला स्वभाव, कुशल वक्ता, कूटनीतिज्ञ और प्रगतिशील।',
            'personality_en' => 'Swati is the star of graceful freedom, moving with the agility of the wind. Blessed by Lord Vayu and Rahu, these children are independent thinkers who adapt smoothly to any culture or environment. They are polite, persuasive speakers who value fairness, trade, and intellectual liberty.',
            'personality_hi' => 'पवन देव की गति और स्वाधीनता से युक्त स्वाति नक्षत्र के बच्चे स्वतंत्र विचारों वाले और अत्यंत मिलनसार होते हैं। ये किसी भी नए माहौल में बहुत जल्दी ढल जाते हैं। इनकी वाकपटुता और तार्किक क्षमता इन्हें समाज और मित्रों में विशेष आदर दिलाती है।',
            'career_en' => 'They excel in global business, international trade, aviation, law, communications, diplomacy, and innovative startups.',
            'career_hi' => 'अंतरराष्ट्रीय व्यापार, वाणिज्य, विमानन, वकालत, जनसंचार, कूटनीति और तकनीकी स्टार्टअप्स में ये बहुत तेजी से उन्नति करते हैं।'
        ],
        'Vishakha' => [
            'hi' => 'विशाखा',
            'lord' => 'Jupiter (बृहस्पति / गुरु)',
            'deity' => 'Indra & Agni (इंद्र व अग्नि - शक्ति, संकल्प और विजय)',
            'symbol' => 'Triumphal Arch / Potter’s Wheel (लक्ष्य प्राप्ति और विजय)',
            'traits_en' => 'Ambitious, laser-focused, victorious, determined, and spiritually inclined.',
            'traits_hi' => 'अत्यंत महत्वाकांक्षी, लक्ष्य के प्रति समर्पित, साहसी और विजयी।',
            'personality_en' => 'Represented by a decorated triumphal archway, Vishakha bestows singular focus on big goals. Governed by Jupiter and the dual force of Indra and Agni, these children will work tirelessly until they achieve what they set out to conquer. Their determination is an inspiration to everyone.',
            'personality_hi' => 'इंद्र और अग्नि के संयुक्त तेज तथा देवगुरु बृहस्पति के आशीर्वाद से विशाखा नक्षत्र के बच्चे जिस लक्ष्य को ठान लेते हैं, उसे पाकर ही रहते हैं। इनमें एकाग्रता और लगन कूट-कूट कर भरी होती है। ये जीवन में कभी आधी-अधूरी सफलता से संतुष्ट नहीं होते।',
            'career_en' => 'Their driven spirit shines in competitive civil examinations, corporate law, politics, sports championships, high finance, and entrepreneurship.',
            'career_hi' => 'प्रतियोगी परीक्षाओं (UPSC), कॉर्पोरेट लॉ, राजनीति, खेलकूद, उच्च वित्त और व्यापारिक उपक्रमों में ये शीर्ष नेतृत्व हासिल करते हैं।'
        ],
        'Anuradha' => [
            'hi' => 'अनुराधा',
            'lord' => 'Saturn (शनि)',
            'deity' => 'Mitra (मित्र देव - सौहार्द, सहयोग और सच्ची मित्रता)',
            'symbol' => 'Lotus Flower (कमल - प्रतिकूलताओं में भी खिलने वाला)',
            'traits_en' => 'Devoted, friendly, resilient, spiritual, and bridge-builders across cultures.',
            'traits_hi' => 'सच्चे मित्र, स्नेही, संघर्षों से न घबराने वाले, आध्यात्मिक और निष्ठावान।',
            'personality_en' => 'Like a pure lotus blossoming amidst muddy waters, Anuradha bestows the ability to thrive anywhere with serenity. Ruled by Saturn and Mitra, deity of friendship, these children are loyal companions, respectful of diverse viewpoints, and possess profound spiritual wisdom.',
            'personality_hi' => 'कीचड़ में खिलने वाले पवित्र कमल की भांति अनुराधा नक्षत्र के बच्चे किसी भी विपरीत परिस्थिति में अपनी सौम्यता और पवित्रता बनाए रखते हैं। मित्र देव और शनि के प्रभाव से ये अत्यंत निष्ठावान मित्र साबित होते हैं और हर संबंध को पूरे दिल से निभाते हैं।',
            'career_en' => 'They thrive in international relations, counseling, travel operations, event curation, spiritual leadership, and organizational management.',
            'career_hi' => 'अंतरराष्ट्रीय संबंध, परामर्श, पर्यटन, सामाजिक संस्थाओं के संचालन, मानव संसाधन (HR) और शोध कार्यों में ये ख्याति प्राप्त करते हैं।'
        ],
        'Jyeshtha' => [
            'hi' => 'ज्येष्ठा',
            'lord' => 'Mercury (बुध)',
            'deity' => 'Lord Indra (देवराज इंद्र - शौर्य, रक्षा और नेतृत्व)',
            'symbol' => 'Amulet / Talisman (सुरक्षा और दिव्य सामर्थ्य)',
            'traits_en' => 'Protective, authoritative, brilliant, self-reliant, and fiercely responsible.',
            'traits_hi' => 'संरक्षक स्वभाव, प्रभावशाली, स्वावलंबी, बुद्धिमान और परिवार के मार्गदर्शक।',
            'personality_en' => 'Jyeshtha signifies seniority, supremacy, and elder guardianship. Ruled by Lord Indra and Mercury, these children have an innate sense of dignity and authority. They instinctively protect younger siblings, stand up against injustice, and lead with intellectual confidence.',
            'personality_hi' => 'ज्येष्ठा का अर्थ है "श्रेष्ठ और ज्येष्ठ"। देवराज इंद्र और बुध के तेज से युक्त ये बच्चे जन्मजात संरक्षक और नेतृत्वकर्ता होते हैं। ये परिवार में छोटों का मार्गदर्शन करते हैं और अन्याय के विरुद्ध सबसे पहले आवाज उठाते हैं। इनका आत्मविश्वास अद्वितीय होता है।',
            'career_en' => 'They excel in military command, police leadership, corporate directorships, investigative journalism, and executive governance.',
            'career_hi' => 'सैन्य नेतृत्व, पुलिस प्रशासन, खोजी पत्रकारिता, कॉर्पोरेट बोर्ड ऑफ डायरेक्टर्स और उच्च प्रशासनिक पदों में ये अपनी अमिट छाप छोड़ते हैं।'
        ],
        'Mula' => [
            'hi' => 'मूल',
            'lord' => 'Ketu (केतु)',
            'deity' => 'Nirriti (परिवर्तन और सत्य की देवी)',
            'symbol' => 'Tied Bunch of Roots (जड़ - मूल कारण तक पहुंचने की शक्ति)',
            'traits_en' => 'Investigative, truth-seeking, philosophical, transformative, and deeply grounded.',
            'traits_hi' => 'गहन अन्वेषक, दार्शनिक सोच, सच के खोजी और मूल सिद्धांतों पर चलने वाले।',
            'personality_en' => 'Mula represents the roots of existence. Governed by Ketu, children born here possess an uncanny ability to dissect problems to their absolute core. They reject superficialities, love uncovering hidden truths, and possess deep philosophical wisdom far beyond their physical years.',
            'personality_hi' => 'मूल का अर्थ है "जड़ या आधार"। केतु के प्रभाव से ये बच्चे किसी भी बात या समस्या की तह तक पहुंचे बिना चैन से नहीं बैठते। इनमें बनावटीपन बिल्कुल नहीं होता। ये अपनी उम्र से कहीं अधिक गंभीर, परिपक्व और दार्शनिक दृष्टिकोण रखते हैं।',
            'career_en' => 'They thrive in advanced scientific research, deep medicine, microbiology, astronomy, geology, philosophy, and high-stakes auditing.',
            'career_hi' => 'वैज्ञानिक अनुसंधान, भूविज्ञान, खगोल शास्त्र, गहन चिकित्सा शोध, दर्शनशास्त्र, पुरातत्व और ऑडिटिंग में ये नई खोजें करते हैं।'
        ],
        'Purva Ashadha' => [
            'hi' => 'पूर्वाषाढ़ा',
            'lord' => 'Venus (शुक्र)',
            'deity' => 'Apas (जल देव - जीवन, प्रवाह और अजेय शक्ति)',
            'symbol' => 'Winnowing Basket / Fan (सार को ग्रहण करने की शक्ति)',
            'traits_en' => 'Invincible, inspiring, optimistic, eloquent, and socially influential.',
            'traits_hi' => 'अजेय संकल्प, प्रेरणादायक, आशावादी, प्रभावशाली वक्ता और लोकप्रिय।',
            'personality_en' => 'Purva Ashadha translates to "the early invincible victory." Under Venus and Apas (Cosmic Water), these children have an indomitable spirit that never accepts defeat. They are natural orators, deeply compassionate, and carry an infectious optimism that lifts everyone around them.',
            'personality_hi' => 'पूर्वाषाढ़ा का अर्थ है "अपराजित विजय"। जल देव और शुक्र की कृपा से इन बच्चों का संकल्प अटूट होता है। ये कभी भी निराशा को अपने पास नहीं फटकने देते। इनकी वाणी में एक विशेष प्रभाव होता है जो श्रोताओं को सहज ही मंत्रमुग्ध कर देता है।',
            'career_en' => 'They shine in advocacy, law, motivational speaking, marketing communication, maritime industries, writing, and leadership coaching.',
            'career_hi' => 'वकालत, प्रेरक वक्ता (Motivational Speaker), जनसंचार, जहाजरानी, उच्च विपणन और नेतृत्व प्रशिक्षण में ये अत्यधिक सफल होते हैं।'
        ],
        'Uttara Ashadha' => [
            'hi' => 'उत्तराषाढ़ा',
            'lord' => 'Sun (सूर्य)',
            'deity' => 'Vishwadevas (दस सार्वभौमिक देवता - सत्य, धर्म और विजय)',
            'symbol' => "Elephant's Tusk (गजदंत - अडिग शक्ति और भव्यता)",
            'traits_en' => 'Steadfast, humble, universally respected, ethical, and victorious.',
            'traits_hi' => 'अडिग, विनम्र, धर्मपरायण, सर्वप्रिय और स्थायी सफलता पाने वाले।',
            'personality_en' => 'Signifying the final and permanent victory, Uttara Ashadha children embody quiet nobility, patience, and unwavering virtue. Ruled by the Sun and the ten universal Vishwadevas, they achieve great heights without losing their humility, winning the lasting respect of society.',
            'personality_hi' => 'उत्तराषाढ़ा स्थायी विजय और संपूर्ण धर्म का प्रतीक है। सूर्य और विश्वेदेवों के आशीर्वाद से ये बच्चे शांत, गंभीर और अत्यंत संस्कारी होते हैं। सफलता के सर्वोच्च शिखर पर पहुंचकर भी इनमें रत्ती भर अहंकार नहीं आता, जिससे समाज में इन्हें सर्वोच्च सम्मान मिलता है।',
            'career_en' => 'Their noble nature thrives in high judicial office, diplomacy, state governance, educational leadership, and foundational research.',
            'career_hi' => 'न्यायाधीश, राजनयिक, उच्च प्रशासनिक सेवा, विश्वविद्यालय संचालन और नीति निर्माण में इनका योगदान ऐतिहासिक होता है।'
        ],
        'Shravana' => [
            'hi' => 'श्रवण',
            'lord' => 'Moon (चंद्रमा)',
            'deity' => 'Lord Vishnu (सृष्टि के पालनकर्ता भगवान विष्णु)',
            'symbol' => 'Three Footprints / Ear (ज्ञान श्रवण और सर्वव्यापकता)',
            'traits_en' => 'Attentive listener, scholarly, wise, respectful of tradition, and pious.',
            'traits_hi' => 'उत्कृष्ट श्रोता, ज्ञानवान, संस्कारी, बड़ों का आदर करने वाले और धार्मिक।',
            'personality_en' => 'Shravana is the star of divine listening and wisdom transmission. Blessed by Lord Vishnu and the Moon, children born here possess exceptional retention, gentle manners, and profound respect for teachers and scriptures. They learn rapidly through listening and speak with quiet wisdom.',
            'personality_hi' => 'भगवान विष्णु के स्वामित्व वाले श्रवण नक्षत्र के बच्चे अद्भुत श्रवण शक्ति और ग्रहणशीलता के धनी होते हैं। ये सुनकर ही बड़ी से बड़ी विद्या को कंठस्थ कर लेते हैं। गुरुजनों और माता-पिता का सम्मान करना और धर्म के मार्ग पर चलना इनका स्वाभाविक गुण है।',
            'career_en' => 'They excel in university academia, linguistics, classical music, audio engineering, counseling, diplomacy, and publishing.',
            'career_hi' => 'उच्च शिक्षा, भाषा विज्ञान, संगीत, रेडियो व पॉडकास्टिंग, परामर्श, साहित्य प्रकाशन और जनसंपर्क में ये ख्याति पाते हैं।'
        ],
        'Dhanishta' => [
            'hi' => 'धनिष्ठा',
            'lord' => 'Mars (मंगल)',
            'deity' => 'Eight Vasus (अष्ट वसु - समृद्धि, ऊर्जा और प्रकृति के तत्व)',
            'symbol' => 'Flute / Drum (लय, संगीत और जीवन की गति)',
            'traits_en' => 'Charismatic, musical, wealthy, courageous, and natural leaders.',
            'traits_hi' => 'प्रतिभाशाली, संगीतप्रिय, समृद्ध, पराक्रमी और संगठनात्मक कौशल से युक्त।',
            'personality_en' => 'Dhanishta translates to "the most benevolent and prosperous." Symbolized by the cosmic flute and drum, children born here have a natural sense of rhythm, social charisma, and leadership. Under Mars and the Eight Vasus, they easily build wealth, goodwill, and community harmony.',
            'personality_hi' => 'धनिष्ठा का अर्थ है "परम समृद्ध और यशस्वी"। अष्ट वसुओं और मंगल के प्रभाव से इन बच्चों में अद्भुत लय, आकर्षण और सबको साथ लेकर चलने की कला होती है। ये अपने जीवन में धन, संपत्ति और मान-सम्मान सहज रूप से अर्जित करते हैं।',
            'career_en' => 'They excel in performing arts, music composition, real estate, engineering, corporate management, sports, and surgical medicine.',
            'career_hi' => 'संगीत, नृत्य, रियल एस्टेट, सिविल इंजीनियरिंग, कॉर्पोरेट प्रबंधन, खेलकूद और चिकित्सा के क्षेत्र में ये अग्रणी रहते हैं।'
        ],
        'Shatabhisha' => [
            'hi' => 'शतभिषा',
            'lord' => 'Rahu (राहु)',
            'deity' => 'Lord Varuna (वरुण देव - जल, अंतरिक्ष और ब्रह्मांडीय नियम)',
            'symbol' => 'Empty Circle / Hundred Physicians (दिव्य आरोग्य और अनंत क्षमता)',
            'traits_en' => 'Visionary, scientific, secretive, deeply independent, and master healers.',
            'traits_hi' => 'दूरदर्शी, वैज्ञानिक दृष्टिकोण, स्वतंत्र, उपचारक और गूढ़ विषयों के मर्मज्ञ।',
            'personality_en' => 'Shatabhisha means "the hundred healers." Ruled by cosmic water deity Varuna and Rahu, children born here have unconventional, brilliant minds. They see through illusions, value solitude, and possess natural aptitude for scientific mysteries, medicine, and futuristic technology.',
            'personality_hi' => 'शतभिषा का अर्थ है "सौ वैद्यों की शक्ति"। वरुण देव और राहु के प्रभाव से ये बच्चे असाधारण बुद्धि और वैज्ञानिक सोच के धनी होते हैं। इनका दृष्टिकोण हमेशा समय से आगे का होता है। चिकित्सा, अनुसंधान और जटिल तकनीकी पहेलियों को सुलझाना इनका शौक होता है।',
            'career_en' => 'They shine in medicine, pharmacology, astrophysics, advanced software architecture, cryptography, and oceanography.',
            'career_hi' => 'चिकित्सा विज्ञान, औषध निर्माण (फार्मेसी), अंतरिक्ष विज्ञान, साइबर सुरक्षा, क्रिप्टोग्राफी और समुद्र विज्ञान में ये विशेष नाम कमाते हैं।'
        ],
        'Purva Bhadrapada' => [
            'hi' => 'पूर्वभाद्रपद',
            'lord' => 'Jupiter (बृहस्पति / गुरु)',
            'deity' => 'Aja Ekapada (एकपाद रुद्र - तपोबल और आध्यात्मिक प्रकाश)',
            'symbol' => 'Sword / Front legs of funeral cot (वैराग्य, शक्ति और रूपांतरण)',
            'traits_en' => 'Intense, philosophical, sincere, highly principled, and transformative.',
            'traits_hi' => 'तपस्वी स्वभाव, गंभीर दार्शनिक, सिद्धांतवादी, स्पष्टवक्ता और निष्ठावान।',
            'personality_en' => 'Purva Bhadrapada bestows intense focus on truth, personal reform, and spiritual elevation. Under Jupiter and Aja Ekapada, these children are deeply sincere, detest pretension, and dedicate themselves passionately to ideals, standing firmly for what is morally right.',
            'personality_hi' => 'देवगुरु और एकपाद रुद्र के आशीर्वाद से पूर्वभाद्रपद के बच्चे गहरे सिद्धांतों और उच्च नैतिक मूल्यों पर जीते हैं। इनमें दिखावा बिल्कुल नहीं होता। ये अपने विचारों में स्पष्ट और दृढ़ होते हैं तथा समाज में बदलाव लाने के लिए सदैव प्रयासरत रहते हैं।',
            'career_en' => 'Their dedication suits judiciary, human rights advocacy, philosophical teaching, psychology, research, and high technology.',
            'career_hi' => 'न्यायपालिका, मानवाधिकार, दार्शनिक चिंतन, उच्च अनुसंधान, मनोविज्ञान और अध्यात्म के क्षेत्र में ये गुरुतुल्य भूमिका निभाते हैं।'
        ],
        'Uttara Bhadrapada' => [
            'hi' => 'उत्तरभाद्रपद',
            'lord' => 'Saturn (शनि)',
            'deity' => 'Ahirbudhnya (गहरे जल के दिव्य सर्प - शांति, स्थिरता और ज्ञान)',
            'symbol' => 'Back legs of funeral cot / Twins (स्थिर प्रज्ञा और आत्म-नियंत्रण)',
            'traits_en' => 'Serene, wise, compassionate, patient, and deeply grounded in spirituality.',
            'traits_hi' => 'शांत, गंभीर, परम दयालु, धैर्यवान और अगाध आत्म-नियंत्रण के स्वामी।',
            'personality_en' => 'Signifying deep wisdom and tranquil oceanic depths, children born under Uttara Bhadrapada possess exceptional emotional control, warm generosity, and spiritual peace. Governed by Saturn, they are patient, protective of the weak, and trusted by everyone.',
            'personality_hi' => 'अहिर्बुध्न्य और शनि देव की अनुकंपा से उत्तरभाद्रपद के बच्चे समुद्र की भांति गहरे, शांत और गंभीर होते हैं। इनके भीतर क्रोध या उतावलापन नहीं होता। ये दूसरों के दुखों को अपना समझकर दूर करने का प्रयास करते हैं और परिवार के सच्चे संबल बनते हैं।',
            'career_en' => 'They thrive in deep research, meditation/yoga leadership, healthcare direction, charitable foundation administration, and literature.',
            'career_hi' => 'गहन शोध, योग व अध्यात्म, स्वास्थ्य सेवाओं के प्रबंधन, साहित्य सृजन और धर्मार्थ ट्रस्टों के संचालन में ये आदरणीय स्थान पाते हैं।'
        ],
        'Revati' => [
            'hi' => 'रेवती',
            'lord' => 'Mercury (बुध)',
            'deity' => 'Lord Pushan (पोषण, सुरक्षा और सुरक्षित यात्रा के देवता)',
            'symbol' => 'Fish / Drum (पूर्णता, आनंद और अंतिम मोक्ष)',
            'traits_en' => 'Nurturing, universally loved, imaginative, gentle, and blessed with prosperity.',
            'traits_hi' => 'स्नेहशील, सर्वप्रिय, कल्पनाशील, सौम्य और संपन्नता के प्रतीक।',
            'personality_en' => 'Revati is the final, supreme nakshatra of the zodiac, representing cosmic fulfillment and divine love. Guided by Lord Pushan and Mercury, these children radiate sweetness, love for animals and nature, and an abundance of artistic charm. They bring peace, wealth, and joy wherever they step.',
            'personality_hi' => 'भचक्र का अंतिम और परम कल्याणकारी नक्षत्र रेवती है। पूषा देव और बुध के संरक्षण में जन्मे बच्चे अत्यंत कोमल हृदय, दयालु और प्रेम की प्रतिमूर्ति होते हैं। पशु-पक्षियों और प्रकृति से इनका गहरा नाता होता है। इनके जन्म से ही परिवार में सुख-समृद्धि की वृद्धि होती है।',
            'career_en' => 'Their compassionate creativity suits pediatric medicine, veterinary sciences, creative arts, global travel, diplomacy, and environmental welfare.',
            'career_hi' => 'बाल चिकित्सा (Pediatrics), पशु चिकित्सा, ललित कला, यात्रा व पर्यटन, कूटनीति और पर्यावरण संरक्षण में ये कीर्ति अर्जित करते हैं।'
        ]
    ];

    /**
     * 12 Rashis Complete Encyclopedic Knowledge Base
     */
    private $rashi_kb = [
        'Mesh' => [
            'hi' => 'मेष (Mesh)',
            'lord_en' => 'Mars (Mangal)',
            'lord_hi' => 'मंगल देव',
            'element_en' => 'Fire (Agni)',
            'element_hi' => 'अग्नि तत्व',
            'archetype_en' => 'The Fearless Trailblazer',
            'archetype_hi' => 'साहसी पथप्रदर्शक',
            'personality_en' => 'As the first sign of the zodiac, Aries bestows vibrant courage, unshakeable self-confidence, and a zest for initiation. They are natural trailblazers who meet challenges head-on without hesitation.',
            'personality_hi' => 'भचक्र की प्रथम राशि होने के कारण मेष राशि साहस, अटूट आत्मविश्वास और नई शुरुआत की अद्भुत प्रेरणा देती है। ये बच्चे किसी भी चुनौती से पीछे नहीं हटते।',
            'career_en' => 'Thrives in leadership, military defense, entrepreneurship, emergency medicine, and athletic sports.',
            'career_hi' => 'सेना, पुलिस प्रशासन, स्वतंत्र उद्योग, शल्य चिकित्सा, खेलकूद और नेतृत्वकारी पदों में विशेष प्रगति करते हैं।'
        ],
        'Vrishabh' => [
            'hi' => 'वृषभ (Vrishabh)',
            'lord_en' => 'Venus (Shukra)',
            'lord_hi' => 'शुक्र देव',
            'element_en' => 'Earth (Prithvi)',
            'element_hi' => 'पृथ्वी तत्व',
            'archetype_en' => 'The Grounded Creator',
            'archetype_hi' => 'स्थिर और कलात्मक निर्माता',
            'personality_en' => 'Taurus infuses stability, patience, aesthetic refinement, and practical wisdom. They have a calm temperament and build enduring relationships based on unshakeable loyalty.',
            'personality_hi' => 'वृषभ राशि धैर्य, स्थिरता, सुरुचिपूर्ण सौंदर्यबोध और व्यावहारिक बुद्धि प्रदान करती है। इनका शांत स्वभाव और निष्ठा इन्हें सभी का विश्वसनीय बनाती है।',
            'career_en' => 'Excels in banking, luxury goods, agriculture, architecture, visual arts, and financial advisory.',
            'career_hi' => 'बैंकिंग, वित्त, वास्तुकला, कला, फैशन, आभूषण निर्माण और भूमि-संपत्ति के कार्यों में ये धनवान और सम्मानित होते हैं।'
        ],
        'Mithun' => [
            'hi' => 'मिथुन (Mithun)',
            'lord_en' => 'Mercury (Budha)',
            'lord_hi' => 'बुध देव',
            'element_en' => 'Air (Vayu)',
            'element_hi' => 'वायु तत्व',
            'archetype_en' => 'The Brilliant Communicator',
            'archetype_hi' => 'कुशाग्र बुद्धि और कुशल वक्ता',
            'personality_en' => 'Gemini bestows agile intellect, witty eloquence, and versatile curiosity. They are conversational virtuosos who love learning diverse subjects and connecting people.',
            'personality_hi' => 'मिथुन राशि तीव्र बुद्धि, वाकपटुता और बहुमुखी प्रतिभा का वरदान देती है। ये बच्चे एक साथ कई विधाओं को सीखने और नए विचारों को अपनाने में निपुण होते हैं।',
            'career_en' => 'Thrives in journalism, media, telecommunications, software engineering, marketing, and publishing.',
            'career_hi' => 'पत्रकारिता, मीडिया, जनसंचार, सॉफ्टवेयर विकास, डिजिटल मार्केटिंग और लेखन में इनकी प्रतिभा का लोहा माना जाता है।'
        ],
        'Karka' => [
            'hi' => 'कर्क (Karka)',
            'lord_en' => 'Moon (Chandra)',
            'lord_hi' => 'चंद्र देव',
            'element_en' => 'Water (Jal)',
            'element_hi' => 'जल तत्व',
            'archetype_en' => 'The Compassionate Nurturer',
            'archetype_hi' => 'संवेदनशील और स्नेही संरक्षक',
            'personality_en' => 'Cancer provides profound emotional intuition, protective instincts, and fierce loyalty to home and family. They have a deeply caring heart that senses others feelings instantly.',
            'personality_hi' => 'कर्क राशि अत्यधिक भावनात्मक गहराई, ममता और परिवार के प्रति अगाध प्रेम प्रदान करती है। दूसरों के सुख-दुख को तुरंत भांप लेना इनकी विशेष शक्ति है।',
            'career_en' => 'Excels in healthcare, pediatric care, hospitality, human resources, child psychology, and marine sciences.',
            'career_hi' => 'चिकित्सा, बाल रोग विशेषज्ञ, मानव संसाधन (HR), आतिथ्य सत्कार, मनोविज्ञान और सामाजिक सेवाओं में ये बहुत सफल होते हैं।'
        ],
        'Simha' => [
            'hi' => 'सिंह (Simha)',
            'lord_en' => 'Sun (Surya)',
            'lord_hi' => 'सूर्य देव',
            'element_en' => 'Fire (Agni)',
            'element_hi' => 'अग्नि तत्व',
            'archetype_en' => 'The Regal Sovereign',
            'archetype_hi' => 'प्रतापी और उदार नेतृत्वकर्ता',
            'personality_en' => 'Leo bestows royal charisma, generous magnanimity, and natural commanding authority. They possess an inspiring warmth, hate pettiness, and lead by virtuous example.',
            'personality_hi' => 'सिंह राशि राजसी तेज, असीम उदारता और सहज नेतृत्व का वरदान देती है। इनका व्यक्तित्व चुंबकीय होता है और ये हमेशा बड़े उद्देश्यों के लिए कार्य करते हैं।',
            'career_en' => 'Thrives in politics, public governance, executive management, filmmaking, and public speaking.',
            'career_hi' => 'राजनीति, उच्च प्रशासनिक सेवा, कॉर्पोरेट प्रबंधन, सिनेमा और सार्वजनिक मंचों पर ये शीर्ष स्थान प्राप्त करते हैं।'
        ],
        'Kanya' => [
            'hi' => 'कन्या (Kanya)',
            'lord_en' => 'Mercury (Budha)',
            'lord_hi' => 'बुध देव',
            'element_en' => 'Earth (Prithvi)',
            'element_hi' => 'पृथ्वी तत्व',
            'archetype_en' => 'The Meticulous Analyst',
            'archetype_hi' => 'सटीक विश्लेषक और कर्मठ साधक',
            'personality_en' => 'Virgo infuses analytical precision, intellectual discipline, and a genuine urge to be helpful. They are observant, methodical, and dedicated to excellence in all endeavors.',
            'personality_hi' => 'कन्या राशि सूक्ष्म विश्लेषण, बौद्धिक अनुशासन और परोपकार की भावना प्रदान करती है। ये किसी भी काम को बिना किसी त्रुटि के संपूर्ण करते हैं।',
            'career_en' => 'Excels in medical diagnostics, data science, accounting, forensic investigation, auditing, and research.',
            'career_hi' => 'डेटा साइंस, चिकित्सा निदान, चार्टर्ड अकाउंटेंसी (CA), वित्तीय विश्लेषण, शोध और तकनीकी संपादन में अग्रणी रहते हैं।'
        ],
        'Tula' => [
            'hi' => 'तुला (Tula)',
            'lord_en' => 'Venus (Shukra)',
            'lord_hi' => 'शुक्र देव',
            'element_en' => 'Air (Vayu)',
            'element_hi' => 'वायु तत्व',
            'archetype_en' => 'The Diplomatic Peacemaker',
            'archetype_hi' => 'न्यायप्रिय और सुरुचिपूर्ण कूटनीतिज्ञ',
            'personality_en' => 'Libra gives a deep love for harmony, balance, refined aesthetics, and justice. They are born peacemakers with charming social manners who weigh every decision fairly.',
            'personality_hi' => 'तुला राशि न्याय, संतुलन, सौहार्द और कलात्मक सौंदर्य की प्रतीक है। ये स्वभाव से निष्पक्ष होते हैं और वाद-विवाद को सुलझाने में सिद्धहस्त होते हैं।',
            'career_en' => 'Thrives in law, judiciary, international diplomacy, interior architecture, public relations, and luxury arts.',
            'career_hi' => 'वकालत, न्यायपालिका, विदेश सेवा, जनसंपर्क, इंटीरियर डिजाइनिंग और फैशन जगत में इनका नाम सम्मान से लिया जाता है।'
        ],
        'Vrishchik' => [
            'hi' => 'वृश्चिक (Vrishchik)',
            'lord_en' => 'Mars (Mangal)',
            'lord_hi' => 'मंगल देव',
            'element_en' => 'Water (Jal)',
            'element_hi' => 'जल तत्व',
            'archetype_en' => 'The Deep Mystic & Strategist',
            'archetype_hi' => 'दृढ़ संकल्पी और गूढ़ रणनीतिकार',
            'personality_en' => 'Scorpio bestows profound willpower, magnetic mystery, and transformative emotional depth. They are fiercely loyal, deeply observant, and never surrender in any contest.',
            'personality_hi' => 'वृश्चिक राशि असीम मानसिक शक्ति, अटूट एकाग्रता और रहस्यमयी आकर्षण देती है। ये अपने लक्ष्यों के प्रति पूर्ण समर्पित रहते हैं और कभी हार नहीं मानते।',
            'career_en' => 'Excels in surgery, cyber defense, intelligence services, scientific innovation, psychology, and risk management.',
            'career_hi' => 'शल्य चिकित्सा, साइबर सुरक्षा, गुप्तचर विभाग, रक्षा अनुसंधान, मनोचिकित्सा और जोखिम प्रबंधन में ये कीर्तिमान बनाते हैं।'
        ],
        'Dhanu' => [
            'hi' => 'धनु (Dhanu)',
            'lord_en' => 'Jupiter (Guru)',
            'lord_hi' => 'देवगुरु बृहस्पति',
            'element_en' => 'Fire (Agni)',
            'element_hi' => 'अग्नि तत्व',
            'archetype_en' => 'The Philosophical Explorer',
            'archetype_hi' => 'ज्ञानपिपासु और आशावादी पथप्रदर्शक',
            'personality_en' => 'Sagittarius infuses boundless optimism, love for truth, higher philosophy, and cross-cultural travel. They have a jovial nature, large heart, and inspirational vision.',
            'personality_hi' => 'धनु राशि असीम आशावाद, उच्च ज्ञान, सत्यनिष्ठा और धार्मिक चिंतन प्रदान करती है। इनका दृष्टिकोण व्यापक होता है और ये हमेशा आगे की ओर देखते हैं।',
            'career_en' => 'Thrives in higher education, university professorship, jurisprudence, international law, publishing, and spiritual coaching.',
            'career_hi' => 'उच्च शिक्षा, कानून, अंतरराष्ट्रीय संबंध, धर्म व दर्शन, प्रकाशन और वैश्विक पर्यटन में ये उच्च प्रतिष्ठा पाते हैं।'
        ],
        'Makar' => [
            'hi' => 'मकर (Makar)',
            'lord_en' => 'Saturn (Shani)',
            'lord_hi' => 'शनि देव',
            'element_en' => 'Earth (Prithvi)',
            'element_hi' => 'पृथ्वी तत्व',
            'archetype_en' => 'The Master Builder',
            'archetype_hi' => 'धैर्यवान और शिखरगामी निर्माता',
            'personality_en' => 'Capricorn bestows mature discipline, unwavering patience, and ambitious determination. They understand the value of persistent labor and steadily climb to the pinnacle of success.',
            'personality_hi' => 'मकर राशि अनुशासन, अथक परिश्रम और धैर्य का प्रतीक है। ये बच्चे जीवन में धीरे-धीरे किंतु निरंतर प्रगति करते हुए सफलता के शिखर तक पहुंचते हैं।',
            'career_en' => 'Excels in corporate governance, civil infrastructure, heavy industry, finance, public administration, and real estate.',
            'career_hi' => 'कॉर्पोरेट प्रबंधन, सिविल निर्माण, वित्तीय संस्थान, उच्च प्रशासनिक पद और उद्योग जगत में ये शीर्ष नेतृत्व संभालते हैं।'
        ],
        'Kumbh' => [
            'hi' => 'कुंभ (Kumbh)',
            'lord_en' => 'Saturn (Shani)',
            'lord_hi' => 'शनि देव',
            'element_en' => 'Air (Vayu)',
            'element_hi' => 'वायु तत्व',
            'archetype_en' => 'The Visionary Humanitarian',
            'archetype_hi' => 'क्रांतिकारी विचारक और समाजसेवी',
            'personality_en' => 'Aquarius gives progressive vision, intellectual originality, and a deep commitment to humanity. They are independent thinkers who champion progress, innovation, and equality.',
            'personality_hi' => 'कुंभ राशि प्रगतिशील सोच, नवीन आविष्कारों और सामाजिक कल्याण की प्रेरणा देती है। ये लीक से हटकर सोचने वाले और मानवता के हितैषी होते हैं।',
            'career_en' => 'Thrives in scientific innovation, aviation, space research, social advocacy, computer architecture, and renewable energy.',
            'career_hi' => 'वैज्ञानिक शोध, अंतरिक्ष विज्ञान, कंप्यूटर आर्किटेक्चर, सामाजिक संस्थाओं और पर्यावरण संरक्षण में ये अग्रणी भूमिका निभाते हैं।'
        ],
        'Meen' => [
            'hi' => 'मीन (Meen)',
            'lord_en' => 'Jupiter (Guru)',
            'lord_hi' => 'देवगुरु बृहस्पति',
            'element_en' => 'Water (Jal)',
            'element_hi' => 'जल तत्व',
            'archetype_en' => 'The Compassionate Visionary',
            'archetype_hi' => 'आध्यात्मिक और परम करुणामयी आत्मा',
            'personality_en' => 'Pisces infuses boundless compassion, rich creative imagination, and spiritual peace. They are intuitive dreamers who possess a poetic soul and deep empathy for all living beings.',
            'personality_hi' => 'मीन राशि अगाध करुणा, समृद्ध कल्पनाशक्ति और आध्यात्मिक शांति का वरदान देती है। इनके भीतर सभी प्राणियों के प्रति प्रेम और परोपकार की भावना होती है।',
            'career_en' => 'Excels in fine arts, music, spiritual philosophy, veterinary medicine, psychology, literature, and philanthropic charities.',
            'career_hi' => 'संगीत, ललित कला, साहित्य, मनोविज्ञान, अध्यात्म और जीव कल्याण के कार्यों में इनकी ख्याति दूर-दूर तक फैलती है।'
        ]
    ];

    /**
     * 9 Numerology Life Path Vibrations (Chaldean / Pythagorean)
     */
    private $numerology_kb = [
        1 => [
            'planet_en' => 'Sun (Surya)',
            'planet_hi' => 'भगवान सूर्य',
            'title_en' => 'The Independent Leader & Pioneer',
            'title_hi' => 'स्वतंत्र नेतृत्वकर्ता और नवप्रवर्तक',
            'desc_en' => 'Number 1 resonates with the supreme solar power of the Sun, conferring bold leadership, high ambition, and unshakeable willpower. Children under this vibration naturally take initiative and inspire others by setting high standards.',
            'desc_hi' => 'अंक 1 सूर्य देव के ओज और तेज से संचालित होता है, जो जातक को उच्च स्वाभिमान, नेतृत्व क्षमता और स्वतंत्र निर्णय लेने की शक्ति प्रदान करता है। यह बच्चा किसी का अनुसरण करने के बजाय अपना नया मार्ग खुद बनाता है।',
            'career_en' => 'Natural fit for administrative leadership, government civil services, entrepreneurship, corporate direction, and executive management.',
            'career_hi' => 'प्रशासनिक सेवाओं (IAS/IPS), स्वतंत्र व्यवसाय, कॉर्पोरेट नेतृत्व और नीति निर्माण के क्षेत्रों में असाधारण उन्नति करते हैं।'
        ],
        2 => [
            'planet_en' => 'Moon (Chandra)',
            'planet_hi' => 'चंद्र देव',
            'title_en' => 'The Intuitive Diplomat & Peacemaker',
            'title_hi' => 'संवेदनशील कूटनीतिज्ञ और शांतिदूत',
            'desc_en' => 'Number 2 is ruled by the Moon, bestowing gentleness, diplomatic tact, emotional intelligence, and harmonious cooperation. These children possess deep empathy and build lasting, loving bridges between people.',
            'desc_hi' => 'अंक 2 चंद्रमा की सौम्यता और शीतलता से युक्त होता है। यह जातक को अत्यधिक संवेदनशील, दयालु और कुशल मध्यस्थ बनाता है। ये अपने मधुर व्यवहार से कठिन से कठिन विवादों को भी सहजता से सुलझा लेते हैं।',
            'career_en' => 'Thrives in diplomacy, counseling, pediatric medicine, creative arts, human resources, and social work.',
            'career_hi' => 'कूटनीति, मानव संसाधन, बाल रोग चिकित्सा, संगीत व कला, और परामर्श सेवाओं में ये विशेष प्रतिष्ठा प्राप्त करते हैं।'
        ],
        3 => [
            'planet_en' => 'Jupiter (Guru)',
            'planet_hi' => 'देवगुरु बृहस्पति',
            'title_en' => 'The Creative Optimist & Scholar',
            'title_hi' => 'सृजनशील विचारक और ज्ञानवान मार्गदर्शक',
            'desc_en' => 'Number 3 vibrates to the wisdom of Jupiter, imparting intellectual brilliance, joyful optimism, artistic creativity, and charismatic eloquence. These children brighten every room with their wit and expansive vision.',
            'desc_hi' => 'अंक 3 देवगुरु बृहस्पति के ज्ञान और विस्तार का प्रतीक है। यह बच्चे को तीव्र बुद्धि, सृजनात्मकता और उच्च दार्शनिक समझ प्रदान करता है। इनकी बातचीत में एक विशेष आकर्षण और सकारात्मकता होती है।',
            'career_en' => 'Excels in higher education, law, journalism, authorship, public speaking, finance, and performing arts.',
            'career_hi' => 'उच्च शिक्षा, वकालत, पत्रकारिता, साहित्य सृजन, वक्तृत्व और वित्तीय प्रबंधन में ये बहुत नाम कमाते हैं।'
        ],
        4 => [
            'planet_en' => 'Rahu (राहु)',
            'planet_hi' => 'राहु देव',
            'title_en' => 'The Pragmatic Architect & Master Builder',
            'title_hi' => 'व्यावहारिक वास्तुकार और कर्मठ निर्माता',
            'desc_en' => 'Number 4 represents grounded discipline, honesty, structural genius, and persistent determination. These children are practical problem-solvers who create durable success through step-by-step perseverance.',
            'desc_hi' => 'अंक 4 स्थिरता, अनुशासन, निष्कपटता और तकनीकी समझ का प्रतीक है। ये बच्चे हवाई बातों में विश्वास नहीं करते, बल्कि धरातल पर काम करके स्थायी सफलता और समृद्धि का निर्माण करते हैं।',
            'career_en' => 'Natural fit for civil engineering, information technology, architecture, accounting, law enforcement, and operations management.',
            'career_hi' => 'सिविल इंजीनियरिंग, सूचना प्रौद्योगिकी (IT), आर्किटेक्चर, बैंकिंग, सुरक्षा और निर्माण उद्योग में इनकी विशेष मांग होती है।'
        ],
        5 => [
            'planet_en' => 'Mercury (Budha)',
            'planet_hi' => 'बुध देव',
            'title_en' => 'The Versatile Explorer & Communicator',
            'title_hi' => 'बहुमुखी प्रतिभा के धनी और कुशल वक्ता',
            'desc_en' => 'Number 5 is governed by Mercury, gifting lightning-quick wit, adaptable intellect, linguistic versatility, and high curiosity. These children grasp complex ideas instantly and love dynamic learning.',
            'desc_hi' => 'अंक 5 बुध ग्रह की तीक्ष्ण बुद्धि और गतिशीलता से परिपूर्ण है। यह बच्चे को अद्भुत हाजिरजवाबी, भाषा ज्ञान और त्वरित अनुकूलन क्षमता देता है। ये किसी भी नए विषय को पलक झपकते सीख लेते हैं।',
            'career_en' => 'Thrives in sales, marketing, global trade, journalism, travel curation, software engineering, and digital media.',
            'career_hi' => 'विपणन (मार्केटिंग), अंतरराष्ट्रीय व्यापार, जनसंचार, सॉफ्टवेयर विकास और मीडिया प्रबंधन में ये तेजी से सफलता पाते हैं।'
        ],
        6 => [
            'planet_en' => 'Venus (Shukra)',
            'planet_hi' => 'शुक्र देव',
            'title_en' => 'The Harmonizer, Healer & Nurturer',
            'title_hi' => 'सौंदर्यप्रिय संरक्षक और परिवार का आधार',
            'desc_en' => 'Number 6 vibrates to the grace of Venus, bestowing deep devotion to family, aesthetic charm, healing warmth, and unconditional generosity. These children bring harmony, peace, and abundance to their homes.',
            'desc_hi' => 'अंक 6 शुक्र देव के सौंदर्य, प्रेम और करुणा का प्रतिनिधित्व करता है। यह बच्चे को पारिवारिक मूल्यों का रक्षक, कला का पारखी और अत्यंत स्नेही बनाता है। परिवार में सुख-शांति बनाए रखना इनकी प्राथमिकता होती है।',
            'career_en' => 'Excels in design, fine arts, hospitality, medical therapies, interior decor, luxury goods, and family law.',
            'career_hi' => 'फैशन डिजाइनिंग, चिकित्सा, आतिथ्य सत्कार, इंटीरियर डेकोरेशन, संगीत और समाज कल्याण में ये लोकप्रिय होते हैं।'
        ],
        7 => [
            'planet_en' => 'Ketu (केतु)',
            'planet_hi' => 'केतु देव',
            'title_en' => 'The Deep Thinker, Seeker & Philosopher',
            'title_hi' => 'आध्यात्मिक साधक और गहन अन्वेषक',
            'desc_en' => 'Number 7 is ruled by Ketu, conferring profound philosophical depth, spiritual intuition, analytical brilliance, and love for wisdom. These children look beyond surface illusions to find the timeless truth.',
            'desc_hi' => 'अंक 7 आध्यात्मिक गहराई, तीक्ष्ण अंतर्ज्ञान और वैज्ञानिक दृष्टिकोण का संगम है। ये बच्चे भौतिक चकाचौंध से अप्रभावित रहकर जीवन के गहरे रहस्यों और ज्ञान की खोज में लीन रहते हैं।',
            'career_en' => 'Natural fit for scientific research, advanced analytics, astronomy, philosophy, medical diagnosis, and spiritual guidance.',
            'career_hi' => 'वैज्ञानिक शोध, उन्नत डेटा विश्लेषण, खगोल विज्ञान, दर्शनशास्त्र, मनोविश्लेषण और अध्यात्म में ये मार्गदर्शक बनते हैं।'
        ],
        8 => [
            'planet_en' => 'Saturn (Shani)',
            'planet_hi' => 'शनि देव',
            'title_en' => 'The Resilient Strategist & Master Executive',
            'title_hi' => 'दृढ़ कर्मयोगी और उच्च रणनीतिकार',
            'desc_en' => 'Number 8 is governed by Saturn, the karmic master of endurance, justice, financial governance, and high organizational power. These children achieve enduring greatness through unwavering patience and integrity.',
            'desc_hi' => 'अंक 8 कर्मफलदाता शनि देव के अनुशासन, न्याय और दृढ़ता से युक्त है। यह बच्चे को असाधारण संगठन क्षमता, वित्तीय सूझबूझ और धैर्य प्रदान करता है। ये जीवन में कभी शॉर्टकट नहीं लेते और दीर्घकालिक साम्राज्य खड़ा करते हैं।',
            'career_en' => 'Thrives in corporate board leadership, heavy industries, judiciary, investment banking, real estate, and public policy.',
            'career_hi' => 'कॉर्पोरेट डायरेक्टर्स, बड़े उद्योग, न्यायपालिका, निवेश बैंकिंग और रियल एस्टेट में ये सर्वोच्च प्रतिष्ठा हासिल करते हैं।'
        ],
        9 => [
            'planet_en' => 'Mars (Mangal)',
            'planet_hi' => 'मंगल देव',
            'title_en' => 'The Courageous Humanitarian & Champion',
            'title_hi' => 'साहसी परोपकारी और मानवीय मूल्यों के रक्षक',
            'desc_en' => 'Number 9 represents Mars in its most noble and universal expression, conferring fearless bravery, large-hearted compassion, chivalry, and high energy. These children stand up for the vulnerable with fierce devotion.',
            'desc_hi' => 'अंक 9 मंगल देव के पराक्रम और सार्वभौमिक करुणा का परिचायक है। यह बच्चे को निडर, उदारमना और अन्याय के खिलाफ लड़ने वाला योद्धा बनाता है। इनका हृदय अत्यंत विशाल और परोपकारी होता है।',
            'career_en' => 'Excels in defense services, surgical medicine, international charity leadership, sports, law enforcement, and civil rights.',
            'career_hi' => 'सैन्य सेवा, पुलिस, सर्जरी, अंतरराष्ट्रीय मानवाधिकार, खेलकूद और सामाजिक आंदोलनों में ये प्रेरणास्रोत बनते हैं।'
        ]
    ];

    /**
     * Resolves Nakshatra Encyclopedic Record
     */
    public function get_nakshatra_info($nakshatra_name)
    {
        $clean = trim($nakshatra_name);
        return $this->nakshatra_kb[$clean] ?? $this->nakshatra_kb['Ashwini'];
    }

    /**
     * Resolves Rashi Encyclopedic Record
     */
    public function get_rashi_info($rashi_name)
    {
        $clean = trim($rashi_name);
        // Handle names like "Mesh (Aries)" or "Mesh"
        foreach ($this->rashi_kb as $k => $v) {
            if (stripos($clean, $k) !== false) {
                return $v;
            }
        }
        return $this->rashi_kb['Mesh'];
    }

    /**
     * Resolves Numerology Encyclopedic Record
     */
    public function get_numerology_info($number)
    {
        $num = (int)$number;
        if ($num < 1 || $num > 9) {
            $num = 1;
        }
        return $this->numerology_kb[$num];
    }

    /**
     * Generates deep 3-paragraph Master Personality in English and authentic Hindi
     */
    public function generate_master_personality($name, $gender, $religion, array $astro_core)
    {
        $v = $astro_core['vedic_astrology'] ?? [];
        $rashi_raw = $v['rashi_moon_sign'] ?? 'Mesh';
        $nak_raw   = $v['nakshatra'] ?? 'Ashwini';
        $pada      = $v['nakshatra_pada'] ?? 1;
        $num       = (int)($astro_core['numerology']['chaldean_number'] ?? 1);

        $r_info = $this->get_rashi_info($rashi_raw);
        $n_info = $this->get_nakshatra_info($nak_raw);
        $num_info = $this->get_numerology_info($num);

        $gender_word_en = ($gender === 'Girl') ? 'girl' : 'boy';
        $gender_word_hi = ($gender === 'Girl') ? 'कन्या' : 'बालक';
        $pronoun_en     = ($gender === 'Girl') ? 'she' : 'he';
        $possessive_en  = ($gender === 'Girl') ? 'her' : 'his';

        // Paragraph 1: Moon Sign & Ruling Planet Vibrational Core
        $en_p1 = "A child blessed with the name {$name} embodies the vibrant vibrational energy of {$r_info['hi']} ({$rashi_raw}) and its celestial lord, {$r_info['lord_en']}. Governed by the dynamic archetype of \"{$r_info['archetype_en']}\", this {$gender_word_en} radiates natural self-confidence, warmth, and inner magnetism. Under the elemental influence of {$r_info['element_en']}, {$name} approaches daily life with clarity of purpose and a cheerful disposition that immediately puts peers and elders at ease.";
        
        $hi_p1 = "{$name} नाम का यह {$gender_word_hi} {$r_info['hi']} और स्वामी ग्रह {$r_info['lord_hi']} के शुभ प्रभाव से संपन्न है। \"{$r_info['archetype_hi']}\" के प्रभाव स्वरूप इस बच्चे के व्यक्तित्व में प्राकृतिक तेज, आत्मविश्वास और विशेष आकर्षण झलकता है। {$r_info['element_hi']} के सामंजस्य से {$name} में सौम्य बातचीत, सकारात्मक ऊर्जा और अपने कुल को गौरवान्वित करने की अद्भुत जन्मजात क्षमता होती है।";

        // Paragraph 2: Nakshatra & Deity Psychological Depth
        $en_p2 = "At a deeper psychological level, the placement in {$n_info['hi']} ({$nak_raw}) Nakshatra (Pada {$pada}) imparts exceptional character depth and mental resilience. Blessed by the divine deity {$n_info['deity']}, {$name} is characterized by {$n_info['traits_en']} {$n_info['personality_en']} Whether engaged in academic studies, creative hobbies, or sports, {$pronoun_en} displays patience, deep focus, and an innate respect for moral principles.";

        $hi_p2 = "जन्म नक्षत्र {$n_info['hi']} (चरण {$pada}) और आराध्य देव {$n_info['deity']} के आशीर्वाद से {$name} में अगाध मानसिक धैर्य और तीक्ष्ण बुद्धि का सुंदर संगम देखने को मिलता है। {$n_info['traits_hi']} {$n_info['personality_hi']} यह बच्चा पढ़ाई-लिखाई, कला और खेलकूद में अपनी गहरी एकाग्रता और बड़ों के प्रति आदर भाव के कारण समाज में विशेष प्रतिष्ठा प्राप्त करता है।";

        // Paragraph 3: Numerology Life Path & Interpersonal Harmony
        $en_p3 = "Under Chaldean Lucky Number {$num}, guided by {$num_info['planet_en']} as \"{$num_info['title_en']}\", {$name} possesses a clear life purpose and natural problem-solving abilities. {$num_info['desc_en']} In friendships and family relationships, {$name} is fiercely loyal, generous, and dependable, serving as a pillar of emotional strength and joyful optimism for everyone around {$possessive_en}.";

        $hi_p3 = "अंकशास्त्र के मुख्य शुभ अंक {$num} (स्वामी: {$num_info['planet_hi']}) के शुभ संयोग से यह बच्चा \"{$num_info['title_hi']}\" के रूप में जीवन में सदैव आगे बढ़ता है। {$num_info['desc_hi']} मित्रों और परिजनों के साथ {$name} का संबंध अत्यंत प्रगाढ़, सच्चा और सहयोगात्मक रहता है, जिससे यह परिवार में सदैव सुख, शांति और समृद्धि का आधार बनता है।";

        return [
            'en' => [$en_p1, $en_p2, $en_p3],
            'hi' => [$hi_p1, $hi_p2, $hi_p3]
        ];
    }

    /**
     * Generates deep 3-paragraph Master Career Predictions in English and authentic Hindi
     */
    public function generate_master_career($name, $gender, $religion, array $astro_core)
    {
        $v = $astro_core['vedic_astrology'] ?? [];
        $rashi_raw = $v['rashi_moon_sign'] ?? 'Mesh';
        $nak_raw   = $v['nakshatra'] ?? 'Ashwini';
        $num       = (int)($astro_core['numerology']['chaldean_number'] ?? 1);

        $r_info   = $this->get_rashi_info($rashi_raw);
        $n_info   = $this->get_nakshatra_info($nak_raw);
        $num_info = $this->get_numerology_info($num);

        $pronoun_en    = ($gender === 'Girl') ? 'she' : 'he';
        $possessive_en = ($gender === 'Girl') ? 'her' : 'his';

        // Paragraph 1: Academic Brilliance & Learning Strengths
        $en_p1 = "In academic learning and skill acquisition, {$name} demonstrates remarkable focus, memory retention, and conceptual clarity. Guided by planetary lord {$r_info['lord_en']} and the intellectual faculty of {$n_info['hi']} Nakshatra, {$pronoun_en} quickly masters challenging subjects and enjoys connecting theoretical knowledge with real-world applications. Teachers and mentors will appreciate {$possessive_en} inquisitive mind and thorough work ethic.";

        $hi_p1 = "शिक्षा और ज्ञानार्जन के क्षेत्र में {$name} नाम के जातक अपनी प्रखर बुद्धि, तीव्र स्मरण शक्ति और गहरी एकाग्रता के बल पर उत्कृष्ट प्रदर्शन करते हैं। स्वामी ग्रह {$r_info['lord_hi']} और नक्षत्र {$n_info['hi']} के शुभ प्रभाव से यह बच्चा कठिन से कठिन विषयों को भी सहजता से समझ लेता है और विद्यालय तथा उच्च शिक्षा में सदैव अग्रणी रहता है।";

        // Paragraph 2: High-Success Professional Sectors
        $en_p2 = "Astrologically and numerologically, the alignment of {$r_info['hi']} and Lucky Number {$num} creates outstanding career resonance in diverse high-impact sectors. {$r_info['career_en']} In addition, the energetic vibration of {$n_info['hi']} supports {$n_info['career_en']} Furthermore, with {$num_info['title_en']} attributes, {$num_info['career_en']}";

        $hi_p2 = "ज्योतिषीय और अंकशास्त्रीय दृष्टि से राशि {$r_info['hi']} और शुभ अंक {$num} का अद्भुत मेल कई प्रतिष्ठित कार्यक्षेत्रों में बड़ी सफलता के द्वार खोलता है। {$r_info['career_hi']} इसके अतिरिक्त जन्म नक्षत्र के प्रभाव से {$n_info['career_hi']} साथ ही \"{$num_info['title_hi']}\" की क्षमताओं के कारण {$num_info['career_hi']}";

        // Paragraph 3: Leadership, Ethics & Long-Term Wealth Prosperity
        $en_p3 = "In professional environments, {$name} is endowed with natural leadership, strategic foresight, and exemplary ethical standards. Rather than relying on shortcuts, {$pronoun_en} builds enduring success upon dedication and methodical planning. As maturity approaches, this child is destined to command high social respect, stable financial prosperity, and positions of meaningful executive authority.";

        $hi_p3 = "कार्यक्षेत्र में {$name} में जन्मजात नेतृत्व, दूरदर्शिता और निष्पक्ष निर्णय लेने का अद्भुत गुण देखने को मिलता है। यह जातक किसी भी कार्य को योजनाबद्ध तरीके से पूरा करता है। समय के साथ यह बच्चा समाज में उच्च मान-सम्मान, स्थायी आर्थिक समृद्धि और प्रतिष्ठित प्रशासनिक या व्यावसायिक पदों को सुशोभित करता है।";

        return [
            'en' => [$en_p1, $en_p2, $en_p3],
            'hi' => [$hi_p1, $hi_p2, $hi_p3]
        ];
    }

    /**
     * Generates 4 Comprehensive Bilingual FAQs tailored directly to the Name's Astrology & Numerology
     */
    public function generate_master_faqs($name, $gender, $religion, array $astro_core)
    {
        $v = $astro_core['vedic_astrology'] ?? [];
        $rashi_raw = $v['rashi_moon_sign'] ?? 'Mesh';
        $nak_raw   = $v['nakshatra'] ?? 'Ashwini';
        $pada      = $v['nakshatra_pada'] ?? 1;
        $num       = (int)($astro_core['numerology']['chaldean_number'] ?? 1);
        $pyth      = (int)($astro_core['numerology']['pythagorean_number'] ?? 1);
        $gemstone  = $astro_core['auspicious_attributes']['lucky_gemstone'] ?? 'Ruby';
        $colors    = implode(', ', $astro_core['auspicious_attributes']['lucky_colors'] ?? ['Gold', 'White']);
        $days      = implode(', ', $astro_core['auspicious_attributes']['lucky_days'] ?? ['Sunday', 'Tuesday']);

        $r_info = $this->get_rashi_info($rashi_raw);
        $n_info = $this->get_nakshatra_info($nak_raw);
        $num_info = $this->get_numerology_info($num);

        return [
            [
                'question' => "What is the meaning and Vedic origin of the name {$name}?",
                'question_hi' => "{$name} नाम का क्या अर्थ और वैदिक उत्पत्ति है?",
                'answer' => "The name {$name} is a cherished {$religion} baby name rooted in ancient Indian linguistic heritage, symbolizing auspiciousness, virtue, and illumination. Astrologically governed by {$r_info['hi']} ({$rashi_raw}) and planetary lord {$r_info['lord_en']}, this name carries high vibrational resonance that fosters intellect, dignity, and family prosperity.",
                'answer_hi' => "{$name} भारतीय वैदिक एवं {$religion} परंपरा का एक अत्यंत सुंदर और मांगलिक नाम है, जिसका संबंध प्रकाश, सदगुण और यश से है। यह नाम राशि {$r_info['hi']} और स्वामी ग्रह {$r_info['lord_hi']} के शुभ प्रभाव से संचालित होता है, जो बच्चे के जीवन में सुख, शांति और समृद्धि का संचार करता है।"
            ],
            [
                'question' => "What is the Rashi (Moon Sign) and Nakshatra for {$name}?",
                'question_hi' => "{$name} नाम की राशि और नक्षत्र कौन सा है?",
                'answer' => "Based on Vedic phonetic principles, the name {$name} falls under the Moon sign {$r_info['hi']} ({$rashi_raw}) and the auspicious Nakshatra {$n_info['hi']} ({$nak_raw}), specifically Pada {$pada}. Its ruling cosmic deity is {$n_info['deity']}, which blesses the child with {$n_info['traits_en']}",
                'answer_hi' => "वैदिक नामकरण प्रणाली के अनुसार {$name} नाम की चंद्र राशि {$r_info['hi']} और जन्म नक्षत्र {$n_info['hi']} (चरण {$pada}) है। इसके अधिष्ठाता देव {$n_info['deity']} हैं, जो बच्चे को {$n_info['traits_hi']} प्रदान करते हैं।"
            ],
            [
                'question' => "What are the lucky numbers, gemstones, and colors for {$name}?",
                'question_hi' => "{$name} नाम के लिए शुभ अंक, रत्न, रंग और दिन कौन से हैं?",
                'answer' => "According to Chaldean numerology, the primary Lucky Number for {$name} is {$num} (guided by {$num_info['planet_en']}), alongside Pythagorean Expression Number {$pyth}. The auspicious gemstone is {$gemstone}, the most favorable colors are {$colors}, and the luckiest days of the week are {$days}.",
                'answer_hi' => "कीरो अंकशास्त्र के अनुसार {$name} नाम का मुख्य शुभ अंक {$num} (स्वामी: {$num_info['planet_hi']}) और पाइथागोरस अंक {$pyth} है। इस नाम के लिए शुभ रत्न {$gemstone}, अनुकूल रंग {$colors} तथा सप्ताह के सबसे भाग्यशाली दिन {$days} हैं।"
            ],
            [
                'question' => "Which careers and life paths are most suitable for {$name}?",
                'question_hi' => "{$name} नाम के बच्चे के लिए कौन से करियर और कार्यक्षेत्र सबसे शुभ हैं?",
                'answer' => "Blessed by the alignment of {$r_info['hi']} and Lucky Number {$num}, {$name} has immense natural talent for leadership, structured thinking, and communication. High-success fields include {$r_info['career_en']} In addition, {$n_info['career_en']}",
                'answer_hi' => "राशि {$r_info['hi']} और शुभ अंक {$num} के प्रभाव से {$name} में प्रशासनिक क्षमता, नवाचार और बौद्धिक नेतृत्व के विशेष गुण होते हैं। इसके लिए {$r_info['career_hi']} इसके साथ ही {$n_info['career_hi']}"
            ]
        ];
    }

    /**
     * Generates culturally harmonious sibling and similar names based on element and rashi
     */
    public function generate_harmonious_siblings($name, $gender, $religion, $rashi_key)
    {
        $sibling_bank = [
            'Mesh' => [
                'boys' => ['Aarav', 'Advait', 'Aryan', 'Ayush', 'Reyansh', 'Anay'],
                'girls' => ['Aadhya', 'Ananya', 'Aarohi', 'Avani', 'Arya', 'Anvi']
            ],
            'Vrishabh' => [
                'boys' => ['Vivaan', 'Vihaan', 'Varun', 'Vedant', 'Bhavin', 'Vansh'],
                'girls' => ['Vanshika', 'Vedika', 'Vrinda', 'Vaani', 'Bhavya', 'Vidhi']
            ],
            'Mithun' => [
                'boys' => ['Kabir', 'Kiaan', 'Kavish', 'Kush', 'Keshav', 'Karan'],
                'girls' => ['Kiara', 'Kavya', 'Krisha', 'Khushi', 'Kritika', 'Kanika']
            ],
            'Karka' => [
                'boys' => ['Devansh', 'Dhruv', 'Darsh', 'Daksh', 'Hridhaan', 'Harsh'],
                'girls' => ['Diya', 'Divya', 'Drishti', 'Dhruvi', 'Disha', 'Haimi']
            ],
            'Simha' => [
                'boys' => ['Manan', 'Moksh', 'Madhav', 'Mayank', 'Meet', 'Mihir'],
                'girls' => ['Myra', 'Mira', 'Meera', 'Mishka', 'Manya', 'Mansha']
            ],
            'Kanya' => [
                'boys' => ['Pranay', 'Parth', 'Pranav', 'Praneel', 'Piyush', 'Param'],
                'girls' => ['Prisha', 'Pari', 'Prachi', 'Pooja', 'Palak', 'Pakhi']
            ],
            'Tula' => [
                'boys' => ['Rudra', 'Rohan', 'Rishi', 'Reyan', 'Ronit', 'Riaan'],
                'girls' => ['Riya', 'Rhea', 'Ridhima', 'Radhika', 'Rupal', 'Roshni']
            ],
            'Vrishchik' => [
                'boys' => ['Neev', 'Naksh', 'Nirvaan', 'Naitik', 'Naman', 'Nayan'],
                'girls' => ['Navya', 'Nisha', 'Netra', 'Nitya', 'Niharika', 'Nandini']
            ],
            'Dhanu' => [
                'boys' => ['Bhavya', 'Bhuvan', 'Bharat', 'Bhanu', 'Bhairav', 'Bhupen'],
                'girls' => ['Bhavna', 'Bhumi', 'Bhumika', 'Barkha', 'Bhavika', 'Bhairavi']
            ],
            'Makar' => [
                'boys' => ['Jai', 'Jatin', 'Jayant', 'Jeevan', 'Jash', 'Jagan'],
                'girls' => ['Jiya', 'Janvi', 'Jhanvi', 'Jahnavi', 'Jasleen', 'Jagriti']
            ],
            'Kumbh' => [
                'boys' => ['Samar', 'Shaurya', 'Shivansh', 'Siddharth', 'Sai', 'Shlok'],
                'girls' => ['Saanvi', 'Siya', 'Shanaya', 'Sara', 'Samaira', 'Shreya']
            ],
            'Meen' => [
                'boys' => ['Chirag', 'Chetan', 'Chinmay', 'Chaitanya', 'Charan', 'Chandan'],
                'girls' => ['Charvi', 'Chhavi', 'Chahat', 'Chandani', 'Charita', 'Chetna']
            ]
        ];

        $matched_key = 'Tula';
        foreach (array_keys($sibling_bank) as $k) {
            if (stripos($rashi_key, $k) !== false) {
                $matched_key = $k;
                break;
            }
        }
        $pool = $sibling_bank[$matched_key];
        
        // Filter out the name itself
        $clean_name = ucfirst(strtolower(trim($name)));
        $filtered_boys = array_values(array_filter($pool['boys'], function($b) use ($clean_name) {
            return strcasecmp($b, $clean_name) !== 0;
        }));
        $filtered_girls = array_values(array_filter($pool['girls'], function($g) use ($clean_name) {
            return strcasecmp($g, $clean_name) !== 0;
        }));

        return [
            'brother_names' => array_slice($filtered_boys, 0, 3),
            'sister_names'  => array_slice($filtered_girls, 0, 3)
        ];
    }
}
