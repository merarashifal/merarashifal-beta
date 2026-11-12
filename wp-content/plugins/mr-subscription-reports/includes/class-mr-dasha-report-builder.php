<?php
/**
 * Class MR_Dasha_Report_Builder
 *
 * Master Vedic Mahadasha & Antardasha Life Timing Report (Strictly 20 Pages).
 * Engineered strictly to the MeraRashifal Master Design System & Typography Architecture.
 *
 * Page Sequence (Strictly 20 Physical A4 Pages):
 * Page 01: Sovereign Cover Page (Verified Birth Details, Triple Sign Bar, Executive Theme, Statutory Disclaimer)
 * Page 02: Table of Contents & Dasha Mechanics (Balanced 2-Column Index, 300px North Indian Diamond D1 Chart)
 * Page 03: The Running Mahadasha Deep Dive & Foundational Dashboard (Duration, Dates, Parashari Functional Role)
 * Page 04: Dasha Sandhi & Karmic Recalibration (Dasha Sandhi Warning Box, Transition Protocol, 6-Month Rules)
 * Page 05: Navamsha (D9) Dasha Verification (300px North Indian Diamond D9 Chart, Soul Fruit Validation)
 * Page 06: Harmonic Strength & Dasha Substrate (2x2 Divisional Grid: D1, D9, D10, D60 at ~205px Each)
 * Page 07: Antardasha Master Timeline (Part I: Sub-Periods 1 to 3 Breakdown & Daily Focus)
 * Page 08: Antardasha Master Timeline (Part II: Sub-Periods 4 to 6 Breakdown & Enterprise Navigation)
 * Page 09: Antardasha Master Timeline (Part III: Sub-Periods 7 to 9 Breakdown & Consolidation)
 * Page 10: Sub-Period Interplay & Angular Geometry (Kendras, Trikonas, 6/8 Shadashtak, 2/12 Dwirdwadash)
 * Page 11: Life Domains: Career, Status & Enterprise (Executive Promotions, Restructuring, Altitude)
 * Page 12: Life Domains: Wealth Accumulation & Treasury Flow (Cash Flow Windows, Liquidity, Investments)
 * Page 13: Life Domains: Marriage, Alliances & Family (Domestic Harmony, Partnerships, Contracts)
 * Page 14: Life Domains: Health, Vitality & Resilience (Bio-Reserves, Dosha Balance, Stress Protocols)
 * Page 15: Yogini Dasha: 36-Year Secondary Karmic Cycle (8 Yogini Archetypes, Active Subconscious Rhythm)
 * Page 16: Parallel Timing Matrix & Dual-System Sync (Vimshottari vs. Yogini "Bulletproof Success Windows")
 * Page 17: Micro-Timing: Pratyantardasha Precision (Granular Sub-Sub-Periods & Weekly Pacing)
 * Page 18: High-Stakes Strategic Event Calendar (Green-Light Execution vs. Red-Light Caution Dates)
 * Page 19: Remedial Upayas & Weekly Planetary Daan Schedule (Gemstones, Rudraksha, Weekday Daan/Mantra Matrix)
 * Page 20: Master Glossary, Sacred Benediction & Back Cover (10-Point Glossary, Upanishadic Shloka, Report Suite)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Dasha_Report_Builder {

    public static function get_logo_data_uri() {
        if (class_exists('MR_Report_Builder')) {
            return MR_Report_Builder::get_logo_data_uri();
        }
        $logo_path = 'c:\\xampp\\htdocs\\Merarashifal\\wp-content\\uploads\\merarashifal-logo.png';
        if (file_exists($logo_path)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
        }
        return '';
    }

    /**
     * Helper to get consistent Vedic planet color
     */
    public static function get_planet_color($pl) {
        if (strpos($pl, 'Ma') !== false) return '#C45738';
        if (strpos($pl, 'Ju') !== false) return '#B45309';
        if (strpos($pl, 'Ve') !== false) return '#047857';
        if (strpos($pl, 'Mo') !== false) return '#1D4ED8';
        if (strpos($pl, 'Su') !== false) return '#B91C1C';
        if (strpos($pl, 'Ra') !== false || strpos($pl, 'Ke') !== false) return '#4B5563';
        if (strpos($pl, 'Sa') !== false) return '#374151';
        if (strpos($pl, 'Me') !== false) return '#0D9488';
        return '#111827';
    }

    /**
     * Render North Indian Diamond D1 Chart (Crisp Responsive Visual Anchor)
     */
    public static function render_d1_diamond_svg($size = 280, $lagna_sign_num = 4, $planets_by_house = []) {
        $s = $size;
        $half = $s / 2;

        if (empty($planets_by_house)) {
            $planets_by_house = [
                1  => ['Ra 19°14\''],
                2  => ['Mo 11°53\''],
                3  => ['Ju 15°29\'', 'Sa 15°43\''],
                5  => ['Ve 16°51\'(AK)'],
                6  => ['Su 11°25\'', 'Me 8°40\''],
                7  => ['Ma 3°02\'(Ex)', 'Ke 19°14\''],
            ];
        }

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.8px solid #C2964C; border-radius:9px; box-shadow:0 3px 12px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.8'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='8' fill='#C2964C' fill-opacity='0.15'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='2.5' fill='#C45738'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.35, 'pl_x' => $half, 'pl_y' => $half * 0.68],
            2  => ['num_x' => $half * 0.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.40],
            3  => ['num_x' => $half * 0.20, 'num_y' => $half * 0.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.72],
            4  => ['num_x' => $half * 0.35, 'num_y' => $half, 'pl_x' => $half * 0.70, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.20, 'num_y' => $half * 1.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.28],
            6  => ['num_x' => $half * 0.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.60],
            7  => ['num_x' => $half, 'num_y' => $half * 1.66, 'pl_x' => $half, 'pl_y' => $half * 1.34],
            8  => ['num_x' => $half * 1.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.60],
            9  => ['num_x' => $half * 1.78, 'num_y' => $half * 1.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.28],
            10 => ['num_x' => $half * 1.65, 'num_y' => $half, 'pl_x' => $half * 1.30, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.78, 'num_y' => $half * 0.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.72],
            12 => ['num_x' => $half * 1.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.40],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $fs_num = ($s < 260) ? 12 : 14;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='{$fs_num}' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = $planets_by_house[$h];
                $cnt = count($pls);
                $fs_pl = ($s < 260) ? 9.2 : 10.5;
                $step = ($s < 260) ? 10.5 : 11.5;
                $y_start = $c['pl_y'] - (($cnt - 1) * ($step / 2));
                foreach ($pls as $idx => $pl) {
                    $color = self::get_planet_color($pl);
                    $cur_y = $y_start + ($idx * $step);
                    $svg .= "<text x='{$c['pl_x']}' y='{$cur_y}' fill='{$color}' font-size='{$fs_pl}' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pl}</text>";
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render North Indian Diamond D9 Navamsha Chart
     */
    public static function render_d9_diamond_svg($size = 250, $lagna_sign_num = 10) {
        $s = $size;
        $half = $s / 2;

        $planets_by_house = [
            1  => ['Ma 3°02\'(Ex)'],
            3  => ['Me', 'Ke'],
            4  => ['Su', 'Mo'],
            5  => ['Ju', 'Sa'],
            11 => ['Ve', 'Ra'],
        ];

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.8px solid #C2964C; border-radius:9px; box-shadow:0 3px 12px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.8'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='8' fill='#C2964C' fill-opacity='0.15'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='2.5' fill='#047857'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.35, 'pl_x' => $half, 'pl_y' => $half * 0.68],
            2  => ['num_x' => $half * 0.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.40],
            3  => ['num_x' => $half * 0.20, 'num_y' => $half * 0.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.72],
            4  => ['num_x' => $half * 0.35, 'num_y' => $half, 'pl_x' => $half * 0.70, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.20, 'num_y' => $half * 1.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.28],
            6  => ['num_x' => $half * 0.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.60],
            7  => ['num_x' => $half, 'num_y' => $half * 1.66, 'pl_x' => $half, 'pl_y' => $half * 1.34],
            8  => ['num_x' => $half * 1.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.60],
            9  => ['num_x' => $half * 1.78, 'num_y' => $half * 1.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.28],
            10 => ['num_x' => $half * 1.65, 'num_y' => $half, 'pl_x' => $half * 1.30, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.78, 'num_y' => $half * 0.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.72],
            12 => ['num_x' => $half * 1.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.40],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $fs_num = ($s < 260) ? 12 : 14;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='{$fs_num}' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = $planets_by_house[$h];
                $cnt = count($pls);
                $fs_pl = ($s < 260) ? 9.2 : 10.5;
                $step = ($s < 260) ? 10.5 : 11.5;
                $y_start = $c['pl_y'] - (($cnt - 1) * ($step / 2));
                foreach ($pls as $idx => $pl) {
                    $color = self::get_planet_color($pl);
                    $cur_y = $y_start + ($idx * $step);
                    $svg .= "<text x='{$c['pl_x']}' y='{$cur_y}' fill='{$color}' font-size='{$fs_pl}' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pl}</text>";
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Divisional Varga Diamond Chart (Crisp Size ~172px for 2x2 Grid)
     */
    public static function render_varga_diamond_svg($size = 172, $planets_by_house = [], $lagna_sign_num = 1, $title = '') {
        $s = $size;
        $half = $s / 2;

        $svg = "<div style='display:flex; flex-direction:column; align-items:center;'>";
        $svg .= "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.8px solid #C2964C; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.03); display:block;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.8'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.4'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='5' fill='#C2964C' fill-opacity='0.2'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.34, 'pl_x' => $half, 'pl_y' => $half * 0.68],
            2  => ['num_x' => $half * 0.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.40],
            3  => ['num_x' => $half * 0.22, 'num_y' => $half * 0.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 0.72],
            4  => ['num_x' => $half * 0.35, 'num_y' => $half, 'pl_x' => $half * 0.70, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.22, 'num_y' => $half * 1.50, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.28],
            6  => ['num_x' => $half * 0.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 0.40, 'pl_y' => $half * 1.60],
            7  => ['num_x' => $half, 'num_y' => $half * 1.66, 'pl_x' => $half, 'pl_y' => $half * 1.34],
            8  => ['num_x' => $half * 1.50, 'num_y' => $half * 1.78, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.60],
            9  => ['num_x' => $half * 1.78, 'num_y' => $half * 1.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 1.28],
            10 => ['num_x' => $half * 1.65, 'num_y' => $half, 'pl_x' => $half * 1.30, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.78, 'num_y' => $half * 0.50, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.72],
            12 => ['num_x' => $half * 1.50, 'num_y' => $half * 0.22, 'pl_x' => $half * 1.60, 'pl_y' => $half * 0.40],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='10.5' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = $planets_by_house[$h];
                $cnt = count($pls);
                if ($cnt === 1) {
                    $color = self::get_planet_color($pls[0]);
                    $svg .= "<text x='{$c['pl_x']}' y='{$c['pl_y']}' fill='{$color}' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pls[0]}</text>";
                } elseif ($cnt === 2) {
                    $color1 = self::get_planet_color($pls[0]);
                    $color2 = self::get_planet_color($pls[1]);
                    $y1 = $c['pl_y'] - 4.5;
                    $y2 = $c['pl_y'] + 5;
                    $svg .= "<text x='{$c['pl_x']}' y='{$y1}' fill='{$color1}' font-size='8.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pls[0]}</text>";
                    $svg .= "<text x='{$c['pl_x']}' y='{$y2}' fill='{$color2}' font-size='8.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pls[1]}</text>";
                } else {
                    $y_start = $c['pl_y'] - (($cnt - 1) * 4);
                    foreach ($pls as $idx => $pl) {
                        $color = self::get_planet_color($pl);
                        $cur_y = $y_start + ($idx * 8);
                        $svg .= "<text x='{$c['pl_x']}' y='{$cur_y}' fill='{$color}' font-size='7.8' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pl}</text>";
                    }
                }
            }
        }

        $svg .= "</svg>";
        if (!empty($title)) {
            $svg .= "<div style='text-align:center; font-size: 11px; color:#111827; font-weight:800; margin-top:3px; text-transform:uppercase; letter-spacing:0.04em;'>{$title}</div>";
        }
        $svg .= "</div>";
        return $svg;
    }

    /**
     * Render Streamlined On-Page Symbol Guide Bar
     */
    public static function render_symbol_guide_bar($items, $title = 'Dasha Terms', $heading_font = "'Poppins', sans-serif") {
        ob_start();
        ?>
        <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:14px; padding:3px 12px; margin-top:auto; margin-bottom:3px; display:flex; align-items:center; flex-wrap:wrap; font-size: 9.8px; line-height:1.3; color:#64748B;">
            <span style="font-weight:800; color:#8C7B6B; text-transform:uppercase; letter-spacing:0.06em; margin-right:8px; flex-shrink:0;">
                🧭 <?php echo esc_html($title); ?>:
            </span>
            <div style="display:inline-flex; align-items:center; flex-wrap:wrap; gap:2px 8px;">
                <?php 
                $total = count($items);
                $idx = 0;
                foreach ($items as $k => $it): 
                    $idx++;
                    $term = is_array($it) ? ($it['term'] ?? ($it['name'] ?? (string)$k)) : (string)$k;
                    $desc = is_array($it) ? ($it['desc'] ?? ($it['value'] ?? '')) : (string)$it;
                ?>
                    <span>
                        <strong style="color:#111827;"><?php echo esc_html($term); ?>:</strong> 
                        <span style="color:#4B5563;"><?php echo esc_html($desc); ?></span>
                        <?php if ($idx < $total): ?>
                            <span style="color:#CBD5E1; margin-left:5px;">|</span>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Page Bottom Takeaway Synthesis (Mandatory on Every Page, 100 to 120 words total)
     */
    public static function render_page_bottom_synthesis($arg1, $arg2 = 'Actionable Strategic Takeaways', $heading_font = "'Poppins', sans-serif") {
        if (is_array($arg1)) {
            $bullets = $arg1;
            $title = is_string($arg2) ? $arg2 : 'Actionable Strategic Takeaways';
        } else {
            $title = is_string($arg1) ? $arg1 : 'Actionable Strategic Takeaways';
            $bullets = is_array($arg2) ? $arg2 : [];
        }
        ob_start();
        ?>
        <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:4px solid #C45738; border-radius:8px; padding:6px 14px; margin-top:0; margin-bottom:2px; box-shadow:0 2px 8px rgba(0,0,0,0.02); width:100%; box-sizing:border-box;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                <div style="font-family:<?php echo $heading_font; ?>; font-size: 13px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.06em;">
                    ⚡ <?php echo esc_html($title); ?>
                </div>
                <span style="font-size: 8.2px; font-weight:700; color:#C45738; background:#FDF2EE; padding:1.5px 7px; border-radius:10px; border:1px solid #FADCCE; text-transform:uppercase; letter-spacing:0.05em;">
                    Actionable Strategic Synthesis
                </span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:2px 16px; font-size: 11px; color:#374151; line-height:1.35;">
                <?php foreach ($bullets as $b): ?>
                    <div style="display:flex; align-items:flex-start;">
                        <span style="color:#C45738; margin-right:6px; font-weight:bold; font-size: 12.5px; line-height:1.2;">•</span>
                        <span><?php echo esc_html($b); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Unified Page Footer Bar (Page X of 20)
     */
    public static function render_page_footer($page_num, $section_title = '') {
        $clean_title = $section_title ? html_entity_decode($section_title, ENT_QUOTES, 'UTF-8') : 'Master Vedic Mahadasha & Antardasha Life Timing Report';
        ob_start();
        ?>
        <div class="mr-footer-bar">
            <span>MeraRashifal · <?php echo esc_html($clean_title); ?></span>
            <span class="mr-page-num">Page <?php echo intval($page_num); ?> of 20</span>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function build_dasha_report_html($person_name, $gender, $dob, $tob, $pob, $editorial_data = [], $vedic_data = []) {
        $logo_uri = self::get_logo_data_uri();
        $dob_clean = str_replace('/', '-', $dob);
        $dob_dt = DateTime::createFromFormat('d-m-Y', $dob_clean);
        $formatted_dob = $dob_dt ? $dob_dt->format('F j, Y') : (strtotime($dob_clean) ? date('F j, Y', strtotime($dob_clean)) : $dob);
        $astro_prof = MR_Report_Builder::extract_astro_profile($dob, $tob, $pob, $vedic_data);

        $motto     = "Align human initiative with celestial cycles to turn latent potential into sovereign mastery.";
        $theme     = "Rahu Mahadasha Expansion: Sovereign Architecture, Unconventional Altitude & Strategic Alliances";
        $head_font = "'Poppins', sans-serif";
        $body_font = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($person_name . ' · Master Vedic Mahadasha & Antardasha Life Timing Report'); ?></title>
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
            font-size: 13.8px;
            line-height: 1.58;
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
            padding: 9.5mm 14mm 11.5mm 14mm;
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
            margin-bottom: 8px;
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
            max-width: 180px;
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
        .mr-footer-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ECE6DD;
            padding-top: 5px;
            font-size: 11px;
            color: #6B7280;
            z-index: 2;
            position: relative;
            margin-top: auto;
            flex-shrink: 0;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 12px;
        }

        .mr-tag-pill {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 3px 12px;
            border-radius: 12px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            color: #C45738;
            margin-bottom: 6px;
        }
        .mr-page-h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 19.5px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 3px 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 12px;
            color: #4B5563;
            margin-bottom: 8px;
            font-weight: 500;
            line-height: 1.35;
        }

        .mr-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .mr-grid-3col {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }
        .mr-grid-4col {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        .mr-grid-2x2-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 18px;
            justify-items: center;
        }

        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 8px;
            padding: 7px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .mr-card-accent {
            background: #FDF8F3;
            border: 1.5px solid #C2964C;
            border-radius: 8px;
            padding: 7px 12px;
        }
        .mr-callout-box {
            background: #FAF7F2;
            border: 1px solid #ECE6DD;
            border-left: 3.5px solid #C45738;
            border-radius: 6px;
            padding: 6px 12px;
            margin-top: 5px;
        }

        /* Standardized Rigid Tables for HTML-to-PDF */
        .mr-dasha-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid #ECE6DD;
            border-radius: 8px;
            overflow: hidden;
            background: #FFFFFF;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            margin-bottom: 7px;
        }
        .mr-dasha-table th {
            background: #FAF7F2;
            border: 1px solid #ECE6DD;
            padding: 4.5px 7px;
            font-size: 11px;
            font-weight: 800;
            color: #111827;
            text-align: left;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .mr-dasha-table td {
            border: 1px solid #ECE6DD;
            padding: 4px 7px;
            font-size: 10.8px;
            line-height: 1.34;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Dasha Sandhi Alert Box */
        .mr-sandhi-alert-box {
            background: #FEF3C7;
            border: 1.5px solid #D97706;
            border-left: 5px solid #B45309;
            border-radius: 8px;
            padding: 12px 18px;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.08);
        }

        /* Offer Cards on Concluding Back Page */
        .mr-offer-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 8px;
            padding: 11px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .mr-offer-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            color: #C45738;
            flex-shrink: 0;
        }
        .mr-offer-info h4 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 15.3px;
            color: #111827;
            font-weight: 700;
        }
        .mr-offer-info p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #4B5563;
            line-height: 1.45;
        }
        .mr-offer-meta {
            font-size: 10.8px;
            font-weight: 800;
            color: #C45738;
            margin-top: 2px;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>

    <!-- ========================================================
         PAGE 1: SOVEREIGN COVER PAGE
         ======================================================== -->
    <div class="mr-page" style="justify-content:space-between; text-align:center; padding:11mm 16mm 9mm 16mm;">
        <!-- Top Sanskrit Invocation & Master 4x Brand Logo -->
        <div style="z-index:2; position:relative;">
            <div style="font-size: 13.8px; font-weight:700; color:#C45738; letter-spacing:0.14em; text-transform:uppercase; margin-bottom:12px;">
                ॥ ॐ श्री गणेशाय नमः ॥ · प्रामाणिक वैदिक दशा काल-चक्र महा-दस्तावेज़
            </div>
            <div style="display:flex; justify-content:center; margin-bottom:14px;">
                <img src="<?php echo esc_attr($logo_uri); ?>" style="height:95px; max-width:320px; object-fit:contain; display:block;" alt="MeraRashifal Logo" />
            </div>
            <h1 style="font-family:<?php echo $head_font; ?>; font-size:26px; font-weight:800; color:#111827; margin:0 0 6px 0; letter-spacing:-0.02em; line-height:1.2;">
                MASTER VEDIC MAHADASHA &amp; ANTARDASHA LIFE TIMING REPORT
            </h1>
            <div style="font-size: 16.3px; font-weight:600; color:#C2964C; font-style:italic; margin-bottom:10px;">
                “<?php echo esc_html($motto); ?>”
            </div>

            <!-- Triple Identity Bar: Western Sun vs Vedic Moon vs Ascendant -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:30px; padding:7px 22px; display:inline-flex; gap:18px; font-size: 12.5px; box-shadow:0 2px 8px rgba(0,0,0,0.03); margin-bottom:14px;">
                <div><span style="color:#C45738; font-weight:700;">Western Sun:</span> <strong style="color:#111827;"><?php echo esc_html($astro_prof['sunsigns']['western_sign'] . ' (' . $astro_prof['sunsigns']['western_dates'] . ')'); ?></strong></div>
                <div style="color:#CBD5E1;">|</div>
                <div><span style="color:#047857; font-weight:700;">Vedic Moon:</span> <strong style="color:#111827;"><?php echo esc_html($astro_prof['moon_sign'] . ' · ' . $astro_prof['moon_nak'] . ' ' . $astro_prof['moon_pada']); ?></strong></div>
                <div style="color:#CBD5E1;">|</div>
                <div><span style="color:#C2964C; font-weight:700;">Lagna:</span> <strong style="color:#111827;"><?php echo esc_html($astro_prof['lagna_sign'] . ' ' . $astro_prof['lagna_deg'] . '°'); ?></strong></div>
            </div>
        </div>

        <!-- Center Native Verified Details Card -->
        <div style="background:#FFFFFF; border:1.5px solid #C2964C; border-radius:12px; padding:12px 20px; box-shadow:0 4px 18px rgba(0,0,0,0.04); max-width:560px; margin:0 auto; z-index:2; position:relative; width:100%;">
            <div style="font-size: 13.1px; font-weight:700; color:#8C7B6B; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:4px;">
                CONFIDENTIAL CHRONOLOGICAL DOSSIER PREPARED FOR
            </div>
            <div style="font-family:<?php echo $head_font; ?>; font-size:24px; font-weight:800; color:#111827; margin-bottom:12px; letter-spacing:-0.01em;">
                <?php echo esc_html(strtoupper($person_name)); ?>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; text-align:left; font-size: 12.5px; border-top:1px solid #F0EAE1; padding-top:12px; margin-bottom:12px;">
                <div><strong style="color:#4B5563;">Date of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($formatted_dob); ?></span></div>
                <div><strong style="color:#4B5563;">Time of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($tob); ?></span></div>
                <div><strong style="color:#4B5563;">Place of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($pob); ?></span></div>
                <div><strong style="color:#4B5563;">Biological Gender:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html(ucfirst($gender)); ?></span></div>
                <div><strong style="color:#4B5563;">Birth Nakshatra:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($astro_prof['moon_nak'] . ' (' . $astro_prof['moon_pada'] . ' · ' . $astro_prof['moon_lord'] . ')'); ?></span></div>
                <div><strong style="color:#4B5563;">Lagna Lord:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($astro_prof['lagna_lord']); ?></span></div>
            </div>

            <div style="background:#FDF8F3; border:1px solid #ECE6DD; border-radius:8px; padding:8px 12px; font-size: 12px; color:#C45738; font-weight:700; display:flex; justify-content:space-around;">
                <span>Vimshottari Engine: <strong>120-Year Lahiri</strong></span>
                <span>•</span>
                <span>Vedic Sun Sign: <strong><?php echo esc_html($astro_prof['sunsigns']['vedic_sign'] . ' (' . $astro_prof['sunsigns']['vedic_dates'] . ')'); ?></strong></span>
                <span>•</span>
                <span>Calculated Age: <strong><?php echo intval($astro_prof['age']); ?> Years</strong></span>
            </div>
        </div>

        <!-- Master Theme Banner & Statutory Disclaimer -->
        <div style="z-index:2; position:relative; max-width:600px; margin:0 auto; padding-top:6px;">
            <div style="font-size: 12.5px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:3px;">
                EXECUTIVE TIMING THEME
            </div>
            <div style="font-size: 14.8px; color:#374151; font-weight:600; line-height:1.4; margin-bottom:8px;">
                “<?php echo esc_html($theme); ?>”
            </div>
            <div style="font-size: 7.2px; color:#64748B; line-height:1.35; text-align:justify; background:#FAF7F2; padding:4px 8px; border-radius:4px; border:1px solid #ECE6DD;">
                <strong style="color:#111827;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
            </div>
        </div>

        <!-- Sovereign Seal Footer (Moved up to create space above bottom frame line) -->
        <div style="border-top:1px solid #ECE6DD; padding-top:6px; margin-bottom:8px; font-size: 11.3px; color:#8C7B6B; display:flex; justify-content:space-between; z-index:2; position:relative;">
            <span style="font-weight:600; color:#4B5563;">www.MeraRashifal.com</span>
            <span>Document ID: MR-DS-<?php echo strtoupper(substr(md5($person_name . $dob . 'DASHA'), 0, 8)); ?></span>
            <span style="font-weight:700; color:#C45738;">Page 1 of 20</span>
        </div>
    </div>

    <!-- ========================================================
         PAGE 2: TABLE OF CONTENTS & DASHA FOUNDATION (D1 ANCHOR)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Table of Contents &amp; D1 Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">01 · Guide &amp; Birth Chart</div>
            <h1 class="mr-page-h1">Table of Contents &amp; Your Birth Chart</h1>
            <div class="mr-page-sub">Your complete 20-page guide to understanding planetary periods, timing of events, and best phases</div>

            <!-- Half-Page Primary Visual Anchor: 240px North Indian Diamond D1 Chart & Table of Contents -->
            <div style="display:grid; grid-template-columns: 240px 1fr; gap: 20px; align-items:center; margin-bottom:8px;">
                <div>
                    <?php echo self::render_d1_diamond_svg(240, 4); ?>
                    <div style="text-align:center; font-size: 11px; color:#4B5563; font-weight:700; margin-top:4px; text-transform:uppercase; letter-spacing:0.04em;">
                        🪐 Natal D1 Kundali · Cancer Lagna · Rahu Mahadasha Anchor
                    </div>
                </div>

                <!-- 2-Column Table of Contents (Part I & Part II Balanced Evenly) -->
                <div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em; border-bottom:1.5px solid #C45738; padding-bottom:2px; margin-bottom:6px;">
                        Part I: Macro Cycles &amp; Sub-Periods (Pages 1–10)
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size: 11.5px; margin-bottom:8px;">
                        <tbody>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">01. Sovereign Cover &amp; Verified Natal Coordinates</td><td style="text-align:right; font-weight:800; color:#C45738;">01</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">02. Table of Contents &amp; D1 Celestial Anchor</td><td style="text-align:right; font-weight:800; color:#C45738;">02</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">03. Active Mahadasha Deep-Dive &amp; Dashboard</td><td style="text-align:right; font-weight:800; color:#C45738;">03</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">04. Dasha Sandhi &amp; Transition Protocol</td><td style="text-align:right; font-weight:800; color:#C45738;">04</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">05. Navamsha (D9) Dasha Verification &amp; Fruit</td><td style="text-align:right; font-weight:800; color:#C45738;">05</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">06. Harmonic Strength &amp; 4-Varga Grid (D1–D60)</td><td style="text-align:right; font-weight:800; color:#C45738;">06</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">07. Antardasha Master Timeline (Part I: Sub 1–3)</td><td style="text-align:right; font-weight:800; color:#C45738;">07</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">08. Antardasha Master Timeline (Part II: Sub 4–6)</td><td style="text-align:right; font-weight:800; color:#C45738;">08</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">09. Antardasha Master Timeline (Part III: Sub 7–9)</td><td style="text-align:right; font-weight:800; color:#C45738;">09</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">10. Sub-Period Interplay &amp; Angular Geometry</td><td style="text-align:right; font-weight:800; color:#C45738;">10</td></tr>
                        </tbody>
                    </table>

                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:800; color:#047857; text-transform:uppercase; letter-spacing:0.06em; border-bottom:1.5px solid #047857; padding-bottom:2px; margin-bottom:6px;">
                        Part II: Domains, Yogini &amp; Upayas (Pages 11–20)
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size: 11.5px;">
                        <tbody>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">11. Life Domains: Career Altitude &amp; Enterprise</td><td style="text-align:right; font-weight:800; color:#C45738;">11</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">12. Life Domains: Wealth Accumulation &amp; Treasury</td><td style="text-align:right; font-weight:800; color:#C45738;">12</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">13. Life Domains: Marriage, Alliances &amp; Family</td><td style="text-align:right; font-weight:800; color:#C45738;">13</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">14. Life Domains: Health, Vitality &amp; Resilience</td><td style="text-align:right; font-weight:800; color:#C45738;">14</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">15. Yogini Dasha: 36-Year Secondary Karmic Cycle</td><td style="text-align:right; font-weight:800; color:#C45738;">15</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">16. Parallel Timing Matrix (Bulletproof Windows)</td><td style="text-align:right; font-weight:800; color:#C45738;">16</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">17. Micro-Timing: Pratyantardasha Precision</td><td style="text-align:right; font-weight:800; color:#C45738;">17</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">18. High-Stakes Strategic Event Calendar</td><td style="text-align:right; font-weight:800; color:#C45738;">18</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">19. Remedial Upayas &amp; Weekly Daan Schedule</td><td style="text-align:right; font-weight:800; color:#C45738;">19</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:3px 0; font-weight:700; color:#111827;">20. Master Glossary, Benediction &amp; Back Cover</td><td style="text-align:right; font-weight:800; color:#C45738;">20</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reading Methodology Directive Card -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:10px 16px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:4px;">
                    Strategic Navigation of the 120-Year Vimshottari Progression
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">1. Mahadasha (Macro Climate):</strong>
                        Sets the overarching 6 to 20-year environmental theme, psychological focus, and major karmic assignments for the soul.
                    </div>
                    <div>
                        <strong style="color:#047857;">2. Antardasha (Micro Weather):</strong>
                        Governs specific 6 to 36-month execution windows, shifting commercial opportunities, alliances, and resource allocations.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">3. Dual Sync (Yogini Validation):</strong>
                        Cross-verifying with the 36-year Yogini cycle isolates high-conviction windows where planetary support reaches maximum velocity.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(2, 'Table of Contents &amp; D1 Celestial Anchor'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 3: THE RUNNING MAHADASHA DEEP DIVE & DASHBOARD
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Active Mahadasha Deep-Dive</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">02 · Your Main Planetary Period (Mahadasha)</div>
            <h1 class="mr-page-h1">Your Rahu Mahadasha: 18-Year Overview (2024–2042)</h1>
            <div class="mr-page-sub">What this 18-year period means for your personal growth, ambitions, and big life goals</div>

            <!-- Active Ruling Mahadasha Executive Dashboard -->
            <div style="background:#FFFFFF; border:1.5px solid #C2964C; border-radius:10px; padding:12px 18px; margin-bottom:12px; box-shadow:0 3px 12px rgba(0,0,0,0.03);">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:14px; text-align:center;">
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Ruling Entity</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#C45738;">Rahu (North Node)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Shadow Planet (Chhaya Graha)</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Temporal Span</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#111827;">18 Years Total</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Active: 2024 to 2042</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Natal Position</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#111827;">1st House (Lagna)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Cancer 19°14' (Ashlesha)</div>
                    </div>
                    <div>
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Current Sub-Period</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#C2964C;">Rahu-Rahu</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Swabhukti (Foundation)</div>
                    </div>
                </div>
            </div>

            <!-- Deep Analysis Cards: Evolutionary Archetype & Life Altitude -->
            <div class="mr-grid-2col" style="margin-bottom:10px;">
                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#C45738; margin-bottom:5px;">
                        1. The Psychological Shift &amp; Ambition Surge
                    </div>
                    <div style="font-size: 12px; color:#374151; line-height:1.48;">
                        Rahu in your 1st house triggers an irresistible surge toward self-reinvention, global horizons, and strategic leadership. Having concluded the disciplined physical labor of Mars Mahadasha, you now operate with intellectual leverage, scalable technology, and bold unconventional alliances. Rahu acts through its dispositor Chandra (the Moon) in royal Leo 2nd house, channeling personal ambition directly into generational wealth accumulation and vocal authority.
                    </div>
                </div>

                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#047857; margin-bottom:5px;">
                        2. Dispositor Synergy &amp; Exalted Mars Aspect
                    </div>
                    <div style="font-size: 12px; color:#374151; line-height:1.48;">
                        Directly opposite Rahu sits an exalted Mars (Ruchaka Mahapurusha Yoga) at 03°02' Capricorn with Ketu in your 7th Kendra. This creates a high-voltage axis of tension and triumph. Mars provides the tactical discipline, structural grit, and legal endurance required to harness Rahu’s expansive vision without burning out. Partnerships, institutional contracts, and corporate negotiations become primary instruments of your elevation.
                    </div>
                </div>
            </div>

            <!-- Macro-Trajectory Breakdown -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:4px;">
                    Evolutionary Phasing of the 18-Year Cycle
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">Phase 1: Inception (2024–2029):</strong>
                        Rahu-Rahu and Rahu-Jupiter establish new infrastructural frameworks, international client networks, and massive intellectual elevation.
                    </div>
                    <div>
                        <strong style="color:#047857;">Phase 2: Consolidation (2029–2035):</strong>
                        Rahu-Saturn and Rahu-Mercury solidify administrative authority, systemic operational efficiency, and institutional wealth stability.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">Phase 3: Sovereign Legacy (2035–2042):</strong>
                        Rahu-Venus and concluding sub-periods harvest monumental commercial dividends, philanthropic honors, and spiritual self-actualization.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Mahadasha' => 'Major 18-Year Planetary Epoch',
                'Swabhukti' => 'Self-Sub-Period (Rahu in Rahu)',
                'Chhaya Graha' => 'Shadow Planet of Unconventional Mastery',
                'Dispositor' => 'Planet Ruling the Node\'s Sign (Moon in Leo)'
            ], 'Mahadasha Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu Mahadasha marks a monumental 18-year chapter of bold reinvention, commanding societal leadership, and unprecedented commercial expansion.",
                "Positioned in Cancer Lagna and ruled by Moon in Leo 2nd house, Rahu channels creative and technical brilliance into tangible treasury flow.",
                "The direct aspect from exalted Mars in the 7th Kendra provides rigorous physical stamina and strategic fearlessness to conquer competitive arenas.",
                "Navigating this major cycle demands strict ethical grounding, transparent legal pacts, and structured daily routines to optimize massive cosmic momentum."
            ], 'Active Mahadasha Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(3, 'Active Mahadasha Deep-Dive &amp; Dashboard'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 4: DASHA SANDHI & KARMIC RECALIBRATION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Dasha Sandhi &amp; Transition Protocol</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">03 · Period Shifts: Changing from One Planet to Another</div>
            <h1 class="mr-page-h1">Dasha Sandhi: Smoothly Managing Major Life Transitions</h1>
            <div class="mr-page-sub">Simple advice for handling changes, staying patient, and adapting smoothly as periods shift</div>

            <!-- Strategic Feature 1: The Dasha Sandhi Warning Box -->
            <div class="mr-sandhi-alert-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12px; font-weight:800; color:#92400E; text-transform:uppercase; letter-spacing:0.04em;">
                        ⚠️ Dasha Sandhi Active Transition Directive (Mars to Rahu Cusp)
                    </div>
                    <span style="font-size: 10.6px; font-weight:800; color:#92400E; background:#FDE68A; padding:2px 8px; border-radius:10px; border:1px solid #F59E0B;">
                        HIGH-SENSITIVITY WINDOW
                    </span>
                </div>
                <div style="font-size: 11.9px; color:#78350F; line-height:1.48;">
                    <strong>The Karmic Detox Phenomenon:</strong> Whenever an individual shifts between two fundamentally distinct Mahadashas (such as transitioning from 7 years of Mars into 18 years of Rahu), the 6 months preceding and following the ingress represent a <em>Dasha Sandhi</em>. During this junction, the departing planet releases its energetic grip while the incoming planet tests the native’s nervous system. <strong>Directive:</strong> Avoid speculative gambles, hasty partnership dissolutions, or radical emotional overhauls. Treat this phase as a conscious calibration window where old routines are purged and long-range foundations are stabilized.
                </div>
            </div>

            <!-- Transition Comparison Grid -->
            <div class="mr-grid-2col" style="margin-top:10px; margin-bottom:10px;">
                <div class="mr-card" style="border-left:3.5px solid #C45738;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#C45738; margin-bottom:4px;">
                        What Has Concluded (Mars Mahadasha):
                    </div>
                    <div style="font-size: 11.8px; color:#374151; line-height:1.45;">
                        • The phase of heavy physical exertion, direct confrontation, and manual infrastructure building.<br>
                        • Rigorous competitive disputes and solo firefighting across commercial sectors.<br>
                        • The necessity to personally oversee tactical minutiae rather than delegating.<br>
                        • Intense expenditure of physical vitality in establishing early professional credibility.
                    </div>
                </div>

                <div class="mr-card" style="border-left:3.5px solid #047857;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#047857; margin-bottom:4px;">
                        What Has Commenced (Rahu Mahadasha):
                    </div>
                    <div style="font-size: 11.8px; color:#374151; line-height:1.45;">
                        • A high-altitude era of intellectual leverage, global scalability, and strategic diplomacy.<br>
                        • Harnessing technology, automated workflows, and institutional networks for compounding.<br>
                        • Cultivating public influence, advisory authority, and commanding vocal expression.<br>
                        • Deep spiritual self-inquiry balancing outward material elevation with internal equanimity.
                    </div>
                </div>
            </div>

            <!-- Karmic Detox & Preservation Rules -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:4px;">
                    Four Non-Negotiable Rules for Dasha Sandhi Stability
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 18px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#111827;">1. Ground the Nervous System:</strong> Rahu activates sudden electrical restlessness. Maintain rigorous sleep cycles, reduce late-night screen exposure, and practice slow Pranayama breathing daily.
                    </div>
                    <div>
                        <strong style="color:#111827;">2. Double-Check Documentation:</strong> The first 18 months of Rahu can trigger illusions in contracts. Ensure every legal agreement and vendor partnership is reviewed by independent counsel.
                    </div>
                    <div>
                        <strong style="color:#111827;">3. Guard Speech and Diplomacy:</strong> With Moon in Leo ruling your speech, resist the urge to issue impulsive executive ultimatums during moments of transitional friction.
                    </div>
                    <div>
                        <strong style="color:#111827;">4. Pacify Ancestral Karma:</strong> Regular charitable offerings of dark sesame seeds, blankets, and water on Saturdays and Amavasya neutralize lingering transitional debts.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Dasha Sandhi' => 'Planetary Junction & Handover Period',
                'Karmic Detox' => 'Purging Residual Energetic Residue',
                'Ingress' => 'Exact Astronomical Boundary Cross',
                'Pranayama' => 'Breathwork for Bio-Electromagnetic Balance'
            ], 'Sandhi Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Dasha Sandhi represents a critical energetic bridge where old life modes dissolve and new cosmic frequencies calibrate.",
                "Transitioning from Mars to Rahu shifts your core operating paradigm from brute physical stamina to refined intellectual strategy.",
                "Emotional equanimity and objective legal verification are your supreme shields against temporary transitional illusions.",
                "Executing the prescribed lifestyle detox and charitable remedies ensures seamless absorption of Rahu’s monumental material blessings."
            ], 'Dasha Sandhi Execution Protocols'); ?>

            <?php echo self::render_page_footer(4, 'Dasha Sandhi &amp; Transition Protocol'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 5: NAVAMSHA (D9) DASHA VERIFICATION & FRUIT
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · D9 Navamsha Dasha Correlation</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">04 · Soul &amp; Inner Chart (D9 Navamsha)</div>
            <h1 class="mr-page-h1">Your Navamsha Chart (D9): Inner Happiness &amp; Long-Term Results</h1>
            <div class="mr-page-sub">Seeing how outer achievements turn into real inner peace, family happiness, and satisfaction</div>

            <!-- Half-Page Visual Anchor: 240px North Indian Diamond D9 Chart -->
            <div class="mr-grid-2col" style="align-items:center; margin-bottom:6px;">
                <div>
                    <?php echo self::render_d9_diamond_svg(240, 10); ?>
                    <div style="text-align:center; font-size: 11px; color:#4B5563; font-weight:700; margin-top:4px; text-transform:uppercase; letter-spacing:0.04em;">
                        🪐 D9 Navamsha Kundali · Capricorn Ascendant · Mars Exalted 1st House
                    </div>
                </div>

                <div class="mr-card" style="padding:8px 12px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13.5px; font-weight:700; color:#047857; margin-bottom:3px;">
                        The Parashari Principle of D9 Confirmation
                    </div>
                    <div style="font-size: 11.2px; color:#374151; line-height:1.42;">
                        Classical Parashari Jyotish dictates that while the D1 Janma Kundali represents the tree (the external manifestation, body, and opportunity), the D9 Navamsha represents the fruit (the real internal satisfaction, late-life endurance, and soul dharma). A dasha lord well-placed in D1 but afflicted in D9 yields fleeting initial glory followed by disappointment. Conversely, strong dignity in D9 guarantees that victories gained during the Mahadasha endure permanently.
                    </div>
                </div>
            </div>

            <!-- Deep Analysis: Rahu and Key Lords in D9 -->
            <div class="mr-grid-2col" style="margin-top:2px; margin-bottom:6px;">
                <div class="mr-card" style="padding:6px 11px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Rahu-Venus Conjunction in D9 Scorpio (11th House)
                    </div>
                    <div style="font-size: 10.8px; color:#4B5563; line-height:1.36;">
                        In your D9 Navamsha, Rahu joins Venus (your Atmakaraka) in the 11th house of gains, networks, and long-range aspirations. This confirms that Rahu Mahadasha will unlock profound commercial dividends through creative ventures, specialized aesthetic domains, and powerful patron networks.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 11px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 13px; font-weight:700; color:#C2964C; margin-bottom:2px;">
                        Mars Exalted in D9 Ascendant (Makara Lagna)
                    </div>
                    <div style="font-size: 10.8px; color:#4B5563; line-height:1.36;">
                        Mars achieves peak exaltation in the 1st house of D9 Navamsha, perfectly echoing its exaltation in the D1 7th Kendra. This rare structural alignment (*Vargottama potency*) guarantees unyielding psychological resilience, unbreakable executive discipline, and sovereign leadership.
                    </div>
                </div>
            </div>

            <!-- Synthesis Box -->
            <div class="mr-callout-box" style="border-left-color:#047857; padding:5px 12px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:2px;">
                    Navamsha Verdict on Rahu Mahadasha
                </div>
                <div style="font-size: 10.8px; color:#374151; line-height:1.36;">
                    The D9 chart decisively validates your active Rahu Mahadasha. Rather than triggering chaotic disorientation, Rahu operates under the grounding stewardship of exalted Mars and the wealth-generating rays of Venus in the 11th harmonic house, confirming lasting institutional achievement.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Navamsha (D9)' => '9th Harmonic Chart of Soul Fruit & Dharma',
                'Vargottama' => 'Planetary Placement in Identical Sign Across D1 & D9',
                'Atmakaraka (AK)' => 'Soul Purpose Indicator (Venus in Scorpio D9)',
                'Phala Vichar' => 'Validation of Concrete Real-World Outcome'
            ], 'Navamsha Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "D9 Navamsha cross-verification confirms that active dasha opportunities translate into permanent, compounding life achievements.",
                "Rahu’s partnership with Atmakaraka Venus in D9 11th house ensures profound financial breakthroughs and prestigious professional alliances.",
                "Exalted Mars anchoring the D9 Ascendant endows you with supreme moral courage, emotional fortitude, and strategic focus.",
                "The harmonic architecture validates that your professional and personal elevation will mature gracefully into lasting legacy."
            ], 'Navamsha Dasha Verification Takeaways'); ?>

            <?php echo self::render_page_footer(5, 'Navamsha (D9) Dasha Verification · Fruit'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 6: HARMONIC STRENGTH & DASHA SUBSTRATE (4-VARGA GRID)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · 4-Harmonic Varga Dasha Substrate</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">05 · Checking Across 4 Main Life Charts</div>
            <h1 class="mr-page-h1">Checking Strengths Across 4 Main Life Charts (D1, D9, D10, D7)</h1>
            <div class="mr-page-sub">Confirming how your current period supports health, relationships, career, and family</div>

            <!-- 2x2 Clean Grid of 4 Divisional Charts (~172px each) -->
            <div class="mr-grid-2x2-charts" style="margin-bottom:6px;">
                <div>
                    <?php echo self::render_varga_diamond_svg(172, [
                        1  => ['Ra'],
                        2  => ['Mo'],
                        3  => ['Ju', 'Sa'],
                        5  => ['Ve'],
                        6  => ['Su', 'Me'],
                        7  => ['Ma', 'Ke'],
                    ], 4, 'D1 Janma Kundali (Physical)'); ?>
                </div>

                <div>
                    <?php echo self::render_varga_diamond_svg(172, [
                        1  => ['Ma'],
                        3  => ['Me', 'Ke'],
                        4  => ['Su', 'Mo'],
                        5  => ['Ju', 'Sa'],
                        11 => ['Ve', 'Ra'],
                    ], 10, 'D9 Navamsha (Soul Fruit)'); ?>
                </div>

                <div>
                    <?php echo self::render_varga_diamond_svg(172, [
                        1  => ['Ju', 'Sa', 'Ma'],
                        5  => ['Ve'],
                        7  => ['Ra'],
                        9  => ['Mo'],
                        10 => ['Su', 'Me'],
                    ], 1, 'D10 Dashamsha (Career Altitude)'); ?>
                </div>

                <div>
                    <?php echo self::render_varga_diamond_svg(172, [
                        1  => ['Ma'],
                        4  => ['Ju'],
                        5  => ['Ve', 'Me'],
                        9  => ['Ra'],
                        10 => ['Sa'],
                    ], 1, 'D60 Shashtiamsha (Past Prarabdha)'); ?>
                </div>
            </div>

            <!-- Dedicated 50-60 Word Finding for Each Harmonic Chart -->
            <div class="mr-grid-2col" style="gap:6px 16px;">
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 9px;">
                    <strong style="color:#C45738; font-size: 11px; display:block; margin-bottom:1px;">D1 Physical Reality (Cancer Lagna):</strong>
                    <span style="font-size: 10.2px; color:#4B5563; line-height:1.32;">
                        Rahu occupying the 1st house anchors personal agency and self-reinvention, directly supported by Yogakaraka exalted Mars in the 7th house. This provides immense physical endurance, strategic vision, and decisive commercial execution throughout the major cycle.
                    </span>
                </div>

                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 9px;">
                    <strong style="color:#047857; font-size: 11px; display:block; margin-bottom:1px;">D9 Soul Fruit (Capricorn Ascendant):</strong>
                    <span style="font-size: 10.2px; color:#4B5563; line-height:1.32;">
                        Mars reaches peak exaltation in the D9 1st house while Rahu aligns with Atmakaraka Venus in the 11th harmonic house. This proves that external dasha endeavors translate directly into deep psychological satisfaction, moral clarity, and enduring wealth.
                    </span>
                </div>

                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 9px;">
                    <strong style="color:#C2964C; font-size: 11px; display:block; margin-bottom:1px;">D10 Executive Altitude (Aries Ascendant):</strong>
                    <span style="font-size: 10.2px; color:#4B5563; line-height:1.32;">
                        The triad of Mars, Jupiter, and Saturn anchoring the D10 1st house endows you with monumental institutional authority. Rahu stationed in the 7th house of corporate partnerships drives large-scale commercial contracts and public executive prominence.
                    </span>
                </div>

                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 9px;">
                    <strong style="color:#111827; font-size: 11px; display:block; margin-bottom:1px;">D60 Past Prarabdha (Karmic Origin):</strong>
                    <span style="font-size: 10.2px; color:#4B5563; line-height:1.32;">
                        Jupiter positioned in the 4th Kendra and exalted Mars in the 1st house confirm extraordinary past-life administrative virtue. This divine credit protects your enterprise during challenging planetary transitions and rewards disciplined service with sovereign honor.
                    </span>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'D1' => 'Gross Physical Plane & Identity',
                'D9' => 'Harmonic Destiny & Marriage Fruit',
                'D10' => 'Vocational Altitude & Public Status',
                'D60' => 'Supreme Causal Root of Past Karma'
            ], 'Varga Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Evaluating the active Mahadasha across all 4 core divisional harmonics confirms deep foundational integrity on every life plane.",
                "D10 Dashamsha indicates that running sub-periods will progressively elevate your vocational status into commanding institutional advisory.",
                "D60 Shashtiamsha reveals positive past-life administrative credit, acting as a divine shield against sudden professional instability.",
                "The extraordinary alignment of exalted Mars across D1, D9, and D10 provides permanent structural resilience to conquer ambitious enterprise goals."
            ], 'Harmonic Dasha Substrate Takeaways'); ?>

            <?php echo self::render_page_footer(6, 'Harmonic Strength · 4-Varga Grid'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 7: ANTARDASHA MASTER TIMELINE (PART I: SUB 1 TO 3)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Antardasha Master Timeline (Part I)</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">06 · Sub-Periods: Step-by-Step Timeline (Part 1)</div>
            <h1 class="mr-page-h1">Antardasha Master Timeline (Part I: 2024–2032)</h1>
            <div class="mr-page-sub">Detailed look at Rahu, Jupiter, and Saturn sub-periods: growth, learning, and steady progress</div>

            <!-- Antardasha Sequence Table (Sub-Periods 1 to 3) -->
            <table class="mr-dasha-table">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 20%;">
                    <col style="width: 17%;">
                    <col style="width: 41%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Sub-Period Cycle</th>
                        <th>Exact Temporal Window</th>
                        <th>Duration &amp; Share</th>
                        <th>Core Psychological &amp; Tactical Theme</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C45738;">
                            1. Rahu-Rahu (Swabhukti)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Jan 2024 – Nov 2026
                        </td>
                        <td style="color:#4B5563;">
                            34 Months · 15.8%
                        </td>
                        <td style="color:#374151;">
                            <strong>Infrastructural Reset:</strong> Recalibrating self-identity, purging outdated obligations, and establishing modernized operational workflows. High mental velocity requiring grounding.
                        </td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="font-weight:700; color:#047857;">
                            2. Rahu-Jupiter (Guru)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Nov 2026 – Apr 2029
                        </td>
                        <td style="color:#4B5563;">
                            29 Months · 13.3%
                        </td>
                        <td style="color:#374151;">
                            <strong>Wisdom &amp; Capital Surge:</strong> Jupiter rules the 9th house of fortune (Bhagya). Enormous moral authority, executive advisory appointments, high-yield capital gains, and institutional prestige.
                        </td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C2964C;">
                            3. Rahu-Saturn (Shani)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Apr 2029 – Feb 2032
                        </td>
                        <td style="color:#4B5563;">
                            34 Months · 15.8%
                        </td>
                        <td style="color:#374151;">
                            <strong>Structural Consolidation:</strong> Saturn functions as Amatyakaraka. Demands uncompromising operational precision, institutional contracts, audit compliance, and enterprise asset hardening.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Deep Analysis Cards for the Triad -->
            <div class="mr-grid-3col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Rahu-Rahu: Foundation Laying
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        This initial sub-cycle tests your mental stamina. Do not mistake transitional restlessness for failure. Prioritize scalable backend infrastructure, automate client systems, and protect bodily vitality against nervous exhaustion.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#047857; margin-bottom:2px;">
                        Rahu-Jupiter: Golden Expansion
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        A luminous window of supreme expansion. Jupiter’s divine aspect mitigates any raw malefic tendencies of Rahu. Deploy capital into ambitious business growth, acquire premium real estate, and mentor key enterprise teams.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C2964C; margin-bottom:2px;">
                        Rahu-Saturn: Corporate Mastery
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        A rigorous testing ground that separates amateur enthusiasm from corporate mastery. Saturn enforces relentless discipline. High-value negotiations close successfully through patient, airtight legal agreements.
                    </div>
                </div>
            </div>

            <!-- Strategic Callout Box -->
            <div class="mr-callout-box" style="border-left-color:#C45738; padding:5px 12px; margin-top:3px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:1px;">
                    Executive Guidance for Phase I (2024–2032)
                </div>
                <div style="font-size: 10.8px; color:#374151; line-height:1.35;">
                    The first 8 years of Rahu Mahadasha constitute your primary wealth-building runway. By systematically transitioning from Rahu-Rahu’s initial setup into Jupiter’s expansive wealth surge and Saturn’s rock-solid operational discipline, you build an unassailable financial fortress.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Antardasha' => 'Secondary Sub-Period Governing 6–36 Months',
                'Bhuktinatha' => 'Ruling Lord of the Operating Sub-Period',
                'Amatyakaraka' => 'Career Signifier Planet (Saturn in Virgo)',
                'Bhagya' => 'Fortune & Divine Grace (Jupiterian Domain)'
            ], 'Antardasha Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu-Rahu acts as the vital testing laboratory, demanding mental discipline and modern operational redesign.",
                "Rahu-Jupiter (2026–2029) stands as one of the most auspicious wealth and honor creation windows of your entire lifetime.",
                "Rahu-Saturn (2029–2032) hardens commercial operations, cementing your reputation as an elite institutional authority.",
                "Steady adherence to ethical practices and legal diligence guarantees compounding success across all three opening cycles."
            ], 'Phase I Antardasha Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(7, 'Antardasha Master Timeline (Part I: Sub 1–3)'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 8: ANTARDASHA MASTER TIMELINE (PART II: SUB 4 TO 6)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Antardasha Master Timeline (Part II)</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">07 · Sub-Periods: Step-by-Step Timeline (Part 2)</div>
            <h1 class="mr-page-h1">Antardasha Master Timeline (Part II: 2032–2039)</h1>
            <div class="mr-page-sub">Detailed look at Mercury, Ketu, and Venus sub-periods: business, wisdom, and personal comfort</div>

            <!-- Antardasha Sequence Table (Sub-Periods 4 to 6) -->
            <table class="mr-dasha-table">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 20%;">
                    <col style="width: 17%;">
                    <col style="width: 41%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Sub-Period Cycle</th>
                        <th>Exact Temporal Window</th>
                        <th>Duration &amp; Share</th>
                        <th>Core Psychological &amp; Tactical Theme</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#047857;">
                            4. Rahu-Mercury (Budh)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Feb 2032 – Aug 2034
                        </td>
                        <td style="color:#4B5563;">
                            30 Months · 14.1%
                        </td>
                        <td style="color:#374151;">
                            <strong>Analytical Brilliance:</strong> Mercury rules speech and commerce. Ideal for launching digital assets, signing multi-year publishing or technology licenses, and monetizing intellectual property.
                        </td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="font-weight:700; color:#DC2626;">
                            5. Rahu-Ketu (Ketu)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Aug 2034 – Sep 2035
                        </td>
                        <td style="color:#4B5563;">
                            13 Months · 5.8%
                        </td>
                        <td style="color:#374151;">
                            <strong>Spiritual Recalibration:</strong> The nodal axis triggers a temporary detachment from mundane metrics. Excellent for inward reflection, health sabbaticals, and shedding unproductive partnerships.
                        </td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C45738;">
                            6. Rahu-Venus (Shukra)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Sep 2035 – Sep 2038
                        </td>
                        <td style="color:#4B5563;">
                            36 Months · 16.7%
                        </td>
                        <td style="color:#374151;">
                            <strong>Supreme Material Apex:</strong> Venus is your Atmakaraka. Extraordinary surge in luxury assets, public adulation, romantic and marital happiness, and international cultural prestige.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Deep Analysis Cards for the Triad -->
            <div class="mr-grid-3col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#047857; margin-bottom:2px;">
                        Rahu-Mercury: Commercial Wit
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        Your analytical faculties operate at peak velocity. Excellent for scaling data-driven enterprises, executing high-stakes negotiations, and monetizing proprietary analytical methodologies.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#DC2626; margin-bottom:2px;">
                        Rahu-Ketu: Nodal Detox
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        A pause button designed by cosmic intelligence. Avoid launching speculative ventures during these 13 months. Focus on detoxifying the physical body, decluttering balance sheets, and spiritual retreats.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Rahu-Venus: The Soul Pinnacle
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        As the longest sub-period (3 full years), Rahu-Venus yields the crowning jewel of the Mahadasha. Enjoy compounding wealth, lavish lifestyle enhancements, and deep domestic contentment.
                    </div>
                </div>
            </div>

            <!-- Strategic Callout Box -->
            <div class="mr-callout-box" style="border-left-color:#047857; padding:5px 12px; margin-top:3px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:1px;">
                    Executive Guidance for Phase II (2032–2038)
                </div>
                <div style="font-size: 10.8px; color:#374151; line-height:1.35;">
                    Phase II demands fluid versatility: leveraging intellect during Mercury, practicing disciplined restraint during Ketu, and fully harvesting magnificent lifestyle and commercial rewards during Venus.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Budh' => 'Mercury: Intellect, Media & Rapid Commerce',
                'Ketu' => 'South Node: Detachment, Purification & Moksha',
                'Shukra' => 'Venus: Luxury, Aesthetics & Atmakaraka Soul Fruit',
                'Nodal Detox' => 'Restorative Hiatus Before Commercial Breakthrough'
            ], 'Antardasha Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu-Mercury sharpens technical intellect, opening lucrative intellectual property and media distribution channels.",
                "Rahu-Ketu acts as a mandatory spiritual detox window, purging unproductive liabilities without damaging core assets.",
                "Rahu-Venus unleashes peak material splendor, activating your Atmakaraka for monumental financial and domestic rewards.",
                "Careful pacing across these three sub-periods bridges strategic innovation with lasting emotional and physical fulfillment."
            ], 'Phase II Antardasha Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(8, 'Antardasha Master Timeline (Part II: Sub 4–6)'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 9: ANTARDASHA MASTER TIMELINE (PART III: SUB 7 TO 9)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Antardasha Master Timeline (Part III)</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">08 · Sub-Periods: Step-by-Step Timeline (Part 3)</div>
            <h1 class="mr-page-h1">Antardasha Master Timeline (Part III: 2038–2042)</h1>
            <div class="mr-page-sub">Detailed look at Sun, Moon, and Mars sub-periods: confidence, stability, and preparing for Jupiter</div>

            <!-- Antardasha Sequence Table (Sub-Periods 7 to 9) -->
            <table class="mr-dasha-table">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 20%;">
                    <col style="width: 17%;">
                    <col style="width: 41%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Sub-Period Cycle</th>
                        <th>Exact Temporal Window</th>
                        <th>Duration &amp; Share</th>
                        <th>Core Psychological &amp; Tactical Theme</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#B91C1C;">
                            7. Rahu-Sun (Surya)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Sep 2038 – Aug 2039
                        </td>
                        <td style="color:#4B5563;">
                            11 Months · 5.0%
                        </td>
                        <td style="color:#374151;">
                            <strong>Government &amp; Civic Honor:</strong> Sun rules your 2nd house of treasury and occupies the 6th house with Mercury. High-level state interactions, public accolades, and decisive victory over institutional competitors.
                        </td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="font-weight:700; color:#1D4ED8;">
                            8. Rahu-Moon (Chandra)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Aug 2039 – Feb 2041
                        </td>
                        <td style="color:#4B5563;">
                            18 Months · 8.3%
                        </td>
                        <td style="color:#374151;">
                            <strong>Lagna Lord Activation:</strong> Lagna lord Moon in Pisces 9th house activates divine fortune, family lineage assets and liquid treasury. Enhanced emotional intuition, voice authority, and real estate portfolio consolidation.
                        </td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C45738;">
                            9. Rahu-Mars (Chidra Dasha)
                        </td>
                        <td style="font-weight:600; color:#111827;">
                            Feb 2041 – Jan 2042
                        </td>
                        <td style="color:#4B5563;">
                            11 Months · 5.0%
                        </td>
                        <td style="color:#374151;">
                            <strong>The Grand Closing Chidra:</strong> Exalted Mars closes the 18-year Rahu cycle with unyielding power. Resolves all pending legalities, locks in enduring institutional pacts, and sets the stage for Jupiter Mahadasha.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Deep Analysis Cards for the Triad -->
            <div class="mr-grid-3col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#B91C1C; margin-bottom:2px;">
                        Rahu-Sun: Public Recognition
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        Your societal standing reaches an authoritative peak. Deal transparently with tax authorities, claim industry leadership titles, and cement your reputation as an uncompromising ethical leader.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#1D4ED8; margin-bottom:2px;">
                        Rahu-Moon: Treasury Consolidation
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        With your Lagna lord Moon fully illuminated, family relationships deepen and personal wealth compounds. Focus on transferring volatile enterprise holdings into rock-solid generational trusts.
                    </div>
                </div>

                <div class="mr-card" style="padding:6px 10px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Rahu-Mars: The Chidra Climax
                    </div>
                    <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                        As the final Chidra Dasha, this period requires surgical precision. Exalted Mars guarantees victory over any final bureaucratic hurdles, allowing you to hand over a triumphant legacy to the incoming Guru cycle.
                    </div>
                </div>
            </div>

            <!-- Strategic Callout Box -->
            <div class="mr-callout-box" style="border-left-color:#B91C1C; padding:5px 12px; margin-top:3px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:1px;">
                    Preparation for the 16-Year Jupiter Mahadasha Ingress (2042)
                </div>
                <div style="font-size: 10.8px; color:#374151; line-height:1.35;">
                    The closing sub-periods of Rahu serve as the final polishing of your soul’s vehicle. By organizing your affairs meticulously during Rahu-Mars, you enter your upcoming 16-year Jupiter Mahadasha as an established, honored patriarch of enterprise and wisdom.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Chidra Dasha' => 'Final Concluding Sub-Period of a Mahadasha',
                'Surya' => 'Sun: Authority, Dignity & High Institutional Accolades',
                'Chandra' => 'Moon: Lagna Lord Anchoring Liquid Generational Wealth',
                'Jupiter Ingress' => 'Subsequent 16-Year Era of Dharmic Mastery'
            ], 'Antardasha Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu-Sun delivers commanding executive recognition, public honor, and supreme victory over institutional adversaries.",
                "Rahu-Moon illuminates your Lagna lord, cementing generational family wealth and liquid treasury reserves.",
                "Rahu-Mars closes the 18-year epoch with the unstoppable authority of an exalted Kendra warrior, resolving all pending disputes.",
                "Completing this final triad successfully positions you for a triumphant, peaceful ingress into the glorious 16-year Jupiter Mahadasha."
            ], 'Phase III Antardasha Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(9, 'Antardasha Master Timeline (Part III: Sub 7–9)'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 10: SUB-PERIOD INTERPLAY & ANGULAR GEOMETRY
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Planetary Geometry &amp; Interplay</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">09 · How Planets Work Together</div>
            <h1 class="mr-page-h1">Planetary Harmony: How the Main Planet and Sub-Planet Interact</h1>
            <div class="mr-page-sub">Understanding friendly angles, supportive combinations, and times when extra care is needed</div>

            <!-- Angular Relationship Diagnostic Matrix -->
            <table class="mr-dasha-table">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 22%;">
                    <col style="width: 18%;">
                    <col style="width: 40%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Sub-Lord Pairing</th>
                        <th>Mutual Angular Position</th>
                        <th>Energetic Classification</th>
                        <th>Operative Dynamic &amp; Strategic Antidote</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C45738;">Rahu – Mars</td>
                        <td>1st / 7th Axis (Opposition)</td>
                        <td style="font-weight:700; color:#047857;">Kendra High Synergy</td>
                        <td style="color:#374151;">Direct confrontational power transmuted into corporate contracts and institutional dominance through exalted Mars.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="font-weight:700; color:#047857;">Rahu – Jupiter</td>
                        <td>3rd / 11th Harmonic Axis</td>
                        <td style="font-weight:700; color:#047857;">Upachaya Compounding</td>
                        <td style="color:#374151;">Effortless capital expansion, moral guidance, and high-level patronage shielding against sudden market volatility.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#C2964C;">Rahu – Saturn</td>
                        <td>3rd / 11th Harmonic Axis</td>
                        <td style="font-weight:700; color:#C2964C;">Growth Through Discipline</td>
                        <td style="color:#374151;">Strict operational compliance and systematic restructuring resulting in rock-solid market standing.</td>
                    </tr>
                    <tr style="background:#FEF2F2;">
                        <td style="font-weight:700; color:#DC2626;">Rahu – Sun / Mercury</td>
                        <td>6th / 8th Shadashtak Axis</td>
                        <td style="font-weight:700; color:#DC2626;">Friction &amp; Audit Caution</td>
                        <td style="color:#991B1B;">Requires airtight paperwork, defensive tax management, and emotional poise during bureaucratic cross-examinations.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="font-weight:700; color:#047857;">Rahu – Venus</td>
                        <td>5th / 9th Trikona Axis</td>
                        <td style="font-weight:700; color:#047857;">Lakshmi Yoga Alignment</td>
                        <td style="color:#374151;">Supreme financial windfall, aesthetic refinement, luxurious vehicle additions, and domestic bliss.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Angular Geometry Analysis Cards -->
            <div class="mr-grid-2col" style="gap:12px; margin-bottom:5px;">
                <div class="mr-card" style="border-left:3.5px solid #047857; padding:6px 11px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#047857; margin-bottom:2px;">
                        The Benefic Geometry: Trikonas &amp; 3/11 Growth
                    </div>
                    <div style="font-size: 10.5px; color:#374151; line-height:1.35;">
                        When sub-lords sit in trine (5th/9th) or growth houses (3rd/11th) from Rahu, cosmic momentum accelerates without friction. Opportunities arrive naturally through trusted advisors, collaborative joint ventures, and favorable market winds.
                    </div>
                </div>

                <div class="mr-card" style="border-left:3.5px solid #DC2626; padding:6px 11px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#DC2626; margin-bottom:2px;">
                        The Shadashtak (6/8) Friction: Tactical Antidote
                    </div>
                    <div style="font-size: 10.5px; color:#374151; line-height:1.35;">
                        A 6/8 relationship triggers temporary legal resistance or health fatigue. Rather than forcing aggressive breakthroughs, adopt a defensive strategy: audit financial ledgers, maintain impeccable confidentiality, and pacify planetary lords through targeted daan.
                    </div>
                </div>
            </div>

            <!-- Parashari Geometric Synthesis -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:6px 12px; margin-top:2px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:1px;">
                    Parashari Rule of Mutual Disposition (Sambandha)
                </div>
                <div style="font-size: 10.5px; color:#4B5563; line-height:1.35;">
                    The mutual angle between the Mahadasha and Antardasha lords dictates whether the native swims with the cosmic tide or against it. By calculating exact angular distances, you anticipate challenging weeks with proactive diplomacy while deploying major capital exclusively during high-synergy Kendra and Trikona windows.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Sambandha' => 'Mutual Angular Planetary Relationship',
                'Shadashtak' => '6th / 8th Axis of Stress, Litigation & Karmic Debt',
                'Dwirdwadash' => '2nd / 12th Axis of Capital Outflow & Distance',
                'Trikona' => '5th / 9th Trine Bestowing Divine Fortune (Lakshmi)'
            ], 'Geometry Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Understanding the mutual angular geometry between dasha rulers allows precise anticipation of favorable vs friction-laden phases.",
                "Rahu-Venus and Rahu-Jupiter enjoy magnificent Trikona and 3/11 alignment, maximizing wealth generation and strategic expansion.",
                "Shadashtak (6/8) phases demand vigilant legal oversight, prudent health management, and defensive balance sheet positioning.",
                "Deploying aggressive capital during Kendra/Trikona windows while practicing calm diplomacy during 6/8 phases guarantees enduring success."
            ], 'Planetary Geometry Takeaways'); ?>

            <?php echo self::render_page_footer(10, 'Sub-Period Interplay · Angular Geometry'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 11: SECTOR-SPECIFIC: CAREER, STATUS & ENTERPRISE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Career Altitude &amp; Enterprise</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">10 · Career, Job &amp; Business</div>
            <h1 class="mr-page-h1">Your Career &amp; Professional Growth During This Period</h1>
            <div class="mr-page-sub">Clear guidance on career promotions, starting a business, leadership, and long-term reputation</div>

            <!-- Executive Vocational Metric Dashboard -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:10px; padding:12px 18px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">10th House Lord</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C45738;">Mars (Mangal)</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Deep Exaltation (Ucha)</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Amatyakaraka</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#111827;">Saturn (Shani)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Analytical Virgo 3rd House</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Peak Career Phase</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#047857;">Rahu – Jupiter</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Nov 2026 to Apr 2029</div>
                    </div>
                    <div>
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Dominant Archetype</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C2964C;">Institutional Architect</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Sovereign Advisory</div>
                    </div>
                </div>
            </div>

            <!-- Deep Analysis Cards: Career Mechanics -->
            <div class="mr-grid-2col" style="margin-bottom:10px;">
                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#C45738; margin-bottom:4px;">
                        1. Transition from Labor to Global Strategic Leverage
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Under Rahu Mahadasha, your professional altitude shifts from linear execution into exponential leverage. Rahu in your 1st house demands that you lead through specialized vision, high-level technology systems, and institutional alliances. Your career expands rapidly as you position yourself as the supreme orchestrator of complex enterprise networks rather than a day-to-day troubleshooter.
                    </div>
                </div>

                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#047857; margin-bottom:4px;">
                        2. Exalted Mars Aspect: Dominance Over Competitors
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Your 10th lord Mars occupies its highest exaltation in the 7th Kendra (Capricorn 03°02'), creating Ruchaka Yoga and casting an unyielding 7th aspect onto your Lagna and Rahu. This provides extraordinary competitive stamina, executive gravitas, and legal invulnerability. High-stakes corporate negotiations, strategic acquisitions, and sovereign appointments consistently break in your favor.
                    </div>
                </div>
            </div>

            <!-- Actionable Strategic Windows -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:5px;">
                    Chronological Career Execution Roadmap (2024–2035)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">2024–2026 (Swabhukti Setup):</strong>
                        Consolidate intellectual property, upgrade organizational technology, and establish exclusive sovereign advisory retainers.
                    </div>
                    <div>
                        <strong style="color:#047857;">2026–2029 (Jupiter Breakthrough):</strong>
                        Launch major public expansions, accept high-level advisory directorships, and expand corporate presence across regional or international borders.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">2029–2035 (Saturn &amp; Mercury Scale):</strong>
                        Lock in institutional licensing contracts, scale automated operational workflows, and formalize multi-generational enterprise succession.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                '10th Bhava' => 'House of Career, Sovereignty & Public Reputation',
                'Karmesh' => 'Ruler of the 10th House (Exalted Mars in Capricorn)',
                'Ruchaka Yoga' => 'Supreme Mahapurusha Yoga Yielding Invincible Leadership',
                'Amatyakaraka' => 'Planet Governing High Executive Office (Saturn)'
            ], 'Vocational Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu Mahadasha elevates your vocational path from manual execution into commanding institutional advisory and global leverage.",
                "Exalted 10th lord Mars grants you unshakeable authority during complex disputes and decisive corporate negotiations.",
                "Rahu-Jupiter (2026–2029) stands as your primary window for executive breakthrough, prestigious honors, and enterprise expansion.",
                "Maintaining unblemished ethical compliance and transparent governance guarantees that your career gains remain permanent."
            ], 'Career Trajectory Takeaways'); ?>

            <?php echo self::render_page_footer(11, 'Life Domains: Career Altitude &amp; Enterprise'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 12: SECTOR-SPECIFIC: WEALTH & TREASURY FLOW
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Wealth Accumulation &amp; Treasury Flow</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">11 · Money, Savings &amp; Investments</div>
            <h1 class="mr-page-h1">Your Wealth, Savings &amp; Investments During This Period</h1>
            <div class="mr-page-sub">Practical guidance on growing your savings, making smart investments, and avoiding unnecessary risks</div>

            <!-- Wealth Metric Dashboard -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:10px; padding:12px 18px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Treasury Lord (2nd)</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#B91C1C;">Sun (Surya)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Sagittarius 6th House</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Gains Lord (11th)</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#047857;">Venus (Shukra)</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Atmakaraka (Scorpio 5th)</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Lagna Lord In Treasury</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#1D4ED8;">Moon in Leo (Magha)</div>
                        <div style="font-size: 10.6px; color:#111827; font-weight:700;">Royal 2nd Bhava</div>
                    </div>
                    <div>
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Peak Wealth Window</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C2964C;">2026–2029 &amp; 2035–2038</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Rahu-Guru &amp; Rahu-Venus</div>
                    </div>
                </div>
            </div>

            <!-- Deep Analysis Cards: Wealth Mechanics -->
            <div class="mr-grid-2col" style="margin-bottom:10px;">
                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#047857; margin-bottom:4px;">
                        1. Royal Moon in 2nd House: The Generational Treasury
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Your Lagna lord Moon sits in royal Leo in the 2nd house of accumulated wealth and lineage property. Because Rahu takes on the energy of its dispositor Moon, Rahu Mahadasha channels astronomical ambition directly into compounding family assets. You naturally attract high-value capital inflows through strategic communication, board-level advisory, and commercial property holdings.
                    </div>
                </div>

                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#C2964C; margin-bottom:4px;">
                        2. 11th Lord Venus as Atmakaraka: Windfalls &amp; Equity
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Venus holds the highest degree in your chart (16°51') as your Atmakaraka and governs the 11th house of profits and networks. Positioned in the 5th house of speculative intellect and joining the 11th house in D9 Navamsha, Venus guarantees that intellectual property, technology licenses, and strategic equity stakes yield monumental compounding returns during Rahu sub-cycles.
                    </div>
                </div>
            </div>

            <!-- Capital Allocation Directives -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:5px;">
                    Strategic Capital Deployment Rules for Rahu Mahadasha
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#047857;">Convert Paper into Land:</strong>
                        Rahu creates massive digital and cash inflows. Immediately anchor 40% of surplus liquid windfalls into tangible commercial real estate and gold reserves.
                    </div>
                    <div>
                        <strong style="color:#C45738;">Avoid Speculative Traps:</strong>
                        Never engage in unhedged options or leveraged speculative bets during Rahu-Rahu or Rahu-Ketu. Focus exclusively on businesses with proven cash-flow moats.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">Institutional Gifting (Daan):</strong>
                        Systematic charitable tithing of 2.5% to 5% of enterprise profits pacifies Rahu’s volatile nature, ensuring divine Lakshmi remains permanent in your treasury.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Dhana Bhava' => '2nd House of Accumulated Capital & Treasury (Simha)',
                'Labha Bhava' => '11th House of Massive Net Inflows & Equity (Vrishabha)',
                'Dhana Yoga' => 'Planetary Combinations Linking Wealth with Fortune',
                'Atmakaraka' => 'Venus: Soul Planet Bestowing Commercial Windfalls'
            ], 'Financial Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu Mahadasha operates as your supreme lifetime engine for building multi-generational financial independence.",
                "Lagna lord Moon stationed in the 2nd house ensures that executive advisory and verbal authority generate commanding liquidity.",
                "Rahu-Jupiter (2026–2029) and Rahu-Venus (2035–2038) represent your twin pinnacle windows for multi-million asset accumulation.",
                "Converting volatile digital gains into tangible commercial land and precious metals guarantees lasting dynasty preservation."
            ], 'Wealth Architecture Takeaways'); ?>

            <?php echo self::render_page_footer(12, 'Life Domains: Wealth Accumulation &amp; Treasury'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 13: SECTOR-SPECIFIC: MARRIAGE, ALLIANCES & FAMILY
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Marriage, Alliances &amp; Family</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">12 · Love, Marriage &amp; Family</div>
            <h1 class="mr-page-h1">Love, Marriage &amp; Family Life During This Period</h1>
            <div class="mr-page-sub">Practical advice for building a peaceful married life, good communication, and strong family bonds</div>

            <!-- Relationship Metric Dashboard -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:10px; padding:12px 18px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">7th House Lord</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C2964C;">Saturn (Shani)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Virgo 3rd House · Grounded</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">7th House Occupant</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C45738;">Mars (Exalted) + Ketu</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Ruchaka Yoga Partnership</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Karaka of Marriage</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#047857;">Venus (Atmakaraka)</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Scorpio 5th House</div>
                    </div>
                    <div>
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Core Partnership Rule</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#111827;">Clarity &amp; Mutual Space</div>
                        <div style="font-size: 10.6px; color:#4B5563;">No Impulsive Ultimatums</div>
                    </div>
                </div>
            </div>

            <!-- Deep Analysis Cards: Relationship Dynamics -->
            <div class="mr-grid-2col" style="margin-bottom:10px;">
                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#C45738; margin-bottom:4px;">
                        1. The 1st–7th Nodal Axis in Partnerships
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        With Rahu in your 1st house and Ketu with exalted Mars in your 7th house, your relationships serve as your supreme spiritual mirror. Your spouse or primary business partners possess sharp intelligence, strong principles, and unyielding will. During Rahu Mahadasha, you must balance your expanding public ambitions with deep attentiveness to your spouse’s emotional needs.
                    </div>
                </div>

                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#047857; margin-bottom:4px;">
                        2. Ruchaka Yoga Neutralization of Conflict
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Although Mars in the 7th house creates technical Manglik energy, its deepest exaltation in Capricorn (Ruchaka Mahapurusha Yoga) acts as a powerful neutralizing factor. Instead of triggering discord, this fiery energy manifests as mutual loyalty, shared enterprise building, and fierce collective protection of family honor and generational wealth.
                    </div>
                </div>
            </div>

            <!-- Harmonization Protocols -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:5px;">
                    Guidelines for Partnership &amp; Family Harmony
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">Clear Communication:</strong>
                        Rahu can generate misinterpretations. Ensure domestic expectations, financial allocations, and travel schedules are discussed with transparent warmth.
                    </div>
                    <div>
                        <strong style="color:#047857;">Spousal Co-Investment:</strong>
                        Involve your spouse in long-term property acquisitions. The auspicious combination of Moon in 2nd and exalted Mars in 7th thrives when assets are co-titled.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">Spiritual Pilgrimage:</strong>
                        Periodic joint visits to sacred Shiva and Devi shrines during Rahu-Jupiter and Rahu-Venus harmonize marital prana and dissolve latent ancestral debts.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Kalatra Bhava' => '7th House of Marriage, Contracts & External Alliances',
                'Ketu in 7th' => 'Karmic Anchor Demanding Spiritual Depth in Union',
                'Manglik Neutralization' => 'Exalted Mars Transmuting Heat into Sovereign Loyalty',
                'D9 Lagnesh' => 'Saturn Governing Marriage Evolution in Capricorn D9'
            ], 'Relational Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu Mahadasha deepens marital companionship by transmuting personal ambition into shared family dynasty building.",
                "Exalted Mars in the 7th Kendra neutralizes volatile marital friction, providing steadfast loyalty and mutual business synergy.",
                "Rahu-Venus (2035–2038) stands as a crowning 3-year golden window of romantic happiness, family travel, and domestic luxury.",
                "Practicing calm active listening and avoiding hasty emotional reactions ensures permanent warmth across all family relationships."
            ], 'Partnership & Alliances Takeaways'); ?>

            <?php echo self::render_page_footer(13, 'Life Domains: Marriage, Alliances &amp; Family'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 14: SECTOR-SPECIFIC: HEALTH, VITALITY & RESILIENCE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Health, Vitality &amp; Resilience</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">13 · Health, Energy &amp; Well-Being</div>
            <h1 class="mr-page-h1">Staying Healthy, Energetic &amp; Stress-Free During This Period</h1>
            <div class="mr-page-sub">Simple daily habits, Ayurvedic lifestyle tips, and ways to keep your energy high and peaceful</div>

            <!-- Health Metric Dashboard -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:10px; padding:12px 18px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">6th House Lord</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C2964C;">Jupiter (Guru)</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Neutralized by 9th Lordship</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Primary Constitution</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#111827;">Pitta-Vata Dynamic</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Agni Drive + Mental Speed</div>
                    </div>
                    <div style="border-right:1px solid #F0EAE1; padding-right:10px;">
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Vulnerable Organ System</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#C45738;">Nervous &amp; Digestive</div>
                        <div style="font-size: 10.6px; color:#4B5563;">Rahu Electrical Restlessness</div>
                    </div>
                    <div>
                        <div style="font-size: 11.3px; color:#8C7B6B; font-weight:700; text-transform:uppercase;">Protective Planetary Shield</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:14px; font-weight:800; color:#047857;">Exalted Mars</div>
                        <div style="font-size: 10.6px; color:#047857; font-weight:700;">Supreme Immune Stamina</div>
                    </div>
                </div>
            </div>

            <!-- Deep Analysis Cards: Health Mechanics -->
            <div class="mr-grid-2col" style="margin-bottom:10px;">
                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#C45738; margin-bottom:4px;">
                        1. Managing Rahu’s Bio-Electric Intensity
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Rahu in Cancer Lagna acts as an electrical accelerator. It heightens sensory perception, cognitive processing speed, and entrepreneurial drive, but if ungrounded, it can trigger erratic sleep patterns, nervous tension, and acidity. Because Cancer governs the chest and stomach, emotional stress translates directly into digestive turbulence. Practicing mindful relaxation and structured unwinding protocols is essential.
                    </div>
                </div>

                <div class="mr-card">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:700; color:#047857; margin-bottom:4px;">
                        2. Exalted Mars: Invincible Cellular Immunity
                    </div>
                    <div style="font-size: 11.9px; color:#374151; line-height:1.48;">
                        Your chart is blessed with peak physical vitality courtesy of exalted Mars. Mars governs bone marrow, red blood cells, and cellular regeneration. Its direct aspect onto your Lagna ensures rapid recovery from fatigue and natural resistance against chronic degeneration. Channeling this immense physical energy through structured resistance training and cardiovascular discipline keeps your bio-field immaculate.
                    </div>
                </div>
            </div>

            <!-- Health Preservation Protocols -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:11px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:5px;">
                    Ayurvedic Dinacharya Protocols for Rahu Mahadasha
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">Grounding Nutrition:</strong>
                        Favor warm, freshly cooked foods rich in healthy fats (ghee, almonds) to pacify Vata. Minimize excessive raw salads, iced beverages, and erratic eating schedules.
                    </div>
                    <div>
                        <strong style="color:#047857;">Strict Sleep Hygiene:</strong>
                        Rahu tends to stimulate midnight intellectual surges. Disconnect from digital devices by 10:00 PM and take a warm magnesium bath or foot massage with sesame oil.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">Daily Solar Exposure:</strong>
                        Morning sunlight (Surya Namaskar) between 6:30 and 7:30 AM strengthens your 2nd lord Sun, soothing nervous anxiety and enhancing natural melatonin production.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Arogya' => 'Bio-Energetic Health & Cellular Longevity',
                'Pitta-Vata' => 'Constitutional Blend of Biological Heat & Movement',
                'Dinacharya' => 'Daily Rhythmic Lifestyle Aligning with Cosmic Solar Clock',
                'Surya Namaskar' => 'Solar Salutation Awakening Physical Vitality'
            ], 'Wellness Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Rahu Mahadasha provides high cognitive energy, requiring intentional physical grounding to prevent nervous burnout.",
                "Exalted Mars delivers extraordinary cellular immunity, rapid recovery, and unyielding biological stamina.",
                "Prioritizing warm grounding foods, regular sleep cycles, and morning solar exposure keeps your vital reserves at peak levels.",
                "Adhering to these Ayurvedic lifestyle protocols ensures robust physical vitality throughout all 18 years of the major cycle."
            ], 'Health & Vitality Takeaways'); ?>

            <?php echo self::render_page_footer(14, 'Life Domains: Health, Vitality &amp; Resilience'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 15: YOGINI DASHA: 36-YEAR KARMIC CYCLE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Yogini Dasha 36-Year Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">14 · Yogini Dasha: 36-Year Timing Cycle</div>
            <h1 class="mr-page-h1">Yogini Dasha: Understanding Your 36-Year Secondary Cycle</h1>
            <div class="mr-page-sub">A traditional timing system that shows inner moods, emotional growth, and special life lessons</div>

            <!-- Yogini 8 Archetypes Architecture Grid -->
            <table class="mr-dasha-table" style="font-size: 10.2px; margin-bottom:6px;">
                <colgroup>
                    <col style="width: 16%;">
                    <col style="width: 17%;">
                    <col style="width: 13%;">
                    <col style="width: 18%;">
                    <col style="width: 36%;">
                </colgroup>
                <thead>
                    <tr style="background:#FAF7F2; border-bottom:2px solid #C2964C;">
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Yogini Name</th>
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Ruling Planet</th>
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Span (Years)</th>
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Active Cycle Status</th>
                        <th style="padding:4px 6px; text-align:left; border:1px solid #ECE6DD;">Operative Psychological &amp; Material Quality</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">1. Mangala</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#1D4ED8;">Moon (Chandra)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">1 Year</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Upcoming</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Spiritual illumination, mental contentment, auspicious family ceremonies, and domestic joy.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">2. Pingala</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#B91C1C;">Sun (Surya)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">2 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Upcoming</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Government recognition, societal authority, high administrative responsibility, and public honor.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">3. Dhanya</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#C2964C;">Jupiter (Guru)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">3 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Upcoming</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Abundance, agricultural or real estate prosperity, scholarly distinction, and institutional growth.</td>
                    </tr>
                    <tr style="background:#FEF3C7; border:1.5px solid #D97706;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:800; color:#92400E;">4. Bhramari</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#C45738;">Mars (Mangal)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700;">4 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:800; color:#C45738;">CURRENTLY ACTIVE</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#78350F;">Dynamic mobility, aggressive market expansion, extensive executive travel, and competitive conquest.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">5. Bhadrika</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#047857;">Mercury (Budh)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">5 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#047857; font-weight:700;">Commencing Next</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Commercial intelligence, media and publication triumphs, lucrative contracts, and diplomatic honors.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">6. Ulka</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Saturn (Shani)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">6 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Future Cycle</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Endurance testing, structural reorganization, dismantling of weak systems, and karmic resilience.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">7. Siddha</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#047857;">Venus (Shukra)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">7 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Future Cycle</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Total fulfillment of desires, artistic and luxury wealth, romantic bliss, and international recognition.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">8. Sankata</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Rahu (North Node)</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD;">8 Years</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#6B7280;">Future Cycle</td>
                        <td style="padding:3px 6px; border:1px solid #ECE6DD; color:#4B5563;">Radical life disruption, unexpected breakthrough leaps, high-stakes exposure, and spiritual surrender.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Deep Analysis: Active Bhramari Phase -->
            <div class="mr-grid-2col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Active Bhramari Phase (Mars Ruler · 4 Years)
                    </div>
                    <div style="color:#4B5563;">
                        You are currently navigating the dynamic 4-year <strong>Bhramari Yogini Dasha</strong>, ruled by Mars. Because your natal Mars is deeply exalted in Capricorn (7th Kendra), this phase supercharges physical mobility, competitive enterprise expansion, and high-stakes negotiations across distant territories.
                    </div>
                </div>

                <div class="mr-card" style="padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#047857; margin-bottom:2px;">
                        Upcoming Bhadrika Transition (Mercury Ruler · 5 Years)
                    </div>
                    <div style="color:#4B5563;">
                        Following Bhramari, you transition into the highly auspicious 5-year <strong>Bhadrika Yogini Dasha</strong>, governed by Mercury. This phase shifts your focus from aggressive expansion to commercial intellect, publication, software distribution, and massive liquidity compounding.
                    </div>
                </div>
            </div>

            <!-- Yogini Philosophy Box -->
            <div class="mr-callout-box" style="border-left-color:#C2964C; padding:5px 12px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:2px;">
                    The Wisdom of Yogini Cross-Verification
                </div>
                <div style="font-size: 10.4px; color:#374151; line-height:1.33;">
                    While Vimshottari Dasha maps external karmic circumstances and material opportunities, the 36-year Yogini cycle reveals your internal psychological state and subconscious readiness. When both cycles harmonize, monumental life leaps occur with absolute natural grace.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Yogini Dasha' => '36-Year Classical North Indian Predictive Cycle',
                'Bhramari' => '4-Year Active Mars Cycle of Dynamic Mobility & Conquest',
                'Bhadrika' => '5-Year Auspicious Mercury Cycle of Intellectual Prosperity',
                'Subconscious Rhythm' => 'Internal Readiness to Harvest External Opportunities'
            ], 'Yogini Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Yogini Dasha provides a powerful parallel timing framework, decoding internal subconscious readiness and psychological cycles.",
                "Currently active in the 4-year Bhramari phase, ruled by exalted Mars, driving massive physical momentum and enterprise travel.",
                "The upcoming 5-year Bhadrika phase (ruled by Mercury) unlocks intellectual breakthroughs, high-value contracts, and liquidity.",
                "Cross-referencing Yogini periods with your Vimshottari progression isolates bulletproof windows for life-defining decisions."
            ], 'Yogini Cycle Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(15, 'Yogini Dasha: 36-Year Secondary Karmic Cycle'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 16: PARALLEL TIMING MATRIX & DUAL-SYSTEM SYNC
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Parallel Timing Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">15 · Comparing Both Timing Systems</div>
            <h1 class="mr-page-h1">Double-Checking Timing: Vimshottari &amp; Yogini Together</h1>
            <div class="mr-page-sub">Finding the best golden windows when both ancient timing systems point to easy success</div>

            <!-- Strategic Feature 2: Dual-System Synchronicity Table with Bulletproof Badges -->
            <table class="mr-dasha-table" style="font-size: 10.6px; margin-bottom:6px;">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 19%;">
                    <col style="width: 18%;">
                    <col style="width: 41%;">
                </colgroup>
                <thead>
                    <tr style="background:#FAF7F2; border-bottom:2px solid #C2964C;">
                        <th style="padding:4.5px 8px; text-align:left; border:1px solid #ECE6DD;">Execution Window</th>
                        <th style="padding:4.5px 8px; text-align:left; border:1px solid #ECE6DD;">Vimshottari Sub-Period</th>
                        <th style="padding:4.5px 8px; text-align:left; border:1px solid #ECE6DD;">Yogini Sub-Period</th>
                        <th style="padding:4.5px 8px; text-align:left; border:1px solid #ECE6DD;">Dual-System Synthesis &amp; Verdict</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#F0FDF4; border:1.5px solid #16A34A;">
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#15803D;">
                            2026 – 2028 (Apex 1)
                            <div style="font-size: 9.5px; font-weight:800; color:#FFFFFF; background:#16A34A; padding:1px 5px; border-radius:6px; display:inline-block; margin-top:2px;">
                                🛡️ BULLETPROOF WINDOW
                            </div>
                        </td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Rahu – Jupiter</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#047857;">Bhadrika (Mercury)</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; color:#14532D; font-size: 10.4px;">
                            <strong>Golden Commercial Convergence:</strong> Jupiter (fortune &amp; advisory) joins Mercury (commercial wit). Ideal for massive corporate expansion, capital raises, and landmark acquisitions.
                        </td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">
                            2029 – 2031 (Consolidation)
                            <div style="font-size: 9.5px; font-weight:700; color:#C2964C; background:#FAF7F2; padding:1px 5px; border-radius:6px; border:1px solid #E2DACF; display:inline-block; margin-top:2px;">
                                High-Discipline Window
                            </div>
                        </td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Rahu – Saturn</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#4B5563;">Ulka (Saturn)</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; color:#374151; font-size: 10.4px;">
                            <strong>Double Shani Fortification:</strong> Both systems trigger Saturnian operational audits. Demands lean overhead, compliance restructuring, and locking in long-term enterprise assets.
                        </td>
                    </tr>
                    <tr style="background:#F0FDF4; border:1.5px solid #16A34A;">
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#15803D;">
                            2035 – 2038 (Apex 2)
                            <div style="font-size: 9.5px; font-weight:800; color:#FFFFFF; background:#16A34A; padding:1px 5px; border-radius:6px; display:inline-block; margin-top:2px;">
                                🛡️ BULLETPROOF WINDOW
                            </div>
                        </td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Rahu – Venus</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#047857;">Siddha (Venus)</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; color:#14532D; font-size: 10.4px;">
                            <strong>Supreme Lakshmi Resonance:</strong> Simultaneous activation of Venus as Atmakaraka in Vimshottari and ruler of Siddha in Yogini. Peak lifetime wealth, luxury holdings, and international honors.
                        </td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">
                            2034 – 2035 (Calibration)
                            <div style="font-size: 9.5px; font-weight:700; color:#B45309; background:#FEF3C7; padding:1px 5px; border-radius:6px; border:1px solid #F59E0B; display:inline-block; margin-top:2px;">
                                Restorative Window
                            </div>
                        </td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#DC2626;">Rahu – Ketu</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; font-weight:700; color:#C45738;">Bhramari Sub-Cycle</td>
                        <td style="padding:4.5px 8px; border:1px solid #ECE6DD; color:#78350F; font-size: 10.4px;">
                            <strong>Nodal Realignment:</strong> Avoid high-risk speculative gambles. Excellent for health rejuvenation, inward contemplation, and pruning unproductive commercial liabilities.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Analysis Cards: Dual System Architecture -->
            <div class="mr-grid-2col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="border-left:3.5px solid #16A34A; padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#15803D; margin-bottom:2px;">
                        The Anatomy of a "Bulletproof Window"
                    </div>
                    <div style="color:#374151;">
                        When both Vimshottari and Yogini systems point in the same benefic direction, probability turns into astrological certainty. The external cosmic environment (Vimshottari) and your internal psychological readiness (Yogini) align into effortless execution, eliminating common obstacles.
                    </div>
                </div>

                <div class="mr-card" style="border-left:3.5px solid #C2964C; padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C2964C; margin-bottom:2px;">
                        Deployment Strategy for Dual Alignments
                    </div>
                    <div style="color:#374151;">
                        Schedule all irreversible high-stakes commitments (signing long-term commercial contracts, acquiring multi-million properties, launching enterprises) strictly within these confirmed Bulletproof Windows to ensure compounding velocity.
                    </div>
                </div>
            </div>

            <!-- Callout Directive -->
            <div class="mr-callout-box" style="border-left-color:#16A34A; padding:5px 12px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:2px;">
                    Executive Dual-Timing Mandate
                </div>
                <div style="font-size: 10.4px; color:#374151; line-height:1.33;">
                    The convergence of Rahu-Guru with Bhadrika (2026–2028) and Rahu-Venus with Siddha (2035–2038) represents your two greatest astrological windows of the decade. Prepare resources in advance to maximize these exceptional alignments.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Bulletproof Window' => 'Dual-System Affirmation of Favorable Cosmic Timing',
                'Bhadrika Sync' => 'Mercury-Jupiter Convergence of Knowledge & Capital',
                'Siddha Sync' => 'Venus-Venus Convergence of Luxury, Equity & Honors',
                'Dual-System' => 'Vimshottari (Environment) + Yogini (Psychology)'
            ], 'Synchronization Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "The Parallel Timing Matrix isolates rare high-conviction windows where both master timing systems align with precision.",
                "2026–2028 stands as an extraordinary Bulletproof Window for executive enterprise expansion and high-yield capital deployment.",
                "2035–2038 marks the supreme lifetime pinnacle of material luxury, domestic contentment, and sovereign societal honor.",
                "Aligning major irreversible commitments with these verified dual windows guarantees frictionless execution and compounding rewards."
            ], 'Parallel Timing Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(16, 'Parallel Timing Matrix · Dual-System Sync'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 17: MICRO-TIMING: PRATYANTARDASHA PRECISION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Pratyantardasha Precision</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">16 · Micro-Timing: Day-to-Day Precision</div>
            <h1 class="mr-page-h1">Pratyantardasha: Day-to-Day Micro-Timing for Key Events</h1>
            <div class="mr-page-sub">Pinpointing the exact weeks and months for important decisions, pitches, and new starts</div>

            <!-- Pratyantardasha Granular Breakdown Table -->
            <table class="mr-dasha-table" style="font-size: 10.4px; margin-bottom:6px;">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 21%;">
                    <col style="width: 17%;">
                    <col style="width: 40%;">
                </colgroup>
                <thead>
                    <tr style="background:#FAF7F2; border-bottom:2px solid #C2964C;">
                        <th style="padding:4px 7px; text-align:left; border:1px solid #ECE6DD;">Pratyantardasha Cycle</th>
                        <th style="padding:4px 7px; text-align:left; border:1px solid #ECE6DD;">Exact Temporal Window</th>
                        <th style="padding:4px 7px; text-align:left; border:1px solid #ECE6DD;">Energy Metric</th>
                        <th style="padding:4px 7px; text-align:left; border:1px solid #ECE6DD;">Operative Focus &amp; Weekly Directives</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:700; color:#C45738;">Rahu – Rahu – Mercury</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:600; color:#111827;">May 2026 – Sep 2026</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#047857; font-weight:700;">High Velocity (88%)</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#374151;">Rapid digital deployments, contract signings, commercial negotiations, and analytical audits.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:700; color:#DC2626;">Rahu – Rahu – Ketu</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:600; color:#111827;">Sep 2026 – Nov 2026</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#B45309; font-weight:700;">Restorative (52%)</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#374151;">Final cleansing of Swabhukti. Avoid launching aggressive marketing; conduct back-office optimization.</td>
                    </tr>
                    <tr style="background:#F0FDF4; border:1px solid #16A34A;">
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:700; color:#15803D;">Rahu – Jupiter – Jupiter</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:600; color:#111827;">Nov 2026 – Mar 2027</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#15803D; font-weight:800;">Peak Sovereign (96%)</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#14532D;"><strong>Ingress of Grace:</strong> Formalize high-level enterprise joint ventures, expand advisory retainers, and accept civic honors.</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:700; color:#C2964C;">Rahu – Jupiter – Saturn</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:600; color:#111827;">Mar 2027 – Jul 2027</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#C2964C; font-weight:700;">Structural (84%)</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#374151;">Institutional governance, rigorous operational protocols, and commercial property acquisitions.</td>
                    </tr>
                    <tr style="background:#FAF7F2;">
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:700; color:#047857;">Rahu – Jupiter – Mercury</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; font-weight:600; color:#111827;">Jul 2027 – Nov 2027</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#047857; font-weight:800;">Commercial Surge (92%)</td>
                        <td style="padding:3.5px 7px; border:1px solid #ECE6DD; color:#374151;">Intellectual property monetization, high-volume capital compounding, and media distribution triumphs.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Micro-Timing Analysis Cards -->
            <div class="mr-grid-2col" style="gap:10px; margin-bottom:5px;">
                <div class="mr-card" style="padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#15803D; margin-bottom:2px;">
                        The Power of Pratyantar Calibration
                    </div>
                    <div style="color:#4B5563;">
                        While a Mahadasha sets the multi-year climate and an Antardasha defines the annual season, the Pratyantardasha (sub-sub-period) acts as the weekly weather dial. By planning around Pratyantar rulers, you sidestep unproductive resistance and hit deadlines with surgical precision.
                    </div>
                </div>

                <div class="mr-card" style="padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Navigating Ingress Boundaries
                    </div>
                    <div style="color:#4B5563;">
                        The shift from Rahu-Rahu-Ketu into Rahu-Jupiter-Jupiter in late 2026 represents a massive energetic inflection point. Use the Ketu weeks for thorough preparation, clearing clutter and debt, so you hit the Jupiter window at full operational velocity.
                    </div>
                </div>
            </div>

            <!-- Strategic Callout Box -->
            <div class="mr-callout-box" style="border-left-color:#C2964C; padding:5px 12px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 12.5px; font-weight:700; color:#111827; margin-bottom:2px;">
                    Weekly Micro-Execution Directive
                </div>
                <div style="font-size: 10.4px; color:#374151; line-height:1.33;">
                    Prioritize major client contract executions, financial disbursements, and enterprise announcements during Mercury and Jupiter Pratyantardashas. Reserve Ketu and Saturn sub-sub-periods for inward system hardening and risk mitigation.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Pratyantardasha' => 'Tertiary Sub-Sub-Period (Governing Weeks to Months)',
                'Sukshma Krama' => 'Micro-Chronological Timing Analysis',
                'Ingress Shift' => 'Exact Date of Sub-Period Planetary Transition',
                'Energy Metric' => 'Quantitative Measure of Actionable Cosmic Velocity'
            ], 'Micro-Timing Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Pratyantardasha precision provides the tactical weekly compass necessary to navigate day-to-day business decisions.",
                "Rahu-Rahu-Mercury (mid-2026) offers intense commercial velocity for negotiations, licensing deals, and media distribution.",
                "Rahu-Jupiter-Jupiter (Nov 2026 – Mar 2027) unlocks a monumental wave of executive elevation, patronage, and capital gains.",
                "Synchronizing major weekly initiatives with Pratyantar rulers ensures minimum friction and maximum strategic leverage."
            ], 'Micro-Timing Precision Takeaways'); ?>

            <?php echo self::render_page_footer(17, 'Micro-Timing · Pratyantardasha Precision'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 18: HIGH-STAKES STRATEGIC EVENT CALENDAR
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Strategic Event Calendar</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">17 · Best Action Windows: Green &amp; Yellow Dates</div>
            <h1 class="mr-page-h1">Your Timing Calendar: Best Dates for Action &amp; Times to Rest</h1>
            <div class="mr-page-sub">Clear green-light dates for big moves, yellow dates for careful steps, and quiet dates for rest</div>

            <!-- Green-Light Execution Windows Card -->
            <div style="background:#F0FDF4; border:1.5px solid #16A34A; border-radius:8px; padding:11px 16px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:800; color:#15803D; text-transform:uppercase;">
                        🟢 Green-Light High-Conviction Execution Windows
                    </div>
                    <span style="font-size: 10.6px; font-weight:700; color:#FFFFFF; background:#16A34A; padding:2px 8px; border-radius:10px;">
                        MAXIMUM MOMENTUM
                    </span>
                </div>
                <div style="font-size: 11.5px; color:#14532D; line-height:1.45;">
                    <strong>Favorable Windows:</strong> Nov 2026 – Apr 2027 (Guru Ingress) · Jul 2027 – Nov 2027 (Budh Pratyantar) · Feb 2032 – Aug 2033 (Rahu-Mercury Expansion).<br>
                    <strong>Action Directives:</strong> Execute high-stakes corporate contracts, incorporate new enterprises, sign commercial property deeds, launch public marketing campaigns, and deploy aggressive venture capital.
                </div>
            </div>

            <!-- Yellow-Light Calibration Windows Card -->
            <div style="background:#FEFCE8; border:1.5px solid #CA8A04; border-radius:8px; padding:11px 16px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:800; color:#854D0E; text-transform:uppercase;">
                        🟡 Yellow-Light Calibration &amp; Optimization Zones
                    </div>
                    <span style="font-size: 10.6px; font-weight:700; color:#854D0E; background:#FEF08A; padding:2px 8px; border-radius:10px; border:1px solid #EAB308;">
                        STRATEGIC PRUDENCE
                    </span>
                </div>
                <div style="font-size: 11.5px; color:#713F12; line-height:1.45;">
                    <strong>Favorable Windows:</strong> Mar 2027 – Jul 2027 (Shani Sub-Sub) · Apr 2029 – Aug 2029 (Saturn Ingress Cusp) · Sep 2034 – Feb 2035.<br>
                    <strong>Action Directives:</strong> Focus on internal balance-sheet audits, back-office system automation, tax efficiency planning, organizational restructurings, and key team member performance reviews.
                </div>
            </div>

            <!-- Red-Light Caution Windows Card -->
            <div style="background:#FEF2F2; border:1.5px solid #DC2626; border-radius:8px; padding:11px 16px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 14.4px; font-weight:800; color:#991B1B; text-transform:uppercase;">
                        🔴 Red-Light Caution &amp; Boundary Protection Windows
                    </div>
                    <span style="font-size: 10.6px; font-weight:700; color:#FFFFFF; background:#DC2626; padding:2px 8px; border-radius:10px;">
                        DEFENSIVE POSITIONING
                    </span>
                </div>
                <div style="font-size: 11.5px; color:#7F1D1D; line-height:1.45;">
                    <strong>Favorable Windows:</strong> Sep 2026 – Nov 2026 (Swabhukti Ketu Cusp) · Aug 2034 – Sep 2035 (Rahu-Ketu) · Solar &amp; Lunar Eclipses across Cancer/Capricorn.<br>
                    <strong>Action Directives:</strong> Avoid unhedged speculative trading, do not issue emotional ultimatums, reject unverified joint ventures, and delay major irreversible legal commitments. Practice physical grounding and perform prescribed planetary daan.
                </div>
            </div>

            <!-- Tactical Decision Rule Summary -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:10px 16px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.8px; font-weight:700; color:#111827; margin-bottom:3px;">
                    The Golden Rule of Vedic Timing
                </div>
                <div style="font-size: 11.5px; color:#4B5563; line-height:1.45;">
                    Vedic timing does not promote passive waiting; it empowers offensive action during green windows and defensive mastery during red windows. Aligning your enterprise decisions with this calendar converts cosmic volatility into predictable competitive advantage.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Green Window' => 'Optimal Planetary Support for Expansion & Signing',
                'Yellow Window' => 'Internal Calibration, Audits & Structural Hardening',
                'Red Window' => 'Defensive Preservation & Boundary Protection',
                'Kala Chakra' => 'Actionable Cycle of Strategic Time Mastery'
            ], 'Calendar Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "The Strategic Event Calendar maps clear action thresholds, categorizing execution windows into Green, Yellow, and Red zones.",
                "Green-light windows (late 2026, mid 2027) demand aggressive offensive execution, major venture launches, and contract signings.",
                "Yellow zones provide essential operational breathing room to audit systems, optimize balance sheets, and train leadership teams.",
                "Strict defensive discipline during Red caution periods safeguards your capital, reputation, and health against sudden volatility."
            ], 'Strategic Event Calendar Takeaways'); ?>

            <?php echo self::render_page_footer(18, 'High-Stakes Strategic Event Calendar'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 19: REMEDIAL UPAYAS & WEEKLY PLANETARY DAAN SCHEDULE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Remedial Upayas &amp; Daan Schedule</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">18 · Simple Remedies &amp; Daily Habits</div>
            <h1 class="mr-page-h1">Simple Daily Remedies &amp; Weekly Acts of Kindness (Daan)</h1>
            <div class="mr-page-sub">Recommended gemstones, Rudraksha, easy mantras, and simple charity for positive planetary energy</div>

            <!-- Gemstone & Rudraksha Alignment Box -->
            <div class="mr-grid-2col" style="gap:10px; margin-bottom:6px;">
                <div class="mr-card" style="border-left:3.5px solid #047857; padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#047857; margin-bottom:2px;">
                        Prescribed Gemstone Architecture (Ratna Vigyan)
                    </div>
                    <div style="color:#374151;">
                        • <strong>Natural South Sea Pearl (Moti):</strong> Strengthens Lagna lord Moon, soothing nervous Vata waves and elevating mental intuition (Little finger, Silver, Monday morning).<br>
                        • <strong>Yellow Sapphire (Pukhraj):</strong> Empowers 9th lord Jupiter, drawing divine fortune, ethical clarity, and institutional mentors (Index finger, Gold, Thursday morning).<br>
                        • <em>Caution:</em> Strictly avoid Hessonite (Gomed) or Blue Sapphire (Neelam) without expert trial during Rahu Mahadasha.
                    </div>
                </div>

                <div class="mr-card" style="border-left:3.5px solid #C45738; padding:5px 10px; font-size: 10.4px; line-height:1.33;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 12.8px; font-weight:700; color:#C45738; margin-bottom:2px;">
                        Bio-Electromagnetic Rudraksha Synergy
                    </div>
                    <div style="color:#374151;">
                        • <strong>8-Mukhi Rudraksha (Lord Ganesha / Rahu):</strong> Grounding electrical restlessness, removing hidden obstacles, and purifying Rahu dasha currents.<br>
                        • <strong>5-Mukhi Rudraksha (Lord Shiva / Guru):</strong> Enhancing mental focus, regulating blood pressure, and aligning daily actions with higher wisdom.<br>
                        • <strong>11-Mukhi Rudraksha (11 Rudras / Exalted Mars):</strong> Bestowing physical fearlessness, executive stamina, and supreme legal defense.
                    </div>
                </div>
            </div>

            <!-- Strategic Feature 3: Actionable Weekly Planetary Daan & Mantra Prescription Schedule -->
            <div style="margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.2px; font-weight:800; color:#111827; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.04em;">
                    📅 Actionable Weekly Planetary Daan &amp; Mantra Prescription Schedule
                </div>
                <table class="mr-dasha-table" style="font-size: 10.2px;">
                    <colgroup>
                        <col style="width: 14%;">
                        <col style="width: 17%;">
                        <col style="width: 34%;">
                        <col style="width: 35%;">
                    </colgroup>
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:2px solid #C2964C;">
                            <th style="padding:3.5px 6px; text-align:left; border:1px solid #ECE6DD;">Weekday</th>
                            <th style="padding:3.5px 6px; text-align:left; border:1px solid #ECE6DD;">Target Planet</th>
                            <th style="padding:3.5px 6px; text-align:left; border:1px solid #ECE6DD;">Prescribed Charitable Act (Daan)</th>
                            <th style="padding:3.5px 6px; text-align:left; border:1px solid #ECE6DD;">Sacred Vedic Mantra / Stotra (108x)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#FFFFFF;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Monday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#1D4ED8; font-weight:700;">Chandra (Moon)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Donate milk, white rice, or silver to elderly women or temples.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Om Som Somaya Namah</td>
                        </tr>
                        <tr style="background:#FAF7F2;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Tuesday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#C45738; font-weight:700;">Mangal (Mars)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Donate red lentils (masoor dal) or jaggery; feed stray dogs.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Hanuman Chalisa / Om Angarakaya Namah</td>
                        </tr>
                        <tr style="background:#FFFFFF;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Wednesday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#047857; font-weight:700;">Budh (Mercury)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Feed green grass or spinach to cows; donate books to students.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Om Budhaya Namah / Vishnu Sahasranama</td>
                        </tr>
                        <tr style="background:#FAF7F2;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Thursday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#C2964C; font-weight:700;">Guru (Jupiter)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Donate chana dal, yellow bananas, or turmeric to priests/scholars.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Om Brihaspataye Namah / Guru Paduka Stotram</td>
                        </tr>
                        <tr style="background:#FFFFFF;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Friday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#047857; font-weight:700;">Shukra (Venus)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Donate kheer, curd, or white clothing to underprivileged women.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Om Shukraya Namah / Sri Suktam</td>
                        </tr>
                        <tr style="background:#FAF7F2; border-left:3px solid #C45738;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:800; color:#C45738;">Saturday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563; font-weight:800;">Shani &amp; Rahu</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#78350F;">Light mustard oil lamp under Peepal; donate black urad/blankets.</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#92400E; font-weight:700;">Maha Mrityunjaya / Om Rahave Namah</td>
                        </tr>
                        <tr style="background:#FFFFFF;">
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; font-weight:700; color:#111827;">Sunday</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#B91C1C; font-weight:700;">Surya (Sun)</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#4B5563;">Offer water in a copper vessel with kumkum at sunrise (Arghya).</td>
                            <td style="padding:2.8px 6px; border:1px solid #ECE6DD; color:#111827; font-weight:600;">Gayatri Mantra / Aditya Hridaya Stotra</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_symbol_guide_bar([
                'Upaya' => 'Prescriptive Remedial Science of Karmic Neutralization',
                'Daan' => 'Sacred Charitable Giving Purifying Planetary Energy',
                'Prana Pratishtha' => 'Ritual Consecration Awakening Gemstone Energy',
                'Stotra' => 'Hymn of Praise Harmonizing Bio-Resonant Frequencies'
            ], 'Remedial Terminology'); ?>

            <?php echo self::render_page_bottom_synthesis([
                "Natural South Sea Pearl and Yellow Sapphire empower your functional benefics, providing emotional equanimity and wealth.",
                "An 8-Mukhi Rudraksha calms Rahu’s erratic electrical restlessness, grounding your mental focus during high-stakes ventures.",
                "The Weekly Planetary Daan Schedule acts as an active karmic filter, systematically dissolving latent friction on designated days.",
                "Executing these targeted spiritual disciplines bridges conscious human initiative with sublime cosmic grace."
            ], 'Remedial Upayas Strategic Takeaways'); ?>

            <?php echo self::render_page_footer(19, 'Remedial Upayas · Weekly Daan Schedule'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 20: MASTER GLOSSARY, BENEDICTION & BACK COVER
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta">MeraRashifal · Astrological Catalogue &amp; Concluding Benediction</span>
        </div>

        <div class="mr-page-body">
            <div style="text-align:center; margin-bottom:8px;">
                <div class="mr-tag-pill" style="margin-bottom:6px;">Sacred Vedic Library &amp; Concluding Benediction</div>
                <h1 class="mr-page-h1" style="font-size:20px; margin-bottom:4px;">Thank You, <?php echo esc_html($person_name); ?> ji!</h1>
                <div class="mr-page-sub" style="margin-bottom:6px; font-size: 13.8px;">Explore our complete collection of personalized astrology reports for you and your family</div>
            </div>

            <!-- Sacred Vedic Shloka Callout Banner -->
            <div style="background:#FDF8F3; border:1.5px solid #C2964C; border-radius:8px; padding:10px 18px; margin-bottom:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                <div style="font-size: 12px; font-weight:700; color:#C45738; letter-spacing:0.04em; margin-bottom:3px;">
                    ॥ ॐ असतो मा सद्गमय । तमसो मा ज्योतिर्गमय । मृत्योर्माऽमृतं गमय ॥ ॐ शान्तिः शान्तिः शान्तिः ॥
                </div>
                <div style="font-size: 11.9px; color:#4B5563; font-style:italic;">
                    “Lead me from ignorance to divine truth, from darkness to cosmic light, and from mortality to immortal consciousness.”
                </div>
            </div>

            <!-- Core 10-Point Dasha Terminology Glossary in 2 Columns -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 16px; font-size: 11.3px; margin-bottom:10px;">
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#C45738;">1. Mahadasha:</strong> Major 6 to 20-year planetary life epoch setting the macro karmic climate.
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#C45738;">2. Antardasha:</strong> Secondary sub-period governing specific 6 to 36-month execution windows.
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#047857;">3. Pratyantardasha:</strong> Tertiary sub-sub-period pinpointing exact weekly events and shifts.
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#047857;">4. Dasha Sandhi:</strong> The 6-month transitional cusp between two major planetary cycles.
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#C2964C;">5. Yogini Dasha:</strong> 36-year secondary North Indian timing cycle mapping subconscious rhythms.
                </div>
                <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:6px; padding:5px 10px;">
                    <strong style="color:#C2964C;">6. Shadashtak:</strong> 6/8 mutual planetary relationship requiring legal and defensive caution.
                </div>
            </div>

            <!-- 4 Core Showcase Offer Cards (From Master Catalog) -->
            <div class="mr-offer-card">
                <div class="mr-offer-icon">💎</div>
                <div class="mr-offer-info">
                    <h4>Master Vedic Kundali &amp; Lifetime Destiny Dossier</h4>
                    <p>Exhaustive 26-page birth analysis covering D1, D9 Navamsha, all 16 divisional harmonics, Shadbala, Ashtakvarga, and 12-house life synthesis.</p>
                    <div class="mr-offer-meta">26 Pages · Full Parashari Synthesis · Large North Indian Diamond Kundali</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">☀️</div>
                <div class="mr-offer-info">
                    <h4>Annual Varshaphal (Solar Return) Master Report</h4>
                    <p>Comprehensive 26-page annual predictive blueprint from birthday to birthday, Muntha transit, Tajika dasha cycles, and month-by-month career roadmap.</p>
                    <div class="mr-offer-meta">26 Pages · Tajika Annual Engine · Month-by-Month Predictive Guidance</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">💍</div>
                <div class="mr-offer-info">
                    <h4>Kundali Matching (Gun Milan) &amp; Marriage Compatibility</h4>
                    <p>Authentic 36-Point Ashtakoot scorecard, Manglik Dosh check, Papasamaya malefic balance, Nadi &amp; Bhakoot analysis, and 20-year timeline sync.</p>
                    <div class="mr-offer-meta">20 Pages · Ashtakoot &amp; Dasha Synchronization · Dual Partner Vedic Analysis</div>
                </div>
            </div>

            <div class="mr-offer-card">
                <div class="mr-offer-icon">🛡️</div>
                <div class="mr-offer-info">
                    <h4>Kundali Dosh Analysis &amp; Vedic Remedial Dossier</h4>
                    <p>In-depth diagnosis of Manglik, Kaal Sarp, Pitra Dosh, and Shani Sade Sati/Dhaiya with certified gemstones, sacred mantras, and lifestyle remedies.</p>
                    <div class="mr-offer-meta">20 Pages · 90-Day Karmic Harmonization Plan · Certified Vedic Remedial Solutions</div>
                </div>
            </div>

            <!-- Ethical Philosophy Card -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:8px; padding:9px 16px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 13.5px; font-weight:700; color:#111827; margin-bottom:2px;">
                    Vedic Timing as an Empowering Navigation Instrument
                </div>
                <p style="font-size: 11.3px; color:#4B5563; margin:0; line-height:1.42;">
                    Vedic Jyotish does not dictate a rigid fate; it illuminates the energetic currents of time (Kala) so you can navigate them with wisdom and purpose. By aligning sincere human effort (Purushartha) with favorable planetary dashas, every challenge transforms into a stepping stone toward enduring mastery.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <div style="border-top:1px solid #ECE6DD; padding-top:6px; margin-bottom:6px; text-align:center; font-size: 11.3px; color:#4B5563; line-height:1.45;">
                <div style="font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:2px;">
                    Access Your Personal Reports Archive &amp; Order Specialized Blueprints
                </div>
                <span>Visit your customer portal at: <strong style="color:#C45738;">www.MeraRashifal.com</strong></span><br>
                <span style="font-size: 8.4px; color:#6B7280; display:block; margin-top:2px;">
                    &copy; <?php echo date('Y'); ?> MeraRashifal. All rights reserved.
                </span>
            </div>
            <div class="mr-footer-bar" style="margin-top:2px;">
                <span>MeraRashifal · Sacred Concluding Benediction &amp; Report Suite</span>
                <span class="mr-page-num">Page 20 of 20</span>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
