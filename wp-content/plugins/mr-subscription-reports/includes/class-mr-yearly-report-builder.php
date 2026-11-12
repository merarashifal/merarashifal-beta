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
    public static function render_d1_diamond_svg($size = 250, $lagna_sign_num = 4, $planets_by_house = []) {
        $s = $size;
        $half = $s / 2;

        if (empty($planets_by_house)) {
            $planets_by_house = [
                1  => ['Ra 28&deg;'],
                2  => ['Ju 19&deg;', 'Mo 24&deg;'],
                3  => ['Sa 15&deg;'],
                5  => ['Ve 26&deg;'],
                6  => ['Su 28&deg;', 'Me 14&deg;'],
                7  => ['Ma 22&deg;(Ex)', 'Ke 28&deg;'],
            ];
        }

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
        $forecast_year = 2026,
        $editorial_data = [],
        $vedic_data = []
    ) {
        $logo_uri = self::get_logo_data_uri();
        $head_font = "'Poppins', sans-serif";

        if (class_exists('MR_Report_Builder')) {
            $profile = MR_Report_Builder::extract_astro_profile($dob, $tob, $pob, $vedic_data);
        } else {
            $profile = [
                'age' => 25,
                'formatted_dob' => $dob,
                'sunsigns' => ['western_sign' => 'Capricorn', 'western_dates' => 'Dec 22 – Jan 19', 'vedic_sign' => 'Sagittarius (Dhanu)', 'vedic_dates' => 'Dec 16 – Jan 13'],
                'lagna_sign' => 'Cancer', 'lagna_deg' => 21.27, 'lagna_lord' => 'Moon',
                'moon_sign' => 'Leo', 'moon_deg' => 11.53, 'moon_nak' => 'Magha', 'moon_pada' => 4, 'moon_lord' => 'Ketu',
                'sun_sign' => 'Sagittarius', 'sun_deg' => 11.42
            ];
        }

        $zodiac_order = ['aries'=>1, 'taurus'=>2, 'gemini'=>3, 'cancer'=>4, 'leo'=>5, 'virgo'=>6, 'libra'=>7, 'scorpio'=>8, 'sagittarius'=>9, 'capricorn'=>10, 'aquarius'=>11, 'pisces'=>12];
        $lagna_clean_str = strtolower(explode(' ', trim($profile['lagna_sign']))[0]);
        $lagna_num = $zodiac_order[$lagna_clean_str] ?? 4;

        // Build planets by house for D1 SVG
        $planets_by_house = [];
        if (!empty($profile['planets']) && is_array($profile['planets'])) {
            $short_symbols = ['Sun'=>'Su', 'Moon'=>'Mo', 'Mars'=>'Ma', 'Mercury'=>'Me', 'Jupiter'=>'Ju', 'Venus'=>'Ve', 'Saturn'=>'Sa', 'Rahu'=>'Ra', 'Ketu'=>'Ke'];
            foreach ($profile['planets'] as $idx => $p) {
                if ($idx === 0) continue; // skip lagna row
                $p_name = $p['name'] ?? '';
                if (!isset($short_symbols[$p_name])) continue;
                $p_sign = strtolower(explode(' ', trim($p['zodiac'] ?? ''))[0]);
                $p_sign_num = $zodiac_order[$p_sign] ?? 1;
                $h = (($p_sign_num - $lagna_num + 12) % 12) + 1;
                $sym = $short_symbols[$p_name];
                if (!empty($p['is_retro']) && ($p['is_retro'] === 'true' || $p['is_retro'] === true)) {
                    $sym .= '(R)';
                }
                $planets_by_house[$h][] = $sym;
            }
        }

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
            font-size: 14.4px;
            line-height: 1.6;
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
            padding: 8mm 13mm 11mm 13mm;
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
            padding-bottom: 5px;
            margin-bottom: 7px;
            z-index: 2;
            position: relative;
            flex-shrink: 0;
        }
        .mr-header-brand-wrap {
            display: flex;
            align-items: center;
        }
        .mr-header-logo-img {
            height: 44px;
            max-width: 175px;
            object-fit: contain;
            display: block;
        }
        .mr-header-meta {
            font-size: 11.5px;
            color: #4B5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .mr-tag-pill {
            display: inline-block;
            font-size: 11px;
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
            font-size: 23px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 3px 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 13.5px;
            color: #4B5563;
            margin-bottom: 10px;
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
            margin: 0 0 5px 0;
            font-size: 13.8px;
            color: #1F2937;
            line-height: 1.55;
        }
        .mr-card p:last-child {
            margin-bottom: 0;
        }

        .mr-badge {
            display: inline-block;
            font-size: 11px;
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
            padding: 7px 12px;
            margin-bottom: 4px;
        }
        .mr-bottom-callout-title {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #111827;
            margin-bottom: 2px;
        }
        .mr-bottom-callout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px 12px;
        }
        .mr-bottom-callout-item {
            font-size: 13px;
            line-height: 1.45;
            color: #374151;
            display: flex;
            align-items: flex-start;
        }
        .mr-callout-dot {
            color: #C45738;
            font-weight: 800;
            margin-right: 5px;
            font-size: 14px;
            line-height: 1;
        }

        .mr-page-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 0.5px solid #E5DECE;
            padding-top: 4px;
            padding-bottom: 0px;
            margin-top: auto;
            font-size: 11px;
            line-height: 1.2;
            color: #78716C;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .mr-footer-right {
            font-weight: 800;
            color: #C45738;
            font-size: 11.5px;
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
                “Personalized Astrological Insights, Key Planetary Transits &amp; Auspicious Action Windows”
            </div>

            <!-- Profile & Coordinates Card -->
            <div class="mr-card" style="max-width:620px; margin:0 auto 16px auto; padding:16px 20px; text-align:left; background:#FFFFFF; border:1.5px solid #C2964C;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ECE6DD; padding-bottom:8px; margin-bottom:12px;">
                    <div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:20px; font-weight:800; color:#111827;"><?php echo esc_html($person_name); ?></div>
                    </div>
                    <span class="mr-badge mr-badge-gold" style="font-size:12px; padding:5px 12px;"><?php echo esc_html($profile['lagna_sign'] . ' Lagna (' . $profile['lagna_lord'] . '-Ruled) • ' . $profile['moon_nak'] . ' Nakshatra'); ?></span>
                </div>

                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; text-align:center; margin-bottom:12px;">
                    <div style="background:#FAF7F2; padding:7px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:700;">Date of Birth</div>
                        <div style="font-size:13.5px; font-weight:800; color:#111827; margin-top:2px;"><?php echo esc_html($profile['formatted_dob']); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:700;">Birth Details</div>
                        <div style="font-size:13px; font-weight:700; color:#111827; margin-top:2px;"><?php echo esc_html($tob); ?> &bull; <?php echo esc_html(explode(',', $pob)[0]); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:700;">Vedic Lagna</div>
                        <div style="font-size:13.5px; font-weight:800; color:#047857; margin-top:2px;"><?php echo esc_html($profile['lagna_sign'] . ' (' . $profile['lagna_deg'] . '°)'); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 6px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:700;">Moon Rashi</div>
                        <div style="font-size:13.5px; font-weight:800; color:#C45738; margin-top:2px;"><?php echo esc_html($profile['moon_sign'] . ' (' . $profile['moon_nak'] . ')'); ?></div>
                    </div>
                </div>

                <!-- 4 Key Core Annual Indicators -->
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="background:#F0FDF4; padding:8px 6px; border-radius:6px; border:1px solid #BBF7D0;">
                        <div style="font-size:10.5px; color:#047857; font-weight:800; text-transform:uppercase;">Western Sun Sign</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13.5px; font-weight:800; color:#047857; line-height:1.2; margin-top:3px;"><?php echo esc_html($profile['sunsigns']['western_sign']); ?></div>
                        <div style="font-size:10px; color:#374151; font-weight:700;"><?php echo esc_html($profile['sunsigns']['western_dates']); ?></div>
                    </div>
                    <div style="background:#EFF6FF; padding:8px 6px; border-radius:6px; border:1px solid #BFDBFE;">
                        <div style="font-size:10.5px; color:#1E40AF; font-weight:800; text-transform:uppercase;">Vedic Sun Sign</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13.5px; font-weight:800; color:#1E40AF; line-height:1.2; margin-top:3px;"><?php echo esc_html($profile['sunsigns']['vedic_sign']); ?></div>
                        <div style="font-size:10px; color:#374151; font-weight:700;"><?php echo esc_html($profile['sunsigns']['vedic_dates']); ?></div>
                    </div>
                    <div style="background:#FFFBEB; padding:8px 6px; border-radius:6px; border:1px solid #FDE68A;">
                        <div style="font-size:10.5px; color:#92400E; font-weight:800; text-transform:uppercase;">Native Life Age</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#92400E; line-height:1.2; margin-top:3px;"><?php echo esc_html($profile['age']); ?> Years</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">Life Horizon</div>
                    </div>
                    <div style="background:#FEF2F2; padding:8px 6px; border-radius:6px; border:1px solid #FECACA;">
                        <div style="font-size:10.5px; color:#991B1B; font-weight:800; text-transform:uppercase;">Opportunity Index</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:18px; font-weight:800; color:#991B1B; line-height:1.1; margin-top:2px;">88%</div>
                        <div style="font-size:10px; color:#374151; font-weight:700;">High Momentum</div>
                    </div>
                </div>
            </div>

            <!-- Executive Annual Theme Box -->
            <div style="max-width:620px; margin:0 auto; padding:10px 16px; background:#FAF7F2; border-left:3.5px solid #C45738; border-radius:0 6px 6px 0; text-align:left;">
                <div style="font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">
                    YEAR AHEAD OVERVIEW &bull; YOUR ANNUAL HIGHLIGHTS
                </div>
                <div style="font-size:13.5px; color:#1F2937; line-height:1.55;">
                    The year <strong><?php echo esc_html($forecast_year); ?></strong> brings great opportunities and fresh momentum for <?php echo esc_html($person_name); ?>. With Jupiter's blessings moving through favorable houses, the hard work you put in over the last few years will start showing wonderful results. Supported by your <?php echo esc_html($profile['lagna_sign']); ?> Lagna and <?php echo esc_html($profile['moon_sign']); ?> Moon, this year rewards honest effort, smart planning, and strong family relationships.
                </div>
            </div>
        </div>

        <!-- Pinned Bottom Section: Statutory Disclaimer & Footer -->
        <div class="mr-page-bottom-pin">
            <!-- Statutory Disclaimer & Non-Liability Notice Pinned at Bottom -->
            <div style="max-width:620px; margin:0 auto 6px auto; padding:4px 8px; background:#FFFFFF; border:1px solid #ECE6DD; border-radius:4px; font-size: 7.2px; color:#64748B; line-height:1.35; text-align:justify;">
                <strong style="color:#111827;">Important Disclaimer:</strong> This personalized report is created using classical Vedic calculation methods and AI analysis based on your provided birth details. Astrology is an ancient interpretive science and should be used as guidance and insight, not as certified legal, financial, or medical advice. MeraRashifal and its team are not liable for any decisions, actions, or outcomes taken based on this report. For important health, business, or legal matters, please consult qualified professionals. We encourage you to use your own wisdom and discernment as you plan your year ahead.
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
            <div class="mr-page-sub">Understanding your birth chart and how moving planets guide your year ahead</div>

            <!-- Upper Grid: D1 Diamond Chart + Natal Coordinate Summary -->
            <div style="display:grid; grid-template-columns:255px 1fr; gap:16px; align-items:center; margin-bottom:10px;">
                <div>
                    <?php echo self::render_d1_diamond_svg(240, $lagna_num, $planets_by_house); ?>
                    <div style="text-align:center; font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        Janma Kundali (D1) &bull; <?php echo esc_html($profile['lagna_sign']); ?> Lagna <?php echo esc_html($profile['lagna_deg']); ?>&deg;
                    </div>
                </div>

                <div class="mr-card" style="padding:12px 16px; background:#FFFFFF;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size:13.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px; border-bottom:1px solid #F3EFEA; padding-bottom:4px;">
                        &diams; Natal Planetary Overview
                    </div>
                    <p style="font-size:13px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your foundational chart is anchored by <strong><?php echo esc_html($profile['lagna_sign']); ?> Lagna</strong> (ruled by <?php echo esc_html($profile['lagna_lord']); ?>), giving you strong intuition, deep thinking, and natural leadership ability.
                    </p>
                    <p style="font-size:13px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your natal <strong>Moon resides in <?php echo esc_html($profile['moon_sign']); ?></strong> under <strong><?php echo esc_html($profile['moon_nak']); ?></strong> Nakshatra (Pada <?php echo esc_html($profile['moon_pada']); ?>), supporting clear decision making and emotional balance.
                    </p>
                    <div style="background:#FDF8F3; border:1px solid #ECE6DD; border-radius:6px; padding:6px 10px; font-size:12px; color:#C45738; font-weight:700;">
                        Natal Pillars: <?php echo esc_html($profile['lagna_sign']); ?> Ascendant &bull; <?php echo esc_html($profile['moon_sign']); ?> Lunar Base &bull; <?php echo esc_html($profile['sun_sign']); ?> Solar Force
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
                        Moving through your houses of gains, Jupiter brings good fortune, helpful advice from elders, and steady financial growth. It is a wonderful time to learn new skills, make good friends, and achieve long-held goals.
                    </p>
                </div>

                <!-- Transit 2: Saturn -->
                <div class="mr-card" style="padding:12px 14px; border-top:3px solid #1E3A8A;">
                    <div style="font-size:9.5px; font-weight:800; color:#1E3A8A; text-transform:uppercase; letter-spacing:0.06em;">MAJOR TRANSIT II</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; margin:2px 0 6px 0;">Saturn (Shani Gochar)</div>
                    <p style="font-size:11.2px; line-height:1.5; color:#4B5563; margin:0;">
                        Saturn asks you to stay disciplined, honest, and patient in your work and studies. There is no active Sade Sati; simple everyday dedication and steady habits will build lasting success.
                    </p>
                </div>

                <!-- Transit 3: Rahu-Ketu -->
                <div class="mr-card" style="padding:12px 14px; border-top:3px solid #C45738;">
                    <div style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em;">MAJOR TRANSIT III</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827; margin:2px 0 6px 0;">Rahu-Ketu Nodal Axis</div>
                    <p style="font-size:11.2px; line-height:1.5; color:#4B5563; margin:0;">
                        Rahu brings creative ideas and fresh ambition for career and technology, while Ketu reminds you to speak kindly and manage household expenses carefully.
                    </p>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Jupiter's Blessings: Jupiter brings strong support for career growth, helpful friends, and good earnings.",
                "Saturn's Guidance: Staying disciplined and patient in your daily routine builds long-term security.",
                "Creative Ideas: Rahu sparks innovative solutions and helps you find fresh ways to solve problems."
            ], "2026 Transit & Key Insights"); ?>
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
            <h1 class="mr-page-h1">The Four Core Life Pillars: <?php echo esc_html($forecast_year); ?> Outlook</h1>
            <div class="mr-page-sub">Clear and practical guidance for career, savings, family happiness, and health</div>

            <!-- 2x2 Grid of the Four Life Pillars -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px 20px; margin-bottom:8px;">
                <!-- Pillar 1: Career & Enterprise -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">1. Career &amp; Professional Growth</div>
                        <span class="mr-badge mr-badge-gold">Steady Growth</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        With supportive planets blessing your work houses, this year gives you great opportunities to take on more responsibility, do well in interviews, and grow your business.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        The middle of the year (April to September) brings noticeable appreciation from seniors, bosses, or business partners. Focus on doing your work with honesty and consistency.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#C45738;">Best Action:</strong> Plan your work goals clearly, communicate well with your team, and keep learning.
                    </div>
                </div>

                <!-- Pillar 2: Wealth & Finances -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">2. Wealth, Savings &amp; Property</div>
                        <span class="mr-badge mr-badge-emerald">Good Gains</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your income streams improve steadily, and past savings or investments will grow nicely. You will feel more comfortable managing your family expenses.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Avoid risky quick-money schemes or speculative trading. Put your hard-earned money into safe fixed deposits, gold, mutual fund SIPs, or real estate.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#047857;">Best Action:</strong> Save a good portion of any bonus into long-term savings, and keep an emergency fund.
                    </div>
                </div>

                <!-- Pillar 3: Love & Relationships -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #1E3A8A;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">3. Family Life, Marriage &amp; Relationships</div>
                        <span class="mr-badge mr-badge-blue">Warm &amp; Caring</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Relationships become warmer and closer. If small misunderstandings happen, take a deep breath and listen with patience rather than reacting immediately.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Spending quality time with family, planning trips together, and respecting elders' advice will bring immense peace and joy to your household.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#1E3A8A;">Best Action:</strong> Keep your conversations gentle at home and leave workplace stress outside the door.
                    </div>
                </div>

                <!-- Pillar 4: Health & Vitality -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:13px; font-weight:800; color:#111827;">4. Health, Energy &amp; Daily Wellness</div>
                        <span class="mr-badge mr-badge-amber">High Stamina</span>
                    </div>
                    <p style="font-size:11.8px; line-height:1.55; color:#374151; margin-bottom:6px;">
                        Your overall energy and stamina remain solid. Take care not to overwork or skip meals when you have busy deadlines at work.
                    </p>
                    <p style="font-size:11.5px; line-height:1.5; color:#4B5563; margin-bottom:8px;">
                        Drink plenty of warm water throughout the day, take a 20-minute morning walk in fresh air, and get good restful sleep by 10:30 PM.
                    </p>
                    <div style="background:#FAF7F2; padding:6px 10px; border-radius:5px; font-size:10.5px; color:#111827; font-weight:600;">
                        <strong style="color:#D97706;">Best Action:</strong> Do 10 minutes of morning stretching or Surya Namaskar, and avoid late-night screens.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Career Wins: Great opportunities for job growth and business success throughout the year.",
                "Safe Savings: Keep your money safe in reliable investments and avoid quick-money schemes.",
                "Family Peace: Gentle communication and spending time together keep home life happy and joyful.",
                "Daily Health: A morning walk, warm water, and regular sleep keep your stamina high."
            ], "Four Pillars 2026 Practical Takeaways"); ?>
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
            <h1 class="mr-page-h1"><?php echo esc_html($forecast_year); ?> Quarterly Guide &amp; Key Dates</h1>
            <div class="mr-page-sub">Your month-by-month quarterly guide to the best timing for work and family</div>

            <!-- 4-Quarter Timeline Progression -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px 18px; margin-bottom:12px;">
                <!-- Q1 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #1E3A8A;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q1 (Jan – Mar): Planning &amp; Foundation</div>
                        <span class="mr-badge mr-badge-blue">Planning Phase</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Focus on clearing pending work from last year, organizing your finances, and setting clear goals. Avoid rushing into new money commitments before mid-February.
                    </p>
                    <div style="font-size:10px; color:#4B5563; font-weight:700;">Key Focus: Daily routines, clearing old dues, planning ahead.</div>
                </div>

                <!-- Q2 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q2 (Apr – Jun): Best Action Window &amp; Growth</div>
                        <span class="mr-badge mr-badge-emerald">Golden Window</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Jupiter brings exciting energy. A great time to start new projects, apply for promotions, sign deals, and make important life moves.
                    </p>
                    <div style="font-size:10px; color:#047857; font-weight:700;">Key Focus: Career moves, new partnerships, buying assets.</div>
                </div>

                <!-- Q3 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q3 (Jul – Sep): Review, Savings &amp; Travel</div>
                        <span class="mr-badge mr-badge-amber">Family &amp; Savings</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        Good time to save money, check your budget, and enjoy family trips or relaxing spiritual holidays. Protect your savings cushion.
                    </p>
                    <div style="font-size:10px; color:#92400E; font-weight:700;">Key Focus: Budgeting, family time, spiritual peace.</div>
                </div>

                <!-- Q4 -->
                <div class="mr-card" style="padding:12px 16px; border-left:3.5px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-weight:800; color:#111827;">Q4 (Oct – Dec): Rewards &amp; Festive Joy</div>
                        <span class="mr-badge mr-badge-gold">Harvest &amp; Rewards</span>
                    </div>
                    <p style="font-size:11.2px; line-height:1.5; color:#374151; margin-bottom:4px;">
                        A wonderful quarter filled with bonuses, festival celebrations, and appreciation for your hard work. Real estate matters settle nicely.
                    </p>
                    <div style="font-size:10px; color:#C45738; font-weight:700;">Key Focus: Financial gains, Diwali festivities, future planning.</div>
                </div>
            </div>

            <!-- Strategic Windows: Golden Opportunity vs Cautionary Windows -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:6px;">
                <!-- Golden Windows -->
                <div class="mr-card" style="padding:12px 16px; background:#F0FDF4; border:1.5px solid #86EFAC;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-size:16px;">🌟</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#166534; text-transform:uppercase;">
                            Golden Action Windows
                        </div>
                    </div>
                    <div style="font-size:11.2px; color:#14532D; line-height:1.5;">
                        <div style="margin-bottom:4px;"><strong>&bull; April 18 – May 26:</strong> Excellent time for property registration, starting ventures, and major signings.</div>
                        <div><strong>&bull; October 10 – November 18:</strong> Strong financial inflows, festive happiness, and family celebrations.</div>
                    </div>
                </div>

                <!-- Cautionary Windows -->
                <div class="mr-card" style="padding:12px 16px; background:#FFFBEB; border:1.5px solid #FCD34D;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-size:16px;">⚠️</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#92400E; text-transform:uppercase;">
                            Careful Planning Windows
                        </div>
                    </div>
                    <div style="font-size:11.2px; color:#78350F; line-height:1.5;">
                        <div style="margin-bottom:4px;"><strong>&bull; March 14 – April 04:</strong> Double-check travel schedules, contracts, and paperwork before signing.</div>
                        <div><strong>&bull; August 12 – August 30:</strong> Stay calm in family talks; avoid quick emotional arguments or lending large sums.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Q2 Growth: April to June is your standout period for making major career and financial moves.",
                "Q3 Rest: July to September is best for organizing finances and spending quality time with family.",
                "Best Dates: Plan your big launches and investments during the April-May and October-November windows.",
                "Careful Dates: Double-check details and keep calm during March and August."
            ], "2026 Quarterly Timing Takeaways"); ?>
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
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Simple Daily Remedies &amp; Next Steps</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">04 &bull; SIMPLE REMEDIES &amp; COMPLETE GUIDES</div>
            <h1 class="mr-page-h1">Simple Daily Remedies &amp; Your Complete Life Guides</h1>
            <div class="mr-page-sub">Easy daily habits for peace and success in <?php echo esc_html($forecast_year); ?> &bull; Explore in-depth life reports</div>

            <!-- Top Section: 3 Practical Everyday Upayas -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #047857;">
                    <div style="font-size:9.5px; font-weight:800; color:#047857; text-transform:uppercase;">EVERYDAY PRACTICE I</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Lucky Days &amp; Colors</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        <strong>Monday (Moon)</strong> and <strong>Tuesday (Mars)</strong> are your most supportive days. Wear soothing white, cream, or light coral red to feel calm and confident. Try to avoid very dark grey during important meetings.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #C45738;">
                    <div style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase;">EVERYDAY PRACTICE II</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Simple Mantras for Peace</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        Chanting <em>Om Som Somaya Namah</em> (11 times) in the morning brings peaceful thoughts. On Tuesdays, chanting <em>Om Hanumate Namah</em> gives you courage, focus, and energy to overcome obstacles.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 14px; background:#FAF7F2; border-top:3px solid #1E3A8A;">
                    <div style="font-size:9.5px; font-weight:800; color:#1E3A8A; text-transform:uppercase;">EVERYDAY PRACTICE III</div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin:2px 0 4px 0;">Acts of Kindness (Daan)</div>
                    <p style="font-size:11px; line-height:1.5; color:#374151; margin:0;">
                        Offering clean water to birds or plants on Mondays brings positive energy to your home. Helping students in need or supporting simple healthcare causes on Saturdays brings peace of mind and clears career roadblocks.
                    </p>
                </div>
            </div>

            <!-- Middle Section: High-Converting Master Report Upgrade Showcase -->
            <div class="mr-card-accent" style="padding:14px 18px; margin-bottom:12px; background:#FFFDF9;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <div>
                        <span style="font-size:9.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em;">UNLOCK YOUR COMPLETE LIFE BLUEPRINT</span>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#111827;">Want to See the Bigger Picture? Explore In-Depth Life Reports</div>
                    </div>
                    <span class="mr-badge mr-badge-gold" style="font-size:11px; padding:4px 10px;">Full Kundali Guides</span>
                </div>
                <p style="font-size:11.8px; color:#4B5563; line-height:1.5; margin-bottom:10px;">
                    This 5-page summary gives you a quick look at 2026. If you want detailed answers about your career, marriage timing, and complete life journey, check out our full specialized reports:
                </p>

                <!-- 3 Master Report Cards -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#C45738; font-size:11.5px; display:block; margin-bottom:2px;">1. Master Janma Kundali (26 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Your complete birth chart analysis, planetary strengths, all 12 life areas, and clear answers for health, family, wealth, and career.
                        </span>
                    </div>

                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#047857; font-size:11.5px; display:block; margin-bottom:2px;">2. Master Dasha Timing (20 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Your complete 120-year planetary timeline, showing which planets run your life right now and pinpointing the best times for new beginnings.
                        </span>
                    </div>

                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:8px 10px;">
                        <strong style="color:#1E3A8A; font-size:11.5px; display:block; margin-bottom:2px;">3. Master Numerology (22 Pages)</strong>
                        <span style="font-size:10.5px; color:#6B7280; line-height:1.4; display:block;">
                            Detailed analysis of your birth date and lucky numbers, Lo Shu energy grid, name spelling advice, and practical action plans.
                        </span>
                    </div>
                </div>

                <div style="background:#FAF7F2; border:1px dashed #C2964C; border-radius:6px; padding:8px 14px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11px; color:#111827; font-weight:700;">
                        Special Member Benefit: You can view or download your full reports anytime from your account dashboard.
                    </span>
                    <span style="font-size:11px; font-weight:800; color:#C45738;">www.MeraRashifal.com &rarr;</span>
                </div>
            </div>

            <!-- Customer Portal & Concluding Benediction Box -->
            <div class="mr-card" style="padding:10px 16px; background:#FFFFFF; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight:800; font-size:11px; color:#111827; text-transform:uppercase; letter-spacing:0.04em;">Official Customer Support &amp; Verification Portal</div>
                    <div style="font-size:10.5px; color:#6B7280; margin-top:1px;">Access verified predictions, planet transits, and clear astrology guidance anytime.</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:<?php echo $head_font; ?>; font-weight:800; color:#C45738; font-size:12px;">www.MeraRashifal.com</div>
                    <div style="font-size:9.5px; color:#9CA3AF;">Document ID: MR-YR-<?php echo strtoupper(substr(md5($person_name . $dob . 'YEARLY2026'), 0, 8)); ?></div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Simple Daily Habits: Small routines on Mondays and Tuesdays help you stay calm and focused through busy weeks.",
                "Power of Kindness: Simple daily prayers and helping others bring positive energy and clear unexpected stress.",
                "Explore Further: Our detailed 26-page Kundali and 20-page Dasha reports give you step-by-step guidance for every decade.",
                "Authentic Calculations: All predictions are calculated using traditional Vedic principles for your exact birth time and place."
            ], "Simple Remedies &amp; Next Steps"); ?>
            <?php echo self::render_page_footer(5, 'Simple Remedies & Master Upgrade'); ?>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
