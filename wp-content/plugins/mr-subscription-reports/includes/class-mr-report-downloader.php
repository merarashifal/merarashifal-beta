<?php
/**
 * White-Labeled Report Downloader & Secure File Streamer
 * 
 * Provides clean, branded download URLs for astrology reports:
 * https://merarashifal.com/download-report/{token}/{filename}.pdf
 * Completely conceals WordPress, wp-content, uploads directories, and server structure.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Report_Downloader {

    public static function init() {
        // Intercept download requests early on WordPress init
        add_action('init', [__CLASS__, 'handle_download_request'], 1);
    }

    /**
     * Generate an unguessable 32-character hexadecimal download token
     */
    public static function generate_token() {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            return md5(uniqid(wp_generate_password(16, true, true), true));
        }
    }

    /**
     * Build the public-facing, white-labeled clean download URL
     */
    public static function get_download_url($token, $filename = '') {
        $clean_token = sanitize_text_field($token);
        $url = home_url('/download-report/' . $clean_token);
        
        if (!empty($filename)) {
            $url .= '/' . rawurlencode(basename($filename));
        }
        
        return $url;
    }

    /**
     * Ensure a report row has a clean URL, auto-generating token if missing
     */
    public static function ensure_clean_url($report) {
        if (is_array($report)) {
            $report = (object) $report;
        }

        if (empty($report->download_token)) {
            global $wpdb;
            $token = self::generate_token();
            $filename = !empty($report->pdf_filename) ? $report->pdf_filename : 'MeraRashifal_Report.pdf';
            $clean_url = self::get_download_url($token, $filename);

            $wpdb->update(
                $wpdb->prefix . 'mr_generated_reports',
                [
                    'download_token' => $token,
                    'pdf_url'        => $clean_url,
                ],
                ['id' => $report->id]
            );

            return $clean_url;
        }

        return self::get_download_url($report->download_token, $report->pdf_filename);
    }

    /**
     * Intercept and stream PDF binary safely without exposing WordPress
     */
    public static function handle_download_request() {
        if (empty($_SERVER['REQUEST_URI'])) {
            return;
        }

        $home_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        $req_path  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');

        // Strip WP subdirectory prefix if in local/nested environment (e.g. /merarashifal/)
        if ($home_path !== '' && stripos($req_path, $home_path) === 0) {
            $relative = trim(substr($req_path, strlen($home_path)), '/');
        } else {
            $relative = $req_path;
        }

        // Match /download-report/{token} or /download-report/{token}/{filename}
        // Also support /reports/download/{token}
        if (!preg_match('#^(?:download-report|reports/download)/([a-fA-F0-9]{16,64})(?:/([^/]+))?$#i', $relative, $matches)) {
            return;
        }

        $token = sanitize_text_field($matches[1]);
        if (empty($token)) {
            self::render_error_page('Invalid Request', 'No valid download token was provided.');
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mr_generated_reports';
        $report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE download_token = %s LIMIT 1", $token));

        if (!$report) {
            self::render_error_page('Report Not Found', 'The requested report link is invalid or has expired. Please verify your account history.');
            exit;
        }

        // Determine physical file on disk
        $upload_dir = wp_upload_dir();
        $target_dir = $upload_dir['basedir'] . '/mr-reports/' . $report->user_id;
        $file_path  = $target_dir . '/' . $report->pdf_filename;

        // Fallbacks for older files or missing extensions
        if (!file_exists($file_path)) {
            if (file_exists($report->pdf_filename)) {
                $file_path = $report->pdf_filename;
            } elseif (file_exists($target_dir . '/' . pathinfo($report->pdf_filename, PATHINFO_FILENAME) . '.pdf')) {
                $file_path = $target_dir . '/' . pathinfo($report->pdf_filename, PATHINFO_FILENAME) . '.pdf';
            } elseif (file_exists($target_dir . '/' . pathinfo($report->pdf_filename, PATHINFO_FILENAME) . '.html')) {
                $file_path = $target_dir . '/' . pathinfo($report->pdf_filename, PATHINFO_FILENAME) . '.html';
            }
        }

        if (!file_exists($file_path)) {
            self::render_error_page('Report Finalizing', 'Your astrological report is being compiled by our cosmic calculation engine. Please refresh in a moment.');
            exit;
        }

        // Clean any active output buffers to avoid corrupting the binary PDF stream
        while (ob_get_level()) {
            ob_end_clean();
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $clean_download_name = !empty($report->pdf_filename) ? basename($report->pdf_filename) : 'MeraRashifal_Report.pdf';

        if ($extension === 'html' || $extension === 'htm') {
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Disposition: inline; filename="' . esc_attr($clean_download_name) . '"');
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . esc_attr($clean_download_name) . '"');
            header('Content-Transfer-Encoding: binary');
        }

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0, private');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        header('Accept-Ranges: bytes');
        header('X-Robots-Tag: noindex, nofollow', true);

        // Strip WordPress signature headers
        header_remove('X-Powered-By');
        header_remove('Link');
        header_remove('X-Pingback');

        readfile($file_path);
        exit;
    }

    /**
     * Render a branded, standalone error page with zero WordPress footprint
     */
    public static function render_error_page($title, $message) {
        status_header(404);
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: text/html; charset=UTF-8');
        header_remove('X-Powered-By');
        header_remove('Link');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($title); ?> - MeraRashifal</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    background: linear-gradient(135deg, #0b0f19 0%, #1e1e38 100%);
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #e2e8f0;
                    padding: 20px;
                }
                .mr-err-card {
                    background: rgba(30, 41, 59, 0.85);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(12px);
                    border-radius: 20px;
                    max-width: 500px;
                    width: 100%;
                    padding: 40px 32px;
                    text-align: center;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                }
                .mr-err-icon {
                    width: 72px;
                    height: 72px;
                    background: linear-gradient(135deg, #f59e0b, #d97706);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 24px;
                    font-size: 32px;
                    color: #fff;
                    box-shadow: 0 10px 20px rgba(217, 119, 6, 0.3);
                }
                .mr-err-title {
                    font-size: 24px;
                    font-weight: 700;
                    color: #ffffff;
                    margin-bottom: 12px;
                    letter-spacing: -0.5px;
                }
                .mr-err-msg {
                    font-size: 15px;
                    color: #94a3b8;
                    line-height: 1.6;
                    margin-bottom: 28px;
                }
                .mr-err-btn {
                    display: inline-block;
                    background: linear-gradient(135deg, #d97706, #b45309);
                    color: #ffffff;
                    text-decoration: none;
                    font-weight: 600;
                    font-size: 14px;
                    padding: 12px 28px;
                    border-radius: 10px;
                    transition: all 0.2s ease;
                    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
                }
                .mr-err-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 18px rgba(217, 119, 6, 0.45);
                }
                .mr-err-brand {
                    margin-top: 32px;
                    font-size: 12px;
                    color: #64748b;
                    letter-spacing: 1px;
                    text-transform: uppercase;
                }
            </style>
        </head>
        <body>
            <div class="mr-err-card">
                <div class="mr-err-icon">✨</div>
                <h1 class="mr-err-title"><?php echo esc_html($title); ?></h1>
                <p class="mr-err-msg"><?php echo esc_html($message); ?></p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="mr-err-btn">Return to MeraRashifal</a>
                <div class="mr-err-brand">&copy; MeraRashifal Vedic Astrological Services</div>
            </div>
        </body>
        </html>
        <?php
    }
}
