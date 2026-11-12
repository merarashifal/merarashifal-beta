<?php
/**
 * Master Numerology & Vedic Integration Report Builder (22 Pages)
 * MeraRashifal Standard: North Indian Diamond Charts, Lo Shu Grids, Dasha Lifelines & Remedial Science
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Numero_Report_Builder {
    /**
     * Calculate Core Numerological Metrics Dynamically
     */
    public static function calculate_numerology($dob, $person_name, $target_year = 2026) {
        $clean_dob = str_replace('/', '-', trim($dob));
        $ts = strtotime($clean_dob);
        if (!$ts) {
            $dt = DateTime::createFromFormat('d-m-Y', $clean_dob);
            $ts = $dt ? $dt->getTimestamp() : time();
        }
        $day = intval(date('d', $ts));
        $month = intval(date('m', $ts));
        $year = intval(date('Y', $ts));

        $reduce = function($num) {
            while ($num > 9) {
                $sum = 0;
                foreach (str_split((string)$num) as $digit) {
                    $sum += intval($digit);
                }
                $num = $sum;
            }
            return $num;
        };

        // Mulank (Day)
        $mulank = $reduce($day);

        // Bhagyank (Full DOB sum)
        $full_sum = 0;
        foreach (str_split(date('dmY', $ts)) as $d) {
            $full_sum += intval($d);
        }
        $bhagyank = $reduce($full_sum);

        // Personal Year
        $py_sum = 0;
        foreach (str_split(date('dm', $ts) . $target_year) as $d) {
            $py_sum += intval($d);
        }
        $personal_year = $reduce($py_sum);

        // Namank (Chaldean / Cheiro)
        $chaldean_map = [
            'A'=>1, 'I'=>1, 'J'=>1, 'Q'=>1, 'Y'=>1,
            'B'=>2, 'K'=>2, 'R'=>2,
            'C'=>3, 'G'=>3, 'L'=>3, 'S'=>3,
            'D'=>4, 'M'=>4, 'T'=>4,
            'E'=>5, 'H'=>5, 'N'=>5, 'X'=>5,
            'U'=>6, 'V'=>6, 'W'=>6,
            'O'=>7, 'Z'=>7,
            'F'=>8, 'P'=>8
        ];
        $name_sum = 0;
        $clean_name = preg_replace('/[^A-Z]/', '', strtoupper($person_name));
        for ($i = 0; $i < strlen($clean_name); $i++) {
            $ch = $clean_name[$i];
            $name_sum += $chaldean_map[$ch] ?? 0;
        }
        $namank = $reduce($name_sum);

        // Lo Shu grid digit frequencies from DOB (d, m, Y)
        $loshu_counts = array_fill(1, 9, 0);
        foreach (str_split(date('dmY', $ts)) as $digit) {
            $n = intval($digit);
            if ($n >= 1 && $n <= 9) {
                $loshu_counts[$n]++;
            }
        }

        $planet_map = [
            1 => ['planet' => 'Sun (Surya)',     'name' => 'Willpower & Leadership'],
            2 => ['planet' => 'Moon (Chandra)',   'name' => 'Intuition & Harmony'],
            3 => ['planet' => 'Jupiter (Guru)',   'name' => 'Wisdom & Expansion'],
            4 => ['planet' => 'Rahu (North)',     'name' => 'Structure & Discipline'],
            5 => ['planet' => 'Mercury (Budh)',   'name' => 'Balance & Intellect'],
            6 => ['planet' => 'Venus (Shukra)',   'name' => 'Luxury & Diplomacy'],
            7 => ['planet' => 'Ketu (South)',     'name' => 'Truth & Philosophy'],
            8 => ['planet' => 'Saturn (Shani)',   'name' => 'Karmic Diligence'],
            9 => ['planet' => 'Mars (Mangal)',    'name' => 'Action & Energy'],
        ];

        return [
            'mulank'        => $mulank,
            'mulank_planet' => $planet_map[$mulank]['planet'],
            'bhagyank'      => $bhagyank,
            'bhagyank_planet'=> $planet_map[$bhagyank]['planet'],
            'namank'        => $namank,
            'namank_planet' => $planet_map[$namank]['planet'],
            'personal_year' => $personal_year,
            'py_planet'     => $planet_map[$personal_year]['planet'],
            'loshu_counts'  => $loshu_counts,
        ];
    }


    /**
     * Get Authentic MeraRashifal Brand Logo URI
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

    public static function render_page_footer($page_num, $section_name = '', $total_pages = 23) {
        ob_start();
        ?>
        <div class="mr-page-footer">
            <span class="mr-footer-left">WWW.MERARASHIFAL.COM - MASTER NUMEROLOGY &amp; VEDIC INTEGRATION REPORT &bull;</span>
            <span class="mr-footer-right">PAGE <?php echo esc_html($page_num); ?> OF <?php echo esc_html($total_pages); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_page_bottom_synthesis($bullets, $title = "Key Numerological & Astrological Takeaways") {
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
     * Render North Indian Diamond Varga Chart (Authentic Vedic Geometry)
     */
    public static function render_varga_diamond_svg($size = 310, $planets_by_house = [], $lagna_sign_num = 1, $title = '') {
        $s = $size;

        $svg = "<div style='display:flex; flex-direction:column; align-items:center;'>";
        $svg .= "<svg width='{$s}' height='{$s}' viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg' style='background:#FAF7F2; border:2px solid #C2964C; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.04); display:block;'>";
        $svg .= "<rect x='5' y='5' width='290' height='290' fill='none' stroke='#C2964C' stroke-width='2'/>";
        $svg .= "<rect x='8' y='8' width='284' height='284' fill='none' stroke='#ECE6DD' stroke-width='1'/>";
        $svg .= "<line x1='5' y1='5' x2='295' y2='295' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<line x1='295' y1='5' x2='5' y2='295' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<polygon points='150,5 295,150 150,295 5,150' fill='#FFFDF9' stroke='#C2964C' stroke-width='1.5'/>";
        $svg .= "<circle cx='150' cy='150' r='5' fill='#C2964C' fill-opacity='0.25'/>";

        // Mathematically calibrated house coordinates on 300x300 canvas
        $house_coords = [
            1  => ['num_x' => 150, 'num_y' => 52,  'pl_x' => 150, 'pl_y' => 84],
            2  => ['num_x' => 75,  'num_y' => 26,  'pl_x' => 75,  'pl_y' => 50],
            3  => ['num_x' => 26,  'num_y' => 75,  'pl_x' => 45,  'pl_y' => 104],
            4  => ['num_x' => 95,  'num_y' => 155, 'pl_x' => 60,  'pl_y' => 148],
            5  => ['num_x' => 26,  'num_y' => 225, 'pl_x' => 45,  'pl_y' => 196],
            6  => ['num_x' => 75,  'num_y' => 280, 'pl_x' => 75,  'pl_y' => 252],
            7  => ['num_x' => 150, 'num_y' => 252, 'pl_x' => 150, 'pl_y' => 208],
            8  => ['num_x' => 225, 'num_y' => 280, 'pl_x' => 225, 'pl_y' => 252],
            9  => ['num_x' => 275, 'num_y' => 225, 'pl_x' => 255, 'pl_y' => 196],
            10 => ['num_x' => 205, 'num_y' => 155, 'pl_x' => 240, 'pl_y' => 148],
            11 => ['num_x' => 275, 'num_y' => 75,  'pl_x' => 255, 'pl_y' => 104],
            12 => ['num_x' => 225, 'num_y' => 26,  'pl_x' => 225, 'pl_y' => 50],
        ];

        for ($h = 1; $h <= 12; $h++) {
            $c = $house_coords[$h];
            $sign = (($lagna_sign_num - 1 + ($h - 1)) % 12) + 1;
            // House sign number
            $svg .= "<text x='{$c['num_x']}' y='{$c['num_y']}' fill='#C45738' font-size='13' font-family='sans-serif' font-weight='800' text-anchor='middle'>{$sign}</text>";

            // Planet labels with clean vertical stacking if multiple
            if (!empty($planets_by_house[$h])) {
                $pls = $planets_by_house[$h];
                $count = count($pls);
                $start_y = $c['pl_y'] - (($count - 1) * 7.5);
                $svg .= "<text x='{$c['pl_x']}' y='{$start_y}' fill='#111827' font-size='11.5' font-family='sans-serif' font-weight='800' text-anchor='middle'>";
                foreach ($pls as $i => $pl) {
                    $dy = ($i === 0) ? '0' : '15';
                    $svg .= "<tspan x='{$c['pl_x']}' dy='{$dy}'>" . str_replace('°', '°', $pl) . "</tspan>";
                }
                $svg .= "</text>";
            }
        }

        $svg .= "</svg>";
        if (!empty($title)) {
            $svg .= "<div style='text-align:center; font-size: 13.5px; color:#111827; font-weight:800; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em;'>{$title}</div>";
        }
        $svg .= "</div>";
        return $svg;
    }

    public static function get_diamond_chart_svg($chart_type = 'D1', $size = 280) {
        ob_start();
        ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="<?php echo $size; ?>" height="<?php echo $size; ?>" style="display:block; margin:0 auto; max-width:100%; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.06));">
            <rect width="300" height="300" fill="#FAF7F2" rx="4"/>
            <rect x="5" y="5" width="290" height="290" fill="none" stroke="#C2964C" stroke-width="2"/>
            <rect x="8" y="8" width="284" height="284" fill="none" stroke="#ECE6DD" stroke-width="1"/>
            
            <line x1="5" y1="5" x2="295" y2="295" stroke="#C2964C" stroke-width="1.5"/>
            <line x1="295" y1="5" x2="5" y2="295" stroke="#C2964C" stroke-width="1.5"/>
            <polygon points="150,5 295,150 150,295 5,150" fill="none" stroke="#C2964C" stroke-width="1.5"/>
            
            <!-- House 1: Cancer (Sign 4), Rahu -->
            <text x="150" y="65" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">4</text>
            <text x="150" y="105" text-anchor="middle" font-family="'Inter', sans-serif" font-size="15" font-weight="800" fill="#6B21A8">Ra 28°</text>

            <!-- House 2: Leo (Sign 5), Jupiter + Moon -->
            <text x="75" y="38" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">5</text>
            <text x="75" y="65" text-anchor="middle" font-family="'Inter', sans-serif" font-size="14.4" font-weight="800" fill="#B45309">Ju 19° <tspan fill="#1E40AF">Mo 24°</tspan></text>

            <!-- House 3: Virgo (Sign 6), Saturn -->
            <text x="38" y="75" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">6</text>
            <text x="44" y="115" text-anchor="middle" font-family="'Inter', sans-serif" font-size="14.4" font-weight="800" fill="#111827">Sa 15°</text>

            <!-- House 4: Libra (Sign 7) -->
            <text x="100" y="155" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">7</text>

            <!-- House 5: Scorpio (Sign 8), Venus -->
            <text x="38" y="225" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">8</text>
            <text x="48" y="200" text-anchor="middle" font-family="'Inter', sans-serif" font-size="14.4" font-weight="800" fill="#047857">Ve 26°</text>

            <!-- House 6: Sagittarius (Sign 9), Sun, Mercury -->
            <text x="75" y="265" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">9</text>
            <text x="82" y="240" text-anchor="middle" font-family="'Inter', sans-serif" font-size="13.8" font-weight="800" fill="#DC2626">Su 28° <tspan fill="#059669">Me 14°</tspan></text>

            <!-- House 7: Capricorn (Sign 10), Mars (Exalted), Ketu -->
            <text x="150" y="235" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">10</text>
            <text x="150" y="200" text-anchor="middle" font-family="'Inter', sans-serif" font-size="15" font-weight="800" fill="#B91C1C">Ma 22° (Uchha) <tspan fill="#6B21A8">Ke 28°</tspan></text>

            <!-- House 8: Aquarius (Sign 11) -->
            <text x="225" y="265" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">11</text>

            <!-- House 9: Pisces (Sign 12) -->
            <text x="262" y="225" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">12</text>

            <!-- House 10: Aries (Sign 1) -->
            <text x="200" y="155" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">1</text>

            <!-- House 11: Taurus (Sign 2) -->
            <text x="262" y="75" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">2</text>

            <!-- House 12: Gemini (Sign 3) -->
            <text x="225" y="38" text-anchor="middle" font-family="'Poppins', sans-serif" font-size="13.8" font-weight="700" fill="#C45738">3</text>
        </svg>
        <?php
        return ob_get_clean();
    }

    public static function render_loshu_grid_html($counts = [1=>2, 2=>2, 3=>0, 4=>0, 5=>0, 6=>1, 7=>0, 8=>1, 9=>1]) {
        $grid_cells = [
            ['num'=>4, 'planet'=>'Rahu', 'elem'=>'Wood', 'name'=>'Wealth & Intellect'],
            ['num'=>9, 'planet'=>'Mangal', 'elem'=>'Fire', 'name'=>'Fame & Energy'],
            ['num'=>2, 'planet'=>'Chandra', 'elem'=>'Earth', 'name'=>'Marriage & Intuition'],
            ['num'=>3, 'planet'=>'Guru', 'elem'=>'Wood', 'name'=>'Wisdom & Family'],
            ['num'=>5, 'planet'=>'Budh', 'elem'=>'Earth', 'name'=>'Stability & Balance'],
            ['num'=>7, 'planet'=>'Ketu', 'elem'=>'Metal', 'name'=>'Children & Spirit'],
            ['num'=>8, 'planet'=>'Shani', 'elem'=>'Earth', 'name'=>'Discipline & Knowledge'],
            ['num'=>1, 'planet'=>'Surya', 'elem'=>'Water', 'name'=>'Career & Will'],
            ['num'=>6, 'planet'=>'Shukra', 'elem'=>'Metal', 'name'=>'Luxury & Friends']
        ];

        ob_start();
        ?>
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 480px; margin: 0 auto;">
            <?php foreach ($grid_cells as $c): 
                $n = $c['num'];
                $count = isset($counts[$n]) ? $counts[$n] : 0;
                $is_present = ($count > 0);
            ?>
                <div style="background: <?php echo $is_present ? '#FFFFFF' : '#F9FAFB'; ?>; border: <?php echo $is_present ? '2px solid #C2964C' : '1.5px dashed #D1D5DB'; ?>; border-radius: 9px; padding: 12px 10px; text-align: center; box-shadow: <?php echo $is_present ? '0 3px 8px rgba(194,150,76,0.15)' : 'none'; ?>; position: relative;">
                    <?php if ($is_present): ?>
                        <div style="position: absolute; top: 5px; right: 6px; background: #047857; color: #fff; font-size: 12.5px; font-weight: 800; padding: 1px 6px; border-radius: 4px;">
                            &times;<?php echo $count; ?>
                        </div>
                    <?php else: ?>
                        <div style="position: absolute; top: 5px; right: 6px; color: #9CA3AF; font-size: 11.9px; font-weight: 700;">
                            Missing
                        </div>
                    <?php endif; ?>
                    <div style="font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 800; color: <?php echo $is_present ? '#111827' : '#9CA3AF'; ?>; line-height: 1;">
                        <?php echo $is_present ? str_repeat((string)$n, $count) : (string)$n; ?>
                    </div>
                    <div style="font-size: 13.8px; font-weight: 800; color: <?php echo $is_present ? '#C45738' : '#6B7280'; ?>; margin-top: 5px;">
                        <?php echo $c['planet']; ?>
                    </div>
                    <div style="font-size: 12.5px; color: #6B7280; font-weight: 600;">
                        <?php echo $c['name']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Build the Complete 22-Page Master Numerology & Vedic Integration Report HTML
     */
    public static function build_numero_report_html(
        $person_name = 'Rahul Sharma',
        $gender      = 'male',
        $dob         = '26/12/1980',
        $tob         = '20:30',
        $pob         = 'Meerut, Uttar Pradesh, India', $vedic_data = []
    ) {
        $logo_uri = self::get_logo_data_uri();
        $head_font = "'Poppins', sans-serif";
        $num_prof = self::calculate_numerology($dob, $person_name);
        $clean_dob_str = str_replace('/', '-', trim($dob));
        $dob_timestamp = strtotime($clean_dob_str);
        $display_dob = $dob_timestamp ? date('d/m/Y', $dob_timestamp) : $dob;
        $astro_prof = MR_Report_Builder::extract_astro_profile($dob, $tob, $pob, $vedic_data ?? []);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Numerology &amp; Vedic Integration Report &bull; <?php echo esc_html($person_name); ?></title>
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
            border-bottom: 1.5px solid #ECE6DD;
            padding-bottom: 6px;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        .mr-header-brand-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .mr-header-logo-img {
            height: 48px;
            max-width: 190px;
            object-fit: contain;
            display: block;
        }
        .mr-header-meta {
            font-size: 11.9px;
            letter-spacing: 0.08em;
            color: #6B7280;
            text-transform: uppercase;
            font-weight: 700;
        }

        .mr-tag-pill {
            display: inline-block;
            font-size: 10.6px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #C45738;
            background: rgba(196, 87, 56, 0.08);
            border: 1px solid rgba(196, 87, 56, 0.2);
            padding: 2.5px 8px;
            border-radius: 4px;
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
            font-size: 15px;
            color: #4B5563;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .mr-card {
            background: #FFFFFF;
            border: 1px solid #ECE6DD;
            border-radius: 7px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            position: relative;
            z-index: 2;
        }

        .mr-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .mr-table th {
            background: #FAF7F2;
            color: #111827;
            font-weight: 700;
            padding: 3px 6px;
            border-bottom: 1.5px solid #E5DECE;
            font-size: 11.8px;
        }
        .mr-table td {
            padding: 2.5px 6px;
            border-bottom: 1px solid #F3EFEA;
            color: #374151;
            font-size: 11.5px;
            vertical-align: middle;
        }
        .mr-table tr:last-child td {
            border-bottom: none;
        }

        .mr-badge {
            display: inline-block;
            font-size: 11.3px;
            font-weight: 700;
            padding: 2px 7px;
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
            border-left: 3px solid #C45738;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 4px;
        }
        .mr-bottom-callout-title {
            font-family: 'Poppins', sans-serif;
            font-size: 13.8px;
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
            font-size: 13.8px;
            line-height: 1.4;
            color: #374151;
            display: flex;
            align-items: flex-start;
        }
        .mr-callout-dot {
            color: #C45738;
            font-weight: 800;
            margin-right: 4px;
            font-size: 15px;
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
    </style>

</head>
<body>

    <!-- ========================================================
         PAGE 01: SOVEREIGN LUXURY COVER & VIBRATION SUMMARY
         ======================================================== -->
    <div class="mr-page" style="justify-content:space-between; text-align:center; padding:11mm 16mm 10mm 16mm;">
        <!-- Top Master Brand Logo & Title -->
        <div style="z-index:2; position:relative;">
            <div style="display:flex; justify-content:center; margin-bottom:6px;">
                <img src="<?php echo esc_attr($logo_uri); ?>" style="height:85px; max-width:280px; object-fit:contain; display:block;" alt="MeraRashifal Logo" />
            </div>

            <div class="mr-tag-pill" style="margin:0 auto 10px auto;">AUTHENTIC SANKHYA SHASTRA &bull; PARASHARI JYOTISH AUDIT</div>
            
            <h1 style="font-family:<?php echo $head_font; ?>; font-size:25px; font-weight:800; color:#111827; line-height:1.2; margin:0 0 6px 0; letter-spacing:-0.02em;">
                MASTER NUMEROLOGY &amp; VEDIC INTEGRATION REPORT
            </h1>
            <div style="font-size: 15.6px; font-weight:600; color:#C2964C; font-style:italic; margin-bottom:6px; letter-spacing:0.02em;">
                “Lo Shu Energy Grids, Core Life Path Archetypes &amp; Planetary Dasha Confluence”
            </div>

            <!-- Profile & Coordinates Card -->
            <div class="mr-card" style="max-width:620px; margin:0 auto 16px auto; padding:16px 20px; text-align:left; background:#FFFFFF; border:1.5px solid #C2964C;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ECE6DD; padding-bottom:8px; margin-bottom:12px;">
                    <div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:20px; font-weight:800; color:#111827;"><?php echo esc_html($person_name); ?></div>
                    </div>
                    <span class="mr-badge mr-badge-gold" style="font-size: 14.4px; padding:5px 12px;">North Indian Kundali + Sankhya Shastra</span>
                </div>

                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; text-align:center; margin-bottom:12px;">
                    <div style="background:#FAF7F2; padding:7px 5px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Date of Birth</div>
                        <div style="font-size: 15.6px; font-weight:800; color:#111827; margin-top:2px;"><?php echo esc_html($display_dob); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 5px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Birth Details</div>
                        <div style="font-size: 14.4px; font-weight:700; color:#111827; margin-top:2px;"><?php echo esc_html($tob); ?> &bull; <?php echo esc_html(explode(',', $pob)[0]); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 5px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Vedic Lagna</div>
                        <div style="font-size: 15.6px; font-weight:800; color:#047857; margin-top:2px;"><?php echo esc_html($astro_prof['lagna_sign'] . ' (' . $astro_prof['lagna_lord'] . ')'); ?></div>
                    </div>
                    <div style="background:#FAF7F2; padding:7px 5px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; text-transform:uppercase; font-weight:700;">Moon Rashi</div>
                        <div style="font-size: 15.6px; font-weight:800; color:#C45738; margin-top:2px;"><?php echo esc_html($astro_prof['moon_sign'] . ' (' . $astro_prof['moon_lord'] . ')'); ?></div>
                    </div>
                </div>

                <!-- 4 Core Numerical Vibrations -->
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; text-align:center;">
                    <div style="background:#F0FDF4; padding:8px 5px; border-radius:6px; border:1px solid #BBF7D0;">
                        <div style="font-size: 11.9px; color:#047857; font-weight:800; text-transform:uppercase;">Psychic / Mulank</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:24px; font-weight:800; color:#047857; line-height:1.1; margin-top:2px;"><?php echo intval($num_prof['mulank']); ?></div>
                        <div style="font-size: 12.5px; color:#374151; font-weight:700;"><?php echo esc_html($num_prof['mulank_planet']); ?></div>
                    </div>
                    <div style="background:#EFF6FF; padding:8px 5px; border-radius:6px; border:1px solid #BFDBFE;">
                        <div style="font-size: 11.9px; color:#1E40AF; font-weight:800; text-transform:uppercase;">Destiny / Bhagyank</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:24px; font-weight:800; color:#1E40AF; line-height:1.1; margin-top:2px;"><?php echo intval($num_prof['bhagyank']); ?></div>
                        <div style="font-size: 12.5px; color:#374151; font-weight:700;"><?php echo esc_html($num_prof['bhagyank_planet']); ?></div>
                    </div>
                    <div style="background:#FFFBEB; padding:8px 5px; border-radius:6px; border:1px solid #FDE68A;">
                        <div style="font-size: 11.9px; color:#92400E; font-weight:800; text-transform:uppercase;">Name / Namank</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:24px; font-weight:800; color:#92400E; line-height:1.1; margin-top:2px;"><?php echo intval($num_prof['namank']); ?></div>
                        <div style="font-size: 12.5px; color:#374151; font-weight:700;"><?php echo esc_html($num_prof['namank_planet']); ?></div>
                    </div>
                    <div style="background:#FEF2F2; padding:8px 5px; border-radius:6px; border:1px solid #FECACA;">
                        <div style="font-size: 11.9px; color:#991B1B; font-weight:800; text-transform:uppercase;">Personal Year 2026</div>
                        <div style="font-family:<?php echo $head_font; ?>; font-size:24px; font-weight:800; color:#991B1B; line-height:1.1; margin-top:2px;"><?php echo intval($num_prof['personal_year']); ?></div>
                        <div style="font-size: 12.5px; color:#374151; font-weight:700;"><?php echo esc_html($num_prof['py_planet']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Executive Theme Box -->
            <div style="max-width:620px; margin:0 auto; padding:12px 18px; background:#FAF7F2; border-left:3.5px solid #C45738; border-radius:0 6px 6px 0; text-align:left;">
                <div style="font-size: 13.8px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
                    EXECUTIVE NUMERICAL &amp; VEDIC SYNTHESIS
                </div>
                <div style="font-size: 15.3px; color:#1F2937; line-height:1.55;">
                    The combination of <strong>Mulank <?php echo intval($num_prof['mulank']); ?> (<?php echo esc_html(explode(' ', $num_prof['mulank_planet'])[0]); ?>)</strong> and <strong>Bhagyank <?php echo intval($num_prof['bhagyank']); ?> (<?php echo esc_html(explode(' ', $num_prof['bhagyank_planet'])[0]); ?>)</strong> creates an agile synthesis of executive initiative, intellectual versatility, and strategic commercial acumen. Supported by an exalted Mars in the 7th house and a harmonious <strong>Namank <?php echo intval($num_prof['namank']); ?> (<?php echo esc_html(explode(' ', $num_prof['namank_planet'])[0]); ?>)</strong>, the native possesses exceptional aptitude for institutional building, strategic negotiation, and sovereign leadership.
                </div>
            </div>
        </div>

        <!-- Pinned Bottom Section: Statutory Disclaimer & Footer -->
        <div class="mr-page-bottom-pin">
            <!-- Statutory Disclaimer & Non-Liability Notice Pinned at Bottom -->
            <div style="max-width:620px; margin:0 auto 6px auto; padding:4px 8px; background:#FFFFFF; border:1px solid #ECE6DD; border-radius:4px; font-size: 7.2px; color:#64748B; line-height:1.35; text-align:justify;">
                <strong style="color:#111827;">Statutory Disclaimer &amp; Non-Liability Notice:</strong> This publication is generated using automated computational algorithms and Artificial Intelligence (AI) based on user-provided birth coordinates. Astrological readings are inherently speculative and subjective; this report does not constitute certified legal, financial, medical, or psychological advice. Neither MeraRashifal nor its associates accept any liability or responsibility for errors, omissions, calculation discrepancies, or predictions herein, nor for any direct, indirect, or consequential outcomes, losses, or life situations arising from reliance on this content. Users are strictly advised to apply their independent intellect and consult licensed professionals for critical life decisions. By reviewing this document, you acknowledge that all actions remain your sole responsibility.
            </div>

            <div class="mr-page-footer" style="margin-top:0;">
                <span class="mr-footer-left">WWW.MERARASHIFAL.COM - MASTER NUMEROLOGY &amp; VEDIC INTEGRATION REPORT &bull;</span>
                <span class="mr-footer-right">PAGE 1 OF 23</span>
            </div>
        </div>
    </div>

    <!-- ========================================================
         PAGE 02: TABLE OF CONTENTS & INTEGRATED ROADMAP
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Table of Contents &amp; Roadmap</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">01 &bull; GUIDE &amp; OVERVIEW</div>
            <h1 class="mr-page-h1">Table of Contents &amp; Numerology Guide</h1>
            <div class="mr-page-sub">Your complete guide to lucky numbers, birth date energy, Lo Shu Grid, and Vedic timing</div>

            <!-- Editorial Orientation Card -->
            <div class="mr-card" style="padding:7px 14px; margin-bottom:6px; background:#FFFDF9; border:1px solid #F0E6D8; border-left:3.5px solid #C2964C;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 16.3px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:5px;">
                    &diams; MULTIDISCIPLINARY VEDIC NUMEROLOGY: HARMONIZING NUMBERS &amp; PLANETS
                </div>
                <p style="font-size: 15.3px; color:#374151; line-height:1.55; margin:0;">
                    Classical Indian Sankhya Shastra establishes that every number from 1 to 9 is the mathematical projection of a planetary archetype. By anchoring Western and Chinese Lo Shu matrixes to the rigorous calculations of Parashari Jyotish, this dossier provides an unprecedented audit of your karmic blueprint—linking your core vibrational digits to planetary house rulers, Vimshottari dasha lifelines, doshic immunities, and certified remedial prescriptions.
                </p>
            </div>

            <!-- 2-Column Table of Contents -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px 26px; margin-bottom:10px;">
                <!-- Column 1 -->
                <div class="mr-card" style="padding:16px 20px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1.5px solid #F3EFEA; padding-bottom:6px; margin-bottom:10px;">
                        PART I: CORE ARCHETYPES &amp; SANKHYA FOUNDATIONS
                    </div>
                    <?php
                    $toc_left = [
                        ['01', 'Sovereign Cover & Executive Vibration Dossier', '01'],
                        ['02', 'Table of Contents & Numerological Dossier Index', '02'],
                        ['03', 'Primary Janma Kundali (D1) & Planetary Rulers', '03'],
                        ['04', 'Numbers 1 to 9 &amp; Their Ruling Planets', '04'],
                        ['05', 'Psychic Number (Mulank 1): Sovereign Solar Pioneer', '05'],
                        ['06', 'Destiny Number (Bhagyank 5): Mercury Commercial Roadmap', '06'],
                        ['07', 'Name Numerology (Namank 6) & Business Spelling Box', '07'],
                        ['08', 'The Lo Shu Grid Visual Matrix & Energy Density', '08'],
                        ['09', 'Planes of Expression & Arrows of Strength Analysis', '09'],
                        ['10', 'Missing Numbers Analysis & Energetic Vacuum Remedies', '10'],
                        ['11', 'Kala Chakra Bridge: Vimshottari Dasha Lifeline', '11']
                    ];
                    foreach ($toc_left as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:2px 0; border-bottom:1px solid #FAF7F2; font-size: 11.8px;">
                            <span style="color:#111827;"><strong style="color:#6B7280; font-size: 13.8px; margin-right:4px;"><?php echo $item[0]; ?>.</strong> <?php echo $item[1]; ?></span>
                            <span style="font-weight:700; color:#C45738; font-size: 14.4px;"><?php echo $item[2]; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Column 2 -->
                <div class="mr-card" style="padding:16px 20px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1.5px solid #F3EFEA; padding-bottom:6px; margin-bottom:10px;">
                        PART II: HARMONICS, DOSHAS &amp; REMEDIAL SCIENCE
                    </div>
                    <?php
                    $toc_right = [
                        ['12', 'Personal Year vs. Dasha Period Sync Calendar', '12'],
                        ['13', '4-Varga Divisional Harmonic Kundali Grid (Charts)', '13'],
                        ['14', '4-Varga Harmonic Interpretations &amp; Readings', '14'],
                        ['15', 'Comprehensive Kundali Dosha Scan: Manglik Audit', '15'],
                        ['16', 'Karmic Loops: Kaalsarp &amp; Pitra Dosha Evaluation', '16'],
                        ['17', 'Numerological Career &amp; Wealth Acceleration Matrix', '17'],
                        ['18', 'Relationship &amp; Matrimonial Concordance Dynamics', '18'],
                        ['19', 'Integrated Remedial Science: Gemstone Prescription', '19'],
                        ['20', 'Rudraksha Therapy &amp; Sacred Bead Consecration', '20'],
                        ['21', 'Specialized Upayas: Mobile, Daan &amp; Mantra Schedules', '21'],
                        ['22', '90-Day Tactical Manifestation &amp; Action Roadmap', '22'],
                        ['23', 'Certificate of Alignment &amp; 16-Point Glossary', '23']
                    ];
                    foreach ($toc_right as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:2px 0; border-bottom:1px solid #FAF7F2; font-size: 11.8px;">
                            <span style="color:#111827;"><strong style="color:#6B7280; font-size: 13.8px; margin-right:4px;"><?php echo $item[0]; ?>.</strong> <?php echo $item[1]; ?></span>
                            <span style="font-weight:700; color:#047857; font-size: 14.4px;"><?php echo $item[2]; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(2, 'Table of Contents & Roadmap', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 03: PRIMARY JANMA KUNDALI (D1) & PLANETARY RULERS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Primary Kundali Visual Anchor</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">02 &bull; BIRTH CHART &amp; NUMBERS</div>
            <h1 class="mr-page-h1">Your Birth Chart (D1) &amp; Planetary Numbers</h1>
            <div class="mr-page-sub">How your birth chart and lucky numbers connect to guide your life</div>

            <!-- Half-Page Visual Anchor: Diamond Chart -->
            <div class="mr-card" style="padding:10px 16px; margin-bottom:10px; text-align:center;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:6px;">
                    Janma Kundali (D1 Rashi Chart) &bull; North Indian Diamond Geometry
                </div>
                <?php echo self::get_diamond_chart_svg('D1', 215); ?>
                <div style="font-size: 14.4px; color:#4B5563; margin-top:6px;">
                    <strong>Lagna:</strong> Cancer (Sign 4, Moon-ruled) &bull; <strong>Moon:</strong> Leo (Sign 5, Magha Nakshatra) &bull; <strong>Exalted Mars:</strong> 7th House Capricorn (Ruchaka Yoga)
                </div>
            </div>

            <!-- Mapping Planetary Positions to Sacred Numbers Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Planetary House Coordinates &amp; Numerical Resonances
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Graha (Planet)</th>
                            <th>Rashi &amp; House</th>
                            <th>Sacred Number</th>
                            <th>Dignity &amp; State</th>
                            <th>Numerical Vibration in Life</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Surya (Sun)</strong></td>
                            <td>Sagittarius (6th)</td>
                            <td><strong>1</strong></td>
                            <td><span class="mr-badge mr-badge-blue">Neutral (Budhaditya)</span></td>
                            <td>Authority, executive willpower, leadership overcoming obstacles.</td>
                        </tr>
                        <tr>
                            <td><strong>Chandra (Moon)</strong></td>
                            <td>Leo (2nd)</td>
                            <td><strong>2</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">Friendly / Lagna Lord</span></td>
                            <td>Intuitive financial instinct, verbal warmth, emotional depth.</td>
                        </tr>
                        <tr>
                            <td><strong>Guru (Jupiter)</strong></td>
                            <td>Leo (2nd)</td>
                            <td><strong>3</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">Friendly (Dhana Yoga)</span></td>
                            <td>Expansive wisdom, institutional advisory, wealth preservation.</td>
                        </tr>
                        <tr>
                            <td><strong>Rahu (North Node)</strong></td>
                            <td>Cancer (1st)</td>
                            <td><strong>4</strong></td>
                            <td><span class="mr-badge mr-badge-amber">Ascendant Axis</span></td>
                            <td>Unconventional ambition, psychological acuity, global reach.</td>
                        </tr>
                        <tr>
                            <td><strong>Budh (Mercury)</strong></td>
                            <td>Sagittarius (6th)</td>
                            <td><strong>5</strong></td>
                            <td><span class="mr-badge mr-badge-blue">Neutral Dignity</span></td>
                            <td>Analytical discernment, commercial strategy, contract accuracy.</td>
                        </tr>
                        <tr>
                            <td><strong>Shukra (Venus)</strong></td>
                            <td>Scorpio (5th)</td>
                            <td><strong>6</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">Own Dignity</span></td>
                            <td>Creative ingenuity, luxury affinity, intellectual elegance.</td>
                        </tr>
                        <tr>
                            <td><strong>Shani (Saturn)</strong></td>
                            <td>Virgo (3rd)</td>
                            <td><strong>8</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">Upachaya Strength</span></td>
                            <td>Unwavering discipline, organizational mastery, persistence.</td>
                        </tr>
                        <tr>
                            <td><strong>Mangal (Mars)</strong></td>
                            <td>Capricorn (7th)</td>
                            <td><strong>9</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">Exalted (Uchha)</span></td>
                            <td>Peerless executive drive, contractual dominance, courage.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(3, 'Primary Janma Kundali & Planetary Rulers', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 04: THE NUMBER-TO-PLANET RESONANCE MATRIX (1-9)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Cosmic Resonance Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">03 &bull; NUMBERS &amp; PLANETS GUIDE</div>
            <h1 class="mr-page-h1">Numbers 1 to 9 &amp; Their Ruling Planets</h1>
            <div class="mr-page-sub">Complete guide to which planets rule which numbers, their colors, and friendly combinations &amp; Cosmic Archetypes</div>

            <!-- Master Reference Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:10px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Sankhya-Graha Sambandha: Complete 9-Digit Cosmic Concordance
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th style="width:5%;">No.</th>
                            <th style="width:16%;">Graha &amp; Deity</th>
                            <th style="width:12%;">Element &amp; Day</th>
                            <th style="width:25%;">Core Psychological Archetype</th>
                            <th style="width:15%;">Harmonious Numbers</th>
                            <th style="width:12%;">Caution Digits</th>
                            <th style="width:15%;">Gemstone Vibration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#C45738;">1</strong></td>
                            <td><strong>Surya (Sun)</strong></td>
                            <td>Fire &bull; Sunday</td>
                            <td>The Sovereign: Pioneer, leader, visionary, vitalizing command.</td>
                            <td>1, 2, 3, 5, 9</td>
                            <td>6, 8</td>
                            <td>Ruby (Manikya)</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#1E40AF;">2</strong></td>
                            <td><strong>Chandra (Moon)</strong></td>
                            <td>Water &bull; Monday</td>
                            <td>The Diplomat: Empath, intuitive guide, peacemaker, nurturing.</td>
                            <td>1, 2, 3, 7</td>
                            <td>4, 8, 9</td>
                            <td>Natural Pearl (Moti)</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#B45309;">3</strong></td>
                            <td><strong>Guru (Jupiter)</strong></td>
                            <td>Ether &bull; Thursday</td>
                            <td>The Preceptor: Counselor, philosopher, ethical scholar.</td>
                            <td>1, 2, 3, 9</td>
                            <td>6</td>
                            <td>Yellow Sapphire</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#6B21A8;">4</strong></td>
                            <td><strong>Rahu (North Node)</strong></td>
                            <td>Air / Earth &bull; Saturday</td>
                            <td>The Innovator: Disruptor, strategist, unconventional architect.</td>
                            <td>1, 4, 5, 6, 7</td>
                            <td>2, 8, 9</td>
                            <td>Hessonite (Gomed)</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#059669;">5</strong></td>
                            <td><strong>Budh (Mercury)</strong></td>
                            <td>Earth &bull; Wednesday</td>
                            <td>The Merchant: Communicator, versatile intellect, networker.</td>
                            <td>1, 4, 5, 6</td>
                            <td>2</td>
                            <td>Emerald (Panna)</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#047857;">6</strong></td>
                            <td><strong>Shukra (Venus)</strong></td>
                            <td>Water &bull; Friday</td>
                            <td>The Harmonizer: Artist, luxury architect, magnetic counselor.</td>
                            <td>4, 5, 6, 8</td>
                            <td>1, 3</td>
                            <td>Diamond / Opal</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#4B5563;">7</strong></td>
                            <td><strong>Ketu (South Node)</strong></td>
                            <td>Water / Fire &bull; Tuesday</td>
                            <td>The Mystic: Researcher, spiritual philosopher, introspective.</td>
                            <td>1, 2, 4, 7</td>
                            <td>9</td>
                            <td>Cat's Eye (Lehsuniya)</td>
                        </tr>
                        <tr style="background:#FFFDF7; border-left:2px solid #C2964C;">
                            <td><strong style="font-size: 16.3px; color:#111827;">8</strong></td>
                            <td><strong>Shani (Saturn)</strong></td>
                            <td>Air / Earth &bull; Saturday</td>
                            <td>The Karmic Judge: Executive administrator, endurance, justice.</td>
                            <td>3, 5, 6, 7</td>
                            <td>1, 2, 4, 8, 9</td>
                            <td>Blue Sapphire</td>
                        </tr>
                        <tr>
                            <td><strong style="font-size: 16.3px; color:#DC2626;">9</strong></td>
                            <td><strong>Mangal (Mars)</strong></td>
                            <td>Fire &bull; Tuesday</td>
                            <td>The Commander: Protector, pioneer warrior, courageous catalyst.</td>
                            <td>1, 2, 3, 5</td>
                            <td>2, 4, 8</td>
                            <td>Red Coral (Moonga)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Resonance Breakout Card -->
            <div class="mr-card" style="padding:6px 12px; margin-bottom:3px; background:#F8FAFC; border:1px solid #E2E8F0;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#1E3A8A; text-transform:uppercase; margin-bottom:4px;">
                    &bull; Cosmic Integration Rule: How Astrological Dignity Modulates Number Expression
                </div>
                <p style="font-size: 11.2px; color:#334155; line-height:1.35; margin:0;">
                    A common mistake in western pop-numerology is treating numbers in a vacuum. In authentic Vedic Sankhya Shastra, if your Mulank is <strong>8 (Saturn)</strong>, the manifestation of your 8 depends directly on the position and dignity of Saturn in your Janma Kundali. In your horoscope, Saturn is positioned favorably in an <em>Upachaya house</em> (3rd house Virgo), meaning the delays often associated with Number 8 are transformed into methodical, indestructible long-term accomplishments.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(4, 'Number-to-Planet Resonance Matrix', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 05: PSYCHIC NUMBER (MULANK 8) DEEP DIVE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Psychic Archetype Deep Dive</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">04 &bull; YOUR BIRTH NUMBER (MULANK)</div>
            <h1 class="mr-page-h1">Birth Number (Mulank 1): Leadership &amp; Confidence</h1>
            <div class="mr-page-sub">Calculated from your birth day &bull; Ruled by the Sun (Surya) &bull; Natural leadership and independence</div>

            <!-- 4 Core Pillars of Mulank 1 -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-bottom:10px;">
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">1. Unshakable Persistence &amp; Grit</span>
                        <span class="mr-badge mr-badge-gold">Core Strength</span>
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        As an 8 native born on the 26th, your psychological foundation is governed by Saturn. You do not look for superficial shortcuts; your mind is naturally wired for heavy-duty execution, systematic endurance, and compound achievements that withstand the test of time.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">2. The 26th Compound Nuance</span>
                        <span class="mr-badge mr-badge-blue">2 + 6 Blend</span>
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        Unlike an 8 born on the 8th or 17th, the 26th brings Moon (2) and Venus (6) into the mix. This softens Saturn's harsh austerity with genuine aesthetic perception, strong financial diplomacy, and extraordinary compassion for colleagues and family.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">3. Institutional Building &amp; Wealth</span>
                        <span class="mr-badge mr-badge-emerald">Material Focus</span>
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        Number 8 is the symbol of infinity and balanced karma. You possess an instinct for asset preservation, real estate, administrative governance, and industrial strategy. Financial growth accelerates significantly in your mature years (post age 35).
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">4. Emotional Shielding &amp; Solitude</span>
                        <span class="mr-badge mr-badge-amber">Growth Area</span>
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        Mulank 1 individuals often feel misunderstood in early life. You tend to internalize professional pressure and emotional burdens rather than venting, which can lead to periods of melancholic isolation. Intentional social connection is your medicine.
                    </p>
                </div>
            </div>

            <!-- Mulank Operational Scorecard -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Mulank 1 Operational Performance Scorecard
                </div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; font-size: 14.4px;">
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:700; color:#6B7280; font-size: 13.1px; text-transform:uppercase;">Fortunate Dates</div>
                        <div style="font-weight:800; color:#111827; margin-top:2px;">1st, 10th, 19th, 28th &bull; Allies: 5th, 6th</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:700; color:#6B7280; font-size: 13.1px; text-transform:uppercase;">Fortunate Weekdays</div>
                        <div style="font-weight:800; color:#047857; margin-top:2px;">Sunday (Surya) &amp; Wednesday (Budh)</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-weight:700; color:#6B7280; font-size: 13.1px; text-transform:uppercase;">Auspicious Colors</div>
                        <div style="font-weight:800; color:#C45738; margin-top:2px;">Radiant Gold, Amber, Saffron, Ruby</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Psychic Core: Governed by Lord Shani, conferring an iron will, deep patience, and strategic focus.",
                "Compound Vibrancy: Born on the 26th, Moon (2) and Venus (6) soften Saturn's dryness with human warmth.",
                "Professional Trajectory: Naturally aligned with infrastructure, finance, governance, and long-term asset development.",
                "Karmic Caution: Avoid extreme introversion; verbalize emotional boundaries to prevent chronic internal stress."
            ], "Psychic Number 8 Behavioral Synthesis"); ?>
            <?php echo self::render_page_footer(5, 'Psychic Number (Mulank 1, 23) Deep Dive'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 06: DESTINY NUMBER (BHAGYANK 2) DEEP DIVE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Destiny / Life Path Roadmap</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">05 &bull; YOUR DESTINY NUMBER (BHAGYANK)</div>
            <h1 class="mr-page-h1">Destiny Number (Bhagyank 5): Quick Thinking &amp; Adaptability</h1>
            <div class="mr-page-sub">Sum of your full birth date &bull; Ruled by Mercury (Budha) &bull; Communication, business, and travel</div>

            <!-- Master Number 11/2 Nuance Box -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#EFF6FF; border:1px solid #BFDBFE; border-left:3px solid #1E40AF;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#1E40AF; text-transform:uppercase; margin-bottom:4px;">
                    &diams; The Master Number 11/2 Elevation: Intuitive Visionary Leadership
                </div>
                <p style="font-size: 14.8px; color:#1E3A8A; line-height:1.5; margin:0;">
                    Your full date of birth reduces to 29, which yields the master frequency <strong>11</strong> before settling into <strong>2</strong>. This designates you as an "illumined mediator"—someone whose life purpose is not mere brute commercial conquest, but uplifting communities, resolving deep conflicts, and translating intuitive insights into tangible physical systems.
                </p>
            </div>

            <!-- Mulank 1 vs Bhagyank 5 Dynamic Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-bottom:10px;">
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                        The Internal Tension: Shani vs. Chandra
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        Saturn (8) demands cold emotional detachment and discipline, while Moon (2) operates on fluid empathy and sensitivity. In Vedic astrology, this pair can form <em>Vish Yoga</em> tendencies (mood swings or self-criticism) if left unmonitored.
                    </p>
                </div>

                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#047857; text-transform:uppercase; margin-bottom:4px;">
                        The Alchemical Gift: Practical Empathy
                    </div>
                    <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                        When harmonized, 8 and 2 produce the ultimate executive diplomat: someone who possesses the iron organizational backbone to build vast enterprises, yet treats every partner, employee, and client with profound respect and emotional fairness.
                    </p>
                </div>
            </div>

            <!-- Life Stage Evolutionary Milestones -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                    Bhagyank 5 Evolutionary Life Stages
                </div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; font-size: 13.8px;">
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">Phase 1 (Ages 0–28):</strong>
                        <div style="color:#4B5563; margin-top:2px;">Inner sensitivity, navigating external demands, searching for vocational grounding and emotional balance.</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#047857;">Phase 2 (Ages 29–48):</strong>
                        <div style="color:#4B5563; margin-top:2px;">Consolidation of authority. The 11/2 master frequency activates, enabling high-stakes advisory and wealth creation.</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#1E40AF;">Phase 3 (Ages 49+):</strong>
                        <div style="color:#4B5563; margin-top:2px;">Legacy building, spiritual and institutional patronage, mentor to the next generation, domestic bliss.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Destiny Calling: Ruled by Chandra, your life path unfolds through collaboration, strategic diplomacy, and empathy.",
                "Master 11 Bridge: The underlying 11 vibration gives you exceptional intuitive foresight in business negotiations.",
                "Harmonizing 8 & 2: Blend Saturn's executive discipline with Moon's psychological awareness for unshakeable leadership.",
                "Mature Era Peak: The full power of your Bhagyank materializes as you establish durable institutional roots."
            ], "Destiny Number 2 Karmic Takeaways"); ?>
            <?php echo self::render_page_footer(6, 'Destiny Number (Bhagyank 5, 23) Deep Dive'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 07: NAME NUMEROLOGY (NAMANK 6) & UPGRADE BOX
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Name &amp; Business Numerology</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">06 &bull; YOUR NAME NUMBER (NAMANK)</div>
            <h1 class="mr-page-h1">Name Numerology (Namank 6) &amp; Spelling Balance</h1>
            <div class="mr-page-sub">Understanding your name vibration &bull; Aligning your name with your birth numbers for harmony</div>

            <!-- Current Name Vibration Breakdown -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase;">
                        Name Mathematical Decomposition (Chaldean System)
                    </div>
                    <span class="mr-badge mr-badge-emerald">Namank 6 &bull; Venus (Shukra)</span>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1.15fr; gap:12px; text-align:center; margin-bottom:10px;">
                    <div style="background:#FAF7F2; padding:10px 8px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; font-weight:800; letter-spacing:0.04em;">FIRST NAME: RAHUL</div>
                        <div style="font-size: 13.5px; font-weight:700; color:#111827; margin-top:4px;">R(2)+A(1)+H(5)+U(6)+L(3)</div>
                        <div style="font-size: 16.5px; font-weight:800; color:#C45738; margin-top:2px;">= 17 &rarr; 8 (Shani)</div>
                    </div>
                    <div style="background:#FAF7F2; padding:10px 8px; border-radius:6px; border:1px solid #ECE6DD;">
                        <div style="font-size: 12.5px; color:#6B7280; font-weight:800; letter-spacing:0.04em;">LAST NAME: SHARMA</div>
                        <div style="font-size: 13.5px; font-weight:700; color:#111827; margin-top:4px;">S(3)+H(5)+A(1)+R(2)+M(4)+A(1)</div>
                        <div style="font-size: 16.5px; font-weight:800; color:#C45738; margin-top:2px;">= 16 &rarr; 7 (Ketu)</div>
                    </div>
                    <div style="background:#F0FDF4; padding:10px 8px; border-radius:6px; border:1.5px solid #BBF7D0;">
                        <div style="font-size: 12.5px; color:#047857; font-weight:800; letter-spacing:0.04em;">FULL COMPOUND: 33</div>
                        <div style="font-size: 13.5px; font-weight:700; color:#111827; margin-top:4px;">17 + 16 = 33 &rarr; 3+3</div>
                        <div style="font-size: 16.5px; font-weight:800; color:#047857; margin-top:2px;">= 6 (Shukra)</div>
                    </div>
                </div>
                <p style="font-size: 14.2px; color:#4B5563; line-height:1.5; margin:0;">
                    <strong>Vibrational Analysis:</strong> In ancient Chaldean lore, the compound number <strong>33</strong> is heralded as the <em>"Master Teacher &amp; Supreme Benefactor"</em> vibration. It reduces to <strong>6 (Shukra / Venus)</strong>, bestowing expansive charisma, aesthetic eloquence, and immense goodwill in public commerce. For Rahul Sharma, Namank 6 establishes a magnificent bridge uniting sovereign Mulank 1 (Sun) and intellectual Bhagyank 5 (Mercury), creating a balanced synergy of commanding authority, sharp market intelligence, and diplomatic charm.
                </p>
            </div>

            <!-- MANDATORY STRATEGIC BREAKOUT: Name Spelling & Business Upgrade Box -->
            <div class="mr-card" style="padding:14px 18px; margin-bottom:6px; background:#FFFDF7; border:1.5px solid #C2964C;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#92400E; text-transform:uppercase; margin-bottom:6px;">
                    &diams; THE "NAME SPELLING &amp; BUSINESS NUMEROLOGY" UPGRADE BOX
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; font-size: 14.8px; line-height:1.5;">
                    <div>
                        <strong style="color:#111827;">1. Personal Signature &amp; Email Optimization:</strong>
                        <p style="color:#374151; margin:2px 0 6px 0;">
                            Your legal name currently resolves to <strong>6</strong>, which is already an ideal natural ally to your Mulank 1. To further synchronize with your active dasha lord, always sign with an upward slant ending with a complete, unbroken flourish to lock in Venusian financial luck.
                        </p>
                        <div style="background:#FEF3C7; padding:5px 8px; border-radius:4px; font-size: 13.8px; color:#78350F;">
                            <strong>Rule:</strong> Never use a trailing dot after your signature, as it symbolically terminates financial expansion.
                        </div>
                    </div>
                    <div>
                        <strong style="color:#111827;">2. Business Entity / Brand Name Tuning:</strong>
                        <p style="color:#374151; margin:2px 0 6px 0;">
                            When naming holding companies, digital domains, or real estate assets, aim for corporate brand totals reducing to <strong>1 (Surya - Authority)</strong>, <strong>5 (Budh - Commercial Speed)</strong>, or <strong>6 (Shukra - High Margins)</strong>.
                        </p>
                        <div style="background:#F0FDF4; padding:5px 8px; border-radius:4px; font-size: 13.8px; color:#065F46;">
                            <strong>Optimal Compound:</strong> Target numbers <strong>24 (6)</strong>, <strong>33 (6)</strong>, or <strong>37 (1)</strong> for brand names to ensure rapid customer trust.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Name Vibrational Power: Master compound 33 reduces to 6 (Venus), providing exceptional personal magnetism.",
                "Harmonious Triad: Mulank 1, Bhagyank 5, and Namank 6 form a mutually supporting triangle of leadership, intellect, and grace.",
                "Signature Upgrade: Sign with an upward flourish without trailing dots to channel sustained career elevation.",
                "Corporate Branding Rule: Anchor commercial business entities in compound 24, 33, or 37 for rapid market expansion."
            ], "Name & Business Numerology Takeaways"); ?>
            <?php echo self::render_page_footer(7, 'Name Numerology & Business Spelling Upgrade', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 08: THE LO SHU GRID VISUAL MATRIX & ENERGY DENSITY
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Lo Shu Grid Master Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">07 &bull; YOUR LO SHU ENERGY GRID</div>
            <h1 class="mr-page-h1">The Lo Shu Grid: 3x3 Energy Map</h1>
            <div class="mr-page-sub">Traditional 3x3 grid calculated from your birth date &bull; Discovering your natural strengths and gifts</div>

            <!-- The Visual 3x3 Magic Square -->
            <div class="mr-card" style="padding:12px 16px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase;">
                        Native's Lo Shu Grid Distribution (DOB: <?php echo esc_html($display_dob); ?>)
                    </div>
                    <span class="mr-badge mr-badge-gold">Sum of Any Line = 15</span>
                </div>
                <?php echo self::render_loshu_grid_html($num_prof['loshu_counts']); ?>
            </div>

            <!-- Digit Frequency & Energetic Breakdown Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Digit Frequency &amp; Energetic Manifestation
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Frequency</th>
                            <th>Planetary Ruler</th>
                            <th>Element</th>
                            <th>Energetic Status in Native's Life</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Number 1</strong></td>
                            <td><span class="mr-badge mr-badge-blue">3 Times (111)</span></td>
                            <td>Surya (Sun)</td>
                            <td>Water</td>
                            <td>Powerful communicative authority, outspoken conviction, sovereign leadership drive.</td>
                        </tr>
                        <tr>
                            <td><strong>Number 2</strong></td>
                            <td><span class="mr-badge mr-badge-emerald">1 Time (2)</span></td>
                            <td>Chandra (Moon)</td>
                            <td>Earth</td>
                            <td>Intuitive diplomacy, empathetic sensitivity, balance between logic and emotion.</td>
                        </tr>
                        <tr style="background:#FAF7F2;">
                            <td colspan="5" style="text-align:center; font-size:12px; color:#6B7280; padding:6px 10px;">
                                <em>Numbers 3, 4, 5, 6, 7, 8, 9 are Missing Digits in Natal DOB &mdash; See Comprehensive Vacuum Remedies on Page 10.</em>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Sacred Geometry: 3x3 Lo Shu grid structures the cosmic distribution of energy across physical reality.",
                "Triple 1 Vibrations: 3 occurrences of Number 1 bestow articulate communicative authority and executive drive.",
                "Single 2 Anchor: One occurrence of Number 2 infuses empathetic diplomacy into leadership endeavors."
            ], "Lo Shu Matrix & Energy Takeaways"); ?>
            <?php echo self::render_page_footer(8, 'Lo Shu Grid Visual Matrix & Energy Density', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 09: PLANES OF EXPRESSION & ARROWS OF STRENGTH
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Planes &amp; Arrows of Strength</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">08 &bull; LO SHU PLANES &amp; ARROWS</div>
            <h1 class="mr-page-h1">Arrows of Strength &amp; Thought Planes</h1>
            <div class="mr-page-sub">Exploring your practical strength, will power, and action planes in the Lo Shu grid</div>

            <!-- The Crown Feature: 8-1-6 Arrow of Prosperity -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#F0FDF4; border:1.5px solid #047857;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase;">
                        &starf; RARE STRUCTURAL ASSET: COMPLETE ARROW OF PRACTICALITY (8-1-6)
                    </div>
                    <span class="mr-badge mr-badge-emerald">100% Fully Formed</span>
                </div>
                <p style="font-size: 14.8px; color:#064E3B; line-height:1.5; margin:0;">
                    Your Lo Shu grid possesses the fully assembled <strong>Bottom Horizontal Plane (8-1-6)</strong>, classically known as the <em>Arrow of Prosperity and Practicality</em>. This is one of the most auspicious configurations in numerology. It confirms that you do not merely dream or ideate—you possess the grounded physical discipline (8), executive focus (1), and resource-attracting elegance (6) to manifest massive commercial and material success in the physical world.
                </p>
            </div>

            <!-- Planes of Expression Analysis Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-bottom:8px;">
                <!-- Horizontal Planes -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                        Horizontal Planes Analysis
                    </div>
                    <div style="font-size: 14.4px; margin-bottom:6px; border-bottom:1px solid #FAF7F2; padding-bottom:4px; line-height:1.45;">
                        <strong style="color:#C45738;">Mental Plane (4-9-2):</strong> 66% Active (9, 2 present). Exceptional intuitive foresight, strategic memory, and situational intellect.
                    </div>
                    <div style="font-size: 14.4px; margin-bottom:6px; border-bottom:1px solid #FAF7F2; padding-bottom:4px; line-height:1.45;">
                        <strong style="color:#6B7280;">Emotional Plane (3-5-7):</strong> Inactive (3, 5, 7 missing). High internal self-control, but requires conscious cultivation of emotional vulnerability.
                    </div>
                    <div style="font-size: 14.4px; line-height:1.45;">
                        <strong style="color:#047857;">Practical Plane (8-1-6):</strong> 100% Intact. Master of earthly execution, contracts, capital management, and real estate.
                    </div>
                </div>

                <!-- Vertical Planes -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px;">
                        Vertical Planes Analysis
                    </div>
                    <div style="font-size: 14.4px; margin-bottom:6px; border-bottom:1px solid #FAF7F2; padding-bottom:4px; line-height:1.45;">
                        <strong style="color:#111827;">Thought Plane (4-3-8):</strong> 33% Active (8 present). Methodical thinker who prefers tested evidence over speculative hypotheses.
                    </div>
                    <div style="font-size: 14.4px; margin-bottom:6px; border-bottom:1px solid #FAF7F2; padding-bottom:4px; line-height:1.45;">
                        <strong style="color:#1E40AF;">Will Plane (9-5-1):</strong> 66% Active (9, 1 present). Formidable stubbornness and perseverance once a commitment is undertaken.
                    </div>
                    <div style="font-size: 14.4px; line-height:1.45;">
                        <strong style="color:#047857;">Action Plane (2-7-6):</strong> 66% Active (2, 6 present). Smooth social execution and high relationship-building acumen.
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "The 8-1-6 Superpower: Possessing the complete Arrow of Practicality is a hallmark of self-made corporate and financial leaders.",
                "Mental Plane Balance: Numbers 9 and 2 endow you with deep emotional intelligence and instinctive situational reading.",
                "Emotional Plane Vacuum: The empty 3-5-7 row explains why you prioritize objective duty over personal emotional expression.",
                "Execution Certainty: You bridge abstract commercial strategy directly into profitable, brick-and-mortar reality."
            ], "Planes of Expression & Arrows Takeaways"); ?>
            <?php echo self::render_page_footer(9, 'Lo Shu Planes of Expression & Arrows', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 10: MISSING NUMBERS & ENERGETIC VACUUM REMEDIES
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Missing Numbers &amp; Vacuum Remedies</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">09 &bull; MISSING NUMBERS &amp; EASY REMEDIES</div>
            <h1 class="mr-page-h1">Missing Numbers in Your Grid &amp; Simple Balance</h1>
            <div class="mr-page-sub">Understanding numbers missing in your birth date &bull; Easy daily habits, colors, and items to balance them</div>

            <!-- Missing Numbers 2x2 Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-bottom:8px;">
                <!-- Missing 3 -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#B45309; text-transform:uppercase;">Missing 3: Guru (Wisdom / Wood)</span>
                        <span class="mr-badge mr-badge-amber">Spiritual Gap</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 5px 0;">
                        <strong>Impact:</strong> Tendency to underestimate formal advisory channels or second-guess personal philosophical vision.
                    </p>
                    <div style="background:#FFFBEB; padding:6px 10px; border-radius:4px; font-size: 13.8px; color:#92400E;">
                        <strong>Remedy:</strong> Wear a 5-Mukhi Rudraksha, maintain live wooden plants in the East of your workspace, and seek guidance from mentors.
                    </div>
                </div>

                <!-- Missing 4 -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#6B21A8; text-transform:uppercase;">Missing 4: Rahu (Order / Wood)</span>
                        <span class="mr-badge mr-badge-amber">Structural Gap</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 5px 0;">
                        <strong>Impact:</strong> Impatience with repetitive clerical bureaucracy or feeling frustrated by micro-management protocols.
                    </p>
                    <div style="background:#FAF5FF; padding:6px 10px; border-radius:4px; font-size: 13.8px; color:#6B21A8;">
                        <strong>Remedy:</strong> Keep the Southeast zone clutter-free, use a solid wooden desk, and wear a Tulsi or Sandalwood bead wristband.
                    </div>
                </div>

                <!-- Missing 5 -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#059669; text-transform:uppercase;">Missing 5: Budh (Balance / Center)</span>
                        <span class="mr-badge mr-badge-amber">Centering Gap</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 5px 0;">
                        <strong>Impact:</strong> As the center-point of the Lo Shu grid, missing 5 can manifest as occasional restlessness or difficulty unplugging.
                    </p>
                    <div style="background:#F0FDF4; padding:6px 10px; border-radius:4px; font-size: 13.8px; color:#047857;">
                        <strong>Remedy:</strong> Place a yellow or earth-toned crystal (Citrine or Pyrite) at the exact center of your home or office desk.
                    </div>
                </div>

                <!-- Missing 7 -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#4B5563; text-transform:uppercase;">Missing 7: Ketu (Spirit / Metal)</span>
                        <span class="mr-badge mr-badge-amber">Introspection Gap</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 5px 0;">
                        <strong>Impact:</strong> Reluctance to engage in esoteric or unproven meditative arts, favoring empirical physical validation.
                    </p>
                    <div style="background:#F3F4F6; padding:6px 10px; border-radius:4px; font-size: 13.8px; color:#374151;">
                        <strong>Remedy:</strong> Wear pure white silver jewelry, feed stray animals on Tuesdays, and practice silent breathwork for 10 minutes daily.
                    </div>
                </div>
            </div>

            <!-- Vedic Kundali Cushioning Factor -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:6px; background:#FFFDF7; border-left:3px solid #C2964C;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#92400E; text-transform:uppercase; margin-bottom:4px;">
                    &bull; The Vedic Astrology Cushion: Why Missing Numbers Do Not Mean Missing Fortune
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    In western numerology, missing digits are feared. However, in our Vedic Integration audit, we observe that <strong>Jupiter (3)</strong> is placed in your 2nd house of wealth alongside the Moon, forming an auspicious <em>Gajakesari-like association</em>, and <strong>Mercury (5)</strong> forms a potent <em>Budhaditya Yoga</em> with the Sun in your 6th house. Thus, your Vedic horoscope fully compensates for the numeric vacancies in your Lo Shu grid!
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Deficit Neutralized: The apparent vacancies of 3, 4, 5, and 7 are powerfully cushioned by strong Vedic planetary placements.",
                "East Zone Activation: Wood and green elements in the East harmonize missing 3 and 4, boosting family clarity.",
                "Center-Point Anchoring: A Pyrite or Citrine stone at your workstation grounds the missing 5, sustaining mental equilibrium.",
                "Spiritual Connection: Simple charitable actions toward stray animals effortlessly appease Ketu's missing 7 vibration."
            ], "Missing Numbers & Vedic Cushioning Takeaways"); ?>
            <?php echo self::render_page_footer(10, 'Missing Numbers Analysis & Remedies', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 11: KALA CHAKRA BRIDGE: VIMSHOTTARI DASHA LIFELINE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Vimshottari Dasha Lifeline</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">10 &bull; PLANETARY TIMING &amp; NUMBERS</div>
            <h1 class="mr-page-h1">Your Dasha Timeline &amp; Number Sync</h1>
            <div class="mr-page-sub">How your current running planetary period works together with your lucky numbers</div>

            <!-- Active Dasha Period Summary -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#FAF7F2; border-left:3px solid #047857;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase;">
                        Active Planetary Governance: Saturn (Shani) Mahadasha &bull; Mercury (Budh) Antardasha
                    </div>
                    <span class="mr-badge mr-badge-emerald">Golden Alignment Window</span>
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    You are navigating the premier planetary period of your life. Because your <strong>Mulank is 8 (Saturn)</strong>, running a Saturn Mahadasha places you in total energetic resonance with the cosmic administrator. The current sub-period of <strong>Mercury (Budh - Number 5)</strong> bridges the missing 5 in your Lo Shu grid, providing elite analytical precision, commercial expansion, and intellectual breakthroughs.
                </p>
            </div>

            <!-- Dasha Sequence Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Comprehensive Vimshottari Lifeline &amp; Numerical Concordance
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Dasha Period</th>
                            <th>Operating Graha</th>
                            <th>Number</th>
                            <th>Duration Window</th>
                            <th>Astrological Dynamic &amp; Numerical Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#F0FDF4; font-weight:700;">
                            <td><strong>Active Mahadasha</strong></td>
                            <td><strong>Shani (Saturn)</strong></td>
                            <td><strong>8</strong></td>
                            <td>2018 &ndash; 2037</td>
                            <td>Total Mulank resonance. Massive consolidation of authority, wealth, and enterprise.</td>
                        </tr>
                        <tr style="background:#FFFDF7;">
                            <td>Current Antardasha</td>
                            <td>Budh (Mercury)</td>
                            <td>5</td>
                            <td>2024 &ndash; 2027</td>
                            <td>Bridges the missing Lo Shu 5. High commercial growth, contract executions, media.</td>
                        </tr>
                        <tr>
                            <td>Upcoming Antardasha</td>
                            <td>Ketu (South Node)</td>
                            <td>7</td>
                            <td>2027 &ndash; 2028</td>
                            <td>Bridges missing 7. Deep spiritual awakening, foreign travels, strategic reorganization.</td>
                        </tr>
                        <tr>
                            <td>Upcoming Antardasha</td>
                            <td>Shukra (Venus)</td>
                            <td>6</td>
                            <td>2028 &ndash; 2031</td>
                            <td>Activates Namank 6. Peak luxury acquisition, vehicle upgrades, harmonious family joy.</td>
                        </tr>
                        <tr>
                            <td>Next Mahadasha</td>
                            <td>Budh (Mercury)</td>
                            <td>5</td>
                            <td>2037 &ndash; 2054</td>
                            <td>17 years of commercial intellectual dominance, publishing, institutional advisory.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Mahadasha Synchronization: Operating under Shani Mahadasha empowers Mulank 1 to manifest lifetime ambitions.",
                "Sub-Lord Compensation: Running Mercury (5) and upcoming Ketu (7) dasha periods naturally activate missing grid links.",
                "Commercial Golden Age (2024–2027): Saturn-Mercury delivers peak intellectual clarity and profitable commercial negotiations.",
                "Upcoming Venus Surge (2028–2031): Triggers Namank 6 for monumental asset acquisition, luxury, and social honor."
            ], "Vimshottari Dasha & Numerical Sync Takeaways"); ?>
            <?php echo self::render_page_footer(11, 'Vimshottari Dasha Lifeline & Number Sync', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 12: PERSONAL YEAR VS PLANETARY PERIOD SYNC CALENDAR
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Personal Year Sync Calendar</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">11 &bull; YOUR PERSONAL YEAR CYCLE</div>
            <h1 class="mr-page-h1">Personal Year &amp; Planet Period Sync</h1>
            <div class="mr-page-sub">Understanding your current 9-year personal cycle and best times for new beginnings &bull; Double-Confirmation Windows</div>

            <!-- Double Confirmation Window Highlight Box -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#FFFBEB; border:1.5px solid #F59E0B;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#92400E; text-transform:uppercase;">
                        &starf; THE "DOUBLE-CONFIRMATION WINDOW" DIAGNOSTIC
                    </div>
                    <span class="mr-badge mr-badge-gold">Rare Strategic Synergy</span>
                </div>
                <p style="font-size: 14.8px; color:#78350F; line-height:1.5; margin:0;">
                    When your Numerological Personal Year number vibrates in natural harmony with your active Vedic Antardasha lord, a <strong>"Double-Confirmation Window"</strong> is unlocked. In this state, neither system contradicts the other; both astrological karma and numerical momentum point in the exact same direction, yielding effortless progress, minimal friction, and multiplied financial returns.
                </p>
            </div>

            <!-- 3-Year Strategic Cycle Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    3-Year Strategic Planetary &amp; Numerological Concordance
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Personal Year</th>
                            <th>Ruling Vibration</th>
                            <th>Active Antardasha</th>
                            <th>Concurrence Status &amp; Life Guidance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#F0FDF4;">
                            <td><strong>2026</strong></td>
                            <td><strong style="color:#047857; font-size: 16.3px;">Year 3</strong></td>
                            <td>Guru (Jupiter)</td>
                            <td>Saturn &ndash; Mercury</td>
                            <td><span class="mr-badge mr-badge-emerald">Expansion &bull; Double Confirmation</span><br/>Wisdom and commerce align. Exceptional window for product launches, asset acquisition, and institutional building.</td>
                        </tr>
                        <tr>
                            <td><strong>2027</strong></td>
                            <td><strong style="color:#6B21A8; font-size: 16.3px;">Year 4</strong></td>
                            <td>Rahu (North Node)</td>
                            <td>Saturn &ndash; Ketu</td>
                            <td><span class="mr-badge mr-badge-amber">Consolidation &bull; Caution</span><br/>Rahu-Ketu dual axis. Focus on operational streamlining, auditing legal fine print, and avoiding unvetted financial speculation.</td>
                        </tr>
                        <tr style="background:#FFFDF7;">
                            <td><strong>2028</strong></td>
                            <td><strong style="color:#059669; font-size: 16.3px;">Year 5</strong></td>
                            <td>Budh (Mercury)</td>
                            <td>Saturn &ndash; Venus</td>
                            <td><span class="mr-badge mr-badge-emerald">Breakthrough &bull; High Velocity</span><br/>Rapid public recognition, cross-border commerce, creative breakthroughs, and joyous social celebrations.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "2026 Double-Confirmation: Jupiterian Year 3 combined with Mercury dasha signals unimpeded professional growth.",
                "Tactical Execution: Use 2026 for decisive business expansion, major contract executions, and legacy property moves.",
                "2027 Discipline: Personal Year 4 mandates rigid bookkeeping and systematic structural consolidation before the next leap.",
                "2028 Golden Window: Year 5 alongside the entry of Venus Antardasha ushers in immense luxury, social prestige, and joy."
            ], "Personal Year & Dasha Sync Takeaways"); ?>
            <?php echo self::render_page_footer(12, 'Personal Year vs. Dasha Sync Calendar', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 13: 4-CHART DIVISIONAL GRID (CHARTS ONLY)
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; 4-Varga Harmonic Kundali Grid</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">12 &bull; 4 MAIN LIFE CHARTS</div>
            <h1 class="mr-page-h1">4 Life Charts Grid: D1, D9, D10 &amp; D7</h1>
            <div class="mr-page-sub">Checking your chart across 4 main areas: overall life, soul fulfillment, career, and family</div>

            <!-- Dedicated 2x2 Grid of Large 205px Diamond Charts -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px 24px; margin-top:8px; justify-items:center;">
                <!-- Chart 1: D1 Janma Kundali -->
                <div style="width:100%; text-align:center;">
                    <?php echo self::render_varga_diamond_svg(325, [
                        1  => ['Ra 28°'],
                        2  => ['Ju 19°', 'Mo 24°'],
                        3  => ['Sa 15°'],
                        5  => ['Ve 26°'],
                        6  => ['Su 28°', 'Me 14°'],
                        7  => ['Ma 22°', 'Ke 28°'],
                    ], 4, '1. D1 Janma Kundali (Physical Reality)'); ?>
                </div>

                <!-- Chart 2: D9 Navamsha Kundali -->
                <div style="width:100%; text-align:center;">
                    <?php echo self::render_varga_diamond_svg(325, [
                        1  => ['Ve 18°'],
                        4  => ['Ju 12°'],
                        7  => ['Su 08°'],
                        10 => ['Ma 14°'],
                        11 => ['Sa 11°', 'Mo 04°'],
                    ], 1, '2. D9 Navamsha (Soul Fruit &amp; Dharma)'); ?>
                </div>

                <!-- Chart 3: D10 Dashamsha Kundali -->
                <div style="width:100%; text-align:center;">
                    <?php echo self::render_varga_diamond_svg(325, [
                        1  => ['Sa 14°'],
                        4  => ['Ju 21°'],
                        7  => ['Me 09°'],
                        10 => ['Su 15°', 'Ma 11°'],
                        11 => ['Ve 12°'],
                    ], 10, '3. D10 Dashamsha (Career &amp; Status)'); ?>
                </div>

                <!-- Chart 4: D7 Saptamsha Kundali -->
                <div style="width:100%; text-align:center;">
                    <?php echo self::render_varga_diamond_svg(325, [
                        1  => ['Ju 16°'],
                        4  => ['Mo 15°'],
                        5  => ['Ve 09°'],
                        9  => ['Me 14°'],
                        10 => ['Sa 08°'],
                    ], 5, '4. D7 Saptamsha (Lineage &amp; Progeny)'); ?>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_footer(13, '4-Varga Harmonic Kundali Grid', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 15: 4-VARGA HARMONIC READINGS & INTERPRETATIONS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Divisional Harmonic Interpretations</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">13 &bull; CHART ANALYSIS &amp; NUMBERS</div>
            <h1 class="mr-page-h1">Reading the 4 Charts with Your Numbers</h1>
            <div class="mr-page-sub">How your lucky numbers 1, 5, and 6 bring positive strength into each life chart</div>

            <!-- 4 Comprehensive Reading Cards -->
            <div style="display:flex; flex-direction:column; gap:12px; margin-top:8px;">
                <!-- Card 1: D1 -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #C45738;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#C45738; text-transform:uppercase;">
                            1. D1 Janma Kundali &bull; Physical Reality &amp; Mulank 1 Anchor
                        </span>
                        <span class="mr-badge mr-badge-gold">Physical Substrate</span>
                    </div>
                    <p style="font-size:13.8px; color:#374151; line-height:1.55; margin:0;">
                        Anchored by Cancer Lagna with Surya and Budh positioned strategically, your foundational chart channels sovereign Mulank 1 (Sun) willpower into sharp, analytical execution. Exalted Mars in the 7th house bestows extraordinary competitive endurance and contractual grit, transforming early career friction into permanent executive accomplishments.
                    </p>
                </div>

                <!-- Card 2: D9 -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #047857;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#047857; text-transform:uppercase;">
                            2. D9 Navamsha &bull; Soul Fruit &amp; Bhagyank 5 Concordance
                        </span>
                        <span class="mr-badge mr-badge-emerald">Dharmic Fruit</span>
                    </div>
                    <p style="font-size:13.8px; color:#374151; line-height:1.55; margin:0;">
                        Aries Navamsha Lagna with Venus situated in dignity elevates Bhagyank 5 (Mercury) into high-level commercial acumen, relational fidelity, and creative innovation. The inner soul blueprint confirms that your commercial enterprises gain compounding velocity as your communicative clarity and ethical integrity mature.
                    </p>
                </div>

                <!-- Card 3: D10 -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #1E40AF;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#1E40AF; text-transform:uppercase;">
                            3. D10 Dashamsha &bull; Career Altitude &amp; Executive Authority
                        </span>
                        <span class="mr-badge mr-badge-blue">Career Engine</span>
                    </div>
                    <p style="font-size:13.8px; color:#374151; line-height:1.55; margin:0;">
                        Capricorn Lagna with Saturn in directional Digbala (1st house) activates an indestructible vocational engine. Confers executive decision-making power, institutional respect, and large-scale asset development capability, rewarding structured, methodical persistence over impulsive shortcuts.
                    </p>
                </div>

                <!-- Card 4: D7 -->
                <div class="mr-card" style="padding:14px 18px; border-left:4px solid #B45309;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size:15px; font-weight:800; color:#B45309; text-transform:uppercase;">
                            4. D7 Saptamsha &bull; Lineage Fruit &amp; Namank 6 Harmony
                        </span>
                        <span class="mr-badge mr-badge-amber">Generational Legacy</span>
                    </div>
                    <p style="font-size:13.8px; color:#374151; line-height:1.55; margin:0;">
                        Leo Lagna with benefic Jupiter aspecting the 5th house safeguards noble lineage, virtuous progeny, and enduring domestic warmth. Harmonizes with Namank 6 (Venus) to preserve generational assets, household peace, and elevated social dignity across lifelong milestones.
                    </p>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Multi-Harmonic Integration: The 4 divisional charts demonstrate how physical vitality (D1), soul purpose (D9), vocational altitude (D10), and legacy (D7) integrate seamlessly.",
                "Sun & Mercury Dominance: Strong harmonic placements empower Mulank 1 and Bhagyank 5 with executive foresight and analytical precision.",
                "Venusian Lineage Blessing: Favorable D7 alignments harmonize with Namank 6 to channel generational stability and domestic joy."
            ], "Harmonic Interpretations & Divisional Takeaways"); ?>
            <?php echo self::render_page_footer(15, '4-Varga Harmonic Divisional Readings', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 15: COMPREHENSIVE KUNDALI DOSHA SCAN: MANGLIK AUDIT
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Manglik Dosha &amp; Numerical Buffer</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">14 &bull; MARS ENERGY &amp; MANGLIK CHECK</div>
            <h1 class="mr-page-h1">Manglik Check &amp; How Numbers Help</h1>
            <div class="mr-page-sub">Why Mars in Capricorn is naturally strong and how lucky numbers add peace to relationships</div>

            <!-- Executive Manglik Status Banner -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#F0FDF4; border:1px solid #BBF7D0; border-left:3px solid #047857;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase;">
                        &check; MANGLIK DOSHA STATUS: COMPLETE KUJA BHANGA (CANCELED &amp; ELEVATED)
                    </div>
                    <span class="mr-badge mr-badge-emerald">Harmless &bull; Highly Productive</span>
                </div>
                <p style="font-size: 14.8px; color:#064E3B; line-height:1.5; margin:0;">
                    While classical texts flag Mars in the 7th house as a primary Manglik position, your chart triggers the paramount canonical exception: <strong>Mars is exalted (Uchha) in Capricorn</strong>. Under Brihat Parashara Hora Shastra, an exalted Mars forms the sovereign <em>Ruchaka Mahapurusha Yoga</em>, completely extinguishing all marital afflictions and converting aggressive friction into indestructible protective courage.
                </p>
            </div>

            <!-- Cancellation Checklist Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Canonical Kuja Bhanga (Cancellation) Scorecard
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Astronomical Condition</th>
                            <th>Canonical Rule</th>
                            <th>Verification in Chart</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Planetary Exaltation</strong></td>
                            <td>Mars in Capricorn dissolves Kuja Dosha</td>
                            <td>Mars at 22° Capricorn in 7th House</td>
                            <td><span class="mr-badge mr-badge-emerald">100% Canceled</span></td>
                        </tr>
                        <tr>
                            <td><strong>Benefic Aspect Cushion</strong></td>
                            <td>Jupiterian or Lunar dignity moderates heat</td>
                            <td>Moon &amp; Jupiter in 2nd house from Lagna</td>
                            <td><span class="mr-badge mr-badge-emerald">Protected</span></td>
                        </tr>
                        <tr>
                            <td><strong>Dispositor Placement</strong></td>
                            <td>Saturn placed in Upachaya (3rd House)</td>
                            <td>Saturn in 3rd Virgo stabilizes marital bond</td>
                            <td><span class="mr-badge mr-badge-emerald">Stabilized</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- The Numerical Shock-Absorber Box -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:4px; background:#FFFDF7; border:1px solid #ECE6DD;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#92400E; text-transform:uppercase; margin-bottom:4px;">
                    &bull; The Sankhya Psychological Buffer: How Mulank 1 Balances Martian Heat
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    Mars is raw energetic fire (Number 9), whereas your Mulank is <strong>8 (Saturn - Earth/Air)</strong>. Saturn's patient temperament acts as an organic psychological regulator, preventing Martian volatility from causing impulsive domestic conflicts. Instead, the native channels this martial drive into fierce professional dedication and unflinching fidelity toward their spouse.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Full Kuja Bhanga: Exaltation in Capricorn eliminates negative Manglik influences, bestowing Ruchaka Yoga.",
                "Contractual Fortitude: Mars in the 7th house endows the native with extraordinary tenacity in joint partnerships.",
                "Numerical Shock Absorber: Mulank 1 grounds volatile Martian temperament into steady, protective responsibility.",
                "Spousal Respect: The marital union is marked by mutual respect, shared ambition, and unshakeable loyalty."
            ], "Manglik Audit & Numerical Buffer Takeaways"); ?>
            <?php echo self::render_page_footer(15, 'Manglik (Kuja Dosha, 23) Audit'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 16: KARMIC LOOPS: KAALSARP & PITRA DOSHA EVALUATION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Kaalsarp &amp; Pitra Karmic Scan</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">15 &bull; RAHU-KETU &amp; ANCESTORS</div>
            <h1 class="mr-page-h1">Kaalsarp &amp; Ancestral Balance with Numbers</h1>
            <div class="mr-page-sub">Understanding Rahu-Ketu energy and easy daily prayers to invite family blessings</div>

            <!-- 2-Column Dosha Diagnostic Cards -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-bottom:10px;">
                <!-- Kaalsarp Card -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">1. Kaalsarp Yoga Evaluation</span>
                        <span class="mr-badge mr-badge-emerald">Fully Broken (Bhanga)</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 6px 0;">
                        With Rahu in the 1st house (Cancer) and Ketu in the 7th house (Capricorn), a nodal axis is formed. However, Jupiter, Moon, and Saturn sit outside the nodal hem, completely dismantling classical Kaalsarp captivity.
                    </p>
                    <div style="font-size: 13.8px; color:#047857; background:#F0FDF4; padding:5px 8px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Diagnostic:</strong> Zero Kaalsarp impediment. Life progression flows forward without cyclical reversals.
                    </div>
                </div>

                <!-- Pitra Dosha Card -->
                <div class="mr-card" style="padding:12px 16px; margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase;">2. Pitra Dosha &amp; Ancestral Flow</span>
                        <span class="mr-badge mr-badge-emerald">Pristine / Negligible</span>
                    </div>
                    <p style="font-size: 14.4px; color:#374151; line-height:1.45; margin:0 0 6px 0;">
                        Paternal indicator Surya (Sun) resides in Sagittarius in the 6th house alongside Mercury, forming Budhaditya Yoga, completely free from Rahu-Ketu or Saturnian conjunction. The ancestral channel is clear.
                    </p>
                    <div style="font-size: 13.8px; color:#047857; background:#F0FDF4; padding:5px 8px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Diagnostic:</strong> Ancestral blessings remain active; no unfulfilled generational debts stall progress.
                    </div>
                </div>
            </div>

            <!-- Lo Shu Numerical Resilience Scorecard -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Sankhya Shastra Karmic Resilience Metrics
                </div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; font-size: 13.8px;">
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">Double 1 Solar Resilience:</strong>
                        <div style="color:#4B5563; margin-top:2px;">Two 1s in the Lo Shu grid supply limitless vitality, burning through minor occult karmic hindrances.</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#047857;">Single 9 Martian Armor:</strong>
                        <div style="color:#4B5563; margin-top:2px;">Fire number 9 provides natural psychological immunity against fear, anxiety, or pessimistic fatalism.</div>
                    </div>
                    <div style="background:#FAF7F2; padding:8px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#1E40AF;">8-1-6 Grounding Circuit:</strong>
                        <div style="color:#4B5563; margin-top:2px;">Practical plane locks the native's energy onto earthly solutions rather than superstitious anxiety.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Kaalsarp Clearance: Independent planetary positions completely break the nodal axis, preventing karmic entrapment.",
                "Pitra Grace: Unblemished Sun guarantees continuous paternal and ancestral support across life milestones.",
                "Solar-Martial Vigor: Double 1 and Single 9 numbers create an energetic shield against negativity.",
                "Reassurance: No costly or elaborate dosha pacification rituals are necessary; standard moral rectitude suffices."
            ], "Kaalsarp & Pitra Dosha Takeaways"); ?>
            <?php echo self::render_page_footer(16, 'Kaalsarp & Pitra Dosha Audit', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 17: CAREER & WEALTH NUMEROLOGICAL MATRIX
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Career &amp; Wealth Matrix</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">16 &bull; CAREER, JOB &amp; WEALTH</div>
            <h1 class="mr-page-h1">Your Career &amp; Money Guide with Numbers</h1>
            <div class="mr-page-sub">Best careers, business opportunities, and smart money habits for your numbers</div>

            <!-- Master Wealth Engine Card -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#FFFDF7; border-left:3px solid #C2964C;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#92400E; text-transform:uppercase;">
                        &starf; The 8-1-6 Commercial Engine &amp; 2nd House Dhana Yoga
                    </div>
                    <span class="mr-badge mr-badge-gold">High Financial Trajectory</span>
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    Your astrological chart features a magnificent <strong>2nd house Dhana Yoga</strong> in Leo (Jupiter + Moon conjunction, forming a classical Gajakesari-like treasury), while your Lo Shu grid is anchored by the full <strong>8-1-6 Arrow of Prosperity</strong>. This combination ensures that your wealth accumulates not through flash-in-the-pan speculation, but through compounding real estate, institutional commerce, technology, and organizational authority.
                </p>
            </div>

            <!-- Industry Alignment Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Vocation &amp; Commercial Sector Compatibility Matrix
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Commercial Sector</th>
                            <th>Planetary &amp; Numerical Ruler</th>
                            <th>Alignment Score</th>
                            <th>Operational Strategy for Native</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Real Estate &amp; Infrastructure</strong></td>
                            <td>Shani (8) &bull; Earth Element</td>
                            <td><span class="mr-badge mr-badge-emerald">98% Ideal</span></td>
                            <td>Land banking, commercial leasing, construction project management.</td>
                        </tr>
                        <tr>
                            <td><strong>Digital Enterprise &amp; Software</strong></td>
                            <td>Rahu (4) &amp; Budh (5)</td>
                            <td><span class="mr-badge mr-badge-emerald">94% Ideal</span></td>
                            <td>Scalable IT architecture, fintech platforms, data-driven systems.</td>
                        </tr>
                        <tr>
                            <td><strong>Financial Advisory &amp; Legal</strong></td>
                            <td>Guru (3) &amp; Shani (8)</td>
                            <td><span class="mr-badge mr-badge-blue">90% Strong</span></td>
                            <td>Corporate governance, structured finance, compliance advisory.</td>
                        </tr>
                        <tr>
                            <td><strong>High-Margin Luxury Goods</strong></td>
                            <td>Shukra (6) &bull; Namank 6</td>
                            <td><span class="mr-badge mr-badge-blue">88% Strong</span></td>
                            <td>Aesthetic retail, lifestyle branding, high-end hospitality.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Prime Calling: High-scale infrastructure, corporate asset development, and technology platform architecture.",
                "Wealth Multiplier: Jupiter and Moon in the 2nd house guarantee continuous resource generation and wealth retention.",
                "Transaction Timing: Schedule high-value asset acquisitions on the 5th, 6th, 14th, 15th, 23rd, or 24th of any month.",
                "Governance Rule: Maintain rigid contractual clarity in all business partnerships to preserve long-term margins."
            ], "Career & Wealth Acceleration Takeaways"); ?>
            <?php echo self::render_page_footer(17, 'Numerological Career & Wealth Matrix', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 18: RELATIONSHIP & MATRIMONIAL CONCORDANCE
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Relationship Concordance Dynamics</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">17 &bull; LOVE &amp; MARRIAGE COMPATIBILITY</div>
            <h1 class="mr-page-h1">Love, Marriage &amp; Partnership Numbers</h1>
            <div class="mr-page-sub">Which partner numbers are most compatible with you for a peaceful, loving married life</div>

            <!-- Relationship Archetype Summary -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#FAF7F2; border-left:3px solid #047857;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase; margin-bottom:4px;">
                    The Dual Dynamic: Saturnian Loyalty &amp; Lunar Warmth
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    In marriage, an individual with <strong>Mulank 1</strong> offers rock-solid reliability, fierce protection, and material security, while <strong>Bhagyank 5</strong> provides tender emotional attentiveness. You do not treat relationships casually; you seek lifelong companionship anchored in mutual trust, intellectual respect, and shared family values.
                </p>
            </div>

            <!-- Partner Compatibility Scorecard -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Partner Numerological Compatibility Matrix
                </div>
                <table class="mr-table" style="font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Partner Number</th>
                            <th>Planetary Ruler</th>
                            <th>Compatibility Rating</th>
                            <th>Relationship Dynamic &amp; Marital Advice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#F0FDF4;">
                            <td><strong>Number 1</strong></td>
                            <td>Surya (Sun)</td>
                            <td><span class="mr-badge mr-badge-emerald">92% &bull; Sovereign Alliance</span></td>
                            <td>Dynamic mutual admiration. Partner provides visionary fire, native provides structure.</td>
                        </tr>
                        <tr style="background:#F0FDF4;">
                            <td><strong>Number 2</strong></td>
                            <td>Chandra (Moon)</td>
                            <td><span class="mr-badge mr-badge-emerald">90% &bull; Deep Empathy</span></td>
                            <td>Total emotional resonance with Bhagyank 5. Peaceful, nurturing domestic sanctuary.</td>
                        </tr>
                        <tr style="background:#FFFDF7;">
                            <td><strong>Number 5</strong></td>
                            <td>Budh (Mercury)</td>
                            <td><span class="mr-badge mr-badge-blue">88% &bull; Lively Harmony</span></td>
                            <td>Intellectual freshness. Bridges native's missing 5, bringing laughter and spontaneity.</td>
                        </tr>
                        <tr style="background:#F0FDF4;">
                            <td><strong>Number 6</strong></td>
                            <td>Shukra (Venus)</td>
                            <td><span class="mr-badge mr-badge-emerald">95% &bull; Supreme Match</span></td>
                            <td>Matches Namank 6. Peak romantic warmth, luxury lifestyle, and domestic joy.</td>
                        </tr>
                        <tr>
                            <td><strong>Number 8</strong></td>
                            <td>Shani (Saturn)</td>
                            <td><span class="mr-badge mr-badge-amber">70% &bull; Power Challenge</span></td>
                            <td>Shared goals, but both partners may struggle with vulnerability; requires conscious softness.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Prime Matrimonial Matches: Numbers 1, 2, 5, and 6 provide ideal psychological and energetic harmony.",
                "Venusian Synergy: Partner with Number 6 unlocks peak romantic fulfillment and domestic comfort.",
                "Communication Protocol: Avoid withholding emotional frustrations; open dialogue prevents Saturnian coldness.",
                "Shared Legacy: Joint financial planning and mutual property building deepen your marital connection."
            ], "Relationship & Matrimonial Takeaways"); ?>
            <?php echo self::render_page_footer(18, 'Relationship & Matrimonial Dynamics', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 19: INTEGRATED REMEDIES: GEMSTONE PRESCRIPTION
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Gemstone Prescription Table</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">18 &bull; LUCKY GEMSTONES &amp; COLORS</div>
            <h1 class="mr-page-h1">Lucky Gemstones, Metals &amp; Power Colors</h1>
            <div class="mr-page-sub">Best gemstones and lucky colors to wear for confidence, focus, and good health</div>

            <!-- Primary Gemstone Focus Card -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:10px; background:#FAF7F2; border-left:3px solid #1E40AF;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#1E40AF; text-transform:uppercase; margin-bottom:4px;">
                    &diams; Primary Recommendation: Natural Blue Sapphire (Neelam) / Blue Topaz
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    As a <strong>Mulank 1 native</strong> with Saturn ruling your mental wiring and currently operating under <strong>Saturn Mahadasha</strong>, Blue Sapphire acts as a supersonic conductor of cosmic discipline, asset protection, and executive focus. For gentler daily alignment, high-grade <strong>Natural Amethyst (Jamunia)</strong> or <strong>Blue Topaz</strong> provides equivalent grounding without excess intensity.
                </p>
            </div>

            <!-- Gemstone Prescription Table -->
            <div class="mr-card" style="padding:10px 14px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Certified Gemstone Prescription &amp; Mounting Protocols
                </div>
                <table class="mr-table" style="font-size: 13.8px;">
                    <thead>
                        <tr>
                            <th>Gemstone (Ratna)</th>
                            <th>Target Vibration</th>
                            <th>Ideal Carat Weight</th>
                            <th>Metal &amp; Finger</th>
                            <th>Consecration Day &amp; Mantra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#EFF6FF;">
                            <td><strong>Blue Sapphire (Neelam)</strong></td>
                            <td>Mulank 1 &bull; Sun</td>
                            <td>4.5 &ndash; 6.25 Carats</td>
                            <td>Silver / White Gold &bull; Middle Finger</td>
                            <td>Saturday dusk &bull; <em>Om Sham Shanaicharaya Namah</em></td>
                        </tr>
                        <tr style="background:#F0FDF4;">
                            <td><strong>Natural Pearl (Moti)</strong></td>
                            <td>Bhagyank 5 &bull; Mercury</td>
                            <td>5.0 &ndash; 7.0 Carats</td>
                            <td>Pure Silver &bull; Little (Pinky) Finger</td>
                            <td>Monday morning &bull; <em>Om Som Somaya Namah</em></td>
                        </tr>
                        <tr style="background:#FFFDF7;">
                            <td><strong>White Zircon / Opal</strong></td>
                            <td>Namank 6 &bull; Venus</td>
                            <td>5.5 &ndash; 7.5 Carats</td>
                            <td>Silver &bull; Ring Finger</td>
                            <td>Friday sunrise &bull; <em>Om Shum Shukraya Namah</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Color Therapy Directive -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:4px; background:#FFFDF7;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#92400E; text-transform:uppercase; margin-bottom:4px;">
                    &bull; Practical Color Therapy Guidelines
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0;">
                    Infuse your wardrobe and executive workspace with <strong>Navy Blue, Steel Grey, Forest Green, and Off-White</strong>. These shades soothe the nervous system, stimulate intellectual clarity, and align with numbers 8, 2, and 6. Minimize aggressive neon scarlet on Saturdays.
                </p>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Primary Gemstone: Blue Sapphire or Amethyst reinforces Saturnian authority and long-term asset growth.",
                "Emotional Balancer: Natural South Sea Pearl worn on the pinky finger stabilizes Bhagyank 5 and Lagna Lord Moon.",
                "Mounting Metal: Silver or White Gold is strictly prescribed to harmonize planetary electrical conductivities.",
                "Color Alignment: Navy Blue, Forest Green, and Charcoal act as natural energetic shields during high-stakes work."
            ], "Gemstone & Color Therapy Takeaways"); ?>
            <?php echo self::render_page_footer(19, 'Gemstone & Color Therapy Prescription', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 20: RUDRAKSHA THERAPY & SACRED BEAD PROTOCOL
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Rudraksha Sacred Bead Protocol</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">19 &bull; SACRED RUDRAKSHA GUIDE</div>
            <h1 class="mr-page-h1">Sacred Rudraksha Beads for Peace &amp; Calm</h1>
            <div class="mr-page-sub">How natural Rudraksha beads protect peace of mind, reduce daily stress, and keep you centered</div>

            <!-- Rudraksha Trio Grid -->
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:10px;">
                <!-- 7-Mukhi -->
                <div class="mr-card" style="padding:12px 14px; margin-bottom:0; text-align:center;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase;">
                        7-Mukhi Rudraksha
                    </div>
                    <span class="mr-badge mr-badge-emerald" style="margin:5px 0;">Goddess Mahalakshmi &bull; Shani</span>
                    <p style="font-size: 14.4px; color:#4B5563; line-height:1.45; margin:4px 0 0 0;">
                        Directly pacifies Lord Shani and activates Mulank 1. Removes financial bottlenecks, shields against negative envy, and fosters steady commercial expansion.
                    </p>
                </div>

                <!-- 2-Mukhi -->
                <div class="mr-card" style="padding:12px 14px; margin-bottom:0; text-align:center;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase;">
                        2-Mukhi Rudraksha
                    </div>
                    <span class="mr-badge mr-badge-blue" style="margin:5px 0;">Ardhanarishvara &bull; Chandra</span>
                    <p style="font-size: 14.4px; color:#4B5563; line-height:1.45; margin:4px 0 0 0;">
                        Governed by Chandra; harmonizes Bhagyank 5 and Lagna Lord. Promotes deep mental tranquility, dissolves inner anxieties, and heals relationships.
                    </p>
                </div>

                <!-- 6-Mukhi -->
                <div class="mr-card" style="padding:12px 14px; margin-bottom:0; text-align:center;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#111827; text-transform:uppercase;">
                        6-Mukhi Rudraksha
                    </div>
                    <span class="mr-badge mr-badge-gold" style="margin:5px 0;">Lord Kartikeya &bull; Shukra</span>
                    <p style="font-size: 14.4px; color:#4B5563; line-height:1.45; margin:4px 0 0 0;">
                        Attuned to Venus and Namank 6. Sharpens willpower, removes lethargy, and enhances articulate speech during crucial executive negotiations.
                    </p>
                </div>
            </div>

            <!-- Master Combination & Consecration Protocol -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:6px; background:#FFFDF9; border:1px solid #F0E6D8;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    Personalized "Trishakti" Rudraksha Pendant Combination
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0 0 6px 0;">
                    String one <strong>7-Mukhi</strong> in the center flanked by one <strong>2-Mukhi</strong> and one <strong>6-Mukhi</strong> bead in pure silver wire or silk thread. Wear this pendant around the neck touching the Anahata (Heart) chakra.
                </p>
                <div style="background:#F9FAFB; padding:8px 12px; border-radius:4px; font-size: 13.8px; color:#4B5563; line-height:1.45;">
                    <strong>Prana Pratishtha Ritual:</strong> Consecrate on a Monday or Saturday morning by washing with raw cow's milk and Gangajal, applying sandalwood paste, and chanting <em>Om Namah Shivaya</em> 108 times.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Trishakti Formula: 7-Mukhi, 2-Mukhi, and 6-Mukhi unite Saturnian discipline, Lunar peace, and Venusian charisma.",
                "Bio-Field Harmony: Wearing authentic beads regulates electromagnetic stress and sustains sharp cognitive endurance.",
                "Chakra Alignment: Suspended over the heart chakra, it dissolves chronic subconscious burdens and restores vitality.",
                "Prana Pratishtha: Consecration under Shiva mantras permanently imprints divine protective frequencies."
            ], "Rudraksha Therapy Takeaways"); ?>
            <?php echo self::render_page_footer(20, 'Rudraksha Therapy & Sacred Beads', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 21: SPECIALIZED UPAYAS: MOBILE, DAAN & MANTRAS
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; Specialized Upayas &amp; Daan Schedules</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">20 &bull; DAILY HABITS, MOBILE &amp; CHARITY</div>
            <h1 class="mr-page-h1">Lucky Mobile Numbers, Daily Habits &amp; Mantras</h1>
            <div class="mr-page-sub">Easy tips for choosing phone numbers, simple daily mantras, and small weekly acts of kindness</div>

            <!-- Mobile & Residential Numerology Card -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    1. Mobile &amp; Residential Number Harmonization
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; font-size: 14.8px; line-height:1.5;">
                    <div>
                        <strong style="color:#047857;">Favorable Mobile Total:</strong>
                        <p style="color:#374151; margin:2px 0 0 0;">
                            Select mobile numbers whose 10 digits sum to <strong>5 (Budh)</strong> or <strong>6 (Shukra)</strong>. Ideal compound totals: <strong>24, 33, 42, or 51</strong>. Avoid numbers ending in 4 or 8 to eliminate sudden communication delays.
                        </p>
                    </div>
                    <div>
                        <strong style="color:#047857;">Auspicious House / Door Numbers:</strong>
                        <p style="color:#374151; margin:2px 0 0 0;">
                            Residences or office premises summing to <strong>1, 3, 5, or 6</strong> foster harmonious family living and continuous commercial cash flow. If your premises sum to 4 or 8, add a copper nameplate engraved with a Sun glyph.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Planetary Daan & Charity Schedule -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:8px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    2. Planetary Daan (Sacred Charity) Calendar
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; font-size: 14.8px; line-height:1.5;">
                    <div style="background:#FAF7F2; padding:10px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#C45738;">Saturday Daan (Lord Shani):</strong>
                        <p style="color:#4B5563; margin:2px 0 0 0;">
                            Donate black sesame seeds, mustard oil, or dark blankets to elderly workers. Light a sesame oil diya under a Peepal tree on Saturday evenings to dissolve stubborn karmic resistance.
                        </p>
                    </div>
                    <div style="background:#FAF7F2; padding:10px 12px; border-radius:6px; border:1px solid #ECE6DD;">
                        <strong style="color:#1E40AF;">Monday Daan (Lord Chandra):</strong>
                        <p style="color:#4B5563; margin:2px 0 0 0;">
                            Offer milk or white rice to needy mothers or animal shelters. Performing Shiva Abhishekam on Mondays ensures uninterrupted mental serenity and emotional fulfillment.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Daily Mantra Prescription Card -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:4px; background:#F8FAFC;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:4px;">
                    3. Daily Japa Prescriptions (108 Chants)
                </div>
                <div style="display:flex; justify-content:space-between; font-size: 14.4px; padding:4px 0; border-bottom:1px solid #E2E8F0;">
                    <span><strong>Shani Beej Mantra:</strong> <em>Om Pram Preem Proum Sah Shanaicharaya Namah</em></span>
                    <span style="color:#C45738; font-weight:700;">108x Saturday</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size: 14.4px; padding:4px 0;">
                    <span><strong>Chandra Beej Mantra:</strong> <em>Om Shram Shreem Shroum Sah Chandraya Namah</em></span>
                    <span style="color:#1E40AF; font-weight:700;">108x Monday</span>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Mobile Optimization: Calibrate phone numbers to compound 24, 33, or 42 to attract profitable commercial contacts.",
                "Saturday Peepal Seva: Weekly mustard oil diya pacifies Shani and unlocks delayed institutional approvals.",
                "Monday Lunar Flow: Milk charity and Shiva Abhishekam nurture Bhagyank 5 and preserve domestic peace.",
                "Vibrational Integrity: Chanting planetary beej mantras on assigned weekdays permanently aligns your mental bio-frequency."
            ], "Specialized Upayas & Protocols Takeaways"); ?>
            <?php echo self::render_page_footer(21, 'Specialized Upayas: Mobile, Daan & Mantras'); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 22: 90-DAY TACTICAL MANIFESTATION ROADMAP
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta"><?php echo esc_html($person_name); ?> &bull; 90-Day Tactical Manifestation Plan</span>
        </div>

        <div class="mr-page-body">
            <div class="mr-tag-pill">21 &bull; 90-DAY STEP-BY-STEP PLAN</div>
            <h1 class="mr-page-h1">Your 90-Day Action Plan for Success</h1>
            <div class="mr-page-sub">A simple 3-month guide to setting goals, building good habits, and achieving results</div>

            <!-- Month 1: Grounding & Reset -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:8px; border-left:3px solid #C45738;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#C45738; text-transform:uppercase;">
                        Month 1: Structural Purification &amp; Workspace Reset
                    </div>
                    <span class="mr-badge mr-badge-amber">Days 1 &ndash; 30</span>
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0 0 5px 0;">
                    <strong>Objective:</strong> Ground the physical environment and dissolve energetic static.
                </p>
                <div style="font-size: 14px; color:#4B5563; line-height:1.45;">
                    &bull; Place an untreated Pyrite or Citrine stone at the center of your primary workspace desk to anchor missing Lo Shu 5.<br/>
                    &bull; Conduct a complete audit of signatures, email footers, and legal identity documents; implement the upward-flourish signature style.<br/>
                    &bull; Initiate the Trishakti Rudraksha protocol on an auspicious Saturday or Monday morning.
                </div>
            </div>

            <!-- Month 2: Strategic Commercial Launches -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:8px; border-left:3px solid #047857;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase;">
                        Month 2: High-Velocity Commercial Expansion &amp; Signings
                    </div>
                    <span class="mr-badge mr-badge-emerald">Days 31 &ndash; 60</span>
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0 0 5px 0;">
                    <strong>Objective:</strong> Leverage the Double-Confirmation Window of Personal Year 3 and Mercury dasha.
                </p>
                <div style="font-size: 14px; color:#4B5563; line-height:1.45;">
                    &bull; Schedule all high-stakes contract executions, business partner negotiations, and brand launches on dates reducing to 5 or 6.<br/>
                    &bull; Activate outreach to institutions, high-net-worth clients, or corporate sponsors; your persuasive clarity is at an annual peak.<br/>
                    &bull; Wear Blue Sapphire or Amethyst on Saturdays and Pearl on Mondays to sustain cognitive sharpness.
                </div>
            </div>

            <!-- Month 3: Consolidation & Legacy Anchoring -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:4px; border-left:3px solid #1E40AF;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#1E40AF; text-transform:uppercase;">
                        Month 3: Asset Locking, Philanthropy &amp; Domestic Grace
                    </div>
                    <span class="mr-badge mr-badge-blue">Days 61 &ndash; 90</span>
                </div>
                <p style="font-size: 14.8px; color:#374151; line-height:1.5; margin:0 0 5px 0;">
                    <strong>Objective:</strong> Retain profits and strengthen domestic bonds for compound growth.
                </p>
                <div style="font-size: 14px; color:#4B5563; line-height:1.45;">
                    &bull; Lock in commercial gains through real estate investments, land banking, or fixed sovereign instruments.<br/>
                    &bull; Host a shared family gathering or spiritual feast (Bhandara) to return gratitude to the community and pacify subtle debts.<br/>
                    &bull; Review quarterly metrics; the groundwork laid will compound steadily over the subsequent 9-year epicycle.
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <?php echo self::render_page_bottom_synthesis([
                "Month 1 Discipline: Environment decluttering and Rudraksha consecration establish an impregnable bio-energetic foundation.",
                "Month 2 Momentum: Direct all commercial expansion into the 5th, 6th, 14th, 15th, 23rd, and 24th power windows.",
                "Month 3 Capitalization: Reinvest revenue into stable physical assets and perform charitable daan to lock in lifetime prosperity.",
                "Holistic Success: Blending practical strategy with Vedic remedial discipline ensures durable, stress-free elevation."
            ], "90-Day Tactical Manifestation Takeaways"); ?>
            <?php echo self::render_page_footer(22, 'Your 90-Day Action Plan for Success', 23); ?>
        </div>
    </div>

    <!-- ========================================================
         PAGE 23: CONCLUDING CERTIFICATE & 16-POINT GLOSSARY
         ======================================================== -->
    <div class="mr-page">
        <div class="mr-header-bar">
            <div class="mr-header-brand-wrap">
                <img src="<?php echo esc_attr($logo_uri); ?>" alt="MeraRashifal" class="mr-header-logo-img" />
            </div>
            <span class="mr-header-meta">MeraRashifal &bull; Concluding Numerological Dossier &amp; Glossary</span>
        </div>

        <div class="mr-page-body">
            <div style="text-align:center; margin-bottom:8px;">
                <div style="font-size: 13.8px; font-weight:800; color:#C45738; text-transform:uppercase; letter-spacing:0.08em;">
                    SACRED SANKHYA &amp; VEDIC BENEDICTION
                </div>
                <h1 style="font-family:<?php echo $head_font; ?>; font-size:19px; font-weight:800; color:#111827; margin:2px 0 4px 0;">
                    May Divine Planetary Grace &amp; Numerical Harmony Guide Your Path!
                </h1>
                <div style="font-family:'Poppins', sans-serif; font-size: 15.6px; font-weight:700; color:#047857; margin-bottom:2px;">
                    &Verbar; ॐ नमः शिवाय &bull; ॐ शं शनैश्चराय नमः &bull; ॐ सों सोमाय नमः &Verbar;
                </div>
                <div style="font-size: 13.1px; color:#6B7280; font-style:italic;">
                    "May Lord Shani bestow endurance, Lord Chandra grant serene wisdom, and the cosmic numbers illuminate your highest purpose."
                </div>
            </div>

            <!-- Alignment Certificate Box -->
            <div class="mr-card" style="padding:12px 18px; margin-bottom:8px; border:1.5px solid #047857; background:#F0FDF4; border-left:4px solid #047857;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <div style="font-family:<?php echo $head_font; ?>; font-size: 15.6px; font-weight:800; color:#047857; text-transform:uppercase;">
                        CERTIFICATE OF NUMEROLOGICAL &amp; VEDIC ALIGNMENT
                    </div>
                    <span class="mr-badge mr-badge-emerald">AUTHENTIC AUDIT APPROVED</span>
                </div>
                <p style="font-size: 14.8px; color:#111827; line-height:1.5; margin:0 0 8px 0;">
                    This certifies that the personal coordinates and cosmic vibrations of <strong><?php echo esc_html(strtoupper($person_name)); ?></strong> (DOB: <?php echo esc_html($dob); ?>) have been comprehensively audited under classical Parashari Jyotish, authentic Indian Sankhya Shastra, and sacred Lo Shu grid geometry. Featuring the complete <strong>8-1-6 Arrow of Practicality</strong>, 100% Kuja Bhanga, and active <strong>Saturn-Mercury dasha synchronicity</strong>, this destiny is endorsed for extraordinary commercial, intellectual, and familial elevation.
                </p>
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:8px; font-size: 13.8px; text-align:center;">
                    <div style="background:#FFFFFF; padding:5px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Mulank:</strong> 8 (Shani)
                    </div>
                    <div style="background:#FFFFFF; padding:5px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Bhagyank:</strong> 2 (Chandra)
                    </div>
                    <div style="background:#FFFFFF; padding:5px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Namank:</strong> 6 (Shukra)
                    </div>
                    <div style="background:#FFFFFF; padding:5px; border-radius:4px; border:1px solid #DCFCE7;">
                        <strong>Practical Arrow:</strong> 100% Complete
                    </div>
                </div>
            </div>

            <!-- 16-Point Comprehensive Glossary -->
            <div class="mr-card" style="padding:10px 16px; margin-bottom:6px;">
                <div style="font-family:<?php echo $head_font; ?>; font-size: 15px; font-weight:800; color:#111827; text-transform:uppercase; margin-bottom:6px; border-bottom:1px solid #F3EFEA; padding-bottom:3px;">
                    Vedic Numerological Glossary (16 Core Canonical Terms)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 18px; font-size: 13.1px; line-height:1.4; color:#374151;">
                    <div><strong style="color:#C45738;">1. Mulank (Psychic Number):</strong> Core personality digit calculated from the day of birth (1–31).</div>
                    <div><strong style="color:#C45738;">2. Bhagyank (Destiny Number):</strong> Life path digit derived from summing full date, month, and year.</div>
                    <div><strong style="color:#C45738;">3. Namank (Name Number):</strong> Mathematical sound frequency of full personal or brand name.</div>
                    <div><strong style="color:#C45738;">4. Lo Shu Grid:</strong> 3x3 magic square balancing nine sacred planetary energies.</div>
                    <div><strong style="color:#C45738;">5. Arrow of Practicality (8-1-6):</strong> Rare configuration conferring grounded commercial success.</div>
                    <div><strong style="color:#C45738;">6. Planes of Expression:</strong> Three horizontal rows representing Mental, Emotional, and Physical life.</div>
                    <div><strong style="color:#C45738;">7. Personal Year:</strong> Annual 9-year cycle modulating opportunities and challenges.</div>
                    <div><strong style="color:#C45738;">8. Double-Confirmation:</strong> Synchronization of personal year number with active dasha lord.</div>
                    <div><strong style="color:#047857;">9. Vimshottari Dasha:</strong> 120-year Vedic planetary cycle determining precise life timing.</div>
                    <div><strong style="color:#047857;">10. Kuja Bhanga:</strong> Canonical astrological conditions that dissolve Manglik afflictions.</div>
                    <div><strong style="color:#047857;">11. Ruchaka Yoga:</strong> Elite Mahapurusha yoga formed by exalted Mars in a Kendra house.</div>
                    <div><strong style="color:#047857;">12. Dhana Yoga:</strong> Astrological alignment of 2nd, 5th, 9th, and 11th houses generating wealth.</div>
                    <div><strong style="color:#047857;">13. Upachaya Houses:</strong> Houses 3, 6, 10, and 11 where malefic planets improve with maturity.</div>
                    <div><strong style="color:#047857;">14. Ratna Therapy:</strong> Wearing specific crystalline gemstones to amplify planetary frequencies.</div>
                    <div><strong style="color:#047857;">15. Rudraksha Mukhi:</strong> Natural sacred beads with distinct facial lines pacifying nervous stress.</div>
                    <div><strong style="color:#047857;">16. Prana Pratishtha:</strong> Sacred Vedic ritual to consecrate and activate energetic remedies.</div>
                </div>
            </div>

            <!-- Official Consultation Portal Footer Box -->
            <div class="mr-card" style="padding:6px 14px; margin-bottom:0; display:flex; justify-content:space-between; align-items:center; background:#FAF7F2; border:1px solid #ECE6DD;">
                <div>
                    <div style="font-weight:800; font-size: 13.1px; color:#111827; text-transform:uppercase;">Official Customer Verification &amp; Consultation Portal</div>
                    <div style="font-size: 12.5px; color:#6B7280;">Access authenticated numerological archives, kundali updates, and expert guidance.</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:<?php echo $head_font; ?>; font-weight:800; font-size: 14.4px; color:#C45738;">www.MeraRashifal.com</div>
                    <div style="font-size: 11.9px; color:#9CA3AF;">Document ID: MR-NUM-8260F7</div>
                </div>
            </div>
        </div>

        <div class="mr-page-bottom-pin">
            <div class="mr-page-footer">
                <span class="mr-footer-left">MeraRashifal &bull; Concluding Numerological Dossier &amp; Glossary</span>
                <span class="mr-footer-right">Page 22 of 22</span>
            </div>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
