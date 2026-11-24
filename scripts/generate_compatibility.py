#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MeraRashifal - Zodiac Compatibility Content & Metadata Generator
Author: Antigravity AI
Purpose: Upgrades all 576 slices of zodiac_compatibility with rich, SEO/GEO-optimized,
         authentic bilingual (English & Hindi) content, structured metadata, and FAQs.
"""

import sys
import os
import json
import argparse
import decimal
import pymysql

# Force UTF-8 stdout for Windows consoles
if sys.stdout.encoding != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

# ==============================================================================
# ASTROLOGICAL KNOWLEDGE BASE
# ==============================================================================

SIGNS = {
    'Aries': {
        'hi': 'मेष',
        'element': 'Fire',
        'element_hi': 'अग्नि',
        'quality': 'Cardinal',
        'quality_hi': 'चर',
        'ruler': 'Mars',
        'ruler_hi': 'मंगल',
        'symbol': 'The Ram',
        'archetype_en': 'Bold Trailblazer',
        'archetype_hi': 'साहसी अग्रदूत',
        'traits_en': 'passionate, direct, energetic, fiercely independent',
        'traits_hi': 'साहसी, स्पष्टवादी, ऊर्जावान और आत्मनिर्भर',
        'strengths_en': 'unstoppable momentum, genuine honesty, courageous leadership',
        'strengths_hi': 'अदम्य उत्साह, निश्छल स्पष्टवादिता और साहसिक नेतृत्व',
        'challenge_en': 'impatience, competitive edge, quick temper',
        'challenge_hi': 'अधीरता, अत्यधिक प्रतिस्पर्धा और क्षणिक क्रोध',
    },
    'Taurus': {
        'hi': 'वृषभ',
        'element': 'Earth',
        'element_hi': 'पृथ्वी',
        'quality': 'Fixed',
        'quality_hi': 'स्थिर',
        'ruler': 'Venus',
        'ruler_hi': 'शुक्र',
        'symbol': 'The Bull',
        'archetype_en': 'Steadfast Anchor',
        'archetype_hi': 'दृढ़ संबल व निर्माता',
        'traits_en': 'grounded, loyal, sensual, deeply patient',
        'traits_hi': 'धैर्यवान, निष्ठावान, व्यावहारिक और शांत',
        'strengths_en': 'unshakable reliability, practical wisdom, enduring loyalty',
        'strengths_hi': 'अटूट विश्वसनीयता, व्यावहारिक समझ और स्थायी समर्पण',
        'challenge_en': 'stubbornness, resistance to change, possessiveness',
        'challenge_hi': 'हठ, बदलाव का विरोध और अधिकार भावना',
    },
    'Gemini': {
        'hi': 'मिथुन',
        'element': 'Air',
        'element_hi': 'वायु',
        'quality': 'Mutable',
        'quality_hi': 'द्विस्वभाव',
        'ruler': 'Mercury',
        'ruler_hi': 'बुध',
        'symbol': 'The Twins',
        'archetype_en': 'Curious Messenger',
        'archetype_hi': 'जिज्ञासु संवादक',
        'traits_en': 'witty, versatile, intellectually curious, adaptable',
        'traits_hi': 'बुद्धिमान, हाजिरजवाब, बहुमुखी और मिलनसार',
        'strengths_en': 'sparkling communication, mental agility, endless playfulness',
        'strengths_hi': 'प्रखर संवाद क्षमता, मानसिक फुर्ती और रचनात्मक सोच',
        'challenge_en': 'restlessness, emotional detachment, scattered focus',
        'challenge_hi': 'अस्थिरता, सतहीपन और भावनात्मक दूरी',
    },
    'Cancer': {
        'hi': 'कर्क',
        'element': 'Water',
        'element_hi': 'जल',
        'quality': 'Cardinal',
        'quality_hi': 'चर',
        'ruler': 'Moon',
        'ruler_hi': 'चंद्र',
        'symbol': 'The Crab',
        'archetype_en': 'Empathetic Nurturer',
        'archetype_hi': 'स्नेही संरक्षक',
        'traits_en': 'intuitive, protective, emotionally profound, devoted',
        'traits_hi': 'संवेदनशील, सहृदय, परिवार-उन्मुख और आत्मीय',
        'strengths_en': 'deep empathetic bonding, domestic warmth, heartfelt devotion',
        'strengths_hi': 'गहन आत्मीयता, पारिवारिक सुरक्षा और निस्वार्थ स्नेह',
        'challenge_en': 'moodiness, protective withdrawal into shell, over-sensitivity',
        'challenge_hi': 'मनोदशा में तीव्र उतार-चढ़ाव, अति-संवेदनशीलता और अलगाव',
    },
    'Leo': {
        'hi': 'सिंह',
        'element': 'Fire',
        'element_hi': 'अग्नि',
        'quality': 'Fixed',
        'quality_hi': 'स्थिर',
        'ruler': 'Sun',
        'ruler_hi': 'सूर्य',
        'symbol': 'The Lion',
        'archetype_en': 'Magnanimous Sovereign',
        'archetype_hi': 'तेजस्वी व उदार नायक',
        'traits_en': 'charismatic, generous, warm-hearted, fiercely proud',
        'traits_hi': 'प्रभावशाली, उदार, निष्ठावान और आत्मविश्वासी',
        'strengths_en': 'radiant warmth, heroic loyalty, joyful generosity',
        'strengths_hi': 'आकर्षक व्यक्तित्व, उदार हृदय और अटूट निष्ठा',
        'challenge_en': 'ego sensitivity, craving for admiration, dramatic flare',
        'challenge_hi': 'अहंकार, निरंतर प्रशंसा की चाह और प्रभुत्व जमाने की प्रवृत्ति',
    },
    'Virgo': {
        'hi': 'कन्या',
        'element': 'Earth',
        'element_hi': 'पृथ्वी',
        'quality': 'Mutable',
        'quality_hi': 'द्विस्वभाव',
        'ruler': 'Mercury',
        'ruler_hi': 'बुध',
        'symbol': 'The Maiden',
        'archetype_en': 'Refined Craftsman',
        'archetype_hi': 'कुशल विश्लेषक व साधक',
        'traits_en': 'analytical, thoughtful, supportive, detail-oriented',
        'traits_hi': 'विवेकशील, व्यवस्थित, विचारशील और सेवाभावी',
        'strengths_en': 'practical devotion, thoughtful attentiveness, quiet dependability',
        'strengths_hi': 'व्यावहारिक सहयोग, सूक्ष्म देखरेख और कार्यकुशलता',
        'challenge_en': 'perfectionism, overthinking, unspoken critique',
        'challenge_hi': 'अति-आलोचनात्मक रवैया, अत्यधिक चिंता और परफेक्शनिज्म',
    },
    'Libra': {
        'hi': 'तुला',
        'element': 'Air',
        'element_hi': 'वायु',
        'quality': 'Cardinal',
        'quality_hi': 'चर',
        'ruler': 'Venus',
        'ruler_hi': 'शुक्र',
        'symbol': 'The Scales',
        'archetype_en': 'Gracious Diplomat',
        'archetype_hi': 'न्यायप्रिय सामंजस्यकर्ता',
        'traits_en': 'diplomatic, charming, aesthetic, peace-seeking',
        'traits_hi': 'संतुलित, सौम्य, न्यायप्रिय और सुरुचिपूर्ण',
        'strengths_en': 'masterful peacemaking, romantic elegance, partner-first focus',
        'strengths_hi': 'सुलह कराने की कला, सौंदर्यबोध और सामंजस्य की प्रबल चाह',
        'challenge_en': 'indecision, conflict-avoidance, superficial harmony',
        'challenge_hi': 'अनिर्णय, विवादों से बचने का प्रयास और बातों को टालना',
    },
    'Scorpio': {
        'hi': 'वृश्चिक',
        'element': 'Water',
        'element_hi': 'जल',
        'quality': 'Fixed',
        'quality_hi': 'स्थिर',
        'ruler': 'Mars',  # Vedic traditional ruler
        'ruler_hi': 'मंगल',
        'symbol': 'The Scorpion',
        'archetype_en': 'Passionate Transformer',
        'archetype_hi': 'गहन व निष्ठावान साधक',
        'traits_en': 'magnetic, deeply intuitive, intensely loyal, resolute',
        'traits_hi': 'रहस्यमयी, दृढ़निश्चयी, गहरे निष्ठावान और संवेदनशील',
        'strengths_en': 'unbreakable devotion, psychic intuition, transformative depth',
        'strengths_hi': 'अखंड निष्ठा, आंतरिक शक्ति और आत्मिक समर्पण',
        'challenge_en': 'jealousy, secretiveness, struggle with emotional surrender',
        'challenge_hi': 'ईर्ष्या, अत्यधिक गोपनीयता और नियंत्रण रखने की प्रवृत्ति',
    },
    'Sagittarius': {
        'hi': 'धनु',
        'element': 'Fire',
        'element_hi': 'अग्नि',
        'quality': 'Mutable',
        'quality_hi': 'द्विस्वभाव',
        'ruler': 'Jupiter',
        'ruler_hi': 'बृहस्पति',
        'symbol': 'The Archer',
        'archetype_en': 'Free Philosopher',
        'archetype_hi': 'स्वतंत्र व दार्शनिक पथिक',
        'traits_en': 'optimistic, candid, adventurous, wisdom-seeking',
        'traits_hi': 'आशावादी, स्पष्टवादी, जिज्ञासु और स्वतंत्रचेता',
        'strengths_en': 'infectious optimism, wide-horizon vision, philosophical truth',
        'strengths_hi': 'उत्साहवर्धक आशावाद, व्यापक दृष्टिकोण और सच्चाई के प्रति निष्ठा',
        'challenge_en': 'restlessness, bluntness, reluctance to settle down',
        'challenge_hi': 'अधीरता, मुँहफट बोलना और बंधनों में घबराहट',
    },
    'Capricorn': {
        'hi': 'मकर',
        'element': 'Earth',
        'element_hi': 'पृथ्वी',
        'quality': 'Cardinal',
        'quality_hi': 'चर',
        'ruler': 'Saturn',
        'ruler_hi': 'शनि',
        'symbol': 'The Sea-Goat',
        'archetype_en': 'Master Architect',
        'archetype_hi': 'कर्मठ व अनुशासित निर्माता',
        'traits_en': 'disciplined, ambitious, patient, profoundly reliable',
        'traits_hi': 'अनुशासित, कर्मठ, धैर्यवान और व्यावहारिक',
        'strengths_en': 'generational stability, quiet protective dedication, integrity',
        'strengths_hi': 'दीर्घकालिक सुरक्षा, गंभीर कर्तव्यनिष्ठा और अडिग धैर्य',
        'challenge_en': 'emotional reserve, workaholic focus, fear of vulnerability',
        'challenge_hi': 'भावनात्मक रूखापन, काम में अति-व्यस्तता और संकोच',
    },
    'Aquarius': {
        'hi': 'कुंभ',
        'element': 'Air',
        'element_hi': 'वायु',
        'quality': 'Fixed',
        'quality_hi': 'स्थिर',
        'ruler': 'Saturn',  # Vedic traditional ruler
        'ruler_hi': 'शनि',
        'symbol': 'The Water-Bearer',
        'archetype_en': 'Visionary Thinker',
        'archetype_hi': 'क्रांतिकारी व प्रबुद्ध विचारक',
        'traits_en': 'innovative, progressive, independent, egalitarian',
        'traits_hi': 'दूरदर्शी, स्वतंत्र, निष्पक्ष और मौलिक',
        'strengths_en': 'intellectual brilliance, deep friendship foundation, unconditional equality',
        'strengths_hi': 'बौद्धिक स्पष्टता, गहरी मित्रता और समानता की भावना',
        'challenge_en': 'emotional detachment, contrarian streak, aloofness',
        'challenge_hi': 'भावनात्मक दूरी, जिद्दी विचार और अलगाववादी दृष्टिकोण',
    },
    'Pisces': {
        'hi': 'मीन',
        'element': 'Water',
        'element_hi': 'जल',
        'quality': 'Mutable',
        'quality_hi': 'द्विस्वभाव',
        'ruler': 'Jupiter',  # Vedic traditional ruler
        'ruler_hi': 'बृहस्पति',
        'symbol': 'The Fish',
        'archetype_en': 'Mystic Romantic',
        'archetype_hi': 'आत्मीय व स्वप्नदृष्टा प्रेमी',
        'traits_en': 'compassionate, artistic, deeply spiritual, empathetic',
        'traits_hi': 'संवेदनशील, दयालु, कल्पनाशील और आध्यात्मिक',
        'strengths_en': 'soulful understanding, boundless tenderness, creative devotion',
        'strengths_hi': 'आत्मीय समझ, अगाध करुणा और निःस्वार्थ प्रेम',
        'challenge_en': 'idealism vs reality, escapism, loose boundaries',
        'challenge_hi': 'अवास्तविक अपेक्षाएं, वास्तविकता से पलायन और कमजोर सीमाएं',
    }
}

# 10 Elemental Matrix Interactions
ELEMENTAL_DYNAMICS = {
    ('Fire', 'Fire'): {
        'title_en': 'Dynamic & Combustible Flame',
        'title_hi': 'तेजस्वी व ऊर्जावान अग्नि योग',
        'synergy_en': 'shared passion, spontaneous adventure, boundless inspiration, and radiant enthusiasm',
        'synergy_hi': 'साझा उत्साह, साहसिक निर्णय, असीम ऊर्जा और प्रेरणादायक तालमेल',
        'friction_en': 'ego clashes, competitive friction, and fiery impatience during disagreements',
        'friction_hi': 'अहंकार का टकराव, अति-प्रतिस्पर्धा और असहमति के समय अधीरता',
        'advice_en': 'Channel competitive energy into common ambitions and take turns yielding ground during arguments.',
        'advice_hi': 'अपनी प्रतिस्पर्धी ऊर्जा को साझा लक्ष्यों में लगाएं और मतभेद के समय एक-दूसरे के विचारों को धैर्यपूर्वक सुनें।',
    },
    ('Fire', 'Earth'): {
        'title_en': 'Spark Meets Mountain',
        'title_hi': 'अग्नि व पृथ्वी का रचनात्मक संतुलन',
        'synergy_en': 'combining visionary inspiration and courageous action with pragmatic structure and lasting security',
        'synergy_hi': 'साहसिक प्रेरणा और दूरदृष्टि को व्यावहारिक अनुशासन और स्थायी सुरक्षा का साथ',
        'friction_en': 'differences in pacing; Fire craves immediate momentum while Earth requires methodical deliberation',
        'friction_hi': 'गति और दृष्टिकोण का अंतर; जहाँ एक ओर त्वरित परिणाम की चाह है वहीं दूसरी ओर सोच-समझकर कदम उठाने की आदत',
        'advice_en': 'Fire must respect Earth’s thoughtful pacing, while Earth can allow Fire to ignite spontaneous joy.',
        'advice_hi': 'अग्नि को पृथ्वी के धैर्य और अनुशासन का सम्मान करना चाहिए, जबकि पृथ्वी को नए उत्साह का स्वागत करना चाहिए।',
    },
    ('Fire', 'Air'): {
        'title_en': 'Wind Feeding the Flame',
        'title_hi': 'वायु व अग्नि का प्रेरक संगम',
        'synergy_en': 'limitless intellectual stimulation, high sociability, vibrant humor, and infectious optimism',
        'synergy_hi': 'बौद्धिक संवाद, सामाजिक सक्रियता, भरपूर हास्य और सकारात्मक सोच का विस्तार',
        'friction_en': 'burning through emotional stamina quickly, moving on before feelings are fully anchored',
        'friction_hi': 'योजनाओं में निरंतरता की कमी और व्यावहारिक स्तर पर टिकाऊपन बनाए रखने में शिथिलता',
        'advice_en': 'Anchor high-flying concepts into tangible habits to ensure your fiery ideas yield enduring real-world results.',
        'advice_hi': 'अपनी उच्च योजनाओं को धरातल पर उतारने के लिए ठोस नियम बनाएं ताकि संबंध केवल उत्साह तक सीमित न रहे।',
    },
    ('Fire', 'Water'): {
        'title_en': 'Steam and Soulful Alchemy',
        'title_hi': 'अग्नि व जल का भावुक समन्वय',
        'synergy_en': 'deep romantic intensity, magnetic attraction, and transformative emotional passion',
        'synergy_hi': 'गहन आकर्षण, भावनात्मक समर्पण और एक-दूसरे के प्रति तीव्र चुंबकीय खिंचाव',
        'friction_en': 'Fire’s blunt directness can wound Water’s sensitivity, while Water’s emotional tides can dampen Fire’s drive',
        'friction_hi': 'स्पष्टवादिता से कोमल भावनाओं को ठेस पहुँचने का जोखिम और मूडीपन से उत्साह में कमी',
        'advice_en': 'Fire must learn gentle emotional attunement, while Water should express vulnerabilities with direct clarity.',
        'advice_hi': 'संवाद में संवेदनशीलता और कोमलता अपनाएं, तथा अनकही बातों के स्थान पर स्पष्ट व सरल बातचीत को प्राथमिकता दें।',
    },
    ('Earth', 'Earth'): {
        'title_en': 'Bedrock of Stability',
        'title_hi': 'अडिग स्थिरता व विश्वास की नींव',
        'synergy_en': 'unshakable dependability, practical mutual support, shared material goals, and profound domestic peace',
        'synergy_hi': 'स्थायी विश्वास, व्यावहारिक सहयोग, आर्थिक सुरक्षा और शांत घरेलू वातावरण',
        'friction_en': 'settling into predictable routines, emotional reserve, and mutual stubbornness during standoffs',
        'friction_hi': 'दैनिक दिनचर्या में नयापन खो जाना और आपसी हठ के कारण किसी बात पर अड़ जाना',
        'advice_en': 'Intentionally inject spontaneous adventures and verbal declarations of affection into your solid daily foundation.',
        'advice_hi': 'रिश्ते में नवीनता बनाए रखने के लिए समय-समय पर बाहर घूमने जाएं और अपनी भावनाओं को शब्दों में व्यक्त करें।',
    },
    ('Earth', 'Air'): {
        'title_en': 'Architecture of Mind and Matter',
        'title_hi': 'विचार और यथार्थ का सेतु',
        'synergy_en': 'Air formulates innovative visions while Earth constructs the tangible roadmap to materialize them',
        'synergy_hi': 'नवाचार और दूरगामी विचारों को धरातल पर क्रियान्वित करने की संतुलित क्षमता',
        'friction_en': 'Earth feels Air is detached or indecisive; Air feels Earth is overly cautious or set in their ways',
        'friction_hi': 'एक पक्ष को बातें अव्यावहारिक लग सकती हैं, जबकि दूसरे को अत्यधिक नियम और प्रतिबंध भारी लग सकते हैं',
        'advice_en': 'Value the gift of contrast: celebrate mental exploration without demanding that every idea immediately balance the ledger.',
        'advice_hi': 'एक-दूसरे की शैली को स्वीकार करें; विचारों की स्वतंत्रता का सम्मान करते हुए व्यावहारिक सीमाओं को सहज बनाएं।',
    },
    ('Earth', 'Water'): {
        'title_en': 'Fertile Soil & Endless Nurturing',
        'title_hi': 'समृद्ध व आत्मीय स्वाभाविक मेल',
        'synergy_en': 'innate emotional security, deep devotion, natural domestic harmony, and supportive shared grounding',
        'synergy_hi': 'स्वाभाविक भावनात्मक सुरक्षा, पारिवारिक स्नेह, अटूट निष्ठा और सहज सामंजस्य',
        'friction_en': 'risk of mutual comfort becoming insular or overly resistant to outside disruption and growth',
        'friction_hi': 'अत्यधिक संकोच या बाहरी दुनिया से कटकर सिर्फ अपने सीमित दायरे में बंध जाने का खतरा',
        'advice_en': 'Continue inviting fresh social circles and collaborative ventures into your exceptionally comforting home space.',
        'advice_hi': 'अपने सुरक्षित दायरे से बाहर निकलकर नए अनुभव हासिल करें और सामाजिक जीवन को जीवंत बनाए रखें।',
    },
    ('Air', 'Air'): {
        'title_en': 'Synchronized Minds & Endless Wonder',
        'title_hi': 'बौद्धिक समानता व वैचारिक स्वतंत्रता',
        'synergy_en': 'brilliant mental camaraderie, shared ideals, sparkling social ease, and zero possessiveness',
        'synergy_hi': 'समान बौद्धिक स्तर, विचारों का खुला आदान-प्रदान, सामाजिक सहजता और एक-दूसरे को स्वतंत्रता देना',
        'friction_en': 'over-intellectualizing emotional struggles and neglecting vulnerable emotional grounding',
        'friction_hi': 'भावनाओं को केवल तर्क की कसौटी पर कसना और अंतरंग भावनात्मक जुड़ाव से कतराना',
        'advice_en': 'Practice connecting through heart-centered vulnerability and physical presence, not just verbal wit.',
        'advice_hi': 'केवल तार्किक संवाद के बजाय हृदयस्पर्शी आत्मीयता और भावनात्मक उपस्थिति को रिश्ते का आधार बनाएं।',
    },
    ('Air', 'Water'): {
        'title_en': 'Breeze Over the Ocean',
        'title_hi': 'संवेदना और विवेक का संगम',
        'synergy_en': 'Air brings clarity and perspective to Water’s feelings, while Water awakens poetic emotional depth in Air',
        'synergy_hi': 'विचारों की स्पष्टता और भावनात्मक गहराई का सुंदर समन्वय, जो दोनों को परिपक्व बनाता है',
        'friction_en': 'Air rationalizes emotions that Water experiences viscerally; Water seeks emotional immersion that Air finds overwhelming',
        'friction_hi': 'तर्क और भावना का द्वंद्व; जहाँ एक पक्ष विश्लेषण करना चाहता है वहीं दूसरा केवल सहानुभूति और आत्मीयता चाहता है',
        'advice_en': 'Air should offer quiet listening without trying to "fix" feelings; Water should articulate needs without assuming mind-reading.',
        'advice_hi': 'बिना समाधान थोपे ध्यान से एक-दूसरे को सुनें और अपनी अपेक्षाओं को शांत भाव से स्पष्ट शब्दों में कहें।',
    },
    ('Water', 'Water'): {
        'title_en': 'Deep Oceanic Intimacy',
        'title_hi': 'अगाध आत्मीयता व आत्मिक बंधन',
        'synergy_en': 'telepathic emotional resonance, profound empathy, unreserved loyalty, and tender romantic devotion',
        'synergy_hi': 'मौन समझ, असीम करुणा, गहन भावनात्मक सुरक्षा और आत्मिक स्तर पर जुड़ाव',
        'friction_en': 'absorbing each other’s emotional storms, boundary blurring, and brooding when hurt',
        'friction_hi': 'एक-दूसरे के तनाव को अपने ऊपर ले लेना, सीमाओं की कमी और नाराजगी को मन में दबाकर रखना',
        'advice_en': 'Establish healthy emotional boundaries and cultivate grounded outside hobbies to keep your shared sanctuary clear.',
        'advice_hi': 'स्वस्थ व्यक्तिगत सीमाएं बनाएं और आपसी संवाद में स्पष्टता रखें ताकि मन में किसी प्रकार का संशय न पनपे।',
    }
}

def article(word):
    """Return word prefixed with 'a' or 'an'."""
    if word and word[0].upper() in ('A', 'E', 'I', 'O', 'U'):
        return f"an {word}"
    return f"a {word}"

def get_element_dynamic(elem_a, elem_b):
    """Retrieve normalized element dynamic dictionary."""
    if (elem_a, elem_b) in ELEMENTAL_DYNAMICS:
        return ELEMENTAL_DYNAMICS[(elem_a, elem_b)]
    elif (elem_b, elem_a) in ELEMENTAL_DYNAMICS:
        return ELEMENTAL_DYNAMICS[(elem_b, elem_a)]
    return ELEMENTAL_DYNAMICS[('Fire', 'Fire')]

# ==============================================================================
# CONTENT GENERATOR ENGINE
# ==============================================================================

def generate_slug(sign_a, gender_a, sign_b, gender_b):
    sa = sign_a.lower()
    sb = sign_b.lower()
    if gender_a == 'male' and gender_b == 'female':
        return f"{sa}-man-{sb}-woman"
    elif gender_a == 'female' and gender_b == 'male':
        return f"{sa}-woman-{sb}-man"
    else:
        return f"{sa}-{sb}"

def format_score(val):
    if val is None:
        return 7.0
    return float(val)

def generate_fields(row):
    sign_a = row['sign_a']
    gender_a = row['gender_a']
    sign_b = row['sign_b']
    gender_b = row['gender_b']
    lang = row['language']

    data_a = SIGNS[sign_a]
    data_b = SIGNS[sign_b]

    elem_a = data_a['element']
    elem_b = data_b['element']
    elem_a_hi = data_a['element_hi']
    elem_b_hi = data_b['element_hi']

    ruler_a = data_a['ruler']
    ruler_b = data_b['ruler']
    ruler_a_hi = data_a['ruler_hi']
    ruler_b_hi = data_b['ruler_hi']

    sign_a_hi = data_a['hi']
    sign_b_hi = data_b['hi']

    dyn = get_element_dynamic(elem_a, elem_b)

    # Scores
    s_gen = format_score(row.get('score_general'))
    s_love = format_score(row.get('score_love'))
    s_friend = format_score(row.get('score_friendship'))
    s_trust = format_score(row.get('score_trust'))
    s_dep = format_score(row.get('score_dependability'))
    s_long = format_score(row.get('score_longevity'))
    s_marr = format_score(row.get('score_marriage'))
    s_kids = format_score(row.get('score_kids'))
    s_fin = format_score(row.get('score_finance'))

    scores_map = {
        'Love': s_love,
        'Friendship': s_friend,
        'Trust': s_trust,
        'Dependability': s_dep,
        'Longevity': s_long,
        'Marriage': s_marr,
        'Parenting': s_kids,
        'Finance': s_fin
    }
    top_domain = max(scores_map.keys(), key=lambda k: scores_map[k])
    lowest_domain = min(scores_map.keys(), key=lambda k: scores_map[k])

    domain_hi_map = {
        'Love': 'प्रेम व स्नेह',
        'Friendship': 'मित्रता व समझ',
        'Trust': 'पारस्परिक विश्वास',
        'Dependability': 'विश्वसनीयता व सहारा',
        'Longevity': 'दीर्घकालिक स्थायित्व',
        'Marriage': 'वैवाहिक जीवन',
        'Parenting': 'संतान सुख व परवरिश',
        'Finance': 'वित्तीय तालमेल'
    }

    # Slug
    slug = generate_slug(sign_a, gender_a, sign_b, gender_b)

    # Gender titles
    if gender_a == 'male':
        g_title_a_en = 'Man'
        g_title_b_en = 'Woman'
        g_title_a_hi = 'पुरुष'
        g_title_b_hi = 'महिला'
        pronoun_a_en = 'He'
        pronoun_b_en = 'She'
    else:
        g_title_a_en = 'Woman'
        g_title_b_en = 'Man'
        g_title_a_hi = 'महिला'
        g_title_b_hi = 'पुरुष'
        pronoun_a_en = 'She'
        pronoun_b_en = 'He'

    pct = int(round(s_gen * 10))

    if lang == 'en':
        element_pair = f"{elem_a} + {elem_b}"
        
        # Meta Title <= 160 chars (aim 65-75 chars)
        meta_title = f"{sign_a} {g_title_a_en} and {sign_b} {g_title_b_en} Compatibility: Love, Marriage & Trust ({pct}%)"
        if len(meta_title) > 160:
            meta_title = f"{sign_a} {g_title_a_en} & {sign_b} {g_title_b_en} Compatibility ({pct}%)"

        # Meta Description <= 255 chars (aim 145-160 chars)
        meta_desc = (
            f"Explore {sign_a} {g_title_a_en.lower()} and {sign_b} {g_title_b_en.lower()} compatibility ({pct}%). "
            f"Discover how {elem_a} and {elem_b} elements align in love, marriage, trust, emotional intimacy, and long-term harmony."
        )
        if len(meta_desc) > 255:
            meta_desc = meta_desc[:252] + "..."

        # Verdict Summary (1-2 sentences)
        if s_gen >= 7.5:
            if sign_a == sign_b:
                archetype_phrase = f"two {data_a['archetype_en']} spirits"
            else:
                archetype_phrase = f"the {data_a['archetype_en']} with the {data_b['archetype_en']}"
            verdict = (
                f"A deeply vibrant and harmonious pairing uniting {archetype_phrase}. "
                f"Governed by {ruler_a} and {ruler_b}, this {element_pair} connection enjoys natural chemistry, high devotion, and strong long-term staying power."
            )
        elif s_gen >= 6.0:
            verdict = (
                f"A dynamic relationship of growth that flourishes through mutual understanding between {sign_a}'s {data_a['traits_en'].split(',')[0]} spirit and {sign_b}'s {data_b['traits_en'].split(',')[0]} perspective. "
                f"By honoring their distinct styles, they turn natural elemental contrasts into an enduring partnership."
            )
        else:
            verdict = (
                f"A complex union of contrasts that calls for conscious patience and mature communication. "
                f"While their {element_pair} interaction creates friction, bridging their differences fosters profound mutual respect and personal transformation."
            )

        # Key Strength
        key_strength = (
            f"Their strongest resonance lies in {top_domain} ({scores_map[top_domain]}/10). "
            f"Here, {dyn['synergy_en']}. Under the combined guidance of {ruler_a} and {ruler_b}, they bring out each other's finest qualities."
        )

        # Key Challenge
        key_challenge = (
            f"Their primary area of conscious work occurs in {lowest_domain} ({scores_map[lowest_domain]}/10). "
            f"Potential tension arises from {dyn['friction_en']}. {dyn['advice_en']}"
        )

        # FAQ JSON
        faq = [
            {
                "question": f"Are {article(f'{sign_a} {g_title_a_en.lower()}')} and {article(f'{sign_b} {g_title_b_en.lower()}')} compatible in marriage?",
                "answer": (
                    f"Yes, {sign_a} {g_title_a_en.lower()} and {sign_b} {g_title_b_en.lower()} hold a marriage compatibility score of {s_marr}/10. "
                    f"Their union succeeds when {sign_a}'s {data_a['strengths_en'].split(',')[0]} complements {sign_b}'s {data_b['strengths_en'].split(',')[0]}, providing a stable balance of passion and loyalty."
                )
            },
            {
                "question": f"How do {sign_a} and {sign_b} build lasting trust?",
                "answer": (
                    f"With a trust rating of {s_trust}/10, security is established through transparent dialogue and consistent actions. "
                    f"{dyn['advice_en']}"
                )
            },
            {
                "question": f"What is the secret to resolving conflict between {article(f'{sign_a} {g_title_a_en.lower()}')} and {article(f'{sign_b} {g_title_b_en.lower()}')}?",
                "answer": (
                    f"The key is navigating {elem_a} and {elem_b} temperaments. "
                    f"{pronoun_a_en} should appreciate {sign_b}'s natural emotional rhythm, while {pronoun_b_en.lower()} gives {sign_a} the space and respect needed to speak openly without defensiveness."
                )
            }
        ]

        # 9 Core Detailed Text Sections
        general_text = (
            f"The connection between {article(f'{sign_a} {g_title_a_en.lower()}')} and {article(f'{sign_b} {g_title_b_en.lower()}')} is a compelling blend of {elem_a} and {elem_b} forces. "
            f"Representing {archetype_phrase if s_gen >= 7.5 else 'distinct cosmic energies'}, their overall compatibility stands at {s_gen}/10 ({pct}%). "
            f"{pronoun_a_en} enters the relationship with a {data_a['traits_en']} presence, while {pronoun_b_en.lower()} contributes a {data_b['traits_en']} energy. "
            f"When their cosmic currents align, their {dyn['synergy_en']} creates a vibrant environment where both partners feel recognized, celebrated, and inspired to grow."
        )

        love_text = (
            f"In romance ({s_love}/10), this union is infused with genuine passion and romantic discovery. "
            f"Ruled by {ruler_a}, {sign_a} conveys affection through {data_a['strengths_en'].split(',')[0]}, while {sign_b}, influenced by {ruler_b}, expresses love through {data_b['strengths_en'].split(',')[0]}. "
            f"Their romantic chemistry thrives when both partners honor each other's pace, expressing tenderness through meaningful shared moments and unconditional devotion."
        )

        friendship_text = (
            f"As companions ({s_friend}/10), their rapport is built on mutual curiosity and shared activities. "
            f"Whether engaging in energetic debates or embarking on weekend getaways, their connection benefits from {dyn['synergy_en']}. "
            f"They uplift one another, offering a supportive sounding board where new plans and ideas are met with genuine encouragement."
        )

        trust_text = (
            f"Trust and fidelity achieve a score of {s_trust}/10. "
            f"{sign_a} values transparency and authentic intention, whereas {sign_b} thrives when emotional reliability is proven day after day. "
            f"To deepen their bond, they must keep commitments, address uncertainties before they fester, and create a safe emotional sanctuary free from judgment."
        )

        dependability_text = (
            f"Dependability rates at {s_dep}/10 in this partnership. "
            f"During unforeseen life hurdles, {pronoun_a_en.lower()} steps up with {data_a['strengths_en'].split(',')[-1].strip()}, while {pronoun_b_en.lower()} provides {data_b['strengths_en'].split(',')[-1].strip()}. "
            f"By dividing responsibilities according to their natural talents, they establish a secure, well-coordinated team."
        )

        longevity_text = (
            f"The long-term resilience of this couple is rated at {s_long}/10. "
            f"A sustainable bond relies on both individuals continually reinventing their shared goals as seasons change. "
            f"By embracing personal growth and avoiding stagnation, this {element_pair} relationship deepens into an enduring life companionship."
        )

        marriage_text = (
            f"In matrimony ({s_marr}/10), establishing domestic harmony and shared core values is the cornerstone of success. "
            f"A {sign_a} {g_title_a_en.lower()} and {sign_b} {g_title_b_en.lower()} create a thriving home when household choices, family rituals, and conflict resolutions are handled equitably. "
            f"Honoring their elemental differences ensures both partners feel equally cherished and empowered within the marriage."
        )

        kids_text = (
            f"When raising children and cultivating family life ({s_kids}/10), this couple balances warmth with constructive guidance. "
            f"{sign_a} inspires their children with confidence, curiosity, and vitality, while {sign_b} surrounds them with emotional security, practical ethics, and unconditional care. "
            f"Together, they provide a well-rounded and nurturing upbringing."
        )

        finance_text = (
            f"Financial cooperation reaches a rating of {s_fin}/10. "
            f"{sign_a}'s approach to resources balances ambition and opportunity, while {sign_b} emphasizes foresight, stability, and prudent management. "
            f"By maintaining transparent budgets and agreeing on joint investment priorities, they construct a prosperous and stress-free economic future."
        )

    else:
        # Hindi (hi)
        element_pair = f"{elem_a_hi} + {elem_b_hi}"
        
        # Meta Title <= 160 chars
        meta_title = f"{sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} अनुकूलता: प्रेम, विवाह व संबंध ({pct}%)"
        if len(meta_title) > 160:
            meta_title = f"{sign_a_hi} {g_title_a_hi} व {sign_b_hi} {g_title_b_hi} अनुकूलता ({pct}%)"

        # Meta Description <= 255 chars
        meta_desc = (
            f"जानिए {sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} की राशि अनुकूलता ({pct}%)। "
            f"{elem_a_hi} और {elem_b_hi} तत्व का यह योग प्रेम, वैवाहिक जीवन, आपसी विश्वास और रिश्ते के भविष्य पर क्या प्रभाव डालता है, विस्तार से समझें।"
        )
        if len(meta_desc) > 255:
            meta_desc = meta_desc[:252] + "..."

        # Verdict Summary
        if s_gen >= 7.5:
            if sign_a == sign_b:
                archetype_phrase_hi = f"दो समान {data_a['archetype_hi']} ऊर्जाओं"
            else:
                archetype_phrase_hi = f"{data_a['archetype_hi']} और {data_b['archetype_hi']}"
            verdict = (
                f"यह एक अत्यंत सामंजस्यपूर्ण और प्रभावशाली युति है, जहाँ {archetype_phrase_hi} का मिलन होता है। "
                f"{ruler_a_hi} और {ruler_b_hi} के प्रभाव से युक्त यह {element_pair} संबंध आपसी निष्ठा, गहरे आकर्षण और दीर्घकालिक स्थिरता का सुंदर उदाहरण प्रस्तुत करता है।"
            )
        elif s_gen >= 6.0:
            verdict = (
                f"यह संबंध समझदारी, संतुलन और निरंतर परिपक्वता पर आधारित है। "
                f"{sign_a_hi} की {data_a['traits_hi'].split(',')[0]} ऊर्जा और {sign_b_hi} का {data_b['traits_hi'].split(',')[0]} स्वभाव मिलकर एक-दूसरे के जीवन में नया संतुलन और सार्थकता लाते हैं।"
            )
        else:
            verdict = (
                f"यह विपरीत प्रवृत्तियों और भिन्न प्राथमिकताओं वाला संबंध है, जिसमें धैर्य और परिपक्व संवाद की विशेष आवश्यकता होती है। "
                f"यदि दोनों अपने {element_pair} स्वभाव के अंतर को समझकर सामंजस्य बनाएं, तो यह युति दोनों के व्यक्तिगत विकास का मजबूत आधार बन सकती है।"
            )

        # Key Strength
        key_strength = (
            f"इस रिश्ते की सबसे बड़ी मजबूती {domain_hi_map[top_domain]} ({scores_map[top_domain]}/10) में दिखाई देती है। "
            f"यहाँ {dyn['synergy_hi']} देखने को मिलता है। {ruler_a_hi} और {ruler_b_hi} का शुभ प्रभाव दोनों को एक-दूसरे के प्रति समर्पित और सहयोगी बनाए रखता है।"
        )

        # Key Challenge
        key_challenge = (
            f"रिश्ते में सजगता और सुधार का मुख्य क्षेत्र {domain_hi_map[lowest_domain]} ({scores_map[lowest_domain]}/10) है। "
            f"दोनों के बीच {dyn['friction_hi']} की स्थिति उत्पन्न हो सकती है। {dyn['advice_hi']}"
        )

        # FAQ JSON
        faq = [
            {
                "question": f"क्या {sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} का वैवाहिक जीवन सफल रहता है?",
                "answer": (
                    f"हाँ, {sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} के बीच विवाह अनुकूलता {s_marr}/10 है। "
                    f"जब {sign_a_hi} का {data_a['strengths_hi'].split(',')[0]} और {sign_b_hi} का {data_b['strengths_hi'].split(',')[0]} एक साथ आते हैं, तो गृहस्थ जीवन में सुख और स्थायित्व बना रहता है।"
                )
            },
            {
                "question": f"{sign_a_hi} और {sign_b_hi} के बीच विश्वास कैसे मजबूत हो सकता है?",
                "answer": (
                    f"इस जोड़ी में विश्वास का स्तर {s_trust}/10 है। "
                    f"पारदर्शिता, नियमित संवाद और एक-दूसरे की भावनाओं के प्रति सम्मान रखकर वे अपने विश्वास को अटूट बना सकते हैं।"
                )
            },
            {
                "question": f"मतभेद होने पर {sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} को क्या सावधानी बरतनी चाहिए?",
                "answer": (
                    f"विवाद के समय {elem_a_hi} और {elem_b_hi} तत्वों के स्वभाव का ध्यान रखें। "
                    f"किसी भी बात को मन में दबाकर रखने के बजाय शांत मन से आमने-सामने बैठकर चर्चा करें और एक-दूसरे के दृष्टिकोण को आदर दें।"
                )
            }
        ]

        # 9 Core Detailed Text Sections
        general_text = (
            f"{sign_a_hi} {g_title_a_hi} और {sign_b_hi} {g_title_b_hi} का संबंध {elem_a_hi} तथा {elem_b_hi} तत्वों का एक अनूठा संगम है। "
            f"इनका समग्र अनुकूलता स्कोर {s_gen}/10 ({pct}%) है। {sign_a_hi} {data_a['traits_hi']} व्यक्तित्व के साथ आगे बढ़ते हैं, जबकि {sign_b_hi} {data_b['traits_hi']} गुणों से रिश्ते को संवारती हैं। "
            f"जब दोनों के विचार मिलते हैं, तो {dyn['synergy_hi']} से युक्त यह साझेदारी जीवन में नई उमंग और सकारात्मक ऊर्जा का संचार करती है।"
        )

        love_text = (
            f"प्रेम और आकर्षण के संदर्भ में यह युति {s_love}/10 का सशक्त स्कोर रखती है। "
            f"{ruler_a_hi} के प्रभाव से {sign_a_hi} अपने स्नेह को {data_a['strengths_hi'].split(',')[0]} के रूप में दर्शाते हैं, जबकि {ruler_b_hi} से प्रभावित {sign_b_hi} {data_b['strengths_hi'].split(',')[0]} के माध्यम से अपनी भावनाएं व्यक्त करती हैं। "
            f"एक-दूसरे की भावनात्मक ज़रूरतों का आदर करने से इनके बीच का प्रेम समय के साथ और अधिक प्रगाढ़ होता जाता है।"
        )

        friendship_text = (
            f"मित्रता और सामाजिक स्तर पर यह जोड़ी {s_friend}/10 की अनुकूलता दर्शाती है। "
            f"दोनों साथ मिलकर सार्थक चर्चाओं और रुचिकर गतिविधियों का आनंद लेते हैं। "
            f"इनके बीच {dyn['synergy_hi']} का सहज प्रवाह रहता है, जिससे दोनों एक-दूसरे के सुख-दुख में सच्चे मित्र और मार्गदर्शक सिद्ध होते हैं।"
        )

        trust_text = (
            f"पारस्परिक विश्वास और निष्ठा का स्तर {s_trust}/10 है। "
            f"{sign_a_hi} सच्चाई और सीधेपन को महत्व देते हैं, जबकि {sign_b_hi} रिश्ते में भावनात्मक सुरक्षा और निरंतरता चाहती हैं। "
            f"अपने वचनों का पालन करना और किसी भी शंका को तुरंत स्पष्ट बातचीत से सुलझाना इस रिश्ते को अटूट बनाता है।"
        )

        dependability_text = (
            f"कठिन परिस्थितियों में एक-दूसरे का संबल बनने की क्षमता {s_dep}/10 है। "
            f"चुनौतियों के समय {sign_a_hi} {data_a['strengths_hi'].split(',')[-1].strip()} का परिचय देते हैं, वहीं {sign_b_hi} {data_b['strengths_hi'].split(',')[-1].strip()} से स्थिति को संभालती हैं। "
            f"दोनों मिलकर किसी भी पारिवारिक या व्यक्तिगत संकट का डटकर सामना करने में सक्षम हैं।"
        )

        longevity_text = (
            f"रिश्ते का दीर्घकालिक स्थायित्व {s_long}/10 पर आंका गया है। "
            f"इस बंधन को दशकों तक जीवंत रखने के लिए दोनों को समय के साथ एक-दूसरे की बदलती प्राथमिकताओं को अपनाना होगा। "
            f"परस्पर सम्मान और साझा लक्ष्यों के प्रति निष्ठा इस {element_pair} संबंध को उम्र भर का अटूट साथ बनाती है।"
        )

        marriage_text = (
            f"वैवाहिक जीवन में अनुकूलता का स्कोर {s_marr}/10 है। "
            f"एक सुखी गृहस्थी के निर्माण के लिए दोनों को पारिवारिक दायित्वों और घरेलू निर्णयों में संतुलन बनाए रखना आवश्यक है। "
            f"जब दोनों साथी एक-दूसरे के आत्मसम्मान और अधिकारों का सम्मान करते हैं, तो दांपत्य जीवन में सुख, शांति और समृद्धि का वास होता है।"
        )

        kids_text = (
            f"संतान सुख और पारिवारिक परवरिश के क्षेत्र में इनका तालमेल {s_kids}/10 है। "
            f"{sign_a_hi} बच्चों में आत्मविश्वास, साहस और अनुशासन का संचार करते हैं, जबकि {sign_b_hi} उन्हें स्नेह, संस्कार और भावनात्मक सुरक्षा प्रदान करती हैं। "
            f"माता-पिता के रूप में इनका संयुक्त दृष्टिकोण बच्चों के सर्वांगीण विकास के लिए अत्यंत लाभकारी सिद्ध होता है।"
        )

        finance_text = (
            f"आर्थिक प्रबंधन और धन संचय में दोनों का तालमेल {s_fin}/10 है। "
            f"{sign_a_hi} नए अवसरों और प्रगतिशील योजनाओं में रुचि रखते हैं, जबकि {sign_b_hi} बचत, स्थिरता और भविष्य की सुरक्षा पर बल देती हैं। "
            f"साझा बजट और पारदर्शी वित्तीय निर्णयों से दोनों अपने परिवार के लिए एक सुदृढ़ आर्थिक आधार तैयार करते हैं।"
        )

    faq_json_str = json.dumps(faq, ensure_ascii=False)

    return {
        'id': row['id'],
        'slug': slug,
        'element_pair': element_pair,
        'meta_title': meta_title,
        'meta_description': meta_desc,
        'verdict_summary': verdict,
        'key_strength': key_strength,
        'key_challenge': key_challenge,
        'faq_json': faq_json_str,
        'general_text': general_text,
        'love_text': love_text,
        'friendship_text': friendship_text,
        'trust_text': trust_text,
        'dependability_text': dependability_text,
        'longevity_text': longevity_text,
        'marriage_text': marriage_text,
        'kids_text': kids_text,
        'finance_text': finance_text,
    }

# ==============================================================================
# MAIN RUNNER & CLI
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(description="Generate & populate zodiac_compatibility table.")
    parser.add_argument('--host', default='localhost', help='MySQL Host')
    parser.add_argument('--user', default='root', help='MySQL User')
    parser.add_argument('--password', default='', help='MySQL Password')
    parser.add_argument('--database', default='merarashifal', help='Database name')
    parser.add_argument('--dry-run', action='store_true', help='Perform in-memory test and print stats (default if no flags)')
    parser.add_argument('--write-files', action='store_true', help='Write SQL and preview JSON files')
    parser.add_argument('--apply-db', action='store_true', help='Directly execute updates in MySQL database')

    args = parser.parse_args()

    # If neither write-files nor apply-db is specified, default to dry-run
    if not args.write_files and not args.apply_db:
        args.dry_run = True

    print("=" * 70)
    print("MeraRashifal - Zodiac Compatibility Engine")
    print(f"Connecting to database '{args.database}' on {args.host}...")
    print("=" * 70)

    conn = pymysql.connect(
        host=args.host,
        user=args.user,
        password=args.password,
        database=args.database,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

    try:
        with conn.cursor() as cur:
            cur.execute("""
                SELECT id, sign_a, gender_a, sign_b, gender_b, language,
                       score_general, score_love, score_friendship, score_trust,
                       score_dependability, score_longevity, score_marriage,
                       score_kids, score_finance
                FROM zodiac_compatibility
                ORDER BY id ASC
            """)
            rows = cur.fetchall()

        total_rows = len(rows)
        print(f"Loaded {total_rows} rows from 'zodiac_compatibility'.")

        processed = []
        preview_samples = []
        
        # Validation accumulators
        max_title_len = 0
        max_desc_len = 0
        json_errors = 0

        for r in rows:
            gen_data = generate_fields(r)
            processed.append(gen_data)

            # Validate lengths
            t_len = len(gen_data['meta_title'])
            d_len = len(gen_data['meta_description'])
            if t_len > max_title_len:
                max_title_len = t_len
            if d_len > max_desc_len:
                max_desc_len = d_len

            # Validate FAQ JSON
            try:
                parsed_faq = json.loads(gen_data['faq_json'])
                if not isinstance(parsed_faq, list) or len(parsed_faq) != 3:
                    json_errors += 1
            except Exception:
                json_errors += 1

            # Select sample for preview
            if r['sign_a'] == 'Aries' and r['sign_b'] == 'Leo':
                preview_samples.append({
                    'id': r['id'],
                    'sign_a': r['sign_a'],
                    'gender_a': r['gender_a'],
                    'sign_b': r['sign_b'],
                    'gender_b': r['gender_b'],
                    'language': r['language'],
                    'generated': gen_data
                })

        print("\n--- Validation Statistics ---")
        print(f"Total Rows Processed: {len(processed)}")
        print(f"Max meta_title length: {max_title_len} chars (limit: 160)")
        print(f"Max meta_description length: {max_desc_len} chars (limit: 255)")
        print(f"FAQ JSON Validation Errors: {json_errors}")

        # Show Sample Previews
        print("\n" + "=" * 70)
        print("SAMPLE OUTPUT PREVIEW: Aries + Leo")
        print("=" * 70)
        for s in preview_samples:
            g = s['generated']
            print(f"\n>>> ID {s['id']}: {s['sign_a']} ({s['gender_a']}) + {s['sign_b']} ({s['gender_b']}) [{s['language']}]")
            print(f"Slug:             {g['slug']}")
            print(f"Element Pair:     {g['element_pair']}")
            print(f"Meta Title:       {g['meta_title']}")
            print(f"Meta Description: {g['meta_description']}")
            print(f"Verdict Summary:  {g['verdict_summary']}")
            print(f"Key Strength:     {g['key_strength']}")
            print(f"Key Challenge:    {g['key_challenge']}")
            print(f"FAQ Count:        {len(json.loads(g['faq_json']))} questions")
            print(f"General Text:     {g['general_text'][:110]}...")
            print(f"Love Text:        {g['love_text'][:110]}...")
            print(f"Trust Text:       {g['trust_text'][:110]}...")

        # If --write-files
        if args.write_files:
            output_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'output')
            os.makedirs(output_dir, exist_ok=True)

            sql_path = os.path.join(output_dir, 'populate_zodiac_compatibility.sql')
            preview_path = os.path.join(output_dir, 'compatibility_preview.json')

            print(f"\nWriting SQL file to: {sql_path}...")
            with open(sql_path, 'w', encoding='utf-8') as f:
                f.write("-- MeraRashifal Zodiac Compatibility Update Script\n")
                f.write("-- Generated automatically with rich SEO, GEO, and authentic content\n\n")
                f.write("START TRANSACTION;\n\n")
                for item in processed:
                    # Escape strings for SQL
                    def esc(val):
                        if val is None:
                            return "NULL"
                        return "'" + str(val).replace("\\", "\\\\").replace("'", "''") + "'"

                    stmt = (
                        f"UPDATE `zodiac_compatibility` SET "
                        f"`slug` = {esc(item['slug'])}, "
                        f"`element_pair` = {esc(item['element_pair'])}, "
                        f"`meta_title` = {esc(item['meta_title'])}, "
                        f"`meta_description` = {esc(item['meta_description'])}, "
                        f"`verdict_summary` = {esc(item['verdict_summary'])}, "
                        f"`key_strength` = {esc(item['key_strength'])}, "
                        f"`key_challenge` = {esc(item['key_challenge'])}, "
                        f"`faq_json` = {esc(item['faq_json'])}, "
                        f"`general_text` = {esc(item['general_text'])}, "
                        f"`love_text` = {esc(item['love_text'])}, "
                        f"`friendship_text` = {esc(item['friendship_text'])}, "
                        f"`trust_text` = {esc(item['trust_text'])}, "
                        f"`dependability_text` = {esc(item['dependability_text'])}, "
                        f"`longevity_text` = {esc(item['longevity_text'])}, "
                        f"`marriage_text` = {esc(item['marriage_text'])}, "
                        f"`kids_text` = {esc(item['kids_text'])}, "
                        f"`finance_text` = {esc(item['finance_text'])} "
                        f"WHERE `id` = {item['id']};\n"
                    )
                    f.write(stmt)
                f.write("\nCOMMIT;\n")

            print(f"SQL update file written ({os.path.getsize(sql_path)} bytes).")

            print(f"Writing preview JSON to: {preview_path}...")
            # Include 12 diverse sample pairs across different elements in preview
            preview_items = []
            sample_combos = [
                ('Aries', 'Leo'),          # Fire + Fire
                ('Taurus', 'Virgo'),       # Earth + Earth
                ('Gemini', 'Libra'),       # Air + Air
                ('Cancer', 'Scorpio'),     # Water + Water
                ('Aries', 'Taurus'),       # Fire + Earth
                ('Leo', 'Gemini'),         # Fire + Air
                ('Sagittarius', 'Pisces'), # Fire + Water
                ('Virgo', 'Cancer'),       # Earth + Water
                ('Capricorn', 'Aquarius'), # Earth + Air
                ('Libra', 'Pisces')        # Air + Water
            ]
            for r in rows:
                if (r['sign_a'], r['sign_b']) in sample_combos and r['gender_a'] == 'male':
                    matching_gen = next(p for p in processed if p['id'] == r['id'])
                    preview_items.append({
                        'id': r['id'],
                        'sign_a': r['sign_a'],
                        'sign_b': r['sign_b'],
                        'gender_a': r['gender_a'],
                        'gender_b': r['gender_b'],
                        'language': r['language'],
                        'scores': {
                            'general': float(r['score_general']),
                            'love': float(r['score_love']),
                            'marriage': float(r['score_marriage'])
                        },
                        'fields': matching_gen
                    })

            with open(preview_path, 'w', encoding='utf-8') as f:
                json.dump(preview_items, f, ensure_ascii=False, indent=2)
            print(f"Preview JSON written ({len(preview_items)} sample items).")

        # If --apply-db
        if args.apply_db:
            print("\nApplying updates directly to MariaDB database...")
            update_query = """
                UPDATE `zodiac_compatibility` SET
                    `slug` = %(slug)s,
                    `element_pair` = %(element_pair)s,
                    `meta_title` = %(meta_title)s,
                    `meta_description` = %(meta_description)s,
                    `verdict_summary` = %(verdict_summary)s,
                    `key_strength` = %(key_strength)s,
                    `key_challenge` = %(key_challenge)s,
                    `faq_json` = %(faq_json)s,
                    `general_text` = %(general_text)s,
                    `love_text` = %(love_text)s,
                    `friendship_text` = %(friendship_text)s,
                    `trust_text` = %(trust_text)s,
                    `dependability_text` = %(dependability_text)s,
                    `longevity_text` = %(longevity_text)s,
                    `marriage_text` = %(marriage_text)s,
                    `kids_text` = %(kids_text)s,
                    `finance_text` = %(finance_text)s
                WHERE `id` = %(id)s
            """
            with conn.cursor() as cur:
                cur.executemany(update_query, processed)
                conn.commit()
            print(f"Successfully updated all {len(processed)} rows in `zodiac_compatibility`!")

        print("\nOperation completed successfully.")

    finally:
        conn.close()

if __name__ == '__main__':
    main()
