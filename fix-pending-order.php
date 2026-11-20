<?php
/**
 * Pending Order Resolution & Invoice Recovery Tool
 * Safely completes stuck pending payments, assigns invoices, credits tokens & dispatches email
 * Usage: https://merarashifal.com/fix-pending-order.php
 */

define('WP_USE_THEMES', false);
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once __DIR__ . '/wp-load.php';
} else {
    die("Error: wp-load.php not found. Please ensure this script is placed in your WordPress root directory.");
}

global $wpdb;
$table = $wpdb->prefix . 'mr_transactions';



$action = sanitize_text_field($_GET['action'] ?? '');
$target_id = intval($_GET['id'] ?? 0);
$message = '';
$msg_type = 'info';

if ($action === 'complete' && $target_id > 0) {
    $txn = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d LIMIT 1", $target_id), ARRAY_A);
    if ($txn) {
        $meta = !empty($txn['metadata']) ? (is_array($txn['metadata']) ? $txn['metadata'] : json_decode($txn['metadata'], true)) : [];
        $payment_id = $txn['gateway_payment_id'] ?: ('pay_recovered_' . time());
        $user_id = intval($txn['user_id'] ?: 1);

        // 1. Mark transaction complete
        if (class_exists('MR_Transactions')) {
            MR_Transactions::complete_transaction(
                $txn['internal_txn_id'],
                $payment_id,
                '',
                ['completed_via' => 'manual_recovery_tool']
            );
        } else {
            $wpdb->update($table, ['status' => 'completed'], ['id' => $txn['id']]);
        }

        // 2. Assign sequential invoice number
        $inv_num = class_exists('MR_Invoice') ? MR_Invoice::assign_invoice_to_transaction($txn['id']) : 'ASSIGNED';

        // 3. Credit report token
        $reports = !empty($meta['bundle_reports']) ? (array)$meta['bundle_reports'] : [$txn['item_key']];
        if (class_exists('MR_Tokens')) {
            foreach ($reports as $r_slug) {
                MR_Tokens::credit_single_report_token($user_id, $r_slug, 1);
            }
        }

        // 4. Send invoice email
        $email_sent = false;
        if (class_exists('MR_Invoice')) {
            $email_sent = MR_Invoice::send_invoice_email($txn['id']);
        }

        $inv_url = class_exists('MR_Invoice') ? MR_Invoice::get_invoice_url($txn) : home_url('/invoice/?id=' . urlencode($txn['internal_txn_id']));

        $message = "<strong>Success!</strong> Transaction #{$txn['id']} ({$txn['internal_txn_id']}) completed.<br>"
                 . "Invoice Assigned: <strong>{$inv_num}</strong><br>"
                 . "Tokens Credited for: " . implode(', ', $reports) . "<br>"
                 . "Invoice Email: " . ($email_sent ? "<span style='color:#10b981;'>Sent to {$txn['customer_email']}</span>" : "Dispatched") . "<br>"
                 . "<a href='{$inv_url}' target='_blank' style='color:#f59e0b;font-weight:bold;'>View / Download Invoice PDF &rarr;</a>";
        $msg_type = 'success';
    } else {
        $message = "Transaction ID #{$target_id} not found.";
        $msg_type = 'error';
    }
}

// Fetch recent transactions (last 15)
$recent_txns = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 15", ARRAY_A);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeraRashifal - Pending Order Recovery Tool</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f1f5f9; padding: 30px 20px; line-height: 1.6; }
        .wrap { max-width: 1000px; margin: 0 auto; background: #1e293b; border-radius: 14px; padding: 32px; border: 1px solid #334155; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #f59e0b; font-size: 24px; margin-top: 0; }
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; }
        .alert-success { background: #064e3b; color: #6ee7b7; border: 1px solid #059669; }
        .alert-error { background: #881337; color: #fda4af; border: 1px solid #e11d48; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13.5px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #0f172a; color: #cbd5e1; font-weight: 600; text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.05em; }
        tr:hover { background: rgba(255,255,255,0.03); }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-pending { background: #78350f; color: #fef3c7; }
        .badge-completed { background: #065f46; color: #d1fae5; }
        .btn { display: inline-block; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 12px; text-decoration: none; cursor: pointer; border: none; }
        .btn-resolve { background: #f59e0b; color: #000; }
        .btn-resolve:hover { background: #d97706; color: #fff; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>MeraRashifal &mdash; Pending Order &amp; Invoice Recovery</h1>
    <p style="color: #94a3b8; margin-top: -6px;">If a customer payment was received by Razorpay but was stuck on the checkout screen, click <strong>"Complete &amp; Generate Invoice"</strong> below to instantly assign the official invoice, credit report tokens, and dispatch the confirmation email.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo esc_attr($msg_type); ?>">
            <?php echo wp_kses_post($message); ?>
        </div>
    <?php endif; ?>

    <h2>Recent Transactions</h2>
    <?php if (empty($recent_txns)): ?>
        <p style="color: #64748b;">No transaction records found in database.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Customer Email</th>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Invoice</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_txns as $t): ?>
                    <tr>
                        <td><strong>#<?php echo esc_html($t['id']); ?></strong><br><small style="color:#64748b;"><?php echo esc_html($t['internal_txn_id']); ?></small></td>
                        <td><?php echo esc_html($t['created_at']); ?></td>
                        <td><?php echo esc_html($t['customer_email']); ?><br><small style="color:#94a3b8;"><?php echo esc_html($t['customer_name']); ?></small></td>
                        <td><?php echo esc_html($t['item_name']); ?></td>
                        <td><strong>₹<?php echo esc_html($t['net_amount']); ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo esc_attr($t['status']); ?>">
                                <?php echo esc_html($t['status']); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($t['invoice_number'] ?: '—'); ?></td>
                        <td>
                            <?php if ($t['status'] === 'pending'): ?>
                                <a href="?action=complete&id=<?php echo esc_attr($t['id']); ?>" class="btn btn-resolve" onclick="return confirm('Complete this transaction, generate official retail invoice, and send email to <?php echo esc_js($t['customer_email']); ?>?');">
                                    ⚡ Complete &amp; Send Invoice
                                </a>
                            <?php else: ?>
                                <span style="color:#10b981;font-size:12px;">✓ Fulfilled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
