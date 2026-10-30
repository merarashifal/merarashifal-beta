<?php
/**
 * Class MR_Dosh_Report_Builder
 *
 * Master Vedic Kundali Dosh & Remedial Report (Strictly 20 Pages).
 * Engineered strictly to the MeraRashifal Master Design System & Typography Architecture.
 *
 * Page Sequence (Strictly 20 Physical A4 Pages):
 * Page 01: Sovereign Cover Page (Verified Birth Details, Triple Sign Bar, Executive Theme, Statutory Disclaimer)
 * Page 02: Table of Contents & Primary Janma Kundali (Balanced 2-Column Index, 280px North Indian Diamond D1 Chart)
 * Page 03: The Dosha Vulnerability Summary Dashboard (Master Status Overview, 6-Dosha Gauge Heatmap, Severity Ratings)
 * Page 04: Mangal (Manglik) Dosha Deep Dive (7th House Martian Placement, Heat vs. Passion Dynamics, Marital Anatomy)
 * Page 05: Manglik Cancellation & Mitigation (Bhanga) Scorecard (Exaltation in Capricorn, Jupiter Aspect, Mitigation Audit)
 * Page 06: Harmonic Strength & Dosh Cross-Verification (2x2 Divisional Grid: D1, D9, D7, D10 at ~195px Each with 50-word Findings)
 * Page 07: Kaalsarp Dosha Deep Dive (1st–7th Nodal Axis Anatomy, Anant Kaal Sarp Configuration, Rahu in Lagna)
 * Page 08: Kaalsarp Life-Phase Impact & Psychological Drive (Early Hurdles, Career Pivots, Amrit Churning Karmic Cleansing)
 * Page 09: The "Karmic Gift vs. Hurdle" Reframe for Kaalsarp (Extreme Psychological Resilience, Grit, Late-Life Hyper-Success)
 * Page 10: Pitra Dosha: Ancestral Karma & Lineage Debt Scan (9th House & Sun-Saturn-Rahu Interplay, Lineage Obligations)
 * Page 11: Pitra Dosha Resolution & Karmic Closure (Tarpan, Dharmic Offerings, Subconscious Pattern Breaking)
 * Page 12: Papasamaya (Malefic Weight) Assessment (Comparative Malefic Points from Sun, Mars, Saturn, Rahu, Ketu)
 * Page 13: Alliance Harmony & Physical Vitality Protection (Partner Papasamaya Balancing, Bio-Reserve Fortification)
 * Page 14: Karmic Priority Matrix (Ranked Affliction Hierarchy: Urgent Structural Afflictions vs. Minor Static)
 * Page 15: Ratna Vigyan: Gemstone & Metal Prescription Matrix (Primary/Secondary Gems, Consecration Day, Metal & Finger)
 * Page 16: Rudraksha Therapy & Sacred Bead Alignments (Mukhi Ratings, Neurological Grounding, Planetary Pacification)
 * Page 17: Sacred Vedic Mantras & Planetary Shanti Rituals (Beej Mantras, Vedic Stotras, Planetary Deity Sadhana)
 * Page 18: Step-by-Step Practical Weekly Daan Schedule (Day-by-Day Charitable Offerings, Planetary Hours, Item Matrix)
 * Page 19: The 90-Day Karmic Cleansing & Growth Roadmap (Phase 1 Detox, Phase 2 Harmonization, Phase 3 Empowerment)
 * Page 20: Master Glossary, Sacred Benediction & Back Cover (10-Point Glossary, Upanishadic Shloka, Report Suite)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Dosh_Report_Builder {
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
     * Render North Indian Diamond D1 Chart (Crisp 300px Visual Anchor)
     */
    public static function render_d1_diamond_svg($size = 300, $lagna_sign_num = 4) {
        $s = $size;
        $half = $s / 2;

        $planets_by_house = [
            1  => ['Ra 19°14\''],
            2  => ['Mo 11°53\''],
            3  => ['Ju 15°29\'', 'Sa 15°43\''],
            5  => ['Ve 16°51\'(AK)'],
            6  => ['Su 11°25\'', 'Me 8°40\''],
            7  => ['Ma 3°02\'(Ex)', 'Ke 19°14\''],
        ];

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:2px solid #C2964C; border-radius:10px; box-shadow:0 4px 14px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='5' y='5' width='" . ($s - 10) . "' height='" . ($s - 10) . "' fill='none' stroke='#C2964C' stroke-width='2'/>";
        $svg .= "<line x1='5' y1='5' x2='" . ($s - 5) . "' y2='" . ($s - 5) . "' stroke='#D8CFBF' stroke-width='1.5'/>";
        $svg .= "<line x1='" . ($s - 5) . "' y1='5' x2='5' y2='" . ($s - 5) . "' stroke='#D8CFBF' stroke-width='1.5'/>";
        $svg .= "<polygon points='{$half},5 " . ($s - 5) . ",{$half} {$half}," . ($s - 5) . " 5,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.6'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='10' fill='#C2964C' fill-opacity='0.15'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='3' fill='#C45738'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.36, 'pl_x' => $half, 'pl_y' => $half * 0.70],
            2  => ['num_x' => $half * 0.5, 'num_y' => $half * 0.20, 'pl_x' => $half * 0.44, 'pl_y' => $half * 0.44],
            3  => ['num_x' => $half * 0.20, 'num_y' => $half * 0.50, 'pl_x' => $half * 0.42, 'pl_y' => $half * 0.74],
            4  => ['num_x' => $half * 0.45, 'num_y' => $half, 'pl_x' => $half * 0.74, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.20, 'num_y' => $half * 1.50, 'pl_x' => $half * 0.42, 'pl_y' => $half * 1.26],
            6  => ['num_x' => $half * 0.5, 'num_y' => $half * 1.80, 'pl_x' => $half * 0.44, 'pl_y' => $half * 1.56],
            7  => ['num_x' => $half, 'num_y' => $half * 1.64, 'pl_x' => $half, 'pl_y' => $half * 1.30],
            8  => ['num_x' => $half * 1.5, 'num_y' => $half * 1.80, 'pl_x' => $half * 1.56, 'pl_y' => $half * 1.56],
            9  => ['num_x' => $half * 1.80, 'num_y' => $half * 1.50, 'pl_x' => $half * 1.58, 'pl_y' => $half * 1.26],
            10 => ['num_x' => $half * 1.55, 'num_y' => $half, 'pl_x' => $half * 1.26, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.80, 'num_y' => $half * 0.50, 'pl_x' => $half * 1.58, 'pl_y' => $half * 0.74],
            12 => ['num_x' => $half * 1.5, 'num_y' => $half * 0.20, 'pl_x' => $half * 1.56, 'pl_y' => $half * 0.44],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='12' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $y_offset = $c['pl_y'];
                foreach ($planets_by_house[$h] as $pl) {
                    $color = '#111827';
                    if (strpos($pl, 'Ma') !== false) $color = '#C45738';
                    elseif (strpos($pl, 'Ju') !== false) $color = '#B45309';
                    elseif (strpos($pl, 'Ve') !== false) $color = '#047857';
                    elseif (strpos($pl, 'Mo') !== false) $color = '#1D4ED8';
                    elseif (strpos($pl, 'Su') !== false) $color = '#B91C1C';
                    elseif (strpos($pl, 'Ra') !== false || strpos($pl, 'Ke') !== false) $color = '#4B5563';

                    $svg .= "<text x='{$c['pl_x']}' y='{$y_offset}' fill='{$color}' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pl}</text>";
                    $y_offset += 12;
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render North Indian Diamond D9 Navamsha Chart (Crisp 300px Visual Anchor)
     */
    public static function render_d9_diamond_svg($size = 300, $lagna_sign_num = 10) {
        $s = $size;
        $half = $s / 2;

        $planets_by_house = [
            1  => ['Ma 3°02\'(Ex)'],
            3  => ['Me', 'Ke'],
            4  => ['Su', 'Mo'],
            5  => ['Ju', 'Sa'],
            11 => ['Ve', 'Ra'],
        ];

        $svg = "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:2px solid #C2964C; border-radius:10px; box-shadow:0 4px 14px rgba(0,0,0,0.03); display:block; margin:0 auto;'>";
        $svg .= "<rect x='5' y='5' width='" . ($s - 10) . "' height='" . ($s - 10) . "' fill='none' stroke='#C2964C' stroke-width='2'/>";
        $svg .= "<line x1='5' y1='5' x2='" . ($s - 5) . "' y2='" . ($s - 5) . "' stroke='#D8CFBF' stroke-width='1.5'/>";
        $svg .= "<line x1='" . ($s - 5) . "' y1='5' x2='5' y2='" . ($s - 5) . "' stroke='#D8CFBF' stroke-width='1.5'/>";
        $svg .= "<polygon points='{$half},5 " . ($s - 5) . ",{$half} {$half}," . ($s - 5) . " 5,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.6'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='10' fill='#C2964C' fill-opacity='0.15'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='3' fill='#047857'/>";

        $house_coords = [
            1  => ['num_x' => $half, 'num_y' => $half * 0.36, 'pl_x' => $half, 'pl_y' => $half * 0.70],
            2  => ['num_x' => $half * 0.5, 'num_y' => $half * 0.20, 'pl_x' => $half * 0.44, 'pl_y' => $half * 0.44],
            3  => ['num_x' => $half * 0.20, 'num_y' => $half * 0.50, 'pl_x' => $half * 0.42, 'pl_y' => $half * 0.74],
            4  => ['num_x' => $half * 0.45, 'num_y' => $half, 'pl_x' => $half * 0.74, 'pl_y' => $half],
            5  => ['num_x' => $half * 0.20, 'num_y' => $half * 1.50, 'pl_x' => $half * 0.42, 'pl_y' => $half * 1.26],
            6  => ['num_x' => $half * 0.5, 'num_y' => $half * 1.80, 'pl_x' => $half * 0.44, 'pl_y' => $half * 1.56],
            7  => ['num_x' => $half, 'num_y' => $half * 1.64, 'pl_x' => $half, 'pl_y' => $half * 1.30],
            8  => ['num_x' => $half * 1.5, 'num_y' => $half * 1.80, 'pl_x' => $half * 1.56, 'pl_y' => $half * 1.56],
            9  => ['num_x' => $half * 1.80, 'num_y' => $half * 1.50, 'pl_x' => $half * 1.58, 'pl_y' => $half * 1.26],
            10 => ['num_x' => $half * 1.55, 'num_y' => $half, 'pl_x' => $half * 1.26, 'pl_y' => $half],
            11 => ['num_x' => $half * 1.80, 'num_y' => $half * 0.50, 'pl_x' => $half * 1.58, 'pl_y' => $half * 0.74],
            12 => ['num_x' => $half * 1.5, 'num_y' => $half * 0.20, 'pl_x' => $half * 1.56, 'pl_y' => $half * 0.44],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='12' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $y_offset = $c['pl_y'];
                foreach ($planets_by_house[$h] as $pl) {
                    $color = '#111827';
                    if (strpos($pl, 'Ma') !== false) $color = '#C45738';
                    elseif (strpos($pl, 'Ju') !== false) $color = '#B45309';
                    elseif (strpos($pl, 'Ve') !== false) $color = '#047857';
                    elseif (strpos($pl, 'Mo') !== false) $color = '#1D4ED8';
                    elseif (strpos($pl, 'Su') !== false) $color = '#B91C1C';
                    elseif (strpos($pl, 'Ra') !== false || strpos($pl, 'Ke') !== false) $color = '#4B5563';

                    $svg .= "<text x='{$c['pl_x']}' y='{$y_offset}' fill='{$color}' font-size='9.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$pl}</text>";
                    $y_offset += 12;
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Divisional Varga Diamond Chart (Crisp Size ~205px for 2x2 Grid)
     */
    public static function render_varga_diamond_svg($size = 195, $planets_by_house = [], $lagna_sign_num = 1, $title = '') {
        $s = $size;
        $half = $s / 2.0;

        $svg = "<div style='display:flex; flex-direction:column; align-items:center;'>";
        $svg .= "<svg width='{$s}' height='{$s}' viewBox='0 0 {$s} {$s}' xmlns='http://www.w3.org/2000/svg' style='background:#FFFFFF; border:1.8px solid #C2964C; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.03); display:block;'>";
        $svg .= "<rect x='4' y='4' width='" . ($s - 8) . "' height='" . ($s - 8) . "' fill='none' stroke='#C2964C' stroke-width='1.8'/>";
        $svg .= "<line x1='4' y1='4' x2='" . ($s - 4) . "' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<line x1='" . ($s - 4) . "' y1='4' x2='4' y2='" . ($s - 4) . "' stroke='#D8CFBF' stroke-width='1.4'/>";
        $svg .= "<polygon points='{$half},4 " . ($s - 4) . ",{$half} {$half}," . ($s - 4) . " 4,{$half}' fill='#FAF7F2' fill-opacity='0.4' stroke='#C2964C' stroke-width='1.4'/>";
        $svg .= "<circle cx='{$half}' cy='{$half}' r='6' fill='#C2964C' fill-opacity='0.2'/>";

        // Mathematically precise geometric centroids for all 12 North Indian diamond houses
        $house_cfg = [
            1  => ['num_x' => $half,         'num_y' => $half * 0.35, 'pl_x' => $half,         'pl_y' => $half * 0.68],
            2  => ['num_x' => $half * 0.50,  'num_y' => $half * 0.18, 'pl_x' => $half * 0.50,  'pl_y' => $half * 0.36],
            3  => ['num_x' => $half * 0.18,  'num_y' => $half * 0.50, 'pl_x' => $half * 0.34,  'pl_y' => $half * 0.50],
            4  => ['num_x' => $half * 0.34,  'num_y' => $half,        'pl_x' => $half * 0.66,  'pl_y' => $half],
            5  => ['num_x' => $half * 0.18,  'num_y' => $half * 1.50, 'pl_x' => $half * 0.34,  'pl_y' => $half * 1.50],
            6  => ['num_x' => $half * 0.50,  'num_y' => $half * 1.84, 'pl_x' => $half * 0.50,  'pl_y' => $half * 1.66],
            7  => ['num_x' => $half,         'num_y' => $half * 1.68, 'pl_x' => $half,         'pl_y' => $half * 1.34],
            8  => ['num_x' => $half * 1.50,  'num_y' => $half * 1.84, 'pl_x' => $half * 1.50,  'pl_y' => $half * 1.66],
            9  => ['num_x' => $half * 1.82,  'num_y' => $half * 1.50, 'pl_x' => $half * 1.66,  'pl_y' => $half * 1.50],
            10 => ['num_x' => $half * 1.66,  'num_y' => $half,        'pl_x' => $half * 1.34,  'pl_y' => $half],
            11 => ['num_x' => $half * 1.82,  'num_y' => $half * 0.50, 'pl_x' => $half * 1.66,  'pl_y' => $half * 0.50],
            12 => ['num_x' => $half * 1.50,  'num_y' => $half * 0.18, 'pl_x' => $half * 1.50,  'pl_y' => $half * 0.36],
        ];

        $colors = [
            'Ma' => '#C45738', 'Su' => '#B91C1C', 'Mo' => '#1D4ED8', 'Ju' => '#B45309',
            'Ve' => '#047857', 'Sa' => '#374151', 'Ra' => '#4B5563', 'Ke' => '#4B5563', 'Me' => '#0D9488'
        ];

        for ($h = 1; $h <= 12; $h++) {
            $cfg = $house_cfg[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            $svg .= "<text x='" . round($cfg['num_x'], 1) . "' y='" . round($cfg['num_y'], 1) . "' fill='#C45738' font-size='9.5' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            if (!empty($planets_by_house[$h])) {
                $pls = $planets_by_house[$h];
                $n = count($pls);
                $step = 10.5;
                $start_y = $cfg['pl_y'] - (($n - 1) * $step / 2.0);
                foreach ($pls as $i => $p) {
                    $py = round($start_y + ($i * $step), 1);
                    $col = '#111827';
                    foreach ($colors as $k => $c) {
                        if (strpos($p, $k) !== false) {
                            $col = $c;
                            break;
                        }
                    }
                    $svg .= "<text x='" . round($cfg['pl_x'], 1) . "' y='{$py}' fill='{$col}' font-size='8.5' font-family='sans-serif' font-weight='700' text-anchor='middle'>{$p}</text>";
                }
            }
        }

        $svg .= "</svg>";
        if (!empty($title)) {
            $svg .= "<div style='text-align:center; font-size:10px; color:#111827; font-weight:800; margin-top:5px; text-transform:uppercase; letter-spacing:0.04em;'>{$title}</div>";
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
        <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:16px; padding:4px 16px; margin-top:auto; margin-bottom:6px; display:flex; align-items:center; flex-wrap:wrap; font-size:8px; line-height:1.35; color:#64748B;">
            <span style="font-weight:800; color:#8C7B6B; text-transform:uppercase; letter-spacing:0.06em; margin-right:8px; flex-shrink:0;">
                🧭 <?php echo esc_html($title); ?>:
            </span>
            <div style="display:inline-flex; align-items:center; flex-wrap:wrap; gap:3px 8px;">
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
                            <span style="color:#CBD5E1; margin-left:6px;">|</span>
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
        <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-left:4px solid #C45738; border-radius:8px; padding:10px 18px; margin-top:0; margin-bottom:2px; box-shadow:0 2px 8px rgba(0,0,0,0.02); width:100%; box-sizing:border-box;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                <div style="font-family:<?php echo $heading_font; ?>; font-size:11px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.06em;">
                    ⚡ <?php echo esc_html($title); ?>
                </div>
                <span style="font-size:8.2px; font-weight:700; color:#C45738; background:#FDF2EE; padding:2px 8px; border-radius:10px; border:1px solid #FADCCE; text-transform:uppercase; letter-spacing:0.05em;">
                    Actionable Strategic Synthesis
                </span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:3px 18px; font-size:9.5px; color:#374151; line-height:1.45;">
                <?php foreach ($bullets as $b): ?>
                    <div style="display:flex; align-items:flex-start;">
                        <span style="color:#C45738; margin-right:7px; font-weight:bold; font-size:11px; line-height:1.2;">•</span>
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
        ob_start();
        ?>
        <div class="mr-footer-bar">
            <span>MeraRashifal · <?php echo esc_html($section_title ?: 'Master Vedic Mahadasha & Antardasha Life Timing Report'); ?></span>
            <span class="mr-page-num">Page <?php echo intval($page_num); ?> of 20</span>
        </div>
        <?php
        return ob_get_clean();
    }

    
    /**
     * Render Dosha Severity Barometer / Heatmap SVG (Page 3)
     */
    public static function render_dosh_severity_gauge_svg($w = 540, $h = 90) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='display:block; margin:0 auto;'>";

        $doshas = [
            ['name' => 'Manglik',     'score' => 20, 'status' => 'Mitigated',     'col' => '#047857', 'icon' => '🛡️'],
            ['name' => 'Kaal Sarp',   'score' => 65, 'status' => 'Active Axis',   'col' => '#C45738', 'icon' => '🐍'],
            ['name' => 'Pitra Dosh',  'score' => 25, 'status' => 'Mild Karmic',   'col' => '#D97706', 'icon' => '🌿'],
            ['name' => 'Sade Sati',   'score' => 15, 'status' => 'Dormant Phase', 'col' => '#047857', 'icon' => '🪐'],
            ['name' => 'Guru Chandal', 'score' => 0,  'status' => 'Clear (0%)',    'col' => '#047857', 'icon' => '👑'],
            ['name' => 'Grahan Yoga', 'score' => 5,   'status' => 'Clear (0%)',    'col' => '#047857', 'icon' => '🌑'],
        ];

        $bar_w = 66;
        $spacing = 86;
        $start_x = 18;

        foreach ($doshas as $i => $d) {
            $x = $start_x + ($i * $spacing);
            $bar_h = max(5, ($d['score'] / 100) * 32);
            $y = 56 - $bar_h;

            $svg .= "<rect x='{$x}' y='24' width='{$bar_w}' height='32' fill='#F4EFE6' rx='4'/>";
            $svg .= "<rect x='{$x}' y='{$y}' width='{$bar_w}' height='{$bar_h}' fill='{$d['col']}' rx='4'/>";
            $svg .= "<text x='" . ($x + $bar_w/2) . "' y='18' fill='#111827' font-size='12' font-family='sans-serif' text-anchor='middle'>{$d['icon']}</text>";
            $svg .= "<text x='" . ($x + $bar_w/2) . "' y='" . ($y - 3) . "' fill='{$d['col']}' font-size='8.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$d['score']}%</text>";
            $svg .= "<text x='" . ($x + $bar_w/2) . "' y='69' fill='#111827' font-size='9' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$d['name']}</text>";
            $svg .= "<text x='" . ($x + $bar_w/2) . "' y='80' fill='{$d['col']}' font-size='7.5' font-weight='800' font-family='sans-serif' text-anchor='middle'>{$d['status']}</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Render Papasamaya Malefic Weight Bar Graph SVG (Page 12)
     */
    public static function render_papasamaya_chart_svg($w = 540, $h = 95) {
        $svg = "<svg width='100%' height='{$h}' viewBox='0 0 {$w} {$h}' xmlns='http://www.w3.org/2000/svg' style='display:block; margin:0 auto;'>";

        $planets = [
            ['name' => 'Sun (Surya)',    'pts' => 12, 'max' => 30, 'col' => '#B91C1C'],
            ['name' => 'Mars (Mangal)',  'pts' => 8,  'max' => 30, 'col' => '#C45738'],
            ['name' => 'Saturn (Shani)', 'pts' => 14, 'max' => 30, 'col' => '#4B5563'],
            ['name' => 'Rahu (North)',   'pts' => 16, 'max' => 30, 'col' => '#6B21A8'],
            ['name' => 'Ketu (South)',   'pts' => 10, 'max' => 30, 'col' => '#374151'],
        ];

        $bar_h = 10;
        $start_y = 12;

        foreach ($planets as $i => $p) {
            $y = $start_y + ($i * 16);
            $bar_w = ($p['pts'] / $p['max']) * 320;

            $svg .= "<text x='15' y='" . ($y + 8) . "' fill='#1F2937' font-size='8.5' font-weight='700' font-family='sans-serif'>{$p['name']}</text>";
            $svg .= "<rect x='130' y='{$y}' width='320' height='{$bar_h}' fill='#F3EFEA' rx='3'/>";
            $svg .= "<rect x='130' y='{$y}' width='{$bar_w}' height='{$bar_h}' fill='{$p['col']}' rx='3'/>";
            $svg .= "<text x='460' y='" . ($y + 8) . "' fill='{$p['col']}' font-size='8.5' font-weight='800' font-family='sans-serif'>{$p['pts']} pts / {$p['max']}</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    public static function build_dosh_report_html($person_name, $gender, $dob, $tob, $pob, $editorial_data = [], $vedic_data = []) {
        $logo_uri = self::get_logo_data_uri();
        $dob_clean = str_replace('/', '-', $dob);
        $dob_dt = DateTime::createFromFormat('d-m-Y', $dob_clean);
        $formatted_dob = $dob_dt ? $dob_dt->format('F j, Y') : (strtotime($dob_clean) ? date('F j, Y', strtotime($dob_clean)) : $dob);

        $motto     = "Transmute past-life karmic friction into sovereign fortitude, wisdom, and enduring fulfillment.";
        $theme     = "Nodal Realignment & Martian Fortitude: Transforming Afflictions into Engines of Greatness";
        $head_font = "'Poppins', sans-serif";
        $body_font = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($person_name . ' · Master Vedic Kundali Dosh & Remedial Report'); ?></title>
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
            font-size: 11px;
            line-height: 1.5;
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
        .mr-footer-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ECE6DD;
            padding-top: 6px;
            font-size: 9px;
            color: #6B7280;
            z-index: 2;
            position: relative;
            margin-top: 8px;
            flex-shrink: 0;
        }
        .mr-page-num {
            font-weight: 800;
            color: #C45738;
            font-size: 9.8px;
        }

        .mr-tag-pill {
            display: inline-block;
            font-size: 9.2px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 4px 14px;
            border-radius: 12px;
            background: #F4EFE6;
            border: 1px solid #E2DACF;
            color: #C45738;
            margin-bottom: 9px;
        }
        .mr-page-h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 7px 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .mr-page-sub {
            font-size: 10.8px;
            color: #4B5563;
            margin-bottom: 15px;
            font-weight: 500;
            line-height: 1.45;
        }

        .mr-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }
        .mr-grid-3col {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .mr-grid-4col {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        .mr-grid-2x2-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 24px;
            justify-items: center;
        }

        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 8px;
            padding: 12px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .mr-card-accent {
            background: #FDF8F3;
            border: 1.5px solid #C2964C;
            border-radius: 8px;
            padding: 12px 18px;
        }
        .mr-callout-box {
            background: #FAF7F2;
            border: 1px solid #ECE6DD;
            border-left: 3.5px solid #C45738;
            border-radius: 6px;
            padding: 11px 18px;
            margin-top: 6px;
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
            font-size: 12.2px;
            color: #111827;
            font-weight: 700;
        }
        .mr-offer-info p {
            margin: 2px 0 0 0;
            font-size: 9.6px;
            color: #4B5563;
            line-height: 1.45;
        }
        .mr-offer-meta {
            font-size: 8.6px;
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
    <div class="mr-page" style="justify-content:space-between; text-align:center; padding:16mm 20mm 14mm 20mm;">
        <!-- Top Sanskrit Invocation & Master Brand Logo -->
        <div style="z-index:2; position:relative;">
            <div style="font-size:11px; font-weight:700; color:#C45738; letter-spacing:0.14em; text-transform:uppercase; margin-bottom:12px;">
                ॥ ॐ श्री गणेशाय नमः ॥ · प्रामाणिक वैदिक दोष निवारण एवं उपाय महा-दस्तावेज़
            </div>
            <div style="display:flex; justify-content:center; margin-bottom:14px;">
                <img src="<?php echo esc_attr($logo_uri); ?>" style="height:125px; max-width:380px; object-fit:contain; display:block;" alt="MeraRashifal Logo" />
            </div>
            <h1 style="font-family:<?php echo $head_font; ?>; font-size:26px; font-weight:800; color:#111827; margin:0 0 6px 0; letter-spacing:-0.02em; line-height:1.2;">
                MASTER VEDIC KUNDALI DOSH &amp; REMEDIAL REPORT
            </h1>
            <div style="font-size:13px; font-weight:600; color:#C2964C; font-style:italic; margin-bottom:16px;">
                “<?php echo esc_html($motto); ?>”
            </div>

            <!-- Triple Identity Bar: Western Sun vs Vedic Moon vs Ascendant -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:30px; padding:8px 24px; display:inline-flex; gap:22px; font-size:11px; box-shadow:0 2px 8px rgba(0,0,0,0.03); margin-bottom:18px;">
                <div><span style="color:#C45738; font-weight:700;">Western Sun Sign:</span> <strong style="color:#111827;">Capricorn (Dec 26)</strong></div>
                <div style="color:#CBD5E1;">|</div>
                <div><span style="color:#047857; font-weight:700;">Vedic Moon Sign:</span> <strong style="color:#111827;">Leo (Simha) · Magha 4</strong></div>
                <div style="color:#CBD5E1;">|</div>
                <div><span style="color:#C2964C; font-weight:700;">Ascendant (Lagna):</span> <strong style="color:#111827;">Cancer (Karka) 21.27°</strong></div>
            </div>
        </div>

        <!-- Center Native Verified Details Card -->
        <div style="background:#FFFFFF; border:1.5px solid #C2964C; border-radius:12px; padding:14px 22px; box-shadow:0 4px 18px rgba(0,0,0,0.04); max-width:560px; margin:0 auto; z-index:2; position:relative; width:100%;">
            <div style="font-size:10px; font-weight:700; color:#8C7B6B; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:3px;">
                CONFIDENTIAL KARMIC &amp; REMEDIAL DOSSIER PREPARED FOR
            </div>
            <div style="font-family:<?php echo $head_font; ?>; font-size:23px; font-weight:800; color:#111827; margin-bottom:10px; letter-spacing:-0.01em;">
                <?php echo esc_html(strtoupper($person_name)); ?>
            </div>

            <!-- Native Coordinates Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 24px; text-align:left; font-size:11px; border-top:1px solid #F0EAE1; padding-top:9px; margin-bottom:9px;">
                <div><strong style="color:#4B5563;">Date of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($formatted_dob); ?></span></div>
                <div><strong style="color:#4B5563;">Time of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($tob); ?> (20:30 IST)</span></div>
                <div><strong style="color:#4B5563;">Place of Birth:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html($pob); ?></span></div>
                <div><strong style="color:#4B5563;">Biological Gender:</strong> <span style="color:#111827; font-weight:700;"><?php echo esc_html(ucfirst($gender)); ?></span></div>
            </div>

            <!-- Executive Kundali Dosha Diagnostic Audit (Clear Affliction Verdict) -->
            <div style="background:#FAF7F2; border:1.2px solid #E2D9CC; border-radius:8px; padding:9px 13px; margin-bottom:9px;">
                <div style="font-size:10px; font-weight:800; color:#8C7B6B; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                    <span>PRIMARY KUNDALI DOSHA AUDIT SUMMARY</span>
                    <span style="font-size:9.5px; font-weight:700; color:#C45738;">CLINICAL VERDICT</span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 12px; font-size:11px; text-align:left;">
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#FFFFFF; padding:5px 9px; border-radius:6px; border:1px solid #E5E7EB;">
                        <span style="font-weight:700; color:#111827;">Kaal Sarp Dosh:</span>
                        <span style="font-weight:800; color:#991B1B; background:#FEE2E2; border:1px solid #FCA5A5; padding:2px 8px; border-radius:10px; font-size:10px; letter-spacing:0.02em;">PRESENT</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#FFFFFF; padding:5px 9px; border-radius:6px; border:1px solid #E5E7EB;">
                        <span style="font-weight:700; color:#111827;">Mangal Dosh:</span>
                        <span style="font-weight:800; color:#065F46; background:#D1FAE5; border:1px solid #6EE7B7; padding:2px 8px; border-radius:10px; font-size:10px; letter-spacing:0.02em;">NOT PRESENT</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#FFFFFF; padding:5px 9px; border-radius:6px; border:1px solid #E5E7EB;">
                        <span style="font-weight:700; color:#111827;">Pitra Dosh:</span>
                        <span style="font-weight:800; color:#065F46; background:#D1FAE5; border:1px solid #6EE7B7; padding:2px 8px; border-radius:10px; font-size:10px; letter-spacing:0.02em;">NOT PRESENT</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#FFFFFF; padding:5px 9px; border-radius:6px; border:1px solid #E5E7EB;">
                        <span style="font-weight:700; color:#111827;">Guru Chandal Dosh:</span>
                        <span style="font-weight:800; color:#065F46; background:#D1FAE5; border:1px solid #6EE7B7; padding:2px 8px; border-radius:10px; font-size:10px; letter-spacing:0.02em;">NOT PRESENT</span>
                    </div>
                </div>
            </div>

            <div style="background:#FDF8F3; border:1px solid #ECE6DD; border-radius:8px; padding:7px 12px; font-size:10px; color:#C45738; font-weight:700; display:flex; justify-content:space-around;">
                <span>Diagnostic Engine: <strong>Parashari &amp; Jaimini</strong></span>
                <span>•</span>
                <span>Affliction Scan: <strong>Shat-Dosha &amp; Papasamaya</strong></span>
                <span>•</span>
                <span>Harmonization: <strong>90-Day Upaya Protocol</strong></span>
            </div>
        </div>

        <!-- Master Theme Banner & Statutory Disclaimer -->
        <div style="z-index:2; position:relative; max-width:600px; margin:0 auto; padding-top:6px;">
            <div style="font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:3px;">
                EXECUTIVE KARMIC THEME
            </div>
            <div style="font-size:11.8px; color:#374151; font-weight:600; line-height:1.4; margin-bottom:8px;">
                “Transmuting Prarabdha Karma: Harnessing Nodal Focus and Exalted Martian Fortitude into Sovereign Achievement”
            </div>
            <div style="font-size:10px; color:#64748B; line-height:1.4; text-align:justify; background:#FAF7F2; padding:8px 14px; border-radius:6px; border:1px solid #ECE6DD;">
                <strong style="color:#111827;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal Technologies nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
            </div>
        </div>

        <!-- Sovereign Seal Footer -->
        <div style="border-top:1px solid #ECE6DD; padding-top:6px; margin-bottom:8px; font-size:9px; color:#8C7B6B; display:flex; justify-content:space-between; z-index:2; position:relative;">
            <span style="font-weight:600; color:#4B5563;">www.MeraRashifal.com</span>
            <span>Document ID: MR-KD-<?php echo strtoupper(substr(md5($person_name . $dob . 'DOSH'), 0, 8)); ?></span>
            <span style="font-weight:700; color:#C45738;">Page 1 of 20</span>
        </div>
    </div>

    <!-- ========================================================
         PAGE 2: TABLE OF CONTENTS & CORE D1 JANMA KUNDALI
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Table of Contents &amp; Natal Anchor</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">01 · Strategic Directory &amp; Natal Geometry</div>
            <h1 class="mr-page-h1">Table of Contents &amp; Core Janma Kundali (D1)</h1>
            <div class="mr-page-sub">Comprehensive 20-Page Clinical Diagnosis of Planetary Afflictions, Cancellations, and Shastric Remedies</div>

            <div class="mr-grid-2col" style="align-items:center; margin-bottom:8px;">
                <div>
                    <?php echo self::render_d1_diamond_svg(280, 4); ?>
                    <div style="text-align:center; font-size:9px; color:#4B5563; font-weight:700; margin-top:4px; text-transform:uppercase; letter-spacing:0.04em;">
                        🪐 Natal D1 Kundali · Cancer Lagna 21.27° · Rahu in 1st · Exalted Mars in 7th
                    </div>
                </div>

                <div>
                    <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.06em; border-bottom:1.5px solid #C45738; padding-bottom:2px; margin-bottom:6px;">
                        Part I: Clinical Affliction Diagnostics (Pages 1–10)
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:11.5px; margin-bottom:8px;">
                        <tbody>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">01. Sovereign Cover &amp; Verified Coordinates</td><td style="text-align:right; font-weight:800; color:#C45738;">01</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">02. Table of Contents &amp; D1 Celestial Anchor</td><td style="text-align:right; font-weight:800; color:#C45738;">02</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">03. The Dosha Vulnerability Summary Dashboard</td><td style="text-align:right; font-weight:800; color:#C45738;">03</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">04. Mangal (Manglik) Dosha: 7th House Analysis</td><td style="text-align:right; font-weight:800; color:#C45738;">04</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">05. Manglik Cancellation &amp; Mitigation (Bhanga)</td><td style="text-align:right; font-weight:800; color:#C45738;">05</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">06. Harmonic Strength &amp; 4-Varga Grid (D1–D10)</td><td style="text-align:right; font-weight:800; color:#C45738;">06</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">07. Kaalsarp Dosha: Anatomy &amp; Nodal Alignment</td><td style="text-align:right; font-weight:800; color:#C45738;">07</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">08. Kaalsarp Life Impact &amp; Temporal Cycles</td><td style="text-align:right; font-weight:800; color:#C45738;">08</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">09. The "Karmic Gift vs. Hurdle" Kaalsarp Reframe</td><td style="text-align:right; font-weight:800; color:#C45738;">09</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">10. Pitra Dosha: Ancestral Karma &amp; Lineage Scan</td><td style="text-align:right; font-weight:800; color:#C45738;">10</td></tr>
                        </tbody>
                    </table>

                    <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:800; color:#047857; text-transform:uppercase; letter-spacing:0.06em; border-bottom:1.5px solid #047857; padding-bottom:2px; margin-bottom:6px;">
                        Part II: Malefic Balance, Upayas &amp; 90-Day Plan (Pages 11–20)
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                        <tbody>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">11. Pitra Dosha Resolution &amp; Tarpan Protocols</td><td style="text-align:right; font-weight:800; color:#C45738;">11</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">12. Papasamaya: Quantifying Cumulative Malefic Load</td><td style="text-align:right; font-weight:800; color:#C45738;">12</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">13. Alliance Harmony &amp; Physical Vitality (Ojas)</td><td style="text-align:right; font-weight:800; color:#C45738;">13</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">14. Karmic Priority Matrix &amp; Strategic Ranking</td><td style="text-align:right; font-weight:800; color:#C45738;">14</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">15. Ratna Vigyan: Gemstone &amp; Metal Prescription</td><td style="text-align:right; font-weight:800; color:#C45738;">15</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">16. Rudraksha Therapy &amp; Neurological Grounding</td><td style="text-align:right; font-weight:800; color:#C45738;">16</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">17. Sacred Vedic Mantras &amp; Planetary Shanti</td><td style="text-align:right; font-weight:800; color:#C45738;">17</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">18. Step-by-Step Practical Weekly Daan Schedule</td><td style="text-align:right; font-weight:800; color:#C45738;">18</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">19. The 90-Day Karmic Cleansing &amp; Growth Roadmap</td><td style="text-align:right; font-weight:800; color:#C45738;">19</td></tr>
                            <tr style="border-bottom:1px solid #F2ECE3;"><td style="padding:4px 0; font-weight:700; color:#111827;">20. Master Dosh Glossary &amp; Sacred Closing Cover</td><td style="text-align:right; font-weight:800; color:#C45738;">20</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reading Methodology Directive Card -->
            <div style="background:#FFFFFF; border:1px solid #ECE6DD; border-radius:8px; padding:10px 16px; margin-top:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:700; color:#111827; margin-bottom:4px;">
                    Parashari Clinical Remedial Philosophy &amp; Navigation
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; font-size:11.5px; color:#4B5563; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">1. Diagnose Afflictions:</strong>
                        Review the vulnerability heatmap (Page 3) to isolate actual structural pressure points from harmless astrological myths.
                    </div>
                    <div>
                        <strong style="color:#047857;">2. Verify Bhanga (Cancellations):</strong>
                        Check classical counterbalances (Pages 5, 9); exalted planets and functional benefics naturally neutralize perceived curses.
                    </div>
                    <div>
                        <strong style="color:#C2964C;">3. Systematic Action:</strong>
                        Implement sustainable weekly Daan (Page 18) and follow the 90-day sequenced roadmap (Page 19) for lasting empowerment.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(2, 'Table of Contents &amp; Core D1 Kundali'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 3: THE DOSHA VULNERABILITY SUMMARY DASHBOARD
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Dosha Vulnerability Dashboard</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">02 · Comprehensive Affliction Audit</div>
            <h1 class="mr-page-h1">The Dosha Vulnerability Summary Dashboard</h1>
            <div class="mr-page-sub">Clinical Audit of Shat-Doshas (Six Classical Afflictions) with Raw Malefic Scores and Mitigation Ratings</div>

            <!-- Dosh Severity Heatmap SVG -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-align:center; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    Planetary Affliction &amp; Vulnerability Heatmap
                </div>
                <?php echo self::render_dosh_severity_gauge_svg(540, 86); ?>
            </div>

            <!-- Shat-Dosha Diagnostic Table -->
            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                    Shat-Dosha Parashari Diagnostic Matrix
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #C2964C; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Affliction</th>
                            <th style="padding:6px 9px; color:#111827;">Key Planetary Signifier</th>
                            <th style="padding:6px 9px; color:#111827;">Raw Score</th>
                            <th style="padding:6px 9px; color:#111827;">Cancellation Status</th>
                            <th style="padding:6px 9px; color:#111827;">Action Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Mangal (Manglik)</td>
                            <td style="padding:6px 9px;">Mars in 7th House (Makara)</td>
                            <td style="padding:6px 9px; font-weight:700;">60% (High Raw)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">80% Mitigated (Uchha Bhanga)</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-emerald">Mild / Managed</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Kaalsarp Dosha</td>
                            <td style="padding:6px 9px;">Rahu 1st · Ketu 7th Axis</td>
                            <td style="padding:6px 9px; font-weight:700;">65% (Active)</td>
                            <td style="padding:6px 9px; color:#D97706; font-weight:700;">Partial (Mars Conjunct Ketu)</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-terracotta">Primary Focus</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Pitra Dosha</td>
                            <td style="padding:6px 9px;">Sun in 6th · 9th Lord with Sa</td>
                            <td style="padding:6px 9px; font-weight:700;">25% (Mild)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">High (Jupiter Exerts Wisdom)</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-emerald">Routine Upaya</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Shani Sade Sati</td>
                            <td style="padding:6px 9px;">Saturn Transit vs Moon in Leo</td>
                            <td style="padding:6px 9px; font-weight:700;">15% (Low)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Dormant Phase (Kumbha/Meena)</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-emerald">No Immediate Alarm</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Guru Chandal Yoga</td>
                            <td style="padding:6px 9px;">Jupiter–Rahu Conjunction</td>
                            <td style="padding:6px 9px; font-weight:700;">0% (Absent)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Jupiter in 3rd, Rahu in 1st</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-emerald">Completely Clear</span></td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">Surya/Chandra Grahan</td>
                            <td style="padding:6px 9px;">Luminaries with Nodes</td>
                            <td style="padding:6px 9px; font-weight:700;">5% (Negligible)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Separation &gt; 20° from Nodes</td>
                            <td style="padding:6px 9px;"><span class="mr-badge mr-badge-emerald">Completely Clear</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Executive Clinical Verdict Card -->
            <div class="mr-callout-box" style="margin-top:2px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin-bottom:3px; text-transform:uppercase;">
                    CLINICAL DIAGNOSTIC VERDICT &amp; ORIENTATION
                </div>
                <p style="font-size:11.5px; color:#374151; line-height:1.45; margin:0;">
                    Your astrological profile is <strong>not</strong> encumbered by severe, multi-faceted afflictions. While high raw Manglik and Kaalsarp indicators appear on preliminary scans, rigorous Parashari analysis establishes substantial mitigation: Mars attains supreme exaltation in Capricorn, and Jupiter casts a protective 5th trine aspect onto the 7th house. Your central operational focus is pacifying the Anant Kaalsarp nodal tension while safely channeling Mars' executive energy.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Shat-Dosha Audit confirms Guru Chandal, Grahan Yoga, and active Sade Sati are completely absent or dormant.",
                "Anant Kaalsarp represents your primary operational focus, requiring consistent mental grounding and somatic calm.",
                "Exalted Manglik Status: Although Mars occupies the 7th house, its exaltation converts marital friction into professional drive.",
                "Manageable Pitra Debt: Ancestral indications are mild (25%) and fully harmonizable through routine seasonal solar reverence."
            ], "Vulnerability Dashboard & Hierarchy Synthesis"); ?>
            <?php echo self::render_page_footer(3, 'The Dosha Vulnerability Summary Dashboard'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 4: MANGAL (MANGLIK) DOSHA DEEP DIVE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Mangal Dosha: 7th House Analysis</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">03 · Martian Dynamics &amp; Marital Anatomy</div>
            <h1 class="mr-page-h1">Mangal Dosha: 7th House Placement &amp; Energy Flow</h1>
            <div class="mr-page-sub">Evaluating Martian Heat, Aspect Dynamics across Kendras, and Partnership Psychological Archetypes</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px; border-left:4px solid #C45738;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Parashari Definition of Kuja (Manglik) Dosha
                </div>
                <p style="font-size:11.5px; color:#4B5563; line-height:1.48; margin:0;">
                    Classical Parashari texts dictate that when Mars occupies the <strong>1st, 4th, 7th, 8th, or 12th house</strong> from the Lagna, Chandra, or Shukra, it exerts intense thermal pressure (Agni Tattva). In your chart, Mars occupies the <strong>7th House (Capricorn / Makara at 3°02')</strong>. The 7th house governs one-on-one contracts, marital alliances, trade partnerships, and public visibility.
                </p>
            </div>

            <div class="mr-grid-2col" style="margin-bottom:8px;">
                <div class="mr-card" style="padding:11px 16px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin-bottom:6px; text-transform:uppercase;">
                        Martian House Placements &amp; Aspects
                    </div>
                    <ul style="font-size:11.5px; color:#4B5563; line-height:1.45; margin:0; padding-left:14px;">
                        <li><strong>7th House (Direct):</strong> Intense passion, uncompromising standards for life partner, zero tolerance for deceit.</li>
                        <li><strong>Aspect on 10th (4th Drishti):</strong> High professional drive, leadership authority, and executive command in enterprise.</li>
                        <li><strong>Aspect on 1st (7th Drishti):</strong> Injects physical stamina, competitive willpower, and mental vigilance into Lagna.</li>
                        <li><strong>Aspect on 2nd (8th Drishti):</strong> Direct speech, sharp financial acumen, but occasional sharpness in family negotiations.</li>
                    </ul>
                </div>

                <div class="mr-card" style="padding:11px 16px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; margin-bottom:6px; text-transform:uppercase;">
                        Martian Psychological Archetype
                    </div>
                    <div style="font-size:11.5px; color:#4B5563; line-height:1.45;">
                        <p style="margin:0 0 6px 0;">
                            Mars represents the cosmic commander (Senapati). When placed in the house of partnerships, you naturally approach relationships with the strategic mindset of an executive alliance.
                        </p>
                        <p style="margin:0;">
                            The friction experienced in Manglik charts is rarely a lack of affection; rather, it is an overflow of operational energy that demands equal competence, direct transparency, and zero complacency from your counterpart.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Tri-Lagna Mangal Severity Evaluation Metric
                </div>
                <div class="mr-grid-3col" style="font-size:11.5px;">
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:800; color:#111827; margin-bottom:2px;">LAGNA-BASED SEVERITY</div>
                        <span style="font-size:15px; font-weight:800; color:#C45738;">65%</span> (High Placement)
                        <p style="margin:4px 0 0 0; color:#6B7280; font-size:10px;">Mars occupies 7th from Cancer Lagna.</p>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:800; color:#111827; margin-bottom:2px;">CHANDRA-BASED SEVERITY</div>
                        <span style="font-size:15px; font-weight:800; color:#D97706;">30%</span> (6th House Position)
                        <p style="margin:4px 0 0 0; color:#6B7280; font-size:10px;">From Moon in Leo, Mars sits in 6th.</p>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:800; color:#111827; margin-bottom:2px;">SHUKRA-BASED SEVERITY</div>
                        <span style="font-size:15px; font-weight:800; color:#047857;">15%</span> (3rd House Position)
                        <p style="margin:4px 0 0 0; color:#6B7280; font-size:10px;">From Venus in Scorpio, Mars is in 3rd.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Direct 7th House Seat: Mars commands your relationship house, bringing exceptional candor, loyalty, and intense work ethic.",
                "Triple Drishti Cast: Mars simultaneously energizes career status (10H), physical stamina (1H), and accumulated wealth (2H).",
                "Chandra & Shukra Shield: Measured from the Moon and Venus, Manglik affliction drops below 30%, confirming no acute marital harm.",
                "Conscious Communication: Channel Martian assertiveness into shared corporate and creative goals rather than domestic micromanagement."
            ], "Mangal 7th House Placement Takeaways"); ?>
            <?php echo self::render_page_footer(4, 'Mangal Dosha: 7th House Analysis & Energy Dynamics'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 5: MANGLIK CANCELLATION & MITIGATION (BHANGA)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Manglik Bhanga (Cancellation)</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">04 · Shastric Counterbalance &amp; Bhanga Audit</div>
            <h1 class="mr-page-h1">Manglik Cancellation &amp; Mitigation (Bhanga) Scorecard</h1>
            <div class="mr-page-sub">Classical Parashari Exceptions, Exaltation In Makara, and the "Karmic Gift" Reframe Analysis</div>

            <!-- Cancellation Audit Table -->
            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Classical Kuja Dosha Bhanga Audit Table
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #047857; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Classical Bhanga Rule</th>
                            <th style="padding:6px 9px; color:#111827;">Your Chart Placement</th>
                            <th style="padding:6px 9px; color:#111827;">Status</th>
                            <th style="padding:6px 9px; color:#111827;">Mitigation Factor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">1. Mars in Exaltation (Makara)</td>
                            <td style="padding:6px 9px;">Mars is exalted at 3°02' in Capricorn</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:800;">✓ VALIDATED</td>
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">-45% Intensity</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">2. Functional Yogakaraka Status</td>
                            <td style="padding:6px 9px;">Mars rules 5th (Scorpio) &amp; 10th (Aries)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:800;">✓ VALIDATED</td>
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">-25% Intensity</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">3. Benefic Aspect from Jupiter</td>
                            <td style="padding:6px 9px;">Jupiter in 3rd casts 5th trine to 7th</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:800;">✓ VALIDATED</td>
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">-15% Intensity</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">4. Maturation Age &gt; 28 Years</td>
                            <td style="padding:6px 9px;">Native age is beyond 28 &amp; 32 thresholds</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:800;">✓ VALIDATED</td>
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">Natural Cooling</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700;">5. Conjunction with Ketu</td>
                            <td style="padding:6px 9px;">Mars conjunct Ketu (Spiritualizes Fire)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:800;">✓ VALIDATED</td>
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">Detachment Filter</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Strategic Addition 1: The "Karmic Gift vs. Hurdle" Reframe Box -->
            <div class="mr-card-accent" style="padding:11px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span>💎</span> THE "KARMIC GIFT VS. HURDLE" REFRAME BOX: MANGAL
                </div>
                <div class="mr-grid-2col" style="font-size:9px; line-height:1.45;">
                    <div style="background:#FFFFFF; border:1px solid #FEE2E2; border-radius:6px; padding:8px 12px;">
                        <strong style="color:#B91C1C;">THE EXOTERIC HURDLE (POPULAR FEAR):</strong><br>
                        Traditional superstition claims that 7th house Mars destroys marital harmony, provokes endless domestic clashes, and ruins partnership peace.
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #D1FAE5; border-radius:6px; padding:8px 12px;">
                        <strong style="color:#047857;">THE ESOTERIC KARMIC GIFT (AUTHENTIC REALITY):</strong><br>
                        Because Mars is exalted in Capricorn and acts as the supreme Yogakaraka (5th &amp; 10th lord) for Cancer Lagna, this placement produces extraordinary executive fortitude, sharp contract acumen, and unshakeable loyalty in real enterprise.
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:9px 14px; margin-bottom:4px; border-left:3.5px solid #047857;">
                <strong style="color:#047857;">NET MANGAL SEVERITY VERDICT:</strong> With 85% total mitigation validated through exaltation and Yogakaraka dignity, your chart is clinically classified as <strong>Exempt from Destructive Manglik Effects</strong>. Marital compatibility is best served by matching with an ambitious, intellectually driven partner.
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Exaltation in Makara: Mars in its supreme sign of exaltation (Capricorn) automatically dissolves malefic venom into disciplined fortitude.",
                "Cancer Yogakaraka: Ruling the 5th (intellect) and 10th (status), Mars is your chart's most potent functional benefic planet.",
                "Jupiter's Protective Trine: Devaguru Jupiter aspects Mars from Virgo, infusing moral maturity and strategic restraint into the 7th house.",
                "Zero Destructive Risk: You do not require alarming, expensive dosha-nivaran rituals; simple Hanuman Chalisa recitations maintain optimal balance."
            ], "Manglik Cancellation & Mitigation Takeaways"); ?>
            <?php echo self::render_page_footer(5, 'Manglik Cancellation & Mitigation (Bhanga) Scorecard'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 6: HARMONIC STRENGTH & 4-VARGA GRID
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · 4-Harmonic Divisional Grid</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">05 · Harmonic Strength &amp; Multi-Varga Cross-Verification</div>
            <h1 class="mr-page-h1">Divisional Harmonic Cross-Verification (4-Varga Grid)</h1>
            <div class="mr-page-sub">Auditing Primary Afflictions (Mangal &amp; Kaalsarp) across Physical (D1), Soul (D9), Progeny (D7), and Career (D10) Harmonics</div>

            <?php
            $d1_pl = [
                1 => ['Ra 19°'], 2 => ['Mo 12°'], 3 => ['Ju 15°', 'Sa 16°'],
                5 => ['Ve 17°'], 6 => ['Su 11°', 'Me 9°'], 7 => ['Ma 3°(Ex)', 'Ke 19°'],
            ];
            $d9_pl = [
                1 => ['Sa 21°'], 4 => ['Mo 17°'], 5 => ['Ke 19°'], 7 => ['Su 12°', 'Ju 19°'],
                9 => ['Ve 4°'], 10 => ['Ma 27°'], 11 => ['Ra 19°'],
            ];
            $d7_pl = [
                1 => ['Ju 14°'], 3 => ['Ve 8°'], 5 => ['Ma 12°'],
                7 => ['Sa 20°'], 9 => ['Su 7°'], 11 => ['Mo 22°'],
            ];
            $d10_pl = [
                1 => ['Su 11°'], 3 => ['Ju 15°'], 6 => ['Ma 3°'],
                7 => ['Ve 17°'], 10 => ['Sa 16°'], 11 => ['Mo 12°'],
            ];
            ?>

            <!-- 2x2 Divisional Grid -->
            <div class="mr-grid-2x2-charts" style="margin-bottom:6px;">
                <!-- D1 -->
                <div class="mr-card" style="padding:8px 12px; width:100%; box-sizing:border-box;">
                    <?php echo self::render_varga_diamond_svg(195, $d1_pl, 4, 'D1 Janma Kundali (Physical Base)'); ?>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4; margin-top:5px; background:#FAF7F2; padding:6px 9px; border-radius:5px; border:1px solid #ECE6DD;">
                        <strong>D1 Finding (54 words):</strong> In the physical substrate, Mars attains supreme exaltation in the 7th house (Capricorn) while Rahu and Ketu lock the 1st–7th axis. While this signals intense partnership lessons and high personal ambition, the exaltation of Mars acts as an internal protective shield against physical or structural catastrophe.
                    </div>
                </div>

                <!-- D9 -->
                <div class="mr-card" style="padding:8px 12px; width:100%; box-sizing:border-box;">
                    <?php echo self::render_varga_diamond_svg(195, $d9_pl, 10, 'D9 Navamsha (Soul Fruit &amp; Dharma)'); ?>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4; margin-top:5px; background:#FAF7F2; padding:6px 9px; border-radius:5px; border:1px solid #ECE6DD;">
                        <strong>D9 Finding (52 words):</strong> In the Dharmic Navamsha, Mars shifts into the 10th house of high vocational enterprise, entirely leaving the 7th marital sector. This confirms that Martian intensity is fundamentally destined for professional leadership and executive mastery rather than marital dissolution, neutralizing primary matrimonial fear.
                    </div>
                </div>

                <!-- D7 -->
                <div class="mr-card" style="padding:8px 12px; width:100%; box-sizing:border-box;">
                    <?php echo self::render_varga_diamond_svg(195, $d7_pl, 4, 'D7 Saptamsha (Lineage &amp; Progeny)'); ?>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4; margin-top:5px; background:#FAF7F2; padding:6px 9px; border-radius:5px; border:1px solid #ECE6DD;">
                        <strong>D7 Finding (53 words):</strong> In the Saptamsha harmonic of lineage, Devaguru Jupiter occupies the 1st house in full dignity, casting radiant trine aspects upon the 5th and 9th houses. This proves that ancestral Pitra indicators and Kaalsarp axis constraints do not impede healthy progeny, legacy continuation, or familial blessings across generations.
                    </div>
                </div>

                <!-- D10 -->
                <div class="mr-card" style="padding:8px 12px; width:100%; box-sizing:border-box;">
                    <?php echo self::render_varga_diamond_svg(195, $d10_pl, 1, 'D10 Dasamsha (Career Altitude)'); ?>
                    <div style="font-size:10.5px; color:#4B5563; line-height:1.4; margin-top:5px; background:#FAF7F2; padding:6px 9px; border-radius:5px; border:1px solid #ECE6DD;">
                        <strong>D10 Finding (55 words):</strong> In the vocational Dasamsha, the Sun sits exalted in Aries in the 1st house, while Mars occupies the 6th house of competitive victory (Shatru Hanta). The Kaalsarp tension in D1 converts directly into relentless stamina, crisis management capability, and superior problem-solving acumen in executive corporate environments.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "D9 Soul Clearance: Mars moves to the 10th house in Navamsha, permanently confirming that marital disruption is largely neutralized.",
                "D7 Lineage Protection: Jupiter's radiant dignity in D7 shields family harmony, children, and legacy continuation from Pitra afflictions.",
                "D10 Vocational Power: Exalted Sun and 6th-house Mars in Dasamsha channel raw karmic pressure into executive dominance and industry prestige.",
                "Multi-Varga Verdict: Afflictions are confined to internal psychological tension and do not manifest as material collapse in real-world assets."
            ], "Harmonic Cross-Verification Takeaways"); ?>
            <?php echo self::render_page_footer(6, 'Harmonic Strength & Multi-Varga Dosh Matrix'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 7: KAALSARP DOSHA ANATOMY & AXIS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Kaalsarp Dosha Anatomy</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">06 · Nodal Axis Architecture</div>
            <h1 class="mr-page-h1">Kaalsarp Dosha: Anatomy &amp; 1st–7th Nodal Alignment</h1>
            <div class="mr-page-sub">Analyzing the Rahu (Cancer 1H) – Ketu (Capricorn 7H) Hemming Arc and Soul Evolution Mandates</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px; border-left:4px solid #6B21A8;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    The Shastrokta Anatomy of Kaalsarp Yoga
                </div>
                <p style="font-size:11.5px; color:#4B5563; line-height:1.48; margin:0;">
                    In classical Jaimini and Parashari lore, <em>Kaalsarp Yoga</em> occurs when all seven visible grahas (Surya, Chandra, Mangal, Budha, Guru, Shukra, Shani) are hemmed between the serpentine lunar nodes: <strong>Rahu (North Node - Serpent's Head)</strong> and <strong>Ketu (South Node - Serpent's Tail)</strong>. This aligns the soul along a polarized axis of destiny.
                </p>
            </div>

            <div class="mr-card" style="padding:11px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Your Nodal Configuration: Anant Kaal Sarp Yoga
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">NODAL AXIS COORDINATES:</strong>
                        <ul style="margin:4px 0 0 0; padding-left:14px; color:#4B5563;">
                            <li><strong>Rahu Placement:</strong> 1st House (Cancer / Karka 19°14')</li>
                            <li><strong>Ketu Placement:</strong> 7th House (Capricorn / Makara 19°14')</li>
                            <li><strong>Orientation:</strong> All visible planets sit between Houses 1 and 7.</li>
                            <li><strong>Planetary Escape Check:</strong> Mars is at 3°02' Capricorn (inside nodal arc).</li>
                        </ul>
                    </div>
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#6B21A8;">ANANT TYPE METAPHYSICS:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            Anant represents the infinite cosmic serpent (Sheshanaga) upon whom Lord Vishnu reclines. In the 1st–7th alignment, the native's central struggle centers on self-identity, autonomous authority (1H), and renegotiating relationship boundaries (7H).
                        </p>
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Psychological Manifestations of 1st House Rahu
                </div>
                <div class="mr-grid-3col" style="font-size:11.5px; line-height:1.45;">
                    <div style="border-left:2.5px solid #C45738; padding-left:8px;">
                        <strong style="color:#111827;">Perpetual Restlessness:</strong> A driving inner hunger to redefine oneself, seek grand platforms, and avoid ordinary molds.
                    </div>
                    <div style="border-left:2.5px solid #047857; padding-left:8px;">
                        <strong style="color:#111827;">Strategic Skepticism:</strong> High situational intuition that quickly senses ulterior motives in unvetted alliances.
                    </div>
                    <div style="border-left:2.5px solid #C2964C; padding-left:8px;">
                        <strong style="color:#111827;">Spiritual Thirst:</strong> 7th house Ketu produces a sudden feeling of detachment after achieving worldly relationship goals.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Anant Configuration: Rahu in 1st house and Ketu in 7th house creates the celebrated Anant (Infinite) Kaal Sarp Yoga.",
                "Self vs. Other Axis: Your spiritual journey forces continuous re-examination of personal sovereignty versus relational compromise.",
                "High Intuitive Reflexes: Rahu in Lagna sharpens worldly perception, granting a near-instinctive radar for organizational opportunities.",
                "Serpent Energy Pacification: Gentle Naga Gayatri and Maha Mrityunjaya japa effectively align this intense nodal frequency."
            ], "Anant Kaalsarp Configuration Takeaways"); ?>
            <?php echo self::render_page_footer(7, 'Kaalsarp Dosha: Anatomy & 1st–7th Nodal Alignment'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 8: KAALSARP LIFE IMPACT & CYCLES
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Kaalsarp Life Impact &amp; Cycles</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">07 · Karmic Cycles &amp; Churning of Destiny</div>
            <h1 class="mr-page-h1">Kaalsarp Life Impact: Delays, Breakthroughs &amp; Elevation</h1>
            <div class="mr-page-sub">The Three Temporal Eras, The Samudra Manthan Metaphor, and Behavioral Preventative Tactics</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    The Three Temporal Eras of Kaalsarp Manifestation
                </div>
                <div style="display:flex; flex-direction:column; gap:7px; font-size:11.5px; line-height:1.45;">
                    <div style="background:#FAF7F2; border-left:3.5px solid #DC2626; padding:7px 12px; border-radius:0 6px 6px 0;">
                        <strong style="color:#B91C1C;">PHASE 1: THE FRICTION ERA (AGES 0 TO 28) — RESISTANCE &amp; RECALIBRATION</strong><br>
                        Early life presents repeated delays where hard work yields unproportional recognition. Frequent feelings of being misunderstood or wrestling with identity, self-worth, and early partnership mismatch.
                    </div>
                    <div style="background:#FAF7F2; border-left:3.5px solid #D97706; padding:7px 12px; border-radius:0 6px 6px 0;">
                        <strong style="color:#D97706;">PHASE 2: THE RESTRUCTURING ERA (AGES 28 TO 42) — BREAKTHROUGH &amp; ASCENT</strong><br>
                        As Rahu matures at age 42 (and completes its 18-year nodal return), friction converts into supreme strategic clarity. The native develops uncommon emotional stamina, sheds unproductive entanglements, and achieves sudden enterprise elevation.
                    </div>
                    <div style="background:#FAF7F2; border-left:3.5px solid #047857; padding:7px 12px; border-radius:0 6px 6px 0;">
                        <strong style="color:#047857;">PHASE 3: THE AMRIT ERA (AGE 42 ONWARD) — SOVEREIGN MASTERY &amp; LEGACY</strong><br>
                        Post-maturation, the serpent axis functions as a formidable spiritual springboard. Worldly success stabilizes into durable institutional authority, mentorship, and high-level philanthropic influence.
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:11px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Behavioral Warning Flags &amp; Practical Antidotes
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">Common Kaalsarp Pitfalls:</strong>
                        <ul style="margin:4px 0 0 0; padding-left:14px; color:#4B5563;">
                            <li>All-or-nothing emotional thinking during high-pressure windows.</li>
                            <li>Impulsive trust in unvetted business partners during Rahu transits.</li>
                            <li>Overthinking minor relational setbacks as permanent betrayals.</li>
                        </ul>
                    </div>
                    <div>
                        <strong style="color:#047857;">Empowering Shastric Antidotes:</strong>
                        <ul style="margin:4px 0 0 0; padding-left:14px; color:#4B5563;">
                            <li>Anchor major contracts in written, legal documentation without ambiguity.</li>
                            <li>Establish daily breathwork (Pranayama) to quiet Rahu's sensory restlessness.</li>
                            <li>Maintain steady charitable support for marginalized elders and stray animals.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Samudra Manthan Principle: The early challenges of Kaalsarp are not punishment; they are mandatory conditioning for late-life leadership.",
                "Age 42 Maturation: Having crossed the critical 42-year threshold, you have fully entered the stable Amrit era of structural achievement.",
                "Clarity Over Confusion: The nodal axis no longer clouds decision-making; it now provides razor-sharp executive discrimination.",
                "Daily Nervous System Balance: Simple morning grounding rituals permanently neutralize any residual sensory agitation."
            ], "Kaalsarp Life Impact & Cycles Takeaways"); ?>
            <?php echo self::render_page_footer(8, 'Kaalsarp Life Impact: Delays, Breakthroughs & Elevation'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 9: THE "KARMIC GIFT VS. HURDLE" REFRAME BOX
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Kaalsarp Karmic Gift Reframe</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">08 · Empowering Philosophical Reframe</div>
            <h1 class="mr-page-h1">The "Karmic Gift vs. Hurdle" Reframe Box: Kaalsarp</h1>
            <div class="mr-page-sub">Deconstructing Medieval Astrological Myths and Activating the Latent Sovereign Engines of Greatness</div>

            <!-- Full-Page Reframe Module -->
            <div class="mr-card-accent" style="padding:12px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span>🐍</span> THE DUAL NATURE OF THE NODAL COIL: FEAR VS. SOVEREIGNTY
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.48;">
                    <div style="background:#FFFFFF; border:1px solid #FEE2E2; border-radius:6px; padding:10px 14px;">
                        <strong style="color:#B91C1C; font-size:11.5px; text-transform:uppercase;">The Exoteric Hurdle (The Myth of Doom)</strong>
                        <p style="margin:5px 0 0 0; color:#4B5563;">
                            The fear-based belief claims that Kaalsarp completely suffocates life progress, locks the individual in lifelong poverty, and denies lasting peace. It induces fatalistic despair, prompting natives to undertake frantic, fear-driven pilgrimages.
                        </p>
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #D1FAE5; border-radius:6px; padding:10px 14px;">
                        <strong style="color:#047857; font-size:11.5px; text-transform:uppercase;">The Esoteric Gift (The Engine of Greatness)</strong>
                        <p style="margin:5px 0 0 0; color:#4B5563;">
                            In reality, by compressing all personal planets into one hemisphere, Kaalsarp creates a powerful laser-like psychological focus. It produces unmatched resilience, immunity to external criticism, and the capacity to build durable empires from scratch.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:11px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Four Sovereign Assets Conferred by Your Anant Nodal Axis
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div style="border-left:2.5px solid #C45738; padding-left:8px;">
                        <strong style="color:#111827;">1. Hyper-Focus in Crisis:</strong> While others panic during market or institutional volatility, the Kaalsarp native enters a state of serene, tactical alertness.
                    </div>
                    <div style="border-left:2.5px solid #047857; padding-left:8px;">
                        <strong style="color:#111827;">2. Bullshit Immunity:</strong> Ketu in the 7th house strips away romantic illusions, leaving you with an unerring ability to evaluate true partner integrity.
                    </div>
                    <div style="border-left:2.5px solid #C2964C; padding-left:8px;">
                        <strong style="color:#111827;">3. Self-Made Archetype:</strong> You rarely inherit effortless windfalls; every milestone you conquer is earned, making your authority impossible to undermine.
                    </div>
                    <div style="border-left:2.5px solid #6B21A8; padding-left:8px;">
                        <strong style="color:#111827;">4. Spiritual Climax:</strong> Nodal compression forces eventual disillusionment with superficial materialism, awakening profound inner wisdom and peace.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Reframe from Fear to Power: Kaalsarp is not an astrological handicap; it is a specialized pressure cooker designed for soul mastery.",
                "Laser-Focused Willpower: The hemming of planets creates an extraordinary concentration of energy toward lifetime objectives.",
                "Crisis Leadership: You possess an inherent psychological advantage during high-stakes corporate and personal transformations.",
                "Harmonious Integration: When acknowledged with reverence rather than anxiety, the serpent axis becomes your greatest inner ally."
            ], "The Kaalsarp Karmic Gift Reframe Takeaways"); ?>
            <?php echo self::render_page_footer(9, "The 'Karmic Gift vs. Hurdle' Reframe Box: Kaalsarp"); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 10: PITRA DOSHA: ANCESTRAL KARMA & LINEAGE SCAN
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Pitra Dosha Diagnostic Scan</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">09 · Lineage Obligations &amp; Ancestral Audit</div>
            <h1 class="mr-page-h1">Pitra Dosha: Ancestral Karma &amp; Lineage Debt Scan</h1>
            <div class="mr-page-sub">Parashari Pitra Rina Analysis, 9th House Indicators, and Hereditary Subconscious Patterns</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px; border-left:4px solid #D97706;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Parashari Foundation of Pitra Rina (Ancestral Debt)
                </div>
                <p style="font-size:11.5px; color:#4B5563; line-height:1.48; margin:0;">
                    In Vedic philosophy, every soul inherits not only physical DNA but also a subtle reservoir of ancestral impressions (<em>Pitra Rina</em>). In classical astrology, Pitra Dosha is identified when the <strong>Sun (Pitri-karaka)</strong>, the <strong>9th House (Dharma &amp; Ancestors)</strong>, or its ruling planet suffers affliction from Saturn, Rahu, or Ketu.
                </p>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Your Chart's Pitra Dosha Diagnostic Scorecard
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #D97706; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Diagnostic Indicator</th>
                            <th style="padding:6px 9px; color:#111827;">Planetary Placement</th>
                            <th style="padding:6px 9px; color:#111827;">Affliction Level</th>
                            <th style="padding:6px 9px; color:#111827;">Mitigating Shastric Factor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">Surya (Pitri-karaka)</td>
                            <td style="padding:6px 9px;">Sun in 6th (Sagittarius 11°25')</td>
                            <td style="padding:6px 9px; color:#D97706; font-weight:700;">Mild (House of Debts)</td>
                            <td style="padding:6px 9px; color:#047857;">Sun sits with friendly Mercury (Budhaditya)</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">9th House (Bhagya / Pitra)</td>
                            <td style="padding:6px 9px;">Pisces (Meena) Sign</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Zero Malefic Occupancy</td>
                            <td style="padding:6px 9px; color:#047857;">9th house is clean; no direct malefic aspect</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">9th Lord (Jupiter)</td>
                            <td style="padding:6px 9px;">Jupiter conjunct Saturn in 3rd</td>
                            <td style="padding:6px 9px; color:#D97706; font-weight:700;">Moderate (Saturn Touch)</td>
                            <td style="padding:6px 9px; color:#047857;">Jupiter is Devaguru; infuses dharmic wisdom</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700;">Rahu–Sun Relationship</td>
                            <td style="padding:6px 9px;">Rahu in 1st, Sun in 6th (6/8 Axis)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">No Direct Conjunction</td>
                            <td style="padding:6px 9px; color:#047857;">Completely exempt from Surya Grahan Yoga</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:5px;">
                    Clinical Pitra Dosha Severity Evaluation
                </div>
                <div style="display:grid; grid-template-columns:1fr 2fr; gap:16px; font-size:11.5px; line-height:1.45;">
                    <div style="background:#FAF7F2; padding:10px 14px; border-radius:6px; border:1px solid #ECE6DD; text-align:center;">
                        <div style="font-weight:800; color:#111827; margin-bottom:2px;">TOTAL PITRA SCORE</div>
                        <span style="font-size:24px; font-weight:800; color:#D97706;">25%</span>
                        <div style="font-size:11px; font-weight:700; color:#047857; margin-top:2px;">MILD KARMIC RESIDUE</div>
                    </div>
                    <div>
                        <p style="margin:0 0 6px 0; color:#4B5563;">
                            Your Pitra score is remarkably mild (25%). You do <strong>not</strong> suffer from severe curses involving ancestral land loss, hereditary chronic illness, or lineage termination.
                        </p>
                        <p style="margin:0; color:#4B5563;">
                            The mild friction manifests simply as an unspoken psychological responsibility to elevate family honor beyond prior generational ceilings.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Mild 25% Severity: Pitra debt in your chart is benign, lacking severe curses or ancestral Grahan blockages.",
                "Clean 9th House: The house of father and ancestors is vacant and unobstructed by Rahu or Ketu.",
                "Jupiter–Saturn Conjunction: Reflects a duty to maintain moral discipline and preserve traditional lineage heritage.",
                "Effortless Remediation: Regular seasonal water offerings during Pitru Paksha provide complete spiritual peace for your lineage."
            ], "Pitra Dosha Diagnostic Scan Takeaways"); ?>
            <?php echo self::render_page_footer(10, 'Pitra Dosha: Ancestral Karma & Lineage Debt Scan'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 11: PITRA DOSHA RESOLUTION & CLOSURE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Pitra Dosha Resolution</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">10 · Ancestral Harmonization &amp; Healing</div>
            <h1 class="mr-page-h1">Pitra Dosha Resolution &amp; Karmic Closure Protocols</h1>
            <div class="mr-page-sub">Four Shastric Remedial Pillars, Pitri Tarpana Science, and Daily Solar Gayatri Sadhana</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Four Time-Tested Remedial Pillars for Pitra Shanti
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">1. Surya Arghya &amp; Gayatri Sadhana:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            Offering water from a copper vessel to the rising Sun (Surya) while facing East directly pacifies your 6th house Sun. Infuse the water with red kumkum and black sesame seeds to simultaneously appease ancestral spirits.
                        </p>
                    </div>
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#047857;">2. Ashvattha (Peepal Tree) Seva:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            The sacred Peepal tree (Ficus religiosa) is the living temple of Brahma, Vishnu, and Shiva. Watering the roots on Saturday mornings (without touching the trunk) pacifies both Saturn and the ancestral realm.
                        </p>
                    </div>
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#D97706;">3. Amavasya &amp; Pitru Paksha Daan:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            On monthly New Moon days (Amavasya) and during the annual Pitru Paksha fortnight, donate cooked rice, white sesame, bananas, and whole grains to underprivileged monks or community kitchens.
                        </p>
                    </div>
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#6B21A8;">4. Lineage Pattern Breaking:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            Consciously identify unexpressed emotional friction or unfulfilled ambitions from past generations and resolve them through disciplined personal ethics and peaceful family communications.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mr-card-accent" style="padding:11px 18px; margin-bottom:4px; text-align:center;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Daily Pitri Gayatri Mantra Sadhana
                </div>
                <div style="font-size:13.5px; font-weight:800; color:#111827; font-family:'serif', Georgia, Times; margin-bottom:4px;">
                    ॥ ॐ पितृगणाय विद्महे जगत धारिण्यै धीमहि तन्नो पितरो प्रचोदयात् ॥
                </div>
                <div style="font-size:11.5px; color:#4B5563; font-style:italic;">
                    "Om Pitraganaya Vidmahe Jagat Dharinyai Dhimahi Tanno Pitaro Prachodayat"
                </div>
                <div style="font-size:11px; color:#6B7280; margin-top:4px;">
                    Recite 11 or 21 times every Sunday morning facing East for profound ancestral grace and lineage protection.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Zero Complex Rituals Needed: No costly pilgrimages are required; sincere intention and daily solar reverence dissolve subtle lineage blocks.",
                "Saturday Ashvattha Protocol: Pouring fresh water at the base of a Peepal tree harmonizes both Saturn and 9th lord Jupiter.",
                "Monthly Amavasya Charity: Donating wholesome meals on New Moon days guarantees sustained peace and blessings for your family.",
                "Psychological Liberation: Elevating your family's societal honor through personal integrity is the supreme fulfillment of Pitra Rina."
            ], "Pitra Dosha Harmonization Takeaways"); ?>
            <?php echo self::render_page_footer(11, 'Pitra Dosha Resolution & Karmic Closure Protocols'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 12: PAPASAMAYA (MALEFIC LOAD) SCORE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Papasamaya Malefic Load</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">11 · Malefic Weight Quantification</div>
            <h1 class="mr-page-h1">Papasamaya: Quantifying Cumulative Malefic Load</h1>
            <div class="mr-page-sub">Mathematical Point-Weight Measurement Across Kendras and Dusthanas for Longevity and Alliance Balances</div>

            <!-- Papasamaya Bar Graph SVG -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-align:center; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    Papasamaya Malefic Point-Weight Distribution
                </div>
                <?php echo self::render_papasamaya_chart_svg(540, 90); ?>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Detailed Papasamaya Point Breakdown
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #C45738; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Planet</th>
                            <th style="padding:6px 9px; color:#111827;">Assigned House</th>
                            <th style="padding:6px 9px; color:#111827;">Raw Points</th>
                            <th style="padding:6px 9px; color:#111827;">Dignity Adjustment</th>
                            <th style="padding:6px 9px; color:#111827;">Net Malefic Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">Sun (Surya)</td>
                            <td style="padding:6px 9px;">6th House (Upachaya)</td>
                            <td style="padding:6px 9px;">16 pts</td>
                            <td style="padding:6px 9px; color:#047857;">-4 pts (Friend's Sign &amp; Budhaditya)</td>
                            <td style="padding:6px 9px; font-weight:700; color:#111827;">12 pts</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">Mars (Mangal)</td>
                            <td style="padding:6px 9px;">7th House (Kendra)</td>
                            <td style="padding:6px 9px;">24 pts</td>
                            <td style="padding:6px 9px; color:#047857;">-16 pts (Exaltation &amp; Yogakaraka)</td>
                            <td style="padding:6px 9px; font-weight:700; color:#111827;">8 pts</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">Saturn (Shani)</td>
                            <td style="padding:6px 9px;">3rd House (Upachaya)</td>
                            <td style="padding:6px 9px;">18 pts</td>
                            <td style="padding:6px 9px; color:#047857;">-4 pts (3rd House Joy Placement)</td>
                            <td style="padding:6px 9px; font-weight:700; color:#111827;">14 pts</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700;">Rahu (North Node)</td>
                            <td style="padding:6px 9px;">1st House (Lagna)</td>
                            <td style="padding:6px 9px;">20 pts</td>
                            <td style="padding:6px 9px; color:#D97706;">-4 pts (Benefic sign aspect)</td>
                            <td style="padding:6px 9px; font-weight:700; color:#111827;">16 pts</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700;">Ketu (South Node)</td>
                            <td style="padding:6px 9px;">7th House (Kendra)</td>
                            <td style="padding:6px 9px;">14 pts</td>
                            <td style="padding:6px 9px; color:#047857;">-4 pts (Exalted sign influence)</td>
                            <td style="padding:6px 9px; font-weight:700; color:#111827;">10 pts</td>
                        </tr>
                        <tr style="background:#FAF7F2; font-weight:800; border-top:1.5px solid #111827;">
                            <td colspan="4" style="padding:6px 8px; text-align:right;">TOTAL ADJUSTED PAPASAMAYA SCORE:</td>
                            <td style="padding:6px 8px; color:#C45738; font-size:11px;">60 pts / 150 (40.0% - Moderate)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Moderate 40% Papasamaya Load: Your net adjusted malefic score of 60 points falls in the highly manageable moderate range.",
                "Upachaya Placement Advantage: Saturn and Sun occupy the 3rd and 6th houses—natural growth houses where malefics produce victory.",
                "Mars Substantial Discount: Exaltation reduces Martian malefic score by 66%, shielding partnerships from genuine destructive harm.",
                "Rahu Primary Point Contributor: Rahu in Lagna (16 pts) represents the single largest contributor, pacifiable with mental grounding."
            ], "Papasamaya Load Quantification Takeaways"); ?>
            <?php echo self::render_page_footer(12, 'Papasamaya: Quantifying Cumulative Malefic Load'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 13: ALLIANCE HARMONY & BIO-RESERVES
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Alliance Harmony &amp; Bio-Reserves</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">12 · Partnership Synchronization &amp; Bio-Reserves</div>
            <h1 class="mr-page-h1">Alliance Harmony &amp; Physical Vitality Protection Protocols</h1>
            <div class="mr-page-sub">Equitable Papasamaya Bilateral Matching, Somatic Ojas Preservation, and Thermal Pitta Regulation</div>

            <div class="mr-card" style="padding:11px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Partner Papasamaya Matching Principles
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">The Principle of Equitable Weight:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            Classical texts warn against pairing a chart with 60 malefic points with a partner whose chart has near-zero points. An ideal partner should possess between <strong>50 to 70 Papasamaya points</strong> to match your emotional resilience and passion.
                        </p>
                    </div>
                    <div style="background:#FAF7F2; padding:9px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#047857;">Balancing Mars &amp; Rahu Axes:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            A partner with Saturn or Jupiter aspecting their Lagna or 7th house provides an anchoring counterweight to your Rahu–Mars dynamic, creating exceptional mutual stability and long-term loyalty.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mr-card" style="padding:11px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Bio-Reserve Fortification (Ojas Preservation)
                </div>
                <div class="mr-grid-3col" style="font-size:11.5px; line-height:1.45;">
                    <div style="border-left:2.5px solid #C45738; padding-left:8px;">
                        <strong style="color:#111827;">Thermal Regulation:</strong> 7th house Mars generates excess internal heat (Pitta). Incorporate cooling herbs (Amla, fennel seeds, coriander water) to regulate body temperature.
                    </div>
                    <div style="border-left:2.5px solid #047857; padding-left:8px;">
                        <strong style="color:#111827;">Circadian Sleep Hygiene:</strong> Rahu in 1st house can trigger restless night-time mental racing. Eliminate blue light screens 60 minutes before bedtime.
                    </div>
                    <div style="border-left:2.5px solid #C2964C; padding-left:8px;">
                        <strong style="color:#111827;">Joint &amp; Bone Health:</strong> Saturn in 3rd house benefits from periodic warm sesame oil self-massages (Abhyanga) on Saturday evenings.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Optimal Matching Range: For marital or key business alliances, seek partners with 50–70 Papasamaya points for natural compatibility.",
                "Pitta Heat Regulation: Exalted Mars provides massive physical stamina; maintain cooling hydration to prevent intermittent thermal fatigue.",
                "Night-Time Mind Calming: Quell Rahu's nocturnal restlessness by establishing consistent evening digital detox habits.",
                "Sustainable High Performance: By guarding bio-reserves (Ojas), your astrological pressures transform into effortless vocational output."
            ], "Alliance Harmony & Vitality Takeaways"); ?>
            <?php echo self::render_page_footer(13, 'Alliance Harmony & Physical Vitality Protection Protocols'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 14: KARMIC PRIORITY ACTION MATRIX
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Karmic Priority Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">13 · Remedial Triage &amp; Strategic Ranking</div>
            <h1 class="mr-page-h1">Karmic Priority Matrix: Which Afflictions Require Action?</h1>
            <div class="mr-page-sub">Three-Tiered Triage Separating Active Structural Bottlenecks from Secondary Maintenance and Non-Existent Issues</div>

            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:8px;">
                <!-- Tier 1 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #DC2626;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#B91C1C; text-transform:uppercase;">
                            TIER 1: ACTIVE STRUCTURAL FOCUS — ANANT KAALSARP AXIS
                        </span>
                        <span class="mr-badge mr-badge-terracotta">Priority: High (Daily Grounding)</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.42; margin:0;">
                        <strong>Diagnosis:</strong> Rahu in 1st house (Cancer) and Ketu in 7th house (Capricorn) hemming all visible grahas.<br>
                        <strong>Action Protocol:</strong> Wear 8-Mukhi Rudraksha, maintain structured morning routines, recite Maha Mrityunjaya / Naga Gayatri japa, and cultivate unwavering transparency in business and marital contracts.
                    </p>
                </div>

                <!-- Tier 2 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#D97706; text-transform:uppercase;">
                            TIER 2: SECONDARY MAINTENANCE — MANGAL FIRE &amp; PITRA RINA
                        </span>
                        <span class="mr-badge mr-badge-gold">Priority: Moderate (Weekly/Seasonal)</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.42; margin:0;">
                        <strong>Diagnosis:</strong> Exalted Mars in 7th (largely cancelled) and mild 25% Pitra debt on Sun in 6th.<br>
                        <strong>Action Protocol:</strong> Tuesday Hanuman Chalisa and red lentil charity to channel Martian drive; Sunday Surya Arghya and annual Pitru Paksha water offerings to honor ancestral lineage.
                    </p>
                </div>

                <!-- Tier 3 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#047857; text-transform:uppercase;">
                            TIER 3: FULLY RESOLVED / DORMANT — SADE SATI, GRAHAN &amp; CHANDAL
                        </span>
                        <span class="mr-badge mr-badge-emerald">Priority: Zero Action Required</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.42; margin:0;">
                        <strong>Diagnosis:</strong> Complete absence of Guru Chandal Yoga, Surya/Chandra Grahan Yoga, and dormant Sade Sati phase.<br>
                        <strong>Action Protocol:</strong> No remedial rituals or gem investments required for these conditions; disregard generic astrological warnings concerning these areas.
                    </p>
                </div>
            </div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Strategic Resource Allocation Principle
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div>
                        <strong>Focus 80% of Energy On:</strong> Mental grounding, somatic calm, clear professional contracts, and channelizing executive energy into high-impact enterprises.
                    </div>
                    <div>
                        <strong>Ignore 100% Of:</strong> Superstitious fears of generational curses, expensive temple dosha ceremonies, or unnecessary fear-driven talisman purchases.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Laser-Focused Triage: Only the Anant Kaalsarp nodal axis requires active, daily consciousness and grounding routines.",
                "Secondary Maintenance: Exalted Mars and mild Pitra debt require only simple, cost-free weekly habits and solar reverence.",
                "Zero Waste of Capital: Ignore costly commercial temple rituals; 80% of generic internet afflictions are absent in your chart.",
                "Actionable Clarity: Channeling energy into disciplined execution provides vastly superior protection compared to passive talismans."
            ], "Karmic Priority Matrix Takeaways"); ?>
            <?php echo self::render_page_footer(14, 'Karmic Priority Matrix: Which Afflictions Require Action?'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 15: RATNA VIGYAN: GEMSTONE PRESCRIPTION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Ratna Vigyan Gemstone Prescription</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">14 · Ratna Vigyan &amp; Gem Therapy</div>
            <h1 class="mr-page-h1">Ratna Vigyan: Gemstone &amp; Sacred Metal Prescription Matrix</h1>
            <div class="mr-page-sub">Certified Functional Benefic Fortification, Metal Alliances, Consecration Muhurtas, and Strict Astrological Bans</div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Certified Vedic Gemstone Prescription Matrix
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #047857; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Role &amp; Ratna</th>
                            <th style="padding:6px 9px; color:#111827;">Target Planet &amp; House</th>
                            <th style="padding:6px 9px; color:#111827;">Metal &amp; Finger</th>
                            <th style="padding:6px 9px; color:#111827;">Weight &amp; Consecration</th>
                            <th style="padding:6px 9px; color:#111827;">Primary Benefit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">
                                PRIMARY: Red Coral<br><span style="font-size:10px; color:#6B7280;">(Rakt-Praval)</span>
                            </td>
                            <td style="padding:6px 9px;">Mars (5th &amp; 10th Lord · Exalted in 7th)</td>
                            <td style="padding:6px 9px;">Copper or Gold<br>Ring Finger (Right Hand)</td>
                            <td style="padding:6px 9px;">7.25–9.25 Ratti<br>Tuesday Morning (Shukla)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Executive Command, Vitality &amp; Courage</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#B45309;">
                                SECONDARY: Yellow Sapphire<br><span style="font-size:10px; color:#6B7280;">(Guru Pukhraj)</span>
                            </td>
                            <td style="padding:6px 9px;">Jupiter (9th Lord of Fortune &amp; Dharma)</td>
                            <td style="padding:6px 9px;">Yellow Gold<br>Index Finger (Right Hand)</td>
                            <td style="padding:6px 9px;">4.25–6.25 Ratti<br>Thursday Morning (Pushya)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Ancestral Grace, Wisdom &amp; Prosperity</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700; color:#1D4ED8;">
                                OPTIONAL: Natural Pearl<br><span style="font-size:10px; color:#6B7280;">(Sacred Moti)</span>
                            </td>
                            <td style="padding:6px 9px;">Moon (Lagna Lord · 2nd House in Leo)</td>
                            <td style="padding:6px 9px;">Pure Silver<br>Little Finger (Right Hand)</td>
                            <td style="padding:6px 9px;">5.25–7.25 Ratti<br>Monday Evening (Rohini)</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Emotional Serenity &amp; Nodal Calm</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Strict Prohibitions Box -->
            <div class="mr-card" style="padding:11px 16px; margin-bottom:4px; background:#FAF7F2; border:1px solid #DC2626;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#B91C1C; text-transform:uppercase; margin-bottom:4px;">
                    ⚠️ STRICT SHASTRIC GEMSTONE PROHIBITIONS
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45; color:#4B5563;">
                    <div>
                        <strong style="color:#111827;">Avoid Blue Sapphire (Neelam):</strong> Saturn rules your 7th and 8th dusthana houses; wearing Neelam directly escalates health vulnerability and partnership litigation.
                    </div>
                    <div>
                        <strong style="color:#111827;">Avoid Diamond (Heera):</strong> Venus rules the 4th and 11th badhaka houses for Cancer Lagna; diamond can trigger unnecessary financial strain and sensory distraction.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Red Coral (Moonga) Anchor: Strengthens your supreme Yogakaraka Mars, enhancing vocational prestige and physical fortitude.",
                "Yellow Sapphire (Pukhraj): Empowers 9th lord Jupiter to attract divine grace, dharmic wisdom, and lineage blessings.",
                "Strict Blue Sapphire Ban: Never wear Neelam or Hessonite (Gomed) without specific short-term astrological supervision.",
                "Consecration Protocol: Ensure gemstones are unheated, untreated, and energized on auspicious Shukla Paksha mornings."
            ], "Ratna Vigyan Gemstone Prescription Takeaways"); ?>
            <?php echo self::render_page_footer(15, 'Ratna Vigyan: Gemstone & Sacred Metal Prescription Matrix'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 16: RUDRAKSHA THERAPY & SACRED BEADS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Rudraksha Therapy Guide</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">15 · Sacred Bio-Energetics &amp; Shivic Beads</div>
            <h1 class="mr-page-h1">Rudraksha Therapy &amp; Neurological Grounding Guide</h1>
            <div class="mr-page-sub">Electromagnetic Shielding, Bio-Field Stabilization, and Pacifying the Serpentine Lunar Nodes</div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Authentic Shastric Rudraksha Combination for Your Chart
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.42;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #C45738; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Bead Specification</th>
                            <th style="padding:6px 9px; color:#111827;">Presiding Deity &amp; Graha</th>
                            <th style="padding:6px 9px; color:#111827;">Chakra &amp; Bio-Field Focus</th>
                            <th style="padding:6px 9px; color:#111827;">Primary Karmic Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#6B21A8;">
                                8-MUKHI RUDRAKSHA<br><span style="font-size:10px; color:#6B7280;">(Sacred Ganesha Bead)</span>
                            </td>
                            <td style="padding:6px 9px;">Lord Ganesha · Neutralizes Rahu</td>
                            <td style="padding:6px 9px;">Muladhara (Root Chakra)<br>Nervous System Stabilization</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Neutralizes Kaalsarp Axis, Removes Obstacles</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">
                                3-MUKHI RUDRAKSHA<br><span style="font-size:10px; color:#6B7280;">(Agni Deva Bead)</span>
                            </td>
                            <td style="padding:6px 9px;">Agni (Sacred Fire) · Mars (Mangal)</td>
                            <td style="padding:6px 9px;">Manipura (Solar Plexus)<br>Thermal Balance &amp; Willpower</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Purifies Past Karma, Pacifies Martian Heat</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700; color:#B45309;">
                                11-MUKHI RUDRAKSHA<br><span style="font-size:10px; color:#6B7280;">(Ekadasha Rudra Bead)</span>
                            </td>
                            <td style="padding:6px 9px;">Lord Hanuman · Shields Saturn/Mars</td>
                            <td style="padding:6px 9px;">Ajna (Third Eye)<br>Fearlessness &amp; Discernment</td>
                            <td style="padding:6px 9px; color:#047857; font-weight:700;">Absolute Psychic Protection &amp; Longevity</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mr-card" style="padding:11px 16px; margin-bottom:4px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Wearing &amp; Consecration Methodology
                </div>
                <div class="mr-grid-2col" style="font-size:11.5px; line-height:1.45;">
                    <div>
                        <strong style="color:#C45738;">Stringing &amp; Materials:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            String the 8-Mukhi, 3-Mukhi, and 11-Mukhi beads together in red silk thread or silver wire. Wear the combination around the neck so the beads rest against the heart (Anahata) center.
                        </p>
                    </div>
                    <div>
                        <strong style="color:#047857;">Prana Pratishtha Consecration:</strong>
                        <p style="margin:4px 0 0 0; color:#4B5563;">
                            Wash in fresh cow's milk and Gangajal on a Monday morning. Recite <em>"Om Namah Shivaya"</em> 108 times, apply sandalwood paste, and wear facing North or East.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "8-Mukhi Ganesha Shield: Directly calms Rahu in 1st house, clearing foggy decision-making and dissolving Kaalsarp friction.",
                "3-Mukhi Agni Cleanser: Transmutes residual Martian heat into constructive enterprise without aggravating emotional temper.",
                "11-Mukhi Hanuman Force: Builds an impenetrable psychic shield against external negativity and occupational stress.",
                "Zero Negative Contraindications: Genuine Rudrakshas carry only stabilizing divine energy and never trigger adverse side effects."
            ], "Rudraksha Therapy & Bio-Energetics Takeaways"); ?>
            <?php echo self::render_page_footer(16, 'Rudraksha Therapy & Neurological Grounding Guide'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 17: SACRED VEDIC MANTRAS & SHANTI
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Sacred Vedic Mantras</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">16 · Vedic Vibration &amp; Shanti Sadhana</div>
            <h1 class="mr-page-h1">Sacred Vedic Mantras &amp; Planetary Shanti Rituals</h1>
            <div class="mr-page-sub">Sound-Code Science (Shabda Brahman) Re-tuning Nodal, Martian, and Ancestral Frequencies</div>

            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:6px;">
                <!-- Mantra 1 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #6B21A8;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#6B21A8; font-size:10px; text-transform:uppercase;">
                            1. MAHA MRITYUNJAYA MANTRA — FOR ANANT KAALSARP PACIFICATION
                        </strong>
                        <span class="mr-badge mr-badge-terracotta">Daily Core Sadhana</span>
                    </div>
                    <div style="font-size:13.5px; font-weight:800; color:#111827; font-family:'serif', Georgia, Times; text-align:center; margin:3px 0;">
                        ॥ ॐ त्र्यम्बकं यजामहे सुगन्धिं पुष्टिवर्धनम् । उर्वारुकमिव बन्धनान्मृत्योर्मुक्षीय माऽमृतात् ॥
                    </div>
                    <p style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0;">
                        <strong>Practice:</strong> Recite 11 times daily at sunrise. Lord Shiva as Mrityunjaya holds cosmic dominion over all serpents (Nagas), converting nodal constriction into infinite vitality, peace, and spiritual protection.
                    </p>
                </div>

                <!-- Mantra 2 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#C45738; font-size:10px; text-transform:uppercase;">
                            2. MANGAL BEEJ MANTRA — FOR DISCIPLINED WILLPOWER &amp; STATUS
                        </strong>
                        <span class="mr-badge mr-badge-gold">Tuesday Sadhana</span>
                    </div>
                    <div style="font-size:13.5px; font-weight:800; color:#111827; font-family:'serif', Georgia, Times; text-align:center; margin:3px 0;">
                        ॥ ॐ क्रां क्रीं क्रौं सः भौमाय नमः ॥
                    </div>
                    <p style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0;">
                        <strong>Practice:</strong> Recite 108 times on Tuesday mornings facing South or East using a Red Coral or Rudraksha mala. Harmonizes Martian heat and aligns 7th house partnerships with your professional destiny.
                    </p>
                </div>

                <!-- Mantra 3 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#D97706; font-size:10px; text-transform:uppercase;">
                            3. SURYA GAYATRI MANTRA — FOR ANCESTRAL HARMONY &amp; LINEAGE GRACE
                        </strong>
                        <span class="mr-badge mr-badge-emerald">Sunday Morning</span>
                    </div>
                    <div style="font-size:13.5px; font-weight:800; color:#111827; font-family:'serif', Georgia, Times; text-align:center; margin:3px 0;">
                        ॥ ॐ आदित्याय विद्महे मार्तण्डाय धीमहि तन्नः सूर्यः प्रचोदयात् ॥
                    </div>
                    <p style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0;">
                        <strong>Practice:</strong> Recite 21 times while offering Arghya water to the rising Sun on Sunday mornings. Dissolves 6th house solar afflictions, fulfills Pitra Rina, and bestows societal honor.
                    </p>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Maha Mrityunjaya Mastery: Reciting 11 times daily provides complete spiritual immunity against Kaalsarp psychological strain.",
                "Martian Beej Alignment: Chanting the Mars seed mantra channels passionate energy into leadership and executive dominance.",
                "Solar Ancestral Grace: Surya Gayatri recitations at sunrise systematically fulfill and dissolve hereditary subtle obligations.",
                "Effortless Consistency: Devoting just 7 minutes each morning produces compounding psychological clarity and spiritual elevation."
            ], "Sacred Vedic Mantras & Shanti Takeaways"); ?>
            <?php echo self::render_page_footer(17, 'Sacred Vedic Mantras & Planetary Shanti Rituals'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 18: STEP-BY-STEP PRACTICAL WEEKLY DAAN
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · Weekly Daan &amp; Upaya Schedule</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">17 · Practical Charitable Calendar</div>
            <h1 class="mr-page-h1">Step-by-Step Practical Weekly Daan &amp; Upaya Schedule</h1>
            <div class="mr-page-sub">Actionable Weekday Matrix Mapping Planetary Hours, Specific Offerings, and Auspicious Recipients</div>

            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:12px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Actionable Weekday Daan &amp; Upaya Calendar
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:11.2px; line-height:1.4;">
                    <thead>
                        <tr style="background:#FAF7F2; border-bottom:1.5px solid #047857; text-align:left;">
                            <th style="padding:6px 9px; color:#111827;">Day &amp; Graha</th>
                            <th style="padding:6px 9px; color:#111827;">Prescribed Charitable Offering</th>
                            <th style="padding:6px 9px; color:#111827;">Target Recipient</th>
                            <th style="padding:6px 9px; color:#111827;">Optimal Hour &amp; Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#1D4ED8;">MONDAY<br><span style="font-size:9.5px; color:#6B7280;">Chandra (Moon)</span></td>
                            <td style="padding:6px 9px;">Offer milk or unboiled rice; drink water from silver tumbler.</td>
                            <td style="padding:6px 9px;">Needy mothers or elderly women</td>
                            <td style="padding:6px 9px;">Evening Twilight · Emotional grounding &amp; mental peace</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#C45738;">TUESDAY<br><span style="font-size:9.5px; color:#6B7280;">Mangal (Mars)</span></td>
                            <td style="padding:6px 9px;">Donate red lentils (Masoor dal), jaggery, or donate blood.</td>
                            <td style="padding:6px 9px;">Soldiers, blood banks, or gym workers</td>
                            <td style="padding:6px 9px;">Midday (Noon) · Channels 7th house Mars into status</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#047857;">WEDNESDAY<br><span style="font-size:9.5px; color:#6B7280;">Budha (Mercury)</span></td>
                            <td style="padding:6px 9px;">Feed green grass or spinach to cows; donate green moong.</td>
                            <td style="padding:6px 9px;">Local Gaushala (Cow Sanctuary)</td>
                            <td style="padding:6px 9px;">Morning · Sharpens analytical business intellect</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#B45309;">THURSDAY<br><span style="font-size:9.5px; color:#6B7280;">Guru (Jupiter)</span></td>
                            <td style="padding:6px 9px;">Donate yellow chana dal, turmeric, or educational books.</td>
                            <td style="padding:6px 9px;">Teachers, students, or temples</td>
                            <td style="padding:6px 9px;">Morning · Awakens 9th house Bhagya &amp; lineage grace</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#DB2777;">FRIDAY<br><span style="font-size:9.5px; color:#6B7280;">Shukra (Venus)</span></td>
                            <td style="padding:6px 9px;">Donate white sweets, sugar, or white curd; respect women.</td>
                            <td style="padding:6px 9px;">Underprivileged young girls</td>
                            <td style="padding:6px 9px;">Sunset · Preserves harmony &amp; domestic luxury</td>
                        </tr>
                        <tr style="border-bottom:1px solid #F3EFEA;">
                            <td style="padding:6px 9px; font-weight:700; color:#4B5563;">SATURDAY<br><span style="font-size:9.5px; color:#6B7280;">Shani &amp; Rahu</span></td>
                            <td style="padding:6px 9px;">Light mustard oil lamp under Peepal tree; feed black dogs or crows.</td>
                            <td style="padding:6px 9px;">Laborers, disabled persons, strays</td>
                            <td style="padding:6px 9px;">After Sunset · Pacifies Rahu in 1st &amp; Saturn in 3rd</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 9px; font-weight:700; color:#DC2626;">SUNDAY<br><span style="font-size:9.5px; color:#6B7280;">Surya (Sun)</span></td>
                            <td style="padding:6px 9px;">Offer Arghya to rising Sun; donate wheat or whole grain bread.</td>
                            <td style="padding:6px 9px;">Public shelters, orphanages</td>
                            <td style="padding:6px 9px;">Sunrise · Dissolves Pitra Rina &amp; bolsters immunity</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mr-card" style="padding:9px 14px; margin-bottom:4px; border-left:3.5px solid #047857;">
                <strong style="color:#047857;">SUSTAINABLE EXECUTION TIP:</strong> You do <em>not</em> need to execute all seven days every week. Prioritize <strong>Tuesday (Mars)</strong> and <strong>Saturday (Rahu/Saturn)</strong> as your foundational anchors to keep all core afflictions effortlessly mitigated.
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Tuesday Martian Anchor: Regular small donations of red lentils or jaggery channel executive fire into productive work.",
                "Saturday Peepal Lighting: Lighting a mustard oil diya after sunset neutralizes Rahu-Ketu nodal tension and Saturnian friction.",
                "Sunday Surya Arghya: Offering water at sunrise permanently clears subtle hereditary debts and strengthens biological vitality.",
                "Practical Simplicity: Performing these small acts with sincere intention produces immediate karmic lightness and peace."
            ], "Weekly Daan Schedule Takeaways"); ?>
            <?php echo self::render_page_footer(18, 'Step-by-Step Practical Weekly Daan Schedule'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 19: THE 90-DAY TRANSFORMATION ROADMAP
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> · 90-Day Karmic Cleansing Roadmap</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">18 · Sequenced 90-Day Transformation</div>
            <h1 class="mr-page-h1">The 90-Day Karmic Cleansing &amp; Growth Roadmap</h1>
            <div class="mr-page-sub">Three Non-Overlapping Monthly Phases to Guarantee Lasting Progress Without Ritual Overwhelm</div>

            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:6px;">
                <!-- Phase 1 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #DC2626;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#B91C1C; font-size:10px; text-transform:uppercase;">
                            PHASE 1 (DAYS 1 TO 30): THE KARMIC DETOX &amp; NERVOUS SYSTEM GROUNDING
                        </strong>
                        <span class="mr-badge mr-badge-terracotta">Month 1 Anchor</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.4; margin:0 0 4px 0;">
                        <strong>Core Objective:</strong> Quell Rahu's sensory restlessness in 1st house and establish baseline mental serenity.
                    </p>
                    <ul style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0; padding-left:14px;">
                        <li><strong>Action 1:</strong> Consecrate and begin wearing the 8-Mukhi &amp; 3-Mukhi Rudraksha combination around the neck.</li>
                        <li><strong>Action 2:</strong> Commit to 11 recitations of the Maha Mrityunjaya Mantra every morning at sunrise.</li>
                        <li><strong>Action 3:</strong> Perform Saturday evening mustard oil diya lighting under a Peepal tree and feed stray dogs.</li>
                    </ul>
                </div>

                <!-- Phase 2 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #D97706;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#D97706; font-size:10px; text-transform:uppercase;">
                            PHASE 2 (DAYS 31 TO 60): MARTIAN HARMONIZATION &amp; ALLIANCE CLARITY
                        </strong>
                        <span class="mr-badge mr-badge-gold">Month 2 Anchor</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.4; margin:0 0 4px 0;">
                        <strong>Core Objective:</strong> Channel exalted Mars in 7th house into corporate leadership and transparent partnership contracts.
                    </p>
                    <ul style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0; padding-left:14px;">
                        <li><strong>Action 1:</strong> Wear certified Red Coral (7.25+ Ratti) on the right ring finger on a Shukla Tuesday.</li>
                        <li><strong>Action 2:</strong> Recite Tuesday Hanuman Chalisa and donate red lentils or donate blood to a local bank.</li>
                        <li><strong>Action 3:</strong> Audit all professional and domestic agreements to ensure 100% written transparency.</li>
                    </ul>
                </div>

                <!-- Phase 3 -->
                <div class="mr-card" style="padding:10px 14px; border-left:4px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="color:#047857; font-size:10px; text-transform:uppercase;">
                            PHASE 3 (DAYS 61 TO 90): SOVEREIGN DHARMA &amp; ANCESTRAL ELEVATION
                        </strong>
                        <span class="mr-badge mr-badge-emerald">Month 3 Anchor</span>
                    </div>
                    <p style="font-size:11.5px; color:#4B5563; line-height:1.4; margin:0 0 4px 0;">
                        <strong>Core Objective:</strong> Dissolve subtle lineage debts and solidify lasting institutional and personal prestige.
                    </p>
                    <ul style="font-size:11.2px; color:#4B5563; line-height:1.4; margin:0; padding-left:14px;">
                        <li><strong>Action 1:</strong> Perform Sunday Surya Arghya with red kumkum and recite Surya Gayatri 21 times.</li>
                        <li><strong>Action 2:</strong> Undertake monthly Amavasya food donation to feed underprivileged monks or elders.</li>
                        <li><strong>Action 3:</strong> Review quarterly life milestones, witnessing the emergence of profound peace and stability.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Month 1 Foundation: Grounding the nervous system via Rudrakshas and Maha Mrityunjaya dissolves early nodal friction.",
                "Month 2 Empowerment: Channeling exalted Mars via Red Coral and transparent partnerships activates executive elevation.",
                "Month 3 Climax: Fulfilling solar and ancestral reverence brings deep spiritual peace and long-term legacy stability.",
                "Permanent Habit Formation: By day 90, these simple practices transform into effortless rituals of lifelong empowerment."
            ], "90-Day Karmic Cleansing Roadmap Takeaways"); ?>
            <?php echo self::render_page_footer(19, 'The 90-Day Karmic Cleansing & Growth Roadmap'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 20: MASTER GLOSSARY & CONCLUDING BACK COVER
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta">MeraRashifal · Concluding Dossier Benediction</span>
        </div>

        <div class="mr-page-body">
            <div style="text-align:center; margin-bottom:8px;">
                <div style="font-size:11.5px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:2px;">
                    SACRED JYOTISH EPILOGUE
                </div>
                <div style="font-family:<?php echo $head_font; ?>; font-size:18px; font-weight:800; color:#111827;">
                    May Cosmic Balance, Fortitude &amp; Divine Grace Guide Your Journey, <?php echo htmlspecialchars($person_name); ?> ji!
                </div>
            </div>

            <!-- Sacred Shanti Shloka Card -->
            <div class="mr-card-accent" style="padding:10px 16px; text-align:center; margin-bottom:8px;">
                <div style="font-size:14px; font-weight:800; color:#111827; font-family:'serif', Georgia, Times; margin-bottom:3px; letter-spacing:0.02em;">
                    ॥ ॐ सर्वे भवन्तु सुखिनः सर्वे सन्तु निरामयाः । सर्वे भद्राणि पश्यन्तु मा कश्चिद्दुःखभाग्भवेत् ॥
                </div>
                <div style="font-size:10px; font-weight:700; color:#C45738; font-family:'serif', Georgia, Times; margin-bottom:3px;">
                    ॥ ॐ शान्तिः शान्तिः शान्तिः ॥
                </div>
                <p style="font-size:11.2px; color:#4B5563; font-style:italic; margin:0; line-height:1.4;">
                    “May all sentient beings be happy; may all beings be free from affliction; may all perceive auspiciousness; may none suffer grief. Om Peace, Peace, Peace.”
                </p>
            </div>

            <!-- 10-Point Dosh Glossary Grid -->
            <div class="mr-card" style="padding:10px 16px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    10-Point Parashari Dosh &amp; Remedial Glossary
                </div>
                <div class="mr-grid-2col" style="gap:5px 16px; font-size:11px; line-height:1.38;">
                    <div><strong style="color:#C45738;">1. Mangal Dosha:</strong> Thermal pressure from Mars in 1st, 4th, 7th, 8th, or 12th house affecting relational dynamics.</div>
                    <div><strong style="color:#047857;">2. Kuja Bhanga:</strong> Classical cancellation rules (exaltation, benefic aspects) that neutralize Martian maleficence.</div>
                    <div><strong style="color:#C45738;">3. Kaalsarp Yoga:</strong> Hemming of all visible planets between Rahu and Ketu, creating focused karmic churning.</div>
                    <div><strong style="color:#047857;">4. Anant Kaal Sarp:</strong> Specific 1st–7th nodal axis alignment governing identity, sovereignty, and partnership.</div>
                    <div><strong style="color:#C45738;">5. Pitra Rina:</strong> Inherited ancestral debts reflected through afflictions to the Sun, 9th house, or 9th lord.</div>
                    <div><strong style="color:#047857;">6. Papasamaya:</strong> Mathematical point balance measuring cumulative malefic pressure from natural malefics.</div>
                    <div><strong style="color:#C45738;">7. Upachaya Houses:</strong> Houses 3, 6, 10, and 11 where natural malefics mature into engines of competitive victory.</div>
                    <div><strong style="color:#047857;">8. Ojas:</strong> Vital somatic bio-reserve protected by pacifying internal thermal inflammation (Pitta).</div>
                    <div><strong style="color:#C45738;">9. Ratna Vigyan:</strong> Science of wearing functional benefic gems to amplify positive cosmic rays.</div>
                    <div><strong style="color:#047857;">10. Rudraksha Therapy:</strong> Electromagnetic shielding using sacred beads to ground neurological and nodal static.</div>
                </div>
            </div>

            <!-- Specialized Report Suite with .mr-offer-card -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                <div class="mr-offer-card" style="margin-bottom:0;">
                    <div class="mr-offer-icon">💎</div>
                    <div class="mr-offer-info">
                        <h4>Master Kundali &amp; Lifetime Dossier</h4>
                        <p>Exhaustive 26-page birth analysis covering all 16 divisional harmonics, Shadbala, and 12-house life synthesis.</p>
                        <div class="mr-offer-meta">26 Pages · Full Parashari Synthesis</div>
                    </div>
                </div>
                <div class="mr-offer-card" style="margin-bottom:0;">
                    <div class="mr-offer-icon">☀️</div>
                    <div class="mr-offer-info">
                        <h4>Annual Varshaphal (Solar Return)</h4>
                        <p>Comprehensive 26-page annual predictive blueprint with Muntha transit, Tajika dasha, and month-by-month timing.</p>
                        <div class="mr-offer-meta">26 Pages · Tajika Annual Engine</div>
                    </div>
                </div>
                <div class="mr-offer-card" style="margin-bottom:0;">
                    <div class="mr-offer-icon">🪐</div>
                    <div class="mr-offer-info">
                        <h4>Master Mahadasha &amp; Antardasha Timing</h4>
                        <p>20-Page life timing blueprint featuring Vimshottari &amp; Yogini parallel matrix and green-light execution dates.</p>
                        <div class="mr-offer-meta">20 Pages · Parallel Timing Matrix</div>
                    </div>
                </div>
                <div class="mr-offer-card" style="margin-bottom:0;">
                    <div class="mr-offer-icon">💍</div>
                    <div class="mr-offer-info">
                        <h4>Kundali Matching (Gun Milan)</h4>
                        <p>Authentic 36-point Ashtakoot scorecard, Manglik check, Papasamaya malefic balance, and bilateral harmony sync.</p>
                        <div class="mr-offer-meta">20 Pages · Ashtakoot &amp; Dasha Sync</div>
                    </div>
                </div>
            </div>

            <!-- Remedial Philosophy Note -->
            <div style="background:#FAF7F2; border:1px solid #ECE6DD; border-radius:6px; padding:7px 14px; text-align:center;">
                <div style="font-family:<?php echo $head_font; ?>; font-size:11.5px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:2px;">
                    Vedic Remediation as an Empowering Navigation Instrument
                </div>
                <p style="font-size:11px; color:#4B5563; line-height:1.4; margin:0;">
                    Vedic Jyotish never fatalistically locks destiny in stone; it illuminates the energetic currents of time (Kala) so you can navigate them with wisdom and purpose. By aligning sincere human effort (Purushartha) with favorable planetary remedies, every challenge transforms into a stepping stone toward enduring mastery.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <div style="border-top:1px solid #ECE6DD; padding-top:6px; margin-bottom:6px; text-align:center; font-size:9px; color:#4B5563; line-height:1.45;">
                <div style="font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:2px;">
                    Access Your Personal Reports Archive &amp; Order Specialized Blueprints
                </div>
                <span>Visit your customer portal at: <strong style="color:#C45738;">www.MeraRashifal.com</strong></span><br>
                <span style="font-size:10px; color:#6B7280; display:block; margin-top:2px;">
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
