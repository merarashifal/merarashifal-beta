<?php
/**
 * Class MR_Yearly_Report_Builder
 *
 * Generates the 5-Page Free Annual Horoscope & Year Ahead Forecast Report (2026)
 * Strictly 5 Physical Pages with Zero Overflow.
 * High-conversion, cost-effective lead magnet adhering to the Mera Rashifal design standard.
 */

if (!defined('ABSPATH')) {
    // Standalone / CLI fallback
}

class MR_Yearly_Report_Builder {

    /**
     * Resolve Official MeraRashifal Logo as Data URI
     */
    public static function get_logo_data_uri() {
        if (class_exists('MR_Report_Builder')) {
            $uri = MR_Report_Builder::get_logo_data_uri();
            if (!empty($uri)) {
                return $uri;
            }
        }
        $logo_path = dirname(__DIR__) . '/assets/images/logo.png';
        if (file_exists($logo_path)) {
            $type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $data = file_get_contents($logo_path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        $wp_logo = 'c:\\xampp\\htdocs\\Merarashifal\\wp-content\\uploads\\merarashifal-logo.png';
        if (file_exists($wp_logo)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($wp_logo));
        }
        return '';
    }

    /**
     * Standard Page Footer Bar
     */
    public static function render_page_footer($page_num, $section_name, $total_pages = 5) {
        ob_start();
        ?>
        <div class="mr-page-footer">
            <span class="mr-footer-left">www.MeraRashifal.com - ANNUAL HOROSCOPE REPORT 2026 &bull; <?php echo esc_html($section_name); ?></span>
            <span class="mr-footer-right">Page <?php echo esc_html($page_num); ?> of <?php echo esc_html($total_pages); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Standard Bottom 4-Pointer Takeaway Callout Box
     */
    public static function render_page_bottom_synthesis($bullets, $title = "Key 2026 Astrological Takeaways") {
        ob_start();
        ?>
        <div class="mr-bottom-callout">
            <div class="mr-bottom-callout-title"><?php echo esc_html($title); ?></div>
            <div class="mr-bottom-callout-grid">
                <?php foreach ($bullets as $b): ?>
                    <div class="mr-bottom-callout-item">
                        <span class="mr-callout-dot">&bull;</span>
                        <span><?php echo $b; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render North Indian Diamond SVG D1 Kundali Chart
     */
    public static function render_d1_diamond_svg($size = 250, $lagna_sign_num = 4) {
        $s = $size;
        $half = $s / 2;

        $planets_by_house = [
            1  => ['Ra 28&deg;'],
            2  => ['Ju 19&deg;', 'Mo 24&deg;'],
            3  => ['Sa 15&deg;'],
            5  => ['Ve 26&deg;'],
            6  => ['Su 28&deg;', 'Me 14&deg;'],
            7  => ['Ma 22&deg;(Ex)', 'Ke 28&deg;'],
        ];

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.5px solid #C2964C; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.2'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.2'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='#FAF7F2' fill-opacity='0.45' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='7' fill='#C2964C' fill-opacity='0.15'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='2.5' fill='#C45738'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.38, 'pl_x' => $half, 'pl_y' => $half * 0.65],
            2  => ['num_x' => $half * 0.46, 'num_y' => $half * 0.18, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.40],
            3  => ['num_x' => $half * 0.18, 'num_y' => $half * 0.46, 'pl_x' => $half * 0.22, 'pl_y' => $half * 0.70],
            4  => ['num_x' => $half * 0.38, 'num_y' => $half, 'pl_x' => $half * 0.55, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.18, 'num_y' => $half * 1.54, 'pl_x' => $half * 0.22, 'pl_y' => $half * 1.30],
            6  => ['num_x' => $half * 0.46, 'num_y' => $half * 1.82, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.62],
            7  => ['num_x' => $half, 'num_y' => $half * 1.62, 'pl_x' => $half, 'pl_y' => $half * 1.35],
            8  => ['num_x' => $half * 1.54, 'num_y' => $half * 1.82, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.62],
            9  => ['num_x' => $half * 1.82, 'num_y' => $half * 1.54, 'pl_x' => $half * 1.78, 'pl_y' => $half * 1.30],
            10 => ['num_x' => $half * 1.62, 'num_y' => $half, 'pl_x' => $half * 1.45, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.82, 'num_y' => $half * 0.46, 'pl_x' => $half * 1.78, 'pl_y' => $half * 0.70],
            12 => ['num_x' => $half * 1.54, 'num_y' => $half * 0.18, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.40],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $sign = (($lagna_sign_num - 1 + $h - 1) % 12) + 1;
            $pos = $house_coords[$h];
            $svg .= "<text x='{$pos['num_x']}' y='{$pos['num_y']}' fill='#8C7B6B' font-size='10' font-family='Inter, sans-serif' font-weight='700' text-anchor='middle' dominant-baseline='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $count = count($planets_by_house[$h]);
                $base_y = $pos['pl_y'] - (($count - 1) * 6.5);
                foreach ($planets_by_house[$h] as $idx => $p) {
                    $py = $base_y + ($idx * 13);
                    $color = '#C45738';
                    if (strpos($p, 'Ju') !== false || strpos($p, 'Mo') !== false) {
                        $color = '#047857';
                    } elseif (strpos($p, 'Ma') !== false) {
                        $color = '#B91C1C';
                    } elseif (strpos($p, 'Sa') !== false) {
                        $color = '#1E3A8A';
                    } elseif (strpos($p, 'Ve') !== false) {
                        $color = '#7C2D12';
                    }
                    $svg .= "<text x='{$pos['pl_x']}' y='{$py}' fill='{$color}' font-size='9' font-family='Inter, sans-serif' font-weight='800' text-anchor='middle' dominant-baseline='middle'>{$p}</text>";
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Build the Complete 5-Page Free Yearly Report HTML
     */
    public static function build_yearly_report_html(
        $person_name = 'Tushar Singhal',
        $gender      = 'male',
        $dob         = '26/12/1980',
        $tob         = '20:30',
        $pob         = 'Meerut, Uttar Pradesh, India',
        $forecast_year = 2026
    ) {
        $logo_uri = self::get_logo_data_uri();
        $head_font = "'Poppins', sans-serif";

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Horoscope &amp; Year Ahead Forecast <?php echo esc_html($forecast_year); ?> &bull; <?php echo esc_html($person_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #FAF7F2;
            --card-bg: #FFFFFF;
            --text-main: #111827;
            --text-body: #1F2937;
            --text-muted: #4B5563;
            --border: #ECE6DD;
            --border-subtle: #F2ECE3;
            --accent-agni: #C45738;
            --accent-sattva: #047857;
            --accent-gold: #C2964C;
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            width: 210mm;
            background: #FAF7F2;
            color: #111827;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 11.5px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

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
            padding: 9mm 14mm 7mm 14mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0 auto;
        }

        .mr-page::before {
            content: "";
            position: absolute;
            top: 6.5mm;
            left: 6.5mm;
            right: 6.5mm;
            bottom: 6.5mm;
            border: 1px solid #ECE6DD;
            pointer-events: none;
            z-index: 1;
        }
        .mr-page::after {
            content: "";
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 0.5px solid #E2DACF;
            pointer-events: none;
            z-index: 1;
        }

        .mr-page-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 0;
            z-index: 2;
            position: relative;
        }

        .mr-page-bottom-pin {
            flex-shrink: 0;
            margin-top: auto;
            width: 100%;
            z-index: 2;
            position: relative;
        }

        .mr-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ECE6DD;
            padding-bottom: 6px;
            margin-bottom: 10px;
            z-index: 2;
            position: relative;
            flex-shrink: 0;
        }
        .mr-header-brand-wrap {
            display: flex;
            align-items: center;
        }
        .mr-header-logo-img {
            height: 48px;
            max-width: 190px;
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

        .mr-tag-pill {
            display: inline-block;
            font-size: 9.2px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 3px 12px;
            border-radius: 4px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            color: #C45738;
            margin-bottom: 5px;
            align-self: flex-start;
        }

        .mr-page-h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 3px 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 11.5px;
            color: #4B5563;
            margin-bottom: 12px;
            line-height: 1.45;
        }

        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 7px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            position: relative;
            z-index: 2;
        }
        .mr-card-accent {
            background: #FDF8F3;
            border: 1.5px solid #C2964C;
            border-radius: 8px;
        }
        .mr-card p {
            margin: 0 0 6px 0;
            font-size: 12px;
            color: #1F2937;
            line-height: 1.55;
        }
        .mr-card p:last-child {
            margin-bottom: 0;
        }

        .mr-badge {
            display: inline-block;
            font-size: 9.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .mr-badge-emerald { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .mr-badge-blue { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
        .mr-badge-gold { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
        .mr-badge-amber { background: #FFFBEB; color: #D97706; border: 1px solid #FCD34D; }
        .mr-badge-red { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }

        .mr-bottom-callout {
            background: #FFFDF9;
            border: 1px solid #EADDC9;
            border-left: 3.5px solid #C45738;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 4px;
        }
        .mr-bottom-callout-title {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #111827;
            margin-bottom: 3px;
        }
        .mr-bottom-callout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px 14px;
        }
        .mr-bottom-callout-item {
            font-size: 11px;
            line-height: 1.45;
            color: #374151;
            display: flex;
            align-items: flex-start;
        }
        .mr-callout-dot {
            color: #C45738;
            font-weight: 800;
            margin-right: 5px;
            font-size: 12px;
            line-height: 1;
        }

        .mr-page-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 0.5px solid #E5DECE;
            padding-top: 4px;
            margin-top: 2px;
            font-size: 9px;
            color: #78716C;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .mr-footer-right {
            font-weight: 800;
            color: #C45738;
            font-size: 9.5px;
        }
    </style>
</head>
<body>

    <!-- ========================================================
         PAGE 01: SOVEREIGN COVER & ANNUAL EXECUTIVE SNAPSHOT
         ======================================================== -->
    <div class="mr-page" style="justify-content:space-between; text-align:center; padding:18mm 20mm 12mm 20mm;">
        <!-- Top Master Brand Logo & Title -->
        <div style="z-index:2; position:relative;">
            <div style="display:flex; justify-content:center; margin-bottom:14px;">
                <img src="<?php echo esc_attr($logo_uri); ?>" style="height:120px; max-width:380px; object-fit:contain; display:block;" alt="MeraRashifal Logo" />
            </div>

            <div class="mr-tag-pill" style="margin:0 auto 10px auto;">AUTHENTIC PARASHARI JYOTISH &bull; <?php echo esc_html($forecast_year); ?> ANNUAL FORECAST</div>
            
            <h1 style="font-family:<?php echo $head_font; ?>; font-size:25px; font-weight:800; color:#111827; line-height:1.2; margin:0 0 6px 0; letter-spacing:-0.02em;">
                ANNUAL HOROSCOPE &amp; YEAR AHEAD FORECAST <?php echo esc_html($forecast_year); ?>
            </h1>
            <div style="font-size:12.5px; font-weight:600; color:#C2964C; font-style:italic; margin-bottom:16px; letter-spacing:0.02em;">
                “Personalized Astrological Dynamics, Key Planetary Transits &amp; Auspicious Action Windows”
            </div>

            <!-- Profile & Coordinates Card -->
            <div class="mr-card" style="max-width:620px; margin:0 auto 16px auto; padding:16px 20px; text-align:left; background:#FFFFFF; border:1.5px solid #C2964C;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ECE6DD; padding-bottom:8px; margin-bottom:12px;">
                    <div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:20px; font-weight:800; color:#111827;"><?php echo esc_html($person_name); ?></div>
                    </div>
                    <span class="mr-badge mr-badge-gold" style="font-size:11.5px; padding:5px 12px;">Cancer Lagna (Moon-Ruled) &bull; Magha Nakshatra</span>
                </div>

                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; text-align:center; margin-bottom:14px;">
                    <div style="background:#FAF7F2; padding:8px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:10.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Date of Birth</div>
                        <div style="font-size:13px; font-weight:800; color:#111827; margin-top:2px;"><?php echo esc_html($dob); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:10.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Birth Details</div>
                        <div style="font-size:12px; font-weight:700; color:#111827; margin-top:2px;"><?php echo esc_html($tob); ?> &bull; <?php echo esc_html(explode(',', $pob)[0]); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:10.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Vedic Lagna</div>
                        <div style="font-size:13px; font-weight:800; color:#047857; margin-top:2px;">Cancer (Moon)</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:10.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Moon Rashi</div>
                        <div style="font-size:13px; font-weight:800; color:#C45738; margin-top:2px;">Leo (Sun)</div>
                    </div>
                </div>

                <!-- 4 Key 2026 Core Annual Indicators -->
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="background:#F0FDF4; padding:10px 6px; border-radius:6px; border:1px solid #BBF7D0;">
                        <div style="font-size:10px; color:#047857; font-weight:800; text-transform:uppercase;">Year Ruling Archetype</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#047857; line-height:1.2; margin-top:4px;">Mars / Ketu</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">Strategic Action</div>
                    </div>
                    <div style="background:#EFF6FF; padding:10px 6px; border-radius:6px; border:1px solid #BFDBFE;">
                        <div style="font-size:10px; color:#1E40AF; font-weight:800; text-transform:uppercase;">Active Mahadasha</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#1E40AF; line-height:1.2; margin-top:4px;">Rahu Cycle</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">Expansion Drive</div>
                    </div>
                    <div style="background:#FFFBEB; padding:10px 6px; border-radius:6px; border:1px solid #FDE68A;">
                        <div style="font-size:10px; color:#92400E; font-weight:800; text-transform:uppercase;">Jupiter Transit (11th)</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#92400E; line-height:1.2; margin-top:4px;">Dhana Labha</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">Network Expansion</div>
                    </div>
                    <div style="background:#FEF2F2; padding:10px 6px; border-radius:6px; border:1px solid #FECACA;">
                        <div style="font-size:10px; color:#991B1B; font-weight:800; text-transform:uppercase;">Opportunity Index</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:18px; font-weight:800; color:#991B1B; line-height:1.1; margin-top:2px;">88%</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">High Momentum</div>
                    </div>
                </div>
            </div>

            <!-- Executive Annual Theme Box -->
            <div style="max-width:620px; margin:0 auto; padding:12px 18px; background:#FAF7F2; border-left:3.5px solid #C45738; border-radius:0 6px 6px 0; text-align:left;">
                <div style="font-size:11px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    EXECUTIVE ANNUAL SYNTHESIS &bull; YEAR AHEAD OVERVIEW
                </div>
                <div style="font-size:12.2px; color:#1F2937; line-height:1.55;">
                    The year <strong><?php echo esc_html($forecast_year); ?></strong> marks a pivotal inflection point for the native. Benefited by <strong>Jupiter’s high-velocity transit</strong> across the houses of gain and overseas recognition, professional initiatives initiated over the past 3 years begin to yield compounding returns. Combined with an active <strong>Rahu Mahadasha</strong> and an exalted Mars in the 7th house, this annual cycle rewards decisive leadership, institutional alliances, and long-term asset building while advising measured caution against impulsive speculative ventures.
                </div>
            </div>
        </div>

        <!-- Pinned Bottom Section: Statutory Disclaimer & Footer -->
        <div class="mr-page-bottom-pin">
            <!-- Statutory Disclaimer & Non-Liability Notice Pinned at Bottom -->
            <div style="max-width:620px; margin:0 auto 10px auto; padding:8px 14px; background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; font-size:8.4px; color:#64748B; line-height:1.45; text-align:justify;">
                <strong style="color:#111827;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal Technologies nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
            </div>

            <div class="mr-page-footer" style="margin-top:0;">
                <span class="mr-footer-left">www.MeraRashifal.com - ANNUAL HOROSCOPE REPORT <?php echo esc_html($forecast_year); ?></span>
                <span class="mr-footer-right">Page 1 of 5</span>
            </div>
        </div>
    </div>

    <!-- ========================================================
         PAGE 02: NATAL CELESTIAL BLUEPRINT & 2026 PLANETARY TRANSITS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Natal Anchor &amp; <?php echo esc_html($forecast_year); ?> Transits</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">01 &bull; CELESTIAL FOUNDATION &amp; GOCHAR TRAJECTORY</div>
            <h1 class="mr-page-h1">Natal Kundali &amp; Major <?php echo esc_html($forecast_year); ?> Planetary Transits</h1>
            <div class="mr-page-sub">Synthesizing Your Natal Astrological Substrate with 2026 Cosmic Movements (Gochar)</div>

            <!-- Upper Grid: D1 Diamond Chart + Natal Coordinate Summary -->
            <div style="display:grid; grid-template-columns:255px 1fr; gap:18px; align-items:center; margin-bottom:14px;">
                <div>
                    <?php echo self::render_d1_diamond_svg(250, 4); ?>
                    <div style="text-align:center; font-size:10px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        Janma Kundali (D1) &bull; Cancer Lagna 21.27&deg;
                    </div>
                </div>

                <div class="mr-card" style="padding:14px 18px; background:#FFFFFF;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:8px; border-bottom:1px solid #F3EFEA; padding-bottom:4px;">
                        &diams; Natal Planetary Substrate Overview
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:8px;">
                        Your foundational chart is anchored by <strong>Cancer Lagna (Karka)</strong> with Rahu stationed on the ascendant axis, endowing you with bold visionary thinking, rapid adaptation, and global perspectives. 
                    </p>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:8px;">
                        <strong>Exalted Mars (Ruchaka Yoga)</strong> in the 7th house Capricorn anchors supreme executive endurance and contractual resilience. The conjunction of <strong>Jupiter &amp; Moon in the 2nd house (Leo)</strong> forms an auspicious Dhana Yoga, ensuring enduring financial dignity and oratorical impact.
                    </p>
                    <div style="background:#FDF8F3; border:1px solid #ECE6DD; border-radius:6px; padding:6px 12px; font-size:11px; color:#C45738; font-weight:700;">
                        Natal Pillars: Exalted Mars (7th) &bull; Gajakesari Influence (2nd) &bull; Upachaya Saturn (3rd)
                    </div>
                </div>
            </div>

            <!-- Lower Section: 3 Major 2026 Planetary Transits (Gochar) -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:6px;">
                <!-- Transit 1: Jupiter -->
                <div class="mr-card" style="padding:12px 14px; border-top:3px solid #047857;">
                    <div style="font-size:9.5px; font-weight:800; color:#047857; text-transform:uppercase; letter-spacing:0.06em;">MAJOR TRANSIT I</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; margin:2px 0 6px 0;">Jupiter (Guru Gochar)</div>
                    <p style="font-size:11.2px; line-height:1.5; color:#4B5563; margin:0;">
                        Transiting the <strong>11th house of gains (Taurus)</strong> and shifting into Gemini, Jupiter triggers massive network expansion, institutional patronage, and realization of long-held financial goals. Auspicious for senior mentorship and multi-stream windfalls.
                    </p>
                </div>

                <!-- Transit 2: Saturn -->
                <div class="mr-card" style="padding:12px 14px; border-top:3px solid #1E3A8A;">
                    <div style="font-size:9.5px; font-weight:800; color:#1E3A8A; text-transform:uppercase; letter-spacing:0.06em;">MAJOR TRANSIT II</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; margin:2px 0 6px 0;">Saturn (Shani Gochar)</div>
                    <p style="font-size:11.2px; line-height:1.5; color:#4B5563; margin:0;">
                        Moving through the <strong>9th house of dharma &amp; fortune (Pisces)</strong>, Saturn demands systematic discipline in higher enterprises, legal filings, and paternal commitments. No active Sade Sati; rewards structured, ethical persistence.
                    </p>
                </div>

                <!-- Transit 3: Rahu-Ketu -->
                <div class="mr-card" style="padding:12px 14px; border-top:3px solid #C45738;">
                    <div style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em;">MAJOR TRANSIT III</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; margin:2px 0 6px 0;">Rahu-Ketu Nodal Axis</div>
                    <p style="font-size:11.2px; line-height:1.5; color:#4B5563; margin:0;">
                        Rahu in the <strong>8th house (Aquarius)</strong> activates research breakthroughs, technological modernization, and unearned asset gains, while Ketu in the <strong>2nd house (Leo)</strong> inspires prudent verbal diplomacy and balanced family budgeting.
                    </p>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Jupiterian Expansion: 11th-house Jupiter transit unlocks high-yield professional alliances and multi-stream revenue.",
                "Saturnian Karmic Anchor: 9th-house transit stabilizes long-term fortune through rigorous discipline and legal clarity.",
                "Rahu Breakthrough Window: 8th-house Rahu triggers innovative research solutions and transformative digital pivots.",
                "Zero Doshic Interference: Absence of acute Sade Sati grants smooth execution bandwidth across major 2026 quarters."
            ], "2026 Transit & Celestial Anchor Takeaways"); ?>
            <?php echo self::render_page_footer(2, 'Natal Anchor & 2026 Transits'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 03: FOUR CORE LIFE PILLARS FORECAST
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; 4 Core Life Pillars <?php echo esc_html($forecast_year); ?></span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">02 &bull; COMPREHENSIVE SECTORAL OUTLOOK</div>
            <h1 class="mr-page-h1">The Four Core Life Pillars: <?php echo esc_html($forecast_year); ?> Strategic Forecast</h1>
            <div class="mr-page-sub">High-Level Strategic Guidance Across Career, Wealth, Relationships &amp; Energy Management</div>

            <!-- 2x2 Grid of the Four Life Pillars -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px 20px; margin-bottom:8px;">
                <!-- Pillar 1: Career & Enterprise -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">1. Career &amp; Professional Altitude</div>
                        <span class="mr-badge mr-badge-gold">Strategic Expansion</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        With your natal <strong>exalted Mars in the 7th</strong> house amplified by Jupiter's aspect on key vocational houses, 2026 provides optimal traction for enterprise expansion, executive leadership appointments, and high-value client acquisitions.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Mid-year (Q2–Q3) brings notable recognition from governing bodies or institutional partners. Avoid delegating critical financial audits to unverified third parties during eclipse cycles.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#C45738;">Priority Action:</strong> Formalize contractual agreements; scale leadership team; build scalable workflows.
                    </div>
                </div>

                <!-- Pillar 2: Wealth & Finances -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">2. Wealth, Assets &amp; Treasury Flow</div>
                        <span class="mr-badge mr-badge-emerald">Compounding Gains</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your 2nd house <strong>Gajakesari resonance</strong> receives fertile planetary support. Income streams diversify, and past fixed-asset investments show sharp appreciation. 
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Because Rahu occupies the 8th house, steer strictly clear of unvetted speculative trading or crypto speculation. Prioritize tangible commercial real estate, sovereign debt, and structured treasury reserves.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#047857;">Priority Action:</strong> Allocate 35% of surplus capital to tangible real assets; establish emergency liquidity reserves.
                    </div>
                </div>

                <!-- Pillar 3: Love & Relationships -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #1E3A8A;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">3. Love, Marriage &amp; Family Harmony</div>
                        <span class="mr-badge mr-badge-blue">Emotional Maturation</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        The presence of exalted Mars in the 7th creates high domestic drive but can occasionally trigger assertive verbal friction. In 2026, cultivating empathetic, active listening will turn potential friction into deep partnership intimacy.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Family commitments require structured time management. Joint spiritual practices, shared retreats, and mutual financial alignment bring immense warmth and harmony in late Q3 and Q4.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#1E3A8A;">Priority Action:</strong> Schedule regular sacred couple time; replace workplace assertiveness with domestic compassion.
                    </div>
                </div>

                <!-- Pillar 4: Health & Vitality -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">4. Health, Vitality &amp; Energy Balance</div>
                        <span class="mr-badge mr-badge-amber">High Stamina</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your underlying constitution is fortified by an energetic Mars. However, 8th-house Rahu mandates conscious nervous system grounding to prevent mental fatigue and adrenal burnout during intense work sprints.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Watch for digestive fire (Pitta) elevations during the summer months. Consistent morning hydration, breathwork (Pranayama), and restorative sleep protocols ensure uninterrupted vitality.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#D97706;">Priority Action:</strong> Adopt 15-minute daily morning solar meditation; minimize late-night screen exposure.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Vocational Milestone: Q2 and Q3 deliver peak corporate traction and institutional alliance opportunities.",
                "Wealth Preservation: Avoid high-risk speculative trading; channel surplus gains into prime real property.",
                "Domestic Alignment: Transform marital passion into supportive teamwork through conscious, patient dialogue.",
                "Vitality Conservation: Balance Martian stamina with structured nervous system downtime and mindful nutrition."
            ], "Four Pillars 2026 Strategic Takeaways"); ?>
            <?php echo self::render_page_footer(3, '4 Core Life Pillars'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 04: 2026 QUARTERLY PROGRESSION & STRATEGIC WINDOWS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; <?php echo esc_html($forecast_year); ?> Quarterly Roadmap</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">03 &bull; TEMPORAL TIMING &amp; HIGH-VELOCITY WINDOWS</div>
            <h1 class="mr-page-h1"><?php echo esc_html($forecast_year); ?> Quarterly Progression &amp; Strategic Windows</h1>
            <div class="mr-page-sub">Chronological Roadmap: Tracking High-Velocity Execution Windows Against Reflective Consolidation Cycles</div>

            <!-- 4-Quarter Timeline Progression -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px 18px; margin-bottom:12px;">
                <!-- Q1 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #1E3A8A;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q1 (Jan – Mar): Foundation &amp; Audit</div>
                        <span class="mr-badge mr-badge-blue">Strategic Planning</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Sun and Mercury transit practical houses. Focus on closing legacy liabilities, tightening team deliverables, and setting annual strategic milestones. Avoid rushed contractual commitments before February 15.
                    </p>
                    <div style="font-size:10px; color:#4B5563; font-weight:700;">Key Focus: Operational efficiency, legal audits, debt retirement.</div>
                </div>

                <!-- Q2 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q2 (Apr – Jun): High-Velocity Execution</div>
                        <span class="mr-badge mr-badge-emerald">Peak Golden Window</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Exalted Sun and peak Jupiterian transit in the 11th house create your strongest expansion portal of 2026. Launch new enterprises, negotiate institutional equity, and capitalize on high-profile public appearances.
                    </p>
                    <div style="font-size:10px; color:#047857; font-weight:700;">Key Focus: Commercial launches, key client acquisitions, asset acquisition.</div>
                </div>

                <!-- Q3 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q3 (Jul – Sep): Consolidation &amp; Travel</div>
                        <span class="mr-badge mr-badge-amber">Internal Calibration</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Mid-year brings planetary retrogrades and monsoon introspection. Consolidate revenues gathered in Q2. Favorable for international long-distance travel, spiritual retreats, and re-calibrating domestic life.
                    </p>
                    <div style="font-size:10px; color:#92400E; font-weight:700;">Key Focus: Reviewing operations, delegating workload, family vacations.</div>
                </div>

                <!-- Q4 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q4 (Oct – Dec): Harvest &amp; Celebration</div>
                        <span class="mr-badge mr-badge-gold">Harvest &amp; Rewards</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Benefic Venus and Mars align in supportive angles. Real estate matters settle favorably, and commercial profits reach projected targets. A celebratory quarter marked by social recognition and festive domestic joy.
                    </p>
                    <div style="font-size:10px; color:#C45738; font-weight:700;">Key Focus: Realizing gains, end-of-year tax planning, future 2027 vision.</div>
                </div>
            </div>

            <!-- Strategic Windows: Golden Opportunity vs Cautionary Windows -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:6px;">
                <!-- Golden Windows -->
                <div class="mr-card" style="padding:12px 16px; background:#F0FDF4; border:1.5px solid #86EFAC;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-size:16px;">🌟</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#166534; text-transform:uppercase;">
                            Golden Opportunity Windows
                        </div>
                    </div>
                    <div style="font-size:11.2px; color:#14532D; line-height:1.5;">
                        <div style="margin-bottom:4px;"><strong>&bull; April 18 – May 26:</strong> Peak transit conjunction; ideal for property registration, high-stakes signings, and major launches.</div>
                        <div><strong>&bull; October 10 – November 18:</strong> Strong financial inflows, public recognition, and celebratory relationship commitments.</div>
                    </div>
                </div>

                <!-- Cautionary Windows -->
                <div class="mr-card" style="padding:12px 16px; background:#FFFBEB; border:1.5px solid #FCD34D;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-size:16px;">⚠️</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#92400E; text-transform:uppercase;">
                            Mindful &amp; Cautionary Windows
                        </div>
                    </div>
                    <div style="font-size:11.2px; color:#78350F; line-height:1.5;">
                        <div style="margin-bottom:4px;"><strong>&bull; March 14 – April 04:</strong> Mercury retrograde window; double-check contracts, travel schedules, and legal clauses.</div>
                        <div><strong>&bull; August 12 – August 30:</strong> Emotional sensitivity during lunar transit shifts; avoid hasty arguments or impulsive financial loans.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Q2 Peak Trajectory: April–June presents the highest cosmic velocity for commercial expansions and career pivots.",
                "Q3 Restorative Recalibration: Utilize July–September for operational consolidation and restorative family travel.",
                "Golden Action Gates: Schedule critical life investments during the verified April-May and October-November windows.",
                "Mindful Contract Scrutiny: Apply rigorous due diligence during Mercury retrograde cycles in March and August."
            ], "2026 Quarterly Progression & Timing Takeaways"); ?>
            <?php echo self::render_page_footer(4, '2026 Quarterly Roadmap'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 05: HARMONIZING UPAYAS, MASTER UPSELL & BENEDICTION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Harmonizing Upayas &amp; Master Upgrade</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">04 &bull; SACRED HARMONIZERS &amp; LIFETIME BLUEPRINTS</div>
            <h1 class="mr-page-h1">Daily Upayas &amp; Unlocking Your Master Lifetime Dossier</h1>
            <div class="mr-page-sub">Accessible Spiritual Remedies for <?php echo esc_html($forecast_year); ?> &bull; Gateway to Exhaustive Multi-Varga Natal Predictions</div>

            <!-- Top Section: 3 Practical Everyday Upayas -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #047857;">
                    <div style="font-size:9.5px; font-weight:800; color:#047857; text-transform:uppercase;">EVERYDAY PRACTICE I</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Power Days &amp; Colors</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        <strong>Monday (Moon)</strong> and <strong>Tuesday (Mars)</strong> are your peak victory days. Wear auspicious Pearl White, Moon Cream, and Bright Coral Red. Minimize dark slate grey during crucial negotiations.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #C45738;">
                    <div style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase;">EVERYDAY PRACTICE II</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Sacred Seed Mantras</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        Recite <em>Om Som Somaya Namah</em> (11 times) at sunrise for mental tranquility, and <em>Om Hanumate Namah</em> on Tuesdays to channel Martian energy into decisive, successful leadership.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #1E3A8A;">
                    <div style="font-size:9.5px; font-weight:800; color:#1E3A8A; text-transform:uppercase;">EVERYDAY PRACTICE III</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Karmic Daan (Charity)</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        Offer clean water or milk to sacred roots or birds on Mondays. Supporting underprivileged students or healthcare initiatives on Saturdays dissolves karmic blockages and accelerates professional ease.
                    </p>
                </div>
            </div>

            <!-- Middle Section: High-Converting Master Report Upgrade Showcase -->
            <div class="mr-card-accent" style="padding:14px 18px; margin-bottom:12px; background:#FFFDF9;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <div>
                        <span style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em;">UNLOCK YOUR LIFETIME CELESTIAL BLUEPRINT</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#111827;">Ready for the Complete Picture? Access Your Master Astrological Dossiers</div>
                    </div>
                    <span class="mr-badge mr-badge-gold" style="font-size:11px; padding:4px 10px;">Premium Kundali Intelligence</span>
                </div>
                <p style="font-size:11.8px; color:#4B5563; line-height:1.5; margin-bottom:10px;">
                    This free annual forecast provides a concise overview of 2026. For comprehensive life planning, deep karmic diagnostics, and micro-timed event tracking, discover our complete library of certified master publications:
                </p>

                <!-- 3 Master Report Cards -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#C45738; font-size:11.5px; display:block; margin-bottom:2px;">1. Master Kundali (26 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Complete 16-Varga divisional harmonics, Shadbala metrics, Ashtakvarga power matrices, and exhaustive lifetime house analysis.
                        </span>
                    </div>

                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#047857; font-size:11.5px; display:block; margin-bottom:2px;">2. Master Dasha Timing (20 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Full 120-year Vimshottari &amp; Yogini chronology, Pratyantar precision micro-windows, and strategic high-stakes decision calendars.
                        </span>
                    </div>

                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#1E3A8A; font-size:11.5px; display:block; margin-bottom:2px;">3. Master Numerology (22 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Pythagorean &amp; Vedic Sankhya integration, full 3x3 Lo Shu energy grid, name spelling upgrades, and 90-day action roadmaps.
                        </span>
                    </div>
                </div>

                <div style="background:#FAF7F2; border:1px dashed #C2964C; border-radius:6px; padding:8px 14px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11px; color:#111827; font-weight:700;">
                        Exclusive Reader Upgrade Benefit: Unlock your instant full report archive anytime from your dashboard.
                    </span>
                    <span style="font-size:11px; font-weight:800; color:#C45738;">www.MeraRashifal.com &rarr;</span>
                </div>
            </div>

            <!-- Customer Portal & Concluding Benediction Box -->
            <div class="mr-card" style="padding:10px 16px; background:#FFFFFF; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight:800; font-size:11px; color:#111827; text-transform:uppercase; letter-spacing:0.04em;">Official Customer Verification &amp; Consultation Portal</div>
                    <div style="font-size:10.5px; color:#6B7280; margin-top:1px;">Access authenticated forecasts, transit updates, and expert astrological guidance.</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:<?php echo $head_font; ?>; font-weight:800; color:#C45738; font-size:12px;">www.MeraRashifal.com</div>
                    <div style="font-size:9.5px; color:#9CA3AF;">Document ID: MR-YR-<?php echo strtoupper(substr(md5($person_name . $dob . 'YEARLY2026'), 0, 8)); ?></div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Accessible Daily Alignment: Simple Monday and Tuesday rituals preserve peace and multiply professional stamina.",
                "Mantra & Daan Efficacy: Consistent gratitude and compassionate charity harmonize Rahu's 8th-house transition.",
                "Strategic Upgrade Pathway: Master 26-page Kundali and 20-page Dasha reports unlock granular decades-long visibility.",
                "Certified Astrological Rigor: Backed by authenticated Parashari algorithms calculated at exact geographical coordinates."
            ], "Harmonizing Upayas & Concluding Benediction"); ?>
            <?php echo self::render_page_footer(5, 'Remedial Upayas & Master Upgrade'); ?>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
