<?php
/**
 * Frontend User Passbook UI & Invoicing Component
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Passbook_UI {

    public static function init() {
        add_shortcode('mr_user_passbook', [__CLASS__, 'render_user_passbook']);
        add_shortcode('mr_passbook', [__CLASS__, 'render_user_passbook']);
        add_shortcode('mr_transactions', [__CLASS__, 'render_user_passbook']);
        add_action('template_redirect', [__CLASS__, 'redirect_standalone_passbook']);
    }

    /**
     * Gracefully 301 redirect any requests from retired pages
     */
    public static function redirect_standalone_passbook() {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
        if (preg_match('#(?:^|/)passbook$#i', $uri)) {
            wp_safe_redirect(home_url('/my-account/#passbook'), 301);
            exit;
        }
        if (preg_match('#(?:^|/)my-account-reports$#i', $uri)) {
            wp_safe_redirect(home_url('/my-account/#reports'), 301);
            exit;
        }
        if (preg_match('#(?:^|/)reports-catalog$#i', $uri)) {
            wp_safe_redirect(home_url('/reports/'), 301);
            exit;
        }
    }

    /**
     * Render User Passbook
     */
    public static function render_user_passbook($atts = []) {
        if (!is_user_logged_in()) {
            return '<div style="max-width:800px; margin:40px auto; padding:30px; text-align:center; background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; font-family:sans-serif;">
                <h3 style="color:#c2410c; margin-top:0;">🔒 Account Login Required</h3>
                <p style="color:#7c2d12;">Please log in to your MeraRashifal account to access your transaction passbook, purchase history, and official invoices.</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" style="display:inline-block; margin-top:10px; background:#FF8C00; color:#fff; padding:10px 24px; border-radius:6px; text-decoration:none; font-weight:700;">Log In to Account</a>
            </div>';
        }

        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        if (!class_exists('MR_Transactions')) {
            return '<p>Transaction engine not loaded.</p>';
        }

        $txns = MR_Transactions::get_user_transactions($user_id, 100);

        // Auto-reconcile recent transactions with Razorpay API (cached per user for 60s)
        $transient_key = 'mr_pb_sync_' . $user_id;
        if (false === get_transient($transient_key) && class_exists('MR_Razorpay')) {
            set_transient($transient_key, 1, 60);
            $needs_refresh = false;
            foreach ($txns as $t) {
                if ($t['status'] === 'success' && !empty($t['gateway_payment_id']) && strtotime($t['created_at']) > (time() - 3 * DAY_IN_SECONDS)) {
                    $sync_res = MR_Razorpay::sync_payment($t['gateway_payment_id']);
                    if (is_array($sync_res) && isset($sync_res['status']) && $sync_res['status'] !== 'success') {
                        $needs_refresh = true;
                    }
                }
            }
            if ($needs_refresh) {
                $txns = MR_Transactions::get_user_transactions($user_id, 100);
            }
        }

        // Calculate user totals
        $total_spent = 0;
        $success_count = 0;
        $total_refunded = 0;
        foreach ($txns as $t) {
            if ($t['status'] === 'success') {
                $total_spent += floatval($t['net_amount']);
                $success_count++;
            } elseif ($t['status'] === 'refunded' || $t['status'] === 'partial_refund') {
                $meta = !empty($t['metadata']) ? json_decode($t['metadata'], true) : [];
                $ref_amt = isset($meta['refund_amount']) ? floatval($meta['refund_amount']) : floatval($t['net_amount']);
                $total_refunded += $ref_amt;
            }
        }

        ob_start();
        ?>
        <style>
        .mr-passbook-wrap {
            max-width: 1100px;
            margin: 30px auto 60px auto;
            font-family: 'Noto Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
        }
        .mr-pb-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 28px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .mr-pb-title-area h2 {
            margin: 0 0 6px 0;
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
        }
        .mr-pb-title-area p {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
        }
        .mr-pb-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .mr-pb-stat-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 12px 20px;
            text-align: right;
        }
        .mr-pb-stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #cbd5e1;
            margin-bottom: 4px;
        }
        .mr-pb-stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #fbbf24;
        }
        .mr-pb-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }
        .mr-pb-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }
        .mr-pb-table th {
            background: #f8fafc;
            padding: 14px 18px;
            font-weight: 700;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #e2e8f0;
        }
        .mr-pb-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .mr-pb-table tr:hover {
            background: #f8fafc;
        }
        .mr-badge-channel {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .mr-badge-report { background: #e0f2fe; color: #0369a1; }
        .mr-badge-subscription { background: #fef3c7; color: #b45309; }
        .mr-badge-chat { background: #f3e8ff; color: #7e22ce; }
        .mr-badge-ecommerce { background: #ecfdf5; color: #047857; }

        .mr-badge-status {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50px;
        }
        .mr-status-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .mr-status-pending { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .mr-status-failed { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .mr-status-refunded { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }

        .mr-mode-badge {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }
        .mr-mode-test { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .mr-mode-live { background: #ecfdf5; color: #047857; }

        .mr-txn-id {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            background: #f1f5f9;
            padding: 3px 7px;
            border-radius: 4px;
            display: inline-block;
        }
        .mr-gateway-id {
            font-size: 11px;
            color: #64748b;
            font-family: monospace;
            margin-top: 3px;
        }
        .mr-btn-action {
            display: inline-block;
            background: #FF8C00;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: background 0.2s ease;
        }
        .mr-btn-action:hover {
            background: #e07b00;
            color: #fff;
        }
        .mr-empty-passbook {
            padding: 50px 20px;
            text-align: center;
            color: #64748b;
        }
        </style>

        <div class="mr-passbook-wrap">
            <div class="mr-pb-header">
                <div class="mr-pb-title-area">
                    <h2>📜 My Account Passbook</h2>
                    <p>Official record of all report orders, subscription renewals, and payments.</p>
                </div>
                <div class="mr-pb-stats">
                    <div class="mr-pb-stat-card">
                        <div class="mr-pb-stat-label">Total Completed</div>
                        <div class="mr-pb-stat-value"><?php echo esc_html($success_count); ?> Orders</div>
                    </div>
                    <div class="mr-pb-stat-card">
                        <div class="mr-pb-stat-label">Total Amount Paid</div>
                        <div class="mr-pb-stat-value">₹<?php echo number_format($total_spent, 2); ?></div>
                    </div>
                    <?php if ($total_refunded > 0): ?>
                    <div class="mr-pb-stat-card" style="border-color: rgba(196, 181, 253, 0.4); background: rgba(124, 58, 237, 0.12);">
                        <div class="mr-pb-stat-label" style="color: #ddd6fe;">Total Refunded</div>
                        <div class="mr-pb-stat-value" style="color: #c4b5fd;">₹<?php echo number_format($total_refunded, 2); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mr-pb-table-card">
                <?php if (empty($txns)): ?>
                    <div class="mr-empty-passbook">
                        <i class="fa-solid fa-receipt fa-3x" style="color:#cbd5e1; margin-bottom:14px;"></i>
                        <p style="font-size:16px; margin:0 0 10px 0;">No transactions found in your passbook yet.</p>
                        <a href="<?php echo esc_url(home_url('/reports')); ?>" class="mr-btn-action" style="padding:10px 20px; font-size:14px;">Browse Report Catalog</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="mr-pb-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Transaction Ref</th>
                                    <th>Service / Item</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($txns as $t): 
                                    $channel_class = 'mr-badge-' . sanitize_html_class($t['channel']);
                                    $channel_label = ($t['channel'] === 'subscription') ? 'Subscription' : (($t['channel'] === 'chat_credit') ? 'AI Chat' : (($t['channel'] === 'ecommerce') ? 'Store Product' : 'Astro Report'));
                                    $status_class  = 'mr-status-' . sanitize_html_class($t['status']);
                                    $is_test       = ($t['gateway_mode'] === 'test');
                                    $is_refunded   = ($t['status'] === 'refunded' || $t['status'] === 'partial_refund');
                                    $meta          = !empty($t['metadata']) ? json_decode($t['metadata'], true) : [];
                                    $refund_amt    = isset($meta['refund_amount']) ? floatval($meta['refund_amount']) : floatval($t['net_amount']);
                                    $refund_id     = $meta['refund_id'] ?? '';
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:#1e293b;"><?php echo date('d M Y', strtotime($t['created_at'])); ?></div>
                                        <div style="font-size:12px; color:#64748b;"><?php echo date('h:i A', strtotime($t['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <span class="mr-txn-id"><?php echo esc_html($t['internal_txn_id']); ?></span>
                                        <?php if (!empty($t['invoice_number'])): ?>
                                            <div style="font-size:11.5px; font-weight:700; color:#7A1F3D; margin-top:3px;">
                                                <i class="fa-solid fa-file-invoice"></i> <?php echo esc_html($t['invoice_number']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($t['gateway_payment_id'])): ?>
                                            <div class="mr-gateway-id"><i class="fa-solid fa-shield-halved" style="font-size:9px;"></i> <?php echo esc_html($t['gateway_payment_id']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($is_refunded && !empty($refund_id)): ?>
                                            <div class="mr-gateway-id" style="color:#7C3AED; font-weight:700;"><i class="fa-solid fa-arrow-rotate-left"></i> <?php echo esc_html($refund_id); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="mr-badge-channel <?php echo esc_attr($channel_class); ?>"><?php echo esc_html($channel_label); ?></span>
                                        <?php if ($is_test): ?>
                                            <span class="mr-mode-badge mr-mode-test">TEST</span>
                                        <?php endif; ?>
                                        <div style="font-weight:700; color:#0f172a; margin-top:4px;"><?php echo esc_html($t['item_name']); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-weight:600; text-transform:capitalize; color:#475569;"><?php echo esc_html($t['txn_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($is_refunded): ?>
                                            <s style="color:#94a3b8; font-size:13px;">₹<?php echo number_format($t['net_amount'], 2); ?></s>
                                            <div style="font-weight:800; font-size:14px; color:#7C3AED; margin-top:2px;">
                                                + ₹<?php echo number_format($refund_amt, 2); ?>
                                                <span style="font-size:11px; font-weight:600; display:block; color:#6b21a8;">Refunded</span>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-weight:800; font-size:15px; color:#0f172a;">₹<?php echo number_format($t['net_amount'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="mr-badge-status <?php echo esc_attr($status_class); ?>">
                                            <?php if ($t['status'] === 'success'): ?>
                                                ✔ Paid
                                            <?php elseif ($t['status'] === 'failed'): ?>
                                                ✖ Failed
                                            <?php elseif ($t['status'] === 'refunded'): ?>
                                                ↩ Refunded
                                            <?php elseif ($t['status'] === 'partial_refund'): ?>
                                                ↩ Partial Refund
                                            <?php else: ?>
                                                ⏳ Pending
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($is_refunded): ?>
                                            <span style="display:inline-block; font-size:11px; color:#7c3aed; background:#f5f3ff; border:1px solid #ddd6fe; padding:4px 8px; border-radius:6px; font-weight:600;">
                                                <i class="fa-solid fa-building-columns"></i> Sent to Bank
                                            </span>
                                        <?php elseif ($t['status'] === 'success'): ?>
                                            <div style="display:flex; flex-direction:column; gap:5px; align-items:flex-start;">
                                                <?php if ($t['channel'] === 'report'): ?>
                                                    <a href="<?php echo esc_url(home_url('/reports')); ?>" class="mr-btn-action" style="white-space:nowrap;">Generate Report</a>
                                                <?php elseif ($t['channel'] === 'subscription'): ?>
                                                    <a href="<?php echo esc_url(home_url('/reports')); ?>" class="mr-btn-action" style="background:#0f172a; white-space:nowrap;">View Pack</a>
                                                <?php elseif ($t['channel'] === 'chat_credit'): ?>
                                                    <button onclick="if(window.document.getElementById('niyati-launcher')){window.document.getElementById('niyati-launcher').click();}" class="mr-btn-action" style="background:#7E22CE; border:none; cursor:pointer; white-space:nowrap;">💬 Open Chat</button>
                                                <?php endif; ?>

                                                <a href="<?php echo esc_url(home_url('/invoice/?id=' . urlencode($t['internal_txn_id']))); ?>" target="_blank" class="mr-btn-action" style="background:#7A1F3D; padding:4px 9px; font-size:11px; border-radius:6px; white-space:nowrap; text-decoration:none;" title="Download PDF Retail Invoice">
                                                    <i class="fa-solid fa-file-invoice"></i> Invoice (PDF)
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size:12px; color:#94a3b8;">--</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
