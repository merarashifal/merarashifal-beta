<?php
/**
 * MeraRashifal Content Bot - Database Setup and Seeder
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_DB_Setup {

    /**
     * Get prefixed table name
     */
    public static function table( $name ) {
        global $wpdb;
        return $wpdb->prefix . 'mr_' . $name;
    }

    public static function table_festivals() {
        return self::table( 'content_festivals' );
    }

    public static function table_series() {
        return self::table( 'content_series' );
    }

    public static function table_queue() {
        return self::table( 'content_queue' );
    }

    public static function table_filler() {
        return self::table( 'filler_bank' );
    }

    public static function table_link_map() {
        return self::table( 'link_map' );
    }

    /**
     * Create all 5 plugin tables
     */
    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $t_festivals = self::table_festivals();
        $t_series    = self::table_series();
        $t_queue     = self::table_queue();
        $t_filler    = self::table_filler();
        $t_link_map  = self::table_link_map();

        // 1. Festivals coverage table
        $sql_festivals = "CREATE TABLE IF NOT EXISTS {$t_festivals} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            af_festival_id INT(11) DEFAULT NULL,
            festival_name_en VARCHAR(255) NOT NULL,
            festival_name_hi VARCHAR(255) DEFAULT '',
            festival_date DATE NOT NULL,
            tier TINYINT(4) DEFAULT 3,
            post_count TINYINT(4) DEFAULT 1,
            lead_days TINYINT(4) DEFAULT 3,
            coverage_status ENUM('active','skipped','done') DEFAULT 'active',
            region_notes VARCHAR(255) DEFAULT 'north,west',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY festival_date_status (festival_date, coverage_status),
            KEY af_festival_id (af_festival_id)
        ) {$charset_collate};";

        // 2. Series table
        $sql_series = "CREATE TABLE IF NOT EXISTS {$t_series} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            festival_id INT(11) NOT NULL,
            series_name VARCHAR(255) NOT NULL,
            total_posts TINYINT(4) DEFAULT 1,
            status ENUM('planned','active','complete','cancelled') DEFAULT 'planned',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY festival_id (festival_id)
        ) {$charset_collate};";

        // 3. Queue table (main content workspace)
        $sql_queue = "CREATE TABLE IF NOT EXISTS {$t_queue} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            series_id INT(11) DEFAULT NULL,
            festival_id INT(11) DEFAULT NULL,
            post_type ENUM('festival','filler') DEFAULT 'festival',
            topic_title_hi VARCHAR(500) NOT NULL,
            topic_angle VARCHAR(100) DEFAULT 'katha',
            persona VARCHAR(50) DEFAULT 'meera',
            focus_keyword VARCHAR(255) DEFAULT '',
            word_count_target SMALLINT(6) DEFAULT 900,
            series_position TINYINT(4) DEFAULT 1,
            scheduled_date DATE NOT NULL,
            publish_time TIME DEFAULT '07:00:00',
            ai_draft LONGTEXT,
            ai_draft_humanized LONGTEXT,
            ai_draft_tagged LONGTEXT,
            ai_meta_title VARCHAR(100) DEFAULT '',
            ai_meta_desc VARCHAR(250) DEFAULT '',
            ai_tags VARCHAR(500) DEFAULT '',
            generation_attempts TINYINT(4) DEFAULT 0,
            last_generated_at DATETIME DEFAULT NULL,
            admin_status ENUM('pending_review','approved','rejected','skipped','published') DEFAULT 'pending_review',
            admin_draft LONGTEXT,
            admin_meta_title VARCHAR(100) DEFAULT '',
            admin_meta_desc VARCHAR(250) DEFAULT '',
            admin_notes TEXT,
            approved_by INT(11) DEFAULT NULL,
            approved_at DATETIME DEFAULT NULL,
            wp_post_id INT(11) DEFAULT NULL,
            wp_slug VARCHAR(255) DEFAULT '',
            wp_scheduled_at DATETIME DEFAULT NULL,
            wp_published_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scheduled_status (scheduled_date, admin_status),
            KEY series_id (series_id),
            KEY festival_id (festival_id)
        ) {$charset_collate};";

        // 4. Filler Bank
        $sql_filler = "CREATE TABLE IF NOT EXISTS {$t_filler} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            topic_title_hi VARCHAR(500) NOT NULL,
            category VARCHAR(100) DEFAULT 'astrology',
            topic_angle VARCHAR(100) DEFAULT 'guide',
            focus_keyword VARCHAR(255) DEFAULT '',
            priority TINYINT(4) DEFAULT 5,
            seasonal_months VARCHAR(50) DEFAULT NULL,
            times_used TINYINT(4) DEFAULT 0,
            last_used_date DATE DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY priority_active (priority, is_active)
        ) {$charset_collate};";

        // 5. Link Map
        $sql_link_map = "CREATE TABLE IF NOT EXISTS {$t_link_map} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            url VARCHAR(500) NOT NULL,
            title_hi VARCHAR(500) DEFAULT '',
            title_en VARCHAR(500) DEFAULT '',
            link_type ENUM('post','page','tool','category','tag') DEFAULT 'post',
            wp_post_id INT(11) DEFAULT NULL,
            keywords_hi TEXT,
            keywords_en TEXT,
            last_synced DATETIME DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            PRIMARY KEY (id),
            KEY link_type_active (link_type, is_active)
        ) {$charset_collate};";

        dbDelta( $sql_festivals );
        dbDelta( $sql_series );
        dbDelta( $sql_queue );
        dbDelta( $sql_filler );
        dbDelta( $sql_link_map );
    }

    /**
     * Seed festivals from af_festivals into mr_content_festivals with tier classification
     */
    public static function seed_festivals() {
        global $wpdb;

        $t_festivals = self::table_festivals();

        // Check if already seeded
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_festivals}" );
        if ( $count > 0 ) {
            return;
        }

        // Check if af_festivals exists
        $af_table = $wpdb->prefix . 'festivals';
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $af_table ) );
        if ( ! $table_exists ) {
            return;
        }

        // Pull festivals from 2026 and 2027
        $festivals = $wpdb->get_results(
            "SELECT id, festival_date, festival_eng_1, festival_hin_1, event_category 
             FROM {$af_table} 
             WHERE festival_date >= '2026-01-01' AND festival_date <= '2027-12-31'
             ORDER BY festival_date ASC",
            ARRAY_A
        );

        if ( empty( $festivals ) ) {
            return;
        }

        foreach ( $festivals as $f ) {
            $name_en  = trim( $f['festival_eng_1'] );
            $name_hi  = trim( $f['festival_hin_1'] );
            $category = trim( $f['event_category'] );
            $date     = $f['festival_date'];

            $classification = self::classify_festival( $name_en, $name_hi, $category );

            $wpdb->insert(
                $t_festivals,
                [
                    'af_festival_id'   => $f['id'],
                    'festival_name_en' => $name_en,
                    'festival_name_hi' => $name_hi,
                    'festival_date'    => $date,
                    'tier'             => $classification['tier'],
                    'post_count'       => $classification['post_count'],
                    'lead_days'        => $classification['lead_days'],
                    'coverage_status'  => $classification['status'],
                    'region_notes'     => 'north,west',
                ],
                [ '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' ]
            );
        }
    }

    /**
     * Tier classification engine based on North + West India cultural importance
    /**
     * Tier classification engine based on North + West India cultural importance
     * Tier 1: Main / Mega Festivals (Diwali, Navratri, Dhanteras, Holi, Shivratri, Karwa Chauth, etc.)
     * Tier 2: Secondary / Major Festivals (Govardhan, Bhai Dooj, Tulsi Vivah, Hanuman Jayanti, Vasant Panchami, etc.)
     * Tier 3: Regular Vrats & Parvas (All 24 Ekadashis, Pradosh Vrats, Sankashti Chaturthi, Purnima)
     * Tier 4: Regional Observances (Onam, Pongal, Ugadi, Bihu, Gudi Padwa)
     * Tier 5: Skipped / Minor / Astronomical Markers
     */
    public static function classify_festival( $name_en, $name_hi, $category ) {
        $n_en = strtolower( $name_en );
        $n_hi = $name_hi;

        // Skip highly regional, administrative or minor rituals
        $skip_terms = [
            'atla tadde', 'anvadhan', 'ishti', 'mandala pooja', 'republic day', 'independence day',
            'bank', 'holiday', 'children', 'gandhi', 'ambedkar', 'darsha amavasya', 'chandra darshana',
            'solstice', 'short day', 'long day', 'winter solstice', 'summer solstice', 'manvadi'
        ];
        foreach ( $skip_terms as $skip ) {
            if ( strpos( $n_en, $skip ) !== false ) {
                return [ 'tier' => 5, 'post_count' => 0, 'lead_days' => 0, 'status' => 'skipped' ];
            }
        }

        // TIER 1: MAIN FESTIVALS (Mega Campaigns: Multi-day/Multi-post coverage across North + West India)
        // Diwali, Navratri (Sharad), Dhanteras, Dussehra, Karwa Chauth, Holi, Maha Shivratri,
        // Raksha Bandhan, Krishna Janmashtami, Ram Navami, Makar Sankranti, Chhath Puja, Ganesh Chaturthi
        if ( strpos( $n_en, 'diwali' ) !== false && strpos( $n_en, 'dev' ) === false && strpos( $n_en, 'choti' ) === false ) {
            return [ 'tier' => 1, 'post_count' => 5, 'lead_days' => 7, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'dhanteras' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 2, 'lead_days' => 4, 'status' => 'active' ];
        }
        if ( ( strpos( $n_en, 'navratri' ) !== false || strpos( $n_en, 'durga puja' ) !== false ) && strpos( $n_en, 'chaitra' ) === false && strpos( $n_en, 'gupt' ) === false ) {
            return [ 'tier' => 1, 'post_count' => 14, 'lead_days' => 3, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'dussehra' ) !== false || strpos( $n_en, 'vijayadashami' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 4, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'karwa chauth' ) !== false || strpos( $n_en, 'karva chauth' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 5, 'status' => 'active' ];
        }
        if ( ( strpos( $n_en, 'holi' ) !== false || strpos( $n_en, 'holika' ) !== false ) && strpos( $n_en, 'holiday' ) === false ) {
            return [ 'tier' => 1, 'post_count' => 5, 'lead_days' => 6, 'status' => 'active' ];
        }
        if ( ( strpos( $n_en, 'shivaratri' ) !== false || strpos( $n_en, 'shivratri' ) !== false ) && strpos( $n_en, 'maha' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 4, 'lead_days' => 5, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'raksha bandhan' ) !== false || strpos( $n_en, 'rakhi' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 5, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'janmashtami' ) !== false && strpos( $n_en, 'masik' ) === false ) {
            return [ 'tier' => 1, 'post_count' => 4, 'lead_days' => 5, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'ram navami' ) !== false || strpos( $n_en, 'rama navami' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 4, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'makar sankranti' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 4, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'chhath' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 5, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'ganesh chaturthi' ) !== false || strpos( $n_en, 'vinayaka chaturthi' ) !== false ) {
            return [ 'tier' => 1, 'post_count' => 3, 'lead_days' => 4, 'status' => 'active' ];
        }

        // TIER 2: SECONDARY FESTIVALS (Major Focused Campaigns: 1-2 high-quality posts)
        $tier2_matches = [
            'govardhan' => 1, 'annakut' => 1, 'bhai dooj' => 1, 'bhaiya dooj' => 1, 'tulsi vivah' => 1,
            'dev diwali' => 1, 'guru nanak' => 2, 'devutthana ekadashi' => 2, 'prabodhini' => 2,
            'ahoi ashtami' => 1, 'akshaya tritiya' => 2, 'hanuman jayanti' => 2, 'guru purnima' => 2,
            'hariyali teej' => 2, 'hartalika teej' => 2, 'vasant panchami' => 2, 'basant panchami' => 2,
            'chaitra navratri' => 4, 'baisakhi' => 2, 'gita jayanti' => 2, 'kalabhairav' => 2,
            'gujarati new year' => 1, 'labh panchami' => 1, 'sheetala ashtami' => 1, 'basoda' => 1,
            'nag panchami' => 1, 'ganga dussehra' => 1, 'sharad purnima' => 1, 'kojagiri' => 1,
            'gupt navratri' => 2, 'chhoti holi' => 1, 'kali chaudas' => 1, 'naraka chaturdashi' => 1,
            'chopda puja' => 1, 'sharda puja' => 1, 'chitragupta' => 1, 'yama deepam' => 1,
            'govatsa' => 1, 'vagh baras' => 1, 'anant chaturdashi' => 1
        ];

        foreach ( $tier2_matches as $term => $cnt ) {
            if ( strpos( $n_en, $term ) !== false ) {
                return [ 'tier' => 2, 'post_count' => $cnt, 'lead_days' => 3, 'status' => 'active' ];
            }
        }

        // TIER 3: REGULAR VRATS & PARVAS (All 24 Ekadashis, Pradosh, Sankashti, Purnima)
        if ( $category === 'ekadashi' || strpos( $n_en, 'ekadashi' ) !== false ) {
            return [ 'tier' => 3, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
        }
        if ( $category === 'pradosh' || strpos( $n_en, 'pradosh' ) !== false ) {
            return [ 'tier' => 3, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'sankashti' ) !== false ) {
            return [ 'tier' => 3, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
        }
        if ( strpos( $n_en, 'sankranti' ) !== false ) {
            return [ 'tier' => 3, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
        }

        // TIER 4: REGIONAL FESTIVALS (1 post)
        $tier4_matches = [ 'onam', 'pongal', 'ugadi', 'gudi padwa', 'bihu', 'kali puja' ];
        foreach ( $tier4_matches as $t4 ) {
            if ( strpos( $n_en, $t4 ) !== false ) {
                return [ 'tier' => 4, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
            }
        }

        // Default: If marked as major in af_festivals, give Tier 2 (Secondary), otherwise Tier 5 (Skipped)
        if ( $category === 'major' || $category === 'puja' ) {
            return [ 'tier' => 2, 'post_count' => 1, 'lead_days' => 2, 'status' => 'active' ];
        }

        return [ 'tier' => 5, 'post_count' => 0, 'lead_days' => 0, 'status' => 'skipped' ];
    }

    /**
     * Synchronize and refresh festivals from the master af_festivals table for a specified date range
     *
     * @param string|null $start_date YYYY-MM-DD (defaults to today)
     * @param string|null $end_date   YYYY-MM-DD (defaults to 1 year ahead)
     * @return array
     */
    public static function sync_festivals_from_master( $start_date = null, $end_date = null ) {
        global $wpdb;
        $t_content = self::table_festivals();
        $t_master  = $wpdb->prefix . 'festivals';

        if ( ! $start_date ) {
            $start_date = current_time( 'Y-m-d' );
        }
        if ( ! $end_date ) {
            $start_date_ts = strtotime( $start_date );
            $end_date = date( 'Y-m-d', strtotime( '+1 year', $start_date_ts ) );
        }

        // Pull master records in range
        $master_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, festival_date, festival_eng_1, festival_hin_1, event_category 
                 FROM {$t_master} 
                 WHERE festival_date >= %s AND festival_date <= %s 
                 ORDER BY festival_date ASC",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $inserted = 0;
        $updated  = 0;

        foreach ( $master_items as $m ) {
            $name_en  = trim( $m['festival_eng_1'] );
            $name_hi  = trim( $m['festival_hin_1'] );
            $cat      = trim( $m['event_category'] );
            $cls      = self::classify_festival( $name_en, $name_hi, $cat );

            $existing = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, tier, post_count, coverage_status FROM {$t_content} WHERE af_festival_id = %d LIMIT 1",
                    $m['id']
                )
            );

            if ( ! $existing ) {
                $wpdb->insert(
                    $t_content,
                    [
                        'af_festival_id'   => $m['id'],
                        'festival_name_en' => $name_en,
                        'festival_name_hi' => $name_hi,
                        'festival_date'    => $m['festival_date'],
                        'tier'             => $cls['tier'],
                        'post_count'       => $cls['post_count'],
                        'lead_days'        => $cls['lead_days'],
                        'coverage_status'  => $cls['status'],
                        'region_notes'     => 'north,west',
                    ],
                    [ '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' ]
                );
                $inserted++;
            }
        }

        // Clean up any false positives (e.g. Bank Holiday)
        $wpdb->query( "UPDATE {$t_content} SET tier = 5, post_count = 0, coverage_status = 'skipped' WHERE festival_name_en LIKE '%Bank%Holiday%' OR festival_name_hi LIKE '%बैंक%अवकाश%'" );

        return [
            'total_master' => count( $master_items ),
            'inserted'     => $inserted,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
        ];
    }

    /**
     * Seed 30 high-value starter filler topics across key astrological & devotional categories
     */
    public static function seed_filler_bank() {
        global $wpdb;

        $t_filler = self::table_filler();

        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_filler}" );
        if ( $count > 0 ) {
            return;
        }

        $starter_topics = [
            // Category: Puja Basics & Rituals
            [ 'title_hi' => 'घर के मंदिर में दीपक जलाने के सही नियम: घी का दीपक या तेल का दीपक?', 'cat' => 'puja_basics', 'kw' => 'ghar ke mandir me deepak jalane ke niyam', 'prio' => 1 ],
            [ 'title_hi' => 'तुलसी के पौधे के पास भूलकर भी न रखें ये 5 चीजें, आती है आर्थिक परेशानी', 'cat' => 'puja_basics', 'kw' => 'tulsi ke paas kya nahi rakhna chahiye', 'prio' => 1 ],
            [ 'title_hi' => 'शंख बजाने के चमत्कारी लाभ और घर में शंख रखने के जरूरी वैदिक नियम', 'cat' => 'puja_basics', 'kw' => 'shankh bajane ke fayde niyam', 'prio' => 2 ],
            [ 'title_hi' => 'हनुमान चालीसा का पाठ करते समय न करें ये गलतियां, जानें सही विधि और समय', 'cat' => 'puja_basics', 'kw' => 'hanuman chalisa path karne ke niyam', 'prio' => 1 ],
            [ 'title_hi' => 'शिवलिंग पर जल चढ़ाने का सही तरीका और दिशा: भूलकर भी न करें ये गलतियां', 'cat' => 'puja_basics', 'kw' => 'shivling par jal chadhane ka sahi tarika', 'prio' => 1 ],

            // Category: Vastu Shastra
            [ 'title_hi' => 'घर का मुख्य द्वार किस दिशा में होना चाहिए? वास्तु शास्त्र के अचूक नियम', 'cat' => 'vastu', 'kw' => 'ghar ka mukhya dwar vastu tips', 'prio' => 2 ],
            [ 'title_hi' => 'तिजोरी और पैसे रखने की सही दिशा: वास्तु के अनुसार धन वृद्धि के उपाय', 'cat' => 'vastu', 'kw' => 'tijori rakhne ki sahi disha vastu', 'prio' => 2 ],
            [ 'title_hi' => 'घर में किस दिशा में सोने से बढ़ती है सुख-शांति? दिशाओं का वैज्ञानिक व वास्तु महत्व', 'cat' => 'vastu', 'kw' => 'sone ki sahi disha vastu', 'prio' => 3 ],
            [ 'title_hi' => 'घर के ईशान कोण (उत्तर-पूर्व) में क्या रखना चाहिए और क्या नहीं?', 'cat' => 'vastu', 'kw' => 'ishan kon vastu tips', 'prio' => 3 ],
            [ 'title_hi' => 'आईना (Mirror) लगाने के वास्तु नियम: गलत दिशा में लगा शीशा बन सकता है वास्तु दोष', 'cat' => 'vastu', 'kw' => 'aaina lagane ke vastu niyam', 'prio' => 3 ],

            // Category: Astrology & Remedies
            [ 'title_hi' => 'शनि की साढ़े साती क्या है? इसके लक्षण और 5 सबसे सरल सात्विक उपाय', 'cat' => 'astrology', 'kw' => 'shani sade sati ke upay', 'prio' => 1 ],
            [ 'title_hi' => 'कुंडली में मांगलिक दोष: क्या यह हमेशा डरावना होता है? जानें इसके वास्तविक प्रभाव', 'cat' => 'astrology', 'kw' => 'kundli me manglik dosh ke prabhav upay', 'prio' => 1 ],
            [ 'title_hi' => 'सूर्य देव को अर्घ्य देने का सही समय, पात्र और मंत्र: मिलता है मान-सम्मान व तेज', 'cat' => 'astrology', 'kw' => 'surya dev ko arghya dene ke niyam', 'prio' => 2 ],
            [ 'title_hi' => 'कालसर्प दोष के वास्तविक लक्षण और जीवन में इसके प्रभावों को शांत करने के उपाय', 'cat' => 'astrology', 'kw' => 'kaal sarp dosh lakshan upay', 'prio' => 2 ],
            [ 'title_hi' => 'बृहस्पति (गुरु) ग्रह को मजबूत करने के आसान घरेलू उपाय: बढ़ती है विद्या और सौभाग्य', 'cat' => 'astrology', 'kw' => 'guru grah ko majboot karne ke upay', 'prio' => 2 ],
            [ 'title_hi' => 'चंद्रमा कमजोर होने के लक्षण: मानसिक तनाव, अनिद्रा और इसका ज्योतिषीय समाधान', 'cat' => 'astrology', 'kw' => 'chandrama kamjor hone ke lakshan upay', 'prio' => 2 ],

            // Category: Sacred Items & Gemstones
            [ 'title_hi' => 'रुद्राक्ष धारण करने के वैदिक नियम: कौन सा रुद्राक्ष आपके लिए है सबसे फलदायी?', 'cat' => 'sacred_items', 'kw' => 'rudraksha dharan karne ke niyam', 'prio' => 2 ],
            [ 'title_hi' => 'पीला पुखराज किसे पहनना चाहिए और किसे नहीं? सावधानियां और धारण विधि', 'cat' => 'sacred_items', 'kw' => 'pukhraj kise pehanna chahiye', 'prio' => 3 ],
            [ 'title_hi' => 'मोती रत्न पहनने के फायदे, नुकसान और सही उंगली में धारण करने की विधि', 'cat' => 'sacred_items', 'kw' => 'moti ratna pehanne ke fayde', 'prio' => 3 ],
            [ 'title_hi' => 'घर में पारद शिवलिंग रखने के नियम और इसके आध्यात्मिक व भौतिक लाभ', 'cat' => 'sacred_items', 'kw' => 'parad shivling ke labh niyam', 'prio' => 3 ],

            // Category: Mythology & Katha
            [ 'title_hi' => 'समुद्र मंथन की कथा: 14 रत्नों की उत्पत्ति और उनका गूढ़ आध्यात्मिक रहस्य', 'cat' => 'mythology', 'kw' => 'samudra manthan 14 ratna katha', 'prio' => 3 ],
            [ 'title_hi' => 'भगवान गणेश की एकदंत होने की कथा: महर्षि परशुराम और विघ्नहर्ता का प्रसंग', 'cat' => 'mythology', 'kw' => 'ganesh ji ekdant katha', 'prio' => 3 ],
            [ 'title_hi' => 'माँ गंगा का पृथ्वी पर अवतरण: राजा भगीरथ की तपस्या और शिव की जटाओं का रहस्य', 'cat' => 'mythology', 'kw' => 'ganga avtaran katha bhagirath', 'prio' => 4 ],
            [ 'title_hi' => 'माता लक्ष्मी और दरिद्रता (अलक्ष्मी) की कथा: घर में क्यों रखी जाती है नींबू-मिर्ची?', 'cat' => 'mythology', 'kw' => 'mata lakshmi aur alakshmi katha', 'prio' => 3 ],

            // Category: Daily Lifestyle & Sanskar
            [ 'title_hi' => 'ब्रह्म मुहूर्त में उठने के वैज्ञानिक व आध्यात्मिक फायदे: जानें सही समय और महत्व', 'cat' => 'lifestyle', 'kw' => 'brahma muhurta me uthne ke fayde', 'prio' => 2 ],
            [ 'title_hi' => 'संध्या वंदन का महत्व: सूर्यास्त के समय घर में क्यों नहीं सोना चाहिए?', 'cat' => 'lifestyle', 'kw' => 'sandhya samay sone ke nuksan niyam', 'prio' => 2 ],
            [ 'title_hi' => 'अन्नपूर्णा स्तोत्र और भोजन के वैदिक नियम: भोजन से पहले क्यों किया जाता है आचमन?', 'cat' => 'lifestyle', 'kw' => 'bhojan karne ke vedic niyam', 'prio' => 3 ],
            [ 'title_hi' => 'एकादशी व्रत के सामान्य नियम: क्या खाएं, क्या न खाएं और पारण की सही विधि', 'cat' => 'puja_basics', 'kw' => 'ekadashi vrat ke niyam parana vidhi', 'prio' => 2 ],
            [ 'title_hi' => 'शनिवार के दिन कौन सी चीजें घर नहीं लानी चाहिए? शनि दोष से बचने के उपाय', 'cat' => 'astrology', 'kw' => 'shanivar ko kya nahi kharidna chahiye', 'prio' => 2 ],
            [ 'title_hi' => 'घर में सुख-समृद्धि के लिए गाय को पहली रोटी खिलाने का महत्व और सही नियम', 'cat' => 'puja_basics', 'kw' => 'gai ko pehli roti khilane ka mahatva', 'prio' => 2 ],
        ];

        foreach ( $starter_topics as $topic ) {
            $wpdb->insert(
                $t_filler,
                [
                    'topic_title_hi' => $topic['title_hi'],
                    'category'       => $topic['cat'],
                    'topic_angle'    => 'guide',
                    'focus_keyword'  => $topic['kw'],
                    'priority'       => $topic['prio'],
                    'is_active'      => 1,
                ],
                [ '%s', '%s', '%s', '%s', '%d', '%d' ]
            );
        }
    }

    /**
     * Seed initial link map with existing tool pages
     */
    public static function seed_link_map() {
        global $wpdb;

        $t_link_map = self::table_link_map();
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_link_map}" );
        if ( $count > 0 ) {
            return;
        }

        $site_base = untrailingslashit( home_url() );

        $initial_links = [
            [ 'url' => $site_base . '/kundali-details/', 'title_hi' => 'जन्म कुंडली एवं जन्म पत्रिका', 'title_en' => 'Kundali Details', 'type' => 'tool', 'kw_hi' => 'कुंडली, जन्मपत्री, जन्म कुंडली, होरोस्कोप', 'kw_en' => 'kundali, birth chart' ],
            [ 'url' => $site_base . '/matchmaking-compatibility-check/', 'title_hi' => 'कुंडली मिलान (गुण मिलान)', 'title_en' => 'Kundli Matchmaking', 'type' => 'tool', 'kw_hi' => 'कुंडली मिलान, गुण मिलान, विवाह मिलान', 'kw_en' => 'kundali matching, matchmaking' ],
            [ 'url' => $site_base . '/dosh/mangal-dosh/', 'title_hi' => 'मांगलिक दोष जांच एवं उपाय', 'title_en' => 'Mangal Dosh Check', 'type' => 'tool', 'kw_hi' => 'मांगलिक दोष, मंगल दोष, मांगलिक', 'kw_en' => 'mangal dosh, manglik dosha' ],
            [ 'url' => $site_base . '/dosh/kaal-sarp-dosh/', 'title_hi' => 'कालसर्प दोष निवारण', 'title_en' => 'Kaal Sarp Dosh Check', 'type' => 'tool', 'kw_hi' => 'कालसर्प दोष, काल सर्प', 'kw_en' => 'kaal sarp dosh' ],
            [ 'url' => $site_base . '/panchang/', 'title_hi' => 'दैनिक हिंदू पंचांग', 'title_en' => 'Daily Hindu Panchang', 'type' => 'tool', 'kw_hi' => 'पंचांग, आज का पंचांग, तिथि, नक्षत्र', 'kw_en' => 'panchang, hindu panchang' ],
            [ 'url' => $site_base . '/panchang/abhijit-muhurat/', 'title_hi' => 'आज का अभिजित मुहूर्त', 'title_en' => 'Abhijit Muhurat', 'type' => 'tool', 'kw_hi' => 'अभिजित मुहूर्त, शुभ मुहूर्त', 'kw_en' => 'abhijit muhurat' ],
            [ 'url' => $site_base . '/daily-choghadiya-muhurat/', 'title_hi' => 'दैनिक चौघड़िया मुहूर्त', 'title_en' => 'Daily Choghadiya', 'type' => 'tool', 'kw_hi' => 'चौघड़िया, अमृत चौघड़िया, शुभ चौघड़िया', 'kw_en' => 'choghadiya, daily choghadiya' ],
            [ 'url' => $site_base . '/festivals/', 'title_hi' => 'हिंदू व्रत एवं त्योहार कैलेंडर', 'title_en' => 'Hindu Festivals Calendar', 'type' => 'tool', 'kw_hi' => 'त्योहार कैलेंडर, व्रत त्योहार, हिंदू व्रत', 'kw_en' => 'festivals calendar, hindu vrat' ],
            [ 'url' => $site_base . '/horoscope/', 'title_hi' => 'आज का राशिफल', 'title_en' => 'Daily Horoscope', 'type' => 'tool', 'kw_hi' => 'आज का राशिफल, दैनिक राशिफल, राशि फल', 'kw_en' => 'daily horoscope, rashifal' ],
        ];

        foreach ( $initial_links as $link ) {
            $wpdb->insert(
                $t_link_map,
                [
                    'url'         => $link['url'],
                    'title_hi'    => $link['title_hi'],
                    'title_en'    => $link['title_en'],
                    'link_type'   => $link['type'],
                    'keywords_hi' => $link['kw_hi'],
                    'keywords_en' => $link['kw_en'],
                    'is_active'   => 1,
                    'last_synced' => current_time( 'mysql' ),
                ],
                [ '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
            );
        }
    }
}
