<?php
/**
 * Unified PDF Engine for MeraRashifal Reports
 * Handles Local Headless Chrome / Edge execution and Cloud Browserless.io API.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_PDF_Engine {

    const BROWSERLESS_URL = 'https://production-sfo.browserless.io/pdf';

    /**
     * Get configured PDF engine choice: 'auto', 'browserless', 'local'
     */
    public static function get_engine_choice() {
        return function_exists('get_option') ? get_option('mr_pdf_engine', 'auto') : 'auto';
    }

    /**
     * Get configured Browserless.io API Token
     */
    public static function get_browserless_token() {
        if (function_exists('get_option')) {
            $t = get_option('mr_browserless_token', '');
            if (!empty($t)) return $t;
        }
        return '2VSwOG5b8ldOoue0281fab0ae0278fbea9460509561a3ec84';
    }

    /**
     * Detect local Chrome or Edge path on current OS
     */
    public static function get_local_chrome_path() {
        if (PHP_OS_FAMILY === 'Windows') {
            $chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
            if (file_exists($chrome)) return $chrome;
            $edge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
            if (file_exists($edge)) return $edge;
        } else {
            $linux_paths = [
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/usr/bin/google-chrome',
                '/usr/bin/google-chrome-stable',
            ];
            foreach ($linux_paths as $p) {
                if (file_exists($p)) return $p;
            }
        }
        return false;
    }

    /**
     * Check if local Chrome is available
     */
    public static function is_local_available() {
        return self::get_local_chrome_path() !== false;
    }

    /**
     * Render HTML file to PDF
     * 
     * @param string $html_path Absolute path to HTML file
     * @param string $pdf_path Absolute path to target PDF file
     * @return array ['success' => bool, 'engine' => string, 'error' => string|null]
     */
    public static function render_pdf($html_path, $pdf_path) {
        $choice = self::get_engine_choice();
        $token  = self::get_browserless_token();
        $local  = self::get_local_chrome_path();

        // 1. Force Browserless or Auto-mode on server where local Chrome does not exist
        if ($choice === 'browserless' || ($choice === 'auto' && !$local)) {
            if (empty($token)) {
                return [
                    'success' => false,
                    'engine'  => 'browserless',
                    'error'   => 'Browserless.io API token is not configured in WordPress settings.'
                ];
            }
            return self::render_via_browserless($html_path, $pdf_path, $token);
        }

        // 2. Try Local Chrome first
        if ($local) {
            $res = self::render_via_local_chrome($html_path, $pdf_path, $local);
            if ($res['success']) {
                return $res;
            }
            // Fallback to Browserless if local Chrome failed and token is available
            if (!empty($token)) {
                return self::render_via_browserless($html_path, $pdf_path, $token);
            }
            return $res;
        }

        // 3. Neither engine available
        return [
            'success' => false,
            'engine'  => 'none',
            'error'   => 'No valid PDF engine available (Local Chrome not found and Browserless token missing).'
        ];
    }

    /**
     * Render via local Chrome / Chromium CLI
     */
    public static function render_via_local_chrome($html_path, $pdf_path, $chrome_path) {
        $cmd = '"' . $chrome_path . '" --headless --disable-gpu --no-margins --no-pdf-header-footer --print-to-pdf="' . $pdf_path . '" "' . $html_path . '"';
        @exec($cmd);

        if (file_exists($pdf_path) && filesize($pdf_path) > 1000) {
            return [
                'success' => true,
                'engine'  => 'local_chrome'
            ];
        }
        return [
            'success' => false,
            'engine'  => 'local_chrome',
            'error'   => 'Local Chrome failed to compile PDF file.'
        ];
    }

    /**
     * Render via Browserless.io Cloud API
     */
    public static function render_via_browserless($html_path, $pdf_path, $token) {
        if (!file_exists($html_path)) {
            return [
                'success' => false,
                'engine'  => 'browserless',
                'error'   => 'HTML source file does not exist on disk.'
            ];
        }

        $html_content = file_get_contents($html_path);
        $url = self::BROWSERLESS_URL . '?token=' . urlencode(trim($token));

        $payload = [
            'html' => $html_content,
            'options' => [
                'format'            => 'A4',
                'printBackground'   => true,
                'preferCSSPageSize' => true,
                'margin' => [
                    'top'    => '0px',
                    'right'  => '0px',
                    'bottom' => '0px',
                    'left'   => '0px',
                ],
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 120, // Generous timeout for large 20-30 page reports
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'engine'  => 'browserless',
                'error'   => $response->get_error_message()
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code === 200 && !empty($body) && strlen($body) > 1000) {
            file_put_contents($pdf_path, $body);
            return [
                'success' => true,
                'engine'  => 'browserless'
            ];
        }

        $err_json = json_decode($body, true);
        $err_msg = $err_json['message'] ?? ('Browserless HTTP Error ' . $code . ': ' . substr($body, 0, 200));
        return [
            'success' => false,
            'engine'  => 'browserless',
            'error'   => $err_msg
        ];
    }

    /**
     * Test Browserless.io connection with a minimal test PDF
     */
    public static function test_browserless_connection($token = null) {
        $token = $token ?: self::get_browserless_token();
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Browserless.io API token is empty.'
            ];
        }

        $url = self::BROWSERLESS_URL . '?token=' . urlencode(trim($token));
        $payload = [
            'html' => '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:30px;"><h2>MeraRashifal Browserless.io Connection Test</h2><p>PDF rendering verified successfully.</p></body></html>',
            'options' => [
                'format'          => 'A4',
                'printBackground' => true,
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code === 200 && strlen($body) > 500) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Browserless.io! Test PDF rendered perfectly.'
            ];
        }

        $json = json_decode($body, true);
        $err = $json['message'] ?? ('HTTP ' . $code . ': ' . substr($body, 0, 150));
        return [
            'success' => false,
            'message' => $err
        ];
    }
}
