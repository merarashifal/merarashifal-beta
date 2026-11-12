<?php
/**
 * Merarashifal - Grand Diwali Festive Theme Overlay (v3)
 * Features:
 * - Hanging Toran anchored cleanly BELOW the navigation header (Zero obstruction of menu items).
 * - Vertical Border Garlands (Phool Mala) along screen edges.
 * - Grand Skyshot Fireworks Canvas engine with instant launch.
 * - Bottom & corner Diyas removed per user request; elegant glowing Diya integrated inside the Toran crest.
 * - Non-intrusive (pointer-events: none), responsive, and visitor-toggleable.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Diwali_Theme_Overlay {

    public static function init() {
        if (!apply_filters('mr_diwali_theme_enabled', true)) {
            return;
        }

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets'], 99);
        add_action('wp_footer', [__CLASS__, 'render_overlay_markup'], 99);
    }

    public static function enqueue_assets() {
        $uri = get_stylesheet_directory_uri() . '/inc/diwali-theme';
        $dir = get_stylesheet_directory() . '/inc/diwali-theme';

        $css_ver = file_exists($dir . '/diwali-overlay.css') ? filemtime($dir . '/diwali-overlay.css') : '3.0.0';
        $js_ver  = file_exists($dir . '/diwali-sparkles.js')  ? filemtime($dir . '/diwali-sparkles.js')  : '3.0.0';

        wp_enqueue_style('mr-diwali-overlay-css', $uri . '/diwali-overlay.css', [], $css_ver);
        wp_enqueue_script('mr-diwali-sparkles-js', $uri . '/diwali-sparkles.js', [], $js_ver, true);
    }

    public static function render_overlay_markup() {
        ?>
        <!-- ================= Merarashifal Grand Diwali Festive Overlay ================= -->
        <div id="mr-diwali-overlay-container" aria-hidden="true">
            <!-- Fullscreen Skyshots & Fireworks Canvas -->
            <canvas id="mr-diwali-sparkles-canvas"></canvas>

            <!-- ================= VERTICAL SIDE GARLANDS (PHOOL MALA) ================= -->
            <!-- Left Side Border Garland -->
            <div class="mr-diwali-side-garland mr-diwali-side-left">
                <svg viewBox="0 0 32 600" width="100%" height="100%" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="16" y1="0" x2="16" y2="580" stroke="#ca8a04" stroke-width="2" stroke-dasharray="4,4"/>
                    <!-- Repeating Marigold Beads -->
                    <circle cx="16" cy="40" r="10" fill="#f97316"/>
                    <circle cx="16" cy="70" r="9" fill="#facc15"/>
                    <circle cx="16" cy="100" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="130" r="9" fill="#fde047"/>
                    <circle cx="16" cy="160" r="10" fill="#f97316"/>
                    <circle cx="16" cy="190" r="9" fill="#facc15"/>
                    <circle cx="16" cy="220" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="250" r="9" fill="#fde047"/>
                    <circle cx="16" cy="280" r="10" fill="#f97316"/>
                    <circle cx="16" cy="310" r="9" fill="#facc15"/>
                    <circle cx="16" cy="340" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="370" r="9" fill="#fde047"/>
                    <circle cx="16" cy="400" r="10" fill="#f97316"/>
                    <circle cx="16" cy="430" r="9" fill="#facc15"/>
                    <circle cx="16" cy="460" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="490" r="9" fill="#fde047"/>
                    <circle cx="16" cy="520" r="10" fill="#f97316"/>
                    <circle cx="16" cy="550" r="9" fill="#facc15"/>
                    <!-- Bottom Hanging Brass Bell -->
                    <path d="M10,580 C10,570 12,568 16,568 C20,568 22,570 22,580 Z" fill="#eab308"/>
                    <ellipse cx="16" cy="580" rx="6" ry="2" fill="#ca8a04"/>
                    <circle cx="16" cy="583" r="2" fill="#fde047"/>
                </svg>
            </div>

            <!-- Right Side Border Garland -->
            <div class="mr-diwali-side-garland mr-diwali-side-right">
                <svg viewBox="0 0 32 600" width="100%" height="100%" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="16" y1="0" x2="16" y2="580" stroke="#ca8a04" stroke-width="2" stroke-dasharray="4,4"/>
                    <circle cx="16" cy="40" r="10" fill="#f97316"/>
                    <circle cx="16" cy="70" r="9" fill="#facc15"/>
                    <circle cx="16" cy="100" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="130" r="9" fill="#fde047"/>
                    <circle cx="16" cy="160" r="10" fill="#f97316"/>
                    <circle cx="16" cy="190" r="9" fill="#facc15"/>
                    <circle cx="16" cy="220" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="250" r="9" fill="#fde047"/>
                    <circle cx="16" cy="280" r="10" fill="#f97316"/>
                    <circle cx="16" cy="310" r="9" fill="#facc15"/>
                    <circle cx="16" cy="340" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="370" r="9" fill="#fde047"/>
                    <circle cx="16" cy="400" r="10" fill="#f97316"/>
                    <circle cx="16" cy="430" r="9" fill="#facc15"/>
                    <circle cx="16" cy="460" r="10" fill="#ea580c"/>
                    <circle cx="16" cy="490" r="9" fill="#fde047"/>
                    <circle cx="16" cy="520" r="10" fill="#f97316"/>
                    <circle cx="16" cy="550" r="9" fill="#facc15"/>
                    <path d="M10,580 C10,570 12,568 16,568 C20,568 22,570 22,580 Z" fill="#eab308"/>
                    <ellipse cx="16" cy="580" rx="6" ry="2" fill="#ca8a04"/>
                    <circle cx="16" cy="583" r="2" fill="#fde047"/>
                </svg>
            </div>

            <!-- ================= HEADER-ANCHORED HANGING TORAN ================= -->
            <!-- Positioned cleanly at the bottom edge of the header; zero menu cutting -->
            <div id="mr-diwali-toran-wrapper" class="mr-diwali-toran-wrapper">
                <svg class="mr-diwali-toran-svg" viewBox="0 0 1600 85" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Gradients -->
                        <linearGradient id="mrToranGoldV3" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#fffbeb"/>
                            <stop offset="25%" stop-color="#fde047"/>
                            <stop offset="65%" stop-color="#eab308"/>
                            <stop offset="100%" stop-color="#b45309"/>
                        </linearGradient>

                        <linearGradient id="mrCrestRedV3" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#701a75"/>
                            <stop offset="20%" stop-color="#991b1b"/>
                            <stop offset="50%" stop-color="#dc2626"/>
                            <stop offset="80%" stop-color="#991b1b"/>
                            <stop offset="100%" stop-color="#701a75"/>
                        </linearGradient>

                        <radialGradient id="mrMarigoldOrangeV3" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#fffbeb"/>
                            <stop offset="35%" stop-color="#fb923c"/>
                            <stop offset="75%" stop-color="#ea580c"/>
                            <stop offset="100%" stop-color="#9a3412"/>
                        </radialGradient>

                        <radialGradient id="mrMarigoldYellowV3" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#ffffff"/>
                            <stop offset="40%" stop-color="#fde047"/>
                            <stop offset="80%" stop-color="#eab308"/>
                            <stop offset="100%" stop-color="#a16207"/>
                        </radialGradient>

                        <linearGradient id="mrMangoLeafV3" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#86efac"/>
                            <stop offset="40%" stop-color="#22c55e"/>
                            <stop offset="100%" stop-color="#15803d"/>
                        </linearGradient>

                        <filter id="mrCrestGlowV3" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="3.5" result="blur"/>
                            <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                        </filter>
                    </defs>

                    <!-- Top Attachment String (hugs the bottom border of header) -->
                    <path d="M0,2 Q400,10 800,6 Q1200,10 1600,2" stroke="#ca8a04" stroke-width="2.5" fill="none" opacity="0.95"/>

                    <!-- Garland Arcs Hanging Downward -->
                    <path d="M0,2 Q170,38 340,16" stroke="#d97706" stroke-width="2" fill="none"/>
                    <path d="M340,16 Q510,42 680,14" stroke="#d97706" stroke-width="2" fill="none"/>
                    <path d="M920,14 Q1090,42 1260,16" stroke="#d97706" stroke-width="2" fill="none"/>
                    <path d="M1260,16 Q1430,38 1600,2" stroke="#d97706" stroke-width="2" fill="none"/>

                    <!-- Marigold Beads - Left -->
                    <g fill="url(#mrMarigoldOrangeV3)">
                        <circle cx="50" cy="14" r="8"/>
                        <circle cx="120" cy="24" r="9"/>
                        <circle cx="190" cy="30" r="9.5"/>
                        <circle cx="260" cy="26" r="9"/>
                        <circle cx="390" cy="23" r="9"/>
                        <circle cx="460" cy="32" r="9.5"/>
                        <circle cx="530" cy="34" r="9.5"/>
                        <circle cx="600" cy="27" r="9"/>
                    </g>
                    <g fill="url(#mrMarigoldYellowV3)">
                        <circle cx="85" cy="19" r="7.5"/>
                        <circle cx="155" cy="28" r="8"/>
                        <circle cx="225" cy="28" r="8"/>
                        <circle cx="295" cy="20" r="7.5"/>
                        <circle cx="425" cy="28" r="8"/>
                        <circle cx="495" cy="34" r="8.5"/>
                        <circle cx="565" cy="31" r="8"/>
                        <circle cx="635" cy="21" r="7.5"/>
                    </g>

                    <!-- Marigold Beads - Right -->
                    <g fill="url(#mrMarigoldOrangeV3)">
                        <circle cx="965" cy="21" r="7.5"/>
                        <circle cx="1035" cy="31" r="8"/>
                        <circle cx="1105" cy="34" r="8.5"/>
                        <circle cx="1175" cy="28" r="8"/>
                        <circle cx="1305" cy="20" r="7.5"/>
                        <circle cx="1375" cy="28" r="8"/>
                        <circle cx="1445" cy="28" r="8"/>
                        <circle cx="1515" cy="19" r="7.5"/>
                    </g>
                    <g fill="url(#mrMarigoldYellowV3)">
                        <circle cx="1000" cy="27" r="9"/>
                        <circle cx="1070" cy="34" r="9.5"/>
                        <circle cx="1140" cy="32" r="9.5"/>
                        <circle cx="1210" cy="23" r="9"/>
                        <circle cx="1340" cy="26" r="9"/>
                        <circle cx="1410" cy="30" r="9.5"/>
                        <circle cx="1480" cy="24" r="9"/>
                        <circle cx="1550" cy="14" r="8"/>
                    </g>

                    <!-- Mango Leaves (Aam ke Patte) -->
                    <g fill="url(#mrMangoLeafV3)">
                        <path d="M190,30 Q187,48 190,56 Q193,48 190,30 Z"/>
                        <path d="M340,16 Q337,38 340,46 Q343,38 340,16 Z"/>
                        <path d="M530,34 Q527,52 530,60 Q533,52 530,34 Z"/>
                        <path d="M1070,34 Q1067,52 1070,60 Q1073,52 1070,34 Z"/>
                        <path d="M1260,16 Q1257,38 1260,46 Q1263,38 1260,16 Z"/>
                        <path d="M1410,30 Q1407,48 1410,56 Q1413,48 1410,30 Z"/>
                    </g>

                    <!-- Swaying Hanging Bell 1 (Far Left) -->
                    <g class="mr-diwali-sway-1" transform="translate(220, 0)">
                        <line x1="10" y1="2" x2="10" y2="40" stroke="#ca8a04" stroke-width="1.8" stroke-dasharray="2.5,2.5"/>
                        <path d="M4,52 C4,44 6,40 10,40 C14,40 16,44 16,52 Z" fill="url(#mrToranGoldV3)"/>
                        <ellipse cx="10" cy="52" rx="6.5" ry="2.2" fill="#ca8a04"/>
                        <circle cx="10" cy="56" r="2" fill="#fde047"/>
                    </g>

                    <!-- Swaying Hanging Lantern 2 (Mid-Left) -->
                    <g class="mr-diwali-sway-2" transform="translate(480, 0)">
                        <line x1="12" y1="2" x2="12" y2="34" stroke="#ca8a04" stroke-width="1.8"/>
                        <polygon points="12,34 22,46 12,58 2,46" fill="#e11d48" stroke="#fbbf24" stroke-width="1.5"/>
                        <polygon points="12,38 18,46 12,54 6,46" fill="#facc15"/>
                        <circle cx="12" cy="46" r="2.8" fill="#ffffff"/>
                        <line x1="7" y1="58" x2="4" y2="74" stroke="#f59e0b" stroke-width="1.4"/>
                        <line x1="12" y1="58" x2="12" y2="78" stroke="#e11d48" stroke-width="1.5"/>
                        <line x1="17" y1="58" x2="20" y2="74" stroke="#f59e0b" stroke-width="1.4"/>
                    </g>

                    <!-- Swaying Hanging Lantern 3 (Mid-Right) -->
                    <g class="mr-diwali-sway-3" transform="translate(1120, 0)">
                        <line x1="12" y1="2" x2="12" y2="34" stroke="#ca8a04" stroke-width="1.8"/>
                        <polygon points="12,34 22,46 12,58 2,46" fill="#7c3aed" stroke="#fbbf24" stroke-width="1.5"/>
                        <polygon points="12,38 18,46 12,54 6,46" fill="#facc15"/>
                        <circle cx="12" cy="46" r="2.8" fill="#ffffff"/>
                        <line x1="7" y1="58" x2="4" y2="74" stroke="#f59e0b" stroke-width="1.4"/>
                        <line x1="12" y1="58" x2="12" y2="78" stroke="#7c3aed" stroke-width="1.5"/>
                        <line x1="17" y1="58" x2="20" y2="74" stroke="#f59e0b" stroke-width="1.4"/>
                    </g>

                    <!-- Swaying Hanging Bell 4 (Far Right) -->
                    <g class="mr-diwali-sway-4" transform="translate(1380, 0)">
                        <line x1="10" y1="2" x2="10" y2="40" stroke="#ca8a04" stroke-width="1.8" stroke-dasharray="2.5,2.5"/>
                        <path d="M4,52 C4,44 6,40 10,40 C14,40 16,44 16,52 Z" fill="url(#mrToranGoldV3)"/>
                        <ellipse cx="10" cy="52" rx="6.5" ry="2.2" fill="#ca8a04"/>
                        <circle cx="10" cy="56" r="2" fill="#fde047"/>
                    </g>

                    <!-- ================= CENTER "HAPPY DIWALI" CREST WITH INTEGRATED GLOWING DIYA ================= -->
                    <g class="mr-diwali-crest-sway" transform="translate(800, 26)">
                        <ellipse cx="0" cy="0" rx="160" ry="22" fill="rgba(245, 158, 11, 0.45)" filter="url(#mrCrestGlowV3)"/>

                        <!-- Center Ribbon Plaque -->
                        <path d="M-150,-16 L150,-16 Q162,-16 158,0 Q154,16 144,16 L-144,16 Q-154,16 -158,0 Q-162,-16 -150,-16 Z" 
                              fill="url(#mrCrestRedV3)" stroke="url(#mrToranGoldV3)" stroke-width="2"/>

                        <!-- Plaque Ends -->
                        <path d="M-154,-10 L-170,-10 L-160,-1 L-170,8 L-152,8 Z" fill="#4c0519" stroke="#ca8a04" stroke-width="1"/>
                        <path d="M154,-10 L170,-10 L160,-1 L170,8 L152,8 Z" fill="#4c0519" stroke="#ca8a04" stroke-width="1"/>

                        <!-- Inner Border -->
                        <rect x="-142" y="-12" width="284" height="24" rx="4" fill="none" stroke="#fde047" stroke-width="0.8" stroke-dasharray="4,2"/>

                        <!-- Festive Typography: शुभ दीपावली • HAPPY DIWALI -->
                        <text x="0" y="5" font-family="'Noto Sans', 'Segoe UI', -apple-system, sans-serif" font-weight="900" font-size="13.5" 
                              text-anchor="middle" fill="url(#mrToranGoldV3)" letter-spacing="1.8" filter="drop-shadow(0 2px 2px rgba(0,0,0,0.9))">
                            ✨ शुभ दीपावली • HAPPY DIWALI ✨
                        </text>

                        <!-- Central Hanging Sacred Diya Emblem (Under Crest) -->
                        <g transform="translate(0, 16)">
                            <line x1="0" y1="0" x2="0" y2="12" stroke="#ca8a04" stroke-width="1.8"/>
                            <!-- Mini Diya Base -->
                            <path d="M-12,20 C-10,28 10,28 12,20 C12,19 -12,19 -12,20 Z" fill="#d97706" stroke="#7c2d12" stroke-width="1"/>
                            <ellipse cx="0" cy="20" rx="12" ry="2.5" fill="#fde047"/>
                            <!-- Animated Flame -->
                            <path class="mr-diwali-flame" d="M0,6 C-3,11 -4,15 -4,18 C-4,21 -2,22 0,22 C2,22 4,21 4,18 C4,15 3,11 0,6 Z" fill="#ffedd5"/>
                            <path class="mr-diwali-flame-inner" d="M0,9 C-1.8,13 -2,16 -2,18 C-2,20 -1,21 0,21 C1,21 2,20 2,18 C2,16 1.8,13 0,9 Z" fill="#f59e0b"/>
                        </g>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Visitor Festive Preference Toggle Pill -->
        <button id="mr-diwali-toggle-btn" class="mr-diwali-toggle-btn" type="button" aria-label="Toggle Diwali Theme Effects">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C12 2 8 7 8 11C8 13.2 9.8 15 12 15C14.2 15 16 13.2 16 11C16 7 12 2 12 2ZM4 17C4 16.4 4.4 16 5 16H19C19.6 16 20 16.4 20 17C20 19.8 16.4 22 12 22C7.6 22 4 19.8 4 17Z"/>
            </svg>
            <span class="mr-diwali-toggle-label">Diwali Theme: ON</span>
        </button>
        <!-- ================= /Merarashifal Grand Diwali Festive Overlay ================= -->
        <?php
    }
}

MR_Diwali_Theme_Overlay::init();
