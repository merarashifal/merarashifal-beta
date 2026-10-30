<?php
/**
 * VedicAstroAPI Integration Engine for Niyati
 * Computes planetary positions, current Vimshottari Mahadasha/Antardasha,
 * Manglik dosha, and Sade Sati status.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Vedic_API {

    const BASE_URL = 'https://api.vedicastroapi.com/v3-json/';

    /**
     * Get VedicAstroAPI key
     */
    public static function get_api_key() {
        $settings = get_option('niyati_chat_settings', []);
        if (!empty($settings['vedic_api_key'])) {
            return trim($settings['vedic_api_key']);
        }
        $fallback = get_option('mr_vedic_api_key', '');
        if (!empty($fallback)) {
            return trim($fallback);
        }
        return '8d9d828d-3f9b-531a-a640-88c00469c300';
    }

    /**
     * Test connection to VedicAstroAPI
     */
    public static function test_connection($api_key = null) {
        $key = $api_key ?: self::get_api_key();
        if (empty($key)) {
            return ['success' => false, 'message' => 'VedicAstroAPI Key is missing.'];
        }

        $url = add_query_arg([
            'dob'     => '01/01/2000',
            'tob'     => '12:00',
            'lat'     => '28.6139',
            'lon'     => '77.2090',
            'tz'      => '5.5',
            'api_key' => $key,
            'lang'    => 'en',
        ], self::BASE_URL . 'horoscope/planet-details');

        $response = wp_remote_get($url, ['timeout' => 15]);
        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code === 200 && (isset($json['status']) && $json['status'] == 200 || isset($json['response']))) {
            return [
                'success' => true,
                'message' => 'Successfully connected to VedicAstroAPI!',
                'sample'  => 'Planets retrieved successfully'
            ];
        }

        $err = $json['message'] ?? $json['error'] ?? ('HTTP Error ' . $code);
        return ['success' => false, 'message' => $err];
    }

    /**
     * Fetch complete Vedic Astrology profile for user
     * Caches result for 24 hours per unique birth profile.
     *
     * @param array $birth_data [name, dob (DD/MM/YYYY), tob (HH:MM), lat, lon, tz, lang]
     * @return array
     */
    public static function get_kundli_profile($birth_data) {
        $name = sanitize_text_field($birth_data['name'] ?? 'User');
        $dob  = sanitize_text_field($birth_data['dob'] ?? '');
        $tob  = sanitize_text_field($birth_data['tob'] ?? '');
        $lat  = floatval($birth_data['lat'] ?? 28.6139);
        $lon  = floatval($birth_data['lon'] ?? 77.2090);
        $tz   = floatval($birth_data['tz'] ?? 5.5);
        $lang = sanitize_text_field($birth_data['lang'] ?? 'hi');

        // Normalize DOB from YYYY-MM-DD to DD/MM/YYYY if needed
        if (strpos($dob, '-') !== false) {
            $parts = explode('-', $dob);
            if (count($parts) === 3 && strlen($parts[0]) === 4) {
                $dob = sprintf('%02d/%02d/%04d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
            }
        }

        // Cache Key
        $cache_key = 'niyati_k_' . md5("{$dob}_{$tob}_{$lat}_{$lon}_{$lang}");
        $cached = get_transient($cache_key);
        if ($cached && is_array($cached)) {
            $cached['name'] = $name;
            return $cached;
        }

        $api_key = self::get_api_key();
        $api_lang = ($lang === 'hi' ? 'hi' : 'en');
        $base_params = [
            'dob'     => $dob,
            'tob'     => $tob,
            'lat'     => (string)$lat,
            'lon'     => (string)$lon,
            'tz'      => (string)$tz,
            'api_key' => $api_key,
            'lang'    => $api_lang,
        ];

        // 1. Fetch Planet Details (Ascendant, Rashi, House Placements)
        $planet_url = add_query_arg($base_params, self::BASE_URL . 'horoscope/planet-details');
        $planet_res = wp_remote_get($planet_url, ['timeout' => 15]);
        $planets_raw = [];
        if (!is_wp_error($planet_res)) {
            $p_json = json_decode(wp_remote_retrieve_body($planet_res), true);
            $planets_raw = $p_json['response'] ?? [];
        }

        // 2. Fetch Mangal Dosha
        $mangal_url = add_query_arg($base_params, self::BASE_URL . 'dosha/mangal-dosh');
        $mangal_res = wp_remote_get($mangal_url, ['timeout' => 15]);
        $mangal_raw = [];
        if (!is_wp_error($mangal_res)) {
            $m_json = json_decode(wp_remote_retrieve_body($mangal_res), true);
            $mangal_raw = $m_json['response'] ?? [];
        }

        // 3. Fetch Sade Sati
        $sade_sati_url = add_query_arg($base_params, self::BASE_URL . 'dosha/sade-sati');
        $sade_sati_res = wp_remote_get($sade_sati_url, ['timeout' => 15]);
        $sade_sati_raw = [];
        if (!is_wp_error($sade_sati_res)) {
            $s_json = json_decode(wp_remote_retrieve_body($sade_sati_res), true);
            $sade_sati_raw = $s_json['response'] ?? [];
        }

        // Extract key planetary summary from planet-details
        $ascendant_sign = '';
        $moon_sign      = $planets_raw['rasi'] ?? '';
        $sun_sign       = '';
        $nakshatra      = $planets_raw['nakshatra'] ?? '';
        $clean_planets  = [];

        // In VedicAstroAPI planet-details:
        // index '0' is Ascendant (Lagna)
        // index '1' is Sun
        // index '2' is Moon
        if (isset($planets_raw['0']['zodiac'])) {
            $ascendant_sign = $planets_raw['0']['zodiac'];
        }
        if (isset($planets_raw['1']['zodiac'])) {
            $sun_sign = $planets_raw['1']['zodiac'];
        }
        if (empty($moon_sign) && isset($planets_raw['2']['zodiac'])) {
            $moon_sign = $planets_raw['2']['zodiac'];
        }

        for ($i = 0; $i <= 9; $i++) {
            $idx = (string)$i;
            if (isset($planets_raw[$idx])) {
                $p = $planets_raw[$idx];
                $p_name  = $p['full_name'] ?? ($p['name'] ?? "Planet_{$i}");
                $sign    = $p['zodiac'] ?? '';
                $house   = $p['house'] ?? '';
                $deg     = isset($p['local_degree']) ? round($p['local_degree'], 1) : '';
                $is_retro = !empty($p['retro']);

                $clean_planets[$p_name] = [
                    'sign'       => $sign,
                    'house'      => $house,
                    'degree'     => $deg,
                    'nakshatra'  => $p['nakshatra'] ?? '',
                    'retrograde' => $is_retro,
                    'avastha'    => $p['basic_avastha'] ?? '',
                ];
            }
        }

        // Dasha from planet-details (or fallback)
        $current_dasa_str = $planets_raw['current_dasa'] ?? ($planets_raw['birth_dasa'] ?? '');
        $current_dasa_time = $planets_raw['current_dasa_time'] ?? '';

        // Structure doshas
        $has_mangal = !empty($mangal_raw['has_mangal_dosha']) || !empty($mangal_raw['is_dosha_present']);
        $mangal_percentage = $mangal_raw['dosha_percentage'] ?? ($has_mangal ? 'Present' : 'None');

        $is_sade_sati = !empty($sade_sati_raw['is_sade_sati_present']) || !empty($sade_sati_raw['status']);
        $sade_sati_phase = $sade_sati_raw['current_phase'] ?? ($is_sade_sati ? 'Active' : 'No active Sade Sati');

        $profile = [
            'name'             => $name,
            'dob'              => $dob,
            'tob'              => $tob,
            'pob_coords'       => "Lat: {$lat}, Lon: {$lon}",
            'ascendant'        => $ascendant_sign ?: 'Lagna',
            'moon_sign'        => $moon_sign ?: 'Rashi',
            'sun_sign'         => $sun_sign,
            'nakshatra'        => $nakshatra,
            'lucky_gem'        => $planets_raw['lucky_gem'] ?? [],
            'planets_summary'  => $clean_planets,
            'current_dasha'    => [
                'dasha_string' => $current_dasa_str,
                'until'        => $current_dasa_time,
            ],
            'doshas'           => [
                'mangal_dosha' => [
                    'present'    => $has_mangal,
                    'percentage' => $mangal_percentage,
                ],
                'sade_sati'    => [
                    'present'    => $is_sade_sati,
                    'phase'      => $sade_sati_phase,
                ],
            ],
            'cached_at'        => time(),
        ];

        // Cache for 30 days (astronomical birth charts do not change)
        set_transient($cache_key, $profile, 30 * DAY_IN_SECONDS);

        return $profile;
    }
}
