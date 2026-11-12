<?php
/**
 * MeraRashifal Homepage Manager
 * Complete Section Placement, Ordering, Headings, Interlinking and Content Management Engine.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Homepage_Manager {

    const OPTION_KEY = 'mr_homepage_config';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('wp_ajax_mr_save_homepage_config', [__CLASS__, 'ajax_save_config']);
        add_action('wp_ajax_mr_reset_homepage_config', [__CLASS__, 'ajax_reset_config']);
    }

    /**
     * Get Default Configuration for All Sections
     */
    public static function get_default_config() {
        return [
            'sections' => [
                'astronomical_bar' => [
                    'id'          => 'astronomical_bar',
                    'name'        => 'Astronomical Status Strip (पंचांग स्टेटस पट्टी)',
                    'enabled'     => 1,
                    'order'       => 1,
                    'title'       => 'Live Ephemeris & Panchang Bar',
                    'subtitle'    => 'Real-time Drik Ganita Tithi, Sunrise, Sunset, Moonrise & Samvat',
                    'badge'       => 'Live Ephemeris',
                ],
                'zodiac_horoscope' => [
                    'id'          => 'zodiac_horoscope',
                    'name'        => '1-Tap Daily Horoscope (दैनिक राशिफल - 12 Signs)',
                    'enabled'     => 1,
                    'order'       => 2,
                    'title'       => 'Daily Horoscope (दैनिक राशिफल)',
                    'subtitle'    => 'Tap your Moon or Sun Sign for immediate planetary forecast',
                    'badge'       => 'Today',
                ],
                'sunsign_calc' => [
                    'id'          => 'sunsign_calc',
                    'name'        => 'Find Your Sun Sign (अपनी राशि जानें - DOB to Vedic Rashi)',
                    'enabled'     => 1,
                    'order'       => 3,
                    'title'       => 'Find Your Sun Sign (अपनी राशि जानें)',
                    'subtitle'    => 'Don\'t know your Rashi or Sun Sign? Select your Date of Birth to reveal your authentic Vedic Sun Sign, ruling planet & personality traits.',
                    'badge'       => 'Instant Calculator',
                ],
                'hero_dashboard' => [
                    'id'          => 'hero_dashboard',
                    'name'        => '3-Column Core Dashboard (Panchang + Kundali + Niyati AI)',
                    'enabled'     => 1,
                    'order'       => 4,
                    'title'       => 'Vedic Astrology Core Dashboard',
                    'subtitle'    => 'Live Panchang, Free Janam Kundali generator & Niyati AI Astrologer',
                    'badge'       => 'Live Astrometry',
                    'panchang_title' => 'Today\'s Panchang (आज का पंचांग)',
                    'kundali_title'  => 'Free Janam Kundali (जन्म कुंडली)',
                    'niyati_title'   => 'Niyati AI Astrologer (नियति AI)',
                ],
                'festivals_calendar' => [
                    'id'          => 'festivals_calendar',
                    'name'        => 'Upcoming Vrats & Festivals (आगामी व्रत एवं पावन पर्व)',
                    'enabled'     => 1,
                    'order'       => 5,
                    'title'       => 'Upcoming Vrats, Fasts & Sacred Festivals',
                    'subtitle'    => 'Calculated using authentic Drik Ganita planetary positions and Shastric Sunrise (Udaya Tithi) timings.',
                    'badge'       => 'Drik Calendar',
                    'cta_text'    => 'View Full 2026-2027 Calendar',
                    'cta_url'     => home_url('/festivals/'),
                    'items_count' => 4,
                ],
                'master_reports' => [
                    'id'          => 'master_reports',
                    'name'        => 'Vedic Astrology Master Reports (वैदिक मास्टर रिपोर्ट्स)',
                    'enabled'     => 1,
                    'order'       => 6,
                    'title'       => 'Vedic Astrology Master Reports',
                    'subtitle'    => 'Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms and verified by traditional pandits.',
                    'badge'       => 'Flagship Reports',
                    'cta_text'    => 'Browse All Reports & Bundles',
                    'cta_url'     => home_url('/pricing/'),
                    'items_count' => 9,
                    'layout'      => '3x2_scroll',
                ],
                'baby_names' => [
                    'id'          => 'baby_names',
                    'name'        => 'Baby Name Finder Engine (नामकरण - वैदिक बेबी नेम्स)',
                    'enabled'     => 1,
                    'order'       => 7,
                    'title'       => 'Sacred Vedic Baby Names Engine (नामकरण)',
                    'subtitle'    => 'Discover auspicious boy & girl names starting with your child\'s birth nakshatra syllables.',
                    'badge'       => 'Nakshatra Based',
                ],
                'vedic_calculators' => [
                    'id'          => 'vedic_calculators',
                    'name'        => 'Vedic Calculators & Shastric Tools (वैदिक कैलकुलेटर)',
                    'enabled'     => 1,
                    'order'       => 8,
                    'title'       => 'Vedic Calculators & Shastric Engines',
                    'subtitle'    => 'Instant, ad-free computations based on ancient Brihat Parashara Hora Shastra and Surya Siddhanta principles.',
                    'badge'       => '8 Free Tools',
                ],
                'devotional_corner' => [
                    'id'          => 'devotional_corner',
                    'name'        => 'Sanatan Devotional Corner (सनातन भक्ति, आरती एवं कथा)',
                    'enabled'     => 1,
                    'order'       => 9,
                    'title'       => 'Sanatan Devotional & Spiritual Corner',
                    'subtitle'    => 'Sacred stotras, authentic vrat kathas, and timeless arti sangrah for your daily puja anushthan.',
                    'badge'       => 'Bhakti & Puja',
                ],
                'custom_banner' => [
                    'id'          => 'custom_banner',
                    'name'        => 'Custom Announcement / Promotional Banner (कस्टम बैनर)',
                    'enabled'     => 0,
                    'order'       => 10,
                    'title'       => 'Special Navratri & Diwali Vedic Anushthan 2026',
                    'subtitle'    => 'Book personalized Pandit Puja & receive consecrated sacred Prasad delivered to your doorstep.',
                    'badge'       => 'Special Offer',
                    'cta_text'    => 'Explore Sacred Offer',
                    'cta_url'     => home_url('/reports/'),
                    'bg_color'    => '#78350F',
                ],
                'telegram_banner' => [
                    'id'          => 'telegram_banner',
                    'name'        => 'Telegram Broadcast Banner (टेलीग्राम कम्युनिटी)',
                    'enabled'     => 0,
                    'order'       => 11,
                    'title'       => 'Get Daily Panchang & Shubh Muhurat on Your Phone Every Morning',
                    'subtitle'    => 'Delivered directly at 6:00 AM daily: Aaj Ka Rashifal, Choghadiya alert, Abhijit Muhurat, Rahukaal warnings, and auspicious Ekadashi reminders. 100% free forever.',
                    'badge'       => 'Join 50,000+ Devotees',
                    'cta_text'    => 'Join Free Telegram Channel',
                    'cta_url'     => 'https://t.me/merarashifal',
                ],
                'trust_badges' => [
                    'id'          => 'trust_badges',
                    'name'        => 'Vedic Authority & Trust Strip (ट्रस्ट बैज)',
                    'enabled'     => 1,
                    'order'       => 12,
                    'title'       => 'Why 120,000+ Seekers Trust MeraRashifal',
                    'subtitle'    => 'Authentic Shastric Algorithms • Instant Delivery • 100% Confidential',
                    'badge'       => 'Verified Trust',
                ],
            ],
            'style' => [
                'bg_color'          => '#FFFBEB',
                'primary_color'     => '#964900',
                'saffron_color'     => '#F58220',
                'card_bg'           => '#FFFFFF',
            ],
        ];
    }

    /**
     * Get Merged Configuration
     */
    public static function get_config() {
        $defaults = self::get_default_config();
        $saved    = get_option(self::OPTION_KEY, []);

        if (!empty($saved['sections']) && is_array($saved['sections'])) {
            foreach ($defaults['sections'] as $key => $default_section) {
                if (isset($saved['sections'][$key])) {
                    $defaults['sections'][$key] = wp_parse_args($saved['sections'][$key], $default_section);
                }
            }
        }

        if (!empty($saved['style']) && is_array($saved['style'])) {
            $defaults['style'] = wp_parse_args($saved['style'], $defaults['style']);
        }

        return $defaults;
    }

    /**
     * Get Sections Ordered by Position
     */
    public static function get_ordered_sections() {
        $config   = self::get_config();
        $sections = $config['sections'];

        uasort($sections, function($a, $b) {
            $order_a = isset($a['order']) ? intval($a['order']) : 99;
            $order_b = isset($b['order']) ? intval($b['order']) : 99;
            if ($order_a === $order_b) {
                return 0;
            }
            return ($order_a < $order_b) ? -1 : 1;
        });

        return $sections;
    }

    /**
     * Check if section is enabled
     */
    public static function is_enabled($section_id) {
        $config = self::get_config();
        return !empty($config['sections'][$section_id]['enabled']);
    }

    /**
     * Get specific section config
     */
    public static function get_section($section_id) {
        $config = self::get_config();
        return $config['sections'][$section_id] ?? [];
    }

    /**
     * Register Admin Menu
     */
    public static function register_admin_menu() {
        add_menu_page(
            'Homepage Manager',
            'Homepage Manager ⚡',
            'manage_options',
            'mr-homepage-manager',
            [__CLASS__, 'render_admin_page'],
            'dashicons-layout',
            27
        );
    }

    /**
     * Enqueue Admin Assets
     */
    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'mr-homepage-manager') === false) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    /**
     * Smart Festival Link Resolver: Interlink to Post or Term, fallback to /festivals/
     */
    public static function get_festival_smart_link($name_en, $name_hi = '') {
        global $wpdb;

        $name_en_clean = trim($name_en);
        $name_hi_clean = trim($name_hi);

        // 1. Search for a published post in wp_posts
        $post = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_title, post_name FROM {$wpdb->posts} 
             WHERE post_status = 'publish' AND post_type = 'post' 
             AND (
                 post_title LIKE %s 
                 OR post_title LIKE %s 
                 OR post_name LIKE %s 
                 OR post_name = %s
             ) 
             LIMIT 1",
            '%' . $wpdb->esc_like($name_en_clean) . '%',
            (!empty($name_hi_clean) ? '%' . $wpdb->esc_like($name_hi_clean) . '%' : '%nomatch%'),
            '%' . $wpdb->esc_like(sanitize_title($name_en_clean)) . '%',
            sanitize_title($name_en_clean)
        ), ARRAY_A);

        if ($post) {
            return [
                'url'   => get_permalink($post['ID']),
                'type'  => 'article',
                'title' => $post['post_title'],
            ];
        }

        // 2. Search for a relevant Category or Tag in wp_terms
        $term = $wpdb->get_row($wpdb->prepare(
            "SELECT t.term_id, t.name, t.slug, tt.taxonomy 
             FROM {$wpdb->terms} t 
             JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
             WHERE tt.taxonomy IN ('category', 'post_tag') 
             AND (
                 t.name LIKE %s 
                 OR t.slug LIKE %s 
                 OR t.slug = %s
             ) 
             LIMIT 1",
            '%' . $wpdb->esc_like($name_en_clean) . '%',
            '%' . $wpdb->esc_like(sanitize_title($name_en_clean)) . '%',
            sanitize_title($name_en_clean)
        ), ARRAY_A);

        if ($term) {
            $link = get_term_link(intval($term['term_id']), $term['taxonomy']);
            if (!is_wp_error($link)) {
                return [
                    'url'   => $link,
                    'type'  => $term['taxonomy'],
                    'title' => $term['name'],
                ];
            }
        }

        // 3. Fallback gracefully to central Festivals archive page
        return [
            'url'   => home_url('/festivals/'),
            'type'  => 'festivals_page',
            'title' => 'All Festivals Calendar',
        ];
    }

    /**
     * Get Live Synced Vedic Reports
     */
    public static function get_synced_reports() {
        $reports_data = [];

        // Primary source: MR_Catalog_Manager + MR_Report_Landing_Data
        if (class_exists('MR_Catalog_Manager') && class_exists('MR_Report_Landing_Data')) {
            $cards = MR_Catalog_Manager::get_all_cards();
            $slug_map = [
                'kundli_predictions'    => ['page' => 'janam-kundali-master-report', 'name' => 'Master Janma Kundali & Harmonics', 'pages' => '26 Pages Detailed PDF', 'desc' => '16 Shodashvargas, Shadbala strengths, Ashtakvarga evaluation & personalized Vedic remedies.'],
                'yearly_horoscope_long' => ['page' => 'yearly-horoscope-report', 'name' => 'Yearly Horoscope Varshaphal 2026-27', 'pages' => '24 Pages Annual Forecast', 'desc' => '12-Month life forecast, major planetary transits, Tajika Sahams & wealth timing.'],
                'mahadasha_timing'      => ['page' => 'dasha-timing-report', 'name' => 'Master Vedic Dasha Life Timing', 'pages' => '20 Pages Chronology', 'desc' => 'Vimshottari Mahadasha & Antardasha timeline to 2050 with remedial countermeasures.'],
                'palmistry_report'      => ['page' => 'palm-reading-report', 'name' => 'Master Hast Rekha & Palmistry', 'pages' => '20 Pages Samudrik', 'desc' => 'Lifeline, heart line, fate line analysis, planetary mounts & Samudrik Shastra remedies.'],
                'dosh_remedial'         => ['page' => 'kundali-dosh-report', 'name' => 'Master Kundali Dosh & Remedies', 'pages' => '20 Pages Remedial', 'desc' => 'Manglik, Kaal Sarp, Pitra, and Kemdrum Dosh evaluation with consecrated shastric remedies.'],
                'kundli_matching'       => ['page' => 'kundali-and-gun-matching-report', 'name' => 'Master 36 Gun Milan & Matching', 'pages' => '24 Pages Compatibility', 'desc' => 'Ashtakoot scoring, Nadi & Bhakoot dosha breakdown with matrimonial harmony remedies.'],
                'numerology'            => ['page' => 'numerology-report', 'name' => 'Master Numerology & Name Alignment', 'pages' => '22 Pages Numerology', 'desc' => 'Life path number, destiny number, name vibration tuning & business compatibility.'],
                'yearly_horoscope_vedic'=> ['page' => 'yearly-horoscope-snapshot-report', 'name' => 'Yearly Horoscope Snapshot (2026)', 'pages' => '5 Pages Snapshot', 'desc' => 'Quick comprehensive snapshot of your upcoming year with key transits and dates.'],
            ];

            foreach ($slug_map as $key => $meta) {
                $card = $cards[$key] ?? [];
                $price_sale = !empty($card['price_sale']) ? floatval($card['price_sale']) : 99;
                $price_reg  = !empty($card['price_regular']) ? floatval($card['price_regular']) : 999;
                $discount   = !empty($card['discount_text']) ? $card['discount_text'] : '90% OFF';
                $image_url  = !empty($card['image_url']) ? $card['image_url'] : '';

                // If image URL is missing or plugin default, check local fallback
                if (empty($image_url) || strpos($image_url, 'kundli_predictions.png') !== false) {
                    $local_fallback = content_url('/uploads/2026/11/Gemini_Generated_Image_s4glkgs4glkgs4gl.png');
                    if ($key === 'yearly_horoscope_long') {
                        $local_fallback = content_url('/uploads/2026/11/Mera-Rashifal-2027_-Himalayan-Zodiac-Sunrise.png');
                    } elseif ($key === 'yearly_horoscope_vedic') {
                        $local_fallback = content_url('/uploads/2026/11/2027-Yearly-Horoscope-Under-the-Zodiac-Wheel.png');
                    }
                    $image_url = $local_fallback;
                }

                $reports_data[$key] = [
                    'key'           => $key,
                    'title'         => $meta['name'],
                    'page_url'      => home_url('/' . $meta['page'] . '/'),
                    'pages_badge'   => $meta['pages'],
                    'description'   => $meta['desc'],
                    'price_sale'    => $price_sale,
                    'price_regular' => $price_reg,
                    'discount_text' => $discount,
                    'image_url'     => $image_url,
                ];
            }
        }

        return $reports_data;
    }

    /**
     * Get Deities with Authentic Media Library Images
     */
    public static function get_deities() {
        return [
            [
                'name'      => 'Shree Ram',
                'hindi'     => 'श्री राम',
                'count'     => '14 Stotras',
                'image_url' => content_url('/uploads/2025/09/Ram-Ji-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Krishna Ji',
                'hindi'     => 'श्री कृष्ण',
                'count'     => '18 Aartis',
                'image_url' => content_url('/uploads/2025/10/Shree-Krishna-Janam-Katha-300x169.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Shiv Ji',
                'hindi'     => 'शिव जी',
                'count'     => '22 Stutis',
                'image_url' => content_url('/uploads/2025/09/Shiv-Ji-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Ganesh Ji',
                'hindi'     => 'गणेश जी',
                'count'     => '12 Chalisa',
                'image_url' => content_url('/uploads/2025/09/Ganesh-Ji-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Vishnu Ji',
                'hindi'     => 'विष्णु जी',
                'count'     => '10 Sahasranam',
                'image_url' => content_url('/uploads/2025/09/vishnu-ji-Full-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Hanuman Ji',
                'hindi'     => 'हनुमान जी',
                'count'     => '8 Paath',
                'image_url' => content_url('/uploads/2025/10/Shree-Hanuman-Chalisa-300x169.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Durga Mata',
                'hindi'     => 'दुर्गा माता',
                'count'     => '16 Suktams',
                'image_url' => content_url('/uploads/2025/10/Durga-Mata-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Laxmi Mata',
                'hindi'     => 'लक्ष्मी माता',
                'count'     => '11 Suktams',
                'image_url' => content_url('/uploads/2025/10/Laxmi-Mata-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Saraswati Mata',
                'hindi'     => 'सरस्वती माता',
                'count'     => '7 Vandana',
                'image_url' => content_url('/uploads/2025/10/Saraswati-Mata-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
            [
                'name'      => 'Surya Dev',
                'hindi'     => 'सूर्य देव',
                'count'     => '9 Namaskar',
                'image_url' => content_url('/uploads/2025/08/only-sun-300x300.webp'),
                'url'       => home_url('/vrat-katha/'),
            ],
        ];
    }

    /**
     * Get Vedic Calculators Configuration
     */
    public static function get_calculators() {
        return [
            [
                'id'          => 'choghadiya',
                'title'       => 'Choghadiya Muhurat (चौघड़िया)',
                'description' => 'Day & Night Shubh, Labh, Amrit, Rog, and Kaal timings with live planetary hour status.',
                'url'         => home_url('/daily-choghadiya-muhurat/'),
                'icon'        => 'alarm',
                'color'       => '#964900',
                'btn_text'    => 'Calculate Choghadiya',
            ],
            [
                'id'          => 'gun_milan',
                'title'       => '36 Gun Milan (गुण मिलान)',
                'description' => 'Ashtakoot compatibility, Nadi dosh, Bhakoot scoring, and matrimonial harmony breakdown.',
                'url'         => home_url('/matchmaking-compatibility-check/'),
                'icon'        => 'favorite',
                'color'       => '#9f3c59',
                'btn_text'    => 'Match Horoscopes',
            ],
            [
                'id'          => 'dosh_calc',
                'title'       => 'Kundali Dosh Calculator (दोष निवारण)',
                'description' => 'In-depth shastric evaluation of Manglik, Kaal Sarp, Kemdrum, and Pitra Dosh.',
                'url'         => home_url('/dosh/'),
                'icon'        => 'warning',
                'color'       => '#964900',
                'btn_text'    => 'Check Dosh Free',
            ],
            [
                'id'          => 'mahadasha_calc',
                'title'       => 'Mahadasha Checker (महादशा)',
                'description' => 'Real-time Vimshottari planetary periods and running transit impact checker.',
                'url'         => home_url('/dasha/'),
                'icon'        => 'hourglass_empty',
                'color'       => '#7d5700',
                'btn_text'    => 'Check Active Dasha',
            ],
            [
                'id'          => 'muhurat_dates',
                'title'       => 'Shubh Muhurat Dates (शुभ मुहूर्त)',
                'description' => 'Auspicious dates for Vivah, Griha Pravesh, Property, and Vehicle purchase.',
                'url'         => home_url('/muhurat-dates/'),
                'icon'        => 'event_available',
                'color'       => '#f58220',
                'btn_text'    => 'Explore Muhurat Dates',
            ],
            [
                'id'          => 'baby_names_tool',
                'title'       => 'Baby Names Engine (नामकरण)',
                'description' => '50,000+ Vedic names filtered by Nakshatra charan, Rashi, and numerological harmony.',
                'url'         => home_url('/baby-names/'),
                'icon'        => 'child_care',
                'color'       => '#9f3c59',
                'btn_text'    => 'Find Sacred Names',
            ],
            [
                'id'          => 'name_suitability',
                'title'       => 'Name Suitability (नाम अनुकूलता)',
                'description' => 'Vedic Swara & Numerological harmony score for personal and corporate brand names.',
                'url'         => home_url('/name-suitability/'),
                'icon'        => 'spellcheck',
                'color'       => '#964900',
                'btn_text'    => 'Analyze Name Score',
            ],
            [
                'id'          => 'remedies_hub',
                'title'       => 'Remedies Hub (ज्योतिष उपाय)',
                'description' => 'Shastric recommendations for Rudraksha, Certified Gemstones, and Vedic Mantras.',
                'url'         => home_url('/remedies-astrological-emotional-sociological-solutions/'),
                'icon'        => 'diamond',
                'color'       => '#7d5700',
                'btn_text'    => 'Explore Remedies',
            ],
        ];
    }

    /**
     * AJAX Save Settings
     */
    public static function ajax_save_config() {
        check_ajax_referer('mr_homepage_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $posted_sections = isset($_POST['sections']) && is_array($_POST['sections']) ? $_POST['sections'] : [];
        $posted_style    = isset($_POST['style']) && is_array($_POST['style']) ? $_POST['style'] : [];

        $current = self::get_config();

        foreach ($posted_sections as $sec_id => $sec_data) {
            $sec_id = sanitize_key($sec_id);
            if (isset($current['sections'][$sec_id])) {
                $current['sections'][$sec_id]['enabled']  = !empty($sec_data['enabled']) ? 1 : 0;
                $current['sections'][$sec_id]['order']    = intval($sec_data['order'] ?? 99);
                $current['sections'][$sec_id]['title']    = sanitize_text_field(wp_unslash($sec_data['title'] ?? ''));
                $current['sections'][$sec_id]['subtitle'] = sanitize_text_field(wp_unslash($sec_data['subtitle'] ?? ''));
                if (isset($sec_data['badge'])) {
                    $current['sections'][$sec_id]['badge'] = sanitize_text_field(wp_unslash($sec_data['badge']));
                }
                if (isset($sec_data['cta_text'])) {
                    $current['sections'][$sec_id]['cta_text'] = sanitize_text_field(wp_unslash($sec_data['cta_text']));
                }
                if (isset($sec_data['cta_url'])) {
                    $current['sections'][$sec_id]['cta_url'] = esc_url_raw($sec_data['cta_url']);
                }
                if (isset($sec_data['items_count'])) {
                    $current['sections'][$sec_id]['items_count'] = max(2, min(12, intval($sec_data['items_count'])));
                }
            }
        }

        if (!empty($posted_style['bg_color'])) {
            $current['style']['bg_color'] = sanitize_hex_color($posted_style['bg_color']) ?: '#FFFBEB';
        }

        update_option(self::OPTION_KEY, $current);

        wp_send_json_success([
            'message' => 'Homepage layout and content updated successfully!',
            'config'  => $current,
        ]);
    }

    /**
     * AJAX Reset to Factory Defaults
     */
    public static function ajax_reset_config() {
        check_ajax_referer('mr_homepage_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        delete_option(self::OPTION_KEY);
        $defaults = self::get_default_config();

        wp_send_json_success([
            'message' => 'Homepage restored to factory defaults.',
            'config'  => $defaults,
        ]);
    }

    /**
     * Render WP Admin Management Page
     */
    public static function render_admin_page() {
        $config   = self::get_config();
        $sections = self::get_ordered_sections();
        $reports  = self::get_synced_reports();
        $deities  = self::get_deities();
        $calcs    = self::get_calculators();
        ?>
        <div class="wrap mr-admin-wrap" style="max-width: 1200px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;">
            
            <!-- Header Bar -->
            <div style="background: linear-gradient(135deg, #78350F 0%, #964900 60%, #D97706 100%); padding: 24px 30px; border-radius: 12px; color: #fff; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(120, 53, 15, 0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="color: #fff; margin: 0 0 6px 0; font-size: 26px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span>🏠 MeraRashifal Homepage Manager</span>
                        <span style="background: rgba(255,255,255,0.2); font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Vedic Engine</span>
                    </h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">
                        Manage section ordering, placement, headings, live database sync, and mobile responsiveness for <code>/newhomepage</code>.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="<?php echo esc_url(home_url('/newhomepage/')); ?>" target="_blank" class="button" style="background: #fff; color: #964900; font-weight: 700; border: none; padding: 8px 16px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 6px;">
                        <span>👁️ Preview Live Homepage</span>
                    </a>
                    <button type="button" id="mr-btn-save-top" class="button button-primary" style="background: #F58220; border-color: #D97706; font-weight: 700; padding: 8px 18px; border-radius: 8px; box-shadow: 0 2px 6px rgba(245, 130, 32, 0.4);">
                        💾 Save All Changes
                    </button>
                </div>
            </div>

            <!-- Notification Toast -->
            <div id="mr-admin-notice" style="display: none; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px;"></div>

            <!-- Main Form -->
            <form id="mr-homepage-form">
                <?php wp_nonce_field('mr_homepage_manager_nonce', 'mr_hm_nonce'); ?>

                <!-- Tabs Navigation -->
                <div style="display: flex; gap: 8px; border-bottom: 2px solid #E5E7EB; margin-bottom: 24px;">
                    <button type="button" class="mr-tab-btn active" data-tab="sections" style="padding: 10px 20px; background: none; border: none; font-weight: 700; font-size: 15px; cursor: pointer; border-bottom: 3px solid #964900; color: #964900;">
                        📑 Section Placements & Order (12 Blocks)
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="style" style="padding: 10px 20px; background: none; border: none; font-weight: 600; font-size: 15px; cursor: pointer; border-bottom: 3px solid transparent; color: #6B7280;">
                        🎨 Background & Branding (#FFFBEB)
                    </button>
                    <button type="button" class="mr-tab-btn" data-tab="sync" style="padding: 10px 20px; background: none; border: none; font-weight: 600; font-size: 15px; cursor: pointer; border-bottom: 3px solid transparent; color: #6B7280;">
                        ⚡ Master DB Sync Status (Reports & Festivals)
                    </button>
                </div>

                <!-- TAB 1: Sections Placement & Order -->
                <div id="mr-tab-sections" class="mr-tab-content">
                    <p style="color: #6B7280; font-size: 13px; margin-bottom: 16px;">
                        💡 <strong>How it works:</strong> Change the <strong>Order number</strong> to reposition any section anywhere on the page (1 appears at the very top, 12 at the bottom). Toggle the switch to show or hide any block instantly.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($sections as $sec_id => $sec) : ?>
                            <div class="mr-section-card" style="background: #fff; border: 1px solid #E5E7EB; border-left: 5px solid <?php echo !empty($sec['enabled']) ? '#10B981' : '#9CA3AF'; ?>; border-radius: 10px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #FEF3C7; color: #92400E; font-weight: 800; border-radius: 8px; font-size: 14px;">
                                            <input type="number" name="sections[<?php echo esc_attr($sec_id); ?>][order]" value="<?php echo esc_attr($sec['order']); ?>" min="1" max="20" style="width: 48px; text-align: center; font-weight: 700; border: 1px solid #D97706; border-radius: 6px; padding: 3px;" title="Placement Position Number" />
                                        </div>
                                        <div>
                                            <strong style="font-size: 16px; color: #1F2937;"><?php echo esc_html($sec['name']); ?></strong>
                                            <span style="font-size: 11px; background: #F3F4F6; color: #4B5563; padding: 2px 8px; border-radius: 4px; margin-left: 8px;"><code><?php echo esc_html($sec_id); ?></code></span>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                                            <input type="checkbox" name="sections[<?php echo esc_attr($sec_id); ?>][enabled]" value="1" <?php checked(!empty($sec['enabled']), 1); ?> style="width: 18px; height: 18px;" />
                                            <span><?php echo !empty($sec['enabled']) ? '<span style="color:#059669;">Active</span>' : '<span style="color:#6B7280;">Hidden</span>'; ?></span>
                                        </label>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; background: #F9FAFB; padding: 12px 16px; border-radius: 8px;">
                                    <div>
                                        <label style="display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px;">Section Heading (Title)</label>
                                        <input type="text" name="sections[<?php echo esc_attr($sec_id); ?>][title]" value="<?php echo esc_attr($sec['title']); ?>" style="width: 100%; font-size: 13px; padding: 6px 10px; border: 1px solid #D1D5DB; border-radius: 6px;" />
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px;">Subtitle / Description</label>
                                        <input type="text" name="sections[<?php echo esc_attr($sec_id); ?>][subtitle]" value="<?php echo esc_attr($sec['subtitle']); ?>" style="width: 100%; font-size: 13px; padding: 6px 10px; border: 1px solid #D1D5DB; border-radius: 6px;" />
                                    </div>
                                    <?php if (isset($sec['badge'])) : ?>
                                        <div style="max-width: 180px;">
                                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px;">Badge Pill Text</label>
                                            <input type="text" name="sections[<?php echo esc_attr($sec_id); ?>][badge]" value="<?php echo esc_attr($sec['badge']); ?>" style="width: 100%; font-size: 13px; padding: 6px 10px; border: 1px solid #D1D5DB; border-radius: 6px;" />
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($sec['cta_text'])) : ?>
                                        <div>
                                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4B5563; margin-bottom: 4px;">CTA Button Text & Link</label>
                                            <div style="display: flex; gap: 6px;">
                                                <input type="text" name="sections[<?php echo esc_attr($sec_id); ?>][cta_text]" value="<?php echo esc_attr($sec['cta_text']); ?>" placeholder="Button Text" style="width: 45%; font-size: 12px; padding: 6px 8px; border: 1px solid #D1D5DB; border-radius: 6px;" />
                                                <input type="text" name="sections[<?php echo esc_attr($sec_id); ?>][cta_url]" value="<?php echo esc_attr($sec['cta_url'] ?? ''); ?>" placeholder="https://..." style="width: 55%; font-size: 12px; padding: 6px 8px; border: 1px solid #D1D5DB; border-radius: 6px;" />
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($sec_id === 'master_reports') : ?>
                                        <div style="grid-column: 1 / -1; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 12px 16px; margin-top: 6px;">
                                            <label style="display: block; font-size: 12px; font-weight: 700; color: #92400E; margin-bottom: 6px;">Display Layout</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #78350F;">
                                                    <input type="radio" name="sections[<?php echo esc_attr($sec_id); ?>][layout]" value="3x2_scroll" <?php checked(($sec['layout'] ?? '3x2_scroll'), '3x2_scroll'); ?> />
                                                    <span>3X2 Horizontal Scroll (3 Columns × 2 Rows with Arrows)</span>
                                                </label>
                                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #78350F;">
                                                    <input type="radio" name="sections[<?php echo esc_attr($sec_id); ?>][layout]" value="2x2" <?php checked(($sec['layout'] ?? '3x2_scroll'), '2x2'); ?> />
                                                    <span>2X2 Vertical Grid (Complete Details on Desktop/Tablet, Limited on Mobile)</span>
                                                </label>
                                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #78350F;">
                                                    <input type="radio" name="sections[<?php echo esc_attr($sec_id); ?>][layout]" value="vertical" <?php checked(($sec['layout'] ?? '3x2_scroll'), 'vertical'); ?> />
                                                    <span>Vertical Listing (Stacked One Below the Other)</span>
                                                </label>
                                            </div>
                                            <p style="margin: 6px 0 0 0; font-size: 11.5px; color: #B45309;">
                                                Shortcodes: <code>[mr_reports_3x2_scroll]</code> | <code>[mr_reports_2x2_grid]</code> | <code>[mr_reports_vertical_list]</code>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TAB 2: Style & Branding -->
                <div id="mr-tab-style" class="mr-tab-content" style="display: none;">
                    <div style="background: #fff; border: 1px solid #E5E7EB; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); max-width: 600px;">
                        <h3 style="margin-top: 0; color: #1F2937;">🎨 Sacred Vedic Color Palette</h3>
                        <p style="color: #6B7280; font-size: 13px;">The background color is strictly calibrated to warm sacred ivory (#FFFBEB) with saffron (#F58220) and deep timber (#964900) accents.</p>
                        
                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Homepage Background Color</label>
                                <input type="text" name="style[bg_color]" value="<?php echo esc_attr($config['style']['bg_color'] ?? '#FFFBEB'); ?>" class="mr-color-field" data-default-color="#FFFBEB" />
                                <span style="font-size: 12px; color: #6B7280; margin-left: 8px;">Default: <code>#FFFBEB</code> (Vedic Cream)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Master DB Sync Status -->
                <div id="mr-tab-sync" class="mr-tab-content" style="display: none;">
                    <div style="background: #fff; border: 1px solid #E5E7EB; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h3 style="margin-top: 0; color: #1F2937;">⚡ Live Database Synchronization Audit</h3>
                        <p style="color: #6B7280; font-size: 13px;">
                            Verification that all Vedic Astrology Master Reports are pulling directly from <code>MR_Catalog_Manager</code> and <code>MR_Report_Landing_Data</code>.
                        </p>

                        <table class="widefat fixed striped" style="margin-top: 16px;">
                            <thead>
                                <tr>
                                    <th>Report Key</th>
                                    <th>Live Name</th>
                                    <th>Live Price (Sale / Reg)</th>
                                    <th>Discount</th>
                                    <th>Landing Page URL</th>
                                    <th>Graphic Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $rk => $rep) : ?>
                                    <tr>
                                        <td><code><?php echo esc_html($rk); ?></code></td>
                                        <td><strong><?php echo esc_html($rep['title']); ?></strong></td>
                                        <td><span style="color:#059669; font-weight:700;">₹<?php echo esc_html($rep['price_sale']); ?></span> <del style="color:#9CA3AF;">₹<?php echo esc_html($rep['price_regular']); ?></del></td>
                                        <td><span style="background:#DCFCE7; color:#166534; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700;"><?php echo esc_html($rep['discount_text']); ?></span></td>
                                        <td><a href="<?php echo esc_url($rep['page_url']); ?>" target="_blank" style="font-size:12px;"><?php echo esc_html(str_replace(home_url(), '', $rep['page_url'])); ?></a></td>
                                        <td><img src="<?php echo esc_url($rep['image_url']); ?>" style="height:32px; border-radius:4px; border:1px solid #ddd;" alt="Preview" /></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bottom Save Actions -->
                <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #E5E7EB;">
                    <button type="button" id="mr-btn-reset" class="button" style="color: #DC2626; border-color: #FCA5A5; font-weight: 600;">
                        🔄 Reset All to Factory Defaults
                    </button>
                    <button type="submit" id="mr-btn-save-bottom" class="button button-primary" style="background: #964900; border-color: #78350F; font-size: 15px; font-weight: 700; padding: 8px 24px; border-radius: 8px;">
                        💾 Save Homepage Configuration
                    </button>
                </div>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Color picker
            $('.mr-color-field').wpColorPicker();

            // Tabs
            $('.mr-tab-btn').on('click', function() {
                var tab = $(this).data('tab');
                $('.mr-tab-btn').removeClass('active').css({'border-bottom': '3px solid transparent', 'color': '#6B7280'});
                $(this).addClass('active').css({'border-bottom': '3px solid #964900', 'color': '#964900'});

                $('.mr-tab-content').hide();
                $('#mr-tab-' + tab).show();
            });

            // Save Form via AJAX
            function saveForm() {
                var $btn = $('#mr-btn-save-top, #mr-btn-save-bottom');
                $btn.prop('disabled', true).text('⏳ Saving...');

                var data = $('#mr-homepage-form').serialize();
                data += '&action=mr_save_homepage_config&nonce=' + $('[name="mr_hm_nonce"]').val();

                $.post(ajaxurl, data, function(res) {
                    $btn.prop('disabled', false).text('💾 Save All Changes');
                    var $notice = $('#mr-admin-notice');
                    if (res.success) {
                        $notice.css({'background': '#D1FAE5', 'color': '#065F46', 'border': '1px solid #6EE7B7'}).text('✅ ' + res.data.message).fadeIn();
                        setTimeout(function() { $notice.fadeOut(); }, 4000);
                    } else {
                        $notice.css({'background': '#FEE2E2', 'color': '#991B1B', 'border': '1px solid #FCA5A5'}).text('❌ ' + (res.data.message || 'Save failed')).fadeIn();
                    }
                }).fail(function() {
                    $btn.prop('disabled', false).text('💾 Save All Changes');
                    alert('Server communication error.');
                });
            }

            $('#mr-homepage-form').on('submit', function(e) {
                e.preventDefault();
                saveForm();
            });

            $('#mr-btn-save-top').on('click', function(e) {
                e.preventDefault();
                saveForm();
            });

            // Reset Button
            $('#mr-btn-reset').on('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to reset all section positions, titles, and styles to default?')) {
                    return;
                }

                $.post(ajaxurl, {
                    action: 'mr_reset_homepage_config',
                    nonce: $('[name="mr_hm_nonce"]').val()
                }, function(res) {
                    if (res.success) {
                        alert(res.data.message);
                        window.location.reload();
                    }
                });
            });
        });
        </script>
        <?php
    }
}

MR_Homepage_Manager::init();
