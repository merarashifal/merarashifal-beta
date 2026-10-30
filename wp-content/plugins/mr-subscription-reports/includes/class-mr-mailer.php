<?php
/**
 * MeraRashifal Outbound Email & Authenticated SMTP Dispatcher
 *
 * Handles SMTP delivery for @merarashifal.com (Serverbyt hosting)
 * Dispatches branded celestial HTML notifications only when reports are compiled and ready.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Mailer {

    const OPTION_KEY = 'mr_smtp_settings';

    public static function init() {
        // Hook PHPMailer to route emails through authenticated SMTP
        add_action('phpmailer_init', [__CLASS__, 'configure_phpmailer']);
        add_filter('wp_mail_from', [__CLASS__, 'get_from_email']);
        add_filter('wp_mail_from_name', [__CLASS__, 'get_from_name']);

        // Admin AJAX to send a live test email
        add_action('wp_ajax_mr_send_test_email', [__CLASS__, 'ajax_send_test_email']);
    }

    /**
     * Get SMTP & Sender Settings with Serverbyt Hosting Defaults
     */
    public static function get_settings() {
        $defaults = [
            'enabled'    => 1,
            'host'       => 'mail.merarashifal.com',
            'port'       => 465,
            'encryption' => 'ssl', // 'ssl', 'tls', or 'none'
            'username'   => 'no-reply@merarashifal.com',
            'password'   => '',
            'from_email' => 'no-reply@merarashifal.com',
            'from_name'  => 'MeraRashifal',
        ];

        $saved = get_option(self::OPTION_KEY, []);
        if (!is_array($saved)) {
            $saved = [];
        }

        return wp_parse_args($saved, $defaults);
    }

    /**
     * Save SMTP Settings
     */
    public static function save_settings($data) {
        $clean = [
            'enabled'    => !empty($data['enabled']) ? 1 : 0,
            'host'       => sanitize_text_field($data['host'] ?? 'mail.merarashifal.com'),
            'port'       => intval($data['port'] ?? 465),
            'encryption' => sanitize_text_field($data['encryption'] ?? 'ssl'),
            'username'   => sanitize_text_field($data['username'] ?? 'no-reply@merarashifal.com'),
            'password'   => sanitize_text_field($data['password'] ?? ''),
            'from_email' => sanitize_email($data['from_email'] ?? 'no-reply@merarashifal.com'),
            'from_name'  => sanitize_text_field($data['from_name'] ?? 'MeraRashifal'),
        ];

        update_option(self::OPTION_KEY, $clean);
        return $clean;
    }

    /**
     * Filter From Email
     */
    public static function get_from_email($default = '') {
        $s = self::get_settings();
        return !empty($s['from_email']) ? $s['from_email'] : 'no-reply@merarashifal.com';
    }

    /**
     * Filter From Name
     */
    public static function get_from_name($default = '') {
        $s = self::get_settings();
        return !empty($s['from_name']) ? $s['from_name'] : 'MeraRashifal';
    }

    /**
     * Configure PHPMailer to use authenticated SMTP
     */
    public static function configure_phpmailer($phpmailer) {
        $s = self::get_settings();

        if (empty($s['enabled'])) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host     = $s['host'];
        $phpmailer->SMTPAuth = !empty($s['username']);
        $phpmailer->Port     = (int) $s['port'];
        $phpmailer->Username = $s['username'];
        $phpmailer->Password = $s['password'];

        if ($s['encryption'] === 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ($s['encryption'] === 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        $phpmailer->From     = $s['from_email'];
        $phpmailer->FromName = $s['from_name'];
        $phpmailer->Timeout  = 12;
    }

    /**
     * Send Branded HTML Report Ready Email to Client
     * Triggered strictly after PDF generation has succeeded and file exists.
     *
     * @param string $recipient_email
     * @param array  $report_data
     * @param string $download_url White-labeled clean URL
     * @return bool
     */
    public static function send_report_ready_email($recipient_email, $report_data, $download_url) {
        if (!is_email($recipient_email)) {
            return false;
        }

        $person_name  = !empty($report_data['person_name']) ? $report_data['person_name'] : 'Valued Client';
        $report_title = !empty($report_data['report_title']) ? $report_data['report_title'] : 'Astrological Report';
        $gender       = !empty($report_data['gender']) ? $report_data['gender'] : 'Male';
        $dob          = !empty($report_data['dob']) ? $report_data['dob'] : '';
        $tob          = !empty($report_data['tob']) ? $report_data['tob'] : '';
        $pob          = !empty($report_data['pob']) ? $report_data['pob'] : '';

        $subject = '✨ Your ' . $report_title . ' is Ready - MeraRashifal';
        $html_body = self::render_html_template([
            'person_name'  => $person_name,
            'report_title' => $report_title,
            'gender'       => $gender,
            'dob'          => $dob,
            'tob'          => $tob,
            'pob'          => $pob,
            'download_url' => $download_url,
        ]);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: MeraRashifal Support <support@merarashifal.com>',
        ];

        return wp_mail($recipient_email, $subject, $html_body, $headers);
    }

    /**
     * Render Celestial Dark & Gold Branded Email Template
     */
    public static function render_html_template($data) {
        $person_name  = esc_html($data['person_name']);
        $report_title = esc_html($data['report_title']);
        $gender       = esc_html($data['gender']);
        $dob          = esc_html($data['dob']);
        $tob          = esc_html($data['tob']);
        $pob          = esc_html($data['pob']);
        $download_url = esc_url($data['download_url']);
        $year         = date('Y');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $report_title; ?> - Ready for Download</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #e2e8f0;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0b0f19; padding: 30px 15px;">
                <tr>
                    <td align="center">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background: #1e293b; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);">
                            
                            <!-- Gold Gradient Accent Bar -->
                            <tr>
                                <td height="6" style="background: linear-gradient(90deg, #f59e0b, #d97706, #10b981);"></td>
                            </tr>

                            <!-- Header -->
                            <tr>
                                <td style="padding: 32px 30px 24px 30px; text-align: center; background: radial-gradient(circle at top, rgba(245, 158, 11, 0.15) 0%, rgba(30, 41, 59, 0) 70%);">
                                    <div style="font-size: 24px; font-weight: 800; letter-spacing: 1.5px; color: #ffffff; text-transform: uppercase;">
                                        ✨ MERARASHIFAL
                                    </div>
                                    <div style="font-size: 13px; color: #94a3b8; margin-top: 4px; letter-spacing: 0.5px;">
                                        Vedic Astrological &amp; Predictive Insights
                                    </div>
                                </td>
                            </tr>

                            <!-- Main Content Area -->
                            <tr>
                                <td style="padding: 0 32px 30px 32px;">
                                    <h2 style="margin: 0 0 16px 0; color: #ffffff; font-size: 21px; font-weight: 700;">
                                        Namaste <?php echo $person_name; ?>,
                                    </h2>
                                    <p style="margin: 0 0 22px 0; color: #cbd5e1; font-size: 15px; line-height: 1.6;">
                                        Your personalized <strong><?php echo $report_title; ?></strong> has been successfully compiled and verified by our cosmic calculation engine. All your predictive charts, dasha life-timings, planetary strengths, and Vedic remedial guidance are now ready for you.
                                    </p>

                                    <!-- Report Summary Card -->
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0f172a; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; margin-bottom: 28px; padding: 18px 20px;">
                                        <tr>
                                            <td style="padding-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Report Title</span><br>
                                                <strong style="font-size: 16px; color: #f59e0b;"><?php echo $report_title; ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0 6px 0;">
                                                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td width="50%" style="font-size: 13px; color: #94a3b8; padding: 4px 0;">
                                                            Native: <strong style="color: #ffffff;"><?php echo $person_name; ?> (<?php echo $gender; ?>)</strong>
                                                        </td>
                                                        <td width="50%" style="font-size: 13px; color: #94a3b8; padding: 4px 0;">
                                                            Status: <strong style="color: #10b981;">● Ready to View</strong>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="50%" style="font-size: 13px; color: #94a3b8; padding: 4px 0;">
                                                            Birth Details: <span style="color: #ffffff;"><?php echo $dob; ?> &bull; <?php echo $tob; ?></span>
                                                        </td>
                                                        <td width="50%" style="font-size: 13px; color: #94a3b8; padding: 4px 0;">
                                                            Place: <span style="color: #ffffff;"><?php echo $pob; ?></span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Primary CTA Download Button -->
                                    <div style="text-align: center; margin: 30px 0;">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo $download_url; ?>" style="height:50px;v-text-anchor:middle;width:340px;" arcsize="16%" stroke="f" fillcolor="#d97706">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">Download Your Astrological Report (PDF)</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <a href="<?php echo $download_url; ?>" target="_blank" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; display: inline-block; font-size: 16px; font-weight: 700; text-decoration: none; padding: 15px 34px; border-radius: 10px; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.4); text-align: center; mso-hide: all;">
                                            <span>Download Your Astrological Report (PDF) &rarr;</span>
                                        </a>
                                    </div>

                                    <!-- Security & Direct Access Info -->
                                    <div style="background-color: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; padding: 14px 16px; border-radius: 0 8px 8px 0; margin-bottom: 24px;">
                                        <p style="margin: 0; font-size: 13px; color: #cbd5e1; line-height: 1.5;">
                                            🔒 <strong>Private &amp; Secure:</strong> This download link has been generated specifically for your report. You can open and view it in your browser or save the PDF file to your computer or phone anytime.
                                        </p>
                                    </div>

                                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #94a3b8;">
                                        If the button above does not open directly, copy and paste this URL into your browser:
                                    </p>
                                    <p style="margin: 0 0 20px 0; font-size: 12px; word-break: break-all; color: #f59e0b;">
                                        <a href="<?php echo $download_url; ?>" style="color: #f59e0b; text-decoration: underline;"><?php echo $download_url; ?></a>
                                    </p>

                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="padding: 24px 30px; background-color: #111827; border-top: 1px solid rgba(255, 255, 255, 0.06); text-align: center;">
                                    <p style="margin: 0 0 8px 0; font-size: 13px; color: #94a3b8;">
                                        Warm regards,<br>
                                        <strong style="color: #ffffff;">MeraRashifal Astrological Team</strong>
                                    </p>
                                    <p style="margin: 0 0 12px 0; font-size: 12px; color: #64748b;">
                                        Have questions about your report or reading? Contact our support team at <a href="mailto:support@merarashifal.com" style="color: #f59e0b; text-decoration: none;">support@merarashifal.com</a>
                                    </p>
                                    <p style="margin: 0; font-size: 11px; color: #475569;">
                                        &copy; <?php echo $year; ?> <a href="https://merarashifal.com" style="color: #64748b; text-decoration: none;">MeraRashifal.com</a>. All rights reserved.<br>
                                        Please do not reply directly to this automated email (no-reply@merarashifal.com).
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Admin AJAX Live Test Email Sender
     */
    public static function ajax_send_test_email() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('mr_test_email_nonce', 'nonce');

        $to = sanitize_email($_POST['test_email'] ?? '');
        if (!is_email($to)) {
            wp_send_json_error(['message' => 'Please provide a valid recipient email address for testing.']);
        }

        // Test sending with detailed error catching
        add_action('wp_mail_failed', function($wp_error) {
            global $mr_last_mail_error;
            $mr_last_mail_error = $wp_error->get_error_message();
        });

        global $mr_last_mail_error;
        $mr_last_mail_error = null;

        $subject = '✨ MeraRashifal Serverbyt SMTP Test Successful';
        $test_data = [
            'person_name'  => 'MeraRashifal Administrator',
            'report_title' => 'Yearly Horoscope Detailed Report (Sample Test)',
            'gender'       => 'Male',
            'dob'          => date('Y-m-d'),
            'tob'          => date('H:i'),
            'pob'          => 'New Delhi, India',
            'download_url' => home_url('/download-report/test-sample-token/MeraRashifal_Sample_Report.pdf'),
        ];
        $html_body = self::render_html_template($test_data);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: MeraRashifal Support <support@merarashifal.com>',
        ];

        $sent = wp_mail($to, $subject, $html_body, $headers);

        if ($sent) {
            $s = self::get_settings();
            wp_send_json_success([
                'message' => 'Test email successfully dispatched to ' . esc_html($to) . ' via ' . esc_html($s['host']) . ':' . esc_html($s['port']) . ' (From: ' . esc_html($s['from_email']) . ')! Please check your inbox or spam folder.'
            ]);
        } else {
            $err = !empty($mr_last_mail_error) ? $mr_last_mail_error : 'Unknown SMTP dispatch failure. Please check your Serverbyt host, port, username, and password.';
            wp_send_json_error(['message' => 'Failed to send test email: ' . $err]);
        }
    }
}
