<?php
/**
 * Niyati Smart Auto-Linker Engine
 * Scans AI chat responses and links relevant MeraRashifal tools, calculators, categories, and tags.
 * 
 * Strict Anti-Spam Guardrails:
 * 1. Each URL is linked AT MOST ONCE per response.
 * 2. Maximum 2 distinct contextual links per message total (keeps responses clean and natural).
 * 3. Natural reading order: links are introduced progressively as the user reads.
 * 4. Longest-phrase matching: Prevents partial/fragmented sub-word collisions (e.g. 'Lord Ganesha' before 'Ganesh').
 * 5. Safe regex boundaries: Never links inside existing HTML tags or Markdown links [text](url).
 * 6. Canonical tool & taxonomy URLs: Matches exact MeraRashifal tools and Category/Tag archives.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Linker {

    const MAX_LINKS_PER_MESSAGE = 2; // Strict anti-spam cap
    const TAXONOMY_CACHE_KEY    = 'niyati_taxonomy_link_cache';
    const TAXONOMY_CACHE_TTL    = 604800; // 7 days cache

    /**
     * Clear cached taxonomy link map
     */
    public static function clear_cache() {
        delete_transient(self::TAXONOMY_CACHE_KEY);
    }

    /**
     * Get Master Link Map (Tools + Taxonomies)
     */
    public static function get_link_dictionary() {
        $tools = self::get_tool_dictionary();
        $taxonomies = self::get_taxonomy_links();
        return array_merge($tools, $taxonomies);
    }

    /**
     * Core Astrological Calculators & Tools Dictionary
     */
    public static function get_tool_dictionary() {
        $base_url = rtrim(home_url(), '/');

        return [
            // 1. Kaal Sarp Dosh Tool
            [
                'url'      => $base_url . '/dosh/kaal-sarp-dosh/',
                'title'    => 'Kaal Sarp Dosh Check',
                'keywords' => [
                    'कालसर्प दोष', 'काल सर्प दोष', 'Kaal Sarp Dosh', 'Kaalsarp Dosh', 'Kaal Sarp'
                ]
            ],

            // 2. Mangal Dosh Tool
            [
                'url'      => $base_url . '/dosh/mangal-dosh/',
                'title'    => 'Mangal Dosh Check',
                'keywords' => [
                    'मांगलिक दोष', 'मंगल दोष', 'Manglik Dosh', 'Mangal Dosh', 'मांगलिक', 'Manglik'
                ]
            ],

            // 3. Pitra Dosh Tool
            [
                'url'      => $base_url . '/dosh/pitra-dosh/',
                'title'    => 'Pitra Dosh Check',
                'keywords' => [
                    'पितृ दोष', 'Pitra Dosh', 'Pitru Dosh'
                ]
            ],

            // 4. General Kundali Dosh Check
            [
                'url'      => $base_url . '/dosh/',
                'title'    => 'Kundali Dosh Calculator',
                'keywords' => [
                    'कुंडली दोष', 'Kundli Dosh', 'Kundali Dosh', 'दोष निवारण'
                ]
            ],

            // 5. Current Mahadasha & Timing Report
            [
                'url'      => $base_url . '/reports/dasha-timing-report/',
                'title'    => 'Dasha Timing & Predictions Report',
                'keywords' => [
                    'वर्तमान महादशा', 'Current Mahadasha', 'महादशा फल', 'महादशा',
                    'Mahadasha', 'Maha Dasha', 'Mahadashas'
                ]
            ],

            // 6. Antardasha Tool
            [
                'url'      => $base_url . '/dasha/',
                'title'    => 'Antardasha Predictions',
                'keywords' => [
                    'अंतर्दशा', 'Antardasha', 'Antar Dasha', 'Antardashas'
                ]
            ],

            // 7. Overall Dasha & Vimshottari
            [
                'url'      => $base_url . '/dasha/',
                'title'    => 'Kundali Dasha & Timing',
                'keywords' => [
                    'विंशोत्तरी दशा', 'Vimshottari Dasha', 'Vimshottari', 'विंशोत्तरी महादशा',
                    'Vimshottari Mahadasha', 'कुंडली दशा', 'Kundli Dasha', 'Kundali Dasha', 'Dasha timing'
                ]
            ],

            // 8. Kundli Matchmaking (Gun Milan)
            [
                'url'      => $base_url . '/matchmaking-compatibility-check/',
                'title'    => 'Kundli Matchmaking (Gun Milan)',
                'keywords' => [
                    'कुंडली मिलान', 'Kundli Matching', 'Kundali Milan', 'Kundali Matching',
                    'गुण मिलान', 'Gun Milan', 'Guna Milan', '36 Guna',
                    'अष्टकूट मिलान', 'Ashtakoot Milan', 'विवाह मिलान'
                ]
            ],

            // 9. Aaj Ka Panchang
            [
                'url'      => $base_url . '/panchang/',
                'title'    => 'Aaj Ka Panchang',
                'keywords' => [
                    'आज का पंचांग', 'Aaj Ka Panchang', 'दैनिक पंचांग',
                    'पंचांग', 'Panchang', 'Hindu Panchang'
                ]
            ],

            // 10. Shubh Muhurat Dates
            [
                'url'      => $base_url . '/muhurat-dates/',
                'title'    => 'Shubh Muhurat Dates',
                'keywords' => [
                    'शुभ मुहूर्त', 'Shubh Muhurat', 'Muhurat Dates', 'मुहूर्त तिथियां',
                    'विवाह मुहूर्त', 'Vivah Muhurat', 'Marriage Muhurat',
                    'गृह प्रवेश मुहूर्त', 'Griha Pravesh', 'गृह प्रवेश'
                ]
            ],

            // 11. Daily Choghadiya
            [
                'url'      => $base_url . '/daily-choghadiya-muhurat/',
                'title'    => 'Daily Choghadiya Muhurat',
                'keywords' => [
                    'आज का चौघड़िया', 'Aaj Ka Choghadiya', 'चौघड़िया मुहूर्त',
                    'Choghadiya Muhurat', 'चौघड़िया'
                ]
            ],

            // 12. Astrological Remedies (Upay)
            [
                'url'      => $base_url . '/remedies-astrological-emotional-sociological-solutions/',
                'title'    => 'Astrological Remedies',
                'keywords' => [
                    'ज्योतिषीय उपाय', 'Astrological Remedies', 'Lal Kitab Upay',
                    'ग्रह शांति उपाय', 'ग्रह शांति', 'वैदिक उपाय'
                ]
            ],

            // 13. Palm Reading (Hastrekha)
            [
                'url'      => $base_url . '/palm-reading/',
                'title'    => 'Palm Reading (Hastrekha)',
                'keywords' => [
                    'हस्तरेखा ज्ञान', 'हस्तरेखा विचार', 'हस्तरेखा',
                    'Palm Reading', 'Palmistry', 'हाथ की रेखाएं'
                ]
            ],

            // 14. Yearly Horoscope (Varshaphal)
            [
                'url'      => $base_url . '/yearly-horoscope-prediction/',
                'title'    => 'Yearly Horoscope Prediction',
                'keywords' => [
                    'वार्षिक राशिफल', 'Yearly Horoscope', 'Varshaphal',
                    'वर्षफल', '2026 राशिफल', '2027 राशिफल'
                ]
            ],

            // 15. Daily Horoscope (Rashifal)
            [
                'url'      => $base_url . '/horoscope/',
                'title'    => 'Daily Horoscope (Aaj Ka Rashifal)',
                'keywords' => [
                    'आज का राशिफल', 'Aaj Ka Rashifal', 'दैनिक राशिफल',
                    'Daily Horoscope', 'Daily Rashifal'
                ]
            ],

            // 16. Sun Sign Compatibility
            [
                'url'      => $base_url . '/compatibility-check/',
                'title'    => 'Sun Sign Compatibility',
                'keywords' => [
                    'राशि अनुकूलता', 'Sun Sign Compatibility',
                    'Zodiac Compatibility', 'Love Compatibility'
                ]
            ],

            // 17. Name Suitability (Naamkaran)
            [
                'url'      => $base_url . '/name-suitability/',
                'title'    => 'Name Suitability',
                'keywords' => [
                    'नाम अनुकूलता', 'Name Suitability', 'नाम विचार',
                    'नामकरण संस्कार', 'Namkaran'
                ]
            ],

            // 18. Baby Names Finder
            [
                'url'      => $base_url . '/baby-names/',
                'title'    => 'Baby Names',
                'keywords' => [
                    'बच्चों के नाम', 'Baby Names', 'Hindu Baby Names', 'शिशु नामकरण'
                ]
            ],

            // 19. Hindu Festivals Calendar
            [
                'url'      => $base_url . '/festivals/',
                'title'    => 'Hindu Festivals Calendar',
                'keywords' => [
                    'त्योहार कैलेंडर', 'Hindu Festivals', 'त्यौहार एवं व्रत',
                    'Vrat Calendar', 'व्रत कैलेंडर'
                ]
            ],

            // 20. Janam Kundali Details
            [
                'url'      => $base_url . '/kundali-details/',
                'title'    => 'Janam Kundali Details',
                'keywords' => [
                    'जन्म कुंडली विवरण', 'Janam Kundli', 'जन्म कुंडली', 'Kundali Details'
                ]
            ]
        ];
    }

    /**
     * Taxonomy Links (Categories and Post Tags)
     * Automatically resolves WordPress categories & tags with multi-lingual synonyms.
     */
    public static function get_taxonomy_links() {
        $cached = get_transient(self::TAXONOMY_CACHE_KEY);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $base_url = rtrim(home_url(), '/');
        $taxonomy_links = [];

        // 1. Curated Deity, Gods, Goddesses, Vrats & Festivals Taxonomy Definitions
        $curated_taxonomies = [
            'ganesh' => [
                'title'              => 'Lord Ganesha Worship & Remedies',
                'preferred_category' => 'ganesh-ji',
                'fallback_tag'       => 'ganesh-ji',
                'default_path'       => '/category/hindu-god/ganesh-ji/',
                'keywords'           => [
                    'Lord Ganesha', 'Lord Ganesh', 'Ganesh Ji', 'Ganesha', 'Ganpati Bappa', 'Ganpati',
                    'गणेश जी', 'भगवान गणेश', 'श्री गणेश', 'गणपति बप्पा', 'गणपति', 'विघ्नहर्ता'
                ]
            ],
            'shiv' => [
                'title'              => 'Lord Shiva Worship & Remedies',
                'preferred_category' => 'shiv-ji',
                'fallback_tag'       => 'shiv-ji',
                'default_path'       => '/category/hindu-god/shiv-ji/',
                'keywords'           => [
                    'Lord Shiva', 'Shiv Ji', 'Mahadev', 'Bholenath', 'Shankar Ji', 'Lord Shankar', 'Shiva Lingam',
                    'भगवान शिव', 'शिव जी', 'महादेव', 'भोलेनाथ', 'शंकर जी', 'शिवलिंग', 'महामृत्युंजय मंत्र'
                ]
            ],
            'vishnu' => [
                'title'              => 'Lord Vishnu Worship & Remedies',
                'preferred_category' => 'vishnu-ji',
                'fallback_tag'       => 'vishnu-ji',
                'default_path'       => '/category/hindu-god/vishnu-ji/',
                'keywords'           => [
                    'Lord Vishnu', 'Vishnu Ji', 'Shree Hari', 'Narayan', 'Bhagwan Vishnu',
                    'भगवान विष्णु', 'विष्णु जी', 'श्री हरि', 'नारायण', 'श्रीहरि'
                ]
            ],
            'laxmi' => [
                'title'              => 'Maa Lakshmi Worship & Wealth Remedies',
                'preferred_category' => 'laxmi-ji',
                'fallback_tag'       => 'laxmi-ji',
                'default_path'       => '/category/hindu-goddess/laxmi-ji/',
                'keywords'           => [
                    'Maa Lakshmi', 'Laxmi Mata', 'Lakshmi Ji', 'Laxmi Ji', 'Goddess Lakshmi', 'Dhanlaxmi',
                    'मां लक्ष्मी', 'देवी लक्ष्मी', 'लक्ष्मी जी', 'महालक्ष्मी', 'धनलक्ष्मी'
                ]
            ],
            'hanuman' => [
                'title'              => 'Hanuman Ji Worship & Remedies',
                'preferred_category' => 'hanuman-ji',
                'fallback_tag'       => 'hanuman-ji',
                'default_path'       => '/category/hindu-god/hanuman-ji/',
                'keywords'           => [
                    'Hanuman Ji', 'Lord Hanuman', 'Bajrangbali', 'Sankat Mochan', 'Shree Hanuman',
                    'हनुमान जी', 'बजरंगबली', 'संकटमोचन', 'हनुमान चालीसा', 'पवनपुत्र'
                ]
            ],
            'surya' => [
                'title'              => 'Surya Dev Worship & Remedies',
                'preferred_category' => 'surya-dev',
                'fallback_tag'       => 'surya-dev',
                'default_path'       => '/category/hindu-god/surya-dev/',
                'keywords'           => [
                    'Surya Dev', 'Bhagwan Surya', 'Sun God', 'Surya Bhagwan',
                    'सूर्य देव', 'भगवान सूर्य', 'सूर्य भगवान', 'आदित्य देव'
                ]
            ],
            'shani' => [
                'title'              => 'Shani Dev Worship & Remedies',
                'preferred_category' => 'shani-dev',
                'fallback_tag'       => 'shani-dev',
                'default_path'       => '/category/hindu-god/shani-dev/',
                'keywords'           => [
                    'Shani Dev', 'Lord Shani', 'Shani Bhagwan', 'Shani Sade Sati', 'Shani Dhaiya',
                    'शनि देव', 'शनि भगवान', 'शनि की साढ़ेसाती', 'साढ़ेसाती', 'शनि ढैय्या'
                ]
            ],
            'durga' => [
                'title'              => 'Maa Durga Worship & Remedies',
                'preferred_category' => 'durga-ji',
                'fallback_tag'       => 'durga-ji',
                'default_path'       => '/category/hindu-goddess/durga-ji/',
                'keywords'           => [
                    'Maa Durga', 'Durga Ji', 'Mata Rani', 'Durga Mata', 'Goddess Durga',
                    'मां दुर्गा', 'दुर्गा जी', 'दुर्गा माता', 'माता रानी'
                ]
            ],
            'krishna' => [
                'title'              => 'Lord Krishna Worship & Katha',
                'preferred_category' => 'krishna-ji',
                'fallback_tag'       => 'krishna-ji',
                'default_path'       => '/category/hindu-god/krishna-ji/',
                'keywords'           => [
                    'Lord Krishna', 'Krishna Ji', 'Bhagwan Krishna', 'Shree Krishna', 'Radhe Krishna',
                    'भगवान कृष्ण', 'श्री कृष्ण', 'राधे कृष्ण', 'कान्हा', 'बांके बिहारी'
                ]
            ],
            'ram' => [
                'title'              => 'Shree Ram Worship & Ramcharitmanas',
                'preferred_category' => 'shree-ram',
                'fallback_tag'       => 'shree-ram',
                'default_path'       => '/category/hindu-god/shree-ram/',
                'keywords'           => [
                    'Shree Ram', 'Lord Rama', 'Ram Ji', 'Bhagwan Ram', 'Ayodhya Ram', 'Jai Shree Ram',
                    'भगवान राम', 'श्री राम', 'जय श्री राम', 'अयोध्या राम', 'राम लला'
                ]
            ],
            'parvati' => [
                'title'              => 'Parvati Mata Worship',
                'preferred_category' => 'parvati-mata',
                'default_path'       => '/category/hindu-goddess/parvati-mata/',
                'keywords'           => [
                    'Parvati Mata', 'Maa Parvati', 'पार्वती माता', 'मां पार्वती', 'गौरी माता'
                ]
            ],
            'santoshi' => [
                'title'              => 'Santoshi Mata Vrat & Katha',
                'preferred_category' => 'santoshi-mata',
                'default_path'       => '/category/hindu-goddess/santoshi-mata/',
                'keywords'           => [
                    'Santoshi Mata', 'संतोषी माता', 'संतोषी मां'
                ]
            ],
            'kaali' => [
                'title'              => 'Kaali Mata Worship',
                'preferred_category' => 'kaali-mata',
                'fallback_tag'       => 'kaali-maata',
                'default_path'       => '/category/hindu-goddess/kaali-mata/',
                'keywords'           => [
                    'Kaali Mata', 'Maa Kali', 'काली माता', 'मां काली', 'महाकाली'
                ]
            ],
            'brahma' => [
                'title'              => 'Brahma Ji Worship',
                'preferred_category' => 'brahma-ji',
                'fallback_tag'       => 'bhrama-ji',
                'default_path'       => '/category/hindu-god/brahma-ji/',
                'keywords'           => [
                    'Brahma Ji', 'Lord Brahma', 'ब्रह्मा जी', 'भगवान ब्रह्मा'
                ]
            ],
            'balaji' => [
                'title'              => 'Shree Mehndipur Balaji',
                'preferred_category' => 'shree-mehndipur-balaji',
                'default_path'       => '/category/hindu-god/shree-mehndipur-balaji/',
                'keywords'           => [
                    'Mehndipur Balaji', 'Shree Balaji', 'मेहंदीपुर बालाजी', 'बालाजी'
                ]
            ],
            'ekadashi' => [
                'title'              => 'Ekadashi Vrat Katha & Muhurat',
                'preferred_category' => 'ekadashi',
                'default_path'       => '/category/vrat-katha/ekadashi/',
                'keywords'           => [
                    'Ekadashi Vrat', 'Ekadashi Katha', 'Ekadashi', 'एकादशी व्रत', 'एकादशी कथा', 'एकादशी'
                ]
            ],
            'karva_chauth' => [
                'title'              => 'Karva Chauth Vrat Katha & Muhurat',
                'preferred_category' => 'karva-chauth',
                'fallback_tag'       => 'karva-chauth',
                'default_path'       => '/category/vrat-katha/karva-chauth/',
                'keywords'           => [
                    'Karva Chauth', 'Karwa Chauth', 'करवा चौथ'
                ]
            ],
            'hartalika_teej' => [
                'title'              => 'Hartalika Teej Vrat Katha',
                'preferred_category' => 'hartalika-teej',
                'default_path'       => '/category/vrat-katha/hartalika-teej/',
                'keywords'           => [
                    'Hartalika Teej', 'हरतालिका तीज', 'तीज व्रत'
                ]
            ],
            'chhath_puja' => [
                'title'              => 'Chhath Puja Muhurat & Katha',
                'preferred_category' => 'chhath-puja',
                'default_path'       => '/category/vrat-katha/chhath-puja/',
                'keywords'           => [
                    'Chhath Puja', 'छठ पूजा', 'छठ महापर्व'
                ]
            ],
            'navratri' => [
                'title'              => 'Navratri Vrat, Puja & Muhurat',
                'preferred_category' => 'navratri',
                'default_path'       => '/category/festivals/navratri/',
                'keywords'           => [
                    'Navratri', 'Navaratri', 'नवरात्रि', 'शारदीय नवरात्रि', 'चैत्र नवरात्रि'
                ]
            ],
            'diwali' => [
                'title'              => 'Diwali Puja & Muhurat',
                'preferred_category' => 'diwali',
                'default_path'       => '/category/festivals/diwali/',
                'keywords'           => [
                    'Diwali Puja', 'Diwali Muhurat', 'Deepawali', 'दीवाली', 'दीपावली'
                ]
            ],
            'dhanteras' => [
                'title'              => 'Dhanteras Puja & Shubh Muhurat',
                'preferred_category' => 'dhanteras',
                'default_path'       => '/category/festivals/dhanteras/',
                'keywords'           => [
                    'Dhanteras Puja', 'Dhanteras', 'धनतेरस'
                ]
            ],
            'bhai_dooj' => [
                'title'              => 'Bhai Dooj Shubh Muhurat & Katha',
                'preferred_category' => 'bhai-dooj',
                'default_path'       => '/category/festivals/bhai-dooj/',
                'keywords'           => [
                    'Bhai Dooj', 'भाई दूज', 'यम द्वितीया'
                ]
            ],
            'aarti' => [
                'title'              => 'Aarti Sangrah',
                'preferred_category' => 'aarti-sangrah',
                'default_path'       => '/category/aarti-sangrah/',
                'keywords'           => [
                    'Aarti Sangrah', 'आरती संग्रह'
                ]
            ]
        ];

        $known_keywords = [];

        // Resolve URLs dynamically using WordPress taxonomy system
        foreach ($curated_taxonomies as $key => $meta) {
            $target_url = '';
            if (!empty($meta['preferred_category'])) {
                $term = get_term_by('slug', $meta['preferred_category'], 'category');
                if ($term && !is_wp_error($term)) {
                    $target_url = get_term_link($term);
                }
            }
            if (empty($target_url) && !empty($meta['fallback_tag'])) {
                $term = get_term_by('slug', $meta['fallback_tag'], 'post_tag');
                if ($term && !is_wp_error($term)) {
                    $target_url = get_term_link($term);
                }
            }
            if (empty($target_url) || is_wp_error($target_url)) {
                $target_url = $base_url . $meta['default_path'];
            }

            $taxonomy_links[] = [
                'url'      => $target_url,
                'title'    => $meta['title'],
                'keywords' => $meta['keywords']
            ];

            foreach ($meta['keywords'] as $kw) {
                $known_keywords[mb_strtolower($kw)] = true;
            }
        }

        // 2. Dynamic Tag Scanner: Automatically scan active WordPress post tags
        $active_tags = get_terms([
            'taxonomy'   => 'post_tag',
            'hide_empty' => true,
            'number'     => 100
        ]);

        if (!empty($active_tags) && !is_wp_error($active_tags)) {
            $ignored_generic = [
                'horoscope', 'janam', 'katha', 'rashi', 'aaj ka rashifal',
                'daily horoscope', 'rashifal', 'astrology', 'hindu-devotional'
            ];

            foreach ($active_tags as $t_obj) {
                $t_name = trim($t_obj->name);
                $lower_name = mb_strtolower($t_name);

                if (mb_strlen($t_name) < 4 || in_array($lower_name, $ignored_generic, true)) {
                    continue;
                }

                if (!isset($known_keywords[$lower_name])) {
                    $tag_url = get_tag_link($t_obj->term_id);
                    if ($tag_url && !is_wp_error($tag_url)) {
                        $taxonomy_links[] = [
                            'url'      => $tag_url,
                            'title'    => $t_name,
                            'keywords' => [$t_name]
                        ];
                        $known_keywords[$lower_name] = true;
                    }
                }
            }
        }

        set_transient(self::TAXONOMY_CACHE_KEY, $taxonomy_links, self::TAXONOMY_CACHE_TTL);
        return $taxonomy_links;
    }

    /**
     * Process message text and apply subtle, anti-spam auto-hyperlinks.
     *
     * @param string $text Raw message from Gemini (can contain Markdown)
     * @param int $max_links Maximum distinct URLs to link in this message (default: 2)
     * @return string Enriched message with clean, clickable links
     */
    public static function auto_link_response($text, $max_links = self::MAX_LINKS_PER_MESSAGE) {
        if (empty($text) || !is_string($text)) {
            return $text;
        }

        // Count existing links in the text
        $existing_md = preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', $text, $md_matches);
        $existing_html = preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $text, $html_matches);
        $existing_link_count = $existing_md + $existing_html;

        if ($existing_link_count >= $max_links) {
            return $text; // Cap already reached
        }

        // Collect existing URLs so we never duplicate them (anti-spam rule)
        $linked_urls = [];
        if (!empty($md_matches[2])) {
            foreach ($md_matches[2] as $u) {
                $linked_urls[] = rtrim(trim($u), '/');
            }
        }
        if (!empty($html_matches[1])) {
            foreach ($html_matches[1] as $u) {
                $linked_urls[] = rtrim(trim($u), '/');
            }
        }

        // Protect existing Markdown links and HTML tags with placeholders to prevent nested/corrupted tags
        $placeholders = [];
        $placeholder_idx = 0;

        $text = preg_replace_callback('/\[[^\]]+\]\([^\)]+\)/', function ($match) use (&$placeholders, &$placeholder_idx) {
            $key = "___NIYATI_MD_LINK_{$placeholder_idx}___";
            $placeholders[$key] = $match[0];
            $placeholder_idx++;
            return $key;
        }, $text);

        $text = preg_replace_callback('/<[^>]+>/', function ($match) use (&$placeholders, &$placeholder_idx) {
            $key = "___NIYATI_HTML_TAG_{$placeholder_idx}___";
            $placeholders[$key] = $match[0];
            $placeholder_idx++;
            return $key;
        }, $text);

        $dictionary = self::get_link_dictionary();

        $kw_map = [];
        $regex_parts = [];

        foreach ($dictionary as $entry) {
            $url = $entry['url'];
            foreach ($entry['keywords'] as $kw) {
                $kw = trim($kw);
                if (mb_strlen($kw) >= 3) {
                    $lower_kw = mb_strtolower($kw);
                    if (!isset($kw_map[$lower_kw])) {
                        $kw_map[$lower_kw] = [
                            'url' => $url,
                            'raw' => $kw
                        ];
                    }
                    $regex_parts[] = preg_quote($kw, '/');
                }
            }
        }

        // Sort regex alternatives by character length descending so that longest phrase matches first
        usort($regex_parts, function ($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        // Combined pattern with /ui flags (case-insensitive + unicode)
        $combined_pattern = '/(?:\b|(?<=^|[\s\p{P}]))(' . implode('|', $regex_parts) . ')(?:\b|(?=[\s\p{P}]|$))/ui';

        $total_links = $existing_link_count;

        $enriched = preg_replace_callback($combined_pattern, function ($matches) use (&$linked_urls, &$total_links, $max_links, $kw_map) {
            if ($total_links >= $max_links) {
                return $matches[0]; // Cap reached
            }

            $matched_text = $matches[1];
            $lower = mb_strtolower($matched_text);

            if (!isset($kw_map[$lower])) {
                return $matches[0];
            }

            $url = $kw_map[$lower]['url'];
            $clean_url = rtrim($url, '/');

            // ANTI-SPAM RULE: Never link the same URL more than ONCE in the same message!
            if (in_array($clean_url, $linked_urls, true)) {
                return $matches[0];
            }

            $linked_urls[] = $clean_url;
            $total_links++;

            return '[' . $matched_text . '](' . $url . ')';
        }, $text);

        // Restore placeholders
        if (!empty($placeholders)) {
            $enriched = strtr($enriched, $placeholders);
        }

        return $enriched ?: $text;
    }
}
