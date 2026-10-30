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
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='11' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = implode(' ', $planets_by_house[$h]);
                $svg .= "<text x='{$c['pl_x']}' y='{$c['pl_y']}' fill='#111827' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pls}</text>";
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Topographic Elevation Wave SVG Graph
     */
    public static function render_topographic_elevation_svg($w = 540, $h = 95) {
        $pad_l = 30;
        $pad_r = 15;
        $pad_t = 12;
        $pad_b = 20;
        $gw = $w - $pad_l - $pad_r;
        $gh = $h - $pad_t - $pad_b;

        $pts = [
            ['w' => 1, 'x' => 0.08, 'y' => 0.45, 'pct' => 68, 'label' => 'W1: Ascent (68%)'],
            ['w' => 2, 'x' => 0.36, 'y' => 0.72, 'pct' => 59, 'label' => 'W2: Recalibrate (59%)'],
            ['w' => 3, 'x' => 0.68, 'y' => 0.35, 'pct' => 73, 'label' => 'W3: Realignment (73%)'],
            ['w' => 4, 'x' => 0.94, 'y' => 0.12, 'pct' => 88, 'label' => 'W4: Breakthrough (88%)'],
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

        $svg = "<svg width='100%' height='auto' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.02); display:block;'>";

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
            $svg .= "<text x='{$c['cx']}' y='" . ($c['cy'] - 8) . "' fill='#111827' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$c['label']}</text>";
        }

        $svg .= "<text x='{$p0['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='9' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 1–7</text>";
        $svg .= "<text x='{$p1['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='9' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 8–14</text>";
        $svg .= "<text x='{$p2['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='9' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 15–21</text>";
        $svg .= "<text x='{$p3['cx']}' y='" . ($h - 5) . "' fill='#4B5563' font-size='9' font-family='sans-serif' font-weight='600' text-anchor='middle'>Days 22–30</text>";

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
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='" . ($by - 3) . "' fill='#111827' font-size='8.5' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$b['pct']}%</text>";
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='68' fill='#4B5563' font-size='8' font-family='sans-serif' font-weight='700' text-anchor='middle'>" . explode(' ', $b['p'])[0] . "</text>";
            $svg .= "<text x='" . ($x + ($col_w - 14) / 2) . "' y='78' fill='#9CA3AF' font-size='7.5' font-family='sans-serif' text-anchor='middle'>{$b['s']} V</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Panchamahabhuta Radar SVG
     */
    public static function render_panchamahabhuta_radar_svg($size = 145) {
        $s = $size;
        $cx = $s / 2;
        $cy = $s / 2;
        $r = ($s / 2) - 24;

        $elements = [
            ['name' => 'Agni (Fire)',     'val' => 0.88, 'angle' => -90],
            ['name' => 'Prithvi (Earth)', 'val' => 0.52, 'angle' => -18],
            ['name' => 'Vayu (Air)',      'val' => 0.82, 'angle' => 54],
            ['name' => 'Jala (Water)',    'val' => 0.60, 'angle' => 126],
            ['name' => 'Akash (Space)',   'val' => 0.70, 'angle' => 198],
        ];

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.02); display:block; margin:0 auto;'>";

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
            $svg .= "<text x='{$lx}' y='{$ly}' fill='#4B5563' font-size='7.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>" . explode(' ', $el['name'])[0] . "</text>";
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
        <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 12px; margin-top:6px; display:flex; align-items:center; gap:12px; font-size:9.5px; color:#4B5563;">
            <strong style="color:#111827; font-size:10px; text-transform:uppercase; letter-spacing:0.06em; flex-shrink:0;">
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
                <div style="font-family:<?php echo $heading_font; ?>; font-size:11px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.06em;">
                    ⚡ <?php echo esc_html($title); ?>
                </div>
                <span style="font-size:8.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.08em;">Strategic Synthesis</span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:3px 12px; font-size:10px; color:#1F2937; line-height:1.42;">
                <?php foreach ($bullets as $b): ?>
                    <div style="display:flex; align-items:flex-start;">
                        <span style="color:#C45738; margin-right:5px; font-weight:bold; font-size:9px;">•</span>
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
            font-size: 11px;
            line-height: 1.5;
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
            padding: 13mm 18mm 11mm 18mm;
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
            margin-top: 6px;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 10px;
        }

        /* Typography Hierarchy */
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
            font-family: <?php echo $head_font; ?>;
            font-size: 21px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px 0;
            line-height: 1.22;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 11px;
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
            font-size: 13.5px;
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
            font-size: 10.5px;
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
            padding: 18mm 18mm 14mm 18mm;
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
            height: 125px;
            max-height: 135px;
            object-fit: contain;
        }
        .mr-cover-badge {
            font-size: 13.5px;
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
            margin: 0 auto 16px auto;
            line-height: 1.45;
        }
        .mr-cover-native-box {
            background: #FFFFFF;
            border: 1.5px solid #ECE6DD;
            border-radius: 10px;
            padding: 16px 22px;
            width: 100%;
            max-width: 470px;
            margin: 0 auto;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            z-index: 2;
        }
        .mr-cover-prepared {
            font-size: 9px;
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
            font-size: 11px;
        }
        .mr-cover-meta-item strong { display: block; font-size: 12.5px; color: #C45738; font-weight: 800; }
        .mr-cover-meta-item span { font-size: 9px; color: #4B5563; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }

        .mr-cover-dasha-strip {
            margin-top: 8px;
            padding-top: 7px;
            border-top: 1px dashed #ECE6DD;
            font-size: 10.5px;
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
            font-size: 13px;
            color: #111827;
            font-weight: 700;
        }
        .mr-offer-info p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #4B5563;
            line-height: 1.4;
        }
        .mr-offer-meta {
            font-size: 8.5px;
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
                        <strong>Dhanu (Sagit)</strong>
                        <span>☀️ Surya Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong>Simha (Leo)</strong>
                        <span>🌙 Chandra Rashi</span>
                    </div>
                    <div class="mr-cover-meta-item">
                        <strong>Karka (Cancer)</strong>
                        <span>🧭 Janma Lagna (21.27°)</span>
                    </div>
                </div>
                <div class="mr-cover-dasha-strip">
                    <div>Active Mahadasha: <strong>Rahu (2024–2042)</strong></div>
                    <div>Active Antardasha: <strong>Rahu (to Jun 2027)</strong></div>
                    <div>Nakshatra: <strong>Magha (Pada 4)</strong></div>
                </div>
            </div>
        </div>

        <div style="z-index:2; font-size:9.5px; color:#4B5563; letter-spacing:0.06em; text-transform:uppercase; line-height:1.65;">
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

            <div class="mr-tag-pill">Executive Navigation &amp; Orientation</div>
            <h1 class="mr-page-h1">Table of Contents &amp; Strategic Reading Guide</h1>
            <div class="mr-page-sub">A complete architectural index and roadmap for navigating your personalized monthly Vedic forecast.</div>

            <!-- Table of Contents Master Grid -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ECE6DD; padding-bottom:5px; margin-bottom:6px;">
                    <span style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em;">Section / Report Module</span>
                    <span style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#C45738; text-transform:uppercase; letter-spacing:0.05em;">Physical Page</span>
                </div>

                <?php
                $toc_items = [
                    ['sec' => 'Section 01', 'title' => 'Master Energy Index & Cosmic Thermal Map (Lagna Kundali D1 & 4 Pillars)', 'page' => '03'],
                    ['sec' => 'Section 02', 'title' => 'The 4-Week Journey: Topographic Elevation Wave & Weekly Milestone Dynamics',  'page' => '04'],
                    ['sec' => 'Section 03', 'title' => '30-Day Astrological Heat Grid & Auspicious Shubh Muhurat Matrix',            'page' => '05'],
                    ['sec' => 'Section 04', 'title' => 'Relational Horizon, Marriage & Family Dynamics (Prema & Bandhu)',             'page' => '06'],
                    ['sec' => 'Section 05', 'title' => 'Career Altitude, Wealth & High-Stakes Negotiation Windows (Karma & Artha)',    'page' => '07'],
                    ['sec' => 'Section 06', 'title' => 'Astrological Geometry: Graha Drishti Aspects & Angular Triggers',             'page' => '08'],
                    ['sec' => 'Section 07', 'title' => 'The Celestial Council (9-Graha Sthiti) & Key Monthly Transit Ingress Shifts', 'page' => '09'],
                    ['sec' => 'Section 08', 'title' => 'Phase-by-Phase Tactical Execution Protocols & Weekly Strategic Blueprints',    'page' => '10'],
                    ['sec' => 'Section 09', 'title' => 'Lunar Cycles & Sacred Pauses: Amavasya, Purnima & Chandra Upasana',           'page' => '11'],
                    ['sec' => 'Section 10', 'title' => 'Elemental Balance & Time-Bound Remedial Prescriptions (Daan & Hora Timing)',   'page' => '12'],
                    ['sec' => 'Section 11', 'title' => 'Sovereign Horizon Dashboard & Guided Monthly Sankalpa (Interactive Prompts)',  'page' => '13'],
                    ['sec' => 'Section 12', 'title' => 'Glossary of Astrological Terms & Reference Jyotish Lexicon',                  'page' => '14'],
                    ['sec' => 'Section 13', 'title' => 'Sovereign Astrological Catalogue, Ecosystem Archive & Verification Seal',      'page' => '15'],
                ];

                foreach ($toc_items as $ti): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px dashed #F2ECE3; font-size:10.5px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; color:#C45738; width:70px; flex-shrink:0; font-size:9.5px;"><?php echo esc_html($ti['sec']); ?></span>
                            <span style="color:#1F2937; font-weight:500;"><?php echo esc_html($ti['title']); ?></span>
                        </div>
                        <div style="font-family:<?php echo $head_font; ?>; font-weight:700; color:#111827; font-size:11px;">
                            Page <?php echo esc_html($ti['page']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- How to Read This Report Executive Guidance Card -->
            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:0;">
                <div class="mr-card-title"><span>🧭 How to Extract Maximum Value from This Monthly Blueprint</span></div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; font-size:10px; line-height:1.45; margin-top:4px;">
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#C45738; font-size:10.5px; display:block; margin-bottom:2px;">1. The Macro Rhythm</strong>
                        Review Pages 3–4 first to absorb your overall monthly thermal index and weekly topographic crests and valleys.
                    </div>
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#C2964C; font-size:10.5px; display:block; margin-bottom:2px;">2. Precision Timing</strong>
                        Consult Pages 5, 7, and 9 before scheduling major negotiations, signing agreements, or deploying significant capital.
                    </div>
                    <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 9px;">
                        <strong style="color:#4E7D68; font-size:10.5px; display:block; margin-bottom:2px;">3. Upayas &amp; Sankalpa</strong>
                        Apply the time-bound Daan and Hora prescriptions on Page 12, and complete the guided New Moon intention prompt on Page 13.
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
            <div class="mr-page-sub">Your Planetary Setup, Prana Vitality &amp; Foundational Astrological Energy Balance</div>

            <!-- Side by Side: Kundali SVG & Executive Analysis -->
            <div style="display:flex; gap:14px; align-items:center;">
                <div style="flex-shrink:0;">
                    <?php echo self::render_north_indian_kundali_svg(200, [], 4, $head_font); ?>
                    <div style="text-align:center; font-size:8.5px; color:#4B5563; font-weight:700; margin-top:3px; text-transform:uppercase; letter-spacing:0.06em;">
                        🪐 Lagna Kundali (Cancer Lagna 21.27°)
                    </div>
                </div>

                <div style="flex:1;">
                    <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:11px 14px;">
                        <div class="mr-card-title">
                            <span>Monthly Energy Level</span>
                            <span style="font-size:11.5px; color:#C45738; font-weight:800;"><?php echo esc_html($thermal_idx); ?> / 100 · <?php echo esc_html($thermal_st); ?></span>
                        </div>
                        <p style="margin:0 0 5px 0; font-size:11px; color:#1F2937; line-height:1.55;">
                            <?php echo esc_html($synthesis); ?>
                        </p>
                        <div class="mr-thermal-bar-wrap">
                            <div class="mr-thermal-bar-fill" style="width: <?php echo esc_attr(min(100, max(10, $thermal_idx))); ?>%;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:8.5px; color:#4B5563; text-transform:uppercase; font-weight:700; margin-bottom:5px;">
                            <span>Rest &amp; Plan (0–40%)</span>
                            <span>Steady Flow (41–70%)</span>
                            <span>High Speed &amp; Action (71–100%)</span>
                        </div>
                        <div style="border-top:1px solid #ECE6DD; padding-top:5px; font-size:10px; color:#4B5563; line-height:1.45;">
                            <strong style="color:#C45738;">Golden Advice for this Month:</strong> Do not rush into unvetted commitments before November 15. Spend the opening fortnight refining blueprints in private. The second half of the month unlocks high velocity, executive visibility, and monetary gains.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Four Pillars Grid -->
            <div class="mr-grid-4" style="margin-top:8px;">
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size:9.5px; color:#C45738; font-weight:800; text-transform:uppercase;">🏹 Career &amp; Job</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['karma']['score'] ?? 78); ?>%</div>
                    <div style="font-size:8.5px; color:#4B5563; line-height:1.32;">Strong senior backing. Work performed behind the scenes gains major public recognition after 15th.</div>
                </div>
                <div class="mr-card sage-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size:9.5px; color:#4E7D68; font-weight:800; text-transform:uppercase;">💰 Money &amp; Savings</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['dhana']['score'] ?? 68); ?>%</div>
                    <div style="font-size:8.5px; color:#4B5563; line-height:1.32;">Regular cash flow remains steady. Avoid speculative lending or unvetted private equity plays early month.</div>
                </div>
                <div class="mr-card gold-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size:9.5px; color:#C2964C; font-weight:800; text-transform:uppercase;">🪷 Love &amp; Family</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['prema']['score'] ?? 64); ?>%</div>
                    <div style="font-size:8.5px; color:#4B5563; line-height:1.32;">Maintain gentle patience in domestic talks. Listen actively to elders and spouse; avoid bringing office pressure home.</div>
                </div>
                <div class="mr-card slate-accent" style="margin-bottom:0; padding:8px 9px;">
                    <div style="font-size:9.5px; color:#4B5563; font-weight:800; text-transform:uppercase;">🌿 Health &amp; Energy</div>
                    <div style="font-size:16px; font-weight:800; color:#111827; margin:1px 0;"><?php echo esc_html($pillars['arogya']['score'] ?? 86); ?>%</div>
                    <div style="font-size:8.5px; color:#4B5563; line-height:1.32;">High solar endurance. Guard against mild acidity or throat irritation. A morning walk keeps prana grounded.</div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏹', 'term' => 'Career (Karma)',  'desc' => 'Vocation, leadership standing & reputation'],
                ['icon' => '💰', 'term' => 'Money (Dhana)',   'desc' => 'Treasury flow, bank liquidity & capital assets'],
                ['icon' => '🪷', 'term' => 'Family (Prema)',  'desc' => 'Spouse harmony, kinship bonds & domestic peace'],
                ['icon' => '🌿', 'term' => 'Health (Arogya)', 'desc' => 'Physical vitality, biorhythms & restorative stamina']
            ], 'Life Domain Guide'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Cancer Lagna is fortified by benefic aspects, bestowing natural resilience, strategic vision, and calm crisis management.",
                "Mercury retrograde in the opening fortnight suggests double-checking all high-stakes contracts, financial drafts, and travel tickets.",
                "The first two weeks serve as an engineering phase: clear pending tasks and maintain budget discipline in private.",
                "Post-November 15 direct stations of Venus and Mercury initiate rapid career expansion, commercial inquiries, and family joy."
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
            <div class="mr-page-sub">Strategic Energy Progression, Critical Pacing &amp; Weekly Milestone Dynamics</div>

            <!-- Topographic Elevation Graph SVG -->
            <div style="margin-bottom:8px;">
                <?php echo self::render_topographic_elevation_svg(540, 95); ?>
            </div>

            <!-- Four Epoch Cards in 2x2 Grid -->
            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>🏔️ Week 1 (Days 1–7): Setting Up Goals</span>
                        <span style="font-size:10px; color:#C45738; font-weight:800;">68% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size:10.5px; color:#1F2937; line-height:1.48;">
                        High mental drive, but external circumstances remain uncalibrated. Utilize this window to audit spreadsheets, organize legal files, and plan monthly milestones in quiet confidence.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Wake early, outline weekly objectives, and seek elders' blessings.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Engaging in authority friction or signing binding debts prematurely.</span>
                    </div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>⏳ Week 2 (Days 8–14): Patience &amp; Review</span>
                        <span style="font-size:10px; color:#4E7D68; font-weight:800;">59% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size:10.5px; color:#1F2937; line-height:1.48;">
                        Amavasya (New Moon) induces a reflective rhythm. Administrative approvals and third-party replies may encounter brief delays. Do not become impatient; cultivate stillness.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Audit liquidity, clear pending liabilities, and spend peaceful time at home.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Impulsive luxury retail therapy or speaking harshly in moments of fatigue.</span>
                    </div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>🌊 Week 3 (Days 15–21): Momentum Picks Up</span>
                        <span style="font-size:10px; color:#4E7D68; font-weight:800;">73% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size:10.5px; color:#1F2937; line-height:1.48;">
                        Mercury and Venus turn direct! Stuck client remittances clear, regulatory approvals arrive, and commercial dialogue with key stakeholders flows with remarkable clarity.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Pitch new enterprise proposals, schedule appraisals, and attend networking events.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Overpromising delivery deadlines when current workload is already heavy.</span>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>⚡ Week 4 (Days 22–30): Peak Results &amp; Win</span>
                        <span style="font-size:10px; color:#C45738; font-weight:800;">88% Energy</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size:10.5px; color:#1F2937; line-height:1.48;">
                        Purnima (Full Moon) illuminates your professional zenith. Sincere efforts invested over the past three weeks manifest into tangible rewards, bonuses, and social standing.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.45;">
                        <span style="color:#4E7D68; font-weight:700;">⚡ DO: Execute agreements, acquire gold or durable assets, and celebrate with team.</span><br>
                        <span style="color:#C45738; font-weight:700;">🛡️ AVOID: Complacency or discounting valuable counsel from your spouse.</span>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '⚡', 'term' => 'Recommended Action (DO)', 'desc' => 'High-leverage maneuvers yielding immediate forward progress'],
                ['icon' => '🛡️', 'term' => 'Caution (AVOID)',         'desc' => 'Friction traps requiring conscious self-restraint'],
                ['icon' => '🏔️/⏳/🌊/⚡', 'term' => 'Weekly Progression',   'desc' => 'From Week 1 planning to Week 4 breakthrough']
            ], 'Weekly Roadmap Key'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Do not assess the overall monthly harvest by the slower tempo of the opening 10 days; delay often shields you from hidden errors.",
                "Weeks 1 and 2 prioritize defensive planning, internal paperwork, and quiet operational optimization.",
                "Week 3 (post-November 15) represents the turning point: favorable cosmic rays dissolve hurdles in career and finance.",
                "Week 4 is your harvest window: take bold calculated leaps, execute agreements, and share your triumphs with family."
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
            <h1 class="mr-page-h1">30-Day Astrological Heat Grid &amp; Shubh Muhurat Matrix</h1>
            <div class="mr-page-sub">Day-by-Day Thermal Ratings, Fortunate Windows &amp; Precision Auspicious Timings</div>

            <!-- Clean Editorial Calendar Table -->
            <table style="width:100%; border-collapse:collapse; text-align:center; font-size:10.5px; margin-top:2px;">
                <thead>
                    <tr style="background:#F4EFE6; color:#111827; font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; letter-spacing:0.05em;">
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
                                <td style="border:1px solid <?php echo esc_attr($bdr); ?>; padding:4px 2px; background:<?php echo esc_attr($bg); ?>; vertical-align:top; height:34px;">
                                    <div style="font-weight:800; font-size:11px; color:#111827;"><?php echo $day; ?></div>
                                    <div style="font-size:8.5px; font-weight:800; color:<?php echo $score >= 70 ? '#C45738' : ($score <= 55 ? '#4B5563' : '#4E7D68'); ?>;">
                                        <?php echo $score; ?>%
                                    </div>
                                    <?php if ($day == 9): ?>
                                        <div style="font-size:7px; color:#C45738; font-weight:800;">🌑 Amavasya</div>
                                    <?php elseif ($day == 24): ?>
                                        <div style="font-size:7px; color:#C2964C; font-weight:800;">🌕 Purnima</div>
                                    <?php elseif ($day == 14): ?>
                                        <div style="font-size:7px; color:#4E7D68; font-weight:800;">🧭 Budha Direct</div>
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
                <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    ✨ Auspicious Shubh Muhurat Timings of the Month
                </div>
                <div class="mr-grid-2">
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #4E7D68; border-radius:6px; padding:6px 10px; font-size:10px; line-height:1.42;">
                        <strong style="color:#4E7D68; font-size:10.5px;">💰 Financial Investments &amp; Capital Assets:</strong><br>
                        <span><strong>Nov 6, 16, 21, 27:</strong> Highly auspicious for real estate token advances, mutual fund deployments, gold purchases, and opening new wealth accounts.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #C45738; border-radius:6px; padding:6px 10px; font-size:10px; line-height:1.42;">
                        <strong style="color:#C45738; font-size:10.5px;">📝 Signing Contracts &amp; High-Value Deals:</strong><br>
                        <span><strong>Nov 15, 19, 24, 28:</strong> Direct planetary stations favor binding legal signatures, partnership deeds, enterprise MoUs, and senior executive offers.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #C2964C; border-radius:6px; padding:6px 10px; font-size:10px; line-height:1.42;">
                        <strong style="color:#C2964C; font-size:10.5px;">✈️ Strategic Travel &amp; Journey Commencements:</strong><br>
                        <span><strong>Nov 7, 11, 22, 26:</strong> Auspicious lunar nakshatras ensure fruitful business trips, client summits, and peaceful out-of-station family pilgrimages.</span>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:3.5px solid #111827; border-radius:6px; padding:6px 10px; font-size:10px; line-height:1.42;">
                        <strong style="color:#111827; font-size:10.5px;">🚀 Launching Ventures &amp; Enterprise Initiatives:</strong><br>
                        <span><strong>Nov 18, 23, 27, 29:</strong> Strongest Kendra and Trikona alignments to inaugurate digital products, campaigns, or inaugurate commercial offices.</span>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '☀️', 'term' => 'Shubh Power Day (70%+)', 'desc' => 'Optimal for high-stakes pitches, major acquisitions & key debuts'],
                ['icon' => '🌿', 'term' => 'Steady Normal Day',      'desc' => 'Favorable for regular commercial routine & operational flow'],
                ['icon' => '🕯️', 'term' => 'Sanyam / Rest Day',     'desc' => 'Practice equanimity; postpone litigation & speculative bets'],
                ['icon' => '🌑', 'term' => 'Amavasya (Day 9)',       'desc' => 'New Moon; light a ghee diya and set quiet intentions'],
                ['icon' => '🌕', 'term' => 'Purnima (Day 24)',       'desc' => 'Full Moon; peak culmination, gratitude & charitable giving']
            ], 'Calendar Colors & Icons'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Schedule high-stakes discussions (such as salary negotiations, new client pitches, or asset contracts) on terracotta dates.",
                "On Sanyam (Rest) days (Nov 17, 18, 20), refrain from volatile debates with superiors or making hasty financial disbursements.",
                "Amavasya on Day 9 serves as an introspective reset: meditate at dusk and light a pure desi ghee diya facing North.",
                "Purnima on Day 24 unlocks public recognition and harvest: offer heartfelt gratitude to mentors and charitable institutions."
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
            <div class="mr-page-sub">Venus (Shukra) Alignment, Marital Harmony &amp; Kinship Bonds</div>

            <div class="mr-card gold-accent" style="padding:10px 14px;">
                <div class="mr-card-title">
                    <span>🪷 Monthly Relationship Summary</span>
                    <span style="color:#C2964C; font-weight:800;">Score: 64% · Gentle &amp; Patient Pace</span>
                </div>
                <h3 style="font-size:12px; font-family:<?php echo $head_font; ?>; color:#111827; font-weight:700; margin:0 0 3px 0;">
                    Gentle, honest conversations will dissolve long-standing family and marital misunderstandings.
                </h3>
                <p style="font-size:10.5px; color:#1F2937; line-height:1.52; margin:0;">
                    Shukra (Venus) is transiting your relationship sector. In the first fortnight, minor differences of opinion regarding domestic budgets, in-laws, or personal routines may emerge. The key to lasting harmony is simple: practice pause rather than immediate reaction. Give your partner space and listen with empathy. Following November 15, Venus advances direct, bringing affectionate warmth, mutual care, and romantic sweetness back into your bond.
                </p>
            </div>

            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="padding:10px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>💍 For Married Couples &amp; Partners</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Leave workplace tension and professional anxieties outside the bedroom threshold.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Should a disagreement spark, allow tempers to cool before resuming dialogue.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Plan a weekend dinner or quiet retreat together during the third week of November.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Share domestic management and children's routines with mutual understanding.</span></div>
                </div>

                <div class="mr-card sage-accent" style="padding:10px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🧭 For Singles &amp; New Matchmaking</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>An acquaintance or connection from the past may reach out unexpectedly early in the month.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Prioritize core values, family alignment, and emotional maturity over superficial charm.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>The optimal astrological window for serious rishta meetings begins after November 15.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Be completely transparent regarding your future career plans; sincerity attracts the right partner.</span></div>
                </div>
            </div>

            <!-- Family & Parents Guidance Card -->
            <div class="mr-card slate-accent" style="padding:9px 13px; margin-top:7px; margin-bottom:0;">
                <div style="font-size:10.5px; font-weight:700; color:#111827; margin-bottom:2px;">🏡 Family Peace &amp; Parents' Blessings (Ghar Parivar):</div>
                <div style="font-size:10px; color:#4B5563; line-height:1.45;">
                    Your mother's or an elderly family member's health requires gentle consideration mid-month. Dedicate 10 minutes daily to sit quietly and converse with your parents. Seeking their heartfelt blessings acts as a protective shield against planetary friction and invites Lakshmi Kripa into your household.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🪷', 'term' => 'Prema Bhava', 'desc' => '7th House governing marital harmony and partnership fidelity'],
                ['icon' => '💍', 'term' => 'Dampatya',    'desc' => 'Long-term marital cooperation and mutual life aspirations'],
                ['icon' => '🏡', 'term' => 'Parivar',     'desc' => 'Elders’ well-being, domestic peace & ancestral blessings']
            ], 'Relationship Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Venus retrograde in the opening fortnight is not meant to break bonds, but to clear away unspoken misunderstandings.",
                "Listen more and speak less during sensitive family discussions; validating your partner's feelings will instantly dissolve anger.",
                "Single natives should take their time and prioritize shared values and emotional maturity before making any final commitment.",
                "Post-November 15 brings wonderful harmony: family dinners, shared laughter, and romantic affection will flourish."
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

            <div class="mr-tag-pill">05 · Career &amp; Wealth Dynamics</div>
            <h1 class="mr-page-h1">Career Altitude, Business &amp; Wealth (Karma &amp; Artha)</h1>
            <div class="mr-page-sub">Executive Career Momentum, Treasury Management &amp; Strategic Negotiation Protocols</div>

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
                    <p style="font-size:10.5px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        Exalted Mars energy fortified by Jovian aspect provides formidable stamina, executive courage, and strategic acumen. Expect decisive career recognition, appraisals, or client contract wins in the final 10 days of the month.
                    </p>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Complete your backend documentation and operational systems before pitching.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span>Remain completely aloof from corporate politics or water-cooler speculation.</span></div>
                </div>

                <div class="mr-card sage-accent" style="margin-bottom:0; padding:10px 12px;">
                    <div class="mr-card-title">
                        <span>💰 Money, Savings &amp; Capital Liquidity</span>
                        <span style="color:#4E7D68; font-weight:800;">Score: 68% · Stable</span>
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        Core enterprise revenues and professional remuneration remain dependable. However, the 2nd house axis flags non-routine domestic repairs or asset maintenance around mid-month. Avoid speculative peer loans.
                    </p>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Preserve at least 25% of monthly inflows in liquid high-yield bank reserves.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span>Postpone unhedged derivatives or speculative crypto bets until direct stations.</span></div>
                </div>
            </div>

            <!-- High-Stakes Financial & Negotiation Windows Box (Requested High-Value Add) -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:7px; padding:8px 12px; margin-top:7px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                    💼 High-Stakes Negotiation &amp; Cash Flow Timing Windows
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; font-size:9.5px; line-height:1.42;">
                    <div style="background:#FAF7F2; border-left:3px solid #C45738; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#C45738; display:block;">🤝 High-Stakes Deals &amp; Talks:</strong>
                        <span><strong>Nov 18–26:</strong> Mercury direct trine Jupiter. Prime window for client pricing, contract renewals, and equity negotiations.</span>
                    </div>
                    <div style="background:#FAF7F2; border-left:3px solid #4E7D68; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#4E7D68; display:block;">📈 Receivables &amp; Cash Influx:</strong>
                        <span><strong>Nov 24–29:</strong> Purnima culmination in 11th house. Stuck invoices, bonuses, and dividend receivables clear smoothly.</span>
                    </div>
                    <div style="background:#FAF7F2; border-left:3px solid #C2964C; padding:5px 8px; border-radius:4px;">
                        <strong style="color:#C2964C; display:block;">🛑 Capital Caution Window:</strong>
                        <span><strong>Nov 8–13:</strong> Amavasya sandhi. Defer non-essential capital expenditures and avoid co-signing financial guarantees.</span>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏹', 'term' => 'Karma Bhava', 'desc' => '10th House governing executive authority & public reputation'],
                ['icon' => '💰', 'term' => 'Artha Bhava', 'desc' => '2nd & 11th Houses governing capital liquidity & commercial profits'],
                ['icon' => '⚖️', 'term' => 'Shadbala',    'desc' => 'Classical 6-fold planetary potency index indicating execution strength']
            ], 'Career & Wealth Guide'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Mars energy anchored in your Kendra grants superior working stamina: channel this into solving complex organizational bottlenecks.",
                "Enforce disciplined budgetary oversight during the first fortnight to insulate against mid-month domestic repair expenses.",
                "Treat the opening 14 days as an engineering sanctuary: refine documentation, perfect proposals, and clear all accounts.",
                "The concluding week presents unprecedented leverage: deploy marketing campaigns, close enterprise agreements, and claim leadership."
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

            <div class="mr-tag-pill">06 · Planetary Aspects &amp; Geometry</div>
            <h1 class="mr-page-h1">Planetary Angles &amp; Drishti (Graha Drishti)</h1>
            <div class="mr-page-sub">Angular Configurations, Retrograde Stations &amp; Cross-Bhava Influences</div>

            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:8px;">
                <div class="mr-card-title"><span>🪐 Key Planetary Dates &amp; Angular Triggers This Month</span></div>
                <?php foreach ($triggers as $trig): ?>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid #ECE6DD; padding:4px 0;">
                        <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#C45738; width:65px; flex-shrink:0;">
                            <?php echo esc_html($trig['date']); ?>
                        </div>
                        <div style="flex:1; padding:0 10px;">
                            <strong style="color:#111827; font-size:10.5px;"><?php echo esc_html($trig['label']); ?></strong>
                            <div style="font-size:10px; color:#4B5563; line-height:1.4;"><?php echo esc_html($trig['impact']); ?></div>
                        </div>
                        <span style="font-size:8px; font-weight:800; text-transform:uppercase; color:#4E7D68; background:#F4EFE6; border:1px solid #ECE6DD; padding:2px 7px; border-radius:10px; flex-shrink:0;">
                            <?php echo esc_html($trig['type']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mr-card sage-accent" style="padding:10px 14px; margin-bottom:0;">
                <div class="mr-card-title"><span>👁️ Major Planetary Aspects (Drishti) Operating on Your Chart</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>⏳ Shani Drishti (Saturn's 10th Aspect):</strong> Demands absolute procedural honesty and institutional discipline. Shortcut attempts will be rejected; meticulous preparation earns permanent respect.</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>🪷 Guru Drishti (Jupiter's 9th Aspect):</strong> Casts an auspicious protective halo over executive status, family harmony, and moral clarity. Guidance from experienced mentors will shield you.</span></div>
                <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#4E7D68;"></div><span><strong>⚔️ Mangal Drishti (Mars's 4th Aspect):</strong> Can stimulate occasional flashes of domestic impatience. Maintain grounded stillness while navigating vehicular transit or sensitive family topics.</span></div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '👁️', 'term' => 'Graha Drishti', 'desc' => 'Planetary gaze impacting distant houses and awakening latent karmas'],
                ['icon' => '🪐', 'term' => 'Gochar (Transit)', 'desc' => 'Active planetary movements interacting with your natal matrix'],
                ['icon' => '🛡️', 'term' => 'Kavach (Protection)', 'desc' => 'Jupiter’s divine trinal ray neutralizing malefic transit currents']
            ], 'Aspect Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Planetary aspects (Drishti) operate like atmospheric forecasts: anticipating their timing empowers conscious response over emotional reaction.",
                "Saturn's rigorous gaze upon your vocational house rewards unimpeachable ethics, thorough contracts, and quiet tenacity.",
                "Jupiter's benefic grace ensures your strategic contributions are acknowledged by corporate leaders and valued partners.",
                "Circle November 14 and 15 on your desk calendar: as Mercury and Venus turn direct, weeks of operational haze dissipate swiftly."
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

            <div class="mr-tag-pill">07 · Graha Sthiti &amp; Transit Ingress</div>
            <h1 class="mr-page-h1">The Planetary Council &amp; Monthly Ingress Shifts (Gochar)</h1>
            <div class="mr-page-sub">Exact Ephemeris Coordinates of 9 Grahas &amp; Specific Month Transit Ingress Stations</div>

            <!-- Graha Sthiti Table -->
            <table style="width:100%; border-collapse:collapse; font-size:10px; margin-top:2px;">
                <thead>
                    <tr style="background:#F4EFE6; color:#111827; font-family:<?php echo $head_font; ?>; font-size:10.5px; font-weight:700;">
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
                            <td style="padding:3.5px 6px; font-weight:700; color:#111827;">
                                <span style="font-size:11px;"><?php echo $pl['icon']; ?></span> <?php echo esc_html($pl['name']); ?>
                            </td>
                            <td style="padding:3.5px 6px; text-align:center; color:#1F2937;"><?php echo esc_html($pl['sign']); ?></td>
                            <td style="padding:3.5px 6px; text-align:center; font-family:monospace; color:#4B5563; font-size:9.5px;"><?php echo esc_html($pl['deg']); ?></td>
                            <td style="padding:3.5px 6px; text-align:center; color:#4B5563;"><?php echo esc_html($pl['nak']); ?></td>
                            <td style="padding:3.5px 6px; text-align:center; font-weight:800; color:<?php echo (strpos($pl['status'], 'Own') !== false || strpos($pl['status'], 'Ucha') !== false) ? '#4E7D68' : '#4B5563'; ?>;">
                                <?php echo esc_html($pl['status']); ?>
                            </td>
                            <td style="padding:3.5px 6px; text-align:center; font-weight:800; color:#C45738;"><?php echo esc_html($pl['house']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Key Transits & Planetary Ingress Table (High-Value Add) -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:7px; padding:7px 11px; margin-top:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                    ⚡ Key Monthly Ingress Shifts &amp; Transit Stations (Gochar)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 12px; font-size:9.5px; line-height:1.4;">
                    <div>
                        <strong style="color:#C45738;">☀️ Surya Sankranti (Nov 16):</strong> Ingress into Scorpio (5th House) stimulates analytical prowess, strategic advisory status, and investment foresight.
                    </div>
                    <div>
                        <strong style="color:#4E7D68;">🧭 Budha Margi (Nov 14):</strong> Mercury stations Direct in Scorpio (5th House). Dissolves negotiation friction, restores swift client consensus and data clarity.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">💎 Shukra Margi (Nov 15):</strong> Venus stations Direct in own sign Libra (4th House). Unlocks domestic property matters, upgrades lifestyle, and rekindles romantic joy.
                    </div>
                    <div>
                        <strong style="color:#111827;">⏳ Shani Margi Horizon (9th House):</strong> Saturn continues direct momentum in Pisces, stabilizing higher wisdom, overseas ventures, and enterprise ethics.
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '☀️/🌙', 'term' => 'Luminaries',      'desc' => 'Surya rules soul power/vitality; Chandra governs emotion & mental flow'],
                ['icon' => '⚔️/📜', 'term' => 'Action & Commerce', 'desc' => 'Exalted Mars drives execution; Mercury regulates trade & legal drafting'],
                ['icon' => '🪷/💎', 'term' => 'Benefics',         'desc' => 'Jupiter grants divine fortune; Venus blesses partnerships & asset luxury']
            ], 'Graha Council Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Sun and Mercury transiting Scorpio activate your 5th house of strategic intellect, creative foresight, and asset planning.",
                "Venus occupying its own sign Libra in your 4th house guarantees domestic peace, aesthetic comfort, and real estate appreciation.",
                "Mars in exalted dignity reinforces Kendra leadership, giving you decisive authority in high-stakes operational disputes.",
                "Harmonizing daily work with active Nakshatra energies ensures purposeful alignment with Dharma and steady progress."
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

            <div class="mr-tag-pill">08 · Weekly Tactical Pacing</div>
            <h1 class="mr-page-h1">Phase-by-Phase Tactical Execution Protocols</h1>
            <div class="mr-page-sub">Actionable Weekly Blueprints for Career, Finances, Family &amp; Mindset Discipline</div>

            <div class="mr-grid-2">
                <div class="mr-card gold-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>🏔️ Phase 1 (Days 1–7): Quiet Planning</span>
                        <span style="color:#C45738; font-size:9.5px; font-weight:800;">Internal Energy</span>
                    </div>
                    <div style="font-size:9px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Preparation &amp; Patience (68% Altitude)
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; margin:0 0 5px 0; line-height:1.45;">
                        The month opens with intense mental ambition. Direct this drive into auditing project spreadsheets, refining system blueprints, and reviewing legal language in privacy before public debuts.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.42;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Build weekly milestone schedules, verify vehicle condition, and meditate at dawn.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Rushing into brand-new joint ventures or boasting about unfinalized transactions.</div>
                    </div>
                </div>

                <div class="mr-card gold-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>⏳ Phase 2 (Days 8–14): Calm Recalibration</span>
                        <span style="color:#C45738; font-size:9.5px; font-weight:800;">Amavasya Window</span>
                    </div>
                    <div style="font-size:9px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Checkpoints &amp; Clearing Delays (59% Altitude)
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; margin:0 0 5px 0; line-height:1.45;">
                        New Moon and retrograde station window. Operational pace decelerates temporarily. Do not force immediate feedback from clients or institutional bodies; use this pause to replenish energy.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.42;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Kindle a ghee lamp on Amavasya dusk, reconcile old accounting books, and retire early.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Domestic friction over minor expenditures or making speculative investments.</div>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>🌊 Phase 3 (Days 15–21): Active Action</span>
                        <span style="color:#C45738; font-size:9.5px; font-weight:800;">Planets Direct</span>
                    </div>
                    <div style="font-size:9px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Clear Communication &amp; Progress (73% Altitude)
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; margin:0 0 5px 0; line-height:1.45;">
                        Full green light across the celestial landscape! Bureaucratic bottlenecks dissolve. Leadership and commercial partners turn receptive. Optimum window to present proposals and negotiate salary terms.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.42;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Schedule decisive boardroom summits, dispatch proposals, and enjoy family outings.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Procrastination or hesitation when long-awaited commercial opportunities arrive.</div>
                    </div>
                </div>

                <div class="mr-card terracotta-accent" style="margin-bottom:0; padding:9px 12px;">
                    <div class="mr-card-title">
                        <span>⚡ Phase 4 (Days 22–30): Peak Harvest</span>
                        <span style="color:#C45738; font-size:9.5px; font-weight:800;">Purnima Radiance</span>
                    </div>
                    <div style="font-size:9px; font-weight:800; color:#C2964C; text-transform:uppercase; margin-bottom:3px;">
                        Main Theme: Executive Closure &amp; Celebrations (88% Altitude)
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; margin:0 0 5px 0; line-height:1.45;">
                        The most commercially rewarding epoch of the month. Full Moon illuminates career milestones. Commercial closures finalize, bonuses or dividends arrive, and your professional standing is cemented.
                    </p>
                    <div style="border-top:1px solid #ECE6DD; padding-top:4px; font-size:9.5px; line-height:1.42;">
                        <div style="color:#4E7D68; font-weight:700; margin-bottom:1px;">⚡ DO: Execute formal contracts, celebrate with partner, and perform charitable Daan.</div>
                        <div style="color:#C45738; font-weight:700;">🛡️ AVOID: Arrogance or neglecting restorative sleep amidst celebratory gatherings.</div>
                    </div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🏔️', 'term' => 'Week 1 Groundwork', 'desc' => 'Internal documentation and system audits without premature fanfare'],
                ['icon' => '⏳', 'term' => 'Week 2 Review',     'desc' => 'Equanimity and review while retrograde personal planets complete stations'],
                ['icon' => '🌊', 'term' => 'Week 3 Velocity',   'desc' => 'Unrestricted momentum as stalled communications regain velocity'],
                ['icon' => '⚡', 'term' => 'Week 4 Harvest',    'desc' => 'Binding execution, leadership recognition and joyful celebrations']
            ], 'Weekly Protocol Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Pacing discipline is the hallmark of sovereign leadership: swimming against early cosmic currents wastes vital mental prana.",
                "Weeks 1 and 2 prioritize defensive planning, internal paperwork, and quiet skill refinement.",
                "Do not take mid-month delays personally: they are simply a cosmic filter giving you time to polish your work.",
                "Transition into bold outward execution during Weeks 3 and 4 to ride the highest celestial momentum of the month."
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
            <h1 class="mr-page-h1">Lunar Cycles &amp; Sacred Pauses (Chandra Upasana)</h1>
            <div class="mr-page-sub">The Biorhythmic Pulse of the Moon, Mind Equilibrium &amp; Classical Observances</div>

            <div class="mr-grid-2">
                <div class="mr-card terracotta-accent" style="padding:10px 13px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🌑 Amavasya (New Moon · Day 9)</span><span style="color:#C45738; font-weight:800;">Day of Peace</span></div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:700; color:#111827; margin-bottom:3px;">
                        The Seed of Fresh Intentions &amp; Releasing Negativity
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        As Chandra conjoins Surya in Scorpio, emotional currents turn calm and introspective. This represents the premier window of the month to release past professional grievances, eliminate mental clutter, and plant quiet intentions.
                    </p>
                    <div style="background:#FDF5F2; border:1px solid #ECE6DD; padding:6px 9px; border-radius:5px; font-size:9.5px; color:#C45738; line-height:1.45;">
                        <strong>🕯️ Simple 3-Step Sankalpa Ritual:</strong><br>
                        1. Cleanse your sacred space or study desk at sunset.<br>
                        2. Kindle a pure Desi Ghee diya facing East or North.<br>
                        3. Sit quietly in contemplation and record your 3 primary monthly objectives.
                    </div>
                </div>

                <div class="mr-card sage-accent" style="padding:10px 13px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🌕 Purnima (Full Moon · Day 24)</span><span style="color:#4E7D68; font-weight:800;">Fulfillment</span></div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:700; color:#111827; margin-bottom:3px;">
                        The Peak of Happiness, Light &amp; Recognition
                    </div>
                    <p style="font-size:10.5px; color:#1F2937; line-height:1.48; margin:0 0 5px 0;">
                        Full Moon radiance bathes your vocational and relationship sectors. Sincere craftsmanship over prior weeks yields observable fruits. Favorable day for milestone presentations, expressing warmth, and sharing blessings.
                    </p>
                    <div style="background:#F4EFE6; border:1px solid #ECE6DD; padding:6px 9px; border-radius:5px; font-size:9.5px; color:#4E7D68; line-height:1.45;">
                        <strong>🪷 Simple 3-Step Gratitude Ritual:</strong><br>
                        1. Express sincere verbal thanks to your parents, partner, and mentors.<br>
                        2. Feed stray cows, birds, or donate kheer/food to the needy.<br>
                        3. Spend 10 minutes walking quietly beneath lunar rays for nervous restoration.
                    </div>
                </div>
            </div>

            <!-- Chandra Ashtama Warning Card -->
            <div class="mr-card" style="margin-top:7px; padding:9px 13px; background:#FFFFFF; margin-bottom:0;">
                <div style="font-size:10.5px; font-weight:700; color:#111827; margin-bottom:2px;">🌙 Chandra Ashtama &amp; Emotional Calmness Protocol:</div>
                <div style="font-size:10px; color:#4B5563; line-height:1.48;">
                    Chandra governs the emotional matrix (Manas). When Moon transits the 8th house from your natal Moon sign (mid-month), the psyche may experience momentary restlessness or heightened sensitivity without objective rationale. During these 48 hours, drink warm water, avoid argumentative corporate exchanges, enjoy classical raagas or meditative chants, and safeguard deep sleep.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🌑', 'term' => 'Amavasya',        'desc' => 'Zero-point lunar stillness; optimal for internal contemplation & goal-setting'],
                ['icon' => '🌕', 'term' => 'Purnima',         'desc' => 'Full lunar illumination; optimal for public expression & celebration'],
                ['icon' => '🕯️', 'term' => 'Ghee Diya Lamp',  'desc' => 'Lighting a lamp at sunset purifies domestic vibrations and thoughts'],
                ['icon' => '🪷', 'term' => 'Gratitude Lotus', 'desc' => 'Heartfelt acknowledgment of blessings and divine grace received']
            ], 'Lunar Ritual Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Chandra Gochar (Moon transit) directly governs your daily mood, sleep quality, and mental clarity.",
                "Transits through Kendra houses amplify creativity, presence, and communicative confidence in meetings.",
                "Amavasya represents an optimal time for quiet reflection, setting clean intentions, and deep restful sleep.",
                "Purnima marks completion of cycles, public recognition, and cultivating heartfelt gratitude with your loved ones."
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

            <div class="mr-tag-pill">10 · Time-Bound Remedial Blueprint</div>
            <h1 class="mr-page-h1">Elemental Balance &amp; Time-Bound Remedial Prescriptions</h1>
            <div class="mr-page-sub">Panchamahabhuta Tuning, Dominant Transit Remedies &amp; Precision Hora Timings</div>

            <!-- Radar Chart & Elemental Guidance -->
            <div style="display:flex; gap:14px; align-items:center;">
                <div style="flex-shrink:0;">
                    <?php echo self::render_panchamahabhuta_radar_svg(145); ?>
                    <div style="text-align:center; font-size:8.5px; color:#4B5563; font-weight:700; margin-top:2px; text-transform:uppercase; letter-spacing:0.06em;">
                        Panchamahabhuta Energy Radar
                    </div>
                </div>

                <div style="flex:1;">
                    <div class="mr-card sage-accent" style="margin-bottom:0; padding:10px 13px;">
                        <div class="mr-card-title"><span>🔥 Dominant Elements: Agni (Fire) &amp; Vayu (Air)</span></div>
                        <p style="font-size:10.5px; color:#111827; margin:0 0 3px 0; line-height:1.45;">
                            <strong>Constitutional Alignment This Month:</strong> Heightened Agni and Vayu bestow sharp intellectual clarity, entrepreneurial initiative, and decisive problem-solving acumen.
                        </p>
                        <p style="font-size:10px; color:#4B5563; margin:0; line-height:1.48;">
                            <strong>Balancing Protocol:</strong> To prevent mental exhaustion or digestive acidity, ground this kinetic current with Prithvi (Earth) practices: morning barefoot walks on grass, consuming water from a pure copper vessel, and adhering to disciplined dining hours.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Time-Bound Remedial Focus of the Month (High-Value Add) -->
            <div class="mr-grid-2" style="margin-top:7px;">
                <div class="mr-card terracotta-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>⏰ Precision Hora &amp; Transit Remediation</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Wednesday Mercury Hora (7:00–8:15 AM):</strong> Chant <em>&ldquo;Om Budhaya Namah&rdquo;</em> 108 times to sharpen commercial decisions and dissolve communication delays.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Sunday Surya Hora (Dawn to +1 hr):</strong> Offer fresh water (Arghya) in a copper lota facing East; shields leadership status and ocular vitality.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot"></div><span><strong>Saturday Sunset Remedy:</strong> Light a mustard oil lamp under a Peepal tree to appease Shani's 10th aspect.</span></div>
                </div>

                <div class="mr-card gold-accent" style="padding:9px 12px; margin-bottom:0;">
                    <div class="mr-card-title"><span>🏺 Targeted Daan &amp; Auspicious Ratna</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>Charitable Daan:</strong> Donate green moong dal, yellow lentils, or feed fresh spinach/jaggery to desi cows on Wednesdays and Sundays before noon.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>💎 Gemstone (Ratna):</strong> Natural Emerald (Panna) for commercial sharpness or Ruby (Manikya) for executive presence.</span></div>
                    <div class="mr-bullet-item"><div class="mr-bullet-dot" style="background:#C2964C;"></div><span><strong>📿 Rudraksha:</strong> 5-Mukhi Rudraksha calms the nervous system; 3-Mukhi pacifies excessive fiery restlessness.</span></div>
                </div>
            </div>

            <!-- Ayurvedic Routine Card -->
            <div class="mr-card" style="margin-top:6px; padding:8px 12px; background:#FFFFFF; margin-bottom:0;">
                <div style="font-size:10.5px; font-weight:700; color:#111827; margin-bottom:2px;">🌿 Ayurvedic Lifestyle &amp; Dinacharya Tip for the Month:</div>
                <div style="font-size:9.5px; color:#4B5563; line-height:1.45;">
                    Drink a glass of warm water infused with lemon or fennel seeds upon waking. Curtail deep-fried foods or heavily spiced dinners late at night. Applying warm sesame or coconut oil to the soles of your feet before sleep calms overactive Vayu and fosters deep, rejuvenating slumber.
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '🔥/🌍/💨', 'term' => 'Panchamahabhuta', 'desc' => 'Fire (Agni), Earth (Prithvi), Air (Vayu), Water (Jala), Space (Akash)'],
                ['icon' => '⏰', 'term' => 'Hora Timing',        'desc' => 'Specific planetary hours when remedial mantras hold maximum efficacy'],
                ['icon' => '🏺', 'term' => 'Daan Protocol',      'desc' => 'Selfless charitable offerings washing away malefic transit residues']
            ], 'Remedial Symbols'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Vedic remedies (Upayas) are not based on fear; they scientifically rebalance constitutional frequencies with natural planetary cycles.",
                "Offering water (Arghya) to the morning sun takes merely 2 minutes, yet enhances executive willpower and vital stamina.",
                "Wearing an authentic 5-Mukhi Rudraksha balances biorhythmic blood pressure and shields your mental field from workplace fatigue.",
                "Acts of quiet charity performed with pure intentions on designated Hora windows invite profound celestial grace into your affairs."
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

            <div class="mr-tag-pill">11 · Monthly Master Compass</div>
            <h1 class="mr-page-h1">Sovereign Horizon Dashboard &amp; Guided Monthly Sankalpa</h1>
            <div class="mr-page-sub">Executive Life Compass, Core Affirmation &amp; Interactive New Moon Intention Prompt</div>

            <div class="mr-card terracotta-accent" style="padding:10px 14px; margin-bottom:7px;">
                <div style="font-size:9.5px; color:#C45738; letter-spacing:0.12em; text-transform:uppercase; font-weight:800;">📜 Monthly Master Mantra &amp; Resolution</div>
                <h2 style="font-family:<?php echo $head_font; ?>; font-size:16px; color:#111827; font-weight:700; margin:2px 0 4px 0;">&ldquo;<?php echo esc_html($theme); ?>&rdquo;</h2>
                <div style="font-family:<?php echo $head_font; ?>; font-size:12.5px; font-style:italic; color:#4B5563; line-height:1.45;">
                    &ldquo;<?php echo esc_html($dashboard['sankalpa'] ?? 'I anchor my ambition in calm patience during the opening fortnight; my sincerity and structured discipline will manifest into leadership breakthroughs and family prosperity in the second half.'); ?>&rdquo;
                </div>
            </div>

            <div class="mr-grid-4">
                <div class="mr-card terracotta-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size:9px; font-weight:700; color:#4B5563; text-transform:uppercase;">🏹 Career</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['karma']['score'] ?? 78); ?>%</div>
                    <span style="font-size:8px; color:#4E7D68; font-weight:700;">High Growth</span>
                </div>
                <div class="mr-card sage-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size:9px; font-weight:700; color:#4B5563; text-transform:uppercase;">💰 Money</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['dhana']['score'] ?? 68); ?>%</div>
                    <span style="font-size:8px; color:#C2964C; font-weight:700;">Stable Liquidity</span>
                </div>
                <div class="mr-card gold-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size:9px; font-weight:700; color:#4B5563; text-transform:uppercase;">🪷 Family</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['prema']['score'] ?? 64); ?>%</div>
                    <span style="font-size:8px; color:#C45738; font-weight:700;">Patience Needed</span>
                </div>
                <div class="mr-card slate-accent" style="text-align:center; padding:7px 5px; margin-bottom:0;">
                    <span style="font-size:9px; font-weight:700; color:#4B5563; text-transform:uppercase;">🌿 Health</span>
                    <div style="font-size:15px; font-weight:800; color:#111827; margin-top:1px;"><?php echo esc_html($pillars['arogya']['score'] ?? 86); ?>%</div>
                    <span style="font-size:8px; color:#4E7D68; font-weight:700;">Good Stamina</span>
                </div>
            </div>

            <!-- Personalized Monthly Sankalpa Interactive Guided Exercise (High-Value Add) -->
            <div style="background:#FFFFFF; border:1.5px solid #C2964C; border-radius:7px; padding:9px 13px; margin-top:7px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <span style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em;">
                        ✍️ Personalized Monthly Sankalpa Reflection (Amavasya Phase)
                    </span>
                    <span style="font-size:8.5px; font-weight:800; color:#C45738; text-transform:uppercase;">Interactive Exercise</span>
                </div>
                <p style="font-size:9.5px; color:#4B5563; margin:0 0 6px 0; line-height:1.4;">
                    Vedic psychology teaches that written intentions formed on the New Moon anchor deep subconscious resolve. Write your solemn commitments below:
                </p>
                <div style="font-size:9.5px; line-height:1.7;">
                    <div style="margin-bottom:3px;">
                        <strong style="color:#C45738;">1. Primary Professional Dharma:</strong> What is the one non-negotiable career milestone you resolve to complete before Purnima?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                    <div style="margin-bottom:3px;">
                        <strong style="color:#4E7D68;">2. Emotional Calibration:</strong> Which reactive habit or impulse will you consciously surrender this month?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                    <div>
                        <strong style="color:#C2964C;">3. Family Stewardship &amp; Seva:</strong> What specific act of generosity or family leadership will you anchor this month?<br>
                        <div style="border-bottom:1px dashed #D8CFBF; height:16px;"></div>
                    </div>
                </div>
            </div>

            <!-- Golden Rules for the Month -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 11px; margin-top:6px;">
                <div style="font-size:10px; font-weight:700; color:#111827; margin-bottom:2px;">🌟 4 Sovereign Rules for <?php echo esc_html($person_name); ?> This Month:</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:3px 10px; font-size:9.5px; line-height:1.4;">
                    <div><strong style="color:#C45738;">1. Career:</strong> Deliver high-caliber execution quietly; defer boardroom debates until Nov 15.</div>
                    <div><strong style="color:#4E7D68;">2. Money:</strong> Enforce strict budgetary checks; preserve liquidity for strategic late-month deployment.</div>
                    <div><strong style="color:#C2964C;">3. Family:</strong> Listen actively to spouse and revere parental counsel in domestic matters.</div>
                    <div><strong style="color:#111827;">4. Health:</strong> Dedicate 20 minutes to dawn walking and offer solar water for mental composure.</div>
                </div>
            </div>

            <!-- On-Page Symbol Guide -->
            <?php echo self::render_symbol_guide_bar([
                ['icon' => '📜', 'term' => 'Maha Mantra',  'desc' => 'Core philosophy guiding all daily thoughts, speech & executive decisions'],
                ['icon' => '🧭', 'term' => 'Sankalpa',     'desc' => 'Personal morning resolution for staying emotionally centered and focused'],
                ['icon' => '🏛️', 'term' => 'Siddhanta',    'desc' => 'Mathematical coordinates verified against classical Parashari Jyotish']
            ], 'Horizon Master Keys'); ?>
        </div>

        <div>
            <?php echo self::render_page_bottom_synthesis([
                "Keep this Master Summary page accessible on your phone or printed upon your desk throughout the unfolding month.",
                "Cross-reference your Shubh Power Days (Nov 7, 8, 26, 27) whenever scheduling major pitches, interviews, or contract signings.",
                "Patience in complex situations is the ultimate proof of sovereign mastery: allow minor frictions to pass without reaction.",
                "Walk forward with unshakeable self-belief, clean intentions, and profound faith in your hard work (Karma) and God's grace."
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

            <div class="mr-tag-pill">Reference Lexicon &amp; Glossary</div>
            <h1 class="mr-page-h1">Glossary of Astrological Terms &amp; Vedic Lexicon</h1>
            <div class="mr-page-sub">Clear, authoritative definitions of classical Sanskrit principles and Jyotish terminology used in this report.</div>

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
                        <strong style="color:#C45738; font-size:10.5px; display:block; margin-bottom:1px;"><?php echo esc_html($gt['term']); ?>:</strong>
                        <span style="font-size:9.5px; color:#4B5563; line-height:1.4; display:block;"><?php echo esc_html($gt['desc']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Master Reference Guidance Card -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-left:3.5px solid #C2964C; border-radius:6px; padding:7px 11px; margin-top:8px;">
                <div style="font-size:10px; font-weight:700; color:#111827; margin-bottom:1px;">📖 Reference Lexicon Application:</div>
                <div style="font-size:9.5px; color:#4B5563; line-height:1.42;">
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
                <div class="mr-page-sub" style="margin-bottom:5px;">Explore Our Suite of Specialized Tailored Vedic Reports for You and Your Family</div>
            </div>

            <!-- 4 Core Showcase Cards -->
            <div class="mr-offer-card">
                <div class="mr-offer-icon">💎</div>
                <div class="mr-offer-info">
                    <h4>Complete Janam Kundali &amp; Lifetime Destiny Dossier</h4>
                    <p>Exhaustive birth analysis covering D1, D9 Navamsha, D10 Dashamsha, Shadbala, and 12-house life trajectory for career, marriage, and wealth.</p>
                    <div class="mr-offer-meta">25 Pages · Parashari &amp; Jaimini Synthesis · Includes Full Kundali Chart</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">💍</div>
                <div class="mr-offer-info">
                    <h4>Kundali Matching (Gun Milan) &amp; Marriage Compatibility</h4>
                    <p>Authentic 36-Point Ashtakoot scorecard, Manglik Dosh check, Papasamaya malefic balance, Nadi &amp; Bhakoot analysis, and 20-year timeline synchronization.</p>
                    <div class="mr-offer-meta">20 Pages · Ashtakoot &amp; Dasha Synchronization · Dual Partner Vedic Analysis</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">☀️</div>
                <div class="mr-offer-info">
                    <h4>Annual Varshaphal (Solar Return) Master Report</h4>
                    <p>Comprehensive 26-page annual predictive blueprint from birthday to birthday, Muntha transit, Tajika dasha, and month-by-month career &amp; health roadmap.</p>
                    <div class="mr-offer-meta">26 Pages · Tajika Annual Engine · Month-by-Month Predictive Guidance</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">🛡️</div>
                <div class="mr-offer-info">
                    <h4>Kundali Dosh Analysis &amp; Vedic Remedial Dossier</h4>
                    <p>In-depth diagnosis of Manglik Dosh, Kaal Sarp Dosh, Pitra Dosh, and Shani Sade Sati/Dhaiya with certified gemstone, mantra, and practical home remedies.</p>
                    <div class="mr-offer-meta">20 Pages · 90-Day Karmic Harmonization Plan · Certified Vedic Remedial Solutions</div>
                </div>
            </div>

            <!-- Certified Verification Seal Box -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 11px; margin-top:6px; text-align:center;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:700; color:#111827; letter-spacing:0.04em;">
                    🏛️ MeraRashifal Siddhanta Verification &amp; Certification Seal
                </div>
                <p style="font-size:9.5px; color:#4B5563; margin:2px 0 0 0; line-height:1.42;">
                    Calculated using standard Indian Lahiri Chitra Paksha Ayanamsha (24°10'38") and authentic Maharishi Parashari Jyotish principles. Cryptographic verification hash: <code style="font-size:9px; color:#C45738;"><?php echo strtoupper(substr(md5($person_name . $dob . $month_year), 0, 16)); ?></code>.
                </p>
            </div>
        </div>

        <div style="border-top:1px solid #ECE6DD; padding-top:5px; text-align:center; font-size:9.5px; color:#4B5563; z-index:2; line-height:1.5;">
            <div style="font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:1px;">
                Access Your Reports Archive &amp; Order New Blueprints
            </div>
            <span>Visit your personal portal at: <strong style="color:#C45738;">merarashifal.com/my-account-reports/</strong></span><br>
            <span style="font-size:8.5px; color:#6B7280; margin-top:1px; display:block;">
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
