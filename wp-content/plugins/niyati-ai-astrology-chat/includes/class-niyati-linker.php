<?php
/**
 * Niyati Smart Auto-Linker Engine
 * Scans AI chat responses and links relevant MeraRashifal tools and pages.
 * 
 * Strict Anti-Spam Guardrails:
 * 1. Each URL is linked AT MOST ONCE per response.
 * 2. Maximum 2 distinct contextual links per message total (keeps responses clean and natural).
 * 3. Natural reading order: links are introduced progressively as the user reads.
 * 4. Longest-phrase matching: Prevents partial/fragmented sub-word collisions (e.g. 'आज का पंचांग' before 'पंचांग').
 * 5. Safe regex boundaries: Never links inside existing HTML tags or Markdown links [text](url).
 * 6. Canonical tool URLs: Matches exact MeraRashifal calculators (e.g. /dosh/kaal-sarp-dosh/, /dosh/mangal-dosh/).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Linker {

    const MAX_LINKS_PER_MESSAGE = 2; // Strict anti-spam cap

    /**
     * Get Master Link Map
     * Returns canonical site URLs and their corresponding Hindi/English keywords.
     */
    public static function get_link_dictionary() {
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

            // 5. Current Mahadasha Tool
            [
                'url'      => $base_url . '/dasha/current-mahadasha/',
                'title'    => 'Current Mahadasha Check',
                'keywords' => [
                    'वर्तमान महादशा', 'Current Mahadasha', 'महादशा फल', 'महादशा',
                    'Mahadasha', 'Maha Dasha', 'Mahadashas'
                ]
            ],

            // 6. Antardasha Tool
            [
                'url'      => $base_url . '/dasha/antar-dasha/',
                'title'    => 'Antardasha Check',
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

            // 20. Shani Dev Category
            [
                'url'      => $base_url . '/category/hindu-god/shani-dev/',
                'title'    => 'Shani Dev Worship & Remedies',
                'keywords' => [
                    'शनि की साढ़ेसाती', 'Shani Sade Sati', 'साढ़ेसाती', 'Sade Sati',
                    'शनि ढैय्या', 'Shani Dhaiya', 'शनि देव'
                ]
            ],

            // 21. Hanuman Ji Category
            [
                'url'      => $base_url . '/category/hindu-god/hanuman-ji/',
                'title'    => 'Hanuman Ji Worship',
                'keywords' => [
                    'हनुमान चालीसा', 'Hanuman Chalisa', 'बजरंगबली', 'हनुमान जी'
                ]
            ],

            // 22. Shiv Ji Category
            [
                'url'      => $base_url . '/category/hindu-god/shiv-ji/',
                'title'    => 'Lord Shiva Worship',
                'keywords' => [
                    'महामृत्युंजय मंत्र', 'Maha Mrityunjaya', 'भगवान शिव', 'महादेव की पूजा'
                ]
            ],

            // 23. Detailed Kundli Details
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
