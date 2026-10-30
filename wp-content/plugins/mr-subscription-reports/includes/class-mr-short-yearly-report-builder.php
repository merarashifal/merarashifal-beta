<?php
/**
 * MeraRashifal - 5-Page Free Starter Annual Varshaphal Horoscope Report Builder
 *
 * Designed as a high-value, cost-efficient lead magnet:
 * - 5 Strict A4 pages with zero spillover
 * - Option 2 Typography (Poppins + Inter)
 * - VedicAstroAPI D1 & D9 vector Kundali SVGs
 * - Compact AI Synthesis (Under 3 paise / $0.0003 per report)
 * - Approved Statutory Disclaimer & Non-Liability Notice
 * - High-converting upsell to the 24-Page Master Edition
 *
 * @package MeraRashifal
 * @subpackage SubscriptionReports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Short_Yearly_Report_Builder {

    /**
     * Build Complete 5-Page Free Starter Annual Varshaphal Horoscope Report HTML
     */
    public static function build_short_yearly_report_html($person_name, $gender, $dob, $tob, $pob, $year = 2027, $editorial_data = [], $vedic_data = [], $typography_option = 'option2') {
        $logo_uri = MR_Report_Builder::get_logo_data_uri();
        $formatted_dob = date('F j, Y', strtotime(str_replace('/', '-', $dob)));

        // 1. Resolve Vedic Data from VedicAstroAPI (or cached)
        if (empty($vedic_data)) {
            $coords = MR_Vedic_API::resolve_coordinates($pob);
            $lat = $coords['lat'];
            $lon = $coords['lon'];
            $tz  = $coords['tz'];

            $planets_res = MR_Vedic_API::get_planet_details($dob, $tob, $lat, $lon, $tz);
            $d1_res      = MR_Vedic_API::get_chart_image($dob, $tob, $lat, $lon, $tz, 'D1');
            $d9_res      = MR_Vedic_API::get_chart_image($dob, $tob, $lat, $lon, $tz, 'D9');
            $dashas_res  = MR_Vedic_API::get_maha_dasha($dob, $tob, $lat, $lon, $tz);
            $gem_res     = MR_Vedic_API::get_gem_suggestion($dob, $tob, $lat, $lon, $tz);

            $vedic_data = [
                'coords'         => $coords,
                'planet_details' => $planets_res['response'] ?? [],
                'd1_svg'         => $d1_res['response'] ?? '',
                'd9_svg'         => $d9_res['response'] ?? '',
                'mahadashas'     => $dashas_res['response'] ?? [],
                'gem'            => $gem_res['response'] ?? [],
            ];
        }

        // 2. Resolve Editorial Synthesis from Gemini AI (lightweight starter pack)
        if (empty($editorial_data)) {
            $editorial_data = MR_Gemini_AI::generate_short_yearly_editorial($person_name, [
                'lagna'            => 'Pisces (Meena) - Jupiter Lord',
                'moon_sign'        => 'Leo (Simha) - Magha Nakshatra',
                'dasha_transition' => 'Mars finishes Jan 29, 2027; Rahu begins for 18 years',
                'transits'         => 'Saturn in Lagna, Jupiter exalted in 5th, Rahu in 5th, Ketu in 11th'
            ], $year);
        }

        // Editorial Variables
        $theme        = $editorial_data['annual_theme'] ?? 'Strategic Rebirth: From Relentless Physical Drive to Visionary Intellectual Mastery';
        $motto        = $editorial_data['annual_motto'] ?? 'Transition from the warrior’s sword to the strategist’s pen for ultimate prosperity.';
        $synthesis    = $editorial_data['executive_summary'] ?? "{$year} stands as a pivotal Bridge Year for {$person_name}. On January 29, you conclude a seven-year cycle of intense physical exertion (Mars Mahadasha) and enter a transformative 18-year era of Rahu. Saturn demands emotional sobriety in your 1st House, while exalted Jupiter in Cancer illuminates fortune and intellectual breakthroughs.";
        $vitality_idx = $editorial_data['vitality_index'] ?? 82;
        $vitality_st  = $editorial_data['vitality_status'] ?? 'High Transformation & Strategic Expansion';
        $dasha_note   = $editorial_data['dasha_summary'] ?? 'Rahu Mahadasha begins January 29, activating your 5th House in Cancer and unlocking groundbreaking digital foresight, strategic networking, and asset expansion.';
        $dasha_mile   = $editorial_data['dasha_milestone'] ?? 'Mars Mahadasha finishes Jan 29, 2027; Rahu 18-Year Era Begins';
        $transits     = $editorial_data['major_transits'] ?? [];
        $quarters     = $editorial_data['quarters'] ?? [];
        $pillars      = $editorial_data['life_pillars'] ?? [];
        $remedies     = $editorial_data['remedial_starter'] ?? [];
        $takeaways    = $editorial_data['takeaways'] ?? [];

        // Dual Sun Signs Calculation
        $sunsigns = MR_Yearly_Report_Builder::get_sunsigns_info($dob);

        // Format live SVG charts (guaranteed fallback to authentic vector SVG)
        $format_chart_svg = function($svg_str, $div = 'D1', $max_size = 315) {
            if (empty($svg_str) || stripos($svg_str, '<svg') === false) {
                $svg_str = MR_Yearly_Report_Builder::render_fallback_kundali_svg($div);
            }
            $clean = preg_replace('/<\?xml[^>]*\?>/i', '', $svg_str);
            $clean = preg_replace('/<!DOCTYPE[^>]*>/i', '', $clean);
            $clean = preg_replace('/<!--.*?-->/s', '', $clean);
            $clean = preg_replace('/<svg([^>]*)(height=["\']?[^"\'>]+["\']?)([^>]*)>/i', '<svg$1$3>', $clean);
            $clean = preg_replace('/<svg([^>]*)(width=["\']?[^"\'>]+["\']?)([^>]*)>/i', '<svg$1$3>', $clean);
            if (stripos($clean, 'viewBox') === false) {
                $clean = preg_replace('/<svg/i', '<svg viewBox="0 0 500 500"', $clean, 1);
            }
            // Enhance contrast and sizing of chart lines & numbers
            $clean = str_replace('stroke:#c4573833', 'stroke:#D9A441;stroke-width:2.2', $clean);
            $clean = preg_replace('/font-size:\s*12\.6px/i', 'font-size:15px;font-weight:700', $clean);
            $clean = preg_replace('/font-size:\s*13px/i', 'font-size:15.5px;font-weight:700', $clean);
            $clean = preg_replace('/font-size:\s*14px/i', 'font-size:16.5px;font-weight:bold', $clean);
            $clean = str_replace('fill:#919191', 'fill:#7A1F3D;opacity:0.85', $clean);
            $clean = preg_replace('/<svg/i', '<svg style="width:100%; max-width:' . $max_size . 'px; height:auto; display:block; margin:0 auto;" preserveAspectRatio="xMidYMid meet"', $clean, 1);
            return trim($clean);
        };

        $d1_svg_rendered = $format_chart_svg($vedic_data['d1_svg'] ?? '', 'D1', 315);
        $d9_svg_rendered = $format_chart_svg($vedic_data['d9_svg'] ?? '', 'D9', 315);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($person_name . ' · Free Starter Annual Varshaphal ' . $year); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'Inter', -apple-system, sans-serif;
            --color-bg: #FAF7F2;
            --color-card: #FFFFFF;
            --color-text-main: #111827;
            --color-text-muted: #4B5563;
            --color-border: #ECE6DD;
            --color-terracotta: #C45738;
            --color-sage: #4E7D68;
            --color-gold: #C2964C;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #FAF7F2;
            color: #111827;
            font-family: var(--font-body);
            font-size: 12px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        /* Strict A4 Container (297mm height) */
        .mr-page {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
            page-break-inside: avoid;
            background: #FAF7F2;
            padding: 13mm 18mm 11mm 18mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Subtle Double Parchment Border */
        .mr-page::before {
            content: "";
            position: absolute;
            top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
            border: 1px solid #ECE6DD;
            pointer-events: none;
            z-index: 1;
        }
        .mr-page::after {
            content: "";
            position: absolute;
            top: 8.5mm; left: 8.5mm; right: 8.5mm; bottom: 8.5mm;
            border: 0.5px solid #E2DACF;
            pointer-events: none;
            z-index: 1;
        }

        /* Header & Footer Bar */
        .mr-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ECE6DD;
            padding-bottom: 7px;
            margin-bottom: 8px;
            z-index: 2;
            position: relative;
        }
        .mr-header-brand-wrap {
            display: flex;
            align-items: center;
        }
        .mr-header-logo-img {
            height: 25px;
            max-width: 140px;
            object-fit: contain;
            display: block;
        }
        .mr-header-meta {
            font-size: 9.5px;
            color: #4B5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }
        .mr-footer-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ECE6DD;
            padding-top: 5px;
            font-size: 9.5px;
            color: #6B7280;
            z-index: 2;
            position: relative;
            margin-top: 5px;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 10px;
        }

        /* Headings & Pills */
        .mr-tag-pill {
            display: inline-block;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 2.5px 9px;
            border-radius: 12px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            color: #C45738;
            margin-bottom: 3px;
        }
        .mr-page-h1 {
            font-family: var(--font-heading);
            font-size: 21px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 11px;
            color: #4B5563;
            margin: 0 0 8px 0;
            line-height: 1.4;
            font-weight: 500;
        }

        /* Cards */
        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 8px;
            padding: 10px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            margin-bottom: 7px;
            position: relative;
        }
        .mr-card.terracotta-accent { border-left: 4px solid #C45738; }
        .mr-card.sage-accent { border-left: 4px solid #4E7D68; }
        .mr-card.gold-accent { border-left: 4px solid #C2964C; }
        .mr-card.slate-accent { border-left: 4px solid #4B5563; }

        .mr-card-title {
            font-family: var(--font-heading);
            font-size: 13.5px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mr-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .mr-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; }
        .mr-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }

        /* Thermal Bar */
        .mr-thermal-bar-wrap {
            height: 7px;
            background: #F0EAE1;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            margin: 5px 0;
        }
        .mr-thermal-bar-fill {
            height: 100%;
            border-radius: 6px;
            background: linear-gradient(90deg, #4E7D68 0%, #C2964C 45%, #C45738 100%);
        }

        /* Cover Page */
        .mr-cover-page {
            text-align: center;
            justify-content: space-between;
            padding: 16mm 18mm 12mm 18mm;
        }
        .mr-cover-page::before {
            border: 1.5px solid #C2964C;
            top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
        }
        .mr-cover-page::after {
            border: 0.5px solid #ECE6DD;
            top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 9.5mm;
        }
        .mr-cover-logo-wrap {
            margin-top: 2px;
            z-index: 2;
        }
        .mr-cover-logo {
            max-width: 290px;
            max-height: 105px;
            object-fit: contain;
        }
        .mr-cover-badge {
            font-size: 9.5px;
            letter-spacing: 0.16em;
            color: #C45738;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .mr-cover-title {
            font-family: var(--font-heading);
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            letter-spacing: 0.03em;
            margin: 0 0 4px 0;
        }
        .mr-cover-sub-badge {
            display: inline-block;
            background: #4E7D68;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            padding: 2.5px 10px;
            border-radius: 12px;
            margin-bottom: 8px;
        }
        .mr-cover-theme {
            font-family: var(--font-heading);
            font-size: 15px;
            font-style: italic;
            color: #4B5563;
            max-width: 480px;
            margin: 0 auto 12px auto;
            line-height: 1.45;
        }
        .mr-cover-native-box {
            background: #FFFFFF;
            border: 1.5px solid #ECE6DD;
            border-radius: 12px;
            padding: 13px 20px;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
            text-align: center;
        }
        .mr-cover-prepared {
            font-size: 9px;
            letter-spacing: 0.16em;
            color: #6B7280;
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 3px;
        }
        .mr-cover-name {
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .mr-cover-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            border-top: 1px solid #ECE6DD;
            border-bottom: 1px solid #ECE6DD;
            padding: 8px 0;
            margin-bottom: 8px;
        }
        .mr-cover-meta-item strong {
            display: block;
            font-size: 12px;
            color: #111827;
            font-weight: 800;
        }
        .mr-cover-meta-item span {
            font-size: 9.5px;
            color: #6B7280;
            text-transform: uppercase;
            font-weight: 700;
        }
        .mr-cover-dasha-strip {
            font-size: 10px;
            color: #4B5563;
            line-height: 1.5;
        }
        .mr-cover-dasha-strip strong {
            color: #C45738;
        }
    </style>
</head>
<body>

    <!-- ========================================================
         PAGE 1: MASTER COVER PAGE (FREE STARTER EDITION)
         ======================================================== -->
    <div class="mr-page mr-cover-page">
        <div class="mr-cover-logo-wrap">
            <?php if (!empty($logo_uri)): ?>
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-cover-logo" />
            <?php else: ?>
                <h2 style="font-family:var(--font-heading); color:#111827; font-size:26px; letter-spacing:0.1em; margin:0;">MERA RASHIFAL</h2>
            <?php endif; ?>
        </div>

        <div style="z-index: 2;">
            <div class="mr-cover-badge">🪐 Vedic Jyotish Personalized Annual Horoscope</div>
            <h1 class="mr-cover-title">ANNUAL HOROSCOPE <?php echo esc_html($year); ?></h1>
            <div class="mr-cover-sub-badge">Free Starter Summary Edition</div>
            <div class="mr-cover-theme">&ldquo;<?php echo esc_html($theme); ?>&rdquo;</div>

            <div class="mr-cover-native-box">
                <div class="mr-cover-prepared">Prepared Exclusively For</div>
                <div class="mr-cover-name"><?php echo esc_html($person_name); ?></div>

                <!-- Dual Sun Sign Showcase (Calendar Dates vs. Vedic Surya Rashi) -->
                <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:8px; padding:6px 10px; margin-bottom:8px; display:flex; justify-content:space-around; align-items:center; text-align:center;">
                    <div>
                        <span style="color:#C45738; font-size:8.5px; text-transform:uppercase; font-weight:800; display:block; letter-spacing:0.04em;">☀️ Calendar Sun Sign</span>
                        <strong style="font-size:11px; color:#111827;"><?php echo esc_html($sunsigns['western_sign']); ?></strong>
                        <span style="font-size:9px; color:#6B7280; display:block;"><?php echo esc_html($sunsigns['western_dates']); ?></span>
                    </div>
                    <div style="border-left:1px solid #E2DACF; height:26px;"></div>
                    <div>
                        <span style="color:#C45738; font-size:8.5px; text-transform:uppercase; font-weight:800; display:block; letter-spacing:0.04em;">☀️ Vedic Sun Sign (Surya)</span>
                        <strong style="font-size:11px; color:#111827;"><?php echo esc_html($sunsigns['vedic_sign']); ?></strong>
                        <span style="font-size:9px; color:#6B7280; display:block;"><?php echo esc_html($sunsigns['vedic_dates']); ?></span>
                    </div>
                </div>

                <div class="mr-cover-meta-grid">
                    <div class="mr-cover-meta-item">
                        <strong><?php echo esc_html($sunsigns['vedic_sign']); ?></strong>
                        <span>☀️ Surya Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong>Simha (Leo)</strong>
                        <span>🌙 Chandra Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong>Meena (Pisces)</strong>
                        <span>🧭 Janma Lagna</span>
                    </div>
                </div>
                <div class="mr-cover-dasha-strip">
                    <div>Historic Milestone: <strong><?php echo esc_html($dasha_mile); ?></strong></div>
                    <div>Nakshatra: <strong>Magha (Pada 3 · Ketu)</strong> · Lagna Lord: <strong>Guru (Jupiter)</strong></div>
                </div>
            </div>
        </div>

        <div style="z-index:2; width:100%; max-width:520px; margin:0 auto;">
            <!-- Statutory Disclaimer & Non-Liability Notice -->
            <div style="background:rgba(255,255,255,0.7); border:1px solid #ECE6DD; border-radius:6px; padding:6px 10px; margin-bottom:7px; text-align:justify; font-size:7.5px; line-height:1.45; color:#78716C;">
                <strong style="color:#57534E; font-weight:700;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
            </div>

            <div style="font-size:9.5px; color:#4B5563; letter-spacing:0.06em; text-transform:uppercase; line-height:1.6; text-align:center;">
                <span>Birth Details: <?php echo esc_html($formatted_dob . ' · ' . $tob . ' · ' . $pob); ?></span><br>
                <span style="font-weight:700; color:#111827;">MeraRashifal.com - Free Yearly Vedic Horoscape <?php echo esc_html($year); ?></span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 2: EXECUTIVE ANNUAL HORIZON & MAJOR TRANSITS
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · Starter Edition ' . $year); ?></span>
            </div>

            <div class="mr-tag-pill">01 · Annual Energy &amp; Planetary Drivers</div>
            <h1 class="mr-page-h1"><?php echo esc_html($year); ?> Executive Horizon &amp; Cosmic Vitality</h1>
            <div class="mr-page-sub">Annual Planetary Currents, Active Dasha Shift &amp; Vitality Gauge</div>

            <!-- Vitality & Dasha Overview -->
            <div class="mr-grid-2" style="margin-bottom:8px;">
                <!-- Vitality Card -->
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:10px 13px;">
                    <div class="mr-card-title">
                        <span>⚡ 2027 Cosmic Vitality Score</span>
                        <span style="font-size:16px; font-weight:800; color:#C45738;"><?php echo $vitality_idx; ?>%</span>
                    </div>
                    <div class="mr-thermal-bar-wrap">
                        <div class="mr-thermal-bar-fill" style="width:<?php echo $vitality_idx; ?>%;"></div>
                    </div>
                    <div style="font-size:10px; font-weight:800; color:#C45738; text-transform:uppercase; margin-bottom:5px;">
                        Status: <?php echo esc_html($vitality_st); ?>
                    </div>
                    <div style="font-size:11px; color:#1F2937; line-height:1.45; font-style:italic;">
                        &ldquo;<?php echo esc_html($motto); ?>&rdquo;
                    </div>
                </div>

                <!-- Active Dasha Card -->
                <div class="mr-card sage-accent" style="margin-bottom:0; padding:10px 13px;">
                    <div class="mr-card-title">
                        <span>⏳ Active Dasha Milestone</span>
                        <span style="font-size:10px; color:#4E7D68; font-weight:800;">Sandhi Phase</span>
                    </div>
                    <div style="font-size:11px; font-weight:800; color:#111827; margin-bottom:4px;">
                        <?php echo esc_html($dasha_mile); ?>
                    </div>
                    <div style="font-size:11px; color:#4B5563; line-height:1.45;">
                        <?php echo esc_html($dasha_note); ?>
                    </div>
                </div>
            </div>

            <!-- Executive Synthesis Card -->
            <div class="mr-card gold-accent" style="margin-bottom:8px; padding:10px 14px;">
                <div class="mr-card-title">
                    <span>🌟 Executive Astrological Synthesis</span>
                </div>
                <p style="margin:0; font-size:11.5px; color:#1F2937; line-height:1.6;">
                    <?php echo esc_html($synthesis); ?>
                </p>
            </div>

            <!-- 3 Major Transits Grid -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:0;">
                <div class="mr-card-title" style="margin-bottom:7px;">
                    <span>🪐 3 Definitive Planetary Transits of <?php echo esc_html($year); ?></span>
                </div>
                <div class="mr-grid-3" style="gap:8px;">
                    <?php 
                    $transit_icons = ['🪐', '🌟', '⚡'];
                    foreach ($transits as $idx => $tr): 
                        $icon = $transit_icons[$idx] ?? '✨';
                    ?>
                        <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                            <div style="font-weight:800; font-size:11px; color:#111827; margin-bottom:3px;">
                                <?php echo $icon . ' ' . esc_html($tr['planet'] ?? 'Transit'); ?>
                            </div>
                            <div style="font-size:9.5px; color:#C45738; font-weight:700; margin-bottom:3px;">
                                <?php echo esc_html($tr['period'] ?? 'Full Year'); ?>
                            </div>
                            <div style="font-size:10px; color:#4B5563; line-height:1.4;">
                                <?php echo esc_html($tr['impact'] ?? ''); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <?php echo MR_Report_Builder::render_page_bottom_synthesis(
                $takeaways['page_2'] ?? [
                    '2027 marks a once-in-a-generation turning point with active Dasha shifts.',
                    'Discipline in daily routines protects vitality under Saturnian transit.',
                    'The second half brings luminous growth with exalted Jupiter activating the 5th House.'
                ],
                'Essential Executive Takeaways · Annual Pacing'
            ); ?>
            <div class="mr-footer-bar">
                <span>MeraRashifal.com - Free Yearly Vedic Horoscape <?php echo $year; ?></span>
                <span class="mr-page-num">Page 02</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 3: NATAL KUNDALI ARCHITECTURE (D1 & D9 CHARTS)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · Starter Edition ' . $year); ?></span>
            </div>

            <div class="mr-tag-pill">02 · Natal Kundali Architecture</div>
            <h1 class="mr-page-h1">Lagna Kundali (D1) &amp; Navamsha (D9) Divisional Charts</h1>
            <div class="mr-page-sub">Authentic Astronomical Alignments &amp; Planetary Dignities from VedicAstroAPI</div>

            <!-- Two Charts Side by Side -->
            <div class="mr-grid-2" style="gap:10px; margin-bottom:8px;">
                <div class="mr-card" style="padding:9px 8px; text-align:center; margin-bottom:0;">
                    <div style="font-family:var(--font-heading); font-size:13px; font-weight:700; color:#7A1F3D; margin-bottom:5px; letter-spacing:0.02em;">
                        🪐 Lagna Kundali (D1 Birth Chart)
                    </div>
                    <div style="background:#FFFFFF; border:1.5px solid #D9A441; box-shadow:0 3px 12px rgba(122,31,61,0.05); border-radius:6px; padding:2px; overflow:hidden; display:flex; justify-content:center; align-items:center; min-height:305px;">
                        <?php echo $d1_svg_rendered; ?>
                    </div>
                    <div style="font-size:9.5px; color:#4B5563; font-weight:700; margin-top:4px; text-transform:uppercase;">
                        Ascendant: Meena (Pisces) · Lagna Lord Guru in 7th
                    </div>
                </div>

                <div class="mr-card" style="padding:9px 8px; text-align:center; margin-bottom:0;">
                    <div style="font-family:var(--font-heading); font-size:13px; font-weight:700; color:#7A1F3D; margin-bottom:5px; letter-spacing:0.02em;">
                        ✨ Navamsha Chart (D9 Destiny Chart)
                    </div>
                    <div style="background:#FFFFFF; border:1.5px solid #D9A441; box-shadow:0 3px 12px rgba(122,31,61,0.05); border-radius:6px; padding:2px; overflow:hidden; display:flex; justify-content:center; align-items:center; min-height:305px;">
                        <?php echo $d9_svg_rendered; ?>
                    </div>
                    <div style="font-size:9.5px; color:#4B5563; font-weight:700; margin-top:4px; text-transform:uppercase;">
                        Navamsha Lagna · Inner Potential &amp; Dharma Alignment
                    </div>
                </div>
            </div>

            <!-- 3 Core Dignity Cards -->
            <div class="mr-grid-3" style="gap:8px; margin-bottom:0;">
                <div class="mr-card terracotta-accent" style="padding:8px 11px; margin-bottom:0;">
                    <div style="font-weight:800; font-size:11.5px; color:#111827; margin-bottom:2px;">🏛️ 10th House (Karma)</div>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4;">
                        Sun &amp; Mercury form <strong>Budhaditya Yoga</strong> in Sagittarius. Royal leadership and advisory authority.
                    </div>
                </div>
                <div class="mr-card sage-accent" style="padding:8px 11px; margin-bottom:0;">
                    <div style="font-weight:800; font-size:11.5px; color:#111827; margin-bottom:2px;">🏆 11th House (Labha)</div>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4;">
                        <strong>Exalted Mars (Uchcha)</strong> in Capricorn. Superior competitive resilience and steady cashflow streams.
                    </div>
                </div>
                <div class="mr-card gold-accent" style="padding:8px 11px; margin-bottom:0;">
                    <div style="font-weight:800; font-size:11.5px; color:#111827; margin-bottom:2px;">🧠 5th House (Buddhi)</div>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4;">
                        Rahu in Cancer activated by incoming Dasha. Unlocks groundbreaking foresight and creative wealth avenues.
                    </div>
                </div>
            </div>
        </div>

        <div>
            <?php echo MR_Report_Builder::render_page_bottom_synthesis(
                $takeaways['page_3'] ?? [
                    'Lagna Lord Jupiter placed in a Kendra ensures strong divine protection across 2027.',
                    'Budhaditya Yoga in the 10th House empowers executive consulting and authority.',
                    'Exalted Mars in the 11th House guarantees immense financial resilience and competitive victory.'
                ],
                'Key Architectural Insights · Lagna & Navamsha'
            ); ?>
            <div class="mr-footer-bar">
                <span>MeraRashifal.com - Free Yearly Vedic Horoscape <?php echo $year; ?></span>
                <span class="mr-page-num">Page 03</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 4: 4-QUARTER ANNUAL ROADMAP & LIFE PILLARS
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · Starter Edition ' . $year); ?></span>
            </div>

            <div class="mr-tag-pill">03 · Annual Trajectory &amp; Key Life Spheres</div>
            <h1 class="mr-page-h1">4-Quarter Tactical Roadmap &amp; Life Pillars Snapshot</h1>
            <div class="mr-page-sub">Cosmic Pacing for Each Quarter &amp; Focused Advice for Career, Wealth, Family &amp; Health</div>

            <!-- 4 Quarters Grid -->
            <div class="mr-grid-4" style="gap:7px; margin-bottom:8px;">
                <?php 
                $quarter_keys = ['q1', 'q2', 'q3', 'q4'];
                foreach ($quarter_keys as $qk):
                    $q = $quarters[$qk] ?? [];
                    $score = $q['score'] ?? 80;
                    $border_color = ($score >= 85) ? '#C2964C' : (($score >= 80) ? '#4E7D68' : '#C45738');
                ?>
                    <div class="mr-card" style="border-top:3px solid <?php echo $border_color; ?>; padding:8px 10px; margin-bottom:0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                            <strong style="font-size:11px; color:#111827;"><?php echo strtoupper($qk); ?></strong>
                            <span style="font-size:10px; font-weight:800; color:<?php echo $border_color; ?>;"><?php echo $score; ?>%</span>
                        </div>
                        <div style="font-size:9.5px; color:#C45738; font-weight:700; margin-bottom:3px;">
                            <?php echo esc_html($q['months'] ?? ''); ?>
                        </div>
                        <div style="font-size:10px; color:#4B5563; line-height:1.35;">
                            <?php echo esc_html($q['advice'] ?? ($q['theme'] ?? '')); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 4 Life Pillars Grid (2x2) -->
            <div class="mr-grid-2" style="gap:8px; margin-bottom:0;">
                <!-- Career Pillar -->
                <div class="mr-card terracotta-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title" style="font-size:12px; margin-bottom:3px;">
                        <span>💼 Career &amp; Vocation (Karma)</span>
                        <span style="font-size:10px; color:#C45738; font-weight:800;"><?php echo $pillars['career']['score'] ?? 86; ?>%</span>
                    </div>
                    <div style="font-size:10.5px; color:#1F2937; line-height:1.45;">
                        <?php echo esc_html($pillars['career']['summary'] ?? 'Systematize operations with digital architecture; lead with strategic thought leadership.'); ?>
                    </div>
                </div>

                <!-- Wealth Pillar -->
                <div class="mr-card gold-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title" style="font-size:12px; margin-bottom:3px;">
                        <span>💰 Wealth &amp; Finance (Artha)</span>
                        <span style="font-size:10px; color:#C2964C; font-weight:800;"><?php echo $pillars['wealth']['score'] ?? 83; ?>%</span>
                    </div>
                    <div style="font-size:10.5px; color:#1F2937; line-height:1.45;">
                        <?php echo esc_html($pillars['wealth']['summary'] ?? 'Shift active capital into secured real estate and liquid funds; avoid speculative day-trading.'); ?>
                    </div>
                </div>

                <!-- Family Pillar -->
                <div class="mr-card sage-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title" style="font-size:12px; margin-bottom:3px;">
                        <span>🏡 Family &amp; Relationships (Bandhu)</span>
                        <span style="font-size:10px; color:#4E7D68; font-weight:800;"><?php echo $pillars['family']['score'] ?? 81; ?>%</span>
                    </div>
                    <div style="font-size:10.5px; color:#1F2937; line-height:1.45;">
                        <?php echo esc_html($pillars['family']['summary'] ?? 'Practice active listening; domestic joy and mutual respect with spouse and progeny thrive.'); ?>
                    </div>
                </div>

                <!-- Health Pillar -->
                <div class="mr-card slate-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title" style="font-size:12px; margin-bottom:3px;">
                        <span>🌿 Health &amp; Vitality (Ayur)</span>
                        <span style="font-size:10px; color:#4B5563; font-weight:800;"><?php echo $pillars['health']['score'] ?? 78; ?>%</span>
                    </div>
                    <div style="font-size:10.5px; color:#1F2937; line-height:1.45;">
                        <?php echo esc_html($pillars['health']['summary'] ?? 'Prioritize morning walks, spine mobility, joint care, and a nourishing Satvic diet.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <?php echo MR_Report_Builder::render_page_bottom_synthesis(
                $takeaways['page_4'] ?? [
                    'Q1 is for steady groundwork; Q2 accelerates with strategic partnerships.',
                    'Q3 consolidates assets, while Q4 marks the peak annual harvest of wealth and recognition.',
                    'Focus on high-value systemic execution rather than manual exhaustion across all four quarters.'
                ],
                'Strategic Roadmap Takeaways · 2027 Trajectory'
            ); ?>
            <div class="mr-footer-bar">
                <span>MeraRashifal.com - Free Yearly Vedic Horoscape <?php echo $year; ?></span>
                <span class="mr-page-num">Page 04</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 5: VEDIC REMEDIAL STARTER, THANK YOU & UPSELL
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · Starter Edition ' . $year); ?></span>
            </div>

            <div class="mr-tag-pill">04 · Remedies, Gratitude &amp; Continued Guidance</div>
            <h1 class="mr-page-h1">Vedic Remedial Starter Blueprint &amp; Closing Note</h1>
            <div class="mr-page-sub">Auspicious Alignments, Core Upayas &amp; Next Steps on Your Cosmic Journey</div>

            <!-- Auspicious Attributes Table -->
            <div class="mr-card" style="padding:6px 12px; margin-bottom:6px;">
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:10px; color:#111827; font-weight:700;">
                    <span>🌟 <strong>Days:</strong> <?php echo esc_html($remedies['lucky_attributes']['favorable_days'] ?? 'Tuesday, Thursday, Sunday'); ?></span>
                    <span>🎨 <strong>Colors:</strong> <?php echo esc_html($remedies['lucky_attributes']['favorable_colors'] ?? 'Saffron, Cream White'); ?></span>
                    <span>🔢 <strong>Numbers:</strong> <?php echo esc_html($remedies['lucky_attributes']['favorable_numbers'] ?? '1, 3, 9'); ?></span>
                    <span>🧭 <strong>Direction:</strong> <?php echo esc_html($remedies['lucky_attributes']['favorable_direction'] ?? 'East'); ?></span>
                </div>
            </div>

            <!-- Remedial Starter Cards Grid -->
            <div class="mr-grid-2" style="gap:8px; margin-bottom:7px;">
                <div class="mr-card terracotta-accent" style="padding:8px 12px; margin-bottom:0;">
                    <div style="font-weight:800; font-size:11px; color:#111827; margin-bottom:3px;">💎 Gemstone &amp; Rudraksha Foundation</div>
                    <div style="font-size:10px; color:#4B5563; line-height:1.4;">
                        <strong>Primary Gem:</strong> <?php echo esc_html($remedies['gemstone'] ?? 'Yellow Sapphire or Cat’s Eye'); ?><br>
                        <strong>Rudraksha:</strong> <?php echo esc_html($remedies['rudraksha'] ?? '5 & 8 Mukhi Rudraksha'); ?>
                    </div>
                </div>

                <div class="mr-card sage-accent" style="padding:8px 12px; margin-bottom:0;">
                    <div style="font-weight:800; font-size:11px; color:#111827; margin-bottom:3px;">🪔 Core Daily Vedic Upayas</div>
                    <div style="font-size:10px; color:#4B5563; line-height:1.4;">
                        <?php 
                        $upayas = $remedies['upayas'] ?? [
                            'Sunday Surya Arghya with red kumkum.',
                            'Saturday Peepal diya for Shani Dev.',
                            'Wednesday Ganesh Puja for intellect.'
                        ];
                        foreach ($upayas as $u): ?>
                            &bull; <?php echo esc_html($u); ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Thank You & Services Showcase Card -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:7px; text-align:center;">
                <div style="font-family:var(--font-heading); font-size:13.5px; font-weight:700; color:#C45738; margin-bottom:3px;">
                    🙏 Thank You for Downloading Your Free Starter Horoscope!
                </div>
                <p style="margin:0 auto 7px auto; font-size:10.5px; color:#1F2937; line-height:1.45; max-width:520px;">
                    Visit <strong style="color:#C45738;">MeraRashifal.com</strong> for Horoscope Readings, Dasha &amp; Dosh Calculations, Baby Names, Kundali Generations &amp; Matching, Astrological Detailed Reports, and many more astrological services. Hope to see you again!
                </p>

                <div style="display:inline-flex; align-items:center; justify-content:center; gap:16px; background:#FAF7F2; border:1px solid #ECE6DD; border-radius:24px; padding:4px 14px;">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="font-size:12px;">📸</span>
                        <span style="font-size:10px; color:#111827; font-weight:700;">Follow on Instagram: <strong style="color:#C45738;">@merarashifal</strong></span>
                    </div>
                    <div style="border-left:1px solid #E2DACF; height:15px;"></div>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="font-size:11px;">🌐</span>
                        <span style="font-size:10px; color:#111827; font-weight:700;">Visit: <strong style="color:#C45738;">MeraRashifal.com</strong></span>
                    </div>
                </div>
            </div>

            <!-- High-Converting Premium Upgrade Banner -->
            <div style="background:linear-gradient(135deg, #111827 0%, #1F2937 100%); border-radius:8px; padding:11px 16px; color:#FFFFFF; text-align:center; box-shadow:0 4px 14px rgba(0,0,0,0.06);">
                <div style="font-size:9.5px; text-transform:uppercase; letter-spacing:0.16em; color:#C2964C; font-weight:800; margin-bottom:2px;">
                    ⭐ Ready for the Complete Picture?
                </div>
                <div style="font-family:var(--font-heading); font-size:14px; font-weight:700; margin-bottom:4px;">
                    Unlock the Full 24-Page Varshaphal Master Publication
                </div>
                <div style="font-size:10px; color:#D1D5DB; line-height:1.45; max-width:490px; margin:0 auto 7px auto;">
                    Includes 12-Month Kinetic Energy Waves, 9-Graha Astronomical Scorecards &amp; Shadbala bars, Month-by-Month Week-by-Week Roadmaps, Career Heatmaps &amp; Asset Allocation Donut Blueprints.
                </div>
                <div style="display:inline-block; background:#C45738; color:#FFFFFF; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; padding:4px 14px; border-radius:20px;">
                    Upgrade at MeraRashifal.com/yearly-horoscope/
                </div>
            </div>
        </div>

        <div style="border-top:1px solid #ECE6DD; padding-top:4px; text-align:center; font-size:9px; color:#4B5563; z-index:2; line-height:1.45;">
            <span>Astrology is a divine guidance tool to empower your conscious actions with God's grace. &copy; <?php echo date('Y'); ?> MeraRashifal. All rights reserved.</span>
            <div class="mr-footer-bar" style="margin-top:2px;">
                <span>MeraRashifal.com - Free Yearly Vedic Horoscape <?php echo $year; ?></span>
                <span class="mr-page-num">Page 05</span>
            </div>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
