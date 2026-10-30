<?php
/**
 * VedicAstroAPI Client & Cache Engine
 * Connects to https://api.vedicastroapi.com/v3-json/
 * Fetches birth charts, planetary positions, dashas, doshas, and panchang.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Vedic_API {

    const BASE_URL = 'https://api.vedicastroapi.com/v3-json/';

    /**
     * Get configured VedicAstroAPI key
     */
    public static function get_api_key() {
        if (function_exists('get_option')) {
            $key = get_option('mr_vedic_api_key', '');
            if (empty($key)) {
                $key = get_option('vedicastro_api_key', '');
            }
            if (!empty($key)) {
                return $key;
            }
        }
        return '8d9d828d-3f9b-531a-a640-88c00469c300';
    }

    /**
     * Get preferred chart style (north / south)
     */
    public static function get_chart_style() {
        return function_exists('get_option') ? get_option('mr_vedic_chart_style', 'north') : 'north';
    }

    /**
     * Test connection to VedicAstroAPI
     */
    public static function test_connection($api_key = null) {
        $key = $api_key ?: self::get_api_key();
        if (empty($key)) {
            return ['success' => false, 'message' => 'VedicAstroAPI Key is not configured yet.'];
        }

        // Test with a sample planet-details call (Delhi coordinates)
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
                'sample'  => 'Planets retrieved: ' . count($json['response'] ?? [])
            ];
        }

        $err = $json['message'] ?? $json['error'] ?? ('HTTP Error ' . $code);
        return ['success' => false, 'message' => $err];
    }

    /**
     * Resolve Place of Birth (POB) string to Latitude, Longitude and Timezone
     */
    public static function resolve_coordinates($pob) {
        $pob = trim($pob);
        if (empty($pob)) {
            return ['lat' => 28.6139, 'lon' => 77.2090, 'tz' => 5.5, 'name' => 'New Delhi, India'];
        }

        $cache_key = 'mr_geo_' . md5(strtolower($pob));
        $cached = get_transient($cache_key);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        // Common defaults lookup dictionary for fast resolution
        $known_places = [
            'meerut' => ['lat' => 28.9845, 'lon' => 77.7064, 'tz' => 5.5, 'name' => 'Meerut, Uttar Pradesh, India'],
            'delhi' => ['lat' => 28.6139, 'lon' => 77.2090, 'tz' => 5.5, 'name' => 'Delhi, India'],
            'new delhi' => ['lat' => 28.6139, 'lon' => 77.2090, 'tz' => 5.5, 'name' => 'New Delhi, India'],
            'noida' => ['lat' => 28.5355, 'lon' => 77.3910, 'tz' => 5.5, 'name' => 'Noida, Uttar Pradesh, India'],
            'lucknow' => ['lat' => 26.8467, 'lon' => 80.9462, 'tz' => 5.5, 'name' => 'Lucknow, Uttar Pradesh, India'],
            'kanpur' => ['lat' => 26.4499, 'lon' => 80.3319, 'tz' => 5.5, 'name' => 'Kanpur, Uttar Pradesh, India'],
            'mumbai' => ['lat' => 19.0760, 'lon' => 72.8777, 'tz' => 5.5, 'name' => 'Mumbai, India'],
            'bangalore' => ['lat' => 12.9716, 'lon' => 77.5946, 'tz' => 5.5, 'name' => 'Bengaluru, India'],
            'bengaluru' => ['lat' => 12.9716, 'lon' => 77.5946, 'tz' => 5.5, 'name' => 'Bengaluru, India'],
            'kolkata' => ['lat' => 22.5726, 'lon' => 88.3639, 'tz' => 5.5, 'name' => 'Kolkata, India'],
            'chennai' => ['lat' => 13.0827, 'lon' => 80.2707, 'tz' => 5.5, 'name' => 'Chennai, India'],
            'hyderabad' => ['lat' => 17.3850, 'lon' => 78.4867, 'tz' => 5.5, 'name' => 'Hyderabad, India'],
            'pune' => ['lat' => 18.5204, 'lon' => 73.8567, 'tz' => 5.5, 'name' => 'Pune, India'],
            'ahmedabad' => ['lat' => 23.0225, 'lon' => 72.5714, 'tz' => 5.5, 'name' => 'Ahmedabad, India'],
            'jaipur' => ['lat' => 26.9124, 'lon' => 75.7873, 'tz' => 5.5, 'name' => 'Jaipur, India'],
            'toronto' => ['lat' => 43.6532, 'lon' => -79.3832, 'tz' => -4.0, 'name' => 'Toronto, Canada'],
            'vancouver' => ['lat' => 49.2827, 'lon' => -123.1207, 'tz' => -7.0, 'name' => 'Vancouver, Canada'],
            'london' => ['lat' => 51.5074, 'lon' => -0.1278, 'tz' => 1.0, 'name' => 'London, UK'],
            'new york' => ['lat' => 40.7128, 'lon' => -74.0060, 'tz' => -4.0, 'name' => 'New York, USA'],
        ];

        $ttl = defined('MONTH_IN_SECONDS') ? MONTH_IN_SECONDS : 86400 * 30;
        $lower_pob = strtolower($pob);
        foreach ($known_places as $city_key => $coords) {
            if (strpos($lower_pob, $city_key) !== false) {
                set_transient($cache_key, $coords, $ttl);
                return $coords;
            }
        }

        // Free OpenStreetMap Nominatim lookup
        $geo_url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($pob) . '&limit=1';
        $res = wp_remote_get($geo_url, [
            'timeout' => 8,
            'headers' => ['User-Agent' => 'MeraRashifal-Astro-Engine/1.0']
        ]);

        if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
            $data = json_decode(wp_remote_retrieve_body($res), true);
            if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
                $lat = floatval($data[0]['lat']);
                $lon = floatval($data[0]['lon']);

                // Approximate timezone from longitude (or India default if in range)
                $tz = ($lon >= 68.0 && $lon <= 97.0 && $lat >= 8.0 && $lat <= 37.0) ? 5.5 : round($lon / 15.0);

                $resolved = [
                    'lat'  => round($lat, 4),
                    'lon'  => round($lon, 4),
                    'tz'   => $tz,
                    'name' => $data[0]['display_name'] ?? $pob
                ];
                set_transient($cache_key, $resolved, $ttl);
                return $resolved;
            }
        }

        // Fallback to New Delhi if cannot be resolved
        return ['lat' => 28.6139, 'lon' => 77.2090, 'tz' => 5.5, 'name' => $pob];
    }

    /**
     * Fetch North or South Indian Chart Image (Base64/SVG)
     * e.g. div=D1, D9, D10, D7
     */
    public static function get_chart_image($dob, $tob, $lat, $lon, $tz, $div = 'D1', $style = null, $custom_params = []) {
        $style = $style ?: self::get_chart_style();
        $formatted_dob = self::format_dob($dob);
        $formatted_tob = self::format_tob($tob);

        $params = array_merge([
            'dob'              => $formatted_dob,
            'tob'              => $formatted_tob,
            'lat'              => $lat,
            'lon'              => $lon,
            'tz'               => $tz,
            'div'              => $div,
            'style'            => $style,
            'stroke'           => 2,
            'color'            => '%23ff850b3d',
            'font_size'        => 14,
            'font_style'       => 'Nunito',
            'colorful_planets' => 'true',
            'format'           => 'base64',
            'size'             => 350,
            'show_degree'      => 'true',
            'lang'             => 'en',
        ], $custom_params);

        return self::cached_request('horoscope/chart-image', $params);
    }

    /**
     * Fetch full planet details (signs, degrees, nakshatra, house)
     */
    public static function get_planet_details($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('horoscope/planet-details', $params);
    }

    /**
     * Fetch active Mahadasha & Antardasha
     */
    public static function get_current_mahadasha($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('dashas/current-maha-dasha', $params);
    }

    /**
     * Fetch complete Vimshottari Mahadasha timeline
     */
    public static function get_maha_dasha($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('dashas/maha-dasha', $params);
    }

    /**
     * Fetch Monthly Panchang & transit windows
     */
    public static function get_monthly_panchang($month, $year, $lat, $lon, $tz) {
        $params = [
            'month' => $month,
            'year'  => $year,
            'lat'   => $lat,
            'lon'   => $lon,
            'tz'    => $tz,
            'lang'  => 'en',
        ];

        return self::cached_request('panchang/monthly-panchang', $params);
    }

    /**
     * Fetch Gemstone suggestion
     */
    public static function get_gem_suggestion($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('extended-horoscope/gem-suggestion', $params);
    }

    /**
     * Fetch Rudraksha suggestion from VedicAstroAPI
     */
    public static function get_rudraksh_suggestion($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('extended-horoscope/rudraksha-suggestion', $params);
    }

    /**
     * Fetch Shadbala strengths from VedicAstroAPI
     */
    public static function get_shad_bala($dob, $tob, $lat, $lon, $tz) {
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request('extended-horoscope/shad-bala', $params);
    }

    /**
     * Fetch Dosha analysis (mangal-dosh, kaalsarp-dosh, pitra-dosh)
     */
    public static function get_dosha($dosha_type, $dob, $tob, $lat, $lon, $tz) {
        $endpoint = 'dosha/' . sanitize_key($dosha_type);
        $params = [
            'dob'  => self::format_dob($dob),
            'tob'  => self::format_tob($tob),
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => 'en',
        ];

        return self::cached_request($endpoint, $params);
    }

    /**
     * Parallel batch fetch using curl_multi with chunking and automatic retry failsafe.
     * Prevents server rate-limits by executing in safe concurrent windows (5-6 requests per chunk),
     * and automatically retries any failed endpoints up to 2 times with exponential backoff.
     *
     * @param array $endpoint_map Array of alias => endpoint path
     * @param string $dob
     * @param string $tob
     * @param float $lat
     * @param float $lon
     * @param float $tz
     * @param int $chunk_size Number of concurrent requests per burst (default: 6)
     * @return array Array of alias => parsed JSON response
     */
    public static function fetch_batch_parallel($endpoint_map, $dob, $tob, $lat, $lon, $tz, $chunk_size = 6) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return [];
        }

        $base_params = [
            'dob'     => self::format_dob($dob),
            'tob'     => self::format_tob($tob),
            'lat'     => $lat,
            'lon'     => $lon,
            'tz'      => $tz,
            'api_key' => $api_key,
            'lang'    => 'en'
        ];

        $results = [];
        $to_fetch = [];
        $ttl = defined('MONTH_IN_SECONDS') ? MONTH_IN_SECONDS : 86400 * 30;

        // 1. Check WP transients first
        $json_enc = function_exists('wp_json_encode') ? 'wp_json_encode' : 'json_encode';
        foreach ($endpoint_map as $alias => $ep) {
            $cache_key = 'mr_vedic_' . md5($ep . '_' . $json_enc($base_params));
            $cached = function_exists('get_transient') ? get_transient($cache_key) : false;
            if ($cached !== false) {
                $results[$alias] = $cached;
            } else {
                $to_fetch[$alias] = [
                    'endpoint'  => $ep,
                    'cache_key' => $cache_key,
                    'url'       => self::BASE_URL . $ep . '?' . http_build_query($base_params)
                ];
            }
        }

        // If all were cached, return immediately (0.00s!)
        if (empty($to_fetch)) {
            return $results;
        }

        // 2. Execute uncached requests in safe chunks with auto-retry
        if (function_exists('curl_multi_init')) {
            $chunks = array_chunk($to_fetch, $chunk_size, true);

            foreach ($chunks as $chunk) {
                $batch_results = self::execute_curl_multi_batch($chunk, $ttl);
                foreach ($batch_results['success'] as $alias => $data) {
                    $results[$alias] = $data;
                }

                // If any endpoint in this chunk failed, trigger the Auto-Retry Failsafe
                if (!empty($batch_results['failed'])) {
                    $retry_attempts = 2;
                    $retry_queue = $batch_results['failed'];

                    for ($attempt = 1; $attempt <= $retry_attempts && !empty($retry_queue); $attempt++) {
                        // Exponential backoff: 300ms, then 600ms
                        usleep(300000 * $attempt);
                        
                        $retry_results = self::execute_curl_multi_batch($retry_queue, $ttl);
                        foreach ($retry_results['success'] as $alias => $data) {
                            $results[$alias] = $data;
                            unset($retry_queue[$alias]);
                        }
                    }

                    // For any endpoints still failing after all retries, record the failure
                    foreach ($retry_queue as $alias => $fail_info) {
                        $results[$alias] = [
                            'status' => 500,
                            'error'  => 'API endpoint unreachable after ' . ($retry_attempts + 1) . ' attempts.'
                        ];
                        if (function_exists('error_log')) {
                            error_log("[MeraRashifal VedicAPI] Endpoint '{$alias}' failed after retries: " . ($fail_info['endpoint'] ?? ''));
                        }
                    }
                }
            }
        } else {
            // Fallback to sequential wp_remote_get if curl_multi is not available
            foreach ($to_fetch as $alias => $info) {
                $results[$alias] = self::cached_request($info['endpoint'], $base_params);
            }
        }

        return $results;
    }

    /**
     * Helper to execute a single curl_multi batch and separate successes from failures.
     */
    private static function execute_curl_multi_batch($batch_items, $ttl) {
        $mh = curl_multi_init();
        $handles = [];

        foreach ($batch_items as $alias => $info) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $info['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_multi_add_handle($mh, $ch);
            $handles[$alias] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        $success = [];
        $failed = [];

        foreach ($handles as $alias => $ch) {
            $body = curl_multi_getcontent($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $json = json_decode($body, true);

            if ($code === 200 && $json && (isset($json['response']) || (isset($json['status']) && $json['status'] == 200))) {
                if (function_exists('set_transient')) {
                    set_transient($batch_items[$alias]['cache_key'], $json, $ttl);
                }
                $success[$alias] = $json;
            } else {
                $failed[$alias] = $batch_items[$alias];
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * Centralized cached GET request to VedicAstroAPI
     */
    private static function cached_request($endpoint, $params) {
        $api_key = self::get_api_key();
        if (empty($api_key)) {
            return null;
        }

        $params['api_key'] = $api_key;
        $cache_key = 'mr_vedic_' . md5($endpoint . '_' . wp_json_encode($params));

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $url = add_query_arg($params, self::BASE_URL . $endpoint);
        $response = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => ['Accept' => 'application/json']
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        if (stripos($body, '<svg') !== false) {
            $ttl = defined('MONTH_IN_SECONDS') ? MONTH_IN_SECONDS : 86400 * 30;
            $res = ['status' => 200, 'response' => $body, 'type' => 'svg'];
            set_transient($cache_key, $res, $ttl);
            return $res;
        }

        $json = json_decode($body, true);

        if ($json && (isset($json['response']) || isset($json['status']) && $json['status'] == 200)) {
            // Cache for 30 days
            $ttl = defined('MONTH_IN_SECONDS') ? MONTH_IN_SECONDS : 86400 * 30;
            set_transient($cache_key, $json, $ttl);
            return $json;
        }

        return $json;
    }

    /**
     * Normalize DOB format to DD/MM/YYYY required by VedicAstroAPI
     */
    public static function format_dob($dob_input) {
        $dob_input = trim($dob_input ?? '');
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dob_input, $m)) {
            return sprintf('%02d/%02d/%04d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dob_input, $m)) {
            return sprintf('%02d/%02d/%04d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dob_input, $m)) {
            return sprintf('%02d/%02d/%04d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }
        $ts = strtotime(str_replace('/', '-', $dob_input));
        if ($ts) {
            return date('d/m/Y', $ts);
        }
        return $dob_input;
    }

    /**
     * Normalize TOB format to HH:MM (24-hour) required by VedicAstroAPI
     */
    public static function format_tob($tob_input) {
        $trimmed = trim($tob_input ?? '');
        if (empty($trimmed)) {
            return '12:00';
        }
        // Check for 12-hour AM/PM representation (e.g., '8:30PM', '08:30 PM', '8:30am')
        if (preg_match('/(am|pm)/i', $trimmed)) {
            $ts = strtotime($trimmed);
            if ($ts !== false) {
                return date('H:i', $ts);
            }
        }
        // 24-hour format (e.g., '20:30', '8:30', '20:30:00')
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $trimmed, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }
        $ts = strtotime($trimmed);
        if ($ts !== false) {
            return date('H:i', $ts);
        }
        return '12:00';
    }
}
