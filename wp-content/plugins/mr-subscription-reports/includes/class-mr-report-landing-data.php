<?php
/**
 * Master Data Dictionary for All 9 Astrology Report Landing Pages
 * MeraRashifal Brand Design System
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Report_Landing_Data {

    /**
     * Map of URL slugs to internal report identifiers
     */
    public static function get_slug_map() {
        return [
            'yearly-horoscope-report'          => 'yearly_horoscope_long',
            'landingpage'                      => 'yearly_horoscope_long',
            'yearly-horoscope-snapshot-report' => 'yearly_horoscope_vedic',
            'janam-kundali-master-report'      => 'kundli_predictions',
            'dasha-timing-report'              => 'mahadasha_timing',
            'kundali-dosh-report'              => 'dosh_remedial',
            'kundali-and-gun-matching-report'  => 'kundli_matching',
            'numerology-report'                => 'numerology',
            'monthly-horoscope-report'         => 'monthly_horoscope_vedic',
            'palm-reading-report'              => 'palmistry_report',
        ];
    }

    /**
     * Retrieve complete report data by slug or internal key
     */
    public static function get($key_or_slug) {
        $map = self::get_slug_map();
        $key = $map[$key_or_slug] ?? $key_or_slug;
        $all = self::get_all();
        $data = $all[$key] ?? $all['yearly_horoscope_long'];

        return self::apply_live_catalog_sync($data, $key);
    }

    /**
     * Synchronize live pricing, discounts, and testimonials from Catalog Manager
     */
    public static function apply_live_catalog_sync($data, $key = '') {
        $slug = $data['report_slug'] ?? $key;

        if (class_exists('MR_Catalog_Manager')) {
            $cards = MR_Catalog_Manager::get_all_cards();
            $card = $cards[$slug] ?? ($cards[$key] ?? []);
            if (empty($card) && ($slug === 'numerology' || $key === 'numerology')) {
                $card = $cards['numerology'] ?? ($cards['numerology_report'] ?? []);
            }

            if (!empty($card)) {
                if (isset($card['price_sale'])) {
                    $data['price_sale'] = floatval($card['price_sale']);
                }
                if (isset($card['price_regular'])) {
                    $data['price_regular'] = floatval($card['price_regular']);
                }
                if (!empty($card['discount_text'])) {
                    $data['discount_text'] = $card['discount_text'];
                }
                if (!empty($card['testimonials']) && is_array($card['testimonials'])) {
                    $data['testimonials'] = $card['testimonials'];
                }
            }
        }

        // Secondary sync with mr_report_pricing_config
        $pricing_config = get_option('mr_report_pricing_config', []);
        $cfg = $pricing_config[$slug] ?? ($pricing_config[$key] ?? []);
        if (empty($cfg) && ($slug === 'numerology' || $key === 'numerology')) {
            $cfg = $pricing_config['numerology_report'] ?? ($pricing_config['numerology'] ?? []);
        }
        if (!empty($cfg) && isset($cfg['sale_price'])) {
            $data['price_sale'] = floatval($cfg['sale_price']);
            if (isset($cfg['list_price'])) {
                $data['price_regular'] = floatval($cfg['list_price']);
            }
        }

        return $data;
    }

    /**
     * All 9 Reports Configuration
     */
    public static function get_all() {
        $reports = [
            // =========================================================
            // 1. YEARLY HOROSCOPE DETAILED REPORT
            // =========================================================
            'yearly_horoscope_long' => [
                'report_slug'      => 'yearly_horoscope_long',
                'page_slug'        => 'yearly-horoscope-report',
                'code'             => 'MR_YHLR',
                'name'             => 'Yearly Horoscope Detailed Report (2026-2027)',
                'meta_title'       => 'Yearly Horoscope Detailed Report (2026-2027) | Vedic Blueprint',
                'meta_description' => 'Get your 26-page Vedic Varshaphal roadmap for 2026-2027. Accurate D1 & D9 charts, Shani Sade Sati audit, career & relationship forecasts, and Shastric Upayas.',
                'meta_keywords'    => 'yearly horoscope 2026, vedic varshaphal report, annual astrology forecast, sade sati audit, jupiter transit 2026, rahu ketu remedies, merarashifal',
                'eyebrow'          => 'Vedic Life Mapping • 2026-2027',
                'title_prefix'     => 'Yearly Horoscope',
                'title_highlight'  => 'Detailed Life Report',
                'hero_sub'         => 'A definitive, 26-page personal Vedic blueprint. We compute your exact Lagna, Moon Sign, and divisional charts (D1 & D9) across all 12 Bhavas, decoding the decisive transits of Jupiter (Guru), Saturn (Shani Sade Sati/Dhaiya), and Rahu-Ketu with customized classical remedies.',
                'badge'            => 'Comprehensive Edition',
                'cover_badge'      => 'Yearly Horoscope 2026 - 2027 Definitive Edition',
                'pages'            => '26 Pages',
                'price_sale'       => 999,
                'price_regular'    => 2499,
                'discount_text'    => '60% OFF Launch Special',
                'emblem_icon'      => 'fa-solid fa-sun',
                'highlights'       => [
                    'D1 Rashi & D9 Navamsha Charts',
                    'Saturn Sade Sati & Dhaiya Audit',
                    '4 Quarters Life Prediction',
                    'Vedic Gemstone & Mantra Remedies',
                ],
                'stats'            => [
                    'stat1_val' => '1,25,000+', 'stat1_lbl' => 'REPORTS GENERATED',
                    'stat2_val' => '4.9 / 5.0', 'stat2_lbl' => 'AVERAGE SEEKER RATING',
                    'stat3_val' => '< 5 Mins', 'stat3_lbl' => 'TO PREPARE & EMAIL',
                    'stat4_val' => '100% Unique', 'stat4_lbl' => 'YOUR CHART • NOT A TEMPLATE',
                ],
                'modules'          => [
                    [
                        'tag' => 'Chapter 01-04',
                        'title' => 'Ascendant & Planetary Dignity Audit',
                        'desc' => 'High-precision degrees of all 9 Grahas in D1 Lagna and D9 Navamsha. Identifies exalted, debilitated, and retrograde placements with Shadbala strength scores.',
                    ],
                    [
                        'tag' => 'Chapter 05-09',
                        'title' => 'Major Planetary Transits (2026-2027)',
                        'desc' => 'Decisive transit timelines of Jupiter through Taurus & Gemini, Saturn through Pisces, and the karmic Rahu-Ketu nodal shift. Exact date ranges of impact.',
                    ],
                    [
                        'tag' => 'Chapter 10-14',
                        'title' => 'Four-Quarter Life Spheres Deep Dive',
                        'desc' => 'Quarterly breakdowns across Career & Promotions, Wealth & Investments, Love & Family Harmony, and Vitality & Mental Well-being with opportunity scores.',
                    ],
                    [
                        'tag' => 'Chapter 15-18',
                        'title' => 'Vimshottari Dasha Activation Windows',
                        'desc' => 'Mapping your active Mahadasha, Antardasha, and Pratyantardasha cycles. Understand which Bhavas are currently ripe for breakthroughs or caution.',
                    ],
                    [
                        'tag' => 'Chapter 19-22',
                        'title' => 'Saturn Sade Sati & Karmic Debt Audit',
                        'desc' => 'Whether you are in the Rising, Peak, or Setting phase of Sade Sati or under Kantaka/Ashtama Shani. Pinpoints exact relief remedies and mitigation rituals.',
                    ],
                    [
                        'tag' => 'Chapter 23-26',
                        'title' => 'Classical Shastric Remedial Arsenal',
                        'desc' => 'Personalized gemstone recommendations with metal and finger guidelines, energizing Beej Mantras, charity timings, and fast-day guidance reviewed by our council.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Birth Chart & Astrological Blueprint', 'pages' => 'Pages 1 - 4', 'summary' => 'D1 Lagna Kundli, D9 Navamsha, Shadbala Potency, Planetary Degrees'],
                    ['title' => 'Macro Celestial Transits (2026-2027)', 'pages' => 'Pages 5 - 8', 'summary' => 'Guru, Shani, Rahu-Ketu Ingress Timelines & Natal House Aspects'],
                    ['title' => 'Quarterly Forecasts: Q1 & Q2', 'pages' => 'Pages 9 - 13', 'summary' => 'Career Acceleration, Financial Liquidity, Domestic Peace & Travel'],
                    ['title' => 'Quarterly Forecasts: Q3 & Q4', 'pages' => 'Pages 14 - 18', 'summary' => 'Strategic Consolidations, Relationship Turning Points & Health'],
                    ['title' => 'Active Vimshottari Dasha Trajectory', 'pages' => 'Pages 19 - 22', 'summary' => 'Mahadasha-Antardasha Synthesis & Precise Karmic Event Windows'],
                    ['title' => 'Classical Shastric Upayas & Remedial Blueprint', 'pages' => 'Pages 23 - 26', 'summary' => 'Gemstones, Vedic Mantras, Daan Muhurats & Wall Calendar'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-microchip', 'title' => 'Astronomical Mathematical Accuracy', 'desc' => 'Calculated with Swiss Ephemeris precision down to arc-seconds with topocentric parallax correction for true Vedic degrees.'],
                    ['icon' => 'fa-solid fa-compass', 'title' => 'Dual Chart Synthesis (D1 + D9)', 'desc' => 'We never rely on Rashi chart alone. Every prediction cross-verifies D9 Navamsha to guarantee root spiritual dignity.'],
                    ['icon' => 'fa-solid fa-feather-pointed', 'title' => 'Readable & Actionable Wisdom', 'desc' => 'No confusing jargon or fear tactics. Structured executive summaries that give you clear decision dates and calming solutions.'],
                ],
                'testimonials'     => [
                    ['quote' => 'The precision for my career transit in June 2026 was startling. It gave me the exact courage to negotiate my overseas move with absolute clarity.', 'name' => 'Vikramaditya S.', 'loc' => 'Bangalore, India', 'rating' => 5],
                    ['quote' => 'Most online horoscopes feel like copy-paste templates. MeraRashifal’s 26-page report felt like sitting across a wise family guru for 2 hours.', 'name' => 'Meera Nambiar', 'loc' => 'Dubai, UAE', 'rating' => 5],
                    ['quote' => 'The Sade Sati remedies and Dasha timing section brought immense peace to our home. The layout is beautiful, elegant, and deeply authentic.', 'name' => 'Anil K. Kulkarni', 'loc' => 'Pune, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'How is this report different from generic sun sign horoscopes?', 'a' => 'Generic horoscopes divide billions of people into 12 sun signs. This report computes your precise birth date, exact time, and geographic coordinates down to seconds. We construct your unique D1 Lagna Kundli and D9 Navamsha chart.'],
                    ['q' => 'What if I do not have my exact birth time?', 'a' => 'An approximate time within a 15-30 minute window still yields highly reliable transit, Dasha, and planet sign analysis. If completely unknown, our Vedic Council can assist with birth time rectification.'],
                    ['q' => 'How soon will I receive my report PDF?', 'a' => 'Immediately upon confirming your birth details, our cosmic engine compiles your report in real time. A sticky notification appears at the bottom of your screen with a direct link, and a copy is emailed from no-reply@merarashifal.com.'],
                    ['q' => 'Can I print this report for personal reference?', 'a' => 'Yes. Every report is generated as a vector print-ready PDF formatted to international A4 dimensions with elegant cream margins.'],
                ],
            ],

            // =========================================================
            // 2. YEARLY HOROSCOPE SNAPSHOT REPORT
            // =========================================================
            'yearly_horoscope_vedic' => [
                'report_slug'      => 'yearly_horoscope_vedic',
                'page_slug'        => 'yearly-horoscope-snapshot-report',
                'code'             => 'MR_YHSR',
                'name'             => 'Yearly Horoscope Snapshot Report (2026)',
                'meta_title'       => 'Free 2026 Yearly Horoscope Snapshot | Vedic Summary Report',
                'meta_description' => 'Download your free 5-page 2026 Vedic annual horoscope snapshot. Instant overview of planetary transits, auspicious dates, and key life timing windows.',
                'meta_keywords'    => 'free yearly horoscope, 2026 horoscope snapshot, free vedic astrology report, annual transit overview, rashifal 2026, merarashifal free report',
                'eyebrow'          => 'Vedic Starter Edition • 2026',
                'title_prefix'     => 'Yearly Horoscope',
                'title_highlight'  => 'Snapshot Summary Report',
                'hero_sub'         => 'An essential 5-page annual overview with key planetary transits, high-level life forecasts, and auspicious timing windows. Perfect for seekers who want instant, reliable astrological clarity at zero cost.',
                'badge'            => 'Free Starter Edition',
                'cover_badge'      => 'Yearly Horoscope 2026 Essential Snapshot',
                'pages'            => '5 Pages',
                'price_sale'       => 0,
                'price_regular'    => 499,
                'discount_text'    => '100% FREE Complimentary',
                'emblem_icon'      => 'fa-solid fa-calendar',
                'highlights'       => [
                    'Core Planetary Placements (D1)',
                    'Major Annual Transit Map (2026)',
                    'Favorable Career & Health Windows',
                    'Key Auspicious Dates & Upay',
                ],
                'stats'            => [
                    'stat1_val' => '2,40,000+', 'stat1_lbl' => 'FREE DOWNLOADS',
                    'stat2_val' => '4.8 / 5.0', 'stat2_lbl' => 'AVERAGE RATING',
                    'stat3_val' => '< 2 Mins', 'stat3_lbl' => 'INSTANT COMPILATION',
                    'stat4_val' => 'Zero Cost', 'stat4_lbl' => 'NO PAYMENT NEEDED',
                ],
                'modules'          => [
                    [
                        'tag' => 'Page 01',
                        'title' => 'Ascendant & Core Planetary Placements',
                        'desc' => 'Instant computation of your Rising Sign (Lagna), Moon Sign (Rashi), Nakshatra Pada, and dominant elemental balance.',
                    ],
                    [
                        'tag' => 'Page 02',
                        'title' => '2026 Major Transit Highlights',
                        'desc' => 'High-level synthesis of Jupiter (Guru) and Saturn (Shani) transits and how they cast their planetary Drishti on your chart.',
                    ],
                    [
                        'tag' => 'Page 03',
                        'title' => 'Career & Wealth Trend Pulse',
                        'desc' => 'Clear executive guidance on whether 2026 favors career advancement, caution in speculative markets, or financial stability.',
                    ],
                    [
                        'tag' => 'Page 04',
                        'title' => 'Relationships & Vitality Outlook',
                        'desc' => 'Overview of domestic atmosphere, emotional well-being, and general physical endurance across the twelve months.',
                    ],
                    [
                        'tag' => 'Page 05',
                        'title' => 'Auspicious Days & Starter Upayas',
                        'desc' => 'Curated list of your lucky days of the week, favorable colors, and a primary universal mantra for peace and protection.',
                    ],
                    [
                        'tag' => 'Bonus',
                        'title' => 'Upgrade Path to Full 26-Page Dossier',
                        'desc' => 'Includes a summary preview of the D9 Navamsha and deep dasha chapters available in the Comprehensive Edition.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Birth Chart Profile & Nakshatra', 'pages' => 'Page 1', 'summary' => 'Lagna, Moon Sign, Planetary Rashi Table'],
                    ['title' => 'Major 2026 Planetary Transits', 'pages' => 'Page 2', 'summary' => 'Guru & Shani transit influences on your Bhavas'],
                    ['title' => 'Professional & Financial Outlook', 'pages' => 'Page 3', 'summary' => 'Growth windows, caution periods, and stability score'],
                    ['title' => 'Relationships & Health Summary', 'pages' => 'Page 4', 'summary' => 'Family harmony, stamina, and emotional balance'],
                    ['title' => 'Lucky Vibrations & Daily Upay', 'pages' => 'Page 5', 'summary' => 'Favorable colors, gemstones intro, and daily mantra'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-gift', 'title' => '100% Free With Zero Ads', 'desc' => 'A dignified, high-quality starter report without cluttered ads, intrusive popups, or fake upsells.'],
                    ['icon' => 'fa-solid fa-bolt', 'title' => 'Instant Real-Time Generation', 'desc' => 'Generated in seconds using genuine Vedic ephemeris computation.'],
                    ['icon' => 'fa-solid fa-certificate', 'title' => 'Vedic Council Certified', 'desc' => 'Verified calculations adhering to Brihat Parashara Hora Shastra principles.'],
                ],
                'testimonials'     => [
                    ['quote' => 'I could not believe this quality was provided for free. The Nakshatra analysis and 2026 transit summary were spot on.', 'name' => 'Pooja Iyer', 'loc' => 'Chennai, India', 'rating' => 5],
                    ['quote' => 'Simple, fast, and very accurate. It gave me a quick reality check on 2026 without having to read a hundred pages.', 'name' => 'Rohan Mehta', 'loc' => 'Mumbai, India', 'rating' => 5],
                    ['quote' => 'Clean design and beautiful PDF delivery directly to my email. Truly represents the premium quality of MeraRashifal.', 'name' => 'Siddharth Roy', 'loc' => 'Kolkata, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'What is covered in this Yearly Snapshot Report?', 'a' => 'This snapshot delivers your core natal planetary placements, major 2026 transit impacts, favorable life timing windows, and essential Vedic Upayas in a focused 5-page summary.'],
                    ['q' => 'Can I upgrade to the full 26-page report later?', 'a' => 'Yes! The snapshot provides the essential macro view. Whenever you want deep divisional charts, Sade Sati audit, and quarter-by-quarter roadmaps, you can order the Detailed Report.'],
                    ['q' => 'Will I receive the report via email?', 'a' => 'Yes, your report is synthesized in real time and delivered directly to your screen and registered email address within minutes.'],
                    ['q' => 'What if I do not know my exact time of birth?', 'a' => 'If your exact birth minute is uncertain, our system evaluates your Moon Chart (Chandra Kundali) and solar noon coordinates for high-accuracy forecasts.'],
                ],
            ],

            // =========================================================
            // 3. MASTER JANMA KUNDALI & HARMONICS REPORT
            // =========================================================
            'kundli_predictions' => [
                'report_slug'      => 'kundli_predictions',
                'page_slug'        => 'janam-kundali-master-report',
                'code'             => 'MR_JKHR',
                'name'             => 'Master Janma Kundali & Harmonics Report',
                'meta_title'       => 'Master Janma Kundali & 16 Shodashvarga Harmonics Report | Complete Vedic Life Analysis',
                'meta_description' => 'Comprehensive 26-page Janam Kundali analysis decoding all 16 divisional charts (Shodashvarga), Shadbala planetary potency, Raj Yogas, Dosha audits, and lifetime karmic trajectory.',
                'meta_keywords'    => 'janam kundali report, shodashvarga charts, d9 navamsha, d10 dasamsa, shadbala analysis, raj yoga report, vedic birth chart analysis, merarashifal kundli',
                'eyebrow'          => 'Foundational Vedic Soul Blueprint',
                'title_prefix'     => 'Master Janma Kundali',
                'title_highlight'  => '& 16 Harmonics Report',
                'hero_sub'         => 'The definitive astrological master record of your soul’s incarnation. An exhaustive 26-page Shastric audit decoding all 16 divisional charts (Shodashvarga), Shadbala planetary potency, auspicious Yogas, and lifetime karmic milestones.',
                'badge'            => 'Master Publication Edition',
                'cover_badge'      => 'Master Janma Kundali 16 Shodashvarga Edition',
                'pages'            => '26 Pages',
                'price_sale'       => 99,
                'price_regular'    => 999,
                'discount_text'    => 'Save 90% Special',
                'emblem_icon'      => 'fa-solid fa-chart-pie',
                'highlights'       => [
                    'Full 16 Shodashvarga Divisional Charts',
                    'Mathematical Shadbala & Bhava Bala',
                    'Exhaustive Raja & Dhana Yoga Audit',
                    'Lifetime Karmic Destiny Roadmap',
                ],
                'stats'            => [
                    'stat1_val' => '1,80,000+', 'stat1_lbl' => 'KUNDALIS CAST',
                    'stat2_val' => '16 Charts', 'stat2_lbl' => 'FULL SHODASHVARGA',
                    'stat3_val' => '99.9%', 'stat3_lbl' => 'EPHEMERIS PRECISION',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'SEEKER SATISFACTION',
                ],
                'modules'          => [
                    [
                        'tag' => 'Module 01',
                        'title' => 'Lagna & Planetary Dignity Matrix',
                        'desc' => 'Exact mathematical degrees, Retrograde status, Combust planets, Avasthas (states of existence), and Bhava Sandhi cusps.',
                    ],
                    [
                        'tag' => 'Module 02',
                        'title' => '16 Shodashvarga Harmonic Charts',
                        'desc' => 'D1 (Body), D2 (Wealth), D3 (Siblings), D7 (Children), D9 (Spouse & Soul), D10 (Career), D12 (Ancestors), up to D60 (Shashtiamsha Karma).',
                    ],
                    [
                        'tag' => 'Module 03',
                        'title' => 'Shadbala & Planetary Potency Scores',
                        'desc' => 'Calculates Positional (Sthana), Directional (Dig), Temporal (Kala), Motional (Chesta), and Natural (Naisargika) planetary strengths.',
                    ],
                    [
                        'tag' => 'Module 04',
                        'title' => 'Auspicious Yogas & Fortunes',
                        'desc' => 'Pinpoints Gajakesari, Budhaditya, Pancha Mahapurusha, Vipareeta Raja Yogas, and Dhana Yogas active in your chart.',
                    ],
                    [
                        'tag' => 'Module 05',
                        'title' => '12 Bhavas Comprehensive Life Audit',
                        'desc' => 'Analysis of all 12 houses governing Identity, Wealth, Siblings, Real Estate, Intelligence, Debt/Enemies, Marriage, Longevity, Luck, Profession, Gains, and Moksha.',
                    ],
                    [
                        'tag' => 'Module 06',
                        'title' => 'Lifetime Shastric Upayas',
                        'desc' => 'Authentic gemstones, Vedic Stotras, Rudraksha recommendations, and temple pilgrimage coordinates tailored to your Ishta Devata.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Planetary Coordinates & Lagna Kundli', 'pages' => 'Pages 1 - 4', 'summary' => 'Mathematical degrees, Nakshatra Padas, and basic chart topography'],
                    ['title' => 'Complete 16 Divisional Charts (Shodashvarga)', 'pages' => 'Pages 5 - 10', 'summary' => 'D1 through D60 harmonic divisional charts with dignities'],
                    ['title' => 'Shadbala Potency & Bhava Bala Matrix', 'pages' => 'Pages 11 - 14', 'summary' => '6-fold mathematical strength scoring for all 9 planets'],
                    ['title' => 'Planetary Yogas & Special Combinations', 'pages' => 'Pages 15 - 18', 'summary' => 'Raja Yogas, Dhana Yogas, and obstacles identification'],
                    ['title' => 'Comprehensive 12 Bhavas Lifetime Audit', 'pages' => 'Pages 19 - 23', 'summary' => 'Exhaustive reading of every life house and associated karmas'],
                    ['title' => 'Ishta Devata, Gemstone & Shastric Remedies', 'pages' => 'Pages 24 - 26', 'summary' => 'Lifetime protective Upayas, fasting days, and auspicious rituals'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-scroll', 'title' => 'True Parashari Methodology', 'desc' => 'Adheres to Sage Parashara’s canonical computational formulas without modern distortions.'],
                    ['icon' => 'fa-solid fa-chart-line', 'title' => 'Full 16 Harmonic Divisions', 'desc' => 'While most sites stop at D1 or D9, we compute the full Shodashvarga including D10 for profession and D60 for past-life karma.'],
                    ['icon' => 'fa-solid fa-gem', 'title' => 'Permanent Lifetime Reference', 'desc' => 'A timeless master record you can refer back to across every major life transition.'],
                ],
                'testimonials'     => [
                    ['quote' => 'The D10 Dasamsa and Shadbala sections revealed precisely why my career took a massive leap in finance. Invaluable document.', 'name' => 'Aditya Kashyap', 'loc' => 'New Delhi, India', 'rating' => 5],
                    ['quote' => 'Having all 16 divisional charts in one beautifully bound PDF is a treasure. The Vedic remedies are gentle, satvik, and powerful.', 'name' => 'Sunita Aggarwal', 'loc' => 'Jaipur, India', 'rating' => 5],
                    ['quote' => 'I keep a printed copy in my puja room. It is the most comprehensive kundali report I have ever seen in 30 years of astrology study.', 'name' => 'Dr. K. Ramanathan', 'loc' => 'Bengaluru, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'What is the Shodashvarga?', 'a' => 'Shodashvarga refers to the 16 divisional charts prescribed by Sage Parashara. Each chart inspects a specific micro-dimension of human life: D9 for marriage and inner strength, D10 for profession and status, D7 for children, D12 for lineage, and D60 for past-life karma.'],
                    ['q' => 'Does this report include remedies?', 'a' => 'Yes, a complete 3-page dedicated remedial section detailing gemstones, favorable metals, Beej mantras, and Ishta Devata worship.'],
                    ['q' => 'Can I share this report with my local astrologer?', 'a' => 'Absolutely. The ephemeris data, bhava tables, and divisional charts follow standard Shastric conventions that any qualified Jyotishi will appreciate.'],
                ],
            ],

            // =========================================================
            // 4. MASTER VEDIC DASHA LIFE TIMING REPORT
            // =========================================================
            'mahadasha_timing' => [
                'report_slug'      => 'mahadasha_timing',
                'page_slug'        => 'dasha-timing-report',
                'code'             => 'MR_VDLT',
                'name'             => 'Master Vedic Dasha Life Timing Report',
                'meta_title'       => 'Master Vedic Dasha Life Timing Report | Vimshottari Mahadasha & Antardasha Analysis',
                'meta_description' => 'Exhaustive 26-page life timing analysis decoding your active Vimshottari Mahadasha, Antardasha, and Pratyantardasha cycles. Pinpoint precise career promotions, marriage, wealth, and transition phases.',
                'meta_keywords'    => 'vimshottari dasha report, mahadasha antardasha timing, life timing astrology, dasha remedies, planetary periods, career timing astrology, merarashifal dasha',
                'eyebrow'          => 'Vedic Time Dimension & Life Cycles',
                'title_prefix'     => 'Master Vedic Dasha',
                'title_highlight'  => 'Life Timing Report',
                'hero_sub'         => 'Astrology is not just what will happen, but WHEN it will happen. An exhaustive 26-page chronological audit of your 120-year Vimshottari Dasha cycles, pinpointing your active Mahadasha, Antardasha, and Pratyantardasha transition windows.',
                'badge'            => 'Timing Mastery Edition',
                'cover_badge'      => 'Master Vedic Dasha Chronological Timing Edition',
                'pages'            => '26 Pages',
                'price_sale'       => 99,
                'price_regular'    => 999,
                'discount_text'    => 'Save 90% Special',
                'emblem_icon'      => 'fa-solid fa-clock',
                'highlights'       => [
                    'Complete 120-Year Vimshottari Dasha Table',
                    'Active Mahadasha & Antardasha Deep Dive',
                    'Dasha Sandhi (Transition Periods) Audit',
                    'Remedies for Difficult Planetary Periods',
                ],
                'stats'            => [
                    'stat1_val' => '120 Years', 'stat1_lbl' => 'LIFETIME DASHA TABLE',
                    'stat2_val' => '3 Levels', 'stat2_lbl' => 'MAHA / ANTAR / PRATYANTAR',
                    'stat3_val' => 'Exact Dates', 'stat3_lbl' => 'TO THE EXACT DAY',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'SEEKER SATISFACTION',
                ],
                'modules'          => [
                    [
                        'tag' => 'Section 01',
                        'title' => 'The Vimshottari 120-Year Chronology',
                        'desc' => 'Complete chronological table of all 9 planetary periods from birth to age 120, calibrated to your birth Moon Nakshatra balance.',
                    ],
                    [
                        'tag' => 'Section 02',
                        'title' => 'Current Active Mahadasha Synthesis',
                        'desc' => 'Deep analysis of the ruling Mahadasha lord: its house rulership, functional nature, planetary friends, and primary psychological orientation.',
                    ],
                    [
                        'tag' => 'Section 03',
                        'title' => 'Active & Upcoming Antardasha Periods',
                        'desc' => 'Month-by-month breakdown of current and upcoming sub-periods. Discover when opportunities unlock and when patience is required.',
                    ],
                    [
                        'tag' => 'Section 04',
                        'title' => 'Dasha Sandhi & Karmic Junctions',
                        'desc' => 'Critical analysis of the transitional junction between two major dashas (e.g. Rahu to Jupiter or Saturn to Mercury), where life pivots occur.',
                    ],
                    [
                        'tag' => 'Section 05',
                        'title' => 'Transit & Dasha Overlap (Double Transit)',
                        'desc' => 'How major transits of Guru and Shani interact with your active dasha lord to trigger major tangible milestones (marriage, child, property).',
                    ],
                    [
                        'tag' => 'Section 06',
                        'title' => 'Dasha-Specific Upayas & Protections',
                        'desc' => 'Customized remedial practices, stotras, and daan targeted specifically to appease and strengthen the current ruling dasha planet.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Vimshottari Dasha Mathematical Chronology', 'pages' => 'Pages 1 - 5', 'summary' => '120-year complete date roadmap with planetary balances'],
                    ['title' => 'Current Mahadasha Lord Diagnosis', 'pages' => 'Pages 6 - 9', 'summary' => 'Macro energetic environment, life themes, and priorities'],
                    ['title' => 'Detailed Antardasha Breakdown (Next 3 Years)', 'pages' => 'Pages 10 - 15', 'summary' => 'Exact dates for each sub-period with career & relationship scores'],
                    ['title' => 'Dasha Sandhi & Life Turning Points', 'pages' => 'Pages 16 - 19', 'summary' => 'Precautions and strategic pivots during cycle transitions'],
                    ['title' => 'Transit Co-Activation & Milestone Timing', 'pages' => 'Pages 20 - 23', 'summary' => 'Guru & Shani double-transit alignment with dasha lords'],
                    ['title' => 'Remedial Blueprint for Current Period', 'pages' => 'Pages 24 - 26', 'summary' => 'Specific mantras, fasts, and gemstone balancing rituals'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-hourglass-half', 'title' => 'Precise Micro-Timing', 'desc' => 'We calculate down to Pratyantardasha (sub-sub period) to give you actionable timing windows, not vague generalities.'],
                    ['icon' => 'fa-solid fa-bridge-water', 'title' => 'Dasha Sandhi Guidance', 'desc' => 'Most astrologers miss the critical transition period between dashas. We provide explicit guidance on how to navigate the shift.'],
                    ['icon' => 'fa-solid fa-shield-heart', 'title' => 'Period-Specific Solutions', 'desc' => 'Remedies designed specifically for your current dasha lord, ensuring maximum spiritual efficacy.'],
                ],
                'testimonials'     => [
                    ['quote' => 'My transition from Rahu to Jupiter Dasha was causing immense confusion. This report explained the exact Dasha Sandhi timeline and gave me tremendous reassurance.', 'name' => 'Gaurav Singhania', 'loc' => 'Mumbai, India', 'rating' => 5],
                    ['quote' => 'The dates predicted for my promotion during Saturn-Mercury Antardasha were accurate within three weeks. Remarkable timing tool.', 'name' => 'Deepak Varma', 'loc' => 'Hyderabad, India', 'rating' => 5],
                    ['quote' => 'Understanding which planet is currently driving my life changed how I make decisions. High return on investment.', 'name' => 'Ananya Sen', 'loc' => 'Singapore', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'What is Vimshottari Dasha?', 'a' => 'Vimshottari Dasha is the premier predictive timing system of Vedic astrology. It maps a 120-year cycle where different planets take stewardship of your consciousness, releasing karmic fruits in a predetermined divine sequence.'],
                    ['q' => 'Can a difficult dasha be improved with remedies?', 'a' => 'Yes. While the planetary cycle delivers lessons, authentic Shastric remedies (Mantra Japa, Daan, specific fasts, and gemstones) soften challenging friction and amplify positive potential.'],
                    ['q' => 'Does this report cover past periods?', 'a' => 'Yes, it provides the full lifetime 120-year chronology from the day of your birth so you can verify past milestones.'],
                ],
            ],

            // =========================================================
            // 5. MASTER KUNDALI DOSH & REMEDIAL AUDIT REPORT
            // =========================================================
            'dosh_remedial' => [
                'report_slug'      => 'dosh_remedial',
                'page_slug'        => 'kundali-dosh-report',
                'code'             => 'MR_KDRA',
                'name'             => 'Master Kundali Dosh & Remedial Audit Report',
                'meta_title'       => 'Master Kundali Dosh & Remedial Audit Report | Manglik, Kaal Sarp, Pitra & Sade Sati',
                'meta_description' => 'Clinical 20-page Vedic dosha audit identifying Manglik Dosh, Kaal Sarp Dosh, Pitra Rin, Kemdrum, and Shani Sade Sati with authenticated Shastric Upayas, gemstone guidance, and Vedic rituals.',
                'meta_keywords'    => 'kundali dosh report, manglik dosh check, kaal sarp dosh remedies, pitra dosh nivaran, sade sati remedies, vedic astrological remedies, dosha audit merarashifal',
                'eyebrow'          => 'Karmic Diagnosis & Shastric Relief',
                'title_prefix'     => 'Master Kundali Dosh',
                'title_highlight'  => '& Remedial Audit Report',
                'hero_sub'         => 'A clinical 20-page diagnostic examination of karmic afflictions in your chart. We verify the presence, intensity, and cancellation (Bhanga) of Manglik, Kaal Sarp, Pitra, Guru Chandal, Kemdrum, and Sade Sati with authentic, non-fear-based Vedic remedies.',
                'badge'            => 'Diagnostic & Remedial Edition',
                'cover_badge'      => 'Master Kundali Dosh Clinical Remedial Audit',
                'pages'            => '20 Pages',
                'price_sale'       => 99,
                'price_regular'    => 799,
                'discount_text'    => 'Save 88% Special',
                'emblem_icon'      => 'fa-solid fa-shield-halved',
                'highlights'       => [
                    'Manglik Dosh & High/Low Intensity Audit',
                    '12 Types of Kaal Sarp Yoga Analysis',
                    'Pitra Dosh, Kemdrum & Chandal Checks',
                    'Authenticated Shastric Upayas & Rituals',
                ],
                'stats'            => [
                    'stat1_val' => '10+ Doshas', 'stat1_lbl' => 'RIGOROUSLY AUDITED',
                    'stat2_val' => 'Full Bhanga', 'stat2_lbl' => 'CANCELLATION RULES CHECK',
                    'stat3_val' => 'Zero Fear', 'stat3_lbl' => 'AUTHENTIC SATVIK SOLUTIONS',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'PEACE OF MIND RATING',
                ],
                'modules'          => [
                    [
                        'tag' => 'Audit 01',
                        'title' => 'Kuja (Manglik) Dosh Evaluation',
                        'desc' => 'Evaluates Mars in 1st, 2nd, 4th, 7th, 8th, and 12th houses from Lagna, Moon, and Venus. Checks all 18 classical Parashari cancellation (Bhanga) exceptions.',
                    ],
                    [
                        'tag' => 'Audit 02',
                        'title' => 'Kaal Sarp Yoga Diagnostic',
                        'desc' => 'Identifies whether all 7 planets are hemmed between Rahu and Ketu, determining the exact type (Anant, Kulik, Vasuki, Shankhpal, etc.) and intensity.',
                    ],
                    [
                        'tag' => 'Audit 03',
                        'title' => 'Pitra Rin & Ancestral Karmic Debts',
                        'desc' => 'Afflictions to Sun and 9th house indicating unfulfilled ancestral obligations, with specific Tarpana, cow service, and Amavasya guidance.',
                    ],
                    [
                        'tag' => 'Audit 04',
                        'title' => 'Kemdrum & Chandrama Afflictions',
                        'desc' => 'Isolation of the Moon with no planets in 2nd or 12th houses, causing mental anxiety or financial fluctuations, and its cancellations.',
                    ],
                    [
                        'tag' => 'Audit 05',
                        'title' => 'Guru Chandal & Grahan Dosh',
                        'desc' => 'Jupiter-Rahu conjunctions or Sun/Moon eclipse conjunctions causing career or ideological instability, with satvik Vedic remedies.',
                    ],
                    [
                        'tag' => 'Audit 06',
                        'title' => 'The Complete Shastric Remedy Blueprint',
                        'desc' => 'Safe, authenticated, peaceful satvik solutions: precise Japa counts, appropriate gemstones, metal yantras, fast-day rules, and charity protocols.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Diagnostic Summary & Severity Matrix', 'pages' => 'Pages 1 - 3', 'summary' => 'Comprehensive executive overview of all audited doshas'],
                    ['title' => 'Manglik Dosh & Cancellation Rules', 'pages' => 'Pages 4 - 7', 'summary' => 'Mars placement from 3 vantage points with Bhanga verification'],
                    ['title' => 'Kaal Sarp Yoga Classification', 'pages' => 'Pages 8 - 11', 'summary' => 'Rahu-Ketu axis analysis and timeline of resolution'],
                    ['title' => 'Pitra Rin, Kemdrum & Minor Afflictions', 'pages' => 'Pages 12 - 15', 'summary' => 'Ancestral karma, Moon isolation, and Chandal assessments'],
                    ['title' => 'Saturn Sade Sati & Dhaiya Mitigation', 'pages' => 'Pages 16 - 18', 'summary' => 'Current Shani status and specific pacification protocols'],
                    ['title' => 'Prescribed Shastric Remedies & Ritual Guide', 'pages' => 'Pages 19 - 20', 'summary' => 'Exact mantras, gemstone parameters, charity, and fast days'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-heart-circle-check', 'title' => 'Zero Fear-Mongering', 'desc' => 'Most commercial platforms exaggerate doshas to sell expensive rituals. We apply strict classical cancellation rules; over 70% of apparent doshas are cancelled.'],
                    ['icon' => 'fa-solid fa-scale-balanced', 'title' => 'Exhaustive Bhanga Analysis', 'desc' => 'We check all 18 Parashari cancellation conditions before declaring any dosha active.'],
                    ['icon' => 'fa-solid fa-hands-praying', 'title' => 'Practical Satvik Upayas', 'desc' => 'Our remedies are peaceful, accessible, and grounded in authentic classical scripture.'],
                ],
                'testimonials'     => [
                    ['quote' => 'Local pundits terrified me by claiming I had severe Manglik dosh. MeraRashifal’s report demonstrated that Mars in Aries cancels the dosh completely. What a relief!', 'name' => 'Divya Sharma', 'loc' => 'Lucknow, India', 'rating' => 5],
                    ['quote' => 'The Kaal Sarp and Pitra dosh audit gave our family clear, actionable Amavasya rituals that brought measurable peace of mind.', 'name' => 'Pradeep Mathur', 'loc' => 'Chandigarh, India', 'rating' => 5],
                    ['quote' => 'Objective, calm, and scientific. Exactly how Vedic astrology should be practiced.', 'name' => 'Rajeshwari Rao', 'loc' => 'Hyderabad, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'Is Manglik Dosh always harmful?', 'a' => 'No. In classical Parashari astrology, there are numerous cancellation (Bhanga) rules. If Mars is in its own sign, exalted, or aspected by Jupiter, the negative effects are neutralized.'],
                    ['q' => 'Do I need to visit expensive temples for dosha nivaran?', 'a' => 'In the majority of cases, regular satvik household remedies (Mantra Japa, feeding cows/birds, specific fasts, and charitable deeds) are entirely sufficient and spiritually recommended.'],
                    ['q' => 'Can this report be used to check compatibility?', 'a' => 'Yes. Understanding your personal doshas is the first step before proceeding with relationship or marriage compatibility.'],
                ],
            ],

            // =========================================================
            // 6. MASTER KUNDALI MATCHING REPORT
            // =========================================================
            'kundli_matching' => [
                'report_slug'      => 'kundli_matching',
                'page_slug'        => 'kundali-and-gun-matching-report',
                'code'             => 'MR_KMMR',
                'name'             => 'Master Kundali Matching (Gun Milan) Report',
                'meta_title'       => 'Master Kundali Matching & 36 Gun Milan Report | Vedic Marriage Compatibility Audit',
                'meta_description' => 'Definitive 24-page relationship & marriage audit: 36 Ashtakoot Gun Milan, Manglik balance check, Bhakoot & Nadi dosha assessment, longevity, emotional harmony, and remedial solutions.',
                'meta_keywords'    => 'kundali matching report, 36 gun milan, marriage compatibility horoscope, nadi dosh remedies, manglik matching, boy girl horoscope match, merarashifal matching',
                'eyebrow'          => 'Sacred Marriage Compatibility & Harmony',
                'title_prefix'     => 'Master Kundali Matching',
                'title_highlight'  => '& 36 Gun Milan Report',
                'hero_sub'         => 'A comprehensive 24-page relationship audit combining classical 36-point Ashtakoot Gun Milan, Manglik balance matching, Bhakoot and Nadi dosha analysis, longevity balance, and emotional-financial harmony projections for the prospective couple.',
                'badge'            => 'Relationship & Vivah Edition',
                'cover_badge'      => 'Master Kundali Matching & Gun Milan Comprehensive Dossier',
                'pages'            => '24 Pages',
                'price_sale'       => 99,
                'price_regular'    => 1499,
                'discount_text'    => 'Save 93% Special',
                'emblem_icon'      => 'fa-solid fa-ring',
                'highlights'       => [
                    '36 Points Ashtakoot Milan Matrix',
                    'Manglik Balance & Cancellation Audit',
                    'Nadi & Bhakoot Dosha Remedial Analysis',
                    'Longevity, Progeny & Financial Harmony',
                ],
                'stats'            => [
                    'stat1_val' => '36 Gunas', 'stat1_lbl' => 'FULL ASHTAKOOT AUDIT',
                    'stat2_val' => '2 Charts', 'stat2_lbl' => 'BOY & GIRL SYNTHESIS',
                    'stat3_val' => '24 Pages', 'stat3_lbl' => 'EXHAUSTIVE ANALYSIS',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'COUPLES HAPPINESS',
                ],
                'modules'          => [
                    [
                        'tag' => 'Module 01',
                        'title' => 'Ashtakoot 36-Gun Scorecard',
                        'desc' => 'Varna (1), Vashya (2), Tara (3), Yoni (4), Graha Maitri (5), Gana (6), Bhakoot (7), and Nadi (8). Detailed point breakdown with benchmarks.',
                    ],
                    [
                        'tag' => 'Module 02',
                        'title' => 'Manglik Dosh Balance (Papasamaya)',
                        'desc' => 'Checks whether the boy and girl have matching Mars intensity. Explains if an imbalance exists or if planetary cancellations harmonize the union.',
                    ],
                    [
                        'tag' => 'Module 03',
                        'title' => 'Nadi Dosh & Genetic Harmony',
                        'desc' => 'Deep dive into Adi, Madhya, and Antya Nadi compatibility with traditional exceptions and satvik remedial protocols.',
                    ],
                    [
                        'tag' => 'Module 04',
                        'title' => 'Bhakoot & Emotional Resonance',
                        'desc' => 'Examines the relative Moon signs (2/12, 6/8, 9/5 positions) to forecast emotional rapport, domestic peace, and longevity of affection.',
                    ],
                    [
                        'tag' => 'Module 05',
                        'title' => 'Financial & Progeny Projections',
                        'desc' => 'Cross-analysis of 2nd (family wealth), 5th (children), 7th (partnership), and 11th (gains) houses for shared prosperity.',
                    ],
                    [
                        'tag' => 'Module 06',
                        'title' => 'Pre-Marriage Shastric Remedies',
                        'desc' => 'Sacred mantras for couple harmony, auspicious wedding muhurat selection guidelines, and remedial offerings for mutual blessings.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Couples Astrological Overview', 'pages' => 'Pages 1 - 3', 'summary' => 'Boy & Girl Lagna, Moon Sign, Nakshatra, and basic coordinates'],
                    ['title' => 'Ashtakoot 36-Gun Milan Breakdown', 'pages' => 'Pages 4 - 8', 'summary' => 'Individual scoring across all 8 Kootas with qualitative interpretation'],
                    ['title' => 'Manglik Dosh Cross-Matching & Load Balance', 'pages' => 'Pages 9 - 12', 'summary' => 'Mars placement verification and mutual cancellation checks'],
                    ['title' => 'Critical Doshas: Nadi & Bhakoot Analysis', 'pages' => 'Pages 13 - 16', 'summary' => 'Health, emotional, and progeny harmony assessments'],
                    ['title' => 'Prosperity, Career & Domestic Life Forecast', 'pages' => 'Pages 17 - 21', 'summary' => 'Combined financial trajectory and relationship dynamics'],
                    ['title' => 'Remedial Roadmap & Vivah Muhurat Advice', 'pages' => 'Pages 22 - 24', 'summary' => 'Shared mantras, gemstone advice, and pre-wedding rituals'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-user-group', 'title' => 'Dual Chart Parashari Synthesis', 'desc' => 'We don’t just count gunas; we analyze the 7th house and Venus/Jupiter dignity in both individual horoscopes.'],
                    ['icon' => 'fa-solid fa-heart-pulse', 'title' => 'Nuanced Compatibility Insight', 'desc' => 'A couple with 22 gunas can thrive with strong Graha Maitri, while 30 gunas with severe Nadi dosh needs remedies. We explain the truth.'],
                    ['icon' => 'fa-solid fa-dove', 'title' => 'Constructive Relationship Advice', 'desc' => 'Empowering and compassionate insights that build mutual understanding and lifelong companionship.'],
                ],
                'testimonials'     => [
                    ['quote' => 'Our score was 24, but the report clearly explained that our Graha Maitri and Bhakoot were exceptional. We got married with complete confidence and couldn’t be happier.', 'name' => 'Nikhil & Shruti', 'loc' => 'Mumbai, India', 'rating' => 5],
                    ['quote' => 'The 2 sets of form inputs made it so simple to enter both our birth details. The 24-page PDF was shared with both our parents who loved it.', 'name' => 'Harsh Vardhan', 'loc' => 'Delhi NCR', 'rating' => 5],
                    ['quote' => 'The Nadi dosh explanation was the clearest I have ever read. It removed all anxiety from both families.', 'name' => 'Kavita Menon', 'loc' => 'Kochi, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'How many Gunas are required for a good match?', 'a' => 'Traditionally, 18 out of 36 gunas is considered the minimum threshold, while 24 to 32 is deemed very good. However, our report also inspects 7th house dignity and Manglik balance, which are equally critical.'],
                    ['q' => 'What if Nadi Dosh is present?', 'a' => 'Nadi Dosh has multiple classical exceptions depending on Nakshatra padas, Moon signs, or ruling lords. Our report verifies these exceptions and provides traditional satvik remedies if needed.'],
                    ['q' => 'Do I need exact birth details for both partners?', 'a' => 'Yes, for the most accurate 36 Gun Milan and planetary cross-analysis, exact birth date, time, and city for both the boy and the girl are essential.'],
                ],
            ],

            // =========================================================
            // 7. MASTER NUMEROLOGY & VEDIC INTEGRATION REPORT
            // =========================================================
            'numerology' => [
                'report_slug'      => 'numerology',
                'page_slug'        => 'numerology-report',
                'code'             => 'MR_NMVI',
                'name'             => 'Master Numerology & Vedic Integration Report',
                'meta_title'       => 'Master Numerology & Vedic Integration Report | Lo Shu Grid & Sankhya Shastra',
                'meta_description' => 'Comprehensive 22-page Sankhya Shastra audit integrating Lo Shu 3x3 grid, Life Path, Destiny, Soul Urge numbers, 8-1-6 Practical Arrow, name vibration alignment, and 90-day action roadmap.',
                'meta_keywords'    => 'numerology report, lo shu grid analysis, life path number, destiny number, name numerology, sankhya shastra, vedic numerology report, merarashifal',
                'eyebrow'          => 'Vedic Sacred Mathematics & Lo Shu Grid',
                'title_prefix'     => 'Master Numerology',
                'title_highlight'  => '& Vedic Integration Report',
                'hero_sub'         => 'A deep 22-page Sankhya Shastra audit decoding the hidden mathematical frequencies of your life. Combines Pythagorean, Chaldean, and Vedic numerology with complete Lo Shu 3x3 grid energy planes, name vibration corrections, and a 90-day action roadmap.',
                'badge'            => 'Sankhya Shastra Edition',
                'cover_badge'      => 'Master Numerology & Lo Shu Grid Integration Dossier',
                'pages'            => '22 Pages',
                'price_sale'       => 99,
                'price_regular'    => 599,
                'discount_text'    => 'Save 83% Special',
                'emblem_icon'      => 'fa-solid fa-star',
                'highlights'       => [
                    'Complete 3x3 Lo Shu Energy Grid Analysis',
                    'Life Path, Destiny & Soul Urge Numbers',
                    'Chaldean Name Spelling Vibration Audit',
                    '90-Day Numerical Life Accelerator Roadmap',
                ],
                'stats'            => [
                    'stat1_val' => '3x3 Grid', 'stat1_lbl' => 'LO SHU TOPOGRAPHY',
                    'stat2_val' => 'Chaldean', 'stat2_lbl' => 'NAME VIBRATION AUDIT',
                    'stat3_val' => '90 Days', 'stat3_lbl' => 'ACTION ROADMAP',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'SATISFACTION RATING',
                ],
                'modules'          => [
                    [
                        'tag' => 'Chapter 01',
                        'title' => 'Core Vibrations (Mulank & Bhagyank)',
                        'desc' => 'Calculates your Driver (Birth Day) and Conductor (Full Date) numbers, mapping your internal drive against your external destiny.',
                    ],
                    [
                        'tag' => 'Chapter 02',
                        'title' => '3x3 Lo Shu Energy Matrix',
                        'desc' => 'Maps your birth numbers into the sacred 3x3 magic square. Analyzes the Thought Plane (4-3-8), Will Plane (9-5-1), and Action Plane (2-7-6).',
                    ],
                    [
                        'tag' => 'Chapter 03',
                        'title' => 'Arrows of Strength & Vulnerability',
                        'desc' => 'Identifies presence of the Arrow of Prosperity, Arrow of Intellect, Arrow of Determination, and mitigates missing number planes.',
                    ],
                    [
                        'tag' => 'Chapter 04',
                        'title' => 'Chaldean Name Vibration Audit',
                        'desc' => 'Audits the numerical frequency of your current full name spelling. Identifies whether it aligns with your Mulank and Bhagyank or causes friction.',
                    ],
                    [
                        'tag' => 'Chapter 05',
                        'title' => 'Personal Year Cycle (Varshank)',
                        'desc' => 'Unveils your active Personal Year number (1 through 9) to guide decisions on launching new ventures, saving capital, or introspection.',
                    ],
                    [
                        'tag' => 'Chapter 06',
                        'title' => 'Remedial Colors, Dates & Yantras',
                        'desc' => 'Your lucky days of the month, favorable colors, compatible partner numbers, and sacred numerical yantras to balance missing grid numbers.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Numerical Identity & Core Frequencies', 'pages' => 'Pages 1 - 4', 'summary' => 'Driver (Mulank), Conductor (Bhagyank), and Soul Urge analysis'],
                    ['title' => 'The 3x3 Lo Shu Grid Energy Planes', 'pages' => 'Pages 5 - 8', 'summary' => 'Complete layout of present and missing numbers across 9 zones'],
                    ['title' => 'Arrows of Fortune & Energy Gaps', 'pages' => 'Pages 9 - 12', 'summary' => 'Practical Arrow (8-1-6), Willpower Arrow, and remedies for empty cells'],
                    ['title' => 'Chaldean Name Correction & Compatibility', 'pages' => 'Pages 13 - 16', 'summary' => 'Audit of current name spelling vibration with alignment advice'],
                    ['title' => 'Personal Year (1-9) Strategic Roadmap', 'pages' => 'Pages 17 - 19', 'summary' => 'Active annual vibration cycle and best months for action'],
                    ['title' => 'Lucky Days, Colors, Gemstones & Yantras', 'pages' => 'Pages 20 - 22', 'summary' => 'Comprehensive daily numerical empowerment toolkit'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-shapes', 'title' => 'Vedic & Lo Shu Integration', 'desc' => 'We don’t just give single-digit interpretations. We map numbers onto the cosmic 3x3 Lo Shu grid for multi-dimensional depth.'],
                    ['icon' => 'fa-solid fa-pen-nib', 'title' => 'Chaldean Name Tuning', 'desc' => 'Includes sound phonetic evaluation to identify whether minor letter additions can optimize your career vibration.'],
                    ['icon' => 'fa-solid fa-calendar-check', 'title' => 'Personal Year Timing', 'desc' => 'Clear guidance on what year in the 9-year cycle you are traversing right now.'],
                ],
                'testimonials'     => [
                    ['quote' => 'The Lo Shu grid analysis explained my persistent career blocks. Adding a single letter to my professional signature as suggested shifted my client response noticeably.', 'name' => 'Manish Chawla', 'loc' => 'Gurugram, India', 'rating' => 5],
                    ['quote' => 'Fascinating report! The 8-1-6 Practical Arrow and Personal Year 5 forecast gave me the confidence to launch my digital agency.', 'name' => 'Sonal Bhasin', 'loc' => 'Chandigarh, India', 'rating' => 5],
                    ['quote' => 'Comprehensive, beautifully presented, and scientifically structured. A must-read for anyone interested in numerology.', 'name' => 'Arun Joshi', 'loc' => 'Toronto, Canada', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'What is the difference between Mulank and Bhagyank?', 'a' => 'Mulank (Driver) is derived from your birth day (e.g., 14th = 1+4 = 5), representing your core personality. Bhagyank (Conductor) is the sum of your full birth date (day+month+year), representing your life path and destiny.'],
                    ['q' => 'What if my Lo Shu grid has missing numbers?', 'a' => 'Almost everyone has missing numbers in their Lo Shu grid. Our report provides specific remedies (colors, elements, gemstone touches, and numerical yantras) to artificially replenish those energy planes.'],
                    ['q' => 'Do I need to officially change my legal name?', 'a' => 'No legal change is necessary. Simply adopting the corrected spelling in your professional email signature, business cards, or daily handwriting practice is sufficient to activate the new vibration.'],
                ],
            ],

            // =========================================================
            // 8. MONTHLY HOROSCOPE & TRANSIT REPORT
            // =========================================================
            'monthly_horoscope_vedic' => [
                'report_slug'      => 'monthly_horoscope_vedic',
                'page_slug'        => 'monthly-horoscope-report',
                'code'             => 'MR_MHTS',
                'name'             => 'Monthly Horoscope & Transit Report',
                'meta_title'       => 'Monthly Horoscope & Transit Report | Month-by-Month Vedic Planetary Forecast',
                'meta_description' => 'Detailed 13-page monthly Vedic forecast tracking Sun, Moon, Mars, Mercury, Venus transits through your natal Bhavas, auspicious dates, challenging windows, and customized monthly remedies.',
                'meta_keywords'    => 'monthly horoscope report, monthly rashifal, monthly planetary transits, auspicious days calendar, monthly vedic astrology, monthly transit forecast',
                'eyebrow'          => 'Tactical Monthly Planetary Guidance',
                'title_prefix'     => 'Monthly Horoscope',
                'title_highlight'  => '& Transit Report',
                'hero_sub'         => 'A granular 13-page monthly tactical advisory. We map fast-moving planetary transits (Sun, Mars, Mercury, Venus, and Moon cycles) through your natal chart to give you week-by-week decision dates, financial windows, and relationship guidance.',
                'badge'            => 'Tactical Monthly Edition',
                'cover_badge'      => 'Monthly Horoscope & Transit Tactical Blueprint',
                'pages'            => '13 Pages',
                'price_sale'       => 99,
                'price_regular'    => 499,
                'discount_text'    => 'Save 80% Special',
                'emblem_icon'      => 'fa-solid fa-calendar-check',
                'highlights'       => [
                    'Week-by-Week Transit Ingress Map',
                    'Career, Finance & Relationship Rating',
                    'Auspicious Days & Muhurat Calendar',
                    'Monthly Pacification Mantras & Fasts',
                ],
                'stats'            => [
                    'stat1_val' => '4 Weeks', 'stat1_lbl' => 'GRANULAR BREAKDOWN',
                    'stat2_val' => '5 Grahas', 'stat2_lbl' => 'FAST TRANSITS TRACKED',
                    'stat3_val' => 'Day by Day', 'stat3_lbl' => 'AUSPICIOUS MUHURATS',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'ACCURACY RATING',
                ],
                'modules'          => [
                    [
                        'tag' => 'Week 01',
                        'title' => 'Foundation & Ingress Overview',
                        'desc' => 'Sun transit into new Rashi, planetary conjunctions, and setting your primary monthly intentions for growth.',
                    ],
                    [
                        'tag' => 'Week 02',
                        'title' => 'Financial Liquidity & Career Drive',
                        'desc' => 'Mars and Mercury alignments affecting negotiations, trade, workplace relationships, and investment timing.',
                    ],
                    [
                        'tag' => 'Week 03',
                        'title' => 'Emotional Rapport & Family Peace',
                        'desc' => 'Venus aspects and full moon transit through your sensitive natal houses, guiding communication and domestic harmony.',
                    ],
                    [
                        'tag' => 'Week 04',
                        'title' => 'Consolidation & Next Month Preview',
                        'desc' => 'Wrapping up key projects, avoiding unnecessary disputes, and preparing for the upcoming celestial shift.',
                    ],
                    [
                        'tag' => 'Calendar',
                        'title' => '30-Day Auspicious Days Scorecard',
                        'desc' => 'Quick reference calendar marking high-energy days, caution days, and prime dates for signing contracts or traveling.',
                    ],
                    [
                        'tag' => 'Remedies',
                        'title' => 'Monthly Vedic Upayas & Japa',
                        'desc' => 'Gentle, effective satvik remedies tailored specifically to the dominant planetary transit of the current month.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Current Month Celestial Environment', 'pages' => 'Pages 1 - 2', 'summary' => 'Active monthly planetary ingress and key celestial alignments'],
                    ['title' => 'Weekly Forecast: Week 1 & Week 2', 'pages' => 'Pages 3 - 6', 'summary' => 'Early month career, negotiations, and financial opportunities'],
                    ['title' => 'Weekly Forecast: Week 3 & Week 4', 'pages' => 'Pages 7 - 10', 'summary' => 'Late month consolidation, personal life, and vitality overview'],
                    ['title' => '30-Day Auspicious & Caution Calendar', 'pages' => 'Pages 11 - 12', 'summary' => 'Quick glance guide for daily scheduling and travels'],
                    ['title' => 'Monthly Remedial Practice & Mantras', 'pages' => 'Page 13', 'summary' => 'Specific monthly planetary pacification rituals'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-stopwatch-20', 'title' => 'Tactical & Immediate', 'desc' => 'While yearly reports give the 30,000-foot view, the monthly report tells you what to execute this week.'],
                    ['icon' => 'fa-solid fa-calendar-days', 'title' => 'Real-Time Ephemeris', 'desc' => 'Tracks exact ingress times of fast-moving planets through your exact rising sign.'],
                    ['icon' => 'fa-solid fa-wallet', 'title' => 'High Return on Investment', 'desc' => 'Helps you pick the right dates for major meetings, investments, and personal milestones.'],
                ],
                'testimonials'     => [
                    ['quote' => 'I check the auspicious dates scorecard at the start of every month before scheduling key board meetings. It has never let me down.', 'name' => 'Sanjay Malhotra', 'loc' => 'New Delhi, India', 'rating' => 5],
                    ['quote' => 'Concise, practical, and beautifully written. Perfect monthly check-in for staying aligned with the stars.', 'name' => 'Pallavi Deshmukh', 'loc' => 'Nagpur, India', 'rating' => 5],
                    ['quote' => 'The weekly breakdown gave me exact clarity on when to sign our property agreement in week 3. Superb service.', 'name' => 'Kunal Bansal', 'loc' => 'Indore, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'Is this a recurring subscription?', 'a' => 'You can purchase single monthly reports a la carte or subscribe through our Cosmic Membership Packs to receive your report automatically at the start of each month.'],
                    ['q' => 'How soon will I get the report?', 'a' => 'Immediately upon completing your details, our engine compiles your 13-page report and delivers the PDF link on screen and via email.'],
                    ['q' => 'Can I choose which month to generate?', 'a' => 'Yes, the report automatically generates for the current active calendar month, with foresight into the upcoming transition.'],
                ],
            ],

            // =========================================================
            // 9. MASTER HAST REKHA & PALMISTRY REPORT
            // =========================================================
            'palmistry_report' => [
                'report_slug'      => 'palmistry_report',
                'page_slug'        => 'palm-reading-report',
                'code'             => 'MR_PLMR',
                'name'             => 'Master Hast Rekha & Palmistry Analysis Report',
                'meta_title'       => 'Master Hast Rekha & Palmistry Analysis Report | 26-Page Samudrik Shastra Dossier',
                'meta_description' => 'Exhaustive 26-page publication-grade Palmistry report with sacred hand topography maps, kinetic energy waves, Mount potency metrics, career velocity heatmaps, asset donuts, and Vedic Upay.',
                'meta_keywords'    => 'palmistry report, hast rekha reading, palm reading report, samudrik shastra, life line head line heart line fate line, palmistry pdf report, merarashifal palmistry',
                'eyebrow'          => 'Vedic Samudrik Shastra & Sacred Hand Topography',
                'title_prefix'     => 'Master Hast Rekha',
                'title_highlight'  => '& Palmistry Analysis Report',
                'hero_sub'         => 'Your destiny is etched directly into the topography of your hands. An exhaustive 26-page publication-grade Samudrik Shastra dossier featuring sacred hand topography maps, planetary mount elevation scores, 12-month kinetic vitality waves, and personalized Vedic Upay.',
                'badge'            => 'Samudrik Shastra Master Dossier',
                'cover_badge'      => 'Master Hast Rekha & Palmistry 26-Page Definitive Edition',
                'pages'            => '26 Pages',
                'price_sale'       => 299,
                'price_regular'    => 1499,
                'discount_text'    => '80% OFF Launch Special',
                'emblem_icon'      => 'fa-solid fa-hand',
                'has_palm_scanner' => true,
                'highlights'       => [
                    'Life, Head, Heart & Fate Lines Analysis',
                    'Sacred Palm Topography Map & Mount Potency',
                    'Career Velocity Heatmap & Asset Allocation Donut',
                    'Personalized Vedic Upay & 5-Phase Age Timeline',
                ],
                'stats'            => [
                    'stat1_val' => '26 Pages', 'stat1_lbl' => 'MASTER DOSSIER',
                    'stat2_val' => '7 Mounts', 'stat2_lbl' => 'ENERGY ELEVATIONS',
                    'stat3_val' => 'Vedic AI', 'stat3_lbl' => 'SAMUDRIK SYNTHESIS',
                    'stat4_val' => '4.9 / 5.0', 'stat4_lbl' => 'SEEKER RATING',
                ],
                'modules'          => [
                    [
                        'tag' => 'Dossier 01',
                        'title' => 'Sacred Hand Topography Map (SVG)',
                        'desc' => 'High-resolution vector map tracing the Heart Line (Saturn/Jupiter termination), Head Line (descent to Chandra), Life Line (vitality curve), and Fate Line.',
                    ],
                    [
                        'tag' => 'Dossier 02',
                        'title' => 'Mount Elevation & Potency Bar Chart',
                        'desc' => 'Evaluates the 7 planetary mounts: Jupiter (Wisdom), Saturn (Discipline), Apollo/Sun (Fame), Mercury (Commerce), Mars (Courage), Venus (Luxury), and Moon (Intuition).',
                    ],
                    [
                        'tag' => 'Dossier 03',
                        'title' => '12-Month Kinetic Vitality Wave',
                        'desc' => 'Mathematical curve tracking monthly prana, intellectual stamina, and peak performance windows across the next 12 months.',
                    ],
                    [
                        'tag' => 'Dossier 04',
                        'title' => 'Professional Velocity Heatmap',
                        'desc' => 'Visual quarterly heatmaps revealing promotion windows, leadership consolidation periods, and safe capital deployment dates.',
                    ],
                    [
                        'tag' => 'Dossier 05',
                        'title' => 'Asset Allocation Donut Chart',
                        'desc' => 'Translates hand texture, finger lengths, and fate line branches into optimal wealth vehicles: Real Estate, Gold, and Liquid Reserves.',
                    ],
                    [
                        'tag' => 'Dossier 06',
                        'title' => '5-Phase Age Milestones & Vedic Upay',
                        'desc' => 'Chronological timeline mapping key transitions from ages 25 through 70, paired with traditional gemstone, mantra, and lifestyle remedies.',
                    ],
                ],
                'toc'              => [
                    ['title' => 'Hand Classification & Elemental Texture', 'pages' => 'Pages 1 - 3', 'summary' => 'Earth, Air, Fire, or Water hand typology with skin ridge texture analysis'],
                    ['title' => 'The Four Major Life Lines', 'pages' => 'Pages 4 - 8', 'summary' => 'Deep dive into Life Line, Head Line, Heart Line, and Fate Line branches'],
                    ['title' => 'Planetary Mounts & Topography Scorecard', 'pages' => 'Pages 9 - 13', 'summary' => 'Detailed analysis of all 7 planetary cushions and energy apexes'],
                    ['title' => '12-Month Kinetic Vitality Wave & Energy Scores', 'pages' => 'Pages 14 - 17', 'summary' => 'Monthly energy curves, stamina fluctuations, and wellness guidance'],
                    ['title' => 'Career Heatmap & Asset Allocation Donut', 'pages' => 'Pages 18 - 22', 'summary' => 'Professional breakthroughs, investment split, and wealth building'],
                    ['title' => '5-Phase Age Timeline & Classical Shastric Upay', 'pages' => 'Pages 23 - 26', 'summary' => 'Lifetime milestones, protective mantras, gemstones, and rituals'],
                ],
                'why_different'    => [
                    ['icon' => 'fa-solid fa-camera', 'title' => 'Dual Experience: Free Scanner + Deep Dossier', 'desc' => 'Upload your palm directly to get an immediate free 6-point snapshot, or unlock the exhaustive 26-page master dossier.'],
                    ['icon' => 'fa-solid fa-palette', 'title' => 'Vector Infographics & Rich Visuals', 'desc' => 'Features hand topography maps, kinetic vitality waves, velocity heatmaps, and asset allocation donuts.'],
                    ['icon' => 'fa-solid fa-brain', 'title' => 'Samudrik Shastra Meets AI', 'desc' => 'Combines authentic ancient palmistry scriptures with advanced Vedic AI intelligence for unparalleled depth.'],
                ],
                'testimonials'     => [
                    ['quote' => 'The palm topography map and the analysis of my descending head line were astonishingly accurate. The level of detail in 26 pages is extraordinary.', 'name' => 'Rajeev Mathur', 'loc' => 'New Delhi, India', 'rating' => 5],
                    ['quote' => 'I scanned my hand and got the free summary in seconds, then immediately bought the full 26-page dossier. Best purchase I have made all year.', 'name' => 'Shreya Sengupta', 'loc' => 'Mumbai, India', 'rating' => 5],
                    ['quote' => 'The asset allocation donut and career velocity heatmap gave me practical clarity that matched my financial planner’s advice. Brilliant work.', 'name' => 'Chetan Parekh', 'loc' => 'Ahmedabad, India', 'rating' => 5],
                ],
                'faqs'             => [
                    ['q' => 'Which hand should I photograph or submit?', 'a' => 'In Vedic Samudrik Shastra, your dominant working hand (the hand you write with) represents your active, manifested karma and current life choices. Your non-dominant hand represents innate inherited potential. Photograph your dominant hand in clear, natural lighting.'],
                    ['q' => 'Can I get a quick reading before ordering the full report?', 'a' => 'Yes! Use the free on-page palm scanner below to upload your hand. You will instantly receive a 6-point snapshot reading covering hand type, dominant mount, heart line preview, head line preview, core strength, and vitality.'],
                    ['q' => 'What if I want to buy the complete 26-page dossier directly?', 'a' => 'Simply click "Get Your Detailed Report Now" or "Order Report". You can provide your palm image and birth details directly in the secure form and receive the full 26-page publication-grade PDF immediately.'],
                    ['q' => 'What format will the report be delivered in?', 'a' => 'A vector print-ready PDF delivered instantly via our bottom notification bar and emailed directly to your inbox from no-reply@merarashifal.com.'],
                ],
            ],
        ];

        foreach ($reports as $k => $d) {
            $reports[$k] = self::apply_live_catalog_sync($d, $k);
        }

        return $reports;
    }
}
