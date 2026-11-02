<?php
/**
 * MeraRashifal Universal Invoicing Engine
 * Generates sequential non-GST retail invoices (MT/26-27/11/00001),
 * renders branded HTML/PDF invoices, dispatches email with snapshot & PDF attachment,
 * and provides customer downloads in My Account passbook.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Invoice {

    const COMPANY_NAME    = 'Merarashifal Technologies';
    const COMPANY_ADDRESS = 'Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, U.P., India, 201014';
    const COMPANY_EMAIL   = 'support@merarashifal.com';
    const TAX_STATUS      = 'Non-GST Registered Business (0% Tax / Nil)';
    const SEQ_OPTION_KEY  = 'mr_next_invoice_seq';

    public static function init() {
        // Ensure database table has invoice_number column
        add_action('admin_init', [__CLASS__, 'ensure_columns']);
        add_action('init', [__CLASS__, 'ensure_columns']);

        // Public/Protected Invoice View/Download endpoint
        add_action('template_redirect', [__CLASS__, 'handle_invoice_request']);
        add_action('wp_ajax_mr_download_invoice', [__CLASS__, 'ajax_download_invoice']);
        add_action('wp_ajax_nopriv_mr_download_invoice', [__CLASS__, 'ajax_download_invoice']);
    }

    /**
     * Ensure invoice_number column exists in transactions table
     */
    public static function ensure_columns() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        if (!$table_exists) return;

        $cols = $wpdb->get_col("DESC $table", 0);
        if (!empty($cols) && !in_array('invoice_number', $cols, true)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN invoice_number varchar(64) NOT NULL DEFAULT '' AFTER internal_txn_id, ADD KEY invoice_number (invoice_number)");
        }
    }

    /**
     * Generate the next sequential invoice number
     * Format: MT/{FY}/{MM}/{SEQUENCE}
     * - Timezone: Asia/Kolkata (IST)
     * - FY: changes on April 1 (e.g. 26-27 -> 27-28 on 1 Apr 2027)
     * - MM: 2 digits (e.g. 11 for Nov)
     * - Sequence: 5 digits zero-padded (e.g. 00001, never resets across months/years)
     *
     * @return string
     */
    public static function generate_next_invoice_number() {
        $dt = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $year         = (int)$dt->format('Y');
        $month        = (int)$dt->format('n');
        $month_padded = $dt->format('m');

        // Indian Financial Year: April (month 4) to March (month 3)
        if ($month >= 4) {
            $fy_start = substr((string)$year, -2);
            $fy_end   = substr((string)($year + 1), -2);
        } else {
            $fy_start = substr((string)($year - 1), -2);
            $fy_end   = substr((string)$year, -2);
        }
        $fy_str = $fy_start . '-' . $fy_end;

        // Atomic sequence increment
        $seq = (int)get_option(self::SEQ_OPTION_KEY, 1);
        if ($seq <= 0) {
            $seq = 1;
        }
        update_option(self::SEQ_OPTION_KEY, $seq + 1);

        $seq_padded = sprintf('%05d', $seq);

        return "MT/{$fy_str}/{$month_padded}/{$seq_padded}";
    }

    /**
     * Get or create invoice number for a transaction
     */
    public static function assign_invoice_to_transaction($txn_id_or_internal_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d OR internal_txn_id = %s OR gateway_order_id = %s LIMIT 1",
            is_numeric($txn_id_or_internal_id) ? intval($txn_id_or_internal_id) : 0,
            $txn_id_or_internal_id,
            $txn_id_or_internal_id
        ), ARRAY_A);

        if (!$txn) {
            return false;
        }

        // Return existing invoice number if already assigned
        if (!empty($txn['invoice_number'])) {
            return $txn['invoice_number'];
        }

        // Generate new invoice number
        $inv_num = self::generate_next_invoice_number();

        $wpdb->update(
            $table,
            ['invoice_number' => $inv_num],
            ['id' => $txn['id']]
        );

        return $inv_num;
    }

    /**
     * Render the complete HTML invoice template
     *
     * @param array $txn Transaction row
     * @param bool $is_email Whether rendering for email snapshot (inlines tables)
     * @return string HTML
     */
    public static function render_invoice_html($txn, $is_email = false) {
        $meta = !empty($txn['metadata']) ? (is_array($txn['metadata']) ? $txn['metadata'] : json_decode($txn['metadata'], true)) : [];
        if (!is_array($meta)) $meta = [];

        $invoice_num    = !empty($txn['invoice_number']) ? $txn['invoice_number'] : ('MT/26-27/' . date('m') . '/00001');
        $internal_id    = $txn['internal_txn_id'] ?? 'MR_ORD_' . strtoupper(bin2hex(random_bytes(4)));
        $rzp_payment_id = !empty($txn['gateway_payment_id']) ? $txn['gateway_payment_id'] : 'pay_' . substr(md5($internal_id), 0, 14);
        $rzp_order_id   = !empty($txn['gateway_order_id']) ? $txn['gateway_order_id'] : 'order_' . substr(md5($internal_id), 0, 14);

        // Date & Time in IST
        $created_raw = $txn['created_at'] ?? 'now';
        $dt = new DateTime($created_raw, new DateTimeZone('Asia/Kolkata'));
        $date_formatted = $dt->format('d M Y, h:i A') . ' IST';

        // Customer & Billing Info
        $customer_name  = !empty($txn['customer_name']) ? $txn['customer_name'] : ($meta['billing_name'] ?? 'Valued Customer');
        $customer_email = !empty($txn['customer_email']) ? $txn['customer_email'] : ($meta['billing_email'] ?? '');
        $customer_phone = !empty($txn['customer_phone']) ? $txn['customer_phone'] : ($meta['billing_phone'] ?? '');

        $billing_country = !empty($meta['billing_country']) ? $meta['billing_country'] : 'India';
        $billing_state   = !empty($meta['billing_state']) ? $meta['billing_state'] : ($meta['state'] ?? 'Uttar Pradesh');
        $billing_city    = !empty($meta['billing_city']) ? $meta['billing_city'] : '';
        $billing_address = !empty($meta['billing_address']) ? $meta['billing_address'] : ($meta['billing_address_1'] ?? '');
        $billing_pincode = !empty($meta['billing_pincode']) ? $meta['billing_pincode'] : ($meta['billing_postcode'] ?? '');
        $is_international = (!empty($meta['is_international']) || ($billing_country !== 'India' && $billing_country !== 'IN'));

        // Items and amounts
        $item_name   = !empty($txn['item_name']) ? $txn['item_name'] : 'Astrological Report Package';
        $bundle_tier = $meta['bundle_tier'] ?? 'single';
        $reports     = !empty($meta['bundle_reports']) ? (array)$meta['bundle_reports'] : [];
        $chat_credits= intval($meta['chat_credits'] ?? 0);

        $gross_amount = floatval($txn['gross_amount'] ?? $txn['net_amount']);
        $net_amount   = floatval($txn['net_amount'] ?? 299);
        $discount     = ($gross_amount > $net_amount) ? ($gross_amount - $net_amount) : 0;
        $currency     = $txn['currency'] ?? 'INR';
        $currency_sym = ($currency === 'INR') ? '₹' : '$';

        $logo_url = MR_PLUGIN_URL . 'assets/images/logo.png';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Invoice <?php echo esc_html($invoice_num); ?> - <?php echo esc_html(self::COMPANY_NAME); ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #f8fafc;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    color: #1e293b;
                    line-height: 1.5;
                }
                .mr-inv-wrapper {
                    max-width: 820px;
                    margin: 30px auto;
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                    padding: 40px;
                    box-sizing: border-box;
                }
                .mr-inv-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    border-bottom: 2px solid #7A1F3D;
                    padding-bottom: 24px;
                    margin-bottom: 24px;
                    gap: 20px;
                }
                .mr-inv-brand h1 {
                    margin: 8px 0 4px 0;
                    font-size: 22px;
                    font-weight: 800;
                    color: #7A1F3D;
                }
                .mr-inv-brand p {
                    margin: 0;
                    font-size: 12px;
                    color: #64748b;
                    line-height: 1.5;
                    max-width: 380px;
                }
                .mr-inv-badge-wrap {
                    text-align: right;
                }
                .mr-inv-badge {
                    background: #FFF3E1;
                    color: #7A1F3D;
                    border: 1.5px solid #D9A441;
                    font-size: 12px;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    padding: 4px 14px;
                    border-radius: 9999px;
                    display: inline-block;
                }
                .mr-inv-num {
                    font-size: 20px;
                    font-weight: 800;
                    color: #1e293b;
                    margin: 8px 0 2px 0;
                    font-family: monospace;
                }
                .mr-inv-date {
                    font-size: 12px;
                    color: #64748b;
                }
                .mr-inv-meta-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 24px;
                    background: #FFFDF9;
                    border: 1px solid #f1e6d4;
                    border-radius: 10px;
                    padding: 18px 22px;
                    margin-bottom: 28px;
                }
                .mr-inv-meta-box h4 {
                    margin: 0 0 6px 0;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #b45309;
                    font-weight: 800;
                }
                .mr-inv-meta-box p {
                    margin: 0;
                    font-size: 13px;
                    line-height: 1.5;
                    color: #334155;
                }
                .mr-inv-id-tag {
                    font-family: monospace;
                    font-weight: 700;
                    background: #f1f5f9;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 11.5px;
                    color: #0f172a;
                    display: inline-block;
                    margin-top: 2px;
                }
                .mr-inv-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 24px;
                }
                .mr-inv-table th {
                    background: #7A1F3D;
                    color: #ffffff;
                    text-align: left;
                    font-size: 12px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    padding: 12px 14px;
                }
                .mr-inv-table td {
                    padding: 14px;
                    border-bottom: 1px solid #e2e8f0;
                    font-size: 13px;
                    vertical-align: top;
                }
                .mr-inv-table th:last-child,
                .mr-inv-table td:last-child {
                    text-align: right;
                }
                .mr-item-title {
                    font-weight: 700;
                    color: #1e293b;
                    font-size: 14px;
                    margin-bottom: 4px;
                }
                .mr-item-sub {
                    font-size: 12px;
                    color: #64748b;
                    line-height: 1.45;
                }
                .mr-inv-totals-box {
                    margin-left: auto;
                    width: 320px;
                    border-collapse: collapse;
                    margin-bottom: 28px;
                }
                .mr-inv-totals-box td {
                    padding: 6px 12px;
                    font-size: 13px;
                }
                .mr-inv-totals-box td:last-child {
                    text-align: right;
                    font-weight: 700;
                }
                .mr-inv-grand-total {
                    border-top: 2px solid #7A1F3D;
                    border-bottom: 2px solid #7A1F3D;
                    font-size: 16px !important;
                    font-weight: 800 !important;
                    color: #7A1F3D !important;
                    padding: 10px 12px !important;
                }
                .mr-inv-footer {
                    border-top: 1px solid #e2e8f0;
                    padding-top: 20px;
                    text-align: center;
                    font-size: 11.5px;
                    color: #94a3b8;
                    line-height: 1.6;
                }
                .mr-inv-print-bar {
                    max-width: 820px;
                    margin: 20px auto 0 auto;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .mr-btn-print {
                    background: #7A1F3D;
                    color: #ffffff;
                    border: none;
                    padding: 9px 18px;
                    border-radius: 8px;
                    font-weight: 700;
                    font-size: 13px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }
                .mr-btn-print:hover {
                    background: #5c152c;
                    color: #fff;
                }
                @media print {
                    body { background: #ffffff !important; }
                    .mr-inv-print-bar, .no-print { display: none !important; }
                    .mr-inv-wrapper {
                        border: none !important;
                        box-shadow: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        max-width: 100% !important;
                    }
                }
            </style>
        </head>
        <body>
            <?php if (!$is_email): ?>
                <div class="mr-inv-print-bar no-print">
                    <a href="<?php echo esc_url(home_url('/my-account/#passbook')); ?>" style="color:#64748b; font-size:13px; text-decoration:none;">
                        &larr; Return to My Account Passbook
                    </a>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="mr-btn-print" onclick="window.print();">
                            🖨️ Print / Save as PDF
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mr-inv-wrapper">
                <!-- Header -->
                <div class="mr-inv-header">
                    <div class="mr-inv-brand">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="MeraRashifal" style="max-height:46px; width:auto;" onerror="this.style.display='none';">
                        <h1><?php echo esc_html(self::COMPANY_NAME); ?></h1>
                        <p>
                            <?php echo esc_html(self::COMPANY_ADDRESS); ?><br>
                            Email: <a href="mailto:<?php echo esc_attr(self::COMPANY_EMAIL); ?>" style="color:#7A1F3D; text-decoration:none;"><?php echo esc_html(self::COMPANY_EMAIL); ?></a><br>
                            Status: <strong><?php echo esc_html(self::TAX_STATUS); ?></strong>
                        </p>
                    </div>

                    <div class="mr-inv-badge-wrap">
                        <span class="mr-inv-badge">RETAIL INVOICE / BILL</span>
                        <div class="mr-inv-num"><?php echo esc_html($invoice_num); ?></div>
                        <div class="mr-inv-date">Date: <strong><?php echo esc_html($date_formatted); ?></strong></div>
                        <div style="margin-top:6px;">
                            <span style="background:#ecfdf5; color:#047857; font-size:11px; font-weight:800; padding:2px 8px; border-radius:4px; border:1px solid #a7f3d0;">
                                ✔ PAYMENT RECEIVED (PAID)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Transaction Reference IDs -->
                <div class="mr-inv-meta-grid">
                    <div class="mr-inv-meta-box">
                        <h4>BILLED TO (CUSTOMER DETAILS)</h4>
                        <p>
                            <strong><?php echo esc_html($customer_name); ?></strong><br>
                            Email: <?php echo esc_html($customer_email ?: 'N/A'); ?><br>
                            Phone: <?php echo esc_html($customer_phone ?: 'N/A'); ?><br>
                            State: <strong><?php echo esc_html($billing_state); ?></strong> (<?php echo esc_html($billing_country); ?>)
                            <?php if ($is_international && !empty($billing_address)): ?>
                                <br>Address: <?php echo esc_html($billing_address . ($billing_city ? ', ' . $billing_city : '') . ($billing_pincode ? ' - ' . $billing_pincode : '')); ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mr-inv-meta-box">
                        <h4>TRANSACTION & GATEWAY REFERENCES</h4>
                        <p>
                            Internal Order Ref:<br>
                            <span class="mr-inv-id-tag"><?php echo esc_html($internal_id); ?></span><br>
                            
                            Razorpay Payment ID:<br>
                            <span class="mr-inv-id-tag" style="background:#fef3c7; color:#92400e;"><?php echo esc_html($rzp_payment_id); ?></span><br>

                            Gateway Order ID:<br>
                            <span class="mr-inv-id-tag"><?php echo esc_html($rzp_order_id); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Line Items Table -->
                <table class="mr-inv-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Description of Services & Dossiers</th>
                            <th style="width: 70px; text-align: center;">Qty</th>
                            <th style="width: 120px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <div class="mr-item-title"><?php echo esc_html($item_name); ?></div>
                                <div class="mr-item-sub">
                                    <?php if (!empty($reports)): ?>
                                        Included: 
                                        <?php 
                                        $r_names = array_map(function($slug) {
                                            return class_exists('MR_Bundles') ? MR_Bundles::get_report_name($slug) : $slug;
                                        }, $reports);
                                        echo esc_html(implode(' + ', $r_names));
                                        ?>
                                    <?php else: ?>
                                        Personalized Vedic Astrological Dossier (Vector PDF & Online Archive)
                                    <?php endif; ?>

                                    <?php if ($chat_credits > 0): ?>
                                        <br><span style="color:#0284c7; font-weight:700;">★ Includes <?php echo esc_html($chat_credits); ?> Niyati AI Astrological Consultation Questions</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                            <td>
                                <strong><?php echo esc_html($currency_sym . number_format($net_amount, 2)); ?></strong>
                                <?php if ($discount > 0): ?>
                                    <div style="font-size:11px; color:#64748b; text-decoration:line-through;">
                                        <?php echo esc_html($currency_sym . number_format($gross_amount, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Summary & Totals -->
                <table class="mr-inv-totals-box">
                    <tr>
                        <td>Subtotal:</td>
                        <td><?php echo esc_html($currency_sym . number_format($net_amount, 2)); ?></td>
                    </tr>
                    <?php if ($discount > 0): ?>
                    <tr>
                        <td style="color:#059669;">Bundle Discount:</td>
                        <td style="color:#059669;">- <?php echo esc_html($currency_sym . number_format($discount, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Taxes (0% Non-GST / Nil):</td>
                        <td><?php echo esc_html($currency_sym); ?>0.00</td>
                    </tr>
                    <tr class="mr-inv-grand-total">
                        <td>TOTAL PAID:</td>
                        <td><?php echo esc_html($currency_sym . number_format($net_amount, 2)); ?></td>
                    </tr>
                </table>

                <!-- Footer -->
                <div class="mr-inv-footer">
                    <p>
                        Thank you for ordering with <strong>MeraRashifal</strong>!<br>
                        This is an official computer-generated retail invoice and electronic receipt for your purchase.<br>
                        All report tokens and AI chat credits have been credited to your account and remain accessible for lifetime download.
                    </p>
                    <p style="margin-top:6px; color:#cbd5e1;">
                        &copy; <?php echo esc_html($year); ?> <?php echo esc_html(self::COMPANY_NAME); ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate and cache PDF file for this invoice
     *
     * @param array $txn Transaction row
     * @return string|false File path on disk or false
     */
    public static function generate_pdf_invoice($txn) {
        $upload_dir = wp_upload_dir();
        $invoice_dir = $upload_dir['basedir'] . '/mr-invoices';
        if (!file_exists($invoice_dir)) {
            wp_mkdir_p($invoice_dir);
        }

        $inv_safe_name = sanitize_file_name(str_replace(['/', '\\'], '-', $txn['invoice_number']));
        if (empty($inv_safe_name)) {
            $inv_safe_name = 'invoice-' . sanitize_file_name($txn['internal_txn_id']);
        }

        $html_file = $invoice_dir . '/' . $inv_safe_name . '.html';
        $pdf_file  = $invoice_dir . '/' . $inv_safe_name . '.pdf';

        // Render HTML
        $html_content = self::render_invoice_html($txn, false);
        file_put_contents($html_file, $html_content);

        // Render PDF using MR_PDF_Engine
        if (class_exists('MR_PDF_Engine')) {
            $res = MR_PDF_Engine::render_pdf($html_file, $pdf_file);
            if (!empty($res['success']) && file_exists($pdf_file)) {
                return $pdf_file;
            }
        }

        // Return HTML file if PDF engine unavailable
        return file_exists($pdf_file) ? $pdf_file : $html_file;
    }

    /**
     * Send email with snapshot in body + PDF invoice attached
     *
     * @param array|string $txn_or_id
     * @return bool
     */
    public static function send_invoice_email($txn_or_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';

        if (is_array($txn_or_id)) {
            $txn = $txn_or_id;
        } else {
            $txn = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d OR internal_txn_id = %s LIMIT 1",
                is_numeric($txn_or_id) ? intval($txn_or_id) : 0,
                $txn_or_id
            ), ARRAY_A);
        }

        if (!$txn || empty($txn['customer_email'])) {
            return false;
        }

        // Ensure invoice number is assigned
        if (empty($txn['invoice_number'])) {
            $txn['invoice_number'] = self::assign_invoice_to_transaction($txn['id']);
        }

        // Generate PDF invoice attachment
        $pdf_path = self::generate_pdf_invoice($txn);
        $attachments = [];
        if (!empty($pdf_path) && file_exists($pdf_path) && substr($pdf_path, -4) === '.pdf') {
            $attachments[] = $pdf_path;
        }

        // Render HTML email body snapshot
        $html_snapshot = self::render_invoice_html($txn, true);

        $to      = $txn['customer_email'];
        $subject = '🧾 Tax Invoice & Order Receipt: ' . $txn['invoice_number'] . ' - MeraRashifal';
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: MeraRashifal Support <' . self::COMPANY_EMAIL . '>',
        ];

        return wp_mail($to, $subject, $html_snapshot, $headers, $attachments);
    }

    /**
     * Public template redirect for viewing / downloading invoices
     * URL: /invoice/?id=MR_ORD_... OR /my-account/?action=invoice&id=...
     */
    public static function handle_invoice_request() {
        $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
        $is_invoice_url = (strpos($uri, 'invoice') === 0 || isset($_GET['mr_invoice']) || (isset($_GET['action']) && $_GET['action'] === 'invoice'));

        if (!$is_invoice_url) {
            return;
        }

        $id = sanitize_text_field($_GET['id'] ?? ($_GET['order_id'] ?? ($_GET['txn'] ?? '')));
        if (empty($id)) {
            wp_die(__('Invalid invoice identifier.', 'merarashifal'), 404);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE internal_txn_id = %s OR invoice_number = %s OR gateway_order_id = %s LIMIT 1",
            $id, $id, $id
        ), ARRAY_A);

        if (!$txn) {
            wp_die(__('Invoice record not found.', 'merarashifal'), 404);
        }

        // Security check: Only buyer or admin can view
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        $is_owner = ($current_user_id > 0 && intval($txn['user_id']) === $current_user_id);

        // Also allow guest if signature hash matches
        $hash = sanitize_text_field($_GET['k'] ?? '');
        $expected_hash = substr(md5($txn['internal_txn_id'] . $txn['customer_email'] . AUTH_KEY), 0, 12);
        $is_hash_valid = hash_equals($expected_hash, (string)$hash);

        if (!$is_admin && !$is_owner && !$is_hash_valid) {
            // If user not logged in, prompt login
            if (!is_user_logged_in()) {
                auth_redirect();
            } else {
                wp_die(__('You do not have permission to view this invoice.', 'merarashifal'), 403);
            }
        }

        // Ensure invoice number
        if (empty($txn['invoice_number'])) {
            $txn['invoice_number'] = self::assign_invoice_to_transaction($txn['id']);
        }

        // Render HTML invoice
        echo self::render_invoice_html($txn, false);
        exit;
    }

    /**
     * AJAX action to download invoice
     */
    public static function ajax_download_invoice() {
        $id = sanitize_text_field($_GET['id'] ?? '');
        if (empty($id)) {
            wp_send_json_error(['message' => 'Missing ID']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mr_transactions';
        $txn = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE internal_txn_id = %s OR invoice_number = %s LIMIT 1",
            $id, $id
        ), ARRAY_A);

        if (!$txn) {
            wp_send_json_error(['message' => 'Invoice not found']);
        }

        if (empty($txn['invoice_number'])) {
            $txn['invoice_number'] = self::assign_invoice_to_transaction($txn['id']);
        }

        $pdf = self::generate_pdf_invoice($txn);
        if ($pdf && file_exists($pdf)) {
            $url = content_url('/uploads/mr-invoices/' . basename($pdf));
            wp_send_json_success(['invoice_url' => $url, 'invoice_number' => $txn['invoice_number']]);
        }

        wp_send_json_error(['message' => 'PDF compilation failed']);
    }

    /**
     * Get direct invoice URL for a transaction
     */
    public static function get_invoice_url($txn) {
        $internal_id = $txn['internal_txn_id'] ?? '';
        $email       = $txn['customer_email'] ?? '';
        $k           = substr(md5($internal_id . $email . AUTH_KEY), 0, 12);
        return home_url('/invoice/?id=' . urlencode($internal_id) . '&k=' . $k);
    }
}
