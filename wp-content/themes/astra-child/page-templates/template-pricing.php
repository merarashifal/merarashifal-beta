<?php
/**
 * Template Name: MeraRashifal Pricing & Plans
 * Description: High-converting, authentic Vedic astrology pricing page with report purchases, Niyati AI chat tokens, comparison matrix, breadcrumbs, and exhaustive SEO schemas.
 */

if (!defined('ABSPATH')) {
    exit;
}

// -------------------------------------------------------------
// 1. Fetch Dynamic Data & Setup Variables
// -------------------------------------------------------------
$canonical_url   = home_url('/pricing/');
$site_name       = get_bloginfo('name') ?: 'MeraRashifal';
$site_logo       = content_url('/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png');
$og_image_url    = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/reports/kundli_predictions.png');
$niyati_avatar   = home_url('/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg');
$img_reports_base = home_url('/wp-content/plugins/mr-subscription-reports/assets/images/reports/');

// SEO Meta Variables
$meta_title       = 'MeraRashifal Pricing | Vedic Astrology Reports & Niyati AI Chat Tokens';
$meta_description = 'Transparent pricing for MeraRashifal Vedic astrology reports from ₹99 & Niyati AI chat token packs from ₹40. 100% accurate Swiss Ephemeris calculations with instant delivery.';
$meta_keywords    = 'vedic astrology pricing, astrology reports price, buy kundli report, kundali matching cost, niyati ai chat tokens, horoscope report price, palm reading price, janam kundali master report price, dasha timing report, merarashifal pricing';

// Fetch Niyati Credit Packs from WP Options or Fallback to Defaults
$raw_niyati_packs = get_option('niyati_credit_packs', []);
$niyati_packs = [
    'pack_starter' => [
        'key'           => 'pack_starter',
        'title_en'      => 'Starter Pack',
        'title_hi'      => 'आरम्भ पैक',
        'base_price'    => 50,
        'offer_price'   => 40,
        'discount_pct'  => 20,
        'questions'     => 60,
        'base_credits'  => 40,
        'bonus_credits' => 20,
        'rate_per_q'    => '0.67',
        'badge'         => 'Popular Starter',
        'is_featured'   => false,
        'description'   => '40 Base + 20 FREE Bonus Credits (60 Questions Total)',
        'features'      => [
            '60 Deep AI Astrology Questions',
            'Lifetime Validity (Never Expires)',
            'Instant Activation in Chat',
            'Lagna, Rashi & Dasha Calculations',
            'Hindi & English Bilingual Support',
        ]
    ],
    'pack_value' => [
        'key'           => 'pack_value',
        'title_en'      => 'Shubh Labh Pack',
        'title_hi'      => 'शुभ लाभ ऑफर',
        'base_price'    => 150,
        'offer_price'   => 100,
        'discount_pct'  => 33,
        'questions'     => 120,
        'base_credits'  => 100,
        'bonus_credits' => 20,
        'rate_per_q'    => '0.83',
        'badge'         => 'Best Value (33% OFF) • Most Popular',
        'is_featured'   => true,
        'description'   => '100 Base + 20 FREE Bonus Credits (120 Questions Total)',
        'features'      => [
            '120 In-depth Consultations',
            'Multiple Family Profiles Supported',
            'Full Kundali, Dasha & Transit Cross-Check',
            'Highest AI Reasoning & Priority Response',
            'Lifetime Validity & Chat Memory',
        ]
    ],
    'pack_mega' => [
        'key'           => 'pack_mega',
        'title_en'      => 'Maharatna Pack',
        'title_hi'      => 'महारत्न पैक',
        'base_price'    => 350,
        'offer_price'   => 199,
        'discount_pct'  => 43,
        'questions'     => 310,
        'base_credits'  => 250,
        'bonus_credits' => 60,
        'rate_per_q'    => '0.64',
        'badge'         => 'Mega Saver (43% OFF)',
        'is_featured'   => false,
        'description'   => '250 Base + 60 FREE Bonus Credits (310 Questions Total)',
        'features'      => [
            '310 Astrological Questions (Lowest Rate)',
            'Unlimited Family Members & Birth Charts',
            'Comprehensive Yearly Planning & Upays',
            'Marriage & Career Timing Deep Audits',
            'Lifetime Validity with Zero Expiry',
        ]
    ]
];

// Update with any saved values if available
if (!empty($raw_niyati_packs) && is_array($raw_niyati_packs)) {
    foreach ($niyati_packs as $k => &$p) {
        if (isset($raw_niyati_packs[$k])) {
            $saved = $raw_niyati_packs[$k];
            if (isset($saved['offer_price'])) $p['offer_price'] = (float)$saved['offer_price'];
            if (isset($saved['base_price']))  $p['base_price']  = (float)$saved['base_price'];
            if (isset($saved['base_credits']) && isset($saved['bonus_credits'])) {
                $p['base_credits']  = (int)$saved['base_credits'];
                $p['bonus_credits'] = (int)$saved['bonus_credits'];
                $p['questions']     = $p['base_credits'] + $p['bonus_credits'];
                if ($p['questions'] > 0) {
                    $p['rate_per_q'] = number_format($p['offer_price'] / $p['questions'], 2);
                }
            }
        }
    }
    unset($p);
}

// -------------------------------------------------------------
// 2. All 9 Astrology Reports Master List
// -------------------------------------------------------------
$reports_list = [
    [
        'id'            => 'yearly_horoscope_long',
        'slug'          => 'yearly-horoscope-report',
        'name'          => 'Yearly Horoscope Detailed Report (2026-2027)',
        'hindi_name'    => 'विस्तृत वार्षिक राशिफल रिपोर्ट (2026-2027)',
        'category'      => 'annual',
        'category_name' => 'Annual & Transits',
        'badge'         => 'Best Seller',
        'badge_class'   => 'bg-amber-500 text-white',
        'pages'         => '26 Pages',
        'image'         => $img_reports_base . 'yearly_horoscope_long.png',
        'price_sale'    => 999,
        'price_reg'     => 2499,
        'discount'      => '60% OFF',
        'desc'          => 'Comprehensive 26-page 12-month Vedic Varshaphal roadmap covering career, wealth, Shani Sade Sati and classical remedies.',
        'highlights'    => [
            'D1 Rashi & D9 Navamsha Charts with Shadbala',
            'Saturn Sade Sati, Dhaiya & Rahu-Ketu Transit Audit',
            '4 Quarters Deep Dive: Career, Wealth, Love & Health',
            'Customized Vedic Gemstone, Rudraksha & Mantra Remedies'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/yearly-horoscope-2026-2027-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now & Get Report →'
    ],
    [
        'id'            => 'kundli_predictions',
        'slug'          => 'janam-kundali-master-report',
        'name'          => 'Master Janma Kundali & Harmonics Report',
        'hindi_name'    => 'मास्टर जन्म पत्रिका एवं षोडशवर्ग रिपोर्ट',
        'category'      => 'kundli',
        'category_name' => 'Kundali & Life Path',
        'badge'         => 'Most Popular',
        'badge_class'   => 'bg-orange-600 text-white',
        'pages'         => '26 Pages',
        'image'         => $img_reports_base . 'kundli_predictions.png',
        'price_sale'    => 99,
        'price_reg'     => 999,
        'discount'      => '90% OFF',
        'desc'          => 'Full 26-page birth chart analysis, all 16 Shodashvarga harmonic charts, mathematical Shadbala, and lifetime karmic trajectory.',
        'highlights'    => [
            'Full 16 Shodashvarga Divisional Charts (D1 to D60)',
            'Mathematical Shadbala, Bhava Bala & Planetary Dignities',
            'Exhaustive Raja, Dhana, Gajakesari & Vipreet Yoga Audit',
            'Lifetime Karmic Destiny Roadmap & Core Potential'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/kundli-prediction-master-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'mahadasha_timing',
        'slug'          => 'dasha-timing-report',
        'name'          => 'Master Vedic Dasha Life Timing Report',
        'hindi_name'    => 'मास्टर वैदिक दशा एवं काल चक्र रिपोर्ट',
        'category'      => 'kundli',
        'category_name' => 'Kundali & Life Path',
        'badge'         => 'Timing Mastery',
        'badge_class'   => 'bg-purple-600 text-white',
        'pages'         => '26 Pages',
        'image'         => $img_reports_base . 'mahadasha_timing.png',
        'price_sale'    => 99,
        'price_reg'     => 999,
        'discount'      => '90% OFF',
        'desc'          => 'Deep dive into your active Vimshottari Dasha cycles across 120 years, predicting major breakthrough windows and caution periods.',
        'highlights'    => [
            'Complete 120-Year Vimshottari Chronological Dasha Table',
            'Active Mahadasha, Antardasha & Pratyantardasha Deep Dive',
            'Dasha Sandhi (Planetary Transition Crossover) Audit',
            'Remedies & Upayas for Challenging Planetary Periods'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/dasha-timing-master-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'dosh_remedial',
        'slug'          => 'kundali-dosh-report',
        'name'          => 'Master Kundali Dosh & Remedial Audit Report',
        'hindi_name'    => 'मास्टर कुण्डली दोष एवं निवारण रिपोर्ट',
        'category'      => 'remedies',
        'category_name' => 'Remedies & Compatibility',
        'badge'         => 'Remedial Audit',
        'badge_class'   => 'bg-rose-600 text-white',
        'pages'         => '20 Pages',
        'image'         => $img_reports_base . 'dosh_remedial.png',
        'price_sale'    => 99,
        'price_reg'     => 799,
        'discount'      => '88% OFF',
        'desc'          => 'Clinical shastric analysis of Manglik, Kaal Sarp, Pitra, Kemdrum & Chandal doshas with authentic Vedic remedies.',
        'highlights'    => [
            'Manglik Dosh Severity, House Audit & Cancellation Rules',
            '12 Types of Kaal Sarp Yoga Analysis (Anant to Sheshnag)',
            'Pitra Dosh, Kemdrum, Guru Chandal & Gandmool Diagnostics',
            'Authenticated Shastric Upayas, Fasting, Yantras & Mantras'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/kundli-dosh-master-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'kundli_matching',
        'slug'          => 'kundali-and-gun-matching-report',
        'name'          => 'Master Kundali Matching (36 Gun Milan) Report',
        'hindi_name'    => 'मास्टर कुण्डली मिलान एवं 36 गुण मिलान रिपोर्ट',
        'category'      => 'remedies',
        'category_name' => 'Remedies & Compatibility',
        'badge'         => 'Vivah Edition',
        'badge_class'   => 'bg-pink-600 text-white',
        'pages'         => '24 Pages',
        'image'         => $img_reports_base . 'kundli_matching.png',
        'price_sale'    => 99,
        'price_reg'     => 1499,
        'discount'      => '93% OFF',
        'desc'          => '24-page Ashtakoot matching dossier with 36 Gunas, Manglik balance, Nadi/Bhakoot dosha remedies, and marital harmony score.',
        'highlights'    => [
            'Complete 36 Points Ashtakoot Milan Matrix & Scoring',
            'Manglik Balance, Mutual Aspect & Papasamaya Check',
            'Nadi Dosh & Bhakoot Dosh Remedial Solutions',
            'Longevity, Progeny & Financial Compatibility Forecast'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/kundli-matching-master-rahul-pooja.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'palmistry_report',
        'slug'          => 'palm-reading-report',
        'name'          => 'Master Hast Rekha & Palmistry Analysis Report',
        'hindi_name'    => 'मास्टर हस्तरेखा एवं सामुद्रिक शास्त्र रिपोर्ट',
        'category'      => 'samudrik',
        'category_name' => 'Samudrik & Numerology',
        'badge'         => 'New Edition',
        'badge_class'   => 'bg-emerald-600 text-white',
        'pages'         => '26 Pages',
        'image'         => $img_reports_base . 'palmistry_report.png',
        'price_sale'    => 299,
        'price_reg'     => 1499,
        'discount'      => '80% OFF',
        'desc'          => '26-page Samudrik Shastra master dossier: Major & minor lines, mounts topography, career velocity heatmap, and 5-phase age timeline.',
        'highlights'    => [
            'Life, Heart, Head & Fate Lines Micro-Analysis',
            'Sacred Palm Topography & Mount Potency (Jupiter, Venus, Sun)',
            'Career Velocity Heatmap & Asset Accumulation Windows',
            '5-Phase Age Timeline (Youth to Golden Years) & Vedic Upay'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/palmistry-master-rahul-sharma.pdf'),
        'button_text'   => 'Buy Now for ₹299 →'
    ],
    [
        'id'            => 'numerology',
        'slug'          => 'numerology-report',
        'name'          => 'Master Numerology & Vedic Integration Report',
        'hindi_name'    => 'मास्टर अंक ज्योतिष एवं लो-शू ग्रिड रिपोर्ट',
        'category'      => 'samudrik',
        'category_name' => 'Samudrik & Numerology',
        'badge'         => 'Sankhya Shastra',
        'badge_class'   => 'bg-indigo-600 text-white',
        'pages'         => '22 Pages',
        'image'         => $img_reports_base . 'numerology.png',
        'price_sale'    => 99,
        'price_reg'     => 599,
        'discount'      => '83% OFF',
        'desc'          => '22-page Sankhya Shastra audit: 3x3 Lo Shu energy grid, Mulank & Bhagyank, Chaldean name spelling vibration, and 90-day accelerator.',
        'highlights'    => [
            'Complete 3x3 Lo Shu Energy Grid & Missing Number Audit',
            'Mulank (Driver), Bhagyank (Conductor) & Soul Urge Numbers',
            'Chaldean Name Spelling Harmonization & Lucky Vibrations',
            '90-Day Numerical Life Accelerator Roadmap'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/numerology-master-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'monthly_horoscope_vedic',
        'slug'          => 'monthly-horoscope-report',
        'name'          => 'Monthly Horoscope & Transit Tactical Report',
        'hindi_name'    => 'मासिक राशिफल एवं गोचर रणनीति रिपोर्ट',
        'category'      => 'annual',
        'category_name' => 'Annual & Transits',
        'badge'         => 'Monthly Edition',
        'badge_class'   => 'bg-blue-600 text-white',
        'pages'         => '13 Pages',
        'image'         => $img_reports_base . 'monthly_horoscope_vedic.png',
        'price_sale'    => 99,
        'price_reg'     => 499,
        'discount'      => '80% OFF',
        'desc'          => 'Tactical 13-page month-by-month planetary transits, auspicious days, career/finance sector ratings, and monthly pacification mantras.',
        'highlights'    => [
            'Week-by-Week Transit Ingress Map for the Month',
            'Career, Finance & Relationship Rating Windows',
            'Auspicious Days, Shubh Muhurats & Caution Dates',
            'Monthly Vedic Mantras, Daan & Planetary Pacification'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/monthly-horoscope-sample-tushar-singhal.pdf'),
        'button_text'   => 'Buy Now for ₹99 →'
    ],
    [
        'id'            => 'yearly_horoscope_vedic',
        'slug'          => 'yearly-horoscope-snapshot-report',
        'name'          => 'Yearly Horoscope Snapshot Report (2026)',
        'hindi_name'    => 'वार्षिक राशिफल स्नैपशॉट रिपोर्ट (2026)',
        'category'      => 'annual',
        'category_name' => 'Annual & Transits',
        'badge'         => '100% FREE',
        'badge_class'   => 'bg-teal-600 text-white',
        'pages'         => '5 Pages',
        'image'         => $img_reports_base . 'yearly_horoscope_vedic.png',
        'price_sale'    => 0,
        'price_reg'     => 499,
        'discount'      => 'FREE',
        'desc'          => 'Essential 5-page complimentary annual overview with core planetary transits and auspicious timing windows for 2026.',
        'highlights'    => [
            'Core Planetary Placements & D1 Chart Overview',
            'Major Annual Transit Map (Guru, Shani & Rahu-Ketu)',
            'Favorable Career, Wealth & Health Timing Windows',
            'Key Auspicious Dates & Simple Shastric Upays'
        ],
        'sample_pdf'    => home_url('/wp-content/uploads/mr-reports/samples/yearly-horoscope-2026-snapshot.pdf'),
        'button_text'   => 'Get Free Report →'
    ],
];

// -------------------------------------------------------------
// 3. Schema.org JSON-LD Generation
// -------------------------------------------------------------
$schema_items = [];
$pos = 1;
foreach ($reports_list as $r) {
    $item_url = home_url('/reports/' . $r['slug'] . '/');
    $schema_items[] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'item'     => [
            '@type'        => 'Product',
            '@id'          => $item_url . '#product',
            'name'         => $r['name'],
            'description'  => $r['desc'],
            'image'        => $r['image'],
            'url'          => $item_url,
            'brand'        => [
                '@type' => 'Brand',
                'name'  => 'MeraRashifal'
            ],
            'offers'       => [
                '@type'           => 'Offer',
                'price'           => (string)$r['price_sale'],
                'priceCurrency'   => 'INR',
                'priceValidUntil' => '2027-12-31',
                'availability'    => 'https://schema.org/InStock',
                'url'             => $item_url,
            ],
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4.9',
                'reviewCount' => '1420',
                'bestRating'  => '5',
                'worstRating' => '1'
            ]
        ]
    ];
}

// Niyati Token Packs Offers
$niyati_offers = [];
foreach ($niyati_packs as $p) {
    $niyati_offers[] = [
        '@type'           => 'Offer',
        'name'            => $p['title_en'] . ' (' . $p['title_hi'] . ') - ' . $p['questions'] . ' AI Astrology Questions',
        'price'           => (string)$p['offer_price'],
        'priceCurrency'   => 'INR',
        'priceValidUntil' => '2027-12-31',
        'availability'    => 'https://schema.org/InStock',
        'description'     => $p['description'],
        'url'             => $canonical_url . '#niyati-pricing'
    ];
}

$schema_graph = [
    [
        '@type' => 'BreadcrumbList',
        '@id'   => $canonical_url . '#breadcrumb',
        'itemListElement' => [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Home',
                'item'     => home_url('/')
            ],
            [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Pricing & Plans',
                'item'     => $canonical_url
            ]
        ]
    ],
    [
        '@type'        => 'WebPage',
        '@id'          => $canonical_url . '#webpage',
        'url'          => $canonical_url,
        'name'         => $meta_title,
        'description'  => $meta_description,
        'inLanguage'   => 'en-IN',
        'isPartOf'     => [
            '@type' => 'WebSite',
            '@id'   => home_url('/#website'),
            'name'  => $site_name,
            'url'   => home_url('/')
        ]
    ],
    [
        '@type'       => 'Organization',
        '@id'         => home_url('/#organization'),
        'name'        => 'MeraRashifal',
        'url'         => home_url('/'),
        'logo'        => $site_logo,
        'sameAs'      => [
            'https://www.facebook.com/merarashifal',
            'https://www.instagram.com/merarashifal'
        ],
        'contactPoint' => [
            '@type'       => 'ContactPoint',
            'telephone'   => '+91-9876543210',
            'contactType' => 'Customer Support',
            'areaServed'  => 'IN',
            'availableLanguage' => ['Hindi', 'English']
        ]
    ],
    [
        '@type'        => 'Product',
        '@id'          => $canonical_url . '#niyati-tokens',
        'name'         => 'Niyati AI Vedic Astrology Chat Credits & Tokens',
        'description'  => 'Instant AI Vedic Astrology consultation tokens powered by Google Gemini and live Swiss Ephemeris calculations. Lifetime validity with no expiry.',
        'brand'        => [
            '@type' => 'Brand',
            'name'  => 'MeraRashifal'
        ],
        'offers'       => $niyati_offers,
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => '4.95',
            'reviewCount' => '3850',
            'bestRating'  => '5',
            'worstRating' => '1'
        ]
    ],
    [
        '@type'           => 'ItemList',
        '@id'             => $canonical_url . '#reports-catalog',
        'name'            => 'Authentic Vedic Astrological Reports Catalog',
        'numberOfItems'   => count($schema_items),
        'itemListElement' => $schema_items
    ],
    [
        '@type'      => 'FAQPage',
        '@id'        => $canonical_url . '#faq',
        'mainEntity' => [
            [
                '@type'          => 'Question',
                'name'           => 'How are the astrological reports delivered after purchase?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'Immediately upon completing your order, our astronomical calculation engine synthesizes your chart using Swiss Ephemeris. Your publication-grade 13-26 page PDF report is delivered to your email inbox within 3-5 minutes and remains accessible permanently in your MeraRashifal account dashboard.'
                ]
            ],
            [
                '@type'          => 'Question',
                'name'           => 'Do Niyati AI Chat Tokens expire?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'No. All Niyati AI Astrology Chat Token packs come with 100% Lifetime Validity. Unused questions remain in your wallet forever, allowing you to consult Niyati whenever you need astrological guidance.'
                ]
            ],
            [
                '@type'          => 'Question',
                'name'           => 'Can I consult Niyati AI chat or buy reports for multiple family members?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'Yes! Both Niyati AI Chat and our report builders allow you to generate charts for multiple people. In Niyati AI, you can switch between saved member profiles effortlessly using the same credit wallet.'
                ]
            ],
            [
                '@type'          => 'Question',
                'name'           => 'What is the difference between Niyati AI Chat and the Detailed PDF Reports?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'Niyati AI Chat is a conversational Vedic astrologer designed for specific queries, daily guidance, transit explanations, and follow-up questions. The PDF Reports are exhaustive, publication-grade dossiers (13-26 pages) containing high-resolution 16 Shodashvarga charts, complete 120-year Dasha tables, Shadbala scores, and permanent reference documentation.'
                ]
            ],
            [
                '@type'          => 'Question',
                'name'           => 'What payment methods do you accept?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'We accept all major Indian and international payment methods through 256-bit SSL encrypted Razorpay gateway, including UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards (Visa, Mastercard, RuPay), Net Banking across 50+ banks, and Wallets.'
                ]
            ],
            [
                '@type'          => 'Question',
                'name'           => 'Are my birth details kept secure and confidential?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'Absolutely. Your date, time, and place of birth are used strictly for mathematical astronomical calculations. We never sell, share, or disclose your personal astrological charts to third parties.'
                ]
            ]
        ]
    ]
];

$schema_json = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => $schema_graph
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo esc_html($meta_title); ?></title>

    <!-- Comprehensive SEO Meta Tags -->
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>"/>
    <meta name="keywords" content="<?php echo esc_attr($meta_keywords); ?>"/>
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>"/>
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1"/>
    <meta name="author" content="MeraRashifal"/>

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:locale" content="en_US"/>
    <meta property="og:locale:alternate" content="hi_IN"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>"/>
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>"/>
    <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>"/>
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>"/>
    <meta property="og:image" content="<?php echo esc_url($og_image_url); ?>"/>
    <meta property="og:image:secure_url" content="<?php echo esc_url($og_image_url); ?>"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height" content="630"/>
    <meta property="og:image:alt" content="MeraRashifal Astrology Reports & Niyati AI Chat Pricing"/>
    <meta property="og:image:type" content="image/png"/>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:site" content="@merarashifal"/>
    <meta name="twitter:creator" content="@merarashifal"/>
    <meta name="twitter:title" content="<?php echo esc_attr($meta_title); ?>"/>
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>"/>
    <meta name="twitter:image" content="<?php echo esc_url($og_image_url); ?>"/>
    <meta name="twitter:image:alt" content="MeraRashifal Astrology Reports & Niyati AI Chat Pricing"/>

    <!-- JSON-LD Structured Data Schema (BreadcrumbList, Product, ItemList, FAQPage, Organization) -->
    <script type="application/ld+json">
<?php echo $schema_json; ?>
    </script>

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&family=Cinzel:wght@600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "sacred-orange": "#F58220",
            "sacred-gold": "#D9A441",
            "deep-maroon": "#7A1F3D",
            "cream-bg": "#FDF8F1",
            "dark-brown": "#2D221D",
            "soft-card": "#FFFFFF",
            "primary": "#964900",
            "primary-container": "#ffb786",
            "surface": "#fff8f6",
            "surface-container": "#fff1eb",
            "surface-card": "#ffffff"
          },
          fontFamily: {
            serif: ["Playfair Display", "Georgia", "serif"],
            sans: ["Inter", "system-ui", "sans-serif"],
            cinzel: ["Cinzel", "serif"]
          }
        }
      }
    };
    </script>

    <style>
      .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        display: inline-block;
        vertical-align: middle;
        line-height: 1;
      }
      .spiritual-radial {
        background: radial-gradient(circle at 50% 0%, rgba(245, 130, 32, 0.12) 0%, rgba(253, 248, 241, 0) 75%);
      }
      .halo-card {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .halo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -8px rgba(150, 73, 0, 0.14), 0 0 0 1px rgba(217, 164, 65, 0.35);
      }
      .halo-featured {
        box-shadow: 0 18px 40px -10px rgba(245, 130, 32, 0.28), 0 0 0 2px #F58220;
      }
      .halo-featured:hover {
        transform: translateY(-5px);
        box-shadow: 0 22px 48px -10px rgba(245, 130, 32, 0.38), 0 0 0 2px #F58220;
      }
      .badge-glow {
        animation: pulseSlow 3s infinite;
      }
      @keyframes pulseSlow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.92; transform: scale(1.02); }
      }
      /* Ensure proper spacing on mobile with bottom menus */
      @media (max-width: 768px) {
        .pricing-page-body {
          padding-bottom: 90px;
        }
      }
    </style>

    <?php wp_head(); ?>
</head>
<body class="bg-cream-bg text-dark-brown antialiased font-sans selection:bg-sacred-gold selection:text-white pricing-page-body">

<!-- ========================================================= -->
<!-- 1. TOP ASTRONOMICAL STATUS BAR                           -->
<!-- ========================================================= -->
<div class="bg-[#f9eee4] border-b border-[#ebd2c1] text-[#6b4e3d] py-1.5 px-4 lg:px-12 text-xs">
  <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 overflow-hidden">
    <div class="flex items-center gap-2 text-[11px] md:text-xs font-medium">
      <span class="inline-flex items-center gap-1.5 text-primary">
        <span class="w-2 h-2 rounded-full bg-sacred-orange animate-pulse"></span>
        <strong class="font-semibold">Vikram Samvat 2083</strong>
        <span class="text-gray-400">|</span>
        <span class="text-deep-maroon font-serif">Kartik Shukla Dashami</span>
      </span>
      <span class="hidden md:inline text-gray-400">|</span>
      <span class="hidden md:inline text-xs text-gray-600">Swiss Ephemeris Precision Engine</span>
    </div>

    <div class="flex items-center gap-4 text-xs font-medium">
      <a href="<?php echo esc_url(home_url('/panchang/')); ?>" class="hover:text-primary transition flex items-center gap-1">
        <span class="material-symbols-outlined text-sm text-sacred-orange">wb_sunny</span>
        <span class="hidden sm:inline">Today's Panchang</span>
      </a>
      <span class="text-gray-300">|</span>
      <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="hover:text-primary transition flex items-center gap-1">
        <span class="material-symbols-outlined text-sm text-sacred-gold">account_circle</span>
        <span>My Account</span>
      </a>
    </div>
  </div>
</div>

<!-- ========================================================= -->
<!-- 2. BRAND NAVIGATION HEADER                               -->
<!-- ========================================================= -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-[#eedbd0] shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 flex items-center justify-between h-16 lg:h-20">
    <!-- Brand Logo -->
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3 group">
      <img src="<?php echo esc_url($site_logo); ?>" alt="MeraRashifal Logo" class="h-10 lg:h-12 w-auto object-contain transition-transform group-hover:scale-105"/>
      <div class="flex flex-col">
        <span class="font-serif text-xl lg:text-2xl font-bold text-primary tracking-tight leading-none">MeraRashifal</span>
        <span class="text-[10px] tracking-widest uppercase font-semibold text-sacred-gold mt-0.5">Vedic Celestial Portal</span>
      </div>
    </a>

    <!-- Navigation Links -->
    <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-gray-700">
      <a href="<?php echo esc_url(home_url('/panchang/')); ?>" class="hover:text-sacred-orange transition">Panchang</a>
      <a href="<?php echo esc_url(home_url('/kundali-details/')); ?>" class="hover:text-sacred-orange transition">Kundali</a>
      <a href="<?php echo esc_url(home_url('/matchmaking-compatibility-check/')); ?>" class="hover:text-sacred-orange transition">Kundali Milan</a>
      <a href="<?php echo esc_url(home_url('/reports/')); ?>" class="hover:text-sacred-orange transition">Astrology Reports</a>
      <a href="<?php echo esc_url($canonical_url); ?>" class="text-sacred-orange font-semibold border-b-2 border-sacred-orange pb-0.5">Pricing &amp; Plans</a>
    </nav>

    <!-- Header Actions -->
    <div class="flex items-center gap-3">
      <button onclick="window.openNiyatiChat ? window.openNiyatiChat() : null" class="hidden sm:inline-flex items-center gap-2 bg-[#fff4eb] hover:bg-[#ffe8d6] text-sacred-orange border border-sacred-orange/30 px-3.5 py-1.5 rounded-full text-xs font-semibold transition">
        <img src="<?php echo esc_url($niyati_avatar); ?>" class="w-5 h-5 rounded-full object-cover" alt="Niyati"/>
        <span>Chat with Niyati AI</span>
      </button>

      <a href="<?php echo esc_url(home_url('/reports/')); ?>" class="inline-flex items-center gap-1.5 bg-sacred-orange hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition">
        <i class="fa-solid fa-scroll text-xs"></i>
        <span>Explore All Reports</span>
      </a>
    </div>
  </div>
</header>

<!-- ========================================================= -->
<!-- 3. VISUAL BREADCRUMBS (Accessible + Schema Microdata)     -->
<!-- ========================================================= -->
<nav class="bg-[#fcf5ed] border-b border-[#f0ded2] py-2.5 px-4 lg:px-12 text-xs" aria-label="Breadcrumb">
  <div class="max-w-7xl mx-auto flex items-center">
    <ol class="flex items-center space-x-2 text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center">
        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="item" class="hover:text-sacred-orange transition flex items-center gap-1">
          <i class="fa-solid fa-house text-gray-400 text-xs"></i>
          <span itemprop="name">Home</span>
        </a>
        <meta itemprop="position" content="1"/>
      </li>
      <li class="text-gray-400">/</li>
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center font-semibold text-primary" aria-current="page">
        <span itemprop="name">Pricing &amp; Plans</span>
        <meta itemprop="position" content="2"/>
      </li>
    </ol>
  </div>
</nav>

<!-- ========================================================= -->
<!-- 4. HERO SECTION                                          -->
<!-- ========================================================= -->
<section class="spiritual-radial pt-12 pb-10 px-4 sm:px-6 lg:px-12 text-center relative overflow-hidden">
  <div class="max-w-4xl mx-auto">
    <!-- Top Eyebrow Pill -->
    <div class="inline-flex items-center gap-2 bg-[#fff1e0] border border-[#f5c699] text-primary px-4 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase mb-4 shadow-xs">
      <i class="fa-solid fa-om text-sacred-orange"></i>
      <span>Transparent Vedic Pricing • पारदर्शी प्रामाणिक मूल्य</span>
    </div>

    <!-- Main H1 Heading -->
    <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-extrabold text-dark-brown tracking-tight leading-tight mb-4">
      Transparent Pricing for Authentic Vedic Reports &amp; <span class="text-sacred-orange underline decoration-sacred-gold/60 decoration-wavy decoration-2">Niyati AI Consultations</span>
    </h1>

    <!-- Subtitle -->
    <p class="text-base sm:text-lg text-gray-700 max-w-2xl mx-auto leading-relaxed mb-8">
      Clear, honest pricing with zero hidden fees. Instant 13–26 page astrological dossiers calculated with NASA JPL Swiss Ephemeris accuracy, alongside lifetime-validity AI astrology chat question credits.
    </p>

    <!-- Trust Stats Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-3xl mx-auto text-left mb-8">
      <div class="bg-white/90 border border-[#ebd8cb] rounded-xl p-3 shadow-xs">
        <div class="text-primary font-bold text-lg font-serif">1,25,000+</div>
        <div class="text-[11px] text-gray-600 uppercase tracking-wider font-semibold">Reports Generated</div>
      </div>
      <div class="bg-white/90 border border-[#ebd8cb] rounded-xl p-3 shadow-xs">
        <div class="text-primary font-bold text-lg font-serif">4.9 / 5.0 ⭐</div>
        <div class="text-[11px] text-gray-600 uppercase tracking-wider font-semibold">Seeker Satisfaction</div>
      </div>
      <div class="bg-white/90 border border-[#ebd8cb] rounded-xl p-3 shadow-xs">
        <div class="text-primary font-bold text-lg font-serif">Lifetime</div>
        <div class="text-[11px] text-gray-600 uppercase tracking-wider font-semibold">AI Token Validity</div>
      </div>
      <div class="bg-white/90 border border-[#ebd8cb] rounded-xl p-3 shadow-xs">
        <div class="text-primary font-bold text-lg font-serif">&lt; 5 Mins</div>
        <div class="text-[11px] text-gray-600 uppercase tracking-wider font-semibold">Instant Delivery</div>
      </div>
    </div>

    <!-- Quick Navigation Anchor Tabs -->
    <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
      <a href="#niyati-pricing" class="inline-flex items-center gap-2 bg-white hover:bg-orange-50 border border-sacred-orange/30 text-primary px-4 py-2 rounded-full text-xs font-bold transition shadow-xs">
        <i class="fa-solid fa-comments text-sacred-orange"></i>
        <span>Niyati AI Chat Tokens (From ₹40)</span>
      </a>
      <a href="#reports-pricing" class="inline-flex items-center gap-2 bg-sacred-orange text-white px-4 py-2 rounded-full text-xs font-bold transition shadow-xs hover:bg-orange-600">
        <i class="fa-solid fa-book-journal-whills"></i>
        <span>Astrology Reports (From ₹99)</span>
      </a>
      <a href="#comparison-matrix" class="inline-flex items-center gap-2 bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-2 rounded-full text-xs font-semibold transition shadow-xs">
        <i class="fa-solid fa-table-columns text-gray-500"></i>
        <span>Compare Features</span>
      </a>
      <a href="#faq-section" class="inline-flex items-center gap-2 bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-2 rounded-full text-xs font-semibold transition shadow-xs">
        <i class="fa-solid fa-circle-question text-gray-500"></i>
        <span>Pricing FAQs</span>
      </a>
    </div>
  </div>
</section>

<!-- ========================================================= -->
<!-- 5. SECTION: NIYATI AI CHAT TOKEN PACKAGES PRICING GRID    -->
<!-- ========================================================= -->
<section id="niyati-pricing" class="py-12 px-4 sm:px-6 lg:px-12 bg-[#fffcf8] border-t border-b border-[#f0ded2]">
  <div class="max-w-7xl mx-auto">
    <!-- Section Header -->
    <div class="text-center max-w-3xl mx-auto mb-10">
      <div class="inline-flex items-center gap-2 text-sacred-orange font-bold text-xs uppercase tracking-wider mb-2">
        <img src="<?php echo esc_url($niyati_avatar); ?>" class="w-6 h-6 rounded-full border border-sacred-orange" alt="Niyati"/>
        <span>Conversational Vedic AI Assistant</span>
      </div>
      <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-extrabold text-dark-brown">
        Niyati AI Vedic Chat Tokens <span class="text-deep-maroon font-serif text-lg sm:text-2xl font-normal block sm:inline">(नियति AI ज्योतिष परामर्श टोकन्स)</span>
      </h2>
      <p class="text-gray-600 text-sm sm:text-base mt-2">
        Ask personal astrological questions to Niyati AI anytime 24/7. 1 Question Token = 1 comprehensive birth chart analysis with Lagna, Vimshottari dasha, and live transits. <strong>Tokens have Lifetime Validity with zero expiry.</strong>
      </p>
    </div>

    <!-- 3-Card Pricing Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto items-stretch">
      <?php foreach ($niyati_packs as $pack): 
        $is_feat = !empty($pack['is_featured']);
      ?>
        <div class="halo-card <?php echo $is_feat ? 'halo-featured bg-gradient-to-b from-[#fffbf5] to-white relative' : 'bg-white border border-[#ebd8cb]'; ?> rounded-2xl p-6 sm:p-8 flex flex-col justify-between">
          
          <div>
            <!-- Featured Ribbon / Badge -->
            <div class="flex items-center justify-between gap-2 mb-4">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?php echo $is_feat ? 'bg-sacred-orange text-white badge-glow shadow-xs' : 'bg-[#fff0e2] text-primary border border-sacred-orange/20'; ?>">
                <i class="fa-solid fa-bolt text-[10px]"></i>
                <?php echo esc_html($pack['badge']); ?>
              </span>
              <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                <?php echo esc_html($pack['discount_pct']); ?>% SAVING
              </span>
            </div>

            <!-- Pack Title -->
            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark-brown">
              <?php echo esc_html($pack['title_en']); ?>
              <span class="block text-sm font-sans font-medium text-gray-500 mt-0.5"><?php echo esc_html($pack['title_hi']); ?></span>
            </h3>

            <!-- Price Row -->
            <div class="mt-4 mb-2 flex items-baseline gap-2">
              <span class="text-3xl sm:text-4xl font-extrabold text-primary font-serif">₹<?php echo esc_html($pack['offer_price']); ?></span>
              <span class="text-base text-gray-400 line-through font-medium">₹<?php echo esc_html($pack['base_price']); ?></span>
              <span class="text-xs text-gray-500 font-medium">one-time</span>
            </div>

            <!-- Per Question Highlight Strip -->
            <div class="bg-[#fff6ee] border border-[#f7dac4] rounded-lg p-2.5 mb-6 text-center">
              <div class="text-sm font-bold text-primary">
                <?php echo esc_html($pack['questions']); ?> Questions Total
              </div>
              <div class="text-[11px] text-gray-600 mt-0.5">
                (<?php echo esc_html($pack['base_credits']); ?> Base + <span class="text-emerald-700 font-bold"><?php echo esc_html($pack['bonus_credits']); ?> FREE Bonus</span> • Just ₹<?php echo esc_html($pack['rate_per_q']); ?>/Q)
              </div>
            </div>

            <!-- Features List -->
            <ul class="space-y-3 mb-8 text-xs sm:text-sm text-gray-700">
              <?php foreach ($pack['features'] as $f): ?>
                <li class="flex items-start gap-2.5">
                  <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                  <span><?php echo esc_html($f); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Bottom Action Area -->
          <div class="pt-4 border-t border-[#f2e1d5]">
            <button 
              type="button" 
              onclick="handleNiyatiPackBuy('<?php echo esc_attr($pack['key']); ?>', this)"
              class="w-full py-3.5 px-5 rounded-xl font-bold text-sm tracking-wide text-center transition flex items-center justify-center gap-2 <?php echo $is_feat ? 'bg-sacred-orange hover:bg-orange-600 text-white shadow-md' : 'bg-primary hover:bg-[#7c3d00] text-white shadow-xs'; ?>">
              <i class="fa-solid fa-coins"></i>
              <span>Recharge for ₹<?php echo esc_html($pack['offer_price']); ?></span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
            <p class="text-[11px] text-center text-gray-500 mt-2">
              <i class="fa-solid fa-shield-halved text-[10px] text-emerald-600"></i> Instant Activation • 256-bit Secure
            </p>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- Free Consultation Teaser Box -->
    <div class="mt-10 max-w-4xl mx-auto bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border border-sacred-gold/40 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
      <div class="flex items-center gap-4 text-left">
        <img src="<?php echo esc_url($niyati_avatar); ?>" class="w-12 h-12 rounded-full border-2 border-sacred-orange object-cover shrink-0" alt="Niyati"/>
        <div>
          <h4 class="font-serif font-bold text-base text-dark-brown">New to Niyati? Try Your Daily Free Question!</h4>
          <p class="text-xs text-gray-600 mt-0.5">Every visitor receives free daily questions. No credit card or upfront recharge needed.</p>
        </div>
      </div>
      <button onclick="window.openNiyatiChat ? window.openNiyatiChat() : null" class="shrink-0 bg-white hover:bg-orange-50 text-primary border border-sacred-orange px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles text-sacred-orange"></i>
        <span>Try Free Chat Now</span>
      </button>
    </div>

  </div>
</section>

<!-- ========================================================= -->
<!-- 6. SECTION: ALL 9 ASTROLOGY REPORTS PRICING & BUY NOW     -->
<!-- ========================================================= -->
<section id="reports-pricing" class="py-14 px-4 sm:px-6 lg:px-12 bg-cream-bg">
  <div class="max-w-7xl mx-auto">
    
    <!-- Section Title & Category Filter Tabs -->
    <div class="text-center max-w-3xl mx-auto mb-10">
      <div class="inline-flex items-center gap-1.5 text-deep-maroon font-bold text-xs uppercase tracking-wider mb-2">
        <i class="fa-solid fa-scroll text-sacred-orange"></i>
        <span>Publication-Grade PDF Dossiers</span>
      </div>
      <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-extrabold text-dark-brown">
        Personalized Astrological Reports &amp; Dossiers
      </h2>
      <p class="text-gray-600 text-sm sm:text-base mt-2">
        Deep, publication-grade 13–26 page analytical blueprints synthesized with mathematical accuracy. Click <strong>"Buy Now"</strong> on any report to configure your birth details and generate your personalized dossier.
      </p>

      <!-- Category Filter Pills -->
      <div class="flex flex-wrap items-center justify-center gap-2 mt-6" id="report-filter-bar">
        <button type="button" onclick="filterReports('all', this)" class="report-filter-btn active bg-primary text-white px-4 py-1.5 rounded-full text-xs font-semibold shadow-xs">
          All 9 Reports
        </button>
        <button type="button" onclick="filterReports('kundli', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-1.5 rounded-full text-xs font-semibold transition">
          Kundali &amp; Timing (2)
        </button>
        <button type="button" onclick="filterReports('annual', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-1.5 rounded-full text-xs font-semibold transition">
          Annual &amp; Monthly Transits (3)
        </button>
        <button type="button" onclick="filterReports('remedies', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-1.5 rounded-full text-xs font-semibold transition">
          Remedies &amp; Matchmaking (2)
        </button>
        <button type="button" onclick="filterReports('samudrik', this)" class="report-filter-btn bg-white hover:bg-orange-50 border border-gray-300 text-gray-700 px-4 py-1.5 rounded-full text-xs font-semibold transition">
          Palmistry &amp; Numerology (2)
        </button>
      </div>
    </div>

    <!-- Reports Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" id="reports-grid-container">
      <?php foreach ($reports_list as $report): 
        $target_url = home_url('/reports/' . $report['slug'] . '/');
      ?>
        <div class="report-card halo-card bg-white border border-[#ebd8cb] rounded-2xl overflow-hidden flex flex-col justify-between" data-category="<?php echo esc_attr($report['category']); ?>">
          
          <div>
            <!-- Top Cover Image with Overlay Badges -->
            <div class="relative bg-gradient-to-b from-[#f9ece0] to-[#f4dfcf] p-4 text-center border-b border-[#eedbd0]">
              <!-- Corner Ribbon Badge -->
              <span class="absolute top-3 left-3 <?php echo esc_attr($report['badge_class']); ?> px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider shadow-xs">
                <?php echo esc_html($report['badge']); ?>
              </span>

              <!-- Pages Count Tag -->
              <span class="absolute top-3 right-3 bg-white/95 text-gray-800 border border-gray-200 px-2.5 py-0.5 rounded text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                <i class="fa-regular fa-file-lines text-sacred-orange"></i>
                <?php echo esc_html($report['pages']); ?>
              </span>

              <!-- Cover Image Thumbnail -->
              <a href="<?php echo esc_url($target_url); ?>" class="block mx-auto pt-4 group">
                <img 
                  src="<?php echo esc_url($report['image']); ?>" 
                  alt="<?php echo esc_attr($report['name']); ?>" 
                  loading="lazy" 
                  class="h-44 sm:h-48 w-auto object-contain mx-auto drop-shadow-md transition-transform duration-300 group-hover:scale-105"
                />
              </a>
            </div>

            <!-- Card Body Content -->
            <div class="p-5 sm:p-6">
              <!-- Title -->
              <h3 class="font-serif text-lg sm:text-xl font-bold text-dark-brown leading-snug">
                <a href="<?php echo esc_url($target_url); ?>" class="hover:text-sacred-orange transition">
                  <?php echo esc_html($report['name']); ?>
                </a>
              </h3>
              <div class="text-xs font-serif text-deep-maroon font-semibold mt-0.5 mb-3">
                <?php echo esc_html($report['hindi_name']); ?>
              </div>

              <!-- Brief Description -->
              <p class="text-xs text-gray-600 leading-relaxed mb-4">
                <?php echo esc_html($report['desc']); ?>
              </p>

              <!-- Highlights Bullets -->
              <div class="border-t border-[#f4e6dc] pt-3 mb-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-2">Key Report Inclusions:</div>
                <ul class="space-y-1.5 text-xs text-gray-700">
                  <?php foreach ($report['highlights'] as $hl): ?>
                    <li class="flex items-start gap-2">
                      <i class="fa-solid fa-check text-sacred-orange text-xs mt-0.5 shrink-0"></i>
                      <span class="leading-tight"><?php echo esc_html($hl); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>

          <!-- Card Footer with Price & Buy Now CTA -->
          <div class="p-5 sm:p-6 pt-0 border-t border-[#f7ece3] bg-[#fffbf8]">
            <div class="flex items-center justify-between mb-3 pt-3">
              <div>
                <?php if ($report['price_sale'] == 0): ?>
                  <span class="text-2xl font-bold text-emerald-600 font-serif">FREE</span>
                  <span class="text-xs text-gray-400 line-through ml-1">₹<?php echo esc_html($report['price_reg']); ?></span>
                <?php else: ?>
                  <span class="text-2xl sm:text-3xl font-extrabold text-primary font-serif">₹<?php echo esc_html($report['price_sale']); ?></span>
                  <span class="text-xs text-gray-400 line-through ml-1">₹<?php echo esc_html($report['price_reg']); ?></span>
                <?php endif; ?>
              </div>
              <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                <?php echo esc_html($report['discount']); ?>
              </span>
            </div>

            <!-- BUY NOW BUTTON (Navigates directly to specific report page) -->
            <a 
              href="<?php echo esc_url($target_url); ?>" 
              class="w-full py-3 px-4 rounded-xl font-bold text-xs sm:text-sm tracking-wide text-center transition flex items-center justify-center gap-2 bg-sacred-orange hover:bg-orange-600 text-white shadow-xs group">
              <span><?php echo esc_html($report['button_text']); ?></span>
              <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
            </a>

            <!-- Sample PDF Preview Trigger -->
            <?php if (!empty($report['sample_pdf'])): ?>
              <div class="text-center mt-2.5">
                <a href="<?php echo esc_url($report['sample_pdf']); ?>" target="_blank" rel="noopener noreferrer" class="text-[11px] text-gray-500 hover:text-sacred-orange transition inline-flex items-center gap-1">
                  <i class="fa-solid fa-eye text-[10px]"></i>
                  <span>Preview Sample Report</span>
                </a>
              </div>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ========================================================= -->
<!-- 7. SECTION: SIDE-BY-SIDE FEATURE COMPARISON MATRIX        -->
<!-- ========================================================= -->
<section id="comparison-matrix" class="py-14 px-4 sm:px-6 lg:px-12 bg-white border-t border-[#eedbd0]">
  <div class="max-w-7xl mx-auto">
    <div class="text-center max-w-3xl mx-auto mb-10">
      <div class="inline-flex items-center gap-1.5 text-primary font-bold text-xs uppercase tracking-wider mb-2">
        <i class="fa-solid fa-table-list text-sacred-orange"></i>
        <span>Feature Decision Matrix</span>
      </div>
      <h2 class="font-serif text-2xl sm:text-3xl font-extrabold text-dark-brown">
        Compare Reports at a Glance
      </h2>
      <p class="text-gray-600 text-sm mt-2">
        Find the exact astrological blueprint suited for your current life juncture.
      </p>
    </div>

    <!-- Responsive Comparison Table -->
    <div class="overflow-x-auto rounded-2xl border border-[#ebd8cb] shadow-xs">
      <table class="w-full text-left text-xs sm:text-sm border-collapse min-w-[700px]">
        <thead>
          <tr class="bg-[#f9eee4] text-dark-brown font-serif border-b border-[#eedbd0]">
            <th class="p-4 font-bold text-sm">Report Name</th>
            <th class="p-4 font-bold text-sm text-center">Pages</th>
            <th class="p-4 font-bold text-sm text-center">Charts Included</th>
            <th class="p-4 font-bold text-sm text-center">Dasha &amp; Transits</th>
            <th class="p-4 font-bold text-sm text-center">Remedies (Upay)</th>
            <th class="p-4 font-bold text-sm text-center">Sale Price</th>
            <th class="p-4 font-bold text-sm text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#f5e6db] bg-white">
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Yearly Horoscope Detailed (2026-2027)
              <span class="block text-xs font-normal text-gray-500">Comprehensive 12-Month Life Roadmap</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">26 Pages</td>
            <td class="p-4 text-center">D1 Lagna &amp; D9 Navamsha</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> 12-Month Map</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Gemstones &amp; Mantras</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹999</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/yearly-horoscope-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Janma Kundali &amp; Harmonics
              <span class="block text-xs font-normal text-gray-500">Full 16 Shodashvargas &amp; Raja Yogas</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">26 Pages</td>
            <td class="p-4 text-center font-bold text-deep-maroon">Full 16 Shodashvargas</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Complete Shadbala</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Karmic Roadmap</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹99</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/janam-kundali-master-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Vedic Dasha Life Timing
              <span class="block text-xs font-normal text-gray-500">120-Year Vimshottari Timeline</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">26 Pages</td>
            <td class="p-4 text-center">D1, D9 &amp; D10 (Dashamsha)</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> 120-Yr Mahadasha</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Dasha Sandhi Upay</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹99</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/dasha-timing-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Kundali Dosh &amp; Remedial Audit
              <span class="block text-xs font-normal text-gray-500">Manglik, Kaal Sarp, Pitra Diagnostic</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">20 Pages</td>
            <td class="p-4 text-center">D1 &amp; Bhava Chalit</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Impact Windows</td>
            <td class="p-4 text-center font-bold text-emerald-700"><i class="fa-solid fa-shield-halved"></i> Exhaustive Shastric Upay</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹99</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/kundali-dosh-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Kundali Matching (Gun Milan)
              <span class="block text-xs font-normal text-gray-500">36 Points Ashtakoot Milan Matrix</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">24 Pages</td>
            <td class="p-4 text-center">Dual D1 &amp; D9 Charts</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Papasamaya Timing</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Nadi &amp; Bhakoot Upay</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹99</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/kundali-and-gun-matching-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Hast Rekha &amp; Palmistry
              <span class="block text-xs font-normal text-gray-500">Samudrik Topography &amp; Career Heatmap</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">26 Pages</td>
            <td class="p-4 text-center">Palm Mounts Topography</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> 5-Phase Age Timeline</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Hand Mudras &amp; Upay</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹299</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/palm-reading-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
          <tr class="hover:bg-orange-50/50 transition">
            <td class="p-4 font-semibold text-dark-brown">
              Master Numerology &amp; Lo Shu Grid
              <span class="block text-xs font-normal text-gray-500">Sankhya Shastra &amp; Name Harmonization</span>
            </td>
            <td class="p-4 text-center font-bold text-primary">22 Pages</td>
            <td class="p-4 text-center">3x3 Lo Shu Matrix</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> 90-Day Accelerator</td>
            <td class="p-4 text-center text-emerald-600"><i class="fa-solid fa-check"></i> Lucky Numbers &amp; Colors</td>
            <td class="p-4 text-center font-serif font-bold text-primary text-base">₹99</td>
            <td class="p-4 text-right">
              <a href="<?php echo esc_url(home_url('/reports/numerology-report/')); ?>" class="inline-flex items-center gap-1 bg-sacred-orange hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <span>Buy Now</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</section>

<!-- ========================================================= -->
<!-- 8. SECTION: TRUST, GUARANTEE & SACRED VALUES              -->
<!-- ========================================================= -->
<section class="py-14 px-4 sm:px-6 lg:px-12 bg-[#fffbf6] border-t border-[#eedbd0]">
  <div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <div class="bg-white border border-[#ebd8cb] rounded-2xl p-6 text-center shadow-xs">
        <div class="w-12 h-12 rounded-full bg-orange-100 text-sacred-orange flex items-center justify-center mx-auto mb-4 text-xl">
          <i class="fa-solid fa-compass"></i>
        </div>
        <h4 class="font-serif font-bold text-base text-dark-brown mb-1.5">Swiss Ephemeris Precision</h4>
        <p class="text-xs text-gray-600 leading-relaxed">
          Calculations calibrated to NASA JPL planetary positions down to exact arc-seconds for genuine astrological accuracy.
        </p>
      </div>

      <div class="bg-white border border-[#ebd8cb] rounded-2xl p-6 text-center shadow-xs">
        <div class="w-12 h-12 rounded-full bg-amber-100 text-sacred-gold flex items-center justify-center mx-auto mb-4 text-xl">
          <i class="fa-solid fa-lock"></i>
        </div>
        <h4 class="font-serif font-bold text-base text-dark-brown mb-1.5">100% Privacy &amp; Security</h4>
        <p class="text-xs text-gray-600 leading-relaxed">
          Your sacred birth details and consultation inquiries are strictly confidential and encrypted with 256-bit SSL protocols.
        </p>
      </div>

      <div class="bg-white border border-[#ebd8cb] rounded-2xl p-6 text-center shadow-xs">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-xl">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h4 class="font-serif font-bold text-base text-dark-brown mb-1.5">Lifetime Token Validity</h4>
        <p class="text-xs text-gray-600 leading-relaxed">
          Niyati AI Chat tokens never expire. Use your questions at your own pace whenever you need cosmic clarity.
        </p>
      </div>

      <div class="bg-white border border-[#ebd8cb] rounded-2xl p-6 text-center shadow-xs">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-4 text-xl">
          <i class="fa-solid fa-file-pdf"></i>
        </div>
        <h4 class="font-serif font-bold text-base text-dark-brown mb-1.5">Instant Email &amp; PDF</h4>
        <p class="text-xs text-gray-600 leading-relaxed">
          Receive your publication-grade PDF report directly in your inbox and account dashboard within 3-5 minutes.
        </p>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================= -->
<!-- 9. SECTION: FREQUENTLY ASKED QUESTIONS (FAQ)              -->
<!-- ========================================================= -->
<section id="faq-section" class="py-14 px-4 sm:px-6 lg:px-12 bg-cream-bg border-t border-[#eedbd0]">
  <div class="max-w-4xl mx-auto">
    <div class="text-center mb-10">
      <div class="inline-flex items-center gap-1.5 text-deep-maroon font-bold text-xs uppercase tracking-wider mb-2">
        <i class="fa-solid fa-circle-question text-sacred-orange"></i>
        <span>Got Questions?</span>
      </div>
      <h2 class="font-serif text-2xl sm:text-3xl font-extrabold text-dark-brown">
        Frequently Asked Questions
      </h2>
      <p class="text-gray-600 text-sm mt-1">
        Everything you need to know about our report pricing, Niyati AI token packages, and delivery.
      </p>
    </div>

    <!-- Accordion FAQ Items -->
    <div class="space-y-3.5" id="faq-accordion">
      
      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>How are the astrological reports delivered after purchase?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          Immediately upon completing your order, our astronomical calculation engine computes your divisional charts using Swiss Ephemeris algorithms. Your publication-grade 13–26 page PDF report is compiled and emailed to your inbox within 3–5 minutes. It is also permanently stored in your MeraRashifal account dashboard for easy download anytime.
        </p>
      </details>

      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>Do Niyati AI Chat Tokens expire?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          No. All Niyati AI Astrology Chat Token packs include <strong>100% Lifetime Validity</strong>. Unused questions remain safely stored in your account wallet indefinitely, allowing you to consult Niyati whenever you need astrological guidance.
        </p>
      </details>

      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>What is the difference between Niyati AI Chat and the Detailed PDF Reports?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          Niyati AI Chat is an interactive conversational astrologer that answers specific, ongoing questions about your career, marriage timing, transits, and daily decisions. The PDF Reports are comprehensive, publication-grade dossiers (13–26 pages) containing high-resolution 16 Shodashvarga charts, complete 120-year Dasha tables, mathematical Shadbala scores, and permanent lifetime reference documentation.
        </p>
      </details>

      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>Can I purchase reports or ask Niyati questions for multiple family members?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          Yes! You can order reports for yourself, spouse, children, or parents by providing their respective birth details on each report page. In Niyati AI Chat, you can easily save and switch between different family member profiles using the same unified credit wallet.
        </p>
      </details>

      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>What payment methods do you support?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          We accept all major payment methods through India's leading secure payment gateway (Razorpay): UPI (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, MasterCard, RuPay), Net Banking across 50+ banks, and Digital Wallets.
        </p>
      </details>

      <details class="group bg-white border border-[#ebd8cb] rounded-xl p-5 shadow-xs transition open:border-sacred-orange">
        <summary class="flex items-center justify-between cursor-pointer font-serif font-bold text-sm sm:text-base text-dark-brown select-none list-none">
          <span>Are my birth data and consultations private?</span>
          <span class="transition-transform group-open:rotate-180 text-sacred-orange">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </span>
        </summary>
        <p class="mt-3 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
          Your privacy is our sacred commitment. Your birth date, time, and coordinates are utilized purely for astronomical calculations and are never shared or sold to third parties. All transactions and chats are protected by 256-bit SSL encryption.
        </p>
      </details>

    </div>
  </div>
</section>

<!-- ========================================================= -->
<!-- 10. FINAL BOTTOM CALL TO ACTION STRIP                     -->
<!-- ========================================================= -->
<section class="py-12 px-4 sm:px-6 lg:px-12 bg-gradient-to-r from-primary via-[#b35900] to-primary text-white text-center relative overflow-hidden">
  <div class="max-w-3xl mx-auto relative z-10">
    <div class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full text-xs font-semibold mb-3">
      <i class="fa-solid fa-star text-sacred-gold"></i>
      <span>Begin Your Cosmic Journey Today</span>
    </div>
    <h3 class="font-serif text-2xl sm:text-3xl font-bold mb-3">
      Ready to Decode Your Destiny with Authentic Vedic Wisdom?
    </h3>
    <p class="text-orange-100 text-xs sm:text-sm max-w-xl mx-auto mb-6">
      Choose any of our 9 publication-grade reports from just ₹99 or recharge your Niyati AI Chat wallet from ₹40 with lifetime validity.
    </p>
    <div class="flex flex-wrap items-center justify-center gap-3">
      <a href="#reports-pricing" class="bg-white text-primary hover:bg-orange-50 font-bold px-6 py-3 rounded-xl text-xs sm:text-sm shadow-md transition flex items-center gap-2">
        <i class="fa-solid fa-scroll text-sacred-orange"></i>
        <span>Order An Astrology Report</span>
      </a>
      <button onclick="window.openNiyatiChat ? window.openNiyatiChat() : null" class="bg-sacred-gold hover:bg-yellow-500 text-dark-brown font-bold px-6 py-3 rounded-xl text-xs sm:text-sm shadow-md transition flex items-center gap-2">
        <i class="fa-solid fa-comments"></i>
        <span>Consult Niyati AI Now</span>
      </button>
    </div>
  </div>
</section>

<!-- ========================================================= -->
<!-- 11. FOOTER                                                -->
<!-- ========================================================= -->
<footer class="bg-[#241a15] text-[#b8a497] py-12 px-4 sm:px-6 lg:px-12 border-t border-[#3b2b23] text-xs">
  <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
    <div>
      <div class="flex items-center gap-2.5 mb-3">
        <img src="<?php echo esc_url($site_logo); ?>" alt="MeraRashifal Logo" class="h-9 w-auto brightness-200 invert opacity-90"/>
        <span class="font-serif text-xl font-bold text-white">MeraRashifal</span>
      </div>
      <p class="text-xs leading-relaxed text-[#9e8b7f]">
        Authentic Vedic astrology portal delivering Swiss Ephemeris precision calculations, daily Panchang, Shodashvarga Kundali dossiers, and AI-powered consultations.
      </p>
    </div>

    <div>
      <h5 class="font-serif font-bold text-white text-sm mb-3">Popular Reports</h5>
      <ul class="space-y-1.5 text-xs">
        <li><a href="<?php echo esc_url(home_url('/reports/yearly-horoscope-report/')); ?>" class="hover:text-white transition">Yearly Horoscope 2026-2027</a></li>
        <li><a href="<?php echo esc_url(home_url('/reports/janam-kundali-master-report/')); ?>" class="hover:text-white transition">Master Janma Kundali Report</a></li>
        <li><a href="<?php echo esc_url(home_url('/reports/dasha-timing-report/')); ?>" class="hover:text-white transition">Master Vedic Dasha Timing</a></li>
        <li><a href="<?php echo esc_url(home_url('/reports/kundali-and-gun-matching-report/')); ?>" class="hover:text-white transition">Kundali Matching (Gun Milan)</a></li>
        <li><a href="<?php echo esc_url(home_url('/reports/palm-reading-report/')); ?>" class="hover:text-white transition">Hast Rekha &amp; Palmistry Report</a></li>
      </ul>
    </div>

    <div>
      <h5 class="font-serif font-bold text-white text-sm mb-3">Vedic Services</h5>
      <ul class="space-y-1.5 text-xs">
        <li><a href="<?php echo esc_url(home_url('/panchang/')); ?>" class="hover:text-white transition">Today's Daily Panchang</a></li>
        <li><a href="<?php echo esc_url(home_url('/kundali-details/')); ?>" class="hover:text-white transition">Kundali Details &amp; Charts</a></li>
        <li><a href="<?php echo esc_url(home_url('/daily-choghadiya-muhurat/')); ?>" class="hover:text-white transition">Daily Choghadiya Muhurat</a></li>
        <li><a href="<?php echo esc_url($canonical_url); ?>" class="hover:text-white transition text-sacred-gold font-semibold">Pricing &amp; Token Packs</a></li>
        <li><a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="hover:text-white transition">My Account &amp; Downloads</a></li>
      </ul>
    </div>

    <div>
      <h5 class="font-serif font-bold text-white text-sm mb-3">Legal &amp; Trust</h5>
      <ul class="space-y-1.5 text-xs">
        <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="hover:text-white transition">Privacy Policy</a></li>
        <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>" class="hover:text-white transition">Terms &amp; Conditions</a></li>
        <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>" class="hover:text-white transition">About Us</a></li>
      </ul>
      <div class="mt-4 pt-3 border-t border-[#3b2b23] text-[11px] text-[#8c776b]">
        <p>🔒 256-bit SSL Secure Checkout</p>
        <p class="mt-1">© <?php echo date('Y'); ?> MeraRashifal. All Rights Reserved.</p>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto pt-6 border-t border-[#3b2b23] text-center text-[11px] text-[#7d695f]">
    <p>Disclaimer: Vedic astrology provides guidance based on classical astronomical calculations and traditional interpretations. Decisions should be taken with personal discretion.</p>
  </div>
</footer>

<!-- ========================================================= -->
<!-- 12. CLIENT-SIDE INTERACTIVITY SCRIPTS                     -->
<!-- ========================================================= -->
<script>
// Filter Reports by Category
function filterReports(category, btn) {
  var buttons = document.querySelectorAll('.report-filter-btn');
  buttons.forEach(function(b) {
    b.classList.remove('bg-primary', 'text-white');
    b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300');
  });

  btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300');
  btn.classList.add('bg-primary', 'text-white');

  var cards = document.querySelectorAll('.report-card');
  cards.forEach(function(card) {
    if (category === 'all' || card.getAttribute('data-category') === category) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

// Handle Niyati Pack Purchase / Recharge Sheet Open
function handleNiyatiPackBuy(packKey, btn) {
  if (typeof window.buyNiyatiPackDirect === 'function') {
    window.buyNiyatiPackDirect(packKey, btn);
  } else if (typeof window.openNiyatiRecharge === 'function') {
    window.openNiyatiRecharge(packKey);
  } else if (typeof window.openNiyatiChat === 'function') {
    window.openNiyatiChat();
  } else {
    var trigger = document.getElementById('niyati-floating-trigger');
    if (trigger) {
      trigger.click();
    } else {
      alert('Niyati AI Chat is loading. Please try again in a few seconds.');
    }
  }
}
</script>

<?php wp_footer(); ?>
</body>
</html>
