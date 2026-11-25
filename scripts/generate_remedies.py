#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MeraRashifal - Astrological Remedies Dataset & Metadata Generator
Author: Antigravity AI
Purpose: Upgrades all 960 slices of `af_remedies` with rich, SEO/GEO-optimized,
         authentic bilingual (English & Hindi) copy, structured checklists, FAQs,
         and AI-search-ready answers.
"""

import sys
import os
import json
import argparse
import pymysql

# Force UTF-8 stdout for Windows consoles
if sys.stdout.encoding != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

# ==============================================================================
# ASTROLOGICAL KNOWLEDGE BASE: SIGNS & ELEMENTS
# ==============================================================================

ZODIAC_SIGNS = {
    'Aries': {
        'hi': 'मेष',
        'ruler_en': 'Mars',
        'ruler_hi': 'मंगल',
        'element_en': 'Fire',
        'element_hi': 'अग्नि',
        'deity_en': 'Lord Hanuman & Kartikeya',
        'deity_hi': 'भगवान हनुमान एवं कार्तिकेय',
        'mantra_en': 'Om Kram Kreem Kroum Sah Bhaumaya Namah',
        'mantra_hi': 'ॐ क्रां क्रीं क्रौं सः भौमाय नमः',
        'nature_en': 'dynamic, ambitious, quick-tempered, and pioneering',
        'nature_hi': 'ऊर्जावान, महत्वाकांक्षी, त्वरित प्रतिक्रिया देने वाले और साहसी',
        'weakness_en': 'impatience, impulsiveness, and burnout from excessive friction',
        'weakness_hi': 'अधीरता, जल्दबाजी और अत्यधिक उत्तेजना',
        'gemstone_default': 'Red Coral (Moonga)',
        'gemstone_hi': 'मूंगा (Red Coral)',
        'gemstone_finger': 'Ring finger in copper or gold on Tuesday morning',
        'gemstone_finger_hi': 'मंगलवार प्रातः अनामिका अंगुली में तांबे या सोने में',
        'daan_item_en': 'Red lentils (masoor dal), jaggery, or copper utensils to young men or temple priests on Tuesdays',
        'daan_item_hi': 'मंगलवार को लाल मसूर की दाल, गुड़ या तांबे के बर्तन का दान',
    },
    'Taurus': {
        'hi': 'वृषभ',
        'ruler_en': 'Venus',
        'ruler_hi': 'शुक्र',
        'element_en': 'Earth',
        'element_hi': 'पृथ्वी',
        'deity_en': 'Goddess Mahalakshmi',
        'deity_hi': 'माँ महालक्ष्मी',
        'mantra_en': 'Om Dram Dreem Droum Sah Shukraya Namah',
        'mantra_hi': 'ॐ द्रां द्रीं द्रौं सः शुक्राय नमः',
        'nature_en': 'steady, grounded, aesthetic, and persevering',
        'nature_hi': 'स्थिर, व्यावहारिक, निष्ठावान और सौंदर्यप्रिय',
        'weakness_en': 'stubbornness, resistance to change, and material anxiety',
        'weakness_hi': 'हठ, परिवर्तन का विरोध और भौतिक असुरक्षा की भावना',
        'gemstone_default': 'Diamond or White Sapphire',
        'gemstone_hi': 'हीरा (Diamond) अथवा श्वेत पुखराज',
        'gemstone_finger': 'Middle or little finger in silver or platinum on Friday morning',
        'gemstone_finger_hi': 'शुक्रवार प्रातः मध्यमा अथवा कनिष्ठिका अंगुली में चांदी या प्लैटिनम में',
        'daan_item_en': 'White rice, sugar, milk sweets, or fragrant items to women or temples on Fridays',
        'daan_item_hi': 'शुक्रवार को सफेद चावल, मिश्री, दूध से बनी मिठाई या सुहाग सामग्री का दान',
    },
    'Gemini': {
        'hi': 'मिथुन',
        'ruler_en': 'Mercury',
        'ruler_hi': 'बुध',
        'element_en': 'Air',
        'element_hi': 'वायु',
        'deity_en': 'Lord Ganesha',
        'deity_hi': 'भगवान श्री गणेश',
        'mantra_en': 'Om Bram Breem Broum Sah Budhaya Namah',
        'mantra_hi': 'ॐ ब्रां ब्रीं ब्रौं सः बुधाय नमः',
        'nature_en': 'intellectually agile, curious, communicative, and adaptable',
        'nature_hi': 'कुशाग्र बुद्धि, जिज्ञासु, मिलनसार और बहुमुखी',
        'weakness_en': 'mental restlessness, scattered focus, and nervous exhaustion',
        'weakness_hi': 'मानसिक अस्थिरता, ध्यान का बिखराव और अनिर्णय की स्थिति',
        'gemstone_default': 'Emerald (Panna)',
        'gemstone_hi': 'पन्ना (Emerald)',
        'gemstone_finger': 'Little finger in gold, silver, or bronze on Wednesday morning',
        'gemstone_finger_hi': 'बुधवार प्रातः कनिष्ठिका अंगुली में सोने अथवा चांदी में',
        'daan_item_en': 'Whole green moong dal, green fodder to cows, or educational stationery to needy students on Wednesdays',
        'daan_item_hi': 'बुधवार को साबुत हरी मूंग की दाल, गाय को हरा चारा या निर्धन विद्यार्थियों को शिक्षा सामग्री का दान',
    },
    'Cancer': {
        'hi': 'कर्क',
        'ruler_en': 'Moon',
        'ruler_hi': 'चंद्र',
        'element_en': 'Water',
        'element_hi': 'जल',
        'deity_en': 'Lord Shiva & Goddess Gauri',
        'deity_hi': 'भगवान शिव एवं माँ गौरी',
        'mantra_en': 'Om Shram Shreem Shroum Sah Chandraya Namah',
        'mantra_hi': 'ॐ श्रां श्रीं श्रौं सः चंद्राय नमः',
        'nature_en': 'deeply intuitive, emotionally empathetic, caring, and protective',
        'nature_hi': 'अत्यंत संवेदनशील, सहृदय, परिवार-उन्मुख और आत्मीय',
        'weakness_en': 'mood fluctuations, defensive withdrawal, and fear of abandonment',
        'weakness_hi': 'मनोदशा में तीव्र उतार-चढ़ाव, अति-संवेदनशीलता और अलगाव की प्रवृत्ति',
        'gemstone_default': 'Natural Pearl (Moti)',
        'gemstone_hi': 'प्राकृतिक मोती (Pearl)',
        'gemstone_finger': 'Little finger in silver on Monday evening or morning',
        'gemstone_finger_hi': 'सोमवार को चांदी की अंगूठी में कनिष्ठिका अंगुली में',
        'daan_item_en': 'Raw milk, white rice, silver coin, or drinking water facilities on Mondays',
        'daan_item_hi': 'सोमवार को कच्चा दूध, सफेद चावल, जल या चांदी का दान',
    },
    'Leo': {
        'hi': 'सिंह',
        'ruler_en': 'Sun',
        'ruler_hi': 'सूर्य',
        'element_en': 'Fire',
        'element_hi': 'अग्नि',
        'deity_en': 'Lord Surya & Lord Vishnu',
        'deity_hi': 'भगवान सूर्य नारायण एवं श्री हरि विष्णु',
        'mantra_en': 'Om Hram Hreem Hroum Sah Suryaya Namah',
        'mantra_hi': 'ॐ ह्रां ह्रीं ह्रौं सः सूर्याय नमः',
        'nature_en': 'magnanimous, charismatic, commanding, and principled',
        'nature_hi': 'तेजस्वी, स्वाभिमानी, नेतृत्वकुशल और उदार',
        'weakness_en': 'fragile pride, dominance, and vulnerability to flattery or disrespect',
        'weakness_hi': 'अहंकार, अत्यधिक प्रभुत्व की चाह और उपेक्षा सहन न कर पाना',
        'gemstone_default': 'Ruby (Manik)',
        'gemstone_hi': 'माणिक्य (Ruby)',
        'gemstone_finger': 'Ring finger in gold or copper on Sunday morning at sunrise',
        'gemstone_finger_hi': 'रविवार सूर्योदय पर अनामिका अंगुली में सोने या तांबे में',
        'daan_item_en': 'Wheat grains, jaggery, ruby-colored cloth, or copper vessels on Sundays',
        'daan_item_hi': 'रविवार को गेहूं, गुड़, तांबे के पात्र या लाल वस्त्र का दान',
    },
    'Virgo': {
        'hi': 'कन्या',
        'ruler_en': 'Mercury',
        'ruler_hi': 'बुध',
        'element_en': 'Earth',
        'element_hi': 'पृथ्वी',
        'deity_en': 'Lord Ganesha & Lord Vishnu',
        'deity_hi': 'श्री गणेश एवं भगवान लक्ष्मीनारायण',
        'mantra_en': 'Om Bram Breem Broum Sah Budhaya Namah',
        'mantra_hi': 'ॐ ब्रां ब्रीं ब्रौं सः बुधाय नमः',
        'nature_en': 'analytical, meticulous, service-oriented, and pragmatically wise',
        'nature_hi': 'विश्लेषणात्मक, कर्तव्यनिष्ठ, सूक्ष्मदर्शी और व्यवस्थित',
        'weakness_en': 'over-criticism, perfectionism paralysis, and somatic anxiety',
        'weakness_hi': 'अत्यधिक नुक्ताचीनी, पूर्णतावाद की चिंता और मानसिक तनाव',
        'gemstone_default': 'Emerald (Panna)',
        'gemstone_hi': 'पन्ना (Emerald)',
        'gemstone_finger': 'Little finger in gold or silver on Wednesday morning',
        'gemstone_finger_hi': 'बुधवार प्रातः कनिष्ठिका अंगुली में सोने अथवा चांदी में',
        'daan_item_en': 'Green vegetables, moong dal, or helping orphanages/schools on Wednesdays',
        'daan_item_hi': 'बुधवार को हरी सब्जियां, मूंग दाल या अनाथालय/विद्यालय में सहयोग',
    },
    'Libra': {
        'hi': 'तुला',
        'ruler_en': 'Venus',
        'ruler_hi': 'शुक्र',
        'element_en': 'Air',
        'element_hi': 'वायु',
        'deity_en': 'Goddess Mahalakshmi & Radha-Krishna',
        'deity_hi': 'माँ महालक्ष्मी एवं युगल सरकार राधा-कृष्ण',
        'mantra_en': 'Om Dram Dreem Droum Sah Shukraya Namah',
        'mantra_hi': 'ॐ द्रां द्रीं द्रौं सः शुक्राय नमः',
        'nature_en': 'diplomatic, harmonious, refined, and value-conscious',
        'nature_hi': 'न्यायप्रिय, सौम्य, शांतिदूत और कला-प्रेमी',
        'weakness_en': 'indecisiveness, codependency, and suppressing conflict until crisis',
        'weakness_hi': 'अनिर्णय, विवाद से बचने के लिए समझौता करना और असमंजस',
        'gemstone_default': 'Diamond or Opal',
        'gemstone_hi': 'ओपल (Opal) अथवा हीरा',
        'gemstone_finger': 'Middle or ring finger in silver on Friday morning',
        'gemstone_finger_hi': 'शुक्रवार प्रातः मध्यमा या अनामिका अंगुली में चांदी में',
        'daan_item_en': 'White clothes, curd, ghee, perfumes, or cosmetics on Fridays',
        'daan_item_hi': 'शुक्रवार को सफेद वस्त्र, दही, शुद्ध घी, इत्र या सौंदर्य प्रसाधनों का दान',
    },
    'Scorpio': {
        'hi': 'वृश्चिक',
        'ruler_en': 'Mars',
        'ruler_hi': 'मंगल',
        'element_en': 'Water',
        'element_hi': 'जल',
        'deity_en': 'Lord Shiva & Lord Hanuman',
        'deity_hi': 'भगवान शिव एवं श्री हनुमान जी',
        'mantra_en': 'Om Kram Kreem Kroum Sah Bhaumaya Namah',
        'mantra_hi': 'ॐ क्रां क्रीं क्रौं सः भौमाय नमः',
        'nature_en': 'profound, resolute, fiercely loyal, and transformative',
        'nature_hi': 'दृढ़निश्चयी, गंभीर, रहस्यमयी और अदम्य साहसी',
        'weakness_en': 'suspicion, emotional grudges, and uncompromising control tendencies',
        'weakness_hi': 'संदेह, पुरानी बातों को न भूलना और अत्यधिक नियंत्रण रखने की आदत',
        'gemstone_default': 'Red Coral (Moonga)',
        'gemstone_hi': 'त्रिकोण मूंगा (Red Coral)',
        'gemstone_finger': 'Ring finger in copper or gold on Tuesday morning',
        'gemstone_finger_hi': 'मंगलवार प्रातः अनामिका अंगुली में तांबे या सोने में',
        'daan_item_en': 'Red sweets (imarti/jalebi), blood donation, or donating to emergency relief on Tuesdays',
        'daan_item_hi': 'मंगलवार को लाल मिठाई का वितरण, रक्तदान या जरूरतमंदों को सहायता',
    },
    'Sagittarius': {
        'hi': 'धनु',
        'ruler_en': 'Jupiter',
        'ruler_hi': 'बृहस्पति',
        'element_en': 'Fire',
        'element_hi': 'अग्नि',
        'deity_en': 'Lord Brihaspati & Lord Dakshinamurthy',
        'deity_hi': 'देवगुरु बृहस्पति एवं दक्षिणामूर्ति शिव',
        'mantra_en': 'Om Gram Greem Groum Sah Gurave Namah',
        'mantra_hi': 'ॐ ग्रां ग्रीं ग्रौं सः गुरवे नमः',
        'nature_en': 'optimistic, truth-seeking, expansive, and morally upright',
        'nature_hi': 'आशावादी, सत्यवादी, जिज्ञासु और आध्यात्मिक',
        'weakness_en': 'bluntness, over-committing, and philosophical detachment from practical realities',
        'weakness_hi': 'मुँहफट बोलना, जरूरत से ज्यादा वादे करना और व्यावहारिक अनदेखी',
        'gemstone_default': 'Yellow Sapphire (Pukhraj)',
        'gemstone_hi': 'पुखराज (Yellow Sapphire)',
        'gemstone_finger': 'Index finger in gold or brass on Thursday morning',
        'gemstone_finger_hi': 'गुरुवार प्रातः तर्जनी अंगुली में सोने या पीतल में',
        'daan_item_en': 'Chana dal, turmeric roots, yellow saffron cloth, or books to teachers on Thursdays',
        'daan_item_hi': 'गुरुवार को चने की दाल, हल्दी की गांठें, पीले वस्त्र या धार्मिक पुस्तकों का दान',
    },
    'Capricorn': {
        'hi': 'मकर',
        'ruler_en': 'Saturn',
        'ruler_hi': 'शनि',
        'element_en': 'Earth',
        'element_hi': 'पृथ्वी',
        'deity_en': 'Lord Shani & Lord Shiva (Kaal Bhairav)',
        'deity_hi': 'शनिदेव एवं भगवान काल भैरव',
        'mantra_en': 'Om Pram Preem Proum Sah Shanaischaraya Namah',
        'mantra_hi': 'ॐ प्रां प्रीं प्रौं सः शनैश्चराय नमः',
        'nature_en': 'disciplined, tenacious, responsible, and architecturally patient',
        'nature_hi': 'अनुशासित, कर्मठ, गंभीर और दीर्घकालिक सोच रखने वाले',
        'weakness_en': 'pessimism, emotional rigidity, and bearing burdens without seeking assistance',
        'weakness_hi': 'निराशावाद, भावनात्मक रूखापन और अत्यधिक कार्यभार का बोझ सहना',
        'gemstone_default': 'Blue Sapphire (Neelam) or Amethyst',
        'gemstone_hi': 'नीलम (Blue Sapphire) अथवा जमुनिया (Amethyst)',
        'gemstone_finger': 'Middle finger in iron, silver, or panchdhatu on Saturday evening',
        'gemstone_finger_hi': 'शनिवार सायंकाल मध्यमा अंगुली में पंचधातु, लोहे अथवा चांदी में',
        'daan_item_en': 'Mustard oil, black sesame seeds, iron pans, or blankets to elderly laborers on Saturdays',
        'daan_item_hi': 'शनिवार को सरसों का तेल, काले तिल, लोहे का तवा या असहाय वृद्धजनों को कंबल का दान',
    },
    'Aquarius': {
        'hi': 'कुंभ',
        'ruler_en': 'Saturn',
        'ruler_hi': 'शनि',
        'element_en': 'Air',
        'element_hi': 'वायु',
        'deity_en': 'Lord Shiva & Rudra Dev',
        'deity_hi': 'भगवान शिव एवं एकादश रुद्र',
        'mantra_en': 'Om Sham Shanaischaraya Namah',
        'mantra_hi': 'ॐ शं शनैश्चराय नमः',
        'nature_en': 'visionary, humanitarian, intellectual, and radically free',
        'nature_hi': 'दूरदर्शी, परोपकारी, स्वतंत्रचेता और वैज्ञानिक सोच वाले',
        'weakness_en': 'aloofness, contrarian defensiveness, and emotional disconnection',
        'weakness_hi': 'भावनात्मक दूरी, समाज से अलगाव और विचारों में हठधर्मिता',
        'gemstone_default': 'Blue Sapphire (Neelam) or Iolite',
        'gemstone_hi': 'नीलम (Blue Sapphire) अथवा कटैला (Iolite)',
        'gemstone_finger': 'Middle finger in silver or white gold on Saturday evening',
        'gemstone_finger_hi': 'शनिवार सायंकाल मध्यमा अंगुली में चांदी अथवा पंचधातु में',
        'daan_item_en': 'Black shoes, umbrella, blue clothes, or service to disabled individuals on Saturdays',
        'daan_item_hi': 'शनिवार को काले जूते, छाता, नीले वस्त्र या दिव्यांगजनों की सेवा व सहायता',
    },
    'Pisces': {
        'hi': 'मीन',
        'ruler_en': 'Jupiter',
        'ruler_hi': 'बृहस्पति',
        'element_en': 'Water',
        'element_hi': 'जल',
        'deity_en': 'Lord Mahavishnu & Matsya Avatar',
        'deity_hi': 'भगवान श्री हरि विष्णु एवं मत्स्य अवतार',
        'mantra_en': 'Om Gram Greem Groum Sah Gurave Namah',
        'mantra_hi': 'ॐ ग्रां ग्रीं ग्रौं सः गुरवे नमः',
        'nature_en': 'deeply compassionate, spiritually attuned, imaginative, and soulful',
        'nature_hi': 'दयालु, आध्यात्मिक, परोपकारी और कल्पनाशील',
        'weakness_en': 'escapism, poor boundary setting, and taking on toxic emotional burdens',
        'weakness_hi': 'वास्तविकता से पलायन, कमजोर सीमाएं और दूसरों के तनाव को ओढ़ लेना',
        'gemstone_default': 'Yellow Sapphire (Pukhraj) or Golden Topaz',
        'gemstone_hi': 'पुखराज (Yellow Sapphire) अथवा सुनहला',
        'gemstone_finger': 'Index finger in gold or brass on Thursday morning',
        'gemstone_finger_hi': 'गुरुवार प्रातः तर्जनी अंगुली में सोने या पीतल में',
        'daan_item_en': 'Feeding fish, donating soaked chana dal, yellow sweets, or helping spiritual institutions on Thursdays',
        'daan_item_hi': 'गुरुवार को मछलियों को आटे की गोलियां खिलाना, चने की दाल या पीली मिठाई का दान',
    }
}

# Sign name mapping (supports English and Hindi lookup)
SIGN_MAP_HI_TO_EN = {v['hi']: k for k, v in ZODIAC_SIGNS.items()}
SIGN_MAP_EN_TO_HI = {k: v['hi'] for k, v in ZODIAC_SIGNS.items()}

# ==============================================================================
# 20 ISSUES: ASTROLOGICAL HOUSES, KARAKAS, AND SPECIFIC DOMAINS
# ==============================================================================

ISSUES = {
    'Business Loss or Slow Down': {
        'slug': 'business-loss-slow-down',
        'hi': 'व्यापार में हानि या मंदी',
        'houses': '7th (Trade & Partnerships), 10th (Commerce & Reputation), and 11th (Labha/Profits)',
        'houses_hi': 'सप्तम भाव (साझेदारी व व्यापार), दशम भाव (कर्म व साख) और एकादश भाव (लाभ भाव)',
        'karakas': 'Mercury (Trade & Calculation) and Jupiter (Financial Growth)',
        'karakas_hi': 'बुध (व्यापार व बुद्धि) और बृहस्पति (विस्तार व धन)',
        'core_conflict_en': 'imbalances in cash flow, delayed buyer receivables, or misjudged commercial ventures',
        'core_conflict_hi': 'रोकड़ प्रवाह में रुकावट, ग्राहकों से भुगतान में विलंब अथवा बाजार में मांग की कमी',
        'social_focus_en': 'restructure supplier contracts, enforce stringent cash reconciliation, and avoid impulsive capital expansion',
        'social_focus_hi': 'लेनदारों व आपूर्तिकर्ताओं से स्पष्ट संवाद रखें, अनावश्यक खर्च रोकें और हिसाब-किताब पारदर्शी बनाएं',
        'emotional_focus_en': 'release financial panic through structured scenario mapping and daily grounding breaths',
        'emotional_focus_hi': 'व्यापारिक तनाव से घबराने के बजाय शांत मन से वित्तीय प्राथमिकताओं की कार्ययोजना बनाएं',
        'practical_actions_en': [
            {"step": 1, "title": "Audit Operating Leaks", "action": "Reconcile past 90 days of cash inflows against fixed overheads and freeze discretionary capital expenditures.", "frequency": "Immediate / Weekly", "timing": "Weekend review"},
            {"step": 2, "title": "Energize Northeast Corner (Ishanya)", "action": "Clear clutter and water stagnancy from the northeast zone of your office or shop; place a clean bronze vessel of water.", "frequency": "Daily", "timing": "Morning before opening"},
            {"step": 3, "title": "Invoke Vyapar Vridhi & Lakshmi Chants", "action": "Chant the Mahalakshmi or Sri Suktam mantra 11 times at the commercial threshold.", "frequency": "Every evening at dusk", "timing": "Sandhya Kaal (Dusk)"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "व्यापारिक खर्चों का विश्लेषण करें", "action": "पिछले तीन महीनों के लेन-देन का ब्योरा जांचें और गैर-जरूरी खर्चों पर तुरंत रोक लगाएं।", "frequency": "तत्काल / साप्ताहिक", "timing": "सप्ताह के अंत में"},
            {"step": 2, "title": "ईशान कोण को स्वच्छ व ऊर्जावान बनाएं", "action": "दुकान या कार्यालय के उत्तर-पूर्व (ईशान) कोने को साफ-सुथरा रखें और वहां तांबे या पीतल के पात्र में जल स्थापित करें।", "frequency": "प्रतिदिन", "timing": "प्रतिष्ठान खोलने के तुरंत बाद"},
            {"step": 3, "title": "व्यापार वृद्धि व श्री सूक्त पाठ", "action": "संध्या समय कार्यस्थल पर धूप-दीप जलाकर कनकधारा स्तोत्र या श्री सूक्त का पाठ करें।", "frequency": "प्रतिदिन संध्याकाल", "timing": "गोधूलि वेला"}
        ],
        'faq_q1_en': "Why does this zodiac sign suffer business sluggishness during this period?",
        'faq_a1_en': "When transiting malefic planets aspect your 7th or 10th houses, decision fatigue sets in. Calibrating Mercury and Jupiter stabilizes commerce.",
        'faq_q1_hi': "इस राशि के जातकों को व्यापार में अचानक मंदी का सामना क्यों करना पड़ता है?",
        'faq_a1_hi': "जब गोचर या दशा में दशम या एकादश भाव पर पाप ग्रहों का प्रभाव होता है, तो व्यापारिक गति मंद हो जाती है। बुध व गुरु के उपाय व्यापार में पुनः तरक्की लाते हैं।"
    },
    'Children Issues': {
        'slug': 'children-issues',
        'hi': 'संतान संबंधी चिंताएं एवं कष्ट',
        'houses': '5th House (Putra/Santana Bhava - Progeny & Intellect)',
        'houses_hi': 'पंचम भाव (संतान व विद्या भाव) एवं नवम भाव (भाग्य भाव)',
        'karakas': 'Jupiter (Putrakaraka) and the Moon (Maternal Nurturing)',
        'karakas_hi': 'देवगुरु बृहस्पति (संतान कारक) एवं चंद्रमा (वात्सल्य कारक)',
        'core_conflict_en': 'generational miscommunication, health concerns of offspring, or delays in child conception/well-being',
        'core_conflict_hi': 'संतान के स्वास्थ्य में उतार-चढ़ाव, पढ़ाई में अरुचि अथवा वैचारिक मतभेद',
        'social_focus_en': 'replace authoritarian criticism with active listening sessions and non-coercive collaborative problem-solving',
        'social_focus_hi': 'संतान पर अनावश्यक दबाव बनाने के स्थान पर धैर्यपूर्वक उनकी बात सुनें और मित्रतापूर्ण व्यवहार रखें',
        'emotional_focus_en': 'cultivate parental patience and surrender attachment to rigid expectations',
        'emotional_focus_hi': 'अत्यधिक चिंता और अधीरता छोड़कर संतान के साथ आत्मीय व सकारात्मक वातावरण बनाएं',
        'practical_actions_en': [
            {"step": 1, "title": "Establish 30-Minute Dialogue Circle", "action": "Conduct uninterrupted, non-judgmental one-on-one conversation with your child daily.", "frequency": "Daily", "timing": "Post-dinner"},
            {"step": 2, "title": "Recite Santana Gopala Mantra", "action": "Chant 'Om Devaki Suda Govinda Vasudeva Jagatpate' 108 times for protection and harmony of offspring.", "frequency": "Daily", "timing": "Morning post-bath"},
            {"step": 3, "title": "Feed Sweet Rice to Birds & Stray Cows", "action": "Distribute boiled sweet yellow rice or wheat bread with jaggery to stray cows or birds.", "frequency": "Every Thursday", "timing": "Morning before 9 AM"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "प्रतिदिन 30 मिनट का आत्मीय संवाद", "action": "संतान के साथ बिना किसी टोक-टाक के दिनभर की बातें साझा करें और उनका मनोबल बढ़ाएं।", "frequency": "प्रतिदिन", "timing": "रात्रि भोजन के उपरांत"},
            {"step": 2, "title": "संतान गोपाल मंत्र का जाप", "action": "'ॐ देवकीसुत गोविन्द वासुदेव जगत्पते' मंत्र का 108 बार तुलसी की माला से जाप करें।", "frequency": "नित्य प्रातः", "timing": "स्नानोपरांत"},
            {"step": 3, "title": "गौमाता को गुड़ व मीठी रोटी खिलाएं", "action": "गुरुवार के दिन गाय को हल्दी लगी रोटी व गुड़ खिलाएं और संतान के उज्ज्वल भविष्य की प्रार्थना करें।", "frequency": "प्रत्येक गुरुवार", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "How does planetary afflictions affect my child's academic or emotional temper?",
        'faq_a1_en': "Afflictions to the 5th house or Jupiter often manifest as behavioral resistance or learning fatigue. Strengthening Jupiter nurtures composure.",
        'faq_q1_hi': "संतान के व्यवहार या पढ़ाई में आ रही रुकावटों का ज्योतिषीय कारण क्या है?",
        'faq_a1_hi': "कुंडली के पंचम भाव पर राहु, शनि या मंगल का दुष्प्रभाव संतान के मन को चंचल या विद्रोही बनाता है। गुरु व चंद्र के उपाय शांति प्रदान करते हैं।"
    },
    'Domestic Issues': {
        'slug': 'domestic-issues',
        'hi': 'गृह क्लेश व पारिवारिक अशांति',
        'houses': '4th House (Sukha Bhava - Domestic Sanctuary) and 2nd House (Family Harmony)',
        'houses_hi': 'चतुर्थ भाव (सुख भाव) एवं द्वितीय भाव (कुटुंब भाव)',
        'karakas': 'Moon (Peace of Mind) and Venus (Domestic Harmony)',
        'karakas_hi': 'चंद्रमा (मनःशांति) और शुक्र (पारिवारिक सौहार्द)',
        'core_conflict_en': 'household friction, boundary disputes with relatives, and lack of mental tranquility at home',
        'core_conflict_hi': 'घर के सदस्यों में अकारण वाद-विवाद, मनमुटाव और गृह शांति का अभाव',
        'social_focus_en': 'call a neutral family meeting, establish shared domestic responsibilities, and avoid discussing disputes in dining areas',
        'social_focus_hi': 'भोजन के समय बहस से बचें, एक-दूसरे के दृष्टिकोण का सम्मान करें और कटु शब्दों का प्रयोग न करें',
        'emotional_focus_en': 'clear emotional residues through evening silence periods and refusing to take provocative family banter personally',
        'emotional_focus_hi': 'क्रोध पर नियंत्रण रखें और किसी भी सदस्य की कड़वी बात पर तुरंत प्रतिक्रिया देने से बचें',
        'practical_actions_en': [
            {"step": 1, "title": "Rock Salt Mopping Ritual", "action": "Mop home floors with natural sea salt or rock salt dissolved in water to absorb stagnant household negativity.", "frequency": "Tuesday and Saturday", "timing": "Morning"},
            {"step": 2, "title": "Burn Camphor & Cloves at Twilight", "action": "Burn two camphor tablets with two cloves in the living area to balance the domestic energetic vibration.", "frequency": "Daily", "timing": "Dusk (Sandhya)"},
            {"step": 3, "title": "Establish Silent Dining Sanctuary", "action": "Strictly prohibit electronic devices and heated debate during the main family meal.", "frequency": "Daily", "timing": "Dinner"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "सेंधा नमक के पानी से पोछा लगाएं", "action": "घर की नकारात्मक ऊर्जा को दूर करने के लिए पानी में थोड़ा सेंधा नमक मिलाकर पोछा लगाएं।", "frequency": "मंगलवार व शनिवार", "timing": "प्रातः काल"},
            {"step": 2, "title": "संध्या समय कपूर व लौंग जलाएं", "action": "प्रतिदिन शाम को घर के मुख्य कक्ष में देसी कपूर और दो लौंग जलाकर पूरे घर में घुमाएं।", "frequency": "प्रतिदिन", "timing": "संध्या समय"},
            {"step": 3, "title": "भोजन के समय शांत वातावरण रखें", "action": "भोजन करते समय फोन दूर रखें और किसी भी विवादित विषय पर चर्चा न करें।", "frequency": "नित्य", "timing": "दोपहर व रात्रि भोजन"}
        ],
        'faq_q1_en': "Which planetary dosha triggers recurring domestic arguments?",
        'faq_a1_en': "An afflicted 4th house or Mars-Moon confrontation frequently fuels domestic arguments. Soothing the 4th lord and Moon restores calmness.",
        'faq_q1_hi': "घर में बिना बात के क्लेश होने का मुख्य ज्योतिषीय कारण क्या है?",
        'faq_a1_hi': "कुंडली का चतुर्थ भाव सुख का कारक है। जब इस भाव पर क्रूर ग्रहों का प्रभाव होता है या चंद्र पीड़ित होता है, तो गृह क्लेश बढ़ता है।"
    },
    'Evil Eye': {
        'slug': 'evil-eye',
        'hi': 'बुरी नज़र व नकारात्मक ऊर्जा',
        'houses': '8th House (Randhra Bhava - Hidden Forces) and 1st House (Aura & Vitality)',
        'houses_hi': 'अष्टम भाव (गुप्त बाधाएं) एवं लग्न भाव (आभामंडल व जीवन शक्ति)',
        'karakas': 'Rahu, Ketu, and Mars',
        'karakas_hi': 'राहु, केतु और मंगल',
        'core_conflict_en': 'sudden inexplicable fatigue, repeated minor mishaps, and heavy toxic vibrations',
        'core_conflict_hi': 'अचानक बनते कार्यों का बिगड़ना, अकारण भारीपन, घबराहट और नकारात्मक ऊर्जा',
        'social_focus_en': 'refrain from broadcasting uncompleted personal wins, milestone intentions, or private financial details on social media',
        'social_focus_hi': 'अपने नए काम, योजनाएं या व्यक्तिगत खुशियां तब तक सार्वजनिक न करें जब तक वे पूरी न हो जाएं',
        'emotional_focus_en': 'ground yourself in protective spiritual awareness, disarming anxiety through fearless trust in the Divine',
        'emotional_focus_hi': 'भय और शंका को मन से निकालकर हनुमान जी के स्मरण से अपने आत्मविश्वास को सुदृढ़ रखें',
        'practical_actions_en': [
            {"step": 1, "title": "Hanuman Chalisa recitation", "action": "Recite the Hanuman Chalisa twice with pure ghee lamp burning.", "frequency": "Daily", "timing": "Sunrise and Sunset"},
            {"step": 2, "title": "Black Mustard & Red Chilli Ousting", "action": "Rotate 7 dried red chillies and a pinch of black mustard seeds 7 times anti-clockwise around yourself and burn safely outside.", "frequency": "Tuesday / Saturday", "timing": "After 7 PM"},
            {"step": 3, "title": "Tie Panchmukhi Hanuman Raksha Sutra", "action": "Wear a consecrated black or red holy thread blessed at a Hanuman temple on your right wrist.", "frequency": "Once a month renewal", "timing": "Tuesday morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "नित्य हनुमान चालीसा का पाठ करें", "action": "घर के मंदिर में चमेली के तेल या घी का दीपक जलाकर श्रद्धापूर्वक हनुमान चालीसा का पाठ करें।", "frequency": "प्रतिदिन", "timing": "प्रातः व सायं"},
            {"step": 2, "title": "राई व सूखी लाल मिर्च से नज़र उतारें", "action": "7 सूखी लाल मिर्च और थोड़े से काले सरसों के दाने सिर से 7 बार उतारकर जलते अंगारों पर डालें।", "frequency": "मंगलवार या शनिवार", "timing": "सूर्यास्त के बाद"},
            {"step": 3, "title": "हनुमान मंदिर का सिंदूर लगाएं", "action": "मंदिर जाकर हनुमान जी के चरणों का सिंदूर अपने माथे पर लगाएं और रक्षा कवच का स्मरण करें।", "frequency": "नित्य / मंगलवार", "timing": "पूजा के समय"}
        ],
        'faq_q1_en': "How does Vedic astrology identify an evil eye affliction?",
        'faq_a1_en': "Weak ascendant lords accompanied by afflicted Rahu/8th house dynamics leave one vulnerable to psychic jealousy and sudden vitality drain.",
        'faq_q1_hi': "ज्योतिष के अनुसार बुरी नज़र का प्रभाव सबसे अधिक कब पड़ता है?",
        'faq_a1_hi': "जब व्यक्ति का लग्न या चंद्रमा निर्बल होता है और राहु का प्रभाव बढ़ता है, तब नकारात्मक ऊर्जा और बुरी नज़र का असर जल्दी होता है।"
    },
    'Friendship Issues': {
        'slug': 'friendship-issues',
        'hi': 'मित्रता में मतभेद व विश्वासघात',
        'houses': '11th House (Labha & Mitra Bhava - Social Networks & Allies)',
        'houses_hi': 'एकादश भाव (मित्र व सामाजिक संपर्क भाव)',
        'karakas': 'Mercury (Companionship) and Venus (Warmth & Social Grace)',
        'karakas_hi': 'बुध (संवाद व बौद्धिक तालमेल) और शुक्र (मित्रता व सौहार्द)',
        'core_conflict_en': 'misunderstood text messages, betrayal of secrets, and one-sided exploitation in social circles',
        'core_conflict_hi': 'मित्रों के साथ गलतफहमी, विश्वासघात की भावना या परस्पर सम्मान में कमी',
        'social_focus_en': 'stop over-investing emotionally in unreciprocated alliances; set clear, polite personal boundaries',
        'social_focus_hi': 'उन लोगों से दूरी बनाएं जो केवल स्वार्थ के लिए मिलते हैं; अपनी प्राथमिकताओं को स्पष्ट रखें',
        'emotional_focus_en': 'discern between real friends and transactional acquaintances without growing bitter',
        'emotional_focus_hi': 'हर किसी पर आंख मूंदकर भरोसा न करें और मित्रता टूटने पर स्वयं को दोषी न मानें',
        'practical_actions_en': [
            {"step": 1, "title": "Social Circle Audit", "action": "Classify peer relationships into inner-confidants versus peripheral acquaintances; limit vulnerable sharing to inner confidants.", "frequency": "Monthly", "timing": "Weekend"},
            {"step": 2, "title": "Water Peepal Tree with Water & Sugar", "action": "Offer clean water with a pinch of sugar or milk to the roots of a Peepal tree to appease Jupiter and Rahu.", "frequency": "Every Saturday", "timing": "Morning"},
            {"step": 3, "title": "Chant Budha Beej Mantra", "action": "Chant 'Om Bram Breem Broum Sah Budhaya Namah' 108 times to clarify social discernment.", "frequency": "Daily", "timing": "Morning post-bath"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "सच्चे और स्वार्थी मित्रों में अंतर समझें", "action": "अपनी व्यक्तिगत व गोपनीय बातें केवल भरोसेमंद लोगों से ही साझा करें; सोशल मीडिया पर सीमाएं तय करें।", "frequency": "मासिक समीक्षा", "timing": "किसी भी दिन"},
            {"step": 2, "title": "पीपल के वृक्ष पर मीठा जल चढ़ाएं", "action": "शनिवार की सुबह पीपल की जड़ में थोड़ा कच्चा दूध या मीठा जल अर्पित करें और परिक्रमा करें।", "frequency": "प्रत्येक शनिवार", "timing": "प्रातः काल"},
            {"step": 3, "title": "बुध बीज मंत्र का जाप करें", "action": "'ॐ ब्रां ब्रीं ब्रौं सः बुधाय नमः' का 108 बार जाप करें ताकि वाणी में स्पष्टता और निर्णय शक्ति बढ़े।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Why do my closest friends turn hostile or distant?",
        'faq_a1_en': "Transits through the 11th house or debility in Mercury often trigger sudden communicative misunderstandings in peer groups.",
        'faq_q1_hi': "मित्रों के साथ बार-बार रिश्ते खराब होने का क्या कारण होता है?",
        'faq_a1_hi': "कुंडली के एकादश भाव पर शनि या राहु का प्रभाव होने पर मित्रता में गलतफहमियां या विश्वासघात की स्थिति उत्पन्न होती है।"
    },
    'Health': {
        'slug': 'health',
        'hi': 'स्वास्थ्य समस्याएं व शारीरिक दुर्बलता',
        'houses': '1st House (Vitality/Tanu), 6th House (Roga/Illness), and 8th House (Longevity/Ayush)',
        'houses_hi': 'प्रथम भाव (आरोग्य व काया), षष्ठ भाव (रोग व व्याधि) और अष्टम भाव (आयु व संकट)',
        'karakas': 'Sun (Life Force/Prana) and Mars (Blood & Muscle Strength)',
        'karakas_hi': 'सूर्य देव (प्राण शक्ति व अस्थि) और मंगल देव (रक्त व ऊर्जा)',
        'core_conflict_en': 'chronic exhaustion, recurrent seasonal infections, digestive sluggishness, or metabolic imbalances',
        'core_conflict_hi': 'शरीर में सुस्ती, बार-बार मौसमी बीमारियां, पाचन तंत्र की खराबी या पुराना दर्द',
        'social_focus_en': 'schedule timely professional medical check-ups, follow certified healthcare advice, and eliminate erratic meal habits',
        'social_focus_hi': 'चिकित्सकीय परामर्श को प्राथमिकता दें, संतुलित आहार लें और समय पर सोने-जागने का नियम बनाएं',
        'emotional_focus_en': 'treat the physical body with reverence, cultivating disciplined recovery rhythms instead of stubborn pushing',
        'emotional_focus_hi': 'बीमारी के भय से मन को कमजोर न होने दें, सकारात्मक सोच और ध्यान से रोग प्रतिरोधक क्षमता बढ़ाएं',
        'practical_actions_en': [
            {"step": 1, "title": "Surya Arghya with Copper Vessel", "action": "Offer fresh water mixed with red sandalwood powder and akshat to the rising Sun.", "frequency": "Daily", "timing": "Within 1 hour of sunrise"},
            {"step": 2, "title": "Maha Mrityunjaya Japa", "action": "Chant the Maha Mrityunjaya mantra 108 times facing north for cellular revitalization and protection.", "frequency": "Daily", "timing": "Morning on empty stomach"},
            {"step": 3, "title": "Consume Warm Water with Basil", "action": "Drink warm water infused with 5 washed Tulsi (holy basil) leaves upon waking.", "frequency": "Daily", "timing": "Early morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "सूर्य देव को तांबे के लोटे से अर्घ्य दें", "action": "प्रातः तांबे के लोटे में जल, थोड़ा रोली और अक्षत डालकर उगते सूर्य को अर्घ्य दें।", "frequency": "प्रतिदिन", "timing": "सूर्योदय के समय"},
            {"step": 2, "title": "महामृत्युंजय मंत्र का नित्य जाप", "action": "'ॐ त्र्यम्बकं यजामहे सुगन्धिं पुष्टिवर्धनम्...' मंत्र का 108 बार जाप करें।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"},
            {"step": 3, "title": "तुलसी पत्र का सेवन करें", "action": "नित्य सुबह खाली पेट 5 स्वच्छ तुलसी के पत्तों का सेवन गुनगुने पानी के साथ करें।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Can gemstone or astrological remedies replace clinical therapy for health?",
        'faq_a1_en': "Astrological remedies and gemstones enhance planetary vitality and mental resilience; they must always complement, never replace, qualified medical treatment.",
        'faq_q1_hi': "क्या ज्योतिषीय उपाय डॉक्टर के इलाज का विकल्प हो सकते हैं?",
        'faq_a1_hi': "ज्योतिषीय उपाय शरीर की आंतरिक ऊर्जा और मनोबल को मजबूत करते हैं; उचित चिकित्सकीय उपचार व दवाइयों के साथ ही इन्हें अपनाना चाहिए।"
    },
    'Home Buying': {
        'slug': 'home-buying',
        'hi': 'मकान व भूमि क्रय में बाधा',
        'houses': '4th House (Matru/Griha Bhava - Real Estate, Property & Vehicles)',
        'houses_hi': 'चतुर्थ भाव (गृह व भूमि भाव) एवं द्वितीय भाव (धन संचय)',
        'karakas': 'Mars (Bhumikaraka - Land Lord) and Saturn (Structure/Construction)',
        'karakas_hi': 'मंगल देव (भूमि कारक) और शनि देव (भवन निर्माण व स्थायित्व)',
        'core_conflict_en': 'stalled registry paperwork, lender loan approvals falling through, or disputes regarding title deeds',
        'core_conflict_hi': 'मनपसंद मकान न मिलना, बैंक लोन में रुकावटें आना या कागजी कार्रवाई में अनावश्यक देरी',
        'social_focus_en': 'engage legal document vetting before signing deeds, review building permits, and ensure non-emotional financial scrutiny',
        'social_focus_hi': 'संपत्ति के कानूनी दस्तावेजों की वकील से जांच कराएं और जल्दबाजी में कोई भी बड़ा बयाना न दें',
        'emotional_focus_en': 'release urgent desperation; trust that the right dwelling manifests when astrological timing aligns',
        'emotional_focus_hi': 'मकान मिलने में हो रही देरी से निराश न हों, धैर्य रखें और सही समय का इंतजार करें',
        'practical_actions_en': [
            {"step": 1, "title": "Invoke Bhumi Suktam / Mangal Stotra", "action": "Chant the Dharani Garbha Sambhutam Mars Stotra 21 times to appease Bhumikaraka Mars.", "frequency": "Every Tuesday", "timing": "Morning"},
            {"step": 2, "title": "Offer Jaggery and Bread to Red Ants / Fish", "action": "Disperse wheat flour mixed with jaggery near ant hills or feed dough balls to river fish.", "frequency": "Tuesday & Saturday", "timing": "Afternoon"},
            {"step": 3, "title": "Install Vastu Mangal Yantra", "action": "Consecrate a copper Mangal Yantra in the southern or eastern zone of your current residence.", "frequency": "Once on an auspicious Tuesday", "timing": "Shukla Paksha Tuesday"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "मंगल स्तोत्र का पाठ करें", "action": "मंगलवार को 'धरणीगर्भसंभूतं...' मंगल स्तोत्र का 21 बार पाठ करें ताकि भूमि कारक मंगल प्रसन्न हों।", "frequency": "प्रत्येक मंगलवार", "timing": "प्रातः काल"},
            {"step": 2, "title": "चींटियों को आटा व गुड़ डालें", "action": "सूखे आटे में थोड़ा सा पिसा हुआ गुड़ मिलाकर चींटियों के बिल के पास डालें।", "frequency": "मंगलवार व शनिवार", "timing": "दोपहर या सायं"},
            {"step": 3, "title": "घर में वास्तु दोष निवारण पूजा", "action": "अपने वर्तमान आवास में उत्तर-पूर्व दिशा को स्वच्छ रखें और सुंदरकांड का पाठ करें।", "frequency": "मासिक", "timing": "शुभ मुहूर्त में"}
        ],
        'faq_q1_en': "Why are my property negotiations collapsing at the final registration stage?",
        'faq_a1_en': "Afflictions between the 4th house and retrograde Mars or Saturn often create procedural gridlock right before title signing. Appeasing Mars smooths obstacles.",
        'faq_q1_hi': "मकान की बात पक्की होते-होते अचानक क्यों टूट जाती है?",
        'faq_a1_hi': "कुंडली में चतुर्थेश या मंगल के कमजोर होने अथवा राहु के प्रभाव के कारण प्रॉपर्टी के सौदे अंतिम समय में अटक जाते हैं।"
    },
    'Job Promotion': {
        'slug': 'job-promotion',
        'hi': 'नौकरी में पदोन्नति व तरक्की में रुकावट',
        'houses': '10th House (Karma Bhava - Executive Rank) and 11th House (Elevation & Recognition)',
        'houses_hi': 'दशम भाव (कर्म व पद भाव) एवं एकादश भाव (वृद्धि व पुरस्कार भाव)',
        'karakas': 'Sun (Authority & Executive Favor) and Saturn (Long-term Karma & Diligence)',
        'karakas_hi': 'सूर्य देव (अधिकार व यश) और शनि देव (कर्म व न्याय)',
        'core_conflict_en': 'being bypassed for deserved elevation, unsupportive line managers, or political credit theft',
        'core_conflict_hi': 'कड़ी मेहनत के बाद भी प्रमोशन न मिलना, उच्चाधिकारियों की नाराजगी या श्रेय किसी और को मिलना',
        'social_focus_en': 'document measurable project achievements, maintain cordial executive relations, and avoid petty corporate politics',
        'social_focus_hi': 'अपने काम का रिकॉर्ड लिखित में रखें, अधिकारियों से पेशेवर संवाद रखें और ऑफिस की राजनीति से दूर रहें',
        'emotional_focus_en': 'stay steadfast in self-worth rather than seeking external validation from toxic corporate hierarchies',
        'emotional_focus_hi': 'हतोत्साहित होने के बजाय अपने कौशल को निखारें और आत्मविश्वास बनाए रखें',
        'practical_actions_en': [
            {"step": 1, "title": "Aditya Hridaya Stotra Recitation", "action": "Recite the sacred Aditya Hridaya Stotra facing east with dedicated mental focus.", "frequency": "Every Sunday morning", "timing": "Sunrise"},
            {"step": 2, "title": "Feed Soaked Gram Dal to Temple Cows", "action": "Offer soaked yellow chana dal mixed with turmeric to a cow to invoke Jupiterian benevolence.", "frequency": "Every Thursday", "timing": "Morning before work"},
            {"step": 3, "title": "Maintain Clean North-North-East Desk Workspace", "action": "Keep your workplace table strictly free from discarded papers and broken pens.", "frequency": "Daily", "timing": "Morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "आदित्य हृदय स्तोत्र का पाठ करें", "action": "रविवार की सुबह स्नान के बाद पूर्व दिशा की ओर मुख करके आदित्य हृदय स्तोत्र का पाठ करें।", "frequency": "प्रत्येक रविवार", "timing": "सूर्योदय के समय"},
            {"step": 2, "title": "गुरुवार को चने की दाल व गुड़ खिलाएं", "action": "गुरुवार को गाय को भीगी हुई चने की दाल और थोड़ा सा गुड़ खिलाकर चरण स्पर्श करें।", "frequency": "प्रत्येक गुरुवार", "timing": "प्रातः काल"},
            {"step": 3, "title": "कार्यस्थल पर सकारात्मक ऊर्जा रखें", "action": "अपनी टेबल पर अनावश्यक कागजात न रखें और उत्तर दिशा में एक छोटा सा हरा पौधा लगाएं।", "frequency": "नित्य", "timing": "काम शुरू करते समय"}
        ],
        'faq_q1_en': "Why does a colleague with less experience receive the promotion over me?",
        'faq_a1_en': "A weakened Sun in your transit chart diminishes your ability to command executive visibility. Strengthening the Sun elevates professional standing.",
        'faq_q1_hi': "काबिलियत होने के बावजूद पदोन्नति में बार-बार बाधा क्यों आती है?",
        'faq_a1_hi': "दशम भाव पर जब सूर्य या शनि की प्रतिकूल दृष्टि होती है, तो वरिष्ठ अधिकारियों से मतभेद या अनदेखी का सामना करना पड़ता है।"
    },
    'Job Search': {
        'slug': 'job-search',
        'hi': 'नई नौकरी व रोजगार प्राप्ति में विलंब',
        'houses': '6th House (Service & Competitive Exams) and 10th House (Profession & Calling)',
        'houses_hi': 'षष्ठ भाव (सेवा व प्रतियोगिता) एवं दशम भाव (आजीविका भाव)',
        'karakas': 'Saturn (Service/Employment Karaka) and Mercury (Interviews & Resumes)',
        'karakas_hi': 'शनि देव (रोजगार कारक) और बुध देव (साक्षात्कार व संवाद)',
        'core_conflict_en': 'unresponsive recruiters, endless interview rounds without final offers, and career stagnation',
        'core_conflict_hi': 'रिज्यूम भेजने पर भी कॉल न आना, इंटरव्यू में अंतिम दौर में छंट जाना या मनचाहा वेतन न मिलना',
        'social_focus_en': 'tailor resume keywords, follow up professionally after interviews, and network proactively with alumni',
        'social_focus_hi': 'बायोडाटा को आधुनिक मानकों के अनुसार अपडेट करें, नए संपर्कों से जुड़ें और आत्मविश्वास से इंटरव्यू दें',
        'emotional_focus_en': 'treat employment rejections as cosmic re-direction rather than personal failure',
        'emotional_focus_hi': 'अस्वीकृति से निराश न हों, प्रतिदिन नया सीखने और खुद को तैयार करने पर ध्यान दें',
        'practical_actions_en': [
            {"step": 1, "title": "Feed Bread with Mustard Oil to Black Dogs", "action": "Serve plain whole wheat rotis lightly smeared with mustard oil to stray dogs.", "frequency": "Every Saturday", "timing": "Late afternoon"},
            {"step": 2, "title": "Light Mustard Oil Lamp under Peepal", "action": "Light a deepak with mustard oil under a Peepal tree in the evening.", "frequency": "Every Saturday", "timing": "Sunset"},
            {"step": 3, "title": "Chant Ganesha Sankata Nashana Stotra", "action": "Recite the Ganesha Sankata Nashana Stotra before submitting applications or attending interviews.", "frequency": "Daily", "timing": "Morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "काले कुत्ते को तेल लगी रोटी खिलाएं", "action": "शनिवार के दिन सरसों के तेल से चुपड़ी हुई सादी रोटी किसी जरूरतमंद कुत्ते को खिलाएं।", "frequency": "प्रत्येक शनिवार", "timing": "सायंकाल"},
            {"step": 2, "title": "पीपल वृक्ष के नीचे सरसों के तेल का दीपक", "action": "शनिवार शाम को पीपल के पेड़ के नीचे सरसों के तेल का चौमुखा दीपक जलाएं।", "frequency": "प्रत्येक शनिवार", "timing": "सूर्यास्त के समय"},
            {"step": 3, "title": "संकटनाशन गणेश स्तोत्र का पाठ", "action": "इंटरव्यू देने जाने से पहले भगवान गणेश का ध्यान करें और संकटनाशन गणेश स्तोत्र पढ़ें।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Which mantra is most potent for securing interview breakthroughs?",
        'faq_a1_en': "Combining the Ganesha Sankat Nashak Stotra with daily Saturn Beej Mantra chanting eliminates obstacles in the recruitment pipeline.",
        'faq_q1_hi': "जॉब इंटरव्यू में सफलता पाने के लिए सबसे प्रभावशाली उपाय क्या है?",
        'faq_a1_hi': "गणेश जी को दूर्वा अर्पित करना और शनिवार को शनिदेव की आराधना करने से इंटरव्यू में आ रही अड़चनें समाप्त होती हैं।"
    },
    'Law Cases': {
        'slug': 'law-cases',
        'hi': 'कोर्ट-कचहरी व कानूनी विवाद',
        'houses': '6th House (Shatru/Rina/Litigation) and 8th House (Courtroom Surprises)',
        'houses_hi': 'षष्ठ भाव (शत्रु व मुकदमा) एवं अष्टम भाव (अप्रत्यक्षित संकट)',
        'karakas': 'Mars (Combat/Dispute) and Jupiter (Justice, Law & Dharma)',
        'karakas_hi': 'मंगल देव (विवाद व पक्ष) और देवगुरु बृहस्पति (न्याय व कानून)',
        'core_conflict_en': 'stalled litigation hearings, hostile opposing counsel, and mounting legal fees',
        'core_conflict_hi': 'मुकदमे की तारीखों का खिंचना, झूठे आरोप या कानूनी खर्चे बढ़ना',
        'social_focus_en': 'seek mediation options where viable, organize documented evidentiary archives, and follow qualified legal counsel',
        'social_focus_hi': 'संभव हो तो आपसी सुलह का प्रयास करें, सभी कागजी सबूत व्यवस्थित रखें और वकील की सलाह पर चलें',
        'emotional_focus_en': 'remain emotionally detached from adversary provocations; truth triumphs through steady composure',
        'emotional_focus_hi': 'क्रोध व आवेश में आकर कोई गलत कदम न उठाएं, संयम रखें और सत्य पर अडिग रहें',
        'practical_actions_en': [
            {"step": 1, "title": "Recite Baglamukhi Mantra", "action": "Chant the Baglamukhi Beej Mantra 'Om Hleem Bagalamukhi Sarvadushtanam Vacham Mukham Padam Stambhaya' with turmeric bead mala.", "frequency": "Daily", "timing": "Night after 9 PM"},
            {"step": 2, "title": "Offer Sindoor & Chola to Lord Hanuman", "action": "Perform a sacred chola offering of vermilion mixed with jasmine oil at a Hanuman temple.", "frequency": "Tuesday", "timing": "Morning"},
            {"step": 3, "title": "Feed Black Sesame to Crows", "action": "Scatter cooked rice mixed with black sesame and curd to crows in an open field.", "frequency": "Saturday", "timing": "Morning before eating"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "माँ बगलामुखी मंत्र का जप", "action": "हल्दी की माला से 'ॐ ह्लीं बगलामुखि सर्वदुष्टानां वाचं मुखं पदं स्तम्भय...' मंत्र का जाप करें।", "frequency": "नित्य सायं", "timing": "रात्रि 9 बजे के बाद"},
            {"step": 2, "title": "हनुमान जी को चोला चढ़ाएं", "action": "मंगलवार को हनुमान जी के मंदिर में चमेली के तेल में सिंदूर मिलाकर चोला चढ़ाएं और चमेली के तेल का दीपक जलाएं।", "frequency": "मंगलवार", "timing": "प्रातः या सायं"},
            {"step": 3, "title": "कौवों को काले तिल मिश्रित चावल खिलाएं", "action": "शनिवार की सुबह उबले हुए चावल में थोड़े काले तिल और दही मिलाकर कौवों को डालें।", "frequency": "प्रत्येक शनिवार", "timing": "भोजन से पूर्व"}
        ],
        'faq_q1_en': "Can astrological remedies guarantee a favorable courtroom verdict?",
        'faq_a1_en': "Remedies appease malefic 6th house influences and sharpen legal lucidity, but outcomes always depend on factual merits and competent representation.",
        'faq_q1_hi': "क्या कानूनी मामलों में ज्योतिषीय उपाय जीत दिला सकते हैं?",
        'faq_a1_hi': "ज्योतिषीय उपाय षष्ठ भाव के शत्रुओं को शांत करते हैं और न्यायप्रिय ग्रहों का बल बढ़ाते हैं, जिससे सत्य की विजय में सहायता मिलती है।"
    },
    'Loans': {
        'slug': 'loans',
        'hi': 'कर्ज व आर्थिक देनदारी की समस्या',
        'houses': '6th House (Rina Bhava - Debts) and 12th House (Vyaya - Heavy Outflows)',
        'houses_hi': 'षष्ठ भाव (ऋण भाव) एवं द्वादश भाव (अनावश्यक व्यय भाव)',
        'karakas': 'Mars (Debt Pressure) and Saturn (Persistent Liabilities)',
        'karakas_hi': 'मंगल देव (ऋण भार) और शनि देव (दीर्घकालिक देनदारी)',
        'core_conflict_en': 'mounting compound interest, overdue repayment notices, and entrapment in secondary borrowing',
        'core_conflict_hi': 'कर्ज का बोझ लगातार बढ़ना, ब्याज चुकाने में असमर्थता और एक कर्ज चुकाने के लिए दूसरा कर्ज लेना',
        'social_focus_en': 'negotiate restructuring or tenure extension with creditors, freeze non-essential expenses, and prioritize high-interest debt payoffs',
        'social_focus_hi': 'ऋणदाताओं से बात कर किस्त पुनर्निर्धारित कराएं, नए कर्ज लेने से बचें और खर्चों में कटौती करें',
        'emotional_focus_en': 'break the cycle of shame and anxiety by taking objective responsibility and celebrating small repayment milestones',
        'emotional_focus_hi': 'कर्ज के तनाव से घबराने के बजाय व्यवस्थित बचत और खर्चों पर नियंत्रण का संकल्प लें',
        'practical_actions_en': [
            {"step": 1, "title": "Rina Mochana Mangala Stotra Chanting", "action": "Recite the Rina Mochana Mangala Stotra with full devotion.", "frequency": "Every Tuesday", "timing": "Morning post-bath"},
            {"step": 2, "title": "Repay Initial Installments on Tuesdays", "action": "Whenever remitting a loan installment or partial repayment, do so on a Tuesday to break debt cycles.", "frequency": "Monthly repayment day", "timing": "Before 2 PM"},
            {"step": 3, "title": "Feed Sweet Wheat Dough to Ants", "action": "Prepare sweet dough balls of roasted flour and jaggery and offer them to ant colonies.", "frequency": "Daily or Saturdays", "timing": "Morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "ऋणमोचक मंगल स्तोत्र का नित्य पाठ", "action": "मंगलवार से प्रारंभ कर प्रतिदिन ऋणमोचक मंगल स्तोत्र का पाठ करें ताकि कर्ज मुक्ति का मार्ग खुले।", "frequency": "नित्य", "timing": "प्रातः काल"},
            {"step": 2, "title": "कर्ज की किस्त मंगलवार को चुकाएं", "action": "जब भी ऋण की कोई किस्त या आंशिक राशि चुकानी हो, तो उसे मंगलवार के दिन दें।", "frequency": "किस्त भुगतान दिवस", "timing": "दोपहर 2 बजे से पूर्व"},
            {"step": 3, "title": "चींटियों को भुने आटे में शक्कर मिलाकर खिलाएं", "action": "सूखे भुने हुए आटे में थोड़ा बूरा मिलाकर चींटियों के बिलों के आसपास डालें।", "frequency": "प्रतिदिन या शनिवार", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Why does taking a single loan trigger an endless chain of new debts?",
        'faq_a1_en': "When the 6th house is energized by malefic Mars transits without benefic Jupiterian oversight, financial liabilities cascade unless karmically appeased.",
        'faq_q1_hi': "एक कर्ज खत्म होते ही दूसरा कर्ज क्यों चढ़ जाता है?",
        'faq_a1_hi': "कुंडली के षष्ठ भाव या मंगल-राहु के दुष्प्रभाव से ऋण का चक्रव्यूह बनता है। ऋणमोचक उपाय करने से यह चक्र टूटता है।"
    },
    'Love': {
        'slug': 'love',
        'hi': 'प्रेम संबंध व आकर्षण में तनाव',
        'houses': '5th House (Romance, Passion & Connection) and 7th House (Partnership Intimacy)',
        'houses_hi': 'पंचम भाव (प्रेम, अनुराग व आकर्षण) एवं सप्तम भाव (साझेदारी)',
        'karakas': 'Venus (Romance & Sensuality) and Moon (Emotional Affinity)',
        'karakas_hi': 'शुक्र देव (प्रेम व आकर्षण कारक) और चंद्रमा (भावनात्मक जुड़ाव)',
        'core_conflict_en': 'emotional misunderstandings, ego friction between lovers, or fear of emotional rejection',
        'core_conflict_hi': 'प्रेम संबंधों में अविश्वास, बिना बात की बहस, संवादहीनता या आकर्षण में कमी',
        'social_focus_en': 'practice open emotional vulnerability, abandon scorekeeping, and respect your partner\'s individual autonomy',
        'social_focus_hi': 'अपने साथी की भावनाओं का आदर करें, बातचीत में अहंकार न लाएं और पुराने गिले-शिकवे बार-बार न दोहराएं',
        'emotional_focus_en': 'cultivate deep inner wholeness instead of demanding that a romantic partner compensate for internal voids',
        'emotional_focus_hi': 'अत्यधिक अधिकार भावना और ईर्ष्या से बचें; प्रेम में स्वतंत्रता और विश्वास का भाव रखें',
        'practical_actions_en': [
            {"step": 1, "title": "Offer White Fragrant Flowers to Radha-Krishna", "action": "Place fresh white or pink flowers before an icon of Radha-Krishna while praying for harmonious affection.", "frequency": "Every Friday", "timing": "Morning"},
            {"step": 2, "title": "Shukra Beej Mantra Recitation", "action": "Chant 'Om Dram Dreem Droum Sah Shukraya Namah' 108 times with a white crystal or sphatik mala.", "frequency": "Daily", "timing": "Evening after sunset"},
            {"step": 3, "title": "Wear Fresh Fragrances & Harmonize Aura", "action": "Apply a dab of natural pure rose or sandalwood attar to your wrists and behind ears.", "frequency": "Daily", "timing": "Morning post-bath"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "राधा-कृष्ण मंदिर में सुगंधित पुष्प अर्पित करें", "action": "शुक्रवार के दिन राधा-कृष्ण के विग्रह पर सफेद या गुलाबी सुगंधित फूल चढ़ाएं और प्रेम सामंजस्य की प्रार्थना करें।", "frequency": "प्रत्येक शुक्रवार", "timing": "प्रातः काल"},
            {"step": 2, "title": "शुक्र बीज मंत्र का जाप करें", "action": "'ॐ द्रां द्रीं द्रौं सः शुक्राय नमः' मंत्र का 108 बार स्फटिक की माला से जाप करें।", "frequency": "प्रतिदिन", "timing": "सायंकाल"},
            {"step": 3, "title": "गुलाब अथवा चंदन के इत्र का प्रयोग करें", "action": "प्रतिदिन स्नान के उपरांत अपनी कलाई व गर्दन पर थोड़ा सा प्राकृतिक गुलाब या चंदन का इत्र लगाएं।", "frequency": "प्रतिदिन", "timing": "स्नान के बाद"}
        ],
        'faq_q1_en': "Why does sudden coldness or withdrawal occur between romantic partners?",
        'faq_a1_en': "Venus transit afflictions or debilitated Moon phases cause temporary emotional shutdowns. Harmonizing Venus rekindles affection.",
        'faq_q1_hi': "प्रेम संबंध में अचानक दूरियां और अनबन क्यों होने लगती है?",
        'faq_a1_hi': "जब शुक्र या पंचम भाव पर पाप ग्रहों का गोचर होता है, तो प्रेमियों के बीच अविश्वास और गलतफहमियां बढ़ जाती हैं।"
    },
    'Marriage Issues': {
        'slug': 'marriage-issues',
        'hi': 'वैवाहिक जीवन में कलह व मतभेद',
        'houses': '7th House (Kalatra Bhava - Wedlock) and 8th House (Mangalya & Marital Longevity)',
        'houses_hi': 'सप्तम भाव (विवाह व जीवनसाथी भाव) एवं अष्टम भाव (मांगल्य व वैवाहिक आयु)',
        'karakas': 'Venus (Wife/Harmony), Jupiter (Husband/Marital Wisdom), and Mars (Kuja Dosha)',
        'karakas_hi': 'शुक्र देव (दांपत्य सुख), देवगुरु बृहस्पति (पति कारक) और मंगल देव (मांगलिक प्रभाव)',
        'core_conflict_en': 'in-law interference, incompatible lifestyles, intimacy breakdowns, or separation threats',
        'core_conflict_hi': 'पति-पत्नी में आए दिन झगड़े, ससुराल पक्ष का अनावश्यक हस्तक्षेप या अलगाव की नौबत',
        'social_focus_en': 'seek professional marriage counseling, establish inviolable spousal privacy, and stop involving extended relatives in personal marital spats',
        'social_focus_hi': 'आपसी बातों में तीसरे व्यक्ति को हस्तक्षेप न करने दें, एक-दूसरे की छोटी-छोटी बातों को माफ करना सीखें',
        'emotional_focus_en': 'replace retaliatory bitterness with unconditional compassion; marriage flourishes through mutual forbearance',
        'emotional_focus_hi': 'गुस्से में कोई कठोर निर्णय न लें; शांत मन से बैठकर एक-दूसरे की जरूरतों को समझें',
        'practical_actions_en': [
            {"step": 1, "title": "Perform Gauri Shankar Puja", "action": "Worship a joined Shiva-Parvati or Gauri-Shankar icon with bilva leaves and milk.", "frequency": "Every Monday", "timing": "Morning"},
            {"step": 2, "title": "Keep Fresh Flowers in Southwest Bedroom Corner", "action": "Place fresh flowers in a ceramic vase in the south-west zone of the master bedroom.", "frequency": "Replace every 3 days", "timing": "Morning"},
            {"step": 3, "title": "Katyayani or Brihaspati Mantra Chanting", "action": "Chant the Katyayani Mantra or Jupiter Beej Mantra 108 times for marital sanctity.", "frequency": "Daily", "timing": "Morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "गौरी-शंकर पूजा व रुद्राभिषेक", "action": "सोमवार के दिन शिवलिंग पर कच्चा दूध व बेलपत्र अर्पित कर सुखी दांपत्य जीवन की प्रार्थना करें।", "frequency": "प्रत्येक सोमवार", "timing": "प्रातः काल"},
            {"step": 2, "title": "शयनकक्ष के दक्षिण-पश्चिम कोने में ताजे फूल", "action": "मास्टर बेडरूम के नैऋत्य (दक्षिण-पश्चिम) कोण में कांच के फूलदान में ताजे सुगंधित फूल रखें।", "frequency": "प्रत्येक 3 दिन में बदलें", "timing": "प्रातः काल"},
            {"step": 3, "title": "कात्यायनी अथवा बृहस्पति मंत्र का जाप", "action": "दांपत्य शांति हेतु 'ॐ क्लीं कात्यायन्यै नमः' अथवा गुरु मंत्र का 108 बार नित्य जाप करें।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Can Mangal Dosha or 7th house afflictions lead to marital breakdown?",
        'faq_a1_en': "Unaddressed Mangal Dosha or severe 7th house stress fuels explosive volatility, but consistent spiritual and psychological remedies protect marital vows.",
        'faq_q1_hi': "क्या मांगलिक दोष के कारण वैवाहिक जीवन में तनाव बढ़ता है?",
        'faq_a1_hi': "हां, सप्तम भाव में मंगल या अन्य क्रूर ग्रहों का प्रभाव दांपत्य जीवन में कलह बढ़ाता है, जिसके निवारण हेतु उचित पूजा व उपाय आवश्यक हैं।"
    },
    'Parents Health': {
        'slug': 'parents-health',
        'hi': 'माता-पिता के स्वास्थ्य से जुड़ी चिंताएं',
        'houses': '9th & 10th Houses (Pitru/Father) and 4th House (Matru/Mother)',
        'houses_hi': 'नवम व दशम भाव (पिता भाव) एवं चतुर्थ भाव (माता भाव)',
        'karakas': 'Sun (Father & Ancestral Health) and Moon (Mother & Maternal Vitality)',
        'karakas_hi': 'सूर्य देव (पिता कारक) और चंद्रमा (माता कारक)',
        'core_conflict_en': 'aging parent ailments, sudden hospitalization, or emotional caregiver fatigue',
        'core_conflict_hi': 'माता या पिता की निरंतर अस्वस्थता, पुरानी बीमारियों का उभरना या अचानक स्वास्थ्य गिरना',
        'social_focus_en': 'ensure regular medical evaluations, maintain compliant medication schedules, and offer comforting physical presence',
        'social_focus_hi': 'माता-पिता की नियमित जांच कराएं, दवाएं समय पर दिलवाएं और उनके साथ समय बिताएं',
        'emotional_focus_en': 'transform fearful helplessness into affectionate service and dedicated filial devotion',
        'emotional_focus_hi': 'चिंता के बजाय सेवा भावना रखें और अपने स्नेह से उनका मनोबल बढ़ाएं',
        'practical_actions_en': [
            {"step": 1, "title": "Touch Parents' Feet for Morning Ashirwad", "action": "Bow and touch the feet of your father and mother each morning before leaving home.", "frequency": "Daily", "timing": "Morning"},
            {"step": 2, "title": "Perform Ayushya / Maha Mrityunjaya Havan", "action": "Conduct or sponsor an Ayushya Homa or Maha Mrityunjaya ritual for your parents' longevity.", "frequency": "Monthly or Birthday", "timing": "Morning"},
            {"step": 3, "title": "Donate Milk & White Sweets on Mondays", "action": "Distribute cow milk or white sweets to elderly women in memory of maternal blessings.", "frequency": "Every Monday", "timing": "Afternoon"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "प्रतिदिन माता-पिता के चरण स्पर्श करें", "action": "प्रातः उठकर सबसे पहले माता-पिता के चरण स्पर्श कर उनका आशीर्वाद लें, इससे सूर्य व चंद्र बलवान होते हैं।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"},
            {"step": 2, "title": "महामृत्युंजय पाठ अथवा आयुष्य हवन", "action": "माता-पिता के स्वास्थ्य लाभ व दीर्घायु के लिए महामृत्युंजय मंत्र का संपुट पाठ कराएं।", "frequency": "मासिक या आवश्यकतानुसार", "timing": "शुभ मुहूर्त"},
            {"step": 3, "title": "सोमवार व रविवार को बुजुर्गों को भोजन कराएं", "action": "वृद्धाश्रम या किसी निर्धन बुजुर्ग को भोजन, दूध अथवा मौसमी फल दान करें।", "frequency": "साप्ताहिक", "timing": "दोपहर"}
        ],
        'faq_q1_en': "Which planetary placements in my horoscope threaten my parents' well-being?",
        'faq_a1_en': "Afflictions to the Sun reflect paternal challenges, while Saturn or Rahu impacting the 4th house and Moon reflects maternal health strains.",
        'faq_q1_hi': "माता-पिता के स्वास्थ्य का विचार कुंडली के किन भावों से किया जाता है?",
        'faq_a1_hi': "पिता के लिए नवम व दशम भाव तथा सूर्य, और माता के लिए चतुर्थ भाव तथा चंद्रमा का विचार किया जाता है।"
    },
    'Property Disputes': {
        'slug': 'property-disputes',
        'hi': 'पैतृक संपत्ति व जमीन विवाद',
        'houses': '4th House (Fixed Assets/Land) and 6th House (Contested Claims & Relatives)',
        'houses_hi': 'चतुर्थ भाव (अचल संपत्ति) एवं षष्ठ भाव (पारिवारिक विवाद व दावे)',
        'karakas': 'Mars (Land Lord) and Saturn (Old Ancestral Real Estate)',
        'karakas_hi': 'मंगल देव (भूमि कारक) और शनि देव (पुश्तैनी संपत्ति)',
        'core_conflict_en': 'contentious inheritance claims, boundary encroachments, and stubborn co-heirs refusing settlement',
        'core_conflict_hi': 'पैतृक संपत्ति के बंटवारे में कलह, हिस्सेदारों की बेईमानी या जमीन पर अवैध कब्जे का भय',
        'social_focus_en': 'engage neutral family arbitrators or certified registrars; prioritize clean separation over protracted court battles',
        'social_focus_hi': 'मध्यस्थों के जरिए शांतिपूर्ण समझौते का प्रयास करें और राजस्व रिकॉर्ड को दुरुस्त रखें',
        'emotional_focus_en': 'release vengeful greed; understand that peace of mind is worth more than contested square footage',
        'emotional_focus_hi': 'अत्यधिक लोभ या अहंकार से बचें; संयमपूर्वक अपनी बात रखें और विवाद को शांति से सुलझाएं',
        'practical_actions_en': [
            {"step": 1, "title": "Sundarkand Recitation on Tuesdays", "action": "Recite the sacred Sundarkand with mustard oil lamp burning.", "frequency": "Every Tuesday", "timing": "Evening after 6 PM"},
            {"step": 2, "title": "Offer Jaggery to Monkeys or Stray Cows", "action": "Feed solid jaggery blocks and roasted chickpeas to monkeys or stray cows.", "frequency": "Tuesday", "timing": "Morning"},
            {"step": 3, "title": "Bhumika Sukta & Mars Shanti Ritual", "action": "Perform Mars Shanti puja to untangle contested ancestral karmas.", "frequency": "Quarterly", "timing": "Shukla Paksha Tuesday"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "सुंदरकांड का पाठ करें", "action": "प्रत्येक मंगलवार शाम को चमेली के तेल का दीपक जलाकर सुंदरकांड का सस्वर पाठ करें।", "frequency": "प्रत्येक मंगलवार", "timing": "सायंकाल"},
            {"step": 2, "title": "बंदरों या गाय को गुड़-चना खिलाएं", "action": "मंगलवार को बंदरों को केला व गुड़-चना खिलाएं या लाल गाय की सेवा करें।", "frequency": "प्रत्येक मंगलवार", "timing": "प्रातः काल"},
            {"step": 3, "title": "भूमि व वास्तु दोष शांति उपाय", "action": "पैतृक भूमि की मिट्टी को गंगाजल से शुद्ध करें और दक्षिण दिशा में हनुमान जी का चित्र लगाएं।", "frequency": "आवश्यकतानुसार", "timing": "शुभ मुहूर्त"}
        ],
        'faq_q1_en': "Why are ancestral inheritances consistently contested in my family?",
        'faq_a1_en': "Combinations of afflicted Mars and Saturn directly influencing the 4th house frequently stir generational land feuds among siblings.",
        'faq_q1_hi': "पैतृक संपत्ति में बार-बार अड़चनें और विवाद क्यों आते हैं?",
        'faq_a1_hi': "चतुर्थ भाव या मंगल पर शनि और राहु का प्रभाव होने से पुश्तैनी जायदाद के बंटवारे में भाई-बंधुओं में विवाद खड़ा होता है।"
    },
    'Relations At Work': {
        'slug': 'relations-at-work',
        'hi': 'कार्यक्षेत्र में सहकर्मियों व अधिकारियों से अनबन',
        'houses': '6th House (Colleagues & Subordinates) and 10th House (Superiors & Executives)',
        'houses_hi': 'षष्ठ भाव (सहकर्मी व मातहत) एवं दशम भाव (अधिकारी व कार्यक्षेत्र)',
        'karakas': 'Mercury (Professional Dialogue) and Sun (Command & Executive Respect)',
        'karakas_hi': 'बुध देव (पेशेवर संवाद) और सूर्य देव (अधिकारियों से संबंध)',
        'core_conflict_en': 'toxic workplace gossip, passive-aggressive managers, and sabotaged collaborative projects',
        'core_conflict_hi': 'सहकर्मियों की गुटबाजी, बॉस का अनुचित दबाव, चुगली या टीम में समन्वय की कमी',
        'social_focus_en': 'maintain documented email trails, communicate purely on merit, and decline informal office rumour sessions',
        'social_focus_hi': 'सभी महत्वपूर्ण निर्देशों का ईमेल रिकॉर्ड रखें, काम पर ध्यान केंद्रित करें और गपशप से दूर रहें',
        'emotional_focus_en': 'detach professional duty from personal sensitivity; treat workplace opposition as tactical challenges',
        'emotional_focus_hi': 'ऑफिस की बातों को दिल पर न लें; पेशेवर दृष्टिकोण अपनाएं और शांत रहकर कार्य करें',
        'practical_actions_en': [
            {"step": 1, "title": "Surya Gayatri Japa before Commute", "action": "Chant the Surya Gayatri mantra 12 times to build an impenetrable aura of professional dignity.", "frequency": "Daily", "timing": "Morning before leaving home"},
            {"step": 2, "title": "Feed Green Fodder to Cows on Wednesdays", "action": "Feed fresh green grass or spinach to cows to appease workplace Mercury.", "frequency": "Every Wednesday", "timing": "Morning"},
            {"step": 3, "title": "Place Small Brass Sun on Work Desk", "action": "Place an energized small brass Sun facing east on your office workstation.", "frequency": "Permanent installation", "timing": "Sunday morning installation"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "सूर्य गायत्री मंत्र का जप", "action": "घर से निकलते समय 'ॐ आदित्याय विद्महे प्रभाकराय धीमहि...' मंत्र का 12 बार स्मरण करें।", "frequency": "प्रतिदिन", "timing": "ऑफिस जाने से पूर्व"},
            {"step": 2, "title": "बुधवार को गाय को हरी पालक या घास खिलाएं", "action": "बुधवार की सुबह गौशाला जाकर गाय को हरी घास या पालक खिलाएं, इससे सहकर्मियों से संबंध सुधरते हैं।", "frequency": "प्रत्येक बुधवार", "timing": "प्रातः काल"},
            {"step": 3, "title": "कार्यस्थल पर तांबे का सूर्य स्थापित करें", "action": "अपनी टेबल पर या पीछे की दीवार पर पूर्व दिशा में छोटा सा तांबे का सूर्य लगाएं।", "frequency": "एक बार", "timing": "रविवार प्रातः"}
        ],
        'faq_q1_en': "How can I stop coworkers from gossiping and undermining my reputation?",
        'faq_a1_en': "Strengthening Mercury with daily green offerings and elevating the Sun neutralizes clandestine office hostility and earns manager trust.",
        'faq_q1_hi': "कार्यस्थल पर ईर्ष्यालु सहकर्मियों और बॉस की नाराजगी से कैसे बचें?",
        'faq_a1_hi': "सूर्य की उपासना से समाज व कार्यक्षेत्र में प्रभाव बढ़ता है और बुध के उपाय से सहकर्मियों के साथ सामंजस्य स्थापित होता है।"
    },
    'Savings': {
        'slug': 'savings',
        'hi': 'धन संचय में कमी व अनावश्यक खर्च',
        'houses': '2nd House (Dhana Bhava - Accumulated Wealth) and 12th House (Vyaya - Expenditures)',
        'houses_hi': 'द्वितीय भाव (धन संचय व कोष) एवं द्वादश भाव (व्यय व अपव्यय)',
        'karakas': 'Jupiter (Dhanakaraka) and Mercury (Financial Prudence)',
        'karakas_hi': 'देवगुरु बृहस्पति (धन कारक) और बुध देव (लेखा-जोखा व बचत)',
        'core_conflict_en': 'money slipping through fingers, unexpected emergency drains, and inability to build reserve wealth',
        'core_conflict_hi': 'आमदनी से अधिक खर्च होना, अचानक आने वाले अनपेक्षित खर्चे और बचत न हो पाना',
        'social_focus_en': 'automate monthly savings transfers immediately upon salary receipt, stop impulse credit spending, and use strict budgeting apps',
        'social_focus_hi': 'वेतन मिलते ही निश्चित बचत अलग कर दें, क्रेडिट कार्ड का अंधाधुंध उपयोग बंद करें और मासिक बजट बनाएं',
        'emotional_focus_en': 'cure subconscious feelings of lack or retail therapy urges through intentional gratitude practice',
        'emotional_focus_hi': 'दिखावे के खर्चों से बचें और बचत को अपनी सुरक्षा व समृद्धि की नींव समझें',
        'practical_actions_en': [
            {"step": 1, "title": "Kuber Mantra & Yantra Worship", "action": "Chant 'Om Hreem Shreem Kreem Kuberaya Namah' 108 times facing north.", "frequency": "Daily", "timing": "Dusk"},
            {"step": 2, "title": "Tie 5 Gomti Chakras & Cowries in Red Cloth", "action": "Tie 5 energized Gomti Chakras and yellow cowries in unstitched red cloth in your cash locker.", "frequency": "Install on Diwali or Friday", "timing": "Shukla Paksha Friday"},
            {"step": 3, "title": "Feed Yellow Sweets to Young Girls on Fridays", "action": "Offer kheer or yellow sweet treats to young girls under 10 years of age.", "frequency": "Every Friday", "timing": "Afternoon"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "कुबेर मंत्र का नित्य जाप करें", "action": "उत्तर दिशा की ओर मुख करके 'ॐ यक्षाय कुबेराय वैश्रवणाय धनधान्याधिपतये नमः' का 108 बार जाप करें।", "frequency": "प्रतिदिन", "timing": "संध्या समय"},
            {"step": 2, "title": "तिजोरी में लाल कपड़े में गोमती चक्र व पीली कौड़ियां", "action": "अपनी तिजोरी या गल्ले में 5 गोमती चक्र और 5 पीली कौड़ियां लाल कपड़े में बांधकर रखें।", "frequency": "शुक्ल पक्ष शुक्रवार", "timing": "शुभ मुहूर्त"},
            {"step": 3, "title": "शुक्रवार को कन्याओं को खीर खिलाएं", "action": "शुक्रवार के दिन छोटी कन्याओं को आदरपूर्वक बुलाकर खीर या सफेद मिठाई खिलाएं।", "frequency": "प्रत्येक शुक्रवार", "timing": "दोपहर"}
        ],
        'faq_q1_en': "Why does my bank balance drain despite earning a substantial income?",
        'faq_a1_en': "When the 2nd house lord is situated in the 12th house, or Venus is afflicted by Rahu, money dissipates uncontrollably unless locked into disciplined assets.",
        'faq_q1_hi': "अच्छी कमाई होने के बावजूद घर में पैसा क्यों नहीं टिकता?",
        'faq_a1_hi': "कुंडली के द्वितीय भाव पर व्ययेश या राहु का प्रभाव होने से धन संचय नहीं हो पाता। कुबेर व लक्ष्मी जी की आराधना से धन स्थिरता आती है।"
    },
    'Sibling Relations': {
        'slug': 'sibling-relations',
        'hi': 'भाई-बहनों से अनबन व कड़वाहट',
        'houses': '3rd House (Bhratri Bhava - Siblings & Courage) and 11th House (Elder Siblings)',
        'houses_hi': 'तृतीय भाव (सहोदर व छोटे भाई-बहन) एवं एकादश भाव (बड़े भाई-बहन)',
        'karakas': 'Mars (Bhratri Karaka) and Mercury (Cousins/Companions)',
        'karakas_hi': 'मंगल देव (भाई कारक) और बुध देव (संवाद व तालमेल)',
        'core_conflict_en': 'jealousy over parental favoritism, childhood resentments resurfacing, or division of assets',
        'core_conflict_hi': 'भाई-बहनों में अकारण ईर्ष्या, बातचीत बंद होना या संपत्ति को लेकर कड़वाहट',
        'social_focus_en': 'initiate casual, warm phone check-ins without bringing up old childhood grievances; celebrate their milestones wholeheartedly',
        'social_focus_hi': 'पुरानी बातों को भुलाकर खुद आगे बढ़कर बात करें और उनके सुख-दुख में सहभागी बनें',
        'emotional_focus_en': 'acknowledge and heal ancestral comparisons; see your sibling as a unique human rather than a competitor',
        'emotional_focus_hi': 'भाई-बहन से प्रतिस्पर्धा की भावना त्यागें और आत्मीयता व प्रेम को प्राथमिकता दें',
        'practical_actions_en': [
            {"step": 1, "title": "Offer Sweet Milk to Lord Ganesha", "action": "Worship Lord Ganesha with modaks and sweet milk, praying for sibling solidarity.", "frequency": "Every Wednesday", "timing": "Morning"},
            {"step": 2, "title": "Gift Red Sweets to Brothers / Silver to Sisters", "action": "Surprise your brother with red sweets or gift your sister a silver ornament.", "frequency": "Festivals or Tuesdays", "timing": "Daytime"},
            {"step": 3, "title": "Chant Kartikeya Stotra", "action": "Recite the Kartikeya Pragya Vivardhana Stotra to eliminate fraternal animosity.", "frequency": "Every Tuesday", "timing": "Morning"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "भगवान गणेश को दूर्वा व मोदक अर्पित करें", "action": "बुधवार को विघ्नहर्ता गणेश जी को 21 दूर्वा और मोदक चढ़ाकर भाई-बहनों में प्रेम की प्रार्थना करें।", "frequency": "प्रत्येक बुधवार", "timing": "प्रातः काल"},
            {"step": 2, "title": "भाई को लाल मिठाई व बहन को उपहार दें", "action": "बिना किसी अवसर के भी भाई को उसकी पसंद की मिठाई खिलाएं या बहन को सम्मानपूर्वक उपहार दें।", "frequency": "मासिक", "timing": "दिन के समय"},
            {"step": 3, "title": "हनुमान बाहुक का पाठ करें", "action": "पारिवारिक रिश्तों में आ रही कड़वाहट दूर करने के लिए मंगलवार को हनुमान बाहुक का पाठ करें।", "frequency": "प्रत्येक मंगलवार", "timing": "सायंकाल"}
        ],
        'faq_q1_en': "Why did my affectionate sibling bond suddenly turn distant and bitter?",
        'faq_a1_en': "Afflictions between the 3rd lord and Mars or Rahu stir unprovoked misunderstandings among siblings. Kartikeya and Ganesha rituals restore harmony.",
        'faq_q1_hi': "भाई-बहनों में बचपन का प्रेम बड़े होकर मनमुटाव में क्यों बदल जाता है?",
        'faq_a1_hi': "कुंडली के तृतीय भाव में मंगल या राहु का दोष उत्पन्न होने पर सहोदरों में ईर्ष्या और संवादहीनता की स्थिति बनती है।"
    },
    'Studies': {
        'slug': 'studies',
        'hi': 'पढ़ाई में एकाग्रता की कमी व शिक्षा में रुकावट',
        'houses': '4th House (Foundational Learning) and 5th House (Buddhi, Retention & Grasping Power)',
        'houses_hi': 'चतुर्थ भाव (प्राथमिक विद्या) एवं पंचम भाव (बुद्धि, स्मरण शक्ति व उच्च शिक्षा)',
        'karakas': 'Mercury (Logic & Intellect) and Jupiter (Higher Wisdom & Guidance)',
        'karakas_hi': 'बुध देव (बुद्धि व तर्क) और देवगुरु बृहस्पति (ज्ञान व एकाग्रता)',
        'core_conflict_en': 'digital distraction, examination anxiety, memory lapses during papers, and wandering concentration',
        'core_conflict_hi': 'पढ़ाई में मन न लगना, याद किया हुआ भूल जाना, परीक्षा का भय या मोबाइल की लत',
        'social_focus_en': 'study in 45-minute timed Pomodoro blocks, switch off smartphone notifications, and review summary notes weekly',
        'social_focus_hi': 'पढ़ाई के समय फोन को दूसरे कमरे में रखें, 45 मिनट पढ़ाई के बाद 5 मिनट का ब्रेक लें और नोट्स बनाकर पढ़ें',
        'emotional_focus_en': 'replace performance panic with deep curiosity for learning; breathe rhythmically before opening textbooks',
        'emotional_focus_hi': 'परीक्षा के अंकों के दबाव से न डरें; शांत मन से समझकर पढ़ने पर ध्यान केंद्रित करें',
        'practical_actions_en': [
            {"step": 1, "title": "Saraswati Beej Mantra Chanting", "action": "Chant 'Om Aim Saraswatyai Namah' 108 times facing north or east.", "frequency": "Daily", "timing": "Early morning before studying"},
            {"step": 2, "title": "Keep Green Tablecloth & Study Desk Clean", "action": "Keep your study desk completely free of clutter, covered with a clean green cloth.", "frequency": "Daily maintenance", "timing": "Morning"},
            {"step": 3, "title": "Consume 5 Soaked Almonds with Honey", "action": "Eat 5 peeled soaked almonds with a spoonful of pure honey upon waking.", "frequency": "Daily", "timing": "Morning on empty stomach"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "माँ सरस्वती के बीज मंत्र का जाप", "action": "पूर्व या उत्तर दिशा में मुख करके 'ॐ ऐं सरस्वत्यै नमः' का 108 बार स्फटिक माला से जाप करें।", "frequency": "प्रतिदिन", "timing": "अध्ययन प्रारंभ करने से पूर्व"},
            {"step": 2, "title": "अध्ययन कक्ष व मेज को व्यवस्थित रखें", "action": "पढ़ने की मेज पर हरा कपड़ा बिछाएं और पुस्तकें सुव्यवस्थित रखें; मेज पर जूठे बर्तन न रखें।", "frequency": "प्रतिदिन", "timing": "सुबह"},
            {"step": 3, "title": "भीगे बादाम और तुलसी का सेवन", "action": "सुबह खाली पेट 5 भीगे हुए बादाम और 2 तुलसी के पत्ते खाएं, इससे स्मरण शक्ति तेज होती है।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"}
        ],
        'faq_q1_en': "Why does my child freeze or forget answers during crucial examinations?",
        'faq_a1_en': "A stressed Mercury afflicted by Rahu triggers intellectual panic and blankness under pressure. Saraswati puja restores mental presence.",
        'faq_q1_hi': "खूब पढ़ने के बाद भी परीक्षा हॉल में जाते ही सब भूल जाने का क्या कारण है?",
        'faq_a1_hi': "जब बुध या पंचम भाव पर राहु का प्रभाव होता है, तो ऐन वक्त पर घबराहट और स्मृति लोप की समस्या होती है।"
    },
    'Study Abroad': {
        'slug': 'study-abroad',
        'hi': 'विदेश में उच्च शिक्षा व वीज़ा संबंधी अड़चनें',
        'houses': '9th House (Higher Knowledge & Long Journeys) and 12th House (Foreign Residence & Oceans)',
        'houses_hi': 'नवम भाव (उच्च शिक्षा व दूरस्थ यात्रा) एवं द्वादश भाव (विदेश वास व वीज़ा)',
        'karakas': 'Rahu (Foreign Cultures & Relocation), Moon (Crossing Waters), and Jupiter (Academics)',
        'karakas_hi': 'राहु देव (विदेशी भूमि व वीज़ा), चंद्रमा (जल पार यात्रा) और देवगुरु बृहस्पति (विश्वविद्यालय)',
        'core_conflict_en': 'visa approval delays, scholarship rejections, university acceptance stalls, or foreign admission bureaucracy',
        'core_conflict_hi': 'वीज़ा फाइल अटकना, मनचाहे विश्वविद्यालय से ऑफर लेटर न आना या विदेशी खर्चे का प्रबंध न होना',
        'social_focus_en': 'audit visa application records with certified consultants, ensure proof of funds compliance, and prepare for consular interviews',
        'social_focus_hi': 'वीज़ा आवेदन के सभी कागजात विशेषज्ञ से जांच कराएं, फंड्स का सही ब्योरा रखें और इंटरव्यू की पूरी तैयारी करें',
        'emotional_focus_en': 'stay centered during embassy waiting periods; channel nervous energy into language proficiency and practical research',
        'emotional_focus_hi': 'वीज़ा प्रक्रिया में होने वाली देरी से अधीर न हों, सकारात्मक रहें और विदेशी संस्कृति के अनुकूल खुद को ढालें',
        'practical_actions_en': [
            {"step": 1, "title": "Rahu Stotra & Bhairav Worship for Visas", "action": "Chant the Rahu Beej Mantra 'Om Bhram Bhreem Bhroum Sah Rahave Namah' 108 times.", "frequency": "Every Saturday evening", "timing": "After 7 PM"},
            {"step": 2, "title": "Feed Seven Grains (Satnaja) to Pigeons", "action": "Scatter a mix of seven grains (satnaja) to pigeons or birds in an open courtyard.", "frequency": "Daily or Wednesdays", "timing": "Morning before breakfast"},
            {"step": 3, "title": "Keep Passport / Offer Letter in North-West", "action": "Store your educational certificates and passport in a clean yellow folder in the North-West (Vayavya) corner of your home.", "frequency": "Continuous", "timing": "Any auspicious day"}
        ],
        'practical_actions_hi': [
            {"step": 1, "title": "राहु बीज मंत्र व भैरव जी की उपासना", "action": "वीज़ा बाधा निवारण हेतु 'ॐ भ्रां भ्रीं भ्रौं सः राहवे नमः' का 108 बार शनिवार शाम को जाप करें।", "frequency": "प्रत्येक शनिवार", "timing": "सायंकाल 7 बजे के बाद"},
            {"step": 2, "title": "पक्षियों को सप्तधान्य (सतनाजा) डालें", "action": "सात प्रकार के अनाजों को मिलाकर प्रतिदिन सुबह पक्षियों को चुगने के लिए डालें।", "frequency": "प्रतिदिन", "timing": "प्रातः काल"},
            {"step": 3, "title": "पासपोर्ट व दस्तावेज वायव्य कोण में रखें", "action": "अपने वीज़ा दस्तावेज व पासपोर्ट को घर के उत्तर-पश्चिम (वायव्य) कोण में पीले लिफाफे में रखें।", "frequency": "नियमित", "timing": "शुभ मुहूर्त"}
        ],
        'faq_q1_en': "Why are my student visa applications repeatedly flagged or delayed?",
        'faq_a1_en': "Relocation to foreign shores requires harmonious coordination between the 9th, 12th houses and Rahu. Appeasing Rahu clears embassy blockages.",
        'faq_q1_hi': "विदेश जाकर पढ़ने के सपने में वीज़ा की रुकावट क्यों आ रही है?",
        'faq_a1_hi': "कुंडली का द्वादश भाव विदेश यात्रा का है और राहु विदेश का कारक है। राहु और नवमेश के उपाय करने से वीज़ा संबंधी बाधाएं दूर होती हैं।"
    }
}

# ==============================================================================
# POOJA HINDI TRANSLATION HELPER
# ==============================================================================

POOJA_HI_MAP = {
    'Brihaspati Puja on Thursdays': 'गुरुवार को देवगुरु बृहस्पति शांति पूजा',
    'Dhanvantari Puja for health': 'आरोग्य लाभ हेतु भगवान धन्वंतरि पूजा',
    'Vishnu Sahasranama chanting': 'भगवान श्री विष्णु सहस्रनाम पाठ',
    'Saraswati Puja': 'माँ सरस्वती विद्या व बुद्धि प्रदाता पूजा',
    'Durga Saptashati Path': 'माँ दुर्गा सप्तशती संपूर्ण पाठ',
    'Chandra Shanti Puja': 'मानसिक शांति हेतु चंद्र शांति पूजा',
    'Griha Shanti Puja': 'पारिवारिक सौहार्द हेतु गृह शांति पूजा',
    'Nazar Dosha Nivaran Puja': 'बुरी नज़र व नकारात्मक ऊर्जा निवारण पूजा',
    'Vastu Shanti Puja': 'गृह व प्रतिष्ठान वास्तु शांति पूजा',
    'Kubera Puja for wealth': 'समृद्धि व धन वृद्धि हेतु भगवान कुबेर पूजा',
    'Friendship harmony rituals': 'मित्रता व सामाजिक सामंजस्य पूजा',
    'Shani Shanti Puja': 'शनि देव शांति व तैलाभिषेक पूजा',
    'Santana Gopal Puja': 'संतान सुख व कल्याण हेतु संतान गोपाल पूजा',
    'Gauri Shankar Puja': 'दांपत्य मधुरता हेतु गौरी-शंकर पूजा',
    'Lakshmi Narayan Havan': 'श्री लक्ष्मी-नारायण महाहवन',
    'Rahu Shanti Puja': 'राहु शांति व ग्रह दोष निवारण पूजा',
    'Mahalakshmi Puja': 'माँ महालक्ष्मी धन वर्षा पूजा',
    'Ayushya Havan for parents': 'माता-पिता के दीर्घायु हेतु आयुष्य हवन',
    'Ganesh Puja for siblings': 'सहोदर प्रेम हेतु श्री गणेश पूजा',
    'Workplace Ganesh Puja': 'कार्यक्षेत्र में सफलता हेतु विघ्नहर्ता गणेश पूजा',
    'Hanuman Chalisa on Tuesdays': 'मंगलवार को हनुमान चालीसा व सुंदरकांड पाठ',
    'Shukra Shanti Puja on Fridays': 'शुक्रवार को शुक्र शांति व लक्ष्मी पूजा'
}

def translate_pooja_hi(pooja_en):
    if not pooja_en:
        return 'वैदिक नवग्रह शांति पूजा'
    if pooja_en in POOJA_HI_MAP:
        return POOJA_HI_MAP[pooja_en]
    for k, v in POOJA_HI_MAP.items():
        if k.lower() in pooja_en.lower():
            return v
    return f"{pooja_en} (वैदिक शांति विधान)"

# ==============================================================================
# CONTENT GENERATOR ENGINE
# ==============================================================================

def generate_remedy_record(row):
    """
    Takes a single row from af_remedies and generates all updated and new fields:
    - slug
    - issue_slug
    - ruling_planet
    - meta_title
    - meta_description
    - quick_answer
    - action_checklist_json
    - faq_json
    - summary
    - astrological_remedies
    - social_remedies
    - emotional_remedies
    Preserves:
    - suggested_gemstone
    - suggested_pooja
    """
    raw_sign = row['sun_sign'].strip()
    raw_gender = row['gender'].strip()
    raw_issue = row['issue'].strip()
    raw_lang = row['language'].strip()

    # Determine language mode
    is_hi = (raw_lang in ('Hindi', 'hi', 'HI'))

    # Normalize Sign
    if raw_sign in ZODIAC_SIGNS:
        sign_en = raw_sign
        sign_info = ZODIAC_SIGNS[raw_sign]
        sign_hi = sign_info['hi']
    elif raw_sign in SIGN_MAP_HI_TO_EN:
        sign_en = SIGN_MAP_HI_TO_EN[raw_sign]
        sign_info = ZODIAC_SIGNS[sign_en]
        sign_hi = raw_sign
    else:
        # Fallback
        sign_en = 'Aries'
        sign_info = ZODIAC_SIGNS['Aries']
        sign_hi = 'मेष'

    # Normalize Gender
    # Gender values in DB: 'Boy', 'Girl' (or 'लड़का', 'लड़की')
    if raw_gender.lower() in ('boy', 'लड़का', 'male'):
        gender_en = 'Boy'
        gender_en_lower = 'boy'
        gender_label_en = 'boys and young men'
        gender_hi = 'लड़का'
        gender_label_hi = 'लड़के/युवक'
    else:
        gender_en = 'Girl'
        gender_en_lower = 'girl'
        gender_label_en = 'girls and young women'
        gender_hi = 'लड़की'
        gender_label_hi = 'लड़की/युवती'

    # Normalize Issue
    issue_info = ISSUES.get(raw_issue)
    if not issue_info:
        # Search case-insensitively
        for k, v in ISSUES.items():
            if k.lower() == raw_issue.lower():
                issue_info = v
                raw_issue = k
                break
    if not issue_info:
        raise ValueError(f"Unknown issue: {raw_issue}")

    issue_slug = issue_info['slug']
    issue_hi = issue_info['hi']

    # Generate canonical URL slug: {sign}-{gender}-{issue}
    slug = f"{sign_en.lower()}-{gender_en_lower}-{issue_slug}"

    # Ruling Planet
    ruling_planet = sign_info['ruler_hi'] if is_hi else sign_info['ruler_en']

    # Preserved or fallback gemstone & pooja
    curr_gemstone = row.get('suggested_gemstone') or sign_info['gemstone_default']
    curr_pooja = row.get('suggested_pooja') or 'Navagraha Shanti Puja'
    curr_pooja_hi = translate_pooja_hi(curr_pooja)

    # Gemstone Hindi name
    gemstone_hi = sign_info['gemstone_hi']

    # --------------------------------------------------------------------------
    # 1. META TITLE (Max 160 chars)
    # --------------------------------------------------------------------------
    if not is_hi:
        meta_title = f"{sign_en} {gender_en} {raw_issue} Remedies: Vedic Astrology & Practical Solutions | MeraRashifal"
        if len(meta_title) > 160:
            meta_title = f"{sign_en} {gender_en} {raw_issue} Remedies: Vedic Guide | MeraRashifal"
    else:
        meta_title = f"{sign_hi} राशि के {gender_label_hi} के लिए {issue_hi} के अचूक ज्योतिषीय उपाय | MeraRashifal"
        if len(meta_title) > 160:
            meta_title = f"{sign_hi} {gender_label_hi} {issue_hi} के उपाय | MeraRashifal"

    # --------------------------------------------------------------------------
    # 2. META DESCRIPTION (Max 255 chars)
    # --------------------------------------------------------------------------
    if not is_hi:
        meta_desc = (
            f"Vedic astrology remedies for {sign_en} {gender_en_lower}s facing {raw_issue.lower()}. "
            f"Understand {sign_info['ruler_en']} influences, {curr_pooja}, {curr_gemstone} guidelines, "
            f"and practical action checklists to restore stability and success."
        )
        if len(meta_desc) > 255:
            meta_desc = meta_desc[:252] + "..."
    else:
        meta_desc = (
            f"{sign_hi} राशि के {gender_label_hi} के लिए {issue_hi} के प्रभावी वैदिक ज्योतिषीय उपाय। "
            f"जानिए स्वामी ग्रह {sign_info['ruler_hi']} का प्रभाव, {curr_pooja_hi}, रत्न सलाह और व्यावहारिक दिनचर्या।"
        )
        if len(meta_desc) > 255:
            meta_desc = meta_desc[:252] + "..."

    # --------------------------------------------------------------------------
    # 3. QUICK ANSWER (GEO / AI Overviews, Punchy & Bulleted)
    # --------------------------------------------------------------------------
    if not is_hi:
        quick_answer = (
            f"🔴 Astrological Cause: For {sign_en} (Ruled by {sign_info['ruler_en']}), this challenge stems from afflictions in the {issue_info['houses']} combined with natural {sign_info['weakness_en']}.\n"
            f"🟢 Core Vedic Remedy: Chant '{sign_info['mantra_en']}' (108 times daily) and perform {curr_pooja}.\n"
            f"💡 Action Strategy: {issue_info['social_focus_en']}."
        )
    else:
        quick_answer = (
            f"🔴 मूल ज्योतिषीय कारण: {sign_hi} राशि (स्वामी: {sign_info['ruler_hi']}) के लिए यह समस्या कुंडली के {issue_info['houses_hi']} में ऊर्जा असंतुलन व {sign_info['weakness_hi']} से उत्पन्न होती है।\n"
            f"🟢 अचूक महा-उपाय: प्रतिदिन '{sign_info['mantra_hi']}' का 108 बार जाप करें एवं {curr_pooja_hi} कराएं।\n"
            f"💡 व्यावहारिक सूत्र: {issue_info['social_focus_hi']}।"
        )

    # --------------------------------------------------------------------------
    # 4. ACTION CHECKLIST JSON (3 structured steps)
    # --------------------------------------------------------------------------
    if not is_hi:
        action_checklist = issue_info['practical_actions_en']
    else:
        action_checklist = issue_info['practical_actions_hi']
    action_checklist_json = json.dumps(action_checklist, ensure_ascii=False)

    # --------------------------------------------------------------------------
    # 5. FAQ JSON (3 structured Q&As)
    # --------------------------------------------------------------------------
    if not is_hi:
        faq_list = [
            {
                "question": f"Why do {sign_en} {gender_en_lower}s struggle specifically with {raw_issue.lower()}?",
                "answer": (
                    f"As a {sign_info['element_en']} sign ruled by {sign_info['ruler_en']}, {sign_en} {gender_label_en} "
                    f"are inherently {sign_info['nature_en']}. Afflictions in the {issue_info['houses']} trigger "
                    f"{sign_info['weakness_en']}, escalating {issue_info['core_conflict_en']}."
                )
            },
            {
                "question": f"Can a {sign_en} {gender_en_lower} wear {curr_gemstone} to resolve this issue?",
                "answer": (
                    f"While {curr_gemstone} harmonizes {sign_info['ruler_en']} energies, gemstones must never be worn solely "
                    f"by Sun sign. Verify that your ascendant chart favors this stone before wearing."
                )
            },
            {
                "question": f"How long does it take for {curr_pooja} and Vedic mantras to show results?",
                "answer": (
                    f"Devout daily mantra chanting ('{sign_info['mantra_en']}') and charitable acts show noticeable stabilizing "
                    f"results within 41 to 45 days (one astrological mandala)."
                )
            }
        ]
    else:
        faq_list = [
            {
                "question": f"{sign_hi} राशि के {gender_label_hi} को {issue_hi} में विशेष परेशानी क्यों आती है?",
                "answer": (
                    f"{sign_hi} राशि का तत्व {sign_info['element_hi']} और स्वामी {sign_info['ruler_hi']} हैं। "
                    f"कुंडली के {issue_info['houses_hi']} पर क्रूर ग्रहों के प्रभाव से उनकी {sign_info['weakness_hi']} बढ़ जाती है, जिससे {issue_info['core_conflict_hi']} होती है।"
                )
            },
            {
                "question": f"क्या {issue_hi} से मुक्ति के लिए {gemstone_hi} पहनना सुरक्षित है?",
                "answer": (
                    f"{gemstone_hi} धारण करने से पहले लग्न कुंडली अवश्य जांचें। यदि ग्रह 6ठे, 8वें या 12वें भाव का स्वामी हो, तो रत्न के स्थान पर केवल मंत्र जप व दान करें।"
                )
            },
            {
                "question": f"{curr_pooja_hi} और वैदिक मंत्रों का प्रभाव कितने समय में दिखाई देता है?",
                "answer": (
                    f"सच्ची निष्ठा से बीज मंत्र का नियमित 108 बार जाप और पूजा एक मंडल (लगभग 41-45 दिनों) में सकारात्मक परिणाम देने लगते हैं।"
                )
            }
        ]
    faq_json = json.dumps(faq_list, ensure_ascii=False)

    # --------------------------------------------------------------------------
    # 6. SUMMARY (Concise, punchy 3-point highlight)
    # --------------------------------------------------------------------------
    if not is_hi:
        summary = (
            f"✨ Key Insight: {sign_en} natives must channel their {sign_info['nature_en']} nature to overcome {issue_info['core_conflict_en']}.\n"
            f"📿 Daily Practice: {sign_info['ruler_en']} Beej Mantra (108 reps) & worship {sign_info['deity_en']}.\n"
            f"🎯 Success Key: {issue_info['social_focus_en']}."
        )
    else:
        summary = (
            f"✨ मुख्य संकेत: {sign_hi} राशि के जातकों को इस समय {issue_info['core_conflict_hi']} से बचने हेतु धैर्य व विवेक से काम लेना होगा।\n"
            f"📿 दैनिक साधना: {sign_info['ruler_hi']} बीज मंत्र जाप (108 बार) व {sign_info['deity_hi']} की आराधना।\n"
            f"🎯 सफलता सूत्र: {issue_info['social_focus_hi']}।"
        )

    # --------------------------------------------------------------------------
    # 7. ASTROLOGICAL REMEDIES (Vedic rituals, pooja & gemstones)
    # --------------------------------------------------------------------------
    if not is_hi:
        astrological_remedies = (
            f"📿 Vedic Mantra Chanting: Chant the {sign_info['ruler_en']} Beej Mantra '{sign_info['mantra_en']}' 108 times daily using a japa mala to fortify vital energy.\n\n"
            f"🪔 Prescribed Temple Pooja: Organize or participate in {curr_pooja} dedicated to {sign_info['deity_en']} to clear karmic hurdles.\n\n"
            f"💎 Gemstone Advice: Consider wearing {curr_gemstone} on {sign_info['gemstone_finger']}. (⚠️ Caution: Always verify ascendant suitability in birth chart first).\n\n"
            f"🌾 Planetary Charity (Daan): Mitigate malefic transit drag by donating {sign_info['daan_item_en']}."
        )
    else:
        astrological_remedies = (
            f"📿 वैदिक मंत्र साधना: प्रतिदिन स्नानोपरांत शांत चित्त से {sign_info['ruler_hi']} के बीज मंत्र '{sign_info['mantra_hi']}' का 108 बार जाप करें।\n\n"
            f"🪔 अनुशंसित पूजा अनुष्ठान: विधि-विधान से {curr_pooja_hi} संपन्न कराएं और {sign_info['deity_hi']} को दीप अर्पित कर आशीर्वाद प्राप्त करें।\n\n"
            f"💎 रत्न परामर्श: अनुकूल स्थिति में {gemstone_hi} को {sign_info['gemstone_finger_hi']} धारण करें। (⚠️ चेतावनी: कुंडली में ग्रह कारक होने पर ही धारण करें)।\n\n"
            f"🌾 ग्रह शांति दान: प्रतिकूल ऊर्जा के शमन हेतु {sign_info['daan_item_hi']}।"
        )

    # --------------------------------------------------------------------------
    # 8. SOCIAL REMEDIES (Interpersonal, boundary, and professional strategies)
    # --------------------------------------------------------------------------
    if not is_hi:
        social_remedies = (
            f"🤝 Practical Harmony: {issue_info['social_focus_en']}.\n\n"
            f"🗣️ Thoughtful Restraint: Pause before reacting in heated discussions; avoid abrasive verbal exchanges.\n\n"
            f"📋 Clear Boundaries: Set firm, mutually respectful boundaries and keep formal transactions transparent."
        )
    else:
        social_remedies = (
            f"🤝 व्यावहारिक तालमेल: {issue_info['social_focus_hi']}।\n\n"
            f"🗣️ वाणी व व्यवहार संयम: किसी भी उत्तेजक परिस्थिति या वाद-विवाद में तुरंत प्रतिक्रिया न दें; धैर्य रखें।\n\n"
            f"📋 स्पष्ट सीमाएं: कार्यक्षेत्र व रिश्तों में स्पष्ट सीमाएं बनाएं और महत्वपूर्ण निर्णय लिखित रूप में लें।"
        )

    # --------------------------------------------------------------------------
    # 9. EMOTIONAL REMEDIES (Psychological resilience, grounding, stress control)
    # --------------------------------------------------------------------------
    if not is_hi:
        emotional_remedies = (
            f"🧘 Pranayama & Mindfulness: Practice 15 minutes of rhythmic diaphragmatic breathing each morning to calm inner tension.\n\n"
            f"🌿 Emotional Resilience: {issue_info['emotional_focus_en']}.\n\n"
            f"✨ Mindset Reset: Conclude your evening with a 3-point gratitude reflection to ground your mind in verifiable facts."
        )
    else:
        emotional_remedies = (
            f"🧘 प्राणायाम व ध्यान: प्रतिदिन सुबह 15 मिनट अनुलोम-विलोम व भ्रामरी प्राणायाम का अभ्यास करें।\n\n"
            f"🌿 मानसिक संतुलन: {issue_info['emotional_focus_hi']}।\n\n"
            f"✨ सकारात्मक दृष्टिकोण: सोने से पहले दिन की 3 अच्छी बातों का स्मरण कर ईश्वर का आभार व्यक्त करें।"
        )

    return {
        'remedy_id': row['remedy_id'],
        'slug': slug,
        'issue_slug': issue_slug,
        'ruling_planet': ruling_planet,
        'meta_title': meta_title,
        'meta_description': meta_desc,
        'quick_answer': quick_answer,
        'action_checklist_json': action_checklist_json,
        'faq_json': faq_json,
        'summary': summary,
        'astrological_remedies': astrological_remedies,
        'social_remedies': social_remedies,
        'emotional_remedies': emotional_remedies
    }

# ==============================================================================
# MAIN CLI & RUNNER
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(description="Generate & populate af_remedies table.")
    parser.add_argument('--host', default='localhost', help='MySQL Host')
    parser.add_argument('--user', default='root', help='MySQL User')
    parser.add_argument('--password', default='', help='MySQL Password')
    parser.add_argument('--database', default='merarashifal', help='Database name')
    parser.add_argument('--dry-run', action='store_true', help='Validate records in-memory and print summary statistics')
    parser.add_argument('--write-files', action='store_true', help='Write output SQL update script and preview JSON file')
    parser.add_argument('--apply-db', action='store_true', help='Execute updates directly in MySQL/MariaDB database')
    parser.add_argument('--limit', type=int, default=None, help='Limit number of rows processed')
    parser.add_argument('--sign', default=None, help='Filter by sun_sign (e.g. Aries, मेष)')
    parser.add_argument('--issue', default=None, help='Filter by issue (e.g. Love, Health)')
    parser.add_argument('--lang', default=None, help='Filter by language (en, hi, English, Hindi)')

    args = parser.parse_args()

    # Default to dry-run if neither apply-db nor write-files requested
    if not args.apply_db and not args.write_files:
        args.dry_run = True

    print("=" * 75)
    print("MeraRashifal - Astrological Remedies Content & Schema Engine")
    print(f"Connecting to database '{args.database}' on {args.host}...")
    print("=" * 75)

    conn = pymysql.connect(
        host=args.host,
        user=args.user,
        password=args.password,
        database=args.database,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

    try:
        where_clauses = []
        params = []

        if args.sign:
            where_clauses.append("sun_sign = %s")
            params.append(args.sign)
        if args.issue:
            where_clauses.append("issue = %s")
            params.append(args.issue)
        if args.lang:
            lang_val = 'Hindi' if args.lang.lower() in ('hi', 'hindi') else 'English'
            where_clauses.append("language = %s")
            params.append(lang_val)

        where_sql = f"WHERE {' AND '.join(where_clauses)}" if where_clauses else ""
        limit_sql = f"LIMIT {args.limit}" if args.limit else ""

        query = f"""
            SELECT remedy_id, sun_sign, gender, issue, language,
                   suggested_gemstone, suggested_pooja
            FROM af_remedies
            {where_sql}
            ORDER BY remedy_id ASC
            {limit_sql}
        """

        with conn.cursor() as cur:
            cur.execute(query, params)
            rows = cur.fetchall()

        total_rows = len(rows)
        print(f"Loaded {total_rows} rows from `af_remedies` for processing.")

        if total_rows == 0:
            print("No matching rows found. Exiting.")
            return

        processed = []
        preview_samples = []

        # Validation counters
        max_title_len = 0
        max_desc_len = 0
        json_checklist_errors = 0
        json_faq_errors = 0
        unique_slugs_en = set()
        unique_slugs_hi = set()

        for r in rows:
            rec = generate_remedy_record(r)
            processed.append(rec)

            t_len = len(rec['meta_title'])
            d_len = len(rec['meta_description'])
            if t_len > max_title_len:
                max_title_len = t_len
            if d_len > max_desc_len:
                max_desc_len = d_len

            # Check JSON Checklist
            try:
                ch = json.loads(rec['action_checklist_json'])
                if not isinstance(ch, list) or len(ch) < 3:
                    json_checklist_errors += 1
            except Exception:
                json_checklist_errors += 1

            # Check JSON FAQ
            try:
                fa = json.loads(rec['faq_json'])
                if not isinstance(fa, list) or len(fa) != 3:
                    json_faq_errors += 1
            except Exception:
                json_faq_errors += 1

            # Check Slug Uniqueness
            if r['language'] == 'English':
                unique_slugs_en.add(rec['slug'])
            else:
                unique_slugs_hi.add(rec['slug'])

            # Collect diverse preview samples (2 English + 2 Hindi)
            if r['issue'] in ('Love', 'Business Loss or Slow Down', 'Job Promotion', 'Health'):
                if r['language'] == 'English' and sum(1 for s in preview_samples if s['language'] == 'English') < 2:
                    preview_samples.append({
                        'remedy_id': r['remedy_id'],
                        'sun_sign': r['sun_sign'],
                        'gender': r['gender'],
                        'issue': r['issue'],
                        'language': r['language'],
                        'fields': rec
                    })
                elif r['language'] == 'Hindi' and sum(1 for s in preview_samples if s['language'] == 'Hindi') < 2:
                    preview_samples.append({
                        'remedy_id': r['remedy_id'],
                        'sun_sign': r['sun_sign'],
                        'gender': r['gender'],
                        'issue': r['issue'],
                        'language': r['language'],
                        'fields': rec
                    })

        print("\n" + "-" * 75)
        print("VALIDATION & QUALITY AUDIT SUMMARY")
        print("-" * 75)
        print(f"Total Rows Processed:             {len(processed)}")
        print(f"Unique English Slugs:             {len(unique_slugs_en)}")
        print(f"Unique Hindi Slugs:               {len(unique_slugs_hi)}")
        print(f"Max meta_title length:            {max_title_len} chars (Schema Limit: 160)")
        print(f"Max meta_description length:      {max_desc_len} chars (Schema Limit: 255)")
        print(f"Action Checklist JSON Errors:     {json_checklist_errors}")
        print(f"FAQ JSON Validation Errors:       {json_faq_errors}")

        if max_title_len > 160:
            print("WARNING: meta_title exceeds 160 chars in one or more records!")
        if max_desc_len > 255:
            print("WARNING: meta_description exceeds 255 chars in one or more records!")

        # Print Sample Previews
        print("\n" + "=" * 75)
        print("SAMPLE OUTPUT PREVIEWS (English & Hindi)")
        print("=" * 75)
        for s in preview_samples:
            f = s['fields']
            print(f"\n>>> Remedy ID: {s['remedy_id']} | {s['sun_sign']} ({s['gender']}) - {s['issue']} [{s['language']}]")
            print(f"Slug:             {f['slug']}")
            print(f"Issue Slug:       {f['issue_slug']}")
            print(f"Ruling Planet:    {f['ruling_planet']}")
            print(f"Meta Title:       {f['meta_title']} ({len(f['meta_title'])} chars)")
            print(f"Meta Description: {f['meta_description']} ({len(f['meta_description'])} chars)")
            print(f"Quick Answer:\n  {f['quick_answer']}")
            print("Checklist Preview:")
            ch = json.loads(f['action_checklist_json'])
            for step in ch[:2]:
                print(f"  Step {step.get('step')}: {step.get('title')} ({step.get('timing')})")
            print("FAQ Preview:")
            fa = json.loads(f['faq_json'])
            print(f"  Q1: {fa[0].get('question')}")
            print(f"  A1: {fa[0].get('answer')[:120]}...")

        # File Writing (output/update_af_remedies.sql and output/af_remedies_preview.json)
        if args.write_files:
            output_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'output')
            os.makedirs(output_dir, exist_ok=True)

            sql_path = os.path.join(output_dir, 'update_af_remedies.sql')
            json_path = os.path.join(output_dir, 'af_remedies_preview.json')

            print(f"\nWriting SQL batch updates to: {sql_path}...")
            with open(sql_path, 'w', encoding='utf-8') as f:
                f.write("-- MeraRashifal: af_remedies content update\n")
                f.write("START TRANSACTION;\n\n")

                def esc(val):
                    if val is None:
                        return "NULL"
                    return "'" + str(val).replace("\\", "\\\\").replace("'", "''") + "'"

                for item in processed:
                    stmt = (
                        f"UPDATE `af_remedies` SET "
                        f"`slug` = {esc(item['slug'])}, "
                        f"`issue_slug` = {esc(item['issue_slug'])}, "
                        f"`ruling_planet` = {esc(item['ruling_planet'])}, "
                        f"`meta_title` = {esc(item['meta_title'])}, "
                        f"`meta_description` = {esc(item['meta_description'])}, "
                        f"`quick_answer` = {esc(item['quick_answer'])}, "
                        f"`action_checklist_json` = {esc(item['action_checklist_json'])}, "
                        f"`faq_json` = {esc(item['faq_json'])}, "
                        f"`summary` = {esc(item['summary'])}, "
                        f"`astrological_remedies` = {esc(item['astrological_remedies'])}, "
                        f"`social_remedies` = {esc(item['social_remedies'])}, "
                        f"`emotional_remedies` = {esc(item['emotional_remedies'])} "
                        f"WHERE `remedy_id` = {item['remedy_id']};\n"
                    )
                    f.write(stmt)
                f.write("\nCOMMIT;\n")

            print(f"SQL file generated successfully ({os.path.getsize(sql_path):,} bytes).")

            print(f"Writing preview JSON to: {json_path}...")
            # Sample 20 diverse rows for JSON preview
            sample_ids = [s['remedy_id'] for s in preview_samples]
            all_preview = [p for p in processed if p['remedy_id'] in sample_ids or p['remedy_id'] % 50 == 0]
            with open(json_path, 'w', encoding='utf-8') as f:
                json.dump(all_preview, f, ensure_ascii=False, indent=2)
            print(f"Preview JSON written successfully ({len(all_preview)} sample items).")

        # Database Update
        if args.apply_db:
            print("\nApplying updates directly to MariaDB database...")
            update_sql = """
                UPDATE `af_remedies` SET
                    `slug` = %(slug)s,
                    `issue_slug` = %(issue_slug)s,
                    `ruling_planet` = %(ruling_planet)s,
                    `meta_title` = %(meta_title)s,
                    `meta_description` = %(meta_description)s,
                    `quick_answer` = %(quick_answer)s,
                    `action_checklist_json` = %(action_checklist_json)s,
                    `faq_json` = %(faq_json)s,
                    `summary` = %(summary)s,
                    `astrological_remedies` = %(astrological_remedies)s,
                    `social_remedies` = %(social_remedies)s,
                    `emotional_remedies` = %(emotional_remedies)s
                WHERE `remedy_id` = %(remedy_id)s
            """
            with conn.cursor() as cur:
                cur.executemany(update_sql, processed)
                conn.commit()
            print(f"Successfully updated all {len(processed)} rows in `af_remedies`!")

        print("\nAll tasks completed successfully.")

    finally:
        conn.close()

if __name__ == '__main__':
    main()
