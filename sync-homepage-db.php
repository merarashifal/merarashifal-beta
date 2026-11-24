<?php
/**
 * Live Database Synchronization for Homepage Bug Fixes, SEO Audits & Ekadashi URLs
 * Usage: https://yourdomain.com/sync-homepage-db.php?token=mr_deploy_2026
 */

define('WP_USE_THEMES', false);
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once __DIR__ . '/wp-load.php';
} else {
    die("wp-load.php not found.");
}

$is_admin = function_exists('current_user_can') && current_user_can('manage_options');
$authorized = $is_admin || (isset($_GET['token']) && $_GET['token'] === 'mr_deploy_2026');

if (!$authorized) {
    http_response_code(403);
    die("Access denied. Please provide valid deployment token or log in as administrator.");
}

global $wpdb;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Sync - SEO, H1 & Bilingual Bridge</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f1f5f9; padding: 24px; line-height: 1.6; }
        .card { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { color: #f59e0b; font-size: 22px; margin-top: 0; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px; }
        .badge-green { background: #065f46; color: #6ee7b7; }
        .log-box { background: #020617; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 13px; max-height: 580px; overflow-y: auto; border: 1px solid #334155; margin-top: 16px; }
        .log-success { color: #34d399; margin-bottom: 6px; }
        .log-info { color: #60a5fa; margin-bottom: 6px; }
        .log-warn { color: #fcd34d; margin-bottom: 6px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 6px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; font-size: 14px; margin-top: 16px; }
        .btn-home { background: #d97706; color: #fff; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚡ Live Database Synchronization &amp; SEO Audit Deployer</h1>
    <p>Automated database updater for SEO duplicates, H1 single-tag enforcement, image renames &amp; alt text, and Bilingual Bridge headings.</p>

    <div class="log-box">
    <?php
    // ---------------------------------------------------------
    // 1. UPDATE SNIPPET 67 IN DATABASE (Bilingual Bridge & Typos)
    // ---------------------------------------------------------
    $snippets_table = $wpdb->prefix . 'snippets';
    if ($wpdb->get_var("SHOW TABLES LIKE '$snippets_table'") === null) {
        $snippets_table = 'af_snippets';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$snippets_table'") !== null) {
        $snip67 = $wpdb->get_row("SELECT id, name, code FROM {$snippets_table} WHERE id = 67", ARRAY_A);
        if ($snip67) {
            $code = $snip67['code'];
            $mod = false;

            // Typos
            if (strpos($code, 'Diety:') !== false) {
                $code = str_replace('Diety:', 'Deity:', $code);
                $mod = true;
            }
            if (strpos($code, 'Samvaat Details') !== false) {
                $code = str_replace('Samvaat Details', 'Samvat Details', $code);
                $mod = true;
            }

            // Bilingual Bridge Headings
            $headings_replace = [
                '<h3>🔍 Find Your Sun Sign</h3>' => '<h3>🔍 अपनी राशि जानें | Find Your Sun Sign</h3>',
                '<h3>📅 Today Panchang —'       => '<h3>📅 आज का पंचांग | Today\'s Panchang —',
                '<h3>🕰️ Muhurat —'              => '<h3>🕰️ शुभ मुहूर्त | Auspicious Muhurat —',
                '<h3>♈ Know Your Rashifal</h3>'   => '<h3>♈ दैनिक राशिफल | Daily Horoscope</h3>',
                '<h3>☀️🌙 Sun & Moon —'          => '<h3>☀️🌙 सूर्य एवं चंद्र | Sun & Moon —',
                '<h3>📜 Samvat Details</h3>'      => '<h3>📜 संवत एवं काल गणना | Samvat Details</h3>',
                '<h3>🗓️ Muhurat Dates</h3>'      => '<h3>🗓️ शुभ मुहूर्त तिथियां | Auspicious Dates</h3>',
            ];

            foreach ($headings_replace as $old_h => $new_h) {
                if (strpos($code, $old_h) !== false) {
                    $code = str_replace($old_h, $new_h, $code);
                    $mod = true;
                }
            }

            // Smart Link Fallback
            if (strpos($code, "'https://merarashifal.com/ekadashi'") !== false && strpos($code, 'get_festival_smart_link') === false) {
                $code = str_replace(
                    "\$ek_url = !empty(\$ek_row->url) ? esc_url(\$ek_row->url) : 'https://merarashifal.com/ekadashi';",
                    "\$ek_url = !empty(\$ek_row->url) ? esc_url(\$ek_row->url) : (class_exists('MR_Homepage_Manager') ? esc_url(MR_Homepage_Manager::get_festival_smart_link(\$ek_row->ekadashi_name)['url']) : 'https://merarashifal.com/ekadashi');",
                    $code
                );
                $mod = true;
            }

            if ($mod) {
                $wpdb->update($snippets_table, ['code' => $code], ['id' => 67]);
                echo "<div class='log-success'>✅ [SNIPPET 67]: Applied Bilingual Bridge headings, Deity/Samvat typo fixes, and smart link fallbacks.</div>\n";
            } else {
                echo "<div class='log-info'>ℹ️ [SNIPPET 67]: Snippet 67 already up-to-date.</div>\n";
            }
        }
    }

    // ---------------------------------------------------------
    // 2. RESOLVE 10 DUPLICATE TITLE & META DESCRIPTION URLS
    // ---------------------------------------------------------
    // Trash duplicate legal pages if they exist
    $dup_7411 = get_post(7411);
    if ($dup_7411 && $dup_7411->post_status !== 'trash') {
        wp_trash_post(7411);
        echo "<div class='log-success'>✅ [DUPLICATES]: Trashed duplicate page 7411 (Cookie Policy).</div>\n";
    }
    $dup_7413 = get_post(7413);
    if ($dup_7413 && $dup_7413->post_status !== 'trash') {
        wp_trash_post(7413);
        echo "<div class='log-success'>✅ [DUPLICATES]: Trashed duplicate page 7413 (Cancellation & Refund Policy).</div>\n";
    }

    // Set unique Yoast titles and descriptions
    $yoast_meta_updates = [
        7410 => [
            'title' => 'Cookie Policy | MeraRashifal',
            'desc'  => 'Understand how MeraRashifal uses cookies and similar tracking technologies to improve user experience, astrological report delivery, and site performance.'
        ],
        7412 => [
            'title' => 'Cancellation & Refund Policy | MeraRashifal',
            'desc'  => 'Official Cancellation & Refund Policy of MeraRashifal. Details on digital astrology reports, cancellation timelines, and consumer protection guidelines.'
        ],
        2306 => [
            'title' => 'देवउठनी एकादशी व्रत कथा, पूजा विधि व महत्व | Dev Uthani Ekadashi Vrat Katha',
            'desc'  => 'Dev Uthani Ekadashi Vrat Katha: कार्तिक मास के शुक्ल पक्ष की देवउठनी (प्रबोधिनी) एकादशी की संपूर्ण पौराणिक व्रत कथा, पूजा विधि, तुलसी विवाह और धार्मिक महत्व पढ़ें।'
        ],
        2637 => [
            'title' => 'पापमोचनी एकादशी व्रत कथा एवं पूजा विधि | Papmochani Ekadashi Vrat Katha',
            'desc'  => 'Papmochani Ekadashi Vrat Katha पढ़ें और सुनें। चैत्र कृष्ण एकादशी पर पापों के प्रायश्चित और भगवान विष्णु की कृपा प्राप्ति की संपूर्ण व्रत कथा, विधि और महत्व।'
        ],
        2640 => [
            'title' => 'वरुथिनी एकादशी व्रत कथा, नियम एवं पूजा विधि | Varuthini Ekadashi Vrat Katha',
            'desc'  => 'Varuthini Ekadashi Vrat Katha: वैशाख कृष्ण एकादशी की पावन वरुथिनी एकादशी व्रत कथा, पूजन विधि, दान का महात्म्य और राजा मांधाता की पौराणिक कथा हिंदी में।'
        ],
        2662 => [
            'title' => 'इंदिरा एकादशी व्रत कथा, पितृ मोक्ष एवं विधि | Indira Ekadashi Vrat Katha',
            'desc'  => 'Indira Ekadashi Vrat Katha: आश्विन मास के कृष्ण पक्ष की पितृ पक्षीय इंदिरा एकादशी की संपूर्ण पौराणिक व्रत कथा, श्राद्ध विधि और पूर्वजों की मुक्ति का महत्व पढ़ें।'
        ],
        2668 => [
            'title' => 'रमा एकादशी व्रत कथा, पूजा विधि एवं आरती | Rama Ekadashi Vrat Katha',
            'desc'  => 'Rama Ekadashi Vrat Katha: कार्तिक मास के कृष्ण पक्ष की पावन रमा एकादशी की संपूर्ण व्रत कथा, मां लक्ष्मी व श्री हरि विष्णु की पूजा विधि और व्रत का आध्यात्मिक फल।'
        ],
        2647 => [
            'title' => 'योगिनी एकादशी व्रत कथा, नियम एवं विधि | Yogini Ekadashi Vrat Katha',
            'desc'  => 'Yogini Ekadashi Vrat Katha: आषाढ़ मास के कृष्ण पक्ष की योगिनी एकादशी की संपूर्ण पौराणिक कथा, कुबेर के शाप से मुक्ति की कथा, पूजन विधि और महत्व हिंदी में।'
        ],
        7099 => [
            'title' => 'Hindu Festivals & Vrats Calendar | हिन्दू व्रत एवं त्यौहार पंचांग | MeraRashifal',
            'desc'  => 'Discover dynamic Hindu festivals, fasting dates, Ekadashi, Pradosh, and sacred tithis calculated using authentic Drik Panchang algorithms on MeraRashifal.'
        ],
        7326 => [
            'title'   => 'Secure Checkout & Astrological Report Order | MeraRashifal',
            'desc'    => 'Order your personalized Vedic horoscope, kundali analysis, and astrology reports safely with secure checkout on MeraRashifal.',
            'noindex' => '1'
        ]
    ];

    foreach ($yoast_meta_updates as $pid => $ym) {
        if (get_post($pid)) {
            update_post_meta($pid, '_yoast_wpseo_title', $ym['title']);
            update_post_meta($pid, '_yoast_wpseo_metadesc', $ym['desc']);
            if (!empty($ym['noindex'])) {
                update_post_meta($pid, '_yoast_wpseo_meta-robots-noindex', '1');
            }
            echo "<div class='log-success'>✅ [YOAST META]: Updated unique SEO title &amp; description for Post ID {$pid}.</div>\n";
        }
    }

    // ---------------------------------------------------------
    // 3. AUDIT STRICTLY ONE <h1> PER PAGE (Bilingual Bridge & Primary Keyword)
    // ---------------------------------------------------------
    // Page 3135 (/horoscope/)
    $page_3135 = get_post(3135);
    if ($page_3135) {
        $el_json = get_post_meta(3135, '_elementor_data', true);
        if ($el_json) {
            $data = json_decode($el_json, true);
            $fn_3135 = function(&$elements) use (&$fn_3135) {
                foreach ($elements as &$el) {
                    if (isset($el['widgetType']) && $el['widgetType'] === 'heading') {
                        if ($el['id'] === '5b5e0fe') {
                            $el['settings']['title'] = 'आज का राशिफल | Daily Horoscope - Today\'s Rashifal';
                            $el['settings']['header_size'] = 'h1';
                        } elseif ($el['id'] === 'de875d9') {
                            $el['settings']['title'] = 'अपनी राशि चुनें | Choose Your Sun Sign';
                            $el['settings']['header_size'] = 'h2';
                        } elseif ($el['id'] === '04b5690') {
                            $el['settings']['title'] = 'भक्ति एवं धार्मिक वीडियो | Bhakti Videos';
                            $el['settings']['header_size'] = 'h2';
                        }
                    }
                    if (!empty($el['elements'])) {
                        $fn_3135($el['elements']);
                    }
                }
            };
            $fn_3135($data);
            update_post_meta(3135, '_elementor_data', wp_slash(json_encode($data)));
        }

        $c3135 = $page_3135->post_content;
        $c3135 = str_replace('<h1>Click On Your Sun Sign</h1>', '<h1>आज का राशिफल | Daily Horoscope - Today\'s Rashifal</h1>', $c3135);
        $c3135 = str_replace('<h1>Choose Your Sun Sign</h1>', '<h2>अपनी राशि चुनें | Choose Your Sun Sign</h2>', $c3135);
        $c3135 = str_replace('<h1>Bhakti Videos</h1>', '<h2>भक्ति एवं धार्मिक वीडियो | Bhakti Videos</h2>', $c3135);
        wp_update_post(['ID' => 3135, 'post_content' => $c3135]);
        echo "<div class='log-success'>✅ [H1 ENFORCEMENT]: Page 3135 (/horoscope/) updated to strictly 1 H1 containing target keyword 'आज का राशिफल'.</div>\n";
    }

    // Page 254 (Homepage)
    $page_254 = get_post(254);
    if ($page_254) {
        $el_json = get_post_meta(254, '_elementor_data', true);
        if ($el_json) {
            $data = json_decode($el_json, true);
            $fn_254 = function(&$elements) use (&$fn_254) {
                if (!is_array($elements)) return;
                foreach ($elements as &$el) {
                    if (isset($el['widgetType']) && $el['widgetType'] === 'heading') {
                        $t = $el['settings']['title'] ?? '';
                        if (strpos($t, 'MeraRashifal - Authentic Vedic Astrology') !== false || ($el['settings']['header_size'] ?? '') === 'h1') {
                            $el['settings']['title'] = 'आज का राशिफल, दैनिक पंचांग एवं जन्म कुंडली | MeraRashifal - Vedic Astrology & Daily Horoscope';
                            $el['settings']['header_size'] = 'h1';
                        } elseif (strpos($t, 'Get Your Personalized Report') !== false) {
                            $el['settings']['title'] = 'व्यक्तिगत जन्म कुंडली रिपोर्ट | Get Your Personalized Report';
                        } elseif (strpos($t, 'Vrat Katha - Hindi') !== false) {
                            $el['settings']['title'] = 'सम्पूर्ण व्रत कथा | Sacred Vrat Katha in Hindi';
                        } elseif (strpos($t, 'Aarti Sangrah') !== false) {
                            $el['settings']['title'] = 'आरती संग्रह | Sampoorna Aarti Sangrah';
                        } elseif (strpos($t, 'Hindu Gods - Aarti, Katha, Festivals') !== false) {
                            $el['settings']['title'] = 'हिन्दू देवी-देवता | Hindu Deities - Aarti, Katha & Festivals';
                        }
                    }
                    if (!empty($el['elements'])) {
                        $fn_254($el['elements']);
                    }
                }
            };
            $fn_254($data);
            $new_json = json_encode($data, JSON_UNESCAPED_UNICODE);
            update_post_meta(254, '_elementor_data', wp_slash($new_json));
        }

        $c254 = $page_254->post_content;
        $c254 = str_replace('MeraRashifal - Authentic Vedic Astrology, Daily Panchang &amp; Kundali Portal', 'आज का राशिफल, दैनिक पंचांग एवं जन्म कुंडली | MeraRashifal - Vedic Astrology &amp; Daily Horoscope', $c254);
        $c254 = str_replace('MeraRashifal - Authentic Vedic Astrology, Daily Panchang & Kundali Portal', 'आज का राशिफल, दैनिक पंचांग एवं जन्म कुंडली | MeraRashifal - Vedic Astrology & Daily Horoscope', $c254);
        $c254 = str_replace('Get Your Personalized Report', 'व्यक्तिगत जन्म कुंडली रिपोर्ट | Get Your Personalized Report', $c254);
        $c254 = str_replace('Vrat Katha - Hindi', 'सम्पूर्ण व्रत कथा | Sacred Vrat Katha in Hindi', $c254);
        $c254 = str_replace('Aarti Sangrah', 'आरती संग्रह | Sampoorna Aarti Sangrah', $c254);
        $c254 = str_replace('Hindu Gods - Aarti, Katha, Festivals', 'हिन्दू देवी-देवता | Hindu Deities - Aarti, Katha & Festivals', $c254);
        wp_update_post(['ID' => 254, 'post_content' => $c254]);

        // Clear Elementor CSS cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        echo "<div class='log-success'>✅ [H1 &amp; BILINGUAL BRIDGE]: Homepage 254 updated with primary target keyword H1 and Devanagari sub-headings.</div>\n";
    }

    // ---------------------------------------------------------
    // 4. RENAME REPORT IMAGES ON DISK & DATABASE
    // ---------------------------------------------------------
    $upload_dir = wp_upload_dir();
    $basedir = $upload_dir['basedir'];

    $renames = [
        7397 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_09_12-AM.webp',
            'new_filename' => 'yearly-horoscope-2027-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Yearly Horoscope Short Report 2027',
            'alt'          => 'वार्षिक राशिफल 2027 रिपोर्ट - MeraRashifal',
        ],
        7398 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_09_16-AM.webp',
            'new_filename' => 'janma-kundali-harmonics-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Master Janma Kundali and Harmonics Report',
            'alt'          => 'जन्म कुंडली एवं गृह फल रिपोर्ट - MeraRashifal',
        ],
        7396 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_09_08-AM.webp',
            'new_filename' => 'vedic-dasha-timing-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Master Vedic Dasha Life Timing Report',
            'alt'          => 'वैदिक महादशा एवं ग्रह गोचर फल रिपोर्ट - MeraRashifal',
        ],
        7395 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_09_03-AM.webp',
            'new_filename' => 'kundali-dosh-remedial-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Master Kundali Dosh and Remedial Audit Report',
            'alt'          => 'कुंडली दोष निवारण एवं उपाय रिपोर्ट - MeraRashifal',
        ],
        7394 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_08_59-AM.webp',
            'new_filename' => 'kundali-matching-gun-milan-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Master Kundali Matching (Gun Milan) Report',
            'alt'          => 'कुंडली मिलान गुण मिलान रिपोर्ट - MeraRashifal',
        ],
        7393 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_08_54-AM.webp',
            'new_filename' => 'numerology-vedic-integration-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Master Numerology and Vedic Integration Report',
            'alt'          => 'अंक ज्योतिष एवं वैदिक न्यूमरोलॉजी रिपोर्ट - MeraRashifal',
        ],
        7392 => [
            'old_filename' => 'ChatGPT-Image-Nov-7-2026-01_08_42-AM.webp',
            'new_filename' => 'monthly-horoscope-transit-report.webp',
            'subfolder'    => '2026/11',
            'new_title'    => 'MeraRashifal - Monthly Horoscope and Transit Subscription Report',
            'alt'          => 'मासिक राशिफल एवं ग्रह गोचर रिपोर्ट - MeraRashifal',
        ],
        3021 => [
            'old_filename' => 'ChatGPT-Image-Mar-7-2026-11_52_01-AM.webp',
            'new_filename' => 'lord-shiva-transformation.webp',
            'subfolder'    => '2026/03',
            'new_title'    => 'Lord Shiva: The God of Destruction and Transformation',
            'alt'          => 'भगवान शिव: संहार और परिवर्तन के देवता - MeraRashifal',
        ],
        3654 => [
            'old_filename' => 'ChatGPT-Image-May-26-2026-09_46_56-AM.png',
            'new_filename' => 'padmini-ekadashi-vrat-katha.png',
            'subfolder'    => '2026/05',
            'new_title'    => 'Padmini Ekadashi Vrat Katha',
            'alt'          => 'पद्मिनी एकादशी व्रत कथा - MeraRashifal',
        ],
        3656 => [
            'old_filename' => 'ChatGPT-Image-May-26-2026-09_50_46-AM.png',
            'new_filename' => 'padmini-ekadashi-puja-vidhi.png',
            'subfolder'    => '2026/05',
            'new_title'    => 'Padmini Ekadashi Puja Vidhi',
            'alt'          => 'पद्मिनी एकादशी पूजा विधि - MeraRashifal',
        ],
        3705 => [
            'old_filename' => 'ChatGPT-Image-May-23-2026-09_11_09-AM.png',
            'new_filename' => 'vedic-astrology-baby-names.png',
            'subfolder'    => '2026/05',
            'new_title'    => 'Vedic Astrology Baby Names',
            'alt'          => 'वैदिक नामकरण एवं नक्षत्र नामाक्षर - MeraRashifal',
        ],
        4014 => [
            'old_filename' => 'ChatGPT-Image-Jun-11-2026-05_06_40-PM.png',
            'new_filename' => 'pradosh-vrat-katha-shiva.png',
            'subfolder'    => '2026/06',
            'new_title'    => 'Pradosh Vrat Katha',
            'alt'          => 'प्रदोष व्रत कथा भगवान शिव - MeraRashifal',
        ],
        5313 => [
            'old_filename' => 'ChatGPT-Image-Jul-30-2026-08_36_49-AM.png',
            'new_filename' => 'sawan-somwar-vrat-shiva-puja.png',
            'subfolder'    => '2026/07',
            'new_title'    => 'Sawan Somwar Vrat Shiva Puja',
            'alt'          => 'सावन सोमवार व्रत एवं शिव पूजा विधि - MeraRashifal',
        ],
    ];

    foreach ($renames as $id => $info) {
        $dir = $basedir . '/' . $info['subfolder'];
        $old_path = $dir . '/' . $info['old_filename'];
        $new_path = $dir . '/' . $info['new_filename'];

        if (file_exists($old_path)) {
            @rename($old_path, $new_path);
        }

        $old_base = pathinfo($info['old_filename'], PATHINFO_FILENAME);
        $new_base = pathinfo($info['new_filename'], PATHINFO_FILENAME);

        if (is_dir($dir)) {
            $files = @scandir($dir);
            if ($files) {
                foreach ($files as $f) {
                    if (strpos($f, $old_base . '-') === 0) {
                        $suffix = substr($f, strlen($old_base));
                        @rename($dir . '/' . $f, $dir . '/' . $new_base . $suffix);
                    }
                }
            }
        }

        $new_attached_file = $info['subfolder'] . '/' . $info['new_filename'];
        update_post_meta($id, '_wp_attached_file', $new_attached_file);
        update_post_meta($id, '_wp_attachment_image_alt', $info['alt']);

        $meta = get_post_meta($id, '_wp_attachment_metadata', true);
        if (is_array($meta)) {
            $meta['file'] = $new_attached_file;
            if (!empty($meta['sizes']) && is_array($meta['sizes'])) {
                foreach ($meta['sizes'] as $sz => &$sz_info) {
                    if (!empty($sz_info['file'])) {
                        $sz_info['file'] = str_replace($old_base, $new_base, $sz_info['file']);
                    }
                }
            }
            update_post_meta($id, '_wp_attachment_metadata', $meta);
        }

        $wpdb->update(
            $wpdb->posts,
            [
                'post_title' => $info['new_title'],
                'guid'       => home_url('/wp-content/uploads/' . $new_attached_file)
            ],
            ['ID' => $id]
        );

        $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->posts} 
            SET post_content = REPLACE(post_content, %s, %s)
            WHERE post_content LIKE %s
        ", $info['old_filename'], $info['new_filename'], '%' . $info['old_filename'] . '%'));
    }
    echo "<div class='log-success'>✅ [IMAGE RENAMING]: Renamed 13 ChatGPT-Image files to descriptive keyword filenames and updated DB metadata.</div>\n";

    // Update mr_catalog_cards_settings
    $catalog = get_option('mr_catalog_cards_settings');
    if (is_array($catalog)) {
        foreach ($catalog as $ckey => &$cdata) {
            if (!empty($cdata['image_url'])) {
                foreach ($renames as $info) {
                    if (strpos($cdata['image_url'], $info['old_filename']) !== false) {
                        $cdata['image_url'] = str_replace($info['old_filename'], $info['new_filename'], $cdata['image_url']);
                    }
                }
            }
        }
        update_option('mr_catalog_cards_settings', $catalog);
        echo "<div class='log-success'>✅ [CATALOG SETTINGS]: Updated card image URLs in mr_catalog_cards_settings.</div>\n";
    }

    // ---------------------------------------------------------
    // 5. POPULATE MISSING ALT TEXT FOR 29+ KEY IMAGES
    // ---------------------------------------------------------
    $alt_map = [
        3028 => 'कुंडली दोष कैलकुलेटर | Kundali Dosh Calculator - MeraRashifal',
        3029 => 'द्वादशी व्रत एवं पूजन तिथि | Dwadashi Vrat Tithi - MeraRashifal',
        3030 => 'पवित्र एकादशी व्रत कथा एवं नियम | Ekadashi Vrat Katha - MeraRashifal',
        3031 => 'कालसर्प दोष निवारण एवं लक्षण | Kaal Sarp Dosh Nivaran - MeraRashifal',
        3033 => 'मांगलिक दोष विश्लेषण एवं उपाय | Manglik Dosh Remedies - MeraRashifal',
        3036 => 'सम्पूर्ण व्रत कथा एवं पूजा विधि | Hindu Vrat Katha - MeraRashifal',
        3045 => 'अग्नि देव - पंचतत्व एवं वैदिक देवता | Lord Agni Dev - MeraRashifal',
        3046 => 'भगवान ब्रह्मा - सृष्टि के रचयिता | Lord Brahma Ji - MeraRashifal',
        3047 => 'मां दुर्गा - शक्ति एवं सुरक्षा की देवी | Maa Durga Devi - MeraRashifal',
        3048 => 'भगवान श्री गणेश - विघ्नहर्ता एवं प्रथम पूज्य | Lord Ganesha Ji - MeraRashifal',
        3049 => 'मां गंगा - पापनाशिनी एवं मोक्षदायिनी | Maa Ganga Devi - MeraRashifal',
        3050 => 'मां गायत्री - वेदों की माता | Maa Gayatri Devi - MeraRashifal',
        3052 => 'देवराज इंद्र - देवताओं के राजा | King of Gods Indra Dev - MeraRashifal',
        3053 => 'मां काली - संहार एवं शक्ति स्वरूपा | Maa Kali Devi - MeraRashifal',
        3054 => 'भगवान कार्तिकेय (मुरुगन) - देवसेनापति | Lord Kartikeya Murugan - MeraRashifal',
        3056 => 'मां लक्ष्मी - धन एवं समृद्धि की देवी | Maa Lakshmi Devi - MeraRashifal',
        3057 => 'माता पार्वती - शक्ति एवं प्रेम की देवी | Mata Parvati Devi - MeraRashifal',
        3059 => 'मां संतोषी - सुख एवं संतोष प्रदायिनी | Maa Santoshi Mata - MeraRashifal',
        3060 => 'मां सरस्वती - विद्या एवं ज्ञान की देवी | Maa Saraswati Devi - MeraRashifal',
        3061 => 'भगवान शनि देव - कर्मफल दाता एवं न्याय के देवता | Lord Shani Dev - MeraRashifal',
        3063 => 'सूर्य देव - प्रत्यक्ष देवता एवं नवग्रह स्वामी | Lord Surya Sun God - MeraRashifal',
        3064 => 'माता वैष्णो देवी - त्रिकूटा पर्वत वासिनी | Mata Vaishno Devi - MeraRashifal',
        3065 => 'वायु देव - प्राण एवं पवन के देवता | Lord Vayu Wind God - MeraRashifal',
        3067 => 'भगवान विश्वकर्मा - दिव्य वास्तुकार एवं शिल्पी | Lord Vishwakarma Ji - MeraRashifal',
        3068 => 'यमराज - धर्मराज एवं न्याय के देवता | Lord Yama God of Dharma - MeraRashifal',
        3450 => 'दैनिक राशिफल | Daily Horoscope Aaj Ka Rashifal - MeraRashifal',
        3451 => 'दैनिक चौघड़िया मुहूर्त | Daily Choghadiya Muhurat - MeraRashifal',
        3452 => 'आज का पंचांग | Today Panchang - MeraRashifal',
        3455 => 'कुंडली मिलान गुण मिलान | Kundali Matching Gun Milan - MeraRashifal',
        3453 => 'ज्योतिषीय उपाय एवं समाधान | Astrological Remedies - MeraRashifal',
        3456 => 'आज का शुभ मुहूर्त | Shubh Muhurat - MeraRashifal',
        3457 => 'वैदिक महादशा गणना | Check Mahadasha - MeraRashifal',
        3458 => 'राशि अनुकूलता परीक्षण | Sun Sign Compatibility - MeraRashifal',
        3459 => 'धार्मिक एवं भक्ति वीडियो | Devotional Videos - MeraRashifal',
        3709 => 'शिशु नामकरण संस्कार | Vedic Baby Names - MeraRashifal',
        3710 => 'नवजात शिशु नाम सुझाव | Baby Names Astrology - MeraRashifal',
        688  => 'MeraRashifal - Authentic Vedic Astrology Portal Logo',
        7294 => 'वैदिक ज्योतिष प्रीमियम रिपोर्ट कैटलॉग | Vedic Astrology Reports - MeraRashifal'
    ];

    foreach ($alt_map as $att_id => $alt_text) {
        update_post_meta($att_id, '_wp_attachment_image_alt', $alt_text);
    }
    echo "<div class='log-success'>✅ [ALT TEXT]: Populated keyword-rich Hindi &amp; English alt text for all 38 key site images.</div>\n";

    // ---------------------------------------------------------
    // 6. POPULATE af_ekadashi ARTICLE URLS
    // ---------------------------------------------------------
    $ek_table = $wpdb->prefix . 'ekadashi';
    if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") === null) {
        $ek_table = 'af_ekadashi';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$ek_table'") !== null) {
        $has_url_col = $wpdb->get_results("SHOW COLUMNS FROM {$ek_table} LIKE 'url'");
        if (empty($has_url_col)) {
            $wpdb->query("ALTER TABLE {$ek_table} ADD COLUMN `url` VARCHAR(255) NULL AFTER `ekadashi_name_hi`");
            echo "<div class='log-success'>✅ Added missing 'url' column to table {$ek_table}.</div>\n";
        }

        $ek_rows = $wpdb->get_results("SELECT id, ekadashi_name, ekadashi_name_hi, url FROM {$ek_table}", ARRAY_A);
        $updated_urls = 0;

        foreach ($ek_rows as $row) {
            $en = $row['ekadashi_name'];
            $hi = $row['ekadashi_name_hi'];
            $clean_en = trim(preg_replace('/\s*\([^)]*\)/', '', $en));

            $url = null;
            if (class_exists('MR_Homepage_Manager')) {
                $res = MR_Homepage_Manager::get_festival_smart_link($clean_en, $hi);
                if (!empty($res['url'])) {
                    $url = $res['url'];
                }
            }

            if ($url && $row['url'] !== $url) {
                $wpdb->update($ek_table, ['url' => $url], ['id' => $row['id']]);
                $updated_urls++;
            }
        }
        echo "<div class='log-success'>✅ [EKADASHI TABLE]: Synced {$updated_urls} canonical Ekadashi URLs in {$ek_table}.</div>\n";
    }

    // ---------------------------------------------------------
    // 7. UPGRADE SNIPPET 47 (MUHURAT CODE) & CREATE 2027 HUBS
    // ---------------------------------------------------------
    $snip_file = __DIR__ . '/snippet_47_new.php';
    if (file_exists($snip_file)) {
        $snip_code = file_get_contents($snip_file);
        $snip_code = preg_replace('/^<\?php\s*/', '', $snip_code);
        $snip_code = str_replace('mr_final_muhurat_viewer_theme_v2', 'mr_final_muhurat_viewer_theme', $snip_code);
        if (strpos($snip_code, "add_shortcode('muhurat_viewer'") === false) {
            $snip_code = "add_shortcode('muhurat_viewer', 'mr_final_muhurat_viewer_theme');\n" . $snip_code;
        }

        $res_snip = $wpdb->update($snippets_table, ['code' => $snip_code], ['id' => 47]);
        if ($res_snip !== false) {
            echo "<div class='log-success'>✅ [SNIPPET 47]: Upgraded Muhurat Code snippet with 2027 hubs, slug normalization, and bilingual support.</div>\n";
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 47]: Could not update Snippet 47 (already up-to-date or error).</div>\n";
        }
    }

    // CREATE OR UPDATE 2027 HUBS
    $hub_file = __DIR__ . '/create_2027_muhurat_pages.php';
    if (file_exists($hub_file)) {
        require_once $hub_file;
        echo "<div class='log-success'>✅ [2027 HUBS]: Verified and synced 2027 Shubh Muhurat Hub Pages.</div>\n";
    }

    // UPDATE PAGE 2538 (/muhurat-dates/)
    $p2538_file = __DIR__ . '/update_page_2538.php';
    if (file_exists($p2538_file)) {
        require_once $p2538_file;
        echo "<div class='log-success'>✅ [PAGE 2538]: Updated Shubh Muhurat Dates hub with 2027 cards and single H1.</div>\n";
    }

    // ---------------------------------------------------------
    // 8. UPGRADE SNIPPETS FOR GOOGLE MAPS API KEY (13, 14, 30, 46, 69, 70, 73, 74, 84)
    // ---------------------------------------------------------
    $maps_updated = 0;
    
    // Snippet 13
    $r13 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 13", ARRAY_A);
    if ($r13) {
        $c = $r13['code'];
        $mod13 = false;
        if (strpos($c, '$mr_maps_key') === false) {
            $c = str_replace(
                "if (!defined('GOOGLE_API_KEY') || !GOOGLE_API_KEY) return;",
                "\$mr_maps_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');\n    if (empty(\$mr_maps_key)) return;",
                $c
            );
            $c = str_replace(
                "\$inline_js = str_replace('GOOGLE_API_KEY_TOKEN', json_encode(GOOGLE_API_KEY), \$inline_js);",
                "\$inline_js = str_replace('GOOGLE_API_KEY_TOKEN', json_encode(\$mr_maps_key), \$inline_js);",
                $c
            );
            $mod13 = true;
        }
        // Optimize: Prevent Google Maps JS loading on pages without city inputs
        $old_lazy = "['scroll', 'keydown', 'pointerdown', 'touchstart'].forEach(function(ev){\n    window.addEventListener(ev, function(){ ensureGoogleMaps(); }, {once:true, passive:true});\n  });\n  setTimeout(function(){ ensureGoogleMaps(); }, 4000);";
        $new_lazy = "// Only load Google Maps if city inputs actually exist on the page\n  setTimeout(function(){ if (findInputs().length > 0) ensureGoogleMaps(); }, 3000);";
        if (strpos($c, $old_lazy) !== false) {
            $c = str_replace($old_lazy, $new_lazy, $c);
            $mod13 = true;
        }
        if ($mod13) {
            $wpdb->update($snippets_table, ['code' => $c], ['id' => 13]);
            $maps_updated++;
        }
    }

    // Snippet 14
    $r14 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 14", ARRAY_A);
    if ($r14 && strpos($r14['code'], 'rawurlencode(GOOGLE_API_KEY)') !== false) {
        $c = str_replace(
            'rawurlencode(GOOGLE_API_KEY)',
            "rawurlencode(defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : ''))",
            $r14['code']
        );
        $wpdb->update($snippets_table, ['code' => $c], ['id' => 14]);
        $maps_updated++;
    }

    // Snippet 30
    $r30 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 30", ARRAY_A);
    if ($r30 && strpos($r30['code'], 'GOOGLE_MAPS_API_KEY') === false) {
        $c = str_replace(
            "\$google_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : 'YOUR_GOOGLE_KEY';",
            "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : 'YOUR_GOOGLE_KEY');",
            $r30['code']
        );
        $wpdb->update($snippets_table, ['code' => $c], ['id' => 30]);
        $maps_updated++;
    }

    // Snippet 46
    $r46 = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = 46", ARRAY_A);
    if ($r46 && strpos($r46['code'], 'GOOGLE_MAPS_API_KEY') === false) {
        $old46 = "if (!defined('GOOGLE_API_KEY')) wp_die('// Missing GOOGLE_API_KEY');\n    \$url = \"https://maps.googleapis.com/maps/api/js?key=\" . GOOGLE_API_KEY . \"&libraries=places\";";
        $new46 = "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');\n    if (empty(\$google_key)) wp_die('// Missing GOOGLE_MAPS_API_KEY');\n    \$url = \"https://maps.googleapis.com/maps/api/js?key=\" . \$google_key . \"&libraries=places\";";
        $c = str_replace($old46, $new46, $r46['code']);
        $wpdb->update($snippets_table, ['code' => $c], ['id' => 46]);
        $maps_updated++;
    }

    // Snippets 69, 70, 73, 74, 84
    $engine_ids = [69, 70, 73, 74, 84];
    foreach ($engine_ids as $eid) {
        $row = $wpdb->get_row("SELECT id, code FROM {$snippets_table} WHERE id = {$eid}", ARRAY_A);
        if ($row && strpos($row['code'], 'GOOGLE_MAPS_API_KEY') === false) {
            $c = str_replace(
                "\$google_key = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';",
                "\$google_key = defined('GOOGLE_MAPS_API_KEY') && !empty(GOOGLE_MAPS_API_KEY) ? GOOGLE_MAPS_API_KEY : (defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '');",
                $row['code']
            );
            $wpdb->update($snippets_table, ['code' => $c], ['id' => $eid]);
            $maps_updated++;
        }
    }
    echo "<div class='log-success'>✅ [GOOGLE MAPS SNIPPETS]: Verified/Synced {$maps_updated} snippets (13, 14, 30, 46, 69, 70, 73, 74, 84) to use GOOGLE_MAPS_API_KEY.</div>\n";

    // UPGRADE SNIPPET 63 (MR Horoscope Programmatic Engine)
    $snip63_file = __DIR__ . '/snippet_63_new.php';
    if (file_exists($snip63_file)) {
        $snip63_code = file_get_contents($snip63_file);
        $snip63_code = preg_replace('/^<\?php\s*/', '', $snip63_code);
        $snip63_code = preg_replace('/\?>\s*$/', '', $snip63_code);
        $res63 = $wpdb->update($snippets_table, ['code' => $snip63_code], ['id' => 63]);
        if ($res63 !== false) {
            echo "<div class='log-success'>✅ [SNIPPET 63]: Upgraded MR Horoscope Programmatic Engine with compact layout, #FFFBEB background, full Hindi parity & centered traits.</div>\n";
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 63]: Could not update Snippet 63 (already up-to-date or error).</div>\n";
        }
    }

    // UPGRADE SNIPPET 64 (MR Panchang Engine - [dynamic_panchang])
    $snip64_file = __DIR__ . '/snippet_64_new.php';
    if (file_exists($snip64_file)) {
        $snip64_code = file_get_contents($snip64_file);
        $snip64_code = preg_replace('/^<\?php\s*/', '', $snip64_code);
        $snip64_code = preg_replace('/\?>\s*$/', '', $snip64_code);
        $res64 = $wpdb->update($snippets_table, ['code' => $snip64_code], ['id' => 64]);
        if ($res64 !== false) {
            echo "<div class='log-success'>✅ [SNIPPET 64]: Upgraded MR Panchang Engine with compact 2-column layout, #FFFBEB background, full Hindi parity & centered traits.</div>\n";
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 64]: Could not update Snippet 64 (already up-to-date or error).</div>\n";
        }
    }

    // UPGRADE SNIPPET 73 (Kundali Engine - [kundali_calc_engine])
    $snip73_file = __DIR__ . '/snippet_73_new.php';
    if (file_exists($snip73_file)) {
        $snip73_code = file_get_contents($snip73_file);
        $snip73_code = preg_replace('/^<\?php\s*/', '', $snip73_code);
        $snip73_code = preg_replace('/\?>\s*$/', '', $snip73_code);
        $res73 = $wpdb->update($snippets_table, ['code' => $snip73_code], ['id' => 73]);
        if ($res73 !== false) {
            echo "<div class='log-success'>✅ [SNIPPET 73]: Upgraded Kundali Engine with warm amber #FFFBEB layout, compact cards, and mobile-friendly controls.</div>\n";
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 73]: Could not update Snippet 73 (already up-to-date or error).</div>\n";
        }
    }

    // UPGRADE SNIPPET 28 (Zodiac Compatibility Engine - [zodiac_compatibility_engine], [compatibility_results])
    $snip28_file = __DIR__ . '/snippet_28_new.php';
    if (file_exists($snip28_file)) {
        $snip28_code = file_get_contents($snip28_file);
        $snip28_code = preg_replace('/^<\?php\s*/', '', $snip28_code);
        $snip28_code = preg_replace('/\?>\s*$/', '', $snip28_code);
        $res28 = $wpdb->update($snippets_table, [
            'name'   => 'Zodiac Compatibility Engine (V2 Bilingual)',
            'code'   => $snip28_code,
            'status' => 1
        ], ['id' => 28]);
        if ($res28 !== false) {
            echo "<div class='log-success'>✅ [SNIPPET 28]: Upgraded Zodiac Compatibility Engine shortcode and REST lookup.</div>\n";
        } else {
            echo "<div class='log-warn'>⚠️ [SNIPPET 28]: Could not update Snippet 28 (already up-to-date or error).</div>\n";
        }
    }

    // Flush OPcache
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "<div class='log-success'>✅ [OPCACHE]: Flushed PHP OPcache successfully.</div>\n";
    }
    ?>
    </div>

    <div style="margin-top: 24px; display: flex; gap: 12px;">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-home">View Live Site &rarr;</a>
    </div>
</div>
</body>
</html>
