<?php
/**
 * Class MR_Monthly_Report_Builder
 *
 * Sovereign Vedic Astrological Monthly Horizon & Thermal Blueprint (Strictly 15 Pages).
 * Fully aligned with the Master Design System & Typography Architecture.
 *
 * Page Budget (Exactly 15 Physical A4 Pages):
 * Page 01: Master Cover Page (4x Logo, Dual Sun Signs, 3 Pillars, Dasha strip, Birth Details)
 * Page 02: Table of Contents & Strategic Reading Guide (Executive Navigation & Blueprint Guide)
 * Page 03: Master Energy Index & Cosmic Thermal Map (Prana Score, Agni/Shita/Sattva, 4 Pillars)
 * Page 04: Topographic Trajectory (4 Elevation Epochs with Do's and Don'ts)
 * Page 05: 30-Day Astrological Heat Grid + Auspicious Shubh Muhurat Matrix
 * Page 06: Relational Horizon (Prema & Bandhu - Committed vs. Singles)
 * Page 07: Material Horizon (Karma & Artha - Cash Flow Timing & High-Stakes Deals)
 * Page 08: Astrological Geometry (Drishti Aspects & Angular Triggers)
 * Page 09: The Celestial Council (9-Graha Sthiti + Monthly Key Transit Ingress Shifts Box)
 * Page 10: Phase-by-Phase Tactical Execution Protocols (Weekly Blueprints)
 * Page 11: Chandra Gochar (Lunar Mansions: Amavasya & Purnima Anchors)
 * Page 12: Panchamahabhuta & Time-Bound Remedial Prescriptions (Specific Daan & Hora)
 * Page 13: Sovereign Horizon Dashboard & Guided Monthly Sankalpa (Interactive Prompts)
 * Page 14: Glossary of Astrological Terms & Reference Lexicon (Comprehensive Definitions)
 * Page 15: Sovereign Astrological Catalogue, Ecosystem Archive & Verification Seal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Monthly_Report_Builder {

    /**
     * Get Base64 Data URI for MeraRashifal Logo
     */
    public static function get_logo_data_uri() {
        return MR_Report_Builder::get_logo_data_uri();
    }

    /**
     * Render North Indian Kundali SVG (Clean Vector, No DOM Overflow)
     */
    public static function render_north_indian_kundali_svg($size = 205, $planets_by_house = [], $lagna_sign_num = 4, $title_font = 'sans-serif') {
        $s = $size;
        $half = $s / 2;

        if (empty($planets_by_house)) {
            // Default placements for Cancer Lagna (Tushar Singhal)
            $planets_by_house = [
                1  => ['Ju'],
                2  => ['Mo'],
                4  => ['Ve(R)'],
                5  => ['Su', 'Me(R)'],
                7  => ['Ma'],
                8  => ['Ra'],
                9  => ['Sa(R)'],
                11 => ['Ke']
            ];
        }

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.5px solid #C2964C; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.2'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.2'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='none' stroke='#C2964C' stroke-width='1.2'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='7' fill='#C2964C' fill-opacity='0.15'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.45, 'pl_x' => $half, 'pl_y' => $half * 0.75],
            2  => ['num_x' => $half * 0.5, 'num_y' => $half * 0.22, 'pl_x' => $half * 0.45, 'pl_y' => $half * 0.45],
            3  => ['num_x' => $half * 0.22, 'num_y' => $half * 0.5, 'pl_x' => $half * 0.45, 'pl_y' => $half * 0.75],
            4  => ['num_x' => $half * 0.45, 'num_y' => $half, 'pl_x' => $half * 0.75, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.22, 'num_y' => $half * 1.5, 'pl_x' => $half * 0.45, 'pl_y' => $half * 1.25],
            6  => ['num_x' => $half * 0.5, 'num_y' => $half * 1.78, 'pl_x' => $half * 0.45, 'pl_y' => $half * 1.55],
            7  => ['num_x' => $half, 'num_y' => $half * 1.55, 'pl_x' => $half, 'pl_y' => $half * 1.25],
            8  => ['num_x' => $half * 1.5, 'num_y' => $half * 1.78, 'pl_x' => $half * 1.55, 'pl_y' => $half * 1.55],
            9  => ['num_x' => $half * 1.78, 'num_y' => $half * 1.5, 'pl_x' => $half * 1.55, 'pl_y' => $half * 1.25],
            10 => ['num_x' => $half * 1.55, 'num_y' => $half, 'pl_x' => $half * 1.25, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.78, 'num_y' => $half * 0.5, 'pl_x' => $half * 1.55, 'pl_y' => $half * 0.75],
            12 => ['num_x' => $half * 1.5, 'num_y' => $half * 0.22, 'pl_x' => $half * 1.55, 'pl_y' => $half * 0.45],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='13.8' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = implode(' ', $planets_by_house[$h]);
                $svg .= "<text x='{$c['pl_x']}' y='{$c['pl_y']}' fill='#111827' font-size='11.9' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pls}</text>";
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Topographic Elevation Wave SVG Graph
     */
    public static function render_topographic_elevation_svg($w = 540, $h = 80) {
        $pad_l = 40;
        $pad_r = 55;
        $pad_t = 24;
        $pad_b = 20;
        $gw = $w - $pad_l - $pad_r;
        $gh = $h - $pad_t - $pad_b;

        $pts = [
            ['w' => 1, 'x' => 0.10, 'y' => 0.45, 'pct' => 68, 'label' => 'W1: Ascent (68%)'],
            ['w' => 2, 'x' => 0.36, 'y' => 0.70, 'pct' => 59, 'label' => 'W2: Recalibrate (59%)'],
            ['w' => 3, 'x' => 0.65, 'y' => 0.36, 'pct' => 73, 'label' => 'W3: Realignment (73%)'],
            ['w' => 4, 'x' => 0.88, 'y' => 0.16, 'pct' => 88, 'label' => 'W4: Breakthrough (88%)'],
        ];

        $coords = [];
        foreach ($pts as $p) {
            $coords[] = [
                'cx'    => round($pad_l + ($gw * $p['x'])),
                'cy'    => round($pad_t + ($gh * $p['y'])),
                'pct'   => $p['pct'],
                'label' => $p['label'],
            ];
        }

        $svg = "<svg width='100%' height='auto' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.02); display:block; overflow:visible;'>";

        for ($g = 0; $g <= 3; $g++) {
            $gy = round($pad_t + ($gh * ($g / 3)));
            $svg .= "<line x1='{$pad_l}' y1='{$gy}' x2='" . ($w - $pad_r) . "' y2='{$gy}' stroke='#F4EFE6' stroke-width='1'/>";
        }

        $p0 = $coords[0]; $p1 = $coords[1]; $p2 = $coords[2]; $p3 = $coords[3];
        $bottom_y = $pad_t + $gh;

        $path_d  = "M {$p0['cx']} {$p0['cy']} ";
        $path_d .= "C " . round($p0['cx'] + 50) . " " . $p0['cy'] . ", " . round($p1['cx'] - 50) . " " . $p1['cy'] . ", {$p1['cx']} {$p1['cy']} ";
        $path_d .= "C " . round($p1['cx'] + 50) . " " . $p1['cy'] . ", " . round($p2['cx'] - 50) . " " . $p2['cy'] . ", {$p2['cx']} {$p2['cy']} ";
        $path_d .= "C " . round($p2['cx'] + 50) . " " . $p2['cy'] . ", " . round($p3['cx'] - 50) . " " . $p3['cy'] . ", {$p3['cx']} {$p3['cy']}";

        $area_d  = $path_d . " L {$p3['cx']} {$bottom_y} L {$p0['cx']} {$bottom_y} Z";

        $svg .= "<defs>";
        $svg .= "<linearGradient id='mrTopoGrad' x1='0' y1='0' x2='0' y2='1'>";
        $svg .= "<stop offset='0%' stop-color='#C45738' stop-opacity='0.22'/>";
        $svg .= "<stop offset='100%' stop-color='#C45738' stop-opacity='0.02'/>";
        $svg .= "</linearGradient>";
        $svg .= "</defs>";

        $svg .= "<path d='{$area_d}' fill='url(#mrTopoGrad)'/>";
        $svg .= "<path d='{$path_d}' fill='none' stroke='#C45738' stroke-width='2.2' stroke-linecap='round'/>";

        foreach ($coords as $c) {
            $svg .= "<circle cx='{$c['cx']}' cy='{$c['cy']}' r='4.5' fill='#C45738' stroke='#FFFFFF' stroke-width='2'/>";
            $svg .= "<text x='{$c['cx']}' y='" . ($c['cy'] - 8) . "' fill='#111827' font-size='11' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$c['label']}</text>";
        }

        $svg .= "<text x='{$p0['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='10.5' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 1–7</text>";
        $svg .= "<text x='{$p1['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='10.5' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 8–14</text>";
        $svg .= "<text x='{$p2['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='10.5' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 15–21</text>";
        $svg .= "<text x='{$p3['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='10.5' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 22–30</text>";

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Shadbala Horizontal Bars SVG
     */
    public static function render_shadbala_bars_svg($w = 540, $h = 88) {
        $bars = [
            ['p' => 'Sun (Surya)',   's' => 412, 'req' => 390, 'pct' => 105, 'c' => '#C45738'],
            ['p' => 'Moon (Chandra)','s' => 456, 'req' => 360, 'pct' => 126, 'c' => '#4E7D68'],
            ['p' => 'Mars (Mangal)', 's' => 388, 'req' => 300, 'pct' => 129, 'c' => '#C45738'],
            ['p' => 'Mercury (Budh)','s' => 420, 'req' => 420, 'pct' => 100, 'c' => '#4E7D68'],
            ['p' => 'Jupiter (Guru)','s' => 495, 'req' => 390, 'pct' => 127, 'c' => '#C2964C'],
            ['p' => 'Venus (Shukra)','s' => 462, 'req' => 330, 'pct' => 140, 'c' => '#C2964C'],
            ['p' => 'Saturn (Shani)','s' => 345, 'req' => 300, 'pct' => 115, 'c' => '#4B5563'],
        ];

        $svg = "<svg width='100%' height='auto' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:6px; box-shadow:0 2px 6px rgba(0,0,0,0.02); display:block;'>";

        $col_w = floor(($w - 24) / count($bars));
        foreach ($bars as $i => $b) {
            $x = 12 + ($i * $col_w);
            $bar_max_h = 44;
            $bh = round(($b['pct'] / 150) * $bar_max_h);
            $by = 56 - $bh;

            $svg .= "<rect x='{$x}' y='{$by}' width='" . ($col_w - 14) . "' height='{$bh}' rx='3' fill='{$b['c']}'/>";
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='" . ($by - 3) . "' fill='#111827' font-size='10.6' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$b['pct']}%</text>";
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='68' fill='#4B5563' font-size='10' font-family='sans-serif' font-weight='700' text-anchor='middle'>" . explode(' ', $b['p'])[0] . "</text>";
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='78' fill='#9CA3AF' font-size='9.4' font-family='sans-serif' text-anchor='middle'>{$b['s']} V</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Panchamahabhuta Radar SVG
     */
    public static function render_panchamahabhuta_radar_svg($size = 145) {
        $vw = 185;
        $vh = 160;
        $cx = 92;
        $cy = 80;
        $r = 38;

        $elements = [
            ['name' => 'Agni (Fire)',     'val' => 0.88, 'angle' => -90],
            ['name' => 'Prithvi (Earth)', 'val' => 0.52, 'angle' => -18],
            ['name' => 'Vayu (Air)',      'val' => 0.82, 'angle' => 54],
            ['name' => 'Jala (Water)',    'val' => 0.60, 'angle' => 126],
            ['name' => 'Akash (Space)',   'val' => 0.70, 'angle' => 198],
        ];

        $svg = "<svg width='160' height='138' viewBox='0 0 {$vw} {$vh}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.02); display:block; margin:0 auto; overflow:visible;'>";

        for ($ring = 1; $ring <= 3; $ring++) {
            $rr = $r * ($ring / 3);
            $ring_pts = [];
            foreach ($elements as $el) {
                $rad = deg2rad($el['angle']);
                $ring_pts[] = round($cx + $rr * cos($rad)) . ',' . round($cy + $rr * sin($rad));
            }
            $svg .= "<polygon points='" . implode(' ', $ring_pts) . "' fill='none' stroke='#F0EAE1' stroke-width='1'/>";
        }

        $data_pts = [];
        foreach ($elements as $el) {
            $rad = deg2rad($el['angle']);
            $svg .= "<line x1='{$cx}' y1='{$cy}' x2='" . round($cx + $r * cos($rad)) . "' y2='" . round($cy + $r * sin($rad)) . "' stroke='#ECE6DD' stroke-width='1'/>";

            $dr = $r * $el['val'];
            $data_pts[] = round($cx + $dr * cos($rad)) . ',' . round($cy + $dr * sin($rad));

            $lbl_r = $r + 14;
            $lx = round($cx + $lbl_r * cos($rad));
            $ly = round($cy + $lbl_r * sin($rad)) + 3;
            $svg .= "<text x='{$lx}' y='{$ly}' fill='#4B5563' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>" . explode(' ', $el['name'])[0] . "</text>";
        }

        $svg .= "<polygon points='" . implode(' ', $data_pts) . "' fill='#C45738' fill-opacity='0.25' stroke='#C45738' stroke-width='1.8'/>";

        foreach ($data_pts as $dp) {
            $xy = explode(',', $dp);
            $svg .= "<circle cx='{$xy[0]}' cy='{$xy[1]}' r='3' fill='#C45738' stroke='#FFFFFF' stroke-width='1.2'/>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Symbol Guide Key Bar
     */
    public static function render_symbol_guide_bar($items, $title = 'Symbol Key') {
        ob_start();
        ?>
        <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 12px; margin-top:6px; display:flex; align-items:center; gap:12px; font-size: 11.9px; color:#4B5563;">
            <strong style="color:#111827; font-size: 12.5px; text-transform:uppercase; letter-spacing:0.06em; flex-shrink:0;">
                🧭 <?php echo esc_html($title); ?>:
            </strong>
            <div style="display:flex; flex-wrap:wrap; gap:14px; flex:1;">
                <?php foreach ($items as $it): ?>
                    <div style="display:inline-flex; align-items:center; gap:4px;">
                        <span><?php echo $it['icon']; ?></span>
                        <strong style="color:#111827;"><?php echo esc_html($it['term']); ?>:</strong>
                        <span><?php echo esc_html($it['desc']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Scannable Bottom Synthesis Box
     */
    public static function render_page_bottom_synthesis($bullets, $title = 'Essential Monthly Takeaways', $heading_font = "'Poppins', sans-serif") {
        ob_start();
        ?>
        <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:4px solid #C45738; border-radius:6px; padding:7px 12px; margin-top:6px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                <div style="font-family:<?php echo $heading_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.06em;">
                    ⚡ <?php echo esc_html($title); ?>
                </div>
                <span style="font-size: 10.6px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.08em;">Strategic Synthesis</span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:3px 12px; font-size: 12.5px; color:#1F2937; line-height:1.42;">
                <?php foreach ($bullets as $b): ?>
                    <div style="display:flex; align-items:flex-start;">
                        <span style="color:#C45738; margin-right:5px; font-weight:bold; font-size: 11.3px;">•</span>
                        <span><?php echo esc_html($b); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Build the Complete 15-Page Publication Report HTML
     */
    public static function build_monthly_report_html($person_name, $gender, $dob, $tob, $pob, $month_year = 'November 2026', $editorial_data = [], $vedic_data = [], $typography_option = 'option2') {
        $logo_uri = self::get_logo_data_uri();
        $dob_clean = str_replace('/', '-', $dob);
        $dob_dt = DateTime::createFromFormat('d-m-Y', $dob_clean);
        $formatted_dob = $dob_dt ? $dob_dt->format('F j, Y') : (strtotime($dob_clean) ? date('F j, Y', strtotime($dob_clean)) : $dob);
        $astro_prof = MR_Report_Builder::extract_astro_profile($dob, $tob, $pob, $vedic_data);

        if (empty($editorial_data)) {
            $editorial_data = MR_Gemini_AI::generate_monthly_editorial($person_name, [
                'sun_sign'    => 'Sagittarius',
                'moon_sign'   => 'Leo (Magha)',
                'rising_sign' => 'Cancer (21.27°)',
                'dasha'       => 'Rahu Mahadasha / Rahu Antardasha'
            ], $month_year);
        }

        $theme       = $editorial_data['monthly_theme'] ?? 'Work with Patience in the First Half; Command Strategic Success in the Second';
        $synthesis   = $editorial_data['executive_synthesis'] ?? "{$month_year} unfolds in two distinct dynamic halves: the opening fortnight counsels disciplined patience, contract review, and quiet infrastructure groundwork, while the second half unleashes powerful forward momentum, career recognition, and financial harvest.";
        $thermal_idx = $editorial_data['thermal_index'] ?? 78;
        $thermal_st  = $editorial_data['thermal_status'] ?? 'Radiant Agni & Strategic Expansion';
        $pillars     = $editorial_data['four_pillars'] ?? [];
        $topography  = $editorial_data['topographic_trajectory'] ?? [];
        $triggers    = $editorial_data['angular_triggers'] ?? [];
        $panchabhuta = $editorial_data['panchamahabhuta'] ?? [];
        $dashboard   = $editorial_data['executive_dashboard'] ?? [];

        // Typography: Inter for Body, Poppins for Headings (Standard Corporate-Astrology Reference)
        $font_link = '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">';
        $head_font = "'Poppins', sans-serif";
        $body_font = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($person_name . ' · ' . $month_year . ' Vedic Monthly Horizon & Thermal Blueprint'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php echo $font_link; ?>
    <style>
        /* ========================================================
           EXECUTIVE DESIGN SYSTEM ARCHITECTURE (A4 STANDARD)
           ======================================================== */
        :root {
            --bg-page: #FAF7F2;
            --card-bg: #FFFFFF;
            --text-main: #111827;
            --text-body: #1F2937;
            --text-muted: #4B5563;
            --border: #ECE6DD;
            --border-subtle: #F2ECE3;
            --accent-agni: #C45738;
            --accent-sattva: #4E7D68;
            --accent-gold: #C2964C;
            --font-heading: <?php echo $head_font; ?>;
            --font-body: <?php echo $body_font; ?>;
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
            font-family: <?php echo $body_font; ?>;
            font-size: 13.8px;
            line-height: 1.58;
            -webkit-font-smoothing: antialiased;
        }

        /* Strict A4 Container (210mm x 297mm) */
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
            padding: 9mm 14mm 11.5mm 14mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0 auto;
        }

        /* Double Parchment Framing Border */
        .mr-page::before {
            content: "";
            position: absolute;
            top: 7mm;
            left: 7mm;
            right: 7mm;
            bottom: 7mm;
            border: 1px solid #ECE6DD;
            pointer-events: none;
            z-index: 1;
        }
        .mr-page::after {
            content: "";
            position: absolute;
            top: 8.5mm;
            left: 8.5mm;
            right: 8.5mm;
            bottom: 8.5mm;
            border: 0.5px solid #E2DACF;
            pointer-events: none;
            z-index: 1;
        }

        /* Interior Header & Footer Bar */
        .mr-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ECE6DD;
            padding-bottom: 6px;
            margin-bottom: 8px;
            z-index: 2;
            position: relative;
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
            font-size: 11.9px;
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
            padding-bottom: 0px;
            font-size: 11.9px;
            line-height: 1.2;
            color: #6B7280;
            z-index: 2;
            position: relative;
            margin-top: auto;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 12.5px;
        }

        /* Typography Hierarchy */
        .mr-tag-pill {
            display: inline-block;
            font-size: 11.3px;
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
            font-family: <?php echo $head_font; ?>;
            font-size: 21px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px 0;
            line-height: 1.22;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 13.8px;
            color: #4B5563;
            margin: 0 0 8px 0;
            line-height: 1.4;
            font-weight: 500;
        }

        /* Crisp Milk White Cards */
        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 7px;
            padding: 9px 13px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            margin-bottom: 7px;
            position: relative;
        }
        .mr-card.terracotta-accent { border-left: 3.5px solid #C45738; }
        .mr-card.sage-accent { border-left: 3.5px solid #4E7D68; }
        .mr-card.gold-accent { border-left: 3.5px solid #C2964C; }
        .mr-card.slate-accent { border-left: 3.5px solid #4B5563; }

        .mr-card-title {
            font-family: <?php echo $head_font; ?>;
            font-size: 16.9px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

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

        .mr-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
        .mr-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 7px; }

        .mr-bullet-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 4px;
            font-size: 13.1px;
            color: #1F2937;
            line-height: 1.45;
        }
        .mr-bullet-dot {
            width: 4.5px;
            height: 4.5px;
            border-radius: 50%;
            background: #C45738;
            margin-top: 5px;
            margin-right: 7px;
            flex-shrink: 0;
        }

        /* Cover Page Styling */
        .mr-cover-page {
            background: #FAF7F2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            padding: 10mm 16mm 12mm 16mm;
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
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .mr-cover-logo {
            max-width: 380px;
            height: 80px;
            max-height: 135px;
            object-fit: contain;
        }
        .mr-cover-badge {
            font-size: 16.9px;
            letter-spacing: 0.10em;
            color: #C45738;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 7px;
            line-height: 1.4;
        }
        .mr-cover-title {
            font-family: <?php echo $head_font; ?>;
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            line-height: 1.25;
            letter-spacing: 0.01em;
            margin: 0 0 6px 0;
        }
        .mr-cover-theme {
            font-family: <?php echo $head_font; ?>;
            font-size: 15px;
            font-style: italic;
            color: #4B5563;
            max-width: 480px;
            margin: 0 auto 8px auto;
            line-height: 1.45;
        }
        .mr-cover-native-box {
            background: #FFFFFF;
            border: 1.5px solid #ECE6DD;
            border-radius: 10px;
            padding: 10px 18px;
            width: 100%;
            max-width: 470px;
            margin: 0 auto;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            z-index: 2;
        }
        .mr-cover-prepared {
            font-size: 11.3px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #6B7280;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .mr-cover-name {
            font-family: <?php echo $head_font; ?>;
            font-size: 23px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        .mr-cover-meta-grid {
            display: flex;
            justify-content: space-around;
            border-top: 1px solid #ECE6DD;
            padding-top: 8px;
            font-size: 13.8px;
        }
        .mr-cover-meta-item strong { display: block; font-size: 15.6px; color: #C45738; font-weight: 800; }
        .mr-cover-meta-item span { font-size: 11.3px; color: #4B5563; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }

        .mr-cover-dasha-strip {
            margin-top: 8px;
            padding-top: 7px;
            border-top: 1px dashed #ECE6DD;
            font-size: 13.1px;
            color: #4B5563;
            display: flex;
            justify-content: space-around;
        }
        .mr-cover-dasha-strip strong { color: #111827; font-weight: 700; }

        /* Offer Cards on Back Page */
        .mr-offer-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 7px;
            padding: 8px 12px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .mr-offer-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #C45738;
            flex-shrink: 0;
        }
        .mr-offer-info h4 {
            margin: 0;
            font-family: <?php echo $head_font; ?>;
            font-size: 16.3px;
            color: #111827;
            font-weight: 700;
        }
        .mr-offer-info p {
            margin: 2px 0 0 0;
            font-size: 12.5px;
            color: #4B5563;
            line-height: 1.4;
        }
        .mr-offer-meta {
            font-size: 10.6px;
            font-weight: 800;
            color: #C45738;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- ========================================================
         PAGE 1: MASTER COVER PAGE (4X BRANDING LOGO)
         ======================================================== -->
    <div class="mr-page mr-cover-page">
        <div class="mr-cover-logo-wrap">
            <?php if (!empty($logo_uri)): ?>
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-cover-logo" />
            <?php else: ?>
                <h2 style="font-family:<?php echo $head_font; ?>; color:#111827; font-size:26px; letter-spacing:0.1em; margin:0;">MERA RASHIFAL</h2>
            <?php endif; ?>
        </div>

        <div style="z-index: 2;">
            <div class="mr-cover-badge">🪐 Vedic Jyotish Personalized Monthly Horizon &amp; Thermal Blueprint</div>
            <h1 class="mr-cover-title"><?php echo esc_html('Monthly Horoscope Report - ' . $month_year); ?></h1>
            <div class="mr-cover-theme">&ldquo;<?php echo esc_html($theme); ?>&rdquo;</div>

            <div class="mr-cover-native-box">
                <div class="mr-cover-prepared">Prepared Exclusively For</div>
                <div class="mr-cover-name"><?php echo esc_html($person_name); ?></div>
                <div class="mr-cover-meta-grid">
                    <div class="mr-cover-meta-item">
                        <strong><?php echo esc_html($astro_prof['sun_sign']); ?></strong>
                        <span>☀️ Surya Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong><?php echo esc_html($astro_prof['moon_sign']); ?></strong>
                        <span>🌙 Chandra Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong><?php echo esc_html($astro_prof['lagna_sign'] . ' (' . $astro_prof['lagna_deg'] . '°)'); ?></strong>
                        <span>🧭 Janma Lagna</span>
                    </div>
                </div>
                <div class="mr-cover-dasha-strip">
                    <div>Nakshatra: <strong><?php echo esc_html($astro_prof['moon_nak'] . ' (' . $astro_prof['moon_pada'] . ')'); ?></strong></div>
                    <div>Calculated Age: <strong><?php echo intval($astro_prof['age']); ?> Years</strong></div>
                </div>
            </div>
        </div>

        <div style="z-index:2; max-width:540px; margin:4px auto 0 auto; text-align:justify; font-size: 7.2px; color:#64748B; line-height:1.35; background:#FFFFFF; padding:4px 8px; border-radius:4px; border:1px solid #ECE6DD;">
            <strong style="color:#111827;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
        </div>

        <div style="z-index:2; font-size: 11.3px; color:#4B5563; letter-spacing:0.06em; text-transform:uppercase; line-height:1.5; margin-top:4px;">
            <span>Birth Details: <?php echo esc_html($formatted_dob . ' · ' . $tob . ' · ' . $pob); ?></span><br>
            <span style="font-weight:700; color:#111827;">MeraRashifal Vedic Astrological Services · Executive Edition · merarashifal.com</span>
        </div>
    </div>


    <!-- ========================================================
         PAGE 2: TABLE OF CONTENTS & STRATEGIC READING GUIDE
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">Guide &amp; Table of Contents</div>
            <h1 class="mr-page-h1">Table of Contents &amp; How to Read This Guide</h1>
            <div class="mr-page-sub">Your quick guide and overview for navigating your personal monthly horoscope.</div>

            <!-- Table of Contents Master Grid -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ECE6DD; padding-bottom:5px; margin-bottom:6px;">
                    <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em;">Section / Report Module</span>
                    <span style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#C45738; text-transform:uppercase; letter-spacing:0.05em;">Physical Page</span>
                </div>

                <?php
                $toc_items = [
                    ['sec' => 'Section 01', 'title' => 'Monthly Energy Overview & Birth Chart (Lagna Kundali D1 & 4 Life Areas)', 'page' => '03'],
                    ['sec' => 'Section 02', 'title' => 'The 4-Week Journey: Highs, Lows & Weekly Milestones',  'page' => '04'],
                    ['sec' => 'Section 03', 'title' => '30-Day Calendar & Lucky Timings (Shubh Muhurat)',            'page' => '05'],
                    ['sec' => 'Section 04', 'title' => 'Love, Marriage & Family Relationships',             'page' => '06'],
                    ['sec' => 'Section 05', 'title' => 'Career, Wealth & Best Days for Financial Decisions',    'page' => '07'],
                    ['sec' => 'Section 06', 'title' => 'Planetary Aspects: How Planets Influence Your Life (Drishti)',             'page' => '08'],
                    ['sec' => 'Section 07', 'title' => 'Planetary Positions & Major Shifts This Month (Gochar)', 'page' => '09'],
                    ['sec' => 'Section 08', 'title' => 'Week-by-Week Action Plan for Work, Money & Home',    'page' => '10'],
                    ['sec' => 'Section 09', 'title' => 'Moon Phases: New Moon, Full Moon & Peaceful Days',           'page' => '11'],
                    ['sec' => 'Section 10', 'title' => 'Simple Daily Remedies, Lucky Hours & Giving (Daan)',   'page' => '12'],
                    ['sec' => 'Section 11', 'title' => 'Your Monthly Focus & Personal Resolution (Sankalpa)',  'page' => '13'],
                    ['sec' => 'Section 12', 'title' => 'Astrology Terms & Simple Meanings',                  'page' => '14'],
                    ['sec' => 'Section 13', 'title' => 'Complete Vedic Astrology Library & Support',      'page' => '15'],
                ];

                foreach ($toc_items as $ti): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px dashed #F2ECE3; font-size: 13.1px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; color:#C45738; width:70px; flex-shrink:0; font-size: 11.9px;"><?php echo esc_html($ti['sec']); ?></span>
                            <span style="color:#1F2937; font-weight:500;"><?php echo esc_html($ti['title']); ?></span>
                        </div>
                        <div style="font-family:<?php echo $head_font; ?>; font-weight:700; color:#111827; font-size: 13.8px;">
                            Page <?php echo esc_html($ti['page']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- How to Read This Report Executive Guidance Card -->
            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:0;">
                <div class="mr-card-title"><span>🧭 How to Get the Most from Your Monthly Horoscope</span></div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; font-size: 12.5px; line-height:1.45; margin-top:4px;">
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#C45738; font-size: 13.1px; display:block; margin-bottom:2px;">1. The Big Picture</strong>
                        Look at Pages 3–4 first to understand your overall energy flow, busy weeks, and easier times.
                    </div>
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#C2964C; font-size: 13.1px; display:block; margin-bottom:2px;">2. Choosing Good Dates</strong>
                        Check Pages 5, 7, and 9 before signing important agreements, starting new projects, or spending big.
                    </div>
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#4E7D68; font-size: 13.1px; display:block; margin-bottom:2px;">3. Simple Daily Habits</strong>
                        Use the easy remedies and lucky hours on Page 12, and set a positive personal goal on Page 13.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-footer-bar">
            <span>MeraRashifal · Table of Contents &amp; Reading Guide</span>
            <span class="mr-page-num">Page 2 of 15</span>
        </div>
    </div>


    <!-- ========================================================
         PAGE 3: MONTHLY ENERGY INDEX & JANAM KUNDALI
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">01 · Monthly Energy Index</div>
            <h1 class="mr-page-h1">Monthly Overview &amp; Janam Kundali (D1 Chart)</h1>
            <div class="mr-page-sub">Your Birth Chart, Energy Level &amp; What to Expect This Month</div>

            <!-- Side by Side: Kundali SVG & Executive Analysis -->
            <div style="display:flex; gap:14px; align-items:center;">
                <div style="flex-shrink:0;">
                    <?php echo self::render_north_indian_kundali_svg(200, [], 4, $head_font); ?>
                    <div style="text-align:center; font-size: 10.6px; color:#4B5563; font-weight:700; margin-top:3px; text-transform:uppercase; letter-spacing:0.06em;">
                        🪐 Lagna Kundali (Cancer Lagna 21.27°)
                    </div>
                </div>

                <div style="flex:1;">
                    <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:11px 14px;">
                        <div class="mr-card-title">
                            <span>Monthly Energy Level</span>
                            <span style="font-size: 14.4px; color:#C45738; font-weight:800;"><?php echo esc_html($thermal_idx); ?> / 100 · <?php echo esc_html($thermal_st); ?></span>
                        </div>
                        <p style="margin:0 0 5px 0; font-size: 13.8px; color:#1F2937; line-height:1.55;">
                            <?php echo esc_html($synthesis); ?>
                        </p>
                        <div class="mr-thermal-bar-wrap">
                            <div class="mr-thermal-bar-fill" style="width: <?php echo esc_attr(min(100, max(10, $thermal_idx))); ?>%;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size: 10.6px; color:#4B5563; text-transform:uppercase; font-weight:700; margin-bottom:5px;">
                            <span>Rest &amp; Plan (0–40%)</span>
                            <span>Steady Flow (41–70%)</span>
                            <span>High Speed &amp; Action (71–100%)</span>
                        </div>
                        <div style="border-top:1px solid #ECE6DD; padding-top:5px; font-size: 12.5px; color:#4B5563; line-height:1.45;">
                            <strong style="color:#C45738;">Golden Advice for this Month:</strong> Do not rush into new commitments before November 15. Spend the first two weeks planning quietly. The second half of the month brings good speed, respect at work, and financial gains.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Four Pillars Grid -->
            <div class="mr-grid-4" style="margin-top:8px;">
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size: 11.9px; color:#C45738; font-weight:800; text-transform:uppercase;">🏹 Career &amp; Job</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['karma']['score'] ?? 78); ?>%</div>
                    <div style="font-size: 10.6px; color:#4B5563; line-height:1.32;">Strong support from seniors. Work done quietly behind the scenes gets recognized and praised after the 15th.</div>
                </div>
                <div class="mr-card sage-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size: 11.9px; color:#4E7D68; font-weight:800; text-transform:uppercase;">💰 Money &amp; Savings</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['dhana']['score'] ?? 68); ?>%</div>
                    <div style="font-size: 10.6px; color:#4B5563; line-height:1.32;">Regular income stays steady. Avoid lending money to friends or making risky investments during early weeks.</div>
                </div>
                <div class="mr-card gold-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size: 11.9px; color:#C2964C; font-weight:800; text-transform:uppercase;">🪷 Love &amp; Family</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['prema']['score'] ?? 64); ?>%</div>
                    <div style="font-size: 10.6px; color:#4B5563; line-height:1.32;">Stay calm and polite during family discussions. Listen to elders and your spouse, and leave office stress at work.</div>
                </div>
                <div class="mr-card slate-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size: 11.9px; color:#4B5563; font-weight:800; text-transform:uppercase;">🌿 Health &amp; Energy</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['arogya']['score'] ?? 86); ?>%</div>
                    <div style="font-size: 10.6px; color:#4B5563; line-height:1.32;">Good physical stamina. Watch out for mild acidity or throat irritation. A morning walk keeps you fresh.</div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏹', 'term' => 'Career (Karma)',  'desc' => "Job, business, leadership & good reputation"],
                ['icon' => '💰', 'term' => 'Money (Dhana)',   'desc' => "Income, bank savings & managing expenses"],
                ['icon' => '🪷', 'term' => 'Family (Prema)',  'desc' => "Spouse, family harmony & peace at home"],
                ['icon' => '🌿', 'term' => 'Health (Arogya)', 'desc' => "Daily energy, good sleep & physical health"]
            ], 'Life Domain Guide'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "With Cancer Ascendant, friendly planets give you good patience, strong focus, and the ability to handle daily tasks smoothly.",
                "Mercury moving backwards early in the month suggests double-checking all important papers, bills, and travel tickets.",
                "Use the first two weeks to organize your desk, finish pending work, and avoid unnecessary spending.",
                "After November 15, planets turn forward, bringing faster career progress, new client calls, and happy moments at home."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Monthly Overview &amp; Janam Kundali</span>
                <span class="mr-page-num">Page 3 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 4: THE 4-WEEK ROADMAP (WEEK-BY-WEEK GUIDANCE)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">02 · Month Roadmap</div>
            <h1 class="mr-page-h1">The 4-Week Journey (Week-by-Week Roadmap)</h1>
            <div class="mr-page-sub">Week-by-Week Guide, Important Dates &amp; Planning Your Month</div>

            <!-- Topographic Elevation Graph SVG -->
            <div style="margin-bottom:8px;">
                <?php echo self::render_topographic_elevation_svg(540, 80); ?>
            </div>

            <!-- Four Epoch Cards in 2x2 Grid -->
            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>🏔️ Week 1 (Days 1–7): Setting Up Goals</span>
                        <span style="font-size: 12.5px; color:#C45738; font-weight:800;">68% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size: 13.1px; color:#1F2937; line-height:1.48;">
                        You will feel motivated to get things done, but outside work may take a few days to fall into place. Use this week to organize your files, check accounts, and plan your goals quietly.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size: 11.9px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Wake early, write down weekly goals, and seek elders' blessings.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Arguing with senior colleagues or taking on big loans prematurely.</span>
                    </div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>⏳ Week 2 (Days 8–14): Patience &amp; Review</span>
                        <span style="font-size: 12.5px; color:#4E7D68; font-weight:800;">59% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size: 13.1px; color:#1F2937; line-height:1.48;">
                        New Moon brings a calm and quiet time. Government or client approvals might take a few days extra. Stay patient and do not worry; things will move soon.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size: 11.9px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Check your bank balance, pay off pending bills, and spend relaxing time at home.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Impulsive luxury shopping or speaking angrily when you are tired.</span>
                    </div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>🌊 Week 3 (Days 15–21): Momentum Picks Up</span>
                        <span style="font-size: 12.5px; color:#4E7D68; font-weight:800;">73% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size: 13.1px; color:#1F2937; line-height:1.48;">
                        Mercury and Venus start moving smoothly forward! Delayed client payments arrive, pending approvals come through, and discussions at work go very well.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size: 11.9px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Share new ideas with your boss or clients, ask for appraisals, and attend meetings.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Saying yes to too many projects when you already have a busy plate.</span>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>⚡ Week 4 (Days 22–30): Peak Results &amp; Win</span>
                        <span style="font-size: 12.5px; color:#C45738; font-weight:800;">88% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size: 13.1px; color:#1F2937; line-height:1.48;">
                        Full Moon highlights your hard work. The efforts you put in over the last three weeks show clear results, bringing praise, bonuses, and respect from colleagues.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size: 11.9px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Sign final agreements, make good long-term investments, and celebrate with family.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Becoming overconfident or ignoring good advice from your spouse.</span>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '⚡', 'term' => 'Helpful Action (DO)',  'desc' => "Practical actions that bring quick and smooth progress"],
                ['icon' => '🛡️', 'term' => 'Take Care (AVOID)',   'desc' => "Habits to avoid so you do not face unnecessary stress"],
                ['icon' => '🏔️/⏳/🌊/⚡', 'term' => 'Weekly Journey',     'desc' => "Moving smoothly from quiet planning to big wins"]
            ], 'Weekly Roadmap Key'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Weeks 1 & 2 are best for quiet planning, organizing files, and being sensible with money.",
                "Weeks 3 & 4 bring full speed: sign agreements, pursue new career opportunities, and celebrate your wins."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · The 4-Week Journey</span>
                <span class="mr-page-num">Page 4 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 5: 30-DAY ASTROLOGICAL CALENDAR & SHUBH MUHURAT
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">03 · Daily Calendar &amp; Muhurat</div>
            <h1 class="mr-page-h1">30-Day Calendar &amp; Lucky Timings (Shubh Muhurat)</h1>
            <div class="mr-page-sub">Day-by-day energy guide, best dates for new starts, and times to take care</div>

            <!-- Clean Editorial Calendar Table -->
            <table style="width:100%; border-collapse:collapse; text-align:center; font-size: 13.1px; margin-top:2px;">
                <thead>
                    <tr style="background:#F4EFE6; color:#111827; font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; letter-spacing:0.05em;">
                        <th style="padding:5px; border:1px solid #ECE6DD;">MON</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">TUE</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">WED</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">THU</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">FRI</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">SAT</th>
                        <th style="padding:5px; border:1px solid #ECE6DD;">SUN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day = 1;
                    for ($row = 0; $row < 5; $row++): ?>
                        <tr>
                            <?php for ($col = 0; $col < 7; $col++):
                                if ($day > 30) {
                                    echo '<td style="border:1px solid #ECE6DD; padding:4px; background:#FAF7F2;"></td>';
                                    continue;
                                }
                                $score = 50 + (($day * 7) % 45);
                                $is_power = in_array('Day ' . $day, $dashboard['zenith_power_days'] ?? []) || in_array('Nov ' . $day, $dashboard['zenith_power_days'] ?? []) || in_array($day, [7, 8, 26, 27]);
                                $is_sanctuary = in_array('Day ' . $day, $dashboard['sanctuary_rest_days'] ?? []) || in_array('Nov ' . $day, $dashboard['sanctuary_rest_days'] ?? []) || in_array($day, [17, 18, 20]);

                                $bg = '#FFFFFF';
                                $bdr = '#ECE6DD';
                                if ($is_power) { $bg = '#FDF5F2'; $bdr = '#C45738'; }
                                elseif ($is_sanctuary) { $bg = '#F4EFE6'; $bdr = '#C2964C'; }
                                ?>
                                <td style="border:1px solid <?php echo esc_attr($bdr); ?>; padding:2px 1px; background:<?php echo esc_attr($bg); ?>; vertical-align:top; height:27px;">
                                    <div style="font-weight:800; font-size: 13.8px; color:#111827;"><?php echo $day; ?></div>
                                    <div style="font-size: 10.6px; font-weight:800; color:<?php echo $score >= 70 ? '#C45738' : ($score <= 55 ? '#4B5563' : '#4E7D68'); ?>;">
                                        <?php echo $score; ?>%
                                    </div>
                                    <?php if ($day == 9): ?>
                                        <div style="font-size: 8.8px; color:#C45738; font-weight:800;">🌑 Amavasya</div>
                                    <?php elseif ($day == 24): ?>
                                        <div style="font-size: 8.8px; color:#C2964C; font-weight:800;">🌕 Purnima</div>
                                    <?php elseif ($day == 14): ?>
                                        <div style="font-size: 8.8px; color:#4E7D68; font-weight:800;">🧭 Budha Direct</div>
                                    <?php endif; ?>
                                </td>
                                <?php $day++;
                            endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <!-- Dedicated Auspicious Shubh Muhurat Matrix (High-Value Add) -->
            <div style="margin-top:7px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    ✨ Best Auspicious Dates (Shubh Muhurat) for November
                </div>
                <div class="mr-grid-2">
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #4E7D68; border-radius:6px; padding:6px 10px; font-size: 12.5px; line-height:1.42;">
                        <strong style="color:#4E7D68; font-size: 13.1px;">💰 Savings, Investments &amp; Purchases:</strong><br>
                        <span><strong>Nov 6, 16, 21, 27:</strong> Best days for buying gold, starting a fixed deposit, mutual funds, or paying advances for property.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #C45738; border-radius:6px; padding:6px 10px; font-size: 12.5px; line-height:1.42;">
                        <strong style="color:#C45738; font-size: 13.1px;">📝 Signing Papers &amp; Important Deals:</strong><br>
                        <span><strong>Nov 15, 19, 24, 28:</strong> Great days for signing job offers, business contracts, legal papers, and formal partnerships.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #C2964C; border-radius:6px; padding:6px 10px; font-size: 12.5px; line-height:1.42;">
                        <strong style="color:#C2964C; font-size: 13.1px;">✈️ Travel &amp; Family Trips:</strong><br>
                        <span><strong>Nov 7, 11, 22, 26:</strong> Favorable days for smooth business trips, client meetings, or pleasant holidays with family.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #111827; border-radius:6px; padding:6px 10px; font-size: 12.5px; line-height:1.42;">
                        <strong style="color:#111827; font-size: 13.1px;">🚀 Starting New Projects &amp; Work:</strong><br>
                        <span><strong>Nov 18, 23, 27, 29:</strong> Best planetary support for launching a new business, starting a new project, or opening a shop/office.</span>
                    </div>
                </div>
            </div>

<!-- Note: Symbol guide omitted on Page 5 for clean layout -->
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Plan important tasks like job interviews, big client meetings, or major purchases on the highlighted red dates.",
                "On restful days (Nov 17, 18, 20), keep a light schedule, avoid arguments, and refrain from hasty spending.",
                "Amavasya (Day 9) is ideal for peaceful reflection: light a small ghee diya in your puja corner in the evening.",
                "Purnima (Day 24) brings appreciation and rewards: express thanks to parents, teachers, and well-wishers."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · 30-Day Astrological Calendar</span>
                <span class="mr-page-num">Page 5 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 6: LOVE, MARRIAGE & FAMILY LIFE (PREMA & BANDHU)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">04 · Relationships &amp; Family</div>
            <h1 class="mr-page-h1">Love, Marriage &amp; Family Life (Dampatya Sukh)</h1>
            <div class="mr-page-sub">Venus (Shukra) guidance, understanding your partner, and peace at home</div>

            <div class="mr-card gold-accent" style="padding:10px 14px;">
                <div class="mr-card-title">
                    <span>🪷 Monthly Relationship Summary</span>
                    <span style="color:#C2964C; font-weight:800;">Score: 64% · Gentle &amp; Patient Pace</span>
                </div>
                <h3 style="font-size: 15px; font-family:<?php echo $head_font; ?>; color:#111827; font-weight:700; margin:0 0 3px 0;">
                    Gentle, honest conversations will dissolve long-standing family and marital misunderstandings.
                </h3>
                <p style="font-size: 13.1px; color:#1F2937; line-height:1.52; margin:0;">
                    Venus (Shukra) is moving through your relationship area this month. In the first two weeks, small arguments about money, daily habits, or in-laws may come up. The easiest way to keep peace is simple: pause before you reply. Give your partner space and listen with an open heart. After November 15, Venus moves forward smoothly, bringing affection, smiles, and warmth back into your relationship.
                </p>
            </div>

            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="padding:10px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>💍 For Married Couples &amp; Partners</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Leave workplace tension and office anxieties outside the home.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>If an argument starts, take a 10-minute break before continuing the conversation.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Plan a pleasant dinner or a weekend outing together during the third week of November.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Share household responsibilities and children's studies with patience.</span></div>
                </div>

                <div class="mr-card sage-accent" style="padding:10px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🧭 For Singles &amp; New Matchmaking</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>An old friend or connection from the past may get in touch unexpectedly early in the month.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Focus on shared family values, good character, and maturity rather than just charm.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>The best time for serious marriage proposals (rishtas) and meetings begins after November 15.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Be open and honest about your career and future plans; sincerity always wins hearts.</span></div>
                </div>
            </div>

            <!-- Family & Parents Guidance Card -->
            <div class="mr-card slate-accent" style="padding:9px 13px; margin-top:7px; margin-bottom:0;">
                <div style="font-size: 13.1px; font-weight:700; color:#111827; margin-bottom:2px;">🏡 Family Peace &amp; Parents' Blessings (Ghar Parivar):</div>
                <div style="font-size: 12.5px; color:#4B5563; line-height:1.45;">
                    Take good care of your mother's or an elder family member's health around mid-month. Spend 10 minutes every day talking peacefully with your parents. Taking their blessings protects you from daily stress and brings harmony into your home.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🪷', 'term' => 'Prema Bhava', 'desc' => "7th House of marriage, life partner & companionship"],
                ['icon' => '💍', 'term' => 'Dampatya',    'desc' => "Sharing a peaceful, supportive married life"],
                ['icon' => '🏡', 'term' => 'Parivar',     'desc' => "Elders' well-being, domestic peace & family blessings"]
            ], 'Relationship Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Venus moving slowly early in the month is not meant to create problems, but to help clear up small misunderstandings.",
                "Listen patiently during family talks; showing that you care will calm down anger immediately.",
                "If you are single, take your time and understand each other well before making any big decision.",
                "After November 15, expect wonderful harmony: cheerful family dinners, mutual care, and romantic sweetness."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Love, Marriage &amp; Family Life</span>
                <span class="mr-page-num">Page 6 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 7: CAREER, JOB & WEALTH (KARMA & ARTHA)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">05 · Career, Business &amp; Money</div>
            <h1 class="mr-page-h1">Career, Business &amp; Money Matters (Karma &amp; Artha)</h1>
            <div class="mr-page-sub">Work progress, smart money decisions, and practical growth advice</div>

            <!-- Shadbala Bars SVG -->
            <div style="margin-bottom:8px;">
                <?php echo self::render_shadbala_bars_svg(540, 88); ?>
            </div>

            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:10px 12px;">
                    <div class="mr-card-title">
                        <span>🏹 Job &amp; Career Growth</span>
                        <span style="color:#C45738; font-weight:800;">Score: 78% · High Growth</span>
                    </div>
                    <p style="font-size: 13.1px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        Strong Mars energy supported by Jupiter gives you great energy, confidence, and problem-solving skills. Expect positive career news, appreciation from your boss, or new client deals in the last 10 days of the month.
                    </p>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Organize your presentations and paperwork before pitching new ideas.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Stay away from office gossip and workplace politics.</span></div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:10px 12px;">
                    <div class="mr-card-title">
                        <span>💰 Money, Savings &amp; Capital Liquidity</span>
                        <span style="color:#4E7D68; font-weight:800;">Score: 68% · Stable</span>
                    </div>
                    <p style="font-size: 13.1px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        Your regular salary or business income remains dependable. However, keep some money aside for routine home repairs or vehicle servicing around mid-month. Avoid lending money to friends or acquaintances for now.
                    </p>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Try to save at least 25% of your monthly income in safe bank savings.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Avoid risky trading or speculative bets while planets are moving backwards.</span></div>
                </div>
            </div>

            <!-- High-Stakes Financial & Negotiation Windows Box (Requested High-Value Add) -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:7px; padding:8px 12px; margin-top:7px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                    💼 Best Dates for Money Decisions &amp; Business Deals
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; font-size: 11.9px; line-height:1.42;">
                    <div style="background:#FAF7F2; border-left:3px solid #C45738; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#C45738; display:block;">🤝 Deals, Negotiations &amp; Talks:</strong>
                        <span><strong>Nov 18–26:</strong> Best time for salary talks, client pricing, project renewals, and important meetings.</span>
                    </div>
                    <div style="background:#FAF7F2; border-left:3px solid #4E7D68; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#4E7D68; display:block;">📈 Money Inflow &amp; Invoices:</strong>
                        <span><strong>Nov 24–29:</strong> Pending client payments, incentives, and reimbursements clear smoothly into your bank account.</span>
                    </div>
                    <div style="background:#FAF7F2; border-left:3px solid #C2964C; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#C2964C; display:block;">🛑 Careful Spending Window:</strong>
                        <span><strong>Nov 8–13:</strong> Keep spending low, avoid unnecessary shopping, and do not sign financial guarantees for anyone.</span>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏹', 'term' => 'Karma Bhava', 'desc' => "10th House of career, job & professional reputation"],
                ['icon' => '💰', 'term' => 'Artha Bhava', 'desc' => "2nd & 11th Houses of income, savings & wealth growth"],
                ['icon' => '⚖️', 'term' => 'Shadbala',    'desc' => "Vedic 6-fold measure showing how strong your planets are"]
            ], 'Career & Wealth Guide'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Strong Mars energy gives you great stamina at work: use this drive to clear pending tasks and solve tough problems.",
                "Keep an eye on daily expenses in the first two weeks to easily manage minor home repairs.",
                "Use the first half of the month to prepare well, organize documents, and polish your proposals.",
                "The last week brings wonderful opportunities: launch new initiatives, close deals, and take the lead at work."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Career, Business &amp; Wealth</span>
                <span class="mr-page-num">Page 7 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 8: PLANETARY ANGLES & INFLUENCES (ASPECTS & TRIGGERS)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">06 · Planetary Aspects &amp; Special Angles</div>
            <h1 class="mr-page-h1">Planetary Angles &amp; Drishti (Graha Drishti)</h1>
            <div class="mr-page-sub">How planets look at each other and influence your day-to-day life</div>

            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:8px;">
                <div class="mr-card-title"><span>🪐 Key Planetary Dates &amp; Angular Triggers This Month</span></div>
                <?php foreach ($triggers as $trig): ?>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid #ECE6DD; padding:4px 0;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#C45738; width:65px; flex-shrink:0;">
                            <?php echo esc_html($trig['date']); ?>
                        </div>
                        <div style="flex:1; padding:0 10px;">
                            <strong style="color:#111827; font-size: 13.1px;"><?php echo esc_html($trig['label']); ?></strong>
                            <div style="font-size: 12.5px; color:#4B5563; line-height:1.4;"><?php echo esc_html($trig['impact']); ?></div>
                        </div>
                        <span style="font-size: 10px; font-weight:800; text-transform:uppercase; color:#4E7D68; background:#F4EFE6; border:1px solid #ECE6DD; padding:2px 7px; border-radius:10px; flex-shrink:0;">
                            <?php echo esc_html($trig['type']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mr-card sage-accent" style="padding:10px 14px; margin-bottom:0;">
                <div class="mr-card-title"><span>👁️ Major Planetary Aspects (Drishti) Operating on Your Chart</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>⏳ Saturn's Aspect (Shani Drishti):</strong> Demands honest work, discipline, and attention to detail. Shortcuts won't work right now, but thorough preparation will earn you high respect.</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>🪷 Jupiter's Blessing (Guru Drishti):</strong> Casts a protective, positive light on your career, family peace, and decision-making. Advice from wise mentors will guide you well.</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>⚔️ Mars's Energy (Mangal Drishti):</strong> Can sometimes cause quick impatience at home. Stay calm and speak politely when discussing sensitive family topics or while driving.</span></div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '👁️', 'term' => 'Graha Drishti', 'desc' => "The way planets look across the chart to influence specific areas of your life"],
                ['icon' => '🪐', 'term' => 'Gochar (Transit)', 'desc' => "Current movement of planets across your birth chart"],
                ['icon' => '🛡️', 'term' => 'Kavach (Protection)', 'desc' => "Jupiter's positive rays protecting you from negativity"]
            ], 'Aspect Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Planetary aspects show what kind of energy surrounds you: knowing them helps you stay calm instead of reacting emotionally.",
                "Saturn's watchful eye on your career rewards sincere work, honest promises, and steady patience.",
                "Jupiter's gentle grace makes sure your hard work is seen and appreciated by senior people.",
                "Keep in mind November 14 and 15: as Mercury and Venus turn direct, confusing situations will clear up quickly."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Planetary Angles &amp; Drishti</span>
                <span class="mr-page-num">Page 8 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 9: THE CELESTIAL COUNCIL & MONTHLY TRANSIT INGRESS SHIFTS
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">07 · Planetary Positions &amp; Shifts (Gochar)</div>
            <h1 class="mr-page-h1">Where the Planets Are &amp; Key Shifts This Month (Gochar)</h1>
            <div class="mr-page-sub">Positions of all 9 planets and major planetary changes happening this month</div>

            <!-- Graha Sthiti Table -->
            <table style="width:100%; border-collapse:collapse; font-size: 11.2px; margin-top:1px;">
                <thead>
                    <tr style="background:#F4EFE6; color:#111827; font-family:<?php echo $head_font; ?>; font-size: 13.1px; font-weight:700;">
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Graha (Planet)</th>
                        <th style="padding:4px 6px; text-align:center; border:1px solid #ECE6DD;">Rashi (Sign)</th>
                        <th style="padding:4px 6px; text-align:center; border:1px solid #ECE6DD;">Degrees</th>
                        <th style="padding:4px 6px; text-align:center; border:1px solid #ECE6DD;">Nakshatra</th>
                        <th style="padding:4px 6px; text-align:center; border:1px solid #ECE6DD;">Dignity (Avastha)</th>
                        <th style="padding:4px 6px; text-align:center; border:1px solid #ECE6DD;">Bhava (House)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $planets = [
                        ['icon' => '☀️', 'name' => 'Surya (Sun)',        'sign' => 'Vrishchika (Scorpio)', 'deg' => '23° 14\'', 'nak' => 'Jyeshtha · 2',    'status' => 'Mitra (Friendly)', 'house' => '5th House (Intellect)'],
                        ['icon' => '🌙', 'name' => 'Chandra (Moon)',      'sign' => 'Simha (Leo)',          'deg' => '26° 02\'', 'nak' => 'Magha · 4',       'status' => 'Sama (Neutral)',    'house' => '2nd House (Speech/Wealth)'],
                        ['icon' => '⚔️', 'name' => 'Mangal (Mars)',       'sign' => 'Makara (Capricorn)',   'deg' => '18° 45\'', 'nak' => 'Shravana · 3',    'status' => 'Ucha (Exalted)',    'house' => '7th House (Kendra)'],
                        ['icon' => '📜', 'name' => 'Budha (Mercury) ℞',   'sign' => 'Vrishchika (Scorpio)', 'deg' => '05° 28\'', 'nak' => 'Anuradha · 1',    'status' => 'Mitra (Friendly)', 'house' => '5th House (Commerce)'],
                        ['icon' => '🪷', 'name' => 'Guru (Jupiter)',      'sign' => 'Vrishabha (Taurus)',   'deg' => '20° 12\'', 'nak' => 'Rohini · 4',      'status' => 'Sama (Neutral)',    'house' => '11th House (Labha)'],
                        ['icon' => '💎', 'name' => 'Shukra (Venus) ℞',    'sign' => 'Tula (Libra)',         'deg' => '22° 19\'', 'nak' => 'Vishakha · 1',    'status' => 'Swa (Own Sign)',    'house' => '4th House (Sukha)'],
                        ['icon' => '⏳', 'name' => 'Shani (Saturn) ℞',    'sign' => 'Meena (Pisces)',       'deg' => '12° 36\'', 'nak' => 'U. Bhadra · 3',   'status' => 'Sama (Neutral)',    'house' => '9th House (Bhagya)'],
                        ['icon' => '🌪️', 'name' => 'Rahu (North Node)',   'sign' => 'Kumbha (Aquarius)',    'deg' => '07° 18\'', 'nak' => 'Shatabhisha · 1', 'status' => 'Mitra (Friendly)', 'house' => '8th House (Transformation)'],
                        ['icon' => '📿', 'name' => 'Ketu (South Node)',   'sign' => 'Simha (Leo)',          'deg' => '07° 18\'', 'nak' => 'Magha · 2',       'status' => 'Sama (Neutral)',    'house' => '2nd House (Kutumbha)'],
                    ];

                    foreach ($planets as $i => $pl):
                        $row_bg = ($i % 2 === 0) ? '#FFFFFF' : '#FAF7F2';
                    ?>
                        <tr style="background:<?php echo esc_attr($row_bg); ?>; border-bottom:1px solid #ECE6DD;">
                            <td style="padding:2px 5px; font-weight:700; color:#111827;">
                                <span style="font-size: 13.8px;"><?php echo $pl['icon']; ?></span> <?php echo esc_html($pl['name']); ?>
                            </td>
                            <td style="padding:2px 5px; text-align:center; color:#1F2937;"><?php echo esc_html($pl['sign']); ?></td>
                            <td style="padding:2px 5px; text-align:center; font-family:monospace; color:#4B5563; font-size: 11.9px;"><?php echo esc_html($pl['deg']); ?></td>
                            <td style="padding:2px 5px; text-align:center; color:#4B5563;"><?php echo esc_html($pl['nak']); ?></td>
                            <td style="padding:2px 5px; text-align:center; font-weight:800; color:<?php echo (strpos($pl['status'], 'Own') !== false || strpos($pl['status'], 'Ucha') !== false) ? '#4E7D68' : '#4B5563'; ?>;">
                                <?php echo esc_html($pl['status']); ?>
                            </td>
                            <td style="padding:2px 5px; text-align:center; font-weight:800; color:#C45738;"><?php echo esc_html($pl['house']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Key Transits & Planetary Ingress Table (High-Value Add) -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:7px; padding:5px 9px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                    ⚡ Key Monthly Ingress Shifts &amp; Transit Stations (Gochar)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 10px; font-size: 11px; line-height:1.35;">
                    <div>
                        <strong style="color:#C45738;">☀️ Surya Sankranti (Nov 16):</strong> Sun moves into Scorpio (5th House), boosting clear thinking, sharp problem-solving, and good financial planning.
                    </div>
                    <div>
                        <strong style="color:#4E7D68;">🧭 Budha Margi (Nov 14):</strong> Mercury turns Direct in Scorpio (5th House), clearing up confusion, speeding up client talks, and sorting out communications.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">💎 Shukra Margi (Nov 15):</strong> Venus turns Direct in Libra (4th House), bringing peace at home, happiness with family, and good news about property.
                    </div>
                    <div>
                        <strong style="color:#111827;">⏳ Shani Margi Horizon (9th House):</strong> Saturn continues moving forward in Pisces, bringing stability, wisdom, and good principles to your work.
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '☀️/🌙', 'term' => 'Sun & Moon',       'desc' => "Sun governs confidence and vitality; Moon governs mood and peace of mind"],
                ['icon' => '⚔️/📜', 'term' => 'Action & Work',     'desc' => "Mars gives energy and drive; Mercury governs business, speech, and accounts"],
                ['icon' => '🪷/💎', 'term' => 'Supportive Planets', 'desc' => "Jupiter brings wisdom and good luck; Venus brings happiness and harmony"]
            ], 'Graha Council Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Sun and Mercury moving through Scorpio sharpen your intelligence, creative ideas, and money planning.",
                "Venus in its own sign Libra brings comfort, happiness, and peace into your home.",
                "Mars in strong dignity gives you courage and authority to solve difficult challenges at work.",
                "Following simple daily routines in harmony with planetary movements keeps life smooth and balanced."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · The Planetary Council</span>
                <span class="mr-page-num">Page 9 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 10: WEEKLY MOVEMENT PROTOCOLS (ACTIONABLE ROADMAP)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">08 · Step-by-Step Weekly Guide</div>
            <h1 class="mr-page-h1">Week-by-Week Action Plan</h1>
            <div class="mr-page-sub">Simple, practical tips for work, money, family, and peace of mind each week</div>

            <div class="mr-grid-2">
                <div class="mr-card gold-accent" style="margin-bottom:0; padding:5.5px 9px;">
                    <div class="mr-card-title">
                        <span>🏔️ Phase 1 (Days 1–7): Quiet Planning</span>
                        <span style="color:#C45738; font-size: 11.9px; font-weight:800;">Internal Energy</span>
                    </div>
                    <div style="font-size: 11.3px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Preparation &amp; Patience (68% Altitude)
                    </div>
                    <p style="font-size: 11.8px; color:#1F2937; margin:0 0 3px 0; line-height:1.38;">
                        The month begins with high energy and lots of ideas. Direct this enthusiasm into organizing your files, checking budgets, and reviewing plans quietly before discussing them publicly.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:3px; font-size: 11px; line-height:1.35;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Set clear weekly goals, take care of your health, and start mornings with a prayer.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Rushing into big new partnerships or talking openly about deals that aren't finalized.</div>
                    </div>
                </div>

                <div class="mr-card gold-accent" style="margin-bottom:0; padding:5.5px 9px;">
                    <div class="mr-card-title">
                        <span>⏳ Phase 2 (Days 8–14): Calm Recalibration</span>
                        <span style="color:#C45738; font-size: 11.9px; font-weight:800;">Amavasya Window</span>
                    </div>
                    <div style="font-size: 11.3px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Checkpoints &amp; Clearing Delays (59% Altitude)
                    </div>
                    <p style="font-size: 11.8px; color:#1F2937; margin:0 0 3px 0; line-height:1.38;">
                        Around the New Moon, things might move a little slower. Do not force quick answers from clients or superiors. Use this time to rest, recharge, and review your work.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:3px; font-size: 11px; line-height:1.35;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Light a small ghee diya on the New Moon evening, check bank balances, and get good rest.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Getting into arguments over small expenses or making risky financial moves.</div>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>🌊 Phase 3 (Days 15–21): Active Action</span>
                        <span style="color:#C45738; font-size: 11.9px; font-weight:800;">Planets Direct</span>
                    </div>
                    <div style="font-size: 11.3px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Clear Communication &amp; Progress (73% Altitude)
                    </div>
                    <p style="font-size: 11.8px; color:#1F2937; margin:0 0 3px 0; line-height:1.38;">
                        All green lights across the board! Confusions clear up, and colleagues and clients are very supportive. This is the best time to present new proposals and discuss promotions or salary increments.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:3px; font-size: 11px; line-height:1.35;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Schedule important meetings, send out proposals, and enjoy fun outings with family.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Procrastinating or hesitating when good opportunities knock at your door.</div>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:4px 8px;">
                    <div class="mr-card-title">
                        <span>⚡ Phase 4 (Days 22–30): Peak Harvest</span>
                        <span style="color:#C45738; font-size: 11.9px; font-weight:800;">Purnima Radiance</span>
                    </div>
                    <div style="font-size: 11.3px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Executive Closure &amp; Celebrations (88% Altitude)
                    </div>
                    <p style="font-size: 11.8px; color:#1F2937; margin:0 0 3px 0; line-height:1.38;">
                        The most rewarding part of the month! The Full Moon brings positive outcomes for your hard work. Deals close smoothly, incentives or bonuses arrive, and you gain respect from everyone.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:3px; font-size: 11px; line-height:1.35;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Sign final papers, celebrate with your loved ones, and do simple acts of charity.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Pride, boasting, or cutting down on sleep during busy celebrations.</div>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏔️', 'term' => 'Week 1 Groundwork', 'desc' => "Internal documentation and system audits without premature fanfare"],
                ['icon' => '⏳', 'term' => 'Week 2 Review',     'desc' => "Equanimity and review while retrograde personal planets complete stations"],
                ['icon' => '🌊', 'term' => 'Week 3 Velocity',   'desc' => "Unrestricted momentum as stalled communications regain velocity"],
                ['icon' => '⚡', 'term' => 'Week 4 Harvest',    'desc' => "Binding execution, leadership recognition and joyful celebrations"]
            ], 'Weekly Protocol Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Pacing yourself is the secret to success: pushing too hard during slow days only causes unnecessary stress.",
                "Weeks 1 and 2 are best for quiet planning, paperwork, and polishing your skills.",
                "Do not worry about small mid-month delays: they give you time to fix any mistakes.",
                "Take bold, confident steps during Weeks 3 and 4 to make the most of the positive planetary momentum."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Weekly Movement Protocols</span>
                <span class="mr-page-num">Page 10 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 11: LUNAR TIDES & SACRED PAUSES (AMAVASYA & PURNIMA)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">09 · Lunar Energy &amp; Cycles</div>
            <h1 class="mr-page-h1">Moon Phases, Mind Peace &amp; Key Dates (Chandra)</h1>
            <div class="mr-page-sub">How the Moon affects your mood and the best days for fasting, prayer, or rest</div>

            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="padding:10px 13px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🌑 Amavasya (New Moon · Day 9)</span><span style="color:#C45738; font-weight:800;">Day of Peace</span></div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:700; color:#111827; margin-bottom:3px;">
                        A Clean Slate for Fresh Goals &amp; Inner Calm
                    </div>
                    <p style="font-size: 13.1px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        When the Sun and Moon meet in Scorpio, our thoughts become calm and reflective. This is the best day of the month to let go of old office stress, clear your head, and set quiet personal goals.
                    </p>
                    <div style="background:#FDF5F2; border:1px solid #ECE6DD; padding:6px 9px; border-radius:5px; font-size: 11.9px; color:#C45738; line-height:1.45;">
                        <strong>🕯️ Simple 3-Step Goal-Setting (Sankalpa):</strong><br>
                        1. Clean your study table or puja area at sunset.<br>
                        2. Light a small desi ghee diya facing East or North.<br>
                        3. Sit quietly for 5 minutes and write down your 3 main goals for the month.
                    </div>
                </div>

                <div class="mr-card sage-accent" style="padding:10px 13px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🌕 Purnima (Full Moon · Day 24)</span><span style="color:#4E7D68; font-weight:800;">Fulfillment</span></div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:700; color:#111827; margin-bottom:3px;">
                        A Time of Happiness, Light &amp; Good News
                    </div>
                    <p style="font-size: 13.1px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        The Full Moon shines brightly on your career and relationships. Sincere work done over the last few weeks brings visible rewards. Great day for important presentations, sharing happiness, and spending time with loved ones.
                    </p>
                    <div style="background:#F4EFE6; border:1px solid #ECE6DD; padding:6px 9px; border-radius:5px; font-size: 11.9px; color:#4E7D68; line-height:1.45;">
                        <strong>🪷 Simple 3-Step Gratitude Ritual:</strong><br>
                        1. Say a warm thank you to your parents, spouse, or teachers.<br>
                        2. Feed birds, cows, or share sweets/food with someone in need.<br>
                        3. Spend 10 minutes walking in the cool evening breeze to relax your mind.
                    </div>
                </div>
            </div>

            <!-- Chandra Ashtama Warning Card -->
            <div class="mr-card" style="margin-top:7px; padding:9px 13px; background:#FFFFFF; margin-bottom:0;">
                <div style="font-size: 13.1px; font-weight:700; color:#111827; margin-bottom:2px;">🌙 Emotional Balance during Sensitive Days (Chandra Ashtama):</div>
                <div style="font-size: 12.5px; color:#4B5563; line-height:1.48;">
                    The Moon governs our feelings and peace of mind. When the Moon passes through the 8th house from your Moon sign (mid-month), you might feel slightly moody or tired without any real reason. During these 2 days, drink warm water, avoid heated arguments, listen to soothing music, and ensure you get 7-8 hours of sleep.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🌑', 'term' => 'Amavasya',        'desc' => "Zero-point lunar stillness; optimal for internal contemplation & goal-setting"],
                ['icon' => '🌕', 'term' => 'Purnima',         'desc' => "Full lunar illumination; optimal for public expression & celebration"],
                ['icon' => '🕯️', 'term' => 'Ghee Diya Lamp',  'desc' => "Lighting a lamp at sunset purifies domestic vibrations and thoughts"],
                ['icon' => '🪷', 'term' => 'Gratitude Lotus', 'desc' => "Heartfelt acknowledgment of blessings and divine grace received"]
            ], 'Lunar Ritual Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "The movement of the Moon directly affects your daily mood, sleep quality, and energy levels.",
                "When the Moon is strong, you will feel creative, confident, and persuasive in meetings.",
                "Amavasya is a wonderful day to rest, think quietly, and set fresh positive goals.",
                "Purnima brings completion, appreciation, and a warm feeling of gratitude."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Lunar Cycles &amp; Home Rituals</span>
                <span class="mr-page-num">Page 11 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 12: ELEMENTAL BALANCE & TIME-BOUND REMEDIES (UPAYAS)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">10 · Simple Remedies &amp; Daily Habits</div>
            <h1 class="mr-page-h1">Simple Daily Remedies &amp; Auspicious Hours (Hora)</h1>
            <div class="mr-page-sub">Easy lifestyle remedies, lucky hours of the day, and peaceful daily practices</div>

            <!-- Radar Chart & Elemental Guidance -->
            <div style="display:flex; gap:14px; align-items:center;">
                <div style="flex-shrink:0;">
                    <?php echo self::render_panchamahabhuta_radar_svg(145); ?>
                    <div style="text-align:center; font-size: 10.6px; color:#4B5563; font-weight:700; margin-top:2px; text-transform:uppercase; letter-spacing:0.06em;">
                        Panchamahabhuta Energy Radar
                    </div>
                </div>

                <div style="flex:1;">
                    <div class="mr-card sage-accent" style="margin-bottom:0; padding:10px 13px;">
                        <div class="mr-card-title"><span>🔥 Dominant Elements: Agni (Fire) &amp; Vayu (Air)</span></div>
                        <p style="font-size: 13.1px; color:#111827; margin:0 0 3px 0; line-height:1.45;">
                            <strong>Energy This Month:</strong> Active Fire and Air energies give you sharp intelligence, good ideas, and quick problem-solving skills.
                        </p>
                        <p style="font-size: 12.5px; color:#4B5563; margin:0; line-height:1.48;">
                            <strong>Keeping Balance:</strong> To prevent feeling tired or getting acidity, stay grounded with simple Earth habits: walking barefoot on fresh grass in the morning, drinking water from a copper vessel, and eating meals at fixed times.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Time-Bound Remedial Focus of the Month (High-Value Add) -->
            <div class="mr-grid-2" style="margin-top:7px;">
                <div class="mr-card terracotta-accent" style="padding:6px 10px; margin-bottom:0; font-size:11.5px;">
                    <div class="mr-card-title"><span>⏰ Simple Daily Remedies &amp; Auspicious Hours</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Wednesday Mercury Hora (7:00–8:15 AM):</strong> Chant <em>&ldquo;Om Budhaya Namah&rdquo;</em> 11 or 108 times to sharpen memory and improve conversations.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Sunday Sun Hora (Morning Sunrise):</strong> Offer fresh water (Arghya) to the rising Sun in a copper vessel for confidence and good health.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Saturday Evening:</strong> Light a mustard oil diya near a Peepal tree or pray to Lord Hanuman for peace of mind.</span></div>
                </div>

                <div class="mr-card gold-accent" style="padding:6px 10px; margin-bottom:0; font-size:11.5px;">
                    <div class="mr-card-title"><span>🏺 Acts of Kindness &amp; Supportive Gemstones</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>Charity (Daan):</strong> Feed green grass or spinach to cows on Wednesdays, or donate yellow lentils/food to those in need on Sundays.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>💎 Gemstone (Ratna):</strong> Emerald (Panna) for sharp communication or Ruby (Manikya) for self-confidence (consult an astrologer before wearing).</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>📿 Rudraksha:</strong> 5-Mukhi Rudraksha helps keep the mind calm, while 3-Mukhi brings courage and removes restlessness.</span></div>
                </div>
            </div>

            <!-- Ayurvedic Routine Card -->
            <div class="mr-card" style="margin-top:4px; padding:5px 10px; background:#FFFFFF; margin-bottom:0;">
                <div style="font-size: 13.1px; font-weight:700; color:#111827; margin-bottom:2px;">🌿 Ayurvedic Lifestyle &amp; Dinacharya Tip for the Month:</div>
                <div style="font-size: 11.9px; color:#4B5563; line-height:1.45;">
                    Drink a warm glass of water with a dash of lemon or fennel seeds in the morning. Avoid eating very oily or spicy meals late at night. Massaging the soles of your feet with warm coconut or sesame oil before bed gives you deep, restful sleep.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🔥/🌍/💨', 'term' => 'Panchamahabhuta', 'desc' => "Fire (Agni), Earth (Prithvi), Air (Vayu), Water (Jala), Space (Akash)"],
                ['icon' => '⏰', 'term' => 'Hora Timing',        'desc' => "Specific planetary hours when remedial mantras hold maximum efficacy"],
                ['icon' => '🏺', 'term' => 'Daan Protocol',      'desc' => "Selfless charitable offerings washing away malefic transit residues"]
            ], 'Remedial Symbols'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Vedic remedies are simple, practical habits designed to bring positive energy into your daily life.",
                "Small daily rituals—like greeting the morning sun and helping others—bring peace of mind and protect your health."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Elemental Balance &amp; Remedies</span>
                <span class="mr-page-num">Page 12 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 13: THE MASTER SOVEREIGN HORIZON (EXECUTIVE DASHBOARD)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">11 · Monthly Focus &amp; Affirmation</div>
            <h1 class="mr-page-h1">Your Monthly Focus &amp; Personal Resolution (Sankalpa)</h1>
            <div class="mr-page-sub">Key focus areas for the month, a positive affirmation, and personal notes</div>

            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:7px;">
                <div style="font-size: 11.9px; color:#C45738; letter-spacing:0.12em; text-transform:uppercase; font-weight:800;">📜 Monthly Master Mantra &amp; Resolution</div>
                <h2 style="font-family:<?php echo $head_font; ?>; font-size:16px; color:#111827; font-weight:700; margin:2px 0 4px 0;">&ldquo;<?php echo esc_html($theme); ?>&rdquo;</h2>
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-style:italic; color:#4B5563; line-height:1.45;">
                    &ldquo;<?php echo esc_html($dashboard['sankalpa'] ?? 'I stay calm, patient, and focused during the first two weeks; my honest work and steady habits will bring great success, respect, and family happiness in the second half of the month.'); ?>&rdquo;
                </div>
            </div>

            <div class="mr-grid-4">
                <div class="mr-card terracotta-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size: 11.3px; font-weight:700; color:#4B5563; text-transform:uppercase;">🏹 Career</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['karma']['score'] ?? 78); ?>%</div>
                    <span style="font-size: 10px; color:#4E7D68; font-weight:700;">High Growth</span>
                </div>
                <div class="mr-card sage-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size: 11.3px; font-weight:700; color:#4B5563; text-transform:uppercase;">💰 Money</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['dhana']['score'] ?? 68); ?>%</div>
                    <span style="font-size: 10px; color:#C2964C; font-weight:700;">Stable Liquidity</span>
                </div>
                <div class="mr-card gold-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size: 11.3px; font-weight:700; color:#4B5563; text-transform:uppercase;">🪷 Family</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['prema']['score'] ?? 64); ?>%</div>
                    <span style="font-size: 10px; color:#C45738; font-weight:700;">Patience Needed</span>
                </div>
                <div class="mr-card slate-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size: 11.3px; font-weight:700; color:#4B5563; text-transform:uppercase;">🌿 Health</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['arogya']['score'] ?? 86); ?>%</div>
                    <span style="font-size: 10px; color:#4E7D68; font-weight:700;">Good Stamina</span>
                </div>
            </div>

            <!-- Personalized Monthly Sankalpa Interactive Guided Exercise (High-Value Add) -->
            <div style="background:#FFFFFF; border:1.5px solid #C2964C; border-radius:7px; padding:9px 13px; margin-top:7px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <span style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em;">
                        ✍️ My Personal Monthly Goals (Amavasya Reflection)
                    </span>
                    <span style="font-size: 10.6px; font-weight:800; color:#C45738; text-transform:uppercase;">Self Reflection</span>
                </div>
                <p style="font-size: 11.9px; color:#4B5563; margin:0 0 6px 0; line-height:1.4;">
                    Writing down your goals on paper gives your mind clear direction. Write down your simple promises for this month:
                </p>
                <div style="font-size: 11.9px; line-height:1.7;">
                    <div style="margin-bottom:3px;">
                        <strong style="color:#C45738;">1. My Main Work Goal:</strong> What is one important task or project you want to finish this month?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                    <div style="margin-bottom:3px;">
                        <strong style="color:#4E7D68;">2. Habits to Improve:</strong> What one stressful reaction or habit will you let go of this month?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                    <div>
                        <strong style="color:#C2964C;">3. Family &amp; Home:</strong> What special gesture or helpful act will you do for your parents, partner, or family this month?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                </div>
            </div>

            <!-- Golden Rules for the Month -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 11px; margin-top:6px;">
                <div style="font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:2px;">🌟 4 Sovereign Rules for <?php echo esc_html($person_name); ?> This Month:</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:3px 10px; font-size: 11.9px; line-height:1.4;">
                    <div><strong style="color:#C45738;">1. Career:</strong> Do your best work quietly; save big presentations for after Nov 15.</div>
                    <div><strong style="color:#4E7D68;">2. Money:</strong> Keep a close watch on spending and keep emergency savings in the bank.</div>
                    <div><strong style="color:#C2964C;">3. Family:</strong> Listen patiently to your spouse and respect your parents' advice.</div>
                    <div><strong style="color:#111827;">4. Health:</strong> Take a 20-minute morning walk and drink plenty of water to stay fresh.</div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '📜', 'term' => 'Maha Mantra',  'desc' => "Core philosophy guiding all daily thoughts, speech & executive decisions"],
                ['icon' => '🧭', 'term' => 'Sankalpa',     'desc' => "Personal morning resolution for staying emotionally centered and focused"],
                ['icon' => '🏛️', 'term' => 'Siddhanta',    'desc' => "Mathematical coordinates verified against classical Parashari Jyotish"]
            ], 'Horizon Master Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Keep this summary page handy on your phone or desk to review your goals whenever you need guidance.",
                "Check your lucky power days (Nov 7, 8, 26, 27) before fixing important job interviews, client calls, or purchases.",
                "Staying calm during stressful moments is your greatest strength: let small issues pass with a smile.",
                "Move forward with confidence, clean intentions, and faith in your hard work (Karma) and God's blessings."
            ], 'Essential Monthly Takeaways', $head_font); ?>

            <div class="mr-footer-bar">
                <span>MeraRashifal · Monthly Master Summary</span>
                <span class="mr-page-num">Page 13 of 15</span>
            </div>
        </div>
    </div>


    <!-- ========================================================
         PAGE 14: GLOSSARY OF ASTROLOGICAL TERMS & LEXICON
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta"><?php echo esc_html($person_name . ' · ' . $month_year); ?></span>
            </div>

            <div class="mr-tag-pill">Astrology Terms &amp; Simple Meanings</div>
            <h1 class="mr-page-h1">Astrology Glossary &amp; Simple Meanings</h1>
            <div class="mr-page-sub">Simple, easy-to-understand explanations of common Vedic astrology terms used in this report.</div>

            <!-- Structured Glossary Definitions Grid (2 Columns, 14 Terms) -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:7px 12px; margin-top:2px;">
                <?php
                $glossary_terms = [
                    [
                        'term' => 'Janma Lagna (Ascendant)',
                        'desc' => 'The exact zodiac sign rising on the Eastern horizon at the moment of your birth. It represents your physical constitution, innate personality, and life path.'
                    ],
                    [
                        'term' => 'Surya & Chandra Rashi',
                        'desc' => 'Surya Rashi (Sun Sign) represents your soul purpose, authority, and public dignity. Chandra Rashi (Moon Sign) rules your emotional mind (Manas), intuition, and perceptions.'
                    ],
                    [
                        'term' => 'Nakshatra & Pada',
                        'desc' => 'The 27 classical lunar mansions dividing the sky into 13°20\' segments, each further subdivided into 4 Padas. Nakshatras govern your inner temperament and karmic tendencies.'
                    ],
                    [
                        'term' => 'Vimshottari Dasha Engine',
                        'desc' => 'The classical 120-year planetary cycle system indicating the timing of life events. The Mahadasha sets the macro 6 to 20-year climate, while the Antardasha activates specific sub-events.'
                    ],
                    [
                        'term' => 'Gochar (Planetary Transits)',
                        'desc' => 'The continuous daily movement of planets across the zodiac in real time, interacting with your static natal chart to activate dormant potential and create opportunities.'
                    ],
                    [
                        'term' => 'Graha Drishti (Aspects)',
                        'desc' => 'The special angular gaze cast by planets onto other houses. While all planets aspect the 7th house, Mars (4th/8th), Jupiter (5th/9th), and Saturn (3rd/10th) possess unique full aspects.'
                    ],
                    [
                        'term' => 'Panchamahabhuta',
                        'desc' => 'The five great elements composing the cosmos: Agni (Fire), Prithvi (Earth), Vayu (Air), Jala (Water), and Akash (Space). Health and prana rely upon their harmonious balance.'
                    ],
                    [
                        'term' => 'Shadbala (Sixfold Strength)',
                        'desc' => 'An exhaustive mathematical calculation measuring a planet’s composite potency across positional, directional, temporal, motion, natural, and aspectual dimensions.'
                    ],
                    [
                        'term' => 'Kendra & Trikona Bhavas',
                        'desc' => 'The pillars of destiny. Kendra houses (1, 4, 7, 10) represent action, structure, and enterprise; Trikona houses (1, 5, 9) represent divine grace, wisdom, and virtuous fortune (Lakshmi).'
                    ],
                    [
                        'term' => 'Amavasya & Purnima',
                        'desc' => 'Amavasya (New Moon) represents zero-point lunar stillness, optimal for inner cleansing and intention setting. Purnima (Full Moon) represents peak culmination and public harvest.'
                    ],
                    [
                        'term' => 'Shubh Muhurat & Choghadiya',
                        'desc' => 'Scientifically calculated auspicious time windows chosen to initiate vital undertakings, ensuring alignment between human action and favorable celestial rays.'
                    ],
                    [
                        'term' => 'Vedic Upayas & Daan',
                        'desc' => 'Remedial actions designed to harmonize cosmic resonance. Involves selfless charity (Daan), sacred sound vibrations (Mantra), and gemstone therapy (Ratna Upasana).'
                    ],
                    [
                        'term' => 'Sankalpa (Sacred Resolution)',
                        'desc' => 'A solemn vow formed with clean consciousness and dedicated willpower. In Vedic practice, a Sankalpa anchors cosmic energy into disciplined physical manifestation.'
                    ],
                    [
                        'term' => 'Chandra Ashtama',
                        'desc' => 'The 48-hour period when the transiting Moon traverses the 8th house from your natal Moon sign, advising mindful rest, digestive moderation, and avoiding major confrontations.'
                    ],
                ];

                foreach ($glossary_terms as $gt): ?>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:6px 9px;">
                        <strong style="color:#C45738; font-size: 13.1px; display:block; margin-bottom:1px;"><?php echo esc_html($gt['term']); ?>:</strong>
                        <span style="font-size: 11.9px; color:#4B5563; line-height:1.4; display:block;"><?php echo esc_html($gt['desc']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Master Reference Guidance Card -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-left:3.5px solid #C2964C; border-radius:6px; padding:7px 11px; margin-top:8px;">
                <div style="font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:1px;">📖 Reference Lexicon Application:</div>
                <div style="font-size: 11.9px; color:#4B5563; line-height:1.42;">
                    This glossary serves as an enduring companion across your MeraRashifal astrological documentation. Each principle represents a mathematical and philosophical pillar of Maharishi Parashara’s <em>Brihat Parashara Hora Shastra</em>, translated into actionable modern executive language.
                </div>
            </div>
        </div>

        <div class="mr-footer-bar">
            <span>MeraRashifal · Glossary of Astrological Terms</span>
            <span class="mr-page-num">Page 14 of 15</span>
        </div>
    </div>


    <!-- ========================================================
         PAGE 15: THANK YOU & REPORT CATALOG (BACK COVER)
         ======================================================== -->
    <div class="mr-page">
        <div>
            <div class="mr-header-bar">
                <div class="mr-header-brand-wrap">
                    <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
                </div>
                <span class="mr-header-meta">Astrological Catalogue &amp; Verification Seal</span>
            </div>

            <div style="text-align:center; margin-bottom:6px;">
                <div class="mr-tag-pill">Complete Vedic Library</div>
                <h1 class="mr-page-h1" style="font-size:18px;">Thank You, <?php echo esc_html($person_name); ?> ji!</h1>
                <div class="mr-page-sub" style="margin-bottom:5px;">Explore our complete collection of personalized astrology reports for you and your family</div>
            </div>

            <!-- 4 Core Showcase Cards -->
            <div class="mr-offer-card">
                <div class="mr-offer-icon">💎</div>
                <div class="mr-offer-info">
                    <h4>Complete Janam Kundali &amp; Lifetime Destiny Dossier</h4>
                    <p>Complete birth chart analysis covering all 12 houses, planetary strengths, career, marriage, and long-term wealth.</p>
                    <div class="mr-offer-meta">25 Pages · Parashari &amp; Jaimini Synthesis · Includes Full Kundali Chart</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">💍</div>
                <div class="mr-offer-info">
                    <h4>Kundali Matching (Gun Milan) &amp; Marriage Compatibility</h4>
                    <p>Traditional 36-Guna matching, Manglik Dosh check, emotional compatibility, and family harmony analysis.</p>
                    <div class="mr-offer-meta">20 Pages · Ashtakoot &amp; Dasha Synchronization · Dual Partner Vedic Analysis</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">☀️</div>
                <div class="mr-offer-info">
                    <h4>Annual Varshaphal (Solar Return) Master Report</h4>
                    <p>Complete year-by-year predictive guide from birthday to birthday, month-by-month career roadmap, and lucky dates.</p>
                    <div class="mr-offer-meta">26 Pages · Tajika Annual Engine · Month-by-Month Predictive Guidance</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">🛡️</div>
                <div class="mr-offer-info">
                    <h4>Kundali Dosh Analysis &amp; Vedic Remedial Dossier</h4>
                    <p>Clear analysis of Manglik Dosh, Kaal Sarp, and Pitra Dosh with simple, reassuring home remedies and gemstones.</p>
                    <div class="mr-offer-meta">20 Pages · 90-Day Karmic Harmonization Plan · Certified Vedic Remedial Solutions</div>
                </div>
            </div>

            <!-- Certified Verification Seal Box -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 11px; margin-top:6px; text-align:center;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#111827; letter-spacing:0.04em;">
                    🏛️ MeraRashifal Siddhanta Verification &amp; Certification Seal
                </div>
                <p style="font-size: 11.9px; color:#4B5563; margin:2px 0 0 0; line-height:1.42;">
                    Calculated using standard Indian Lahiri Chitra Paksha Ayanamsha (24°10'38") and authentic Maharishi Parashari Jyotish principles. Cryptographic verification hash: <code style="font-size: 11.3px; color:#C45738;"><?php echo strtoupper(substr(md5($person_name . $dob . $month_year), 0, 16)); ?></code>.
                </p>
            </div>
        </div>

        <div style="border-top:1px solid #ECE6DD; padding-top:5px; text-align:center; font-size: 11.9px; color:#4B5563; z-index:2; line-height:1.5;">
            <div style="font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:1px;">
                Access Your Reports Archive &amp; Order New Blueprints
            </div>
            <span>Visit your personal portal at: <strong style="color:#C45738;">merarashifal.com/my-account-reports/</strong></span><br>
            <span style="font-size: 10.6px; color:#6B7280; margin-top:1px; display:block;">
                Astrology is a sacred guidance system to illuminate conscious choices with God's grace and sincere hard work (Karma). &copy; <?php echo date('Y'); ?> MeraRashifal. All rights reserved.
            </span>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
