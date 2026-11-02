<?php
/**
 * Template Name: Stitch New Home
 * Description: High-converting authentic Vedic astrology portal homepage designed with Stitch & Tailwind.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fetch live Panchang data for today (fallback gracefully to Shastric astronomical calculation)
global $wpdb;
$today_str = current_time('Y-m-d');
$live_panchang = $wpdb->get_row($wpdb->prepare("SELECT * FROM af_panchang_data WHERE api_date = %s LIMIT 1", $today_str));
if (!$live_panchang) {
    $live_panchang = $wpdb->get_row("SELECT * FROM af_panchang_data ORDER BY api_date DESC LIMIT 1");
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts: Playfair Display & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS with Forms & Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "surface-bright": "#fff8f6",
            "primary": "#964900",
            "on-tertiary": "#ffffff",
            "error-container": "#ffdad6",
            "primary-fixed-dim": "#ffb786",
            "secondary-fixed": "#ffd9df",
            "on-error-container": "#93000a",
            "on-tertiary-container": "#4b3300",
            "error": "#ba1a1a",
            "surface": "#fff8f6",
            "on-primary-container": "#5b2a00",
            "surface-container-high": "#f5e5df",
            "inverse-primary": "#ffb786",
            "tertiary": "#7d5700",
            "on-error": "#ffffff",
            "tertiary-container": "#cb9836",
            "on-tertiary-fixed": "#271900",
            "surface-variant": "#efdfd9",
            "on-secondary": "#ffffff",
            "on-surface-variant": "#564336",
            "on-primary-fixed": "#311300",
            "inverse-on-surface": "#feeee7",
            "outline": "#8a7264",
            "primary-container": "#f58220",
            "tertiary-fixed": "#ffdeaa",
            "surface-container-lowest": "#ffffff",
            "surface-tint": "#964900",
            "on-surface": "#221a16",
            "secondary": "#9f3c59",
            "background": "#fff8f6",
            "primary-fixed": "#ffdcc6",
            "on-primary-fixed-variant": "#723600",
            "secondary-container": "#fd86a4",
            "inverse-surface": "#372f2b",
            "on-primary": "#ffffff",
            "on-secondary-container": "#761c3a",
            "on-tertiary-fixed-variant": "#5f4100",
            "surface-container": "#fbebe4",
            "on-secondary-fixed": "#3f0018",
            "tertiary-fixed-dim": "#f5bd58",
            "on-background": "#221a16",
            "on-secondary-fixed-variant": "#802442",
            "outline-variant": "#ddc1b0",
            "surface-dim": "#e6d7d1",
            "surface-container-low": "#fff1eb",
            "surface-container-highest": "#efdfd9",
            "secondary-fixed-dim": "#ffb1c2"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "title-lg": ["Inter", "sans-serif"],
            "headline-sm": ["Playfair Display", "serif"],
            "label-lg": ["Inter", "sans-serif"],
            "label-sm": ["Inter", "sans-serif"],
            "label-md": ["Inter", "sans-serif"],
            "headline-lg": ["Playfair Display", "serif"],
            "headline-lg-mobile": ["Playfair Display", "serif"],
            "headline-md": ["Playfair Display", "serif"],
            "display-lg-mobile": ["Playfair Display", "serif"],
            "body-md": ["Inter", "sans-serif"],
            "body-sm": ["Inter", "sans-serif"],
            "display-lg": ["Playfair Display", "serif"],
            "title-md": ["Inter", "sans-serif"],
            "body-lg": ["Inter", "sans-serif"]
          },
          "fontSize": {
            "title-lg": ["18px", { "lineHeight": "26px", "fontWeight": "600" }],
            "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
            "label-lg": ["14px", { "lineHeight": "20px", "letterSpacing": "0.02em", "fontWeight": "600" }],
            "label-sm": ["10px", { "lineHeight": "14px", "letterSpacing": "0.06em", "fontWeight": "700" }],
            "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "600" }],
            "headline-lg": ["32px", { "lineHeight": "40px", "fontWeight": "600" }],
            "headline-lg-mobile": ["26px", { "lineHeight": "34px", "fontWeight": "600" }],
            "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
            "display-lg-mobile": ["34px", { "lineHeight": "42px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "body-md": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
            "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }],
            "display-lg": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "title-md": ["16px", { "lineHeight": "24px", "fontWeight": "600" }],
            "body-lg": ["16px", { "lineHeight": "26px", "fontWeight": "400" }]
          }
        },
      },
    }
    </script>
    <style>
        .material-symbols-outlined {
          font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
          display: inline-block;
          vertical-align: middle;
          line-height: 1;
        }
        .custom-scroll::-webkit-scrollbar {
          height: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
          background: #ddc1b0;
          border-radius: 4px;
        }
        .spiritual-glow {
          background: radial-gradient(ellipse at top, rgba(245, 130, 32, 0.08) 0%, rgba(253, 248, 241, 0) 70%);
        }
        .golden-halo:hover {
          box-shadow: 0 10px 25px -4px rgba(245, 130, 32, 0.28), 0 0 0 1px rgba(203, 152, 54, 0.35);
        }
        .custom-scroll {
          -webkit-overflow-scrolling: touch;
        }
    
        /* Custom Theming for Baby Name Compact Search (Snippet #74 / #75) */
        .baby-premium-strip-wrap {
          background: linear-gradient(135deg, #964900 0%, #7d5700 60%, #5b2a00 100%) !important;
          border: 1px solid rgba(245, 130, 32, 0.4) !important;
          border-radius: 0.75rem !important;
          padding: 1.25rem 1.5rem !important;
          margin-bottom: 2rem !important;
          box-shadow: 0 4px 18px -2px rgba(150, 73, 0, 0.22) !important;
        }
        .bps-title {
          color: #ffdeaa !important;
          font-family: "Playfair Display", serif !important;
          font-size: 1.35rem !important;
          font-weight: 700 !important;
        }
        .bps-subtitle {
          color: #fff1eb !important;
          font-family: "Inter", sans-serif !important;
          font-size: 0.85rem !important;
          opacity: 0.9 !important;
        }
        .bps-form input, .bps-form select {
          background: #ffffff !important;
          border: 1px solid #ddc1b0 !important;
          border-radius: 0.5rem !important;
          color: #221a16 !important;
          font-size: 0.8rem !important;
          padding: 0.5rem 0.75rem !important;
        }
        .bps-btn-field button {
          background: #f58220 !important;
          color: #ffffff !important;
          border: none !important;
          border-radius: 0.5rem !important;
          font-weight: 600 !important;
          padding: 0.55rem 1.25rem !important;
          cursor: pointer !important;
          transition: all 0.2s !important;
        }
        .bps-btn-field button:hover {
          filter: brightness(1.1) !important;
          transform: translateY(-1px) !important;
        }
        
        /* Sun Sign Result Card Theming */
        .af-zodiac-result-card {
          background: #fff8f6 !important;
          border: 1px solid rgba(245, 130, 32, 0.3) !important;
          border-radius: 0.75rem !important;
          padding: 1.5rem !important;
          box-shadow: 0 4px 12px rgba(150, 73, 0, 0.08) !important;
        }
        .af-zodiac-header h4 {
          color: #964900 !important;
          font-family: "Playfair Display", serif !important;
          font-size: 1.4rem !important;
          font-weight: 700 !important;
        }
        .af-zodiac-header h4 span {
          color: #f58220 !important;
          font-weight: 800 !important;
        }
        .af-zodiac-desc {
          color: #564336 !important;
          font-size: 0.875rem !important;
          line-height: 1.65 !important;
          margin-top: 0.5rem !important;
        }
        .af-zodiac-details-grid {
          display: grid !important;
          grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
          gap: 0.75rem !important;
          margin: 1.25rem 0 !important;
        }
        .af-zodiac-item {
          background: #ffffff !important;
          border: 1px solid #efdfd9 !important;
          border-left: 4px solid #f58220 !important;
          border-radius: 0.5rem !important;
          padding: 0.75rem 1rem !important;
        }
        .af-z-label {
          font-size: 0.7rem !important;
          font-weight: 700 !important;
          color: #7d5700 !important;
          text-transform: uppercase !important;
          letter-spacing: 0.05em !important;
        }
        .af-z-value {
          font-size: 1rem !important;
          font-weight: 700 !important;
          color: #964900 !important;
          margin-top: 0.25rem !important;
        }
        .af-btn.zodiac-btn {
          display: inline-flex !important;
          align-items: center !important;
          gap: 0.5rem !important;
          background: #f58220 !important;
          color: #ffffff !important;
          padding: 0.6rem 1.5rem !important;
          border-radius: 0.5rem !important;
          font-weight: 600 !important;
          font-size: 0.85rem !important;
          text-decoration: none !important;
          transition: all 0.2s !important;
        }
        .af-btn.zodiac-btn:hover {
          filter: brightness(1.1) !important;
        }

        /* Mobile Intelligent Overrides */
        @media (max-width: 768px) {
          /* Hide intrusive Niyati speech bubble tooltip on mobile */
          .niyati-nudge-bubble {
            display: none !important;
          }
          /* Position Niyati launcher safely above the bottom menu */
          .niyati-launcher-btn {
            bottom: 74px !important;
            right: 14px !important;
            width: 50px !important;
            height: 50px !important;
            min-width: 50px !important;
            min-height: 50px !important;
            box-shadow: 0 4px 16px rgba(245, 130, 32, 0.42) !important;
          }
          .niyati-launcher-avatar {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
          }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body class="bg-background text-on-surface antialiased font-body-md selection:bg-tertiary-fixed selection:text-on-tertiary-fixed pb-24 md:pb-0">

<!-- 1. TOP ASTRONOMICAL STATUS BAR -->
<div class="bg-surface-container-high border-b border-outline-variant/30 text-on-surface-variant font-label-md py-1.5 px-3 lg:px-12 text-xs">
  <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 overflow-hidden">

    <!-- Mobile View: Clean, single-line condensed astrological status -->
    <div class="flex md:hidden items-center justify-between w-full text-[11px] font-medium">
      <span class="inline-flex items-center gap-1.5 text-primary truncate max-w-[58%]">
        <span class="w-1.5 h-1.5 rounded-full bg-primary-container animate-pulse shrink-0"></span>
        <strong class="text-primary font-semibold">VS 2083</strong>
        <span class="text-outline-variant">|</span>
        <span class="text-secondary truncate">कार्तिक शुक्ल दशमी</span>
      </span>
      <span class="inline-flex items-center gap-2 text-on-surface-variant shrink-0 text-[10px]">
        <span>☀️ 06:34 AM</span>
        <span>🌙 01:14 PM</span>
      </span>
    </div>

    <!-- Desktop View: Full Astronomical Reference -->
    <div class="hidden md:flex items-center space-x-3 overflow-x-auto whitespace-nowrap py-0.5">
      <span class="inline-flex items-center gap-1.5 font-label-md text-primary">
        <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
        Vikram Samvat 2083 (Pingala)
      </span>
      <span class="text-outline-variant">|</span>
      <span class="text-on-surface-variant">Saka 1948</span>
      <span class="text-outline-variant">|</span>
      <span class="font-label-md text-secondary">Kartik Shukla Dashami</span>
      <span class="text-on-surface-variant text-[11px]">(Ends 04:22 PM)</span>
    </div>

    <div class="hidden md:flex items-center space-x-4 overflow-x-auto whitespace-nowrap text-on-surface-variant py-0.5">
      <span class="inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-tertiary text-sm">wb_sunny</span>
        Sunrise: <strong class="text-on-surface font-label-md">06:34 AM</strong>
      </span>
      <span class="inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-primary text-sm">wb_twilight</span>
        Sunset: <strong class="text-on-surface font-label-md">05:42 PM</strong>
      </span>
      <span class="inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-tertiary text-sm">bedtime</span>
        Moonrise: <strong class="text-on-surface font-label-md">01:14 PM</strong>
      </span>
      <span class="inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-outline text-sm">nights_stay</span>
        Moonset: <strong class="text-on-surface font-label-md">01:38 AM</strong>
      </span>
      <span class="hidden xl:inline-flex items-center gap-1 bg-surface-container-lowest px-2 py-0.5 rounded border border-outline-variant/40 text-tertiary font-label-sm text-label-sm">
        <span class="material-symbols-outlined text-xs">tune</span>
        Live Ephemeris: Ujjain Reference 23.17° N
      </span>
    </div>

  </div>
</div>
<!-- 2. TOP NAVIGATION BAR (TopNavBar Shared Component Execution) -->
<header class="docked full-width top-0 sticky z-50 bg-surface border-b border-outline-variant/30 shadow-sm transition-colors duration-200">
<div class="w-full px-6 lg:px-12 max-w-7xl mx-auto flex items-center justify-between h-16 lg:h-20">
<!-- Brand Logo & Sacred Anchor -->
<div class="flex items-center gap-3">
<a class="flex items-center gap-2.5 group" href="http://localhost/merarashifal/">
<img alt="MeraRashifal Sacred Brand Logo" class="h-10 lg:h-12 w-auto object-contain transition-transform duration-200 group-hover:scale-102" src="<?php echo esc_url( content_url( '/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png' ) ); ?>"/>
<div class="flex flex-col">
<span class="font-headline-md text-headline-md font-bold text-primary tracking-tight leading-none">MeraRashifal</span>
<span class="text-[10px] tracking-widest uppercase font-label-sm text-tertiary mt-0.5">Vedic Celestial Portal</span>
</div>
</a>
</div>
<!-- Navigation Links (Desktop) -->
<nav class="hidden xl:flex items-center gap-6">
<a class="text-primary font-title-md text-title-md border-b-2 border-primary-container pb-1 transition-colors duration-200" href="http://localhost/merarashifal/panchang/"><a class="hover:text-primary transition" href="http://localhost/merarashifal/panchang/">Today's Panchang</a></a>
<a class="text-on-surface-variant font-body-md text-body-md hover:text-on-surface pb-1 transition-colors duration-200" href="http://localhost/merarashifal/kundali-details/">Kundali</a>
<a class="text-on-surface-variant font-body-md text-body-md hover:text-on-surface pb-1 transition-colors duration-200" href="http://localhost/merarashifal/matchmaking-compatibility-check/"><a class="hover:text-primary transition" href="http://localhost/merarashifal/matchmaking-compatibility-check/">Kundali Milan</a></a>
<a class="text-on-surface-variant font-body-md text-body-md hover:text-on-surface pb-1 transition-colors duration-200" href="#calculators">Vedic Calculators</a>
<a class="text-on-surface-variant font-body-md text-body-md hover:text-on-surface pb-1 transition-colors duration-200" href="http://localhost/merarashifal/reports/">Astro Reports</a>
<a class="text-on-surface-variant font-body-md text-body-md hover:text-on-surface pb-1 transition-colors duration-200" href="#devotional">Devotional</a>
</nav>

<!-- Mobile Menu Hamburger Button -->
<button id="mr-mobile-menu-toggle" type="button" class="xl:hidden p-2 text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container-high/40 transition-colors focus:outline-none" aria-label="Toggle Menu">
  <span class="material-symbols-outlined text-2xl" id="mr-menu-icon">menu</span>
</button>

<!-- Trailing Actions & Search -->
<div class="flex items-center gap-3 lg:gap-4">
<!-- Search Field -->
<div class="relative hidden md:block w-48 lg:w-56">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
<input class="w-full pl-9 pr-3 py-1.5 text-xs bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface placeholder:text-outline focus:outline-none focus:border-tertiary-container focus:ring-1 focus:ring-tertiary" placeholder="Search Rashi, Kundali..." type="text"/>
</div>
<!-- Language Switcher -->
<button class="px-2.5 py-1 text-xs font-label-md text-on-surface border border-outline-variant/50 rounded-lg hover:bg-surface-container-high/40 transition-colors duration-200">
          English / हिन्दी
        </button>
<!-- Notification Bell -->
<button aria-label="Notifications" class="p-2 text-on-surface-variant hover:text-primary rounded-full hover:bg-surface-container-high/40 transition-colors duration-200 relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary-container rounded-full"></span>
</button>
<!-- Primary CTA: Consult Astrologer -->
<button class="hidden sm:inline-flex items-center gap-1.5 bg-primary-container text-white px-4 py-2 rounded-lg font-label-lg text-label-lg shadow-sm hover:brightness-105 active:scale-95 transition-all duration-150 golden-halo">
<span class="material-symbols-outlined text-sm">support_agent</span>
          Consult Astrologer
        </button>
</div>
</div>
</header>

<!-- Mobile Navigation Drawer -->
<nav id="mr-mobile-drawer" class="hidden xl:hidden bg-surface border-b border-outline-variant/30 shadow-md px-6 py-4 space-y-3 transition-all duration-200">
  <div class="relative w-full mb-3">
    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
    <input class="w-full pl-9 pr-3 py-2 text-xs bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface placeholder:text-outline focus:outline-none focus:border-tertiary-container focus:ring-1 focus:ring-tertiary" placeholder="Search Rashi, Kundali..." type="text"/>
  </div>
  <a class="block py-2 text-primary font-title-md border-b border-outline-variant/20 hover:text-primary-container" href="http://localhost/merarashifal/panchang/">Today's Panchang (पंचांग)</a>
  <a class="block py-2 text-on-surface-variant font-body-md border-b border-outline-variant/20 hover:text-primary" href="http://localhost/merarashifal/kundali-details/">Kundali (जन्म कुंडली)</a>
  <a class="block py-2 text-on-surface-variant font-body-md border-b border-outline-variant/20 hover:text-primary" href="http://localhost/merarashifal/matchmaking-compatibility-check/">Kundali Milan (गुण मिलान)</a>
  <a class="block py-2 text-on-surface-variant font-body-md border-b border-outline-variant/20 hover:text-primary" href="#calculators">Vedic Calculators (कैलकुलेटर)</a>
  <a class="block py-2 text-on-surface-variant font-body-md border-b border-outline-variant/20 hover:text-primary" href="http://localhost/merarashifal/reports/">Astro Reports (प्रीमियम रिपोर्ट)</a>
  <a class="block py-2 text-on-surface-variant font-body-md border-b border-outline-variant/20 hover:text-primary" href="#devotional">Devotional Corner (भक्ति)</a>
  <div class="pt-2">
    <button type="button" class="mr-consult-cta-btn w-full inline-flex items-center justify-center gap-2 bg-primary-container text-white py-2.5 rounded-lg font-label-lg shadow-sm hover:brightness-105 transition-all golden-halo">
      <span class="material-symbols-outlined text-sm">support_agent</span>
      Consult Astrologer
    </button>
  </div>
</nav>

<!-- 3. HERO DASHBOARD (CORE DAILY ENGAGEMENT) -->
<main class="max-w-7xl mx-auto px-4 lg:px-12 pt-6 pb-16 space-y-8">
  <div class="mb-4">
    <h1 class="text-2xl sm:text-3xl font-headline-lg font-bold text-primary tracking-tight">Authentic Vedic Astrology, Daily Panchang &amp; Kundali Portal</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant mt-1">Real-time Swiss Ephemeris calculations, Aaj Ka Rashifal, Kundli Milan &amp; Sacred Jyotish Guidance</p>
  </div>
<!-- A. 1-Tap Zodiac Sign Selector (Top Strip) -->
<section class="bg-surface-container-lowest p-3.5 lg:p-5 rounded-2xl border border-outline-variant/30 shadow-xs">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3 px-0.5">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary-container text-xl">cyclone</span>
      <h2 class="font-title-lg text-title-lg text-on-surface font-bold">Daily Horoscope (दैनिक राशिफल)</h2>
    </div>
    <span class="text-xs text-on-surface-variant font-medium">Tap your Rashi for today's celestial forecast:</span>
  </div>

  <!-- Mobile View: App-Style 4x3 Grid (All 12 signs visible instantly without scrolling!) -->
  <div class="grid grid-cols-4 gap-2 md:hidden">
    <a href="http://localhost/merarashifal/horoscope/aries/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary group-hover:scale-110 transition-transform">♈</span>
      <span class="text-xs font-bold text-on-surface mt-1">मेष</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Aries</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/taurus/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-tertiary group-hover:scale-110 transition-transform">♉</span>
      <span class="text-xs font-bold text-on-surface mt-1">वृषभ</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Taurus</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/gemini/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-secondary group-hover:scale-110 transition-transform">♊</span>
      <span class="text-xs font-bold text-on-surface mt-1">मिथुन</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Gemini</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/cancer/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary-container group-hover:scale-110 transition-transform">♋</span>
      <span class="text-xs font-bold text-on-surface mt-1">कर्क</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Cancer</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/leo/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary group-hover:scale-110 transition-transform">♌</span>
      <span class="text-xs font-bold text-on-surface mt-1">सिंह</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Leo</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/virgo/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-tertiary group-hover:scale-110 transition-transform">♍</span>
      <span class="text-xs font-bold text-on-surface mt-1">कन्या</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Virgo</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/libra/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-secondary group-hover:scale-110 transition-transform">♎</span>
      <span class="text-xs font-bold text-on-surface mt-1">तुला</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Libra</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/scorpio/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary-container group-hover:scale-110 transition-transform">♏</span>
      <span class="text-xs font-bold text-on-surface mt-1">वृश्चिक</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Scorpio</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/sagittarius/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary group-hover:scale-110 transition-transform">♐</span>
      <span class="text-xs font-bold text-on-surface mt-1">धनु</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Sagittarius</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/capricorn/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-tertiary group-hover:scale-110 transition-transform">♑</span>
      <span class="text-xs font-bold text-on-surface mt-1">मकर</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Capricorn</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/aquarius/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-secondary group-hover:scale-110 transition-transform">♒</span>
      <span class="text-xs font-bold text-on-surface mt-1">कुंभ</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Aquarius</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/pisces/" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 hover:border-primary-container text-center transition-all duration-150 active:scale-95 group shadow-xs">
      <span class="text-xl leading-none text-primary-container group-hover:scale-110 transition-transform">♓</span>
      <span class="text-xs font-bold text-on-surface mt-1">मीन</span>
      <span class="text-[9px] text-on-surface-variant/80 font-medium">Pisces</span>
    </a>
  </div>

  <!-- Desktop View: Sleek Horizontal Scroller with full names -->
  <div class="hidden md:flex items-center gap-2 overflow-x-auto pb-1.5 pt-0.5 custom-scroll">
    <a href="http://localhost/merarashifal/horoscope/aries/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container border border-primary-container text-primary font-label-md whitespace-nowrap shadow-xs hover:brightness-105 transition">
      <span class="text-tertiary font-bold text-sm">♈</span>
      <span>Aries (Mesh - मेष)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/taurus/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♉</span>
      <span>Taurus (Vrishabh - वृषभ)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/gemini/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♊</span>
      <span>Gemini (Mithun - मिथुन)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/cancer/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♋</span>
      <span>Cancer (Kark - कर्क)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/leo/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♌</span>
      <span>Leo (Singh - सिंह)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/virgo/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♍</span>
      <span>Virgo (Kanya - कन्या)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/libra/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♎</span>
      <span>Libra (Tula - तुला)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/scorpio/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♏</span>
      <span>Scorpio (Vrishchik - वृश्चिक)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/sagittarius/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♐</span>
      <span>Sagittarius (Dhanu - धनु)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/capricorn/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♑</span>
      <span>Capricorn (Makar - मकर)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/aquarius/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♒</span>
      <span>Aquarius (Kumbh - कुंभ)</span>
    </a>
    <a href="http://localhost/merarashifal/horoscope/pisces/" class="flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container border border-outline-variant/40 text-on-surface font-body-md whitespace-nowrap transition">
      <span class="text-tertiary font-bold text-sm">♓</span>
      <span>Pisces (Meen - मीन)</span>
    </a>
  </div>
</section>

<!-- FIND YOUR SUN SIGN CALCULATOR (Mapped from Snippet #67) -->
<section class="bg-surface-container-lowest p-4 lg:p-6 rounded-2xl border border-outline-variant/30 shadow-xs" id="sunsign-calculator">
  <div class="flex flex-col space-y-3">
    <!-- Header with Title & Language Toggle -->
    <div class="flex items-center justify-between gap-2 border-b border-outline-variant/20 pb-2.5">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary-container text-xl">stars</span>
        <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Find Your Sun Sign (अपनी राशि जानें)</h3>
      </div>
      <div class="inline-flex rounded-lg border border-outline-variant/40 p-0.5 bg-surface-container-low shrink-0">
        <button type="button" class="mr-z-lang-btn px-2.5 py-1 text-xs rounded font-label-md transition-colors bg-primary-container text-white" data-lang="en">EN</button>
        <button type="button" class="mr-z-lang-btn px-2.5 py-1 text-xs rounded font-label-md transition-colors text-on-surface hover:text-primary" data-lang="hi">हिन्दी</button>
      </div>
    </div>

    <p class="font-body-sm text-xs text-on-surface-variant leading-relaxed">
      Don't know your Rashi or Sun Sign? Select your Date of Birth to reveal your authentic Vedic Sun Sign, ruling planet &amp; personality traits.
    </p>

    <!-- Responsive Form Controls -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-1">
      <div class="relative flex-1">
        <input type="date" id="mr-zodiac-dob" max="2026-10-28" class="w-full text-xs px-3.5 py-2.5 bg-surface-container-low border border-outline-variant/50 rounded-xl text-on-surface font-medium focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary" placeholder="YYYY-MM-DD"/>
      </div>
      <input type="hidden" id="mr_zodiac_nonce_field" name="mr_zodiac_nonce_field" value="3e6b1d9380"/>
      <button type="button" id="mr-zodiac-submit-btn" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary-container text-white text-xs font-label-lg shadow-sm hover:brightness-105 active:scale-95 transition-all flex items-center justify-center gap-2 golden-halo">
        <span class="material-symbols-outlined text-sm">search</span>
        <span>Calculate Sun Sign</span>
      </button>
    </div>
  </div>
  <div id="mr-zodiac-result" class="mt-4 hidden"></div>
</section>

<!-- B. 3-Column Responsive Engagement Grid -->
<section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
<!-- COLUMN 1: Live Daily Panchang & Muhurat At A Glance (4 Cols) -->
<div class="lg:col-span-4 bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between spiritual-glow shadow-sm" id="panchang">
<div>
<!-- Card Header -->
<div class="flex items-center justify-between pb-3 border-b border-outline-variant/20">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary-container">calendar_today</span>
<h2 class="font-headline-sm text-headline-sm text-on-surface"><a class="hover:text-primary transition" href="http://localhost/merarashifal/panchang/">Today's Panchang</a></h2>
</div>
<a class="text-xs font-label-md text-tertiary hover:underline" href="#">Choghadiya →</a>
</div>
<div class="mt-3 text-xs text-on-surface-variant">
<span class="font-semibold text-secondary">कार्तिक शुक्ल दशमी</span> • Dynamic Drik Ganita Calculations
          </div>
<!-- Auspicious Muhurat Indicator -->
<div class="mt-4 p-3 rounded-lg bg-surface-container-low border border-outline-variant/30 flex items-start justify-between">
<div class="space-y-0.5">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-emerald-600"></span>
<span class="font-title-md text-title-md text-on-surface">Abhijit Muhurat</span>
</div>
<p class="font-body-md text-body-md font-semibold text-primary">11:47 AM – 12:32 PM</p>
<p class="text-[11px] text-on-surface-variant">Ideal for contracts, auspicious ventures &amp; rituals</p>
</div>
<span class="bg-emerald-100 text-emerald-800 text-[10px] font-label-sm px-2 py-0.5 rounded-full border border-emerald-300">
              Most Auspicious
            </span>
</div>
<!-- Inauspicious Muhurat Warning -->
<div class="mt-3 p-3 rounded-lg bg-surface-container-high/60 border border-outline-variant/40 flex items-start justify-between">
<div class="space-y-0.5">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-error"></span>
<span class="font-title-md text-title-md text-on-surface">Rahukaal</span>
</div>
<p class="font-body-md text-body-md font-semibold text-secondary">03:00 PM – 04:22 PM</p>
<p class="text-[11px] text-on-surface-variant">Inauspicious planetary hour; halt major beginnings</p>
</div>
<span class="bg-error-container text-on-error-container text-[10px] font-label-sm px-2 py-0.5 rounded-full border border-error/20">
              Avoid New Undertakings
            </span>
</div>
<!-- Micro Shastric Panchang Metrics -->
<div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-outline-variant/20 pt-3">
<div class="bg-surface-container p-2 rounded">
<span class="text-on-surface-variant block text-[11px]">Active Nakshatra</span>
<strong class="text-on-surface font-title-md text-xs block">Shatabhisha</strong>
<span class="text-[10px] text-tertiary">Lord: Rahu | Deity: Varuna</span>
</div>
<div class="bg-surface-container p-2 rounded">
<span class="text-on-surface-variant block text-[11px]">Yoga &amp; Karana</span>
<strong class="text-on-surface font-title-md text-xs block">Dhruva / Taitila</strong>
<span class="text-[10px] text-tertiary">Anandadi: Siddha</span>
</div>
<div class="bg-surface-container p-2 rounded">
<span class="text-on-surface-variant block text-[11px]">Sun Sign (Surya)</span>
<strong class="text-on-surface font-title-md text-xs block">Tula (Libra)</strong>
</div>
<div class="bg-surface-container p-2 rounded">
<span class="text-on-surface-variant block text-[11px]">Moon Sign (Chandra)</span>
<strong class="text-on-surface font-title-md text-xs block">Kumbha (Aquarius)</strong>
</div>
</div>
</div>
<button class="mt-5 w-full py-2.5 px-4 rounded-lg border border-tertiary-container text-tertiary hover:bg-tertiary/5 font-label-lg text-label-lg transition-colors duration-150 flex items-center justify-center gap-1.5">
<span>View Complete Panchang Details</span>
<span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- COLUMN 2: Free Instant Janam Kundali Generator (4 Cols) -->
<div class="lg:col-span-4 bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-sm relative" id="kundali">
<div class="space-y-4">
<!-- Header -->
<div class="flex items-center justify-between pb-3 border-b border-outline-variant/20">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary-container">hub</span>
<h2 class="font-headline-sm text-headline-sm text-on-surface">Free Janam Kundali</h2>
</div>
<span class="text-[10px] font-label-sm bg-tertiary-fixed text-on-tertiary-fixed px-2 py-0.5 rounded">Vedic D-1 &amp; D-9</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">Generate your precision birth chart, planetary degrees &amp; current Vimshottari Mahadasha instantly.</p>
<!-- Interactive Form -->
<form class="space-y-3" method="GET" action="http://localhost/merarashifal/kundali-details/">
<div>
<label class="block text-xs font-label-md text-on-surface-variant mb-1">Full Name</label>
<input class="w-full text-xs px-3 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1 focus:ring-tertiary" placeholder="e.g. Aarav Sharma" type="text" value="Aarav Sharma"/>
</div>
<div class="grid grid-cols-2 gap-2">
<div>
<label class="block text-xs font-label-md text-on-surface-variant mb-1">Date of Birth</label>
<input class="w-full text-xs px-2.5 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1" type="date" value="1996-10-15"/>
</div>
<div>
<label class="block text-xs font-label-md text-on-surface-variant mb-1">Time of Birth</label>
<input class="w-full text-xs px-2.5 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1" type="time" value="10:45"/>
</div>
</div>
<div>
<label class="block text-xs font-label-md text-on-surface-variant mb-1">Place of Birth (City / Coordinates)</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-sm">location_on</span>
<input class="w-full text-xs pl-8 pr-3 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1" type="text" value="New Delhi, India"/>
</div>
</div>
<!-- Gender Toggle -->
<div>
<label class="block text-xs font-label-md text-on-surface-variant mb-1">Gender</label>
<div class="grid grid-cols-3 gap-2">
<label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-primary-container bg-surface-container rounded-lg cursor-pointer text-primary font-label-md">
<input checked="" class="hidden" name="gender" type="radio"/>
<span>Male</span>
</label>
<label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-outline-variant/40 bg-surface-container-low rounded-lg cursor-pointer text-on-surface hover:bg-surface-container">
<input class="hidden" name="gender" type="radio"/>
<span>Female</span>
</label>
<label class="flex items-center justify-center gap-1 text-xs py-1.5 border border-outline-variant/40 bg-surface-container-low rounded-lg cursor-pointer text-on-surface hover:bg-surface-container">
<input class="hidden" name="gender" type="radio"/>
<span>Other</span>
</label>
</div>
</div>
</form>
<p class="text-[11px] text-on-surface-variant/80 flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-tertiary">verified</span>
            Computed with Swiss Ephemeris &amp; Lahiri Ayanamsha.
          </p>
</div>
<button class="mt-4 w-full py-2.5 px-4 rounded-lg bg-primary-container text-white font-label-lg text-label-lg shadow-sm hover:brightness-105 active:scale-95 transition-all duration-150 flex items-center justify-center gap-2 golden-halo">
<span>Generate Free Kundali</span>
<span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- COLUMN 3: Niyati AI Vedic Astrologer Spotlight (4 Cols) -->
<div class="lg:col-span-4 bg-surface-container-lowest rounded-xl border border-tertiary-container/40 p-5 flex flex-col justify-between shadow-sm relative overflow-hidden">
<!-- Top Glow Accent -->
<div class="absolute -top-12 -right-12 w-32 h-32 bg-primary-container/10 rounded-full blur-2xl pointer-events-none"></div>
<div>
<!-- Portrait & Status -->
<div class="flex items-center gap-3.5 pb-3 border-b border-outline-variant/20">
<div class="relative">
<img alt="Niyati AI Vedic Astrologer" class="w-14 h-14 rounded-full object-cover ring-2 ring-tertiary-container/80 shadow-md" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA56fs91auK-YQGaPHDrYW3w8pke5ZsTwaZUGxBTQDtPENk4IOeDHGQyljUIFwxNv1PLpclY3yHeWsmD82HCln3ZfmZbeDOmSUmCcDtx6-tStZC72c3q4vXxoEpiKQKU9Y8tiBhvCEeN_kqgRHKqyAJxmWlLUzi-yXxeIFndOU5PKR9msRiPyEqvvBsewcB_u6GMFAe-yXo4_ZmUCq8-LP2HBPEqmrHo23IdTdNTw"/>
<span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
</div>
<div>
<div class="flex items-center gap-1.5">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Niyati AI Astrologer</h2>
<span class="material-symbols-outlined text-tertiary text-sm">auto_awesome</span>
</div>
<span class="inline-flex items-center gap-1 text-[11px] text-emerald-800 font-medium">
                Online Now • Shastric Vedic Guide
              </span>
</div>
</div>
<p class="mt-3 font-body-sm text-body-sm text-on-surface-variant">
            Instant shastric answers to your career, relationship, and planetary dosha questions backed by Parashara Hora Shastra.
          </p>
<!-- Quick Question Interactive Chips -->
<div class="mt-3.5 space-y-2">
<span class="text-[11px] font-label-md text-tertiary block">Frequently Asked Inquiries:</span>
<div class="flex flex-col gap-1.5 text-xs">
<button class="text-left px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/30 transition-colors flex items-center justify-between group">
<span>When will my career transition happen?</span>
<span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
</button>
<button class="text-left px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/30 transition-colors flex items-center justify-between group">
<span>Check Mangal Dosh in my chart</span>
<span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
</button>
<button class="text-left px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/30 transition-colors flex items-center justify-between group">
<span>Which gemstone is auspicious for my Rashi?</span>
<span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
</button>
<button class="text-left px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/30 transition-colors flex items-center justify-between group">
<span>Marriage prospects in 2026-2027</span>
<span class="material-symbols-outlined text-xs text-outline group-hover:text-primary">chevron_right</span>
</button>
</div>
</div>
<!-- Quick Chat Input Box -->
<div class="mt-3.5 relative">
<input class="w-full text-xs pl-3 pr-9 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg focus:outline-none focus:border-tertiary-container focus:ring-1" placeholder="Ask Niyati any astrological question..." type="text"/>
<button aria-label="Send query" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 text-primary-container hover:text-primary">
<span class="material-symbols-outlined text-base">send</span>
</button>
</div>
</div>
<button class="mt-4 w-full py-2.5 px-4 rounded-lg bg-secondary text-white font-label-lg text-label-lg shadow-sm hover:brightness-110 active:scale-95 transition-all duration-150 flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-sm">chat</span>
<span>Start Free AI Consultation</span>
</button>
</div>
</section>
<!-- 4. PRIMARY PRODUCT 1: UPCOMING FESTIVALS & VRAT CALENDAR (DRIK ENGINE) -->
<section class="pt-6 space-y-4">
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-outline-variant/30 pb-3">
<div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-tertiary">event_upcoming</span>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Upcoming Vrats, Fasts &amp; Sacred Festivals</h2>
</div>
<p class="font-body-md text-body-md text-on-surface-variant">Calculated using authentic Drik Ganita planetary positions and Shastric Sunrise (Udaya Tithi) timings.</p>
</div>
<a class="inline-flex items-center gap-1 font-label-lg text-label-lg text-tertiary hover:text-primary transition-colors" href="#">
          View Full 2026-2027 Calendar
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
<!-- 4-Column Card Grid -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
<!-- Card 1 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4.5 flex flex-col justify-between shadow-xs hover:border-tertiary-container transition-all">
<div class="space-y-2">
<div class="flex items-center justify-between">
<span class="px-2 py-0.5 rounded text-[11px] font-label-sm bg-primary-fixed text-on-primary-fixed">Starts in 2 Days</span>
<span class="material-symbols-outlined text-tertiary text-sm">spa</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface pt-1">Rama Ekadashi (रमा एकादशी)</h3>
<div class="text-xs text-secondary font-label-md">Kartik Krishna Ekadashi</div>
<div class="text-xs font-semibold text-on-surface flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-outline">calendar_month</span>
              05 Nov 2026
            </div>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Liberates from past karmic debt and bestows Lord Vishnu's grace through unbroken fasting and night vigil.
            </p>
</div>
<button class="mt-4 w-full py-1.5 px-3 rounded-lg border border-outline-variant/60 hover:bg-surface-container text-xs font-label-md text-on-surface transition-colors">
            View Vrat Katha &amp; Muhurat
          </button>
</div>
<!-- Card 2 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4.5 flex flex-col justify-between shadow-xs hover:border-tertiary-container transition-all">
<div class="space-y-2">
<div class="flex items-center justify-between">
<span class="px-2 py-0.5 rounded text-[11px] font-label-sm bg-secondary-fixed text-on-secondary-fixed">In 4 Days</span>
<span class="material-symbols-outlined text-secondary text-sm">self_improvement</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface pt-1">Pradosh Vrat (त्रयोदशी प्रदोष)</h3>
<div class="text-xs text-secondary font-label-md">Kartik Krishna Trayodashi</div>
<div class="text-xs font-semibold text-on-surface flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-outline">calendar_month</span>
              07 Nov 2026
            </div>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Evening Shiva Puja during Sandhya Kaal for peace, elimination of inner anxiety, and enduring family prosperity.
            </p>
</div>
<button class="mt-4 w-full py-1.5 px-3 rounded-lg border border-outline-variant/60 hover:bg-surface-container text-xs font-label-md text-on-surface transition-colors">
            View Puja Vidhi
          </button>
</div>
<!-- Card 3: Featured -->
<div class="bg-surface-container-lowest rounded-xl border-2 border-primary-container p-4.5 flex flex-col justify-between shadow-sm relative">
<span class="absolute -top-2.5 right-4 bg-primary-container text-white text-[10px] font-label-sm px-2 py-0.5 rounded-full shadow-xs">
            Featured Grand Festival
          </span>
<div class="space-y-2">
<div class="flex items-center justify-between">
<span class="px-2 py-0.5 rounded text-[11px] font-label-sm bg-tertiary-fixed text-on-tertiary-fixed">Upcoming Parva</span>
<span class="material-symbols-outlined text-tertiary text-sm">savings</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface pt-1">Dhanteras (धनतेरस)</h3>
<div class="text-xs text-secondary font-label-md">Dhantrayodashi Tithi</div>
<div class="text-xs font-semibold text-on-surface flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-outline">calendar_month</span>
              08 Nov 2026
            </div>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Auspicious gold, silver, and brass purchase muhurat alongside Lord Dhanvantari and Kuber Deepdan.
            </p>
</div>
<button class="mt-4 w-full py-1.5 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-xs font-label-md text-primary transition-colors">
            View Shubh Muhurat
          </button>
</div>
<!-- Card 4 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-4.5 flex flex-col justify-between shadow-xs hover:border-tertiary-container transition-all">
<div class="space-y-2">
<div class="flex items-center justify-between">
<span class="px-2 py-0.5 rounded text-[11px] font-label-sm bg-primary-fixed-dim text-on-primary-fixed">Major Parva</span>
<span class="material-symbols-outlined text-primary-container text-sm">local_fire_department</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface pt-1">Deepawali (दीपावली)</h3>
<div class="text-xs text-secondary font-label-md">Kartik Amavasya</div>
<div class="text-xs font-semibold text-on-surface flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-outline">calendar_month</span>
              10 Nov 2026
            </div>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Pradosh Kaal Lakshmi-Ganesh Shubh Choghadiya &amp; Sthir Vrishabha Lagna timings for eternal Abundance.
            </p>
</div>
<button class="mt-4 w-full py-1.5 px-3 rounded-lg border border-outline-variant/60 hover:bg-surface-container text-xs font-label-md text-on-surface transition-colors">
            View Lakshmi Puja Timings
          </button>
</div>
</div>
</section>
<!-- 5. PRIMARY PRODUCT 2: VEDIC ASTROLOGY REPORTS (FLAGSHIP MONETIZATION HUB) -->
<section class="pt-6 space-y-4" id="reports">
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-outline-variant/30 pb-3">
<div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary-container">menu_book</span>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Vedic Astrology Master Reports</h2>
</div>
<p class="font-body-md text-body-md text-on-surface-variant">Comprehensive 20-26 page shastric dossiers computed by high-precision mathematical algorithms and verified by traditional pandits.</p>
</div>
<a class="inline-flex items-center gap-1 font-label-lg text-label-lg text-tertiary hover:text-primary transition-colors" href="http://localhost/merarashifal/festivals/">
          Browse All 9 Master Reports
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
<!-- 4-Column Product Showcase Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
<!-- Report 1 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow relative">
<div class="space-y-3">
<div class="flex items-center justify-between">
<span class="text-[10px] font-label-sm bg-surface-container px-2 py-0.5 rounded text-on-surface-variant">26 Pages Detailed PDF</span>
<span class="text-[10px] font-label-sm bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-semibold">90% OFF</span>
</div>
<!-- Visual Graphic Mockup -->
<div class="h-28 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-center p-2 relative overflow-hidden">
<div class="w-20 h-20 border border-tertiary-container/40 rotate-45 flex items-center justify-center">
<div class="w-12 h-12 border border-primary/30 rotate-45 flex items-center justify-center">
<span class="text-xs font-bold text-tertiary">D-1</span>
</div>
</div>
<span class="absolute bottom-1 right-2 text-[10px] text-outline">Shodashvarga</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface">Master Janma Kundali &amp; Harmonics</h3>
<ul class="text-xs font-body-sm text-on-surface-variant space-y-1">
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                16 Shodashvargas &amp; Shadbala
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Ashtakvarga points evaluation
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Personalized Shastric Remedies
              </li>
</ul>
<div class="pt-2 border-t border-outline-variant/20 flex items-baseline gap-2">
<span class="text-xl font-bold font-headline-sm text-on-surface">₹99</span>
<span class="text-xs text-outline line-through">₹999</span>
<span class="text-[11px] text-tertiary font-medium">Instant PDF</span>
</div>
</div>
<div class="mt-4 space-y-2">
<a href="http://localhost/merarashifal/janam-kundali-master-report/" class="w-full py-2 rounded-lg bg-primary-container text-white text-xs font-label-lg shadow-xs hover:brightness-105 transition text-center">
              Get Your Report Now
            </a>
<button class="w-full py-1 text-center text-xs text-on-surface-variant hover:text-primary transition-colors">
              View Sample PDF
            </button>
</div>
</div>
<!-- Report 2 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow relative">
<div class="space-y-3">
<div class="flex items-center justify-between">
<span class="text-[10px] font-label-sm bg-surface-container px-2 py-0.5 rounded text-on-surface-variant">24 Pages Annual Forecast</span>
<span class="text-[10px] font-label-sm bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-semibold">85% OFF</span>
</div>
<!-- Visual Graphic Mockup -->
<div class="h-28 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-center p-2 relative overflow-hidden">
<div class="w-20 h-20 rounded-full border border-dashed border-tertiary-container flex items-center justify-center">
<span class="material-symbols-outlined text-primary-container text-2xl">sunny</span>
</div>
<span class="absolute bottom-1 right-2 text-[10px] text-outline">Varshaphal</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface">Yearly Horoscope Varshaphal 2026-27</h3>
<ul class="text-xs font-body-sm text-on-surface-variant space-y-1">
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                12-Month timeline forecasts
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Career, wealth &amp; health transits
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Tajika Saham points analysis
              </li>
</ul>
<div class="pt-2 border-t border-outline-variant/20 flex items-baseline gap-2">
<span class="text-xl font-bold font-headline-sm text-on-surface">₹199</span>
<span class="text-xs text-outline line-through">₹1,499</span>
<span class="text-[11px] text-tertiary font-medium">Instant PDF</span>
</div>
</div>
<div class="mt-4 space-y-2">
<a href="http://localhost/merarashifal/yearly-horoscope-report/" class="w-full py-2 rounded-lg bg-primary-container text-white text-xs font-label-lg shadow-xs hover:brightness-105 transition text-center">
              Get Your Report Now
            </a>
<button class="w-full py-1 text-center text-xs text-on-surface-variant hover:text-primary transition-colors">
              View Sample PDF
            </button>
</div>
</div>
<!-- Report 3 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow relative">
<div class="space-y-3">
<div class="flex items-center justify-between">
<span class="text-[10px] font-label-sm bg-surface-container px-2 py-0.5 rounded text-on-surface-variant">22 Pages Chronology</span>
<span class="text-[10px] font-label-sm bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-semibold">80% OFF</span>
</div>
<!-- Visual Graphic Mockup -->
<div class="h-28 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-center p-2 relative overflow-hidden">
<div class="w-20 h-20 rounded-full border-2 border-tertiary/40 flex items-center justify-center">
<span class="material-symbols-outlined text-secondary text-2xl">schedule</span>
</div>
<span class="absolute bottom-1 right-2 text-[10px] text-outline">Vimshottari</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface">Master Vedic Dasha Life Timing</h3>
<ul class="text-xs font-body-sm text-on-surface-variant space-y-1">
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Mahadasha &amp; Antardasha to 2050
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Sub-period auspicious windows
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Gemstone &amp; Mantra counter-measures
              </li>
</ul>
<div class="pt-2 border-t border-outline-variant/20 flex items-baseline gap-2">
<span class="text-xl font-bold font-headline-sm text-on-surface">₹149</span>
<span class="text-xs text-outline line-through">₹1,199</span>
<span class="text-[11px] text-tertiary font-medium">Instant PDF</span>
</div>
</div>
<div class="mt-4 space-y-2">
<a href="http://localhost/merarashifal/dasha-timing-report/" class="w-full py-2 rounded-lg bg-primary-container text-white text-xs font-label-lg shadow-xs hover:brightness-105 transition text-center">
              Get Your Report Now
            </a>
<button class="w-full py-1 text-center text-xs text-on-surface-variant hover:text-primary transition-colors">
              View Sample PDF
            </button>
</div>
</div>
<!-- Report 4 -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow relative">
<div class="space-y-3">
<div class="flex items-center justify-between">
<span class="text-[10px] font-label-sm bg-surface-container px-2 py-0.5 rounded text-on-surface-variant">20 Pages Samudrik</span>
<span class="text-[10px] font-label-sm bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-semibold">88% OFF</span>
</div>
<!-- Visual Graphic Mockup -->
<div class="h-28 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-center p-2 relative overflow-hidden">
<span class="material-symbols-outlined text-tertiary-container text-4xl">front_hand</span>
<span class="absolute bottom-1 right-2 text-[10px] text-outline">Hast Rekha</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface">Master Hast Rekha &amp; Palmistry</h3>
<ul class="text-xs font-body-sm text-on-surface-variant space-y-1">
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Lifeline, Heart &amp; Fate lines
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Finger Mounts &amp; Chakra signs
              </li>
<li class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                Samudrik Shastra remedies
              </li>
</ul>
<div class="pt-2 border-t border-outline-variant/20 flex items-baseline gap-2">
<span class="text-xl font-bold font-headline-sm text-on-surface">₹129</span>
<span class="text-xs text-outline line-through">₹1,099</span>
<span class="text-[11px] text-tertiary font-medium">Instant PDF</span>
</div>
</div>
<div class="mt-4 space-y-2">
<a href="http://localhost/merarashifal/palm-reading-report/" class="w-full py-2 rounded-lg bg-primary-container text-white text-xs font-label-lg shadow-xs hover:brightness-105 transition text-center">
              Get Your Report Now
            </a>
<button class="w-full py-1 text-center text-xs text-on-surface-variant hover:text-primary transition-colors">
              View Sample PDF
            </button>
</div>
</div>
</div>
</section>

<!-- BABY NAME FINDER COMPACT ENGINE (Mapped from Snippet #74) -->
<section class="pt-2" id="baby-names-finder">
  <?php echo do_shortcode('[baby_name_compact_search]'); ?>
</section>

<!-- 6. INTERACTIVE VEDIC CALCULATION ENGINES & FREE TOOLS -->
<section class="pt-6 space-y-4" id="calculators">
<div class="border-b border-outline-variant/30 pb-3">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-tertiary">calculate</span>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Vedic Calculators &amp; Shastric Engines</h2>
</div>
<p class="font-body-md text-body-md text-on-surface-variant">Instant, ad-free computations based on ancient Brihat Parashara Hora Shastra and Surya Siddhanta principles.</p>
</div>
<!-- 8-Card responsive micro-grid (4x2 on desktop) -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
<!-- Tool 1 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/daily-choghadiya-muhurat/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">alarm</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Choghadiya Muhurat</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Day &amp; Night Shubh, Labh, Amrit, Rog, and Kaal timings with live planetary hour status.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Calculate Choghadiya</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 2 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/matchmaking-compatibility-check/" id="milan">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">favorite</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">36 Gun Milan (Matching)</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Ashtakoot compatibility, Nadi dosh, Bhakoot scoring, and matrimonial harmony breakdown.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Match Horoscopes</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 3 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/dosh/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">warning</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Kundali Dosh Calculator</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">In-depth shastric evaluation of Manglik, Kaal Sarp, Kemdrum, and Pitra Dosh.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Check Dosh Free</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 4 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/dasha/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-tertiary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">hourglass_empty</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Mahadasha Checker</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Real-time Vimshottari planetary periods and running transit impact checker.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Check Active Dasha</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 5 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/muhurat-dates/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">event_available</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Shubh Muhurat Dates</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Auspicious dates for Vivah, Griha Pravesh, Property, and Vehicle purchase.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Explore Muhurat Dates</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 6 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/baby-names/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">child_care</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Baby Names Engine</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">50,000+ Vedic names filtered by Nakshatra charan, Rashi, and numerological harmony.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Find Sacred Names</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 7 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/name-suitability/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">spellcheck</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Name Suitability Engine</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Vedic Swara &amp; Numerological harmony score for personal and corporate brand names.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Analyze Name Score</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
<!-- Tool 8 -->
<a class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:border-tertiary-container shadow-xs hover:shadow transition-all group flex flex-col justify-between" href="http://localhost/merarashifal/remedies-astrological-emotional-sociological-solutions/">
<div>
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-tertiary mb-3 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined">diamond</span>
</div>
<h4 class="font-title-md text-title-md text-on-surface group-hover:text-primary">Astrological Remedies Hub</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Shastric recommendations for Rudraksha, Certified Gemstones, and Vedic Mantras.</p>
</div>
<div class="mt-4 flex items-center gap-1 text-xs font-label-md text-tertiary group-hover:translate-x-1 transition-transform">
<span>Explore Remedies</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</div>
</a>
</div>
</section>
<!-- 7. SANATAN DEVOTIONAL & SPIRITUAL CORNER -->
<section class="pt-6 space-y-6" id="devotional">
<div class="border-b border-outline-variant/30 pb-3">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary-container">temple_hindu</span>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Sanatan Devotional &amp; Spiritual Corner</h2>
</div>
<p class="font-body-md text-body-md text-on-surface-variant">Sacred stotras, authentic vrat kathas, and timeless arti sangrah for your daily puja anushthan.</p>
</div>
<!-- Subsection A: Hindu Deities Navigation (Horizontal Circular Avatar Bar) -->
<div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 shadow-xs">
<div class="flex items-center justify-between mb-3">
<span class="text-xs font-label-md text-on-surface">Choose Deity for Dedicated Aartis &amp; Stotras:</span>
</div>
<div class="flex items-center gap-4 lg:gap-6 overflow-x-auto pb-1 custom-scroll">
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border-2 border-primary-container flex items-center justify-center text-primary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">brightness_high</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Shree Ram</span>
<span class="text-[10px] text-outline">14 Stotras</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-tertiary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">music_note</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Krishna Ji</span>
<span class="text-[10px] text-outline">18 Aartis</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-secondary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">water_drop</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Shiv Ji</span>
<span class="text-[10px] text-outline">22 Stutis</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary-container group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">stars</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Ganesh Ji</span>
<span class="text-[10px] text-outline">12 Chalisa</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">shield</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Vishnu Ji</span>
<span class="text-[10px] text-outline">10 Sahasranam</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary-container group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">local_fire_department</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Hanuman Ji</span>
<span class="text-[10px] text-outline">8 Paath</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-secondary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">cyclone</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Durga Mata</span>
<span class="text-[10px] text-outline">16 Suktams</span>
</button>
<button class="flex flex-col items-center gap-1.5 min-w-[72px] group">
<div class="w-14 h-14 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-tertiary group-hover:scale-105 transition">
<span class="material-symbols-outlined text-2xl">sunny</span>
</div>
<span class="font-label-md text-xs text-on-surface group-hover:text-primary">Surya Dev</span>
<span class="text-[10px] text-outline">9 Namaskar</span>
</button>
</div>
</div>
<!-- Subsection B: Devotional Content & Media Grid (3-column layout) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
<!-- Col 1: Trending Vrat Katha -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-xs">
<div>
<div class="flex items-center gap-2 pb-3 border-b border-outline-variant/20 mb-3">
<span class="material-symbols-outlined text-primary">auto_stories</span>
<h3 class="font-title-lg text-title-lg text-on-surface">Trending Vrat Kathas</h3>
</div>
<div class="space-y-3">
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer">
<div class="font-title-md text-xs text-on-surface font-semibold">Satyanarayan Vrat Katha (सत्यनारायण कथा)</div>
<div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
<span>Complete 5 Chapters with Vidhi</span>
<span class="text-tertiary">18 min read</span>
</div>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer">
<div class="font-title-md text-xs text-on-surface font-semibold">Rama Ekadashi Mahatmya (रमा एकादशी माहात्म्य)</div>
<div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
<span>Brahmavaivarta Purana extract</span>
<span class="text-tertiary">10 min read</span>
</div>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer">
<div class="font-title-md text-xs text-on-surface font-semibold">Karwa Chauth Traditional Vrat Katha</div>
<div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1">
<span>Veeravati Katha &amp; Moon timings</span>
<span class="text-tertiary">12 min read</span>
</div>
</div>
</div>
</div>
<a class="mt-4 text-xs font-label-md text-primary flex items-center gap-1 hover:underline" href="#">
<span>Read All Sacred Kathas</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</a>
</div>
<!-- Col 2: Daily Aarti Sangrah -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-xs">
<div>
<div class="flex items-center gap-2 pb-3 border-b border-outline-variant/20 mb-3">
<span class="material-symbols-outlined text-tertiary">library_music</span>
<h3 class="font-title-lg text-title-lg text-on-surface">Daily Aarti Sangrah</h3>
</div>
<div class="space-y-3">
<div class="p-2.5 rounded-lg bg-surface-container-low flex items-center justify-between">
<div>
<div class="text-xs font-semibold text-on-surface">Jai Ganesh Deva (जय गणेश देवा)</div>
<div class="text-[11px] text-outline">Evening Pradosh Aarti</div>
</div>
<button aria-label="Play Aarti" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition">
<span class="material-symbols-outlined text-sm">play_arrow</span>
</button>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low flex items-center justify-between">
<div>
<div class="text-xs font-semibold text-on-surface">Om Jai Jagdish Hare (जगदीश हरे)</div>
<div class="text-[11px] text-outline">Universal Vishnu Aarti</div>
</div>
<button aria-label="Play Aarti" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition">
<span class="material-symbols-outlined text-sm">play_arrow</span>
</button>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low flex items-center justify-between">
<div>
<div class="text-xs font-semibold text-on-surface">Shiv Aarti (जय शिव ओंकारा)</div>
<div class="text-[11px] text-outline">Kailash Stuti with damru</div>
</div>
<button aria-label="Play Aarti" class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center hover:scale-105 transition">
<span class="material-symbols-outlined text-sm">play_arrow</span>
</button>
</div>
</div>
</div>
<a class="mt-4 text-xs font-label-md text-tertiary flex items-center gap-1 hover:underline" href="#">
<span>Explore 108+ Aartis with Audio</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</a>
</div>
<!-- Col 3: Devotional Videos & Chants -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 p-5 flex flex-col justify-between shadow-xs">
<div>
<div class="flex items-center gap-2 pb-3 border-b border-outline-variant/20 mb-3">
<span class="material-symbols-outlined text-secondary">videocam</span>
<h3 class="font-title-lg text-title-lg text-on-surface">Devotional Chants &amp; Audio</h3>
</div>
<div class="space-y-3">
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer flex items-center gap-3">
<div class="w-12 h-10 rounded bg-secondary-fixed flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-lg">mic</span>
</div>
<div>
<div class="text-xs font-semibold text-on-surface">Hanuman Chalisa (108 आवर्तन)</div>
<div class="text-[11px] text-on-surface-variant">Hariharan Swar • 43 mins</div>
</div>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer flex items-center gap-3">
<div class="w-12 h-10 rounded bg-tertiary-fixed flex items-center justify-center text-tertiary">
<span class="material-symbols-outlined text-lg">graphic_eq</span>
</div>
<div>
<div class="text-xs font-semibold text-on-surface">Mahamrityunjaya Mantra</div>
<div class="text-[11px] text-on-surface-variant">Vedic Chanting • Rigveda 7.59.12</div>
</div>
</div>
<div class="p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition cursor-pointer flex items-center gap-3">
<div class="w-12 h-10 rounded bg-primary-fixed flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-lg">music_video</span>
</div>
<div>
<div class="text-xs font-semibold text-on-surface">Morning Gayatri Stuti (11 Reps)</div>
<div class="text-[11px] text-on-surface-variant">Solar Awakening chant • 15 mins</div>
</div>
</div>
</div>
</div>
<a class="mt-4 text-xs font-label-md text-secondary flex items-center gap-1 hover:underline" href="#">
<span>Access Vedic Media Library</span>
<span class="material-symbols-outlined text-xs">arrow_forward</span>
</a>
</div>
</div>
</section>
<!-- 8. TELEGRAM BROADCAST BANNER -->
<section class="bg-inverse-surface rounded-2xl p-6 lg:p-8 text-inverse-on-surface relative overflow-hidden shadow-md">
<div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
<div class="space-y-2 max-w-2xl text-center md:text-left">
<div class="inline-flex items-center gap-2 bg-white/10 px-3 py-1 rounded-full text-xs text-tertiary-fixed font-label-md">
<span class="material-symbols-outlined text-sm">send</span>
            Join 50,000+ Devotees on Telegram
          </div>
<h2 class="font-headline-md text-headline-md text-white font-semibold">
            Get Daily Panchang &amp; Shubh Muhurat on Your Phone Every Morning
          </h2>
<p class="font-body-md text-body-md text-outline-variant">
            Delivered directly at 6:00 AM daily: Aaj Ka Rashifal, Choghadiya alert, Abhijit Muhurat, Rahukaal warnings, and auspicious Ekadashi reminders. 100% free forever.
          </p>
</div>
<a href="https://t.me/merarashifal" target="_blank" rel="noopener" class="whitespace-nowrap px-6 py-3 rounded-xl bg-primary-container text-white font-label-lg golden-halo inline-flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md">
<span class="material-symbols-outlined text-lg">qr_code_2</span>
<span>Join Free Telegram Channel</span>
</a>
</div>
<!-- Background Sacred Motif Hint -->
<div class="absolute -right-8 -bottom-8 w-48 h-48 bg-primary/10 rounded-full blur-xl pointer-events-none"></div>
</section>
<!-- TRUST BADGES STRIP -->
<section class="py-4 border-y border-outline-variant/30 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
<div class="flex flex-col items-center gap-1 p-2">
<span class="material-symbols-outlined text-tertiary text-2xl">verified</span>
<span class="font-label-md text-xs text-on-surface">Authentic Shastric Algorithms</span>
<span class="text-[11px] text-on-surface-variant">Strict Lahiri Ayanamsha Ephemeris</span>
</div>
<div class="flex flex-col items-center gap-1 p-2">
<span class="material-symbols-outlined text-primary text-2xl">flash_on</span>
<span class="font-label-md text-xs text-on-surface">Instant PDF &amp; WhatsApp Delivery</span>
<span class="text-[11px] text-on-surface-variant">Dossier generated within 60 seconds</span>
</div>
<div class="flex flex-col items-center gap-1 p-2">
<span class="material-symbols-outlined text-secondary text-2xl">lock</span>
<span class="font-label-md text-xs text-on-surface">100% Confidential &amp; Encrypted</span>
<span class="text-[11px] text-on-surface-variant">Razorpay &amp; 256-bit SSL Protection</span>
</div>
<div class="flex flex-col items-center gap-1 p-2">
<span class="material-symbols-outlined text-primary-container text-2xl">star</span>
<span class="font-label-md text-xs text-on-surface">4.9/5 Star Verified Rating</span>
<span class="text-[11px] text-on-surface-variant">From 120,000+ satisfied seekers</span>
</div>
</section>
</main>
<!-- 9. WEB FOOTER (Footer Shared Component Execution) -->
<footer class="docked full-width bottom bg-surface-container border-t border-outline-variant/40 transition-colors duration-200">
<div class="w-full px-6 lg:px-12 max-w-7xl mx-auto pt-16 pb-28 lg:pb-16">
<!-- 4-Column Shastric Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 pb-12 border-b border-outline-variant/30">
<!-- Brand & Description (2 cols on large) -->
<div class="lg:col-span-2 space-y-4">
<div class="flex items-center gap-2">
<img alt="MeraRashifal Logo" class="h-9 w-auto object-contain" src="<?php echo esc_url( content_url( '/uploads/2025/08/logo-for-webstie-3-BG-Remove-300x200.png' ) ); ?>"/>
<span class="font-headline-md text-headline-md font-bold text-primary">MeraRashifal</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm leading-relaxed">
            The sanctuary for authentic Vedic astronomy and Parashara Hora Jyotish. Free from superstition, powered by precision astronomical calculations (Drik Ganita), honoring timeless Sanatan traditions.
          </p>
<div class="flex items-center gap-3 text-tertiary">
<a aria-label="Social Link" class="p-2 rounded-full bg-surface-container-high/50 hover:bg-surface-container-high transition" href="#"><span class="material-symbols-outlined text-sm">public</span></a>
<a aria-label="Telegram" class="p-2 rounded-full bg-surface-container-high/50 hover:bg-surface-container-high transition" href="#"><span class="material-symbols-outlined text-sm">send</span></a>
<a aria-label="Email support" class="p-2 rounded-full bg-surface-container-high/50 hover:bg-surface-container-high transition" href="#"><span class="material-symbols-outlined text-sm">mail</span></a>
</div>
</div>
<!-- Col 1: Daily Astrology -->
<div class="space-y-3">
<h4 class="font-headline-sm text-headline-sm text-primary">Daily Astrology</h4>
<ul class="space-y-2 text-xs">
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Today's Shubh Muhurat</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Daily &amp; Monthly Panchang</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Choghadiya Calculator</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Aaj Ka Rashifal (12 Signs)</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Rahu Kaal &amp; Yamaganda Timings</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Vrat &amp; Upvas Calendar</a></li>
</ul>
</div>
<!-- Col 2: Vedic Reports -->
<div class="space-y-3">
<h4 class="font-headline-sm text-headline-sm text-primary">Vedic Reports</h4>
<ul class="space-y-2 text-xs">
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Master Janma Kundali (26 Pgs)</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Yearly Varshaphal 2026-27</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Vimshottari Dasha Analysis</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Sade Sati Report</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Mangal Dosh Analysis</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Kundali Matching (Gun Milan)</a></li>
</ul>
</div>
<!-- Col 3: Devotional & Trust -->
<div class="space-y-3">
<h4 class="font-headline-sm text-headline-sm text-primary">Devotional &amp; Seva</h4>
<ul class="space-y-2 text-xs">
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Sanatan Aarti Sangrah</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Satyanarayan Vrat Katha</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Ekadashi Mahatmya Vidhi</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Editorial Ethics</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Astrologer Verification Policy</a></li>
<li><a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Privacy Policy &amp; Terms of Seva</a></li>
</ul>
</div>
</div>
<!-- Copyright & Bottom Disclaimers -->
<div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-body-sm text-on-surface-variant">
<div>
          © 2026 MeraRashifal. All sacred Vedic wisdom preserved. Shastric computations based on Drik Ganita ephemeris.
        </div>
<div class="flex flex-wrap items-center gap-4 text-[11px]">
<a class="hover:text-primary" href="#"><a class="hover:text-primary transition" href="http://localhost/merarashifal/privacy-policy/">Privacy Policy</a></a>
<span>•</span>
<a class="hover:text-primary" href="#">Terms of Seva</a>
<span>•</span>
<a class="hover:text-primary" href="#">Astrological Disclaimer</a>
<span>•</span>
<a class="hover:text-primary" href="#">Sitemap</a>
</div>
</div>
</div>
</footer>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Mobile Drawer Toggle
    const toggleBtn = document.getElementById("mr-mobile-menu-toggle");
    const drawer = document.getElementById("mr-mobile-drawer");
    const menuIcon = document.getElementById("mr-menu-icon");
    if (toggleBtn && drawer) {
        toggleBtn.addEventListener("click", function() {
            drawer.classList.toggle("hidden");
            if (drawer.classList.contains("hidden")) {
                menuIcon.textContent = "menu";
            } else {
                menuIcon.textContent = "close";
            }
        });
    }

    // Niyati AI Integration
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
        } else {
            console.log("Niyati query:", msg);
        }
    }

    // Attach to question chips in Niyati card
    document.querySelectorAll(".lg\:col-span-4 button").forEach(function(btn) {
        const text = btn.textContent.trim();
        if (text.includes("Career") || text.includes("Vivah") || text.includes("Mangal") || text.includes("Sade Sati")) {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                openNiyatiWithMessage(text);
            });
        }
    });

    // Attach to Niyati hero card send button & enter key
    const heroNiyatiInput = document.querySelector(".lg\:col-span-4 input[placeholder*='Ask Niyati']");
    if (heroNiyatiInput) {
        const heroNiyatiBtn = heroNiyatiInput.nextElementSibling;
        const triggerSend = function() {
            const val = heroNiyatiInput.value.trim();
            if (val) {
                openNiyatiWithMessage(val);
                heroNiyatiInput.value = "";
            }
        };
        if (heroNiyatiBtn) {
            heroNiyatiBtn.addEventListener("click", triggerSend);
        }
        heroNiyatiInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                triggerSend();
            }
        });
    }

    // Consult Astrologer CTA
    document.querySelectorAll(".mr-consult-cta-btn, header button").forEach(function(el) {
        if (el.textContent.trim().includes("Consult Astrologer")) {
            el.addEventListener("click", function(e) {
                const launcher = document.querySelector("#niyati-launcher, .niyati-launcher, #niyati-widget");
                if (launcher) {
                    e.preventDefault();
                    launcher.click();
                }
            });
        }
    });
});
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Sun Sign Calculator AJAX Logic (Mapped from Snippet #67)
    const dobInput = document.getElementById("mr-zodiac-dob");
    const submitBtn = document.getElementById("mr-zodiac-submit-btn");
    const langBtns = document.querySelectorAll(".mr-z-lang-btn");
    const resultArea = document.getElementById("mr-zodiac-result");
    let currentLang = "en";

    langBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            langBtns.forEach(b => {
                b.classList.remove("bg-primary-container", "text-white");
                b.classList.add("text-on-surface");
            });
            this.classList.remove("text-on-surface");
            this.classList.add("bg-primary-container", "text-white");
            currentLang = this.getAttribute("data-lang");
            if (dobInput && dobInput.value) {
                fetchSunSign();
            }
        });
    });

    const fetchSunSign = () => {
        if (!dobInput || !dobInput.value) {
            alert(currentLang === "hi" ? "कृपया अपनी जन्मतिथि चुनें" : "Please select your date of birth");
            return;
        }

        resultArea.classList.remove("hidden");
        resultArea.innerHTML = '<div class="p-6 text-center text-primary font-semibold text-sm flex items-center justify-center gap-2"><span class="w-3 h-3 rounded-full bg-primary-container animate-ping"></span>' + (currentLang === "hi" ? "✨ आपके नक्षत्र व सूर्य राशि का विश्लेषण हो रहा है..." : "✨ Analyzing your stars & planetary positions...") + '</div>';

        const formData = new FormData();
        formData.append("action", "af_get_zodiac_details");
        formData.append("dob", dobInput.value);
        formData.append("lang", currentLang);
        formData.append("hp_nonce", document.getElementById("mr_zodiac_nonce_field").value);

        fetch("http://localhost/merarashifal/wp-admin/admin-ajax.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = data.data;
                html = html.split("https://merarashifal.com/horoscope/zodiac_sign/").join("http://localhost/merarashifal/horoscope/");
                resultArea.innerHTML = html;
            } else {
                resultArea.innerHTML = '<div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs">' + data.data + '</div>';
            }
        })
        .catch(err => {
            console.error("Zodiac fetch error:", err);
            resultArea.innerHTML = '<div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs">Failed to calculate zodiac sign. Please try again.</div>';
        });
    };

    if (submitBtn) {
        submitBtn.addEventListener("click", fetchSunSign);
    }
    if (dobInput) {
        dobInput.addEventListener("change", fetchSunSign);
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>