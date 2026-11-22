<?php
/**
 * Template Name: Stitch New Home
 * Description: Authentic Vedic Astrology Portal Homepage with Live Panchang, Reports Sync & Dynamic Section Manager.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Fetch live Panchang data for today (fallback gracefully to latest calculated day)
global $wpdb;
$today_str = current_time('Y-m-d');
$live_panchang = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_panchang_data WHERE api_date = %s LIMIT 1", $today_str), ARRAY_A);
if (!$live_panchang) {
    $live_panchang = $wpdb->get_row("SELECT * FROM af_panchang_data ORDER BY api_date DESC LIMIT 1", ARRAY_A);
}

// Fallback values if DB is empty or missing specific fields
$tithi_display     = !empty($live_panchang['tithi_name']) ? ($live_panchang['tithi_type'] . ' ' . $live_panchang['tithi_name']) : 'कार्तिक शुक्ल एकादशी';
$nakshatra_display = !empty($live_panchang['nakshatra_name']) ? $live_panchang['nakshatra_name'] : 'उत्तराषाढ़ा';
$nakshatra_lord    = !empty($live_panchang['nakshatra_lord']) ? $live_panchang['nakshatra_lord'] : 'सूर्य';
$nakshatra_deity   = !empty($live_panchang['nakshatra_deity']) ? $live_panchang['nakshatra_deity'] : (!empty($live_panchang['nakshatra_diety']) ? $live_panchang['nakshatra_diety'] : 'विश्वेदेवस');
$yoga_display      = !empty($live_panchang['yoga_name']) ? $live_panchang['yoga_name'] : 'सिद्धि';
$karana_display    = !empty($live_panchang['karana_name']) ? $live_panchang['karana_name'] : 'कौलव';
$sun_zodiac        = !empty($live_panchang['sun_zodiac']) ? $live_panchang['sun_zodiac'] : 'तुला (Libra)';
$moon_zodiac       = !empty($live_panchang['rasi_name']) ? $live_panchang['rasi_name'] : 'मकर (Capricorn)';
$sunrise_display   = !empty($live_panchang['sun_rise']) ? $live_panchang['sun_rise'] : '06:34 AM';
$sunset_display    = !empty($live_panchang['sun_set']) ? $live_panchang['sun_set'] : '05:42 PM';
$moonrise_display  = !empty($live_panchang['moon_rise']) ? $live_panchang['moon_rise'] : '01:14 PM';
$moonset_display   = !empty($live_panchang['moon_set']) ? $live_panchang['moon_set'] : '01:38 AM';
$abhijit_display   = (!empty($live_panchang['abhijit_muhurta_start']) && !empty($live_panchang['abhijit_muhurta_end']))
    ? ($live_panchang['abhijit_muhurta_start'] . ' – ' . $live_panchang['abhijit_muhurta_end'])
    : '11:47 AM – 12:32 PM';
$rahukaal_display  = !empty($live_panchang['rahukaal']) ? $live_panchang['rahukaal'] : '03:00 PM – 04:22 PM';
$vikram_samvat     = !empty($live_panchang['year_vikram']) ? $live_panchang['year_vikram'] : '2083';
$saka_samvat       = !empty($live_panchang['year_saka']) ? $live_panchang['year_saka'] : '1948';

// 2. Fetch Homepage Manager Configuration
$homepage_config  = class_exists('MR_Homepage_Manager') ? MR_Homepage_Manager::get_config() : [];
$ordered_sections = class_exists('MR_Homepage_Manager') ? MR_Homepage_Manager::get_ordered_sections() : [];
$style_cfg        = $homepage_config['style'] ?? [];
$page_bg_color    = !empty($style_cfg['bg_color']) ? $style_cfg['bg_color'] : '#FFFBEB';

// Call the official theme header (Elementor / Astra Header)
get_header();
?>

<!-- Material Symbols & Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<!-- Scoped Tailwind CDN without preflight to preserve Astra/Elementor theme header & footer -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config-mr">
  tailwind.config = {
    darkMode: "class",
    corePlugins: {
      preflight: false,
    },
    theme: {
      extend: {
        colors: {
          "primary": "#964900",
          "primary-hover": "#7a3b00",
          "primary-container": "#f58220",
          "primary-fixed": "#ffdcc6",
          "on-primary-fixed": "#311300",
          "secondary": "#9f3c59",
          "secondary-hover": "#802442",
          "secondary-fixed": "#ffd9df",
          "on-secondary-fixed": "#3f0018",
          "tertiary": "#7d5700",
          "tertiary-container": "#cb9836",
          "tertiary-fixed": "#ffdeaa",
          "on-tertiary-fixed": "#271900",
          "on-surface": "#221a16",
          "on-surface-variant": "#564336",
          "surface-card": "#ffffff",
          "surface-cream": "#fff8f6",
          "surface-container": "#fbebe4",
          "surface-container-low": "#fff1eb",
          "surface-container-high": "#f5e5df",
          "outline-warm": "rgba(217, 119, 6, 0.2)",
          "outline-variant": "#ddc1b0",
        },
        fontFamily: {
          "sans": ["Inter", "-apple-system", "BlinkMacSystemFont", "Segoe UI", "Roboto", "sans-serif"],
          "serif": ["Playfair Display", "Georgia", "serif"],
          "display": ["Poppins", "Inter", "sans-serif"],
        }
      }
    }
  };
</script>

<style>
/* ==========================================================
 * SACRED VEDIC VISUAL IDENTITY & AMBIENCE
 * ========================================================== */
.mr-homepage-master-wrap {
  overflow-x: hidden;
  max-width: 100vw;
  width: 100%;
  background-color: #FFFBEB;
  background-image: 
    radial-gradient(ellipse at 50% 0%, rgba(245, 130, 32, 0.12) 0%, rgba(255, 251, 235, 0) 65%),
    radial-gradient(circle at 85% 25%, rgba(159, 60, 89, 0.05) 0%, transparent 40%),
    radial-gradient(circle at 15% 65%, rgba(203, 152, 54, 0.06) 0%, transparent 45%);
  color: #221a16;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  position: relative;
}
.mr-homepage-master-wrap * {
  box-sizing: border-box;
}
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  display: inline-block;
  vertical-align: middle;
  line-height: 1;
}

/* Custom Scrollbars */
.custom-scroll::-webkit-scrollbar {
  height: 4px;
  width: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #cb9836;
  border-radius: 9999px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: rgba(221, 193, 176, 0.3);
  border-radius: 9999px;
}

/* Shastric Radiance & Card Hover Animations */
.spiritual-glow {
  background: radial-gradient(ellipse at top, rgba(245, 130, 32, 0.08) 0%, rgba(255, 255, 255, 0.96) 75%);
}
.golden-card {
  background: #ffffff;
  border: 1px solid rgba(217, 119, 6, 0.18);
  border-radius: 1rem;
  box-shadow: 0 4px 18px -4px rgba(150, 73, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.golden-card:hover {
  transform: translateY(-3px);
  border-color: rgba(245, 130, 32, 0.45);
  box-shadow: 0 14px 28px -6px rgba(150, 73, 0, 0.14), 0 0 0 1px rgba(203, 152, 54, 0.25);
}

/* Mobile Segmented Tab Active Styles */
.hero-tab-btn.active {
  background: #f58220;
  color: #ffffff;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(245, 130, 32, 0.3);
}

/* Compact Card Elevations */
.compact-card {
  background: #ffffff;
  border: 1px solid rgba(217, 119, 6, 0.16);
  border-radius: 0.875rem;
  box-shadow: 0 2px 10px -2px rgba(150, 73, 0, 0.04);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.compact-card:hover {
  transform: translateY(-2px);
  border-color: rgba(245, 130, 32, 0.4);
  box-shadow: 0 8px 20px -4px rgba(150, 73, 0, 0.12);
}

/* Micro Zodiac Pill */
.zodiac-pill {
  border: 1px solid rgba(217, 119, 6, 0.18);
  background: #ffffff;
  transition: all 0.18s ease-in-out;
}
.zodiac-pill:hover, .zodiac-pill:active {
  background: #fff8f6;
  border-color: #f58220;
  transform: translateY(-1.5px);
  box-shadow: 0 4px 10px rgba(150, 73, 0, 0.1);
}

/* Gateway Micro Chip */
.gateway-chip {
  border: 1px solid rgba(217, 119, 6, 0.15);
  background: #ffffff;
  transition: all 0.18s ease-in-out;
}
.gateway-chip:hover, .gateway-chip:active {
  border-color: #f58220;
  background: #fff8f6;
  transform: translateY(-1px);
}

.blend-logo {
  mix-blend-mode: multiply;
}

button, a, input, select {
  touch-action: manipulation;
}

/* Sun Sign AJAX Card Styling */
.af-zodiac-result-card {
  background: #ffffff !important;
  border: 1px solid rgba(245, 130, 32, 0.35) !important;
  border-radius: 1rem !important;
  padding: 1.5rem !important;
  box-shadow: 0 8px 24px rgba(150, 73, 0, 0.09) !important;
  margin-top: 1.25rem !important;
  animation: fadeIn 0.35s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.af-zodiac-header h4 {
  color: #964900 !important;
  font-family: "Playfair Display", serif !important;
  font-size: 1.35rem !important;
  font-weight: 700 !important;
  margin: 0 0 0.5rem 0 !important;
}
.af-zodiac-header h4 span {
  color: #f58220 !important;
  font-weight: 800 !important;
}
.af-zodiac-desc {
  color: #564336 !important;
  font-size: 0.875rem !important;
  line-height: 1.65 !important;
}
.af-zodiac-details-grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
  gap: 0.75rem !important;
  margin: 1.25rem 0 !important;
}
.af-zodiac-item {
  background: #fff8f6 !important;
  border: 1px solid #f0ded7 !important;
  border-left: 3px solid #f58220 !important;
  border-radius: 0.625rem !important;
  padding: 0.6rem 0.85rem !important;
}
.af-z-label {
  font-size: 0.68rem !important;
  font-weight: 700 !important;
  color: #7d5700 !important;
  text-transform: uppercase !important;
}
.af-z-value {
  font-size: 0.95rem !important;
  font-weight: 700 !important;
  color: #964900 !important;
  margin-top: 0.15rem !important;
}
.af-btn.zodiac-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 0.5rem !important;
  background: #f58220 !important;
  color: #ffffff !important;
  padding: 0.6rem 1.4rem !important;
  border-radius: 0.625rem !important;
  font-weight: 600 !important;
  font-size: 0.85rem !important;
  text-decoration: none !important;
  transition: all 0.2s !important;
}
.af-btn.zodiac-btn:hover {
  background: #e07210 !important;
  transform: translateY(-1px);
}
</style>

<div class="mr-homepage-master-wrap">

<?php
// Iterate through sections ordered by manager
foreach ($ordered_sections as $section_id => $sec_data) {
    if (empty($sec_data['enabled'])) {
        continue;
    }

    // Skip telegram_banner as per user instruction (marked with red cross)
    if ($section_id === 'telegram_banner') {
        continue;
    }

    $title    = $sec_data['title'] ?? '';
    $subtitle = $sec_data['subtitle'] ?? '';

    switch ($section_id) {

        /* ==========================================================
         * 1. ASTRONOMICAL STATUS STRIP
         * ========================================================== */
        case 'astronomical_bar':
            $logo_url = home_url('/merarashifal-logo.png');
            ?>
            <div class="border-b border-amber-900/10 bg-white/95 backdrop-blur-sm sticky top-0 z-40">
              <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-12 py-2 flex items-center justify-between gap-3">
                <!-- Left: Official Mera Rashifal Logo -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 shrink-0">
                  <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="h-8 sm:h-10 w-auto object-contain blend-logo" />
                </a>

                <!-- Right: Compact Live Celestial Strip -->
                <div class="flex items-center gap-2 sm:gap-4 text-[11px] sm:text-xs text-on-surface-variant">
                  <div class="flex items-center gap-1.5 font-bold text-primary">
                    <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse shrink-0"></span>
                    <span class="hidden md:inline">Vikram Samvat <?php echo esc_html($vikram_samvat); ?> (Pingala)</span>
                    <span class="md:hidden">VS <?php echo esc_html($vikram_samvat); ?></span>
                  </div>
                  <span class="text-amber-300">|</span>
                  <span class="text-secondary font-semibold truncate max-w-[150px] sm:max-w-none">
                    <?php echo esc_html($tithi_display); ?>
                  </span>
                  <span class="hidden lg:inline text-amber-300">|</span>
                  <span class="hidden lg:inline text-on-surface-variant font-medium">
                    ☀️ <?php echo esc_html($sunrise_display); ?> • Ujjain 23.17° N
                  </span>
                  <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="bg-primary-container hover:bg-orange-600 text-white font-bold px-2.5 py-1 rounded-lg text-[10px] sm:text-xs shadow-2xs shrink-0 flex items-center gap-1">
                    <span>₹99 Reports</span>
                  </a>
                </div>
              </div>
            </div>
            <?php
            break;

        /* ==========================================================
         * 2. 1-TAP DAILY HOROSCOPE (ULTRA-COMPACT ZODIAC PILLS)
         * ========================================================== */
        case 'zodiac_horoscope':
            $zodiacs = [
                ['slug' => 'aries',       'name' => 'Aries',       'hi' => 'मेष',     'symbol' => '♈'],
                ['slug' => 'taurus',      'name' => 'Taurus',      'hi' => 'वृषभ',    'symbol' => '♉'],
                ['slug' => 'gemini',      'name' => 'Gemini',      'hi' => 'मिथुन',   'symbol' => '♊'],
                ['slug' => 'cancer',      'name' => 'Cancer',      'hi' => 'कर्क',    'symbol' => '♋'],
                ['slug' => 'leo',         'name' => 'Leo',         'hi' => 'सिंह',    'symbol' => '♌'],
                ['slug' => 'virgo',       'name' => 'Virgo',       'hi' => 'कन्या',   'symbol' => '♍'],
                ['slug' => 'libra',       'name' => 'Libra',       'hi' => 'तुला',    'symbol' => '♎'],
                ['slug' => 'scorpio',     'name' => 'Scorpio',     'hi' => 'वृश्चिक', 'symbol' => '♏'],
                ['slug' => 'sagittarius', 'name' => 'Sagittarius', 'hi' => 'धनु',     'symbol' => '♐'],
                ['slug' => 'capricorn',   'name' => 'Capricorn',   'hi' => 'मकर',     'symbol' => '♑'],
                ['slug' => 'aquarius',    'name' => 'Aquarius',    'hi' => 'कुंभ',    'symbol' => '♒'],
                ['slug' => 'pisces',      'name' => 'Pisces',      'hi' => 'मीन',     'symbol' => '♓'],
            ];
            ?>
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-12 pt-3 pb-1">
              <div class="bg-white p-2.5 sm:p-3.5 rounded-2xl border border-amber-900/10 shadow-2xs">
                <!-- Sleek Header with Inline DOB Finder Toggle -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pb-2 mb-2 border-b border-amber-900/10">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container text-lg sm:text-xl">cyclone</span>
                    <h2 class="font-serif text-sm sm:text-base font-bold text-primary leading-none">Daily Horoscope (दैनिक राशिफल)</h2>
                    <span class="text-[10px] text-tertiary font-semibold bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded-full">
                      Today: <?php echo date('d M'); ?>
                    </span>
                  </div>
                  
                  <button type="button" id="mr-toggle-dob-btn" class="inline-flex items-center gap-1 text-[11px] font-bold text-tertiary hover:text-primary transition-colors self-start sm:self-auto">
                    <span class="material-symbols-outlined text-xs sm:text-sm">search</span>
                    <span>Don't know your Rashi? Enter DOB ▾</span>
                  </button>
                </div>

                <!-- 12 Compact Zodiac Pills: 12-Col Desktop (Single 36px Row!) / 6-Col Tablet / 4x3 Micro-Pills Mobile -->
                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-12 gap-1 sm:gap-1.5">
                  <?php foreach ($zodiacs as $z): ?>
                    <a href="<?php echo esc_url(home_url('/horoscope/' . $z['slug'] . '/')); ?>"
                       class="zodiac-pill p-1 sm:p-1.5 rounded-lg flex items-center justify-center gap-1 text-center group">
                      <span class="text-primary-container font-bold text-xs sm:text-sm group-hover:scale-110 transition-transform"><?php echo esc_html($z['symbol']); ?></span>
                      <span class="font-bold text-[10px] sm:text-xs text-on-surface group-hover:text-primary leading-none"><?php echo esc_html($z['hi']); ?></span>
                      <span class="hidden xl:inline text-[9px] text-on-surface-variant font-medium leading-none"><?php echo esc_html(substr($z['name'], 0, 3)); ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>

            <!-- Life Problem Solution Gateway Pills (2-Col Mobile / 5-Col Desktop) -->
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-12 py-1.5">
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-1.5 sm:gap-2">
                <a href="<?php echo esc_url(home_url('/dasha/')); ?>" class="gateway-chip p-2 rounded-xl flex items-center gap-2">
                  <span class="text-lg shrink-0">💼</span>
                  <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-on-surface leading-tight truncate">Career &amp; Growth</span>
                    <span class="block text-[9px] text-on-surface-variant truncate">Dasha &amp; Varshaphal</span>
                  </div>
                </a>
                <a href="<?php echo esc_url(home_url('/matchmaking-compatibility-check/')); ?>" class="gateway-chip p-2 rounded-xl flex items-center gap-2">
                  <span class="text-lg shrink-0">💍</span>
                  <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-on-surface leading-tight truncate">Vivah &amp; Milan</span>
                    <span class="block text-[9px] text-on-surface-variant truncate">36 Gun Matchmaking</span>
                  </div>
                </a>
                <a href="<?php echo esc_url(home_url('/muhurat-dates/')); ?>" class="gateway-chip p-2 rounded-xl flex items-center gap-2">
                  <span class="text-lg shrink-0">💰</span>
                  <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-on-surface leading-tight truncate">Shubh Muhurat</span>
                    <span class="block text-[9px] text-on-surface-variant truncate">Choghadiya &amp; Dates</span>
                  </div>
                </a>
                <a href="<?php echo esc_url(home_url('/dosh/')); ?>" class="gateway-chip p-2 rounded-xl flex items-center gap-2">
                  <span class="text-lg shrink-0">🪐</span>
                  <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-on-surface leading-tight truncate">Shani &amp; Dosha</span>
                    <span class="block text-[9px] text-on-surface-variant truncate">Manglik &amp; Sade Sati</span>
                  </div>
                </a>
                <a href="<?php echo esc_url(home_url('/baby-names/')); ?>" class="gateway-chip p-2 rounded-xl flex items-center gap-2 col-span-2 sm:col-span-1">
                  <span class="text-lg shrink-0">👶</span>
                  <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-on-surface leading-tight truncate">Baby Names</span>
                    <span class="block text-[9px] text-on-surface-variant truncate">50k+ Nakshatra Names</span>
                  </div>
                </a>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 3. FIND YOUR SUN SIGN CALCULATOR (Cosmic Charm)
         * ========================================================== */
        case 'sunsign_calc':
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-4">
              <div class="bg-gradient-to-br from-amber-50 via-orange-50/70 to-amber-100/60 border border-amber-300/70 rounded-2xl p-5 sm:p-7 shadow-sm relative overflow-hidden">
                <div class="max-w-3xl mx-auto relative z-10">
                  <div class="text-center mb-5">
                    <div class="inline-flex items-center gap-1.5 text-primary mb-1">
                      <span class="material-symbols-outlined text-xl text-primary-container">auto_awesome</span>
                      <h3 class="font-serif text-xl sm:text-2xl font-bold text-primary">Find Your Vedic Sun Sign &amp; Rashi (अपनी राशि जानें)</h3>
                    </div>
                    <p class="text-xs sm:text-sm text-on-surface-variant max-w-xl mx-auto">
                      Enter your Date of Birth to reveal your authentic Vedic Sun Sign, ruling planet, element &amp; personality traits.
                    </p>
                  </div>

                  <div class="bg-white border border-amber-900/15 rounded-xl p-3 sm:p-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                      <div class="flex items-center justify-center bg-amber-50 border border-amber-200/80 rounded-lg p-1 shrink-0">
                        <button type="button" class="mr-z-lang-btn px-3 py-1.5 rounded-md text-xs font-bold bg-primary-container text-white transition-all shadow-xs" data-lang="en">EN</button>
                        <button type="button" class="mr-z-lang-btn px-3 py-1.5 rounded-md text-xs font-bold text-on-surface hover:text-primary transition-all" data-lang="hi">हिंदी</button>
                      </div>

                      <div class="flex-1 relative">
                        <input type="date" id="mr-zodiac-dob" 
                               value="1995-05-15"
                               min="1920-01-01"
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full h-11 px-3.5 bg-amber-50/40 border border-amber-200 rounded-lg text-base sm:text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary-container focus:bg-white transition-all font-semibold" />
                      </div>

                      <input type="hidden" id="mr_zodiac_nonce_field" value="<?php echo esc_attr(wp_create_nonce('hp_zodiac_nonce')); ?>" />

                      <button type="button" id="mr-zodiac-submit-btn" 
                              class="h-11 px-6 bg-primary-container hover:bg-orange-600 active:scale-95 text-white font-bold text-xs sm:text-sm rounded-lg shadow-sm flex items-center justify-center gap-2 transition-all shrink-0 golden-halo">
                        <span class="material-symbols-outlined text-lg">explore</span>
                        <span>Find My Sun Sign</span>
                      </button>
                    </div>

                    <div id="mr-zodiac-result" class="hidden"></div>
                  </div>
                </div>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 4. 3-COLUMN CORE HERO ENGAGEMENT GRID
         * ========================================================== */
        case 'hero_dashboard':
            $panchang_title = "Today's Panchang";
            $kundali_title  = "Free Janam Kundali";
            $niyati_title   = "Niyati AI Astrologer";
            ?>
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-12 py-3 sm:py-5">
              <!-- MOBILE TAB SWITCHER: Visible only on mobile (<1024px) -->
              <div class="lg:hidden flex items-center justify-between bg-amber-200/50 p-1 rounded-2xl mb-3 border border-amber-300/60">
                <button type="button" class="hero-tab-btn active flex-1 py-2 text-xs font-bold rounded-xl transition-all text-center flex items-center justify-center gap-1" data-target="hero-panchang-col">
                  <span class="material-symbols-outlined text-sm">calendar_today</span>
                  <span>Panchang</span>
                </button>
                <button type="button" class="hero-tab-btn flex-1 py-2 text-xs font-bold text-on-surface rounded-xl transition-all text-center flex items-center justify-center gap-1" data-target="hero-kundali-col">
                  <span class="material-symbols-outlined text-sm">hub</span>
                  <span>Kundali</span>
                </button>
                <button type="button" class="hero-tab-btn flex-1 py-2 text-xs font-bold text-on-surface rounded-xl transition-all text-center flex items-center justify-center gap-1" data-target="hero-niyati-col">
                  <span class="material-symbols-outlined text-sm">psychology</span>
                  <span>Niyati AI</span>
                </button>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                <!-- 1. PANCHANG CARD (Col 1 - 4 Cols) -->
                <div id="hero-panchang-col" class="hero-column-panel lg:col-span-4 golden-card p-4 sm:p-6 flex flex-col justify-between spiritual-glow relative block">
                  <div>
                    <div class="flex items-center justify-between pb-3 border-b border-amber-900/10 mb-4">
                      <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container text-xl">calendar_today</span>
                        <h2 class="font-serif font-bold text-lg text-primary leading-tight"><?php echo esc_html($panchang_title); ?></h2>
                      </div>
                      <a href="<?php echo esc_url(home_url('/panchang/')); ?>" class="text-xs font-semibold text-tertiary hover:text-primary transition-colors">Choghadiya →</a>
                    </div>

                    <div class="text-xs text-on-surface-variant mb-4">
                      <span class="font-bold text-secondary"><?php echo esc_html($tithi_display); ?></span> • Drik Ganita Astrometry
                    </div>

                    <!-- Auspicious Muhurat Indicator -->
                    <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200/80 mb-3 flex items-start justify-between">
                      <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                          <span class="font-bold text-xs text-emerald-950">Abhijit Muhurat</span>
                        </div>
                        <p class="text-sm font-bold text-primary"><?php echo esc_html($abhijit_display); ?></p>
                        <p class="text-[11px] text-emerald-800">Ideal for contracts, auspicious ventures &amp; rituals</p>
                      </div>
                      <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-300">
                        Most Auspicious
                      </span>
                    </div>

                    <!-- Inauspicious Muhurat Warning -->
                    <div class="p-3.5 rounded-xl bg-red-50/80 border border-red-200/80 mb-4 flex items-start justify-between">
                      <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                          <span class="font-bold text-xs text-red-950">Rahukaal</span>
                        </div>
                        <p class="text-sm font-bold text-secondary"><?php echo esc_html($rahukaal_display); ?></p>
                        <p class="text-[11px] text-red-800">Inauspicious planetary hour; halt major beginnings</p>
                      </div>
                      <span class="bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-300">
                        Avoid Beginnings
                      </span>
                    </div>

                    <!-- Micro Shastric Panchang Metrics (2x2 Grid) -->
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-amber-900/10 pt-3">
                      <div class="bg-amber-50/60 p-2.5 rounded-lg border border-amber-900/5">
                        <span class="text-on-surface-variant block text-[11px] font-medium">Active Nakshatra</span>
                        <strong class="text-on-surface text-xs block font-bold"><?php echo esc_html($nakshatra_display); ?></strong>
                        <span class="text-[10px] text-tertiary">Lord: <?php echo esc_html($nakshatra_lord); ?></span>
                      </div>
                      <div class="bg-amber-50/60 p-2.5 rounded-lg border border-amber-900/5">
                        <span class="text-on-surface-variant block text-[11px] font-medium">Yoga &amp; Karana</span>
                        <strong class="text-on-surface text-xs block font-bold"><?php echo esc_html($yoga_display); ?> / <?php echo esc_html($karana_display); ?></strong>
                        <span class="text-[10px] text-tertiary">Anandadi: Siddha</span>
                      </div>
                      <div class="bg-amber-50/60 p-2.5 rounded-lg border border-amber-900/5">
                        <span class="text-on-surface-variant block text-[11px] font-medium">Sun Sign (Surya)</span>
                        <strong class="text-on-surface text-xs block font-bold"><?php echo esc_html($sun_zodiac); ?></strong>
                      </div>
                      <div class="bg-amber-50/60 p-2.5 rounded-lg border border-amber-900/5">
                        <span class="text-on-surface-variant block text-[11px] font-medium">Moon Sign (Chandra)</span>
                        <strong class="text-on-surface text-xs block font-bold"><?php echo esc_html($moon_zodiac); ?></strong>
                      </div>
                    </div>
                  </div>

                  <div class="pt-4 mt-4 border-t border-amber-900/10">
                    <a href="<?php echo esc_url(home_url('/panchang/')); ?>" 
                       class="w-full py-2.5 px-4 rounded-xl border border-tertiary-container text-tertiary hover:bg-tertiary/10 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                      <span>View Complete Panchang Details</span>
                      <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                  </div>
                </div>

                <!-- 2. FREE INSTANT JANAM KUNDALI GENERATOR (Col 2 - 4 Cols) -->
                <div id="hero-kundali-col" class="hero-column-panel lg:col-span-4 golden-card p-4 sm:p-6 flex flex-col justify-between relative hidden lg:flex">
                  <div>
                    <div class="flex items-center justify-between pb-3 border-b border-amber-900/10 mb-3">
                      <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container text-xl">hub</span>
                        <h2 class="font-serif font-bold text-lg text-primary leading-tight"><?php echo esc_html($kundali_title); ?></h2>
                      </div>
                      <span class="text-[10px] font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full border border-amber-300">Vedic D-1 &amp; D-9</span>
                    </div>

                    <p class="text-xs text-on-surface-variant mb-4 leading-relaxed">
                      Generate your precision birth chart, planetary degrees &amp; current Vimshottari Mahadasha instantly.
                    </p>

                    <form action="<?php echo esc_url(home_url('/kundali/')); ?>" method="GET" class="space-y-3 text-xs">
                      <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Full Name</label>
                        <input type="text" name="name" placeholder="e.g. Rahul Sharma" required
                               class="w-full h-9 px-3 bg-amber-50/40 border border-amber-200/80 rounded-lg text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container font-medium" />
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Date of Birth</label>
                          <input type="date" name="dob" value="1998-08-15" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" required
                                 class="w-full h-11 sm:h-9 px-2 bg-amber-50/40 border border-amber-200/80 rounded-lg text-base sm:text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container font-semibold" />
                        </div>
                        <div>
                          <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Time of Birth</label>
                          <input type="time" name="tob" value="10:30" required
                                 class="w-full h-11 sm:h-9 px-2 bg-amber-50/40 border border-amber-200/80 rounded-lg text-base sm:text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container font-semibold" />
                        </div>
                      </div>
                      <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Place of Birth (City / Coordinates)</label>
                        <div class="relative">
                          <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-sm">location_on</span>
                          <input type="text" name="pob" placeholder="e.g. New Delhi, India" required
                                 class="w-full h-9 pl-8 pr-3 bg-amber-50/40 border border-amber-200/80 rounded-lg text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container font-medium" />
                        </div>
                      </div>
                      <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Gender</label>
                        <div class="grid grid-cols-3 gap-2">
                          <label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-primary-container bg-amber-50/80 rounded-lg cursor-pointer text-primary font-bold">
                            <input checked="" class="hidden" name="gender" type="radio" value="male" />
                            <span>Male</span>
                          </label>
                          <label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-amber-200 bg-white rounded-lg cursor-pointer text-on-surface hover:bg-amber-50">
                            <input class="hidden" name="gender" type="radio" value="female" />
                            <span>Female</span>
                          </label>
                          <label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-amber-200 bg-white rounded-lg cursor-pointer text-on-surface hover:bg-amber-50">
                            <input class="hidden" name="gender" type="radio" value="other" />
                            <span>Other</span>
                          </label>
                        </div>
                      </div>

                      <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl bg-primary-container hover:bg-orange-600 text-white font-bold text-xs shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2 golden-halo">
                          <span>Generate Free Kundali</span>
                          <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                      </div>
                    </form>
                  </div>

                  <div class="pt-3 mt-3 border-t border-amber-900/10 text-center">
                    <p class="text-[11px] text-on-surface-variant/80 flex items-center justify-center gap-1">
                      <span class="material-symbols-outlined text-xs text-tertiary">verified</span>
                      <span>Computed with Swiss Ephemeris &amp; Lahiri Ayanamsha.</span>
                    </p>
                  </div>
                </div>

                <!-- 3. NIYATI AI VEDIC ASTROLOGER (Col 3 - 4 Cols) -->
                <div id="hero-niyati-col" class="hero-column-panel lg:col-span-4 golden-card p-4 sm:p-6 flex flex-col justify-between relative overflow-hidden hidden lg:flex">
                  <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary-container/15 rounded-full blur-2xl pointer-events-none"></div>

                  <div>
                    <div class="flex items-center gap-3 pb-3 border-b border-amber-900/10 mb-3">
                      <div class="relative">
                        <div class="w-13 h-13 rounded-full bg-gradient-to-tr from-amber-700 via-primary to-orange-500 flex items-center justify-center text-white ring-2 ring-tertiary-container shadow-md">
                          <span class="material-symbols-outlined text-2xl">psychology</span>
                        </div>
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                      </div>
                      <div>
                        <div class="flex items-center gap-1.5">
                          <h2 class="font-serif font-bold text-lg text-primary leading-tight"><?php echo esc_html($niyati_title); ?></h2>
                          <span class="material-symbols-outlined text-tertiary text-sm">auto_awesome</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] text-emerald-800 font-bold">
                          Online Now • Shastric Vedic Guide
                        </span>
                      </div>
                    </div>

                    <p class="text-xs text-on-surface-variant mb-3 leading-relaxed">
                      Instant shastric answers to your career, relationship, and planetary dosha questions backed by Parashara Hora Shastra.
                    </p>

                    <div class="space-y-1.5 mb-3.5">
                      <span class="text-[11px] font-bold text-tertiary block">Frequently Asked Inquiries:</span>
                      <div class="flex flex-col gap-1.5 text-xs">
                        <button type="button" class="mr-niyati-chip text-left px-2.5 py-1.5 rounded-lg bg-amber-50/70 hover:bg-amber-100 text-on-surface border border-amber-900/10 transition-colors flex items-center justify-between group">
                          <span>When will my career transition happen?</span>
                          <span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
                        </button>
                        <button type="button" class="mr-niyati-chip text-left px-2.5 py-1.5 rounded-lg bg-amber-50/70 hover:bg-amber-100 text-on-surface border border-amber-900/10 transition-colors flex items-center justify-between group">
                          <span>Check Mangal Dosh in my chart</span>
                          <span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
                        </button>
                        <button type="button" class="mr-niyati-chip text-left px-2.5 py-1.5 rounded-lg bg-amber-50/70 hover:bg-amber-100 text-on-surface border border-amber-900/10 transition-colors flex items-center justify-between group">
                          <span>Which gemstone is auspicious for my Rashi?</span>
                          <span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
                        </button>
                        <button type="button" class="mr-niyati-chip text-left px-2.5 py-1.5 rounded-lg bg-amber-50/70 hover:bg-amber-100 text-on-surface border border-amber-900/10 transition-colors flex items-center justify-between group">
                          <span>Marriage prospects in 2026-2027</span>
                          <span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
                        </button>
                      </div>
                    </div>

                    <div class="relative">
                      <input id="mr-niyati-quick-input" class="w-full text-xs pl-3 pr-9 py-2 bg-amber-50/50 border border-amber-200 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1" placeholder="Ask Niyati any astrological question..." type="text"/>
                      <button type="button" id="mr-niyati-quick-send" aria-label="Send query" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 text-primary-container hover:text-primary">
                        <span class="material-symbols-outlined text-base">send</span>
                      </button>
                    </div>
                  </div>

                  <div class="pt-3 mt-3 border-t border-amber-900/10">
                    <button type="button" id="mr-niyati-hero-trigger"
                            class="w-full py-2.5 px-4 rounded-xl bg-secondary hover:bg-secondary-hover text-white font-bold text-xs shadow-sm hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2">
                      <span class="material-symbols-outlined text-sm">chat</span>
                      <span>Start Free AI Consultation</span>
                    </button>
                  </div>
                </div>

              </div>
            </section>
            <?php
            break;

        /* ==========================================================
        /* --------------------------------------------------
         * 5. UPCOMING VRATS & FESTIVALS (Drik Engine - Dynamic af_festivals)
         * -------------------------------------------------- */
        case 'festivals_calendar':
            $items_limit        = !empty($sec_data['items_count']) ? intval($sec_data['items_count']) : 4;
            $month_end_date     = date('Y-m-t', strtotime($today_str));
            $current_month_name = date('F Y', strtotime($today_str));
            $cta_text           = 'View ' . $current_month_name . ' Calendar';
            $cta_url            = !empty($sec_data['cta_url']) ? $sec_data['cta_url'] : home_url('/festivals/');

            // Dynamic current-month dates query from af_festivals
            $upcoming_festivals = $wpdb->get_results($wpdb->prepare(
                "SELECT id, festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event_category 
                 FROM af_festivals 
                 WHERE festival_date >= %s 
                   AND festival_date <= %s 
                   AND festival_eng_1 IS NOT NULL 
                   AND TRIM(festival_eng_1) != '' 
                 ORDER BY festival_date ASC 
                 LIMIT %d",
                $today_str,
                $month_end_date,
                $items_limit
            ), ARRAY_A);

            // If remaining days in current month have fewer than $items_limit, expand query across next 45 days
            if (empty($upcoming_festivals) || count($upcoming_festivals) < $items_limit) {
                $upcoming_festivals = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, festival_date, festival_eng_1, festival_hin_1, tithi_eng, tithi_hin, event_category 
                     FROM af_festivals 
                     WHERE festival_date >= %s 
                       AND festival_eng_1 IS NOT NULL 
                       AND TRIM(festival_eng_1) != '' 
                     ORDER BY festival_date ASC 
                     LIMIT %d",
                    $today_str,
                    $items_limit
                ), ARRAY_A);
            }
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-6" id="festivals">
              <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-amber-900/10 pb-3 mb-6">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary text-2xl">event_upcoming</span>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary">Upcoming Vrats, Fasts &amp; Sacred Festivals (आगामी व्रत एवं त्यौहार)</h2>
                  </div>
                  <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                    Calculated using authentic Drik Ganita planetary positions and Shastric Sunrise (Udaya Tithi) timings.
                  </p>
                </div>
                <div>
                  <a href="<?php echo esc_url($cta_url); ?>" class="inline-flex items-center gap-1 text-xs font-bold text-tertiary hover:text-primary transition-colors">
                    <span><?php echo esc_html($cta_text); ?></span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <?php foreach ($upcoming_festivals as $fest): 
                    $f_en     = !empty($fest['festival_eng_1']) ? $fest['festival_eng_1'] : ($fest['festival_name_en'] ?? '');
                    $f_hi     = !empty($fest['festival_hin_1']) ? $fest['festival_hin_1'] : ($fest['festival_name_hi'] ?? '');
                    $f_date   = !empty($fest['festival_date']) ? strtotime($fest['festival_date']) : time();
                    $f_cat    = strtolower($fest['event_category'] ?? '');
                    $tithi_hi = !empty($fest['tithi_hin']) ? $fest['tithi_hin'] : '';
                    $tithi_en = !empty($fest['tithi_eng']) ? $fest['tithi_eng'] : '';

                    $resolved   = MR_Homepage_Manager::get_festival_smart_link($f_en, $f_hi);
                    $dest_url   = $resolved['url'];
                    $is_article = ($resolved['type'] === 'article');

                    // Determine tier badge based on category
                    if ($f_cat === 'major') {
                        $tier_badge  = 'Featured Grand Parva';
                        $is_featured = true;
                    } elseif ($f_cat === 'ekadashi') {
                        $tier_badge  = 'Sacred Ekadashi Vrat';
                        $is_featured = true;
                    } elseif ($f_cat === 'pradosh') {
                        $tier_badge  = 'Pradosh Vrat';
                        $is_featured = false;
                    } elseif ($f_cat === 'fasting') {
                        $tier_badge  = 'Auspicious Vrat';
                        $is_featured = false;
                    } else {
                        $tier_badge  = 'Upcoming Parva';
                        $is_featured = false;
                    }
                ?>
                  <div class="golden-card p-5 flex flex-col justify-between relative <?php echo $is_featured ? 'border-2 border-primary-container ring-1 ring-primary-container/30' : ''; ?>">
                    <?php if ($is_featured): ?>
                      <span class="absolute -top-2.5 right-4 bg-primary-container text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs">
                        Featured Parva
                      </span>
                    <?php endif; ?>

                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900">
                          <?php echo esc_html($tier_badge); ?>
                        </span>
                        <span class="material-symbols-outlined text-tertiary text-sm">spa</span>
                      </div>

                      <h3 class="font-serif text-lg font-bold text-primary pt-1 leading-snug">
                        <?php echo esc_html($f_en); ?> <?php if ($f_hi): ?><span class="text-sm font-semibold text-secondary">(<?php echo esc_html($f_hi); ?>)</span><?php endif; ?>
                      </h3>

                      <div class="text-xs font-bold text-on-surface flex items-center gap-1.5 pt-0.5">
                        <span class="material-symbols-outlined text-xs text-primary-container">calendar_month</span>
                        <span><?php echo date('d M Y', $f_date); ?></span>
                        <?php if ($tithi_hi || $tithi_en): ?>
                          <span class="text-secondary font-semibold text-[11px]">• <?php echo esc_html($tithi_hi ?: $tithi_en); ?></span>
                        <?php endif; ?>
                      </div>

                      <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-3">
                        Auspicious puja muhurat, shastric fasting rules, and traditional katha for divine grace and planetary harmony.
                      </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-amber-900/10">
                      <a href="<?php echo esc_url($dest_url); ?>" 
                         class="w-full py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-colors <?php echo $is_article ? 'bg-primary-container hover:bg-orange-600 text-white shadow-xs' : 'border border-amber-900/20 hover:bg-amber-100/60 text-primary'; ?>">
                        <span><?php echo $is_article ? 'View Vrat Katha & Muhurat' : 'Explore Festival Muhurat'; ?></span>
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 6. VEDIC ASTROLOGY MASTER REPORTS (Flagship Store)
         * ========================================================== */
        case 'master_reports':
            $items_limit = !empty($sec_data['items_count']) ? intval($sec_data['items_count']) : 4;
            $cta_text    = 'Browse All Reports & Bundles';
            $cta_url     = !empty($sec_data['cta_url']) ? $sec_data['cta_url'] : home_url('/pricing/');

            $synced_reports = MR_Homepage_Manager::get_synced_reports();
            $reports_subset = array_slice($synced_reports, 0, $items_limit);
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-6" id="reports">
              <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-amber-900/10 pb-3 mb-6">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container text-2xl">menu_book</span>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary">Vedic Astrology Master Reports (वैदिक ज्योतिष मास्टर रिपोर्ट)</h2>
                  </div>
                  <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                    Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms and verified by traditional pandits.
                  </p>
                </div>
                <div>
                  <a href="<?php echo esc_url($cta_url); ?>" class="inline-flex items-center gap-1 text-xs font-bold text-tertiary hover:text-primary transition-colors">
                    <span><?php echo esc_html($cta_text); ?></span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>
              </div>

              <!-- 4-Column Product Showcase Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach ($reports_subset as $rep): 
                    $price_sale = number_format($rep['price_sale'], 0);
                    $price_reg  = number_format($rep['price_regular'], 0);
                    $discount   = $rep['discount_text'];
                ?>
                  <div class="golden-card p-5 flex flex-col justify-between relative group">
                    <div class="space-y-3">
                      <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded">
                          <?php echo esc_html($rep['pages_badge']); ?>
                        </span>
                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">
                          <?php echo esc_html($discount); ?>
                        </span>
                      </div>

                      <div class="h-36 bg-amber-50 rounded-xl border border-amber-900/10 overflow-hidden relative group-hover:shadow-inner transition-all">
                        <?php if (!empty($rep['image_url'])): ?>
                          <img src="<?php echo esc_url($rep['image_url']); ?>" 
                               alt="<?php echo esc_attr($rep['title']); ?>" 
                               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <?php else: ?>
                          <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-100 to-orange-100 text-primary">
                            <span class="material-symbols-outlined text-4xl">auto_stories</span>
                          </div>
                        <?php endif; ?>
                      </div>

                      <h3 class="font-serif font-bold text-base text-primary leading-snug">
                        <a href="<?php echo esc_url($rep['page_url']); ?>" class="hover:text-primary-container transition-colors">
                          <?php echo esc_html($rep['title']); ?>
                        </a>
                      </h3>

                      <ul class="text-xs text-on-surface-variant space-y-1">
                        <li class="flex items-center gap-1.5">
                          <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                          <span>Verified by Traditional Pandits</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                          <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                          <span>Instant PDF Delivery in 60s</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                          <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                          <span>Personalized Vedic Remedies</span>
                        </li>
                      </ul>

                      <div class="pt-2 border-t border-amber-900/10 flex items-baseline gap-2">
                        <span class="text-xl font-bold font-serif text-primary">₹<?php echo esc_html($price_sale); ?></span>
                        <span class="text-xs text-outline line-through">₹<?php echo esc_html($price_reg); ?></span>
                        <span class="text-[11px] text-tertiary font-bold ml-auto">Instant PDF</span>
                      </div>
                    </div>

                    <div class="mt-4 space-y-2">
                      <a href="<?php echo esc_url($rep['page_url']); ?>" 
                         class="w-full py-2.5 rounded-xl bg-primary-container hover:bg-orange-600 active:scale-95 text-white text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition-all golden-halo">
                        <span>Get Your Report Now</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 7. SACRED VEDIC BABY NAMES ENGINE (नामकरण)
         * ========================================================== */
        case 'baby_names':
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-6" id="baby-names">
              <div class="border-b border-amber-900/10 pb-3 mb-5">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-secondary text-2xl">child_care</span>
                  <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary">Sacred Vedic Baby Names Engine (शिशु नामकरण संस्कार)</h2>
                </div>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                  Discover auspicious boy &amp; girl names starting with your child's birth nakshatra syllables and numerological harmony.
                </p>
              </div>

              <div class="bg-gradient-to-r from-amber-50 via-pink-50/40 to-amber-50 border border-amber-200/80 rounded-2xl p-5 sm:p-6 shadow-sm">
                <form action="<?php echo esc_url(home_url('/baby-names/')); ?>" method="GET" class="space-y-4">
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                      <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Gender (लिंग)</label>
                      <select name="gender" class="w-full h-10 px-3 bg-white border border-amber-300 rounded-lg text-xs font-semibold text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container">
                        <option value="">All Genders (लड़के एवं लड़कियाँ)</option>
                        <option value="boy">Boy (लड़का - 👦)</option>
                        <option value="girl">Girl (लड़की - 👧)</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Starting Syllable (नक्षत्र नामाक्षर)</label>
                      <input type="text" name="letter" placeholder="e.g. A, R, Sh, Ra, V (अ, र, श)"
                             class="w-full h-10 px-3 bg-white border border-amber-300 rounded-lg text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary-container font-medium" />
                    </div>

                    <div class="flex items-end">
                      <button type="submit" 
                              class="w-full h-10 bg-primary-container hover:bg-orange-600 text-white font-bold text-xs rounded-lg shadow-sm flex items-center justify-center gap-2 transition-all golden-halo">
                        <span class="material-symbols-outlined text-base">search</span>
                        <span>Find Auspicious Names</span>
                      </button>
                    </div>
                  </div>

                  <div class="pt-2 flex flex-wrap items-center gap-1.5 text-xs text-on-surface-variant">
                    <span class="font-bold text-tertiary">Popular Syllables:</span>
                    <?php 
                    $syllables = ['अ (A)', 'र (R)', 'स (S)', 'म (M)', 'क (K)', 'व (V)', 'द (D)', 'प (P)'];
                    foreach ($syllables as $syl): ?>
                      <a href="<?php echo esc_url(home_url('/baby-names/?letter=' . urlencode(substr($syl, 0, 1)))); ?>" 
                         class="px-2.5 py-1 bg-white hover:bg-amber-100 border border-amber-200/70 rounded-md text-[11px] font-semibold text-primary transition-colors">
                        <?php echo esc_html($syl); ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </form>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 8. VEDIC CALCULATORS & SHASTRIC ENGINES (8 Free Tools)
         * ========================================================== */
        case 'vedic_calculators':
            $tools = MR_Homepage_Manager::get_calculators();
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-6" id="calculators">
              <div class="border-b border-amber-900/10 pb-3 mb-6">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-tertiary text-2xl">calculate</span>
                  <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary">Vedic Calculators &amp; Shastric Engines (वैदिक ज्योतिष कैलकुलेटर)</h2>
                </div>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                  Instant, ad-free computations based on ancient Brihat Parashara Hora Shastra and Surya Siddhanta principles.
                </p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($tools as $t): ?>
                  <a href="<?php echo esc_url($t['url']); ?>" 
                     class="golden-card p-4.5 flex flex-col justify-between group transition-all">
                    <div>
                      <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-primary-container mb-3 group-hover:scale-110 group-hover:bg-amber-100 transition-all">
                        <span class="material-symbols-outlined text-2xl"><?php echo esc_html($t['icon']); ?></span>
                      </div>
                      <h4 class="font-serif font-bold text-sm sm:text-base text-primary group-hover:text-primary-container transition-colors leading-tight">
                        <?php echo esc_html($t['title']); ?>
                      </h4>
                      <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed line-clamp-2">
                        <?php echo esc_html($t['description']); ?>
                      </p>
                    </div>
                    <div class="mt-4 pt-2.5 border-t border-amber-900/10 flex items-center gap-1 text-xs font-bold text-tertiary group-hover:text-primary group-hover:translate-x-1 transition-all">
                      <span><?php echo esc_html($t['btn_text']); ?></span>
                      <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 9. SANATAN DEVOTIONAL & SPIRITUAL CORNER
         * ========================================================== */
        case 'devotional_corner':
            $deities = MR_Homepage_Manager::get_deities();
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-6" id="devotional">
              <div class="border-b border-amber-900/10 pb-3 mb-6">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary-container text-2xl">temple_hindu</span>
                  <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary">Sanatan Devotional &amp; Spiritual Corner (सनातन भक्ति एवं अध्यात्म)</h2>
                </div>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                  Sacred stotras, authentic vrat kathas, and timeless arti sangrah for your daily puja anushthan.
                </p>
              </div>

              <!-- Subsection A: Hindu Deities Navigation (Horizontal Circular Avatar Bar) -->
              <!-- Subsection A: Hindu Deities Navigation (5-Col Mobile Grid, 10-Col Desktop - Zero Horizontal Scroll) -->
              <div class="bg-white p-3 sm:p-5 rounded-2xl border border-amber-900/10 shadow-xs mb-6">
                <div class="flex items-center justify-between mb-3 px-1">
                  <span class="text-xs font-bold text-on-surface">Choose Deity for Dedicated Aartis &amp; Stotras:</span>
                </div>
                <div class="grid grid-cols-5 sm:grid-cols-5 lg:grid-cols-10 gap-2 sm:gap-4 items-center">
                  <?php foreach ($deities as $d): ?>
                    <a href="<?php echo esc_url($d['url']); ?>" class="deity-circle-card flex flex-col items-center gap-1 group">
                      <div class="avatar-ring w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-amber-50 border-2 border-amber-300 p-0.5 shadow-xs transition-all">
                        <img src="<?php echo esc_url($d['image_url']); ?>" 
                             alt="<?php echo esc_attr($d['name']); ?>" 
                             class="w-full h-full object-cover rounded-full" />
                      </div>
                      <span class="font-bold text-[11px] sm:text-xs text-on-surface group-hover:text-primary transition-colors text-center truncate max-w-full"><?php echo esc_html($d['name']); ?></span>
                      <span class="text-[9px] sm:text-[10px] text-tertiary text-center"><?php echo esc_html($d['count']); ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Subsection B: Devotional Content & Media Grid -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="golden-card p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-amber-900/10 mb-3">
                      <span class="material-symbols-outlined text-primary text-xl">auto_stories</span>
                      <h3 class="font-serif font-bold text-base text-primary">Trending Vrat Kathas (प्रमुख व्रत कथाएं)</h3>
                    </div>
                    <div class="space-y-2.5">
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Satyanarayan Vrat Katha (सत्यनारायण कथा)</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
                          <span>Complete 5 Chapters with Vidhi</span>
                          <span class="text-tertiary font-semibold">18 min read</span>
                        </div>
                      </a>
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Rama Ekadashi Mahatmya (रमा एकादशी)</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
                          <span>Brahmavaivarta Purana extract</span>
                          <span class="text-tertiary font-semibold">10 min read</span>
                        </div>
                      </a>
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Dhanteras &amp; Diwali Puja Katha</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
                          <span>Lord Dhanvantari &amp; Lakshmi Puja</span>
                          <span class="text-tertiary font-semibold">12 min read</span>
                        </div>
                      </a>
                    </div>
                  </div>
                  <a class="mt-4 pt-3 border-t border-amber-900/10 text-xs font-bold text-primary flex items-center justify-between hover:text-primary-container transition-colors" href="<?php echo esc_url(home_url('/vrat-katha/')); ?>">
                    <span>Read All Sacred Kathas</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>

                <div class="golden-card p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-amber-900/10 mb-3">
                      <span class="material-symbols-outlined text-tertiary text-xl">library_music</span>
                      <h3 class="font-serif font-bold text-base text-primary">Daily Aarti Sangrah (दैनिक आरती संग्रह)</h3>
                    </div>
                    <div class="space-y-2.5">
                      <div class="p-2.5 rounded-xl bg-amber-50/60 flex items-center justify-between">
                        <div>
                          <div class="text-xs font-bold text-on-surface">Jai Ganesh Deva (जय गणेश देवा)</div>
                          <div class="text-[11px] text-outline">Evening Pradosh Aarti</div>
                        </div>
                        <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition shadow-xs">
                          <span class="material-symbols-outlined text-sm">play_arrow</span>
                        </a>
                      </div>
                      <div class="p-2.5 rounded-xl bg-amber-50/60 flex items-center justify-between">
                        <div>
                          <div class="text-xs font-bold text-on-surface">Om Jai Jagdish Hare (जगदीश हरे)</div>
                          <div class="text-[11px] text-outline">Universal Vishnu Aarti</div>
                        </div>
                        <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition shadow-xs">
                          <span class="material-symbols-outlined text-sm">play_arrow</span>
                        </a>
                      </div>
                      <div class="p-2.5 rounded-xl bg-amber-50/60 flex items-center justify-between">
                        <div>
                          <div class="text-xs font-bold text-on-surface">Shiv Aarti (जय शिव ओंकारा)</div>
                          <div class="text-[11px] text-outline">Kailash Stuti with damru</div>
                        </div>
                        <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition shadow-xs">
                          <span class="material-symbols-outlined text-sm">play_arrow</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <a class="mt-4 pt-3 border-t border-amber-900/10 text-xs font-bold text-tertiary flex items-center justify-between hover:text-primary transition-colors" href="<?php echo esc_url(home_url('/vrat-katha/')); ?>">
                    <span>Explore 108+ Aartis</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>

                <div class="golden-card p-5 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-amber-900/10 mb-3">
                      <span class="material-symbols-outlined text-secondary text-xl">graphic_eq</span>
                      <h3 class="font-serif font-bold text-base text-primary">Sacred Chants &amp; Mantras (पावन मंत्र एवं स्तोत्र)</h3>
                    </div>
                    <div class="space-y-2.5">
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Shree Hanuman Chalisa (108 आवर्तन)</div>
                        <div class="text-[11px] text-on-surface-variant mt-0.5">Swar Anushthan • Daily Mangal Protection</div>
                      </a>
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Mahamrityunjaya Mantra (महामृत्युंजय)</div>
                        <div class="text-[11px] text-on-surface-variant mt-0.5">Rigvedic 7.59.12 • Planetary Healing</div>
                      </a>
                      <a href="<?php echo esc_url(home_url('/vrat-katha/')); ?>" class="block p-2.5 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 transition">
                        <div class="text-xs font-bold text-on-surface">Gayatri Mantra (गायत्री महामंत्र)</div>
                        <div class="text-[11px] text-on-surface-variant mt-0.5">Surya Upasana • Divine Illumination</div>
                      </a>
                    </div>
                  </div>
                  <a class="mt-4 pt-3 border-t border-amber-900/10 text-xs font-bold text-secondary flex items-center justify-between hover:text-primary transition-colors" href="<?php echo esc_url(home_url('/vrat-katha/')); ?>">
                    <span>Listen to Chants &amp; Audio</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 10. CUSTOM ANNOUNCEMENT BANNER (If enabled by Admin)
         * ========================================================== */
        case 'custom_banner':
            $bg_color = !empty($sec_data['bg_color']) ? $sec_data['bg_color'] : '#78350F';
            $cta_text = !empty($sec_data['cta_text']) ? $sec_data['cta_text'] : 'Explore Sacred Offer';
            $cta_url  = !empty($sec_data['cta_url']) ? $sec_data['cta_url'] : home_url('/pricing/');
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-4">
              <div class="rounded-2xl p-6 sm:p-8 text-white shadow-md flex flex-col md:flex-row items-center justify-between gap-6"
                   style="background: <?php echo esc_attr($bg_color); ?>;">
                <div class="space-y-1.5 text-center md:text-left">
                  <h3 class="font-serif text-xl sm:text-2xl font-bold text-white"><?php echo esc_html($title); ?></h3>
                  <?php if (!empty($subtitle)): ?>
                    <p class="text-xs sm:text-sm text-amber-100/90 max-w-xl"><?php echo esc_html($subtitle); ?></p>
                  <?php endif; ?>
                </div>
                <div class="shrink-0">
                  <a href="<?php echo esc_url($cta_url); ?>" 
                     class="px-6 py-3 bg-white hover:bg-amber-50 text-amber-900 font-bold text-xs sm:text-sm rounded-xl shadow transition-all block text-center">
                    <?php echo esc_html($cta_text); ?>
                  </a>
                </div>
              </div>
            </section>
            <?php
            break;

        /* ==========================================================
         * 11. TRUST & AUTHORITY STRIP
         * ========================================================== */
        case 'trust_badges':
            ?>
            <section class="max-w-7xl mx-auto px-4 lg:px-12 py-8 border-t border-amber-900/10">
              <div class="text-center mb-6">
                <h3 class="font-serif text-xl sm:text-2xl font-bold text-primary"><?php echo esc_html($title); ?></h3>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-1"><?php echo esc_html($subtitle); ?></p>
              </div>

              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="p-4 bg-white border border-amber-900/10 rounded-xl shadow-xs">
                  <span class="material-symbols-outlined text-primary text-3xl mb-1.5">verified</span>
                  <h4 class="font-bold text-xs sm:text-sm text-on-surface">100% Shastric Drik Ganita</h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">Surya Siddhanta &amp; NASA JPL Ephemeris</p>
                </div>
                <div class="p-4 bg-white border border-amber-900/10 rounded-xl shadow-xs">
                  <span class="material-symbols-outlined text-primary-container text-3xl mb-1.5">bolt</span>
                  <h4 class="font-bold text-xs sm:text-sm text-on-surface">Instant PDF Delivery</h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">Delivered to WhatsApp &amp; Email in 60s</p>
                </div>
                <div class="p-4 bg-white border border-amber-900/10 rounded-xl shadow-xs">
                  <span class="material-symbols-outlined text-secondary text-3xl mb-1.5">lock</span>
                  <h4 class="font-bold text-xs sm:text-sm text-on-surface">100% Confidential &amp; Secure</h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">Razorpay &amp; 256-bit SSL Protection</p>
                </div>
                <div class="p-4 bg-white border border-amber-900/10 rounded-xl shadow-xs">
                  <span class="material-symbols-outlined text-tertiary-container text-3xl mb-1.5">star</span>
                  <h4 class="font-bold text-xs sm:text-sm text-on-surface">4.9/5 Star Rating</h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">From 120,000+ Satisfied Seekers</p>
                </div>
              </div>
            </section>
            <?php
            break;

    } // end switch
} // end foreach
?>

</div><!-- /.mr-homepage-master-wrap -->

<!-- Client-side Interactive Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. Sun Sign Calculator AJAX Logic (Snippet #67 & af_get_zodiac_details)
    const dobInput   = document.getElementById("mr-zodiac-dob");
    const submitBtn  = document.getElementById("mr-zodiac-submit-btn");
    const langBtns   = document.querySelectorAll(".mr-z-lang-btn");
    const resultArea = document.getElementById("mr-zodiac-result");
    const nonceField = document.getElementById("mr_zodiac_nonce_field");
    let currentLang  = "en";

    if (langBtns.length > 0) {
        langBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                langBtns.forEach(b => {
                    b.classList.remove("bg-primary-container", "text-white", "shadow-xs");
                    b.classList.add("text-on-surface");
                });
                this.classList.remove("text-on-surface");
                this.classList.add("bg-primary-container", "text-white", "shadow-xs");
                currentLang = this.getAttribute("data-lang");
                if (dobInput && dobInput.value && !resultArea.classList.contains("hidden")) {
                    fetchSunSign();
                }
            });
        });
    }

    const fetchSunSign = () => {
        if (!dobInput || !dobInput.value) {
            alert(currentLang === "hi" ? "कृपया अपनी जन्मतिथि चुनें" : "Please select your date of birth");
            return;
        }

        resultArea.classList.remove("hidden");
        resultArea.innerHTML = '<div class="p-5 text-center text-primary font-semibold text-xs flex items-center justify-center gap-2"><span class="w-3 h-3 rounded-full bg-primary-container animate-ping"></span>' + (currentLang === "hi" ? "✨ आपके नक्षत्र व सूर्य राशि का विश्लेषण हो रहा है..." : "✨ Analyzing your stars & planetary positions...") + '</div>';

        const formData = new FormData();
        formData.append("action", "af_get_zodiac_details");
        formData.append("dob", dobInput.value);
        formData.append("lang", currentLang);
        if (nonceField) {
            formData.append("hp_nonce", nonceField.value);
        }

        fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = data.data;
                html = html.split("https://merarashifal.com/horoscope/zodiac_sign/").join("<?php echo esc_url(home_url('/horoscope/')); ?>");
                resultArea.innerHTML = html;
            } else {
                resultArea.innerHTML = '<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium">' + data.data + '</div>';
            }
        })
        .catch(err => {
            console.error("Zodiac fetch error:", err);
            resultArea.innerHTML = '<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-medium">Failed to calculate zodiac sign. Please try again.</div>';
        });
    };

    if (submitBtn) {
        submitBtn.addEventListener("click", fetchSunSign);
    }
    if (dobInput) {
        dobInput.addEventListener("change", fetchSunSign);
    }

    // 2. Niyati AI Assistant Integrator
    function openNiyatiWithMessage(msg) {
        const launcher = document.querySelector("#niyati-launcher, .niyati-launcher, [aria-label*='Niyati'], #niyati-widget");
        if (launcher) {
            launcher.click();
            setTimeout(function() {
                const input = document.querySelector("#niyati-user-input, .niyati-input, input[placeholder*='Niyati']");
                if (input && msg) {
                    input.value = msg;
                    input.focus();
                }
            }, 300);
        }
    }

    // Attach to Niyati Question Chips
    document.querySelectorAll(".mr-niyati-chip").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const text = this.querySelector("span") ? this.querySelector("span").textContent.trim() : this.textContent.trim();
            openNiyatiWithMessage(text);
        });
    });

    const niyatiHeroTrigger = document.getElementById("mr-niyati-hero-trigger");
    if (niyatiHeroTrigger) {
        niyatiHeroTrigger.addEventListener("click", function(e) {
            e.preventDefault();
            openNiyatiWithMessage("Namaste Niyati, please analyze my Janam Kundali.");
        });
    }

    const niyatiQuickInput = document.getElementById("mr-niyati-quick-input");
    const niyatiQuickSend  = document.getElementById("mr-niyati-quick-send");
    if (niyatiQuickSend && niyatiQuickInput) {
        niyatiQuickSend.addEventListener("click", function() {
            const val = niyatiQuickInput.value.trim();
            if (val) {
                openNiyatiWithMessage(val);
                niyatiQuickInput.value = "";
            }
        });
        niyatiQuickInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const val = niyatiQuickInput.value.trim();
                if (val) {
                    openNiyatiWithMessage(val);
                    niyatiQuickInput.value = "";
                }
            }
        });
    }

    // 3. Mobile Hero Tab Switcher (Zero Overhead)
    const tabButtons = document.querySelectorAll(".hero-tab-btn");
    const panels = document.querySelectorAll(".hero-column-panel");

    tabButtons.forEach(function(btn) {
        btn.addEventListener("click", function() {
            const targetId = this.getAttribute("data-target");

            tabButtons.forEach(function(b) {
                b.classList.remove("active");
                b.classList.add("text-on-surface");
            });
            this.classList.add("active");
            this.classList.remove("text-on-surface");

            panels.forEach(function(p) {
                if (p.id === targetId) {
                    p.classList.remove("hidden");
                    p.classList.add("block");
                } else {
                    p.classList.add("hidden");
                    p.classList.remove("block");
                }
            });
        });
    });
});
</script>

<?php
// Call the official theme footer (Elementor / Astra Footer)
get_footer();